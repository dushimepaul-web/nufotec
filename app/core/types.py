"""Types communs des réponses.

DateTime : toutes les dates sortent au format ISO 8601 AVEC fuseau (ex. 2026-11-13T06:28:00+02:00).
Les dates de la base sont « naïves » (heure locale du serveur, comme le PHP) : on leur attache le fuseau
de l'application ; les dates déjà avec fuseau (expiration des jetons, en UTC) sont converties."""

from datetime import datetime
from functools import lru_cache
from typing import Annotated
from zoneinfo import ZoneInfo

from pydantic import PlainSerializer

from app.core.config import get_settings


@lru_cache
def app_tz() -> ZoneInfo:
    return ZoneInfo(get_settings().app_timezone)


def localize(value: datetime) -> datetime:
    if value.tzinfo is None:
        return value.replace(tzinfo=app_tz())
    return value.astimezone(app_tz())


def _serialize(value: datetime) -> str:
    return localize(value).replace(microsecond=0).isoformat()


DateTime = Annotated[datetime, PlainSerializer(_serialize, return_type=str, when_used="json")]
