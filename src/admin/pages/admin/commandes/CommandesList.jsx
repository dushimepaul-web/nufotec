// Liste des commandes — GET /admin/commandes (filtre statut + recherche + pagination serveur).
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { downloadCsv } from '../../../lib/api'
import { getUser } from '../../../lib/auth'
import { can } from '../../../lib/permissions'
import { formatDateTime } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

const STATUTS = ['en_attente', 'en_cours', 'livree', 'annulee']

const money = (v, d) => (v != null ? `${Number(v).toLocaleString('fr-FR', { maximumFractionDigits: 2 })} ${d || ''}` : '—')

export default function CommandesList() {
  const [page, setPage] = useState(1)
  const [statut, setStatut] = useState('')
  const [search, setSearch] = useState('')
  const [q, setQ] = useState('')
  const [exporting, setExporting] = useState(false)

  useEffect(() => {
    const timer = setTimeout(() => setQ(search.trim()), 350)
    return () => clearTimeout(timer)
  }, [search])

  const onSearch = (v) => { setSearch(v); setPage(1) }
  const applyStatut = (v) => { setStatut(v); setPage(1) }

  const params = { page, per_page: 15, ...(statut ? { statut } : {}), ...(q ? { q } : {}) }
  const commandes = useApi('/admin/commandes', params)
  const items = commandes.data?.items ?? []

  const exportCsv = async () => {
    setExporting(true)
    try {
      await downloadCsv('/admin/commandes/export.csv', { statut, q }, `commandes_${new Date().toISOString().slice(0, 10)}.csv`)
    } catch {
      window.alert("L'export a échoué.")
    } finally {
      setExporting(false)
    }
  }

  const columns = [
    {
      key: 'numero',
      label: 'N°',
      render: (o) => <Link to={ROUTES.commande(o.numero)} className="fw-semibold">{o.numero}</Link>,
    },
    { key: 'created_at', label: 'Date', render: (o) => formatDateTime(o.created_at) },
    { key: 'client', label: 'Client', render: (o) => <>{o.client}<br /><small className="text-muted">{o.telephone}</small></> },
    { key: 'produit', label: 'Produit', render: (o) => <>{o.produit}<br /><small className="text-muted">× {o.quantite}</small></> },
    { key: 'total', label: 'Total', render: (o) => money(o.total, o.devise) },
    { key: 'statut', label: 'Statut', render: (o) => <StatusBadge value={o.statut} /> },
    {
      key: 'contacte',
      label: 'Contacté',
      render: (o) => (o.whatsapp_contacte
        ? <span className="text-success"><i className="bi bi-check-circle-fill"></i></span>
        : <span className="text-warning"><i className="bi bi-hourglass-split"></i></span>),
    },
  ]

  return (
    <>
      <PageTitle title="Commandes" crumbs={[{ label: 'Commandes' }]} />
      <section className="section">
        <DataTable
          title={`Commandes (${commandes.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={commandes.data?.meta}
          onPage={setPage}
          loading={commandes.loading}
          error={commandes.error}
          emptyText={{ title: 'Aucune commande', text: 'Aucun résultat pour ces filtres.' }}
          toolbar={
            <>
              <div className="search-bar">
                <form className="search-form d-flex align-items-center" onSubmit={(e) => e.preventDefault()}>
                  <input type="text" placeholder="Rechercher…" title="Rechercher" value={search}
                         onChange={(e) => onSearch(e.target.value)} />
                  <button type="submit" title="Rechercher"><i className="bi bi-search"></i></button>
                </form>
              </div>
              <select className="form-select form-select-sm w-auto" value={statut} onChange={(e) => applyStatut(e.target.value)}>
                <option value="">Tous les statuts</option>
                {STATUTS.map((s) => <option key={s} value={s}>{s.replace(/_/g, ' ')}</option>)}
              </select>
              <button type="button" className="btn btn-sm btn-outline-secondary" onClick={exportCsv}
                      disabled={exporting || !can(getUser(), 'commandes', 'export')}>
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
