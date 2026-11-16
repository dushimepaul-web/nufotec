"""Indexation des sources : articles, documents envoyés, tables choisies par l'admin.

Chaque source est transformée en passages (rag_chunks) avec leur vecteur. Les passages inchangés
(même empreinte) gardent leur vecteur : resynchroniser une table de 5 000 lignes ne recalcule que ce qui a changé."""

import hashlib
import logging

from sqlalchemy import delete, select
from sqlalchemy.orm import Session

from app.core.files import private_path
from app.core.helpers import now
from app.models import ChatbotKb, RagChunk, RagDocument, RagSource, RagTableRule
from app.modules.chatbot import catalog, embeddings
from app.modules.chatbot.extract import chunk, extract_text

log = logging.getLogger("nufotec.rag")
PRIVATE_FOLDER = "chatbot"
KB_SOURCE_NAME = "Base de connaissances"


def ensure_kb_source(db: Session) -> RagSource:
    source = db.scalar(select(RagSource).where(RagSource.kind == "kb"))
    if source is None:
        source = RagSource(kind="kb", name=KB_SOURCE_NAME, status="pending", created_at=now())
        db.add(source)
        db.flush()
    return source


def _items_kb(db: Session) -> list[tuple[str, str, str]]:
    items = []
    for article in db.scalars(select(ChatbotKb).where(ChatbotKb.active.is_(True)).order_by(ChatbotKb.id)):
        title = article.title + (f" ({article.category})" if article.category else "")
        for i, piece in enumerate(chunk(article.content)):
            items.append((f"kb:{article.id}:{i}", title, piece))
    return items


def _items_document(db: Session, source: RagSource) -> list[tuple[str, str, str]]:
    items = []
    for doc in db.scalars(select(RagDocument).where(RagDocument.source_id == source.id)):
        raw = private_path(PRIVATE_FOLDER, doc.stored_name).read_bytes()
        ext = doc.original_name.rsplit(".", 1)[-1].lower()
        sections = extract_text(ext, raw)
        doc.text_chars = sum(len(t) for _, t in sections)
        for label, section in sections:
            for i, piece in enumerate(chunk(section)):
                items.append((f"doc:{doc.id}:{label}:{i}"[:120], f"{doc.original_name} – {label}", piece))
    return items


def _items_table(db: Session, rule: RagTableRule) -> list[tuple[str, str, str]]:
    columns = catalog.loads(rule.columns_json, [])
    filters = catalog.loads(rule.filters_json, [])
    real = catalog.validate_rule(db, rule.table_name, columns, filters, rule.template, rule.title_column)
    pk = catalog.primary_key(real)
    sql, params = catalog.build_select(rule.table_name, columns + ([rule.title_column] if rule.title_column else []),
                                       filters, pk, rule.max_rows)
    items = []
    for n, row in enumerate(db.execute(sql, params).mappings()):
        content = catalog.render_row(dict(row), columns, rule.template)
        if not content:
            continue
        title = str(row.get(rule.title_column) or "") if rule.title_column else ""
        key = f"{rule.table_name}:{row.get(pk) if pk else n}"
        for i, piece in enumerate(chunk(content)):
            items.append((f"{key}:{i}"[:120], (title or rule.table_name)[:255], piece))
    return items


def _store(db: Session, source: RagSource, items: list[tuple[str, str, str]]) -> int:
    existing = {c.content_hash: c.embedding for c in db.scalars(select(RagChunk).where(RagChunk.source_id == source.id))}
    hashes = [hashlib.sha256(f"{title}\n{content}".encode()).hexdigest() for _, title, content in items]
    missing = [i for i, h in enumerate(hashes) if h not in existing]
    vectors = embeddings.embed([f"{items[i][1]}\n{items[i][2]}" for i in missing])
    computed = {hashes[i]: embeddings.to_bytes(vectors[k]) for k, i in enumerate(missing)}
    db.execute(delete(RagChunk).where(RagChunk.source_id == source.id))
    for (ref, title, content), h in zip(items, hashes, strict=True):
        db.add(RagChunk(source_id=source.id, ref_key=ref, title=title[:255] if title else None, content=content,
                        content_hash=h, embedding=existing.get(h) or computed[h], created_at=now()))
    return len(missing)


def index_source(db: Session, source: RagSource) -> RagSource:
    source.status, source.error = "indexing", None
    db.commit()
    try:
        if source.kind == "kb":
            items = _items_kb(db)
        elif source.kind == "document":
            items = _items_document(db, source)
        else:
            rule = db.scalar(select(RagTableRule).where(RagTableRule.source_id == source.id))
            items = _items_table(db, rule)
        recomputed = _store(db, source, items)
        source.status, source.chunks_count, source.last_indexed_at = "ready", len(items), now()
        db.commit()
        log.info("Source %s indexée : %s passages (%s recalculés)", source.name, len(items), recomputed)
    except Exception as exc:
        db.rollback()
        source = db.get(RagSource, source.id)
        source.status = "error"
        source.error = (getattr(exc, "message", None) or f"{type(exc).__name__}: {exc}")[:500]
        db.commit()
        log.exception("Échec d'indexation de la source %s", source.name)
    return source


def index_source_by_id(source_id: int) -> None:
    """Pour les tâches de fond (session propre)."""
    from app.core.database import background_session

    db = background_session()
    try:
        source = db.get(RagSource, source_id)
        if source is not None:
            index_source(db, source)
    finally:
        db.close()


def nightly_sync(db: Session) -> int:
    """Resynchronise les tables en mode « nightly » et la base de connaissances."""
    count = 0
    sources = db.scalars(select(RagSource).join(RagTableRule, RagTableRule.source_id == RagSource.id)
                         .where(RagTableRule.schedule == "nightly", RagSource.is_active.is_(True)))
    for source in list(sources) + [ensure_kb_source(db)]:
        index_source(db, source)
        count += 1
    return count
