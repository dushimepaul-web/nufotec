"""Contenu du site : pages, actualités, éléments publics, paramètres, contact, newsletter, visites, tableau de bord."""

import html
import hashlib
import json
import re
import secrets
from datetime import date, timedelta

from fastapi import BackgroundTasks
from sqlalchemy import func, or_, select
from sqlalchemy.orm import Session

from app.core.client import ClientInfo
from app.core.config import get_settings
from app.core.helpers import minutes_ago, now, phone_or_422
from app.core.mailer import deliver_pending, queue_email, simple_email
from app.core.responses import ApiError
from app.core.uploads import public_url
from app.models import (
    Article,
    Parametre,
    Pays,
    ContactMessage,
    NewsletterSubscriber,
    Page,
    PageSection,
    VisitorLog,
)

# ───────────────────────── pages ─────────────────────────


def menu(db: Session) -> list[dict]:
    rows = list(db.scalars(select(Page).where(Page.est_publiee.is_(True), Page.afficher_menu.is_(True),
                                              Page.deleted_at.is_(None)).order_by(Page.menu_ordre, Page.titre)))
    items = {p.id: {"titre": p.titre, "slug": p.slug, "icone": p.icone, "enfants": [], "_p": p} for p in rows}
    roots = []
    for p in rows:
        (items[p.parent_id]["enfants"] if p.parent_id in items else roots).append(items[p.id])
    return roots


def clean_menu(nodes: list[dict]) -> list[dict]:
    for n in nodes:
        n.pop("_p", None)
        clean_menu(n["enfants"])
    return nodes


def get_page(db: Session, slug: str, admin: bool = False) -> Page:
    query = select(Page).where(Page.slug == slug, Page.deleted_at.is_(None))
    if not admin:
        query = query.where(Page.est_publiee.is_(True))
    p = db.scalar(query)
    if p is None:
        raise ApiError(404, "NOT_FOUND", "Page introuvable")
    return p


def image(value: str | None) -> str | None:
    if not value:
        return None
    if value.startswith(("http://", "https://")):
        return value
    tail = value.lstrip("/").removeprefix("attachments/")
    return f"{get_settings().media_base_url.rstrip('/')}/{tail}"


def section_out(s: PageSection) -> dict:
    texts = {"titre": s.titre, "sous_titre": s.sous_titre, "contenu": s.contenu, "bouton_texte": s.bouton_texte}
    try:
        options = json.loads(s.options_json) if s.options_json else None
    except ValueError:
        options = None
    return {"id": s.id, "type": s.type, **texts, "image": image(s.image_url), "image_droite": s.image_droite,
            "bouton_lien": s.bouton_lien, "classe": s.classe, "options": options, "ordre": s.ordre,
            "publiee": s.est_publiee}


def page_out(db: Session, p: Page, admin: bool = False) -> dict:
    texts = {"titre": p.titre, "meta_description": p.meta_description, "contenu": p.contenu}
    query = select(PageSection).where(PageSection.page_id == p.id).order_by(PageSection.ordre, PageSection.id)
    if not admin:
        query = query.where(PageSection.est_publiee.is_(True))
    return {"id": p.id, "slug": p.slug, **texts, "meta_keywords": p.meta_keywords, "template": p.template,
            "publiee": p.est_publiee, "parent_id": p.parent_id,
            "menu_ordre": p.menu_ordre, "afficher_menu": p.afficher_menu, "modifiee_le": p.updated_at,
            "sections": [section_out(s) for s in db.scalars(query)]}


# ───────────────────────── actualités ─────────────────────────

def published_articles():
    """Publiés et dont la date de publication est passée : la programmation fonctionne sans tâche planifiée."""
    return select(Article).where(Article.est_publie.is_(True), Article.deleted_at.is_(None),
                                 or_(Article.date_publication.is_(None), Article.date_publication <= now()))


def reading_minutes(content: str | None) -> int:
    """Temps de lecture (200 mots par minute), comme la vue PHP ; au moins 1 minute."""
    words = len(re.sub(r"<[^>]+>", " ", content or "").split())
    return max(1, round(words / 200))


def article_out(a: Article, detail: bool = False) -> dict:
    texts = {"titre": a.titre, "resume": a.resume}
    if detail:
        texts["contenu"] = a.contenu
    return {"id": a.id, "slug": a.slug, **texts, "image": image(a.image_principale), "auteur": a.auteur,
            "date_publication": a.date_publication or a.created_at, "categorie": a.categorie,
            "tags": [t.strip() for t in (a.tags or "").split(",") if t.strip()], "a_la_une": a.est_en_avant,
            "vues": a.vues or 0, "temps_lecture": reading_minutes(a.contenu)}


def article_link(a: Article | None) -> dict | None:
    return {"slug": a.slug, "titre": a.titre} if a else None


def popular_articles(db: Session, limit: int = 4, exclude: int | None = None) -> list[dict]:
    query = published_articles().order_by(Article.vues.desc(), Article.date_publication.desc()).limit(limit)
    if exclude:
        query = query.where(Article.id != exclude)
    return [{"slug": a.slug, "titre": a.titre, "image": image(a.image_principale), "vues": a.vues or 0,
             "date_publication": a.date_publication or a.created_at} for a in db.scalars(query)]


def category_counts(db: Session) -> list[dict]:
    rows = db.execute(select(Article.categorie, func.count()).where(
        Article.est_publie.is_(True), Article.deleted_at.is_(None), Article.categorie.is_not(None), Article.categorie != "",
        or_(Article.date_publication.is_(None), Article.date_publication <= now()))
        .group_by(Article.categorie).order_by(func.count().desc()))
    return [{"nom": c, "articles": n} for c, n in rows]


# ───────────────────────── paramètres du site ─────────────────────────

SECRET = re.compile(r"(pass|key|token|secret)", re.I)
PUBLIC_CATEGORIES = {"agf_identity", "agf_facility", "agf_finance", "home", "media", "seo", "ui", "system", "general"}
PUBLIC_CONTACT = {"adresse_siege", "horaires_travail", "linkgroupewhatsapp", "site_phone", "contact_email_invest"}


def _value(c: Parametre) -> object:
    kind = c.type or "texte"
    if kind == "image":
        return public_url("Configurations", c.valeur, None) if c.valeur and "/" not in c.valeur else image(c.valeur)
    if kind == "boolean":
        return str(c.valeur).lower() in ("1", "true", "oui", "yes")
    if kind == "nombre":
        try:
            return float(c.valeur) if "." in str(c.valeur) else int(c.valeur)
        except (TypeError, ValueError):
            return c.valeur
    if kind == "json":
        try:
            return json.loads(c.valeur or "null")
        except ValueError:
            return c.valeur
    # le PHP enregistrait du texte échappé (&#039; …) : on le rend en texte normal
    return html.unescape(c.valeur) if isinstance(c.valeur, str) else c.valeur


def public_settings(db: Session) -> dict:
    from app.core.i18n import LANGUAGES

    rows = db.scalars(select(Parametre))
    values = {c.cle: _value(c) for c in rows if not SECRET.search(c.cle)
              and ((c.categorie or "") in PUBLIC_CATEGORIES or c.cle in PUBLIC_CONTACT)}
    # Le contenu est en français ; le site le traduit dans le navigateur avec Google Translate
    # (rien n'est traduit ni stocké côté serveur).
    values["langue_par_defaut"] = "fr"
    values["traduction"] = {
        "moteur": "google_translate", "langue_de_la_page": "fr",
        "langues": [{"code": code, "google": google, "nom": native} for code, (native, _fr, google) in LANGUAGES.items()],
    }
    return values


def admin_settings(db: Session) -> list[dict]:
    return [{"cle": c.cle, "valeur": c.valeur, "valeur_affichee": _value(c), "type": c.type, "categorie": c.categorie,
             "description": c.description, "publique": (c.categorie or "") in PUBLIC_CATEGORIES or c.cle in PUBLIC_CONTACT}
            for c in db.scalars(select(Parametre).order_by(Parametre.categorie, Parametre.cle))
            if not SECRET.search(c.cle) and (c.categorie or "") != "chatbot"]


def update_settings(db: Session, values: dict[str, str]) -> None:
    for key, value in values.items():
        c = db.scalar(select(Parametre).where(Parametre.cle == key))
        if c is None or SECRET.search(key) or (c.categorie or "") == "chatbot":
            raise ApiError(422, "VALIDATION_ERROR", f"Paramètre inconnu ou protégé : {key}")
        kind = c.type or "texte"
        text = "" if value is None else str(value)
        if kind == "nombre" and text and not re.fullmatch(r"-?\d+(\.\d+)?", text):
            raise ApiError(422, "VALIDATION_ERROR", f"{key} doit être un nombre")
        if kind == "boolean":
            text = "1" if text.lower() in ("1", "true", "oui", "yes") else "0"
        if kind == "json":
            try:
                json.loads(text or "null")
            except ValueError:
                raise ApiError(422, "VALIDATION_ERROR", f"{key} doit être du JSON valide") from None
        if kind == "image":
            raise ApiError(422, "VALIDATION_ERROR", f"{key} est une image : utilisez l'envoi d'image")
        c.valeur = text
    db.commit()


# ───────────────────────── contact ─────────────────────────

def _admin_email(db: Session) -> str:
    return db.scalar(select(Parametre.valeur).where(Parametre.cle == "admin_email")) or get_settings().mail_from


def submit_contact(db: Session, data, client: ClientInfo, background: BackgroundTasks) -> dict:
    """Enregistre le message, prévient l'équipe par email et renvoie le message WhatsApp préparé vers l'équipe."""
    from app.modules.shop.service import sales_number, wa_link  # même numéro WhatsApp que la boutique

    if data.site_web:  # robot : on fait semblant d'accepter
        return wa_link(sales_number(db), "")
    if client.ip and (db.scalar(select(func.count()).select_from(ContactMessage).where(
            ContactMessage.ip_address == client.ip, ContactMessage.created_at > minutes_ago(60))) or 0) >= 5:
        raise ApiError(429, "TOO_MANY_REQUESTS", "Trop de messages envoyés. Réessayez plus tard.",
                       {"retry_after_seconds": 3600}, {"Retry-After": "3600"})
    if len(re.findall(r"https?://", data.message)) > 3:
        raise ApiError(422, "MESSAGE_REJECTED", "Votre message contient trop de liens")
    pays = db.get(Pays, data.pays_id) if data.pays_id else None
    if data.pays_id and pays is None:
        raise ApiError(422, "VALIDATION_ERROR", "Pays inconnu", [{"field": "pays_id", "message": "Pays inconnu"}])
    phone = phone_or_422(data.telephone)
    pays_name = pays.pays if pays else None
    place = ", ".join(x for x in (data.lieu, pays_name) if x) or None
    m = ContactMessage(nom=data.nom, email=data.email.lower(), sujet=data.sujet, message=data.message,
                       telephone=phone, lieu=place[:200] if place else None, ip_address=client.ip,
                       user_agent=client.user_agent or None, lu=False, created_at=now())
    db.add(m)
    text, html = simple_email("équipe", f"Nouveau message : {data.sujet}",
                              [f"De : {data.nom} <{data.email}>", f"WhatsApp : {phone}",
                               f"Pays : {pays_name or '—'}" + (f" – ville : {data.lieu}" if data.lieu else ""),
                               f"Objet : {data.sujet}", data.message])
    queue_email(db, _admin_email(db), f"Nufotec – Contact : {data.sujet}", text, html)
    background.add_task(deliver_pending)
    db.commit()
    lines = ["MESSAGE - CONTACT NUFOTEC", "", f"• Nom : {data.nom}", f"• Email : {data.email}",
             f"• WhatsApp : {phone}", f"• Pays : {pays_name or '—'}"]
    if data.lieu:
        lines.append(f"• Ville : {data.lieu}")
    lines += [f"• Objet : {data.sujet}", "", data.message]
    return wa_link(sales_number(db), "\n".join(lines))


def reply_contact(db: Session, m: ContactMessage, admin, texte: str, background: BackgroundTasks) -> ContactMessage:
    text, html = simple_email(m.nom.split(" ")[0], "Réponse à votre message",
                              [texte, f"— Votre message : « {m.message[:500]} »"])
    queue_email(db, m.email, f"Nufotec – Re : {m.sujet or 'votre message'}", text, html)
    m.reponse, m.repondu_at, m.repondu_par, m.lu = texte, now(), admin.id, True
    background.add_task(deliver_pending)
    db.commit()
    return m


def contact_out(m: ContactMessage) -> dict:
    return {"id": m.id, "nom": m.nom, "email": m.email, "telephone": m.telephone, "sujet": m.sujet,
            "message": m.message, "lieu": m.lieu, "lu": m.lu, "reponse": m.reponse, "repondu_le": m.repondu_at,
            "recu_le": m.created_at}


# ───────────────────────── newsletter (double confirmation) ─────────────────────────

def _links(token: str) -> tuple[str, str]:
    base = get_settings().public_api_url.rstrip("/") + "/api/v1/newsletter"
    return f"{base}/confirmer/{token}", f"{base}/desinscrire/{token}"


def subscribe(db: Session, email: str, lang: str, client: ClientInfo, background: BackgroundTasks,
              telephone: str | None = None) -> None:
    """Réponse identique dans tous les cas (on ne révèle pas si l'email est déjà inscrit)."""
    email = email.strip().lower()
    if client.ip and (db.scalar(select(func.count()).select_from(NewsletterSubscriber).where(
            NewsletterSubscriber.consent_ip == client.ip,
            NewsletterSubscriber.date_inscription > minutes_ago(60))) or 0) >= 5:
        raise ApiError(429, "TOO_MANY_REQUESTS", "Trop d'inscriptions depuis ce réseau. Réessayez plus tard.",
                       {"retry_after_seconds": 3600}, {"Retry-After": "3600"})
    sub = db.scalar(select(NewsletterSubscriber).where(NewsletterSubscriber.email == email))
    if sub is not None and sub.statut == "confirme":
        return
    if sub is None:
        sub = NewsletterSubscriber(email=email, date_inscription=now())
        db.add(sub)
    sub.statut, sub.jeton, sub.langue, sub.consent_ip = "en_attente", secrets.token_hex(32), lang, client.ip
    sub.date_inscription = now()
    if telephone and telephone.strip():
        sub.telephone = telephone.strip()
    confirm, unsubscribe = _links(sub.jeton)
    text, html = simple_email("", "Confirmez votre inscription",
                              ["Merci de votre intérêt pour la newsletter Nufotec.",
                               "Cliquez sur le bouton pour confirmer votre inscription. Sans confirmation, vous ne "
                               "recevrez rien.", f"Pour ne plus rien recevoir : {unsubscribe}"],
                              ("Confirmer mon inscription", confirm))
    queue_email(db, email, "Nufotec – Confirmez votre inscription à la newsletter", text, html)
    background.add_task(deliver_pending)
    db.commit()


def confirm_subscription(db: Session, token: str) -> NewsletterSubscriber:
    sub = db.scalar(select(NewsletterSubscriber).where(NewsletterSubscriber.jeton == token))
    if sub is None or sub.statut == "desinscrit":
        raise ApiError(400, "INVALID_TOKEN", "Lien invalide ou expiré")
    sub.statut, sub.confirme_at = "confirme", now()
    db.commit()
    return sub


def unsubscribe(db: Session, token: str) -> NewsletterSubscriber:
    sub = db.scalar(select(NewsletterSubscriber).where(NewsletterSubscriber.jeton == token))
    if sub is None:
        raise ApiError(400, "INVALID_TOKEN", "Lien invalide")
    sub.statut, sub.desinscrit_at = "desinscrit", now()
    db.commit()
    return sub


def send_reconfirmation(db: Session, background: BackgroundTasks) -> int:
    """Action manuelle de l'admin : demande aux anciens inscrits de reconfirmer (aucun envoi automatique)."""
    subs = list(db.scalars(select(NewsletterSubscriber).where(NewsletterSubscriber.statut == "a_reconfirmer",
                                                              NewsletterSubscriber.email.is_not(None))))
    for sub in subs:
        confirm, unsubscribe_link = _links(sub.jeton)
        text, html = simple_email("", "Souhaitez-vous rester inscrit ?",
                                  ["Vous êtes inscrit à la newsletter Nufotec. Nous mettons à jour notre liste : "
                                   "cliquez pour continuer à recevoir nos nouvelles.",
                                   f"Sans réponse, vous ne recevrez plus rien. Se désinscrire : {unsubscribe_link}"],
                                  ("Je reste inscrit", confirm))
        queue_email(db, sub.email, "Nufotec – Souhaitez-vous rester inscrit ?", text, html)
        sub.statut = "en_attente"
    db.commit()
    background.add_task(deliver_pending)
    return len(subs)


# ───────────────────────── visites (respect de la vie privée) ─────────────────────────

def _device(ua: str) -> str:
    """Valeurs de la colonne visitors_logs.device : Mobile, Desktop, Other (« bot » = visite ignorée)."""
    ua = ua.lower()
    if any(b in ua for b in ("bot", "spider", "crawler")):
        return "bot"
    if "ipad" in ua or "tablet" in ua:
        return "Other"
    if any(m in ua for m in ("mobile", "android", "iphone")):
        return "Mobile"
    return "Desktop"


def record_visit(db: Session, page: str, referer: str | None, client: ClientInfo, country: str | None) -> None:
    """Ni IP ni navigateur conservés : un identifiant haché du jour permet de compter les visiteurs uniques."""
    device = _device(client.user_agent or "")
    if device == "bot":
        return
    day_salt = f"{date.today()}|{get_settings().jwt_secret[:16]}"
    visitor = hashlib.sha256(f"{day_salt}|{client.ip}|{client.user_agent}".encode()).hexdigest()[:32]
    current = now()
    db.add(VisitorLog(visitor_hash=visitor, ip_address="", user_agent=None, device=device, page=page[:255],
                      referer=(referer or "")[:500] or None, visit_date=current.date(), visit_time=current.time(),
                      country=(country or "")[:100] or None, created_at=current))
    db.commit()


def visit_stats(db: Session, days: int) -> dict:
    since = date.today() - timedelta(days=days - 1)
    base = select(VisitorLog).where(VisitorLog.visit_date >= since)
    per_day = db.execute(select(VisitorLog.visit_date, func.count(), func.count(func.distinct(VisitorLog.visitor_hash)))
                         .where(VisitorLog.visit_date >= since).group_by(VisitorLog.visit_date)
                         .order_by(VisitorLog.visit_date)).all()

    def top(column, n=10):
        return [{"valeur": v or "—", "visites": c} for v, c in db.execute(
            select(column, func.count()).where(VisitorLog.visit_date >= since).group_by(column)
            .order_by(func.count().desc()).limit(n)).all()]

    total = db.scalar(select(func.count()).select_from(base.subquery())) or 0
    return {"periode_jours": days, "visites": total,
            "visiteurs_uniques": db.scalar(select(func.count(func.distinct(VisitorLog.visitor_hash)))
                                           .where(VisitorLog.visit_date >= since)) or 0,
            "par_jour": [{"jour": d, "visites": v, "visiteurs": u} for d, v, u in per_day],
            "pages": top(VisitorLog.page), "pays": top(VisitorLog.country), "appareils": top(VisitorLog.device, 5)}


# ───────────────────────── tableau de bord admin ─────────────────────────

def dashboard(db: Session) -> dict:
    from app.models import (
        Broker,
        ChatbotConversation,
        Consultation,
        Investor,
        Media,
        MediaComment,
        OrderRequest,
        User,
    )

    def count(model, *conds) -> int:
        return db.scalar(select(func.count()).select_from(model).where(*conds)) or 0

    month = now() - timedelta(days=30)
    return {
        "utilisateurs": {"total": count(User, User.deleted_at.is_(None)),
                         "nouveaux_30_jours": count(User, User.created_at >= month, User.deleted_at.is_(None))},
        "consultations": {"a_venir": count(Consultation, Consultation.statut.in_(("en_attente", "confirmee")),
                                           Consultation.date_souhaitee >= now()),
                          "en_attente_confirmation": count(Consultation, Consultation.statut == "en_attente"),
                          "paiements_a_valider": count(Consultation, Consultation.paiement_statut == "en_attente",
                                                       Consultation.statut.in_(("en_attente", "confirmee")))},
        "commandes": {"en_attente": count(OrderRequest, OrderRequest.order_status == "pending"),
                      "non_contactees": count(OrderRequest, OrderRequest.order_status == "pending",
                                              OrderRequest.whatsapp_sent.is_(False))},
        "investisseurs": {"nouveaux": count(Investor, Investor.statut == "nouveau"),
                          "relances_dues": count(Investor, Investor.prochaine_relance <= now()),
                          "courtiers_a_examiner": count(Broker, Broker.statut == "en_attente")},
        "medias": {"total": count(Media), "commentaires_a_moderer": count(MediaComment, MediaComment.is_approved == 0),
                   "traitements_en_erreur": count(Media, Media.traitement == "erreur")},
        "messages": {"non_lus": count(ContactMessage, ContactMessage.lu.is_(False))},
        "newsletter": {"confirmes": count(NewsletterSubscriber, NewsletterSubscriber.statut == "confirme"),
                       "a_reconfirmer": count(NewsletterSubscriber, NewsletterSubscriber.statut == "a_reconfirmer")},
        "chatbot": {"en_attente_conseiller": count(ChatbotConversation, ChatbotConversation.status == "pending_human")},
        "visites_7_jours": count(VisitorLog, VisitorLog.visit_date >= date.today() - timedelta(days=6)),
    }
