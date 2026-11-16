// Data room — GET/POST /admin/data-room + modification de visibilité et journal des téléchargements.
import { useState } from 'react'
import api from '../../../lib/api'
import { formatDateTime } from '../../../lib/format'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

const fmtSize = (b) => (b >= 1048576 ? `${(b / 1048576).toFixed(1)} Mo` : `${Math.round(b / 1024)} Ko`)

function UploadForm({ onClose, onSaved }) {
  const [form, setForm] = useState({ titre: '', description: '', fichier: null })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')

  const submit = async (e) => {
    e.preventDefault()
    if (!form.titre.trim() || !form.fichier) return
    setSending(true)
    setError('')
    try {
      const fd = new FormData()
      fd.append('titre', form.titre.trim())
      fd.append('description', form.description || '')
      fd.append('fichier', form.fichier)
      await api.post('/admin/data-room', fd)
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open onClose={onClose} icon="bi-folder-plus" title="Ajouter un document">
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          <div className="col-12">
            <label className="form-label">Titre *</label>
            <input className="form-control" value={form.titre} onChange={(e) => setForm({ ...form, titre: e.target.value })} />
          </div>
          <div className="col-12">
            <label className="form-label">Description</label>
            <input className="form-control" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
          </div>
          <div className="col-12">
            <label className="form-label">Fichier * (PDF, DOCX, XLSX, PPTX — 50 Mo max)</label>
            <input type="file" className="form-control" onChange={(e) => setForm({ ...form, fichier: e.target.files[0] })} />
          </div>
          <div className="col-12 d-flex justify-content-end gap-2">
            <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
            <button type="submit" className="btn btn-primary" disabled={sending || !form.titre.trim() || !form.fichier}>Ajouter</button>
          </div>
        </div>
      </form>
    </Modal>
  )
}

function EditForm({ doc, onClose, onSaved }) {
  const [form, setForm] = useState({ titre: doc.titre, description: doc.description ?? '', visibilite: doc.visibilite, actif: doc.actif })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')

  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setError('')
    try {
      await api.patch(`/admin/data-room/${doc.uuid}`, {
        titre: form.titre.trim(), description: form.description || null,
        visibilite: form.visibilite, actif: form.actif,
      })
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open onClose={onClose} icon="bi-folder" title="Modifier le document">
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          <div className="col-12">
            <label className="form-label">Titre</label>
            <input className="form-control" value={form.titre} onChange={(e) => setForm({ ...form, titre: e.target.value })} />
          </div>
          <div className="col-12">
            <label className="form-label">Description</label>
            <input className="form-control" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Visibilité</label>
            <select className="form-select" value={form.visibilite} onChange={(e) => setForm({ ...form, visibilite: e.target.value })}>
              <option value="tous_qualifies">Tous les qualifiés</option>
              <option value="selection">Sélection</option>
            </select>
          </div>
          <div className="col-md-6 d-flex align-items-end">
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="drActif" checked={form.actif} onChange={(e) => setForm({ ...form, actif: e.target.checked })} />
              <label className="form-check-label" htmlFor="drActif">Actif</label>
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

function DownloadsModal({ doc, onClose }) {
  const downloads = useApi(doc ? `/admin/data-room/${doc.uuid}/telechargements` : null)
  const items = downloads.data ?? []

  return (
    <Modal open={Boolean(doc)} onClose={onClose} icon="bi-download" title={`Téléchargements — ${doc?.titre}`} size="lg">
      {items.length ? (
        <table className="table table-sm mb-0">
          <thead><tr><th>Investisseur</th><th>IP</th><th>Le</th></tr></thead>
          <tbody>
            {items.map((d, i) => (
              <tr key={i}>
                <td>{d.investisseur || '—'}{d.organisation ? <span className="text-muted"> ({d.organisation})</span> : null}</td>
                <td>{d.ip || '—'}</td>
                <td className="text-nowrap small">{formatDateTime(d.le)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      ) : <p className="text-muted mb-0">Aucun téléchargement.</p>}
    </Modal>
  )
}

export default function DataRoom() {
  const [version, setVersion] = useState(0)
  const [modal, setModal] = useState(null) // {mode:'upload'} | {mode:'edit', doc} | {mode:'downloads', doc}
  const room = useApi('/admin/data-room', undefined, [version])
  const items = room.data ?? []

  const columns = [
    { key: 'titre', label: 'Titre', render: (d) => <span className="fw-semibold">{d.titre}</span> },
    { key: 'nom_fichier', label: 'Fichier', render: (d) => <span className="text-muted">{d.nom_fichier}</span> },
    { key: 'taille', label: 'Taille', render: (d) => fmtSize(d.taille) },
    { key: 'visibilite', label: 'Visibilité', render: (d) => <span className="text-capitalize">{d.visibilite === 'tous_qualifies' ? 'Tous les qualifiés' : 'Sélection'}</span> },
    { key: 'actif', label: 'État', render: (d) => <StatusBadge value={d.actif ? 'actif' : 'inactif'} /> },
    { key: 'telechargements', label: 'Tél.' },
    {
      key: 'action', label: '', className: 'text-end',
      render: (d) => (
        <>
          <button type="button" className="btn btn-sm btn-outline-secondary me-1" onClick={() => setModal({ mode: 'downloads', doc: d })}><i className="bi bi-download"></i></button>
          <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => setModal({ mode: 'edit', doc: d })}><i className="bi bi-pencil"></i></button>
        </>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Data room" crumbs={[{ label: 'Investisseurs', to: '/admin/investisseurs' }, { label: 'Data room' }]} />
      <section className="section">
        <DataTable
          title={`Documents (${items.length})`}
          columns={columns}
          items={items}
          loading={room.loading}
          error={room.error}
          emptyText={{ title: 'Aucun document' }}
          toolbar={
            <button type="button" className="btn btn-primary" onClick={() => setModal({ mode: 'upload' })}>
              <i className="bi bi-plus-lg me-1"></i> Ajouter un document
            </button>
          }
        />
      </section>

      {modal?.mode === 'upload' && <UploadForm onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
      {modal?.mode === 'edit' && <EditForm doc={modal.doc} onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
      {modal?.mode === 'downloads' && <DownloadsModal doc={modal.doc} onClose={() => setModal(null)} />}
    </>
  )
}
