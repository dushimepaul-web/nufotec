"""Produits et commandes : catalogue, commande via WhatsApp (sans panier), suivi, administration."""

import io
import uuid
from urllib.parse import parse_qs, urlparse

import pytest
from PIL import Image
from sqlalchemy import select

from app.core.helpers import now
from app.core.security import hash_password
from app.models import OrderHistory, OrderRequest, Product, Role, User

PASSWORD = "MotDePasse123"
BURUNDI, FRANCE = 194, 75


def _auth(token):
    return {"Authorization": f"Bearer {token}"}


def _user(db, slug="user", type_utilisateur="patient", **extra):
    u = User(uuid=str(uuid.uuid4()), email=f"test-{uuid.uuid4().hex[:8]}@example.com", password=hash_password(PASSWORD),
             nom="Test", prenom="Client", role_id=db.scalar(select(Role.id).where(Role.slug == slug)),
             type_utilisateur=type_utilisateur, is_active=True, created_at=now(), updated_at=now(), **extra)
    db.add(u)
    db.commit()
    return u


def _login(client, u):
    return _auth(client.post("/api/v1/auth/login", json={"login": u.email, "password": PASSWORD})
                 .json()["data"]["tokens"]["access_token"])


@pytest.fixture
def admin_h(client, db):
    return _login(client, _user(db, "admin", "admin"))


@pytest.fixture
def product(client, admin_h):
    r = client.post("/api/v1/admin/produits", headers=admin_h, json={
        "titre": "Miel de Kirundo", "description": "Miel pur des collines.", "categorie_id": 2,
        "prix_bif": "15000", "prix_usd": "5", "stock": 10, "vedette": True})
    assert r.status_code == 201, r.text
    return r.json()["data"]


def _order(client, slug, **extra):
    body = {"nom_complet": "Aline Niyonzima", "telephone": "79 333 222", "pays_id": BURUNDI, "ville": "Bujumbura",
            "adresse": "Rohero, avenue de l'Amitié 12", "quantite": 2, **extra}
    return client.post(f"/api/v1/produits/{slug}/commander", json=body)


# ───────────── catalogue ─────────────

def test_existing_products_converted(client):
    items = client.get("/api/v1/produits", params={"per_page": 50}).json()["data"]["items"]
    dermecur = next(p for p in items if p["titre"] == "Dermécur")  # accents corrects après conversion
    assert dermecur["prix_usd"] == "7.00" and dermecur["prix_bif"] == "21000.00" and dermecur["produit_sante"]
    assert all(p["titre"] != "bbbbbbbb" for p in items)  # produit supprimé invisible


def test_health_product_shows_warning(client):
    items = client.get("/api/v1/produits", params={"q": "Dermécur"}).json()["data"]["items"]
    detail = client.get(f"/api/v1/produits/{items[0]['slug']}").json()["data"]
    assert "ne remplace pas" in detail["avertissement_sante"] and detail["proposer_consultation"]


def test_categories_tree_includes_product_lines(client):
    tree = client.get("/api/v1/produits/categories").json()["data"]
    codes = {c["code"] for c in tree if c["code"]}
    assert {"A", "B", "C", "D", "E", "F", "G"} <= codes
    medical = next(c for c in tree if c["slug"] == "produits-medicaux")
    assert medical["produits"] >= 6


def test_catalog_filters_and_sort(client, product):
    r = client.get("/api/v1/produits", params={"categorie": "produits-des-plantes"}).json()["data"]
    assert [p["uuid"] for p in r["items"]] == [product["uuid"]]
    r = client.get("/api/v1/produits", params={"prix_max": 5, "devise": "USD", "tri": "prix_asc"}).json()["data"]
    prices = [float(p["prix_usd"]) for p in r["items"]]
    assert prices == sorted(prices) and max(prices) <= 5
    assert client.get("/api/v1/produits", params={"vedette": True}).json()["data"]["items"][0]["vedette"]


# ───────────── commande via WhatsApp ─────────────

def test_order_returns_whatsapp_to_sales(client, db, product):
    r = _order(client, product["slug"])
    assert r.status_code == 201, r.text
    data = r.json()["data"]
    order = data["commande"]
    assert order["total"] == "30000.00" and order["devise"] == "BIF" and order["statut"] == "en_attente"
    wa = data["whatsapp"]
    assert wa["numero"] == "+25779666439"  # parametres.contact_whatsapp
    url = urlparse(wa["url"])
    assert url.netloc == "wa.me" and url.path == "/25779666439"
    message = parse_qs(url.query)["text"][0]
    for expected in (order["numero"], "Miel de Kirundo", "Quantité : 2", "30.000 BIF", "+25779333222", "Bujumbura"):
        assert expected in message, expected
    row = db.scalar(select(OrderRequest).where(OrderRequest.numero == order["numero"]))
    assert row.customer_phone == "+25779333222" and row.prix_unitaire == 15000


def test_foreign_customer_pays_usd(client, product):
    data = _order(client, product["slug"], pays_id=FRANCE, telephone="+33612345678").json()["data"]
    assert data["commande"]["devise"] == "USD" and data["commande"]["total"] == "10.00"


def test_out_of_stock_and_honeypot(client, db, product):
    r = _order(client, product["slug"], quantite=11)
    assert r.status_code == 409 and r.json()["error"]["code"] == "OUT_OF_STOCK"
    before = db.query(OrderRequest).count()
    r = _order(client, product["slug"], site_web="http://spam")
    assert r.status_code == 201 and db.query(OrderRequest).count() == before


def test_order_validation(client, product):
    r = client.post(f"/api/v1/produits/{product['slug']}/commander", json={"nom_complet": "A"})
    fields = {d["field"] for d in r.json()["error"]["details"]}
    assert {"telephone", "pays_id", "ville", "adresse"} <= fields


def test_price_request_counts_and_opens_whatsapp(client, db, product):
    wa = client.post(f"/api/v1/produits/{product['slug']}/demande-prix").json()["data"]
    assert "prix actualisé" in wa["message"] and wa["url"].startswith("https://wa.me/")
    assert db.scalar(select(Product.price_request_count).where(Product.uuid == product["uuid"])) == 1


# ───────────── suivi ─────────────

def test_guest_tracking_needs_number_and_phone(client, product):
    numero = _order(client, product["slug"]).json()["data"]["commande"]["numero"]
    r = client.get("/api/v1/commandes/suivi", params={"numero": numero, "telephone": "+257 79 333 222"})
    assert r.status_code == 200 and r.json()["data"]["statut"] == "en_attente"
    r = client.get("/api/v1/commandes/suivi", params={"numero": numero, "telephone": "79000000"})
    assert r.status_code == 404


def test_my_orders_by_account_and_profile_phone(client, db, product):
    u = _user(db, telephone="+25779444555")
    h = _login(client, u)
    client.post(f"/api/v1/produits/{product['slug']}/commander", headers=h, json={
        "nom_complet": "Client Connecté", "telephone": "79 111 000", "pays_id": BURUNDI, "ville": "Gitega", "adresse": "Centre"})
    _order(client, product["slug"], telephone="79 444 555")  # sans compte, mais avec le téléphone du profil
    items = client.get("/api/v1/commandes", headers=h).json()["data"]["items"]
    assert len(items) == 2


# ───────────── administration ─────────────

def test_order_status_flow_stock_and_history(client, db, admin_h, product, outbox):
    numero = _order(client, product["slug"], email="aline@example.com").json()["data"]["commande"]["numero"]
    r = client.post(f"/api/v1/admin/commandes/{numero}/statut", headers=admin_h, json={"statut": "livree"})
    assert r.status_code == 409 and r.json()["error"]["code"] == "INVALID_TRANSITION"
    client.post(f"/api/v1/admin/commandes/{numero}/contacte", headers=admin_h)
    client.post(f"/api/v1/admin/commandes/{numero}/statut", headers=admin_h, json={"statut": "en_cours"})
    r = client.post(f"/api/v1/admin/commandes/{numero}/statut", headers=admin_h,
                    json={"statut": "livree", "note": "Livrée par moto"})
    assert r.json()["data"]["statut"] == "livree" and r.json()["data"]["whatsapp_contacte"]
    assert db.scalar(select(Product.stock_quantite).where(Product.uuid == product["uuid"])) == 8  # 10 − 2
    detail = client.get(f"/api/v1/admin/commandes/{numero}", headers=admin_h).json()["data"]
    assert [h["vers"] for h in detail["historique"] if h["vers"]] == ["en_attente", "en_cours", "livree"]
    assert detail["whatsapp_client"]["numero"] == "+25779333222"
    assert any(m.to == "aline@example.com" and numero in m.subject for m in outbox)


def test_admin_orders_list_stats_export(client, db, admin_h, product):
    # Le nom est nettoyé à la saisie (= et parenthèses retirés) ; l'adresse accepte « = » : l'export doit la neutraliser
    placed = _order(client, product["slug"], nom_complet="=HYPERLINK(evil)", adresse="=HYPERLINK(evil)").json()["data"]
    numero = placed["commande"]["numero"]
    saved = db.scalar(select(OrderRequest).where(OrderRequest.numero == numero))
    assert saved.customer_name == "HYPERLINKevil" and saved.customer_address == "=HYPERLINK(evil)"
    r = client.get("/api/v1/admin/commandes", headers=admin_h, params={"q": numero}).json()["data"]
    assert r["meta"]["total"] == 1
    stats = client.get("/api/v1/admin/commandes/stats", headers=admin_h).json()["data"]
    assert stats["total"] >= 36 and stats["par_statut"]["en_attente"] >= 35  # les 35 anciennes demandes
    assert "'=HYPERLINK(evil)" in client.get("/api/v1/admin/commandes/export.csv", headers=admin_h,
                                            params={"q": numero}).text


def test_legacy_orders_visible_with_numbers(client, admin_h):
    items = client.get("/api/v1/admin/commandes", headers=admin_h, params={"per_page": 100}).json()["data"]["items"]
    legacy = [o for o in items if o["numero"].startswith("CMD-2026") and o["prix_affiche"]]
    assert len(legacy) >= 35


def test_product_crud_images_and_soft_delete(client, db, admin_h, product, uploads_dir):
    buf = io.BytesIO()
    Image.new("RGB", (2000, 1500), "green").save(buf, "JPEG")
    r = client.post(f"/api/v1/admin/produits/{product['uuid']}/image", headers=admin_h,
                    files={"image": ("miel.jpg", buf.getvalue(), "image/jpeg")})
    assert r.status_code == 200 and r.json()["data"]["image"]
    r = client.post(f"/api/v1/admin/produits/{product['uuid']}/galerie", headers=admin_h,
                    files={"image": ("miel2.jpg", buf.getvalue(), "image/jpeg")})
    assert len(r.json()["data"]["galerie"]) == 1
    r = client.patch(f"/api/v1/admin/produits/{product['uuid']}", headers=admin_h, json={"prix_bif": "16000", "titre": "Miel de Kirundo bio"})
    data = r.json()["data"]
    assert data["slug"] == "miel-de-kirundo-bio" and data["prix_bif"] == "16000.00"
    assert db.scalar(select(Product.price).where(Product.uuid == product["uuid"])) == "5$ = 16000fbu"  # texte PHP à jour

    numero = _order(client, data["slug"]).json()["data"]["commande"]["numero"]
    client.delete(f"/api/v1/admin/produits/{product['uuid']}", headers=admin_h)
    assert client.get(f"/api/v1/produits/{data['slug']}").status_code == 404
    # L'historique de la commande survit à la suppression du produit
    assert client.get(f"/api/v1/admin/commandes/{numero}", headers=admin_h).json()["data"]["produit"] == "Miel de Kirundo bio"


def test_categories_admin(client, admin_h, product):
    r = client.post("/api/v1/admin/produits/categories", headers=admin_h, json={"nom": "Miels et dérivés", "parent_id": 2})
    assert r.status_code == 201 and r.json()["data"]["slug"] == "miels-et-derives"
    r = client.delete("/api/v1/admin/produits/categories/2", headers=admin_h)
    assert r.status_code == 409 and r.json()["error"]["code"] == "CATEGORY_IN_USE"


def test_admin_only(client, db):
    h = _login(client, _user(db))
    assert client.get("/api/v1/admin/commandes", headers=h).status_code == 403
    assert client.post("/api/v1/admin/produits", headers=h, json={"titre": "x"}).status_code in (403, 422)


def test_order_history_rows(db, client, product):
    numero = _order(client, product["slug"]).json()["data"]["commande"]["numero"]
    o = db.scalar(select(OrderRequest).where(OrderRequest.numero == numero))
    assert db.query(OrderHistory).filter(OrderHistory.order_id == o.id).count() == 1


def test_admin_reads_one_product_for_the_edit_form(client, admin_h, product):
    r = client.get(f"/api/v1/admin/produits/{product['uuid']}", headers=admin_h)
    assert r.status_code == 200, r.text
    data = r.json()["data"]
    assert data["uuid"] == product["uuid"] and "publie" in data and "galerie" in data
    assert client.get("/api/v1/admin/produits/inconnu", headers=admin_h).status_code == 404
    assert client.get(f"/api/v1/admin/produits/{product['uuid']}").status_code == 401


def test_admin_sees_inactive_categories_with_their_state(client, admin_h):
    r = client.post("/api/v1/admin/produits/categories", headers=admin_h, json={"nom": "Catégorie cachée test", "actif": False})
    assert r.status_code == 201, r.text
    cid = r.json()["data"]["id"]
    public = client.get("/api/v1/produits/categories").json()["data"]
    flat = lambda tree: [c for n in tree for c in [n, *flat(n["sous_categories"])]]
    assert all(c["id"] != cid for c in flat(public))
    admin = flat(client.get("/api/v1/admin/produits/categories", headers=admin_h).json()["data"])
    mine = next(c for c in admin if c["id"] == cid)
    assert mine["actif"] is False
    assert client.get("/api/v1/admin/produits/categories").status_code == 401
