"""
preprocessing.py - Nettoyage et normalisation du texte
Gère les variantes WhatsApp, fautes d'orthographe, Unicode
"""

import re
import unicodedata


# Caractères accentués → ASCII
ACCENT_MAP = {
    'é': 'e', 'è': 'e', 'ê': 'e', 'ë': 'e',
    'à': 'a', 'â': 'a', 'ä': 'a',
    'î': 'i', 'ï': 'i',
    'û': 'u', 'ù': 'u', 'ü': 'u',
    'ô': 'o', 'ö': 'o',
    'ç': 'c', 'œ': 'oe', 'æ': 'ae',
    'ñ': 'n',
}

# Apostrophes variées → apostrophe simple
APOSTROPHES = ["'", '\u2019', '`', '\u00b4', '\u2018']

# Patterns WhatsApp / chat
WHATSAPP_PATTERNS = [
    (r'\b(u|w)a\b', r'a'),          # "wa" → "a" (kirundi)
    (r'nge\b', r'nge'),              # garder
    (r'(\w)\1{2,}', r'\1\1'),       # "ndaaaashaka" → "ndaashaka"
]


def normalize_text(text: str) -> str:
    """
    Normalise le texte pour la détection de langue.
    Gère: minuscules, Unicode, apostrophes, espaces, caractères inutiles.
    """
    if not text or not text.strip():
        return ""

    # Minuscules + trim
    text = text.lower().strip()

    # Normaliser Unicode (supprimer diacritiques combinés)
    text = unicodedata.normalize('NFD', text)
    text = ''.join(c for c in text if unicodedata.category(c) != 'Mn')
    text = unicodedata.normalize('NFC', text)

    # Normaliser apostrophes
    for apost in APOSTROPHES:
        text = text.replace(apost, "'")

    # Appliquer remplacements WhatsApp
    for pattern, replacement in WHATSAPP_PATTERNS:
        text = re.sub(pattern, replacement, text)

    # Supprimer caractères inutiles (garder lettres unicode, chiffres, espaces, apostrophes, tirets)
    text = re.sub(r'[^\w\s\'-]', '', text, flags=re.UNICODE)

    # Normaliser espaces multiples
    text = re.sub(r'\s+', ' ', text)

    return text.strip()


def tokenize(text: str) -> list[str]:
    """
    Tokenise le texte normalisé en mots.
    """
    normalized = normalize_text(text)
    if not normalized:
        return []
    return normalized.split()


def char_ngrams(text: str, n_min: int = 2, n_max: int = 5) -> list[str]:
    """
    Extrait les character n-grams du texte.
    Utile pour la détection morphologique du Kirundi.
    """
    normalized = normalize_text(text)
    if not normalized or len(normalized) < n_min:
        return []

    ngrams = []
    for n in range(n_min, min(n_max + 1, len(normalized) + 1)):
        for i in range(len(normalized) - n + 1):
            ngrams.append(normalized[i:i + n])
    return ngrams


def word_ngrams(text: str, n_min: int = 1, n_max: int = 2) -> list[str]:
    """
    Extrait les word n-grams du texte.
    """
    tokens = tokenize(text)
    if not tokens:
        return []

    ngrams = []
    for n in range(n_min, min(n_max + 1, len(tokens) + 1)):
        for i in range(len(tokens) - n + 1):
            ngrams.append(' '.join(tokens[i:i + n]))
    return ngrams
