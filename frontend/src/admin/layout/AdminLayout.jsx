// Mise en page de l'espace administrateur.
import Shell from './Shell'
import { ADMIN_NAV } from './nav'

export default function AdminLayout() {
  return <Shell nav={ADMIN_NAV} brand="NUFOTEC Admin" />
}
