// Présentation — conversion de pages/Presentation_Detail_View.php : mêmes sections et même texte (bandeau ; qui
// sommes-nous ; domaines d'activité ; Vision 2026-2031 en chiffres ; chaîne de valeur ; valeurs ; impact ; partenaires
// en défilement ; pourquoi choisir NUFOTEC ; appel à l'action). Chiffres (GET /chiffres-cles), images (paramètres du
// site) et partenaires (GET /partenaires) viennent de l'API, comme dans le PHP. Design du site : couleurs du logo,
// aucun dégradé, bandeau logo sous voile vert.
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { ROUTES } from '../lib/routes'
import { usePageMeta, useSite } from '../lib/site'
import useApi from '../lib/useApi'
import PageBanner from '../ui/PageBanner'
import '../styles/presentation.css'

const ACTIVITIES = [
  ['capsule', 'Médecines Traditionnelles', 'MTCAs standardisées à base de plantes, ciblant rigoureusement les maladies aiguës et chroniques.'],
  ['heart-pulse', 'Nutraceutiques', 'Compléments alimentaires scientifiques et naturels pour la prévention et la santé globale.'],
  ['cup-straw', 'Aliments Santé', 'Produits alimentaires et boissons fortifiés clean-label, exempts de sucres et de produits chimiques nocifs ajoutés.'],
  ['flower1', 'Engrais Organiques', "Engrais organiques haute-nutrition pour la régénération et l'enrichissement durable des sols."],
  ['shield-plus', 'Phytomédicaments', 'Formulations phyto-pharmaceutiques avancées soumises à des essais précliniques et cliniques.'],
  ['clipboard2-pulse', 'Recherche Scientifique', "Laboratoire de recherche et installation d'élevage d'animaux de laboratoire de pointe."],
]
const BRIEF = [
  ['MTCAs', ' (Médecines Traditionnelles, Complémentaires et Alternatives), ciblant les maladies aiguës et chroniques ;'],
  ['Nutraceutiques/Compléments Alimentaires', ' ciblant les maladies aiguës et chroniques ;'],
  ['Produits alimentaires et boissons santé fortifiés clean-label', ' pour la prévention et la lutte contre la malnutrition, en particulier chez les enfants, les femmes enceintes et les mères allaitantes ;'],
  ['Engrais organiques haute-nutrition', ' pour la régénération et l’enrichissement des sols ;'],
  ['Phytomédicaments', ', ciblant les maladies aiguës et chroniques ;'],
  ['Phytopharmaceutiques', ', ciblant les maladies aiguës et chroniques.'],
]
const STEPS = [
  ['search', 'Recherche', 'Plantes médicinales ciblées'], ['tree', 'Culture', 'Agriculture biologique'],
  ['basket', 'Récolte', 'Matières premières sélectionnées'], ['gear', 'Transformation', 'Extraction & formulation'],
  ['clipboard-check', 'Contrôle qualité', 'Standards GMP rigoureux'], ['box-seam', 'Production', 'Fabrication industrielle'],
  ['truck', 'Distribution', 'Réseaux régionaux'], ['globe2', 'Exportation', 'Marchés internationaux'],
]
const VALUES = [
  ['lightbulb', 'Innovation', "Pionniers dans l'application des biotechnologies modernes à la pharmacopée africaine."],
  ['award', 'Qualité', 'Standards de fabrication stricts, produits clean-label exempts de substances nocives.'],
  ['shield-check', 'Intégrité', 'Transparence totale envers nos investisseurs, partenaires, courtiers et clients.'],
  ['recycle', 'Durabilité', 'Agriculture climato-intelligente et régénération des sols pour les générations futures.'],
  ['capsule-pill', 'Recherche', 'Essais précliniques et cliniques rigoureux pilotés par notre laboratoire scientifique.'],
  ['people', 'Impact social', 'Emplois durables et autonomisation des jeunes et des femmes au Burundi.'],
]
const WHY = [
  ['shield-check', 'Normes ISO & GMP', "Des infrastructures de production aux standards internationaux pour la sécurité et l'efficacité des produits."],
  ['capsule-pill', 'Recherche scientifique intégrée', 'Profilage de produits piloté par le laboratoire et essais précliniques et cliniques rigoureux.'],
  ['flower1', 'Produits 100 % naturels clean-label', 'Totalement exempts de sucres et de produits chimiques nocifs ajoutés.'],
  ['sunrise', 'Agriculture climato-intelligente', "Des pratiques durables associées à des réseaux structurés d'agriculteurs sous contrat."],
  ['layers', 'Intégration verticale complète', "De la graine à l'exportation : chaque maillon de la chaîne de valeur est maîtrisé en interne."],
  ['people', 'Impact social et économique', "Création d'emplois durables et transformation économique du Burundi et de la région."],
]
// mêmes correspondances que la vue PHP (étiquettes des chiffres clés -> libellés et unités en français)
const VISION_KEYS = {
  'Seed Capital': ["Capital d'amorçage", 'M USD'], Farmland: ['Plantations biologiques', 'hectares'],
  'Export Share': ['Exportation régionale', '%'], 'Direct Jobs': ["Création d'emplois", 'emplois'],
}
const IMPACT_KEYS = {
  'Out-growers': 'Agriculteurs partenaires', Farmland: 'Surface cultivée', 'Seed Capital': 'Investissements',
  'WhatsApp Groups': 'Groupes WhatsApp', 'WhatsApp Participants': 'Patients & communauté',
}
const UNITS = { 'USD Million': 'M USD', farmers: 'agriculteurs', employees: 'employés', groups: 'groupes', people: 'personnes' }

function Head({ badge, title, text, icon }) {
  return (
    <div className="pr_head" data-aos="fade-up">
      <span className="pr_badge">{icon && <i className={`bi ${icon}`}></i>} {badge}</span>
      <h2>{title}</h2>
      {text && <p>{text}</p>}
    </div>
  )
}

function Figure({ icon, value, unit, label, text, dark }) {
  return (
    <div className={`pr_fig ${dark ? 'dark' : ''}`} data-aos="fade-up">
      <div className="pr_fig-ico"><i className={`bi bi-${icon || 'gem'}`}></i></div>
      <div className="pr_fig-val">{value}{unit && <small> {unit}</small>}</div>
      <div className="pr_fig-lbl">{label}</div>
      {text && <p>{text}</p>}
    </div>
  )
}

function PartnerLogo({ partner }) {
  const [broken, setBroken] = useState(false)
  if (!partner.logo || broken) return <span className="pr_partner-ini">{partner.nom.slice(0, 2).toUpperCase()}</span>
  return <img src={partner.logo} alt={partner.nom} loading="lazy" onError={() => setBroken(true)} />
}

function Partners() {
  const { data, loading } = useApi('/partenaires')
  const list = data ?? []
  if (loading) return <div className="pr_partners-empty"><span className="spinner-border spinner-border-sm text-success"></span></div>
  if (!list.length) return <div className="pr_partners-empty">Aucun partenaire répertorié pour le moment.</div>
  return (
    <div className="pr_marquee">
      <div className="pr_track">
        {[...list, ...list].map((p, i) => (
          <article className="pr_partner" key={`${p.id}-${i}`} aria-hidden={i >= list.length}>
            <div className="pr_partner-logo">
              <PartnerLogo partner={p} />
            </div>
            {p.type && <span className="pr_partner-type">{p.type}</span>}
            <h3>{p.nom}</h3>
            {p.pays && <small><i className="bi bi-geo-alt-fill"></i> {p.pays}</small>}
            {p.description && <p>{p.description}</p>}
            {p.site_web && <a href={p.site_web} target="_blank" rel="noopener noreferrer" tabIndex={i >= list.length ? -1 : 0}><i className="bi bi-globe"></i> Visiter le site</a>}
          </article>
        ))}
      </div>
    </div>
  )
}

export default function Presentation() {
  usePageMeta('Présentation', "NUFOTEC-PHYTOMED INDUSTRIES : industrialisation de l'économie des plantes médicinales en Afrique.")
  const { settings } = useSite()
  const figures = useApi('/chiffres-cles')
  const byLabel = Object.fromEntries((figures.data ?? []).map((f) => [f.etiquette.trim(), f]))
  const vision = Object.entries(VISION_KEYS).filter(([k]) => byLabel[k]).map(([k, [label, unit]]) => ({ ...byLabel[k], label, unit }))
  vision.push({ icone: 'capsule-pill', valeur: 'GMP', unit: '', label: 'Recherche scientifique', description: 'Essais précliniques et cliniques pilotés par notre laboratoire scientifique interne.' })
  vision.push({ icone: 'gear', valeur: 'Scalable', unit: '', label: 'Transformation industrielle', description: 'Infrastructure de transformation scalable et fabrication à haute valeur ajoutée.' })
  const impact = Object.entries(IMPACT_KEYS).filter(([k]) => byLabel[k]).map(([k, label]) => ({ ...byLabel[k], label }))
  const imgWho = settings.image_vision_2026 || '/logo.jpeg'
  const imgWhy = settings.image_sante_connectee || '/logo.jpeg'

  return (
    <div className="pr">
      {/* BANDEAU */}
      <PageBanner crumbs={[['À propos'], ['Présentation']]} badge={<><i className="bi bi-building"></i> Présentation</>}
                  title="NUFOTEC-PHYTOMED INDUSTRIES" subtitle="Industrialisation de l'économie des plantes médicinales en Afrique">
        <p className="pr_banner-text">Entreprise de biotechnologie agro-industrielle intégrée verticalement, NUFOTEC transforme l'agriculture biologique
          africaine en produits de santé naturelle standardisés, de la plante au laboratoire, jusqu'au marché international.</p>
        <div className="pr_btns">
          <a href="#pr-vision" className="pr_btn gold">Découvrir notre vision <i className="bi bi-arrow-right"></i></a>
          <Link to={ROUTES.contact} className="pr_btn ghost"><i className="bi bi-envelope"></i> Nous contacter</Link>
        </div>
      </PageBanner>

      {/* 1. QUI SOMMES-NOUS ? */}
      <section className="pr_section" id="pr-qui-sommes-nous">
        <div className="pr_wrap pr_who">
          <div className="pr_who-img" data-aos="fade-right">
            <img src={imgWho} alt="Laboratoire moderne NUFOTEC-PHYTOMED Industries" onError={(e) => { e.currentTarget.src = '/logo.jpeg' }} />
          </div>
          <div className="pr_who-text" data-aos="fade-left">
            <span className="pr_badge">Qui sommes-nous ?</span>
            <h2>NUFOTEC-PHYTOMED INDUSTRIES – Industrialisation de l'économie des plantes médicinales en Afrique</h2>
            <div className="pr_callout"><i className="bi bi-megaphone"></i>
              <p>Investissez en nous ou devenez notre courtier contractuel dès aujourd'hui !</p>
              <div className="pr_callout-btns"><Link to={ROUTES.investor}>Investir</Link><Link to={ROUTES.broker}>Devenir courtier</Link></div>
            </div>
            <p>Avec plus de 40 millions USD de capital d'amorçage dans le cadre de la vision quinquennale (Vision 2026-2031), NUFOTEC Limited est une
              entreprise de biotechnologie agro-industrielle privée, intégrée verticalement, basée au Burundi, qui s'emploie régulièrement à transformer
              l'agriculture biologique commerciale de plantes médicinales ciblées et sélectionnées, de cultures fonctionnelles, de fruits et de ressources
              agricoles riches en nutriments en MTCAs standardisées à base de plantes (Médecines Traditionnelles, Complémentaires et Alternatives), en
              Nutraceutiques/Compléments Alimentaires, en produits alimentaires et boissons santé fortifiés clean-label totalement exempts de sucres et de
              produits chimiques nocifs ajoutés, en engrais organiques haute-nutrition ainsi qu'en formulations phyto-médicinales et phyto-pharmaceutiques
              scientifiquement avancées soumises à des essais précliniques et cliniques via notre laboratoire de recherche scientifique et notre
              installation d'élevage d'animaux de laboratoire.</p>
            <p>En intégrant l'agriculture biologique commerciale climato-intelligente, des réseaux structurés d'agriculteurs sous contrat, le profilage de
              produits piloté par le laboratoire, la standardisation, les essais précliniques et cliniques, et une infrastructure de transformation scalable,
              NUFOTEC fait passer l'agriculture d'une production de matières premières à faible marge à une agriculture industrielle et une fabrication à
              haute valeur ajoutée.</p>
            <p>L'entreprise construit une plateforme de croissance conçue pour étendre la superficie cultivée, augmenter la capacité de transformation,
              renforcer la pénétration des exportations régionales et générer un emploi durable, en particulier pour les jeunes et les femmes, tout en
              contribuant à la transformation économique du Burundi et au développement de la chaîne de valeur de la santé naturelle.</p>
            <p className="pr_strong">Nous visons un chiffre d'affaires annuel de plusieurs millions de dollars, une performance EBITDA durable et une expansion
              des exportations scalable d'ici 2031 (Vision 2026-2031).</p>
            <h3 className="pr_brief-title">En bref :</h3>
            <p className="pr_muted">L'installation de NUFOTEC fabrique les produits standard et conformes aux BPF suivants :</p>
            <ul className="pr_brief">
              {BRIEF.map(([b, rest], i) => <li key={b}><span>{String.fromCharCode(65 + i)}</span><p><strong>{b}</strong>{rest}</p></li>)}
            </ul>
          </div>
        </div>
      </section>

      {/* 2. NOS DOMAINES D'ACTIVITÉ */}
      <section className="pr_section soft">
        <div className="pr_wrap">
          <Head badge="Domaines d'excellence" title="Nos domaines d'activité" text="Six pôles d'expertise complémentaires, de la plante médicinale au produit de santé scientifique." />
          <div className="pr_grid3">
            {ACTIVITIES.map(([icon, title, text], i) => (
              <article className="pr_card" key={title} data-aos="fade-up" data-aos-delay={i * 60}>
                <div className="pr_ico"><i className={`bi bi-${icon}`}></i></div>
                <h3>{title}</h3><p>{text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      {/* 3. NOTRE VISION 2026-2031 */}
      <section className="pr_section" id="pr-vision">
        <div className="pr_wrap">
          <Head badge="Vision 2026-2031" title="Une vision quinquennale ambitieuse" text="Industrialiser la chaîne de valeur de la santé naturelle africaine avec une performance financière durable." />
          <div className="pr_vision" data-aos="fade-up">
            <h3>La Vision 2026-2031 en chiffres</h3>
            <p>De la graine au produit fini : un modèle intégré qui combine agriculture, recherche, transformation industrielle et exportation.</p>
            <div className="pr_grid3">
              {vision.map((v) => <Figure key={v.label} dark icon={v.icone} value={v.valeur} unit={v.unit} label={v.label} text={v.description} />)}
            </div>
          </div>
        </div>
      </section>

      {/* 4. NOTRE CHAÎNE DE VALEUR */}
      <section className="pr_section soft">
        <div className="pr_wrap">
          <Head badge="Processus intégré" title="Notre chaîne de valeur" text="Huit étapes maîtrisées, de la recherche fondamentale jusqu'à l'exportation des produits finis." />
          <ol className="pr_steps">
            {STEPS.map(([icon, title, text], i) => (
              <li key={title} data-aos="fade-up" data-aos-delay={i * 50}>
                <div className="pr_step-dot"><i className={`bi bi-${icon}`}></i><span>{i + 1}</span></div>
                <h3>{title}</h3><p>{text}</p>
              </li>
            ))}
          </ol>
        </div>
      </section>

      {/* 5. NOS VALEURS */}
      <section className="pr_section">
        <div className="pr_wrap">
          <Head badge="Nos valeurs" title="Ce qui guide chacune de nos décisions" text="Des principes forts qui structurent notre croissance et notre impact." />
          <div className="pr_grid3">
            {VALUES.map(([icon, title, text], i) => (
              <article className="pr_card value" key={title} data-aos="fade-up" data-aos-delay={i * 60}>
                <div className="pr_ico gold"><i className={`bi bi-${icon}`}></i></div>
                <h3>{title}</h3><p>{text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      {/* 6. NOTRE IMPACT */}
      <section className="pr_section pr_impact">
        <div className="pr_wrap">
          <Head badge="Notre impact" title="Des résultats mesurables" text="Un impact concret sur l'économie rurale, la santé naturelle et l'emploi." />
          <div className="pr_grid3">
            {impact.map((f) => <Figure key={f.etiquette} icon={f.icone} value={f.valeur} unit={UNITS[f.unite] ?? f.unite} label={f.label} text={f.description} />)}
            <Figure icon="globe-europe-africa" value="EAC" unit="+ Afrique" label="Pays ciblés" text="Pénétration des marchés régionaux et internationaux à forte valeur ajoutée." />
          </div>
        </div>
      </section>

      {/* 7. PARTENAIRES STRATÉGIQUES & ACADÉMIQUES */}
      <section className="pr_section soft pr_partners">
        <div className="pr_wrap">
          <Head icon="bi-people" badge="Réseau & Collaboration" title="Nos Partenaires Stratégiques & Académiques"
                text="Nos alliances mondiales avec des universités, institutions et organismes de premier plan." />
        </div>
        <Partners />
      </section>

      {/* 8. POURQUOI CHOISIR NUFOTEC ? */}
      <section className="pr_section">
        <div className="pr_wrap pr_why">
          <div className="pr_who-img" data-aos="fade-right">
            <img src={imgWhy} alt="Équipe médicale et scientifique NUFOTEC" onError={(e) => { e.currentTarget.src = '/logo.jpeg' }} />
          </div>
          <div data-aos="fade-left">
            <span className="pr_badge">Notre différence</span>
            <h2 className="pr_h2">Pourquoi choisir NUFOTEC ?</h2>
            <ul className="pr_why-list">
              {WHY.map(([icon, title, text]) => (
                <li key={title}><div className="pr_ico sm"><i className={`bi bi-${icon}`}></i></div><div><h3>{title}</h3><p>{text}</p></div></li>
              ))}
            </ul>
          </div>
        </div>
      </section>

      {/* 9. APPEL À L'ACTION */}
      <section className="pr_section pr_cta-section">
        <div className="pr_wrap">
          <div className="pr_cta" data-aos="fade-up">
            <span className="pr_badge light">Rejoignez-nous</span>
            <h2>Construisons ensemble l'avenir de la santé naturelle en Afrique.</h2>
            <p>Investissez dans une vision d'envergure internationale, devenez courtier contractuel, ou découvrez dès aujourd'hui nos produits de santé
              naturelle d'exception.</p>
            <div className="pr_btns center">
              <Link to={ROUTES.products} className="pr_btn white"><i className="bi bi-box-seam"></i> Découvrir les produits</Link>
              <Link to={ROUTES.investor} className="pr_btn gold"><i className="bi bi-graph-up-arrow"></i> Investir</Link>
              <Link to={ROUTES.contact} className="pr_btn ghost"><i className="bi bi-envelope"></i> Nous contacter</Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
