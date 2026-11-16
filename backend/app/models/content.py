"""Contenu du site : pages et sections (sql/013), actualités, FAQ, témoignages, partenaires,
chiffres clés, réseaux sociaux, newsletter, contact, visites (tables existantes, enrichies par sql/013)."""

from datetime import date, datetime, time

from sqlalchemy import Boolean, Date, DateTime, Enum, ForeignKey, Integer, SmallInteger, String, Text, Time
from sqlalchemy.orm import Mapped, mapped_column

from app.core.database import Base


class Page(Base):
    __tablename__ = "pages"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    titre: Mapped[str] = mapped_column(String(255))
    slug: Mapped[str] = mapped_column(String(150), unique=True)
    parent_id: Mapped[int | None] = mapped_column(ForeignKey("pages.id"))
    menu_ordre: Mapped[int] = mapped_column(SmallInteger, default=0)
    afficher_menu: Mapped[bool] = mapped_column(Boolean, default=True)
    est_publiee: Mapped[bool] = mapped_column(Boolean, default=True)
    meta_description: Mapped[str | None] = mapped_column(String(500))
    meta_keywords: Mapped[str | None] = mapped_column(String(500))
    contenu: Mapped[str | None] = mapped_column(Text)
    template: Mapped[str | None] = mapped_column(String(60))
    icone: Mapped[str | None] = mapped_column(String(60))
    legacy_id: Mapped[int | None] = mapped_column(Integer, unique=True)
    created_at: Mapped[datetime] = mapped_column(DateTime)
    updated_at: Mapped[datetime] = mapped_column(DateTime)
    deleted_at: Mapped[datetime | None] = mapped_column(DateTime)


class PageSection(Base):
    __tablename__ = "page_sections"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    page_id: Mapped[int] = mapped_column(ForeignKey("pages.id"))
    type: Mapped[str] = mapped_column(String(30), default="texte")
    titre: Mapped[str | None] = mapped_column(String(500))
    sous_titre: Mapped[str | None] = mapped_column(String(1000))
    contenu: Mapped[str | None] = mapped_column(Text)
    image_url: Mapped[str | None] = mapped_column(String(500))
    image_droite: Mapped[bool] = mapped_column(Boolean, default=False)
    bouton_texte: Mapped[str | None] = mapped_column(String(150))
    bouton_lien: Mapped[str | None] = mapped_column(String(500))
    classe: Mapped[str | None] = mapped_column(String(100))
    options_json: Mapped[str | None] = mapped_column(Text)
    ordre: Mapped[int] = mapped_column(SmallInteger, default=0)
    est_publiee: Mapped[bool] = mapped_column(Boolean, default=True)
    legacy_id: Mapped[int | None] = mapped_column(Integer, unique=True)
    created_at: Mapped[datetime] = mapped_column(DateTime)
    updated_at: Mapped[datetime] = mapped_column(DateTime)


class Article(Base):
    __tablename__ = "actualites_blog"

    id: Mapped[int] = mapped_column("id_actualite", Integer, primary_key=True)
    titre: Mapped[str] = mapped_column(String(255))
    slug: Mapped[str | None] = mapped_column(String(255))
    resume: Mapped[str | None] = mapped_column(Text)
    contenu: Mapped[str | None] = mapped_column(Text)
    image_principale: Mapped[str | None] = mapped_column(String(255))
    auteur: Mapped[str | None] = mapped_column(String(150))
    date_publication: Mapped[datetime | None] = mapped_column(DateTime)
    categorie: Mapped[str | None] = mapped_column(String(100))
    tags: Mapped[str | None] = mapped_column(String(255))
    est_en_avant: Mapped[bool] = mapped_column(Boolean, default=False)
    est_publie: Mapped[bool] = mapped_column(Boolean, default=False)
    vues: Mapped[int | None] = mapped_column(Integer, default=0)
    created_at: Mapped[datetime | None] = mapped_column(DateTime)
    updated_at: Mapped[datetime | None] = mapped_column(DateTime)
    deleted_at: Mapped[datetime | None] = mapped_column(DateTime)


class Faq(Base):
    __tablename__ = "faq"

    id: Mapped[int] = mapped_column("id_faq", Integer, primary_key=True)
    question: Mapped[str] = mapped_column(Text)
    reponse: Mapped[str] = mapped_column(Text)
    categorie: Mapped[str | None] = mapped_column(String(100))
    ordre: Mapped[int | None] = mapped_column(Integer, default=0)
    est_publiee: Mapped[bool] = mapped_column(Boolean, default=True)


class Testimonial(Base):
    __tablename__ = "temoignages"

    id: Mapped[int] = mapped_column("id_temoignage", Integer, primary_key=True)
    titre: Mapped[str | None] = mapped_column(String(255))
    video_url: Mapped[str | None] = mapped_column(String(500))
    miniature: Mapped[str | None] = mapped_column(String(500))
    audio_duration: Mapped[str | None] = mapped_column(String(20))
    est_approuve: Mapped[bool] = mapped_column(Boolean, default=False)


class Partner(Base):
    __tablename__ = "partenaires"

    id: Mapped[int] = mapped_column("id_partenaire", Integer, primary_key=True)
    type_partenaire: Mapped[str | None] = mapped_column(String(50))
    nom: Mapped[str] = mapped_column(String(255))
    logo_url: Mapped[str | None] = mapped_column(String(500))
    description: Mapped[str | None] = mapped_column(Text)
    pays: Mapped[str | None] = mapped_column(String(100))
    site_web: Mapped[str | None] = mapped_column(String(255))
    niveau_partenariat: Mapped[str | None] = mapped_column(String(50))
    date_debut: Mapped[date | None] = mapped_column(Date)
    est_actif: Mapped[bool] = mapped_column(Boolean, default=True)
    created_at: Mapped[datetime | None] = mapped_column(DateTime)
    updated_at: Mapped[datetime | None] = mapped_column(DateTime)
    deleted_at: Mapped[datetime | None] = mapped_column(DateTime)


class KeyFigure(Base):
    __tablename__ = "chiffres_cles"

    id: Mapped[int] = mapped_column("id_chiffre", Integer, primary_key=True)
    etiquette: Mapped[str] = mapped_column(String(255))
    valeur: Mapped[str] = mapped_column(String(100))
    unite: Mapped[str | None] = mapped_column(String(50))
    description: Mapped[str | None] = mapped_column(Text)
    icone: Mapped[str | None] = mapped_column(String(100))
    ordre: Mapped[int | None] = mapped_column(Integer, default=0)
    annee_vision: Mapped[int | None] = mapped_column(Integer)


class SocialLink(Base):
    __tablename__ = "social_links"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    platform: Mapped[str] = mapped_column(String(50))
    label: Mapped[str | None] = mapped_column(String(100))
    url: Mapped[str] = mapped_column(String(500))
    icon_class: Mapped[str] = mapped_column(String(100), default="bi")  # icônes Bootstrap (bi-…) du site
    icon_name: Mapped[str | None] = mapped_column(String(100))
    display_order: Mapped[int | None] = mapped_column(Integer, default=0)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    target_blank: Mapped[bool] = mapped_column(Boolean, default=True)
    followers: Mapped[int | None] = mapped_column(Integer)
    created_at: Mapped[datetime | None] = mapped_column(DateTime)
    updated_at: Mapped[datetime | None] = mapped_column(DateTime)


class NewsletterSubscriber(Base):
    __tablename__ = "newsletter"

    id: Mapped[int] = mapped_column("id_newsletter", Integer, primary_key=True)
    email: Mapped[str | None] = mapped_column(String(255), unique=True)
    telephone: Mapped[str | None] = mapped_column(String(50))
    date_inscription: Mapped[datetime | None] = mapped_column(DateTime)
    statut: Mapped[str] = mapped_column(Enum("a_reconfirmer", "en_attente", "confirme", "desinscrit", native_enum=False),
                                        default="en_attente")
    jeton: Mapped[str | None] = mapped_column(String(64), unique=True)
    langue: Mapped[str | None] = mapped_column(String(5))
    consent_ip: Mapped[str | None] = mapped_column(String(45))
    confirme_at: Mapped[datetime | None] = mapped_column(DateTime)
    desinscrit_at: Mapped[datetime | None] = mapped_column(DateTime)


class ContactMessage(Base):
    __tablename__ = "contact_us"

    id: Mapped[int] = mapped_column("IdContact", Integer, primary_key=True)
    nom: Mapped[str] = mapped_column("FullName", String(255))
    email: Mapped[str] = mapped_column("Email", String(255))
    sujet: Mapped[str | None] = mapped_column("Subject", String(255))
    message: Mapped[str] = mapped_column("Message", Text)
    telephone: Mapped[str | None] = mapped_column("PhoneNumber", String(50))
    created_at: Mapped[datetime | None] = mapped_column("Date_creation", DateTime)
    lieu: Mapped[str | None] = mapped_column("Location", String(255))
    ip_address: Mapped[str | None] = mapped_column(String(45))
    user_agent: Mapped[str | None] = mapped_column(Text)
    lu: Mapped[bool] = mapped_column("is_readed", Boolean, default=False)
    reponse: Mapped[str | None] = mapped_column(Text)
    repondu_at: Mapped[datetime | None] = mapped_column(DateTime)
    repondu_par: Mapped[int | None] = mapped_column(Integer)


class VisitorLog(Base):
    __tablename__ = "visitors_logs"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    visitor_hash: Mapped[str | None] = mapped_column(String(32))
    ip_address: Mapped[str] = mapped_column(String(45), default="")
    user_agent: Mapped[str | None] = mapped_column(Text)
    device: Mapped[str | None] = mapped_column(String(50))
    page: Mapped[str | None] = mapped_column(String(255))
    referer: Mapped[str | None] = mapped_column(String(500))
    visit_date: Mapped[date | None] = mapped_column(Date)
    visit_time: Mapped[time | None] = mapped_column(Time)
    country: Mapped[str | None] = mapped_column(String(100))
    city: Mapped[str | None] = mapped_column(String(100))
    created_at: Mapped[datetime | None] = mapped_column(DateTime)
