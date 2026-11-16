// Badge de statut : couleur déduite de la valeur (voir lib/format.js).
import { badgeClass, labelOf } from '../lib/format'

export default function StatusBadge({ value }) {
  return <span className={`badge bg-${badgeClass(value)}`}>{labelOf(value)}</span>
}
