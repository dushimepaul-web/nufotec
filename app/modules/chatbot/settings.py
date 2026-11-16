"""Réglages du chatbot (table chatbot_settings), modifiables par l'admin."""

from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core.helpers import now
from app.models import ChatbotSetting

DEFAULT_PROMPT = (
    "Tu es l'assistant virtuel officiel de Nufotec (Burundi). Réponds uniquement à partir du CONTEXTE fourni. "
    "Si le contexte ne contient pas la réponse, dis-le simplement et propose de parler à un conseiller. "
    "Sois clair, concis et chaleureux. Réponds dans la langue de l'utilisateur. "
    "Cite tes sources avec leur numéro entre crochets, par exemple [1]."
)
DEFAULTS: dict[str, str] = {
    "assistant_name": "Assistant Nufotec",
    "greeting": "Bonjour ! Je suis l'assistant Nufotec. Comment puis-je vous aider ?",
    "system_prompt": DEFAULT_PROMPT,
    "chat_provider_id": "",
    "chat_model": "",
    "fallback_provider_id": "",
    "fallback_model": "",
    "temperature": "0.3",
    "max_tokens": "700",
    "top_k": "5",
    "min_score": "0.35",
    "history_messages": "8",
    "handoff_email": "support@nufotec.bi",
    "emergency_message": ("Si vous ou quelqu'un est en danger immédiat (difficulté à respirer, douleur forte à la "
                          "poitrine, perte de connaissance, saignement important), appelez immédiatement les "
                          "services d'urgence ou rendez-vous à l'hôpital le plus proche. Je ne peux pas gérer "
                          "les urgences."),
    "last_nightly_sync": "",
}
NUMERIC = {"temperature": (0.0, 1.5), "max_tokens": (50, 4000), "top_k": (1, 20), "min_score": (0.0, 1.0),
           "history_messages": (0, 30)}


def get_all(db: Session) -> dict[str, str]:
    values = dict(DEFAULTS)
    values.update({row.cle: row.valeur or "" for row in db.scalars(select(ChatbotSetting))})
    return values


def get(db: Session, key: str) -> str:
    row = db.get(ChatbotSetting, key)
    return row.valeur if row is not None and row.valeur is not None else DEFAULTS.get(key, "")


def set_many(db: Session, values: dict[str, str]) -> None:
    for key, value in values.items():
        row = db.get(ChatbotSetting, key)
        if row is None:
            db.add(ChatbotSetting(cle=key, valeur=value, updated_at=now()))
        else:
            row.valeur, row.updated_at = value, now()
