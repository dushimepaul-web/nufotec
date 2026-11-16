// Remplissage automatique des formulaires de la médiathèque : titre tiré du nom du fichier (ou des
// informations intégrées d'un MP3), date du jour, crédits par défaut, titre d'une vidéo YouTube.

export const DEFAULT_CREDITS = 'NUFOTEC BURUNDI'

/** Date du jour au format des champs <input type="date"> (heure locale, pas UTC). */
export function todayISO() {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

/** « 20260823170834_cancer_y'ibere (1).mp3 » -> « Cancer y'ibere » */
export function titleFromFileName(name) {
  let t = (name || '').replace(/\.[a-z0-9]{1,5}$/i, '')                 // extension
  t = t.replace(/^\d{8,14}[_-]+(?:[0-9a-f]{6,20}[_-]+)?/i, '')           // horodatage ajouté par un logiciel
  t = t.replace(/[_]+/g, ' ').replace(/\s*[-.]{2,}\s*/g, ' - ').replace(/(?<=\S)\.(?=\S)/g, ' ')
  t = t.replace(/\s*\((?:\d+|copie|copy)\)\s*$/i, '').replace(/\s+-\s*(?:copie|copy)$/i, '')
  t = t.replace(/\s{2,}/g, ' ').trim()
  // reste seulement un code (chiffres, identifiant hexadécimal) : pas un vrai titre, on laisse l'admin l'écrire
  if (!t || /^[0-9a-f\s_-]+$/i.test(t)) return ''
  return t.charAt(0).toUpperCase() + t.slice(1)
}

function decodeText(bytes, encoding) {
  try {
    if (encoding === 1 || encoding === 2) {
      const le = encoding === 1 && !(bytes[0] === 0xfe && bytes[1] === 0xff)
      return new TextDecoder(le ? 'utf-16le' : 'utf-16be').decode(bytes)
    }
    return new TextDecoder(encoding === 3 ? 'utf-8' : 'latin1').decode(bytes)
  } catch {
    return ''
  }
}

/** Titre et artiste intégrés à un MP3 (étiquettes ID3v2.3 / 2.4), sinon {}. Lit seulement le début du fichier. */
export async function readMp3Tags(file) {
  if (!file || !/\.mp3$/i.test(file.name)) return {}
  const head = new Uint8Array(await file.slice(0, 10).arrayBuffer())
  if (head[0] !== 0x49 || head[1] !== 0x44 || head[2] !== 0x33) return {}            // « ID3 »
  const version = head[3]
  const size = ((head[6] & 0x7f) << 21) | ((head[7] & 0x7f) << 14) | ((head[8] & 0x7f) << 7) | (head[9] & 0x7f)
  const tag = new Uint8Array(await file.slice(10, 10 + Math.min(size, 512 * 1024)).arrayBuffer())
  const out = {}
  let i = 0
  while (i + 10 <= tag.length) {
    const id = String.fromCharCode(tag[i], tag[i + 1], tag[i + 2], tag[i + 3])
    if (!/^[A-Z0-9]{4}$/.test(id)) break
    const len = version === 4
      ? ((tag[i + 4] & 0x7f) << 21) | ((tag[i + 5] & 0x7f) << 14) | ((tag[i + 6] & 0x7f) << 7) | (tag[i + 7] & 0x7f)
      : (tag[i + 4] << 24) | (tag[i + 5] << 16) | (tag[i + 6] << 8) | tag[i + 7]
    if (len <= 0 || i + 10 + len > tag.length) break
    if (id === 'TIT2' || id === 'TPE1') {
      const body = tag.subarray(i + 10, i + 10 + len)
      const text = decodeText(body.subarray(1), body[0]).split(String.fromCharCode(0)).join('').trim()
      if (text) out[id === 'TIT2' ? 'title' : 'artist'] = text
    }
    i += 10 + len
  }
  return out
}

/** Titre et chaîne d'une vidéo YouTube (oEmbed public), sinon null. */
export async function youtubeInfo(url) {
  if (!/(?:youtube\.com\/(?:watch|shorts|embed)|youtu\.be\/)/i.test(url || '')) return null
  try {
    const res = await fetch(`https://www.youtube.com/oembed?format=json&url=${encodeURIComponent(url.trim())}`)
    if (!res.ok) return null
    const data = await res.json()
    const tidy = (v) => (v || '').replace(/\s+/g, ' ').trim()
    return { title: tidy(data.title), author: tidy(data.author_name) }
  } catch {
    return null
  }
}
