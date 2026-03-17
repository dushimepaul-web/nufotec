<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// ============================================================================
// HELPERS PHP - Équivalents aux méthodes du contrôleur Video.php
// ============================================================================

if (!function_exists('format_duration')) {
    function format_duration($seconds) {
        if (empty($seconds) || $seconds <= 0) return '0:00';
        if ($seconds < 60) {
            return gmdate("s\\s", $seconds);
        } elseif ($seconds < 3600) {
            return gmdate("i\\m s\\s", $seconds);
        } else {
            return gmdate("H\\h i\\m s\\s", $seconds);
        }
    }
}

if (!function_exists('format_bytes')) {
    function format_bytes($bytes, $decimals = 2) {
        if (empty($bytes) || $bytes === 0) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), $decimals) . ' ' . $sizes[$i];
    }
}

if (!function_exists('get_video_quality')) {
    function get_video_quality($metadata_json) {
        if (empty($metadata_json)) return null;
        $meta = json_decode($metadata_json, true);
        if (!empty($meta['height'])) {
            if ($meta['height'] >= 1080) return ['label' => 'HD', 'class' => 'danger'];
            if ($meta['height'] >= 720) return ['label' => '720p', 'class' => 'success'];
            return ['label' => 'SD', 'class' => 'warning'];
        }
        return null;
    }
}
?>

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
                        <li class="breadcrumb-item active" aria-current="page">Gestion des Vidéos v4.0</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a class="btn btn-primary btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_video">
                        <i class="bx bx-plus"></i> Nouvelle Vidéo
                    </a>
                    <a class="btn btn-outline-info btn-sm" href="<?= base_url('video/diagnostics') ?>" target="_blank">
                        <i class="bx bx-test-tube"></i> Diagnostic
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 bg-primary text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3"><i class="bx bx-video"></i></div>
                            <div>
                                <h6 class="mb-0">Total Vidéos</h6>
                                <h3 class="mb-0"><?= count($videos ?? []) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-success text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3"><i class="bx bx-time"></i></div>
                            <div>
                                <h6 class="mb-0">Durée Totale</h6>
                                <h3 class="mb-0"><?= format_duration($total_duration ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-info text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3"><i class="bx bx-hdd"></i></div>
                            <div>
                                <h6 class="mb-0">Upload Chunked</h6>
                                <h5 class="mb-0">2MB / 10GB Max</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-warning text-dark shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3"><i class="bx bx-chip"></i></div>
                            <div>
                                <h6 class="mb-0">Traitement</h6>
                                <h5 class="mb-0">FFmpeg + FFprobe</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Flash -->
        <?php $this->load->view('includes/backend/FlashMessages.php'); ?>

        <!-- Card Principale -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 text-primary"><i class="bx bx-video me-2"></i>Bibliothèque Vidéo</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Upload Illimité</span>
                        <span class="badge bg-info"><i class="bx bx-film me-1"></i>Auto-Thumbnails</span>
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
                                <th width="20%">Titre & Infos</th>
                                <th width="8%">Source</th>
                                <th width="10%">Catégorie</th>
                                <th width="8%">Durée/Taille</th>
                                <th width="8%">Qualité</th>
                                <th width="6%">Statut</th>
                                <th width="6%">WA</th>
                                <th width="6%">Web</th>
                                <th width="11%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($videos)): $i = 1; foreach ($videos as $value): 
                            $is_upload = !empty($value['fichier']);
                            $is_link = !empty($value['lien']);
                            
                            // Badge source
                            $source_badge = '';
                            if ($is_upload) {
                                $source_badge = '<span class="badge bg-success"><i class="bx bx-upload me-1"></i>Upload</span>';
                            } elseif ($is_link) {
                                if (strpos($value['lien'], 'youtube') !== false) {
                                    $source_badge = '<span class="badge bg-danger"><i class="bx bxl-youtube me-1"></i>YouTube</span>';
                                } elseif (strpos($value['lien'], 'vimeo') !== false) {
                                    $source_badge = '<span class="badge bg-info"><i class="bx bxl-vimeo me-1"></i>Vimeo</span>';
                                } else {
                                    $source_badge = '<span class="badge bg-info"><i class="bx bx-link me-1"></i>Lien</span>';
                                }
                            }

                            // Formatage
                            $taille_formatee = !empty($value['taille']) ? format_bytes($value['taille']) : '-';
                            $duree_formatee = (!empty($value['duree']) && $value['duree'] > 0) ? format_duration($value['duree']) : '-';
                            
                            // Qualité
                            $quality_info = get_video_quality($value['metadata_id3'] ?? null);
                            $quality_badge = $quality_info ? '<span class="badge bg-'.$quality_info['class'].'">'.$quality_info['label'].'</span>' : '<span class="badge bg-secondary">N/A</span>';
                            
                            // Miniature
                            $thumb_url = base_url('assets/images/video-placeholder.jpg');
                            if (!empty($value['miniature'])) {
                                $thumb_url = (strpos($value['miniature'], 'http') === 0) ? $value['miniature'] : base_url($value['miniature']);
                            } elseif ($is_link && preg_match('/youtube\.com|youtu\.be/', $value['lien'])) {
                                preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\s]+)/', $value['lien'], $matches);
                                if (!empty($matches[1])) $thumb_url = "https://img.youtube.com/vi/{$matches[1]}/mqdefault.jpg";
                            }
                        ?>
                            <tr data-id="<?= $value['id_media'] ?>">
                                <td><?= $i++ ?></td>
                                
                                <td>
                                    <div class="position-relative video-thumbnail-container" style="width: 120px; height: 68px;">
                                        <img src="<?= $thumb_url ?>" class="rounded border w-100 h-100" style="object-fit: cover;" loading="lazy">
                                        <div class="play-overlay" onclick="openVideoPlayer(<?= $value['id_media'] ?>)">
                                            <i class="bx bx-play-circle"></i>
                                        </div>
                                        <?php if ($duree_formatee !== '-'): ?>
                                            <div class="duration-badge">
                                                <span class="badge bg-dark"><?= $duree_formatee ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="text-dark text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($value['titre'] ?? '') ?>">
                                            <?= htmlspecialchars($value['titre'] ?? 'Sans titre') ?>
                                        </strong>
                                        <?php if (!empty($value['description'])): ?>
                                            <small class="text-muted text-truncate" style="max-width: 200px;">
                                                <?= htmlspecialchars(substr($value['description'], 0, 60)) ?>...
                                            </small>
                                        <?php endif; ?>
                                        <div class="mt-1">
                                            <?php if (!empty($value['credits'])): ?>
                                                <small class="text-muted me-2"><i class="bx bx-user me-1"></i><?= htmlspecialchars($value['credits']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td><?= $source_badge ?></td>
                                <td><?= !empty($value['categorie']) ? '<span class="badge bg-light text-dark border">'.htmlspecialchars($value['categorie']).'</span>' : '<span class="text-muted">-</span>' ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= $taille_formatee ?></span>
                                    <?php if ($duree_formatee !== '-'): ?>
                                        <small class="text-muted d-block mt-1"><i class="bx bx-time me-1"></i><?= $duree_formatee ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= $quality_badge ?></td>
                                <td class="text-center">
                                    <a href="javascript:void(0)" onclick="toggleStatus(<?= $value['id_media'] ?>, <?= $value['est_actif'] ?? 0 ?>)" class="text-decoration-none">
                                        <?php if (!empty($value['est_actif']) && $value['est_actif'] == 1): ?>
                                            <span class="badge bg-success status-badge-<?= $value['id_media'] ?>">Actif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger status-badge-<?= $value['id_media'] ?>">Inactif</span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input toggle-field" type="checkbox" data-id="<?= $value['id_media'] ?>" data-field="is_for_whatsapp" <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input toggle-field" type="checkbox" data-id="<?= $value['id_media'] ?>" data-field="is_for_website" <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="openVideoPlayer(<?= $value['id_media'] ?>)"><i class="bx bx-play me-2 text-success"></i>Lire</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#edit_<?= $value['id_media'] ?>"><i class="bx bx-edit me-2 text-primary"></i>Modifier</a></li>
                                            <?php if ($is_upload): ?>
                                                <li><a class="dropdown-item" href="<?= base_url('video/stream/' . basename($value['fichier'])) ?>" target="_blank"><i class="bx bx-download me-2 text-info"></i>Stream</a></li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmDelete(<?= $value['id_media'] ?>, '<?= htmlspecialchars(addslashes($value['titre'])) ?>')"><i class="bx bx-trash me-2"></i>Supprimer</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Edit -->
                            <div class="modal fade" id="edit_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la vidéo</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('video/Update') ?>" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                                
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($value['titre']) ?>" required maxlength="255">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Type de source</label>
                                                            <select class="form-select source-type-selector" name="type_source" data-target="edit_<?= $value['id_media'] ?>">
                                                                <option value="upload" <?= $is_upload ? 'selected' : '' ?>>Fichier uploadé</option>
                                                                <option value="link" <?= $is_link ? 'selected' : '' ?>>Lien externe</option>
                                                            </select>
                                                        </div>

                                                        <div class="upload-section mb-3" id="upload_section_edit_<?= $value['id_media'] ?>" style="<?= $is_upload ? '' : 'display:none;' ?>">
                                                            <label class="form-label fw-bold">Remplacer le fichier</label>
                                                            <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_edit_<?= $value['id_media'] ?>">
                                                                <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                                                                <p class="mb-2">Glissez-déposez ou <span class="text-primary fw-bold browse-text">cliquez</span></p>
                                                                <small class="text-muted">Max 10GB - Chunks 2MB</small>
                                                                <input type="file" class="form-control d-none file-input" accept="video/*" data-upload-id="edit_<?= $value['id_media'] ?>">
                                                                <input type="hidden" name="uploaded_file_path" class="uploaded-path">
                                                                <input type="hidden" name="auto_detected_data" class="auto-detected-data">
                                                                <div class="upload-progress mt-3 d-none">
                                                                    <div class="progress mb-2" style="height: 8px;">
                                                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%"></div>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between small">
                                                                        <span class="upload-status">Upload...</span>
                                                                        <span class="upload-percent">0%</span>
                                                                    </div>
                                                                </div>
                                                                <?php if ($is_upload): ?>
                                                                    <div class="current-file mt-3 p-2 bg-white rounded border">
                                                                        <small class="d-block fw-bold"><?= basename($value['fichier']) ?></small>
                                                                        <small class="text-muted"><?= format_bytes($value['taille'] ?? 0) ?> • <?= format_duration($value['duree'] ?? 0) ?></small>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div class="new-file-info mt-2 d-none">
                                                                    <div class="alert alert-success mb-0 py-2">Nouveau fichier uploadé!</div>
                                                                </div>
                                                            </div>
                                                            <div class="mt-2 d-flex gap-2 control-buttons">
                                                                <button type="button" class="btn btn-sm btn-outline-danger cancel-upload d-none"><i class="bx bx-x me-1"></i>Annuler</button>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary pause-upload d-none"><i class="bx bx-pause me-1"></i>Pause</button>
                                                                <button type="button" class="btn btn-sm btn-outline-primary resume-upload d-none"><i class="bx bx-play me-1"></i>Reprendre</button>
                                                            </div>
                                                        </div>

                                                        <div class="link-section mb-3" id="link_section_edit_<?= $value['id_media'] ?>" style="<?= $is_link ? '' : 'display:none;' ?>">
                                                            <label class="form-label fw-bold">Lien vidéo</label>
                                                            <input type="url" class="form-control" name="lien" value="<?= htmlspecialchars($value['lien'] ?? '') ?>" placeholder="https://...">
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Catégorie</label>
                                                                <input type="text" class="form-control" name="categorie" value="<?= htmlspecialchars($value['categorie'] ?? '') ?>" list="categories_list">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Date</label>
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
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="card border h-100">
                                                            <div class="card-header bg-light"><h6 class="mb-0"><i class="bx bx-cog me-2"></i>Options</h6></div>
                                                            <div class="card-body">
                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="est_actif" value="1" <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label fw-bold">Vidéo active</label>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input share-toggle" type="checkbox" name="a_partager_reseaux" value="1" <?= (!empty($value['a_partager_reseaux']) && $value['a_partager_reseaux'] == 1) ? 'checked' : '' ?> data-target="edit_msg_<?= $value['id_media'] ?>">
                                                                        <label class="form-check-label fw-bold">Réseaux sociaux</label>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3" id="edit_msg_<?= $value['id_media'] ?>" style="<?= (!empty($value['a_partager_reseaux']) && $value['a_partager_reseaux'] == 1) ? '' : 'display:none;' ?>">
                                                                    <label class="form-label">Message</label>
                                                                    <textarea class="form-control" name="message_reseaux" rows="2" maxlength="280"><?= htmlspecialchars($value['message_reseaux'] ?? '') ?></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1" <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label fw-bold"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="is_for_website" value="1" <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label fw-bold"><i class="bx bx-globe text-primary me-1"></i>Site Web</label>
                                                                    </div>
                                                                </div>
                                                                <?php if (!empty($value['metadata_id3'])): $meta = json_decode($value['metadata_id3'], true); ?>
                                                                <hr>
                                                                <h6 class="mb-2"><i class="bx bx-info-circle me-1"></i>Métadonnées</h6>
                                                                <small class="text-muted">
                                                                    <?php if (!empty($meta['width'])): ?><div><strong>Résolution:</strong> <?= $meta['width'] ?>x<?= $meta['height'] ?></div><?php endif; ?>
                                                                    <?php if (!empty($meta['codec'])): ?><div><strong>Codec:</strong> <?= strtoupper($meta['codec']) ?></div><?php endif; ?>
                                                                    <?php if (!empty($meta['fps'])): ?><div><strong>FPS:</strong> <?= $meta['fps'] ?></div><?php endif; ?>
                                                                </small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <i class="bx bx-video-off fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">Aucune vidéo trouvée</p>
                                    <a href="javascript:;" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#create_video"><i class="bx bx-plus me-1"></i>Ajouter</a>
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouvelle Vidéo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= base_url('video/Create') ?>" method="POST" id="create_form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="titre" required maxlength="255" placeholder="Titre de la vidéo">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Type de source <span class="text-danger">*</span></label>
                                    <select class="form-select source-type-selector" name="type_source" data-target="create" id="create_type_source">
                                        <option value="upload" selected>Uploader un fichier (Max 10GB)</option>
                                        <option value="link">Lien externe (YouTube, Vimeo...)</option>
                                    </select>
                                </div>

                                <div class="upload-section mb-3" id="upload_section_create">
                                    <label class="form-label fw-bold">Fichier vidéo</label>
                                    <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_create">
                                        <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                                        <p class="mb-2">Glissez-déposez ou <span class="text-primary fw-bold browse-text">cliquez</span></p>
                                        <small class="text-muted d-block mb-2">Formats: MP4, WebM, MOV, AVI, MKV...</small>
                                        <div class="d-flex justify-content-center gap-2">
                                            <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>10GB Max</span>
                                            <span class="badge bg-info"><i class="bx bx-chip me-1"></i>2MB Chunks</span>
                                        </div>
                                        <input type="file" class="form-control d-none file-input" id="create_file_input" accept="video/*" data-upload-id="create">
                                        <input type="hidden" name="uploaded_file_path" id="create_uploaded_path">
                                        <input type="hidden" name="thumbnail" id="create_thumbnail_path">
                                        <input type="hidden" name="auto_detected_data" id="create_auto_data">
                                        
                                        <div class="upload-progress mt-3 d-none" id="create_progress_container">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span id="create_upload_status" class="fw-bold text-primary">Préparation...</span>
                                                <span id="create_upload_percent" class="badge bg-primary">0%</span>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="create_progress_bar" style="width: 0%"></div>
                                            </div>
                                            <div class="d-flex justify-content-between small text-muted mt-1">
                                                <span id="create_chunks_info">0/0 chunks</span>
                                                <span id="create_upload_speed">0 MB/s</span>
                                            </div>
                                            <div class="small text-primary mt-1" id="create_size_info">0 MB / 0 MB</div>
                                            <div class="mt-2 p-2 bg-light rounded small text-start d-none" id="create_processing_info">
                                                <i class="bx bx-cog bx-spin me-1"></i><span id="create_processing_text">Analyse vidéo...</span>
                                            </div>
                                        </div>
                                        
                                        <div class="file-info mt-2 d-none" id="create_file_info">
                                            <div class="alert alert-info mb-0 py-2">
                                                <i class="bx bx-file me-2"></i>
                                                <div>
                                                    <div id="create_file_name" class="fw-bold text-truncate" style="max-width: 300px;"></div>
                                                    <div id="create_file_size" class="small"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="upload-success mt-2 d-none" id="create_upload_success">
                                            <div class="alert alert-success mb-0 py-2">
                                                <div class="d-flex align-items-center justify-content-center mb-2">
                                                    <i class="bx bx-check-circle me-2 fs-5"></i>
                                                    <span class="fw-bold">Upload terminé!</span>
                                                </div>
                                                <div id="create_video_metadata" class="small text-start border-top pt-2 mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2 d-flex gap-2" id="create_controls">
                                        <button type="button" class="btn btn-sm btn-outline-danger cancel-upload d-none" id="create_cancel"><i class="bx bx-x me-1"></i>Annuler</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary pause-upload d-none" id="create_pause"><i class="bx bx-pause me-1"></i>Pause</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary resume-upload d-none" id="create_resume"><i class="bx bx-play me-1"></i>Reprendre</button>
                                    </div>
                                </div>

                                <div class="link-section mb-3" id="link_section_create" style="display:none;">
                                    <label class="form-label fw-bold">Lien vidéo</label>
                                    <input type="url" class="form-control" name="lien" placeholder="https://www.youtube.com/watch?v=...">
                                    <small class="text-muted">Miniature extraite automatiquement</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Catégorie</label>
                                        <input type="text" class="form-control" name="categorie" list="categories_list" placeholder="Ex: Documentaire...">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Date</label>
                                        <input type="date" class="form-control" name="date_media">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea class="form-control" name="description" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Crédits</label>
                                    <input type="text" class="form-control" name="credits" placeholder="Ex: Réalisé par...">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border h-100">
                                    <div class="card-header bg-light"><h6 class="mb-0"><i class="bx bx-share-alt me-2"></i>Options</h6></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input share-toggle" type="checkbox" name="a_partager_reseaux" value="1" data-target="create_msg_reseaux">
                                                <label class="form-check-label fw-bold">Réseaux sociaux</label>
                                            </div>
                                        </div>
                                        <div class="mb-3" id="create_msg_reseaux" style="display:none;">
                                            <label class="form-label">Message</label>
                                            <textarea class="form-control" name="message_reseaux" rows="2" maxlength="280"></textarea>
                                            <small class="text-muted">Max 280 caractères</small>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1">
                                                <label class="form-check-label fw-bold"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked>
                                                <label class="form-check-label fw-bold"><i class="bx bx-globe text-primary me-1"></i>Site Web</label>
                                            </div>
                                        </div>
                                        <div class="alert alert-info small">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Les vidéos sont analysées automatiquement (FFprobe) et des miniatures sont générées.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success" id="create_submit"><i class="bx bx-save me-1"></i>Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Video Player -->
    <div class="modal fade" id="videoPlayerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary text-white">
                    <h5 class="modal-title" id="playerTitle">Lecture</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="ratio ratio-16x9" id="playerContainer"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= base_url('video/Delete') ?>" method="POST">
                    <div class="modal-body text-center">
                        <i class="bx bx-error-circle text-danger display-4"></i>
                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                        <p class="text-muted" id="deleteVideoTitle"></p>
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-2"></i>
                            Le fichier et les miniatures seront supprimés définitivement.
                        </div>
                        <input type="hidden" name="id" id="deleteVideoId">
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger"><i class="bx bx-trash me-1"></i>Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Datalist -->
    <datalist id="categories_list">
        <option value="Documentaire"><option value="Interview"><option value="Reportage">
        <option value="Tutoriel"><option value="Promotion"><option value="Événement">
        <option value="Webinaire"><option value="Podcast"><option value="Musique"><option value="Vlog">
        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>">
        <?php endforeach; endif; ?>
    </datalist>

</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<script>
// Configuration
const UPLOAD_CONFIG = {
    baseUrl: '<?= base_url('video/') ?>',
    chunkSize: 2 * 1024 * 1024,
    maxFileSize: 10 * 1024 * 1024 * 1024,
    csrfName: '<?= $this->security->get_csrf_token_name() ?>',
    csrfHash: '<?= $this->security->get_csrf_hash() ?>'
};

let uploadManagers = new Map();

// Helpers JS
class FileUtils {
    static formatBytes(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

class ChunkedUploadManager {
    constructor(options) {
        this.config = {...UPLOAD_CONFIG, ...options};
        this.callbacks = options.callbacks || {};
        this.reset();
    }

    reset() {
        this.state = {
            file: null, uploadId: null, totalChunks: 0,
            uploadedChunks: new Set(), failedChunks: new Map(),
            isPaused: false, isCancelled: false, isUploading: false,
            startTime: null, bytesUploaded: 0
        };
    }

    async start(file) {
        try {
            this.reset();
            this.state.file = file;
            this.state.isUploading = true;
            this.state.startTime = Date.now();

            if (file.size > this.config.maxFileSize) {
                throw new Error(`Fichier trop grand. Max: ${FileUtils.formatBytes(this.config.maxFileSize)}`);
            }

            this.emit('init', {});
            const initData = await this.post('initUpload', {
                file_name: file.name,
                file_size: file.size
            });
            
            this.state.uploadId = initData.upload_id;
            this.state.totalChunks = initData.total_chunks;

            this.emit('progress', {phase: 'uploading', percent: 0});
            await this.uploadChunks();

            if (this.state.isCancelled) {
                await this.cancel();
                return;
            }

            this.emit('progress', {phase: 'finalizing', percent: 100, message: 'Traitement vidéo...'});
            const result = await this.post('completeUpload', {upload_id: this.state.uploadId});
            this.emit('complete', result);

        } catch (error) {
            this.emit('error', error.message);
        } finally {
            this.state.isUploading = false;
        }
    }

    async post(endpoint, data) {
        const formData = new FormData();
        for (let key in data) formData.append(key, data[key]);
        formData.append(this.config.csrfName, this.config.csrfHash);

        const response = await fetch(this.config.baseUrl + endpoint, {
            method: 'POST',
            body: formData,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur serveur');
        return result;
    }

    async uploadChunks() {
        const queue = [];
        for (let i = 0; i < this.state.totalChunks; i++) {
            if (!this.state.uploadedChunks.has(i)) queue.push(i);
        }

        const workers = [];
        const maxWorkers = Math.min(2, queue.length);
        for (let w = 0; w < maxWorkers; w++) workers.push(this.worker(queue));
        await Promise.all(workers);
    }

    async worker(queue) {
        while (queue.length > 0 && !this.state.isCancelled && !this.state.isPaused) {
            await this.uploadChunk(queue.shift());
        }
    }

    async uploadChunk(index, attempt = 0) {
        while (this.state.isPaused && !this.state.isCancelled) await new Promise(r => setTimeout(r, 100));
        if (this.state.isCancelled) return;

        try {
            const start = index * this.config.chunkSize;
            const end = Math.min(start + this.config.chunkSize, this.state.file.size);
            const chunk = this.state.file.slice(start, end);

            const formData = new FormData();
            formData.append('upload_id', this.state.uploadId);
            formData.append('chunk_index', index);
            formData.append('chunk', chunk);
            formData.append(this.config.csrfName, this.config.csrfHash);

            const response = await fetch(this.config.baseUrl + 'uploadChunk', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (!data.success) throw new Error(data.message);

            this.state.uploadedChunks.add(index);
            this.state.bytesUploaded += chunk.size;

            const speed = this.calculateSpeed();
            this.emit('progress', {
                phase: 'uploading',
                percent: (this.state.uploadedChunks.size / this.state.totalChunks) * 100,
                uploadedChunks: this.state.uploadedChunks.size,
                totalChunks: this.state.totalChunks,
                speed: speed,
                currentChunk: index
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

    async cancel() {
        this.state.isCancelled = true;
        if (this.state.uploadId) {
            try {
                await this.post('cancelUpload', {upload_id: this.state.uploadId});
            } catch(e) {}
        }
        this.emit('cancel');
    }

    pause() { this.state.isPaused = true; this.emit('pause'); }
    resume() { this.state.isPaused = false; this.emit('resume'); }
    emit(event, data) { if (this.callbacks[event]) this.callbacks[event](data); }
}

$(document).ready(function() {
    // Toggle AJAX WhatsApp/Web
    $(document).on('change', '.toggle-field', function() {
        const $cb = $(this), id = $cb.data('id'), field = $cb.data('field'), value = $cb.is(':checked') ? 1 : 0;
        $cb.prop('disabled', true);
        $.post(UPLOAD_CONFIG.baseUrl + 'toggleField', {
            id: id, field: field, value: value,
            [UPLOAD_CONFIG.csrfName]: UPLOAD_CONFIG.csrfHash
        }, function(r) {
            if (!r?.success) $cb.prop('checked', !value);
        }, 'json').fail(function() {
            $cb.prop('checked', !value);
        }).always(() => $cb.prop('disabled', false));
    });

    // Toggle sections
    $(document).on('change', '.source-type-selector', function() {
        const target = $(this).data('target'), value = $(this).val();
        if (target === 'create') {
            $('#upload_section_create').toggle(value === 'upload');
            $('#link_section_create').toggle(value === 'link');
        } else {
            $(`#upload_section_${target}`).toggle(value === 'upload');
            $(`#link_section_${target}`).toggle(value === 'link');
        }
    });

    // Toggle share message
    $(document).on('change', '.share-toggle', function() {
        $('#' + $(this).data('target')).toggle($(this).is(':checked'));
    });

    // Drop zones
    setupDropZone('create');
    $('.file-input[data-mode="edit"]').each(function() {
        setupDropZone($(this).data('upload-id'));
    });

    // DataTable
    if ($.fn.DataTable) {
        $('#videosTable').DataTable({
            language: {url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'},
            order: [[0, 'desc']], pageLength: 25, responsive: true
        });
    }
});

function setupDropZone(uploadId) {
    const $zone = $(`#drop_zone_${uploadId}`);
    const $input = $(`#${uploadId}_file_input, .file-input[data-upload-id="${uploadId}"]`).first();

    $zone.on('click', function(e) {
        if ($(e.target).closest('.upload-progress, button').length) return;
        $input.trigger('click');
    });

    $zone.on('dragover', e => { e.preventDefault(); $zone.addClass('drag-active'); });
    $zone.on('dragleave drop', e => { e.preventDefault(); $zone.removeClass('drag-active'); });
    $zone.on('drop', function(e) {
        const files = e.originalEvent.dataTransfer.files;
        if (files.length) handleFile(files[0], uploadId, $zone);
    });

    $input.on('change', function() {
        if (this.files[0]) handleFile(this.files[0], uploadId, $zone);
    });
}

function handleFile(file, uploadId, $zone) {
    if (!file.type.startsWith('video/')) {
        toastr.error('Veuillez sélectionner un fichier vidéo');
        return;
    }

    const isCreate = uploadId === 'create';
    
    // Show file info
    if (isCreate) {
        $('#create_file_name').text(file.name).attr('title', file.name);
        $('#create_file_size').text(FileUtils.formatBytes(file.size));
        $('#create_file_info').removeClass('d-none');
    } else {
        $zone.find('.new-file-info').removeClass('d-none');
        $zone.find('.new-file-name').text(file.name);
        $zone.find('.new-file-size').text(FileUtils.formatBytes(file.size));
    }

    startUpload(file, uploadId, $zone);
}

function startUpload(file, uploadId, $zone) {
    const ui = getUI(uploadId, $zone);
    resetUI(ui);
    showControls(ui);

    const manager = new ChunkedUploadManager({
        callbacks: {
            init: () => ui.$status.text('Initialisation...').addClass('text-primary'),
            progress: (data) => updateUI(ui, data),
            complete: (data) => handleComplete(ui, data, uploadId),
            error: (msg) => handleError(ui, msg),
            cancel: () => handleCancel(ui, uploadId),
            pause: () => { ui.$status.text('En pause'); ui.$bar.removeClass('progress-bar-animated'); },
            resume: () => { ui.$status.text('Reprise...'); ui.$bar.addClass('progress-bar-animated'); }
        }
    });

    uploadManagers.set(uploadId, manager);
    manager.start(file);
    bindControls(ui, manager);
}

function getUI(uploadId, $zone) {
    if (uploadId === 'create') {
        return {
            $progress: $('#create_progress_container'), $status: $('#create_upload_status'),
            $percent: $('#create_upload_percent'), $bar: $('#create_progress_bar'),
            $chunks: $('#create_chunks_info'), $speed: $('#create_upload_speed'),
            $sizeInfo: $('#create_size_info'), $processing: $('#create_processing_info'),
            $cancel: $('#create_cancel'), $pause: $('#create_pause'), $resume: $('#create_resume'),
            $success: $('#create_upload_success'), $metadata: $('#create_video_metadata'),
            $fileInfo: $('#create_file_info')
        };
    }
    return {
        $progress: $zone.find('.upload-progress'), $status: $zone.find('.upload-status'),
        $percent: $zone.find('.upload-percent'), $bar: $zone.find('.progress-bar'),
        $chunks: $zone.find('.upload-chunks'), $speed: $zone.find('.upload-speed'),
        $cancel: $zone.closest('.upload-section').find('.cancel-upload'),
        $pause: $zone.closest('.upload-section').find('.pause-upload'),
        $resume: $zone.closest('.upload-section').find('.resume-upload'),
        $success: $zone.find('.new-file-info'), $fileInfo: $zone.find('.current-file')
    };
}

function resetUI(ui) {
    ui.$progress.removeClass('d-none');
    ui.$bar.css('width', '0%').attr('aria-valuenow', 0);
    ui.$percent.text('0%');
    ui.$status.text('Préparation...');
    if (ui.$chunks) ui.$chunks.text('0/0 chunks');
    if (ui.$speed) ui.$speed.text('0 MB/s');
    if (ui.$sizeInfo) ui.$sizeInfo.text('0 MB / 0 MB');
    if (ui.$processing) ui.$processing.addClass('d-none');
    ui.$success.addClass('d-none');
}

function showControls(ui) {
    ui.$cancel.removeClass('d-none');
    ui.$pause.removeClass('d-none');
    ui.$resume.addClass('d-none');
}

function updateUI(ui, data) {
    const pct = Math.round(data.percent);
    ui.$bar.css('width', pct + '%').attr('aria-valuenow', pct);
    ui.$percent.text(pct + '%');
    
    if (data.phase === 'finalizing') {
        ui.$status.text(data.message);
        if (ui.$processing) ui.$processing.removeClass('d-none');
    } else {
        ui.$status.text(`Upload: ${pct}%`);
        if (ui.$chunks) ui.$chunks.text(`${data.uploadedChunks}/${data.totalChunks} chunks`);
        if (ui.$speed) ui.$speed.text(data.speed.toFixed(2) + ' MB/s');
        if (ui.$sizeInfo && data.uploadedChunks) {
            const mgr = uploadManagers.get('create');
            const uploaded = (data.uploadedChunks / data.totalChunks) * (mgr?.state.file.size || 0);
            ui.$sizeInfo.text(`${FileUtils.formatBytes(uploaded)} / ${FileUtils.formatBytes(mgr?.state.file.size || 0)}`);
        }
    }
}

function handleComplete(ui, data, uploadId) {
    ui.$progress.addClass('d-none');
    ui.$cancel.addClass('d-none');
    ui.$pause.addClass('d-none');
    ui.$resume.addClass('d-none');

    if (uploadId === 'create') {
        $('#create_uploaded_path').val(data.file_path);
        $('#create_thumbnail_path').val(data.thumbnail || '');
        $('#create_auto_data').val(JSON.stringify(data.suggested_data || {}));
        
        let metaHtml = '';
        if (data.duration_formatted) metaHtml += `<div><i class="bx bx-time me-1"></i>Durée: ${data.duration_formatted}</div>`;
        if (data.width && data.height) metaHtml += `<div><i class="bx bx-tv me-1"></i>Résolution: ${data.width}x${data.height}</div>`;
        if (data.file_size_formatted) metaHtml += `<div><i class="bx bx-hdd me-1"></i>Taille: ${data.file_size_formatted}</div>`;
        ui.$metadata.html(metaHtml);
        ui.$success.removeClass('d-none');

        if (!$('input[name="titre"]').val() && data.suggested_data?.titre) {
            $('input[name="titre"]').val(data.suggested_data.titre);
        }
    } else {
        const $zone = $(`#drop_zone_${uploadId}`);
        $zone.closest('form').find('.uploaded-path').val(data.file_path);
        $zone.closest('form').find('.auto-detected-data').val(JSON.stringify(data.suggested_data || {}));
        ui.$success.html('<div class="alert alert-success mb-0 py-2"><i class="bx bx-check-circle me-2"></i>Nouveau fichier uploadé!</div>').removeClass('d-none');
    }

    toastr.success('Upload terminé!');
    uploadManagers.delete(uploadId);
}

function handleError(ui, message) {
    ui.$progress.addClass('d-none');
    ui.$cancel.addClass('d-none');
    ui.$pause.addClass('d-none');
    toastr.error(message, 'Erreur', {timeOut: 10000});
    uploadManagers.clear();
}

function handleCancel(ui, uploadId) {
    ui.$progress.addClass('d-none');
    ui.$cancel.addClass('d-none');
    ui.$pause.addClass('d-none');
    if (uploadId === 'create') {
        $('#create_file_info, #create_upload_success').addClass('d-none');
        $('#create_uploaded_path, #create_thumbnail_path').val('');
    }
    uploadManagers.delete(uploadId);
}

function bindControls(ui, manager) {
    ui.$cancel.off('click').on('click', () => { if (confirm('Annuler?')) manager.cancel(); });
    ui.$pause.off('click').on('click', () => { manager.pause(); ui.$pause.addClass('d-none'); ui.$resume.removeClass('d-none'); });
    ui.$resume.off('click').on('click', () => { manager.resume(); ui.$resume.addClass('d-none'); ui.$pause.removeClass('d-none'); });
}

// Form validation
$('#create_form').on('submit', function(e) {
    if ($('#create_type_source').val() === 'upload' && !$('#create_uploaded_path').val()) {
        e.preventDefault();
        toastr.warning('Veuillez attendre la fin de l\'upload');
        return false;
    }
});

// Reset modal
$('#create_video').on('hidden.bs.modal', function() {
    const mgr = uploadManagers.get('create');
    if (mgr?.state.isUploading) mgr.cancel();
    $(this).find('form')[0].reset();
    $('#create_progress_container, #create_file_info, #create_upload_success').addClass('d-none');
    $('#create_cancel, #create_pause, #create_resume').addClass('d-none');
    $('#create_progress_bar').css('width', '0%');
    $('#create_upload_percent').text('0%');
    $('#create_uploaded_path, #create_thumbnail_path, #create_auto_data').val('');
});

// Global functions
function openVideoPlayer(id) {
    alert('Lecture vidéo ID: ' + id);
}

function confirmDelete(id, title) {
    $('#deleteVideoId').val(id);
    $('#deleteVideoTitle').text(title);
    $('#deleteConfirmModal').modal('show');
}

function toggleStatus(id, currentStatus) {
    if (!confirm('Changer le statut?')) return;
    const newStatus = currentStatus == 1 ? 0 : 1;
    $.post(UPLOAD_CONFIG.baseUrl + 'ChangeStatus', {
        id: id, est_actif: currentStatus,
        [UPLOAD_CONFIG.csrfName]: UPLOAD_CONFIG.csrfHash
    }, function(r) {
        if (r) {
            const $badge = $(`.status-badge-${id}`);
            $badge.removeClass('bg-success bg-danger').addClass(newStatus == 1 ? 'bg-success' : 'bg-danger').text(newStatus == 1 ? 'Actif' : 'Inactif');
            toastr.success('Statut mis à jour');
        }
    });
}
</script>

<style>
.upload-zone { transition: all 0.3s ease; cursor: pointer; position: relative; border: 2px dashed #dee2e6 !important; }
.upload-zone:hover { border-color: #0d6efd !important; background-color: #f8f9fa !important; }
.upload-zone.drag-active { border-color: #0d6efd !important; background-color: #e7f1ff !important; transform: scale(1.01); }
.browse-text { cursor: pointer; text-decoration: underline; }
.video-thumbnail-container { position: relative; overflow: hidden; border-radius: 6px; cursor: pointer; }
.play-overlay { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.8; transition: all 0.3s; background: rgba(0,0,0,0.5); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; }
.play-overlay i { font-size: 1.25rem; color: white; }
.video-thumbnail-container:hover .play-overlay { opacity: 1; transform: translate(-50%, -50%) scale(1.1); background: rgba(13, 110, 253, 0.8); }
.duration-badge { position: absolute; bottom: 4px; right: 4px; }
.duration-badge .badge { font-size: 0.7rem; padding: 0.25em 0.5em; }
</style>