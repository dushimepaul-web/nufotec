<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <?php if (!empty($detail)): ?>

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Catalogue</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('product-categories') ?>">Catégories de produits</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Détails de la catégorie</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-secondary" href="<?= base_url('product-categories') ?>">
                <i class="bx bx-arrow-back me-1"></i>Retour
            </a>
            <a class="btn btn-warning" href="javascript:;" data-bs-toggle="modal" data-bs-target="#update_<?= $detail['id'] ?>">
                <i class="bx bx-edit me-1"></i>Modifier
            </a>
            <a class="btn btn-danger" href="javascript:;" data-bs-toggle="modal" data-bs-target="#delete_<?= $detail['id'] ?>">
                <i class="bx bx-trash me-1"></i>Supprimer
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-primary"><i class="bx bx-category me-2"></i>Détails de la catégorie</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="text-muted small fw-bold">ID</label>
                    <p class="fw-bold">#<?= $detail['id'] ?></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold">Nom de la catégorie</label>
                    <p class="fw-bold"><?= htmlspecialchars($detail['name']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL UPDATE -->
    <div class="modal fade" id="update_<?= $detail['id'] ?>" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= base_url('product-categories/update') ?>" method="POST">
                    <input type="hidden" name="id" value="<?= $detail['id'] ?>">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nom de la catégorie <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($detail['name']) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning"><i class="bx bx-save me-2"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DELETE -->
    <div class="modal fade" id="delete_<?= $detail['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Êtes-vous sûr ?</h5>
                    <p class="text-muted">Vous allez supprimer la catégorie <strong><?= htmlspecialchars($detail['name']) ?></strong>.</p>
                    <p class="text-danger small">Cette action est irréversible.</p>
                </div>
                <form action="<?= base_url('product-categories/delete') ?>" method="POST">
                    <input type="hidden" name="id" value="<?= $detail['id'] ?>">
                    <div class="modal-footer bg-light justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger"><i class="bx bx-trash me-2"></i>Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bx bx-category text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">Catégorie introuvable</h5>
                <a class="btn btn-primary mt-2" href="<?= base_url('product-categories') ?>">
                    <i class="bx bx-arrow-back me-1"></i>Retour aux catégories
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>