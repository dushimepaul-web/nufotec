"""Police d'icônes allégée (rapidité) : génère src/styles/icons/.

    pip install fonttools brotli
    python scripts/build_icons.py        (à relancer après avoir ajouté de nouvelles icônes bi-… dans le code)

La police complète Bootstrap Icons pèse 134 Ko pour ~2 000 icônes ; le site en utilise une centaine.
On crée une petite police avec les icônes trouvées dans le code (frontend et backend) et on déclare deux
@font-face pour la même famille, chacune avec sa plage de caractères (unicode-range) :
  - la petite police pour les icônes du code : la seule téléchargée en temps normal ;
  - la police complète pour toutes les autres : téléchargée seulement si une page en affiche une
    (icône choisie dans l'administration, ou ajoutée au code sans relancer ce script).
Aucune icône ne peut donc manquer : au pire, la page télécharge la police complète, comme avant."""

import json
import re
from pathlib import Path

from fontTools import subset
from fontTools.ttLib import TTFont

ROOT = Path(__file__).resolve().parent.parent
PKG = ROOT / "node_modules" / "bootstrap-icons" / "font"
OUT = ROOT / "src" / "styles" / "icons"
SCAN = [ROOT / "src", ROOT / "index.html", ROOT / "admin.html", ROOT.parent / "backend" / "app", ROOT.parent / "backend" / "sql"]
NAME = re.compile(r"\bbi-([a-z0-9]+(?:-[a-z0-9]+)*)")
# nom d'icône écrit seul entre guillemets, ex. ['cup-straw', 'Aliments Santé'] puis className={`bi bi-${icone}`}
QUOTED = re.compile(r"['\"`]([a-z0-9]+(?:-[a-z0-9]+)*)['\"`]")
CODEPOINT = re.compile(r"content:\s*['\"]\\([0-9a-fA-F]{4,5})['\"]")


def used_codepoints(names: dict[str, int]) -> tuple[set[int], set[str]]:
    found, unknown = set(), set()
    for base in SCAN:
        files = [base] if base.is_file() else [p for p in base.rglob("*") if p.suffix in (".js", ".jsx", ".css", ".html", ".py", ".sql")]
        for path in files:
            if OUT in path.parents:
                continue
            text = path.read_text("utf-8", errors="ignore")
            for name in NAME.findall(text):
                # « bi-check-square-fill » peut être une icône ou un nom tronqué : on garde ce qui existe
                if name in names:
                    found.add(names[name])
                else:
                    unknown.add(name)
            found.update(names[name] for name in QUOTED.findall(text) if name in names)
            found.update(int(cp, 16) for cp in CODEPOINT.findall(text))
    return found, unknown


def ranges(codepoints: list[int]) -> str:
    """[0xF101, 0xF102, 0xF105] -> 'U+F101-F102, U+F105'"""
    parts, start = [], None
    for i, cp in enumerate(codepoints):
        if start is None:
            start = cp
        if i + 1 == len(codepoints) or codepoints[i + 1] != cp + 1:
            parts.append(f"U+{start:X}" if start == cp else f"U+{start:X}-{cp:X}")
            start = None
    return ", ".join(parts)


def main() -> None:
    names = json.loads((PKG / "bootstrap-icons.json").read_text("utf-8"))
    full = PKG / "fonts" / "bootstrap-icons.woff2"
    all_codepoints = sorted(TTFont(full).getBestCmap())
    used, _ = used_codepoints(names)
    used = sorted(used & set(all_codepoints))
    rest = [cp for cp in all_codepoints if cp not in set(used)]

    OUT.mkdir(parents=True, exist_ok=True)
    small = OUT / "bootstrap-icons-site.woff2"
    options = subset.Options()
    options.flavor = "woff2"
    options.layout_features = []
    font = TTFont(full)
    subsetter = subset.Subsetter(options)
    subsetter.populate(unicodes=used)
    subsetter.subset(font)
    font.flavor = "woff2"
    font.save(small)

    original_css = (PKG / "bootstrap-icons.css").read_text("utf-8")
    rules = re.sub(r"@font-face\s*{[^}]*}", "", original_css, count=1)
    full_url = "../../../node_modules/bootstrap-icons/font/fonts/bootstrap-icons.woff2"
    css = f"""/* Généré par scripts/build_icons.py : ne pas modifier à la main. */
/* {len(used)} icônes utilisées par le site : petite police, la seule téléchargée en temps normal */
@font-face {{
  font-display: block;
  font-family: "bootstrap-icons";
  src: url("./bootstrap-icons-site.woff2") format("woff2");
  unicode-range: {ranges(used)};
}}
/* Les {len(rest)} autres icônes : police complète, téléchargée seulement si une page en affiche une */
@font-face {{
  font-display: block;
  font-family: "bootstrap-icons";
  src: url("{full_url}") format("woff2");
  unicode-range: {ranges(rest)};
}}
{rules}"""
    (OUT / "bootstrap-icons.css").write_text(css, "utf-8")
    print(f"{len(used)} icônes -> {small.stat().st_size / 1024:.1f} Ko (police complète : {full.stat().st_size / 1024:.0f} Ko)")


if __name__ == "__main__":
    main()
