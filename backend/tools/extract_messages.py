"""Relève automatiquement tous les messages renvoyés par l'API (erreurs, confirmations, validations) et les écrit
dans app/locales/_sources.json : c'est la liste que tools.generate_translations fait traduire.

    .venv\\Scripts\\python -m tools.extract_messages

Les messages avec une partie variable (f-string) deviennent des modèles avec {} :
f"Trop de tentatives. Réessayez dans {minutes} minute(s)." → "Trop de tentatives. Réessayez dans {} minute(s)."."""

import ast
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
CALLS = {  # fonction → position(s) du message
    "ApiError": [2], "error_response": [2], "_error": [2], "validation_error": [1], "_validation": [1],
    "_invalid": [0], "_too_many": [0], "too_many": [0], "ok": [1], "ValueError": [0], "InvalidPhone": [0],
    "ProcessingError": [0], "_not_self": [2], "_protect_last_admin": [2],
}
KEYWORDS = {"message"}


def _text(node) -> str | None:
    if isinstance(node, ast.Constant) and isinstance(node.value, str):
        return node.value
    if isinstance(node, ast.JoinedStr):
        parts = []
        for value in node.values:
            if isinstance(value, ast.Constant):
                parts.append(str(value.value))
            else:
                parts.append("{}")
        return "".join(parts)
    if isinstance(node, ast.BinOp) and isinstance(node.op, ast.Add):
        left, right = _text(node.left), _text(node.right)
        return (left or "{}") + (right or "{}") if (left or right) else None
    if isinstance(node, ast.IfExp):  # "a" if x else "b" : les deux branches
        return None
    return None


def collect() -> list[str]:
    found: set[str] = set()
    for path in (ROOT / "app").rglob("*.py"):
        tree = ast.parse(path.read_text(encoding="utf-8"))
        for node in ast.walk(tree):
            if not isinstance(node, ast.Call):
                continue
            name = getattr(node.func, "id", None) or getattr(node.func, "attr", None)
            candidates = []
            if name in CALLS:
                candidates += [node.args[i] for i in CALLS[name] if i < len(node.args)]
            candidates += [k.value for k in node.keywords if k.arg in KEYWORDS]
            for c in candidates:
                if isinstance(c, ast.IfExp):
                    candidates += [c.body, c.orelse]
                    continue
                text = _text(c)
                if text and text.strip() and text != "{}" and any(ch.isalpha() for ch in text):
                    found.add(text.strip())
    return sorted(found)


def main() -> None:
    messages = collect()
    target = ROOT / "app" / "locales" / "_sources.json"
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_text(json.dumps(messages, ensure_ascii=False, indent=1) + "\n", encoding="utf-8")
    print(f"{len(messages)} message(s) relevé(s) → {target.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
