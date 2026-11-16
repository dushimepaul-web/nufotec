"""Vérification rapide d'un serveur en marche : appelle tous les endpoints GET publics (sans paramètre obligatoire)
et ceux qui prennent un slug, avec un slug réel lu dans les listes. Aucune écriture.

    .venv\\Scripts\\python -m tools.smoke_test http://127.0.0.1:8000"""

import sys

import httpx


def main() -> None:
    base = (sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8000").rstrip("/")
    spec = httpx.get(f"{base}/openapi.json", timeout=30).json()
    results, failures = [], []
    with httpx.Client(base_url=base, timeout=30) as http:
        def first(path: str, key: str = "slug") -> str | None:
            data = http.get(path).json().get("data")
            items = data.get("items") if isinstance(data, dict) else data
            return items[0].get(key) if items else None

        samples = {
            "/api/v1/pages/{slug}": first("/api/v1/pages"),
            "/api/v1/produits/{slug}": first("/api/v1/produits"),
            "/api/v1/medias/{slug}": first("/api/v1/medias"),
            "/api/v1/medias/{slug}/commentaires": first("/api/v1/medias"),
            "/api/v1/medecins/{medecin_uuid}": first("/api/v1/medecins", "uuid"),
            "/api/v1/medecins/{medecin_uuid}/creneaux": first("/api/v1/medecins", "uuid"),
        }
        for path, ops in sorted(spec["paths"].items()):
            op = ops.get("get")
            if not op or op.get("security") or path.startswith(("/api/v1/admin", "/api/v1/auth")):
                continue
            required = [p for p in op.get("parameters", []) if p.get("required") and p.get("in") == "query"]
            if required:
                continue
            url = path
            if "{" in path:
                value = samples.get(path)
                if not value:
                    continue
                url = path.replace("{slug}", value).replace("{medecin_uuid}", value)
            r = http.get(url)
            needs_login = r.status_code == 401  # espaces connectés (favoris, reprise…) : attendu sans jeton
            ok = r.status_code == 200 or needs_login
            results.append((r.status_code, url))
            if not ok:
                failures.append((r.status_code, url, r.text[:200]))
    for code, url in results:
        print(f"{code}  {url}")
    print(f"\n{len(results)} endpoint(s) appelé(s), {len(failures)} échec(s)")
    for f in failures:
        print("ÉCHEC", f)
    sys.exit(1 if failures else 0)


if __name__ == "__main__":
    main()
