from html import escape

from fastapi import APIRouter, BackgroundTasks, Depends, File, UploadFile, status
from fastapi.responses import HTMLResponse
from sqlalchemy.orm import Session

from app.core.client import ClientInfo, get_client
from app.core.database import get_db
from app.core.deps import get_current_session, get_current_user
from app.core.responses import ApiError, Envelope, ok
from app.models import User, UserSession
from app.modules.auth import service
from app.modules.auth.schemas import (
    AuthOut,
    ChangePasswordIn,
    DeleteAccountIn,
    ForgotPasswordIn,
    LoginIn,
    ProfileUpdateIn,
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
def login(body: LoginIn, db: Session = DB, client: ClientInfo = CLIENT):
    user = service.authenticate(db, body.login, body.password, client)
    tokens = service.open_session(db, user, client)
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
def reset_password(body: ResetPasswordIn, db: Session = DB, client: ClientInfo = CLIENT):
    service.reset_password(db, body.email, body.code, body.new_password, client)
    return ok(message="Mot de passe réinitialisé. Vous pouvez vous connecter.")


@router.post("/change-password", response_model=Envelope[None],
             summary="Changer son mot de passe (déconnecte les autres appareils)")
def change_password(body: ChangePasswordIn, user: User = USER, session: UserSession = SESSION,
                    db: Session = DB, client: ClientInfo = CLIENT):
    service.change_password(db, user, session, body.current_password, body.new_password, client)
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
def delete_me(body: DeleteAccountIn, user: User = USER, db: Session = DB, client: ClientInfo = CLIENT):
    service.delete_account(db, user, body.password, body.reason, client)
    return ok(message="Votre compte a été supprimé")


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
