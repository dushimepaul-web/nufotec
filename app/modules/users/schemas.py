from datetime import date
from decimal import Decimal
from typing import Literal

from pydantic import BaseModel, EmailStr, Field, field_validator

from app.core.types import DateTime
from app.core.uploads import public_url
from app.models.user import USER_TYPES
from app.modules.auth.schemas import Genre, PaysOut, RoleOut, _clean_name

UserType = Literal[USER_TYPES]  # type: ignore[valid-type]
Statut = Literal["actif", "inactif", "supprime"]
Tri = Literal["created_at", "-created_at", "nom", "-nom", "email", "-email", "last_login_at", "-last_login_at"]


class _UserFields(BaseModel):
    telephone: str | None = Field(default=None, max_length=30)
    pays_id: int | None = None
    date_naissance: date | None = None
    genre: Genre | None = None
    nom_entreprise: str | None = Field(default=None, max_length=100)
    secteur_activite: str | None = Field(default=None, max_length=100)
    numero_registre_commerce: str | None = Field(default=None, max_length=50)
    interet_investissement: Decimal | None = Field(default=None, ge=0, max_digits=15, decimal_places=2)


class AdminUserCreateIn(_UserFields):
    email: EmailStr
    nom: str = Field(min_length=1, max_length=50)
    prenom: str = Field(min_length=1, max_length=50)
    type_utilisateur: UserType = Field(description="Le rôle en découle : admin, medecin, sinon utilisateur")
    role_id: int | None = Field(default=None, description="Rôle précis (prioritaire sur le type)")
    est_verifie: bool = False
    send_invitation: bool = Field(default=True, description="Envoie un code pour choisir son mot de passe (72 h)")

    _names = field_validator("nom", "prenom")(_clean_name)


class AdminUserUpdateIn(_UserFields):
    email: EmailStr | None = None
    nom: str | None = Field(default=None, min_length=1, max_length=50)
    prenom: str | None = Field(default=None, min_length=1, max_length=50)
    type_utilisateur: UserType | None = Field(default=None, description="Changer le type change aussi le rôle")
    role_id: int | None = Field(default=None, description="Rôle précis (prioritaire sur le type)")

    _names = field_validator("nom", "prenom")(_clean_name)


class KycIn(BaseModel):
    est_verifie: bool


class AdminUserItem(BaseModel):
    uuid: str
    email: str
    nom: str
    prenom: str
    telephone: str | None
    photo_url: str | None
    type_utilisateur: str | None
    role: RoleOut
    is_active: bool
    email_verified: bool
    est_verifie: bool
    last_login_at: DateTime | None
    created_at: DateTime | None
    deleted_at: DateTime | None

    @classmethod
    def of(cls, user) -> "AdminUserItem":
        computed = {"email_verified", "photo_url"}
        data = {k: getattr(user, k) for k in cls.model_fields if k not in computed}
        return cls(**data, email_verified=user.email_verified_at is not None,
                   photo_url=public_url("Users", user.photo))


class AdminUserOut(AdminUserItem):
    date_naissance: date | None
    genre: str | None
    pays: PaysOut | None
    nom_entreprise: str | None
    secteur_activite: str | None
    numero_registre_commerce: str | None
    interet_investissement: Decimal | None
    email_verified_at: DateTime | None
    last_login_ip: str | None
    updated_at: DateTime | None
    active_sessions: int = 0


class UserStatsOut(BaseModel):
    total: int
    actifs: int
    inactifs: int
    supprimes: int
    email_verifies: int
    kyc_verifies: int
    nouveaux_30_jours: int
    par_type: dict[str, int]
    par_role: dict[str, int]
    inscriptions_par_mois: list[dict]


class ActivityOut(BaseModel):
    id: int
    action: str
    module: str | None
    description: str | None
    auteur: str | None
    ip_address: str | None
    created_at: DateTime | None
