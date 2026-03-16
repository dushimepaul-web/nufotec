<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
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

        <!-- Statistiques -->
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
                        <h3 class="mb-0"><?= $this->Autre->formatBytes($stats['total_size']) ?></h3>
                        <small>Stockage</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Flash -->
        <?php $this->load->view('includes/backend/FlashMessages.php'); ?>

        <!-- Filtres -->
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

        <!-- Card Principale -->
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
                                ? $this->Autre->formatBytes($value['taille']) 
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

                            <!-- Modal Status -->
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

                            <!-- Modal View -->
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
                                                
                                            <?php elseif (!empty($value['fichier