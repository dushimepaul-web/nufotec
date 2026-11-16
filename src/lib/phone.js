// Indicatifs téléphoniques et numéros complets (partagé par tous les formulaires)
export const BURUNDI_ID = 194
export const DEFAULT_COUNTRIES = [{ id: BURUNDI_ID, pays: 'Burundi', indicatif: '+257', iso2: 'BI' }]

/** Indicatif téléphonique d'un pays (« +1-684 » → « +1684 ») */
export const dialCode = (c) => (c?.indicatif ? `+${c.indicatif.replace(/\D/g, '')}` : '')

/** Numéro complet envoyé à l'API : indicatif du pays + numéro local (sauf si la personne a déjà tapé un numéro en +…) */
export function fullPhone(local, country) {
  const raw = (local || '').trim()
  if (!raw) return ''
  if (raw.startsWith('+')) return raw.replace(/\s/g, '')
  return `${dialCode(country)}${raw.replace(/\D/g, '').replace(/^0+/, '')}`
}
