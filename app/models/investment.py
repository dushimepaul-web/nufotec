"""Investisseurs, courtiers, phases d'investissement (tables existantes) ; suivi, data room et NDA (sql/009)."""

from datetime import datetime
from decimal import Decimal

from sqlalchemy import BigInteger, Boolean, DateTime, Enum, ForeignKey, Integer, Numeric, String, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base
from app.models.user import Pays

INVESTOR_STATUTS = ("nouveau", "contacte", "qualifie", "due_diligence", "engage", "perdu")
QUALIFIED = ("qualifie", "due_diligence", "engage")
BROKER_STATUTS = ("en_attente", "approuve", "refuse", "suspendu")
COMMITMENT_RANGES = ("Below 250K", "250K-1M", "1M-5M", "5M+", "To be discussed")
TIMELINES = ("Immediate", "3-6 months", "6-12 months", "Exploratory")
REGULATORY = ("Licensed", "Exempt", "Unlicensed")
ENGAGEMENT = ("Success Commission", "Retainer + Success Fee", "Referral Arrangement", "To be negotiated")

# Cases à cocher des formulaires : nom public → colonne
INTERESTS = {"equity": "interest_equity", "debt": "interest_debt", "blended_finance": "interest_blended_finance",
             "grant": "interest_grant", "strategic_partnership": "interest_strategic_partnership",
             "technical_collaboration": "interest_technical_collaboration",
             "offtake_distribution": "interest_offtake_distribution"}
FOCUS = {"research_lab": "focus_research_lab", "gmp_facility": "focus_gmp_facility",
         "medicinal_plant": "focus_medicinal_plant", "commercialization": "focus_commercialization",
         "full_platform": "focus_full_platform"}
CAPACITIES = {"investment_broker": "capacity_investment_broker", "placement_agent": "capacity_placement_agent",
              "corporate_finance_advisor": "capacity_corporate_finance_advisor", "fund_manager": "capacity_fund_manager",
              "family_office_rep": "capacity_family_office_rep", "esg_advisor": "capacity_esg_advisor",
              "independent_introducer": "capacity_independent_introducer"}
INVESTOR_TYPES = {"private_equity": "investor_private_equity", "venture_capital": "investor_venture_capital",
                  "esg_impact": "investor_esg_impact", "dfi": "investor_dfi", "institutional": "investor_institutional",
                  "hnwi": "investor_hnwi", "sovereign": "investor_sovereign"}
MANDATES = {"equity": "mandate_equity", "structured_debt": "mandate_structured_debt",
            "blended_finance": "mandate_blended_finance", "grant": "mandate_grant",
            "strategic_partnership": "mandate_strategic_partnership", "full_program": "mandate_full_program"}


class Investor(Base):
    __tablename__ = "investors"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True)
    uuid: Mapped[str | None] = mapped_column(String(36), unique=True)
    user_id: Mapped[int | None] = mapped_column(Integer)
    full_name: Mapped[str] = mapped_column(String(150))
    organization: Mapped[str | None] = mapped_column(String(150))
    position_title: Mapped[str | None] = mapped_column(String(150))
    id_pays: Mapped[int] = mapped_column(ForeignKey("pays.id"))
    email: Mapped[str] = mapped_column(String(150))
    phone: Mapped[str | None] = mapped_column(String(50))
    interest_equity: Mapped[bool] = mapped_column(Boolean, default=False)
    interest_debt: Mapped[bool] = mapped_column(Boolean, default=False)
    interest_blended_finance: Mapped[bool] = mapped_column(Boolean, default=False)
    interest_grant: Mapped[bool] = mapped_column(Boolean, default=False)
    interest_strategic_partnership: Mapped[bool] = mapped_column(Boolean, default=False)
    interest_technical_collaboration: Mapped[bool] = mapped_column(Boolean, default=False)
    interest_offtake_distribution: Mapped[bool] = mapped_column(Boolean, default=False)
    interest_other: Mapped[str | None] = mapped_column(String(255))
    commitment_range: Mapped[str | None] = mapped_column(String(50))
    focus_research_lab: Mapped[bool] = mapped_column(Boolean, default=False)
    focus_gmp_facility: Mapped[bool] = mapped_column(Boolean, default=False)
    focus_medicinal_plant: Mapped[bool] = mapped_column(Boolean, default=False)
    focus_commercialization: Mapped[bool] = mapped_column(Boolean, default=False)
    focus_full_platform: Mapped[bool] = mapped_column(Boolean, default=False)
    timeline: Mapped[str | None] = mapped_column(String(30), default="Exploratory")
    strategic_message: Mapped[str | None] = mapped_column(Text)
    agree_contact: Mapped[bool] = mapped_column(Boolean, default=False)
    non_binding_confirmation: Mapped[bool] = mapped_column(Boolean, default=False)
    statut: Mapped[str] = mapped_column(Enum(*INVESTOR_STATUTS, native_enum=False), default="nouveau")
    assigned_to: Mapped[int | None] = mapped_column(Integer)
    prochaine_relance: Mapped[datetime | None] = mapped_column(DateTime)
    relance_envoyee_at: Mapped[datetime | None] = mapped_column(DateTime)
    source: Mapped[str] = mapped_column(Enum("direct", "courtier", native_enum=False), default="direct")
    broker_id: Mapped[int | None] = mapped_column(BigInteger)
    consent_at: Mapped[datetime | None] = mapped_column(DateTime)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    created_at: Mapped[datetime | None] = mapped_column(DateTime)
    updated_at: Mapped[datetime | None] = mapped_column(DateTime)

    pays: Mapped[Pays] = relationship(lazy="joined")


class Broker(Base):
    __tablename__ = "brokers"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True)
    uuid: Mapped[str | None] = mapped_column(String(36), unique=True)
    user_id: Mapped[int | None] = mapped_column(Integer)
    full_name: Mapped[str] = mapped_column(String(150))
    firm_name: Mapped[str] = mapped_column(String(200))
    jurisdiction_of_incorporation: Mapped[str | None] = mapped_column(String(150))
    registration_number: Mapped[str | None] = mapped_column(String(100))
    regulatory_status: Mapped[str | None] = mapped_column(String(30))
    regulatory_authority: Mapped[str | None] = mapped_column(String(150))
    id_pays: Mapped[int] = mapped_column(ForeignKey("pays.id"))
    email: Mapped[str] = mapped_column(String(150))
    mobile_phone: Mapped[str | None] = mapped_column(String(50))
    whatsapp: Mapped[str | None] = mapped_column(String(50))
    corporate_website: Mapped[str | None] = mapped_column(String(200))
    capacity_investment_broker: Mapped[bool] = mapped_column(Boolean, default=False)
    capacity_placement_agent: Mapped[bool] = mapped_column(Boolean, default=False)
    capacity_corporate_finance_advisor: Mapped[bool] = mapped_column(Boolean, default=False)
    capacity_fund_manager: Mapped[bool] = mapped_column(Boolean, default=False)
    capacity_family_office_rep: Mapped[bool] = mapped_column(Boolean, default=False)
    capacity_esg_advisor: Mapped[bool] = mapped_column(Boolean, default=False)
    capacity_independent_introducer: Mapped[bool] = mapped_column(Boolean, default=False)
    capacity_other: Mapped[str | None] = mapped_column(String(255))
    investor_private_equity: Mapped[bool] = mapped_column(Boolean, default=False)
    investor_venture_capital: Mapped[bool] = mapped_column(Boolean, default=False)
    investor_esg_impact: Mapped[bool] = mapped_column(Boolean, default=False)
    investor_dfi: Mapped[bool] = mapped_column(Boolean, default=False)
    investor_institutional: Mapped[bool] = mapped_column(Boolean, default=False)
    investor_hnwi: Mapped[bool] = mapped_column(Boolean, default=False)
    investor_sovereign: Mapped[bool] = mapped_column(Boolean, default=False)
    typical_ticket_size: Mapped[str | None] = mapped_column(String(150))
    geographic_coverage: Mapped[str | None] = mapped_column(Text)
    mandate_equity: Mapped[bool] = mapped_column(Boolean, default=False)
    mandate_structured_debt: Mapped[bool] = mapped_column(Boolean, default=False)
    mandate_blended_finance: Mapped[bool] = mapped_column(Boolean, default=False)
    mandate_grant: Mapped[bool] = mapped_column(Boolean, default=False)
    mandate_strategic_partnership: Mapped[bool] = mapped_column(Boolean, default=False)
    mandate_full_program: Mapped[bool] = mapped_column(Boolean, default=False)
    engagement_model: Mapped[str | None] = mapped_column(String(50))
    confirm_authorized: Mapped[bool] = mapped_column(Boolean, default=False)
    confirm_aml_kyc: Mapped[bool] = mapped_column(Boolean, default=False)
    acknowledge_no_exclusivity: Mapped[bool] = mapped_column(Boolean, default=False)
    understand_formal_mandate_required: Mapped[bool] = mapped_column(Boolean, default=False)
    statut: Mapped[str] = mapped_column(Enum(*BROKER_STATUTS, native_enum=False), default="en_attente")
    motif_decision: Mapped[str | None] = mapped_column(String(255))
    decision_at: Mapped[datetime | None] = mapped_column(DateTime)
    decision_par: Mapped[int | None] = mapped_column(Integer)
    consent_at: Mapped[datetime | None] = mapped_column(DateTime)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    created_at: Mapped[datetime | None] = mapped_column(DateTime)
    updated_at: Mapped[datetime | None] = mapped_column(DateTime)

    pays: Mapped[Pays] = relationship(lazy="joined")


class BrokerInvestor(Base):
    __tablename__ = "broker_investors"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    broker_id: Mapped[int] = mapped_column(ForeignKey("brokers.id"))
    investor_id: Mapped[int | None] = mapped_column(BigInteger)
    full_name: Mapped[str] = mapped_column(String(150))
    organization: Mapped[str | None] = mapped_column(String(200))
    email: Mapped[str] = mapped_column(String(150))
    phone: Mapped[str | None] = mapped_column(String(50))
    commitment_range: Mapped[str | None] = mapped_column(String(50))
    status: Mapped[str | None] = mapped_column(Enum("pending", "contacted", "invested", native_enum=False),
                                              default="pending")
    notes: Mapped[str | None] = mapped_column(Text)
    attribution_until: Mapped[datetime | None] = mapped_column(DateTime)
    created_at: Mapped[datetime | None] = mapped_column(DateTime)
    updated_at: Mapped[datetime | None] = mapped_column(DateTime)


class InvestmentPhase(Base):
    __tablename__ = "investissement_phases"

    id: Mapped[int] = mapped_column("id_phase", Integer, primary_key=True)
    nom_phase: Mapped[str] = mapped_column(String(100))
    annee_debut: Mapped[int | None] = mapped_column(Integer)
    annee_fin: Mapped[int | None] = mapped_column(Integer)
    montant_total: Mapped[Decimal | None] = mapped_column(Numeric(15, 2))
    devise: Mapped[str | None] = mapped_column(String(10), default="USD")
    description: Mapped[str | None] = mapped_column(Text)
    allocation_details: Mapped[str | None] = mapped_column(Text)
    id_page_associee: Mapped[int | None] = mapped_column(Integer)


class InvestorNote(Base):
    __tablename__ = "investor_notes"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    investor_id: Mapped[int] = mapped_column(ForeignKey("investors.id"))
    author_id: Mapped[int | None] = mapped_column(Integer)
    kind: Mapped[str] = mapped_column(Enum("note", "statut", "relance", "assignation", "manifestation",
                                           native_enum=False), default="note")
    from_status: Mapped[str | None] = mapped_column(String(20))
    to_status: Mapped[str | None] = mapped_column(String(20))
    content: Mapped[str | None] = mapped_column(Text)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class DataRoomDocument(Base):
    __tablename__ = "data_room_documents"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    uuid: Mapped[str] = mapped_column(String(36), unique=True)
    titre: Mapped[str] = mapped_column(String(255))
    description: Mapped[str | None] = mapped_column(Text)
    stored_name: Mapped[str] = mapped_column(String(100))
    original_name: Mapped[str] = mapped_column(String(255))
    mime: Mapped[str] = mapped_column(String(150))
    size_bytes: Mapped[int] = mapped_column(Integer)
    visibilite: Mapped[str] = mapped_column(Enum("tous_qualifies", "selection", native_enum=False),
                                            default="tous_qualifies")
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    created_by: Mapped[int | None] = mapped_column(Integer)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class DataRoomAccess(Base):
    __tablename__ = "data_room_access"

    document_id: Mapped[int] = mapped_column(ForeignKey("data_room_documents.id"), primary_key=True)
    investor_id: Mapped[int] = mapped_column(ForeignKey("investors.id"), primary_key=True)


class DataRoomDownload(Base):
    __tablename__ = "data_room_downloads"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True)
    document_id: Mapped[int] = mapped_column(ForeignKey("data_room_documents.id"))
    investor_id: Mapped[int | None] = mapped_column(BigInteger)
    user_id: Mapped[int | None] = mapped_column(Integer)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    created_at: Mapped[datetime] = mapped_column(DateTime)


class NdaAcceptance(Base):
    __tablename__ = "nda_acceptances"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    investor_id: Mapped[int] = mapped_column(ForeignKey("investors.id"))
    user_id: Mapped[int] = mapped_column(Integer)
    nda_version: Mapped[str] = mapped_column(String(20))
    ip_address: Mapped[str | None] = mapped_column(String(45))
    user_agent: Mapped[str | None] = mapped_column(String(500))
    accepted_at: Mapped[datetime] = mapped_column(DateTime)
