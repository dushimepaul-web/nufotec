"""En-tête Idempotency-Key : une requête rejouée (coupure réseau sur mobile) ne s'exécute qu'une fois.

L'app envoie `Idempotency-Key: <uuid>` sur un POST/PUT/PATCH/DELETE. La première réponse (sauf erreur 5xx)
est mémorisée 24 h ; la même requête avec la même clé reçoit la même réponse, avec l'en-tête
`Idempotent-Replayed: true`, sans rien exécuter. La clé est propre à son auteur (jeton ou IP) et au chemin.
Même clé avec un contenu différent → 422 IDEMPOTENCY_KEY_REUSED ; requête encore en cours → 409."""

import hashlib
import re
from datetime import timedelta

from sqlalchemy import delete
from sqlalchemy.exc import IntegrityError
from starlette.concurrency import run_in_threadpool
from starlette.middleware.base import BaseHTTPMiddleware
from starlette.requests import Request
from starlette.responses import Response

from app.core.client import client_ip
from app.core.config import get_settings
from app.core.database import background_session
from app.core.helpers import now
from app.core.responses import error_response

METHODS = {"POST", "PUT", "PATCH", "DELETE"}
_SAFE_KEY = re.compile(r"^[A-Za-z0-9._:-]{8,100}$")
STALE_SECONDS = 120  # une requête « en cours » depuis plus longtemps est considérée comme abandonnée


def _sha(data: bytes | str) -> str:
    return hashlib.sha256(data.encode() if isinstance(data, str) else data).hexdigest()


def _reserve(key_hash: str, request_hash: str):
    """Réserve la clé. Renvoie ("new", None), ("replay", (status, body, type)), ("conflict", None)
    ou ("in_progress", None)."""
    from app.models import IdempotencyKey

    db = background_session()
    try:
        hours = get_settings().idempotency_hours
        db.execute(delete(IdempotencyKey).where(IdempotencyKey.created_at < now() - timedelta(hours=hours)))
        db.add(IdempotencyKey(key_hash=key_hash, request_hash=request_hash, created_at=now()))
        try:
            db.commit()
            return "new", None
        except IntegrityError:
            db.rollback()
        row = db.get(IdempotencyKey, key_hash, populate_existing=True)
        if row is None:
            return "in_progress", None
        if row.request_hash != request_hash:
            return "conflict", None
        if row.status_code is None:
            if (now() - row.created_at).total_seconds() > STALE_SECONDS:
                row.created_at = now()  # requête abandonnée (serveur arrêté…) : on la reprend
                db.commit()
                return "new", None
            return "in_progress", None
        return "replay", (row.status_code, row.response_body or "", row.content_type)
    finally:
        db.close()


def _store(key_hash: str, status: int | None, body: str | None, content_type: str | None) -> None:
    from app.models import IdempotencyKey

    db = background_session()
    try:
        row = db.get(IdempotencyKey, key_hash)
        if row is not None:
            if status is None:
                db.delete(row)  # erreur serveur : la requête pourra être retentée
            else:
                row.status_code, row.response_body, row.content_type = status, body, content_type
            db.commit()
    finally:
        db.close()


class IdempotencyMiddleware(BaseHTTPMiddleware):
    async def dispatch(self, request: Request, call_next):
        key = request.headers.get("idempotency-key")
        if request.method not in METHODS or key is None:
            return await call_next(request)
        if not _SAFE_KEY.match(key):
            return error_response(400, "INVALID_IDEMPOTENCY_KEY",
                                  "Idempotency-Key invalide (8 à 100 caractères : lettres, chiffres, . _ : -)")

        body = await request.body()
        author = _sha(request.headers.get("authorization") or client_ip(request) or "-")
        key_hash = _sha(f"{key}|{request.method}|{request.url.path}|{author}")
        request_hash = _sha(body + b"|" + request.url.query.encode())

        state, saved = await run_in_threadpool(_reserve, key_hash, request_hash)
        if state == "conflict":
            return error_response(422, "IDEMPOTENCY_KEY_REUSED",
                                  "Cette Idempotency-Key a déjà servi pour une autre requête")
        if state == "in_progress":
            return error_response(409, "REQUEST_IN_PROGRESS",
                                  "Cette requête est déjà en cours de traitement, réessayez dans un instant",
                                  headers={"Retry-After": "2"})
        if state == "replay":
            status, text, content_type = saved
            return Response(content=text, status_code=status, media_type=content_type,
                            headers={"Idempotent-Replayed": "true"})

        try:
            response = await call_next(request)
        except Exception:
            await run_in_threadpool(_store, key_hash, None, None, None)
            raise
        content = b"".join([chunk async for chunk in response.body_iterator])
        if response.status_code >= 500:
            await run_in_threadpool(_store, key_hash, None, None, None)
        else:
            await run_in_threadpool(_store, key_hash, response.status_code, content.decode("utf-8", "replace"),
                                    response.headers.get("content-type"))
        headers = {k: v for k, v in response.headers.items() if k.lower() != "content-length"}
        return Response(content=content, status_code=response.status_code, headers=headers,
                        background=response.background)
