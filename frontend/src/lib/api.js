// Client de l'API Nufotec (FastAPI) — voir nufotec-api/docs/FRONTEND.md
// Toutes les réponses : { success, message, data } ou { success:false, error:{ code, message, details } }.
import axios from 'axios'
import { installResilience } from '../shared/network'

export const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api/v1'

const TOKENS_KEY = 'nuf_tokens'

export const tokens = {
  get: () => JSON.parse(localStorage.getItem(TOKENS_KEY) || 'null'),
  set: (t) => localStorage.setItem(TOKENS_KEY, JSON.stringify(t)),
  clear: () => localStorage.removeItem(TOKENS_KEY),
}

/** Erreur de l'API : toujours se baser sur `code` (le texte dépend de la langue). */
export class ApiError extends Error {
  constructor({ code = 'NETWORK_ERROR', message = 'Connexion impossible. Vérifiez votre réseau.', details = null }, status = 0) {
    super(message)
    this.code = code
    this.details = details
    this.status = status
  }

  /** Message du champ `field` dans une erreur de validation (422). */
  field(name) {
    return Array.isArray(this.details) ? this.details.find((d) => d.field === name)?.message : undefined
  }
}

const http = axios.create({ baseURL: API_URL, timeout: 30000, headers: { 'Accept-Language': 'fr' } })
// Connexions lentes ou coupées : nouvelles tentatives, clé anti-doublon stable, état du réseau (voir shared/network.js)
installResilience(http)

http.interceptors.request.use((config) => {
  const t = tokens.get()
  if (t?.access_token) config.headers.Authorization = `Bearer ${t.access_token}`
  return config
})

let refreshing = null

http.interceptors.response.use(
  (res) => res,
  async (err) => {
    const status = err.response?.status ?? 0
    const body = err.response?.data?.error
    const original = err.config
    // Jeton expiré : on le renouvelle une seule fois, puis on rejoue la requête
    if (status === 401 && body?.code === 'TOKEN_EXPIRED' && tokens.get()?.refresh_token && !original._retried) {
      original._retried = true
      refreshing ??= axios
        .post(`${API_URL}/auth/refresh`, { refresh_token: tokens.get().refresh_token })
        .then((r) => tokens.set(r.data.data.tokens ?? r.data.data))
        .catch(() => tokens.clear())
        .finally(() => { refreshing = null })
      await refreshing
      if (tokens.get()) return http(original)
    }
    if (status === 401 && ['TOKEN_REUSED', 'TOKEN_INVALID'].includes(body?.code)) tokens.clear()
    throw new ApiError(body ?? {}, status)
  },
)

/** Renvoie directement `data` (et `message` sur demande). */
async function call(method, url, { params, data, headers, key, noRetry } = {}) {
  const h = { ...(headers ?? {}), ...(key ? { 'Idempotency-Key': key } : {}) }
  const res = await http.request({ method, url, params, data, headers: h, noRetry })
  return res.data?.data
}

export const api = {
  get: (url, params) => call('get', url, { params }),
  /** opts.key : Idempotency-Key imposée (renvoi plus tard d'une demande mise en attente) */
  post: (url, data, opts) => call('post', url, { data, ...(opts ?? {}) }),
  put: (url, data) => call('put', url, { data }),
  patch: (url, data) => call('patch', url, { data }),
  delete: (url) => call('delete', url),
  /** Pour afficher le message de succès de l'API (ex. newsletter). */
  postWithMessage: async (url, data) => (await http.post(url, data)).data,
}

export default api
