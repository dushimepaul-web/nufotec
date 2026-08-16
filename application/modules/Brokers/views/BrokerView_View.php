<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
$broker = $broker ?? [];
$pays_nom = '-';
if (!empty($broker['id_pays'])) {
    if (!empty($pays) && isset($pays['pays'])) {
        $pays_nom = $pays['pays'];
    } elseif (!empty($pays) && is_array($pays)) {
        foreach ($pays as $p) {
            if (isset($p['id']) && $p['id'] == $broker['id_pays']) { $pays_nom = $p['pays'] ?? $p['name'] ?? '-'; break; }
        }
    }
}
$capacites = [
    'capacity_investment_broker' => 'Courtier en investissement',
    'capacity_placement_agent' => 'Agent de placement',
    'capacity_corporate_finance_advisor' => 'Conseiller en finance d\'entreprise',
    'capacity_fund_manager' => 'Gestionnaire de fonds',
    'capacity_family_office_rep' => 'Représentant family office',
    'capacity_esg_advisor' => 'Conseiller ESG',
    'capacity_independent_introducer' => 'Introducteur indépendant',
];
$investisseurs = [
    'investor_private_equity' => 'Capital investissement',
    'investor_venture_capital' => 'Capital risque',
    'investor_esg_impact' => 'ESG / Impact',
    'investor_dfi' => 'Financement de développement',
    'investor_institutional' => 'Institutionnel',
    'investor_hnwi' => 'Grande fortune',
    'investor_sovereign' => 'Souverain',
];
$mandats = [
    'mandate_equity' => 'Capitaux propres',
    'mandate_structured_debt' => 'Dette structurée',
    'mandate_blended_finance' => 'Financement mixte',
    'mandate_grant' => 'Subvention',
    'mandate_strategic_partnership' => 'Partenariat stratégique',
    'mandate_full_program' => 'Programme complet',
];
?>

<div class="page-wrapper">
<div class="page-content">

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Admin</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Brokers') ?>">Courtiers</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Vue détaillée</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-outline-secondary" href="<?= base_url('Brokers') ?>">
                <i class="bx bx-arrow-back"></i> Retour
            </a>
        </div>
    </div>

    <hr/>

    <?php if (empty($broker)): ?>
        <div class="alert alert-warning" role="alert">
            <strong>Introuvable!</strong> Ce courtier n'existe pas ou a été supprimé.
        </div>
    <?php else: ?>
    <div class="card">
        <div class="card-header py-3">
            <h5 class="mb-0 text-primary"><i class="bx bx-briefcase me-2"></i>Courtier</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Nom complet:</strong></div>
                <div class="col-md-8"><?= $broker['full_name'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Société:</strong></div>
                <div class="col-md-8"><?= $broker['firm_name'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Juridiction d'incorporation:</strong></div>
                <div class="col-md-8"><?= $broker['jurisdiction_of_incorporation'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Numéro d'immatriculation:</strong></div>
                <div class="col-md-8"><?= $broker['registration_number'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Statut réglementaire:</strong></div>
                <div class="col-md-8"><?= $broker['regulatory_status'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Autorité de régulation:</strong></div>
                <div class="col-md-8"><?= $broker['regulatory_authority'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Pays:</strong></div>
                <div class="col-md-8"><?= $pays_nom ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Email:</strong></div>
                <div class="col-md-8"><?= $broker['email'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Téléphone mobile:</strong></div>
                <div class="col-md-8"><?= $broker['mobile_phone'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>WhatsApp:</strong></div>
                <div class="col-md-8"><?= $broker['whatsapp'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Site web:</strong></div>
                <div class="col-md-8"><?= !empty($broker['corporate_website']) ? '<a href="' . htmlspecialchars($broker['corporate_website']) . '" target="_blank">' . $broker['corporate_website'] . '</a>' : '-' ?></div>
            </div>

            <hr>
            <h6 class="text-primary mb-3"><i class="bx bx-badge-check me-1"></i>Capacités déclarées</h6>
            <div class="row mb-3">
                <div class="col-md-12">
                    <?php $c_found = false; foreach ($capacites as $cle => $label): ?>
                        <?php if (!empty($broker[$cle])): $c_found = true; ?>
                            <span class="badge bg-info text-dark me-1 mb-1"><?= $label ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!$c_found && empty($broker['capacity_other'])): ?>
                        <span class="text-muted">Aucune capacité sélectionnée</span>
                    <?php endif; ?>
                    <?php if (!empty($broker['capacity_other'])): ?>
                        <span class="badge bg-secondary me-1 mb-1">Autre: <?= $broker['capacity_other'] ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <hr>
            <h6 class="text-primary mb-3"><i class="bx bx-pie-chart-alt me-1"></i>Type d'investisseur</h6>
            <div class="row mb-3">
                <div class="col-md-12">
                    <?php $i_found = false; foreach ($investisseurs as $cle => $label): ?>
                        <?php if (!empty($broker[$cle])): $i_found = true; ?>
                            <span class="badge bg-success me-1 mb-1"><?= $label ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!$i_found): ?>
                        <span class="text-muted">Aucun type d'investisseur sélectionné</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4"><strong>Taille de billet typique:</strong></div>
                <div class="col-md-8"><?= $broker['typical_ticket_size'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Couverture géographique:</strong></div>
                <div class="col-md-8"><?= $broker['geographic_coverage'] ?? '-' ?></div>
            </div>

            <hr>
            <h6 class="text-primary mb-3"><i class="bx bx-target-lock me-1"></i>Mandats recherchés</h6>
            <div class="row mb-3">
                <div class="col-md-12">
                    <?php $m_found = false; foreach ($mandats as $cle => $label): ?>
                        <?php if (!empty($broker[$cle])): $m_found = true; ?>
                            <span class="badge bg-warning text-dark me-1 mb-1"><?= $label ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!$m_found): ?>
                        <span class="text-muted">Aucun mandat sélectionné</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4"><strong>Modèle d'engagement:</strong></div>
                <div class="col-md-8"><?= $broker['engagement_model'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Inscrit le:</strong></div>
                <div class="col-md-8"><?= !empty($broker['created_at']) ? date('d/m/Y H:i:s', strtotime($broker['created_at'])) : '-' ?></div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="<?= base_url('Brokers') ?>" class="btn btn-secondary">Fermer</a>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>