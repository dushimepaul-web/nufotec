from html import escape

from fastapi import APIRouter, BackgroundTasks, Depends, File, UploadFile, status
from fastapi.responses import HTMLResponse
from pydantic import BaseModel, EmailStr, Field
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core import push
from app.core.client import ClientInfo, get_client
from app.core.database import get_db
from app.core.deps import get_current_session, get_current_user
from app.core.responses import ApiError, Envelope, ok
from app.models import User, UserSession
from app.modules.auth import security, service
from app.modules.auth.schemas import (
    AuthOut,
    ChangePasswordIn,
    DeleteAccountIn,
    ForgotPasswordIn,
    LoginIn,
    ProfileUpdateIn,
    PushDeviceIn,
    PushDeviceOut,
    PushTokenIn,
    RefreshIn,
    RegisterIn,
    ResetPasswordIn,
    SessionOut,
    TokensOut,
    UserOut,
    VerifyEmailIn,
)

router = APIRouter(prefix="/auth", tags=["Authentification"])

DB = Depends(get_db)
CLIENT = Depends(get_client)
USER = Depends(get_current_user)
SESSION = Depends(get_current_session)


# ── Inscription et connexion ──

@router.post("/register", status_code=status.HTTP_201_CREATED, response_model=Envelope[AuthOut],
             summary="Créer un compte (connecte directement et envoie l'email de vérification)")
def register(body: RegisterIn, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT):
    user = service.register(db, body, client, background)
    tokens = service.open_session(db, user, client)
    return ok(AuthOut(user=UserOut.of(user), tokens=tokens), "Compte créé avec succès")


@router.post("/login", response_model=Envelope[AuthOut], summary="Se connecter (email ou téléphone)",
             responses={429: {"description": "Trop de tentatives (en-tête Retry-After)"}})
def login(body: LoginIn, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT):
    user = service.authenticate(db, body.login, body.password, client)
    tokens = service.open_session(db, user, client)
    session_id = db.scalar(select(UserSession.id).where(UserSession.user_id == user.id).order_by(UserSession.id.desc()))
    security.alert_new_device(db, user, session_id, client, background)
    return ok(AuthOut(user=UserOut.of(user), tokens=tokens), "Connexion réussie")


@router.post("/refresh", response_model=Envelope[TokensOut], summary="Renouveler les jetons (rotation)")
def refresh(body: RefreshIn, db: Session = DB, client: ClientInfo = CLIENT):
    return ok(service.refresh_session(db, body.refresh_token, client))


@router.post("/logout", response_model=Envelope[None], summary="Fermer la session courante")
def logout(session: UserSession = SESSION, db: Session = DB, client: ClientInfo = CLIENT):
    service.logout(db, session, client)
    return ok(message="Déconnexion réussie")


# ── Mot de passe ──

@router.post("/password/forgot", response_model=Envelope[None],
             summary="Demander un code de réinitialisation à 6 chiffres par email")
def forgot_password(body: ForgotPasswordIn, background: BackgroundTasks, db: Session = DB,
                    client: ClientInfo = CLIENT):
    service.forgot_password(db, body.email, client, background)
    return ok(message="Si un compte existe pour cet email, un code de réinitialisation vient d'être envoyé.")


@router.post("/password/reset", response_model=Envelope[None],
             summary="Choisir un nouveau mot de passe avec le code reçu (déconnecte tous les appareils)")
def reset_password(body: ResetPasswordIn, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT):
    service.reset_password(db, body.email, body.code, body.new_password, client)
    user = db.scalar(select(User).where(User.email == body.email.strip().lower()))
    if user is not None:
        security.alert_password_changed(db, user, client, background, reset=True)
    return ok(message="Mot de passe réinitialisé. Vous pouvez vous connecter.")


@router.post("/change-password", response_model=Envelope[None],
             summary="Changer son mot de passe (déconnecte les autres appareils)")
def change_password(body: ChangePasswordIn, background: BackgroundTasks, user: User = USER,
                    session: UserSession = SESSION, db: Session = DB, client: ClientInfo = CLIENT):
    service.change_password(db, user, session, body.current_password, body.new_password, client)
    security.alert_password_changed(db, user, client, background, reset=False)
    return ok(message="Mot de passe modifié")


# ── Vérification d'email ──

@router.post("/verify-email", response_model=Envelope[UserOut], summary="Vérifier son email (depuis l'app)")
def verify_email(body: VerifyEmailIn, db: Session = DB, client: ClientInfo = CLIENT):
    return ok(UserOut.of(service.verify_email(db, body.token, client)), "Adresse email vérifiée")


@router.get("/verify-email/{token}", response_class=HTMLResponse, include_in_schema=False)
def verify_email_link(token: str, db: Session = DB, client: ClientInfo = CLIENT):
    """Lien cliqué dans l'email : renvoie une petite page lisible."""
    try:
        user = service.verify_email(db, token, client)
        title, text, color = "Email confirmé", f"Merci {escape(user.prenom)}, votre adresse est vérifiée.", "#0b6e4f"
    except ApiError as e:
        title, text, color = "Lien invalide", escape(e.message), "#b42318"
    return HTMLResponse(f"""<!doctype html><html lang="fr"><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title>{title} – Nufotec</title>
<body style="font-family:Arial,sans-serif;display:grid;place-items:center;min-height:90vh;color:#1f2933">
<div style="text-align:center;max-width:420px"><h1 style="color:{color}">{title}</h1><p>{text}</p>
<p>Vous pouvez retourner dans l'application Nufotec.</p></div></body></html>""")


@router.post("/resend-verification", response_model=Envelope[None], summary="Renvoyer l'email de vérification")
def resend_verification(background: BackgroundTasks, user: User = USER, db: Session = DB,
                        client: ClientInfo = CLIENT):
    service.resend_verification(db, user, client, background)
    return ok(message="Email de vérification envoyé")


# ── Profil ──

@router.get("/me", response_model=Envelope[UserOut], summary="Profil de l'utilisateur connecté")
def me(user: User = USER):
    return ok(UserOut.of(user))


@router.patch("/me", response_model=Envelope[UserOut], summary="Modifier son profil")
def update_me(body: ProfileUpdateIn, user: User = USER, db: Session = DB, client: ClientInfo = CLIENT):
    user = service.update_profile(db, user, body.model_dump(exclude_unset=True), client)
    return ok(UserOut.of(user), "Profil mis à jour")


@router.post("/me/photo", response_model=Envelope[UserOut],
             summary="Changer sa photo (JPEG, PNG, WEBP ou GIF, 2 Mo max, redimensionnée en 400 px)")
async def upload_photo(photo: UploadFile = File(...), user: User = USER, db: Session = DB,
                       client: ClientInfo = CLIENT):
    raw = await photo.read(2 * 1024 * 1024 + 1)
    user = service.update_photo(db, user, raw, client)
    return ok(UserOut.of(user), "Photo mise à jour")


@router.delete("/me/photo", response_model=Envelope[UserOut], summary="Supprimer sa photo")
def delete_photo(user: User = USER, db: Session = DB, client: ClientInfo = CLIENT):
    return ok(UserOut.of(service.delete_photo(db, user, client)), "Photo supprimée")


@router.delete("/me", response_model=Envelope[None],
               summary="Supprimer définitivement son compte (anonymisation, exigée par les stores)")
def delete_me(body: DeleteAccountIn, background: BackgroundTasks, user: User = USER, db: Session = DB,
              client: ClientInfo = CLIENT):
    email, prenom = user.email, user.prenom  # avant l'anonymisation
    service.delete_account(db, user, body.password, body.reason, client)
    security.alert_account_deleted(db, email, prenom, client, background)
    return ok(message="Votre compte a été supprimé")


# ── Changement d'adresse email (confirmé depuis la nouvelle adresse) ──

class EmailChangeIn(BaseModel):
    nouvel_email: EmailStr
    mot_de_passe: str = Field(min_length=1, max_length=72)


class EmailTokenIn(BaseModel):
    jeton: str = Field(min_length=20, max_length=100)


@router.post("/me/email", response_model=Envelope[None],
             summary="Changer d'adresse email : lien de confirmation envoyé à la nouvelle adresse, alerte à l'ancienne")
def change_email(body: EmailChangeIn, background: BackgroundTasks, user: User = USER, db: Session = DB,
                 client: ClientInfo = CLIENT):
    security.request_email_change(db, user, body.nouvel_email, body.mot_de_passe, client, background)
    return ok(message="Un lien de confirmation a été envoyé à votre nouvelle adresse")


@router.post("/email/confirmer", response_model=Envelope[UserOut], summary="Confirmer la nouvelle adresse (depuis l'app)")
def confirm_email(body: EmailTokenIn, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT):
    return ok(UserOut.of(security.confirm_email_change(db, body.jeton, client, background)), "Adresse email changée")


@router.get("/email/confirmer/{token}", response_class=HTMLResponse, include_in_schema=False)
def confirm_email_link(token: str, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT):
    try:
        user = security.confirm_email_change(db, token, client, background)
        title, text, color = "Adresse changée", f"Votre compte utilise désormais {escape(user.email)}.", "#0b6e4f"
    except ApiError as e:
        title, text, color = "Lien invalide", escape(e.message), "#b42318"
    return HTMLResponse(f"""<!doctype html><html lang="fr"><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title>{title} – Nufotec</title>
<body style="font-family:Arial,sans-serif;display:grid;place-items:center;min-height:90vh;color:#1f2933">
<div style="text-align:center;max-width:420px"><h1 style="color:{color}">{title}</h1><p>{text}</p></div></body></html>""")


# ── Appareils connectés ──

@router.get("/sessions", response_model=Envelope[list[SessionOut]], summary="Mes appareils connectés")
def sessions(user: User = USER, session: UserSession = SESSION, db: Session = DB):
    return ok(service.list_sessions(db, user, session))


@router.delete("/sessions/{session_id}", response_model=Envelope[None], summary="Déconnecter un appareil")
def revoke_session(session_id: int, user: User = USER, db: Session = DB, client: ClientInfo = CLIENT):
    service.revoke_session(db, user, session_id, client)
    return ok(message="Appareil déconnecté")


@router.delete("/sessions", response_model=Envelope[dict], summary="Déconnecter tous les autres appareils")
def revoke_others(user: User = USER, session: UserSession = SESSION, db: Session = DB,
                  client: ClientInfo = CLIENT):
    count = service.revoke_other_sessions(db, user, session, client)
    return ok({"closed": count}, f"{count} appareil(s) déconnecté(s)")


# ── Notifications push ──

@router.post("/push-devices", response_model=Envelope[PushDeviceOut],
             summary="Enregistrer le jeton push de cet appareil (à rappeler à chaque ouverture de l'app)")
def register_push(body: PushDeviceIn, user: User = USER, session: UserSession = SESSION, db: Session = DB):
    device = service.register_push_device(db, user, session, body)
    return ok(PushDeviceOut(id=device.id, provider=device.provider, platform=device.platform,
                            app_version=device.app_version, created_at=device.created_at,
                            last_seen_at=device.last_seen_at), "Appareil enregistré")


@router.delete("/push-devices", response_model=Envelope[None], summary="Ne plus recevoir de notifications ici")
def unregister_push(body: PushTokenIn, user: User = USER, db: Session = DB):
    service.unregister_push_device(db, user, body.token)
    return ok(message="Notifications désactivées sur cet appareil")


@router.post("/push-devices/test", response_model=Envelope[None],
             summary="M'envoyer une notification de test (pour vérifier l'intégration mobile)")
def test_push(background: BackgroundTasks, user: User = USER, db: Session = DB):
    if not push.active_devices(db, [user.id]):
        raise ApiError(404, "NO_DEVICE", "Aucun appareil enregistré pour les notifications")
    background.add_task(push.send_to_users, [user.id], "Nufotec", "Les notifications fonctionnent ✅",
                        {"type": "test"})
    return ok(message="Notification de test envoyée")
