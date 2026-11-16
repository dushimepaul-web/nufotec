// Téléconsultation : nos experts — conversion de Consultations/views/Medecin_present_View.php.
// Mêmes 5 sections : bandeau, filtres, statistiques + experts (avec fenêtre de détail), « pourquoi nous choisir »,
// produits. Design du site (couleurs du logo, aucun dégradé, mobile d'abord).
import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../lib/api'
import { formatHour, formatSlot, isoDay } from '../lib/format'
import { DOCTOR_FALLBACK, doctorName, doctorPrices } from '../lib/doctors'
import { ROUTES } from '../lib/routes'
import { usePageMeta } from '../lib/site'
import useApi from '../lib/useApi'
import Modal from '../ui/Modal'
import PageBanner from '../ui/PageBanner'
import ProductCard from '../ui/ProductCard'
import '../styles/doctors.css'

const DAYS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche']

const WHY = [
  ['bi-clock-history', '7 jours sur 7', 'Des créneaux tous les jours, y compris le week-end selon les experts.'],
  ['bi-shield-check', 'Experts qualifiés', 'Des professionnels de santé dont la licence est contrôlée.'],
  ['bi-cash-coin', 'Tarifs clairs', 'Le prix est affiché avant de réserver, en BIF pour les résidents du Burundi.'],
  ['bi-lock', 'Confidentiel', 'Vos informations médicales sont stockées de façon privée et sécurisée.'],
]


/** Note sur 5 étoiles (les anciennes notes sur 10 sont ramenées à 5) */
const stars = (note) => {
  const n = Number(note) || 0
  return Math.round(n > 5 ? n / 2 : n)
}

/** Premier créneau libre d'un expert dans les 7 prochains jours */
function useNextSlot(uuid, enabled) {
  const [slot, setSlot] = useState(undefined)
  useEffect(() => {
    if (!enabled) return undefined
    let alive = true
    const now = new Date()
    api.get(`/medecins/${uuid}/creneaux`, { du: isoDay(now), au: isoDay(new Date(now.getTime() + 7 * 864e5)) })
      .then((list) => alive && setSlot(list?.find((s) => new Date(s.debut) > now)?.debut ?? null))
      .catch(() => alive && setSlot(null))
    return () => { alive = false }
  }, [uuid, enabled])
  return slot
}

function Stars({ note, count }) {
  const n = stars(note)
  return (
    <div className="doc_rating" aria-label={`${n} étoiles sur 5`}>
      {Array.from({ length: 5 }, (_, i) => <i key={i} className={`bi bi-star${i < n ? '-fill' : ''}`}></i>)}
      {count > 0 && <span>({count.toLocaleString('fr-FR')} avis)</span>}
    </div>
  )
}

function Prices({ d, large = false }) {
  const p = doctorPrices(d)
  return (
    <div className={`doc_prices${large ? ' large' : ''}`}>
      {p.burundi && (
        <div className="doc_price bi"><i className="bi bi-geo-alt-fill"></i><span>Résidant du Burundi</span><b>{p.burundi}</b></div>
      )}
      {p.etranger && (
        <div className="doc_price usd">
          <i className="bi bi-globe2"></i><span>Résidant à l'étranger</span><b>{p.etranger}</b>
          {p.equivalent && <small>Équivalent en BIF : {p.equivalent}</small>}
        </div>
      )}
    </div>
  )
}

function DoctorCard({ d, onDetails }) {
  const available = d.disponible
  const slot = useNextSlot(d.uuid, available)
  return (
    <article className="doc_card">
      <div className="doc_head">
        <div className="doc_avatar">
          <img src={d.photo_url || DOCTOR_FALLBACK} alt="" onError={(e) => { e.currentTarget.src = DOCTOR_FALLBACK }} />
          <span className={`doc_status${d.disponible_maintenant ? ' on' : ''}`} title={d.disponible_maintenant ? 'Disponible maintenant' : 'Sur rendez-vous'}></span>
        </div>
        <div className="doc_id">
          <h3>{doctorName(d)} {d.licence_verifiee && <i className="bi bi-patch-check-fill doc_verified" title="Licence vérifiée"></i>}</h3>
          <div className="doc_spec">{d.specialite || 'Santé générale'}</div>
          <Stars note={d.note_moyenne} count={d.nombre_avis} />
        </div>
      </div>
      <ul className="doc_facts">
        {d.annees_experience > 0 && <li><i className="bi bi-award"></i> {d.annees_experience} ans d'expérience</li>}
        {d.langues?.length > 0 && <li><i className="bi bi-translate"></i> {d.langues.join(', ')}</li>}
        <li><i className="bi bi-camera-video"></i> Consultation de {d.duree_minutes || 30} minutes</li>
      </ul>
      <Prices d={d} />
      {available && (
        <div className="doc_next">
          <i className="bi bi-lightning-charge-fill"></i>
          {slot === undefined ? 'Recherche du prochain créneau…' : slot ? <>Prochain créneau : <b>{formatSlot(slot)}</b></> : 'Aucun créneau cette semaine'}
        </div>
      )}
      <div className="doc_actions">
        <button type="button" className="doc_btn ghost" onClick={() => onDetails(d)}><i className="bi bi-eye"></i> Détails</button>
        {available ? (
          <Link to={ROUTES.booking(d.uuid)} className="doc_btn primary"><i className="bi bi-calendar-plus"></i> Prendre rendez-vous</Link>
        ) : (
          <button type="button" className="doc_btn ghost" disabled><i className="bi bi-clock"></i> Indisponible</button>
        )}
      </div>
    </article>
  )
}

function DoctorModal({ doctor, onClose }) {
  const detail = useApi(doctor ? `/medecins/${doctor.uuid}` : null)
  const d = { ...doctor, ...(detail.data ?? {}) }
  const schedule = [...(d.horaires ?? [])].sort((a, b) => DAYS.indexOf(a.jour) - DAYS.indexOf(b.jour))
  return (
    <Modal open={Boolean(doctor)} onClose={onClose} size="lg" icon="bi-person-badge" title="Profil de l'expert">
      {doctor && (
        <div className="doc_detail">
          <div className="doc_detail-head">
            <img src={d.photo_url || DOCTOR_FALLBACK} alt="" onError={(e) => { e.currentTarget.src = DOCTOR_FALLBACK }} />
            <div>
              <h2>{doctorName(d)}</h2>
              <p>{d.specialite || 'Santé générale'}</p>
              <div className="doc_tags">
                {d.licence_verifiee && <span className="ok"><i className="bi bi-check-circle"></i> Licence vérifiée</span>}
                <span className={d.disponible ? 'ok' : 'off'}><i className="bi bi-circle-fill"></i> {d.disponible ? 'Disponible' : 'Indisponible'}</span>
              </div>
            </div>
          </div>
          {d.bio && <p className="doc_bio">{d.bio}</p>}
          <div className="doc_detail-grid">
            <section>
              <h4><i className="bi bi-info-circle"></i> Informations</h4>
              <dl>
                {d.annees_experience > 0 && <><dt>Expérience</dt><dd>{d.annees_experience} ans</dd></>}
                {d.langues?.length > 0 && <><dt>Langues</dt><dd>{d.langues.join(', ')}</dd></>}
                {d.diplomes && <><dt>Diplômes</dt><dd>{d.diplomes}</dd></>}
                <dt>Durée</dt><dd>{d.duree_minutes || 30} minutes</dd>
              </dl>
            </section>
            <section>
              <h4><i className="bi bi-clock"></i> Horaires</h4>
              {detail.loading ? <span className="spinner-border spinner-border-sm text-success"></span> : schedule.length ? (
                <table className="doc_hours">
                  <tbody>
                    {schedule.map((h) => <tr key={h.jour}><td>{h.jour}</td><td>{formatHour(h.debut)} – {formatHour(h.fin)}</td></tr>)}
                  </tbody>
                </table>
              ) : <p className="doc_muted">Sur rendez-vous.</p>}
            </section>
          </div>
          <h4 className="doc_h4"><i className="bi bi-cash-coin"></i> Tarifs</h4>
          <Prices d={d} large />
          <div className="doc_detail-cta">
            {d.disponible ? (
              <Link to={ROUTES.booking(d.uuid)} className="doc_btn primary lg" onClick={onClose}>
                <i className="bi bi-calendar-plus"></i> Prendre rendez-vous
              </Link>
            ) : (
              <button type="button" className="doc_btn ghost lg" disabled><i className="bi bi-clock"></i> Expert indisponible</button>
            )}
          </div>
        </div>
      )}
    </Modal>
  )
}

export default function Doctors() {
  usePageMeta('Téléconsultation', 'Consultez un expert en ligne : choisissez votre expert, réservez un créneau, consultez en vidéo.')
  const doctors = useApi('/medecins')
  const products = useApi('/produits', { per_page: 4 })
  const [specialty, setSpecialty] = useState('')
  const [search, setSearch] = useState('')
  const [selected, setSelected] = useState(null)

  const list = useMemo(() => doctors.data ?? [], [doctors.data])
  const specialties = useMemo(() => [...new Set(list.map((d) => d.specialite || 'Santé générale'))].sort(), [list])
  const shown = list.filter((d) => {
    const spec = d.specialite || 'Santé générale'
    const text = `${doctorName(d)} ${spec} ${(d.langues ?? []).join(' ')}`.toLowerCase()
    return (!specialty || spec === specialty) && (!search.trim() || text.includes(search.trim().toLowerCase()))
  })
  const availableNow = list.filter((d) => d.disponible_maintenant).length

  return (
    <div className="doc">
      {/* 1. BANDEAU */}
      <PageBanner crumbs={[['Téléconsultation']]} badge={<><i className="bi bi-camera-video-fill"></i> Consultation vidéo en ligne</>}
                  title="Nos experts" subtitle="Choisissez votre expert, réservez un créneau et consultez depuis chez vous, en toute confidentialité." />

      <div className="doc_wrap">
        {/* 2. FILTRES */}
        <section className="doc_filters" aria-label="Filtres">
          <div className="doc_search">
            <i className="bi bi-search"></i>
            <input type="search" placeholder="Nom, spécialité ou langue…" aria-label="Rechercher un expert" maxLength={40}
                   value={search} onChange={(e) => setSearch(e.target.value.replace(/[<>{}]/g, ''))} />
          </div>
          <select value={specialty} onChange={(e) => setSpecialty(e.target.value)} aria-label="Spécialité">
            <option value="">Toutes les spécialités</option>
            {specialties.map((s) => <option key={s} value={s}>{s}</option>)}
          </select>
        </section>

        {/* 3. STATISTIQUES + EXPERTS */}
        <section className="doc_main">
          <div className="doc_stats">
            <div><i className="bi bi-people-fill"></i> <b>{list.length}</b> expert{list.length > 1 ? 's' : ''}</div>
            <div><span className="doc_dot"></span> <b>{availableNow}</b> disponible{availableNow > 1 ? 's' : ''} maintenant</div>
          </div>
          {doctors.loading ? (
            <div className="doc_grid">{[0, 1, 2].map((i) => <div key={i} className="doc_card doc_skeleton"></div>)}</div>
          ) : doctors.error ? (
            <div className="doc_empty"><i className="bi bi-exclamation-triangle"></i><p>{doctors.error.message}</p></div>
          ) : shown.length === 0 ? (
            <div className="doc_empty"><i className="bi bi-emoji-frown"></i><p>Aucun expert ne correspond à votre recherche.</p></div>
          ) : (
            <div className="doc_grid">{shown.map((d) => <DoctorCard key={d.uuid} d={d} onDetails={setSelected} />)}</div>
          )}
        </section>
      </div>

      {/* 4. POURQUOI NOUS CHOISIR */}
      <section className="doc_why">
        <div className="doc_wrap">
          <div className="doc_why-grid">
            {WHY.map(([icon, title, text]) => (
              <div key={title} className="doc_why-item">
                <i className={`bi ${icon}`}></i>
                <h3>{title}</h3>
                <p>{text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* 5. PRODUITS */}
      {(products.data?.items ?? []).length > 0 && (
        <section className="doc_products">
          <div className="doc_wrap">
            <div className="doc_products-head">
              <h2>Nos produits naturels</h2>
              <Link to={ROUTES.products}>Toute la boutique <i className="bi bi-arrow-right"></i></Link>
            </div>
            <div className="pcard-grid">
              {products.data.items.map((p) => <ProductCard key={p.uuid} product={p} animate={false} />)}
            </div>
          </div>
        </section>
      )}

      <DoctorModal doctor={selected} onClose={() => setSelected(null)} />
    </div>
  )
}
