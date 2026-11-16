// Réseaux sociaux — CRUD via SimpleCrud.
import SimpleCrud from '../../../ui/SimpleCrud'
import StatusBadge from '../../../ui/StatusBadge'

const FIELDS = [
  { key: 'plateforme', label: 'Plateforme', type: 'text', required: true },
  { key: 'url', label: 'URL', type: 'text', required: true },
  { key: 'libelle', label: 'Libellé', type: 'text' },
  { key: 'icone', label: 'Icône', type: 'text' },
  { key: 'ordre', label: 'Ordre', type: 'number' },
  { key: 'actif', label: 'Actif', type: 'checkbox', default: true },
  { key: 'nouvel_onglet', label: 'Nouvel onglet', type: 'checkbox', default: true },
]

const COLUMNS = [
  { key: 'plateforme', label: 'Plateforme', render: (s) => <span className="fw-semibold text-capitalize">{s.plateforme}</span> },
  { key: 'url', label: 'URL', render: (s) => <a href={s.url} target="_blank" rel="noopener noreferrer" className="text-truncate d-inline-block" style={{ maxWidth: 220 }}>{s.url}</a> },
  { key: 'actif', label: 'État', render: (s) => <StatusBadge value={s.actif ? 'actif' : 'inactif'} /> },
]

export default function ReseauxSociaux() {
  return (
    <SimpleCrud title="Réseaux sociaux" base="/admin/contenu/reseaux-sociaux" columns={COLUMNS} fields={FIELDS} module="reseaux_sociaux"
                buttonLabel="Nouveau réseau" />
  )
}
