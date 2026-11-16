"""Traces : `user_activities` (visible dans le tableau de bord admin PHP) et `logs` (événements de sécurité)."""

import json

from sqlalchemy.orm import Session

from app.core.client import ClientInfo
from app.models import Log, UserActivity


def activity(db: Session, user_id: int | None, action: str, client: ClientInfo | None,
             description: str | None = None, new_data: dict | None = None, module: str = "auth",
             item_id: int | None = None, item_name: str | None = None, old_data: dict | None = None) -> None:
    """user_id = auteur de l'action ; item_id = élément concerné (par défaut l'auteur lui-même)."""
    db.add(UserActivity(
        user_id=user_id, action=action, module=module, item_id=item_id if item_id is not None else user_id,
        item_name=item_name, description=description,
        old_data=json.dumps(old_data, ensure_ascii=False, default=str) if old_data else None,
        ip_address=client.ip if client else None, user_agent=client.user_agent if client else None,
        new_data=json.dumps(new_data, ensure_ascii=False, default=str) if new_data else None,
    ))


def security_log(db: Session, action: str, client: ClientInfo | None, description: str | None = None,
                 user_id: int | None = None, niveau: str = "info") -> None:
    db.add(Log(
        user_id=user_id, action=action, description=description, niveau=niveau,
        ip_address=client.ip if client else None, user_agent=client.user_agent if client else None,
    ))
