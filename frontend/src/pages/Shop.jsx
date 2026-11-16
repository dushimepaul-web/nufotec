// Boutique — conversion de sections/Produits_View.php : mêmes 4 sections (bandeau, recherche et filtres,
// grille de produits, pagination), au design du site (couleurs du logo, aucun dégradé, mobile d'abord).
// Les filtres sont dans l'adresse (?q=&categorie=&tri=&par=&page=) : un lien filtré peut être partagé.
import { useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { ROUTES } from '../lib/routes'
import { usePageMeta } from '../lib/site'
import useApi from '../lib/useApi'
import ProductCard, { ProductCardSkeleton } from '../ui/ProductCard'
import '../styles/shop.css'

const PER_PAGE = [12, 24, 36, 48]
const SORTS = [
  ['recent', 'Les plus récents'],
  ['populaire', 'Les plus demandés'],
  ['prix_asc', 'Prix croissant'],
  ['prix_desc', 'Prix décroissant'],
  ['nom', 'Nom (A à Z)'],
]

/** Numéros de pages avec « … » : 1 … 4 5 [6] 7 8 … 20 */
function pageList(current, total) {
  const pages = []
  const start = Math.max(1, current - 2)
  const end = Math.min(total, current + 2)
  if (start > 1) pages.push(1, ...(start > 2 ? ['…'] : []))
  for (let i = start; i <= end; i++) pages.push(i)
  if (end < total) pages.push(...(end < total - 1 ? ['…'] : []), total)
  return pages
}

export default function Shop() {
  usePageMeta('Boutique', 'Produits phytomédicinaux NUFOTEC : commandez en ligne, confirmation sur WhatsApp.')
  const [params, setParams] = useSearchParams()
  const q = params.get('q') ?? ''
  const categorie = params.get('categorie') ?? ''
  const tri = params.get('tri') ?? 'recent'
  const par = Number(params.get('par')) || 12
  const page = Number(params.get('page')) || 1
  const [search, setSearch] = useState(q)

  /** Met à jour un ou plusieurs filtres ; tout changement de filtre ramène à la page 1 */
  const update = (changes, keepPage = false) => {
    const next = new URLSearchParams(params)
    Object.entries(changes).forEach(([k, v]) => (v === '' || v == null ? next.delete(k) : next.set(k, String(v))))
    if (!keepPage) next.delete('page')
    setParams(next, { replace: !('page' in changes) })
  }

  // Recherche : on attend la fin de la frappe avant d'interroger l'API
  useEffect(() => {
    if (search === q) return undefined
    const timer = setTimeout(() => update({ q: search.trim() }), 400)
    return () => clearTimeout(timer)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search])

  const query = { page, per_page: par, tri, ...(q ? { q } : {}), ...(categorie ? { categorie } : {}) }
  const products = useApi('/produits', query)
  const categories = useApi('/produits/categories')
  const visibleCategories = (categories.data ?? []).filter((c) => c.produits > 0 || c.slug === categorie)

  const items = products.data?.items ?? []
  const meta = products.data?.meta
  const goToPage = (p) => {
    update({ page: p }, true)
    document.getElementById('shop-grid')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
  const from = meta ? (meta.page - 1) * meta.per_page + 1 : 0
  const to = meta ? Math.min(meta.page * meta.per_page, meta.total) : 0
  const hasFilters = Boolean(q || categorie)

  return (
    <div className="shop">
      {/* 1. BANDEAU */}
      <section className="shop_banner">
        <img className="shop_banner-bg" src="/logo.jpeg" alt="" />
        <div className="shop_wrap">
          <nav className="shop_crumbs" aria-label="Fil d'Ariane">
            <Link to={ROUTES.home}>Accueil</Link> <i className="bi bi-chevron-right"></i> <span>Boutique</span>
          </nav>
          <h1><i className="bi bi-shop me-2"></i>Notre catalogue</h1>
          <p>Des produits naturels à base de plantes médicinales, commandés en ligne et confirmés sur WhatsApp.</p>
        </div>
      </section>

      <div className="shop_wrap">
        {/* 2. RECHERCHE ET FILTRES */}
        <section className="shop_filters" aria-label="Recherche et filtres">
          <div className="shop_search">
            <i className="bi bi-search"></i>
            <input type="search" placeholder="Rechercher un produit, une indication…" aria-label="Rechercher un produit"
                   value={search} onChange={(e) => setSearch(e.target.value)} />
            {search && (
              <button type="button" aria-label="Effacer la recherche" onClick={() => { setSearch(''); update({ q: '' }) }}>
                <i className="bi bi-x-lg"></i>
              </button>
            )}
          </div>

          <div className="shop_cats" role="tablist" aria-label="Catégories">
            <button type="button" role="tab" aria-selected={!categorie} className={!categorie ? 'active' : ''} onClick={() => update({ categorie: '' })}>
              <i className="bi bi-grid"></i> Tous
            </button>
            {visibleCategories.map((c) => (
              <button key={c.slug} type="button" role="tab" aria-selected={categorie === c.slug}
                      className={categorie === c.slug ? 'active' : ''} onClick={() => update({ categorie: c.slug })}>
                {c.nom.charAt(0) + c.nom.slice(1).toLowerCase()} <span>{c.produits}</span>
              </button>
            ))}
          </div>

          <div className="shop_tools">
            <label>
              <span>Trier</span>
              <select value={tri} onChange={(e) => update({ tri: e.target.value === 'recent' ? '' : e.target.value })}>
                {SORTS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
              </select>
            </label>
            <label>
              <span>Afficher</span>
              <select value={par} onChange={(e) => update({ par: e.target.value === '12' ? '' : e.target.value })}>
                {PER_PAGE.map((n) => <option key={n} value={n}>{n}</option>)}
              </select>
            </label>
          </div>
        </section>

        {/* 3. GRILLE DE PRODUITS */}
        <section id="shop-grid" className="shop_grid-section" aria-live="polite">
          {meta && !products.loading && (
            <p className="shop_count">
              {meta.total} produit{meta.total > 1 ? 's' : ''}{q && <> pour « <b>{q}</b> »</>}
              {hasFilters && (
                <button type="button" onClick={() => { setSearch(''); update({ q: '', categorie: '' }) }}>Effacer les filtres</button>
              )}
            </p>
          )}

          {products.error ? (
            <div className="shop_empty">
              <i className="bi bi-exclamation-triangle"></i>
              <h3>Erreur de chargement</h3>
              <p>{products.error.message}</p>
              <button type="button" className="shop_btn" onClick={() => update({}, true)}>Réessayer</button>
            </div>
          ) : !products.loading && items.length === 0 ? (
            <div className="shop_empty">
              <i className="bi bi-box-seam"></i>
              <h3>Aucun produit trouvé</h3>
              <p>Essayez un autre mot ou une autre catégorie.</p>
              {hasFilters && <button type="button" className="shop_btn" onClick={() => { setSearch(''); update({ q: '', categorie: '' }) }}>Voir tous les produits</button>}
            </div>
          ) : (
            <div className="pcard-grid">
              {products.loading
                ? Array.from({ length: Math.min(par, 8) }, (_, i) => <ProductCardSkeleton key={i} />)
                : items.map((p) => <ProductCard key={p.uuid} product={p} animate={false} />)}
            </div>
          )}
        </section>

        {/* 4. PAGINATION */}
        {meta && meta.pages > 1 && (
          <nav className="shop_pagination" aria-label="Pages">
            <button type="button" disabled={page <= 1} onClick={() => goToPage(page - 1)} aria-label="Page précédente">
              <i className="bi bi-chevron-left"></i>
            </button>
            {pageList(page, meta.pages).map((p, i) => (p === '…'
              ? <span key={`gap-${i}`} className="gap">…</span>
              : <button key={p} type="button" className={p === page ? 'active' : ''} aria-current={p === page ? 'page' : undefined} onClick={() => goToPage(p)}>{p}</button>))}
            <button type="button" disabled={page >= meta.pages} onClick={() => goToPage(page + 1)} aria-label="Page suivante">
              <i className="bi bi-chevron-right"></i>
            </button>
          </nav>
        )}
        {meta && meta.total > 0 && (
          <p className="shop_results-info"><i className="bi bi-bar-chart me-1"></i> Affichage de <b>{from}</b> à <b>{to}</b> sur <b>{meta.total}</b> produits</p>
        )}
      </div>
    </div>
  )
}
