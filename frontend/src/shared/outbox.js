// Boîte d'envoi : une demande qui n'a pas pu partir (pas de réseau) est gardée sur le téléphone et renvoyée
// automatiquement au retour de la connexion, avec SA clé d'idempotence d'origine : même si le premier envoi
// était en fait arrivé, l'API ne crée pas de doublon. Jamais de mot de passe dans cette boîte.
import { idempotencyKey, onNetworkChange } from './network'

const KEY = 'nuf_outbox'
const MAX_AGE = 3 * 24 * 3600 * 1000      // une demande de plus de 3 jours n'est plus renvoyée
const listeners = new Set()
let sender = null                          // fonction (url, data, key) => Promise, fournie par le client API
let flushing = false

function read() {
  try {
    const list = JSON.parse(localStorage.getItem(KEY) || '[]')
    return list.filter((e) => Date.now() - e.at < MAX_AGE)
  } catch {
    return []
  }
}

function write(list) {
  localStorage.setItem(KEY, JSON.stringify(list))
  listeners.forEach((fn) => fn(list.length))
}

export function pendingCount() { return read().length }

export function onOutboxChange(fn) {
  listeners.add(fn)
  return () => listeners.delete(fn)
}

/** Clé à utiliser pour un envoi : la garder si la demande est mise en attente. */
export const keyFor = (url, data) => idempotencyKey('post', url, data)

/** Met une demande en attente (POST JSON, sans mot de passe). */
export function queue(url, data, key, label) {
  if (JSON.stringify(data ?? {}).match(/"(password|mot_de_passe|new_password)"/)) return
  write([...read().filter((e) => e.key !== key), { url, data, key, label, at: Date.now() }])
}

/** Renvoie les demandes en attente (au retour du réseau, au démarrage, toutes les minutes). */
export async function flush() {
  if (flushing || !sender) return
  const list = read()
  if (!list.length) return
  flushing = true
  const keep = []
  for (const e of list) {
    try {
      await sender(e.url, e.data, e.key)
    } catch (err) {
      // toujours pas de réseau : on garde ; refus de l'API (créneau pris, donnée invalide…) : on abandonne
      if (!err?.status || err.status >= 500) keep.push(e)
    }
  }
  write(keep)
  flushing = false
}

/** À appeler une fois par application avec la fonction d'envoi (api.post). */
export function startOutbox(send) {
  if (sender) return
  sender = send
  onNetworkChange((s) => { if (s === 'ok') flush() })
  if (typeof window !== 'undefined') {
    window.addEventListener('online', () => flush())
    setInterval(() => { if (pendingCount()) flush() }, 60000)
    setTimeout(flush, 3000)
  }
}
