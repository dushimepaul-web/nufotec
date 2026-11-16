"""Chiffrement des secrets stockés en base (clés d'API des fournisseurs d'IA).

Clé : AI_ENCRYPTION_KEY dans le .env (jamais en base). Sans elle, les valeurs chiffrées sont illisibles :
la sauvegarder avec la configuration du serveur."""

from functools import lru_cache

from cryptography.fernet import Fernet, InvalidToken

from app.core.config import get_settings
from app.core.responses import ApiError


@lru_cache
def _fernet() -> Fernet:
    key = get_settings().ai_encryption_key
    if not key:
        raise ApiError(500, "CONFIG_ERROR", "AI_ENCRYPTION_KEY manquante dans la configuration")
    return Fernet(key.encode())


def encrypt(value: str) -> str:
    return _fernet().encrypt(value.encode()).decode()


def decrypt(token: str | None) -> str | None:
    if not token:
        return None
    try:
        return _fernet().decrypt(token.encode()).decode()
    except InvalidToken:
        raise ApiError(500, "CONFIG_ERROR", "Clé chiffrée illisible (AI_ENCRYPTION_KEY a changé ?)") from None


def hint(secret: str) -> str:
    """Indice affichable : préfixe et 4 derniers caractères (ex. gsk_••••4f2a)."""
    prefix = secret.split("-")[0] if secret.startswith("sk-") else secret[:4]
    prefix = "sk-" if secret.startswith("sk-") else prefix
    return f"{prefix}••••{secret[-4:]}" if len(secret) > 8 else "••••"
