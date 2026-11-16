// Petits outils partagés pour les experts (liste, fiche, réservation)
export const DOCTOR_FALLBACK = '/img/doctor-default.svg'

/** « Eric Hakizimana » ; ignore les noms vides ou réduits à « . » (anciennes données) */
export const doctorName = (d) => [d?.prenom, d?.nom].filter((x) => x && x.trim() && x.trim() !== '.').join(' ') || 'Expert'

/** Tarifs prêts à afficher, tels que renvoyés par l'API (même règle que le message WhatsApp) :
 *  « Résidant du Burundi : 40.000 BIF » et « Résidant à l'étranger : 50.00 USD/EUR (Équivalent en BIF : 325.000-375.000 BIF) » */
export function doctorPrices(d) {
  const t = d?.tarifs ?? {}
  const bif = (v) => (v == null ? null : `${Math.trunc(Number(v)).toLocaleString('de-DE')} BIF`)
  const foreign = (v) => (v == null ? null : `${Number(v).toFixed(2)} ${d?.devise_etranger || 'USD'}`)
  return {
    burundi: t.burundi ?? bif(d?.prix_bif),
    etranger: t.etranger ?? foreign(d?.prix_usd),
    equivalent: t.equivalent_bif ?? null,
  }
}
