// État vide / erreur générique.
export default function EmptyState({ icon = 'bi-inbox', title = 'Aucun résultat', text }) {
  return (
    <div className="text-center py-5">
      <i className={`bi ${icon}`} style={{ fontSize: 48, color: 'var(--bs-secondary)' }}></i>
      <h5 className="mt-3 mb-1">{title}</h5>
      {text && <p className="text-muted mb-0">{text}</p>}
    </div>
  )
}
