// FAQ — conversion de Home_page/faq_view.php : mêmes sections (bandeau ; en-tête « Besoin d'aide ? » ; questions
// numérotées par catégorie, une seule ouverte à la fois ; encadré « Vous avez encore des questions ? » ; état vide).
// Une recherche dans les questions est ajoutée à l'en-tête. Réponses affichées en texte (comme le PHP).
import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { ROUTES } from '../lib/routes'
import { cleanSearch } from '../lib/sanitize'
import { usePageMeta, useSite } from '../lib/site'
import useApi from '../lib/useApi'
import PageBanner from '../ui/PageBanner'
import '../styles/faq.css'

// mêmes catégories et intitulés que la vue PHP
const CATEGORY = {
  general: ['Général', 'bi-info-circle'],
  produits: ['Produits', 'bi-box-seam'],
  qualite: ['Qualité & Certifications', 'bi-patch-check'],
  investissement: ['Investissement', 'bi-graph-up-arrow'],
  social: ['Impact Social', 'bi-people'],
  technique: ['Technique', 'bi-gear'],
}
const ORDER = Object.keys(CATEGORY)
const plain = (html) => (html || '').replace(/<br\s*\/?>/gi, '\n').replace(/<\/p>\s*<p[^>]*>/gi, '\n\n').replace(/<[^>]+>/g, '').replace(/&nbsp;/g, ' ').trim()
const fold = (s) => s.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase()

export default function Faq() {
  usePageMeta('FAQ', 'Questions fréquentes sur NUFOTEC BURUNDI, ses produits, la qualité et l’investissement.')
  const { settings } = useSite()
  const { data, loading, error } = useApi('/faq')
  const [open, setOpen] = useState(null)
  const [search, setSearch] = useState('')
  const waNumber = (settings.contact_whatsapp || settings.site_phone || '').replace(/\D/g, '')

  // catégories dans l'ordre du PHP, questions numérotées en continu
  const groups = useMemo(() => {
    const list = [...(data ?? [])].sort((a, b) => (ORDER.indexOf(a.categorie) + 1 || 99) - (ORDER.indexOf(b.categorie) + 1 || 99))
    const start = list.map((_, i) => list.slice(0, i).reduce((sum, x) => sum + x.questions.length, 0))
    const q = fold(search.trim())
    return list.map((g, gi) => ({
      ...g,
      questions: g.questions.map((item, qi) => ({ ...item, n: start[gi] + qi + 1, text: plain(item.reponse) }))
        .filter((item) => !q || fold(item.question).includes(q) || fold(item.text).includes(q)),
    })).filter((g) => g.questions.length > 0)
  }, [data, search])
  const total = (data ?? []).reduce((s, g) => s + g.questions.length, 0)
  const shown = groups.reduce((s, g) => s + g.questions.length, 0)

  return (
    <div className="fq">
      {/* 1. BANDEAU */}
      <PageBanner crumbs={[['FAQ']]} badge={<><i className="bi bi-question-circle"></i> Questions fréquentes</>} icon="bi-chat-square-text"
                  title="FAQ" subtitle="Trouvez rapidement les réponses à vos questions sur NUFOTEC et nos produits." />

      <section className="fq_section">
        {/* 2. EN-TÊTE */}
        <div className="fq_head">
          <span className="fq_tag">Besoin d'aide ?</span>
          <h2>Foire aux questions</h2>
          <p>Retrouvez ici les réponses aux questions les plus fréquentes. Si vous ne trouvez pas votre réponse, contactez-nous.</p>
          {total > 3 && (
            <div className="fq_search">
              <i className="bi bi-search"></i>
              <input type="search" value={search} placeholder="Rechercher une question…" aria-label="Rechercher une question"
                     onChange={(e) => { setSearch(cleanSearch(e.target.value)); setOpen(null) }} />
              {search && <button type="button" aria-label="Effacer" onClick={() => setSearch('')}><i className="bi bi-x-lg"></i></button>}
            </div>
          )}
        </div>

        {/* 3. QUESTIONS PAR CATÉGORIE */}
        {loading ? (
          <div className="fq_list">{Array.from({ length: 4 }, (_, i) => <div key={i} className="fq_item fq_skeleton"><span></span></div>)}</div>
        ) : error ? (
          <div className="fq_empty"><i className="bi bi-exclamation-triangle"></i><h3>Erreur de chargement</h3><p>{error.message}</p></div>
        ) : total === 0 ? (
          /* 5. ÉTAT VIDE */
          <div className="fq_empty"><i className="bi bi-question-circle"></i><h3>Aucune question disponible</h3><p>Les questions fréquentes seront bientôt disponibles. Revenez nous voir prochainement !</p></div>
        ) : (
          <div className="fq_list">
            {search && <p className="fq_count">{shown} réponse{shown > 1 ? 's' : ''} pour « <b>{search}</b> »{shown === 0 && ' — essayez un autre mot, ou écrivez-nous.'}</p>}
            {groups.map((g) => {
              const [title, icon] = CATEGORY[g.categorie] ?? [g.categorie.charAt(0).toUpperCase() + g.categorie.slice(1), 'bi-question-circle']
              return (
                <div key={g.categorie} className="fq_cat">
                  <h3><i className={`bi ${icon}`}></i> {title}</h3>
                  {g.questions.map((item) => {
                    const isOpen = open === item.id
                    return (
                      <div key={item.id} className={`fq_item ${isOpen ? 'open' : ''}`}>
                        <button type="button" className="fq_q" aria-expanded={isOpen} aria-controls={`fq-a-${item.id}`}
                                onClick={() => setOpen(isOpen ? null : item.id)}>
                          <span className="fq_n">{item.n}</span>
                          <span className="fq_q-text">{item.question}</span>
                          <i className="bi bi-chevron-down"></i>
                        </button>
                        <div id={`fq-a-${item.id}`} className="fq_a" role="region" hidden={!isOpen}>
                          <p>{item.text}</p>
                        </div>
                      </div>
                    )
                  })}
                </div>
              )
            })}
          </div>
        )}

        {/* 4. ENCADRÉ CONTACT */}
        <div className="fq_cta">
          <i className="bi bi-chat-heart fq_cta-ico"></i>
          <h3>Vous avez encore des questions ?</h3>
          <p>Notre équipe est là pour vous aider et répondre à toutes vos interrogations.</p>
          <div className="fq_cta-btns">
            <Link to={ROUTES.contact} className="fq_btn gold"><i className="bi bi-envelope"></i> Contactez-nous</Link>
            {waNumber && <a href={`https://wa.me/${waNumber}`} target="_blank" rel="noopener noreferrer" className="fq_btn wa"><i className="bi bi-whatsapp"></i> WhatsApp</a>}
          </div>
        </div>
      </section>
    </div>
  )
}
