// Collections de médias — liste depuis GET /medias/collections, CRUD via /admin/medias/collections.
import { useState } from 'react'
import api from '../../../lib/api'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

function CollectionForm({ open, initial, onClose, onSaved }) {
  const isEdit = Boolean(initial)
  const [form, setForm] = useState(() => ({
    titre: initial?.titre ?? '', description: initial?.description ?? '',
    medias: (initial?.medias ?? []).map((m) => m.uuid).join(', '),
    position: initial?.position ?? 0, actif: initial?.actif ?? true,
  }))
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setError('')
    try {
      const body = {
        titre: form.titre.trim(),
        description: form.description || null,
        medias: form.medias.split(',').map((s) => s.trim()).filter(Boolean),
        position: Number(form.position) || 0,
        actif: form.actif,
      }
      if (isEdit) await api.put(`/admin/medias/collections/${initial.uuid}`, body)
      else await api.post('/admin/medias/collections', body)
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={open} onClose={onClose} icon="bi-collection" title={isEdit ? 'Modifier la collection' : 'Nouvelle collection'} size="lg">
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          <div className="col-md-8">
            <label className="form-label">Titre *</label>
            <input className="form-control" value={form.titre} onChange={(e) => set('titre', e.target.value)} />
          </div>
          <div className="col-md-4">
            <label className="form-label">Position</label>
            <input type="number" className="form-control" value={form.position} onChange={(e) => set('position', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Description</label>
            <textarea className="form-control" rows="2" value={form.description} onChange={(e) => set('description', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Médias (UUID séparés par des virgules, dans l'ordre)</label>
            <textarea className="form-control" rows="2" value={form.medias}
                      onChange={(e) => set('medias', e.target.value)} placeholder="uuid1, uuid2, uuid3" />
          </div>
          <div className="col-12">
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="colActif" checked={form.actif} onChange={(e) => set('actif', e.target.checked)} />
              <label className="form-check-label" htmlFor="colActif">Active</label>
            </div>
          </div>
          <div className="col-12 d-flex justify-content-end gap-2">
            <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
            <button type="submit" className="btn btn-primary" disabled={sending}>Enregistrer</button>
          </div>
        </div>
      </form>
    </Modal>
  )
}

export default function Collections() {
  const [version, setVersion] = useState(0)
  const [modal, setModal] = useState(null)
  const [loadingDetail, setLoadingDetail] = useState(false)
  const collections = useApi('/medias/collections', undefined, [version])
  const items = collections.data ?? []

  const edit = async (c) => {
    setLoadingDetail(true)
    try {
      const detail = await api.get(`/medias/collections/${c.slug}`)
      setModal({ mode: 'edit', item: { ...c, medias: detail.medias ?? [] } })
    } catch (err) {
      window.alert(err.message)
    } finally {
      setLoadingDetail(false)
    }
  }

  const remove = async (c) => {
    if (!window.confirm('Supprimer cette collection ? Les médias sont conservés.')) return
    try { await api.delete(`/admin/medias/collections/${c.uuid}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }

  const columns = [
    {
      key: 'titre', label: 'Collection',
      render: (c) => (
        <div className="d-flex align-items-center gap-2">
          {c.couverture && <img src={c.couverture} alt="" width="40" height="40" className="rounded object-fit-cover" />}
          <span className="fw-semibold">{c.titre}</span>
        </div>
      ),
    },
    { key: 'episodes', label: 'Épisodes' },
    { key: 'actif', label: 'État', render: (c) => <StatusBadge value={c.actif ? 'actif' : 'inactif'} /> },
    {
      key: 'action', label: '', className: 'text-end',
      render: (c) => (
        <>
          <button type="button" className="btn btn-sm btn-outline-primary me-1" disabled={loadingDetail} onClick={() => edit(c)}><i className="bi bi-pencil"></i></button>
          <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(c)}><i className="bi bi-trash"></i></button>
        </>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Collections" crumbs={[{ label: 'Médias', to: '/admin/medias' }, { label: 'Collections' }]} />
      <section className="section">
        <DataTable
          title={`Collections (${items.length})`}
          columns={columns}
          items={items}
          loading={collections.loading}
          error={collections.error}
          emptyText={{ title: 'Aucune collection' }}
          toolbar={
            <button type="button" className="btn btn-primary" onClick={() => setModal({ mode: 'new' })}>
              <i className="bi bi-plus-lg me-1"></i> Nouvelle collection
            </button>
          }
        />
      </section>

      {modal?.mode === 'new' && <CollectionForm open onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
      {modal?.mode === 'edit' && <CollectionForm open initial={modal.item} onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
    </>
  )
}
