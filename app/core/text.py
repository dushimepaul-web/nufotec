"""Nettoyage des textes saisis par le public (formulaires du site) — deuxième barrière après le navigateur.

Le navigateur filtre déjà pendant la frappe, mais n'importe qui peut appeler l'API directement :
on retire donc ici aussi les caractères de contrôle invisibles, les caractères « bidi » (qui inversent
l'affichage pour tromper le lecteur) et les balises / caractères de code, et on refuse un nom sans lettre.
Ces textes partent dans WhatsApp, dans les emails et dans l'administration.
"""

import re

_CONTROL = re.compile(r"[\x00-\x08\x0b\x0c\x0e-\x1f\x7f\u200b-\u200f\u202a-\u202e\u2066-\u2069]")
_CODE = re.compile(r"[<>{}\[\]`\\|^~$]")
_NAME_BAD = re.compile(r"[^\w\s'’.\-]", re.UNICODE)
_HAS_LETTER = re.compile(r"[^\W\d_]", re.UNICODE)


def clean_free_text(value: str | None, keep_newlines: bool = True) -> str | None:
    """Adresse, remarque, message : tout sauf caractères de contrôle et balises ; espaces superflus retirés."""
    if value is None:
        return None
    text = _CODE.sub("", _CONTROL.sub("", value))
    lines = [" ".join(line.split()) for line in text.splitlines()] if keep_newlines else [" ".join(text.split())]
    return "\n".join(line for line in lines if line).strip() or None


def clean_person_name(value: str) -> str:
    """Nom de personne ou de ville : lettres, chiffres, espaces, apostrophe, point, tiret ; au moins une lettre."""
    text = " ".join(_NAME_BAD.sub("", _CONTROL.sub("", value)).split())
    if not _HAS_LETTER.search(text):
        raise ValueError("doit contenir des lettres")
    return text
