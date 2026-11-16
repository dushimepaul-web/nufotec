// Filtrage des champs de formulaire (sécurité et qualité des données), appliqué pendant la frappe.
// Première barrière seulement : l'API revalide et nettoie tout de son côté (ne jamais se fier au navigateur seul).
// React échappe déjà tout ce qu'il affiche (pas d'injection HTML) ; ici on empêche en plus les caractères
// inutiles ou dangereux (balises, caractères de contrôle) et les textes trop longs.

export const LIMITS = { name: 100, city: 100, phone: 20, address: 500, note: 500, search: 40, quantity: 100 }

// Caractères de contrôle invisibles (sauf retour à la ligne et tabulation) et caractères « bidi »
// qui peuvent inverser l'affichage d'un texte pour tromper le lecteur.
const CONTROL = /[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F\u200B-\u200F\u202A-\u202E\u2066-\u2069]/g
// Balises et caractères de code : < > { } [ ] ` \ | ^ ~ $
const CODE_CHARS = /[<>{}[\]`\\|^~$]/g

const cut = (s, max) => (s.length > max ? s.slice(0, max) : s)

/** Nom de personne : lettres (avec accents), espaces, apostrophe, tiret et point */
export const cleanName = (v) => cut(v.replace(CONTROL, '').replace(/[^\p{L}\p{M} '’.-]/gu, '').replace(/\s{2,}/g, ' ').replace(/^\s+/, ''), LIMITS.name)

/** Ville : comme un nom, chiffres en plus (« Bujumbura 2 ») */
export const cleanCity = (v) => cut(v.replace(CONTROL, '').replace(/[^\p{L}\p{M}\p{N} '’.,-]/gu, '').replace(/\s{2,}/g, ' ').replace(/^\s+/, ''), LIMITS.city)

/** Téléphone : chiffres et espaces ; un « + » seulement au début (numéro international complet) */
export function cleanPhone(v) {
  const plus = v.trimStart().startsWith('+') ? '+' : ''
  return cut(plus + v.replace(/[^\d ]/g, '').replace(/\s{2,}/g, ' ').replace(/^\s+/, ''), LIMITS.phone)
}

/** Texte libre (adresse, remarque) : tout sauf balises, caractères de code et caractères de contrôle */
export const cleanText = (v, max = LIMITS.address) => cut(v.replace(CONTROL, '').replace(CODE_CHARS, '').replace(/[ \t]{2,}/g, ' '), max)

/** Recherche : lettres, chiffres, espaces, + ' - . ( ) */
export const cleanSearch = (v) => cut(v.replace(CONTROL, '').replace(/[^\p{L}\p{M}\p{N} +'’().-]/gu, ''), LIMITS.search)

/** Quantité entière entre 1 et max (vide autorisé pendant la frappe) */
export function cleanQuantity(v, max = LIMITS.quantity) {
  const digits = String(v).replace(/\D/g, '').slice(0, 3)
  if (digits === '') return ''
  return String(Math.min(max, Math.max(1, Number(digits))))
}

/** Nettoyage final avant envoi : espaces superflus retirés partout */
export const trimAll = (obj) => Object.fromEntries(Object.entries(obj).map(([k, v]) => [k, typeof v === 'string' ? v.trim().replace(/\s{2,}/g, ' ') : v]))
