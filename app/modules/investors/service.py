"""Investisseurs et courtiers.

- Formulaires publics (sans compte) : manifestation d'intérêt, candidature de courtier.
- Liaison stricte au compte (user_id) ; une fiche existante n'est rattachée que si l'email du compte
  est identique ET vérifié — jamais par le nom.
- Pipeline investisseur : nouveau → contacté → qualifié → due diligence → engagé / perdu (historisé).
- Data room : investisseurs qualifiés ayant accepté le NDA en vigueur ; chaque téléchargement est journalisé.
- Courtiers approuvés : présentent des investisseurs ; le premier courtier garde l'attribution 12 mois."""

import csv
import io
import json
import secrets
import uuid
from collections.abc import Iterator
from datetime import timedelta
from decimal import Decimal

from fastapi import BackgroundTasks
from sqlalchemy import func, or_, select
from sqlalchemy.orm import Session

from app.core import audit
from app.core.client import ClientInfo
from app.core.config import get_settings
from app.core.files import private_root, signed_link
from app.core.helpers import like_pattern, minutes_ago, now, phone_or_422
from app.core.mailer import deliver_pending, queue_email, simple_email
from app.core.responses import ApiError
from app.models import (
    Broker,
    BrokerInvestor,
    Parametre,
    DataRoomAccess,
    DataRoomDocument,
    DataRoomDownload,
    InvestmentPhase,
    Investor,
    InvestorNote,
    NdaAcceptance,
    Pays,
    User,
)
from app.models.investment import CAPACITIES, FOCUS, INTERESTS, INVESTOR_TYPES, MANDATES, QUALIFIED

PRIVATE_FOLDER = "data_room"
ATTRIBUTION_DAYS = 365
NDA_VERSION = "2026-11"
NDA_TEXT = (
    "ACCORD DE CONFIDENTIALITÉ – NUFOTEC\n\n"
    "En accédant à la data room, vous vous engagez à : (1) utiliser les informations communiquées uniquement pour "
    "évaluer une opportunité d'investissement ou de partenariat avec Nufotec ; (2) ne pas les divulguer à des tiers, "
    "à l'exception de vos conseillers soumis à la même obligation ; (3) ne pas copier ni redistribuer les documents ; "
    "(4) les détruire sur simple demande de Nufotec. Cet engagement reste valable trois ans. Les informations "
    "fournies ne constituent ni une offre ni une sollicitation d'investissement. Votre acceptation (date, adresse IP) "
    "est enregistrée."
)
DATA_ROOM_TYPES = {
    "pdf": ("application/pdf", b"%PDF-"),
    "docx": ("application/vnd.openxmlformats-officedocument.wordprocessingml.document", b"PK"),
    "xlsx": ("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", b"PK"),
    "pptx": ("application/vnd.openxmlformats-officedocument.presentationml.presentation", b"PK"),
}
ALLOCATION_LABELS = {
    "construction": "Construction", "lab_equipment": "Équipement de laboratoire",
    "processing_lines": "Lignes de transformation", "cleanrooms": "Salles blanches",
    "working_capital": "Fonds de roulement", "rd": "Recherche et développement", "training": "Formation",
    "certification": "Certification", "contingency": "Imprévus",
}
BROKER_VIEW = {"nouveau": "pending", "contacte": "contacted", "qualifie": "contacted",
               "due_diligence": "contacted", "engage": "invested", "perdu": "contacted"}
from app.modules.investors.labels import (CAPACITIES_FR, COMMITMENT_FR, FOCUS_FR, INTERESTS_FR, INVESTOR_TYPES_FR, MANDATES_FR, TIMELINE_FR, join)
from app.modules.shop.service import sales_number, wa_link


# ───────────────────────── outils ─────────────────────────

def _email(value: str) -> str:
    return value.strip().lower()


def _selected(obj, mapping: dict[str, str]) -> list[str]:
    return [name for name, column in mapping.items() if getattr(obj, column)]


def _apply_flags(obj, mapping: dict[str, str], chosen: list[str]) -> None:
    for name, column in mapping.items():
        setattr(obj, column, name in chosen)


def _invest_email(db: Session) -> str:
    return db.scalar(select(Parametre.valeur).where(Parametre.cle == "contact_email_invest")) \
        or get_settings().mail_from


def _check_pays(db: Session, pays_id: int, field: str = "pays_id") -> None:
    if not db.get(Pays, pays_id):
        raise ApiError(422, "VALIDATION_ERROR", "Données invalides", [{"field": field, "message": "Pays inconnu"}])


def _check_rate(db: Session, ip: str | None) -> None:
    if not ip:
        return
    recent = (db.scalar(select(func.count()).select_from(Investor).where(Investor.ip_address == ip,
                                                                           Investor.created_at > minutes_ago(60))) or 0) \
        + (db.scalar(select(func.count()).select_from(Broker).where(Broker.ip_address == ip,
                                                                      Broker.created_at > minutes_ago(60))) or 0)
    if recent >= 5:
        raise ApiError(429, "TOO_MANY_REQUESTS", "Trop de demandes depuis ce réseau. Réessayez plus tard.",
                       {"retry_after_seconds": 3600}, {"Retry-After": "3600"})


def _note(db: Session, investor: Investor, kind: str, content: str | None, author: User | None = None,
          from_status: str | None = None, to_status: str | None = None) -> None:
    db.add(InvestorNote(investor_id=investor.id, author_id=author.id if author else None, kind=kind,
                        from_status=from_status, to_status=to_status, content=content, created_at=now()))


def _mail(db: Session, background: BackgroundTasks | None, to: str, prenom: str, subject: str, lines: list[str]) -> None:
    text, html = simple_email(prenom, subject, lines)
    queue_email(db, to, f"Nufotec – {subject}", text, html)
    if background is not None:
        background.add_task(deliver_pending)


def _can_link(user: User | None, email: str, new: bool = False) -> bool:
    """Lier un dossier à un compte : même email. Un dossier NOUVEAU envoyé par le compte lui-même (compte créé dans le
    même formulaire) est lié tout de suite ; un dossier déjà existant exige un email vérifié (personne ne peut
    s'approprier le dossier d'un autre en créant un compte à son adresse)."""
    if not user or _email(user.email) != _email(email):
        return False
    return new or bool(user.email_verified_at)


# ───────────────────────── sérialisation ─────────────────────────

def investor_out(db: Session, i: Investor, admin: bool = False) -> dict:
    data = {
        "uuid": i.uuid, "nom_complet": i.full_name, "organisation": i.organization, "fonction": i.position_title,
        "pays": i.pays.pays if i.pays else None, "email": i.email, "telephone": i.phone,
        "interets": _selected(i, INTERESTS), "interet_autre": i.interest_other, "montant": i.commitment_range,
        "domaines": _selected(i, FOCUS), "calendrier": i.timeline, "message": i.strategic_message,
        "statut": i.statut, "source": i.source, "created_at": i.created_at,
    }
    if admin:
        owner = db.get(User, i.assigned_to) if i.assigned_to else None
        broker = db.get(Broker, i.broker_id) if i.broker_id else None
        data.update({
            "responsable": f"{owner.prenom} {owner.nom}" if owner else None,
            "prochaine_relance": i.prochaine_relance,
            "courtier": f"{broker.full_name} ({broker.firm_name})" if broker else None,
            "nda_accepte": nda_accepted(db, i) is not None, "consent_at": i.consent_at,
        })
    return data


def broker_out(b: Broker) -> dict:
    return {
        "uuid": b.uuid, "nom_complet": b.full_name, "cabinet": b.firm_name, "pays": b.pays.pays if b.pays else None,
        "email": b.email, "telephone": b.mobile_phone, "whatsapp": b.whatsapp, "site_internet": b.corporate_website,
        "juridiction": b.jurisdiction_of_incorporation, "numero_enregistrement": b.registration_number,
        "statut_reglementaire": b.regulatory_status, "autorite_reglementaire": b.regulatory_authority,
        "capacites": _selected(b, CAPACITIES), "types_investisseurs": _selected(b, INVESTOR_TYPES),
        "mandats": _selected(b, MANDATES), "ticket_moyen": b.typical_ticket_size,
        "couverture_geographique": b.geographic_coverage, "modele_remuneration": b.engagement_model,
        "statut": b.statut, "motif_decision": b.motif_decision, "created_at": b.created_at,
    }


def phase_out(p: InvestmentPhase) -> dict:
    try:
        allocation = json.loads(p.allocation_details or "{}")
    except ValueError:
        allocation = {}
    return {"id": p.id, "nom": p.nom_phase, "annee_debut": p.annee_debut, "annee_fin": p.annee_fin,
            "montant_total": p.montant_total, "devise": p.devise, "description": p.description,
            "repartition": [{"poste": k, "libelle": ALLOCATION_LABELS.get(k, k.replace("_", " ").capitalize()),
                             "pourcentage": v} for k, v in allocation.items()]}


# ───────────────────────── formulaires publics ─────────────────────────

def _pays_name(db: Session, pays_id: int | None) -> str:
    p = db.get(Pays, pays_id) if pays_id else None
    return p.pays if p else "—"


def submit_investor(db: Session, data, user: User | None, client: ClientInfo, background: BackgroundTasks) -> dict:
    """Toujours la même réponse : on ne révèle pas si l'email est déjà connu. Renvoie le lien WhatsApp préparé."""
    if data.site_web:  # robot : on fait semblant d'accepter
        return wa_link(sales_number(db), "")
    if not (data.accepte_contact and data.confirmation_non_engageante):
        raise ApiError(422, "CONSENT_REQUIRED", "Merci d'accepter d'être contacté et de confirmer le caractère non engageant",
                       [{"field": "accepte_contact"}, {"field": "confirmation_non_engageante"}])
    _check_rate(db, client.ip)
    _check_pays(db, data.pays_id)
    email = _email(data.email)
    phone = phone_or_422(data.telephone) if data.telephone else None
    summary = (f"Montant : {data.montant or '—'} ; calendrier : {data.calendrier} ; intérêts : "
               f"{', '.join(data.interets) or '—'}\n{data.message or ''}").strip()

    existing = db.scalar(select(Investor).where(func.lower(Investor.email) == email))
    if existing is not None:
        _note(db, existing, "manifestation", f"Nouvelle manifestation d'intérêt.\n{summary}")
        if existing.user_id is None and _can_link(user, email):
            existing.user_id = user.id
        existing.updated_at = now()
        investor = existing
    else:
        investor = Investor(
            uuid=str(uuid.uuid4()), user_id=user.id if _can_link(user, email, new=True) else None, full_name=data.nom_complet,
            organization=data.organisation, position_title=data.fonction, id_pays=data.pays_id, email=email,
            phone=phone, interest_other=data.interet_autre, commitment_range=data.montant, timeline=data.calendrier,
            strategic_message=data.message, agree_contact=True, non_binding_confirmation=True, statut="nouveau",
            source="direct", consent_at=now(), ip_address=client.ip, created_at=now(), updated_at=now())
        _apply_flags(investor, INTERESTS, data.interets)
        _apply_flags(investor, FOCUS, data.domaines)
        db.add(investor)
        db.flush()
        _note(db, investor, "manifestation", summary)
    _mail(db, background, email, data.nom_complet.split(" ")[0], "Manifestation d'intérêt reçue",
          ["Merci pour votre intérêt pour Nufotec. Notre équipe investisseurs étudie votre demande et vous recontacte "
           "rapidement.", "Cette manifestation d'intérêt n'engage ni vous ni Nufotec."])
    pays = _pays_name(db, data.pays_id)
    _mail(db, background, _invest_email(db), "équipe", "Nouvelle manifestation d'intérêt",
          [f"{data.nom_complet} ({data.organisation or 'sans organisation'}) – {email}.",
           f"Téléphone / WhatsApp : {phone or '—'}", f"Pays : {pays}", summary])
    db.commit()
    lines = ["MANIFESTATION D'INTÉRÊT - INVESTISSEUR - NUFOTEC", "",
             f"• Nom : {data.nom_complet}", f"• Organisation : {data.organisation or '—'}",
             f"• Fonction : {data.fonction or '—'}", f"• Pays : {pays}", f"• Email : {email}",
             f"• Téléphone / WhatsApp : {phone or '—'}", "",
             f"• Type d'engagement : {join(data.interets, INTERESTS_FR)}"
             + (f" ; autre : {data.interet_autre}" if data.interet_autre else ""),
             f"• Fourchette d'engagement : {COMMITMENT_FR.get(data.montant, data.montant) if data.montant else '—'}",
             f"• Horizon : {TIMELINE_FR.get(data.calendrier, data.calendrier)}",
             f"• Segments d'intérêt : {join(data.domaines, FOCUS_FR)}"]
    if data.message:
        lines += ["", f"• Message : {data.message}"]
    lines += ["", "Je confirme que cette manifestation d'intérêt est non contraignante et exploratoire."]
    return wa_link(sales_number(db), "\n".join(lines))


def submit_broker(db: Session, data, user: User | None, client: ClientInfo, background: BackgroundTasks) -> dict:
    """Renvoie le lien WhatsApp préparé vers l'équipe Nufotec."""
    if data.site_web:
        return wa_link(sales_number(db), "")
    if not all((data.confirme_autorise, data.confirme_aml_kyc, data.reconnait_non_exclusivite,
                data.comprend_mandat_formel)):
        raise ApiError(422, "CONFIRMATIONS_REQUIRED", "Les quatre confirmations de conformité sont obligatoires")
    _check_rate(db, client.ip)
    _check_pays(db, data.pays_id)
    email = _email(data.email)
    mobile = phone_or_422(data.telephone) if data.telephone else None
    wa_number = phone_or_422(data.whatsapp, "whatsapp") if data.whatsapp else None
    existing = db.scalar(select(Broker).where(func.lower(Broker.email) == email))
    if existing is None:
        broker = Broker(
            uuid=str(uuid.uuid4()), user_id=user.id if _can_link(user, email, new=True) else None, full_name=data.nom_complet,
            firm_name=data.cabinet, jurisdiction_of_incorporation=data.juridiction,
            registration_number=data.numero_enregistrement, regulatory_status=data.statut_reglementaire,
            regulatory_authority=data.autorite_reglementaire, id_pays=data.pays_id, email=email,
            mobile_phone=mobile, whatsapp=wa_number,
            corporate_website=data.site_internet, capacity_other=data.capacite_autre,
            typical_ticket_size=data.ticket_moyen, geographic_coverage=data.couverture_geographique,
            engagement_model=data.modele_remuneration, confirm_authorized=True, confirm_aml_kyc=True,
            acknowledge_no_exclusivity=True, understand_formal_mandate_required=True, statut="en_attente",
            consent_at=now(), ip_address=client.ip, created_at=now(), updated_at=now())
        _apply_flags(broker, CAPACITIES, data.capacites)
        _apply_flags(broker, INVESTOR_TYPES, data.types_investisseurs)
        _apply_flags(broker, MANDATES, data.mandats)
        db.add(broker)
        db.flush()
    elif existing.user_id is None and _can_link(user, email):
        existing.user_id = user.id
    _mail(db, background, email, data.nom_complet.split(" ")[0], "Candidature de courtier reçue",
          ["Merci pour votre candidature. Nous vérifions vos informations (statut réglementaire, conformité AML/KYC) "
           "et revenons vers vous. Aucun mandat n'existe sans accord écrit."])
    pays = _pays_name(db, data.pays_id)
    _mail(db, background, _invest_email(db), "équipe", "Nouvelle candidature de courtier",
          [f"{data.nom_complet} – {data.cabinet} – {email}.", f"WhatsApp : {wa_number or '—'}",
           f"Téléphone : {mobile or '—'}", f"Pays : {pays}", "À examiner dans l'administration."])
    db.commit()
    lines = ["CANDIDATURE COURTIER / AGENT - NUFOTEC", "",
             f"• Nom : {data.nom_complet}", f"• Firme : {data.cabinet}", f"• Pays : {pays}", f"• Email : {email}",
             f"• WhatsApp : {wa_number or '—'}", f"• Téléphone : {mobile or '—'}",
             f"• Juridiction : {data.juridiction or '—'}", f"• N° d'enregistrement : {data.numero_enregistrement or '—'}",
             f"• Statut réglementaire : {data.statut_reglementaire or '—'}"
             + (f" ({data.autorite_reglementaire})" if data.autorite_reglementaire else ""),
             f"• Site web : {data.site_internet or '—'}", "",
             f"• Capacités : {join(data.capacites, CAPACITIES_FR)}" + (f" ; autre : {data.capacite_autre}" if data.capacite_autre else ""),
             f"• Investisseurs représentés : {join(data.types_investisseurs, INVESTOR_TYPES_FR)}",
             f"• Ticket typique : {data.ticket_moyen or '—'}", f"• Couverture : {data.couverture_geographique or '—'}",
             f"• Mandats acceptés : {join(data.mandats, MANDATES_FR)}",
             f"• Modèle d'engagement : {data.modele_remuneration or '—'}"]
    return wa_link(sales_number(db), "\n".join(lines))


# ───────────────────────── espaces liés au compte ─────────────────────────

def my_investor(db: Session, user: User) -> Investor:
    investor = db.scalar(select(Investor).where(Investor.user_id == user.id))
    if investor is None and user.email_verified_at:
        candidate = db.scalar(select(Investor).where(func.lower(Investor.email) == _email(user.email),
                                                     Investor.user_id.is_(None)))
        if candidate is not None:  # rattachement par email vérifié uniquement
            candidate.user_id = user.id
            db.commit()
            investor = candidate
    if investor is None:
        raise ApiError(404, "NO_INVESTOR_PROFILE",
                       "Aucune fiche investisseur liée à ce compte (vérifiez votre email ou remplissez le formulaire)")
    return investor


def my_broker(db: Session, user: User) -> Broker:
    broker = db.scalar(select(Broker).where(Broker.user_id == user.id))
    if broker is None and user.email_verified_at:
        candidate = db.scalar(select(Broker).where(func.lower(Broker.email) == _email(user.email),
                                                   Broker.user_id.is_(None)))
        if candidate is not None:
            candidate.user_id = user.id
            db.commit()
            broker = candidate
    if broker is None:
        raise ApiError(404, "NO_BROKER_PROFILE", "Aucune fiche courtier liée à ce compte")
    return broker


def nda_accepted(db: Session, investor: Investor) -> NdaAcceptance | None:
    return db.scalar(select(NdaAcceptance).where(NdaAcceptance.investor_id == investor.id,
                                                 NdaAcceptance.nda_version == NDA_VERSION))


def accept_nda(db: Session, investor: Investor, user: User, client: ClientInfo) -> NdaAcceptance:
    if investor.statut not in QUALIFIED:
        raise ApiError(403, "NOT_QUALIFIED", "La data room s'ouvre une fois votre dossier qualifié par notre équipe")
    row = nda_accepted(db, investor)
    if row is None:
        row = NdaAcceptance(investor_id=investor.id, user_id=user.id, nda_version=NDA_VERSION, ip_address=client.ip,
                            user_agent=client.user_agent[:500] or None, accepted_at=now())
        db.add(row)
        _note(db, investor, "note", f"NDA {NDA_VERSION} accepté (IP {client.ip or '?'})", user)
        db.commit()
    return row


def _visible_documents(db: Session, investor: Investor):
    return select(DataRoomDocument).where(
        DataRoomDocument.is_active.is_(True),
        or_(DataRoomDocument.visibilite == "tous_qualifies",
            DataRoomDocument.id.in_(select(DataRoomAccess.document_id).where(DataRoomAccess.investor_id == investor.id))))


def _require_data_room(db: Session, investor: Investor) -> None:
    if investor.statut not in QUALIFIED:
        raise ApiError(403, "NOT_QUALIFIED", "La data room s'ouvre une fois votre dossier qualifié par notre équipe")
    if nda_accepted(db, investor) is None:
        raise ApiError(403, "NDA_REQUIRED", "Acceptez d'abord l'accord de confidentialité")


def data_room(db: Session, investor: Investor) -> list[DataRoomDocument]:
    _require_data_room(db, investor)
    return list(db.scalars(_visible_documents(db, investor).order_by(DataRoomDocument.created_at.desc())))


def data_room_link(db: Session, investor: Investor, user: User, doc_uuid: str, client: ClientInfo) -> dict:
    _require_data_room(db, investor)
    doc = db.scalar(_visible_documents(db, investor).where(DataRoomDocument.uuid == doc_uuid))
    if doc is None:
        raise ApiError(404, "NOT_FOUND", "Document introuvable")
    db.add(DataRoomDownload(document_id=doc.id, investor_id=investor.id, user_id=user.id, ip_address=client.ip,
                            created_at=now()))
    db.commit()
    url, expires = signed_link(doc.id, scope="dataroom")
    return {"url": url, "expire_le": expires}


# ───────────────────────── courtier : présentations ─────────────────────────

def introductions(db: Session, broker: Broker) -> list[dict]:
    rows = db.scalars(select(BrokerInvestor).where(BrokerInvestor.broker_id == broker.id)
                      .order_by(BrokerInvestor.created_at.desc()))
    out = []
    for r in rows:
        investor = db.get(Investor, r.investor_id) if r.investor_id else None
        statut = BROKER_VIEW.get(investor.statut, r.status) if investor else (r.status or "pending")
        out.append({"id": r.id, "nom_complet": r.full_name, "organisation": r.organization, "email": r.email,
                    "montant": r.commitment_range, "statut": statut, "attribue_jusqu_au": r.attribution_until,
                    "created_at": r.created_at})
    return out


def introduce(db: Session, broker: Broker, user: User, data, client: ClientInfo,
              background: BackgroundTasks) -> BrokerInvestor:
    if broker.statut != "approuve":
        raise ApiError(403, "BROKER_NOT_APPROVED", "Votre candidature doit être approuvée avant de présenter des investisseurs")
    _check_pays(db, data.pays_id)
    email = _email(data.email)
    active = db.scalar(select(BrokerInvestor).where(func.lower(BrokerInvestor.email) == email,
                                                    BrokerInvestor.attribution_until > now()))
    if active is not None and active.broker_id == broker.id:
        raise ApiError(409, "ALREADY_INTRODUCED", "Vous avez déjà présenté cet investisseur")
    # Même message qu'il soit connu en direct ou par un autre courtier : rien n'est révélé.
    if active is not None or db.scalar(select(Investor.id).where(func.lower(Investor.email) == email)):
        raise ApiError(409, "ALREADY_KNOWN", "Cet investisseur est déjà en relation avec Nufotec")

    investor = Investor(uuid=str(uuid.uuid4()), full_name=data.nom_complet, organization=data.organisation,
                        id_pays=data.pays_id, email=email,
                        phone=phone_or_422(data.telephone) if data.telephone else None,
                        commitment_range=data.montant, timeline="Exploratory", statut="nouveau", source="courtier",
                        broker_id=broker.id, created_at=now(), updated_at=now())
    db.add(investor)
    db.flush()
    _note(db, investor, "manifestation", f"Présenté par {broker.full_name} ({broker.firm_name}).\n{data.notes or ''}".strip(),
          user)
    intro = BrokerInvestor(broker_id=broker.id, investor_id=investor.id, full_name=data.nom_complet,
                           organization=data.organisation, email=email, phone=investor.phone,
                           commitment_range=data.montant, status="pending", notes=data.notes,
                           attribution_until=now() + timedelta(days=ATTRIBUTION_DAYS), created_at=now(), updated_at=now())
    db.add(intro)
    audit.activity(db, user.id, "broker_introduction", client, f"{broker.firm_name} présente {email}",
                   module="investisseurs", item_id=investor.id, item_name=email)
    _mail(db, background, _invest_email(db), "équipe", "Nouvel investisseur présenté par un courtier",
          [f"{broker.full_name} ({broker.firm_name}) présente {data.nom_complet} – {email}."])
    db.commit()
    return intro


# ───────────────────────── administration : pipeline ─────────────────────────

def get_investor(db: Session, investor_uuid: str) -> Investor:
    i = db.scalar(select(Investor).where(Investor.uuid == investor_uuid))
    if i is None:
        raise ApiError(404, "NOT_FOUND", "Investisseur introuvable")
    return i


def admin_query(statut, source, montant, calendrier, pays_id, responsable_uuid, relance_due, q):
    query = select(Investor)
    if statut:
        query = query.where(Investor.statut == statut)
    if source:
        query = query.where(Investor.source == source)
    if montant:
        query = query.where(Investor.commitment_range == montant)
    if calendrier:
        query = query.where(Investor.timeline == calendrier)
    if pays_id:
        query = query.where(Investor.id_pays == pays_id)
    if responsable_uuid:
        query = query.join(User, User.id == Investor.assigned_to).where(User.uuid == responsable_uuid)
    if relance_due:
        query = query.where(Investor.prochaine_relance <= now())
    if q and q.strip():
        p = like_pattern(q.strip())
        query = query.where(or_(Investor.full_name.like(p), Investor.organization.like(p), Investor.email.like(p)))
    return query.order_by(Investor.created_at.desc(), Investor.id.desc())


def history(db: Session, investor: Investor) -> list[dict]:
    rows = db.scalars(select(InvestorNote).where(InvestorNote.investor_id == investor.id).order_by(InvestorNote.id.desc()))
    authors = {}
    out = []
    for n in rows:
        if n.author_id and n.author_id not in authors:
            u = db.get(User, n.author_id)
            authors[n.author_id] = f"{u.prenom} {u.nom}" if u else None
        out.append({"type": n.kind, "de": n.from_status, "vers": n.to_status, "texte": n.content,
                    "auteur": authors.get(n.author_id), "le": n.created_at})
    return out


def set_status(db: Session, investor: Investor, admin: User, statut: str, note: str | None, client: ClientInfo) -> Investor:
    if statut == investor.statut:
        raise ApiError(409, "SAME_STATUS", "Ce dossier a déjà ce statut")
    old = investor.statut
    investor.statut, investor.updated_at = statut, now()
    _note(db, investor, "statut", note, admin, old, statut)
    if investor.broker_id:
        for intro in db.scalars(select(BrokerInvestor).where(BrokerInvestor.investor_id == investor.id)):
            intro.status, intro.updated_at = BROKER_VIEW[statut], now()
    audit.activity(db, admin.id, "investor_status", client, f"{old} → {statut}", module="investisseurs",
                   item_id=investor.id, item_name=investor.email)
    db.commit()
    return investor


def add_note(db: Session, investor: Investor, admin: User, texte: str) -> None:
    _note(db, investor, "note", texte.strip(), admin)
    db.commit()


def assign(db: Session, investor: Investor, admin: User, owner_uuid: str | None) -> Investor:
    owner = None
    if owner_uuid:
        owner = db.scalar(select(User).where(User.uuid == owner_uuid, User.deleted_at.is_(None)))
        if owner is None or owner.role.slug != "admin":
            raise ApiError(422, "VALIDATION_ERROR", "Le responsable doit être un administrateur actif")
    investor.assigned_to = owner.id if owner else None
    _note(db, investor, "assignation", f"Responsable : {owner.prenom} {owner.nom}" if owner else "Responsable retiré", admin)
    db.commit()
    return investor


def follow_up(db: Session, investor: Investor, admin: User, when, note: str | None) -> Investor:
    from app.modules.consultations.slots import to_local_naive

    investor.prochaine_relance = to_local_naive(when) if when else None
    investor.relance_envoyee_at = None
    _note(db, investor, "relance", (f"Relance prévue le {investor.prochaine_relance:%d/%m/%Y %H:%M}"
                                    if when else "Relance annulée") + (f" – {note}" if note else ""), admin)
    db.commit()
    return investor


def update_investor(db: Session, investor: Investor, changes: dict) -> Investor:
    mapping = {"organisation": "organization", "fonction": "position_title", "telephone": "phone",
               "montant": "commitment_range", "calendrier": "timeline"}
    if changes.get("telephone"):
        changes["telephone"] = phone_or_422(changes["telephone"])
    for key, value in changes.items():
        setattr(investor, mapping[key], value)
    investor.updated_at = now()
    db.commit()
    return investor


def stats(db: Session) -> dict:
    def grouped(column):
        return {k or "—": v for k, v in db.execute(select(column, func.count()).group_by(column)).all()}

    return {"total": db.scalar(select(func.count()).select_from(Investor)) or 0,
            "par_statut": grouped(Investor.statut), "par_montant": grouped(Investor.commitment_range),
            "par_calendrier": grouped(Investor.timeline), "par_source": grouped(Investor.source),
            "relances_dues": db.scalar(select(func.count()).select_from(Investor).where(
                Investor.prochaine_relance <= now())) or 0,
            "courtiers_en_attente": db.scalar(select(func.count()).select_from(Broker).where(
                Broker.statut == "en_attente")) or 0}


def export_csv(db: Session, query) -> Iterator[str]:
    from app.modules.users.service import _cell

    buffer = io.StringIO()
    writer = csv.writer(buffer, delimiter=";")
    yield "﻿"
    writer.writerow(["uuid", "nom", "organisation", "email", "telephone", "pays", "montant", "calendrier", "statut",
                     "source", "cree_le"])
    for i in db.scalars(query.limit(20_000)).unique():
        writer.writerow([_cell(v) for v in (i.uuid, i.full_name, i.organization, i.email, i.phone,
                                            i.pays.pays if i.pays else "", i.commitment_range, i.timeline, i.statut,
                                            i.source, i.created_at)])
    yield buffer.getvalue()


# ───────────────────────── administration : courtiers ─────────────────────────

def get_broker(db: Session, broker_uuid: str) -> Broker:
    b = db.scalar(select(Broker).where(Broker.uuid == broker_uuid))
    if b is None:
        raise ApiError(404, "NOT_FOUND", "Courtier introuvable")
    return b


def decide_broker(db: Session, broker: Broker, admin: User, statut: str, motif: str | None, client: ClientInfo,
                  background: BackgroundTasks) -> Broker:
    if statut == "refuse" and not motif:
        raise ApiError(422, "VALIDATION_ERROR", "Un motif est obligatoire pour un refus",
                       [{"field": "motif", "message": "obligatoire"}])
    old = broker.statut
    broker.statut, broker.motif_decision = statut, motif
    broker.decision_at, broker.decision_par, broker.updated_at = now(), admin.id, now()
    audit.activity(db, admin.id, "broker_decision", client, f"{old} → {statut}" + (f" ({motif})" if motif else ""),
                   module="investisseurs", item_id=broker.id, item_name=broker.email)
    messages = {
        "approuve": ("Candidature approuvée", ["Votre candidature de courtier est approuvée. Vous pouvez désormais "
                                               "présenter des investisseurs depuis votre espace."]),
        "refuse": ("Candidature non retenue", ["Après examen, votre candidature n'a pas été retenue.",
                                               f"Motif : {motif}"]),
        "suspendu": ("Compte courtier suspendu", ["Votre accès courtier est suspendu." + (f" Motif : {motif}" if motif else "")]),
    }
    if statut in messages:
        subject, lines = messages[statut]
        _mail(db, background, broker.email, broker.full_name.split(" ")[0], subject, lines)
    db.commit()
    return broker


def broker_query(statut: str | None, q: str | None):
    query = select(Broker)
    if statut:
        query = query.where(Broker.statut == statut)
    if q and q.strip():
        p = like_pattern(q.strip())
        query = query.where(or_(Broker.full_name.like(p), Broker.firm_name.like(p), Broker.email.like(p)))
    return query.order_by(Broker.created_at.desc())


# ───────────────────────── administration : phases ─────────────────────────

def save_phase(db: Session, phase: InvestmentPhase | None, data) -> InvestmentPhase:
    if data.annee_debut and data.annee_fin and data.annee_fin < data.annee_debut:
        raise ApiError(422, "VALIDATION_ERROR", "L'année de fin doit être après l'année de début")
    if data.repartition:
        total = sum(data.repartition.values(), Decimal(0))
        if abs(total - 100) > Decimal("0.01"):
            raise ApiError(422, "INVALID_ALLOCATION", f"La répartition doit faire 100 % (actuellement {total} %)")
        if any(v < 0 for v in data.repartition.values()):
            raise ApiError(422, "INVALID_ALLOCATION", "Les pourcentages doivent être positifs")
    phase = phase or InvestmentPhase()
    phase.nom_phase, phase.annee_debut, phase.annee_fin = data.nom, data.annee_debut, data.annee_fin
    phase.montant_total, phase.devise, phase.description = data.montant_total, data.devise, data.description
    phase.allocation_details = json.dumps({k: float(v) for k, v in data.repartition.items()})
    db.add(phase)
    db.commit()
    return phase


# ───────────────────────── administration : data room ─────────────────────────

def upload_document(db: Session, admin: User, raw: bytes, filename: str, titre: str, description: str | None,
                    client: ClientInfo) -> DataRoomDocument:
    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else ""
    if ext not in DATA_ROOM_TYPES:
        raise ApiError(422, "INVALID_FILE", "Formats acceptés : PDF, DOCX, XLSX, PPTX")
    mime, magic = DATA_ROOM_TYPES[ext]
    if not raw or not raw.startswith(magic):
        raise ApiError(422, "INVALID_FILE", "Le contenu du fichier ne correspond pas à son extension")
    if len(raw) > 50 * 1024 * 1024:
        raise ApiError(422, "INVALID_FILE", "Fichier trop lourd (maximum 50 Mo)")
    if ext == "pdf" and (b"/JavaScript" in raw or b"/Launch" in raw):
        raise ApiError(422, "INVALID_FILE", "PDF refusé : il contient du code exécutable")
    stored = f"{secrets.token_hex(16)}.{ext}"
    folder = private_root() / PRIVATE_FOLDER
    folder.mkdir(parents=True, exist_ok=True)
    (folder / stored).write_bytes(raw)
    doc = DataRoomDocument(uuid=str(uuid.uuid4()), titre=titre.strip(), description=description, stored_name=stored,
                           original_name=filename[:255], mime=mime, size_bytes=len(raw), visibilite="tous_qualifies",
                           is_active=True, created_by=admin.id, created_at=now())
    db.add(doc)
    audit.activity(db, admin.id, "data_room_upload", client, titre, module="investisseurs", item_name=filename)
    db.commit()
    return doc


def get_document(db: Session, doc_uuid: str) -> DataRoomDocument:
    doc = db.scalar(select(DataRoomDocument).where(DataRoomDocument.uuid == doc_uuid))
    if doc is None:
        raise ApiError(404, "NOT_FOUND", "Document introuvable")
    return doc


def update_document(db: Session, doc: DataRoomDocument, changes: dict) -> DataRoomDocument:
    if changes.get("titre"):
        doc.titre = changes["titre"].strip()
    if "description" in changes:
        doc.description = changes["description"]
    if changes.get("visibilite"):
        doc.visibilite = changes["visibilite"]
    if changes.get("actif") is not None:
        doc.is_active = changes["actif"]
    if changes.get("investisseurs") is not None:
        ids = list(db.scalars(select(Investor.id).where(Investor.uuid.in_(changes["investisseurs"]))))
        if len(ids) != len(set(changes["investisseurs"])):
            raise ApiError(422, "VALIDATION_ERROR", "Investisseur inconnu dans la sélection")
        for row in db.scalars(select(DataRoomAccess).where(DataRoomAccess.document_id == doc.id)):
            db.delete(row)
        db.flush()
        db.add_all(DataRoomAccess(document_id=doc.id, investor_id=i) for i in ids)
    db.commit()
    return doc


def document_admin_out(db: Session, doc: DataRoomDocument) -> dict:
    downloads = db.scalar(select(func.count()).select_from(DataRoomDownload)
                          .where(DataRoomDownload.document_id == doc.id)) or 0
    selection = list(db.scalars(select(Investor.uuid).join(DataRoomAccess, DataRoomAccess.investor_id == Investor.id)
                                .where(DataRoomAccess.document_id == doc.id)))
    return {"uuid": doc.uuid, "titre": doc.titre, "description": doc.description, "nom_fichier": doc.original_name,
            "taille": doc.size_bytes, "visibilite": doc.visibilite, "investisseurs": selection, "actif": doc.is_active,
            "telechargements": downloads, "ajoute_le": doc.created_at}


def downloads_log(db: Session, doc: DataRoomDocument) -> list[dict]:
    rows = db.execute(select(DataRoomDownload, Investor.full_name, Investor.organization)
                      .join(Investor, Investor.id == DataRoomDownload.investor_id, isouter=True)
                      .where(DataRoomDownload.document_id == doc.id).order_by(DataRoomDownload.id.desc()).limit(500)).all()
    return [{"investisseur": name, "organisation": org, "ip": d.ip_address, "le": d.created_at} for d, name, org in rows]


# ───────────────────────── relances (tâche de fond) ─────────────────────────

def send_followups(db: Session) -> int:
    due = list(db.scalars(select(Investor).where(
        Investor.prochaine_relance <= now(),
        or_(Investor.relance_envoyee_at.is_(None), Investor.relance_envoyee_at < Investor.prochaine_relance))
        .with_for_update(skip_locked=True)))
    for i in due:
        i.relance_envoyee_at = now()
        owner = db.get(User, i.assigned_to) if i.assigned_to else None
        to = owner.email if owner and owner.is_active else _invest_email(db)
        _mail(db, None, to, owner.prenom if owner else "équipe", "Relance investisseur à faire",
              [f"Relance prévue pour {i.full_name} ({i.organization or 'sans organisation'}) – statut : {i.statut}.",
               "Ouvrez sa fiche dans l'administration pour noter le résultat."])
    db.commit()
    return len(due)
