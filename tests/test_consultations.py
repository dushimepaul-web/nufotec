"""Module Consultations et médecins : parcours complet, sécurité, cas limites."""

import io
import uuid
from datetime import date, datetime, timedelta
from urllib.parse import urlparse

import pytest
from PIL import Image
from sqlalchemy import select

from app.core.helpers import now
from app.core.security import hash_password
from app.models import Consultation, ConsultationHistory, Medecin, Paiement, Role, User
from app.modules.consultations.service import send_due_reminders

PASSWORD = "MotDePasse123"
BURUNDI = 194
FORM = {"age": 38, "poids": "70", "taille": "150", "pays_residence_id": BURUNDI, "mode_paiement_id": 3}
ALL_WEEK = [{"jour": j, "debut": "08:00", "fin": "18:00"}
            for j in ("lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi", "dimanche")]


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


def _user(db, slug="user", type_utilisateur="patient", **extra):
    role_id = db.scalar(select(Role.id).where(Role.slug == slug))
    u = User(uuid=str(uuid.uuid4()), email=f"test-{uuid.uuid4().hex[:8]}@example.com", password=hash_password(PASSWORD),
             nom="Test", prenom="Compte", role_id=role_id, type_utilisateur=type_utilisateur, is_active=True,
             created_at=now(), updated_at=now(), **extra)
    db.add(u)
    db.commit()
    db.refresh(u)
    return u


def _token(client, user):
    r = client.post("/api/v1/auth/login", json={"login": user.email, "password": PASSWORD})
    assert r.status_code == 200, r.text
    return r.json()["data"]["tokens"]["access_token"]


def _png():
    buf = io.BytesIO()
    Image.new("RGB", (300, 200), "white").save(buf, "PNG")
    return buf.getvalue()


@pytest.fixture
def admin_h(client, db):
    return _auth(_token(client, _user(db, "admin", "admin")))


@pytest.fixture
def doctor(client, db, admin_h):
    """Médecin créé par l'admin, disponible tous les jours 8 h – 18 h, consultations de 30 min."""
    body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "nom": "kaneza", "prenom": "alice",
            "specialite": "Médecine générale", "numero_licence": "BI-12345", "annees_experience": 8,
            "langues": ["kirundi", "français"], "prix_usd": "50.00", "prix_bif": "40000", "horaires": ALL_WEEK}
    r = client.post("/api/v1/admin/medecins", headers=admin_h, json=body)
    assert r.status_code == 201, r.text
    data = r.json()["data"]
    m = db.scalar(select(Medecin).where(Medecin.uuid == data["uuid"]))
    m.user.password = hash_password(PASSWORD)  # en vrai : choisi via le code d'invitation
    db.commit()
    return {"uuid": data["uuid"], "model": m, "h": _auth(_token(client, m.user))}


@pytest.fixture
def patient(client, db):
    u = _user(db, pays_id=BURUNDI, telephone="+25779111222")
    return {"user": u, "h": _auth(_token(client, u))}


def _slot(client, doctor_uuid, day_offset=1, index=0):
    day = date.today() + timedelta(days=day_offset)
    r = client.get(f"/api/v1/medecins/{doctor_uuid}/creneaux", params={"du": day.isoformat(), "au": day.isoformat()})
    assert r.status_code == 200, r.text
    return r.json()["data"][index]["debut"]


def _book(client, patient, doctor, **extra):
    body = {**FORM, "medecin_uuid": doctor["uuid"], "debut": _slot(client, doctor["uuid"]),
            "symptomes": "Maux de tête et fièvre depuis trois jours.", **extra}
    r = client.post("/api/v1/consultations", headers=patient["h"], json=body)
    assert r.status_code == 201, r.text
    return r.json()["data"]


def _set_start(db, numero, minutes_from_now):
    c = db.scalar(select(Consultation).where(Consultation.numero_consultation == numero))
    c.date_souhaitee = (now() + timedelta(minutes=minutes_from_now)).replace(second=0)
    db.commit()
    return c


def _pay(client, db, admin_h, numero):
    r = client.post(f"/api/v1/admin/consultations/{numero}/paiement", headers=admin_h, json={"statut": "paye"})
    assert r.status_code == 200, r.text


# ───────────── médecins et créneaux ─────────────

def test_doctor_public_listing_and_profile(client, doctor):
    items = client.get("/api/v1/medecins", params={"q": "kaneza"}).json()["data"]
    assert len(items) == 1 and items[0]["prix_bif"] == "40000.00" and items[0]["langues"] == ["Kirundi", "Français"]
    detail = client.get(f"/api/v1/medecins/{doctor['uuid']}").json()["data"]
    assert len(detail["horaires"]) == 7 and detail["duree_minutes"] == 30


def test_slots_follow_schedule_and_lead_time(client, doctor):
    day = date.today() + timedelta(days=1)
    slots = client.get(f"/api/v1/medecins/{doctor['uuid']}/creneaux",
                       params={"du": day.isoformat(), "au": day.isoformat()}).json()["data"]
    assert len(slots) == 20  # 8 h – 18 h en créneaux de 30 min
    assert slots[0]["debut"].endswith("T08:00:00+02:00") and slots[-1]["fin"].endswith("T18:00:00+02:00")
    r = client.get(f"/api/v1/medecins/{doctor['uuid']}/creneaux", params={"du": "2026-01-01", "au": "2026-03-01"})
    assert r.status_code == 422


def test_doctor_duration_changes_slots(client, doctor, admin_h):
    client.patch(f"/api/v1/admin/medecins/{doctor['uuid']}", headers=admin_h, json={"duree_consultation_minutes": 45})
    day = date.today() + timedelta(days=1)
    slots = client.get(f"/api/v1/medecins/{doctor['uuid']}/creneaux",
                       params={"du": day.isoformat(), "au": day.isoformat()}).json()["data"]
    assert len(slots) == 13 and slots[1]["debut"].endswith("T08:45:00+02:00")


def test_absence_blocks_slots_and_reports_conflicts(client, doctor, patient):
    booked = _book(client, patient, doctor)
    day = date.today() + timedelta(days=1)
    r = client.post("/api/v1/medecin/absences", headers=doctor["h"],
                    json={"debut": f"{day}T00:00:00", "fin": f"{day}T23:59:00", "motif": "Congé"})
    assert r.status_code == 201 and booked["numero"] in r.json()["data"]["rendez_vous_en_conflit"]
    slots = client.get(f"/api/v1/medecins/{doctor['uuid']}/creneaux",
                       params={"du": day.isoformat(), "au": day.isoformat()}).json()["data"]
    assert slots == []
    absence_id = r.json()["data"]["id"]
    assert client.delete(f"/api/v1/medecin/absences/{absence_id}", headers=doctor["h"]).status_code == 200


def test_schedule_validation(client, doctor):
    r = client.put("/api/v1/medecin/horaires", headers=doctor["h"],
                   json=[{"jour": "lundi", "debut": "08:00", "fin": "12:00"},
                         {"jour": "lundi", "debut": "11:00", "fin": "14:00"}])
    assert r.status_code == 422 and r.json()["error"]["code"] == "INVALID_SCHEDULE"
    r = client.put("/api/v1/medecin/horaires", headers=doctor["h"], json=[{"jour": "lundi", "debut": "08:07", "fin": "12:00"}])
    assert r.status_code == 422


def test_only_admin_creates_doctors(client, patient, doctor):
    body = {"email": "x@example.com", "nom": "A", "prenom": "B", "specialite": "Pédiatrie", "numero_licence": "X1",
            "prix_usd": "10", "prix_bif": "10000"}
    for h in (patient["h"], doctor["h"]):
        assert client.post("/api/v1/admin/medecins", headers=h, json=body).status_code == 403


# ───────────── réservation ─────────────

def test_booking_price_is_computed_by_server(client, db, patient, doctor):
    data = _book(client, patient, doctor, prix_ht="1.00", devise="USD")  # champs ignorés
    assert data["prix_ht"] == "40000.00" and data["devise"] == "BIF"  # patient résident du Burundi
    assert data["statut"] == "en_attente" and data["peut_annuler"] and data["peut_deplacer"]
    other = _user(db, pays_id=75)  # France
    h = _auth(_token(client, other))
    r = client.post("/api/v1/consultations", headers=h, json={
        **FORM, "pays_residence_id": 75, "whatsapp": "+33612345678", "medecin_uuid": doctor["uuid"],
        "debut": _slot(client, doctor["uuid"], 2), "symptomes": "Toux persistante depuis une semaine."})
    assert r.json()["data"]["devise"] == "USD" and r.json()["data"]["prix_ht"] == "50.00"


def test_booking_requires_account(client, doctor):
    r = client.post("/api/v1/consultations", json={**FORM, "medecin_uuid": doctor["uuid"],
                                                    "debut": "2030-01-01T10:00:00", "symptomes": "x" * 30})
    assert r.status_code == 401


def test_double_booking_refused(client, db, patient, doctor):
    slot = _slot(client, doctor["uuid"])
    body = {**FORM, "medecin_uuid": doctor["uuid"], "debut": slot, "symptomes": "Douleurs abdominales depuis hier.",
            "whatsapp": "+25779333444"}
    assert client.post("/api/v1/consultations", headers=patient["h"], json=body).status_code == 201
    other = _auth(_token(client, _user(db)))
    r = client.post("/api/v1/consultations", headers=other, json=body)
    assert r.status_code == 409 and r.json()["error"]["code"] == "SLOT_UNAVAILABLE"
    taken = client.get(f"/api/v1/medecins/{doctor['uuid']}/creneaux",
                       params={"du": slot[:10], "au": slot[:10]}).json()["data"]
    assert slot not in [s["debut"] for s in taken]


def test_off_grid_and_past_slots_refused(client, patient, doctor):
    tomorrow = date.today() + timedelta(days=1)
    for debut in (f"{tomorrow}T08:10:00", f"{tomorrow}T19:00:00", "2020-01-01T10:00:00"):
        r = client.post("/api/v1/consultations", headers=patient["h"],
                        json={**FORM, "medecin_uuid": doctor["uuid"], "debut": debut,
                              "symptomes": "Fièvre et fatigue générale."})
        assert r.status_code == 409, debut


def test_slot_with_timezone_is_converted(client, patient, doctor):
    tomorrow = date.today() + timedelta(days=1)
    # 07:00 UTC = 09:00 au Burundi
    data = _book(client, patient, doctor, debut=f"{tomorrow}T07:00:00Z")
    assert data["debut"] == f"{tomorrow}T09:00:00+02:00"


def test_doctor_notified_on_booking(client, patient, doctor, outbox, pushes):
    _book(client, patient, doctor)
    assert any("Nouvelle demande" in m.subject and m.to == doctor["model"].user.email for m in outbox)


def test_other_patient_cannot_see_consultation(client, db, patient, doctor):
    data = _book(client, patient, doctor)
    intruder = _auth(_token(client, _user(db)))
    assert client.get(f"/api/v1/consultations/{data['numero']}", headers=intruder).status_code == 404


# ───────────── documents privés ─────────────

def test_documents_private_with_signed_links(client, db, patient, doctor, admin_h):
    numero = _book(client, patient, doctor)["numero"]
    r = client.post(f"/api/v1/consultations/{numero}/documents", headers=patient["h"],
                    files={"fichier": ("analyse.png", _png(), "image/png")})
    assert r.status_code == 201, r.text
    doc_id = r.json()["data"]["id"]
    link = client.get(f"/api/v1/consultations/{numero}/documents/{doc_id}/lien", headers=patient["h"]).json()["data"]
    path = urlparse(link["url"]).path
    r = client.get(path)
    assert r.status_code == 200 and r.headers["content-type"] == "image/jpeg"
    assert r.headers["cache-control"] == "private, no-store"
    # Le médecin y a accès, l'admin non (secret médical)
    assert client.get(f"/api/v1/medecin/consultations/{numero}/documents/{doc_id}/lien", headers=doctor["h"]).status_code == 200
    assert client.get(f"/api/v1/admin/consultations/{numero}/documents/{doc_id}/lien", headers=admin_h).status_code == 404
    assert client.get("/api/v1/fichiers/faux.jeton.ici").status_code == 404


def test_dangerous_files_refused(client, patient, doctor):
    numero = _book(client, patient, doctor)["numero"]
    for name, content in (("x.pdf", b"%PDF-1.4 /JavaScript (app.alert(1))"), ("x.php", b"<?php system('id'); ?>")):
        r = client.post(f"/api/v1/consultations/{numero}/documents", headers=patient["h"],
                        files={"fichier": (name, content, "application/octet-stream")})
        assert r.status_code == 422, name


# ───────────── parcours complet ─────────────

def test_full_journey(client, db, patient, doctor, admin_h, outbox):
    numero = _book(client, patient, doctor)["numero"]
    # Pas de paiement en ligne : le patient téléverse la preuve d'un paiement fait hors ligne
    modes = client.get("/api/v1/modes-paiement").json()["data"]
    r = client.post(f"/api/v1/consultations/{numero}/paiement", headers=patient["h"],
                    data={"mode_paiement_id": str(modes[0]["id"])}, files={"preuve": ("recu.png", _png(), "image/png")})
    assert r.status_code == 200, r.text

    # L'admin voit la preuve de paiement mais pas les symptômes
    admin_view = client.get(f"/api/v1/admin/consultations/{numero}", headers=admin_h).json()["data"]
    assert admin_view["symptomes"] is None and admin_view["documents"][0]["kind"] == "preuve_paiement"
    _pay(client, db, admin_h, numero)
    assert db.query(Paiement).join(Consultation).filter(Consultation.numero_consultation == numero).count() == 1

    r = client.post(f"/api/v1/medecin/consultations/{numero}/confirmer", headers=doctor["h"])
    assert r.status_code == 200 and r.json()["data"]["statut"] == "confirmee"
    c = db.scalar(select(Consultation).where(Consultation.numero_consultation == numero))
    assert c.room_url.startswith("https://meet.jit.si/nufotec-") and len(c.room_id) > 25

    # Trop tôt pour rejoindre, puis la salle s'ouvre 10 min avant
    assert client.get(f"/api/v1/consultations/{numero}/rejoindre", headers=patient["h"]).json()["error"]["code"] == "NOT_OPEN_YET"
    _set_start(db, numero, 5)
    join = client.get(f"/api/v1/consultations/{numero}/rejoindre", headers=patient["h"]).json()["data"]
    assert join["url"] == c.room_url

    assert client.post(f"/api/v1/medecin/consultations/{numero}/demarrer", headers=doctor["h"]).status_code == 200
    patient_view = client.get(f"/api/v1/consultations/{numero}", headers=patient["h"]).json()["data"]
    assert patient_view["diagnostic"] is None
    r = client.post(f"/api/v1/medecin/consultations/{numero}/terminer", headers=doctor["h"],
                    json={"diagnostic": "Grippe saisonnière", "traitement": "Repos et paracétamol", "notes_medecin": "RAS"})
    assert r.status_code == 200
    patient_view = client.get(f"/api/v1/consultations/{numero}", headers=patient["h"]).json()["data"]
    assert patient_view["diagnostic"] == "Grippe saisonnière" and patient_view["notes_medecin"] is None

    assert client.post(f"/api/v1/consultations/{numero}/avis", headers=patient["h"], json={"note": 5}).status_code == 200
    assert client.post(f"/api/v1/consultations/{numero}/avis", headers=patient["h"], json={"note": 1}).status_code == 409
    assert client.get(f"/api/v1/medecins/{doctor['uuid']}").json()["data"]["nombre_avis"] == 1

    history = [h.to_status for h in db.scalars(select(ConsultationHistory).join(Consultation)
                                                .where(Consultation.numero_consultation == numero)
                                                .order_by(ConsultationHistory.id))]
    assert history == ["en_attente", "confirmee", "en_cours", "terminee"]
    subjects = {m.subject for m in outbox}
    assert {"Nufotec – Consultation confirmée", "Nufotec – Paiement validé", "Nufotec – Compte-rendu disponible"} <= subjects


def test_invalid_transitions(client, db, patient, doctor):
    numero = _book(client, patient, doctor)["numero"]
    r = client.post(f"/api/v1/medecin/consultations/{numero}/terminer", headers=doctor["h"], json={"diagnostic": "x" * 5})
    assert r.status_code == 409 and r.json()["error"]["code"] == "INVALID_TRANSITION"
    client.post(f"/api/v1/medecin/consultations/{numero}/refuser", headers=doctor["h"], json={"motif": "Indisponible"})
    r = client.post(f"/api/v1/medecin/consultations/{numero}/confirmer", headers=doctor["h"])
    assert r.json()["error"]["code"] == "INVALID_TRANSITION"


def test_join_requires_payment(client, db, patient, doctor):
    numero = _book(client, patient, doctor)["numero"]
    client.post(f"/api/v1/medecin/consultations/{numero}/confirmer", headers=doctor["h"])
    _set_start(db, numero, 5)
    r = client.get(f"/api/v1/consultations/{numero}/rejoindre", headers=patient["h"])
    assert r.json()["error"]["code"] == "PAYMENT_REQUIRED"


def test_other_doctor_cannot_access(client, db, patient, doctor, admin_h):
    numero = _book(client, patient, doctor)["numero"]
    body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "nom": "B", "prenom": "C", "specialite": "ORL",
            "numero_licence": "Z9", "prix_usd": "30", "prix_bif": "30000"}
    other = client.post("/api/v1/admin/medecins", headers=admin_h, json=body).json()["data"]
    m = db.scalar(select(Medecin).where(Medecin.uuid == other["uuid"]))
    m.user.password = hash_password(PASSWORD)
    db.commit()
    h = _auth(_token(client, m.user))
    assert client.get(f"/api/v1/medecin/consultations/{numero}", headers=h).status_code == 404
    assert client.post(f"/api/v1/medecin/consultations/{numero}/confirmer", headers=h).status_code == 404


# ───────────── annulation, déplacement, absence ─────────────

def test_patient_cancel_window(client, db, patient, doctor):
    numero = _book(client, patient, doctor)["numero"]
    r = client.post(f"/api/v1/consultations/{numero}/annuler", headers=patient["h"], json={"motif": "Empêchement"})
    assert r.status_code == 200 and r.json()["data"]["statut"] == "annulee"
    numero2 = _book(client, patient, doctor)["numero"]
    _set_start(db, numero2, 60)  # moins de 2 h avant
    r = client.post(f"/api/v1/consultations/{numero2}/annuler", headers=patient["h"], json={})
    assert r.status_code == 409 and r.json()["error"]["code"] == "CANNOT_CANCEL"


def test_cancelled_slot_becomes_free_again(client, patient, doctor):
    data = _book(client, patient, doctor)
    client.post(f"/api/v1/consultations/{data['numero']}/annuler", headers=patient["h"], json={})
    day = data["debut"][:10]
    free = [s["debut"] for s in client.get(f"/api/v1/medecins/{doctor['uuid']}/creneaux",
                                           params={"du": day, "au": day}).json()["data"]]
    assert data["debut"] in free


def test_reschedule(client, db, patient, doctor, outbox):
    data = _book(client, patient, doctor)
    new_slot = _slot(client, doctor["uuid"], 2, 3)
    r = client.post(f"/api/v1/consultations/{data['numero']}/deplacer", headers=patient["h"], json={"debut": new_slot})
    assert r.status_code == 200 and r.json()["data"]["debut"] == new_slot
    assert any("déplacé" in m.subject for m in outbox)
    day = data["debut"][:10]
    free = [s["debut"] for s in client.get(f"/api/v1/medecins/{doctor['uuid']}/creneaux",
                                           params={"du": day, "au": day}).json()["data"]]
    assert data["debut"] in free  # l'ancien créneau est libéré


def test_no_show(client, db, patient, doctor):
    numero = _book(client, patient, doctor)["numero"]
    client.post(f"/api/v1/medecin/consultations/{numero}/confirmer", headers=doctor["h"])
    _set_start(db, numero, -5)
    assert client.post(f"/api/v1/medecin/consultations/{numero}/absent", headers=doctor["h"]).json()["error"]["code"] == "TOO_EARLY"
    _set_start(db, numero, -15)
    r = client.post(f"/api/v1/medecin/consultations/{numero}/absent", headers=doctor["h"])
    assert r.status_code == 200 and r.json()["data"]["statut"] == "absent"


# ───────────── tâches automatiques ─────────────

def test_reminders_and_expiry(client, db, patient, doctor, admin_h, outbox):
    confirmed = _book(client, patient, doctor)["numero"]
    client.post(f"/api/v1/medecin/consultations/{confirmed}/confirmer", headers=doctor["h"])
    pending = _book(client, patient, doctor)["numero"]
    _set_start(db, confirmed, 45)
    _set_start(db, pending, -30)
    outbox.clear()
    from app.core.mailer import deliver_pending

    assert send_due_reminders(db) >= 2
    deliver_pending()
    assert any("commence bientôt" in m.subject for m in outbox)
    assert any("Demande expirée" in m.subject for m in outbox)
    c = db.scalar(select(Consultation).where(Consultation.numero_consultation == pending))
    assert c.statut == "annulee"
    outbox.clear()
    send_due_reminders(db)
    deliver_pending()
    assert not any("commence bientôt" in m.subject for m in outbox)  # une seule fois


def test_calendar_file(client, patient, doctor):
    numero = _book(client, patient, doctor)["numero"]
    r = client.get(f"/api/v1/consultations/{numero}/agenda.ics", headers=patient["h"])
    assert r.status_code == 200 and r.headers["content-type"].startswith("text/calendar")
    assert "BEGIN:VEVENT" in r.text and f"UID:{numero}@nufotec" in r.text


# ───────────── administration ─────────────

def test_admin_reassign_and_stats(client, db, patient, doctor, admin_h, outbox):
    numero = _book(client, patient, doctor)["numero"]
    client.post(f"/api/v1/medecin/consultations/{numero}/confirmer", headers=doctor["h"])
    body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "nom": "B", "prenom": "C", "specialite": "Médecine générale",
            "numero_licence": "Z8", "prix_usd": "50", "prix_bif": "40000", "horaires": ALL_WEEK}
    other = client.post("/api/v1/admin/medecins", headers=admin_h, json=body).json()["data"]
    r = client.post(f"/api/v1/admin/consultations/{numero}/reattribuer", headers=admin_h,
                    json={"medecin_uuid": other["uuid"]})
    assert r.status_code == 200 and r.json()["data"]["statut"] == "en_attente"  # le nouveau médecin doit confirmer
    stats = client.get("/api/v1/admin/consultations/stats", headers=admin_h).json()["data"]
    assert stats["total"] >= 1 and "en_attente" in stats["par_statut"]


def test_payment_state_rules(client, db, patient, doctor, admin_h):
    numero = _book(client, patient, doctor)["numero"]
    r = client.post(f"/api/v1/admin/consultations/{numero}/paiement", headers=admin_h, json={"statut": "rembourse"})
    assert r.status_code == 409
    _pay(client, db, admin_h, numero)
    r = client.post(f"/api/v1/consultations/{numero}/paiement", headers=patient["h"],
                    data={"mode_paiement_id": "3"}, files={"preuve": ("r.png", _png(), "image/png")})
    assert r.json()["error"]["code"] == "ALREADY_PAID"


def test_doctor_routes_need_doctor_profile(client, patient):
    assert client.get("/api/v1/medecin/consultations", headers=patient["h"]).json()["error"]["code"] == "NOT_A_DOCTOR"


def test_existing_php_doctor_still_readable(client):
    """Le médecin déjà en base (créé par le PHP) apparaît avec ses tarifs convertis."""
    items = client.get("/api/v1/medecins").json()["data"]
    legacy = [m for m in items if m["prix_bif"] == "40000.00" and m["prix_usd"] == "50.00"]
    assert legacy


def test_dates_are_iso_with_tz(client, patient, doctor):
    data = _book(client, patient, doctor)
    assert datetime.fromisoformat(data["debut"]).utcoffset() == timedelta(hours=2)


# ───────────── WhatsApp vers le médecin ─────────────

def test_booking_returns_whatsapp_link_to_chosen_doctor(client, db, patient, doctor, admin_h):
    from urllib.parse import parse_qs, urlparse as parse

    client.patch(f"/api/v1/admin/medecins/{doctor['uuid']}", headers=admin_h, json={"whatsapp": "79 555 666"})
    data = _book(client, patient, doctor)
    wa = data["whatsapp_medecin"]
    assert wa["numero"] == "+25779555666"
    url = parse(wa["url"])
    assert url.netloc == "wa.me" and url.path == "/25779555666"
    message = parse_qs(url.query)["text"][0]
    assert message == wa["message"]
    for expected in ("DEMANDE DE CONSULTATION EN LIGNE - NUFOTEC", f"N° suivi : {data['numero']}", "Âge : 38 ans",
                     "Pays : Burundi", "Poids : 70 kg | Taille : 150 cm", "Maux de tête", "40.000 BIF", "50.00 USD",
                     "Mode de paiement : Lumicash", "Montant à payer : 40.000 BIF", "EcoCash: +257 79 666 439"):
        assert expected in message, expected
    assert patient["user"].email in message


def test_whatsapp_falls_back_to_doctor_phone_then_central_number(client, db, patient, doctor):
    doctor["model"].user.telephone = "+25779123123"
    db.commit()
    data = _book(client, patient, doctor)
    assert data["whatsapp_medecin"]["numero"] == "+25779123123"
    doctor["model"].user.telephone = None
    db.commit()
    again = client.get(f"/api/v1/consultations/{data['numero']}/whatsapp", headers=patient["h"]).json()["data"]
    assert again["numero"] == "+25779666439"  # parametres.contact_whatsapp


def test_booking_form_required_fields(client, patient, doctor):
    r = client.post("/api/v1/consultations", headers=patient["h"],
                    json={"medecin_uuid": doctor["uuid"], "debut": _slot(client, doctor["uuid"]),
                          "symptomes": "Maux de tête et fièvre depuis trois jours."})
    fields = {d["field"] for d in r.json()["error"]["details"]}
    assert r.status_code == 422 and {"age", "poids", "taille", "pays_residence_id", "mode_paiement_id"} <= fields


def test_whatsapp_required_when_profile_has_no_phone(client, db, doctor):
    u = _user(db, pays_id=BURUNDI)
    h = _auth(_token(client, u))
    r = client.post("/api/v1/consultations", headers=h, json={
        **FORM, "medecin_uuid": doctor["uuid"], "debut": _slot(client, doctor["uuid"]),
        "symptomes": "Maux de tête et fièvre depuis trois jours."})
    assert r.status_code == 422 and r.json()["error"]["details"][0]["field"] == "whatsapp"


def test_legacy_schedule_with_odd_minutes_still_displays(client, db, doctor):
    """Un horaire hérité du PHP (« 23:03 ») ne doit pas empêcher d'afficher la fiche du médecin."""
    from datetime import time

    doctor["model"].horaires[0].heure_fin = time(23, 3)
    db.commit()
    r = client.get(f"/api/v1/medecins/{doctor['uuid']}")
    assert r.status_code == 200 and any(h["fin"] == "23:03:00" for h in r.json()["data"]["horaires"])
