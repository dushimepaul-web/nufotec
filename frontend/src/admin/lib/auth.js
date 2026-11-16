// Utilisateur connecté (mémorisé après POST /auth/login).
import api, { tokens } from './api'

const USER_KEY = 'nuf_user'

export function getUser() {
  if (!tokens.get()) return null
  return JSON.parse(localStorage.getItem(USER_KEY) || 'null')
}

/** Recharge le profil (et ses permissions) depuis l'API, puis met à jour le stockage local. */
export async function refreshUser() {
  if (!tokens.get()) return null
  try {
    const user = await api.get('/auth/me')
    localStorage.setItem(USER_KEY, JSON.stringify(user))
    return user
  } catch {
    return null
  }
}

export function saveSession(data) {
  tokens.set(data.tokens)
  localStorage.setItem(USER_KEY, JSON.stringify(data.user))
}

export function clearSession() {
  tokens.clear()
  localStorage.removeItem(USER_KEY)
}

/** Rôle de l'utilisateur connecté (admin | medecin | user). */
export const roleOf = (user) => user?.role?.slug ?? null

/** Initiales pour l'avatar. */
export function initials(user) {
  const name = [user?.prenom, user?.nom].filter(Boolean).join(' ').trim()
  if (!name) return '?'
  const parts = name.split(/\s+/)
  return (parts.length >= 2 ? parts[0][0] + parts[1][0] : name.slice(0, 2)).toUpperCase()
}
