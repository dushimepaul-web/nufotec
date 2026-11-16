// Formulaire produit (création + modification).
// Création : POST /admin/produits ; modification : PATCH /admin/produits/{uuid}.
// Image principale et galerie envoyées en multipart après l'enregistrement des champs.
import { useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import api from '../../../lib/api'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'

const FALLBACK = '/default-product.svg'

const empty = {
  titre: '', description: '', categorie_id: '', prix_bif: '', prix_usd: '',
  stock: '', produit_sante: false, publie: true, vedette: false,
}

const toForm = (p) => ({
  titre: p.titre ?? '',
  description: p.description ?? '',
  categorie_id: p.categorie?.id ?? '',
  prix_bif: p.prix_bif ?? '',
  prix_usd: p.prix_usd ?? '',
  stock: p.stock ?? '',
  produit_sante: p.produit_sante ?? false,
  publie: p.publie ?? true,
  vedette: p.vedette ?? false,
})

/** Aplatit l'arbre des catégories en liste { id, nom } avec indentation. */
const flatten = (cats, depth = 0, out = []) => {
  for (const c of cats ?? []) {
    out.push({ id: c.id, nom: `${'— '.repeat(depth)}${c.nom}` })
    flatten(c.sous_categories, depth + 1, out)
  }
  return out
}

export default function ProduitForm() {
  const { uuid } = useParams()
  const isEdit = Boolean(uuid)
  const detail = useApi(isEdit ? `/admin/produits/${uuid}` : null, undefined, [uuid])

  if (isEdit && detail.loading) return <><PageTitle title="Modifier le produit" /><Spinner /></>
  if (isEdit && detail.error) {
    return <><PageTitle title="Modifier le produit" /><div className="alert alert-danger m-3">{detail.error.message}</div></>
  }

  return <ProduitFormFields initial={isEdit ? toForm(detail.data) : empty} uuid={uuid} isEdit={isEdit} detail={detail.data} />
}

function ProduitFormFields({ initial, uuid, isEdit, detail }) {
  const navigate = useNavigate()
  const categories = useApi('/produits/categories')
  const [form, setForm] = useState(initial)
  const [image, setImage] = useState(null)
  const [preview, setPreview] = useState(null)
  const [sending, setSending] = useState(false)
  const [error, setError] = useState(null)

  const flat = flatten(categories.data)

  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const pickImage = (e) => {
    const file = e.target.files[0]
    setImage(file)
    setPreview(file ? URL.createObjectURL(file) : null)
  }

  const upload = (id, file) => {
    const fd = new FormData()
    fd.append('image', file)
    return api.post(`/admin/produits/${id}/image`, fd)
  }

  const body = {
    titre: form.titre.trim(),
    description: form.description || null,
    categorie_id: form.categorie_id ? Number(form.categorie_id) : null,
    prix_bif: form.prix_bif === '' ? null : Number(form.prix_bif),
    prix_usd: form.prix_usd === '' ? null : Number(form.prix_usd),
    stock: form.stock === '' ? null : Number(form.stock),
    produit_sante: form.produit_sante,
    publie: form.publie,
    vedette: form.vedette,
  }

  const submit = async (e) => {
    e.preventDefault()
    if (!form.titre.trim()) return
    setSending(true)
    setError(null)
    try {
      if (isEdit) {
        await api.patch(`/admin/produits/${uuid}`, body)
        if (image) await upload(uuid, image)
        navigate(ROUTES.produits)
      } else {
        const created = await api.post('/admin/produits', body)
        if (image) await upload(created.uuid, image)
        navigate(ROUTES.produits)
      }
    } catch (err) {
      setError(err)
    } finally {
      setSending(false)
    }
  }

  const remove = async () => {
    if (!window.confirm('Supprimer ce produit ? L’historique des commandes est conservé.')) return
    setSending(true)
    try {
      await api.delete(`/admin/produits/${uuid}`)
      navigate(ROUTES.produits)
    } catch (err) {
      setError(err)
      setSending(false)
    }
  }

  // ── Galerie (modification uniquement) ──
  const [galFile, setGalFile] = useState(null)
  const [galerie, setGalerie] = useState(detail?.galerie ?? [])

  const addGallery = async () => {
    if (!galFile) return
    setSending(true)
    try {
      const fd = new FormData()
      fd.append('image', galFile)
      const p = await api.post(`/admin/produits/${uuid}/galerie`, fd)
      setGalerie(p.galerie ?? [])
      setGalFile(null)
    } catch (err) {
      setError(err)
    } finally {
      setSending(false)
    }
  }

  const removeGallery = async (imageId) => {
    setSending(true)
    try {
      const p = await api.delete(`/admin/produits/${uuid}/galerie/${imageId}`)
      setGalerie(p.galerie ?? [])
    } catch (err) {
      setError(err)
    } finally {
      setSending(false)
    }
  }

  return (
    <>
      <PageTitle title={isEdit ? 'Modifier le produit' : 'Nouveau produit'}
                 crumbs={[{ label: 'Produits', to: ROUTES.produits }, { label: isEdit ? 'Modifier' : 'Nouveau' }]} />
      <section className="section">
        <form onSubmit={submit} noValidate>
          <div className="row g-3">
            <div className="col-lg-8">
              <div className="card">
                <div className="card-body">
                  <h5 className="card-title">Informations</h5>
                  <div className="row g-3">
                    <div className="col-12">
                      <label className="form-label">Titre *</label>
                      <input className="form-control" value={form.titre} onChange={(e) => set('titre', e.target.value)} />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Catégorie</label>
                      <select className="form-select" value={form.categorie_id} onChange={(e) => set('categorie_id', e.target.value)}>
                        <option value="">Sans catégorie</option>
                        {flat.map((c) => <option key={c.id} value={c.id}>{c.nom}</option>)}
                      </select>
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Stock (vide = non suivi)</label>
                      <input type="number" min="0" className="form-control" value={form.stock}
                             onChange={(e) => set('stock', e.target.value)} />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Prix BIF</label>
                      <input type="number" min="0" className="form-control" value={form.prix_bif} placeholder="15000"
                             onChange={(e) => set('prix_bif', e.target.value)} />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Prix USD</label>
                      <input type="number" min="0" step="0.01" className="form-control" value={form.prix_usd} placeholder="5.00"
                             onChange={(e) => set('prix_usd', e.target.value)} />
                    </div>
                    <div className="col-12">
                      <label className="form-label">Description</label>
                      <textarea className="form-control" rows="5" value={form.description}
                                onChange={(e) => set('description', e.target.value)} />
                    </div>
                    <div className="col-12 d-flex flex-wrap gap-4">
                      <div className="form-check">
                        <input className="form-check-input" type="checkbox" id="publie" checked={form.publie}
                               onChange={(e) => set('publie', e.target.checked)} />
                        <label className="form-check-label" htmlFor="publie">Publié</label>
                      </div>
                      <div className="form-check">
                        <input className="form-check-input" type="checkbox" id="vedette" checked={form.vedette}
                               onChange={(e) => set('vedette', e.target.checked)} />
                        <label className="form-check-label" htmlFor="vedette">Vedette</label>
                      </div>
                      <div className="form-check">
                        <input className="form-check-input" type="checkbox" id="produitSante" checked={form.produit_sante}
                               onChange={(e) => set('produit_sante', e.target.checked)} />
                        <label className="form-check-label" htmlFor="produitSante">Produit de santé</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className="col-lg-4">
              {/* Image principale */}
              <div className="card mb-3">
                <div className="card-body">
                  <h5 className="card-title">Image principale</h5>
                  <img src={preview || detail?.image || FALLBACK} alt="" className="img-fluid rounded mb-3" />
                  <input type="file" accept="image/*" className="form-control form-control-sm" onChange={pickImage} />
                </div>
              </div>

              {/* Galerie (modification) */}
              {isEdit && (
                <div className="card">
                  <div className="card-body">
                    <h5 className="card-title">Galerie</h5>
                    <div className="d-flex flex-wrap gap-2 mb-3">
                      {galerie.map((g) => (
                        <div key={g.id} className="position-relative">
                          <img src={g.url} alt="" width="56" height="56" className="rounded object-fit-cover" />
                          <button type="button" className="btn btn-sm btn-danger position-absolute top-0 end-0 p-0" style={{ width: 20, height: 20, lineHeight: 1 }}
                                  onClick={() => removeGallery(g.id)}>
                            <i className="bi bi-x" style={{ fontSize: 14 }}></i>
                          </button>
                        </div>
                      ))}
                      {galerie.length === 0 && <p className="text-muted small mb-0">Aucune image de galerie.</p>}
                    </div>
                    <div className="d-flex gap-2">
                      <input type="file" accept="image/*" className="form-control form-control-sm"
                             onChange={(e) => setGalFile(e.target.files[0])} />
                      <button type="button" className="btn btn-sm btn-outline-primary" disabled={!galFile || sending}
                              onClick={addGallery}>Ajouter</button>
                    </div>
                  </div>
                </div>
              )}
            </div>

            <div className="col-12">
              {error && <div className="alert alert-danger py-2">{error.message}</div>}
              <div className="d-flex gap-2">
                <button type="submit" className="btn btn-primary" disabled={sending}>
                  {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : <i className="bi bi-check2 me-1"></i>}
                  {isEdit ? 'Enregistrer' : 'Créer le produit'}
                </button>
                <Link to={ROUTES.produits} className="btn btn-outline-secondary">Annuler</Link>
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
