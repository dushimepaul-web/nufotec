"""Versions WebP des images (rapidité) : photo.jpg -> photo.jpg.webp, à côté de l'original.

L'original n'est jamais modifié. Apache sert la version WebP aux navigateurs qui l'acceptent, sous la même adresse
(règle dans attachments/.htaccess) : rien à changer dans la base, le site React ni le site PHP.
La version WebP est limitée à 1920 px de côté et n'est gardée que si elle est plus légère que l'original."""

import logging
import os
import time
from pathlib import Path

from PIL import Image, ImageOps, UnidentifiedImageError

log = logging.getLogger("nufotec.images")

SOURCES = (".jpg", ".jpeg", ".png")
MAX_SIDE = 1920
QUALITY = 80
TARGET_BYTES = 200 * 1024  # objectif : aucune image servie au-delà de 200 Ko
# essais successifs (qualité, plus grand côté) tant que l'image dépasse l'objectif
ATTEMPTS = ((QUALITY, MAX_SIDE), (72, 1600), (65, 1280))


def webp_path(path: Path) -> Path:
    return path.with_name(path.name + ".webp")


def is_current(path: Path) -> bool:
    """La version WebP existe et date d'après l'original (rien à refaire)."""
    target = webp_path(path)
    return target.exists() and target.stat().st_mtime >= path.stat().st_mtime


def make_webp(path: Path) -> int | None:
    """Crée (ou recrée) path.webp. Renvoie sa taille en octets, ou None si elle n'apporte rien."""
    target = webp_path(path)
    temp = target.with_name(f".{target.name}.{os.getpid()}.tmp")
    try:
        with Image.open(path) as source:
            base = ImageOps.exif_transpose(source)
            has_alpha = base.mode in ("RGBA", "LA") or (base.mode == "P" and "transparency" in base.info)
            base = base.convert("RGBA" if has_alpha else "RGB")
        for quality, max_side in ATTEMPTS:
            img = base.copy()
            img.thumbnail((max_side, max_side))
            img.save(temp, "WEBP", quality=quality, method=6)
            if temp.stat().st_size <= TARGET_BYTES:
                break
    except (UnidentifiedImageError, Image.DecompressionBombError, OSError, SyntaxError, ValueError) as exc:
        log.warning("WebP impossible pour %s : %s", path, exc)
        temp.unlink(missing_ok=True)
        return None
    size = temp.stat().st_size
    if size >= path.stat().st_size:  # pas plus léger : Apache continuera de servir l'original
        temp.unlink(missing_ok=True)
        target.unlink(missing_ok=True)
        return None
    os.replace(temp, target)  # remplacement atomique : jamais de fichier à moitié écrit servi
    return size


def optimize_folder(root: Path, force: bool = False) -> dict:
    """Crée les versions WebP manquantes ou périmées sous root. Peut être relancé : il ne refait que le nécessaire."""
    stats = {"images": 0, "creees": 0, "inutiles": 0, "avant": 0, "apres": 0}
    start = time.monotonic()
    for path in root.rglob("*"):
        if path.suffix.lower() not in SOURCES or not path.is_file() or path.name.startswith("."):
            continue
        stats["images"] += 1
        original = path.stat().st_size
        if not force and is_current(path):
            stats["avant"] += original
            stats["apres"] += webp_path(path).stat().st_size
            continue
        size = make_webp(path)
        stats["avant"] += original
        if size is None:
            stats["inutiles"] += 1
            stats["apres"] += original
        else:
            stats["creees"] += 1
            stats["apres"] += size
    stats["secondes"] = round(time.monotonic() - start, 1)
    return stats
