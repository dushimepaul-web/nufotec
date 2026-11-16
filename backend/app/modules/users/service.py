"""Gestion des utilisateurs par l'administration (reprise de modules/Users en PHP, avec garde-fous)."""

import csv
import io
import secrets
import uuid
from collections.abc import Iterator
from datetime import date, datetime, timedelta

from fastapi import BackgroundTasks
from sqlalchemy import and_, func, or_, select
from sqlalchemy.orm import Session
from sqlalchemy.sql import Select

from app.core import audit
from app.core.client import ClientInfo
from app.core.config import get_settings
from app.core.helpers import like_pattern, now, phone_or_422, users_by_phone, validation_error
from app.core.pagination import PageParams, paginate
from app.core.permissions import ADMIN_LEVEL, ADMIN_SLUG, level_of, role_slug_for_type
from app.core.phone import InvalidPhone, normalize_phone, sql_clean_phone
from app.core.responses import ApiError
from app.core.security import hash_password
from app.core.uploads import delete_file, save_image
from app.models import Pays, Role, User, UserActivity, UserSession
from app.modules.auth import service as auth_service
from app.modules.users.schemas import AdminUserCreateIn, AdminUserItem, AdminUserOut

ANONYMIZED_DOMAIN = "@deleted.nufotec.invalid"
SORTS = {
    "created_at": User.created_at.asc(), "-created_at": User.created_at.desc(),
    "nom": User.nom.asc(), "-nom": User.nom.desc(),
    "email": User.email.asc(), "-email": User.email.desc(),
    "last_login_at": User.last_login_at.asc(), "-last_login_at": User.last_login_at.desc(),
}


# ───────────────────────── lecture ─────────────────────────

def get_by_uuid(db: Session, user_uuid: str, include_deleted: bool = True) -> User:
    query = select(User).where(User.uuid == user_uuid)
    if not include_deleted:
        query = query.where(User.deleted_at.is_(None))
    user = db.scalar(query)
    if user is None:
        raise ApiError(404, "NOT_FOUND", "Utilisateur introuvable")
    return user


def build_query(q: str | None = None, role_id: int | None = None, type_utilisateur: str | None = None,
                statut: str | None = None, email_verifie: bool | None = None, kyc: bool | None = None,
                inscrit_du: date | None = None, inscrit_au: date | None = None,
                tri: str = "-created_at") -> Select:
    query = select(User)
    if statut == "supprime":
        query = query.where(User.deleted_at.is_not(None))
    else:
        query = query.where(User.deleted_at.is_(None))
        if statut == "actif":
            query = query.where(User.is_active.is_(True))
        elif statut == "inactif":
            query = query.where(User.is_active.is_(False))
    if q and q.strip():
        term = q.strip()
        pattern = like_pattern(term)
        conditions = [User.nom.like(pattern), User.prenom.like(pattern), User.email.like(pattern),
                      func.concat(User.prenom, " ", User.nom).like(pattern),
                      func.concat(User.nom, " ", User.prenom).like(pattern)]
        digits = "".join(c for c in term if c.isdigit())
        if len(digits) >= 4:
            conditions.append(sql_clean_phone(User.telephone).like(f"%{digits}%"))
        query = query.where(or_(*conditions))
    if role_id:
        query = query.where(User.role_id == role_id)
    if type_utilisateur:
        query = query.where(User.type_utilisateur == type_utilisateur)
    if email_verifie is not None:
        query = query.where(User.email_verified_at.is_not(None) if email_verifie else User.email_verified_at.is_(None))
    if kyc is not None:
        query = query.where(User.est_verifie.is_(kyc))
    if inscrit_du:
        query = query.where(User.created_at >= datetime.combine(inscrit_du, datetime.min.time()))
    if inscrit_au:
        query = query.where(User.created_at < datetime.combine(inscrit_au + timedelta(days=1), datetime.min.time()))
    return query.order_by(SORTS[tri], User.id.desc())


def list_users(db: Session, params: PageParams, **filters):
    rows, meta = paginate(db, build_query(**filters), params)
    return [AdminUserItem.of(u) for u in rows], meta


def detail(db: Session, user: User) -> AdminUserOut:
    sessions = db.scalar(select(func.count()).select_from(UserSession).where(
        UserSession.user_id == user.id, UserSession.is_active.is_(True), UserSession.expiry_time > now())) or 0
    base = AdminUserItem.of(user).model_dump()
    extra = {k: getattr(user, k) for k in AdminUserOut.model_fields if k not in base and k != "active_sessions"}
    return AdminUserOut(**base, **extra, active_sessions=sessions)


def stats(db: Session) -> dict:
    alive = User.deleted_at.is_(None)

    def count(*conds) -> int:
        return db.scalar(select(func.count()).select_from(User).where(*conds)) or 0

    par_type = dict(db.execute(select(User.type_utilisateur, func.count()).where(alive)
                               .group_by(User.type_utilisateur)).all())
    par_role = dict(db.execute(select(Role.nom, func.count(User.id)).join(User, User.role_id == Role.id)
                               .where(alive).group_by(Role.nom)).all())
    month = func.date_format(User.created_at, "%Y-%m")
    since = (date.today().replace(day=1) - timedelta(days=335)).replace(day=1)
    months = db.execute(select(month, func.count()).where(alive, User.created_at >= since)
                        .group_by(month).order_by(month)).all()
    return {
        "total": count(alive),
        "actifs": count(alive, User.is_active.is_(True)),
        "inactifs": count(alive, User.is_active.is_(False)),
        "supprimes": count(User.deleted_at.is_not(None)),
        "email_verifies": count(alive, User.email_verified_at.is_not(None)),
        "kyc_verifies": count(alive, User.est_verifie.is_(True)),
        "nouveaux_30_jours": count(alive, User.created_at >= now() - timedelta(days=30)),
        "par_type": {k or "inconnu": v for k, v in par_type.items()},
        "par_role": par_role,
        "inscriptions_par_mois": [{"mois": m, "total": n} for m, n in months],
    }


def activities(db: Session, user: User, params: PageParams):
    """Ce que l'utilisateur a fait, et ce qu'on a fait sur son compte."""
    author = User.__table__.alias("auteur")
    query = (
        select(UserActivity)
        .where(or_(UserActivity.user_id == user.id,
                   and_(UserActivity.item_id == user.id, UserActivity.module.in_(["auth", "users"]))))
        .order_by(UserActivity.id.desc())
    )
    rows, meta = paginate(db, query, params)
    author_ids = {r.user_id for r in rows if r.user_id}
    names = dict(db.execute(select(author.c.id, func.concat(author.c.prenom, " ", author.c.nom))
                            .where(author.c.id.in_(author_ids))).all()) if author_ids else {}
    items = [{"id": r.id, "action": r.action, "module": r.module, "description": r.description,
              "auteur": names.get(r.user_id), "ip_address": r.ip_address, "created_at": r.created_at}
             for r in rows]
    return items, meta


# ───────────────────────── export CSV ─────────────────────────

def _cell(value) -> str:
    """Neutralise l'injection de formules quand le fichier est ouvert dans Excel."""
    text = "" if value is None else str(value)
    return "'" + text if text[:1] in ("=", "+", "-", "@", "\t", "\r") else text


def export_csv(db: Session, **filters) -> Iterator[str]:
    columns = ["uuid", "nom", "prenom", "email", "telephone", "type_utilisateur", "role", "actif",
               "email_verifie", "kyc", "derniere_connexion", "inscrit_le"]
    buffer = io.StringIO()
    writer = csv.writer(buffer, delimiter=";")
    yield "﻿"  # BOM : Excel lit correctement les accents
    writer.writerow(columns)
    query = build_query(**filters).limit(20_000)
    for u in db.scalars(query).unique():
        writer.writerow([_cell(v) for v in (
            u.uuid, u.nom, u.prenom, u.email, u.telephone, u.type_utilisateur, u.role.nom,
            "oui" if u.is_active else "non", "oui" if u.email_verified_at else "non",
            "oui" if u.est_verifie else "non", u.last_login_at, u.created_at)])
        if buffer.tell() > 64_000:
            yield buffer.getvalue()
            buffer.seek(0)
            buffer.truncate()
    yield buffer.getvalue()


# ───────────────────────── garde-fous ─────────────────────────

def _is_admin(user: User) -> bool:
    return user.role is not None and user.role.slug == ADMIN_SLUG


def _other_active_admins(db: Session, user: User) -> int:
    return db.scalar(select(func.count()).select_from(User).join(Role, Role.id == User.role_id).where(
        Role.slug == ADMIN_SLUG, User.is_active.is_(True), User.deleted_at.is_(None), User.id != user.id)) or 0


def _protect_last_admin(db: Session, target: User, action: str) -> None:
    if _is_admin(target) and target.is_active and target.deleted_at is None and not _other_active_admins(db, target):
        raise ApiError(409, "LAST_ADMIN", f"Impossible de {action} le dernier administrateur actif")


def _not_self(actor: User, target: User, action: str) -> None:
    if actor.id == target.id:
        raise ApiError(409, "SELF_ACTION", f"Vous ne pouvez pas {action} votre propre compte")


def _can_manage(actor: User, target: User) -> None:
    if actor.id != target.id and level_of(target) > level_of(actor):
        raise ApiError(403, "FORBIDDEN", "Ce compte a un niveau supérieur au vôtre")


def _role_for_type(db: Session, actor: User, type_utilisateur: str) -> Role:
    """Le rôle découle du type : admin → admin, medecin → medecin, tous les autres → user."""
    slug = role_slug_for_type(type_utilisateur)
    role = db.scalar(select(Role).where(Role.slug == slug))
    if role is None:
        raise ApiError(500, "CONFIG_ERROR", f"Le rôle « {slug} » n'existe pas dans la table roles")
    if slug == ADMIN_SLUG and level_of(actor) < ADMIN_LEVEL:
        raise ApiError(403, "FORBIDDEN", "Seul un administrateur peut créer ou nommer un administrateur")
    return role


def _role_for_id(db: Session, actor: User, role_id: int) -> Role:
    """Rôle choisi explicitement par l'administrateur (rôle dynamique)."""
    role = db.get(Role, role_id)
    if role is None:
        raise validation_error("role_id", "Rôle inconnu")
    if role.slug == ADMIN_SLUG and level_of(actor) < ADMIN_LEVEL:
        raise ApiError(403, "FORBIDDEN", "Seul un administrateur peut nommer un administrateur")
    if actor.role is None or actor.role.slug != ADMIN_SLUG:
        if (role.niveau or 0) > level_of(actor):
            raise ApiError(403, "FORBIDDEN", "Ce rôle a un niveau supérieur au vôtre")
    return role


def _role_for(db: Session, actor: User, role_id: int | None, type_utilisateur: str | None) -> Role:
    if role_id is not None:
        return _role_for_id(db, actor, role_id)
    return _role_for_type(db, actor, type_utilisateur or "patient")


def _check_contact(db: Session, email: str | None, phone: str | None, exclude_id: int | None = None) -> None:
    if email:
        query = select(User.id).where(User.email == email)
        if exclude_id:
            query = query.where(User.id != exclude_id)
        if db.scalar(query):
            raise ApiError(409, "EMAIL_TAKEN", "Cet email est déjà utilisé", [{"field": "email"}])
    if phone and users_by_phone(db, phone, exclude_id=exclude_id):
        raise ApiError(409, "PHONE_TAKEN", "Ce numéro de téléphone est déjà utilisé", [{"field": "telephone"}])


def _log(db: Session, actor: User, target: User, action: str, client: ClientInfo, description: str,
         old: dict | None = None, new: dict | None = None) -> None:
    audit.activity(db, actor.id, action, client, description, module="users", item_id=target.id,
                   item_name=target.email, old_data=old, new_data=new)


# ───────────────────────── écriture ─────────────────────────

def create(db: Session, actor: User, data: AdminUserCreateIn, client: ClientInfo,
           background: BackgroundTasks) -> User:
    email = data.email.strip().lower()
    phone = phone_or_422(data.telephone)
    _check_contact(db, email, phone)
    role = _role_for(db, actor, data.role_id, data.type_utilisateur)
    if data.pays_id and not db.get(Pays, data.pays_id):
        raise validation_error("pays_id", "Pays inconnu")

    fields = data.model_dump(exclude={"email", "telephone", "send_invitation", "role_id"})
    user = User(
        **fields, uuid=str(uuid.uuid4()), email=email, telephone=phone, role_id=role.id,
        # Mot de passe aléatoire inconnu de tous : l'utilisateur choisit le sien avec le code d'invitation.
        password=hash_password(secrets.token_urlsafe(32)), photo="default-avatar.png", is_active=True,
        email_verification_token=None, created_at=now(), updated_at=now(),
    )
    db.add(user)
    db.flush()
    db.refresh(user)
    if data.send_invitation:
        auth_service.issue_reset_code(db, user, client, background, invitation=True)
    _log(db, actor, user, "user_created", client, f"Compte {email} créé par l'administration",
         new={k: v for k, v in data.model_dump().items() if k != "send_invitation"})
    db.commit()
    return user


def update(db: Session, actor: User, target: User, changes: dict, client: ClientInfo,
           background: BackgroundTasks) -> User:
    if target.deleted_at is not None:
        raise ApiError(409, "USER_DELETED", "Restaurez le compte avant de le modifier")
    _can_manage(actor, target)

    if "email" in changes:
        if not changes["email"]:
            raise validation_error("email", "L'email est obligatoire")
        changes["email"] = changes["email"].strip().lower()
    if "telephone" in changes:
        changes["telephone"] = phone_or_422(changes["telephone"])
    for field in ("nom", "prenom", "type_utilisateur"):
        if field in changes and not changes[field]:
            raise validation_error(field, "Ne peut pas être vide")
    _check_contact(db, changes.get("email") if changes.get("email") != target.email else None,
                   changes.get("telephone"), exclude_id=target.id)
    if changes.get("pays_id") and not db.get(Pays, changes["pays_id"]):
        raise validation_error("pays_id", "Pays inconnu")

    # Rôle explicite (rôle dynamique) ou, à défaut, rôle déduit du type.
    role_id = changes.pop("role_id", None)
    role = _role_for(db, actor, role_id, changes.get("type_utilisateur") or target.type_utilisateur)
    role_changed = role.id != target.role_id
    if role_changed and _is_admin(target):
        _not_self(actor, target, "retirer le rôle admin de")
        _protect_last_admin(db, target, "rétrograder")

    email_changed = "email" in changes and changes["email"] != target.email
    old = {k: getattr(target, k) for k in changes}
    for key, value in changes.items():
        setattr(target, key, value)
    if role_changed:
        old["role"] = target.role.slug
        changes["role"] = role.slug
        target.role_id = role.id
        target.role = role
    target.updated_at = now()
    if email_changed:
        target.email_verified_at = None
        target.email_verification_token = secrets.token_urlsafe(32)
    if role_changed:
        # Les droits changent : l'utilisateur doit se reconnecter (sauf l'admin qui fait l'action).
        auth_service.close_all_sessions(db, target.id, "force")
    _log(db, actor, target, "user_updated", client, "Compte modifié par l'administration", old, changes)
    if email_changed:
        auth_service.queue_verification_email(db, target, background)
    db.commit()
    db.refresh(target)
    return target


def set_active(db: Session, actor: User, target: User, active: bool, client: ClientInfo) -> User:
    if target.deleted_at is not None:
        raise ApiError(409, "USER_DELETED", "Restaurez le compte avant de changer son statut")
    _can_manage(actor, target)
    if not active:
        _not_self(actor, target, "désactiver")
        _protect_last_admin(db, target, "désactiver")
        auth_service.close_all_sessions(db, target.id, "force")  # effet immédiat
    target.is_active = active
    target.updated_at = now()
    _log(db, actor, target, "user_activated" if active else "user_deactivated", client,
         "Compte activé" if active else "Compte désactivé, sessions fermées")
    db.commit()
    return target


def set_kyc(db: Session, actor: User, target: User, verified: bool, client: ClientInfo) -> User:
    _can_manage(actor, target)
    target.est_verifie = verified
    target.updated_at = now()
    _log(db, actor, target, "user_kyc", client, "KYC validé" if verified else "KYC retiré")
    db.commit()
    return target


def send_reset(db: Session, actor: User, target: User, client: ClientInfo, background: BackgroundTasks) -> None:
    if target.deleted_at is not None or not target.is_active:
        raise ApiError(409, "USER_INACTIVE", "Le compte doit être actif")
    if auth_service.reset_codes_last_hour(db, target.id) >= get_settings().email_codes_per_hour:
        raise ApiError(429, "TOO_MANY_REQUESTS", "Trop de codes envoyés à ce compte cette heure-ci",
                       {"retry_after_seconds": 3600}, {"Retry-After": "3600"})
    auth_service.issue_reset_code(db, target, client, background)
    _log(db, actor, target, "user_reset_sent", client, "Code de réinitialisation envoyé par l'administration")
    db.commit()


def set_photo(db: Session, actor: User, target: User, raw: bytes | None, client: ClientInfo) -> User:
    _can_manage(actor, target)
    old = target.photo
    target.photo = save_image(raw, "Users", target.id, 2 * 1024 * 1024, 400) if raw is not None \
        else "default-avatar.png"
    target.updated_at = now()
    _log(db, actor, target, "user_photo", client, "Photo modifiée" if raw is not None else "Photo supprimée")
    db.commit()
    delete_file("Users", old)
    return target


def soft_delete(db: Session, actor: User, target: User, client: ClientInfo) -> None:
    if target.deleted_at is not None:
        raise ApiError(409, "USER_DELETED", "Ce compte est déjà supprimé")
    _not_self(actor, target, "supprimer")
    _can_manage(actor, target)
    _protect_last_admin(db, target, "supprimer")
    target.deleted_at = now()
    target.is_active = False
    target.updated_at = now()
    auth_service.close_all_sessions(db, target.id, "force")
    # La photo est gardée pour permettre une restauration complète.
    _log(db, actor, target, "user_deleted", client, "Compte supprimé par l'administration (restaurable)")
    db.commit()


def restore(db: Session, actor: User, target: User, client: ClientInfo) -> User:
    if target.deleted_at is None:
        raise ApiError(409, "NOT_DELETED", "Ce compte n'est pas supprimé")
    if target.email.endswith(ANONYMIZED_DOMAIN):
        raise ApiError(409, "ANONYMIZED", "Ce compte a été supprimé par son titulaire et anonymisé : "
                                          "il ne peut pas être restauré")
    try:
        phone = normalize_phone(target.telephone) if target.telephone else None
    except InvalidPhone:
        phone = None  # ancien numéro mal formé : on ne bloque pas la restauration
    _check_contact(db, target.email, phone, exclude_id=target.id)
    target.deleted_at = None
    target.is_active = True
    target.updated_at = now()
    _log(db, actor, target, "user_restored", client, "Compte restauré")
    db.commit()
    return target


def close_sessions(db: Session, actor: User, target: User, client: ClientInfo) -> int:
    _can_manage(actor, target)
    count = auth_service.close_all_sessions(db, target.id, "force")
    _log(db, actor, target, "user_sessions_closed", client, f"{count} session(s) fermée(s) par l'administration")
    db.commit()
    return count
