// Espace médecin — agenda : GET /medecin/consultations (statut et période filtrables).
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { formatDateTime } from '../../lib/format'
import { ROUTES } from '../../lib/routes'
import useApi from '../../lib/useApi'
import DataTable from '../../ui/DataTable'
import PageTitle from '../../ui/PageTitle'
import StatusBadge from '../../ui/StatusBadge'

const FILTERS = [
  ['', 'Toutes'],
  ['en_attente', 'En attente'],
  ['confirmee', 'Confirmées'],
  ['en_cours', 'En cours'],
  ['terminee', 'Terminées'],
]

export default function DoctorDashboard() {
  const [page, setPage] = useState(1)
  const [statut, setStatut] = useState('')

  const agenda = useApi('/medecin/consultations', { page, per_page: 10, ...(statut ? { statut } : {}) }, [statut])
  const items = agenda.data?.items ?? []

  const columns = [
    {
      key: 'debut',
      label: 'Créneau',
      render: (c) => <span className="fw-semibold">{formatDateTime(c.debut)}</span>,
    },
    {
      key: 'patient',
      label: 'Patient',
      render: (c) => <Link to={ROUTES.doctorConsultation(c.numero)}>{c.patient?.nom ? `${c.patient.prenom ?? ''} ${c.patient.nom}`.trim() : 'Patient'}</Link>,
    },
    { key: 'statut', label: 'Statut', render: (c) => <StatusBadge value={c.statut} /> },
    { key: 'paiement_statut', label: 'Paiement', render: (c) => <StatusBadge value={c.paiement_statut} /> },
    {
      key: 'action',
      label: '',
      className: 'text-end',
      render: (c) => (
        <Link to={ROUTES.doctorConsultation(c.numero)} className="btn btn-sm btn-outline-primary">
          <i className="bi bi-eye"></i> Dossier
        </Link>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Mon agenda" crumbs={[{ label: 'Tableau de bord' }]} />
      <section className="section">
        <DataTable
          title={`Mes consultations (${agenda.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={agenda.data?.meta}
          onPage={setPage}
          loading={agenda.loading}
          error={agenda.error}
          emptyText={{ title: 'Aucune consultation', text: 'Votre agenda est vide pour cette période.' }}
          toolbar={
            <select className="form-select form-select-sm w-auto" value={statut} onChange={(e) => { setStatut(e.target.value); setPage(1) }}>
              {FILTERS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
            </select>
          }
        />
      </section>
    </>
  )
}
