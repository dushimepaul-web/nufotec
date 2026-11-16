"""Protections globales : en-têtes de sécurité, taille maximale des requêtes, limite de débit.

Limite de débit (fenêtre glissante d'une minute) : par compte si un jeton est présent, sinon par adresse IP.
Compteurs en mémoire : suffisant pour un serveur ; avec plusieurs serveurs, il faudra un stockage partagé (Redis)."""

import threading
import time
from collections import defaultdict, deque

import jwt
from starlette.middleware.base import BaseHTTPMiddleware
from starlette.requests import Request

from app.core.client import client_ip
from app.core.config import get_settings
from app.core.responses import error_response

SECURITY_HEADERS = {
    "X-Content-Type-Options": "nosniff",
    "X-Frame-Options": "DENY",
    "Referrer-Policy": "strict-origin-when-cross-origin",
    "Permissions-Policy": "camera=(), microphone=(), geolocation=()",
}
HTML_CSP = "default-src 'none'; style-src 'unsafe-inline'; img-src 'self' data:; frame-ancestors 'none'"
API_CSP = "default-src 'none'; frame-ancestors 'none'"
DOCS_PREFIXES = ("/docs", "/redoc", "/openapi.json")  # Swagger charge ses scripts depuis un CDN : pas de CSP stricte
EXEMPT_PREFIXES = DOCS_PREFIXES + ("/api/v1/health",)


class RateLimiter:
    def __init__(self) -> None:
        self.hits: dict[str, deque] = defaultdict(deque)
        self.lock = threading.Lock()
        self.last_cleanup = time.monotonic()

    def check(self, key: str, limit: int, window: float = 60.0) -> int:
        """Renvoie 0 si la requête passe, sinon le nombre de secondes à attendre."""
        current = time.monotonic()
        with self.lock:
            bucket = self.hits[key]
            while bucket and bucket[0] <= current - window:
                bucket.popleft()
            if len(bucket) >= limit:
                return max(int(bucket[0] + window - current) + 1, 1)
            bucket.append(current)
            if current - self.last_cleanup > 300:  # ménage : les clés inactives disparaissent
                for k in [k for k, b in self.hits.items() if not b or b[-1] <= current - window]:
                    del self.hits[k]
                self.last_cleanup = current
        return 0

    def reset(self) -> None:
        with self.lock:
            self.hits.clear()


limiter = RateLimiter()


def _rate_key(request: Request) -> tuple[str, int]:
    s = get_settings()
    auth = request.headers.get("authorization", "")
    if auth.lower().startswith("bearer "):
        try:  # identification légère du compte (la vraie vérification se fait plus loin)
            sub = jwt.decode(auth[7:], s.jwt_secret, algorithms=[s.jwt_algorithm]).get("sub")
            if sub:
                return f"u:{sub}", s.rate_limit_user_per_minute
        except jwt.PyJWTError:
            pass
    return f"ip:{client_ip(request)}", s.rate_limit_ip_per_minute


class ProtectionMiddleware(BaseHTTPMiddleware):
    async def dispatch(self, request: Request, call_next):
        s = get_settings()
        path = request.url.path
        length = request.headers.get("content-length")
        if length and length.isdigit() and int(length) > s.max_request_mb * 1024 * 1024:
            return self._secure(error_response(413, "PAYLOAD_TOO_LARGE",
                                               f"Requête trop volumineuse (maximum {s.max_request_mb} Mo)"), path)
        # Lecture en continu : les lecteurs audio/vidéo font de nombreuses requêtes « Range » légitimes.
        if not path.startswith(EXEMPT_PREFIXES) and not path.endswith("/flux"):
            key, limit = _rate_key(request)
            wait = limiter.check(key, limit)
            if wait:
                return self._secure(error_response(429, "TOO_MANY_REQUESTS", "Trop de requêtes. Patientez un instant.",
                                                   {"retry_after_seconds": wait}, {"Retry-After": str(wait)}), path)
        return self._secure(await call_next(request), path)

    @staticmethod
    def _secure(response, path: str):
        for name, value in SECURITY_HEADERS.items():
            response.headers.setdefault(name, value)
        if not path.startswith(DOCS_PREFIXES):
            is_html = response.headers.get("content-type", "").startswith("text/html")
            response.headers.setdefault("Content-Security-Policy", HTML_CSP if is_html else API_CSP)
        if get_settings().is_production:  # HTTPS obligatoire en production
            response.headers.setdefault("Strict-Transport-Security", "max-age=31536000; includeSubDomains")
        return response
