"""Rôles (admin), dynamiques.

Trois rôles systèmes subsistent (admin, medecin, user) : on ne peut ni les supprimer ni changer
leur slug. L'administrateur peut en revanche créer de nouveaux rôles et attribuer à chacun des
permissions par module (une entrée de la barre latérale) et par action (voir, ajouter, modifier,
supprimer, exporter, activer). Le rôle admin conserve toujours tous les droits.
"""

import json

from fastapi import APIRouter, Depends
from pydantic import BaseModel, Field
from sqlalchemy import func, select
from sqlalchemy.orm import Session

from app.core import audit
from app.core.client import ClientInfo, get_client
from app.core.database import get_db
from app.core.helpers import slugify, validation_error
from app.core.permissions import (
    ACTIONS,
    GRANULAR,
    MODULES,
    PERMISSIONS,
    ROLE_PERMISSIONS,
    SYSTEM_ROLES,
    require_permission,
)
from app.core.responses import ApiError, Envelope, ok
from app.models import Role, User

router = APIRouter(prefix="/admin/roles", tags=["Admin · Rôles"])

DEFAULT_NIVEAU = 10


class RoleCreateIn(BaseModel):
    nom: str = Field(min_length=2, max_length=50, description="Nom affiché")
    description: str | None = Field(default=None, max_length=1000)
    permissions: list[str] = Field(default_factory=list, description="Clés « module.action »")


class RoleUpdateIn(BaseModel):
    nom: str | None = Field(default=None, min_length=2, max_length=50, description="Nom affiché")
    description: str | None = Field(default=None, max_length=1000)
    permissions: list[str] | None = Field(default=None, description="Remplace les permissions du rôle")


class RoleDetailOut(BaseModel):
    id: int
    nom: str
    slug: str
    description: str | None
    niveau: int
    systeme: bool
    users_count: int
    permissions: list[str]


def _stored_permissions(role: Role) -> list[str]:
    """Permissions affichées : le rôle admin a tout, les autres lisent la colonne (ou défaut)."""
    if role.slug == "admin":
        return sorted(GRANULAR)
    if role.permissions:
        try:
            data = json.loads(role.permissions)
            if isinstance(data, list):
                return sorted(str(item) for item in data)
        except (TypeError, ValueError):
            pass
    return sorted(ROLE_PERMISSIONS.get(role.slug, set()))


def _out(db: Session, role: Role) -> RoleDetailOut:
    count = db.scalar(select(func.count()).select_from(User).where(
        User.role_id == role.id, User.deleted_at.is_(None))) or 0
    return RoleDetailOut(id=role.id, nom=role.nom, slug=role.slug, description=role.description,
                         niveau=role.niveau or 0, systeme=role.slug in SYSTEM_ROLES, users_count=count,
                         permissions=_stored_permissions(role))


def _get(db: Session, role_id: int) -> Role:
    role = db.get(Role, role_id)
    if role is None:
        raise ApiError(404, "NOT_FOUND", "Rôle introuvable")
    return role


def _check_unique_name(db: Session, nom: str, exclude_id: int | None = None) -> str:
    slug = slugify(nom)
    if not slug:
        raise validation_error("nom", "Nom invalide")
    query = select(Role.id).where((func.lower(Role.nom) == nom.lower()) | (Role.slug == slug))
    if exclude_id:
        query = query.where(Role.id != exclude_id)
    if db.scalar(query):
        raise ApiError(409, "ROLE_EXISTS", "Un rôle porte déjà ce nom", [{"field": "nom"}])
    return slug


def _validate_permissions(permissions: list[str]) -> set[str]:
    unknown = {p for p in permissions if p not in PERMISSIONS}
    if unknown:
        raise validation_error("permissions", f"Permission inconnue : {', '.join(sorted(unknown))}")
    return set(permissions)


@router.get("", response_model=Envelope[list[RoleDetailOut]], summary="Liste des rôles")
def list_roles(db: Session = Depends(get_db), _: User = Depends(require_permission("roles.read"))):
    return ok([_out(db, r) for r in db.scalars(select(Role).order_by(Role.niveau.desc(), Role.id))])


@router.get("/permissions", response_model=Envelope[dict], summary="Catalogue des modules et actions")
def list_permissions(_: User = Depends(require_permission("roles.read"))):
    return ok({"actions": ACTIONS, "modules": MODULES})


@router.get("/{role_id}", response_model=Envelope[RoleDetailOut], summary="Détail d'un rôle")
def get_role(role_id: int, db: Session = Depends(get_db), _: User = Depends(require_permission("roles.read"))):
    return ok(_out(db, _get(db, role_id)))


@router.post("", status_code=201, response_model=Envelope[RoleDetailOut], summary="Créer un rôle")
def create_role(body: RoleCreateIn, db: Session = Depends(get_db), client: ClientInfo = Depends(get_client),
                actor: User = Depends(require_permission("roles.create"))):
    nom = " ".join(body.nom.split())
    slug = _check_unique_name(db, nom)
    permissions = _validate_permissions(body.permissions)
    role = Role(nom=nom, slug=slug, description=body.description, niveau=DEFAULT_NIVEAU,
                permissions=json.dumps(sorted(permissions)) if permissions else None)
    db.add(role)
    db.flush()
    audit.activity(db, actor.id, "role_created", client, f"Rôle « {nom} » créé", module="roles",
                   item_id=role.id, item_name=nom, new_data={"nom": nom, "permissions": sorted(permissions)})
    db.commit()
    db.refresh(role)
    return ok(_out(db, role), "Rôle créé")


@router.patch("/{role_id}", response_model=Envelope[RoleDetailOut],
              summary="Modifier le nom, la description ou les permissions d'un rôle")
def update_role(role_id: int, body: RoleUpdateIn, db: Session = Depends(get_db),
                client: ClientInfo = Depends(get_client),
                actor: User = Depends(require_permission("roles.update"))):
    role = _get(db, role_id)
    changes = body.model_dump(exclude_unset=True)
    if "nom" in changes:
        if not changes["nom"]:
            raise validation_error("nom", "Ne peut pas être vide")
        changes["nom"] = " ".join(changes["nom"].split())
        _check_unique_name(db, changes["nom"], exclude_id=role.id)
        role.nom = changes["nom"]
    if "description" in changes:
        role.description = changes["description"]
    if "permissions" in changes and role.slug != "admin":
        permissions = _validate_permissions(changes["permissions"] or [])
        role.permissions = json.dumps(sorted(permissions)) if permissions else None
    audit.activity(db, actor.id, "role_updated", client, f"Rôle « {role.nom} » modifié", module="roles",
                   item_id=role.id, item_name=role.nom, new_data=changes)
    db.commit()
    db.refresh(role)
    return ok(_out(db, role), "Rôle mis à jour")


@router.delete("/{role_id}", response_model=Envelope[dict], summary="Supprimer un rôle personnalisé")
def delete_role(role_id: int, db: Session = Depends(get_db), client: ClientInfo = Depends(get_client),
                actor: User = Depends(require_permission("roles.delete"))):
    role = _get(db, role_id)
    if role.slug in SYSTEM_ROLES:
        raise ApiError(409, "SYSTEM_ROLE", "Ce rôle système ne peut pas être supprimé")
    count = db.scalar(select(func.count()).select_from(User).where(
        User.role_id == role.id, User.deleted_at.is_(None))) or 0
    if count:
        raise ApiError(409, "ROLE_IN_USE", f"{count} utilisateur(s) portent encore ce rôle")
    audit.activity(db, actor.id, "role_deleted", client, f"Rôle « {role.nom} » supprimé", module="roles",
                   item_id=role.id, item_name=role.nom)
    db.delete(role)
    db.commit()
    return ok({"id": role_id}, "Rôle supprimé")
