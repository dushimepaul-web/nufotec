<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Admin</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Categories') ?>">Catégories</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Détail</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-outline-secondary" href="<?= base_url('Categories') ?>">
                <i class="bx bx-arrow-back"></i> Retour
            </a>
        </div>
    </div>

    <hr/>

    <?php if (empty($detail)): ?>
        <div class="alert alert-warning" role="alert">
            <strong>Introuvable!</strong> Cette catégorie n'existe pas ou a été supprimée.
        </div>
    <?php else: ?>
    <div class="card">
        <div class="card-header py-3">
            <h5 class="mb-0 text-primary"><i class="bx bx-category me-2"></i>Détail de la Catégorie</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Code:</strong></div>
                <div class="col-md-8"><code><?= $detail['code_categorie'] ?? '-' ?></code></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Nom:</strong></div>
                <div class="col-md-8"><?= $detail['nom_categorie'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Slug:</strong></div>
                <div class="col-md-8"><code><?= $detail['slug'] ?? '-' ?></code></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Icône:</strong></div>
                <div class="col-md-8"><?= $detail['icone'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Description courte:</strong></div>
                <div class="col-md-8"><?= nl2br($detail['description_courte'] ?? '-') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Description longue:</strong></div>
                <div class="col-md-8"><?= nl2br($detail['description_longue'] ?? '-') ?></div>
            </div>
            <?php if (!empty($detail['image_url'])): ?>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Image:</strong></div>
                <div class="col-md-8">
                    <img src="<?= base_url($detail['image_url']) ?>" alt="<?= $detail['nom_categorie'] ?? '' ?>" class="img-thumbnail" style="max-height:120px;">
                </div>
            </div>
            <?php endif; ?>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Statut:</strong></div>
                <div class="col-md-8">
                    <span class="badge bg-<?= !empty($detail['is_active']) ? 'success' : 'secondary' ?>">
                        <?= !empty($detail['is_active']) ? 'Actif' : 'Inactif' ?>
                    </span>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Créé le:</strong></div>
                <div class="col-md-8"><?= !empty($detail['created_at']) ? date('d/m/Y H:i:s', strtotime($detail['created_at'])) : '-' ?></div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="<?= base_url('Categories') ?>" class="btn btn-secondary">Fermer</a>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>
