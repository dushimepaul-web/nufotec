from datetime import date

from fastapi import APIRouter, BackgroundTasks, Depends, File, Query, UploadFile, status
from fastapi.responses import StreamingResponse
from pydantic import BaseModel, Field
from sqlalchemy.orm import Session

from app.core.client import ClientInfo, get_client
from app.core.database import get_db
from app.core.helpers import now
from app.core.pagination import Page, PageParams
from app.core.permissions import require_permission
from app.core.responses import Envelope, ok
from app.models import User
from app.modules.auth import service as auth_service
from app.modules.auth.schemas import SessionOut
from app.modules.users import service
from app.modules.users.schemas import (
    ActivityOut,
    AdminUserCreateIn,
    AdminUserItem,
    AdminUserOut,
    AdminUserUpdateIn,
    KycIn,
    Statut,
    Tri,
    UserStatsOut,
    UserType,
)

router = APIRouter(prefix="/admin/users", tags=["Admin · Utilisateurs"])

DB = Depends(get_db)
CLIENT = Depends(get_client)
READ = Depends(require_permission("users.read"))
WRITE = Depends(require_permission("users.write"))
DELETE = Depends(require_permission("users.delete"))


class Filters:
    def __init__(
        self,
        q: str | None = Query(None, max_length=100, description="Nom, prénom, email ou téléphone"),
        role_id: int | None = None,
        type_utilisateur: UserType | None = None,
        statut: Statut | None = Query(None, description="Par défaut : tous les comptes non supprimés"),
        email_verifie: bool | None = None,
        kyc: bool | None = None,
        inscrit_du: date | None = None,
        inscrit_au: date | None = None,
        tri: Tri = "-created_at",
    ):
        self.values = {k: v for k, v in locals().items() if k != "self"}


# ── Lecture ──

@router.get("", response_model=Envelope[Page[AdminUserItem]], summary="Liste paginée avec recherche et filtres")
def list_users(filters: Filters = Depends(), page: PageParams = Depends(), db: Session = DB, _: User = READ):
    items, meta = service.list_users(db, page, **filters.values)
    return ok({"items": items, "meta": meta})


@router.get("/stats", response_model=Envelope[UserStatsOut], summary="Chiffres pour le tableau de bord")
def stats(db: Session = DB, _: User = READ):
    return ok(service.stats(db))


@router.get("/export.csv", summary="Exporter la liste filtrée (CSV pour Excel)",
            response_class=StreamingResponse, responses={200: {"content": {"text/csv": {}}}})
def export(filters: Filters = Depends(), db: Session = DB, client: ClientInfo = CLIENT,
           actor: User = Depends(require_permission("users.read", "users.export"))):
    from app.core import audit

    audit.activity(db, actor.id, "users_exported", client, "Export CSV des utilisateurs", module="users",
                   new_data={k: v for k, v in filters.values.items() if v is not None})
    db.commit()
    filename = f"utilisateurs_{now():%Y%m%d_%H%M}.csv"
    return StreamingResponse(service.export_csv(db, **filters.values), media_type="text/csv; charset=utf-8",
                             headers={"Content-Disposition": f'attachment; filename="{filename}"'})


@router.get("/{user_uuid}", response_model=Envelope[AdminUserOut], summary="Détail d'un utilisateur")
def get_user(user_uuid: str, db: Session = DB, _: User = READ):
    return ok(service.detail(db, service.get_by_uuid(db, user_uuid)))


@router.get("/{user_uuid}/activities", response_model=Envelope[Page[ActivityOut]], summary="Journal d'activité")
def user_activities(user_uuid: str, page: PageParams = Depends(), db: Session = DB, _: User = READ):
    items, meta = service.activities(db, service.get_by_uuid(db, user_uuid), page)
    return ok({"items": items, "meta": meta})


@router.get("/{user_uuid}/sessions", response_model=Envelope[list[SessionOut]], summary="Appareils connectés")
def user_sessions(user_uuid: str, db: Session = DB, _: User = READ):
    return ok(auth_service.list_sessions(db, service.get_by_uuid(db, user_uuid), None))


# ── Écriture ──

@router.post("", status_code=status.HTTP_201_CREATED, response_model=Envelope[AdminUserOut],
             summary="Créer un compte (l'utilisateur reçoit un code pour choisir son mot de passe)")
def create_user(body: AdminUserCreateIn, background: BackgroundTasks, db: Session = DB,
                client: ClientInfo = CLIENT, actor: User = WRITE):
    user = service.create(db, actor, body, client, background)
    return ok(service.detail(db, user), "Compte créé" + (", invitation envoyée" if body.send_invitation else ""))


@router.patch("/{user_uuid}", response_model=Envelope[AdminUserOut],
              summary="Modifier un compte (changer le rôle déconnecte l'utilisateur)")
def update_user(user_uuid: str, body: AdminUserUpdateIn, background: BackgroundTasks, db: Session = DB,
                client: ClientInfo = CLIENT, actor: User = WRITE):
    target = service.get_by_uuid(db, user_uuid)
    user = service.update(db, actor, target, body.model_dump(exclude_unset=True), client, background)
    return ok(service.detail(db, user), "Compte mis à jour")


@router.post("/{user_uuid}/activate", response_model=Envelope[AdminUserOut], summary="Activer")
def activate(user_uuid: str, db: Session = DB, client: ClientInfo = CLIENT, actor: User = WRITE):
    user = service.set_active(db, actor, service.get_by_uuid(db, user_uuid), True, client)
    return ok(service.detail(db, user), "Compte activé")


@router.post("/{user_uuid}/deactivate", response_model=Envelope[AdminUserOut],
             summary="Désactiver (déconnecte immédiatement)")
def deactivate(user_uuid: str, db: Session = DB, client: ClientInfo = CLIENT, actor: User = WRITE):
    user = service.set_active(db, actor, service.get_by_uuid(db, user_uuid), False, client)
    return ok(service.detail(db, user), "Compte désactivé")


@router.post("/{user_uuid}/kyc", response_model=Envelope[AdminUserOut], summary="Valider ou retirer le KYC")
def kyc(user_uuid: str, body: KycIn, db: Session = DB, client: ClientInfo = CLIENT, actor: User = WRITE):
    user = service.set_kyc(db, actor, service.get_by_uuid(db, user_uuid), body.est_verifie, client)
    return ok(service.detail(db, user), "KYC mis à jour")


@router.post("/{user_uuid}/send-reset", response_model=Envelope[None],
             summary="Envoyer un code de réinitialisation à l'utilisateur")
def send_reset(user_uuid: str, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT,
               actor: User = WRITE):
    service.send_reset(db, actor, service.get_by_uuid(db, user_uuid), client, background)
    return ok(message="Code de réinitialisation envoyé à l'utilisateur")


@router.post("/{user_uuid}/photo", response_model=Envelope[AdminUserOut], summary="Changer la photo")
async def set_photo(user_uuid: str, photo: UploadFile = File(...), db: Session = DB, client: ClientInfo = CLIENT,
                    actor: User = WRITE):
    raw = await photo.read(2 * 1024 * 1024 + 1)
    user = service.set_photo(db, actor, service.get_by_uuid(db, user_uuid, include_deleted=False), raw, client)
    return ok(service.detail(db, user), "Photo mise à jour")


@router.delete("/{user_uuid}/photo", response_model=Envelope[AdminUserOut], summary="Supprimer la photo")
def delete_photo(user_uuid: str, db: Session = DB, client: ClientInfo = CLIENT, actor: User = WRITE):
    user = service.set_photo(db, actor, service.get_by_uuid(db, user_uuid), None, client)
    return ok(service.detail(db, user), "Photo supprimée")


@router.delete("/{user_uuid}/sessions", response_model=Envelope[dict], summary="Forcer la déconnexion")
def close_sessions(user_uuid: str, db: Session = DB, client: ClientInfo = CLIENT, actor: User = WRITE):
    count = service.close_sessions(db, actor, service.get_by_uuid(db, user_uuid), client)
    return ok({"closed": count}, f"{count} session(s) fermée(s)")


@router.delete("/{user_uuid}", response_model=Envelope[None], summary="Supprimer (restaurable)")
def delete_user(user_uuid: str, db: Session = DB, client: ClientInfo = CLIENT, actor: User = DELETE):
    service.soft_delete(db, actor, service.get_by_uuid(db, user_uuid), client)
    return ok(message="Compte supprimé")


@router.post("/{user_uuid}/restore", response_model=Envelope[AdminUserOut], summary="Restaurer un compte supprimé")
def restore(user_uuid: str, db: Session = DB, client: ClientInfo = CLIENT, actor: User = DELETE):
    user = service.restore(db, actor, service.get_by_uuid(db, user_uuid), client)
    return ok(service.detail(db, user), "Compte restauré")


class PurgeIn(BaseModel):
    jours: int | None = Field(default=None, ge=7, le=3650, description="Par défaut : DELETED_ACCOUNTS_PURGE_DAYS (30)")


@router.post("/purger", response_model=Envelope[dict],
             summary="Anonymiser les comptes supprimés depuis plus de N jours (action manuelle, aucune tâche planifiée)")
def purge(body: PurgeIn, db: Session = DB, client: ClientInfo = CLIENT, actor: User = DELETE):
    from app.core.config import get_settings
    from app.modules.auth.security import purge_deleted_accounts

    days = body.jours or get_settings().deleted_accounts_purge_days
    count = purge_deleted_accounts(db, actor, days, client)
    return ok({"anonymises": count, "jours": days}, f"{count} compte(s) anonymisé(s)")
