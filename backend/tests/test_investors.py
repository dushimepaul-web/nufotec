"""Investisseurs et courtiers : formulaires, liaison stricte, pipeline, data room + NDA, attribution."""

import uuid
from datetime import datetime, timedelta
from urllib.parse import urlparse

import pytest
from sqlalchemy import select

from app.core.helpers import now
from app.core.security import hash_password
from app.models import Broker, BrokerInvestor, DataRoomDownload, Investor, InvestorNote, Role, User
from app.modules.investors.service import send_followups

PASSWORD = "MotDePasse123"
BURUNDI = 194


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


def _user(db, slug="user", type_utilisateur="investisseur", verified=True, email=None):
    u = User(uuid=str(uuid.uuid4()), email=email or f"test-{uuid.uuid4().hex[:8]}@example.com",
             password=hash_password(PASSWORD), nom="Test", prenom="Compte",
             role_id=db.scalar(select(Role.id).where(Role.slug == slug)), type_utilisateur=type_utilisateur,
             is_active=True, email_verified_at=now() if verified else None, created_at=now(), updated_at=now())
    db.add(u)
    db.commit()
    return u


def _login(client, user):
    r = client.post("/api/v1/auth/login", json={"login": user.email, "password": PASSWORD})
    return _auth(r.json()["data"]["tokens"]["access_token"])


@pytest.fixture
def admin(client, db):
    u = _user(db, "admin", "admin")
    return {"user": u, "h": _login(client, u)}


def _investor_form(email, **extra):
    return {"nom_complet": "Grace Iradukunda", "organisation": "Lake Capital", "pays_id": BURUNDI, "email": email,
            "interets": ["equity", "grant"], "montant": "1M-5M", "domaines": ["gmp_facility"],
            "calendrier": "3-6 months", "message": "Intéressés par l'usine GMP.", "accepte_contact": True,
            "confirmation_non_engageante": True, **extra}


def _broker_form(email, **extra):
    return {"nom_complet": "Jean Ndayishimiye", "cabinet": "Great Lakes Advisory", "pays_id": BURUNDI, "email": email,
            "statut_reglementaire": "Licensed", "capacites": ["placement_agent"], "types_investisseurs": ["dfi"],
            "mandats": ["equity"], "modele_remuneration": "Success Commission", "confirme_autorise": True,
            "confirme_aml_kyc": True, "reconnait_non_exclusivite": True, "comprend_mandat_formel": True, **extra}


def _approved_broker(client, db, admin):
    u = _user(db, type_utilisateur="broker")
    client.post("/api/v1/courtiers/candidature", json=_broker_form(u.email))
    b = db.scalar(select(Broker).where(Broker.email == u.email))
    client.post(f"/api/v1/admin/courtiers/{b.uuid}/decision", headers=admin["h"], json={"statut": "approuve"})
    return {"user": u, "model": b, "h": _login(client, u)}


# ───────────── public ─────────────

def test_phases_public_with_labels(client):
    phases = client.get("/api/v1/investissement/phases").json()["data"]
    assert phases and phases[0]["repartition"]
    assert any(r["libelle"] == "Construction" for r in phases[0]["repartition"])


def test_investor_form_creates_lead_and_emails(client, db, outbox):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    r = client.post("/api/v1/investisseurs/manifestation", json=_investor_form(email.upper()))
    assert r.status_code == 201, r.text
    i = db.scalar(select(Investor).where(Investor.email == email))
    assert i.statut == "nouveau" and i.interest_equity and i.interest_grant and not i.interest_debt
    assert i.focus_gmp_facility and i.consent_at and i.user_id is None
    assert any(m.to == email for m in outbox) and any("manifestation" in m.subject.lower() for m in outbox)


def test_duplicate_form_adds_note_not_duplicate(client, db):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    client.post("/api/v1/investisseurs/manifestation", json=_investor_form(email))
    r = client.post("/api/v1/investisseurs/manifestation", json=_investor_form(email, message="Relance"))
    assert r.status_code == 201  # même réponse : on ne révèle pas que l'email est connu
    assert db.query(Investor).filter(Investor.email == email).count() == 1
    i = db.scalar(select(Investor).where(Investor.email == email))
    assert db.query(InvestorNote).filter(InvestorNote.investor_id == i.id).count() == 2


def test_consent_and_honeypot(client, db):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    r = client.post("/api/v1/investisseurs/manifestation", json=_investor_form(email, accepte_contact=False))
    assert r.status_code == 422 and r.json()["error"]["code"] == "CONSENT_REQUIRED"
    r = client.post("/api/v1/investisseurs/manifestation", json=_investor_form(email, site_web="http://spam"))
    assert r.status_code == 201 and db.query(Investor).filter(Investor.email == email).count() == 0


def test_broker_form_requires_all_confirmations(client, db):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    r = client.post("/api/v1/courtiers/candidature", json=_broker_form(email, confirme_aml_kyc=False))
    assert r.status_code == 422 and r.json()["error"]["code"] == "CONFIRMATIONS_REQUIRED"
    assert client.post("/api/v1/courtiers/candidature", json=_broker_form(email)).status_code == 201
    b = db.scalar(select(Broker).where(Broker.email == email))
    assert b.statut == "en_attente" and b.capacity_placement_agent and b.investor_dfi


def test_unicode_names_supported(client, db):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    client.post("/api/v1/investisseurs/manifestation", json=_investor_form(email, nom_complet="王小明 Ñandú عبد الله"))
    assert db.scalar(select(Investor.full_name).where(Investor.email == email)) == "王小明 Ñandú عبد الله"


# ───────────── liaison stricte au compte ─────────────

def test_link_only_by_verified_email_never_by_name(client, db):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    client.post("/api/v1/investisseurs/manifestation", json=_investor_form(email))
    # Même nom, autre email : aucun accès (faille du PHP)
    homonym = _user(db)
    homonym.nom, homonym.prenom = "Iradukunda", "Grace"
    db.commit()
    assert client.get("/api/v1/investisseur/dossier", headers=_login(client, homonym)).status_code == 404
    # Bon email mais non vérifié : pas d'accès
    unverified = _user(db, email=email.replace("test-", "test2-"), verified=False)
    unverified.email = email
    db.commit()
    assert client.get("/api/v1/investisseur/dossier", headers=_login(client, unverified)).status_code == 404
    # Email identique et vérifié : rattachement
    unverified.email_verified_at = now()
    db.commit()
    r = client.get("/api/v1/investisseur/dossier", headers=_login(client, unverified))
    assert r.status_code == 200 and r.json()["data"]["email"] == email


# ───────────── pipeline (admin) ─────────────

def test_pipeline_status_note_assign_follow_up(client, db, admin, outbox):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    client.post("/api/v1/investisseurs/manifestation", json=_investor_form(email))
    i = db.scalar(select(Investor).where(Investor.email == email))
    base = f"/api/v1/admin/investisseurs/{i.uuid}"
    assert client.post(f"{base}/statut", headers=admin["h"], json={"statut": "contacte", "note": "Appel fait"}).status_code == 200
    assert client.post(f"{base}/notes", headers=admin["h"], json={"texte": "Attend le business plan"}).status_code == 200
    r = client.post(f"{base}/responsable", headers=admin["h"], json={"admin_uuid": admin["user"].uuid})
    assert r.json()["data"]["responsable"] == "Compte Test"
    when = (datetime.now() + timedelta(days=3)).replace(microsecond=0).isoformat()
    assert client.post(f"{base}/relance", headers=admin["h"], json={"le": when}).status_code == 200
    detail = client.get(base, headers=admin["h"]).json()["data"]
    kinds = [h["type"] for h in detail["historique"]]
    assert {"statut", "note", "assignation", "relance", "manifestation"} <= set(kinds)
    # La relance part par email quand elle est due
    i.prochaine_relance = now() - timedelta(minutes=1)
    db.commit()
    outbox.clear()
    assert send_followups(db) >= 1
    from app.core.mailer import deliver_pending

    deliver_pending()
    assert any(m.to == admin["user"].email and "Relance" in m.subject for m in outbox)


def test_admin_list_filters_stats_export(client, db, admin):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    # Le nom est nettoyé à la saisie ; l'organisation accepte « = » : l'export CSV doit neutraliser la formule
    client.post("/api/v1/investisseurs/manifestation", json=_investor_form(email, nom_complet="=CMD|calc", organisation="=SUM(A1)"))
    r = client.get("/api/v1/admin/investisseurs", headers=admin["h"], params={"q": email, "montant": "1M-5M"})
    assert r.json()["data"]["meta"]["total"] == 1
    stats = client.get("/api/v1/admin/investisseurs/stats", headers=admin["h"]).json()["data"]
    assert stats["total"] >= 1 and "nouveau" in stats["par_statut"]
    csv_text = client.get("/api/v1/admin/investisseurs/export.csv", headers=admin["h"], params={"q": email}).text
    assert "'=SUM(A1)" in csv_text and "CMDcalc" in csv_text


def test_admin_only(client, db):
    h = _login(client, _user(db))
    for path in ("/admin/investisseurs", "/admin/courtiers", "/admin/data-room"):
        assert client.get(f"/api/v1{path}", headers=h).status_code == 403


# ───────────── data room + NDA ─────────────

def _qualified_investor(client, db, admin):
    u = _user(db)
    client.post("/api/v1/investisseurs/manifestation", json=_investor_form(u.email))
    i = db.scalar(select(Investor).where(Investor.email == u.email))
    client.post(f"/api/v1/admin/investisseurs/{i.uuid}/statut", headers=admin["h"], json={"statut": "qualifie"})
    return {"user": u, "model": i, "h": _login(client, u)}


def _upload(client, admin, titre="Business plan 2026"):
    r = client.post("/api/v1/admin/data-room", headers=admin["h"], data={"titre": titre},
                    files={"fichier": ("bp.pdf", b"%PDF-1.4 business plan", "application/pdf")})
    assert r.status_code == 201, r.text
    return r.json()["data"]


def test_data_room_requires_qualification_and_nda(client, db, admin):
    doc = _upload(client, admin)
    u = _user(db)
    client.post("/api/v1/investisseurs/manifestation", json=_investor_form(u.email))
    h = _login(client, u)
    assert client.get("/api/v1/investisseur/data-room", headers=h).json()["error"]["code"] == "NOT_QUALIFIED"
    assert client.post("/api/v1/investisseur/nda/accepter", headers=h).json()["error"]["code"] == "NOT_QUALIFIED"

    q = _qualified_investor(client, db, admin)
    assert client.get("/api/v1/investisseur/data-room", headers=q["h"]).json()["error"]["code"] == "NDA_REQUIRED"
    nda = client.get("/api/v1/investisseur/nda", headers=q["h"]).json()["data"]
    assert "CONFIDENTIALITÉ" in nda["texte"] and not nda["accepte"]
    assert client.post("/api/v1/investisseur/nda/accepter", headers=q["h"]).json()["data"]["accepte"]
    items = client.get("/api/v1/investisseur/data-room", headers=q["h"]).json()["data"]
    assert [d["uuid"] for d in items] == [doc["uuid"]]

    link = client.get(f"/api/v1/investisseur/data-room/{doc['uuid']}/lien", headers=q["h"]).json()["data"]
    r = client.get(urlparse(link["url"]).path)
    assert r.status_code == 200 and r.content.startswith(b"%PDF")
    assert db.query(DataRoomDownload).filter(DataRoomDownload.investor_id == q["model"].id).count() == 1
    log = client.get(f"/api/v1/admin/data-room/{doc['uuid']}/telechargements", headers=admin["h"]).json()["data"]
    assert log[0]["investisseur"] == "Grace Iradukunda"


def test_data_room_selection_visibility(client, db, admin):
    doc = _upload(client, admin, "Term sheet confidentielle")
    a, b = _qualified_investor(client, db, admin), _qualified_investor(client, db, admin)
    for x in (a, b):
        client.post("/api/v1/investisseur/nda/accepter", headers=x["h"])
    client.patch(f"/api/v1/admin/data-room/{doc['uuid']}", headers=admin["h"],
                 json={"visibilite": "selection", "investisseurs": [a["model"].uuid]})
    assert len(client.get("/api/v1/investisseur/data-room", headers=a["h"]).json()["data"]) == 1
    assert client.get("/api/v1/investisseur/data-room", headers=b["h"]).json()["data"] == []
    assert client.get(f"/api/v1/investisseur/data-room/{doc['uuid']}/lien", headers=b["h"]).status_code == 404


def test_data_room_link_cannot_open_medical_files(client, db, admin):
    from app.core.files import signed_link

    url, _ = signed_link(1, scope="dataroom")
    token = url.rsplit("/", 1)[-1]
    assert client.get(f"/api/v1/fichiers/{token}").status_code == 404  # jeton data room refusé côté consultations


def test_fake_data_room_file_refused(client, admin):
    r = client.post("/api/v1/admin/data-room", headers=admin["h"], data={"titre": "Faux"},
                    files={"fichier": ("x.pdf", b"<script>alert(1)</script>", "application/pdf")})
    assert r.status_code == 422


# ───────────── courtiers ─────────────

def test_broker_must_be_approved_to_introduce(client, db, admin):
    u = _user(db, type_utilisateur="broker")
    client.post("/api/v1/courtiers/candidature", json=_broker_form(u.email))
    h = _login(client, u)
    body = {"nom_complet": "Investisseur X", "email": f"test-{uuid.uuid4().hex[:8]}@example.com", "pays_id": BURUNDI}
    assert client.post("/api/v1/courtier/investisseurs", headers=h, json=body).json()["error"]["code"] == "BROKER_NOT_APPROVED"


def test_broker_refusal_needs_reason_and_emails(client, db, admin, outbox):
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    client.post("/api/v1/courtiers/candidature", json=_broker_form(email))
    b = db.scalar(select(Broker).where(Broker.email == email))
    r = client.post(f"/api/v1/admin/courtiers/{b.uuid}/decision", headers=admin["h"], json={"statut": "refuse"})
    assert r.status_code == 422
    client.post(f"/api/v1/admin/courtiers/{b.uuid}/decision", headers=admin["h"],
                json={"statut": "refuse", "motif": "Licence non vérifiable"})
    assert any(m.to == email and "non retenue" in m.subject for m in outbox)


def test_introduction_attribution_rules(client, db, admin):
    first, second = _approved_broker(client, db, admin), _approved_broker(client, db, admin)
    target = f"test-{uuid.uuid4().hex[:8]}@example.com"
    body = {"nom_complet": "Fonds Kibira", "organisation": "Kibira Fund", "email": target, "pays_id": BURUNDI,
            "montant": "5M+"}
    r = client.post("/api/v1/courtier/investisseurs", headers=first["h"], json=body)
    assert r.status_code == 201, r.text
    assert r.json()["data"]["attribue_jusqu_au"][:4] == str((datetime.now() + timedelta(days=365)).year)
    assert client.post("/api/v1/courtier/investisseurs", headers=first["h"], json=body).json()["error"]["code"] == "ALREADY_INTRODUCED"
    r = client.post("/api/v1/courtier/investisseurs", headers=second["h"], json={**body, "email": target.upper()})
    assert r.status_code == 409 and r.json()["error"]["code"] == "ALREADY_KNOWN"
    # Un investisseur venu en direct ne peut pas être « pris » par un courtier
    direct = f"test-{uuid.uuid4().hex[:8]}@example.com"
    client.post("/api/v1/investisseurs/manifestation", json=_investor_form(direct))
    r = client.post("/api/v1/courtier/investisseurs", headers=second["h"], json={**body, "email": direct})
    assert r.json()["error"]["code"] == "ALREADY_KNOWN"

    # Le pipeline se reflète chez le courtier
    i = db.scalar(select(Investor).where(Investor.email == target))
    assert i.source == "courtier" and i.broker_id == first["model"].id
    client.post(f"/api/v1/admin/investisseurs/{i.uuid}/statut", headers=admin["h"], json={"statut": "engage"})
    intros = client.get("/api/v1/courtier/investisseurs", headers=first["h"]).json()["data"]
    assert intros[0]["statut"] == "invested"
    assert db.scalar(select(BrokerInvestor.status).where(BrokerInvestor.investor_id == i.id)) == "invested"


def test_expired_attribution_frees_investor(client, db, admin):
    first, second = _approved_broker(client, db, admin), _approved_broker(client, db, admin)
    target = f"test-{uuid.uuid4().hex[:8]}@example.com"
    body = {"nom_complet": "Ancien contact", "email": target, "pays_id": BURUNDI}
    client.post("/api/v1/courtier/investisseurs", headers=first["h"], json=body)
    intro = db.scalar(select(BrokerInvestor).where(BrokerInvestor.email == target))
    intro.attribution_until = now() - timedelta(days=1)
    db.query(Investor).filter(Investor.email == target).delete()
    db.commit()
    assert client.post("/api/v1/courtier/investisseurs", headers=second["h"], json=body).status_code == 201


# ───────────── phases ─────────────

def test_phase_allocation_must_total_100(client, admin):
    body = {"nom": "Phase test", "annee_debut": 2030, "annee_fin": 2031, "montant_total": "1000000",
            "repartition": {"construction": 60, "rd": 30}}
    r = client.post("/api/v1/admin/investissement/phases", headers=admin["h"], json=body)
    assert r.status_code == 422 and r.json()["error"]["code"] == "INVALID_ALLOCATION"
    body["repartition"]["training"] = 10
    r = client.post("/api/v1/admin/investissement/phases", headers=admin["h"], json=body)
    assert r.status_code == 201 and len(r.json()["data"]["repartition"]) == 3


def test_forms_return_prepared_whatsapp_and_team_email_has_phone_and_country(client, db, outbox):
    """Comme les commandes et les rendez-vous : WhatsApp s'ouvre avec un message préparé ; l'email de l'équipe
    contient le téléphone / WhatsApp et le pays."""
    from urllib.parse import unquote

    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    r = client.post("/api/v1/investisseurs/manifestation", json=_investor_form(email, telephone="+25779111222"))
    assert r.status_code == 201, r.text
    msg = unquote(r.json()["data"]["whatsapp"]["url"])
    assert "INVESTISSEUR" in msg and "Grace Iradukunda" in msg and "+25779111222" in msg and "Burundi" in msg
    assert "Équité (Equity)" in msg and "1M - 5M €" in msg and "3-6 mois" in msg and "Facility GMP" in msg
    team = next(m for m in outbox if "Nouvelle manifestation" in m.subject)
    assert "+25779111222" in team.text and "Pays : Burundi" in team.text

    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    r = client.post("/api/v1/courtiers/candidature", json=_broker_form(email, whatsapp="+25779333444"))
    assert r.status_code == 201, r.text
    msg = unquote(r.json()["data"]["whatsapp"]["url"])
    assert "COURTIER" in msg and "Great Lakes Advisory" in msg and "+25779333444" in msg and "Agent de placement" in msg
    team = next(m for m in outbox if "Nouvelle candidature de courtier" in m.subject)
    assert "+25779333444" in team.text and "Pays : Burundi" in team.text

    bad = client.post("/api/v1/investisseurs/manifestation", json=_investor_form(email, nom_complet="<>{}"))
    assert bad.status_code == 422


def test_account_created_in_form_is_linked_but_cannot_claim_existing_file(client, db):
    """Investisseur / courtier : le compte est créé dans le formulaire et le nouveau dossier lui est lié tout de suite ;
    un dossier déjà existant (envoyé avant, sans compte) ne peut pas être récupéré par un compte non vérifié."""
    email = f"test-{uuid.uuid4().hex[:8]}@example.com"
    reg = client.post("/api/v1/auth/register", json={"email": email, "password": "MotDePasse123", "nom": "Iradukunda",
                                                     "prenom": "Grace", "pays_id": BURUNDI, "type_utilisateur": "investisseur"})
    assert reg.status_code == 201, reg.text
    h = {"Authorization": f"Bearer {reg.json()['data']['tokens']['access_token']}"}
    assert client.post("/api/v1/investisseurs/manifestation", headers=h, json=_investor_form(email)).status_code == 201
    inv = db.scalar(select(Investor).where(Investor.email == email))
    assert inv.user_id == db.scalar(select(User.id).where(User.email == email))

    other = f"test-{uuid.uuid4().hex[:8]}@example.com"
    client.post("/api/v1/courtiers/candidature", json=_broker_form(other))  # dossier envoyé sans compte
    reg2 = client.post("/api/v1/auth/register", json={"email": other, "password": "MotDePasse123", "nom": "X", "prenom": "Y",
                                                      "pays_id": BURUNDI, "type_utilisateur": "broker"})
    h2 = {"Authorization": f"Bearer {reg2.json()['data']['tokens']['access_token']}"}
    client.post("/api/v1/courtiers/candidature", headers=h2, json=_broker_form(other))
    assert db.scalar(select(Broker).where(Broker.email == other)).user_id is None
