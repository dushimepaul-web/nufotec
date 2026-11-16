"""Génère app/locales/<code>.json pour les 50 langues avec Google Translate (gratuit, sans clé).

    .venv\\Scripts\\python -m tools.generate_translations            # langues manquantes ou incomplètes
    .venv\\Scripts\\python -m tools.generate_translations --force    # tout régénérer
    .venv\\Scripts\\python -m tools.generate_translations --only sw rw

À relancer après avoir ajouté des messages dans app/core/i18n_messages.py : seuls les textes
manquants sont traduits. Les parties variables ({} , {min_length}…) sont protégées pendant la traduction
et vérifiées ensuite ; un texte dont une partie variable a été abîmée n'est pas gardé (l'API retombera
sur l'anglais pour ce texte). Le français est la source ; l'anglais relu à la main reste prioritaire."""

import argparse
import json
import re
import sys
import time

from deep_translator import GoogleTranslator
from deep_translator.exceptions import TooManyRequests

from app.core.i18n import LANGUAGES, LOCALES_DIR, source_messages

PLACEHOLDER = re.compile(r"\{[a-z_]*\}")
CHUNK_CHARS = 3500


def protect(text: str) -> tuple[str, list[str]]:
    """Remplace chaque {…} par un nombre improbable que Google laisse intact."""
    found = PLACEHOLDER.findall(text)
    for i, ph in enumerate(found):
        text = text.replace(ph, f"9{i}7{i}5", 1)
    return text, found


def restore(text: str, found: list[str]) -> str | None:
    for i, ph in enumerate(found):
        token = f"9{i}7{i}5"
        if token not in text:
            return None
        text = text.replace(token, ph, 1)
    if PLACEHOLDER.findall(text) != found:
        return None
    return text.strip()


class GoogleBusy(Exception):
    """Google demande de ralentir (429) : on attend puis on réessaie, sans contourner le blocage."""


def _call(translator: GoogleTranslator, text: str) -> str:
    for attempt in range(6):
        try:
            return translator.translate(text) or ""
        except TooManyRequests:
            wait = 60 * (attempt + 1)
            print(f"    Google demande de ralentir : pause de {wait} s…", file=sys.stderr, flush=True)
            time.sleep(wait)
    raise GoogleBusy()


def translate_batch(translator: GoogleTranslator, texts: list[str]) -> list[str | None]:
    """Envoie les textes par paquets (une ligne par texte) ; texte par texte si le paquet se décale."""
    results: list[str | None] = []
    chunk: list[str] = []

    def flush():
        if not chunk:
            return
        protected = [protect(t) for t in chunk]
        lines = _call(translator, "\n".join(p[0] for p in protected)).split("\n")
        if len(lines) != len(chunk):
            lines = []
            for p in protected:
                lines.append(_call(translator, p[0]))
                time.sleep(1.5)
        results.extend(restore(line, p[1]) for line, p in zip(lines, protected, strict=True))
        chunk.clear()
        time.sleep(3)  # bien en dessous de la limite de Google

    size = 0
    for text in texts:
        if size + len(text) > CHUNK_CHARS:
            flush()
            size = 0
        chunk.append(text)
        size += len(text) + 1
    flush()
    return results


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--force", action="store_true", help="tout retraduire")
    parser.add_argument("--only", nargs="*", help="codes de langue à traiter")
    args = parser.parse_args()

    LOCALES_DIR.mkdir(parents=True, exist_ok=True)
    sources = source_messages()
    # L'anglais est aussi généré : les traductions relues à la main (i18n_messages.EN) restent prioritaires.
    targets = [c for c in LANGUAGES if c != "fr" and (not args.only or c in args.only)]
    report = {}
    for code in targets:
        path = LOCALES_DIR / f"{code}.json"
        existing = {} if args.force or not path.exists() else json.loads(path.read_text(encoding="utf-8"))
        missing = [s for s in sources if not existing.get(s)]
        if not missing:
            report[code] = (len(sources), 0)
            continue
        google_code = LANGUAGES[code][2]
        print(f"{code} ({LANGUAGES[code][1]}) : {len(missing)} texte(s) à traduire…")
        translator = GoogleTranslator(source="fr", target=google_code)
        try:
            translated = translate_batch(translator, missing)
        except GoogleBusy:
            print("Google bloque encore : relancez la commande plus tard (elle reprendra ici).")
            break
        for src, dst in zip(missing, translated, strict=True):
            if dst:
                existing[src] = dst
        data = {s: existing[s] for s in sources if existing.get(s)}  # ordre stable, textes obsolètes retirés
        path.write_text(json.dumps(data, ensure_ascii=False, indent=1, sort_keys=False) + "\n", encoding="utf-8")
        report[code] = (len(data), len(sources) - len(data))

    print("\nRésultat (traduits / manquants) :")
    for code, (done, todo) in report.items():
        print(f"  {code:3} {done:4} / {todo}")


if __name__ == "__main__":
    main()
