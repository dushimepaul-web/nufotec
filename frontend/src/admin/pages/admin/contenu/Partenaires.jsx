// Partenaires — CRUD via SimpleCrud.
import SimpleCrud from '../../../ui/SimpleCrud'
import StatusBadge from '../../../ui/StatusBadge'

const TYPES = [
  ['outgrower', 'Producteur (outgrower)'],
  ['client_export', 'Client export'],
  ['fournisseur', 'Fournisseur'],
  ['institutionnel', 'Institutionnel'],
  ['scientifique', 'Scientifique'],
  ['financier', 'Financier'],
  ['regulateur', 'Régulateur'],
]

const NIVEAUX = [
  ['strategique', 'Stratégique'],
  ['technique', 'Technique'],
  ['commercial', 'Commercial'],
  ['academique', 'Académique'],
]

const FIELDS = [
  { key: 'nom', label: 'Nom', type: 'text', required: true },
  { key: 'type', label: 'Type', type: 'select', options: TYPES, required: true },
  { key: 'niveau', label: 'Niveau', type: 'select', options: NIVEAUX },
  { key: 'pays', label: 'Pays', type: 'text' },
  { key: 'site_web', label: 'Site web', type: 'text' },
  { key: 'date_debut', label: 'Date de début', type: 'date' },
  { key: 'description', label: 'Description', type: 'textarea' },
  { key: 'actif', label: 'Actif', type: 'checkbox', default: true },
]

const COLUMNS = [
  {
    key: 'nom', label: 'Partenaire',
    render: (p) => (
      <div className="d-flex align-items-center gap-2">
        {p.logo && <img loading="lazy" decoding="async" src={p.logo} alt="" width="32" height="32" className="rounded object-fit-cover" />}
        <span className="fw-semibold">{p.nom}</span>
      </div>
    ),
  },
  { key: 'type', label: 'Type', render: (p) => p.type || '—' },
  { key: 'pays', label: 'Pays', render: (p) => p.pays || '—' },
  { key: 'actif', label: 'État', render: (p) => <StatusBadge value={p.actif ? 'actif' : 'inactif'} /> },
]

export default function Partenaires() {
  return (
    <SimpleCrud title="Partenaires" base="/admin/contenu/partenaires" columns={COLUMNS} fields={FIELDS} module="partenaires"
                imageField={{ label: 'Logo', previewKey: 'logo' }} buttonLabel="Nouveau partenaire" />
  )
}
