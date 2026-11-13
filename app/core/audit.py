"""Traces : `user_activities` (visible dans le tableau de bord admin PHP) et `logs` (événements de sécurité)."""

import json

from sqlalchemy.orm import Session

from app.core.client import ClientInfo
from app.models import Log, UserActivity


def activity(db: Session, user_id: int | None, action: str, client: ClientInfo | None,
             description: str | None = None, new_data: dict | None = None, module: str = "auth") -> None:
    db.add(UserActivity(
        user_id=user_id, action=action, module=module, item_id=user_id, description=description,
        ip_address=client.ip if client else None, user_agent=client.user_agent if client else None,
        new_data=json.dumps(new_data, ensure_ascii=False, default=str) if new_data else None,
    ))


def security_log(db: Session, action: str, client: ClientInfo | None, description: str | None = None,
                 user_id: int | None = None, niveau: str = "info") -> None:
    db.add(Log(
        user_id=user_id, action=action, description=description, niveau=niveau,
        ip_address=client.ip if client else None, user_agent=client.user_agent if client else None,
    ))
