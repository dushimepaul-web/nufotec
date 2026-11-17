// Prendre rendez-vous — conversion de Consultations/views/PatientForm_View.php, SANS compte :
// le visiteur donne son nom et ce qui est demandé, la demande est enregistrée puis WhatsApp s'ouvre directement
// vers l'expert avec le message préparé. Mêmes sections : bandeau, 4 étapes (Informations, Symptômes, Documents,
// Vérification et paiement), colonne de l'expert et « comment ça marche », écran de confirmation.
import { useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import api from '../lib/api'
import { DOCTOR_FALLBACK, doctorName, doctorPrices } from '../lib/doctors'
import { formatSlot, isoDay } from '../lib/format'
import { BURUNDI_ID, DEFAULT_COUNTRIES, fullPhone } from '../lib/phone'
import { ROUTES } from '../lib/routes'
import { LIMITS, cleanName, cleanText } from '../lib/sanitize'
import { usePageMeta, useSite } from '../lib/site'
import { isNetworkFailure, localWhatsAppUrl, openIn } from '../lib/whatsapp'
import { keyFor, queue } from '../shared/outbox'
import useApi from '../lib/useApi'
import CountrySelect from '../ui/CountrySelect'
import PageBanner from '../ui/PageBanner'
import PhoneField from '../ui/PhoneField'
import '../styles/booking.css'

const STEPS = ['Informations', 'Symptômes', 'Documents', 'Vérification et paiement']
const DURATIONS = [['24h', 'Moins de 24 heures'], ['2-3j', '2 à 3 jours'], ['1sem', 'Une semaine'], ['2sem', 'Deux semaines'], ['1mois', "Plus d'un mois"]]
const HOW = [
  ['bi-pencil-square', 'Remplissez le formulaire'],
  ['bi-chat-text', 'Décrivez vos symptômes'],
  ['bi-upload', 'Ajoutez vos documents si vous en avez'],
  ['bi-whatsapp', 'Envoyez la demande sur WhatsApp'],
  ['bi-person-check', "L'expert confirme votre rendez-vous"],
]
const ACCEPT = '.pdf,.jpg,.jpeg,.png,.webp'
const MAX_FILE = 10 * 1024 * 1024
const MAX_FILES = 8 // l'API accepte 10 documents par consultation (dont la preuve de paiement)
const digits = (v, max) => v.replace(/[^\d.,]/g, '').replace(',', '.').slice(0, max)

/** Créneaux libres des 7 prochains jours, groupés par jour */
function useSlots(uuid, refreshKey) {
  const [state, setState] = useState({ loading: true, days: [] })
  useEffect(() => {
    let alive = true
    const now = new Date()
    setState((s) => ({ ...s, loading: true }))
    api.get(`/medecins/${uuid}/creneaux`, { du: isoDay(now), au: isoDay(new Date(now.getTime() + 6 * 864e5)) })
      .then((list) => {
        const byDay = new Map()
        for (const s of list ?? []) {
          if (new Date(s.debut) <= now) continue
          const key = s.debut.slice(0, 10)
          byDay.set(key, [...(byDay.get(key) ?? []), s])
        }
        if (alive) setState({ loading: false, days: [...byDay.entries()].map(([day, slots]) => ({ day, slots })) })
      })
      .catch(() => alive && setState({ loading: false, days: [] }))
    return () => { alive = false }
  }, [uuid, refreshKey])
  return state
}

function FileZone({ label, hint, files, onAdd, onRemove, icon }) {
  return (
    <div className="bk_zone">
      <label className="bk_drop">
        <i className={`bi ${icon}`}></i>
        <b>{label}</b>
        <span>{hint}</span>
        <input type="file" multiple accept={ACCEPT} onChange={(e) => { onAdd([...e.target.files]); e.target.value = '' }} />
      </label>
      {files.length > 0 && (
        <ul className="bk_files">
          {files.map((f, i) => (
            <li key={`${f.name}-${i}`}>
              <i className={`bi ${f.type === 'application/pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image'}`}></i>
              <span>{f.name}</span><small>{(f.size / 1024 / 1024).toFixed(1)} Mo</small>
              <button type="button" aria-label={`Retirer ${f.name}`} onClick={() => onRemove(i)}><i className="bi bi-x-lg"></i></button>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}

export default function Booking() {
  const { uuid } = useParams()
  const { settings } = useSite()
  const doctor = useApi(`/medecins/${uuid}`, undefined, [uuid])
  const countries = useApi('/pays')
  const modes = useApi('/modes-paiement')
  const [slotsKey, setSlotsKey] = useState(0)
  const slots = useSlots(uuid, slotsKey)
  const d = doctor.data
  usePageMeta(d ? `Rendez-vous avec ${doctorName(d)}` : 'Prendre rendez-vous')

  const [step, setStep] = useState(0)
  const [day, setDay] = useState(null)
  const [form, setForm] = useState({
    debut: '', pays_id: BURUNDI_ID, whatsapp: '', nom_complet: '', age: '', taille: '', poids: '',
    symptomes: '', duree_symptomes: '', consultation_precedente: '', mode_paiement_id: '', accepte: false, site_web: '',
  })
  const [exams, setExams] = useState([])
  const [prescriptions, setPrescriptions] = useState([])
  const [proof, setProof] = useState(null)
  const [errors, setErrors] = useState({})
  const [sending, setSending] = useState(false)
  const [done, setDone] = useState(null)
  const [fileError, setFileError] = useState('')

  const countryList = countries.data ?? DEFAULT_COUNTRIES
  const country = countryList.find((c) => c.id === Number(form.pays_id))
  const burundi = Number(form.pays_id) === BURUNDI_ID
  const tarifs = doctorPrices(d)
  // Le tarif suit le pays de résidence choisi (comme le calcul fait par l'API)
  const price = burundi ? (tarifs.burundi ?? tarifs.etranger) : (tarifs.etranger ?? tarifs.burundi)
  const equivalent = !burundi && tarifs.etranger ? tarifs.equivalent : null
  const set = (k, v) => { setForm((f) => ({ ...f, [k]: v })); setErrors((e) => ({ ...e, [k]: undefined })) }
  const activeDay = day ?? slots.days[0]?.day
  const daySlots = slots.days.find((x) => x.day === activeDay)?.slots ?? []
  const allFiles = useMemo(() => [...exams, ...prescriptions], [exams, prescriptions])

  const addFiles = (setter, current) => (list) => {
    setFileError('')
    const ok = []
    for (const f of list) {
      if (!/\.(pdf|jpe?g|png|webp)$/i.test(f.name)) { setFileError(`${f.name} : formats acceptés PDF, JPG, PNG ou WEBP`); continue }
      if (f.size > MAX_FILE) { setFileError(`${f.name} dépasse 10 Mo`); continue }
      ok.push(f)
    }
    if (current.length + allFiles.length - current.length + ok.length > MAX_FILES) {
      setFileError(`${MAX_FILES} fichiers au maximum`)
      ok.splice(Math.max(0, MAX_FILES - allFiles.length))
    }
    setter([...current, ...ok])
  }

  /** Contrôle de l'étape avant de passer à la suivante */
  const check = (s) => {
    const e = {}
    if (s === 0) {
      if (!form.debut) e.debut = 'Choisissez un créneau'
      if (form.whatsapp.replace(/\D/g, '').length < 6) e.whatsapp = 'Numéro WhatsApp obligatoire'
      if (cleanName(form.nom_complet).trim().length < 2) e.nom_complet = 'Votre nom complet'
      const age = Number(form.age)
      if (form.age === '' || age < 0 || age > 120) e.age = 'Âge entre 0 et 120'
      const t = Number(form.taille)
      if (!(t > 40 && t < 260)) e.taille = 'En cm (40 à 260)'
      const p = Number(form.poids)
      if (!(p > 0 && p < 300)) e.poids = 'En kg (1 à 299)'
    }
    if (s === 1) {
      if (form.symptomes.trim().length < 20) e.symptomes = 'Décrivez vos symptômes (20 caractères au moins)'
    }
    if (s === 3 && !form.accepte) e.accepte = 'Veuillez accepter pour continuer'
    if (s === 3 && proof && !form.mode_paiement_id) e.mode_paiement_id = 'Choisissez le mode de paiement utilisé'
    setErrors(e)
    return Object.keys(e).length === 0
  }
  const go = (s) => { setStep(s); window.scrollTo({ top: document.querySelector('.bk_steps')?.offsetTop - 140 || 0, behavior: 'smooth' }) }
  const next = () => { if (check(step)) go(step + 1) }

  const submit = async (e) => {
    e.preventDefault()
    if (!check(3)) return
    setSending(true)
    // Onglet WhatsApp ouvert PENDANT le clic (sinon bloqué), dirigé ensuite vers le lien préparé par l'API
    const tab = window.open('', '_blank')
    try {
      const body = {
        medecin_uuid: uuid, debut: form.debut, nom_complet: form.nom_complet.trim(), whatsapp: fullPhone(form.whatsapp, country),
        pays_residence_id: Number(form.pays_id), age: Number(form.age), taille: form.taille, poids: form.poids,
        symptomes: form.symptomes.trim(), duree_symptomes: form.duree_symptomes || null,
        consultation_precedente: form.consultation_precedente === 'oui',
        mode_paiement_id: form.mode_paiement_id ? Number(form.mode_paiement_id) : null, site_web: form.site_web || null,
      }
      const key = keyFor('/consultations/demande', body)
      let data
      try {
        data = await api.post('/consultations/demande', body, { key })
      } catch (err) {
        if (!isNetworkFailure(err)) throw err
        // Connexion coupée : la demande part sur WhatsApp et sera enregistrée automatiquement au retour du réseau.
        // Les fichiers ne peuvent pas attendre sur le téléphone : le visiteur les joint dans WhatsApp.
        queue('/consultations/demande', body, key, `Rendez-vous : ${doctorName(d)}`)
        const files = [...allFiles, ...(proof && form.mode_paiement_id ? [proof] : [])].map((f) => f.name)
        const url = localWhatsAppUrl(settings, [
          `Bonjour NUFOTEC BURUNDI, je demande une consultation avec ${doctorName(d)}.`,
          `Créneau souhaité : ${formatSlot(form.debut)}`, '',
          `Nom : ${body.nom_complet}`, `WhatsApp : ${body.whatsapp}`, `Pays : ${country?.pays ?? ''}`,
          `Âge : ${body.age} ans · Taille : ${body.taille} · Poids : ${body.poids}`, '',
          `Symptômes : ${body.symptomes}`, files.length > 0 && `Documents (je les joins ici) : ${files.join(', ')}`,
        ])
        setDone({ queued: true, debut: form.debut, failed: files, whatsapp_medecin: { url } })
        window.scrollTo({ top: 0, behavior: 'smooth' })
        openIn(tab, url)
        return
      }
      // Documents et preuve : le code temporaire renvoyé par l'API remplace le compte
      const failed = []
      for (const f of allFiles) {
        const fd = new FormData()
        fd.append('jeton', data.jeton)
        fd.append('fichier', f)
        try { await api.post(`/consultations/demande/${data.numero}/documents`, fd) } catch { failed.push(f.name) }
      }
      if (proof && form.mode_paiement_id) {
        const fd = new FormData()
        fd.append('jeton', data.jeton)
        fd.append('mode_paiement_id', form.mode_paiement_id)
        fd.append('preuve', proof)
        try { await api.post(`/consultations/demande/${data.numero}/paiement`, fd) } catch { failed.push(proof.name) }
      }
      setDone({ ...data, failed })
      window.scrollTo({ top: 0, behavior: 'smooth' })
      openIn(tab, data.whatsapp_medecin.url)
    } catch (err) {
      tab?.close()
      const fields = {}
      for (const x of Array.isArray(err.details) ? err.details : []) fields[x.field === 'pays_residence_id' ? 'pays_id' : x.field] = x.message
      if (err.code === 'SLOT_UNAVAILABLE') { fields.debut = err.message; set('debut', ''); setSlotsKey((k) => k + 1) }
      setErrors({ ...fields, _form: Object.keys(fields).length ? undefined : err.message })
      const firstStep = fields.debut || fields.whatsapp || fields.nom_complet || fields.age || fields.taille || fields.poids || fields.pays_id ? 0
        : fields.symptomes ? 1 : 3
      go(firstStep)
    } finally {
      setSending(false)
    }
  }

  if (doctor.loading) return <div className="bk"><div className="bk_wrap bk_center"><span className="spinner-border text-success"></span></div></div>
  if (doctor.error || !d) {
    return (
      <div className="bk">
        <div className="bk_wrap bk_center bk_notfound">
          <i className="bi bi-person-x"></i>
          <h1>Expert introuvable</h1>
          <p>{doctor.error?.status === 404 ? "Cet expert n'existe pas ou n'est plus disponible." : doctor.error?.message}</p>
          <Link to={ROUTES.doctors} className="bk_btn primary"><i className="bi bi-arrow-left"></i> Voir nos experts</Link>
        </div>
      </div>
    )
  }

  const expertCard = (
    <div className="bk_expert">
      <img src={d.photo_url || DOCTOR_FALLBACK} alt="" onError={(e) => { e.currentTarget.src = DOCTOR_FALLBACK }} />
      <div>
        <b>{doctorName(d)}</b>
        <span>{d.specialite || 'Santé générale'}</span>
        <em>{price} · {d.duree_minutes || 30} min</em>
      </div>
      <Link to={ROUTES.doctors} className="bk_change">Changer</Link>
    </div>
  )

  return (
    <div className="bk">
      {/* BANDEAU */}
      <PageBanner crumbs={[['Téléconsultation', ROUTES.doctors], ['Rendez-vous']]} badge={<><i className="bi bi-calendar-check"></i> Sans compte, en quelques minutes</>}
                  title="Prendre rendez-vous" subtitle="Remplissez la demande : elle est envoyée à l'expert sur WhatsApp, qui vous confirme le rendez-vous." />

      <div className="bk_wrap">
        {done ? (
          /* CONFIRMATION */
          <section className="bk_done">
            <i className="bi bi-check-circle"></i>
            <h2>{done.queued ? 'Merci, votre demande est prête' : 'Merci, votre demande est enregistrée'}</h2>
            {done.numero && <p>Numéro de suivi : <b>{done.numero}</b></p>}
            {done.queued ? (
              <>
                <p>WhatsApp s'est ouvert avec votre demande déjà rédigée pour <b>{doctorName(d)}</b> : appuyez sur Envoyer.
                  NUFOTEC BURUNDI vous répond pour confirmer le rendez-vous ({formatSlot(done.debut ?? form.debut)}).</p>
                <p className="bk_queued"><i className="bi bi-cloud-arrow-up"></i> Connexion trop faible : votre demande est gardée sur votre téléphone et sera enregistrée automatiquement dès le retour du réseau.</p>
              </>
            ) : (
              <p>WhatsApp s'est ouvert avec votre demande déjà rédigée pour <b>{doctorName(d)}</b> : appuyez sur Envoyer.
                L'expert vous répond pour confirmer le rendez-vous ({formatSlot(done.debut ?? form.debut)}).</p>
            )}
            {done.failed?.length > 0 && <p className="bk_warn"><i className="bi bi-exclamation-triangle"></i> {done.queued ? 'À joindre dans WhatsApp' : 'Non envoyés'} : {done.failed.join(', ')}.{done.queued ? '' : ' Vous pourrez les transmettre sur WhatsApp.'}</p>}
            <div className="bk_done-actions">
              <a href={done.whatsapp_medecin.url} target="_blank" rel="noopener noreferrer" className="bk_btn whatsapp lg"><i className="bi bi-whatsapp"></i> Rouvrir WhatsApp</a>
              <Link to={ROUTES.home} className="bk_btn ghost lg">Retour à l'accueil</Link>
            </div>
          </section>
        ) : (
          <div className="bk_layout">
            <div className="bk_mobile-expert">{expertCard}</div>
            {/* FORMULAIRE EN 4 ÉTAPES */}
            <form className="bk_form" onSubmit={submit} noValidate>
              <ol className="bk_steps">
                {STEPS.map((label, i) => (
                  <li key={label} className={i === step ? 'active' : i < step ? 'done' : ''}>
                    <button type="button" disabled={i > step} onClick={() => i < step && go(i)}>
                      <span>{i < step ? <i className="bi bi-check-lg"></i> : i + 1}</span><em>{label}</em>
                    </button>
                  </li>
                ))}
              </ol>
              {errors._form && <div className="bk_alert" role="alert">{errors._form}</div>}
              <input type="text" className="bk_trap" tabIndex={-1} autoComplete="off" aria-hidden="true" value={form.site_web} onChange={(e) => set('site_web', e.target.value)} />

              {step === 0 && (
                <div className="bk_card">
                  <h2><i className="bi bi-person-badge"></i> Vos informations</h2>
                  <div className="bk_field">
                    <span>Votre créneau <em>*</em></span>
                    {slots.loading ? <div className="bk_muted"><span className="spinner-border spinner-border-sm"></span> Chargement des disponibilités…</div>
                      : slots.days.length === 0 ? <div className="bk_muted">Aucun créneau libre cette semaine. Écrivez-nous sur WhatsApp.</div> : (
                        <>
                          <div className="bk_days">
                            {slots.days.map(({ day: k, slots: list }) => {
                              const dt = new Date(`${k}T12:00:00`)
                              return (
                                <button key={k} type="button" className={k === activeDay ? 'active' : ''} onClick={() => setDay(k)}>
                                  <small>{dt.toLocaleDateString('fr-FR', { weekday: 'short' })}</small>
                                  <b>{dt.getDate()}</b>
                                  <small>{list.length} libre{list.length > 1 ? 's' : ''}</small>
                                </button>
                              )
                            })}
                          </div>
                          <div className="bk_times">
                            {daySlots.map((s) => (
                              <button key={s.debut} type="button" className={form.debut === s.debut ? 'active' : ''} onClick={() => set('debut', s.debut)}>
                                {new Date(s.debut).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
                              </button>
                            ))}
                          </div>
                          {form.debut && <div className="bk_chosen"><i className="bi bi-calendar-check"></i> {formatSlot(form.debut)} <small>(heure du Burundi)</small></div>}
                        </>
                      )}
                    {errors.debut && <small className="err">{errors.debut}</small>}
                  </div>
                  <div className="bk_row">
                    <div className="bk_field" role="group" aria-label="Pays de résidence">
                      <span>Pays de résidence <em>*</em></span>
                      <CountrySelect required countries={countryList} value={form.pays_id} onChange={(id) => set('pays_id', id)} />
                      <small>{burundi ? 'Résidant du Burundi' : "Résidant à l'étranger"} : <b>{price}</b>{equivalent && <> (Équivalent en BIF : {equivalent})</>}</small>
                    </div>
                    <label className="bk_field"><span>Téléphone WhatsApp <em>*</em></span>
                      <PhoneField required country={country} value={form.whatsapp} onChange={(v) => set('whatsapp', v)} error={errors.whatsapp}
                                  hint="L'expert vous répond sur ce numéro" />
                    </label>
                  </div>
                  <label className="bk_field"><span>Nom complet <em>*</em></span>
                    <input value={form.nom_complet} maxLength={LIMITS.name} autoComplete="name" placeholder="Votre nom et prénom"
                           onChange={(e) => set('nom_complet', cleanName(e.target.value))} />
                    {errors.nom_complet && <small className="err">{errors.nom_complet}</small>}
                  </label>
                  <div className="bk_row three">
                    <label className="bk_field"><span>Âge <em>*</em></span>
                      <input inputMode="numeric" value={form.age} placeholder="ans" onChange={(e) => set('age', e.target.value.replace(/\D/g, '').slice(0, 3))} />
                      {errors.age && <small className="err">{errors.age}</small>}
                    </label>
                    <label className="bk_field"><span>Taille <em>*</em></span>
                      <input inputMode="decimal" value={form.taille} placeholder="cm" onChange={(e) => set('taille', digits(e.target.value, 5))} />
                      {errors.taille && <small className="err">{errors.taille}</small>}
                    </label>
                    <label className="bk_field"><span>Poids <em>*</em></span>
                      <input inputMode="decimal" value={form.poids} placeholder="kg" onChange={(e) => set('poids', digits(e.target.value, 5))} />
                      {errors.poids && <small className="err">{errors.poids}</small>}
                    </label>
                  </div>
                </div>
              )}

              {step === 1 && (
                <div className="bk_card">
                  <h2><i className="bi bi-chat-text"></i> Vos symptômes</h2>
                  <label className="bk_field"><span>Décrivez ce que vous ressentez <em>*</em></span>
                    <textarea rows={5} maxLength={2000} value={form.symptomes} placeholder="Depuis quand, où avez-vous mal, ce qui soulage ou aggrave…"
                              onChange={(e) => set('symptomes', cleanText(e.target.value, 2000))} />
                    <small className={errors.symptomes ? 'err' : ''}>{errors.symptomes || `${form.symptomes.trim().length} / 2000 caractères (20 au moins)`}</small>
                  </label>
                  <label className="bk_field"><span>Depuis combien de temps ?</span>
                    <select value={form.duree_symptomes} onChange={(e) => set('duree_symptomes', e.target.value)}>
                      <option value="">Choisir une durée</option>
                      {DURATIONS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                    </select>
                  </label>
                  <div className="bk_field"><span>Avez-vous déjà consulté pour ce problème ?</span>
                    <div className="bk_choice">
                      {[['oui', 'Oui'], ['non', 'Non']].map(([v, l]) => (
                        <label key={v} className={form.consultation_precedente === v ? 'active' : ''}>
                          <input type="radio" name="prev" value={v} checked={form.consultation_precedente === v} onChange={() => set('consultation_precedente', v)} /> {l}
                        </label>
                      ))}
                    </div>
                  </div>
                </div>
              )}

              {step === 2 && (
                <div className="bk_card">
                  <h2><i className="bi bi-upload"></i> Vos documents <small>(facultatif)</small></h2>
                  <p className="bk_muted">Résultats d'analyses, radios, ordonnances… Ils sont stockés de façon privée et seul l'expert y a accès.</p>
                  <FileZone icon="bi-clipboard2-pulse" label="Examens et résultats" hint="PDF, JPG, PNG · 10 Mo max par fichier"
                            files={exams} onAdd={addFiles(setExams, exams)} onRemove={(i) => setExams(exams.filter((_, j) => j !== i))} />
                  <FileZone icon="bi-prescription2" label="Ordonnances" hint="PDF, JPG, PNG · 10 Mo max par fichier"
                            files={prescriptions} onAdd={addFiles(setPrescriptions, prescriptions)} onRemove={(i) => setPrescriptions(prescriptions.filter((_, j) => j !== i))} />
                  {fileError && <small className="err">{fileError}</small>}
                </div>
              )}

              {step === 3 && (
                <div className="bk_card">
                  <h2><i className="bi bi-clipboard-check"></i> Vérification et paiement</h2>
                  <div className="bk_summary">
                    <div><h3>Rendez-vous <button type="button" onClick={() => go(0)}><i className="bi bi-pencil"></i> Modifier</button></h3>
                      <p>{doctorName(d)} · {formatSlot(form.debut)}</p></div>
                    <div><h3>Vos informations <button type="button" onClick={() => go(0)}><i className="bi bi-pencil"></i> Modifier</button></h3>
                      <p>{form.nom_complet} · {fullPhone(form.whatsapp, country)} · {country?.pays}<br />{form.age} ans · {form.taille} cm · {form.poids} kg</p></div>
                    <div><h3>Symptômes <button type="button" onClick={() => go(1)}><i className="bi bi-pencil"></i> Modifier</button></h3>
                      <p className="bk_clamp">{form.symptomes}</p></div>
                    <div><h3>Documents <button type="button" onClick={() => go(2)}><i className="bi bi-pencil"></i> Modifier</button></h3>
                      <p>{allFiles.length ? `${allFiles.length} fichier${allFiles.length > 1 ? 's' : ''}` : 'Aucun'}</p></div>
                  </div>

                  <div className="bk_pay-head"><span>Montant de la consultation<small>{burundi ? 'Résidant du Burundi' : "Résidant à l'étranger"}</small></span><b>{price}{equivalent && <small>Équivalent en BIF : {equivalent}</small>}</b></div>
                  <p className="bk_muted">Aucun paiement en ligne : payez avec l'un des moyens ci-dessous, puis joignez la preuve (ou envoyez-la plus tard sur WhatsApp).</p>
                  <div className="bk_modes">
                    {(modes.data ?? []).map((m) => (
                      <label key={m.id} className={String(form.mode_paiement_id) === String(m.id) ? 'active' : ''}>
                        <input type="radio" name="mode" value={m.id} checked={String(form.mode_paiement_id) === String(m.id)} onChange={() => set('mode_paiement_id', m.id)} />
                        <div><b>{m.nom}</b>{m.numero_compte && <span>{m.numero_compte}{m.nom_compte ? ` · ${m.nom_compte}` : ''}</span>}</div>
                      </label>
                    ))}
                  </div>
                  {errors.mode_paiement_id && <small className="err">{errors.mode_paiement_id}</small>}
                  <label className="bk_drop small">
                    <i className="bi bi-receipt"></i>
                    <b>{proof ? proof.name : 'Joindre la preuve de paiement'}</b>
                    <span>{proof ? 'Cliquez pour changer' : 'Capture ou photo du reçu · facultatif'}</span>
                    <input type="file" accept={ACCEPT} onChange={(e) => {
                      const f = e.target.files[0]
                      if (f && (f.size > MAX_FILE || !/\.(pdf|jpe?g|png|webp)$/i.test(f.name))) setFileError('Preuve : PDF, JPG, PNG ou WEBP, 10 Mo max')
                      else { setFileError(''); setProof(f || null) }
                    }} />
                  </label>
                  {fileError && <small className="err">{fileError}</small>}
                  <label className={`bk_accept${errors.accepte ? ' error' : ''}`}>
                    <input type="checkbox" checked={form.accepte} onChange={(e) => set('accepte', e.target.checked)} />
                    <span>J'accepte que ces informations soient transmises à l'expert pour ma consultation. Elles restent confidentielles.</span>
                  </label>
                  {errors.accepte && <small className="err">{errors.accepte}</small>}
                </div>
              )}

              <div className="bk_nav">
                <button type="button" className="bk_btn ghost" disabled={step === 0 || sending} onClick={() => go(step - 1)}><i className="bi bi-arrow-left"></i> Précédent</button>
                {step < 3 ? (
                  <button type="button" className="bk_btn primary" onClick={next}>Suivant <i className="bi bi-arrow-right"></i></button>
                ) : (
                  <button type="submit" className="bk_btn whatsapp" disabled={sending}>
                    {sending ? <><span className="spinner-border spinner-border-sm"></span> Envoi…</> : <><i className="bi bi-whatsapp"></i> Envoyer sur WhatsApp</>}
                  </button>
                )}
              </div>
            </form>

            {/* COLONNE : L'EXPERT + COMMENT ÇA MARCHE */}
            <aside className="bk_side">
              {expertCard}
              <div className="bk_how">
                <h3><i className="bi bi-question-circle"></i> Comment ça marche ?</h3>
                <ol>{HOW.map(([icon, text], i) => <li key={text}><span>{i + 1}</span><i className={`bi ${icon}`}></i> {text}</li>)}</ol>
                <p><i className="bi bi-lock"></i> Pas de compte à créer. Vos informations médicales restent confidentielles.</p>
              </div>
            </aside>
          </div>
        )}
      </div>
    </div>
  )
}
