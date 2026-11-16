// Pages statiques (Investissement, Corporate) : copies à l'identique des vues PHP de src/modules/pages, générées par
// scripts/convert_static_pages.py dans src/static-pages. Chaque page est chargée à la demande. Les liens internes
// restent dans l'application (pas de rechargement). Adresse inconnue : page « en préparation ».
import AOS from 'aos'
import { useEffect, useRef, useState } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import { usePageMeta } from '../lib/site'
import { STATIC_PAGES } from '../static-pages'
import PageBanner from '../ui/PageBanner'
import ComingSoon from './ComingSoon'
import '../styles/static-page.css'

export default function StaticPage() {
  const { pathname } = useLocation()
  const entry = STATIC_PAGES[pathname.replace(/\/+$/, '') || '/']
  if (!entry) return <ComingSoon />
  return <StaticContent key={entry.key} entry={entry} />
}

function StaticContent({ entry }) {
  const navigate = useNavigate()
  const box = useRef(null)
  const [page, setPage] = useState(null)
  const [failed, setFailed] = useState(false)
  usePageMeta(entry.title)

  useEffect(() => {
    let alive = true
    entry.load().then((mod) => alive && setPage(mod)).catch(() => alive && setFailed(true))
    window.scrollTo({ top: 0 })
    return () => { alive = false }
  }, [entry])

  useEffect(() => { if (page) AOS.refreshHard() }, [page])

  // liens internes (/courtier, /contact…) : navigation dans l'application, sans recharger la page
  const onClick = (e) => {
    const a = e.target.closest('a')
    if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank') return
    const href = a.getAttribute('href') || ''
    if (href.startsWith('/') && !href.startsWith('//')) {
      e.preventDefault()
      navigate(href)
    }
  }

  if (failed) return <ComingSoon title={entry.title} />
  if (!page) return <div className="container py-5 text-center" style={{ minHeight: '60vh' }}><span className="spinner-border text-success"></span></div>
  const b = page.banner ?? {}
  return (
    <div className="sp">
      {/* bandeau du site (logo sous voile vert), à la place de l'en-tête PHP */}
      <PageBanner crumbs={[['Corporate'], [b.crumb || entry.title]]} title={b.title || entry.title} subtitle={b.subtitle || undefined}
                  badge={b.badge ? <>{b.badgeIcon && <i className={`bi ${b.badgeIcon}`}></i>} {b.badge}</> : undefined} />
      {/* contenu fixe écrit dans le code (copie des vues PHP), aucune donnée saisie par un visiteur */}
      <div ref={box} className="static-page" onClick={onClick} dangerouslySetInnerHTML={{ __html: page.html }}></div>
    </div>
  )
}
