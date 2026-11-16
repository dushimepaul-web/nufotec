"""Module Utilisateurs et rôles (admin)."""

import io
import re
import uuid

import pytest
from PIL import Image
from sqlalchemy import select

from app.core.helpers import now
from app.core.security import hash_password
from app.models import Role, User, UserActivity

PASSWORD = "MotDePasse123"
API = "/api/v1/admin"


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


def _role_id(db, slug):
    return db.scalar(select(Role.id).where(Role.slug == slug))


def _make_user(db, slug="user", type_utilisateur="patient", **extra):
    fields = {"nom": "Test", "prenom": "Compte", **extra}
    user = User(uuid=str(uuid.uuid4()), email=f"test-{uuid.uuid4().hex[:8]}@example.com",
                password=hash_password(PASSWORD), role_id=_role_id(db, slug),
                type_utilisateur=type_utilisateur, is_active=True, created_at=now(), updated_at=now(), **fields)
    db.add(user)
    db.commit()
    db.refresh(user)
    return user


def _token(client, user):
    r = client.post("/api/v1/auth/login", json={"login": user.email, "password": PASSWORD})
    assert r.status_code == 200, r.text
    return r.json()["data"]["tokens"]["access_token"]


@pytest.fixture
def admin(db):
    return _make_user(db, "admin", "admin")


@pytest.fixture
def admin_h(client, admin):
    return _auth(_token(client, admin))


# ───────────── accès ─────────────

def test_admin_routes_require_permission(client, db):
    patient = _make_user(db)
    h = _auth(_token(client, patient))
    assert client.get(f"{API}/users", headers=h).status_code == 403
    assert client.get(f"{API}/roles", headers=h).status_code == 403
    assert client.get(f"{API}/users").status_code == 401


# ───────────── liste, recherche, détail ─────────────

def test_list_search_filter_paginate(client, db, admin_h):
    target = _make_user(db, nom="Zygomatix", telephone="+25779123987")
    r = client.get(f"{API}/users", headers=admin_h, params={"q": "zygomatix"})
    data = r.json()["data"]
    assert r.status_code == 200 and data["meta"]["total"] == 1
    assert data["items"][0]["uuid"] == target.uuid

    r = client.get(f"{API}/users", headers=admin_h, params={"q": "79 123 987"})
    assert any(i["uuid"] == target.uuid for i in r.json()["data"]["items"])

    r = client.get(f"{API}/users", headers=admin_h, params={"per_page": 5, "page": 2, "type_utilisateur": "patient"})
    meta = r.json()["data"]["meta"]
    assert meta["per_page"] == 5 and meta["page"] == 2 and len(r.json()["data"]["items"]) <= 5

    # % est pris littéralement, il ne renvoie pas tout le monde
    assert client.get(f"{API}/users", headers=admin_h, params={"q": "%"}).json()["data"]["meta"]["total"] == 0


def test_detail_uses_uuid_not_id(client, db, admin_h):
    target = _make_user(db)
    r = client.get(f"{API}/users/{target.uuid}", headers=admin_h)
    assert r.status_code == 200 and "id" not in r.json()["data"]
    assert client.get(f"{API}/users/{target.id}", headers=admin_h).status_code == 404


def test_stats(client, db, admin_h):
    data = client.get(f"{API}/users/stats", headers=admin_h).json()["data"]
    alive = db.query(User).filter(User.deleted_at.is_(None)).count()
    assert data["total"] == alive and data["supprimes"] >= 1 and "patient" in data["par_type"]
    assert data["actifs"] + data["inactifs"] == data["total"]


def test_export_csv_safe(client, db, admin_h):
    _make_user(db, nom="=HYPERLINK(evil)")
    r = client.get(f"{API}/users/export.csv", headers=admin_h, params={"q": "HYPERLINK"})
    assert r.status_code == 200 and r.headers["content-type"].startswith("text/csv")
    body = r.text
    assert body.startswith("﻿uuid;nom")
    assert "'=HYPERLINK(evil)" in body  # formule neutralisée


# ───────────── création ─────────────

def test_create_user_sends_invitation(client, db, admin_h, outbox):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    body = {"email": email, "nom": "dupont", "prenom": "marie", "type_utilisateur": "investisseur", "telephone": "79 222 333", "interet_investissement": "50000.00"}
    r = client.post(f"{API}/users", headers=admin_h, json=body)
    assert r.status_code == 201, r.text
    data = r.json()["data"]
    assert data["nom"] == "Dupont" and data["telephone"] == "+25779222333"
    assert data["role"]["slug"] == "user"

    mail = next(m for m in outbox if m.to == email)
    code = re.search(r"\b(\d{6})\b", mail.text).group(1)
    r = client.post("/api/v1/auth/password/reset", json={"email": email, "code": code, "new_password": "ChoisiPar1Moi"})
    assert r.status_code == 200, r.text
    assert client.post("/api/v1/auth/login", json={"login": email, "password": "ChoisiPar1Moi"}).status_code == 200


def test_role_follows_type(client, db, admin_h):
    def create(type_utilisateur, **extra):
        body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "nom": "A", "prenom": "B",
                "type_utilisateur": type_utilisateur, **extra}
        return client.post(f"{API}/users", headers=admin_h, json=body)

    assert create("medecin").json()["data"]["role"]["slug"] == "medecin"
    assert create("admin").json()["data"]["role"]["slug"] == "admin"
    for t in ("patient", "entreprise", "investisseur", "partenaire", "broker"):
        assert create(t).json()["data"]["role"]["slug"] == "user", t
    # role_id explicite : il est honoré (rôle dynamique choisi par l'administrateur)
    assert create("patient", role_id=_role_id(db, "admin")).json()["data"]["role"]["slug"] == "admin"


def test_legacy_inconsistent_account_is_realigned(client, db, admin_h):
    # Comme lycemaranathasda@gmail.com : type medecin mais rôle user
    target = _make_user(db, "user", "medecin")
    r = client.patch(f"{API}/users/{target.uuid}", headers=admin_h, json={"prenom": "paul"})
    assert r.status_code == 200 and r.json()["data"]["role"]["slug"] == "medecin"


def test_create_duplicate_email(client, db, admin_h):
    existing = _make_user(db)
    r = client.post(f"{API}/users", headers=admin_h, json={"email": existing.email, "nom": "A", "prenom": "B",
                                                           "type_utilisateur": "patient"})
    assert r.status_code == 409


# ───────────── modification ─────────────

def test_role_change_closes_sessions(client, db, admin_h):
    target = _make_user(db)
    t = _token(client, target)
    r = client.patch(f"{API}/users/{target.uuid}", headers=admin_h, json={"type_utilisateur": "medecin"})
    assert r.status_code == 200, r.text
    assert r.json()["data"]["role"]["slug"] == "medecin"
    assert client.get("/api/v1/auth/me", headers=_auth(t)).status_code == 401


def test_email_change_resets_verification(client, db, admin_h, outbox):
    target = _make_user(db, email_verified_at=now())
    new_email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    r = client.patch(f"{API}/users/{target.uuid}", headers=admin_h, json={"email": new_email.upper()})
    assert r.status_code == 200
    assert r.json()["data"]["email"] == new_email and r.json()["data"]["email_verified"] is False
    assert any(m.to == new_email for m in outbox)


# ───────────── statut, KYC, reset, photo, sessions ─────────────

def test_deactivate_is_immediate_and_reactivate(client, db, admin_h):
    target = _make_user(db)
    t = _token(client, target)
    r = client.post(f"{API}/users/{target.uuid}/deactivate", headers=admin_h)
    assert r.status_code == 200 and r.json()["data"]["is_active"] is False
    assert client.get("/api/v1/auth/me", headers=_auth(t)).status_code == 401
    assert client.post("/api/v1/auth/login", json={"login": target.email, "password": PASSWORD}).status_code == 403
    client.post(f"{API}/users/{target.uuid}/activate", headers=admin_h)
    assert client.post("/api/v1/auth/login", json={"login": target.email, "password": PASSWORD}).status_code == 200


def test_kyc_and_send_reset(client, db, admin_h, outbox):
    target = _make_user(db)
    r = client.post(f"{API}/users/{target.uuid}/kyc", headers=admin_h, json={"est_verifie": True})
    assert r.json()["data"]["est_verifie"] is True
    assert client.post(f"{API}/users/{target.uuid}/send-reset", headers=admin_h).status_code == 200
    assert any(m.to == target.email and "réinitialisation" in m.subject for m in outbox)


def test_admin_photo_and_force_logout(client, db, admin_h):
    target = _make_user(db)
    t = _token(client, target)
    buf = io.BytesIO()
    Image.new("RGB", (600, 600), "blue").save(buf, "PNG")
    r = client.post(f"{API}/users/{target.uuid}/photo", headers=admin_h,
                    files={"photo": ("p.png", buf.getvalue(), "image/png")})
    assert r.status_code == 200 and r.json()["data"]["photo_url"]
    svg = b'<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
    r = client.post(f"{API}/users/{target.uuid}/photo", headers=admin_h,
                    files={"photo": ("x.svg", svg, "image/svg+xml")})
    assert r.status_code == 422  # le SVG accepté par le PHP est refusé

    sessions = client.get(f"{API}/users/{target.uuid}/sessions", headers=admin_h).json()["data"]
    assert len(sessions) == 1
    r = client.delete(f"{API}/users/{target.uuid}/sessions", headers=admin_h)
    assert r.json()["data"]["closed"] == 1
    assert client.get("/api/v1/auth/me", headers=_auth(t)).status_code == 401


# ───────────── suppression, restauration ─────────────

def test_soft_delete_and_restore(client, db, admin_h):
    target = _make_user(db)
    assert client.delete(f"{API}/users/{target.uuid}", headers=admin_h).status_code == 200
    r = client.get(f"{API}/users", headers=admin_h, params={"statut": "supprime", "q": target.email})
    assert r.json()["data"]["meta"]["total"] == 1
    assert client.post("/api/v1/auth/login", json={"login": target.email, "password": PASSWORD}).status_code == 401
    r = client.post(f"{API}/users/{target.uuid}/restore", headers=admin_h)
    assert r.status_code == 200 and r.json()["data"]["deleted_at"] is None
    assert client.post("/api/v1/auth/login", json={"login": target.email, "password": PASSWORD}).status_code == 200


def test_anonymized_account_cannot_be_restored(client, db, admin_h):
    target = _make_user(db)
    t = _token(client, target)
    client.request("DELETE", "/api/v1/auth/me", headers=_auth(t), json={"password": PASSWORD})
    r = client.post(f"{API}/users/{target.uuid}/restore", headers=admin_h)
    assert r.status_code == 409 and r.json()["error"]["code"] == "ANONYMIZED"


# ───────────── garde-fous admin ─────────────

def test_admin_cannot_act_on_self(client, admin, admin_h):
    assert client.post(f"{API}/users/{admin.uuid}/deactivate", headers=admin_h).json()["error"]["code"] == "SELF_ACTION"
    assert client.delete(f"{API}/users/{admin.uuid}", headers=admin_h).json()["error"]["code"] == "SELF_ACTION"


def test_last_admin_protected(client, db, admin, admin_h):
    # On ne laisse qu'un seul autre admin actif : l'admin de test.
    db.query(User).filter(User.role_id == _role_id(db, "admin"), User.id != admin.id).update(
        {"is_active": False}, synchronize_session=False)
    db.commit()
    second = _make_user(db, "admin", "admin")
    second_h = _auth(_token(client, second))
    # second peut désactiver admin (il en reste un : second)…
    assert client.post(f"{API}/users/{admin.uuid}/deactivate", headers=second_h).status_code == 200
    # …mais le dernier admin actif ne peut être ni rétrogradé ni supprimé par personne
    other = _make_user(db, "admin", "admin")
    other_h = _auth(_token(client, other))
    client.post(f"{API}/users/{other.uuid}/deactivate", headers=second_h)
    r = client.patch(f"{API}/users/{second.uuid}", headers=second_h, json={"type_utilisateur": "patient"})
    assert r.json()["error"]["code"] == "SELF_ACTION"


def test_admin_actions_are_audited(client, db, admin, admin_h):
    target = _make_user(db)
    client.post(f"{API}/users/{target.uuid}/kyc", headers=admin_h, json={"est_verifie": True})
    row = db.scalar(select(UserActivity).where(UserActivity.action == "user_kyc", UserActivity.item_id == target.id))
    assert row.user_id == admin.id and row.module == "users"
    r = client.get(f"{API}/users/{target.uuid}/activities", headers=admin_h)
    assert any(a["action"] == "user_kyc" and a["auteur"] for a in r.json()["data"]["items"])


# ───────────── rôles ─────────────

def test_roles_system_and_custom(client, db, admin_h):
    roles = client.get(f"{API}/roles", headers=admin_h).json()["data"]
    assert {"admin", "medecin", "user"} <= {r["slug"] for r in roles}
    admin_role = next(r for r in roles if r["slug"] == "admin")
    assert admin_role["systeme"] and "users.create" in admin_role["permissions"]
    # Création d'un rôle personnalisé avec des permissions par module
    r = client.post(f"{API}/roles", headers=admin_h,
                    json={"nom": "Gestionnaire test", "permissions": ["users.read", "medias.read"]})
    assert r.status_code == 201, r.text
    created = r.json()["data"]
    assert created["slug"] == "gestionnaire-test" and created["systeme"] is False
    assert set(created["permissions"]) == {"users.read", "medias.read"}
    # Suppression possible tant qu'aucun utilisateur ne porte le rôle
    assert client.delete(f"{API}/roles/{created['id']}", headers=admin_h).status_code == 200
    # Un rôle système est protégé
    assert client.delete(f"{API}/roles/{_role_id(db, 'user')}", headers=admin_h).status_code == 409


def test_custom_role_permissions_are_enforced(client, db, admin_h):
    role = client.post(f"{API}/roles", headers=admin_h,
                       json={"nom": "Lecteur test", "permissions": ["users.read"]}).json()["data"]
    reader = User(uuid=str(uuid.uuid4()), email=f"test-{uuid.uuid4().hex[:8]}@example.com",
                  password=hash_password(PASSWORD), role_id=role["id"], type_utilisateur="patient",
                  is_active=True, created_at=now(), updated_at=now(), nom="Lecteur", prenom="Test")
    db.add(reader)
    db.commit()
    db.refresh(reader)
    h = _auth(_token(client, reader))
    # Lecture autorisée
    assert client.get(f"{API}/users", headers=h).status_code == 200
    # Création et suppression refusées
    body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "nom": "A", "prenom": "B",
            "type_utilisateur": "patient"}
    assert client.post(f"{API}/users", headers=h, json=body).status_code == 403
    assert client.delete(f"{API}/users/{reader.uuid}", headers=h).status_code == 403
    # Aucun droit sur les rôles
    assert client.get(f"{API}/roles", headers=h).status_code == 403


def test_role_display_name_editable_slug_fixed(client, db, admin_h):
    user_role = _role_id(db, "user")
    r = client.patch(f"{API}/roles/{user_role}", headers=admin_h,
                     json={"nom": "Utilisateur simple", "description": "Patients, entreprises, investisseurs…",
                           "niveau": 99, "slug": "autre"})
    data = r.json()["data"]
    assert r.status_code == 200 and data["nom"] == "Utilisateur simple"
    assert data["slug"] == "user" and data["niveau"] == 0  # slug et niveau ignorés
    assert client.patch(f"{API}/roles/{user_role}", headers=admin_h, json={"nom": "Admin"}).status_code == 409


# ───────────── pays ─────────────

def test_pays_public_search(client):
    r = client.get("/api/v1/pays", params={"q": "burundi"})
    assert r.status_code == 200
    items = r.json()["data"]
    assert any(p["iso2"] == "BI" for p in items)
    assert len(client.get("/api/v1/pays").json()["data"]) > 100


# ───────────── qui peut créer un médecin ─────────────

def test_public_signup_is_simple_user_only(client):
    """Inscription publique : uniquement des utilisateurs simples (rôle user)."""
    for t in ("patient", "entreprise", "investisseur", "partenaire", "broker"):
        body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "password": PASSWORD,
                "nom": "A", "prenom": "B", "type_utilisateur": t}
        r = client.post("/api/v1/auth/register", json=body)
        assert r.status_code == 201 and r.json()["data"]["user"]["role"]["slug"] == "user", t
    for t in ("medecin", "admin"):
        body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "password": PASSWORD,
                "nom": "A", "prenom": "B", "type_utilisateur": t}
        assert client.post("/api/v1/auth/register", json=body).status_code == 422, t


def test_user_cannot_make_himself_doctor(client, db):
    patient = _make_user(db)
    h = _auth(_token(client, patient))
    r = client.patch("/api/v1/auth/me", headers=h, json={"type_utilisateur": "medecin", "role_id": 2})
    assert r.status_code == 200
    assert r.json()["data"]["type_utilisateur"] == "patient" and r.json()["data"]["role"]["slug"] == "user"


def test_only_admin_creates_doctors(client, db, admin_h):
    body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "nom": "Kaneza", "prenom": "Alice",
            "type_utilisateur": "medecin"}
    doctor = _make_user(db, "medecin", "medecin")
    patient = _make_user(db)
    for h in (_auth(_token(client, doctor)), _auth(_token(client, patient))):
        assert client.post(f"{API}/users", headers=h, json=body).status_code == 403
    r = client.post(f"{API}/users", headers=admin_h, json=body)
    assert r.status_code == 201 and r.json()["data"]["role"]["slug"] == "medecin"
