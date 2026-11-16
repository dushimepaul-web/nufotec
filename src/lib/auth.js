// Utilisateur connecté (mémorisé après POST /auth/login ; voir la page de connexion)
import api, { ApiError, tokens } from './api'

const USER_KEY = 'nuf_user'

export function getUser() {
  if (!tokens.get()) return null
  return JSON.parse(localStorage.getItem(USER_KEY) || 'null')
}

export function saveSession(data) {
  tokens.set(data.tokens)
  localStorage.setItem(USER_KEY, JSON.stringify(data.user))
}

export function clearSession() {
  tokens.clear()
  localStorage.removeItem(USER_KEY)
}

/** Initiales pour l'avatar (comme le header PHP) */
export function initials(user) {
  const name = [user?.prenom, user?.nom].filter(Boolean).join(' ').trim()
  if (!name) return '?'
  const parts = name.split(/\s+/)
  return (parts.length >= 2 ? parts[0][0] + parts[1][0] : name.slice(0, 2)).toUpperCase()
}

/** Mêmes règles que l'API (app/core/passwords.py) : 8 caractères, une lettre, un chiffre,
 *  sans le début de l'email ni le prénom / nom (4 lettres ou plus). */
export function passwordProblem(p, { nomComplet = '', email = '' } = {}) {
  if (!p || p.length < 8) return '8 caractères au moins'
  if (!/\p{L}/u.test(p) || !/\d/.test(p)) return 'Au moins une lettre et un chiffre'
  const lower = p.toLowerCase()
  const local = email.split('@')[0].toLowerCase()
  if (local.length >= 4 && lower.includes(local)) return 'Ne doit pas contenir votre email'
  const parts = nomComplet.trim().split(/\s+/)
  const names = parts.length > 1 ? [parts[0], parts.slice(1).join(' ')] : [parts[0]]
  if (names.some((n) => n && n.length >= 4 && lower.includes(n.toLowerCase()))) return 'Ne doit pas contenir votre nom'
  return null
}

/**
 * Crée le compte NUFOTEC depuis un formulaire (investisseur, courtier) puis ouvre la session.
 * Si l'email a déjà un compte, on se connecte avec le mot de passe saisi.
 * Renvoie l'utilisateur ; lève une ApiError (champ « password » si le mot de passe du compte existant est faux).
 */
export async function createOrSignIn({ email, password, nomComplet, telephone, paysId, type }) {
  const current = getUser()
  if (current && current.email?.toLowerCase() === email.toLowerCase()) return current
  const parts = nomComplet.trim().split(/\s+/)
  const prenom = parts.length > 1 ? parts[0] : parts[0]
  const nom = parts.length > 1 ? parts.slice(1).join(' ') : parts[0]
  try {
    const data = await api.post('/auth/register', {
      email, password, prenom, nom, telephone: telephone || null, pays_id: paysId, type_utilisateur: type,
    })
    saveSession(data)
    return data.user
  } catch (err) {
    if (err.code === 'PHONE_TAKEN') {
      // Numéro déjà lié à un autre compte : on crée le compte sans le numéro (il reste dans le formulaire)
      const data = await api.post('/auth/register', { email, password, prenom, nom, pays_id: paysId, type_utilisateur: type })
      saveSession(data)
      return data.user
    }
    if (err.code !== 'EMAIL_TAKEN') throw err
    try {
      const data = await api.post('/auth/login', { login: email, password })
      saveSession(data)
      return data.user
    } catch {
      throw new ApiError({
        code: 'ACCOUNT_EXISTS', message: 'Un compte existe déjà avec cet email : saisissez son mot de passe.',
        details: [{ field: 'password', message: 'Mot de passe de votre compte existant' }],
      }, 409)
    }
  }
}
