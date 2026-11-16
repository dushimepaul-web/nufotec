"""Tables médecins, horaires, consultations, paiements (existantes) et documents / historique / avis
(sql/005_consultations.sql)."""

from datetime import datetime, time
from decimal import Decimal

from sqlalchemy import (
    Boolean,
    Computed,
    DateTime,
    Enum,
    ForeignKey,
    Integer,
    Numeric,
    SmallInteger,
    String,
    Text,
    Time,
    func,
)
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base
from app.models.user import Pays, User

JOURS = ("lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi", "dimanche")
STATUTS = ("en_attente", "confirmee", "en_cours", "terminee", "annulee", "refusee", "absent")
PAIEMENT_STATUTS = ("en_attente", "paye", "echoue", "rembourse")
TYPES = ("video", "presentiel", "telephone")


class Medecin(Base):
    __tablename__ = "medecins"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    uuid: Mapped[str] = mapped_column(String(36), unique=True)
    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"))
    specialite: Mapped[str] = mapped_column(String(100))
    numero_licence: Mapped[str] = mapped_column(String(50))
    annees_experience: Mapped[int | None] = mapped_column(Integer, default=0)
    diplomes: Mapped[str | None] = mapped_column(Text)
    bio: Mapped[str | None] = mapped_column(Text)
    langues_parlees: Mapped[str | None] = mapped_column(String(255))
    whatsapp: Mapped[str | None] = mapped_column(String(30))
    honoraires_consultation: Mapped[Decimal | None] = mapped_column(Numeric(10, 2))
    prix_usd: Mapped[Decimal | None] = mapped_column(Numeric(10, 2))
    prix_bif: Mapped[Decimal | None] = mapped_column(Numeric(12, 2))
    currency: Mapped[str | None] = mapped_column(String(10), default="USD")
    # Colonnes texte du PHP (obligatoires en base) : tenues à jour pour l'affichage côté PHP.
    usd_eur_equivalent_bif: Mapped[str] = mapped_column("USD_EUR_Equivalent_en_BIF", String(50), default="")
    prix_pour_residant_burundi: Mapped[str] = mapped_column(String(50), default="")
    est_disponible: Mapped[bool] = mapped_column(Boolean, default=True)
    duree_consultation_minutes: Mapped[int] = mapped_column(SmallInteger, default=30)
    note_moyenne: Mapped[Decimal | None] = mapped_column(Numeric(3, 2), default=0)
    nombre_avis: Mapped[int | None] = mapped_column(Integer, default=0)
    created_at: Mapped[datetime | None] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime | None] = mapped_column(DateTime, server_default=func.now(), onupdate=func.now())

    user: Mapped[User] = relationship(lazy="joined")
    horaires: Mapped[list["MedecinHoraire"]] = relationship(
        back_populates="medecin", lazy="selectin", order_by="MedecinHoraire.id", cascade="all, delete-orphan")


class MedecinAbsence(Base):
    """Absence / congé : aucun créneau proposé pendant la période (sql/006)."""

    __tablename__ = "medecin_absences"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    medecin_id: Mapped[int] = mapped_column(ForeignKey("medecins.id"))
    debut: Mapped[datetime] = mapped_column(DateTime)
    fin: Mapped[datetime] = mapped_column(DateTime)
    motif: Mapped[str | None] = mapped_column(String(255))
    created_at: Mapped[datetime] = mapped_column(DateTime)


class MedecinHoraire(Base):
    __tablename__ = "medecin_horaires"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    medecin_id: Mapped[int] = mapped_column(ForeignKey("medecins.id"))
    jour_semaine: Mapped[str] = mapped_column(Enum(*JOURS, native_enum=False))
    heure_debut: Mapped[time] = mapped_column(Time)
    heure_fin: Mapped[time] = mapped_column(Time)
    est_actif: Mapped[bool] = mapped_column(Boolean, default=True)

    medecin: Mapped[Medecin] = relationship(back_populates="horaires")


class ModePaiement(Base):
    __tablename__ = "mode_payement"

    id: Mapped[int] = mapped_column("id_mode_payement", Integer, primary_key=True)
    description: Mapped[str] = mapped_column(String(200))
    etapepaiement: Mapped[str | None] = mapped_column(String(100))
    numero_compte: Mapped[str | None] = mapped_column(String(50))
    nom_compte: Mapped[str | None] = mapped_column(String(100))
    est_actif: Mapped[bool] = mapped_column(Boolean, default=True)


class Consultation(Base):
    __tablename__ = "consultations"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    patient_id: Mapped[int | None] = mapped_column(ForeignKey("users.id"))
    patient_name: Mapped[str | None] = mapped_column(String(100))
    whatsapp_number: Mapped[str | None] = mapped_column(String(20))
    medecin_id: Mapped[int] = mapped_column(ForeignKey("medecins.id"))
    numero_consultation: Mapped[str] = mapped_column(String(50), unique=True)
    type: Mapped[str | None] = mapped_column(Enum(*TYPES, native_enum=False), default="video")
    poids: Mapped[Decimal | None] = mapped_column(Numeric(5, 2))
    taille: Mapped[Decimal | None] = mapped_column(Numeric(5, 2))
    country_id: Mapped[int | None] = mapped_column(ForeignKey("pays.id"))
    age: Mapped[int | None] = mapped_column(Integer)
    whatsapppatient: Mapped[str | None] = mapped_column(String(50))
    symptomes: Mapped[str | None] = mapped_column(Text)
    duree_symptomes: Mapped[str | None] = mapped_column(String(50))
    consultation_precedente: Mapped[str | None] = mapped_column(Enum("yes", "no", native_enum=False), default="no")
    examens_demandes: Mapped[str | None] = mapped_column(Text)
    ordonnances: Mapped[str | None] = mapped_column(Text)
    diagnostic: Mapped[str | None] = mapped_column(Text)
    traitement: Mapped[str | None] = mapped_column(Text)
    notes_medecin: Mapped[str | None] = mapped_column(Text)
    date_souhaitee: Mapped[datetime] = mapped_column(DateTime)
    date_confirmee: Mapped[datetime | None] = mapped_column(DateTime)
    date_debut: Mapped[datetime | None] = mapped_column(DateTime)
    date_fin: Mapped[datetime | None] = mapped_column(DateTime)
    duree_minutes: Mapped[int | None] = mapped_column(Integer, default=30)
    room_id: Mapped[str | None] = mapped_column(String(100), unique=True)
    room_url: Mapped[str | None] = mapped_column(String(500))
    statut: Mapped[str | None] = mapped_column(Enum(*STATUTS, native_enum=False), default="en_attente")
    honoraires_consultation: Mapped[Decimal] = mapped_column(Numeric(10, 2), default=0)
    motif_annulation: Mapped[str | None] = mapped_column(String(255))
    prix_ht: Mapped[Decimal] = mapped_column(Numeric(15, 2))
    devise: Mapped[str | None] = mapped_column(String(10), default="USD")
    tva: Mapped[Decimal | None] = mapped_column(Numeric(5, 2), default=0)
    # Colonne calculée par MySQL (GENERATED ALWAYS) : jamais écrite par l'API.
    prix_ttc: Mapped[Decimal | None] = mapped_column(Numeric(15, 2), Computed("prix_ht * (1 + tva / 100)",
                                                                              persisted=True))
    paiement_statut: Mapped[str | None] = mapped_column(Enum(*PAIEMENT_STATUTS, native_enum=False),
                                                        default="en_attente")
    mode_paiement: Mapped[str | None] = mapped_column(String(50))
    preuve_paiement: Mapped[str | None] = mapped_column(String(255))
    ip_creation: Mapped[str] = mapped_column(String(45), default="")
    rappel_envoye_at: Mapped[datetime | None] = mapped_column(DateTime)
    rappel_veille_at: Mapped[datetime | None] = mapped_column(DateTime)
    created_at: Mapped[datetime | None] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime | None] = mapped_column(DateTime, server_default=func.now(), onupdate=func.now())

    medecin: Mapped[Medecin] = relationship(lazy="joined")
    patient: Mapped[User | None] = relationship(lazy="joined", foreign_keys=[patient_id])
    pays: Mapped[Pays | None] = relationship(lazy="joined")


class ConsultationDocument(Base):
    __tablename__ = "consultation_documents"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    consultation_id: Mapped[int] = mapped_column(ForeignKey("consultations.id"))
    uploaded_by: Mapped[int | None] = mapped_column(Integer)
    kind: Mapped[str] = mapped_column(Enum("document", "ordonnance", "preuve_paiement", native_enum=False))
    stored_name: Mapped[str] = mapped_column(String(100), unique=True)
    original_name: Mapped[str | None] = mapped_column(String(255))
    mime: Mapped[str] = mapped_column(String(100))
    size_bytes: Mapped[int] = mapped_column(Integer)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class ConsultationHistory(Base):
    __tablename__ = "consultation_history"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    consultation_id: Mapped[int] = mapped_column(ForeignKey("consultations.id"))
    from_status: Mapped[str | None] = mapped_column(String(20))
    to_status: Mapped[str] = mapped_column(String(20))
    actor_id: Mapped[int | None] = mapped_column(Integer)
    actor_role: Mapped[str | None] = mapped_column(String(20))
    note: Mapped[str | None] = mapped_column(String(500))
    created_at: Mapped[datetime] = mapped_column(DateTime)


class ConsultationAvis(Base):
    __tablename__ = "consultation_avis"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    consultation_id: Mapped[int] = mapped_column(ForeignKey("consultations.id"), unique=True)
    patient_id: Mapped[int] = mapped_column(Integer)
    medecin_id: Mapped[int] = mapped_column(Integer)
    note: Mapped[int] = mapped_column(SmallInteger)
    commentaire: Mapped[str | None] = mapped_column(String(1000))
    created_at: Mapped[datetime] = mapped_column(DateTime)


class Paiement(Base):
    __tablename__ = "paiements"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    consultation_id: Mapped[int] = mapped_column(ForeignKey("consultations.id"))
    prix_ttc: Mapped[Decimal] = mapped_column(Numeric(10, 2))
    devise: Mapped[str] = mapped_column(String(10), default="USD")
    payment_date: Mapped[datetime] = mapped_column(DateTime)
    created_at: Mapped[datetime | None] = mapped_column(DateTime, server_default=func.now())
