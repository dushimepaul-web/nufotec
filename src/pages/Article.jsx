// Article — conversion de Home_page/Blog_Detail_View.php : mêmes sections (en-tête avec catégorie, titre, auteur,
// date, temps de lecture, vues ; image, résumé, contenu, mots-clés, auteur, article précédent / suivant ; articles
// similaires ; colonne : partager, « Suivez-nous », les plus lus). Le contenu est nettoyé par l'API à l'enregistrement.
import { Link, useParams } from 'react-router-dom'
import { formatDate } from '../lib/format'
import { ROUTES } from '../lib/routes'
import { usePageMeta } from '../lib/site'
import useApi from '../lib/useApi'
import { NewsThumb, PopularWidget, ShareWidget, SocialWidget } from '../ui/NewsWidgets'
import '../styles/news.css'

export default function Article() {
  const { slug } = useParams()
  const { data: a, loading, error } = useApi(`/actualites/${slug}`, undefined, [slug])
  usePageMeta(a?.titre ?? 'Actualité', a?.resume || undefined)

  if (loading && !a) {
    return <div className="nw"><div className="nw_head nw_head-skel"></div><div className="nw_wrap"><div className="nw_main"><div className="nw_article nw_skeleton-article"><span></span><span></span><span></span></div></div></div></div>
  }
  if (error || !a) {
    return (
      <div className="nw"><div className="nw_wrap nw_wrap-single"><div className="nw_empty">
        <i className="bi bi-newspaper"></i>
        <h3>{error?.status === 404 ? 'Article introuvable' : 'Erreur de chargement'}</h3>
        <p>{error?.status === 404 ? "Cet article n'existe plus ou n'est pas encore publié." : error?.message}</p>
        <Link className="nw_btn" to={ROUTES.blog}><i className="bi bi-arrow-left"></i> Toutes les actualités</Link>
      </div></div></div>
    )
  }

  const author = a.auteur || 'NUFOTEC BURUNDI'
  return (
    <div className="nw">
      {/* 1. EN-TÊTE */}
      <header className="nw_head">
        {a.image ? <img className="nw_head-bg" src={a.image} alt="" /> : <img className="nw_head-bg logo" src="/logo.jpeg" alt="" />}
        <div className="nw_head-wrap">
          <nav className="nw_crumbs" aria-label="Fil d'Ariane">
            <Link to={ROUTES.home}>Accueil</Link> <i className="bi bi-chevron-right"></i> <Link to={ROUTES.blog}>Actualités</Link>
            {a.categorie && <> <i className="bi bi-chevron-right"></i> <Link to={`${ROUTES.blog}?categorie=${encodeURIComponent(a.categorie)}`}>{a.categorie}</Link></>}
          </nav>
          {a.categorie && <span className="nw_head-cat">{a.categorie}</span>}
          <h1>{a.titre}</h1>
          <div className="nw_head-meta">
            <span><i className="bi bi-person"></i> {author}</span>
            <span><i className="bi bi-calendar3"></i> {formatDate(a.date_publication)}</span>
            <span><i className="bi bi-clock"></i> {a.temps_lecture} min de lecture</span>
            <span><i className="bi bi-eye"></i> {a.vues} vue{a.vues > 1 ? 's' : ''}</span>
          </div>
        </div>
      </header>

      <div className="nw_wrap">
        <main className="nw_main">
          {/* 2. ARTICLE */}
          <article className="nw_article">
            {a.image && <img className="nw_article-img" src={a.image} alt={a.titre} />}
            {a.resume && <p className="nw_lead">{a.resume}</p>}
            <div className="nw_content" dangerouslySetInnerHTML={{ __html: a.contenu || '' }}></div>
            {a.tags?.length > 0 && (
              <div className="nw_tags nw_tags-lg"><i className="bi bi-tags"></i>{a.tags.map((t) => <Link key={t} to={`${ROUTES.blog}?q=${encodeURIComponent(t)}`}>#{t}</Link>)}</div>
            )}
            <div className="nw_author">
              <div className="nw_author-ava">{author.split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase()}</div>
              <div><b>{author}</b><small>Auteur et contributeur des actualités NUFOTEC</small></div>
            </div>
            {(a.precedent || a.suivant) && (
              <nav className="nw_nav" aria-label="Autres articles">
                {a.precedent ? <Link to={ROUTES.article(a.precedent.slug)} className="prev"><small><i className="bi bi-arrow-left"></i> Article précédent</small><b>{a.precedent.titre}</b></Link> : <span></span>}
                {a.suivant && <Link to={ROUTES.article(a.suivant.slug)} className="next"><small>Article suivant <i className="bi bi-arrow-right"></i></small><b>{a.suivant.titre}</b></Link>}
              </nav>
            )}
          </article>

          {/* 3. ARTICLES SIMILAIRES */}
          {a.articles_lies?.length > 0 && (
            <section className="nw_related">
              <h2><i className="bi bi-grid"></i> Articles similaires</h2>
              <div className="nw_related-grid">
                {a.articles_lies.map((r) => (
                  <Link key={r.slug} to={ROUTES.article(r.slug)} className="nw_related-card">
                    <div className="nw_related-img"><NewsThumb src={r.image} /></div>
                    <div><small>{r.categorie}</small><b>{r.titre}</b><span><i className="bi bi-calendar3"></i> {formatDate(r.date_publication)} · <i className="bi bi-eye"></i> {r.vues}</span></div>
                  </Link>
                ))}
              </div>
            </section>
          )}
        </main>

        {/* 4. COLONNE */}
        <aside className="nw_side nw_side-sticky">
          <ShareWidget title={a.titre} />
          <SocialWidget />
          <PopularWidget items={a.populaires} />
        </aside>
      </div>
    </div>
  )
}
