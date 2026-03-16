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
        </div>
    </div>

    <!-- Messages Flash -->
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

    <!-- Card Principale -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-primary"><i class="bx bx-video me-2"></i>Liste des Vidéos</h5>
                <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Upload Illimité (Chunked)</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table" class="table table-hover align-middle" style="width:100%">
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
                        if ($is_upload) {
                            $source_badge = '<span class="badge bg-success"><i class="bx bx-upload me-1"></i>Upload</span>';
                        } elseif ($is_link) {
                            $source_badge = '<span class="badge bg-info"><i class="bx bx-link me-1"></i>Lien</span>';
                        } else {
                            $source_badge = '<span class="badge bg-secondary">Inconnu</span>';
                        }
                        
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
                        if (!empty($value['miniature'])) {
                            $thumb_path = $value['miniature'];
                        } elseif ($is_link && preg_match('/youtube/', $value['lien'])) {
                            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\s]+)/', $value['lien'], $matches);
                            $thumb_path = !empty($matches[1]) ? "https://img.youtube.com/vi/{$matches[1]}/mqdefault.jpg" : base_url('assets/images/video-placeholder.jpg');
                        } else {
                            $thumb_path = base_url('assets/images/video-placeholder.jpg');
                        }
                        
                        $thumb_url = (strpos($thumb_path, 'http') === 0) ? $thumb_path : base_url($thumb_path);
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <div class="position-relative" style="width: 100px; height: 60px;">
                                    <img src="<?= $thumb_url ?>" class="rounded border w-100 h-100" style="object-fit: cover;" alt="Miniature">
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <i class="bx bx-play-circle text-white" style="font-size: 2rem; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"></i>
                                    </div>
                                    <?php if ($is_upload && !empty($value['taille']) && $value['taille'] > 104857600): ?>
                                        <div class="position-absolute bottom-0 end-0">
                                            <span class="badge bg-warning" title="Fichier volumineux"><i class="bx bx-hdd"></i></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark text-decoration-none"><?= htmlspecialchars($value['titre'] ?? 'Sans titre') ?></strong>
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
                                            <i class="bx bx-question-mark-circle text-warning" style="font-size: 4rem;"></i>
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
                                        <div class="ratio ratio-16x9 mb-3 bg-dark rounded">
                                            <?php if ($is_upload && !empty($value['fichier'])): ?>
                                                <video controls poster="<?= $thumb_url ?>" class="rounded">
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
                        <div class="modal fade" id="edit_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la vidéo</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                                                <label class="form-label fw-bold">Nouveau fichier vidéo <span class="badge bg-success">Illimité</span></label>
                                                
                                                <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_<?= $value['id_media'] ?>">
                                                    <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                                                    <p class="mb-2">Glissez-déposez une vidéo ici ou <span class="text-primary fw-bold">cliquez pour parcourir</span></p>
                                                    <small class="text-muted d-block">Formats: MP4, WebM, MOV, AVI, MKV... (Taille illimitée grâce au chunked upload)</small>
                                                    
                                                    <input type="file" class="form-control d-none file-input" accept="video/*" data-upload-id="<?= $value['id_media'] ?>">
                                                    <input type="hidden" name="uploaded_file_path" class="uploaded-path">
                                                    <input type="hidden" name="thumbnail" class="thumbnail-path">
                                                    
                                                    <!-- Zone de progression -->
                                                    <div class="upload-progress mt-3 d-none">
                                                        <div class="d-flex justify-content-between small mb-1">
                                                            <span class="upload-status">Préparation...</span>
                                                            <span class="upload-percent">0%</span>
                                                        </div>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                                        </div>
                                                        <div class="upload-chunks small text-muted mt-1">Chunk 0/0</div>
                                                    </div>
                                                    
                                                    <!-- Info fichier actuel -->
                                                    <?php if ($is_upload): ?>
                                                        <div class="current-file mt-3 p-2 bg-white rounded border d-flex align-items-center">
                                                            <i class="bx bx-video text-success fs-4 me-2"></i>
                                                            <div class="text-start">
                                                                <small class="d-block fw-bold"><?= basename($value['fichier']) ?></small>
                                                                <small class="text-muted"><?= $taille_formatee ?></small>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Info nouveau fichier -->
                                                    <div class="new-file-info mt-2 d-none">
                                                        <div class="alert alert-success mb-0 py-2">
                                                            <i class="bx bx-check-circle me-1"></i>
                                                            <span class="new-file-name"></span>
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
                                            <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
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
                        <label class="form-label fw-bold">Fichier vidéo <span class="badge bg-success">Chunked Upload - Illimité</span></label>
                        
                        <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_create">
                            <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                            <p class="mb-2">Glissez-déposez une vidéo ici ou <span class="text-primary fw-bold cursor-pointer">cliquez pour parcourir</span></p>
                            <small class="text-muted d-block mb-2">Formats: MP4, WebM, OGG, MOV, AVI, MKV, FLV, M4V, 3GP, WMV</small>
                            <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Pas de limite de taille (upload par chunks de 5MB)</span>
                            
                            <input type="file" class="form-control d-none file-input" id="create_file_input" accept="video/*" data-upload-id="create">
                            <input type="hidden" name="uploaded_file_path" id="create_uploaded_path">
                            <input type="hidden" name="thumbnail" id="create_thumbnail_path">
                            
                            <!-- Zone de progression -->
                            <div class="upload-progress mt-3 d-none" id="create_progress_container">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span id="create_upload_status">Préparation...</span>
                                    <span id="create_upload_percent">0%</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="create_progress_bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="small text-muted mt-1" id="create_chunks_info">Chunk 0/0</div>
                                <div class="small text-primary mt-1" id="create_size_info">0 MB / 0 MB</div>
                            </div>
                            
                            <!-- Info fichier sélectionné -->
                            <div class="file-info mt-2 d-none" id="create_file_info">
                                <div class="alert alert-info mb-0 py-2 d-flex align-items-center justify-content-center">
                                    <i class="bx bx-file me-2"></i>
                                    <span id="create_file_name"></span>
                                    <span class="mx-2">|</span>
                                    <span id="create_file_size" class="fw-bold"></span>
                                </div>
                            </div>
                            
                            <!-- Succès upload -->
                            <div class="upload-success mt-2 d-none" id="create_upload_success">
                                <div class="alert alert-success mb-0 py-2">
                                    <i class="bx bx-check-circle me-1"></i>
                                    <span>Upload terminé avec succès!</span>
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
</datalist>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<script>
// DEBUG - Intercepter toutes les réponses AJAX
$(document).ajaxComplete(function(event, xhr, settings) {
    console.log('AJAX Complete:', settings.url, 'Status:', xhr.status, 'Response:', xhr.responseText);
});

/**
 * Gestionnaire d'upload chunked
 * Permet d'uploader des fichiers de taille illimitée
 */
class ChunkedUploader {
    constructor(file, options = {}) {
        this.file = file;
        this.chunkSize = options.chunkSize || 5 * 1024 * 1024; // 5MB par défaut
        this.baseUrl = options.baseUrl || '<?= base_url('video/') ?>';
        this.uploadId = null;
        this.totalChunks = Math.ceil(file.size / this.chunkSize);
        this.uploadedChunks = new Set();
        this.isPaused = false;
        this.isCancelled = false;
        
        // Callbacks
        this.onProgress = options.onProgress || (() => {});
        this.onChunkComplete = options.onChunkComplete || (() => {});
        this.onComplete = options.onComplete || (() => {});
        this.onError = options.onError || (() => {});
        this.onCancel = options.onCancel || (() => {});
    }

    /**
     * Démarrer l'upload
     */
    async start() {
        try {
            this.isCancelled = false;
            this.isPaused = false;
            
            // 1. Initialiser la session
            const initData = await this.ajaxRequest('initUpload', {
                file_name: this.file.name,
                file_size: this.file.size
            });
            
            if (!initData.success) {
                throw new Error(initData.message || 'Erreur initialisation');
            }
            
            this.uploadId = initData.upload_id;
            const chunkSize = initData.chunk_size || this.chunkSize;
            
            this.onProgress({
                phase: 'uploading',
                percent: 0,
                uploadedChunks: 0,
                totalChunks: this.totalChunks,
                uploadedSize: 0,
                totalSize: this.file.size
            });

            // 2. Upload des chunks
            for (let i = 0; i < this.totalChunks; i++) {
                // Vérifier si annulé
                if (this.isCancelled) {
                    await this.cancelUpload();
                    this.onCancel();
                    return;
                }
                
                // Attendre si en pause
                while (this.isPaused) {
                    await new Promise(r => setTimeout(r, 100));
                    if (this.isCancelled) {
                        await this.cancelUpload();
                        this.onCancel();
                        return;
                    }
                }
                
                // Skip si déjà uploadé (reprise)
                if (this.uploadedChunks.has(i)) continue;
                
                // Uploader le chunk
                await this.uploadChunk(i, chunkSize);
                
                // Mettre à jour la progression
                const progress = ((this.uploadedChunks.size) / this.totalChunks) * 100;
                const uploadedSize = Math.min((i + 1) * chunkSize, this.file.size);
                
                this.onProgress({
                    phase: 'uploading',
                    percent: progress,
                    uploadedChunks: this.uploadedChunks.size,
                    totalChunks: this.totalChunks,
                    uploadedSize: uploadedSize,
                    totalSize: this.file.size,
                    currentChunk: i + 1
                });
            }

            // 3. Finaliser l'upload
            this.onProgress({
                phase: 'finalizing',
                percent: 100,
                message: 'Assemblage des chunks...'
            });
            
            const completeData = await this.ajaxRequest('completeUpload', {
                upload_id: this.uploadId
            });
            
            if (!completeData.success) {
                // Si chunks manquants, les réuploader
                if (completeData.missing_chunks && completeData.missing_chunks.length > 0) {
                    for (const chunkIndex of completeData.missing_chunks) {
                        await this.uploadChunk(chunkIndex, chunkSize);
                    }
                    // Réessayer de finaliser
                    const retryData = await this.ajaxRequest('completeUpload', {
                        upload_id: this.uploadId
                    });
                    if (!retryData.success) {
                        throw new Error(retryData.message || 'Erreur finalisation');
                    }
                    this.onComplete(retryData);
                } else {
                    throw new Error(completeData.message || 'Erreur finalisation');
                }
            } else {
                this.onComplete(completeData);
            }

        } catch (error) {
            console.error('Upload error:', error);
            this.onError(error.message || 'Erreur inconnue');
        }
    }

    /**
     * Uploader un chunk spécifique - VERSION CORRIGÉE
     */
    async uploadChunk(index, chunkSize) {
        const start = index * chunkSize;
        const end = Math.min(start + chunkSize, this.file.size);
        const chunk = this.file.slice(start, end);

        const formData = new FormData();
        formData.append('upload_id', this.uploadId);
        formData.append('chunk_index', index);
        formData.append('chunk', chunk, `chunk_${index}`);
        formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');

        return new Promise((resolve, reject) => {
            $.ajax({
                url: this.baseUrl + 'uploadChunk',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: (response, status, xhr) => {
                    console.log('uploadChunk success:', response, 'status:', status);
                    
                    // Vérifier si la réponse est un objet valide
                    if (typeof response === 'object' && response !== null) {
                        if (response.success === true) {
                            this.uploadedChunks.add(index);
                            this.onChunkComplete(index, response);
                            resolve(response);
                        } else {
                            reject(new Error(response.message || 'Erreur serveur'));
                        }
                    } else {
                        // Si ce n'est pas un objet, essayer de parser ou rejeter
                        reject(new Error('Réponse serveur invalide (type: ' + typeof response + ')'));
                    }
                },
                error: (xhr, status, error) => {
                    console.error('uploadChunk AJAX error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    
                    let msg = 'Erreur réseau';
                    if (xhr.status === 0) msg = 'Connexion refusée';
                    else if (xhr.status === 404) msg = 'URL non trouvée (404)';
                    else if (xhr.status === 500) msg = 'Erreur serveur (500)';
                    else if (xhr.responseText) {
                        try {
                            const err = JSON.parse(xhr.responseText);
                            msg = err.message || msg;
                        } catch(e) {}
                    }
                    
                    reject(new Error(`Chunk ${index}: ${msg}`));
                }
            });
        });
    }

    /**
     * Requête AJAX générique - VERSION CORRIGÉE
     */
    ajaxRequest(method, data) {
        return new Promise((resolve, reject) => {
            console.log('Appel AJAX vers:', this.baseUrl + method, 'data:', data);
            
            $.ajax({
                url: this.baseUrl + method,
                type: 'POST',
                data: {
                    ...data,
                    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
                },
                dataType: 'json',
                success: (response, status, xhr) => {
                    console.log('ajaxRequest success:', method, response);
                    
                    if (typeof response === 'object' && response !== null) {
                        resolve(response);
                    } else {
                        reject(new Error('Réponse invalide pour ' + method));
                    }
                },
                error: (xhr, status, error) => {
                    console.error('ajaxRequest error:', method, {
                        status: xhr.status,
                        responseText: xhr.responseText
                    });
                    
                    let msg = `Erreur ${method}`;
                    if (xhr.status === 0) msg = 'Connexion refusée';
                    else if (xhr.status === 404) msg = 'URL non trouvée';
                    else if (xhr.status === 500) msg = 'Erreur serveur';
                    
                    reject(new Error(msg));
                }
            });
        });
    }

    /**
     * Mettre en pause
     */
    pause() {
        this.isPaused = true;
    }

    /**
     * Reprendre
     */
    resume() {
        this.isPaused = false;
    }

    /**
     * Annuler
     */
    cancel() {
        this.isCancelled = true;
        this.isPaused = false;
    }

    /**
     * Annuler côté serveur
     */
    async cancelUpload() {
        if (this.uploadId) {
            try {
                await this.ajaxRequest('cancelUpload', { upload_id: this.uploadId });
            } catch(e) {
                console.log('cancelUpload error (ignoré):', e);
            }
        }
    }

    /**
     * Formater la taille
     */
    static formatSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

// ==================== INITIALISATION ====================

$(document).ready(function() {
    

    // Auto-hide alerts
    setTimeout(() => {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Toggle AJAX WhatsApp/Site Web
    $(document).on('change', '.toggle-field', function() {
        const id = $(this).data('id');
        const field = $(this).data('field');
        const value = $(this).is(':checked') ? 1 : 0;
        const $checkbox = $(this);
        
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
                console.log('toggleField response:', response);
                if (!response || !response.success) {
                    $checkbox.prop('checked', !value);
                    alert('Erreur lors de la mise à jour');
                }
            },
            error: function(xhr, status, error) {
                console.error('toggleField error:', xhr, status, error);
                $checkbox.prop('checked', !value);
                alert('Erreur de connexion');
            }
        });
    });

    // Toggle affichage section upload/link
    $(document).on('change', '.source-type-selector', function() {
        const target = $(this).data('target');
        const value = $(this).val();
        
        if (target === 'create') {
            if (value === 'upload') {
                $('#upload_section_create').show();
                $('#link_section_create').hide();
                $('#create_uploaded_path').prop('required', true);
                $('input[name="lien"]').prop('required', false);
            } else {
                $('#upload_section_create').hide();
                $('#link_section_create').show();
                $('#create_uploaded_path').prop('required', false);
                $('input[name="lien"]').prop('required', true);
            }
        } else {
            // Mode édition
            if (value === 'upload') {
                $(`#upload_section_${target}`).show();
                $(`#link_section_${target}`).hide();
            } else {
                $(`#upload_section_${target}`).hide();
                $(`#link_section_${target}`).show();
            }
        }
    });

    // Toggle message réseaux sociaux
    $(document).on('change', '.share-toggle', function() {
        const target = $(this).data('target');
        if ($(this).is(':checked')) {
            $(`#${target}`).show();
        } else {
            $(`#${target}`).hide();
        }
    });

    // ==================== GESTION UPLOAD CHUNKED ====================
    
    let currentUploader = null;

    // Click sur zone de drop pour ouvrir le fichier
    $(document).on('click', '.upload-zone', function(e) {
         console.log('Clic détecté sur upload-zone');
        if ($(e.target).hasClass('file-input') || $(e.target).closest('.upload-progress').length) {
            return;
        }
        $(this).find('.file-input').click();
    });

    // Drag & drop
    $(document).on('dragover', '.upload-zone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('border-primary bg-light');
    });

    $(document).on('dragleave', '.upload-zone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('border-primary bg-light');
    });

    $(document).on('drop', '.upload-zone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('border-primary bg-light');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0 && files[0].type.startsWith('video/')) {
            handleFileSelect(files[0], $(this));
        } else if (files.length > 0) {
            alert('Veuillez sélectionner un fichier vidéo valide.');
        }
    });

    // Sélection fichier via input
    $(document).on('change', '.file-input', function() {
        const file = this.files[0];
        if (file) {
            if (!file.type.startsWith('video/')) {
                alert('Veuillez sélectionner un fichier vidéo.');
                $(this).val('');
                return;
            }
            handleFileSelect(file, $(this).closest('.upload-zone'));
        }
    });

    // Gestion sélection fichier
    function handleFileSelect(file, $zone) {
        const uploadId = $zone.find('.file-input').data('upload-id');
        
        // Afficher info fichier
        if (uploadId === 'create') {
            $('#create_file_name').text(file.name);
            $('#create_file_size').text(ChunkedUploader.formatSize(file.size));
            $('#create_file_info').removeClass('d-none');
        } else {
            $zone.find('.new-file-info').removeClass('d-none');
            $zone.find('.new-file-name').text(file.name);
        }

        // Démarrer l'upload
        startUpload(file, uploadId, $zone);
    }

    // Démarrer upload
    function startUpload(file, uploadId, $zone) {
        const isCreate = uploadId === 'create';
        
        // Références UI
        const $progress = isCreate ? $('#create_progress_container') : $zone.find('.upload-progress');
        const $status = isCreate ? $('#create_upload_status') : $zone.find('.upload-status');
        const $percent = isCreate ? $('#create_upload_percent') : $zone.find('.upload-percent');
        const $bar = isCreate ? $('#create_progress_bar') : $zone.find('.progress-bar');
        const $chunks = isCreate ? $('#create_chunks_info') : $zone.find('.upload-chunks');
        const $sizeInfo = isCreate ? $('#create_size_info') : null;
        
        const $cancelBtn = isCreate ? $('#create_cancel') : $zone.closest('.upload-section').find('.cancel-upload');
        const $pauseBtn = isCreate ? $('#create_pause') : $zone.closest('.upload-section').find('.pause-upload');
        const $resumeBtn = isCreate ? $('#create_resume') : $zone.closest('.upload-section').find('.resume-upload');

        $progress.removeClass('d-none');
        $cancelBtn.removeClass('d-none');
        $pauseBtn.removeClass('d-none');

        currentUploader = new ChunkedUploader(file, {
            onProgress: (data) => {
                const percent = Math.round(data.percent);
                $bar.css('width', percent + '%').attr('aria-valuenow', percent);
                $percent.text(percent + '%');
                $status.text(data.phase === 'finalizing' ? 'Finalisation...' : `Upload en cours (${data.currentChunk || '?'}/${data.totalChunks})`);
                $chunks.text(`Chunk ${data.uploadedChunks}/${data.totalChunks}`);
                
                if ($sizeInfo) {
                    $sizeInfo.text(`${ChunkedUploader.formatSize(data.uploadedSize)} / ${ChunkedUploader.formatSize(data.totalSize)}`);
                }
            },
            onComplete: (data) => {
                console.log('Upload complete:', data);
                $progress.addClass('d-none');
                $cancelBtn.addClass('d-none');
                $pauseBtn.addClass('d-none');
                $resumeBtn.addClass('d-none');
                
                // Stocker les chemins
                if (isCreate) {
                    $('#create_uploaded_path').val(data.file_path);
                    $('#create_thumbnail_path').val(data.thumbnail || '');
                    $('#create_upload_success').removeClass('d-none');
                } else {
                    $zone.closest('form').find('.uploaded-path').val(data.file_path);
                    $zone.closest('form').find('.thumbnail-path').val(data.thumbnail || '');
                    $zone.find('.new-file-info').html('<div class="alert alert-success mb-0 py-2"><i class="bx bx-check-circle me-1"></i>Upload terminé!</div>');
                }
                
                currentUploader = null;
            },
            onError: (message) => {
                console.error('Upload error:', message);
                $progress.addClass('d-none');
                $cancelBtn.addClass('d-none');
                $pauseBtn.addClass('d-none');
                $resumeBtn.addClass('d-none');
                
                alert('Erreur upload: ' + message);
                currentUploader = null;
            },
            onCancel: () => {
                $progress.addClass('d-none');
                $cancelBtn.addClass('d-none');
                $pauseBtn.addClass('d-none');
                $resumeBtn.addClass('d-none');
                
                if (isCreate) {
                    $('#create_file_info').addClass('d-none');
                    $('#create_upload_success').addClass('d-none');
                    $('#create_uploaded_path').val('');
                    $('#create_thumbnail_path').val('');
                } else {
                    $zone.find('.new-file-info').addClass('d-none');
                }
                
                currentUploader = null;
            }
        });

        currentUploader.start();

        // Boutons contrôle
        $cancelBtn.off('click').on('click', function() {
            if (currentUploader) {
                if (confirm('Voulez-vous vraiment annuler l\'upload ?')) {
                    currentUploader.cancel();
                }
            }
        });

        $pauseBtn.off('click').on('click', function() {
            if (currentUploader) {
                currentUploader.pause();
                $pauseBtn.addClass('d-none');
                $resumeBtn.removeClass('d-none');
                $status.text('En pause');
                $bar.removeClass('progress-bar-animated');
            }
        });

        $resumeBtn.off('click').on('click', function() {
            if (currentUploader) {
                currentUploader.resume();
                $resumeBtn.addClass('d-none');
                $pauseBtn.removeClass('d-none');
                $status.text('Upload en cours...');
                $bar.addClass('progress-bar-animated');
            }
        });
    }

    // Validation formulaire création
    $('#create_form').on('submit', function(e) {
        const typeSource = $('#create_type_source').val();
        
        if (typeSource === 'upload') {
            const uploadedPath = $('#create_uploaded_path').val();
            if (!uploadedPath) {
                e.preventDefault();
                alert('Veuillez attendre la fin de l\'upload du fichier vidéo ou sélectionner un fichier.');
                return false;
            }
        } else if (typeSource === 'link') {
            const lien = $('input[name="lien"]').val();
            if (!lien || !lien.trim()) {
                e.preventDefault();
                alert('Veuillez saisir un lien vidéo valide.');
                return false;
            }
        }
    });

    // Reset modal création à la fermeture
    $('#create_video').on('hidden.bs.modal', function() {
        if (currentUploader) {
            currentUploader.cancel();
        }
        
        // Reset form
        $(this).find('form')[0].reset();
        
        // Reset UI upload
        $('#create_progress_container').addClass('d-none');
        $('#create_file_info').addClass('d-none');
        $('#create_upload_success').addClass('d-none');
        $('#create_cancel, #create_pause, #create_resume').addClass('d-none');
        $('#create_progress_bar').css('width', '0%').attr('aria-valuenow', 0);
        $('#create_upload_percent').text('0%');
        $('#create_upload_status').text('Préparation...');
        $('#create_uploaded_path').val('');
        $('#create_thumbnail_path').val('');
        
        // Reset sections
        $('#upload_section_create').show();
        $('#link_section_create').hide();
    });

});
</script>
