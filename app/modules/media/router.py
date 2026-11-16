import datetime as dt
from typing import Literal

from fastapi import APIRouter, BackgroundTasks, Depends, Query, Request, status
from fastapi.responses import FileResponse
from fastapi.security import HTTPAuthorizationCredentials
from pydantic import BaseModel, Field
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core.client import ClientInfo, get_client
from app.core.config import get_settings
from app.core.database import get_db
from app.core.deps import bearer, get_current_user
from app.core.pagination import Page, PageParams, paginate
from app.core.permissions import require_permission
from app.core.responses import ApiError, Envelope, ok
from app.models import Media, MediaCollection, User
from app.modules.media import processing, service

DB = Depends(get_db)
CLIENT = Depends(get_client)
USER = Depends(get_current_user)
MANAGE = Depends(require_permission("media.manage"))
Category = Literal["audio", "video", "image", "fichier", "link"]

public = APIRouter(prefix="/medias", tags=["Médias (public)"])
listener = APIRouter(prefix="/medias", tags=["Médias · Auditeur connecté"])
admin = APIRouter(prefix="/admin/medias", tags=["Admin · Médias"])


class PlayIn(BaseModel):
    secondes_ecoutees: int = Field(ge=0, le=86400)
    position: int | None = Field(default=None, ge=0, le=86400)


class ReactionIn(BaseModel):
    reaction: Literal["like", "dislike"] | None = Field(description="null pour retirer sa réaction")


class RatingIn(BaseModel):
    note: int = Field(ge=1, le=5)


class CommentIn(BaseModel):
    texte: str = Field(min_length=2, max_length=2000)


class ProgressIn(BaseModel):
    position: int = Field(ge=0, le=86400)


class UploadInitIn(BaseModel):
    nom_fichier: str = Field(min_length=3, max_length=255)
    taille: int = Field(gt=0)


class MediaMetaIn(BaseModel):
    titre: str = Field(min_length=2, max_length=255)
    description: str | None = Field(default=None, max_length=10000)
    credits: str | None = Field(default=None, max_length=255)
    date: dt.date | None = None
    publie: bool = True
    site_web: bool = True
    whatsapp: bool = False


class LinkIn(MediaMetaIn):
    url: str = Field(min_length=10, max_length=500)


class MediaUpdateIn(BaseModel):
    titre: str | None = Field(default=None, min_length=2, max_length=255)
    description: str | None = Field(default=None, max_length=10000)
    credits: str | None = Field(default=None, max_length=255)
    date: dt.date | None = None
    publie: bool | None = None
    site_web: bool | None = None
    whatsapp: bool | None = None


class CollectionIn(BaseModel):
    titre: str = Field(min_length=2, max_length=255)
    description: str | None = Field(default=None, max_length=5000)
    medias: list[str] = Field(default_factory=list, max_length=200, description="uuid des médias, dans l'ordre")
    position: int = Field(default=0, ge=0, le=10000)
    actif: bool = True


def _optional_user(db: Session, creds: HTTPAuthorizationCredentials | None) -> User | None:
    if creds is None:
        return None
    from app.core.deps import get_current_session

    try:
        return get_current_user(get_current_session(creds, db), db)
    except ApiError:
        return None


# ═════════════ Public ═════════════

@public.get("/categories", response_model=Envelope[list[dict]], summary="Catégories : audio, video, image, fichier, link")
def categories(db: Session = DB):
    return ok(service.categories(db))


@public.get("", response_model=Envelope[Page[dict]], summary="Médiathèque : catégorie, recherche, tri")
def library(categorie: Category | None = None, q: str | None = Query(None, max_length=100),
            tri: Literal["recent", "populaire", "tendance", "mieux_notes"] = "recent",
            page: PageParams = Depends(), db: Session = DB):
    rows, meta = paginate(db, service.library_query(q, categorie, tri), page)
    return ok({"items": service.items_with_stats(db, rows), "meta": meta})


@public.get("/collections", response_model=Envelope[list[dict]], summary="Collections (séries)")
def collections(db: Session = DB):
    rows = db.scalars(select(MediaCollection).where(MediaCollection.is_active.is_(True))
                      .order_by(MediaCollection.position, MediaCollection.created_at.desc()))
    return ok([service.collection_out(db, c) for c in rows])


@public.get("/collections/{slug}", response_model=Envelope[dict], summary="Une collection et ses épisodes, dans l'ordre")
def collection(slug: str, db: Session = DB):
    return ok(service.collection_out(db, service.get_collection(db, slug), with_items=True))


@listener.get("/mes-favoris", response_model=Envelope[Page[dict]], summary="Mes favoris")
def my_favorites(page: PageParams = Depends(), user: User = USER, db: Session = DB):
    rows, meta = paginate(db, service.favorites_query(user), page)
    return ok({"items": service.items_with_stats(db, rows), "meta": meta})


@listener.get("/reprendre", response_model=Envelope[list[dict]], summary="Reprendre la lecture (en cours)")
def resume(user: User = USER, db: Session = DB):
    return ok(service.continue_listening(db, user))


@public.get("/{slug}", response_model=Envelope[dict], summary="Fiche d'un média (statistiques, recommandations)")
def media(slug: str, db: Session = DB, creds: HTTPAuthorizationCredentials | None = Depends(bearer)):
    return ok(service.detail(db, service.get_public(db, slug), _optional_user(db, creds)))


@public.get("/{slug}/commentaires", response_model=Envelope[Page[dict]], summary="Commentaires publiés")
def media_comments(slug: str, page: PageParams = Depends(), db: Session = DB):
    rows, meta = paginate(db, service.comments(db, service.get_public(db, slug)), page)
    return ok({"items": [service.comment_out(c) for c in rows], "meta": meta})


@public.get("/{slug}/flux", summary="Lecture en continu (avance rapide possible : en-tête Range)",
            response_class=FileResponse, responses={200: {"content": {"audio/*": {}, "video/*": {}}}, 206: {}})
def stream(slug: str, qualite: Literal["legere", "originale"] = "legere", db: Session = DB):
    m = service.get_public(db, slug)
    path = service.stream_path(m, qualite == "legere")
    media_type = "audio/mp4" if path.suffix == ".m4a" else service.mime_of(path, m.mime_type)
    return FileResponse(path, media_type=media_type, headers={"Cache-Control": "public, max-age=86400",
                                                              "Accept-Ranges": "bytes"})


@public.get("/{slug}/apercu", summary="Afficher un document ou une image dans la page (sans compter de téléchargement)",
            response_class=FileResponse)
def preview(slug: str, db: Session = DB):
    m = service.get_public(db, slug)
    path = service.preview_path(m)
    # Seul cet aperçu peut être affiché dans une page (lecteur PDF du site) : uniquement par les origines autorisées
    origins = get_settings().cors_origin_list
    ancestors = "*" if "*" in origins or not origins else " ".join(origins)
    return FileResponse(path, media_type=service.mime_of(path, m.mime_type), content_disposition_type="inline",
                        filename=f"{m.slug or 'media'}{path.suffix}",
                        headers={"Cache-Control": "public, max-age=86400", "X-Frame-Options": "SAMEORIGIN",
                                 "Content-Security-Policy": f"frame-ancestors {ancestors}"})


@public.get("/{slug}/telecharger", summary="Télécharger le fichier original", response_class=FileResponse)
def download(slug: str, db: Session = DB, client: ClientInfo = CLIENT,
             creds: HTTPAuthorizationCredentials | None = Depends(bearer)):
    m = service.get_public(db, slug)
    path = service.download(db, m, _optional_user(db, creds), client)
    name = f"{m.slug or 'media'}{path.suffix}"
    return FileResponse(path, media_type=m.mime_type or "application/octet-stream", filename=name)


@public.post("/{slug}/vue", response_model=Envelope[dict], summary="Compter une vue (une par visiteur / 30 min)")
def view(slug: str, db: Session = DB, client: ClientInfo = CLIENT,
         creds: HTTPAuthorizationCredentials | None = Depends(bearer)):
    counted = service.record_view(db, service.get_public(db, slug), _optional_user(db, creds), client)
    return ok({"comptee": counted})


@public.post("/{slug}/ecoute", response_model=Envelope[None],
             summary="Durée écoutée (et position si connecté : reprise de lecture)")
def play(slug: str, body: PlayIn, db: Session = DB, client: ClientInfo = CLIENT,
         creds: HTTPAuthorizationCredentials | None = Depends(bearer)):
    service.record_play(db, service.get_public(db, slug), body.secondes_ecoutees, body.position,
                        _optional_user(db, creds), client)
    return ok()


# ═════════════ Auditeur connecté ═════════════

@listener.post("/{slug}/reaction", response_model=Envelope[dict], summary="J'aime / je n'aime pas (une par compte)")
def react(slug: str, body: ReactionIn, user: User = USER, db: Session = DB):
    return ok(service.react(db, service.get_public(db, slug), user, body.reaction))


@listener.put("/{slug}/note", response_model=Envelope[dict], summary="Noter (1 à 5, modifiable)")
def rate(slug: str, body: RatingIn, user: User = USER, db: Session = DB):
    return ok(service.rate(db, service.get_public(db, slug), user, body.note))


@listener.post("/{slug}/favori", response_model=Envelope[None], summary="Ajouter aux favoris")
def add_favorite(slug: str, user: User = USER, db: Session = DB):
    service.favorite(db, service.get_public(db, slug), user, True)
    return ok(message="Ajouté aux favoris")


@listener.delete("/{slug}/favori", response_model=Envelope[None], summary="Retirer des favoris")
def remove_favorite(slug: str, user: User = USER, db: Session = DB):
    service.favorite(db, service.get_public(db, slug), user, False)
    return ok(message="Retiré des favoris")


@listener.post("/{slug}/commentaires", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
               summary="Commenter (publié après modération)")
def comment(slug: str, body: CommentIn, user: User = USER, db: Session = DB, client: ClientInfo = CLIENT):
    c = service.add_comment(db, service.get_public(db, slug), user, body.texte, client)
    return ok(service.comment_out(c), "Merci ! Votre commentaire sera publié après relecture.")


@listener.put("/{slug}/progression", response_model=Envelope[None], summary="Enregistrer la position de lecture")
def progress(slug: str, body: ProgressIn, user: User = USER, db: Session = DB):
    service.save_progress(db, service.get_public(db, slug), user, body.position)
    return ok()


# ═════════════ Administration ═════════════

@admin.get("", response_model=Envelope[Page[dict]], summary="Tous les médias (masqués et état du traitement compris)")
def admin_list(categorie: Category | None = None, q: str | None = Query(None, max_length=100),
               traitement: Literal["aucun", "en_attente", "en_cours", "pret", "erreur"] | None = None,
               page: PageParams = Depends(), db: Session = DB, _: User = MANAGE):
    query = select(Media)
    if categorie:
        query = query.where(Media.type.in_(service.CATEGORIES[categorie]))
    if q:
        from app.core.helpers import like_pattern

        query = query.where(Media.titre.like(like_pattern(q)))
    if traitement:
        query = query.where(Media.traitement == traitement)
    rows, meta = paginate(db, query.order_by(Media.created_at.desc(), Media.id.desc()), page)
    return ok({"items": [service.admin_out(db, m) for m in rows], "meta": meta})


@admin.get("/stats", response_model=Envelope[dict], summary="Statistiques de la médiathèque")
def admin_stats(db: Session = DB, _: User = MANAGE):
    return ok(service.stats(db))


@admin.post("/envois", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
            summary="1. Démarrer un envoi par morceaux (reprise possible après coupure)")
def upload_init(body: UploadInitIn, db: Session = DB, actor: User = MANAGE):
    return ok(service.upload_out(service.init_upload(db, actor, body.nom_fichier, body.taille)))


@admin.get("/envois/{upload_id}", response_model=Envelope[dict], summary="État d'un envoi (morceaux déjà reçus)")
def upload_status(upload_id: str, db: Session = DB, actor: User = MANAGE):
    return ok(service.upload_out(service.get_upload(db, upload_id, actor)))


@admin.put("/envois/{upload_id}/morceaux/{index}", response_model=Envelope[dict],
           summary="2. Envoyer un morceau (corps brut, 5 Mo, sauf le dernier)")
async def upload_chunk(upload_id: str, index: int, request: Request, db: Session = DB, actor: User = MANAGE):
    up = service.get_upload(db, upload_id, actor)
    data = await request.body()
    return ok(service.put_chunk(db, up, index, data))


@admin.post("/envois/{upload_id}/terminer", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
            summary="3. Terminer : assemble, vérifie le contenu, crée le média et lance le traitement")
def upload_complete(upload_id: str, body: MediaMetaIn, background: BackgroundTasks, db: Session = DB,
                    client: ClientInfo = CLIENT, actor: User = MANAGE):
    m = service.complete_upload(db, service.get_upload(db, upload_id, actor), actor, body, client)
    if m.traitement == "en_attente" and processing.available():
        background.add_task(processing.process, m.id)
    return ok(service.admin_out(db, m), "Média ajouté : traitement en cours" if m.traitement == "en_attente" else "Média ajouté")


@admin.post("/liens", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
            summary="Ajouter un lien (YouTube…)")
def add_link(body: LinkIn, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    return ok(service.admin_out(db, service.add_link(db, actor, body, client)), "Lien ajouté")


@admin.get("/commentaires", response_model=Envelope[Page[dict]], summary="Modération des commentaires")
def moderation(statut: Literal["en_attente", "publie", "refuse"] = "en_attente", page: PageParams = Depends(),
               db: Session = DB, _: User = MANAGE):
    rows, meta = paginate(db, service.moderation_query(statut), page)
    return ok({"items": [service.comment_out(c, admin=True) for c in rows], "meta": meta})


@admin.post("/commentaires/{comment_id}/publier", response_model=Envelope[dict], summary="Publier un commentaire")
def approve(comment_id: int, db: Session = DB, actor: User = MANAGE):
    return ok(service.comment_out(service.moderate(db, comment_id, actor, True), admin=True), "Commentaire publié")


@admin.post("/commentaires/{comment_id}/refuser", response_model=Envelope[dict], summary="Refuser un commentaire")
def reject(comment_id: int, db: Session = DB, actor: User = MANAGE):
    return ok(service.comment_out(service.moderate(db, comment_id, actor, False), admin=True), "Commentaire refusé")


@admin.post("/collections", status_code=status.HTTP_201_CREATED, response_model=Envelope[dict],
            summary="Créer une collection (médias dans l'ordre)")
def add_collection(body: CollectionIn, db: Session = DB, _: User = MANAGE):
    return ok(service.collection_out(db, service.save_collection(db, None, body), with_items=True), "Collection créée")


@admin.put("/collections/{collection_uuid}", response_model=Envelope[dict], summary="Modifier une collection")
def edit_collection(collection_uuid: str, body: CollectionIn, db: Session = DB, _: User = MANAGE):
    c = service.get_collection(db, collection_uuid, public=False)
    return ok(service.collection_out(db, service.save_collection(db, c, body), with_items=True), "Collection mise à jour")


@admin.delete("/collections/{collection_uuid}", response_model=Envelope[None], summary="Supprimer une collection")
def delete_collection(collection_uuid: str, db: Session = DB, _: User = MANAGE):
    db.delete(service.get_collection(db, collection_uuid, public=False))
    db.commit()
    return ok(message="Collection supprimée (les médias sont conservés)")


@admin.get("/{media_uuid}", response_model=Envelope[dict], summary="Détail et statistiques d'un média")
def admin_media(media_uuid: str, db: Session = DB, _: User = MANAGE):
    return ok(service.admin_out(db, service.get_admin(db, media_uuid)))


@admin.patch("/{media_uuid}", response_model=Envelope[dict], summary="Modifier (titre, publication…)")
def admin_update(media_uuid: str, body: MediaUpdateIn, db: Session = DB, _: User = MANAGE):
    m = service.update_media(db, service.get_admin(db, media_uuid), body.model_dump(exclude_unset=True))
    return ok(service.admin_out(db, m), "Média mis à jour")


@admin.post("/{media_uuid}/retraiter", response_model=Envelope[dict], summary="Relancer le traitement ffmpeg")
def admin_reprocess(media_uuid: str, background: BackgroundTasks, db: Session = DB, _: User = MANAGE):
    m = service.get_admin(db, media_uuid)
    if m.type not in ("audio", "video") or not m.fichier:
        raise ApiError(409, "NOT_PROCESSABLE", "Seuls les audios et vidéos sont traités")
    if not processing.available():
        raise ApiError(503, "FFMPEG_MISSING", "ffmpeg n'est pas installé sur le serveur")
    m.traitement = "en_attente"
    db.commit()
    background.add_task(processing.process, m.id)
    return ok(service.admin_out(db, m), "Traitement relancé")


@admin.delete("/{media_uuid}", response_model=Envelope[None], summary="Supprimer (fichier et versions dérivées)")
def admin_delete(media_uuid: str, db: Session = DB, client: ClientInfo = CLIENT, actor: User = MANAGE):
    service.delete_media(db, actor, service.get_admin(db, media_uuid), client)
    return ok(message="Média supprimé")
