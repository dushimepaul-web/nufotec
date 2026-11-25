"""Sauvegarde de la base nufotec_db (et, en option, des documents privés), avec rotation.

    .venv\\Scripts\\python -m tools.backup_db                       # base seule, garde 14 jours
    .venv\\Scripts\\python -m tools.backup_db --prive --jours 30    # + documents médicaux (PRIVATE_DIR)

Crée dans BACKUP_DIR (par défaut backend/backups, ignoré par git) :
  nufotec_db_AAAAMMJJ_HHMMSS.sql.gz   vidage complet (mysqldump --single-transaction : le site reste en ligne)
  prive_AAAAMMJJ_HHMMSS.zip           documents privés (avec --prive)
puis supprime les sauvegardes plus anciennes que --jours.

À planifier chaque nuit (Planificateur de tâches Windows ou cron), et à COPIER AILLEURS (autre disque, autre
machine) : une sauvegarde restée sur le même serveur ne protège pas d'une panne de ce serveur.
Restauration : gunzip -c fichier.sql.gz | mysql --default-character-set=utf8mb4 nufotec_db
Le mot de passe MySQL passe par la variable MYSQL_PWD, jamais sur la ligne de commande (visible des autres)."""

import argparse
import gzip
import os
import shutil
import subprocess
import sys
import time
import zipfile
from datetime import datetime
from pathlib import Path

from sqlalchemy.engine import make_url

from app.core.config import get_settings

ROOT = Path(__file__).resolve().parent.parent


def _mysqldump() -> str:
    found = shutil.which("mysqldump")
    if found:
        return found
    candidates = sorted(Path(r"C:\wamp64\bin\mysql").glob("mysql*/bin/mysqldump.exe"))  # WAMP
    if candidates:
        return str(candidates[-1])
    raise SystemExit("mysqldump introuvable : ajoutez le dossier bin de MySQL au PATH")


def backup_database(target: Path, stamp: str) -> Path:
    url = make_url(get_settings().database_url)
    out = target / f"{url.database}_{stamp}.sql.gz"
    command = [_mysqldump(), "--single-transaction", "--routines", "--triggers", "--events",
               "--default-character-set=utf8mb4", f"--host={url.host or '127.0.0.1'}",
               f"--port={url.port or 3306}", f"--user={url.username or 'root'}", url.database]
    env = {**os.environ, "MYSQL_PWD": url.password or ""}
    partial = out.with_suffix(".partial")
    with subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, env=env) as proc, \
            gzip.open(partial, "wb", compresslevel=6) as gz:
        shutil.copyfileobj(proc.stdout, gz, 1024 * 1024)
        errors = proc.stderr.read().decode("utf-8", "replace").strip()
    if proc.returncode != 0:
        partial.unlink(missing_ok=True)
        raise SystemExit(f"mysqldump a échoué : {errors}")
    partial.rename(out)  # le fichier final n'existe que si le vidage est complet
    return out


def backup_private(target: Path, stamp: str) -> Path | None:
    source = Path(get_settings().private_dir)
    if not source.is_dir():
        return None
    out = target / f"prive_{stamp}.zip"
    with zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as archive:
        for path in source.rglob("*"):
            if path.is_file():
                archive.write(path, path.relative_to(source))
    return out


def rotate(target: Path, days: int) -> int:
    limit = time.time() - days * 86400
    removed = 0
    for path in target.glob("*"):
        if path.suffix in (".gz", ".zip") and path.stat().st_mtime < limit:
            path.unlink()
            removed += 1
    return removed


def main() -> None:
    parser = argparse.ArgumentParser(description="Sauvegarde de nufotec_db")
    parser.add_argument("--dossier", default=os.environ.get("BACKUP_DIR", str(ROOT / "backups")))
    parser.add_argument("--jours", type=int, default=14, help="durée de conservation (jours)")
    parser.add_argument("--prive", action="store_true", help="sauvegarder aussi les documents privés")
    args = parser.parse_args()

    target = Path(args.dossier)
    target.mkdir(parents=True, exist_ok=True)
    stamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    made = [backup_database(target, stamp)]
    if args.prive:
        made.append(backup_private(target, stamp))
    for path in filter(None, made):
        print(f"OK  {path}  ({path.stat().st_size / 1024 / 1024:.1f} Mo)")
    print(f"{rotate(target, args.jours)} ancienne(s) sauvegarde(s) supprimée(s)")


if __name__ == "__main__":
    sys.exit(main())
