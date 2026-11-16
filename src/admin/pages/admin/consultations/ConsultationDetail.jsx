// Détail administratif d'une consultation — GET /admin/consultations/{numero}.
// Actions : décision de paiement, réattribution, annulation. Documents médicaux invisibles (secret médical).
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

const fullName = (p) => [p?.prenom, p?.nom].filter(Boolean).join(' ').trim() || '—'
const money = (v, d) => (v != null ? `${Number(v).toLocaleString('fr-FR', { maximumFractionDigits: 2 })} ${d || ''}` : '—')

/** Transitions de paiement autorisées par l'API (voir decide_payment). */
const PAYMENT_ACTIONS = {
  en_attente: [['paye', 'Valider'], ['echoue', 'Refuser']],
  echoue: [['paye', 'Valider']],
  paye: [['rembourse', 'Rembourser']],
}

export default function ConsultationDetail() {
  const { numero } = useParams()
  const [version, setVersion] = useState(0)
  const [busy, setBusy] = useState(false)
  const [note, setNote] = useState('')
  const [modal, setModal] = useState(null) // 'reattribuer' | 'annuler'
  const [reassignUuid, setReassignUuid] = useState('')
  const [motif, setMotif] = useState('')

  const detail = useApi(`/admin/consultations/${numero}`, undefined, [numero, version])
  const medecins = useApi('/admin/medecins')
  const c = detail.data

  const act = async (fn) => {
    setBusy(true)
    try { await fn(); setVersion((v) => v + 1); setModal(null); setNote(''); setMotif('') }
    catch { /* l'erreur est reflétée par le rechargement */ }
    finally { setBusy(false) }
  }

  const openDoc = async (docId) => {
    try {
      const { url } = await api.get(`/admin/consultations/${numero}/documents/${docId}/lien`)
      window.open(url, '_blank')
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

  const actions = PAYMENT_ACTIONS[c.paiement_statut] ?? []
  const otherMedecins = (medecins.data ?? []).filter((m) => m.uuid !== c.medecin?.uuid)

  return (
    <>
      <PageTitle title={`Consultation ${c.numero}`} crumbs={[{ label: 'Consultations', to: ROUTES.consultations }, { label: c.numero }]} />

      <section className="section">
        <div className="row g-3">
          {/* En-tête */}
          <div className="col-12">
            <div className="card">
              <div className="card-body d-flex flex-wrap align-items-center gap-3">
                <div className="me-auto">
                  <h4 className="mb-1">Consultation {c.numero}</h4>
                  <div className="text-muted small">Créée le {formatDateTime(c.created_at)}</div>
                </div>
                <StatusBadge value={c.statut} />
                <StatusBadge value={c.paiement_statut} />
                <button type="button" className="btn btn-outline-secondary" onClick={() => setModal('reattribuer')} disabled={busy}>
                  <i className="bi bi-arrow-left-right me-1"></i> Réattribuer
                </button>
                <button type="button" className="btn btn-outline-danger" onClick={() => setModal('annuler')} disabled={busy}>
                  <i className="bi bi-x-circle me-1"></i> Annuler
                </button>
              </div>
            </div>
          </div>

          {/* Informations */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Rendez-vous</h5>
                <dl className="row mb-0 small">
                  <dt className="col-4 text-muted">Patient</dt><dd className="col-8">{fullName(c.patient)}</dd>
                  <dt className="col-4 text-muted">Médecin</dt>
                  <dd className="col-8">{fullName(c.medecin)} {c.specialite ? <span className="text-muted">({c.specialite})</span> : null}</dd>
                  <dt className="col-4 text-muted">Début</dt><dd className="col-8">{formatDateTime(c.debut)}</dd>
                  <dt className="col-4 text-muted">Fin</dt><dd className="col-8">{formatDateTime(c.fin)}</dd>
                  <dt className="col-4 text-muted">Durée</dt><dd className="col-8">{c.duree_minutes} min</dd>
                  <dt className="col-4 text-muted">Type</dt><dd className="col-8">{c.type || '—'}</dd>
                  {c.motif_annulation && <><dt className="col-4 text-muted">Motif annulation</dt><dd className="col-8">{c.motif_annulation}</dd></>}
                </dl>
              </div>
            </div>
          </div>

          {/* Paiement */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Paiement</h5>
                <dl className="row mb-3 small">
                  <dt className="col-4 text-muted">HT</dt><dd className="col-8">{money(c.prix_ht, c.devise)}</dd>
                  <dt className="col-4 text-muted">TVA</dt><dd className="col-8">{money(c.tva, c.devise)}</dd>
                  <dt className="col-4 text-muted">TTC</dt><dd className="col-8 fw-semibold">{money(c.prix_ttc, c.devise)}</dd>
                  <dt className="col-4 text-muted">Mode</dt><dd className="col-8">{c.mode_paiement || '—'}</dd>
                </dl>

                {actions.length > 0 && (
                  <div className="row g-2">
                    <div className="col-12">
                      <input className="form-control form-control-sm" placeholder="Note (facultatif)" value={note}
                             onChange={(e) => setNote(e.target.value)} />
                    </div>
                    <div className="col-12 d-flex flex-wrap gap-2">
                      {actions.map(([statut, label]) => (
                        <button key={statut} type="button" disabled={busy}
                                className={`btn btn-sm ${statut === 'paye' ? 'btn-success' : statut === 'rembourse' ? 'btn-info' : 'btn-danger'}`}
                                onClick={() => act(() => api.post(`/admin/consultations/${numero}/paiement`, { statut, note: note || null }))}>
                          <i className="bi bi-check2 me-1"></i> {label}
                        </button>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Preuve de paiement */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Preuve de paiement</h5>
                {c.documents?.length ? (
                  <ul className="list-group list-group-flush">
                    {c.documents.map((d) => (
                      <li key={d.id} className="list-group-item d-flex align-items-center justify-content-between px-0">
                        <span><i className="bi bi-receipt me-2"></i>{d.nom || 'Preuve'}</span>
                        <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => openDoc(d.id)}>
                          <i className="bi bi-eye me-1"></i> Voir
                        </button>
                      </li>
                    ))}
                  </ul>
                ) : <p className="text-muted mb-0">Aucune preuve de paiement téléversée.</p>}
              </div>
            </div>
          </div>

          {/* Historique */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Historique</h5>
                {c.historique?.length ? (
                  <table className="table table-sm mb-0">
                    <thead>
                      <tr><th>De</th><th>Vers</th><th>Par</th><th>Note</th><th>Le</th></tr>
                    </thead>
                    <tbody>
                      {c.historique.map((h, i) => (
                        <tr key={i}>
                          <td>{h.de || '—'}</td>
                          <td><StatusBadge value={h.vers} /></td>
                          <td className="text-capitalize">{h.par || '—'}</td>
                          <td>{h.note || '—'}</td>
                          <td className="text-nowrap small">{formatDateTime(h.le)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                ) : <p className="text-muted mb-0">Aucun changement enregistré.</p>}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Réattribuer */}
      <Modal open={modal === 'reattribuer'} onClose={() => setModal(null)} icon="bi-arrow-left-right" title="Réattribuer la consultation">
        <div className="mb-3">
          <label className="form-label">Nouveau médecin</label>
          <select className="form-select" value={reassignUuid} onChange={(e) => setReassignUuid(e.target.value)}>
            <option value="">Choisir…</option>
            {otherMedecins.map((m) => (
              <option key={m.uuid} value={m.uuid}>{[m.prenom, m.nom].filter(Boolean).join(' ')} — {m.specialite}</option>
            ))}
          </select>
        </div>
        <div className="d-flex justify-content-end gap-2">
          <button type="button" className="btn btn-outline-secondary" onClick={() => setModal(null)}>Annuler</button>
          <button type="button" className="btn btn-primary" disabled={!reassignUuid || busy}
                  onClick={() => act(() => api.post(`/admin/consultations/${numero}/reattribuer`, { medecin_uuid: reassignUuid }))}>
            {busy ? <span className="spinner-border spinner-border-sm me-1"></span> : null} Réattribuer
          </button>
        </div>
      </Modal>

      {/* Annuler */}
      <Modal open={modal === 'annuler'} onClose={() => setModal(null)} icon="bi-x-circle" title="Annuler la consultation">
        <div className="mb-3">
          <label className="form-label">Motif (3 caractères min)</label>
          <textarea className="form-control" rows="3" value={motif} onChange={(e) => setMotif(e.target.value)} />
        </div>
        <div className="d-flex justify-content-end gap-2">
          <button type="button" className="btn btn-outline-secondary" onClick={() => setModal(null)}>Fermer</button>
          <button type="button" className="btn btn-danger" disabled={motif.trim().length < 3 || busy}
                  onClick={() => act(() => api.post(`/admin/consultations/${numero}/annuler`, { motif: motif.trim() }))}>
            {busy ? <span className="spinner-border spinner-border-sm me-1"></span> : null} Confirmer l'annulation
          </button>
        </div>
      </Modal>
    </>
  )
}
