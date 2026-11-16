"""Administration du contenu : pages et sections (CMS), actualités, FAQ, témoignages, partenaires, chiffres clés,
réseaux sociaux, paramètres, messages, newsletter, statistiques de visites, tableau de bord."""

import csv
import io
from collections.abc import Callable
from datetime import date, datetime
from typing import Literal

from fastapi import APIRouter, BackgroundTasks, Depends, File, Query, UploadFile, status
from fastapi.responses import StreamingResponse
from pydantic import BaseModel, Field, model_validator
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core import audit
from app.core.client import ClientInfo, get_client
from app.core.database import Base, get_db
from app.core.helpers import like_pattern, now, slugify
from app.core.pagination import PageParams, paginate
from app.core.permissions import require_permission
from app.core.responses import ApiError, Envelope, ok
from app.core.uploads import save_image
from app.models import (
    Article,
    Parametre,
    ContactMessage,
    Faq,
    KeyFigure,
    NewsletterSubscriber,
    Page,
    PageSection,
    Partner,
    SocialLink,
    Testimonial,
    User,
)
from app.modules.content import service
from app.modules.content.sanitize import clean_html

DB = Depends(get_db)
CLIENT = Depends(get_client)
MANAGE = Depends(require_permission("content.manage"))
router = APIRouter(prefix="/admin/contenu", tags=["Admin · Contenu du site"])
dashboard_router = APIRouter(prefix="/admin", tags=["Admin · Tableau de bord"])


# ═════════════ Pages et sections ═════════════

class PageIn(BaseModel):
    titre: str = Field(min_length=1, max_length=255)
    slug: str | None = Field(default=None, max_length=150)
    parent_id: int | None = None
    menu_ordre: int = Field(default=0, ge=-1000, le=10000)
    afficher_menu: bool = True
    publiee: bool = True
    meta_description: str | None = Field(default=None, max_length=500)
    meta_keywords: str | None = Field(default=None, max_length=500)
    contenu: str | None = Field(default=None, max_length=500000)
    template: str | None = Field(default=None, max_length=60)
    icone: str | None = Field(default=None, max_length=60)


class SectionIn(BaseModel):
    type: Literal["hero", "texte", "image_texte", "tableau", "liste", "liste_card", "grille", "grille_card", "html",
                  "timeline", "cta"] = "texte"
    titre: str | None = Field(default=None, max_length=500)
    sous_titre: str | None = Field(default=None, max_length=1000)
    contenu: str | None = Field(default=None, max_length=500000)
    image_url: str | None = Field(default=None, max_length=500)
    image_droite: bool = False
    bouton_texte: str | None = Field(default=None, max_length=150)
    bouton_lien: str | None = Field(default=None, max_length=500)
    classe: str | None = Field(default=None, max_length=100)
    options_json: str | None = Field(default=None, max_length=200000)
    ordre: int = Field(default=0, ge=0, le=10000)
    publiee: bool = True


def _page(db: Session, page_id: int) -> Page:
    p = db.get(Page, page_id)
    if p is None or p.deleted_at is not None:
        raise ApiError(404, "NOT_FOUND", "Page introuvable")
    return p


def _apply_page(db: Session, p: Page, body: PageIn) -> Page:
    slug = slugify(body.slug or body.titre)[:150] or "page"
    if db.scalar(select(Page.id).where(Page.slug == slug, Page.id != (p.id or 0))):
        raise ApiError(409, "CONFLICT", "Une page utilise déjà cette adresse (slug)")
    if body.parent_id is not None and (body.parent_id == p.id or db.get(Page, body.parent_id) is None):
        raise ApiError(422, "VALIDATION_ERROR", "Page parente invalide")
    p.titre, p.slug, p.parent_id, p.menu_ordre = body.titre.strip(), slug, body.parent_id, body.menu_ordre
    p.afficher_menu, p.est_publiee = body.afficher_menu, body.publiee
    p.meta_description, p.meta_keywords, p.template, p.icone = body.meta_description, body.meta_keywords, body.template, body.icone
    p.contenu, p.updated_at = clean_html(body.contenu), now()
    return p


@router.get("/pages", response_model=Envelope[list[dict]], summary="Toutes les pages (brouillons compris)")
def list_pages(db: Session = DB, _: User = MANAGE):
    rows = db.scalars(select(Page).where(Page.deleted_at.is_(None)).order_by(Page.menu_ordre, Page.titre))
    return ok([{"id": p.id, "titre": p.titre, "slug": p.slug, "publiee": p.est_publiee, "parent_id": p.parent_id,
                "menu_ordre": p.menu_ordre, "modifiee_le": p.updated_at} for p in rows])


@router.get("/pages/{page_id}", response_model=Envelope[dict], summary="Page et toutes ses sections (sans traduction)")
def get_page(page_id: int, db: Session = DB, _: User = MANAGE):
    return ok(service.page_out(db, _page(db, page_id), admin=True))


@router.post("/pages", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict], summary="Nouvelle page")
def add_page(body: PageIn, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    p = _apply_page(db, Page(created_at=now()), body)
    db.add(p)
    db.flush()
    audit.activity(db, actor.id, "page_created", client, p.titre, module="contenu", item_id=p.id, item_name=p.slug)
    db.commit()
    return ok(service.page_out(db, p, admin=True), "Page créée")


@router.put("/pages/{page_id}", response_model=Envelope[dict], summary="Modifier une page")
def edit_page(page_id: int, body: PageIn, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    p = _apply_page(db, _page(db, page_id), body)
    audit.activity(db, actor.id, "page_updated", client, p.titre, module="contenu", item_id=p.id, item_name=p.slug)
    db.commit()
    return ok(service.page_out(db, p, admin=True), "Page mise à jour")


@router.delete("/pages/{page_id}", response_model=Envelope[None], summary="Supprimer une page (suppression douce)")
def delete_page(page_id: int, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    p = _page(db, page_id)
    p.deleted_at, p.est_publiee = now(), False
    audit.activity(db, actor.id, "page_deleted", client, p.titre, module="contenu", item_id=p.id, item_name=p.slug)
    db.commit()
    return ok(message="Page supprimée")


def _section_apply(s: PageSection, body: SectionIn) -> PageSection:
    s.type, s.titre, s.sous_titre, s.contenu = body.type, body.titre, body.sous_titre, clean_html(body.contenu)
    s.image_url, s.image_droite, s.bouton_texte, s.bouton_lien = body.image_url, body.image_droite, body.bouton_texte, body.bouton_lien
    s.classe, s.options_json, s.ordre, s.est_publiee, s.updated_at = body.classe, body.options_json, body.ordre, body.publiee, now()
    return s


@router.post("/pages/{page_id}/sections", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
             summary="Ajouter une section")
def add_section(page_id: int, body: SectionIn, db: Session = DB, _: User = MANAGE):
    p = _page(db, page_id)
    s = _section_apply(PageSection(page_id=p.id, created_at=now()), body)
    db.add(s)
    p.updated_at = now()
    db.commit()
    return ok(service.section_out(s), "Section ajoutée")


@router.put("/sections/{section_id}", response_model=Envelope[dict], summary="Modifier une section")
def edit_section(section_id: int, body: SectionIn, db: Session = DB, _: User = MANAGE):
    s = db.get(PageSection, section_id)
    if s is None:
        raise ApiError(404, "NOT_FOUND", "Section introuvable")
    _section_apply(s, body)
    db.commit()
    return ok(service.section_out(s), "Section mise à jour")


@router.delete("/sections/{section_id}", response_model=Envelope[None], summary="Supprimer une section")
def delete_section(section_id: int, db: Session = DB, _: User = MANAGE):
    s = db.get(PageSection, section_id)
    if s is None:
        raise ApiError(404, "NOT_FOUND", "Section introuvable")
    db.delete(s)
    db.commit()
    return ok(message="Section supprimée")


@router.put("/pages/{page_id}/sections/ordre", response_model=Envelope[dict], summary="Réordonner les sections")
def reorder(page_id: int, ids: list[int], db: Session = DB, _: User = MANAGE):
    p = _page(db, page_id)
    sections = {s.id: s for s in db.scalars(select(PageSection).where(PageSection.page_id == p.id))}
    if set(ids) != set(sections):
        raise ApiError(422, "VALIDATION_ERROR", "La liste doit contenir exactement toutes les sections de la page")
    for position, sid in enumerate(ids):
        sections[sid].ordre = position
    db.commit()
    return ok(service.page_out(db, p, admin=True), "Ordre enregistré")


# ═════════════ Actualités ═════════════

class ArticleIn(BaseModel):
    titre: str = Field(min_length=2, max_length=255)
    resume: str | None = Field(default=None, max_length=2000)
    contenu: str | None = Field(default=None, max_length=500000)
    auteur: str | None = Field(default=None, max_length=150)
    categorie: str | None = Field(default=None, max_length=100)
    tags: list[str] = Field(default_factory=list, max_length=20)
    a_la_une: bool = False
    publie: bool = False
    date_publication: datetime | None = Field(default=None, description="Dans le futur : publication programmée")


def _article(db: Session, article_id: int) -> Article:
    a = db.get(Article, article_id)
    if a is None or a.deleted_at is not None:
        raise ApiError(404, "NOT_FOUND", "Article introuvable")
    return a


def _apply_article(db: Session, a: Article, body: ArticleIn) -> Article:
    from app.modules.consultations.slots import to_local_naive

    slug = slugify(body.titre)[:200] or "article"
    if db.scalar(select(Article.id).where(Article.slug == slug, Article.id != (a.id or 0))):
        slug = f"{slug}-{now():%Y%m%d%H%M}"
    a.titre, a.slug, a.resume, a.contenu = body.titre.strip(), slug, body.resume, clean_html(body.contenu)
    a.auteur, a.categorie, a.tags = body.auteur, body.categorie, ", ".join(t.strip() for t in body.tags if t.strip()) or None
    a.est_en_avant, a.est_publie = body.a_la_une, body.publie
    a.date_publication = to_local_naive(body.date_publication) if body.date_publication else (now() if body.publie else None)
    a.updated_at = now()
    return a


def _article_admin(a: Article) -> dict:
    scheduled = a.est_publie and a.date_publication and a.date_publication > now()
    return {**service.article_out(a, detail=True), "publie": a.est_publie,
            "etat": "programme" if scheduled else ("publie" if a.est_publie else "brouillon")}


@router.get("/actualites", response_model=Envelope[dict], summary="Tous les articles (brouillons et programmés)")
def list_articles(q: str | None = Query(None, max_length=100), page: PageParams = Depends(), db: Session = DB,
                  _: User = MANAGE):
    query = select(Article).where(Article.deleted_at.is_(None))
    if q:
        query = query.where(Article.titre.like(like_pattern(q)))
    rows, meta = paginate(db, query.order_by(Article.created_at.desc()), page)
    return ok({"items": [_article_admin(a) for a in rows], "meta": meta})


@router.get("/actualites/{article_id}", response_model=Envelope[dict], summary="Un article (formulaire de modification)")
def get_article(article_id: int, db: Session = DB, _: User = MANAGE):
    return ok(_article_admin(_article(db, article_id)))


@router.post("/actualites", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict], summary="Nouvel article")
def add_article(body: ArticleIn, db: Session = DB, _: User = MANAGE):
    a = _apply_article(db, Article(created_at=now(), vues=0), body)
    db.add(a)
    db.commit()
    return ok(_article_admin(a), "Article créé")


@router.put("/actualites/{article_id}", response_model=Envelope[dict], summary="Modifier un article")
def edit_article(article_id: int, body: ArticleIn, db: Session = DB, _: User = MANAGE):
    a = _apply_article(db, _article(db, article_id), body)
    db.commit()
    return ok(_article_admin(a), "Article mis à jour")


@router.post("/actualites/{article_id}/image", response_model=Envelope[dict], summary="Image principale")
async def article_image(article_id: int, image: UploadFile = File(...), db: Session = DB, _: User = MANAGE):
    a = _article(db, article_id)
    name = save_image(await image.read(5 * 1024 * 1024 + 1), "Actualites", a.id, 5 * 1024 * 1024, 1600)
    a.image_principale = f"attachments/Actualites/{name}"
    db.commit()
    return ok(_article_admin(a), "Image mise à jour")


@router.delete("/actualites/{article_id}", response_model=Envelope[None], summary="Supprimer un article")
def delete_article(article_id: int, db: Session = DB, _: User = MANAGE):
    a = _article(db, article_id)
    a.deleted_at, a.est_publie = now(), False
    db.commit()
    return ok(message="Article supprimé")


# ═════════════ CRUD simples : FAQ, témoignages, partenaires, chiffres clés, réseaux sociaux ═════════════

def crud(path: str, model: type[Base], schema: type[BaseModel], to_db: dict[str, str], to_out: Callable,
         order_by, soft_delete: bool = False, image_field: str | None = None, image_folder: str | None = None,
         label: str = "Élément"):
    """Génère liste / création / modification / suppression (et envoi d'image) pour une ressource simple."""

    def _get(db: Session, item_id: int):
        obj = db.get(model, item_id)
        if obj is None or (soft_delete and getattr(obj, "deleted_at", None) is not None):
            raise ApiError(404, "NOT_FOUND", f"{label} introuvable")
        return obj

    def _apply(obj, body: BaseModel):
        for key, column in to_db.items():
            value = getattr(body, key)
            setattr(obj, column, clean_html(value) if key in ("reponse", "description") and value else value)
        if hasattr(obj, "updated_at"):
            obj.updated_at = now()
        return obj

    @router.get(f"/{path}", response_model=Envelope[list[dict]], summary=f"{label}s (tous)", name=f"list_{path}")
    def _list(db: Session = DB, _: User = MANAGE):
        query = select(model)
        if soft_delete:
            query = query.where(model.deleted_at.is_(None))
        return ok([to_out(o) for o in db.scalars(query.order_by(*order_by))])

    @router.post(f"/{path}", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
                 summary=f"Créer : {label.lower()}", name=f"add_{path}")
    def _add(body: schema, db: Session = DB, _: User = MANAGE):  # type: ignore[valid-type]
        obj = _apply(model(), body)
        if hasattr(obj, "created_at"):
            obj.created_at = now()
        db.add(obj)
        db.commit()
        return ok(to_out(obj), f"{label} créé")

    @router.put(f"/{path}/{{item_id}}", response_model=Envelope[dict], summary=f"Modifier : {label.lower()}",
                name=f"edit_{path}")
    def _edit(item_id: int, body: schema, db: Session = DB, _: User = MANAGE):  # type: ignore[valid-type]
        obj = _apply(_get(db, item_id), body)
        db.commit()
        return ok(to_out(obj), f"{label} mis à jour")

    @router.delete(f"/{path}/{{item_id}}", response_model=Envelope[None], summary=f"Supprimer : {label.lower()}",
                   name=f"delete_{path}")
    def _delete(item_id: int, db: Session = DB, _: User = MANAGE):
        obj = _get(db, item_id)
        if soft_delete:
            obj.deleted_at = now()
        else:
            db.delete(obj)
        db.commit()
        return ok(message=f"{label} supprimé")

    if image_field:
        @router.post(f"/{path}/{{item_id}}/image", response_model=Envelope[dict], summary=f"Image : {label.lower()}",
                     name=f"image_{path}")
        async def _image(item_id: int, image: UploadFile = File(...), db: Session = DB, _: User = MANAGE):
            obj = _get(db, item_id)
            name = save_image(await image.read(5 * 1024 * 1024 + 1), image_folder, item_id, 5 * 1024 * 1024, 800)
            setattr(obj, image_field, f"attachments/{image_folder}/{name}")
            db.commit()
            return ok(to_out(obj), "Image mise à jour")


class FaqIn(BaseModel):
    question: str = Field(min_length=3, max_length=2000)
    reponse: str = Field(min_length=1, max_length=20000)
    categorie: str | None = Field(default=None, max_length=100)
    ordre: int = Field(default=0, ge=0, le=10000)
    publiee: bool = True


class TestimonialIn(BaseModel):
    titre: str = Field(min_length=2, max_length=255)
    video_url: str = Field(min_length=5, max_length=500)  # un témoignage est une vidéo (colonne obligatoire)
    approuve: bool = False


class PartnerIn(BaseModel):
    nom: str = Field(min_length=2, max_length=255)
    type: Literal["outgrower", "client_export", "fournisseur", "institutionnel", "scientifique", "financier", "regulateur"]
    niveau: Literal["strategique", "technique", "commercial", "academique"] | None = None
    description: str | None = Field(default=None, max_length=5000)
    pays: str | None = Field(default=None, max_length=100)
    site_web: str | None = Field(default=None, max_length=255)
    date_debut: date | None = None
    actif: bool = True


class KeyFigureIn(BaseModel):
    etiquette: str = Field(min_length=1, max_length=255)
    valeur: str = Field(min_length=1, max_length=100)
    unite: str | None = Field(default=None, max_length=50)
    description: str | None = Field(default=None, max_length=2000)
    icone: str | None = Field(default=None, max_length=100)
    ordre: int = Field(default=0, ge=0, le=10000)
    annee: int | None = Field(default=None, ge=2000, le=2100)


class SocialIn(BaseModel):
    plateforme: str = Field(min_length=2, max_length=50)
    libelle: str | None = Field(default=None, max_length=100)
    url: str = Field(min_length=8, max_length=500, pattern=r"^https://")
    icone: str | None = Field(default=None, max_length=100)
    ordre: int = Field(default=0, ge=0, le=10000)
    actif: bool = True
    nouvel_onglet: bool = True

    @model_validator(mode="after")
    def _default_label(self):
        # label et icon_name sont obligatoires en base : on reprend la plateforme s'ils ne sont pas fournis
        self.plateforme = self.plateforme.strip().lower()
        self.libelle = self.libelle or self.plateforme.capitalize()
        self.icone = self.icone or self.plateforme
        return self


crud("faq", Faq, FaqIn, {"question": "question", "reponse": "reponse", "categorie": "categorie", "ordre": "ordre",
                         "publiee": "est_publiee"},
     lambda f: {"id": f.id, "question": f.question, "reponse": f.reponse, "categorie": f.categorie, "ordre": f.ordre,
                "publiee": f.est_publiee}, (Faq.categorie, Faq.ordre), label="Question")
crud("temoignages", Testimonial, TestimonialIn, {"titre": "titre", "video_url": "video_url", "approuve": "est_approuve"},
     lambda t: {"id": t.id, "titre": t.titre, "video_url": t.video_url, "miniature": service.image(t.miniature),
                "approuve": t.est_approuve}, (Testimonial.id.desc(),), image_field="miniature",
     image_folder="Testimony", label="Témoignage")
crud("partenaires", Partner, PartnerIn, {"nom": "nom", "type": "type_partenaire", "niveau": "niveau_partenariat",
                                         "description": "description", "pays": "pays", "site_web": "site_web",
                                         "date_debut": "date_debut", "actif": "est_actif"},
     lambda p: {"id": p.id, "nom": p.nom, "type": p.type_partenaire, "niveau": p.niveau_partenariat,
                "description": p.description, "pays": p.pays, "site_web": p.site_web, "date_debut": p.date_debut,
                "actif": p.est_actif, "logo": service.image(p.logo_url)}, (Partner.nom,), soft_delete=True,
     image_field="logo_url", image_folder="Partener", label="Partenaire")
crud("chiffres-cles", KeyFigure, KeyFigureIn, {"etiquette": "etiquette", "valeur": "valeur", "unite": "unite",
                                               "description": "description", "icone": "icone", "ordre": "ordre",
                                               "annee": "annee_vision"},
     lambda k: {"id": k.id, "etiquette": k.etiquette, "valeur": k.valeur, "unite": k.unite, "description": k.description,
                "icone": k.icone, "ordre": k.ordre, "annee": k.annee_vision}, (KeyFigure.ordre,), label="Chiffre clé")
crud("reseaux-sociaux", SocialLink, SocialIn, {"plateforme": "platform", "libelle": "label", "url": "url",
                                               "icone": "icon_name", "ordre": "display_order", "actif": "is_active",
                                               "nouvel_onglet": "target_blank"},
     lambda s: {"id": s.id, "plateforme": s.platform, "libelle": s.label, "url": s.url, "icone": s.icon_name,
                "ordre": s.display_order, "actif": s.is_active, "nouvel_onglet": s.target_blank},
     (SocialLink.display_order,), label="Réseau social")


# ═════════════ Paramètres du site ═════════════

class SettingsIn(BaseModel):
    valeurs: dict[str, str | int | float | bool | None]


@router.get("/parametres", response_model=Envelope[list[dict]], summary="Paramètres (sans les secrets)")
def get_settings_(db: Session = DB, _: User = MANAGE):
    return ok(service.admin_settings(db))


@router.put("/parametres", response_model=Envelope[list[dict]], summary="Modifier des paramètres (contrôle par type)")
def put_settings(body: SettingsIn, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    service.update_settings(db, body.valeurs)
    audit.activity(db, actor.id, "site_settings", client, ", ".join(body.valeurs), module="contenu")
    db.commit()
    return ok(service.admin_settings(db), "Paramètres enregistrés")


@router.post("/parametres/{cle}/image", response_model=Envelope[list[dict]], summary="Image d'un paramètre (logo…)")
async def setting_image(cle: str, image: UploadFile = File(...), db: Session = DB, _: User = MANAGE):
    c = db.scalar(select(Parametre).where(Parametre.cle == cle))
    if c is None or c.type != "image":
        raise ApiError(422, "VALIDATION_ERROR", "Ce paramètre n'est pas une image")
    c.valeur = save_image(await image.read(5 * 1024 * 1024 + 1), "Configurations", c.id, 5 * 1024 * 1024, 2000)
    db.commit()
    return ok(service.admin_settings(db), "Image mise à jour")


# ═════════════ Messages de contact ═════════════

class ReplyIn(BaseModel):
    texte: str = Field(min_length=2, max_length=10000)


def _message(db: Session, message_id: int) -> ContactMessage:
    m = db.get(ContactMessage, message_id)
    if m is None:
        raise ApiError(404, "NOT_FOUND", "Message introuvable")
    return m


@router.get("/messages", response_model=Envelope[dict], summary="Boîte de réception")
def messages(non_lus: bool = False, page: PageParams = Depends(), db: Session = DB, _: User = MANAGE):
    query = select(ContactMessage)
    if non_lus:
        query = query.where(ContactMessage.lu.is_(False))
    rows, meta = paginate(db, query.order_by(ContactMessage.created_at.desc()), page)
    return ok({"items": [service.contact_out(m) for m in rows], "meta": meta})


@router.get("/messages/{message_id}", response_model=Envelope[dict], summary="Lire un message (le marque comme lu)")
def read_message(message_id: int, db: Session = DB, _: User = MANAGE):
    m = _message(db, message_id)
    m.lu = True
    db.commit()
    return ok(service.contact_out(m))


@router.post("/messages/{message_id}/repondre", response_model=Envelope[dict], summary="Répondre par email")
def reply(message_id: int, body: ReplyIn, background: BackgroundTasks, db: Session = DB, actor: User = MANAGE):
    return ok(service.contact_out(service.reply_contact(db, _message(db, message_id), actor, body.texte, background)),
              "Réponse envoyée")


@router.delete("/messages/{message_id}", response_model=Envelope[None], summary="Supprimer un message")
def delete_message(message_id: int, db: Session = DB, _: User = MANAGE):
    db.delete(_message(db, message_id))
    db.commit()
    return ok(message="Message supprimé")


# ═════════════ Newsletter ═════════════

NewsletterStatut = Literal["a_reconfirmer", "en_attente", "confirme", "desinscrit"]


def _subscriber_out(s: NewsletterSubscriber) -> dict:
    return {"id": s.id, "email": s.email, "telephone": s.telephone, "statut": s.statut, "langue": s.langue,
            "inscrit_le": s.date_inscription, "confirme_le": s.confirme_at, "desinscrit_le": s.desinscrit_at}


@router.get("/newsletter", response_model=Envelope[dict], summary="Abonnés (filtre par statut)")
def subscribers(statut: NewsletterStatut | None = None, page: PageParams = Depends(), db: Session = DB, _: User = MANAGE):
    query = select(NewsletterSubscriber)
    if statut:
        query = query.where(NewsletterSubscriber.statut == statut)
    rows, meta = paginate(db, query.order_by(NewsletterSubscriber.date_inscription.desc()), page)
    return ok({"items": [_subscriber_out(s) for s in rows], "meta": meta})


@router.get("/newsletter/export.csv", summary="Export des abonnés CONFIRMÉS", response_class=StreamingResponse,
            responses={200: {"content": {"text/csv": {}}}})
def export_subscribers(db: Session = DB, _: User = MANAGE):
    buffer = io.StringIO()
    writer = csv.writer(buffer, delimiter=";")
    writer.writerow(["email", "telephone", "langue", "confirme_le"])
    for s in db.scalars(select(NewsletterSubscriber).where(NewsletterSubscriber.statut == "confirme")):
        writer.writerow([s.email, s.telephone, s.langue, s.confirme_at])
    return StreamingResponse(iter(["﻿" + buffer.getvalue()]), media_type="text/csv; charset=utf-8",
                             headers={"Content-Disposition": f'attachment; filename="newsletter_{now():%Y%m%d}.csv"'})


@router.post("/newsletter/reconfirmation", response_model=Envelope[dict],
             summary="Envoyer l'email de reconfirmation aux anciens inscrits (action manuelle)")
def reconfirm(background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    count = service.send_reconfirmation(db, background)
    audit.activity(db, actor.id, "newsletter_reconfirmation", client, f"{count} email(s)", module="contenu")
    db.commit()
    return ok({"emails": count}, f"{count} email(s) de reconfirmation envoyé(s)")


@router.delete("/newsletter/{subscriber_id}", response_model=Envelope[None], summary="Supprimer un abonné")
def delete_subscriber(subscriber_id: int, db: Session = DB, _: User = MANAGE):
    s = db.get(NewsletterSubscriber, subscriber_id)
    if s is None:
        raise ApiError(404, "NOT_FOUND", "Abonné introuvable")
    db.delete(s)
    db.commit()
    return ok(message="Abonné supprimé")


# ═════════════ Statistiques et tableau de bord ═════════════

@router.get("/visites", response_model=Envelope[dict], summary="Visites (anonymes) : par jour, pages, pays, appareils")
def visits(jours: int = Query(30, ge=1, le=365), db: Session = DB, _: User = MANAGE):
    return ok(service.visit_stats(db, jours))


@dashboard_router.get("/tableau-de-bord", response_model=Envelope[dict], summary="Vue d'ensemble de tous les modules")
def dashboard(db: Session = DB, _: User = MANAGE):
    return ok(service.dashboard(db))
