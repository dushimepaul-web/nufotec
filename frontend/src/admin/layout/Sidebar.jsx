// Barre latérale (NiceAdmin) : liste des liens autorisés par les permissions, état actif via NavLink.
import { NavLink } from 'react-router-dom'
import { canSeeModule } from '../lib/permissions'

/** Ne garde que les entrées visibles et supprime les titres de section devenus vides. */
function visibleItems(nav, user) {
  const out = []
  let pendingSection = null
  nav.forEach((item) => {
    if (item.section) {
      pendingSection = item
      return
    }
    if (item.perm && !canSeeModule(user, item.perm)) return
    if (pendingSection) {
      out.push({ ...pendingSection, key: `s-${out.length}` })
      pendingSection = null
    }
    out.push(item)
  })
  return out
}

export default function Sidebar({ nav, brand, user }) {
  const items = visibleItems(nav, user)
  return (
    <aside id="sidebar" className="sidebar">
      <ul className="sidebar-nav" id="sidebar-nav">
        <li className="nav-heading">{brand}</li>
        {items.map((item, i) => {
          if (item.section) {
            return <li key={item.key ?? `s-${i}`} className="nav-heading">{item.section}</li>
          }
          return (
            <li key={item.to} className="nav-item">
              <NavLink
                to={item.to}
                end={item.end}
                className={({ isActive }) => `nav-link${isActive ? '' : ' collapsed'}`}
              >
                <i className={`bi ${item.icon}`}></i>
                <span>{item.label}</span>
              </NavLink>
            </li>
          )
        })}
      </ul>
    </aside>
  )
}
