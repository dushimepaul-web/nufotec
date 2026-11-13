from functools import lru_cache
from typing import Literal

from pydantic import model_validator
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=".env", env_file_encoding="utf-8", extra="ignore")

    app_name: str = "Nufotec API"
    app_env: Literal["local", "test", "staging", "production"] = "local"
    debug: bool = False
    log_level: str = "INFO"

    database_url: str = "mysql+pymysql://root:@127.0.0.1:3306/nufotec_db?charset=utf8mb4"

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

    # Téléphone : pays utilisé quand le numéro est saisi sans indicatif
    default_phone_region: str = "BI"

    # Fichiers : on écrit dans le dossier attachments du site PHP pour partager les mêmes fichiers.
    uploads_dir: str = r"C:\wamp64\www\nufotec-backend\attachments"
    media_base_url: str = "http://localhost/nufotec-backend/attachments"
    public_api_url: str = "http://127.0.0.1:8000"

    # Emails : "console" garde les emails en mémoire et les écrit dans le journal (dev/tests),
    # "smtp" les envoie. Sans SMTP_* ici, on lit smtp_host/smtp_port/smtp_user/smtp_pass dans la table configurations.
    mail_mode: Literal["console", "smtp"] = "console"
    mail_from: str = "info@nufotec.com"
    mail_from_name: str = "Nufotec"
    smtp_host: str | None = None
    smtp_port: int | None = None
    smtp_user: str | None = None
    smtp_password: str | None = None

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
        if problems:
            raise ValueError("Configuration invalide : " + " ; ".join(problems))
        return self


@lru_cache
def get_settings() -> Settings:
    return Settings()
