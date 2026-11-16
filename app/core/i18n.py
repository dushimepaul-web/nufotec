"""Langue des messages : 50 langues, français par défaut.

Choix de la langue : paramètre ?lang=xx, sinon en-tête Accept-Language, sinon français.
Les messages sont écrits en français dans le code. L'anglais est traduit à la main (i18n_messages.EN) ;
les 48 autres langues sont dans app/locales/<code>.json, générés une fois avec Google Translate (gratuit)
par `python -m tools.generate_translations` : à l'exécution, aucune requête externe.
Un message avec une partie variable s'écrit avec {} (ex. « Trop de tentatives. Réessayez dans {} minute(s). »).
Les CODES d'erreur (EMAIL_TAKEN…) ne changent jamais : l'app doit s'appuyer sur eux, pas sur le texte.
Traduction manquante : on retombe sur l'anglais, puis sur le français."""

import json
import re
from contextvars import ContextVar
from functools import lru_cache
from pathlib import Path

from app.core.i18n_messages import EN, HTTP_FR, VALIDATION

DEFAULT = "fr"
LOCALES_DIR = Path(__file__).resolve().parent.parent / "locales"

# Les 50 langues les plus parlées au monde : code → (nom natif, nom en français, code Google Translate)
LANGUAGES: dict[str, tuple[str, str, str]] = {
    "fr": ("Français", "Français", "fr"),
    "en": ("English", "Anglais", "en"),
    "zh": ("中文", "Chinois (mandarin)", "zh-CN"),
    "hi": ("हिन्दी", "Hindi", "hi"),
    "es": ("Español", "Espagnol", "es"),
    "ar": ("العربية", "Arabe", "ar"),
    "bn": ("বাংলা", "Bengali", "bn"),
    "pt": ("Português", "Portugais", "pt"),
    "ru": ("Русский", "Russe", "ru"),
    "ur": ("اردو", "Ourdou", "ur"),
    "id": ("Bahasa Indonesia", "Indonésien", "id"),
    "de": ("Deutsch", "Allemand", "de"),
    "ja": ("日本語", "Japonais", "ja"),
    "sw": ("Kiswahili", "Swahili", "sw"),
    "mr": ("मराठी", "Marathi", "mr"),
    "te": ("తెలుగు", "Télougou", "te"),
    "tr": ("Türkçe", "Turc", "tr"),
    "ta": ("தமிழ்", "Tamoul", "ta"),
    "vi": ("Tiếng Việt", "Vietnamien", "vi"),
    "ko": ("한국어", "Coréen", "ko"),
    "fa": ("فارسی", "Persan", "fa"),
    "it": ("Italiano", "Italien", "it"),
    "ha": ("Hausa", "Haoussa", "ha"),
    "jv": ("Basa Jawa", "Javanais", "jw"),
    "pa": ("ਪੰਜਾਬੀ", "Pendjabi", "pa"),
    "gu": ("ગુજરાતી", "Goudjarati", "gu"),
    "th": ("ไทย", "Thaï", "th"),
    "am": ("አማርኛ", "Amharique", "am"),
    "yo": ("Yorùbá", "Yoruba", "yo"),
    "pl": ("Polski", "Polonais", "pl"),
    "uk": ("Українська", "Ukrainien", "uk"),
    "ms": ("Bahasa Melayu", "Malais", "ms"),
    "my": ("မြန်မာ", "Birman", "my"),
    "tl": ("Filipino", "Filipino (tagalog)", "tl"),
    "kn": ("ಕನ್ನಡ", "Kannada", "kn"),
    "ml": ("മലയാളം", "Malayalam", "ml"),
    "or": ("ଓଡ଼ିଆ", "Odia", "or"),
    "ig": ("Igbo", "Igbo", "ig"),
    "uz": ("Oʻzbekcha", "Ouzbek", "uz"),
    "ro": ("Română", "Roumain", "ro"),
    "nl": ("Nederlands", "Néerlandais", "nl"),
    "ne": ("नेपाली", "Népalais", "ne"),
    "si": ("සිංහල", "Cingalais", "si"),
    "km": ("ខ្មែរ", "Khmer", "km"),
    "so": ("Soomaali", "Somali", "so"),
    "zu": ("isiZulu", "Zoulou", "zu"),
    "rw": ("Ikinyarwanda", "Kinyarwanda", "rw"),
    "el": ("Ελληνικά", "Grec", "el"),
    "hu": ("Magyar", "Hongrois", "hu"),
    "cs": ("Čeština", "Tchèque", "cs"),
}
SUPPORTED = tuple(LANGUAGES)
RTL = {"ar", "ur", "fa"}  # langues écrites de droite à gauche
ALIASES = {"jw": "jv", "fil": "tl", "in": "id"}  # variantes envoyées par certains appareils

lang_var: ContextVar[str] = ContextVar("lang", default=DEFAULT)


def _normalize(tag: str | None) -> str | None:
    if not tag:
        return None
    base = tag.strip().lower().replace("_", "-").split("-")[0]
    base = ALIASES.get(base, base)
    return base if base in LANGUAGES else None


def parse_accept_language(header: str | None) -> str:
    """« en-US,en;q=0.9,fr;q=0.8 » → « en ». Langue non gérée → français."""
    if not header:
        return DEFAULT
    choices = []
    for i, part in enumerate(header.split(",")):
        piece = part.strip().split(";")
        tag = _normalize(piece[0])
        q = 1.0
        for param in piece[1:]:
            if param.strip().startswith("q="):
                try:
                    q = float(param.strip()[2:])
                except ValueError:
                    q = 0.0
        if tag and q > 0:
            choices.append((-q, i, tag))
    return min(choices)[2] if choices else DEFAULT


def resolve_language(query_lang: str | None, accept_language: str | None) -> str:
    return _normalize(query_lang) or parse_accept_language(accept_language)


@lru_cache(maxsize=None)
def catalog(lang: str) -> dict[str, str]:
    """Traductions d'une langue (clé = message français)."""
    data: dict[str, str] = {}
    path = LOCALES_DIR / f"{lang}.json"
    if path.exists() and not lang.startswith("_"):
        data = {k: v for k, v in json.loads(path.read_text(encoding="utf-8")).items() if v}
    if lang == "en":
        data.update(EN)  # l'anglais relu à la main a priorité
    return data


@lru_cache(maxsize=None)
def _patterns(lang: str) -> list[tuple[re.Pattern, str]]:
    out = []
    for fr, translated in catalog(lang).items():
        if "{}" in fr:
            regex = "^" + re.escape(fr).replace(re.escape("{}"), "(.+?)") + "$"
            out.append((re.compile(regex, re.S), translated))
    return out


def _lookup_list(fragment: str, lang: str) -> str:
    """Traduit aussi les listes « a, b, c » (règles de mot de passe…)."""
    table = catalog(lang)
    pieces = [p.strip() for p in fragment.split(", ")]
    if len(pieces) > 1 and all(p in table for p in pieces):
        return ", ".join(table[p] for p in pieces)
    return table.get(fragment, fragment)


def _lookup(message: str, lang: str) -> str | None:
    table = catalog(lang)
    if message in table:
        return table[message]
    for regex, translated in _patterns(lang):
        match = regex.match(message)
        if match and translated.count("{}") == len(match.groups()):
            parts = iter(_lookup_list(g, lang) for g in match.groups())
            return re.sub(r"\{\}", lambda _: next(parts), translated)
    return None


def translate(message: str | None, lang: str | None = None) -> str | None:
    if message is None:
        return None
    lang = lang or lang_var.get()
    message = HTTP_FR.get(message, message)
    if lang == DEFAULT:
        return message
    return _lookup(message, lang) or (_lookup(message, "en") if lang != "en" else None) or message


def validation_message(error: dict, lang: str | None = None) -> str:
    lang = lang or lang_var.get()
    ctx = dict(error.get("ctx") or {})
    if "error" in ctx:
        ctx["error"] = translate(str(ctx["error"]).removeprefix("Value error, "), lang)
    kind = error.get("type", "")
    fr_template = VALIDATION["fr"].get(kind)
    if fr_template:
        if lang == DEFAULT:
            template = fr_template
        elif lang == "en":
            template = VALIDATION["en"][kind]
        else:
            template = catalog(lang).get(fr_template) or VALIDATION["en"][kind]
        try:
            return template.format(**ctx)
        except (KeyError, IndexError, ValueError):
            pass
    return translate(str(error.get("msg", "")).removeprefix("Value error, "), lang) or ""


def source_messages() -> list[str]:
    """Tous les textes à traduire : messages relevés dans le code (tools.extract_messages → locales/_sources.json),
    plus le dictionnaire anglais, les messages de validation et les messages HTTP."""
    extracted = []
    path = LOCALES_DIR / "_sources.json"
    if path.exists():
        extracted = json.loads(path.read_text(encoding="utf-8"))
    return sorted(set(extracted) | set(EN) | set(VALIDATION["fr"].values()) | set(HTTP_FR.values()))
