<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
    <div class="page-content">

        <!-- ==================== BREADCRUMB ==================== -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Médias</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="javascript:;">Galerie</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gestion Autre</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a class="btn btn-primary btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_autre">
                    <i class="bx bx-plus"></i> Nouvel Élément
                </a>
                <a class="btn btn-outline-info btn-sm ms-2" href="<?= base_url('autre/diagnostics') ?>" target="_blank">
                    <i class="bx bx-test-tube"></i> Diagnostic
                </a>
            </div>
        </div>

        <!-- ==================== STATISTIQUES ==================== -->
        <div class="row mb-4">
            <div class="col-md-2 col-6">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?= $stats['total'] ?></h3>
                        <small>Total</small>
                    </div>
                </div>
            </div>
            <?php foreach ($type_configs as $key => $config): ?>
            <div class="col-md-2 col-6">
                <div class="card border-0 shadow-sm bg-<?= $config['color'] ?> text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?= $stats['by_type'][$key] ?? 0 ?></h3>
                        <small><i class="bx <?= $config['icon'] ?>"></i> <?= $config['label'] ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="col-md-2 col-6">
                <div class="card border-0 shadow-sm bg-dark text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?= $this->autre->formatBytes($stats['total_size']) ?></h3>
                        <small>Stockage</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== MESSAGES FLASH ==================== -->
        <?php $this->load->view('includes/backend/FlashMessages.php'); ?>

        <!-- ==================== FILTRES ==================== -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <span class="text-muted me-2"><i class="bx bx-filter me-1"></i>Filtrer:</span>
                    <button class="btn btn-sm btn-primary active filter-type" data-filter="all">
                        <i class="bx bx-grid-alt me-1"></i>Tous
                    </button>
                    <?php foreach ($type_configs as $key => $config): ?>
                    <button class="btn btn-sm btn-outline-<?= $config['color'] ?> filter-type" data-filter="<?= $key ?>">
                        <i class="bx <?= $config['icon'] ?> me-1"></i><?= $config['label'] ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ==================== TABLEAU PRINCIPAL ==================== -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 text-primary"><i class="bx bx-collection me-2"></i>Bibliothèque</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Upload Illimité</span>
                        <span class="badge bg-info"><i class="bx bx-image me-1"></i>Thumbnails Auto</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="autreTable" class="table table-hover align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="10%">Type</th>
                                <th width="12%">Aperçu</th>
                                <th width="23%">Titre</th>
                                <th width="10%">Source</th>
                                <th width="8%">Taille</th>
                                <th width="8%">Statut</th>
                                <th width="8%">WhatsApp</th>
                                <th width="8%">Site</th>
                                <th width="8%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($items)): $i = 1; foreach ($items as $value): 
                            $sous_type = $value['sous_type'] ?? 'other';
                            $config = $type_configs[$sous_type] ?? $type_configs['other'];
                            
                            $source_badge = ($sous_type === 'link') 
                                ? '<span class="badge bg-primary"><i class="bx bx-globe me-1"></i>URL</span>'
                                : (!empty($value['fichier']) 
                                    ? '<span class="badge bg-success"><i class="bx bx-upload me-1"></i>Fichier</span>'
                                    : '<span class="badge bg-light text-dark">-</span>');
                            
                            $taille_formatee = !empty($value['taille']) 
                                ? $this->autre->formatBytes($value['taille']) 
                                : '-';
                            
                            $thumb_url = !empty($value['miniature']) 
                                ? (strpos($value['miniature'], 'http') === 0 ? $value['miniature'] : base_url($value['miniature']))
                                : base_url('assets/images/file-default.png');
                        ?>
                            <tr data-type="<?= $sous_type ?>">
                                <td><?= $i++ ?></td>
                                <td>
                                    <span class="badge bg-<?= $config['color'] ?>">
                                        <i class="bx <?= $config['icon'] ?> me-1"></i><?= $config['label'] ?>
                                    </span>
                                </td>
                                
                                <td>
                                    <div class="position-relative preview-container" style="width: 80px; height: 60px;">
                                        <img src="<?= $thumb_url ?>" class="rounded border w-100 h-100" style="object-fit: cover;" alt="" loading="lazy">
                                        <?php if ($sous_type === 'photo'): ?>
                                            <div class="position-absolute top-0 end-0 p-1">
                                                <i class="bx bx-zoom-in text-primary bg-white rounded-circle p-1" style="font-size: 0.7rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="text-dark"><?= htmlspecialchars($value['titre'] ?? 'Sans titre') ?></strong>
                                        <?php if (!empty($value['description'])): ?>
                                            <small class="text-muted text-truncate" style="max-width: 250px;">
                                                <?= htmlspecialchars(substr($value['description'], 0, 60)) ?>...
                                            </small>
                                        <?php endif; ?>
                                        <?php if (!empty($value['categorie'])): ?>
                                            <small class="mt-1">
                                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($value['categorie']) ?></span>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td><?= $source_badge ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $taille_formatee ?></span></td>

                                <td class="text-center">
                                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_media'] ?>" class="text-decoration-none">
                                        <?php if (!empty($value['est_actif']) && $value['est_actif'] == 1): ?>
                                            <span class="badge bg-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactif</span>
                                        <?php endif; ?>
                                    </a>
                                </td>

                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input toggle-field" type="checkbox" 
                                               data-id="<?= $value['id_media'] ?>" 
                                               data-field="is_for_whatsapp"
                                               <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input toggle-field" type="checkbox" 
                                               data-id="<?= $value['id_media'] ?>" 
                                               data-field="is_for_website"
                                               <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>>
                                    </div>
                                </td>

                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_media'] ?>">
                                                    <i class="bx bx-show me-2 text-info"></i>Voir
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#edit_<?= $value['id_media'] ?>">
                                                    <i class="bx bx-edit me-2 text-primary"></i>Modifier
                                                </a>
                                            </li>
                                            <?php if (!empty($value['fichier'])): ?>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url($value['fichier']) ?>" download>
                                                    <i class="bx bx-download me-2 text-success"></i>Télécharger
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_media'] ?>">
                                                    <i class="bx bx-trash me-2"></i>Supprimer
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-- ==================== MODAL STATUS ==================== -->
                            <div class="modal fade" id="status_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Changer le statut</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('autre/ChangeStatus') ?>" method="POST">
                                            <div class="modal-body text-center">
                                                <i class="bx bx-question-mark-circle text-warning display-4"></i>
                                                <h5 class="mt-3">Changer le statut ?</h5>
                                                <p class="text-muted"><?= htmlspecialchars($value['titre']) ?></p>
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                                <input type="hidden" name="est_actif" value="<?= $value['est_actif'] ?>">
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-warning">Confirmer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== MODAL VIEW ==================== -->
                            <div class="modal fade" id="view_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-<?= $config['color'] ?> text-white">
                                            <h5 class="modal-title">
                                                <i class="bx <?= $config['icon'] ?> me-2"></i>
                                                <?= htmlspecialchars($value['titre']) ?>
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php if ($sous_type === 'link'): ?>
                                                <div class="alert alert-info">
                                                    <i class="bx bx-link-external me-2"></i>
                                                    <a href="<?= htmlspecialchars($value['lien']) ?>" target="_blank" class="fw-bold">
                                                        <?= htmlspecialchars($value['lien']) ?>
                                                    </a>
                                                </div>
                                                <?php if (!empty($value['miniature']) && strpos($value['miniature'], 'http') === 0): ?>
                                                    <img src="<?= $value['miniature'] ?>" class="img-fluid rounded" alt="Preview">
                                                <?php endif; ?>
                                                
                                            <?php elseif ($sous_type === 'texte'): ?>
                                                <div class="card">
                                                    <div class="card-body bg-light">
                                                        <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?= htmlspecialchars($value['contenu_texte'] ?? 'Aucun contenu') ?></pre>
                                                    </div>
                                                </div>
                                                
                                            <?php elseif ($sous_type === 'photo' && !empty($value['fichier'])): ?>
                                                <div class="text-center">
                                                    <img src="<?= base_url($value['fichier']) ?>" class="img-fluid rounded" alt="Photo" style="max-height: 500px;">
                                                    <?php if (!empty($value['dimensions'])): ?>
                                                        <p class="text-muted mt-2">
                                                            <i class="bx bx-ruler me-1"></i>
                                                            <?= $value['dimensions'] ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                                
                                            <?php elseif ($sous_type === 'book' && !empty($value['fichier'])): ?>
                                                <div class="text-center p-4">
                                                    <i class="bx bx-file-pdf text-danger display-1"></i>
                                                    <p class="mt-3">
                                                        <a href="<?= base_url($value['fichier']) ?>" target="_blank" class="btn btn-primary btn-lg">
                                                            <i class="bx bx-book-open me-2"></i>Ouvrir le PDF
                                                        </a>
                                                    </p>
                                                    <?php if (!empty($value['pages'])): ?>
                                                        <p class="text-muted"><?= $value['pages'] ?> pages</p>
                                                    <?php endif; ?>
                                                </div>
                                                
                                            <?php elseif (!empty($value['fichier'])): ?>
                                                <div class="text-center p-4">
                                                    <i class="bx bx-file text-secondary display-1"></i>
                                                    <p class="mt-3">
                                                        <a href="<?= base_url($value['fichier']) ?>" target="_blank" class="btn btn-primary">
                                                            <i class="bx bx-download me-2"></i>Télécharger
                                                        </a>
                                                    </p>
                                                    <p class="text-muted"><?= $taille_formatee ?></p>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning">Aucun contenu disponible</div>
                                            <?php endif; ?>

                                            <?php if (!empty($value['description'])): ?>
                                                <div class="mt-3">
                                                    <h6>Description:</h6>
                                                    <p class="text-muted"><?= nl2br(htmlspecialchars($value['description'])) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== MODAL EDIT ==================== -->
                            <div class="modal fade" id="edit_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-<?= $config['color'] ?> text-white">
                                            <h5 class="modal-title">
                                                <i class="bx <?= $config['icon'] ?> me-2"></i>
                                                Modifier <?= $config['label'] ?>
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('autre/Update') ?>" method="POST" class="edit-form" data-type="<?= $sous_type ?>">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                                
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="mb-3">
                                                            <label class="form-label">Titre *</label>
                                                            <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($value['titre']) ?>" required>
                                                        </div>
                                                        
                                                        <?php if ($sous_type === 'link'): ?>
                                                            <div class="mb-3">
                                                                <label class="form-label">URL *</label>
                                                                <input type="url" name="lien" class="form-control" value="<?= htmlspecialchars($value['lien']) ?>" required>
                                                            </div>
                                                        <?php elseif ($sous_type === 'texte'): ?>
                                                            <div class="mb-3">
                                                                <label class="form-label">Contenu</label>
                                                                <textarea name="contenu_texte" class="form-control" rows="6"><?= htmlspecialchars($value['contenu_texte'] ?? '') ?></textarea>
                                                            </div>
                                                        <?php else: ?>
                                                            <!-- Upload de fichier pour book, photo, other -->
                                                            <div class="mb-3 file-upload-section" data-max-size="<?= $config['max_size'] ?>" data-accept="<?= implode(',', $config['accept'] ?? ['*']) ?>">
                                                                <label class="form-label">Nouveau fichier (optionnel)</label>
                                                                <div class="upload-zone p-4 border rounded text-center bg-light">
                                                                    <input type="file" class="form-control file-input" id="edit_file_<?= $value['id_media'] ?>">
                                                                    <input type="hidden" name="uploaded_file_path" class="uploaded-path">
                                                                    <input type="hidden" name="miniature" class="uploaded-thumb">
                                                                    
                                                                    <div class="upload-prompt">
                                                                        <i class="bx bx-cloud-upload display-4 text-muted"></i>
                                                                        <p class="mb-1">Glissez-déposez ou cliquez pour sélectionner</p>
                                                                        <small class="text-muted">
                                                                            Max: <?= $this->autre->formatBytes($config['max_size']) ?> | 
                                                                            Types: <?= is_array($config['accept']) ? implode(', ', $config['accept']) : '*' ?>
                                                                        </small>
                                                                    </div>
                                                                    
                                                                    <div class="upload-progress mt-3 d-none">
                                                                        <div class="progress mb-2">
                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                                                        </div>
                                                                        <small class="upload-status">Initialisation...</small>
                                                                        <button type="button" class="btn btn-sm btn-danger mt-2 cancel-upload">Annuler</button>
                                                                    </div>
                                                                    
                                                                    <div class="upload-complete mt-3 d-none">
                                                                        <div class="alert alert-success mb-2">
                                                                            <i class="bx bx-check-circle me-1"></i>
                                                                            <span class="file-name"></span>
                                                                        </div>
                                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-file">Supprimer</button>
                                                                    </div>
                                                                </div>
                                                                
                                                                <?php if (!empty($value['fichier'])): ?>
                                                                    <div class="current-file mt-2">
                                                                        <small class="text-muted">
                                                                            <i class="bx bx-file me-1"></i>
                                                                            Fichier actuel: <?= basename($value['fichier']) ?>
                                                                        </small>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">Catégorie</label>
                                                            <input type="text" name="categorie" class="form-control" list="categories_list" value="<?= htmlspecialchars($value['categorie'] ?? '') ?>">
                                                            <datalist id="categories_list">
                                                                <?php foreach ($categories as $cat): ?>
                                                                    <option value="<?= htmlspecialchars($cat) ?>">
                                                                <?php endforeach; ?>
                                                            </datalist>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Date</label>
                                                            <input type="date" name="date_media" class="form-control" value="<?= $value['date_media'] ?? '' ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Crédits</label>
                                                            <input type="text" name="credits" class="form-control" value="<?= htmlspecialchars($value['credits'] ?? '') ?>">
                                                        </div>
                                                        
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox" name="est_actif" value="1" <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label">Actif</label>
                                                        </div>
                                                        
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1" <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label">WhatsApp</label>
                                                        </div>
                                                        
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox" name="is_for_website" value="1" <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label">Site Web</label>
                                                        </div>
                                                        
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox" name="a_partager_reseaux" value="1" <?= (!empty($value['a_partager_reseaux']) && $value['a_partager_reseaux'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label">Partager réseaux</label>
                                                        </div>
                                                        
                                                        <?php if (!empty($value['a_partager_reseaux']) && $value['a_partager_reseaux'] == 1): ?>
                                                            <div class="mb-3">
                                                                <label class="form-label">Message réseaux</label>
                                                                <textarea name="message_reseaux" class="form-control" rows="2"><?= htmlspecialchars($value['message_reseaux'] ?? '') ?></textarea>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== MODAL DELETE ==================== -->
                            <div class="modal fade" id="delete_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Supprimer</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('autre/Delete') ?>" method="POST">
                                            <div class="modal-body text-center">
                                                <i class="bx bx-error-circle text-danger display-4"></i>
                                                <h5 class="mt-3">Confirmer la suppression ?</h5>
                                                <p class="text-muted"><?= htmlspecialchars($value['titre']) ?></p>
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="bx bx-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-2">Aucun élément dans la bibliothèque</p>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#create_autre">
                                        <i class="bx bx-plus me-1"></i>Ajouter un élément
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==================== MODAL CREATE ==================== -->
        <div class="modal fade" id="create_autre" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvel Élément</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <!-- Sélection du type -->
                        <div class="type-selector p-4 bg-light border-bottom">
                            <h6 class="mb-3">Sélectionner le type de contenu:</h6>
                            <div class="row g-3">
                                <?php foreach ($type_configs as $key => $config): ?>
                                <div class="col-md-4 col-6">
                                    <div class="card type-card h-100 cursor-pointer border-0 shadow-sm" data-type="<?= $key ?>" onclick="selectType('<?= $key ?>')">
                                        <div class="card-body text-center">
                                            <i class="bx <?= $config['icon'] ?> display-5 text-<?= $config['color'] ?> mb-2"></i>
                                            <h6 class="mb-0"><?= $config['label'] ?></h6>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Formulaires par type -->
                        <div class="form-container p-4 d-none" id="form_container">
                            <form action="<?= base_url('autre/Create') ?>" method="POST" id="create_form" enctype="multipart/form-data">
                                <input type="hidden" name="sous_type" id="selected_type">
                                
                                <!-- Champs communs -->
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">Titre *</label>
                                            <input type="text" name="titre" class="form-control" required>
                                        </div>
                                        
                                        <!-- Champs spécifiques par type -->
                                        <div id="type_specific_fields">
                                            <!-- Link -->
                                            <div class="type-fields" data-type="link">
                                                <div class="mb-3">
                                                    <label class="form-label">URL *</label>
                                                    <input type="url" name="lien" class="form-control" placeholder="https://...">
                                                </div>
                                            </div>
                                            
                                            <!-- Texte -->
                                            <div class="type-fields" data-type="texte">
                                                <div class="mb-3">
                                                    <label class="form-label">Contenu</label>
                                                    <textarea name="contenu_texte" class="form-control" rows="8" placeholder="Votre texte..."></textarea>
                                                </div>
                                            </div>
                                            
                                            <!-- Upload fichiers (book, photo, other) -->
                                            <div class="type-fields" data-type="file">
                                                <div class="mb-3" id="file_upload_container">
                                                    <label class="form-label">Fichier *</label>
                                                    <div class="upload-zone p-5 border rounded-3 text-center bg-light position-relative" id="drop_zone">
                                                        <input type="file" id="create_file_input" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer">
                                                        <input type="hidden" name="uploaded_file_path" id="create_uploaded_path">
                                                        <input type="hidden" name="miniature" id="create_uploaded_thumb">
                                                        
                                                        <div id="upload_initial">
                                                            <i class="bx bx-cloud-upload display-3 text-primary mb-3"></i>
                                                            <h5>Glissez-déposez votre fichier ici</h5>
                                                            <p class="text-muted mb-2">ou cliquez pour parcourir</p>
                                                            <div id="file_constraints" class="badge bg-info text-dark">
                                                                Chargement...
                                                            </div>
                                                        </div>
                                                        
                                                        <div id="upload_progress" class="d-none">
                                                            <div class="mb-3">
                                                                <div class="progress" style="height: 20px;">
                                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="progress_bar" role="progressbar" style="width: 0%"></div>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-between text-muted small mb-2">
                                                                <span id="upload_status">Préparation...</span>
                                                                <span id="upload_percent">0%</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between text-muted small">
                                                                <span id="upload_speed">0 MB/s</span>
                                                                <span id="upload_remaining">Calcul...</span>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-danger mt-3" id="cancel_upload">
                                                                <i class="bx bx-x me-1"></i>Annuler l'upload
                                                            </button>
                                                        </div>
                                                        
                                                        <div id="upload_success" class="d-none">
                                                            <i class="bx bx-check-circle display-3 text-success mb-2"></i>
                                                            <h5 class="text-success">Upload terminé !</h5>
                                                            <p class="file-info mb-3"></p>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" id="remove_uploaded">
                                                                <i class="bx bx-trash me-1"></i>Supprimer et recommencer
                                                            </button>
                                                        </div>
                                                        
                                                        <div id="upload_error" class="d-none">
                                                            <i class="bx bx-error-circle display-3 text-danger mb-2"></i>
                                                            <h5 class="text-danger">Échec de l'upload</h5>
                                                            <p class="error-message mb-3"></p>
                                                            <button type="button" class="btn btn-primary btn-sm" onclick="resetUpload()">
                                                                <i class="bx bx-retry me-1"></i>Réessayer
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Catégorie</label>
                                            <input type="text" name="categorie" class="form-control" list="create_categories_list">
                                            <datalist id="create_categories_list">
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?= htmlspecialchars($cat) ?>">
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" name="date_media" class="form-control">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Crédits</label>
                                            <input type="text" name="credits" class="form-control">
                                        </div>
                                        
                                        <div class="card border-0 shadow-sm mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="bx bx-cog me-1"></i>Options</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" name="est_actif" value="1" checked>
                                                    <label class="form-check-label">Publier (actif)</label>
                                                </div>
                                                
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1">
                                                    <label class="form-check-label">Disponible WhatsApp</label>
                                                </div>
                                                
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked>
                                                    <label class="form-check-label">Afficher sur le site</label>
                                                </div>
                                                
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" name="a_partager_reseaux" value="1" id="share_social">
                                                    <label class="form-check-label">Partager sur réseaux</label>
                                                </div>
                                                
                                                <div class="mb-3 d-none" id="social_message_container">
                                                    <label class="form-label">Message réseaux</label>
                                                    <textarea name="message_reseaux" class="form-control" rows="2" placeholder="Message pour les réseaux sociaux..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <button type="button" class="btn btn-outline-secondary" onclick="backToTypeSelection()">
                                        <i class="bx bx-arrow-back me-1"></i>Retour
                                    </button>
                                    <div>
                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-primary" id="submit_create" disabled>
                                            <i class="bx bx-save me-1"></i>Créer l'élément
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<!-- ==================== JAVASCRIPT ==================== -->
<script>
// ==================== CONFIGURATION ====================
const CHUNK_SIZE = 2 * 1024 * 1024; // 2MB
const MAX_RETRIES = 3;
const RETRY_DELAY = 1000;

const TYPE_CONFIGS = <?= json_encode($type_configs) ?>;

let currentUpload = {
    id: null,
    file: null,
    chunks: [],
    uploadedChunks: [],
    abortController: null,
    startTime: null,
    lastChunkTime: null,
    bytesUploaded: 0
};

// ==================== SÉLECTION DU TYPE ====================
function selectType(type) {
    document.querySelectorAll('.type-card').forEach(card => {
        card.classList.remove('border-primary', 'border-3');
    });
    event.currentTarget.classList.add('border-primary', 'border-3');
    
    document.getElementById('selected_type').value = type;
    document.querySelector('.type-selector').classList.add('d-none');
    document.getElementById('form_container').classList.remove('d-none');
    
    // Afficher les champs spécifiques
    document.querySelectorAll('.type-fields').forEach(el => el.classList.add('d-none'));
    
    if (type === 'link') {
        document.querySelector('[data-type="link"]').classList.remove('d-none');
        document.getElementById('submit_create').disabled = false;
    } else if (type === 'texte') {
        document.querySelector('[data-type="texte"]').classList.remove('d-none');
        document.getElementById('submit_create').disabled = false;
    } else {
        // book, photo, other - upload de fichier
        document.querySelector('[data-type="file"]').classList.remove('d-none');
        setupFileUpload(type);
        document.getElementById('submit_create').disabled = true;
    }
    
    // Mettre à jour les contraintes d'upload
    updateFileConstraints(type);
}

function backToTypeSelection() {
    document.querySelector('.type-selector').classList.remove('d-none');
    document.getElementById('form_container').classList.add('d-none');
    resetUpload();
}

function updateFileConstraints(type) {
    const config = TYPE_CONFIGS[type];
    const constraintsEl = document.getElementById('file_constraints');
    
    if (config) {
        const maxSize = formatBytes(config.max_size);
        const accept = config.accept === '*' ? 'Tous types' : config.accept.join(', ');
        constraintsEl.textContent = `Max: ${maxSize} | Types: ${accept}`;
        
        const fileInput = document.getElementById('create_file_input');
        if (config.accept && config.accept !== '*') {
            fileInput.accept = config.accept.map(ext => `.${ext}`).join(',');
        } else {
            fileInput.accept = '';
        }
    }
}

// ==================== UPLOAD CHUNKÉ ====================
function setupFileUpload(type) {
    const dropZone = document.getElementById('drop_zone');
    const fileInput = document.getElementById('create_file_input');
    
    // Drag & drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-primary', 'bg-white');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-primary', 'bg-white');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-white');
        const files = e.dataTransfer.files;
        if (files.length) handleFileSelect(files[0], type);
    });
    
    // Click to select
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) handleFileSelect(e.target.files[0], type);
    });
    
    // Cancel upload
    document.getElementById('cancel_upload').addEventListener('click', cancelUpload);
    
    // Remove uploaded file
    document.getElementById('remove_uploaded').addEventListener('click', resetUpload);
}

async function handleFileSelect(file, type) {
    const config = TYPE_CONFIGS[type];
    
    // Validation
    if (config.max_size > 0 && file.size > config.max_size) {
        showUploadError(`Fichier trop grand. Maximum: ${formatBytes(config.max_size)}`);
        return;
    }
    
    if (config.accept && config.accept !== '*') {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!config.accept.includes(ext)) {
            showUploadError(`Type non supporté. Types acceptés: ${config.accept.join(', ')}`);
            return;
        }
    }
    
    currentUpload.file = file;
    currentUpload.chunks = createChunks(file);
    currentUpload.uploadedChunks = [];
    currentUpload.abortController = new AbortController();
    currentUpload.startTime = Date.now();
    currentUpload.bytesUploaded = 0;
    
    showUploadProgress();
    
    try {
        // 1. Initialiser l'upload
        const initResponse = await initUpload(file, type);
        if (!initResponse.success) {
            throw new Error(initResponse.message || 'Erreur initialisation');
        }
        
        currentUpload.id = initResponse.upload_id;
        
        // 2. Uploader les chunks
        await uploadAllChunks(initResponse.total_chunks);
        
        // 3. Finaliser l'upload
        const completeResponse = await completeUpload();
        if (!completeResponse.success) {
            throw new Error(completeResponse.message || 'Erreur finalisation');
        }
        
        // 4. Succès
        showUploadSuccess(completeResponse);
        document.getElementById('create_uploaded_path').value = completeResponse.file_path;
        document.getElementById('create_uploaded_thumb').value = completeResponse.miniature || '';
        document.getElementById('submit_create').disabled = false;
        
    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error('Upload error:', error);
            showUploadError(error.message || 'Erreur lors de l\\'upload');
        }
    }
}

function createChunks(file) {
    const chunks = [];
    let start = 0;
    while (start < file.size) {
        chunks.push(file.slice(start, start + CHUNK_SIZE));
        start += CHUNK_SIZE;
    }
    return chunks;
}

async function initUpload(file, type) {
    const formData = new FormData();
    formData.append('file_name', file.name);
    formData.append('file_size', file.size);
    formData.append('file_hash', await calculateFileHash(file));
    formData.append('sous_type', type);
    
    const response = await fetch('<?= base_url('autre/initUpload') ?>', {
        method: 'POST',
        body: formData
    });
    
    return await response.json();
}

async function uploadAllChunks(totalChunks) {
    const concurrency = 3; // Nombre de chunks simultanés
    const queue = [];
    
    for (let i = 0; i < totalChunks; i++) {
        if (currentUpload.uploadedChunks.includes(i)) continue;
        
        queue.push(uploadChunkWithRetry(i));
        
        if (queue.length >= concurrency || i === totalChunks - 1) {
            await Promise.all(queue);
            queue.length = 0;
        }
    }
}

async function uploadChunkWithRetry(chunkIndex, attempt = 1) {
    try {
        await uploadChunk(chunkIndex);
    } catch (error) {
        if (attempt < MAX_RETRIES) {
            await delay(RETRY_DELAY * attempt);
            return uploadChunkWithRetry(chunkIndex, attempt + 1);
        }
        throw new Error(`Échec chunk ${chunkIndex} après ${MAX_RETRIES} tentatives`);
    }
}

async function uploadChunk(chunkIndex) {
    const chunk = currentUpload.chunks[chunkIndex];
    const formData = new FormData();
    
    formData.append('upload_id', currentUpload.id);
    formData.append('chunk_index', chunkIndex);
    formData.append('chunk_hash', await calculateChunkHash(chunk));
    formData.append('chunk', chunk, `chunk_${chunkIndex}`);
    
    const response = await fetch('<?= base_url('autre/uploadChunk') ?>', {
        method: 'POST',
        body: formData,
        signal: currentUpload.abortController.signal
    });
    
    const result = await response.json();
    
    if (!result.success) {
        throw new Error(result.message || `Erreur chunk ${chunkIndex}`);
    }
    
    currentUpload.uploadedChunks.push(chunkIndex);
    currentUpload.bytesUploaded += chunk.size;
    currentUpload.lastChunkTime = Date.now();
    
    updateProgress();
}

async function completeUpload() {
    const formData = new FormData();
    formData.append('upload_id', currentUpload.id);
    
    const response = await fetch('<?= base_url('autre/completeUpload') ?>', {
        method: 'POST',
        body: formData
    });
    
    return await response.json();
}

async function cancelUpload() {
    if (currentUpload.abortController) {
        currentUpload.abortController.abort();
    }
    
    if (currentUpload.id) {
        const formData = new FormData();
        formData.append('upload_id', currentUpload.id);
        
        await fetch('<?= base_url('autre/cancelUpload') ?>', {
            method: 'POST',
            body: formData
        });
    }
    
    resetUpload();
}

function resetUpload() {
    currentUpload = {
        id: null,
        file: null,
        chunks: [],
        uploadedChunks: [],
        abortController: null,
        startTime: null,
        lastChunkTime: null,
        bytesUploaded: 0
    };
    
    document.getElementById('upload_initial').classList.remove('d-none');
    document.getElementById('upload_progress').classList.add('d-none');
    document.getElementById('upload_success').classList.add('d-none');
    document.getElementById('upload_error').classList.add('d-none');
    document.getElementById('create_file_input').value = '';
    document.getElementById('create_uploaded_path').value = '';
    document.getElementById('create_uploaded_thumb').value = '';
    document.getElementById('submit_create').disabled = true;
}

// ==================== UI HELPERS ====================
function showUploadProgress() {
    document.getElementById('upload_initial').classList.add('d-none');
    document.getElementById('upload_progress').classList.remove('d-none');
    document.getElementById('upload_success').classList.add('d-none');
    document.getElementById('upload_error').classList.add('d-none');
}

function updateProgress() {
    const total = currentUpload.chunks.length;
    const uploaded = currentUpload.uploadedChunks.length;
    const percent = Math.round((uploaded / total) * 100);
    
    document.getElementById('progress_bar').style.width = `${percent}%`;
    document.getElementById('upload_percent').textContent = `${percent}%`;
    document.getElementById('upload_status').textContent = `Uploadé ${uploaded}/${total} chunks`;
    
    // Calculer la vitesse
    if (currentUpload.lastChunkTime && currentUpload.startTime) {
        const elapsed = (currentUpload.lastChunkTime - currentUpload.startTime) / 1000;
        const speed = currentUpload.bytesUploaded / elapsed;
        document.getElementById('upload_speed').textContent = `${formatBytes(speed)}/s`;
        
        const remaining = (total - uploaded) * (currentUpload.file.size / total) / speed;
        document.getElementById('upload_remaining').textContent = formatTime(remaining);
    }
}

function showUploadSuccess(response) {
    document.getElementById('upload_progress').classList.add('d-none');
    document.getElementById('upload_success').classList.remove('d-none');
    
    const info = document.querySelector('#upload_success .file-info');
    info.innerHTML = `
        <strong>${escapeHtml(response.file_name)}</strong><br>
        <small class="text-muted">${response.file_size_formatted}</small>
        ${response.miniature ? '<br><small class="text-success"><i class="bx bx-image me-1"></i>Miniature générée</small>' : ''}
    `;
}

function showUploadError(message) {
    document.getElementById('upload_progress').classList.add('d-none');
    document.getElementById('upload_error').classList.remove('d-none');
    document.querySelector('#upload_error .error-message').textContent = message;
}

// ==================== UTILITAIRES ====================
async function calculateFileHash(file) {
    // Simplification: utiliser le nom + taille + date comme hash
    return btoa(`${file.name}_${file.size}_${file.lastModified}`).replace(/[^a-zA-Z0-9]/g, '').substring(0, 16);
}

async function calculateChunkHash(chunk) {
    // CRC32 simplifié ou autre méthode légère
    return 'chunk_hash_placeholder';
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatTime(seconds) {
    if (!isFinite(seconds) || seconds < 0) return 'Calcul...';
    if (seconds < 60) return Math.round(seconds) + 's';
    if (seconds < 3600) return Math.round(seconds / 60) + 'min';
    return Math.round(seconds / 3600) + 'h';
}

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ==================== FILTRES ====================
document.querySelectorAll('.filter-type').forEach(btn => {
    btn.addEventListener('click', function() {
        // Mise à jour visuelle des boutons
        document.querySelectorAll('.filter-type').forEach(b => {
            b.classList.remove('active', 'btn-primary');
            b.classList.add('btn-outline-' + (b.dataset.filter === 'all' ? 'primary' : TYPE_CONFIGS[b.dataset.filter]?.color || 'secondary'));
        });
        
        this.classList.remove('btn-outline-' + (this.dataset.filter === 'all' ? 'primary' : TYPE_CONFIGS[this.dataset.filter]?.color || 'secondary'));
        this.classList.add('active', 'btn-primary');
        
        // Filtrage du tableau
        const filter = this.dataset.filter;
        document.querySelectorAll('#autreTable tbody tr[data-type]').forEach(row => {
            if (filter === 'all' || row.dataset.type === filter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

// ==================== TOGGLES AJAX ====================
document.querySelectorAll('.toggle-field').forEach(toggle => {
    toggle.addEventListener('change', async function() {
        const id = this.dataset.id;
        const field = this.dataset.field;
        const value = this.checked ? 1 : 0;
        
        try {
            const response = await fetch('<?= base_url('autre/toggleField') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&field=${field}&value=${value}`
            });
            
            const result = await response.json();
            
            if (!result.success) {
                this.checked = !this.checked;
                alert('Erreur lors de la mise à jour');
            }
        } catch (error) {
            console.error('Toggle error:', error);
            this.checked = !this.checked;
            alert('Erreur réseau');
        }
    });
});

// ==================== GESTION SOCIAUX ====================
document.getElementById('share_social')?.addEventListener('change', function() {
    document.getElementById('social_message_container').classList.toggle('d-none', !this.checked);
});

// ==================== UPLOAD DANS MODALS EDIT ====================
document.querySelectorAll('.edit-form').forEach(form => {
    const fileSection = form.querySelector('.file-upload-section');
    if (!fileSection) return;
    
    const maxSize = parseInt(fileSection.dataset.maxSize);
    const acceptTypes = fileSection.dataset.accept.split(',');
    const fileInput = form.querySelector('.file-input');
    const uploadZone = form.querySelector('.upload-zone');
    
    // Setup similaire au create mais simplifié
    fileInput.addEventListener('change', async function() {
        if (!this.files.length) return;
        
        const file = this.files[0];
        const type = form.dataset.type;
        
        // Validation
        if (maxSize > 0 && file.size > maxSize) {
            alert(`Fichier trop grand. Max: ${formatBytes(maxSize)}`);
            return;
        }
        
        // UI de progression
        const progressDiv = form.querySelector('.upload-progress');
        const completeDiv = form.querySelector('.upload-complete');
        const promptDiv = form.querySelector('.upload-prompt');
        
        progressDiv.classList.remove('d-none');
        promptDiv.classList.add('d-none');
        
        try {
            // Upload chunked pour edit (simplifié - réutilise les fonctions globales)
            currentUpload.file = file;
            currentUpload.chunks = createChunks(file);
            currentUpload.uploadedChunks = [];
            currentUpload.abortController = new AbortController();
            
            const initResponse = await initUpload(file, type);
            if (!initResponse.success) throw new Error(initResponse.message);
            
            currentUpload.id = initResponse.upload_id;
            
            await uploadAllChunks(initResponse.total_chunks);
            const completeResponse = await completeUpload();
            
            if (!completeResponse.success) throw new Error(completeResponse.message);
            
            // Succès
            form.querySelector('.uploaded-path').value = completeResponse.file_path;
            form.querySelector('.uploaded-thumb').value = completeResponse.miniature || '';
            
            progressDiv.classList.add('d-none');
            completeDiv.classList.remove('d-none');
            completeDiv.querySelector('.file-name').textContent = completeResponse.file_name;
            
        } catch (error) {
            progressDiv.classList.add('d-none');
            promptDiv.classList.remove('d-none');
            alert('Erreur upload: ' + error.message);
        }
    });
    
    // Cancel et remove
    form.querySelector('.cancel-upload')?.addEventListener('click', function() {
        cancelUpload();
        form.querySelector('.upload-progress').classList.add('d-none');
        form.querySelector('.upload-prompt').classList.remove('d-none');
        fileInput.value = '';
    });
    
    form.querySelector('.remove-file')?.addEventListener('click', function() {
        form.querySelector('.upload-complete').classList.add('d-none');
        form.querySelector('.upload-prompt').classList.remove('d-none');
        form.querySelector('.uploaded-path').value = '';
        form.querySelector('.uploaded-thumb').value = '';
        fileInput.value = '';
    });
});

// ==================== DATA TABLE ====================
$(document).ready(function() {
    $('#autreTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
});
</script>