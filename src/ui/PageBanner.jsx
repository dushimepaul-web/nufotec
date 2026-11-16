// Bandeau de haut de page : le logo NUFOTEC en arrière-plan sous un voile vert uni (préférence validée),
// fil d'Ariane, badge, titre et sous-titre. Réutilisé par toutes les pages intérieures.
import { Link } from 'react-router-dom'
import { ROUTES } from '../lib/routes'
import './page-banner.css'

export default function PageBanner({ crumbs = [], badge, icon, title, subtitle, children }) {
  return (
    <section className="pbanner">
      <img className="pbanner-bg" src="/logo.jpeg" alt="" />
      <div className="pbanner-wrap">
        <nav className="pbanner-crumbs" aria-label="Fil d'Ariane">
          <Link to={ROUTES.home}>Accueil</Link>
          {crumbs.map(([label, to]) => (
            <span key={label}> <i className="bi bi-chevron-right"></i> {to ? <Link to={to}>{label}</Link> : <span>{label}</span>}</span>
          ))}
        </nav>
        {badge && <span className="pbanner-badge">{badge}</span>}
        {title && <h1>{icon && <i className={`bi ${icon} me-2`}></i>}{title}</h1>}
        {subtitle && <p>{subtitle}</p>}
        {children}
      </div>
    </section>
  )
}
