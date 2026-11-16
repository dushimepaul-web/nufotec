"""Contenu public du site et de l'app, en français. La traduction se fait sur le site avec Google Translate."""

from html import escape
from fastapi import APIRouter, BackgroundTasks, Depends, Query, Request, status
from fastapi.responses import HTMLResponse
from pydantic import BaseModel, EmailStr, Field, field_validator
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core.client import ClientInfo, get_client
from app.core.database import get_db
from app.core.helpers import like_pattern, plain_excerpt
from app.core.i18n import lang_var
from app.core.pagination import PageParams, paginate
from app.core.responses import ApiError, Envelope, ok
from app.core.text import clean_free_text, clean_person_name
from app.models import Article, Faq, KeyFigure, Page, Partner, Product, SocialLink, Testimonial
from app.modules.content import service

DB = Depends(get_db)
CLIENT = Depends(get_client)
router = APIRouter(tags=["Contenu du site (public)"])


class ContactIn(BaseModel):
    """Comme la vue PHP : nom, email, téléphone (WhatsApp), objet et message obligatoires ; pays et ville facultatifs."""
    nom: str = Field(min_length=2, max_length=250)
    email: EmailStr
    telephone: str = Field(min_length=6, max_length=20, description="Numéro WhatsApp, avec l'indicatif du pays")
    pays_id: int | None = Field(default=None, description="Pays de résidence (GET /pays)")
    sujet: str = Field(min_length=3, max_length=250)
    message: str = Field(min_length=10, max_length=5000)
    lieu: str | None = Field(default=None, max_length=150, description="Ville")
    site_web: str | None = Field(default=None, description="Champ invisible anti-robots : doit rester vide")

    @field_validator("nom")
    @classmethod
    def _clean_name(cls, v: str) -> str:
        cleaned = clean_person_name(v)
        if len(cleaned) < 2:
            raise ValueError("Nom trop court")
        return cleaned

    @field_validator("sujet", "message")
    @classmethod
    def _clean_required_text(cls, v: str, info) -> str:
        cleaned = clean_free_text(v) or ""
        if len(cleaned) < (3 if info.field_name == "sujet" else 10):
            raise ValueError("Texte trop court")
        return cleaned

    @field_validator("lieu")
    @classmethod
    def _clean_city(cls, v: str | None) -> str | None:
        return clean_free_text(v)


class NewsletterIn(BaseModel):
    email: EmailStr
    telephone: str | None = Field(default=None, max_length=20, description="Facultatif")
    site_web: str | None = None


class TokenIn(BaseModel):
    jeton: str = Field(min_length=20, max_length=64)


class VisitIn(BaseModel):
    page: str = Field(min_length=1, max_length=255)
    referer: str | None = Field(default=None, max_length=500)


@router.get("/site/parametres", response_model=Envelope[dict],
            summary="Paramètres publics du site (nom, logo, contacts, SEO, thème) — jamais de secret")
def site_settings(db: Session = DB):
    return ok(service.public_settings(db))


@router.get("/recherche", response_model=Envelope[dict],
            summary="Recherche rapide du site (barre du haut) : produits, actualités, pages")
def search(q: str = Query(min_length=2, max_length=100), limite: int = Query(4, ge=1, le=20), db: Session = DB):
    p = like_pattern(q.strip())
    products = db.scalars(select(Product).where(Product.is_active.is_(True), Product.deleted_at.is_(None),
                                                Product.title.like(p) | Product.description.like(p))
                          .order_by(Product.in_vedette.desc(), Product.title).limit(limite))
    articles = db.scalars(service.published_articles().where(Article.titre.like(p) | Article.resume.like(p))
                          .order_by(Article.date_publication.desc()).limit(limite))
    pages = db.scalars(select(Page).where(Page.est_publiee.is_(True), Page.deleted_at.is_(None),
                                          Page.titre.like(p) | Page.meta_description.like(p))
                       .order_by(Page.titre).limit(limite))
    return ok({
        "produits": [{"slug": x.slug, "titre": x.title, "extrait": plain_excerpt(x.description, 60)} for x in products],
        "actualites": [{"slug": x.slug, "titre": x.titre, "extrait": plain_excerpt(x.resume, 60)} for x in articles],
        "pages": [{"slug": x.slug, "titre": x.titre, "extrait": plain_excerpt(x.meta_description, 60)} for x in pages],
    })


@router.get("/pages", response_model=Envelope[list[dict]], summary="Menu du site (pages publiées, sous-menus)")
def pages_menu(db: Session = DB):
    return ok(service.clean_menu(service.menu(db)))


@router.get("/pages/{slug}", response_model=Envelope[dict], summary="Une page et ses sections")
def page(slug: str, db: Session = DB):
    return ok(service.page_out(db, service.get_page(db, slug)))


@router.get("/actualites", response_model=Envelope[dict], summary="Actualités publiées")
def articles(q: str | None = Query(None, max_length=100),
             categorie: str | None = Query(None, max_length=100), page: PageParams = Depends(), db: Session = DB):
    query = service.published_articles()
    if q:
        query = query.where(Article.titre.like(like_pattern(q)) | Article.resume.like(like_pattern(q)))
    if categorie:
        query = query.where(Article.categorie == categorie)
    rows, meta = paginate(db, query.order_by(Article.est_en_avant.desc(), Article.date_publication.desc(),
                                             Article.id.desc()), page)
    items = [service.article_out(a) for a in rows]
    categories = [c for c, in db.execute(select(Article.categorie).where(
        Article.est_publie.is_(True), Article.deleted_at.is_(None), Article.categorie.is_not(None)).distinct())]
    return ok({"items": items, "meta": meta, "categories": categories,
               "categories_compte": service.category_counts(db), "populaires": service.popular_articles(db)})


@router.get("/actualites/{slug}", response_model=Envelope[dict], summary="Un article (compte une vue)")
def article(slug: str, db: Session = DB):
    a = db.scalar(service.published_articles().where(Article.slug == slug))
    if a is None:
        raise ApiError(404, "NOT_FOUND", "Article introuvable")
    a.vues = (a.vues or 0) + 1
    db.commit()
    data = service.article_out(a, detail=True)
    related = db.scalars(service.published_articles().where(Article.id != a.id, Article.categorie == a.categorie)
                         .order_by(Article.date_publication.desc()).limit(3))
    data["articles_lies"] = [service.article_out(r) for r in related]
    when = a.date_publication or a.created_at
    older = db.scalar(service.published_articles().where(Article.id != a.id, Article.date_publication < when)
                      .order_by(Article.date_publication.desc()).limit(1)) if when else None
    newer = db.scalar(service.published_articles().where(Article.id != a.id, Article.date_publication > when)
                      .order_by(Article.date_publication.asc()).limit(1)) if when else None
    data["precedent"], data["suivant"] = service.article_link(older), service.article_link(newer)
    data["populaires"] = service.popular_articles(db, exclude=a.id)
    return ok(data)


@router.get("/faq", response_model=Envelope[list[dict]], summary="Questions fréquentes, par catégorie")
def faq(db: Session = DB):
    groups: dict[str, list] = {}
    for f in db.scalars(select(Faq).where(Faq.est_publiee.is_(True)).order_by(Faq.categorie, Faq.ordre, Faq.id)):
        groups.setdefault(f.categorie or "Général", []).append({"id": f.id, "question": f.question,
                                                                 "reponse": f.reponse})
    return ok([{"categorie": k, "questions": v} for k, v in groups.items()])


@router.get("/temoignages", response_model=Envelope[list[dict]], summary="Témoignages approuvés")
def testimonials(db: Session = DB):
    from app.modules.media.service import youtube_id

    rows = db.scalars(select(Testimonial).where(Testimonial.est_approuve.is_(True)).order_by(Testimonial.id.desc()))
    return ok([{"id": t.id, "titre": t.titre, "video_url": t.video_url, "youtube_id": youtube_id(t.video_url),
                "miniature": service.image(t.miniature) or (f"https://img.youtube.com/vi/{youtube_id(t.video_url)}/hqdefault.jpg"
                                                             if youtube_id(t.video_url) else None)} for t in rows])


@router.get("/partenaires", response_model=Envelope[list[dict]], summary="Partenaires actifs")
def partners(type: str | None = Query(None, max_length=50), db: Session = DB):
    query = select(Partner).where(Partner.est_actif.is_(True), Partner.deleted_at.is_(None))
    if type:
        query = query.where(Partner.type_partenaire == type)
    return ok([{"id": p.id, "nom": p.nom, "type": p.type_partenaire, "niveau": p.niveau_partenariat,
                "logo": service.image(p.logo_url), "description": p.description, "pays": p.pays,
                "site_web": p.site_web} for p in db.scalars(query.order_by(Partner.nom))])


@router.get("/chiffres-cles", response_model=Envelope[list[dict]], summary="Chiffres clés")
def key_figures(db: Session = DB):
    return ok([{"id": k.id, "etiquette": k.etiquette, "description": k.description, "valeur": k.valeur,
                "unite": k.unite, "icone": k.icone, "annee": k.annee_vision}
               for k in db.scalars(select(KeyFigure).order_by(KeyFigure.ordre, KeyFigure.id))])


@router.get("/reseaux-sociaux", response_model=Envelope[list[dict]], summary="Liens vers les réseaux sociaux")
def socials(db: Session = DB):
    return ok([{"plateforme": s.platform, "libelle": s.label, "url": s.url, "icone": s.icon_name or s.icon_class,
                "abonnes": s.followers, "nouvel_onglet": s.target_blank}
               for s in db.scalars(select(SocialLink).where(SocialLink.is_active.is_(True)).order_by(SocialLink.display_order))])


@router.post("/contact", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
             summary="Nous écrire (renvoie le message WhatsApp préparé vers l'équipe)")
def contact(body: ContactIn, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT):
    wa = service.submit_contact(db, body, client, background)
    return ok({"whatsapp": wa}, message="Merci ! Votre message a bien été envoyé. Nous vous répondons rapidement.")


@router.post("/newsletter/inscription", status_code=status.HTTP_201_CREATED, response_model=Envelope[None],
             summary="S'inscrire (un email de confirmation est envoyé)")
def newsletter(body: NewsletterIn, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT):
    if not body.site_web:
        service.subscribe(db, body.email, lang_var.get(), client, background, body.telephone)
    return ok(message="Presque fini ! Confirmez votre inscription grâce à l'email que nous venons de vous envoyer.")


def _html(title: str, text: str, color: str) -> HTMLResponse:
    return HTMLResponse(f"""<!doctype html><html lang="fr"><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title>{title} – Nufotec</title>
<body style="font-family:Arial,sans-serif;display:grid;place-items:center;min-height:90vh;color:#1f2933">
<div style="text-align:center;max-width:440px"><h1 style="color:{color}">{title}</h1><p>{text}</p></div></body></html>""")


@router.get("/newsletter/confirmer/{token}", response_class=HTMLResponse, include_in_schema=False)
def confirm_link(token: str, db: Session = DB):
    try:
        service.confirm_subscription(db, token)
        return _html("Inscription confirmée", "Merci ! Vous recevrez désormais les nouvelles de Nufotec.", "#0b6e4f")
    except ApiError as e:
        return _html("Lien invalide", escape(e.message), "#b42318")


@router.get("/newsletter/desinscrire/{token}", response_class=HTMLResponse, include_in_schema=False)
def unsubscribe_link(token: str, db: Session = DB):
    try:
        service.unsubscribe(db, token)
        return _html("Désinscription effectuée", "Vous ne recevrez plus la newsletter Nufotec.", "#0b6e4f")
    except ApiError as e:
        return _html("Lien invalide", escape(e.message), "#b42318")


@router.post("/newsletter/desinscription", response_model=Envelope[None], summary="Se désinscrire (depuis l'app)")
def unsubscribe(body: TokenIn, db: Session = DB):
    service.unsubscribe(db, body.jeton)
    return ok(message="Vous êtes désinscrit")


@router.post("/stats/visite", status_code=status.HTTP_204_NO_CONTENT, summary="Compter une visite (anonyme)")
def visit(body: VisitIn, request: Request, db: Session = DB, client: ClientInfo = CLIENT):
    country = request.headers.get("cf-ipcountry")  # fourni par Cloudflare s'il est utilisé
    service.record_visit(db, body.page, body.referer, client, country)
