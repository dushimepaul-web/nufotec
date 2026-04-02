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
             <a class="btn btn-primary" href="<?= base_url('advertise_product/promo') ?>">
                <i class="bx bx-plus"></i> PROMO
            </a>
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_product">
                <i class="bx bx-plus"></i> Nouveau Produit
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
                <h5 class="mb-0 text-primary"><i class="bx bx-package me-2"></i>Liste des Produits</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="productsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Image</th>
                            <th width="20%">Titre</th>
                            <th width="15%">Catégorie</th>
                            <th width="25%">Description</th>
                            <th width="10%">Prix</th>
                            <th width="8%">Statut</th>
                            <th width="8%">Vedette</th>
                            <th width="9%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($products)): $i = 1; foreach ($products as $value): 
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
                    ?>
                         <tr>
                            <td><?= $i++ ?></td>
                            <td class="text-center">
                                <img src="<?= base_url($image_path) ?>" 
                                     class="rounded border"
                                     style="width:60px; height:60px; object-fit:cover;"
                                     onerror="this.src='<?= base_url('attachments/Products/default-product.png') ?>'"
                                     alt="Product">
                            </td>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($value['title'] ?? '') ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $category_name ?></span>
                            </td>
                            <td>
                                <div class="text-muted" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars(substr($value['description'] ?? '', 0, 80)) ?>...
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-success fs-6"><?= htmlspecialchars($value['price'] ?? '') ?></span>
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
                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#featured_<?= $value['id'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['in_vedette']) && $value['in_vedette'] == 1): ?>
                                        <span class="badge bg-warning text-dark"><i class="bx bxs-star"></i> Vedette</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="bx bx-star"></i> Normal</span>
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

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-package me-2"></i>Détails du produit</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-5 text-center border-end">
                                                <img src="<?= base_url($image_path) ?>" 
                                                     class="rounded border border-3 border-primary mb-3"
                                                     style="width:200px; height:200px; object-fit:cover;"
                                                     onerror="this.src='<?= base_url('attachments/Products/default-product.png') ?>'"
                                                     alt="Product">
                                                <h5 class="mb-2"><?= htmlspecialchars($value['title'] ?? '') ?></h5>
                                                <span class="badge bg-success fs-5"><?= htmlspecialchars($value['price'] ?? '') ?></span>
                                                <div class="mt-2">
                                                    <?php if (!empty($value['is_active']) && $value['is_active'] == 1): ?>
                                                        <span class="badge bg-success">Actif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactif</span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($value['in_vedette']) && $value['in_vedette'] == 1): ?>
                                                        <span class="badge bg-warning text-dark">En vedette</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="text-muted small fw-bold">Catégorie</label>
                                                        <p class="mb-0 fw-bold"><?= $category_name ?></p>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="text-muted small fw-bold">Description</label>
                                                        <p class="mb-0 mt-1 p-2 bg-light rounded"><?= nl2br(htmlspecialchars($value['description'] ?? '')) ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">ID Produit</label>
                                                        <p class="mb-0 fw-bold">#<?= $value['id'] ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Slug</label>
                                                        <p class="mb-0 text-muted"><?= htmlspecialchars($value['slug'] ?? '-') ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Créé le</label>
                                                        <p class="mb-0 fw-bold"><?= !empty($value['created_at']) ? date('d/m/Y H:i', strtotime($value['created_at'])) : '-' ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Dernière modification</label>
                                                        <p class="mb-0 fw-bold"><?= !empty($value['updated_at']) ? date('d/m/Y H:i', strtotime($value['updated_at'])) : '-' ?></p>
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

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le produit</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('advertise-product-update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Image principale</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-12">
                                                            <div class="mb-2">
                                                                <img src="<?= base_url($image_path) ?>" 
                                                                     id="preview_<?= $value['id'] ?>"
                                                                     class="rounded border"
                                                                     style="width:100px; height:100px; object-fit:cover;">
                                                            </div>
                                                            <input type="file" class="form-control" name="main_image" accept="image/*" onchange="previewImage(this, <?= $value['id'] ?>)">
                                                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WEBP, SVG. Laissez vide pour conserver l'image actuelle.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations générales</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($value['title'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Prix <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="price" value="<?= htmlspecialchars($value['price'] ?? '') ?>" required placeholder="Ex: 99.99€, Gratuit, Sur devis">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Catégorie</label>
                                                            <select class="form-select" name="category_id">
                                                                <option value="">Sélectionner une catégorie...</option>
                                                                <?php foreach ($categories as $cat): ?>
                                                                    <option value="<?= $cat['id'] ?>" <?= ($value['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($cat['name']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" name="description" rows="5" required><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-slider me-2"></i>Paramètres d'affichage</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active_<?= $value['id'] ?>" value="1" <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="is_active_<?= $value['id'] ?>">Produit actif</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch">
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
                                                <i class="bx bx-save me-2"></i>Enregistrer les modifications
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
                                        <p class="text-muted">Vous êtes sur le point de supprimer le produit :</p>
                                        <p class="fw-bold text-danger"><?= htmlspecialchars($value['title'] ?? '') ?></p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('advertise-product-delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
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

                        <!-- MODAL STATUS (Actif/Inactif) -->
                        <div class="modal fade" id="status_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? '<i class="bx bx-block me-2"></i>Désactiver' : '<i class="bx bx-check-circle me-2"></i>Activer' ?> le produit
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'désactiver' : 'activer' ?></strong> le produit <strong><?= htmlspecialchars($value['title'] ?? '') ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('advertise-product-change-status') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="is_active" value="<?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 1 : 0 ?>">
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

                        <!-- MODAL FEATURED (Vedette) -->
                        <div class="modal fade" id="featured_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 'bg-secondary' : 'bg-warning' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? '<i class="bx bx-star me-2"></i>Retirer de la vedette' : '<i class="bx bxs-star me-2"></i>Mettre en vedette' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 'retirer de la vedette' : 'mettre en vedette' ?></strong> le produit <strong><?= htmlspecialchars($value['title'] ?? '') ?></strong> ?</p>
                                        <?php if (empty($value['in_vedette']) || $value['in_vedette'] == 0): ?>
                                            <p class="text-info small">Les produits en vedette apparaîtront en priorité sur la page d'accueil.</p>
                                        <?php endif; ?>
                                    </div>
                                    <form action="<?= base_url('advertise-product-change-featured') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="in_vedette" value="<?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 1 : 0 ?>">
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

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bx bx-package text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun produit trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- MODAL CREATE PRODUCT -->
<div class="modal fade" id="create_product" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouveau Produit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('advertise-product-create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Image principale</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="mb-2">
                                        <img id="create_preview" 
                                             src="<?= base_url('attachments/Products/default-product.png') ?>" 
                                             class="rounded border"
                                             style="width:100px; height:100px; object-fit:cover;">
                                    </div>
                                    <input type="file" class="form-control" name="main_image" accept="image/*" required onchange="previewCreateImage(this)">
                                    <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WEBP, SVG</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations générales</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
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
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="description" rows="5" required></textarea>
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

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#productsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [1, 6, 7, 8] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Preview image pour la modification
function previewImage(input, id) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#preview_' + id).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Preview image pour la création
function previewCreateImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#create_preview').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>