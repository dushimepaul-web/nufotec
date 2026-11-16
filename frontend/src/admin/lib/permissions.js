// Permissions côté interface : lues depuis `user.permissions` (renvoyé par l'API).
// L'administrateur (slug « admin ») a toujours tous les droits.
// Une permission = « module.action » où module = une entrée de la barre latérale.

export const ACTIONS = {
  read: 'Voir / lire',
  create: 'Ajouter',
  update: 'Modifier',
  delete: 'Supprimer',
  export: 'Exporter',
  activate: 'Activer / désactiver',
}

// Miroir de app/core/permissions.py (repli si le catalogue de l'API n'est pas encore chargé).
export const MODULES = [
  { key: 'users', label: 'Utilisateurs', icon: 'bi-people', actions: ['read', 'create', 'update', 'delete', 'export', 'activate'] },
  { key: 'roles', label: 'Rôles', icon: 'bi-person-badge', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'medecins', label: 'Médecins', icon: 'bi-heart-pulse', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'consultations', label: 'Consultations', icon: 'bi-calendar-check', actions: ['read', 'update', 'delete'] },
  { key: 'modes_paiement', label: 'Comptes de paiement', icon: 'bi-cash-coin', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'produits', label: 'Produits', icon: 'bi-box-seam', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'categories', label: 'Catégories', icon: 'bi-tags', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'commandes', label: 'Commandes', icon: 'bi-cart', actions: ['read', 'update', 'export'] },
  { key: 'pages', label: 'Pages', icon: 'bi-file-text', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'actualites', label: 'Actualités', icon: 'bi-newspaper', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'faq', label: 'FAQ', icon: 'bi-question-circle', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'temoignages', label: 'Témoignages', icon: 'bi-chat-quote', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'partenaires', label: 'Partenaires', icon: 'bi-building', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'chiffres_cles', label: 'Chiffres clés', icon: 'bi-bar-chart', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'reseaux_sociaux', label: 'Réseaux sociaux', icon: 'bi-share', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'visites', label: 'Visites', icon: 'bi-eye', actions: ['read', 'export'] },
  { key: 'parametres', label: 'Paramètres', icon: 'bi-gear', actions: ['read', 'update'] },
  { key: 'messages', label: 'Messages', icon: 'bi-envelope', actions: ['read', 'update', 'delete'] },
  { key: 'newsletter', label: 'Newsletter', icon: 'bi-send', actions: ['read', 'delete', 'export'] },
  { key: 'investisseurs', label: 'Investisseurs', icon: 'bi-graph-up-arrow', actions: ['read', 'update', 'export'] },
  { key: 'courtiers', label: 'Courtiers', icon: 'bi-handshake', actions: ['read', 'update'] },
  { key: 'phases', label: 'Phases', icon: 'bi-diagram-3', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'data_room', label: 'Data room', icon: 'bi-folder-lock', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'medias', label: 'Médias', icon: 'bi-collection-play', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'collections', label: 'Collections', icon: 'bi-collection', actions: ['read', 'create', 'update', 'delete'] },
  { key: 'commentaires', label: 'Commentaires', icon: 'bi-chat-left-text', actions: ['read', 'update', 'delete'] },
  { key: 'chatbot', label: 'Chatbot', icon: 'bi-robot', actions: ['read', 'update'] },
  { key: 'journal', label: 'Journal', icon: 'bi-journal-text', actions: ['read', 'export'] },
]

export function isAdmin(user) {
  return user?.role?.slug === 'admin'
}

/** Liste des permissions accordées (« * » pour l'administrateur). */
export function permissionsOf(user) {
  if (!user) return []
  if (isAdmin(user)) return ['*']
  return Array.isArray(user.permissions) ? user.permissions : []
}

/** Vrai si toutes les clés demandées sont accordées. */
export function hasPermission(user, ...needed) {
  const granted = permissionsOf(user)
  if (granted.includes('*')) return true
  return needed.every((key) => granted.includes(key))
}

/** Vrai si le rôle peut effectuer `action` sur le module (ex. can(user, 'produits', 'create')). */
export function can(user, module, action) {
  if (isAdmin(user)) return true
  return hasPermission(user, `${module}.${action}`)
}

/** Vrai si au moins une action du module est accordée (pour afficher l'entrée de menu). */
export function canSeeModule(user, module) {
  if (isAdmin(user)) return true
  return permissionsOf(user).some((key) => key.startsWith(`${module}.`))
}
