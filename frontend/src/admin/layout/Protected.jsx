// Garde d'accès : redirige vers la connexion si non connecté, ou selon le rôle attendu.
import { Navigate, Outlet } from 'react-router-dom'
import { getUser } from '../lib/auth'
import { ROUTES } from '../lib/routes'

export default function Protected({ roles = [] }) {
  const user = getUser()
  const role = user?.role?.slug ?? null

  if (!user) return <Navigate to={ROUTES.login} replace />

  // L'espace admin accueille aussi les rôles personnalisés disposant d'au moins une permission.
  const allowed = roles.length === 0
    || roles.includes(role)
    || (roles.includes('admin') && (user.permissions?.length ?? 0) > 0)

  if (!allowed) {
    return <Navigate to={role === 'medecin' ? ROUTES.doctorHome : ROUTES.login} replace />
  }

  return <Outlet />
}
