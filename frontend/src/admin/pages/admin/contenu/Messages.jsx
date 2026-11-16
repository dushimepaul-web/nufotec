// Boîte de réception des messages de contact — GET /admin/contenu/messages + réponse + suppression.
import { useState } from 'react'
import api from '../../../lib/api'
import { formatDateTime } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'

export default function Messages() {
  const [version, setVersion] = useState(0)
  const [page, setPage] = useState(1)
  const [nonLus, setNonLus] = useState(false)
  const [selected, setSelected] = useState(null)
  const [reponse, setReponse] = useState('')
  const [sending, setSending] = useState(false)

  const messages = useApi('/admin/contenu/messages', { page, per_page: 15, ...(nonLus ? { non_lus: true } : {}) }, [version])
  const items = messages.data?.items ?? []

  // Ouvrir un message : le marque comme lu puis met à jour l'affichage.
  const open = async (m) => {
    setSelected(m)
    setReponse('')
    try {
      const updated = await api.get(`/admin/contenu/messages/${m.id}`)
      setSelected(updated)
      setVersion((v) => v + 1)
    } catch { /* affichage avec les données de la liste */ }
  }

  const reply = async () => {
    if (!reponse.trim()) return
    setSending(true)
    try {
      const updated = await api.post(`/admin/contenu/messages/${selected.id}/repondre`, { texte: reponse.trim() })
      setSelected(updated)
      setReponse('')
      setVersion((v) => v + 1)
    } finally {
      setSending(false)
    }
  }

  const remove = async () => {
    if (!window.confirm('Supprimer ce message ?')) return
    try {
      await api.delete(`/admin/contenu/messages/${selected.id}`)
      setSelected(null)
      setVersion((v) => v + 1)
    } catch (err) {
      window.alert(err.message)
    }
  }

  const columns = [
    {
      key: 'nom', label: 'Expéditeur',
      render: (m) => (
        <span className={m.lu ? '' : 'fw-semibold'}>
          {!m.lu && <i className="bi bi-circle-fill text-primary me-1" style={{ fontSize: 8 }}></i>}
          {m.nom || '—'}
        </span>
      ),
    },
    { key: 'email', label: 'Email', render: (m) => m.email || '—' },
    { key: 'sujet', label: 'Sujet', render: (m) => m.sujet || '—' },
    { key: 'recu_le', label: 'Reçu le', render: (m) => formatDateTime(m.recu_le) },
    {
      key: 'action', label: '', className: 'text-end',
      render: (m) => (
        <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => open(m)}>
          <i className="bi bi-envelope-open me-1"></i> {m.lu ? 'Voir' : 'Lire'}
        </button>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Messages" crumbs={[{ label: 'Contenu', to: ROUTES.contenu }, { label: 'Messages' }]} />
      <section className="section">
        <DataTable
          title={`Messages (${messages.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={messages.data?.meta}
          onPage={setPage}
          loading={messages.loading}
          error={messages.error}
          emptyText={{ title: 'Aucun message' }}
          toolbar={
            <div className="form-check form-switch mb-0">
              <input className="form-check-input" type="checkbox" id="nonLus" checked={nonLus}
                     onChange={(e) => { setNonLus(e.target.checked); setPage(1) }} />
              <label className="form-check-label" htmlFor="nonLus">Non lus uniquement</label>
            </div>
          }
        />
      </section>

      <Modal open={Boolean(selected)} onClose={() => setSelected(null)} icon="bi-envelope" title="Message" size="lg">
        {selected && (
          <div>
            <dl className="row small mb-3">
              <dt className="col-3 text-muted">De</dt><dd className="col-9">{selected.nom || '—'} {selected.email && <span className="text-muted">({selected.email})</span>}</dd>
              {selected.telephone && <><dt className="col-3 text-muted">Tél.</dt><dd className="col-9">{selected.telephone}</dd></>}
              <dt className="col-3 text-muted">Sujet</dt><dd className="col-9">{selected.sujet || '—'}</dd>
              <dt className="col-3 text-muted">Reçu le</dt><dd className="col-9">{formatDateTime(selected.recu_le)}</dd>
            </dl>
            <div className="p-3 bg-light rounded mb-3">{selected.message}</div>

            {selected.reponse ? (
              <div className="p-3 bg-success bg-opacity-10 rounded mb-3">
                <div className="text-muted small mb-1">Réponse envoyée le {formatDateTime(selected.repondu_le)}</div>
                {selected.reponse}
              </div>
            ) : (
              <div className="mb-3">
                <label className="form-label">Répondre par email</label>
                <textarea className="form-control" rows="3" value={reponse} onChange={(e) => setReponse(e.target.value)} />
              </div>
            )}

            <div className="d-flex justify-content-between">
              <button type="button" className="btn btn-outline-danger" onClick={remove}>
                <i className="bi bi-trash me-1"></i> Supprimer
              </button>
              {!selected.reponse && (
                <button type="button" className="btn btn-primary" disabled={!reponse.trim() || sending} onClick={reply}>
                  {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : <i className="bi bi-send me-1"></i>}
                  Envoyer la réponse
                </button>
              )}
            </div>
          </div>
        )}
      </Modal>
    </>
  )
}
