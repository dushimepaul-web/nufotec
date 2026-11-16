// Médiathèque — GET /admin/medias + ajout de lien + téléversement par morceaux.
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api, { API_URL, tokens } from '../../../lib/api'
import { getUser } from '../../../lib/auth'
import { can } from '../../../lib/permissions'
import { formatDate } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

const CATEGORIES = ['audio', 'video', 'image', 'fichier', 'link']
const TRAITEMENTS = ['aucun', 'en_attente', 'en_cours', 'pret', 'erreur']

function LinkForm({ onClose, onSaved }) {
  const [form, setForm] = useState({ titre: '', url: '', description: '', credits: '', date: '', publie: true, site_web: true, whatsapp: false })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const submit = async (e) => {
    e.preventDefault()
    if (!form.titre.trim() || form.url.trim().length < 10) return
    setSending(true)
    setError('')
    try {
      await api.post('/admin/medias/liens', {
        titre: form.titre.trim(), url: form.url.trim(), description: form.description || null,
        credits: form.credits || null, date: form.date || null, publie: form.publie,
        site_web: form.site_web, whatsapp: form.whatsapp,
      })
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open onClose={onClose} icon="bi-link-45deg" title="Ajouter un lien (YouTube…)">
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          <div className="col-12">
            <label className="form-label">Titre *</label>
            <input className="form-control" value={form.titre} onChange={(e) => set('titre', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">URL *</label>
            <input className="form-control" value={form.url} placeholder="https://youtube.com/…" onChange={(e) => set('url', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Description</label>
            <textarea className="form-control" rows="2" value={form.description} onChange={(e) => set('description', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Crédits</label>
            <input className="form-control" value={form.credits} onChange={(e) => set('credits', e.target.value)} />
          </div>
          <div className="col-md-6">
            <label className="form-label">Date</label>
            <input type="date" className="form-control" value={form.date} onChange={(e) => set('date', e.target.value)} />
          </div>
          <div className="col-12 d-flex gap-4">
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="lPublie" checked={form.publie} onChange={(e) => set('publie', e.target.checked)} />
              <label className="form-check-label" htmlFor="lPublie">Publié</label>
            </div>
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="lSite" checked={form.site_web} onChange={(e) => set('site_web', e.target.checked)} />
              <label className="form-check-label" htmlFor="lSite">Sur le site</label>
            </div>
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="lWa" checked={form.whatsapp} onChange={(e) => set('whatsapp', e.target.checked)} />
              <label className="form-check-label" htmlFor="lWa">Sur WhatsApp</label>
            </div>
          </div>
          <div className="col-12 d-flex justify-content-end gap-2">
            <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
            <button type="submit" className="btn btn-primary" disabled={sending}>Ajouter</button>
          </div>
        </div>
      </form>
    </Modal>
  )
}

function UploadForm({ onClose, onSaved }) {
  const [file, setFile] = useState(null)
  const [meta, setMeta] = useState({ titre: '', description: '', credits: '', date: '', publie: true, site_web: true, whatsapp: false })
  const [progress, setProgress] = useState(0)
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  const set = (k, v) => setMeta((f) => ({ ...f, [k]: v }))

  const start = async () => {
    if (!file) return
    setSending(true)
    setError('')
    try {
      const token = tokens.get()?.access_token
      const init = await api.post('/admin/medias/envois', { nom_fichier: file.name, taille: file.size })
      const status = await api.get(`/admin/medias/envois/${init.id}`)
      const deja = new Set(status.morceaux_recus ?? [])
      const total = status.morceaux_total
      const size = status.taille_morceau
      for (let i = 0; i < total; i++) {
        if (deja.has(i)) {
          setProgress(Math.round(((i + 1) / total) * 100))
          continue
        }
        const blob = file.slice(i * size, Math.min(file.size, (i + 1) * size))
        const res = await fetch(`${API_URL}/admin/medias/envois/${init.id}/morceaux/${i}`, {
          method: 'PUT',
          headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/octet-stream' },
          body: blob,
        })
        if (!res.ok) throw new Error(`Échec du morceau ${i + 1}/${total}`)
        setProgress(Math.round(((i + 1) / total) * 100))
      }
      await api.post(`/admin/medias/envois/${init.id}/terminer`, {
        titre: meta.titre.trim(), description: meta.description || null, credits: meta.credits || null,
        date: meta.date || null, publie: meta.publie, site_web: meta.site_web, whatsapp: meta.whatsapp,
      })
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open onClose={onClose} icon="bi-cloud-arrow-up" title="Téléverser un fichier" size="lg">
      {error && <div className="alert alert-danger py-2 small">{error}</div>}
      <div className="row g-3">
        <div className="col-12">
          <label className="form-label">Fichier *</label>
          <input type="file" className="form-control" onChange={(e) => setFile(e.target.files[0])} />
          <small className="text-muted">Le type (audio, vidéo, image…) est détecté automatiquement.</small>
        </div>
        <div className="col-12">
          <label className="form-label">Titre *</label>
          <input className="form-control" value={meta.titre} onChange={(e) => set('titre', e.target.value)} />
        </div>
        <div className="col-12">
          <label className="form-label">Description</label>
          <textarea className="form-control" rows="2" value={meta.description} onChange={(e) => set('description', e.target.value)} />
        </div>
        <div className="col-md-6">
          <label className="form-label">Crédits</label>
          <input className="form-control" value={meta.credits} onChange={(e) => set('credits', e.target.value)} />
        </div>
        <div className="col-md-6">
          <label className="form-label">Date</label>
          <input type="date" className="form-control" value={meta.date} onChange={(e) => set('date', e.target.value)} />
        </div>
        <div className="col-12 d-flex gap-4">
          <div className="form-check">
            <input className="form-check-input" type="checkbox" id="uPublie" checked={meta.publie} onChange={(e) => set('publie', e.target.checked)} />
            <label className="form-check-label" htmlFor="uPublie">Publié</label>
          </div>
          <div className="form-check">
            <input className="form-check-input" type="checkbox" id="uSite" checked={meta.site_web} onChange={(e) => set('site_web', e.target.checked)} />
            <label className="form-check-label" htmlFor="uSite">Sur le site</label>
          </div>
          <div className="form-check">
            <input className="form-check-input" type="checkbox" id="uWa" checked={meta.whatsapp} onChange={(e) => set('whatsapp', e.target.checked)} />
            <label className="form-check-label" htmlFor="uWa">Sur WhatsApp</label>
          </div>
        </div>
        {sending && (
          <div className="col-12">
            <div className="progress" style={{ height: 8 }}>
              <div className="progress-bar" style={{ width: `${progress}%` }}>{progress}%</div>
            </div>
          </div>
        )}
        <div className="col-12 d-flex justify-content-end gap-2">
          <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
          <button type="button" className="btn btn-primary" disabled={!file || !meta.titre.trim() || sending} onClick={start}>
            {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null} Téléverser
          </button>
        </div>
      </div>
    </Modal>
  )
}

export default function MediasList() {
  const [version, setVersion] = useState(0)
  const [page, setPage] = useState(1)
  const [categorie, setCategorie] = useState('')
  const [traitement, setTraitement] = useState('')
  const [search, setSearch] = useState('')
  const [q, setQ] = useState('')
  const [modal, setModal] = useState(null) // 'link' | 'upload'

  useEffect(() => {
    const timer = setTimeout(() => setQ(search.trim()), 350)
    return () => clearTimeout(timer)
  }, [search])

  const params = { page, per_page: 15, ...(categorie ? { categorie } : {}), ...(traitement ? { traitement } : {}), ...(q ? { q } : {}) }
  const medias = useApi('/admin/medias', params, [version])
  const items = medias.data?.items ?? []
  const user = getUser()
  const canCreate = can(user, 'medias', 'create')
  const canUpdate = can(user, 'medias', 'update')
  const canDelete = can(user, 'medias', 'delete')

  const remove = async (uuid) => {
    if (!window.confirm('Supprimer ce média ? Le fichier est définitivement effacé.')) return
    try { await api.delete(`/admin/medias/${uuid}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }

  const columns = [
    {
      key: 'titre', label: 'Titre',
      render: (m) => (
        <div className="d-flex align-items-center gap-2">
          {m.miniature && <img src={m.miniature} alt="" width="40" height="40" className="rounded object-fit-cover" />}
          <div>
            <Link to={`${ROUTES.medias}/${m.uuid}`} className="fw-semibold">{m.titre}</Link>
            <div className="text-muted small text-capitalize">{m.categorie}</div>
          </div>
        </div>
      ),
    },
    { key: 'date', label: 'Date', render: (m) => formatDate(m.date || m.created_at) },
    { key: 'publie', label: 'Publication', render: (m) => <StatusBadge value={m.publie ? 'publie' : 'brouillon'} /> },
    { key: 'traitement', label: 'Traitement', render: (m) => <StatusBadge value={m.traitement || 'aucun'} /> },
    {
      key: 'action', label: '', className: 'text-end',
      render: (m) => (
        <>
          {canUpdate && <Link to={`${ROUTES.medias}/${m.uuid}`} className="btn btn-sm btn-outline-primary me-1"><i className="bi bi-pencil"></i></Link>}
          {canDelete && (
            <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(m.uuid)}><i className="bi bi-trash"></i></button>
          )}
        </>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Médias" crumbs={[{ label: 'Médias' }]} />
      <section className="section">
        <DataTable
          title={`Médias (${medias.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={medias.data?.meta}
          onPage={setPage}
          loading={medias.loading}
          error={medias.error}
          emptyText={{ title: 'Aucun média' }}
          toolbar={
            <>
              <div className="search-bar">
                <form className="search-form d-flex align-items-center" onSubmit={(e) => e.preventDefault()}>
                  <input type="text" placeholder="Rechercher…" title="Rechercher" value={search}
                         onChange={(e) => { setSearch(e.target.value); setPage(1) }} />
                  <button type="submit" title="Rechercher"><i className="bi bi-search"></i></button>
                </form>
              </div>
              <select className="form-select form-select-sm w-auto" value={categorie} onChange={(e) => { setCategorie(e.target.value); setPage(1) }}>
                <option value="">Toutes les catégories</option>
                {CATEGORIES.map((c) => <option key={c} value={c}>{c}</option>)}
              </select>
              <select className="form-select form-select-sm w-auto" value={traitement} onChange={(e) => { setTraitement(e.target.value); setPage(1) }}>
                <option value="">Tous les traitements</option>
                {TRAITEMENTS.map((t) => <option key={t} value={t}>{t.replace(/_/g, ' ')}</option>)}
              </select>
              <button type="button" className="btn btn-outline-primary btn-sm" onClick={() => setModal('link')} disabled={!canCreate}>
                <i className="bi bi-link-45deg me-1"></i> Lien
              </button>
              <button type="button" className="btn btn-primary btn-sm" onClick={() => setModal('upload')} disabled={!canCreate}>
                <i className="bi bi-cloud-arrow-up me-1"></i> Téléverser
              </button>
            </>
          }
        />
      </section>

      {modal === 'link' && <LinkForm onClose={() => setModal(null)} onSaved={() => { setVersion((v) => v + 1) }} />}
      {modal === 'upload' && <UploadForm onClose={() => setModal(null)} onSaved={() => { setVersion((v) => v + 1) }} />}
    </>
  )
}
