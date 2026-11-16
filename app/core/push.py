"""Notifications push.

Chaque appareil enregistre son jeton (POST /auth/push-devices), lié à la session courante : un appareil
déconnecté (session fermée, expirée, compte désactivé) ne reçoit plus rien.
Envoi : service Expo Push (jetons ExponentPushToken[...]). Les jetons FCM sont enregistrés mais leur
envoi direct demandera la clé de service Firebase. En mode console, rien n'est envoyé : les messages
sont gardés dans `sent` (dev/tests)."""

import logging
from dataclasses import dataclass, field

import httpx
from sqlalchemy import select

from app.core.config import get_settings
from app.core.database import background_session
from app.core.helpers import now

log = logging.getLogger("nufotec.push")
EXPO_URL = "https://exp.host/--/api/v2/push/send"


@dataclass
class PushMessage:
    token: str
    title: str
    body: str
    data: dict = field(default_factory=dict)


sent: list[PushMessage] = []


def active_devices(db, user_ids: list[int]):
    from app.models import PushDevice, User, UserSession

    return list(db.scalars(
        select(PushDevice)
        .join(UserSession, UserSession.id == PushDevice.session_id)
        .join(User, User.id == PushDevice.user_id)
        .where(PushDevice.user_id.in_(user_ids), PushDevice.revoked_at.is_(None),
               UserSession.is_active.is_(True), UserSession.expiry_time > now(),
               User.is_active.is_(True), User.deleted_at.is_(None))
    ))


def _send_expo(messages: list[PushMessage]) -> list[dict]:
    s = get_settings()
    headers = {"Accept": "application/json", "Content-Type": "application/json"}
    if s.expo_access_token:
        headers["Authorization"] = f"Bearer {s.expo_access_token}"
    tickets: list[dict] = []
    with httpx.Client(timeout=15) as http:
        for i in range(0, len(messages), 100):  # Expo accepte 100 messages par appel
            batch = messages[i:i + 100]
            payload = [{"to": m.token, "title": m.title, "body": m.body, "data": m.data, "sound": "default"}
                       for m in batch]
            response = http.post(EXPO_URL, json=payload, headers=headers)
            response.raise_for_status()
            tickets.extend(response.json().get("data", []))
    return tickets


def send_to_users(user_ids: list[int], title: str, body: str, data: dict | None = None) -> int:
    """À lancer en tâche de fond. Renvoie le nombre de messages acceptés."""
    db = background_session()
    try:
        devices = [d for d in active_devices(db, user_ids) if d.provider == "expo"]
        messages = [PushMessage(d.token, title, body, data or {}) for d in devices]
        if not messages:
            return 0
        if get_settings().push_mode == "console":
            sent.extend(messages)
            log.info("Push (console) à %s appareil(s) : %s", len(messages), title)
            return len(messages)
        try:
            tickets = _send_expo(messages)
        except Exception:
            log.exception("Échec d'envoi des notifications push")
            return 0
        accepted = 0
        for device, ticket in zip(devices, tickets, strict=False):
            if ticket.get("status") == "ok":
                accepted += 1
            elif (ticket.get("details") or {}).get("error") == "DeviceNotRegistered":
                device.revoked_at = now()  # application désinstallée : on n'enverra plus
            else:
                log.warning("Push refusé pour l'appareil %s : %s", device.id, ticket.get("message"))
        db.commit()
        return accepted
    finally:
        db.close()
