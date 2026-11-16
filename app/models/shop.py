"""Produits, catégories, commandes (tables existantes, enrichies par sql/011)."""

from datetime import datetime
from decimal import Decimal

from sqlalchemy import Boolean, DateTime, Enum, ForeignKey, Integer, Numeric, SmallInteger, String, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base

# Statuts en base (compatibles PHP) → libellés de l'API
ORDER_STATUS = {"pending": "en_attente", "processing": "en_cours", "completed": "livree", "cancelled": "annulee"}
ORDER_STATUS_DB = {v: k for k, v in ORDER_STATUS.items()}


class ProductCategory(Base):
    __tablename__ = "product_categories"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    name: Mapped[str] = mapped_column(String(100))
    parent_id: Mapped[int | None] = mapped_column(ForeignKey("product_categories.id"))
    code: Mapped[str | None] = mapped_column(String(5))
    slug: Mapped[str | None] = mapped_column(String(120), unique=True)
    description: Mapped[str | None] = mapped_column(Text)
    image: Mapped[str | None] = mapped_column(String(255))
    position: Mapped[int] = mapped_column(SmallInteger, default=0)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    legacy_categorie_id: Mapped[int | None] = mapped_column(Integer)


class Product(Base):
    __tablename__ = "advertise_product"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    uuid: Mapped[str | None] = mapped_column(String(36), unique=True)
    category_id: Mapped[int | None] = mapped_column(ForeignKey("product_categories.id"))
    main_image: Mapped[str] = mapped_column(String(255), default="")
    title: Mapped[str] = mapped_column(String(255))
    slug: Mapped[str | None] = mapped_column(String(255), unique=True)
    description: Mapped[str | None] = mapped_column(Text)
    price: Mapped[str] = mapped_column(String(25), default="")  # texte affiché par le PHP, tenu à jour
    prix_usd: Mapped[Decimal | None] = mapped_column(Numeric(10, 2))
    prix_bif: Mapped[Decimal | None] = mapped_column(Numeric(12, 2))
    stock_quantite: Mapped[int | None] = mapped_column(Integer)
    est_produit_sante: Mapped[bool] = mapped_column(Boolean, default=False)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    in_vedette: Mapped[bool] = mapped_column(Boolean, default=False)
    price_request_count: Mapped[int] = mapped_column(Integer, default=0)
    created_at: Mapped[datetime | None] = mapped_column(DateTime)
    updated_at: Mapped[datetime | None] = mapped_column(DateTime)
    deleted_at: Mapped[datetime | None] = mapped_column(DateTime)

    category: Mapped[ProductCategory | None] = relationship(lazy="joined")
    images: Mapped[list["ProductImage"]] = relationship(lazy="selectin", order_by="ProductImage.position",
                                                        cascade="all, delete-orphan")


class ProductImage(Base):
    __tablename__ = "product_images"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    product_id: Mapped[int] = mapped_column(ForeignKey("advertise_product.id"))
    fichier: Mapped[str] = mapped_column(String(255))
    position: Mapped[int] = mapped_column(SmallInteger, default=0)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class OrderRequest(Base):
    __tablename__ = "order_requests"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    numero: Mapped[str | None] = mapped_column(String(30), unique=True)
    product_id: Mapped[int | None] = mapped_column(ForeignKey("advertise_product.id"))
    user_id: Mapped[int | None] = mapped_column(Integer)
    customer_name: Mapped[str] = mapped_column(String(255))
    customer_phone: Mapped[str] = mapped_column(String(50))
    customer_email: Mapped[str | None] = mapped_column(String(150))
    customer_country: Mapped[str] = mapped_column(String(100))
    customer_city: Mapped[str] = mapped_column(String(100))
    customer_address: Mapped[str] = mapped_column(Text)
    customer_notes: Mapped[str | None] = mapped_column(Text)
    product_title: Mapped[str] = mapped_column(String(255))
    product_price: Mapped[str] = mapped_column(String(50))
    quantite: Mapped[int] = mapped_column(Integer, default=1)
    prix_unitaire: Mapped[Decimal | None] = mapped_column(Numeric(12, 2))
    devise: Mapped[str | None] = mapped_column(String(5))
    total: Mapped[Decimal | None] = mapped_column(Numeric(14, 2))
    order_status: Mapped[str] = mapped_column(Enum(*ORDER_STATUS, native_enum=False), default="pending")
    whatsapp_sent: Mapped[bool] = mapped_column(Boolean, default=False)
    whatsapp_sent_at: Mapped[datetime | None] = mapped_column(DateTime)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    user_agent: Mapped[str | None] = mapped_column(Text)
    created_at: Mapped[datetime | None] = mapped_column(DateTime)
    updated_at: Mapped[datetime | None] = mapped_column(DateTime)

    product: Mapped[Product | None] = relationship(lazy="joined")


class OrderHistory(Base):
    __tablename__ = "order_history"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    order_id: Mapped[int] = mapped_column(ForeignKey("order_requests.id"))
    from_status: Mapped[str | None] = mapped_column(String(20))
    to_status: Mapped[str | None] = mapped_column(String(20))
    note: Mapped[str | None] = mapped_column(Text)
    author_id: Mapped[int | None] = mapped_column(Integer)
    created_at: Mapped[datetime] = mapped_column(DateTime)
