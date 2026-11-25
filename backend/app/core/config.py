from functools import lru_cache
from pathlib import Path
from typing import Literal

from pydantic import model_validator
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=Path(__file__).resolve().parents[2] / ".env", env_file_encoding="utf-8", extra="ignore")

    app_name: str = "Nufotec API"
    app_env: Literal["local", "test", "staging", "production"] = "local"
    debug: bool = False
    log_level: str = "INFO"

    database_url: str = "mysql+pymysql://root:@127.0.0.1:3306/nufotec_db?charset=utf8mb4"
    # Connexions à MySQL : une base lente ou arrêtée ne doit jamais bloquer l'API indéfiniment.
    db_pool_size: int = 10            # connexions gardées ouvertes
    db_max_overflow: int = 20         # connexions supplémentaires en cas de pic
    db_pool_timeout: int = 10         # secondes d'attente d'une connexion libre, puis 503
    db_connect_timeout: int = 5       # secondes pour joindre MySQL
    db_read_timeout: int = 60         # secondes au plus pour une requête SQL

    # Jetons
    jwt_secret: str
    jwt_algorithm: str = "HS256"
    access_token_minutes: int = 30
    refresh_token_days: int = 30
    # Délai pendant lequel deux rafraîchissements simultanés (app mobile) ne sont pas pris pour un vol.
    refresh_reuse_grace_seconds: int = 15

    # Anti force brute
    login_max_failures_per_account: int = 5
    login_max_failures_per_ip: int = 30  # plus large : les opérateurs mobiles partagent les IP
    login_lockout_minutes: int = 15

    # Codes envoyés par email
    reset_code_minutes: int = 15
    reset_code_max_attempts: int = 5
    email_codes_per_hour: int = 3

    # Réseau
    cors_origins: str = "*"
    trusted_proxies: str = "127.0.0.1,::1"  # IP des proxys (nginx, Cloudflare…) autorisés à fixer X-Forwarded-For

    # Fuseau des dates renvoyées (les dates de la base sont en heure locale du serveur)
    app_timezone: str = "Africa/Bujumbura"

    # Téléphone : pays utilisé quand le numéro est saisi sans indicatif
    default_phone_region: str = "BI"

    # Fichiers : on écrit dans le dossier attachments du site PHP pour partager les mêmes fichiers.
    uploads_dir: str = r"C:\wamp64\www\nufotec\backend\attachments"
    media_base_url: str = "http://localhost/nufotec/backend/attachments"
    public_api_url: str = "http://127.0.0.1:8000"

    # Emails : "console" garde les emails en mémoire et les écrit dans le journal (dev/tests),
    # "smtp" les envoie. Sans SMTP_* ici, on lit smtp_host/smtp_port/smtp_user/smtp_pass dans la table parametres.
    mail_mode: Literal["console", "smtp"] = "console"
    outbox_worker: bool = True  # tâche de fond qui renvoie les emails en échec (désactivée dans les tests)
    mail_from: str = "info@nufotec.com"
    mail_from_name: str = "Nufotec"
    smtp_host: str | None = None
    smtp_port: int | None = None
    smtp_user: str | None = None
    smtp_password: str | None = None

    # Notifications push : "console" (dev/tests, rien n'est envoyé) ou "expo" (service Expo Push)
    push_mode: Literal["console", "expo"] = "console"
    expo_access_token: str | None = None  # facultatif : « Enhanced push security » d'Expo

    # Fichiers privés (documents médicaux, preuves de paiement) : HORS du dossier web public.
    private_dir: str = r"C:\wamp64\nufotec-private"
    file_link_minutes: int = 5  # durée de validité des liens de téléchargement signés

    # Consultations
    jitsi_base_url: str = "https://meet.jit.si"
    consultation_tva: float = 0.0          # TVA appliquée aux consultations (en %)
    booking_min_lead_minutes: int = 60     # réserver au moins 1 h à l'avance
    booking_max_days: int = 60             # et au plus 60 jours à l'avance
    cancel_min_hours: int = 2              # annulation par le patient jusqu'à 2 h avant
    join_before_minutes: int = 10          # le lien vidéo s'ouvre 10 min avant
    join_after_minutes: int = 30           # et reste valable 30 min après la fin prévue
    reminder_minutes: int = 60             # rappel 1 h avant
    burundi_pays_id: int = 194             # pays « Burundi » dans la table pays (tarif résident en BIF)

    # Chatbot / IA
    ai_encryption_key: str | None = None   # Fernet : chiffre les clés d'API des fournisseurs
    embeddings_backend: Literal["fastembed", "hash"] = "fastembed"  # "hash" : tests, sans modèle
    embeddings_model: str = "sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2"
    embeddings_cache_dir: str = r"C:\wamp64\nufotec-models"
    chatbot_messages_per_minute: int = 10   # par conversation
    chatbot_conversations_per_hour: int = 20  # par adresse IP
    ai_timeout_seconds: int = 60

    # Protections globales
    rate_limit_ip_per_minute: int = 120     # requêtes par minute et par adresse IP (visiteur sans compte)
    rate_limit_user_per_minute: int = 300   # requêtes par minute et par compte connecté
    max_request_mb: int = 60                # taille maximale d'une requête (les envois de médias passent par morceaux)
    deleted_accounts_purge_days: int = 30   # comptes supprimés par l'admin : anonymisables après ce délai (action manuelle)

    # Idempotency-Key : durée de conservation des réponses
    idempotency_hours: int = 24

    @property
    def is_production(self) -> bool:
        return self.app_env == "production"

    @property
    def cors_origin_list(self) -> list[str]:
        return [o.strip() for o in self.cors_origins.split(",") if o.strip()]

    @property
    def trusted_proxy_list(self) -> set[str]:
        return {p.strip() for p in self.trusted_proxies.split(",") if p.strip()}

    @model_validator(mode="after")
    def _check_production(self) -> "Settings":
        problems = []
        if len(self.jwt_secret) < 32 or self.jwt_secret == "change-me":
            problems.append("JWT_SECRET doit faire au moins 32 caractères aléatoires")
        if self.is_production:
            if self.debug:
                problems.append("DEBUG doit être false en production")
            if "*" in self.cors_origin_list:
                problems.append("CORS_ORIGINS ne peut pas être * en production")
            if self.mail_mode != "smtp":
                problems.append("MAIL_MODE doit être smtp en production")
            if "://root:@" in self.database_url or "://root@" in self.database_url:
                problems.append("DATABASE_URL : utilisez un compte MySQL dédié avec mot de passe, pas root sans mot de passe")
        if problems:
            raise ValueError("Configuration invalide : " + " ; ".join(problems))
        return self


@lru_cache
def get_settings() -> Settings:
    return Settings()
