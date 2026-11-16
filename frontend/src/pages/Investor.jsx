// Devenir investisseur — conversion de pages/investor_form.php : mêmes sections (bandeau, présentation,
// 3 étapes Identité / Intérêts / Confirmation, remerciement), sans compte. Après l'envoi, WhatsApp s'ouvre
// directement avec la demande préparée pour l'équipe NUFOTEC (préférence validée).
import { useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../lib/api'
import { createOrSignIn, getUser, passwordProblem } from '../lib/auth'
import { BURUNDI_ID, DEFAULT_COUNTRIES, fullPhone } from '../lib/phone'
import { ROUTES } from '../lib/routes'
import { LIMITS, cleanName, cleanText } from '../lib/sanitize'
import { usePageMeta } from '../lib/site'
import useApi from '../lib/useApi'
import { submitAndOpenWhatsApp } from '../lib/whatsapp'
import { AccountFields, CheckGrid, Confirm, StepBar, ThankYou } from '../ui/ApplyForm'
import CountrySelect from '../ui/CountrySelect'
import PageBanner from '../ui/PageBanner'
import PhoneField from '../ui/PhoneField'

const STEPS = ['Identité', 'Intérêts', 'Confirmation']
const INTERESTS = [['equity', 'Équité (Equity)'], ['debt', 'Dette structurée'], ['blended_finance', 'Finance mixte'],
  ['grant', 'Grant / Subvention'], ['strategic_partnership', 'Partenariat stratégique'],
  ['technical_collaboration', 'Collaboration technique'], ['offtake_distribution', 'Offtake / Distribution']]
const FOCUS = [['research_lab', 'Labo de recherche'], ['gmp_facility', 'Facility GMP'], ['medicinal_plant', 'Plantes médicinales'],
  ['commercialization', 'Commercialisation'], ['full_platform', 'Plateforme complète']]
const AMOUNTS = [['Below 250K', 'Moins de 250K €'], ['250K-1M', '250K - 1M €'], ['1M-5M', '1M - 5M €'], ['5M+', '5M+ €'], ['To be discussed', 'À discuter']]
const TIMELINES = [['Immediate', 'Immédiat'], ['3-6 months', '3-6 mois'], ['6-12 months', '6-12 mois'], ['Exploratory', 'Exploratoire']]
const label = (list, v) => list.find(([k]) => k === v)?.[1] ?? '—'
const EMAIL = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/

export default function Investor() {
  usePageMeta('Devenir investisseur', "Manifestez votre intérêt pour investir dans NUFOTEC-PHYTOMED Industries.")
  const countries = useApi('/pays')
  const countryList = countries.data ?? DEFAULT_COUNTRIES
  const [step, setStep] = useState(0)
  const [f, setF] = useState({
    pays_id: BURUNDI_ID, telephone: '', nom_complet: '', email: '', organisation: '', fonction: '',
    interets: [], interet_autre: '', montant: '', calendrier: 'Exploratory', domaines: [], message: '',
    accepte_contact: false, confirmation_non_engageante: false, site_web: '',
  })
  const [errors, setErrors] = useState({})
  const [pw, setPw] = useState('')
  const [pw2, setPw2] = useState('')
  const [sending, setSending] = useState(false)
  const [done, setDone] = useState(null)
  const country = countryList.find((c) => c.id === Number(f.pays_id))
  const user = getUser()
  const signedIn = user && user.email?.toLowerCase() === f.email.trim().toLowerCase() ? user : null
  const set = (k, v) => { setF((x) => ({ ...x, [k]: v })); setErrors((e) => ({ ...e, [k]: undefined })) }

  const check = (s) => {
    const e = {}
    if (s === 0) {
      if (cleanName(f.nom_complet).trim().length < 2) e.nom_complet = 'Le nom complet est requis'
      if (!EMAIL.test(f.email.trim())) e.email = f.email.trim() ? 'Email invalide' : "L'email est requis"
      if (f.telephone && f.telephone.replace(/\D/g, '').length < 6) e.telephone = 'Numéro trop court'
    }
    if (s === 2 && !(f.accepte_contact && f.confirmation_non_engageante)) e.confirm = 'Les deux confirmations sont obligatoires'
    if (s === 2 && !signedIn) {
      const problem = passwordProblem(pw, { nomComplet: f.nom_complet, email: f.email.trim() })
      if (problem) e.password = problem
      else if (pw !== pw2) e.password2 = 'Les deux mots de passe ne correspondent pas'
    }
    setErrors(e)
    return Object.keys(e).length === 0
  }
  const go = (s) => { setStep(s); window.scrollTo({ top: (document.querySelector('.ap_steps')?.offsetTop ?? 0) - 140, behavior: 'smooth' }) }

  const submit = async (ev) => {
    ev.preventDefault()
    if (!check(2)) return
    setSending(true)
    try {
      const res = await submitAndOpenWhatsApp(async () => {
        if (!signedIn) {
          await createOrSignIn({ email: f.email.trim(), password: pw, nomComplet: f.nom_complet,
            telephone: fullPhone(f.telephone, country), paysId: Number(f.pays_id), type: 'investisseur' })
        }
        return api.postWithMessage('/investisseurs/manifestation', {
        ...f, nom_complet: f.nom_complet.trim(), email: f.email.trim(), pays_id: Number(f.pays_id),
        telephone: fullPhone(f.telephone, country) || null, montant: f.montant || null,
        organisation: f.organisation.trim() || null, fonction: f.fonction.trim() || null,
        interet_autre: f.interet_autre.trim() || null, message: f.message.trim() || null, site_web: f.site_web || null,
      })
      })
      setDone(res)
      window.scrollTo({ top: 0, behavior: 'smooth' })
    } catch (err) {
      const fields = {}
      for (const x of Array.isArray(err.details) ? err.details : []) if (x.field) fields[x.field] = x.message || 'À vérifier'
      setErrors({ ...fields, _form: err.message })
      go(fields.nom_complet || fields.email || fields.telephone || fields.pays_id ? 0 : fields.message || fields.interet_autre ? 1 : 2)
    } finally {
      setSending(false)
    }
  }

  return (
    <div className="ap">
      <PageBanner crumbs={[['Investissement'], ['Devenir investisseur']]} badge={<><i className="bi bi-graph-up-arrow"></i> Investisseur</>}
                  title="Enregistrez votre intérêt" subtitle="Indiquez-nous vos préférences d'investissement. Notre équipe vous contacte sous 48 h." />
      <div className="ap_wrap">
        {done ? (
          <ThankYou title="Merci pour votre intérêt" message={done.message} whatsappUrl={done.data?.whatsapp?.url} />
        ) : (
          <>
            <div className="ap_intro">
              <i className="bi bi-shield-check"></i>
              <div><b>Manifestation d'intérêt non contraignante</b>
                <p>Aucun compte à créer. Vos informations restent confidentielles et ne servent qu'à préparer nos échanges.</p></div>
            </div>
            <form onSubmit={submit} noValidate>
              <StepBar steps={STEPS} step={step} onGo={go} />
              {errors._form && <div className="ap_alert" role="alert">{errors._form}</div>}
              <input type="text" className="ap_trap" tabIndex={-1} autoComplete="off" aria-hidden="true" value={f.site_web} onChange={(e) => set('site_web', e.target.value)} />

              {step === 0 && (
                <div className="ap_card">
                  <h2><i className="bi bi-person-vcard"></i> Informations personnelles</h2>
                  <div className="ap_row">
                    <div className="ap_field" role="group" aria-label="Pays">
                      <span>Pays <em>*</em></span>
                      <CountrySelect required countries={countryList} value={f.pays_id} onChange={(id) => set('pays_id', id)} />
                    </div>
                    <label className="ap_field"><span>Téléphone / WhatsApp</span>
                      <PhoneField country={country} value={f.telephone} onChange={(v) => set('telephone', v)} error={errors.telephone}
                                  hint="Facultatif : pour échanger sur WhatsApp" />
                    </label>
                  </div>
                  <div className="ap_row">
                    <label className="ap_field"><span>Nom complet <em>*</em></span>
                      <input value={f.nom_complet} maxLength={LIMITS.name} autoComplete="name" placeholder="Jean Dupont" onChange={(e) => set('nom_complet', cleanName(e.target.value))} />
                      {errors.nom_complet && <small className="err">{errors.nom_complet}</small>}
                    </label>
                    <label className="ap_field"><span>Email <em>*</em></span>
                      <input type="email" value={f.email} maxLength={150} autoComplete="email" placeholder="jean@example.com" onChange={(e) => set('email', e.target.value.replace(/\s/g, ''))} />
                      {errors.email && <small className="err">{errors.email}</small>}
                    </label>
                  </div>
                  <div className="ap_row">
                    <label className="ap_field"><span>Organisation</span>
                      <input value={f.organisation} maxLength={150} autoComplete="organization" placeholder="Nom de votre société" onChange={(e) => set('organisation', cleanText(e.target.value, 150))} />
                    </label>
                    <label className="ap_field"><span>Titre / Poste</span>
                      <input value={f.fonction} maxLength={150} autoComplete="organization-title" placeholder="Directeur des investissements" onChange={(e) => set('fonction', cleanText(e.target.value, 150))} />
                    </label>
                  </div>
                </div>
              )}

              {step === 1 && (
                <div className="ap_card">
                  <h2><i className="bi bi-bullseye"></i> Type d'engagement souhaité</h2>
                  <CheckGrid options={INTERESTS} values={f.interets} onChange={(v) => set('interets', v)} />
                  <label className="ap_field"><span>Autre intérêt (préciser)</span>
                    <input value={f.interet_autre} maxLength={255} placeholder="Décrivez votre intérêt spécifique…" onChange={(e) => set('interet_autre', cleanText(e.target.value, 255))} />
                  </label>
                  <h2><i className="bi bi-sliders"></i> Capacité et priorités</h2>
                  <div className="ap_row">
                    <label className="ap_field"><span>Fourchette d'engagement</span>
                      <select value={f.montant} onChange={(e) => set('montant', e.target.value)}>
                        <option value="">Choisir</option>
                        {AMOUNTS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                      </select>
                    </label>
                    <label className="ap_field"><span>Horizon d'investissement</span>
                      <select value={f.calendrier} onChange={(e) => set('calendrier', e.target.value)}>
                        {TIMELINES.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                      </select>
                    </label>
                  </div>
                  <h3>Segments d'intérêt</h3>
                  <CheckGrid options={FOCUS} values={f.domaines} onChange={(v) => set('domaines', v)} />
                  <label className="ap_field"><span>Message stratégique</span>
                    <textarea rows={4} maxLength={2000} value={f.message} placeholder="Votre vision, vos attentes ou vos questions…" onChange={(e) => set('message', cleanText(e.target.value, 2000))} />
                  </label>
                </div>
              )}

              {step === 2 && (
                <div className="ap_card">
                  <h2><i className="bi bi-clipboard-check"></i> Confirmation</h2>
                  <div className="ap_recap">
                    <div><b>{f.nom_complet}</b>{f.organisation && ` · ${f.organisation}`}{f.fonction && ` · ${f.fonction}`}</div>
                    <div>{country?.pays} · {f.email}{f.telephone && ` · ${fullPhone(f.telephone, country)}`}</div>
                    <div>Engagement : {f.interets.map((v) => label(INTERESTS, v)).join(', ') || '—'}</div>
                    <div>Montant : {label(AMOUNTS, f.montant)} · Horizon : {label(TIMELINES, f.calendrier)}</div>
                  </div>
                  <AccountFields email={f.email.trim()} password={pw} confirm={pw2} signedIn={signedIn} errors={errors}
                                 benefit="Votre compte vous permet de suivre votre dossier, de signer l'accord de confidentialité et d'accéder à la data room."
                                 onPassword={(v) => { setPw(v); setErrors((e) => ({ ...e, password: undefined })) }}
                                 onConfirm={(v) => { setPw2(v); setErrors((e) => ({ ...e, password2: undefined })) }} />
                  <Confirm required checked={f.accepte_contact} onChange={(v) => set('accepte_contact', v)} error={Boolean(errors.confirm)}>
                    J'accepte d'être contacté par l'équipe NUFOTEC au sujet de cette opportunité d'investissement.
                  </Confirm>
                  <Confirm required checked={f.confirmation_non_engageante} onChange={(v) => set('confirmation_non_engageante', v)} error={Boolean(errors.confirm)}>
                    Je confirme que cet enregistrement est non contraignant et exploratoire.
                  </Confirm>
                  {errors.confirm && <small className="err">{errors.confirm}</small>}
                </div>
              )}

              <div className="ap_nav">
                <button type="button" className="ap_btn ghost" disabled={step === 0 || sending} onClick={() => go(step - 1)}><i className="bi bi-arrow-left"></i> Précédent</button>
                {step < 2 ? (
                  <button type="button" className="ap_btn primary" onClick={() => check(step) && go(step + 1)}>Suivant <i className="bi bi-arrow-right"></i></button>
                ) : (
                  <button type="submit" className="ap_btn whatsapp" disabled={sending}>
                    {sending ? <><span className="spinner-border spinner-border-sm"></span> Envoi…</> : <><i className="bi bi-whatsapp"></i> Envoyer ma demande</>}
                  </button>
                )}
              </div>
              <p className="text-center mt-3" style={{ fontSize: '.85rem', color: 'var(--ink-soft)' }}>
                Vous êtes courtier ou apporteur d'affaires ? <Link to={ROUTES.broker}>Devenir courtier</Link>
              </p>
            </form>
          </>
        )}
      </div>
    </div>
  )
}
