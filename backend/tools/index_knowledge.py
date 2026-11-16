"""Télécharge le modèle d'embeddings (si besoin) et indexe toutes les sources actives du chatbot.

    .venv\\Scripts\\python -m tools.index_knowledge"""

import os

os.environ.setdefault("HF_HUB_DISABLE_XET", "1")  # téléchargement classique (plus fiable que le transfert Xet)

from sqlalchemy import select  # noqa: E402

from app.core.database import SessionLocal  # noqa: E402
from app.models import RagSource  # noqa: E402
from app.modules.chatbot import embeddings, indexer, retrieval  # noqa: E402


def main() -> None:
    vectors = embeddings.embed(["Comment prendre rendez-vous avec un médecin ?", "How do I book a doctor?",
                                "Prix du café"])
    print(f"Modèle prêt : {vectors.shape[1]} dimensions ; similarité fr/en = {float(vectors[0] @ vectors[1]):.2f}, "
          f"fr/sans rapport = {float(vectors[0] @ vectors[2]):.2f}")
    db = SessionLocal()
    try:
        indexer.ensure_kb_source(db)
        db.commit()
        for source in db.scalars(select(RagSource).where(RagSource.is_active.is_(True))).all():
            result = indexer.index_source(db, source)
            print(f"- {result.name} : {result.status}, {result.chunks_count} passage(s)"
                  + (f" — {result.error}" if result.error else ""))
        for question in ("Comment se faire livrer ?", "Quels produits vendez-vous ?", "How can I invest?"):
            hits = retrieval.search(db, question, 2)
            print(f"« {question} » → " + " | ".join(f"{h.title} ({h.score:.2f})" for h in hits))
    finally:
        db.close()


if __name__ == "__main__":
    main()
