// FAQ — CRUD simple via /admin/contenu/faq.
import { useState } from 'react'
import api from '../../../lib/api'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

const empty = { question: '', reponse: '', categorie: '', ordre: 0, publiee: true }

function FaqForm({ open, initial, onClose, onSaved }) {
  const isEdit = Boolean(initial)
  const [form, setForm] = useState(initial ?? empty)
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const submit = async (e) => {
    e.preventDefault()
    if (!form.question.trim() || !form.reponse.trim()) return
    setSending(true)
    setError('')
    try {
      const body = {
        question: form.question.trim(), reponse: form.reponse.trim(),
        categorie: form.categorie || null, ordre: Number(form.ordre) || 0, publiee: form.publiee,
      }
      if (isEdit) await api.put(`/admin/contenu/faq/${initial.id}`, body)
      else await api.post('/admin/contenu/faq', body)
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={open} onClose={onClose} icon="bi-question-circle" title={isEdit ? 'Modifier la question' : 'Nouvelle question'} size="lg">
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          <div className="col-12">
            <label className="form-label">Question *</label>
            <input className="form-control" value={form.question} onChange={(e) => set('question', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Réponse *</label>
            <textarea className="form-control" rows="4" value={form.reponse} onChange={(e) => set('reponse', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Catégorie</label>
            <input className="form-control" value={form.categorie} onChange={(e) => set('categorie', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Ordre</label>
            <input type="number" className="form-control" value={form.ordre} onChange={(e) => set('ordre', e.target.value)} />
          </div>
          <div className="col-12">
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="faqPubliee" checked={form.publiee}
                     onChange={(e) => set('publiee', e.target.checked)} />
              <label className="form-check-label" htmlFor="faqPubliee">Publiée</label>
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

export default function Faq() {
  const [version, setVersion] = useState(0)
  const [modal, setModal] = useState(null)
  const faq = useApi('/admin/contenu/faq', undefined, [version])
  const items = faq.data ?? []

  const remove = async (id) => {
    if (!window.confirm('Supprimer cette question ?')) return
    try { await api.delete(`/admin/contenu/faq/${id}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }

  const columns = [
    { key: 'question', label: 'Question' },
    { key: 'categorie', label: 'Catégorie', render: (f) => f.categorie || '—' },
    { key: 'ordre', label: 'Ordre' },
    { key: 'publiee', label: 'État', render: (f) => <StatusBadge value={f.publiee ? 'publie' : 'brouillon'} /> },
    {
      key: 'action', label: '', className: 'text-end',
      render: (f) => (
        <>
          <button type="button" className="btn btn-sm btn-outline-primary me-1" onClick={() => setModal({ mode: 'edit', item: f })}>
            <i className="bi bi-pencil"></i>
          </button>
          <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(f.id)}>
            <i className="bi bi-trash"></i>
          </button>
        </>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="FAQ" crumbs={[{ label: 'Contenu', to: ROUTES.contenu }, { label: 'FAQ' }]} />
      <section className="section">
        <DataTable
          title={`Questions (${items.length})`}
          columns={columns}
          items={items}
          loading={faq.loading}
          error={faq.error}
          emptyText={{ title: 'Aucune question' }}
          toolbar={
            <button type="button" className="btn btn-primary" onClick={() => setModal({ mode: 'new' })}>
              <i className="bi bi-plus-lg me-1"></i> Nouvelle question
            </button>
          }
        />
      </section>

      {modal?.mode === 'new' && <FaqForm open onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
      {modal?.mode === 'edit' && <FaqForm open initial={modal.item} onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
    </>
  )
}
