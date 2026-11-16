// Détail d'une commande — GET /admin/commandes/{numero}.
// Actions : changement de statut (transitions), marquer contacté, ajouter une note.
import { useState } from 'react'
import { useParams } from 'react-router-dom'
import api from '../../../lib/api'
import { formatDateTime } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import EmptyState from '../../../ui/EmptyState'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'
import StatusBadge from '../../../ui/StatusBadge'

// Transitions autorisées par l'API (voir TRANSITIONS dans shop/service.py).
const TRANSITIONS = {
  en_attente: [['en_cours', 'Passer en cours', 'btn-primary'], ['annulee', 'Annuler', 'btn-outline-danger']],
  en_cours: [['livree', 'Marquer livrée', 'btn-success'], ['annulee', 'Annuler', 'btn-outline-danger']],
}

const money = (v, d) => (v != null ? `${Number(v).toLocaleString('fr-FR', { maximumFractionDigits: 2 })} ${d || ''}` : '—')

export default function CommandeDetail() {
  const { numero } = useParams()
  const [version, setVersion] = useState(0)
  const [busy, setBusy] = useState(false)
  const [note, setNote] = useState('')
  const [newNote, setNewNote] = useState('')

  const detail = useApi(`/admin/commandes/${numero}`, undefined, [numero, version])
  const o = detail.data

  const act = async (fn) => {
    setBusy(true)
    try { await fn(); setVersion((v) => v + 1); setNote(''); setNewNote('') }
    catch { /* l'erreur est reflétée par le rechargement */ }
    finally { setBusy(false) }
  }

  if (detail.loading) return <><PageTitle title="Commande" /><Spinner /></>
  if (detail.error || !o) {
    return (
      <>
        <PageTitle title="Commande" />
        <EmptyState icon="bi-cart-x" title="Commande introuvable" text={detail.error?.message} />
      </>
    )
  }

  const transitions = TRANSITIONS[o.statut] ?? []

  return (
    <>
      <PageTitle title={`Commande ${o.numero}`} crumbs={[{ label: 'Commandes', to: ROUTES.commandes }, { label: o.numero }]} />

      <section className="section">
        <div className="row g-3">
          {/* En-tête */}
          <div className="col-12">
            <div className="card">
              <div className="card-body d-flex flex-wrap align-items-center gap-3">
                <div className="me-auto">
                  <h4 className="mb-1">Commande {o.numero}</h4>
                  <div className="text-muted small">Passée le {formatDateTime(o.created_at)}</div>
                </div>
                <StatusBadge value={o.statut} />
                {o.whatsapp_contacte
                  ? <span className="badge bg-success">Contacté</span>
                  : <span className="badge bg-warning text-dark">Non contacté</span>}
                {o.whatsapp_client?.url && (
                  <a href={o.whatsapp_client.url} target="_blank" rel="noopener noreferrer" className="btn btn-sm btn-outline-success">
                    <i className="bi bi-whatsapp me-1"></i> WhatsApp
                  </a>
                )}
              </div>
            </div>
          </div>

          {/* Client */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Client</h5>
                <dl className="row mb-0 small">
                  <dt className="col-4 text-muted">Nom</dt><dd className="col-8">{o.client || '—'}</dd>
                  <dt className="col-4 text-muted">Téléphone</dt><dd className="col-8">{o.telephone || '—'}</dd>
                  <dt className="col-4 text-muted">Email</dt><dd className="col-8">{o.email || '—'}</dd>
                  <dt className="col-4 text-muted">Adresse</dt><dd className="col-8">{o.adresse || '—'}</dd>
                  <dt className="col-4 text-muted">Ville</dt><dd className="col-8">{o.ville || '—'}</dd>
                  <dt className="col-4 text-muted">Pays</dt><dd className="col-8">{o.pays || '—'}</dd>
                  {o.remarques && <><dt className="col-4 text-muted">Remarques</dt><dd className="col-8">{o.remarques}</dd></>}
                </dl>
              </div>
            </div>
          </div>

          {/* Produit & paiement */}
          <div className="col-lg-6">
            <div className="card mb-3">
              <div className="card-body">
                <h5 className="card-title">Article</h5>
                <dl className="row mb-0 small">
                  <dt className="col-4 text-muted">Produit</dt><dd className="col-8">{o.produit || '—'}</dd>
                  <dt className="col-4 text-muted">Quantité</dt><dd className="col-8">{o.quantite}</dd>
                  <dt className="col-4 text-muted">Prix unitaire</dt><dd className="col-8">{money(o.prix_unitaire, o.devise)}</dd>
                  <dt className="col-4 text-muted">Total</dt><dd className="col-8 fw-semibold">{money(o.total, o.devise)}</dd>
                </dl>
              </div>
            </div>

            {/* Actions */}
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Actions</h5>
                {transitions.length > 0 && (
                  <div className="row g-2 mb-3">
                    <div className="col-12">
                      <input className="form-control form-control-sm" placeholder="Note (facultatif)" value={note}
                             onChange={(e) => setNote(e.target.value)} />
                    </div>
                    <div className="col-12 d-flex flex-wrap gap-2">
                      {transitions.map(([statut, label, cls]) => (
                        <button key={statut} type="button" disabled={busy}
                                className={`btn btn-sm ${cls}`}
                                onClick={() => act(() => api.post(`/admin/commandes/${numero}/statut`, { statut, note: note || null }))}>
                          {label}
                        </button>
                      ))}
                    </div>
                  </div>
                )}

                {!o.whatsapp_contacte && (
                  <button type="button" className="btn btn-sm btn-outline-success mb-3" disabled={busy}
                          onClick={() => act(() => api.post(`/admin/commandes/${numero}/contacte`))}>
                    <i className="bi bi-whatsapp me-1"></i> Marquer contacté
                  </button>
                )}

                <div className="d-flex gap-2">
                  <input className="form-control form-control-sm" placeholder="Ajouter une note interne" value={newNote}
                         onChange={(e) => setNewNote(e.target.value)} />
                  <button type="button" className="btn btn-sm btn-outline-primary" disabled={!newNote.trim() || busy}
                          onClick={() => act(() => api.post(`/admin/commandes/${numero}/notes`, { texte: newNote.trim() }))}>
                    Ajouter
                  </button>
                </div>
              </div>
            </div>
          </div>

          {/* Historique */}
          <div className="col-12">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Historique</h5>
                {o.historique?.length ? (
                  <table className="table table-sm mb-0">
                    <thead>
                      <tr><th>De</th><th>Vers</th><th>Note</th><th>Le</th></tr>
                    </thead>
                    <tbody>
                      {o.historique.map((h, i) => (
                        <tr key={i}>
                          <td>{h.de ? <StatusBadge value={h.de} /> : '—'}</td>
                          <td>{h.vers ? <StatusBadge value={h.vers} /> : '—'}</td>
                          <td>{h.note || '—'}</td>
                          <td className="text-nowrap small">{formatDateTime(h.le)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                ) : <p className="text-muted mb-0">Aucun événement.</p>}
              </div>
            </div>
          </div>
        </div>
      </section>
    </>
  )
}
