// Mise en forme partagée (prix, dates, images de secours)

export const PRODUCT_FALLBACK = '/default-product.svg'

/** Prix principal : en BIF, sinon en USD */
export function formatPrice(p) {
  if (p?.prix_bif != null) return `${Number(p.prix_bif).toLocaleString('fr-FR', { maximumFractionDigits: 0 })} BIF`
  if (p?.prix_usd != null) return `${Number(p.prix_usd).toLocaleString('fr-FR')} USD`
  return ''
}

/** Équivalent en USD affiché sous le prix BIF */
export function formatUsd(p) {
  return p?.prix_usd != null && p?.prix_bif != null ? `≈ ${Number(p.prix_usd).toLocaleString('fr-FR')} USD` : ''
}

export function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) : ''
}

/** onError d'une image : remplace par l'image de secours (une seule fois) */
export const fallback = (src) => (e) => { if (!e.currentTarget.src.endsWith(src)) e.currentTarget.src = src }

/** Créneau lisible : « Aujourd'hui · 14 h 30 », « Demain · 09 h 00 » ou « lundi 18 novembre · 10 h 00 » */
export function formatSlot(iso) {
  const d = new Date(iso)
  const today = new Date()
  const tomorrow = new Date(today.getTime() + 864e5)
  const time = d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }).replace(':', ' h ')
  if (d.toDateString() === today.toDateString()) return `Aujourd'hui · ${time}`
  if (d.toDateString() === tomorrow.toDateString()) return `Demain · ${time}`
  return `${d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' })} · ${time}`
}

/** « 08:00:00 » → « 08 h 00 » */
export const formatHour = (t) => (t ? t.slice(0, 5).replace(':', ' h ') : '')

/** Date du jour au format AAAA-MM-JJ (heure locale) */
export const isoDay = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`

/** Montant : 40000 → « 40 000 BIF » */
export const formatMoney = (value, currency) => (value == null ? '' : `${Number(value).toLocaleString('fr-FR', { maximumFractionDigits: 2 })} ${currency}`)
