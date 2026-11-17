"""Médiathèque.

Catégories = types : audio, video, image, fichier (document/autre), link.
Vues et écoutes : anonymes, comptées une fois par visiteur et par 30 minutes.
Réactions, notes, favoris, commentaires : compte obligatoire ; commentaires modérés avant publication."""

import hashlib
import json
import mimetypes
import re
import secrets
import uuid
from datetime import date, timedelta
from pathlib import Path

from sqlalchemy import case, delete, func, or_, select
from sqlalchemy.orm import Session

from app.core import audit
from app.core.client import ClientInfo
from app.core.config import get_settings
from app.core.helpers import like_pattern, now, slugify
from app.core.responses import ApiError
from app.models import (
    Media,
    MediaCollection,
    MediaCollectionItem,
    MediaComment,
    MediaDownload,
    MediaFavorite,
    MediaLike,
    MediaPlay,
    MediaProgress,
    MediaRating,
    MediaUpload,
    MediaView,
    User,
)
from app.models.media import CATEGORIES, CATEGORY_OF
from app.modules.media import processing

VIEW_WINDOW = timedelta(minutes=30)
YOUTUBE = re.compile(r"(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})")
UPLOAD_LIMITS = {"audio": 500, "video": 500, "image": 20, "fichier": 100}  # Mo
EXTENSIONS = {"audio": {"mp3", "m4a", "aac", "opus", "ogg", "wav", "flac", "webm"},
              "video": {"mp4", "mov", "webm", "mkv", "avi"},
              "image": {"jpg", "jpeg", "png", "webp", "gif"},
              "fichier": {"pdf", "docx", "xlsx", "pptx", "txt"}}
FOLDERS = {"audio": "attachments/Audio/Originals", "video": "attachments/Video/Originals",
           "image": "attachments/Media", "fichier": "attachments/Document/Files"}
CHUNK = 5 * 1024 * 1024
BANNED = re.compile(r"(viagra|casino|bitcoin|crypto|porn|xxx|loan|forex)", re.I)


# ───────────────────────── outils ─────────────────────────

def file_url(relative: str | None) -> str | None:
    if not relative:
        return None
    if relative.startswith(("http://", "https://")):
        return relative
    tail = relative.removeprefix("attachments/").lstrip("/")
    return f"{get_settings().media_base_url.rstrip('/')}/{tail}"


def youtube_id(url: str | None) -> str | None:
    match = YOUTUBE.search(url or "")
    return match.group(1) if match else None


def _visitor(client: ClientInfo, user: User | None) -> str:
    """Identifiant anonyme du visiteur (IP + navigateur hachés) pour dédoublonner vues et écoutes."""
    return f"u{user.id}" if user else "v" + hashlib.sha256(f"{client.ip}|{client.user_agent}".encode()).hexdigest()[:40]


def _visible():
    return select(Media).where(Media.est_actif.is_(True))


def _stats_for(db: Session, ids: list[int]) -> dict[int, dict]:
    if not ids:
        return {}
    out = {i: {"vues": 0, "likes": 0, "dislikes": 0, "note": None, "notes": 0, "commentaires": 0} for i in ids}
    for mid, n in db.execute(select(MediaView.id_media, func.count()).where(MediaView.id_media.in_(ids))
                             .group_by(MediaView.id_media)):
        out[mid]["vues"] = n
    for mid, likes, dislikes in db.execute(select(MediaLike.id_media, func.sum(case((MediaLike.action == "like", 1), else_=0)),
                                                  func.sum(case((MediaLike.action == "dislike", 1), else_=0)))
                                           .where(MediaLike.id_media.in_(ids)).group_by(MediaLike.id_media)):
        out[mid]["likes"], out[mid]["dislikes"] = int(likes or 0), int(dislikes or 0)
    for mid, avg, n in db.execute(select(MediaRating.id_media, func.avg(MediaRating.rating), func.count())
                                  .where(MediaRating.id_media.in_(ids)).group_by(MediaRating.id_media)):
        out[mid]["note"], out[mid]["notes"] = round(float(avg), 1), n
    for mid, n in db.execute(select(MediaComment.id_media, func.count()).where(
            MediaComment.id_media.in_(ids), MediaComment.is_approved == 1).group_by(MediaComment.id_media)):
        out[mid]["commentaires"] = n
    return out


def _waveform(raw: str | None) -> list[int] | None:
    """Liste de pics (JSON). Le PHP stockait parfois le chemin d'une image : ancien format ignoré."""
    if not raw or not raw.lstrip().startswith("["):
        return None
    try:
        return json.loads(raw)
    except ValueError:
        return None


def media_out(m: Media, stats: dict | None = None, detail: bool = False) -> dict:
    yt = youtube_id(m.lien) if m.type == "link" else None
    thumbnail = file_url(m.miniature) or (f"https://img.youtube.com/vi/{yt}/hqdefault.jpg" if yt else None)
    data = {
        "uuid": m.uuid, "slug": m.slug, "titre": m.titre, "categorie": CATEGORY_OF.get(m.type, "fichier"),
        "miniature": thumbnail, "duree": m.duree,
        "date": m.date_media if isinstance(m.date_media, date) else None,  # « 0000-00-00 » du PHP
        "credits": m.credits,
        "telechargements": m.telechargements or 0, "stats": stats or {}, "created_at": m.created_at,
        # pour la carte : icône selon le type de document, menu « ⋮ » (télécharger, ouvrir le lien)
        "format": (m.fichier.rsplit(".", 1)[-1].lower() if m.fichier and "." in m.fichier else None),
        "telechargeable": m.type != "link" and bool(m.fichier),
        "lien_externe": m.lien if m.type == "link" else None,
    }
    if detail:
        data.update({
            "description": m.description, "lien": m.lien if m.type == "link" else None, "youtube_id": yt,
            "taille": m.taille, "type_mime": m.mime_type,
            "forme_onde": _waveform(m.waveform_data),
            "version_legere_disponible": _light_exists(m),
            "lisible": m.type in ("audio", "video") and bool(m.fichier),
            "telechargeable": m.type != "link" and bool(m.fichier),
            "apercu": m.type in ("document", "image") and bool(m.fichier),
            # URL directe servie par nginx (rapide) au lieu de l'endpoint Python /flux
            "flux_url": file_url(m.version_legere if _light_exists(m) else m.fichier),
        })
    return data


# ───────────────────────── bibliothèque publique ─────────────────────────

def categories(db: Session) -> list[dict]:
    counts = dict(db.execute(select(Media.type, func.count()).where(Media.est_actif.is_(True)).group_by(Media.type)).all())
    labels = {"audio": "Audio", "video": "Vidéo", "image": "Image", "fichier": "Fichier", "link": "Lien"}
    return [{"categorie": c, "libelle": labels[c], "medias": sum(counts.get(t, 0) for t in types)}
            for c, types in CATEGORIES.items()]


def library_query(q: str | None, categorie: str | None, tri: str):
    query = _visible()
    if categorie:
        query = query.where(Media.type.in_(CATEGORIES[categorie]))
    if q and q.strip():
        p = like_pattern(q.strip())
        query = query.where(or_(Media.titre.like(p), Media.description.like(p), Media.credits.like(p)))
    if tri == "populaire":
        views = select(MediaView.id_media, func.count().label("n")).group_by(MediaView.id_media).subquery()
        return query.outerjoin(views, views.c.id_media == Media.id).order_by(func.coalesce(views.c.n, 0).desc(), Media.id.desc())
    if tri == "tendance":
        views = select(MediaView.id_media, func.count().label("n")).where(
            MediaView.viewed_at >= now() - timedelta(days=7)).group_by(MediaView.id_media).subquery()
        return query.outerjoin(views, views.c.id_media == Media.id).order_by(func.coalesce(views.c.n, 0).desc(), Media.id.desc())
    if tri == "mieux_notes":
        notes = select(MediaRating.id_media, func.avg(MediaRating.rating).label("avg")).group_by(MediaRating.id_media).subquery()
        return query.outerjoin(notes, notes.c.id_media == Media.id).order_by(func.coalesce(notes.c.avg, 0).desc(), Media.id.desc())
    return query.order_by(Media.created_at.desc(), Media.id.desc())


def items_with_stats(db: Session, rows: list[Media]) -> list[dict]:
    stats = _stats_for(db, [m.id for m in rows])
    return [media_out(m, stats.get(m.id)) for m in rows]


def get_public(db: Session, slug: str) -> Media:
    m = db.scalar(_visible().where(or_(Media.slug == slug, Media.uuid == slug)))
    if m is None:
        raise ApiError(404, "NOT_FOUND", "Média introuvable")
    return m


def detail(db: Session, m: Media, user: User | None) -> dict:
    data = media_out(m, _stats_for(db, [m.id]).get(m.id), detail=True)
    similar = list(db.scalars(_visible().where(Media.type.in_(CATEGORIES[data["categorie"]]), Media.id != m.id)
                              .order_by(Media.created_at.desc()).limit(8)))
    data["recommandations"] = items_with_stats(db, similar)
    data["collections"] = [{"titre": c.titre, "slug": c.slug} for c in db.scalars(
        select(MediaCollection).join(MediaCollectionItem, MediaCollectionItem.collection_id == MediaCollection.id)
        .where(MediaCollectionItem.id_media == m.id, MediaCollection.is_active.is_(True)))]
    if user:
        like = db.scalar(select(MediaLike.action).where(MediaLike.id_media == m.id, MediaLike.user_id == user.id))
        rating = db.scalar(select(MediaRating.rating).where(MediaRating.id_media == m.id, MediaRating.user_id == user.id))
        progress = db.get(MediaProgress, (user.id, m.id))
        data["moi"] = {"reaction": like, "note": rating,
                       "favori": bool(db.scalar(select(MediaFavorite.id).where(MediaFavorite.id_media == m.id,
                                                                               MediaFavorite.user_id == user.id))),
                       "position": progress.position_secondes if progress and not progress.termine else 0}
    return data


def comments(db: Session, m: Media):
    return select(MediaComment).where(MediaComment.id_media == m.id, MediaComment.is_approved == 1) \
        .order_by(MediaComment.created_at.desc())


def comment_out(c: MediaComment, admin: bool = False) -> dict:
    data = {"id": c.id, "auteur": c.author_name or "Anonyme", "texte": c.comment, "le": c.created_at}
    if admin:
        data.update({"statut": {0: "en_attente", 1: "publie", -1: "refuse"}.get(c.is_approved, "en_attente"),
                     "email": c.author_email, "media_id": c.id_media})
    return data


def _light_exists(m: Media) -> bool:
    if not m.version_legere:
        return False
    try:
        return processing.absolute(m.version_legere).exists()
    except processing.ProcessingError:
        return False


def stream_path(m: Media, light: bool) -> Path:
    if m.type not in ("audio", "video") or not m.fichier:
        raise ApiError(409, "NOT_STREAMABLE", "Ce média ne se lit pas en continu")
    # version légère absente du disque (jamais générée) : on lit l'original
    relative = m.version_legere if light and _light_exists(m) else m.fichier
    path = processing.absolute(relative)
    if not path.exists():
        raise ApiError(404, "FILE_MISSING", "Fichier introuvable sur le serveur")
    return path


# Type MIME : beaucoup de fichiers importés du PHP n'en ont pas ; sans lui, certains navigateurs refusent la lecture
EXTRA_MIME = {".opus": "audio/ogg", ".ogg": "audio/ogg", ".mp3": "audio/mpeg", ".m4a": "audio/mp4", ".mp4": "video/mp4",
              ".webm": "video/webm", ".pdf": "application/pdf"}


def mime_of(path: Path, stored: str | None) -> str:
    if stored:
        return stored
    return EXTRA_MIME.get(path.suffix.lower()) or mimetypes.guess_type(path.name)[0] or "application/octet-stream"


def preview_path(m: Media) -> Path:
    """Fichier à afficher dans la page : documents (PDF…) et images seulement."""
    if m.type not in ("document", "image") or not m.fichier:
        raise ApiError(409, "NO_PREVIEW", "Ce média ne s'affiche pas en aperçu")
    path = processing.absolute(m.fichier)
    if not path.exists():
        raise ApiError(404, "FILE_MISSING", "Fichier introuvable sur le serveur")
    return path


def download(db: Session, m: Media, user: User | None, client: ClientInfo) -> Path:
    if m.type == "link" or not m.fichier:
        raise ApiError(409, "NOT_DOWNLOADABLE", "Ce média ne se télécharge pas")
    path = processing.absolute(m.fichier)
    if not path.exists():
        raise ApiError(404, "FILE_MISSING", "Fichier introuvable sur le serveur")
    m.telechargements = (m.telechargements or 0) + 1
    db.add(MediaDownload(id_media=m.id, user_id=user.id if user else None, ip_address=client.ip,
                         user_agent=client.user_agent or None, downloaded_at=now()))
    db.commit()
    return path


def record_view(db: Session, m: Media, user: User | None, client: ClientInfo) -> bool:
    visitor = _visitor(client, user)
    recent = db.scalar(select(MediaView.id).where(MediaView.id_media == m.id, MediaView.session_id == visitor,
                                                  MediaView.viewed_at > now() - VIEW_WINDOW).limit(1))
    if recent:
        return False
    db.add(MediaView(id_media=m.id, user_id=user.id if user else None, session_id=visitor, ip_address=client.ip,
                     user_agent=client.user_agent or None, viewed_at=now()))
    db.commit()
    return True


def record_play(db: Session, m: Media, seconds: int, position: int | None, user: User | None, client: ClientInfo) -> None:
    visitor = _visitor(client, user)
    play = db.scalar(select(MediaPlay).where(MediaPlay.id_media == m.id, MediaPlay.session_id == visitor,
                                             MediaPlay.played_at > now() - VIEW_WINDOW).order_by(MediaPlay.id.desc()).limit(1))
    if play is None:
        db.add(MediaPlay(id_media=m.id, ip_address=client.ip, session_id=visitor, duration_played=seconds, played_at=now()))
    else:
        play.duration_played = max(play.duration_played or 0, seconds)
    if user and position is not None:
        save_progress(db, m, user, position, commit=False)
    db.commit()


# ───────────────────────── auditeur connecté ─────────────────────────

def react(db: Session, m: Media, user: User, reaction: str | None) -> dict:
    row = db.scalar(select(MediaLike).where(MediaLike.id_media == m.id, MediaLike.user_id == user.id))
    if reaction is None:
        if row is not None:
            db.delete(row)
    elif row is None:
        db.add(MediaLike(id_media=m.id, user_id=user.id, action=reaction, created_at=now()))
    else:
        row.action = reaction
    db.commit()
    return _stats_for(db, [m.id])[m.id]


def rate(db: Session, m: Media, user: User, note: int) -> dict:
    row = db.scalar(select(MediaRating).where(MediaRating.id_media == m.id, MediaRating.user_id == user.id))
    if row is None:
        db.add(MediaRating(id_media=m.id, user_id=user.id, rating=note, created_at=now()))
    else:
        row.rating = note
    db.commit()
    return _stats_for(db, [m.id])[m.id]


def favorite(db: Session, m: Media, user: User, on: bool) -> None:
    row = db.scalar(select(MediaFavorite).where(MediaFavorite.id_media == m.id, MediaFavorite.user_id == user.id))
    if on and row is None:
        db.add(MediaFavorite(id_media=m.id, user_id=user.id, created_at=now()))
    elif not on and row is not None:
        db.delete(row)
    db.commit()


def favorites_query(user: User):
    return select(Media).join(MediaFavorite, MediaFavorite.id_media == Media.id).where(
        MediaFavorite.user_id == user.id, Media.est_actif.is_(True)).order_by(MediaFavorite.created_at.desc())


def add_comment(db: Session, m: Media, user: User, text: str, client: ClientInfo) -> MediaComment:
    text = text.strip()
    recent = db.scalar(select(func.count()).select_from(MediaComment).where(
        MediaComment.user_id == user.id, MediaComment.created_at > now() - timedelta(minutes=10))) or 0
    if recent >= 5:
        raise ApiError(429, "TOO_MANY_REQUESTS", "Trop de commentaires d'affilée. Réessayez dans quelques minutes.",
                       {"retry_after_seconds": 600}, {"Retry-After": "600"})
    if len(re.findall(r"https?://", text)) > 1 or BANNED.search(text):
        raise ApiError(422, "COMMENT_REJECTED", "Ce commentaire ressemble à un message publicitaire")
    c = MediaComment(id_media=m.id, user_id=user.id, author_name=f"{user.prenom} {user.nom[:1]}.",
                     author_email=user.email, comment=text, ip_address=client.ip, is_approved=0, created_at=now())
    db.add(c)
    db.commit()
    return c


def save_progress(db: Session, m: Media, user: User, position: int, commit: bool = True) -> MediaProgress:
    total = m.duree
    row = db.get(MediaProgress, (user.id, m.id))
    if row is None:
        row = MediaProgress(user_id=user.id, id_media=m.id)
        db.add(row)
    row.position_secondes = max(position, 0)
    row.duree_secondes = total
    row.termine = bool(total and position >= total * 0.95)  # écouté à 95 % : terminé
    row.updated_at = now()
    if commit:
        db.commit()
    return row


def continue_listening(db: Session, user: User, limit: int = 20) -> list[dict]:
    rows = db.execute(select(Media, MediaProgress).join(MediaProgress, MediaProgress.id_media == Media.id).where(
        MediaProgress.user_id == user.id, MediaProgress.termine.is_(False), MediaProgress.position_secondes > 0,
        Media.est_actif.is_(True)).order_by(MediaProgress.updated_at.desc()).limit(limit)).all()
    return [{**media_out(m), "position": p.position_secondes,
             "pourcentage": round(p.position_secondes * 100 / m.duree) if m.duree else None} for m, p in rows]


# ───────────────────────── collections ─────────────────────────

def collection_out(db: Session, c: MediaCollection, with_items: bool = False) -> dict:
    items = list(db.scalars(select(Media).join(MediaCollectionItem, MediaCollectionItem.id_media == Media.id)
                            .where(MediaCollectionItem.collection_id == c.id, Media.est_actif.is_(True))
                            .order_by(MediaCollectionItem.position)))
    cover = db.get(Media, c.couverture_media_id) if c.couverture_media_id else (items[0] if items else None)
    data = {"uuid": c.uuid, "slug": c.slug, "titre": c.titre, "description": c.description,
            "couverture": media_out(cover)["miniature"] if cover else None, "episodes": len(items),
            "duree_totale": sum(m.duree or 0 for m in items), "actif": c.is_active}
    if with_items:
        data["medias"] = [{**media_out(m), "episode": i + 1} for i, m in enumerate(items)]
    return data


def get_collection(db: Session, slug: str, public: bool = True) -> MediaCollection:
    query = select(MediaCollection).where(or_(MediaCollection.slug == slug, MediaCollection.uuid == slug))
    if public:
        query = query.where(MediaCollection.is_active.is_(True))
    c = db.scalar(query)
    if c is None:
        raise ApiError(404, "NOT_FOUND", "Collection introuvable")
    return c


def save_collection(db: Session, c: MediaCollection | None, data) -> MediaCollection:
    media_ids = []
    for ref in data.medias:
        m = db.scalar(select(Media).where(Media.uuid == ref))
        if m is None:
            raise ApiError(422, "VALIDATION_ERROR", f"Média inconnu : {ref}")
        media_ids.append(m.id)
    if len(set(media_ids)) != len(media_ids):
        raise ApiError(422, "VALIDATION_ERROR", "Un média apparaît deux fois dans la collection")
    slug = slugify(data.titre)[:200] or "collection"
    if db.scalar(select(MediaCollection.id).where(MediaCollection.slug == slug,
                                                  MediaCollection.id != (c.id if c is not None else 0))):
        slug = f"{slug}-{secrets.token_hex(2)}"
    if c is None:
        c = MediaCollection(uuid=str(uuid.uuid4()), created_at=now())
        db.add(c)
    c.titre, c.slug, c.description, c.is_active, c.position = data.titre.strip(), slug, data.description, data.actif, data.position
    db.flush()
    db.execute(delete(MediaCollectionItem).where(MediaCollectionItem.collection_id == c.id))
    db.add_all(MediaCollectionItem(collection_id=c.id, id_media=mid, position=i) for i, mid in enumerate(media_ids))
    c.couverture_media_id = media_ids[0] if media_ids else None
    db.commit()
    return c


# ───────────────────────── administration : envoi par morceaux ─────────────────────────

def _category_for(filename: str) -> str:
    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else ""
    for category, exts in EXTENSIONS.items():
        if ext in exts:
            return category
    raise ApiError(422, "INVALID_FILE", "Extension non prise en charge")


def _temp_dir(upload_id: str) -> Path:
    from app.core.files import private_root

    path = private_root() / "media_uploads" / upload_id
    path.mkdir(parents=True, exist_ok=True)
    return path


def init_upload(db: Session, admin: User, filename: str, size: int) -> MediaUpload:
    category = _category_for(filename)
    limit = UPLOAD_LIMITS[category] * 1024 * 1024
    if size <= 0 or size > limit:
        raise ApiError(422, "INVALID_FILE", f"Taille invalide (maximum {UPLOAD_LIMITS[category]} Mo pour ce type)")
    up = MediaUpload(id=secrets.token_hex(16), admin_id=admin.id, nom_fichier=filename[:255], type=category, taille=size,
                     taille_morceau=CHUNK, morceaux_recus="[]", statut="en_cours", created_at=now(), updated_at=now())
    db.add(up)
    db.commit()
    return up


def upload_out(up: MediaUpload) -> dict:
    received = json.loads(up.morceaux_recus or "[]")
    total = -(-up.taille // up.taille_morceau)
    return {"id": up.id, "nom_fichier": up.nom_fichier, "categorie": up.type, "taille": up.taille,
            "taille_morceau": up.taille_morceau, "morceaux_total": total, "morceaux_recus": sorted(received),
            "complet": len(received) == total, "statut": up.statut}


def get_upload(db: Session, upload_id: str, admin: User) -> MediaUpload:
    up = db.get(MediaUpload, upload_id)
    if up is None or up.admin_id != admin.id:
        raise ApiError(404, "NOT_FOUND", "Envoi introuvable")
    return up


def put_chunk(db: Session, up: MediaUpload, index: int, data: bytes) -> dict:
    if up.statut != "en_cours":
        raise ApiError(409, "UPLOAD_CLOSED", "Cet envoi est terminé")
    total = -(-up.taille // up.taille_morceau)
    expected = up.taille_morceau if index < total - 1 else up.taille - up.taille_morceau * (total - 1)
    if not 0 <= index < total or len(data) != expected:
        raise ApiError(422, "INVALID_CHUNK", f"Morceau invalide (attendu : index 0–{total - 1}, {expected} octets)")
    (_temp_dir(up.id) / f"{index:06d}.part").write_bytes(data)
    received = set(json.loads(up.morceaux_recus or "[]"))
    received.add(index)
    up.morceaux_recus, up.updated_at = json.dumps(sorted(received)), now()
    db.commit()
    return upload_out(up)


def _check_content(category: str, path: Path) -> str | None:
    head = path.open("rb").read(16)
    if category in ("audio", "video"):
        try:
            info = processing.probe(path) if processing.available() else None
        except processing.ProcessingError as exc:
            raise ApiError(422, "INVALID_FILE", str(exc)) from None
        if info is not None and not (info["has_audio"] or info["has_video"]):
            raise ApiError(422, "INVALID_FILE", "Ce fichier ne contient ni audio ni vidéo")
        return None
    if category == "image":
        from PIL import Image, UnidentifiedImageError

        try:
            with Image.open(path) as img:
                img.verify()
        except (UnidentifiedImageError, OSError, SyntaxError):
            raise ApiError(422, "INVALID_FILE", "Ce fichier n'est pas une image valide") from None
        return None
    ext = path.suffix.lower().lstrip(".")
    if (ext == "pdf" and not head.startswith(b"%PDF-")) or (ext in ("docx", "xlsx", "pptx") and not head.startswith(b"PK")):
        raise ApiError(422, "INVALID_FILE", "Le contenu du fichier ne correspond pas à son extension")
    return None


def complete_upload(db: Session, up: MediaUpload, admin: User, data, client: ClientInfo) -> Media:
    info = upload_out(up)
    if not info["complet"]:
        raise ApiError(409, "UPLOAD_INCOMPLETE", "Des morceaux manquent", {"morceaux_recus": info["morceaux_recus"]})
    ext = up.nom_fichier.rsplit(".", 1)[-1].lower()
    relative = f"{FOLDERS[up.type]}/{now():%Y%m%d%H%M%S}_{secrets.token_hex(8)}.{ext}"
    target = processing.absolute(relative)
    target.parent.mkdir(parents=True, exist_ok=True)
    temp = _temp_dir(up.id)
    with target.open("wb") as out:
        for i in range(info["morceaux_total"]):
            out.write((temp / f"{i:06d}.part").read_bytes())
    try:
        _check_content(up.type, target)
    except Exception:
        target.unlink(missing_ok=True)
        raise
    for part in temp.glob("*.part"):
        part.unlink()
    temp.rmdir()
    db_type = {"fichier": "document"}.get(up.type, up.type)
    m = Media(uuid=str(uuid.uuid4()), titre=data.titre.strip(), slug=_unique_slug(db, data.titre), type=db_type,
              fichier=relative, description=data.description, date_media=data.date, credits=data.credits,
              taille=up.taille, mime_type=_mime(ext), est_actif=data.publie, is_for_website=data.site_web,
              is_for_whatsapp=data.whatsapp, telechargements=0,
              traitement="en_attente" if db_type in ("audio", "video") else "aucun", created_at=now(), updated_at=now())
    db.add(m)
    up.statut, up.updated_at = "termine", now()
    db.flush()
    audit.activity(db, admin.id, "media_uploaded", client, m.titre, module="medias", item_id=m.id, item_name=m.titre)
    db.commit()
    return m


def _mime(ext: str) -> str:
    return {"mp3": "audio/mpeg", "m4a": "audio/mp4", "aac": "audio/aac", "opus": "audio/ogg", "ogg": "audio/ogg",
            "wav": "audio/wav", "flac": "audio/flac", "webm": "video/webm", "mp4": "video/mp4", "mov": "video/quicktime",
            "mkv": "video/x-matroska", "avi": "video/x-msvideo", "jpg": "image/jpeg", "jpeg": "image/jpeg",
            "png": "image/png", "webp": "image/webp", "gif": "image/gif", "pdf": "application/pdf",
            "txt": "text/plain"}.get(ext, "application/octet-stream")


def _unique_slug(db: Session, title: str, exclude_id: int | None = None) -> str:
    base = slugify(title)[:200] or "media"
    slug, n = base, 2
    while db.scalar(select(Media.id).where(Media.slug == slug, Media.id != (exclude_id or 0))):
        slug, n = f"{base}-{n}", n + 1
    return slug


def add_link(db: Session, admin: User, data, client: ClientInfo) -> Media:
    if not data.url.startswith("https://"):
        raise ApiError(422, "VALIDATION_ERROR", "Le lien doit commencer par https://")
    m = Media(uuid=str(uuid.uuid4()), titre=data.titre.strip(), slug=_unique_slug(db, data.titre), type="link",
              lien=data.url, description=data.description, credits=data.credits, est_actif=data.publie,
              is_for_website=data.site_web, is_for_whatsapp=data.whatsapp, telechargements=0, traitement="aucun",
              created_at=now(), updated_at=now())
    db.add(m)
    db.flush()
    audit.activity(db, admin.id, "media_link_added", client, m.titre, module="medias", item_id=m.id, item_name=m.titre)
    db.commit()
    return m


# ───────────────────────── administration : gestion ─────────────────────────

def get_admin(db: Session, media_uuid: str) -> Media:
    m = db.scalar(select(Media).where(Media.uuid == media_uuid))
    if m is None:
        raise ApiError(404, "NOT_FOUND", "Média introuvable")
    return m


def admin_out(db: Session, m: Media) -> dict:
    data = media_out(m, _stats_for(db, [m.id]).get(m.id), detail=True)
    plays = db.execute(select(func.count(), func.sum(MediaPlay.duration_played)).where(MediaPlay.id_media == m.id)).one()
    data.update({"publie": m.est_actif, "site_web": m.is_for_website, "whatsapp": m.is_for_whatsapp,
                 "traitement": m.traitement, "traitement_erreur": m.traitement_erreur, "fichier": file_url(m.fichier),
                 "ecoutes": plays[0] or 0, "temps_ecoute_secondes": int(plays[1] or 0)})
    return data


def update_media(db: Session, m: Media, changes: dict) -> Media:
    mapping = {"titre": "titre", "description": "description", "credits": "credits", "date": "date_media",
               "publie": "est_actif", "site_web": "is_for_website", "whatsapp": "is_for_whatsapp"}
    if changes.get("titre"):
        changes["titre"] = changes["titre"].strip()
        m.slug = _unique_slug(db, changes["titre"], m.id)
    for key, value in changes.items():
        setattr(m, mapping[key], value)
    m.updated_at = now()
    db.commit()
    return m


def delete_media(db: Session, admin: User, m: Media, client: ClientInfo) -> None:
    files = [m.fichier, m.version_legere, m.miniature]
    audit.activity(db, admin.id, "media_deleted", client, m.titre, module="medias", item_id=m.id, item_name=m.titre)
    for model in (MediaView, MediaLike, MediaRating, MediaComment, MediaDownload, MediaPlay, MediaFavorite):
        db.execute(delete(model).where(model.id_media == m.id))
    db.delete(m)
    db.commit()
    for relative in files:
        if relative and not relative.startswith("http"):
            try:
                processing.absolute(relative).unlink(missing_ok=True)
            except processing.ProcessingError:
                pass


def moderation_query(statut: str):
    value = {"en_attente": 0, "publie": 1, "refuse": -1}[statut]
    return select(MediaComment).where(MediaComment.is_approved == value).order_by(MediaComment.created_at.desc())


def moderate(db: Session, comment_id: int, admin: User, approve: bool) -> MediaComment:
    c = db.get(MediaComment, comment_id)
    if c is None:
        raise ApiError(404, "NOT_FOUND", "Commentaire introuvable")
    c.is_approved, c.moderated_by, c.moderated_at = (1 if approve else -1), admin.id, now()
    db.commit()
    return c


def stats(db: Session) -> dict:
    since = now() - timedelta(days=30)
    top = db.execute(select(Media.titre, Media.slug, func.count(MediaView.id)).join(MediaView, MediaView.id_media == Media.id)
                     .where(MediaView.viewed_at >= since).group_by(Media.id).order_by(func.count(MediaView.id).desc())
                     .limit(10)).all()
    by_type = dict(db.execute(select(Media.type, func.count()).group_by(Media.type)).all())
    return {"medias": {c: sum(by_type.get(t, 0) for t in types) for c, types in CATEGORIES.items()},
            "vues_30_jours": db.scalar(select(func.count()).select_from(MediaView).where(MediaView.viewed_at >= since)) or 0,
            "heures_ecoute_30_jours": round((db.scalar(select(func.sum(MediaPlay.duration_played))
                                                       .where(MediaPlay.played_at >= since)) or 0) / 3600, 1),
            "telechargements": int(db.scalar(select(func.sum(Media.telechargements))) or 0),
            "commentaires_en_attente": db.scalar(select(func.count()).select_from(MediaComment)
                                                 .where(MediaComment.is_approved == 0)) or 0,
            "traitements_en_attente": db.scalar(select(func.count()).select_from(Media)
                                                .where(Media.traitement.in_(("en_attente", "erreur")))) or 0,
            "top_30_jours": [{"titre": t, "slug": s, "vues": n} for t, s, n in top]}
