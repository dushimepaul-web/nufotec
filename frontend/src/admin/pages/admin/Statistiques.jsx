// Statistiques détaillées — agrège les endpoints de stats par module.
// GET /admin/users/stats · /admin/consultations/stats · /admin/commandes/stats
// GET /admin/investisseurs/stats · /admin/medias/stats
import useApi from '../../lib/useApi'
import { labelOf } from '../../lib/format'
import PageTitle from '../../ui/PageTitle'
import Spinner from '../../ui/Spinner'

function Stat({ label, value, tone = 'primary' }) {
  return (
    <div className="col-6 col-md-3">
      <div className={`border-start border-4 border-${tone} rounded px-3 py-2 h-100`}>
        <div className="text-muted small">{label}</div>
        <div className="fs-4 fw-semibold">{value}</div>
      </div>
    </div>
  )
}

function KV({ title, data }) {
  const entries = Object.entries(data ?? {})
  if (!entries.length) return null
  return (
    <div className="mt-3">
      <h6 className="text-muted small text-uppercase mb-2">{title}</h6>
      <div className="d-flex flex-wrap gap-2">
        {entries.map(([k, v]) => (
          <span key={k} className="badge text-bg-light border text-capitalize">
            {labelOf(k)} : <strong>{v}</strong>
          </span>
        ))}
      </div>
    </div>
  )
}

function Card({ icon, title, loading, error, children }) {
  return (
    <div className="col-12">
      <div className="card h-100">
        <div className="card-body">
          <h5 className="card-title d-flex align-items-center gap-2">
            <i className={`bi ${icon} text-primary`}></i> {title}
          </h5>
          {loading ? <Spinner /> : error ? (
            <div className="alert alert-danger py-2 small mb-0">{error.message}</div>
          ) : children}
        </div>
      </div>
    </div>
  )
}

export default function Statistiques() {
  const users = useApi('/admin/users/stats')
  const consultations = useApi('/admin/consultations/stats')
  const commandes = useApi('/admin/commandes/stats')
  const investisseurs = useApi('/admin/investisseurs/stats')
  const medias = useApi('/admin/medias/stats')

  return (
    <>
      <PageTitle title="Statistiques" crumbs={[{ label: 'Statistiques' }]} />
      <section className="section dashboard">
        <div className="row g-3">
          <Card icon="bi-people" title="Utilisateurs" loading={users.loading} error={users.error}>
            {users.data && (
              <>
                <div className="row g-3">
                  <Stat label="Total" value={users.data.total} />
                  <Stat label="Actifs" value={users.data.actifs} tone="success" />
                  <Stat label="Inactifs" value={users.data.inactifs} tone="secondary" />
                  <Stat label="Nouveaux (30 j)" value={users.data.nouveaux_30_jours} tone="info" />
                  <Stat label="Email vérifiés" value={users.data.email_verifies} tone="success" />
                  <Stat label="KYC vérifiés" value={users.data.kyc_verifies} tone="success" />
                  <Stat label="Supprimés" value={users.data.supprimes} tone="danger" />
                </div>
                <KV title="Par type" data={users.data.par_type} />
                <KV title="Par rôle" data={users.data.par_role} />
                {(users.data.inscriptions_par_mois ?? []).length > 0 && (
                  <div className="mt-3">
                    <h6 className="text-muted small text-uppercase mb-2">Inscriptions par mois</h6>
                    <div className="d-flex flex-wrap gap-2">
                      {users.data.inscriptions_par_mois.map((m) => (
                        <span key={m.mois} className="badge text-bg-light border">{m.mois} : <strong>{m.total}</strong></span>
                      ))}
                    </div>
                  </div>
                )}
              </>
            )}
          </Card>

          <Card icon="bi-calendar-check" title="Consultations" loading={consultations.loading} error={consultations.error}>
            {consultations.data && (
              <>
                <div className="row g-3">
                  <Stat label="Total" value={consultations.data.total} />
                  <Stat label="À venir" value={consultations.data.a_venir} tone="info" />
                  <Stat label="Paiements à valider" value={consultations.data.a_valider} tone="warning" />
                </div>
                <KV title="Par statut" data={consultations.data.par_statut} />
                <KV title="Par paiement" data={consultations.data.par_paiement} />
                <KV title="Encaissé par devise" data={consultations.data.encaisse} />
              </>
            )}
          </Card>

          <Card icon="bi-cart" title="Commandes" loading={commandes.loading} error={commandes.error}>
            {commandes.data && (
              <>
                <div className="row g-3">
                  <Stat label="Total" value={commandes.data.total} />
                  <Stat label="Non contactées" value={commandes.data.non_contactees} tone="warning" />
                  <Stat label="Demandes de prix" value={commandes.data.demandes_de_prix} tone="info" />
                </div>
                <KV title="Par statut" data={commandes.data.par_statut} />
                <KV title="Livre par devise" data={commandes.data.livre} />
                {(commandes.data.produits ?? []).length > 0 && (
                  <div className="mt-3">
                    <h6 className="text-muted small text-uppercase mb-2">Top produits</h6>
                    <table className="table table-sm mb-0">
                      <thead><tr><th>Produit</th><th className="text-end">Commandes</th><th className="text-end">Quantité</th></tr></thead>
                      <tbody>
                        {commandes.data.produits.map((p) => (
                          <tr key={p.produit}><td>{p.produit}</td><td className="text-end">{p.commandes}</td><td className="text-end">{p.quantite}</td></tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
                {(commandes.data.pays ?? []).length > 0 && (
                  <KV title="Par pays" data={Object.fromEntries(commandes.data.pays.map((p) => [p.pays, p.commandes]))} />
                )}
              </>
            )}
          </Card>

          <Card icon="bi-graph-up-arrow" title="Investisseurs" loading={investisseurs.loading} error={investisseurs.error}>
            {investisseurs.data && (
              <>
                <div className="row g-3">
                  <Stat label="Total" value={investisseurs.data.total} />
                  <Stat label="Relances dues" value={investisseurs.data.relances_dues} tone="warning" />
                  <Stat label="Courtiers en attente" value={investisseurs.data.courtiers_en_attente} tone="info" />
                </div>
                <KV title="Par statut" data={investisseurs.data.par_statut} />
                <KV title="Par montant" data={investisseurs.data.par_montant} />
                <KV title="Par calendrier" data={investisseurs.data.par_calendrier} />
                <KV title="Par source" data={investisseurs.data.par_source} />
              </>
            )}
          </Card>

          <Card icon="bi-collection-play" title="Médias" loading={medias.loading} error={medias.error}>
            {medias.data && (
              <>
                <div className="row g-3">
                  <Stat label="Vues (30 j)" value={medias.data.vues_30_jours} tone="info" />
                  <Stat label="Heures d'écoute (30 j)" value={medias.data.heures_ecoute_30_jours} tone="primary" />
                  <Stat label="Téléchargements" value={medias.data.telechargements} tone="success" />
                  <Stat label="Commentaires à modérer" value={medias.data.commentaires_en_attente} tone="warning" />
                  <Stat label="Traitements en attente" value={medias.data.traitements_en_attente} tone="danger" />
                </div>
                <KV title="Contenus par catégorie" data={medias.data.medias} />
                {(medias.data.top_30_jours ?? []).length > 0 && (
                  <div className="mt-3">
                    <h6 className="text-muted small text-uppercase mb-2">Top 30 jours</h6>
                    <table className="table table-sm mb-0">
                      <thead><tr><th>Titre</th><th className="text-end">Vues</th></tr></thead>
                      <tbody>
                        {medias.data.top_30_jours.map((m) => (
                          <tr key={m.slug}><td>{m.titre}</td><td className="text-end">{m.vues}</td></tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </>
            )}
          </Card>
        </div>
      </section>
    </>
  )
}
