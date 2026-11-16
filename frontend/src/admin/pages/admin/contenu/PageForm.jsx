// Formulaire de page (CMS) + éditeur de sections.
// Page : POST/PUT /admin/contenu/pages · Sections : POST/PUT/DELETE + réordonnancement.
import { useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import api from '../../../lib/api'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'

const SECTION_TYPES = ['hero', 'texte', 'image_texte', 'tableau', 'liste', 'liste_card', 'grille', 'grille_card', 'html', 'timeline', 'cta']

const pageEmpty = {
  titre: '', slug: '', parent_id: '', menu_ordre: 0, afficher_menu: true, publiee: true,
  meta_description: '', meta_keywords: '', icone: '', contenu: '',
}

const toPage = (p) => ({
  titre: p.titre ?? '',
  slug: p.slug ?? '',
  parent_id: p.parent_id ?? '',
  menu_ordre: p.menu_ordre ?? 0,
  afficher_menu: p.afficher_menu ?? true,
  publiee: p.publiee ?? true,
  meta_description: p.meta_description ?? '',
  meta_keywords: p.meta_keywords ?? '',
  icone: p.icone ?? '',
  contenu: p.contenu ?? '',
})

const sectionEmpty = {
  type: 'texte', titre: '', sous_titre: '', contenu: '', image_url: '', image_droite: false,
  bouton_texte: '', bouton_lien: '', classe: '', options_json: '', ordre: 0, publiee: true,
}

const toSection = (s) => ({
  type: s.type ?? 'texte',
  titre: s.titre ?? '',
  sous_titre: s.sous_titre ?? '',
  contenu: s.contenu ?? '',
  image_url: s.image ?? '',
  image_droite: s.image_droite ?? false,
  bouton_texte: s.bouton_texte ?? '',
  bouton_lien: s.bouton_lien ?? '',
  classe: s.classe ?? '',
  options_json: s.options ? JSON.stringify(s.options) : '',
  ordre: s.ordre ?? 0,
  publiee: s.publiee ?? true,
})

function SectionForm({ open, initial, onClose, onSaved }) {
  const isEdit = Boolean(initial)
  const [form, setForm] = useState(() => (initial ? toSection(initial) : sectionEmpty))
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const build = () => ({
    type: form.type,
    titre: form.titre || null,
    sous_titre: form.sous_titre || null,
    contenu: form.contenu || null,
    image_url: form.image_url || null,
    image_droite: form.image_droite,
    bouton_texte: form.bouton_texte || null,
    bouton_lien: form.bouton_lien || null,
    classe: form.classe || null,
    options_json: form.options_json || null,
    ordre: Number(form.ordre) || 0,
    publiee: form.publiee,
  })

  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setError('')
    try {
      await onSaved(build())
      onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={open} onClose={onClose} icon="bi-layers" title={isEdit ? 'Modifier la section' : 'Nouvelle section'} size="lg">
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          <div className="col-md-6">
            <label className="form-label">Type</label>
            <select className="form-select" value={form.type} onChange={(e) => set('type', e.target.value)}>
              {SECTION_TYPES.map((t) => <option key={t} value={t}>{t}</option>)}
            </select>
          </div>
          <div className="col-md-6">
            <label className="form-label">Titre</label>
            <input className="form-control" value={form.titre} onChange={(e) => set('titre', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Sous-titre</label>
            <input className="form-control" value={form.sous_titre} onChange={(e) => set('sous_titre', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Contenu</label>
            <textarea className="form-control" rows="4" value={form.contenu} onChange={(e) => set('contenu', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Image (URL)</label>
            <input className="form-control" value={form.image_url} onChange={(e) => set('image_url', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Texte du bouton</label>
            <input className="form-control" value={form.bouton_texte} onChange={(e) => set('bouton_texte', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Lien du bouton</label>
            <input className="form-control" value={form.bouton_lien} onChange={(e) => set('bouton_lien', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Classe CSS</label>
            <input className="form-control" value={form.classe} onChange={(e) => set('classe', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Ordre</label>
            <input type="number" className="form-control" value={form.ordre} onChange={(e) => set('ordre', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Options (JSON, optionnel)</label>
            <textarea className="form-control" rows="2" style={{ fontFamily: 'monospace' }} value={form.options_json}
                      onChange={(e) => set('options_json', e.target.value)} />
          </div>
          <div className="col-12 d-flex gap-4">
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="secDroite" checked={form.image_droite}
                     onChange={(e) => set('image_droite', e.target.checked)} />
              <label className="form-check-label" htmlFor="secDroite">Image à droite</label>
            </div>
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="secPubliee" checked={form.publiee}
                     onChange={(e) => set('publiee', e.target.checked)} />
              <label className="form-check-label" htmlFor="secPubliee">Publiée</label>
            </div>
          </div>
          <div className="col-12 d-flex justify-content-end gap-2">
            <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
            <button type="submit" className="btn btn-primary" disabled={sending}>
              {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null}
              {isEdit ? 'Enregistrer' : 'Ajouter'}
            </button>
          </div>
        </div>
      </form>
    </Modal>
  )
}

export default function PageForm() {
  const { id } = useParams()
  const isEdit = Boolean(id)
  const detail = useApi(isEdit ? `/admin/contenu/pages/${id}` : null, undefined, [id])

  if (isEdit && detail.loading) return <><PageTitle title="Modifier la page" /><Spinner /></>
  if (isEdit && detail.error) return <><PageTitle title="Modifier la page" /><div className="alert alert-danger m-3">{detail.error.message}</div></>

  if (isEdit && !detail.data) return <><PageTitle title="Modifier la page" /><div className="alert alert-warning m-3">Page introuvable.</div></>

  // clé = id : le formulaire repart de zéro quand on passe d'une page à l'autre (ou de « nouvelle » à « modifier »)
  return <PageFormFields key={id ?? 'nouvelle'} initial={isEdit ? toPage(detail.data) : pageEmpty}
                         sections={isEdit ? (detail.data?.sections ?? []) : []} id={id} isEdit={isEdit} />
}

function PageFormFields({ initial, sections, id, isEdit }) {
  const navigate = useNavigate()
  const pages = useApi('/admin/contenu/pages')
  const [form, setForm] = useState(initial)
  const [items, setItems] = useState(sections)
  const [modal, setModal] = useState(null) // {mode:'new'} | {mode:'edit', section}
  const [sending, setSending] = useState(false)
  const [error, setError] = useState(null)

  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))
  const parentOptions = (pages.data ?? []).filter((p) => String(p.id) !== String(id))

  const body = {
    titre: form.titre.trim(),
    slug: form.slug || null,
    parent_id: form.parent_id ? Number(form.parent_id) : null,
    menu_ordre: Number(form.menu_ordre) || 0,
    afficher_menu: form.afficher_menu,
    publiee: form.publiee,
    meta_description: form.meta_description || null,
    meta_keywords: form.meta_keywords || null,
    icone: form.icone || null,
    contenu: form.contenu || null,
  }

  const savePage = async (e) => {
    e.preventDefault()
    if (!form.titre.trim()) return
    setSending(true)
    setError(null)
    try {
      if (isEdit) await api.put(`/admin/contenu/pages/${id}`, body)
      else { const created = await api.post('/admin/contenu/pages', body); navigate(ROUTES.page(created.id)); return }
    } catch (err) {
      setError(err)
    } finally {
      setSending(false)
    }
  }

  const addSection = async (data) => {
    const created = await api.post(`/admin/contenu/pages/${id}/sections`, data)
    setItems((s) => [...s, created])
  }

  const editSection = async (sectionId, data) => {
    await api.put(`/admin/contenu/sections/${sectionId}`, data)
    setItems((s) => s.map((x) => (x.id === sectionId ? { ...x, ...data } : x)))
  }

  const removeSection = async (sectionId) => {
    if (!window.confirm('Supprimer cette section ?')) return
    await api.delete(`/admin/contenu/sections/${sectionId}`)
    setItems((s) => s.filter((x) => x.id !== sectionId))
  }

  const move = async (i, dir) => {
    const j = i + dir
    if (j < 0 || j >= items.length) return
    const next = [...items]
    ;[next[i], next[j]] = [next[j], next[i]]
    setItems(next)
    try { await api.put(`/admin/contenu/pages/${id}/sections/ordre`, next.map((s) => s.id)) }
    catch { /* l'ordre sera resynchronisé au prochain chargement */ }
  }

  return (
    <>
      <PageTitle title={isEdit ? 'Modifier la page' : 'Nouvelle page'}
                 crumbs={[{ label: 'Contenu' }, { label: 'Pages', to: ROUTES.pages }, { label: isEdit ? 'Modifier' : 'Nouvelle' }]} />
      <section className="section">
        <div className="row g-3">
          {/* Page */}
          <div className="col-12">
            <form onSubmit={savePage} noValidate>
              <div className="card">
                <div className="card-body">
                  <h5 className="card-title">Informations de la page</h5>
                  <div className="row g-3">
                    <div className="col-md-8">
                      <label className="form-label">Titre *</label>
                      <input className="form-control" value={form.titre} onChange={(e) => set('titre', e.target.value)} />
                    </div>
                    <div className="col-md-4">
                      <label className="form-label">Adresse (slug)</label>
                      <input className="form-control" value={form.slug} placeholder="auto si vide" onChange={(e) => set('slug', e.target.value)} />
                    </div>
                    <div className="col-md-4">
                      <label className="form-label">Page parente</label>
                      <select className="form-select" value={form.parent_id ?? ''} onChange={(e) => set('parent_id', e.target.value)}>
                        <option value="">—</option>
                        {parentOptions.map((p) => <option key={p.id} value={p.id}>{p.titre}</option>)}
                      </select>
                    </div>
                    <div className="col-md-4">
                      <label className="form-label">Ordre dans le menu</label>
                      <input type="number" className="form-control" value={form.menu_ordre} onChange={(e) => set('menu_ordre', e.target.value)} />
                    </div>
                    <div className="col-md-4">
                      <label className="form-label">Icône (bootstrap)</label>
                      <input className="form-control" value={form.icone} onChange={(e) => set('icone', e.target.value)} />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Meta description</label>
                      <input className="form-control" value={form.meta_description} onChange={(e) => set('meta_description', e.target.value)} />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Meta keywords</label>
                      <input className="form-control" value={form.meta_keywords} onChange={(e) => set('meta_keywords', e.target.value)} />
                    </div>
                    <div className="col-12">
                      <label className="form-label">Contenu (HTML)</label>
                      <textarea className="form-control" rows="5" value={form.contenu} onChange={(e) => set('contenu', e.target.value)} />
                    </div>
                    <div className="col-12 d-flex gap-4">
                      <div className="form-check">
                        <input className="form-check-input" type="checkbox" id="pgPubliee" checked={form.publiee} onChange={(e) => set('publiee', e.target.checked)} />
                        <label className="form-check-label" htmlFor="pgPubliee">Publiée</label>
                      </div>
                      <div className="form-check">
                        <input className="form-check-input" type="checkbox" id="pgMenu" checked={form.afficher_menu} onChange={(e) => set('afficher_menu', e.target.checked)} />
                        <label className="form-check-label" htmlFor="pgMenu">Afficher dans le menu</label>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="card-footer d-flex gap-2">
                  {error && <div className="alert alert-danger py-2 mb-0 flex-grow-1">{error.message}</div>}
                  <button type="submit" className="btn btn-primary ms-auto" disabled={sending}>
                    {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : <i className="bi bi-check2 me-1"></i>}
                    Enregistrer la page
                  </button>
                  <Link to={ROUTES.pages} className="btn btn-outline-secondary">Retour</Link>
                </div>
              </div>
            </form>
          </div>

          {/* Sections (modification uniquement) */}
          {isEdit && (
            <div className="col-12">
              <div className="card">
                <div className="card-body">
                  <div className="d-flex align-items-center justify-content-between mb-3">
                    <h5 className="card-title mb-0">Sections ({items.length})</h5>
                    <button type="button" className="btn btn-sm btn-primary" onClick={() => setModal({ mode: 'new' })}>
                      <i className="bi bi-plus-lg me-1"></i> Ajouter une section
                    </button>
                  </div>

                  {items.length === 0 ? (
                    <p className="text-muted mb-0">Aucune section. Ajoutez la première section de cette page.</p>
                  ) : (
                    <table className="table table-sm align-middle mb-0">
                      <tbody>
                        {items.map((s, i) => (
                          <tr key={s.id}>
                            <td style={{ width: 40 }}><code>{s.type}</code></td>
                            <td>
                              <div className="fw-semibold">{s.titre || '(sans titre)'}</div>
                              <small className="text-muted">ordre {s.ordre ?? i}</small>
                            </td>
                            <td className="text-end" style={{ width: 160 }}>
                              <button type="button" className="btn btn-sm btn-outline-secondary me-1" disabled={i === 0} onClick={() => move(i, -1)}>
                                <i className="bi bi-arrow-up"></i>
                              </button>
                              <button type="button" className="btn btn-sm btn-outline-secondary me-1" disabled={i === items.length - 1} onClick={() => move(i, 1)}>
                                <i className="bi bi-arrow-down"></i>
                              </button>
                              <button type="button" className="btn btn-sm btn-outline-primary me-1" onClick={() => setModal({ mode: 'edit', section: s })}>
                                <i className="bi bi-pencil"></i>
                              </button>
                              <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => removeSection(s.id)}>
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
          )}
        </div>
      </section>

      {modal?.mode === 'new' && (
        <SectionForm open onClose={() => setModal(null)} onSaved={addSection} />
      )}
      {modal?.mode === 'edit' && (
        <SectionForm open initial={modal.section} onClose={() => setModal(null)}
                     onSaved={(data) => editSection(modal.section.id, data)} />
      )}
    </>
  )
}
