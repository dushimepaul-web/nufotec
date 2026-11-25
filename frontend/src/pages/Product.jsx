// Fiche produit — conversion de sections/ProductDetail_View.php : mêmes sections (mini-bandeau, image avec zoom,
// informations et boutons Commander sur WhatsApp / Partager, produits similaires, « produit non trouvé »)
// et mêmes fenêtres (commande, partage, zoom), au design du site (couleurs du logo, aucun dégradé, mobile d'abord).
import { useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import api from '../lib/api'
import { PRODUCT_FALLBACK, fallback, formatDate, formatPrice, formatUsd } from '../lib/format'
import { ROUTES } from '../lib/routes'
import { usePageMeta, useSite } from '../lib/site'
import { isNetworkFailure, localWhatsAppUrl, openIn } from '../lib/whatsapp'
import { keyFor, queue } from '../shared/outbox'
import useApi from '../lib/useApi'
import { LIMITS, cleanCity, cleanName, cleanQuantity, cleanText, trimAll } from '../lib/sanitize'
import { BURUNDI_ID, DEFAULT_COUNTRIES, fullPhone } from '../lib/phone'
import CountrySelect from '../ui/CountrySelect'
import PhoneField from '../ui/PhoneField'
import Modal from '../ui/Modal'
import ProductCard from '../ui/ProductCard'
import '../styles/product.css'


/** Description de la base : paragraphes séparés par une ligne vide, lignes « - » ou « * » en liste */
function DescriptionBlocks({ text }) {
  const blocks = (text || '').replace(/\r/g, '').split(/\n\s*\n/).map((b) => b.trim()).filter(Boolean)
  return blocks.map((block, i) => {
    const lines = block.split('\n').map((l) => l.trim()).filter(Boolean)
    const bullets = block.includes('*') && !block.includes('\n') ? block.split('*').map((s) => s.trim()).filter(Boolean) : null
    if (bullets && bullets.length > 1) return <ul key={i}>{bullets.map((l) => <li key={l}>{l}</li>)}</ul>
    if (lines.every((l) => /^[-*•]/.test(l))) {
      return <ul key={i}>{lines.map((l) => <li key={l}>{l.replace(/^[-*•]\s*/, '').replace(/;$/, '')}</li>)}</ul>
    }
    return <p key={i}>{lines.join(' ')}</p>
  })
}

/** Ouvre un lien renvoyé par l'API dans un nouvel onglet sans être bloqué (onglet ouvert pendant le clic) */
async function openFromApi(getUrl) {
  const tab = window.open('', '_blank')
  try {
    const url = await getUrl()
    if (tab) tab.location.href = url
    else window.location.href = url
  } catch (err) {
    tab?.close()
    throw err
  }
}


function OrderModal({ product, open, onClose }) {
  const { settings } = useSite()
  const countries = useApi(open ? '/pays' : null)
  const countryList = countries.data ?? DEFAULT_COUNTRIES
  const empty = { nom_complet: '', telephone: '', pays_id: BURUNDI_ID, ville: 'Bujumbura', adresse: '', quantite: 1, remarques: '', site_web: '' }
  const [form, setForm] = useState(empty)
  const [state, setState] = useState({ sending: false, error: null, done: null })
  // Chaque champ n'accepte que les caractères prévus (voir lib/sanitize.js) ; l'API revalide tout de son côté
  const FILTERS = {
    nom_complet: cleanName, ville: cleanCity, adresse: (v) => cleanText(v, LIMITS.address),
    remarques: (v) => cleanText(v, LIMITS.note), quantite: cleanQuantity,
  }
  const set = (k) => (e) => {
    const value = FILTERS[k] ? FILTERS[k](e.target.value) : e.target.value
    setForm((f) => ({ ...f, [k]: value }))
  }

  const close = () => { onClose(); setTimeout(() => setState({ sending: false, error: null, done: null }), 200) }

  const submit = async (e) => {
    e.preventDefault()
    setState({ sending: true, error: null, done: null })
    // L'onglet WhatsApp est ouvert PENDANT le clic (sinon le navigateur le bloque), puis dirigé vers le lien
    // renvoyé par l'API une fois la commande enregistrée : WhatsApp s'ouvre directement avec le message prêt.
    const tab = window.open('', '_blank')
    const url = `/produits/${product.slug}/commander`
    const body = {
      ...trimAll(form), telephone: fullPhone(form.telephone, country), pays_id: Number(form.pays_id),
      quantite: Number(form.quantite) || 1, remarques: form.remarques || null,
    }
    const key = keyFor(url, body)
    try {
      const data = await api.post(url, body, { key })
      setState({ sending: false, error: null, done: { ...data, opened: true } })
      setForm(empty)
      // Nouvel onglet bloqué (navigateur, téléphone) : la page elle-même va sur WhatsApp — l'appli s'ouvre directement.
      // La commande est déjà enregistrée, rien n'est perdu.
      openIn(tab, data.whatsapp.url)
    } catch (err) {
      if (isNetworkFailure(err)) {
        // Connexion coupée : la commande part sur WhatsApp et sera enregistrée automatiquement au retour du réseau
        queue(url, body, key, `Commande : ${product.titre}`)
        const wa = localWhatsAppUrl(settings, [
          `Bonjour NUFOTEC BURUNDI, je souhaite commander :`, `• ${product.titre} × ${body.quantite}`, '',
          `Nom : ${body.nom_complet}`, `Téléphone : ${body.telephone}`, `Pays : ${country?.pays ?? ''}`,
          `Ville : ${body.ville || ''}`, body.adresse ? `Adresse : ${body.adresse}` : null, body.remarques ? `Remarques : ${body.remarques}` : null,
        ])
        setState({ sending: false, error: null, done: { whatsapp: { url: wa }, opened: true, queued: true } })
        setForm(empty)
        openIn(tab, wa)
        return
      }
      tab?.close()
      setState({ sending: false, error: err, done: null })
    }
  }

  const fieldError = (name) => state.error?.field?.(name)
  const done = state.done
  const country = countryList.find((c) => c.id === Number(form.pays_id))
  const chooseCountry = (id) => setForm((f) => ({
    ...f,
    pays_id: id,
    // « Bujumbura » n'a de sens que pour le Burundi
    ville: id !== BURUNDI_ID && f.ville === 'Bujumbura' ? '' : (id === BURUNDI_ID && !f.ville ? 'Bujumbura' : f.ville),
  }))

  return (
    <Modal open={open} onClose={close} size="lg" icon="bi-whatsapp" title={done ? (done.queued ? 'Commande envoyée sur WhatsApp' : 'Commande enregistrée') : 'Finaliser votre commande'}>
      <div className="pd_order-product">
        <img loading="lazy" decoding="async" src={product.image || PRODUCT_FALLBACK} alt="" onError={fallback(PRODUCT_FALLBACK)} />
        <div>
          <b>{product.titre}</b>
          <span>{formatPrice(product)}</span>
        </div>
      </div>

      {done ? (
        <div className="pd_order-done">
          <i className="bi bi-check-circle"></i>
          <h4>{done.queued ? 'Merci, votre commande est prête' : 'Merci, votre commande est enregistrée'}</h4>
          {done.commande?.numero && <p>Numéro de commande : <b>{done.commande.numero}</b></p>}
          {done.queued && <p className="pd_queued"><i className="bi bi-cloud-arrow-up"></i> Connexion trop faible : votre commande est gardée sur votre téléphone et sera enregistrée automatiquement dès le retour du réseau.</p>}
          <p>{done.opened
            ? "WhatsApp s'est ouvert avec votre message déjà prêt : appuyez sur Envoyer. Un conseiller vous répond pour confirmer le prix, la livraison et le paiement."
            : 'Dernière étape : envoyez-la sur WhatsApp. Le message est déjà prêt, un conseiller vous répond pour confirmer le prix, la livraison et le paiement.'}</p>
          <a className="pd_btn whatsapp lg" href={done.whatsapp.url} target="_blank" rel="noopener noreferrer">
            <i className="bi bi-whatsapp"></i> {done.opened ? 'Rouvrir WhatsApp' : 'Envoyer sur WhatsApp'}
          </a>
          <button type="button" className="pd_link" onClick={close}>Fermer</button>
        </div>
      ) : (
        <form onSubmit={submit} className="pd_form" noValidate={false}>
          {state.error && !fieldError('nom_complet') && !fieldError('telephone') && (
            <div className="pd_alert" role="alert">{state.error.message}</div>
          )}
          <input type="text" value={form.site_web} onChange={set('site_web')} tabIndex={-1} autoComplete="off" aria-hidden="true" className="pd_trap" />
          {/* Le pays d'abord : l'indicatif du téléphone se remplit selon le pays choisi */}
          <div className="pd_row">
            {/* div et non label : un clic dans la liste ne doit pas redonner le focus au champ (la liste se rouvrirait) */}
            <div className="pd_field" role="group" aria-label="Pays">
              <span>Pays <em>*</em></span>
              <CountrySelect required countries={countryList} value={form.pays_id} onChange={chooseCountry} />
            </div>
            <label><span>Téléphone WhatsApp <em>*</em></span>
              <PhoneField required autoFocus country={country} value={form.telephone}
                          onChange={(v) => setForm((f) => ({ ...f, telephone: v }))} error={fieldError('telephone')} />
            </label>
          </div>
          <div className="pd_row">
            <label><span>Nom complet <em>*</em></span>
              <input required minLength={2} maxLength={LIMITS.name} value={form.nom_complet} onChange={set('nom_complet')} placeholder="Votre nom et prénom" autoComplete="name" />
              {fieldError('nom_complet') && <small className="err">{fieldError('nom_complet')}</small>}
            </label>
            <label><span>Ville <em>*</em></span>
              <input required minLength={2} maxLength={LIMITS.city} value={form.ville} onChange={set('ville')} placeholder="Votre ville" autoComplete="address-level2" />
            </label>
          </div>
          <label><span>Adresse de livraison <em>*</em></span>
            <textarea required minLength={3} maxLength={LIMITS.address} rows={2} value={form.adresse} onChange={set('adresse')} placeholder="Quartier, rue, numéro, point de repère…" autoComplete="street-address" />
            {fieldError('adresse') && <small className="err">{fieldError('adresse')}</small>}
          </label>
          <div className="pd_row">
            <label><span>Quantité</span>
              <input type="number" inputMode="numeric" min={1} max={LIMITS.quantity} value={form.quantite} onChange={set('quantite')} />
            </label>
            <label><span>Remarque (facultatif)</span>
              <input maxLength={LIMITS.note} value={form.remarques} onChange={set('remarques')} placeholder="Heure de livraison, précision…" />
            </label>
          </div>
          <div className="pd_form-foot">
            <button type="button" className="pd_btn ghost" onClick={close}>Annuler</button>
            <button type="submit" className="pd_btn whatsapp" disabled={state.sending}>
              {state.sending ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-whatsapp"></i>} Envoyer la commande
            </button>
          </div>
        </form>
      )}
    </Modal>
  )
}

function ShareModal({ product, open, onClose }) {
  const [copied, setCopied] = useState(false)
  const url = typeof window !== 'undefined' ? window.location.href : ''
  const text = `${product.titre} — ${formatPrice(product)} | NUFOTEC BURUNDI`
  const e = encodeURIComponent
  const links = [
    ['WhatsApp', 'bi-whatsapp', '#25d366', `https://wa.me/?text=${e(`${text}\n${url}`)}`],
    ['Facebook', 'bi-facebook', '#1877f2', `https://www.facebook.com/sharer/sharer.php?u=${e(url)}`],
    ['X (Twitter)', 'bi-twitter-x', '#111', `https://twitter.com/intent/tweet?text=${e(text)}&url=${e(url)}`],
    ['LinkedIn', 'bi-linkedin', '#0a66c2', `https://www.linkedin.com/sharing/share-offsite/?url=${e(url)}`],
    ['Telegram', 'bi-telegram', '#229ed9', `https://t.me/share/url?url=${e(url)}&text=${e(text)}`],
    ['Email', 'bi-envelope', '#56645a', `mailto:?subject=${e(product.titre)}&body=${e(`${text}\n${url}`)}`],
  ]
  const copy = async () => {
    try { await navigator.clipboard.writeText(url) } catch { /* presse-papiers indisponible : l'utilisateur copie à la main */ }
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }
  return (
    <Modal open={open} onClose={onClose} icon="bi-share" title="Partager ce produit">
      <div className="pd_share-head">
        <img loading="lazy" decoding="async" src={product.image || PRODUCT_FALLBACK} alt="" onError={fallback(PRODUCT_FALLBACK)} />
        <b>{product.titre}</b>
        <span>{formatPrice(product)}</span>
      </div>
      <div className="pd_share-grid">
        {links.map(([name, icon, color, href]) => (
          <a key={name} href={href} target="_blank" rel="noopener noreferrer" style={{ '--c': color }}><i className={`bi ${icon}`}></i>{name}</a>
        ))}
        {typeof navigator !== 'undefined' && navigator.share && (
          <button type="button" style={{ '--c': '#56645a' }} onClick={() => navigator.share({ title: product.titre, text, url }).catch(() => {})}>
            <i className="bi bi-three-dots"></i>Autres applis
          </button>
        )}
      </div>
      <label className="pd_share-link">Lien du produit
        <div>
          <input readOnly value={url} onFocus={(ev) => ev.target.select()} />
          <button type="button" onClick={copy}>{copied ? <><i className="bi bi-check2"></i> Copié</> : <><i className="bi bi-copy"></i> Copier</>}</button>
        </div>
      </label>
      <p className="pd_share-note"><i className="bi bi-heart"></i> Vous connaissez quelqu'un à qui ce produit pourrait être utile ? Partagez-le avec lui.</p>
    </Modal>
  )
}

export default function Product() {
  const { slug } = useParams()
  const { data: product, loading, error } = useApi(`/produits/${slug}`, undefined, [slug])
  const images = useMemo(() => {
    if (!product) return []
    const all = [product.image, ...(product.galerie ?? []).map((g) => g.url)].filter(Boolean)
    return all.length ? [...new Set(all)] : [PRODUCT_FALLBACK]
  }, [product])
  const [active, setActive] = useState(0)
  const [modal, setModal] = useState(null) // 'order' | 'share' | 'zoom'
  const [priceError, setPriceError] = useState('')
  usePageMeta(product?.titre ?? (error ? 'Produit non trouvé' : 'Produit'), product?.extrait)

  useEffect(() => { setActive(0); setModal(null) }, [slug])

  if (loading) {
    return (
      <div className="pd"><div className="pd_wrap pd_loading"><span className="spinner-border text-success"></span></div></div>
    )
  }

  if (error || !product) {
    return (
      <div className="pd">
        <div className="pd_wrap pd_notfound">
          <i className="bi bi-box-seam"></i>
          <h1>{error?.status === 404 ? 'Produit non trouvé' : 'Erreur de chargement'}</h1>
          <p>{error?.status === 404 ? "Le produit que vous recherchez n'existe pas ou a été retiré." : error?.message}</p>
          <Link to={ROUTES.products} className="pd_btn primary"><i className="bi bi-arrow-left"></i> Retour à la boutique</Link>
        </div>
      </div>
    )
  }

  const askPrice = async () => {
    setPriceError('')
    try {
      await openFromApi(async () => (await api.post(`/produits/${product.slug}/demande-prix`)).url)
    } catch (err) {
      setPriceError(err.message)
    }
  }

  return (
    <div className="pd">
      {/* 1. MINI-BANDEAU */}
      <section className="pd_banner">
        <img className="pd_banner-bg" src="/logo.jpeg" alt="" />
        <div className="pd_wrap">
          <nav className="pd_crumbs" aria-label="Fil d'Ariane">
            <Link to={ROUTES.home}>Accueil</Link> <i className="bi bi-chevron-right"></i>
            <Link to={ROUTES.products}>Boutique</Link> <i className="bi bi-chevron-right"></i>
            <span>{product.titre}</span>
          </nav>
        </div>
      </section>

      {/* 2. PRODUIT : image + informations */}
      <div className="pd_wrap">
        <section className="pd_main">
          <div className="pd_gallery">
            <div className="pd_image">
              {product.nouveau && <span className="pd_badge new">Nouveau</span>}
              {product.vedette && <span className="pd_badge star"><i className="bi bi-star"></i> Vedette</span>}
              <button type="button" className="pd_image-btn" onClick={() => setModal('zoom')} aria-label="Agrandir l'image">
                <img src={images[active]} alt={product.titre} onError={fallback(PRODUCT_FALLBACK)} />
              </button>
              <button type="button" className="pd_zoom" onClick={() => setModal('zoom')}><i className="bi bi-zoom-in"></i> Zoom</button>
            </div>
            {images.length > 1 && (
              <div className="pd_thumbs">
                {images.map((src, i) => (
                  <button key={src} type="button" className={i === active ? 'active' : ''} onClick={() => setActive(i)} aria-label={`Image ${i + 1}`}>
                    <img loading="lazy" decoding="async" src={src} alt="" onError={fallback(PRODUCT_FALLBACK)} />
                  </button>
                ))}
              </div>
            )}
          </div>

          <div className="pd_info">
            {product.categorie && <Link to={`${ROUTES.products}?categorie=${product.categorie.slug}`} className="pd_cat">{product.categorie.nom.toLowerCase()}</Link>}
            <h1>{product.titre}</h1>

            <div className="pd_price-box">
              <div>
                <div className="pd_price">{formatPrice(product)}</div>
                <div className="pd_price-alt">{formatUsd(product)}</div>
              </div>
              <span className={`pd_stock${product.en_stock ? '' : ' out'}`}>
                {product.en_stock ? (product.stock ? `${product.stock} en stock` : 'En stock') : 'Épuisé'}
              </span>
            </div>
            <button type="button" className="pd_price-ask" onClick={askPrice}>
              <i className="bi bi-info-circle"></i> Les prix peuvent changer : demander le prix actualisé sur WhatsApp
            </button>
            {priceError && <small className="pd_err">{priceError}</small>}

            <div className="pd_ctas">
              <button type="button" className="pd_btn whatsapp lg" disabled={!product.en_stock} onClick={() => setModal('order')}>
                <i className="bi bi-whatsapp"></i> Commander sur WhatsApp
              </button>
              <button type="button" className="pd_btn outline lg" onClick={() => setModal('share')}>
                <i className="bi bi-share"></i> Partager
              </button>
            </div>

            <ul className="pd_assure">
              <li><i className="bi bi-chat-dots"></i> Un conseiller confirme votre commande sur WhatsApp</li>
              <li><i className="bi bi-flower1"></i> Produit à base de plantes médicinales</li>
              {product.created_at && <li><i className="bi bi-calendar3"></i> En boutique depuis le {formatDate(product.created_at)}</li>}
            </ul>

            {product.produit_sante && (
              <div className="pd_health">
                <i className="bi bi-heart-pulse"></i>
                <div>
                  <p>{product.avertissement_sante}</p>
                  {product.proposer_consultation && (
                    <Link to={ROUTES.doctors}>Consulter un expert en ligne <i className="bi bi-arrow-right"></i></Link>
                  )}
                </div>
              </div>
            )}
          </div>
        </section>

        {/* Description complète */}
        {product.description && (
          <section className="pd_desc">
            <h2><i className="bi bi-info-circle"></i> Description</h2>
            <div className="pd_desc-text"><DescriptionBlocks text={product.description} /></div>
          </section>
        )}

        {/* 3. PRODUITS SIMILAIRES */}
        {product.similaires?.length > 0 && (
          <section className="pd_similar">
            <div className="pd_similar-head">
              <h2>Produits similaires</h2>
              <Link to={ROUTES.products}>Toute la boutique <i className="bi bi-arrow-right"></i></Link>
            </div>
            <div className="pcard-grid">
              {product.similaires.slice(0, 4).map((p) => <ProductCard key={p.uuid} product={p} animate={false} />)}
            </div>
          </section>
        )}
      </div>

      {/* Barre d'achat fixée en bas sur mobile (au-dessus de la navigation du bas) */}
      <div className="pd_sticky">
        <div>
          <b>{formatPrice(product)}</b>
          <span>{product.titre}</span>
        </div>
        <button type="button" className="pd_btn whatsapp" disabled={!product.en_stock} onClick={() => setModal('order')}>
          <i className="bi bi-whatsapp"></i> Commander
        </button>
      </div>

      <OrderModal product={product} open={modal === 'order'} onClose={() => setModal(null)} />
      <ShareModal product={product} open={modal === 'share'} onClose={() => setModal(null)} />
      <Modal open={modal === 'zoom'} onClose={() => setModal(null)} size="xl" dark>
        <img loading="lazy" decoding="async" className="pd_zoom-img" src={images[active]} alt={product.titre} onError={fallback(PRODUCT_FALLBACK)} />
      </Modal>
    </div>
  )
}
