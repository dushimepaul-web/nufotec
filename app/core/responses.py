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

T = TypeVar("T")


class Envelope(BaseModel, Generic[T]):
    success: bool = True
    message: str | None = None
    data: T | None = None


def ok(data: Any = None, message: str | None = None) -> dict:
    return {"success": True, "message": message, "data": data}


class ApiError(Exception):
    def __init__(self, status: int, code: str, message: str, details: Any = None,
                 headers: dict[str, str] | None = None):
        self.status, self.code, self.message, self.details = status, code, message, details
        self.headers = headers or {}


def _error(status: int, code: str, message: str, details: Any = None, headers=None) -> JSONResponse:
    body = {"success": False, "error": {"code": code, "message": message, "details": details}}
    return JSONResponse(status_code=status, content=body, headers=headers)


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
            {"field": ".".join(str(p) for p in e["loc"] if p != "body"), "message": e["msg"]}
            for e in exc.errors()
        ]
        return _error(422, "VALIDATION_ERROR", "Données invalides", details)

    @app.exception_handler(StarletteHTTPException)
    async def _http(_: Request, exc: StarletteHTTPException):
        code = _HTTP_CODES.get(exc.status_code, "HTTP_ERROR")
        return _error(exc.status_code, code, str(exc.detail))

