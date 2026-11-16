// Médiathèque : catégories, durée lisible, types de documents (partagés par les pages Médias et Détail)

// mêmes catégories que la vue PHP (Vidéos, Audio, Images, Livres) ; les liens n'apparaissent que s'il y en a
export const CATEGORIES = [
  ['video', 'bi-camera-video', 'Vidéos'],
  ['audio', 'bi-music-note-beamed', 'Audio'],
  ['image', 'bi-image', 'Images'],
  ['fichier', 'bi-book', 'Livres et documents'],
  ['link', 'bi-link-45deg', 'Liens'],
]
export const ICON = Object.fromEntries(CATEGORIES.map(([k, icon]) => [k, icon]))

export function duration(seconds) {
  if (!seconds) return ''
  const h = Math.floor(seconds / 3600)
  const m = Math.floor((seconds % 3600) / 60)
  const s = String(seconds % 60).padStart(2, '0')
  return h ? `${h}:${String(m).padStart(2, '0')}:${s}` : `${m}:${s}`
}

// Documents : icône, couleur et libellé selon le type de fichier
export const DOC_TYPES = {
  pdf: ['PDF', 'bi-file-earmark-pdf-fill', 'pdf'],
  doc: ['WORD', 'bi-file-earmark-word-fill', 'word'], docx: ['WORD', 'bi-file-earmark-word-fill', 'word'],
  xls: ['EXCEL', 'bi-file-earmark-excel-fill', 'excel'], xlsx: ['EXCEL', 'bi-file-earmark-excel-fill', 'excel'],
  ppt: ['POWERPOINT', 'bi-file-earmark-ppt-fill', 'ppt'], pptx: ['POWERPOINT', 'bi-file-earmark-ppt-fill', 'ppt'],
  txt: ['TEXTE', 'bi-file-earmark-text-fill', 'text'], epub: ['LIVRE', 'bi-book-fill', 'book'],
}
export const docType = (format) => DOC_TYPES[format] ?? ['DOCUMENT', 'bi-file-earmark-fill', 'text']
