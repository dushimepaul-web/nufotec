// Témoignages — CRUD via SimpleCrud (une vidéo par témoignage).
import SimpleCrud from '../../../ui/SimpleCrud'
import StatusBadge from '../../../ui/StatusBadge'

const FIELDS = [
  { key: 'titre', label: 'Titre', type: 'text', required: true },
  { key: 'video_url', label: 'URL de la vidéo', type: 'text', required: true },
  { key: 'approuve', label: 'Approuvé', type: 'checkbox' },
]

const COLUMNS = [
  {
    key: 'titre', label: 'Témoignage',
    render: (t) => (
      <div className="d-flex align-items-center gap-2">
        {t.miniature && <img loading="lazy" decoding="async" src={t.miniature} alt="" width="40" height="40" className="rounded object-fit-cover" />}
        <span className="fw-semibold">{t.titre}</span>
      </div>
    ),
  },
  { key: 'video_url', label: 'Vidéo', render: (t) => <a href={t.video_url} target="_blank" rel="noopener noreferrer" className="text-truncate d-inline-block" style={{ maxWidth: 220 }}>{t.video_url}</a> },
  { key: 'approuve', label: 'État', render: (t) => <StatusBadge value={t.approuve ? 'publie' : 'brouillon'} /> },
]

export default function Temoignages() {
  return (
    <SimpleCrud title="Témoignages" base="/admin/contenu/temoignages" columns={COLUMNS} fields={FIELDS} module="temoignages"
                imageField={{ label: 'Miniature', previewKey: 'miniature' }} buttonLabel="Nouveau témoignage" />
  )
}
