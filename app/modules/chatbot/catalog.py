"""Catalogue des tables de la base, et garde-fous sur ce que l'IA peut voir.

L'admin choisit les tables et les colonnes. Certaines restent interdites quoi qu'il arrive :
secrets (mots de passe, jetons, clés), sessions, journaux, données médicales et paiements."""

import json
import re

from sqlalchemy import text
from sqlalchemy.orm import Session

from app.core.responses import ApiError

# Tables jamais accessibles à l'IA
BLOCKED_TABLES = {
    # comptes et sécurité
    "password_resets", "user_sessions", "idempotency_keys", "push_devices", "email_outbox", "logs",
    "user_activities", "visitors_logs", "statistiques_visites",
    # IA elle-même
    "ai_providers", "ai_usage", "chatbot_settings", "rag_sources", "rag_table_rules", "rag_documents", "rag_chunks",
    "chatbot_conversations", "chatbot_messages", "chatbot_handoff_requests", "chatbot_feedback",
    # santé (secret médical) et argent
    "consultations", "consultation_documents", "consultation_history", "chatbot_patient_documents",
    "chatbot_consultation_data", "miniatures_documents", "paiements", "order_requests",
    # ancien nom de « parametres » (vue de compatibilité pour le PHP) : jamais lisible par l'IA
    "configurations",
}
# Colonnes jamais accessibles (dans n'importe quelle table)
BLOCKED_COLUMN = re.compile(r"(pass(word|wd)?|token|secret|api_?key|two_factor|hash|otp|salt|"
                            r"smtp|session|signature|credential)", re.I)
# Colonnes personnelles : décochées par défaut (l'admin peut les autoriser)
SENSITIVE_COLUMN = re.compile(r"(e_?mail|telephone|phone|whatsapp|ip_address|date_naissance|adresse|address|"
                              r"numero_compte|preuve|room|last_login|uuid|registre)", re.I)
# Filtres imposés : lignes qui ne doivent jamais sortir
FORCED_FILTERS = {
    "parametres": "`cle` NOT REGEXP '(pass|key|token|secret|smtp)'",
    "users": "`deleted_at` IS NULL",
}
OPS = {"=", "!=", "<", ">", "<=", ">=", "like", "is_null", "not_null", "in"}
_IDENT = re.compile(r"^[A-Za-z0-9_]{1,64}$")


def quote(identifier: str) -> str:
    if not _IDENT.match(identifier):
        raise ApiError(422, "INVALID_IDENTIFIER", f"Nom invalide : {identifier}")
    return f"`{identifier}`"


def table_columns(db: Session, table: str) -> list[dict]:
    rows = db.execute(text(
        "SELECT column_name AS name, data_type AS type, column_key AS ckey FROM information_schema.columns "
        "WHERE table_schema = DATABASE() AND table_name = :t ORDER BY ordinal_position"), {"t": table}).mappings()
    return [{"name": r["name"], "type": r["type"], "primary": r["ckey"] == "PRI",
             "blocked": bool(BLOCKED_COLUMN.search(r["name"])),
             "sensitive": bool(SENSITIVE_COLUMN.search(r["name"]))} for r in rows]


def list_tables(db: Session) -> list[dict]:
    rows = db.execute(text(
        "SELECT table_name AS name, table_rows AS approx_rows FROM information_schema.tables "
        "WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE' ORDER BY table_name")).mappings()
    out = []
    for r in rows:
        name = r["name"]
        out.append({"table": name, "lignes_approx": int(r["approx_rows"] or 0), "bloquee": name in BLOCKED_TABLES,
                    "colonnes": table_columns(db, name)})
    return out


def primary_key(columns: list[dict]) -> str | None:
    return next((c["name"] for c in columns if c["primary"]), None)


def validate_rule(db: Session, table: str, columns: list[str], filters: list[dict], template: str | None,
                  title_column: str | None) -> list[dict]:
    """Vérifie une règle ; renvoie les colonnes réelles de la table."""
    if table in BLOCKED_TABLES:
        raise ApiError(403, "TABLE_BLOCKED", f"La table « {table} » est protégée et ne peut pas être donnée à l'IA")
    real = table_columns(db, table)
    if not real:
        raise ApiError(404, "NOT_FOUND", f"Table « {table} » introuvable")
    by_name = {c["name"]: c for c in real}
    if not columns:
        raise ApiError(422, "VALIDATION_ERROR", "Choisissez au moins une colonne")
    for col in columns + ([title_column] if title_column else []):
        if col not in by_name:
            raise ApiError(422, "VALIDATION_ERROR", f"Colonne inconnue : {col}")
        if by_name[col]["blocked"]:
            raise ApiError(403, "COLUMN_BLOCKED", f"La colonne « {col} » est protégée (secret)")
    for f in filters:
        if f.get("column") not in by_name or by_name[f["column"]]["blocked"]:
            raise ApiError(422, "VALIDATION_ERROR", f"Filtre sur une colonne invalide : {f.get('column')}")
        if f.get("op") not in OPS:
            raise ApiError(422, "VALIDATION_ERROR", f"Opérateur invalide : {f.get('op')}")
    if template:
        unknown = set(re.findall(r"\{(\w+)\}", template)) - set(columns)
        if unknown:
            raise ApiError(422, "VALIDATION_ERROR",
                           f"Le modèle de texte utilise des colonnes non autorisées : {', '.join(sorted(unknown))}")
    return real


def build_select(table: str, columns: list[str], filters: list[dict], pk: str | None, limit: int):
    """Requête paramétrée : noms validés et entre accents graves, valeurs jamais concaténées."""
    cols = list(dict.fromkeys(([pk] if pk else []) + columns))
    where, params = [], {}
    for i, f in enumerate(filters):
        col, op = quote(f["column"]), f["op"]
        if op == "is_null":
            where.append(f"{col} IS NULL")
        elif op == "not_null":
            where.append(f"{col} IS NOT NULL")
        elif op == "in":
            values = f.get("value") if isinstance(f.get("value"), list) else [f.get("value")]
            names = [f"p{i}_{j}" for j in range(len(values))]
            where.append(f"{col} IN ({', '.join(':' + n for n in names)})")
            params.update(dict(zip(names, values, strict=True)))
        else:
            where.append(f"{col} {'LIKE' if op == 'like' else op} :p{i}")
            params[f"p{i}"] = f.get("value")
    if table in FORCED_FILTERS:
        where.append(FORCED_FILTERS[table])
    sql = f"SELECT {', '.join(quote(c) for c in cols)} FROM {quote(table)}"
    if where:
        sql += " WHERE " + " AND ".join(where)
    if pk:
        sql += f" ORDER BY {quote(pk)}"
    sql += f" LIMIT {int(limit)}"
    return text(sql), params


def render_row(row: dict, columns: list[str], template: str | None) -> str:
    def value(v) -> str:
        return "" if v is None else str(v).strip()

    if template:
        return re.sub(r"\{(\w+)\}", lambda m: value(row.get(m.group(1))), template).strip()
    return " ; ".join(f"{c} : {value(row.get(c))}" for c in columns if value(row.get(c)))


def loads(raw: str | None, default):
    try:
        return json.loads(raw) if raw else default
    except ValueError:
        return default
