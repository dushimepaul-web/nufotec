// Adresses des pages publiques (une seule source pour le menu, le pied de page et les liens)
export const ROUTES = {
  home: '/',
  products: '/produits',
  product: (slug) => `/produits/${slug}`,
  doctors: '/teleconsultation',
  booking: (uuid) => `/teleconsultation/rendez-vous/${uuid}`,
  media: '/medias',
  mediaItem: (slug) => `/medias/${slug}`,
  contact: '/contact',
  blog: '/actualites',
  article: (slug) => `/actualites/${slug}`,
  faq: '/faq',
  investor: '/investisseur',
  broker: '/courtier',
  presentation: '/presentation',
  login: '/connexion',
  account: '/mon-compte',
  page: (slug) => `/${slug}`,
}

// Pages de contenu gérées dans l'administration (slugs de l'API GET /pages)
export const PAGE = {
  strategic: '/background-strategic-rationale',
  governance: '/corporate-structure-governance',
  vision: '/vision-mission',
  esg: '/esg-sustainability',
  risk: '/risk-analysis',
  innovation: '/research-innovation',
  market: '/market-outlook',
  facility: '/nufotec-phytomed-industries-facility',
  digital: '/digital-growth',
  projection: '/investment-projection',
  commitment: '/investor-commitment',
  partnerships: '/strategic-partnerships',
  commission: '/broker-commission',
}
