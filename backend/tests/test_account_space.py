"""Espace « Mon compte » : rubriques affichées seulement si la personne a quelque chose ;
réservations faites sans compte retrouvées par le WhatsApp du profil, sans données médicales."""

import uuid

from tests.test_consultation_guest import _body, _doctor, _slot
from tests.test_investors import _investor_form

BURUNDI = 194
PHONE = "+25779456123"


def _register(client, email, **extra):
    r = client.post("/api/v1/auth/register", json={"email": email, "password": "Kirundo2026x", "nom": "Ndayizeye",
                                                   "prenom": "Alice", "pays_id": BURUNDI, **extra})
    assert r.status_code == 201, r.text
    return {"Authorization": f"Bearer {r.json()['data']['tokens']['access_token']}"}


def test_empty_account_has_no_sections(client, db):
    h = _register(client, f"test-{uuid.uuid4().hex[:8]}@example.com")
    data = client.get("/api/v1/mon-espace", headers=h).json()["data"]
    assert data["commandes"] == 0 and data["consultations"] == 0
    assert data["investisseur"] is False and data["courtier"] is False


def test_guest_booking_and_order_found_by_profile_phone_without_medical_data(client, db):
    doctor = _doctor(client, db)
    booked = client.post("/api/v1/consultations/demande",
                         json=_body(doctor, _slot(client, doctor), whatsapp=PHONE)).json()["data"]
    product = client.get("/api/v1/produits", params={"per_page": 1}).json()["data"]["items"][0]
    client.post(f"/api/v1/produits/{product['slug']}/commander", json={
        "nom_complet": "Alice Ndayizeye", "telephone": PHONE, "pays_id": BURUNDI, "ville": "Gitega", "adresse": "Centre"})

    h = _register(client, f"test-{uuid.uuid4().hex[:8]}@example.com", telephone=PHONE)
    space = client.get("/api/v1/mon-espace", headers=h).json()["data"]
    assert space["consultations"] == 1 and space["commandes"] == 1

    items = client.get("/api/v1/consultations", headers=h).json()["data"]["items"]
    assert len(items) == 1 and items[0]["numero"] == booked["numero"]
    c = items[0]
    assert c["lie_par_telephone"] is True and c["symptomes"] is None and c["age"] is None and c["documents"] == []
    assert c["peut_annuler"] is False and c["medecin"]["nom"]
    # le détail complet reste réservé au compte qui a réservé
    assert client.get(f"/api/v1/consultations/{booked['numero']}", headers=h).status_code == 404


def test_investor_file_linked_shows_section(client, db):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    h = _register(client, email, type_utilisateur="investisseur")
    client.post("/api/v1/investisseurs/manifestation", headers=h, json=_investor_form(email))
    space = client.get("/api/v1/mon-espace", headers=h).json()["data"]
    assert space["investisseur"] is True and space["investisseur_statut"] == "nouveau"
