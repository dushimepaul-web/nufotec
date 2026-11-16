"""Données de référence publiques (formulaires de l'app)."""

from fastapi import APIRouter, Depends, Query
from pydantic import BaseModel, ConfigDict
from sqlalchemy import or_, select
from sqlalchemy.orm import Session

from app.core.database import get_db
from app.core.helpers import like_pattern
from app.core.responses import Envelope, ok
from app.models import Pays

router = APIRouter(tags=["Référence"])


class PaysItem(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    pays: str | None
    iso2: str | None
    indicatif: str | None


@router.get("/pays", response_model=Envelope[list[PaysItem]], summary="Liste des pays (recherche possible)")
def list_pays(q: str | None = Query(None, max_length=50, description="Nom ou code ISO"),
              db: Session = Depends(get_db)):
    query = select(Pays).where(Pays.pays.is_not(None), Pays.pays != "").order_by(Pays.pays)
    if q and q.strip():
        pattern = like_pattern(q.strip())
        query = query.where(or_(Pays.pays.like(pattern), Pays.iso2 == q.strip().upper()))
    return ok([PaysItem.model_validate(p) for p in db.scalars(query)])


class LanguageItem(BaseModel):
    code: str
    nom_natif: str
    nom: str
    rtl: bool
    par_defaut: bool


@router.get("/languages", response_model=Envelope[list[LanguageItem]],
            summary="Langues disponibles (pour le sélecteur de langue du site et de l'app)")
def list_languages():
    from app.core.i18n import DEFAULT, LANGUAGES, RTL

    return ok([LanguageItem(code=code, nom_natif=native, nom=fr_name, rtl=code in RTL, par_defaut=code == DEFAULT)
               for code, (native, fr_name, _) in LANGUAGES.items()])
