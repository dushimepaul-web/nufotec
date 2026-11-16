"""Créneaux libres d'un médecin : ses horaires de la semaine, découpés selon SA durée de consultation,
moins les rendez-vous déjà pris (chevauchement réel, pas seulement même heure de début) et ses absences.
Toutes les heures sont en heure locale (Burundi), comme en base."""

from datetime import date, datetime, timedelta

from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core.config import get_settings
from app.core.helpers import now
from app.core.types import app_tz
from app.models.consultation import JOURS, Consultation, Medecin, MedecinAbsence

ACTIVE = ("en_attente", "confirmee", "en_cours")
DEFAULT_MINUTES = 30
SLOT_MINUTES = DEFAULT_MINUTES  # compatibilité


def duration(medecin: Medecin) -> timedelta:
    return timedelta(minutes=medecin.duree_consultation_minutes or DEFAULT_MINUTES)


def to_local_naive(value: datetime) -> datetime:
    """Une date reçue avec fuseau est convertie en heure du Burundi ; sans fuseau, elle l'est déjà."""
    if value.tzinfo is not None:
        value = value.astimezone(app_tz()).replace(tzinfo=None)
    return value.replace(second=0, microsecond=0)


def bookable(medecin: Medecin) -> bool:
    user = medecin.user
    return bool(medecin.est_disponible and user and user.is_active and user.deleted_at is None)


def _busy(db: Session, medecin_id: int, start: datetime, end: datetime,
          exclude_id: int | None = None) -> list[tuple[datetime, datetime]]:
    """Périodes occupées (rendez-vous actifs + absences) qui touchent [start, end)."""
    query = select(Consultation.date_souhaitee, Consultation.duree_minutes).where(
        Consultation.medecin_id == medecin_id, Consultation.statut.in_(ACTIVE),
        Consultation.date_souhaitee < end, Consultation.date_souhaitee >= start - timedelta(hours=4))
    if exclude_id:
        query = query.where(Consultation.id != exclude_id)
    busy = [(s, s + timedelta(minutes=d or DEFAULT_MINUTES)) for s, d in db.execute(query)]
    busy += [(a.debut, a.fin) for a in db.scalars(select(MedecinAbsence).where(
        MedecinAbsence.medecin_id == medecin_id, MedecinAbsence.debut < end, MedecinAbsence.fin > start))]
    return busy


def free_slots(db: Session, medecin: Medecin, first_day: date, last_day: date,
               exclude_id: int | None = None) -> list[datetime]:
    if not bookable(medecin):
        return []
    s = get_settings()
    earliest = now() + timedelta(minutes=s.booking_min_lead_minutes)
    latest = now() + timedelta(days=s.booking_max_days)
    range_start = datetime.combine(first_day, datetime.min.time())
    range_end = datetime.combine(last_day + timedelta(days=1), datetime.min.time())
    busy = _busy(db, medecin.id, range_start, range_end, exclude_id)
    step = duration(medecin)
    result: set[datetime] = set()
    day = first_day
    while day <= last_day:
        for h in medecin.horaires:
            if not h.est_actif or h.jour_semaine != JOURS[day.weekday()] or h.heure_fin <= h.heure_debut:
                continue
            t, end = datetime.combine(day, h.heure_debut), datetime.combine(day, h.heure_fin)
            while t + step <= end:
                if earliest <= t <= latest and not any(b0 < t + step and t < b1 for b0, b1 in busy):
                    result.add(t)
                t += step
        day += timedelta(days=1)
    return sorted(result)


def is_free(db: Session, medecin: Medecin, start: datetime, exclude_id: int | None = None) -> bool:
    return start in free_slots(db, medecin, start.date(), start.date(), exclude_id)


def available_now(medecin: Medecin) -> bool:
    if not bookable(medecin):
        return False
    current = now()
    return any(h.est_actif and h.jour_semaine == JOURS[current.weekday()] and h.heure_debut <= current.time() < h.heure_fin
               for h in medecin.horaires)
