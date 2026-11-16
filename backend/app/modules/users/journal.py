"""Journal d'activité général (admin) : qui a fait quoi, sur tout le site, et événements de sécurité."""

import math
from datetime import date, datetime, timedelta
from typing import Literal

from fastapi import APIRouter, Depends, Query
from sqlalchemy import func, or_, select
from sqlalchemy.orm import Session, aliased

from app.core.database import get_db
from app.core.helpers import like_pattern
from app.core.pagination import Page, PageMeta, PageParams, paginate
from app.core.permissions import require_permission
from app.core.responses import Envelope, ok
from app.models import Log, User, UserActivity

router = APIRouter(prefix="/admin/journal", tags=["Admin · Journal d'activité"])
READ = Depends(require_permission("journal.read"))


def _period(query, column, du: date | None, au: date | None):
    if du:
        query = query.where(column >= datetime.combine(du, datetime.min.time()))
    if au:
        query = query.where(column < datetime.combine(au + timedelta(days=1), datetime.min.time()))
    return query


@router.get("", response_model=Envelope[Page[dict]],
            summary="Activités de tous les modules (filtres : module, action, auteur, période, recherche)")
def activities(module: str | None = Query(None, max_length=50), action: str | None = Query(None, max_length=100),
               auteur_uuid: str | None = None, du: date | None = None, au: date | None = None,
               q: str | None = Query(None, max_length=100), page: PageParams = Depends(), db: Session = Depends(get_db),
               _: User = READ):
    author = aliased(User)
    query = select(UserActivity, author.prenom, author.nom, author.uuid).outerjoin(author, author.id == UserActivity.user_id)
    if module:
        query = query.where(UserActivity.module == module)
    if action:
        query = query.where(UserActivity.action == action)
    if auteur_uuid:
        query = query.where(author.uuid == auteur_uuid)
    if q:
        p = like_pattern(q)
        query = query.where(or_(UserActivity.description.like(p), UserActivity.item_name.like(p)))
    query = _period(query, UserActivity.created_at, du, au).order_by(UserActivity.id.desc())
    total = db.scalar(select(func.count()).select_from(query.order_by(None).subquery())) or 0
    rows = db.execute(query.limit(page.per_page).offset((page.page - 1) * page.per_page)).all()
    items = [{"id": a.id, "le": a.created_at, "module": a.module, "action": a.action, "description": a.description,
              "element": a.item_name, "auteur": f"{prenom} {nom}" if prenom else None, "auteur_uuid": uuid,
              "ip": a.ip_address} for a, prenom, nom, uuid in rows]
    meta = PageMeta(page=page.page, per_page=page.per_page, total=total, pages=max(math.ceil(total / page.per_page), 1))
    return ok({"items": items, "meta": meta})


@router.get("/modules", response_model=Envelope[list[dict]], summary="Modules et actions présents dans le journal")
def modules(db: Session = Depends(get_db), _: User = READ):
    rows = db.execute(select(UserActivity.module, UserActivity.action, func.count())
                      .group_by(UserActivity.module, UserActivity.action).order_by(UserActivity.module)).all()
    out: dict[str, list] = {}
    for module, action, n in rows:
        out.setdefault(module or "—", []).append({"action": action, "nombre": n})
    return ok([{"module": m, "actions": a} for m, a in out.items()])


@router.get("/securite", response_model=Envelope[Page[dict]],
            summary="Événements de sécurité : connexions échouées, codes, jetons réutilisés…")
def security(niveau: Literal["info", "warning", "error", "critical"] | None = None,
             action: str | None = Query(None, max_length=100), du: date | None = None, au: date | None = None,
             page: PageParams = Depends(), db: Session = Depends(get_db), _: User = READ):
    query = select(Log)
    if niveau:
        query = query.where(Log.niveau == niveau)
    if action:
        query = query.where(Log.action == action)
    rows, meta = paginate(db, _period(query, Log.created_at, du, au).order_by(Log.id.desc()), page)
    return ok({"items": [{"id": r.id, "le": r.created_at, "action": r.action, "niveau": r.niveau,
                          "description": r.description, "utilisateur_id": r.user_id, "ip": r.ip_address}
                         for r in rows], "meta": meta})

