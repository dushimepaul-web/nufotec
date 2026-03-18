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
        if (!empty($meta['height'])) {
            $h = $meta['height'];
            if ($h >= 2160) return ['label' => '4K', 'class' => 'danger', 'height' => $h];
            if ($h >= 1440) return ['label' => '2K', 'class' => 'warning', 'height' => $h];
            if ($h >= 1080) return ['label' => 'HD', 'class' => 'primary', 'height' => $h];
            if ($h >= 720) return ['label' => '720p', 'class' => 'success', 'height' => $h];
            return ['label' => '480p', 'class' => 'info', 'height' => $h];
        }
        return ['label' => 'SD', 'class' => 'secondary', 'height' => 0];
    }
}

if (!function_exists('get_streaming_status')) {
    function get_streaming_status($video) {
        $meta = !empty($video['metadata_id3']) ? json_decode($video['metadata_id3'], true) : [];
        $status = [];
        
        if (!empty($meta['encoding_jobs'])) {
            foreach ($meta['encoding_jobs'] as $quality => $job) {
                $status[$quality] = $job['status'] ?? 'pending';
            }
        }
        return $status;
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
                    <a class="btn btn-outline-dark btn-sm" href="<?= base_url('video/diagnostics') ?>" target="_blank" title="Diagnostics système">
                        <i class="bx bx-test-tube"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Dashboard YouTube-Style -->
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
                        <div class="position-absolute top-0 end-0 p-2 opacity-10">
                            <i class="bx bx-video fs-1"></i>
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
                                    <?= $avc_capabilities['features']['hardware_encoding'] ? '<span class="text-success"><i class="bx bx-check"></i> GPU</span>' : '<span class="text-muted">CPU</span>' ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AVC Capabilities Banner -->
        <?php if (!empty($avc_capabilities)): ?>
        <div class="card border-0 shadow-sm mb-4 bg-gradient-dark text-white overflow-hidden" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-2"><i class="bx bx-movie-play me-2"></i>Pipeline AVC/H.264 YouTube-Style</h5>
                        <p class="mb-0 small opacity-75">
                            <i class="bx bx-check-circle me-1 text-success"></i>Multi-bitrate encoding 
                            <i class="bx bx-check-circle me-1 text-success ms-3"></i>DASH/HLS Streaming 
                            <i class="bx bx-check-circle me-1 text-success ms-3"></i>Scene Detection 
                            <i class="bx bx-check-circle me-1 text-success ms-3"></i>Hardware Accel
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="badge bg-youtube me-2">H.264</span>
                        <span class="badge bg-success me-2">DASH</span>
                        <span class="badge bg-info">HLS</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Messages Flash -->
        <?php $this->load->view('includes/backend/FlashMessages.php'); ?>

        <!-- Videos Grid YouTube-Style -->
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
                        <select class="form-select form-select-sm" id="filterStatus" style="width: auto;">
                            <option value="">Tous statuts</option>
                            <option value="active">Actif</option>
                            <option value="processing">En traitement</option>
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
                                <th width="25%">Détails</th>
                                <th width="15%">Qualités AVC</th>
                                <th width="10%">Streaming</th>
                                <th width="10%">Statut</th>
                                <th width="8%">Visibilité</th>
                                <th width="7%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($videos)): $i = 1; foreach ($videos as $value): 
                            $meta = !empty($value['metadata_id3']) ? json_decode($value['metadata_id3'], true) : [];
                            $quality_info = get_quality_badge($value['metadata_id3']);
                            $encoding_status = get_streaming_status($value);
                            
                            $is_upload = !empty($value['fichier']);
                            $is_link = !empty($value['lien']);
                            
                            // Source badge
                            if ($is_upload) {
                                $source_badge = '<span class="badge bg-dark"><i class="bx bx-hdd me-1"></i>Upload</span>';
                            } elseif (strpos($value['lien'] ?? '', 'youtube') !== false) {
                                $source_badge = '<span class="badge bg-youtube"><i class="bx bxl-youtube me-1"></i>YT</span>';
                            } else {
                                $source_badge = '<span class="badge bg-info"><i class="bx bx-link me-1"></i>URL</span>';
                            }

                            // Thumbnail with YouTube-style overlay
                            $thumb_url = base_url('assets/images/video-placeholder.jpg');
                            $poster_url = null;
                            if (!empty($value['miniature'])) {
                                $thumb_url = (strpos($value['miniature'], 'http') === 0) ? $value['miniature'] : base_url($value['miniature']);
                            } elseif ($is_link && preg_match('/youtube\.com|youtu\.be/', $value['lien'])) {
                                preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\s]+)/', $value['lien'], $matches);
                                if (!empty($matches[1])) {
                                    $thumb_url = "https://img.youtube.com/vi/{$matches[1]}/mqdefault.jpg";
                                    $poster_url = "https://img.youtube.com/vi/{$matches[1]}/maxresdefault.jpg";
                                }
                            }
                            
                            // Check for generated poster
                            if (!empty($meta['thumbnails']['poster'])) {
                                $poster_url = base_url($meta['thumbnails']['poster']);
                            }
                        ?>
                            <tr data-id="<?= $value['id_media'] ?>" data-quality="<?= $quality_info['height'] ?>">
                                <td>
                                    <div class="video-thumb-wrapper position-relative" style="width: 160px; height: 90px;">
                                        <img src="<?= $thumb_url ?>" 
                                             class="rounded w-100 h-100" 
                                             style="object-fit: cover; background: #000;"
                                             loading="lazy"
                                             onerror="this.src='<?= base_url('assets/images/video-placeholder.jpg') ?>'">
                                        
                                        <!-- Duration Badge -->
                                        <?php if (!empty($value['duree']) && $value['duree'] > 0): ?>
                                        <div class="position-absolute bottom-0 end-0 m-1">
                                            <span class="badge bg-dark bg-opacity-75" style="font-size: 0.7rem;">
                                                <?= format_duration($value['duree']) ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Play Overlay -->
                                        <div class="video-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                                             onclick="openPlayer(<?= $value['id_media'] ?>, '<?= htmlspecialchars(addslashes($value['titre'] ?? ''), ENT_QUOTES) ?>')"
                                             style="background: rgba(0,0,0,0); transition: all 0.3s; cursor: pointer;">
                                            <i class="bx bx-play-circle text-white fs-1 opacity-0" style="transition: all 0.3s;"></i>
                                        </div>
                                        
                                        <!-- Quality Badge -->
                                        <div class="position-absolute top-0 start-0 m-1">
                                            <span class="badge bg-<?= $quality_info['class'] ?>" style="font-size: 0.6rem;">
                                                <?= $quality_info['label'] ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <h6 class="mb-1 fw-bold text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($value['titre'] ?? '') ?>">
                                            <?= htmlspecialchars($value['titre'] ?? 'Sans titre') ?>
                                        </h6>
                                        
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <?= $source_badge ?>
                                            <?php if (!empty($value['categorie'])): ?>
                                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($value['categorie']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($value['description'])): ?>
                                            <small class="text-muted text-truncate" style="max-width: 280px;">
                                                <?= htmlspecialchars(substr($value['description'], 0, 80)) ?>...
                                            </small>
                                        <?php endif; ?>
                                        
                                        <div class="mt-1 small text-muted">
                                            <?php if (!empty($value['credits'])): ?>
                                                <span class="me-2"><i class="bx bx-user me-1"></i><?= htmlspecialchars($value['credits']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($value['taille'])): ?>
                                                <span><i class="bx bx-hdd me-1"></i><?= format_bytes($value['taille']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($meta['analysis'])): ?>
                                        <div class="mt-1 small">
                                            <span class="badge bg-light text-dark border me-1">
                                                <i class="bx bx-tv me-1"></i><?= $meta['analysis']['resolution'] ?? 'N/A' ?>
                                            </span>
                                            <span class="badge bg-light text-dark border me-1">
                                                <i class="bx bx-film me-1"></i><?= $meta['analysis']['fps'] ?? '?' ?>fps
                                            </span>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bx bx-chip me-1"></i><?= strtoupper($meta['analysis']['codec_original'] ?? 'N/A') ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <?php if (!empty($meta['encoding_jobs'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($meta['encoding_jobs'] as $quality => $job): 
                                                $status_class = [
                                                    'completed' => 'success',
                                                    'encoding' => 'warning',
                                                    'pending' => 'secondary',
                                                    'error' => 'danger'
                                                ][$job['status']] ?? 'secondary';
                                            ?>
                                                <span class="badge bg-<?= $status_class ?>" style="font-size: 0.7rem;" 
                                                      title="<?= $job['config']['video_bitrate'] ?? '' ?>">
                                                    <?= $quality ?>
                                                    <?php if ($job['status'] === 'encoding'): ?>
                                                        <i class="bx bx-loader-alt bx-spin ms-1"></i>
                                                    <?php elseif ($job['status'] === 'completed'): ?>
                                                        <i class="bx bx-check ms-1"></i>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if (!empty($meta['target_ladder'])): ?>
                                            <small class="text-muted d-block mt-1">
                                                Ladder: <?= implode(', ', $meta['target_ladder']) ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Standard</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if (!empty($meta['streaming'])): ?>
                                        <div class="d-flex flex-column gap-1">
                                            <?php if (!empty($meta['streaming']['dash'])): ?>
                                                <span class="badge bg-success" style="font-size: 0.7rem;">
                                                    <i class="bx bx-broadcast me-1"></i>DASH
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($meta['streaming']['hls'])): ?>
                                                <span class="badge bg-info" style="font-size: 0.7rem;">
                                                    <i class="bx bx-apple me-1"></i>HLS
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($meta['streaming']['dash']['representations'])): ?>
                                            <small class="text-muted"><?= $meta['streaming']['dash']['representations'] ?> qualités</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Progressive</span>
                                    <?php endif; ?>
                                </td>

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
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="toggleStatus(<?= $value['id_media'] ?>, 1)">
                                                    <i class="bx bx-globe me-2 text-success"></i>Public
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="toggleStatus(<?= $value['id_media'] ?>, 0)">
                                                    <i class="bx bx-lock me-2 text-secondary"></i>Privé
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column gap-2 align-items-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   data-id="<?= $value['id_media'] ?>" data-field="is_for_whatsapp"
                                                   <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>
                                                   title="WhatsApp">
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   data-id="<?= $value['id_media'] ?>" data-field="is_for_website"
                                                   <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>
                                                   title="Site Web">
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="openPlayer(<?= $value['id_media'] ?>, '<?= htmlspecialchars(addslashes($value['titre'] ?? ''), ENT_QUOTES) ?>')">
                                                    <i class="bx bx-play me-2 text-success"></i>Lire
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#editModal<?= $value['id_media'] ?>">
                                                    <i class="bx bx-edit me-2 text-primary"></i>Modifier
                                                </a>
                                            </li>
                                            <?php if ($is_upload && !empty($meta['streaming']['dash']['manifest'])): ?>
                                                <li>
                                                    <a class="dropdown-item" href="<?= base_url($meta['streaming']['dash']['manifest']) ?>" target="_blank">
                                                        <i class="bx bx-file me-2 text-info"></i>Manifest DASH
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmDelete(<?= $value['id_media'] ?>, '<?= htmlspecialchars(addslashes($value['titre'] ?? ''), ENT_QUOTES) ?>')">
                                                    <i class="bx bx-trash me-2"></i>Supprimer
                                                </a>
                                            </li>
                                        </ul>
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
                                        <form action="<?= base_url('video/Update') ?>" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                                
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control form-control-lg" name="titre" 
                                                                   value="<?= htmlspecialchars($value['titre'] ?? '') ?>" required maxlength="255">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Description</label>
                                                            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Catégorie</label>
                                                                <input type="text" class="form-control" name="categorie" 
                                                                       value="<?= htmlspecialchars($value['categorie'] ?? '') ?>" list="categoriesList">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Date</label>
                                                                <input type="date" class="form-control" name="date_media" value="<?= $value['date_media'] ?? '' ?>">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Crédits / Auteur</label>
                                                            <input type="text" class="form-control" name="credits" 
                                                                   value="<?= htmlspecialchars($value['credits'] ?? '') ?>">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="card border h-100">
                                                            <div class="card-header bg-light">
                                                                <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Paramètres</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="est_actif" value="1" 
                                                                               <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label fw-bold">Vidéo publique</label>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1"
                                                                               <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="is_for_website" value="1"
                                                                               <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label"><i class="bx bx-globe text-primary me-1"></i>Site Web</label>
                                                                    </div>
                                                                </div>

                                                                <?php if (!empty($meta['analysis'])): ?>
                                                                <hr>
                                                                <h6 class="mb-2"><i class="bx bx-info-circle me-1"></i>Métadonnées AVC</h6>
                                                                <ul class="list-unstyled small text-muted mb-0">
                                                                    <li><strong>Durée:</strong> <?= $meta['analysis']['duration_formatted'] ?? 'N/A' ?></li>
                                                                    <li><strong>Résolution:</strong> <?= $meta['analysis']['resolution'] ?? 'N/A' ?></li>
                                                                    <li><strong>Codec:</strong> <?= strtoupper($meta['analysis']['codec_original'] ?? 'N/A') ?></li>
                                                                    <li><strong>FPS:</strong> <?= $meta['analysis']['fps'] ?? 'N/A' ?></li>
                                                                    <li><strong>Bitrate:</strong> <?= $meta['analysis']['bitrate'] ?? 'N/A' ?></li>
                                                                </ul>
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
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bx bx-video-off fs-1 text-muted mb-3"></i>
                                        <h5>Aucune vidéo</h5>
                                        <p class="text-muted">Commencez par uploader votre première vidéo AVC</p>
                                        <a href="javascript:;" class="btn btn-youtube" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                            <i class="bx bx-upload me-1"></i>Uploader
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

    <!-- Upload Modal - YouTube Studio Style -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-youtube text-white border-0">
                    <h5 class="modal-title"><i class="bx bx-upload me-2"></i>Uploader des vidéos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeUploadModal"></button>
                </div>
                <div class="modal-body p-0">
                    
                    <!-- Step 1: Select File -->
                    <div id="uploadStep1" class="upload-zone p-5 text-center">
                        <div class="upload-illustration mb-4">
                            <div class="position-relative d-inline-block">
                                <i class="bx bx-cloud-upload text-youtube" style="font-size: 5rem;"></i>
                                <div class="position-absolute bottom-0 end-0 bg-success rounded-circle p-2">
                                    <i class="bx bx-plus text-white"></i>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="mb-3">Glissez-déposez des fichiers vidéo</h4>
                        <p class="text-muted mb-4">
                            ou <span class="text-youtube fw-bold cursor-pointer" onclick="document.getElementById('fileInput').click()">parcourir</span> pour sélectionner
                        </p>
                        
                        <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
                            <span class="badge bg-light text-dark border">MP4</span>
                            <span class="badge bg-light text-dark border">MOV</span>
                            <span class="badge bg-light text-dark border">AVI</span>
                            <span class="badge bg-light text-dark border">MKV</span>
                            <span class="badge bg-light text-dark border">WebM</span>
                        </div>
                        
                        <div class="alert alert-light border mx-auto" style="max-width: 500px;">
                            <small class="text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Technologie AVC/H.264:</strong> Encodage multi-bitrate automatique, 
                                streaming DASH/HLS, miniatures intelligentes. Max 50GB.
                            </small>
                        </div>
                        
                        <input type="file" id="fileInput" class="d-none" accept="video/*" multiple>
                    </div>

                    <!-- Step 2: Upload Progress -->
                    <div id="uploadStep2" class="d-none p-4">
                        <div class="upload-item mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-youtube bg-opacity-10 rounded p-2">
                                        <i class="bx bx-film text-youtube fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fw-bold" id="uploadFileName">video.mp4</h6>
                                    <small class="text-muted" id="uploadFileSize">0 MB</small>
                                </div>
                                <div class="flex-shrink-0">
                                    <button class="btn btn-sm btn-outline-danger" id="cancelUploadBtn">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Progress -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-youtube" id="uploadPhase">Préparation...</span>
                                    <span class="fw-bold" id="uploadPercent">0%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-youtube progress-bar-striped progress-bar-animated" 
                                         id="uploadProgressBar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1 small text-muted">
                                    <span id="uploadSpeed">0 MB/s</span>
                                    <span id="uploadChunks">0 / 0 chunks</span>
                                </div>
                            </div>
                            
                            <!-- Processing Status -->
                            <div id="processingStatus" class="d-none">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-3">
                                        <h6 class="mb-3"><i class="bx bx-cog bx-spin me-2 text-youtube"></i>Traitement AVC...</h6>
                                        <div class="row g-2" id="encodingLadder">
                                            <!-- Dynamic encoding status -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Details Form -->
                    <div id="uploadStep3" class="d-none">
                        <form id="videoDetailsForm" action="<?= base_url('video/Create') ?>" method="POST">
                            <div class="row g-0">
                                <div class="col-md-8 p-4 border-end">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" name="titre" id="videoTitle" required maxlength="255">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea class="form-control" name="description" rows="5" id="videoDescription"></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Miniature</label>
                                            <div class="d-flex gap-2 flex-wrap" id="thumbnailSelector">
                                                <!-- Generated thumbnails -->
                                            </div>
                                            <input type="hidden" name="thumbnail" id="selectedThumbnail">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Catégorie</label>
                                            <input type="text" class="form-control" name="categorie" list="categoriesList">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Crédits</label>
                                        <input type="text" class="form-control" name="credits" id="videoCredits">
                                    </div>
                                </div>
                                
                                <div class="col-md-4 bg-light p-4">
                                    <h6 class="mb-3"><i class="bx bx-cog me-2"></i>Paramètres</h6>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="est_actif" value="1" checked>
                                            <label class="form-check-label fw-bold">Public</label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1">
                                            <label class="form-check-label"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked>
                                            <label class="form-check-label"><i class="bx bx-globe text-primary me-1"></i>Site Web</label>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <h6 class="mb-2"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                                    <ul class="list-unstyled small text-muted mb-0" id="videoInfoList">
                                        <!-- Dynamic info -->
                                    </ul>
                                    
                                    <input type="hidden" name="uploaded_file_path" id="uploadedFilePath">
                                    <input type="hidden" name="auto_detected_data" id="autoDetectedData">
                                    <input type="hidden" name="type_source" value="upload">
                                </div>
                            </div>
                            
                            <div class="p-4 border-top bg-white">
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetUpload()">
                                        <i class="bx bx-arrow-back me-1"></i>Annuler
                                    </button>
                                    <button type="submit" class="btn btn-youtube btn-lg">
                                        <i class="bx bx-save me-1"></i>Publier
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Video Player Modal - YouTube Style -->
    <div class="modal fade" id="playerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark border-0">
                <div class="modal-header border-secondary border-bottom-0">
                    <h5 class="modal-title text-white" id="playerTitle"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="ratio ratio-16x9 bg-black" id="playerContainer">
                        <!-- Video player injected here -->
                    </div>
                </div>
                <div class="modal-footer border-secondary border-top-0 bg-dark">
                    <div class="d-flex gap-2 w-100 justify-content-between align-items-center">
                        <div class="btn-group">
                            <button class="btn btn-outline-light btn-sm" id="playerQualityBtn">
                                <i class="bx bx-cog me-1"></i>Qualité
                            </button>
                            <button class="btn btn-outline-light btn-sm" onclick="toggleFullscreen()">
                                <i class="bx bx-fullscreen"></i>
                            </button>
                        </div>
                        <div class="text-white-50 small" id="playerStats">
                            <!-- Stats -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Supprimer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= base_url('video/Delete') ?>" method="POST">
                    <div class="modal-body text-center py-4">
                        <i class="bx bx-error-circle text-danger display-4 mb-3"></i>
                        <h5>Confirmer la suppression</h5>
                        <p class="text-muted" id="deleteVideoTitle"></p>
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-2"></i>
                            Cette action supprimera définitivement la vidéo et tous les fichiers encodés (DASH, HLS, miniatures).
                        </div>
                        <input type="hidden" name="id" id="deleteVideoId">
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

    <!-- Datalist -->
    <datalist id="categoriesList">
        <option value="Documentaire">
        <option value="Interview">
        <option value="Reportage">
        <option value="Tutoriel">
        <option value="Promotion">
        <option value="Événement">
        <option value="Webinaire">
        <option value="Podcast">
        <option value="Musique">
        <option value="Vlog">
        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>">
        <?php endforeach; endif; ?>
    </datalist>

</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<script>
// YouTube-Style Upload Configuration
const UPLOAD_CONFIG = {
    baseUrl: '<?= base_url('video/') ?>',
    chunkSize: 5 * 1024 * 1024, // 5MB chunks as per controller
    maxFileSize: 50 * 1024 * 1024 * 1024, // 50GB
    csrfName: '<?= $this->security->get_csrf_token_name() ?>',
    csrfHash: '<?= $this->security->get_csrf_hash() ?>'
};

let currentUpload = null;
let uploadManager = null;

// Utility Classes
class FileUtils {
    static formatBytes(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    static formatDuration(seconds) {
        if (!seconds) return '0:00';
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        if (hrs > 0) return `${hrs}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
}

// AVC Upload Manager
class AVCUploadManager {
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
            isPaused: false,
            isCancelled: false,
            isUploading: false,
            startTime: null,
            bytesUploaded: 0,
            metadata: null
        };
    }
    
    async start(file) {
        try {
            this.reset();
            this.state.file = file;
            this.state.isUploading = true;
            this.state.startTime = Date.now();
            
            // Validate
            if (file.size > UPLOAD_CONFIG.maxFileSize) {
                throw new Error(`Fichier trop grand. Maximum: ${FileUtils.formatBytes(UPLOAD_CONFIG.maxFileSize)}`);
            }
            
            // Update UI
            this.updateUI('init', { fileName: file.name, fileSize: FileUtils.formatBytes(file.size) });
            
            // Initialize upload session
            const initData = await this.apiCall('initUpload', {
                file_name: file.name,
                file_size: file.size,
                file_hash: await this.calculateHash(file)
            });
            
            this.state.uploadId = initData.upload_id;
            this.state.totalChunks = initData.total_chunks;
            
            // Start chunk upload
            this.updateUI('uploading', { percent: 0 });
            await this.uploadChunks();
            
            if (this.state.isCancelled) {
                await this.cancel();
                return;
            }
            
            // Complete upload - triggers AVC encoding pipeline
            this.updateUI('processing', { message: 'Analyse et encodage AVC...' });
            const result = await this.apiCall('completeUpload', {
                upload_id: this.state.uploadId,
                description: '',
                generate_previews: 'true',
                target_qualities: JSON.stringify(initData.avc_ready ? ['360p', '720p', '1080p'] : [])
            });
            
            this.state.metadata = result.data;
            this.updateUI('complete', result.data);
            
        } catch (error) {
            this.updateUI('error', { message: error.message });
        } finally {
            this.state.isUploading = false;
        }
    }
    
    async apiCall(endpoint, data) {
        const formData = new FormData();
        for (let key in data) formData.append(key, data[key]);
        formData.append(UPLOAD_CONFIG.csrfName, UPLOAD_CONFIG.csrfHash);
        
        const response = await fetch(UPLOAD_CONFIG.baseUrl + endpoint, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur serveur');
        return result;
    }
    
    async calculateHash(file) {
        // Simplified hash - in production use crypto.subtle
        return file.name + file.size + file.lastModified;
    }
    
    async uploadChunks() {
        const queue = [];
        for (let i = 0; i < this.state.totalChunks; i++) {
            if (!this.state.uploadedChunks.has(i)) queue.push(i);
        }
        
        // Parallel upload with 3 workers as per controller config
        const workers = [];
        const maxWorkers = Math.min(3, queue.length);
        for (let w = 0; w < maxWorkers; w++) {
            workers.push(this.worker(queue));
        }
        await Promise.all(workers);
    }
    
    async worker(queue) {
        while (queue.length > 0 && !this.state.isCancelled && !this.state.isPaused) {
            await this.uploadChunk(queue.shift());
        }
    }
    
    async uploadChunk(index, attempt = 0) {
        while (this.state.isPaused && !this.state.isCancelled) {
            await new Promise(r => setTimeout(r, 100));
        }
        if (this.state.isCancelled) return;
        
        try {
            const start = index * UPLOAD_CONFIG.chunkSize;
            const end = Math.min(start + UPLOAD_CONFIG.chunkSize, this.state.file.size);
            const chunk = this.state.file.slice(start, end);
            
            const formData = new FormData();
            formData.append('upload_id', this.state.uploadId);
            formData.append('chunk_index', index);
            formData.append('chunk', chunk);
            formData.append(UPLOAD_CONFIG.csrfName, UPLOAD_CONFIG.csrfHash);
            
            const response = await fetch(UPLOAD_CONFIG.baseUrl + 'uploadChunk', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
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
    
    async cancel() {
        this.state.isCancelled = true;
        if (this.state.uploadId) {
            try {
                await this.apiCall('cancelUpload', { upload_id: this.state.uploadId });
            } catch(e) {}
        }
        this.updateUI('cancel');
    }
    
    pause() { 
        this.state.isPaused = true; 
        this.updateUI('pause');
    }
    
    resume() { 
        this.state.isPaused = false; 
        this.updateUI('resume');
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
                $('#uploadPhase').text('Upload en cours...').addClass('text-youtube');
            },
            progress: () => {
                const pct = Math.round(data.percent);
                $('#uploadProgressBar').css('width', pct + '%');
                $('#uploadPercent').text(pct + '%');
                $('#uploadChunks').text(`${data.uploadedChunks} / ${data.totalChunks} chunks`);
                $('#uploadSpeed').text(data.speed.toFixed(2) + ' MB/s');
            },
            processing: () => {
                $('#uploadPhase').text('Encodage AVC...');
                $('#processingStatus').removeClass('d-none');
                $('#uploadProgressBar').removeClass('progress-bar-animated');
            },
            complete: () => {
                $('#uploadStep2').addClass('d-none');
                $('#uploadStep3').removeClass('d-none');
                populateDetailsForm(data);
            },
            error: () => {
                toastr.error(data.message, 'Erreur');
                resetUpload();
            },
            cancel: () => {
                resetUpload();
            },
            pause: () => {
                $('#uploadPhase').text('En pause');
                $('#uploadProgressBar').removeClass('progress-bar-animated');
            },
            resume: () => {
                $('#uploadPhase').text('Upload en cours...');
                $('#uploadProgressBar').addClass('progress-bar-animated');
            }
        };
        
        if (handlers[event]) handlers[event]();
    }
}

// UI Functions
function populateDetailsForm(data) {
    // Auto-fill form with detected metadata
    if (data.form_suggestions) {
        $('#videoTitle').val(data.form_suggestions.titre || '');
        $('#videoDescription').val(data.form_suggestions.description || '');
        $('#videoCredits').val(data.form_suggestions.credits || '');
    }
    
    // Populate video info
    let infoHtml = '';
    if (data.analysis) {
        infoHtml += `<li><i class="bx bx-time me-2"></i>Durée: ${data.analysis.duration_formatted}</li>`;
        infoHtml += `<li><i class="bx bx-tv me-2"></i>Résolution: ${data.analysis.resolution}</li>`;
        infoHtml += `<li><i class="bx bx-film me-2"></i>FPS: ${data.analysis.fps}</li>`;
        infoHtml += `<li><i class="bx bx-chip me-2"></i>Codec: ${data.analysis.codec_original}</li>`;
    }
    if (data.file_size) {
        infoHtml += `<li><i class="bx bx-hdd me-2"></i>Taille: ${data.file_size}</li>`;
    }
    $('#videoInfoList').html(infoHtml);
    
    // Show encoding ladder
    if (data.encoding_jobs) {
        let ladderHtml = '';
        Object.entries(data.encoding_jobs).forEach(([quality, job]) => {
            ladderHtml += `
                <div class="col-6">
                    <div class="d-flex align-items-center p-2 bg-white rounded border">
                        <span class="badge bg-youtube me-2">${quality}</span>
                        <small class="text-muted">${job.config.video_bitrate}</small>
                    </div>
                </div>
            `;
        });
        $('#encodingLadder').html(ladderHtml);
    }
    
    // Set hidden fields
    $('#uploadedFilePath').val(data.original_file || '');
    $('#autoDetectedData').val(JSON.stringify(data));
    
    // Thumbnails
    if (data.thumbnails) {
        let thumbHtml = '';
        Object.entries(data.thumbnails).forEach(([type, url]) => {
            if (url) {
                thumbHtml += `
                    <div class="position-relative cursor-pointer thumbnail-option" onclick="selectThumbnail('${url}', this)">
                        <img src="<?= base_url() ?>${url}" class="rounded" style="width: 120px; height: 68px; object-fit: cover;">
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 d-none check-overlay">
                            <i class="bx bx-check text-white fs-4"></i>
                        </div>
                    </div>
                `;
            }
        });
        $('#thumbnailSelector').html(thumbHtml);
        // Select first by default
        if ($('.thumbnail-option').length) {
            selectThumbnail(data.thumbnails.default || data.thumbnails.poster, $('.thumbnail-option')[0]);
        }
    }
}

function selectThumbnail(url, element) {
    $('#selectedThumbnail').val(url);
    $('.thumbnail-option .check-overlay').addClass('d-none');
    $(element).find('.check-overlay').removeClass('d-none');
}

function resetUpload() {
    uploadManager = null;
    $('#uploadStep1').removeClass('d-none');
    $('#uploadStep2').addClass('d-none');
    $('#uploadStep3').addClass('d-none');
    $('#processingStatus').addClass('d-none');
    $('#uploadProgressBar').css('width', '0%');
    $('#uploadPercent').text('0%');
    $('#videoDetailsForm')[0].reset();
}

// Event Listeners
$(document).ready(function() {
    // File input change
    $('#fileInput').on('change', function(e) {
        if (this.files.length > 0) {
            uploadManager = new AVCUploadManager();
            uploadManager.start(this.files[0]);
        }
    });
    
    // Drag and drop
    const dropZone = $('#uploadStep1')[0];
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-active');
    });
    
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-active');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-active');
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type.startsWith('video/')) {
            uploadManager = new AVCUploadManager();
            uploadManager.start(files[0]);
        } else {
            toastr.error('Veuillez déposer un fichier vidéo');
        }
    });
    
    // Cancel upload
    $('#cancelUploadBtn').on('click', function() {
        if (confirm('Annuler l\'upload ?')) {
            if (uploadManager) uploadManager.cancel();
        }
    });
    
    // Modal close protection
    $('#uploadModal').on('hide.bs.modal', function(e) {
        if (uploadManager && uploadManager.state.isUploading) {
            if (!confirm('Un upload est en cours. Fermer annulera l\'opération. Continuer ?')) {
                e.preventDefault();
                return false;
            }
            uploadManager.cancel();
        }
    });
    
    // Toggle switches AJAX
    $(document).on('change', '.form-check-input[data-field]', function() {
        const $cb = $(this);
        const id = $cb.data('id');
        const field = $cb.data('field');
        const value = $cb.is(':checked') ? 1 : 0;
        
        $cb.prop('disabled', true);
        
        $.post(UPLOAD_CONFIG.baseUrl + 'toggleField', {
            id: id,
            field: field,
            value: value,
            [UPLOAD_CONFIG.csrfName]: UPLOAD_CONFIG.csrfHash
        }, function(r) {
            if (r && r.success) {
                toastr.success('Paramètre mis à jour');
            } else {
                $cb.prop('checked', !value);
                toastr.error('Erreur de mise à jour');
            }
        }, 'json').fail(function() {
            $cb.prop('checked', !value);
            toastr.error('Erreur réseau');
        }).always(function() {
            $cb.prop('disabled', false);
        });
    });
    
    // DataTable
    if ($.fn.DataTable) {
        $('#videosTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [0, 6] }
            ]
        });
    }
    
    // Quality filter
    $('#filterQuality').on('change', function() {
        const quality = $(this).val();
        if (quality) {
            $('#videosTable tbody tr').each(function() {
                const rowQuality = $(this).data('quality');
                if (quality === '4K' && rowQuality >= 2160) {
                    $(this).show();
                } else if (quality === '1080' && rowQuality >= 1080 && rowQuality < 2160) {
                    $(this).show();
                } else if (quality === '720' && rowQuality >= 720 && rowQuality < 1080) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        } else {
            $('#videosTable tbody tr').show();
        }
    });
});

// Global functions
function openPlayer(id, title) {
    $('#playerTitle').text(title || 'Lecture vidéo');
    $('#playerModal').modal('show');
    
    // Load video via streaming endpoint
    const videoUrl = UPLOAD_CONFIG.baseUrl + 'stream/progressive/' + id;
    
    $('#playerContainer').html(`
        <video controls autoplay class="w-100 h-100">
            <source src="${videoUrl}" type="video/mp4">
            Votre navigateur ne supporte pas la lecture vidéo.
        </video>
    `);
}

function confirmDelete(id, title) {
    $('#deleteVideoId').val(id);
    $('#deleteVideoTitle').text(title);
    $('#deleteModal').modal('show');
}

function toggleStatus(id, status) {
    $.post(UPLOAD_CONFIG.baseUrl + 'ChangeStatus', {
        id: id,
        est_actif: status,
        [UPLOAD_CONFIG.csrfName]: UPLOAD_CONFIG.csrfHash
    }, function(r) {
        if (r) {
            location.reload();
        }
    });
}

function toggleFullscreen() {
    const elem = document.getElementById('playerContainer');
    if (!document.fullscreenElement) {
        elem.requestFullscreen().catch(err => {
            toastr.error(`Erreur fullscreen: ${err.message}`);
        });
    } else {
        document.exitFullscreen();
    }
}

// Close player on modal hide
$('#playerModal').on('hidden.bs.modal', function() {
    $('#playerContainer').html('');
});
</script>

<style>
/* YouTube-Style Colors */
.bg-youtube { background-color: #FF0000 !important; }
.text-youtube { color: #FF0000 !important; }
.btn-youtube { 
    background-color: #FF0000; 
    border-color: #FF0000; 
    color: white;
}
.btn-youtube:hover {
    background-color: #CC0000;
    border-color: #CC0000;
    color: white;
}

/* Upload Zone */
.upload-zone {
    min-height: 400px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    cursor: pointer;
}
.upload-zone.drag-active {
    background-color: #f8f9fa;
    transform: scale(1.02);
}

/* Video Thumbnail Hover Effects */
.video-thumb-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    background: #000;
}
.video-overlay:hover {
    background: rgba(0,0,0,0.3) !important;
}
.video-overlay:hover i {
    opacity: 1 !important;
    transform: scale(1.2);
}

/* Progress Bar Animation */
.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}
@keyframes progress-bar-stripes {
    0% { background-position: 1rem 0; }
    100% { background-position: 0 0; }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
}
::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Empty State */
.empty-state {
    padding: 3rem;
    text-align: center;
}

/* Cursor Pointer */
.cursor-pointer {
    cursor: pointer;
}

/* Thumbnail Selection */
.thumbnail-option {
    transition: all 0.2s;
    border: 2px solid transparent;
}
.thumbnail-option:hover {
    border-color: #FF0000;
}
.thumbnail-option.selected {
    border-color: #FF0000;
}

/* Quality Badges */
.badge.bg-youtube {
    background-color: #FF0000 !important;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .upload-zone {
        min-height: 300px;
    }
    .video-thumb-wrapper {
        width: 100% !important;
        height: auto !important;
        aspect-ratio: 16/9;
    }
}
</style>