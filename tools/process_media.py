"""Traite les médias existants (audio/vidéo) : durée, forme d'onde, version légère, miniature vidéo.

    .venv\\Scripts\\python -m tools.process_media            # médias « en_attente » ou « erreur »
    .venv\\Scripts\\python -m tools.process_media --tous     # tout retraiter

Lancement manuel uniquement (aucune tâche planifiée). Peut être interrompu puis relancé : il reprend."""

import argparse
import time

from sqlalchemy import select

from app.core.database import SessionLocal
from app.models import Media
from app.modules.media import processing


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--tous", action="store_true")
    args = parser.parse_args()
    if not processing.available():
        raise SystemExit("ffmpeg / ffprobe introuvables : installez ffmpeg sur le serveur")
    with SessionLocal() as db:
        query = select(Media.id, Media.titre).where(Media.type.in_(("audio", "video")), Media.fichier.is_not(None))
        if not args.tous:
            query = query.where(Media.traitement.in_(("en_attente", "erreur")))
        todo = db.execute(query.order_by(Media.id.desc())).all()
    print(f"{len(todo)} média(s) à traiter", flush=True)
    start, ok, ko = time.time(), 0, 0
    for n, (media_id, titre) in enumerate(todo, 1):
        processing.process(media_id)
        with SessionLocal() as db:
            m = db.get(Media, media_id)
            if m.traitement == "pret":
                ok += 1
            else:
                ko += 1
                print(f"  ✗ {titre} : {m.traitement_erreur}", flush=True)
        if n % 10 == 0 or n == len(todo):
            print(f"{n}/{len(todo)} – {ok} prêt(s), {ko} erreur(s) – {time.time() - start:.0f} s", flush=True)
    print("Terminé.")


if __name__ == "__main__":
    main()
