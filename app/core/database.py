from collections.abc import Generator

from sqlalchemy import create_engine
from sqlalchemy.orm import DeclarativeBase, Session, sessionmaker

from app.core.config import get_settings

engine = create_engine(get_settings().database_url, pool_pre_ping=True, pool_recycle=3600)
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
