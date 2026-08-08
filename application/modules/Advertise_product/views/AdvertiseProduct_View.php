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
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Produits</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bx bx-flag"></i> Langue
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="?lang=fr">🇫🇷 Français</a></li>
                    <li><a class="dropdown-item" href="?lang=en">🇬🇧 English</a></li>
                    <li><a class="dropdown-item" href="?lang=sw">🇹🇿 Kiswahili</a></li>
                </ul>
            </div>
            <a class="btn btn-warning me-2" href="<?= base_url('advertise-product/promo') ?>">
                <i class="bx bx-send"></i> Envoyer PROMO
            </a>
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_product">
                <i class="bx bx-plus"></i> Nouveau Produit
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="card-title mb-0"><i class="bx bx-package me-2"></i>Liste des Produits</h5>
                <span class="badge text-bg-info"><?= count($products) ?> produit(s)</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="productsTable" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">#</th>
                            <th width="8%">Image</th>
                            <th width="25%">Titre</th>
                            <th width="15%">Catégorie</th>
                            <th width="10%">Prix</th>
                            <th width="8%">Statut</th>
                            <th width="10%">Vedette</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $modals_html = '';
                    if (!empty($products)): 
                        $i = 1; 
                        foreach ($products as $value): 
                            $image_path = !empty($value['main_image']) ? 'attachments/Products/'.$value['main_image'] : 'attachments/Products/default-product.png';
                            
                            // Récupérer le nom de la catégorie
                            $category_name = '-';
                            if (!empty($value['category_id']) && !empty($categories)) {
                                foreach ($categories as $cat) {
                                    if ($cat['id'] == $value['category_id']) {
                                        $category_name = htmlspecialchars($cat['name']);
                                        break;
                                    }
                                }
                            }
                            
                            // Récupérer le titre
                            $title = htmlspecialchars($value['title'] ?? '');
                    ?>
                         <tr>
                            <td><?= $i++ ?></td>
                            <td class="text-center">
                                <img src="<?= base_url($image_path) ?>" 
                                     class="rounded border"
                                     style="width:60px; height:60px; object-fit:cover; cursor: pointer;"
                                     onclick="previewImageModal('<?= base_url($image_path) ?>')"
                                     onerror="this.src='<?= base_url('attachments/Products/default-product.png') ?>'"
                                     alt="Product">
                            </td>
                            <td>
                                <strong class="text-dark"><?= $title ?></strong>
                                <?php if (!empty($value['slug'])): ?>
                                    <br><small class="text-muted" style="font-size: 0.65rem;">slug: <?= $value['slug'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-info"><?= $category_name ?></span>
                            </td>
                            <td>
                                <span class="badge text-bg-success fs-6"><?= htmlspecialchars($value['price'] ?? '') ?></span>
                            </td>
                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['is_active']) && $value['is_active'] == 1): ?>
                                        <span class="badge text-bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-danger">Inactif</span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#featured_<?= $value['id'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['in_vedette']) && $value['in_vedette'] == 1): ?>
                                        <span class="badge text-bg-warning"><i class="bx bxs-star"></i> Vedette</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary"><i class="bx bx-star"></i> Normal</span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
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

                        <!-- MODAL VIEW DETAILS (MULTILINGUE) -->
                        <div class="modal fade" id="view_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-package me-2"></i>Détails du produit</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center border-end">
                                                <img src="<?= base_url($image_path) ?>" 
                                                     class="rounded border border-3 border-primary mb-3"
                                                     style="width:200px; height:200px; object-fit:cover;"
                                                     alt="Product">
                                                <h5><?= htmlspecialchars($value['title'] ?? '') ?></h5>
                                                <span class="badge text-bg-success fs-5"><?= htmlspecialchars($value['price'] ?? '') ?></span>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label class="text-muted small fw-bold">Catégorie</label>
                                                    <p><?= $category_name ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small fw-bold">Description</label>
                                                    <div class="p-2 bg-light rounded"><?= nl2br(htmlspecialchars($value['description'] ?? '')) ?></div>
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

                        <!-- MODAL UPDATE (MULTILINGUE) -->
                        <div class="modal fade" id="update_<?= $value['id'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le produit</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('advertise-product/update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <!-- Image -->
                                            <div class="card card-outline card-secondary mb-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0 text-primary"><i class="bx bx-image me-2"></i>Image principale</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-12">
                                                            <div class="mb-2">
                                                                <img src="<?= base_url($image_path) ?>" 
                                                                     id="preview_<?= $value['id'] ?>"
                                                                     class="rounded border"
                                                                     style="width:100px; height:100px; object-fit:cover;">
                                                            </div>
                                                            <input type="file" class="form-control" name="main_image" accept="image/*" onchange="previewImage(this, <?= $value['id'] ?>)">
                                                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WEBP, SVG</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Informations -->
                                            <div class="card card-outline card-secondary mb-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0 text-primary"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($value['title'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" name="description" rows="5" required><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Paramètres -->
                                            <div class="card card-outline card-secondary">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0 text-primary"><i class="bx bx-slider me-2"></i>Paramètres</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Prix <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="price" value="<?= htmlspecialchars($value['price'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Catégorie</label>
                                                            <select class="form-select" name="category_id">
                                                                <option value="">Sélectionner...</option>
                                                                <?php foreach ($categories as $cat): ?>
                                                                    <option value="<?= $cat['id'] ?>" <?= ($value['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($cat['name']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check form-switch mt-4">
                                                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active_<?= $value['id'] ?>" value="1" <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="is_active_<?= $value['id'] ?>">Produit actif</label>
                                                            </div>
                                                            <div class="form-check form-switch mt-2">
                                                                <input type="checkbox" class="form-check-input" name="in_vedette" id="in_vedette_<?= $value['id'] ?>" value="1" <?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="in_vedette_<?= $value['id'] ?>">Mettre en vedette</label>
                                                            </div>
                                                        </div>
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
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Produit : <strong><?= htmlspecialchars($value['title'] ?? '') ?></strong></p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('advertise-product/delete') ?>" method="POST">
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
                                    <form action="<?= base_url('advertise-product/change-status') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="is_active" value="<?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-header <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                            <h5 class="modal-title">
                                                <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'Désactiver' : 'Activer' ?> le produit
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <p>Voulez-vous vraiment <strong><?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'désactiver' : 'activer' ?></strong> le produit <strong><?= htmlspecialchars($value['title'] ?? '') ?></strong> ?</p>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'btn-warning' : 'btn-success' ?>">
                                                <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL FEATURED -->
                        <div class="modal fade" id="featured_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form action="<?= base_url('advertise-product/change-featured') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="in_vedette" value="<?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-header <?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 'bg-secondary' : 'bg-warning' ?> text-white">
                                            <h5 class="modal-title">
                                                <?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 'Retirer de la vedette' : 'Mettre en vedette' ?>
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <p>Voulez-vous vraiment <strong><?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 'retirer de la vedette' : 'mettre en vedette' ?></strong> le produit <strong><?= htmlspecialchars($value['title'] ?? '') ?></strong> ?</p>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 'btn-secondary' : 'btn-warning' ?>">
                                                <?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 'Retirer' : 'Mettre en vedette' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                            <?php $modals_html .= ob_get_clean(); ?>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-package text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun produit trouvé</p>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#create_product">
                                    <i class="bx bx-plus"></i> Créer un produit
                                </button>
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

<!-- MODAL CREATE PRODUCT (MULTILINGUE) -->
<div class="modal fade" id="create_product" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouveau Produit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('advertise-product/create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <!-- Image -->
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-primary"><i class="bx bx-image me-2"></i>Image principale <span class="text-danger">*</span></h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="mb-2">
                                        <img id="create_preview" src="<?= base_url('attachments/Products/default-product.png') ?>" class="rounded border" style="width:100px; height:100px; object-fit:cover;">
                                    </div>
                                    <input type="file" class="form-control" name="main_image" accept="image/*" required onchange="previewCreateImage(this)">
                                    <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WEBP, SVG</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations -->
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-primary"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="description" rows="5" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paramètres -->
                    <div class="card card-outline card-secondary">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-primary"><i class="bx bx-slider me-2"></i>Paramètres</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Prix <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="price" required placeholder="Ex: 99.99€, Gratuit, Sur devis">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Catégorie</label>
                                    <select class="form-select" name="category_id">
                                        <option value="">Sélectionner une catégorie...</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
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
                        <i class="bx bx-save me-2"></i>Créer le produit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW IMAGE -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Prévisualisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" class="img-fluid rounded" style="max-height: 70vh;" alt="Preview">
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input, id) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#preview_' + id).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function previewCreateImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#create_preview').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function previewImageModal(src) {
    document.getElementById('previewImage').src = src;
    new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
}

$(document).ready(function() {
    $('#productsTable').DataTable({
        language: {
            url: '<?= base_url("assets/backend/plugins/datatable/js/fr-FR.json") ?>'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [1, 7] }
        ]
    });
    
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>