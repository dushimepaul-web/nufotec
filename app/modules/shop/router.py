from datetime import date
from decimal import Decimal
from typing import Literal

from fastapi import APIRouter, BackgroundTasks, Depends, File, Query, UploadFile, status
from fastapi.responses import StreamingResponse
from fastapi.security import HTTPAuthorizationCredentials
from pydantic import BaseModel, EmailStr, Field, field_validator
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core.client import ClientInfo, get_client
from app.core.database import get_db
from app.core.deps import bearer, get_current_user
from app.core.helpers import now
from app.core.pagination import Page, PageParams, paginate
from app.core.permissions import require_permission
from app.core.responses import ApiError, Envelope, ok
from app.core.text import clean_free_text, clean_person_name
from app.core.types import DateTime
from app.models import Product, ProductCategory, User
from app.modules.shop import service

DB = Depends(get_db)
CLIENT = Depends(get_client)
MANAGE = Depends(require_permission("shop.manage"))

public = APIRouter(tags=["Produits (public)"])
customer = APIRouter(prefix="/commandes", tags=["Commandes · Client"])
admin = APIRouter(prefix="/admin", tags=["Admin · Produits et commandes"])

OrderStatut = Literal["en_attente", "en_cours", "livree", "annulee"]


# ───────────── schémas ─────────────

class CategoryBrief(BaseModel):
    id: int
    nom: str
    slug: str | None
    code: str | None


class ProductItem(BaseModel):
    uuid: str
    slug: str | None
    titre: str
    image: str | None
    prix_bif: Decimal | None
    prix_usd: Decimal | None
    categorie: CategoryBrief | None
    vedette: bool
    en_stock: bool
    produit_sante: bool
    extrait: str | None = Field(None, description="Début de la description en texte brut (cartes produits)")
    nouveau: bool = Field(False, description="Ajouté il y a moins de 30 jours (badge NOUVEAU)")
    created_at: DateTime | None = None


class ProductDetail(ProductItem):
    description: str | None
    galerie: list[dict]
    stock: int | None
    avertissement_sante: str | None
    proposer_consultation: bool
    similaires: list[ProductItem] = []


class WhatsAppOut(BaseModel):
    numero: str
    message: str
    url: str = Field(description="À ouvrir : WhatsApp s'ouvre vers le service des ventes, message déjà rempli")


class OrderIn(BaseModel):
    nom_complet: str = Field(min_length=2, max_length=255)
    telephone: str = Field(min_length=6, max_length=30, description="Numéro WhatsApp")
    email: EmailStr | None = None
    pays_id: int
    ville: str = Field(min_length=2, max_length=100)
    adresse: str = Field(min_length=3, max_length=1000)
    quantite: int = Field(default=1, ge=1, le=100)
    remarques: str | None = Field(default=None, max_length=1000)
    site_web: str | None = Field(default=None, description="Champ invisible anti-robots : doit rester vide")

    @field_validator("nom_complet", "ville")
    @classmethod
    def _clean(cls, v: str) -> str:
        # caractères de contrôle, balises et symboles retirés ; au moins une lettre (voir app/core/text.py)
        cleaned = clean_person_name(v)
        if len(cleaned) < 2:
            raise ValueError("trop court")
        return cleaned

    @field_validator("adresse", "remarques")
    @classmethod
    def _clean_text(cls, v: str | None) -> str | None:
        return clean_free_text(v)

    @field_validator("adresse")
    @classmethod
    def _address_required(cls, v: str | None) -> str:
        if not v or len(v) < 3:
            raise ValueError("adresse trop courte")
        return v


class OrderOut(BaseModel):
    numero: str | None
    statut: str
    produit: str
    produit_slug: str | None
    quantite: int
    prix_unitaire: Decimal | None
    devise: str | None
    total: Decimal | None
    prix_affiche: str | None
    ville: str | None
    pays: str | None
    created_at: DateTime | None


class OrderPlacedOut(BaseModel):
    commande: OrderOut | None
    whatsapp: WhatsAppOut


class OrderAdminOut(OrderOut):
    client: str
    telephone: str
    email: str | None
    adresse: str | None
    remarques: str | None
    whatsapp_contacte: bool
    whatsapp_contacte_le: DateTime | None


class ProductIn(BaseModel):
    titre: str = Field(min_length=2, max_length=255)
    description: str | None = Field(default=None, max_length=20000)
    categorie_id: int | None = None
    prix_bif: Decimal | None = Field(default=None, ge=0, max_digits=12, decimal_places=2)
    prix_usd: Decimal | None = Field(default=None, ge=0, max_digits=10, decimal_places=2)
    stock: int | None = Field(default=None, ge=0, description="null = stock non suivi")
    produit_sante: bool = False
    publie: bool = True
    vedette: bool = False


class ProductUpdateIn(BaseModel):
    titre: str | None = Field(default=None, min_length=2, max_length=255)
    description: str | None = Field(default=None, max_length=20000)
    categorie_id: int | None = None
    prix_bif: Decimal | None = Field(default=None, ge=0, max_digits=12, decimal_places=2)
    prix_usd: Decimal | None = Field(default=None, ge=0, max_digits=10, decimal_places=2)
    stock: int | None = Field(default=None, ge=0)
    produit_sante: bool | None = None
    publie: bool | None = None
    vedette: bool | None = None


class CategoryIn(BaseModel):
    nom: str = Field(min_length=2, max_length=100)
    parent_id: int | None = None
    code: str | None = Field(default=None, max_length=5)
    description: str | None = Field(default=None, max_length=5000)
    position: int = Field(default=0, ge=0, le=10000)
    actif: bool = True


class StatusIn(BaseModel):
    statut: OrderStatut
    note: str | None = Field(default=None, max_length=1000)


class NoteIn(BaseModel):
    texte: str = Field(min_length=1, max_length=3000)


def _optional_user(db: Session, creds: HTTPAuthorizationCredentials | None) -> User | None:
    if creds is None:
        return None
    from app.core.deps import get_current_session

    try:
        return get_current_user(get_current_session(creds, db), db)
    except ApiError:
        return None


# ═════════════ Public ═════════════

@public.get("/produits/categories", response_model=Envelope[list[dict]], summary="Catégories (arbre, avec nombre de produits)")
def categories(db: Session = DB):
    return ok(service.categories_tree(db))


@public.get("/produits", response_model=Envelope[Page[ProductItem]], summary="Catalogue : recherche, filtres, tri")
def catalog(q: str | None = Query(None, max_length=100), categorie: str | None = Query(None, max_length=120),
            vedette: bool = False, prix_min: Decimal | None = Query(None, ge=0), prix_max: Decimal | None = Query(None, ge=0),
            devise: Literal["BIF", "USD"] = "BIF",
            tri: Literal["recent", "prix_asc", "prix_desc", "populaire", "nom"] = "recent",
            page: PageParams = Depends(), db: Session = DB):
    rows, meta = paginate(db, service.catalog_query(db, q, categorie, vedette, prix_min, prix_max, devise, tri), page)
    return ok({"items": [service.product_out(p) for p in rows], "meta": meta})


@public.get("/produits/{slug}", response_model=Envelope[ProductDetail], summary="Fiche produit")
def product(slug: str, db: Session = DB):
    p = service.get_public(db, slug)
    return ok({**service.product_out(p, detail=True), "similaires": [service.product_out(r) for r in service.related(db, p)]})


@public.post("/produits/{slug}/commander", status_code=status.HTTP_201_CREATED, response_model=Envelope[OrderPlacedOut],
             summary="Commander : enregistre la commande et renvoie le lien WhatsApp vers les ventes")
def order(slug: str, body: OrderIn, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT,
          creds: HTTPAuthorizationCredentials | None = Depends(bearer)):
    p = service.get_public(db, slug)
    o, wa = service.place_order(db, p, body, _optional_user(db, creds), client, background)
    return ok({"commande": service.order_out(db, o) if o else None, "whatsapp": wa},
              "Commande enregistrée : envoyez-la maintenant sur WhatsApp pour finaliser")


@public.post("/produits/{slug}/demande-prix", response_model=Envelope[WhatsAppOut],
             summary="Demander le prix actualisé (ouvre WhatsApp)")
def ask_price(slug: str, db: Session = DB):
    return ok(service.price_request(db, service.get_public(db, slug)))


# ═════════════ Client ═════════════

@customer.get("/suivi", response_model=Envelope[OrderOut], summary="Suivre une commande sans compte (numéro + téléphone)")
def track(numero: str = Query(max_length=30), telephone: str = Query(max_length=30), db: Session = DB):
    return ok(service.order_out(db, service.track(db, numero, telephone)))


@customer.get("", response_model=Envelope[Page[OrderOut]], summary="Mes commandes (compte ou téléphone du profil)")
def mine(page: PageParams = Depends(), user: User = Depends(get_current_user), db: Session = DB):
    rows, meta = paginate(db, service.my_orders_query(user), page)
    return ok({"items": [service.order_out(db, o) for o in rows], "meta": meta})


# ═════════════ Administration ═════════════

def _product(db: Session, product_uuid: str) -> Product:
    return service.get_product(db, product_uuid)


def _admin_product(p: Product) -> dict:
    return {**service.product_out(p, detail=True), "publie": p.is_active, "supprime": p.deleted_at is not None,
            "demandes_de_prix": p.price_request_count}


@admin.get("/produits", response_model=Envelope[Page[dict]], summary="Tous les produits (brouillons compris)")
def admin_products(q: str | None = Query(None, max_length=100), supprimes: bool = False, page: PageParams = Depends(),
                   db: Session = DB, _: User = MANAGE):
    query = select(Product).where(Product.deleted_at.is_not(None) if supprimes else Product.deleted_at.is_(None))
    if q:
        from app.core.helpers import like_pattern

        query = query.where(Product.title.like(like_pattern(q)))
    rows, meta = paginate(db, query.order_by(Product.created_at.desc()), page)
    return ok({"items": [_admin_product(p) for p in rows], "meta": meta})


@admin.get("/produits/categories", response_model=Envelope[list[dict]],
           summary="Toutes les catégories, inactives comprises (avec leur état)")
def admin_categories(db: Session = DB, _: User = MANAGE):
    return ok(service.categories_tree(db, include_inactive=True))


@admin.get("/produits/{product_uuid}", response_model=Envelope[dict], summary="Un produit (formulaire de modification)")
def admin_product(product_uuid: str, db: Session = DB, _: User = MANAGE):
    return ok(_admin_product(_product(db, product_uuid)))


@admin.post("/produits", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict], summary="Nouveau produit")
def admin_create(body: ProductIn, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    return ok(_admin_product(service.create_product(db, actor, body, client)), "Produit créé")


@admin.patch("/produits/{product_uuid}", response_model=Envelope[dict], summary="Modifier (prix, stock, publication…)")
def admin_update(product_uuid: str, body: ProductUpdateIn, db: Session = DB, client: ClientInfo = CLIENT,
                 actor: User = MANAGE):
    p = service.update_product(db, actor, _product(db, product_uuid), body.model_dump(exclude_unset=True), client)
    return ok(_admin_product(p), "Produit mis à jour")


@admin.post("/produits/{product_uuid}/image", response_model=Envelope[dict], summary="Image principale")
async def admin_main_image(product_uuid: str, image: UploadFile = File(...), db: Session = DB, _: User = MANAGE):
    p = service.set_main_image(db, _product(db, product_uuid), await image.read(5 * 1024 * 1024 + 1))
    return ok(_admin_product(p), "Image mise à jour")


@admin.post("/produits/{product_uuid}/galerie", response_model=Envelope[dict], summary="Ajouter une image à la galerie")
async def admin_gallery(product_uuid: str, image: UploadFile = File(...), db: Session = DB, _: User = MANAGE):
    p = service.add_gallery_image(db, _product(db, product_uuid), await image.read(5 * 1024 * 1024 + 1))
    return ok(_admin_product(p), "Image ajoutée")


@admin.delete("/produits/{product_uuid}/galerie/{image_id}", response_model=Envelope[dict], summary="Retirer une image")
def admin_gallery_delete(product_uuid: str, image_id: int, db: Session = DB, _: User = MANAGE):
    return ok(_admin_product(service.delete_gallery_image(db, _product(db, product_uuid), image_id)), "Image retirée")


@admin.delete("/produits/{product_uuid}", response_model=Envelope[None],
              summary="Supprimer (suppression douce : l'historique des commandes est conservé)")
def admin_delete(product_uuid: str, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    service.soft_delete(db, actor, _product(db, product_uuid), client)
    return ok(message="Produit supprimé")


@admin.post("/produits/categories", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
            summary="Nouvelle catégorie")
def admin_add_category(body: CategoryIn, db: Session = DB, _: User = MANAGE):
    c = service.save_category(db, None, body)
    return ok({"id": c.id, "nom": c.name, "slug": c.slug, "parent_id": c.parent_id}, "Catégorie créée")


@admin.put("/produits/categories/{category_id}", response_model=Envelope[dict], summary="Modifier une catégorie")
def admin_edit_category(category_id: int, body: CategoryIn, db: Session = DB, _: User = MANAGE):
    c = db.get(ProductCategory, category_id)
    if c is None:
        raise ApiError(404, "NOT_FOUND", "Catégorie introuvable")
    c = service.save_category(db, c, body)
    return ok({"id": c.id, "nom": c.name, "slug": c.slug, "parent_id": c.parent_id}, "Catégorie mise à jour")


@admin.delete("/produits/categories/{category_id}", response_model=Envelope[None], summary="Supprimer une catégorie vide")
def admin_delete_category(category_id: int, db: Session = DB, _: User = MANAGE):
    c = db.get(ProductCategory, category_id)
    if c is None:
        raise ApiError(404, "NOT_FOUND", "Catégorie introuvable")
    service.delete_category(db, c)
    return ok(message="Catégorie supprimée")


class OrderFilters:
    def __init__(self, statut: OrderStatut | None = None, q: str | None = Query(None, max_length=100),
                 du: date | None = None, au: date | None = None, produit_uuid: str | None = None):
        self.query = service.orders_query(statut, q, du, au, produit_uuid)


@admin.get("/commandes", response_model=Envelope[Page[OrderAdminOut]], summary="Commandes")
def admin_orders(f: OrderFilters = Depends(), page: PageParams = Depends(), db: Session = DB, _: User = MANAGE):
    rows, meta = paginate(db, f.query, page)
    return ok({"items": [service.order_out(db, o, admin=True) for o in rows], "meta": meta})


@admin.get("/commandes/stats", response_model=Envelope[dict], summary="Statistiques des commandes")
def admin_order_stats(db: Session = DB, _: User = MANAGE):
    return ok(service.stats(db))


@admin.get("/commandes/export.csv", summary="Export CSV", response_class=StreamingResponse,
           responses={200: {"content": {"text/csv": {}}}})
def admin_orders_export(f: OrderFilters = Depends(), db: Session = DB, _: User = MANAGE):
    return StreamingResponse(service.export_csv(db, f.query), media_type="text/csv; charset=utf-8",
                             headers={"Content-Disposition": f'attachment; filename="commandes_{now():%Y%m%d}.csv"'})


@admin.get("/commandes/{numero}", response_model=Envelope[dict], summary="Détail et historique")
def admin_order(numero: str, db: Session = DB, _: User = MANAGE):
    o = service.get_order(db, numero)
    return ok({**service.order_out(db, o, admin=True), "historique": service.history(db, o),
               "whatsapp_client": service.wa_link(o.customer_phone, f"Bonjour {o.customer_name}, concernant votre "
                                                                   f"commande {o.numero} ({o.product_title})…")})


@admin.post("/commandes/{numero}/statut", response_model=Envelope[OrderAdminOut],
            summary="en_attente → en_cours → livree (ou annulee)")
def admin_order_status(numero: str, body: StatusIn, background: BackgroundTasks, db: Session = DB,
                       client: ClientInfo = CLIENT, actor: User = MANAGE):
    o = service.set_order_status(db, service.get_order(db, numero), actor, body.statut, body.note, client, background)
    return ok(service.order_out(db, o, admin=True), "Statut mis à jour")


@admin.post("/commandes/{numero}/contacte", response_model=Envelope[OrderAdminOut],
            summary="Marquer le client comme contacté sur WhatsApp")
def admin_contacted(numero: str, db: Session = DB, actor: User = MANAGE):
    return ok(service.order_out(db, service.mark_contacted(db, service.get_order(db, numero), actor), admin=True))


@admin.post("/commandes/{numero}/notes", response_model=Envelope[None], summary="Ajouter une note")
def admin_order_note(numero: str, body: NoteIn, db: Session = DB, actor: User = MANAGE):
    service.add_order_note(db, service.get_order(db, numero), actor, body.texte)
    return ok(message="Note ajoutée")
