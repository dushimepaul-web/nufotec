"""Revalidation des réponses publiques (ETag / 304 Not Modified) pour les connexions lentes.

Chaque réponse JSON publique (GET, sans compte) reçoit une empreinte de son contenu (ETag). Le navigateur ou l'app
la renvoie à la requête suivante (If-None-Match) ; si rien n'a changé, l'API répond « 304 » sans corps :
quelques centaines d'octets au lieu de la liste complète (catalogue, médecins, actualités, paramètres du site…).
Les données restent toujours à jour : Cache-Control « no-cache » oblige à revalider à chaque fois.

Jamais pour les réponses liées à un compte (en-tête Authorization), les réponses qui fixent déjà leur
Cache-Control (fichiers, flux, documents privés) ni les réponses qui posent un cookie."""

import hashlib

from starlette.middleware.base import BaseHTTPMiddleware
from starlette.requests import Request
from starlette.responses import Response

MAX_BODY = 2 * 1024 * 1024  # au-delà, pas d'empreinte (évite de garder un gros corps en mémoire)


def _matches(if_none_match: str, etag: str) -> bool:
    candidates = {c.strip().removeprefix("W/") for c in if_none_match.split(",")}
    return "*" in candidates or etag.removeprefix("W/") in candidates


class ETagMiddleware(BaseHTTPMiddleware):
    async def dispatch(self, request: Request, call_next):
        response = await call_next(request)
        if (request.method != "GET" or response.status_code != 200 or "authorization" in request.headers
                or not response.headers.get("content-type", "").startswith("application/json")
                or "cache-control" in response.headers or "set-cookie" in response.headers):
            return response

        chunks, size = [], 0
        async for chunk in response.body_iterator:
            chunks.append(chunk)
            size += len(chunk)
        body = b"".join(chunks)
        headers = {k: v for k, v in response.headers.items() if k.lower() != "content-length"}
        if size > MAX_BODY:
            return Response(content=body, status_code=200, headers=headers)

        etag = f'W/"{hashlib.blake2b(body, digest_size=16).hexdigest()}"'
        headers.update({"ETag": etag, "Cache-Control": "no-cache",
                        "Vary": "Accept-Language, Authorization"})
        if _matches(request.headers.get("if-none-match", ""), etag):
            not_modified = {k: v for k, v in headers.items() if k.lower() != "content-type"}
            return Response(status_code=304, headers=not_modified)
        return Response(content=body, status_code=200, headers=headers)
