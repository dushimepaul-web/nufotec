"""Chatbot RAG : modèles connectés, sources (articles, documents, tables choisies), garde-fous, conversations."""

import io
import json
import uuid

import pytest
from docx import Document
from sqlalchemy import select

from app.core.helpers import now
from app.core.security import hash_password
from app.models import AiProvider, ChatbotKb, RagChunk, RagSource, Role, User
from app.modules.chatbot import indexer, providers
from app.modules.chatbot.providers import ChatResult, ProviderError, Usage

PASSWORD = "MotDePasse123"
API = "/api/v1/admin/chatbot"


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


def _user(db, slug="admin", type_utilisateur="admin"):
    u = User(uuid=str(uuid.uuid4()), email=f"test-{uuid.uuid4().hex[:8]}@example.com", password=hash_password(PASSWORD),
             nom="Test", prenom="Admin", role_id=db.scalar(select(Role.id).where(Role.slug == slug)),
             type_utilisateur=type_utilisateur, is_active=True, created_at=now(), updated_at=now())
    db.add(u)
    db.commit()
    return u


@pytest.fixture
def admin_h(client, db):
    u = _user(db)
    r = client.post("/api/v1/auth/login", json={"login": u.email, "password": PASSWORD})
    return _auth(r.json()["data"]["tokens"]["access_token"])


class FakeLLM:
    """Remplace les appels réseau : enregistre les messages reçus et répond en citant le contexte."""

    def __init__(self):
        self.calls, self.fail_names = [], set()

    def stream(self, p, model, messages, temperature, max_tokens, usage):
        self.calls.append((p.name, model, messages))
        if p.name in self.fail_names:
            raise ProviderError(f"{p.name} : clé d'API refusée")
        usage.prompt_tokens, usage.completion_tokens = 120, 12
        for word in f"Réponse de {p.name} [1]".split(" "):
            yield word + " "

    def chat(self, p, model, messages, temperature, max_tokens):
        self.calls.append((p.name, model, messages))
        if p.name in self.fail_names:
            raise ProviderError(f"{p.name} : clé d'API refusée")
        return ChatResult(f"Réponse de test de {p.name}", Usage(100, 10))


@pytest.fixture
def llm(monkeypatch):
    fake = FakeLLM()
    monkeypatch.setattr(providers, "stream_chat", fake.stream)
    monkeypatch.setattr(providers, "chat", fake.chat)
    monkeypatch.setattr(providers, "list_models", lambda p: ["llama-3.3-70b", "openai/gpt-oss-20b"])
    return fake


@pytest.fixture
def configured(client, db, admin_h, llm):
    """Groq (principal) + OpenAI (secours), et quelques articles indexés."""
    groq = client.post(f"{API}/fournisseurs", headers=admin_h, json={
        "nom": "Groq test", "type": "groq", "cle_api": "gsk_TESTKEY1234567890abcd"}).json()["data"]
    openai = client.post(f"{API}/fournisseurs", headers=admin_h, json={
        "nom": "OpenAI test", "type": "openai", "cle_api": "sk-TESTKEY9876543210wxyz"}).json()["data"]
    client.put(f"{API}/reglages", headers=admin_h, json={
        "chat_provider_id": groq["id"], "chat_model": "llama-3.3-70b",
        "fallback_provider_id": openai["id"], "fallback_model": "gpt-4o-mini", "min_score": 0.1})
    db.query(ChatbotKb).update({"active": False})
    db.add_all([
        ChatbotKb(title="Horaires de livraison", content="Nufotec livre à Bujumbura du lundi au samedi, de 8 h à 18 h. "
                  "La livraison coûte 2000 BIF.", category="Livraison", active=True, created_at=now(), updated_at=now()),
        ChatbotKb(title="Prendre une consultation", content="Pour consulter un médecin en ligne, créez un compte, choisissez "
                  "un médecin et un créneau, puis payez par Lumicash ou EcoCash.", category="Santé", active=True,
                  created_at=now(), updated_at=now())])
    db.commit()
    indexer.index_source(db, indexer.ensure_kb_source(db))
    return {"groq": groq, "openai": openai}


def _start(client, **headers):
    r = client.post("/api/v1/chatbot/conversations", json={}, headers=headers)
    assert r.status_code == 201, r.text
    return r.json()["data"]["jeton"]


# ───────────── fournisseurs et clés ─────────────

def test_api_keys_encrypted_and_never_returned(client, db, admin_h, configured):
    providers_list = client.get(f"{API}/fournisseurs", headers=admin_h).json()["data"]
    groq = next(p for p in providers_list if p["nom"] == "Groq test")
    assert groq["cle"] == "gsk_••••abcd" and groq["url"] == "https://api.groq.com/openai/v1"
    assert "TESTKEY" not in json.dumps(providers_list)
    row = db.get(AiProvider, groq["id"])
    assert "TESTKEY" not in row.api_key_encrypted  # chiffrée en base
    from app.core.crypto import decrypt

    assert decrypt(row.api_key_encrypted) == "gsk_TESTKEY1234567890abcd"


def test_test_connection_lists_models(client, admin_h, configured):
    r = client.post(f"{API}/fournisseurs/{configured['groq']['id']}/tester", headers=admin_h).json()["data"]
    assert r["ok"] and "llama-3.3-70b" in r["modeles"]


def test_provider_validation(client, admin_h, llm):
    r = client.post(f"{API}/fournisseurs", headers=admin_h, json={"nom": "Sans clé", "type": "openai"})
    assert r.status_code == 422
    r = client.post(f"{API}/fournisseurs", headers=admin_h, json={"nom": "Ollama local", "type": "ollama"})
    assert r.status_code == 201 and r.json()["data"]["url"] == "http://localhost:11434/v1"
    r = client.post(f"{API}/fournisseurs", headers=admin_h,
                    json={"nom": "Pas sûr", "type": "openai_compatible", "url": "http://exemple.com/v1", "cle_api": "x" * 20})
    assert r.status_code == 422  # https obligatoire


def test_provider_in_use_cannot_be_deleted(client, admin_h, configured):
    r = client.delete(f"{API}/fournisseurs/{configured['groq']['id']}", headers=admin_h)
    assert r.status_code == 409 and r.json()["error"]["code"] == "PROVIDER_IN_USE"


def test_admin_only(client, db):
    u = _user(db, "user", "patient")
    h = _auth(client.post("/api/v1/auth/login", json={"login": u.email, "password": PASSWORD})
              .json()["data"]["tokens"]["access_token"])
    for path in ("/fournisseurs", "/tables", "/reglages", "/conversations"):
        assert client.get(f"{API}{path}", headers=h).status_code == 403


# ───────────── conversation ─────────────

def test_answer_uses_context_and_cites_sources(client, configured, llm):
    token = _start(client)
    r = client.post(f"/api/v1/chatbot/conversations/{token}/messages", json={"texte": "Quels sont les horaires de livraison ?"})
    assert r.status_code == 200, r.text
    reply = r.json()["data"]["reponse"]
    assert reply["texte"].startswith("Réponse de Groq test")
    assert reply["sources"][0]["titre"].startswith("Horaires de livraison")
    system = llm.calls[-1][2][0]["content"]
    assert "Bujumbura du lundi au samedi" in system and "jamais de diagnostic" in system


def test_streaming_events(client, configured):
    token = _start(client)
    with client.stream("POST", f"/api/v1/chatbot/conversations/{token}/messages?stream=true",
                       json={"texte": "Combien coûte la livraison ?"}) as r:
        assert r.headers["content-type"].startswith("text/event-stream")
        body = "".join(r.iter_text())
    events = [line.split(": ", 1)[1] for line in body.splitlines() if line.startswith("event: ")]
    assert events[0] == "sources" and "delta" in events and events[-1] == "done"
    resumed = client.get(f"/api/v1/chatbot/conversations/{token}").json()["data"]["messages"]
    assert resumed[-1]["auteur"] == "bot" and "Groq test" in resumed[-1]["texte"]


def test_fallback_model_when_primary_fails(client, configured, llm):
    llm.fail_names.add("Groq test")
    token = _start(client)
    reply = client.post(f"/api/v1/chatbot/conversations/{token}/messages",
                        json={"texte": "Livraison ?"}).json()["data"]["reponse"]
    assert reply["texte"].startswith("Réponse de OpenAI test")


def test_all_models_down(client, configured, llm):
    llm.fail_names.update({"Groq test", "OpenAI test"})
    token = _start(client)
    reply = client.post(f"/api/v1/chatbot/conversations/{token}/messages", json={"texte": "Livraison ?"}).json()["data"]["reponse"]
    assert "problème technique" in reply["texte"]


def test_not_configured(client, db, llm):
    from app.modules.chatbot import settings as chat_settings

    chat_settings.set_many(db, {"chat_provider_id": "", "fallback_provider_id": ""})  # annulé à la fin du test
    db.commit()
    token = _start(client)
    r = client.post(f"/api/v1/chatbot/conversations/{token}/messages", json={"texte": "Bonjour"})
    assert r.status_code == 503 and r.json()["error"]["code"] == "AI_NOT_CONFIGURED"


def test_emergency_answered_without_ai(client, configured, llm):
    token = _start(client)
    before = len(llm.calls)
    reply = client.post(f"/api/v1/chatbot/conversations/{token}/messages",
                        json={"texte": "Mon père a une douleur forte à la poitrine, c'est urgent"}).json()["data"]["reponse"]
    assert "services d'urgence" in reply["texte"] and len(llm.calls) == before


def test_health_question_suggests_consultation(client, configured):
    token = _start(client)
    reply = client.post(f"/api/v1/chatbot/conversations/{token}/messages",
                        json={"texte": "J'ai de la fièvre et de la toux depuis deux jours"}).json()["data"]["reponse"]
    assert reply["actions"] and reply["actions"][0]["type"] == "reserver_consultation"


def test_handoff_and_agent_reply(client, configured, admin_h, outbox, llm):
    token = _start(client)
    client.post(f"/api/v1/chatbot/conversations/{token}/messages", json={"texte": "Je veux parler à un conseiller"})
    state = client.get(f"/api/v1/chatbot/conversations/{token}").json()["data"]
    assert state["statut"] == "pending_human"
    assert any("conseiller" in m.subject for m in outbox)
    calls = len(llm.calls)
    client.post(f"/api/v1/chatbot/conversations/{token}/messages", json={"texte": "Vous êtes là ?"})
    assert len(llm.calls) == calls  # le bot se tait pendant le transfert

    queue = client.get(f"{API}/conversations", headers=admin_h, params={"statut": "pending_human"}).json()["data"]["items"]
    conv_id = queue[0]["id"]
    client.post(f"{API}/conversations/{conv_id}/prendre", headers=admin_h)
    client.post(f"{API}/conversations/{conv_id}/repondre", headers=admin_h, json={"texte": "Bonjour, je suis là !"})
    messages = client.get(f"/api/v1/chatbot/conversations/{token}").json()["data"]["messages"]
    assert messages[-1]["auteur"] == "agent" and messages[-1]["texte"] == "Bonjour, je suis là !"


def test_feedback_and_stats(client, configured, admin_h):
    token = _start(client)
    reply = client.post(f"/api/v1/chatbot/conversations/{token}/messages", json={"texte": "Livraison ?"}).json()["data"]["reponse"]
    assert client.post(f"/api/v1/chatbot/conversations/{token}/messages/{reply['id']}/avis", json={"note": 1}).status_code == 200
    stats = client.get(f"{API}/statistiques", headers=admin_h).json()["data"]
    assert stats["avis"]["utiles"] >= 1
    assert any(u["fournisseur"] == "Groq test" and u["jetons_entree"] >= 120 for u in stats["consommation"])


def test_conversation_token_is_secret(client, configured):
    assert client.get("/api/v1/chatbot/conversations/devine-moi").status_code == 404


def test_message_rate_limit(client, configured):
    token = _start(client)
    codes = [client.post(f"/api/v1/chatbot/conversations/{token}/messages", json={"texte": f"Livraison {i}"}).status_code
             for i in range(11)]
    assert codes[-1] == 429


# ───────────── sources : documents ─────────────

def test_upload_document_is_indexed_and_searchable(client, db, admin_h, configured):
    doc = Document()
    doc.add_paragraph("Le miel de Ngozi est récolté en altitude. Il est vendu 15000 BIF le pot.")
    buf = io.BytesIO()
    doc.save(buf)
    r = client.post(f"{API}/documents", headers=admin_h,
                    files={"fichier": ("catalogue miel.docx", buf.getvalue(), "application/octet-stream")})
    assert r.status_code == 201, r.text
    source = db.get(RagSource, r.json()["data"]["id"])
    assert source.status == "ready" and source.chunks_count >= 1  # indexé en tâche de fond
    test = client.post(f"{API}/tester", headers=admin_h, json={"question": "prix du miel de Ngozi"}).json()["data"]
    assert test["passages"][0]["titre"].startswith("catalogue miel.docx")


def test_fake_document_refused(client, admin_h):
    r = client.post(f"{API}/documents", headers=admin_h, files={"fichier": ("faux.pdf", b"<html>pas un pdf</html>", "application/pdf")})
    assert r.status_code == 422
    r = client.post(f"{API}/documents", headers=admin_h, files={"fichier": ("script.exe", b"MZ....", "application/octet-stream")})
    assert r.status_code == 422


# ───────────── sources : tables choisies par l'admin ─────────────

def test_table_catalog_marks_protected_parts(client, admin_h):
    tables = {t["table"]: t for t in client.get(f"{API}/tables", headers=admin_h).json()["data"]}
    assert tables["password_resets"]["bloquee"] and tables["consultations"]["bloquee"]
    users_cols = {c["name"]: c for c in tables["users"]["colonnes"]}
    assert users_cols["password"]["blocked"] and users_cols["email"]["sensitive"] and not users_cols["nom"]["blocked"]


def test_blocked_tables_and_columns_refused(client, admin_h):
    r = client.post(f"{API}/tables", headers=admin_h, json={"table": "consultations", "colonnes": ["symptomes"]})
    assert r.status_code == 403 and r.json()["error"]["code"] == "TABLE_BLOCKED"
    r = client.post(f"{API}/tables", headers=admin_h, json={"table": "users", "colonnes": ["nom", "password"]})
    assert r.status_code == 403 and r.json()["error"]["code"] == "COLUMN_BLOCKED"
    r = client.post(f"{API}/tables", headers=admin_h,
                    json={"table": "faq", "colonnes": ["question"], "modele_texte": "{question} {secret_inconnu}"})
    assert r.status_code == 422


def test_table_selection_preview_index_and_answer(client, db, admin_h, configured, llm):
    body = {"table": "faq", "colonnes": ["question", "reponse"], "modele_texte": "Question : {question}. Réponse : {reponse}"}
    cols = {c["name"] for c in next(t for t in client.get(f"{API}/tables", headers=admin_h).json()["data"]
                                    if t["table"] == "faq")["colonnes"]}
    if not {"question", "reponse"} <= cols:
        pytest.skip(f"colonnes de faq : {cols}")
    preview = client.post(f"{API}/tables/apercu", headers=admin_h, json=body).json()["data"]
    assert preview["exemples"] and preview["exemples"][0].startswith("Question :")
    r = client.post(f"{API}/tables", headers=admin_h, json=body)
    assert r.status_code == 201, r.text
    source = db.get(RagSource, r.json()["data"]["source"]["id"])
    assert source.status == "ready" and source.chunks_count == preview["lignes"]
    chunk = db.scalar(select(RagChunk).where(RagChunk.source_id == source.id).limit(1))
    assert chunk.content.startswith("Question :")


def test_parametres_secrets_never_indexed(client, db, admin_h, configured):
    r = client.post(f"{API}/tables", headers=admin_h, json={"table": "parametres", "colonnes": ["cle", "valeur"]})
    assert r.status_code == 201, r.text
    source = db.get(RagSource, r.json()["data"]["source"]["id"])
    contents = " ".join(c.content for c in db.scalars(select(RagChunk).where(RagChunk.source_id == source.id)))
    for secret_key in ("smtp_pass", "chatbot_api_key", "chatbot_groq_api_key", "smtp_password"):
        assert f"cle : {secret_key} ;" not in contents
    assert "chatbot_site_name" in contents  # les réglages anodins restent lisibles


def test_sql_injection_in_filters_is_harmless(client, admin_h, configured):
    body = {"table": "faq", "colonnes": ["id_faq"], "filtres": [{"column": "id_faq", "op": "=", "value": "1 OR 1=1; DROP TABLE faq"}]}
    r = client.post(f"{API}/tables/apercu", headers=admin_h, json=body)
    assert r.status_code == 200 and r.json()["data"]["lignes"] <= 1
    r = client.post(f"{API}/tables/apercu", headers=admin_h, json={"table": "faq`; DROP TABLE faq; --", "colonnes": ["id_faq"]})
    assert r.status_code in (404, 422)


# ───────────── articles et espace de test ─────────────

def test_kb_crud_reindexes(client, db, admin_h, configured):
    r = client.post(f"{API}/articles", headers=admin_h, json={"titre": "Retour produit", "contenu":
                    "Vous pouvez retourner un produit sous 7 jours avec le reçu.", "categorie": "Support"})
    assert r.status_code == 201
    test = client.post(f"{API}/tester", headers=admin_h, json={"question": "retourner un produit reçu"}).json()["data"]
    assert test["passages"][0]["titre"].startswith("Retour produit")
    assert test["reponse"].startswith("Réponse de test de Groq test")


def test_settings_validation(client, admin_h, configured):
    assert client.put(f"{API}/reglages", headers=admin_h, json={"temperature": 3}).status_code == 422
    r = client.put(f"{API}/reglages", headers=admin_h, json={"assistant_name": "Nufo", "greeting": "Salut !"})
    assert r.json()["data"]["assistant_name"] == "Nufo"
    start = client.post("/api/v1/chatbot/conversations", json={}).json()["data"]
    assert start["assistant"] == "Nufo" and start["messages"][0]["texte"] == "Salut !"


def test_old_configurations_view_blocked(client, admin_h, configured):
    """La vue « configurations » (compatibilité PHP) ne doit jamais contourner le filtre des secrets."""
    r = client.post(f"{API}/tables", headers=admin_h, json={"table": "configurations", "colonnes": ["cle", "valeur"]})
    assert r.status_code == 403 and r.json()["error"]["code"] == "TABLE_BLOCKED"
