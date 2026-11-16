// Carte produit façon site de commerce (accueil, boutique, produits similaires)
import { Link } from 'react-router-dom'
import { PRODUCT_FALLBACK, fallback, formatPrice, formatUsd } from '../lib/format'
import { ROUTES } from '../lib/routes'
import './product-card.css'

export default function ProductCard({ product: p, delay = 0, animate = true }) {
  return (
    <Link to={ROUTES.product(p.slug)} className="pcard" {...(animate ? { 'data-aos': 'fade-up', 'data-aos-delay': delay } : {})}>
      <div className="pcard-img">
        {p.vedette && <span className="pcard-badge star">Vedette</span>}
        {p.nouveau && <span className="pcard-badge new">Nouveau</span>}
        <img src={p.image || PRODUCT_FALLBACK} alt={p.titre} loading="lazy" onError={fallback(PRODUCT_FALLBACK)} />
      </div>
      <div className="pcard-info">
        <h3 className="pcard-name">{p.titre}</h3>
        <div className="pcard-price">{formatPrice(p)}</div>
        <div className="pcard-sub">
          <span>{formatUsd(p)}</span>
          {!p.en_stock && <span className="out">Épuisé</span>}
        </div>
        <span className="pcard-cta"><i className="bi bi-whatsapp"></i> Commander</span>
      </div>
    </Link>
  )
}

/** Carte vide affichée pendant le chargement */
export function ProductCardSkeleton() {
  return (
    <div className="pcard pcard-skeleton" aria-hidden="true">
      <div className="pcard-img"></div>
      <div className="pcard-info">
        <span className="sk sk-line"></span>
        <span className="sk sk-line short"></span>
        <span className="sk sk-price"></span>
      </div>
    </div>
  )
}
