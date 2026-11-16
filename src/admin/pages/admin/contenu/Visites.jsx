// Visites du site — GET /admin/contenu/visites (anonymes : par jour, pages, pays, appareils).
import { useState } from 'react'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'

const RANGES = [[7, '7 jours'], [30, '30 jours'], [90, '90 jours'], [365, '1 an']]

export default function Visites() {
  const [jours, setJours] = useState(30)
  const visites = useApi('/admin/contenu/visites', { jours }, [jours])

  if (visites.loading) return <><PageTitle title="Visites" /><Spinner /></>
  if (visites.error) return <><PageTitle title="Visites" /><div className="alert alert-danger m-3">{visites.error.message}</div></>
  const s = visites.data

  return (
    <>
      <PageTitle title="Visites" crumbs={[{ label: 'Contenu', to: '/admin/contenu/pages' }, { label: 'Visites' }]} />
      <section className="section">
        <div className="d-flex justify-content-end mb-3">
          <select className="form-select form-select-sm w-auto" value={jours} onChange={(e) => setJours(Number(e.target.value))}>
            {RANGES.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
          </select>
        </div>

        <div className="row g-3">
          <div className="col-md-4">
            <div className="card info-card sales-card">
              <div className="card-body">
                <h5 className="card-title">Visites</h5>
                <div className="d-flex align-items-center">
                  <div className="card-icon rounded-circle d-flex align-items-center justify-content-center"><i className="bi bi-eye"></i></div>
                  <div className="ps-3"><h6>{s.visites}</h6></div>
                </div>
              </div>
            </div>
          </div>
          <div className="col-md-4">
            <div className="card info-card revenue-card">
              <div className="card-body">
                <h5 className="card-title">Visiteurs uniques</h5>
                <div className="d-flex align-items-center">
                  <div className="card-icon rounded-circle d-flex align-items-center justify-content-center"><i className="bi bi-people"></i></div>
                  <div className="ps-3"><h6>{s.visiteurs_uniques}</h6></div>
                </div>
              </div>
            </div>
          </div>
          <div className="col-md-4">
            <div className="card info-card customers-card">
              <div className="card-body">
                <h5 className="card-title">Période</h5>
                <div className="d-flex align-items-center">
                  <div className="card-icon rounded-circle d-flex align-items-center justify-content-center"><i className="bi bi-calendar"></i></div>
                  <div className="ps-3"><h6>{s.periode_jours} jours</h6></div>
                </div>
              </div>
            </div>
          </div>

          <div className="col-lg-4">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Pages populaires</h5>
                <table className="table table-sm mb-0">
                  <thead><tr><th>Page</th><th>Visites</th></tr></thead>
                  <tbody>
                    {(s.pages ?? []).map((p, i) => <tr key={i}><td className="text-truncate" style={{ maxWidth: 200 }}>{p.valeur}</td><td>{p.visites}</td></tr>)}
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div className="col-lg-4">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Pays</h5>
                <table className="table table-sm mb-0">
                  <thead><tr><th>Pays</th><th>Visites</th></tr></thead>
                  <tbody>
                    {(s.pays ?? []).map((p, i) => <tr key={i}><td>{p.valeur}</td><td>{p.visites}</td></tr>)}
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div className="col-lg-4">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Appareils</h5>
                <table className="table table-sm mb-0">
                  <thead><tr><th>Appareil</th><th>Visites</th></tr></thead>
                  <tbody>
                    {(s.appareils ?? []).map((p, i) => <tr key={i}><td className="text-capitalize">{p.valeur}</td><td>{p.visites}</td></tr>)}
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div className="col-12">
            <div className="card">
              <div className="card-body">
                <h5 className="card-title">Par jour</h5>
                <DataTable
                  columns={[
                    { key: 'jour', label: 'Jour' },
                    { key: 'visites', label: 'Visites' },
                    { key: 'visiteurs', label: 'Visiteurs' },
                  ]}
                  items={s.par_jour ?? []}
                />
              </div>
            </div>
          </div>
        </div>
      </section>
    </>
  )
}
