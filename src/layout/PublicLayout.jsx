// Mise en page des pages publiques : en-tête + contenu + pied de page
import { useEffect } from 'react'
import { Outlet, useLocation } from 'react-router-dom'
import AOS from 'aos'
import 'aos/dist/aos.css'
import api from '../lib/api'
import Footer from './Footer'
import Header from './Header'
import '../styles/theme-logo.css' // couleurs du logo (après header.css et footer.css)

export default function PublicLayout() {
  const { pathname } = useLocation()

  useEffect(() => {
    AOS.init({ duration: 800, once: true, offset: 60 })
  }, [])

  // Nouvelle page : retour en haut, animations recalculées, visite comptée (statistiques anonymes)
  useEffect(() => {
    window.scrollTo(0, 0)
    setTimeout(() => AOS.refreshHard(), 50)
    api.post('/stats/visite', { page: pathname, referer: document.referrer || null }).catch(() => {})
  }, [pathname])

  return (
    <>
      <Header />
      <main>
        <Outlet />
      </main>
      <Footer />
    </>
  )
}
