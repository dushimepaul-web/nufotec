from fastapi import APIRouter, BackgroundTasks, Depends, File, Form, Query, UploadFile, status
from fastapi.responses import FileResponse, StreamingResponse
from fastapi.security import HTTPAuthorizationCredentials
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core.client import ClientInfo, get_client
from app.core.database import get_db
from app.core.deps import bearer, get_current_user
from app.core.files import private_path, read_link
from app.core.helpers import now
from app.core.pagination import Page, PageParams, paginate
from app.core.permissions import require_permission
from app.core.responses import ApiError, Envelope, ok
from app.models import DataRoomDocument, InvestmentPhase, User
from app.modules.investors import service
from app.modules.investors.schemas import (
    AssignIn,
    BrokerDecisionIn,
    BrokerFormIn,
    BrokerOut,
    BrokerStatut,
    Commitment,
    DataRoomItem,
    DataRoomUpdateIn,
    FollowUpIn,
    IntroductionIn,
    IntroductionOut,
    InvestorAdminOut,
    InvestorFormIn,
    InvestorOut,
    InvestorStatut,
    InvestorUpdateIn,
    NdaOut,
    NoteIn,
    PhaseIn,
    PhaseOut,
    StatusIn,
    Timeline,
)

DB = Depends(get_db)
CLIENT = Depends(get_client)
USER = Depends(get_current_user)
MANAGE = Depends(require_permission("investors.manage"))

public = APIRouter(tags=["Investir (public)"])
investor = APIRouter(prefix="/investisseur", tags=["Espace investisseur"])
broker = APIRouter(prefix="/courtier", tags=["Espace courtier"])
admin = APIRouter(prefix="/admin", tags=["Admin · Investisseurs et courtiers"])

THANKS_INVESTOR = "Merci ! Votre manifestation d'intérêt a bien été reçue. Notre équipe vous recontacte rapidement."
THANKS_BROKER = "Merci ! Votre candidature a bien été reçue. Nous l'examinons et revenons vers vous."


def _optional_user(db: Session, creds: HTTPAuthorizationCredentials | None) -> User | None:
    if creds is None:
        return None
    from app.core.deps import get_current_session

    try:
        return get_current_user(get_current_session(creds, db), db)
    except ApiError:
        return None


# ═════════════ Public ═════════════

@public.get("/investissement/phases", response_model=Envelope[list[PhaseOut]], summary="Phases d'investissement")
def phases(db: Session = DB):
    rows = db.scalars(select(InvestmentPhase).order_by(InvestmentPhase.annee_debut, InvestmentPhase.id))
    return ok([service.phase_out(p) for p in rows])


@public.post("/investisseurs/manifestation", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
             summary="Manifestation d'intérêt d'un investisseur (sans compte)")
def express_interest(body: InvestorFormIn, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT,
                     creds: HTTPAuthorizationCredentials | None = Depends(bearer)):
    wa = service.submit_investor(db, body, _optional_user(db, creds), client, background)
    return ok({"whatsapp": wa}, THANKS_INVESTOR)


@public.post("/courtiers/candidature", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
             summary="Candidature d'un courtier / apporteur (sans compte)")
def broker_application(body: BrokerFormIn, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT,
                       creds: HTTPAuthorizationCredentials | None = Depends(bearer)):
    wa = service.submit_broker(db, body, _optional_user(db, creds), client, background)
    return ok({"whatsapp": wa}, THANKS_BROKER)


# ═════════════ Espace investisseur ═════════════

def _me_investor(user: User = USER, db: Session = DB):
    return service.my_investor(db, user)


@investor.get("/dossier", response_model=Envelope[InvestorOut], summary="Mon dossier et son avancement")
def my_file(i=Depends(_me_investor), db: Session = DB):
    return ok(service.investor_out(db, i))


@investor.get("/nda", response_model=Envelope[NdaOut], summary="Accord de confidentialité (texte et état)")
def nda(i=Depends(_me_investor), db: Session = DB):
    row = service.nda_accepted(db, i)
    return ok({"version": service.NDA_VERSION, "texte": service.NDA_TEXT, "accepte": row is not None,
               "accepte_le": row.accepted_at if row else None})


@investor.post("/nda/accepter", response_model=Envelope[NdaOut], summary="Accepter l'accord de confidentialité")
def accept(i=Depends(_me_investor), user: User = USER, db: Session = DB, client: ClientInfo = CLIENT):
    row = service.accept_nda(db, i, user, client)
    return ok({"version": service.NDA_VERSION, "texte": service.NDA_TEXT, "accepte": True,
               "accepte_le": row.accepted_at}, "Accord de confidentialité accepté")


@investor.get("/data-room", response_model=Envelope[list[DataRoomItem]],
              summary="Documents de la data room (dossier qualifié + NDA accepté)")
def room(i=Depends(_me_investor), db: Session = DB):
    return ok([{"uuid": d.uuid, "titre": d.titre, "description": d.description, "nom_fichier": d.original_name,
                "taille": d.size_bytes, "ajoute_le": d.created_at} for d in service.data_room(db, i)])


@investor.get("/data-room/{doc_uuid}/lien", response_model=Envelope[dict],
              summary="Lien de téléchargement temporaire (journalisé)")
def room_link(doc_uuid: str, i=Depends(_me_investor), user: User = USER, db: Session = DB, client: ClientInfo = CLIENT):
    return ok(service.data_room_link(db, i, user, doc_uuid, client))


@investor.get("/fichiers/{token}", include_in_schema=False)
def room_file(token: str, db: Session = DB):
    doc = db.get(DataRoomDocument, read_link(token, scope="dataroom"))
    if doc is None or not doc.is_active:
        raise ApiError(404, "NOT_FOUND", "Fichier introuvable")
    path = private_path(service.PRIVATE_FOLDER, doc.stored_name)
    if not path.exists():
        raise ApiError(404, "NOT_FOUND", "Fichier introuvable")
    return FileResponse(path, media_type=doc.mime, filename=doc.original_name, content_disposition_type="inline",
                        headers={"Cache-Control": "private, no-store", "X-Content-Type-Options": "nosniff"})


# ═════════════ Espace courtier ═════════════

def _me_broker(user: User = USER, db: Session = DB):
    return service.my_broker(db, user)


@broker.get("/dossier", response_model=Envelope[BrokerOut], summary="Ma fiche courtier et son statut")
def broker_file(b=Depends(_me_broker)):
    return ok(service.broker_out(b))


@broker.get("/investisseurs", response_model=Envelope[list[IntroductionOut]], summary="Investisseurs que j'ai présentés")
def my_introductions(b=Depends(_me_broker), db: Session = DB):
    return ok(service.introductions(db, b))


@broker.post("/investisseurs", status_code=status.HTTP_201_CREATED, response_model=Envelope[IntroductionOut],
             summary="Présenter un investisseur (attribution 12 mois au premier courtier)")
def introduce(body: IntroductionIn, background: BackgroundTasks, b=Depends(_me_broker), user: User = USER,
              db: Session = DB, client: ClientInfo = CLIENT):
    intro = service.introduce(db, b, user, body, client, background)
    return ok({"id": intro.id, "nom_complet": intro.full_name, "organisation": intro.organization, "email": intro.email,
               "montant": intro.commitment_range, "statut": "pending", "attribue_jusqu_au": intro.attribution_until,
               "created_at": intro.created_at}, "Investisseur présenté")


# ═════════════ Administration ═════════════

class InvestorFilters:
    def __init__(self, statut: InvestorStatut | None = None,
                 source: str | None = Query(None, pattern="^(direct|courtier)$"), montant: Commitment | None = None,
                 calendrier: Timeline | None = None, pays_id: int | None = None, responsable_uuid: str | None = None,
                 relance_due: bool = False, q: str | None = Query(None, max_length=100)):
        self.query = service.admin_query(statut, source, montant, calendrier, pays_id, responsable_uuid,
                                         relance_due, q)


@admin.get("/investisseurs", response_model=Envelope[Page[InvestorAdminOut]], summary="Pipeline des investisseurs")
def admin_investors(f: InvestorFilters = Depends(), page: PageParams = Depends(), db: Session = DB, _: User = MANAGE):
    rows, meta = paginate(db, f.query, page)
    return ok({"items": [service.investor_out(db, i, admin=True) for i in rows], "meta": meta})


@admin.get("/investisseurs/stats", response_model=Envelope[dict], summary="Statistiques du pipeline")
def admin_stats(db: Session = DB, _: User = MANAGE):
    return ok(service.stats(db))


@admin.get("/investisseurs/export.csv", summary="Export CSV (filtres identiques à la liste)",
           response_class=StreamingResponse, responses={200: {"content": {"text/csv": {}}}})
def admin_export(f: InvestorFilters = Depends(), db: Session = DB, _: User = MANAGE):
    return StreamingResponse(service.export_csv(db, f.query), media_type="text/csv; charset=utf-8",
                             headers={"Content-Disposition": f'attachment; filename="investisseurs_{now():%Y%m%d}.csv"'})


@admin.get("/investisseurs/{investor_uuid}", response_model=Envelope[dict], summary="Fiche, notes et historique")
def admin_investor(investor_uuid: str, db: Session = DB, _: User = MANAGE):
    i = service.get_investor(db, investor_uuid)
    return ok({**service.investor_out(db, i, admin=True), "historique": service.history(db, i)})


@admin.patch("/investisseurs/{investor_uuid}", response_model=Envelope[InvestorAdminOut], summary="Modifier la fiche")
def admin_update(investor_uuid: str, body: InvestorUpdateIn, db: Session = DB, _: User = MANAGE):
    i = service.update_investor(db, service.get_investor(db, investor_uuid), body.model_dump(exclude_unset=True))
    return ok(service.investor_out(db, i, admin=True), "Fiche mise à jour")


@admin.post("/investisseurs/{investor_uuid}/statut", response_model=Envelope[InvestorAdminOut],
            summary="Changer l'étape du pipeline")
def admin_status(investor_uuid: str, body: StatusIn, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    i = service.set_status(db, service.get_investor(db, investor_uuid), actor, body.statut, body.note, client)
    return ok(service.investor_out(db, i, admin=True), "Statut mis à jour")


@admin.post("/investisseurs/{investor_uuid}/notes", response_model=Envelope[None], summary="Ajouter une note")
def admin_note(investor_uuid: str, body: NoteIn, db: Session = DB, actor: User = MANAGE):
    service.add_note(db, service.get_investor(db, investor_uuid), actor, body.texte)
    return ok(message="Note ajoutée")


@admin.post("/investisseurs/{investor_uuid}/responsable", response_model=Envelope[InvestorAdminOut],
            summary="Assigner un responsable")
def admin_assign(investor_uuid: str, body: AssignIn, db: Session = DB, actor: User = MANAGE):
    i = service.assign(db, service.get_investor(db, investor_uuid), actor, body.admin_uuid)
    return ok(service.investor_out(db, i, admin=True), "Responsable mis à jour")


@admin.post("/investisseurs/{investor_uuid}/relance", response_model=Envelope[InvestorAdminOut],
            summary="Programmer une relance (rappel par email au responsable)")
def admin_follow_up(investor_uuid: str, body: FollowUpIn, db: Session = DB, actor: User = MANAGE):
    i = service.follow_up(db, service.get_investor(db, investor_uuid), actor, body.le, body.note)
    return ok(service.investor_out(db, i, admin=True), "Relance programmée" if body.le else "Relance annulée")


@admin.get("/courtiers", response_model=Envelope[Page[BrokerOut]], summary="Courtiers (filtre par statut)")
def admin_brokers(statut: BrokerStatut | None = None, q: str | None = Query(None, max_length=100),
                  page: PageParams = Depends(), db: Session = DB, _: User = MANAGE):
    rows, meta = paginate(db, service.broker_query(statut, q), page)
    return ok({"items": [service.broker_out(b) for b in rows], "meta": meta})


@admin.get("/courtiers/{broker_uuid}", response_model=Envelope[dict], summary="Fiche courtier et ses présentations")
def admin_broker(broker_uuid: str, db: Session = DB, _: User = MANAGE):
    b = service.get_broker(db, broker_uuid)
    return ok({**service.broker_out(b), "presentations": service.introductions(db, b)})


@admin.post("/courtiers/{broker_uuid}/decision", response_model=Envelope[BrokerOut],
            summary="Approuver, refuser ou suspendre un courtier")
def admin_decide(broker_uuid: str, body: BrokerDecisionIn, background: BackgroundTasks, db: Session = DB,
                 client: ClientInfo = CLIENT, actor: User = MANAGE):
    b = service.decide_broker(db, service.get_broker(db, broker_uuid), actor, body.statut, body.motif, client, background)
    return ok(service.broker_out(b), "Décision enregistrée")


@admin.post("/investissement/phases", status_code=status.HTTP_201_CREATED, response_model=Envelope[PhaseOut],
            summary="Nouvelle phase (répartition = 100 %)")
def admin_add_phase(body: PhaseIn, db: Session = DB, _: User = MANAGE):
    return ok(service.phase_out(service.save_phase(db, None, body)), "Phase créée")


@admin.put("/investissement/phases/{phase_id}", response_model=Envelope[PhaseOut], summary="Modifier une phase")
def admin_edit_phase(phase_id: int, body: PhaseIn, db: Session = DB, _: User = MANAGE):
    phase = db.get(InvestmentPhase, phase_id)
    if phase is None:
        raise ApiError(404, "NOT_FOUND", "Phase introuvable")
    return ok(service.phase_out(service.save_phase(db, phase, body)), "Phase mise à jour")


@admin.delete("/investissement/phases/{phase_id}", response_model=Envelope[None], summary="Supprimer une phase")
def admin_delete_phase(phase_id: int, db: Session = DB, _: User = MANAGE):
    phase = db.get(InvestmentPhase, phase_id)
    if phase is None:
        raise ApiError(404, "NOT_FOUND", "Phase introuvable")
    db.delete(phase)
    db.commit()
    return ok(message="Phase supprimée")


@admin.get("/data-room", response_model=Envelope[list[dict]], summary="Documents de la data room")
def admin_room(db: Session = DB, _: User = MANAGE):
    docs = db.scalars(select(DataRoomDocument).order_by(DataRoomDocument.created_at.desc()))
    return ok([service.document_admin_out(db, d) for d in docs])


@admin.post("/data-room", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
            summary="Ajouter un document (PDF, DOCX, XLSX, PPTX – 50 Mo max)")
async def admin_upload(titre: str = Form(..., min_length=2, max_length=255), description: str | None = Form(None),
                       fichier: UploadFile = File(...), db: Session = DB, client: ClientInfo = CLIENT,
                       actor: User = MANAGE):
    raw = await fichier.read(50 * 1024 * 1024 + 1)
    name = (fichier.filename or "document").replace("\\", "/").split("/")[-1]
    doc = service.upload_document(db, actor, raw, name, titre, description, client)
    return ok(service.document_admin_out(db, doc), "Document ajouté")


@admin.patch("/data-room/{doc_uuid}", response_model=Envelope[dict],
             summary="Titre, visibilité (tous les qualifiés ou sélection), activation")
def admin_room_update(doc_uuid: str, body: DataRoomUpdateIn, db: Session = DB, _: User = MANAGE):
    doc = service.update_document(db, service.get_document(db, doc_uuid), body.model_dump(exclude_unset=True))
    return ok(service.document_admin_out(db, doc), "Document mis à jour")


@admin.get("/data-room/{doc_uuid}/telechargements", response_model=Envelope[list[dict]], summary="Qui a téléchargé")
def admin_room_log(doc_uuid: str, db: Session = DB, _: User = MANAGE):
    return ok(service.downloads_log(db, service.get_document(db, doc_uuid)))
