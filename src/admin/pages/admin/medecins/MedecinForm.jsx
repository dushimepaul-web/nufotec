// Formulaire médecin (création + modification).
// Création : POST /admin/medecins (nouveau compte ou lien vers un compte médecin existant).
// Modification : PATCH /admin/medecins/{uuid} (fiche) puis PUT .../horaires.
import { useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import api from '../../../lib/api'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'

const DAYS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche']

const empty = {
  mode: 'nouveau',
  user_uuid: '',
  email: '', nom: '', prenom: '', telephone: '', genre: '',
  specialite: '', numero_licence: '', annees_experience: '', diplomes: '', bio: '',
  langues: '', whatsapp: '', prix_usd: '', prix_bif: '',
  est_disponible: true, duree_minutes: 30,
  horaires: [],
}

/** horaires de l'API « 08:00:00 » → « 08:00 » pour les champs time */
const toForm = (m) => ({
  ...empty,
  specialite: m.specialite ?? '',
  numero_licence: m.numero_licence ?? '',
  annees_experience: m.annees_experience ?? '',
  diplomes: m.diplomes ?? '',
  bio: m.bio ?? '',
  langues: (m.langues ?? []).join(', '),
  whatsapp: m.whatsapp ?? '',
  prix_usd: m.prix_usd ?? '',
  prix_bif: m.prix_bif ?? '',
  est_disponible: m.disponible ?? true,
  duree_minutes: m.duree_minutes ?? 30,
  horaires: (m.horaires ?? []).map((h) => ({ jour: h.jour, debut: (h.debut || '').slice(0, 5), fin: (h.fin || '').slice(0, 5) })),
})

const title = (isEdit) => (isEdit ? 'Modifier le médecin' : 'Nouveau médecin')

export default function MedecinForm() {
  const { uuid } = useParams()
  const isEdit = Boolean(uuid)
  const detail = useApi(isEdit ? `/admin/medecins/${uuid}` : null, undefined, [uuid])

  if (isEdit && detail.loading) return <><PageTitle title={title(true)} /><Spinner /></>
  if (isEdit && detail.error) {
    return <><PageTitle title={title(true)} /><div className="alert alert-danger m-3">{detail.error.message}</div></>
  }

  return <MedecinFormFields initial={isEdit ? toForm(detail.data) : empty} uuid={uuid} isEdit={isEdit} />
}

function MedecinFormFields({ initial, uuid, isEdit }) {
  const navigate = useNavigate()
  const [form, setForm] = useState(initial)
  const [sending, setSending] = useState(false)
  const [error, setError] = useState(null)
  const [fieldErrors, setFieldErrors] = useState({})

  const set = (k, v) => { setForm((f) => ({ ...f, [k]: v })); setFieldErrors((e) => ({ ...e, [k]: undefined })) }
  const fieldErr = (name) => fieldErrors[name] || error?.field?.(name)

  // ── Horaires ──
  const setHoraire = (i, k, v) => setForm((f) => ({ ...f, horaires: f.horaires.map((h, j) => (j === i ? { ...h, [k]: v } : h)) }))
  const addHoraire = () => setForm((f) => ({ ...f, horaires: [...f.horaires, { jour: 'lundi', debut: '08:00', fin: '17:00' }] }))
  const removeHoraire = (i) => setForm((f) => ({ ...f, horaires: f.horaires.filter((_, j) => j !== i) }))

  const langues = () => form.langues.split(',').map((s) => s.trim()).filter(Boolean)

  const validate = () => {
    const e = {}
    if (!isEdit && form.mode === 'nouveau') {
      if (!form.email) e.email = 'Email obligatoire'
      if (!form.nom) e.nom = 'Nom obligatoire'
      if (!form.prenom) e.prenom = 'Prénom obligatoire'
    }
    if (!form.specialite.trim()) e.specialite = 'Spécialité obligatoire'
    if (!form.numero_licence.trim()) e.numero_licence = 'Numéro de licence obligatoire'
    if (!form.prix_usd || Number(form.prix_usd) <= 0) e.prix_usd = 'Tarif USD obligatoire'
    if (!form.prix_bif || Number(form.prix_bif) <= 0) e.prix_bif = 'Tarif BIF obligatoire'
    setFieldErrors(e)
    return Object.keys(e).length === 0
  }

  const submit = async (ev) => {
    ev.preventDefault()
    if (!validate()) return
    setSending(true)
    setError(null)
    try {
      const doctor = {
        specialite: form.specialite.trim(),
        numero_licence: form.numero_licence.trim(),
        annees_experience: Number(form.annees_experience) || 0,
        diplomes: form.diplomes || null,
        bio: form.bio || null,
        langues: langues(),
        whatsapp: form.whatsapp || null,
        prix_usd: Number(form.prix_usd),
        prix_bif: Number(form.prix_bif),
        est_disponible: form.est_disponible,
        duree_consultation_minutes: Number(form.duree_minutes) || 30,
      }
      if (isEdit) {
        await api.patch(`/admin/medecins/${uuid}`, doctor)
        await api.put(`/admin/medecins/${uuid}/horaires`, form.horaires)
        navigate(ROUTES.medecin(uuid))
      } else {
        const body = form.mode === 'existant'
          ? { user_uuid: form.user_uuid.trim(), ...doctor, horaires: form.horaires }
          : {
              email: form.email.trim(), nom: form.nom.trim(), prenom: form.prenom.trim(),
              telephone: form.telephone || null, genre: form.genre || null,
              ...doctor, horaires: form.horaires,
            }
        const created = await api.post('/admin/medecins', body)
        navigate(ROUTES.medecin(created.uuid))
      }
    } catch (err) {
      setError(err)
    } finally {
      setSending(false)
    }
  }

  return (
    <>
      <PageTitle
        title={title(isEdit)}
        crumbs={[{ label: 'Médecins', to: ROUTES.medecins }, { label: isEdit ? 'Modifier' : 'Nouveau' }]}
      />
      <section className="section">
        <form onSubmit={submit} noValidate>
          <div className="row g-3">
            {/* ── Compte ── */}
            {!isEdit && (
              <div className="col-12">
                <div className="card">
                  <div className="card-body">
                    <h5 className="card-title">Compte</h5>
                    <div className="row g-3">
                      <div className="col-12">
                        <div className="form-check form-check-inline">
                          <input className="form-check-input" type="radio" id="modeNouveau" checked={form.mode === 'nouveau'}
                                 onChange={() => set('mode', 'nouveau')} />
                          <label className="form-check-label" htmlFor="modeNouveau">Nouveau compte (invitation par email)</label>
                        </div>
                        <div className="form-check form-check-inline">
                          <input className="form-check-input" type="radio" id="modeExistant" checked={form.mode === 'existant'}
                                 onChange={() => set('mode', 'existant')} />
                          <label className="form-check-label" htmlFor="modeExistant">Compte médecin existant</label>
                        </div>
                      </div>

                      {form.mode === 'existant' ? (
                        <div className="col-md-6">
                          <label className="form-label">UUID du compte médecin</label>
                          <input className="form-control" value={form.user_uuid} placeholder="uuid"
                                 onChange={(e) => set('user_uuid', e.target.value)} />
                          {fieldErr('user_uuid') && <small className="text-danger">{fieldErr('user_uuid')}</small>}
                        </div>
                      ) : (
                        <>
                          <div className="col-md-6">
                            <label className="form-label">Email</label>
                            <input type="email" className="form-control" value={form.email}
                                   onChange={(e) => set('email', e.target.value)} />
                            {fieldErr('email') && <small className="text-danger">{fieldErr('email')}</small>}
                          </div>
                          <div className="col-md-3">
                            <label className="form-label">Nom</label>
                            <input className="form-control" value={form.nom} onChange={(e) => set('nom', e.target.value)} />
                            {fieldErr('nom') && <small className="text-danger">{fieldErr('nom')}</small>}
                          </div>
                          <div className="col-md-3">
                            <label className="form-label">Prénom</label>
                            <input className="form-control" value={form.prenom} onChange={(e) => set('prenom', e.target.value)} />
                            {fieldErr('prenom') && <small className="text-danger">{fieldErr('prenom')}</small>}
                          </div>
                          <div className="col-md-6">
                            <label className="form-label">Téléphone</label>
                            <input className="form-control" value={form.telephone} placeholder="+257…"
                                   onChange={(e) => set('telephone', e.target.value)} />
                          </div>
                          <div className="col-md-6">
                            <label className="form-label">Genre</label>
                            <select className="form-select" value={form.genre} onChange={(e) => set('genre', e.target.value)}>
                              <option value="">—</option>
                              <option value="M">Masculin</option>
                              <option value="F">Féminin</option>
                              <option value="Autre">Autre</option>
                            </select>
                          </div>
                        </>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* ── Fiche médicale ── */}
            <div className="col-12">
              <div className="card">
                <div className="card-body">
                  <h5 className="card-title">Fiche médicale</h5>
                  <div className="row g-3">
                    <div className="col-md-6">
                      <label className="form-label">Spécialité *</label>
                      <input className="form-control" value={form.specialite} onChange={(e) => set('specialite', e.target.value)} />
                      {fieldErr('specialite') && <small className="text-danger">{fieldErr('specialite')}</small>}
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Numéro de licence *</label>
                      <input className="form-control" value={form.numero_licence} onChange={(e) => set('numero_licence', e.target.value)} />
                      {fieldErr('numero_licence') && <small className="text-danger">{fieldErr('numero_licence')}</small>}
                    </div>
                    <div className="col-md-4">
                      <label className="form-label">Années d'expérience</label>
                      <input type="number" min="0" max="70" className="form-control" value={form.annees_experience}
                             onChange={(e) => set('annees_experience', e.target.value)} />
                    </div>
                    <div className="col-md-4">
                      <label className="form-label">Durée consultation (min)</label>
                      <input type="number" min="15" max="120" step="5" className="form-control" value={form.duree_minutes}
                             onChange={(e) => set('duree_minutes', e.target.value)} />
                    </div>
                    <div className="col-md-4">
                      <label className="form-label">WhatsApp</label>
                      <input className="form-control" value={form.whatsapp} placeholder="+257…"
                             onChange={(e) => set('whatsapp', e.target.value)} />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Tarif BIF *</label>
                      <input type="number" min="0" className="form-control" value={form.prix_bif} placeholder="40000"
                             onChange={(e) => set('prix_bif', e.target.value)} />
                      {fieldErr('prix_bif') && <small className="text-danger">{fieldErr('prix_bif')}</small>}
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Tarif étranger (USD) *</label>
                      <input type="number" min="0" step="0.01" className="form-control" value={form.prix_usd} placeholder="50.00"
                             onChange={(e) => set('prix_usd', e.target.value)} />
                      {fieldErr('prix_usd') && <small className="text-danger">{fieldErr('prix_usd')}</small>}
                    </div>
                    <div className="col-12">
                      <label className="form-label">Langues (séparées par des virgules)</label>
                      <input className="form-control" value={form.langues} placeholder="Français, Anglais, Kirundi"
                             onChange={(e) => set('langues', e.target.value)} />
                    </div>
                    <div className="col-12">
                      <label className="form-label">Diplômes</label>
                      <textarea className="form-control" rows="2" value={form.diplomes}
                                onChange={(e) => set('diplomes', e.target.value)} />
                    </div>
                    <div className="col-12">
                      <label className="form-label">Biographie</label>
                      <textarea className="form-control" rows="3" value={form.bio} onChange={(e) => set('bio', e.target.value)} />
                    </div>
                    <div className="col-12">
                      <div className="form-check">
                        <input className="form-check-input" type="checkbox" id="estDisponible" checked={form.est_disponible}
                               onChange={(e) => set('est_disponible', e.target.checked)} />
                        <label className="form-check-label" htmlFor="estDisponible">Disponible pour les consultations</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* ── Horaires ── */}
            <div className="col-12">
              <div className="card">
                <div className="card-body">
                  <div className="d-flex align-items-center justify-content-between">
                    <h5 className="card-title mb-0">Horaires de la semaine</h5>
                    <button type="button" className="btn btn-sm btn-outline-primary" onClick={addHoraire}>
                      <i className="bi bi-plus-lg me-1"></i> Ajouter une plage
                    </button>
                  </div>
                  {form.horaires.length === 0 && <p className="text-muted mt-3 mb-0">Aucune plage horaire définie.</p>}
                  {form.horaires.map((h, i) => (
                    <div key={i} className="row g-2 align-items-end mt-2">
                      <div className="col-md-4">
                        <label className="form-label small">Jour</label>
                        <select className="form-select form-select-sm" value={h.jour} onChange={(e) => setHoraire(i, 'jour', e.target.value)}>
                          {DAYS.map((d) => <option key={d} value={d}>{d}</option>)}
                        </select>
                      </div>
                      <div className="col-md-3">
                        <label className="form-label small">Début</label>
                        <input type="time" className="form-control form-control-sm" value={h.debut}
                               onChange={(e) => setHoraire(i, 'debut', e.target.value)} />
                      </div>
                      <div className="col-md-3">
                        <label className="form-label small">Fin</label>
                        <input type="time" className="form-control form-control-sm" value={h.fin}
                               onChange={(e) => setHoraire(i, 'fin', e.target.value)} />
                      </div>
                      <div className="col-md-2">
                        <button type="button" className="btn btn-sm btn-outline-danger w-100" onClick={() => removeHoraire(i)}>
                          <i className="bi bi-trash"></i>
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            {/* ── Actions ── */}
            <div className="col-12">
              {error && !Object.keys(fieldErrors).length && (
                <div className="alert alert-danger py-2">{error.message}</div>
              )}
              <div className="d-flex gap-2">
                <button type="submit" className="btn btn-primary" disabled={sending}>
                  {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : <i className="bi bi-check2 me-1"></i>}
                  {isEdit ? 'Enregistrer' : 'Créer le médecin'}
                </button>
                <Link to={isEdit ? ROUTES.medecin(uuid) : ROUTES.medecins} className="btn btn-outline-secondary">Annuler</Link>
              </div>
            </div>
          </div>
        </form>
      </section>
    </>
  )
}
