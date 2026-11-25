// Page d'accueil — version 2 : les 8 sections d'origine (même ordre), nouveau design aux couleurs du logo.
// Version précédente conservée dans .restore/home-v1/ (fidèle au site PHP).
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import AOS from 'aos'
import api from '../lib/api'
import { PAGE, ROUTES } from '../lib/routes'
import { usePageMeta, useSite } from '../lib/site'
import useApi from '../lib/useApi'
import { fallback, formatSlot, isoDay } from '../lib/format'
import ProductCard from '../ui/ProductCard'
import '../styles/home-v2.css'

// Images de secours téléchargées dans public/img (plus d'appel à Unsplash)
const IMG = {
  hero: '/img/hero-fallback.jpg',
  vision: '/img/vision-lab.jpg',
  sante: '/img/sante-fallback.jpg',
}

const VALUES = [
  ['bi-clock-history', 'Consultations rapides', 'Disponibles 7 jours sur 7.'],
  ['bi-award', 'Professionnels de santé', 'Qualifiés et certifiés.'],
  ['bi-shield-lock', 'Sécurité et confidentialité', 'Plateforme 100 % sécurisée.'],
  ['bi-calendar-check', 'Prise de rendez-vous', 'Facile, en quelques clics.'],
]

const STEPS = [
  ['Choisir un expert', 'Spécialité, tarif et langue parlée.'],
  ['Réserver un créneau', 'Les disponibilités sont affichées en direct.'],
  ['Consulter', 'En vidéo, avec un suivi sur WhatsApp.'],
]

// Chaque public prend une couleur du logo : vert (anneau), or (lauriers), argent (médaillon), rouge (ruban).
// Une carte = à qui on s'adresse, ce qu'il obtient (3 bénéfices concrets), une action principale et un lien d'info.
const CTAS = [
  { tone: 'green', icon: 'bi-graph-up-arrow', who: 'Vous voulez investir', title: 'Investisseurs',
    text: "Un projet biotechnologique d'envergure internationale, à fort impact durable.",
    points: ["Plus de 40 M USD de capital d'amorçage", 'Projections détaillées par phase', 'Data room sécurisée après accord de confidentialité'],
    to: ROUTES.investor, label: 'Manifester mon intérêt', more: [PAGE.projection, 'Voir les projections'] },
  { tone: 'gold', icon: 'bi-people', who: 'Vous avez un réseau', title: 'Courtiers',
    text: 'Mettez-nous en relation avec des investisseurs qualifiés et soyez rémunéré.',
    points: ['Commission sur chaque mise en relation', 'Mandat formel, non exclusif', 'Espace courtier pour suivre vos dossiers'],
    to: ROUTES.broker, label: 'Devenir courtier', more: [PAGE.commission, 'Voir les commissions'] },
  { tone: 'silver', icon: 'bi-bag-heart', who: 'Vous voulez acheter', title: 'Acheteurs',
    text: 'Nos produits nutraceutiques, alimentaires et cosmétiques à base de plantes.',
    points: ['Produits naturels, sans additifs nocifs', 'Commande simple, suivie sur WhatsApp', "Avis d'un expert si besoin"],
    to: ROUTES.products, label: 'Voir la boutique', more: [ROUTES.products, 'Tous les produits'] },
  { tone: 'red', icon: 'bi-heart-pulse', who: 'Vous voulez être soigné', title: 'Patients',
    text: 'Une téléconsultation professionnelle et un suivi de santé personnalisé.',
    points: ['Experts qualifiés, 7 jours sur 7', 'Créneaux disponibles en direct', 'Suivi confidentiel sur WhatsApp'],
    to: ROUTES.doctors, label: 'Prendre rendez-vous', more: [ROUTES.doctors, 'Voir les experts'] },
]


/** Premier créneau libre parmi les experts (GET /medecins puis /medecins/{uuid}/creneaux). */
function useNextSlot() {
  const [slot, setSlot] = useState(null)
  useEffect(() => {
    let alive = true
    api.get('/medecins').then(async (doctors) => {
      const now = new Date()
      const params = { du: isoDay(now), au: isoDay(new Date(now.getTime() + 7 * 864e5)) }
      for (const doc of (doctors ?? []).slice(0, 3)) {
        const slots = await api.get(`/medecins/${doc.uuid}/creneaux`, params).catch(() => [])
        const next = slots?.find((s) => new Date(s.debut) > now)
        if (next) { if (alive) setSlot(next.debut); return }
      }
    }).catch(() => {})
    return () => { alive = false }
  }, [])
  return slot
}

function Head({ icon, badge, title, text, center = true }) {
  return (
    <div className={`hv2_head${center ? ' center' : ''}`} data-aos="fade-up">
      <span className="hv2_badge"><i className={`bi ${icon}`}></i> {badge}</span>
      <h2>{title}</h2>
      {text && <p>{text}</p>}
    </div>
  )
}

function Newsletter() {
  const [form, setForm] = useState({ email: '', telephone: '', site_web: '' })
  const [state, setState] = useState({ sending: false, message: '' })

  const submit = async (e) => {
    e.preventDefault()
    setState({ sending: true, message: '' })
    try {
      const res = await api.postWithMessage('/newsletter/inscription', { ...form, telephone: form.telephone || null })
      setState({ sending: false, message: res.message })
      setForm({ email: '', telephone: '', site_web: '' })
    } catch (err) {
      setState({ sending: false, message: err.field?.('email') || err.message })
    }
  }

  return (
    <div className="hv2_news" data-aos="fade-up">
      <div>
        <h3><i className="bi bi-envelope-paper me-2"></i>Abonnez-vous à notre newsletter</h3>
        <p>Nouveautés, conseils santé, offres et actualités de NUFOTEC. Vos informations restent confidentielles,
          désinscription à tout moment.</p>
      </div>
      <div>
        <form onSubmit={submit}>
          <input type="text" value={form.site_web} onChange={(e) => setForm({ ...form, site_web: e.target.value })} tabIndex={-1}
                 autoComplete="off" aria-hidden="true" style={{ position: 'absolute', left: '-9999px' }} />
          <input type="email" required placeholder="Votre adresse email" aria-label="Adresse email"
                 value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
          <input type="tel" placeholder="Téléphone (facultatif)" aria-label="Téléphone"
                 value={form.telephone} onChange={(e) => setForm({ ...form, telephone: e.target.value })} />
          <button type="submit" className="hv2_btn gold" disabled={state.sending}>
            {state.sending ? <span className="spinner-border spinner-border-sm"></span> : "Je m'abonne"}
          </button>
        </form>
        {state.message && <div className="hv2_news-msg mt-2" role="status">{state.message}</div>}
      </div>
    </div>
  )
}

export default function Home() {
  const { settings } = useSite()
  const figures = useApi('/chiffres-cles')
  const [category, setCategory] = useState(null)
  const products = useApi('/produits', { per_page: 4, ...(category ? { categorie: category } : {}) })
  const categories = useApi('/produits/categories')
  const nextSlot = useNextSlot()
  usePageMeta(null, settings.site_description)

  // Les cartes arrivent après le chargement : on recalcule les animations
  useEffect(() => {
    setTimeout(() => AOS.refresh(), 50)
  }, [figures.data, products.data])

  const productItems = products.data?.items ?? []
  // Onglets de catégories : seulement celles qui ont des produits, et seulement s'il y en a au moins deux
  const productCategories = (categories.data ?? []).filter((c) => c.produits > 0)

  return (
    <div className="hv2">
      {/* HERO — le logo NUFOTEC en arrière-plan plein écran, voile vert uni (aucun dégradé), texte par-dessus */}
      <section className="hv2_hero2">
        <img className="hv2_hero2-bg" fetchPriority="high" src={settings.site_logo || '/logo.jpeg'} alt=""
             onError={(e) => { e.currentTarget.src = '/logo.jpeg' }} />
        <div className="hv2_wrap hv2_hero2-inner">
          <div className="hv2_hero2-grid">
            <div data-aos="fade-up">
              <span className="hv2_badge"><i className="bi bi-flower1"></i> NUFOTEC BURUNDI · Médecine naturelle</span>
              <h1>La santé par les plantes, <em>prouvée par la science</em></h1>
              <p className="hv2_hero2-lead">
                Consultez un expert en ligne et commandez des produits phytomédicinaux certifiés, livrés chez vous.
              </p>
              <div className="hv2_hero-ctas">
                <Link to={ROUTES.doctors} className="hv2_btn primary"><i className="bi bi-calendar-check"></i> Être consulté</Link>
                <Link to={ROUTES.products} className="hv2_btn outline"><i className="bi bi-box-seam"></i> Acheter les produits</Link>
              </div>
            </div>
          </div>
          <div className="hv2_hero2-foot" data-aos="fade-up" data-aos-delay="150">
            <div className="hv2_trust">
              <span><i className="bi bi-patch-check"></i>Experts qualifiés</span>
              <span><i className="bi bi-whatsapp"></i>Suivi WhatsApp</span>
              <span><i className="bi bi-shield-lock"></i>Confidentiel</span>
            </div>
            <Link to={ROUTES.doctors} className="hv2_slot">
              <div className="hv2_slot-ico"><i className="bi bi-clock"></i></div>
              <div>
                <b><span className="hv2_live"></span>{nextSlot ? 'Prochain créneau' : 'Experts disponibles'}</b>
                <span>{nextSlot ? formatSlot(nextSlot) : '7 jours sur 7'}</span>
              </div>
            </Link>
          </div>
        </div>
      </section>

      {/* 1. PRÉSENTATION — Vision 2026-2031 */}
      <section className="hv2_section">
        <div className="hv2_wrap hv2_pres">
          <div className="hv2_pres-media" data-aos="fade-up">
            <img loading="lazy" decoding="async" src={settings.image_vision_2026 || IMG.vision}
                 alt="Laboratoire NUFOTEC-PHYTOMED Industries" onError={fallback(IMG.vision)} />
            <div className="hv2_floating">
              <div className="hv2_floating-ico"><i className="bi bi-shield-check"></i></div>
              <div><b>Normes ISO et GMP</b><span>Recherche et innovation</span></div>
            </div>
          </div>
          <div data-aos="fade-up" data-aos-delay="100">
            <span className="hv2_badge"><i className="bi bi-stars"></i> Vision 2026-2031</span>
            <h2>NUFOTEC-PHYTOMED INDUSTRIES — industrialiser l'économie des plantes médicinales en Afrique</h2>
            <div className="hv2_kicker">Investissez en nous ou devenez notre courtier contractuel</div>
            <p className="lead-text">
              Entreprise de biotechnologie agro-industrielle basée au Burundi, NUFOTEC transforme les plantes médicinales
              en médecines traditionnelles améliorées, nutraceutiques, aliments santé et engrais organiques, validés par
              son laboratoire de recherche.
            </p>
            <ul className="hv2_pres-points">
              <li><i className="bi bi-check-circle"></i>Plus de 40 millions USD de capital d'amorçage</li>
              <li><i className="bi bi-check-circle"></i>Agriculture biologique et agriculteurs sous contrat</li>
              <li><i className="bi bi-check-circle"></i>Emplois durables, en particulier pour les jeunes et les femmes</li>
            </ul>
            <Link to={ROUTES.presentation} className="hv2_btn primary">Voir plus <i className="bi bi-arrow-right"></i></Link>
          </div>
        </div>
      </section>

      {/* 2. NOTRE VALEUR AJOUTÉE */}
      <section className="hv2_section hv2_soft">
        <div className="hv2_wrap">
          <Head icon="bi-gem" badge="Excellence et distinction" title="Notre valeur ajoutée"
                text="Pourquoi choisir notre plateforme de téléconsultation ?" />
          <div className="hv2_values">
            {VALUES.map(([icon, title, text], i) => (
              <div className="hv2_value" key={title} data-aos="fade-up" data-aos-delay={i * 80}>
                <div className="hv2_path-ico"><i className={`bi ${icon}`}></i></div>
                <b>{title}</b>
                <span>{text}</span>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* 3. TÉLÉCONSULTATION */}
      <section className="hv2_section">
        <div className="hv2_wrap hv2_tele">
          <div data-aos="fade-up">
            <Head center={false} icon="bi-camera-video" badge="Santé connectée"
                  title="Consultez un professionnel de santé, où que vous soyez"
                  text="Un avis médical, un suivi ou des conseils, sans déplacement ni longue attente." />
            <div className="hv2_steps">
              {STEPS.map(([title, text], i) => (
                <div className="hv2_step" key={title}>
                  <div className="hv2_step-n">{i + 1}</div>
                  <div><b>{title}</b><span>{text}</span></div>
                </div>
              ))}
            </div>
            <Link to={ROUTES.doctors} className="hv2_btn primary"><i className="bi bi-calendar-plus"></i> Prendre rendez-vous</Link>
          </div>
          <div className="hv2_tele-media" data-aos="fade-up" data-aos-delay="100">
            <img loading="lazy" decoding="async" src={settings.image_sante_connectee || IMG.sante} alt="Téléconsultation NUFOTEC" onError={fallback(IMG.sante)} />
            <div className="hv2_floating">
              <div className="hv2_floating-ico"><i className="bi bi-star"></i></div>
              <div><b>98,4 %</b><span>Satisfaction patients</span></div>
            </div>
          </div>
        </div>
      </section>

      {/* 4. CHIFFRES CLÉS */}
      <section className="hv2_section hv2_figures">
        <div className="hv2_wrap">
          <div className="hv2_head center" data-aos="fade-up">
            <span className="hv2_badge"><i className="bi bi-bar-chart"></i> Impact et envergure</span>
            <h2 style={{ color: '#fff' }}>Chiffres clés</h2>
            <p style={{ color: '#cfe3da' }}>Notre croissance mesurée en chiffres.</p>
          </div>
          <div className="hv2_fig-grid">
            {figures.loading && <div className="text-center" style={{ gridColumn: '1 / -1' }}><span className="spinner-border text-light"></span></div>}
            {(figures.data ?? []).map((f, i) => (
              <div className="hv2_fig" key={f.id} title={f.description || undefined} data-aos="fade-up" data-aos-delay={(i % 4) * 80}>
                <b>{f.valeur}</b>
                <span>{[f.unite, f.etiquette].filter(Boolean).join(' · ')}</span>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* 5. NOS APPELS À L'ACTION */}
      <section className="hv2_section">
        <div className="hv2_wrap">
          <Head icon="bi-hand-index" badge="Rejoignez-nous" title="Nos appels à l'action"
                text="Investisseur, courtier, acheteur ou patient : choisissez votre parcours, nous nous occupons du reste." />
          <div className="hv2_ctas">
            {CTAS.map((c, i) => (
              <article className={`hv2_cta ${c.tone}`} key={c.title} data-aos="fade-up" data-aos-delay={i * 80}>
                <header className="hv2_cta-top">
                  <div className="hv2_cta-ico"><i className={`bi ${c.icon}`}></i></div>
                  <div>
                    <span className="hv2_cta-who">{c.who}</span>
                    <h3>{c.title}</h3>
                  </div>
                </header>
                <div className="hv2_cta-body">
                  <p>{c.text}</p>
                  <ul>
                    {c.points.map((pt) => <li key={pt}><i className="bi bi-check2-circle"></i>{pt}</li>)}
                  </ul>
                  <Link to={c.to} className="hv2_btn sm hv2_cta-btn">{c.label} <i className="bi bi-arrow-right"></i></Link>
                  <Link to={c.more[0]} className="hv2_cta-more">{c.more[1]}</Link>
                </div>
              </article>
            ))}
          </div>
          <p className="hv2_cta-help" data-aos="fade-up">
            Vous ne savez pas par où commencer ? <Link to={ROUTES.contact}>Écrivez-nous</Link>, nous vous orientons.
          </p>
        </div>
      </section>

      {/* 6. NOS PRODUITS */}
      <section className="hv2_section hv2_soft">
        <div className="hv2_wrap">
          <div className="hv2_head hv2_headrow" data-aos="fade-up">
            <div>
              <span className="hv2_badge"><i className="bi bi-bag-heart"></i> Nos produits</span>
              <h2>Découvrez nos produits naturels</h2>
              <p style={{ margin: 0 }}>Des produits phytomédicinaux formulés à partir de plantes sélectionnées, pour votre bien-être.</p>
            </div>
          </div>

          {productCategories.length >= 2 && (
            <div className="hv2_chips" role="tablist" aria-label="Catégories de produits">
              <button type="button" role="tab" aria-selected={!category} className={!category ? 'active' : ''} onClick={() => setCategory(null)}>Tous</button>
              {productCategories.map((c) => (
                <button key={c.slug} type="button" role="tab" aria-selected={category === c.slug}
                        className={category === c.slug ? 'active' : ''} onClick={() => setCategory(c.slug)}>
                  {c.nom} <span>{c.produits}</span>
                </button>
              ))}
            </div>
          )}

          {products.loading && <div className="text-center py-4"><span className="spinner-border text-success"></span></div>}
          {!products.loading && productItems.length === 0 && <p className="text-muted text-center">Aucun produit disponible pour le moment</p>}
          <div className="hv2_products">
            {productItems.map((p, i) => <ProductCard key={p.uuid} product={p} delay={(i % 4) * 80} />)}
          </div>
          {(products.data?.meta?.total ?? 0) > productItems.length && (
            <div className="hv2_more-wrap">
              <Link to={ROUTES.products} className="hv2_btn outline">
                Voir plus de produits ({products.data.meta.total}) <i className="bi bi-arrow-right"></i>
              </Link>
            </div>
          )}

          <div className="hv2_shop-trust" data-aos="fade-up">
            <div><i className="bi bi-whatsapp"></i><b>Commande sur WhatsApp</b><span>Un conseiller confirme avec vous</span></div>
            <div><i className="bi bi-heart-pulse"></i><b>Avis d'un expert</b><span>Téléconsultation si besoin</span></div>
            <div><i className="bi bi-flower1"></i><b>À base de plantes</b><span>Sélectionnées et transformées au Burundi</span></div>
          </div>
          <p className="hv2_notice"><i className="bi bi-info-circle"></i> Ces produits ne remplacent pas un avis ni un traitement médical.
            Consultez un expert avant usage, en particulier en cas de grossesse, de maladie chronique ou de traitement en cours.</p>
        </div>
      </section>

      {/* 7. NEWSLETTER */}
      <section className="hv2_section">
        <div className="hv2_wrap"><Newsletter /></div>
      </section>
    </div>
  )
}
