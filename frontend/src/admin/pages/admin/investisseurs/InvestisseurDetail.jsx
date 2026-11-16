// Fiche investisseur — GET /admin/investisseurs/{uuid}.
// Actions : changer de statut (pipeline), ajouter une note, assigner un responsable, programmer une relance.
import { useState } from 'react'
import { useParams } from 'react-router-dom'
import api from '../../../lib/api'
import { formatDateTime } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import EmptyState from '../../../ui/EmptyState'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'
import StatusBadge from '../../../ui/StatusBadge'

const STATUTS = ['nouveau', 'contacte', 'qualifie', 'due_diligence', 'engage', 'perdu']
const MONTANTS = ['Below 250K', '250K-1M', '1M-5M', '5M+', 'To be discussed']
const CALENDRIERS = ['Immediate', '3-6 months', '6-12 months', 'Exploratory']

function EditForm({ investisseur, onClose, onSaved }) {
  const [form, setForm] = useState({
    organisation: investisseur.organisation ?? '', fonction: investisseur.fonction ?? '',
    telephone: investisseur.telephone ?? '', montant: investisseur.montant ?? '', calendrier: investisseur.calendrier ?? '',
  })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setError('')
    try {
      await api.patch(`/admin/investisseurs/${investisseur.uuid}`, {
        organisation: form.organisation || null, fonction: form.fonction || null,
        telephone: form.telephone || null, montant: form.montant || null, calendrier: form.calendrier || null,
      })
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open onClose={onClose} icon="bi-pencil" title="Modifier la fiche">
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          <div className="col-12">
            <label className="form-label">Organisation</label>
            <input className="form-control" value={form.organisation} onChange={(e) => set('organisation', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Fonction</label>
            <input className="form-control" value={form.fonction} onChange={(e) => set('fonction', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Téléphone</label>
            <input className="form-control" value={form.telephone} onChange={(e) => set('telephone', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Montant</label>
            <select className="form-select" value={form.montant} onChange={(e) => set('montant', e.target.value)}>
              <option value="">—</option>
              {MONTANTS.map((m) => <option key={m} value={m}>{m}</option>)}
            </select>
          </div>
          <div className="col-md-6">
            <label className="form-label">Calendrier</label>
            <select className="form-select" value={form.calendrier} onChange={(e) => set('calendrier', e.target.value)}>
              <option value="">—</option>
              {CALENDRIERS.map((c) => <option key={c} value={c}>{c}</option>)}
            </select>
          </div>
          <div className="col-12 d-flex justify-content-end gap-2">
            <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
            <button type="submit" className="btn btn-primary" disabled={sending}>Enregistrer</button>
          </div>
        </div>
      </form>
    </Modal>
  )
}

export default function InvestisseurDetail() {
  const { uuid } = useParams()
  const [version, setVersion] = useState(0)
  const [busy, setBusy] = useState(false)
  const [note, setNote] = useState('')
  const [newNote, setNewNote] = useState('')
  const [responsable, setResponsable] = useState('')
  const [relance, setRelance] = useState('')
  const [edit, setEdit] = useState(false)

  const detail = useApi(`/admin/investisseurs/${uuid}`, undefined, [uuid, version])
  const admins = useApi('/admin/users', { type_utilisateur: 'admin', per_page: 100 })
  const i = detail.data

  const act = async (fn) => {
    setBusy(true)
    try { await fn(); setVersion((v) => v + 1); setNote(''); setNewNote(''); setRelance('') }
    catch { /* reflété par le rechargement */ }
    finally { setBusy(false) }
  }

  if (detail.loading) return <><PageTitle title="Investisseur" /><Spinner /></>
  if (detail.error || !i) {
    return (
      <>
        <PageTitle title="Investisseur" />
        <EmptyState icon="bi-person-x" title="Investisseur introuvable" text={detail.error?.message} />
      </>
    )
  }

  return (
    <>
      <PageTitle title={i.nom_complet} crumbs={[{ label: 'Investisseurs', to: ROUTES.investisseurs }, { label: i.nom_complet }]} />

      <section className="section">
        <div className="row g-3">
          {/* En-tête */}
          <div className="col-12">
            <div className="card">
              <div className="card-body d-flex flex-wrap align-items-center gap-3">
                <div className="me-auto">
                  <h4 className="mb-1">{i.nom_complet}</h4>
                  <div className="text-muted small">{i.organisation || 'Particulier'} · reçu le {formatDateTime(i.created_at)}</div>
                </div>
                <StatusBadge value={i.statut} />
                <span className="text-muted small text-capitalize">source : {i.source}</span>
                <button type="button" className="btn btn-sm btn-outline-primary ms-auto" onClick={() => setEdit(true)}>
                  <i className="bi bi-pencil me-1"></i> Modifier
                </button>
              </div>
            </div>
          </div>

          {/* Coordonnées */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Coordonnées</h5>
                <dl className="row mb-0 small">
                  <dt className="col-4 text-muted">Email</dt><dd className="col-8">{i.email}</dd>
                  <dt className="col-4 text-muted">Téléphone</dt><dd className="col-8">{i.telephone || '—'}</dd>
                  <dt className="col-4 text-muted">Pays</dt><dd className="col-8">{i.pays || '—'}</dd>
                  <dt className="col-4 text-muted">Fonction</dt><dd className="col-8">{i.fonction || '—'}</dd>
                  <dt className="col-4 text-muted">Montant</dt><dd className="col-8">{i.montant || '—'}</dd>
                  <dt className="col-4 text-muted">Calendrier</dt><dd className="col-8">{i.calendrier || '—'}</dd>
                  <dt className="col-4 text-muted">Intérêts</dt><dd className="col-8">{(i.interets ?? []).join(', ') || '—'}</dd>
                  <dt className="col-4 text-muted">Responsable</dt><dd className="col-8">{i.responsable || '—'}</dd>
                  <dt className="col-4 text-muted">Courtier</dt><dd className="col-8">{i.courtier || '—'}</dd>
                  <dt className="col-4 text-muted">NDA</dt><dd className="col-8">{i.nda_accepte ? 'Accepté' : 'Non'}</dd>
                </dl>
                {i.message && <p className="mt-3 mb-0 p-3 bg-light rounded small">{i.message}</p>}
              </div>
            </div>
          </div>

          {/* Pipeline + actions */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Pipeline</h5>
                <div className="mb-3">
                  <label className="form-label small">Changer le statut</label>
                  <div className="d-flex flex-wrap gap-2">
                    {STATUTS.filter((s) => s !== i.statut).map((s) => (
                      <button key={s} type="button" disabled={busy}
                              className={`btn btn-sm ${s === 'engage' ? 'btn-success' : s === 'perdu' ? 'btn-danger' : 'btn-outline-primary'}`}
                              onClick={() => act(() => api.post(`/admin/investisseurs/${uuid}/statut`, { statut: s, note: note || null }))}>
                        {s.replace(/_/g, ' ')}
                      </button>
                    ))}
                  </div>
                  <input className="form-control form-control-sm mt-2" placeholder="Note (facultatif)" value={note}
                         onChange={(e) => setNote(e.target.value)} />
                </div>

                <div className="mb-3">
                  <label className="form-label small">Responsable</label>
                  <div className="d-flex gap-2">
                    <select className="form-select form-select-sm" value={responsable} onChange={(e) => setResponsable(e.target.value)}>
                      <option value="">—</option>
                      {(admins.data?.items ?? []).map((a) => (
                        <option key={a.uuid} value={a.uuid}>{[a.prenom, a.nom].filter(Boolean).join(' ')}</option>
                      ))}
                    </select>
                    <button type="button" className="btn btn-sm btn-outline-primary text-nowrap" disabled={!responsable || busy}
                            onClick={() => act(() => api.post(`/admin/investisseurs/${uuid}/responsable`, { admin_uuid: responsable }))}>
                      Assigner
                    </button>
                  </div>
                </div>

                <div className="mb-3">
                  <label className="form-label small">Prochaine relance</label>
                  <div className="d-flex gap-2">
                    <input type="datetime-local" className="form-control form-control-sm" value={relance}
                           onChange={(e) => setRelance(e.target.value)} />
                    <button type="button" className="btn btn-sm btn-outline-primary text-nowrap" disabled={!relance || busy}
                            onClick={() => act(() => api.post(`/admin/investisseurs/${uuid}/relance`, { le: relance, note: null }))}>
                      Programmer
                    </button>
                  </div>
                  {i.prochaine_relance && <small className="text-muted">Relance actuelle : {formatDateTime(i.prochaine_relance)}</small>}
                </div>

                <div>
                  <label className="form-label small">Ajouter une note</label>
                  <div className="d-flex gap-2">
                    <input className="form-control form-control-sm" value={newNote} onChange={(e) => setNewNote(e.target.value)} />
                    <button type="button" className="btn btn-sm btn-outline-primary text-nowrap" disabled={!newNote.trim() || busy}
                            onClick={() => act(() => api.post(`/admin/investisseurs/${uuid}/notes`, { texte: newNote.trim() }))}>
                      Ajouter
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Historique */}
          <div className="col-12">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Historique</h5>
                {i.historique?.length ? (
                  <table className="table table-sm mb-0">
                    <thead>
                      <tr><th>Type</th><th>De</th><th>Vers</th><th>Contenu</th><th>Auteur</th><th>Le</th></tr>
                    </thead>
                    <tbody>
                      {i.historique.map((h, idx) => (
                        <tr key={idx}>
                          <td><span className="badge bg-light text-dark">{h.type}</span></td>
                          <td>{h.de ? <StatusBadge value={h.de} /> : '—'}</td>
                          <td>{h.vers ? <StatusBadge value={h.vers} /> : '—'}</td>
                          <td>{h.texte || '—'}</td>
                          <td>{h.auteur || '—'}</td>
                          <td className="text-nowrap small">{formatDateTime(h.le)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                ) : <p className="text-muted mb-0">Aucun événement.</p>}
              </div>
            </div>
          </div>
        </div>
      </section>

      {edit && (
        <EditForm investisseur={i} onClose={() => setEdit(false)} onSaved={() => setVersion((v) => v + 1)} />
      )}
    </>
  )
}
