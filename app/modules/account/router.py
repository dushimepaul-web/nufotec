"""Espace « Mon compte » : ce qui concerne la personne connectée, pour n'afficher que les rubriques utiles."""

from fastapi import APIRouter, Depends
from sqlalchemy import func, select
from sqlalchemy.orm import Session

from app.core.database import get_db
from app.core.deps import get_current_user
from app.core.responses import Envelope, ok
from app.models import Broker, Investor, User
from app.modules.consultations.service import patient_query
from app.modules.shop.service import my_orders_query

router = APIRouter(tags=["Mon compte"])


def _count(db: Session, query) -> int:
    return db.scalar(select(func.count()).select_from(query.order_by(None).subquery())) or 0


@router.get("/mon-espace", response_model=Envelope[dict],
            summary="Résumé de mon compte : commandes, consultations, dossier investisseur, fiche courtier")
def my_space(user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    investor = db.scalar(select(Investor).where(Investor.user_id == user.id))
    broker = db.scalar(select(Broker).where(Broker.user_id == user.id))
    return ok({
        "commandes": _count(db, my_orders_query(user)),
        "consultations": _count(db, patient_query(user, None, None)),
        "investisseur": investor is not None,
        "investisseur_statut": investor.statut if investor else None,
        "courtier": broker is not None,
        "courtier_statut": broker.statut if broker else None,
        "type_utilisateur": user.type_utilisateur,
    })
