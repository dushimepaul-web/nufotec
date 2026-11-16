// Fiche média — GET /admin/medias/{uuid} + modification, relance du traitement, suppression.
import { useState } from 'react'
import { useParams } from 'react-router-dom'
import api from '../../../lib/api'
import { formatDate } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import EmptyState from '../../../ui/EmptyState'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'
import StatusBadge from '../../../ui/StatusBadge'

const duration = (s) => {
  if (!s) return '—'
  const h = Math.floor(s / 3600)
  const m = Math.floor((s % 3600) / 60)
  return h ? `${h} h ${m} min` : `${m} min`
}

function MediaForm({ initial, uuid }) {
  const [form, setForm] = useState({
    titre: initial.titre ?? '', description: initial.description ?? '', credits: initial.credits ?? '',
    date: (initial.date || '').slice(0, 10), publie: initial.publie ?? true, site_web: initial.site_web ?? true, whatsapp: initial.whatsapp ?? false,
  })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState(null)
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const submit = async (e) => {
    e.preventDefault()
    if (!form.titre.trim()) return
    setSending(true)
    setError(null)
    try {
      await api.patch(`/admin/medias/${uuid}`, {
        titre: form.titre.trim(), description: form.description || null, credits: form.credits || null,
        date: form.date || null, publie: form.publie, site_web: form.site_web, whatsapp: form.whatsapp,
      })
      window.location.reload()
    } catch (err) {
      setError(err)
    } finally {
      setSending(false)
    }
  }

  return (
    <div className="card">
      <div className="card-body">
        <h5 className="card-title">Modifier</h5>
        <form onSubmit={submit} noValidate>
          {error && <div className="alert alert-danger py-2 small">{error.message}</div>}
          <div className="row g-3">
            <div className="col-12">
              <label className="form-label">Titre</label>
              <input className="form-control" value={form.titre} onChange={(e) => set('titre', e.target.value)} />
            </div>
            <div className="col-12">
              <label className="form-label">Description</label>
              <textarea className="form-control" rows="3" value={form.description} onChange={(e) => set('description', e.target.value)} />
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
                <input className="form-check-input" type="checkbox" id="mPublie" checked={form.publie} onChange={(e) => set('publie', e.target.checked)} />
                <label className="form-check-label" htmlFor="mPublie">Publié</label>
              </div>
              <div className="form-check">
                <input className="form-check-input" type="checkbox" id="mSite" checked={form.site_web} onChange={(e) => set('site_web', e.target.checked)} />
                <label className="form-check-label" htmlFor="mSite">Sur le site</label>
              </div>
              <div className="form-check">
                <input className="form-check-input" type="checkbox" id="mWa" checked={form.whatsapp} onChange={(e) => set('whatsapp', e.target.checked)} />
                <label className="form-check-label" htmlFor="mWa">Sur WhatsApp</label>
              </div>
            </div>
            <div className="col-12">
              <button type="submit" className="btn btn-primary" disabled={sending}>
                {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : <i className="bi bi-check2 me-1"></i>}
                Enregistrer
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  )
}

export default function MediaDetail() {
  const { uuid } = useParams()
  const [version, setVersion] = useState(0)
  const [busy, setBusy] = useState(false)
  const detail = useApi(`/admin/medias/${uuid}`, undefined, [uuid, version])
  const m = detail.data

  const act = async (fn) => {
    setBusy(true)
    try { await fn(); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
    finally { setBusy(false) }
  }

  if (detail.loading) return <><PageTitle title="Média" /><Spinner /></>
  if (detail.error || !m) {
    return (
      <>
        <PageTitle title="Média" />
        <EmptyState icon="bi-collection-x" title="Média introuvable" text={detail.error?.message} />
      </>
    )
  }

  return (
    <>
      <PageTitle title={m.titre} crumbs={[{ label: 'Médias', to: ROUTES.medias }, { label: m.titre }]} />

      <section className="section">
        <div className="row g-3">
          {/* En-tête */}
          <div className="col-12">
            <div className="card">
              <div className="card-body d-flex flex-wrap align-items-center gap-3">
                {m.miniature && <img src={m.miniature} alt="" width="64" height="64" className="rounded object-fit-cover" />}
                <div className="me-auto">
                  <h4 className="mb-1">{m.titre}</h4>
                  <div className="text-muted small text-capitalize">{m.categorie} · {formatDate(m.date || m.created_at)}</div>
                  <div className="d-flex flex-wrap gap-2 mt-2">
                    <StatusBadge value={m.publie ? 'publie' : 'brouillon'} />
                    <StatusBadge value={m.traitement || 'aucun'} />
                  </div>
                </div>
                <div className="d-flex flex-wrap gap-2">
                  {['audio', 'video'].includes(m.categorie) && m.fichier && (
                    <button type="button" className="btn btn-sm btn-outline-primary" disabled={busy}
                            onClick={() => act(() => api.post(`/admin/medias/${uuid}/retraiter`))}>
                      <i className="bi bi-arrow-repeat me-1"></i> Retraiter
                    </button>
                  )}
                  <button type="button" className="btn btn-sm btn-outline-danger" disabled={busy}
                          onClick={() => act(() => api.delete(`/admin/medias/${uuid}`).then(() => {}))}>
                    <i className="bi bi-trash me-1"></i> Supprimer
                  </button>
                </div>
              </div>
            </div>
          </div>

          {/* Stats */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Statistiques</h5>
                <dl className="row mb-0 small">
                  <dt className="col-5 text-muted">Vues</dt><dd className="col-7">{m.stats?.vues ?? '—'}</dd>
                  <dt className="col-5 text-muted">Écoutes</dt><dd className="col-7">{m.ecoutes ?? 0}</dd>
                  <dt className="col-5 text-muted">Temps écouté</dt><dd className="col-7">{duration(m.temps_ecoute_secondes)}</dd>
                  <dt className="col-5 text-muted">Téléchargements</dt><dd className="col-7">{m.telechargements ?? 0}</dd>
                  <dt className="col-5 text-muted">Durée</dt><dd className="col-7">{duration(m.duree)}</dd>
                </dl>
                {m.lien && <a href={m.lien} target="_blank" rel="noopener noreferrer" className="btn btn-sm btn-outline-primary mt-3">Ouvrir le lien</a>}
              </div>
            </div>
          </div>

          <div className="col-lg-6">
            <MediaForm initial={m} uuid={uuid} />
          </div>
        </div>
      </section>
    </>
  )
}
