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
                        <li class="breadcrumb-item active" aria-current="page">Gestion des Vidéos</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a class="btn btn-primary btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_video">
                    <i class="bx bx-plus"></i> Nouvelle Vidéo
                </a>
                <a class="btn btn-outline-info btn-sm ms-2" href="<?= base_url('video/diagnostics') ?>" target="_blank">
                    <i class="bx bx-test-tube"></i> Diagnostic
                </a>
            </div>
        </div>

        <!-- Messages Flash -->
        <?php $this->load->view('includes/backend/FlashMessages.php'); ?>

        <!-- Card Principale -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 text-primary"><i class="bx bx-video me-2"></i>Liste des Vidéos</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Upload Illimité</span>
                        <span class="badge bg-info"><i class="bx bx-chip me-1"></i>2MB Chunks</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="videosTable" class="table table-hover align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="12%">Miniature</th>
                                <th width="20%">Titre</th>
                                <th width="10%">Source</th>
                                <th width="10%">Catégorie</th>
                                <th width="8%">Taille</th>
                                <th width="8%">Statut</th>
                                <th width="8%">WhatsApp</th>
                                <th width="8%">Site Web</th>
                                <th width="11%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($videos)): $i = 1; foreach ($videos as $value): 
                            $is_upload = !empty($value['fichier']);
                            $is_link = !empty($value['lien']);
                            
                            // Badge source
                            $source_badge = $is_upload 
                                ? '<span class="badge bg-success"><i class="bx bx-upload me-1"></i>Upload</span>'
                                : ($is_link 
                                    ? '<span class="badge bg-info"><i class="bx bx-link me-1"></i>Lien</span>'
                                    : '<span class="badge bg-secondary">Inconnu</span>');
                            
                            // Formatage taille
                            $taille_formatee = '-';
                            if (!empty($value['taille'])) {
                                $taille = $value['taille'];
                                if ($taille >= 1073741824) {
                                    $taille_formatee = number_format($taille / 1073741824, 2) . ' GB';
                                } elseif ($taille >= 1048576) {
                                    $taille_formatee = number_format($taille / 1048576, 2) . ' MB';
                                } elseif ($taille >= 1024) {
                                    $taille_formatee = number_format($taille / 1024, 2) . ' KB';
                                } else {
                                    $taille_formatee = $taille . ' B';
                                }
                            }
                            
                            // Miniature
                            $thumb_url = base_url('assets/images/video-placeholder.jpg');
                            if (!empty($value['miniature'])) {
                                $thumb_url = (strpos($value['miniature'], 'http') === 0) 
                                    ? $value['miniature'] 
                                    : base_url($value['miniature']);
                            } elseif ($is_link && preg_match('/youtube/', $value['lien'])) {
                                preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\s]+)/', $value['lien'], $matches);
                                if (!empty($matches[1])) {
                                    $thumb_url = "https://img.youtube.com/vi/{$matches[1]}/mqdefault.jpg";
                                }
                            }
                        ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                
                                <td>
                                    <div class="position-relative video-thumbnail-container" style="width: 100px; height: 60px;">
                                        <img src="<?= $thumb_url ?>" class="rounded border w-100 h-100" style="object-fit: cover;" alt="Miniature" loading="lazy">
                                        <div class="play-overlay">
                                            <i class="bx bx-play-circle"></i>
                                        </div>
                                        <?php if ($is_upload && !empty($value['taille']) && $value['taille'] > 104857600): ?>
                                            <div class="hd-badge">
                                                <span class="badge bg-warning" title="Fichier volumineux"><i class="bx bx-hdd"></i></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="text-dark"><?= htmlspecialchars($value['titre'] ?? 'Sans titre') ?></strong>
                                        <?php if (!empty($value['description'])): ?>
                                            <small class="text-muted text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($value['description']) ?>">
                                                <?= htmlspecialchars(substr($value['description'], 0, 50)) ?>...
                                            </small>
                                        <?php endif; ?>
                                        <?php if (!empty($value['credits'])): ?>
                                            <small class="text-muted"><i class="bx bx-user me-1"></i><?= htmlspecialchars($value['credits']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td><?= $source_badge ?></td>

                                <td>
                                    <?php if (!empty($value['categorie'])): ?>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($value['categorie']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border"><?= $taille_formatee ?></span>
                                </td>

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
                                        <form action="<?= base_url('video/ChangeStatus') ?>" method="POST">
                                            <div class="modal-body text-center">
                                                <i class="bx bx-question-mark-circle text-warning display-4"></i>
                                                <h5 class="mt-3">Voulez-vous changer le statut ?</h5>
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
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= htmlspecialchars($value['titre']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="ratio ratio-16x9 mb-3 bg-dark rounded video-player-container">
                                                <?php if ($is_upload && !empty($value['fichier'])): ?>
                                                    <video controls poster="<?= $thumb_url ?>" class="rounded" preload="metadata">
                                                        <source src="<?= base_url($value['fichier']) ?>" type="<?= $value['mime_type'] ?? 'video/mp4' ?>">
                                                        Votre navigateur ne supporte pas la lecture vidéo.
                                                    </video>
                                                <?php elseif ($is_link): ?>
                                                    <?php 
                                                    $embed_url = $value['lien'];
                                                    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $value['lien'], $matches)) {
                                                        $embed_url = "https://www.youtube.com/embed/{$matches[1]}";
                                                    } elseif (preg_match('/vimeo\.com\/(\d+)/', $value['lien'], $matches)) {
                                                        $embed_url = "https://player.vimeo.com/video/{$matches[1]}";
                                                    }
                                                    ?>
                                                    <iframe src="<?= $embed_url ?>" allowfullscreen class="rounded"></iframe>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($value['description'])): ?>
                                                <p><?= nl2br(htmlspecialchars($value['description'])) ?></p>
                                            <?php endif; ?>
                                            
                                            <div class="row text-muted small">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Date:</strong> <?= !empty($value['date_media']) ? date('d/m/Y', strtotime($value['date_media'])) : '-' ?></p>
                                                    <p class="mb-1"><strong>Crédits:</strong> <?= htmlspecialchars($value['credits'] ?? '-') ?></p>
                                                    <p class="mb-1"><strong>Créé le:</strong> <?= !empty($value['created_at']) ? date('d/m/Y H:i', strtotime($value['created_at'])) : '-' ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Taille:</strong> <?= $taille_formatee ?></p>
                                                    <p class="mb-1"><strong>Type:</strong> <?= $value['mime_type'] ?? '-' ?></p>
                                                    <p class="mb-1"><strong>Catégorie:</strong> <?= htmlspecialchars($value['categorie'] ?? '-') ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Edit -->
                            <div class="modal fade" id="edit_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la vidéo</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('video/Update') ?>" method="POST" class="video-form" data-mode="edit">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($value['titre']) ?>" required maxlength="255">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Type de source</label>
                                                    <select class="form-select source-type-selector" name="type_source" data-target="<?= $value['id_media'] ?>">
                                                        <option value="upload" <?= $is_upload ? 'selected' : '' ?>>Fichier uploadé</option>
                                                        <option value="link" <?= $is_link ? 'selected' : '' ?>>Lien externe</option>
                                                    </select>
                                                </div>

                                                <!-- Upload Section avec Chunked -->
                                                <div class="upload-section mb-3" id="upload_section_<?= $value['id_media'] ?>" style="<?= $is_upload ? '' : 'display:none;' ?>">
                                                    <label class="form-label fw-bold">Nouveau fichier vidéo</label>
                                                    
                                                    <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_<?= $value['id_media'] ?>">
                                                        <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                                                        <p class="mb-2">Glissez-déposez une vidéo ici ou <span class="text-primary fw-bold browse-text">cliquez pour parcourir</span></p>
                                                        <small class="text-muted d-block">Formats: MP4, WebM, MOV, AVI, MKV... (Taille illimitée)</small>
                                                        
                                                        <input type="file" class="form-control d-none file-input" accept="video/*" data-upload-id="<?= $value['id_media'] ?>">
                                                        <input type="hidden" name="uploaded_file_path" class="uploaded-path">
                                                        <input type="hidden" name="thumbnail" class="thumbnail-path">
                                                        
                                                        <!-- Zone de progression -->
                                                        <div class="upload-progress mt-3 d-none">
                                                            <div class="d-flex justify-content-between small mb-1">
                                                                <span class="upload-status fw-bold">Préparation...</span>
                                                                <span class="upload-percent text-primary">0%</span>
                                                            </div>
                                                            <div class="progress" style="height: 10px;">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                                                            </div>
                                                            <div class="d-flex justify-content-between small text-muted mt-1">
                                                                <span class="upload-chunks">Chunk 0/0</span>
                                                                <span class="upload-speed">0 MB/s</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Info fichier actuel -->
                                                        <?php if ($is_upload): ?>
                                                            <div class="current-file mt-3 p-2 bg-white rounded border d-flex align-items-center">
                                                                <i class="bx bx-video text-success fs-4 me-2"></i>
                                                                <div class="text-start flex-grow-1">
                                                                    <small class="d-block fw-bold text-truncate"><?= basename($value['fichier']) ?></small>
                                                                    <small class="text-muted"><?= $taille_formatee ?></small>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Info nouveau fichier -->
                                                        <div class="new-file-info mt-2 d-none">
                                                            <div class="alert alert-success mb-0 py-2 d-flex align-items-center">
                                                                <i class="bx bx-check-circle me-2"></i>
                                                                <div class="text-start">
                                                                    <div class="new-file-name fw-bold text-truncate"></div>
                                                                    <div class="new-file-size small"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mt-2 d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger cancel-upload d-none">
                                                            <i class="bx bx-x me-1"></i>Annuler
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary pause-upload d-none">
                                                            <i class="bx bx-pause me-1"></i>Pause
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-primary resume-upload d-none">
                                                            <i class="bx bx-play me-1"></i>Reprendre
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Link Section -->
                                                <div class="link-section mb-3" id="link_section_<?= $value['id_media'] ?>" style="<?= $is_link ? '' : 'display:none;' ?>">
                                                    <label class="form-label fw-bold">Lien vidéo (YouTube, Vimeo...)</label>
                                                    <input type="url" class="form-control" name="lien" value="<?= htmlspecialchars($value['lien'] ?? '') ?>" placeholder="https://...">
                                                    <small class="text-muted">La miniature sera extraite automatiquement</small>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">Catégorie</label>
                                                        <input type="text" class="form-control" name="categorie" value="<?= htmlspecialchars($value['categorie'] ?? '') ?>" list="categories_list">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">Date du média</label>
                                                        <input type="date" class="form-control" name="date_media" value="<?= $value['date_media'] ?? '' ?>">
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Description</label>
                                                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Crédits</label>
                                                    <input type="text" class="form-control" name="credits" value="<?= htmlspecialchars($value['credits'] ?? '') ?>">
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="est_actif" value="1" <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label fw-bold">Vidéo active</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input share-toggle" type="checkbox" name="a_partager_reseaux" value="1" <?= (!empty($value['a_partager_reseaux']) && $value['a_partager_reseaux'] == 1) ? 'checked' : '' ?> data-target="edit_msg_<?= $value['id_media'] ?>">
                                                            <label class="form-check-label fw-bold">Partager sur réseaux</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3" id="edit_msg_<?= $value['id_media'] ?>" style="<?= (!empty($value['a_partager_reseaux']) && $value['a_partager_reseaux'] == 1) ? '' : 'display:none;' ?>">
                                                    <label class="form-label fw-bold">Message réseaux sociaux</label>
                                                    <textarea class="form-control" name="message_reseaux" rows="2" maxlength="280"><?= htmlspecialchars($value['message_reseaux'] ?? '') ?></textarea>
                                                    <small class="text-muted">Max 280 caractères</small>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1" <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label fw-bold">Pour WhatsApp</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_for_website" value="1" <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label fw-bold">Pour Site Web</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-save me-1"></i>Enregistrer
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Delete -->
                            <div class="modal fade" id="delete_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('video/Delete') ?>" method="POST">
                                            <div class="modal-body text-center">
                                                <i class="bx bx-error-circle text-danger display-4"></i>
                                                <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                                <p class="text-muted"><?= htmlspecialchars($value['titre']) ?></p>
                                                <div class="alert alert-warning">
                                                    <i class="bx bx-info-circle me-2"></i>
                                                    Le fichier sera définitivement supprimé.
                                                </div>
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="bx bx-trash me-1"></i>Supprimer
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="bx bx-video-off fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">Aucune vidéo trouvée</p>
                                    <a href="javascript:;" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#create_video">
                                        <i class="bx bx-plus me-1"></i>Ajouter une vidéo
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="create_video" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouvelle Vidéo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= base_url('video/Create') ?>" method="POST" class="video-form" id="create_form" data-mode="create">
                    <div class="modal-body">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="titre" required maxlength="255" placeholder="Titre de la vidéo">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Type de source <span class="text-danger">*</span></label>
                            <select class="form-select source-type-selector" name="type_source" data-target="create" id="create_type_source">
                                <option value="upload" selected>Uploader un fichier (Illimité)</option>
                                <option value="link">Lien externe (YouTube, Vimeo...)</option>
                            </select>
                        </div>

                        <!-- Upload Section avec Chunked -->
                        <div class="upload-section mb-3" id="upload_section_create">
                            <label class="form-label fw-bold">Fichier vidéo</label>
                            
                            <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_create">
                                <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                                <p class="mb-2">Glissez-déposez une vidéo ici ou <span class="text-primary fw-bold browse-text">cliquez pour parcourir</span></p>
                                <small class="text-muted d-block mb-2">Formats: MP4, WebM, OGG, MOV, AVI, MKV, FLV, M4V, 3GP, WMV</small>
                                <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Pas de limite de taille (chunks de 2MB)</span>
                                
                                <input type="file" class="form-control d-none file-input" id="create_file_input" accept="video/*" data-upload-id="create">
                                <input type="hidden" name="uploaded_file_path" id="create_uploaded_path">
                                <input type="hidden" name="thumbnail" id="create_thumbnail_path">
                                
                                <!-- Zone de progression détaillée -->
                                <div class="upload-progress mt-3 d-none" id="create_progress_container">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span id="create_upload_status" class="fw-bold">Préparation...</span>
                                        <span id="create_upload_percent" class="text-primary">0%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="create_progress_bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mt-1">
                                        <span id="create_chunks_info">Chunk 0/0</span>
                                        <span id="create_upload_speed">0 MB/s</span>
                                    </div>
                                    <div class="small text-primary mt-1" id="create_size_info">0 MB / 0 MB</div>
                                </div>
                                
                                <!-- Info fichier sélectionné -->
                                <div class="file-info mt-2 d-none" id="create_file_info">
                                    <div class="alert alert-info mb-0 py-2 d-flex align-items-center justify-content-center">
                                        <i class="bx bx-file me-2"></i>
                                        <div>
                                            <div id="create_file_name" class="fw-bold text-truncate" style="max-width: 300px;"></div>
                                            <div id="create_file_size" class="small"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Succès upload -->
                                <div class="upload-success mt-2 d-none" id="create_upload_success">
                                    <div class="alert alert-success mb-0 py-2 d-flex align-items-center justify-content-center">
                                        <i class="bx bx-check-circle me-2 fs-5"></i>
                                        <span class="fw-bold">Upload terminé avec succès!</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-2 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-danger cancel-upload d-none" id="create_cancel">
                                    <i class="bx bx-x me-1"></i>Annuler
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary pause-upload d-none" id="create_pause">
                                    <i class="bx bx-pause me-1"></i>Pause
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary resume-upload d-none" id="create_resume">
                                    <i class="bx bx-play me-1"></i>Reprendre
                                </button>
                            </div>
                        </div>

                        <!-- Link Section -->
                        <div class="link-section mb-3" id="link_section_create" style="display:none;">
                            <label class="form-label fw-bold">Lien vidéo <span class="text-muted">(YouTube, Vimeo, etc.)</span></label>
                            <input type="url" class="form-control" name="lien" placeholder="https://www.youtube.com/watch?v=...">
                            <small class="text-muted">La miniature sera extraite automatiquement depuis le lien</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Catégorie</label>
                                <input type="text" class="form-control" name="categorie" list="categories_list" placeholder="Ex: Documentaire, Interview...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date du média</label>
                                <input type="date" class="form-control" name="date_media">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Description de la vidéo..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Crédits / Auteur</label>
                            <input type="text" class="form-control" name="credits" placeholder="Ex: Réalisé par...">
                        </div>

                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-share-alt me-2"></i>Options de partage</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input share-toggle" type="checkbox" name="a_partager_reseaux" value="1" data-target="create_msg_reseaux">
                                            <label class="form-check-label fw-bold">Partager sur réseaux sociaux</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3" id="create_msg_reseaux" style="display:none;">
                                    <label class="form-label fw-bold">Message pour réseaux sociaux</label>
                                    <textarea class="form-control" name="message_reseaux" rows="2" maxlength="280" placeholder="Texte à publier avec la vidéo..."></textarea>
                                    <small class="text-muted">Maximum 280 caractères (Twitter/X)</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1">
                                            <label class="form-check-label fw-bold">
                                                <i class="bx bxl-whatsapp text-success me-1"></i>Disponible pour WhatsApp
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked>
                                            <label class="form-check-label fw-bold">
                                                <i class="bx bx-globe text-primary me-1"></i>Visible sur le site web
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success" id="create_submit">
                            <i class="bx bx-save me-1"></i>Créer la vidéo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Datalist catégories -->
    <datalist id="categories_list">
        <option value="Documentaire">
        <option value="Interview">
        <option value="Reportage">
        <option value="Tutoriel">
        <option value="Promotion">
        <option value="Événement">
        <option value="Webinaire">
        <option value="Podcast">
        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>">
        <?php endforeach; endif; ?>
    </datalist>

</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<script>
/**
 * =============================================================================
 * VIDEO UPLOAD MANAGER - VERSION PROFESSIONNELLE 3.0
 * Architecture: Modular ES6+ Class with Web Workers support
 * Inspired by: YouTube, Vimeo, AWS S3 Multipart Upload
 * =============================================================================
 */

/**
 * Classe utilitaire pour la gestion des fichiers
 */
class FileUtils {
    /**
     * Formate une taille en bytes en format lisible
     */
    static formatSize(bytes, decimals = 2) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i];
    }

    /**
     * Calcule le hash CRC32 d'un blob (pour vérification d'intégrité)
     */
    static async calculateCRC32(blob) {
        const buffer = await blob.arrayBuffer();
        const view = new Uint8Array(buffer);
        let crc = 0 ^ (-1);
        
        const table = FileUtils.getCRC32Table();
        
        for (let i = 0; i < view.length; i++) {
            crc = (crc >>> 8) ^ table[(crc ^ view[i]) & 0xFF];
        }
        
        return ((crc ^ (-1)) >>> 0).toString(16).padStart(8, '0');
    }

    /**
     * Table CRC32 précalculée
     */
    static getCRC32Table() {
        if (FileUtils.crcTable) return FileUtils.crcTable;
        
        FileUtils.crcTable = new Uint32Array(256);
        for (let i = 0; i < 256; i++) {
            let c = i;
            for (let j = 0; j < 8; j++) {
                c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1);
            }
            FileUtils.crcTable[i] = c;
        }
        return FileUtils.crcTable;
    }

    /**
     * Détecte le type MIME d'un fichier vidéo
     */
    static getVideoMimeType(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const types = {
            'mp4': 'video/mp4',
            'webm': 'video/webm',
            'ogg': 'video/ogg',
            'mov': 'video/quicktime',
            'avi': 'video/x-msvideo',
            'mkv': 'video/x-matroska',
            'flv': 'video/x-flv',
            'm4v': 'video/x-m4v',
            '3gp': 'video/3gpp',
            'wmv': 'video/x-ms-wmv'
        };
        return types[ext] || 'video/mp4';
    }
}

/**
 * Gestionnaire d'upload chunked professionnel
 * Supporte: parallélisme, retry exponentiel, reprise, pause/cancel
 */
class ChunkedUploadManager {
    constructor(options = {}) {
        this.baseUrl = options.baseUrl || '';
        this.chunkSize = options.chunkSize || 2 * 1024 * 1024; // 2MB par défaut
        this.maxRetries = options.maxRetries || 3;
        this.maxConcurrency = options.maxConcurrency || 2; // Uploads parallèles
        this.retryDelay = options.retryDelay || 1000; // ms
        
        this.state = {
            file: null,
            uploadId: null,
            totalChunks: 0,
            uploadedChunks: new Set(),
            failedChunks: new Map(), // chunkIndex -> retryCount
            isPaused: false,
            isCancelled: false,
            isUploading: false,
            startTime: null,
            lastChunkTime: null,
            bytesUploaded: 0
        };

        this.callbacks = {
            onProgress: () => {},
            onChunkComplete: () => {},
            onComplete: () => {},
            onError: () => {},
            onCancel: () => {},
            onPause: () => {},
            onResume: () => {}
        };

        // Bind callbacks
        Object.assign(this.callbacks, options);
    }

    /**
     * Démarre un nouvel upload
     */
    async start(file) {
        try {
            this.reset();
            this.state.file = file;
            this.state.isUploading = true;
            this.state.startTime = Date.now();

            // Étape 1: Initialisation
            const initData = await this.initializeUpload();
            this.state.uploadId = initData.upload_id;
            this.state.totalChunks = initData.total_chunks;
            
            // Ajuster chunkSize si serveur impose une limite différente
            if (initData.chunk_size) {
                this.chunkSize = initData.chunk_size;
                this.state.totalChunks = Math.ceil(file.size / this.chunkSize);
            }

            this.notifyProgress({
                phase: 'uploading',
                percent: 0,
                uploadedChunks: 0,
                totalChunks: this.state.totalChunks,
                uploadedSize: 0,
                totalSize: file.size,
                speed: 0
            });

            // Étape 2: Upload parallèle des chunks
            await this.uploadChunksParallel();

            // Si annulé pendant l'upload, ne pas finaliser
            if (this.state.isCancelled) {
                await this.cancel();
                return;
            }

            // Étape 3: Finalisation
            await this.finalizeUpload();

        } catch (error) {
            console.error('[ChunkedUploadManager] Error:', error);
            this.callbacks.onError(error.message || 'Erreur inconnue', error);
        } finally {
            this.state.isUploading = false;
        }
    }

    /**
     * Initialise la session côté serveur
     */
    async initializeUpload() {
        const formData = new FormData();
        formData.append('file_name', this.state.file.name);
        formData.append('file_size', this.state.file.size);
        formData.append('file_hash', await FileUtils.calculateCRC32(this.state.file.slice(0, Math.min(1024 * 1024, this.state.file.size))));

        const response = await fetch(this.baseUrl + 'initUpload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Erreur initialisation');
        }

        return data;
    }

    /**
     * Upload parallèle des chunks avec gestion de file d'attente
     */
    async uploadChunksParallel() {
        const chunksToUpload = [];
        for (let i = 0; i < this.state.totalChunks; i++) {
            if (!this.state.uploadedChunks.has(i)) {
                chunksToUpload.push(i);
            }
        }

        // Worker pool pour upload parallèle
        const workers = [];
        const maxWorkers = Math.min(this.maxConcurrency, chunksToUpload.length);

        for (let w = 0; w < maxWorkers; w++) {
            workers.push(this.uploadWorker(chunksToUpload));
        }

        await Promise.all(workers);
    }

    /**
     * Worker d'upload qui traite la file de chunks
     */
    async uploadWorker(queue) {
        while (queue.length > 0 && !this.state.isCancelled && !this.state.isPaused) {
            const chunkIndex = queue.shift();
            await this.uploadChunkWithRetry(chunkIndex);
        }
    }

    /**
     * Upload un chunk avec retry automatique
     */
    async uploadChunkWithRetry(chunkIndex, attempt = 0) {
        try {
            await this.uploadChunk(chunkIndex);
            this.state.failedChunks.delete(chunkIndex);
        } catch (error) {
            const retryCount = this.state.failedChunks.get(chunkIndex) || 0;
            
            if (retryCount < this.maxRetries && !this.state.isCancelled) {
                // Backoff exponentiel
                const delay = this.retryDelay * Math.pow(2, retryCount);
                this.state.failedChunks.set(chunkIndex, retryCount + 1);
                
                console.warn(`[ChunkedUploadManager] Retry chunk ${chunkIndex}, attempt ${retryCount + 1} after ${delay}ms`);
                await this.sleep(delay);
                await this.uploadChunkWithRetry(chunkIndex, retryCount + 1);
            } else {
                throw new Error(`Chunk ${chunkIndex} échoué après ${this.maxRetries} tentatives: ${error.message}`);
            }
        }
    }

    /**
     * Upload un chunk spécifique
     */
    async uploadChunk(chunkIndex) {
        // Attendre si en pause
        while (this.state.isPaused && !this.state.isCancelled) {
            await this.sleep(100);
        }

        if (this.state.isCancelled) return;

        const start = chunkIndex * this.chunkSize;
        const end = Math.min(start + this.chunkSize, this.state.file.size);
        const chunk = this.state.file.slice(start, end);

        const formData = new FormData();
        formData.append('upload_id', this.state.uploadId);
        formData.append('chunk_index', chunkIndex);
        formData.append('chunk', chunk, `chunk_${chunkIndex}`);
        
        // Ajouter hash pour vérification d'intégrité (optionnel)
        if (chunk.size < 10 * 1024 * 1024) { // Seulement pour chunks < 10MB
            formData.append('chunk_hash', await FileUtils.calculateCRC32(chunk));
        }

        const startTime = Date.now();
        
        const response = await fetch(this.baseUrl + 'uploadChunk', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const elapsed = Date.now() - startTime;
        this.state.lastChunkTime = elapsed;

        if (!response.ok) {
            const text = await response.text();
            throw new Error(`HTTP ${response.status}: ${text}`);
        }

        const data = await response.json();
        
        if (!data.success) {
            // Gestion spécifique des erreurs PHP
            if (data.error_type === 'PHP_LIMIT') {
                throw new Error(`Limite serveur atteinte: ${data.message}. Contactez l'administrateur.`);
            }
            throw new Error(data.message || `Erreur chunk ${chunkIndex}`);
        }

        // Mettre à jour l'état
        this.state.uploadedChunks.add(chunkIndex);
        this.state.bytesUploaded += chunk.size;

        // Calculer la vitesse
        const speed = this.calculateSpeed();

        // Notifier progression
        const progress = {
            phase: 'uploading',
            percent: (this.state.uploadedChunks.size / this.state.totalChunks) * 100,
            uploadedChunks: this.state.uploadedChunks.size,
            totalChunks: this.state.totalChunks,
            uploadedSize: this.state.bytesUploaded,
            totalSize: this.state.file.size,
            currentChunk: chunkIndex,
            speed: speed,
            chunkTime: elapsed
        };

        this.notifyProgress(progress);
        this.callbacks.onChunkComplete(chunkIndex, data);
    }

    /**
     * Finalise l'upload côté serveur
     */
    async finalizeUpload() {
        this.notifyProgress({
            phase: 'finalizing',
            percent: 100,
            message: 'Assemblage des chunks...'
        });

        const formData = new FormData();
        formData.append('upload_id', this.state.uploadId);

        const response = await fetch(this.baseUrl + 'completeUpload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (!data.success) {
            // Si chunks manquants, les réuploader
            if (data.missing_chunks && data.missing_chunks.length > 0) {
                console.warn('[ChunkedUploadManager] Missing chunks detected:', data.missing_chunks);
                
                for (const idx of data.missing_chunks) {
                    this.state.uploadedChunks.delete(idx);
                }
                
                // Relancer l'upload des chunks manquants
                await this.uploadChunksParallel();
                
                // Réessayer de finaliser
                return this.finalizeUpload();
            }
            
            throw new Error(data.message || 'Erreur finalisation');
        }

        this.callbacks.onComplete(data);
    }

    /**
     * Annule l'upload
     */
    async cancel() {
        this.state.isCancelled = true;
        this.state.isPaused = false;

        if (this.state.uploadId) {
            try {
                const formData = new FormData();
                formData.append('upload_id', this.state.uploadId);
                
                await fetch(this.baseUrl + 'cancelUpload', {
                    method: 'POST',
                    body: formData
                });
            } catch (e) {
                console.log('[ChunkedUploadManager] Cancel error (ignored):', e);
            }
        }

        this.callbacks.onCancel();
    }

    /**
     * Met en pause l'upload
     */
    pause() {
        if (this.state.isUploading && !this.state.isCancelled) {
            this.state.isPaused = true;
            this.callbacks.onPause();
        }
    }

    /**
     * Reprend l'upload
     */
    resume() {
        if (this.state.isPaused) {
            this.state.isPaused = false;
            this.callbacks.onResume();
        }
    }

    /**
     * Calcule la vitesse d'upload en MB/s
     */
    calculateSpeed() {
        const elapsed = (Date.now() - this.state.startTime) / 1000;
        if (elapsed === 0) return 0;
        return (this.state.bytesUploaded / elapsed) / (1024 * 1024);
    }

    /**
     * Notifie la progression
     */
    notifyProgress(progress) {
        // Throttle pour éviter trop de mises à jour DOM
        if (this.progressThrottle && progress.phase === 'uploading') {
            const now = Date.now();
            if (now - this.lastProgressUpdate < 100) return; // Max 10 updates/sec
            this.lastProgressUpdate = now;
        }
        
        this.callbacks.onProgress(progress);
    }

    /**
     * Réinitialise l'état
     */
    reset() {
        this.state = {
            file: null,
            uploadId: null,
            totalChunks: 0,
            uploadedChunks: new Set(),
            failedChunks: new Map(),
            isPaused: false,
            isCancelled: false,
            isUploading: false,
            startTime: null,
            lastChunkTime: null,
            bytesUploaded: 0
        };
    }

    /**
     * Utilitaire: sleep
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Vérifie si un upload est en cours
     */
    get isUploading() {
        return this.state.isUploading;
    }

    /**
     * Vérifie si en pause
     */
    get isPaused() {
        return this.state.isPaused;
    }
}

/**
 * =============================================================================
 * INITIALISATION DE L'INTERFACE
 * =============================================================================
 */

$(document).ready(function() {
    
    // Configuration globale
    const UPLOAD_CONFIG = {
        baseUrl: '<?= base_url('video/') ?>',
        chunkSize: 2 * 1024 * 1024, // 2MB
        maxRetries: 3
    };

    let currentUploader = null;

    // Auto-hide des alertes
    setTimeout(() => {
        $('.alert:not(.alert-permanent)').fadeOut('slow');
    }, 5000);

    // Toggle AJAX WhatsApp/Site Web
    $(document).on('change', '.toggle-field', function() {
        const $checkbox = $(this);
        const id = $checkbox.data('id');
        const field = $checkbox.data('field');
        const value = $checkbox.is(':checked') ? 1 : 0;
        
        // Feedback visuel immédiat
        $checkbox.prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url('video/toggleField') ?>',
            type: 'POST',
            data: {
                id: id,
                field: field,
                value: value,
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (!response || !response.success) {
                    $checkbox.prop('checked', !value);
                    toastr.error('Erreur lors de la mise à jour');
                } else {
                    toastr.success('Mise à jour effectuée');
                }
            },
            error: function() {
                $checkbox.prop('checked', !value);
                toastr.error('Erreur de connexion');
            },
            complete: function() {
                $checkbox.prop('disabled', false);
            }
        });
    });

    // Toggle affichage section upload/link
    $(document).on('change', '.source-type-selector', function() {
        const target = $(this).data('target');
        const value = $(this).val();
        
        if (target === 'create') {
            toggleCreateSections(value);
        } else {
            // Mode édition
            $(`#upload_section_${target}`).toggle(value === 'upload');
            $(`#link_section_${target}`).toggle(value === 'link');
        }
    });

    function toggleCreateSections(value) {
        const isUpload = value === 'upload';
        $('#upload_section_create').toggle(isUpload);
        $('#link_section_create').toggle(!isUpload);
        $('#create_uploaded_path').prop('required', isUpload);
        $('input[name="lien"]').prop('required', !isUpload);
        
        // Reset si on switch
        if (!isUpload && currentUploader) {
            currentUploader.cancel();
        }
    }

    // Toggle message réseaux sociaux
    $(document).on('change', '.share-toggle', function() {
        const target = $(this).data('target');
        $(`#${target}`).toggle($(this).is(':checked'));
    });

    // ========================================================================
    // GESTION UPLOAD CHUNKED
    // ========================================================================

    // Click sur zone de drop
    $(document).on('click', '.upload-zone', function(e) {
        // Éviter si clic sur input file ou zone de progression
        if ($(e.target).is('input, .upload-progress, .upload-progress *')) {
            return;
        }
        $(this).find('.file-input').trigger('click');
    });

    // Drag & drop
    $(document).on('dragover', '.upload-zone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-active border-primary bg-light');
    });

    $(document).on('dragleave', '.upload-zone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-active border-primary bg-light');
    });

    $(document).on('drop', '.upload-zone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-active border-primary bg-light');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelection(files[0], $(this));
        }
    });

    // Sélection fichier via input
    $(document).on('change', '.file-input', function() {
        const file = this.files[0];
        if (file) {
            handleFileSelection(file, $(this).closest('.upload-zone'));
        }
    });

    /**
     * Gère la sélection d'un fichier
     */
    function handleFileSelection(file, $zone) {
        // Validation type
        if (!file.type.startsWith('video/')) {
            toastr.error('Veuillez sélectionner un fichier vidéo valide.');
            return;
        }

        const uploadId = $zone.find('.file-input').data('upload-id');
        const isCreate = uploadId === 'create';
        
        // Afficher info fichier
        displayFileInfo(file, uploadId);
        
        // Démarrer l'upload
        startChunkedUpload(file, uploadId, $zone);
    }

    /**
     * Affiche les informations du fichier
     */
    function displayFileInfo(file, uploadId) {
        const formattedSize = FileUtils.formatSize(file.size);
        
        if (uploadId === 'create') {
            $('#create_file_name').text(file.name).attr('title', file.name);
            $('#create_file_size').text(formattedSize);
            $('#create_file_info').removeClass('d-none');
        } else {
            const $zone = $(`#drop_zone_${uploadId}`);
            $zone.find('.new-file-info').removeClass('d-none');
            $zone.find('.new-file-name').text(file.name).attr('title', file.name);
            $zone.find('.new-file-size').text(formattedSize);
        }
    }

    /**
     * Démarre l'upload chunked
     */
    function startChunkedUpload(file, uploadId, $zone) {
        const isCreate = uploadId === 'create';
        
        // Références UI
        const ui = getUploadUI(uploadId, $zone);
        
        // Reset UI
        resetUploadUI(ui);
        showUploadControls(ui);
        
        // Créer le gestionnaire d'upload
        currentUploader = new ChunkedUploadManager({
            baseUrl: UPLOAD_CONFIG.baseUrl,
            chunkSize: UPLOAD_CONFIG.chunkSize,
            maxRetries: UPLOAD_CONFIG.maxRetries,
            
            onProgress: (data) => {
                updateProgressUI(ui, data);
            },
            
            onChunkComplete: (index, response) => {
                console.log(`[Upload] Chunk ${index} completed`);
            },
            
            onComplete: (data) => {
                handleUploadComplete(ui, data, uploadId);
            },
            
            onError: (message, error) => {
                handleUploadError(ui, message, error);
            },
            
            onCancel: () => {
                handleUploadCancel(ui, uploadId);
            },
            
            onPause: () => {
                ui.$status.text('En pause');
                ui.$bar.removeClass('progress-bar-animated');
            },
            
            onResume: () => {
                ui.$status.text('Upload en cours...');
                ui.$bar.addClass('progress-bar-animated');
            }
        });

        // Démarrer
        currentUploader.start(file);

        // Bind boutons de contrôle
        bindControlButtons(ui, currentUploader);
    }

    /**
     * Récupère les références UI
     */
    function getUploadUI(uploadId, $zone) {
        const isCreate = uploadId === 'create';
        
        if (isCreate) {
            return {
                $progress: $('#create_progress_container'),
                $status: $('#create_upload_status'),
                $percent: $('#create_upload_percent'),
                $bar: $('#create_progress_bar'),
                $chunks: $('#create_chunks_info'),
                $speed: $('#create_upload_speed'),
                $sizeInfo: $('#create_size_info'),
                $cancelBtn: $('#create_cancel'),
                $pauseBtn: $('#create_pause'),
                $resumeBtn: $('#create_resume'),
                $success: $('#create_upload_success'),
                $fileInfo: $('#create_file_info')
            };
        } else {
            return {
                $progress: $zone.find('.upload-progress'),
                $status: $zone.find('.upload-status'),
                $percent: $zone.find('.upload-percent'),
                $bar: $zone.find('.progress-bar'),
                $chunks: $zone.find('.upload-chunks'),
                $speed: $zone.find('.upload-speed'),
                $sizeInfo: null,
                $cancelBtn: $zone.closest('.upload-section').find('.cancel-upload'),
                $pauseBtn: $zone.closest('.upload-section').find('.pause-upload'),
                $resumeBtn: $zone.closest('.upload-section').find('.resume-upload'),
                $success: $zone.find('.new-file-info'),
                $fileInfo: $zone.find('.current-file')
            };
        }
    }

    /**
     * Reset l'interface d'upload
     */
    function resetUploadUI(ui) {
        ui.$progress.removeClass('d-none');
        ui.$bar.css('width', '0%').attr('aria-valuenow', 0);
        ui.$percent.text('0%');
        ui.$status.text('Préparation...');
        ui.$chunks.text('Chunk 0/0');
        if (ui.$speed) ui.$speed.text('0 MB/s');
        if (ui.$sizeInfo) ui.$sizeInfo.text('0 MB / 0 MB');
        ui.$success.addClass('d-none');
    }

    /**
     * Affiche les contrôles d'upload
     */
    function showUploadControls(ui) {
        ui.$cancelBtn.removeClass('d-none');
        ui.$pauseBtn.removeClass('d-none');
        ui.$resumeBtn.addClass('d-none');
    }

    /**
     * Met à jour la progression
     */
    function updateProgressUI(ui, data) {
        const percent = Math.round(data.percent);
        ui.$bar.css('width', percent + '%').attr('aria-valuenow', percent);
        ui.$percent.text(percent + '%');
        
        if (data.phase === 'finalizing') {
            ui.$status.text(data.message);
        } else {
            ui.$status.text(`Upload en cours (${data.currentChunk + 1}/${data.totalChunks})`);
            ui.$chunks.text(`Chunk ${data.uploadedChunks}/${data.totalChunks}`);
            
            if (ui.$speed) {
                ui.$speed.text(data.speed.toFixed(2) + ' MB/s');
            }
            
            if (ui.$sizeInfo) {
                ui.$sizeInfo.text(
                    `${FileUtils.formatSize(data.uploadedSize)} / ${FileUtils.formatSize(data.totalSize)}`
                );
            }
        }
    }

    /**
     * Gère la complétion de l'upload
     */
    function handleUploadComplete(ui, data, uploadId) {
        // Cacher progression
        ui.$progress.addClass('d-none');
        ui.$cancelBtn.addClass('d-none');
        ui.$pauseBtn.addClass('d-none');
        ui.$resumeBtn.addClass('d-none');
        
        // Afficher succès
        if (uploadId === 'create') {
            $('#create_uploaded_path').val(data.file_path);
            $('#create_thumbnail_path').val(data.thumbnail || '');
            $('#create_upload_success').removeClass('d-none');
        } else {
            const $zone = $(`#drop_zone_${uploadId}`);
            $zone.closest('form').find('.uploaded-path').val(data.file_path);
            $zone.closest('form').find('.thumbnail-path').val(data.thumbnail || '');
            ui.$success.html(`
                <div class="alert alert-success mb-0 py-2">
                    <i class="bx bx-check-circle me-2"></i>
                    <span class="fw-bold">Upload terminé!</span>
                    <div class="small mt-1">${data.file_size_formatted}</div>
                </div>
            `).removeClass('d-none');
        }
        
        toastr.success('Upload terminé avec succès!');
        currentUploader = null;
    }

    /**
     * Gère les erreurs d'upload
     */
    function handleUploadError(ui, message, error) {
        ui.$progress.addClass('d-none');
        ui.$cancelBtn.addClass('d-none');
        ui.$pauseBtn.addClass('d-none');
        ui.$resumeBtn.addClass('d-none');
        
        // Afficher message d'erreur détaillé
        toastr.error(message, 'Erreur Upload', {
            timeOut: 10000,
            closeButton: true
        });
        
        console.error('[Upload Error]', error);
        currentUploader = null;
    }

    /**
     * Gère l'annulation
     */
    function handleUploadCancel(ui, uploadId) {
        ui.$progress.addClass('d-none');
        ui.$cancelBtn.addClass('d-none');
        ui.$pauseBtn.addClass('d-none');
        ui.$resumeBtn.addClass('d-none');
        
        if (uploadId === 'create') {
            $('#create_file_info').addClass('d-none');
            $('#create_upload_success').addClass('d-none');
            $('#create_uploaded_path').val('');
            $('#create_thumbnail_path').val('');
        } else {
            const $zone = $(`#drop_zone_${uploadId}`);
            $zone.find('.new-file-info').addClass('d-none');
        }
        
        currentUploader = null;
    }

    /**
     * Bind les boutons de contrôle
     */
    function bindControlButtons(ui, uploader) {
        // Cancel
        ui.$cancelBtn.off('click').on('click', function() {
            if (confirm('Voulez-vous vraiment annuler l\'upload ?')) {
                uploader.cancel();
            }
        });
        
        // Pause
        ui.$pauseBtn.off('click').on('click', function() {
            uploader.pause();
            $(this).addClass('d-none');
            ui.$resumeBtn.removeClass('d-none');
        });
        
        // Resume
        ui.$resumeBtn.off('click').on('click', function() {
            uploader.resume();
            $(this).addClass('d-none');
            ui.$pauseBtn.removeClass('d-none');
        });
    }

    // ========================================================================
    // VALIDATION FORMULAIRES
    // ========================================================================

    // Validation création
    $('#create_form').on('submit', function(e) {
        const typeSource = $('#create_type_source').val();
        
        if (typeSource === 'upload') {
            const uploadedPath = $('#create_uploaded_path').val();
            if (!uploadedPath) {
                e.preventDefault();
                toastr.warning('Veuillez attendre la fin de l\'upload ou sélectionner un fichier.');
                return false;
            }
        } else if (typeSource === 'link') {
            const lien = $('input[name="lien"]').val().trim();
            if (!lien) {
                e.preventDefault();
                toastr.warning('Veuillez saisir un lien vidéo valide.');
                return false;
            }
        }
    });

    // Reset modal création à la fermeture
    $('#create_video').on('hidden.bs.modal', function() {
        if (currentUploader && currentUploader.isUploading) {
            currentUploader.cancel();
        }
        
        // Reset complet
        $(this).find('form')[0].reset();
        $('#create_progress_container').addClass('d-none');
        $('#create_file_info').addClass('d-none');
        $('#create_upload_success').addClass('d-none');
        $('#create_cancel, #create_pause, #create_resume').addClass('d-none');
        $('#create_progress_bar').css('width', '0%');
        $('#create_upload_percent').text('0%');
        $('#create_upload_status').text('Préparation...');
        $('#create_uploaded_path').val('');
        $('#create_thumbnail_path').val('');
        $('#upload_section_create').show();
        $('#link_section_create').hide();
    });

    // ========================================================================
    // DATATABLE INITIALIZATION
    // ========================================================================
    
    if ($.fn.DataTable) {
        $('#videosTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true
        });
    }

});
</script>

<style>
/* Styles additionnels pour l'upload */
.upload-zone {
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.upload-zone:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa !important;
}

.upload-zone.drag-active {
    border-color: #0d6efd !important;
    background-color: #e7f1ff !important;
    transform: scale(1.02);
}

.browse-text {
    cursor: pointer;
    text-decoration: underline;
}

.video-thumbnail-container {
    position: relative;
    overflow: hidden;
}

.play-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.8;
    transition: opacity 0.3s;
}

.play-overlay i {
    font-size: 2rem;
    color: white;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}

.video-thumbnail-container:hover .play-overlay {
    opacity: 1;
}

.hd-badge {
    position: absolute;
    bottom: 2px;
    right: 2px;
}

.upload-progress {
    background: rgba(255,255,255,0.9);
    padding: 10px;
    border-radius: 8px;
}

/* Animation pour le drop */
@keyframes pulse-border {
    0% { border-color: #0d6efd; }
    50% { border-color: #0dcaf0; }
    100% { border-color: #0d6efd; }
}

.upload-zone.drag-active {
    animation: pulse-border 1s infinite;
}
</style>