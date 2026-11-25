from collections.abc import Generator

from sqlalchemy import create_engine
from sqlalchemy.orm import DeclarativeBase, Session, sessionmaker

from app.core.config import get_settings

def _engine_options() -> dict:
    """pool_pre_ping : une connexion coupée par MySQL est remplacée sans erreur pour l'utilisateur.
    Délais : si MySQL est lent ou arrêté, la requête échoue vite (503 + Retry-After) au lieu de rester bloquée."""
    s = get_settings()
    options = {"pool_pre_ping": True, "pool_recycle": 3600}
    if s.database_url.startswith("mysql"):
        options.update(pool_size=s.db_pool_size, max_overflow=s.db_max_overflow, pool_timeout=s.db_pool_timeout,
                       connect_args={"connect_timeout": s.db_connect_timeout, "read_timeout": s.db_read_timeout,
                                     "write_timeout": s.db_read_timeout})
    return options


engine = create_engine(get_settings().database_url, **_engine_options())
SessionLocal = sessionmaker(bind=engine, autoflush=False, expire_on_commit=False)


class Base(DeclarativeBase):
    """Base des modèles. Les tables existent déjà dans nufotec_db :
    les modèles les décrivent, ils ne les créent pas."""


def get_db() -> Generator[Session, None, None]:
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


# Sessions ouvertes hors requête (envoi des emails, Idempotency-Key, push).
# Remplaçable dans les tests pour rester dans la transaction annulée.
_background_factory = SessionLocal


def set_background_session_factory(factory) -> None:
    global _background_factory
    _background_factory = factory


def background_session() -> Session:
    return _background_factory()
