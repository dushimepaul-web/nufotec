// En-tête du site public — conversion de includes/frontend/Header.php (mêmes classes, même comportement)
import { useEffect, useRef, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import api from '../lib/api'
import { clearSession, getUser, initials } from '../lib/auth'
import { PAGE, ROUTES } from '../lib/routes'
import { STATIC_PAGES } from '../static-pages'
import { useSite } from '../lib/site'
import { ALL_LANGUAGES, MAIN_LANGUAGES, OTHER_LANGUAGES, flagUrl, getActiveLang, initGoogleTranslate, setLanguage } from '../lib/translate'
import '../styles/header.css'

const FALLBACK_LOGO = '/logo.jpeg'

const ABOUT_MENU = [
  { icon: 'bi-building', title: 'Entreprise', links: [
    [PAGE.strategic, 'Contexte stratégique'], [PAGE.governance, 'Gouvernance'], [PAGE.vision, 'Vision & Mission']] },
  { icon: 'bi-leaf', title: 'Durabilité', links: [
    [PAGE.esg, 'ESG & Durabilité'], [PAGE.risk, 'Analyse des risques'], [PAGE.innovation, 'Innovation'],
    [PAGE.market, 'Perspectives marché']] },
  { icon: 'bi-gear-wide-connected', title: 'Installations', links: [
    [PAGE.facility, 'Unité PhytoMed'], [PAGE.digital, 'Croissance digitale']] },
]

const INVEST_MENU = [
  { icon: 'bi-handshake', title: 'Partenariats', links: [
    [PAGE.projection, 'Projections investissement'], [PAGE.commitment, 'Engagement investisseur'],
    [PAGE.partnerships, 'Partenariats stratégiques']] },
  { icon: 'bi-bank', title: 'Relations', links: [
    [PAGE.commission, 'Commission courtier'], [ROUTES.broker, 'Devenir Broker'], [ROUTES.investor, 'Devenir Investisseur']] },
]

const MOBILE_SUBS = {
  'mob-about': [[PAGE.vision, 'Vision & Mission'], [PAGE.governance, 'Gouvernance'], [PAGE.esg, 'Durabilité ESG']],
  'mob-invest': [[PAGE.projection, 'Projections'], [PAGE.partnerships, 'Partenariats'], [PAGE.commission, 'Commission courtier'],
    [ROUTES.broker, 'Devenir Broker'], [ROUTES.investor, 'Devenir Investisseur']],
}

const SEARCH_GROUPS = [
  ['produits', 'Produits', 'bi-box-seam', (slug) => ROUTES.product(slug)],
  ['actualites', 'Actualités', 'bi-newspaper', (slug) => ROUTES.article(slug)],
  ['pages', 'Pages', 'bi-file-text', (slug) => ROUTES.page(slug)],
]

function MegaMenu({ label, columns }) {
  return (
    <li className="nuf-menu-item nuf-mega">
      <a href="#" className="nuf-menu-link" onClick={(e) => e.preventDefault()}>
        {label}
        <i className="bi bi-chevron-down"></i>
      </a>
      <div className="nuf-mega-drop">
        <div className="nuf-mega-grid">
          {columns.map((col) => (
            <div className="nuf-mega-col" key={col.title}>
              <h3><i className={`bi ${col.icon}`}></i> {col.title}</h3>
              <ul className="nuf-mega-list">
                {col.links.map(([to, text]) => (
                  <li key={to}><Link to={to}><i className="bi bi-chevron-right"></i> {text}</Link></li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </div>
    </li>
  )
}

export default function Header() {
  const { settings } = useSite()
  const location = useLocation()
  const user = getUser()
  const lang = getActiveLang()
  const currentLang = ALL_LANGUAGES.find(([code]) => code === lang) ?? ALL_LANGUAGES[0]

  const [scrolled, setScrolled] = useState(false)
  const [hideUp, setHideUp] = useState(false)
  const [panelOpen, setPanelOpen] = useState(false)
  const [openSub, setOpenSub] = useState(null)
  const [langOpen, setLangOpen] = useState(false)
  const [searchShow, setSearchShow] = useState(false)
  const [query, setQuery] = useState('')
  const [results, setResults] = useState(null) // null = fermé, 'loading', 'error' ou les résultats
  const searchRef = useRef(null)
  const langRef = useRef(null)
  const subRefs = useRef({})

  useEffect(() => { initGoogleTranslate() }, [])

  // Défilement : header compact, barre du haut masquée, header caché en descendant
  useEffect(() => {
    let lastY = 0
    const onScroll = () => {
      const y = window.pageYOffset
      setScrolled(y > 80)
      setHideUp(y > 80 && y > lastY && y > 200)
      lastY = y
    }
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  // Changement de page : on ferme menus et recherche
  useEffect(() => {
    setPanelOpen(false)
    setLangOpen(false)
    setSearchShow(false)
    setResults(null)
    setQuery('')
  }, [location.pathname])

  useEffect(() => {
    document.body.style.overflow = panelOpen ? 'hidden' : ''
  }, [panelOpen])

  // Clic à l'extérieur : fermer la recherche et la liste des langues
  useEffect(() => {
    const onClick = (e) => {
      if (searchRef.current && !searchRef.current.contains(e.target) && !e.target.closest('#nufSearchToggle')) {
        setSearchShow(false)
        setResults(null)
      }
      if (langRef.current && !langRef.current.contains(e.target)) setLangOpen(false)
    }
    document.addEventListener('click', onClick)
    return () => document.removeEventListener('click', onClick)
  }, [])

  // Recherche avec un petit délai pendant la frappe (GET /recherche)
  useEffect(() => {
    const q = query.trim()
    if (q.length < 2) { setResults(null); return undefined }
    const timer = setTimeout(() => {
      setResults('loading')
      // pages : seulement celles que le site affiche (les anciennes pages modèle de l'administration restent cachées)
      api.get('/recherche', { q })
        .then((r) => setResults({ ...r, pages: (r?.pages ?? []).filter((pg) => STATIC_PAGES[ROUTES.page(pg.slug)]) }))
        .catch(() => setResults('error'))
    }, 280)
    return () => clearTimeout(timer)
  }, [query])

  const toggleSub = (id) => setOpenSub((cur) => (cur === id ? null : id))
  const subStyle = (id) => ({ maxHeight: openSub === id ? `${subRefs.current[id]?.scrollHeight ?? 0}px` : '0' })
  const chooseLang = (code) => { setLangOpen(false); setPanelOpen(false); setLanguage(code) }

  const logo = settings.site_logo || FALLBACK_LOGO
  const accountLink = user ? ROUTES.account : ROUTES.login

  const renderMobileSub = (id, icon, label) => (
    <li>
      <button type="button" className={`nuf-panel-link${openSub === id ? ' open' : ''}`} onClick={() => toggleSub(id)}>
        <i className={`bi ${icon}`}></i><span>{label}</span><i className="bi bi-chevron-right ch"></i>
      </button>
      <div className={`nuf-sub${openSub === id ? ' open' : ''}`} ref={(el) => { subRefs.current[id] = el }} style={subStyle(id)}>
        {MOBILE_SUBS[id].map(([to, text]) => <Link key={to} to={to} className="nuf-sub-item">{text}</Link>)}
      </div>
    </li>
  )

  const renderResults = () => {
    if (results === 'loading') return <div style={{ padding: 16, textAlign: 'center', color: 'var(--gray)' }}>Recherche...</div>
    if (results === 'error') return <div style={{ padding: 16, textAlign: 'center', color: 'var(--danger)' }}>Erreur de recherche</div>
    const groups = SEARCH_GROUPS.filter(([key]) => results?.[key]?.length)
    if (!groups.length) return <div style={{ padding: 20, textAlign: 'center' }}>Aucun résultat pour « {query.trim()} »</div>
    return groups.map(([key, label, icon, toUrl]) => (
      <div key={key}>
        <div className="nuf-search-cat">{label}</div>
        {results[key].slice(0, 4).map((item) => (
          <Link key={item.slug} to={toUrl(item.slug)} className="nuf-search-item">
            <i className={`bi ${icon}`}></i>
            <div>
              <div style={{ fontWeight: 600 }}>{item.titre}</div>
              <div style={{ fontSize: 12, color: 'var(--gray)' }}>{(item.extrait || '').slice(0, 50)}</div>
            </div>
          </Link>
        ))}
      </div>
    ))
  }

  return (
    <>
      {/* TOP BAR */}
      <div className={`nuf-topbar${scrolled ? ' hidden' : ''}`} id="nufTopbar">
        <div className="nuf-topbar-inner">
          <div className="nuf-topbar-side">
            <a href={`tel:${settings.site_phone}`}>
              <i className="bi bi-telephone-fill"></i>
              <span className="d-none d-md-inline">{settings.site_phone}</span>
            </a>
            <div className="nuf-topbar-div d-none d-sm-block"></div>
            <a href={`mailto:${settings.contact_email_invest}`}>
              <i className="bi bi-envelope-fill"></i>
              <span className="d-none d-lg-inline">{settings.contact_email_invest}</span>
            </a>
          </div>
          <div className="nuf-topbar-side">
            <a href="#" onClick={(e) => e.preventDefault()}>
              <i className="bi bi-geo-alt-fill"></i>
              <span className="d-none d-md-inline">{settings.adresse_siege}</span>
            </a>
            <div className="nuf-topbar-div d-none d-sm-block"></div>
            <a href="#" onClick={(e) => e.preventDefault()}>
              <i className="bi bi-clock-fill"></i>
              <span className="d-none d-sm-inline">{settings.horaires_travail}</span>
            </a>
          </div>
        </div>
      </div>

      {/* MAIN HEADER */}
      <header className={`nuf-header${scrolled ? ' scrolled' : ''}${hideUp ? ' hide-up' : ''}`} id="nufHeader">
        <div className="nuf-header-inner">
          <Link to={ROUTES.home} className="nuf-brand">
            <div className="nuf-brand-logo">
              <img src={logo} alt="NUFOTEC" onError={(e) => { e.currentTarget.src = FALLBACK_LOGO }} />
            </div>
            <div className="nuf-brand-text">
              <h1>{settings.site_name}</h1>
              <span>{settings.span_site_name}</span>
            </div>
          </Link>

          {/* Recherche */}
          <div className={`nuf-search${searchShow ? ' show' : ''}`} id="nufSearch" ref={searchRef}>
            <input type="text" id="nufSearchInput" className="nuf-search-input" placeholder="Rechercher produits, pages..."
                   autoComplete="off" value={query} onChange={(e) => setQuery(e.target.value)}
                   ref={(el) => { if (el && searchShow) setTimeout(() => el.focus(), 80) }} />
            <button className="nuf-search-btn" id="nufSearchBtn" aria-label="Rechercher" type="button">
              <i className="bi bi-search"></i>
            </button>
            <div className={`nuf-search-results${results ? ' open' : ''}`} id="nufSearchResults">
              {results && renderResults()}
            </div>
          </div>

          <div className="nuf-actions">
            <button className="nuf-action-btn d-lg-none" id="nufSearchToggle" aria-label="Rechercher" type="button"
                    onClick={(e) => { e.stopPropagation(); setSearchShow((v) => !v) }}>
              <i className="bi bi-search"></i>
            </button>

            <Link to={accountLink} className="nuf-action-btn d-none d-lg-flex" title={user ? 'Mon compte' : 'Connexion'}>
              {user?.photo_url ? <img src={user.photo_url} alt="Avatar" className="nuf-avatar" />
                : user ? <div className="nuf-initials">{initials(user)}</div>
                  : <i className="bi bi-person-circle"></i>}
              <span className="d-none d-xl-inline">{user ? 'Mon compte' : 'Connexion'}</span>
            </Link>

            {/* Langues (ordinateur) */}
            <div className="nuf-lang d-none d-lg-block" id="nufLangWrap" ref={langRef}>
              <button className={`nuf-lang-btn${langOpen ? ' open' : ''}`} id="nufLangBtn" type="button"
                      onClick={(e) => { e.stopPropagation(); setLangOpen((v) => !v) }}>
                <img src={flagUrl(currentLang[1])} alt="" id="nufLangFlag" />
                <span id="nufLangLabel">{currentLang[2]}</span>
                <i className="bi bi-chevron-down"></i>
              </button>
              <div className={`nuf-lang-drop${langOpen ? ' open' : ''}`} id="nufLangDrop">
                <div className="nuf-lang-label">Langues principales</div>
                {MAIN_LANGUAGES.map(([code, flag, , label]) => (
                  <button key={code} type="button" className={`nuf-lang-opt${code === lang ? ' active' : ''}`} onClick={() => chooseLang(code)}>
                    <img src={flagUrl(flag)} alt="" /> {label}
                  </button>
                ))}
                <div className="nuf-lang-sep"></div>
                <div className="nuf-lang-label">Autres langues</div>
                {OTHER_LANGUAGES.map(([code, flag, , label]) => (
                  <button key={code} type="button" className={`nuf-lang-opt${code === lang ? ' active' : ''}`} onClick={() => chooseLang(code)}>
                    <img src={flagUrl(flag)} alt="" /> {label}
                  </button>
                ))}
              </div>
            </div>

            <button className={`nuf-hamburger${panelOpen ? ' open' : ''}`} id="nufHamburger" aria-label="Menu" type="button"
                    onClick={() => setPanelOpen(true)}>
              <i className="bi bi-list"></i>
            </button>
          </div>
        </div>
      </header>

      {/* MENU ORDINATEUR */}
      <nav className="nuf-nav" id="nufNav">
        <div className="nuf-nav-inner">
          <ul className="nuf-menu">
            <li className="nuf-menu-item"><Link to={ROUTES.home} className="nuf-menu-link">Accueil</Link></li>
            <MegaMenu label="À propos" columns={ABOUT_MENU} />
            <li className="nuf-menu-item"><Link to={ROUTES.products} className="nuf-menu-link">Boutique</Link></li>
            <li className="nuf-menu-item"><Link to={ROUTES.doctors} className="nuf-menu-link">Téléconsultation</Link></li>
            <MegaMenu label="Investissement" columns={INVEST_MENU} />
            <li className="nuf-menu-item"><Link to={ROUTES.media} className="nuf-menu-link">Médias</Link></li>
          </ul>
          <div className="nuf-nav-cta">
            <Link to={ROUTES.contact} className="nuf-btn-cta"><i className="bi bi-headset"></i> Contact</Link>
          </div>
        </div>
      </nav>

      {/* PANNEAU MOBILE */}
      <div className={`nuf-overlay${panelOpen ? ' open' : ''}`} id="nufOverlay" onClick={() => setPanelOpen(false)}></div>
      <div className={`nuf-panel${panelOpen ? ' open' : ''}`} id="nufPanel">
        <div className="nuf-panel-head">
          <button className="nuf-panel-close" id="nufPanelClose" type="button" onClick={() => setPanelOpen(false)}>
            <i className="bi bi-x-lg"></i>
          </button>
          <div className="nuf-panel-user">
            {user?.photo_url ? <img src={user.photo_url} alt="Avatar" className="nuf-panel-avatar" />
              : user ? <div className="nuf-panel-avatar" style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}>{initials(user)}</div>
                : (
                  <div className="nuf-panel-avatar" style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'transparent', border: '2px solid var(--accent)' }}>
                    <i className="bi bi-person-circle" style={{ fontSize: 32, color: 'var(--accent)' }}></i>
                  </div>
                )}
            <div className="nuf-panel-userinfo">
              <h4>{user ? [user.prenom, user.nom].filter(Boolean).join(' ') : 'Bienvenue'}</h4>
              <p>{user ? 'Connecté' : 'Connectez-vous à votre compte'}</p>
            </div>
          </div>
        </div>

        <div className="nuf-panel-body">
          <div className="nuf-panel-section">
            <div className="nuf-panel-sec-title"><i className="bi bi-grid-3x3-gap"></i> Navigation</div>
            <ul className="nuf-panel-list">
              <li><Link to={ROUTES.home} className="nuf-panel-link"><i className="bi bi-house-door"></i><span>Accueil</span></Link></li>
              {renderMobileSub('mob-about', 'bi-building', 'À propos')}
              <li><Link to={ROUTES.products} className="nuf-panel-link"><i className="bi bi-box-seam"></i><span>Boutique</span></Link></li>
              <li><Link to={ROUTES.doctors} className="nuf-panel-link"><i className="bi bi-camera-video"></i><span>Téléconsultation</span></Link></li>
              {renderMobileSub('mob-invest', 'bi-bar-chart-line', 'Investissement')}
              <li><Link to={ROUTES.media} className="nuf-panel-link"><i className="bi bi-collection-play"></i><span>Médias</span></Link></li>
              <li><Link to={ROUTES.contact} className="nuf-panel-link"><i className="bi bi-envelope"></i><span>Contact</span></Link></li>
            </ul>
          </div>

          <div className="nuf-panel-section">
            <div className="nuf-panel-sec-title"><i className="bi bi-globe"></i> Langue</div>
            <ul className="nuf-panel-list">
              <li>
                <button type="button" className={`nuf-panel-link${openSub === 'mob-lang' ? ' open' : ''}`} onClick={() => toggleSub('mob-lang')}>
                  <i className="bi bi-translate"></i><span>Changer de langue</span><i className="bi bi-chevron-right ch"></i>
                </button>
                <div className={`nuf-sub${openSub === 'mob-lang' ? ' open' : ''}`} id="nuf-sub-mob-lang"
                     ref={(el) => { subRefs.current['mob-lang'] = el }} style={{ ...subStyle('mob-lang'), overflowY: 'auto' }}>
                  {ALL_LANGUAGES.map(([code, flag, , label]) => (
                    <button key={code} type="button" className="nuf-sub-item" onClick={() => chooseLang(code)}>
                      <img src={flagUrl(flag)} alt="" /> {label}
                    </button>
                  ))}
                </div>
              </li>
            </ul>
          </div>
        </div>

        <div className="nuf-panel-foot">
          <Link to={ROUTES.contact} className="nuf-panel-btn primary"><i className="bi bi-headset"></i> Nous contacter</Link>
          {user ? (
            <a href="/" className="nuf-panel-btn outline" onClick={() => { api.post('/auth/logout').catch(() => {}); clearSession() }}>
              <i className="bi bi-box-arrow-right"></i> Déconnexion
            </a>
          ) : (
            <Link to={ROUTES.login} className="nuf-panel-btn outline"><i className="bi bi-box-arrow-in-right"></i> Connexion</Link>
          )}
        </div>
      </div>

      {/* BARRE DU BAS (mobile) */}
      <div className="nuf-bottom">
        <ul className="nuf-bottom-list">
          <li><Link to={ROUTES.home} className="nuf-bottom-link"><i className="bi bi-house-door"></i><span>Accueil</span></Link></li>
          <li><Link to={ROUTES.products} className="nuf-bottom-link"><i className="bi bi-box-seam"></i><span>Boutique</span></Link></li>
          <li><Link to={ROUTES.doctors} className="nuf-bottom-link"><i className="bi bi-camera-video"></i><span>Consult.</span></Link></li>
          <li><Link to={ROUTES.media} className="nuf-bottom-link"><i className="bi bi-collection-play"></i><span>media</span></Link></li>
          <li><Link to={accountLink} className="nuf-bottom-link"><i className="bi bi-person"></i><span>{user ? 'Compte' : 'Connexion'}</span></Link></li>
        </ul>
      </div>
    </>
  )
}
