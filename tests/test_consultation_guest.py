"""Réservation SANS compte : nom + WhatsApp saisis, demande enregistrée puis envoyée au médecin par WhatsApp."""

import io
import uuid
from datetime import date, timedelta
from urllib.parse import unquote

from PIL import Image
from sqlalchemy import select

from app.core.helpers import now
from app.core.security import hash_password
from app.models import Consultation, ConsultationDocument, Role, User

BURUNDI = 194
ALL_WEEK = [{"jour": j, "debut": "08:00", "fin": "18:00"}
            for j in ("lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi", "dimanche")]


def _png():
    buf = io.BytesIO()
    Image.new("RGB", (60, 40), "white").save(buf, "PNG")
    return buf.getvalue()


def _doctor(client, db):
    admin = User(uuid=str(uuid.uuid4()), email=f"test-{uuid.uuid4().hex[:8]}@example.com", password=hash_password("MotDePasse123"),
                 role_id=db.scalar(select(Role.id).where(Role.slug == "admin")), type_utilisateur="admin", is_active=True,
                 nom="Admin", prenom="Test", created_at=now(), updated_at=now())
    db.add(admin)
    db.commit()
    token = client.post("/api/v1/auth/login", json={"login": admin.email, "password": "MotDePasse123"}).json()["data"]["tokens"]["access_token"]
    body = {"email": f"test-{uuid.uuid4().hex[:8]}@example.com", "nom": "Ndayishimiye", "prenom": "Claire", "specialite": "Pédiatrie",
            "numero_licence": "BI-GUEST-1", "prix_usd": "40", "prix_bif": "30000", "horaires": ALL_WEEK, "whatsapp": "+25779555111"}
    r = client.post("/api/v1/admin/medecins", headers={"Authorization": f"Bearer {token}"}, json=body)
    assert r.status_code == 201, r.text
    return r.json()["data"]["uuid"]


def _slot(client, doctor_uuid):
    day = (date.today() + timedelta(days=1)).isoformat()
    return client.get(f"/api/v1/medecins/{doctor_uuid}/creneaux", params={"du": day, "au": day}).json()["data"][0]["debut"]


def _body(doctor_uuid, debut, **extra):
    return {"medecin_uuid": doctor_uuid, "debut": debut, "nom_complet": "Aline Niyonzima", "whatsapp": "+25779123456",
            "pays_residence_id": BURUNDI, "symptomes": "Fièvre et toux depuis trois jours, fatigue importante.",
            "duree_symptomes": "2-3j", "age": 32, "poids": "58", "taille": "162", **extra}


def test_book_without_account_and_attach_files(client, db, outbox):
    doctor = _doctor(client, db)
    r = client.post("/api/v1/consultations/demande", json=_body(doctor, _slot(client, doctor), mode_paiement_id=3))
    assert r.status_code == 201, r.text
    data = r.json()["data"]
    numero, jeton = data["numero"], data["jeton"]
    assert data["devise"] == "BIF" and str(data["prix_ht"]).startswith("30000")
    message = unquote(data["whatsapp_medecin"]["url"])
    assert "ALINE NIYONZIMA" in message and "+25779123456" in message
    # même format de tarif que sur le site : « Résidant du Burundi : 30.000 BIF », « 40.00 USD »
    assert "Résidant du Burundi : 30.000 BIF" in message and "Résidant à l'étranger : 40.00 USD" in message
    assert "Expert :" in message and "Médecin" not in message
    assert "Depuis : 2 à 3 jours" in message and "dans cette conversation WhatsApp" in message
    # Email à l'expert : WhatsApp et pays du patient
    mail = next(m for m in outbox if "Nouvelle demande de consultation" in m.subject)
    assert "+25779123456" in mail.text and "Pays de résidence : Burundi" in mail.text and numero in mail.text
    c = db.scalar(select(Consultation).where(Consultation.numero_consultation == numero))
    assert c.patient_id is None and c.patient_name == "Aline Niyonzima"

    doc = client.post(f"/api/v1/consultations/demande/{numero}/documents", data={"jeton": jeton},
                      files={"fichier": ("analyse.pdf", b"%PDF-1.4 resultats", "application/pdf")})
    assert doc.status_code == 201, doc.text
    pay = client.post(f"/api/v1/consultations/demande/{numero}/paiement", data={"jeton": jeton, "mode_paiement_id": "3"},
                      files={"preuve": ("recu.png", _png(), "image/png")})
    assert pay.status_code == 200, pay.text
    kinds = {d.kind for d in db.scalars(select(ConsultationDocument).where(ConsultationDocument.consultation_id == c.id))}
    assert kinds == {"document", "preuve_paiement"}


def test_guest_code_is_required_and_bound_to_the_consultation(client, db):
    doctor = _doctor(client, db)
    first = client.post("/api/v1/consultations/demande", json=_body(doctor, _slot(client, doctor))).json()["data"]
    files = {"fichier": ("a.pdf", b"%PDF-1.4 x", "application/pdf")}
    assert client.post(f"/api/v1/consultations/demande/{first['numero']}/documents", data={"jeton": "faux"}, files=files).status_code == 401
    other_slot = client.get(f"/api/v1/medecins/{doctor}/creneaux",
                            params={"du": (date.today() + timedelta(days=2)).isoformat()}).json()["data"][0]["debut"]
    second = client.post("/api/v1/consultations/demande", json=_body(doctor, other_slot)).json()["data"]
    r = client.post(f"/api/v1/consultations/demande/{first['numero']}/documents", data={"jeton": second["jeton"]}, files=files)
    assert r.status_code == 403


def test_guest_fields_are_cleaned_and_validated(client, db):
    doctor = _doctor(client, db)
    slot = _slot(client, doctor)
    bad = client.post("/api/v1/consultations/demande", json=_body(doctor, slot, nom_complet="<>{}"))
    assert bad.status_code == 422 and bad.json()["error"]["details"][0]["field"] == "nom_complet"
    assert client.post("/api/v1/consultations/demande", json=_body(doctor, slot, site_web="robot")).status_code == 422
    ok = client.post("/api/v1/consultations/demande",
                     json=_body(doctor, slot, nom_complet="Jean <b>Dupont</b>",
                                symptomes="Douleurs <script>x</script> au ventre depuis une semaine."))
    assert ok.status_code == 201, ok.text
    c = db.scalar(select(Consultation).where(Consultation.numero_consultation == ok.json()["data"]["numero"]))
    assert c.patient_name == "Jean bDupontb" and "<" not in c.symptomes
