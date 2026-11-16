"""Message WhatsApp envoyé par le patient au médecin choisi, juste après sa demande de consultation.

L'API ne peut pas envoyer de WhatsApp à la place du patient : elle fournit un lien https://wa.me/<numéro>?text=…
que l'app (ou le site) ouvre ; WhatsApp s'ouvre sur la conversation du médecin avec le message déjà rempli.
Numéro utilisé : WhatsApp de la fiche médecin, sinon téléphone du compte, sinon contact_whatsapp (parametres)."""

import re
from decimal import Decimal
from urllib.parse import quote

from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core.phone import InvalidPhone, normalize_phone
from app.models import Parametre, Consultation, ModePaiement
from app.modules.consultations.prices import money, price_labels

LINE = "━" * 30
DEFAULT_CONTACT = "+257 79 666 439"


# Durées choisies dans le formulaire → texte lisible
DURATIONS = {"24h": "moins de 24 heures", "2-3j": "2 à 3 jours", "1sem": "une semaine",
             "2sem": "deux semaines", "1mois": "plus d'un mois"}


def _person(user) -> str:
    """« Claire Ndayishimiye » ; ignore les morceaux vides ou réduits à « . » (anciennes données)."""
    return " ".join(x.strip() for x in (user.prenom, user.nom) if x and x.strip() and x.strip() != ".") or "—"


def _money(value: Decimal | None, currency: str) -> str:
    return money(value, currency)


def _num(value: Decimal) -> str:
    """70.00 → « 70 », 70.50 → « 70.5 »."""
    return format(Decimal(value).normalize(), "f")


def doctor_number(db: Session, c: Consultation) -> str:
    candidates = [c.medecin.whatsapp, c.medecin.user.telephone,
                  db.scalar(select(Parametre.valeur).where(Parametre.cle == "contact_whatsapp")),
                  DEFAULT_CONTACT]
    for raw in candidates:
        if raw:
            try:
                return normalize_phone(raw)
            except InvalidPhone:
                continue
    return normalize_phone(DEFAULT_CONTACT)


def build_message(db: Session, c: Consultation) -> str:
    m, patient = c.medecin, c.patient
    pays = c.pays.pays if c.pays else "—"
    lines = [
        "DEMANDE DE CONSULTATION EN LIGNE - NUFOTEC", "",
        LINE, "INFORMATIONS PATIENT", LINE,
        f"• Nom : {(c.patient_name or '').upper()}",
        f"• Email : {patient.email if patient else '—'}",
        f"• WhatsApp : {c.whatsapppatient or '—'}",
        f"• Date de demande : {c.created_at:%d/%m/%Y %H:%M}" if c.created_at else "• Date de demande : —", "",
        LINE, "DÉTAILS DE LA CONSULTATION", LINE,
        f"• N° suivi : {c.numero_consultation}",
        f"• Expert : {_person(m.user)} ({m.specialite or 'Santé'})",
        f"• Rendez-vous : {c.date_souhaitee:%d/%m/%Y à %H:%M} (heure du Burundi) – {c.type}",
        f"• Âge : {c.age} ans" if c.age is not None else "• Âge : —",
        f"• Pays : {pays}",
        f"• Poids : {_num(c.poids)} kg | Taille : {_num(c.taille)} cm" if c.poids and c.taille else "• Poids / taille : —",
        "", f"• Symptômes : {c.symptomes or '—'}",
    ]
    if c.duree_symptomes:
        lines.append(f"• Depuis : {DURATIONS.get(c.duree_symptomes, c.duree_symptomes)}")
    tarifs = price_labels(m)
    lines += ["", "Frais de consultation",
              f"• {tarifs['ligne_burundi'] or 'Résidant du Burundi : —'}",
              f"• {tarifs['ligne_etranger'] or 'Résidant à l’étranger : —'}",
              f"• Montant à payer : {_money(c.prix_ttc or c.prix_ht, c.devise or 'USD')}", ""]
    if c.mode_paiement:
        lines.append(f"Mode de paiement : {c.mode_paiement}")
    if c.paiement_statut != "paye":
        modes = db.scalars(select(ModePaiement).where(ModePaiement.est_actif.is_(True)).order_by(ModePaiement.id))
        where = ("dans l'application Nufotec" if c.patient_id
                 else "en l'envoyant dans cette conversation WhatsApp (photo ou capture du reçu)")
        lines += ["", f"*NB — Paiement en attente : payez puis transmettez votre preuve de paiement {where} :*"]
        for mode in modes:
            detail = ": " + " ".join(x for x in (mode.numero_compte, f"({mode.nom_compte})" if mode.nom_compte else "") if x) \
                if mode.numero_compte or mode.nom_compte else ""
            lines.append(f"* {mode.description}{detail}")
    return "\n".join(lines)


def link(db: Session, c: Consultation) -> dict:
    number = doctor_number(db, c)
    message = build_message(db, c)
    return {"numero": number, "message": message,
            "url": f"https://wa.me/{re.sub(r'[^0-9]', '', number)}?text={quote(message)}"}
