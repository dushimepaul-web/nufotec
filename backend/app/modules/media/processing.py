"""Traitement des médias avec ffmpeg / ffprobe, en tâche de fond juste après l'envoi (aucune tâche planifiée).

- durée, débit, fréquence, canaux (ffprobe) ;
- forme d'onde (200 valeurs de 0 à 100) pour l'affichage du lecteur ;
- version légère des audios (~64 kbit/s, mono) : 5 à 10 fois plus petite, pour économiser les données mobiles ;
- miniature des vidéos (image à 10 % de la durée)."""

import json
import logging
import shutil
import struct
import subprocess
from pathlib import Path

from app.core.config import get_settings
from app.core.helpers import now

log = logging.getLogger("nufotec.media")
TIMEOUT = 15 * 60
WAVEFORM_POINTS = 200


class ProcessingError(Exception):
    pass


def tool(name: str) -> str:
    path = shutil.which(name)
    if not path:
        raise ProcessingError(f"{name} introuvable sur le serveur (installez ffmpeg)")
    return path


def available() -> bool:
    return bool(shutil.which("ffmpeg") and shutil.which("ffprobe"))


def root() -> Path:
    """Les chemins en base sont relatifs au site PHP : « attachments/Audio/Originals/x.opus »."""
    return Path(get_settings().uploads_dir).parent


def absolute(relative: str) -> Path:
    path = (root() / relative).resolve()
    if root().resolve() not in path.parents:
        raise ProcessingError("Chemin de fichier invalide")
    return path


def probe(path: Path) -> dict:
    result = subprocess.run([tool("ffprobe"), "-v", "error", "-print_format", "json", "-show_format", "-show_streams",
                             str(path)], capture_output=True, text=True, timeout=120)
    if result.returncode != 0:
        raise ProcessingError("Fichier illisible : ce n'est pas un audio ou une vidéo valide")
    data = json.loads(result.stdout or "{}")
    streams = data.get("streams", [])
    audio = next((s for s in streams if s.get("codec_type") == "audio"), None)
    video = next((s for s in streams if s.get("codec_type") == "video" and s.get("disposition", {}).get("attached_pic") != 1), None)
    fmt = data.get("format", {})
    return {"duree": int(float(fmt.get("duration") or 0)) or None, "bitrate": int(fmt.get("bit_rate") or 0) or None,
            "sample_rate": int(audio["sample_rate"]) if audio and audio.get("sample_rate") else None,
            "channels": audio.get("channels") if audio else None, "has_audio": audio is not None,
            "has_video": video is not None}


def waveform(path: Path, duration: int | None) -> list[int]:
    """Lit l'audio en PCM 8 kHz mono et calcule le pic de chaque tranche."""
    result = subprocess.run([tool("ffmpeg"), "-v", "error", "-i", str(path), "-ac", "1", "-ar", "8000", "-f", "s16le", "-"],
                            capture_output=True, timeout=TIMEOUT)
    raw = result.stdout
    count = len(raw) // 2
    if count == 0:
        return []
    samples = struct.unpack(f"<{count}h", raw[: count * 2])
    size = max(count // WAVEFORM_POINTS, 1)
    peaks = [max((abs(s) for s in samples[i:i + size]), default=0) for i in range(0, count, size)][:WAVEFORM_POINTS]
    top = max(peaks) or 1
    return [round(p * 100 / top) for p in peaks]


def light_audio(source: Path, relative_source: str) -> str:
    target_rel = str(Path(relative_source).with_name(Path(relative_source).stem + "_leger.m4a")).replace("\\", "/")
    target_rel = target_rel.replace("/Originals/", "/Light/") if "/Originals/" in target_rel else target_rel
    target = absolute(target_rel)
    target.parent.mkdir(parents=True, exist_ok=True)
    result = subprocess.run([tool("ffmpeg"), "-v", "error", "-y", "-i", str(source), "-vn", "-ac", "1", "-c:a", "aac",
                             "-b:a", "64k", "-movflags", "+faststart", str(target)], capture_output=True, timeout=TIMEOUT)
    if result.returncode != 0 or not target.exists():
        raise ProcessingError("Échec de la création de la version légère")
    return target_rel


def video_thumbnail(source: Path, relative_source: str, duration: int | None) -> str:
    target_rel = "attachments/Video/Thumbnails/" + Path(relative_source).stem + ".jpg"
    target = absolute(target_rel)
    target.parent.mkdir(parents=True, exist_ok=True)
    at = str(max((duration or 0) // 10, 1))
    subprocess.run([tool("ffmpeg"), "-v", "error", "-y", "-ss", at, "-i", str(source), "-frames:v", "1", "-vf",
                    "scale='min(1280,iw)':-2", str(target)], capture_output=True, timeout=300)
    if not target.exists():
        raise ProcessingError("Échec de la création de la miniature")
    return target_rel


def process(media_id: int) -> None:
    """Traite un média (à lancer en tâche de fond). L'état est visible dans l'admin (traitement / erreur)."""
    from app.core.database import background_session
    from app.models import Media

    db = background_session()
    try:
        m = db.get(Media, media_id)
        if m is None or not m.fichier or m.type not in ("audio", "video"):
            return
        m.traitement, m.traitement_erreur = "en_cours", None
        db.commit()
        try:
            source = absolute(m.fichier)
            info = probe(source)
            m.duree, m.bitrate, m.sample_rate, m.channels = info["duree"], info["bitrate"], info["sample_rate"], info["channels"]
            if info["has_audio"]:
                m.waveform_data = json.dumps(waveform(source, info["duree"]))
            if m.type == "audio":
                m.version_legere = light_audio(source, m.fichier)
            elif info["has_video"] and not m.miniature:
                m.miniature = video_thumbnail(source, m.fichier, info["duree"])
            m.traitement, m.updated_at = "pret", now()
            db.commit()
            log.info("Média %s traité", media_id)
        except (ProcessingError, subprocess.TimeoutExpired, OSError, ValueError) as exc:
            db.rollback()
            m = db.get(Media, media_id)
            m.traitement = "erreur"
            m.traitement_erreur = str(exc)[:500] if isinstance(exc, ProcessingError) else f"{type(exc).__name__}"
            db.commit()
            log.warning("Échec du traitement du média %s : %s", media_id, m.traitement_erreur)
    finally:
        db.close()
