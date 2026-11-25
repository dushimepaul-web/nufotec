"""Accueil en une seule requête (rapidité, surtout sur réseau mobile).

Au démarrage, l'app mobile avait besoin de six requêtes : paramètres du site, réseaux sociaux, témoignages,
catégories de médias, médias récents et produits. Sur un réseau mobile, chaque requête coûte 300 à 600 ms
d'attente, quelle que soit sa taille. GET /accueil renvoie tout d'un coup, dans les mêmes formats que les
adresses d'origine (l'app range chaque partie comme si elle venait de son adresse habituelle)."""

from fastapi import APIRouter, Depends, Query
from sqlalchemy.orm import Session

from app.core.database import get_db
from app.core.pagination import Page, PageParams
from app.core.responses import Envelope, ok

router = APIRouter(tags=["Accueil"])
DB = Depends(get_db)


@router.get("/accueil", response_model=Envelope[dict],
            summary="Tout l'accueil en une requête : paramètres, réseaux, témoignages, médias, produits")
def home(produits: int = Query(8, ge=1, le=20, description="Nombre de produits"),
         medias: int = Query(10, ge=1, le=20, description="Nombre de médias récents"), db: Session = DB):
    # Les fonctions des adresses d'origine sont réutilisées telles quelles : mêmes données, mêmes formats.
    from app.modules.content.router_public import site_settings, socials, testimonials
    from app.modules.media.router import categories, library
    from app.modules.shop.router import ProductItem, catalog

    products = catalog(q=None, categorie=None, vedette=False, prix_min=None, prix_max=None, devise="BIF",
                       tri="recent", page=PageParams(page=1, per_page=produits), db=db)["data"]
    return ok({
        "parametres": site_settings(db=db)["data"],
        "reseaux_sociaux": socials(db=db)["data"],
        "temoignages": testimonials(db=db)["data"],
        "medias_categories": categories(db=db)["data"],
        "medias_recents": library(categorie=None, q=None, tri="recent", page=PageParams(page=1, per_page=medias),
                                  db=db)["data"],
        # même filtrage des champs que GET /produits (modèle de réponse ProductItem)
        "produits": Page[ProductItem].model_validate(products).model_dump(mode="json"),
    })
