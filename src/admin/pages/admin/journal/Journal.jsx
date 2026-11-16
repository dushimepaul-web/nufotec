// Journal d'activité — GET /admin/journal et /admin/journal/securite.
import { useEffect, useState } from 'react'
import { formatDateTime } from '../../../lib/format'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'

const NIVEAUX = ['info', 'warning', 'error', 'critical']
const niveauBadge = (n) => {
  const map = { info: 'info', warning: 'warning', error: 'danger', critical: 'dark' }
  return <span className={`badge bg-${map[n] ?? 'secondary'}`}>{n}</span>
}

export default function Journal() {
  const [tab, setTab] = useState('activites')
  const [page, setPage] = useState(1)
  const [module, setModule] = useState('')
  const [niveau, setNiveau] = useState('')
  const [search, setSearch] = useState('')
  const [q, setQ] = useState('')

  useEffect(() => {
    const timer = setTimeout(() => setQ(search.trim()), 350)
    return () => clearTimeout(timer)
  }, [search])

  const modules = useApi('/admin/journal/modules')
  const params = { page, per_page: 20, ...(module ? { module } : {}), ...(q ? { q } : {}) }
  const activites = useApi('/admin/journal', params)
  const securite = useApi('/admin/journal/securite', { page, per_page: 20, ...(niveau ? { niveau } : {}) }, [niveau])

  const colsActivites = [
    { key: 'le', label: 'Le', render: (a) => <span className="text-nowrap small">{formatDateTime(a.le)}</span> },
    { key: 'module', label: 'Module', render: (a) => a.module || '—' },
    { key: 'action', label: 'Action', render: (a) => <code>{a.action}</code> },
    { key: 'description', label: 'Description', render: (a) => a.description || '—' },
    { key: 'element', label: 'Élément', render: (a) => a.element || '—' },
    { key: 'auteur', label: 'Auteur', render: (a) => a.auteur || '—' },
    { key: 'ip', label: 'IP', render: (a) => a.ip || '—' },
  ]

  const colsSecurite = [
    { key: 'le', label: 'Le', render: (a) => <span className="text-nowrap small">{formatDateTime(a.le)}</span> },
    { key: 'niveau', label: 'Niveau', render: (a) => niveauBadge(a.niveau) },
    { key: 'action', label: 'Action', render: (a) => <code>{a.action}</code> },
    { key: 'description', label: 'Description', render: (a) => a.description || '—' },
    { key: 'ip', label: 'IP', render: (a) => a.ip || '—' },
  ]

  return (
    <>
      <PageTitle title="Journal" crumbs={[{ label: 'Journal' }]} />

      <section className="section">
        <ul className="nav nav-tabs nav-tabs-bordered mb-3">
          <li className="nav-item">
            <button type="button" className={`nav-link${tab === 'activites' ? ' active' : ''}`} onClick={() => { setTab('activites'); setPage(1) }}>
              Activités
            </button>
          </li>
          <li className="nav-item">
            <button type="button" className={`nav-link${tab === 'securite' ? ' active' : ''}`} onClick={() => { setTab('securite'); setPage(1) }}>
              Sécurité
            </button>
          </li>
        </ul>

        {tab === 'activites' ? (
          <DataTable
            title={`Activités (${activites.data?.meta?.total ?? 0})`}
            columns={colsActivites}
            items={activites.data?.items ?? []}
            meta={activites.data?.meta}
            onPage={setPage}
            loading={activites.loading}
            error={activites.error}
            emptyText={{ title: 'Aucune activité' }}
            toolbar={
              <>
                <div className="search-bar">
                  <form className="search-form d-flex align-items-center" onSubmit={(e) => e.preventDefault()}>
                    <input type="text" placeholder="Rechercher…" title="Rechercher" value={search}
                           onChange={(e) => { setSearch(e.target.value); setPage(1) }} />
                    <button type="submit" title="Rechercher"><i className="bi bi-search"></i></button>
                  </form>
                </div>
                <select className="form-select form-select-sm w-auto" value={module}
                        onChange={(e) => { setModule(e.target.value); setPage(1) }}>
                  <option value="">Tous les modules</option>
                  {(modules.data ?? []).map((m) => <option key={m.module} value={m.module}>{m.module}</option>)}
                </select>
              </>
            }
          />
        ) : (
          <DataTable
            title={`Événements de sécurité (${securite.data?.meta?.total ?? 0})`}
            columns={colsSecurite}
            items={securite.data?.items ?? []}
            meta={securite.data?.meta}
            onPage={setPage}
            loading={securite.loading}
            error={securite.error}
            emptyText={{ title: 'Aucun événement' }}
            toolbar={
              <select className="form-select form-select-sm w-auto" value={niveau}
                      onChange={(e) => { setNiveau(e.target.value); setPage(1) }}>
                <option value="">Tous les niveaux</option>
                {NIVEAUX.map((n) => <option key={n} value={n}>{n}</option>)}
              </select>
            }
          />
        )}
      </section>
    </>
  )
}
