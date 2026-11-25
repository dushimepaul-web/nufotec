// Liste des produits — GET /admin/produits (recherche + pagination serveur).
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { formatPrice } from '../../../lib/format'
import { getUser } from '../../../lib/auth'
import { can } from '../../../lib/permissions'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

const FALLBACK = '/default-product.svg'

export default function ProduitsList() {
  const [page, setPage] = useState(1)
  const [search, setSearch] = useState('')
  const [q, setQ] = useState('')

  useEffect(() => {
    const timer = setTimeout(() => setQ(search.trim()), 350)
    return () => clearTimeout(timer)
  }, [search])

  const onSearch = (v) => { setSearch(v); setPage(1) }

  const produits = useApi('/admin/produits', { page, per_page: 15, ...(q ? { q } : {}) })
  const items = produits.data?.items ?? []

  const columns = [
    {
      key: 'titre',
      label: 'Produit',
      render: (p) => (
        <div className="d-flex align-items-center gap-2">
          {p.image && <img loading="lazy" decoding="async" src={p.image} alt="" width="36" height="36" className="rounded object-fit-cover" onError={(e) => { e.currentTarget.src = FALLBACK }} />}
          <div>
            <div className="fw-semibold">
              <Link to={ROUTES.produit(p.uuid)}>{p.titre}</Link>
              {p.vedette && <i className="bi bi-star-fill text-warning ms-1" title="Vedette"></i>}
            </div>
            <small className="text-muted">{p.categorie?.nom || 'Sans catégorie'}</small>
          </div>
        </div>
      ),
    },
    { key: 'prix', label: 'Prix', render: (p) => formatPrice(p) },
    {
      key: 'stock',
      label: 'Stock',
      render: (p) => (p.stock == null ? 'Non suivi' : p.stock > 0 ? `${p.stock}` : 'Épuisé'),
    },
    { key: 'publie', label: 'Publication', render: (p) => <StatusBadge value={p.publie ? 'publie' : 'brouillon'} /> },
    {
      key: 'action',
      label: '',
      className: 'text-end',
      render: (p) => (
        <Link to={ROUTES.produit(p.uuid)} className="btn btn-sm btn-outline-primary">
          <i className="bi bi-pencil"></i> Modifier
        </Link>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Produits" crumbs={[{ label: 'Produits' }]} />
      <section className="section">
        <DataTable
          title={`Produits (${produits.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={produits.data?.meta}
          onPage={setPage}
          loading={produits.loading}
          error={produits.error}
          emptyText={{ title: 'Aucun produit', text: q ? `Aucun résultat pour « ${q} »` : undefined }}
          toolbar={
            <>
              <div className="search-bar">
                <form className="search-form d-flex align-items-center" onSubmit={(e) => e.preventDefault()}>
                  <input type="text" placeholder="Rechercher…" title="Rechercher" value={search}
                         onChange={(e) => onSearch(e.target.value)} />
                  <button type="submit" title="Rechercher"><i className="bi bi-search"></i></button>
                </form>
              </div>
              {can(getUser(), 'produits', 'create') && (
                <Link to={ROUTES.produitNouveau} className="btn btn-primary">
                  <i className="bi bi-plus-lg me-1"></i> Nouveau produit
                </Link>
              )}
            </>
          }
        />
      </section>
    </>
  )
}
