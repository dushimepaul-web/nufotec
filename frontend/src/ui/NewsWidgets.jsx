// Encadrés de la colonne Actualités (liste et article) : les plus lus, newsletter, « Suivez-nous », partager.
import { useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../lib/api'
import { formatDate } from '../lib/format'
import { ROUTES } from '../lib/routes'
import useApi from '../lib/useApi'

export function NewsThumb({ src, alt = '' }) {
  const [broken, setBroken] = useState(false)
  if (!src || broken) return <div className="nw_thumb-ph"><img loading="lazy" decoding="async" src="/logo.jpeg" alt="" /></div>
  return <img src={src} alt={alt} loading="lazy" onError={() => setBroken(true)} />
}

export function PopularWidget({ items, title = 'Les plus lus' }) {
  if (!items?.length) return null
  return (
    <div className="nw_widget">
      <h3><i className="bi bi-fire"></i> {title}</h3>
      <ul className="nw_popular">
        {items.map((p, i) => (
          <li key={p.slug}>
            <Link to={ROUTES.article(p.slug)}>
              <span className="nw_rank">{i + 1}</span>
              <div className="nw_popular-thumb"><NewsThumb src={p.image} /></div>
              <div><b>{p.titre}</b><small><i className="bi bi-eye"></i> {p.vues} vue{p.vues > 1 ? 's' : ''} · {formatDate(p.date_publication)}</small></div>
            </Link>
          </li>
        ))}
      </ul>
    </div>
  )
}

export function NewsletterWidget() {
  const [email, setEmail] = useState('')
  const [state, setState] = useState({ sending: false, msg: '', error: '' })
  const submit = async (e) => {
    e.preventDefault()
    if (!/^[^@\s]+@[^@\s]+\.[a-z]{2,}$/i.test(email.trim())) { setState({ sending: false, msg: '', error: 'Adresse email invalide' }); return }
    setState({ sending: true, msg: '', error: '' })
    try {
      const res = await api.postWithMessage('/newsletter/inscription', { email: email.trim().toLowerCase() })
      setEmail('')
      setState({ sending: false, msg: res?.message || 'Vérifiez votre boîte email pour confirmer votre inscription.', error: '' })
    } catch (err) { setState({ sending: false, msg: '', error: err.message }) }
  }
  return (
    <div className="nw_widget nw_newsletter">
      <div className="nw_newsletter-ico"><i className="bi bi-envelope-open-heart"></i></div>
      <h3><i className="bi bi-send"></i> Newsletter</h3>
      <p>Recevez nos actualités et nos innovations. Gratuit, désinscription en un clic.</p>
      {state.msg ? <p className="nw_ok"><i className="bi bi-check-circle"></i> {state.msg}</p> : (
        <form onSubmit={submit}>
          <div className="nw_input"><i className="bi bi-envelope"></i>
            <input type="email" inputMode="email" value={email} maxLength={150} placeholder="Votre adresse email" aria-label="Votre adresse email"
                   onChange={(e) => setEmail(e.target.value.replace(/[^\w.@+-]/g, ''))} /></div>
          {state.error && <small className="nw_err">{state.error}</small>}
          <button type="submit" className="nw_btn" disabled={state.sending}>
            {state.sending ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-bell"></i>} Je m'inscris
          </button>
        </form>
      )}
    </div>
  )
}

export function SocialWidget() {
  const { data } = useApi('/reseaux-sociaux')
  if (!data?.length) return null
  return (
    <div className="nw_widget">
      <h3><i className="bi bi-people"></i> Suivez-nous</h3>
      <div className="nw_social">
        {data.map((s) => (
          <a key={s.plateforme} href={s.url} target="_blank" rel="noopener noreferrer" className={`nw_social-${s.plateforme}`}>
            <span className="nw_social-ico"><i className={`bi bi-${s.icone}`}></i></span>
            <span className="nw_social-txt"><b>{s.libelle}</b>{s.abonnes && <small><i className="bi bi-people-fill"></i> {s.abonnes} abonnés</small>}</span>
            <i className="bi bi-arrow-up-right"></i>
          </a>
        ))}
      </div>
    </div>
  )
}

export function ShareWidget({ title }) {
  const url = typeof window !== 'undefined' ? window.location.href : ''
  const u = encodeURIComponent(url)
  const [copied, setCopied] = useState(false)
  const copy = async () => { try { await navigator.clipboard.writeText(url); setCopied(true); setTimeout(() => setCopied(false), 2000) } catch { /* rien */ } }
  return (
    <div className="nw_widget nw_share-widget">
      <h3><i className="bi bi-share"></i> Partager</h3>
      <div className="nw_share">
        <a className="wa" href={`https://wa.me/?text=${encodeURIComponent(`${title} — ${url}`)}`} target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><i className="bi bi-whatsapp"></i></a>
        <a className="fb" href={`https://www.facebook.com/sharer/sharer.php?u=${u}`} target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i className="bi bi-facebook"></i></a>
        <a className="x" href={`https://twitter.com/intent/tweet?url=${u}&text=${encodeURIComponent(title)}`} target="_blank" rel="noopener noreferrer" aria-label="X (Twitter)"><i className="bi bi-twitter-x"></i></a>
        <a className="in" href={`https://www.linkedin.com/sharing/share-offsite/?url=${u}`} target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i className="bi bi-linkedin"></i></a>
        <button type="button" onClick={copy} aria-label="Copier le lien"><i className={`bi ${copied ? 'bi-check2' : 'bi-link-45deg'}`}></i></button>
      </div>
      {copied && <small className="nw_ok">Lien copié</small>}
    </div>
  )
}
