// Liste des médecins — GET /admin/medecins (liste simple, recherche côté serveur).
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { formatPrice } from '../../../lib/format'
import { ROUTES } from '../../../lib/routes'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'
import StatusBadge from '../../../ui/StatusBadge'

export default function MedecinsList() {
  const [q, setQ] = useState('')
  const [search, setSearch] = useState('')

  useEffect(() => {
    const timer = setTimeout(() => setQ(search.trim()), 350)
    return () => clearTimeout(timer)
  }, [search])

  const medecins = useApi('/admin/medecins', q ? { q } : undefined)
  const items = medecins.data ?? []

  const columns = [
    {
      key: 'nom',
      label: 'Médecin',
      render: (m) => (
        <>
          <div className="d-flex align-items-center gap-2">
            {m.photo_url && <img src={m.photo_url} alt="" width="32" height="32" className="rounded-circle object-fit-cover" />}
            <div>
              <div className="fw-semibold">
                <Link to={ROUTES.medecin(m.uuid)}>{[m.prenom, m.nom].filter(Boolean).join(' ')}</Link>
              </div>
              <small className="text-muted">{m.email}</small>
            </div>
          </div>
        </>
      ),
    },
    { key: 'specialite', label: 'Spécialité' },
    { key: 'telephone', label: 'Téléphone', render: (m) => m.telephone || m.whatsapp || '—' },
    {
      key: 'licence',
      label: 'Licence',
      render: (m) => <StatusBadge value={m.licence_verifiee ? 'verifiee' : 'non_verifiee'} />,
    },
    { key: 'disponible', label: 'Disponibilité', render: (m) => <StatusBadge value={m.disponible ? 'disponible' : 'indisponible'} /> },
    { key: 'tarif', label: 'Tarif', render: (m) => formatPrice(m) },
    {
      key: 'action',
      label: '',
      className: 'text-end',
      render: (m) => (
        <Link to={ROUTES.medecin(m.uuid)} className="btn btn-sm btn-outline-primary">
          <i className="bi bi-eye"></i> Fiche
        </Link>
      ),
    },
  ]

  return (
    <>
      <PageTitle title="Médecins" crumbs={[{ label: 'Médecins' }]} />
      <section className="section">
        <DataTable
          title={`Médecins (${items.length})`}
          columns={columns}
          items={items}
          loading={medecins.loading}
          error={medecins.error}
          emptyText={{ title: 'Aucun médecin', text: q ? `Aucun résultat pour « ${q} »` : undefined }}
          toolbar={
            <>
              <div className="search-bar">
                <form className="search-form d-flex align-items-center" onSubmit={(e) => e.preventDefault()}>
                  <input type="text" placeholder="Rechercher…" title="Rechercher" value={search}
                         onChange={(e) => setSearch(e.target.value)} />
                  <button type="submit" title="Rechercher"><i className="bi bi-search"></i></button>
                </form>
              </div>
              <Link to={ROUTES.medecinNouveau} className="btn btn-primary">
                <i className="bi bi-plus-lg me-1"></i> Nouveau médecin
              </Link>
            </>
          }
        />
      </section>
    </>
  )
}
