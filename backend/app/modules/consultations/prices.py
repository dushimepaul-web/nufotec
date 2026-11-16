"""Affichage des tarifs d'un expert — une seule règle pour le site, l'application et le message WhatsApp :

    Résidant du Burundi : 40.000 BIF
    Résidant à l'étranger : 50.00 USD/EUR (Équivalent en BIF : 325.000-375.000 BIF)

Les valeurs viennent de la base (prix_bif, prix_usd, currency, USD_EUR_Equivalent_en_BIF).
"""

from decimal import Decimal

from app.models import Medecin


def money(value: Decimal | None, currency: str) -> str:
    """40000 → « 40.000 BIF » ; 50 → « 50.00 USD/EUR »."""
    if value is None:
        return "—"
    if currency == "BIF":
        return f"{int(value):,}".replace(",", ".") + " BIF"
    return f"{Decimal(value):.2f} {currency}"


def _clean_equivalent(text: str | None) -> str | None:
    """« 325.000-375.000 BIF » tel que saisi ; « BIF » ajouté s'il manque."""
    text = " ".join((text or "").split())
    if not text:
        return None
    return text if "BIF" in text.upper() else f"{text} BIF"


def foreign_currency(m: Medecin) -> str:
    return (m.currency or "USD").strip() or "USD"


def price_labels(m: Medecin) -> dict:
    """Textes prêts à afficher pour les deux tarifs de l'expert."""
    burundi = money(m.prix_bif, "BIF") if m.prix_bif is not None else None
    foreign = money(m.prix_usd, foreign_currency(m)) if m.prix_usd is not None else None
    equivalent = _clean_equivalent(m.usd_eur_equivalent_bif)
    return {
        "burundi": burundi,
        "etranger": foreign,
        "equivalent_bif": equivalent,
        "ligne_burundi": f"Résidant du Burundi : {burundi}" if burundi else None,
        "ligne_etranger": (f"Résidant à l'étranger : {foreign}" + (f" (Équivalent en BIF : {equivalent})" if equivalent else ""))
        if foreign else None,
    }
