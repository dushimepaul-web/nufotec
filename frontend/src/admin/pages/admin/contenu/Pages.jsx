// Pages du site — GET /admin/contenu/pages (liste).
import { useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../../../lib/api'
import { formatDateTime } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

export default function Pages() {
  const [version, setVersion] = useState(0)
  const pages = useApi('/admin/contenu/pages', undefined, [version])
  const items = pages.data ?? []

  const remove = async (id) => {
    if (!window.confirm('Supprimer cette page ?')) return
    try { await api.delete(`/admin/contenu/pages/${id}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }

  const columns = [
    {
      key: 'titre', label: 'Titre',
      render: (p) => <Link to={ROUTES.page(p.id)} className="fw-semibold">{p.titre}</Link>,
    },
    { key: 'slug', label: 'Adresse', render: (p) => <code>/{p.slug}</code> },
    { key: 'publiee', label: 'État', render: (p) => <StatusBadge value={p.publiee ? 'publie' : 'brouillon'} /> },
    { key: 'menu_ordre', label: 'Ordre menu' },
    { key: 'modifiee_le', label: 'Modifiée le', render: (p) => formatDateTime(p.modifiee_le) },
    {
      key: 'action', label: '', className: 'text-end',
      render: (p) => (
        <>
          <Link to={ROUTES.page(p.id)} className="btn btn-sm btn-outline-primary me-1"><i className="bi bi-pencil"></i></Link>
          <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(p.id)}><i className="bi bi-trash"></i></button>
        </>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Pages" crumbs={[{ label: 'Contenu' }, { label: 'Pages' }]} />
      <section className="section">
        <DataTable
          title={`Pages (${items.length})`}
          columns={columns}
          items={items}
          loading={pages.loading}
          error={pages.error}
          emptyText={{ title: 'Aucune page' }}
          toolbar={
            <Link to={ROUTES.pageNouveau} className="btn btn-primary">
              <i className="bi bi-plus-lg me-1"></i> Nouvelle page
            </Link>
          }
        />
      </section>
    </>
  )
}
