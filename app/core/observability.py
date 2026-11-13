"""Identifiant de requête et journal d'accès."""

import logging
import re
import time
import uuid
from contextvars import ContextVar

from starlette.middleware.base import BaseHTTPMiddleware
from starlette.requests import Request
from starlette.responses import JSONResponse

request_id_var: ContextVar[str] = ContextVar("request_id", default="-")
_SAFE_ID = re.compile(r"^[A-Za-z0-9._-]{8,64}$")
access_log = logging.getLogger("nufotec.access")
error_log = logging.getLogger("nufotec.errors")


class _RequestIdFilter(logging.Filter):
    def filter(self, record: logging.LogRecord) -> bool:
        record.request_id = request_id_var.get()
        return True


def setup_logging(level: str) -> None:
    handler = logging.StreamHandler()
    handler.addFilter(_RequestIdFilter())
    handler.setFormatter(logging.Formatter("%(asctime)s %(levelname)s [%(request_id)s] %(name)s: %(message)s"))
    root = logging.getLogger("nufotec")
    root.handlers = [handler]
    root.setLevel(level.upper())
    root.propagate = False


class RequestContextMiddleware(BaseHTTPMiddleware):
    """Réutilise X-Request-ID s'il est fourni (et sûr), sinon en crée un ; le renvoie dans la réponse."""

    async def dispatch(self, request: Request, call_next):
        incoming = request.headers.get("x-request-id", "")
        rid = incoming if _SAFE_ID.match(incoming) else uuid.uuid4().hex
        token = request_id_var.set(rid)
        start = time.perf_counter()
        try:
            try:
                response = await call_next(request)
            except Exception:
                # Erreur non gérée : le détail reste dans le journal, le client reçoit l'identifiant.
                error_log.exception("Erreur non gérée sur %s %s", request.method, request.url.path)
                response = JSONResponse(status_code=500, content={"success": False, "error": {
                    "code": "INTERNAL_ERROR", "message": "Une erreur interne est survenue",
                    "details": {"request_id": rid}}})
            response.headers["X-Request-ID"] = rid
            access_log.info("%s %s -> %s (%.0f ms)", request.method, request.url.path, response.status_code,
                            (time.perf_counter() - start) * 1000)
            return response
        finally:
            request_id_var.reset(token)
