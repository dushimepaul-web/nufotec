// Mise en forme partagée (prix, dates, statuts → badge Bootstrap).

/** Prix en BIF sinon USD. */
export function formatPrice(p) {
  if (p?.prix_bif != null) return `${Number(p.prix_bif).toLocaleString('fr-FR', { maximumFractionDigits: 0 })} BIF`
  if (p?.prix_usd != null) return `${Number(p.prix_usd).toLocaleString('fr-FR')} USD`
  return '—'
}

export function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' }) : '—'
}

export function formatDateTime(iso) {
  return iso
    ? new Date(iso).toLocaleString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
    : '—'
}

// Couleurs Bootstrap pour les statuts (consultations, commandes, paiements, comptes).
const BADGES = {
  // consultation
  en_attente: 'warning',
  confirmee: 'success',
  en_cours: 'primary',
  terminee: 'info',
  refusee: 'danger',
  annulee: 'secondary',
  absent: 'dark',
  // paiement
  paye: 'success',
  echoue: 'danger',
  rembourse: 'info',
  // commande
  livree: 'success',
  // compte
  actif: 'success',
  inactif: 'secondary',
  supprime: 'danger',
  // médecin
  disponible: 'success',
  indisponible: 'secondary',
  verifiee: 'success',
  non_verifiee: 'warning',
  // divers
  brouillon: 'secondary',
  publie: 'success',
  programme: 'info',
  // newsletter
  confirme: 'success',
  desinscrit: 'secondary',
  a_reconfirmer: 'warning',
  // investisseurs
  nouveau: 'primary',
  contacte: 'info',
  qualifie: 'warning',
  due_diligence: 'primary',
  engage: 'success',
  perdu: 'danger',
  approuve: 'success',
  refuse: 'danger',
  suspendu: 'secondary',
  // média (traitement)
  aucun: 'secondary',
  pret: 'success',
  erreur: 'danger',
  // chatbot
  bot: 'secondary',
  pending_human: 'warning',
  human: 'info',
  closed: 'dark',
}

/** Texte lisible d'un statut technique. */
export const labelOf = (value) => {
  if (value == null) return '—'
  return String(value).replace(/_/g, ' ')
}

/** Classe de couleur Bootstrap pour un statut donné. */
export const badgeClass = (value) => BADGES[value] ?? 'secondary'
