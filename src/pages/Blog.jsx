// Actualités — conversion de Home_page/Blog_View.php : mêmes sections (bandeau ; grille d'articles avec catégorie,
// « À la une », date, temps de lecture, auteur, vues, mots-clés ; pagination ou état vide ; colonne : recherche,
// catégories, populaires, newsletter, « Suivez-nous »). Filtres dans l'adresse (?q=&categorie=&page=).
import { useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { formatDate } from '../lib/format'
import { ROUTES } from '../lib/routes'
import { cleanSearch } from '../lib/sanitize'
import { usePageMeta } from '../lib/site'
import useApi from '../lib/useApi'
import { NewsThumb, NewsletterWidget, PopularWidget, SocialWidget } from '../ui/NewsWidgets'
import PageBanner from '../ui/PageBanner'
import '../styles/news.css'

const PER_PAGE = 10

function pageList(current, total) {
  const pages = []
  const start = Math.max(1, current - 2)
  const end = Math.min(total, current + 2)
  if (start > 1) pages.push(1, ...(start > 2 ? ['…'] : []))
  for (let i = start; i <= end; i++) pages.push(i)
  if (end < total) pages.push(...(end < total - 1 ? ['…'] : []), total)
  return pages
}

function ArticleCard({ a }) {
  return (
    <article className="nw_card">
      <Link to={ROUTES.article(a.slug)} className="nw_card-img" tabIndex={-1} aria-hidden="true">
        <NewsThumb src={a.image} />
        {a.categorie && <span className="nw_cat">{a.categorie}</span>}
        {a.a_la_une && <span className="nw_star"><i className="bi bi-star-fill"></i> À la une</span>}
      </Link>
      <div className="nw_card-body">
        <div className="nw_meta"><span><i className="bi bi-calendar3"></i> {formatDate(a.date_publication)}</span><span><i className="bi bi-clock"></i> {a.temps_lecture} min</span></div>
        <h3><Link to={ROUTES.article(a.slug)}>{a.titre}</Link></h3>
        {a.resume && <p>{a.resume}</p>}
        <div className="nw_meta"><span><i className="bi bi-person"></i> {a.auteur || 'NUFOTEC'}</span><span><i className="bi bi-eye"></i> {a.vues} vue{a.vues > 1 ? 's' : ''}</span></div>
        {a.tags?.length > 0 && <div className="nw_tags">{a.tags.slice(0, 3).map((t) => <Link key={t} to={`${ROUTES.blog}?q=${encodeURIComponent(t)}`}>#{t}</Link>)}</div>}
        <Link to={ROUTES.article(a.slug)} className="nw_more">Lire la suite <i className="bi bi-arrow-right"></i></Link>
      </div>
    </article>
  )
}

function CardSkeleton() {
  return <div className="nw_card nw_skeleton" aria-hidden="true"><div className="nw_card-img"></div><div className="nw_card-body"><span></span><span></span><span></span></div></div>
}

export default function Blog() {
  usePageMeta('Actualités', 'Les dernières nouvelles de NUFOTEC BURUNDI : recherche, produits, événements et innovations.')
  const [params, setParams] = useSearchParams()
  const q = params.get('q') ?? ''
  const categorie = params.get('categorie') ?? ''
  const page = Number(params.get('page')) || 1
  const [search, setSearch] = useState(q)

  const update = (changes, keepPage = false) => {
    const next = new URLSearchParams(params)
    Object.entries(changes).forEach(([k, v]) => (v === '' || v == null ? next.delete(k) : next.set(k, String(v))))
    if (!keepPage) next.delete('page')
    setParams(next, { replace: !('page' in changes) })
  }
  useEffect(() => {
    if (search === q) return undefined
    const t = setTimeout(() => update({ q: search.trim() }), 400)
    return () => clearTimeout(t)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search])

  const list = useApi('/actualites', { page, per_page: PER_PAGE, ...(q ? { q } : {}), ...(categorie ? { categorie } : {}) })
  const items = list.data?.items ?? []
  const meta = list.data?.meta
  const cats = list.data?.categories_compte ?? []
  const filtered = Boolean(q || categorie)
  const clear = () => { setSearch(''); update({ q: '', categorie: '' }) }
  const goToPage = (p) => { update({ page: p }, true); document.getElementById('nw-list')?.scrollIntoView({ behavior: 'smooth', block: 'start' }) }

  return (
    <div className="nw">
      {/* 1. BANDEAU */}
      <PageBanner crumbs={[['Actualités']]} badge={<><i className="bi bi-newspaper"></i> News & Actualités</>} icon="bi-megaphone"
                  title="Actualités" subtitle="Recherche, produits, événements et innovations : suivez la vie de NUFOTEC BURUNDI." />

      <div className="nw_wrap">
        {/* 2. ARTICLES */}
        <main id="nw-list" className="nw_main" aria-live="polite">
          {filtered && meta && !list.loading && (
            <p className="nw_count">
              {meta.total} article{meta.total > 1 ? 's' : ''}{q && <> pour « <b>{q}</b> »</>}{categorie && <> dans <b>{categorie}</b></>}
              <button type="button" onClick={clear}>Effacer les filtres</button>
            </p>
          )}
          {list.error ? (
            <div className="nw_empty"><i className="bi bi-exclamation-triangle"></i><h3>Erreur de chargement</h3><p>{list.error.message}</p></div>
          ) : !list.loading && items.length === 0 ? (
            <div className="nw_empty">
              <i className="bi bi-newspaper"></i>
              <h3>Aucun article disponible</h3>
              <p>{filtered ? 'Aucun résultat pour votre recherche. Essayez avec d’autres mots.' : 'Revenez bientôt pour découvrir nos nouveaux contenus.'}</p>
              {filtered && <button type="button" className="nw_btn" onClick={clear}><i className="bi bi-arrow-left"></i> Voir tous les articles</button>}
            </div>
          ) : (
            <div className="nw_grid">
              {list.loading ? Array.from({ length: 4 }, (_, i) => <CardSkeleton key={i} />) : items.map((a) => <ArticleCard key={a.slug} a={a} />)}
            </div>
          )}
          {meta && meta.pages > 1 && !list.loading && (
            <nav className="nw_pages" aria-label="Pagination">
              <button type="button" disabled={page <= 1} onClick={() => goToPage(page - 1)} aria-label="Page précédente"><i className="bi bi-chevron-left"></i></button>
              {pageList(page, meta.pages).map((p, i) => p === '…' ? <span key={`d${i}`}>…</span>
                : <button key={p} type="button" className={p === page ? 'active' : ''} aria-current={p === page ? 'page' : undefined} onClick={() => goToPage(p)}>{p}</button>)}
              <button type="button" disabled={page >= meta.pages} onClick={() => goToPage(page + 1)} aria-label="Page suivante"><i className="bi bi-chevron-right"></i></button>
            </nav>
          )}
        </main>

        {/* 3. COLONNE */}
        <aside className="nw_side">
          <div className="nw_widget">
            <h3><i className="bi bi-search"></i> Rechercher</h3>
            <form className="nw_input" role="search" onSubmit={(e) => { e.preventDefault(); update({ q: search.trim() }) }}>
              <i className="bi bi-search"></i>
              <input type="search" value={search} placeholder="Rechercher un article…" aria-label="Rechercher un article"
                     onChange={(e) => setSearch(cleanSearch(e.target.value))} />
            </form>
          </div>
          {cats.length > 0 && (
            <div className="nw_widget">
              <h3><i className="bi bi-folder2-open"></i> Catégories</h3>
              <ul className="nw_cats">
                <li><button type="button" className={!categorie ? 'active' : ''} onClick={() => update({ categorie: '' })}><span>Toutes</span></button></li>
                {cats.map((c) => (
                  <li key={c.nom}>
                    <button type="button" className={categorie === c.nom ? 'active' : ''} onClick={() => update({ categorie: c.nom })}>
                      <span>{c.nom}</span><em>{c.articles}</em>
                    </button>
                  </li>
                ))}
              </ul>
            </div>
          )}
          <PopularWidget items={list.data?.populaires} title="Populaires" />
          <NewsletterWidget />
          <SocialWidget />
        </aside>
      </div>
    </div>
  )
}
