<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <?php if (!empty($detail)): ?>
    <?php
        $image_path = !empty($detail['main_image']) ? 'attachments/Products/'.$detail['main_image'] : 'attachments/Products/default-product.png';
        $category_name = '-';
        if (!empty($detail['category_id']) && !empty($categories)) {
            foreach ($categories as $cat) {
                if ($cat['id'] == $detail['category_id']) {
                    $category_name = htmlspecialchars($cat['name']);
                    break;
                }
            }
        }
    ?>

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Administration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('advertise-product') ?>">Gestion des Produits</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Détails du produit</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-secondary" href="<?= base_url('advertise-product') ?>">
                <i class="bx bx-arrow-back me-1"></i>Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <img src="<?= base_url($image_path) ?>" 
                         class="rounded border border-3 mb-3"
                         style="width:220px; height:220px; object-fit:cover;"
                         onerror="this.src='<?= base_url('attachments/Products/default-product.png') ?>'"
                         alt="<?= htmlspecialchars($detail['title'] ?? '') ?>">
                    <h4 class="mb-1"><?= htmlspecialchars($detail['title'] ?? '') ?></h4>
                    <span class="badge text-bg-success fs-6"><?= htmlspecialchars($detail['price'] ?? '') ?></span>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 text-primary"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">ID</label>
                        <span>#<?= $detail['id'] ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">Catégorie</label>
                        <span class="badge text-bg-info"><?= $category_name ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">Slug</label>
                        <span>/<?= htmlspecialchars($detail['slug'] ?? '') ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">Prix</label>
                        <span class="badge text-bg-success"><?= htmlspecialchars($detail['price'] ?? '') ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">Statut</label>
                        <?php if (!empty($detail['is_active']) && $detail['is_active'] == 1): ?>
                            <span class="badge text-bg-success">Actif</span>
                        <?php else: ?>
                            <span class="badge text-bg-danger">Inactif</span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block">En vedette</label>
                        <?php if (!empty($detail['in_vedette']) && $detail['in_vedette'] == 1): ?>
                            <span class="badge text-bg-warning"><i class="bx bxs-star"></i>Vedette</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Normal</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 text-primary"><i class="bx bx-detail me-2"></i>Description</h6>
                </div>
                <div class="card-body">
                    <div class="p-2 bg-light rounded"><?= nl2br(htmlspecialchars($detail['description'] ?? '')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bx bx-package text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">Produit introuvable</h5>
                <a class="btn btn-primary mt-2" href="<?= base_url('advertise-product') ?>">
                    <i class="bx bx-arrow-back me-1"></i>Retour aux produits
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>