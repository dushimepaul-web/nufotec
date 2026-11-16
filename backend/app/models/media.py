"""Médiathèque : médias et interactions (tables existantes), collections, progression, envois (sql/012).
Les catégories sont les types : audio, video, image, fichier (document/autre), link."""

from datetime import date, datetime

from sqlalchemy import BigInteger, Boolean, Date, DateTime, Enum, ForeignKey, Integer, SmallInteger, String, Text
from sqlalchemy.orm import Mapped, mapped_column

from app.core.database import Base

MEDIA_TYPES = ("image", "video", "audio", "document", "link", "autre")
# Catégorie publique → types en base
CATEGORIES = {"audio": ("audio",), "video": ("video",), "image": ("image",),
              "fichier": ("document", "autre"), "link": ("link",)}
CATEGORY_OF = {t: c for c, types in CATEGORIES.items() for t in types}


class Media(Base):
    __tablename__ = "galerie_medias"

    id: Mapped[int] = mapped_column("id_media", Integer, primary_key=True)
    uuid: Mapped[str | None] = mapped_column(String(36), unique=True)
    titre: Mapped[str | None] = mapped_column(String(255))
    slug: Mapped[str | None] = mapped_column(String(255), unique=True)
    type: Mapped[str] = mapped_column(Enum(*MEDIA_TYPES, native_enum=False))
    fichier: Mapped[str | None] = mapped_column(String(500))
    lien: Mapped[str | None] = mapped_column(String(500))
    miniature: Mapped[str | None] = mapped_column(String(500))
    description: Mapped[str | None] = mapped_column(Text)
    categorie: Mapped[str | None] = mapped_column(String(100))  # texte du PHP, tenu à jour
    date_media: Mapped[date | None] = mapped_column(Date)
    credits: Mapped[str | None] = mapped_column(String(255))
    taille: Mapped[int | None] = mapped_column(Integer)
    mime_type: Mapped[str | None] = mapped_column(String(100))
    duree: Mapped[int | None] = mapped_column(Integer)
    bitrate: Mapped[int | None] = mapped_column(Integer)
    sample_rate: Mapped[int | None] = mapped_column(Integer)
    channels: Mapped[int | None] = mapped_column(SmallInteger)
    waveform_data: Mapped[str | None] = mapped_column(Text)
    version_legere: Mapped[str | None] = mapped_column(String(500))
    traitement: Mapped[str] = mapped_column(Enum("aucun", "en_attente", "en_cours", "pret", "erreur", native_enum=False),
                                            default="aucun")
    traitement_erreur: Mapped[str | None] = mapped_column(String(500))
    est_actif: Mapped[bool] = mapped_column(Boolean, default=True)
    is_for_whatsapp: Mapped[bool] = mapped_column(Boolean, default=False)
    is_for_website: Mapped[bool] = mapped_column(Boolean, default=False)
    telechargements: Mapped[int] = mapped_column(Integer, default=0)
    created_at: Mapped[datetime | None] = mapped_column(DateTime)
    updated_at: Mapped[datetime | None] = mapped_column(DateTime)



class MediaView(Base):
    __tablename__ = "media_views"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    id_media: Mapped[int] = mapped_column(Integer)
    user_id: Mapped[int | None] = mapped_column(Integer)
    session_id: Mapped[str | None] = mapped_column(String(255))
    ip_address: Mapped[str | None] = mapped_column(String(45))
    user_agent: Mapped[str | None] = mapped_column(Text)
    viewed_at: Mapped[datetime] = mapped_column(DateTime)


class MediaPlay(Base):
    __tablename__ = "media_plays"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    id_media: Mapped[int] = mapped_column(Integer)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    session_id: Mapped[str | None] = mapped_column(String(255))
    duration_played: Mapped[int | None] = mapped_column(Integer)
    played_at: Mapped[datetime] = mapped_column(DateTime)


class MediaLike(Base):
    __tablename__ = "media_likes"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    id_media: Mapped[int] = mapped_column(Integer)
    user_id: Mapped[int | None] = mapped_column(Integer)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    action: Mapped[str] = mapped_column(Enum("like", "dislike", native_enum=False))
    created_at: Mapped[datetime] = mapped_column(DateTime)


class MediaRating(Base):
    __tablename__ = "media_ratings"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    id_media: Mapped[int] = mapped_column(Integer)
    user_id: Mapped[int | None] = mapped_column(Integer)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    rating: Mapped[int] = mapped_column(SmallInteger)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class MediaFavorite(Base):
    __tablename__ = "media_favorites"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    id_media: Mapped[int] = mapped_column(Integer)
    user_id: Mapped[int] = mapped_column(Integer)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class MediaComment(Base):
    __tablename__ = "media_comments"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    id_media: Mapped[int] = mapped_column(Integer)
    user_id: Mapped[int | None] = mapped_column(Integer)
    author_name: Mapped[str | None] = mapped_column(String(255))
    author_email: Mapped[str | None] = mapped_column(String(255))
    comment: Mapped[str] = mapped_column(Text)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    is_approved: Mapped[int] = mapped_column(SmallInteger, default=0)  # 0 en attente, 1 publié, -1 refusé
    moderated_by: Mapped[int | None] = mapped_column(Integer)
    moderated_at: Mapped[datetime | None] = mapped_column(DateTime)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class MediaDownload(Base):
    __tablename__ = "media_downloads"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    id_media: Mapped[int] = mapped_column(Integer)
    user_id: Mapped[int | None] = mapped_column(Integer)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    user_agent: Mapped[str | None] = mapped_column(Text)
    downloaded_at: Mapped[datetime] = mapped_column(DateTime)


class MediaProgress(Base):
    __tablename__ = "media_progress"

    user_id: Mapped[int] = mapped_column(Integer, primary_key=True)
    id_media: Mapped[int] = mapped_column(ForeignKey("galerie_medias.id_media"), primary_key=True)
    position_secondes: Mapped[int] = mapped_column(Integer, default=0)
    duree_secondes: Mapped[int | None] = mapped_column(Integer)
    termine: Mapped[bool] = mapped_column(Boolean, default=False)
    updated_at: Mapped[datetime] = mapped_column(DateTime)


class MediaCollection(Base):
    __tablename__ = "media_collections"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    uuid: Mapped[str] = mapped_column(String(36), unique=True)
    titre: Mapped[str] = mapped_column(String(255))
    slug: Mapped[str] = mapped_column(String(255), unique=True)
    description: Mapped[str | None] = mapped_column(Text)
    couverture_media_id: Mapped[int | None] = mapped_column(Integer)
    position: Mapped[int] = mapped_column(SmallInteger, default=0)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class MediaCollectionItem(Base):
    __tablename__ = "media_collection_items"

    collection_id: Mapped[int] = mapped_column(ForeignKey("media_collections.id"), primary_key=True)
    id_media: Mapped[int] = mapped_column(ForeignKey("galerie_medias.id_media"), primary_key=True)
    position: Mapped[int] = mapped_column(SmallInteger, default=0)


class MediaUpload(Base):
    __tablename__ = "media_uploads"

    id: Mapped[str] = mapped_column(String(32), primary_key=True)
    admin_id: Mapped[int] = mapped_column(Integer)
    nom_fichier: Mapped[str] = mapped_column(String(255))
    type: Mapped[str] = mapped_column(String(20))
    taille: Mapped[int] = mapped_column(BigInteger)
    taille_morceau: Mapped[int] = mapped_column(Integer)
    morceaux_recus: Mapped[str | None] = mapped_column(Text)
    statut: Mapped[str] = mapped_column(Enum("en_cours", "termine", "abandonne", native_enum=False), default="en_cours")
    created_at: Mapped[datetime] = mapped_column(DateTime)
    updated_at: Mapped[datetime] = mapped_column(DateTime)
