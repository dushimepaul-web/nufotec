// Devenir courtier — conversion de pages/broker_form.php : mêmes sections (bandeau, présentation,
// 3 étapes Identité / Capacités / Mandat et confirmation, remerciement), sans compte. Après l'envoi,
// WhatsApp s'ouvre directement avec la candidature préparée pour l'équipe NUFOTEC (préférence validée).
import { useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../lib/api'
import { createOrSignIn, getUser, passwordProblem } from '../lib/auth'
import { BURUNDI_ID, DEFAULT_COUNTRIES, fullPhone } from '../lib/phone'
import { PAGE, ROUTES } from '../lib/routes'
import { LIMITS, cleanName, cleanText } from '../lib/sanitize'
import { usePageMeta } from '../lib/site'
import useApi from '../lib/useApi'
import { submitAndOpenWhatsApp } from '../lib/whatsapp'
import { AccountFields, CheckGrid, Confirm, StepBar, ThankYou } from '../ui/ApplyForm'
import CountrySelect from '../ui/CountrySelect'
import PageBanner from '../ui/PageBanner'
import PhoneField from '../ui/PhoneField'
import '../ui/apply-form.css'

const STEPS = ['Identité', 'Capacités', 'Mandat et confirmation']
const REGULATORY = [['Licensed', 'Agréé (Licensed)'], ['Exempt', 'Exempté (Exempt)'], ['Unlicensed', 'Non agréé (Unlicensed)']]
const CAPACITIES = [['investment_broker', "Broker d'investissement"], ['placement_agent', 'Agent de placement'],
  ['corporate_finance_advisor', 'Conseiller Corp. Finance'], ['fund_manager', 'Gestionnaire de fonds'],
  ['family_office_rep', 'Family Office Rep.'], ['esg_advisor', 'Conseiller ESG'], ['independent_introducer', 'Introducteur indépendant']]
const INVESTOR_TYPES = [['private_equity', 'Private Equity'], ['venture_capital', 'Venture Capital'], ['esg_impact', 'ESG / Impact'],
  ['dfi', 'DFI'], ['institutional', 'Institutionnel'], ['hnwi', 'HNWI'], ['sovereign', 'Fonds souverain']]
const MANDATES = [['equity', 'Équité'], ['structured_debt', 'Dette structurée'], ['blended_finance', 'Finance mixte'], ['grant', 'Grant'],
  ['strategic_partnership', 'Partenariat stratégique'], ['full_program', 'Programme complet']]
const ENGAGEMENT = ['Success Commission', 'Retainer + Success Fee', 'Referral Arrangement', 'To be negotiated']
const EMAIL = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/
const CONFIRMS = [
  ['confirme_autorise', "Je confirme être autorisé à agir en tant qu'intermédiaire financier selon la réglementation applicable."],
  ['confirme_aml_kyc', 'Je confirme respecter les obligations AML/KYC applicables à mon activité.'],
  ['reconnait_non_exclusivite', "Je reconnais que cet enregistrement ne constitue pas un accord d'exclusivité avec NUFOTEC."],
  ['comprend_mandat_formel', "Je comprends qu'un mandat formel sera requis avant toute introduction d'investisseur."],
]

export default function Broker() {
  usePageMeta('Devenir courtier', 'Rejoignez le réseau de courtiers et apporteurs qualifiés de NUFOTEC.')
  const countries = useApi('/pays')
  const countryList = countries.data ?? DEFAULT_COUNTRIES
  const [step, setStep] = useState(0)
  const [f, setF] = useState({
    pays_id: BURUNDI_ID, whatsapp: '', telephone: '', nom_complet: '', cabinet: '', email: '', juridiction: '',
    numero_enregistrement: '', statut_reglementaire: '', autorite_reglementaire: '', site_internet: '',
    capacites: [], capacite_autre: '', types_investisseurs: [], ticket_moyen: '', couverture_geographique: '',
    mandats: [], modele_remuneration: '', confirme_autorise: false, confirme_aml_kyc: false,
    reconnait_non_exclusivite: false, comprend_mandat_formel: false, site_web: '',
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
      if (f.cabinet.trim().length < 2) e.cabinet = 'Le nom de la firme est requis'
      if (!EMAIL.test(f.email.trim())) e.email = f.email.trim() ? 'Email invalide' : "L'email professionnel est requis"
      if (f.whatsapp && f.whatsapp.replace(/\D/g, '').length < 6) e.whatsapp = 'Numéro trop court'
      if (f.telephone && f.telephone.replace(/\D/g, '').length < 6) e.telephone = 'Numéro trop court'
      if (f.site_internet && !/^https?:\/\/[^\s.]+\.[^\s]{2,}/i.test(f.site_internet.trim())) e.site_internet = 'Adresse commençant par https://'
    }
    if (s === 2 && !CONFIRMS.every(([k]) => f[k])) e.confirm = 'Les quatre déclarations sont obligatoires'
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
    const opt = (v) => (typeof v === 'string' ? v.trim() || null : v)
    try {
      const res = await submitAndOpenWhatsApp(async () => {
        if (!signedIn) {
          await createOrSignIn({ email: f.email.trim(), password: pw, nomComplet: f.nom_complet,
            telephone: fullPhone(f.whatsapp || f.telephone, country), paysId: Number(f.pays_id), type: 'broker' })
        }
        return api.postWithMessage('/courtiers/candidature', {
        ...f, nom_complet: f.nom_complet.trim(), cabinet: f.cabinet.trim(), email: f.email.trim(), pays_id: Number(f.pays_id),
        whatsapp: fullPhone(f.whatsapp, country) || null, telephone: fullPhone(f.telephone, country) || null,
        juridiction: opt(f.juridiction), numero_enregistrement: opt(f.numero_enregistrement),
        statut_reglementaire: f.statut_reglementaire || null, autorite_reglementaire: opt(f.autorite_reglementaire),
        site_internet: opt(f.site_internet), capacite_autre: opt(f.capacite_autre), ticket_moyen: opt(f.ticket_moyen),
        couverture_geographique: opt(f.couverture_geographique), modele_remuneration: f.modele_remuneration || null,
        site_web: f.site_web || null,
      })
      })
      setDone(res)
      window.scrollTo({ top: 0, behavior: 'smooth' })
    } catch (err) {
      const fields = {}
      for (const x of Array.isArray(err.details) ? err.details : []) if (x.field) fields[x.field] = x.message || 'À vérifier'
      setErrors({ ...fields, _form: err.message })
      const first = ['nom_complet', 'cabinet', 'email', 'whatsapp', 'telephone', 'site_internet', 'pays_id', 'juridiction'].some((k) => fields[k]) ? 0
        : fields.capacite_autre ? 1 : 2
      go(first)
    } finally {
      setSending(false)
    }
  }

  const text = (k, max, props = {}) => (
    <input value={f[k]} maxLength={max} onChange={(e) => set(k, cleanText(e.target.value, max))} {...props} />
  )

  return (
    <div className="ap">
      <PageBanner crumbs={[['Investissement'], ['Devenir courtier']]} badge={<><i className="bi bi-people"></i> Courtier / Agent</>}
                  title="Enregistrez votre profil" subtitle="Complétez votre profil professionnel pour entrer dans notre réseau de courtiers qualifiés." />
      <div className="ap_wrap">
        {done ? (
          <ThankYou title="Merci pour votre candidature" message={done.message} whatsappUrl={done.data?.whatsapp?.url} />
        ) : (
          <>
            <div className="ap_intro">
              <i className="bi bi-patch-check"></i>
              <div><b>Réseau de courtiers qualifiés</b>
                <p>Aucun compte à créer. Nous vérifions votre statut réglementaire avant tout mandat. <Link to={PAGE.commission}>Voir les commissions</Link></p></div>
            </div>
            <form onSubmit={submit} noValidate>
              <StepBar steps={STEPS} step={step} onGo={go} />
              {errors._form && <div className="ap_alert" role="alert">{errors._form}</div>}
              <input type="text" className="ap_trap" tabIndex={-1} autoComplete="off" aria-hidden="true" value={f.site_web} onChange={(e) => set('site_web', e.target.value)} />

              {step === 0 && (
                <div className="ap_card">
                  <h2><i className="bi bi-building"></i> Identité professionnelle</h2>
                  <div className="ap_row">
                    <div className="ap_field" role="group" aria-label="Pays">
                      <span>Pays <em>*</em></span>
                      <CountrySelect required countries={countryList} value={f.pays_id} onChange={(id) => set('pays_id', id)} />
                    </div>
                    <label className="ap_field"><span>WhatsApp</span>
                      <PhoneField country={country} value={f.whatsapp} onChange={(v) => set('whatsapp', v)} error={errors.whatsapp} hint="Pour échanger directement avec l'équipe" />
                    </label>
                  </div>
                  <div className="ap_row">
                    <label className="ap_field"><span>Nom complet <em>*</em></span>
                      <input value={f.nom_complet} maxLength={LIMITS.name} autoComplete="name" placeholder="Marie Kamana" onChange={(e) => set('nom_complet', cleanName(e.target.value))} />
                      {errors.nom_complet && <small className="err">{errors.nom_complet}</small>}
                    </label>
                    <label className="ap_field"><span>Nom de la firme <em>*</em></span>
                      {text('cabinet', 200, { placeholder: 'Capital Africa Ltd', autoComplete: 'organization' })}
                      {errors.cabinet && <small className="err">{errors.cabinet}</small>}
                    </label>
                  </div>
                  <div className="ap_row">
                    <label className="ap_field"><span>Email professionnel <em>*</em></span>
                      <input type="email" value={f.email} maxLength={150} autoComplete="email" placeholder="contact@firm.com" onChange={(e) => set('email', e.target.value.replace(/\s/g, ''))} />
                      {errors.email && <small className="err">{errors.email}</small>}
                    </label>
                    <label className="ap_field"><span>Téléphone mobile</span>
                      <PhoneField country={country} value={f.telephone} onChange={(v) => set('telephone', v)} error={errors.telephone} hint="Facultatif" />
                    </label>
                  </div>
                  <div className="ap_row">
                    <label className="ap_field"><span>Juridiction d'incorporation</span>{text('juridiction', 150, { placeholder: 'Maurice, Luxembourg…' })}</label>
                    <label className="ap_field"><span>Numéro d'enregistrement</span>{text('numero_enregistrement', 100, { placeholder: 'REG-12345' })}</label>
                  </div>
                  <div className="ap_row">
                    <label className="ap_field"><span>Statut réglementaire</span>
                      <select value={f.statut_reglementaire} onChange={(e) => set('statut_reglementaire', e.target.value)}>
                        <option value="">Choisir</option>
                        {REGULATORY.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                      </select>
                    </label>
                    <label className="ap_field"><span>Autorité régulatrice</span>{text('autorite_reglementaire', 150, { placeholder: 'CMA, FSC, AMF…' })}</label>
                  </div>
                  <label className="ap_field"><span>Site web</span>
                    <input type="url" value={f.site_internet} maxLength={200} placeholder="https://votresite.com" onChange={(e) => set('site_internet', e.target.value.replace(/[\s<>"'{}]/g, ''))} />
                    {errors.site_internet && <small className="err">{errors.site_internet}</small>}
                  </label>
                </div>
              )}

              {step === 1 && (
                <div className="ap_card">
                  <h2><i className="bi bi-briefcase"></i> Capacités et profil investisseur</h2>
                  <h3>Votre capacité professionnelle</h3>
                  <CheckGrid options={CAPACITIES} values={f.capacites} onChange={(v) => set('capacites', v)} />
                  <label className="ap_field"><span>Autre capacité (préciser)</span>{text('capacite_autre', 255, { placeholder: 'Décrivez votre autre capacité…' })}</label>
                  <h3>Type d'investisseurs que vous représentez</h3>
                  <CheckGrid options={INVESTOR_TYPES} values={f.types_investisseurs} onChange={(v) => set('types_investisseurs', v)} />
                </div>
              )}

              {step === 2 && (
                <div className="ap_card">
                  <h2><i className="bi bi-file-earmark-text"></i> Mandat et engagement</h2>
                  <div className="ap_row">
                    <label className="ap_field"><span>Ticket typique</span>{text('ticket_moyen', 150, { placeholder: 'ex. 500K - 5M USD' })}</label>
                    <label className="ap_field"><span>Couverture géographique</span>{text('couverture_geographique', 255, { placeholder: "ex. Afrique de l'Est, Europe…" })}</label>
                  </div>
                  <h3>Types de mandat acceptés</h3>
                  <CheckGrid options={MANDATES} values={f.mandats} onChange={(v) => set('mandats', v)} />
                  <label className="ap_field"><span>Modèle d'engagement préféré</span>
                    <select value={f.modele_remuneration} onChange={(e) => set('modele_remuneration', e.target.value)}>
                      <option value="">Choisir</option>
                      {ENGAGEMENT.map((v) => <option key={v} value={v}>{v}</option>)}
                    </select>
                  </label>
                  <h2><i className="bi bi-clipboard-check"></i> Déclarations et confirmations</h2>
                  <div className="ap_recap">
                    <div><b>{f.nom_complet}</b> · {f.cabinet}</div>
                    <div>{country?.pays} · {f.email}{f.whatsapp && ` · WhatsApp ${fullPhone(f.whatsapp, country)}`}</div>
                  </div>
                  <AccountFields email={f.email.trim()} password={pw} confirm={pw2} signedIn={signedIn} errors={errors}
                                 benefit="Votre compte vous permet de suivre votre candidature, vos investisseurs présentés et vos commissions."
                                 onPassword={(v) => { setPw(v); setErrors((e) => ({ ...e, password: undefined })) }}
                                 onConfirm={(v) => { setPw2(v); setErrors((e) => ({ ...e, password2: undefined })) }} />
                  {CONFIRMS.map(([k, t]) => (
                    <Confirm key={k} required checked={f[k]} onChange={(v) => set(k, v)} error={Boolean(errors.confirm)}>{t}</Confirm>
                  ))}
                  {errors.confirm && <small className="err">{errors.confirm}</small>}
                </div>
              )}

              <div className="ap_nav">
                <button type="button" className="ap_btn ghost" disabled={step === 0 || sending} onClick={() => go(step - 1)}><i className="bi bi-arrow-left"></i> Précédent</button>
                {step < 2 ? (
                  <button type="button" className="ap_btn primary" onClick={() => check(step) && go(step + 1)}>Suivant <i className="bi bi-arrow-right"></i></button>
                ) : (
                  <button type="submit" className="ap_btn whatsapp" disabled={sending}>
                    {sending ? <><span className="spinner-border spinner-border-sm"></span> Envoi…</> : <><i className="bi bi-whatsapp"></i> Envoyer mon profil</>}
                  </button>
                )}
              </div>
              <p className="text-center mt-3" style={{ fontSize: '.85rem', color: 'var(--ink-soft)' }}>
                Vous souhaitez investir vous-même ? <Link to={ROUTES.investor}>Devenir investisseur</Link>
              </p>
            </form>
          </>
        )}
      </div>
    </div>
  )
}
