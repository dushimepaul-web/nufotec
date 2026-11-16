"""Consultations : réservation, cycle de vie, documents, vidéo, avis, notifications.

Paiement : aucun paiement en ligne. Le patient paie (Lumicash, EcoCash, Western Union) puis téléverse sa preuve
dans l'app ; l'administration la valide, ce qui ouvre le lien vidéo. Les autres échanges se font sur WhatsApp.

Cycle de vie (toute autre transition est refusée) :
    en_attente ──confirmer (médecin)──► confirmee ──démarrer (médecin)──► en_cours ──terminer──► terminee
        │ refuser (médecin) ► refusee        ├── patient absent (médecin, après l'heure) ► absent
        └── annuler (patient ≥ 2 h avant, admin, ou expiration automatique) ► annulee ◄─┘
Le patient peut aussi DÉPLACER son rendez-vous vers un autre créneau libre (≥ 2 h avant).
Chaque changement est enregistré dans consultation_history."""

import secrets
from datetime import UTC, date, datetime, timedelta
from decimal import Decimal

from fastapi import BackgroundTasks
from sqlalchemy import and_, func, or_, select
from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session

from app.core import audit, push
from app.core.client import ClientInfo
from app.core.config import get_settings
from app.core.files import delete_private, save_private, signed_link
from app.core.phone import InvalidPhone, normalize_phone, sql_clean_phone
from app.core.helpers import like_pattern, minutes_ago, now, phone_or_422
from app.core.mailer import deliver_pending, queue_email, simple_email
from app.core.pagination import PageParams, paginate
from app.core.responses import ApiError
from app.core.uploads import public_url
from app.modules.consultations.prices import foreign_currency, price_labels
from app.models import (
    MedecinAbsence,
    Consultation,
    ConsultationAvis,
    ConsultationDocument,
    ConsultationHistory,
    Medecin,
    ModePaiement,
    Paiement,
    Pays,
    User,
)
from app.modules.consultations.slots import (
    ACTIVE,
    DEFAULT_MINUTES,
    available_now,
    duration,
    is_free,
    to_local_naive,
)

PRIVATE_FOLDER = "consultations"
MAX_DOCUMENTS = 10
TRANSITIONS = {
    "en_attente": {"confirmee", "refusee", "annulee"},
    "confirmee": {"en_cours", "annulee", "absent"},
    "en_cours": {"terminee"},
}


# ───────────────────────── outils ─────────────────────────

def _end(c: Consultation) -> datetime:
    return c.date_souhaitee + timedelta(minutes=c.duree_minutes or DEFAULT_MINUTES)


def _not_found() -> ApiError:
    return ApiError(404, "NOT_FOUND", "Consultation introuvable")


def _move(db: Session, c: Consultation, to: str, actor: User | None, role: str, note: str | None = None) -> None:
    current = c.statut or "en_attente"
    if to not in TRANSITIONS.get(current, set()):
        raise ApiError(409, "INVALID_TRANSITION", f"Impossible de passer de « {current} » à « {to} »",
                       {"statut": current, "demande": to})
    c.statut = to
    db.add(ConsultationHistory(consultation_id=c.id, from_status=current, to_status=to,
                               actor_id=actor.id if actor else None, actor_role=role,
                               note=(note or None) and note[:500], created_at=now()))


def _notify(db: Session, background: BackgroundTasks, users: list[User], subject: str, paragraphs: list[str],
            data: dict | None = None, link: tuple[str, str] | None = None) -> None:
    """Email (en file, dans la transaction) + notification push (après la réponse)."""
    for user in users:
        if user is None or not user.is_active or user.deleted_at is not None:
            continue
        text, html = simple_email(user.prenom, subject, paragraphs, link)
        queue_email(db, user.email, f"Nufotec – {subject}", text, html)
    background.add_task(deliver_pending)
    ids = [u.id for u in users if u is not None]
    if ids:
        background.add_task(push.send_to_users, ids, subject, paragraphs[0], data or {})


def _when(c: Consultation) -> str:
    return f"{c.date_souhaitee:%d/%m/%Y à %H:%M} (heure du Burundi)"


def get_by_numero(db: Session, numero: str) -> Consultation:
    c = db.scalar(select(Consultation).where(Consultation.numero_consultation == numero))
    if c is None:
        raise _not_found()
    return c


def for_patient(db: Session, user: User, numero: str) -> Consultation:
    c = get_by_numero(db, numero)
    if c.patient_id != user.id:
        raise _not_found()  # 404 et non 403 : on ne révèle pas l'existence de la consultation
    return c


def for_doctor(db: Session, medecin: Medecin, numero: str) -> Consultation:
    c = get_by_numero(db, numero)
    if c.medecin_id != medecin.id:
        raise _not_found()
    return c


def current_medecin(db: Session, user: User) -> Medecin:
    medecin = db.scalar(select(Medecin).where(Medecin.user_id == user.id))
    if user.role.slug != "medecin" or medecin is None:
        raise ApiError(403, "NOT_A_DOCTOR", "Réservé aux médecins disposant d'une fiche")
    return medecin


# ───────────────────────── sérialisation ─────────────────────────

def can_cancel(c: Consultation) -> bool:
    return c.statut in ("en_attente", "confirmee") and \
        c.date_souhaitee - now() >= timedelta(hours=get_settings().cancel_min_hours)


def join_window(c: Consultation) -> tuple[datetime, datetime]:
    s = get_settings()
    return (c.date_souhaitee - timedelta(minutes=s.join_before_minutes),
            _end(c) + timedelta(minutes=s.join_after_minutes))


def can_join(c: Consultation) -> bool:
    opens, closes = join_window(c)
    return bool(c.type == "video" and c.room_url and c.statut in ("confirmee", "en_cours")
                and c.paiement_statut == "paye" and opens <= now() <= closes)


def _person(user: User | None) -> dict | None:
    if user is None:
        return None
    return {"uuid": user.uuid, "nom": user.nom, "prenom": user.prenom, "photo_url": public_url("Users", user.photo)}


def _profile_phone(user: User) -> str | None:
    try:
        return normalize_phone(user.telephone) if user.telephone else None
    except InvalidPhone:
        return None


MEDICAL_FIELDS = ("symptomes", "duree_symptomes", "consultation_precedente", "age", "poids", "taille",
                  "diagnostic", "traitement", "notes_medecin", "avis")


def serialize_for_patient(db: Session, c: Consultation, user: User) -> dict:
    """Consultation du compte : tout. Consultation retrouvée seulement par le numéro WhatsApp (réservée sans compte) :
    rendez-vous, expert, statut et prix, mais aucune donnée médicale ni document — un numéro de téléphone ne
    suffit pas à prouver qu'on est le patient."""
    data = serialize(db, c, "patient")
    if c.patient_id == user.id:
        data["lie_par_telephone"] = False
        return data
    for key in MEDICAL_FIELDS:
        data[key] = None
    data.update(documents=[], peut_annuler=False, peut_deplacer=False, peut_rejoindre=False, lie_par_telephone=True)
    return data


def serialize(db: Session, c: Consultation, viewer: str) -> dict:
    """viewer : « patient », « medecin » ou « admin » (sans données médicales)."""
    docs = list(db.scalars(select(ConsultationDocument).where(ConsultationDocument.consultation_id == c.id)
                           .order_by(ConsultationDocument.id)))
    if viewer == "admin":
        docs = [d for d in docs if d.kind == "preuve_paiement"]
    data = {
        "numero": c.numero_consultation, "statut": c.statut, "paiement_statut": c.paiement_statut, "type": c.type,
        "debut": c.date_souhaitee, "fin": _end(c), "duree_minutes": c.duree_minutes or DEFAULT_MINUTES,
        "medecin": _person(c.medecin.user) if c.medecin else None, "specialite": c.medecin.specialite if c.medecin else None,
        "patient": _person(c.patient) or {"uuid": None, "nom": c.patient_name, "prenom": None},
        "prix_ht": c.prix_ht, "tva": c.tva, "prix_ttc": c.prix_ttc, "devise": c.devise,
        "mode_paiement": c.mode_paiement, "motif_annulation": c.motif_annulation,
        "peut_annuler": viewer == "patient" and can_cancel(c),
        "peut_deplacer": viewer == "patient" and can_cancel(c),
        "peut_rejoindre": viewer in ("patient", "medecin") and can_join(c),
        "created_at": c.created_at,
        "documents": [{"id": d.id, "kind": d.kind, "nom": d.original_name, "mime": d.mime, "taille": d.size_bytes,
                       "ajoute_le": d.created_at} for d in docs],
    }
    if viewer in ("patient", "medecin"):
        data.update({
            "symptomes": c.symptomes, "duree_symptomes": c.duree_symptomes,
            "consultation_precedente": c.consultation_precedente == "yes", "age": c.age, "poids": c.poids,
            "taille": c.taille, "whatsapp": c.whatsapppatient,
            # Le compte-rendu n'est visible du patient qu'une fois la consultation terminée.
            "diagnostic": c.diagnostic if viewer == "medecin" or c.statut == "terminee" else None,
            "traitement": c.traitement if viewer == "medecin" or c.statut == "terminee" else None,
            "notes_medecin": c.notes_medecin if viewer == "medecin" else None,
        })
        avis = db.scalar(select(ConsultationAvis).where(ConsultationAvis.consultation_id == c.id))
        data["avis"] = {"note": avis.note, "commentaire": avis.commentaire} if avis else None
    if viewer == "admin":
        rows = db.scalars(select(ConsultationHistory).where(ConsultationHistory.consultation_id == c.id)
                          .order_by(ConsultationHistory.id))
        data["historique"] = [{"de": h.from_status, "vers": h.to_status, "par": h.actor_role, "note": h.note,
                               "le": h.created_at} for h in rows]
    return data


# ───────────────────────── médecins (public) ─────────────────────────

def doctor_public(m: Medecin, detail: bool = False) -> dict:
    data = {
        "uuid": m.uuid, "nom": m.user.nom, "prenom": m.user.prenom, "photo_url": public_url("Users", m.user.photo),
        "specialite": m.specialite, "annees_experience": m.annees_experience,
        "langues": [x.strip().capitalize() for x in (m.langues_parlees or "").split(",") if x.strip()],
        "bio": m.bio, "prix_usd": m.prix_usd, "prix_bif": m.prix_bif, "devise_etranger": foreign_currency(m),
        "tarifs": price_labels(m), "note_moyenne": m.note_moyenne,
        "nombre_avis": m.nombre_avis, "disponible": bool(m.est_disponible),
        "disponible_maintenant": available_now(m), "licence_verifiee": bool(m.user.est_verifie),
        "duree_minutes": m.duree_consultation_minutes or DEFAULT_MINUTES,
    }
    if detail:
        data["diplomes"] = m.diplomes
        data["horaires"] = [{"jour": h.jour_semaine, "debut": h.heure_debut, "fin": h.heure_fin}
                            for h in m.horaires if h.est_actif]
    return data


def list_doctors(db: Session, q: str | None, specialite: str | None, langue: str | None,
                 include_unavailable: bool = False) -> list[Medecin]:
    query = select(Medecin).join(User, User.id == Medecin.user_id).where(User.deleted_at.is_(None))
    if not include_unavailable:
        query = query.where(User.is_active.is_(True), Medecin.est_disponible.is_(True))
    if q and q.strip():
        p = like_pattern(q.strip())
        query = query.where(or_(User.nom.like(p), User.prenom.like(p), Medecin.specialite.like(p)))
    if specialite:
        query = query.where(Medecin.specialite.like(like_pattern(specialite)))
    if langue:
        query = query.where(Medecin.langues_parlees.like(like_pattern(langue)))
    return list(db.scalars(query.order_by(Medecin.note_moyenne.desc(), User.nom)).unique())


def get_doctor(db: Session, medecin_uuid: str, public: bool = True) -> Medecin:
    m = db.scalar(select(Medecin).where(Medecin.uuid == medecin_uuid))
    if m is None or (public and (m.user.deleted_at is not None or not m.user.is_active)):
        raise ApiError(404, "NOT_FOUND", "Expert introuvable")
    return m


def payment_methods(db: Session) -> list[dict]:
    rows = db.scalars(select(ModePaiement).where(ModePaiement.est_actif.is_(True)).order_by(ModePaiement.id))
    return [{"id": m.id, "nom": m.description, "numero_compte": m.numero_compte, "nom_compte": m.nom_compte}
            for m in rows]


# ───────────────────────── réservation (patient) ─────────────────────────

def _price(medecin: Medecin, pays_id: int | None) -> tuple[Decimal, str]:
    if pays_id == get_settings().burundi_pays_id:
        if medecin.prix_bif is None:
            raise ApiError(409, "PRICE_NOT_SET", "Le tarif de cet expert n'est pas encore défini")
        return medecin.prix_bif, "BIF"
    if medecin.prix_usd is None:
        raise ApiError(409, "PRICE_NOT_SET", "Le tarif de cet expert n'est pas encore défini")
    return medecin.prix_usd, "USD"


def _age(user: User) -> int | None:
    if not user.date_naissance:
        return None
    today = date.today()
    born = user.date_naissance
    return today.year - born.year - ((today.month, today.day) < (born.month, born.day))


def book(db: Session, user: User | None, data, client: ClientInfo, background: BackgroundTasks) -> Consultation:
    """Réserver. Sans compte (user=None), le nom et le WhatsApp viennent du formulaire (data.nom_complet, data.whatsapp)."""
    medecin = get_doctor(db, data.medecin_uuid)
    if user is None and client.ip and (db.scalar(select(func.count()).select_from(Consultation).where(
            Consultation.ip_creation == client.ip, Consultation.patient_id.is_(None),
            Consultation.created_at > minutes_ago(60))) or 0) >= 5:
        raise ApiError(429, "TOO_MANY_REQUESTS", "Trop de demandes depuis ce réseau. Réessayez plus tard.",
                       {"retry_after_seconds": 3600}, {"Retry-After": "3600"})
    start = to_local_naive(data.debut)
    pays_id = data.pays_residence_id or (user.pays_id if user else None)
    if pays_id and not db.get(Pays, pays_id):
        raise ApiError(422, "VALIDATION_ERROR", "Données invalides", [{"field": "pays_residence_id", "message": "Pays inconnu"}])
    mode = None
    if data.mode_paiement_id:
        mode = db.get(ModePaiement, data.mode_paiement_id)
        if mode is None or not mode.est_actif:
            raise ApiError(422, "VALIDATION_ERROR", "Données invalides",
                           [{"field": "mode_paiement_id", "message": "Mode de paiement inconnu"}])
    prix, devise = _price(medecin, pays_id)
    patient_whatsapp = phone_or_422(data.whatsapp, "whatsapp") if data.whatsapp else (user.telephone if user else None)
    patient_name = f"{user.prenom} {user.nom}" if user else data.nom_complet
    if not patient_whatsapp:
        raise ApiError(422, "VALIDATION_ERROR", "Données invalides",
                       [{"field": "whatsapp", "message": "Numéro WhatsApp obligatoire"}])

    # Verrou sur le médecin : deux réservations simultanées du même créneau passent l'une après l'autre.
    db.scalar(select(Medecin.id).where(Medecin.id == medecin.id).with_for_update())
    if not is_free(db, medecin, start):
        raise ApiError(409, "SLOT_UNAVAILABLE", "Ce créneau n'est pas disponible, choisissez-en un autre")

    c = Consultation(
        patient_id=user.id if user else None, patient_name=patient_name[:100], medecin_id=medecin.id,
        numero_consultation=f"NUF-{now():%Y%m%d}-{secrets.token_hex(3).upper()}", type=data.type,
        poids=data.poids, taille=data.taille, country_id=pays_id, age=data.age if data.age is not None else (_age(user) if user else None),
        whatsapppatient=patient_whatsapp,
        whatsapp_number=user.telephone if user else patient_whatsapp, symptomes=data.symptomes.strip(), duree_symptomes=data.duree_symptomes,
        consultation_precedente="yes" if data.consultation_precedente else "no", date_souhaitee=start,
        duree_minutes=int(duration(medecin).total_seconds() // 60), statut="en_attente", honoraires_consultation=prix, prix_ht=prix, devise=devise,
        tva=Decimal(str(get_settings().consultation_tva)), paiement_statut="en_attente",
        mode_paiement=mode.description[:50] if mode else None, ip_creation=client.ip or "",
    )
    db.add(c)
    try:
        db.flush()
    except IntegrityError:
        db.rollback()
        raise ApiError(409, "SLOT_UNAVAILABLE", "Ce créneau vient d'être réservé, choisissez-en un autre") from None
    db.add(ConsultationHistory(consultation_id=c.id, from_status=None, to_status="en_attente", actor_id=user.id if user else None,
                               actor_role="patient", created_at=now()))
    audit.activity(db, user.id if user else None, "consultation_booked", client,
                   f"Consultation {c.numero_consultation} réservée" + ("" if user else " (sans compte)"),
                   module="consultations", item_id=c.id, item_name=c.numero_consultation)
    _notify(db, background, [medecin.user], "Nouvelle demande de consultation",
            [f"{patient_name} demande une consultation ({data.type}) le {_when(c)}.",
             f"WhatsApp du patient : {patient_whatsapp}",
             f"Pays de résidence : {pays.pays if (pays := db.get(Pays, pays_id) if pays_id else None) else '—'}",
             f"N° de suivi : {c.numero_consultation}",
             "Ouvrez votre agenda pour la confirmer ou la refuser."],
            {"type": "consultation", "numero": c.numero_consultation})
    db.commit()
    db.refresh(c)
    return c


def list_mine(db: Session, query, params: PageParams):
    rows, meta = paginate(db, query, params)
    return rows, meta


def patient_query(user: User, statut: str | None, a_venir: bool | None):
    """Consultations du compte, et celles réservées SANS compte avec le WhatsApp du profil."""
    condition = Consultation.patient_id == user.id
    phone = _profile_phone(user)
    if phone:
        condition = or_(condition, and_(Consultation.patient_id.is_(None),
                                        sql_clean_phone(Consultation.whatsapppatient) == phone))
    q = select(Consultation).where(condition)
    if statut:
        q = q.where(Consultation.statut == statut)
    if a_venir is True:
        q = q.where(Consultation.date_souhaitee >= now() - timedelta(hours=1)).order_by(Consultation.date_souhaitee)
    else:
        q = q.order_by(Consultation.date_souhaitee.desc())
    return q


def add_document(db: Session, c: Consultation, user: User | None, kind: str, raw: bytes, original: str | None,
                 client: ClientInfo) -> ConsultationDocument:
    if c.statut in ("annulee", "refusee"):
        raise ApiError(409, "CONSULTATION_CLOSED", "Cette consultation est close")
    count = db.scalar(select(func.count()).select_from(ConsultationDocument)
                      .where(ConsultationDocument.consultation_id == c.id)) or 0
    if count >= MAX_DOCUMENTS:
        raise ApiError(409, "TOO_MANY_DOCUMENTS", f"{MAX_DOCUMENTS} documents au maximum par consultation")
    stored, mime = save_private(raw, PRIVATE_FOLDER)
    doc = ConsultationDocument(consultation_id=c.id, uploaded_by=user.id if user else None, kind=kind, stored_name=stored,
                               original_name=(original or "")[:255] or None, mime=mime, size_bytes=len(raw),
                               created_at=now())
    db.add(doc)
    audit.activity(db, user.id if user else None, "consultation_document", client, f"Document ({kind}) ajouté",
                   module="consultations", item_id=c.id, item_name=c.numero_consultation)
    try:
        db.commit()
    except Exception:
        delete_private(PRIVATE_FOLDER, stored)
        raise
    return doc


def document_link(db: Session, c: Consultation, doc_id: int, viewer: str) -> dict:
    doc = db.get(ConsultationDocument, doc_id)
    if doc is None or doc.consultation_id != c.id or (viewer == "admin" and doc.kind != "preuve_paiement"):
        raise ApiError(404, "NOT_FOUND", "Document introuvable")
    url, expires = signed_link(doc.id)
    return {"url": url, "expire_le": expires}


def submit_payment(db: Session, c: Consultation, user: User | None, mode_id: int, raw: bytes, original: str | None,
                   client: ClientInfo) -> Consultation:
    """Le patient téléverse la preuve de son paiement (fait hors ligne) ; l'administration la validera."""
    if c.paiement_statut == "paye":
        raise ApiError(409, "ALREADY_PAID", "Cette consultation est déjà payée")
    if c.statut not in ("en_attente", "confirmee"):
        raise ApiError(409, "CONSULTATION_CLOSED", "Cette consultation ne peut plus être payée")
    mode = db.get(ModePaiement, mode_id)
    if mode is None or not mode.est_actif:
        raise ApiError(422, "VALIDATION_ERROR", "Données invalides",
                       [{"field": "mode_paiement_id", "message": "Mode de paiement inconnu"}])
    c.mode_paiement = mode.description[:50]
    c.paiement_statut = "en_attente"
    add_document(db, c, user, "preuve_paiement", raw, original, client)
    return c


def cancel_by_patient(db: Session, c: Consultation, user: User, motif: str | None, client: ClientInfo,
                      background: BackgroundTasks) -> Consultation:
    if not can_cancel(c):
        raise ApiError(409, "CANNOT_CANCEL",
                       f"Annulation possible jusqu'à {get_settings().cancel_min_hours} h avant le rendez-vous")
    _move(db, c, "annulee", user, "patient", motif)
    c.motif_annulation = (motif or "Annulée par le patient")[:255]
    audit.activity(db, user.id, "consultation_cancelled", client, c.motif_annulation, module="consultations",
                   item_id=c.id, item_name=c.numero_consultation)
    _notify(db, background, [c.medecin.user], "Consultation annulée",
            [f"La consultation {c.numero_consultation} du {_when(c)} a été annulée par le patient."],
            {"type": "consultation", "numero": c.numero_consultation})
    db.commit()
    return c


def reschedule(db: Session, c: Consultation, user: User, new_start, client: ClientInfo,
               background: BackgroundTasks) -> Consultation:
    """Déplacer vers un autre créneau libre du même médecin (≥ 2 h avant le rendez-vous actuel)."""
    if not can_cancel(c):
        raise ApiError(409, "CANNOT_RESCHEDULE",
                       f"Le rendez-vous peut être déplacé jusqu'à {get_settings().cancel_min_hours} h avant")
    start_at = to_local_naive(new_start)
    if start_at == c.date_souhaitee:
        raise ApiError(409, "SAME_SLOT", "C'est déjà l'heure de votre rendez-vous")
    db.scalar(select(Medecin.id).where(Medecin.id == c.medecin_id).with_for_update())
    if not is_free(db, c.medecin, start_at, exclude_id=c.id):
        raise ApiError(409, "SLOT_UNAVAILABLE", "Ce créneau n'est pas disponible, choisissez-en un autre")
    old = c.date_souhaitee
    c.date_souhaitee = start_at
    if c.statut == "confirmee":
        c.date_confirmee = start_at
    c.duree_minutes = int(duration(c.medecin).total_seconds() // 60)
    c.rappel_envoye_at = c.rappel_veille_at = None
    db.add(ConsultationHistory(consultation_id=c.id, from_status=c.statut, to_status=c.statut, actor_id=user.id,
                               actor_role="patient", note=f"Déplacée du {old:%d/%m/%Y %H:%M} au {start_at:%d/%m/%Y %H:%M}",
                               created_at=now()))
    try:
        db.flush()
    except IntegrityError:
        db.rollback()
        raise ApiError(409, "SLOT_UNAVAILABLE", "Ce créneau vient d'être réservé, choisissez-en un autre") from None
    audit.activity(db, user.id, "consultation_rescheduled", client, f"{old:%d/%m/%Y %H:%M} → {start_at:%d/%m/%Y %H:%M}",
                   module="consultations", item_id=c.id, item_name=c.numero_consultation)
    _notify(db, background, [c.medecin.user], "Rendez-vous déplacé",
            [f"Le patient a déplacé la consultation {c.numero_consultation} : nouveau rendez-vous le {_when(c)}."],
            {"type": "consultation", "numero": c.numero_consultation})
    db.commit()
    return c


def mark_absent(db: Session, c: Consultation, user: User, client: ClientInfo) -> Consultation:
    if now() < c.date_souhaitee + timedelta(minutes=10):
        raise ApiError(409, "TOO_EARLY", "Attendez au moins 10 minutes après l'heure du rendez-vous")
    _move(db, c, "absent", user, "medecin", "Le patient ne s'est pas présenté")
    audit.activity(db, user.id, "consultation_no_show", client, None, module="consultations", item_id=c.id,
                   item_name=c.numero_consultation)
    db.commit()
    return c


def calendar_ics(c: Consultation, viewer: str) -> str:
    """Fichier iCalendar (Google Agenda, Outlook, iPhone)."""
    from app.core.types import localize

    def stamp(value: datetime) -> str:
        return localize(value).astimezone(UTC).strftime("%Y%m%dT%H%M%SZ")

    other = f"Dr {c.medecin.user.nom}" if viewer == "patient" else (c.patient_name or "Patient")
    description = f"Consultation {c.numero_consultation} ({c.type})."
    if c.room_url and c.type == "video":
        description += " Le lien vidéo s'ouvre dans l'application Nufotec 10 minutes avant."
    lines = ["BEGIN:VCALENDAR", "VERSION:2.0", "PRODID:-//Nufotec//Consultations//FR", "CALSCALE:GREGORIAN",
             "BEGIN:VEVENT", f"UID:{c.numero_consultation}@nufotec", f"DTSTAMP:{stamp(now())}",
             f"DTSTART:{stamp(c.date_souhaitee)}", f"DTEND:{stamp(_end(c))}",
             f"SUMMARY:Consultation Nufotec – {other}", f"DESCRIPTION:{description}",
             "BEGIN:VALARM", "TRIGGER:-PT30M", "ACTION:DISPLAY", "DESCRIPTION:Consultation Nufotec", "END:VALARM",
             "END:VEVENT", "END:VCALENDAR"]
    return "\r\n".join(lines) + "\r\n"


# ───────────────────────── absences du médecin ─────────────────────────

def add_absence(db: Session, m: Medecin, debut, fin, motif: str | None) -> tuple[MedecinAbsence, list[str]]:
    start_at, end_at = to_local_naive(debut), to_local_naive(fin)
    if end_at <= start_at:
        raise ApiError(422, "INVALID_RANGE", "La fin de l'absence doit être après le début")
    if end_at - start_at > timedelta(days=366):
        raise ApiError(422, "INVALID_RANGE", "Une absence ne peut pas dépasser un an")
    if end_at <= now():
        raise ApiError(422, "INVALID_RANGE", "Cette période est déjà passée")
    absence = MedecinAbsence(medecin_id=m.id, debut=start_at, fin=end_at, motif=(motif or None), created_at=now())
    db.add(absence)
    db.commit()
    # Les rendez-vous déjà pris pendant l'absence ne sont pas supprimés : on les signale au médecin.
    conflicts = db.scalars(select(Consultation.numero_consultation).where(
        Consultation.medecin_id == m.id, Consultation.statut.in_(ACTIVE),
        Consultation.date_souhaitee < end_at, Consultation.date_souhaitee >= start_at - timedelta(hours=2)))
    return absence, list(conflicts)


def list_absences(db: Session, m: Medecin) -> list[MedecinAbsence]:
    return list(db.scalars(select(MedecinAbsence).where(MedecinAbsence.medecin_id == m.id,
                                                        MedecinAbsence.fin > now()).order_by(MedecinAbsence.debut)))


def delete_absence(db: Session, m: Medecin, absence_id: int) -> None:
    absence = db.get(MedecinAbsence, absence_id)
    if absence is None or absence.medecin_id != m.id:
        raise ApiError(404, "NOT_FOUND", "Absence introuvable")
    db.delete(absence)
    db.commit()


def join(c: Consultation) -> dict:
    if c.type != "video":
        raise ApiError(409, "NOT_VIDEO", "Cette consultation n'est pas en vidéo")
    if c.paiement_statut != "paye":
        raise ApiError(409, "PAYMENT_REQUIRED", "Le paiement doit être validé avant la consultation")
    if c.statut not in ("confirmee", "en_cours") or not c.room_url:
        raise ApiError(409, "NOT_CONFIRMED", "La consultation n'est pas encore confirmée par le médecin")
    opens, closes = join_window(c)
    if not opens <= now() <= closes:
        raise ApiError(409, "NOT_OPEN_YET" if now() < opens else "SESSION_OVER",
                       "La salle n'est pas ouverte" if now() < opens else "La consultation est terminée",
                       {"ouvre_le": opens.isoformat(), "ferme_le": closes.isoformat()})
    return {"url": c.room_url, "salle": c.room_id, "ouvre_le": opens, "ferme_le": closes}


def review(db: Session, c: Consultation, user: User, note: int, commentaire: str | None) -> ConsultationAvis:
    if c.statut != "terminee":
        raise ApiError(409, "NOT_FINISHED", "Vous pourrez donner votre avis après la consultation")
    if db.scalar(select(ConsultationAvis.id).where(ConsultationAvis.consultation_id == c.id)):
        raise ApiError(409, "ALREADY_REVIEWED", "Vous avez déjà donné votre avis")
    avis = ConsultationAvis(consultation_id=c.id, patient_id=user.id, medecin_id=c.medecin_id, note=note,
                            commentaire=(commentaire or "").strip() or None, created_at=now())
    db.add(avis)
    m = c.medecin
    n = m.nombre_avis or 0
    m.note_moyenne = (Decimal(m.note_moyenne or 0) * n + note) / (n + 1)
    m.nombre_avis = n + 1
    db.commit()
    return avis


# ───────────────────────── médecin ─────────────────────────

def doctor_query(medecin: Medecin, statut: str | None, du: date | None, au: date | None):
    q = select(Consultation).where(Consultation.medecin_id == medecin.id)
    if statut:
        q = q.where(Consultation.statut == statut)
    if du:
        q = q.where(Consultation.date_souhaitee >= datetime.combine(du, datetime.min.time()))
    if au:
        q = q.where(Consultation.date_souhaitee < datetime.combine(au + timedelta(days=1), datetime.min.time()))
    return q.order_by(Consultation.date_souhaitee)


def confirm(db: Session, c: Consultation, user: User, client: ClientInfo, background: BackgroundTasks) -> Consultation:
    if c.date_souhaitee < now():
        raise ApiError(409, "IN_THE_PAST", "Ce créneau est déjà passé")
    _move(db, c, "confirmee", user, "medecin")
    c.date_confirmee = c.date_souhaitee
    if c.type == "video":
        # Nom de salle long et aléatoire : impossible à deviner.
        c.room_id = f"nufotec-{secrets.token_urlsafe(18)}"
        c.room_url = f"{get_settings().jitsi_base_url.rstrip('/')}/{c.room_id}"
    audit.activity(db, user.id, "consultation_confirmed", client, None, module="consultations", item_id=c.id,
                   item_name=c.numero_consultation)
    lines = [f"Votre consultation du {_when(c)} est confirmée par le Dr {c.medecin.user.nom}."]
    if c.paiement_statut != "paye":
        lines.append("Téléversez votre preuve de paiement dans l'application : le lien vidéo s'ouvrira dès que le "
                     "paiement sera validé par notre équipe.")
    _notify(db, background, [c.patient], "Consultation confirmée", lines,
            {"type": "consultation", "numero": c.numero_consultation})
    db.commit()
    return c


def refuse(db: Session, c: Consultation, user: User, motif: str, client: ClientInfo,
           background: BackgroundTasks) -> Consultation:
    _move(db, c, "refusee", user, "medecin", motif)
    c.motif_annulation = motif[:255]
    audit.activity(db, user.id, "consultation_refused", client, motif, module="consultations", item_id=c.id,
                   item_name=c.numero_consultation)
    lines = [f"Le médecin ne peut pas assurer la consultation du {_when(c)}.", f"Motif : {motif}"]
    if c.paiement_statut == "paye":
        lines.append("Votre paiement vous sera remboursé ; notre équipe vous contactera.")
    _notify(db, background, [c.patient], "Consultation refusée", lines,
            {"type": "consultation", "numero": c.numero_consultation})
    db.commit()
    return c


def start(db: Session, c: Consultation, user: User) -> Consultation:
    opens, _ = join_window(c)
    if now() < opens:
        raise ApiError(409, "TOO_EARLY", "La consultation ne peut pas encore commencer",
                       {"ouvre_le": opens.isoformat()})
    _move(db, c, "en_cours", user, "medecin")
    c.date_debut = now()
    db.commit()
    return c


def finish(db: Session, c: Consultation, user: User, data, client: ClientInfo,
           background: BackgroundTasks) -> Consultation:
    _move(db, c, "terminee", user, "medecin")
    c.date_fin = now()
    c.diagnostic, c.traitement, c.notes_medecin = data.diagnostic, data.traitement, data.notes_medecin
    audit.activity(db, user.id, "consultation_finished", client, None, module="consultations", item_id=c.id,
                   item_name=c.numero_consultation)
    _notify(db, background, [c.patient], "Compte-rendu disponible",
            [f"Votre consultation {c.numero_consultation} est terminée.",
             "Le diagnostic et le traitement sont disponibles dans l'application. Vous pouvez aussi donner votre avis."],
            {"type": "consultation", "numero": c.numero_consultation})
    db.commit()
    return c


# ───────────────────────── administration ─────────────────────────

def admin_query(statut: str | None, paiement: str | None, medecin_uuid: str | None, q: str | None,
                du: date | None, au: date | None):
    query = select(Consultation)
    if statut:
        query = query.where(Consultation.statut == statut)
    if paiement:
        query = query.where(Consultation.paiement_statut == paiement)
    if medecin_uuid:
        query = query.join(Medecin, Medecin.id == Consultation.medecin_id).where(Medecin.uuid == medecin_uuid)
    if q and q.strip():
        p = like_pattern(q.strip())
        query = query.where(or_(Consultation.numero_consultation.like(p), Consultation.patient_name.like(p)))
    if du:
        query = query.where(Consultation.date_souhaitee >= datetime.combine(du, datetime.min.time()))
    if au:
        query = query.where(Consultation.date_souhaitee < datetime.combine(au + timedelta(days=1), datetime.min.time()))
    return query.order_by(Consultation.date_souhaitee.desc())


def stats(db: Session) -> dict:
    def grouped(column):
        return dict(db.execute(select(column, func.count()).group_by(column)).all())

    revenue = db.execute(select(Consultation.devise, func.sum(Consultation.prix_ttc))
                         .where(Consultation.paiement_statut == "paye").group_by(Consultation.devise)).all()
    upcoming = db.scalar(select(func.count()).select_from(Consultation).where(
        Consultation.statut.in_(ACTIVE), Consultation.date_souhaitee >= now())) or 0
    return {"total": db.scalar(select(func.count()).select_from(Consultation)) or 0,
            "par_statut": {k or "inconnu": v for k, v in grouped(Consultation.statut).items()},
            "par_paiement": {k or "inconnu": v for k, v in grouped(Consultation.paiement_statut).items()},
            "a_venir": upcoming,
            "a_valider": db.scalar(select(func.count()).select_from(Consultation).where(
                Consultation.paiement_statut == "en_attente", Consultation.statut.in_(ACTIVE))) or 0,
            "encaisse": {devise or "?": float(total or 0) for devise, total in revenue}}


def decide_payment(db: Session, c: Consultation, admin: User, statut: str, note: str | None, client: ClientInfo,
                   background: BackgroundTasks) -> Consultation:
    allowed = {"paye": {"en_attente", "echoue"}, "echoue": {"en_attente"}, "rembourse": {"paye"}}
    if c.paiement_statut not in allowed[statut]:
        raise ApiError(409, "INVALID_PAYMENT_STATE",
                       f"Impossible de passer le paiement de « {c.paiement_statut} » à « {statut} »")
    old = c.paiement_statut
    c.paiement_statut = statut
    if statut == "paye":
        db.add(Paiement(consultation_id=c.id, prix_ttc=c.prix_ttc or c.prix_ht, devise=c.devise or "USD",
                        payment_date=now()))
    audit.activity(db, admin.id, f"consultation_payment_{statut}", client, note, module="consultations",
                   item_id=c.id, item_name=c.numero_consultation, old_data={"paiement": old},
                   new_data={"paiement": statut})
    messages = {
        "paye": ("Paiement validé", [f"Votre paiement pour la consultation du {_when(c)} est validé."]),
        "echoue": ("Paiement non validé", [f"Nous n'avons pas pu valider votre paiement pour la consultation "
                                            f"{c.numero_consultation}.", note or "Merci de téléverser une nouvelle preuve de paiement dans l'application."]),
        "rembourse": ("Paiement remboursé", [f"Le paiement de la consultation {c.numero_consultation} a été remboursé."]),
    }
    subject, lines = messages[statut]
    _notify(db, background, [c.patient], subject, lines, {"type": "consultation", "numero": c.numero_consultation})
    db.commit()
    return c


def reassign(db: Session, c: Consultation, admin: User, medecin_uuid: str, client: ClientInfo,
             background: BackgroundTasks) -> Consultation:
    if c.statut not in ("en_attente", "confirmee"):
        raise ApiError(409, "INVALID_TRANSITION", "Seule une consultation en attente ou confirmée peut être réattribuée")
    new = get_doctor(db, medecin_uuid)
    if new.id == c.medecin_id:
        raise ApiError(409, "SAME_DOCTOR", "La consultation est déjà attribuée à ce médecin")
    db.scalar(select(Medecin.id).where(Medecin.id == new.id).with_for_update())
    if not is_free(db, new, c.date_souhaitee):
        raise ApiError(409, "SLOT_UNAVAILABLE", "Ce médecin n'est pas libre sur ce créneau")
    old_doctor = c.medecin
    previous = c.statut
    c.medecin_id, c.medecin = new.id, new
    if previous == "confirmee":  # le nouveau médecin doit confirmer à son tour
        c.statut, c.room_id, c.room_url, c.date_confirmee = "en_attente", None, None, None
    db.add(ConsultationHistory(consultation_id=c.id, from_status=previous, to_status=c.statut, actor_id=admin.id,
                               actor_role="admin", note=f"Réattribuée au Dr {new.user.nom}", created_at=now()))
    try:
        db.flush()
    except IntegrityError:
        db.rollback()
        raise ApiError(409, "SLOT_UNAVAILABLE", "Ce médecin n'est pas libre sur ce créneau") from None
    audit.activity(db, admin.id, "consultation_reassigned", client, f"Dr {old_doctor.user.nom} → Dr {new.user.nom}",
                   module="consultations", item_id=c.id, item_name=c.numero_consultation)
    _notify(db, background, [new.user], "Nouvelle demande de consultation",
            [f"Une consultation du {_when(c)} vous a été attribuée.", "Ouvrez votre agenda pour la confirmer."],
            {"type": "consultation", "numero": c.numero_consultation})
    _notify(db, background, [c.patient], "Changement de médecin",
            [f"Votre consultation du {_when(c)} sera assurée par le Dr {new.user.nom}."],
            {"type": "consultation", "numero": c.numero_consultation})
    db.commit()
    return c


def cancel_by_admin(db: Session, c: Consultation, admin: User, motif: str, client: ClientInfo,
                    background: BackgroundTasks) -> Consultation:
    _move(db, c, "annulee", admin, "admin", motif)
    c.motif_annulation = motif[:255]
    audit.activity(db, admin.id, "consultation_cancelled", client, motif, module="consultations", item_id=c.id,
                   item_name=c.numero_consultation)
    _notify(db, background, [c.patient, c.medecin.user], "Consultation annulée",
            [f"La consultation {c.numero_consultation} du {_when(c)} a été annulée.", f"Motif : {motif}"],
            {"type": "consultation", "numero": c.numero_consultation})
    db.commit()
    return c


# ───────────────────────── tâches de fond (chaque minute) ─────────────────────────

def _remind(db: Session, rows: list[Consultation], column: str, title: str) -> list[Consultation]:
    for c in rows:
        setattr(c, column, now())
        for user in (c.patient, c.medecin.user):
            if user is None or not user.is_active:
                continue
            text, html = simple_email(user.prenom, title,
                                      [f"Consultation {c.numero_consultation} le {_when(c)}.",
                                       "Le lien vidéo sera disponible dans l'application 10 minutes avant."])
            queue_email(db, user.email, f"Nufotec – {title}", text, html)
    return rows


def send_due_reminders(db: Session) -> int:
    """Rappels au patient et au médecin : la veille (24 h avant) et 1 h avant ; une seule fois chacun.
    Expire aussi les demandes jamais confirmées dont l'heure est passée."""
    s = get_settings()
    soon = now() + timedelta(minutes=s.reminder_minutes)
    base = select(Consultation).where(Consultation.statut == "confirmee", Consultation.date_souhaitee > now())
    hour = list(db.scalars(base.where(Consultation.rappel_envoye_at.is_(None), Consultation.date_souhaitee <= soon)
                           .with_for_update(skip_locked=True)))
    eve = list(db.scalars(base.where(Consultation.rappel_veille_at.is_(None), Consultation.date_souhaitee > soon,
                                     Consultation.date_souhaitee <= now() + timedelta(hours=24))
                          .with_for_update(skip_locked=True)))
    _remind(db, hour, "rappel_envoye_at", "Votre consultation commence bientôt")
    _remind(db, eve, "rappel_veille_at", "Rappel : consultation demain")

    expired = list(db.scalars(select(Consultation).where(
        Consultation.statut == "en_attente", Consultation.date_souhaitee < now()).with_for_update(skip_locked=True)))
    for c in expired:
        c.statut = "annulee"
        c.motif_annulation = "Demande non confirmée à temps"
        db.add(ConsultationHistory(consultation_id=c.id, from_status="en_attente", to_status="annulee",
                                   actor_role="systeme", note=c.motif_annulation, created_at=now()))
        if c.patient is not None and c.patient.is_active:
            lines = [f"Votre demande de consultation du {_when(c)} n'a pas été confirmée à temps et a été annulée.",
                     "Vous pouvez réserver un autre créneau dans l'application."]
            if c.paiement_statut == "paye":
                lines.append("Votre paiement vous sera remboursé ; notre équipe vous contactera.")
            text, html = simple_email(c.patient.prenom, "Demande expirée", lines)
            queue_email(db, c.patient.email, "Nufotec – Demande expirée", text, html)
    db.commit()
    for c in hour + eve:
        ids = [u.id for u in (c.patient, c.medecin.user) if u is not None]
        push.send_to_users(ids, "Rappel de consultation", f"Consultation le {_when(c)}.",
                           {"type": "consultation", "numero": c.numero_consultation})
    return len(hour) + len(eve) + len(expired)
