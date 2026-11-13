"""Numéros de téléphone au format international E.164 (+25779123456).

La base contient des formats variés (« +257 79 123 456 », « +25767159950 ») : on ne la réécrit pas
(le site PHP compare les numéros tels quels), on compare des versions nettoyées."""

import phonenumbers
from sqlalchemy import func
from sqlalchemy.sql.elements import ColumnElement

from app.core.config import get_settings


class InvalidPhone(ValueError):
    pass


def normalize_phone(raw: str, region: str | None = None) -> str:
    raw = raw.strip()
    if raw.startswith("00"):
        raw = "+" + raw[2:]
    try:
        number = phonenumbers.parse(raw, region or get_settings().default_phone_region)
    except phonenumbers.NumberParseException:
        raise InvalidPhone("Numéro de téléphone invalide") from None
    if not phonenumbers.is_valid_number(number):
        raise InvalidPhone("Numéro de téléphone invalide")
    return phonenumbers.format_number(number, phonenumbers.PhoneNumberFormat.E164)


def looks_like_phone(value: str) -> bool:
    return "@" not in value and sum(c.isdigit() for c in value) >= 6


def sql_clean_phone(column) -> ColumnElement:
    """Version SQL du numéro sans espaces, tirets, points ni parenthèses."""
    expr = column
    for ch in (" ", "-", ".", "(", ")"):
        expr = func.replace(expr, ch, "")
    return expr
