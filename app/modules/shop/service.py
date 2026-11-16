"""Catalogue et commandes.

Pas de panier : sur la fiche d'un produit, le client remplit ses coordonnées ; la commande est enregistrée
(suivi par l'admin) puis l'API renvoie un lien WhatsApp vers le service des ventes, message déjà rempli.
La suite (paiement, livraison) se passe sur WhatsApp. Aucune tâche planifiée, aucun email promotionnel."""

import csv
import io
import re
import secrets
import uuid
from collections.abc import Iterator
from datetime import timedelta
from decimal import Decimal
from urllib.parse import quote

from fastapi import BackgroundTasks
from sqlalchemy import func, or_, select
from sqlalchemy.orm import Session

from app.core import audit
from app.core.client import ClientInfo
from app.core.config import get_settings
from app.core.helpers import like_pattern, minutes_ago, now, phone_or_422, plain_excerpt, slugify
from app.core.mailer import deliver_pending, queue_email, simple_email
from app.core.phone import InvalidPhone, normalize_phone, sql_clean_phone
from app.core.responses import ApiError
from app.core.uploads import delete_file, public_url, save_image
from app.models import Parametre, OrderHistory, OrderRequest, Pays, Product, ProductCategory, ProductImage, User
from app.models.shop import ORDER_STATUS, ORDER_STATUS_DB

PRODUCTS_FOLDER = "Products"
CATEGORIES_FOLDER = "Categories"
DEFAULT_SALES_WHATSAPP = "+257 79 666 439"
HEALTH_NOTICE = ("Ce produit ne remplace pas un avis ni un traitement médical. Consultez un expert avant usage, "
                 "en particulier en cas de grossesse, de maladie chronique ou de traitement en cours.")
# Transitions autorisées (libellés de l'API)
TRANSITIONS = {"en_attente": {"en_cours", "annulee"}, "en_cours": {"livree", "annulee"}}


# ───────────────────────── outils ─────────────────────────

def money(value: Decimal | None, currency: str) -> str:
    if value is None:
        return "—"
    if currency == "BIF":
        return f"{int(value):,}".replace(",", ".") + " BIF"
    return f"{value:.2f} USD"


def legacy_price(p: Product) -> str:
    """Texte « 7$ = 21000fbu » tenu à jour pour l'affichage du site PHP."""
    parts = []
    if p.prix_usd is not None:
        parts.append(f"{p.prix_usd.normalize():f}$")
    if p.prix_bif is not None:
        parts.append(f"{int(p.prix_bif)}fbu")
    return " = ".join(parts)[:25]


def sales_number(db: Session) -> str:
    for raw in (db.scalar(select(Parametre.valeur).where(Parametre.cle == "contact_whatsapp")),
                DEFAULT_SALES_WHATSAPP):
        if raw:
            try:
                return normalize_phone(raw)
            except InvalidPhone:
                continue
    return normalize_phone(DEFAULT_SALES_WHATSAPP)


def wa_link(number: str, message: str) -> dict:
    return {"numero": number, "message": message,
            "url": f"https://wa.me/{re.sub(r'[^0-9]', '', number)}?text={quote(message)}"}


def _visible():
    return select(Product).where(Product.is_active.is_(True), Product.deleted_at.is_(None))


def category_out(c: ProductCategory | None) -> dict | None:
    if c is None:
        return None
    return {"id": c.id, "nom": c.name, "slug": c.slug, "code": c.code}


def product_out(p: Product, detail: bool = False) -> dict:
    in_stock = p.stock_quantite is None or p.stock_quantite > 0
    data = {
        "uuid": p.uuid, "slug": p.slug, "titre": p.title, "image": public_url(PRODUCTS_FOLDER, p.main_image, None),
        "prix_bif": p.prix_bif, "prix_usd": p.prix_usd, "categorie": category_out(p.category),
        "vedette": bool(p.in_vedette), "en_stock": in_stock, "produit_sante": bool(p.est_produit_sante),
        "extrait": plain_excerpt(p.description, 100),
        "nouveau": bool(p.created_at and p.created_at > now() - timedelta(days=30)),  # moins de 30 jours
        "created_at": p.created_at,
    }
    if detail:
        data.update({
            "description": p.description,
            "galerie": [{"id": i.id, "url": public_url(PRODUCTS_FOLDER, i.fichier, None)} for i in p.images],
            "stock": p.stock_quantite,
            "avertissement_sante": HEALTH_NOTICE if p.est_produit_sante else None,
            "proposer_consultation": bool(p.est_produit_sante),
        })
    return data


# ───────────────────────── catalogue public ─────────────────────────

def categories_tree(db: Session) -> list[dict]:
    counts = dict(db.execute(select(Product.category_id, func.count()).where(
        Product.is_active.is_(True), Product.deleted_at.is_(None)).group_by(Product.category_id)).all())
    rows = list(db.scalars(select(ProductCategory).where(ProductCategory.is_active.is_(True))
                           .order_by(ProductCategory.position, ProductCategory.name)))
    items = {c.id: {"id": c.id, "nom": c.name, "slug": c.slug, "code": c.code, "description": c.description,
                    "image": public_url(CATEGORIES_FOLDER, c.image, None), "produits": counts.get(c.id, 0),
                    "sous_categories": []} for c in rows}
    roots = []
    for c in rows:
        if c.parent_id and c.parent_id in items:
            items[c.parent_id]["sous_categories"].append(items[c.id])
        else:
            roots.append(items[c.id])
    return roots


def catalog_query(db: Session, q, categorie, vedette, prix_min, prix_max, devise, tri):
    query = _visible()
    if q and q.strip():
        p = like_pattern(q.strip())
        query = query.where(or_(Product.title.like(p), Product.description.like(p)))
    if categorie:
        cat = db.scalar(select(ProductCategory).where(ProductCategory.slug == categorie))
        if cat is None:
            return query.where(Product.id == -1)
        children = list(db.scalars(select(ProductCategory.id).where(ProductCategory.parent_id == cat.id)))
        query = query.where(Product.category_id.in_([cat.id, *children]))
    if vedette:
        query = query.where(Product.in_vedette.is_(True))
    column = Product.prix_usd if devise == "USD" else Product.prix_bif
    if prix_min is not None:
        query = query.where(column >= prix_min)
    if prix_max is not None:
        query = query.where(column <= prix_max)
    order = {"recent": Product.created_at.desc(), "prix_asc": column.asc(), "prix_desc": column.desc(),
             "populaire": Product.price_request_count.desc(), "nom": Product.title.asc()}[tri]
    return query.order_by(Product.in_vedette.desc() if tri == "recent" else order, order, Product.id.desc())


def get_public(db: Session, slug: str) -> Product:
    p = db.scalar(_visible().where(Product.slug == slug))
    if p is None:
        raise ApiError(404, "NOT_FOUND", "Produit introuvable")
    return p


def related(db: Session, p: Product, limit: int = 4) -> list[Product]:
    if not p.category_id:
        return []
    return list(db.scalars(_visible().where(Product.category_id == p.category_id, Product.id != p.id)
                           .order_by(Product.in_vedette.desc(), Product.price_request_count.desc()).limit(limit)))


# ───────────────────────── commande via WhatsApp ─────────────────────────

def _order_message(o: OrderRequest, p: Product) -> str:
    lines = [
        "NOUVELLE COMMANDE - NUFOTEC", "",
        f"• N° de commande : {o.numero}",
        f"• Produit : {o.product_title}",
        f"• Quantité : {o.quantite}",
        f"• Prix unitaire : {money(o.prix_unitaire, o.devise or 'BIF')}",
        f"• Total : {money(o.total, o.devise or 'BIF')}", "",
        f"• Nom : {o.customer_name}",
        f"• Téléphone : {o.customer_phone}",
    ]
    if o.customer_email:
        lines.append(f"• Email : {o.customer_email}")
    lines += [f"• Adresse : {o.customer_address}, {o.customer_city}, {o.customer_country}"]
    if o.customer_notes:
        lines.append(f"• Remarques : {o.customer_notes}")
    lines += ["", "Merci de me confirmer la disponibilité, les frais de livraison et le mode de paiement."]
    return "\n".join(lines)


def place_order(db: Session, p: Product, data, user: User | None, client: ClientInfo,
                background: BackgroundTasks) -> tuple[OrderRequest | None, dict]:
    sales = sales_number(db)
    if data.site_web:  # robot : on fait semblant
        return None, wa_link(sales, "")
    if client.ip and (db.scalar(select(func.count()).select_from(OrderRequest).where(
            OrderRequest.ip_address == client.ip, OrderRequest.created_at > minutes_ago(60))) or 0) >= 10:
        raise ApiError(429, "TOO_MANY_REQUESTS", "Trop de commandes depuis ce réseau. Réessayez plus tard.",
                       {"retry_after_seconds": 3600}, {"Retry-After": "3600"})
    pays = db.get(Pays, data.pays_id)
    if pays is None:
        raise ApiError(422, "VALIDATION_ERROR", "Données invalides", [{"field": "pays_id", "message": "Pays inconnu"}])
    if p.stock_quantite is not None and p.stock_quantite < data.quantite:
        raise ApiError(409, "OUT_OF_STOCK", "Stock insuffisant pour cette quantité", {"disponible": max(p.stock_quantite, 0)})
    burundi = data.pays_id == get_settings().burundi_pays_id
    unit, currency = (p.prix_bif, "BIF") if burundi else (p.prix_usd, "USD")
    if unit is None:  # prix non renseigné dans cette devise : on prend l'autre
        unit, currency = (p.prix_usd, "USD") if burundi else (p.prix_bif, "BIF")
    phone = phone_or_422(data.telephone)
    order = OrderRequest(
        numero=f"CMD-{now():%Y%m%d}-{secrets.token_hex(3).upper()}", product_id=p.id, user_id=user.id if user else None,
        customer_name=data.nom_complet, customer_phone=phone, customer_email=data.email,
        customer_country=pays.pays or "", customer_city=data.ville, customer_address=data.adresse,
        customer_notes=data.remarques, product_title=p.title, product_price=p.price or legacy_price(p),
        quantite=data.quantite, prix_unitaire=unit, devise=currency if unit is not None else None,
        total=unit * data.quantite if unit is not None else None, order_status="pending", whatsapp_sent=False,
        ip_address=client.ip, user_agent=client.user_agent or None, created_at=now(), updated_at=now())
    db.add(order)
    db.flush()
    db.add(OrderHistory(order_id=order.id, to_status="en_attente", note="Commande passée depuis le site / l'app",
                        created_at=now()))
    if data.email:
        text, html = simple_email(data.nom_complet.split(" ")[0], "Commande enregistrée",
                                  [f"Votre commande {order.numero} ({data.quantite} × {p.title}) est enregistrée.",
                                   "Notre équipe vous répond sur WhatsApp pour la disponibilité, la livraison et le paiement."])
        queue_email(db, data.email, f"Nufotec – Commande {order.numero}", text, html)
        background.add_task(deliver_pending)
    db.commit()
    return order, wa_link(sales, _order_message(order, p))


def price_request(db: Session, p: Product) -> dict:
    p.price_request_count = (p.price_request_count or 0) + 1
    db.commit()
    return wa_link(sales_number(db), f"Bonjour, je souhaite connaître le prix actualisé et la disponibilité de : {p.title}.")


def order_out(db: Session, o: OrderRequest, admin: bool = False) -> dict:
    data = {
        "numero": o.numero, "statut": ORDER_STATUS.get(o.order_status, o.order_status), "produit": o.product_title,
        "produit_slug": o.product.slug if o.product else None, "quantite": o.quantite,
        "prix_unitaire": o.prix_unitaire, "devise": o.devise, "total": o.total,
        "prix_affiche": o.product_price, "ville": o.customer_city, "pays": o.customer_country,
        "created_at": o.created_at,
    }
    if admin:
        data.update({"client": o.customer_name, "telephone": o.customer_phone, "email": o.customer_email,
                     "adresse": o.customer_address, "remarques": o.customer_notes,
                     "whatsapp_contacte": bool(o.whatsapp_sent), "whatsapp_contacte_le": o.whatsapp_sent_at})
    return data


def track(db: Session, numero: str, telephone: str) -> OrderRequest:
    """Suivi sans compte : le numéro de commande ET le téléphone doivent correspondre."""
    try:
        phone = normalize_phone(telephone)
    except InvalidPhone:
        phone = None
    o = db.scalar(select(OrderRequest).where(OrderRequest.numero == numero))
    if o is None or phone is None or re.sub(r"[^0-9+]", "", o.customer_phone or "") != phone:
        raise ApiError(404, "NOT_FOUND", "Commande introuvable (vérifiez le numéro et le téléphone)")
    return o


def my_orders_query(user: User):
    """Commandes passées connecté, ou avec le téléphone du profil (anciennes commandes sans compte)."""
    try:
        phone = normalize_phone(user.telephone) if user.telephone else None
    except InvalidPhone:
        phone = None
    condition = OrderRequest.user_id == user.id
    if phone:
        condition = or_(condition, sql_clean_phone(OrderRequest.customer_phone) == phone)
    return select(OrderRequest).where(condition).order_by(OrderRequest.created_at.desc())


# ───────────────────────── administration : produits ─────────────────────────

def get_product(db: Session, product_uuid: str) -> Product:
    p = db.scalar(select(Product).where(Product.uuid == product_uuid))
    if p is None:
        raise ApiError(404, "NOT_FOUND", "Produit introuvable")
    return p


def _unique_slug(db: Session, title: str, exclude_id: int | None = None) -> str:
    base = slugify(title)[:200] or "produit"
    slug, n = base, 2
    while db.scalar(select(Product.id).where(Product.slug == slug, Product.id != (exclude_id or 0))):
        slug, n = f"{base}-{n}", n + 1
    return slug


def _check_category(db: Session, category_id: int | None) -> None:
    if category_id is not None and not db.get(ProductCategory, category_id):
        raise ApiError(422, "VALIDATION_ERROR", "Données invalides", [{"field": "categorie_id", "message": "Catégorie inconnue"}])


def create_product(db: Session, admin: User, data, client: ClientInfo) -> Product:
    _check_category(db, data.categorie_id)
    p = Product(uuid=str(uuid.uuid4()), category_id=data.categorie_id, main_image="", title=data.titre.strip(),
                slug=_unique_slug(db, data.titre), description=data.description, prix_usd=data.prix_usd,
                prix_bif=data.prix_bif, stock_quantite=data.stock, est_produit_sante=data.produit_sante,
                is_active=data.publie, in_vedette=data.vedette, price_request_count=0, created_at=now(),
                updated_at=now())
    p.price = legacy_price(p)
    db.add(p)
    db.flush()
    audit.activity(db, admin.id, "product_created", client, p.title, module="produits", item_id=p.id, item_name=p.title)
    db.commit()
    db.refresh(p)
    return p


def update_product(db: Session, admin: User, p: Product, changes: dict, client: ClientInfo) -> Product:
    mapping = {"titre": "title", "description": "description", "prix_usd": "prix_usd", "prix_bif": "prix_bif",
               "stock": "stock_quantite", "produit_sante": "est_produit_sante", "publie": "is_active",
               "vedette": "in_vedette", "categorie_id": "category_id"}
    if "categorie_id" in changes:
        _check_category(db, changes["categorie_id"])
    if changes.get("titre"):
        changes["titre"] = changes["titre"].strip()
        p.slug = _unique_slug(db, changes["titre"], p.id)
    for key, value in changes.items():
        setattr(p, mapping[key], value)
    p.price = legacy_price(p)
    p.updated_at = now()
    audit.activity(db, admin.id, "product_updated", client, p.title, module="produits", item_id=p.id, item_name=p.title,
                   new_data=changes)
    db.commit()
    db.refresh(p)
    return p


def set_main_image(db: Session, p: Product, raw: bytes) -> Product:
    old = p.main_image
    p.main_image = save_image(raw, PRODUCTS_FOLDER, p.id, 5 * 1024 * 1024, 1200)
    p.updated_at = now()
    db.commit()
    delete_file(PRODUCTS_FOLDER, old, keep=())
    return p


def add_gallery_image(db: Session, p: Product, raw: bytes) -> Product:
    if len(p.images) >= 10:
        raise ApiError(409, "TOO_MANY_IMAGES", "10 images au maximum par produit")
    name = save_image(raw, PRODUCTS_FOLDER, p.id, 5 * 1024 * 1024, 1600)
    p.images.append(ProductImage(fichier=name, position=len(p.images), created_at=now()))
    db.commit()
    return p


def delete_gallery_image(db: Session, p: Product, image_id: int) -> Product:
    image = next((i for i in p.images if i.id == image_id), None)
    if image is None:
        raise ApiError(404, "NOT_FOUND", "Image introuvable")
    name = image.fichier
    p.images.remove(image)
    db.commit()
    delete_file(PRODUCTS_FOLDER, name, keep=())
    return p


def soft_delete(db: Session, admin: User, p: Product, client: ClientInfo) -> None:
    """Suppression douce : les commandes passées gardent leur historique."""
    p.deleted_at, p.is_active, p.updated_at = now(), False, now()
    audit.activity(db, admin.id, "product_deleted", client, p.title, module="produits", item_id=p.id, item_name=p.title)
    db.commit()


# ───────────────────────── administration : catégories ─────────────────────────

def save_category(db: Session, c: ProductCategory | None, data) -> ProductCategory:
    if data.parent_id is not None:
        parent = db.get(ProductCategory, data.parent_id)
        if parent is None:
            raise ApiError(422, "VALIDATION_ERROR", "Catégorie parente inconnue")
        if c is not None and (parent.id == c.id or parent.parent_id == c.id):
            raise ApiError(422, "VALIDATION_ERROR", "Une catégorie ne peut pas être sa propre parente")
    c = c or ProductCategory()
    slug = slugify(data.nom)[:110] or "categorie"
    clash = db.scalar(select(ProductCategory.id).where(ProductCategory.slug == slug, ProductCategory.id != (c.id or 0)))
    if clash:
        raise ApiError(409, "CONFLICT", "Une catégorie porte déjà ce nom")
    c.name, c.slug, c.parent_id, c.code = data.nom.strip(), slug, data.parent_id, data.code
    c.description, c.position, c.is_active = data.description, data.position, data.actif
    db.add(c)
    db.commit()
    return c


def delete_category(db: Session, c: ProductCategory) -> None:
    used = db.scalar(select(func.count()).select_from(Product).where(Product.category_id == c.id,
                                                                       Product.deleted_at.is_(None))) or 0
    if used:
        raise ApiError(409, "CATEGORY_IN_USE", f"{used} produit(s) utilisent cette catégorie : déplacez-les d'abord")
    if db.scalar(select(ProductCategory.id).where(ProductCategory.parent_id == c.id)):
        raise ApiError(409, "CATEGORY_IN_USE", "Cette catégorie a des sous-catégories")
    db.delete(c)
    db.commit()


# ───────────────────────── administration : commandes ─────────────────────────

def get_order(db: Session, numero: str) -> OrderRequest:
    o = db.scalar(select(OrderRequest).where(OrderRequest.numero == numero))
    if o is None:
        raise ApiError(404, "NOT_FOUND", "Commande introuvable")
    return o


def orders_query(statut, q, du, au, produit_uuid):
    from datetime import datetime, timedelta

    query = select(OrderRequest)
    if statut:
        query = query.where(OrderRequest.order_status == ORDER_STATUS_DB[statut])
    if q and q.strip():
        p = like_pattern(q.strip())
        query = query.where(or_(OrderRequest.numero.like(p), OrderRequest.customer_name.like(p),
                                OrderRequest.customer_phone.like(p), OrderRequest.product_title.like(p)))
    if du:
        query = query.where(OrderRequest.created_at >= datetime.combine(du, datetime.min.time()))
    if au:
        query = query.where(OrderRequest.created_at < datetime.combine(au + timedelta(days=1), datetime.min.time()))
    if produit_uuid:
        query = query.join(Product, Product.id == OrderRequest.product_id).where(Product.uuid == produit_uuid)
    return query.order_by(OrderRequest.created_at.desc(), OrderRequest.id.desc())


def history(db: Session, o: OrderRequest) -> list[dict]:
    rows = db.scalars(select(OrderHistory).where(OrderHistory.order_id == o.id).order_by(OrderHistory.id))
    return [{"de": h.from_status, "vers": h.to_status, "note": h.note, "le": h.created_at} for h in rows]


def set_order_status(db: Session, o: OrderRequest, admin: User, statut: str, note: str | None,
                     client: ClientInfo, background: BackgroundTasks) -> OrderRequest:
    current = ORDER_STATUS.get(o.order_status, o.order_status)
    if statut not in TRANSITIONS.get(current, set()):
        raise ApiError(409, "INVALID_TRANSITION", f"Impossible de passer de « {current} » à « {statut} »")
    if statut == "livree" and o.product is not None and o.product.stock_quantite is not None:
        if o.product.stock_quantite < o.quantite:
            raise ApiError(409, "OUT_OF_STOCK", "Stock insuffisant pour livrer cette commande")
        o.product.stock_quantite -= o.quantite  # le stock baisse à la livraison
    o.order_status, o.updated_at = ORDER_STATUS_DB[statut], now()
    db.add(OrderHistory(order_id=o.id, from_status=current, to_status=statut, note=note, author_id=admin.id,
                        created_at=now()))
    audit.activity(db, admin.id, "order_status", client, f"{current} → {statut}", module="commandes", item_id=o.id,
                   item_name=o.numero)
    if o.customer_email:
        labels = {"en_cours": "est en cours de préparation", "livree": "est livrée", "annulee": "est annulée"}
        lines = [f"Votre commande {o.numero} ({o.product_title}) {labels[statut]}."] + ([note] if note else [])
        text, html = simple_email(o.customer_name.split(" ")[0], f"Commande {o.numero}", lines)
        queue_email(db, o.customer_email, f"Nufotec – Commande {o.numero}", text, html)
        background.add_task(deliver_pending)
    db.commit()
    return o


def add_order_note(db: Session, o: OrderRequest, admin: User, texte: str) -> None:
    db.add(OrderHistory(order_id=o.id, note=texte.strip(), author_id=admin.id, created_at=now()))
    db.commit()


def mark_contacted(db: Session, o: OrderRequest, admin: User) -> OrderRequest:
    o.whatsapp_sent, o.whatsapp_sent_at, o.updated_at = True, now(), now()
    db.add(OrderHistory(order_id=o.id, note="Client contacté sur WhatsApp", author_id=admin.id, created_at=now()))
    db.commit()
    return o


def stats(db: Session) -> dict:
    by_status = {ORDER_STATUS.get(k, k): v for k, v in db.execute(
        select(OrderRequest.order_status, func.count()).group_by(OrderRequest.order_status)).all()}
    top = db.execute(select(OrderRequest.product_title, func.count(), func.sum(OrderRequest.quantite))
                     .group_by(OrderRequest.product_title).order_by(func.count().desc()).limit(10)).all()
    countries = db.execute(select(OrderRequest.customer_country, func.count()).group_by(OrderRequest.customer_country)
                           .order_by(func.count().desc()).limit(10)).all()
    revenue = db.execute(select(OrderRequest.devise, func.sum(OrderRequest.total))
                         .where(OrderRequest.order_status == "completed", OrderRequest.devise.is_not(None))
                         .group_by(OrderRequest.devise)).all()
    return {"total": sum(by_status.values()), "par_statut": by_status,
            "non_contactees": db.scalar(select(func.count()).select_from(OrderRequest).where(
                OrderRequest.whatsapp_sent.is_(False), OrderRequest.order_status == "pending")) or 0,
            "produits": [{"produit": t, "commandes": n, "quantite": int(q or 0)} for t, n, q in top],
            "pays": [{"pays": c or "—", "commandes": n} for c, n in countries],
            "livre": {d: float(t or 0) for d, t in revenue},
            "demandes_de_prix": int(db.scalar(select(func.sum(Product.price_request_count))) or 0)}


def export_csv(db: Session, query) -> Iterator[str]:
    from app.modules.users.service import _cell

    buffer = io.StringIO()
    writer = csv.writer(buffer, delimiter=";")
    yield "﻿"
    writer.writerow(["numero", "date", "statut", "produit", "quantite", "total", "devise", "client", "telephone",
                     "email", "pays", "ville", "adresse", "contacte_whatsapp"])
    for o in db.scalars(query.limit(20_000)).unique():
        writer.writerow([_cell(v) for v in (o.numero, o.created_at, ORDER_STATUS.get(o.order_status), o.product_title,
                                            o.quantite, o.total, o.devise, o.customer_name, o.customer_phone,
                                            o.customer_email, o.customer_country, o.customer_city, o.customer_address,
                                            "oui" if o.whatsapp_sent else "non")])
    yield buffer.getvalue()
