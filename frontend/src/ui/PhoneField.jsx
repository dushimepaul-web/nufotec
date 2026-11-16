// Téléphone avec l'indicatif du pays choisi affiché devant (drapeau + +257…), changé automatiquement
// quand le pays change. Utilisé par TOUS les formulaires (commande, inscription, réservation…).
import { LIMITS, cleanPhone } from '../lib/sanitize'
import { BURUNDI_ID, dialCode } from '../lib/phone'
import { Flag } from './CountrySelect'
import './phone-field.css'

export default function PhoneField({ country, value, onChange, required = false, autoFocus = false, error, hint, id }) {
  const code = dialCode(country)
  const isBurundi = country?.id === BURUNDI_ID
  return (
    <>
      <div className="phonef">
        {code && <span className="phonef-code" title={country?.pays}><Flag key={country?.iso2} iso2={country?.iso2} />{code}</span>}
        <input id={id} required={required} type="tel" inputMode="tel" minLength={6} maxLength={LIMITS.phone}
               {...(autoFocus ? { 'data-autofocus': true } : {})}
               value={value} onChange={(e) => onChange(cleanPhone(e.target.value))}
               placeholder={isBurundi ? '79 666 439' : 'Numéro sans l’indicatif'} autoComplete="tel-national" />
      </div>
      {error
        ? <small className="err">{error}</small>
        : <small>{hint ?? (code ? `Indicatif ${code} ajouté automatiquement` : 'Nous vous contactons sur ce numéro')}</small>}
    </>
  )
}
