"""Médiathèque : catégories par type, lecture en continu, vues dédoublonnées, interactions avec compte,
modération, reprise de lecture, collections, envoi par morceaux et traitement ffmpeg réel."""

import io
import json
import math
import struct
import uuid
import wave

import pytest
from sqlalchemy import select

from app.core.helpers import now
from app.core.security import hash_password
from app.models import Media, MediaComment, MediaLike, MediaView, Role, User
from app.modules.media import processing

PASSWORD = "MotDePasse123"
API = "/api/v1/medias"
ADMIN = "/api/v1/admin/medias"


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


def _user(db, slug="user", type_utilisateur="patient"):
    u = User(uuid=str(uuid.uuid4()), email=f"test-{uuid.uuid4().hex[:8]}@example.com", password=hash_password(PASSWORD),
             nom="Test", prenom="Auditeur", role_id=db.scalar(select(Role.id).where(Role.slug == slug)),
             type_utilisateur=type_utilisateur, is_active=True, created_at=now(), updated_at=now())
    db.add(u)
    db.commit()
    return u


def _login(client, u):
    return _auth(client.post("/api/v1/auth/login", json={"login": u.email, "password": PASSWORD})
                 .json()["data"]["tokens"]["access_token"])


def _wav(seconds=3, rate=8000) -> bytes:
    buf = io.BytesIO()
    with wave.open(buf, "wb") as w:
        w.setnchannels(1)
        w.setsampwidth(2)
        w.setframerate(rate)
        frames = b"".join(struct.pack("<h", int(12000 * math.sin(2 * math.pi * 440 * i / rate) * (i / (rate * seconds))))
                          for i in range(rate * seconds))
        w.writeframes(frames)
    return buf.getvalue()


@pytest.fixture
def admin_h(client, db):
    return _login(client, _user(db, "admin", "admin"))


def _upload(client, admin_h, name, raw, **meta):
    init = client.post(f"{ADMIN}/envois", headers=admin_h, json={"nom_fichier": name, "taille": len(raw)})
    assert init.status_code == 201, init.text
    up = init.json()["data"]
    size = up["taille_morceau"]
    for i in range(up["morceaux_total"]):
        r = client.put(f"{ADMIN}/envois/{up['id']}/morceaux/{i}", headers=admin_h, content=raw[i * size:(i + 1) * size])
        assert r.status_code == 200, r.text
    return client.post(f"{ADMIN}/envois/{up['id']}/terminer", headers=admin_h,
                       json={"titre": meta.pop("titre", "Enseignement test"), **meta})


@pytest.fixture
def audio(client, admin_h):
    r = _upload(client, admin_h, "lecon.wav", _wav(), titre="Leçon sur le diabète", credits="Dr Kaneza")
    assert r.status_code == 201, r.text
    return r.json()["data"]


# ───────────── catégories et bibliothèque ─────────────

def test_categories_are_the_five_types(client):
    cats = {c["categorie"]: c["medias"] for c in client.get(f"{API}/categories").json()["data"]}
    assert list(cats) == ["audio", "video", "image", "fichier", "link"]
    assert cats["audio"] >= 147 and cats["fichier"] >= 11 and cats["link"] >= 8


def test_library_filters_and_sorts(client):
    data = client.get(API, params={"categorie": "fichier", "per_page": 50}).json()["data"]
    assert data["items"] and all(m["categorie"] == "fichier" for m in data["items"])
    for tri in ("populaire", "tendance", "mieux_notes", "recent"):
        assert client.get(API, params={"tri": tri}).status_code == 200


def test_youtube_link_detected(client):
    links = client.get(API, params={"categorie": "link", "per_page": 50}).json()["data"]["items"]
    yt = next(m for m in links if m["miniature"] and "img.youtube.com" in m["miniature"])
    detail = client.get(f"{API}/{yt['slug']}").json()["data"]
    assert len(detail["youtube_id"]) == 11 and not detail["telechargeable"]


# ───────────── envoi, traitement, lecture ─────────────

@pytest.mark.skipif(not processing.available(), reason="ffmpeg absent")
def test_chunked_upload_processing_and_streaming(client, db, audio, uploads_dir):
    m = db.scalar(select(Media).where(Media.uuid == audio["uuid"]))
    db.refresh(m)
    assert m.traitement == "pret", m.traitement_erreur  # traité en tâche de fond juste après l'envoi
    assert m.duree == 3 and m.version_legere.endswith("_leger.m4a")
    assert len(json.loads(m.waveform_data)) > 50
    detail = client.get(f"{API}/{m.slug}").json()["data"]
    assert detail["forme_onde"] and detail["version_legere_disponible"] and detail["lisible"]
    # Lecture en continu avec avance rapide (Range) sur la version légère
    r = client.get(f"{API}/{m.slug}/flux", headers={"Range": "bytes=0-99"})
    assert r.status_code == 206 and len(r.content) == 100 and r.headers["content-type"] == "audio/mp4"
    full = client.get(f"{API}/{m.slug}/flux", params={"qualite": "originale"})
    assert full.status_code == 200 and full.content[:4] == b"RIFF"


def test_resume_after_missing_chunk(client, admin_h):
    raw = b"%PDF-1.4 " + b"x" * (6 * 1024 * 1024)
    up = client.post(f"{ADMIN}/envois", headers=admin_h, json={"nom_fichier": "rapport.pdf", "taille": len(raw)}).json()["data"]
    size = up["taille_morceau"]
    client.put(f"{ADMIN}/envois/{up['id']}/morceaux/0", headers=admin_h, content=raw[:size])
    r = client.post(f"{ADMIN}/envois/{up['id']}/terminer", headers=admin_h, json={"titre": "Rapport"})
    assert r.status_code == 409 and r.json()["error"]["code"] == "UPLOAD_INCOMPLETE"
    status = client.get(f"{ADMIN}/envois/{up['id']}", headers=admin_h).json()["data"]
    assert status["morceaux_recus"] == [0]  # l'app sait quoi renvoyer après une coupure
    client.put(f"{ADMIN}/envois/{up['id']}/morceaux/1", headers=admin_h, content=raw[size:])
    r = client.post(f"{ADMIN}/envois/{up['id']}/terminer", headers=admin_h, json={"titre": "Rapport"})
    assert r.status_code == 201 and r.json()["data"]["categorie"] == "fichier"


def test_fake_files_refused(client, admin_h):
    r = _upload(client, admin_h, "faux.pdf", b"<html>pas un pdf</html>")
    assert r.status_code == 422
    if processing.available():
        r = _upload(client, admin_h, "faux.mp3", b"ceci n'est pas un audio" * 100)
        assert r.status_code == 422
    r = client.post(f"{ADMIN}/envois", headers=admin_h, json={"nom_fichier": "virus.exe", "taille": 10})
    assert r.status_code == 422


def test_download_counts(client, db, audio):
    r = client.get(f"{API}/{audio['slug']}/telecharger")
    assert r.status_code == 200 and "attachment" in r.headers["content-disposition"]
    assert db.scalar(select(Media.telechargements).where(Media.uuid == audio["uuid"])) == 1



PDF = b"\n".join([b"%PDF-1.4", b"1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj",
                   b"2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj",
                   b"3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>endobj",
                   b"trailer<</Root 1 0 R>>", b"%%EOF", b""])


def test_document_preview_inline_without_counting_download(client, db, admin_h):
    r = _upload(client, admin_h, "guide.pdf", PDF, titre="Guide des plantes")
    assert r.status_code == 201, r.text
    doc = r.json()["data"]
    detail = client.get(f"{API}/{doc['slug']}").json()["data"]
    assert detail["apercu"] is True and detail["format"] == "pdf" and detail["telechargeable"] is True
    shown = client.get(f"{API}/{doc['slug']}/apercu")
    assert shown.status_code == 200 and shown.headers["content-type"] == "application/pdf"
    assert shown.headers["content-disposition"].startswith("inline")
    assert db.scalar(select(Media.telechargements).where(Media.uuid == doc["uuid"])) == 0


def test_audio_has_no_preview_and_stream_guesses_missing_mime(client, db, audio):
    assert client.get(f"{API}/{audio['slug']}/apercu").status_code == 409
    m = db.scalar(select(Media).where(Media.uuid == audio["uuid"]))
    m.mime_type = None  # fichiers importés du PHP sans type
    db.commit()
    r = client.get(f"{API}/{audio['slug']}/flux", params={"qualite": "originale"})
    assert r.status_code == 200 and r.headers["content-type"].startswith("audio/")


# ───────────── vues et écoutes ─────────────

def test_views_deduplicated_per_visitor(client, db, audio):
    assert client.post(f"{API}/{audio['slug']}/vue").json()["data"]["comptee"] is True
    assert client.post(f"{API}/{audio['slug']}/vue").json()["data"]["comptee"] is False  # même visiteur, < 30 min
    other = client.post(f"{API}/{audio['slug']}/vue", headers={"User-Agent": "AutreTelephone/1.0"})
    assert other.json()["data"]["comptee"] is True
    m = db.scalar(select(Media).where(Media.uuid == audio["uuid"]))
    assert db.query(MediaView).filter(MediaView.id_media == m.id).count() == 2


def test_resume_listening(client, db, audio):
    h = _login(client, _user(db))
    client.post(f"{API}/{audio['slug']}/ecoute", headers=h, json={"secondes_ecoutees": 1, "position": 1})
    resume = client.get(f"{API}/reprendre", headers=h).json()["data"]
    assert resume[0]["uuid"] == audio["uuid"] and resume[0]["position"] == 1
    assert client.get(f"{API}/{audio['slug']}", headers=h).json()["data"]["moi"]["position"] == 1
    client.put(f"{API}/{audio['slug']}/progression", headers=h, json={"position": 3})  # écouté jusqu'au bout
    assert client.get(f"{API}/reprendre", headers=h).json()["data"] == []


# ───────────── interactions avec compte ─────────────

def test_reactions_need_account_and_are_one_per_account(client, db, audio):
    assert client.post(f"{API}/{audio['slug']}/reaction", json={"reaction": "like"}).status_code == 401
    a, b = _login(client, _user(db)), _login(client, _user(db))
    client.post(f"{API}/{audio['slug']}/reaction", headers=a, json={"reaction": "like"})
    client.post(f"{API}/{audio['slug']}/reaction", headers=a, json={"reaction": "like"})
    stats = client.post(f"{API}/{audio['slug']}/reaction", headers=b, json={"reaction": "like"}).json()["data"]
    assert stats["likes"] == 2  # deux comptes derrière la même IP : tous deux comptés
    stats = client.post(f"{API}/{audio['slug']}/reaction", headers=a, json={"reaction": "dislike"}).json()["data"]
    assert stats["likes"] == 1 and stats["dislikes"] == 1
    m = db.scalar(select(Media).where(Media.uuid == audio["uuid"]))
    assert db.query(MediaLike).filter(MediaLike.id_media == m.id).count() == 2


def test_rating_and_favorites(client, db, audio):
    h = _login(client, _user(db))
    client.put(f"{API}/{audio['slug']}/note", headers=h, json={"note": 3})
    stats = client.put(f"{API}/{audio['slug']}/note", headers=h, json={"note": 5}).json()["data"]
    assert stats["note"] == 5.0 and stats["notes"] == 1
    client.post(f"{API}/{audio['slug']}/favori", headers=h)
    favs = client.get(f"{API}/mes-favoris", headers=h).json()["data"]["items"]
    assert [f["uuid"] for f in favs] == [audio["uuid"]]
    client.delete(f"{API}/{audio['slug']}/favori", headers=h)
    assert client.get(f"{API}/mes-favoris", headers=h).json()["data"]["items"] == []


def test_comments_are_moderated(client, db, admin_h, audio):
    h = _login(client, _user(db))
    r = client.post(f"{API}/{audio['slug']}/commentaires", headers=h, json={"texte": "Murakoze cane, iki kiganiro ni ciza !"})
    assert r.status_code == 201
    assert client.get(f"{API}/{audio['slug']}/commentaires").json()["data"]["items"] == []  # pas encore publié
    pending = client.get(f"{ADMIN}/commentaires", headers=admin_h).json()["data"]["items"]
    cid = next(c["id"] for c in pending if "Murakoze" in c["texte"])
    client.post(f"{ADMIN}/commentaires/{cid}/publier", headers=admin_h)
    published = client.get(f"{API}/{audio['slug']}/commentaires").json()["data"]["items"]
    assert published[0]["texte"].startswith("Murakoze") and published[0]["auteur"] == "Auditeur T."
    spam = client.post(f"{API}/{audio['slug']}/commentaires", headers=h, json={"texte": "Gagnez au casino https://a.b https://c.d"})
    assert spam.status_code == 422


def test_existing_comments_stay_published(db):
    assert db.query(MediaComment).filter(MediaComment.is_approved == 1).count() >= 18


# ───────────── collections ─────────────

def test_collections_ordered(client, admin_h, audio):
    second = _upload(client, admin_h, "lecon2.pdf", b"%PDF-1.4 support", titre="Support de cours").json()["data"]
    r = client.post(f"{ADMIN}/collections", headers=admin_h,
                    json={"titre": "Comprendre le diabète", "medias": [second["uuid"], audio["uuid"]]})
    assert r.status_code == 201, r.text
    public = client.get(f"{API}/collections/comprendre-le-diabete").json()["data"]
    assert [m["episode"] for m in public["medias"]] == [1, 2] and public["medias"][0]["uuid"] == second["uuid"]
    assert any(c["slug"] == "comprendre-le-diabete" for c in client.get(f"{API}/collections").json()["data"])
    r = client.post(f"{ADMIN}/collections", headers=admin_h, json={"titre": "Doublon", "medias": [audio["uuid"], audio["uuid"]]})
    assert r.status_code == 422


# ───────────── administration ─────────────

def test_hide_update_delete(client, db, admin_h, audio, uploads_dir):
    client.patch(f"{ADMIN}/{audio['uuid']}", headers=admin_h, json={"publie": False})
    assert client.get(f"{API}/{audio['slug']}").status_code == 404
    client.patch(f"{ADMIN}/{audio['uuid']}", headers=admin_h, json={"publie": True, "titre": "Diabète : les bases"})
    m = db.scalar(select(Media).where(Media.uuid == audio["uuid"]))
    db.refresh(m)
    path = processing.absolute(m.fichier)
    assert m.slug == "diabete-les-bases" and path.exists()
    client.delete(f"{ADMIN}/{audio['uuid']}", headers=admin_h)
    assert not path.exists() and db.scalar(select(Media).where(Media.uuid == audio["uuid"])) is None


def test_admin_stats_and_link(client, admin_h):
    r = client.post(f"{ADMIN}/liens", headers=admin_h, json={"titre": "Conférence", "url": "https://youtu.be/Hf1kPp7yWcI"})
    assert r.status_code == 201 and r.json()["data"]["youtube_id"] == "Hf1kPp7yWcI"
    stats = client.get(f"{ADMIN}/stats", headers=admin_h).json()["data"]
    assert stats["medias"]["audio"] >= 147 and "commentaires_en_attente" in stats


def test_admin_only(client, db):
    h = _login(client, _user(db))
    assert client.get(ADMIN, headers=h).status_code == 403
    assert client.post(f"{ADMIN}/envois", headers=h, json={"nom_fichier": "a.mp3", "taille": 10}).status_code == 403


def test_missing_light_version_falls_back_to_original(client, db, audio):
    m = db.scalar(select(Media).where(Media.uuid == audio["uuid"]))
    m.version_legere = "attachments/Audio/Light/absent_leger.m4a"  # enregistrée mais jamais générée
    db.commit()
    assert client.get(f"{API}/{audio['slug']}").json()["data"]["version_legere_disponible"] is False
    r = client.get(f"{API}/{audio['slug']}/flux", params={"qualite": "legere"})
    assert r.status_code == 200 and r.headers["content-type"].startswith("audio/")


def test_only_preview_can_be_embedded_in_a_page(client, admin_h):
    doc = _upload(client, admin_h, "fiche.pdf", PDF, titre="Fiche").json()["data"]
    shown = client.get(f"{API}/{doc['slug']}/apercu")
    assert "frame-ancestors" in shown.headers["content-security-policy"] and shown.headers["x-frame-options"] == "SAMEORIGIN"
    assert client.get(f"{API}/{doc['slug']}").headers["x-frame-options"] == "DENY"
