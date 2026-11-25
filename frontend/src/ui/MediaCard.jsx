// Cartes de la médiathèque (liste Médias et « À regarder ensuite » du détail) : couverture par défaut selon le type,
// durée, menu « ⋮ » (télécharger, j'aime, favoris, partager).
import { useEffect, useRef, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import api, { API_URL } from '../lib/api'
import { getUser } from '../lib/auth'
import { ICON, docType, duration } from '../lib/media'
import { ROUTES } from '../lib/routes'

/** Miniature : l'image du média, sinon une couverture par défaut aux couleurs NUFOTEC selon le type */
export function Thumb({ m }) {
  if (m.miniature) {
    return (
      <>
        <img src={m.miniature} alt="" loading="lazy" />
        {m.categorie === 'fichier' && <span className={`md_format md_format-${docType(m.format)[2]}`}>{docType(m.format)[0]}</span>}
      </>
    )
  }
  if (m.categorie === 'audio') {
    return (
      <div className="md_ph md_ph-audio">
        <div className="md_disc"><img loading="lazy" decoding="async" src="/logo.jpeg" alt="" /></div>
        <div className="md_wave" aria-hidden="true">{Array.from({ length: 9 }, (_, i) => <i key={i}></i>)}</div>
        <span className="md_ph-label"><i className="bi bi-headphones"></i> AUDIO</span>
      </div>
    )
  }
  if (m.categorie === 'fichier') {
    const [label, icon, tone] = docType(m.format)
    return (
      <div className={`md_ph md_ph-doc md_doc-${tone}`}>
        <div className="md_sheet"><i className={`bi ${icon}`}></i><b>{label}</b></div>
        <img loading="lazy" decoding="async" className="md_ph-logo" src="/logo.jpeg" alt="" />
      </div>
    )
  }
  if (m.categorie === 'video') {
    return (
      <div className="md_ph md_ph-video">
        <img loading="lazy" decoding="async" className="md_ph-mark" src="/logo.jpeg" alt="" />
        <i className="bi bi-play-circle-fill md_ph-big"></i>
        <span className="md_ph-label"><i className="bi bi-camera-video"></i> VIDÉO</span>
      </div>
    )
  }
  return (
    <div className="md_ph md_ph-video">
      <img loading="lazy" decoding="async" className="md_ph-mark" src="/logo.jpeg" alt="" />
      <i className={`bi ${ICON[m.categorie] ?? 'bi-link-45deg'} md_ph-big`}></i>
      <span className="md_ph-label">{m.categorie === 'image' ? 'IMAGE' : 'LIEN'}</span>
    </div>
  )
}

/** Menu « ⋮ » d'une carte : télécharger, ouvrir le lien, j'aime, favoris, partager */
export function CardMenu({ m, notify }) {
  const navigate = useNavigate()
  const [open, setOpen] = useState(false)
  const [liked, setLiked] = useState(false)
  const [fav, setFav] = useState(false)
  const ref = useRef(null)
  const url = `${window.location.origin}${ROUTES.mediaItem(m.slug)}`

  useEffect(() => {
    if (!open) return undefined
    const close = (e) => { if (!ref.current?.contains(e.target)) setOpen(false) }
    const esc = (e) => { if (e.key === 'Escape') setOpen(false) }
    document.addEventListener('mousedown', close)
    document.addEventListener('keydown', esc)
    return () => { document.removeEventListener('mousedown', close); document.removeEventListener('keydown', esc) }
  }, [open])

  // « J'aime » et favoris : un par compte (règle de l'API) ; sans compte, on passe par la connexion puis on revient ici
  const needAccount = () => {
    if (getUser()) return false
    navigate(`${ROUTES.login}?suite=${encodeURIComponent(window.location.pathname + window.location.search)}`)
    return true
  }
  const like = async () => {
    setOpen(false)
    if (needAccount()) return
    try {
      await api.post(`/medias/${m.slug}/reaction`, { reaction: liked ? null : 'like' })
      setLiked(!liked)
      notify(liked ? 'J’aime retiré' : 'Merci pour votre « J’aime » !')
    } catch (err) { notify(err.message) }
  }
  const favorite = async () => {
    setOpen(false)
    if (needAccount()) return
    try {
      if (fav) await api.delete(`/medias/${m.slug}/favori`); else await api.post(`/medias/${m.slug}/favori`)
      setFav(!fav)
      notify(fav ? 'Retiré des favoris' : 'Ajouté aux favoris')
    } catch (err) { notify(err.message) }
  }
  const copy = async () => {
    setOpen(false)
    try { await navigator.clipboard.writeText(url); notify('Lien copié') } catch { notify(url) }
  }
  const share = async () => {
    setOpen(false)
    if (navigator.share) { try { await navigator.share({ title: m.titre, url }) } catch { /* partage annulé */ } } else copy()
  }

  return (
    <div className="md_more" ref={ref}>
      <button type="button" className="md_more-btn" aria-label={`Plus d’actions pour ${m.titre}`} aria-haspopup="menu" aria-expanded={open}
              onClick={() => setOpen((o) => !o)}><i className="bi bi-three-dots-vertical"></i></button>
      {open && (
        <div className="md_menu-pop" role="menu">
          {m.telechargeable && (
            <a role="menuitem" href={`${API_URL}/medias/${m.slug}/telecharger`} download onClick={() => setOpen(false)}>
              <i className="bi bi-download"></i> Télécharger{m.format ? ` (${m.format.toUpperCase()})` : ''}
            </a>
          )}
          {m.lien_externe && (
            <a role="menuitem" href={m.lien_externe} target="_blank" rel="noopener noreferrer" onClick={() => setOpen(false)}>
              <i className="bi bi-youtube"></i> Ouvrir sur YouTube
            </a>
          )}
          <button type="button" role="menuitem" onClick={like}><i className={`bi ${liked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up'}`}></i> {liked ? 'Je n’aime plus' : 'J’aime'}</button>
          <button type="button" role="menuitem" onClick={favorite}><i className={`bi ${fav ? 'bi-bookmark-check-fill' : 'bi-bookmark-plus'}`}></i> {fav ? 'Retirer des favoris' : 'Ajouter aux favoris'}</button>
          <a role="menuitem" href={`https://wa.me/?text=${encodeURIComponent(`${m.titre} — ${url}`)}`} target="_blank" rel="noopener noreferrer" onClick={() => setOpen(false)}>
            <i className="bi bi-whatsapp"></i> Partager sur WhatsApp
          </a>
          <button type="button" role="menuitem" onClick={share}><i className="bi bi-share"></i> Partager…</button>
          <button type="button" role="menuitem" onClick={copy}><i className="bi bi-link-45deg"></i> Copier le lien</button>
        </div>
      )}
    </div>
  )
}

export default function MediaCard({ m, notify }) {
  const icon = ICON[m.categorie] ?? 'bi-play-circle'
  return (
    <div className="md_item">
      <Link to={ROUTES.mediaItem(m.slug)} className="md_card">
        <div className="md_thumb">
          <Thumb m={m} />
          <span className="md_play"><i className={`bi ${m.categorie === 'fichier' ? 'bi-book-half' : m.categorie === 'image' ? 'bi-zoom-in' : 'bi-play-fill'}`}></i></span>
          {duration(m.duree) && <span className="md_duration">{duration(m.duree)}</span>}
          <span className="md_type"><i className={`bi ${icon}`}></i></span>
        </div>
        <div className="md_info">
          <h3 title={m.titre}>{m.titre}</h3>
          <p><span>{m.credits || 'NUFOTEC BURUNDI'}</span><b className="md_sep"> · </b><span><i className="bi bi-eye"></i> {m.stats?.vues ?? 0} vue{m.stats?.vues > 1 ? 's' : ''}</span></p>
        </div>
      </Link>
      <CardMenu m={m} notify={notify} />
    </div>
  )
}

export function CardSkeleton() {
  return <div className="md_card md_skeleton" aria-hidden="true"><div className="md_thumb"></div><div className="md_info"><span></span><span></span></div></div>
}
