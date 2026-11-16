// Abonnés newsletter — GET /admin/contenu/newsletter + export + reconfirmation + suppression.
import { useState } from 'react'
import api, { downloadCsv } from '../../../lib/api'
import { formatDateTime } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

const STATUTS = ['a_reconfirmer', 'en_attente', 'confirme', 'desinscrit']

export default function Newsletter() {
  const [version, setVersion] = useState(0)
  const [page, setPage] = useState(1)
  const [statut, setStatut] = useState('')
  const [busy, setBusy] = useState(false)

  const newsletter = useApi('/admin/contenu/newsletter', { page, per_page: 15, ...(statut ? { statut } : {}) }, [version])
  const items = newsletter.data?.items ?? []

  const remove = async (id) => {
    if (!window.confirm('Supprimer cet abonné ?')) return
    try { await api.delete(`/admin/contenu/newsletter/${id}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }

  const reconfirm = async () => {
    setBusy(true)
    try {
      const r = await api.post('/admin/contenu/newsletter/reconfirmation')
      window.alert(r?.emails != null ? `${r.emails} email(s) envoyé(s)` : 'Emails envoyés')
    } catch (err) {
      window.alert(err.message)
    } finally {
      setBusy(false)
    }
  }

  const exportCsv = async () => {
    setBusy(true)
    try { await downloadCsv('/admin/contenu/newsletter/export.csv', {}, `newsletter_${new Date().toISOString().slice(0, 10)}.csv`) }
    catch { window.alert("L'export a échoué.") }
    finally { setBusy(false) }
  }

  const columns = [
    { key: 'email', label: 'Email' },
    { key: 'telephone', label: 'Téléphone', render: (s) => s.telephone || '—' },
    { key: 'statut', label: 'Statut', render: (s) => <StatusBadge value={s.statut} /> },
    { key: 'langue', label: 'Langue', render: (s) => s.langue || '—' },
    { key: 'inscrit_le', label: 'Inscrit le', render: (s) => formatDateTime(s.inscrit_le) },
    {
      key: 'action', label: '', className: 'text-end',
      render: (s) => (
        <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(s.id)}>
          <i className="bi bi-trash"></i>
        </button>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Newsletter" crumbs={[{ label: 'Contenu', to: ROUTES.contenu }, { label: 'Newsletter' }]} />
      <section className="section">
        <DataTable
          title={`Abonnés (${newsletter.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={newsletter.data?.meta}
          onPage={setPage}
          loading={newsletter.loading}
          error={newsletter.error}
          emptyText={{ title: 'Aucun abonné' }}
          toolbar={
            <>
              <select className="form-select form-select-sm w-auto" value={statut}
                      onChange={(e) => { setStatut(e.target.value); setPage(1) }}>
                <option value="">Tous les statuts</option>
                {STATUTS.map((s) => <option key={s} value={s}>{s.replace(/_/g, ' ')}</option>)}
              </select>
              <button type="button" className="btn btn-sm btn-outline-secondary" onClick={exportCsv} disabled={busy}>
                <i className="bi bi-download me-1"></i> Exporter
              </button>
              <button type="button" className="btn btn-sm btn-outline-primary" onClick={reconfirm} disabled={busy}>
                <i className="bi bi-envelope-check me-1"></i> Reconfirmation
              </button>
            </>
          }
        />
      </section>
    </>
  )
}
