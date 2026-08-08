<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Catalogue</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Catégories de produits</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_category">
                <i class="bx bx-plus"></i> Nouvelle Catégorie
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
                <h5 class="mb-0 text-primary"><i class="bx bx-category me-2"></i>Liste des Catégories</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="categoriesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="70%">Nom de la catégorie</th>
                            <th width="25%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $modals_html = ''; ?>
                    <?php if (!empty($product_categories)): $i = 1; foreach ($product_categories as $value): ?>
                         <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($value['name']) ?></strong>
                            </td>
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

                        <?php ob_start(); ?>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-category me-2"></i>Détails de la catégorie</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-12">
                                                <label class="text-muted small">ID</label>
                                                <p class="mb-3 fw-bold">#<?= $value['id'] ?></p>
                                                
                                                <label class="text-muted small">Nom de la catégorie</label>
                                                <p class="mb-0 fw-bold"><?= htmlspecialchars($value['name']) ?></p>
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
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la catégorie</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('product-categories/update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label fw-bold">Nom de la catégorie <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($value['name']) ?>" required>
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
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer la catégorie <strong><?= htmlspecialchars($value['name']) ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('product-categories/delete') ?>" method="POST">
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

                            <?php $modals_html .= ob_get_clean(); ?>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="bx bx-category text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune catégorie trouvée</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <?= $modals_html ?>
            </div>
        </div>
    </div>

</div>


<!-- MODAL CREATE CATEGORY -->
<div class="modal fade" id="create_category" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouvelle Catégorie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('product-categories/create') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Nom de la catégorie <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="Ex: Électroménager, Vêtements, etc.">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer la catégorie
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#categoriesTable').DataTable({
        language: {
            url: '<?= base_url("assets/backend/plugins/datatable/js/fr-FR.json") ?>'
        },
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [2] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>