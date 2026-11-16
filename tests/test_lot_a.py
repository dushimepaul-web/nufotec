"""Lot A : dates avec fuseau, file d'emails, Idempotency-Key, notifications push, langues."""

import json
import re
import uuid
from datetime import datetime, timedelta

from sqlalchemy import select

from app.core import mailer
from app.core.i18n import LANGUAGES, LOCALES_DIR, parse_accept_language, source_messages, translate
from app.models import EmailOutbox, PushDevice, User

PASSWORD = "MotDePasse123"
ISO_WITH_TZ = re.compile(r"^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+02:00$")


def _register(client, **headers):
    body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "password": PASSWORD, "nom": "Test", "prenom": "Jean"}
    return client.post("/api/v1/auth/register", json=body, headers=headers)


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


# ───────────── dates ─────────────

def test_dates_have_burundi_timezone(client):
    data = _register(client).json()["data"]
    assert ISO_WITH_TZ.match(data["user"]["created_at"]), data["user"]["created_at"]
    # Les expirations de jetons (en UTC en interne) sortent aussi en heure du Burundi
    assert ISO_WITH_TZ.match(data["tokens"]["access_expires_at"])
    sessions = client.get("/api/v1/auth/sessions", headers=_auth(data["tokens"]["access_token"])).json()["data"]
    assert ISO_WITH_TZ.match(sessions[0]["login_time"])


# ───────────── file d'emails ─────────────

def test_email_is_queued_then_sent_and_body_erased(client, db, outbox):
    r = _register(client)
    email = r.json()["data"]["user"]["email"]
    assert any(m.to == email for m in outbox)  # envoyé juste après la requête
    row = db.scalar(select(EmailOutbox).where(EmailOutbox.to_email == email))
    assert row.status == "sent" and row.text_body is None and row.html_body is None


def test_failed_email_is_retried_then_abandoned(db, monkeypatch):
    mailer.queue_email(db, "retry@example.com", "Test", "texte", "<p>html</p>")
    db.commit()
    calls = []

    def boom(*_args):
        calls.append(1)
        raise ConnectionError("SMTP injoignable")

    monkeypatch.setattr(mailer, "_transport", boom)
    mailer.deliver_pending()
    row = db.scalar(select(EmailOutbox).where(EmailOutbox.to_email == "retry@example.com"))
    assert row.status == "pending" and row.attempts == 1 and "SMTP injoignable" in row.last_error
    assert row.next_attempt_at > datetime.now()  # pas avant le délai de nouvelle tentative

    mailer.deliver_pending()
    assert len(calls) == 1  # pas encore dû

    for _ in range(len(mailer.RETRY_DELAYS)):
        row.next_attempt_at = datetime.now() - timedelta(seconds=1)
        db.commit()
        mailer.deliver_pending()
    db.refresh(row)
    assert row.status == "failed" and row.text_body is None


def test_no_email_when_action_fails(client, db, outbox):
    """L'email est dans la même transaction : inscription refusée → aucun email en file."""
    existing = _register(client).json()["data"]["user"]["email"]
    outbox.clear()
    r = client.post("/api/v1/auth/register", json={"email": existing, "password": PASSWORD, "nom": "A", "prenom": "B"})
    assert r.status_code == 409 and not outbox


# ───────────── Idempotency-Key ─────────────

def test_idempotent_register_replays_same_response(client, db, outbox):
    key = str(uuid.uuid4())
    body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "password": PASSWORD, "nom": "A", "prenom": "B"}
    first = client.post("/api/v1/auth/register", json=body, headers={"Idempotency-Key": key})
    second = client.post("/api/v1/auth/register", json=body, headers={"Idempotency-Key": key})
    assert first.status_code == second.status_code == 201
    assert second.headers.get("Idempotent-Replayed") == "true"
    assert first.json() == second.json()
    assert db.query(User).filter(User.email == body["email"]).count() == 1  # un seul compte
    assert len([m for m in outbox if m.to == body["email"]]) == 1  # un seul email


def test_idempotency_key_reused_with_other_body(client):
    key = str(uuid.uuid4())
    body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "password": PASSWORD, "nom": "A", "prenom": "B"}
    client.post("/api/v1/auth/register", json=body, headers={"Idempotency-Key": key})
    r = client.post("/api/v1/auth/register", json={**body, "nom": "Autre"}, headers={"Idempotency-Key": key})
    assert r.status_code == 422 and r.json()["error"]["code"] == "IDEMPOTENCY_KEY_REUSED"


def test_idempotency_errors_are_replayed_too(client):
    key = str(uuid.uuid4())
    bad = {"email": "pas-un-email", "password": PASSWORD, "nom": "A", "prenom": "B"}
    a = client.post("/api/v1/auth/register", json=bad, headers={"Idempotency-Key": key})
    b = client.post("/api/v1/auth/register", json=bad, headers={"Idempotency-Key": key})
    assert a.status_code == b.status_code == 422 and b.headers.get("Idempotent-Replayed") == "true"


def test_invalid_idempotency_key(client):
    r = client.post("/api/v1/auth/login", json={"login": "x@y.z", "password": "x"}, headers={"Idempotency-Key": "court"})
    assert r.status_code == 400 and r.json()["error"]["code"] == "INVALID_IDEMPOTENCY_KEY"


def test_without_key_nothing_changes(client):
    body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "password": PASSWORD, "nom": "A", "prenom": "B"}
    assert client.post("/api/v1/auth/register", json=body).status_code == 201
    assert client.post("/api/v1/auth/register", json=body).status_code == 409


# ───────────── notifications push ─────────────

TOKEN = "ExponentPushToken[abcdefghijklmnopqrstuv]"


def test_push_device_register_test_and_logout(client, db, pushes):
    access = _register(client).json()["data"]["tokens"]["access_token"]
    r = client.post("/api/v1/auth/push-devices", headers=_auth(access),
                    json={"token": TOKEN, "platform": "android", "app_version": "1.0.3"})
    assert r.status_code == 200, r.text
    assert client.post("/api/v1/auth/push-devices/test", headers=_auth(access)).status_code == 200
    assert pushes and pushes[-1].token == TOKEN

    # Après la déconnexion, l'appareil ne reçoit plus rien
    client.post("/api/v1/auth/logout", headers=_auth(access))
    from app.core.push import active_devices

    user_id = db.scalar(select(PushDevice.user_id).where(PushDevice.token == TOKEN))
    assert active_devices(db, [user_id]) == []


def test_push_token_moves_to_new_account(client, db):
    a = _register(client).json()["data"]["tokens"]["access_token"]
    b = _register(client).json()["data"]["tokens"]["access_token"]
    client.post("/api/v1/auth/push-devices", headers=_auth(a), json={"token": TOKEN})
    client.post("/api/v1/auth/push-devices", headers=_auth(b), json={"token": TOKEN})
    assert db.query(PushDevice).filter(PushDevice.token == TOKEN).count() == 1  # même téléphone, nouveau compte


def test_push_invalid_token_and_unregister(client):
    access = _register(client).json()["data"]["tokens"]["access_token"]
    r = client.post("/api/v1/auth/push-devices", headers=_auth(access), json={"token": "n'importe quoi !"})
    assert r.status_code == 422
    client.post("/api/v1/auth/push-devices", headers=_auth(access), json={"token": TOKEN})
    assert client.request("DELETE", "/api/v1/auth/push-devices", headers=_auth(access),
                          json={"token": TOKEN}).status_code == 200
    assert client.post("/api/v1/auth/push-devices/test", headers=_auth(access)).json()["error"]["code"] == "NO_DEVICE"


# ───────────── langues ─────────────

def test_fifty_languages_listed(client):
    data = client.get("/api/v1/languages").json()["data"]
    assert len(data) == 50 == len(LANGUAGES)
    fr = next(lang for lang in data if lang["code"] == "fr")
    assert fr["par_defaut"] and next(lang for lang in data if lang["code"] == "ar")["rtl"]


def test_accept_language_parsing():
    assert parse_accept_language("en-US,en;q=0.9,fr;q=0.8") == "en"
    assert parse_accept_language("xx-YY") == "fr"
    assert parse_accept_language("de;q=0.5, sw;q=0.9") == "sw"
    assert parse_accept_language(None) == "fr"


def test_messages_in_french_by_default_and_english(client):
    r = client.post("/api/v1/auth/login", json={"login": "personne@example.com", "password": "Xyz12345"})
    assert r.json()["error"]["message"] == "Identifiants incorrects" and r.headers["Content-Language"] == "fr"
    r = client.post("/api/v1/auth/login", json={"login": "personne@example.com", "password": "Xyz12345"},
                    headers={"Accept-Language": "en-GB,en;q=0.9"})
    assert r.json()["error"]["message"] == "Invalid credentials" and r.headers["Content-Language"] == "en"
    assert r.json()["error"]["code"] == "INVALID_CREDENTIALS"  # le code ne change jamais


def test_query_lang_and_validation_messages(client):
    r = client.post("/api/v1/auth/register?lang=en", json={"email": "x@example.com"})
    fields = {d["field"]: d["message"] for d in r.json()["error"]["details"]}
    assert fields["password"] == "Field required"
    r = client.post("/api/v1/auth/register", json={"email": "x@example.com", "password": "court", "nom": "A", "prenom": "B"})
    fields = {d["field"]: d["message"] for d in r.json()["error"]["details"]}
    assert fields["password"] == "Trop court (minimum 8 caractère(s))"


def test_dynamic_messages_translated():
    assert translate("Trop de tentatives. Réessayez dans 7 minute(s).", "en") == "Too many attempts. Try again in 7 minute(s)."
    assert translate("Mot de passe trop faible : au moins une lettre, au moins un chiffre", "en") == \
        "Password too weak: at least one letter, at least one digit"


def test_unknown_translation_falls_back_to_english():
    assert translate("Identifiants incorrects", "ha") in (
        json.loads((LOCALES_DIR / "ha.json").read_text(encoding="utf-8")).get("Identifiants incorrects")
        if (LOCALES_DIR / "ha.json").exists() else None, "Invalid credentials")


def test_generated_catalogs_keep_placeholders():
    """Chaque traduction générée garde exactement les mêmes parties variables que le français."""
    sources = set(source_messages())
    for path in LOCALES_DIR.glob("[!_]*.json"):
        data = json.loads(path.read_text(encoding="utf-8"))
        for fr, translated in data.items():
            assert fr in sources, f"{path.name} : texte obsolète {fr!r}"
            assert re.findall(r"\{[a-z_]*\}", fr) == re.findall(r"\{[a-z_]*\}", translated), (path.name, fr)
