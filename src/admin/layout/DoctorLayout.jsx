// Mise en page de l'espace médecin.
import Shell from './Shell'
import { DOCTOR_NAV } from './nav'

export default function DoctorLayout() {
  return <Shell nav={DOCTOR_NAV} brand="NUFOTEC Médecin" />
}
