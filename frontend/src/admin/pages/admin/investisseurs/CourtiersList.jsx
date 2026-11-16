// Courtiers — GET /admin/courtiers + décision (approuver / refuser / suspendre).
import { useState } from 'react'
import api from '../../../lib/api'
import { formatDate } from '../../../lib/format'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

const STATUTS = ['en_attente', 'approuve', 'refuse', 'suspendu']
const DECISIONS = [['approuve', 'Approuver'], ['refuse', 'Refuser'], ['suspendu', 'Suspendre'], ['en_attente', 'Repasser en attente']]

function FicheModal({ broker, onClose }) {
  const detail = useApi(broker ? `/admin/courtiers/${broker.uuid}` : null)
  const b = detail.data ?? broker
  const presentations = b?.presentations ?? []

  return (
    <Modal open={Boolean(broker)} onClose={onClose} icon="bi-person-badge" title={`Fiche — ${broker?.nom_complet}`} size="lg">
      {broker && (
        <div>
          <dl className="row small mb-3">
            <dt className="col-4 text-muted">Cabinet</dt><dd className="col-8">{b.cabinet || '—'}</dd>
            <dt className="col-4 text-muted">Email</dt><dd className="col-8">{b.email}</dd>
            <dt className="col-4 text-muted">Téléphone</dt><dd className="col-8">{b.telephone || '—'}</dd>
            <dt className="col-4 text-muted">WhatsApp</dt><dd className="col-8">{b.whatsapp || '—'}</dd>
            <dt className="col-4 text-muted">Site</dt><dd className="col-8">{b.site_internet || '—'}</dd>
            <dt className="col-4 text-muted">Juridiction</dt><dd className="col-8">{b.juridiction || '—'}</dd>
            <dt className="col-4 text-muted">Réglementation</dt><dd className="col-8">{b.statut_reglementaire || '—'}</dd>
            <dt className="col-4 text-muted">Capacités</dt><dd className="col-8">{(b.capacites ?? []).join(', ') || '—'}</dd>
            <dt className="col-4 text-muted">Types investisseurs</dt><dd className="col-8">{(b.types_investisseurs ?? []).join(', ') || '—'}</dd>
            <dt className="col-4 text-muted">Mandats</dt><dd className="col-8">{(b.mandats ?? []).join(', ') || '—'}</dd>
            <dt className="col-4 text-muted">Statut</dt><dd className="col-8"><StatusBadge value={b.statut} /></dd>
          </dl>

          <h6 className="mb-2">Investisseurs présentés ({presentations.length})</h6>
          {presentations.length ? (
            <table className="table table-sm mb-0">
              <thead><tr><th>Nom</th><th>Email</th><th>Montant</th><th>Statut</th></tr></thead>
              <tbody>
                {presentations.map((p) => (
                  <tr key={p.id}>
                    <td>{p.nom_complet}{p.organisation ? <span className="text-muted"> ({p.organisation})</span> : null}</td>
                    <td>{p.email}</td>
                    <td>{p.montant || '—'}</td>
                    <td><StatusBadge value={p.statut} /></td>
                  </tr>
                ))}
              </tbody>
            </table>
          ) : <p className="text-muted mb-0">Aucun investisseur présenté.</p>}
        </div>
      )}
    </Modal>
  )
}

function DecisionModal({ broker, onClose, onSaved }) {
  const [statut, setStatut] = useState(broker?.statut ?? 'approuve')
  const [motif, setMotif] = useState('')
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')

  const submit = async () => {
    setSending(true)
    setError('')
    try {
      await api.post(`/admin/courtiers/${broker.uuid}/decision`, { statut, motif: motif || null })
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={Boolean(broker)} onClose={onClose} icon="bi-handshake" title={`Décision — ${broker?.nom_complet}`} size="lg">
      {broker && (
        <div>
          <dl className="row small mb-3">
            <dt className="col-4 text-muted">Cabinet</dt><dd className="col-8">{broker.cabinet || '—'}</dd>
            <dt className="col-4 text-muted">Email</dt><dd className="col-8">{broker.email}</dd>
            <dt className="col-4 text-muted">Téléphone</dt><dd className="col-8">{broker.telephone || '—'}</dd>
            <dt className="col-4 text-muted">Pays</dt><dd className="col-8">{broker.pays || '—'}</dd>
            <dt className="col-4 text-muted">Juridiction</dt><dd className="col-8">{broker.juridiction || '—'}</dd>
            <dt className="col-4 text-muted">Réglementation</dt><dd className="col-8">{broker.statut_reglementaire || '—'}</dd>
            <dt className="col-4 text-muted">Statut actuel</dt><dd className="col-8"><StatusBadge value={broker.statut} /></dd>
          </dl>

          {error && <div className="alert alert-danger py-2 small">{error}</div>}

          <div className="row g-3">
            <div className="col-md-6">
              <label className="form-label">Décision</label>
              <select className="form-select" value={statut} onChange={(e) => setStatut(e.target.value)}>
                {DECISIONS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
              </select>
            </div>
            <div className="col-md-6">
              <label className="form-label">Motif</label>
              <input className="form-control" value={motif} onChange={(e) => setMotif(e.target.value)} />
            </div>
            <div className="col-12 d-flex justify-content-end gap-2">
              <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
              <button type="button" className="btn btn-primary" disabled={sending} onClick={submit}>
                {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null} Enregistrer
              </button>
            </div>
          </div>
        </div>
      )}
    </Modal>
  )
}

export default function CourtiersList() {
  const [version, setVersion] = useState(0)
  const [page, setPage] = useState(1)
  const [statut, setStatut] = useState('')
  const [selected, setSelected] = useState(null)
  const [fiche, setFiche] = useState(null)
  const courtiers = useApi('/admin/courtiers', { page, per_page: 15, ...(statut ? { statut } : {}) }, [version])
  const items = courtiers.data?.items ?? []

  const columns = [
    {
      key: 'nom_complet', label: 'Courtier',
      render: (b) => <div><span className="fw-semibold">{b.nom_complet}</span><div className="text-muted small">{b.cabinet}</div></div>,
    },
    { key: 'email', label: 'Email' },
    { key: 'pays', label: 'Pays', render: (b) => b.pays || '—' },
    { key: 'statut', label: 'Statut', render: (b) => <StatusBadge value={b.statut} /> },
    { key: 'created_at', label: 'Reçu le', render: (b) => formatDate(b.created_at) },
    {
      key: 'action', label: '', className: 'text-end',
      render: (b) => (
        <>
          <button type="button" className="btn btn-sm btn-outline-secondary me-1" onClick={() => setFiche(b)}>
            <i className="bi bi-eye"></i>
          </button>
          <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => setSelected(b)}>
            <i className="bi bi-person-check me-1"></i> Décision
          </button>
        </>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Courtiers" crumbs={[{ label: 'Courtiers' }]} />
      <section className="section">
        <DataTable
          title={`Courtiers (${courtiers.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={courtiers.data?.meta}
          onPage={setPage}
          loading={courtiers.loading}
          error={courtiers.error}
          emptyText={{ title: 'Aucun courtier' }}
          toolbar={
            <select className="form-select form-select-sm w-auto" value={statut}
                    onChange={(e) => { setStatut(e.target.value); setPage(1) }}>
              <option value="">Tous les statuts</option>
              {STATUTS.map((s) => <option key={s} value={s}>{s.replace(/_/g, ' ')}</option>)}
            </select>
          }
        />
      </section>

      {selected && (
        <DecisionModal broker={selected} onClose={() => setSelected(null)} onSaved={() => setVersion((v) => v + 1)} />
      )}
      {fiche && <FicheModal broker={fiche} onClose={() => setFiche(null)} />}
    </>
  )
}
