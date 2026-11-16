"""Enveloppe de réponse commune et erreurs de l'API.

Succès : {"success": true, "message": "...", "data": {...}}
Erreur : {"success": false, "error": {"code": "...", "message": "...", "details": ...}}
"""

from typing import Any, Generic, TypeVar

from fastapi import FastAPI, Request
from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse
from pydantic import BaseModel
from starlette.exceptions import HTTPException as StarletteHTTPException

from app.core.i18n import translate, validation_message

T = TypeVar("T")


class Envelope(BaseModel, Generic[T]):
    success: bool = True
    message: str | None = None
    data: T | None = None


def _with_timezone(value: Any) -> Any:
    """Toutes les dates sortent avec le fuseau du Burundi (+02:00), même dans les dictionnaires libres."""
    from datetime import datetime

    from app.core.types import localize

    if isinstance(value, datetime):
        return localize(value)
    if isinstance(value, dict):
        return {k: _with_timezone(v) for k, v in value.items()}
    if isinstance(value, list):
        return [_with_timezone(v) for v in value]
    return value


def ok(data: Any = None, message: str | None = None) -> dict:
    """Le message est traduit dans la langue de la requête ; les dates reçoivent leur fuseau."""
    return {"success": True, "message": translate(message), "data": _with_timezone(data)}


class ApiError(Exception):
    def __init__(self, status: int, code: str, message: str, details: Any = None,
                 headers: dict[str, str] | None = None):
        self.status, self.code, self.message, self.details = status, code, message, details
        self.headers = headers or {}


def _translate_details(details: Any) -> Any:
    if isinstance(details, list):
        return [{**d, "message": translate(d["message"])} if isinstance(d, dict) and isinstance(d.get("message"), str)
                else d for d in details]
    return details


def error_response(status: int, code: str, message: str, details: Any = None, headers=None) -> JSONResponse:
    """Le message (et les messages des détails) sont traduits dans la langue de la requête."""
    body = {"success": False, "error": {"code": code, "message": translate(message),
                                        "details": _translate_details(details)}}
    return JSONResponse(status_code=status, content=body, headers=headers)


_error = error_response


_HTTP_CODES = {
    400: "BAD_REQUEST", 401: "UNAUTHENTICATED", 403: "FORBIDDEN", 404: "NOT_FOUND",
    405: "METHOD_NOT_ALLOWED", 409: "CONFLICT", 429: "TOO_MANY_REQUESTS",
}


def register_error_handlers(app: FastAPI) -> None:
    @app.exception_handler(ApiError)
    async def _api_error(_: Request, exc: ApiError):
        headers = dict(exc.headers)
        if exc.status == 401:
            headers["WWW-Authenticate"] = "Bearer"
        return _error(exc.status, exc.code, exc.message, exc.details, headers or None)

    @app.exception_handler(RequestValidationError)
    async def _validation(_: Request, exc: RequestValidationError):
        details = [
            {"field": ".".join(str(p) for p in e["loc"] if p != "body"), "message": validation_message(e)}
            for e in exc.errors()
        ]
        return _error(422, "VALIDATION_ERROR", "Données invalides", details)

    @app.exception_handler(StarletteHTTPException)
    async def _http(_: Request, exc: StarletteHTTPException):
        code = _HTTP_CODES.get(exc.status_code, "HTTP_ERROR")
        return _error(exc.status_code, code, str(exc.detail))

