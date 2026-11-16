"""Couvre les opérations qui n'étaient appelées par aucun autre test : chaque endpoint de l'API est ainsi exercé
au moins une fois (mesure : ROUTE_COVERAGE=fichier pytest)."""

import io
import re
import uuid
from datetime import date, datetime, timedelta

import pytest
from PIL import Image
from sqlalchemy import select

from app.core.helpers import now
from app.core.security import hash_password
from app.models import ChatbotConversation, Investor, MediaComment, Medecin, Role, User
from app.modules.chatbot import providers
from app.modules.chatbot.providers import ChatResult, Usage

PASSWORD = "MotDePasse123"
BURUNDI = 194
ALL_WEEK = [{"jour": j, "debut": "08:00", "fin": "18:00"}
            for j in ("lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi", "dimanche")]


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


def _user(db, slug="user", type_utilisateur="patient", **extra):
    fields = {"is_active": True, "nom": "Test", "prenom": "Compte", **extra}
    u = User(uuid=str(uuid.uuid4()), email=f"test-{uuid.uuid4().hex[:8]}@example.com", password=hash_password(PASSWORD),
             role_id=db.scalar(select(Role.id).where(Role.slug == slug)), type_utilisateur=type_utilisateur,
             created_at=now(), updated_at=now(), **fields)
    db.add(u)
    db.commit()
    return u


def _login(client, u):
    r = client.post("/api/v1/auth/login", json={"login": u.email, "password": PASSWORD})
    assert r.status_code == 200, r.text
    return _auth(r.json()["data"]["tokens"]["access_token"])


def _png(size=(300, 200)):
    buf = io.BytesIO()
    Image.new("RGB", size, "teal").save(buf, "PNG")
    return buf.getvalue()


def _ok(r, code=200):
    assert r.status_code == code, f"{r.request.method} {r.request.url} → {r.status_code} {r.text[:300]}"
    return r.json()["data"] if r.headers.get("content-type", "").startswith("application/json") else r


@pytest.fixture
def admin(client, db):
    u = _user(db, "admin", "admin")
    return {"user": u, "h": _login(client, u)}


@pytest.fixture
def doctor(client, db, admin):
    body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "nom": "Hakizimana", "prenom": "Eric",
            "specialite": "Pédiatrie", "numero_licence": "BI-777", "prix_usd": "40", "prix_bif": "30000",
            "horaires": ALL_WEEK}
    data = _ok(client.post("/api/v1/admin/medecins", headers=admin["h"], json=body), 201)
    m = db.scalar(select(Medecin).where(Medecin.uuid == data["uuid"]))
    m.user.password = hash_password(PASSWORD)
    db.commit()
    return {"uuid": data["uuid"], "model": m, "h": _login(client, m.user)}


def _booking(client, db, doctor):
    patient = _user(db, pays_id=BURUNDI, telephone="+25779123321")
    h = _login(client, patient)
    day = (date.today() + timedelta(days=1)).isoformat()
    slot = client.get(f"/api/v1/medecins/{doctor['uuid']}/creneaux", params={"du": day, "au": day}).json()["data"][0]
    body = {"medecin_uuid": doctor["uuid"], "debut": slot["debut"], "symptomes": "Fièvre et toux depuis trois jours.",
            "age": 30, "poids": "60", "taille": "165", "pays_residence_id": BURUNDI, "mode_paiement_id": 3}
    return h, _ok(client.post("/api/v1/consultations", headers=h, json=body), 201)["numero"]


# ───────────── comptes ─────────────

def test_auth_email_confirm_post_and_newsletter_unsubscribe_post(client, db, outbox):
    u = _user(db)
    h = _login(client, u)
    new = f"test-{uuid.uuid4().hex[:8]}@example.com"
    _ok(client.post("/api/v1/auth/me/email", headers=h, json={"nouvel_email": new, "mot_de_passe": PASSWORD}))
    token = re.search(r"/email/confirmer/(\S+)", next(m for m in outbox if m.to == new).text).group(1)
    assert _ok(client.post("/api/v1/auth/email/confirmer", json={"jeton": token}))["email"] == new

    client.post("/api/v1/newsletter/inscription", json={"email": new})
    jeton = re.search(r"/newsletter/desinscrire/([0-9a-f]+)", outbox[-1].text).group(1)
    _ok(client.post("/api/v1/newsletter/desinscription", json={"jeton": jeton}))


def test_admin_user_photo_delete_and_roles_detail(client, db, admin):
    target = _user(db)
    _ok(client.post(f"/api/v1/admin/users/{target.uuid}/photo", headers=admin["h"], files={"photo": ("p.png", _png(), "image/png")}))
    assert _ok(client.delete(f"/api/v1/admin/users/{target.uuid}/photo", headers=admin["h"]))["photo_url"] is None
    catalog = _ok(client.get("/api/v1/admin/roles/permissions", headers=admin["h"]))
    assert any(m["key"] == "users" for m in catalog["modules"])
    role_id = db.scalar(select(Role.id).where(Role.slug == "user"))
    assert _ok(client.get(f"/api/v1/admin/roles/{role_id}", headers=admin["h"]))["slug"] == "user"


# ───────────── consultations et médecins ─────────────

def test_doctor_profile_absences_and_admin_doctor_views(client, db, admin, doctor):
    profile = _ok(client.get("/api/v1/medecin/profil", headers=doctor["h"]))
    assert profile["specialite"] == "Pédiatrie"
    assert _ok(client.patch("/api/v1/medecin/profil", headers=doctor["h"], json={"bio": "Pédiatre à Gitega"}))["bio"]
    start = (datetime.now() + timedelta(days=10)).replace(microsecond=0)
    absence = _ok(client.post("/api/v1/medecin/absences", headers=doctor["h"],
                              json={"debut": start.isoformat(), "fin": (start + timedelta(days=2)).isoformat()}), 201)
    assert len(_ok(client.get("/api/v1/medecin/absences", headers=doctor["h"]))) == 1

    assert any(m["uuid"] == doctor["uuid"] for m in _ok(client.get("/api/v1/admin/medecins", headers=admin["h"])))
    assert _ok(client.get(f"/api/v1/admin/medecins/{doctor['uuid']}", headers=admin["h"]))["numero_licence"] == "BI-777"
    assert _ok(client.get(f"/api/v1/admin/medecins/{doctor['uuid']}/absences", headers=admin["h"]))[0]["id"] == absence["id"]
    other = _ok(client.post(f"/api/v1/admin/medecins/{doctor['uuid']}/absences", headers=admin["h"],
                            json={"debut": (start + timedelta(days=20)).isoformat(),
                                  "fin": (start + timedelta(days=21)).isoformat(), "motif": "Formation"}), 201)
    _ok(client.delete(f"/api/v1/admin/medecins/{doctor['uuid']}/absences/{other['id']}", headers=admin["h"]))
    _ok(client.delete(f"/api/v1/medecin/absences/{absence['id']}", headers=doctor["h"]))
    assert _ok(client.post(f"/api/v1/admin/medecins/{doctor['uuid']}/licence", headers=admin["h"],
                           json={"verifiee": True}))["licence_verifiee"] is True
    horaires = _ok(client.put(f"/api/v1/admin/medecins/{doctor['uuid']}/horaires", headers=admin["h"],
                              json=[{"jour": "lundi", "debut": "09:00", "fin": "12:00"}]))["horaires"]
    assert horaires == [{"jour": "lundi", "debut": "09:00:00", "fin": "12:00:00"}]


def test_consultation_remaining_flows(client, db, admin, doctor):
    h, numero = _booking(client, db, doctor)
    assert _ok(client.get("/api/v1/consultations", headers=h))["meta"]["total"] == 1
    _ok(client.post(f"/api/v1/medecin/consultations/{numero}/confirmer", headers=doctor["h"]))
    ics = client.get(f"/api/v1/medecin/consultations/{numero}/agenda.ics", headers=doctor["h"])
    assert ics.status_code == 200 and "BEGIN:VCALENDAR" in ics.text
    # Lien vidéo côté médecin : refusé tant que le paiement n'est pas validé (comportement attendu)
    assert client.get(f"/api/v1/medecin/consultations/{numero}/rejoindre", headers=doctor["h"]).json()["error"]["code"] == "PAYMENT_REQUIRED"
    assert _ok(client.get("/api/v1/admin/consultations", headers=admin["h"]))["meta"]["total"] >= 1
    # Ordonnance : pendant ou après la consultation
    from app.models import Consultation

    c = db.scalar(select(Consultation).where(Consultation.numero_consultation == numero))
    c.date_souhaitee = now() + timedelta(minutes=5)
    db.commit()
    _ok(client.post(f"/api/v1/medecin/consultations/{numero}/demarrer", headers=doctor["h"]))
    doc = _ok(client.post(f"/api/v1/medecin/consultations/{numero}/ordonnance", headers=doctor["h"],
                          files={"fichier": ("ordonnance.pdf", b"%PDF-1.4 ordonnance", "application/pdf")}), 201)
    assert doc["kind"] == "ordonnance"
    # Annulation par l'admin d'une autre consultation
    _, other = _booking(client, db, doctor)
    assert _ok(client.post(f"/api/v1/admin/consultations/{other}/annuler", headers=admin["h"],
                           json={"motif": "Médecin indisponible"}))["statut"] == "annulee"


def test_payment_account_update(client, admin):
    acc = _ok(client.post("/api/v1/admin/modes-paiement", headers=admin["h"], json={"nom": "Lumicash test"}), 201)
    updated = _ok(client.put(f"/api/v1/admin/modes-paiement/{acc['id']}", headers=admin["h"],
                             json={"nom": "Lumicash", "numero_compte": "+257 65 000 000", "nom_compte": "NUFOTEC"}))
    assert updated["numero_compte"] == "+257 65 000 000"


# ───────────── investisseurs et courtiers ─────────────

def test_investor_and_broker_remaining(client, db, admin):
    u = _user(db, type_utilisateur="broker", email_verified_at=now())
    client.post("/api/v1/courtiers/candidature", json={
        "nom_complet": "Alice Courtier", "cabinet": "Lake Advisory", "pays_id": BURUNDI, "email": u.email,
        "confirme_autorise": True, "confirme_aml_kyc": True, "reconnait_non_exclusivite": True,
        "comprend_mandat_formel": True})
    h = _login(client, u)
    broker = _ok(client.get("/api/v1/courtier/dossier", headers=h))
    assert broker["statut"] == "en_attente"
    detail = _ok(client.get(f"/api/v1/admin/courtiers/{broker['uuid']}", headers=admin["h"]))
    assert detail["presentations"] == []

    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    client.post("/api/v1/investisseurs/manifestation", json={
        "nom_complet": "Jean Investisseur", "pays_id": BURUNDI, "email": email, "accepte_contact": True,
        "confirmation_non_engageante": True})
    inv = db.scalar(select(Investor).where(Investor.email == email))
    assert _ok(client.patch(f"/api/v1/admin/investisseurs/{inv.uuid}", headers=admin["h"],
                            json={"organisation": "Kibira Fund", "montant": "5M+"}))["organisation"] == "Kibira Fund"
    phase = _ok(client.post("/api/v1/admin/investissement/phases", headers=admin["h"],
                            json={"nom": "Phase test", "repartition": {"construction": 100}}), 201)
    edited = _ok(client.put(f"/api/v1/admin/investissement/phases/{phase['id']}", headers=admin["h"],
                            json={"nom": "Phase test 2", "repartition": {"construction": 50, "rd": 50}}))
    assert edited["nom"] == "Phase test 2" and len(edited["repartition"]) == 2
    _ok(client.delete(f"/api/v1/admin/investissement/phases/{phase['id']}", headers=admin["h"]))


# ───────────── produits et commandes ─────────────

def test_shop_remaining(client, admin):
    products = _ok(client.get("/api/v1/admin/produits", headers=admin["h"]))
    assert products["meta"]["total"] >= 6
    p = _ok(client.post("/api/v1/admin/produits", headers=admin["h"], json={"titre": "Spiruline test", "prix_bif": "9000"}), 201)
    gallery = _ok(client.post(f"/api/v1/admin/produits/{p['uuid']}/galerie", headers=admin["h"],
                              files={"image": ("g.png", _png(), "image/png")}))["galerie"]
    assert not _ok(client.delete(f"/api/v1/admin/produits/{p['uuid']}/galerie/{gallery[0]['id']}", headers=admin["h"]))["galerie"]
    cat = _ok(client.post("/api/v1/admin/produits/categories", headers=admin["h"], json={"nom": "Algues test"}), 201)
    assert _ok(client.put(f"/api/v1/admin/produits/categories/{cat['id']}", headers=admin["h"],
                          json={"nom": "Algues et spiruline"}))["slug"] == "algues-et-spiruline"
    numero = _ok(client.post(f"/api/v1/produits/{p['slug']}/commander", json={
        "nom_complet": "Client Test", "telephone": "79 000 111", "pays_id": BURUNDI, "ville": "Ngozi",
        "adresse": "Centre-ville"}), 201)["commande"]["numero"]
    _ok(client.post(f"/api/v1/admin/commandes/{numero}/notes", headers=admin["h"], json={"texte": "Rappeler demain"}))
    history = _ok(client.get(f"/api/v1/admin/commandes/{numero}", headers=admin["h"]))["historique"]
    assert any(h["note"] == "Rappeler demain" for h in history)


# ───────────── médias ─────────────

def test_media_remaining(client, db, admin):
    link = _ok(client.post("/api/v1/admin/medias/liens", headers=admin["h"],
                           json={"titre": "Vidéo test", "url": "https://youtu.be/Hf1kPp7yWcI"}), 201)
    assert _ok(client.get(f"/api/v1/admin/medias/{link['uuid']}", headers=admin["h"]))["youtube_id"] == "Hf1kPp7yWcI"
    col = _ok(client.post("/api/v1/admin/medias/collections", headers=admin["h"],
                          json={"titre": "Série test", "medias": [link["uuid"]]}), 201)
    assert _ok(client.put(f"/api/v1/admin/medias/collections/{col['uuid']}", headers=admin["h"],
                          json={"titre": "Série test 2", "medias": []}))["episodes"] == 0
    _ok(client.delete(f"/api/v1/admin/medias/collections/{col['uuid']}", headers=admin["h"]))
    # Retraiter : réservé aux audios et vidéos (un lien est refusé proprement)
    assert client.post(f"/api/v1/admin/medias/{link['uuid']}/retraiter", headers=admin["h"]).json()["error"]["code"] == "NOT_PROCESSABLE"
    # Refuser un commentaire
    listener = _user(db)
    h = _login(client, listener)
    _ok(client.post(f"/api/v1/medias/{link['slug']}/commentaires", headers=h, json={"texte": "Très instructif, merci."}), 201)
    comment = db.scalar(select(MediaComment).where(MediaComment.user_id == listener.id))
    assert _ok(client.post(f"/api/v1/admin/medias/commentaires/{comment.id}/refuser", headers=admin["h"]))["statut"] == "refuse"


# ───────────── contenu du site ─────────────

def test_content_crud_remaining(client, db, admin):
    h = admin["h"]
    base = "/api/v1/admin/contenu"
    page = _ok(client.post(f"{base}/pages", headers=h, json={"titre": "Page test"}), 201)
    assert _ok(client.get(f"{base}/pages/{page['id']}", headers=h))["slug"] == "page-test"
    _ok(client.put(f"{base}/pages/{page['id']}", headers=h, json={"titre": "Page test modifiée", "slug": "page-test"}))
    section = _ok(client.post(f"{base}/pages/{page['id']}/sections", headers=h, json={"titre": "Intro"}), 201)
    assert _ok(client.put(f"{base}/sections/{section['id']}", headers=h, json={"type": "hero", "titre": "Bienvenue"}))["titre"] == "Bienvenue"
    _ok(client.delete(f"{base}/sections/{section['id']}", headers=h))

    article = _ok(client.post(f"{base}/actualites", headers=h, json={"titre": "Article test"}), 201)
    assert _ok(client.get(f"{base}/actualites", headers=h))["meta"]["total"] >= 1
    assert _ok(client.post(f"{base}/actualites/{article['id']}/image", headers=h,
                           files={"image": ("a.png", _png((1200, 800)), "image/png")}))["image"]
    _ok(client.delete(f"{base}/actualites/{article['id']}", headers=h))

    for path, body, update in (
        ("chiffres-cles", {"etiquette": "Hectares", "valeur": "500"}, {"etiquette": "Hectares", "valeur": "600"}),
        ("temoignages", {"titre": "Témoignage test", "video_url": "https://youtu.be/Hf1kPp7yWcI"},
         {"titre": "Témoignage modifié", "video_url": "https://youtu.be/Hf1kPp7yWcI", "approuve": True}),
        ("partenaires", {"nom": "Partenaire test", "type": "fournisseur"},
         {"nom": "Partenaire modifié", "type": "financier", "niveau": "commercial"}),
        ("reseaux-sociaux", {"plateforme": "tiktok", "url": "https://tiktok.com/@nufotec"},
         {"plateforme": "tiktok", "url": "https://tiktok.com/@nufotec_bi"}),
        ("faq", {"question": "Question test ?", "reponse": "Réponse"}, {"question": "Question test ?", "reponse": "Nouvelle"}),
    ):
        item = _ok(client.post(f"{base}/{path}", headers=h, json=body), 201)
        assert isinstance(_ok(client.get(f"{base}/{path}", headers=h)), list)
        _ok(client.put(f"{base}/{path}/{item['id']}", headers=h, json=update))
        if path in ("temoignages", "partenaires"):
            _ok(client.post(f"{base}/{path}/{item['id']}/image", headers=h, files={"image": ("i.png", _png(), "image/png")}))
        _ok(client.delete(f"{base}/{path}/{item['id']}", headers=h))

    logo = next(s for s in _ok(client.get(f"{base}/parametres", headers=h)) if s["type"] == "image")
    assert _ok(client.post(f"{base}/parametres/{logo['cle']}/image", headers=h, files={"image": ("l.png", _png(), "image/png")}))

    client.post("/api/v1/contact", json={"nom": "Visiteur", "email": "visiteur@example.com", "telephone": "+25779456126",
                                         "sujet": "Test", "message": "Message à supprimer ensuite."})
    msg = _ok(client.get(f"{base}/messages", headers=h))["items"][0]
    _ok(client.delete(f"{base}/messages/{msg['id']}", headers=h))
    subs = _ok(client.get(f"{base}/newsletter", headers=h))
    assert subs["meta"]["total"] >= 45
    _ok(client.delete(f"{base}/newsletter/{subs['items'][0]['id']}", headers=h))
    _ok(client.delete(f"{base}/pages/{page['id']}", headers=h))


# ───────────── chatbot (en pause, mais chaque endpoint répond) ─────────────

@pytest.fixture
def llm(monkeypatch):
    monkeypatch.setattr(providers, "chat", lambda p, m, msgs, t, mx: ChatResult("ok", Usage(1, 1)))

    def stream(p, m, msgs, t, mx, usage):
        yield "ok"

    monkeypatch.setattr(providers, "stream_chat", stream)
    monkeypatch.setattr(providers, "list_models", lambda p: ["m"])


def test_chatbot_remaining(client, db, admin, llm):
    h = admin["h"]
    base = "/api/v1/admin/chatbot"
    prov = _ok(client.post(f"{base}/fournisseurs", headers=h, json={"nom": "Fournisseur test", "type": "ollama"}), 201)
    assert _ok(client.patch(f"{base}/fournisseurs/{prov['id']}", headers=h, json={"actif": False}))["actif"] is False
    art = _ok(client.post(f"{base}/articles", headers=h, json={"titre": "Horaires", "contenu": "Ouvert de 8 h à 17 h."}), 201)
    assert _ok(client.get(f"{base}/articles", headers=h))["meta"]["total"] >= 1
    _ok(client.put(f"{base}/articles/{art['id']}", headers=h, json={"titre": "Horaires", "contenu": "Ouvert de 8 h à 18 h."}))
    _ok(client.delete(f"{base}/articles/{art['id']}", headers=h))
    sources = _ok(client.get(f"{base}/sources", headers=h))
    kb = next(s for s in sources if s["type"] == "kb")
    _ok(client.post(f"{base}/sources/{kb['id']}/indexer", headers=h))
    _ok(client.patch(f"{base}/sources/{kb['id']}", headers=h, params={"actif": True}))
    rule = _ok(client.post(f"{base}/tables", headers=h, json={"table": "faq", "colonnes": ["question", "reponse"]}), 201)
    assert any(r["id"] == rule["id"] for r in _ok(client.get(f"{base}/tables/regles", headers=h)))
    _ok(client.put(f"{base}/tables/{rule['id']}", headers=h, json={"table": "faq", "colonnes": ["question"]}))
    _ok(client.delete(f"{base}/sources/{rule['source']['id']}", headers=h))

    token = _ok(client.post("/api/v1/chatbot/conversations", json={}), 201)["jeton"]
    _ok(client.post(f"/api/v1/chatbot/conversations/{token}/conseiller", json={"motif": "Question précise"}))
    conv = db.scalar(select(ChatbotConversation).order_by(ChatbotConversation.id.desc()))
    assert _ok(client.get(f"{base}/conversations/{conv.id}", headers=h))["statut"] == "pending_human"
    assert _ok(client.post(f"{base}/conversations/{conv.id}/rendre-au-bot", headers=h))["statut"] == "bot"
    assert _ok(client.post(f"{base}/conversations/{conv.id}/fermer", headers=h))["statut"] == "closed"
