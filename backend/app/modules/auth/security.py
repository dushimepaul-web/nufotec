"""Sécurité du compte : changement d'email confirmé, alertes par email.

Alertes envoyées (toujours à l'adresse du compte, avant tout changement) :
- connexion depuis un appareil jamais vu (type, système, navigateur) ;
- mot de passe modifié ou réinitialisé ;
- demande de changement d'email (à l'ancienne adresse) puis changement effectué ;
- suppression du compte."""

import hashlib
import secrets
from datetime import timedelta

from fastapi import BackgroundTasks
from datetime import datetime

from sqlalchemy import DateTime, Integer, String, func, select
from sqlalchemy.orm import Mapped, Session, mapped_column

from app.core import audit
from app.core.client import ClientInfo
from app.core.config import get_settings
from app.core.database import Base
from app.core.helpers import minutes_ago, now
from app.core.mailer import deliver_pending, queue_email, simple_email
from app.core.responses import ApiError
from app.core.security import verify_password
from app.models import User, UserSession

CHANGE_HOURS = 24


class EmailChange(Base):
    __tablename__ = "email_changes"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    user_id: Mapped[int] = mapped_column(Integer)
    ancien_email: Mapped[str] = mapped_column(String(100))
    nouvel_email: Mapped[str] = mapped_column(String(100))
    token_hash: Mapped[str] = mapped_column(String(64), unique=True)
    expires_at: Mapped[datetime] = mapped_column(DateTime)
    used_at: Mapped[datetime | None] = mapped_column(DateTime)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    created_at: Mapped[datetime] = mapped_column(DateTime)


def _hash(token: str) -> str:
    return hashlib.sha256(token.encode()).hexdigest()


def _alert(db: Session, to: str, prenom: str, subject: str, lines: list[str], background: BackgroundTasks | None) -> None:
    text, html = simple_email(prenom, subject, lines + [
        "Si ce n'est pas vous, changez immédiatement votre mot de passe et contactez-nous."])
    queue_email(db, to, f"Nufotec – {subject}", text, html)
    if background is not None:
        background.add_task(deliver_pending)


def _when(client: ClientInfo | None) -> str:
    where = f" depuis l'adresse IP {client.ip}" if client and client.ip else ""
    return f"le {now():%d/%m/%Y à %H:%M} (heure du Burundi){where}"


# ───────────────────────── alertes ─────────────────────────

def alert_new_device(db: Session, user: User, session_id: int, client: ClientInfo, background: BackgroundTasks) -> bool:
    """Après une connexion : alerte si cet appareil (type + système + navigateur) n'a jamais été utilisé."""
    current = db.get(UserSession, session_id)
    if current is None:
        return False
    previous = select(func.count()).select_from(UserSession).where(UserSession.user_id == user.id,
                                                                    UserSession.id != current.id)
    if not db.scalar(previous):
        return False  # première connexion du compte : rien d'anormal
    seen = db.scalar(previous.where(UserSession.device_type == current.device_type,
                                    UserSession.platform.is_(current.platform) if current.platform is None
                                    else UserSession.platform == current.platform,
                                    UserSession.browser.is_(current.browser) if current.browser is None
                                    else UserSession.browser == current.browser))
    if seen:
        return False
    device = " / ".join(x for x in (current.platform, current.browser) if x) or current.device_type or "appareil inconnu"
    _alert(db, user.email, user.prenom, "Nouvelle connexion à votre compte",
           [f"Une connexion à votre compte a eu lieu {_when(client)} sur un nouvel appareil : {device}.",
            "Vous pouvez voir et déconnecter vos appareils dans l'application (Mes appareils)."], background)
    db.commit()
    return True


def alert_password_changed(db: Session, user: User, client: ClientInfo, background: BackgroundTasks, reset: bool) -> None:
    what = "réinitialisé (code reçu par email)" if reset else "modifié"
    _alert(db, user.email, user.prenom, "Votre mot de passe a été changé",
           [f"Le mot de passe de votre compte a été {what} {_when(client)}.",
            "Vos autres appareils ont été déconnectés."], background)
    db.commit()


def alert_account_deleted(db: Session, email: str, prenom: str, client: ClientInfo, background: BackgroundTasks) -> None:
    text, html = simple_email(prenom, "Votre compte a été supprimé",
                              [f"Votre compte Nufotec a été supprimé {_when(client)}, à votre demande.",
                               "Vos données personnelles ont été effacées. Merci d'avoir utilisé Nufotec."])
    queue_email(db, email, "Nufotec – Compte supprimé", text, html)
    background.add_task(deliver_pending)
    db.commit()


# ───────────────────────── changement d'email ─────────────────────────

def request_email_change(db: Session, user: User, new_email: str, password: str, client: ClientInfo,
                         background: BackgroundTasks) -> None:
    if not verify_password(password, user.password):
        raise ApiError(400, "WRONG_PASSWORD", "Mot de passe incorrect", [{"field": "mot_de_passe"}])
    new_email = new_email.strip().lower()
    if new_email == user.email:
        raise ApiError(409, "SAME_EMAIL", "C'est déjà votre adresse email")
    if db.scalar(select(User.id).where(User.email == new_email)):
        raise ApiError(409, "EMAIL_TAKEN", "Cet email est déjà utilisé", [{"field": "nouvel_email"}])
    recent = db.scalar(select(func.count()).select_from(EmailChange).where(
        EmailChange.user_id == user.id, EmailChange.created_at > minutes_ago(60))) or 0
    if recent >= 3:
        raise ApiError(429, "TOO_MANY_REQUESTS", "Trop de demandes. Réessayez dans une heure.",
                       {"retry_after_seconds": 3600}, {"Retry-After": "3600"})
    token = secrets.token_urlsafe(32)
    db.add(EmailChange(user_id=user.id, ancien_email=user.email, nouvel_email=new_email, token_hash=_hash(token),
                       expires_at=now() + timedelta(hours=CHANGE_HOURS), ip_address=client.ip, created_at=now()))
    link = f"{get_settings().public_api_url.rstrip('/')}/api/v1/auth/email/confirmer/{token}"
    text, html = simple_email(user.prenom, "Confirmez votre nouvelle adresse email",
                              ["Vous avez demandé à utiliser cette adresse pour votre compte Nufotec.",
                               f"Le lien est valable {CHANGE_HOURS} heures."], ("Confirmer cette adresse", link))
    queue_email(db, new_email, "Nufotec – Confirmez votre nouvelle adresse email", text, html)
    _alert(db, user.email, user.prenom, "Demande de changement d'adresse email",
           [f"Une demande de changement de l'adresse de votre compte vers {new_email} a été faite {_when(client)}.",
            "Le changement ne sera effectif qu'après confirmation depuis la nouvelle adresse."], background)
    audit.activity(db, user.id, "email_change_requested", client, f"→ {new_email}")
    db.commit()


def confirm_email_change(db: Session, token: str, client: ClientInfo, background: BackgroundTasks) -> User:
    change = db.scalar(select(EmailChange).where(EmailChange.token_hash == _hash(token)))
    if change is None or change.used_at is not None or change.expires_at < now():
        raise ApiError(400, "INVALID_TOKEN", "Lien invalide, expiré ou déjà utilisé")
    user = db.get(User, change.user_id)
    if user is None or user.deleted_at is not None or user.email != change.ancien_email:
        raise ApiError(400, "INVALID_TOKEN", "Ce lien n'est plus valable")
    if db.scalar(select(User.id).where(User.email == change.nouvel_email, User.id != user.id)):
        raise ApiError(409, "EMAIL_TAKEN", "Cette adresse est désormais utilisée par un autre compte")
    old = user.email
    user.email, user.email_verified_at, user.updated_at = change.nouvel_email, now(), now()
    change.used_at = now()
    _alert(db, old, user.prenom, "Votre adresse email a été changée",
           [f"L'adresse de votre compte est désormais {change.nouvel_email} (changement confirmé {_when(client)}).",
            "Vous recevrez dorénavant nos emails à cette nouvelle adresse."], background)
    audit.activity(db, user.id, "email_changed", client, f"{old} → {change.nouvel_email}")
    db.commit()
    return user


# ───────────────────────── purge manuelle (admin) ─────────────────────────

def purge_deleted_accounts(db: Session, admin: User, days: int, client: ClientInfo | None) -> int:
    """Anonymise les comptes supprimés par l'administration depuis plus de `days` jours (action manuelle).
    La ligne reste (consultations, commandes liées) mais ne contient plus aucune donnée personnelle."""
    from app.core.security import hash_password
    from app.modules.auth.service import close_all_sessions

    limit = now() - timedelta(days=days)
    rows = list(db.scalars(select(User).where(User.deleted_at.is_not(None), User.deleted_at < limit,
                                              ~User.email.like("deleted+%"))))
    for u in rows:
        u.email = f"deleted+{u.uuid}@deleted.nufotec.invalid"
        u.password = hash_password(secrets.token_urlsafe(32))
        u.nom, u.prenom = "Utilisateur", "Supprimé"
        u.telephone = u.date_naissance = u.genre = None
        u.nom_entreprise = u.secteur_activite = u.numero_registre_commerce = None
        u.photo, u.email_verification_token, u.last_login_ip = "default-avatar.png", None, None
        u.two_factor_secret, u.two_factor_enabled = None, False
        close_all_sessions(db, u.id, "force")
    audit.activity(db, admin.id if admin else None, "accounts_purged", client,
                   f"{len(rows)} compte(s) anonymisé(s) (supprimés depuis plus de {days} jours)", module="users")
    db.commit()
    return len(rows)
