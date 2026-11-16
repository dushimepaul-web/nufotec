from datetime import datetime, time
from decimal import Decimal
from typing import Literal

from pydantic import BaseModel, EmailStr, Field, field_validator

from app.core.text import clean_free_text, clean_person_name
from app.core.types import DateTime
from app.models.consultation import JOURS, STATUTS, TYPES
from app.modules.auth.schemas import Genre, _clean_name

Jour = Literal[JOURS]  # type: ignore[valid-type]
Statut = Literal[STATUTS]  # type: ignore[valid-type]
TypeConsultation = Literal[TYPES]  # type: ignore[valid-type]


# ───────────── médecins ─────────────

class HoraireItem(BaseModel):
    jour: Jour
    debut: time
    fin: time

    @field_validator("debut", "fin")
    @classmethod
    def _minutes(cls, v: time) -> time:
        if v.second or v.microsecond or v.minute % 15:
            raise ValueError("Les heures doivent être des quarts d'heure (ex. 08:00, 08:15)")
        return v


class MedecinPublic(BaseModel):
    uuid: str
    nom: str
    prenom: str
    photo_url: str | None
    specialite: str
    annees_experience: int | None
    langues: list[str]
    bio: str | None
    prix_usd: Decimal | None
    prix_bif: Decimal | None
    devise_etranger: str = "USD"
    tarifs: dict = Field(default_factory=dict, description="Textes prêts à afficher (burundi, etranger, equivalent_bif, lignes)")
    note_moyenne: Decimal | None
    nombre_avis: int | None
    disponible: bool
    disponible_maintenant: bool
    licence_verifiee: bool
    duree_minutes: int


class HoraireOut(BaseModel):
    """Affichage : sans contrôle des quarts d'heure (d'anciens horaires saisis dans le PHP peuvent être « 23:03 »)."""

    jour: Jour
    debut: time
    fin: time


class MedecinDetail(MedecinPublic):
    diplomes: str | None
    horaires: list[HoraireOut]


class MedecinAdminOut(MedecinDetail):
    user_uuid: str
    email: str
    telephone: str | None
    numero_licence: str
    whatsapp: str | None
    compte_actif: bool


class Creneau(BaseModel):
    debut: DateTime
    fin: DateTime


class ModePaiementOut(BaseModel):
    id: int
    nom: str
    numero_compte: str | None
    nom_compte: str | None


class MedecinProfileIn(BaseModel):
    """Ce que le médecin peut modifier lui-même."""

    bio: str | None = Field(default=None, max_length=3000)
    langues: list[str] | None = Field(default=None, max_length=10)
    est_disponible: bool | None = None
    whatsapp: str | None = Field(default=None, max_length=30)


class AdminMedecinIn(BaseModel):
    """Créer un médecin : soit un compte existant de type medecin (user_uuid), soit un nouveau compte."""

    user_uuid: str | None = None
    email: EmailStr | None = None
    nom: str | None = Field(default=None, min_length=1, max_length=50)
    prenom: str | None = Field(default=None, min_length=1, max_length=50)
    telephone: str | None = Field(default=None, max_length=30)
    genre: Genre | None = None
    specialite: str = Field(min_length=2, max_length=100)
    numero_licence: str = Field(min_length=2, max_length=50)
    annees_experience: int = Field(default=0, ge=0, le=70)
    diplomes: str | None = Field(default=None, max_length=3000)
    bio: str | None = Field(default=None, max_length=3000)
    langues: list[str] = Field(default_factory=list, max_length=10)
    whatsapp: str | None = Field(default=None, max_length=30, description="Par défaut : le téléphone du compte")
    prix_usd: Decimal = Field(gt=0, max_digits=10, decimal_places=2)
    prix_bif: Decimal = Field(gt=0, max_digits=12, decimal_places=2)
    est_disponible: bool = True
    duree_consultation_minutes: int = Field(default=30, ge=15, le=120, multiple_of=5)
    horaires: list[HoraireItem] = Field(default_factory=list, max_length=30)

    _names = field_validator("nom", "prenom")(_clean_name)


class AdminMedecinUpdateIn(BaseModel):
    specialite: str | None = Field(default=None, min_length=2, max_length=100)
    numero_licence: str | None = Field(default=None, min_length=2, max_length=50)
    annees_experience: int | None = Field(default=None, ge=0, le=70)
    diplomes: str | None = Field(default=None, max_length=3000)
    bio: str | None = Field(default=None, max_length=3000)
    langues: list[str] | None = Field(default=None, max_length=10)
    whatsapp: str | None = Field(default=None, max_length=30)
    prix_usd: Decimal | None = Field(default=None, gt=0, max_digits=10, decimal_places=2)
    prix_bif: Decimal | None = Field(default=None, gt=0, max_digits=12, decimal_places=2)
    est_disponible: bool | None = None
    duree_consultation_minutes: int | None = Field(default=None, ge=15, le=120, multiple_of=5)


class LicenceIn(BaseModel):
    verifiee: bool


# ───────────── consultations ─────────────

class BookingIn(BaseModel):
    medecin_uuid: str
    debut: datetime = Field(description="Créneau choisi (heure du Burundi si aucun fuseau n'est indiqué)")
    type: TypeConsultation = "video"
    symptomes: str = Field(min_length=20, max_length=5000)
    duree_symptomes: str | None = Field(default=None, max_length=50)
    consultation_precedente: bool = False
    age: int = Field(ge=0, le=120)
    poids: Decimal = Field(gt=0, lt=300, max_digits=5, decimal_places=2, description="kg")
    taille: Decimal = Field(gt=40, lt=260, max_digits=5, decimal_places=2, description="cm")
    pays_residence_id: int = Field(description="Pays de résidence (Burundi → tarif en BIF)")
    whatsapp: str | None = Field(default=None, max_length=30, description="Par défaut : le téléphone du profil")
    mode_paiement_id: int


class PublicBookingIn(BookingIn):
    """Réservation SANS compte : le visiteur donne son nom et son WhatsApp ; la demande part ensuite sur WhatsApp."""
    nom_complet: str = Field(min_length=2, max_length=100)
    whatsapp: str = Field(min_length=6, max_length=30, description="Numéro WhatsApp du patient (avec indicatif)")
    email: EmailStr | None = None
    mode_paiement_id: int | None = None
    site_web: str | None = Field(default=None, description="Champ invisible anti-robots : doit rester vide")

    @field_validator("nom_complet")
    @classmethod
    def _clean_name_field(cls, v: str) -> str:
        cleaned = clean_person_name(v)
        if len(cleaned) < 2:
            raise ValueError("trop court")
        return cleaned

    @field_validator("symptomes", "duree_symptomes")
    @classmethod
    def _clean_texts(cls, v: str | None) -> str | None:
        return clean_free_text(v)


class RescheduleIn(BaseModel):
    debut: datetime = Field(description="Nouveau créneau (heure du Burundi si aucun fuseau n'est indiqué)")


class AbsenceIn(BaseModel):
    debut: datetime
    fin: datetime
    motif: str | None = Field(default=None, max_length=255)


class AbsenceOut(BaseModel):
    id: int
    debut: DateTime
    fin: DateTime
    motif: str | None
    rendez_vous_en_conflit: list[str] = []


class PaymentIn(BaseModel):
    mode_paiement_id: int


class CancelIn(BaseModel):
    motif: str | None = Field(default=None, max_length=255)


class RefuseIn(BaseModel):
    motif: str = Field(min_length=3, max_length=255)


class FinishIn(BaseModel):
    diagnostic: str = Field(min_length=2, max_length=5000)
    traitement: str | None = Field(default=None, max_length=5000)
    notes_medecin: str | None = Field(default=None, max_length=5000)


class ReviewIn(BaseModel):
    note: int = Field(ge=1, le=5)
    commentaire: str | None = Field(default=None, max_length=1000)


class PaymentDecisionIn(BaseModel):
    statut: Literal["paye", "echoue", "rembourse"]
    note: str | None = Field(default=None, max_length=255)


class ReassignIn(BaseModel):
    medecin_uuid: str


class AdminCancelIn(BaseModel):
    motif: str = Field(min_length=3, max_length=255)


class WhatsAppOut(BaseModel):
    numero: str = Field(description="Numéro WhatsApp du médecin")
    message: str = Field(description="Récapitulatif de la demande")
    url: str = Field(description="Lien à ouvrir : WhatsApp s'ouvre avec le message prêt à envoyer")


class PersonBrief(BaseModel):
    uuid: str | None
    nom: str | None
    prenom: str | None
    photo_url: str | None = None


class DocumentOut(BaseModel):
    id: int
    kind: str
    nom: str | None
    mime: str
    taille: int
    ajoute_le: DateTime


class LinkOut(BaseModel):
    url: str
    expire_le: DateTime


class JoinOut(BaseModel):
    url: str
    salle: str
    ouvre_le: DateTime
    ferme_le: DateTime


class HistoryOut(BaseModel):
    de: str | None
    vers: str
    par: str | None
    note: str | None
    le: DateTime


class ConsultationOut(BaseModel):
    """Une seule forme pour tous ; les champs médicaux sont absents (null) pour l'administration."""
    lie_par_telephone: bool | None = Field(default=None, description="Retrouvée par le WhatsApp du profil (sans données médicales)")

    numero: str
    statut: str
    paiement_statut: str | None
    type: str | None
    debut: DateTime
    fin: DateTime
    duree_minutes: int
    medecin: PersonBrief
    specialite: str | None
    patient: PersonBrief | None
    prix_ht: Decimal
    tva: Decimal | None
    prix_ttc: Decimal | None
    devise: str | None
    mode_paiement: str | None
    motif_annulation: str | None
    peut_annuler: bool
    peut_deplacer: bool = False
    peut_rejoindre: bool
    created_at: DateTime | None
    # Champs médicaux : patient et médecin uniquement
    symptomes: str | None = None
    duree_symptomes: str | None = None
    consultation_precedente: bool | None = None
    age: int | None = None
    poids: Decimal | None = None
    taille: Decimal | None = None
    whatsapp: str | None = None
    diagnostic: str | None = None
    traitement: str | None = None
    notes_medecin: str | None = None
    documents: list[DocumentOut] = []
    avis: dict | None = None
    historique: list[HistoryOut] | None = None
    whatsapp_medecin: WhatsAppOut | None = Field(
        default=None, description="Après la réservation : lien WhatsApp vers le médecin, message déjà rempli")


class GuestBookingOut(ConsultationOut):
    jeton: str = Field(description="Code temporaire (12 h) pour joindre documents et preuve de paiement sans compte")
