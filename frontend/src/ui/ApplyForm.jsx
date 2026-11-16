// Briques communes des formulaires en plusieurs étapes (investisseur, courtier) : barre d'étapes,
// grille de cases à cocher, confirmations, écran de remerciement avec WhatsApp.
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { ROUTES } from '../lib/routes'
import './apply-form.css'

export function StepBar({ steps, step, onGo }) {
  return (
    <ol className="ap_steps">
      {steps.map((label, i) => (
        <li key={label} className={i === step ? 'active' : i < step ? 'done' : ''}>
          <button type="button" disabled={i > step} onClick={() => i < step && onGo(i)}>
            <span>{i < step ? <i className="bi bi-check-lg"></i> : i + 1}</span><em>{label}</em>
          </button>
        </li>
      ))}
    </ol>
  )
}

/** Cases à cocher multiples : options = [[valeur, libellé], …] */
export function CheckGrid({ options, values, onChange, columns = 2 }) {
  const toggle = (v) => onChange(values.includes(v) ? values.filter((x) => x !== v) : [...values, v])
  return (
    <div className={`ap_checks cols-${columns}`}>
      {options.map(([v, label]) => (
        <label key={v} className={values.includes(v) ? 'active' : ''}>
          <input type="checkbox" checked={values.includes(v)} onChange={() => toggle(v)} />
          <i className={`bi ${values.includes(v) ? 'bi-check-square-fill' : 'bi-square'}`}></i>
          <span>{label}</span>
        </label>
      ))}
    </div>
  )
}

/** Déclaration obligatoire ou facultative */
export function Confirm({ checked, onChange, required = false, error = false, children }) {
  return (
    <label className={`ap_confirm${checked ? ' active' : ''}${error && !checked ? ' error' : ''}`}>
      <input type="checkbox" checked={checked} onChange={(e) => onChange(e.target.checked)} />
      <i className={`bi ${checked ? 'bi-check-square-fill' : 'bi-square'}`}></i>
      <span>{children}{required && <em> *</em>}</span>
    </label>
  )
}

/** Écran de remerciement : WhatsApp s'est ouvert avec le message préparé */
export function ThankYou({ title, message, whatsappUrl }) {
  return (
    <section className="ap_done">
      <i className="bi bi-check-circle"></i>
      <h2>{title}</h2>
      <p>{message}</p>
      <p>WhatsApp s'est ouvert avec votre demande déjà rédigée pour l'équipe NUFOTEC : appuyez sur Envoyer pour échanger directement avec nous.</p>
      <div className="ap_done-actions">
        {whatsappUrl && <a href={whatsappUrl} target="_blank" rel="noopener noreferrer" className="ap_btn whatsapp"><i className="bi bi-whatsapp"></i> Rouvrir WhatsApp</a>}
        <Link to={ROUTES.home} className="ap_btn ghost">Retour à l'accueil</Link>
      </div>
    </section>
  )
}

/** Création du compte NUFOTEC dans le formulaire : email déjà saisi + mot de passe et confirmation */
export function AccountFields({ email, password, confirm, onPassword, onConfirm, errors = {}, benefit, signedIn }) {
  const [show, setShow] = useState(false)
  if (signedIn) {
    return (
      <div className="ap_account signed">
        <i className="bi bi-person-check"></i>
        <div><b>Connecté en tant que {signedIn.email}</b><p>Votre demande sera ajoutée à votre compte NUFOTEC.</p></div>
      </div>
    )
  }
  return (
    <div className="ap_account">
      <h2><i className="bi bi-person-plus"></i> Créez votre compte NUFOTEC</h2>
      <p className="ap_account-why">{benefit}</p>
      <div className="ap_account-email"><i className="bi bi-envelope"></i> Identifiant : <b>{email || '—'}</b></div>
      <div className="ap_row">
        <label className="ap_field"><span>Mot de passe <em>*</em></span>
          <div className="ap_pw">
            <input type={show ? 'text' : 'password'} value={password} maxLength={72} autoComplete="new-password"
                   placeholder="8 caractères, lettres et chiffres, sans votre nom" onChange={(e) => onPassword(e.target.value)} />
            <button type="button" aria-label={show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'} onClick={() => setShow((v) => !v)}>
              <i className={`bi ${show ? 'bi-eye-slash' : 'bi-eye'}`}></i>
            </button>
          </div>
          {errors.password ? <small className="err">{errors.password}</small> : <small>Si vous avez déjà un compte, saisissez son mot de passe.</small>}
        </label>
        <label className="ap_field"><span>Confirmer le mot de passe <em>*</em></span>
          <input type={show ? 'text' : 'password'} value={confirm} maxLength={72} autoComplete="new-password" onChange={(e) => onConfirm(e.target.value)} />
          {errors.password2 && <small className="err">{errors.password2}</small>}
        </label>
      </div>
    </div>
  )
}
