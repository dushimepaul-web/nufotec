// Pied de page — mêmes sections que includes/frontend/Footer.php (marque, navigation, services, contact, réseaux,
// copyright). Le logo NUFOTEC est en arrière-plan sous un voile vert transparent (préférence validée, sans dégradé).
// Contact UNIQUEMENT par WhatsApp (pas d'appel) : le numéro ouvre WhatsApp directement.
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

const YEAR = new Date().getFullYear()

export default function Footer() {
  const { settings, socials } = useSite()
  const phone = settings.contact_whatsapp || settings.site_phone || ''
  const whatsapp = `https://wa.me/${phone.replace(/\D/g, '')}?text=${encodeURIComponent('Bonjour NUFOTEC BURUNDI, ')}`
  const email = settings.contact_email_invest
  const map = `https://maps.google.com/?q=${encodeURIComponent(settings.adresse_siege || 'Bujumbura, Burundi')}`
  const slogan = settings.agf_slogan || 'Excellence Agro-Industrielle et Phytomédicinale'

  return (
    <footer className="nfoot" id="footer">
      <img className="nfoot-bg" src="/logo.jpeg" alt="" loading="lazy" />
      <span className="nfoot-veil" aria-hidden="true"></span>

      {/* Bande d'appel à l'action : écrire sur WhatsApp */}
      <div className="nfoot-cta">
        <div className="nfoot-wrap nfoot-cta-in">
          <p><i className="bi bi-chat-dots"></i> Une question ? Un expert ou un conseiller vous répond sur WhatsApp.</p>
          <a className="nfoot-cta-btn" href={whatsapp} target="_blank" rel="noopener noreferrer">
            <i className="bi bi-whatsapp"></i> <span>Écrire sur WhatsApp</span>
          </a>
        </div>
      </div>

      <div className="nfoot-wrap nfoot-grid">
        {/* Marque */}
        <div className="nfoot-brand">
          <Link to={ROUTES.home} className="nfoot-id">
            <img src={settings.site_logo || '/logo.jpeg'} alt={settings.site_name} className="nfoot-logo" />
            <span>
              <b>{settings.site_name}</b>
              <small>{slogan}</small>
            </span>
          </Link>
          <p className="nfoot-desc">{settings.agf_description_courte}</p>

          {/* Mobile : boutons de contact rapides (WhatsApp, e-mail, plan — jamais d'appel) */}
          <div className="nfoot-quick">
            <a href={whatsapp} target="_blank" rel="noopener noreferrer"><i className="bi bi-whatsapp"></i>WhatsApp</a>
            {email && <a href={`mailto:${email}`}><i className="bi bi-envelope"></i>E-mail</a>}
            <a href={map} target="_blank" rel="noopener noreferrer"><i className="bi bi-geo-alt"></i>Plan</a>
          </div>

          {socials.length > 0 && (
            <div className="nfoot-social">
              {socials.map((s) => (
                <a key={s.url} href={s.url} aria-label={s.libelle} target="_blank" rel="noopener noreferrer">
                  <i className={`bi bi-${s.icone}`}></i>
                </a>
              ))}
            </div>
          )}
        </div>

        {/* Navigation */}
        <nav className="nfoot-col" aria-label="Navigation du pied de page">
          <h4>Navigation</h4>
          <ul>
            {NAV_LINKS.map(([to, label]) => <li key={label}><Link to={to}>{label}</Link></li>)}
          </ul>
        </nav>

        {/* Services */}
        <div className="nfoot-col">
          <h4>Nos services</h4>
          <ul>
            {SERVICE_LINKS.map(([to, icon, label]) => (
              <li key={label}><Link to={to}><i className={`bi ${icon}`}></i>{label}</Link></li>
            ))}
          </ul>
        </div>

        {/* Contact (ordinateur et tablette) */}
        <div className="nfoot-col nfoot-contact">
          <h4>Contact</h4>
          <ul>
            <li><a href={whatsapp} target="_blank" rel="noopener noreferrer"><i className="bi bi-whatsapp"></i>{phone}</a></li>
            {email && <li><a href={`mailto:${email}`}><i className="bi bi-envelope"></i>{email}</a></li>}
            <li><a href={map} target="_blank" rel="noopener noreferrer"><i className="bi bi-geo-alt"></i>{settings.adresse_siege}</a></li>
            {settings.horaires_travail && <li><span><i className="bi bi-clock"></i>{settings.horaires_travail}</span></li>}
          </ul>
        </div>
      </div>

      <div className="nfoot-bottom">
        <div className="nfoot-wrap nfoot-bottom-in">
          <p>&copy; {YEAR} <b>{settings.site_name}</b>. Tous droits réservés.</p>
          <p>Conçu par Dushime Paul</p>
          <button className="back-to-top" type="button" aria-label="Retour en haut"
                  onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}>
            <i className="bi bi-arrow-up"></i>
          </button>
        </div>
      </div>
    </footer>
  )
}
