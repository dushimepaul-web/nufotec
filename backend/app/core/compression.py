"""Compression gzip des réponses (JSON, HTML) pour les connexions lentes : 60 à 80 % de données en moins.

Jamais pour les fichiers de la médiathèque (flux audio/vidéo, téléchargements, aperçus) : déjà compressés,
et la compression casserait la lecture par morceaux (en-tête Range, réponses 206).
"""
import re

from starlette.middleware.gzip import GZipMiddleware
from starlette.types import ASGIApp, Receive, Scope, Send

# fichiers servis tels quels : lecture en continu, téléchargement, aperçu, exports
RAW_PATHS = re.compile(r"/(flux|telecharger|apercu|lien|morceaux/\d+)$|\.csv$")


class SelectiveGZipMiddleware:
    def __init__(self, app: ASGIApp, minimum_size: int = 800, compresslevel: int = 6) -> None:
        self.app = app
        self.gzip = GZipMiddleware(app, minimum_size=minimum_size, compresslevel=compresslevel)

    async def __call__(self, scope: Scope, receive: Receive, send: Send) -> None:
        if scope["type"] == "http" and not RAW_PATHS.search(scope.get("path", "")):
            await self.gzip(scope, receive, send)
        else:
            await self.app(scope, receive, send)
