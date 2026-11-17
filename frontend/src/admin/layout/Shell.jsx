// Coquille de l'interface (NiceAdmin) : en-tête + barre latérale + contenu.
// Gère le repli de la barre latérale via la classe body.toggle-sidebar (comme le template).
import { Suspense, useEffect, useState } from 'react'
import { Outlet, useLocation } from 'react-router-dom'
import Header from './Header'
import Sidebar from './Sidebar'
import { getUser, refreshUser } from '../lib/auth'
import Spinner from '../ui/Spinner'
import NetworkBanner from '../../shared/NetworkBanner'

const YEAR = new Date().getFullYear()

export default function Shell({ nav, brand = 'NUFOTEC' }) {
  const [collapsed, setCollapsed] = useState(false)
  const [user, setUser] = useState(getUser)
  const { pathname } = useLocation()

  // Rafraîchit les permissions à l'ouverture (elles peuvent avoir changé côté API).
  useEffect(() => {
    refreshUser().then((fresh) => { if (fresh) setUser(fresh) })
  }, [])

  useEffect(() => {
    document.body.classList.toggle('toggle-sidebar', collapsed)
  }, [collapsed])

  // Nouvelle page : retour en haut
  useEffect(() => {
    window.scrollTo(0, 0)
  }, [pathname])

  return (
    <>
      <Header brand={brand} user={user} onToggle={() => setCollapsed((v) => !v)} />
      <Sidebar nav={nav} brand={brand} user={user} />
      <main id="main" className="main">
        <Suspense fallback={<Spinner />}>
          <Outlet />
        </Suspense>
      </main>
      <footer id="footer" className="footer">
        <div className="copyright">
          &copy; {YEAR} <strong><span>NUFOTEC BURUNDI</span></strong>. Tous droits réservés.
        </div>
      </footer>
      <a href="#" className="back-to-top d-flex align-items-center justify-content-center" onClick={(e) => { e.preventDefault(); window.scrollTo({ top: 0, behavior: 'smooth' }) }}>
        <i className="bi bi-arrow-up-short"></i>
      </a>
      <NetworkBanner />
    </>
  )
}
