"""Tests des lots 1 à 3 : sécurité, fonctions mobiles, exploitation."""

import io
import os
import re
import uuid
from datetime import datetime, timedelta

import jwt
import pytest
from PIL import Image

from app.core.config import get_settings
from app.core.passwords import password_problems
from app.core.phone import InvalidPhone, normalize_phone
from app.models import PasswordReset, User, UserActivity, UserSession

PASSWORD = "MotDePasse123"


def _email():
    return f"test-{uuid.uuid4().hex[:8]}@example.com"


def _register(client, **extra):
    email = extra.pop("email", None) or _email()
    body = {"email": email, "password": PASSWORD, "nom": "Test", "prenom": "Jean", **extra}
    r = client.post("/api/v1/auth/register", json=body)
    assert r.status_code == 201, r.text
    return r.json()["data"], email


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


def _login(client, login, password=PASSWORD, **kw):
    return client.post("/api/v1/auth/login", json={"login": login, "password": password}, **kw)


# ───────────── Lot 1 : sécurité ─────────────

def test_lockout_after_five_failures(client):
    _, email = _register(client)
    for _ in range(5):
        assert _login(client, email, "Mauvais123").status_code == 401
    r = _login(client, email)  # même le bon mot de passe est refusé pendant le blocage
    assert r.status_code == 429
    assert r.json()["error"]["code"] == "TOO_MANY_REQUESTS"
    assert int(r.headers["Retry-After"]) > 0


def test_lockout_is_per_account(client):
    _, victim = _register(client)
    _, other = _register(client)
    for _ in range(5):
        _login(client, victim, "Mauvais123")
    assert _login(client, other).status_code == 200


def test_success_resets_failure_counter(client):
    _, email = _register(client)
    for _ in range(4):
        _login(client, email, "Mauvais123")
    assert _login(client, email).status_code == 200
    for _ in range(4):
        _login(client, email, "Mauvais123")
    assert _login(client, email).status_code == 200


def test_unknown_account_same_error(client):
    r = _login(client, "personne-ici@example.com", "Peu1mporte")
    assert r.status_code == 401 and r.json()["error"]["code"] == "INVALID_CREDENTIALS"


def test_phone_normalization():
    assert normalize_phone("79 123 456") == "+25779123456"
    assert normalize_phone("+257 79-123-456") == "+25779123456"
    assert normalize_phone("0025779123456") == "+25779123456"
    with pytest.raises(InvalidPhone):
        normalize_phone("123")


def test_phone_stored_e164_and_login_any_format(client):
    data, _ = _register(client, telephone="79 555 012")
    assert data["user"]["telephone"] == "+25779555012"
    for variant in ("79555012", "+257 79 555 012", "0025779555012"):
        assert _login(client, variant).status_code == 200, variant


def test_phone_matches_legacy_spaced_format(client, db):
    _, email = _register(client)
    db.query(User).filter(User.email == email).update({"telephone": "+257 79 444 321"})
    db.commit()
    assert _login(client, "79444321").status_code == 200
    r = client.post("/api/v1/auth/register",
                    json={"email": _email(), "password": PASSWORD, "nom": "A", "prenom": "B",
                          "telephone": "+25779444321"})
    assert r.status_code == 409 and r.json()["error"]["code"] == "PHONE_TAKEN"


def test_shared_phone_picks_account_by_password(client, db):
    _, a = _register(client, password="PremierMdp111")
    _, b = _register(client, password="SecondMdp222")
    db.query(User).filter(User.email.in_([a, b])).update({"telephone": "+257 79 321 654"},
                                                         synchronize_session=False)
    db.commit()
    r = _login(client, "79321654", "SecondMdp222")
    assert r.status_code == 200 and r.json()["data"]["user"]["email"] == b


def test_refresh_reuse_revokes_session(client, db):
    data, _ = _register(client)
    old = data["tokens"]
    new = client.post("/api/v1/auth/refresh", json={"refresh_token": old["refresh_token"]}).json()["data"]

    # Ancien jeton rejoué juste après : rafraîchissement concurrent, la session reste ouverte.
    r = client.post("/api/v1/auth/refresh", json={"refresh_token": old["refresh_token"]})
    assert r.json()["error"]["code"] == "TOKEN_ROTATED"
    assert client.get("/api/v1/auth/me", headers=_auth(new["access_token"])).status_code == 200

    # Rejoué plus tard : vol présumé, la session est fermée (le voleur ET l'utilisateur sont coupés).
    payload = jwt.decode(old["refresh_token"], options={"verify_signature": False})
    db.query(UserSession).filter(UserSession.id == payload["ses"]).update(
        {"last_activity": datetime.now() - timedelta(minutes=5)})
    db.commit()
    r = client.post("/api/v1/auth/refresh", json={"refresh_token": old["refresh_token"]})
    assert r.status_code == 401 and r.json()["error"]["code"] == "TOKEN_REUSED"
    assert client.get("/api/v1/auth/me", headers=_auth(new["access_token"])).status_code == 401
    assert client.post("/api/v1/auth/refresh", json={"refresh_token": new["refresh_token"]}).status_code == 401


# ───────────── Lot 2 : fonctions mobiles ─────────────

def _code_from(outbox, email):
    mail = next(m for m in reversed(outbox) if m.to == email)
    return re.search(r"\b(\d{6})\b", mail.text).group(1)


def test_forgot_and_reset_password(client, outbox):
    data, email = _register(client)
    access = data["tokens"]["access_token"]
    r = client.post("/api/v1/auth/password/forgot", json={"email": email.upper()})
    assert r.status_code == 200
    code = _code_from(outbox, email)

    r = client.post("/api/v1/auth/password/reset",
                    json={"email": email, "code": code, "new_password": "NouveauMdp789"})
    assert r.status_code == 200, r.text
    assert client.get("/api/v1/auth/me", headers=_auth(access)).status_code == 401  # tout est déconnecté
    assert _login(client, email, "NouveauMdp789").status_code == 200
    # Le code ne sert qu'une fois
    r = client.post("/api/v1/auth/password/reset",
                    json={"email": email, "code": code, "new_password": "EncoreAutre321"})
    assert r.status_code == 400


def test_forgot_unknown_email_same_answer(client, outbox):
    r = client.post("/api/v1/auth/password/forgot", json={"email": "inconnu-total@example.com"})
    assert r.status_code == 200 and not outbox


def test_reset_code_attempts_limited(client, outbox):
    _, email = _register(client)
    client.post("/api/v1/auth/password/forgot", json={"email": email})
    code = _code_from(outbox, email)
    wrong = "000000" if code != "000000" else "111111"
    for i in range(5):
        r = client.post("/api/v1/auth/password/reset",
                        json={"email": email, "code": wrong, "new_password": "NouveauMdp789"})
        assert r.status_code == 400
    r = client.post("/api/v1/auth/password/reset",
                    json={"email": email, "code": code, "new_password": "NouveauMdp789"})
    assert r.status_code == 400  # code grillé après 5 erreurs


def test_reset_code_stored_hashed(client, db, outbox):
    _, email = _register(client)
    client.post("/api/v1/auth/password/forgot", json={"email": email})
    code = _code_from(outbox, email)
    user = db.query(User).filter(User.email == email).one()
    row = db.query(PasswordReset).filter(PasswordReset.user_id == user.id).one()
    assert code not in row.code_hash and len(row.code_hash) == 64


def test_forgot_limited_per_hour(client, outbox):
    _, email = _register(client)
    for _ in range(5):
        client.post("/api/v1/auth/password/forgot", json={"email": email})
    assert len([m for m in outbox if m.to == email and "réinitialisation" in m.subject]) == 3


def test_email_verification(client, outbox):
    data, email = _register(client)
    assert data["user"]["email_verified"] is False
    link = next(m for m in outbox if m.to == email).text.split()[-1]
    token = link.rsplit("/", 1)[-1]

    r = client.get(f"/api/v1/auth/verify-email/{token}")
    assert r.status_code == 200 and "Email confirmé" in r.text
    me = client.get("/api/v1/auth/me", headers=_auth(data["tokens"]["access_token"])).json()["data"]
    assert me["email_verified"] is True
    r = client.post("/api/v1/auth/verify-email", json={"token": token})
    assert r.status_code == 400  # lien déjà utilisé
    r = client.post("/api/v1/auth/resend-verification", headers=_auth(data["tokens"]["access_token"]))
    assert r.json()["error"]["code"] == "ALREADY_VERIFIED"


def test_sessions_list_and_revoke(client):
    data, email = _register(client, )
    phone = data["tokens"]["access_token"]
    laptop = _login(client, email, headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0) Chrome/130"}).json()
    laptop = laptop["data"]["tokens"]["access_token"]

    sessions = client.get("/api/v1/auth/sessions", headers=_auth(phone)).json()["data"]
    assert len(sessions) == 2 and sum(s["current"] for s in sessions) == 1
    win = next(s for s in sessions if s["platform"] == "Windows")
    assert win["browser"] == "Chrome"

    assert client.delete(f"/api/v1/auth/sessions/{win['id']}", headers=_auth(phone)).status_code == 200
    assert client.get("/api/v1/auth/me", headers=_auth(laptop)).status_code == 401
    assert client.get("/api/v1/auth/me", headers=_auth(phone)).status_code == 200

    third = _login(client, email).json()["data"]["tokens"]["access_token"]
    r = client.delete("/api/v1/auth/sessions", headers=_auth(phone))
    assert r.json()["data"]["closed"] == 1
    assert client.get("/api/v1/auth/me", headers=_auth(third)).status_code == 401


def test_cannot_revoke_someone_else_session(client):
    a, _ = _register(client)
    b, _ = _register(client)
    b_sessions = client.get("/api/v1/auth/sessions", headers=_auth(b["tokens"]["access_token"])).json()["data"]
    r = client.delete(f"/api/v1/auth/sessions/{b_sessions[0]['id']}", headers=_auth(a["tokens"]["access_token"]))
    assert r.status_code == 404


def test_delete_account(client, db):
    data, email = _register(client, telephone="79 777 001")
    token = data["tokens"]["access_token"]
    r = client.request("DELETE", "/api/v1/auth/me", headers=_auth(token), json={"password": "Faux12345"})
    assert r.status_code == 400
    r = client.request("DELETE", "/api/v1/auth/me", headers=_auth(token), json={"password": PASSWORD})
    assert r.status_code == 200
    assert client.get("/api/v1/auth/me", headers=_auth(token)).status_code == 401
    assert _login(client, email).status_code == 401
    user = db.query(User).filter(User.uuid == data["user"]["uuid"]).one()
    assert user.deleted_at and user.email.startswith("deleted+") and user.telephone is None
    # L'email et le numéro sont à nouveau libres
    _register(client, email=email, telephone="79 777 001")


def _image(fmt="PNG", size=(1200, 800), mode="RGB"):
    buf = io.BytesIO()
    Image.new(mode, size, (200, 30, 30)).save(buf, fmt)
    return buf.getvalue()


def test_photo_upload_resized_and_replaced(client, uploads_dir):
    data, _ = _register(client)
    token = data["tokens"]["access_token"]
    r = client.post("/api/v1/auth/me/photo", headers=_auth(token),
                    files={"photo": ("moi.png", _image(), "image/png")})
    assert r.status_code == 200, r.text
    url = r.json()["data"]["photo_url"]
    first = url.rsplit("/", 1)[-1]
    with Image.open(os.path.join(uploads_dir, "Users", first)) as img:
        assert max(img.size) == 400 and img.format == "JPEG"

    r = client.post("/api/v1/auth/me/photo", headers=_auth(token),
                    files={"photo": ("moi.webp", _image("WEBP"), "image/webp")})
    assert r.status_code == 200
    assert not os.path.exists(os.path.join(uploads_dir, "Users", first))  # ancienne photo supprimée

    r = client.delete("/api/v1/auth/me/photo", headers=_auth(token))
    assert r.json()["data"]["photo_url"] is None


def test_photo_rejects_fake_and_big_files(client):
    token = _register(client)[0]["tokens"]["access_token"]
    r = client.post("/api/v1/auth/me/photo", headers=_auth(token),
                    files={"photo": ("virus.png", b"<?php system($_GET['c']); ?>", "image/png")})
    assert r.status_code == 422 and r.json()["error"]["code"] == "INVALID_IMAGE"
    r = client.post("/api/v1/auth/me/photo", headers=_auth(token),
                    files={"photo": ("big.png", b"0" * (2 * 1024 * 1024 + 10), "image/png")})
    assert r.status_code == 422


def test_activities_are_logged(client, db):
    data, email = _register(client)
    _login(client, email)
    user = db.query(User).filter(User.email == email).one()
    actions = {a.action for a in db.query(UserActivity).filter(UserActivity.user_id == user.id)}
    assert {"register", "login"} <= actions


# ───────────── Lot 3 : qualité et exploitation ─────────────

def test_password_rules():
    assert password_problems("bonjourami") == ["au moins un chiffre"]
    assert "au moins une lettre" in password_problems("12345678901")
    assert "ne doit pas être un mot de passe courant" in password_problems("password123")
    assert "ne doit pas contenir votre email" in password_problems("Jeandupont1", "jeandupont@x.com")
    assert password_problems("Bonne1Phrase") == []


def test_register_weak_password(client):
    r = client.post("/api/v1/auth/register",
                    json={"email": _email(), "password": "password123", "nom": "A", "prenom": "B"})
    assert r.status_code == 422 and r.json()["error"]["code"] == "WEAK_PASSWORD"


def test_request_id_header(client):
    r = client.get("/api/v1/health", headers={"X-Request-ID": "mon-id-12345"})
    assert r.headers["X-Request-ID"] == "mon-id-12345"
    r = client.get("/api/v1/health", headers={"X-Request-ID": "<script>"})
    assert r.headers["X-Request-ID"] != "<script>"


def test_forwarded_for_only_from_trusted_proxy():
    from starlette.requests import Request

    from app.core.client import client_ip

    def req(peer, xff):
        return Request({"type": "http", "client": (peer, 1), "headers": [(b"x-forwarded-for", xff.encode())]})

    assert client_ip(req("127.0.0.1", "41.79.1.2")) == "41.79.1.2"            # proxy de confiance
    assert client_ip(req("8.8.8.8", "1.2.3.4")) == "8.8.8.8"                  # en-tête falsifié ignoré
    assert client_ip(req("127.0.0.1", "6.6.6.6, 41.79.1.2")) == "41.79.1.2"   # on ne croit que le dernier saut


def test_production_config_refuses_weak_settings():
    from app.core.config import Settings

    with pytest.raises(ValueError, match="JWT_SECRET"):
        Settings(jwt_secret="court", app_env="local")
    with pytest.raises(ValueError, match="CORS_ORIGINS"):
        Settings(jwt_secret="x" * 40, app_env="production", cors_origins="*", mail_mode="smtp", debug=False)
    Settings(jwt_secret="x" * 40, app_env="production", cors_origins="https://nufotec.com", mail_mode="smtp",
             debug=False)


def test_settings_loaded():
    assert get_settings().mail_mode == "console"
