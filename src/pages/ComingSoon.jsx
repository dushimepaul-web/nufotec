// Page provisoire pour les écrans pas encore convertis (remplacée au fur et à mesure)
import { Link, useLocation } from 'react-router-dom'
import { ROUTES } from '../lib/routes'
import { usePageMeta } from '../lib/site'

export default function ComingSoon({ title = 'Page en préparation' }) {
  const { pathname } = useLocation()
  usePageMeta(title)
  return (
    <section className="container py-5 text-center" style={{ minHeight: '50vh' }}>
      <i className="bi bi-tools" style={{ fontSize: 56, color: 'var(--primary)' }}></i>
      <h2 className="mt-3 fw-bold" style={{ color: 'var(--primary)' }}>{title}</h2>
      <p className="text-secondary">Cette page ({pathname}) arrive très bientôt.</p>
      <Link to={ROUTES.home} className="btn btn-success rounded-pill px-4 mt-2">
        <i className="bi bi-house-door me-2"></i>Retour à l'accueil
      </Link>
    </section>
  )
}
