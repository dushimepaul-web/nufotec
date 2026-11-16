"""Embeddings (recherche par le sens), calculés EN LOCAL : aucune donnée n'est envoyée à un fournisseur.

Modèle par défaut : paraphrase-multilingual-MiniLM-L12-v2 (50+ langues, 384 dimensions, ~220 Mo),
téléchargé une seule fois dans EMBEDDINGS_CACHE_DIR. Mode « hash » : vecteurs lexicaux simples pour les tests."""

import hashlib
import re
import threading

import numpy as np

from app.core.config import get_settings

DIM_HASH = 384
_lock = threading.Lock()
_model = None


def _fastembed():
    global _model
    with _lock:
        if _model is None:
            from fastembed import TextEmbedding

            s = get_settings()
            _model = TextEmbedding(s.embeddings_model, cache_dir=s.embeddings_cache_dir)
    return _model


def _normalize(matrix: np.ndarray) -> np.ndarray:
    norms = np.linalg.norm(matrix, axis=1, keepdims=True)
    norms[norms == 0] = 1
    return (matrix / norms).astype(np.float32)


def _hash_vectors(texts: list[str]) -> np.ndarray:
    out = np.zeros((len(texts), DIM_HASH), dtype=np.float32)
    for i, text in enumerate(texts):
        for word in re.findall(r"\w{3,}", text.lower()):
            bucket = int(hashlib.md5(word.encode()).hexdigest()[:8], 16) % DIM_HASH
            out[i, bucket] += 1
    return out


def embed(texts: list[str]) -> np.ndarray:
    """Vecteurs normalisés (float32) : le produit scalaire donne la similarité cosinus."""
    if not texts:
        return np.zeros((0, DIM_HASH), dtype=np.float32)
    if get_settings().embeddings_backend == "hash":
        return _normalize(_hash_vectors(texts))
    return _normalize(np.array(list(_fastembed().embed(texts, batch_size=32)), dtype=np.float32))


def to_bytes(vector: np.ndarray) -> bytes:
    return vector.astype(np.float32).tobytes()


def from_bytes(blob: bytes) -> np.ndarray:
    return np.frombuffer(blob, dtype=np.float32)
