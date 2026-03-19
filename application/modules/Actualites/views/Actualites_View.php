<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// ============================================================================
// HELPERS PHP
// ============================================================================

if (!function_exists('format_date_actu')) {
    function format_date_actu($date) {
        if (empty($date)) return '-';
        return date('d/m/Y H:i', strtotime($date));
    }
}

if (!function_exists('truncate_text')) {
    function truncate_text($text, $length = 100) {
        if (empty($text)) return '';
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }
}

if (!function_exists('format_tags')) {
    function format_tags($tags_json) {
        if (empty($tags_json)) return '';
        $tags = json_decode($tags_json, true);
        if (!is_array($tags)) return '';
        return implode(', ', $tags);
    }
}
?>

<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Contenu</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="javascript:;">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Actualités v5.0</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a class="btn btn-danger btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bx bx-plus"></i> <span class="d-none d-sm-inline">Nouvelle actualité</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Dashboard -->
        <div class="row mb-4 g-3">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-primary bg-opacity-10">
                    <div class="card-body text-center">
                        <i class="bx bx-news text-primary fs-2 mb-2"></i>
                        <h4 class="mb-0 fw-bold"><?= $stats['total'] ?? 0 ?></h4>
                        <small class="text-muted">Total articles</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-success bg-opacity-10">
                    <div class="card-body text-center">
                        <i class="bx bx-check-circle text-success fs-2 mb-2"></i>
                        <h4 class="mb-0 fw-bold"><?= $stats['publiees'] ?? 0 ?></h4>
                        <small class="text-muted">Publiés</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-warning bg-opacity-10">
                    <div class="card-body text-center">
                        <i class="bx bx-star text-warning fs-2 mb-2"></i>
                        <h4 class="mb-0 fw-bold"><?= $stats['en_avant'] ?? 0 ?></h4>
                        <small class="text-muted">En avant</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-info bg-opacity-10">
                    <div class="card-body text-center">
                        <i class="bx bx-crown text-info fs-2 mb-2"></i>
                        <h4 class="mb-0 fw-bold"><?= $stats['for_subscriber'] ?? 0 ?></h4>
                        <small class="text-muted">Abonnés</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-purple bg-opacity-10">
                    <div class="card-body text-center">
                        <i class="bx bxl-facebook text-purple fs-2 mb-2" style="color: #6f42c1;"></i>
                        <h4 class="mb-0 fw-bold"><?= $stats['in_socialmedia'] ?? 0 ?></h4>
                        <small class="text-muted">Réseaux</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-secondary bg-opacity-10">
                    <div class="card-body text-center">
                        <i class="bx bx-show text-secondary fs-2 mb-2"></i>
                        <h4 class="mb-0 fw-bold"><?= number_format($total_vues ?? 0, 0, ',', ' ') ?></h4>
                        <small class="text-muted">Vues totales</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Articles Grid -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0"><i class="bx bx-news me-2 text-danger"></i>Gestion des Actualités</h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="filterCategorie" style="width: auto;">
                            <option value="">Toutes catégories</option>
                            <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <select class="form-select form-select-sm" id="filterStatut" style="width: auto;">
                            <option value="">Tous statuts</option>
                            <option value="publie">Publiés</option>
                            <option value="archive">Archivés</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="articlesTable" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">Image</th>
                                <th width="30%">Article</th>
                                <th width="15%">Infos</th>
                                <th width="8%">Vues</th>
                                <th width="8%">Statut</th>
                                <th width="8%">En avant</th>
                                <th width="8%">Abonnés</th>
                                <th width="8%">Réseaux</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($actualites)): foreach ($actualites as $item): 
                            // Image principale
                            $image_url = base_url('assets/images/news-placeholder.jpg');
                            if (!empty($item['image_principale'])) {
                                $image_url = (strpos($item['image_principale'], 'http') === 0) 
                                    ? $item['image_principale'] 
                                    : base_url($item['image_principale']);
                            }
                            
                            // Escape pour JS
                            $js_title = htmlspecialchars($item['titre'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');
                            $is_archive = !empty($item['deleted_at']);
                            
                            // Booléens
                            $is_en_avant = !empty($item['est_en_avant']) && $item['est_en_avant'] == 1;
                            $is_for_subscriber = !empty($item['for_subscriber']) && $item['for_subscriber'] == 1;
                            $is_in_socialmedia = !empty($item['in_socialmedia']) && $item['in_socialmedia'] == 1;
                        ?>
                            <tr data-id="<?= $item['id_actualite'] ?>" 
                                data-categorie="<?= htmlspecialchars($item['categorie'] ?? '') ?>"
                                data-statut="<?= $is_archive ? 'archive' : 'publie' ?>">
                                <td>
                                    <div class="article-thumb-wrapper position-relative" style="width: 100px; height: 70px;">
                                        <img src="<?= $image_url ?>" 
                                             class="rounded w-100 h-100" 
                                             style="object-fit: cover; background: #f8f9fa;"
                                             loading="lazy"
                                             onerror="this.src='<?= base_url('assets/images/news-placeholder.jpg') ?>'">
                                        
                                        <?php if ($is_en_avant && !$is_archive): ?>
                                            <div class="position-absolute top-0 start-0 m-1">
                                                <span class="badge bg-warning"><i class="bx bx-star"></i></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <h6 class="mb-1 fw-bold text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($item['titre'] ?? '') ?>">
                                            <?= htmlspecialchars($item['titre'] ?? 'Sans titre') ?>
                                        </h6>
                                        
                                        <small class="text-muted mb-1">
                                            <i class="bx bx-user me-1"></i><?= htmlspecialchars($item['auteur'] ?? 'Admin') ?>
                                            <span class="mx-1">|</span>
                                            <i class="bx bx-calendar me-1"></i><?= format_date_actu($item['date_publication']) ?>
                                        </small>
                                        
                                        <?php if (!empty($item['categorie'])): ?>
                                            <span class="badge bg-light text-dark border w-fit-content mb-1" style="font-size: 0.7rem;">
                                                <?= htmlspecialchars($item['categorie']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <small class="text-muted">
                                            <i class="bx bx-tag me-1"></i><?= !empty(format_tags($item['tags'])) ? truncate_text(format_tags($item['tags']), 25) : 'Aucun tag' ?>
                                        </small>
                                        <?php if (!empty($item['slug'])): ?>
                                            <small class="text-muted" style="font-size: 0.7rem;">
                                                /<?= htmlspecialchars($item['slug']) ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if (!empty($item['resume'])): ?>
                                            <small class="text-muted text-truncate" style="max-width: 200px;">
                                                <?= truncate_text($item['resume'], 40) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bx bx-show me-1 text-info"></i><?= number_format($item['vues'] ?? 0, 0, ',', ' ') ?>
                                    </span>
                                </td>

                                <td class="text-center">
                                    <?php if (!$is_archive): ?>
                                        <span class="badge bg-success">Publié</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Archivé</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php if (!$is_archive): ?>
                                        <div class="form-check form-switch form-check-sm d-flex justify-content-center">
                                            <input class="form-check-input toggle-field" type="checkbox" 
                                                   data-id="<?= $item['id_actualite'] ?>" data-field="est_en_avant"
                                                   <?= $is_en_avant ? 'checked' : '' ?>>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php if (!$is_archive): ?>
                                        <div class="form-check form-switch form-check-sm d-flex justify-content-center">
                                            <input class="form-check-input toggle-field" type="checkbox" 
                                                   data-id="<?= $item['id_actualite'] ?>" data-field="for_subscriber"
                                                   <?= $is_for_subscriber ? 'checked' : '' ?>>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php if (!$is_archive): ?>
                                        <div class="form-check form-switch form-check-sm d-flex justify-content-center">
                                            <input class="form-check-input toggle-field" type="checkbox" 
                                                   data-id="<?= $item['id_actualite'] ?>" data-field="in_socialmedia"
                                                   <?= $is_in_socialmedia ? 'checked' : '' ?>>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="<?= base_url('Actualites/view/' . ($item['slug'] ?? '')) ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Voir l'article">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        
                                        <?php if (!$is_archive): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?= $item['id_actualite'] ?>"
                                                    title="Modifier">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?= $item['id_actualite'] ?>, '<?= $js_title ?>', false)"
                                                    title="Archiver">
                                                <i class="bx bx-archive-in"></i>
                                            </button>
                                        <?php else: ?>
                                            <form action="<?= base_url('Actualites/Restore') ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $item['id_actualite'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Restaurer">
                                                    <i class="bx bx-refresh"></i>
                                                </button>
                                            </form>
                                            
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?= $item['id_actualite'] ?>, '<?= $js_title ?>', true)"
                                                    title="Supprimer définitivement">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <!-- ========================================== -->
                            <!-- MODAL EDIT - AVEC GESTION D'IMAGE COMPLÈTE -->
                            <!-- ========================================== -->
                            <div class="modal fade" id="editModal<?= $item['id_actualite'] ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'article</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('Actualites/Update') ?>" method="POST" id="editForm<?= $item['id_actualite'] ?>">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $item['id_actualite'] ?>">
                                                
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control form-control-lg" name="titre" 
                                                                   value="<?= htmlspecialchars($item['titre'] ?? '') ?>" required maxlength="255"
                                                                   onchange="generateSlug(<?= $item['id_actualite'] ?>)">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="slug" 
                                                                   value="<?= htmlspecialchars($item['slug'] ?? '') ?>" required maxlength="255">
                                                            <small class="text-muted">URL: /actualite/<span id="slugPreview<?= $item['id_actualite'] ?>"><?= htmlspecialchars($item['slug'] ?? '') ?></span></small>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Résumé</label>
                                                            <textarea class="form-control" name="resume" rows="2"><?= htmlspecialchars($item['resume'] ?? '') ?></textarea>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Contenu</label>
                                                            <textarea class="form-control" name="contenu" rows="6"><?= htmlspecialchars($item['contenu'] ?? '') ?></textarea>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Catégorie</label>
                                                                <input type="text" class="form-control" name="categorie" 
                                                                       value="<?= htmlspecialchars($item['categorie'] ?? '') ?>" list="categoriesList">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Tags</label>
                                                                <input type="text" class="form-control" name="tags" 
                                                                       value="<?= htmlspecialchars(format_tags($item['tags'] ?? '')) ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <!-- ========================================== -->
                                                        <!-- SECTION IMAGE - POUR MODAL UPDATE           -->
                                                        <!-- ========================================== -->
                                                        <div class="card border mb-3">
                                                            <div class="card-header bg-light">
                                                                <h6 class="mb-0"><i class="bx bx-image me-2"></i>Image principale</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <label class="form-label small text-muted">Image actuelle</label>
                                                                        <div class="position-relative mb-3">
                                                                            <?php 
                                                                            $current_image = $item['image_principale'] ?? '';
                                                                            $image_display = $current_image ? base_url($current_image) : base_url('assets/images/news-placeholder.jpg');
                                                                            ?>
                                                                            <img src="<?= $image_display ?>" 
                                                                                 class="rounded w-100" 
                                                                                 style="height: 150px; object-fit: cover; border: 1px solid #dee2e6;"
                                                                                 id="currentImage<?= $item['id_actualite'] ?>"
                                                                                 onerror="this.src='<?= base_url('assets/images/news-placeholder.jpg') ?>'">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="col-md-12">
                                                                        <label class="form-label small text-muted">Changer l'image</label>
                                                                        <div class="upload-thumbnail-zone border rounded p-3 text-center mb-2" 
                                                                             style="border-style: dashed !important; cursor: pointer; background: #f8f9fa;"
                                                                             onclick="document.getElementById('updateImageInput<?= $item['id_actualite'] ?>').click()">
                                                                            <i class="bx bx-cloud-upload fs-3 text-muted mb-2"></i>
                                                                            <p class="mb-0 small text-muted">Cliquez pour uploader une nouvelle image</p>
                                                                            <p class="mb-0" style="font-size: 0.7rem; color: #999;">JPG, PNG, WEBP (max 50MB)</p>
                                                                        </div>
                                                                        <input type="file" 
                                                                               id="updateImageInput<?= $item['id_actualite'] ?>" 
                                                                               class="d-none" 
                                                                               accept="image/*"
                                                                               onchange="uploadUpdateImage(<?= $item['id_actualite'] ?>, this.files[0])">
                                                                        
                                                                        <div id="updateImagePreview<?= $item['id_actualite'] ?>" class="d-none position-relative mt-2">
                                                                            <div class="position-relative">
                                                                                <img src="" class="rounded w-100" style="height: 120px; object-fit: cover;" id="updateImageImg<?= $item['id_actualite'] ?>">
                                                                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                                                                                        onclick="removeUpdateImage(<?= $item['id_actualite'] ?>)">
                                                                                    <i class="bx bx-x"></i>
                                                                                </button>
                                                                                <div class="position-absolute bottom-0 start-0 w-100 bg-success text-white text-center small py-1">
                                                                                    <i class="bx bx-check me-1"></i>Nouvelle image
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div id="updateImageProgress<?= $item['id_actualite'] ?>" class="d-none mt-2">
                                                                            <div class="progress" style="height: 4px;">
                                                                                <div class="progress-bar bg-success" style="width: 0%"></div>
                                                                            </div>
                                                                            <small class="text-muted">Upload en cours...</small>
                                                                        </div>
                                                                        
                                                                        <input type="hidden" name="image_principale" id="updateImageSelected<?= $item['id_actualite'] ?>" value="<?= htmlspecialchars($current_image) ?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- FIN SECTION IMAGE -->

                                                        <!-- PARAMÈTRES -->
                                                        <div class="card border">
                                                            <div class="card-header bg-light">
                                                                <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Paramètres</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Auteur</label>
                                                                    <input type="text" class="form-control" name="auteur" 
                                                                           value="<?= htmlspecialchars($item['auteur'] ?? 'Admin') ?>">
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Date</label>
                                                                    <input type="datetime-local" class="form-control" name="date_publication" 
                                                                           value="<?= !empty($item['date_publication']) ? date('Y-m-d\TH:i', strtotime($item['date_publication'])) : '' ?>">
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Page associée</label>
                                                                    <input type="number" class="form-control" name="id_page_associee" 
                                                                           value="<?= $item['id_page_associee'] ?? '' ?>">
                                                                </div>

                                                                <hr>

                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="est_en_avant" value="1" 
                                                                               <?= $is_en_avant ? 'checked' : '' ?>>
                                                                        <label class="form-check-label"><i class="bx bx-star text-warning me-1"></i>En avant</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="for_subscriber" value="1" 
                                                                               <?= $is_for_subscriber ? 'checked' : '' ?>>
                                                                        <label class="form-check-label"><i class="bx bx-crown text-info me-1"></i>Abonnés</label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="in_socialmedia" value="1" 
                                                                               <?= $is_in_socialmedia ? 'checked' : '' ?>>
                                                                        <label class="form-check-label"><i class="bx bxl-facebook text-primary me-1"></i>Réseaux</label>
                                                                    </div>
                                                                </div>

                                                                <hr>

                                                                <h6 class="mb-2">Statistiques</h6>
                                                                <ul class="list-unstyled small text-muted">
                                                                    <li><strong>Vues:</strong> <?= number_format($item['vues'] ?? 0) ?></li>
                                                                    <li><strong>Créé:</strong> <?= format_date_actu($item['created_at']) ?></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-danger"><i class="bx bx-save me-1"></i>Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bx bx-news fs-1 text-muted mb-3"></i>
                                        <h5>Aucune actualité</h5>
                                        <p class="text-muted">Commencez par créer votre premier article</p>
                                        <a href="javascript:;" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                            <i class="bx bx-plus me-1"></i>Nouvelle actualité
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- MODAL UPLOAD - CRÉATION NOUVEL ARTICLE     -->
    <!-- ========================================== -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title"><i class="bx bx-upload me-2"></i>Uploader une image pour actualité</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeUploadModal"></button>
                </div>
                <div class="modal-body p-0">
                    
                    <!-- ÉTAPE 1: Sélection du fichier image -->
                    <div id="uploadStep1" class="upload-zone p-5 text-center">
                        <div class="upload-illustration mb-4">
                            <div class="position-relative d-inline-block">
                                <i class="bx bx-cloud-upload text-danger" style="font-size: 5rem;"></i>
                                <div class="position-absolute bottom-0 end-0 bg-success rounded-circle p-2">
                                    <i class="bx bx-plus text-white"></i>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="mb-3">Glissez-déposez une image</h4>
                        <p class="text-muted mb-4">
                            ou <span class="text-danger fw-bold cursor-pointer" onclick="document.getElementById('fileInput').click()">parcourir</span> pour sélectionner
                        </p>
                        
                        <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
                            <span class="badge bg-light text-dark border">JPG</span>
                            <span class="badge bg-light text-dark border">JPEG</span>
                            <span class="badge bg-light text-dark border">PNG</span>
                            <span class="badge bg-light text-dark border">GIF</span>
                            <span class="badge bg-light text-dark border">WEBP</span>
                        </div>
                        
                        <div class="alert alert-light border mx-auto" style="max-width: 500px;">
                            <small class="text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Image principale:</strong> L'image sera automatiquement redimensionnée. 
                                Miniature générée pour les listes. Max 50MB.
                            </small>
                        </div>
                        
                        <input type="file" id="fileInput" class="d-none" accept="image/*">
                    </div>

                    <!-- ÉTAPE 2: Upload progress et détection automatique -->
                    <div id="uploadStep2" class="d-none p-4">
                        <div class="upload-item mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-danger bg-opacity-10 rounded p-2">
                                        <i class="bx bx-image text-danger fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fw-bold" id="uploadFileName">image.jpg</h6>
                                    <small class="text-muted" id="uploadFileSize">0 MB</small>
                                </div>
                                <div class="flex-shrink-0">
                                    <button class="btn btn-sm btn-outline-danger" id="cancelUploadBtn">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Barre de progression -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-danger" id="uploadPhase">Upload en cours...</span>
                                    <span class="fw-bold" id="uploadPercent">0%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" 
                                         id="uploadProgressBar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1 small text-muted">
                                    <span id="uploadSpeed">0 MB/s</span>
                                    <span id="uploadChunks">0 / 0 chunks</span>
                                </div>
                            </div>
                            
                            <!-- DÉTECTION AUTOMATIQUE DU NOM -->
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body">
                                    <h6 class="mb-2"><i class="bx bx-bulb text-warning me-1"></i>Détection automatique</h6>
                                    <div class="mb-2">
                                        <label class="form-label small text-muted">Titre suggéré (modifiable)</label>
                                        <input type="text" class="form-control" id="detectedTitle" 
                                               placeholder="Titre de l'article" value="Mon article">
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted d-block"><i class="bx bx-image me-1"></i><span id="detectedDimensions">-</span></small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block"><i class="bx bx-hdd me-1"></i><span id="detectedSize">-</span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Traitement en cours -->
                            <div id="processingStatus" class="d-none">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-3">
                                        <h6 class="mb-3"><i class="bx bx-cog bx-spin me-2 text-danger"></i>Traitement de l'image...</h6>
                                        <div class="row g-2" id="processingSteps">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-2 bg-white rounded border">
                                                    <i class="bx bx-check-circle text-success me-2" id="step-upload-icon"></i>
                                                    <span class="flex-grow-1">Upload terminé</span>
                                                    <span class="badge bg-success">OK</span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-2 bg-white rounded border">
                                                    <i class="bx bx-loader-alt bx-spin text-danger me-2" id="step-analysis-icon"></i>
                                                    <span class="flex-grow-1">Analyse image</span>
                                                    <span class="badge bg-secondary" id="step-analysis-status">En cours</span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-2 bg-white rounded border">
                                                    <i class="bx bx-circle text-muted me-2" id="step-thumbnail-icon"></i>
                                                    <span class="flex-grow-1">Génération miniature</span>
                                                    <span class="badge bg-secondary" id="step-thumbnail-status">En attente</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ÉTAPE 3: Formulaire de création -->
                    <div id="uploadStep3" class="d-none">
                        <form id="articleDetailsForm" action="<?= base_url('Actualites/Create') ?>" method="POST">
                            <div class="row g-0">
                                <div class="col-md-8 p-4 border-end">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" name="titre" id="articleTitle" required maxlength="255" 
                                               onchange="generateSlugCreate()" placeholder="Titre de l'article">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="slug" id="articleSlug" required maxlength="255">
                                        <small class="text-muted">URL: /actualite/<span id="slugPreviewCreate">mon-article</span></small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Résumé</label>
                                        <textarea class="form-control" name="resume" rows="2" id="articleResume" placeholder="Court résumé pour les listes"></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Contenu</label>
                                        <textarea class="form-control" name="contenu" rows="6" id="articleContent" placeholder="Contenu de l'article..."></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Catégorie</label>
                                            <input type="text" class="form-control" name="categorie" id="articleCategory" list="categoriesList" placeholder="Ex: Actualités">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Tags</label>
                                            <input type="text" class="form-control" name="tags" id="articleTags" placeholder="tag1, tag2, tag3">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 bg-light p-4">
                                    <h6 class="mb-3"><i class="bx bx-cog me-2"></i>Paramètres</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Auteur</label>
                                        <input type="text" class="form-control" name="auteur" id="articleAuteur" value="Admin">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date publication</label>
                                        <input type="datetime-local" class="form-control" name="date_publication" id="articleDate">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Page associée</label>
                                        <input type="number" class="form-control" name="id_page_associee" placeholder="ID page">
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="est_en_avant" value="1" id="estEnAvant">
                                            <label class="form-check-label"><i class="bx bx-star text-warning me-1"></i>Mettre en avant</label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="for_subscriber" value="1" id="forSubscriber">
                                            <label class="form-check-label"><i class="bx bx-crown text-info me-1"></i>Réservé aux abonnés</label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="in_socialmedia" value="1" id="inSocialMedia">
                                            <label class="form-check-label"><i class="bx bxl-facebook text-primary me-1"></i>Publié sur réseaux</label>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h6 class="mb-2"><i class="bx bx-image me-1"></i>Image uploadée</h6>
                                    <div id="uploadedImagePreview" class="mb-3">
                                        <img src="" class="img-fluid rounded border" id="previewImage" style="max-height: 120px; width: auto;">
                                    </div>
                                    
                                    <input type="hidden" name="thumbnail" id="selectedThumbnail">
                                    <input type="hidden" name="uploaded_file_path" id="uploadedFilePath">
                                    <input type="hidden" name="auto_detected_data" id="autoDetectedData">
                                </div>
                            </div>
                            
                            <div class="p-4 border-top bg-white d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetUpload()">
                                    <i class="bx bx-arrow-back me-1"></i>Retour
                                </button>
                                <button type="submit" class="btn btn-danger btn-lg">
                                    <i class="bx bx-save me-1"></i>Publier l'article
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Delete/Archive Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= base_url('Actualites/Delete') ?>" method="POST" id="deleteForm">
                    <input type="hidden" name="permanent" id="deletePermanent" value="0">
                    <div class="modal-body text-center py-4">
                        <i class="bx bx-error-circle text-danger display-4 mb-3"></i>
                        <h5 id="deleteTitle">Confirmer l'action ?</h5>
                        <p class="text-muted" id="deleteArticleTitle"></p>
                        <div class="alert alert-warning" id="deleteWarning">
                            <i class="bx bx-info-circle me-2"></i>
                            Cette action archivera l'article. Vous pourrez le restaurer ultérieurement.
                        </div>
                        <input type="hidden" name="id" id="deleteArticleId">
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger" id="deleteConfirmBtn">
                            <i class="bx bx-trash me-1"></i>Confirmer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Datalist -->
    <datalist id="categoriesList">
        <option value="Actualités">
        <option value="Blog">
        <option value="Événements">
        <option value="Presse">
        <option value="Communiqués">
        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>">
        <?php endforeach; endif; ?>
    </datalist>

</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<!-- Scripts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
// ==========================================
// CONFIGURATION
// ==========================================
const UPLOAD_CONFIG = {
    baseUrl: '<?= base_url('Actualites/') ?>',
    chunkSize: 5 * 1024 * 1024,
    maxFileSize: 50 * 1024 * 1024
};

let uploadManager = null;

// ==========================================
// TOASTR
// ==========================================
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000"
};

// ==========================================
// CLASS UPLOAD MANAGER - STYLE VIDEO
// ==========================================
class ActualitesUploadManager {
    constructor() {
        this.reset();
    }
    
    reset() {
        this.state = {
            file: null,
            uploadId: null,
            totalChunks: 0,
            uploadedChunks: new Set(),
            failedChunks: new Map(),
            isUploading: false,
            isCancelled: false,
            startTime: null,
            bytesUploaded: 0
        };
    }
    
    async start(file) {
        try {
            this.reset();
            this.state.file = file;
            this.state.isUploading = true;
            this.state.startTime = Date.now();
            
            // Validation
            if (file.size > UPLOAD_CONFIG.maxFileSize) {
                throw new Error(`Fichier trop grand. Maximum: ${this.formatBytes(UPLOAD_CONFIG.maxFileSize)}`);
            }
            
            // Étape 1: Initialisation
            this.updateUI('init', { 
                fileName: file.name, 
                fileSize: this.formatBytes(file.size) 
            });
            
            // Détection automatique du titre depuis le nom du fichier
            const detectedTitle = this.detectTitleFromFilename(file.name);
            $('#detectedTitle').val(detectedTitle);
            
            // Init upload session
            const initData = await this.apiCall('initUpload', {
                file_name: file.name,
                file_size: file.size
            });
            
            this.state.uploadId = initData.upload_id;
            this.state.totalChunks = initData.total_chunks;
            
            // Étape 2: Upload des chunks
            this.updateUI('uploading', { percent: 0 });
            await this.uploadChunks();
            
            if (this.state.isCancelled) return;
            
            // Étape 3: Completion
            this.updateUI('processing');
            const result = await this.apiCall('completeUpload', {
                upload_id: this.state.uploadId
            });
            
            this.updateUI('complete', result.data);
            
        } catch (error) {
            console.error('Upload error:', error);
            this.updateUI('error', { message: error.message });
        }
    }
    
    detectTitleFromFilename(filename) {
        // Enlever l'extension
        let name = filename.replace(/\.[^/.]+$/, "");
        // Remplacer les tirets et underscores par des espaces
        name = name.replace(/[-_]/g, ' ');
        // Capitaliser les mots
        return name.split(' ').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
        ).join(' ');
    }
    
    async apiCall(endpoint, data) {
        const formData = new FormData();
        for (let key in data) formData.append(key, data[key]);
        
        try {
            const response = await fetch(UPLOAD_CONFIG.baseUrl + endpoint, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                throw new Error('Réponse invalide');
            }
            
            if (!result.success) throw new Error(result.message);
            return result;
            
        } catch (error) {
            console.error('API error:', error);
            throw error;
        }
    }
    
    async uploadChunks() {
        const queue = [];
        for (let i = 0; i < this.state.totalChunks; i++) {
            if (!this.state.uploadedChunks.has(i)) queue.push(i);
        }
        
        const workers = [];
        const maxWorkers = Math.min(3, queue.length);
        for (let w = 0; w < maxWorkers; w++) {
            workers.push(this.worker(queue));
        }
        await Promise.all(workers);
    }
    
    async worker(queue) {
        while (queue.length > 0 && !this.state.isCancelled) {
            await this.uploadChunk(queue.shift());
        }
    }
    
    async uploadChunk(index, attempt = 0) {
        try {
            const start = index * UPLOAD_CONFIG.chunkSize;
            const end = Math.min(start + UPLOAD_CONFIG.chunkSize, this.state.file.size);
            const chunk = this.state.file.slice(start, end);
            
            const formData = new FormData();
            formData.append('upload_id', this.state.uploadId);
            formData.append('chunk_index', index);
            formData.append('chunk', chunk);
            
            const response = await fetch(UPLOAD_CONFIG.baseUrl + 'uploadChunk', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const text = await response.text();
            const data = JSON.parse(text);
            if (!data.success) throw new Error(data.message);
            
            this.state.uploadedChunks.add(index);
            this.state.bytesUploaded += chunk.size;
            
            const progress = (this.state.uploadedChunks.size / this.state.totalChunks) * 100;
            const speed = this.calculateSpeed();
            
            this.updateUI('progress', {
                percent: progress,
                uploadedChunks: this.state.uploadedChunks.size,
                totalChunks: this.state.totalChunks,
                speed: speed
            });
            
        } catch (error) {
            const retries = this.state.failedChunks.get(index) || 0;
            if (retries < 3 && !this.state.isCancelled) {
                this.state.failedChunks.set(index, retries + 1);
                await new Promise(r => setTimeout(r, 1000 * Math.pow(2, retries)));
                await this.uploadChunk(index, retries + 1);
            } else {
                throw error;
            }
        }
    }
    
    calculateSpeed() {
        const elapsed = (Date.now() - this.state.startTime) / 1000;
        return elapsed > 0 ? (this.state.bytesUploaded / elapsed) / (1024 * 1024) : 0;
    }
    
    formatBytes(bytes) {
        if (bytes <= 0) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + units[i];
    }
    
    updateUI(event, data = {}) {
        const handlers = {
            init: () => {
                $('#uploadStep1').addClass('d-none');
                $('#uploadStep2').removeClass('d-none');
                $('#uploadFileName').text(data.fileName);
                $('#uploadFileSize').text(data.fileSize);
            },
            uploading: () => {
                $('#uploadPhase').text('Upload en cours...');
            },
            progress: () => {
                const pct = Math.round(data.percent);
                $('#uploadProgressBar').css('width', pct + '%');
                $('#uploadPercent').text(pct + '%');
                $('#uploadChunks').text(`${data.uploadedChunks} / ${data.totalChunks} chunks`);
                $('#uploadSpeed').text(data.speed.toFixed(2) + ' MB/s');
            },
            processing: () => {
                $('#uploadPhase').text('Traitement...');
                $('#uploadProgressBar').removeClass('progress-bar-animated');
                $('#processingStatus').removeClass('d-none');
                
                setTimeout(() => {
                    $('#step-analysis-icon').removeClass('bx-loader-alt bx-spin text-danger').addClass('bx-check-circle text-success');
                    $('#step-analysis-status').text('OK');
                }, 500);
                
                setTimeout(() => {
                    $('#step-thumbnail-icon').removeClass('bx-circle text-muted').addClass('bx-check-circle text-success');
                    $('#step-thumbnail-status').text('OK');
                }, 1000);
            },
            complete: () => {
                $('#uploadStep2').addClass('d-none');
                $('#uploadStep3').removeClass('d-none');
                
                if (data.form_suggestions) {
                    $('#articleTitle').val(data.form_suggestions.titre || '');
                    
                    if (data.form_suggestions.titre) {
                        const slug = data.form_suggestions.titre.toLowerCase()
                            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/^-+|-+$/g, '')
                            .substring(0, 200);
                        $('#articleSlug').val(slug);
                        $('#slugPreviewCreate').text(slug);
                    }
                    
                    $('#articleCategory').val(data.form_suggestions.categorie || '');
                }
                
                if (data.dimensions) {
                    $('#detectedDimensions').text(data.dimensions);
                }
                
                if (data.original_file) {
                    const previewUrl = '<?= base_url() ?>' + data.original_file;
                    $('#previewImage').attr('src', previewUrl);
                    $('#uploadedImagePreview').removeClass('d-none');
                    
                    $('#selectedThumbnail').val(data.original_file);
                    $('#uploadedFilePath').val(data.original_file);
                    $('#autoDetectedData').val(JSON.stringify(data));
                }
            },
            error: () => {
                toastr.error(data.message, 'Erreur');
                resetUpload();
            },
            cancel: () => {
                resetUpload();
            }
        };
        
        if (handlers[event]) handlers[event]();
    }
    
    async cancel() {
        this.state.isCancelled = true;
        if (this.state.uploadId) {
            try {
                await this.apiCall('cancelUpload', { upload_id: this.state.uploadId });
            } catch (e) {}
        }
        resetUpload();
    }
}

// ==========================================
// FONCTIONS UI GÉNÉRALES
// ==========================================

function resetUpload() {
    uploadManager = null;
    $('#uploadStep1').removeClass('d-none');
    $('#uploadStep2').addClass('d-none');
    $('#uploadStep3').addClass('d-none');
    $('#processingStatus').addClass('d-none');
    $('#uploadProgressBar').css('width', '0%');
    $('#uploadPercent').text('0%');
    $('#articleDetailsForm')[0].reset();
    $('#previewImage').attr('src', '');
    
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    $('#articleDate').val(now.toISOString().slice(0, 16));
    
    $('#fileInput').val('');
}

function generateSlug(id) {
    const title = $('#editTitle' + id).val();
    const slug = title.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .substring(0, 200);
    $('#editSlug' + id).val(slug);
    $('#slugPreview' + id).text(slug);
}

function generateSlugCreate() {
    const title = $('#articleTitle').val();
    const slug = title.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .substring(0, 200);
    $('#articleSlug').val(slug);
    $('#slugPreviewCreate').text(slug);
}

function confirmDelete(id, title, isPermanent) {
    $('#deleteArticleId').val(id);
    $('#deleteArticleTitle').text(title || 'Cet article');
    $('#deletePermanent').val(isPermanent ? 1 : 0);
    
    if (isPermanent) {
        $('#deleteTitle').text('Supprimer définitivement ?');
        $('#deleteWarning').html('<i class="bx bx-error-circle me-2"></i>Cette action est irréversible. L\'article sera supprimé définitivement.');
        $('#deleteConfirmBtn').html('<i class="bx bx-trash me-1"></i>Supprimer');
    } else {
        $('#deleteTitle').text('Archiver l\'article ?');
        $('#deleteWarning').html('<i class="bx bx-info-circle me-2"></i>Cette action archivera l\'article. Vous pourrez le restaurer ultérieurement.');
        $('#deleteConfirmBtn').html('<i class="bx bx-archive me-1"></i>Archiver');
    }
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ==========================================
// FONCTIONS POUR MODAL UPDATE - GESTION D'IMAGE
// ==========================================

function uploadUpdateImage(id, file) {
    if (!file) return;
    
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        toastr.error('Format non supporté. Utilisez JPG, PNG, GIF ou WEBP');
        return;
    }
    
    if (file.size > 50 * 1024 * 1024) {
        toastr.error('Image trop grande. Maximum 50MB');
        return;
    }
    
    $(`#updateImageProgress${id}`).removeClass('d-none');
    $(`#updateImageProgress${id} .progress-bar`).css('width', '0%');
    
    const formData = new FormData();
    formData.append('image_file', file);
    
    $.ajax({
        url: '<?= base_url('Actualites/uploadUpdateImage') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(evt) {
                if (evt.lengthComputable) {
                    const percent = (evt.loaded / evt.total) * 100;
                    $(`#updateImageProgress${id} .progress-bar`).css('width', percent + '%');
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            $(`#updateImageProgress${id}`).addClass('d-none');
            
            if (response.success) {
                $(`#currentImage${id}`).attr('src', response.preview_url);
                $(`#updateImageImg${id}`).attr('src', response.preview_url);
                $(`#updateImagePreview${id}`).removeClass('d-none');
                $(`#updateImageSelected${id}`).val(response.file_path);
                
                toastr.success('Image uploadée avec succès');
            } else {
                toastr.error(response.message || 'Erreur upload');
            }
        },
        error: function() {
            $(`#updateImageProgress${id}`).addClass('d-none');
            toastr.error('Erreur réseau lors de l\'upload');
        }
    });
}

function removeUpdateImage(id) {
    $(`#updateImagePreview${id}`).addClass('d-none');
    $(`#updateImageImg${id}`).attr('src', '');
    $(`#updateImageInput${id}`).val('');
    
    // Revenir à l'image précédente
    const currentSrc = $(`#currentImage${id}`).attr('src');
    $(`#updateImageSelected${id}`).val(currentSrc.replace('<?= base_url() ?>', ''));
}

// ==========================================
// EVENT LISTENERS
// ==========================================

$(document).ready(function() {
    console.log('Actualités Blog v5.0 initialisé');
    
    if ($.fn.DataTable && $('#articlesTable tbody tr').length > 0) {
        $('#articlesTable').DataTable({
            language: {
                "sProcessing": "Traitement...",
                "sSearch": "Rechercher:",
                "sLengthMenu": "Afficher _MENU_ éléments",
                "sInfo": "Affichage _START_ à _END_ sur _TOTAL_",
                "sInfoEmpty": "Affichage 0 à 0 sur 0",
                "sInfoFiltered": "(filtré de _MAX_ total)",
                "sZeroRecords": "Aucun résultat",
                "oPaginate": {
                    "sFirst": "Premier",
                    "sPrevious": "Précédent",
                    "sNext": "Suivant",
                    "sLast": "Dernier"
                }
            },
            order: [[0, 'desc']],
            pageLength: 25
        });
    }
    
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    $('#articleDate').val(now.toISOString().slice(0, 16));
    
    $('#fileInput').on('change', function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            uploadManager = new ActualitesUploadManager();
            uploadManager.start(files[0]);
        }
    });
    
    const dropZone = document.getElementById('uploadStep1');
    if (dropZone) {
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-danger', 'bg-light');
        });
        
        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-danger', 'bg-light');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-danger', 'bg-light');
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                uploadManager = new ActualitesUploadManager();
                uploadManager.start(files[0]);
            } else {
                toastr.error('Veuillez déposer une image');
            }
        });
    }
    
    $('#cancelUploadBtn').on('click', function() {
        if (uploadManager && confirm('Annuler l\'upload ?')) {
            uploadManager.cancel();
        }
    });
    
    $('#closeUploadModal').on('click', function() {
        if (uploadManager && uploadManager.state.isUploading) {
            if (!confirm('Un upload est en cours. Fermer ?')) {
                return false;
            }
            uploadManager.cancel();
        }
    });
    
    $(document).on('change', '.toggle-field', function() {
        const $cb = $(this);
        const id = $cb.data('id');
        const field = $cb.data('field');
        const value = $cb.is(':checked') ? 1 : 0;
        
        $cb.prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url('Actualites/toggleField') ?>',
            type: 'POST',
            data: { id: id, field: field, value: value },
            success: function(response) {
                if (response.success) {
                    toastr.success('Mis à jour');
                } else {
                    $cb.prop('checked', !$cb.prop('checked'));
                    toastr.error('Erreur');
                }
            },
            error: function() {
                $cb.prop('checked', !$cb.prop('checked'));
                toastr.error('Erreur réseau');
            },
            complete: function() {
                $cb.prop('disabled', false);
            }
        });
    });
    
    $('#articleDetailsForm').on('submit', function(e) {
        const titre = $('#articleTitle').val().trim();
        const slug = $('#articleSlug').val().trim();
        
        if (!titre) {
            e.preventDefault();
            toastr.error('Le titre est obligatoire');
            return false;
        }
        if (!slug) {
            e.preventDefault();
            toastr.error('Le slug est obligatoire');
            return false;
        }
        return true;
    });
    
    $('#filterCategorie').on('change', function() {
        const cat = $(this).val();
        try {
            const table = $('#articlesTable').DataTable();
            table.column(1).search(cat).draw();
        } catch (e) {}
    });
    
    $('#filterStatut').on('change', function() {
        const statut = $(this).val();
        try {
            const table = $('#articlesTable').DataTable();
            if (statut === 'archive') {
                table.column(4).search('Archivé').draw();
            } else if (statut === 'publie') {
                table.column(4).search('Publié').draw();
            } else {
                table.column(4).search('').draw();
            }
        } catch (e) {}
    });
});
</script>

<style>
.bg-purple { background-color: #6f42c1; }
.text-purple { color: #6f42c1; }
.upload-zone {
    min-height: 400px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    cursor: pointer;
}
.upload-zone.border-danger {
    border: 2px dashed #dc3545 !important;
    background-color: #f8f9fa;
}
.upload-thumbnail-zone {
    transition: all 0.3s ease;
}
.upload-thumbnail-zone:hover {
    background: #e9ecef !important;
    border-color: #dc3545 !important;
}
.cursor-pointer {
    cursor: pointer;
}
.w-fit-content {
    width: fit-content;
}
</style>