<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// ============================================================================
// HELPERS PHP - YouTube-Style Video Processing
// ============================================================================

if (!function_exists('format_duration')) {
    function format_duration($seconds) {
        if (empty($seconds) || $seconds <= 0) return '0:00';
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%d:%02d', $minutes, $secs);
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

if (!function_exists('get_quality_badge')) {
    function get_quality_badge($metadata_json) {
        if (empty($metadata_json)) return ['label' => 'SD', 'class' => 'secondary', 'height' => 0];
        $meta = json_decode($metadata_json, true);
        if (!empty($meta['analysis']['height'])) {
            $h = $meta['analysis']['height'];
            if ($h >= 2160) return ['label' => '4K', 'class' => 'danger', 'height' => $h];
            if ($h >= 1440) return ['label' => '2K', 'class' => 'warning', 'height' => $h];
            if ($h >= 1080) return ['label' => 'HD', 'class' => 'primary', 'height' => $h];
            if ($h >= 720) return ['label' => '720p', 'class' => 'success', 'height' => $h];
            return ['label' => '480p', 'class' => 'info', 'height' => $h];
        }
        return ['label' => 'SD', 'class' => 'secondary', 'height' => 0];
    }
}

if (!function_exists('extract_youtube_id')) {
    function extract_youtube_id($url) {
        if (empty($url)) return null;
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/',
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
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
                        <li class="breadcrumb-item active" aria-current="page">Studio Vidéo AVC v5.0</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a class="btn btn-youtube btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bx bx-upload"></i> <span class="d-none d-sm-inline">Uploader</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Dashboard -->
        <div class="row mb-4 g-3">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body position-relative">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-youtube bg-opacity-10 p-3 me-3">
                                <i class="bx bx-video text-youtube fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Vidéos</h6>
                                <h3 class="mb-0 fw-bold"><?= count($videos ?? []) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body position-relative">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="bx bx-time text-success fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Durée totale</h6>
                                <h3 class="mb-0 fw-bold"><?= format_duration($total_duration ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body position-relative">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                <i class="bx bx-hdd text-info fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Stockage utilisé</h6>
                                <h3 class="mb-0 fw-bold"><?= $storage_stats['total_used'] ?? '0 MB' ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body position-relative">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                <i class="bx bx-chip text-warning fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Encodage AVC</h6>
                                <h3 class="mb-0 fw-bold">
                                    <?= !empty($avc_capabilities['features']['hardware_encoding']) ? '<span class="text-success"><i class="bx bx-check"></i> GPU</span>' : '<span class="text-muted">CPU</span>' ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Videos Grid -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0"><i class="bx bx-collection me-2 text-youtube"></i>Bibliothèque</h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="filterQuality" style="width: auto;">
                            <option value="">Toutes qualités</option>
                            <option value="4K">4K</option>
                            <option value="1080">1080p</option>
                            <option value="720">720p</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="videosTable" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">Vidéo</th>
                                <th width="30%">Détails</th>
                                <th width="10%">Qualité</th>
                                <th width="10%">Durée</th>
                                <th width="10%">Statut</th>
                                <th width="8%">Visibilité</th>
                                <th width="7%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($videos)): foreach ($videos as $value): 
                            $meta = !empty($value['metadata_id3']) ? json_decode($value['metadata_id3'], true) : [];
                            $quality_info = get_quality_badge($value['metadata_id3']);
                            
                            $is_upload = !empty($value['fichier']);
                            $is_link = !empty($value['lien']);
                            
                            if ($is_upload) {
                                $source_badge = '<span class="badge bg-dark"><i class="bx bx-hdd me-1"></i>Upload</span>';
                            } elseif (strpos($value['lien'] ?? '', 'youtube') !== false) {
                                $source_badge = '<span class="badge bg-youtube"><i class="bx bxl-youtube me-1"></i>YT</span>';
                            } else {
                                $source_badge = '<span class="badge bg-info"><i class="bx bx-link me-1"></i>URL</span>';
                            }

                            $thumb_url = base_url('assets/images/video-placeholder.jpg');
                            if (!empty($value['miniature'])) {
                                $thumb_url = (strpos($value['miniature'], 'http') === 0) ? $value['miniature'] : base_url($value['miniature']);
                            } elseif ($is_link) {
                                $yt_id = extract_youtube_id($value['lien']);
                                if ($yt_id) $thumb_url = "https://img.youtube.com/vi/{$yt_id}/mqdefault.jpg";
                            }
                            
                            $js_title = htmlspecialchars($value['titre'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr data-id="<?= $value['id_media'] ?>" data-quality="<?= $quality_info['height'] ?>">
                                <td>
                                    <div class="video-thumb-wrapper position-relative" style="width: 160px; height: 90px;">
                                        <img src="<?= $thumb_url ?>" 
                                             class="rounded w-100 h-100" 
                                             style="object-fit: cover; background: #000;"
                                             loading="lazy"
                                             onerror="this.src='<?= base_url('assets/images/video-placeholder.jpg') ?>'">
                                        <?php if (!empty($value['duree']) && $value['duree'] > 0): ?>
                                        <div class="position-absolute bottom-0 end-0 m-1">
                                            <span class="badge bg-dark bg-opacity-75" style="font-size: 0.7rem;"><?= format_duration($value['duree']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="video-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                                             onclick="openPlayer(<?= $value['id_media'] ?>, '<?= $js_title ?>')"
                                             style="background: rgba(0,0,0,0); transition: all 0.3s; cursor: pointer;">
                                            <i class="bx bx-play-circle text-white fs-1 opacity-0" style="transition: all 0.3s;"></i>
                                        </div>
                                        <div class="position-absolute top-0 start-0 m-1">
                                            <span class="badge bg-<?= $quality_info['class'] ?>" style="font-size: 0.6rem;"><?= $quality_info['label'] ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <h6 class="mb-1 fw-bold text-truncate" style="max-width: 300px;"><?= htmlspecialchars($value['titre'] ?? 'Sans titre') ?></h6>
                                        <div class="d-flex align-items-center gap-2 mb-1"><?= $source_badge ?>
                                            <?php if (!empty($value['categorie'])): ?><span class="badge bg-light text-dark border"><?= htmlspecialchars($value['categorie']) ?></span><?php endif; ?>
                                        </div>
                                        <?php if (!empty($meta['analysis'])): ?>
                                        <div class="mt-1 small">
                                            <span class="badge bg-light text-dark border me-1"><i class="bx bx-tv me-1"></i><?= $meta['analysis']['resolution'] ?? 'N/A' ?></span>
                                            <span class="badge bg-light text-dark border"><i class="bx bx-chip me-1"></i><?= strtoupper($meta['analysis']['codec_original'] ?? 'N/A') ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><span class="badge bg-<?= $quality_info['class'] ?>"><?= $quality_info['label'] ?></span></td>
                                <td><span class="badge bg-dark"><i class="bx bx-time me-1"></i><?= format_duration($value['duree'] ?? 0) ?></span></td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <a href="javascript:void(0)" class="text-decoration-none" data-bs-toggle="dropdown">
                                            <?php if (!empty($value['est_actif']) && $value['est_actif'] == 1): ?>
                                                <span class="badge bg-success">Publié</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Privé</span>
                                            <?php endif; ?>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleStatus(<?= $value['id_media'] ?>, 1)"><i class="bx bx-globe me-2 text-success"></i>Public</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleStatus(<?= $value['id_media'] ?>, 0)"><i class="bx bx-lock me-2 text-secondary"></i>Privé</a></li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-2 align-items-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" data-id="<?= $value['id_media'] ?>" data-field="is_for_whatsapp" <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?> title="WhatsApp">
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" data-id="<?= $value['id_media'] ?>" data-field="is_for_website" <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?> title="Site Web">
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
    <div class="d-flex gap-2 justify-content-center">
        <!-- Icône Lire -->
        <button class="btn btn-sm btn-outline-success rounded-circle" 
                onclick="openPlayer(<?= $value['id_media'] ?>, '<?= $js_title ?>')" 
                title="Lire la vidéo"
                style="width: 32px; height: 32px; padding: 0;">
            <i class="bx bx-play fs-5"></i>
        </button>
        
        <!-- Icône Modifier -->
        <button class="btn btn-sm btn-outline-primary rounded-circle" 
                data-bs-toggle="modal" 
                data-bs-target="#editModal<?= $value['id_media'] ?>" 
                title="Modifier la vidéo"
                style="width: 32px; height: 32px; padding: 0;">
            <i class="bx bx-edit fs-5"></i>
        </button>
        
        <!-- Icône Supprimer -->
        <button class="btn btn-sm btn-outline-danger rounded-circle" 
                onclick="confirmDelete(<?= $value['id_media'] ?>, '<?= $js_title ?>')" 
                title="Supprimer la vidéo"
                style="width: 32px; height: 32px; padding: 0;">
            <i class="bx bx-trash fs-5"></i>
        </button>
    </div>
</td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la vidéo</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('video/Update') ?>" method="POST" id="editForm<?= $value['id_media'] ?>">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="mb-3"><label class="form-label fw-bold">Titre <span class="text-danger">*</span></label><input type="text" class="form-control form-control-lg" name="titre" value="<?= htmlspecialchars($value['titre'] ?? '') ?>" required maxlength="255"></div>
                                                        <div class="mb-3"><label class="form-label fw-bold">Description</label><textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($value['description'] ?? '') ?></textarea></div>
                                                        <div class="card border mb-3">
                                                            <div class="card-header bg-light"><h6 class="mb-0"><i class="bx bx-image me-2"></i>Miniature</h6></div>
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-5">
                                                                        <label class="form-label small text-muted">Miniature actuelle</label>
                                                                        <div class="position-relative">
                                                                            <?php $current_thumb = $value['miniature'] ?? ''; $thumb_display = $current_thumb ? base_url($current_thumb) : base_url('assets/images/video-placeholder.jpg'); ?>
                                                                            <img src="<?= $thumb_display ?>" class="rounded w-100" style="height: 120px; object-fit: cover;" id="currentThumb<?= $value['id_media'] ?>" onerror="this.src='<?= base_url('assets/images/video-placeholder.jpg') ?>'">
                                                                            <?php if ($is_upload && !empty($meta['thumbnails'])): ?>
                                                                            <div class="mt-2"><label class="form-label small text-muted">Changer pour :</label><div class="d-flex gap-2 flex-wrap">
                                                                                <?php if (!empty($meta['thumbnails']['default'])): ?><img src="<?= base_url($meta['thumbnails']['default']) ?>" class="rounded cursor-pointer edit-thumb-option" style="width: 80px; height: 45px; object-fit: cover; border: 2px solid transparent;" onclick="selectEditThumbnail(<?= $value['id_media'] ?>, '<?= $meta['thumbnails']['default'] ?>', this)" data-thumb="<?= $meta['thumbnails']['default'] ?>"><?php endif; ?>
                                                                                <?php if (!empty($meta['thumbnails']['poster'])): ?><img src="<?= base_url($meta['thumbnails']['poster']) ?>" class="rounded cursor-pointer edit-thumb-option" style="width: 80px; height: 45px; object-fit: cover; border: 2px solid transparent;" onclick="selectEditThumbnail(<?= $value['id_media'] ?>, '<?= $meta['thumbnails']['poster'] ?>', this)" data-thumb="<?= $meta['thumbnails']['poster'] ?>"><?php endif; ?>
                                                                            </div></div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-7">
                                                                        <label class="form-label small text-muted">Ou uploader une nouvelle</label>
                                                                        <div class="upload-thumbnail-zone border rounded p-3 text-center mb-2" style="border-style: dashed !important; cursor: pointer; background: #f8f9fa;" onclick="document.getElementById('editThumbInput<?= $value['id_media'] ?>').click()"><i class="bx bx-cloud-upload fs-3 text-muted mb-2"></i><p class="mb-0 small text-muted">Cliquez pour uploader</p><p class="mb-0" style="font-size: 0.7rem; color: #999;">JPG, PNG, WEBP (max 2MB)</p></div>
                                                                        <input type="file" id="editThumbInput<?= $value['id_media'] ?>" class="d-none" accept="image/*" onchange="uploadEditThumbnail(<?= $value['id_media'] ?>, this.files[0])">
                                                                        <div id="editThumbPreview<?= $value['id_media'] ?>" class="d-none position-relative"><img src="" class="rounded w-100" style="height: 120px; object-fit: cover;" id="editThumbImg<?= $value['id_media'] ?>"><button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeEditThumbnail(<?= $value['id_media'] ?>)"><i class="bx bx-x"></i></button><div class="position-absolute bottom-0 start-0 w-100 bg-success text-white text-center small"><i class="bx bx-check me-1"></i>Nouvelle miniature</div></div>
                                                                        <div id="editThumbProgress<?= $value['id_media'] ?>" class="d-none mt-2"><div class="progress" style="height: 4px;"><div class="progress-bar bg-success" style="width: 0%"></div></div></div>
                                                                        <input type="hidden" name="thumbnail" id="editThumbSelected<?= $value['id_media'] ?>" value="<?= htmlspecialchars($current_thumb) ?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3"><label class="form-label fw-bold">Catégorie</label><input type="text" class="form-control" name="categorie" value="<?= htmlspecialchars($value['categorie'] ?? '') ?>" list="categoriesList"></div>
                                                            <div class="col-md-6 mb-3"><label class="form-label fw-bold">Date</label><input type="date" class="form-control" name="date_media" value="<?= $value['date_media'] ?? '' ?>"></div>
                                                        </div>
                                                        <div class="mb-3"><label class="form-label fw-bold">Crédits / Auteur</label><input type="text" class="form-control" name="credits" value="<?= htmlspecialchars($value['credits'] ?? '') ?>"></div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="card border h-100">
                                                            <div class="card-header bg-light"><h6 class="mb-0"><i class="bx bx-cog me-2"></i>Paramètres</h6></div>
                                                            <div class="card-body">
                                                                <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="est_actif" value="1" <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>><label class="form-check-label fw-bold">Vidéo publique</label></div></div>
                                                                <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1" <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>><label class="form-check-label"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label></div></div>
                                                                <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_for_website" value="1" <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>><label class="form-check-label"><i class="bx bx-globe text-primary me-1"></i>Site Web</label></div></div>
                                                                <?php if (!empty($meta['analysis'])): ?>
                                                                <hr><h6 class="mb-2"><i class="bx bx-info-circle me-1"></i>Métadonnées</h6>
                                                                <ul class="list-unstyled small text-muted mb-0">
                                                                    <li><strong>Durée:</strong> <?= $meta['analysis']['duration_formatted'] ?? 'N/A' ?></li>
                                                                    <li><strong>Résolution:</strong> <?= $meta['analysis']['resolution'] ?? 'N/A' ?></li>
                                                                    <li><strong>Codec:</strong> <?= strtoupper($meta['analysis']['codec_original'] ?? 'N/A') ?></li>
                                                                    <li><strong>FPS:</strong> <?= $meta['analysis']['fps'] ?? 'N/A' ?></li>
                                                                </ul>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Enregistrer</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <tr><td colspan="7" class="text-center py-5"><div class="empty-state"><i class="bx bx-video-off fs-1 text-muted mb-3"></i><h5>Aucune vidéo</h5><p class="text-muted">Commencez par uploader votre première vidéo</p><a href="javascript:;" class="btn btn-youtube" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bx bx-upload me-1"></i>Uploader</a></div></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-youtube text-white border-0">
                    <h5 class="modal-title"><i class="bx bx-upload me-2"></i>Uploader des vidéos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeUploadModal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="uploadStep1" class="upload-zone p-5 text-center">
                        <div class="upload-illustration mb-4"><div class="position-relative d-inline-block"><i class="bx bx-cloud-upload text-youtube" style="font-size: 5rem;"></i><div class="position-absolute bottom-0 end-0 bg-success rounded-circle p-2"><i class="bx bx-plus text-white"></i></div></div></div>
                        <h4 class="mb-3">Glissez-déposez des fichiers vidéo</h4>
                        <p class="text-muted mb-4">ou <span class="text-youtube fw-bold cursor-pointer" onclick="document.getElementById('fileInput').click()">parcourir</span> pour sélectionner</p>
                        <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap"><span class="badge bg-light text-dark border">MP4</span><span class="badge bg-light text-dark border">MOV</span><span class="badge bg-light text-dark border">AVI</span><span class="badge bg-light text-dark border">MKV</span><span class="badge bg-light text-dark border">WebM</span></div>
                        <div class="alert alert-light border mx-auto" style="max-width: 500px;"><small class="text-muted"><i class="bx bx-info-circle me-1"></i><strong>Technologie AVC/H.264:</strong> Upload chunked 1.5MB, encodage multi-bitrate auto, miniatures intelligentes. Max 2GB.</small></div>
                        <input type="file" id="fileInput" class="d-none" accept="video/*" multiple>
                    </div>

                    <div id="uploadStep2" class="d-none p-4">
                        <div class="upload-item mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0"><div class="bg-youtube bg-opacity-10 rounded p-2"><i class="bx bx-film text-youtube fs-4"></i></div></div>
                                <div class="flex-grow-1 ms-3"><h6 class="mb-1 fw-bold" id="uploadFileName">video.mp4</h6><small class="text-muted" id="uploadFileSize">0 MB</small></div>
                                <div class="flex-shrink-0"><button class="btn btn-sm btn-outline-danger" id="cancelUploadBtn"><i class="bx bx-x"></i></button></div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1"><span class="fw-bold text-youtube" id="uploadPhase">Préparation...</span><span class="fw-bold" id="uploadPercent">0%</span></div>
                                <div class="progress" style="height: 8px;"><div class="progress-bar bg-youtube progress-bar-striped progress-bar-animated" id="uploadProgressBar" style="width: 0%"></div></div>
                                <div class="d-flex justify-content-between mt-1 small text-muted"><span id="uploadSpeed">0 MB/s</span><span id="uploadChunks">0 / 0 chunks</span></div>
                            </div>
                            <div id="processingStatus" class="d-none"><div class="card bg-light border-0"><div class="card-body py-3"><h6 class="mb-3"><i class="bx bx-cog bx-spin me-2 text-youtube"></i>Traitement AVC...</h6></div></div></div>
                        </div>
                    </div>

                    <div id="uploadStep3" class="d-none">
                        <form id="videoDetailsForm" action="<?= base_url('video/Create') ?>" method="POST">
                            <div class="row g-0">
                                <div class="col-md-8 p-4 border-end">
                                    <div class="mb-3"><label class="form-label fw-bold">Titre <span class="text-danger">*</span></label><input type="text" class="form-control form-control-lg" name="titre" id="videoTitle" required maxlength="255"></div>
                                    <div class="mb-3"><label class="form-label fw-bold">Description</label><textarea class="form-control" name="description" rows="5" id="videoDescription"></textarea></div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Miniature</label>
                                            <ul class="nav nav-tabs mb-3" id="thumbnailTab" role="tablist">
                                                <li class="nav-item"><button class="nav-link active" id="generated-tab" data-bs-toggle="tab" data-bs-target="#generated-thumbnails" type="button" role="tab"><i class="bx bx-video me-1"></i>Générées</button></li>
                                                <li class="nav-item"><button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-thumbnail" type="button" role="tab"><i class="bx bx-upload me-1"></i>Upload</button></li>
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane fade show active" id="generated-thumbnails"><div class="d-flex gap-2 flex-wrap" id="thumbnailSelector"></div></div>
                                                <div class="tab-pane fade" id="upload-thumbnail">
                                                    <div class="upload-thumbnail-zone border rounded p-3 text-center" style="border-style: dashed !important; cursor: pointer; background: #f8f9fa;" onclick="document.getElementById('customThumbnailInput').click()"><i class="bx bx-image-add fs-2 text-muted mb-2"></i><p class="mb-1 text-muted small">Cliquez pour uploader une image</p><p class="mb-0 text-muted" style="font-size: 0.75rem;">JPG, PNG, GIF, WEBP (max 2MB)</p></div>
                                                    <input type="file" id="customThumbnailInput" class="d-none" accept="image/*">
                                                    <div id="customThumbnailPreview" class="mt-2 d-none"><div class="position-relative d-inline-block"><img src="" class="rounded" style="width: 120px; height: 68px; object-fit: cover;" id="customThumbnailImg"><button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" style="padding: 0.1rem 0.3rem; font-size: 0.7rem;" onclick="removeCustomThumbnail()"><i class="bx bx-x"></i></button><div class="position-absolute bottom-0 start-0 w-100 bg-success text-white text-center small"><i class="bx bx-check me-1"></i>Personnalisée</div></div></div>
                                                    <div id="thumbnailUploadProgress" class="mt-2 d-none"><div class="progress" style="height: 4px;"><div class="progress-bar bg-success" style="width: 0%"></div></div><small class="text-muted">Upload...</small></div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="thumbnail" id="selectedThumbnail">
                                        </div>
                                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">Catégorie</label><input type="text" class="form-control" name="categorie" id="videoCategory" list="categoriesList"></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label fw-bold">Crédits</label><input type="text" class="form-control" name="credits" id="videoCredits"></div>
                                </div>
                                <div class="col-md-4 bg-light p-4">
                                    <h6 class="mb-3"><i class="bx bx-cog me-2"></i>Paramètres</h6>
                                    <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="est_actif" value="1" checked><label class="form-check-label fw-bold">Public</label></div></div>
                                    <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1"><label class="form-check-label"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label></div></div>
                                    <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked><label class="form-check-label"><i class="bx bx-globe text-primary me-1"></i>Site Web</label></div></div>
                                    <hr class="my-4"><h6 class="mb-2"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                                    <ul class="list-unstyled small text-muted mb-0" id="videoInfoList"></ul>
                                    <input type="hidden" name="uploaded_file_path" id="uploadedFilePath"><input type="hidden" name="auto_detected_data" id="autoDetectedData"><input type="hidden" name="type_source" value="upload">
                                </div>
                            </div>
                            <div class="p-4 border-top bg-white"><div class="d-flex justify-content-between"><button type="button" class="btn btn-outline-secondary" onclick="resetUpload()"><i class="bx bx-arrow-back me-1"></i>Annuler</button><button type="submit" class="btn btn-youtube btn-lg"><i class="bx bx-save me-1"></i>Publier</button></div></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Player Modal -->
    <div class="modal fade" id="playerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark border-0">
                <div class="modal-header border-secondary border-bottom-0"><h5 class="modal-title text-white" id="playerTitle"></h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-0"><div class="ratio ratio-16x9 bg-black" id="playerContainer"></div></div>
                <div class="modal-footer border-secondary border-top-0 bg-dark"><div class="text-white-50 small" id="playerStats"></div></div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="bx bx-trash me-2"></i>Supprimer</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form action="<?= base_url('video/Delete') ?>" method="POST"><div class="modal-body text-center py-4"><i class="bx bx-error-circle text-danger display-4 mb-3"></i><h5>Confirmer la suppression</h5><p class="text-muted" id="deleteVideoTitle"></p><div class="alert alert-warning"><i class="bx bx-info-circle me-2"></i>Cette action supprimera définitivement la vidéo.</div><input type="hidden" name="id" id="deleteVideoId"></div><div class="modal-footer justify-content-center"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-danger"><i class="bx bx-trash me-1"></i>Supprimer</button></div></form></div>
        </div>
    </div>

    <datalist id="categoriesList">
        <option value="Documentaire"><option value="Interview"><option value="Reportage"><option value="Tutoriel"><option value="Promotion"><option value="Événement"><option value="Webinaire"><option value="Podcast"><option value="Musique"><option value="Vlog">
        <?php if (!empty($categories)): foreach ($categories as $cat): ?><option value="<?= htmlspecialchars($cat) ?>"><?php endforeach; endif; ?>
    </datalist>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
toastr.options = { "closeButton": true, "debug": false, "newestOnTop": true, "progressBar": true, "positionClass": "toast-top-right", "preventDuplicates": false, "showDuration": "300", "hideDuration": "1000", "timeOut": "5000" };

const UPLOAD_CONFIG = {
    baseUrl: '<?= base_url('video/') ?>',
    chunkSize: null,
    maxFileSize: 2 * 1024 * 1024 * 1024
};

let uploadManager = null;

class FileUtils {
    static formatBytes(b) { if(!b||b===0)return'0 B';const k=1024,s=['B','KB','MB','GB','TB'],i=Math.floor(Math.log(b)/Math.log(k));return parseFloat((b/Math.pow(k,i)).toFixed(2))+' '+s[i];}
}

class VideoUploadManager {
    constructor(){this.reset();}
    reset(){this.state={file:null,uploadId:null,totalChunks:0,chunkSize:0,uploadedChunks:new Set(),failedChunks:new Map(),isCancelled:false,isUploading:false,startTime:null,bytesUploaded:0,metadata:null};}
    async start(file){
        try{
            this.reset();this.state.file=file;this.state.isUploading=true;this.state.startTime=Date.now();
            if(file.size>UPLOAD_CONFIG.maxFileSize)throw new Error(`Fichier trop grand. Max 2GB`);
            this.updateUI('init',{fileName:file.name,fileSize:FileUtils.formatBytes(file.size)});
            const initData=await this.apiCall('initUpload',{file_name:file.name,file_size:file.size});
            this.state.uploadId=initData.upload_id;this.state.totalChunks=initData.total_chunks;this.state.chunkSize=initData.chunk_size;
            console.log(`Upload: ${this.state.totalChunks} chunks de ${FileUtils.formatBytes(this.state.chunkSize)}`);
            this.updateUI('uploading',{percent:0});await this.uploadAllChunks();
            if(this.state.isCancelled)return;
            this.updateUI('processing',{message:'Traitement AVC...'});
            const result=await this.apiCall('completeUpload',{upload_id:this.state.uploadId});
            this.state.metadata=result.data;this.updateUI('complete',result.data);
        }catch(error){console.error('Upload error:',error);this.updateUI('error',{message:error.message});}
        finally{this.state.isUploading=false;}
    }
    async uploadAllChunks(){
        for(let i=0;i<this.state.totalChunks;i++){
            if(this.state.isCancelled)break;
            let attempt=0,maxAttempts=3,success=false;
            while(!success&&attempt<maxAttempts&&!this.state.isCancelled){
                try{await this.uploadSingleChunk(i);success=true;}
                catch(error){attempt++;console.error(`Chunk ${i} failed (${attempt}/${maxAttempts}):`,error);if(attempt<maxAttempts)await new Promise(r=>setTimeout(r,2000));else throw new Error(`Chunk ${i} échoué`);}
            }
        }
    }
    async uploadSingleChunk(index){
        const start=index*this.state.chunkSize,end=Math.min(start+this.state.chunkSize,this.state.file.size),chunk=this.state.file.slice(start,end);
        const formData=new FormData();formData.append('upload_id',this.state.uploadId);formData.append('chunk_index',index);formData.append('chunk',chunk);
        const controller=new AbortController(),timeoutId=setTimeout(()=>controller.abort(),30000);
        const response=await fetch(UPLOAD_CONFIG.baseUrl+'uploadChunk',{method:'POST',body:formData,signal:controller.signal});
        clearTimeout(timeoutId);
        if(!response.ok)throw new Error(`HTTP ${response.status}`);
        const text=await response.text();let data;try{data=JSON.parse(text);}catch(e){throw new Error('Réponse invalide');}
        if(!data.success)throw new Error(data.message);
        this.state.uploadedChunks.add(index);this.state.bytesUploaded+=chunk.size;
        const progress=(this.state.uploadedChunks.size/this.state.totalChunks)*100,speed=((Date.now()-this.state.startTime)/1000)>0?(this.state.bytesUploaded/((Date.now()-this.state.startTime)/1000))/(1024*1024):0;
        this.updateUI('progress',{percent:progress,uploadedChunks:this.state.uploadedChunks.size,totalChunks:this.state.totalChunks,speed:speed});
    }
    async apiCall(endpoint,data){const fd=new FormData();for(let k in data)fd.append(k,data[k]);const r=await fetch(UPLOAD_CONFIG.baseUrl+endpoint,{method:'POST',body:fd});if(!r.ok)throw new Error(`HTTP ${r.status}`);const t=await r.text();let res;try{res=JSON.parse(t);}catch(e){throw new Error('Réponse invalide');}if(!res.success)throw new Error(res.message);return res;}
    cancel(){this.state.isCancelled=true;this.updateUI('cancel');}
    updateUI(e,d={}){const h={init:()=>{$('#uploadStep1').addClass('d-none');$('#uploadStep2').removeClass('d-none');$('#uploadFileName').text(d.fileName);$('#uploadFileSize').text(d.fileSize);},uploading:()=>{$('#uploadPhase').text('Upload en cours...');$('#processingStatus').removeClass('d-none');},progress:()=>{$('#uploadProgressBar').css('width',Math.round(d.percent)+'%');$('#uploadPercent').text(Math.round(d.percent)+'%');$('#uploadChunks').text(`${d.uploadedChunks}/${d.totalChunks} chunks`);$('#uploadSpeed').text(d.speed.toFixed(2)+' MB/s');},processing:()=>{$('#uploadPhase').text('Traitement AVC...');$('#uploadProgressBar').removeClass('progress-bar-animated');},complete:()=>{$('#uploadStep2').addClass('d-none');$('#uploadStep3').removeClass('d-none');populateDetailsForm(d);},error:()=>{toastr.error(d.message,'Erreur');resetUpload();},cancel:()=>{resetUpload();}};if(h[e])h[e]();}
}

function populateDetailsForm(data){
    if(data.form_suggestions){$('#videoTitle').val(data.form_suggestions.titre||'');$('#videoCredits').val(data.form_suggestions.credits||'');$('#videoCategory').val(data.form_suggestions.categorie||'');}
    let infoHtml='';if(data.analysis){infoHtml+=`<li><i class="bx bx-time me-2"></i>Durée: ${data.analysis.duration_formatted||'N/A'}</li><li><i class="bx bx-tv me-2"></i>Résolution: ${data.analysis.resolution||'N/A'}</li><li><i class="bx bx-chip me-2"></i>Codec: ${data.analysis.codec_original||'N/A'}</li>`;}
    if(data.file_size)infoHtml+=`<li><i class="bx bx-hdd me-2"></i>Taille: ${data.file_size}</li>`;
    $('#videoInfoList').html(infoHtml||'<li>Aucune information</li>');
    $('#uploadedFilePath').val(data.original_file||'');$('#autoDetectedData').val(JSON.stringify(data));
    $('#selectedThumbnail').val('');$('#customThumbnailPreview').addClass('d-none');$('#customThumbnailInput').val('');$('#thumbnailUploadProgress').addClass('d-none');
    $('#upload-tab').removeClass('active');$('#generated-tab').addClass('active');$('#generated-thumbnails').addClass('show active');$('#upload-thumbnail').removeClass('show active');
    let thumbnails=data.thumbnails||{};if(Array.isArray(thumbnails)){let t={};thumbnails.forEach((item,i)=>{if(item)t['thumb_'+i]=item;});thumbnails=t;}
    let thumbHtml='',firstThumbUrl=null,thumbCount=0;
    Object.entries(thumbnails).forEach(([type,url])=>{if(url&&typeof url==='string'){const fullUrl=url.startsWith('http')?url:'<?= base_url() ?>'+url;if(thumbCount===0)firstThumbUrl=url;thumbHtml+=`<div class="position-relative cursor-pointer thumbnail-option ${thumbCount===0?'selected':''}" onclick="selectThumbnail('${url}', this)" data-url="${url}" style="width:120px;height:68px;border:2px solid ${thumbCount===0?'#FF0000':'transparent'};border-radius:4px;overflow:hidden;"><img src="${fullUrl}" class="w-100 h-100" style="object-fit:cover;"><div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-white text-center" style="font-size:0.6rem;">${type==='default'?'Auto':type}</div>${thumbCount===0?'<div class="position-absolute top-0 end-0 m-1"><i class="bx bx-check-circle text-success bg-white rounded-circle"></i></div>':''}</div>`;thumbCount++;}});
    if(thumbCount===0)thumbHtml='<div class="alert alert-warning w-100 mb-0"><i class="bx bx-info-circle me-1"></i>Aucune miniature générée. Vous pouvez en uploader une.</div>';
    $('#thumbnailSelector').html(thumbHtml);
    if(firstThumbUrl)$('#selectedThumbnail').val(firstThumbUrl);
}

function selectThumbnail(url,element){$('#selectedThumbnail').val(url);$('.thumbnail-option').css('border','2px solid transparent').find('.bx-check-circle').remove();$(element).css('border','2px solid #FF0000');if(!$(element).find('.bx-check-circle').length)$(element).append('<div class="position-absolute top-0 end-0 m-1"><i class="bx bx-check-circle text-success bg-white rounded-circle"></i></div>');removeCustomThumbnail();toastr.success('Miniature sélectionnée');}

function uploadCustomThumbnail(file){
    if(!file)return;if(!['image/jpeg','image/png','image/gif','image/webp'].includes(file.type)){toastr.error('Format non supporté');return;}
    if(file.size>2*1024*1024){toastr.error('Image trop grande (max 2MB)');return;}
    $('#thumbnailUploadProgress').removeClass('d-none');$('#thumbnailUploadProgress .progress-bar').css('width','0%');
    const fd=new FormData();fd.append('thumbnail_file',file);
    $.ajax({url:UPLOAD_CONFIG.baseUrl+'uploadThumbnail',type:'POST',data:fd,processData:false,contentType:false,dataType:'json',xhr:function(){const x=new XMLHttpRequest();x.upload.addEventListener('progress',function(e){if(e.lengthComputable)$('#thumbnailUploadProgress .progress-bar').css('width',(e.loaded/e.total*100)+'%');},false);return x;},success:function(r){$('#thumbnailUploadProgress').addClass('d-none');if(r.success&&r.file_path){$('#customThumbnailImg').attr('src',r.preview_url);$('#customThumbnailPreview').removeClass('d-none');$('#selectedThumbnail').val(r.file_path);$('.thumbnail-option').css('border','2px solid transparent').find('.bx-check-circle').remove();$('#generated-tab').removeClass('active');$('#upload-tab').addClass('active');$('#generated-thumbnails').removeClass('show active');$('#upload-thumbnail').addClass('show active');toastr.success('Miniature uploadée');}else toastr.error(r.message||'Erreur');},error:function(){$('#thumbnailUploadProgress').addClass('d-none');toastr.error('Erreur réseau');}});}

function removeCustomThumbnail(){$('#customThumbnailPreview').addClass('d-none');$('#customThumbnailImg').attr('src','');$('#customThumbnailInput').val('');const ft=$('.thumbnail-option').first();if(ft.length){const url=ft.data('url');if(url)selectThumbnail(url,ft[0]);}else $('#selectedThumbnail').val('');}

function selectEditThumbnail(id,url,el){$('#editThumbSelected'+id).val(url);const fullUrl=url.startsWith('http')?url:'<?= base_url() ?>'+url;$('#currentThumb'+id).attr('src',fullUrl);$('#editModal'+id+' .edit-thumb-option').css('border-color','transparent');$(el).css('border-color','#FF0000');$('#editThumbPreview'+id).addClass('d-none');toastr.info('Miniature sélectionnée');}

function uploadEditThumbnail(id,file){
    if(!file)return;if(!['image/jpeg','image/png','image/gif','image/webp'].includes(file.type)){toastr.error('Format non supporté');return;}
    if(file.size>2*1024*1024){toastr.error('Image trop grande (max 2MB)');return;}
    $('#editThumbProgress'+id).removeClass('d-none');$('#editThumbProgress'+id+' .progress-bar').css('width','0%');
    const fd=new FormData();fd.append('thumbnail_file',file);
    $.ajax({url:UPLOAD_CONFIG.baseUrl+'uploadThumbnail',type:'POST',data:fd,processData:false,contentType:false,dataType:'json',xhr:function(){const x=new XMLHttpRequest();x.upload.addEventListener('progress',function(e){if(e.lengthComputable)$('#editThumbProgress'+id+' .progress-bar').css('width',(e.loaded/e.total*100)+'%');},false);return x;},success:function(r){$('#editThumbProgress'+id).addClass('d-none');if(r.success&&r.file_path){$('#editThumbImg'+id).attr('src',r.preview_url);$('#editThumbPreview'+id).removeClass('d-none');$('#editThumbSelected'+id).val(r.file_path);$('#currentThumb'+id).attr('src',r.preview_url);$('#editModal'+id+' .edit-thumb-option').css('border-color','transparent');toastr.success('Nouvelle miniature prête');}else toastr.error(r.message||'Erreur');},error:function(){$('#editThumbProgress'+id).addClass('d-none');toastr.error('Erreur réseau');}});}

function removeEditThumbnail(id){$('#editThumbPreview'+id).addClass('d-none');$('#editThumbInput'+id).val('');toastr.info('Miniature restaurée');}

function resetUpload(){if(uploadManager)uploadManager.reset();$('#uploadStep1').removeClass('d-none');$('#uploadStep2').addClass('d-none');$('#uploadStep3').addClass('d-none');$('#processingStatus').addClass('d-none');$('#uploadProgressBar').css('width','0%');$('#uploadPercent').text('0%');$('#videoDetailsForm')[0].reset();$('#thumbnailSelector').empty();$('#customThumbnailPreview').addClass('d-none');$('#customThumbnailInput').val('');$('#selectedThumbnail').val('');$('#fileInput').val('');}

function openPlayer(id,title){$('#playerTitle').text(title||'Lecture vidéo');const modalEl=document.getElementById('playerModal');if(modalEl){const modal=new bootstrap.Modal(modalEl);modal.show();}$('#playerContainer').html(`<video controls autoplay class="w-100 h-100"><source src="${UPLOAD_CONFIG.baseUrl}stream/progressive/${id}" type="video/mp4"></video>`);}
function confirmDelete(id,title){$('#deleteVideoId').val(id);$('#deleteVideoTitle').text(title);const modalEl=document.getElementById('deleteModal');if(modalEl){const modal=new bootstrap.Modal(modalEl);modal.show();}}
function toggleStatus(id,status){$.ajax({url:UPLOAD_CONFIG.baseUrl+'ChangeStatus',type:'POST',data:{id:id,est_actif:status},dataType:'json',success:function(r){if(r&&r.success)location.reload();else toastr.error('Erreur');},error:function(){toastr.error('Erreur réseau');}});}

$(document).ready(function(){
    $('#fileInput').on('change',function(e){if(this.files.length>0){uploadManager=new VideoUploadManager();uploadManager.start(this.files[0]);}});
    $(document).on('change','#customThumbnailInput',function(e){if(this.files&&this.files[0])uploadCustomThumbnail(this.files[0]);});
    $('.upload-zone').on('dragover',function(e){e.preventDefault();$(this).addClass('drag-active');}).on('dragleave',function(e){e.preventDefault();$(this).removeClass('drag-active');}).on('drop',function(e){e.preventDefault();$(this).removeClass('drag-active');const files=e.originalEvent.dataTransfer.files;if(files.length>0&&files[0].type.startsWith('video/')){uploadManager=new VideoUploadManager();uploadManager.start(files[0]);}else toastr.error('Déposez un fichier vidéo');});
    $('#cancelUploadBtn').on('click',function(){if(confirm('Annuler l\'upload ?')&&uploadManager)uploadManager.cancel();});
    $('#uploadModal').on('hide.bs.modal',function(e){if(uploadManager&&uploadManager.state.isUploading&&!confirm('Upload en cours. Fermer ?')){e.preventDefault();return false;}});
    $(document).on('change','.form-check-input[data-field]',function(){const $cb=$(this),id=$cb.data('id'),field=$cb.data('field'),value=$cb.is(':checked')?1:0;$cb.prop('disabled',true);$.ajax({url:UPLOAD_CONFIG.baseUrl+'toggleField',type:'POST',data:{id:id,field:field,value:value},dataType:'json',success:function(r){if(r&&r.success)toastr.success('Mis à jour');else{$cb.prop('checked',!value);toastr.error('Erreur');}},error:function(){$cb.prop('checked',!value);toastr.error('Erreur réseau');},complete:function(){$cb.prop('disabled',false);}});});
    if($.fn.DataTable)$('#videosTable').DataTable({language:{url:'//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'},order:[[0,'desc']],pageLength:25});
    $('#filterQuality').on('change',function(){const q=$(this).val();if(q){$('#videosTable tbody tr').each(function(){const h=$(this).data('quality');if(q==='4K'&&h>=2160)$(this).show();else if(q==='1080'&&h>=1080&&h<2160)$(this).show();else if(q==='720'&&h>=720&&h<1080)$(this).show();else $(this).hide();});}else $('#videosTable tbody tr').show();});
    console.log('✅ Scripts chargés - Mode AVC avec chunks 1.5MB');
});

document.addEventListener('DOMContentLoaded',function(){const pm=document.getElementById('playerModal');if(pm){pm.addEventListener('hidden.bs.modal',function(){$('#playerContainer').html('');});}});
</script>

<style>
.bg-youtube{background-color:#FF0000!important}.text-youtube{color:#FF0000!important}.btn-youtube{background-color:#FF0000;border-color:#FF0000;color:white}.btn-youtube:hover{background-color:#CC0000;color:white}.upload-zone{min-height:400px;display:flex;flex-direction:column;align-items:center;justify-content:center;transition:all .3s;cursor:pointer}.upload-zone.drag-active{background-color:#f8f9fa;transform:scale(1.02)}.video-thumb-wrapper{position:relative;overflow:hidden;border-radius:8px;background:#000}.video-overlay:hover{background:rgba(0,0,0,0.3)!important}.video-overlay:hover i{opacity:1!important;transform:scale(1.2)}.thumbnail-option{transition:all .2s;border:2px solid transparent;border-radius:4px;cursor:pointer}.thumbnail-option:hover{border-color:#FF0000}.thumbnail-option.selected{border-color:#FF0000}.upload-thumbnail-zone{transition:all .3s}.upload-thumbnail-zone:hover{background:#e9ecef!important;border-color:#FF0000!important}.cursor-pointer{cursor:pointer}::-webkit-scrollbar{width:8px}::-webkit-scrollbar-track{background:#f1f1f1}::-webkit-scrollbar-thumb{background:#888;border-radius:4px}
</style>