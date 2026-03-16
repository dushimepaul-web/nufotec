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
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Investisseurs</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_investor">
                <i class="bx bx-plus"></i> Nouvel Investisseur
            </a>
            <a class="btn btn-success ms-2" href="<?= base_url('Investors/export_csv') ?>">
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
                <h5 class="mb-0 text-primary"><i class="bx bx-chart me-2"></i>Liste des Investisseurs</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="investorsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Nom complet</th>
                            <th width="15%">Organisation</th>
                            <th width="10%">Poste</th>
                            <th width="15%">Contact</th>
                            <th width="12%">Pays</th>
                            <th width="10%">Fourchette</th>
                            <th width="8%">Timeline</th>
                            <th width="10%">Date d'inscription</th>
                            <th width="8%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($investors)): $i = 1; foreach ($investors as $value): 
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
                        
                        // Formater les intérêts pour l'affichage
                        $interests = [];
                        if (!empty($value['interest_equity']) && $value['interest_equity'] == 1) $interests[] = 'Equity';
                        if (!empty($value['interest_debt']) && $value['interest_debt'] == 1) $interests[] = 'Debt';
                        if (!empty($value['interest_blended_finance']) && $value['interest_blended_finance'] == 1) $interests[] = 'Blended';
                        if (!empty($value['interest_grant']) && $value['interest_grant'] == 1) $interests[] = 'Grant';
                        if (!empty($value['interest_strategic_partnership']) && $value['interest_strategic_partnership'] == 1) $interests[] = 'Strategic';
                        if (!empty($value['interest_technical_collaboration']) && $value['interest_technical_collaboration'] == 1) $interests[] = 'Technical';
                        if (!empty($value['interest_offtake_distribution']) && $value['interest_offtake_distribution'] == 1) $interests[] = 'Offtake';
                        if (!empty($value['interest_other'])) $interests[] = 'Autre';
                        
                        $interests_str = !empty($interests) ? implode(', ', $interests) : 'Non spécifié';
                        
                        // Badges pour fourchette
                        $range_badges = [
                            'Below 250K' => '<span class="badge bg-light text-dark">Below 250K</span>',
                            '250K-1M' => '<span class="badge bg-info">250K - 1M</span>',
                            '1M-5M' => '<span class="badge bg-primary">1M - 5M</span>',
                            '5M+' => '<span class="badge bg-success">5M+</span>',
                            'To be discussed' => '<span class="badge bg-warning text-dark">À discuter</span>'
                        ];
                        
                        $range_badge = $range_badges[$value['commitment_range'] ?? ''] ?? '<span class="badge bg-secondary">Non spécifié</span>';
                        
                        // Badges pour timeline
                        $timeline_badges = [
                            'Immediate' => '<span class="badge bg-success">Immédiat</span>',
                            '3-6 months' => '<span class="badge bg-info">3-6 mois</span>',
                            '6-12 months' => '<span class="badge bg-warning text-dark">6-12 mois</span>',
                            'Exploratory' => '<span class="badge bg-secondary">Exploratoire</span>'
                        ];
                        
                        $timeline_badge = $timeline_badges[$value['timeline'] ?? 'Exploratory'] ?? '<span class="badge bg-secondary">Exploratoire</span>';
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['full_name'] ?? '-') ?></strong>
                                    <?php if (!empty($interests_str) && $interests_str != 'Non spécifié'): ?>
                                        <small class="text-muted" title="Intérêts"><?= htmlspecialchars($interests_str) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <?php if (!empty($value['organization'])): ?>
                                    <span class="fw-medium"><?= htmlspecialchars($value['organization']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty($value['position_title'])): ?>
                                    <span><?= htmlspecialchars($value['position_title']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span><i class="bx bx-envelope text-muted me-1"></i><?= htmlspecialchars($value['email'] ?? '-') ?></span>
                                    <?php if (!empty($value['phone'])): ?>
                                        <small><i class="bx bx-phone text-muted me-1"></i><?= htmlspecialchars($value['phone']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($pays_name) ?></span>
                            </td>

                            <td class="text-center">
                                <?= $range_badge ?>
                            </td>

                            <td class="text-center">
                                <?= $timeline_badge ?>
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
                                        <h5 class="modal-title"><i class="bx bx-user-circle me-2"></i>Détails de l'investisseur</h5>
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
                                                    <?php if (!empty($value['position_title'])): ?>
                                                        <p class="text-muted mb-1"><?= htmlspecialchars($value['position_title']) ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($value['organization'])): ?>
                                                        <p class="fw-bold"><?= htmlspecialchars($value['organization']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="list-group list-group-flush">
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <span class="text-muted"><i class="bx bx-envelope me-2"></i>Email</span>
                                                        <span class="fw-bold"><?= htmlspecialchars($value['email'] ?? '-') ?></span>
                                                    </div>
                                                    <?php if (!empty($value['phone'])): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <span class="text-muted"><i class="bx bx-phone me-2"></i>Téléphone</span>
                                                        <span class="fw-bold"><?= htmlspecialchars($value['phone']) ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <span class="text-muted"><i class="bx bx-map me-2"></i>Pays</span>
                                                        <span class="fw-bold"><?= htmlspecialchars($pays_name) ?></span>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <span class="text-muted"><i class="bx bx-calendar me-2"></i>Inscrit le</span>
                                                        <span class="fw-bold"><?= !empty($value['created_at']) ? date('d/m/Y H:i', strtotime($value['created_at'])) : '-' ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="row g-4">
                                                    <!-- Types d'intérêt -->
                                                    <div class="col-12">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-handicap me-2"></i>Types d'intérêt</h6>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <?php if (!empty($value['interest_equity']) && $value['interest_equity'] == 1): ?>
                                                                        <span class="badge bg-success p-2"><i class="bx bx-check-circle me-1"></i>Equity</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['interest_debt']) && $value['interest_debt'] == 1): ?>
                                                                        <span class="badge bg-warning text-dark p-2"><i class="bx bx-check-circle me-1"></i>Debt</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['interest_blended_finance']) && $value['interest_blended_finance'] == 1): ?>
                                                                        <span class="badge bg-info p-2"><i class="bx bx-check-circle me-1"></i>Blended Finance</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['interest_grant']) && $value['interest_grant'] == 1): ?>
                                                                        <span class="badge bg-primary p-2"><i class="bx bx-check-circle me-1"></i>Grant</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['interest_strategic_partnership']) && $value['interest_strategic_partnership'] == 1): ?>
                                                                        <span class="badge bg-secondary p-2"><i class="bx bx-check-circle me-1"></i>Strategic Partnership</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['interest_technical_collaboration']) && $value['interest_technical_collaboration'] == 1): ?>
                                                                        <span class="badge bg-dark p-2"><i class="bx bx-check-circle me-1"></i>Technical Collaboration</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['interest_offtake_distribution']) && $value['interest_offtake_distribution'] == 1): ?>
                                                                        <span class="badge bg-danger p-2"><i class="bx bx-check-circle me-1"></i>Offtake/Distribution</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['interest_other'])): ?>
                                                                        <span class="badge bg-light text-dark p-2"><i class="bx bx-check-circle me-1"></i><?= htmlspecialchars($value['interest_other']) ?></span>
                                                                    <?php endif; ?>
                                                                    <?php if (empty($value['interest_equity']) && empty($value['interest_debt']) && empty($value['interest_blended_finance']) && 
                                                                              empty($value['interest_grant']) && empty($value['interest_strategic_partnership']) && 
                                                                              empty($value['interest_technical_collaboration']) && empty($value['interest_offtake_distribution']) && 
                                                                              empty($value['interest_other'])): ?>
                                                                        <span class="text-muted">Aucun intérêt spécifié</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Focus Areas -->
                                                    <div class="col-12">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-bullseye me-2"></i>Domaines d'intérêt</h6>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <?php if (!empty($value['focus_research_lab']) && $value['focus_research_lab'] == 1): ?>
                                                                        <span class="badge bg-info p-2"><i class="bx bx-flask me-1"></i>Research & Lab</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['focus_gmp_facility']) && $value['focus_gmp_facility'] == 1): ?>
                                                                        <span class="badge bg-warning text-dark p-2"><i class="bx bx-industry me-1"></i>GMP Facility</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['focus_medicinal_plant']) && $value['focus_medicinal_plant'] == 1): ?>
                                                                        <span class="badge bg-success p-2"><i class="bx bx-leaf me-1"></i>Medicinal Plant</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['focus_commercialization']) && $value['focus_commercialization'] == 1): ?>
                                                                        <span class="badge bg-primary p-2"><i class="bx bx-chart-line me-1"></i>Commercialization</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($value['focus_full_platform']) && $value['focus_full_platform'] == 1): ?>
                                                                        <span class="badge bg-secondary p-2"><i class="bx bx-cubes me-1"></i>Full Platform</span>
                                                                    <?php endif; ?>
                                                                    <?php if (empty($value['focus_research_lab']) && empty($value['focus_gmp_facility']) && empty($value['focus_medicinal_plant']) && 
                                                                              empty($value['focus_commercialization']) && empty($value['focus_full_platform'])): ?>
                                                                        <span class="text-muted">Aucun domaine spécifié</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Engagement -->
                                                    <div class="col-md-6">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-money me-2"></i>Fourchette d'engagement</h6>
                                                                <p class="h5 mb-0"><?= $range_badge ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-time me-2"></i>Calendrier</h6>
                                                                <p class="h5 mb-0"><?= $timeline_badge ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Message stratégique -->
                                                    <?php if (!empty($value['strategic_message'])): ?>
                                                    <div class="col-12">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-message-dots me-2"></i>Message stratégique</h6>
                                                                <p class="mb-0"><?= nl2br(htmlspecialchars($value['strategic_message'])) ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Conformité -->
                                                    <div class="col-12">
                                                        <div class="card border-0 bg-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary mb-3"><i class="bx bx-shield me-2"></i>Conformité</h6>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="bx <?= (!empty($value['agree_contact']) && $value['agree_contact'] == 1) ? 'bx-check-circle text-success' : 'bx-x-circle text-danger' ?> fs-5 me-2"></i>
                                                                            <span>Accepte d'être contacté</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="bx <?= (!empty($value['non_binding_confirmation']) && $value['non_binding_confirmation'] == 1) ? 'bx-check-circle text-success' : 'bx-x-circle text-danger' ?> fs-5 me-2"></i>
                                                                            <span>Confirmation non engageante</span>
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
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'investisseur</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Investors/Update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <!-- Section Identité -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-user me-2"></i>Informations personnelles</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($value['full_name'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($value['email'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Téléphone</label>
                                                            <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($value['phone'] ?? '') ?>">
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
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Organisation</label>
                                                            <input type="text" class="form-control" name="organization" value="<?= htmlspecialchars($value['organization'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Poste / Titre</label>
                                                            <input type="text" class="form-control" name="position_title" value="<?= htmlspecialchars($value['position_title'] ?? '') ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Intérêts -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-handicap me-2"></i>Types d'intérêt</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="interest_equity" id="interest_equity_<?= $value['id'] ?>" value="1" <?= (!empty($value['interest_equity']) && $value['interest_equity'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="interest_equity_<?= $value['id'] ?>">Equity</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="interest_debt" id="interest_debt_<?= $value['id'] ?>" value="1" <?= (!empty($value['interest_debt']) && $value['interest_debt'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="interest_debt_<?= $value['id'] ?>">Debt</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="interest_blended_finance" id="interest_blended_finance_<?= $value['id'] ?>" value="1" <?= (!empty($value['interest_blended_finance']) && $value['interest_blended_finance'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="interest_blended_finance_<?= $value['id'] ?>">Blended Finance</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="interest_grant" id="interest_grant_<?= $value['id'] ?>" value="1" <?= (!empty($value['interest_grant']) && $value['interest_grant'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="interest_grant_<?= $value['id'] ?>">Grant</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="interest_strategic_partnership" id="interest_strategic_partnership_<?= $value['id'] ?>" value="1" <?= (!empty($value['interest_strategic_partnership']) && $value['interest_strategic_partnership'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="interest_strategic_partnership_<?= $value['id'] ?>">Strategic Partnership</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="interest_technical_collaboration" id="interest_technical_collaboration_<?= $value['id'] ?>" value="1" <?= (!empty($value['interest_technical_collaboration']) && $value['interest_technical_collaboration'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="interest_technical_collaboration_<?= $value['id'] ?>">Technical Collaboration</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="interest_offtake_distribution" id="interest_offtake_distribution_<?= $value['id'] ?>" value="1" <?= (!empty($value['interest_offtake_distribution']) && $value['interest_offtake_distribution'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="interest_offtake_distribution_<?= $value['id'] ?>">Offtake/Distribution</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Autre intérêt</label>
                                                            <input type="text" class="form-control" name="interest_other" value="<?= htmlspecialchars($value['interest_other'] ?? '') ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Engagement -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-money me-2"></i>Engagement</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Fourchette d'engagement</label>
                                                            <select class="form-select" name="commitment_range">
                                                                <option value="">Sélectionner...</option>
                                                                <option value="Below 250K" <?= ($value['commitment_range'] ?? '') == 'Below 250K' ? 'selected' : '' ?>>Below 250K</option>
                                                                <option value="250K-1M" <?= ($value['commitment_range'] ?? '') == '250K-1M' ? 'selected' : '' ?>>250K - 1M</option>
                                                                <option value="1M-5M" <?= ($value['commitment_range'] ?? '') == '1M-5M' ? 'selected' : '' ?>>1M - 5M</option>
                                                                <option value="5M+" <?= ($value['commitment_range'] ?? '') == '5M+' ? 'selected' : '' ?>>5M+</option>
                                                                <option value="To be discussed" <?= ($value['commitment_range'] ?? '') == 'To be discussed' ? 'selected' : '' ?>>To be discussed</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Calendrier</label>
                                                            <select class="form-select" name="timeline">
                                                                <option value="">Sélectionner...</option>
                                                                <option value="Immediate" <?= ($value['timeline'] ?? '') == 'Immediate' ? 'selected' : '' ?>>Immédiat</option>
                                                                <option value="3-6 months" <?= ($value['timeline'] ?? '') == '3-6 months' ? 'selected' : '' ?>>3-6 mois</option>
                                                                <option value="6-12 months" <?= ($value['timeline'] ?? '') == '6-12 months' ? 'selected' : '' ?>>6-12 mois</option>
                                                                <option value="Exploratory" <?= ($value['timeline'] ?? '') == 'Exploratory' ? 'selected' : '' ?>>Exploratoire</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Focus Areas -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-bullseye me-2"></i>Domaines d'intérêt</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="focus_research_lab" id="focus_research_lab_<?= $value['id'] ?>" value="1" <?= (!empty($value['focus_research_lab']) && $value['focus_research_lab'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="focus_research_lab_<?= $value['id'] ?>">Research & Lab</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="focus_gmp_facility" id="focus_gmp_facility_<?= $value['id'] ?>" value="1" <?= (!empty($value['focus_gmp_facility']) && $value['focus_gmp_facility'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="focus_gmp_facility_<?= $value['id'] ?>">GMP Facility</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="focus_medicinal_plant" id="focus_medicinal_plant_<?= $value['id'] ?>" value="1" <?= (!empty($value['focus_medicinal_plant']) && $value['focus_medicinal_plant'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="focus_medicinal_plant_<?= $value['id'] ?>">Medicinal Plant</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="focus_commercialization" id="focus_commercialization_<?= $value['id'] ?>" value="1" <?= (!empty($value['focus_commercialization']) && $value['focus_commercialization'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="focus_commercialization_<?= $value['id'] ?>">Commercialization</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="focus_full_platform" id="focus_full_platform_<?= $value['id'] ?>" value="1" <?= (!empty($value['focus_full_platform']) && $value['focus_full_platform'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="focus_full_platform_<?= $value['id'] ?>">Full Platform</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Message Stratégique -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-message-dots me-2"></i>Message stratégique</h6>
                                                    <textarea class="form-control" name="strategic_message" rows="3"><?= htmlspecialchars($value['strategic_message'] ?? '') ?></textarea>
                                                </div>
                                            </div>

                                            <!-- Section Conformité -->
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-shield me-2"></i>Conformité</h6>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input" name="agree_contact" id="agree_contact_<?= $value['id'] ?>" value="1" <?= (!empty($value['agree_contact']) && $value['agree_contact'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="agree_contact_<?= $value['id'] ?>">Accepte d'être contacté</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input" name="non_binding_confirmation" id="non_binding_confirmation_<?= $value['id'] ?>" value="1" <?= (!empty($value['non_binding_confirmation']) && $value['non_binding_confirmation'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="non_binding_confirmation_<?= $value['id'] ?>">Confirmation non engageante</label>
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
                                        <p class="text-muted">Vous êtes sur le point de supprimer <strong><?= htmlspecialchars($value['full_name'] ?? '') ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible (suppression définitive).</p>
                                    </div>
                                    <form action="<?= base_url('Investors/Delete') ?>" method="POST">
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
                                <p class="mt-3 text-muted">Aucun investisseur trouvé</p>
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

<!-- MODAL CREATE INVESTOR -->
<div class="modal fade" id="create_investor" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-user-plus me-2"></i>Nouvel Investisseur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Investors/Create') ?>" method="POST">
                <div class="modal-body p-4">
                    <!-- Section Identité -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-user me-2"></i>Informations personnelles</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="full_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Téléphone</label>
                                    <input type="tel" class="form-control" name="phone">
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
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Organisation</label>
                                    <input type="text" class="form-control" name="organization">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Poste / Titre</label>
                                    <input type="text" class="form-control" name="position_title">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Intérêts -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-handicap me-2"></i>Types d'intérêt</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="interest_equity" id="create_interest_equity" value="1">
                                        <label class="form-check-label" for="create_interest_equity">Equity</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="interest_debt" id="create_interest_debt" value="1">
                                        <label class="form-check-label" for="create_interest_debt">Debt</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="interest_blended_finance" id="create_interest_blended_finance" value="1">
                                        <label class="form-check-label" for="create_interest_blended_finance">Blended Finance</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="interest_grant" id="create_interest_grant" value="1">
                                        <label class="form-check-label" for="create_interest_grant">Grant</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="interest_strategic_partnership" id="create_interest_strategic_partnership" value="1">
                                        <label class="form-check-label" for="create_interest_strategic_partnership">Strategic Partnership</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="interest_technical_collaboration" id="create_interest_technical_collaboration" value="1">
                                        <label class="form-check-label" for="create_interest_technical_collaboration">Technical Collaboration</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="interest_offtake_distribution" id="create_interest_offtake_distribution" value="1">
                                        <label class="form-check-label" for="create_interest_offtake_distribution">Offtake/Distribution</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Autre intérêt</label>
                                    <input type="text" class="form-control" name="interest_other">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Engagement -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-money me-2"></i>Engagement</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fourchette d'engagement</label>
                                    <select class="form-select" name="commitment_range">
                                        <option value="">Sélectionner...</option>
                                        <option value="Below 250K">Below 250K</option>
                                        <option value="250K-1M">250K - 1M</option>
                                        <option value="1M-5M">1M - 5M</option>
                                        <option value="5M+">5M+</option>
                                        <option value="To be discussed">To be discussed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Calendrier</label>
                                    <select class="form-select" name="timeline">
                                        <option value="">Sélectionner...</option>
                                        <option value="Immediate">Immédiat</option>
                                        <option value="3-6 months">3-6 mois</option>
                                        <option value="6-12 months">6-12 mois</option>
                                        <option value="Exploratory" selected>Exploratoire</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Focus Areas -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-bullseye me-2"></i>Domaines d'intérêt</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="focus_research_lab" id="create_focus_research_lab" value="1">
                                        <label class="form-check-label" for="create_focus_research_lab">Research & Lab</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="focus_gmp_facility" id="create_focus_gmp_facility" value="1">
                                        <label class="form-check-label" for="create_focus_gmp_facility">GMP Facility</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="focus_medicinal_plant" id="create_focus_medicinal_plant" value="1">
                                        <label class="form-check-label" for="create_focus_medicinal_plant">Medicinal Plant</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="focus_commercialization" id="create_focus_commercialization" value="1">
                                        <label class="form-check-label" for="create_focus_commercialization">Commercialization</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="focus_full_platform" id="create_focus_full_platform" value="1">
                                        <label class="form-check-label" for="create_focus_full_platform">Full Platform</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Message Stratégique -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-message-dots me-2"></i>Message stratégique</h6>
                            <textarea class="form-control" name="strategic_message" rows="3" placeholder="Décrivez brièvement l'intérêt stratégique..."></textarea>
                        </div>
                    </div>

                    <!-- Section Conformité -->
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-shield me-2"></i>Conformité</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="agree_contact" id="create_agree_contact" value="1" checked>
                                        <label class="form-check-label" for="create_agree_contact">Accepte d'être contacté</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="non_binding_confirmation" id="create_non_binding_confirmation" value="1" checked>
                                        <label class="form-check-label" for="create_non_binding_confirmation">Confirmation non engageante</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer l'investisseur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#investorsTable').DataTable({
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

// Preview des champs conditionnels si nécessaire
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>