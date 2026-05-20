<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// ============================================================================
// HELPERS PHP
// ============================================================================
if (!function_exists('format_duration_audio')) {
    function format_duration_audio($seconds) {
        if (empty($seconds) || $seconds <= 0) return '0:00';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
    }
}
if (!function_exists('format_bytes_audio')) {
    function format_bytes_audio($bytes, $decimals = 2) {
        if (empty($bytes) || $bytes === 0) return '0 B';
        $k = 1024; $sizes = ['B','KB','MB','GB','TB'];
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
        return ['label' => $kbps.'kbps', 'class' => 'info'];
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
                        <li class="breadcrumb-item active" aria-current="page">Studio Audio v5.1</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a class="btn btn-spotify btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bx bx-upload"></i> <span class="d-none d-sm-inline">Uploader</span>
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="row mb-4 g-3">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
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
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
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
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
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
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                <i class="bx bx-chip text-warning fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Conversion</h6>
                                <h3 class="mb-0 fw-bold">
                                    <?= !empty($audio_capabilities['features']['multi_bitrate']) 
                                        ? '<span class="text-success"><i class="bx bx-check"></i> Multi</span>' 
                                        : '<span class="text-muted">Simple</span>' ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Audios -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0"><i class="bx bx-collection me-2 text-spotify"></i>Bibliothèque Audio</h5>
                    <select class="form-select form-select-sm" id="filterQuality" style="width:auto;">
                        <option value="">Toutes qualités</option>
                        <option value="320">320kbps</option>
                        <option value="256">256kbps</option>
                        <option value="192">192kbps</option>
                        <option value="128">128kbps</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="audiosTable" class="table table-hover align-middle mb-0">
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
                            $meta          = !empty($value['metadata_id3']) ? json_decode($value['metadata_id3'], true) : [];
                            $quality_info  = get_quality_badge_audio($value['bitrate'] ?? ($meta['analysis']['bitrate'] ?? 0));
                            $thumb_url     = base_url('assets/images/audio-placeholder.jpg');
                            if (!empty($value['miniature'])) {
                                $is_remote = (strpos($value['miniature'], 'http') === 0);
                                if ($is_remote || file_exists(FCPATH . $value['miniature'])) {
                                    $thumb_url = $is_remote ? $value['miniature'] : base_url($value['miniature']);
                                }
                            }
                            $js_title = htmlspecialchars($value['titre'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr data-id="<?= $value['id_media'] ?>" data-bitrate="<?= $value['bitrate'] ?? 0 ?>">
                                <td>
                                    <div class="audio-thumb-wrapper position-relative" style="width:80px;height:80px;">
                                        <img src="<?= $thumb_url ?>" class="rounded w-100 h-100" style="object-fit:cover;"
                                             loading="lazy"
                                             onerror="this.src='<?= base_url('assets/images/audio-placeholder.jpg') ?>'">
                                        <div class="audio-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                                             onclick="openPlayer(<?= $value['id_media'] ?>, '<?= $js_title ?>')"
                                             style="background:rgba(0,0,0,0);transition:all .3s;cursor:pointer;">
                                            <i class="bx bx-play-circle text-white fs-2 opacity-0" style="transition:all .3s;"></i>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <h6 class="mb-1 fw-bold text-truncate" style="max-width:300px;" title="<?= htmlspecialchars($value['titre'] ?? '') ?>">
                                        <?= htmlspecialchars($value['titre'] ?? 'Sans titre') ?>
                                    </h6>
                                    <?php if (!empty($value['credits'])): ?>
                                        <small class="text-muted mb-1 d-block"><i class="bx bx-user me-1"></i><?= htmlspecialchars($value['credits']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($value['categorie'])): ?>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($value['categorie']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($meta['conversions'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($meta['conversions'] as $q => $conv): ?>
                                                <span class="badge bg-<?= $q === 'max' ? 'danger' : ($q === 'high' ? 'primary' : 'secondary') ?>" style="font-size:.7rem;">
                                                    <?= $conv['bitrate'] ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-<?= $quality_info['class'] ?>"><?= $quality_info['label'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-dark"><i class="bx bx-time me-1"></i><?= format_duration_audio($value['duree'] ?? 0) ?></span></td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <a href="javascript:void(0)" class="text-decoration-none" data-bs-toggle="dropdown">
                                            <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) 
                                                ? '<span class="badge bg-success">Publié</span>' 
                                                : '<span class="badge bg-secondary">Privé</span>' ?>
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
                                                title="Lire" style="width:32px;height:32px;padding:0;">
                                            <i class="bx bx-play fs-5"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary rounded-circle"
                                                data-bs-toggle="modal" data-bs-target="#editModal<?= $value['id_media'] ?>"
                                                title="Modifier" style="width:32px;height:32px;padding:0;">
                                            <i class="bx bx-edit fs-5"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle"
                                                onclick="confirmDelete(<?= $value['id_media'] ?>, '<?= $js_title ?>')"
                                                title="Supprimer" style="width:32px;height:32px;padding:0;">
                                            <i class="bx bx-trash fs-5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Édition -->
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
                                                        <div class="card border mb-3">
                                                            <div class="card-header bg-light"><h6 class="mb-0"><i class="bx bx-image me-2"></i>Miniature</h6></div>
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-5">
                                                                        <?php 
                                                                         $cur = $value['miniature'] ?? ''; 
                                                                         $disp = (!empty($cur) && (strpos($cur, 'http') === 0 || file_exists(FCPATH . $cur))) ? base_url($cur) : base_url('assets/images/audio-placeholder.jpg'); 
                                                                        ?>
                                                                        <img src="<?= $disp ?>" class="rounded w-100" style="height:120px;object-fit:cover;" id="currentThumb<?= $value['id_media'] ?>"
                                                                             onerror="this.src='<?= base_url('assets/images/audio-placeholder.jpg') ?>'">
                                                                        <?php if (!empty($meta['thumbnails'])): ?>
                                                                        <div class="mt-2">
                                                                            <label class="form-label small text-muted">Changer pour :</label>
                                                                            <div class="d-flex gap-2 flex-wrap">
                                                                                <?php foreach ($meta['thumbnails'] as $tk => $tu): if (empty($tu)) continue; ?>
                                                                                    <img src="<?= base_url($tu) ?>" class="rounded cursor-pointer edit-thumb-option"
                                                                                         style="width:60px;height:60px;object-fit:cover;border:2px solid transparent;"
                                                                                         onclick="selectEditThumbnail(<?= $value['id_media'] ?>, '<?= $tu ?>', this)" data-thumb="<?= $tu ?>">
                                                                                <?php endforeach; ?>
                                                                            </div>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="col-md-7">
                                                                        <label class="form-label small text-muted">Ou uploader une nouvelle</label>
                                                                        <div class="border rounded p-3 text-center mb-2"
                                                                             style="border-style:dashed!important;cursor:pointer;background:#f8f9fa;"
                                                                             onclick="document.getElementById('editThumbInput<?= $value['id_media'] ?>').click()">
                                                                            <i class="bx bx-cloud-upload fs-3 text-muted"></i>
                                                                            <p class="mb-0 small text-muted">Cliquez pour uploader</p>
                                                                        </div>
                                                                        <input type="file" id="editThumbInput<?= $value['id_media'] ?>" class="d-none" accept="image/*"
                                                                               onchange="uploadEditThumbnail(<?= $value['id_media'] ?>, this.files[0])">
                                                                        <div id="editThumbPreview<?= $value['id_media'] ?>" class="d-none position-relative">
                                                                            <img src="" class="rounded w-100" style="height:120px;object-fit:cover;" id="editThumbImg<?= $value['id_media'] ?>">
                                                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeEditThumbnail(<?= $value['id_media'] ?>)"><i class="bx bx-x"></i></button>
                                                                        </div>
                                                                        <div id="editThumbProgress<?= $value['id_media'] ?>" class="d-none mt-2">
                                                                            <div class="progress" style="height:4px;"><div class="progress-bar bg-success" style="width:0%"></div></div>
                                                                        </div>
                                                                        <input type="hidden" name="thumbnail" id="editThumbSelected<?= $value['id_media'] ?>" value="<?= htmlspecialchars($cur) ?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Catégorie</label>
                                                                <input type="text" class="form-control" name="categorie" value="<?= htmlspecialchars($value['categorie'] ?? '') ?>" list="categoriesList">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Date</label>
                                                                <input type="date" class="form-control" name="date_media" value="<?= (!empty($value['date_media']) && $value['date_media'] !== '0000-00-00') ? $value['date_media'] : '' ?>">
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Crédits / Artiste</label>
                                                            <input type="text" class="form-control" name="credits" value="<?= htmlspecialchars($value['credits'] ?? '') ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="card border h-100">
                                                            <div class="card-header bg-light"><h6 class="mb-0"><i class="bx bx-cog me-2"></i>Paramètres</h6></div>
                                                            <div class="card-body">
                                                                <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="est_actif" value="1" <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>><label class="form-check-label fw-bold">Audio public</label></div></div>
                                                                <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1" <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>><label class="form-check-label"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label></div></div>
                                                                <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_for_website" value="1" <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>><label class="form-check-label"><i class="bx bx-globe text-primary me-1"></i>Site Web</label></div></div>
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
                                    <i class="bx bx-music fs-1 text-muted mb-3 d-block"></i>
                                    <h5>Aucun audio</h5>
                                    <p class="text-muted">Commencez par uploader votre premier audio</p>
                                    <a href="javascript:;" class="btn btn-spotify" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                        <i class="bx bx-upload me-1"></i>Uploader
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- fin page-content -->

<!-- ==================== MODAL UPLOAD ==================== -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-spotify text-white border-0">
                <h5 class="modal-title"><i class="bx bx-upload me-2"></i>Uploader un audio</h5>
                <button type="button" class="btn-close btn-close-white" id="closeUploadModal" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">

                <!-- ÉTAPE 1: Sélection fichier -->
                <div id="uploadStep1" class="upload-zone p-5 text-center">
                    <div class="mb-4">
                        <i class="bx bx-cloud-upload text-spotify" style="font-size:5rem;"></i>
                    </div>
                    <h4 class="mb-3">Glissez-déposez un fichier audio</h4>
                    <p class="text-muted mb-4">ou <span class="text-spotify fw-bold" style="cursor:pointer;" onclick="document.getElementById('fileInput').click()">parcourir</span> vos fichiers</p>
                    <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
                        <span class="badge bg-light text-dark border">MP3</span>
                        <span class="badge bg-light text-dark border">WAV</span>
                        <span class="badge bg-light text-dark border">FLAC</span>
                        <span class="badge bg-light text-dark border">AAC</span>
                        <span class="badge bg-light text-dark border">OGG</span>
                        <span class="badge bg-light text-dark border">M4A</span>
                    </div>
                    <div class="alert alert-info border mx-auto" style="max-width:550px;">
                        <i class="bx bx-info-circle me-1"></i>
                        <strong>Upload intelligent:</strong> Chunks de 1.5MB · Fichiers jusqu'à 10GB · Reprise automatique en cas d'erreur
                    </div>
                    <input type="file" id="fileInput" class="d-none" accept="audio/*">
                </div>

                <!-- ÉTAPE 2: Progression upload -->
                <div id="uploadStep2" class="d-none p-4">
                    <div class="mb-3 d-flex align-items-center">
                        <div class="bg-spotify bg-opacity-10 rounded p-2 me-3">
                            <i class="bx bx-music text-spotify fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold" id="uploadFileName">fichier.mp3</h6>
                            <small class="text-muted" id="uploadFileSize">0 MB</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" id="cancelUploadBtn">
                            <i class="bx bx-x me-1"></i>Annuler
                        </button>
                    </div>

                    <!-- Barre de progression principale -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-spotify" id="uploadPhase">Initialisation...</span>
                            <span class="fw-bold" id="uploadPercent">0%</span>
                        </div>
                        <div class="progress mb-1" style="height:12px;border-radius:6px;">
                            <div class="progress-bar bg-spotify progress-bar-striped progress-bar-animated"
                                 id="uploadProgressBar" role="progressbar"
                                 style="width:0%;transition:width 0.3s ease;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span id="uploadSpeed">0 KB/s</span>
                            <span id="uploadChunks">0 / 0 chunks</span>
                            <span id="uploadETA">--</span>
                        </div>
                    </div>

                    <!-- Étapes de traitement -->
                    <div id="processingStatus" class="d-none">
                        <div class="card bg-light border-0">
                            <div class="card-body py-3">
                                <h6 class="mb-3"><i class="bx bx-cog bx-spin me-2 text-spotify"></i>Traitement en cours...</h6>

                                <!-- Barre progression traitement -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted" id="processingLabel">Assemblage des chunks...</small>
                                        <small class="fw-bold" id="processingPercent">0%</small>
                                    </div>
                                    <div class="progress" style="height:6px;">
                                        <div class="progress-bar bg-info progress-bar-striped progress-bar-animated"
                                             id="processingProgressBar" style="width:0%;transition:width 0.5s ease;"></div>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="d-flex align-items-center p-2 bg-white rounded border">
                                            <i class="bx bx-check-circle text-success me-2 fs-5" id="step-upload-icon"></i>
                                            <span class="flex-grow-1">Upload chunks</span>
                                            <span class="badge bg-success" id="step-upload-status">✓</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center p-2 bg-white rounded border">
                                            <i class="bx bx-loader-alt bx-spin text-warning me-2 fs-5" id="step-assemble-icon"></i>
                                            <span class="flex-grow-1">Assemblage fichier</span>
                                            <span class="badge bg-warning text-dark" id="step-assemble-status">...</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center p-2 bg-white rounded border">
                                            <i class="bx bx-circle text-muted me-2 fs-5" id="step-analysis-icon"></i>
                                            <span class="flex-grow-1">Analyse audio (ID3, durée, bitrate)</span>
                                            <span class="badge bg-secondary" id="step-analysis-status">Attente</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center p-2 bg-white rounded border">
                                            <i class="bx bx-circle text-muted me-2 fs-5" id="step-thumbnail-icon"></i>
                                            <span class="flex-grow-1">Extraction miniature</span>
                                            <span class="badge bg-secondary" id="step-thumbnail-status">Attente</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center p-2 bg-white rounded border">
                                            <i class="bx bx-circle text-muted me-2 fs-5" id="step-waveform-icon"></i>
                                            <span class="flex-grow-1">Génération waveform</span>
                                            <span class="badge bg-secondary" id="step-waveform-status">Attente</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center p-2 bg-white rounded border">
                                            <i class="bx bx-circle text-muted me-2 fs-5" id="step-conversion-icon"></i>
                                            <span class="flex-grow-1">Conversion multi-bitrate (64k→320k)</span>
                                            <span class="badge bg-secondary" id="step-conversion-status">Attente</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ÉTAPE 3: Formulaire détails -->
                <div id="uploadStep3" class="d-none">
                    <form id="audioDetailsForm" action="<?= base_url('audio/Create') ?>" method="POST">
                        <div class="row g-0">
                            <div class="col-md-8 p-4 border-end">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" name="titre" id="audioTitle" required maxlength="255">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea class="form-control" name="description" rows="5" id="audioDescription"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Miniature</label>
                                        <ul class="nav nav-tabs mb-3" role="tablist">
                                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#generated-thumbnails" type="button"><i class="bx bx-music me-1"></i>Générées</button></li>
                                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#upload-thumbnail" type="button"><i class="bx bx-upload me-1"></i>Upload</button></li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="generated-thumbnails">
                                                <div class="d-flex gap-2 flex-wrap" id="thumbnailSelector"></div>
                                            </div>
                                            <div class="tab-pane fade" id="upload-thumbnail">
                                                <div class="border rounded p-3 text-center" style="border-style:dashed!important;cursor:pointer;background:#f8f9fa;"
                                                     onclick="document.getElementById('customThumbnailInput').click()">
                                                    <i class="bx bx-image-add fs-2 text-muted mb-2"></i>
                                                    <p class="mb-1 text-muted small">Cliquez pour uploader</p>
                                                </div>
                                                <input type="file" id="customThumbnailInput" class="d-none" accept="image/*">
                                                <div id="customThumbnailPreview" class="mt-2 d-none">
                                                    <div class="position-relative d-inline-block">
                                                        <img src="" class="rounded" style="width:120px;height:120px;object-fit:cover;" id="customThumbnailImg">
                                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" style="padding:.1rem .3rem;" onclick="removeCustomThumbnail()"><i class="bx bx-x"></i></button>
                                                    </div>
                                                </div>
                                                <div id="thumbnailUploadProgress" class="mt-2 d-none">
                                                    <div class="progress" style="height:4px;"><div class="progress-bar bg-success" style="width:0%"></div></div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="thumbnail" id="selectedThumbnail">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Catégorie</label>
                                        <input type="text" class="form-control" name="categorie" id="audioCategory" list="categoriesList">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Crédits / Artiste</label>
                                    <input type="text" class="form-control" name="credits" id="audioCredits">
                                </div>
                            </div>
                            <div class="col-md-4 bg-light p-4">
                                <h6 class="mb-3"><i class="bx bx-cog me-2"></i>Paramètres</h6>
                                <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="est_actif" value="1" checked><label class="form-check-label fw-bold">Public</label></div></div>
                                <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1"><label class="form-check-label"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label></div></div>
                                <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked><label class="form-check-label"><i class="bx bx-globe text-primary me-1"></i>Site Web</label></div></div>
                                <hr class="my-4">
                                <h6 class="mb-2"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                                <ul class="list-unstyled small text-muted mb-0" id="audioInfoList"></ul>
                                <input type="hidden" name="uploaded_file_path" id="uploadedFilePath">
                                <input type="hidden" name="auto_detected_data" id="autoDetectedData">
                                <input type="hidden" name="type_source" value="upload">
                            </div>
                        </div>
                        <div class="p-4 border-top bg-white">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetUpload()"><i class="bx bx-arrow-back me-1"></i>Annuler</button>
                                <button type="submit" class="btn btn-spotify btn-lg"><i class="bx bx-save me-1"></i>Publier l'audio</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Lecteur -->
<div class="modal fade" id="playerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-secondary border-bottom-0">
                <h5 class="modal-title text-white" id="playerTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-4 text-center">
                    <div class="mb-4"><img src="" class="rounded shadow-lg" style="width:300px;height:300px;object-fit:cover;" id="playerAlbumArt"></div>
                    <audio id="mainAudioPlayer" controls class="w-100" style="height:50px;"><source src="" type="audio/mpeg"></audio>
                    <div id="playerWaveform" class="mt-3" style="height:60px;background:rgba(255,255,255,.1);border-radius:4px;"></div>
                </div>
            </div>
            <div class="modal-footer border-secondary border-top-0 bg-dark justify-content-center">
                <div class="text-white-50 small" id="playerStats"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Supprimer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('audio/Delete') ?>" method="POST">
                <div class="modal-body text-center py-4">
                    <i class="bx bx-error-circle text-danger display-4 mb-3"></i>
                    <h5>Confirmer la suppression</h5>
                    <p class="text-muted" id="deleteAudioTitle"></p>
                    <div class="alert alert-warning"><i class="bx bx-info-circle me-2"></i>Action irréversible.</div>
                    <input type="hidden" name="id" id="deleteAudioId">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger"><i class="bx bx-trash me-1"></i>Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<datalist id="categoriesList">
    <option value="Musique"><option value="Podcast"><option value="Interview">
    <option value="Conférence"><option value="Méditation"><option value="Son">
    <option value="Livre audio"><option value="Radio">
    <?php if (!empty($categories)): foreach ($categories as $cat): ?>
        <option value="<?= htmlspecialchars($cat) ?>">
    <?php endforeach; endif; ?>
</datalist>

<!-- ==================== SCRIPTS ==================== -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<style>
.bg-spotify  { background-color: #1DB954 !important; }
.text-spotify{ color: #1DB954 !important; }
.btn-spotify { background-color: #1DB954; border-color: #1DB954; color: #fff; }
.btn-spotify:hover { background-color: #1ed760; border-color: #1ed760; color: #fff; }
.upload-zone.drag-over { background-color: rgba(29,185,84,.1) !important; border: 2px dashed #1DB954 !important; border-radius: 8px; }
.thumbnail-option { transition: all .2s ease; cursor: pointer; }
.thumbnail-option:hover { transform: scale(1.05); box-shadow: 0 4px 8px rgba(0,0,0,.2); }
#uploadProgressBar { min-width: 2%; }
</style>

<script>
// ==========================================
// CONFIG GLOBALE
// ==========================================
toastr.options = {
    closeButton: true, progressBar: true,
    positionClass: "toast-top-right",
    timeOut: "5000", showDuration: "300"
};

const BASE_URL = '<?= base_url('audio/') ?>';
let uploadManager = null;

// ==========================================
// UTILITAIRES
// ==========================================
function formatBytes(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024, sizes = ['B','KB','MB','GB','TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatTime(seconds) {
    if (!isFinite(seconds) || seconds <= 0) return '--';
    const m = Math.floor(seconds / 60), s = Math.floor(seconds % 60);
    return m > 0 ? `${m}m ${s}s` : `${s}s`;
}

// ==========================================
// GESTIONNAIRE D'UPLOAD CHUNKED
// Supporte des fichiers illimités (ex: 10GB)
// Chunks de 1.5MB max (sous upload_max_filesize=2M)
// ==========================================
class AudioUploadManager {

    constructor() {
        this._reset();
    }

    _reset() {
        this.file           = null;
        this.uploadId       = null;
        this.totalChunks    = 0;
        this.chunkSize      = 0;         // Vient du SERVEUR
        this.uploadedChunks = new Set();
        this.isCancelled    = false;
        this.isUploading    = false;
        this.startTime      = null;
        this.bytesUploaded  = 0;
    }

    async start(file) {
        try {
            this._reset();
            this.file        = file;
            this.isUploading = true;
            this.startTime   = Date.now();

            // Afficher étape 2
            $('#uploadStep1').addClass('d-none');
            $('#uploadStep2').removeClass('d-none');
            $('#uploadFileName').text(file.name);
            $('#uploadFileSize').text(formatBytes(file.size));
            this._setProgress(0, 'Initialisation...');

            // ---- ÉTAPE 1: initUpload ----
            const initData = await this._apiPost('initUpload', {
                file_name: file.name,
                file_size: file.size
            });

            // Récupérer la config depuis le SERVEUR (chunk_size officiel)
            this.uploadId    = initData.upload_id;
            this.totalChunks = initData.total_chunks;
            this.chunkSize   = initData.chunk_size; // 1.5MB du serveur

            console.log(`✅ initUpload OK | ID: ${this.uploadId} | ${this.totalChunks} chunks × ${formatBytes(this.chunkSize)}`);

            this._setProgress(1, 'Upload en cours...');

            // ---- ÉTAPE 2: uploadAllChunks ----
            await this._uploadAllChunks();
            if (this.isCancelled) return;

            // ---- ÉTAPE 3: completeUpload ----
            this._showProcessing();
            const result = await this._apiPost('completeUpload', { upload_id: this.uploadId });

            this._completeSuccess(result.data);

        } catch (err) {
            console.error('Upload error:', err);
            toastr.error(err.message || 'Erreur upload', 'Erreur');
            resetUpload();
        } finally {
            this.isUploading = false;
        }
    }

    async _uploadAllChunks() {
        // Upload séquentiel pour garantir l'ordre et éviter les surcharges serveur
        for (let i = 0; i < this.totalChunks; i++) {
            if (this.isCancelled) throw new Error('Annulé');

            let attempts = 0;
            while (attempts < 4) {
                try {
                    await this._uploadOneChunk(i);
                    break; // Succès
                } catch (err) {
                    attempts++;
                    console.warn(`Chunk ${i} tentative ${attempts}/4:`, err.message);
                    if (attempts >= 4) throw new Error(`Chunk ${i} échoué après 4 tentatives: ${err.message}`);
                    // Attendre avant de réessayer (backoff exponentiel)
                    await new Promise(r => setTimeout(r, attempts * 1500));
                }
            }
        }
    }

    async _uploadOneChunk(index) {
        // Calculer la plage du chunk
        const start = index * this.chunkSize;
        const end   = Math.min(start + this.chunkSize, this.file.size);
        const blob  = this.file.slice(start, end);

        const form = new FormData();
        form.append('upload_id',    this.uploadId);
        form.append('chunk_index',  index);
        form.append('chunk',        blob, `chunk_${index}`);

        // Timeout 60s par chunk
        const ctrl    = new AbortController();
        const timeout = setTimeout(() => ctrl.abort(), 60000);

        let response;
        try {
            response = await fetch(BASE_URL + 'uploadChunk', {
                method: 'POST',
                body:   form,
                signal: ctrl.signal
            });
        } finally {
            clearTimeout(timeout);
        }

        if (!response.ok) throw new Error(`HTTP ${response.status} ${response.statusText}`);

        const text = await response.text();
        let data;
        try { data = JSON.parse(text); }
        catch (e) { throw new Error(`Réponse non-JSON: ${text.substring(0, 200)}`); }

        if (!data.success) throw new Error(data.message || 'Erreur serveur chunk');

        // Mise à jour progression
        this.uploadedChunks.add(index);
        this.bytesUploaded += blob.size;

        const percent  = (this.uploadedChunks.size / this.totalChunks) * 100;
        const elapsed  = (Date.now() - this.startTime) / 1000;
        const speed    = elapsed > 0 ? this.bytesUploaded / elapsed : 0;
        const remaining = speed > 0 ? (this.file.size - this.bytesUploaded) / speed : 0;

        this._setProgress(percent, 'Upload en cours...');
        $('#uploadChunks').text(`${this.uploadedChunks.size} / ${this.totalChunks} chunks`);
        $('#uploadSpeed').text(formatBytes(speed) + '/s');
        $('#uploadETA').text('ETA: ' + formatTime(remaining));
    }

    _setProgress(percent, label) {
        const pct = Math.min(100, Math.max(0, Math.round(percent)));
        $('#uploadProgressBar')
            .css('width', pct + '%')
            .attr('aria-valuenow', pct);
        $('#uploadPercent').text(pct + '%');
        if (label) $('#uploadPhase').text(label);
    }

    _showProcessing() {
        this._setProgress(100, 'Traitement sur le serveur...');
        $('#uploadProgressBar')
            .removeClass('bg-spotify')
            .addClass('bg-success')
            .removeClass('progress-bar-striped progress-bar-animated');

        $('#processingStatus').removeClass('d-none');

        // Animation des étapes
        this._animateStep('assemble', 0);
        this._animateStep('analysis', 800);
        this._animateStep('thumbnail', 1600);
        this._animateStep('waveform', 2400);
        this._animateStep('conversion', 3200);
        this._animateProcessingBar();
    }

    _animateStep(step, delay) {
        setTimeout(() => {
            $(`#step-${step}-icon`)
                .removeClass('bx-circle text-muted')
                .addClass('bx-loader-alt bx-spin text-warning');
            $(`#step-${step}-status`)
                .removeClass('bg-secondary')
                .addClass('bg-warning text-dark')
                .text('...');
        }, delay);
    }

    _animateProcessingBar() {
        let pct = 0;
        const steps = ['Assemblage fichier...', 'Analyse audio...', 'Extraction miniature...', 'Génération waveform...', 'Conversion multi-bitrate...'];
        let stepIdx = 0;
        const interval = setInterval(() => {
            pct += 3;
            if (pct > 95) pct = 95;
            $('#processingProgressBar').css('width', pct + '%');
            $('#processingPercent').text(Math.round(pct) + '%');
            if (pct > 0 && stepIdx < steps.length && pct >= (stepIdx + 1) * 18) {
                $('#processingLabel').text(steps[stepIdx]);
                stepIdx++;
            }
        }, 200);
        this._processingInterval = interval;
    }

    _completeSuccess(data) {
        clearInterval(this._processingInterval);

        // Marquer toutes les étapes comme terminées
        ['upload','assemble','analysis','thumbnail','waveform','conversion'].forEach(s => {
            $(`#step-${s}-icon`)
                .removeClass('bx-circle bx-loader-alt bx-spin text-muted text-warning')
                .addClass('bx-check-circle text-success');
            $(`#step-${s}-status`)
                .removeClass('bg-secondary bg-warning text-dark')
                .addClass('bg-success text-white')
                .text('✓');
        });

        $('#processingProgressBar').css('width', '100%');
        $('#processingPercent').text('100%');
        $('#processingLabel').text('Terminé !');

        setTimeout(() => {
            $('#uploadStep2').addClass('d-none');
            $('#uploadStep3').removeClass('d-none');
            this._fillDetailsForm(data);
        }, 600);
    }

    _fillDetailsForm(data) {
        // Suggestions de titre / crédits / catégorie
        if (data.form_suggestions) {
            $('#audioTitle').val(data.form_suggestions.titre || '');
            $('#audioCredits').val(data.form_suggestions.credits || '');
            $('#audioCategory').val(data.form_suggestions.categorie || '');
        }

        // Infos audio
        let info = '';
        if (data.analysis) {
            info += `<li><i class="bx bx-time me-2"></i>Durée: ${data.analysis.duration_formatted || 'N/A'}</li>`;
            info += `<li><i class="bx bx-signal-4 me-2"></i>Bitrate: ${Math.round((data.analysis.bitrate || 0)/1000)} kbps</li>`;
            info += `<li><i class="bx bx-volume-full me-2"></i>Sample: ${data.analysis.sample_rate || 'N/A'} Hz</li>`;
            info += `<li><i class="bx bx-layer me-2"></i>Canaux: ${data.analysis.channels || 'N/A'}</li>`;
            info += `<li><i class="bx bx-chip me-2"></i>Codec: ${(data.analysis.codec||'N/A').toUpperCase()}</li>`;
        }
        if (data.file_size) info += `<li><i class="bx bx-hdd me-2"></i>Taille: ${data.file_size}</li>`;
        $('#audioInfoList').html(info || '<li class="text-muted">Aucune info disponible</li>');

        $('#uploadedFilePath').val(data.original_file || '');
        $('#autoDetectedData').val(JSON.stringify(data));

        // Miniatures
        const thumbs = data.thumbnails || {};
        let thumbHtml = '', firstUrl = null;
        let idx = 0;

        const thumbEntries = typeof thumbs === 'object' && !Array.isArray(thumbs)
            ? Object.entries(thumbs)
            : [];

        thumbEntries.forEach(([type, url]) => {
            if (!url || typeof url !== 'string') return;
            const fullUrl = url.startsWith('http') ? url : '<?= base_url() ?>' + url;
            const isFirst = idx === 0;
            const label   = type === 'cover' ? 'Cover' : (type === 'generated' ? 'Waveform' : type);
            if (isFirst) firstUrl = url;

            thumbHtml += `
            <div class="position-relative thumbnail-option ${isFirst ? 'selected' : ''}"
                 onclick="selectThumbnail('${url}', this)" data-thumb-url="${url}"
                 style="width:120px;height:120px;border:3px solid ${isFirst ? '#1DB954' : 'transparent'};border-radius:8px;overflow:hidden;">
                <img src="${fullUrl}" class="w-100 h-100" style="object-fit:cover;">
                <div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-white text-center py-1" style="font-size:.75rem;">${label}</div>
                ${isFirst ? '<div class="position-absolute top-0 end-0 m-1"><i class="bx bx-check-circle text-success bg-white rounded-circle"></i></div>' : ''}
            </div>`;
            idx++;
        });

        if (idx === 0) {
            thumbHtml = `<div class="text-center p-3 bg-light rounded w-100"><i class="bx bx-image fs-2 text-muted mb-2"></i><p class="small text-muted mb-0">Aucune miniature générée</p><p class="small text-muted">Utilisez "Upload" pour ajouter une image</p></div>`;
        }

        $('#thumbnailSelector').html(thumbHtml);
        if (firstUrl) $('#selectedThumbnail').val(firstUrl);
    }

    cancel() {
        this.isCancelled = true;
        clearInterval(this._processingInterval);
        resetUpload();
        toastr.warning('Upload annulé');
    }

    async _apiPost(endpoint, data) {
        const form = new FormData();
        for (const k in data) form.append(k, data[k]);

        const response = await fetch(BASE_URL + endpoint, { method: 'POST', body: form });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const text = await response.text();
        let result;
        try { result = JSON.parse(text); }
        catch (e) { throw new Error(`Réponse serveur invalide: ${text.substring(0, 300)}`); }

        if (!result.success) throw new Error(result.message || `Erreur ${endpoint}`);
        return result;
    }
}

// ==========================================
// FONCTIONS GLOBALES
// ==========================================

function resetUpload() {
    $('#uploadStep1').removeClass('d-none');
    $('#uploadStep2').addClass('d-none');
    $('#uploadStep3').addClass('d-none');
    $('#audioDetailsForm')[0].reset();
    $('#selectedThumbnail').val('');
    $('#thumbnailSelector').empty();
    $('#customThumbnailPreview').addClass('d-none');
    $('#customThumbnailInput').val('');
    $('#uploadProgressBar').css('width','0%').attr('aria-valuenow',0)
        .addClass('bg-spotify progress-bar-striped progress-bar-animated')
        .removeClass('bg-success');
    $('#uploadPercent').text('0%');
    $('#uploadChunks').text('0 / 0 chunks');
    $('#uploadSpeed').text('0 KB/s');
    $('#uploadETA').text('--');
    $('#processingStatus').addClass('d-none');
    $('#processingProgressBar').css('width','0%');
    $('#fileInput').val('');
    if (uploadManager) uploadManager._reset();
}

function selectThumbnail(url, element) {
    $('#selectedThumbnail').val(url);
    $('.thumbnail-option').css('border','3px solid transparent').find('.bx-check-circle').closest('div').remove();
    $(element).css('border','3px solid #1DB954');
    $(element).append('<div class="position-absolute top-0 end-0 m-1"><i class="bx bx-check-circle text-success bg-white rounded-circle"></i></div>');
    removeCustomThumbnail(true);
}

function uploadCustomThumbnail(file) {
    if (!file) return;
    if (!['image/jpeg','image/png','image/gif','image/webp'].includes(file.type)) { toastr.error('Format non supporté'); return; }
    if (file.size > 2*1024*1024) { toastr.error('Image trop grande (max 2MB)'); return; }

    $('#thumbnailUploadProgress').removeClass('d-none').find('.progress-bar').css('width','0%');
    const form = new FormData();
    form.append('thumbnail_file', file);

    $.ajax({
        url: BASE_URL + 'uploadThumbnail', type: 'POST',
        data: form, processData: false, contentType: false, dataType: 'json',
        xhr: function() {
            const xhr = new XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) $('#thumbnailUploadProgress .progress-bar').css('width', (e.loaded/e.total*100)+'%');
            });
            return xhr;
        },
        success: function(r) {
            $('#thumbnailUploadProgress').addClass('d-none');
            if (r.success && r.file_path) {
                $('#customThumbnailImg').attr('src', r.preview_url);
                $('#customThumbnailPreview').removeClass('d-none');
                $('#selectedThumbnail').val(r.file_path);
                $('.thumbnail-option').css('border','3px solid transparent').find('.bx-check-circle').closest('div').remove();
                toastr.success('Miniature uploadée');
            } else toastr.error(r.message || 'Erreur upload');
        },
        error: function(xhr) {
            $('#thumbnailUploadProgress').addClass('d-none');
            try { const j = JSON.parse(xhr.responseText); toastr.error(j.message || 'Erreur upload'); }
            catch(e) { toastr.error('Erreur upload miniature'); }
        }
    });
}

function removeCustomThumbnail(silent = false) {
    $('#customThumbnailPreview').addClass('d-none');
    $('#customThumbnailImg').attr('src','');
    $('#customThumbnailInput').val('');
    if (!silent) {
        const first = $('.thumbnail-option').first();
        if (first.length) selectThumbnail(first.data('thumb-url'), first[0]);
        else $('#selectedThumbnail').val('');
    }
}

function uploadEditThumbnail(id, file) {
    if (!file) return;
    if (!['image/jpeg','image/png','image/gif','image/webp'].includes(file.type)) { toastr.error('Format non supporté'); return; }
    if (file.size > 2*1024*1024) { toastr.error('Image trop grande (max 2MB)'); return; }

    $(`#editThumbProgress${id}`).removeClass('d-none').find('.progress-bar').css('width','0%');
    const form = new FormData();
    form.append('thumbnail_file', file);

    $.ajax({
        url: BASE_URL + 'uploadThumbnail', type: 'POST',
        data: form, processData: false, contentType: false, dataType: 'json',
        xhr: function() {
            const xhr = new XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) $(`#editThumbProgress${id} .progress-bar`).css('width', (e.loaded/e.total*100)+'%');
            });
            return xhr;
        },
        success: function(r) {
            $(`#editThumbProgress${id}`).addClass('d-none');
            if (r.success && r.file_path) {
                $(`#currentThumb${id}`).attr('src', r.preview_url);
                $(`#editThumbImg${id}`).attr('src', r.preview_url);
                $(`#editThumbPreview${id}`).removeClass('d-none');
                $(`#editThumbSelected${id}`).val(r.file_path);
                toastr.success('Miniature uploadée');
            } else toastr.error(r.message || 'Erreur');
        },
        error: function() { $(`#editThumbProgress${id}`).addClass('d-none'); toastr.error('Erreur upload miniature'); }
    });
}

function selectEditThumbnail(id, url, element) {
    $(`#editThumbSelected${id}`).val(url);
    const full = url.startsWith('http') ? url : '<?= base_url() ?>' + url;
    $(`#currentThumb${id}`).attr('src', full);
    $(element).closest('.modal-body').find('.edit-thumb-option').css('border','2px solid transparent');
    $(element).css('border','2px solid #1DB954');
    $(`#editThumbPreview${id}`).addClass('d-none');
}

function removeEditThumbnail(id) {
    $(`#editThumbPreview${id}`).addClass('d-none');
    $(`#editThumbImg${id}`).attr('src','');
    const first = $(`#editModal${id} .edit-thumb-option`).first();
    if (first.length) selectEditThumbnail(id, first.data('thumb'), first[0]);
    else $(`#editThumbSelected${id}`).val('');
}

function openPlayer(id, title) {
    $('#playerTitle').text(title);
    const row     = $(`tr[data-id="${id}"]`);
    const thumbSrc = row.find('img').first().attr('src');
    $('#playerAlbumArt').attr('src', thumbSrc || '<?= base_url('assets/images/audio-placeholder.jpg') ?>');
    $('#mainAudioPlayer source').attr('src', '<?= base_url('audio/stream/') ?>' + id);
    $('#mainAudioPlayer')[0].load();
    const dur    = row.find('td:eq(3) .badge').text().trim();
    const bitrate = row.find('td:eq(2)').text().trim();
    $('#playerStats').html(`<i class="bx bx-time me-1"></i>${dur} <span class="mx-2">|</span>${bitrate}`);
    new bootstrap.Modal(document.getElementById('playerModal')).show();
    setTimeout(() => document.getElementById('mainAudioPlayer').play().catch(() => {}), 300);
}

function toggleStatus(id, status) {
    $.ajax({
        url: BASE_URL + 'ChangeStatus', type: 'POST',
        data: { id, est_actif: status }, dataType: 'json',
        success: function(r) {
            if (r && r.success) { toastr.success(status ? 'Audio publié' : 'Audio privé'); setTimeout(() => location.reload(), 800); }
            else toastr.error('Erreur changement statut');
        },
        error: () => toastr.error('Erreur serveur')
    });
}

function confirmDelete(id, title) {
    $('#deleteAudioId').val(id);
    $('#deleteAudioTitle').text('"' + title + '"');
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ==========================================
// INIT
// ==========================================
$(document).ready(function() {

    // DataTable
    if ($.fn.DataTable) {
        $('#audiosTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
            order: [[0,'desc']], pageLength: 25
        });
    }

    // Instancier le manager
    uploadManager = new AudioUploadManager();

    // Sélection fichier
    $('#fileInput').on('change', function(e) {
        if (e.target.files.length > 0) uploadManager.start(e.target.files[0]);
    });

    // Drag & drop
    $('#uploadStep1')
        .on('dragover',  function(e) { e.preventDefault(); $(this).addClass('drag-over'); })
        .on('dragleave', function(e) { e.preventDefault(); $(this).removeClass('drag-over'); })
        .on('drop', function(e) {
            e.preventDefault(); $(this).removeClass('drag-over');
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) uploadManager.start(files[0]);
        });

    // Annuler upload
    $('#cancelUploadBtn').on('click', function() {
        if (confirm('Annuler l\'upload en cours ?')) uploadManager.cancel();
    });

    // Miniature personnalisée
    $('#customThumbnailInput').on('change', function(e) {
        if (e.target.files[0]) uploadCustomThumbnail(e.target.files[0]);
    });

    // Toggles visibilité
    $(document).on('change', '.form-check-input[data-field]', function() {
        const id    = $(this).data('id');
        const field = $(this).data('field');
        const value = $(this).is(':checked') ? 1 : 0;
        $.ajax({
            url: BASE_URL + 'toggleField', type: 'POST',
            data: { id, field, value }, dataType: 'json',
            success: function(r) { if (r.success) toastr.success('Mis à jour'); else toastr.error('Erreur'); }
        });
    });

    // Survol miniature audio
    $(document).on('mouseenter', '.audio-thumb-wrapper', function() {
        $(this).find('.audio-overlay').css('background','rgba(0,0,0,.5)');
        $(this).find('.bx-play-circle').removeClass('opacity-0');
    }).on('mouseleave', '.audio-thumb-wrapper', function() {
        $(this).find('.audio-overlay').css('background','rgba(0,0,0,0)');
        $(this).find('.bx-play-circle').addClass('opacity-0');
    });

    // Fermer modal si upload en cours
    $('#closeUploadModal').on('click', function() {
        if (uploadManager && uploadManager.isUploading) {
            if (!confirm('Un upload est en cours. Voulez-vous vraiment fermer ?')) return false;
            uploadManager.cancel();
        }
    });

    // Validation formulaire
    $('#audioDetailsForm').on('submit', function(e) {
        if (!$('#audioTitle').val().trim()) {
            e.preventDefault();
            toastr.error('Le titre est obligatoire');
            $('#audioTitle').focus();
        }
    });

    // Filtre qualité
    $('#filterQuality').on('change', function() {
        const q = $(this).val();
        const t = $('#audiosTable').DataTable();
        q ? t.column(2).search(q+'kbps').draw() : t.column(2).search('').draw();
    });

    console.log('🎵 Audio Studio v5.1 — Upload chunked 1.5MB — Fichiers illimités');
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>
