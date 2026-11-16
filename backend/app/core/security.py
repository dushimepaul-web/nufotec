"""Mots de passe (compatibles avec password_hash/password_verify de PHP) et jetons JWT."""

import uuid
from datetime import UTC, datetime, timedelta

import bcrypt
import jwt

from app.core.config import get_settings

ACCESS = "access"
REFRESH = "refresh"


def hash_password(password: str) -> str:
    # Préfixe $2y$ comme PHP, pour que le site PHP accepte aussi les mots de passe créés ici.
    hashed = bcrypt.hashpw(password.encode("utf-8"), bcrypt.gensalt(rounds=10)).decode()
    return "$2y$" + hashed[4:]


# Hash factice : vérifié quand le compte n'existe pas, pour que le temps de réponse soit identique.
DUMMY_HASH = bcrypt.hashpw(b"nufotec-dummy-password", bcrypt.gensalt(rounds=10)).decode()


def verify_password(password: str, hashed: str | None) -> bool:
    if not hashed:
        return False
    try:
        # bcrypt (Python) ne connaît que $2b$ ; $2y$ (PHP) est équivalent.
        normalized = "$2b$" + hashed[4:] if hashed.startswith("$2y$") else hashed
        return bcrypt.checkpw(password.encode("utf-8"), normalized.encode())
    except ValueError:
        return False


def _encode(sub: str, token_type: str, lifetime: timedelta, **extra) -> tuple[str, str, datetime]:
    s = get_settings()
    now = datetime.now(UTC)
    exp = now + lifetime
    jti = uuid.uuid4().hex
    payload = {"sub": sub, "type": token_type, "jti": jti, "iat": now, "exp": exp, **extra}
    return jwt.encode(payload, s.jwt_secret, algorithm=s.jwt_algorithm), jti, exp


def create_access_token(user_uuid: str, session_jti: str) -> tuple[str, datetime]:
    # Le jeton d'accès porte l'identifiant de session (sid) : fermer la session l'invalide.
    minutes = get_settings().access_token_minutes
    token, _, exp = _encode(user_uuid, ACCESS, timedelta(minutes=minutes), sid=session_jti)
    return token, exp


def create_refresh_token(user_uuid: str, session_db_id: int) -> tuple[str, str, datetime]:
    # "ses" (id de la ligne user_sessions) permet de reconnaître un ancien jeton réutilisé après rotation.
    return _encode(user_uuid, REFRESH, timedelta(days=get_settings().refresh_token_days), ses=session_db_id)


def decode_token(token: str, expected_type: str) -> dict:
    s = get_settings()
    payload = jwt.decode(token, s.jwt_secret, algorithms=[s.jwt_algorithm])
    if payload.get("type") != expected_type:
        raise jwt.InvalidTokenError("Type de jeton invalide")
    return payload
