"""Les tests tournent sur nufotec_db dans une transaction annulée à la fin :
aucune donnée réelle n'est modifiée, aucun email n'est envoyé, les images vont dans un dossier temporaire."""

import os
import tempfile

_TMP_UPLOADS = os.path.join(tempfile.mkdtemp(prefix="nufotec-site-"), "attachments")
os.makedirs(_TMP_UPLOADS)
_TMP_PRIVATE = tempfile.mkdtemp(prefix="nufotec-private-")
os.environ.update({"APP_ENV": "test", "MAIL_MODE": "console", "UPLOADS_DIR": _TMP_UPLOADS,
                   "LOG_LEVEL": "WARNING", "OUTBOX_WORKER": "false", "PUSH_MODE": "console",
                   "PRIVATE_DIR": _TMP_PRIVATE, "EMBEDDINGS_BACKEND": "hash",
                   # limites de débit très hautes : la suite de tests envoie des milliers de requêtes depuis la même IP
                   "RATE_LIMIT_IP_PER_MINUTE": "1000000", "RATE_LIMIT_USER_PER_MINUTE": "1000000"})

import pytest  # noqa: E402
from fastapi.testclient import TestClient  # noqa: E402
from sqlalchemy.orm import Session  # noqa: E402

from app.core import mailer, push  # noqa: E402
from app.core.database import SessionLocal, engine, get_db, set_background_session_factory  # noqa: E402
from app.main import app  # noqa: E402


@pytest.fixture
def uploads_dir():
    return _TMP_UPLOADS


@pytest.fixture
def db():
    conn = engine.connect()
    trans = conn.begin()
    session = Session(bind=conn, join_transaction_mode="create_savepoint", expire_on_commit=False)

    class _Shared:
        """Les tâches hors requête (emails, Idempotency-Key, push) utilisent la même session de test."""

        def __getattr__(self, name):
            return getattr(session, name)

        def close(self):
            pass

    set_background_session_factory(_Shared)
    try:
        yield session
    finally:
        set_background_session_factory(SessionLocal)
        session.close()
        trans.rollback()
        conn.close()


@pytest.fixture
def outbox():
    mailer.outbox.clear()
    return mailer.outbox


@pytest.fixture
def pushes():
    push.sent.clear()
    return push.sent


@pytest.fixture
def client(db, outbox):
    app.dependency_overrides[get_db] = lambda: db
    target = _measured(app) if os.environ.get("ROUTE_COVERAGE") else app
    with TestClient(target, raise_server_exceptions=False) as c:
        yield c
    app.dependency_overrides.clear()



# ───── Mesure facultative : quelles opérations de l'API les tests appellent (ROUTE_COVERAGE=fichier) ─────
_seen_routes: set[str] = set()


def _measured(asgi_app):
    async def wrapper(scope, receive, send):
        await asgi_app(scope, receive, send)
        route = scope.get("route")
        if scope.get("type") == "http" and route is not None and hasattr(route, "path"):
            _seen_routes.add(f"{scope['method']} {route.path}")
    return wrapper


def pytest_sessionfinish(session, exitstatus):
    if os.environ.get("ROUTE_COVERAGE"):
        with open(os.environ["ROUTE_COVERAGE"], "w", encoding="utf-8") as fh:
            fh.write("\n".join(sorted(_seen_routes)) + "\n")
