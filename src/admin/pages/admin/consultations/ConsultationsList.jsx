// Liste des consultations — GET /admin/consultations (filtres statut/paiement/recherche + pagination serveur).
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { formatDateTime } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

const STATUTS = ['en_attente', 'confirmee', 'en_cours', 'terminee', 'refusee', 'annulee', 'absent']
const PAIEMENTS = ['en_attente', 'paye', 'echoue', 'rembourse']

const fullName = (p) => [p?.prenom, p?.nom].filter(Boolean).join(' ').trim() || 'Patient'

export default function ConsultationsList() {
  const [page, setPage] = useState(1)
  const [statut, setStatut] = useState('')
  const [paiement, setPaiement] = useState('')
  const [search, setSearch] = useState('')
  const [q, setQ] = useState('')

  useEffect(() => {
    const timer = setTimeout(() => setQ(search.trim()), 350)
    return () => clearTimeout(timer)
  }, [search])

  const resetPage = () => setPage(1)
  const applyStatut = (v) => { setStatut(v); resetPage() }
  const applyPaiement = (v) => { setPaiement(v); resetPage() }
  const onSearch = (v) => { setSearch(v); resetPage() }

  const params = { page, per_page: 15, ...(statut ? { statut } : {}), ...(paiement ? { paiement } : {}), ...(q ? { q } : {}) }
  const consultations = useApi('/admin/consultations', params)
  const items = consultations.data?.items ?? []

  const columns = [
    {
      key: 'numero',
      label: 'N°',
      render: (c) => <Link to={ROUTES.consultation(c.numero)} className="fw-semibold">{c.numero}</Link>,
    },
    { key: 'debut', label: 'Créneau', render: (c) => formatDateTime(c.debut) },
    { key: 'patient', label: 'Patient', render: (c) => fullName(c.patient) },
    { key: 'medecin', label: 'Médecin', render: (c) => <>{fullName(c.medecin)}<br /><small className="text-muted">{c.specialite}</small></> },
    { key: 'statut', label: 'Statut', render: (c) => <StatusBadge value={c.statut} /> },
    { key: 'paiement_statut', label: 'Paiement', render: (c) => <StatusBadge value={c.paiement_statut} /> },
    {
      key: 'montant',
      label: 'Montant',
      render: (c) => (c.prix_ttc != null ? `${Number(c.prix_ttc).toLocaleString('fr-FR', { maximumFractionDigits: 2 })} ${c.devise || ''}` : '—'),
    },
  ]

  return (
    <>
      <PageTitle title="Consultations" crumbs={[{ label: 'Consultations' }]} />
      <section className="section">
        <DataTable
          title={`Consultations (${consultations.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={consultations.data?.meta}
          onPage={setPage}
          loading={consultations.loading}
          error={consultations.error}
          emptyText={{ title: 'Aucune consultation', text: 'Aucun résultat pour ces filtres.' }}
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
              <select className="form-select form-select-sm w-auto" value={paiement} onChange={(e) => applyPaiement(e.target.value)}>
                <option value="">Tous les paiements</option>
                {PAIEMENTS.map((s) => <option key={s} value={s}>{s.replace(/_/g, ' ')}</option>)}
              </select>
            </>
          }
        />
      </section>
    </>
  )
}
