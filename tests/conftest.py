"""Les tests tournent sur nufotec_db dans une transaction annulée à la fin :
aucune donnée réelle n'est modifiée, aucun email n'est envoyé, les images vont dans un dossier temporaire."""

import os
import tempfile

_TMP_UPLOADS = tempfile.mkdtemp(prefix="nufotec-uploads-")
os.environ.update({"APP_ENV": "test", "MAIL_MODE": "console", "UPLOADS_DIR": _TMP_UPLOADS,
                   "LOG_LEVEL": "WARNING"})

import pytest  # noqa: E402
from fastapi.testclient import TestClient  # noqa: E402
from sqlalchemy.orm import Session  # noqa: E402

from app.core import mailer  # noqa: E402
from app.core.database import engine, get_db  # noqa: E402
from app.main import app  # noqa: E402


@pytest.fixture
def uploads_dir():
    return _TMP_UPLOADS


@pytest.fixture
def db():
    conn = engine.connect()
    trans = conn.begin()
    session = Session(bind=conn, join_transaction_mode="create_savepoint", expire_on_commit=False)
    try:
        yield session
    finally:
        session.close()
        trans.rollback()
        conn.close()


@pytest.fixture
def outbox():
    mailer.outbox.clear()
    return mailer.outbox


@pytest.fixture
def client(db, outbox):
    app.dependency_overrides[get_db] = lambda: db
    with TestClient(app, raise_server_exceptions=False) as c:
        yield c
    app.dependency_overrides.clear()
