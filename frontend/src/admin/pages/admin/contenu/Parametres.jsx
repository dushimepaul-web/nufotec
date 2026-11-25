// Paramètres du site — GET/PUT /admin/contenu/parametres + envoi d'image par paramètre.
import { useState } from 'react'
import api from '../../../lib/api'
import useApi from '../../../lib/useApi'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'

/** Valeur d'édition (chaîne) selon le type du paramètre. */
const toEdit = (p) => {
  if (p.type === 'boolean') return p.valeur_affichee ? 'true' : 'false'
  if (p.type === 'json') return typeof p.valeur_affichee === 'string' ? p.valeur_affichee : JSON.stringify(p.valeur_affichee, null, 2)
  if (p.valeur_affichee == null) return ''
  return String(p.valeur_affichee)
}

const groupBy = (list) => {
  const groups = new Map()
  for (const p of list) {
    const key = p.categorie || 'général'
    if (!groups.has(key)) groups.set(key, [])
    groups.get(key).push(p)
  }
  return [...groups.entries()]
}

export default function Parametres() {
  const [version, setVersion] = useState(0)
  const settings = useApi('/admin/contenu/parametres', undefined, [version])

  if (settings.loading) return <><PageTitle title="Paramètres" /><Spinner /></>
  if (settings.error) return <><PageTitle title="Paramètres" /><div className="alert alert-danger m-3">{settings.error.message}</div></>

  return <ParametresForm settings={settings.data ?? []} reload={() => setVersion((v) => v + 1)} />
}

function ParametresForm({ settings, reload }) {
  const [values, setValues] = useState(() => Object.fromEntries(settings.map((p) => [p.cle, toEdit(p)])))
  const [saving, setSaving] = useState(false)
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')

  const set = (cle, v) => { setValues((f) => ({ ...f, [cle]: v })); setMessage('') }

  const uploadImage = async (cle, file) => {
    if (!file) return
    setSaving(true)
    setError('')
    try {
      const fd = new FormData()
      fd.append('image', file)
      await api.post(`/admin/contenu/parametres/${cle}/image`, fd)
      reload()
      setMessage('Image enregistrée')
    } catch (err) {
      setError(err.message)
    } finally {
      setSaving(false)
    }
  }

  const save = async () => {
    setSaving(true)
    setError('')
    setMessage('')
    try {
      const valeurs = {}
      for (const p of settings) {
        if (p.type === 'image') continue
        const v = values[p.cle] ?? ''
        if (p.type === 'boolean') valeurs[p.cle] = v === 'true'
        else if (p.type === 'nombre') valeurs[p.cle] = v === '' ? null : v
        else valeurs[p.cle] = v
      }
      await api.put('/admin/contenu/parametres', { valeurs })
      setMessage('Paramètres enregistrés')
    } catch (err) {
      setError(err.message)
    } finally {
      setSaving(false)
    }
  }

  const groups = groupBy(settings)

  return (
    <>
      <PageTitle title="Paramètres" crumbs={[{ label: 'Contenu', to: '/admin/contenu/pages' }, { label: 'Paramètres' }]} />
      <section className="section">
        {message && <div className="alert alert-success py-2">{message}</div>}
        {error && <div className="alert alert-danger py-2">{error}</div>}

        {groups.map(([cat, params]) => (
          <div className="card mb-3" key={cat}>
            <div className="card-body">
              <h5 className="card-title text-capitalize">{cat}</h5>
              <div className="row g-3">
                {params.map((p) => (
                  <div className={p.type === 'json' || p.type === 'texte' ? 'col-12' : 'col-md-6'} key={p.cle}>
                    <label className="form-label">
                      {p.cle}
                      {p.description && <span className="text-muted small"> — {p.description}</span>}
                    </label>

                    {p.type === 'image' ? (
                      <div className="d-flex align-items-center gap-2">
                        {p.valeur_affichee && <img loading="lazy" decoding="async" src={p.valeur_affichee} alt="" width="48" height="48" className="rounded object-fit-cover" />}
                        <input type="file" accept="image/*" className="form-control form-control-sm"
                               onChange={(e) => uploadImage(p.cle, e.target.files[0])} />
                      </div>
                    ) : p.type === 'boolean' ? (
                      <select className="form-select" value={values[p.cle]} onChange={(e) => set(p.cle, e.target.value)}>
                        <option value="false">Non</option>
                        <option value="true">Oui</option>
                      </select>
                    ) : p.type === 'nombre' ? (
                      <input type="number" className="form-control" value={values[p.cle]} onChange={(e) => set(p.cle, e.target.value)} />
                    ) : p.type === 'json' ? (
                      <textarea className="form-control" rows="4" style={{ fontFamily: 'monospace' }} value={values[p.cle]}
                                onChange={(e) => set(p.cle, e.target.value)} />
                    ) : (
                      <input className="form-control" value={values[p.cle]} onChange={(e) => set(p.cle, e.target.value)} />
                    )}
                  </div>
                ))}
              </div>
            </div>
          </div>
        ))}

        <div className="d-flex gap-2">
          <button type="button" className="btn btn-primary" onClick={save} disabled={saving}>
            {saving ? <span className="spinner-border spinner-border-sm me-1"></span> : <i className="bi bi-check2 me-1"></i>}
            Enregistrer
          </button>
        </div>
      </section>
    </>
  )
}
