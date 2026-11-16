// Pipeline des investisseurs — GET /admin/investisseurs (filtres + pagination) + export CSV.
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { downloadCsv } from '../../../lib/api'
import { getUser } from '../../../lib/auth'
import { can } from '../../../lib/permissions'
import { formatDate } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

const STATUTS = ['nouveau', 'contacte', 'qualifie', 'due_diligence', 'engage', 'perdu']
const SOURCES = [['direct', 'Direct'], ['courtier', 'Courtier']]

export default function InvestisseursList() {
  const [page, setPage] = useState(1)
  const [statut, setStatut] = useState('')
  const [source, setSource] = useState('')
  const [search, setSearch] = useState('')
  const [q, setQ] = useState('')
  const [exporting, setExporting] = useState(false)

  useEffect(() => {
    const timer = setTimeout(() => setQ(search.trim()), 350)
    return () => clearTimeout(timer)
  }, [search])

  const onSearch = (v) => { setSearch(v); setPage(1) }

  const params = { page, per_page: 15, ...(statut ? { statut } : {}), ...(source ? { source } : {}), ...(q ? { q } : {}) }
  const investisseurs = useApi('/admin/investisseurs', params)
  const items = investisseurs.data?.items ?? []

  const exportCsv = async () => {
    setExporting(true)
    try { await downloadCsv('/admin/investisseurs/export.csv', { statut, source, q }, `investisseurs_${new Date().toISOString().slice(0, 10)}.csv`) }
    catch { window.alert("L'export a échoué.") }
    finally { setExporting(false) }
  }

  const columns = [
    {
      key: 'nom_complet', label: 'Investisseur',
      render: (i) => (
        <div>
          <Link to={ROUTES.investisseur(i.uuid)} className="fw-semibold">{i.nom_complet}</Link>
          {i.organisation && <div className="text-muted small">{i.organisation}</div>}
        </div>
      ),
    },
    { key: 'email', label: 'Email' },
    { key: 'pays', label: 'Pays', render: (i) => i.pays || '—' },
    { key: 'montant', label: 'Montant', render: (i) => i.montant || '—' },
    { key: 'statut', label: 'Statut', render: (i) => <StatusBadge value={i.statut} /> },
    { key: 'source', label: 'Source', render: (i) => <span className="text-capitalize">{i.source}</span> },
    { key: 'responsable', label: 'Responsable', render: (i) => i.responsable || '—' },
    { key: 'created_at', label: 'Reçu le', render: (i) => formatDate(i.created_at) },
  ]

  return (
    <>
      <PageTitle title="Investisseurs" crumbs={[{ label: 'Investisseurs' }]} />
      <section className="section">
        <DataTable
          title={`Pipeline (${investisseurs.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={investisseurs.data?.meta}
          onPage={setPage}
          loading={investisseurs.loading}
          error={investisseurs.error}
          emptyText={{ title: 'Aucun investisseur', text: 'Aucun résultat pour ces filtres.' }}
          toolbar={
            <>
              <div className="search-bar">
                <form className="search-form d-flex align-items-center" onSubmit={(e) => e.preventDefault()}>
                  <input type="text" placeholder="Rechercher…" title="Rechercher" value={search}
                         onChange={(e) => onSearch(e.target.value)} />
                  <button type="submit" title="Rechercher"><i className="bi bi-search"></i></button>
                </form>
              </div>
              <select className="form-select form-select-sm w-auto" value={statut} onChange={(e) => { setStatut(e.target.value); setPage(1) }}>
                <option value="">Tous les statuts</option>
                {STATUTS.map((s) => <option key={s} value={s}>{s.replace(/_/g, ' ')}</option>)}
              </select>
              <select className="form-select form-select-sm w-auto" value={source} onChange={(e) => { setSource(e.target.value); setPage(1) }}>
                <option value="">Toutes les sources</option>
                {SOURCES.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
              </select>
              <button type="button" className="btn btn-sm btn-outline-secondary" onClick={exportCsv}
                      disabled={exporting || !can(getUser(), 'investisseurs', 'export')}>
                {exporting ? <span className="spinner-border spinner-border-sm me-1"></span> : <i className="bi bi-download me-1"></i>}
                Exporter
              </button>
            </>
          }
        />
      </section>
    </>
  )
}
