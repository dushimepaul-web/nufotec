from fastapi import APIRouter, FastAPI
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy import text

from app.core.config import get_settings
from app.core.database import engine
from app.core.observability import RequestContextMiddleware, setup_logging
from app.core.responses import ok, register_error_handlers
from app.modules.auth.router import router as auth_router

settings = get_settings()
setup_logging(settings.log_level)

app = FastAPI(
    title=settings.app_name,
    version="1.1.0",
    description="API REST de Nufotec (réécriture Python du backend CodeIgniter).",
    # Documentation masquée en production.
    docs_url=None if settings.is_production else "/docs",
    redoc_url=None if settings.is_production else "/redoc",
    openapi_url=None if settings.is_production else "/openapi.json",
)

# Ajouté en premier = exécuté à l'intérieur de CORS : les réponses 500 gardent les en-têtes CORS.
app.add_middleware(RequestContextMiddleware)
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.cors_origin_list,
    allow_credentials="*" not in settings.cors_origin_list,
    allow_methods=["*"],
    allow_headers=["*"],
    expose_headers=["X-Request-ID", "Retry-After"],
)
register_error_handlers(app)

api = APIRouter(prefix="/api/v1")


@api.get("/health", tags=["Système"], summary="État de l'API et de la base")
def health():
    with engine.connect() as conn:
        conn.execute(text("SELECT 1"))
    return ok({"status": "ok", "database": "ok", "env": settings.app_env})


api.include_router(auth_router)
app.include_router(api)
