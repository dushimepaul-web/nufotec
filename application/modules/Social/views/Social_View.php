<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Configuration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Liens Sociaux</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_social">
                <i class="bx bx-plus"></i> Nouveau Lien
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
                <h5 class="mb-0 text-primary"><i class="bx bx-share-alt me-2"></i>Gestion des Liens Sociaux</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="socialTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="8%">Ordre</th>
                            <th width="15%">Plateforme</th>
                            <th width="15%">Label</th>
                            <th width="25%">URL</th>
                            <th width="10%">Icône</th>
                            <th width="8%">Statut</th>
                            <th width="14%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($social_links)): $i = 1; foreach ($social_links as $value): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <span class="badge bg-light text-dark"><?= $value['display_order'] ?></span>
                            </td>
                            <td>
                                <strong class="text-dark text-uppercase"><?= htmlspecialchars($value['platform']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($value['label']) ?></td>
                            <td>
                                <a href="<?= htmlspecialchars($value['url']) ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">
                                    <?= htmlspecialchars($value['url']) ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <i class="bi bi-<?= $value['icon_name'] ?>" style="font-size: 1.5rem; color: #0f4c3a;"></i>
                                <small class="d-block text-muted"><?= $value['icon_name'] ?></small>
                            </td>
                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['is_active']) && $value['is_active'] == 1): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactif</span>
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
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir
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

                        <!-- MODAL VIEW -->
                        <div class="modal fade" id="view_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-show me-2"></i>Détails du lien</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-<?= $value['icon_name'] ?>" style="font-size: 3rem; color: #0f4c3a;"></i>
                                            <h4 class="mt-2"><?= htmlspecialchars($value['label']) ?></h4>
                                        </div>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td class="text-muted">Plateforme:</td>
                                                <td class="fw-bold text-uppercase"><?= htmlspecialchars($value['platform']) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">URL:</td>
                                                <td>
                                                    <a href="<?= htmlspecialchars($value['url']) ?>" target="_blank">
                                                        <?= htmlspecialchars($value['url']) ?> <i class="bx bx-external-link"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Ordre d'affichage:</td>
                                                <td><?= $value['display_order'] ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Nouvel onglet:</td>
                                                <td><?= $value['target_blank'] ? 'Oui' : 'Non' ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Statut:</td>
                                                <td>
                                                    <?php if ($value['is_active']): ?>
                                                        <span class="badge bg-success">Actif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactif</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Créé le:</td>
                                                <td><?= date('d/m/Y H:i', strtotime($value['created_at'])) ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le lien</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('social-update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Plateforme <small class="text-muted">(non modifiable)</small></label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($value['platform']) ?>" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Label <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="label" value="<?= htmlspecialchars($value['label']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">URL <span class="text-danger">*</span></label>
                                                <input type="url" class="form-control" name="url" value="<?= htmlspecialchars($value['url']) ?>" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Nom de l'icône <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="icon_name" value="<?= $value['icon_name'] ?>" required>
                                                    <small class="text-muted">Ex: linkedin, facebook, twitter-x</small>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Ordre d'affichage</label>
                                                    <input type="number" class="form-control" name="display_order" value="<?= $value['display_order'] ?>" min="0">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" name="is_active" id="is_active_upd_<?= $value['id'] ?>" value="1" <?= $value['is_active'] ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="is_active_upd_<?= $value['id'] ?>">Actif</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" name="target_blank" id="target_blank_upd_<?= $value['id'] ?>" value="1" <?= $value['target_blank'] ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="target_blank_upd_<?= $value['id'] ?>">Nouvel onglet</label>
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

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                                        <h5 class="mt-3">Supprimer <?= htmlspecialchars($value['label']) ?> ?</h5>
                                    </div>
                                    <form action="<?= base_url('social-delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
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
                        <div class="modal fade" id="status_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= $value['is_active'] ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= $value['is_active'] ? '<i class="bx bx-block me-2"></i>Désactiver' : '<i class="bx bx-check-circle me-2"></i>Activer' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <?= $value['is_active'] ? 'désactiver' : 'activer' ?> <strong><?= htmlspecialchars($value['label']) ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('social/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="is_active" value="<?= $value['is_active'] ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= $value['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                                                <?= $value['is_active'] ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-share-alt text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun lien social configuré</p>
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

<!-- MODAL CREATE -->
<div class="modal fade" id="create_social" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouveau Lien Social</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('social-create') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Plateforme <span class="text-danger">*</span> <small class="text-muted">(unique, minuscules)</small></label>
                        <input type="text" class="form-control" name="platform" placeholder="ex: linkedin, twitter, facebook" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="label" placeholder="ex: LinkedIn" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" name="url" placeholder="https://..." required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Icône Bootstrap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="icon_name" value="linkedin" required>
                            <small class="text-muted">ex: linkedin, facebook, twitter-x, instagram, youtube</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ordre</label>
                            <input type="number" class="form-control" name="display_order" value="0" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="is_active" id="create_is_active" value="1" checked>
                                <label class="form-check-label" for="create_is_active">Actif</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="target_blank" id="create_target_blank" value="1" checked>
                                <label class="form-check-label" for="create_target_blank">Nouvel onglet</label>
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
    $('#socialTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[1, 'asc']],
        pageLength: 10,
        responsive: true
    });
    
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>