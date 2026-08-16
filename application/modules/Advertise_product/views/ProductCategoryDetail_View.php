<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Catalogue</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('product-categories') ?>">Catégories de produits</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Détail</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-outline-secondary" href="<?= base_url('product-categories') ?>">
                <i class="bx bx-arrow-back"></i> Retour
            </a>
        </div>
    </div>

    <hr/>

    <?php if (empty($detail)): ?>
        <div class="alert alert-warning" role="alert">
            <strong>Introuvable!</strong> Cette catégorie de produits n'existe pas ou a été supprimée.
        </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-primary"><i class="bx bx-category me-2"></i>Détail de la Catégorie</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Nom:</strong></div>
                <div class="col-md-8"><?= $detail['name'] ?? '-' ?></div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="<?= base_url('product-categories') ?>" class="btn btn-secondary">Fermer</a>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>