// Profil médecin — GET/PATCH /medecin/profil (bio, langues, disponibilité, WhatsApp).
import { useState } from 'react'
import api from '../../lib/api'
import useApi from '../../lib/useApi'
import PageTitle from '../../ui/PageTitle'
import Spinner from '../../ui/Spinner'
import StatusBadge from '../../ui/StatusBadge'

const money = (v, d) => (v != null ? `${Number(v).toLocaleString('fr-FR')} ${d || ''}` : '—')

export default function DoctorProfil() {
  const [version, setVersion] = useState(0)
  const profil = useApi('/medecin/profil', undefined, [version])

  if (profil.loading) return <><PageTitle title="Mon profil" /><Spinner /></>
  if (profil.error) return <><PageTitle title="Mon profil" /><div className="alert alert-danger m-3">{profil.error.message}</div></>

  return <ProfilForm m={profil.data} reload={() => setVersion((v) => v + 1)} />
}

function ProfilForm({ m, reload }) {
  const [form, setForm] = useState({
    bio: m.bio ?? '',
    langues: (m.langues ?? []).join(', '),
    whatsapp: m.whatsapp ?? '',
    est_disponible: m.disponible ?? false,
  })
  const [sending, setSending] = useState(false)
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setError('')
    setMessage('')
    try {
      await api.patch('/medecin/profil', {
        bio: form.bio || null,
        langues: form.langues.split(',').map((s) => s.trim()).filter(Boolean),
        whatsapp: form.whatsapp || null,
        est_disponible: form.est_disponible,
      })
      setMessage('Profil mis à jour')
      reload()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <>
      <PageTitle title="Mon profil" crumbs={[{ label: 'Tableau de bord', to: '/medecin' }, { label: 'Mon profil' }]} />
      <section className="section">
        <div className="row g-3">
          {/* En-tête */}
          <div className="col-12">
            <div className="card">
              <div className="card-body d-flex flex-wrap align-items-center gap-3">
                {m.photo_url
                  ? <img src={m.photo_url} alt="" width="64" height="64" className="rounded-circle object-fit-cover" />
                  : <div className="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style={{ width: 64, height: 64 }}>{[m.prenom, m.nom].filter(Boolean).map((s) => s[0]).join('')}</div>}
                <div className="me-auto">
                  <h4 className="mb-1">{[m.prenom, m.nom].filter(Boolean).join(' ')}</h4>
                  <div className="text-muted">{m.specialite}</div>
                  <div className="d-flex gap-2 mt-2">
                    <StatusBadge value={m.disponible ? 'disponible' : 'indisponible'} />
                    <StatusBadge value={m.licence_verifiee ? 'verifiee' : 'non_verifiee'} />
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Informations */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Informations</h5>
                <dl className="row mb-0 small">
                  <dt className="col-4 text-muted">Email</dt><dd className="col-8">{m.email}</dd>
                  <dt className="col-4 text-muted">Téléphone</dt><dd className="col-8">{m.telephone || '—'}</dd>
                  <dt className="col-4 text-muted">Numéro licence</dt><dd className="col-8">{m.numero_licence || '—'}</dd>
                  <dt className="col-4 text-muted">Expérience</dt><dd className="col-8">{m.annees_experience > 0 ? `${m.annees_experience} ans` : '—'}</dd>
                  <dt className="col-4 text-muted">Durée</dt><dd className="col-8">{m.duree_minutes} min</dd>
                  <dt className="col-4 text-muted">Tarif BIF</dt><dd className="col-8">{money(m.prix_bif, 'BIF')}</dd>
                  <dt className="col-4 text-muted">Tarif étranger</dt><dd className="col-8">{money(m.prix_usd, m.devise_etranger || 'USD')}</dd>
                  <dt className="col-4 text-muted">Note</dt><dd className="col-8">{m.note_moyenne != null ? `${m.note_moyenne}/5 (${m.nombre_avis} avis)` : '—'}</dd>
                </dl>
              </div>
            </div>
          </div>

          {/* Formulaire */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Modifier ma présentation</h5>
                <form onSubmit={submit} noValidate>
                  {message && <div className="alert alert-success py-2 small">{message}</div>}
                  {error && <div className="alert alert-danger py-2 small">{error}</div>}
                  <div className="row g-3">
                    <div className="col-12">
                      <label className="form-label">Biographie</label>
                      <textarea className="form-control" rows="4" value={form.bio} onChange={(e) => set('bio', e.target.value)} />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Langues (séparées par des virgules)</label>
                      <input className="form-control" value={form.langues} onChange={(e) => set('langues', e.target.value)} />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">WhatsApp</label>
                      <input className="form-control" value={form.whatsapp} placeholder="+257…" onChange={(e) => set('whatsapp', e.target.value)} />
                    </div>
                    <div className="col-12">
                      <div className="form-check">
                        <input className="form-check-input" type="checkbox" id="disp" checked={form.est_disponible}
                               onChange={(e) => set('est_disponible', e.target.checked)} />
                        <label className="form-check-label" htmlFor="disp">Disponible pour les consultations</label>
                      </div>
                    </div>
                    <div className="col-12">
                      <button type="submit" className="btn btn-primary" disabled={sending}>
                        {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null} Enregistrer
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
    </>
  )
}
