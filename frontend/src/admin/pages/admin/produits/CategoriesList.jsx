// Catégories de produits — arbre depuis GET /admin/produits/categories (inactives comprises), CRUD via /admin/produits/categories.
import { useState } from 'react'
import api from '../../../lib/api'
import { getUser } from '../../../lib/auth'
import { can } from '../../../lib/permissions'
import useApi from '../../../lib/useApi'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'

/** Aplatit l'arbre en gardant la profondeur et le parent pour les formulaires. */
const flatten = (cats, parentId = null, depth = 0, out = []) => {
  for (const c of cats ?? []) {
    out.push({ ...c, parent_id: parentId, depth })
    flatten(c.sous_categories, c.id, depth + 1, out)
  }
  return out
}

const empty = { nom: '', code: '', description: '', parent_id: '', actif: true }

function CategorieForm({ open, initial, all, onClose, onSaved }) {
  const isEdit = Boolean(initial)
  const [form, setForm] = useState(() => (initial ? { ...initial, actif: initial.actif ?? true } : empty))
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')

  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const parentOptions = (all ?? []).filter((c) => c.id !== initial?.id)

  const submit = async (e) => {
    e.preventDefault()
    if (!form.nom.trim()) return
    setSending(true)
    setError('')
    try {
      const body = {
        nom: form.nom.trim(),
        code: form.code || null,
        description: form.description || null,
        parent_id: form.parent_id ? Number(form.parent_id) : null,
        actif: form.actif,
      }
      if (isEdit) await api.put(`/admin/produits/categories/${initial.id}`, body)
      else await api.post('/admin/produits/categories', body)
      onSaved()
      onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={open} onClose={onClose} icon="bi-tags" title={isEdit ? 'Modifier la catégorie' : 'Nouvelle catégorie'}>
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          <div className="col-12">
            <label className="form-label">Nom *</label>
            <input className="form-control" value={form.nom} onChange={(e) => set('nom', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Code</label>
            <input className="form-control" value={form.code} onChange={(e) => set('code', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Catégorie parente</label>
            <select className="form-select" value={form.parent_id ?? ''} onChange={(e) => set('parent_id', e.target.value)}>
              <option value="">—</option>
              {parentOptions.map((c) => <option key={c.id} value={c.id}>{c.nom}</option>)}
            </select>
          </div>
          <div className="col-12">
            <label className="form-label">Description</label>
            <input className="form-control" value={form.description} onChange={(e) => set('description', e.target.value)} />
          </div>
          <div className="col-12">
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="catActif" checked={form.actif}
                     onChange={(e) => set('actif', e.target.checked)} />
              <label className="form-check-label" htmlFor="catActif">Active</label>
            </div>
          </div>
          <div className="col-12 d-flex justify-content-end gap-2">
            <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
            <button type="submit" className="btn btn-primary" disabled={sending}>
              {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null}
              {isEdit ? 'Enregistrer' : 'Créer'}
            </button>
          </div>
        </div>
      </form>
    </Modal>
  )
}

export default function CategoriesList() {
  const [version, setVersion] = useState(0)
  const [modal, setModal] = useState(null) // null | { mode:'new' } | { mode:'edit', cat }
  // route admin : toutes les catégories, inactives comprises (la route publique ne renvoie que les actives)
  const categories = useApi('/admin/produits/categories', undefined, [version])
  const items = flatten(categories.data)
  const user = getUser()
  const canCreate = can(user, 'categories', 'create')
  const canUpdate = can(user, 'categories', 'update')
  const canDelete = can(user, 'categories', 'delete')

  const remove = async (id) => {
    if (!window.confirm('Supprimer cette catégorie ?')) return
    try {
      await api.delete(`/admin/produits/categories/${id}`)
      setVersion((v) => v + 1)
    } catch (err) {
      window.alert(err.message)
    }
  }

  return (
    <>
      <PageTitle title="Catégories" crumbs={[{ label: 'Produits', to: '/admin/produits' }, { label: 'Catégories' }]} />
      <section className="section">
        <div className="card">
          <div className="card-body">
            <div className="d-flex align-items-center justify-content-between mb-3">
              <h5 className="card-title mb-0">Catégories ({items.length})</h5>
              {canCreate && (
                <button type="button" className="btn btn-primary" onClick={() => setModal({ mode: 'new' })}>
                  <i className="bi bi-plus-lg me-1"></i> Nouvelle catégorie
                </button>
              )}
            </div>

            {categories.loading ? <Spinner /> : items.length === 0 ? (
              <p className="text-muted mb-0">Aucune catégorie.</p>
            ) : (
              <table className="table table-hover align-middle mb-0">
                <tbody>
                  {items.map((c) => (
                    <tr key={c.id}>
                      <td style={{ paddingLeft: `${c.depth * 24 + 12}px` }}>
                        <span className="fw-semibold">{c.nom}</span>
                        {c.actif === false && <span className="badge bg-secondary ms-2">Inactive</span>}
                        {c.code && <span className="text-muted small ms-2">[{c.code}]</span>}
                      </td>
                      <td className="text-end text-muted small">{c.produits} produit{c.produits > 1 ? 's' : ''}</td>
                      <td className="text-end" style={{ width: 140 }}>
                        {canUpdate && (
                          <button type="button" className="btn btn-sm btn-outline-primary me-1" onClick={() => setModal({ mode: 'edit', cat: c })}>
                            <i className="bi bi-pencil"></i>
                          </button>
                        )}
                        {canDelete && (
                          <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(c.id)}>
                            <i className="bi bi-trash"></i>
                          </button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        </div>
      </section>

      {modal?.mode === 'new' && (
        <CategorieForm open all={items} onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />
      )}
      {modal?.mode === 'edit' && (
        <CategorieForm open initial={modal.cat} all={items} onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />
      )}
    </>
  )
}
