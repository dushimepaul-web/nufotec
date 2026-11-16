// Modération des commentaires — GET /admin/medias/commentaires + publier/refuser.
import { useState } from 'react'
import api from '../../../lib/api'
import { formatDateTime } from '../../../lib/format'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

const STATUTS = ['en_attente', 'publie', 'refuse']

export default function Commentaires() {
  const [version, setVersion] = useState(0)
  const [page, setPage] = useState(1)
  const [statut, setStatut] = useState('en_attente')

  const commentaires = useApi('/admin/medias/commentaires', { statut, page, per_page: 20 }, [version])
  const items = commentaires.data?.items ?? []

  const act = async (fn) => {
    try { await fn(); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }

  const columns = [
    { key: 'auteur', label: 'Auteur', render: (c) => <span className="fw-semibold">{c.auteur}</span> },
    { key: 'texte', label: 'Commentaire' },
    { key: 'email', label: 'Email', render: (c) => c.email || '—' },
    { key: 'statut', label: 'Statut', render: (c) => <StatusBadge value={c.statut} /> },
    { key: 'le', label: 'Le', render: (c) => <span className="text-nowrap small">{formatDateTime(c.le)}</span> },
    {
      key: 'action', label: '', className: 'text-end',
      render: (c) => (
        c.statut === 'en_attente' ? (
          <>
            <button type="button" className="btn btn-sm btn-success me-1" onClick={() => act(() => api.post(`/admin/medias/commentaires/${c.id}/publier`))}>
              <i className="bi bi-check-lg me-1"></i> Publier
            </button>
            <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => act(() => api.post(`/admin/medias/commentaires/${c.id}/refuser`))}>
              <i className="bi bi-x-lg me-1"></i> Refuser
            </button>
          </>
        ) : null
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Commentaires" crumbs={[{ label: 'Médias', to: '/admin/medias' }, { label: 'Commentaires' }]} />
      <section className="section">
        <DataTable
          title={`Commentaires (${commentaires.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={commentaires.data?.meta}
          onPage={setPage}
          loading={commentaires.loading}
          error={commentaires.error}
          emptyText={{ title: 'Aucun commentaire' }}
          toolbar={
            <select className="form-select form-select-sm w-auto" value={statut}
                    onChange={(e) => { setStatut(e.target.value); setPage(1) }}>
              {STATUTS.map((s) => <option key={s} value={s}>{s.replace(/_/g, ' ')}</option>)}
            </select>
          }
        />
      </section>
    </>
  )
}
