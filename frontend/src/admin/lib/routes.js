// Adresses des pages de l'interface d'administration et de l'espace médecin.

export const ROUTES = {
  login: '/login',
  adminHome: '/admin',
  doctorHome: '/medecin',

  // Admin — utilisateurs & rôles
  users: '/admin/utilisateurs',
  user: (uuid) => `/admin/utilisateurs/${uuid}`,
  roles: '/admin/roles',

  // Admin — médecins & consultations
  medecins: '/admin/medecins',
  medecinNouveau: '/admin/medecins/nouveau',
  medecin: (uuid) => `/admin/medecins/${uuid}`,
  medecinModifier: (uuid) => `/admin/medecins/${uuid}/modifier`,
  consultations: '/admin/consultations',
  consultation: (numero) => `/admin/consultations/${numero}`,
  modesPaiement: '/admin/modes-paiement',

  // Admin — boutique
  produits: '/admin/produits',
  produitNouveau: '/admin/produits/nouveau',
  produit: (uuid) => `/admin/produits/${uuid}/modifier`,
  categories: '/admin/categories',
  commandes: '/admin/commandes',
  commande: (numero) => `/admin/commandes/${numero}`,

  // Admin — contenu
  contenu: '/admin/contenu',
  pages: '/admin/contenu/pages',
  pageNouveau: '/admin/contenu/pages/nouveau',
  page: (id) => `/admin/contenu/pages/${id}`,
  actualites: '/admin/contenu/actualites',
  actualiteNouveau: '/admin/contenu/actualites/nouveau',
  actualite: (id) => `/admin/contenu/actualites/${id}/modifier`,
  faq: '/admin/contenu/faq',
  parametres: '/admin/contenu/parametres',
  messages: '/admin/contenu/messages',
  newsletter: '/admin/contenu/newsletter',

  // Admin — autres modules
  investisseurs: '/admin/investisseurs',
  investisseur: (uuid) => `/admin/investisseurs/${uuid}`,
  courtiers: '/admin/courtiers',
  phases: '/admin/investissement/phases',
  dataRoom: '/admin/data-room',
  medias: '/admin/medias',
  media: (uuid) => `/admin/medias/${uuid}`,
  mediasCommentaires: '/admin/medias/commentaires',
  mediasCollections: '/admin/medias/collections',
  chatbot: '/admin/chatbot',
  journal: '/admin/journal',
  statistiques: '/admin/statistiques',

  // Médecin
  doctorAgenda: '/medecin/consultations',
  doctorConsultation: (numero) => `/medecin/consultations/${numero}`,
  doctorProfil: '/medecin/profil',
  doctorHoraires: '/medecin/horaires',
  doctorAbsences: '/medecin/absences',
}

/** Retour à l'accueil selon le rôle. */
export function homeFor(role) {
  return role === 'medecin' ? ROUTES.doctorHome : ROUTES.adminHome
}
