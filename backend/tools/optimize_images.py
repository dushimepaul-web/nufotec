"""Crée les versions WebP des images existantes (attachments du site, et autres dossiers au besoin).

    .venv\\Scripts\\python -m tools.optimize_images                         # UPLOADS_DIR
    .venv\\Scripts\\python -m tools.optimize_images --dossier ..\\frontend\\public\\img
    .venv\\Scripts\\python -m tools.optimize_images --tout-refaire

Les originaux ne sont jamais modifiés. L'API refait aussi ce travail chaque nuit (images déposées par le site PHP)."""

import argparse
from pathlib import Path

from app.core.config import get_settings
from app.core.images import optimize_folder


def main() -> None:
    parser = argparse.ArgumentParser(description="Versions WebP des images")
    parser.add_argument("--dossier", action="append", help="dossier à traiter (par défaut UPLOADS_DIR)")
    parser.add_argument("--tout-refaire", action="store_true", help="recréer même les versions à jour")
    args = parser.parse_args()
    for folder in args.dossier or [get_settings().uploads_dir]:
        s = optimize_folder(Path(folder), force=args.tout_refaire)
        gain = 100 - 100 * s["apres"] / s["avant"] if s["avant"] else 0
        print(f"{folder} : {s['images']} image(s), {s['creees']} WebP créée(s), {s['inutiles']} sans gain ; "
              f"{s['avant'] / 1024 / 1024:.1f} Mo -> {s['apres'] / 1024 / 1024:.1f} Mo (-{gain:.0f} %) "
              f"en {s['secondes']} s")


if __name__ == "__main__":
    main()
