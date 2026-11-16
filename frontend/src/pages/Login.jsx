// Connexion — conversion de Auth/views/connexion_inscription.php : mêmes panneaux (connexion, création de compte,
// mot de passe oublié). Design du site (couleurs du logo, aucun dégradé, mobile d'abord). Après connexion :
// retour à la page demandée (?suite=…) ou à « Mon compte ».
import { useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import api from '../lib/api'
import { getUser, passwordProblem, saveSession } from '../lib/auth'
import { BURUNDI_ID, DEFAULT_COUNTRIES, fullPhone } from '../lib/phone'
import { ROUTES } from '../lib/routes'
import { LIMITS, cleanName } from '../lib/sanitize'
import { usePageMeta, useSite } from '../lib/site'
import useApi from '../lib/useApi'
import CountrySelect from '../ui/CountrySelect'
import Modal from '../ui/Modal'
import PhoneField from '../ui/PhoneField'
import '../styles/login.css'

const EMAIL = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/

function PasswordInput({ value, onChange, autoComplete, placeholder, id }) {
  const [show, setShow] = useState(false)
  return (
    <div className="lg_pw">
      <input id={id} type={show ? 'text' : 'password'} value={value} maxLength={72} autoComplete={autoComplete} placeholder={placeholder}
             onChange={(e) => onChange(e.target.value)} />
      <button type="button" aria-label={show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'} onClick={() => setShow((v) => !v)}>
        <i className={`bi ${show ? 'bi-eye-slash' : 'bi-eye'}`}></i>
      </button>
    </div>
  )
}

function ForgotModal({ open, onClose, initial }) {
  // Étape 1 : email → un code à 6 chiffres est envoyé ; étape 2 : code + nouveau mot de passe
  const [step, setStep] = useState(1)
  const [email, setEmail] = useState(initial || '')
  const [code, setCode] = useState('')
  const [pw, setPw] = useState('')
  const [pw2, setPw2] = useState('')
  const [state, setState] = useState({ sending: false, info: '', error: '', done: false })
  const close = () => { onClose(); setTimeout(() => { setStep(1); setCode(''); setPw(''); setPw2(''); setState({ sending: false, info: '', error: '', done: false }) }, 200) }

  const sendCode = async (e) => {
    e.preventDefault()
    if (!EMAIL.test(email.trim())) { setState((s) => ({ ...s, error: 'Adresse email invalide' })); return }
    setState({ sending: true, info: '', error: '', done: false })
    try {
      const res = await api.postWithMessage('/auth/password/forgot', { email: email.trim() })
      setState({ sending: false, info: res.message, error: '', done: false })
      setStep(2)
    } catch (err) {
      setState({ sending: false, info: '', error: err.message, done: false })
    }
  }

  const reset = async (e) => {
    e.preventDefault()
    let error = ''
    if (!/^\d{6}$/.test(code)) error = 'Le code reçu contient 6 chiffres'
    else error = passwordProblem(pw, { email: email.trim() }) || (pw !== pw2 ? 'Les deux mots de passe ne correspondent pas' : '')
    if (error) { setState((s) => ({ ...s, error })); return }
    setState((s) => ({ ...s, sending: true, error: '' }))
    try {
      const res = await api.postWithMessage('/auth/password/reset', { email: email.trim(), code, new_password: pw })
      setState({ sending: false, info: res.message, error: '', done: true })
    } catch (err) {
      setState((s) => ({ ...s, sending: false, error: err.message }))
    }
  }

  return (
    <Modal open={open} onClose={close} size="sm" icon="bi-key" title="Mot de passe oublié">
      {state.done ? (
        <div className="lg_sent"><i className="bi bi-check-circle"></i><p>{state.info}</p>
          <button type="button" className="lg_btn primary" onClick={close}>Se connecter</button></div>
      ) : step === 1 ? (
        <form onSubmit={sendCode} className="lg_form">
          <p className="lg_muted">Saisissez l'email de votre compte : nous vous envoyons un code à 6 chiffres.</p>
          <label className="lg_field"><span>Adresse email</span>
            <input type="email" value={email} autoComplete="email" data-autofocus onChange={(e) => setEmail(e.target.value.replace(/\s/g, ''))} />
            {state.error && <small className="err">{state.error}</small>}
          </label>
          <button type="submit" className="lg_btn primary" disabled={state.sending}>
            {state.sending ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-send"></i>} Recevoir le code
          </button>
        </form>
      ) : (
        <form onSubmit={reset} className="lg_form">
          <p className="lg_muted">{state.info} Saisissez-le ci-dessous avec votre nouveau mot de passe.</p>
          <label className="lg_field"><span>Code reçu par email</span>
            <input inputMode="numeric" autoComplete="one-time-code" maxLength={6} value={code} data-autofocus placeholder="123456"
                   onChange={(e) => setCode(e.target.value.replace(/\D/g, '').slice(0, 6))} />
          </label>
          <label className="lg_field"><span>Nouveau mot de passe</span>
            <PasswordInput value={pw} autoComplete="new-password" placeholder="8 caractères, lettres et chiffres" onChange={setPw} />
          </label>
          <label className="lg_field"><span>Confirmer</span>
            <PasswordInput value={pw2} autoComplete="new-password" onChange={setPw2} />
          </label>
          {state.error && <small className="err">{state.error}</small>}
          <button type="submit" className="lg_btn primary" disabled={state.sending}>
            {state.sending ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-check2"></i>} Changer le mot de passe
          </button>
          <button type="button" className="lg_link" onClick={() => setStep(1)}>Renvoyer un code</button>
        </form>
      )}
    </Modal>
  )
}

export default function Login() {
  const [params] = useSearchParams()
  const navigate = useNavigate()
  const { settings } = useSite()
  const next = params.get('suite')?.startsWith('/') ? params.get('suite') : ROUTES.account
  const [panel, setPanel] = useState(params.get('panneau') === 'inscription' ? 'register' : 'login')
  usePageMeta(panel === 'login' ? 'Connexion' : 'Créer un compte')
  const countries = useApi(panel === 'register' ? '/pays' : null)
  const countryList = countries.data ?? DEFAULT_COUNTRIES
  const [forgot, setForgot] = useState(false)
  const [sending, setSending] = useState(false)
  const [errors, setErrors] = useState({})
  const [login, setLogin] = useState({ login: '', password: '' })
  const [reg, setReg] = useState({ pays_id: BURUNDI_ID, telephone: '', prenom: '', nom: '', email: '', password: '', password2: '', accepte: false })
  const country = countryList.find((c) => c.id === Number(reg.pays_id))
  const already = getUser()

  const finish = (data) => { saveSession(data); navigate(next, { replace: true }) }
  const setR = (k, v) => { setReg((r) => ({ ...r, [k]: v })); setErrors((e) => ({ ...e, [k]: undefined, _form: undefined })) }

  const submitLogin = async (e) => {
    e.preventDefault()
    const errs = {}
    if (login.login.trim().length < 3) errs.login = 'Votre email ou votre numéro de téléphone'
    if (!login.password) errs.password = 'Votre mot de passe'
    setErrors(errs)
    if (Object.keys(errs).length) return
    setSending(true)
    try {
      const raw = login.login.trim()
      const id = raw.includes('@') ? raw : raw.replace(/[^\d+]/g, '')
      finish(await api.post('/auth/login', { login: id, password: login.password }))
    } catch (err) {
      setErrors({ _form: err.code === 'INVALID_CREDENTIALS' || err.status === 401 ? 'Identifiant ou mot de passe incorrect' : err.message })
    } finally {
      setSending(false)
    }
  }

  const submitRegister = async (e) => {
    e.preventDefault()
    const errs = {}
    if (cleanName(reg.prenom).trim().length < 1) errs.prenom = 'Votre prénom'
    if (cleanName(reg.nom).trim().length < 1) errs.nom = 'Votre nom'
    if (!EMAIL.test(reg.email.trim())) errs.email = reg.email.trim() ? 'Email invalide' : 'Votre adresse email'
    if (reg.telephone && reg.telephone.replace(/\D/g, '').length < 6) errs.telephone = 'Numéro trop court'
    const problem = passwordProblem(reg.password, { nomComplet: `${reg.prenom} ${reg.nom}`, email: reg.email.trim() })
    if (problem) errs.password = problem
    else if (reg.password !== reg.password2) errs.password2 = 'Les deux mots de passe ne correspondent pas'
    if (!reg.accepte) errs.accepte = 'Veuillez accepter les conditions'
    setErrors(errs)
    if (Object.keys(errs).length) return
    setSending(true)
    try {
      finish(await api.post('/auth/register', {
        prenom: reg.prenom.trim(), nom: reg.nom.trim(), email: reg.email.trim(), password: reg.password,
        pays_id: Number(reg.pays_id), telephone: fullPhone(reg.telephone, country) || null, type_utilisateur: 'patient',
      }))
    } catch (err) {
      const f = {}
      for (const x of Array.isArray(err.details) ? err.details : []) if (x.field) f[x.field] = x.message || err.message
      if (err.code === 'EMAIL_TAKEN') f.email = 'Cet email a déjà un compte : connectez-vous'
      if (err.code === 'PHONE_TAKEN') f.telephone = 'Ce numéro est déjà utilisé par un autre compte'
      setErrors({ ...f, _form: Object.keys(f).length ? undefined : err.message })
    } finally {
      setSending(false)
    }
  }

  return (
    <div className="lg_page">
      <div className="lg_card">
        <div className="lg_logo">
          <Link to={ROUTES.home}><img src={settings.site_logo || '/logo.jpeg'} alt="NUFOTEC" onError={(e) => { e.currentTarget.src = '/logo.jpeg' }} /></Link>
          <h1>{settings.site_name || 'NUFOTEC BURUNDI'}</h1>
        </div>

        {already && (
          <div className="lg_already">
            <i className="bi bi-person-check"></i> Vous êtes connecté ({already.email}). <Link to={ROUTES.account}>Aller à mon compte</Link>
          </div>
        )}

        {panel === 'login' ? (
          /* CONNEXION */
          <form className="lg_form" onSubmit={submitLogin} noValidate>
            <div className="lg_title">Bienvenue chez NUFOTEC</div>
            <p className="lg_subtitle">Utilisez votre email ou votre numéro de téléphone pour vous connecter.</p>
            {errors._form && <div className="lg_alert" role="alert">{errors._form}</div>}
            <label className="lg_field"><span>Email ou téléphone</span>
              <input value={login.login} autoComplete="username" placeholder="nom@exemple.com ou 79 666 439" maxLength={100}
                     onChange={(e) => { setLogin((l) => ({ ...l, login: e.target.value.replace(/[<>{}]/g, '') })); setErrors({}) }} />
              {errors.login && <small className="err">{errors.login}</small>}
            </label>
            <label className="lg_field"><span>Mot de passe</span>
              <PasswordInput value={login.password} autoComplete="current-password" onChange={(v) => { setLogin((l) => ({ ...l, password: v })); setErrors({}) }} />
              {errors.password && <small className="err">{errors.password}</small>}
            </label>
            <div className="lg_forgot"><button type="button" onClick={() => setForgot(true)}><i className="bi bi-key"></i> Mot de passe oublié ?</button></div>
            <button type="submit" className="lg_btn primary lg_btn-full" disabled={sending}>
              {sending ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-box-arrow-in-right"></i>} Se connecter
            </button>
            <p className="lg_switch">Pas encore de compte ? <button type="button" onClick={() => { setPanel('register'); setErrors({}) }}>Créer un compte</button></p>
          </form>
        ) : (
          /* CRÉATION DE COMPTE */
          <form className="lg_form" onSubmit={submitRegister} noValidate>
            <div className="lg_title">Créer un compte NUFOTEC</div>
            <p className="lg_subtitle">Retrouvez vos commandes et vos consultations dans votre espace.</p>
            {errors._form && <div className="lg_alert" role="alert">{errors._form}</div>}
            <div className="lg_field" role="group" aria-label="Pays de résidence">
              <span>Pays de résidence</span>
              <CountrySelect countries={countryList} value={reg.pays_id} onChange={(id) => setR('pays_id', id)} />
            </div>
            <label className="lg_field"><span>Téléphone WhatsApp</span>
              <PhoneField country={country} value={reg.telephone} onChange={(v) => setR('telephone', v)} error={errors.telephone}
                          hint="Le même numéro que vos commandes et rendez-vous : ils apparaîtront dans votre compte" />
            </label>
            <div className="lg_row">
              <label className="lg_field"><span>Prénom</span>
                <input value={reg.prenom} maxLength={50} autoComplete="given-name" onChange={(e) => setR('prenom', cleanName(e.target.value).slice(0, 50))} />
                {errors.prenom && <small className="err">{errors.prenom}</small>}
              </label>
              <label className="lg_field"><span>Nom</span>
                <input value={reg.nom} maxLength={50} autoComplete="family-name" onChange={(e) => setR('nom', cleanName(e.target.value).slice(0, LIMITS.name))} />
                {errors.nom && <small className="err">{errors.nom}</small>}
              </label>
            </div>
            <label className="lg_field"><span>Adresse email</span>
              <input type="email" value={reg.email} maxLength={150} autoComplete="email" onChange={(e) => setR('email', e.target.value.replace(/\s/g, ''))} />
              {errors.email && <small className="err">{errors.email}</small>}
            </label>
            <div className="lg_row">
              <label className="lg_field"><span>Mot de passe</span>
                <PasswordInput value={reg.password} autoComplete="new-password" placeholder="8 caractères, lettres et chiffres" onChange={(v) => setR('password', v)} />
                {errors.password ? <small className="err">{errors.password}</small> : <small>Sans votre nom ni votre email</small>}
              </label>
              <label className="lg_field"><span>Confirmer</span>
                <PasswordInput value={reg.password2} autoComplete="new-password" onChange={(v) => setR('password2', v)} />
                {errors.password2 && <small className="err">{errors.password2}</small>}
              </label>
            </div>
            <label className={`lg_check${errors.accepte ? ' error' : ''}`}>
              <input type="checkbox" checked={reg.accepte} onChange={(e) => setR('accepte', e.target.checked)} />
              <span>J'accepte les conditions générales et la politique de confidentialité de NUFOTEC.</span>
            </label>
            {errors.accepte && <small className="err">{errors.accepte}</small>}
            <button type="submit" className="lg_btn primary lg_btn-full" disabled={sending}>
              {sending ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-person-plus"></i>} Créer mon compte
            </button>
            <p className="lg_switch">Déjà inscrit ? <button type="button" onClick={() => { setPanel('login'); setErrors({}) }}>Se connecter</button></p>
          </form>
        )}
        <p className="lg_legal"><i className="bi bi-shield-lock"></i> Vos informations restent confidentielles.</p>
      </div>
      <ForgotModal open={forgot} onClose={() => setForgot(false)} initial={login.login.includes('@') ? login.login : ''} />
    </div>
  )
}
