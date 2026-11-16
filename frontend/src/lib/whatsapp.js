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
