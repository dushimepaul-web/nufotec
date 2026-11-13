"""Dépendances communes : utilisateur connecté et contrôle des rôles."""

from collections.abc import Callable

import jwt
from fastapi import Depends
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core.database import get_db
from app.core.responses import ApiError
from app.core.security import ACCESS, decode_token
from app.models import User, UserSession

bearer = HTTPBearer(auto_error=False)


def _unauthenticated(message: str = "Authentification requise") -> ApiError:
    return ApiError(401, "UNAUTHENTICATED", message)


def get_current_session(
    creds: HTTPAuthorizationCredentials | None = Depends(bearer),
    db: Session = Depends(get_db),
) -> UserSession:
    if creds is None:
        raise _unauthenticated()
    try:
        payload = decode_token(creds.credentials, ACCESS)
    except jwt.ExpiredSignatureError:
        raise ApiError(401, "TOKEN_EXPIRED", "Le jeton a expiré") from None
    except jwt.InvalidTokenError:
        raise _unauthenticated("Jeton invalide") from None

    session = db.scalar(select(UserSession).where(UserSession.session_id == payload.get("sid")))
    if session is None or not session.is_active:
        raise _unauthenticated("Session fermée, reconnectez-vous")
    return session


def get_current_user(
    session: UserSession = Depends(get_current_session),
    db: Session = Depends(get_db),
) -> User:
    user = db.get(User, session.user_id)
    if user is None or user.deleted_at is not None:
        raise _unauthenticated("Compte introuvable")
    if not user.is_active:
        raise ApiError(403, "ACCOUNT_DISABLED", "Votre compte est désactivé")
    return user


def require_roles(*slugs: str) -> Callable[[User], User]:
    """Exemple : Depends(require_roles("admin", "medecin"))."""

    def checker(user: User = Depends(get_current_user)) -> User:
        if user.role.slug not in slugs:
            raise ApiError(403, "FORBIDDEN", "Action non autorisée pour votre rôle")
        return user

    return checker
