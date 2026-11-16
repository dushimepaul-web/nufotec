from datetime import date, timedelta

from fastapi import APIRouter, BackgroundTasks, Depends, File, Form, Query, UploadFile, status
from fastapi.responses import FileResponse, Response
from sqlalchemy.orm import Session

from app.core.client import ClientInfo, get_client
from app.core.database import get_db
from app.core.deps import get_current_user
from app.core.security import _encode, decode_token
from app.core.files import MAX_BYTES, private_path, read_link
from app.core.pagination import Page, PageParams, paginate
from app.core.permissions import require_permission
from app.core.responses import ApiError, Envelope, ok
from pydantic import BaseModel, Field
from sqlalchemy import select

from app.models import Consultation, ConsultationDocument, ModePaiement, User
from app.modules.consultations import doctors, service, whatsapp
from app.modules.consultations.schemas import (
    GuestBookingOut,
    PublicBookingIn,
    AbsenceIn,
    AbsenceOut,
    AdminCancelIn,
    AdminMedecinIn,
    AdminMedecinUpdateIn,
    BookingIn,
    CancelIn,
    ConsultationOut,
    Creneau,
    DocumentOut,
    FinishIn,
    HoraireItem,
    JoinOut,
    LicenceIn,
    LinkOut,
    MedecinAdminOut,
    MedecinDetail,
    MedecinProfileIn,
    MedecinPublic,
    ModePaiementOut,
    PaymentDecisionIn,
    ReassignIn,
    RefuseIn,
    RescheduleIn,
    ReviewIn,
    Statut,
    WhatsAppOut,
)
from app.modules.consultations.slots import duration, free_slots

DB = Depends(get_db)
CLIENT = Depends(get_client)
USER = Depends(get_current_user)

public = APIRouter(tags=["Médecins (public)"])
patient = APIRouter(prefix="/consultations", tags=["Consultations · Patient"])
doctor = APIRouter(prefix="/medecin", tags=["Consultations · Médecin"])
admin = APIRouter(prefix="/admin", tags=["Admin · Consultations et médecins"])


async def _read(upload: UploadFile) -> bytes:
    return await upload.read(MAX_BYTES + 1)


def _page(db, query, params, viewer):
    rows, meta = paginate(db, query, params)
    return ok({"items": [service.serialize(db, c, viewer) for c in rows], "meta": meta})


# ═════════════ Public ═════════════

@public.get("/medecins", response_model=Envelope[list[MedecinPublic]], summary="Médecins disponibles")
def list_doctors(q: str | None = Query(None, max_length=100), specialite: str | None = Query(None, max_length=100),
                 langue: str | None = Query(None, max_length=30), db: Session = DB):
    return ok([service.doctor_public(m) for m in service.list_doctors(db, q, specialite, langue)])


@public.get("/medecins/{medecin_uuid}", response_model=Envelope[MedecinDetail], summary="Fiche d'un médecin")
def get_doctor(medecin_uuid: str, db: Session = DB):
    return ok(service.doctor_public(service.get_doctor(db, medecin_uuid), detail=True))


@public.get("/medecins/{medecin_uuid}/creneaux", response_model=Envelope[list[Creneau]],
            summary="Créneaux libres (14 jours maximum par demande)")
def slots(medecin_uuid: str, du: date | None = None, au: date | None = None, db: Session = DB):
    du = du or date.today()
    au = au or du + timedelta(days=6)
    if au < du or (au - du).days > 13:
        raise ApiError(422, "INVALID_RANGE", "Période invalide (14 jours maximum)")
    m = service.get_doctor(db, medecin_uuid)
    step = duration(m)
    return ok([{"debut": s, "fin": s + step} for s in free_slots(db, m, du, au)])


@public.get("/modes-paiement", response_model=Envelope[list[ModePaiementOut]], summary="Modes de paiement")
def payment_methods(db: Session = DB):
    return ok(service.payment_methods(db))


@public.get("/fichiers/{token}", include_in_schema=False)
def download(token: str, db: Session = DB):
    """Lien signé temporaire (délivré après contrôle d'accès)."""
    doc = db.get(ConsultationDocument, read_link(token))
    if doc is None:
        raise ApiError(404, "NOT_FOUND", "Fichier introuvable")
    path = private_path(service.PRIVATE_FOLDER, doc.stored_name)
    if not path.exists():
        raise ApiError(404, "NOT_FOUND", "Fichier introuvable")
    ext = "pdf" if doc.mime == "application/pdf" else "jpg"
    return FileResponse(path, media_type=doc.mime, filename=doc.original_name or f"document.{ext}",
                        content_disposition_type="inline",
                        headers={"Cache-Control": "private, no-store", "X-Content-Type-Options": "nosniff"})


# ═════════════ Réservation sans compte ═════════════
# Le visiteur ne crée pas de compte : il donne son nom et son WhatsApp, la demande est enregistrée puis
# envoyée au médecin par WhatsApp (message préparé). Un code temporaire permet d'ajouter documents et preuve.

GUEST = "consult_guest"


def _guest_consultation(db: Session, numero: str, jeton: str):
    try:
        payload = decode_token(jeton, GUEST)
    except Exception:
        raise ApiError(401, "INVALID_TOKEN", "Lien expiré : recommencez la demande ou contactez-nous") from None
    if payload.get("sub") != numero:
        raise ApiError(403, "FORBIDDEN", "Ce code ne correspond pas à cette consultation")
    c = db.scalar(select(Consultation).where(Consultation.numero_consultation == numero))
    if c is None:
        raise ApiError(404, "NOT_FOUND", "Consultation introuvable")
    return c


@public.post("/consultations/demande", status_code=status.HTTP_201_CREATED, response_model=Envelope[GuestBookingOut],
             summary="Demander une consultation SANS compte (nom + WhatsApp) ; renvoie le lien WhatsApp vers l'expert")
def book_without_account(body: PublicBookingIn, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT):
    if body.site_web:  # robot : champ invisible rempli
        raise ApiError(422, "VALIDATION_ERROR", "Données invalides")
    c = service.book(db, None, body, client, background)
    data = service.serialize(db, c, "patient")
    data["whatsapp_medecin"] = whatsapp.link(db, c)
    data["jeton"], _, _ = _encode(c.numero_consultation, GUEST, timedelta(hours=12))
    return ok(data, "Demande enregistrée : envoyez maintenant le récapitulatif à l'expert par WhatsApp")


@public.post("/consultations/demande/{numero}/documents", status_code=status.HTTP_201_CREATED,
             response_model=Envelope[DocumentOut], summary="Sans compte : ajouter un document médical (code temporaire)")
async def guest_document(numero: str, jeton: str = Form(...), fichier: UploadFile = File(...), db: Session = DB,
                         client: ClientInfo = CLIENT):
    c = _guest_consultation(db, numero, jeton)
    doc = service.add_document(db, c, None, "document", await _read(fichier), fichier.filename, client)
    return ok({"id": doc.id, "kind": doc.kind, "nom": doc.original_name, "mime": doc.mime, "taille": doc.size_bytes,
               "ajoute_le": doc.created_at}, "Document ajouté")


@public.post("/consultations/demande/{numero}/paiement", response_model=Envelope[ConsultationOut],
             summary="Sans compte : téléverser la preuve de paiement (code temporaire)")
async def guest_payment(numero: str, jeton: str = Form(...), mode_paiement_id: int = Form(...), preuve: UploadFile = File(...),
                        db: Session = DB, client: ClientInfo = CLIENT):
    c = _guest_consultation(db, numero, jeton)
    service.submit_payment(db, c, None, mode_paiement_id, await _read(preuve), preuve.filename, client)
    return ok(service.serialize(db, c, "patient"), "Preuve de paiement envoyée")


# ═════════════ Patient ═════════════

@patient.post("", status_code=status.HTTP_201_CREATED, response_model=Envelope[ConsultationOut],
              summary="Réserver une consultation (prix calculé par le serveur)")
def book(body: BookingIn, background: BackgroundTasks, user: User = USER, db: Session = DB,
         client: ClientInfo = CLIENT):
    c = service.book(db, user, body, client, background)
    data = service.serialize(db, c, "patient")
    data["whatsapp_medecin"] = whatsapp.link(db, c)
    return ok(data, "Consultation réservée : envoyez maintenant le récapitulatif à l'expert par WhatsApp")


@patient.get("", response_model=Envelope[Page[ConsultationOut]], summary="Mes consultations")
def mine(statut: Statut | None = None, a_venir: bool | None = None, page: PageParams = Depends(),
         user: User = USER, db: Session = DB):
    rows, meta = paginate(db, service.patient_query(user, statut, a_venir), page)
    return ok({"items": [service.serialize_for_patient(db, c, user) for c in rows], "meta": meta})


@patient.get("/{numero}", response_model=Envelope[ConsultationOut], summary="Détail d'une de mes consultations")
def mine_detail(numero: str, user: User = USER, db: Session = DB):
    return ok(service.serialize(db, service.for_patient(db, user, numero), "patient"))


@patient.get("/{numero}/whatsapp", response_model=Envelope[WhatsAppOut],
             summary="Lien WhatsApp vers le médecin avec le récapitulatif de la demande")
def whatsapp_link(numero: str, user: User = USER, db: Session = DB):
    return ok(whatsapp.link(db, service.for_patient(db, user, numero)))


@patient.post("/{numero}/documents", status_code=status.HTTP_201_CREATED, response_model=Envelope[DocumentOut],
              summary="Ajouter un document médical (PDF ou image, 10 Mo max, stockage privé)")
async def add_document(numero: str, fichier: UploadFile = File(...), user: User = USER, db: Session = DB,
                       client: ClientInfo = CLIENT):
    c = service.for_patient(db, user, numero)
    doc = service.add_document(db, c, user, "document", await _read(fichier), fichier.filename, client)
    return ok({"id": doc.id, "kind": doc.kind, "nom": doc.original_name, "mime": doc.mime, "taille": doc.size_bytes,
               "ajoute_le": doc.created_at}, "Document ajouté")


@patient.get("/{numero}/documents/{doc_id}/lien", response_model=Envelope[LinkOut],
             summary="Lien de téléchargement temporaire (5 min)")
def patient_doc_link(numero: str, doc_id: int, user: User = USER, db: Session = DB):
    return ok(service.document_link(db, service.for_patient(db, user, numero), doc_id, "patient"))


@patient.post("/{numero}/paiement", response_model=Envelope[ConsultationOut],
              summary="Téléverser la preuve de paiement (aucun paiement en ligne ; validée ensuite par l'administration)")
async def pay(numero: str, mode_paiement_id: int = Form(...), preuve: UploadFile = File(...), user: User = USER,
              db: Session = DB, client: ClientInfo = CLIENT):
    c = service.for_patient(db, user, numero)
    service.submit_payment(db, c, user, mode_paiement_id, await _read(preuve), preuve.filename, client)
    return ok(service.serialize(db, c, "patient"), "Preuve de paiement envoyée")


@patient.post("/{numero}/annuler", response_model=Envelope[ConsultationOut], summary="Annuler (jusqu'à 2 h avant)")
def cancel(numero: str, body: CancelIn, background: BackgroundTasks, user: User = USER, db: Session = DB,
           client: ClientInfo = CLIENT):
    c = service.cancel_by_patient(db, service.for_patient(db, user, numero), user, body.motif, client, background)
    return ok(service.serialize(db, c, "patient"), "Consultation annulée")


@patient.post("/{numero}/deplacer", response_model=Envelope[ConsultationOut],
              summary="Déplacer vers un autre créneau libre (jusqu'à 2 h avant)")
def reschedule(numero: str, body: RescheduleIn, background: BackgroundTasks, user: User = USER, db: Session = DB,
               client: ClientInfo = CLIENT):
    c = service.reschedule(db, service.for_patient(db, user, numero), user, body.debut, client, background)
    return ok(service.serialize(db, c, "patient"), "Rendez-vous déplacé")


def _ics(c, viewer: str) -> Response:
    return Response(service.calendar_ics(c, viewer), media_type="text/calendar; charset=utf-8",
                    headers={"Content-Disposition": f'attachment; filename="{c.numero_consultation}.ics"'})


@patient.get("/{numero}/agenda.ics", summary="Ajouter à mon agenda (Google, Outlook, iPhone)",
             response_class=Response, responses={200: {"content": {"text/calendar": {}}}})
def patient_ics(numero: str, user: User = USER, db: Session = DB):
    return _ics(service.for_patient(db, user, numero), "patient")


@patient.get("/{numero}/rejoindre", response_model=Envelope[JoinOut], summary="Lien de la salle vidéo")
def join(numero: str, user: User = USER, db: Session = DB):
    return ok(service.join(service.for_patient(db, user, numero)))


@patient.post("/{numero}/avis", response_model=Envelope[None], summary="Noter la consultation (1 à 5)")
def review(numero: str, body: ReviewIn, user: User = USER, db: Session = DB):
    service.review(db, service.for_patient(db, user, numero), user, body.note, body.commentaire)
    return ok(message="Merci pour votre avis")


# ═════════════ Médecin ═════════════

def _me(user: User = USER, db: Session = DB):
    return service.current_medecin(db, user)


@doctor.get("/profil", response_model=Envelope[MedecinAdminOut], summary="Ma fiche")
def my_profile(m=Depends(_me)):
    return ok(doctors.admin_out(m))


@doctor.patch("/profil", response_model=Envelope[MedecinAdminOut], summary="Modifier ma présentation et ma disponibilité")
def update_profile(body: MedecinProfileIn, m=Depends(_me), db: Session = DB):
    return ok(doctors.admin_out(doctors.update_own_profile(db, m, body.model_dump(exclude_unset=True))),
              "Profil mis à jour")


@doctor.put("/horaires", response_model=Envelope[MedecinAdminOut], summary="Mes horaires de la semaine")
def my_schedule(body: list[HoraireItem], m=Depends(_me), db: Session = DB):
    doctors.set_horaires(db, m, body)
    db.commit()
    db.refresh(m)
    return ok(doctors.admin_out(m), "Horaires mis à jour")


def _absence_out(a, conflicts=None) -> dict:
    return {"id": a.id, "debut": a.debut, "fin": a.fin, "motif": a.motif, "rendez_vous_en_conflit": conflicts or []}


@doctor.get("/absences", response_model=Envelope[list[AbsenceOut]], summary="Mes absences à venir")
def my_absences(m=Depends(_me), db: Session = DB):
    return ok([_absence_out(a) for a in service.list_absences(db, m)])


@doctor.post("/absences", status_code=status.HTTP_201_CREATED, response_model=Envelope[AbsenceOut],
             summary="Déclarer une absence ou des congés (les créneaux sont bloqués)")
def add_absence(body: AbsenceIn, m=Depends(_me), db: Session = DB):
    absence, conflicts = service.add_absence(db, m, body.debut, body.fin, body.motif)
    message = "Absence enregistrée"
    if conflicts:
        message += f" : {len(conflicts)} rendez-vous déjà pris sur cette période, à traiter"
    return ok(_absence_out(absence, conflicts), message)


@doctor.delete("/absences/{absence_id}", response_model=Envelope[None], summary="Supprimer une absence")
def remove_absence(absence_id: int, m=Depends(_me), db: Session = DB):
    service.delete_absence(db, m, absence_id)
    return ok(message="Absence supprimée")


@doctor.get("/consultations", response_model=Envelope[Page[ConsultationOut]], summary="Mon agenda")
def agenda(statut: Statut | None = None, du: date | None = None, au: date | None = None,
           page: PageParams = Depends(), m=Depends(_me), db: Session = DB):
    return _page(db, service.doctor_query(m, statut, du, au), page, "medecin")


@doctor.get("/consultations/{numero}", response_model=Envelope[ConsultationOut], summary="Détail (dossier médical)")
def doctor_detail(numero: str, m=Depends(_me), db: Session = DB):
    return ok(service.serialize(db, service.for_doctor(db, m, numero), "medecin"))


@doctor.get("/consultations/{numero}/documents/{doc_id}/lien", response_model=Envelope[LinkOut],
            summary="Lien de téléchargement temporaire")
def doctor_doc_link(numero: str, doc_id: int, m=Depends(_me), db: Session = DB):
    return ok(service.document_link(db, service.for_doctor(db, m, numero), doc_id, "medecin"))


@doctor.post("/consultations/{numero}/ordonnance", status_code=status.HTTP_201_CREATED,
             response_model=Envelope[DocumentOut], summary="Joindre une ordonnance (PDF ou image)")
async def prescription(numero: str, fichier: UploadFile = File(...), user: User = USER, m=Depends(_me),
                       db: Session = DB, client: ClientInfo = CLIENT):
    c = service.for_doctor(db, m, numero)
    if c.statut not in ("en_cours", "terminee"):
        raise ApiError(409, "INVALID_TRANSITION", "L'ordonnance se joint pendant ou après la consultation")
    doc = service.add_document(db, c, user, "ordonnance", await _read(fichier), fichier.filename, client)
    return ok({"id": doc.id, "kind": doc.kind, "nom": doc.original_name, "mime": doc.mime, "taille": doc.size_bytes,
               "ajoute_le": doc.created_at}, "Ordonnance ajoutée")


@doctor.post("/consultations/{numero}/confirmer", response_model=Envelope[ConsultationOut], summary="Confirmer")
def confirm(numero: str, background: BackgroundTasks, user: User = USER, m=Depends(_me), db: Session = DB,
            client: ClientInfo = CLIENT):
    c = service.confirm(db, service.for_doctor(db, m, numero), user, client, background)
    return ok(service.serialize(db, c, "medecin"), "Consultation confirmée")


@doctor.post("/consultations/{numero}/refuser", response_model=Envelope[ConsultationOut], summary="Refuser")
def refuse(numero: str, body: RefuseIn, background: BackgroundTasks, user: User = USER, m=Depends(_me),
           db: Session = DB, client: ClientInfo = CLIENT):
    c = service.refuse(db, service.for_doctor(db, m, numero), user, body.motif, client, background)
    return ok(service.serialize(db, c, "medecin"), "Consultation refusée")


@doctor.post("/consultations/{numero}/demarrer", response_model=Envelope[ConsultationOut], summary="Démarrer")
def begin(numero: str, user: User = USER, m=Depends(_me), db: Session = DB):
    return ok(service.serialize(db, service.start(db, service.for_doctor(db, m, numero), user), "medecin"),
              "Consultation démarrée")


@doctor.post("/consultations/{numero}/absent", response_model=Envelope[ConsultationOut],
             summary="Le patient ne s'est pas présenté")
def no_show(numero: str, user: User = USER, m=Depends(_me), db: Session = DB, client: ClientInfo = CLIENT):
    c = service.mark_absent(db, service.for_doctor(db, m, numero), user, client)
    return ok(service.serialize(db, c, "medecin"), "Absence du patient enregistrée")


@doctor.get("/consultations/{numero}/agenda.ics", summary="Ajouter à mon agenda", response_class=Response,
            responses={200: {"content": {"text/calendar": {}}}})
def doctor_ics(numero: str, m=Depends(_me), db: Session = DB):
    return _ics(service.for_doctor(db, m, numero), "medecin")


@doctor.get("/consultations/{numero}/rejoindre", response_model=Envelope[JoinOut], summary="Lien de la salle vidéo")
def doctor_join(numero: str, m=Depends(_me), db: Session = DB):
    return ok(service.join(service.for_doctor(db, m, numero)))


@doctor.post("/consultations/{numero}/terminer", response_model=Envelope[ConsultationOut],
             summary="Terminer avec le compte-rendu")
def finish(numero: str, body: FinishIn, background: BackgroundTasks, user: User = USER, m=Depends(_me),
           db: Session = DB, client: ClientInfo = CLIENT):
    c = service.finish(db, service.for_doctor(db, m, numero), user, body, client, background)
    return ok(service.serialize(db, c, "medecin"), "Consultation terminée")


# ═════════════ Administration ═════════════

MANAGE_DOCTORS = Depends(require_permission("medecins.manage"))
READ_CONSULT = Depends(require_permission("consultations.read"))
MANAGE_CONSULT = Depends(require_permission("consultations.manage"))


@admin.get("/medecins", response_model=Envelope[list[MedecinAdminOut]], summary="Tous les médecins")
def admin_doctors(q: str | None = Query(None, max_length=100), db: Session = DB, _: User = MANAGE_DOCTORS):
    return ok([doctors.admin_out(m) for m in service.list_doctors(db, q, None, None, include_unavailable=True)])


@admin.post("/medecins", status_code=status.HTTP_201_CREATED, response_model=Envelope[MedecinAdminOut],
            summary="Créer un médecin (nouveau compte invité par email, ou compte médecin existant)")
def admin_create_doctor(body: AdminMedecinIn, background: BackgroundTasks, db: Session = DB,
                        client: ClientInfo = CLIENT, actor: User = MANAGE_DOCTORS):
    return ok(doctors.admin_out(doctors.create(db, actor, body, client, background)), "Médecin créé")


@admin.get("/medecins/{medecin_uuid}", response_model=Envelope[MedecinAdminOut], summary="Fiche d'un médecin")
def admin_doctor(medecin_uuid: str, db: Session = DB, _: User = MANAGE_DOCTORS):
    return ok(doctors.admin_out(service.get_doctor(db, medecin_uuid, public=False)))


@admin.patch("/medecins/{medecin_uuid}", response_model=Envelope[MedecinAdminOut],
             summary="Modifier la fiche (tarifs, licence, spécialité…)")
def admin_update_doctor(medecin_uuid: str, body: AdminMedecinUpdateIn, db: Session = DB, client: ClientInfo = CLIENT,
                        actor: User = MANAGE_DOCTORS):
    m = service.get_doctor(db, medecin_uuid, public=False)
    return ok(doctors.admin_out(doctors.update(db, actor, m, body.model_dump(exclude_unset=True), client)),
              "Fiche mise à jour")


@admin.put("/medecins/{medecin_uuid}/horaires", response_model=Envelope[MedecinAdminOut], summary="Horaires")
def admin_schedule(medecin_uuid: str, body: list[HoraireItem], db: Session = DB, _: User = MANAGE_DOCTORS):
    m = service.get_doctor(db, medecin_uuid, public=False)
    doctors.set_horaires(db, m, body)
    db.commit()
    db.refresh(m)
    return ok(doctors.admin_out(m), "Horaires mis à jour")


@admin.get("/medecins/{medecin_uuid}/absences", response_model=Envelope[list[AbsenceOut]], summary="Absences")
def admin_absences(medecin_uuid: str, db: Session = DB, _: User = MANAGE_DOCTORS):
    m = service.get_doctor(db, medecin_uuid, public=False)
    return ok([_absence_out(a) for a in service.list_absences(db, m)])


@admin.post("/medecins/{medecin_uuid}/absences", status_code=status.HTTP_201_CREATED,
            response_model=Envelope[AbsenceOut], summary="Déclarer une absence pour un médecin")
def admin_add_absence(medecin_uuid: str, body: AbsenceIn, db: Session = DB, _: User = MANAGE_DOCTORS):
    m = service.get_doctor(db, medecin_uuid, public=False)
    absence, conflicts = service.add_absence(db, m, body.debut, body.fin, body.motif)
    return ok(_absence_out(absence, conflicts), "Absence enregistrée")


@admin.delete("/medecins/{medecin_uuid}/absences/{absence_id}", response_model=Envelope[None],
              summary="Supprimer une absence")
def admin_remove_absence(medecin_uuid: str, absence_id: int, db: Session = DB, _: User = MANAGE_DOCTORS):
    service.delete_absence(db, service.get_doctor(db, medecin_uuid, public=False), absence_id)
    return ok(message="Absence supprimée")


@admin.post("/medecins/{medecin_uuid}/licence", response_model=Envelope[MedecinAdminOut],
            summary="Valider ou retirer la vérification de licence")
def admin_licence(medecin_uuid: str, body: LicenceIn, db: Session = DB, client: ClientInfo = CLIENT,
                  actor: User = MANAGE_DOCTORS):
    m = service.get_doctor(db, medecin_uuid, public=False)
    return ok(doctors.admin_out(doctors.set_licence(db, actor, m, body.verifiee, client)), "Licence mise à jour")


@admin.get("/consultations", response_model=Envelope[Page[ConsultationOut]],
           summary="Consultations (informations administratives, sans données médicales)")
def admin_list(statut: Statut | None = None, paiement: str | None = Query(None, pattern="^(en_attente|paye|echoue|rembourse)$"),
               medecin_uuid: str | None = None, q: str | None = Query(None, max_length=100), du: date | None = None,
               au: date | None = None, page: PageParams = Depends(), db: Session = DB, _: User = READ_CONSULT):
    return _page(db, service.admin_query(statut, paiement, medecin_uuid, q, du, au), page, "admin")


@admin.get("/consultations/stats", response_model=Envelope[dict], summary="Statistiques")
def admin_stats(db: Session = DB, _: User = READ_CONSULT):
    return ok(service.stats(db))


@admin.get("/consultations/{numero}", response_model=Envelope[ConsultationOut], summary="Détail administratif")
def admin_detail(numero: str, db: Session = DB, _: User = READ_CONSULT):
    return ok(service.serialize(db, service.get_by_numero(db, numero), "admin"))


@admin.get("/consultations/{numero}/documents/{doc_id}/lien", response_model=Envelope[LinkOut],
           summary="Lien vers la preuve de paiement (les documents médicaux ne sont pas accessibles)")
def admin_doc_link(numero: str, doc_id: int, db: Session = DB, _: User = READ_CONSULT):
    return ok(service.document_link(db, service.get_by_numero(db, numero), doc_id, "admin"))


@admin.post("/consultations/{numero}/paiement", response_model=Envelope[ConsultationOut],
            summary="Valider, refuser ou rembourser le paiement (après vérification de la preuve)")
def admin_payment(numero: str, body: PaymentDecisionIn, background: BackgroundTasks, db: Session = DB,
                  client: ClientInfo = CLIENT, actor: User = MANAGE_CONSULT):
    c = service.decide_payment(db, service.get_by_numero(db, numero), actor, body.statut, body.note, client,
                               background)
    return ok(service.serialize(db, c, "admin"), "Paiement mis à jour")


@admin.post("/consultations/{numero}/reattribuer", response_model=Envelope[ConsultationOut],
            summary="Confier la consultation à un autre médecin")
def admin_reassign(numero: str, body: ReassignIn, background: BackgroundTasks, db: Session = DB,
                   client: ClientInfo = CLIENT, actor: User = MANAGE_CONSULT):
    c = service.reassign(db, service.get_by_numero(db, numero), actor, body.medecin_uuid, client, background)
    return ok(service.serialize(db, c, "admin"), "Consultation réattribuée")


@admin.post("/consultations/{numero}/annuler", response_model=Envelope[ConsultationOut], summary="Annuler")
def admin_cancel(numero: str, body: AdminCancelIn, background: BackgroundTasks, db: Session = DB,
                 client: ClientInfo = CLIENT, actor: User = MANAGE_CONSULT):
    c = service.cancel_by_admin(db, service.get_by_numero(db, numero), actor, body.motif, client, background)
    return ok(service.serialize(db, c, "admin"), "Consultation annulée")


# ═════════════ Comptes de paiement (affichés dans les messages WhatsApp et au moment de payer) ═════════════

class PaymentAccountIn(BaseModel):
    nom: str = Field(min_length=2, max_length=200, description="Ex. Lumicash, EcoCash, Western Union")
    numero_compte: str | None = Field(default=None, max_length=50)
    nom_compte: str | None = Field(default=None, max_length=100, description="Titulaire du compte")
    instructions: str | None = Field(default=None, max_length=100, description="Étape ou consigne de paiement")
    actif: bool = True


def _account_out(m: ModePaiement) -> dict:
    return {"id": m.id, "nom": m.description, "numero_compte": m.numero_compte, "nom_compte": m.nom_compte,
            "instructions": m.etapepaiement, "actif": m.est_actif}


def _account(db: Session, account_id: int) -> ModePaiement:
    m = db.get(ModePaiement, account_id)
    if m is None:
        raise ApiError(404, "NOT_FOUND", "Compte de paiement introuvable")
    return m


def _apply_account(m: ModePaiement, body: PaymentAccountIn) -> ModePaiement:
    m.description, m.numero_compte, m.nom_compte = body.nom.strip(), body.numero_compte, body.nom_compte
    m.etapepaiement, m.est_actif = body.instructions, body.actif
    return m


@admin.get("/modes-paiement", response_model=Envelope[list[dict]], summary="Comptes de paiement (actifs et inactifs)")
def admin_accounts(db: Session = DB, _: User = MANAGE_CONSULT):
    return ok([_account_out(m) for m in db.scalars(select(ModePaiement).order_by(ModePaiement.id))])


@admin.post("/modes-paiement", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
            summary="Ajouter un compte de paiement (aucun paiement en ligne : informations affichées au client)")
def admin_add_account(body: PaymentAccountIn, db: Session = DB, _: User = MANAGE_CONSULT):
    m = _apply_account(ModePaiement(), body)
    db.add(m)
    db.commit()
    return ok(_account_out(m), "Compte de paiement ajouté")


@admin.put("/modes-paiement/{account_id}", response_model=Envelope[dict], summary="Modifier un compte de paiement")
def admin_edit_account(account_id: int, body: PaymentAccountIn, db: Session = DB, _: User = MANAGE_CONSULT):
    m = _apply_account(_account(db, account_id), body)
    db.commit()
    return ok(_account_out(m), "Compte de paiement mis à jour")


@admin.delete("/modes-paiement/{account_id}", response_model=Envelope[None],
              summary="Retirer un compte (désactivé : l'historique des consultations et commandes reste lisible)")
def admin_delete_account(account_id: int, db: Session = DB, _: User = MANAGE_CONSULT):
    m = _account(db, account_id)
    m.est_actif = False
    db.commit()
    return ok(message="Compte de paiement retiré")
