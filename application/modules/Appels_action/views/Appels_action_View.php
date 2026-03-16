<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Marketing</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Appels à l'Action (CTA)</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_cta">
                <i class="bx bx-plus"></i> Nouveau CTA
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
                <h5 class="mb-0 text-primary"><i class="bx bx-bullseye me-2"></i>Liste des Appels à l'Action</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="ctaTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">Ordre</th>
                            <th width="20%">Titre</th>
                            <th width="15%">Public cible</th>
                            <th width="15%">Bouton</th>
                            <th width="10%">Page</th>
                            <th width="10%">Expiration</th>
                            <th width="8%">Statut</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($ctas)): foreach ($ctas as $value): 
                        // Récupérer le nom de la page associée
                        $page_name = 'Toutes';
                        if (!empty($value['id_page_associee'])) {
                            foreach ($pages as $page) {
                                if ($page['id_page'] == $value['id_page_associee']) {
                                    $page_name = $page['titre_page'];
                                    break;
                                }
                            }
                        }
                        
                        // Badge type de public
                        $public_badges = [
                            'investisseurs' => '<span class="badge bg-success"><i class="bx bx-money"></i> Investisseurs</span>',
                            'donateurs' => '<span class="badge bg-info"><i class="bx bx-heart"></i> Donateurs</span>',
                            'courtiers' => '<span class="badge bg-warning text-dark"><i class="bx bx-transfer"></i> Courtiers</span>',
                            'acheteurs' => '<span class="badge bg-primary"><i class="bx bx-cart"></i> Acheteurs</span>',
                            'patients' => '<span class="badge bg-danger"><i class="bx bx-plus-medical"></i> Patients</span>',
                            'tous' => '<span class="badge bg-secondary"><i class="bx bx-world"></i> Tous</span>'
                        ];
                        
                        $public_badge = $public_badges[$value['type_public'] ?? 'tous'] ?? '<span class="badge bg-secondary">Tous</span>';
                        
                        // Vérifier expiration
                        $is_expired = (!empty($value['date_expiration']) && strtotime($value['date_expiration']) < time());
                        $expiration_badge = '';
                        if ($is_expired) {
                            $expiration_badge = '<span class="badge bg-danger ms-1"><i class="bx bx-time"></i> Expiré</span>';
                        }
                    ?>
                        <tr class="<?= $is_expired ? 'table-danger' : '' ?>">
                            <td><span class="badge bg-light text-dark border"><?= $value['ordre'] ?? 0 ?></span></td>
                            
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['titre'] ?? '') ?></strong>
                                    <?php if (!empty($value['sous_titre'])): ?>
                                        <small class="text-muted text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars(substr($value['sous_titre'], 0, 50)) ?>...
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td><?= $public_badge ?></td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="badge bg-primary"><?= htmlspecialchars($value['bouton_texte'] ?? '') ?></span>
                                    <small class="text-muted"><?= htmlspecialchars($value['bouton_lien'] ?? '') ?></small>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bx bx-file me-1"></i><?= htmlspecialchars($page_name) ?>
                                </span>
                            </td>

                            <td>
                                <?= !empty($value['date_expiration']) ? date('d/m/Y', strtotime($value['date_expiration'])) : '<span class="text-muted">-</span>' ?>
                                <?= $expiration_badge ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_cta'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_actif']) && $value['est_actif'] == 1 && !$is_expired): ?>
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
                                            <a class="dropdown-item" href="<?= base_url('Appels_action/CtaDetail/'.$value['id_cta']) ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_cta'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_cta'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id_cta'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'appel à l'action</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Appels_action/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_cta" value="<?= $value['id_cta'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($value['titre'] ?? '') ?>" required>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Ordre</label>
                                                    <input type="number" class="form-control" name="ordre" value="<?= $value['ordre'] ?? 0 ?>" min="0">
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Sous-titre</label>
                                                    <textarea class="form-control" name="sous_titre" rows="2" placeholder="Description courte..."><?= htmlspecialchars($value['sous_titre'] ?? '') ?></textarea>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Texte du bouton <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="bouton_texte" value="<?= htmlspecialchars($value['bouton_texte'] ?? '') ?>" required>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Lien du bouton <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="bouton_lien" value="<?= htmlspecialchars($value['bouton_lien'] ?? '') ?>" required placeholder="/page-url">
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Type de public <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="type_public" required>
                                                        <option value="tous" <?= ($value['type_public'] ?? '') == 'tous' ? 'selected' : '' ?>>Tous</option>
                                                        <option value="investisseurs" <?= ($value['type_public'] ?? '') == 'investisseurs' ? 'selected' : '' ?>>Investisseurs</option>
                                                        <option value="donateurs" <?= ($value['type_public'] ?? '') == 'donateurs' ? 'selected' : '' ?>>Donateurs</option>
                                                        <option value="courtiers" <?= ($value['type_public'] ?? '') == 'courtiers' ? 'selected' : '' ?>>Courtiers</option>
                                                        <option value="acheteurs" <?= ($value['type_public'] ?? '') == 'acheteurs' ? 'selected' : '' ?>>Acheteurs</option>
                                                        <option value="patients" <?= ($value['type_public'] ?? '') == 'patients' ? 'selected' : '' ?>>Patients</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Page associée</label>
                                                    <select class="form-select" name="id_page_associee">
                                                        <option value="">Toutes les pages</option>
                                                        <?php foreach ($pages as $page): ?>
                                                            <option value="<?= $page['id_page'] ?>" <?= ($value['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($page['titre_page']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Image de fond</label>
                                                    <div class="input-group mb-2">
                                                        <span class="input-group-text"><i class="bx bx-image"></i></span>
                                                        <input type="text" class="form-control" name="image_fond_url" value="<?= htmlspecialchars($value['image_fond_url'] ?? '') ?>" placeholder="/images/cta-bg.jpg">
                                                    </div>
                                                    <input type="file" class="form-control" name="image_fond" accept="image/*">
                                                    <?php if (!empty($value['image_fond_url'])): ?>
                                                        <small class="text-muted">Actuelle: <?= basename($value['image_fond_url']) ?></small>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Date d'expiration</label>
                                                    <input type="date" class="form-control" name="date_expiration" value="<?= $value['date_expiration'] ?? '' ?>">
                                                    <small class="text-muted">Laisser vide pour aucune expiration</small>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" name="est_actif" id="est_actif_<?= $value['id_cta'] ?>" value="1" <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="est_actif_<?= $value['id_cta'] ?>">CTA actif</label>
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
                        <div class="modal fade" id="delete_<?= $value['id_cta'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer le CTA <strong><?= htmlspecialchars($value['titre'] ?? '') ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Appels_action/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_cta'] ?>">
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

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $value['id_cta'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? '<i class="bx bx-block me-2"></i>Désactiver' : '<i class="bx bx-check-circle me-2"></i>Activer' ?> le CTA
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'désactiver' : 'activer' ?></strong> le CTA <strong><?= htmlspecialchars($value['titre'] ?? '') ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('Appels_action/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_cta'] ?>">
                                        <input type="hidden" name="est_actif" value="<?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'btn-warning' : 'btn-success' ?>">
                                                <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-bullseye text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun appel à l'action trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- MODAL CREATE CTA -->
<div class="modal fade" id="create_cta" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvel Appel à l'Action</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Appels_action/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="titre" required placeholder="Ex: Investors & Donors">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ordre</label>
                            <input type="number" class="form-control" name="ordre" value="0" min="0">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Sous-titre</label>
                            <textarea class="form-control" name="sous_titre" rows="2" placeholder="Description courte du CTA..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Texte du bouton <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="bouton_texte" required placeholder="Ex: Submit MOI">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lien du bouton <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="bouton_lien" required placeholder="/investors/moi">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Type de public <span class="text-danger">*</span></label>
                            <select class="form-select" name="type_public" required>
                                <option value="tous" selected>Tous</option>
                                <option value="investisseurs">Investisseurs</option>
                                <option value="donateurs">Donateurs</option>
                                <option value="courtiers">Courtiers</option>
                                <option value="acheteurs">Acheteurs</option>
                                <option value="patients">Patients</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Page associée</label>
                            <select class="form-select" name="id_page_associee">
                                <option value="">Toutes les pages</option>
                                <?php foreach ($pages as $page): ?>
                                    <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Image de fond</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="bx bx-image"></i></span>
                                <input type="text" class="form-control" name="image_fond_url" placeholder="/images/cta-bg.jpg">
                            </div>
                            <input type="file" class="form-control" name="image_fond" accept="image/*">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date d'expiration</label>
                            <input type="date" class="form-control" name="date_expiration">
                            <small class="text-muted">Laisser vide pour aucune expiration</small>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="est_actif" id="create_est_actif" value="1" checked>
                                <label class="form-check-label" for="create_est_actif">CTA actif immédiatement</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer le CTA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#ctaTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [7] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>