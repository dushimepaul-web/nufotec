// Pagination pilotée par le serveur (meta de l'API : page, pages, total).
export default function Pagination({ meta, onPage }) {
  if (!meta || meta.pages <= 1) return null

  const { page, pages, total } = meta
  const start = Math.max(1, page - 2)
  const end = Math.min(pages, page + 2)
  const nums = []
  if (start > 1) nums.push(1, ...(start > 2 ? ['…'] : []))
  for (let i = start; i <= end; i++) nums.push(i)
  if (end < pages) nums.push(...(end < pages - 1 ? ['…'] : []), pages)

  return (
    <nav className="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <small className="text-muted">
        Page {page} sur {pages} · {total} élément{total > 1 ? 's' : ''}
      </small>
      <ul className="pagination pagination-sm mb-0">
        <li className={`page-item${page <= 1 ? ' disabled' : ''}`}>
          <button className="page-link" onClick={() => onPage(page - 1)} disabled={page <= 1}>Précédent</button>
        </li>
        {nums.map((n, i) => (n === '…'
          ? <li key={`gap-${i}`} className="page-item disabled"><span className="page-link">…</span></li>
          : <li key={n} className={`page-item${n === page ? ' active' : ''}`}>
              <button className="page-link" onClick={() => onPage(n)}>{n}</button>
            </li>))}
        <li className={`page-item${page >= pages ? ' disabled' : ''}`}>
          <button className="page-link" onClick={() => onPage(page + 1)} disabled={page >= pages}>Suivant</button>
        </li>
      </ul>
    </nav>
  )
}
