"""Déplace les clés d'IA de la table `parametres` (en clair) vers `ai_providers` (chiffrées),
règle le chatbot (Groq principal, OpenAI en secours) puis retire les clés de `parametres`.

    .venv\\Scripts\\python -m tools.migrate_chatbot_keys

Idempotent : relancer ne crée pas de doublon. Les clés ne sont jamais affichées."""

from sqlalchemy import delete, select

from app.core.crypto import decrypt, encrypt, hint
from app.core.database import SessionLocal
from app.core.helpers import now
from app.models import AiProvider, Parametre
from app.modules.chatbot import settings
from app.modules.chatbot.providers import DEFAULT_URLS

SECRET_KEYS = ("chatbot_api_key", "chatbot_groq_api_key")


def main() -> None:
    db = SessionLocal()
    try:
        conf = {c.cle: (c.valeur or "").strip() for c in db.scalars(select(Parametre).where(
            Parametre.cle.like("chatbot_%")))}
        plan = [
            ("Groq", "groq", conf.get("chatbot_groq_api_key"), conf.get("chatbot_groq_base_url") or DEFAULT_URLS["groq"],
             conf.get("chatbot_groq_chat_model") or "llama-3.3-70b-versatile"),
            ("OpenAI", "openai", conf.get("chatbot_api_key"), DEFAULT_URLS["openai"],
             conf.get("chatbot_chat_model") or "gpt-4o-mini"),
        ]
        created: dict[str, tuple[AiProvider, str]] = {}
        for name, kind, key, url, model in plan:
            if not key:
                print(f"- {name} : aucune clé dans parametres, ignoré")
                continue
            provider = db.scalar(select(AiProvider).where(AiProvider.name == name))
            if provider is None:
                provider = AiProvider(name=name, kind=kind, base_url=url.rstrip("/"), is_active=True,
                                      created_at=now(), updated_at=now())
                db.add(provider)
            provider.api_key_encrypted, provider.api_key_hint, provider.updated_at = encrypt(key), hint(key), now()
            db.flush()
            assert decrypt(provider.api_key_encrypted) == key  # vérifie la copie chiffrée avant de supprimer
            created[name] = (provider, model)
            print(f"- {name} : clé chiffrée enregistrée ({provider.api_key_hint}), modèle {model}")

        values = settings.get_all(db)
        updates = {}
        primary = created.get("Groq") or created.get("OpenAI")
        if primary and not values["chat_provider_id"]:
            updates.update(chat_provider_id=str(primary[0].id), chat_model=primary[1])
        if "Groq" in created and "OpenAI" in created and not values["fallback_provider_id"]:
            updates.update(fallback_provider_id=str(created["OpenAI"][0].id), fallback_model=created["OpenAI"][1])
        for old, new in (("chatbot_site_name", "assistant_name"), ("chatbot_greeting", "greeting"),
                         ("chatbot_handoff_email", "handoff_email")):
            if conf.get(old) and conf[old] not in ("Assistant",) and values.get(new) == settings.DEFAULTS[new]:
                updates[new] = conf[old]
        settings.set_many(db, updates)

        removed = db.execute(delete(Parametre).where(Parametre.cle.in_(SECRET_KEYS))).rowcount
        db.commit()
        print(f"- Réglages du chatbot : {', '.join(sorted(updates)) or 'déjà faits'}")
        print(f"- {removed} clé(s) en clair retirée(s) de la table parametres")
        print("Pensez à régénérer ces clés chez Groq et OpenAI (elles ont été lisibles en clair), puis à les "
              "remplacer dans l'administration du chatbot.")
    finally:
        db.close()


if __name__ == "__main__":
    main()
