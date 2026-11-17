// Ouverture directe de WhatsApp après l'envoi d'un formulaire (préférence validée)

/** Envoie le formulaire puis ouvre WhatsApp directement (onglet ouvert pendant le clic, sinon la page elle-même) */
export async function submitAndOpenWhatsApp(send) {
  const tab = window.open('', '_blank')
  try {
    const res = await send()
    const url = res?.data?.whatsapp?.url
    if (url) {
      if (tab) tab.location.href = url
      else window.location.assign(url)
    } else tab?.close()
    return res
  } catch (err) {
    tab?.close()
    throw err
  }
}

/* ───────── Secours quand l'API est injoignable (connexion coupée ou trop lente) ───────── */

/** L'erreur vient du réseau (ou d'un serveur en panne) et non d'un refus de l'API (champ invalide, créneau pris…) */
export const isNetworkFailure = (err) => !err?.status || err.status >= 500

/** Lien WhatsApp vers NUFOTEC construit sur le téléphone, sans l'API (numéro du site, ou celui par défaut) */
export function localWhatsAppUrl(settings, lines) {
  const number = (settings?.contact_whatsapp || settings?.site_phone || '+257 79 666 439').replace(/\D/g, '')
  const text = lines.filter((l) => l !== false && l != null).join('\n')   // '' = ligne vide voulue
  return `https://wa.me/${number}?text=${encodeURIComponent(text)}`
}

/** Dirige l'onglet ouvert pendant le clic vers WhatsApp (ou la page elle-même si l'onglet a été bloqué) */
export function openIn(tab, url) {
  if (tab) tab.location.href = url
  else window.location.assign(url)
}
