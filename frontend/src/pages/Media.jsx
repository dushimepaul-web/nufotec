// Médias — conversion de Home_page/Media_View.php : mêmes sections (recherche, menu Accueil / Tendances / Actualités /
// Témoignages, catégories, grille de médias, témoignages en vidéo dans une fenêtre, état vide), au design du site.
// Le choix est dans l'adresse (?rubrique=&categorie=&q=&tri=&page=) : un lien filtré peut être partagé.
import { useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { formatDate } from '../lib/format'
import { CATEGORIES, ICON } from '../lib/media'
import { ROUTES } from '../lib/routes'
import { cleanSearch } from '../lib/sanitize'
import { usePageMeta } from '../lib/site'
import useApi, { useApiAll } from '../lib/useApi'
import MediaCard, { CardSkeleton } from '../ui/MediaCard'
import Modal from '../ui/Modal'
import '../styles/media.css'

const SECTIONS = [
  ['', 'bi-house-door', 'Accueil'],
  ['tendances', 'bi-fire', 'Tendances'],
  ['actualites', 'bi-newspaper', 'Actualités'],
  ['temoignages', 'bi-chat-quote', 'Témoignages'],
]
const SORTS = [['recent', 'Les plus récents'], ['populaire', 'Les plus vus'], ['mieux_notes', 'Les mieux notés']]

function Empty({ icon = 'bi-play-circle', title, children }) {
  return (
    <div className="md_empty">
      <i className={`bi ${icon}`}></i>
      <h3>{title}</h3>
      {children && <p>{children}</p>}
    </div>
  )
}

function Testimonials() {
  const { data, loading } = useApi('/temoignages')
  const [open, setOpen] = useState(null)
  const items = data ?? []
  if (loading) return <div className="md_grid">{Array.from({ length: 4 }, (_, i) => <CardSkeleton key={i} />)}</div>
  if (!items.length) return <Empty icon="bi-chat-quote" title="Aucun témoignage disponible" />
  return (
    <>
      <div className="md_grid">
        {items.map((t) => (
          <button key={t.id} type="button" className="md_card" onClick={() => setOpen(t)}>
            <div className="md_thumb">
              {t.miniature ? <img src={t.miniature} alt="" loading="lazy" /> : <i className="bi bi-chat-quote"></i>}
              <span className="md_play"><i className="bi bi-play-fill"></i></span>
              <span className="md_type"><i className="bi bi-chat-quote"></i></span>
            </div>
            <div className="md_info">
              <h3 title={t.titre}>{t.titre}</h3>
              <p><span>NUFOTEC BURUNDI</span><b className="md_sep"> · </b><span>Témoignage</span></p>
            </div>
          </button>
        ))}
      </div>
      <Modal open={Boolean(open)} onClose={() => setOpen(null)} title={open?.titre} icon="bi-chat-quote" size="lg" dark>
        {open?.youtube_id ? (
          <div className="md_video">
            <iframe src={`https://www.youtube-nocookie.com/embed/${open.youtube_id}?autoplay=1&rel=0`} title={open.titre}
                    allow="autoplay; encrypted-media; picture-in-picture" allowFullScreen></iframe>
          </div>
        ) : open && <p>Vidéo indisponible. <a href={open.video_url} target="_blank" rel="noopener noreferrer">Ouvrir le lien</a></p>}
      </Modal>
    </>
  )
}

function News() {
  const { data, loading } = useApi('/actualites', { per_page: 12 })
  const items = data?.items ?? []
  if (loading) return <div className="md_grid">{Array.from({ length: 3 }, (_, i) => <CardSkeleton key={i} />)}</div>
  if (!items.length) return <Empty icon="bi-newspaper" title="Aucune actualité pour le moment">Revenez bientôt pour suivre nos nouvelles.</Empty>
  return (
    <div className="md_news">
      {items.map((a) => (
        <Link key={a.slug} to={ROUTES.article(a.slug)} className="md_news-card">
          <div className="md_news-thumb">
            {a.image ? <img src={a.image} alt="" loading="lazy" /> : <i className="bi bi-newspaper"></i>}
            {a.categorie && <span>{a.categorie}</span>}
          </div>
          <div className="md_news-body">
            <small><i className="bi bi-calendar3"></i> {formatDate(a.date_publication ?? a.created_at)}</small>
            <h3>{a.titre}</h3>
            {a.resume && <p>{a.resume}</p>}
            <em>Lire plus <i className="bi bi-arrow-right"></i></em>
          </div>
        </Link>
      ))}
    </div>
  )
}

export default function Media() {
  usePageMeta('Médias', 'Vidéos, audios, documents et témoignages NUFOTEC : la santé par les plantes, expliquée.')
  const [params, setParams] = useSearchParams()
  const rubrique = params.get('rubrique') ?? ''
  const categorie = params.get('categorie') ?? ''
  const q = params.get('q') ?? ''
  const tri = params.get('tri') ?? 'recent'
  const [search, setSearch] = useState(q)
  const [menuOpen, setMenuOpen] = useState(false)
  const [toast, setToast] = useState('')
  useEffect(() => {
    if (!toast) return undefined
    const t = setTimeout(() => setToast(''), 2800)
    return () => clearTimeout(t)
  }, [toast])

  const update = (changes, keepPage = false) => {
    const next = new URLSearchParams(params)
    Object.entries(changes).forEach(([k, v]) => (v === '' || v == null ? next.delete(k) : next.set(k, String(v))))
    if (!keepPage) next.delete('page')
    setParams(next, { replace: !('page' in changes) })
  }
  useEffect(() => {
    if (search === q) return undefined
    const timer = setTimeout(() => update({ q: search.trim(), rubrique: '' }), 400)
    return () => clearTimeout(timer)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search])

  const isLibrary = rubrique === '' || rubrique === 'tendances'
  const query = { tri: rubrique === 'tendances' ? 'tendance' : tri, ...(q ? { q } : {}), ...(categorie ? { categorie } : {}) }
  const medias = useApiAll(isLibrary ? '/medias' : null, query)
  const cats = useApi('/medias/categories')
  const counts = Object.fromEntries((cats.data ?? []).map((c) => [c.categorie, c.medias]))
  const visibleCats = CATEGORIES.filter(([k]) => k !== 'link' || counts.link > 0 || categorie === 'link')
  const items = medias.data?.items ?? []
  const meta = medias.data?.meta

  const choose = (changes) => { setMenuOpen(false); if ('rubrique' in changes && changes.rubrique !== '') setSearch(''); update(changes) }
  const clear = () => { setSearch(''); update({ q: '', categorie: '' }) }
  const section = SECTIONS.find(([k]) => k === rubrique) ?? SECTIONS[0]
  const catLabel = CATEGORIES.find(([k]) => k === categorie)?.[2]
  const heading = q ? `Résultats pour « ${q} »` : catLabel ?? (rubrique ? section[2] : 'Toute la médiathèque')

  return (
    <div className="md">
      {/* 1. BARRE DE RECHERCHE (comme YouTube : pas de bandeau sur cette page) */}
      <div className="md_bar">
        <form className="md_search" role="search" onSubmit={(e) => { e.preventDefault(); update({ q: search.trim(), rubrique: '' }) }}>
          <input type="search" placeholder="Rechercher une vidéo, un audio, un document…" aria-label="Rechercher un média"
                 value={search} maxLength={100} onChange={(e) => setSearch(cleanSearch(e.target.value))} />
          {search && <button type="button" className="md_search-clear" aria-label="Effacer la recherche" onClick={clear}><i className="bi bi-x-lg"></i></button>}
          <button type="submit" className="md_search-go" aria-label="Rechercher"><i className="bi bi-search"></i></button>
        </form>
      </div>

      <div className="md_wrap">
        {/* 2. MENU : rubriques et catégories */}
        <aside className={`md_side ${menuOpen ? 'open' : ''}`} aria-label="Rubriques de la médiathèque">
          <button type="button" className="md_side-toggle" aria-expanded={menuOpen} onClick={() => setMenuOpen((o) => !o)}>
            <i className={`bi ${catLabel ? ICON[categorie] : section[1]}`}></i> {catLabel ?? section[2]} <i className="bi bi-chevron-down ms-auto"></i>
          </button>
          <div className="md_side-body">
            <nav className="md_menu">
              {SECTIONS.map(([key, icon, label]) => (
                <button key={key || 'accueil'} type="button" className={rubrique === key && !categorie && !q ? 'active' : ''}
                        onClick={() => choose({ rubrique: key, categorie: '', q: '' })}>
                  <i className={`bi ${icon}`}></i><span>{label}</span>
                </button>
              ))}
            </nav>
            <h4>Catégories</h4>
            <nav className="md_menu">
              {visibleCats.map(([key, icon, label]) => (
                <button key={key} type="button" className={categorie === key ? 'active' : ''}
                        onClick={() => choose({ rubrique: '', categorie: key })}>
                  <i className={`bi ${icon}`}></i><span>{label}</span>{counts[key] > 0 && <em>{counts[key]}</em>}
                </button>
              ))}
            </nav>
          </div>
        </aside>

        {/* 3. CONTENU */}
        <main id="md-content" className="md_main" aria-live="polite">
          <div className="md_head">
            <div>
              <h2>{rubrique === 'temoignages' ? 'Témoignages' : rubrique === 'actualites' ? 'Actualités' : heading}</h2>
              {isLibrary && meta && !medias.loading && <small>{meta.total} média{meta.total > 1 ? 's' : ''}</small>}
            </div>
            {rubrique === '' && (
              <label className="md_sort"><span>Trier</span>
                <select value={tri} onChange={(e) => update({ tri: e.target.value === 'recent' ? '' : e.target.value })}>
                  {SORTS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                </select>
              </label>
            )}
          </div>

          {rubrique === 'temoignages' ? <Testimonials /> : rubrique === 'actualites' ? <News /> : medias.error ? (
            <Empty icon="bi-exclamation-triangle" title="Erreur de chargement">{medias.error.message}</Empty>
          ) : !medias.loading && items.length === 0 ? (
            /* 4. ÉTAT VIDE */
            <Empty title={q || categorie ? 'Aucun média trouvé' : 'Aucun média disponible'}>
              {q || categorie ? 'Essayez un autre mot ou une autre catégorie.' : 'Revenez plus tard pour découvrir du nouveau contenu.'}
              {(q || categorie) && <><br /><button type="button" className="md_btn" onClick={clear}>Voir toute la médiathèque</button></>}
            </Empty>
          ) : (
            <div className="md_grid">
              {medias.loading ? Array.from({ length: 8 }, (_, i) => <CardSkeleton key={i} />) : items.map((m) => <MediaCard key={m.uuid} m={m} notify={setToast} />)}
            </div>
          )}
        </main>
      </div>
      {toast && <div className="md_toast" role="status">{toast}</div>}
    </div>
  )
}
