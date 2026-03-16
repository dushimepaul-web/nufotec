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
                    <li class="breadcrumb-item active" aria-current="page">Déclarations (Mission, Vision, Objectifs)</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_statement">
                <i class="bx bx-plus"></i> Nouvelle Déclaration
            </button>
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
                <h5 class="mb-0 text-primary"><i class="bx bx-bullseye me-2"></i>Liste des Déclarations</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="statementsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Type</th>
                            <th width="15%">Titre</th>
                            <th width="35%">Description</th>
                            <th width="8%">Icône</th>
                            <th width="8%">Ordre</th>
                            <th width="8%">Statut</th>
                            <th width="11%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($statements)): $i = 1; foreach ($statements as $st): 
                        // Badge type
                        $type_badges = [
                            'mission' => '<span class="badge bg-primary"><i class="bx bx-target"></i> Mission</span>',
                            'vision' => '<span class="badge bg-info"><i class="bx bx-show"></i> Vision</span>',
                            'objective' => '<span class="badge bg-warning text-dark"><i class="bx bx-trophy"></i> Objectif</span>',
                            'value' => '<span class="badge bg-success"><i class="bx bx-heart"></i> Valeur</span>',
                            'slogan' => '<span class="badge bg-secondary"><i class="bx bx-message"></i> Slogan</span>',
                            'other' => '<span class="badge bg-dark"><i class="bx bx-category"></i> Autre</span>'
                        ];
                        $type_badge = $type_badges[$st['type'] ?? 'other'] ?? '<span class="badge bg-light text-dark">Inconnu</span>';
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $type_badge ?></td>
                            <td><?= htmlspecialchars($st['title'] ?? '-') ?></td>
                            <td><?= word_limiter(strip_tags($st['description'] ?? ''), 15) ?></td>
                            <td class="text-center">
                                <?php if (!empty($st['icon'])): ?>
                                    <i class="bx <?= htmlspecialchars($st['icon']) ?> fs-4 text-primary"></i>
                                    <br><small class="text-muted"><?= htmlspecialchars($st['icon']) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $st['order'] ?? 0 ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm <?= (!empty($st['is_active']) && $st['is_active'] == 1) ? 'btn-success' : 'btn-danger' ?>" data-bs-toggle="modal" data-bs-target="#status_<?= $st['id'] ?>">
                                    <?= (!empty($st['is_active']) && $st['is_active'] == 1) ? 'Actif' : 'Inactif' ?>
                                </button>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#view_<?= $st['id'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#update_<?= $st['id'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#delete_<?= $st['id'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-inbox text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune déclaration trouvée</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div> <!-- FIN page-content -->
</div> <!-- FIN page-wrapper -->

<!-- ============================================
     TOUS LES MODALS PLACÉS ICI, À L'EXTÉRIEUR 
     DES DIVS PRINCIPALES
     ============================================ -->

<?php if (!empty($statements)): foreach ($statements as $st): 
    // Recalculer les badges pour les modals
    $type_badges = [
        'mission' => '<span class="badge bg-primary"><i class="bx bx-target"></i> Mission</span>',
        'vision' => '<span class="badge bg-info"><i class="bx bx-show"></i> Vision</span>',
        'objective' => '<span class="badge bg-warning text-dark"><i class="bx bx-trophy"></i> Objectif</span>',
        'value' => '<span class="badge bg-success"><i class="bx bx-heart"></i> Valeur</span>',
        'slogan' => '<span class="badge bg-secondary"><i class="bx bx-message"></i> Slogan</span>',
        'other' => '<span class="badge bg-dark"><i class="bx bx-category"></i> Autre</span>'
    ];
    $type_badge = $type_badges[$st['type'] ?? 'other'] ?? '<span class="badge bg-light text-dark">Inconnu</span>';
?>

<!-- MODAL VIEW <?= $st['id'] ?> -->
<div class="modal fade" id="view_<?= $st['id'] ?>" tabindex="-1" aria-labelledby="viewLabel_<?= $st['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewLabel_<?= $st['id'] ?>"><i class="bx bx-show me-2"></i>Détails de la déclaration</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <td width="30%"><strong>Type:</strong></td>
                                <td><?= $type_badge ?></td>
                            </tr>
                            <tr>
                                <td><strong>Titre:</strong></td>
                                <td><?= htmlspecialchars($st['title'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Description:</strong></td>
                                <td style="white-space: pre-wrap;"><?= htmlspecialchars($st['description'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Icône:</strong></td>
                                <td>
                                    <?php if (!empty($st['icon'])): ?>
                                        <i class="bx <?= htmlspecialchars($st['icon']) ?> fs-4 me-2 text-primary"></i>
                                        <code><?= htmlspecialchars($st['icon']) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">Aucune icône</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Ordre d'affichage:</strong></td>
                                <td><?= $st['order'] ?? 0 ?></td>
                            </tr>
                            <tr>
                                <td><strong>Statut:</strong></td>
                                <td>
                                    <?php if (!empty($st['is_active']) && $st['is_active'] == 1): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Créé le:</strong></td>
                                <td><?= !empty($st['created_at']) ? date('d/m/Y H:i', strtotime($st['created_at'])) : '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Modifié le:</strong></td>
                                <td><?= !empty($st['updated_at']) ? date('d/m/Y H:i', strtotime($st['updated_at'])) : '-' ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4 text-center border-start">
                        <?php if (!empty($st['icon'])): ?>
                            <div class="p-4 bg-light rounded-3 mb-3">
                                <i class="bx <?= htmlspecialchars($st['icon']) ?> fs-1 text-primary"></i>
                            </div>
                        <?php endif; ?>
                        <h5 class="mb-1 fw-bold"><?= htmlspecialchars($st['title'] ?? 'Sans titre') ?></h5>
                        <p class="text-muted small text-uppercase"><?= ucfirst($st['type'] ?? '-') ?></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL UPDATE <?= $st['id'] ?> -->
<div class="modal fade" id="update_<?= $st['id'] ?>" data-bs-backdrop="static" tabindex="-1" aria-labelledby="updateLabel_<?= $st['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="updateLabel_<?= $st['id'] ?>"><i class="bx bx-edit me-2"></i>Modifier la déclaration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= base_url('Company_statements/Update') ?>" method="POST">
                <input type="hidden" name="id" value="<?= $st['id'] ?>">
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="mission" <?= ($st['type'] ?? '') == 'mission' ? 'selected' : '' ?>>Mission</option>
                                <option value="vision" <?= ($st['type'] ?? '') == 'vision' ? 'selected' : '' ?>>Vision</option>
                                <option value="objective" <?= ($st['type'] ?? '') == 'objective' ? 'selected' : '' ?>>Objectif</option>
                                <option value="value" <?= ($st['type'] ?? '') == 'value' ? 'selected' : '' ?>>Valeur</option>
                                <option value="slogan" <?= ($st['type'] ?? '') == 'slogan' ? 'selected' : '' ?>>Slogan</option>
                                <option value="other" <?= ($st['type'] ?? '') == 'other' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Titre</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($st['title'] ?? '') ?>" placeholder="Titre optionnel">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="5" required placeholder="Contenu de la déclaration..."><?= htmlspecialchars($st['description'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Icône (classe Boxicons)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx <?= htmlspecialchars($st['icon'] ?? 'bx-bullseye') ?>"></i></span>
                                <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($st['icon'] ?? 'bx-bullseye') ?>" placeholder="ex: bx-target">
                            </div>
                            <small class="text-muted">Exemple: bx-bullseye, bx-show, bx-heart, bx-trophy</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ordre d'affichage</label>
                            <input type="number" name="order" class="form-control" value="<?= $st['order'] ?? 0 ?>" min="0">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active_<?= $st['id'] ?>" value="1" <?= (!empty($st['is_active']) && $st['is_active'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active_<?= $st['id'] ?>">Actif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DELETE <?= $st['id'] ?> -->
<div class="modal fade" id="delete_<?= $st['id'] ?>" tabindex="-1" aria-labelledby="deleteLabel_<?= $st['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteLabel_<?= $st['id'] ?>"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Êtes-vous sûr ?</h5>
                <p class="text-muted">Vous êtes sur le point de supprimer <strong><?= htmlspecialchars($st['title'] ?? 'cette déclaration') ?></strong>.</p>
                <p class="text-danger small">Cette action est irréversible.</p>
            </div>
            <form action="<?= base_url('Company_statements/Delete') ?>" method="POST">
                <input type="hidden" name="id" value="<?= $st['id'] ?>">
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

<!-- MODAL STATUS <?= $st['id'] ?> -->
<div class="modal fade" id="status_<?= $st['id'] ?>" tabindex="-1" aria-labelledby="statusLabel_<?= $st['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header <?= (!empty($st['is_active']) && $st['is_active'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                <h5 class="modal-title" id="statusLabel_<?= $st['id'] ?>">
                    <?= (!empty($st['is_active']) && $st['is_active'] == 1) ? '<i class="bx bx-block me-2"></i>Désactiver' : '<i class="bx bx-check-circle me-2"></i>Activer' ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p>Voulez-vous vraiment <strong><?= (!empty($st['is_active']) && $st['is_active'] == 1) ? 'désactiver' : 'activer' ?></strong> cette déclaration ?</p>
                <p class="fw-bold"><?= htmlspecialchars($st['title'] ?? 'Sans titre') ?></p>
            </div>
            <form action="<?= base_url('Company_statements/ChangeStatus') ?>" method="POST">
                <input type="hidden" name="id" value="<?= $st['id'] ?>">
                <input type="hidden" name="is_active" value="<?= (!empty($st['is_active']) && $st['is_active'] == 1) ? 1 : 0 ?>">
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn <?= (!empty($st['is_active']) && $st['is_active'] == 1) ? 'btn-warning' : 'btn-success' ?>">
                        <?= (!empty($st['is_active']) && $st['is_active'] == 1) ? 'Désactiver' : 'Activer' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endforeach; endif; ?>

<!-- MODAL CREATE -->
<div class="modal fade" id="create_statement" data-bs-backdrop="static" tabindex="-1" aria-labelledby="createLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createLabel"><i class="bx bx-plus me-2"></i>Nouvelle Déclaration</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= base_url('Company_statements/Create') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                <option value="mission">Mission</option>
                                <option value="vision">Vision</option>
                                <option value="objective">Objectif</option>
                                <option value="value">Valeur</option>
                                <option value="slogan">Slogan</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Titre</label>
                            <input type="text" name="title" class="form-control" placeholder="Titre optionnel">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="5" required placeholder="Contenu de la déclaration..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Icône (classe Boxicons)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-bullseye"></i></span>
                                <input type="text" name="icon" class="form-control" value="bx-bullseye" placeholder="ex: bx-target">
                            </div>
                            <small class="text-muted">Exemple: bx-bullseye, bx-show, bx-heart, bx-trophy</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ordre d'affichage</label>
                            <input type="number" name="order" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="is_active" id="create_is_active" value="1" checked>
                                <label class="form-check-label" for="create_is_active">Actif immédiatement</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#statementsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[5, 'asc']], // tri par ordre
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [3, 7] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>