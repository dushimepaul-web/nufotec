"""Pagination commune : ?page=1&per_page=20 → {"items": [...], "meta": {...}}."""

import math
from typing import Generic, TypeVar

from fastapi import Query
from pydantic import BaseModel
from sqlalchemy import func, select
from sqlalchemy.orm import Session
from sqlalchemy.sql import Select

T = TypeVar("T")


class PageMeta(BaseModel):
    page: int
    per_page: int
    total: int
    pages: int


class Page(BaseModel, Generic[T]):
    items: list[T]
    meta: PageMeta


class PageParams:
    def __init__(self, page: int = Query(1, ge=1, description="Numéro de page"),
                 per_page: int = Query(20, ge=1, le=100, description="Éléments par page (100 max)")):
        self.page, self.per_page = page, per_page


def paginate(db: Session, query: Select, params: PageParams) -> tuple[list, PageMeta]:
    total = db.scalar(select(func.count()).select_from(query.order_by(None).subquery())) or 0
    rows = list(db.scalars(query.limit(params.per_page).offset((params.page - 1) * params.per_page)).unique())
    meta = PageMeta(page=params.page, per_page=params.per_page, total=total,
                    pages=max(math.ceil(total / params.per_page), 1))
    return rows, meta
