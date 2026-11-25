import asyncio
import contextlib
import logging
import time
from contextlib import asynccontextmanager

from fastapi import APIRouter, FastAPI
from starlette.concurrency import run_in_threadpool
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy import text

from app.core.config import get_settings
from app.core.database import engine
from app.core.caching import ETagMiddleware
from app.core.compression import SelectiveGZipMiddleware
from app.core.idempotency import IdempotencyMiddleware
from app.core.observability import RequestContextMiddleware, setup_logging
from app.core.protection import ProtectionMiddleware
from app.core.responses import error_response, ok, register_error_handlers
from app.modules.account import router as account_router
from app.modules.auth.router import router as auth_router
from app.modules.chatbot import router as chatbot
from app.modules.content import router_admin as content_admin
from app.modules.content import router_public as content_public
from app.modules.investors import router as investors
from app.modules.media import router as media
from app.modules.shop import router as shop
from app.modules.consultations import router as consultations
from app.modules.reference.router import router as reference_router
from app.modules.roles.router import router as roles_router
from app.modules.users.journal import router as journal_router
from app.modules.users.router import router as users_router

settings = get_settings()
setup_logging(settings.log_level)
log = logging.getLogger("nufotec.worker")


def _send_reminders() -> None:
    from app.core.database import background_session
    from app.modules.consultations.service import send_due_reminders
    from app.modules.investors.service import send_followups

    db = background_session()
    try:
        send_due_reminders(db)
        send_followups(db)
    finally:
        db.close()


def _nightly_rag_sync() -> None:
    """Une fois par nuit (après 2 h) : resynchronise les tables données au chatbot et la base de connaissances."""
    from app.core.database import background_session
    from app.core.helpers import now
    from app.modules.chatbot import indexer
    from app.modules.chatbot import settings as chat_settings

    current = now()
    if current.hour < 2:
        return
    db = background_session()
    try:
        if chat_settings.get(db, "last_nightly_sync") == current.strftime("%Y-%m-%d"):
            return
        chat_settings.set_many(db, {"last_nightly_sync": current.strftime("%Y-%m-%d")})
        db.commit()
        indexer.nightly_sync(db)
    finally:
        db.close()


_last_image_pass: str | None = None


def _nightly_images() -> None:
    """Une fois par nuit (après 3 h) : versions WebP des nouvelles images, dont celles déposées par le site PHP."""
    global _last_image_pass
    from pathlib import Path

    from app.core.helpers import now
    from app.core.images import optimize_folder

    current = now()
    today = current.strftime("%Y-%m-%d")
    if current.hour < 3 or _last_image_pass == today:
        return
    _last_image_pass = today
    stats = optimize_folder(Path(settings.uploads_dir))
    log.info("Images WebP : %s créée(s) sur %s image(s) en %s s", stats["creees"], stats["images"], stats["secondes"])


async def _outbox_loop() -> None:
    """Toutes les minutes : rappels de consultation, puis envoi des emails dus (avec nouvelles tentatives)."""
    from app.core.mailer import deliver_pending

    while True:
        try:
            await run_in_threadpool(_send_reminders)
            await run_in_threadpool(_nightly_rag_sync)
            await run_in_threadpool(_nightly_images)
            await run_in_threadpool(deliver_pending, 50)
        except Exception:
            log.exception("Tâche de fond (rappels, emails)")
        await asyncio.sleep(60)


@asynccontextmanager
async def lifespan(_: FastAPI):
    task = asyncio.create_task(_outbox_loop()) if settings.outbox_worker else None
    yield
    if task:
        task.cancel()
        with contextlib.suppress(asyncio.CancelledError):
            await task

app = FastAPI(
    title=settings.app_name,
    version="1.9.0",
    description="API REST de Nufotec (réécriture Python du backend CodeIgniter).",
    # Documentation masquée en production.
    docs_url=None if settings.is_production else "/docs",
    redoc_url=None if settings.is_production else "/redoc",
    openapi_url=None if settings.is_production else "/openapi.json",
    lifespan=lifespan,
)

# Ordre d'exécution : CORS → contexte (langue, request id, erreurs 500) → protections (en-têtes, taille, débit)
# → Idempotency-Key → ETag (304 si rien n'a changé) → routes.
# (add_middleware place chaque nouveau middleware à l'extérieur des précédents.)
app.add_middleware(ETagMiddleware)
app.add_middleware(IdempotencyMiddleware)
app.add_middleware(ProtectionMiddleware)
app.add_middleware(RequestContextMiddleware)
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.cors_origin_list,
    allow_credentials="*" not in settings.cors_origin_list,
    allow_methods=["*"],
    allow_headers=["*"],
    expose_headers=["X-Request-ID", "Retry-After", "Idempotent-Replayed", "Content-Language"],
)
# Compression gzip (connexions lentes), à l'extérieur de tout le reste ; jamais pour les fichiers médias
app.add_middleware(SelectiveGZipMiddleware)
register_error_handlers(app)

api = APIRouter(prefix="/api/v1")


@api.get("/health/live", tags=["Système"], summary="L'API tourne (sans interroger la base)")
def health_live():
    """Pour le superviseur (NSSM, systemd, Docker) : redémarrer l'API seulement si elle ne répond plus du tout."""
    return ok({"status": "ok"})


@api.get("/health", tags=["Système"], summary="État de l'API et de la base")
def health():
    """Pour la surveillance (Uptime Kuma, UptimeRobot…) : 200 si tout va bien, 503 si la base est injoignable."""
    start = time.perf_counter()
    try:
        with engine.connect() as conn:
            conn.execute(text("SELECT 1"))
    except Exception:
        log.exception("Contrôle de santé : base de données injoignable")
        return error_response(503, "SERVICE_UNAVAILABLE", "Service momentanément indisponible, réessayez dans un instant",
                              {"database": "error"}, {"Retry-After": "5"})
    data = {"status": "ok", "database": "ok", "database_ms": round((time.perf_counter() - start) * 1000, 1)}
    if not settings.is_production:  # l'environnement n'est pas divulgué en production
        data["env"] = settings.app_env
    return ok(data)


api.include_router(auth_router)
api.include_router(reference_router)
api.include_router(users_router)
api.include_router(roles_router)
api.include_router(journal_router)
api.include_router(consultations.public)
api.include_router(consultations.patient)
api.include_router(consultations.doctor)
api.include_router(consultations.admin)
api.include_router(chatbot.public)
api.include_router(chatbot.admin)
api.include_router(investors.public)
api.include_router(investors.investor)
api.include_router(investors.broker)
api.include_router(investors.admin)
api.include_router(shop.public)
api.include_router(shop.customer)
api.include_router(shop.admin)
api.include_router(media.listener)
api.include_router(media.public)
api.include_router(media.admin)
api.include_router(content_public.router)
api.include_router(content_admin.router)
api.include_router(content_admin.dashboard_router)
api.include_router(account_router.router)
app.include_router(api)
