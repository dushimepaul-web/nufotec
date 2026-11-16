// Mon compte — conversion de Home_page/Dashboard_Client.php : même structure (en-tête « Bonjour », menu latéral avec
// profil et déconnexion, tableau de bord, rubriques), avec les vraies données de la personne. Une rubrique n'apparaît
// que si la personne a quelque chose dedans : commandes, consultations, dossier investisseur, fiche courtier.
// Toujours présentes : tableau de bord et informations personnelles.
import { useEffect, useMemo, useState } from 'react'
import { Link, Navigate, useNavigate, useSearchParams } from 'react-router-dom'
import api from '../lib/api'
import { clearSession, getUser, initials, passwordProblem } from '../lib/auth'
import { formatDate, formatSlot } from '../lib/format'
import { BURUNDI_ID, DEFAULT_COUNTRIES, dialCode, fullPhone } from '../lib/phone'
import { ROUTES } from '../lib/routes'
import { cleanName } from '../lib/sanitize'
import { usePageMeta } from '../lib/site'
import useApi from '../lib/useApi'
import CountrySelect from '../ui/CountrySelect'
import PhoneField from '../ui/PhoneField'
import '../styles/account.css'

const ORDER_STATUS = { en_attente: ['En attente', 'wait'], en_cours: ['En cours', 'info'], livree: ['Livrée', 'ok'], annulee: ['Annulée', 'off'] }
const CONSULT_STATUS = {
  en_attente: ['En attente de confirmation', 'wait'], confirmee: ['Confirmée', 'ok'], en_cours: ['En cours', 'info'],
  terminee: ['Terminée', 'ok'], annulee: ['Annulée', 'off'], refusee: ['Refusée', 'off'], absent: ['Absence', 'off'],
}
const PAY_STATUS = { en_attente: 'Paiement en attente', preuve_envoyee: 'Preuve envoyée', paye: 'Payé', rembourse: 'Remboursé' }
const INVESTOR_STATUS = {
  nouveau: ['Reçu', 'wait'], contacte: ['Contacté', 'info'], qualifie: ['Qualifié', 'ok'], due_diligence: ['Due diligence', 'ok'],
  engage: ['Engagé', 'ok'], perdu: ['Clos', 'off'],
}
const INVESTOR_STEPS = ['nouveau', 'contacte', 'qualifie', 'due_diligence', 'engage']
const BROKER_STATUS = { en_attente: ["En cours d'examen", 'wait'], approuve: ['Approuvé', 'ok'], refuse: ['Refusé', 'off'], suspendu: ['Suspendu', 'off'] }
const QUALIFIED = ['qualifie', 'due_diligence', 'engage']
// mêmes libellés que l'API (app/modules/investors/labels.py)
const COMMITMENT_FR = { 'Below 250K': 'Moins de 250K €', '250K-1M': '250K - 1M €', '1M-5M': '1M - 5M €', '5M+': '5M+ €', 'To be discussed': 'À discuter' }
const REGULATORY_FR = { Licensed: 'Agréé', Exempt: 'Exempté', Unlicensed: 'Non agréé' }
const TIMELINE_FR = { Immediate: 'Immédiat', '3-6 months': '3-6 mois', '6-12 months': '6-12 mois', Exploratory: 'Exploratoire' }

// même écriture que les prix du site : 60.000 BIF
const orderAmount = (value, currency) => `${Number(value).toLocaleString('de-DE', { maximumFractionDigits: 2 })} ${currency}`

function Badge({ map, value }) {
  const [label, tone] = map[value] ?? [value, 'info']
  return <span className={`ac_badge ${tone}`}>{label}</span>
}

function Empty({ icon, children }) {
  return <div className="ac_empty"><i className={`bi ${icon}`}></i><p>{children}</p></div>
}

/* ───────────── rubriques ───────────── */

function Orders({ limit }) {
  const { data, loading } = useApi('/commandes', { per_page: limit || 50 })
  const items = data?.items ?? []
  if (loading) return <div className="ac_loading"><span className="spinner-border spinner-border-sm"></span></div>
  if (!items.length) return <Empty icon="bi-bag">Aucune commande.</Empty>
  return (
    <ul className="ac_list">
      {items.map((o) => (
        <li key={o.numero}>
          <div className="ac_item-main">
            <b>{o.produit_slug ? <Link to={ROUTES.product(o.produit_slug)}>{o.produit}</Link> : o.produit}</b>
            <span>N° {o.numero} · {formatDate(o.created_at)} · quantité {o.quantite}</span>
          </div>
          <div className="ac_item-side">
            <Badge map={ORDER_STATUS} value={o.statut} />
            {o.total != null && o.devise ? (
              <em><b>{orderAmount(o.total, o.devise)}</b>{o.quantite > 1 && <> · {o.prix_affiche || orderAmount(o.prix_unitaire, o.devise)} × {o.quantite}</>}</em>
            ) : o.prix_affiche && <em>{o.prix_affiche}</em>}
          </div>
        </li>
      ))}
    </ul>
  )
}

function Consultations({ limit }) {
  const { data, loading } = useApi('/consultations', { per_page: limit || 50 })
  const items = data?.items ?? []
  if (loading) return <div className="ac_loading"><span className="spinner-border spinner-border-sm"></span></div>
  if (!items.length) return <Empty icon="bi-camera-video">Aucune consultation.</Empty>
  return (
    <ul className="ac_list">
      {items.map((c) => (
        <li key={c.numero}>
          <div className="ac_item-main">
            <b>{[c.medecin?.prenom, c.medecin?.nom].filter((x) => x && x !== '.').join(' ') || 'Expert'} <small>{c.specialite}</small></b>
            <span>{formatSlot(c.debut)} · N° {c.numero}</span>
            {c.lie_par_telephone && <span className="ac_note"><i className="bi bi-telephone"></i> Réservée sans compte avec votre numéro WhatsApp</span>}
          </div>
          <div className="ac_item-side">
            <Badge map={CONSULT_STATUS} value={c.statut} />
            {c.paiement_statut && <em>{PAY_STATUS[c.paiement_statut] ?? c.paiement_statut}</em>}
          </div>
        </li>
      ))}
    </ul>
  )
}

function InvestorFile() {
  const file = useApi('/investisseur/dossier')
  const d = file.data
  const qualified = d && QUALIFIED.includes(d.statut)
  const nda = useApi(qualified ? '/investisseur/nda' : null)
  const [ndaState, setNdaState] = useState(null)
  const accepted = ndaState?.accepte ?? nda.data?.accepte
  const room = useApi(qualified && accepted ? '/investisseur/data-room' : null, undefined, [accepted])
  const [busy, setBusy] = useState('')
  const [error, setError] = useState('')

  if (file.loading) return <div className="ac_loading"><span className="spinner-border spinner-border-sm"></span></div>
  if (!d) return <Empty icon="bi-graph-up-arrow">Aucun dossier investisseur. <Link to={ROUTES.investor}>Manifester mon intérêt</Link></Empty>
  const stepIndex = INVESTOR_STEPS.indexOf(d.statut)

  const accept = async () => {
    setBusy('nda'); setError('')
    try { setNdaState(await api.post('/investisseur/nda/accepter')) } catch (err) { setError(err.message) } finally { setBusy('') }
  }
  const download = async (doc) => {
    setBusy(doc.uuid); setError('')
    const tab = window.open('', '_blank')
    try {
      const link = await api.get(`/investisseur/data-room/${doc.uuid}/lien`)
      if (tab) tab.location.href = link.url; else window.location.assign(link.url)
    } catch (err) { tab?.close(); setError(err.message) } finally { setBusy('') }
  }

  return (
    <div className="ac_file">
      <div className="ac_file-head">
        <div><b>{d.nom_complet}</b><span>{[d.organisation, d.pays].filter(Boolean).join(' · ')}</span></div>
        <Badge map={INVESTOR_STATUS} value={d.statut} />
      </div>
      {d.statut !== 'perdu' && (
        <ol className="ac_track">
          {INVESTOR_STEPS.map((s, i) => <li key={s} className={i <= stepIndex ? 'done' : ''}><span></span>{INVESTOR_STATUS[s][0]}</li>)}
        </ol>
      )}
      <dl className="ac_dl">
        <dt>Montant envisagé</dt><dd>{COMMITMENT_FR[d.montant] ?? (d.montant || '—')}</dd>
        <dt>Horizon</dt><dd>{TIMELINE_FR[d.calendrier] ?? (d.calendrier || '—')}</dd>
        <dt>Envoyé le</dt><dd>{formatDate(d.created_at)}</dd>
      </dl>
      {!qualified ? (
        <p className="ac_info"><i className="bi bi-hourglass-split"></i> Votre dossier est en cours d'étude. L'accord de confidentialité et la data room s'ouvriront dès qu'il sera qualifié par notre équipe.</p>
      ) : !accepted ? (
        <div className="ac_nda">
          <h4><i className="bi bi-file-earmark-lock"></i> Accord de confidentialité {nda.data?.version && <small>version {nda.data.version}</small>}</h4>
          <div className="ac_nda-text">{nda.data?.texte}</div>
          <button type="button" className="ac_btn primary" disabled={busy === 'nda' || nda.loading} onClick={accept}>
            {busy === 'nda' ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-check2"></i>} J'accepte l'accord
          </button>
        </div>
      ) : (
        <div className="ac_room">
          <h4><i className="bi bi-folder2-open"></i> Data room</h4>
          {room.loading ? <span className="spinner-border spinner-border-sm"></span> : (room.data ?? []).length === 0 ? <p className="ac_muted">Aucun document pour le moment.</p> : (
            <ul className="ac_list">
              {room.data.map((doc) => (
                <li key={doc.uuid}>
                  <div className="ac_item-main"><b>{doc.titre}</b><span>{doc.description || doc.nom_fichier} · {(doc.taille / 1024 / 1024).toFixed(1)} Mo</span></div>
                  <button type="button" className="ac_btn ghost sm" disabled={busy === doc.uuid} onClick={() => download(doc)}><i className="bi bi-download"></i> Ouvrir</button>
                </li>
              ))}
            </ul>
          )}
        </div>
      )}
      {error && <small className="err">{error}</small>}
    </div>
  )
}

function BrokerFile() {
  const { data: d, loading } = useApi('/courtier/dossier')
  if (loading) return <div className="ac_loading"><span className="spinner-border spinner-border-sm"></span></div>
  if (!d) return <Empty icon="bi-people">Aucune fiche courtier. <Link to={ROUTES.broker}>Devenir courtier</Link></Empty>
  return (
    <div className="ac_file">
      <div className="ac_file-head">
        <div><b>{d.nom_complet}</b><span>{[d.cabinet, d.pays].filter(Boolean).join(' · ')}</span></div>
        <Badge map={BROKER_STATUS} value={d.statut} />
      </div>
      <dl className="ac_dl">
        <dt>Statut réglementaire</dt><dd>{REGULATORY_FR[d.statut_reglementaire] ?? (d.statut_reglementaire || '—')}{d.autorite_reglementaire ? ` (${d.autorite_reglementaire})` : ''}</dd>
        <dt>Modèle d'engagement</dt><dd>{d.modele_remuneration || '—'}</dd>
        <dt>Envoyée le</dt><dd>{formatDate(d.created_at)}</dd>
      </dl>
      {d.statut === 'en_attente' && <p className="ac_info"><i className="bi bi-hourglass-split"></i> Nous vérifions votre statut réglementaire et votre conformité AML/KYC. Aucun mandat n'existe sans accord écrit.</p>}
      {d.motif_decision && <p className="ac_info"><i className="bi bi-chat-left-text"></i> {d.motif_decision}</p>}
    </div>
  )
}

function localPart(phone, country) {
  const digits = (phone || '').replace(/\D/g, '')
  const code = (dialCode(country) || '').replace(/\D/g, '')
  return code && digits.startsWith(code) ? digits.slice(code.length) : digits
}

function Profile({ user, onSaved }) {
  const countries = useApi('/pays')
  const list = countries.data ?? DEFAULT_COUNTRIES
  const [f, setF] = useState({ prenom: user.prenom || '', nom: user.nom || '', pays_id: user.pays?.id ?? BURUNDI_ID, telephone: localPart(user.telephone, list.find((c) => c.id === (user.pays?.id ?? BURUNDI_ID))) })
  const [state, setState] = useState({ saving: false, msg: '', error: '' })
  const [pw, setPw] = useState({ current: '', next: '', confirm: '' })
  const [pwState, setPwState] = useState({ saving: false, msg: '', error: '' })
  const country = list.find((c) => c.id === Number(f.pays_id))

  const save = async (e) => {
    e.preventDefault()
    setState({ saving: true, msg: '', error: '' })
    try {
      const updated = await api.patch('/auth/me', {
        prenom: f.prenom.trim(), nom: f.nom.trim(), pays_id: Number(f.pays_id), telephone: fullPhone(f.telephone, country) || null,
      })
      onSaved(updated)
      setState({ saving: false, msg: 'Informations enregistrées', error: '' })
    } catch (err) { setState({ saving: false, msg: '', error: err.message }) }
  }
  const changePw = async (e) => {
    e.preventDefault()
    const problem = passwordProblem(pw.next, { nomComplet: `${user.prenom} ${user.nom}`, email: user.email })
    if (problem || pw.next !== pw.confirm) { setPwState({ saving: false, msg: '', error: problem || 'Les deux mots de passe ne correspondent pas' }); return }
    setPwState({ saving: true, msg: '', error: '' })
    try {
      await api.post('/auth/change-password', { current_password: pw.current, new_password: pw.next })
      setPw({ current: '', next: '', confirm: '' })
      setPwState({ saving: false, msg: 'Mot de passe modifié', error: '' })
    } catch (err) { setPwState({ saving: false, msg: '', error: err.message }) }
  }

  return (
    <div className="ac_profile">
      <form className="ac_form" onSubmit={save}>
        <div className="ac_row">
          <label className="ac_field"><span>Prénom</span><input value={f.prenom} maxLength={50} onChange={(e) => setF({ ...f, prenom: cleanName(e.target.value).slice(0, 50) })} /></label>
          <label className="ac_field"><span>Nom</span><input value={f.nom} maxLength={50} onChange={(e) => setF({ ...f, nom: cleanName(e.target.value).slice(0, 50) })} /></label>
        </div>
        <div className="ac_row">
          <div className="ac_field" role="group" aria-label="Pays"><span>Pays</span><CountrySelect countries={list} value={f.pays_id} onChange={(id) => setF({ ...f, pays_id: id })} /></div>
          <label className="ac_field"><span>Téléphone WhatsApp</span>
            <PhoneField country={country} value={f.telephone} onChange={(v) => setF({ ...f, telephone: v })}
                        hint="Vos commandes et rendez-vous passés avec ce numéro apparaissent ici" />
          </label>
        </div>
        <label className="ac_field"><span>Email</span><input value={user.email} disabled /></label>
        {state.error && <small className="err">{state.error}</small>}
        {state.msg && <small className="ok"><i className="bi bi-check-circle"></i> {state.msg}</small>}
        <button type="submit" className="ac_btn primary" disabled={state.saving}>{state.saving ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-save"></i>} Enregistrer</button>
      </form>
      <form className="ac_form" onSubmit={changePw}>
        <h3>Changer mon mot de passe</h3>
        <label className="ac_field"><span>Mot de passe actuel</span><input type="password" autoComplete="current-password" value={pw.current} onChange={(e) => setPw({ ...pw, current: e.target.value })} /></label>
        <div className="ac_row">
          <label className="ac_field"><span>Nouveau mot de passe</span><input type="password" autoComplete="new-password" value={pw.next} onChange={(e) => setPw({ ...pw, next: e.target.value })} /></label>
          <label className="ac_field"><span>Confirmer</span><input type="password" autoComplete="new-password" value={pw.confirm} onChange={(e) => setPw({ ...pw, confirm: e.target.value })} /></label>
        </div>
        {pwState.error && <small className="err">{pwState.error}</small>}
        {pwState.msg && <small className="ok"><i className="bi bi-check-circle"></i> {pwState.msg}</small>}
        <button type="submit" className="ac_btn ghost" disabled={pwState.saving}><i className="bi bi-key"></i> Modifier le mot de passe</button>
      </form>
    </div>
  )
}

/* ───────────── page ───────────── */

export default function Account() {
  const navigate = useNavigate()
  const [params, setParams] = useSearchParams()
  const [stored] = useState(getUser)
  const [saved, setSaved] = useState(null)
  const [today] = useState(() => new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }))
  const space = useApi(stored ? '/mon-espace' : null)
  const me = useApi(stored ? '/auth/me' : null)
  const user = saved ?? me.data ?? stored
  usePageMeta('Mon compte')

  useEffect(() => {
    if (me.data) localStorage.setItem('nuf_user', JSON.stringify(me.data))
  }, [me.data])
  useEffect(() => {
    if (me.error?.status === 401) { clearSession(); navigate(`${ROUTES.login}?suite=${ROUTES.account}`, { replace: true }) }
  }, [me.error, navigate])

  const s = space.data
  const menu = useMemo(() => [
    ['tableau', 'bi-grid-1x2', 'Tableau de bord', true],
    ['commandes', 'bi-bag-check', 'Mes commandes', s?.commandes > 0, s?.commandes],
    ['consultations', 'bi-camera-video', 'Mes consultations', s?.consultations > 0, s?.consultations],
    ['investisseur', 'bi-graph-up-arrow', 'Mon dossier investisseur', s?.investisseur],
    ['courtier', 'bi-people', 'Ma fiche courtier', s?.courtier],
    ['profil', 'bi-person', 'Mes informations', true],
  ].filter((m) => m[3]), [s])

  if (!user) return <Navigate to={`${ROUTES.login}?suite=${ROUTES.account}`} replace />
  const tab = menu.some((m) => m[0] === params.get('rubrique')) ? params.get('rubrique') : 'tableau'
  const go = (t) => { setParams(t === 'tableau' ? {} : { rubrique: t }); window.scrollTo({ top: 0, behavior: 'smooth' }) }
  const logout = async () => {
    try { await api.post('/auth/logout') } catch { /* session déjà fermée */ }
    clearSession()
    navigate(ROUTES.home)
  }
  const nothing = s && !s.commandes && !s.consultations && !s.investisseur && !s.courtier

  return (
    <div className="ac">
      <div className="ac_wrap">
        <header className="ac_hello">
          <div><h1>Bonjour, {user.prenom || 'cher client'} !</h1><p>{today}</p></div>
        </header>

        <div className="ac_grid">
          {/* MENU LATÉRAL */}
          <aside className="ac_side">
            <div className="ac_me">
              {user.photo_url ? <img src={user.photo_url} alt="" /> : <div className="ac_avatar">{initials(user)}</div>}
              <div><b>{[user.prenom, user.nom].filter(Boolean).join(' ')}</b><span>{user.email}</span></div>
            </div>
            <nav className="ac_menu" aria-label="Rubriques de mon compte">
              {menu.map(([key, icon, label, , count]) => (
                <button key={key} type="button" className={tab === key ? 'active' : ''} onClick={() => go(key)}>
                  <i className={`bi ${icon}`}></i><span>{label}</span>{count > 0 && <em>{count}</em>}
                </button>
              ))}
            </nav>
            <button type="button" className="ac_logout" onClick={logout}><i className="bi bi-box-arrow-right"></i> Déconnexion</button>
          </aside>

          {/* CONTENU */}
          <main className="ac_main">
            {space.loading ? <div className="ac_loading"><span className="spinner-border text-success"></span></div> : tab === 'tableau' ? (
              <>
                {s && (
                  <div className="ac_stats">
                    {s.commandes > 0 && <button type="button" onClick={() => go('commandes')}><i className="bi bi-bag-check"></i><b>{s.commandes}</b><span>Commande{s.commandes > 1 ? 's' : ''}</span></button>}
                    {s.consultations > 0 && <button type="button" onClick={() => go('consultations')}><i className="bi bi-camera-video"></i><b>{s.consultations}</b><span>Consultation{s.consultations > 1 ? 's' : ''}</span></button>}
                    {s.investisseur && <button type="button" onClick={() => go('investisseur')}><i className="bi bi-graph-up-arrow"></i><b>{INVESTOR_STATUS[s.investisseur_statut]?.[0] ?? '—'}</b><span>Dossier investisseur</span></button>}
                    {s.courtier && <button type="button" onClick={() => go('courtier')}><i className="bi bi-people"></i><b>{BROKER_STATUS[s.courtier_statut]?.[0] ?? '—'}</b><span>Fiche courtier</span></button>}
                  </div>
                )}
                {nothing && (
                  <div className="ac_card ac_welcome">
                    <h2><i className="bi bi-stars"></i> Bienvenue dans votre espace</h2>
                    <p>Vos commandes, vos consultations et vos dossiers apparaîtront ici. Si vous avez déjà commandé ou réservé
                      sans compte, indiquez le même numéro WhatsApp dans <button type="button" className="ac_inline" onClick={() => go('profil')}>Mes informations</button>.</p>
                    <div className="ac_shortcuts">
                      <Link to={ROUTES.products}><i className="bi bi-bag-heart"></i> Boutique</Link>
                      <Link to={ROUTES.doctors}><i className="bi bi-heart-pulse"></i> Téléconsultation</Link>
                      <Link to={ROUTES.investor}><i className="bi bi-graph-up-arrow"></i> Investir</Link>
                    </div>
                  </div>
                )}
                {s?.consultations > 0 && (
                  <section className="ac_card"><h2><i className="bi bi-camera-video"></i> Consultations récentes <button type="button" onClick={() => go('consultations')}>Tout voir</button></h2><Consultations limit={3} /></section>
                )}
                {s?.commandes > 0 && (
                  <section className="ac_card"><h2><i className="bi bi-clock-history"></i> Commandes récentes <button type="button" onClick={() => go('commandes')}>Tout voir</button></h2><Orders limit={3} /></section>
                )}
              </>
            ) : (
              <section className="ac_card">
                {tab === 'commandes' && <><h2><i className="bi bi-bag-check"></i> Mes commandes</h2><Orders /></>}
                {tab === 'consultations' && <><h2><i className="bi bi-camera-video"></i> Mes consultations</h2><Consultations /></>}
                {tab === 'investisseur' && <><h2><i className="bi bi-graph-up-arrow"></i> Mon dossier investisseur</h2><InvestorFile /></>}
                {tab === 'courtier' && <><h2><i className="bi bi-people"></i> Ma fiche courtier</h2><BrokerFile /></>}
                {tab === 'profil' && <><h2><i className="bi bi-person"></i> Mes informations personnelles</h2>
                  <Profile user={user} onSaved={(u) => { setSaved(u); localStorage.setItem('nuf_user', JSON.stringify(u)) }} /></>}
              </section>
            )}
          </main>
        </div>
      </div>
    </div>
  )
}
