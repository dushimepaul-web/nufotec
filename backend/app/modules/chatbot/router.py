"""Routes du chatbot : widget public et administration (modèles, sources, tables, test, conversations)."""

import json
import secrets
from datetime import timedelta
from typing import Literal

from fastapi import APIRouter, BackgroundTasks, Depends, File, Query, UploadFile, status
from fastapi.responses import StreamingResponse
from fastapi.security import HTTPAuthorizationCredentials
from pydantic import BaseModel, EmailStr, Field
from sqlalchemy import func, select
from sqlalchemy.orm import Session

from app.core import audit
from app.core.client import ClientInfo, get_client
from app.core.crypto import encrypt, hint
from app.core.database import background_session, get_db
from app.core.deps import bearer
from app.core.files import delete_private, private_root
from app.core.helpers import now
from app.core.i18n import lang_var
from app.core.pagination import Page, PageParams, paginate
from app.core.permissions import require_permission
from app.core.responses import ApiError, Envelope, ok
from app.core.types import DateTime
from app.models import (
    AiProvider,
    AiUsage,
    ChatbotConversation,
    ChatbotFeedback,
    ChatbotHandoff,
    ChatbotKb,
    ChatbotMessage,
    RagDocument,
    RagSource,
    RagTableRule,
    User,
)
from app.models.chatbot import PROVIDER_KINDS
from app.modules.chatbot import catalog, engine, indexer, providers, settings
from app.modules.chatbot.extract import detect

DB = Depends(get_db)
CLIENT = Depends(get_client)
MANAGE = Depends(require_permission("chatbot.manage"))

public = APIRouter(prefix="/chatbot", tags=["Chatbot (widget)"])
admin = APIRouter(prefix="/admin/chatbot", tags=["Admin · Chatbot"])


# ═════════════════════════ Widget public ═════════════════════════

class StartIn(BaseModel):
    nom: str | None = Field(default=None, max_length=150)
    email: EmailStr | None = None


class MessageIn(BaseModel):
    texte: str = Field(min_length=1, max_length=2000)


class FeedbackIn(BaseModel):
    note: Literal[1, -1] = Field(description="1 = utile, -1 = pas utile")
    commentaire: str | None = Field(default=None, max_length=500)


class HumanIn(BaseModel):
    motif: str | None = Field(default=None, max_length=255)


class ChatMessageOut(BaseModel):
    id: int
    auteur: str
    texte: str
    le: DateTime
    sources: list[dict] = []
    actions: list[dict] = []


class StartOut(BaseModel):
    jeton: str = Field(description="À garder côté app : identifie la conversation (envoyer dans l'URL)")
    statut: str
    assistant: str
    messages: list[ChatMessageOut]


def _optional_user(db: Session, creds: HTTPAuthorizationCredentials | None) -> User | None:
    """Le widget marche sans compte ; si un jeton valide est fourni, la conversation est liée au compte."""
    if creds is None:
        return None
    from app.core.deps import get_current_session, get_current_user

    try:
        session = get_current_session(creds, db)
        return get_current_user(session, db)
    except ApiError:
        return None


@public.post("/conversations", status_code=status.HTTP_201_CREATED, response_model=Envelope[StartOut],
             summary="Ouvrir une conversation (avec ou sans compte)")
def start(body: StartIn, db: Session = DB, client: ClientInfo = CLIENT,
          creds: HTTPAuthorizationCredentials | None = Depends(bearer)):
    user = _optional_user(db, creds)
    token, conv, _ = engine.start(db, user, body.nom, body.email, lang_var.get(), client.ip)
    return ok({"jeton": token, "statut": conv.status, "assistant": settings.get(db, "assistant_name"),
               "messages": [engine.message_out(m) for m in engine.history(db, conv)]})


@public.get("/conversations/{jeton}", response_model=Envelope[StartOut], summary="Reprendre une conversation")
def resume(jeton: str, db: Session = DB):
    conv = engine.by_token(db, jeton)
    return ok({"jeton": jeton, "statut": conv.status, "assistant": settings.get(db, "assistant_name"),
               "messages": [engine.message_out(m) for m in engine.history(db, conv)]})


def _sse(event: str, data) -> str:
    return f"event: {event}\ndata: {json.dumps(data, ensure_ascii=False, default=str)}\n\n"


@public.post("/conversations/{jeton}/messages",
             summary="Envoyer un message. ?stream=true : réponse en flux (text/event-stream) mot par mot",
             responses={200: {"content": {"text/event-stream": {}}}})
def send(jeton: str, body: MessageIn, background: BackgroundTasks, stream: bool = False, db: Session = DB):
    conv = engine.by_token(db, jeton)
    decision = engine.prepare(db, conv, body.texte, background)
    conv_id = conv.id

    if "done" in decision:
        final = decision["done"]
        payload = engine.message_out(final) if final else None
        if not stream:
            return ok({"reponse": payload, "statut": conv.status})

        def finished():
            if payload:
                yield _sse("done", payload)
            yield _sse("statut", {"statut": conv.status})

        return StreamingResponse(finished(), media_type="text/event-stream")

    ctx = decision["generate"]
    if not stream:
        final = None
        for kind, value in engine.generate(db, conv_id, ctx):
            if kind == "done":
                final = value
        return ok({"reponse": engine.message_out(final), "statut": "bot"})

    def events():
        own = background_session()  # session propre : la réponse continue après la fin de la requête
        try:
            yield _sse("sources", ctx["sources"])
            if ctx["actions"]:
                yield _sse("actions", ctx["actions"])
            for kind, value in engine.generate(own, conv_id, ctx):
                yield _sse("delta", {"texte": value}) if kind == "delta" else _sse("done", engine.message_out(value))
        finally:
            own.close()

    return StreamingResponse(events(), media_type="text/event-stream",
                             headers={"Cache-Control": "no-cache", "X-Accel-Buffering": "no"})


@public.post("/conversations/{jeton}/conseiller", response_model=Envelope[ChatMessageOut],
             summary="Demander à parler à un conseiller")
def ask_human(jeton: str, body: HumanIn, background: BackgroundTasks, db: Session = DB):
    conv = engine.by_token(db, jeton)
    m = engine.request_human(db, conv, body.motif, background)
    db.commit()
    return ok(engine.message_out(m))


@public.post("/conversations/{jeton}/messages/{message_id}/avis", response_model=Envelope[None],
             summary="Réponse utile 👍 ou pas 👎")
def rate(jeton: str, message_id: int, body: FeedbackIn, db: Session = DB):
    engine.feedback(db, engine.by_token(db, jeton), message_id, body.note, body.commentaire)
    return ok(message="Merci pour votre avis")


# ═════════════════════════ Admin : fournisseurs et modèles ═════════════════════════

class ProviderIn(BaseModel):
    nom: str = Field(min_length=2, max_length=100)
    type: Literal[PROVIDER_KINDS]  # type: ignore[valid-type]
    url: str | None = Field(default=None, max_length=255, description="Par défaut : l'adresse officielle du service")
    cle_api: str | None = Field(default=None, max_length=500)
    actif: bool = True


class ProviderUpdateIn(BaseModel):
    nom: str | None = Field(default=None, min_length=2, max_length=100)
    url: str | None = Field(default=None, max_length=255)
    cle_api: str | None = Field(default=None, max_length=500, description="Laisser vide pour garder la clé actuelle")
    actif: bool | None = None


class ProviderOut(BaseModel):
    id: int
    nom: str
    type: str
    url: str
    cle: str | None = Field(description="Indice seulement, jamais la clé entière")
    actif: bool
    dernier_test: DateTime | None
    dernier_test_ok: bool | None
    dernier_test_message: str | None


def _provider_out(p: AiProvider) -> dict:
    return {"id": p.id, "nom": p.name, "type": p.kind, "url": p.base_url, "cle": p.api_key_hint, "actif": p.is_active,
            "dernier_test": p.last_test_at, "dernier_test_ok": p.last_test_ok,
            "dernier_test_message": p.last_test_message}


def _get_provider(db: Session, provider_id: int) -> AiProvider:
    p = db.get(AiProvider, provider_id)
    if p is None:
        raise ApiError(404, "NOT_FOUND", "Fournisseur introuvable")
    return p


def _check_url(url: str) -> str:
    url = url.strip().rstrip("/")
    if not url.startswith(("https://", "http://localhost", "http://127.0.0.1")):
        raise ApiError(422, "VALIDATION_ERROR", "L'adresse doit être en https (ou locale pour Ollama)",
                       [{"field": "url", "message": "https obligatoire"}])
    return url


@admin.get("/fournisseurs", response_model=Envelope[list[ProviderOut]], summary="Fournisseurs d'IA connectés")
def list_providers(db: Session = DB, _: User = MANAGE):
    return ok([_provider_out(p) for p in db.scalars(select(AiProvider).order_by(AiProvider.id))])


@admin.post("/fournisseurs", status_code=status.HTTP_201_CREATED, response_model=Envelope[ProviderOut],
            summary="Connecter un fournisseur (Groq, OpenAI, Anthropic, Gemini, Mistral, Ollama…)")
def add_provider(body: ProviderIn, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    url = _check_url(body.url or providers.DEFAULT_URLS.get(body.type, ""))
    if not body.url and body.type == "openai_compatible":
        raise ApiError(422, "VALIDATION_ERROR", "Adresse obligatoire pour un service compatible OpenAI")
    if not body.cle_api and body.type not in providers.KEY_OPTIONAL:
        raise ApiError(422, "VALIDATION_ERROR", "Clé d'API obligatoire pour ce fournisseur",
                       [{"field": "cle_api", "message": "obligatoire"}])
    if db.scalar(select(AiProvider.id).where(AiProvider.name == body.nom.strip())):
        raise ApiError(409, "CONFLICT", "Un fournisseur porte déjà ce nom")
    key = (body.cle_api or "").strip()
    p = AiProvider(name=body.nom.strip(), kind=body.type, base_url=url, is_active=body.actif,
                   api_key_encrypted=encrypt(key) if key else None, api_key_hint=hint(key) if key else None,
                   created_at=now(), updated_at=now())
    db.add(p)
    db.flush()
    audit.activity(db, actor.id, "ai_provider_added", client, f"Fournisseur {p.name} ({p.kind})", module="chatbot",
                   item_id=p.id, item_name=p.name)
    db.commit()
    return ok(_provider_out(p), "Fournisseur connecté")


@admin.patch("/fournisseurs/{provider_id}", response_model=Envelope[ProviderOut], summary="Modifier un fournisseur")
def update_provider(provider_id: int, body: ProviderUpdateIn, db: Session = DB, client: ClientInfo = CLIENT,
                    actor: User = MANAGE):
    p = _get_provider(db, provider_id)
    if body.nom:
        p.name = body.nom.strip()
    if body.url:
        p.base_url = _check_url(body.url)
    if body.cle_api:
        p.api_key_encrypted, p.api_key_hint = encrypt(body.cle_api.strip()), hint(body.cle_api.strip())
        p.last_test_ok = None
    if body.actif is not None:
        p.is_active = body.actif
    p.updated_at = now()
    audit.activity(db, actor.id, "ai_provider_updated", client, f"Fournisseur {p.name}", module="chatbot",
                   item_id=p.id, item_name=p.name, new_data={"cle_changee": bool(body.cle_api)})
    db.commit()
    return ok(_provider_out(p), "Fournisseur mis à jour")


@admin.delete("/fournisseurs/{provider_id}", response_model=Envelope[None], summary="Supprimer un fournisseur")
def delete_provider(provider_id: int, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    p = _get_provider(db, provider_id)
    values = settings.get_all(db)
    if str(p.id) in (values["chat_provider_id"], values["fallback_provider_id"]):
        raise ApiError(409, "PROVIDER_IN_USE", "Ce fournisseur est utilisé par le chatbot : choisissez-en un autre d'abord")
    audit.activity(db, actor.id, "ai_provider_deleted", client, f"Fournisseur {p.name}", module="chatbot",
                   item_id=p.id, item_name=p.name)
    db.delete(p)
    db.commit()
    return ok(message="Fournisseur supprimé")


@admin.post("/fournisseurs/{provider_id}/tester", response_model=Envelope[dict],
            summary="Tester la connexion et lister les modèles disponibles")
def test_provider(provider_id: int, db: Session = DB, _: User = MANAGE):
    p = _get_provider(db, provider_id)
    try:
        models = providers.list_models(p)
        p.last_test_ok, p.last_test_message = True, f"{len(models)} modèle(s) disponible(s)"
    except providers.ProviderError as exc:
        models = []
        p.last_test_ok, p.last_test_message = False, str(exc)[:255]
    p.last_test_at = now()
    db.commit()
    return ok({"ok": p.last_test_ok, "message": p.last_test_message, "modeles": models})


# ═════════════════════════ Admin : réglages ═════════════════════════

class SettingsIn(BaseModel):
    assistant_name: str | None = Field(default=None, max_length=80)
    greeting: str | None = Field(default=None, max_length=500)
    system_prompt: str | None = Field(default=None, max_length=6000)
    chat_provider_id: int | None = None
    chat_model: str | None = Field(default=None, max_length=120)
    fallback_provider_id: int | None = None
    fallback_model: str | None = Field(default=None, max_length=120)
    temperature: float | None = Field(default=None, ge=0, le=1.5)
    max_tokens: int | None = Field(default=None, ge=50, le=4000)
    top_k: int | None = Field(default=None, ge=1, le=20)
    min_score: float | None = Field(default=None, ge=0, le=1)
    history_messages: int | None = Field(default=None, ge=0, le=30)
    handoff_email: EmailStr | None = None
    emergency_message: str | None = Field(default=None, max_length=1000)


@admin.get("/reglages", response_model=Envelope[dict], summary="Réglages du chatbot")
def get_settings_(db: Session = DB, _: User = MANAGE):
    values = settings.get_all(db)
    values.pop("last_nightly_sync", None)
    return ok(values)


@admin.put("/reglages", response_model=Envelope[dict], summary="Modifier les réglages (modèle, prompt, ton…)")
def put_settings(body: SettingsIn, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    changes = body.model_dump(exclude_unset=True)
    for key in ("chat_provider_id", "fallback_provider_id"):
        if changes.get(key):
            _get_provider(db, changes[key])
    settings.set_many(db, {k: "" if v is None else str(v) for k, v in changes.items()})
    audit.activity(db, actor.id, "chatbot_settings", client, "Réglages du chatbot modifiés", module="chatbot",
                   new_data={k: v for k, v in changes.items() if k != "system_prompt"})
    db.commit()
    values = settings.get_all(db)
    values.pop("last_nightly_sync", None)
    return ok(values, "Réglages enregistrés")


# ═════════════════════════ Admin : sources de connaissance ═════════════════════════

class SourceOut(BaseModel):
    id: int
    type: str
    nom: str
    statut: str
    erreur: str | None
    passages: int
    actif: bool
    indexe_le: DateTime | None


def _source_out(s: RagSource) -> dict:
    return {"id": s.id, "type": s.kind, "nom": s.name, "statut": s.status, "erreur": s.error,
            "passages": s.chunks_count, "actif": s.is_active, "indexe_le": s.last_indexed_at}


def _get_source(db: Session, source_id: int) -> RagSource:
    s = db.get(RagSource, source_id)
    if s is None:
        raise ApiError(404, "NOT_FOUND", "Source introuvable")
    return s


@admin.get("/sources", response_model=Envelope[list[SourceOut]], summary="Toutes les sources et leur état")
def list_sources(db: Session = DB, _: User = MANAGE):
    indexer.ensure_kb_source(db)
    db.commit()
    return ok([_source_out(s) for s in db.scalars(select(RagSource).order_by(RagSource.id))])


@admin.post("/sources/{source_id}/indexer", response_model=Envelope[SourceOut], summary="(Ré)indexer une source")
def reindex(source_id: int, background: BackgroundTasks, db: Session = DB, _: User = MANAGE):
    s = _get_source(db, source_id)
    s.status = "pending"
    db.commit()
    background.add_task(indexer.index_source_by_id, s.id)
    return ok(_source_out(s), "Indexation lancée")


@admin.patch("/sources/{source_id}", response_model=Envelope[SourceOut], summary="Activer / désactiver une source")
def toggle_source(source_id: int, actif: bool, db: Session = DB, _: User = MANAGE):
    s = _get_source(db, source_id)
    s.is_active = actif
    db.commit()
    return ok(_source_out(s))


# ── Articles (base de connaissances) ──

class KbIn(BaseModel):
    titre: str = Field(min_length=2, max_length=255)
    contenu: str = Field(min_length=5, max_length=20000)
    categorie: str | None = Field(default=None, max_length=120)
    actif: bool = True


class KbOut(BaseModel):
    id: int
    titre: str
    contenu: str
    categorie: str | None
    actif: bool
    modifie_le: DateTime | None


def _kb_out(a: ChatbotKb) -> dict:
    return {"id": a.id, "titre": a.title, "contenu": a.content, "categorie": a.category, "actif": a.active,
            "modifie_le": a.updated_at}


def _reindex_kb(db: Session, background: BackgroundTasks) -> None:
    source = indexer.ensure_kb_source(db)
    source.status = "pending"
    db.commit()
    background.add_task(indexer.index_source_by_id, source.id)


@admin.get("/articles", response_model=Envelope[Page[KbOut]], summary="Articles de la base de connaissances")
def list_kb(q: str | None = Query(None, max_length=100), categorie: str | None = None, page: PageParams = Depends(),
            db: Session = DB, _: User = MANAGE):
    from app.core.helpers import like_pattern

    query = select(ChatbotKb).order_by(ChatbotKb.category, ChatbotKb.title)
    if q:
        query = query.where(ChatbotKb.title.like(like_pattern(q)) | ChatbotKb.content.like(like_pattern(q)))
    if categorie:
        query = query.where(ChatbotKb.category == categorie)
    rows, meta = paginate(db, query, page)
    return ok({"items": [_kb_out(a) for a in rows], "meta": meta})


@admin.post("/articles", status_code=status.HTTP_201_CREATED, response_model=Envelope[KbOut], summary="Nouvel article")
def add_kb(body: KbIn, background: BackgroundTasks, db: Session = DB, _: User = MANAGE):
    a = ChatbotKb(title=body.titre.strip(), content=body.contenu.strip(), category=body.categorie, active=body.actif,
                  created_at=now(), updated_at=now())
    db.add(a)
    db.commit()
    _reindex_kb(db, background)
    return ok(_kb_out(a), "Article ajouté")


@admin.put("/articles/{article_id}", response_model=Envelope[KbOut], summary="Modifier un article")
def edit_kb(article_id: int, body: KbIn, background: BackgroundTasks, db: Session = DB, _: User = MANAGE):
    a = db.get(ChatbotKb, article_id)
    if a is None:
        raise ApiError(404, "NOT_FOUND", "Article introuvable")
    a.title, a.content, a.category, a.active, a.updated_at = (body.titre.strip(), body.contenu.strip(), body.categorie,
                                                                body.actif, now())
    db.commit()
    _reindex_kb(db, background)
    return ok(_kb_out(a), "Article mis à jour")


@admin.delete("/articles/{article_id}", response_model=Envelope[None], summary="Supprimer un article")
def delete_kb(article_id: int, background: BackgroundTasks, db: Session = DB, _: User = MANAGE):
    a = db.get(ChatbotKb, article_id)
    if a is None:
        raise ApiError(404, "NOT_FOUND", "Article introuvable")
    db.delete(a)
    db.commit()
    _reindex_kb(db, background)
    return ok(message="Article supprimé")


# ── Documents envoyés ──

@admin.post("/documents", status_code=status.HTTP_201_CREATED, response_model=Envelope[SourceOut],
            summary="Envoyer un document (PDF, DOCX, XLSX, CSV, TXT, MD — 20 Mo max)")
async def upload_document(background: BackgroundTasks, fichier: UploadFile = File(...), db: Session = DB,
                          client: ClientInfo = CLIENT, actor: User = MANAGE):
    from app.modules.chatbot.extract import MAX_BYTES, TYPES

    raw = await fichier.read(MAX_BYTES + 1)
    name = (fichier.filename or "document").replace("\\", "/").split("/")[-1][:255]
    ext = detect(name, raw)
    stored = f"{secrets.token_hex(16)}.{ext}"
    folder = private_root() / indexer.PRIVATE_FOLDER
    folder.mkdir(parents=True, exist_ok=True)
    (folder / stored).write_bytes(raw)
    source = RagSource(kind="document", name=name, status="pending", created_at=now())
    db.add(source)
    db.flush()
    db.add(RagDocument(source_id=source.id, stored_name=stored, original_name=name, mime=TYPES[ext],
                       size_bytes=len(raw), created_at=now()))
    audit.activity(db, actor.id, "chatbot_document", client, f"Document {name}", module="chatbot", item_id=source.id,
                   item_name=name)
    db.commit()
    background.add_task(indexer.index_source_by_id, source.id)
    return ok(_source_out(source), "Document reçu : indexation en cours")


@admin.delete("/sources/{source_id}", response_model=Envelope[None], summary="Supprimer un document ou une table")
def delete_source(source_id: int, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    s = _get_source(db, source_id)
    if s.kind == "kb":
        raise ApiError(409, "CONFLICT", "La base de connaissances ne se supprime pas (désactivez-la ou videz ses articles)")
    files = [d.stored_name for d in db.scalars(select(RagDocument).where(RagDocument.source_id == s.id))]
    audit.activity(db, actor.id, "chatbot_source_deleted", client, s.name, module="chatbot", item_id=s.id, item_name=s.name)
    db.delete(s)
    db.commit()
    for f in files:
        delete_private(indexer.PRIVATE_FOLDER, f)
    return ok(message="Source supprimée")


# ── Tables de la base, choisies par l'admin ──

class FilterIn(BaseModel):
    column: str = Field(max_length=64)
    op: Literal["=", "!=", "<", ">", "<=", ">=", "like", "is_null", "not_null", "in"]
    value: str | int | float | bool | list | None = None


class TableRuleIn(BaseModel):
    table: str = Field(max_length=64)
    colonnes: list[str] = Field(min_length=1, max_length=50, description="Colonnes que l'IA pourra lire")
    filtres: list[FilterIn] = Field(default_factory=list, max_length=10)
    modele_texte: str | None = Field(default=None, max_length=2000,
                                     description="Ex. « Produit {nom} : {description}. Prix : {prix} BIF »")
    colonne_titre: str | None = Field(default=None, max_length=64)
    synchronisation: Literal["manual", "nightly"] = "nightly"
    max_lignes: int = Field(default=5000, ge=1, le=50000)


class TableRuleOut(TableRuleIn):
    id: int
    source: SourceOut


def _rule_out(db: Session, r: RagTableRule) -> dict:
    return {"id": r.id, "table": r.table_name, "colonnes": catalog.loads(r.columns_json, []),
            "filtres": catalog.loads(r.filters_json, []), "modele_texte": r.template, "colonne_titre": r.title_column,
            "synchronisation": r.schedule, "max_lignes": r.max_rows, "source": _source_out(db.get(RagSource, r.source_id))}


@admin.get("/tables", response_model=Envelope[list[dict]],
           summary="Toutes les tables de la base, leurs colonnes et ce qui est protégé")
def tables(db: Session = DB, _: User = MANAGE):
    rules = {r.table_name: r for r in db.scalars(select(RagTableRule))}
    out = []
    for t in catalog.list_tables(db):
        rule = rules.get(t["table"])
        t["selectionnee"] = rule is not None
        t["colonnes_choisies"] = catalog.loads(rule.columns_json, []) if rule else []
        out.append(t)
    return ok(out)


@admin.post("/tables/apercu", response_model=Envelope[dict],
            summary="Aperçu : voir le texte que l'IA lira (5 lignes) avant de valider")
def preview(body: TableRuleIn, db: Session = DB, _: User = MANAGE):
    filters = [f.model_dump() for f in body.filtres]
    real = catalog.validate_rule(db, body.table, body.colonnes, filters, body.modele_texte, body.colonne_titre)
    sql, params = catalog.build_select(body.table, body.colonnes, filters, catalog.primary_key(real), 5)
    rows = [catalog.render_row(dict(r), body.colonnes, body.modele_texte) for r in db.execute(sql, params).mappings()]
    count_sql, count_params = catalog.build_select(body.table, body.colonnes, filters, None, body.max_lignes)
    total = len(db.execute(count_sql, count_params).all())
    return ok({"exemples": rows, "lignes": total})


def _save_rule(db: Session, body: TableRuleIn, rule: RagTableRule | None) -> RagTableRule:
    filters = [f.model_dump() for f in body.filtres]
    catalog.validate_rule(db, body.table, body.colonnes, filters, body.modele_texte, body.colonne_titre)
    if rule is None:
        if db.scalar(select(RagTableRule.id).where(RagTableRule.table_name == body.table)):
            raise ApiError(409, "CONFLICT", "Cette table est déjà sélectionnée : modifiez sa règle")
        source = RagSource(kind="table", name=f"Table {body.table}", status="pending", created_at=now())
        db.add(source)
        db.flush()
        rule = RagTableRule(source_id=source.id, table_name=body.table)
        db.add(rule)
    rule.columns_json = json.dumps(body.colonnes)
    rule.filters_json = json.dumps(filters, default=str)
    rule.template, rule.title_column = body.modele_texte, body.colonne_titre
    rule.schedule, rule.max_rows = body.synchronisation, body.max_lignes
    return rule


@admin.post("/tables", status_code=status.HTTP_201_CREATED, response_model=Envelope[TableRuleOut],
            summary="Donner une table à l'IA (colonnes choisies) et l'indexer")
def add_table(body: TableRuleIn, background: BackgroundTasks, db: Session = DB, client: ClientInfo = CLIENT,
              actor: User = MANAGE):
    rule = _save_rule(db, body, None)
    audit.activity(db, actor.id, "chatbot_table_added", client, f"Table {body.table} : {', '.join(body.colonnes)}",
                   module="chatbot", item_name=body.table)
    db.commit()
    background.add_task(indexer.index_source_by_id, rule.source_id)
    return ok(_rule_out(db, rule), "Table ajoutée : indexation en cours")


@admin.put("/tables/{rule_id}", response_model=Envelope[TableRuleOut], summary="Modifier les colonnes / filtres")
def edit_table(rule_id: int, body: TableRuleIn, background: BackgroundTasks, db: Session = DB,
               client: ClientInfo = CLIENT, actor: User = MANAGE):
    rule = db.get(RagTableRule, rule_id)
    if rule is None:
        raise ApiError(404, "NOT_FOUND", "Règle introuvable")
    if body.table != rule.table_name:
        raise ApiError(422, "VALIDATION_ERROR", "On ne change pas de table : créez une nouvelle règle")
    _save_rule(db, body, rule)
    audit.activity(db, actor.id, "chatbot_table_updated", client, f"Table {body.table} : {', '.join(body.colonnes)}",
                   module="chatbot", item_name=body.table)
    db.commit()
    background.add_task(indexer.index_source_by_id, rule.source_id)
    return ok(_rule_out(db, rule), "Règle mise à jour : réindexation en cours")


@admin.get("/tables/regles", response_model=Envelope[list[TableRuleOut]], summary="Tables données à l'IA")
def list_rules(db: Session = DB, _: User = MANAGE):
    return ok([_rule_out(db, r) for r in db.scalars(select(RagTableRule).order_by(RagTableRule.table_name))])


# ═════════════════════════ Admin : espace de test ═════════════════════════

class TestIn(BaseModel):
    question: str = Field(min_length=2, max_length=1000)


@admin.post("/tester", response_model=Envelope[dict],
            summary="Poser une question : passages retrouvés (avec score) et réponse du modèle")
def playground(body: TestIn, db: Session = DB, _: User = MANAGE):
    return ok(engine.answer_once(db, body.question))


# ═════════════════════════ Admin : conversations ═════════════════════════

class ConversationOut(BaseModel):
    id: int
    statut: str
    visiteur: str | None
    email: str | None
    langue: str | None
    messages: int
    dernier_message: str | None
    mis_a_jour: DateTime


class ReplyIn(BaseModel):
    texte: str = Field(min_length=1, max_length=4000)


def _conv_out(db: Session, c: ChatbotConversation) -> dict:
    last = db.scalar(select(ChatbotMessage.message).where(ChatbotMessage.conversation_id == c.id)
                     .order_by(ChatbotMessage.id.desc()).limit(1))
    count = db.scalar(select(func.count()).select_from(ChatbotMessage).where(ChatbotMessage.conversation_id == c.id))
    return {"id": c.id, "statut": c.status, "visiteur": c.user_name, "email": c.user_email, "langue": c.lang,
            "messages": count or 0, "dernier_message": (last or "")[:200], "mis_a_jour": c.updated_at}


def _get_conv(db: Session, conv_id: int) -> ChatbotConversation:
    c = db.get(ChatbotConversation, conv_id)
    if c is None:
        raise ApiError(404, "NOT_FOUND", "Conversation introuvable")
    return c


@admin.get("/conversations", response_model=Envelope[Page[ConversationOut]],
           summary="Conversations (filtre : bot, pending_human = en attente d'un conseiller, human, closed)")
def conversations(statut: Literal["bot", "pending_human", "human", "closed"] | None = None, page: PageParams = Depends(),
                  db: Session = DB, _: User = MANAGE):
    query = select(ChatbotConversation).order_by(ChatbotConversation.updated_at.desc())
    if statut:
        query = query.where(ChatbotConversation.status == statut)
    rows, meta = paginate(db, query, page)
    return ok({"items": [_conv_out(db, c) for c in rows], "meta": meta})


@admin.get("/conversations/{conv_id}", response_model=Envelope[dict], summary="Détail d'une conversation")
def conversation(conv_id: int, db: Session = DB, _: User = MANAGE):
    c = _get_conv(db, conv_id)
    return ok({**_conv_out(db, c), "historique": [engine.message_out(m) for m in engine.history(db, c)]})


@admin.post("/conversations/{conv_id}/prendre", response_model=Envelope[dict], summary="Prendre la main (conseiller)")
def take_over(conv_id: int, db: Session = DB, actor: User = MANAGE):
    c = _get_conv(db, conv_id)
    c.status, c.agent_id, c.updated_at = "human", actor.id, now()
    for h in db.scalars(select(ChatbotHandoff).where(ChatbotHandoff.conversation_id == c.id, ChatbotHandoff.status == "open")):
        h.status, h.agent_id, h.updated_at = "assigned", actor.id, now()
    engine._bot(db, c, f"{actor.prenom} (conseiller) a rejoint la conversation.", sender="system")
    db.commit()
    return ok(_conv_out(db, c), "Vous avez pris la conversation")


@admin.post("/conversations/{conv_id}/repondre", response_model=Envelope[ChatMessageOut], summary="Répondre au visiteur")
def reply(conv_id: int, body: ReplyIn, db: Session = DB, actor: User = MANAGE):
    c = _get_conv(db, conv_id)
    if c.status == "closed":
        raise ApiError(409, "CONVERSATION_CLOSED", "Conversation terminée")
    if c.status != "human":
        c.status, c.agent_id = "human", actor.id
    m = engine._bot(db, c, body.texte.strip(), {"conseiller": f"{actor.prenom} {actor.nom}"}, sender="agent")
    db.commit()
    return ok(engine.message_out(m))


@admin.post("/conversations/{conv_id}/rendre-au-bot", response_model=Envelope[dict], summary="Rendre la main au bot")
def back_to_bot(conv_id: int, db: Session = DB, _: User = MANAGE):
    c = _get_conv(db, conv_id)
    c.status, c.updated_at = "bot", now()
    for h in db.scalars(select(ChatbotHandoff).where(ChatbotHandoff.conversation_id == c.id, ChatbotHandoff.status != "closed")):
        h.status, h.updated_at = "closed", now()
    engine._bot(db, c, "L'assistant reprend la conversation.", sender="system")
    db.commit()
    return ok(_conv_out(db, c))


@admin.post("/conversations/{conv_id}/fermer", response_model=Envelope[dict], summary="Clore la conversation")
def close(conv_id: int, db: Session = DB, _: User = MANAGE):
    c = _get_conv(db, conv_id)
    c.status, c.updated_at = "closed", now()
    for h in db.scalars(select(ChatbotHandoff).where(ChatbotHandoff.conversation_id == c.id, ChatbotHandoff.status != "closed")):
        h.status, h.updated_at = "closed", now()
    db.commit()
    return ok(_conv_out(db, c), "Conversation close")


# ═════════════════════════ Admin : statistiques ═════════════════════════

@admin.get("/statistiques", response_model=Envelope[dict],
           summary="Consommation par modèle, avis, questions sans réponse, conseillers en attente")
def stats(jours: int = Query(30, ge=1, le=365), db: Session = DB, _: User = MANAGE):
    since = now() - timedelta(days=jours)
    usage = db.execute(select(AiProvider.name, AiUsage.model, func.count(), func.sum(AiUsage.prompt_tokens),
                              func.sum(AiUsage.completion_tokens), func.sum(AiUsage.ok == False))  # noqa: E712
                       .select_from(AiUsage).join(AiProvider, AiProvider.id == AiUsage.provider_id, isouter=True)
                       .where(AiUsage.created_at >= since).group_by(AiProvider.name, AiUsage.model)).all()
    ratings = dict(db.execute(select(ChatbotFeedback.rating, func.count()).where(ChatbotFeedback.created_at >= since)
                              .group_by(ChatbotFeedback.rating)).all())
    unanswered = db.execute(
        select(ChatbotMessage.conversation_id, ChatbotMessage.created_at, ChatbotMessage.meta)
        .where(ChatbotMessage.sender == "bot", ChatbotMessage.created_at >= since,
               ChatbotMessage.meta.like('%"sans_reponse": true%'))
        .order_by(ChatbotMessage.id.desc()).limit(30)).all()
    questions = []
    for conv_id, created, _meta in unanswered:
        q = db.scalar(select(ChatbotMessage.message).where(ChatbotMessage.conversation_id == conv_id,
                                                           ChatbotMessage.sender == "user",
                                                           ChatbotMessage.created_at <= created)
                      .order_by(ChatbotMessage.id.desc()).limit(1))
        if q:
            questions.append(q[:300])
    return ok({
        "periode_jours": jours,
        "conversations": db.scalar(select(func.count()).select_from(ChatbotConversation)
                                   .where(ChatbotConversation.created_at >= since)) or 0,
        "en_attente_conseiller": db.scalar(select(func.count()).select_from(ChatbotConversation)
                                           .where(ChatbotConversation.status == "pending_human")) or 0,
        "consommation": [{"fournisseur": p or "?", "modele": m, "appels": n, "jetons_entree": int(i or 0),
                          "jetons_sortie": int(o or 0), "echecs": int(e or 0)} for p, m, n, i, o, e in usage],
        "avis": {"utiles": ratings.get(1, 0), "pas_utiles": ratings.get(-1, 0)},
        "questions_sans_reponse": list(dict.fromkeys(questions)),
    })
