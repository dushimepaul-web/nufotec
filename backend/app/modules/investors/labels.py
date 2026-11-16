"""Libellés français des choix des formulaires investisseur / courtier (messages WhatsApp et emails).
Mêmes textes que les formulaires du site."""

INTERESTS_FR = {"equity": "Équité (Equity)", "debt": "Dette structurée", "blended_finance": "Finance mixte",
                "grant": "Grant / Subvention", "strategic_partnership": "Partenariat stratégique",
                "technical_collaboration": "Collaboration technique", "offtake_distribution": "Offtake / Distribution"}
FOCUS_FR = {"research_lab": "Labo de recherche", "gmp_facility": "Facility GMP", "medicinal_plant": "Plantes médicinales",
            "commercialization": "Commercialisation", "full_platform": "Plateforme complète"}
COMMITMENT_FR = {"Below 250K": "Moins de 250K €", "250K-1M": "250K - 1M €", "1M-5M": "1M - 5M €", "5M+": "5M+ €",
                 "To be discussed": "À discuter"}
TIMELINE_FR = {"Immediate": "Immédiat", "3-6 months": "3-6 mois", "6-12 months": "6-12 mois", "Exploratory": "Exploratoire"}
CAPACITIES_FR = {"investment_broker": "Broker d'investissement", "placement_agent": "Agent de placement",
                 "corporate_finance_advisor": "Conseiller Corp. Finance", "fund_manager": "Gestionnaire de fonds",
                 "family_office_rep": "Family Office Rep.", "esg_advisor": "Conseiller ESG",
                 "independent_introducer": "Introducteur indépendant"}
INVESTOR_TYPES_FR = {"private_equity": "Private Equity", "venture_capital": "Venture Capital", "esg_impact": "ESG / Impact",
                     "dfi": "DFI", "institutional": "Institutionnel", "hnwi": "HNWI", "sovereign": "Fonds souverain"}
MANDATES_FR = {"equity": "Équité", "structured_debt": "Dette structurée", "blended_finance": "Finance mixte",
               "grant": "Grant", "strategic_partnership": "Partenariat stratégique", "full_program": "Programme complet"}


def join(values, labels: dict) -> str:
    return ", ".join(labels.get(v, v) for v in values) or "—"
