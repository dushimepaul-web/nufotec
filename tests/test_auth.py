import uuid

from app.core.security import hash_password, verify_password
from app.models import User

PASSWORD = "MotDePasse123"


def _register(client, **extra):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    body = {"email": email, "password": PASSWORD, "nom": "TEST", "prenom": "jean", **extra}
    return client.post("/api/v1/auth/register", json=body), email


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


def test_health(client):
    r = client.get("/api/v1/health")
    assert r.status_code == 200
    assert r.json()["data"]["database"] == "ok"


def test_php_hash_is_compatible():
    # Hash réellement produit par password_hash("secret123", PASSWORD_DEFAULT) avec PHP 8.4.
    php_hash = "$2y$12$N33CU5DqJvLMY9ddzyO0mOzhtOqF7132PsyRq5grz5VPxXkJHzGTa"
    ours = hash_password("secret123")
    assert ours.startswith("$2y$")
    assert verify_password("secret123", ours)
    assert not verify_password("autre", ours)
    assert verify_password("secret123", php_hash)
    assert not verify_password("secret124", php_hash)


def test_register_login_me_flow(client):
    r, email = _register(client, telephone="+25779000001")
    assert r.status_code == 201, r.text
    data = r.json()["data"]
    assert data["user"]["nom"] == "Test" and data["user"]["prenom"] == "Jean"
    assert data["user"]["role"]["slug"] in ("patient", "user")
    assert data["user"]["type_utilisateur"] == "patient"

    # Connexion par email puis par téléphone
    for login in (email.upper(), "+25779000001"):
        r = client.post("/api/v1/auth/login", json={"login": login, "password": PASSWORD})
        assert r.status_code == 200, r.text
    access = r.json()["data"]["tokens"]["access_token"]

    r = client.get("/api/v1/auth/me", headers=_auth(access))
    assert r.status_code == 200
    assert r.json()["data"]["email"] == email


def test_register_duplicate_email(client):
    _, email = _register(client)
    r = client.post("/api/v1/auth/register",
                    json={"email": email, "password": PASSWORD, "nom": "a", "prenom": "b"})
    assert r.status_code == 409
    assert r.json()["error"]["code"] == "EMAIL_TAKEN"


def test_register_cannot_choose_admin(client):
    r, _ = _register(client, type_utilisateur="admin")
    assert r.status_code == 422
    assert r.json()["error"]["code"] == "VALIDATION_ERROR"


def test_wrong_password_and_disabled_account(client, db):
    _, email = _register(client)
    r = client.post("/api/v1/auth/login", json={"login": email, "password": "mauvais"})
    assert r.status_code == 401 and r.json()["error"]["code"] == "INVALID_CREDENTIALS"

    db.query(User).filter(User.email == email).update({"is_active": False})
    db.commit()
    r = client.post("/api/v1/auth/login", json={"login": email, "password": PASSWORD})
    assert r.status_code == 403 and r.json()["error"]["code"] == "ACCOUNT_DISABLED"


def test_refresh_rotation_and_logout(client):
    r, _ = _register(client)
    tokens = r.json()["data"]["tokens"]

    r = client.post("/api/v1/auth/refresh", json={"refresh_token": tokens["refresh_token"]})
    assert r.status_code == 200
    new = r.json()["data"]

    # L'ancien jeton de rafraîchissement et l'ancien jeton d'accès ne marchent plus
    r = client.post("/api/v1/auth/refresh", json={"refresh_token": tokens["refresh_token"]})
    assert r.status_code == 401
    assert client.get("/api/v1/auth/me", headers=_auth(tokens["access_token"])).status_code == 401
    assert client.get("/api/v1/auth/me", headers=_auth(new["access_token"])).status_code == 200

    assert client.post("/api/v1/auth/logout", headers=_auth(new["access_token"])).status_code == 200
    assert client.get("/api/v1/auth/me", headers=_auth(new["access_token"])).status_code == 401


def test_update_profile_and_change_password(client):
    r, email = _register(client)
    first = r.json()["data"]["tokens"]["access_token"]
    other = client.post("/api/v1/auth/login",
                        json={"login": email, "password": PASSWORD}).json()["data"]["tokens"]["access_token"]

    r = client.patch("/api/v1/auth/me", headers=_auth(first), json={"prenom": "marie", "genre": "F"})
    assert r.status_code == 200 and r.json()["data"]["prenom"] == "Marie"

    r = client.post("/api/v1/auth/change-password", headers=_auth(first),
                    json={"current_password": "faux", "new_password": "NouveauMdp456"})
    assert r.status_code == 400

    r = client.post("/api/v1/auth/change-password", headers=_auth(first),
                    json={"current_password": PASSWORD, "new_password": "NouveauMdp456"})
    assert r.status_code == 200
    assert client.get("/api/v1/auth/me", headers=_auth(first)).status_code == 200   # session courante gardée
    assert client.get("/api/v1/auth/me", headers=_auth(other)).status_code == 401   # autre appareil déconnecté
    r = client.post("/api/v1/auth/login", json={"login": email, "password": "NouveauMdp456"})
    assert r.status_code == 200


def test_me_requires_token(client):
    r = client.get("/api/v1/auth/me")
    assert r.status_code == 401
    assert r.json()["error"]["code"] == "UNAUTHENTICATED"
