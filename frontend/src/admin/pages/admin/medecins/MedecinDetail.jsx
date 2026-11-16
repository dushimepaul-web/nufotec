// Fiche médecin — GET /admin/medecins/{uuid} + absences.
// Actions : modifier, valider/retirer la licence, activer/désactiver, gérer les absences.
import { useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import api from '../../../lib/api'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import EmptyState from '../../../ui/EmptyState'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'
import StatusBadge from '../../../ui/StatusBadge'

const DOCTOR_FALLBACK = '/assets/img/profile-img.jpg'
const hour = (t) => (t || '').slice(0, 5).replace(':', ' h ')

function Absences({ uuid }) {
  const [version, setVersion] = useState(0)
  const [form, setForm] = useState({ debut: '', fin: '', motif: '' })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')

  const absences = useApi(`/admin/medecins/${uuid}/absences`, undefined, [version])
  const items = absences.data ?? []

  const submit = async (e) => {
    e.preventDefault()
    if (!form.debut || !form.fin) return
    setSending(true)
    setError('')
    try {
      await api.post(`/admin/medecins/${uuid}/absences`, {
        debut: form.debut, fin: form.fin, motif: form.motif || null,
      })
      setForm({ debut: '', fin: '', motif: '' })
      setVersion((v) => v + 1)
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  const remove = async (id) => {
    try {
      await api.delete(`/admin/medecins/${uuid}/absences/${id}`)
      setVersion((v) => v + 1)
    } catch (err) {
      setError(err.message)
    }
  }

  return (
    <div className="card">
      <div className="card-body">
        <h5 className="card-title">Absences & congés</h5>

        <form className="row g-2 mb-3" onSubmit={submit}>
          <div className="col-md-4">
            <label className="form-label small">Du</label>
            <input type="datetime-local" className="form-control form-control-sm" value={form.debut}
                   onChange={(e) => setForm({ ...form, debut: e.target.value })} />
          </div>
          <div className="col-md-4">
            <label className="form-label small">Au</label>
            <input type="datetime-local" className="form-control form-control-sm" value={form.fin}
                   onChange={(e) => setForm({ ...form, fin: e.target.value })} />
          </div>
          <div className="col-md-4">
            <label className="form-label small">Motif</label>
            <input className="form-control form-control-sm" value={form.motif} placeholder="Congés, maladie…"
                   onChange={(e) => setForm({ ...form, motif: e.target.value })} />
          </div>
          <div className="col-12">
            {error && <small className="text-danger">{error}</small>}
          </div>
          <div className="col-12">
            <button type="submit" className="btn btn-sm btn-primary" disabled={sending || !form.debut || !form.fin}>
              {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : <i className="bi bi-plus-lg me-1"></i>}
              Ajouter
            </button>
          </div>
        </form>

        {absences.loading ? <Spinner text="" /> : items.length === 0 ? (
          <p className="text-muted mb-0">Aucune absence déclarée.</p>
        ) : (
          <table className="table table-sm align-middle mb-0">
            <tbody>
              {items.map((a) => (
                <tr key={a.id}>
                  <td>
                    <div className="fw-semibold">
                      {new Date(a.debut).toLocaleString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}
                      {' — '}
                      {new Date(a.fin).toLocaleString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}
                    </div>
                    {a.motif && <small className="text-muted">{a.motif}</small>}
                  </td>
                  <td className="text-end">
                    <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(a.id)}>
                      <i className="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  )
}

export default function MedecinDetail() {
  const { uuid } = useParams()
  const [version, setVersion] = useState(0)
  const [busy, setBusy] = useState(false)

  const detail = useApi(`/admin/medecins/${uuid}`, undefined, [uuid, version])
  const m = detail.data

  const act = async (fn) => {
    setBusy(true)
    try { await fn(); setVersion((v) => v + 1) } catch { /* l'erreur est reflétée par le rechargement */ }
    finally { setBusy(false) }
  }

  if (detail.loading) return <><PageTitle title="Fiche médecin" /><Spinner /></>
  if (detail.error || !m) {
    return (
      <>
        <PageTitle title="Fiche médecin" />
        <EmptyState icon="bi-person-x" title="Médecin introuvable" text={detail.error?.message} />
      </>
    )
  }

  return (
    <>
      <PageTitle title="Fiche médecin" crumbs={[{ label: 'Médecins', to: ROUTES.medecins }, { label: [m.prenom, m.nom].filter(Boolean).join(' ') }]} />

      <section className="section">
        <div className="row g-3">
          {/* Identité + actions */}
          <div className="col-12">
            <div className="card">
              <div className="card-body">
                <div className="d-flex flex-wrap align-items-center gap-3">
                  <img src={m.photo_url || DOCTOR_FALLBACK} alt="" width="72" height="72"
                       className="rounded-circle object-fit-cover" />
                  <div className="me-auto">
                    <h4 className="mb-1">{[m.prenom, m.nom].filter(Boolean).join(' ')}</h4>
                    <div className="text-muted mb-2">{m.specialite}</div>
                    <div className="d-flex flex-wrap gap-2">
                      <StatusBadge value={m.disponible ? 'disponible' : 'indisponible'} />
                      <StatusBadge value={m.licence_verifiee ? 'verifiee' : 'non_verifiee'} />
                      <StatusBadge value={m.compte_actif ? 'actif' : 'inactif'} />
                    </div>
                  </div>
                  <div className="d-flex flex-wrap gap-2">
                    <Link to={ROUTES.medecinModifier(uuid)} className="btn btn-primary">
                      <i className="bi bi-pencil me-1"></i> Modifier
                    </Link>
                    <button type="button" className="btn btn-outline-primary" disabled={busy}
                            onClick={() => act(() => api.post(`/admin/medecins/${uuid}/licence`, { verifiee: !m.licence_verifiee }))}>
                      <i className="bi bi-patch-check me-1"></i> {m.licence_verifiee ? 'Retirer la licence' : 'Valider la licence'}
                    </button>
                    <button type="button" className="btn btn-outline-secondary" disabled={busy}
                            onClick={() => act(() => api.patch(`/admin/medecins/${uuid}`, { est_disponible: !m.disponible }))}>
                      <i className="bi bi-power me-1"></i> {m.disponible ? 'Désactiver' : 'Activer'}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Coordonnées */}
          <div className="col-lg-4">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Coordonnées</h5>
                <dl className="row mb-0 small">
                  <dt className="col-4 text-muted">Email</dt><dd className="col-8">{m.email || '—'}</dd>
                  <dt className="col-4 text-muted">Téléphone</dt><dd className="col-8">{m.telephone || '—'}</dd>
                  <dt className="col-4 text-muted">WhatsApp</dt><dd className="col-8">{m.whatsapp || '—'}</dd>
                  <dt className="col-4 text-muted">Licence</dt><dd className="col-8">{m.numero_licence || '—'}</dd>
                  <dt className="col-4 text-muted">Expérience</dt><dd className="col-8">{m.annees_experience > 0 ? `${m.annees_experience} ans` : '—'}</dd>
                  <dt className="col-4 text-muted">Durée</dt><dd className="col-8">{m.duree_minutes} min</dd>
                  <dt className="col-4 text-muted">Langues</dt><dd className="col-8">{(m.langues ?? []).join(', ') || '—'}</dd>
                </dl>
              </div>
            </div>
          </div>

          {/* Tarifs */}
          <div className="col-lg-4">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Tarifs</h5>
                <div className="row">
                  <div className="col-6">
                    <div className="text-muted small">Résidant Burundi</div>
                    <div className="fs-5 fw-semibold">{m.prix_bif != null ? `${Number(m.prix_bif).toLocaleString('fr-FR')} BIF` : '—'}</div>
                  </div>
                  <div className="col-6">
                    <div className="text-muted small">Résidant étranger</div>
                    <div className="fs-5 fw-semibold">{m.prix_usd != null ? `${Number(m.prix_usd).toLocaleString('fr-FR')} ${m.devise_etranger || 'USD'}` : '—'}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Horaires */}
          <div className="col-lg-4">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Horaires</h5>
                {m.horaires?.length ? (
                  <table className="table table-sm mb-0">
                    <tbody>
                      {m.horaires.map((h, i) => (
                        <tr key={i}><td className="text-capitalize">{h.jour}</td><td className="text-end">{hour(h.debut)} – {hour(h.fin)}</td></tr>
                      ))}
                    </tbody>
                  </table>
                ) : <p className="text-muted mb-0">Aucun horaire défini.</p>}
              </div>
            </div>
          </div>

          {/* Bio & diplômes */}
          <div className="col-12">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Présentation</h5>
                {m.diplomes && <p className="mb-2"><strong>Diplômes :</strong> {m.diplomes}</p>}
                {m.bio ? <p className="mb-0">{m.bio}</p> : <p className="text-muted mb-0">Aucune biographie.</p>}
              </div>
            </div>
          </div>

          {/* Absences */}
          <div className="col-12">
            <Absences uuid={uuid} />
          </div>
        </div>
      </section>
    </>
  )
}
