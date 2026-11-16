// Page 404.
import { Link } from 'react-router-dom'

export default function NotFound() {
  return (
    <main id="main" className="main">
      <section className="section">
        <div className="container text-center py-5">
          <h1 className="display-1 fw-bold text-secondary">404</h1>
          <h2 className="mb-3">Page introuvable</h2>
          <p className="text-muted">La page que vous recherchez n'existe pas ou a été déplacée.</p>
          <Link to="/" className="btn btn-primary rounded-pill px-4">Retour à l'accueil</Link>
        </div>
      </section>
    </main>
  )
}
