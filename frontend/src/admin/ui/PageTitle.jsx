// Titre de page + fil d'Ariane (NiceAdmin .pagetitle).
import { Link } from 'react-router-dom'

export default function PageTitle({ title, crumbs = [] }) {
  return (
    <div className="pagetitle">
      <h1>{title}</h1>
      <nav>
        <ol className="breadcrumb">
          {crumbs.map((c, i) => {
            const last = i === crumbs.length - 1
            return (
              <li key={i} className={`breadcrumb-item${last ? ' active' : ''}`}>
                {c.to && !last ? <Link to={c.to}>{c.label}</Link> : c.label}
              </li>
            )
          })}
        </ol>
      </nav>
    </div>
  )
}
