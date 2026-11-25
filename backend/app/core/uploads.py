"""Images envoyées par les utilisateurs : contrôle réel du contenu, redimensionnement, ré-encodage
(supprime les métadonnées EXIF, dont la position GPS)."""

import io
import re
import secrets
from datetime import datetime
from pathlib import Path

from PIL import Image, ImageOps, UnidentifiedImageError

from app.core.config import get_settings
from app.core.images import make_thumb, make_webp, thumb_path, webp_path
from app.core.responses import ApiError

Image.MAX_IMAGE_PIXELS = 40_000_000  # protège contre les « bombes de décompression »
ALLOWED_FORMATS = {"JPEG", "PNG", "WEBP", "GIF"}
_SAFE_NAME = re.compile(r"^[A-Za-z0-9._-]+$")


def _invalid(message: str) -> ApiError:
    return ApiError(422, "INVALID_IMAGE", message, [{"field": "photo", "message": message}])


def save_image(raw: bytes, folder: str, owner_id: int, max_bytes: int, max_side: int) -> str:
    if not raw:
        raise _invalid("Fichier vide")
    if len(raw) > max_bytes:
        raise _invalid(f"Image trop lourde (maximum {max_bytes // (1024 * 1024)} Mo)")
    try:
        with Image.open(io.BytesIO(raw)) as probe:
            fmt = probe.format
            probe.verify()
        if fmt not in ALLOWED_FORMATS:
            raise _invalid("Format accepté : JPEG, PNG, WEBP ou GIF")
        img = Image.open(io.BytesIO(raw))
        img = ImageOps.exif_transpose(img)
    except (UnidentifiedImageError, Image.DecompressionBombError, OSError, SyntaxError):
        raise _invalid("Ce fichier n'est pas une image valide") from None

    img.thumbnail((max_side, max_side))
    has_alpha = img.mode in ("RGBA", "LA") or (img.mode == "P" and "transparency" in img.info)
    ext = "png" if has_alpha else "jpg"
    img = img.convert("RGBA" if has_alpha else "RGB")

    name = f"{datetime.now():%Y%m%d%H%M%S}_{secrets.token_hex(6)}_{owner_id}.{ext}"
    target_dir = Path(get_settings().uploads_dir) / folder
    target_dir.mkdir(parents=True, exist_ok=True)
    if has_alpha:
        img.save(target_dir / name, "PNG", optimize=True)
    else:
        img.save(target_dir / name, "JPEG", quality=88, optimize=True)
    make_webp(target_dir / name)  # version légère servie par Apache aux navigateurs qui l'acceptent
    make_thumb(target_dir / name)  # vignette 400 px (adresse ?w=400) pour les listes
    return name


def delete_file(folder: str, name: str | None, keep: tuple[str, ...] = ("default-avatar.png",)) -> None:
    if not name or name in keep or not _SAFE_NAME.match(name):
        return
    path = Path(get_settings().uploads_dir) / folder / name
    path.unlink(missing_ok=True)
    webp_path(path).unlink(missing_ok=True)
    thumb_path(path).unlink(missing_ok=True)


def public_url(folder: str, name: str | None, default: str | None = "default-avatar.png") -> str | None:
    if not name or name == default:
        return None
    return f"{get_settings().media_base_url.rstrip('/')}/{folder}/{name}"
