"""Petits outils partagés par les modules."""

import re
import unicodedata
from datetime import datetime

from sqlalchemy import func, literal_column, select
from sqlalchemy.orm import Session

from app.core.phone import InvalidPhone, normalize_phone, sql_clean_phone
from app.core.responses import ApiError


def now() -> datetime:
    # Heure locale sans fuseau, comme date('Y-m-d H:i:s') côté PHP et NOW() côté MySQL.
    return datetime.now().replace(microsecond=0)


def to_local(dt: datetime) -> datetime:
    return dt.astimezone().replace(tzinfo=None, microsecond=0)


def validation_error(field: str, message: str, code: str = "VALIDATION_ERROR") -> ApiError:
    return ApiError(422, code, message, [{"field": field, "message": message}])


def too_many(message: str, retry_after: int) -> ApiError:
    retry_after = max(int(retry_after), 1)
    return ApiError(429, "TOO_MANY_REQUESTS", message, {"retry_after_seconds": retry_after},
                    {"Retry-After": str(retry_after)})


def minutes_ago(minutes: int):
    return func.date_sub(func.now(), literal_column(f"INTERVAL {int(minutes)} MINUTE"))


def phone_or_422(raw: str | None, field: str = "telephone") -> str | None:
    if not raw or not raw.strip():
        return None
    try:
        return normalize_phone(raw)
    except InvalidPhone as e:
        raise validation_error(field, str(e)) from None


def users_by_phone(db: Session, e164: str, exclude_id: int | None = None) -> list:
    from app.models import User

    query = select(User).where(sql_clean_phone(User.telephone) == e164, User.deleted_at.is_(None))
    if exclude_id:
        query = query.where(User.id != exclude_id)
    return list(db.scalars(query).unique())


def like_pattern(text: str) -> str:
    """Motif LIKE sûr : % et _ saisis par l'utilisateur sont pris littéralement."""
    escaped = text.replace("\\", "\\\\").replace("%", "\\%").replace("_", "\\_")
    return f"%{escaped}%"


def slugify(text: str) -> str:
    ascii_text = unicodedata.normalize("NFKD", text).encode("ascii", "ignore").decode()
    return re.sub(r"[^a-z0-9]+", "-", ascii_text.lower()).strip("-")


def plain_excerpt(text: str | None, length: int = 120) -> str | None:
    """Texte brut court (balises HTML retirées, entités décodées) pour les cartes et la recherche."""
    import html as _html

    if not text:
        return None
    plain = re.sub(r"\s+", " ", _html.unescape(re.sub(r"<[^>]+>", " ", text))).strip()
    return plain if len(plain) <= length else plain[:length].rsplit(" ", 1)[0] + "…"
