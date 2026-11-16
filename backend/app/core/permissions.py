"""Droits d'accès dynamiques.

Chaque rôle stocke dans `roles.permissions` une liste JSON de clés « module.action ».
L'administrateur peut créer des rôles et cocher, module par module (une entrée de la barre
latérale), les actions autorisées : voir, ajouter, modifier, supprimer, exporter, activer.
Le rôle admin conserve toujours tous les droits (court-circuit). Les rôles systèmes medecin
et user n'ont aucun droit d'administration par défaut.

Les routeurs existants continuent d'appeler `require_permission("users.write")`,
`require_permission("shop.manage")`, etc. Le vérificateur traduit le chemin de la requête en
module et la méthode HTTP en action, ce qui permet d'accorder un accès en lecture seule.
"""

import json
from collections.abc import Callable

from fastapi import Depends, Request

from app.core.deps import get_current_user
from app.core.responses import ApiError
from app.models import User

ADMIN_SLUG = "admin"
SYSTEM_ROLES = {"admin", "medecin", "user"}
ADMIN_LEVEL = 100

# ───────────────────────── catalogue ─────────────────────────

ACTIONS: dict[str, str] = {
    "read": "Voir / lire",
    "create": "Ajouter",
    "update": "Modifier",
    "delete": "Supprimer",
    "export": "Exporter",
    "activate": "Activer / désactiver",
}

ALL_ACTIONS = tuple(ACTIONS)

# Un module = une entrée de la barre latérale. `actions` liste les actions utiles au module.
MODULES: list[dict] = [
    {"key": "users", "label": "Utilisateurs", "icon": "bi-people",
     "actions": ["read", "create", "update", "delete", "export", "activate"]},
    {"key": "roles", "label": "Rôles", "icon": "bi-person-badge",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "medecins", "label": "Médecins", "icon": "bi-heart-pulse",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "consultations", "label": "Consultations", "icon": "bi-calendar-check",
     "actions": ["read", "update", "delete"]},
    {"key": "modes_paiement", "label": "Comptes de paiement", "icon": "bi-cash-coin",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "produits", "label": "Produits", "icon": "bi-box-seam",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "categories", "label": "Catégories", "icon": "bi-tags",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "commandes", "label": "Commandes", "icon": "bi-cart",
     "actions": ["read", "update", "export"]},
    {"key": "pages", "label": "Pages", "icon": "bi-file-text",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "actualites", "label": "Actualités", "icon": "bi-newspaper",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "faq", "label": "FAQ", "icon": "bi-question-circle",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "temoignages", "label": "Témoignages", "icon": "bi-chat-quote",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "partenaires", "label": "Partenaires", "icon": "bi-building",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "chiffres_cles", "label": "Chiffres clés", "icon": "bi-bar-chart",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "reseaux_sociaux", "label": "Réseaux sociaux", "icon": "bi-share",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "visites", "label": "Visites", "icon": "bi-eye",
     "actions": ["read", "export"]},
    {"key": "parametres", "label": "Paramètres", "icon": "bi-gear",
     "actions": ["read", "update"]},
    {"key": "messages", "label": "Messages", "icon": "bi-envelope",
     "actions": ["read", "update", "delete"]},
    {"key": "newsletter", "label": "Newsletter", "icon": "bi-send",
     "actions": ["read", "delete", "export"]},
    {"key": "investisseurs", "label": "Investisseurs", "icon": "bi-graph-up-arrow",
     "actions": ["read", "update", "export"]},
    {"key": "courtiers", "label": "Courtiers", "icon": "bi-handshake",
     "actions": ["read", "update"]},
    {"key": "phases", "label": "Phases", "icon": "bi-diagram-3",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "data_room", "label": "Data room", "icon": "bi-folder-lock",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "medias", "label": "Médias", "icon": "bi-collection-play",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "collections", "label": "Collections", "icon": "bi-collection",
     "actions": ["read", "create", "update", "delete"]},
    {"key": "commentaires", "label": "Commentaires", "icon": "bi-chat-left-text",
     "actions": ["read", "update", "delete"]},
    {"key": "chatbot", "label": "Chatbot", "icon": "bi-robot",
     "actions": ["read", "update"]},
    {"key": "journal", "label": "Journal", "icon": "bi-journal-text",
     "actions": ["read", "export"]},
]

GRANULAR: dict[str, str] = {
    f"{m['key']}.{a}": f"{m['label']} — {ACTIONS[a]}"
    for m in MODULES for a in m["actions"]
}

# Clés historiques encore présentes dans les routeurs. Elles sont traduites en module + action.
LEGACY_PERMISSIONS: dict[str, str] = {
    "users.write": "Gérer les utilisateurs",
    "roles.manage": "Gérer les rôles",
    "medecins.manage": "Gérer les médecins",
    "consultations.manage": "Gérer les consultations",
    "chatbot.manage": "Gérer le chatbot",
    "investors.manage": "Gérer investisseurs, courtiers, phases et data room",
    "shop.manage": "Gérer les produits, catégories et commandes",
    "media.manage": "Gérer la médiathèque : envois, publication, modération, collections",
    "content.manage": "Gérer le contenu du site : pages, actualités, FAQ, paramètres, messages, newsletter",
}

# Catalogue complet (clés granulaires + historiques) : sert au contrôle des clés inconnues.
PERMISSIONS: dict[str, str] = {**GRANULAR, **LEGACY_PERMISSIONS}

# Droits par défaut quand `roles.permissions` est vide.
ROLE_PERMISSIONS: dict[str, set[str]] = {
    "medecin": set(),  # ses droits passent par l'espace /medecin/*
    "user": set(),
}

# ───────────────────────── module associé à un chemin ─────────────────────────

_ROOT_MODULES = {
    "users": "users",
    "roles": "roles",
    "medecins": "medecins",
    "consultations": "consultations",
    "modes-paiement": "modes_paiement",
    "produits": "produits",
    "categories": "categories",
    "commandes": "commandes",
    "investisseurs": "investisseurs",
    "courtiers": "courtiers",
    "data-room": "data_room",
    "chatbot": "chatbot",
    "journal": "journal",
}

_CONTENT_MODULES = {
    "pages": "pages", "sections": "pages", "actualites": "actualites", "faq": "faq",
    "parametres": "parametres", "messages": "messages", "newsletter": "newsletter",
    "temoignages": "temoignages", "partenaires": "partenaires", "chiffres-cles": "chiffres_cles",
    "reseaux-sociaux": "reseaux_sociaux", "visites": "visites",
}

_MEDIA_MODULES = {"collections": "collections", "commentaires": "commentaires", "envois": "medias"}

MODULE_ACTIONS: dict[str, tuple[str, ...]] = {m["key"]: tuple(m["actions"]) for m in MODULES}


def module_for_path(path: str) -> str | None:
    """Traduit /api/v1/admin/contenu/faq/12 en « faq », /api/v1/admin/users en « users »…"""
    segments = [s for s in path.split("/") if s]
    if "admin" not in segments:
        return None
    rest = segments[segments.index("admin") + 1:]
    if not rest:
        return None
    first = rest[0]
    if first == "contenu":
        return _CONTENT_MODULES.get(rest[1]) if len(rest) > 1 else None
    if first == "medias":
        return _MEDIA_MODULES.get(rest[1], "medias") if len(rest) > 1 else "medias"
    if first == "produits":
        return "categories" if len(rest) > 1 and rest[1] == "categories" else "produits"
    if first == "investissement":
        return "phases" if len(rest) > 1 and rest[1] == "phases" else None
    if first == "tableau-de-bord":
        return "__dashboard__"
    return _ROOT_MODULES.get(first)


# ───────────────────────── résolution des droits ─────────────────────────

def role_slug_for_type(type_utilisateur: str | None) -> str:
    return type_utilisateur if type_utilisateur in ("admin", "medecin") else "user"


def _stored_permissions(user: User) -> set[str] | None:
    raw = getattr(user.role, "permissions", None)
    if not raw:
        return None
    try:
        data = json.loads(raw)
    except (TypeError, ValueError):
        return None
    return {str(item) for item in data} if isinstance(data, list) else None


def permissions_of(user: User) -> set[str]:
    if user.role is None:
        return set()
    if user.role.slug == ADMIN_SLUG:
        return set(PERMISSIONS)
    stored = _stored_permissions(user)
    if stored is not None:
        return stored
    return set(ROLE_PERMISSIONS.get(user.role.slug, set()))


def level_of(user: User) -> int:
    return user.role.niveau or 0


def _required_actions(suffix: str, method: str, path: str) -> set[str] | None:
    """Actions autorisées à effectuer, ou None pour « n'importe quelle action du module »."""
    if "/export" in path or path.endswith(".csv"):
        return {"export"}
    if suffix == "export":
        return {"export"}
    if suffix == "delete":
        return {"delete"}
    if suffix == "create":
        return {"create"}
    if suffix == "update":
        return {"update"}
    if suffix == "activate":
        return {"activate"}
    if suffix == "read":
        return None
    # « manage » / « write » : on déduit l'action de la méthode HTTP
    if method == "GET":
        return None
    if method == "DELETE":
        return {"delete"}
    if method in ("PUT", "PATCH"):
        return {"update"}
    # POST : activation/désactivation explicite, sinon création ou mise à jour
    last = path.rstrip("/").rsplit("/", 1)[-1]
    if last in ("activate", "deactivate"):
        return {"activate"}
    return {"create", "update"}


def _satisfied(needed: str, granted: set[str], module: str | None, method: str, path: str) -> bool:
    if needed in granted:
        return True
    if module == "__dashboard__":
        return bool(granted)  # le tableau de bord est ouvert à tout rôle disposant d'au moins un droit
    if module is None:
        return False
    actions = _required_actions(needed.rsplit(".", 1)[-1], method, path)
    if actions is None:
        return any(f"{module}.{action}" in granted for action in MODULE_ACTIONS.get(module, ALL_ACTIONS))
    return any(f"{module}.{action}" in granted for action in actions)


def require_permission(*needed: str) -> Callable:
    """Exemple : Depends(require_permission("users.write"))."""
    unknown = set(needed) - set(PERMISSIONS)
    if unknown:
        raise RuntimeError(f"Permission inconnue : {unknown}")

    def checker(request: Request, user: User = Depends(get_current_user)) -> User:
        if user.role is not None and user.role.slug == ADMIN_SLUG:
            return user
        granted = permissions_of(user)
        module = module_for_path(request.url.path)
        if not all(_satisfied(key, granted, module, request.method, request.url.path) for key in needed):
            raise ApiError(403, "FORBIDDEN", "Vous n'avez pas les droits pour cette action")
        return user

    return checker
