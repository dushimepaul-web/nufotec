// Page provisoire pour les modules pas encore construits.
import PageTitle from '../ui/PageTitle'

export default function Placeholder({ title, crumbs = [] }) {
  return (
    <>
      <PageTitle title={title} crumbs={crumbs} />
      <section className="section">
        <div className="card">
          <div className="card-body text-center py-5">
            <i className="bi bi-tools" style={{ fontSize: 48, color: 'var(--bs-secondary)' }}></i>
            <h5 className="mt-3 mb-1">{title}</h5>
            <p className="text-muted mb-0">Ce module arrive prochainement.</p>
          </div>
        </div>
      </section>
    </>
  )
}
