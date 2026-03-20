<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// ============================================================================
// HELPERS PHP - Audio Processing
// ============================================================================

if (!function_exists('format_duration_audio')) {
    function format_duration_audio($seconds) {
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

if (!function_exists('format_bytes_audio')) {
    function format_bytes_audio($bytes, $decimals = 2) {
        if (empty($bytes) || $bytes === 0) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), $decimals) . ' ' . $sizes[$i];
    }
}

if (!function_exists('get_quality_badge_audio')) {
    function get_quality_badge_audio($bitrate) {
        if (empty($bitrate)) return ['label' => 'Unknown', 'class' => 'secondary'];
        $kbps = round($bitrate / 1000);
        if ($kbps >= 320) return ['label' => '320kbps', 'class' => 'danger'];
        if ($kbps >= 256) return ['label' => '256kbps', 'class' => 'warning'];
        if ($kbps >= 192) return ['label' => '192kbps', 'class' => 'primary'];
        if ($kbps >= 128) return ['label' => '128kbps', 'class' => 'success'];
        return ['label' => $kbps . 'kbps', 'class' => 'info'];
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
                        <li class="breadcrumb-item active" aria-current="page">Studio Audio v5.0</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a class="btn btn-spotify btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#uploadModal">
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
                            <div class="rounded-circle bg-spotify bg-opacity-10 p-3 me-3">
                                <i class="bx bx-music text-spotify fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Audios</h6>
                                <h3 class="mb-0 fw-bold"><?= count($audios ?? []) ?></h3>
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
                                <h3 class="mb-0 fw-bold"><?= format_duration_audio($total_duration ?? 0) ?></h3>
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
                                <h6 class="text-muted mb-1">Conversion</h6>
                                <h3 class="mb-0 fw-bold">
                                    <?= !empty($audio_capabilities['features']['multi_bitrate']) ? '<span class="text-success"><i class="bx bx-check"></i> Multi</span>' : '<span class="text-muted">Simple</span>' ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audios Grid -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0"><i class="bx bx-collection me-2 text-spotify"></i>Bibliothèque Audio</h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="filterQuality" style="width: auto;">
                            <option value="">Toutes qualités</option>
                            <option value="320">320kbps</option>
                            <option value="256">256kbps</option>
                            <option value="192">192kbps</option>
                            <option value="128">128kbps</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="audiosTable" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">Audio</th>
                                <th width="30%">Détails</th>
                                <th width="15%">Qualités</th>
                                <th width="10%">Durée</th>
                                <th width="10%">Statut</th>
                                <th width="8%">Visibilité</th>
                                <th width="7%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($audios)): foreach ($audios as $value): 
                            $meta = !empty($value['metadata_id3']) ? json_decode($value['metadata_id3'], true) : [];
                            $quality_info = get_quality_badge_audio($value['bitrate'] ?? ($meta['analysis']['bitrate'] ?? 0));
                            
                            $thumb_url = base_url('assets/images/audio-placeholder.jpg');
                            if (!empty($value['miniature'])) {
                                $thumb_url = (strpos($value['miniature'], 'http') === 0) ? $value['miniature'] : base_url($value['miniature']);
                            }
                            
                            $js_title = htmlspecialchars($value['titre'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr data-id="<?= $value['id_media'] ?>" data-bitrate="<?= $value['bitrate'] ?? 0 ?>">
                                <td>
                                    <div class="audio-thumb-wrapper position-relative" style="width: 80px; height: 80px;">
                                        <img src="<?= $thumb_url ?>" 
                                             class="rounded w-100 h-100" 
                                             style="object-fit: cover; background: #000;"
                                             loading="lazy"
                                             onerror="this.src='<?= base_url('assets/images/audio-placeholder.jpg') ?>'">
                                        <div class="audio-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                                             onclick="openPlayer(<?= $value['id_media'] ?>, '<?= $js_title ?>')"
                                             style="background: rgba(0,0,0,0); transition: all 0.3s; cursor: pointer;">
                                            <i class="bx bx-play-circle text-white fs-2 opacity-0" style="transition: all 0.3s;"></i>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <h6 class="mb-1 fw-bold text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($value['titre'] ?? '') ?>">
                                            <?= htmlspecialchars($value['titre'] ?? 'Sans titre') ?>
                                        </h6>
                                        <?php if (!empty($value['credits'])): ?>
                                            <small class="text-muted mb-1"><i class="bx bx-user me-1"></i><?= htmlspecialchars($value['credits']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($value['categorie'])): ?>
                                            <span class="badge bg-light text-dark border w-fit-content"><?= htmlspecialchars($value['categorie']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($meta['analysis']['album'])): ?>
                                            <small class="text-muted mt-1"><i class="bx bx-album me-1"></i><?= htmlspecialchars($meta['analysis']['album']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($meta['conversions'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($meta['conversions'] as $quality => $conv): ?>
                                                <span class="badge bg-<?= $quality === 'max' ? 'danger' : ($quality === 'high' ? 'primary' : 'secondary') ?>" style="font-size: 0.7rem;">
                                                    <?= $conv['bitrate'] ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-<?= $quality_info['class'] ?>"><?= $quality_info['label'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-dark">
                                        <i class="bx bx-time me-1"></i><?= format_duration_audio($value['duree'] ?? 0) ?>
                                    </span>
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
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleStatus(<?= $value['id_media'] ?>, 1)"><i class="bx bx-globe me-2 text-success"></i>Public</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleStatus(<?= $value['id_media'] ?>, 0)"><i class="bx bx-lock me-2 text-secondary"></i>Privé</a></li>
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
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button class="btn btn-sm btn-outline-success rounded-circle" 
                                                onclick="openPlayer(<?= $value['id_media'] ?>, '<?= $js_title ?>')" 
                                                title="Lire l'audio"
                                                style="width: 32px; height: 32px; padding: 0;">
                                            <i class="bx bx-play fs-5"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary rounded-circle" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?= $value['id_media'] ?>" 
                                                title="Modifier l'audio"
                                                style="width: 32px; height: 32px; padding: 0;">
                                            <i class="bx bx-edit fs-5"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle" 
                                                onclick="confirmDelete(<?= $value['id_media'] ?>, '<?= $js_title ?>')" 
                                                title="Supprimer l'audio"
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
                                        <div class="modal-header bg-spotify text-white">
                                            <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'audio</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('audio/Update') ?>" method="POST" id="editForm<?= $value['id_media'] ?>">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control form-control-lg" name="titre" value="<?= htmlspecialchars($value['titre'] ?? '') ?>" required maxlength="255">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Description</label>
                                                            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                        </div>
                                                        
                                                        <!-- SECTION MINIATURE CORRIGÉE -->
                                                        <div class="card border mb-3">
                                                            <div class="card-header bg-light">
                                                                <h6 class="mb-0"><i class="bx bx-image me-2"></i>Miniature</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-5">
                                                                        <label class="form-label small text-muted">Miniature actuelle</label>
                                                                        <div class="position-relative">
                                                                            <?php 
                                                                            $current_thumb = $value['miniature'] ?? '';
                                                                            $thumb_display = $current_thumb ? base_url($current_thumb) : base_url('assets/images/audio-placeholder.jpg');
                                                                            ?>
                                                                            <img src="<?= $thumb_display ?>" 
                                                                                 class="rounded w-100" 
                                                                                 style="height: 120px; object-fit: cover;"
                                                                                 id="currentThumb<?= $value['id_media'] ?>"
                                                                                 data-original="<?= $thumb_display ?>"
                                                                                 onerror="this.src='<?= base_url('assets/images/audio-placeholder.jpg') ?>'">
                                                                            
                                                                            <?php if (!empty($meta['thumbnails'])): ?>
                                                                            <div class="mt-2">
                                                                                <label class="form-label small text-muted">Changer pour :</label>
                                                                                <div class="d-flex gap-2 flex-wrap">
                                                                                    <?php foreach ($meta['thumbnails'] as $thumb_key => $thumb_url): 
                                                                                        if (empty($thumb_url)) continue; 
                                                                                    ?>
                                                                                        <img src="<?= base_url($thumb_url) ?>" 
                                                                                             class="rounded cursor-pointer edit-thumb-option" 
                                                                                             style="width: 60px; height: 60px; object-fit: cover; border: 2px solid transparent;" 
                                                                                             onclick="selectEditThumbnail(<?= $value['id_media'] ?>, '<?= $thumb_url ?>', this)" 
                                                                                             data-thumb="<?= $thumb_url ?>" 
                                                                                             title="<?= htmlspecialchars($thumb_key) ?>">
                                                                                    <?php endforeach; ?>
                                                                                </div>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="col-md-7">
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
                                                                        
                                                                        <div id="editThumbProgress<?= $value['id_media'] ?>" class="d-none mt-2">
                                                                            <div class="progress" style="height: 4px;">
                                                                                <div class="progress-bar bg-success" style="width: 0%"></div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <input type="hidden" name="thumbnail" id="editThumbSelected<?= $value['id_media'] ?>" value="<?= htmlspecialchars($current_thumb) ?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- FIN SECTION MINIATURE -->
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Catégorie</label>
                                                                <input type="text" class="form-control" name="categorie" value="<?= htmlspecialchars($value['categorie'] ?? '') ?>" list="categoriesList">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Date</label>
                                                                <input type="date" class="form-control" name="date_media" value="<?= $value['date_media'] ?? '' ?>">
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Crédits / Artiste</label>
                                                            <input type="text" class="form-control" name="credits" value="<?= htmlspecialchars($value['credits'] ?? '') ?>">
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
                                                                        <label class="form-check-label fw-bold">Audio public</label>
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
                                                                <h6 class="mb-2"><i class="bx bx-info-circle me-1"></i>Métadonnées</h6>
                                                                <ul class="list-unstyled small text-muted mb-0">
                                                                    <li><strong>Durée:</strong> <?= $meta['analysis']['duration_formatted'] ?? 'N/A' ?></li>
                                                                    <li><strong>Bitrate:</strong> <?= round(($meta['analysis']['bitrate'] ?? 0) / 1000) ?> kbps</li>
                                                                    <li><strong>Sample:</strong> <?= $meta['analysis']['sample_rate'] ?? 'N/A' ?> Hz</li>
                                                                    <li><strong>Canaux:</strong> <?= $meta['analysis']['channels'] ?? 'N/A' ?></li>
                                                                    <li><strong>Codec:</strong> <?= strtoupper($meta['analysis']['codec'] ?? 'N/A') ?></li>
                                                                </ul>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-spotify"><i class="bx bx-save me-1"></i>Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bx bx-music fs-1 text-muted mb-3"></i>
                                        <h5>Aucun audio</h5>
                                        <p class="text-muted">Commencez par uploader votre premier audio</p>
                                        <a href="javascript:;" class="btn btn-spotify" data-bs-toggle="modal" data-bs-target="#uploadModal">
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

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-spotify text-white border-0">
                    <h5 class="modal-title"><i class="bx bx-upload me-2"></i>Uploader des audios</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeUploadModal"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Step 1: Select File -->
                    <div id="uploadStep1" class="upload-zone p-5 text-center">
                        <div class="upload-illustration mb-4">
                            <div class="position-relative d-inline-block">
                                <i class="bx bx-cloud-upload text-spotify" style="font-size: 5rem;"></i>
                                <div class="position-absolute bottom-0 end-0 bg-success rounded-circle p-2">
                                    <i class="bx bx-plus text-white"></i>
                                </div>
                            </div>
                        </div>
                        <h4 class="mb-3">Glissez-déposez des fichiers audio</h4>
                        <p class="text-muted mb-4">ou <span class="text-spotify fw-bold cursor-pointer" onclick="document.getElementById('fileInput').click()">parcourir</span> pour sélectionner</p>
                        <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
                            <span class="badge bg-light text-dark border">MP3</span>
                            <span class="badge bg-light text-dark border">WAV</span>
                            <span class="badge bg-light text-dark border">FLAC</span>
                            <span class="badge bg-light text-dark border">AAC</span>
                            <span class="badge bg-light text-dark border">OGG</span>
                        </div>
                        <div class="alert alert-light border mx-auto" style="max-width: 500px;">
                            <small class="text-muted">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Technologie Audio:</strong> Upload chunked 1.5MB, conversion multi-bitrate auto (64k-320k), waveform visuel. Max 500MB.
                            </small>
                        </div>
                        <input type="file" id="fileInput" class="d-none" accept="audio/*" multiple>
                    </div>

                    <!-- Step 2: Upload Progress -->
                    <div id="uploadStep2" class="d-none p-4">
                        <div class="upload-item mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-spotify bg-opacity-10 rounded p-2">
                                        <i class="bx bx-music text-spotify fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fw-bold" id="uploadFileName">audio.mp3</h6>
                                    <small class="text-muted" id="uploadFileSize">0 MB</small>
                                </div>
                                <div class="flex-shrink-0">
                                    <button class="btn btn-sm btn-outline-danger" id="cancelUploadBtn">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-spotify" id="uploadPhase">Préparation...</span>
                                    <span class="fw-bold" id="uploadPercent">0%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-spotify progress-bar-striped progress-bar-animated" id="uploadProgressBar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1 small text-muted">
                                    <span id="uploadSpeed">0 MB/s</span>
                                    <span id="uploadChunks">0 / 0 chunks</span>
                                </div>
                            </div>
                            <div id="processingStatus" class="d-none">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-3">
                                        <h6 class="mb-3"><i class="bx bx-cog bx-spin me-2 text-spotify"></i>Traitement Audio...</h6>
                                        <div class="row g-2" id="processingSteps">
                                            <div class="col-12"><div class="d-flex align-items-center p-2 bg-white rounded border"><i class="bx bx-check-circle text-success me-2" id="step-upload-icon"></i><span class="flex-grow-1">Upload</span><span class="badge bg-success" id="step-upload-status">OK</span></div></div>
                                            <div class="col-12"><div class="d-flex align-items-center p-2 bg-white rounded border"><i class="bx bx-loader-alt bx-spin text-spotify me-2" id="step-analysis-icon"></i><span class="flex-grow-1">Analyse ID3</span><span class="badge bg-secondary" id="step-analysis-status">...</span></div></div>
                                            <div class="col-12"><div class="d-flex align-items-center p-2 bg-white rounded border"><i class="bx bx-circle text-muted me-2" id="step-thumbnail-icon"></i><span class="flex-grow-1">Extraction miniature</span><span class="badge bg-secondary" id="step-thumbnail-status">En attente</span></div></div>
                                            <div class="col-12"><div class="d-flex align-items-center p-2 bg-white rounded border"><i class="bx bx-circle text-muted me-2" id="step-waveform-icon"></i><span class="flex-grow-1">Génération waveform</span><span class="badge bg-secondary" id="step-waveform-status">En attente</span></div></div>
                                            <div class="col-12"><div class="d-flex align-items-center p-2 bg-white rounded border"><i class="bx bx-circle text-muted me-2" id="step-conversion-icon"></i><span class="flex-grow-1">Conversion multi-bitrate</span><span class="badge bg-secondary" id="step-conversion-status">En attente</span></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Details Form -->
                    <div id="uploadStep3" class="d-none">
                        <form id="audioDetailsForm" action="<?= base_url('audio/Create') ?>" method="POST">
                            <div class="row g-0">
                                <div class="col-md-8 p-4 border-end">
                                    <div class="mb-3"><label class="form-label fw-bold">Titre <span class="text-danger">*</span></label><input type="text" class="form-control form-control-lg" name="titre" id="audioTitle" required maxlength="255"></div>
                                    <div class="mb-3"><label class="form-label fw-bold">Description</label><textarea class="form-control" name="description" rows="5" id="audioDescription"></textarea></div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Miniature</label>
                                            <ul class="nav nav-tabs mb-3" id="thumbnailTab" role="tablist">
                                                <li class="nav-item"><button class="nav-link active" id="generated-tab" data-bs-toggle="tab" data-bs-target="#generated-thumbnails" type="button" role="tab"><i class="bx bx-music me-1"></i>Générées</button></li>
                                                <li class="nav-item"><button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-thumbnail" type="button" role="tab"><i class="bx bx-upload me-1"></i>Upload</button></li>
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane fade show active" id="generated-thumbnails"><div class="d-flex gap-2 flex-wrap" id="thumbnailSelector"></div></div>
                                                <div class="tab-pane fade" id="upload-thumbnail">
                                                    <div class="upload-thumbnail-zone border rounded p-3 text-center" style="border-style: dashed !important; cursor: pointer; background: #f8f9fa;" onclick="document.getElementById('customThumbnailInput').click()"><i class="bx bx-image-add fs-2 text-muted mb-2"></i><p class="mb-1 text-muted small">Cliquez pour uploader une image</p><p class="mb-0 text-muted" style="font-size: 0.75rem;">JPG, PNG, GIF, WEBP (max 2MB)</p></div>
                                                    <input type="file" id="customThumbnailInput" class="d-none" accept="image/*">
                                                    <div id="customThumbnailPreview" class="mt-2 d-none"><div class="position-relative d-inline-block"><img src="" class="rounded" style="width: 120px; height: 120px; object-fit: cover;" id="customThumbnailImg"><button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" style="padding: 0.1rem 0.3rem; font-size: 0.7rem;" onclick="removeCustomThumbnail()"><i class="bx bx-x"></i></button><div class="position-absolute bottom-0 start-0 w-100 bg-success text-white text-center small" style="font-size: 0.65rem;"><i class="bx bx-check me-1"></i>Personnalisée</div></div></div>
                                                    <div id="thumbnailUploadProgress" class="mt-2 d-none"><div class="progress" style="height: 4px;"><div class="progress-bar bg-success" style="width: 0%"></div></div><small class="text-muted">Upload...</small></div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="thumbnail" id="selectedThumbnail">
                                        </div>
                                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">Catégorie</label><input type="text" class="form-control" name="categorie" id="audioCategory" list="categoriesList"></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label fw-bold">Crédits / Artiste</label><input type="text" class="form-control" name="credits" id="audioCredits"></div>
                                </div>
                                <div class="col-md-4 bg-light p-4">
                                    <h6 class="mb-3"><i class="bx bx-cog me-2"></i>Paramètres</h6>
                                    <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="est_actif" value="1" checked><label class="form-check-label fw-bold">Public</label></div></div>
                                    <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1"><label class="form-check-label"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label></div></div>
                                    <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked><label class="form-check-label"><i class="bx bx-globe text-primary me-1"></i>Site Web</label></div></div>
                                    <hr class="my-4"><h6 class="mb-2"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                                    <ul class="list-unstyled small text-muted mb-0" id="audioInfoList"></ul>
                                    <input type="hidden" name="uploaded_file_path" id="uploadedFilePath"><input type="hidden" name="auto_detected_data" id="autoDetectedData"><input type="hidden" name="type_source" value="upload">
                                </div>
                            </div>
                            <div class="p-4 border-top bg-white"><div class="d-flex justify-content-between"><button type="button" class="btn btn-outline-secondary" onclick="resetUpload()"><i class="bx bx-arrow-back me-1"></i>Annuler</button><button type="submit" class="btn btn-spotify btn-lg"><i class="bx bx-save me-1"></i>Publier</button></div></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio Player Modal -->
    <div class="modal fade" id="playerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark border-0">
                <div class="modal-header border-secondary border-bottom-0"><h5 class="modal-title text-white" id="playerTitle"></h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-0"><div class="p-4 text-center"><div id="playerArt" class="mb-4"><img src="" class="rounded shadow-lg" style="width: 300px; height: 300px; object-fit: cover;" id="playerAlbumArt"></div><div class="audio-player-wrapper"><audio id="mainAudioPlayer" controls class="w-100" style="height: 50px;"><source src="" type="audio/mpeg">Votre navigateur ne supporte pas la lecture audio.</audio></div><div id="playerWaveform" class="mt-3" style="height: 60px; background: rgba(255,255,255,0.1); border-radius: 4px;"></div></div></div>
                <div class="modal-footer border-secondary border-top-0 bg-dark justify-content-center"><div class="text-white-50 small" id="playerStats"></div></div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="bx bx-trash me-2"></i>Supprimer</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form action="<?= base_url('audio/Delete') ?>" method="POST"><div class="modal-body text-center py-4"><i class="bx bx-error-circle text-danger display-4 mb-3"></i><h5>Confirmer la suppression</h5><p class="text-muted" id="deleteAudioTitle"></p><div class="alert alert-warning"><i class="bx bx-info-circle me-2"></i>Cette action supprimera définitivement l'audio et tous les fichiers associés.</div><input type="hidden" name="id" id="deleteAudioId"></div><div class="modal-footer justify-content-center"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-danger"><i class="bx bx-trash me-1"></i>Supprimer</button></div></form></div>
        </div>
    </div>

    <datalist id="categoriesList">
        <option value="Musique"><option value="Podcast"><option value="Interview"><option value="Conférence"><option value="Méditation"><option value="Son"><option value="Livre audio"><option value="Radio">
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
// ==========================================
// CONFIGURATION
// ==========================================
toastr.options = { "closeButton": true, "debug": false, "newestOnTop": true, "progressBar": true, "positionClass": "toast-top-right", "preventDuplicates": false, "showDuration": "300", "hideDuration": "1000", "timeOut": "5000" };

const UPLOAD_CONFIG = {
    baseUrl: '<?= base_url('audio/') ?>',
    chunkSize: null,
    maxFileSize: 500 * 1024 * 1024
};

let uploadManager = null;

class FileUtils {
    static formatBytes(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

class AudioUploadManager {
    constructor() {
        this.reset();
    }
    
    reset() {
        this.state = {
            file: null,
            uploadId: null,
            totalChunks: 0,
            chunkSize: 0,
            uploadedChunks: new Set(),
            failedChunks: new Map(),
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
            
            if (file.size > UPLOAD_CONFIG.maxFileSize) throw new Error(`Fichier trop grand. Maximum: 500MB`);
            
            this.updateUI('init', { fileName: file.name, fileSize: FileUtils.formatBytes(file.size) });
            
            const initData = await this.apiCall('initUpload', { file_name: file.name, file_size: file.size });
            
            this.state.uploadId = initData.upload_id;
            this.state.totalChunks = initData.total_chunks;
            this.state.chunkSize = initData.chunk_size;
            
            console.log(`Upload: ${this.state.totalChunks} chunks de ${FileUtils.formatBytes(this.state.chunkSize)}`);
            
            this.updateUI('uploading', { percent: 0 });
            await this.uploadAllChunks();
            
            if (this.state.isCancelled) return;
            
            this.updateUI('processing', { message: 'Traitement audio...' });
            const result = await this.apiCall('completeUpload', { upload_id: this.state.uploadId });
            
            this.state.metadata = result.data;
            this.updateUI('complete', result.data);
            
        } catch (error) {
            console.error('Upload error:', error);
            this.updateUI('error', { message: error.message });
        } finally {
            this.state.isUploading = false;
        }
    }
    
    async uploadAllChunks() {
        for (let i = 0; i < this.state.totalChunks; i++) {
            if (this.state.isCancelled) break;
            
            let attempt = 0;
            const maxAttempts = 3;
            let success = false;
            
            while (!success && attempt < maxAttempts && !this.state.isCancelled) {
                try {
                    await this.uploadSingleChunk(i);
                    success = true;
                } catch (error) {
                    attempt++;
                    console.error(`Chunk ${i} failed (${attempt}/${maxAttempts}):`, error);
                    if (attempt < maxAttempts) await new Promise(r => setTimeout(r, 2000));
                    else throw new Error(`Chunk ${i} échoué après ${maxAttempts} tentatives`);
                }
            }
        }
    }
    
    async uploadSingleChunk(index) {
        const start = index * this.state.chunkSize;
        const end = Math.min(start + this.state.chunkSize, this.state.file.size);
        const chunk = this.state.file.slice(start, end);
        
        const formData = new FormData();
        formData.append('upload_id', this.state.uploadId);
        formData.append('chunk_index', index);
        formData.append('chunk', chunk);
        
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000);
        
        const response = await fetch(UPLOAD_CONFIG.baseUrl + 'uploadChunk', {
            method: 'POST',
            body: formData,
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const text = await response.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { throw new Error('Réponse invalide'); }
        if (!data.success) throw new Error(data.message);
        
        this.state.uploadedChunks.add(index);
        this.state.bytesUploaded += chunk.size;
        
        const progress = (this.state.uploadedChunks.size / this.state.totalChunks) * 100;
        const speed = ((Date.now() - this.state.startTime) / 1000) > 0 ? (this.state.bytesUploaded / ((Date.now() - this.state.startTime) / 1000)) / (1024 * 1024) : 0;
        
        this.updateUI('progress', {
            percent: progress,
            uploadedChunks: this.state.uploadedChunks.size,
            totalChunks: this.state.totalChunks,
            speed: speed
        });
    }
    
    async apiCall(endpoint, data) {
        const formData = new FormData();
        for (let key in data) formData.append(key, data[key]);
        
        const response = await fetch(UPLOAD_CONFIG.baseUrl + endpoint, { method: 'POST', body: formData });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const text = await response.text();
        let result;
        try { result = JSON.parse(text); } catch(e) { throw new Error('Réponse serveur invalide'); }
        if (!result.success) throw new Error(result.message);
        return result;
    }
    
    async cancel() {
        this.state.isCancelled = true;
        this.updateUI('cancel');
    }
    
    updateUI(event, data = {}) {
        const handlers = {
            init: () => { $('#uploadStep1').addClass('d-none'); $('#uploadStep2').removeClass('d-none'); $('#uploadFileName').text(data.fileName); $('#uploadFileSize').text(data.fileSize); },
            uploading: () => { $('#uploadPhase').text('Upload en cours...'); $('#processingStatus').removeClass('d-none'); $('#step-upload-icon').removeClass('bx-loader-alt bx-spin').addClass('bx-check-circle text-success'); $('#step-upload-status').removeClass('bg-secondary').addClass('bg-success').text('OK'); },
            progress: () => { $('#uploadProgressBar').css('width', Math.round(data.percent) + '%'); $('#uploadPercent').text(Math.round(data.percent) + '%'); $('#uploadChunks').text(`${data.uploadedChunks} / ${data.totalChunks} chunks`); $('#uploadSpeed').text(data.speed.toFixed(2) + ' MB/s'); },
            processing: () => { $('#uploadPhase').text('Traitement audio...'); $('#uploadProgressBar').removeClass('progress-bar-animated'); setTimeout(() => { $('#step-analysis-icon').removeClass('bx-loader-alt bx-spin').addClass('bx-check-circle text-success'); $('#step-analysis-status').removeClass('bg-secondary').addClass('bg-success').text('OK'); }, 500); },
            complete: () => { $('#uploadStep2').addClass('d-none'); $('#uploadStep3').removeClass('d-none'); populateDetailsForm(data); },
            error: () => { toastr.error(data.message, 'Erreur'); resetUpload(); },
            cancel: () => { resetUpload(); }
        };
        if (handlers[event]) handlers[event]();
    }
}

function populateDetailsForm(data) {
    if (data.form_suggestions) {
        $('#audioTitle').val(data.form_suggestions.titre || '');
        $('#audioCredits').val(data.form_suggestions.credits || '');
        $('#audioCategory').val(data.form_suggestions.categorie || '');
    }
    
    let infoHtml = '';
    if (data.analysis) {
        infoHtml += `<li><i class="bx bx-time me-2"></i>Durée: ${data.analysis.duration_formatted || 'N/A'}</li>`;
        infoHtml += `<li><i class="bx bx-signal-4 me-2"></i>Bitrate: ${Math.round((data.analysis.bitrate || 0) / 1000)} kbps</li>`;
        infoHtml += `<li><i class="bx bx-volume-full me-2"></i>Sample: ${data.analysis.sample_rate || 'N/A'} Hz</li>`;
        infoHtml += `<li><i class="bx bx-layer me-2"></i>Canaux: ${data.analysis.channels || 'N/A'}</li>`;
        infoHtml += `<li><i class="bx bx-chip me-2"></i>Codec: ${(data.analysis.codec || 'N/A').toUpperCase()}</li>`;
    }
    if (data.file_size) infoHtml += `<li><i class="bx bx-hdd me-2"></i>Taille: ${data.file_size}</li>`;
    $('#audioInfoList').html(infoHtml || '<li>Aucune information disponible</li>');
    
    $('#uploadedFilePath').val(data.original_file || '');
    $('#autoDetectedData').val(JSON.stringify(data));
    
    $('#selectedThumbnail').val('');
    $('#customThumbnailPreview').addClass('d-none');
    $('#customThumbnailInput').val('');
    $('#thumbnailUploadProgress').addClass('d-none');
    $('#upload-tab').removeClass('active');
    $('#generated-tab').addClass('active');
    $('#generated-thumbnails').addClass('show active');
    $('#upload-thumbnail').removeClass('show active');
    
    let thumbnails = data.thumbnails || {};
    if (Array.isArray(thumbnails)) { let t = {}; thumbnails.forEach((item,i) => { if(item) t['thumb_'+i]=item; }); thumbnails = t; }
    
    let thumbHtml = '', firstThumbUrl = null, thumbCount = 0;
    Object.entries(thumbnails).forEach(([type, url]) => {
        if (url && typeof url === 'string') {
            const fullUrl = url.startsWith('http') ? url : '<?= base_url() ?>' + url;
            if (thumbCount === 0) firstThumbUrl = url;
            const label = type === 'cover' ? 'Cover' : (type === 'generated' ? 'Waveform' : type);
            thumbHtml += `<div class="position-relative cursor-pointer thumbnail-option ${thumbCount===0?'selected':''}" onclick="selectThumbnail('${url}', this)" data-thumb-url="${url}" style="width:120px;height:120px;border:3px solid ${thumbCount===0?'#1DB954':'transparent'};border-radius:8px;overflow:hidden;"><img src="${fullUrl}" class="w-100 h-100" style="object-fit:cover;"><div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-white text-center py-1" style="font-size:0.75rem;">${label}</div>${thumbCount===0?'<div class="position-absolute top-0 end-0 m-1"><i class="bx bx-check-circle text-success bg-white rounded-circle"></i></div>':''}</div>`;
            thumbCount++;
        }
    });
    if (thumbCount === 0) thumbHtml = `<div class="text-center p-3 bg-light rounded w-100"><i class="bx bx-image fs-2 text-muted mb-2"></i><p class="small text-muted mb-0">Aucune miniature générée</p><p class="small text-muted">Utilisez l'onglet "Upload" pour ajouter une image</p></div>`;
    $('#thumbnailSelector').html(thumbHtml);
    if (firstThumbUrl) $('#selectedThumbnail').val(firstThumbUrl);
}

function selectThumbnail(url, element) {
    $('#selectedThumbnail').val(url);
    $('.thumbnail-option').css('border', '3px solid transparent').find('.bx-check-circle').remove();
    $(element).css('border', '3px solid #1DB954');
    if (!$(element).find('.bx-check-circle').length) $(element).append('<div class="position-absolute top-0 end-0 m-1"><i class="bx bx-check-circle text-success bg-white rounded-circle"></i></div>');
    removeCustomThumbnail();
    toastr.success('Miniature sélectionnée');
}

function uploadCustomThumbnail(file) {
    if (!file) return;
    if (!['image/jpeg','image/png','image/gif','image/webp'].includes(file.type)) { toastr.error('Format non supporté'); return; }
    if (file.size > 2*1024*1024) { toastr.error('Image trop grande (max 2MB)'); return; }
    
    $('#thumbnailUploadProgress').removeClass('d-none');
    $('#thumbnailUploadProgress .progress-bar').css('width', '0%');
    
    const formData = new FormData();
    formData.append('thumbnail_file', file);
    
    $.ajax({
        url: UPLOAD_CONFIG.baseUrl + 'uploadThumbnail',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        xhr: function() { const xhr = new XMLHttpRequest(); xhr.upload.addEventListener('progress', function(evt) { if (evt.lengthComputable) $('#thumbnailUploadProgress .progress-bar').css('width', (evt.loaded/evt.total*100)+'%'); }, false); return xhr; },
        success: function(response) {
            $('#thumbnailUploadProgress').addClass('d-none');
            if (response.success && response.file_path) {
                $('#customThumbnailImg').attr('src', response.preview_url);
                $('#customThumbnailPreview').removeClass('d-none');
                $('#selectedThumbnail').val(response.file_path);
                $('.thumbnail-option').css('border', '3px solid transparent').find('.bx-check-circle').remove();
                $('#generated-tab').removeClass('active'); $('#upload-tab').addClass('active');
                $('#generated-thumbnails').removeClass('show active'); $('#upload-thumbnail').addClass('show active');
                toastr.success('Miniature uploadée');
            } else toastr.error(response.message || 'Erreur upload');
        },
        error: function(xhr, status, error) {
            $('#thumbnailUploadProgress').addClass('d-none');
            let errorMsg = 'Erreur upload';
            if (xhr.responseText) {
                try { const json = JSON.parse(xhr.responseText); errorMsg = json.message || errorMsg; } catch(e) {}
            }
            toastr.error(errorMsg);
        }
    });
}

function removeCustomThumbnail() {
    $('#customThumbnailPreview').addClass('d-none');
    $('#customThumbnailImg').attr('src', '');
    $('#customThumbnailInput').val('');
    const firstThumb = $('.thumbnail-option').first();
    if (firstThumb.length) { const url = firstThumb.data('thumb-url'); if (url) selectThumbnail(url, firstThumb[0]); }
    else $('#selectedThumbnail').val('');
}

function uploadEditThumbnail(id, file) {
    if (!file) return;
    if (!['image/jpeg','image/png','image/gif','image/webp'].includes(file.type)) { toastr.error('Format non supporté'); return; }
    if (file.size > 2*1024*1024) { toastr.error('Image trop grande (max 2MB)'); return; }
    
    $(`#editThumbProgress${id}`).removeClass('d-none');
    $(`#editThumbProgress${id} .progress-bar`).css('width', '0%');
    
    const formData = new FormData();
    formData.append('thumbnail_file', file);
    
    $.ajax({
        url: UPLOAD_CONFIG.baseUrl + 'uploadThumbnail',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        xhr: function() { const xhr = new XMLHttpRequest(); xhr.upload.addEventListener('progress', function(evt) { if (evt.lengthComputable) $(`#editThumbProgress${id} .progress-bar`).css('width', (evt.loaded/evt.total*100)+'%'); }, false); return xhr; },
        success: function(response) {
            $(`#editThumbProgress${id}`).addClass('d-none');
            if (response.success && response.file_path) {
                $(`#currentThumb${id}`).attr('src', response.preview_url);
                $(`#editThumbImg${id}`).attr('src', response.preview_url);
                $(`#editThumbPreview${id}`).removeClass('d-none');
                $(`#editThumbSelected${id}`).val(response.file_path);
                $(`.edit-thumb-option[data-id="${id}"]`).css('border', '2px solid transparent');
                toastr.success('Miniature uploadée');
            } else toastr.error(response.message || 'Erreur upload');
        },
        error: function(xhr, status, error) {
            $(`#editThumbProgress${id}`).addClass('d-none');
            let errorMsg = 'Erreur upload';
            if (xhr.responseText) {
                try { const json = JSON.parse(xhr.responseText); errorMsg = json.message || errorMsg; } catch(e) {}
            }
            toastr.error(errorMsg);
        }
    });
}

function selectEditThumbnail(id, url, element) {
    $(`#editThumbSelected${id}`).val(url);
    const fullUrl = url.startsWith('http') ? url : '<?= base_url() ?>' + url;
    $(`#currentThumb${id}`).attr('src', fullUrl);
    $(element).closest('.modal-body').find('.edit-thumb-option').css('border', '2px solid transparent');
    $(element).css('border', '2px solid #1DB954');
    $(`#editThumbPreview${id}`).addClass('d-none');
    toastr.success('Miniature sélectionnée');
}

function removeEditThumbnail(id) {
    $(`#editThumbPreview${id}`).addClass('d-none');
    $(`#editThumbImg${id}`).attr('src', '');
    const firstOption = $(`#editModal${id} .edit-thumb-option`).first();
    if (firstOption.length) { const url = firstOption.data('thumb'); selectEditThumbnail(id, url, firstOption[0]); }
    else $(`#editThumbSelected${id}`).val('');
}

function resetUpload() {
    $('#uploadStep1').removeClass('d-none'); $('#uploadStep2').addClass('d-none'); $('#uploadStep3').addClass('d-none');
    $('#audioDetailsForm')[0].reset(); $('#selectedThumbnail').val(''); $('#thumbnailSelector').empty(); $('#customThumbnailPreview').addClass('d-none'); $('#customThumbnailInput').val('');
    $('#uploadProgressBar').css('width', '0%'); $('#uploadPercent').text('0%'); $('#uploadChunks').text('0 / 0 chunks'); $('#uploadSpeed').text('0 MB/s');
    $('#processingStatus').addClass('d-none'); $('#fileInput').val('');
    if (uploadManager) uploadManager.reset();
}

function openPlayer(id, title) {
    $('#playerTitle').text(title);
    const row = $(`tr[data-id="${id}"]`);
    const thumbSrc = row.find('img').first().attr('src');
    $('#playerAlbumArt').attr('src', thumbSrc || '<?= base_url('assets/images/audio-placeholder.jpg') ?>');
    $('#mainAudioPlayer').attr('src', '<?= base_url('audio/stream/') ?>' + id);
    const duration = row.find('td:eq(3) .badge').text();
    const bitrate = row.find('td:eq(2)').text().trim();
    $('#playerStats').html(`<i class="bx bx-time me-1"></i> ${duration} <span class="mx-2">|</span> ${bitrate}`);
    const modal = new bootstrap.Modal(document.getElementById('playerModal'));
    modal.show();
    document.getElementById('mainAudioPlayer').play().catch(e => console.log('Autoplay prevented'));
}

function toggleStatus(id, status) {
    $.ajax({ url: UPLOAD_CONFIG.baseUrl + 'ChangeStatus', type: 'POST', data: { id: id, est_actif: status }, dataType: 'json', success: function(r) { if(r && r.success) { toastr.success(status ? 'Audio public' : 'Audio privé'); setTimeout(()=>location.reload(),500); } else toastr.error('Erreur'); }, error: () => toastr.error('Erreur serveur') });
}

function confirmDelete(id, title) {
    $('#deleteAudioId').val(id);
    $('#deleteAudioTitle').text(title);
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

$(document).ready(function() {
    if ($.fn.DataTable) $('#audiosTable').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' }, order: [[0, 'desc']], pageLength: 25 });
    
    uploadManager = new AudioUploadManager();
    
    $('#fileInput').on('change', function(e) { if(e.target.files.length > 0) uploadManager.start(e.target.files[0]); });
    $('.upload-zone').on('dragover', function(e) { e.preventDefault(); $(this).addClass('drag-over'); }).on('dragleave', function(e) { e.preventDefault(); $(this).removeClass('drag-over'); }).on('drop', function(e) { e.preventDefault(); $(this).removeClass('drag-over'); const files = e.originalEvent.dataTransfer.files; if(files.length > 0) uploadManager.start(files[0]); });
    $('#cancelUploadBtn').on('click', function() { if(confirm('Annuler l\'upload ?')) uploadManager.cancel(); });
    $('#customThumbnailInput').on('change', function(e) { if(e.target.files[0]) uploadCustomThumbnail(e.target.files[0]); });
    
    $('.form-check-input[data-field]').on('change', function() { const id = $(this).data('id'), field = $(this).data('field'), value = $(this).is(':checked') ? 1 : 0; $.ajax({ url: UPLOAD_CONFIG.baseUrl + 'toggleField', type: 'POST', data: { id: id, field: field, value: value }, success: function(r) { if(r.success) toastr.success('Mis à jour'); else toastr.error('Erreur'); } }); });
    
    $(document).on('mouseenter', '.audio-thumb-wrapper', function() { $(this).find('.audio-overlay').css('background', 'rgba(0,0,0,0.5)'); $(this).find('.bx-play-circle').removeClass('opacity-0'); }).on('mouseleave', '.audio-thumb-wrapper', function() { $(this).find('.audio-overlay').css('background', 'rgba(0,0,0,0)'); $(this).find('.bx-play-circle').addClass('opacity-0'); });
    
    $('#closeUploadModal').on('click', function() { if(uploadManager && uploadManager.state.isUploading && !confirm('Upload en cours. Fermer ?')) return false; });
    
    $('#audioDetailsForm').on('submit', function(e) { if(!$('#audioTitle').val().trim()) { e.preventDefault(); toastr.error('Titre obligatoire'); $('#audioTitle').focus(); return false; } });
    
    $('#filterQuality').on('change', function() { const q = $(this).val(); const t = $('#audiosTable').DataTable(); q ? t.column(2).search(q + 'kbps').draw() : t.column(2).search('').draw(); });
    
    console.log('Audio Studio v5.0 initialisé (limites serveur: 2M chunks)');
});

$('head').append(`<style>.bg-spotify{background-color:#1DB954!important}.text-spotify{color:#1DB954!important}.btn-spotify{background-color:#1DB954;border-color:#1DB954;color:white}.btn-spotify:hover{background-color:#1ed760;border-color:#1ed760;color:white}.upload-zone.drag-over{background-color:rgba(29,185,84,0.1)!important;border-color:#1DB954!important}.thumbnail-option{transition:all .2s ease}.thumbnail-option:hover{transform:scale(1.05);box-shadow:0 4px 8px rgba(0,0,0,0.2)}.cursor-pointer{cursor:pointer}.w-fit-content{width:fit-content}</style>`);
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>