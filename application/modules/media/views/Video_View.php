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
                                    <?= !empty($avc_capabilities['features']['hardware_encoding']) ? '<span class="text-success"><i class="bx bx-check"></i> GPU</span>' : '<span class="text-muted">CPU</span>' ?>
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
                            $thumb_url = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYwIiBoZWlnaHQ9IjkwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9IiMzMzMiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjZmZmIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Vmlkw6lvPC90ZXh0Pjwvc3ZnPg==';
                            $poster_url = null;
                            
                            if (!empty($value['miniature'])) {
                                $thumb_url = (strpos($value['miniature'], 'http') === 0) ? $value['miniature'] : base_url($value['miniature']);
                            } elseif ($is_link) {
                                $yt_id = extract_youtube_id($value['lien']);
                                if ($yt_id) {
                                    $thumb_url = "https://img.youtube.com/vi/{$yt_id}/mqdefault.jpg";
                                    $poster_url = "https://img.youtube.com/vi/{$yt_id}/maxresdefault.jpg";
                                }
                            }
                            
                            // Check for generated poster
                            if (!empty($meta['thumbnails']['poster'])) {
                                $poster_url = base_url($meta['thumbnails']['poster']);
                            }
                            
                            // Escape title for JS
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
                                             onclick="openPlayer(<?= $value['id_media'] ?>, '<?= $js_title ?>')"
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
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="openPlayer(<?= $value['id_media'] ?>, '<?= $js_title ?>')">
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
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmDelete(<?= $value['id_media'] ?>, '<?= $js_title ?>')">
                                                    <i class="bx bx-trash me-2"></i>Supprimer
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <!-- Edit Modal -->
<div class="modal fade" id="editModal<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la vidéo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('video/Update') ?>" method="POST" enctype="multipart/form-data" id="editForm<?= $value['id_media'] ?>">
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

                            <!-- SECTION MINIATURE MODIFIABLE -->
                            <div class="card border mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bx bx-image me-2"></i>Miniature</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <!-- Miniature actuelle -->
                                            <label class="form-label small text-muted">Miniature actuelle</label>
                                            <div class="position-relative">
                                                <?php 
                                                $current_thumb = $value['miniature'] ?? '';
                                                $thumb_display = $current_thumb ? base_url($current_thumb) : base_url('assets/images/video-placeholder.jpg');
                                                ?>
                                                <img src="<?= $thumb_display ?>" 
                                                     class="rounded w-100" 
                                                     style="height: 120px; object-fit: cover;"
                                                     id="currentThumb<?= $value['id_media'] ?>"
                                                     onerror="this.src='<?= base_url('assets/images/video-placeholder.jpg') ?>'">
                                                
                                                <?php if ($is_upload && !empty($meta['thumbnails'])): ?>
                                                <div class="mt-2">
                                                    <label class="form-label small text-muted">Changer pour :</label>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <?php if (!empty($meta['thumbnails']['default'])): ?>
                                                        <img src="<?= base_url($meta['thumbnails']['default']) ?>" 
                                                             class="rounded cursor-pointer edit-thumb-option" 
                                                             style="width: 80px; height: 45px; object-fit: cover; border: 2px solid transparent;"
                                                             onclick="selectEditThumbnail(<?= $value['id_media'] ?>, '<?= $meta['thumbnails']['default'] ?>', this)"
                                                             data-thumb="<?= $meta['thumbnails']['default'] ?>">
                                                        <?php endif; ?>
                                                        <?php if (!empty($meta['thumbnails']['poster'])): ?>
                                                        <img src="<?= base_url($meta['thumbnails']['poster']) ?>" 
                                                             class="rounded cursor-pointer edit-thumb-option" 
                                                             style="width: 80px; height: 45px; object-fit: cover; border: 2px solid transparent;"
                                                             onclick="selectEditThumbnail(<?= $value['id_media'] ?>, '<?= $meta['thumbnails']['poster'] ?>', this)"
                                                             data-thumb="<?= $meta['thumbnails']['poster'] ?>">
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-7">
                                            <!-- Upload nouvelle miniature -->
                                            <label class="form-label small text-muted">Ou uploader une nouvelle</label>
                                            <div class="upload-thumbnail-zone border rounded p-3 text-center mb-2" 
                                                 style="border-style: dashed !important; cursor: pointer; background: #f8f9fa;"
                                                 onclick="document.getElementById('editThumbInput<?= $value['id_media'] ?>').click()">
                                                <i class="bx bx-cloud-upload fs-3 text-muted mb-2"></i>
                                                <p class="mb-0 small text-muted">Cliquez pour uploader</p>
                                                <p class="mb-0" style="font-size: 0.7rem; color: #999;">JPG, PNG, WEBP (max 2MB)</p>
                                            </div>
                                            <input type="file" 
                                                   id="editThumbInput<?= $value['id_media'] ?>" 
                                                   class="d-none" 
                                                   accept="image/*"
                                                   onchange="uploadEditThumbnail(<?= $value['id_media'] ?>, this.files[0])">
                                            
                                            <!-- Preview nouvelle miniature -->
                                            <div id="editThumbPreview<?= $value['id_media'] ?>" class="d-none position-relative">
                                                <img src="" class="rounded w-100" style="height: 120px; object-fit: cover;" id="editThumbImg<?= $value['id_media'] ?>">
                                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                                                        onclick="removeEditThumbnail(<?= $value['id_media'] ?>)">
                                                    <i class="bx bx-x"></i>
                                                </button>
                                                <div class="position-absolute bottom-0 start-0 w-100 bg-success text-white text-center small">
                                                    <i class="bx bx-check me-1"></i>Nouvelle miniature
                                                </div>
                                            </div>
                                            
                                            <!-- Progress -->
                                            <div id="editThumbProgress<?= $value['id_media'] ?>" class="d-none mt-2">
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar bg-success" style="width: 0%"></div>
                                                </div>
                                            </div>
                                            
                                            <!-- Champ caché pour la miniature sélectionnée -->
                                            <input type="hidden" name="thumbnail" id="editThumbSelected<?= $value['id_media'] ?>" value="<?= htmlspecialchars($current_thumb) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- FIN SECTION MINIATURE -->

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
                                streaming DASH/HLS, miniatures intelligentes. Max 2GB.
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
                                    
                                    <!-- CORRECTION: Structure HTML propre pour Miniature et Catégorie -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Miniature</label>
                                            
                                            <!-- Onglets pour choisir la source -->
                                            <ul class="nav nav-tabs mb-3" id="thumbnailTab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="generated-tab" data-bs-toggle="tab" data-bs-target="#generated-thumbnails" type="button" role="tab">
                                                        <i class="bx bx-video me-1"></i>Générées
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-thumbnail" type="button" role="tab">
                                                        <i class="bx bx-upload me-1"></i>Upload
                                                    </button>
                                                </li>
                                            </ul>
                                            
                                            <div class="tab-content" id="thumbnailTabContent">
                                                <!-- Miniatures générées automatiquement -->
                                                <div class="tab-pane fade show active" id="generated-thumbnails" role="tabpanel">
                                                    <div class="d-flex gap-2 flex-wrap" id="thumbnailSelector">
                                                        <!-- Generated thumbnails injectées ici -->
                                                    </div>
                                                </div>
                                                
                                                <!-- Upload personnalisé -->
                                                <div class="tab-pane fade" id="upload-thumbnail" role="tabpanel">
                                                    <div class="upload-thumbnail-zone border rounded p-3 text-center" 
                                                         style="border-style: dashed !important; cursor: pointer; background: #f8f9fa;"
                                                         onclick="document.getElementById('customThumbnailInput').click()">
                                                        <i class="bx bx-image-add fs-2 text-muted mb-2"></i>
                                                        <p class="mb-1 text-muted small">Cliquez pour uploader une image</p>
                                                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">JPG, PNG, GIF, WEBP (max 2MB)</p>
                                                    </div>
                                                    <input type="file" id="customThumbnailInput" class="d-none" accept="image/*">
                                                    
                                                    <!-- Preview de l'upload -->
                                                    <div id="customThumbnailPreview" class="mt-2 d-none">
                                                        <div class="position-relative d-inline-block">
                                                            <img src="" class="rounded" style="width: 120px; height: 68px; object-fit: cover;" id="customThumbnailImg">
                                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                                                    style="padding: 0.1rem 0.3rem; font-size: 0.7rem;"
                                                                    onclick="removeCustomThumbnail()">
                                                                <i class="bx bx-x"></i>
                                                            </button>
                                                            <div class="position-absolute bottom-0 start-0 w-100 bg-success text-white text-center small" 
                                                                 style="font-size: 0.65rem;">
                                                                <i class="bx bx-check me-1"></i>Personnalisée
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Progress upload -->
                                                    <div id="thumbnailUploadProgress" class="mt-2 d-none">
                                                        <div class="progress" style="height: 4px;">
                                                            <div class="progress-bar bg-success" style="width: 0%"></div>
                                                        </div>
                                                        <small class="text-muted">Upload...</small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <input type="hidden" name="thumbnail" id="selectedThumbnail">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Catégorie</label>
                                            <input type="text" class="form-control" name="categorie" list="categoriesList">
                                        </div>
                                    </div>
                                    <!-- FIN CORRECTION -->
                                    
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

<!-- ========================================== -->
<!-- SCRIPTS - ORDRE CRITIQUE                   -->
<!-- ========================================== -->

<!-- 1. jQuery (déjà chargé normalement dans Header.php) -->
<!-- 2. Bootstrap JS (déjà chargé normalement) -->

<!-- 3. Toastr -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- 4. DataTables avec traduction locale (pas de CORS) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
// ==========================================
// CONFIGURATION TOASTR
// ==========================================
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

// ==========================================
// CONFIGURATION UPLOAD YOUTUBE-STYLE (SANS CSRF)
// ==========================================
const UPLOAD_CONFIG = {
    baseUrl: '<?= base_url('video/') ?>',
    chunkSize: 5 * 1024 * 1024, // 5MB chunks
    maxFileSize: 2 * 1024 * 1024 * 1024 // 2GB
};

let currentUpload = null;
let uploadManager = null;

// ==========================================
// UTILITAIRES
// ==========================================
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

// ==========================================
// GESTIONNAIRE UPLOAD AVC (SANS CSRF)
// ==========================================
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
            
            // Validation
            if (file.size > UPLOAD_CONFIG.maxFileSize) {
                throw new Error(`Fichier trop grand. Maximum: ${FileUtils.formatBytes(UPLOAD_CONFIG.maxFileSize)}`);
            }
            
            // Update UI
            this.updateUI('init', { fileName: file.name, fileSize: FileUtils.formatBytes(file.size) });
            
            // Initialize upload session - PAS DE CSRF
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
            console.error('Upload error:', error);
            this.updateUI('error', { message: error.message });
        } finally {
            this.state.isUploading = false;
        }
    }
    
    async apiCall(endpoint, data) {
        const formData = new FormData();
        for (let key in data) formData.append(key, data[key]);
        // PAS DE CSRF TOKEN
        
        try {
            const response = await fetch(UPLOAD_CONFIG.baseUrl + endpoint, {
                method: 'POST',
                body: formData,
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest'
                    // PAS DE X-CSRF-TOKEN
                }
            });
            
            // Vérifier si la réponse est OK
            if (!response.ok) {
                const text = await response.text();
                throw new Error(`Erreur HTTP ${response.status}: ${text.substring(0, 100)}`);
            }
            
            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', text);
                throw new Error('Réponse serveur invalide (JSON attendu)');
            }
            
            if (!result.success) throw new Error(result.message || 'Erreur serveur');
            return result;
            
        } catch (error) {
            console.error('API Call error:', error);
            throw error;
        }
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
        
        // Parallel upload with 3 workers
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
            // PAS DE CSRF TOKEN
            
            const response = await fetch(UPLOAD_CONFIG.baseUrl + 'uploadChunk', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                const text = await response.text();
                throw new Error(`HTTP ${response.status}: ${text.substring(0, 100)}`);
            }
            
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Réponse chunk invalide');
            }
            
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
            } catch(e) {
                // Ignorer erreur cancel
            }
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

// ==========================================
// FONCTIONS UI - CORRIGÉES ET UNIQUES
// ==========================================

/**
 * Remplit le formulaire de détails après upload vidéo
 * VERSION CORRIGÉE - Gère correctement les miniatures
 */
function populateDetailsForm(data) {
    console.log('populateDetailsForm appelé avec:', data);
    
    // 1. Auto-fill des métadonnées basiques
    if (data.form_suggestions) {
        $('#videoTitle').val(data.form_suggestions.titre || '');
        $('#videoDescription').val(data.form_suggestions.description || '');
        $('#videoCredits').val(data.form_suggestions.credits || '');
    }
    
    // 2. Informations vidéo (sidebar droite)
    let infoHtml = '';
    if (data.analysis) {
        infoHtml += `<li><i class="bx bx-time me-2"></i>Durée: ${data.analysis.duration_formatted || 'N/A'}</li>`;
        infoHtml += `<li><i class="bx bx-tv me-2"></i>Résolution: ${data.analysis.resolution || 'N/A'}</li>`;
        infoHtml += `<li><i class="bx bx-film me-2"></i>FPS: ${data.analysis.fps || 'N/A'}</li>`;
        infoHtml += `<li><i class="bx bx-chip me-2"></i>Codec: ${data.analysis.codec_original || 'N/A'}</li>`;
    }
    if (data.file_size) {
        infoHtml += `<li><i class="bx bx-hdd me-2"></i>Taille: ${data.file_size}</li>`;
    }
    $('#videoInfoList').html(infoHtml || '<li>Aucune information disponible</li>');
    
    // 3. Encoding ladder
    if (data.encoding_jobs) {
        let ladderHtml = '';
        Object.entries(data.encoding_jobs).forEach(([quality, job]) => {
            ladderHtml += `
                <div class="col-6">
                    <div class="d-flex align-items-center p-2 bg-white rounded border">
                        <span class="badge bg-youtube me-2">${quality}</span>
                        <small class="text-muted">${job.config?.video_bitrate || ''}</small>
                    </div>
                </div>
            `;
        });
        $('#encodingLadder').html(ladderHtml);
    }
    
    // 4. Champs cachés du formulaire
    $('#uploadedFilePath').val(data.original_file || '');
    $('#autoDetectedData').val(JSON.stringify(data));
    
    // 5. === GESTION DES MINIATURES - CORRECTION CRITIQUE ===
    
    // Reset complet
    $('#selectedThumbnail').val('');
    $('#customThumbnailPreview').addClass('d-none');
    $('#customThumbnailInput').val('');
    $('#thumbnailUploadProgress').addClass('d-none');
    
    // Reset des onglets vers "Générées"
    $('#upload-tab').removeClass('active');
    $('#generated-tab').addClass('active');
    $('#generated-thumbnails').addClass('show active');
    $('#upload-thumbnail').removeClass('show active');
    
    // CORRECTION: Normaliser thumbnails (objet ou tableau vide)
    let thumbnails = data.thumbnails || {};
    
    // Si c'est un tableau vide ou null, convertir en objet vide
    if (Array.isArray(thumbnails) && thumbnails.length === 0) {
        thumbnails = {};
    }
    
    // Convertir tableau associatif en objet si nécessaire
    if (Array.isArray(thumbnails)) {
        let tempObj = {};
        thumbnails.forEach((item, index) => {
            if (item) tempObj['thumb_' + index] = item;
        });
        thumbnails = tempObj;
    }
    
    console.log('Thumbnails normalisés:', thumbnails);
    
    // Générer les miniatures automatiques
    let thumbHtml = '';
    let firstThumbUrl = null;
    let thumbCount = 0;
    
    Object.entries(thumbnails).forEach(([type, url], index) => {
        if (url && typeof url === 'string') {
            const fullUrl = url.startsWith('http') ? url : '<?= base_url() ?>' + url;
            const isFirst = thumbCount === 0;
            if (isFirst) firstThumbUrl = url;
            
            thumbHtml += `
                <div class="position-relative cursor-pointer thumbnail-option ${isFirst ? 'selected' : ''}" 
                     onclick="selectThumbnail('${url}', this)"
                     data-url="${url}">
                    <img src="${fullUrl}" class="rounded" style="width: 120px; height: 68px; object-fit: cover;" 
                         onerror="this.src='<?= base_url('assets/images/video-placeholder.jpg') ?>'">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 ${isFirst ? '' : 'd-none'} check-overlay">
                        <i class="bx bx-check text-white fs-4"></i>
                    </div>
                    <div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-white text-center" style="font-size: 0.6rem;">
                        ${type === 'default' ? 'Auto' : type}
                    </div>
                </div>
            `;
            thumbCount++;
        }
    });
    
    $('#thumbnailSelector').html(thumbHtml);
    
    // Si aucune miniature générée, afficher un message
    if (thumbCount === 0) {
        $('#thumbnailSelector').html(`
            <div class="alert alert-warning w-100 mb-0">
                <i class="bx bx-info-circle me-1"></i>
                Aucune miniature générée. Vous pouvez en uploader une personnalisée.
            </div>
        `);
        console.warn('Aucune miniature générée disponible');
        
        // Basculer directement sur l'onglet upload
        $('#upload-tab').tab('show');
    } else if (firstThumbUrl) {
        $('#selectedThumbnail').val(firstThumbUrl);
        console.log('Miniature par défaut sélectionnée:', firstThumbUrl);
    }
}

/**
 * Sélectionne une miniature générée automatiquement
 */
function selectThumbnail(url, element) {
    console.log('Sélection thumbnail:', url);
    
    if (!url || typeof url !== 'string') {
        console.error('URL invalide:', url);
        return;
    }
    
    $('#selectedThumbnail').val(url);
    
    $('.thumbnail-option .check-overlay').addClass('d-none');
    $('.thumbnail-option').removeClass('selected');
    
    $(element).find('.check-overlay').removeClass('d-none');
    $(element).addClass('selected');
    
    $('#customThumbnailPreview').addClass('d-none');
    $('#customThumbnailInput').val('');
    
    $('#generated-tab').tab('show');
}

/**
 * Upload d'une miniature personnalisée - VERSION CORRIGÉE
 */
function uploadCustomThumbnail(file) {
    console.log('Upload thumbnail personnalisée:', file.name);
    
    // Validation du fichier
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    if (!validTypes.includes(file.type)) {
        toastr.error('Format non supporté. Utilisez: JPG, PNG, GIF, WEBP, SVG');
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        toastr.error('Fichier trop grand (max 2MB)');
        return;
    }

    // Afficher la progress bar
    $('#thumbnailUploadProgress').removeClass('d-none');
    $('#thumbnailUploadProgress .progress-bar').css('width', '0%');
    $('#customThumbnailPreview').addClass('d-none');
    
    // Préparer FormData
    const formData = new FormData();
    formData.append('thumbnail_file', file);
    
    console.log('Envoi AJAX vers:', UPLOAD_CONFIG.baseUrl + 'uploadThumbnail');
    
    // Upload AJAX - VERSION CORRIGÉE
    $.ajax({
        url: UPLOAD_CONFIG.baseUrl + 'uploadThumbnail',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json', // FORCER le type JSON
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    $('#thumbnailUploadProgress .progress-bar').css('width', percent + '%');
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            console.log('Réponse serveur (success):', response);
            $('#thumbnailUploadProgress').addClass('d-none');
            
            if (response.success && response.file_path) {
                $('#customThumbnailImg').attr('src', response.preview_url);
                $('#customThumbnailPreview').removeClass('d-none');
                
                $('#selectedThumbnail').val(response.file_path);
                console.log('Thumbnail personnalisée sélectionnée:', response.file_path);
                
                $('.thumbnail-option').removeClass('selected');
                $('.thumbnail-option .check-overlay').addClass('d-none');
                
                $('#upload-tab').tab('show');
                
                toastr.success(response.message || 'Miniature uploadée avec succès');
            } else {
                toastr.error(response.message || 'Erreur upload miniature');
            }
        },
        error: function(xhr, status, error) {
            console.error('=== ERREUR AJAX ===');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Status HTTP:', xhr.status);
            console.error('Response Text:', xhr.responseText);
            
            $('#thumbnailUploadProgress').addClass('d-none');
            
            // Essayer d'afficher l'erreur du serveur
            let errorMsg = 'Erreur réseau lors de l\'upload';
            if (xhr.responseText) {
                // Si c'est du HTML, extraire le message
                if (xhr.responseText.indexOf('<') === 0) {
                    // C'est du HTML, chercher un message d'erreur
                    const match = xhr.responseText.match(/<p>(.*?)<\/p>/);
                    if (match) {
                        errorMsg += ': ' + match[1];
                    } else {
                        errorMsg += ' (voir console)';
                    }
                } else {
                    // Essayer de parser comme JSON
                    try {
                        const json = JSON.parse(xhr.responseText);
                        errorMsg = json.message || errorMsg;
                    } catch(e) {
                        errorMsg += ': ' + xhr.responseText.substring(0, 100);
                    }
                }
            }
            
            toastr.error(errorMsg);
        }
    });
}

/**
 * Supprime la miniature personnalisée et retourne aux générées
 */
function removeCustomThumbnail() {
    $('#customThumbnailPreview').addClass('d-none');
    $('#customThumbnailInput').val('');
    
    const firstThumb = $('.thumbnail-option').first();
    if (firstThumb.length) {
        const url = firstThumb.data('url');
        if (url) {
            selectThumbnail(url, firstThumb[0]);
        } else {
            $('#selectedThumbnail').val('');
        }
    } else {
        $('#selectedThumbnail').val('');
    }
    
    $('#generated-tab').tab('show');
}



// ==========================================
// FONCTIONS POUR MODIFICATION MINIATURE (MODE ÉDITION)
// ==========================================

/**
 * Sélectionne une miniature existante dans le mode édition
 */
function selectEditThumbnail(videoId, thumbUrl, element) {
    console.log('Sélection miniature édition:', videoId, thumbUrl);
    
    // Mettre à jour le champ caché
    $('#editThumbSelected' + videoId).val(thumbUrl);
    
    // Mettre à jour l'image principale
    const fullUrl = thumbUrl.startsWith('http') ? thumbUrl : '<?= base_url() ?>' + thumbUrl;
    $('#currentThumb' + videoId).attr('src', fullUrl);
    
    // UI: désélectionner toutes les options
    $('#editModal' + videoId + ' .edit-thumb-option').css('border-color', 'transparent');
    
    // UI: sélectionner l'option cliquée
    $(element).css('border-color', '#FF0000');
    
    // Cacher la preview d'upload si visible
    $('#editThumbPreview' + videoId).addClass('d-none');
    
    toastr.info('Miniature sélectionnée. Enregistrez pour appliquer.');
}

/**
 * Upload une nouvelle miniature dans le mode édition
 */
function uploadEditThumbnail(videoId, file) {
    console.log('Upload miniature édition:', videoId, file.name);
    
    if (!file) return;
    
    // Validation
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        toastr.error('Format non supporté. Utilisez: JPG, PNG, GIF, WEBP');
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        toastr.error('Fichier trop grand (max 2MB)');
        return;
    }

    // Afficher progress
    $('#editThumbProgress' + videoId).removeClass('d-none');
    $('#editThumbProgress' + videoId + ' .progress-bar').css('width', '0%');
    
    // Préparer FormData
    const formData = new FormData();
    formData.append('thumbnail_file', file);
    
    // Upload AJAX
    $.ajax({
        url: UPLOAD_CONFIG.baseUrl + 'uploadThumbnail',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    $('#editThumbProgress' + videoId + ' .progress-bar').css('width', percent + '%');
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            console.log('Réponse upload édition:', response);
            $('#editThumbProgress' + videoId).addClass('d-none');
            
            if (response.success && response.file_path) {
                // Afficher la preview
                $('#editThumbImg' + videoId).attr('src', response.preview_url);
                $('#editThumbPreview' + videoId).removeClass('d-none');
                
                // Mettre à jour le champ caché
                $('#editThumbSelected' + videoId).val(response.file_path);
                
                // Mettre à jour l'image principale aussi
                $('#currentThumb' + videoId).attr('src', response.preview_url);
                
                // Désélectionner les miniatures générées
                $('#editModal' + videoId + ' .edit-thumb-option').css('border-color', 'transparent');
                
                toastr.success('Nouvelle miniature prête. Enregistrez pour appliquer.');
            } else {
                toastr.error(response.message || 'Erreur upload');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur upload édition:', error);
            $('#editThumbProgress' + videoId).addClass('d-none');
            
            let errorMsg = 'Erreur upload';
            if (xhr.responseText) {
                try {
                    const json = JSON.parse(xhr.responseText);
                    errorMsg = json.message || errorMsg;
                } catch(e) {
                    errorMsg += ': ' + xhr.responseText.substring(0, 100);
                }
            }
            toastr.error(errorMsg);
        }
    });
}

/**
 * Supprime la nouvelle miniature uploadée en mode édition
 */
function removeEditThumbnail(videoId) {
    $('#editThumbPreview' + videoId).addClass('d-none');
    
    // Restaurer la valeur originale
    const originalThumb = $('#currentThumb' + videoId).attr('src');
    // Extraire le chemin relatif de l'URL complète si nécessaire
    $('#editThumbSelected' + videoId).val(originalThumb.replace('<?= base_url() ?>', ''));
    
    // Réinitialiser l'input file
    $('#editThumbInput' + videoId).val('');
    
    toastr.info('Miniature restaurée. Enregistrez pour appliquer.');
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
    $('#thumbnailSelector').empty();
    $('#customThumbnailPreview').addClass('d-none');
    $('#customThumbnailInput').val('');
    $('#selectedThumbnail').val('');
}











// ==========================================
// FONCTIONS GLOBALES - CORRIGÉES POUR BOOTSTRAP 5
// ==========================================

function openPlayer(id, title) {
    $('#playerTitle').text(title || 'Lecture vidéo');
    
    // Utiliser l'API Bootstrap 5 native (pas jQuery)
    const modalElement = document.getElementById('playerModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
    
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
    
    // Utiliser l'API Bootstrap 5 native
    const modalElement = document.getElementById('deleteModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

function toggleStatus(id, status) {
    $.ajax({
        url: UPLOAD_CONFIG.baseUrl + 'ChangeStatus',
        type: 'POST',
        data: {
            id: id,
            est_actif: status
        },
        dataType: 'json',
        success: function(r) {
            if (r && r.success) {
                location.reload();
            } else {
                toastr.error('Erreur lors du changement de statut');
            }
        },
        error: function() {
            toastr.error('Erreur réseau');
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

// ==========================================
// EVENT LISTENERS - TOUT DANS $(document).ready()
// ==========================================
$(document).ready(function() {
    console.log('✅ Document ready - Initialisation...');
    
    // File input change pour vidéo
    $('#fileInput').on('change', function(e) {
        if (this.files.length > 0) {
            uploadManager = new AVCUploadManager();
            uploadManager.start(this.files[0]);
        }
    });
    
    // Gestion du file input pour thumbnail personnalisé
    $(document).on('change', '#customThumbnailInput', function(e) {
        if (this.files && this.files[0]) {
            uploadCustomThumbnail(this.files[0]);
        }
    });
    
    // Drag and drop
    const dropZone = $('#uploadStep1')[0];
    if (dropZone) {
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
    }
    
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
    
    // Toggle switches AJAX - SANS CSRF
    $(document).on('change', '.form-check-input[data-field]', function() {
        const $cb = $(this);
        const id = $cb.data('id');
        const field = $cb.data('field');
        const value = $cb.is(':checked') ? 1 : 0;
        
        $cb.prop('disabled', true);
        
        $.ajax({
            url: UPLOAD_CONFIG.baseUrl + 'toggleField',
            type: 'POST',
            data: {
                id: id,
                field: field,
                value: value
            },
            dataType: 'json',
            success: function(r) {
                if (r && r.success) {
                    toastr.success('Paramètre mis à jour');
                } else {
                    $cb.prop('checked', !value);
                    toastr.error('Erreur de mise à jour');
                }
            },
            error: function(xhr, status, error) {
                $cb.prop('checked', !value);
                console.error('AJAX Error:', error);
                toastr.error('Erreur réseau');
            },
            complete: function() {
                $cb.prop('disabled', false);
            }
        });
    });
    
    // DataTables avec traduction locale (pas de CORS)
    if ($.fn.DataTable) {
        $('#videosTable').DataTable({
            language: {
                "sEmptyTable": "Aucune donnée disponible",
                "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                "sInfoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                "sInfoFiltered": "(filtré de _MAX_ entrées totales)",
                "sInfoPostFix": "",
                "sInfoThousands": " ",
                "sLengthMenu": "Afficher _MENU_ entrées",
                "sLoadingRecords": "Chargement...",
                "sProcessing": "Traitement...",
                "sSearch": "Rechercher :",
                "sZeroRecords": "Aucun résultat trouvé",
                "oPaginate": {
                    "sFirst": "Premier",
                    "sLast": "Dernier",
                    "sNext": "Suivant",
                    "sPrevious": "Précédent"
                },
                "oAria": {
                    "sSortAscending": ": activer pour trier la colonne par ordre croissant",
                    "sSortDescending": ": activer pour trier la colonne par ordre décroissant"
                }
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
    
    console.log('✅ Scripts chargés avec succès - Mode SANS CSRF');
});

// Fermer le player quand le modal se ferme
document.addEventListener('DOMContentLoaded', function() {
    const playerModal = document.getElementById('playerModal');
    if (playerModal) {
        playerModal.addEventListener('hidden.bs.modal', function() {
            $('#playerContainer').html('');
        });
    }
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
    border-radius: 4px;
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

/* Toastr Custom Position */
#toast-container > div {
    opacity: 0.95;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Fix CORS warning suppression */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.3em 0.8em;
}
/* Upload Thumbnail Zone */
.upload-thumbnail-zone {
    transition: all 0.3s ease;
}
.upload-thumbnail-zone:hover {
    background: #e9ecef !important;
    border-color: #FF0000 !important;
}

/* Thumbnail Option Selected State */
.thumbnail-option.selected {
    border-color: #FF0000;
    box-shadow: 0 0 0 2px rgba(255, 0, 0, 0.3);
}

/* Custom Thumbnail Badge */
.custom-thumbnail-badge {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(40, 167, 69, 0.9);
    color: white;
    font-size: 0.65rem;
    text-align: center;
    padding: 2px;
    border-bottom-left-radius: 4px;
    border-bottom-right-radius: 4px;
}

/* Tab styling */
.nav-tabs .nav-link {
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
}
.nav-tabs .nav-link.active {
    font-weight: bold;
    color: #FF0000;
    border-bottom-color: #FF0000;
}
</style>