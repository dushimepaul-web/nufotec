<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Administration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Brokers</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_broker">
                <i class="bx bx-plus"></i> Nouveau Broker
            </a>
            <a class="btn btn-success ms-2" href="<?= base_url('Brokers/export_csv') ?>">
                <i class="bx bx-export"></i> Exporter CSV
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-error-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-transfer me-2"></i>Liste des Brokers</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="brokersTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Nom complet</th>
                            <th width="15%">Société</th>
                            <th width="10%">Statut</th>
                            <th width="15%">Contact</th>
                            <th width="10%">Pays</th>
                            <th width="10%">Capacités</th>
                            <th width="10%">Modèle</th>
                            <th width="10%">Date</th>
                            <th width="8%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($brokers)): $i = 1; foreach ($brokers as $value): 
                        // Récupérer le nom du pays
                        $pays_name = 'Non spécifié';
                        if (!empty($pays)) {
                            foreach ($pays as $p) {
                                if ($p['id'] == $value['id_pays']) {
                                    $pays_name = $p['pays'] ?? $p['name'] ?? 'Non spécifié';
                                    break;
                                }
                            }
                        }
                        
                        // Formater les capacités pour l'affichage
                        $capacities = [];
                        if (!empty($value['capacity_investment_broker']) && $value['capacity_investment_broker'] == 1) $capacities[] = 'Investment Broker';
                        if (!empty($value['capacity_placement_agent']) && $value['capacity_placement_agent'] == 1) $capacities[] = 'Placement Agent';
                        if (!empty($value['capacity_corporate_finance_advisor']) && $value['capacity_corporate_finance_advisor'] == 1) $capacities[] = 'Corporate Finance';
                        if (!empty($value['capacity_fund_manager']) && $value['capacity_fund_manager'] == 1) $capacities[] = 'Fund Manager';
                        if (!empty($value['capacity_family_office_rep']) && $value['capacity_family_office_rep'] == 1) $capacities[] = 'Family Office';
                        if (!empty($value['capacity_esg_advisor']) && $value['capacity_esg_advisor'] == 1) $capacities[] = 'ESG Advisor';
                        if (!empty($value['capacity_independent_introducer']) && $value['capacity_independent_introducer'] == 1) $capacities[] = 'Independent';
                        if (!empty($value['capacity_other'])) $capacities[] = 'Autre';
                        
                        $capacities_preview = !empty($capacities) ? implode(', ', array_slice($capacities, 0, 2)) . (count($capacities) > 2 ? '...' : '') : 'Non spécifié';
                        
                        // Badge pour statut régulatoire
                        $status_badges = [
                            'Licensed' => '<span class="badge bg-success">Licensed</span>',
                            'Exempt' => '<span class="badge bg-info">Exempt</span>',
                            'Unlicensed' => '<span class="badge bg-warning text-dark">Unlicensed</span>'
                        ];
                        
                        $status_badge = $status_badges[$value['regulatory_status'] ?? ''] ?? '<span class="badge bg-secondary">Non spécifié</span>';
                        
                        // Badge pour modèle d'engagement
                        $model_badges = [
                            'Success Commission' => '<span class="badge bg-primary">Success Commission</span>',
                            'Retainer + Success Fee' => '<span class="badge bg-info">Retainer + Success</span>',
                            'Referral Arrangement' => '<span class="badge bg-success">Referral</span>',
                            'To be negotiated' => '<span class="badge bg-warning text-dark">À négocier</span>'
                        ];
                        
                        $model_badge = $model_badges[$value['engagement_model'] ?? ''] ?? '<span class="badge bg-secondary">Non spécifié</span>';
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['full_name'] ?? '-') ?></strong>
                                    <?php if (!empty($value['registration_number'])): ?>
                                        <small class="text-muted">Reg: <?= htmlspecialchars($value['registration_number']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium"><?= htmlspecialchars($value['firm_name'] ?? '-') ?></span>
                                    <?php if (!empty($value['jurisdiction_of_incorporation'])): ?>
                                        <small class="text-muted">Juridiction: <?= htmlspecialchars($value['jurisdiction_of_incorporation']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="text-center">
                                <?= $status_badge ?>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span><i class="bx bx-envelope text-muted me-1"></i><?= htmlspecialchars($value['email'] ?? '-') ?></span>
                                    <?php if (!empty($value['mobile_phone'])): ?>
                                        <small><i class="bx bx-phone text-muted me-1"></i><?= htmlspecialchars($value['mobile_phone']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($value['whatsapp'])): ?>
                                        <small><i class="bx bxl-whatsapp text-success me-1"></i><?= htmlspecialchars($value['whatsapp']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($pays_name) ?></span>
                            </td>

                            <td>
                                <span class="badge bg-info" title="<?= htmlspecialchars(implode(', ', $capacities)) ?>"><?= htmlspecialchars($capacities_preview) ?></span>
                            </td>

                            <td class="text-center">
                                <?= $model_badge ?>
                            </td>

                            <td><?= !empty($value['created_at']) ? date('d/m/Y', strtotime($value['created_at'])) : '-' ?></td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-user-circle me-2"></i>Détails du Broker</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 border-end">
                                                <div class="text-center mb-3">
                                                    <div class="bg-light rounded-circle d-inline-flex p-3 mb-3">
                                                        <i class="bx bx-user-circle text-primary" style="font-size: 4rem;"></i>
                                                    </div>
                                                    <h4 class="mb-1"><?= htmlspecialchars($value['full_name'] ?? '') ?></h4>
                                                    <p class="fw-bold mb-1"><?= htmlspecialchars($value['firm_name'] ?? '') ?></p>
                                                    <?= $status_badge ?>
                                                </div>
                                                
                                                <div class="list-group list-group-flush">
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <span class="text-muted"><i class="bx bx-envelope me-2"></i>Email</span>
                                                        <span class="fw-bold"><?= htmlspecialchars($value['email'] ?? '-') ?></span>
                                                    </div>
                                                    <?php if (!empty($value['mobile_phone'])): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <span class="text-muted"><i class="bx bx-phone me-2"></i>Téléphone</span>
                                                        <span class="fw-bold"><?= htmlspecialchars($value['mobile_phone']) ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($value['whatsapp'])): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <span class="text-muted"><i class="bx bxl-whatsapp text-success me-2"></i>WhatsApp</span>
                                                        <span class="fw-bold"><?= htmlspecialchars($value['whatsapp']) ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($value['corporate_website'])): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <span class="text-muted"><i class="bx bx-globe me-2"></i>Site web</span>
                                                        <a href="<?= htmlspecialchars($value['corporate_website']) ?>" target="_blank" class="fw-bold">Visiter</a>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <span class="text-muted"><i class="bx bx-map me-2"></i>Pays</span>
                                                        <span class="fw-bold"><?= htmlspecialchars($pays_name) ?></span>
                                                    </div>
                                                    <?php if (!empty($value['jurisdiction_of_incorporation'])): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <span class="text-muted"><i class="bx bx-gavel me-2"></i>Juridiction</span>
                                                        <span class="fw-bold"><?= htmlspecialchars($value['jurisdiction_of_incorporation']) ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <span class="text-muted"><i class="bx bx-calendar me-2"></i>Inscrit le</span>
                                                        <span class="fw-bold"><?= !empty($value['created_at']) ? date('d/m/Y H:i', strtotime($value['created_at'])) : '-' ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="row g-4">
                                                    <!-- Informations légales -->
                                                    <div class="col-12">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-gavel me-2"></i>Informations légales</h6>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <p class="mb-1"><strong>Numéro d'enregistrement:</strong></p>
                                                                        <p><?= !empty($value['registration_number']) ? htmlspecialchars($value['registration_number']) : 'Non spécifié' ?></p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <p class="mb-1"><strong>Autorité de régulation:</strong></p>
                                                                        <p><?= !empty($value['regulatory_authority']) ? htmlspecialchars($value['regulatory_authority']) : 'Non spécifié' ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Capacités -->
                                                    <div class="col-12">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-briefcase me-2"></i>Capacités professionnelles</h6>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <?php if (!empty($value['capacity_investment_broker']) && $value['capacity_investment_broker'] == 1): ?>
                                                                        <span class="badge bg-success p-2"><i class="bx bx-check-circle me-1"></i>Investment Broker</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['capacity_placement_agent']) && $value['capacity_placement_agent'] == 1): ?>
                                                                        <span class="badge bg-warning text-dark p-2"><i class="bx bx-check-circle me-1"></i>Placement Agent</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['capacity_corporate_finance_advisor']) && $value['capacity_corporate_finance_advisor'] == 1): ?>
                                                                        <span class="badge bg-info p-2"><i class="bx bx-check-circle me-1"></i>Corporate Finance Advisor</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['capacity_fund_manager']) && $value['capacity_fund_manager'] == 1): ?>
                                                                        <span class="badge bg-primary p-2"><i class="bx bx-check-circle me-1"></i>Fund Manager</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['capacity_family_office_rep']) && $value['capacity_family_office_rep'] == 1): ?>
                                                                        <span class="badge bg-secondary p-2"><i class="bx bx-check-circle me-1"></i>Family Office Rep</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['capacity_esg_advisor']) && $value['capacity_esg_advisor'] == 1): ?>
                                                                        <span class="badge bg-success p-2"><i class="bx bx-check-circle me-1"></i>ESG Advisor</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['capacity_independent_introducer']) && $value['capacity_independent_introducer'] == 1): ?>
                                                                        <span class="badge bg-dark p-2"><i class="bx bx-check-circle me-1"></i>Independent Introducer</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['capacity_other'])): ?>
                                                                        <span class="badge bg-light text-dark p-2"><i class="bx bx-check-circle me-1"></i><?= htmlspecialchars($value['capacity_other']) ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Types d'investisseurs -->
                                                    <div class="col-12">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-group me-2"></i>Types d'investisseurs représentés</h6>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <?php if (!empty($value['investor_private_equity']) && $value['investor_private_equity'] == 1): ?>
                                                                        <span class="badge bg-danger p-2">Private Equity</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['investor_venture_capital']) && $value['investor_venture_capital'] == 1): ?>
                                                                        <span class="badge bg-warning text-dark p-2">Venture Capital</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['investor_esg_impact']) && $value['investor_esg_impact'] == 1): ?>
                                                                        <span class="badge bg-success p-2">ESG/Impact</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['investor_dfi']) && $value['investor_dfi'] == 1): ?>
                                                                        <span class="badge bg-info p-2">DFIs</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['investor_institutional']) && $value['investor_institutional'] == 1): ?>
                                                                        <span class="badge bg-primary p-2">Institutionnels</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['investor_hnwi']) && $value['investor_hnwi'] == 1): ?>
                                                                        <span class="badge bg-secondary p-2">HNWI</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['investor_sovereign']) && $value['investor_sovereign'] == 1): ?>
                                                                        <span class="badge bg-dark p-2">Souverains</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                
                                                                <?php if (!empty($value['typical_ticket_size']) || !empty($value['geographic_coverage'])): ?>
                                                                <hr>
                                                                <div class="row mt-3">
                                                                    <?php if (!empty($value['typical_ticket_size'])): ?>
                                                                    <div class="col-md-6">
                                                                        <p class="mb-1"><strong>Ticket typique:</strong></p>
                                                                        <p><?= htmlspecialchars($value['typical_ticket_size']) ?></p>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['geographic_coverage'])): ?>
                                                                    <div class="col-md-6">
                                                                        <p class="mb-1"><strong>Couverture géographique:</strong></p>
                                                                        <p><?= htmlspecialchars($value['geographic_coverage']) ?></p>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Mandats -->
                                                    <div class="col-md-6">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-file me-2"></i>Périmètre du mandat</h6>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <?php if (!empty($value['mandate_equity']) && $value['mandate_equity'] == 1): ?>
                                                                        <span class="badge bg-success p-2">Equity</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['mandate_structured_debt']) && $value['mandate_structured_debt'] == 1): ?>
                                                                        <span class="badge bg-warning text-dark p-2">Structured Debt</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['mandate_blended_finance']) && $value['mandate_blended_finance'] == 1): ?>
                                                                        <span class="badge bg-info p-2">Blended Finance</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['mandate_grant']) && $value['mandate_grant'] == 1): ?>
                                                                        <span class="badge bg-primary p-2">Grant</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['mandate_strategic_partnership']) && $value['mandate_strategic_partnership'] == 1): ?>
                                                                        <span class="badge bg-secondary p-2">Strategic Partnership</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['mandate_full_program']) && $value['mandate_full_program'] == 1): ?>
                                                                        <span class="badge bg-dark p-2">Full Program</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Modèle d'engagement -->
                                                    <div class="col-md-6">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-handshake me-2"></i>Modèle d'engagement</h6>
                                                                <p class="h5 mb-0"><?= $model_badge ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Conformité -->
                                                    <div class="col-12">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-shield me-2"></i>Conformité</h6>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="d-flex align-items-center mb-2">
                                                                            <i class="bx <?= (!empty($value['confirm_authorized']) && $value['confirm_authorized'] == 1) ? 'bx-check-circle text-success' : 'bx-x-circle text-danger' ?> fs-5 me-2"></i>
                                                                            <span>Autorisé à représenter</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="d-flex align-items-center mb-2">
                                                                            <i class="bx <?= (!empty($value['confirm_aml_kyc']) && $value['confirm_aml_kyc'] == 1) ? 'bx-check-circle text-success' : 'bx-x-circle text-danger' ?> fs-5 me-2"></i>
                                                                            <span>Conformité AML/KYC</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="bx <?= (!empty($value['acknowledge_no_exclusivity']) && $value['acknowledge_no_exclusivity'] == 1) ? 'bx-check-circle text-success' : 'bx-x-circle text-danger' ?> fs-5 me-2"></i>
                                                                            <span>Non exclusivité reconnue</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="bx <?= (!empty($value['understand_formal_mandate_required']) && $value['understand_formal_mandate_required'] == 1) ? 'bx-check-circle text-success' : 'bx-x-circle text-danger' ?> fs-5 me-2"></i>
                                                                            <span>Mandat formel requis</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le Broker</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Brokers/Update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <!-- Section Identification -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-user me-2"></i>Identification</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($value['full_name'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Société <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="firm_name" value="<?= htmlspecialchars($value['firm_name'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Juridiction d'incorporation</label>
                                                            <input type="text" class="form-control" name="jurisdiction_of_incorporation" value="<?= htmlspecialchars($value['jurisdiction_of_incorporation'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Numéro d'enregistrement</label>
                                                            <input type="text" class="form-control" name="registration_number" value="<?= htmlspecialchars($value['registration_number'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($value['email'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Téléphone mobile</label>
                                                            <input type="tel" class="form-control" name="mobile_phone" value="<?= htmlspecialchars($value['mobile_phone'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">WhatsApp</label>
                                                            <input type="tel" class="form-control" name="whatsapp" value="<?= htmlspecialchars($value['whatsapp'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Site web</label>
                                                            <input type="url" class="form-control" name="corporate_website" value="<?= htmlspecialchars($value['corporate_website'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Pays <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="id_pays" required>
                                                                <option value="">Sélectionner...</option>
                                                                <?php foreach ($pays as $p): ?>
                                                                    <option value="<?= $p['id'] ?>" <?= ($value['id_pays'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($p['pays'] ?? $p['name'] ?? 'Pays') ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Régulation -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-shield me-2"></i>Régulation</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Statut régulatoire</label>
                                                            <select class="form-select" name="regulatory_status">
                                                                <option value="">Sélectionner...</option>
                                                                <option value="Licensed" <?= ($value['regulatory_status'] ?? '') == 'Licensed' ? 'selected' : '' ?>>Licensed</option>
                                                                <option value="Exempt" <?= ($value['regulatory_status'] ?? '') == 'Exempt' ? 'selected' : '' ?>>Exempt</option>
                                                                <option value="Unlicensed" <?= ($value['regulatory_status'] ?? '') == 'Unlicensed' ? 'selected' : '' ?>>Unlicensed</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Autorité de régulation</label>
                                                            <input type="text" class="form-control" name="regulatory_authority" value="<?= htmlspecialchars($value['regulatory_authority'] ?? '') ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Capacités -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-briefcase me-2"></i>Capacités professionnelles</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="capacity_investment_broker" id="capacity_investment_broker_<?= $value['id'] ?>" value="1" <?= (!empty($value['capacity_investment_broker']) && $value['capacity_investment_broker'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="capacity_investment_broker_<?= $value['id'] ?>">Investment Broker</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="capacity_placement_agent" id="capacity_placement_agent_<?= $value['id'] ?>" value="1" <?= (!empty($value['capacity_placement_agent']) && $value['capacity_placement_agent'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="capacity_placement_agent_<?= $value['id'] ?>">Placement Agent</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="capacity_corporate_finance_advisor" id="capacity_corporate_finance_advisor_<?= $value['id'] ?>" value="1" <?= (!empty($value['capacity_corporate_finance_advisor']) && $value['capacity_corporate_finance_advisor'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="capacity_corporate_finance_advisor_<?= $value['id'] ?>">Corporate Finance Advisor</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="capacity_fund_manager" id="capacity_fund_manager_<?= $value['id'] ?>" value="1" <?= (!empty($value['capacity_fund_manager']) && $value['capacity_fund_manager'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="capacity_fund_manager_<?= $value['id'] ?>">Fund Manager</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="capacity_family_office_rep" id="capacity_family_office_rep_<?= $value['id'] ?>" value="1" <?= (!empty($value['capacity_family_office_rep']) && $value['capacity_family_office_rep'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="capacity_family_office_rep_<?= $value['id'] ?>">Family Office Rep</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="capacity_esg_advisor" id="capacity_esg_advisor_<?= $value['id'] ?>" value="1" <?= (!empty($value['capacity_esg_advisor']) && $value['capacity_esg_advisor'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="capacity_esg_advisor_<?= $value['id'] ?>">ESG Advisor</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="capacity_independent_introducer" id="capacity_independent_introducer_<?= $value['id'] ?>" value="1" <?= (!empty($value['capacity_independent_introducer']) && $value['capacity_independent_introducer'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="capacity_independent_introducer_<?= $value['id'] ?>">Independent Introducer</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Autre capacité</label>
                                                            <input type="text" class="form-control" name="capacity_other" value="<?= htmlspecialchars($value['capacity_other'] ?? '') ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Investisseurs -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-group me-2"></i>Types d'investisseurs représentés</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="investor_private_equity" id="investor_private_equity_<?= $value['id'] ?>" value="1" <?= (!empty($value['investor_private_equity']) && $value['investor_private_equity'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="investor_private_equity_<?= $value['id'] ?>">Private Equity</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="investor_venture_capital" id="investor_venture_capital_<?= $value['id'] ?>" value="1" <?= (!empty($value['investor_venture_capital']) && $value['investor_venture_capital'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="investor_venture_capital_<?= $value['id'] ?>">Venture Capital</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="investor_esg_impact" id="investor_esg_impact_<?= $value['id'] ?>" value="1" <?= (!empty($value['investor_esg_impact']) && $value['investor_esg_impact'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="investor_esg_impact_<?= $value['id'] ?>">ESG/Impact</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="investor_dfi" id="investor_dfi_<?= $value['id'] ?>" value="1" <?= (!empty($value['investor_dfi']) && $value['investor_dfi'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="investor_dfi_<?= $value['id'] ?>">DFIs</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="investor_institutional" id="investor_institutional_<?= $value['id'] ?>" value="1" <?= (!empty($value['investor_institutional']) && $value['investor_institutional'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="investor_institutional_<?= $value['id'] ?>">Institutionnels</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="investor_hnwi" id="investor_hnwi_<?= $value['id'] ?>" value="1" <?= (!empty($value['investor_hnwi']) && $value['investor_hnwi'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="investor_hnwi_<?= $value['id'] ?>">HNWI</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="investor_sovereign" id="investor_sovereign_<?= $value['id'] ?>" value="1" <?= (!empty($value['investor_sovereign']) && $value['investor_sovereign'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="investor_sovereign_<?= $value['id'] ?>">Souverains</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Ticket typique</label>
                                                            <input type="text" class="form-control" name="typical_ticket_size" value="<?= htmlspecialchars($value['typical_ticket_size'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Couverture géographique</label>
                                                            <input type="text" class="form-control" name="geographic_coverage" value="<?= htmlspecialchars($value['geographic_coverage'] ?? '') ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Mandats -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-file me-2"></i>Périmètre du mandat</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="mandate_equity" id="mandate_equity_<?= $value['id'] ?>" value="1" <?= (!empty($value['mandate_equity']) && $value['mandate_equity'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="mandate_equity_<?= $value['id'] ?>">Equity</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="mandate_structured_debt" id="mandate_structured_debt_<?= $value['id'] ?>" value="1" <?= (!empty($value['mandate_structured_debt']) && $value['mandate_structured_debt'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="mandate_structured_debt_<?= $value['id'] ?>">Structured Debt</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="mandate_blended_finance" id="mandate_blended_finance_<?= $value['id'] ?>" value="1" <?= (!empty($value['mandate_blended_finance']) && $value['mandate_blended_finance'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="mandate_blended_finance_<?= $value['id'] ?>">Blended Finance</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="mandate_grant" id="mandate_grant_<?= $value['id'] ?>" value="1" <?= (!empty($value['mandate_grant']) && $value['mandate_grant'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="mandate_grant_<?= $value['id'] ?>">Grant</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="mandate_strategic_partnership" id="mandate_strategic_partnership_<?= $value['id'] ?>" value="1" <?= (!empty($value['mandate_strategic_partnership']) && $value['mandate_strategic_partnership'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="mandate_strategic_partnership_<?= $value['id'] ?>">Strategic Partnership</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="mandate_full_program" id="mandate_full_program_<?= $value['id'] ?>" value="1" <?= (!empty($value['mandate_full_program']) && $value['mandate_full_program'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="mandate_full_program_<?= $value['id'] ?>">Full Program</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Modèle d'engagement -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-handshake me-2"></i>Modèle d'engagement</h6>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <select class="form-select" name="engagement_model">
                                                                <option value="">Sélectionner...</option>
                                                                <option value="Success Commission" <?= ($value['engagement_model'] ?? '') == 'Success Commission' ? 'selected' : '' ?>>Success Commission</option>
                                                                <option value="Retainer + Success Fee" <?= ($value['engagement_model'] ?? '') == 'Retainer + Success Fee' ? 'selected' : '' ?>>Retainer + Success Fee</option>
                                                                <option value="Referral Arrangement" <?= ($value['engagement_model'] ?? '') == 'Referral Arrangement' ? 'selected' : '' ?>>Referral Arrangement</option>
                                                                <option value="To be negotiated" <?= ($value['engagement_model'] ?? '') == 'To be negotiated' ? 'selected' : '' ?>>To be negotiated</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Conformité -->
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-shield me-2"></i>Conformité</h6>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch mb-2">
                                                                <input type="checkbox" class="form-check-input" name="confirm_authorized" id="confirm_authorized_<?= $value['id'] ?>" value="1" <?= (!empty($value['confirm_authorized']) && $value['confirm_authorized'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="confirm_authorized_<?= $value['id'] ?>">Autorisé à représenter</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch mb-2">
                                                                <input type="checkbox" class="form-check-input" name="confirm_aml_kyc" id="confirm_aml_kyc_<?= $value['id'] ?>" value="1" <?= (!empty($value['confirm_aml_kyc']) && $value['confirm_aml_kyc'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="confirm_aml_kyc_<?= $value['id'] ?>">Conformité AML/KYC</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch mb-2">
                                                                <input type="checkbox" class="form-check-input" name="acknowledge_no_exclusivity" id="acknowledge_no_exclusivity_<?= $value['id'] ?>" value="1" <?= (!empty($value['acknowledge_no_exclusivity']) && $value['acknowledge_no_exclusivity'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="acknowledge_no_exclusivity_<?= $value['id'] ?>">Reconnaît non exclusivité</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch mb-2">
                                                                <input type="checkbox" class="form-check-input" name="understand_formal_mandate_required" id="understand_formal_mandate_required_<?= $value['id'] ?>" value="1" <?= (!empty($value['understand_formal_mandate_required']) && $value['understand_formal_mandate_required'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="understand_formal_mandate_required_<?= $value['id'] ?>">Mandat formel requis</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bx bx-save me-2"></i>Enregistrer les modifications
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer <strong><?= htmlspecialchars($value['full_name'] ?? '') ?></strong> de la société <strong><?= htmlspecialchars($value['firm_name'] ?? '') ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible (suppression définitive).</p>
                                    </div>
                                    <form action="<?= base_url('Brokers/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <div class="modal-footer bg-light justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bx bx-trash me-2"></i>Supprimer définitivement
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bx bx-user-x text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun broker trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<!-- MODAL CREATE BROKER -->
<div class="modal fade" id="create_broker" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-user-plus me-2"></i>Nouveau Broker</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Brokers/Create') ?>" method="POST">
                <div class="modal-body p-4">
                    <!-- Section Identification -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-user me-2"></i>Identification</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="full_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Société <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="firm_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Juridiction d'incorporation</label>
                                    <input type="text" class="form-control" name="jurisdiction_of_incorporation">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Numéro d'enregistrement</label>
                                    <input type="text" class="form-control" name="registration_number">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Téléphone mobile</label>
                                    <input type="tel" class="form-control" name="mobile_phone">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">WhatsApp</label>
                                    <input type="tel" class="form-control" name="whatsapp">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Site web</label>
                                    <input type="url" class="form-control" name="corporate_website">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Pays <span class="text-danger">*</span></label>
                                    <select class="form-select" name="id_pays" required>
                                        <option value="">Sélectionner...</option>
                                        <?php foreach ($pays as $p): ?>
                                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['pays'] ?? $p['name'] ?? 'Pays') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Régulation -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-shield me-2"></i>Régulation</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Statut régulatoire</label>
                                    <select class="form-select" name="regulatory_status">
                                        <option value="">Sélectionner...</option>
                                        <option value="Licensed">Licensed</option>
                                        <option value="Exempt">Exempt</option>
                                        <option value="Unlicensed">Unlicensed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Autorité de régulation</label>
                                    <input type="text" class="form-control" name="regulatory_authority">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Capacités -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-briefcase me-2"></i>Capacités professionnelles</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="capacity_investment_broker" id="create_capacity_investment_broker" value="1">
                                        <label class="form-check-label" for="create_capacity_investment_broker">Investment Broker</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="capacity_placement_agent" id="create_capacity_placement_agent" value="1">
                                        <label class="form-check-label" for="create_capacity_placement_agent">Placement Agent</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="capacity_corporate_finance_advisor" id="create_capacity_corporate_finance_advisor" value="1">
                                        <label class="form-check-label" for="create_capacity_corporate_finance_advisor">Corporate Finance Advisor</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="capacity_fund_manager" id="create_capacity_fund_manager" value="1">
                                        <label class="form-check-label" for="create_capacity_fund_manager">Fund Manager</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="capacity_family_office_rep" id="create_capacity_family_office_rep" value="1">
                                        <label class="form-check-label" for="create_capacity_family_office_rep">Family Office Rep</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="capacity_esg_advisor" id="create_capacity_esg_advisor" value="1">
                                        <label class="form-check-label" for="create_capacity_esg_advisor">ESG Advisor</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="capacity_independent_introducer" id="create_capacity_independent_introducer" value="1">
                                        <label class="form-check-label" for="create_capacity_independent_introducer">Independent Introducer</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Autre capacité</label>
                                    <input type="text" class="form-control" name="capacity_other">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Investisseurs -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-group me-2"></i>Types d'investisseurs représentés</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="investor_private_equity" id="create_investor_private_equity" value="1">
                                        <label class="form-check-label" for="create_investor_private_equity">Private Equity</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="investor_venture_capital" id="create_investor_venture_capital" value="1">
                                        <label class="form-check-label" for="create_investor_venture_capital">Venture Capital</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="investor_esg_impact" id="create_investor_esg_impact" value="1">
                                        <label class="form-check-label" for="create_investor_esg_impact">ESG/Impact</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="investor_dfi" id="create_investor_dfi" value="1">
                                        <label class="form-check-label" for="create_investor_dfi">DFIs</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="investor_institutional" id="create_investor_institutional" value="1">
                                        <label class="form-check-label" for="create_investor_institutional">Institutionnels</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="investor_hnwi" id="create_investor_hnwi" value="1">
                                        <label class="form-check-label" for="create_investor_hnwi">HNWI</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="investor_sovereign" id="create_investor_sovereign" value="1">
                                        <label class="form-check-label" for="create_investor_sovereign">Souverains</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Ticket typique</label>
                                    <input type="text" class="form-control" name="typical_ticket_size" placeholder="Ex: $1M - $5M">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Couverture géographique</label>
                                    <input type="text" class="form-control" name="geographic_coverage" placeholder="Ex: Afrique de l'Ouest">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Mandats -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-file me-2"></i>Périmètre du mandat</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="mandate_equity" id="create_mandate_equity" value="1">
                                        <label class="form-check-label" for="create_mandate_equity">Equity</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="mandate_structured_debt" id="create_mandate_structured_debt" value="1">
                                        <label class="form-check-label" for="create_mandate_structured_debt">Structured Debt</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="mandate_blended_finance" id="create_mandate_blended_finance" value="1">
                                        <label class="form-check-label" for="create_mandate_blended_finance">Blended Finance</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="mandate_grant" id="create_mandate_grant" value="1">
                                        <label class="form-check-label" for="create_mandate_grant">Grant</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="mandate_strategic_partnership" id="create_mandate_strategic_partnership" value="1">
                                        <label class="form-check-label" for="create_mandate_strategic_partnership">Strategic Partnership</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="mandate_full_program" id="create_mandate_full_program" value="1">
                                        <label class="form-check-label" for="create_mandate_full_program">Full Program</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Modèle d'engagement -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-handshake me-2"></i>Modèle d'engagement</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-select" name="engagement_model">
                                        <option value="">Sélectionner...</option>
                                        <option value="Success Commission">Success Commission</option>
                                        <option value="Retainer + Success Fee">Retainer + Success Fee</option>
                                        <option value="Referral Arrangement">Referral Arrangement</option>
                                        <option value="To be negotiated">To be negotiated</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Conformité -->
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-shield me-2"></i>Conformité</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" class="form-check-input" name="confirm_authorized" id="create_confirm_authorized" value="1" checked>
                                        <label class="form-check-label" for="create_confirm_authorized">Autorisé à représenter</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" class="form-check-input" name="confirm_aml_kyc" id="create_confirm_aml_kyc" value="1" checked>
                                        <label class="form-check-label" for="create_confirm_aml_kyc">Conformité AML/KYC</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" class="form-check-input" name="acknowledge_no_exclusivity" id="create_acknowledge_no_exclusivity" value="1" checked>
                                        <label class="form-check-label" for="create_acknowledge_no_exclusivity">Reconnaît non exclusivité</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" class="form-check-input" name="understand_formal_mandate_required" id="create_understand_formal_mandate_required" value="1" checked>
                                        <label class="form-check-label" for="create_understand_formal_mandate_required">Mandat formel requis</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer le broker
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#brokersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [9] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>