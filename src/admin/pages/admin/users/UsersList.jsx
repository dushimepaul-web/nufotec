// Liste des utilisateurs — GET /admin/users (recherche + pagination serveur) + création + export.
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api, { downloadCsv } from '../../../lib/api'
import { getUser } from '../../../lib/auth'
import { can } from '../../../lib/permissions'
import { formatDate } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'
import UserForm from './UserForm'

const statutOf = (u) => (u.deleted_at ? 'supprime' : u.is_active ? 'actif' : 'inactif')

export default function UsersList() {
  const [page, setPage] = useState(1)
  const [q, setQ] = useState('')
  const [search, setSearch] = useState('')
  const [version, setVersion] = useState(0)
  const [showForm, setShowForm] = useState(false)
  const [exporting, setExporting] = useState(false)

  // Recherche avec un petit délai pendant la frappe
  useEffect(() => {
    const timer = setTimeout(() => setQ(search.trim()), 350)
    return () => clearTimeout(timer)
  }, [search])

  const onSearch = (value) => { setSearch(value); setPage(1) }

  const users = useApi('/admin/users', { page, per_page: 15, ...(q ? { q } : {}) }, [version])
  const items = users.data?.items ?? []
  const currentUser = getUser()
  const canCreate = can(currentUser, 'users', 'create')
  const canExport = can(currentUser, 'users', 'export')
  const canPurge = can(currentUser, 'users', 'delete')

  const exportCsv = async () => {
    setExporting(true)
    try { await downloadCsv('/admin/users/export.csv', { q }, `utilisateurs_${new Date().toISOString().slice(0, 10)}.csv`) }
    catch { window.alert("L'export a échoué.") }
    finally { setExporting(false) }
  }

  const purge = async () => {
    const jours = window.prompt('Anonymiser les comptes supprimés depuis combien de jours ? (défaut 30)', '30')
    if (jours == null) return
    if (!window.confirm(`Anonymiser définitivement les comptes supprimés depuis ${jours} jours ?`)) return
    try {
      const r = await api.post('/admin/users/purger', { jours: Number(jours) || null })
      window.alert(`${r.anonymises} compte(s) anonymisé(s)`)
      setVersion((v) => v + 1)
    } catch (err) {
      window.alert(err.message)
    }
  }

  const columns = [
    {
      key: 'nom',
      label: 'Utilisateur',
      render: (u) => (
        <>
          <div className="fw-semibold">
            <Link to={ROUTES.user(u.uuid)}>{[u.prenom, u.nom].filter(Boolean).join(' ') || '—'}</Link>
          </div>
          <small className="text-muted">{u.email}</small>
        </>
      ),
    },
    { key: 'telephone', label: 'Téléphone', render: (u) => u.telephone || '—' },
    { key: 'type_utilisateur', label: 'Type', render: (u) => u.type_utilisateur || '—' },
    { key: 'role', label: 'Rôle', render: (u) => u.role?.nom || '—' },
    { key: 'statut', label: 'Statut', render: (u) => <StatusBadge value={statutOf(u)} /> },
    { key: 'created_at', label: 'Inscrit le', render: (u) => formatDate(u.created_at) },
  ]

  return (
    <>
      <PageTitle title="Utilisateurs" crumbs={[{ label: 'Utilisateurs' }]} />
      <section className="section">
        <DataTable
          title={`Utilisateurs (${users.data?.meta?.total ?? 0})`}
          columns={columns}
          items={items}
          meta={users.data?.meta}
          onPage={setPage}
          loading={users.loading}
          error={users.error}
          emptyText={{ title: 'Aucun utilisateur', text: q ? `Aucun résultat pour « ${q} »` : undefined }}
          toolbar={
            <>
              <div className="search-bar">
                <form className="search-form d-flex align-items-center" onSubmit={(e) => e.preventDefault()}>
                  <input type="text" placeholder="Rechercher…" title="Rechercher" value={search}
                         onChange={(e) => onSearch(e.target.value)} />
                  <button type="submit" title="Rechercher"><i className="bi bi-search"></i></button>
                </form>
              </div>
              <button type="button" className="btn btn-sm btn-outline-secondary" onClick={exportCsv}
                      disabled={exporting || !canExport}>
                {exporting ? <span className="spinner-border spinner-border-sm me-1"></span> : <i className="bi bi-download me-1"></i>}
                Exporter
              </button>
              {canPurge && (
                <button type="button" className="btn btn-sm btn-outline-secondary" onClick={purge}>
                  <i className="bi bi-eraser me-1"></i> Purge
                </button>
              )}
              {canCreate && (
                <button type="button" className="btn btn-primary" onClick={() => setShowForm(true)}>
                  <i className="bi bi-plus-lg me-1"></i> Nouvel utilisateur
                </button>
              )}
            </>
          }
        />
      </section>

      <UserForm open={showForm} onClose={() => setShowForm(false)} onSaved={() => setVersion((v) => v + 1)} />
    </>
  )
}
