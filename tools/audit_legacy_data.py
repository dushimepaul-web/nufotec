"""Vérifie que TOUTES les données réelles passent dans les formats de sortie de l'API (lecture seule).
Une donnée héritée du PHP qui casserait un endpoint est signalée avec son identifiant.

    .venv\\Scripts\\python -m tools.audit_legacy_data"""

from sqlalchemy import select

from app.core.database import SessionLocal
from app.models import Broker, Consultation, Investor, Media, Medecin, OrderRequest, Page, Product, User
from app.modules.auth.schemas import UserOut
from app.modules.consultations import service as consult
from app.modules.consultations.schemas import ConsultationOut, MedecinAdminOut, MedecinDetail
from app.modules.content import service as content
from app.modules.investors import service as inv
from app.modules.investors.schemas import BrokerOut, InvestorAdminOut
from app.modules.media import service as media
from app.modules.shop import service as shop
from app.modules.users.schemas import AdminUserOut
from app.modules.users import service as users


def main() -> None:
    problems: list[str] = []
    counts: dict[str, int] = {}

    def check(label: str, rows, build) -> None:
        counts[label] = 0
        for row in rows:
            counts[label] += 1
            try:
                build(row)
            except Exception as exc:
                problems.append(f"{label} #{getattr(row, 'id', '?')} : {type(exc).__name__}: {str(exc)[:160]}")

    with SessionLocal() as db:
        check("utilisateurs", db.scalars(select(User)), lambda u: (UserOut.of(u), AdminUserOut(**users.detail(db, u).model_dump())))
        check("médecins", db.scalars(select(Medecin)), lambda m: (MedecinDetail(**consult.doctor_public(m, detail=True)),
                                                                   MedecinAdminOut(**__import__("app.modules.consultations.doctors", fromlist=["x"]).admin_out(m))))
        check("consultations", db.scalars(select(Consultation)),
              lambda c: [ConsultationOut(**consult.serialize(db, c, v)) for v in ("patient", "medecin", "admin")])
        check("médias", db.scalars(select(Media)), lambda m: media.admin_out(db, m))
        check("produits", db.scalars(select(Product)), lambda p: shop.product_out(p, detail=True))
        check("commandes", db.scalars(select(OrderRequest)), lambda o: shop.order_out(db, o, admin=True))
        check("investisseurs", db.scalars(select(Investor)), lambda i: InvestorAdminOut(**inv.investor_out(db, i, admin=True)))
        check("courtiers", db.scalars(select(Broker)), lambda b: BrokerOut(**inv.broker_out(b)))
        check("pages", db.scalars(select(Page)), lambda p: content.page_out(db, p, admin=True))
    for label, n in counts.items():
        print(f"{n:5}  {label}")
    print(f"\n{len(problems)} problème(s)")
    for p in problems:
        print("  ✗", p)


if __name__ == "__main__":
    main()
