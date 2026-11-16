// Indicateur de chargement centré.
export default function Spinner({ text = 'Chargement…' }) {
  return (
    <div className="text-center py-5">
      <div className="spinner-border text-success" role="status"></div>
      <p className="text-muted mt-2 mb-0">{text}</p>
    </div>
  )
}
