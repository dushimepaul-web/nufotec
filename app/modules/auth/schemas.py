from datetime import date, datetime
from typing import Literal

from pydantic import BaseModel, ConfigDict, EmailStr, Field, field_validator

from app.core.uploads import public_url

# Les règles détaillées (lettre, chiffre, mots courants…) sont vérifiées dans le service,
# qui connaît l'email et le nom de l'utilisateur.
NewPassword = Field(min_length=8, max_length=72)

# Types qu'un visiteur peut choisir lui-même (admin et médecin sont créés par un administrateur).
SelfType = Literal["patient", "entreprise", "investisseur", "partenaire", "broker"]
Genre = Literal["M", "F", "Autre"]


def _clean_name(v: str | None) -> str | None:
    if v is None:
        return None
    v = " ".join(v.split())
    if not v:
        raise ValueError("Ne peut pas être vide")
    return v.lower().capitalize()


class RegisterIn(BaseModel):
    email: EmailStr
    password: str = NewPassword
    nom: str = Field(min_length=1, max_length=50)
    prenom: str = Field(min_length=1, max_length=50)
    telephone: str | None = Field(default=None, max_length=30, description="Avec ou sans indicatif (+257 par défaut)")
    pays_id: int | None = None
    date_naissance: date | None = None
    genre: Genre | None = None
    type_utilisateur: SelfType = "patient"
    nom_entreprise: str | None = Field(default=None, max_length=100)

    _names = field_validator("nom", "prenom")(_clean_name)

    @field_validator("date_naissance")
    @classmethod
    def _birth(cls, v: date | None) -> date | None:
        if v and not (date(1900, 1, 1) <= v <= date.today()):
            raise ValueError("Date de naissance invalide")
        return v


class LoginIn(BaseModel):
    login: str = Field(min_length=3, max_length=100, description="Email ou numéro de téléphone")
    password: str = Field(min_length=1, max_length=72)


class RefreshIn(BaseModel):
    refresh_token: str = Field(min_length=10, max_length=2000)


class ChangePasswordIn(BaseModel):
    current_password: str = Field(min_length=1, max_length=72)
    new_password: str = NewPassword


class ForgotPasswordIn(BaseModel):
    email: EmailStr


class ResetPasswordIn(BaseModel):
    email: EmailStr
    code: str = Field(pattern=r"^\d{6}$", description="Code à 6 chiffres reçu par email")
    new_password: str = NewPassword


class VerifyEmailIn(BaseModel):
    token: str = Field(min_length=20, max_length=255)


class DeleteAccountIn(BaseModel):
    password: str = Field(min_length=1, max_length=72)
    reason: str | None = Field(default=None, max_length=500)


class ProfileUpdateIn(BaseModel):
    nom: str | None = Field(default=None, min_length=1, max_length=50)
    prenom: str | None = Field(default=None, min_length=1, max_length=50)
    telephone: str | None = Field(default=None, max_length=30)
    pays_id: int | None = None
    date_naissance: date | None = None
    genre: Genre | None = None
    nom_entreprise: str | None = Field(default=None, max_length=100)
    secteur_activite: str | None = Field(default=None, max_length=100)

    _names = field_validator("nom", "prenom")(_clean_name)


class RoleOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    nom: str
    slug: str


class PaysOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    pays: str | None
    iso2: str | None


class UserOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    uuid: str
    email: str
    nom: str
    prenom: str
    telephone: str | None
    date_naissance: date | None
    genre: str | None
    photo_url: str | None
    type_utilisateur: str | None
    nom_entreprise: str | None
    secteur_activite: str | None
    is_active: bool
    email_verified: bool
    est_verifie: bool
    last_login_at: datetime | None
    created_at: datetime | None
    role: RoleOut
    pays: PaysOut | None

    @classmethod
    def of(cls, user) -> "UserOut":
        computed = {"email_verified", "photo_url"}
        data = {k: getattr(user, k) for k in cls.model_fields if k not in computed}
        return cls(**data, email_verified=user.email_verified_at is not None,
                   photo_url=public_url("Users", user.photo))


class TokensOut(BaseModel):
    token_type: str = "Bearer"
    access_token: str
    access_expires_at: datetime
    refresh_token: str
    refresh_expires_at: datetime


class AuthOut(BaseModel):
    user: UserOut
    tokens: TokensOut


class SessionOut(BaseModel):
    id: int
    device_type: str | None
    platform: str | None
    browser: str | None
    ip_address: str | None
    login_time: datetime
    last_activity: datetime
    current: bool
