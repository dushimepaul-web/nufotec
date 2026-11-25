// Mise en page des pages publiques : en-tête + contenu + pied de page
import { Suspense, useEffect } from 'react'
import { Outlet, useLocation } from 'react-router-dom'
import AOS from 'aos'
import 'aos/dist/aos.css'
import api from '../lib/api'
import NetworkBanner from '../shared/NetworkBanner'
import { startOutbox } from '../shared/outbox'
import Footer from './Footer'
import Header from './Header'
import '../styles/theme-logo.css' // couleurs du logo (après header.css et footer.css)

export default function PublicLayout() {
  const { pathname } = useLocation()

  useEffect(() => {
    AOS.init({ duration: 800, once: true, offset: 60 })
    // demandes mises en attente faute de réseau : renvoyées au retour de la connexion (même clé, pas de doublon)
    startOutbox((url, data, key) => api.post(url, data, { key }))
  }, [])

  // Nouvelle page : retour en haut, animations recalculées, visite comptée (statistiques anonymes)
  useEffect(() => {
    window.scrollTo(0, 0)
    setTimeout(() => AOS.refreshHard(), 50)
    // statistique non essentielle : jamais retentée (on n'use pas les données d'une connexion faible)
    api.post('/stats/visite', { page: pathname, referer: document.referrer || null }, { noRetry: true }).catch(() => {})
  }, [pathname])

  return (
    <>
      <Header />
      <main>
        <Suspense fallback={<div className="d-flex justify-content-center py-5" role="status" aria-label="Chargement"><div className="spinner-border text-success" /></div>}>
          <Outlet />
        </Suspense>
      </main>
      <Footer />
      <NetworkBanner />
    </>
  )
}
