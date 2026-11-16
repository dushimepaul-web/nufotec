// CRUD générique pour les ressources simples du contenu (témoignages, partenaires, chiffres clés, réseaux sociaux).
// L'API expose le même schéma (liste + POST/PUT/DELETE + envoi d'image) pour ces ressources.
import { useState } from 'react'
import api from '../lib/api'
import { getUser } from '../lib/auth'
import { can } from '../lib/permissions'
import useApi from '../lib/useApi'
import DataTable from './DataTable'
import Modal from './Modal'
import PageTitle from './PageTitle'

function FormModal({ open, initial, fields, imageField, base, title, onClose, onSaved }) {
  const isEdit = Boolean(initial)

  const initForm = () => {
    const out = {}
    for (const f of fields) {
      if (initial) {
        if (f.type === 'checkbox') out[f.key] = Boolean(initial[f.key])
        else if (f.type === 'number') out[f.key] = initial[f.key] != null ? String(initial[f.key]) : ''
        else out[f.key] = initial[f.key] ?? ''
      } else {
        out[f.key] = f.type === 'checkbox' ? (f.default ?? false) : (f.default ?? '')
      }
    }
    return out
  }

  const [form, setForm] = useState(initForm)
  const [image, setImage] = useState(null)
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const build = () => {
    const body = {}
    for (const f of fields) {
      const v = form[f.key]
      if (f.type === 'number') body[f.key] = v === '' || v == null ? null : Number(v)
      else if (f.type === 'checkbox') body[f.key] = Boolean(v)
      else body[f.key] = v === '' || v == null ? null : v
    }
    return body
  }

  const submit = async (e) => {
    e.preventDefault()
    const required = fields.find((f) => f.required && (form[f.key] === '' || form[f.key] == null || form[f.key] === false))
    if (required) return
    setSending(true)
    setError('')
    try {
      const body = build()
      let id
      if (isEdit) { await api.put(`${base}/${initial.id}`, body); id = initial.id }
      else { const created = await api.post(base, body); id = created.id }
      if (imageField && image) {
        const fd = new FormData()
        fd.append('image', image)
        await api.post(`${base}/${id}/image`, fd)
      }
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={open} onClose={onClose} icon="bi-pencil" title={title} size="lg">
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          {fields.map((f) => {
            const wide = f.type === 'textarea' || f.type === 'checkbox'
            return (
              <div key={f.key} className={wide ? 'col-12' : 'col-md-6'}>
                <label className="form-label">{f.label}{f.required ? ' *' : ''}</label>
                {f.type === 'textarea' ? (
                  <textarea className="form-control" rows="3" value={form[f.key]} onChange={(e) => set(f.key, e.target.value)} />
                ) : f.type === 'checkbox' ? (
                  <div className="form-check mt-1">
                    <input className="form-check-input" type="checkbox" id={`f-${f.key}`} checked={Boolean(form[f.key])}
                           onChange={(e) => set(f.key, e.target.checked)} />
                    <label className="form-check-label" htmlFor={`f-${f.key}`}>{f.checkLabel || f.label}</label>
                  </div>
                ) : f.type === 'select' ? (
                  <select className="form-select" value={form[f.key]} onChange={(e) => set(f.key, e.target.value)}>
                    <option value="">—</option>
                    {f.options.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                  </select>
                ) : (
                  <input type={f.type || 'text'} className="form-control" value={form[f.key]}
                         onChange={(e) => set(f.key, e.target.value)} />
                )}
              </div>
            )
          })}

          {imageField && (
            <div className="col-12">
              <label className="form-label">{imageField.label || 'Image'}</label>
              {initial?.[imageField.previewKey] && (
                <img src={initial[imageField.previewKey]} alt="" width="48" height="48" className="rounded object-fit-cover d-block mb-2" />
              )}
              <input type="file" accept="image/*" className="form-control form-control-sm"
                     onChange={(e) => setImage(e.target.files[0])} />
            </div>
          )}

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

export default function SimpleCrud({ title, base, columns, fields, imageField, buttonLabel = 'Nouveau', crumbs, module }) {
  const [version, setVersion] = useState(0)
  const [modal, setModal] = useState(null)
  const list = useApi(base, undefined, [version])
  const items = list.data ?? []
  const user = getUser()
  const canCreate = !module || can(user, module, 'create')
  const canUpdate = !module || can(user, module, 'update')
  const canDelete = !module || can(user, module, 'delete')

  const remove = async (id) => {
    if (!window.confirm('Supprimer cet élément ?')) return
    try { await api.delete(`${base}/${id}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }

  const cols = [...columns, {
    key: 'action', label: '', className: 'text-end',
    render: (item) => (
      <>
        {canUpdate && (
          <button type="button" className="btn btn-sm btn-outline-primary me-1" onClick={() => setModal({ mode: 'edit', item })}>
            <i className="bi bi-pencil"></i>
          </button>
        )}
        {canDelete && (
          <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(item.id)}>
            <i className="bi bi-trash"></i>
          </button>
        )}
      </>
    ),
  }]

  return (
    <>
      <PageTitle title={title} crumbs={crumbs ?? [{ label: 'Contenu', to: '/admin/contenu/pages' }, { label: title }]} />
      <section className="section">
        <DataTable
          title={`${title} (${items.length})`}
          columns={cols}
          items={items}
          loading={list.loading}
          error={list.error}
          emptyText={{ title: 'Aucun élément' }}
          toolbar={canCreate ? (
            <button type="button" className="btn btn-primary" onClick={() => setModal({ mode: 'new' })}>
              <i className="bi bi-plus-lg me-1"></i> {buttonLabel}
            </button>
          ) : null}
        />
      </section>

      {modal?.mode === 'new' && (
        <FormModal open fields={fields} imageField={imageField} base={base} title={`Nouveau — ${title}`}
                   onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />
      )}
      {modal?.mode === 'edit' && (
        <FormModal open initial={modal.item} fields={fields} imageField={imageField} base={base} title={`Modifier — ${title}`}
                   onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />
      )}
    </>
  )
}
