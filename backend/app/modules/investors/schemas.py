from datetime import datetime
from decimal import Decimal
from typing import Literal

from pydantic import BaseModel, EmailStr, Field, field_validator

from app.core.text import clean_free_text, clean_person_name
from app.core.types import DateTime
from app.models.investment import (
    BROKER_STATUTS,
    CAPACITIES,
    COMMITMENT_RANGES,
    ENGAGEMENT,
    FOCUS,
    INTERESTS,
    INVESTOR_STATUTS,
    INVESTOR_TYPES,
    MANDATES,
    REGULATORY,
    TIMELINES,
)

Interest = Literal[tuple(INTERESTS)]  # type: ignore[valid-type]
Focus = Literal[tuple(FOCUS)]  # type: ignore[valid-type]
Capacity = Literal[tuple(CAPACITIES)]  # type: ignore[valid-type]
InvestorType = Literal[tuple(INVESTOR_TYPES)]  # type: ignore[valid-type]
Mandate = Literal[tuple(MANDATES)]  # type: ignore[valid-type]
Commitment = Literal[COMMITMENT_RANGES]  # type: ignore[valid-type]
Timeline = Literal[TIMELINES]  # type: ignore[valid-type]
InvestorStatut = Literal[INVESTOR_STATUTS]  # type: ignore[valid-type]
BrokerStatut = Literal[BROKER_STATUTS]  # type: ignore[valid-type]


def _strip(v: str | None) -> str | None:
    return " ".join(v.split()) or None if v else None


def _person(v: str) -> str:
    """Nom : caractères de contrôle, balises et symboles retirés ; au moins une lettre (app/core/text.py)."""
    cleaned = clean_person_name(v)
    if len(cleaned) < 2:
        raise ValueError("trop court")
    return cleaned


def _text(v: str | None) -> str | None:
    return clean_free_text(v)


class _Honeypot(BaseModel):
    site_web: str | None = Field(default=None, description="Champ invisible anti-robots : doit rester vide")


# ───────────── formulaires publics ─────────────

class InvestorFormIn(_Honeypot):
    nom_complet: str = Field(min_length=2, max_length=150)
    organisation: str | None = Field(default=None, max_length=150)
    fonction: str | None = Field(default=None, max_length=150)
    pays_id: int
    email: EmailStr
    telephone: str | None = Field(default=None, max_length=30)
    interets: list[Interest] = Field(default_factory=list, max_length=7)
    interet_autre: str | None = Field(default=None, max_length=255)
    montant: Commitment | None = None
    domaines: list[Focus] = Field(default_factory=list, max_length=5)
    calendrier: Timeline = "Exploratory"
    message: str | None = Field(default=None, max_length=5000)
    accepte_contact: bool = Field(description="Obligatoire : accepte d'être contacté")
    confirmation_non_engageante: bool = Field(description="Obligatoire : la manifestation n'engage pas")

    _clean = field_validator("nom_complet", "organisation", "fonction", "interet_autre")(_strip)
    _name = field_validator("nom_complet")(_person)
    _texts = field_validator("organisation", "fonction", "interet_autre", "message")(_text)


class BrokerFormIn(_Honeypot):
    nom_complet: str = Field(min_length=2, max_length=150)
    cabinet: str = Field(min_length=2, max_length=200)
    juridiction: str | None = Field(default=None, max_length=150)
    numero_enregistrement: str | None = Field(default=None, max_length=100)
    statut_reglementaire: Literal[REGULATORY] | None = None  # type: ignore[valid-type]
    autorite_reglementaire: str | None = Field(default=None, max_length=150)
    pays_id: int
    email: EmailStr
    telephone: str | None = Field(default=None, max_length=30)
    whatsapp: str | None = Field(default=None, max_length=30)
    site_internet: str | None = Field(default=None, max_length=200)
    capacites: list[Capacity] = Field(default_factory=list, max_length=7)
    capacite_autre: str | None = Field(default=None, max_length=255)
    types_investisseurs: list[InvestorType] = Field(default_factory=list, max_length=7)
    ticket_moyen: str | None = Field(default=None, max_length=150)
    couverture_geographique: str | None = Field(default=None, max_length=2000)
    mandats: list[Mandate] = Field(default_factory=list, max_length=6)
    modele_remuneration: Literal[ENGAGEMENT] | None = None  # type: ignore[valid-type]
    confirme_autorise: bool
    confirme_aml_kyc: bool
    reconnait_non_exclusivite: bool
    comprend_mandat_formel: bool

    _clean = field_validator("nom_complet", "cabinet", "juridiction", "capacite_autre", "ticket_moyen")(_strip)
    _name = field_validator("nom_complet")(_person)
    _texts = field_validator("cabinet", "juridiction", "numero_enregistrement", "autorite_reglementaire", "capacite_autre",
                             "ticket_moyen", "couverture_geographique")(_text)

    @field_validator("site_internet")
    @classmethod
    def _url(cls, v: str | None) -> str | None:
        if v and not v.startswith(("https://", "http://")):
            v = "https://" + v
        return v


# ───────────── espaces investisseur / courtier ─────────────

class PhaseOut(BaseModel):
    id: int
    nom: str
    annee_debut: int | None
    annee_fin: int | None
    montant_total: Decimal | None
    devise: str | None
    description: str | None
    repartition: list[dict]


class InvestorOut(BaseModel):
    uuid: str
    nom_complet: str
    organisation: str | None
    fonction: str | None
    pays: str | None
    email: str
    telephone: str | None
    interets: list[str]
    interet_autre: str | None
    montant: str | None
    domaines: list[str]
    calendrier: str | None
    message: str | None
    statut: str
    source: str
    created_at: DateTime | None


class InvestorAdminOut(InvestorOut):
    responsable: str | None
    prochaine_relance: DateTime | None
    courtier: str | None
    nda_accepte: bool
    consent_at: DateTime | None


class BrokerOut(BaseModel):
    uuid: str
    nom_complet: str
    cabinet: str
    pays: str | None
    email: str
    telephone: str | None
    whatsapp: str | None
    site_internet: str | None
    juridiction: str | None
    numero_enregistrement: str | None
    statut_reglementaire: str | None
    autorite_reglementaire: str | None
    capacites: list[str]
    types_investisseurs: list[str]
    mandats: list[str]
    ticket_moyen: str | None
    couverture_geographique: str | None
    modele_remuneration: str | None
    statut: str
    motif_decision: str | None
    created_at: DateTime | None


class IntroductionIn(BaseModel):
    nom_complet: str = Field(min_length=2, max_length=150)
    organisation: str | None = Field(default=None, max_length=200)
    email: EmailStr
    telephone: str | None = Field(default=None, max_length=30)
    pays_id: int
    montant: Commitment | None = None
    notes: str | None = Field(default=None, max_length=3000)


class IntroductionOut(BaseModel):
    id: int
    nom_complet: str
    organisation: str | None
    email: str
    montant: str | None
    statut: str
    attribue_jusqu_au: DateTime | None
    created_at: DateTime | None


class NdaOut(BaseModel):
    version: str
    texte: str
    accepte: bool
    accepte_le: DateTime | None


class DataRoomItem(BaseModel):
    uuid: str
    titre: str
    description: str | None
    nom_fichier: str
    taille: int
    ajoute_le: DateTime


# ───────────── administration ─────────────

class InvestorUpdateIn(BaseModel):
    organisation: str | None = Field(default=None, max_length=150)
    fonction: str | None = Field(default=None, max_length=150)
    telephone: str | None = Field(default=None, max_length=30)
    montant: Commitment | None = None
    calendrier: Timeline | None = None


class StatusIn(BaseModel):
    statut: InvestorStatut
    note: str | None = Field(default=None, max_length=2000)


class NoteIn(BaseModel):
    texte: str = Field(min_length=1, max_length=5000)


class AssignIn(BaseModel):
    admin_uuid: str | None = Field(description="null pour retirer le responsable")


class FollowUpIn(BaseModel):
    le: datetime | None = Field(description="Date de relance (null pour annuler)")
    note: str | None = Field(default=None, max_length=1000)


class BrokerDecisionIn(BaseModel):
    statut: Literal["approuve", "refuse", "suspendu", "en_attente"]
    motif: str | None = Field(default=None, max_length=255)


class PhaseIn(BaseModel):
    nom: str = Field(min_length=2, max_length=100)
    annee_debut: int | None = Field(default=None, ge=2000, le=2100)
    annee_fin: int | None = Field(default=None, ge=2000, le=2100)
    montant_total: Decimal | None = Field(default=None, ge=0, max_digits=15, decimal_places=2)
    devise: str = Field(default="USD", max_length=10)
    description: str | None = Field(default=None, max_length=10000)
    repartition: dict[str, Decimal] = Field(default_factory=dict,
                                            description="Poste → pourcentage ; le total doit faire 100")


class DataRoomUpdateIn(BaseModel):
    titre: str | None = Field(default=None, min_length=2, max_length=255)
    description: str | None = Field(default=None, max_length=3000)
    visibilite: Literal["tous_qualifies", "selection"] | None = None
    investisseurs: list[str] | None = Field(default=None, description="uuid des investisseurs (visibilité « selection »)")
    actif: bool | None = None
