// Actualités — GET /admin/contenu/actualites (recherche + pagination serveur).
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { formatDateTime } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

export default function Actualites() {
  const [page, setPage] = useState(1)
  const [search, setSearch] = useState('')
  const [q, setQ] = useState('')

  useEffect(() => {
    const timer = setTimeout(() => setQ(search.trim()), 350)
    return () => clearTimeout(timer)
  }, [search])

  const onSearch = (v) => { setSearch(v); setPage(1) }

  const actualites = useApi('/admin/contenu/actualites', { page, per_page: 15, ...(q ? { q } : {}) })
  const items = actualites.data?.items ?? []

  const columns = [
    {
      key: 'titre',
      label: 'Titre',
      render: (a) => (
        <div className="d-flex align-items-center gap-2">
          {a.image && <img loading="lazy" decoding="async" src={a.image} alt="" width="36" height="36" className="rounded object-fit-cover" />}
          <div>
            <div className="fw-semibold">
              <Link to={ROUTES.actualite(a.id)}>{a.titre}</Link>
              {a.a_la_une && <i className="bi bi-star-fill text-warning ms-1" title="À la une"></i>}
            </div>
            {a.resume && <small className="text-muted d-inline-block text-truncate" style={{ maxWidth: 320 }}>{a.resume}</small>}
          </div>
        </div>
      ),
    },
    { key: 'categorie', label: 'Catégorie', render: (a) => a.categorie || '—' },
    { key: 'auteur', label: 'Auteur', render: (a) => a.auteur || '—' },
    { key: 'date_publication', label: 'Publication', render: (a) => formatDateTime(a.date_publication) },
    { key: 'etat', label: 'État', render: (a) => <StatusBadge value={a.etat} /> },
    { key: 'vues', label: 'Vues', render: (a) => a.vues },
    {
      key: 'action',
      label: '',
      className: 'text-end',
      render: (a) => (
        <Link to={ROUTES.actualite(a.id)} state={{ article: a }} className="btn btn-sm btn-outline-primary">
          <i className="bi bi-pencil"></i> Modifier
        </Link>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Actualités" crumbs={[{ label: 'Contenu', to: ROUTES.contenu }, { label: 'Actualités' }]} />
      <section className="section">
        <DataTable
          title={`Actualités (${actualites.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={actualites.data?.meta}
          onPage={setPage}
          loading={actualites.loading}
          error={actualites.error}
          emptyText={{ title: 'Aucun article', text: q ? `Aucun résultat pour « ${q} »` : undefined }}
          toolbar={
            <>
              <div className="search-bar">
                <form className="search-form d-flex align-items-center" onSubmit={(e) => e.preventDefault()}>
                  <input type="text" placeholder="Rechercher…" title="Rechercher" value={search}
                         onChange={(e) => onSearch(e.target.value)} />
                  <button type="submit" title="Rechercher"><i className="bi bi-search"></i></button>
                </form>
              </div>
              <Link to={ROUTES.actualiteNouveau} className="btn btn-primary">
                <i className="bi bi-plus-lg me-1"></i> Nouvel article
              </Link>
            </>
          }
        />
      </section>
    </>
  )
}
