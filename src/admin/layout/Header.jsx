// En-tête fixe : logo + bouton replier la barre latérale + profil (déconnexion).
import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import api from '../lib/api'
import { clearSession, getUser, initials } from '../lib/auth'
import { ROUTES } from '../lib/routes'

export default function Header({ brand, onToggle, user: current }) {
  const navigate = useNavigate()
  const user = current ?? getUser()
  const [open, setOpen] = useState(false)
  const [leaving, setLeaving] = useState(false)

  const logout = async () => {
    if (leaving) return
    setLeaving(true)
    try { await api.post('/auth/logout') } catch { /* déjà expiré */ }
    clearSession()
    navigate(ROUTES.login)
  }

  return (
    <header id="header" className="header fixed-top d-flex align-items-center">
      <div className="d-flex align-items-center justify-content-between">
        <Link to={ROUTES.adminHome} className="logo d-flex align-items-center">
          <img src="/logo.jpeg" alt="NUFOTEC" />
          <span className="d-none d-lg-block">{brand}</span>
        </Link>
        <i className="bi bi-list toggle-sidebar-btn" onClick={onToggle}></i>
      </div>

      <nav className="header-nav ms-auto">
        <ul className="d-flex align-items-center">
          <li className="nav-item dropdown pe-3">
            <a
              className="nav-link nav-profile d-flex align-items-center pe-0"
              href="#"
              data-bs-toggle="dropdown"
              onClick={(e) => { e.preventDefault(); setOpen((v) => !v) }}
            >
              {user?.photo_url
                ? <img src={user.photo_url} alt="Profil" className="rounded-circle" />
                : <span className="rounded-circle d-flex align-items-center justify-content-center" style={{ width: 36, height: 36, background: '#0f4c3a', color: '#fff', fontSize: 14 }}>{initials(user)}</span>}
              <span className="d-none d-md-block dropdown-toggle ps-2">{user ? [user.prenom, user.nom].filter(Boolean).join(' ') : 'Compte'}</span>
            </a>

            <ul className={`dropdown-menu dropdown-menu-end dropdown-menu-arrow profile${open ? ' show' : ''}`}>
              <li className="dropdown-header">
                <h6>{user ? [user.prenom, user.nom].filter(Boolean).join(' ') : 'Compte'}</h6>
                <span>{user?.role?.nom ?? ''}</span>
              </li>
              <li><hr className="dropdown-divider" /></li>
              <li>
                <button type="button" className="dropdown-item d-flex align-items-center" onClick={logout} disabled={leaving}>
                  <i className="bi bi-box-arrow-right"></i>
                  <span>{leaving ? 'Déconnexion…' : 'Se déconnecter'}</span>
                </button>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
    </header>
  )
}
