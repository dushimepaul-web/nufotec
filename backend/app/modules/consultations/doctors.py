"""Fiches médecins : création par l'administration (seul l'admin crée un médecin), tarifs, horaires, licence."""

import uuid
from datetime import time

from fastapi import BackgroundTasks
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core import audit
from app.core.client import ClientInfo
from app.core.helpers import now
from app.core.responses import ApiError
from app.models import Medecin, MedecinHoraire, User
from app.modules.users import service as users_service
from app.modules.users.schemas import AdminUserCreateIn


def _fmt_bif(value) -> str:
    return f"{int(value):,} BIF".replace(",", " ") if value is not None else ""


def _sync_legacy(m: Medecin) -> None:
    """Tient à jour les colonnes texte utilisées par le site PHP."""
    m.honoraires_consultation = m.prix_usd
    m.currency = "USD"
    m.prix_pour_residant_burundi = _fmt_bif(m.prix_bif)
    m.usd_eur_equivalent_bif = m.usd_eur_equivalent_bif or ""


def _langues(values: list[str] | None) -> str | None:
    if values is None:
        return None
    cleaned = [v.strip().upper() for v in values if v and v.strip()]
    return ", ".join(dict.fromkeys(cleaned))[:255] or None


def validate_horaires(items: list) -> None:
    """Pas de chevauchement, fin après début, 4 plages au plus par jour."""
    by_day: dict[str, list[tuple[time, time]]] = {}
    for item in items:
        if item.fin <= item.debut:
            raise ApiError(422, "INVALID_SCHEDULE", f"{item.jour} : l'heure de fin doit être après l'heure de début")
        by_day.setdefault(item.jour, []).append((item.debut, item.fin))
    for day, ranges in by_day.items():
        if len(ranges) > 4:
            raise ApiError(422, "INVALID_SCHEDULE", f"{day} : 4 plages horaires au maximum")
        ranges.sort()
        for (_, end1), (start2, _) in zip(ranges, ranges[1:], strict=False):
            if start2 < end1:
                raise ApiError(422, "INVALID_SCHEDULE", f"{day} : des plages horaires se chevauchent")


def set_horaires(db: Session, m: Medecin, items: list) -> None:
    """Remplace les horaires de la semaine."""
    validate_horaires(items)
    m.horaires.clear()
    db.flush()
    for item in items:
        m.horaires.append(MedecinHoraire(jour_semaine=item.jour, heure_debut=item.debut, heure_fin=item.fin,
                                         est_actif=True))


def _whatsapp(raw: str | None) -> str | None:
    from app.core.helpers import phone_or_422

    return phone_or_422(raw, "whatsapp") if raw else None


def admin_out(m: Medecin) -> dict:
    from app.modules.consultations.service import doctor_public

    data = doctor_public(m, detail=True)
    data["horaires"] = [{"jour": h.jour_semaine, "debut": h.heure_debut, "fin": h.heure_fin}
                        for h in m.horaires if h.est_actif]
    data.update({"user_uuid": m.user.uuid, "email": m.user.email, "telephone": m.user.telephone,
                 "numero_licence": m.numero_licence, "whatsapp": m.whatsapp, "compte_actif": bool(m.user.is_active and not m.user.deleted_at)})
    return data


def create(db: Session, admin: User, data, client: ClientInfo, background: BackgroundTasks) -> Medecin:
    validate_horaires(data.horaires)  # avant de créer le compte : pas de compte orphelin si les horaires sont faux
    if data.user_uuid:
        user = users_service.get_by_uuid(db, data.user_uuid, include_deleted=False)
        if user.type_utilisateur != "medecin":
            raise ApiError(409, "NOT_A_DOCTOR_ACCOUNT", "Ce compte n'est pas de type médecin")
    else:
        missing = [f for f in ("email", "nom", "prenom") if not getattr(data, f)]
        if missing:
            raise ApiError(422, "VALIDATION_ERROR", "Données invalides",
                           [{"field": f, "message": "Champ obligatoire"} for f in missing])
        user = users_service.create(db, admin, AdminUserCreateIn(
            email=data.email, nom=data.nom, prenom=data.prenom, telephone=data.telephone, genre=data.genre,
            type_utilisateur="medecin", send_invitation=True), client, background)
    if db.scalar(select(Medecin.id).where(Medecin.user_id == user.id)):
        raise ApiError(409, "DOCTOR_EXISTS", "Ce compte a déjà une fiche médecin")

    m = Medecin(uuid=str(uuid.uuid4()), user_id=user.id, specialite=data.specialite.strip(),
                numero_licence=data.numero_licence.strip(), annees_experience=data.annees_experience,
                diplomes=data.diplomes, bio=data.bio, langues_parlees=_langues(data.langues),
                whatsapp=_whatsapp(data.whatsapp),
                prix_usd=data.prix_usd, prix_bif=data.prix_bif, est_disponible=data.est_disponible,
                duree_consultation_minutes=data.duree_consultation_minutes,
                note_moyenne=0, nombre_avis=0, usd_eur_equivalent_bif="")
    _sync_legacy(m)
    db.add(m)
    db.flush()
    set_horaires(db, m, data.horaires)
    audit.activity(db, admin.id, "doctor_created", client, f"Fiche médecin {user.email}", module="medecins",
                   item_id=m.id, item_name=user.email)
    db.commit()
    db.refresh(m)
    return m


def update(db: Session, admin: User, m: Medecin, changes: dict, client: ClientInfo) -> Medecin:
    if "whatsapp" in changes:
        changes["whatsapp"] = _whatsapp(changes["whatsapp"])
    if "langues" in changes:
        changes["langues_parlees"] = _langues(changes.pop("langues"))
    for field in ("specialite", "numero_licence"):
        if field in changes and not changes[field]:
            raise ApiError(422, "VALIDATION_ERROR", "Données invalides", [{"field": field, "message": "Ne peut pas être vide"}])
    old = {k: getattr(m, k) for k in changes}
    for key, value in changes.items():
        setattr(m, key, value)
    _sync_legacy(m)
    audit.activity(db, admin.id, "doctor_updated", client, None, module="medecins", item_id=m.id,
                   item_name=m.user.email, old_data=old, new_data=changes)
    db.commit()
    db.refresh(m)
    return m


def update_own_profile(db: Session, m: Medecin, changes: dict) -> Medecin:
    if "whatsapp" in changes:
        changes["whatsapp"] = _whatsapp(changes["whatsapp"])
    if "langues" in changes:
        changes["langues_parlees"] = _langues(changes.pop("langues"))
    for key, value in changes.items():
        setattr(m, key, value)
    m.updated_at = now()
    db.commit()
    db.refresh(m)
    return m


def set_licence(db: Session, admin: User, m: Medecin, verified: bool, client: ClientInfo) -> Medecin:
    """Licence vérifiée = KYC du compte (users.est_verifie), comme dans le PHP."""
    m.user.est_verifie = verified
    audit.activity(db, admin.id, "doctor_licence", client, "Licence vérifiée" if verified else "Licence non vérifiée",
                   module="medecins", item_id=m.id, item_name=m.user.email)
    db.commit()
    return m
