// Tableau de bord administrateur — GET /admin/tableau-de-bord (vue d'ensemble de tous les modules).
import { Link } from 'react-router-dom'
import { ROUTES } from '../../lib/routes'
import useApi from '../../lib/useApi'
import PageTitle from '../../ui/PageTitle'
import Spinner from '../../ui/Spinner'

function InfoCard({ icon, tone, title, value, sub, to }) {
  return (
    <div className="col-xxl-3 col-md-6">
      <div className={`card info-card ${tone}-card`}>
        <div className="card-body">
          <h5 className="card-title">{title}</h5>
          <div className="d-flex align-items-center">
            <div className="card-icon rounded-circle d-flex align-items-center justify-content-center">
              <i className={`bi ${icon}`}></i>
            </div>
            <div className="ps-3">
              <h6>{value}</h6>
              {sub && <span className="text-muted small pt-2 ps-1">{sub}</span>}
            </div>
          </div>
          {to && <Link to={to} className="small stretched-link mt-2 d-inline-block">Voir</Link>}
        </div>
      </div>
    </div>
  )
}

export default function Dashboard() {
  const { data, loading, error } = useApi('/admin/tableau-de-bord')

  return (
    <>
      <PageTitle title="Tableau de bord" crumbs={[{ label: 'Tableau de bord' }]} />

      {loading ? <Spinner /> : error ? (
        <div className="alert alert-danger">{error.message}</div>
      ) : (
        <section className="section dashboard">
          <div className="row">
            <InfoCard icon="bi-people" tone="customers" title="Utilisateurs" value={data.utilisateurs.total}
                     sub={`${data.utilisateurs.nouveaux_30_jours} nouveaux (30 j)`} to={ROUTES.users} />
            <InfoCard icon="bi-calendar-check" tone="sales" title="Consultations à venir" value={data.consultations.a_venir}
                     sub={`${data.consultations.paiements_a_valider} paiements à valider`} to={ROUTES.consultations} />
            <InfoCard icon="bi-cart" tone="revenue" title="Commandes en attente" value={data.commandes.en_attente}
                     sub={`${data.commandes.non_contactees} non contactées`} to={ROUTES.commandes} />
            <InfoCard icon="bi-graph-up-arrow" tone="sales" title="Investisseurs" value={data.investisseurs.nouveaux}
                     sub={`${data.investisseurs.relances_dues} relances dues`} to={ROUTES.investisseurs} />

            <InfoCard icon="bi-envelope" tone="customers" title="Messages non lus" value={data.messages.non_lus}
                     to={ROUTES.messages} />
            <InfoCard icon="bi-collection-play" tone="revenue" title="Médias" value={data.medias.total}
                     sub={`${data.medias.commentaires_a_moderer} commentaires à modérer`} to={ROUTES.medias} />
            <InfoCard icon="bi-robot" tone="sales" title="Chatbot" value={data.chatbot.en_attente_conseiller}
                     sub="en attente d'un conseiller" to={ROUTES.chatbot} />
            <InfoCard icon="bi-eye" tone="customers" title="Visites (7 jours)" value={data.visites_7_jours} />
          </div>
        </section>
      )}
    </>
  )
}
