// Paramètres publics du site (nom, logo, contacts…) et réseaux sociaux, chargés une seule fois.
import { createContext, useContext, useEffect, useState } from 'react'
import api from './api'

// Valeurs par défaut identiques au site PHP (get_setting(..., défaut))
const DEFAULTS = {
  site_name: 'NUFOTEC BURUNDI',
  span_site_name: 'Natural Health',
  site_phone: '+257 79 666 439',
  contact_email_invest: 'nufotecburundi2026@gmail.com',
  adresse_siege: 'Bujumbura, Burundi',
  horaires_travail: 'Lun-Ven: 8h-17h',
  agf_description_courte: 'Plateforme de téléconsultation médicale et de produits phytomédicinaux',
}

const SiteContext = createContext({ settings: DEFAULTS, socials: [], ready: false })

export function SiteProvider({ children }) {
  const [state, setState] = useState({ settings: DEFAULTS, socials: [], ready: false })

  useEffect(() => {
    Promise.allSettled([api.get('/site/parametres'), api.get('/reseaux-sociaux')]).then(([s, r]) => {
      const settings = { ...DEFAULTS, ...(s.status === 'fulfilled' ? s.value : {}) }
      setState({ settings, socials: r.status === 'fulfilled' ? r.value : [], ready: true })
      if (settings.favicon_ico) {
        const link = document.querySelector("link[rel='icon']")
        if (link) { link.href = settings.favicon_ico; link.type = 'image/jpeg' }
      }
    })
  }, [])

  return <SiteContext.Provider value={state}>{children}</SiteContext.Provider>
}

export const useSite = () => useContext(SiteContext)

/** Met à jour le titre et la description de la page (SEO), comme le header PHP. */
export function usePageMeta(title, description) {
  const { settings } = useSite()
  useEffect(() => {
    document.title = title ? `${title} - ${settings.site_name}` : settings.site_name
    const meta = document.querySelector("meta[name='description']")
    if (meta) meta.content = description || settings.agf_description_courte || ''
  }, [title, description, settings.site_name, settings.agf_description_courte])
}
