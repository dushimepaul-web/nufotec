// Contact — conversion de Home_page/contact_us_view.php : mêmes sections (bandeau ; colonne infos : adresse, carte et
// itinéraire, téléphone, email, horaires avec « ouvert / fermé » ; formulaire avec consentement et politique de
// confidentialité ; remerciement). Préférences appliquées : pays avec drapeau, indicatif automatique, champs filtrés
// (navigateur et API), WhatsApp ouvert directement avec le message prêt, carte chargée seulement à la demande.
import { useEffect, useState } from 'react'
import api from '../lib/api'
import { isNetworkFailure, localWhatsAppUrl, openIn } from '../lib/whatsapp'
import { keyFor, queue } from '../shared/outbox'
import { BURUNDI_ID, DEFAULT_COUNTRIES, fullPhone } from '../lib/phone'
import { cleanCity, cleanName, cleanText } from '../lib/sanitize'
import { usePageMeta, useSite } from '../lib/site'
import useApi from '../lib/useApi'
import CountrySelect from '../ui/CountrySelect'
import Modal from '../ui/Modal'
import PageBanner from '../ui/PageBanner'
import PhoneField from '../ui/PhoneField'
import '../styles/contact.css'

const DAYS = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi']
const LIMIT = { nom: 100, email: 150, lieu: 100, sujet: 150, message: 3000 }

/** « Dimanche - Vendredi: 8h00 - 17h00 » → ouvert / fermé maintenant, à l'heure du Burundi */
function openNow(hours) {
  const m = (hours || '').toLowerCase().match(/([a-zéû]+)\s*-\s*([a-zéû]+)\s*:?\s*(\d{1,2})h(\d{0,2})\s*-\s*(\d{1,2})h(\d{0,2})/)
  if (!m) return null
  const from = DAYS.indexOf(m[1])
  const to = DAYS.indexOf(m[2])
  if (from < 0 || to < 0) return null
  const parts = Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Africa/Bujumbura', weekday: 'long', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' })
    .formatToParts(new Date()).map((p) => [p.type, p.value]))
  const day = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'].indexOf(parts.weekday.toLowerCase())
  const minutes = Number(parts.hour) * 60 + Number(parts.minute)
  const inDays = from <= to ? day >= from && day <= to : day >= from || day <= to
  const start = Number(m[3]) * 60 + Number(m[4] || 0)
  const end = Number(m[5]) * 60 + Number(m[6] || 0)
  return inDays && minutes >= start && minutes < end
}

function OpenBadge({ hours }) {
  const [open] = useState(() => openNow(hours))
  if (open === null) return null
  return <span className={`ct_status ${open ? 'open' : 'closed'}`}><i className="bi bi-circle-fill"></i> {open ? 'Ouvert maintenant' : 'Fermé pour le moment'}</span>
}

export default function Contact() {
  usePageMeta('Contact', 'Contactez NUFOTEC BURUNDI : une question, un projet, une suggestion ? Réponse sous 24 heures.')
  const { settings } = useSite()
  const countries = useApi('/pays')
  const countryList = countries.data ?? DEFAULT_COUNTRIES
  const empty = { nom: '', email: '', telephone: '', pays_id: BURUNDI_ID, lieu: '', sujet: '', message: '', site_web: '' }
  const [form, setForm] = useState(empty)
  const [consent, setConsent] = useState(false)
  const [tried, setTried] = useState(false)
  const [state, setState] = useState({ sending: false, error: null, done: null })
  const [privacy, setPrivacy] = useState(false)
  const [mapOn, setMapOn] = useState(false)
  const [copied, setCopied] = useState(false)

  const address = settings.adresse_siege || 'Bujumbura, Burundi'
  const phone = settings.site_phone || settings.contact_whatsapp || ''
  const email = settings.contact_email_invest || ''
  const hours = settings.horaires_travail || ''
  const waNumber = (settings.contact_whatsapp || phone).replace(/\D/g, '')
  const q = encodeURIComponent(address)
  const country = countryList.find((c) => c.id === Number(form.pays_id))

  useEffect(() => {
    if (!copied) return undefined
    const t = setTimeout(() => setCopied(false), 2000)
    return () => clearTimeout(t)
  }, [copied])

  // Chaque champ n'accepte que les caractères prévus ; l'API revalide tout de son côté
  const FILTERS = {
    nom: (v) => cleanName(v).slice(0, LIMIT.nom),
    email: (v) => v.replace(/[^\w.@+-]/g, '').slice(0, LIMIT.email),
    lieu: (v) => cleanCity(v).slice(0, LIMIT.lieu),
    sujet: (v) => cleanText(v, LIMIT.sujet).replace(/\n/g, ' '),
    message: (v) => cleanText(v, LIMIT.message),
  }
  const set = (k) => (e) => {
    const value = FILTERS[k] ? FILTERS[k](e.target.value) : e.target.value
    setForm((f) => ({ ...f, [k]: value }))
  }

  const problems = {
    nom: form.nom.trim().length < 2 && 'Votre nom complet (2 caractères au moins)',
    email: !/^[^@\s]+@[^@\s]+\.[a-z]{2,}$/i.test(form.email.trim()) && 'Une adresse email valide',
    telephone: form.telephone.replace(/\D/g, '').length < 6 && 'Votre numéro WhatsApp',
    sujet: form.sujet.trim().length < 3 && "L'objet (3 caractères au moins)",
    message: form.message.trim().length < 10 && 'Votre message (10 caractères au moins)',
    consent: !consent && 'Votre accord pour le traitement de vos données',
  }
  const show = (k) => (tried ? problems[k] : null) || state.error?.field?.(k === 'consent' ? '' : k)

  const submit = async (e) => {
    e.preventDefault()
    setTried(true)
    if (Object.values(problems).some(Boolean)) return
    setState({ sending: true, error: null, done: null })
    // L'onglet WhatsApp est ouvert PENDANT le clic (sinon le navigateur le bloque), puis dirigé vers le lien prêt
    const tab = window.open('', '_blank')
    const body = {
      nom: form.nom.trim(), email: form.email.trim().toLowerCase(), telephone: fullPhone(form.telephone, country),
      pays_id: Number(form.pays_id), lieu: form.lieu.trim() || null, sujet: form.sujet.trim(),
      message: form.message.trim(), site_web: form.site_web,
    }
    const key = keyFor('/contact', body)
    const reset = () => { setForm(empty); setConsent(false); setTried(false) }
    try {
      const data = await api.post('/contact', body, { key })
      const url = data?.whatsapp?.url
      setState({ sending: false, error: null, done: { name: form.nom.trim().split(' ')[0], url } })
      reset()
      if (url) openIn(tab, url); else tab?.close()
    } catch (err) {
      if (isNetworkFailure(err)) {
        // Connexion coupée : le message part quand même sur WhatsApp et sera enregistré au retour du réseau
        queue('/contact', body, key, 'Message de contact')
        const url = localWhatsAppUrl(settings, [
          `Bonjour NUFOTEC BURUNDI, je suis ${body.nom}.`, `Sujet : ${body.sujet}`, body.message, '',
          `Pays : ${country?.pays ?? ''}`, `Téléphone : ${body.telephone}`, `E-mail : ${body.email}`,
        ])
        setState({ sending: false, error: null, done: { name: form.nom.trim().split(' ')[0], url, queued: true } })
        reset()
        openIn(tab, url)
        return
      }
      tab?.close()
      setState({ sending: false, error: err, done: null })
    }
  }

  const copyPhone = async () => {
    try { await navigator.clipboard.writeText(phone); setCopied(true) } catch { window.location.href = `tel:${phone.replace(/\s/g, '')}` }
  }
  const done = state.done

  return (
    <div className="ct">
      {/* 1. BANDEAU */}
      <PageBanner crumbs={[['Contact']]} badge={<><i className="bi bi-chat-dots"></i> Nous écrire</>} icon="bi-envelope-paper"
                  title="Contactez-nous" subtitle="Une question, un projet, une suggestion ? Écrivez-nous : notre équipe vous répond sous 24 heures." />

      <div className="ct_wrap">
        {/* 2. COLONNE INFOS */}
        <aside className="ct_info">
          <div className="ct_brand">
            <img loading="lazy" decoding="async" src="/logo.jpeg" alt="" />
            <div><h2>{settings.site_name || 'NUFOTEC BURUNDI'}</h2><span>{settings.span_site_name || 'Natural Health'}</span></div>
          </div>
          <p className="ct_addr"><i className="bi bi-geo-alt-fill"></i> {address}</p>

          <div className="ct_cards">
            <div className="ct_card">
              <div className="ct_ico"><i className="bi bi-telephone-fill"></i></div>
              <div className="ct_txt">
                <h4>Téléphone</h4>
                <button type="button" className="ct_copy" onClick={copyPhone}>{phone}</button>
                <small>{copied ? <><i className="bi bi-check2"></i> Numéro copié</> : 'Toucher pour copier'}</small>
                <div className="ct_quick">
                  <a href={`tel:${phone.replace(/\s/g, '')}`}><i className="bi bi-telephone"></i> Appeler</a>
                  {waNumber && <a className="wa" href={`https://wa.me/${waNumber}`} target="_blank" rel="noopener noreferrer"><i className="bi bi-whatsapp"></i> WhatsApp</a>}
                </div>
              </div>
            </div>
            {email && (
              <a className="ct_card" href={`mailto:${email}`}>
                <div className="ct_ico"><i className="bi bi-envelope-fill"></i></div>
                <div className="ct_txt"><h4>Email</h4><p>{email}</p><small>Toucher pour écrire un email</small></div>
              </a>
            )}
            {hours && (
              <div className="ct_card">
                <div className="ct_ico"><i className="bi bi-clock-fill"></i></div>
                <div className="ct_txt">
                  <h4>Horaires</h4><p>{hours}</p>
                  <OpenBadge key={hours} hours={hours} />
                </div>
              </div>
            )}
          </div>
        </aside>

        {/* 3. FORMULAIRE */}
        <section className="ct_form-col" aria-label="Formulaire de contact">
          {done ? (
            /* 4. REMERCIEMENT */
            <div className="ct_done">
              <div className="ct_done-ico"><i className="bi bi-send-check"></i></div>
              <h2>Merci {done.name} !</h2>
              {done.queued
                ? <p className="ct_queued"><i className="bi bi-cloud-arrow-up"></i> Connexion trop faible : votre message est gardé sur votre téléphone et sera enregistré automatiquement dès le retour du réseau.</p>
                : <p>Votre message est bien reçu : notre équipe vous répond sous 24 heures.</p>}
              {done.url && (
                <>
                  <p className="ct_muted">WhatsApp s'est ouvert avec votre message déjà prêt : appuyez sur Envoyer pour une réponse plus rapide.</p>
                  <a className="ct_btn wa" href={done.url} target="_blank" rel="noopener noreferrer"><i className="bi bi-whatsapp"></i> Rouvrir WhatsApp</a>
                </>
              )}
              <button type="button" className="ct_link" onClick={() => setState({ sending: false, error: null, done: null })}>Écrire un autre message</button>
            </div>
          ) : (
            <>
              <div className="ct_form-head">
                <h2>Écrivez-nous</h2>
                <p>Remplissez le formulaire : votre message part chez nous et sur WhatsApp, prêt à envoyer.</p>
              </div>
              <form className="ct_form" onSubmit={submit} noValidate>
                <input type="text" value={form.site_web} onChange={set('site_web')} tabIndex={-1} autoComplete="off" aria-hidden="true" className="ct_trap" />
                {state.error && !state.error.field?.('telephone') && <div className="ct_alert" role="alert">{state.error.message}</div>}

                <label className={`ct_field ${show('nom') ? 'bad' : ''}`}>
                  <span>Nom complet *</span>
                  <div className="ct_input"><i className="bi bi-person"></i>
                    <input value={form.nom} onChange={set('nom')} maxLength={LIMIT.nom} autoComplete="name" placeholder="Ex. Jean Ndayishimiye" /></div>
                  {show('nom') && <small className="err">{show('nom')}</small>}
                </label>
                <label className={`ct_field ${show('email') ? 'bad' : ''}`}>
                  <span>Email *</span>
                  <div className="ct_input"><i className="bi bi-envelope"></i>
                    <input type="email" inputMode="email" value={form.email} onChange={set('email')} maxLength={LIMIT.email} autoComplete="email" placeholder="nom@exemple.com" /></div>
                  {show('email') && <small className="err">{show('email')}</small>}
                </label>
                <div className="ct_row">
                  <div className="ct_field" role="group" aria-label="Pays de résidence">
                    <span>Pays de résidence *</span>
                    <CountrySelect required countries={countryList} value={form.pays_id} onChange={(id) => setForm((f) => ({ ...f, pays_id: id }))} />
                  </div>
                  <label className={`ct_field ${show('telephone') ? 'bad' : ''}`}>
                    <span>Téléphone WhatsApp *</span>
                    <PhoneField required country={country} value={form.telephone} onChange={(v) => setForm((f) => ({ ...f, telephone: v }))}
                                error={show('telephone') || undefined} />
                  </label>
                </div>
                <div className="ct_row">
                  <label className="ct_field">
                    <span>Ville</span>
                    <div className="ct_input"><i className="bi bi-geo-alt"></i>
                      <input value={form.lieu} onChange={set('lieu')} maxLength={LIMIT.lieu} autoComplete="address-level2" placeholder="Ex. Gitega" /></div>
                  </label>
                  <label className={`ct_field ${show('sujet') ? 'bad' : ''}`}>
                    <span>Objet *</span>
                    <div className="ct_input"><i className="bi bi-tag"></i>
                      <input value={form.sujet} onChange={set('sujet')} maxLength={LIMIT.sujet} placeholder="Objet de votre message" /></div>
                    {show('sujet') && <small className="err">{show('sujet')}</small>}
                  </label>
                </div>
                <label className={`ct_field ${show('message') ? 'bad' : ''}`}>
                  <span>Message *</span>
                  <textarea rows={5} value={form.message} onChange={set('message')} maxLength={LIMIT.message} placeholder="Votre message…"></textarea>
                  <small className={`ct_count ${show('message') ? 'err' : ''}`}>{show('message') || `${form.message.length} / ${LIMIT.message}`}</small>
                </label>
                <label className={`ct_check ${show('consent') ? 'bad' : ''}`}>
                  <input type="checkbox" checked={consent} onChange={(e) => setConsent(e.target.checked)} />
                  <span>J'accepte que mes données soient utilisées pour répondre à ma demande, selon la{' '}
                    <button type="button" className="ct_inline" onClick={() => setPrivacy(true)}>politique de confidentialité</button> *</span>
                </label>
                <button type="submit" className="ct_btn primary" disabled={state.sending}>
                  {state.sending ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-send"></i>} Envoyer le message
                </button>
                <p className="ct_note"><i className="bi bi-whatsapp"></i> Après l'envoi, WhatsApp s'ouvre avec votre message prêt.</p>
              </form>
            </>
          )}
        </section>
      </div>

      {/* 5. CARTE (sous le formulaire) */}
      <section className="ct_map-section" aria-label="Carte et itinéraire">
        <h2><i className="bi bi-geo-alt-fill"></i> Nous trouver <small>{address}</small></h2>
        <div className="ct_map">
            {mapOn ? (
              <iframe src={`https://www.google.com/maps?q=${q}&output=embed`} loading="lazy" referrerPolicy="no-referrer-when-downgrade"
                      title={`Localisation : ${address}`}></iframe>
            ) : (
              <button type="button" className="ct_map-cover" onClick={() => setMapOn(true)}>
                <img loading="lazy" decoding="async" src="/logo.jpeg" alt="" />
                <span><i className="bi bi-map"></i> Afficher la carte</span>
              </button>
            )}
            <a className="ct_map-go" href={`https://www.google.com/maps/search/?api=1&query=${q}`} target="_blank" rel="noopener noreferrer">
              <i className="bi bi-signpost-split"></i> Itinéraire
            </a>
          </div>
      </section>

      <Modal open={privacy} onClose={() => setPrivacy(false)} title="Politique de confidentialité" icon="bi-shield-lock"
             footer={<button type="button" className="ct_btn primary" onClick={() => { setConsent(true); setPrivacy(false) }}>J'ai compris</button>}>
        <p>Nous utilisons vos données uniquement pour répondre à votre demande de contact. Elles sont conservées en sécurité et ne sont jamais partagées avec des tiers sans votre accord.</p>
        <p className="mb-0">Vous pouvez demander à les consulter, les corriger ou les supprimer{email ? <> en écrivant à <b>{email}</b></> : ''}.</p>
      </Modal>
    </div>
  )
}
