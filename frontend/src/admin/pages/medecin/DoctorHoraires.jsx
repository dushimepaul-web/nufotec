// Horaires hebdomadaires du médecin — horaires depuis /medecin/profil, enregistrement via PUT /medecin/horaires.
import { useState } from 'react'
import api from '../../lib/api'
import useApi from '../../lib/useApi'
import PageTitle from '../../ui/PageTitle'
import Spinner from '../../ui/Spinner'

const DAYS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche']

export default function DoctorHoraires() {
  const profil = useApi('/medecin/profil')

  if (profil.loading) return <><PageTitle title="Mes horaires" /><Spinner /></>
  if (profil.error) return <><PageTitle title="Mes horaires" /><div className="alert alert-danger m-3">{profil.error.message}</div></>

  return <HorairesForm initial={(profil.data?.horaires ?? []).map((h) => ({ jour: h.jour, debut: (h.debut || '').slice(0, 5), fin: (h.fin || '').slice(0, 5) }))} />
}

function HorairesForm({ initial }) {
  const [horaires, setHoraires] = useState(initial)
  const [sending, setSending] = useState(false)
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')

  const setH = (i, k, v) => setHoraires((h) => h.map((x, j) => (j === i ? { ...x, [k]: v } : x)))
  const add = () => setHoraires((h) => [...h, { jour: 'lundi', debut: '08:00', fin: '17:00' }])
  const remove = (i) => setHoraires((h) => h.filter((_, j) => j !== i))

  const save = async () => {
    setSending(true)
    setError('')
    setMessage('')
    try {
      await api.put('/medecin/horaires', horaires)
      setMessage('Horaires enregistrés')
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <>
      <PageTitle title="Mes horaires" crumbs={[{ label: 'Tableau de bord', to: '/medecin' }, { label: 'Mes horaires' }]} />
      <section className="section">
        <div className="card">
          <div className="card-body">
            <div className="d-flex align-items-center justify-content-between mb-3">
              <h5 className="card-title mb-0">Horaires de la semaine</h5>
              <button type="button" className="btn btn-sm btn-outline-primary" onClick={add}>
                <i className="bi bi-plus-lg me-1"></i> Ajouter une plage
              </button>
            </div>

            {message && <div className="alert alert-success py-2">{message}</div>}
            {error && <div className="alert alert-danger py-2">{error}</div>}

            {horaires.length === 0 && <p className="text-muted">Aucun horaire défini.</p>}
            {horaires.map((h, i) => (
              <div key={i} className="row g-2 align-items-end mb-2">
                <div className="col-md-4">
                  <label className="form-label small">Jour</label>
                  <select className="form-select form-select-sm" value={h.jour} onChange={(e) => setH(i, 'jour', e.target.value)}>
                    {DAYS.map((d) => <option key={d} value={d}>{d}</option>)}
                  </select>
                </div>
                <div className="col-md-3">
                  <label className="form-label small">Début</label>
                  <input type="time" className="form-control form-control-sm" value={h.debut} onChange={(e) => setH(i, 'debut', e.target.value)} />
                </div>
                <div className="col-md-3">
                  <label className="form-label small">Fin</label>
                  <input type="time" className="form-control form-control-sm" value={h.fin} onChange={(e) => setH(i, 'fin', e.target.value)} />
                </div>
                <div className="col-md-2">
                  <button type="button" className="btn btn-sm btn-outline-danger w-100" onClick={() => remove(i)}>
                    <i className="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            ))}

            <button type="button" className="btn btn-primary mt-3" onClick={save} disabled={sending}>
              {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null} Enregistrer
            </button>
          </div>
        </div>
      </section>
    </>
  )
}
