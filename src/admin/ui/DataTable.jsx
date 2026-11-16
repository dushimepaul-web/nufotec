// Tableau paginé côté serveur (carte NiceAdmin). Colonnes : { key, label, render? }.
import EmptyState from './EmptyState'
import Pagination from './Pagination'
import Spinner from './Spinner'

export default function DataTable({ title, columns, items, meta, onPage, loading, error, toolbar, emptyText }) {
  return (
    <div className="card">
      <div className="card-body">
        <div className="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          {title && <h5 className="card-title mb-0">{title}</h5>}
          {toolbar}
        </div>

        {loading ? (
          <Spinner />
        ) : error ? (
          <EmptyState icon="bi-exclamation-triangle" title="Erreur de chargement" text={error.message} />
        ) : !items?.length ? (
          <EmptyState title={emptyText?.title ?? 'Aucun résultat'} text={emptyText?.text} />
        ) : (
          <>
            <div className="table-responsive">
              <table className="table table-hover datatable align-middle">
                <thead>
                  <tr>
                    {columns.map((c) => <th key={c.key} scope="col" className={c.className}>{c.label}</th>)}
                  </tr>
                </thead>
                <tbody>
                  {items.map((row, i) => (
                    <tr key={row.uuid ?? row.numero ?? row.id ?? i}>
                      {columns.map((c) => (
                        <td key={c.key} className={c.className}>
                          {c.render ? c.render(row) : row[c.key]}
                        </td>
                      ))}
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            <Pagination meta={meta} onPage={onPage} />
          </>
        )}
      </div>
    </div>
  )
}
