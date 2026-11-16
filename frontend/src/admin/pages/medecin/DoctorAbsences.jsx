// Absences et congés du médecin — GET/POST/DELETE /medecin/absences.
import { useState } from 'react'
import api from '../../lib/api'
import { formatDateTime } from '../../lib/format'
import useApi from '../../lib/useApi'
import PageTitle from '../../ui/PageTitle'
import Spinner from '../../ui/Spinner'

export default function DoctorAbsences() {
  const [version, setVersion] = useState(0)
  const [form, setForm] = useState({ debut: '', fin: '', motif: '' })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')

  const absences = useApi('/medecin/absences', undefined, [version])
  const items = absences.data ?? []

  const submit = async (e) => {
    e.preventDefault()
    if (!form.debut || !form.fin) return
    setSending(true)
    setError('')
    try {
      const r = await api.post('/medecin/absences', { debut: form.debut, fin: form.fin, motif: form.motif || null })
      setForm({ debut: '', fin: '', motif: '' })
      setVersion((v) => v + 1)
      if (r.rendez_vous_en_conflit?.length) {
        window.alert(`Absence enregistrée. ${r.rendez_vous_en_conflit.length} rendez-vous en conflit à traiter.`)
      }
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  const remove = async (id) => {
    if (!window.confirm('Supprimer cette absence ?')) return
    try { await api.delete(`/medecin/absences/${id}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }

  return (
    <>
      <PageTitle title="Mes absences" crumbs={[{ label: 'Tableau de bord', to: '/medecin' }, { label: 'Mes absences' }]} />
      <section className="section">
        <div className="row g-3">
          <div className="col-lg-5">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Déclarer une absence</h5>
                <form onSubmit={submit} noValidate>
                  {error && <div className="alert alert-danger py-2 small">{error}</div>}
                  <div className="row g-3">
                    <div className="col-12">
                      <label className="form-label">Du</label>
                      <input type="datetime-local" className="form-control" value={form.debut}
                             onChange={(e) => setForm({ ...form, debut: e.target.value })} />
                    </div>
                    <div className="col-12">
                      <label className="form-label">Au</label>
                      <input type="datetime-local" className="form-control" value={form.fin}
                             onChange={(e) => setForm({ ...form, fin: e.target.value })} />
                    </div>
                    <div className="col-12">
                      <label className="form-label">Motif</label>
                      <input className="form-control" value={form.motif} placeholder="Congés, maladie…"
                             onChange={(e) => setForm({ ...form, motif: e.target.value })} />
                    </div>
                    <div className="col-12">
                      <button type="submit" className="btn btn-primary" disabled={sending || !form.debut || !form.fin}>
                        {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null} Déclarer
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div className="col-lg-7">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Mes absences</h5>
                {absences.loading ? <Spinner /> : items.length === 0 ? (
                  <p className="text-muted mb-0">Aucune absence déclarée.</p>
                ) : (
                  <table className="table table-sm align-middle mb-0">
                    <thead><tr><th>Période</th><th>Motif</th><th></th></tr></thead>
                    <tbody>
                      {items.map((a) => (
                        <tr key={a.id}>
                          <td className="text-nowrap small">{formatDateTime(a.debut)} → {formatDateTime(a.fin)}</td>
                          <td>{a.motif || '—'}</td>
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
          </div>
        </div>
      </section>
    </>
  )
}
