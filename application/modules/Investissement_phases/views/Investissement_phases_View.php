<?php
// =====================================================================
// PAGES STATIQUES — la table `pages` a été supprimée de la base.
// Ces données sont définies en dur directement dans les vues.
// =====================================================================
$pages = array(
    array('id_page' => 1,  'titre_page' => 'Home',                                    'slug' => 'home'),
    array('id_page' => 2,  'titre_page' => 'About',                                   'slug' => 'about'),
    array('id_page' => 3,  'titre_page' => 'Background & Strategic Rationale',        'slug' => 'background-strategic-rationale'),
    array('id_page' => 4,  'titre_page' => 'ESG & Sustainability',                    'slug' => 'esg-sustainability'),
    array('id_page' => 5,  'titre_page' => 'Research & Innovation',                   'slug' => 'research-innovation'),
    array('id_page' => 6,  'titre_page' => 'Corporate Structure & Governance',        'slug' => 'corporate-structure-governance'),
    array('id_page' => 7,  'titre_page' => 'nufotec-phytomed-industries-facility',    'slug' => 'nufotec-phytomed-industries-facility'),
    array('id_page' => 8,  'titre_page' => 'Our Product Categories',                  'slug' => 'product-categories'),
    array('id_page' => 9,  'titre_page' => 'Raw Material Acquisition',                'slug' => 'raw-material-acquisition'),
    array('id_page' => 10, 'titre_page' => 'Industrial Technology & Processing Systems', 'slug' => 'industrial-technology'),
    array('id_page' => 11, 'titre_page' => 'Market & Industry Outlook',               'slug' => 'market-outlook'),
    array('id_page' => 12, 'titre_page' => 'Digital Growth & Market Expansion Platform', 'slug' => 'digital-growth'),
    array('id_page' => 13, 'titre_page' => 'Digital Health Consultation',             'slug' => 'digital-health'),
    array('id_page' => 14, 'titre_page' => 'Phased Investment Projection',            'slug' => 'investment-projection'),
    array('id_page' => 15, 'titre_page' => 'Our Investor & Partner Commitment',       'slug' => 'investor-commitment'),
    array('id_page' => 16, 'titre_page' => 'Commission Fee Payment to Brokers',       'slug' => 'broker-commission'),
    array('id_page' => 17, 'titre_page' => 'Risk Analysis & Mitigation Strategies',   'slug' => 'risk-analysis'),
    array('id_page' => 18, 'titre_page' => 'Strategic Partnerships',                  'slug' => 'strategic-partnerships'),
    array('id_page' => 19, 'titre_page' => 'Our Services',                            'slug' => 'our-services'),
    array('id_page' => 22, 'titre_page' => 'Vision & Mission',                        'slug' => 'vision-mission'),
    array('id_page' => 23, 'titre_page' => 'Brokers Form',                            'slug' => 'brokers-form'),
    array('id_page' => 24, 'titre_page' => 'Investors form',                          'slug' => 'investors-form'),
);
$pages_list = $pages; ?>
<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Gestion Investissement</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Phases d'Investissement</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_phase">
                <i class="bx bx-plus"></i> Nouvelle Phase
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
                <h5 class="mb-0 text-primary"><i class="bx bx-trending-up me-2"></i>Phases d'Investissement</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="phasesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Nom de la phase</th>
                            <th width="12%">Période</th>
                            <th width="15%">Montant total</th>
                            <th width="25%">Allocation</th>
                            <th width="10%">Page</th>
                            <th width="5%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($phases)): $i = 1; foreach ($phases as $value): 
                        // Récupérer le nom de la page associée
                        $page_name = 'Aucune';
                        if (!empty($pages) && !empty($value['id_page_associee'])) {
                            foreach ($pages as $page) {
                                if ($page['id_page'] == $value['id_page_associee']) {
                                    $page_name = $page['titre_page'];
                                    break;
                                }
                            }
                        }
                        
                        // Parser l'allocation JSON
                        $allocations = $this->Investissement_phases->get_allocation_array($value['allocation_details']);
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['nom_phase']) ?></strong>
                                    <?php if (!empty($value['description'])): ?>
                                        <small class="text-muted text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($value['description']) ?>">
                                            <?= htmlspecialchars(substr($value['description'], 0, 50)) ?>...
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bx bx-calendar me-1"></i>
                                    <?= $value['annee_debut'] ?><?= !empty($value['annee_fin']) ? ' - ' . $value['annee_fin'] : '' ?>
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-success fs-6">
                                    <?= $this->Investissement_phases->format_montant($value['montant_total'], $value['devise']) ?>
                                </span>
                                <small class="text-muted d-block"><?= $value['devise'] ?></small>
                            </td>

                            <td>
                                <?php if (!empty($allocations)): ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($allocations as $key => $pourcentage): 
                                            $color = $this->Investissement_phases->get_allocation_color($key);
                                            $label = $this->Investissement_phases->get_allocation_label($key);
                                        ?>
                                            <span class="badge bg-<?= $color ?>" title="<?= $label ?>: <?= $pourcentage ?>%">
                                                <?= $label ?> (<?= $pourcentage ?>%)
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <small class="text-muted"><?= htmlspecialchars($page_name) ?></small>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('Investissement_phases/PhaseDetail/' . $value['id_phase'] . '_' . urlencode($value['nom_phase'])) ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_phase'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_phase'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id_phase'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la phase</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Investissement_phases/Update') ?>" method="POST">
                                        <input type="hidden" name="id_phase" value="<?= $value['id_phase'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations générales</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Nom de la phase <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="nom_phase" value="<?= htmlspecialchars($value['nom_phase']) ?>" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Année début <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" name="annee_debut" value="<?= $value['annee_debut'] ?>" min="2000" max="2100" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Année fin</label>
                                                            <input type="number" class="form-control" name="annee_fin" value="<?= $value['annee_fin'] ?>" min="2000" max="2100">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-money me-2"></i>Financement</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Montant total <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" class="form-control" name="montant_total" value="<?= $value['montant_total'] ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Devise</label>
                                                            <select class="form-select" name="devise">
                                                                <option value="USD" <?= $value['devise'] == 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                                                <option value="EUR" <?= $value['devise'] == 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                                                                <option value="GBP" <?= $value['devise'] == 'GBP' ? 'selected' : '' ?>>GBP (£)</option>
                                                                <option value="XOF" <?= $value['devise'] == 'XOF' ? 'selected' : '' ?>>XOF (FCFA)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-pie-chart-alt me-2"></i>Allocation des fonds</h6>
                                                    <div id="allocation-container-<?= $value['id_phase'] ?>">
                                                        <?php 
                                                        $allocations = $this->Investissement_phases->get_allocation_array($value['allocation_details']);
                                                        if (!empty($allocations)): 
                                                            $idx = 0;
                                                            foreach ($allocations as $key => $pourcentage): 
                                                        ?>
                                                            <div class="row g-2 mb-2 allocation-row">
                                                                <div class="col-md-5">
                                                                    <input type="text" class="form-control" name="allocation_categorie[]" value="<?= $key ?>" placeholder="Catégorie (ex: construction)">
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <div class="input-group">
                                                                        <input type="number" step="0.1" class="form-control" name="allocation_pourcentage[]" value="<?= $pourcentage ?>" placeholder="Pourcentage" min="0" max="100">
                                                                        <span class="input-group-text">%</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeAllocationRow(this)">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        <?php 
                                                            $idx++;
                                                            endforeach; 
                                                        else: 
                                                        ?>
                                                            <div class="row g-2 mb-2 allocation-row">
                                                                <div class="col-md-5">
                                                                    <input type="text" class="form-control" name="allocation_categorie[]" placeholder="Catégorie (ex: construction)">
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <div class="input-group">
                                                                        <input type="number" step="0.1" class="form-control" name="allocation_pourcentage[]" placeholder="Pourcentage" min="0" max="100">
                                                                        <span class="input-group-text">%</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeAllocationRow(this)">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addAllocationRow('allocation-container-<?= $value['id_phase'] ?>')">
                                                        <i class="bx bx-plus"></i> Ajouter une allocation
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-detail me-2"></i>Description</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-12">
                                                            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-link me-2"></i>Association</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-bold">Page associée</label>
                                                            <select class="form-select" name="id_page_associee">
                                                                <option value="">-- Aucune --</option>
                                                                <?php foreach ($pages as $page): ?>
                                                                    <option value="<?= $page['id_page'] ?>" <?= ($value['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($page['titre_page']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
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
                        <div class="modal fade" id="delete_<?= $value['id_phase'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer la phase:</p>
                                        <p class="font-weight-bold text-dark">"<?= htmlspecialchars($value['nom_phase']) ?>"</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Investissement_phases/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_phase'] ?>">
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
                            <td colspan="7" class="text-center py-5">
                                <i class="bx bx-trending-up text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune phase d'investissement enregistrée</p>
                                <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_phase">
                                    <i class="bx bx-plus"></i> Ajouter une phase
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


<!-- MODAL CREATE -->
<div class="modal fade" id="create_phase" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Phase d'Investissement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Investissement_phases/Create') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations générales</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nom de la phase <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nom_phase" placeholder="ex: Seed Capital Phase I" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Année début <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="annee_debut" value="2026" min="2000" max="2100" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Année fin</label>
                                    <input type="number" class="form-control" name="annee_fin" min="2000" max="2100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-money me-2"></i>Financement</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Montant total <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="montant_total" placeholder="20000000.00" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Devise</label>
                                    <select class="form-select" name="devise">
                                        <option value="USD" selected>USD ($)</option>
                                        <option value="EUR">EUR (€)</option>
                                        <option value="GBP">GBP (£)</option>
                                        <option value="XOF">XOF (FCFA)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-pie-chart-alt me-2"></i>Allocation des fonds</h6>
                            <div id="allocation-container-create">
                                <div class="row g-2 mb-2 allocation-row">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="allocation_categorie[]" placeholder="Catégorie (ex: construction)">
                                    </div>
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <input type="number" step="0.1" class="form-control" name="allocation_pourcentage[]" placeholder="Pourcentage" min="0" max="100">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeAllocationRow(this)">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addAllocationRow('allocation-container-create')">
                                <i class="bx bx-plus"></i> Ajouter une allocation
                            </button>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-detail me-2"></i>Description</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <textarea class="form-control" name="description" rows="3" placeholder="Description de la phase d'investissement..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-link me-2"></i>Association</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Page associée</label>
                                    <select class="form-select" name="id_page_associee">
                                        <option value="">-- Aucune --</option>
                                        <?php foreach ($pages as $page): ?>
                                            <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer la phase
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#phasesTable').DataTable({
        language: {
            url: '<?= base_url("assets/backend/plugins/datatable/js/fr-FR.json") ?>'
        },
        order: [[2, 'asc']], // Tri par année début
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [6] }
        ]
    });
    
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Ajouter une ligne d'allocation
function addAllocationRow(containerId) {
    const container = document.getElementById(containerId);
    const newRow = document.createElement('div');
    newRow.className = 'row g-2 mb-2 allocation-row';
    newRow.innerHTML = `
        <div class="col-md-5">
            <input type="text" class="form-control" name="allocation_categorie[]" placeholder="Catégorie (ex: construction)">
        </div>
        <div class="col-md-5">
            <div class="input-group">
                <input type="number" step="0.1" class="form-control" name="allocation_pourcentage[]" placeholder="Pourcentage" min="0" max="100">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeAllocationRow(this)">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(newRow);
}

// Supprimer une ligne d'allocation
function removeAllocationRow(button) {
    const row = button.closest('.allocation-row');
    const container = row.parentElement;
    // Garder au moins une ligne
    if (container.querySelectorAll('.allocation-row').length > 1) {
        row.remove();
    } else {
        // Vider les champs au lieu de supprimer
        row.querySelectorAll('input').forEach(input => input.value = '');
    }
}
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>