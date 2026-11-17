// Résilience réseau commune au site public et à l'administration (connexions lentes ou coupées) :
//  - nouvelles tentatives automatiques (coupure, délai dépassé, serveur occupé) avec attente croissante ;
//  - même Idempotency-Key à chaque tentative ET pour un même formulaire envoyé deux fois (10 min) : pas de doublon ;
//  - état du réseau diffusé (hors ligne / lent / rétabli) pour le bandeau d'information.

const SLOW_MS = 6000            // au-delà : « connexion lente »
const MAX_RETRIES = 3           // en plus du premier essai
const RETRY_STATUS = [408, 425, 429, 500, 502, 503, 504]

/* ───────────── état du réseau ───────────── */

let state = typeof navigator !== 'undefined' && navigator.onLine === false ? 'offline' : 'ok'
const listeners = new Set()

export function networkState() { return state }

export function onNetworkChange(fn) {
  listeners.add(fn)
  return () => listeners.delete(fn)
}

function setState(next) {
  if (next === state) return
  state = next
  listeners.forEach((fn) => fn(state))
}

if (typeof window !== 'undefined') {
  window.addEventListener('offline', () => setState('offline'))
  window.addEventListener('online', () => setState('ok'))
}

/* ───────────── clé d'idempotence ───────────── */

function hash(text) {
  // FNV-1a sur 53 bits : stable, rapide, sans dépendance
  let h1 = 0x811c9dc5, h2 = 0x01000193
  for (let i = 0; i < text.length; i++) {
    const c = text.charCodeAt(i)
    h1 = Math.imul(h1 ^ c, 16777619)
    h2 = Math.imul(h2 ^ c, 2246822519)
  }
  return (h1 >>> 0).toString(16).padStart(8, '0') + (h2 >>> 0).toString(16).padStart(8, '0')
}

/** Même formulaire (même adresse, mêmes données) dans la même tranche de 10 minutes -> même clé. */
export function idempotencyKey(method, url, data) {
  if (typeof FormData !== 'undefined' && data instanceof FormData) return `f-${crypto.randomUUID?.() ?? Date.now()}`
  const bucket = Math.floor(Date.now() / 600000)
  return `k-${hash(`${method}|${url}|${JSON.stringify(data ?? null)}|${bucket}`)}`
}

/* ───────────── nouvelles tentatives ───────────── */

const wait = (ms) => new Promise((r) => setTimeout(r, ms))

/** Attend le retour du réseau (au plus `max` ms) si le téléphone est hors ligne. */
function waitOnline(max = 30000) {
  if (typeof navigator === 'undefined' || navigator.onLine !== false) return Promise.resolve()
  return new Promise((resolve) => {
    const done = () => { window.removeEventListener('online', done); clearTimeout(t); resolve() }
    const t = setTimeout(done, max)
    window.addEventListener('online', done)
  })
}

function retryDelay(attempt, err) {
  const after = Number(err.response?.headers?.['retry-after'])
  if (after > 0) return Math.min(after, 10) * 1000
  return 1000 * 2 ** attempt + Math.random() * 300   // 1 s, 2 s, 4 s (+ un peu de hasard)
}

/** Installe la résilience sur une instance axios. À appeler AVANT les autres intercepteurs de réponse. */
export function installResilience(http) {
  http.interceptors.request.use((config) => {
    const method = (config.method || 'get').toLowerCase()
    if (['post', 'put', 'patch', 'delete'].includes(method) && !config.headers['Idempotency-Key']) {
      config.headers['Idempotency-Key'] = idempotencyKey(method, config.url, config.data)
    }
    config._slowTimer ??= setTimeout(() => { if (state !== 'offline') setState('slow') }, SLOW_MS)
    return config
  })

  http.interceptors.response.use(
    (res) => {
      clearTimeout(res.config._slowTimer)
      if (state !== 'ok') setState('ok')
      return res
    },
    async (err) => {
      const config = err.config
      if (!config) throw err
      clearTimeout(config._slowTimer)
      const status = err.response?.status ?? 0
      const network = !err.response                              // coupure, délai dépassé, serveur injoignable
      const retryable = network || RETRY_STATUS.includes(status)
      const safe = (config.method || 'get').toLowerCase() === 'get' || Boolean(config.headers?.['Idempotency-Key'])
      config._attempt = config._attempt ?? 0
      if (retryable && safe && config._attempt < MAX_RETRIES && !config.noRetry) {
        if (network) setState(typeof navigator !== 'undefined' && navigator.onLine === false ? 'offline' : 'slow')
        await waitOnline()
        await wait(retryDelay(config._attempt, err))
        config._attempt += 1
        config._slowTimer = null
        return http(config)
      }
      if (network) setState(typeof navigator !== 'undefined' && navigator.onLine === false ? 'offline' : 'unreachable')
      throw err
    },
  )
}
