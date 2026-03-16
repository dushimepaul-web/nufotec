<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<!-- TinyMCE Self-Hosted -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Workflow</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Workflow par Catégorie</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_workflow">
                <i class="bx bx-plus"></i> Nouvelle Étape
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

    <?php
    // Organiser les workflows par catégorie
    $workflows_by_category = [];
    foreach ($workflows as $wf) {
        $cat_id = $wf['id_categorie'];
        if (!isset($workflows_by_category[$cat_id])) {
            $workflows_by_category[$cat_id] = [];
        }
        $workflows_by_category[$cat_id][] = $wf;
    }
    
    // Mapping des types d'étapes
    $type_labels = [
        'soumission' => ['label' => 'Soumission', 'color' => 'info', 'icon' => 'upload'],
        'validation' => ['label' => 'Validation', 'color' => 'warning', 'icon' => 'check-circle'],
        'production' => ['label' => 'Production', 'color' => 'primary', 'icon' => 'cogs'],
        'controle_qualite' => ['label' => 'Contrôle Qualité', 'color' => 'success', 'icon' => 'shield-check'],
        'conditionnement' => ['label' => 'Conditionnement', 'color' => 'secondary', 'icon' => 'box'],
        'stockage' => ['label' => 'Stockage', 'color' => 'dark', 'icon' => 'warehouse'],
        'expedition' => ['label' => 'Expédition', 'color' => 'primary', 'icon' => 'truck'],
        'livraison' => ['label' => 'Livraison', 'color' => 'success', 'icon' => 'check-double'],
        'facturation' => ['label' => 'Facturation', 'color' => 'warning', 'icon' => 'file-invoice'],
        'archive' => ['label' => 'Archivage', 'color' => 'secondary', 'icon' => 'archive']
    ];
    ?>

    <!-- STATISTIQUES GÉNÉRALES -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-white-50">Total Étapes</p>
                            <h3 class="mb-0"><?= $stats['total_etapes'] ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-list-ol fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-white-50">Catégories avec Workflow</p>
                            <h3 class="mb-0"><?= count($stats['categories_with_workflow']) ?> / <?= $stats['total_categories'] ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-category fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-dark-50">Moyenne Étapes/Catégorie</p>
                            <h3 class="mb-0"><?= $stats['moyenne_etapes_par_cat'] ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-calculator fs-1 text-dark-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-white-50">Délai Moyen par Étape</p>
                            <h3 class="mb-0"><?= $stats['delai_total_moyen'] ?>h</h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-time fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VISUALISATION DES WORKFLOWS PAR CATÉGORIE -->
    <div class="row mb-4">
        <?php foreach ($categories as $cat): 
            $cat_workflows = $workflows_by_category[$cat['id_categorie']] ?? [];
            $total_delai = array_sum(array_column($cat_workflows, 'delai_heures'));
            $nb_obligatoires = count(array_filter($cat_workflows, function($w) { return $w['est_obligatoire']; }));
        ?>
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                            <i class="bi bi-<?= htmlspecialchars($cat['icone'] ?? 'tag') ?> text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">[<?= $cat['code_categorie'] ?>] <?= htmlspecialchars($cat['nom_categorie']) ?></h5>
                            <small class="text-muted"><?= count($cat_workflows) ?> étape(s) configurée(s)</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <?php if (count($cat_workflows) > 0): ?>
                            <span class="badge bg-success">Actif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Non configuré</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($cat_workflows) > 0): ?>
                        <!-- Timeline du workflow -->
                        <div class="workflow-timeline">
                            <?php foreach ($cat_workflows as $index => $wf): 
                                $type_info = $type_labels[$wf['type_etape']] ?? ['label' => $wf['type_etape'], 'color' => 'secondary', 'icon' => 'circle'];
                                $is_last = ($index === count($cat_workflows) - 1);
                            ?>
                            <div class="workflow-step <?= $wf['est_active'] ? '' : 'opacity-50' ?>" data-id="<?= $wf['id_workflow'] ?>">
                                <div class="step-marker" style="background-color: <?= $wf['couleur_etape'] ?>">
                                    <i class="bi bi-<?= $wf['icone_etape'] ?>"></i>
                                    <span class="step-number"><?= $wf['etape_ordre'] ?></span>
                                </div>
                                <div class="step-content">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($wf['nom_etape']) ?></h6>
                                            <p class="mb-1 text-muted small">
                                                <?php 
                                                $desc = strip_tags($wf['description_etape'] ?? '');
                                                echo (strlen($desc) > 60) ? substr($desc, 0, 60) . '...' : $desc;
                                                ?>
                                            </p>
                                            <div class="d-flex gap-2 mt-2">
                                                <span class="badge bg-<?= $type_info['color'] ?> bg-opacity-75">
                                                    <i class="bi bi-<?= $type_info['icon'] ?> me-1"></i><?= $type_info['label'] ?>
                                                </span>
                                                <?php if ($wf['est_obligatoire']): ?>
                                                    <span class="badge bg-danger">Obligatoire</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Optionnel</span>
                                                <?php endif; ?>
                                                <?php if (!empty($wf['delai_heures'])): ?>
                                                    <span class="badge bg-info">
                                                        <i class="bx bx-time me-1"></i><?= $wf['delai_heures'] ?>h
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $wf['id_workflow'] ?>">
                                                        <i class="bx bx-show text-info me-2"></i>Voir
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0)" onclick="openEditModal(<?= $wf['id_workflow'] ?>)">
                                                        <i class="bx bx-edit text-warning me-2"></i>Modifier
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $wf['id_workflow'] ?>">
                                                        <i class="bx bx-trash me-2"></i>Supprimer
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!$is_last): ?>
                                <div class="step-connector"></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Résumé -->
                        <div class="alert alert-light border mt-3 mb-0">
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted d-block">Délai total</small>
                                    <strong><?= $total_delai ?>h</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Étapes obligatoires</small>
                                    <strong><?= $nb_obligatoires ?> / <?= count($cat_workflows) ?></strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Notifications</small>
                                    <strong><?= count(array_filter($cat_workflows, function($w) { return $w['notification_email']; })) ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bx bx-git-branch text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Aucun workflow configuré pour cette catégorie</p>
                            <button class="btn btn-sm btn-outline-primary" onclick="prefillCategory(<?= $cat['id_categorie'] ?>)">
                                <i class="bx bx-plus me-1"></i>Créer le premier workflow
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- LISTE TABLEAU COMPLET -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-primary"><i class="bx bx-table me-2"></i>Liste Complète des Étapes</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="workflowsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">Ordre</th>
                            <th width="15%">Catégorie</th>
                            <th width="20%">Nom Étape</th>
                            <th width="12%">Type</th>
                            <th width="12%">Responsable</th>
                            <th width="8%">Délai</th>
                            <th width="8%">Obligatoire</th>
                            <th width="8%">Actif</th>
                            <th width="12%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($workflows)): foreach ($workflows as $wf): 
                        // Trouver la catégorie
                        $cat_nom = 'Inconnue';
                        $cat_code = '';
                        foreach ($categories as $c) {
                            if ($c['id_categorie'] == $wf['id_categorie']) {
                                $cat_nom = $c['nom_categorie'];
                                $cat_code = $c['code_categorie'];
                                break;
                            }
                        }
                        
                        $type_info = $type_labels[$wf['type_etape']] ?? ['label' => $wf['type_etape'], 'color' => 'secondary', 'icon' => 'circle'];
                    ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary fs-6"><?= $wf['etape_ordre'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-primary me-1"><?= $cat_code ?></span>
                                <small><?= htmlspecialchars($cat_nom) ?></small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="step-icon-small me-2" style="background-color: <?= $wf['couleur_etape'] ?>">
                                        <i class="bi bi-<?= $wf['icone_etape'] ?> text-white small"></i>
                                    </div>
                                    <strong><?= htmlspecialchars($wf['nom_etape']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $type_info['color'] ?> bg-opacity-75">
                                    <i class="bi bi-<?= $type_info['icon'] ?> me-1"></i><?= $type_info['label'] ?>
                                </span>
                            </td>
                            <td>
                                <?= $wf['responsable_role'] ? htmlspecialchars($wf['responsable_role']) : '<span class="text-muted">-</span>' ?>
                            </td>
                            <td>
                                <?= $wf['delai_heures'] ? '<span class="badge bg-info">' . $wf['delai_heures'] . 'h</span>' : '<span class="text-muted">-</span>' ?>
                            </td>
                            <td class="text-center">
                                <?php if ($wf['est_obligatoire']): ?>
                                    <span class="badge bg-danger"><i class="bx bx-check"></i></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $wf['id_workflow'] ?>" class="text-decoration-none">
                                    <?php if ($wf['est_active']): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactif</span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $wf['id_workflow'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="openEditModal(<?= $wf['id_workflow'] ?>)">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $wf['id_workflow'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW -->
                        <div class="modal fade" id="view_<?= $wf['id_workflow'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header" style="background-color: <?= $wf['couleur_etape'] ?>">
                                        <h5 class="modal-title text-white"><i class="bx bx-git-commit me-2"></i>Détails de l'Étape</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h4 class="mb-3"><?= htmlspecialchars($wf['nom_etape']) ?></h4>
                                                <div class="bg-light p-3 rounded mb-3">
                                                    <?= nl2br($wf['description_etape'] ?? '<em class="text-muted">Aucune description</em>') ?>
                                                </div>
                                                
                                                <div class="row g-3 mt-3">
                                                    <div class="col-md-6">
                                                        <label class="text-muted small">Type d'étape</label>
                                                        <p class="mb-0 fw-bold">
                                                            <span class="badge bg-<?= $type_info['color'] ?>">
                                                                <?= $type_info['label'] ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="text-muted small">Responsable</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($wf['responsable_role'] ?? 'Non défini') ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="text-muted small">Délai estimé</label>
                                                        <p class="mb-0 fw-bold"><?= $wf['delai_heures'] ? $wf['delai_heures'] . ' heures' : 'Non défini' ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="text-muted small">Ordre dans le workflow</label>
                                                        <p class="mb-0 fw-bold">Étape <?= $wf['etape_ordre'] ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 border-start">
                                                <div class="text-center mb-3">
                                                    <div class="step-icon-large mx-auto" style="background-color: <?= $wf['couleur_etape'] ?>">
                                                        <i class="bi bi-<?= $wf['icone_etape'] ?> text-white fs-1"></i>
                                                    </div>
                                                </div>
                                                
                                                <div class="list-group list-group-flush">
                                                    <div class="list-group-item d-flex justify-content-between">
                                                        <span>Obligatoire</span>
                                                        <?= $wf['est_obligatoire'] ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-secondary">Non</span>' ?>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between">
                                                        <span>Notification email</span>
                                                        <?= $wf['notification_email'] ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-secondary">Non</span>' ?>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between">
                                                        <span>Statut</span>
                                                        <?= $wf['est_active'] ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Inactif</span>' ?>
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

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $wf['id_workflow'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer l'étape <strong><?= htmlspecialchars($wf['nom_etape']) ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Workflow_categories/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $wf['id_workflow'] ?>">
                                        <div class="modal-footer bg-light justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $wf['id_workflow'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= $wf['est_active'] ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= $wf['est_active'] ? '<i class="bx bx-block me-2"></i>Désactiver' : '<i class="bx bx-check-circle me-2"></i>Activer' ?> l'étape
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= $wf['est_active'] ? 'désactiver' : 'activer' ?></strong> l'étape <strong><?= htmlspecialchars($wf['nom_etape']) ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('Workflow_categories/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $wf['id_workflow'] ?>">
                                        <input type="hidden" name="est_active" value="<?= $wf['est_active'] ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= $wf['est_active'] ? 'btn-warning' : 'btn-success' ?>">
                                                <?= $wf['est_active'] ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bx bx-git-branch text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune étape de workflow configurée</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- MODAL CREATE WORKFLOW -->
<div class="modal fade" id="create_workflow" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Étape de Workflow</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="destroyTinyMCE('create_description')"></button>
            </div>

            <form action="<?= base_url('Workflow_categories/Create') ?>" method="POST" id="formCreate">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_categorie" required id="create_id_categorie">
                                <option value="">Sélectionner...</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id_categorie'] ?>">
                                        [<?= $c['code_categorie'] ?>] <?= htmlspecialchars($c['nom_categorie']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ordre de l'étape <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="etape_ordre" min="1" value="1" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nom de l'étape <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nom_etape" required placeholder="Ex: Validation administrative">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Type d'étape <span class="text-danger">*</span></label>
                            <select class="form-select" name="type_etape" required>
                                <?php foreach ($type_labels as $key => $info): ?>
                                    <option value="<?= $key ?>"><?= $info['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- DESCRIPTION AVEC TINYMCE -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea id="create_description" name="description_etape" class="form-control tinymce-editor" rows="6" placeholder="Description détaillée de cette étape..."></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Responsable (rôle)</label>
                            <input type="text" class="form-control" name="responsable_role" placeholder="Ex: production_manager, quality_manager...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Délai estimé (heures)</label>
                            <input type="number" class="form-control" name="delai_heures" min="0" placeholder="Ex: 24">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Icône (Bootstrap Icons)</label>
                            <input type="text" class="form-control" name="icone_etape" value="arrow-right" placeholder="Ex: check-circle, cogs...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Couleur</label>
                            <input type="color" class="form-control form-control-color" name="couleur_etape" value="#0f4c3a">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" class="form-check-input" name="est_obligatoire" id="create_oblig" value="1" checked>
                                <label class="form-check-label" for="create_oblig">Étape obligatoire</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="notification_email" id="create_notif" value="1" checked>
                                <label class="form-check-label" for="create_notif">Envoyer notification</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="destroyTinyMCE('create_description')">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer l'étape
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT WORKFLOW (UNIQUE, CHARGÉ DYNAMIQUEMENT) -->
<div class="modal fade" id="edit_workflow" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'Étape</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="destroyTinyMCE('edit_description')"></button>
            </div>

            <form action="<?= base_url('Workflow_categories/Update') ?>" method="POST" id="formEdit">
                <input type="hidden" name="id_workflow" id="edit_id_workflow">
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_categorie" required id="edit_id_categorie">
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id_categorie'] ?>">
                                        [<?= $c['code_categorie'] ?>] <?= htmlspecialchars($c['nom_categorie']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ordre de l'étape <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="etape_ordre" id="edit_etape_ordre" min="1" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nom de l'étape <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nom_etape" id="edit_nom_etape" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Type d'étape <span class="text-danger">*</span></label>
                            <select class="form-select" name="type_etape" required id="edit_type_etape">
                                <?php foreach ($type_labels as $key => $info): ?>
                                    <option value="<?= $key ?>"><?= $info['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- DESCRIPTION AVEC TINYMCE -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea id="edit_description" name="description_etape" class="form-control tinymce-editor" rows="6"></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Responsable (rôle)</label>
                            <input type="text" class="form-control" name="responsable_role" id="edit_responsable_role">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Délai estimé (heures)</label>
                            <input type="number" class="form-control" name="delai_heures" id="edit_delai_heures" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Icône (Bootstrap Icons)</label>
                            <input type="text" class="form-control" name="icone_etape" id="edit_icone_etape">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Couleur</label>
                            <input type="color" class="form-control form-control-color" name="couleur_etape" id="edit_couleur_etape">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" class="form-check-input" name="est_obligatoire" id="edit_est_obligatoire" value="1">
                                <label class="form-check-label" for="edit_est_obligatoire">Étape obligatoire</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="notification_email" id="edit_notification_email" value="1">
                                <label class="form-check-label" for="edit_notification_email">Notification email</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="est_active" id="edit_est_active" value="1">
                                <label class="form-check-label" for="edit_est_active">Étape active</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="destroyTinyMCE('edit_description')">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Workflow Timeline Styles */
.workflow-timeline {
    position: relative;
    padding-left: 40px;
}

.workflow-step {
    position: relative;
    margin-bottom: 20px;
}

.workflow-step:last-child {
    margin-bottom: 0;
}

.step-marker {
    position: absolute;
    left: -40px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    z-index: 2;
}

.step-number {
    position: absolute;
    bottom: -5px;
    right: -5px;
    background: #d4af37;
    color: #0f4c3a;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    font-size: 0.7rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
}

.step-connector {
    position: absolute;
    left: -20px;
    top: 40px;
    width: 2px;
    height: calc(100% + 20px);
    background: #dee2e6;
    z-index: 1;
}

.workflow-step:last-child .step-connector {
    display: none;
}

.step-content {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    border-left: 4px solid #0f4c3a;
}

.step-icon-small {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.step-icon-large {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* TinyMCE Custom Styles */
.tox-tinymce {
    border-radius: 8px !important;
    border: 1px solid #dee2e6 !important;
}

.tox-editor-container {
    border-radius: 8px !important;
}
</style>

<script>
// Configuration TinyMCE globale
const tinyMCEConfig = {
    height: 300,
    language: 'fr_FR',
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: [
        'undo redo | formatselect | bold italic underline strikethrough | forecolor backcolor',
        'alignleft aligncenter alignright alignjustify',
        'bullist numlist outdent indent',
        'table | link image | code fullscreen help'
    ].join(' | '),
    menubar: 'file edit view insert format tools table help',
    branding: false,
    promotion: false,
    content_style: `
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
            font-size: 14px; 
            line-height: 1.6; 
            padding: 15px;
        }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        table td, table th { border: 1px solid #dee2e6; padding: 8px; }
        table th { background-color: #f8f9fa; font-weight: 600; }
        img { max-width: 100%; height: auto; border-radius: 4px; }
    `,
    setup: function(editor) {
        editor.on('init', function() {
            console.log('TinyMCE initialisé:', editor.id);
        });
    }
};

// Initialisation TinyMCE pour le modal de création
document.addEventListener('DOMContentLoaded', function() {
    // Quand le modal create s'ouvre
    const createModal = document.getElementById('create_workflow');
    createModal.addEventListener('shown.bs.modal', function() {
        initTinyMCE('create_description');
    });
    
    // Initialisation DataTable
    $('#workflowsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Fonction pour initialiser TinyMCE
function initTinyMCE(selector) {
    // Détruire l'instance existante si présente
    destroyTinyMCE(selector);
    
    // Créer nouvelle instance avec délai pour le DOM
    setTimeout(() => {
        tinymce.init({
            ...tinyMCEConfig,
            selector: '#' + selector
        });
    }, 100);
}

// Fonction pour détruire TinyMCE proprement
function destroyTinyMCE(selector) {
    const editor = tinymce.get(selector);
    if (editor) {
        editor.remove();
    }
}

// Fonction pour ouvrir le modal d'édition avec données
function openEditModal(workflowId) {
    // Récupérer les données via AJAX
    fetch('<?= base_url('Workflow_categories/GetWorkflow/') ?>' + workflowId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const wf = data.workflow;
                
                // Remplir les champs
                document.getElementById('edit_id_workflow').value = wf.id_workflow;
                document.getElementById('edit_id_categorie').value = wf.id_categorie;
                document.getElementById('edit_etape_ordre').value = wf.etape_ordre;
                document.getElementById('edit_nom_etape').value = wf.nom_etape;
                document.getElementById('edit_type_etape').value = wf.type_etape;
                document.getElementById('edit_responsable_role').value = wf.responsable_role || '';
                document.getElementById('edit_delai_heures').value = wf.delai_heures || '';
                document.getElementById('edit_icone_etape').value = wf.icone_etape;
                document.getElementById('edit_couleur_etape').value = wf.couleur_etape;
                
                // Checkboxes
                document.getElementById('edit_est_obligatoire').checked = wf.est_obligatoire == 1;
                document.getElementById('edit_notification_email').checked = wf.notification_email == 1;
                document.getElementById('edit_est_active').checked = wf.est_active == 1;
                
                // Ouvrir le modal
                const modal = new bootstrap.Modal(document.getElementById('edit_workflow'));
                modal.show();
                
                // Initialiser TinyMCE après ouverture du modal
                setTimeout(() => {
                    initTinyMCE('edit_description');
                    // Mettre le contenu après initialisation
                    setTimeout(() => {
                        const editor = tinymce.get('edit_description');
                        if (editor && wf.description_etape) {
                            editor.setContent(wf.description_etape);
                        }
                    }, 300);
                }, 200);
            } else {
                alert('Erreur lors du chargement des données');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur de connexion');
        });
}

// Gestion soumission formulaires
document.getElementById('formCreate').addEventListener('submit', function(e) {
    // TinyMCE met à jour automatiquement le textarea
    const content = tinymce.get('create_description').getContent();
    document.getElementById('create_description').value = content;
});

document.getElementById('formEdit').addEventListener('submit', function(e) {
    const content = tinymce.get('edit_description').getContent();
    document.getElementById('edit_description').value = content;
});

function prefillCategory(catId) {
    $('#create_id_categorie').val(catId);
    $('#create_workflow').modal('show');
}
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>