// Détail d'un média — conversion de Home_page/Media_Detail_View.php : mêmes sections (lecteur selon le type :
// YouTube, vidéo, audio, image, document ; titre et statistiques ; j'aime / je n'aime pas / partager / télécharger ;
// description dépliable ; commentaires ; « À regarder ensuite »), à la manière de YouTube et aux couleurs du logo.
import { useEffect, useRef, useState } from 'react'
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom'
import api, { API_URL } from '../lib/api'
import { getUser, initials } from '../lib/auth'
import { formatDate } from '../lib/format'
import { ICON, docType, duration } from '../lib/media'
import { ROUTES } from '../lib/routes'
import { cleanText } from '../lib/sanitize'
import { usePageMeta } from '../lib/site'
import useApi from '../lib/useApi'
import { Thumb } from '../ui/MediaCard'
import '../styles/media.css'
import '../styles/media-detail.css'

const SPEEDS = [1, 1.25, 1.5, 2, 0.75]

function fileSize(bytes) {
  if (!bytes) return ''
  if (bytes >= 1073741824) return `${(bytes / 1073741824).toFixed(1)} Go`
  if (bytes >= 1048576) return `${(bytes / 1048576).toFixed(1)} Mo`
  return `${Math.max(1, Math.round(bytes / 1024))} Ko`
}

/* ───────────── lecteurs ───────────── */

function AudioPlayer({ m, src, next, onPrev }) {
  const ref = useRef(null)
  const [playing, setPlaying] = useState(false)
  const [time, setTime] = useState(0)
  const [total, setTotal] = useState(m.duree || 0)
  const [speed, setSpeed] = useState(0)
  const navigate = useNavigate()

  useEffect(() => {
    const a = ref.current
    a?.play().catch(() => { /* lecture automatique bloquée : la personne appuie sur lecture */ })
  }, [src])

  const toggle = () => { const a = ref.current; if (a.paused) a.play(); else a.pause() }
  const jump = (s) => { const a = ref.current; a.currentTime = Math.max(0, Math.min((a.duration || total), a.currentTime + s)) }
  const changeSpeed = () => { const i = (speed + 1) % SPEEDS.length; setSpeed(i); ref.current.playbackRate = SPEEDS[i] }

  return (
    <div className="mdd_audio">
      <div className="mdd_audio-cover">
        {m.miniature ? <img src={m.miniature} alt="" /> : (
          <div className={`md_disc mdd_disc ${playing ? 'spin' : ''}`}><img src="/logo.jpeg" alt="" /></div>
        )}
      </div>
      <div className="mdd_audio-body">
        <span className="mdd_audio-kind"><i className="bi bi-headphones"></i> Audio NUFOTEC</span>
        <h2>{m.titre}</h2>
        <p>{m.credits && m.credits !== 'Artiste inconnu' ? m.credits : 'NUFOTEC BURUNDI'}</p>
        <audio ref={ref} src={src} preload="metadata"
               onPlay={() => setPlaying(true)} onPause={() => setPlaying(false)}
               onTimeUpdate={(e) => setTime(e.currentTarget.currentTime)}
               onLoadedMetadata={(e) => Number.isFinite(e.currentTarget.duration) && setTotal(e.currentTarget.duration)}
               onEnded={() => { setPlaying(false); if (next) navigate(ROUTES.mediaItem(next.slug)) }}></audio>
        <input type="range" className="mdd_seek" min="0" max={Math.max(1, Math.floor(total))} step="1" value={Math.floor(time)}
               aria-label="Position de lecture" style={{ '--p': `${total ? (time / total) * 100 : 0}%` }}
               onChange={(e) => { ref.current.currentTime = Number(e.target.value); setTime(Number(e.target.value)) }} />
        <div className="mdd_times"><span>{duration(Math.floor(time)) || '0:00'}</span><span>{duration(Math.floor(total)) || '0:00'}</span></div>
        <div className="mdd_controls">
          <button type="button" onClick={onPrev} aria-label="Précédent"><i className="bi bi-skip-start-fill"></i></button>
          <button type="button" onClick={() => jump(-10)} aria-label="Reculer de 10 secondes"><i className="bi bi-arrow-counterclockwise"></i><small>10</small></button>
          <button type="button" className="mdd_play" onClick={toggle} aria-label={playing ? 'Pause' : 'Lecture'}>
            <i className={`bi ${playing ? 'bi-pause-fill' : 'bi-play-fill'}`}></i>
          </button>
          <button type="button" onClick={() => jump(10)} aria-label="Avancer de 10 secondes"><i className="bi bi-arrow-clockwise"></i><small>10</small></button>
          <button type="button" disabled={!next} onClick={() => next && navigate(ROUTES.mediaItem(next.slug))} aria-label="Suivant"><i className="bi bi-skip-end-fill"></i></button>
        </div>
        <button type="button" className="mdd_speed" onClick={changeSpeed} aria-label="Vitesse de lecture">{SPEEDS[speed]}×</button>
      </div>
    </div>
  )
}

function DocumentViewer({ m }) {
  const box = useRef(null)
  const [label, icon, tone] = docType(m.format)
  const preview = `${API_URL}/medias/${m.slug}/apercu`
  const full = () => (document.fullscreenElement ? document.exitFullscreen() : box.current?.requestFullscreen?.())
  return (
    <div className="mdd_doc" ref={box}>
      <div className="mdd_doc-bar">
        <i className={`bi ${icon} mdd_doc-icon md_doc-${tone}`}></i>
        <span className="mdd_doc-title">{m.titre}</span>
        <span className={`md_format md_format-${tone} mdd_doc-format`}>{label}</span>
        {m.taille ? <small>{fileSize(m.taille)}</small> : null}
        <div className="mdd_doc-actions">
          <button type="button" onClick={full} title="Plein écran" aria-label="Plein écran"><i className="bi bi-arrows-fullscreen"></i></button>
          {m.telechargeable && <a href={`${API_URL}/medias/${m.slug}/telecharger`} download title="Télécharger" aria-label="Télécharger"><i className="bi bi-download"></i></a>}
          <a href={preview} target="_blank" rel="noopener noreferrer" title="Ouvrir dans un nouvel onglet" aria-label="Ouvrir dans un nouvel onglet"><i className="bi bi-box-arrow-up-right"></i></a>
        </div>
      </div>
      {m.format === 'pdf' ? (
        <>
          <iframe className="mdd_doc-frame" src={`${preview}#toolbar=1&navpanes=0&view=FitH`} title={m.titre}></iframe>
          {/* téléphone : les navigateurs mobiles n'affichent pas les PDF dans la page */}
          <div className="mdd_doc-mobile">
            <div className="md_thumb"><Thumb m={{ ...m, miniature: null }} /></div>
            <a className="mdd_btn primary" href={preview} target="_blank" rel="noopener noreferrer"><i className="bi bi-book"></i> Lire le document</a>
          </div>
        </>
      ) : (
        <div className="mdd_doc-mobile show">
          <div className="md_thumb"><Thumb m={{ ...m, miniature: null }} /></div>
          <p>Ce document s'ouvre avec {label === 'WORD' ? 'Word' : label === 'EXCEL' ? 'Excel' : label === 'POWERPOINT' ? 'PowerPoint' : 'votre application'}.</p>
          {m.telechargeable && <a className="mdd_btn primary" href={`${API_URL}/medias/${m.slug}/telecharger`} download><i className="bi bi-download"></i> Télécharger</a>}
        </div>
      )}
    </div>
  )
}

function Player({ m, next, onPrev }) {
  // Fichier servi directement par nginx (rapide) ; repli sur l'API /flux si l'URL directe est absente
  const stream = m.flux_url || `${API_URL}/medias/${m.slug}/flux?qualite=${m.version_legere_disponible ? 'legere' : 'originale'}`
  if (m.youtube_id) {
    return (
      <div className="mdd_frame">
        <iframe src={`https://www.youtube-nocookie.com/embed/${m.youtube_id}?autoplay=1&rel=0&modestbranding=1`} title={m.titre}
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowFullScreen></iframe>
      </div>
    )
  }
  if (m.categorie === 'video' && m.lisible) {
    return <div className="mdd_frame"><video src={stream} poster={m.miniature ?? undefined} controls autoPlay playsInline></video></div>
  }
  if (m.categorie === 'audio' && m.lisible) return <AudioPlayer m={m} src={stream} next={next} onPrev={onPrev} />
  if (m.categorie === 'image' && m.apercu) {
    return <div className="mdd_image"><img src={`${API_URL}/medias/${m.slug}/apercu`} alt={m.titre} /></div>
  }
  if (m.categorie === 'fichier' && m.apercu) return <DocumentViewer m={m} />
  if (m.lien_externe) {
    return (
      <div className="mdd_frame mdd_link">
        <div className="md_thumb"><Thumb m={m} /></div>
        <a className="mdd_btn primary" href={m.lien_externe} target="_blank" rel="noopener noreferrer"><i className="bi bi-box-arrow-up-right"></i> Ouvrir le lien</a>
      </div>
    )
  }
  return <div className="mdd_frame"><div className="md_thumb"><Thumb m={m} /></div></div>
}

/* ───────────── commentaires ───────────── */

function Comments({ slug, count, onNeedAccount }) {
  const user = getUser()
  const [page, setPage] = useState(1)
  const list = useApi(`/medias/${slug}/commentaires`, { page, per_page: 20 })
  const [older, setOlder] = useState([])
  const items = [...older, ...(list.data?.items ?? [])]
  const [text, setText] = useState('')
  const [state, setState] = useState({ sending: false, msg: '', error: '' })

  const send = async (e) => {
    e.preventDefault()
    if (text.trim().length < 2) return
    setState({ sending: true, msg: '', error: '' })
    try {
      await api.post(`/medias/${slug}/commentaires`, { texte: text.trim() })
      setText('')
      setState({ sending: false, msg: 'Merci ! Votre commentaire sera publié après vérification par notre équipe.', error: '' })
    } catch (err) { setState({ sending: false, msg: '', error: err.message }) }
  }

  return (
    <section className="mdd_comments" aria-label="Commentaires">
      <h3><i className="bi bi-chat-dots"></i> {count} commentaire{count > 1 ? 's' : ''}</h3>
      {user ? (
        <form className="mdd_comment-form" onSubmit={send}>
          <div className="mdd_avatar">{initials(user)}</div>
          <div className="mdd_comment-input">
            <textarea rows={text ? 3 : 1} maxLength={2000} placeholder="Ajouter un commentaire…" value={text}
                      onChange={(e) => setText(cleanText(e.target.value, 2000))} aria-label="Votre commentaire"></textarea>
            {text && (
              <div className="mdd_comment-actions">
                <button type="button" className="mdd_btn ghost" onClick={() => setText('')}>Annuler</button>
                <button type="submit" className="mdd_btn primary" disabled={state.sending || text.trim().length < 2}>
                  {state.sending ? <span className="spinner-border spinner-border-sm"></span> : 'Commenter'}
                </button>
              </div>
            )}
            {state.msg && <small className="ok"><i className="bi bi-check-circle"></i> {state.msg}</small>}
            {state.error && <small className="err">{state.error}</small>}
          </div>
        </form>
      ) : (
        <p className="mdd_login"><i className="bi bi-info-circle"></i> <button type="button" onClick={onNeedAccount}>Connectez-vous</button> pour commenter.</p>
      )}
      {items.length === 0 && !list.loading ? (
        <div className="mdd_no-comments"><i className="bi bi-chat"></i><p>Aucun commentaire pour le moment</p></div>
      ) : (
        <ul className="mdd_comment-list">
          {items.map((c) => (
            <li key={c.id}>
              <div className="mdd_avatar">{(c.auteur || 'V').charAt(0).toUpperCase()}</div>
              <div><b>{c.auteur}</b> <small>{formatDate(c.le)}</small><p>{c.texte}</p></div>
            </li>
          ))}
        </ul>
      )}
      {list.data && list.data.meta.pages > page && (
        <button type="button" className="mdd_btn ghost mdd_more-comments" disabled={list.loading} onClick={() => { setOlder(items); setPage((p) => p + 1) }}>Voir plus de commentaires</button>
      )}
    </section>
  )
}

/* ───────────── page ───────────── */

export default function MediaDetail() {
  const { slug } = useParams()
  return <MediaView key={slug} slug={slug} />
}

function MediaView({ slug }) {
  const navigate = useNavigate()
  const location = useLocation()
  const media = useApi(`/medias/${slug}`)
  const m = media.data
  const [statsPatch, setStatsPatch] = useState(null)
  const [mePatch, setMePatch] = useState(null)
  const [open, setOpen] = useState(false)
  const [toast, setToast] = useState('')
  const me = { ...(m?.moi ?? {}), ...(mePatch ?? {}) }
  usePageMeta(m?.titre ?? 'Média')

  useEffect(() => { window.scrollTo({ top: 0 }) }, [])
  // une vue par visiteur toutes les 30 minutes (dédoublonnée par l'API)
  useEffect(() => { api.post(`/medias/${slug}/vue`).catch(() => {}) }, [slug])
  useEffect(() => {
    if (!toast) return undefined
    const t = setTimeout(() => setToast(''), 2800)
    return () => clearTimeout(t)
  }, [toast])

  const goLogin = () => navigate(`${ROUTES.login}?suite=${encodeURIComponent(location.pathname)}`)
  const react = async (kind) => {
    if (!getUser()) { goLogin(); return }
    const next = me.reaction === kind ? null : kind
    try {
      const s = await api.post(`/medias/${slug}/reaction`, { reaction: next })
      setStatsPatch(s)
      setMePatch((old) => ({ ...(old ?? {}), reaction: next }))
    } catch (err) { setToast(err.message) }
  }
  const favorite = async () => {
    if (!getUser()) { goLogin(); return }
    try {
      if (me.favori) await api.delete(`/medias/${slug}/favori`); else await api.post(`/medias/${slug}/favori`)
      setToast(me.favori ? 'Retiré des favoris' : 'Ajouté aux favoris')
      setMePatch((old) => ({ ...(old ?? {}), favori: !me.favori }))
    } catch (err) { setToast(err.message) }
  }
  const share = async () => {
    const url = window.location.href
    if (navigator.share) { try { await navigator.share({ title: m.titre, url }) } catch { /* annulé */ } return }
    try { await navigator.clipboard.writeText(url); setToast('Lien copié') } catch { setToast(url) }
  }

  if (media.loading && !m) {
    return <div className="md mdd"><div className="mdd_wrap"><div className="mdd_main"><div className="mdd_frame mdd_skeleton"></div><div className="mdd_skel-line"></div><div className="mdd_skel-line short"></div></div></div></div>
  }
  if (media.error || !m) {
    return (
      <div className="md mdd"><div className="mdd_wrap"><div className="md_empty mdd_missing">
        <i className="bi bi-camera-video-off"></i>
        <h3>{media.error?.status === 404 ? 'Média introuvable' : 'Erreur de chargement'}</h3>
        <p>{media.error?.status === 404 ? "Ce média n'existe plus ou a été retiré." : media.error?.message}</p>
        <Link className="md_btn" to={ROUTES.media}>Retour à la médiathèque</Link>
      </div></div></div>
    )
  }

  const s = { ...m.stats, ...(statsPatch ?? {}) }
  const recos = m.recommandations ?? []
  const desc = (m.description || '').trim()
  const date = m.date || m.created_at
  const longDesc = desc.length > 180 || desc.split('\n').length > 3

  return (
    <div className="md mdd">
      <div className="mdd_wrap">
        <div className="mdd_main">
          {/* 1. LECTEUR */}
          <Player m={m} next={recos[0]} onPrev={() => navigate(-1)} />

          {/* 2. TITRE, STATISTIQUES ET ACTIONS */}
          <h1 className="mdd_title">{m.titre}</h1>
          <div className="mdd_bar">
            <div className="mdd_channel">
              <img src="/logo.jpeg" alt="" />
              <div><b>NUFOTEC BURUNDI</b><small><i className={`bi ${ICON[m.categorie] ?? 'bi-play'}`}></i> {m.credits && m.credits !== 'Artiste inconnu' ? m.credits : 'Médiathèque'}</small></div>
            </div>
            <div className="mdd_actions">
              <div className="mdd_pill">
                <button type="button" className={me.reaction === 'like' ? 'on' : ''} onClick={() => react('like')} aria-pressed={me.reaction === 'like'} aria-label="J'aime">
                  <i className={`bi ${me.reaction === 'like' ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up'}`}></i> {s.likes ?? 0}
                </button>
                <button type="button" className={me.reaction === 'dislike' ? 'on' : ''} onClick={() => react('dislike')} aria-pressed={me.reaction === 'dislike'} aria-label="Je n'aime pas">
                  <i className={`bi ${me.reaction === 'dislike' ? 'bi-hand-thumbs-down-fill' : 'bi-hand-thumbs-down'}`}></i>
                </button>
              </div>
              <button type="button" className="mdd_pill-btn" onClick={share}><i className="bi bi-share"></i> Partager</button>
              <a className="mdd_pill-btn wa" href={`https://wa.me/?text=${encodeURIComponent(`${m.titre} — ${window.location.href}`)}`} target="_blank" rel="noopener noreferrer"><i className="bi bi-whatsapp"></i> WhatsApp</a>
              {m.telechargeable && <a className="mdd_pill-btn" href={`${API_URL}/medias/${m.slug}/telecharger`} download><i className="bi bi-download"></i> Télécharger</a>}
              <button type="button" className={`mdd_pill-btn ${me.favori ? 'on' : ''}`} onClick={favorite}><i className={`bi ${me.favori ? 'bi-bookmark-check-fill' : 'bi-bookmark-plus'}`}></i> {me.favori ? 'Enregistré' : 'Enregistrer'}</button>
            </div>
          </div>

          {/* 3. DESCRIPTION */}
          <div className={`mdd_desc ${open ? 'open' : ''}`}>
            <p className="mdd_desc-meta">
              <b>{s.vues ?? 0} vue{s.vues > 1 ? 's' : ''}</b>{date && <b> · {formatDate(date)}</b>}
              {m.duree ? <span> · {duration(m.duree)}</span> : null}
              {m.format ? <span> · {m.format.toUpperCase()}</span> : null}{m.taille ? <span> · {fileSize(m.taille)}</span> : null}
              {m.telechargements > 0 && <span> · {m.telechargements} téléchargement{m.telechargements > 1 ? 's' : ''}</span>}
            </p>
            <div className="mdd_desc-text">{desc && desc.toLowerCase() !== m.titre.toLowerCase() ? desc : 'Aucune description.'}</div>
            {m.collections?.length > 0 && <p className="mdd_desc-coll"><i className="bi bi-collection-play"></i> Série : {m.collections.map((c) => c.titre).join(', ')}</p>}
            {longDesc && <button type="button" onClick={() => setOpen((o) => !o)}>{open ? 'Afficher moins' : 'Afficher plus'}</button>}
          </div>

          {/* 4. COMMENTAIRES */}
          <Comments slug={m.slug} count={s.commentaires ?? 0} onNeedAccount={goLogin} />
        </div>

        {/* 5. À REGARDER ENSUITE */}
        <aside className="mdd_side" aria-label="À regarder ensuite">
          <h3><i className="bi bi-collection-play"></i> {m.categorie === 'audio' ? 'À écouter ensuite' : m.categorie === 'fichier' ? 'À lire ensuite' : 'À regarder ensuite'}</h3>
          {recos.length === 0 ? <p className="mdd_none">Aucune suggestion</p> : (
            <ul>
              {recos.map((r) => (
                <li key={r.uuid}>
                  <Link to={ROUTES.mediaItem(r.slug)} className="mdd_rel">
                    <div className="md_thumb">
                      <Thumb m={r} />
                      {duration(r.duree) && <span className="md_duration">{duration(r.duree)}</span>}
                    </div>
                    <div>
                      <b title={r.titre}>{r.titre}</b>
                      <small>NUFOTEC BURUNDI</small>
                      <small><i className="bi bi-eye"></i> {r.stats?.vues ?? 0} vue{r.stats?.vues > 1 ? 's' : ''}</small>
                    </div>
                  </Link>
                </li>
              ))}
            </ul>
          )}
          <Link to={ROUTES.media} className="mdd_back"><i className="bi bi-grid"></i> Toute la médiathèque</Link>
        </aside>
      </div>
      {toast && <div className="md_toast" role="status">{toast}</div>}
    </div>
  )
}
