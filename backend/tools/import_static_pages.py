"""Importe en base les pages et sections que le site PHP garde en dur
(helpers/static_pages_helper.php et static_sections_helper.php). Idempotent : relancer ne crée pas de doublon.

    .venv\\Scripts\\python -m tools.import_static_pages [--php C:\\wamp64\\bin\\php\\php8.3.14\\php.exe]"""

import argparse
import glob
import json
import subprocess
from datetime import datetime
from pathlib import Path

from sqlalchemy import select

from app.core.database import SessionLocal
from app.core.helpers import now, slugify
from app.models import Page, PageSection
from app.modules.content.sanitize import clean_html

HELPERS = Path(r"C:\wamp64\www\nufotec-backend\helpers")
DUMP = ('define("BASEPATH", 1); require "static_pages_helper.php"; require "static_sections_helper.php"; '
        'echo json_encode(["pages" => static_pages_all(), "sections" => static_sections_all()], '
        'JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);')


def _php() -> str:
    found = sorted(glob.glob(r"C:\wamp64\bin\php\php8*\php.exe"))
    if not found:
        raise SystemExit("PHP introuvable : passez --php")
    return found[-1]


def _date(value) -> datetime:
    try:
        return datetime.fromisoformat(str(value)) if value else now()
    except ValueError:
        return now()


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--php", default=None)
    args = parser.parse_args()
    out = subprocess.run([args.php or _php(), "-d", "display_errors=0", "-r", DUMP], cwd=HELPERS, capture_output=True,
                         timeout=120)
    data = json.loads(out.stdout.decode("utf-8"))
    created_pages = created_sections = 0
    with SessionLocal() as db:
        by_legacy: dict[int, Page] = {}
        for p in data["pages"]:
            page = db.scalar(select(Page).where(Page.legacy_id == int(p["id_page"])))
            if page is None:
                page = Page(legacy_id=int(p["id_page"]), created_at=_date(p.get("date_creation")))
                db.add(page)
                created_pages += 1
            page.titre = (p.get("titre_page") or "Page").strip()[:255]
            page.slug = (p.get("slug") or slugify(page.titre))[:150]
            page.menu_ordre = int(p.get("menu_ordre") or 0)
            page.est_publiee = bool(int(p.get("est_publiee") or 0))
            page.meta_description = p.get("meta_description")
            page.meta_keywords = p.get("meta_keywords")
            page.contenu = clean_html(p.get("contenu_page"))
            page.template = p.get("template_specifique")
            page.icone = p.get("icone_menu")
            page.updated_at = _date(p.get("date_modification"))
            page.deleted_at = _date(p["deleted_at"]) if p.get("deleted_at") else None
            by_legacy[page.legacy_id] = page
        db.flush()
        for p in data["pages"]:  # sous-menus, une fois tous les identifiants connus
            parent = p.get("menu_parent_id")
            by_legacy[int(p["id_page"])].parent_id = by_legacy[int(parent)].id if parent and int(parent) in by_legacy else None
        for s in data["sections"]:
            page = by_legacy.get(int(s.get("id_page") or 0))
            if page is None:
                continue
            section = db.scalar(select(PageSection).where(PageSection.legacy_id == int(s["id_section"])))
            if section is None:
                section = PageSection(legacy_id=int(s["id_section"]), created_at=_date(s.get("created_at")))
                db.add(section)
                created_sections += 1
            section.page_id = page.id
            section.type = (s.get("type_section") or "texte").strip() or "texte"
            section.titre = s.get("titre_section")
            section.sous_titre = s.get("sous_titre")
            section.contenu = clean_html(s.get("contenu_texte"))
            section.image_url = s.get("image_url")
            section.image_droite = bool(int(s.get("image_droite") or 0))
            section.bouton_texte = s.get("bouton_texte")
            section.bouton_lien = s.get("bouton_lien")
            section.classe = s.get("custom_class")
            section.options_json = s.get("options_json") if isinstance(s.get("options_json"), str) else (
                json.dumps(s["options_json"], ensure_ascii=False) if s.get("options_json") else None)
            section.ordre = int(s.get("ordre") or 0)
            section.est_publiee = True
            section.updated_at = _date(s.get("updated_at") or s.get("created_at"))
        db.commit()
        print(f"Pages : {len(data['pages'])} lues, {created_pages} créées ; "
              f"sections : {len(data['sections'])} lues, {created_sections} créées.")


if __name__ == "__main__":
    main()
