// Phases d'investissement — liste depuis GET /investissement/phases, CRUD via /admin/investissement/phases.
import { useState } from 'react'
import api from '../../../lib/api'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'

const POSTES = [
  ['construction', 'Construction'], ['lab_equipment', 'Équipement de laboratoire'],
  ['processing_lines', 'Lignes de transformation'], ['cleanrooms', 'Salles blanches'],
  ['working_capital', 'Fonds de roulement'], ['rd', 'Recherche et développement'],
  ['training', 'Formation'], ['certification', 'Certification'], ['contingency', 'Imprévus'],
]

const money = (v, d) => (v != null ? `${Number(v).toLocaleString('fr-FR', { maximumFractionDigits: 2 })} ${d || ''}` : '—')

function PhaseForm({ open, initial, onClose, onSaved }) {
  const isEdit = Boolean(initial)
  const [form, setForm] = useState(() => {
    const repartition = {}
    if (initial?.repartition) for (const r of initial.repartition) repartition[r.poste] = r.pourcentage
    return {
      nom: initial?.nom ?? '', annee_debut: initial?.annee_debut ?? '', annee_fin: initial?.annee_fin ?? '',
      montant_total: initial?.montant_total ?? '', devise: initial?.devise || 'USD',
      description: initial?.description ?? '', repartition,
    }
  })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')

  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))
  const setPoste = (poste, v) => setForm((f) => ({ ...f, repartition: { ...f.repartition, [poste]: v === '' ? undefined : Number(v) } }))

  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setError('')
    try {
      const body = {
        nom: form.nom.trim(),
        annee_debut: form.annee_debut ? Number(form.annee_debut) : null,
        annee_fin: form.annee_fin ? Number(form.annee_fin) : null,
        montant_total: form.montant_total ? Number(form.montant_total) : null,
        devise: form.devise || 'USD',
        description: form.description || null,
        repartition: Object.fromEntries(Object.entries(form.repartition).filter(([, v]) => v != null)),
      }
      if (isEdit) await api.put(`/admin/investissement/phases/${initial.id}`, body)
      else await api.post('/admin/investissement/phases', body)
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={open} onClose={onClose} icon="bi-diagram-3" title={isEdit ? 'Modifier la phase' : 'Nouvelle phase'} size="lg">
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          <div className="col-md-8">
            <label className="form-label">Nom *</label>
            <input className="form-control" value={form.nom} onChange={(e) => set('nom', e.target.value)} />
          </div>
          <div className="col-md-4">
            <label className="form-label">Devise</label>
            <input className="form-control" value={form.devise} onChange={(e) => set('devise', e.target.value)} />
          </div>
          <div className="col-md-4">
            <label className="form-label">Année début</label>
            <input type="number" min="2000" max="2100" className="form-control" value={form.annee_debut} onChange={(e) => set('annee_debut', e.target.value)} />
          </div>
          <div className="col-md-4">
            <label className="form-label">Année fin</label>
            <input type="number" min="2000" max="2100" className="form-control" value={form.annee_fin} onChange={(e) => set('annee_fin', e.target.value)} />
          </div>
          <div className="col-md-4">
            <label className="form-label">Montant total</label>
            <input type="number" min="0" className="form-control" value={form.montant_total} onChange={(e) => set('montant_total', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Description</label>
            <textarea className="form-control" rows="2" value={form.description} onChange={(e) => set('description', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Répartition (% — le total doit faire 100)</label>
            <div className="row g-2">
              {POSTES.map(([key, label]) => (
                <div className="col-md-4" key={key}>
                  <label className="form-label small">{label}</label>
                  <input type="number" min="0" max="100" className="form-control form-control-sm"
                         value={form.repartition[key] ?? ''} onChange={(e) => setPoste(key, e.target.value)} />
                </div>
              ))}
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

export default function Phases() {
  const [version, setVersion] = useState(0)
  const [modal, setModal] = useState(null)
  const phases = useApi('/investissement/phases', undefined, [version])
  const items = phases.data ?? []

  const remove = async (id) => {
    if (!window.confirm('Supprimer cette phase ?')) return
    try { await api.delete(`/admin/investissement/phases/${id}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }

  const columns = [
    { key: 'nom', label: 'Phase', render: (p) => <span className="fw-semibold">{p.nom}</span> },
    { key: 'annees', label: 'Période', render: (p) => [p.annee_debut, p.annee_fin].filter(Boolean).join(' – ') || '—' },
    { key: 'montant_total', label: 'Montant', render: (p) => money(p.montant_total, p.devise) },
    { key: 'repartition', label: 'Répartition', render: (p) => (p.repartition ?? []).map((r) => `${r.libelle} ${r.pourcentage}%`).join(', ') || '—' },
    {
      key: 'action', label: '', className: 'text-end',
      render: (p) => (
        <>
          <button type="button" className="btn btn-sm btn-outline-primary me-1" onClick={() => setModal({ mode: 'edit', item: p })}><i className="bi bi-pencil"></i></button>
          <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(p.id)}><i className="bi bi-trash"></i></button>
        </>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Phases d'investissement" crumbs={[{ label: 'Investisseurs', to: '/admin/investisseurs' }, { label: 'Phases' }]} />
      <section className="section">
        <DataTable
          title={`Phases (${items.length})`}
          columns={columns}
          items={items}
          loading={phases.loading}
          error={phases.error}
          emptyText={{ title: 'Aucune phase' }}
          toolbar={
            <button type="button" className="btn btn-primary" onClick={() => setModal({ mode: 'new' })}>
              <i className="bi bi-plus-lg me-1"></i> Nouvelle phase
            </button>
          }
        />
      </section>

      {modal?.mode === 'new' && <PhaseForm open onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
      {modal?.mode === 'edit' && <PhaseForm open initial={modal.item} onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
    </>
  )
}
