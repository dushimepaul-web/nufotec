// Fiche utilisateur — GET /admin/users/{uuid}.
// Actions : activer/désactiver, KYC, renvoyer le code, supprimer/restaurer ; modification du profil ; sessions et journal.
import { useState } from 'react'
import { useParams } from 'react-router-dom'
import api from '../../../lib/api'
import { getUser } from '../../../lib/auth'
import { can } from '../../../lib/permissions'
import { formatDateTime } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import EmptyState from '../../../ui/EmptyState'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'
import StatusBadge from '../../../ui/StatusBadge'

const TYPES = ['admin', 'medecin', 'patient', 'entreprise', 'investisseur', 'partenaire', 'broker']
const statutOf = (u) => (u.deleted_at ? 'supprime' : u.is_active ? 'actif' : 'inactif')

function UserForm({ initial, uuid }) {
  const pays = useApi('/pays')
  const roles = useApi('/admin/roles')
  const [form, setForm] = useState({
    nom: initial.nom ?? '', prenom: initial.prenom ?? '', email: initial.email ?? '', telephone: initial.telephone ?? '',
    pays_id: initial.pays?.id ?? '', genre: initial.genre ?? '', type_utilisateur: initial.type_utilisateur ?? '',
    role_id: initial.role?.id ?? '',
    nom_entreprise: initial.nom_entreprise ?? '', secteur_activite: initial.secteur_activite ?? '',
  })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState(null)
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setError(null)
    try {
      await api.patch(`/admin/users/${uuid}`, {
        nom: form.nom.trim(), prenom: form.prenom.trim(), email: form.email.trim(),
        telephone: form.telephone || null, pays_id: form.pays_id ? Number(form.pays_id) : null,
        genre: form.genre || null, type_utilisateur: form.type_utilisateur || null,
        role_id: form.role_id ? Number(form.role_id) : null,
        nom_entreprise: form.nom_entreprise || null, secteur_activite: form.secteur_activite || null,
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
        <h5 className="card-title">Modifier le profil</h5>
        <form onSubmit={submit} noValidate>
          {error && <div className="alert alert-danger py-2 small">{error.message}</div>}
          <div className="row g-3">
            <div className="col-md-4">
              <label className="form-label">Nom</label>
              <input className="form-control" value={form.nom} onChange={(e) => set('nom', e.target.value)} />
            </div>
            <div className="col-md-4">
              <label className="form-label">Prénom</label>
              <input className="form-control" value={form.prenom} onChange={(e) => set('prenom', e.target.value)} />
            </div>
            <div className="col-md-4">
              <label className="form-label">Email</label>
              <input type="email" className="form-control" value={form.email} onChange={(e) => set('email', e.target.value)} />
            </div>
            <div className="col-md-4">
              <label className="form-label">Téléphone</label>
              <input className="form-control" value={form.telephone} onChange={(e) => set('telephone', e.target.value)} />
            </div>
            <div className="col-md-4">
              <label className="form-label">Pays</label>
              <select className="form-select" value={form.pays_id} onChange={(e) => set('pays_id', e.target.value)}>
                <option value="">—</option>
                {(pays.data ?? []).map((p) => <option key={p.id} value={p.id}>{p.pays}</option>)}
              </select>
            </div>
            <div className="col-md-4">
              <label className="form-label">Genre</label>
              <select className="form-select" value={form.genre} onChange={(e) => set('genre', e.target.value)}>
                <option value="">—</option>
                <option value="M">Masculin</option>
                <option value="F">Féminin</option>
                <option value="Autre">Autre</option>
              </select>
            </div>
            <div className="col-md-4">
              <label className="form-label">Type</label>
              <select className="form-select" value={form.type_utilisateur} onChange={(e) => set('type_utilisateur', e.target.value)}>
                <option value="">—</option>
                {TYPES.map((t) => <option key={t} value={t}>{t}</option>)}
              </select>
            </div>
            <div className="col-md-4">
              <label className="form-label">Rôle</label>
              <select className="form-select" value={form.role_id} onChange={(e) => set('role_id', e.target.value)}>
                <option value="">Automatique (selon le type)</option>
                {(roles.data ?? []).map((r) => <option key={r.id} value={r.id}>{r.nom}</option>)}
              </select>
            </div>
            <div className="col-md-4">
              <label className="form-label">Entreprise</label>
              <input className="form-control" value={form.nom_entreprise} onChange={(e) => set('nom_entreprise', e.target.value)} />
            </div>
            <div className="col-md-4">
              <label className="form-label">Secteur d'activité</label>
              <input className="form-control" value={form.secteur_activite} onChange={(e) => set('secteur_activite', e.target.value)} />
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

export default function UserDetail() {
  const { uuid } = useParams()
  const [version, setVersion] = useState(0)
  const [busy, setBusy] = useState(false)

  const detail = useApi(`/admin/users/${uuid}`, undefined, [uuid, version])
  const sessions = useApi(`/admin/users/${uuid}/sessions`, undefined, [uuid, version])
  const activities = useApi(`/admin/users/${uuid}/activities`, { per_page: 20 }, [uuid, version])
  const u = detail.data

  const currentUser = getUser()
  const canUpdate = can(currentUser, 'users', 'update')
  const canDelete = can(currentUser, 'users', 'delete')
  const canToggle = canUpdate || can(currentUser, 'users', 'activate')

  const act = async (fn) => {
    setBusy(true)
    try { await fn(); setVersion((v) => v + 1) }
    catch { /* reflété par le rechargement */ }
    finally { setBusy(false) }
  }

  if (detail.loading) return <><PageTitle title="Utilisateur" /><Spinner /></>
  if (detail.error || !u) {
    return (
      <>
        <PageTitle title="Utilisateur" />
        <EmptyState icon="bi-person-x" title="Utilisateur introuvable" text={detail.error?.message} />
      </>
    )
  }

  return (
    <>
      <PageTitle title={[u.prenom, u.nom].filter(Boolean).join(' ') || u.email}
                 crumbs={[{ label: 'Utilisateurs', to: ROUTES.users }, { label: u.email }]} />

      <section className="section">
        <div className="row g-3">
          {/* En-tête + actions */}
          <div className="col-12">
            <div className="card">
              <div className="card-body d-flex flex-wrap align-items-center gap-3">
                {u.photo_url
                  ? <img src={u.photo_url} alt="" width="56" height="56" className="rounded-circle object-fit-cover" />
                  : <div className="rounded-circle d-flex align-items-center justify-content-center bg-success text-white" style={{ width: 56, height: 56 }}>{[u.prenom, u.nom].filter(Boolean).map((s) => s[0]).join('')}</div>}
                <div className="me-auto">
                  <h4 className="mb-1">{[u.prenom, u.nom].filter(Boolean).join(' ') || '—'}</h4>
                  <div className="text-muted small">{u.email}</div>
                  <div className="d-flex flex-wrap gap-2 mt-2">
                    <StatusBadge value={statutOf(u)} />
                    <StatusBadge value={u.est_verifie ? 'verifiee' : 'non_verifiee'} />
                    {u.email_verified && <span className="badge bg-info">Email vérifié</span>}
                    <span className="badge bg-light text-dark">{u.role?.nom}</span>
                  </div>
                </div>
                <div className="d-flex flex-wrap gap-2">
                  {canToggle && (
                    <button type="button" className="btn btn-sm btn-outline-primary" disabled={busy}
                            onClick={() => act(() => api.post(`/admin/users/${uuid}/${u.is_active ? 'deactivate' : 'activate'}`))}>
                      <i className={`bi bi-power me-1`}></i> {u.is_active ? 'Désactiver' : 'Activer'}
                    </button>
                  )}
                  {canUpdate && (
                    <>
                      <button type="button" className="btn btn-sm btn-outline-secondary" disabled={busy}
                              onClick={() => act(() => api.post(`/admin/users/${uuid}/kyc`, { est_verifie: !u.est_verifie }))}>
                        <i className="bi bi-patch-check me-1"></i> {u.est_verifie ? 'Retirer KYC' : 'Valider KYC'}
                      </button>
                      <button type="button" className="btn btn-sm btn-outline-secondary" disabled={busy}
                              onClick={() => act(() => api.post(`/admin/users/${uuid}/send-reset`))}>
                        <i className="bi bi-key me-1"></i> Envoyer reset
                      </button>
                      <button type="button" className="btn btn-sm btn-outline-secondary" disabled={busy}
                              onClick={() => act(() => api.delete(`/admin/users/${uuid}/sessions`))}>
                        <i className="bi bi-box-arrow-right me-1"></i> Déconnecter
                      </button>
                      <label className="btn btn-sm btn-outline-secondary mb-0">
                        <i className="bi bi-camera me-1"></i> Photo
                        <input type="file" accept="image/*" className="d-none" disabled={busy}
                               onChange={async (e) => {
                                 const f = e.target.files[0]
                                 if (!f) return
                                 setBusy(true)
                                 try {
                                   const fd = new FormData()
                                   fd.append('photo', f)
                                   await api.post(`/admin/users/${uuid}/photo`, fd)
                                   setVersion((v) => v + 1)
                                 } catch (err) { window.alert(err.message) }
                                 finally { setBusy(false); e.target.value = '' }
                               }} />
                      </label>
                      {u.photo_url && (
                        <button type="button" className="btn btn-sm btn-outline-secondary" disabled={busy}
                                onClick={() => act(() => api.delete(`/admin/users/${uuid}/photo`))}>
                          <i className="bi bi-trash me-1"></i> Photo
                        </button>
                      )}
                    </>
                  )}
                  {canDelete && (u.deleted_at
                    ? <button type="button" className="btn btn-sm btn-outline-success" disabled={busy}
                              onClick={() => act(() => api.post(`/admin/users/${uuid}/restore`))}>
                        <i className="bi bi-arrow-counterclockwise me-1"></i> Restaurer
                      </button>
                    : <button type="button" className="btn btn-sm btn-outline-danger" disabled={busy}
                              onClick={() => act(() => api.delete(`/admin/users/${uuid}`))}>
                        <i className="bi bi-trash me-1"></i> Supprimer
                      </button>)}
                </div>
              </div>
            </div>
          </div>

          {/* Infos + formulaire */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Informations</h5>
                <dl className="row mb-0 small">
                  <dt className="col-4 text-muted">Type</dt><dd className="col-8">{u.type_utilisateur || '—'}</dd>
                  <dt className="col-4 text-muted">Rôle</dt><dd className="col-8">{u.role?.nom || '—'}</dd>
                  <dt className="col-4 text-muted">Téléphone</dt><dd className="col-8">{u.telephone || '—'}</dd>
                  <dt className="col-4 text-muted">Pays</dt><dd className="col-8">{u.pays?.pays || '—'}</dd>
                  <dt className="col-4 text-muted">Genre</dt><dd className="col-8">{u.genre || '—'}</dd>
                  <dt className="col-4 text-muted">Inscrit le</dt><dd className="col-8">{formatDateTime(u.created_at)}</dd>
                  <dt className="col-4 text-muted">Dernière connexion</dt><dd className="col-8">{formatDateTime(u.last_login_at)}</dd>
                  <dt className="col-4 text-muted">Sessions actives</dt><dd className="col-8">{u.active_sessions}</dd>
                </dl>
              </div>
            </div>
          </div>

          <div className="col-lg-6">
            {canUpdate && <UserForm initial={u} uuid={uuid} />}
          </div>

          {/* Sessions */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Appareils connectés</h5>
                {sessions.data?.length ? (
                  <table className="table table-sm mb-0">
                    <thead><tr><th>Appareil</th><th>IP</th><th>Dernière activité</th></tr></thead>
                    <tbody>
                      {sessions.data.map((s) => (
                        <tr key={s.id}>
                          <td>{[s.device_type, s.platform, s.browser].filter(Boolean).join(' · ') || '—'}</td>
                          <td>{s.ip_address || '—'}</td>
                          <td className="text-nowrap small">{formatDateTime(s.last_activity)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                ) : <p className="text-muted mb-0">Aucun appareil.</p>}
              </div>
            </div>
          </div>

          {/* Activités */}
          <div className="col-lg-6">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Activité récente</h5>
                {activities.data?.items?.length ? (
                  <table className="table table-sm mb-0">
                    <thead><tr><th>Action</th><th>Module</th><th>Le</th></tr></thead>
                    <tbody>
                      {activities.data.items.map((a) => (
                        <tr key={a.id}>
                          <td>{a.action}</td>
                          <td>{a.module || '—'}</td>
                          <td className="text-nowrap small">{formatDateTime(a.created_at)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                ) : <p className="text-muted mb-0">Aucune activité.</p>}
              </div>
            </div>
          </div>
        </div>
      </section>
    </>
  )
}
