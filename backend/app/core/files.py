"""Fichiers privés (documents médicaux, ordonnances, preuves de paiement).

Ils sont rangés HORS du dossier web (PRIVATE_DIR) : aucune URL publique ne permet de les ouvrir.
Pour les consulter, l'API délivre — après contrôle d'accès — un lien signé valable quelques minutes :
/api/v1/fichiers/<jeton>. Le jeton est un JWT de type « file » ; il ne contient que l'identifiant du document."""

import io
import secrets
from datetime import UTC, datetime, timedelta
from pathlib import Path

import jwt
from PIL import Image, ImageOps, UnidentifiedImageError

from app.core.config import get_settings
from app.core.responses import ApiError

MAX_BYTES = 10 * 1024 * 1024
FILE_TOKEN = "file"


def _invalid(message: str) -> ApiError:
    return ApiError(422, "INVALID_FILE", message, [{"field": "fichier", "message": message}])


def private_root() -> Path:
    root = Path(get_settings().private_dir)
    root.mkdir(parents=True, exist_ok=True)
    return root


def save_private(raw: bytes, folder: str) -> tuple[str, str]:
    """Vérifie le contenu réel (PDF ou image), ré-encode les images (supprime EXIF/GPS) et enregistre.
    Renvoie (nom_stocké, type MIME)."""
    if not raw:
        raise _invalid("Fichier vide")
    if len(raw) > MAX_BYTES:
        raise _invalid("Fichier trop lourd (maximum 10 Mo)")

    if raw[:5] == b"%PDF-":
        if b"/JavaScript" in raw or b"/JS " in raw or b"/Launch" in raw:
            raise _invalid("PDF refusé : il contient du code exécutable")
        data, ext, mime = raw, "pdf", "application/pdf"
    else:
        try:
            with Image.open(io.BytesIO(raw)) as probe:
                fmt = probe.format
                probe.verify()
            if fmt not in ("JPEG", "PNG", "WEBP"):
                raise _invalid("Formats acceptés : PDF, JPEG, PNG ou WEBP")
            img = ImageOps.exif_transpose(Image.open(io.BytesIO(raw)))
        except (UnidentifiedImageError, Image.DecompressionBombError, OSError, SyntaxError):
            raise _invalid("Formats acceptés : PDF, JPEG, PNG ou WEBP") from None
        img.thumbnail((2400, 2400))  # garde la lisibilité d'un document photographié
        buffer = io.BytesIO()
        img.convert("RGB").save(buffer, "JPEG", quality=88, optimize=True)
        data, ext, mime = buffer.getvalue(), "jpg", "image/jpeg"

    name = f"{secrets.token_hex(16)}.{ext}"
    target = private_root() / folder
    target.mkdir(parents=True, exist_ok=True)
    (target / name).write_bytes(data)
    return name, mime


def private_path(folder: str, name: str) -> Path:
    path = (private_root() / folder / name).resolve()
    if private_root().resolve() not in path.parents:
        raise ApiError(404, "NOT_FOUND", "Fichier introuvable")
    return path


def delete_private(folder: str, name: str) -> None:
    private_path(folder, name).unlink(missing_ok=True)


LINK_PATHS = {"consultation": "/api/v1/fichiers", "dataroom": "/api/v1/investisseur/fichiers"}


def signed_link(document_id: int, scope: str = "consultation") -> tuple[str, datetime]:
    """Le « scope » lie le jeton à un type de document : un lien de data room n'ouvre jamais un document médical."""
    s = get_settings()
    expires = datetime.now(UTC) + timedelta(minutes=s.file_link_minutes)
    token = jwt.encode({"type": FILE_TOKEN, "scope": scope, "doc": document_id, "exp": expires}, s.jwt_secret,
                       algorithm=s.jwt_algorithm)
    return f"{s.public_api_url.rstrip('/')}{LINK_PATHS[scope]}/{token}", expires


def read_link(token: str, scope: str = "consultation") -> int:
    s = get_settings()
    try:
        payload = jwt.decode(token, s.jwt_secret, algorithms=[s.jwt_algorithm])
    except jwt.ExpiredSignatureError:
        raise ApiError(410, "LINK_EXPIRED", "Ce lien a expiré, demandez-en un nouveau") from None
    except jwt.InvalidTokenError:
        raise ApiError(404, "NOT_FOUND", "Fichier introuvable") from None
    if payload.get("type") != FILE_TOKEN or payload.get("scope", "consultation") != scope:
        raise ApiError(404, "NOT_FOUND", "Fichier introuvable")
    return int(payload["doc"])
