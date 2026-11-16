// Dossier patient (médecin) — GET /medecin/consultations/{numero} + actions de consultation.
import { useState } from 'react'
import { useParams } from 'react-router-dom'
import api, { downloadCsv } from '../../lib/api'
import { formatDateTime } from '../../lib/format'
import { ROUTES } from '../../lib/routes'
import useApi from '../../lib/useApi'
import EmptyState from '../../ui/EmptyState'
import Modal from '../../ui/Modal'
import PageTitle from '../../ui/PageTitle'
import Spinner from '../../ui/Spinner'
import StatusBadge from '../../ui/StatusBadge'

const fullName = (p) => [p?.prenom, p?.nom].filter(Boolean).join(' ').trim() || 'Patient'

function FinishModal({ open, onClose, onDone }) {
  const [form, setForm] = useState({ diagnostic: '', traitement: '', notes_medecin: '' })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')

  const submit = async () => {
    if (form.diagnostic.trim().length < 2) return
    setSending(true)
    setError('')
    try {
      await onDone({
        diagnostic: form.diagnostic.trim(),
        traitement: form.traitement || null,
        notes_medecin: form.notes_medecin || null,
      })
      onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={open} onClose={onClose} icon="bi-clipboard-check" title="Terminer la consultation" size="lg">
      {error && <div className="alert alert-danger py-2 small">{error}</div>}
      <div className="row g-3">
        <div className="col-12">
          <label className="form-label">Diagnostic *</label>
          <textarea className="form-control" rows="3" value={form.diagnostic} onChange={(e) => setForm({ ...form, diagnostic: e.target.value })} />
        </div>
        <div className="col-12">
          <label className="form-label">Traitement</label>
          <textarea className="form-control" rows="3" value={form.traitement} onChange={(e) => setForm({ ...form, traitement: e.target.value })} />
        </div>
        <div className="col-12">
          <label className="form-label">Notes privées</label>
          <textarea className="form-control" rows="2" value={form.notes_medecin} onChange={(e) => setForm({ ...form, notes_medecin: e.target.value })} />
        </div>
        <div className="col-12 d-flex justify-content-end gap-2">
          <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
          <button type="button" className="btn btn-primary" disabled={sending || form.diagnostic.trim().length < 2} onClick={submit}>
            {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null} Terminer
          </button>
        </div>
      </div>
    </Modal>
  )
}

export default function DoctorConsultationDetail() {
  const { numero } = useParams()
  const [version, setVersion] = useState(0)
  const [busy, setBusy] = useState(false)
  const [modal, setModal] = useState(null) // 'refuser' | 'terminer'
  const [motif, setMotif] = useState('')

  const detail = useApi(`/medecin/consultations/${numero}`, undefined, [numero, version])
  const c = detail.data

  const act = async (fn) => {
    setBusy(true)
    try { await fn(); setVersion((v) => v + 1); setModal(null); setMotif('') }
    catch (err) { window.alert(err.message) }
    finally { setBusy(false) }
  }

  const openLink = async (url) => {
    try {
      const r = await api.get(url)
      window.open(r.url, '_blank')
    } catch { /* lien expiré */ }
  }

  if (detail.loading) return <><PageTitle title="Consultation" /><Spinner /></>
  if (detail.error || !c) {
    return (
      <>
        <PageTitle title="Consultation" />
        <EmptyState icon="bi-calendar-x" title="Consultation introuvable" text={detail.error?.message} />
      </>
    )
  }

  return (
    <>
      <PageTitle title={`Consultation ${c.numero}`}
                 crumbs={[{ label: 'Mon agenda', to: ROUTES.doctorAgenda }, { label: c.numero }]} />

      <section className="section">
        <div className="row g-3">
          {/* En-tête */}
          <div className="col-12">
            <div className="card">
              <div className="card-body d-flex flex-wrap align-items-center gap-3">
                <div className="me-auto">
                  <h4 className="mb-1">{fullName(c.patient)}</h4>
                  <div className="text-muted small">{formatDateTime(c.debut)} · {c.duree_minutes} min</div>
                  <div className="d-flex gap-2 mt-2">
                    <StatusBadge value={c.statut} />
                    <StatusBadge value={c.paiement_statut} />
                  </div>
                </div>
                <div className="d-flex flex-wrap gap-2">
                  <button type="button" className="btn btn-sm btn-outline-secondary"
                          onClick={() => downloadCsv(`/medecin/consultations/${numero}/agenda.ics`, {}, `rendez-vous-${numero}.ics`)}>
                    <i className="bi bi-calendar-plus me-1"></i> Agenda .ics
                  </button>
                  {c.peut_rejoindre && (
                    <button type="button" className="btn btn-sm btn-success" disabled={busy}
                            onClick={() => openLink(`/medecin/consultations/${numero}/rejoindre`)}>
                      <i className="bi bi-camera-video me-1"></i> Rejoindre la salle
                    </button>
                  )}
                  {c.statut === 'en_attente' && (
                    <>
                      <button type="button" className="btn btn-sm btn-primary" disabled={busy}
                              onClick={() => act(() => api.post(`/medecin/consultations/${numero}/confirmer`))}>
                        <i className="bi bi-check-lg me-1"></i> Confirmer
                      </button>
                      <button type="button" className="btn btn-sm btn-outline-danger" disabled={busy} onClick={() => setModal('refuser')}>
                        <i className="bi bi-x-lg me-1"></i> Refuser
                      </button>
                    </>
                  )}
                  {c.statut === 'confirmee' && (
                    <>
                      <button type="button" className="btn btn-sm btn-primary" disabled={busy}
                              onClick={() => act(() => api.post(`/medecin/consultations/${numero}/demarrer`))}>
                        <i className="bi bi-play me-1"></i> Démarrer
                      </button>
                      <button type="button" className="btn btn-sm btn-outline-secondary" disabled={busy}
                              onClick={() => act(() => api.post(`/medecin/consultations/${numero}/absent`))}>
                        <i className="bi bi-person-x me-1"></i> Patient absent
                      </button>
                    </>
                  )}
                  {c.statut === 'en_cours' && (
                    <button type="button" className="btn btn-sm btn-success" disabled={busy} onClick={() => setModal('terminer')}>
                      <i className="bi bi-clipboard-check me-1"></i> Terminer
                    </button>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* Patient */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Patient</h5>
                <dl className="row mb-0 small">
                  <dt className="col-4 text-muted">Âge</dt><dd className="col-8">{c.age ?? '—'}</dd>
                  <dt className="col-4 text-muted">Poids</dt><dd className="col-8">{c.poids != null ? `${c.poids} kg` : '—'}</dd>
                  <dt className="col-4 text-muted">Taille</dt><dd className="col-8">{c.taille != null ? `${c.taille} cm` : '—'}</dd>
                  <dt className="col-4 text-muted">WhatsApp</dt><dd className="col-8">{c.whatsapp || '—'}</dd>
                  <dt className="col-4 text-muted">Consult. précédente</dt><dd className="col-8">{c.consultation_precedente ? 'Oui' : 'Non'}</dd>
                </dl>
              </div>
            </div>
          </div>

          {/* Symptômes */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Symptômes</h5>
                <p className="mb-1">{c.symptomes || '—'}</p>
                {c.duree_symptomes && <span className="badge bg-light text-dark">{c.duree_symptomes}</span>}
              </div>
            </div>
          </div>

          {/* Documents */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Documents</h5>
                {c.documents?.length ? (
                  <ul className="list-group list-group-flush">
                    {c.documents.map((d) => (
                      <li key={d.id} className="list-group-item d-flex align-items-center justify-content-between px-0">
                        <span>
                          <i className={`bi ${d.kind === 'ordonnance' ? 'bi-prescription2' : d.mime === 'application/pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image'} me-2`}></i>
                          {d.nom || d.kind}
                        </span>
                        <button type="button" className="btn btn-sm btn-outline-primary"
                                onClick={() => openLink(`/medecin/consultations/${numero}/documents/${d.id}/lien`)}>
                          <i className="bi bi-eye me-1"></i> Voir
                        </button>
                      </li>
                    ))}
                  </ul>
                ) : <p className="text-muted mb-0">Aucun document.</p>}

                {['en_cours', 'terminee'].includes(c.statut) && (
                  <div className="mt-3">
                    <label className="form-label small">Joindre une ordonnance (PDF/image)</label>
                    <input type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" className="form-control form-control-sm"
                           onChange={async (e) => {
                             const f = e.target.files[0]
                             if (!f) return
                             setBusy(true)
                             try {
                               const fd = new FormData()
                               fd.append('fichier', f)
                               await api.post(`/medecin/consultations/${numero}/ordonnance`, fd)
                               setVersion((v) => v + 1)
                             } catch (err) { window.alert(err.message) }
                             finally { setBusy(false); e.target.value = '' }
                           }} />
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Compte-rendu */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Compte-rendu</h5>
                {c.diagnostic ? (
                  <>
                    <div className="mb-2"><strong>Diagnostic :</strong><p className="mb-1">{c.diagnostic}</p></div>
                    {c.traitement && <div className="mb-2"><strong>Traitement :</strong><p className="mb-1">{c.traitement}</p></div>}
                    {c.notes_medecin && <div><strong>Notes privées :</strong><p className="mb-0">{c.notes_medecin}</p></div>}
                  </>
                ) : <p className="text-muted mb-0">Pas encore de compte-rendu.</p>}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Refuser */}
      <Modal open={modal === 'refuser'} onClose={() => setModal(null)} icon="bi-x-circle" title="Refuser la consultation">
        <div className="mb-3">
          <label className="form-label">Motif (3 caractères min)</label>
          <textarea className="form-control" rows="3" value={motif} onChange={(e) => setMotif(e.target.value)} />
        </div>
        <div className="d-flex justify-content-end gap-2">
          <button type="button" className="btn btn-outline-secondary" onClick={() => setModal(null)}>Annuler</button>
          <button type="button" className="btn btn-danger" disabled={motif.trim().length < 3 || busy}
                  onClick={() => act(() => api.post(`/medecin/consultations/${numero}/refuser`, { motif: motif.trim() }))}>
            Confirmer le refus
          </button>
        </div>
      </Modal>

      {/* Terminer */}
      <FinishModal open={modal === 'terminer'} onClose={() => setModal(null)}
                   onDone={(body) => api.post(`/medecin/consultations/${numero}/terminer`, body)} />
    </>
  )
}
