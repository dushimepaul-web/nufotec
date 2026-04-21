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
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <h5 class="mb-0 text-primary"><i class="bx bx-package me-2"></i>Liste des Produits</h5>
                <div class="mt-2 mt-sm-0">
                    <span class="badge bg-info"><?= count($products) ?> produit(s)</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="productsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">#</th>
                            <th width="8%">Image</th>
                            <th width="15%">Titre (FR)</th>
                            <th width="12%">Titre (EN)</th>
                            <th width="12%">Titre (SW)</th>
                            <th width="10%">Catégorie</th>
                            <th width="8%">Prix</th>
                            <th width="5%">Statut</th>
                            <th width="7%">Vedette</th>
                            <th width="8%">Traductions</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $current_lang = $this->input->get('lang') ?: 'fr';
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
                            
                            // Vérifier les traductions disponibles
                            $has_fr = !empty($value['title_fr']) || !empty($value['description_fr']);
                            $has_en = !empty($value['title_en']) || !empty($value['description_en']);
                            $has_sw = !empty($value['title_sw']) || !empty($value['description_sw']);
                            
                            // Récupérer les titres par langue
                            $title_fr = htmlspecialchars($value['title_fr'] ?: $value['title']);
                            $title_en = htmlspecialchars($value['title_en'] ?: '-');
                            $title_sw = htmlspecialchars($value['title_sw'] ?: '-');
                    ?>
                         <tr>
                            <td><?= $i++ ?></td>
                            <td class="text-center">
                                <img src="<?= base_url($image_path) ?>" 
                                     class="rounded border"
                                     style="width:60px; height:60px; object-fit:cover; cursor: pointer;"
                                     onclick="previewImage('<?= base_url($image_path) ?>')"
                                     onerror="this.src='<?= base_url('attachments/Products/default-product.png') ?>'"
                                     alt="Product">
                            </td>
                            <td>
                                <strong class="text-dark"><?= $title_fr ?></strong>
                                <?php if (!empty($value['slug'])): ?>
                                    <br><small class="text-muted" style="font-size: 0.65rem;">slug: <?= $value['slug'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $title_en ?>
                                <?php if ($title_en == '-' && $has_en == false): ?>
                                    <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">manquant</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $title_sw ?>
                                <?php if ($title_sw == '-' && $has_sw == false): ?>
                                    <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">manquant</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $category_name ?></span>
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
                            <td class="text-center">
                                <div class="d-flex gap-1 flex-wrap justify-content-center">
                                    <span class="badge <?= $has_fr ? 'bg-primary' : 'bg-light text-muted border' ?>" style="font-size: 0.7rem;" title="<?= $has_fr ? 'Français disponible' : 'Français manquant' ?>">
                                        FR
                                    </span>
                                    <span class="badge <?= $has_en ? 'bg-primary' : 'bg-light text-muted border' ?>" style="font-size: 0.7rem;" title="<?= $has_en ? 'English available' : 'English missing' ?>">
                                        EN
                                    </span>
                                    <span class="badge <?= $has_sw ? 'bg-primary' : 'bg-light text-muted border' ?>" style="font-size: 0.7rem;" title="<?= $has_sw ? 'Kiswahili inapatikana' : 'Kiswahili haipo' ?>">
                                        SW
                                    </span>
                                </div>
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
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#translations_<?= $value['id'] ?>">
                                                <i class="bx bx-flag text-primary me-2"></i>Traductions
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

                        <!-- MODAL VIEW DETAILS (MULTILINGUE) -->
                        <div class="modal fade" id="view_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-package me-2"></i>Détails du produit</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <ul class="nav nav-tabs mb-3">
                                            <li class="nav-item">
                                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#view_fr_<?= $value['id'] ?>">🇫🇷 Français</button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#view_en_<?= $value['id'] ?>">🇬🇧 English</button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#view_sw_<?= $value['id'] ?>">🇹🇿 Kiswahili</button>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <!-- FR -->
                                            <div class="tab-pane fade show active" id="view_fr_<?= $value['id'] ?>">
                                                <div class="row">
                                                    <div class="col-md-4 text-center border-end">
                                                        <img src="<?= base_url($image_path) ?>" 
                                                             class="rounded border border-3 border-primary mb-3"
                                                             style="width:200px; height:200px; object-fit:cover;"
                                                             alt="Product">
                                                        <h5><?= htmlspecialchars($value['title_fr'] ?: $value['title']) ?></h5>
                                                        <span class="badge bg-success fs-5"><?= htmlspecialchars($value['price']) ?></span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="mb-3">
                                                            <label class="text-muted small fw-bold">Catégorie</label>
                                                            <p><?= $category_name ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="text-muted small fw-bold">Description</label>
                                                            <div class="p-2 bg-light rounded"><?= nl2br(htmlspecialchars($value['description_fr'] ?: $value['description'])) ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- EN -->
                                            <div class="tab-pane fade" id="view_en_<?= $value['id'] ?>">
                                                <div class="row">
                                                    <div class="col-md-4 text-center border-end">
                                                        <img src="<?= base_url($image_path) ?>" 
                                                             class="rounded border border-3 border-primary mb-3"
                                                             style="width:200px; height:200px; object-fit:cover;"
                                                             alt="Product">
                                                        <h5><?= htmlspecialchars($value['title_en'] ?: '-') ?></h5>
                                                        <span class="badge bg-success fs-5"><?= htmlspecialchars($value['price']) ?></span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="mb-3">
                                                            <label class="text-muted small fw-bold">Category</label>
                                                            <p><?= $category_name ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="text-muted small fw-bold">Description</label>
                                                            <div class="p-2 bg-light rounded"><?= nl2br(htmlspecialchars($value['description_en'] ?: 'Not defined')) ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- SW -->
                                            <div class="tab-pane fade" id="view_sw_<?= $value['id'] ?>">
                                                <div class="row">
                                                    <div class="col-md-4 text-center border-end">
                                                        <img src="<?= base_url($image_path) ?>" 
                                                             class="rounded border border-3 border-primary mb-3"
                                                             style="width:200px; height:200px; object-fit:cover;"
                                                             alt="Product">
                                                        <h5><?= htmlspecialchars($value['title_sw'] ?: '-') ?></h5>
                                                        <span class="badge bg-success fs-5"><?= htmlspecialchars($value['price']) ?></span>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="mb-3">
                                                            <label class="text-muted small fw-bold">Category</label>
                                                            <p><?= $category_name ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="text-muted small fw-bold">Maudhui</label>
                                                            <div class="p-2 bg-light rounded"><?= nl2br(htmlspecialchars($value['description_sw'] ?: 'Haijafafanuliwa')) ?></div>
                                                        </div>
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

                        <!-- MODAL UPDATE (MULTILINGUE) -->
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
                                            <!-- Image -->
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
                                                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WEBP, SVG</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Informations multilingues -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-flag me-2"></i>Contenu multilingue</h6>
                                                    <ul class="nav nav-tabs mb-3">
                                                        <li class="nav-item">
                                                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#update_fr_<?= $value['id'] ?>">🇫🇷 Français</button>
                                                        </li>
                                                        <li class="nav-item">
                                                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#update_en_<?= $value['id'] ?>">🇬🇧 English</button>
                                                        </li>
                                                        <li class="nav-item">
                                                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#update_sw_<?= $value['id'] ?>">🇹🇿 Kiswahili</button>
                                                        </li>
                                                    </ul>
                                                    <div class="tab-content">
                                                        <!-- FR -->
                                                        <div class="tab-pane fade show active" id="update_fr_<?= $value['id'] ?>">
                                                            <div class="row g-3">
                                                                <div class="col-12">
                                                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" name="title_fr" value="<?= htmlspecialchars($value['title_fr'] ?: $value['title']) ?>" required>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                                                    <textarea class="form-control" name="description_fr" rows="5" required><?= htmlspecialchars($value['description_fr'] ?: $value['description']) ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- EN -->
                                                        <div class="tab-pane fade" id="update_en_<?= $value['id'] ?>">
                                                            <div class="row g-3">
                                                                <div class="col-12">
                                                                    <label class="form-label fw-bold">Title</label>
                                                                    <input type="text" class="form-control" name="title_en" value="<?= htmlspecialchars($value['title_en'] ?? '') ?>" placeholder="English title">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label fw-bold">Description</label>
                                                                    <textarea class="form-control" name="description_en" rows="5" placeholder="English description"><?= htmlspecialchars($value['description_en'] ?? '') ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- SW -->
                                                        <div class="tab-pane fade" id="update_sw_<?= $value['id'] ?>">
                                                            <div class="row g-3">
                                                                <div class="col-12">
                                                                    <label class="form-label fw-bold">Kichwa</label>
                                                                    <input type="text" class="form-control" name="title_sw" value="<?= htmlspecialchars($value['title_sw'] ?? '') ?>" placeholder="Kichwa kwa Kiswahili">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label fw-bold">Maelezo</label>
                                                                    <textarea class="form-control" name="description_sw" rows="5" placeholder="Maelezo kwa Kiswahili"><?= htmlspecialchars($value['description_sw'] ?? '') ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Paramètres -->
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-slider me-2"></i>Paramètres</h6>
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

                        <!-- MODAL TRANSLATIONS -->
                        <div class="modal fade" id="translations_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title"><i class="bx bx-flag me-2"></i>Traductions - <?= htmlspecialchars($value['title_fr'] ?: $value['title']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 border-end">
                                                <div class="text-center">
                                                    <div class="badge bg-primary mb-2 p-2">🇫🇷 FRANÇAIS</div>
                                                    <h6><?= htmlspecialchars($value['title_fr'] ?: $value['title']) ?></h6>
                                                    <p class="small"><?= nl2br(htmlspecialchars(substr($value['description_fr'] ?: $value['description'], 0, 100))) ?>...</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 border-end">
                                                <div class="text-center">
                                                    <div class="badge bg-primary mb-2 p-2">🇬🇧 ENGLISH</div>
                                                    <h6><?= htmlspecialchars($value['title_en'] ?: '-') ?></h6>
                                                    <p class="small"><?= nl2br(htmlspecialchars(substr($value['description_en'] ?? '', 0, 100))) ?>...</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <div class="badge bg-primary mb-2 p-2">🇹🇿 KISWAHILI</div>
                                                    <h6><?= htmlspecialchars($value['title_sw'] ?: '-') ?></h6>
                                                    <p class="small"><?= nl2br(htmlspecialchars(substr($value['description_sw'] ?? '', 0, 100))) ?>...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id'] ?>" class="btn btn-warning">
                                            <i class="bx bx-edit"></i> Modifier les traductions
                                        </a>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
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
                                        <p class="text-muted">Produit : <strong><?= htmlspecialchars($value['title_fr'] ?: $value['title']) ?></strong></p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('advertise-product-delete') ?>" method="POST">
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
                                    <form action="<?= base_url('advertise-product-change-status') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="is_active" value="<?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-header <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                            <h5 class="modal-title">
                                                <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'Désactiver' : 'Activer' ?> le produit
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <p>Voulez-vous vraiment <strong><?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'désactiver' : 'activer' ?></strong> le produit <strong><?= htmlspecialchars($value['title_fr'] ?: $value['title']) ?></strong> ?</p>
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
                                    <form action="<?= base_url('advertise-product-change-featured') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="in_vedette" value="<?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-header <?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 'bg-secondary' : 'bg-warning' ?> text-white">
                                            <h5 class="modal-title">
                                                <?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 'Retirer de la vedette' : 'Mettre en vedette' ?>
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <p>Voulez-vous vraiment <strong><?= (!empty($value['in_vedette']) && $value['in_vedette'] == 1) ? 'retirer de la vedette' : 'mettre en vedette' ?></strong> le produit <strong><?= htmlspecialchars($value['title_fr'] ?: $value['title']) ?></strong> ?</p>
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

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="11" class="text-center py-5">
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

            <form action="<?= base_url('advertise-product-create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <!-- Image -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Image principale <span class="text-danger">*</span></h6>
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

                    <!-- Contenu multilingue -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-flag me-2"></i>Contenu multilingue</h6>
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#create_fr">🇫🇷 Français <span class="text-danger">*</span></button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#create_en">🇬🇧 English</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#create_sw">🇹🇿 Kiswahili</button>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <!-- FR -->
                                <div class="tab-pane fade show active" id="create_fr">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title_fr" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="description_fr" rows="5" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                <!-- EN -->
                                <div class="tab-pane fade" id="create_en">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Title</label>
                                            <input type="text" class="form-control" name="title_en" placeholder="English title">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Description</label>
                                            <textarea class="form-control" name="description_en" rows="5" placeholder="English description"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <!-- SW -->
                                <div class="tab-pane fade" id="create_sw">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Kichwa</label>
                                            <input type="text" class="form-control" name="title_sw" placeholder="Kichwa kwa Kiswahili">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Maelezo</label>
                                            <textarea class="form-control" name="description_sw" rows="5" placeholder="Maelezo kwa Kiswahili"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paramètres -->
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-slider me-2"></i>Paramètres</h6>
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
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [1, 7, 8, 9, 10] }
        ]
    });
    
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<style>
.nav-tabs .nav-link {
    color: #495057;
    font-weight: 500;
    border: none;
    border-bottom: 2px solid transparent;
}
.nav-tabs .nav-link:hover {
    color: #667eea;
}
.nav-tabs .nav-link.active {
    color: #667eea;
    border-bottom: 2px solid #667eea;
    background: transparent;
}
.badge {
    font-weight: 500;
}
</style>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>