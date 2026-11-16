"""Contenu du site : CMS (pages importées du PHP), actualités programmées, FAQ & co, paramètres publics sans
secret (français par défaut, traduction par Google Translate côté site), contact, newsletter en double confirmation,
visites anonymes, tableau de bord."""

import re
import uuid
from datetime import datetime, timedelta

import pytest
from sqlalchemy import select

from app.core.helpers import now
from app.core.security import hash_password
from app.models import ContactMessage, NewsletterSubscriber, Role, User, VisitorLog

PASSWORD = "MotDePasse123"
ADMIN = "/api/v1/admin/contenu"


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


def _user(db, slug="user", type_utilisateur="patient"):
    u = User(uuid=str(uuid.uuid4()), email=f"test-{uuid.uuid4().hex[:8]}@example.com", password=hash_password(PASSWORD),
             nom="Test", prenom="Admin", role_id=db.scalar(select(Role.id).where(Role.slug == slug)),
             type_utilisateur=type_utilisateur, is_active=True, created_at=now(), updated_at=now())
    db.add(u)
    db.commit()
    return u


@pytest.fixture
def admin_h(client, db):
    u = _user(db, "admin", "admin")
    return _auth(client.post("/api/v1/auth/login", json={"login": u.email, "password": PASSWORD})
                 .json()["data"]["tokens"]["access_token"])


# ───────────── pages importées ─────────────

def test_imported_pages_and_menu(client):
    menu = client.get("/api/v1/pages").json()["data"]
    slugs = {m["slug"] for m in menu} | {c["slug"] for m in menu for c in m["enfants"]}
    assert {"home", "about", "investment-projection", "vision-mission"} <= slugs
    about = next(m for m in menu if m["slug"] == "about")
    assert any(c["slug"] == "vision-mission" for c in about["enfants"])  # sous-menu repris du PHP


def test_page_with_sections_in_source_language(client):
    page = client.get("/api/v1/pages/home", headers={"Accept-Language": "sw"}).json()["data"]
    assert len(page["sections"]) == 4 and "traduction_en_cours" not in page  # servi tel quel, quelle que soit la langue
    assert any("Teleconsultation" in (s["contenu"] or "") or "Healthcare" in (s["titre"] or "") for s in page["sections"])
    assert all("<script" not in (s["contenu"] or "") for s in page["sections"])






def test_cms_create_page_sections_reorder_and_sanitize(client, admin_h):
    r = client.post(f"{ADMIN}/pages", headers=admin_h, json={
        "titre": "Nos engagements", "contenu": '<p onclick="vol()">Texte<script>alert(1)</script></p>'})
    assert r.status_code == 201, r.text
    page = r.json()["data"]
    assert page["slug"] == "nos-engagements" and "<script" not in page["contenu"] and "onclick" not in page["contenu"]
    s1 = client.post(f"{ADMIN}/pages/{page['id']}/sections", headers=admin_h, json={"type": "hero", "titre": "A"}).json()["data"]
    s2 = client.post(f"{ADMIN}/pages/{page['id']}/sections", headers=admin_h,
                     json={"type": "texte", "titre": "B", "contenu": '<a href="javascript:alert(1)">x</a>'}).json()["data"]
    assert "javascript:" not in s2["contenu"]
    r = client.put(f"{ADMIN}/pages/{page['id']}/sections/ordre", headers=admin_h, json=[s2["id"], s1["id"]])
    assert [s["titre"] for s in r.json()["data"]["sections"]] == ["B", "A"]
    public = client.get("/api/v1/pages/nos-engagements").json()["data"]
    assert [s["titre"] for s in public["sections"]] == ["B", "A"]
    assert client.post(f"{ADMIN}/pages", headers=admin_h, json={"titre": "Nos engagements"}).status_code == 409
    client.delete(f"{ADMIN}/pages/{page['id']}", headers=admin_h)
    assert client.get("/api/v1/pages/nos-engagements").status_code == 404


# ───────────── actualités ─────────────

def test_articles_draft_scheduled_published(client, admin_h):
    base = {"titre": "Inauguration du laboratoire", "resume": "Grande journée", "contenu": "<p>Texte</p>",
            "categorie": "Événements", "tags": ["labo", "GMP"]}
    draft = client.post(f"{ADMIN}/actualites", headers=admin_h, json=base).json()["data"]
    assert draft["etat"] == "brouillon"
    assert client.get(f"/api/v1/actualites/{draft['slug']}").status_code == 404
    future = (datetime.now() + timedelta(days=2)).replace(microsecond=0).isoformat()
    sched = client.put(f"{ADMIN}/actualites/{draft['id']}", headers=admin_h,
                       json={**base, "publie": True, "date_publication": future}).json()["data"]
    assert sched["etat"] == "programme" and client.get(f"/api/v1/actualites/{sched['slug']}").status_code == 404
    pub = client.put(f"{ADMIN}/actualites/{draft['id']}", headers=admin_h, json={**base, "publie": True}).json()["data"]
    assert pub["etat"] == "publie"
    article = client.get(f"/api/v1/actualites/{pub['slug']}").json()["data"]
    assert article["vues"] == 1 and article["tags"] == ["labo", "GMP"]
    listing = client.get("/api/v1/actualites").json()["data"]
    assert listing["items"][0]["slug"] == pub["slug"] and "Événements" in listing["categories"]



def test_articles_reading_time_counts_popular_and_navigation(client, admin_h):
    slugs = []
    for i, day in enumerate((3, 2, 1)):
        when = (datetime.now() - timedelta(days=day)).replace(microsecond=0).isoformat()
        body = {"titre": f"Article de test {i}", "resume": "Résumé", "contenu": "<p>" + "mot " * 450 + "</p>",
                "categorie": "Recherche", "publie": True, "date_publication": when}
        slugs.append(client.post(f"{ADMIN}/actualites", headers=admin_h, json=body).json()["data"]["slug"])
    listing = client.get("/api/v1/actualites").json()["data"]
    assert {"nom": "Recherche", "articles": 3} in listing["categories_compte"]
    assert listing["populaires"] and listing["items"][0]["temps_lecture"] == 2
    middle = client.get(f"/api/v1/actualites/{slugs[1]}").json()["data"]
    assert middle["precedent"]["slug"] == slugs[0] and middle["suivant"]["slug"] == slugs[2]
    assert all(p["slug"] != slugs[1] for p in middle["populaires"])


# ───────────── FAQ, témoignages, partenaires, chiffres, réseaux ─────────────

def test_public_blocks(client):
    faq = client.get("/api/v1/faq").json()["data"]
    assert sum(len(g["questions"]) for g in faq) >= 6
    assert client.get("/api/v1/partenaires").json()["data"]
    assert len(client.get("/api/v1/chiffres-cles").json()["data"]) == 12
    assert client.get("/api/v1/reseaux-sociaux").status_code == 200
    assert isinstance(client.get("/api/v1/temoignages").json()["data"], list)


def test_generic_crud(client, admin_h):
    r = client.post(f"{ADMIN}/faq", headers=admin_h, json={"question": "Livrez-vous à Gitega ?",
                                                          "reponse": "<p>Oui<script>x</script></p>", "categorie": "Livraison"})
    assert r.status_code == 201 and "<script" not in r.json()["data"]["reponse"]
    fid = r.json()["data"]["id"]
    client.put(f"{ADMIN}/faq/{fid}", headers=admin_h, json={"question": "Livrez-vous à Gitega ?", "reponse": "Oui", "publiee": False})
    assert all(q["id"] != fid for g in client.get("/api/v1/faq").json()["data"] for q in g["questions"])
    assert client.delete(f"{ADMIN}/faq/{fid}", headers=admin_h).status_code == 200
    r = client.post(f"{ADMIN}/reseaux-sociaux", headers=admin_h, json={"plateforme": "tiktok", "url": "http://pas-https.com"})
    assert r.status_code == 422
    r = client.post(f"{ADMIN}/partenaires", headers=admin_h, json={"nom": "Université du Burundi", "type": "scientifique", "niveau": "academique"})
    pid = r.json()["data"]["id"]
    client.delete(f"{ADMIN}/partenaires/{pid}", headers=admin_h)
    assert all(p["id"] != pid for p in client.get("/api/v1/partenaires").json()["data"])


# ───────────── paramètres ─────────────

def test_public_settings_never_expose_secrets(client):
    data = client.get("/api/v1/site/parametres").json()["data"]
    assert "site_name" in data and "contact_whatsapp" in data
    # Français par défaut ; le site traduit avec Google Translate (50 langues), rien côté serveur
    assert data["langue_par_defaut"] == "fr" and data["traduction"]["moteur"] == "google_translate"
    assert data["traduction"]["langue_de_la_page"] == "fr" and len(data["traduction"]["langues"]) == 50
    assert {"code": "zh", "google": "zh-CN", "nom": "中文"} in data["traduction"]["langues"]
    for key in (k for k in data if k not in ("langue_par_defaut", "traduction")):
        assert not re.search(r"(pass|key|token|secret|smtp)", key, re.I), key
    assert "admin_email" not in data and not any(k.startswith("chatbot") for k in data)


def test_admin_settings_typed(client, admin_h):
    keys = {s["cle"]: s for s in client.get(f"{ADMIN}/parametres", headers=admin_h).json()["data"]}
    assert "smtp_pass" not in keys and "smtp_password" not in keys  # supprimés de la base
    r = client.put(f"{ADMIN}/parametres", headers=admin_h, json={"valeurs": {"site_maintenance": "oui"}})
    assert r.status_code == 200
    assert client.get("/api/v1/site/parametres").json()["data"]["site_maintenance"] is True
    assert client.put(f"{ADMIN}/parametres", headers=admin_h, json={"valeurs": {"smtp_host": "x", "inconnu": "y"}}).status_code == 422


# ───────────── contact ─────────────

def test_contact_flow(client, db, admin_h, outbox):
    body = {"nom": "Claudine Ndikumana", "email": "claudine@example.com", "sujet": "Partenariat",
            "telephone": "+25779456123", "pays_id": 194, "lieu": "Ngozi",
            "message": "Bonjour, nous aimerions distribuer vos produits à Ngozi."}
    assert client.post("/api/v1/contact", json=body).status_code == 201
    assert any("Contact" in m.subject for m in outbox)
    inbox = client.get(f"{ADMIN}/messages", headers=admin_h, params={"non_lus": True}).json()["data"]["items"]
    mid = next(m["id"] for m in inbox if m["email"] == "claudine@example.com")
    assert client.get(f"{ADMIN}/messages/{mid}", headers=admin_h).json()["data"]["lu"] is True
    r = client.post(f"{ADMIN}/messages/{mid}/repondre", headers=admin_h, json={"texte": "Merci, appelons-nous lundi."})
    assert r.json()["data"]["reponse"] and any(m.to == "claudine@example.com" for m in outbox)
    honeypot = client.post("/api/v1/contact", json={**body, "site_web": "spam"})
    assert honeypot.status_code == 201
    assert db.query(ContactMessage).filter(ContactMessage.email == "claudine@example.com").count() == 1



def test_contact_email_and_whatsapp_carry_phone_and_country(client, outbox):
    r = client.post("/api/v1/contact", json={
        "nom": "  Jean <b>Bosco</b>  ", "email": "jbosco@example.com", "telephone": "79 456 124", "pays_id": 194,
        "lieu": "Gitega", "sujet": "Distribution", "message": "Bonjour, je souhaite revendre vos produits à Gitega."})
    assert r.status_code == 201, r.text
    wa = r.json()["data"]["whatsapp"]
    assert wa["url"].startswith("https://wa.me/") and "+25779456124" in wa["message"] and "Burundi" in wa["message"]
    mail = next(m for m in outbox if "Contact : Distribution" in m.subject)
    assert "+25779456124" in mail.text and "Burundi" in mail.text and "Gitega" in mail.text
    assert "<b>" not in wa["message"]


def test_contact_requires_valid_phone_and_subject(client):
    base = {"nom": "Alice", "email": "alice@example.com", "message": "Un message suffisamment long."}
    assert client.post("/api/v1/contact", json=base).status_code == 422
    assert client.post("/api/v1/contact", json={**base, "telephone": "123", "sujet": "Objet"}).status_code == 422
    assert client.post("/api/v1/contact", json={**base, "telephone": "+25779456125", "sujet": "Objet",
                                                "pays_id": 999999}).status_code == 422


# ───────────── newsletter ─────────────

def test_newsletter_double_opt_in(client, db, outbox):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    assert client.post("/api/v1/newsletter/inscription", json={"email": email}).status_code == 201
    sub = db.scalar(select(NewsletterSubscriber).where(NewsletterSubscriber.email == email))
    assert sub.statut == "en_attente"
    mail = next(m for m in outbox if m.to == email)
    confirm = re.search(r"/api/v1/newsletter/confirmer/[0-9a-f]+", mail.text).group(0)
    assert "Inscription confirmée" in client.get(confirm).text
    db.refresh(sub)
    assert sub.statut == "confirme"
    unsubscribe = re.search(r"/api/v1/newsletter/desinscrire/[0-9a-f]+", mail.text).group(0)
    assert "Désinscription" in client.get(unsubscribe).text
    db.refresh(sub)
    assert sub.statut == "desinscrit"


def test_old_subscribers_need_reconfirmation(client, db, admin_h, outbox):
    pending = db.query(NewsletterSubscriber).filter(NewsletterSubscriber.statut == "a_reconfirmer").count()
    assert pending >= 45
    with_email = db.query(NewsletterSubscriber).filter(NewsletterSubscriber.statut == "a_reconfirmer",
                                                       NewsletterSubscriber.email.is_not(None)).count()
    r = client.post(f"{ADMIN}/newsletter/reconfirmation", headers=admin_h)
    assert r.json()["data"]["emails"] == with_email  # uniquement sur action de l'admin
    export = client.get(f"{ADMIN}/newsletter/export.csv", headers=admin_h).text
    assert export.count("\n") >= 1  # seulement les confirmés


# ───────────── visites et tableau de bord ─────────────

def test_visits_are_anonymous(client, db, admin_h):
    r = client.post("/api/v1/stats/visite", json={"page": "/pages/home"}, headers={"User-Agent": "Mozilla/5.0 (Android) Mobile"})
    assert r.status_code == 204
    row = db.scalar(select(VisitorLog).where(VisitorLog.page == "/pages/home").order_by(VisitorLog.id.desc()))
    assert row.ip_address == "" and row.user_agent is None and row.device == "Mobile" and len(row.visitor_hash) == 32
    client.post("/api/v1/stats/visite", json={"page": "/x"}, headers={"User-Agent": "Googlebot/2.1"})
    assert db.scalar(select(VisitorLog).where(VisitorLog.page == "/x")) is None  # robots ignorés
    stats = client.get(f"{ADMIN}/visites", headers=admin_h, params={"jours": 7}).json()["data"]
    # les vraies visites du site comptent aussi : on vérifie la forme, pas le classement
    assert stats["visites"] >= 1 and isinstance(stats["pages"], list)


def test_old_visits_anonymized(db):
    assert db.query(VisitorLog).filter(VisitorLog.ip_address != "").count() == 0


def test_dashboard(client, admin_h):
    data = client.get("/api/v1/admin/tableau-de-bord", headers=admin_h).json()["data"]
    assert data["utilisateurs"]["total"] >= 140 and data["commandes"]["en_attente"] >= 35
    assert data["newsletter"]["a_reconfirmer"] >= 45 and "en_attente_conseiller" in data["chatbot"]


def test_admin_only(client, db):
    u = _user(db)
    h = _auth(client.post("/api/v1/auth/login", json={"login": u.email, "password": PASSWORD}).json()["data"]["tokens"]["access_token"])
    for path in (f"{ADMIN}/pages", f"{ADMIN}/messages", f"{ADMIN}/parametres", "/api/v1/admin/tableau-de-bord"):
        assert client.get(path, headers=h).status_code == 403

