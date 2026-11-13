"""Logique d'authentification.

Reprise de modules/Auth (PHP) : connexion par email OU téléphone, compte actif et non supprimé,
rôle par défaut « patient » puis « user ». Ajouts : anti force brute, détection de vol de jeton,
réinitialisation par code, vérification d'email, gestion des appareils, suppression de compte."""

import hashlib
import hmac
import secrets
import uuid
from datetime import datetime, timedelta

import jwt
from fastapi import BackgroundTasks
from sqlalchemy import func, literal_column, or_, select, update
from sqlalchemy.orm import Session

from app.core import audit
from app.core.client import ClientInfo
from app.core.config import get_settings
from app.core.mailer import reset_code_email, send_email, verification_email
from app.core.passwords import password_problems
from app.core.phone import InvalidPhone, looks_like_phone, normalize_phone, sql_clean_phone
from app.core.responses import ApiError
from app.core.security import (
    DUMMY_HASH,
    REFRESH,
    create_access_token,
    create_refresh_token,
    decode_token,
    hash_password,
    verify_password,
)
from app.core.uploads import delete_file, save_image
from app.models import Log, PasswordReset, Pays, Role, User, UserSession
from app.modules.auth.schemas import RegisterIn, TokensOut

DISABLED_MESSAGE = (
    "Votre compte est désactivé. Veuillez contacter l'administrateur sur nufotecburundi2026@gmail.com."
)
INVALID_CREDENTIALS = ApiError(401, "INVALID_CREDENTIALS", "Identifiants incorrects")


# ───────────────────────── outils ─────────────────────────

def _now() -> datetime:
    # Heure locale sans fuseau, comme date('Y-m-d H:i:s') côté PHP et NOW() côté MySQL.
    return datetime.now().replace(microsecond=0)


def _local(dt: datetime) -> datetime:
    return dt.astimezone().replace(tzinfo=None, microsecond=0)


def _validation(field: str, message: str, code: str = "VALIDATION_ERROR") -> ApiError:
    return ApiError(422, code, message, [{"field": field, "message": message}])


def _check_password(password: str, user_email: str, names: tuple[str, ...], field: str = "password") -> None:
    problems = password_problems(password, user_email, names)
    if problems:
        message = "Mot de passe trop faible : " + ", ".join(problems)
        raise _validation(field, message, "WEAK_PASSWORD")


def _phone_or_422(raw: str | None, field: str = "telephone") -> str | None:
    if not raw or not raw.strip():
        return None
    try:
        return normalize_phone(raw)
    except InvalidPhone as e:
        raise _validation(field, str(e)) from None


def _users_by_phone(db: Session, e164: str, exclude_id: int | None = None) -> list[User]:
    query = select(User).where(sql_clean_phone(User.telephone) == e164, User.deleted_at.is_(None))
    if exclude_id:
        query = query.where(User.id != exclude_id)
    return list(db.scalars(query).unique())


def _minutes_ago(minutes: int):
    return func.date_sub(func.now(), literal_column(f"INTERVAL {int(minutes)} MINUTE"))


def _count_logs(db: Session, action: str, since_minutes: int, **filters) -> int:
    query = select(func.count()).select_from(Log).where(Log.action == action,
                                                        Log.created_at > _minutes_ago(since_minutes))
    for column, value in filters.items():
        query = query.where(getattr(Log, column) == value)
    return db.scalar(query) or 0


def _too_many(message: str, retry_after: int) -> ApiError:
    retry_after = max(retry_after, 1)
    return ApiError(429, "TOO_MANY_REQUESTS", message, {"retry_after_seconds": retry_after},
                    {"Retry-After": str(retry_after)})


def _device(user_agent: str) -> tuple[str, str | None, str | None]:
    ua = user_agent.lower()
    if any(b in ua for b in ("bot", "spider", "crawler")):
        device = "bot"
    elif "ipad" in ua or "tablet" in ua:
        device = "tablet"
    elif any(m in ua for m in ("mobile", "android", "iphone", "okhttp", "expo", "dart", "cfnetwork")):
        device = "mobile"
    elif ua:
        device = "desktop"
    else:
        device = "unknown"
    platform = next((p for k, p in (("android", "Android"), ("iphone", "iOS"), ("ipad", "iOS"),
                                    ("cfnetwork", "iOS"), ("windows", "Windows"), ("mac os", "macOS"),
                                    ("linux", "Linux")) if k in ua), None)
    browser = next((b for k, b in (("edg", "Edge"), ("chrome", "Chrome"), ("firefox", "Firefox"),
                                   ("safari", "Safari"), ("okhttp", "App Android"), ("cfnetwork", "App iOS"),
                                   ("expo", "App Expo")) if k in ua), None)
    return device, platform, browser


def _default_role_id(db: Session) -> int:
    for slug in ("patient", "user"):
        role_id = db.scalar(select(Role.id).where(Role.slug == slug))
        if role_id:
            return role_id
    raise ApiError(500, "CONFIG_ERROR", "Aucun rôle par défaut (patient/user) n'est défini")


def _close(session: UserSession, reason: str) -> None:
    session.is_active = False
    session.logout_time = _now()
    session.logout_reason = reason


def _close_all(db: Session, user_id: int, reason: str, except_id: int | None = None) -> int:
    query = update(UserSession).where(UserSession.user_id == user_id, UserSession.is_active.is_(True))
    if except_id:
        query = query.where(UserSession.id != except_id)
    result = db.execute(query.values(is_active=False, logout_time=_now(), logout_reason=reason))
    return result.rowcount or 0


# ───────────────────────── inscription ─────────────────────────

def register(db: Session, data: RegisterIn, client: ClientInfo, background: BackgroundTasks) -> User:
    email = data.email.strip().lower()
    phone = _phone_or_422(data.telephone)
    if db.scalar(select(User.id).where(User.email == email)):
        raise ApiError(409, "EMAIL_TAKEN", "Cet email est déjà utilisé", [{"field": "email"}])
    if phone and _users_by_phone(db, phone):
        raise ApiError(409, "PHONE_TAKEN", "Ce numéro de téléphone est déjà utilisé", [{"field": "telephone"}])
    if data.pays_id and not db.get(Pays, data.pays_id):
        raise _validation("pays_id", "Pays inconnu")
    _check_password(data.password, email, (data.nom, data.prenom))

    user = User(
        uuid=str(uuid.uuid4()), email=email, password=hash_password(data.password),
        nom=data.nom, prenom=data.prenom, telephone=phone, pays_id=data.pays_id,
        date_naissance=data.date_naissance, genre=data.genre, photo="default-avatar.png",
        role_id=_default_role_id(db), type_utilisateur=data.type_utilisateur,
        nom_entreprise=data.nom_entreprise, is_active=True,
        email_verification_token=secrets.token_urlsafe(32), created_at=_now(), updated_at=_now(),
    )
    db.add(user)
    db.flush()
    audit.activity(db, user.id, "register", client, "Inscription via l'API",
                   {"type_utilisateur": user.type_utilisateur})
    db.commit()
    db.refresh(user)
    _queue_verification_email(user, background)
    return user


# ───────────────────────── connexion ─────────────────────────

def _login_key(login: str) -> str:
    login = login.strip()
    if looks_like_phone(login):
        try:
            return normalize_phone(login)
        except InvalidPhone:
            pass
    return login.lower()


def _check_lockout(db: Session, key: str, ip: str | None) -> None:
    s = get_settings()
    window = s.login_lockout_minutes
    last_success = db.scalar(select(func.max(Log.created_at)).where(
        Log.action == "auth.login_success", Log.description == key, Log.created_at > _minutes_ago(window)))
    account_q = select(func.count(), func.min(Log.created_at)).where(
        Log.action == "auth.login_failed", Log.description == key, Log.created_at > _minutes_ago(window))
    if last_success:
        account_q = account_q.where(Log.created_at > last_success)
    failures, oldest = db.execute(account_q).one()
    if failures >= s.login_max_failures_per_account:
        retry = window * 60 - int((datetime.now() - oldest).total_seconds()) if oldest else window * 60
        raise _too_many(f"Trop de tentatives. Réessayez dans {max(retry // 60, 1)} minute(s).", retry)
    if ip and _count_logs(db, "auth.login_failed", window, ip_address=ip) >= s.login_max_failures_per_ip:
        raise _too_many("Trop de tentatives depuis ce réseau. Réessayez plus tard.", window * 60)


def _failed(db: Session, key: str, client: ClientInfo, user_id: int | None) -> ApiError:
    audit.security_log(db, "auth.login_failed", client, key, user_id, "warning")
    db.commit()
    return INVALID_CREDENTIALS


def authenticate(db: Session, login: str, password: str, client: ClientInfo) -> User:
    key = _login_key(login)
    _check_lockout(db, key, client.ip)

    if key.startswith("+"):
        candidates = _users_by_phone(db, key)
    else:
        candidates = list(db.scalars(select(User).where(User.email == key, User.deleted_at.is_(None))).unique())

    if not candidates:
        verify_password(password, DUMMY_HASH)  # même durée que pour un vrai compte
        raise _failed(db, key, client, None)

    # Un même numéro peut appartenir à plusieurs comptes (données existantes) :
    # on garde celui dont le mot de passe correspond.
    matches = [u for u in candidates if verify_password(password, u.password)]
    if not matches:
        raise _failed(db, key, client, candidates[0].id if len(candidates) == 1 else None)
    if len(matches) > 1:
        raise ApiError(409, "PHONE_AMBIGUOUS",
                       "Ce numéro est lié à plusieurs comptes : connectez-vous avec votre email.")
    user = matches[0]
    if not user.is_active:
        raise ApiError(403, "ACCOUNT_DISABLED", DISABLED_MESSAGE)

    user.last_login_at = _now()
    user.last_login_ip = client.ip
    audit.security_log(db, "auth.login_success", client, key, user.id)
    audit.activity(db, user.id, "login", client, "Connexion via l'API")
    db.commit()
    return user


# ───────────────────────── sessions et jetons ─────────────────────────

def _issue(db: Session, user: User, session: UserSession) -> TokensOut:
    refresh, jti, refresh_exp = create_refresh_token(user.uuid, session.id)
    session.session_id = jti
    session.last_activity = _now()
    session.expiry_time = _local(refresh_exp)
    db.commit()
    access, access_exp = create_access_token(user.uuid, jti)
    return TokensOut(access_token=access, access_expires_at=access_exp,
                     refresh_token=refresh, refresh_expires_at=refresh_exp)


def open_session(db: Session, user: User, client: ClientInfo) -> TokensOut:
    device, platform, browser = _device(client.user_agent)
    now = _now()
    session = UserSession(
        user_id=user.id, session_id="pending-" + uuid.uuid4().hex, ip_address=client.ip,
        user_agent=client.user_agent or None, device_type=device, platform=platform, browser=browser,
        login_time=now, last_activity=now, expiry_time=now, is_active=True,
    )
    db.add(session)
    db.flush()
    return _issue(db, user, session)


def refresh_session(db: Session, refresh_token: str, client: ClientInfo) -> TokensOut:
    """Rotation : chaque rafraîchissement émet un nouveau jeton et invalide l'ancien.
    Un ancien jeton présenté à nouveau signale un vol : la session est fermée."""
    try:
        payload = decode_token(refresh_token, REFRESH)
    except jwt.ExpiredSignatureError:
        raise ApiError(401, "TOKEN_EXPIRED", "Session expirée, reconnectez-vous") from None
    except jwt.InvalidTokenError:
        raise ApiError(401, "UNAUTHENTICATED", "Jeton invalide") from None

    session = db.scalar(select(UserSession).where(UserSession.session_id == payload["jti"]))
    if session is None:
        previous = db.get(UserSession, payload.get("ses") or 0)
        if previous is not None and previous.is_active:
            grace = get_settings().refresh_reuse_grace_seconds
            if (datetime.now() - previous.last_activity).total_seconds() <= grace:
                # Deux rafraîchissements simultanés de la même app : pas un vol.
                raise ApiError(401, "TOKEN_ROTATED", "Jeton déjà renouvelé, utilisez le plus récent")
            _close(previous, "force")
            audit.security_log(db, "auth.refresh_reuse", client, f"session={previous.id}",
                               previous.user_id, "critical")
            audit.activity(db, previous.user_id, "session_revoked", client,
                           "Session fermée : réutilisation d'un ancien jeton (vol possible)")
            db.commit()
            raise ApiError(401, "TOKEN_REUSED", "Session fermée par sécurité, reconnectez-vous")
        raise ApiError(401, "UNAUTHENTICATED", "Session fermée, reconnectez-vous")

    if not session.is_active:
        raise ApiError(401, "UNAUTHENTICATED", "Session fermée, reconnectez-vous")
    user = db.get(User, session.user_id)
    if user is None or user.deleted_at is not None or user.uuid != payload["sub"]:
        raise ApiError(401, "UNAUTHENTICATED", "Compte introuvable")
    if not user.is_active:
        _close(session, "force")
        db.commit()
        raise ApiError(403, "ACCOUNT_DISABLED", DISABLED_MESSAGE)
    if client.ip:
        session.ip_address = client.ip
    return _issue(db, user, session)


def logout(db: Session, session: UserSession, client: ClientInfo) -> None:
    _close(session, "manual")
    audit.activity(db, session.user_id, "logout", client, "Déconnexion")
    db.commit()


def list_sessions(db: Session, user: User, current: UserSession) -> list[dict]:
    rows = db.scalars(
        select(UserSession)
        .where(UserSession.user_id == user.id, UserSession.is_active.is_(True),
               UserSession.expiry_time > _now())
        .order_by(UserSession.last_activity.desc())
    )
    return [{
        "id": s.id, "device_type": s.device_type, "platform": s.platform, "browser": s.browser,
        "ip_address": s.ip_address, "login_time": s.login_time, "last_activity": s.last_activity,
        "current": s.id == current.id,
    } for s in rows]


def revoke_session(db: Session, user: User, session_id: int, client: ClientInfo) -> None:
    session = db.get(UserSession, session_id)
    if session is None or session.user_id != user.id or not session.is_active:
        raise ApiError(404, "NOT_FOUND", "Session introuvable")
    _close(session, "manual")
    audit.activity(db, user.id, "session_revoked", client, f"Session {session_id} fermée")
    db.commit()


def revoke_other_sessions(db: Session, user: User, current: UserSession, client: ClientInfo) -> int:
    count = _close_all(db, user.id, "manual", except_id=current.id)
    audit.activity(db, user.id, "sessions_revoked", client, f"{count} autre(s) session(s) fermée(s)")
    db.commit()
    return count


# ───────────────────────── mot de passe ─────────────────────────

def change_password(db: Session, user: User, current_session: UserSession, current: str, new: str,
                    client: ClientInfo) -> None:
    if not verify_password(current, user.password):
        raise ApiError(400, "WRONG_PASSWORD", "Le mot de passe actuel est incorrect",
                       [{"field": "current_password"}])
    if verify_password(new, user.password):
        raise _validation("new_password", "Le nouveau mot de passe doit être différent de l'actuel")
    _check_password(new, user.email, (user.nom, user.prenom), "new_password")
    user.password = hash_password(new)
    user.updated_at = _now()
    # Les autres appareils sont déconnectés ; la session courante reste ouverte.
    _close_all(db, user.id, "force", except_id=current_session.id)
    audit.activity(db, user.id, "password_changed", client, "Mot de passe modifié")
    db.commit()


def _code_hash(user_id: int, code: str) -> str:
    key = get_settings().jwt_secret.encode()
    return hmac.new(key, f"{user_id}:{code}".encode(), hashlib.sha256).hexdigest()


def forgot_password(db: Session, email: str, client: ClientInfo, background: BackgroundTasks) -> None:
    """Répond toujours la même chose, que le compte existe ou non (pas d'énumération des comptes)."""
    s = get_settings()
    if client.ip and _count_logs(db, "auth.reset_requested", 60, ip_address=client.ip) >= 10:
        raise _too_many("Trop de demandes depuis ce réseau. Réessayez plus tard.", 3600)
    email = email.strip().lower()
    audit.security_log(db, "auth.reset_requested", client, email)
    user = db.scalar(select(User).where(User.email == email, User.deleted_at.is_(None)))
    if user is None or not user.is_active:
        db.commit()
        return
    recent = db.scalar(select(func.count()).select_from(PasswordReset).where(
        PasswordReset.user_id == user.id, PasswordReset.created_at > _minutes_ago(60)))
    if recent >= s.email_codes_per_hour:
        db.commit()
        return

    # Un seul code valable à la fois.
    db.execute(update(PasswordReset).where(PasswordReset.user_id == user.id, PasswordReset.used_at.is_(None))
               .values(used_at=_now()))
    code = f"{secrets.randbelow(1_000_000):06d}"
    db.add(PasswordReset(user_id=user.id, code_hash=_code_hash(user.id, code), ip_address=client.ip,
                         expires_at=_now() + timedelta(minutes=s.reset_code_minutes), attempts=0))
    audit.activity(db, user.id, "password_reset_requested", client, "Code de réinitialisation envoyé")
    db.commit()
    subject, text, html = reset_code_email(user.prenom, code, s.reset_code_minutes)
    background.add_task(send_email, user.email, subject, text, html)


def reset_password(db: Session, email: str, code: str, new_password: str, client: ClientInfo) -> None:
    s = get_settings()
    invalid = ApiError(400, "INVALID_CODE", "Code invalide ou expiré", [{"field": "code"}])
    user = db.scalar(select(User).where(User.email == email.strip().lower(), User.deleted_at.is_(None)))
    if user is None or not user.is_active:
        raise invalid
    reset = db.scalar(
        select(PasswordReset)
        .where(PasswordReset.user_id == user.id, PasswordReset.used_at.is_(None),
               PasswordReset.expires_at > _now())
        .order_by(PasswordReset.id.desc())
    )
    if reset is None or reset.attempts >= s.reset_code_max_attempts:
        raise invalid
    if not hmac.compare_digest(reset.code_hash, _code_hash(user.id, code)):
        reset.attempts += 1
        if reset.attempts >= s.reset_code_max_attempts:
            reset.used_at = _now()  # code grillé : il faut en redemander un
        audit.security_log(db, "auth.reset_code_failed", client, user.email, user.id, "warning")
        db.commit()
        remaining = max(s.reset_code_max_attempts - reset.attempts, 0)
        raise ApiError(400, "INVALID_CODE", "Code invalide ou expiré", {"remaining_attempts": remaining})

    _check_password(new_password, user.email, (user.nom, user.prenom), "new_password")
    user.password = hash_password(new_password)
    user.updated_at = _now()
    if user.email_verified_at is None:
        user.email_verified_at = _now()  # recevoir le code prouve que l'email lui appartient
    reset.used_at = _now()
    _close_all(db, user.id, "force")
    audit.activity(db, user.id, "password_reset", client, "Mot de passe réinitialisé par code email")
    db.commit()


# ───────────────────────── vérification d'email ─────────────────────────

def _queue_verification_email(user: User, background: BackgroundTasks) -> None:
    link = f"{get_settings().public_api_url.rstrip('/')}/api/v1/auth/verify-email/{user.email_verification_token}"
    subject, text, html = verification_email(user.prenom, link)
    background.add_task(send_email, user.email, subject, text, html)


def resend_verification(db: Session, user: User, client: ClientInfo, background: BackgroundTasks) -> None:
    if user.email_verified_at is not None:
        raise ApiError(400, "ALREADY_VERIFIED", "Votre email est déjà vérifié")
    if _count_logs(db, "auth.verification_sent", 60, user_id=user.id) >= get_settings().email_codes_per_hour:
        raise _too_many("Trop d'emails envoyés. Réessayez dans une heure.", 3600)
    user.email_verification_token = secrets.token_urlsafe(32)
    audit.security_log(db, "auth.verification_sent", client, user.email, user.id)
    db.commit()
    _queue_verification_email(user, background)


def verify_email(db: Session, token: str, client: ClientInfo) -> User:
    user = db.scalar(select(User).where(User.email_verification_token == token, User.deleted_at.is_(None)))
    if user is None:
        raise ApiError(400, "INVALID_TOKEN", "Lien de vérification invalide ou déjà utilisé")
    user.email_verified_at = _now()
    user.email_verification_token = None
    audit.activity(db, user.id, "email_verified", client, "Adresse email vérifiée")
    db.commit()
    return user


# ───────────────────────── profil ─────────────────────────

def update_profile(db: Session, user: User, changes: dict, client: ClientInfo) -> User:
    if "telephone" in changes:
        changes["telephone"] = _phone_or_422(changes["telephone"])
        if changes["telephone"] and _users_by_phone(db, changes["telephone"], exclude_id=user.id):
            raise ApiError(409, "PHONE_TAKEN", "Ce numéro de téléphone est déjà utilisé", [{"field": "telephone"}])
    if changes.get("pays_id") and not db.get(Pays, changes["pays_id"]):
        raise _validation("pays_id", "Pays inconnu")
    for field in ("nom", "prenom"):
        if field in changes and not changes[field]:
            raise _validation(field, "Ne peut pas être vide")
    old = {k: getattr(user, k) for k in changes}
    for key, value in changes.items():
        setattr(user, key, value)
    user.updated_at = _now()
    audit.activity(db, user.id, "profile_updated", client, "Profil modifié",
                   {"avant": old, "apres": changes})
    db.commit()
    db.refresh(user)
    return user


def update_photo(db: Session, user: User, raw: bytes, client: ClientInfo) -> User:
    name = save_image(raw, "Users", user.id, max_bytes=2 * 1024 * 1024, max_side=400)
    old = user.photo
    user.photo = name
    user.updated_at = _now()
    audit.activity(db, user.id, "photo_updated", client, "Photo de profil modifiée")
    db.commit()
    delete_file("Users", old)
    return user


def delete_photo(db: Session, user: User, client: ClientInfo) -> User:
    old = user.photo
    user.photo = "default-avatar.png"
    user.updated_at = _now()
    audit.activity(db, user.id, "photo_deleted", client, "Photo de profil supprimée")
    db.commit()
    delete_file("Users", old)
    return user


def delete_account(db: Session, user: User, password: str, reason: str | None, client: ClientInfo) -> None:
    """Suppression exigée par Google Play / App Store : compte anonymisé et désactivé
    (la ligne reste pour les consultations, commandes… qui y sont liées)."""
    if not verify_password(password, user.password):
        raise ApiError(400, "WRONG_PASSWORD", "Mot de passe incorrect", [{"field": "password"}])
    old_photo = user.photo
    user.deleted_at = _now()
    user.is_active = False
    user.email = f"deleted+{user.uuid}@deleted.nufotec.invalid"
    user.password = hash_password(secrets.token_urlsafe(32))
    user.nom, user.prenom = "Utilisateur", "Supprimé"
    user.telephone = user.date_naissance = user.genre = None
    user.nom_entreprise = user.secteur_activite = user.numero_registre_commerce = None
    user.photo = "default-avatar.png"
    user.email_verification_token = user.two_factor_secret = None
    user.two_factor_enabled = False
    user.last_login_ip = None
    _close_all(db, user.id, "force")
    audit.activity(db, user.id, "account_deleted", client, reason or "Compte supprimé par l'utilisateur")
    db.commit()
    delete_file("Users", old_photo)
