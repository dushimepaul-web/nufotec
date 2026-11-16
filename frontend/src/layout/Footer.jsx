// Pied de page — conversion de includes/frontend/Footer.php (3 colonnes, accordéons sur mobile)
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { PAGE, ROUTES } from '../lib/routes'
import { useSite } from '../lib/site'
import '../styles/footer.css'

const NAV_LINKS = [
  [ROUTES.home, 'Accueil'],
  [ROUTES.presentation, 'À propos'],
  [PAGE.projection, 'Investissement'],
  [ROUTES.media, 'Médias'],
  [ROUTES.contact, 'Contact'],
]

const SERVICE_LINKS = [
  [ROUTES.doctors, 'bi-heart-pulse', 'Consultation médicale'],
  [ROUTES.products, 'bi-shop', 'Vente de produits'],
  [ROUTES.investor, 'bi-graph-up-arrow', 'Investir'],
]

function AccordionColumn({ id, title, open, onToggle, className, children }) {
  return (
    <div className={`footer-col ${className}`}>
      <button className="footer-accordion-toggle d-lg-none" type="button" aria-expanded={open} aria-controls={id} onClick={onToggle}>
        <h4>{title}</h4>
        <i className="bi bi-chevron-down"></i>
      </button>
      <h4 className="d-none d-lg-block">{title}</h4>
      <div className={`footer-accordion-content${open ? ' open' : ''}`} id={id}>{children}</div>
    </div>
  )
}

export default function Footer() {
  const { settings, socials } = useSite()
  const [open, setOpen] = useState(null)
  const toggle = (id) => setOpen((cur) => (cur === id ? null : id))
  const openMap = (e) => {
    e.preventDefault()
    window.open(`https://maps.google.com/?q=${encodeURIComponent(settings.adresse_siege)}`, '_blank')
  }

  return (
    <footer className="site-footer" id="footer">
      <div className="footer-container">
        <div className="footer-main">
          <div className="container">
            <div className="footer-grid">
              {/* Colonne 1 : marque et contact rapide */}
              <div className="footer-col footer-col-brand">
                <div className="footer-brand">
                  {settings.site_logo && <img src={settings.site_logo} alt={settings.site_name} className="footer-logo" />}
                  <div className="brand-info">
                    <h3>{settings.site_name}</h3>
                    <span>Innovation & Technologie</span>
                  </div>
                </div>

                <p className="footer-desc">{settings.agf_description_courte}</p>

                <div className="footer-quick-contact">
                  <a href={`tel:${settings.site_phone}`} className="quick-contact-item">
                    <i className="bi bi-telephone-fill"></i><span>{settings.site_phone}</span>
                  </a>
                  <a href={`mailto:${settings.contact_email_invest}`} className="quick-contact-item">
                    <i className="bi bi-envelope-fill"></i><span>{settings.contact_email_invest}</span>
                  </a>
                  <a href="#" className="quick-contact-item" onClick={openMap}>
                    <i className="bi bi-geo-alt-fill"></i><span>{settings.adresse_siege}</span>
                  </a>
                </div>

                <div className="footer-social">
                  {socials.map((s) => (
                    <a key={s.url} href={s.url} className="social-link" aria-label={s.libelle} target="_blank" rel="noopener noreferrer">
                      <i className={`bi bi-${s.icone}`}></i>
                    </a>
                  ))}
                </div>
              </div>

              {/* Colonne 2 : navigation */}
              <AccordionColumn id="footerNav1" title="Navigation" className="footer-col-nav" open={open === 1} onToggle={() => toggle(1)}>
                <ul className="footer-links">
                  {NAV_LINKS.map(([to, label]) => <li key={label}><Link to={to}>{label}</Link></li>)}
                </ul>
              </AccordionColumn>

              {/* Colonne 3 : nos services */}
              <AccordionColumn id="footerNav2" title="Nos services" className="footer-col-services" open={open === 2} onToggle={() => toggle(2)}>
                <ul className="footer-links">
                  {SERVICE_LINKS.map(([to, icon, label]) => (
                    <li key={label}><Link to={to}><i className={`bi ${icon}`}></i> {label}</Link></li>
                  ))}
                </ul>
              </AccordionColumn>
            </div>
          </div>
        </div>

        <div className="footer-bottom">
          <div className="container">
            <div className="footer-bottom-content">
              <div className="footer-copyright">
                <p>&copy; {new Date().getFullYear()} <strong>{settings.site_name}</strong>. Tous droits réservés.</p>
              </div>
              <div className="footer-legal">
                <p>Conçu par Dushime Paul : dushimeyesupaulin@gmail.com</p>
              </div>
              <button className="back-to-top" type="button" aria-label="Retour en haut"
                      onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}>
                <i className="bi bi-arrow-up"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </footer>
  )
}
