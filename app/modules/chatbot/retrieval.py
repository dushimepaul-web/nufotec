"""Recherche hybride : par le sens (vecteurs, calcul en mémoire) + par les mots (FULLTEXT MySQL),
fusionnées par Reciprocal Rank Fusion. Les vecteurs sont gardés en cache et rechargés quand l'index change."""

import threading
from dataclasses import dataclass

import numpy as np
from sqlalchemy import func, select, text
from sqlalchemy.orm import Session

from app.models import RagChunk, RagSource
from app.modules.chatbot import embeddings

_lock = threading.Lock()
_cache: dict = {"key": None, "ids": None, "matrix": None}


@dataclass
class Hit:
    chunk_id: int
    title: str | None
    content: str
    source: str
    ref: str
    score: float  # similarité cosinus (0 à 1)


def _index_key(db: Session):
    return tuple(db.execute(select(func.count(RagChunk.id), func.max(RagChunk.id), func.sum(RagSource.chunks_count))
                            .select_from(RagChunk).join(RagSource, RagSource.id == RagChunk.source_id)
                            .where(RagSource.is_active.is_(True))).one())


def _matrix(db: Session):
    key = _index_key(db)
    with _lock:
        if _cache["key"] != key:
            rows = db.execute(select(RagChunk.id, RagChunk.embedding).join(RagSource, RagSource.id == RagChunk.source_id)
                              .where(RagSource.is_active.is_(True), RagChunk.embedding.is_not(None))).all()
            ids = np.array([r[0] for r in rows], dtype=np.int64)
            matrix = np.vstack([embeddings.from_bytes(r[1]) for r in rows]) if rows else np.zeros((0, 1), np.float32)
            _cache.update(key=key, ids=ids, matrix=matrix)
        return _cache["ids"], _cache["matrix"]


def search(db: Session, query: str, top_k: int = 5, pool: int = 25) -> list[Hit]:
    ids, matrix = _matrix(db)
    vector_rank: dict[int, int] = {}
    cosine: dict[int, float] = {}
    if len(ids):
        q = embeddings.embed([query])[0]
        if matrix.shape[1] == q.shape[0]:
            scores = matrix @ q
            order = np.argsort(-scores)[:pool]
            for rank, i in enumerate(order):
                vector_rank[int(ids[i])] = rank
                cosine[int(ids[i])] = float(scores[i])

    text_rank: dict[int, int] = {}
    try:
        rows = db.execute(text(
            "SELECT c.id FROM rag_chunks c JOIN rag_sources s ON s.id = c.source_id "
            "WHERE s.is_active = 1 AND MATCH(c.title, c.content) AGAINST (:q IN NATURAL LANGUAGE MODE) "
            "ORDER BY MATCH(c.title, c.content) AGAINST (:q IN NATURAL LANGUAGE MODE) DESC LIMIT :n"),
            {"q": query[:500], "n": pool}).all()
        text_rank = {r[0]: rank for rank, r in enumerate(rows)}
    except Exception:
        text_rank = {}

    fused: dict[int, float] = {}
    for ranks in (vector_rank, text_rank):
        for chunk_id, rank in ranks.items():
            fused[chunk_id] = fused.get(chunk_id, 0.0) + 1.0 / (60 + rank)
    best = sorted(fused, key=lambda i: -fused[i])[:top_k]
    if not best:
        return []
    chunks = {c.id: c for c in db.scalars(select(RagChunk).where(RagChunk.id.in_(best)))}
    names = dict(db.execute(select(RagSource.id, RagSource.name)).all())
    hits = []
    for chunk_id in best:
        c = chunks.get(chunk_id)
        if c is not None:
            hits.append(Hit(c.id, c.title, c.content, names.get(c.source_id, ""), c.ref_key,
                            round(cosine.get(chunk_id, 0.0), 4)))
    return hits
