// Chiffres clés — CRUD via SimpleCrud.
import SimpleCrud from '../../../ui/SimpleCrud'

const FIELDS = [
  { key: 'etiquette', label: 'Étiquette', type: 'text', required: true },
  { key: 'valeur', label: 'Valeur', type: 'text', required: true },
  { key: 'unite', label: 'Unité', type: 'text' },
  { key: 'icone', label: 'Icône (bootstrap)', type: 'text' },
  { key: 'ordre', label: 'Ordre', type: 'number' },
  { key: 'annee', label: 'Année', type: 'number' },
  { key: 'description', label: 'Description', type: 'textarea' },
]

const COLUMNS = [
  { key: 'etiquette', label: 'Étiquette', render: (k) => <span className="fw-semibold">{k.etiquette}</span> },
  { key: 'valeur', label: 'Valeur' },
  { key: 'unite', label: 'Unité', render: (k) => k.unite || '—' },
  { key: 'ordre', label: 'Ordre' },
  { key: 'annee', label: 'Année', render: (k) => k.annee || '—' },
]

export default function ChiffresCles() {
  return (
    <SimpleCrud title="Chiffres clés" base="/admin/contenu/chiffres-cles" columns={COLUMNS} fields={FIELDS} module="chiffres_cles"
                buttonLabel="Nouveau chiffre" />
  )
}
