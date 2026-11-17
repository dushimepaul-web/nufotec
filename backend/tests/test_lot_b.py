"""Lot B : en-têtes de sécurité, taille maximale, limite de débit, changement d'email confirmé, alertes,
purge manuelle, comptes de paiement (affichés dans WhatsApp), journal d'activité, dates avec fuseau."""

import re
import uuid
from datetime import timedelta

import pytest
from sqlalchemy import select

from app.core.helpers import now
from app.core.protection import limiter
from app.core.security import hash_password
from app.models import Role, User

PASSWORD = "MotDePasse123"


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


def _user(db, slug="user", type_utilisateur="patient", **extra):
    fields = {"is_active": True, **extra}
    u = User(uuid=str(uuid.uuid4()), email=f"test-{uuid.uuid4().hex[:8]}@example.com", password=hash_password(PASSWORD),
             nom="Test", prenom="Compte", role_id=db.scalar(select(Role.id).where(Role.slug == slug)),
             type_utilisateur=type_utilisateur, created_at=now(), updated_at=now(), **fields)
    db.add(u)
    db.commit()
    return u


def _login(client, u, **headers):
    r = client.post("/api/v1/auth/login", json={"login": u.email, "password": PASSWORD}, headers=headers)
    assert r.status_code == 200, r.text
    return _auth(r.json()["data"]["tokens"]["access_token"])


@pytest.fixture
def admin_h(client, db):
    return _login(client, _user(db, "admin", "admin"))


# ───────────── protections globales ─────────────

def test_security_headers(client):
    r = client.get("/api/v1/produits")
    assert r.headers["x-content-type-options"] == "nosniff" and r.headers["x-frame-options"] == "DENY"
    assert r.headers["content-security-policy"].startswith("default-src 'none'")
    assert "content-security-policy" not in client.get("/docs").headers  # Swagger doit rester utilisable


def test_payload_too_large(client):
    r = client.post("/api/v1/contact", content=b"x", headers={"Content-Length": str(61 * 1024 * 1024),
                                                               "Content-Type": "application/json"})
    assert r.status_code == 413 and r.json()["error"]["code"] == "PAYLOAD_TOO_LARGE"


def test_rate_limit_per_ip(client, monkeypatch):
    from app.core import config

    limiter.reset()
    monkeypatch.setattr(config.get_settings(), "rate_limit_ip_per_minute", 5)
    codes = [client.get("/api/v1/produits/categories").status_code for _ in range(7)]
    assert codes[:5] == [200] * 5 and codes[5] == 429
    r = client.get("/api/v1/produits/categories")
    assert int(r.headers["Retry-After"]) >= 1
    assert client.get("/api/v1/health").status_code == 200  # santé jamais limitée
    limiter.reset()


# ───────────── changement d'email ─────────────

def test_email_change_confirmed_by_new_address(client, db, outbox):
    u = _user(db)
    old = u.email
    h = _login(client, u)
    new = f"test-{uuid.uuid4().hex[:8]}@example.com"
    assert client.post("/api/v1/auth/me/email", headers=h, json={"nouvel_email": new, "mot_de_passe": "faux"}).status_code == 400
    r = client.post("/api/v1/auth/me/email", headers=h, json={"nouvel_email": new.upper(), "mot_de_passe": PASSWORD})
    assert r.status_code == 200
    assert client.get("/api/v1/auth/me", headers=h).json()["data"]["email"] == old  # rien ne change avant confirmation
    assert any(m.to == old and "changement" in m.subject.lower() for m in outbox)  # alerte à l'ancienne adresse
    mail = next(m for m in outbox if m.to == new)
    link = re.search(r"/api/v1/auth/email/confirmer/\S+", mail.text).group(0)
    assert "Adresse changée" in client.get(link).text
    me = client.get("/api/v1/auth/me", headers=h).json()["data"]
    assert me["email"] == new and me["email_verified"] is True
    assert "Lien invalide" in client.get(link).text  # usage unique


def test_email_change_refuses_taken_address(client, db):
    a, b = _user(db), _user(db)
    r = client.post("/api/v1/auth/me/email", headers=_login(client, a), json={"nouvel_email": b.email, "mot_de_passe": PASSWORD})
    assert r.status_code == 409 and r.json()["error"]["code"] == "EMAIL_TAKEN"


# ───────────── alertes ─────────────

def test_new_device_alert(client, db, outbox):
    u = _user(db)
    _login(client, u, **{"User-Agent": "Mozilla/5.0 (Windows NT 10.0) Chrome/130"})  # première connexion : pas d'alerte
    assert not any("Nouvelle connexion" in m.subject for m in outbox)
    _login(client, u, **{"User-Agent": "Mozilla/5.0 (Windows NT 10.0) Chrome/130"})  # même appareil
    assert not any("Nouvelle connexion" in m.subject for m in outbox)
    _login(client, u, **{"User-Agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 17) Mobile Safari"})  # nouvel appareil
    alert = next(m for m in outbox if "Nouvelle connexion" in m.subject)
    assert alert.to == u.email and "iOS" in alert.text


def test_password_and_deletion_alerts(client, db, outbox):
    u = _user(db)
    email = u.email
    h = _login(client, u)
    client.post("/api/v1/auth/change-password", headers=h, json={"current_password": PASSWORD, "new_password": "NouveauMdp789"})
    assert any(m.to == email and "mot de passe" in m.subject.lower() for m in outbox)
    client.request("DELETE", "/api/v1/auth/me", headers=h, json={"password": "NouveauMdp789"})
    assert any(m.to == email and "supprimé" in m.subject for m in outbox)  # envoyé à l'adresse d'origine


# ───────────── purge manuelle ─────────────

def test_manual_purge_of_old_deleted_accounts(client, db, admin_h):
    old = _user(db, deleted_at=now() - timedelta(days=40), is_active=False)
    recent = _user(db, deleted_at=now() - timedelta(days=5), is_active=False)
    r = client.post("/api/v1/admin/users/purger", headers=admin_h, json={})
    assert r.status_code == 200 and r.json()["data"]["anonymises"] >= 1
    db.refresh(old)
    db.refresh(recent)
    assert old.email.startswith("deleted+") and old.nom == "Utilisateur"
    assert not recent.email.startswith("deleted+")  # moins de 30 jours : restaurable


# ───────────── comptes de paiement ─────────────

def test_payment_accounts_admin_and_whatsapp(client, admin_h):
    r = client.post("/api/v1/admin/modes-paiement", headers=admin_h,
                    json={"nom": "Ihela", "numero_compte": "+257 61 000 111", "nom_compte": "NUFOTEC SARL"})
    assert r.status_code == 201
    acc = r.json()["data"]
    assert any(m["nom"] == "Ihela" for m in client.get("/api/v1/modes-paiement").json()["data"])
    client.delete(f"/api/v1/admin/modes-paiement/{acc['id']}", headers=admin_h)
    assert all(m["nom"] != "Ihela" for m in client.get("/api/v1/modes-paiement").json()["data"])
    listing = client.get("/api/v1/admin/modes-paiement", headers=admin_h).json()["data"]
    assert next(m for m in listing if m["id"] == acc["id"])["actif"] is False  # conservé, désactivé


# ───────────── journal d'activité ─────────────

def test_global_activity_log(client, db, admin_h):
    client.post("/api/v1/admin/modes-paiement", headers=admin_h, json={"nom": "Test journal"})
    target = _user(db)
    client.post(f"/api/v1/admin/users/{target.uuid}/kyc", headers=admin_h, json={"est_verifie": True})
    log = client.get("/api/v1/admin/journal", headers=admin_h, params={"module": "users"}).json()["data"]
    entry = next(e for e in log["items"] if e["action"] == "user_kyc")
    assert entry["auteur"] == "Compte Test" and entry["le"].endswith("+02:00")
    modules = {m["module"] for m in client.get("/api/v1/admin/journal/modules", headers=admin_h).json()["data"]}
    assert "users" in modules
    client.post("/api/v1/auth/login", json={"login": "inconnu@example.com", "password": "Faux12345"})
    sec = client.get("/api/v1/admin/journal/securite", headers=admin_h, params={"action": "auth.login_failed"}).json()["data"]
    assert sec["items"] and sec["items"][0]["niveau"] == "warning"


def test_journal_admin_only(client, db):
    h = _login(client, _user(db))
    assert client.get("/api/v1/admin/journal", headers=h).status_code == 403


# ───────────── dates : toujours avec fuseau ─────────────

def test_dates_in_free_dicts_have_timezone(client, admin_h):
    pages = client.get("/api/v1/admin/contenu/pages", headers=admin_h).json()["data"]
    assert pages[0]["modifiee_le"].endswith("+02:00")


def test_json_is_gzipped_but_media_streams_are_not(client):
    r = client.get("/api/v1/produits", params={"per_page": 12}, headers={"Accept-Encoding": "gzip"})
    assert r.status_code == 200 and r.headers.get("content-encoding") == "gzip"
    from app.core.compression import RAW_PATHS
    assert RAW_PATHS.search("/api/v1/medias/une-emission/flux") and RAW_PATHS.search("/api/v1/medias/x/apercu")
    assert not RAW_PATHS.search("/api/v1/medias/x")
