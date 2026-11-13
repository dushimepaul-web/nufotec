"""Tables users, roles, user_sessions et pays de nufotec_db (schéma existant, non modifié)."""

from datetime import date, datetime
from decimal import Decimal

from sqlalchemy import Boolean, Date, DateTime, Enum, ForeignKey, Integer, Numeric, String, Text, func
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base

USER_TYPES = ("admin", "medecin", "patient", "entreprise", "investisseur", "partenaire", "broker")


class Role(Base):
    __tablename__ = "roles"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    nom: Mapped[str] = mapped_column(String(50))
    slug: Mapped[str] = mapped_column(String(50))
    description: Mapped[str | None] = mapped_column(Text)
    niveau: Mapped[int | None] = mapped_column(Integer)
    created_at: Mapped[datetime | None] = mapped_column(DateTime, server_default=func.now())


class Pays(Base):
    __tablename__ = "pays"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    pays: Mapped[str | None] = mapped_column(String(255))
    iso2: Mapped[str | None] = mapped_column("ISO_3166_1_2_Letter_Code", String(255))
    indicatif: Mapped[str | None] = mapped_column("ITU_T_Telephone_Code", String(255))


class User(Base):
    __tablename__ = "users"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    uuid: Mapped[str] = mapped_column(String(36), unique=True)
    email: Mapped[str] = mapped_column(String(100), unique=True)
    password: Mapped[str] = mapped_column(String(255))
    nom: Mapped[str] = mapped_column(String(50))
    prenom: Mapped[str] = mapped_column(String(50))
    telephone: Mapped[str | None] = mapped_column(String(20))
    pays_id: Mapped[int | None] = mapped_column(ForeignKey("pays.id"))
    date_naissance: Mapped[date | None] = mapped_column(Date)
    genre: Mapped[str | None] = mapped_column(Enum("M", "F", "Autre", native_enum=False))
    photo: Mapped[str | None] = mapped_column(String(255), default="default-avatar.png")
    role_id: Mapped[int] = mapped_column(ForeignKey("roles.id"))
    type_utilisateur: Mapped[str | None] = mapped_column(Enum(*USER_TYPES, native_enum=False))
    nom_entreprise: Mapped[str | None] = mapped_column(String(100))
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    email_verified_at: Mapped[datetime | None] = mapped_column(DateTime)
    email_verification_token: Mapped[str | None] = mapped_column(String(255))
    two_factor_secret: Mapped[str | None] = mapped_column(String(255))
    two_factor_enabled: Mapped[bool] = mapped_column(Boolean, default=False)
    last_login_at: Mapped[datetime | None] = mapped_column(DateTime)
    last_login_ip: Mapped[str | None] = mapped_column(String(45))
    created_at: Mapped[datetime | None] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime | None] = mapped_column(DateTime, server_default=func.now(), onupdate=func.now())
    deleted_at: Mapped[datetime | None] = mapped_column(DateTime)
    secteur_activite: Mapped[str | None] = mapped_column(String(100))
    numero_registre_commerce: Mapped[str | None] = mapped_column(String(50))
    interet_investissement: Mapped[Decimal | None] = mapped_column(Numeric(15, 2))
    est_verifie: Mapped[bool] = mapped_column(Boolean, default=False)

    role: Mapped[Role] = relationship(lazy="joined")
    pays: Mapped[Pays | None] = relationship(lazy="joined")


class UserSession(Base):
    """Une session = un jeton de rafraîchissement (session_id = jti du jeton)."""

    __tablename__ = "user_sessions"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"))
    session_id: Mapped[str] = mapped_column(String(255), index=True)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    user_agent: Mapped[str | None] = mapped_column(Text)
    device_type: Mapped[str | None] = mapped_column(
        Enum("desktop", "mobile", "tablet", "bot", "unknown", native_enum=False), default="unknown"
    )
    platform: Mapped[str | None] = mapped_column(String(100))
    browser: Mapped[str | None] = mapped_column(String(100))
    login_time: Mapped[datetime] = mapped_column(DateTime)
    last_activity: Mapped[datetime] = mapped_column(DateTime)
    expiry_time: Mapped[datetime] = mapped_column(DateTime)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    logout_time: Mapped[datetime | None] = mapped_column(DateTime)
    logout_reason: Mapped[str | None] = mapped_column(
        Enum("manual", "timeout", "expired", "force", native_enum=False)
    )
    created_at: Mapped[datetime | None] = mapped_column(DateTime, server_default=func.now())
