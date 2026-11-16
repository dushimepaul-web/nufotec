"""Convertit les pages statiques PHP (src/modules/pages/*_View.php) en pages React, à l'identique :
même texte, même mise en page, mêmes styles (déjà préfixés par page). Seules adaptations :
- liens PHP (base_url) -> adresses du nouveau site ;
- en-tête / pied de page PHP retirés (le site a les siens) ;
- dégradés remplacés par une couleur unie et couleurs ramenées à celles du logo (préférences validées).

Usage : python scripts/convert_static_pages.py   (régénère src/static-pages/)
"""
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SRC = ROOT / "src" / "modules" / "pages"
OUT = ROOT / "src" / "static-pages"

# fichier PHP -> (clé, adresse) ; adresses = celles de src/lib/routes.js (PAGE)
PAGES = {
    "Background_Strategic_Rationale_View.php": ("strategic", "/background-strategic-rationale"),
    "Corporate_Structure_Governance_View.php": ("governance", "/corporate-structure-governance"),
    "Vision_Mission_View.php": ("vision", "/vision-mission"),
    "Esg_Sustainability_View.php": ("esg", "/esg-sustainability"),
    "Risk_Analysis_Mitigation_Strategies_View.php": ("risk", "/risk-analysis"),
    "Research_Innovation_View.php": ("innovation", "/research-innovation"),
    "Market_Industry_Outlook_View.php": ("market", "/market-outlook"),
    "NUFOTEC_PHYTOMED_INDUSTRIES_Facility_View.php": ("facility", "/nufotec-phytomed-industries-facility"),
    "Market_Expansion_Platform_View.php": ("digital", "/digital-growth"),
    "Phased_Investment_Projection_View.php": ("projection", "/investment-projection"),
    "Our_Investor_Partner_Commitment_View.php": ("commitment", "/investor-commitment"),
    "Strategic_Partnerships_View.php": ("partnerships", "/strategic-partnerships"),
    "Commission_Fee_Payment_to_Brokers_View.php": ("commission", "/broker-commission"),
}

# Titres dont l'ornement « { » du design PHP tenait lieu de « & » ou de trait d'union
TITLES = {
    "innovation": "Recherche & Innovation", "market": "Aperçu du marché & du secteur",
    "facility": "Installation NUFOTEC-PHYTOMED INDUSTRIES", "digital": "Plateforme de croissance digitale & d'expansion commerciale",
    "commitment": "Notre engagement envers les investisseurs & partenaires",
}

# base_url('...') du PHP -> adresse du nouveau site
LINKS = {
    "": "/", "corporate": "/", "About/presentation": "/presentation", "Home/Contact": "/contact",
    "Products": "/produits", "investor": "/investisseur", "Investors": "/investisseur", "broker": "/courtier",
}

# couleurs du PHP -> couleurs du logo (theme-logo.css)
COLORS = {
    "#083D2A": "#053d16", "#0B5D3B": "#075a20", "#0E7A4E": "#0b8a30", "#D4A017": "#c9a646",
    "#B8860B": "#9a7a22", "#E0B032": "#d8b85a", "#F3D98B": "#e2cd7e",
}


# Icônes FontAwesome (non chargé par le site) -> Bootstrap Icons
FA_TO_BI = {
    "angle-right": "chevron-right", "house-chimney-medical": "hospital", "globe": "globe2", "ship": "truck",
    "ruler-combined": "rulers", "robot": "robot", "handshake": "people", "calendar-check": "calendar-check",
    "bullhorn": "megaphone", "headset": "headset", "chart-line": "graph-up-arrow", "sack-dollar": "cash-stack",
    "circle-check": "check-circle-fill", "scale-balanced": "bank", "shield-heart": "shield-check", "chart-pie": "pie-chart",
    "hand-holding-heart": "heart", "file-invoice-dollar": "receipt", "flask-vial": "capsule", "user-doctor": "person-badge",
    "file-shield": "file-earmark-lock", "gears": "gear-wide-connected", "dna": "activity", "building-columns": "bank",
    "file-signature": "file-earmark-text", "circle-exclamation": "exclamation-circle",
}

# Habillage commun : chaque bloc reçoit une classe partagée (sp-*) stylée dans src/styles/static-page.css
SKIN = {
    "sp-card": ("card", "card--wide", "block", "callout", "pos", "action", "stat"),  # vrais blocs seulement (pas les cellules de tableau)
    "sp-note": ("note", "cool-note", "quote", "intro", "fence"),
    "sp-ico": ("ico", "card-icon", "pos-icon", "action-icon"),
    "sp-tag": ("tag", "chip", "badge", "card-label", "tagline"),
    "sp-num": ("num", "block-num", "step-n", "roman", "stat-val"),
    "sp-title": ("section-title", "block-title", "table-title", "card-title"),
    "sp-table-wrap": ("table-wrap",),
    "sp-table": ("table",),
    "sp-btn": ("btn",),
    "sp-hide": ("brace", "goldbar"),
}
SKIN_OF = {suffix: cls for cls, suffixes in SKIN.items() for suffix in suffixes}


def map_icons(html: str) -> str:
    def repl(m):
        classes = m.group(1).split()
        names = [c[3:] for c in classes if c.startswith("fa-") and c not in ("fa-solid", "fa-regular", "fa-brands")]
        if not any(c.startswith(("fa-", "fas", "far", "fab")) for c in classes) or not names:
            return m.group(0)
        if names[0] not in FA_TO_BI:
            raise SystemExit(f"icône FontAwesome sans équivalent : fa-{names[0]}")
        rest = [c for c in classes if not c.startswith(("fa-", "fas", "far", "fab"))]
        return 'class="' + " ".join(["bi", f"bi-{FA_TO_BI[names[0]]}", *rest]) + '"'
    return re.sub(r'class="([^"]*\bfa[srb]?[- ][^"]*)"', repl, html)


def add_skin(html: str) -> str:
    def repl(m):
        classes = m.group(1).split()
        extra = []
        for c in classes:
            part = c.split("-", 1)[1] if re.match(r"^[a-z]{3}-", c) else None
            if part in SKIN_OF and SKIN_OF[part] not in extra:
                extra.append(SKIN_OF[part])
        return 'class="' + " ".join(classes + extra) + '"'
    html = re.sub(r'class="([^"]+)"', repl, html)
    return re.sub(r"<table(?![^>]*class=)", '<table class="sp-table"', html)


def text_of(fragment: str) -> str:
    t = re.sub(r"<[^>]+>", " ", fragment).replace("&amp;", "&").replace("&nbsp;", " ")
    return re.sub(r"\s+", " ", t).replace(" { ", " ").replace("{", "").strip()


def block_at(html: str, index: int) -> tuple[int, int]:
    """Début et fin du <div> qui contient la position index (comptage des div ouvrants / fermants)."""
    start = html.rfind("<div", 0, index)
    depth = 0
    for m in re.finditer(r"<(/?)div\b[^>]*>", html[start:]):
        depth += -1 if m.group(1) else 1
        if depth == 0:
            return start, start + m.end()
    raise SystemExit("bloc d'en-tête non fermé")


def extract_banner(html: str) -> tuple[dict, str]:
    """En-tête PHP (…-page-head ou …-hero) -> données du bandeau du site ; retiré du contenu."""
    m = re.search(r'class="[a-z]{3}-(?:page-head|hero)\b', html)
    if not m:
        return {}, html
    a, b = block_at(html, m.start())
    head = html[a:b]
    crumb = re.findall(r"<span[^>]*>(.*?)</span>", re.search(r'class="(?:[a-z]{3}-crumb|breadcrumb-ribbon)".*?</div>', head, re.S).group(0), re.S)
    tag = re.search(r'class="[a-z]{3}-tagline[^"]*"[^>]*>(.*?)</div>', head, re.S)
    tag_icon = re.search(r'class="bi (bi-[a-z0-9-]+)', tag.group(1)) if tag else None
    lead = re.search(r'<p class="[a-z]{3}-lead[^"]*"[^>]*>(.*?)</p>', head, re.S)
    banner = {"crumb": text_of(crumb[-1]) if crumb else "", "title": text_of(re.search(r"<h1[^>]*>(.*?)</h1>", head, re.S).group(1)),
              "badge": text_of(tag.group(1)) if tag else "", "badgeIcon": tag_icon.group(1) if tag_icon else "",
              "subtitle": text_of(lead.group(1)) if lead else ""}
    return banner, html[:a] + html[b:]


# Ancien nom de la société (modèle d'origine) -> NUFOTEC BURUNDI (demande du 16/11/2026)
RENAME = [
    (r"African Green Farmers (?:Limited|Ltd\.?)\s*\((?:AGF|NUFOTEC)\)", "NUFOTEC BURUNDI"),
    (r"African Green Farmers(?: (?:Limited|Ltd\.?))?", "NUFOTEC BURUNDI"),
    (r"\bAGF-PHYTOMED\b", "NUFOTEC-PHYTOMED"),
    (r"\bAGF\b", "NUFOTEC BURUNDI"),
]


def rename_company(text: str) -> str:
    for pattern, new in RENAME:
        text = re.sub(pattern, new, text)
    return text

def first_color(args: str) -> str:
    """Première couleur d'une liste d'arguments de dégradé (#hex, rgb[a](...), nom)."""
    m = re.search(r"#[0-9a-fA-F]{3,8}\b|rgba?\([^)]*\)|hsla?\([^)]*\)|\b(?:white|black|transparent)\b", args)
    return m.group(0) if m else "transparent"


def flatten_gradients(css: str) -> str:
    out, i = [], 0
    pattern = re.compile(r"(repeating-)?(linear|radial|conic)-gradient\(")
    while True:
        m = pattern.search(css, i)
        if not m:
            out.append(css[i:])
            break
        out.append(css[i:m.start()])
        depth, j = 1, m.end()
        while depth and j < len(css):
            depth += {"(": 1, ")": -1}.get(css[j], 0)
            j += 1
        args = css[m.end():j - 1]
        # dégradé linéaire -> sa première couleur ; halos radiaux / coniques décoratifs -> retirés
        out.append(first_color(args) if m.group(2) == "linear" else "transparent")
        i = j
    return "".join(out)


def recolor(text: str) -> str:
    for old, new in COLORS.items():
        text = re.sub(re.escape(old), new, text, flags=re.I)
    return text


def convert(php: str, name: str) -> tuple[str, str, str]:
    styles = re.findall(r"<style[^>]*>(.*?)</style>", php, flags=re.S)
    css = "\n".join(styles)
    css = re.sub(r"@import[^;]+;", "", css)
    body = re.sub(r"<style[^>]*>.*?</style>", "", php, flags=re.S)
    body = re.sub(r"<\?php\s+include[^?]*\?>", "", body)
    body = re.sub(r"<link[^>]*>", "", body)
    body = re.sub(r"<script[^>]*>.*?</script>", "", body, flags=re.S)
    body = re.sub(r"<!--.*?-->", "", body, flags=re.S)

    def link(m):
        key = m.group(1) or ""
        if key not in LINKS:
            raise SystemExit(f"{name} : lien inconnu base_url('{key}')")
        return LINKS[key]

    body = re.sub(r"<\?=\s*base_url\((?:'([^']*)')?\)\s*\?>", link, body)
    if "<?" in body:
        raise SystemExit(f"{name} : code PHP restant : {body[body.index('<?'):body.index('<?') + 80]}")
    css = recolor(flatten_gradients(css))
    # colonnes de grille « 1fr » : autorisées à rétrécir (sinon un mot long fait déborder l'écran sur mobile)
    css = re.sub(r"grid-template-columns:\s*1fr\s*;", "grid-template-columns: minmax(0, 1fr);", css)
    css = re.sub(r"grid-template-columns:\s*1fr 1fr\s*;", "grid-template-columns: repeat(2, minmax(0, 1fr));", css)
    css = re.sub(r"grid-template-columns:\s*1fr 1fr 1fr\s*;", "grid-template-columns: repeat(3, minmax(0, 1fr));", css)
    body = recolor(flatten_gradients(body))  # styles en ligne
    body = re.sub(r"\n\s*\n+", "\n", body).strip()
    body = rename_company(add_skin(map_icons(body)))
    banner, body = extract_banner(body)
    title_m = re.search(r'class="[a-z]+-page-title"[^>]*>(.*?)</h1>', body, flags=re.S) or re.search(r"<h1[^>]*>(.*?)</h1>", body, flags=re.S)
    title = re.sub(r"<[^>]+>|\s+", " ", title_m.group(1)).replace("&amp;", "&").strip() if title_m else ""
    title = re.sub(r"\s+", " ", title).replace(" { ", " & ")  # « { » : ornement du design PHP
    return body, css, title, banner


def main():
    OUT.mkdir(parents=True, exist_ok=True)
    index = []
    for file, (key, path) in PAGES.items():
        php = (SRC / file).read_text(encoding="utf-8")
        body, css, title, banner = convert(php, file)
        if key in TITLES:
            banner["title"] = TITLES[key]
        title = banner.get("title") or title
        (OUT / f"{key}.css").write_text(f"/* Copie de src/modules/pages/{file} (généré par scripts/convert_static_pages.py) */\n{css.strip()}\n", encoding="utf-8")
        (OUT / f"{key}.js").write_text(
            f"// Copie de src/modules/pages/{file} : généré par scripts/convert_static_pages.py, ne pas modifier à la main.\n"
            f"import './{key}.css'\n\nexport const title = {json.dumps(title, ensure_ascii=False)}\n"
            f"export const banner = {json.dumps(banner, ensure_ascii=False)}\n"
            f"export const html = {json.dumps(body, ensure_ascii=False)}\n", encoding="utf-8")
        index.append((key, path, title))
        print(f"{key:13} {path:40} {len(body):6} car. HTML  {len(css):6} car. CSS  « {title} »")
    lines = ["// Pages statiques (copies des vues PHP) : adresse -> chargement à la demande. Généré, ne pas modifier à la main.",
             "export const STATIC_PAGES = {"]
    lines += [f"  {json.dumps(path)}: {{ key: {json.dumps(key)}, title: {json.dumps(title, ensure_ascii=False)}, load: () => import('./{key}.js') }},"
              for key, path, title in index]
    lines.append("}")
    (OUT / "index.js").write_text("\n".join(lines) + "\n", encoding="utf-8")


if __name__ == "__main__":
    main()
