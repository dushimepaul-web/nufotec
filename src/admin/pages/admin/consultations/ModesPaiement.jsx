// Comptes de paiement (Lumicash, EcoCash…) — CRUD via /admin/modes-paiement.
import SimpleCrud from '../../../ui/SimpleCrud'
import StatusBadge from '../../../ui/StatusBadge'

const FIELDS = [
  { key: 'nom', label: 'Nom', type: 'text', required: true },
  { key: 'numero_compte', label: 'Numéro de compte', type: 'text' },
  { key: 'nom_compte', label: 'Titulaire', type: 'text' },
  { key: 'instructions', label: 'Instructions', type: 'text' },
  { key: 'actif', label: 'Actif', type: 'checkbox', default: true },
]

const COLUMNS = [
  { key: 'nom', label: 'Compte', render: (m) => <span className="fw-semibold">{m.nom}</span> },
  { key: 'numero_compte', label: 'N° compte', render: (m) => m.numero_compte || '—' },
  { key: 'nom_compte', label: 'Titulaire', render: (m) => m.nom_compte || '—' },
  { key: 'actif', label: 'État', render: (m) => <StatusBadge value={m.actif ? 'actif' : 'inactif'} /> },
]

export default function ModesPaiement() {
  return (
    <SimpleCrud title="Comptes de paiement" base="/admin/modes-paiement" columns={COLUMNS} fields={FIELDS} module="modes_paiement"
                buttonLabel="Nouveau compte" crumbs={[{ label: 'Consultations', to: '/admin/consultations' }, { label: 'Comptes de paiement' }]} />
  )
}
