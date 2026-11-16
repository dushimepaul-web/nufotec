// Formulaire d'article (création + modification) — GET/POST/PUT /admin/contenu/actualites.
// Image principale envoyée en multipart après l'enregistrement.
import { useState } from 'react'
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom'
import api from '../../../lib/api'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'

const empty = {
  titre: '', resume: '', contenu: '', auteur: '', categorie: '',
  tags: '', a_la_une: false, publie: false, date_publication: '',
}

const toForm = (a) => ({
  titre: a.titre ?? '',
  resume: a.resume ?? '',
  contenu: a.contenu ?? '',
  auteur: a.auteur ?? '',
  categorie: a.categorie ?? '',
  tags: (a.tags ?? []).join(', '),
  a_la_une: a.a_la_une ?? false,
  publie: a.publie ?? false,
  // On pré-remplit la date seulement pour une publication programmée (dans le futur).
  date_publication: a.etat === 'programme' ? (a.date_publication || '').slice(0, 16) : '',
})

export default function ActualiteForm() {
  const { id } = useParams()
  const { state } = useLocation()
  const isEdit = Boolean(id)
  // Article chargé depuis l'API (GET /admin/contenu/actualites/{id}) : la page marche aussi ouverte directement
  // ou rechargée. L'article transmis par la liste sert seulement à afficher le formulaire sans attendre.
  const fetched = useApi(isEdit && !state?.article ? `/admin/contenu/actualites/${id}` : null, undefined, [id])
  const article = state?.article ?? fetched.data

  if (isEdit && !article) {
    return (
      <>
        <PageTitle title="Modifier l'article" />
        <section className="section">
          {fetched.loading ? <Spinner /> : (
            <div className="alert alert-warning">
              Article introuvable. Revenez à la <Link to={ROUTES.actualites} className="alert-link">liste des actualités</Link>.
            </div>
          )}
        </section>
      </>
    )
  }

  return <ActualiteFormFields key={article?.id ?? 'nouveau'} initial={isEdit ? toForm(article) : empty} id={id} isEdit={isEdit} />
}

function ActualiteFormFields({ initial, id, isEdit }) {
  const navigate = useNavigate()
  const [form, setForm] = useState(initial)
  const [image, setImage] = useState(null)
  const [preview, setPreview] = useState(null)
  const [sending, setSending] = useState(false)
  const [error, setError] = useState(null)

  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const pickImage = (e) => {
    const file = e.target.files[0]
    setImage(file)
    setPreview(file ? URL.createObjectURL(file) : null)
  }

  const body = {
    titre: form.titre.trim(),
    resume: form.resume || null,
    contenu: form.contenu || null,
    auteur: form.auteur || null,
    categorie: form.categorie || null,
    tags: form.tags.split(',').map((s) => s.trim()).filter(Boolean),
    a_la_une: form.a_la_une,
    publie: form.publie,
    date_publication: form.date_publication || null,
  }

  const submit = async (e) => {
    e.preventDefault()
    if (!form.titre.trim()) return
    setSending(true)
    setError(null)
    try {
      if (isEdit) {
        await api.put(`/admin/contenu/actualites/${id}`, body)
        if (image) {
          const fd = new FormData()
          fd.append('image', image)
          await api.post(`/admin/contenu/actualites/${id}/image`, fd)
        }
        navigate(ROUTES.actualites)
      } else {
        const created = await api.post('/admin/contenu/actualites', body)
        if (image) {
          const fd = new FormData()
          fd.append('image', image)
          await api.post(`/admin/contenu/actualites/${created.id}/image`, fd)
        }
        navigate(ROUTES.actualites)
      }
    } catch (err) {
      setError(err)
    } finally {
      setSending(false)
    }
  }

  const remove = async () => {
    if (!window.confirm('Supprimer cet article ?')) return
    setSending(true)
    try {
      await api.delete(`/admin/contenu/actualites/${id}`)
      navigate(ROUTES.actualites)
    } catch (err) {
      setError(err)
      setSending(false)
    }
  }

  return (
    <>
      <PageTitle title={isEdit ? "Modifier l'article" : 'Nouvel article'}
                 crumbs={[{ label: 'Contenu', to: ROUTES.contenu }, { label: 'Actualités', to: ROUTES.actualites }, { label: isEdit ? 'Modifier' : 'Nouveau' }]} />
      <section className="section">
        <form onSubmit={submit} noValidate>
          <div className="row g-3">
            <div className="col-lg-8">
              <div className="card">
                <div className="card-body">
                  <h5 className="card-title">Article</h5>
                  <div className="row g-3">
                    <div className="col-12">
                      <label className="form-label">Titre *</label>
                      <input className="form-control" value={form.titre} onChange={(e) => set('titre', e.target.value)} />
                    </div>
                    <div className="col-12">
                      <label className="form-label">Résumé</label>
                      <textarea className="form-control" rows="2" value={form.resume} onChange={(e) => set('resume', e.target.value)} />
                    </div>
                    <div className="col-12">
                      <label className="form-label">Contenu</label>
                      <textarea className="form-control" rows="10" value={form.contenu} onChange={(e) => set('contenu', e.target.value)} />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className="col-lg-4">
              <div className="card mb-3">
                <div className="card-body">
                  <h5 className="card-title">Image</h5>
                  {preview && <img src={preview} alt="" className="img-fluid rounded mb-3" />}
                  <input type="file" accept="image/*" className="form-control form-control-sm" onChange={pickImage} />
                </div>
              </div>

              <div className="card">
                <div className="card-body">
                  <h5 className="card-title">Métadonnées</h5>
                  <div className="row g-3">
                    <div className="col-md-6">
                      <label className="form-label">Auteur</label>
                      <input className="form-control" value={form.auteur} onChange={(e) => set('auteur', e.target.value)} />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Catégorie</label>
                      <input className="form-control" value={form.categorie} onChange={(e) => set('categorie', e.target.value)} />
                    </div>
                    <div className="col-12">
                      <label className="form-label">Tags (séparés par des virgules)</label>
                      <input className="form-control" value={form.tags} onChange={(e) => set('tags', e.target.value)} />
                    </div>
                    <div className="col-12">
                      <label className="form-label">Date de publication (pour programmer)</label>
                      <input type="datetime-local" className="form-control" value={form.date_publication}
                             onChange={(e) => set('date_publication', e.target.value)} />
                    </div>
                    <div className="col-12 d-flex flex-column gap-2">
                      <div className="form-check">
                        <input className="form-check-input" type="checkbox" id="publie" checked={form.publie}
                               onChange={(e) => set('publie', e.target.checked)} />
                        <label className="form-check-label" htmlFor="publie">Publié</label>
                      </div>
                      <div className="form-check">
                        <input className="form-check-input" type="checkbox" id="alaune" checked={form.a_la_une}
                               onChange={(e) => set('a_la_une', e.target.checked)} />
                        <label className="form-check-label" htmlFor="alaune">À la une</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className="col-12">
              {error && <div className="alert alert-danger py-2">{error.message}</div>}
              <div className="d-flex gap-2">
                <button type="submit" className="btn btn-primary" disabled={sending}>
                  {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : <i className="bi bi-check2 me-1"></i>}
                  {isEdit ? 'Enregistrer' : "Créer l'article"}
                </button>
                <Link to={ROUTES.actualites} className="btn btn-outline-secondary">Annuler</Link>
                {isEdit && (
                  <button type="button" className="btn btn-outline-danger ms-auto" onClick={remove} disabled={sending}>
                    <i className="bi bi-trash me-1"></i> Supprimer
                  </button>
                )}
              </div>
            </div>
          </div>
        </form>
      </section>
    </>
  )
}
