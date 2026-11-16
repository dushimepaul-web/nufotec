// Rôles — liste, création, suppression et attribution des permissions par module de la barre latérale.
// Consomme GET/POST/PATCH/DELETE /admin/roles et GET /admin/roles/permissions.
import { useState } from 'react'
import api from '../../../lib/api'
import { getUser } from '../../../lib/auth'
import { ACTIONS, MODULES, can } from '../../../lib/permissions'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'

const ACTION_KEYS = Object.keys(ACTIONS)

function PermissionMatrix({ modules, actions, granted, disabled, onToggle }) {
  return (
    <div className="table-responsive border rounded">
      <table className="table table-sm align-middle mb-0">
        <thead>
          <tr>
            <th>Module</th>
            {ACTION_KEYS.map((key) => <th key={key} className="text-center small">{actions[key]}</th>)}
          </tr>
        </thead>
        <tbody>
          {modules.map((m) => (
            <tr key={m.key}>
              <td className="text-nowrap"><i className={`bi ${m.icon} me-1`}></i>{m.label}</td>
              {ACTION_KEYS.map((action) => (
                <td key={action} className="text-center">
                  {m.actions.includes(action) ? (
                    <input
                      type="checkbox"
                      className="form-check-input"
                      aria-label={`${m.label} — ${actions[action]}`}
                      checked={disabled || granted.has(`${m.key}.${action}`)}
                      disabled={disabled}
                      onChange={() => onToggle(`${m.key}.${action}`)}
                    />
                  ) : <span className="text-muted">·</span>}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

function RoleForm({ role, modules, actions, onClose, onSaved }) {
  const isNew = !role
  const isAdminRole = role?.slug === 'admin'
  const [nom, setNom] = useState(role?.nom ?? '')
  const [description, setDescription] = useState(role?.description ?? '')
  const [granted, setGranted] = useState(() => new Set(role?.permissions ?? []))
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')

  const toggle = (key) => {
    if (isAdminRole) return
    setGranted((prev) => {
      const next = new Set(prev)
      if (next.has(key)) next.delete(key)
      else {
        next.add(key)
        // Toute action implique de pouvoir consulter le module.
        const [module] = key.split('.')
        next.add(`${module}.read`)
      }
      return next
    })
  }

  const submit = async (e) => {
    e.preventDefault()
    if (nom.trim().length < 2) { setError('Le nom doit contenir au moins 2 caractères.'); return }
    setSending(true)
    setError('')
    try {
      const body = { nom: nom.trim(), description: description.trim() || null }
      if (!isAdminRole) body.permissions = [...granted].sort()
      if (isNew) await api.post('/admin/roles', body)
      else await api.patch(`/admin/roles/${role.id}`, body)
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <form onSubmit={submit} noValidate>
      {error && <div className="alert alert-danger py-2 small">{error}</div>}
      <div className="row g-3">
        <div className="col-md-6">
          <label className="form-label">Nom affiché *</label>
          <input className="form-control" value={nom} onChange={(e) => setNom(e.target.value)} />
        </div>
        <div className="col-md-6">
          <label className="form-label">Slug</label>
          <input className="form-control" value={role?.slug ?? '(généré à partir du nom)'} disabled />
        </div>
        <div className="col-12">
          <label className="form-label">Description</label>
          <textarea className="form-control" rows="2" value={description}
                    onChange={(e) => setDescription(e.target.value)} />
        </div>
        <div className="col-12">
          <div className="d-flex align-items-center justify-content-between mb-2">
            <label className="form-label mb-0">Permissions ({isAdminRole ? 'toutes' : granted.size})</label>
            {isAdminRole && <small className="text-muted">Le rôle administrateur a toujours tous les droits.</small>}
          </div>
          <PermissionMatrix modules={modules} actions={actions} granted={granted}
                            disabled={isAdminRole || sending} onToggle={toggle} />
        </div>
        <div className="col-12 d-flex justify-content-end gap-2">
          <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
          <button type="submit" className="btn btn-primary" disabled={sending}>
            {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null}
            {isNew ? 'Créer le rôle' : 'Enregistrer'}
          </button>
        </div>
      </div>
    </form>
  )
}

function RoleEditor({ role, modules, actions, onClose, onSaved }) {
  const detail = useApi(role ? `/admin/roles/${role.id}` : null)
  const loading = role && detail.loading

  return (
    <Modal open onClose={onClose} icon="bi-person-badge" size="xl"
           title={role ? `Rôle — ${role.nom}` : 'Nouveau rôle'}>
      {loading ? <Spinner /> : detail.error ? (
        <div className="alert alert-danger py-2 small mb-0">{detail.error.message}</div>
      ) : (
        <RoleForm role={detail.data ?? null} modules={modules} actions={actions}
                  onClose={onClose} onSaved={onSaved} />
      )}
    </Modal>
  )
}

export default function Roles() {
  const user = getUser()
  const [version, setVersion] = useState(0)
  const [selected, setSelected] = useState(null)
  const [creating, setCreating] = useState(false)
  const roles = useApi('/admin/roles', undefined, [version])
  const catalog = useApi('/admin/roles/permissions', undefined, [version])
  const items = roles.data ?? []
  const modules = catalog.data?.modules ?? MODULES
  const actions = catalog.data?.actions ?? ACTIONS

  const canCreate = can(user, 'roles', 'create')
  const canUpdate = can(user, 'roles', 'update')
  const canDelete = can(user, 'roles', 'delete')

  const remove = async (role) => {
    if (!window.confirm(`Supprimer le rôle « ${role.nom} » ?`)) return
    try {
      await api.delete(`/admin/roles/${role.id}`)
      setVersion((v) => v + 1)
    } catch (err) {
      window.alert(err.message)
    }
  }

  const columns = [
    { key: 'nom', label: 'Rôle', render: (r) => <span className="fw-semibold">{r.nom}</span> },
    { key: 'slug', label: 'Slug', render: (r) => <code>{r.slug}</code> },
    { key: 'description', label: 'Description', render: (r) => r.description || '—' },
    { key: 'users_count', label: 'Utilisateurs' },
    {
      key: 'permissions', label: 'Permissions',
      render: (r) => (r.slug === 'admin' ? 'Toutes' : (r.permissions?.length ?? 0)),
    },
    {
      key: 'action', label: '', className: 'text-end',
      render: (r) => (
        <>
          {canUpdate && (
            <button type="button" className="btn btn-sm btn-outline-primary me-1" onClick={() => setSelected(r)}>
              <i className="bi bi-pencil"></i>
            </button>
          )}
          {canDelete && !r.systeme && (
            <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(r)}>
              <i className="bi bi-trash"></i>
            </button>
          )}
        </>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Rôles" crumbs={[{ label: 'Rôles' }]} />
      <section className="section">
        <DataTable
          title={`Rôles (${items.length})`}
          columns={columns}
          items={items}
          loading={roles.loading}
          error={roles.error}
          emptyText={{ title: 'Aucun rôle' }}
          toolbar={canCreate ? (
            <button type="button" className="btn btn-primary" onClick={() => setCreating(true)}>
              <i className="bi bi-plus-lg me-1"></i> Nouveau rôle
            </button>
          ) : null}
        />
      </section>

      {creating && (
        <RoleEditor modules={modules} actions={actions}
                    onClose={() => setCreating(false)} onSaved={() => setVersion((v) => v + 1)} />
      )}
      {selected && (
        <RoleEditor role={selected} modules={modules} actions={actions}
                    onClose={() => setSelected(null)} onSaved={() => setVersion((v) => v + 1)} />
      )}
    </>
  )
}
