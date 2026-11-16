// Pages statiques (copies des vues PHP) : adresse -> chargement à la demande. Généré, ne pas modifier à la main.
export const STATIC_PAGES = {
  "/background-strategic-rationale": { key: "strategic", title: "Contexte et justification stratégique", load: () => import('./strategic.js') },
  "/corporate-structure-governance": { key: "governance", title: "Structure et gouvernance d'entreprise", load: () => import('./governance.js') },
  "/vision-mission": { key: "vision", title: "Vision, mission et objectifs", load: () => import('./vision.js') },
  "/esg-sustainability": { key: "esg", title: "ESG et durabilité", load: () => import('./esg.js') },
  "/risk-analysis": { key: "risk", title: "Analyse des risques et stratégies d'atténuation", load: () => import('./risk.js') },
  "/research-innovation": { key: "innovation", title: "Recherche & Innovation", load: () => import('./innovation.js') },
  "/market-outlook": { key: "market", title: "Aperçu du marché & du secteur", load: () => import('./market.js') },
  "/nufotec-phytomed-industries-facility": { key: "facility", title: "Installation NUFOTEC-PHYTOMED INDUSTRIES", load: () => import('./facility.js') },
  "/digital-growth": { key: "digital", title: "Plateforme de croissance digitale & d'expansion commerciale", load: () => import('./digital.js') },
  "/investment-projection": { key: "projection", title: "Projection d'investissement par phase", load: () => import('./projection.js') },
  "/investor-commitment": { key: "commitment", title: "Notre engagement envers les investisseurs & partenaires", load: () => import('./commitment.js') },
  "/strategic-partnerships": { key: "partnerships", title: "Partenariats stratégiques", load: () => import('./partnerships.js') },
  "/broker-commission": { key: "commission", title: "Paiement des commissions aux courtiers", load: () => import('./commission.js') },
}
