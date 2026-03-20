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

// Détection source analyse
if (!function_exists('get_analysis_source_badge')) {
    function get_analysis_source_badge($meta) {
        if (empty($meta)) return ['text' => 'N/A', 'class' => 'secondary'];
        $data = is_string($meta) ? json_decode($meta, true) : $meta;
        $source = $data['analysis']['source'] ?? 'unknown';
        if ($source === 'ffprobe') return ['text' => 'FFmpeg', 'class' => 'success'];
        if ($source === 'estimated') return ['text' => 'Estimé', 'class' => 'warning'];
        return ['text' => 'Auto', 'class' => 'info'];
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

        <!-- Alertes Configuration -->
        <?php if (empty($audio_capabilities['hardware']['ffmpeg'])): ?>
        <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Mode estimation activé:</strong> FFmpeg n'est pas disponible sur ce serveur. 
            La durée et le bitrate seront estimés automatiquement à partir de la taille du fichier.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

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
                                <h6 class="text-muted mb-1">Stockage</h6>
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
                                <h6 class="text-muted mb-1">Détection</h6>
                                <h3 class="mb-0 fw-bold">
                                    <?= ($audio_capabilities['hardware']['ffmpeg'] ?? false) ? 'FFmpeg' : 'Estimation' ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Audio -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bx bx-music me-2 text-spotify"></i>Bibliothèque Audio
                    </h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm w-auto" id="filterCategory">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories ?? [] as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" class="form-control form-control-sm w-auto" id="searchAudio" placeholder="Rechercher...">
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="audioTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3" style="width: 50px;">#</th>
                                <th style="width: 80px;">Miniature</th>
                                <th>Titre & Infos</th>
                                <th>Catégorie</th>
                                <th class="text-center">Durée</th>
                                <th class="text-center">Qualité</th>
                                <th class="text-center">Taille</th>
                                <th class="text-center">Source</th>
                                <th class="text-center">Statut</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($audios ?? [] as $audio): 
                                $meta = json_decode($audio['metadata_id3'] ?? '{}', true);
                                $analysis = $meta['analysis'] ?? [];
                                $duration = $audio['duree'] ?? $analysis['duration'] ?? 0;
                                $bitrate = $audio['bitrate'] ?? $analysis['bitrate'] ?? 0;
                                $quality = get_quality_badge_audio($bitrate);
                                $source = get_analysis_source_badge($meta);
                            ?>
                            <tr data-id="<?= $audio['id_media'] ?>" data-category="<?= htmlspecialchars($audio['categorie'] ?? '') ?>">
                                <td class="ps-3 text-muted"><?= $i++ ?></td>
                                <td>
                                    <?php if (!empty($audio['miniature'])): ?>
                                        <img src="<?= base_url($audio['miniature']) ?>" 
                                             class="rounded shadow-sm" 
                                             style="width: 60px; height: 60px; object-fit: cover;"
                                             alt="Cover">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" 
                                             style="width: 60px; height: 60px;">
                                            <i class="bx bx-music text-muted fs-3"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($audio['titre'] ?? 'Sans titre') ?></span>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($meta['form_suggestions']['credits'] ?? $audio['credits'] ?? 'Artiste inconnu') ?>
                                            <?php if (!empty($analysis['codec']) && $analysis['codec'] !== 'unknown'): ?>
                                                <span class="badge bg-light text-dark ms-1"><?= strtoupper($analysis['codec']) ?></span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-soft-primary text-primary">
                                        <?= htmlspecialchars($audio['categorie'] ?? 'Non classé') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-dark">
                                        <i class="bx bx-time me-1"></i><?= format_duration_audio($duration) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $quality['class'] ?>">
                                        <?= $quality['label'] ?>
                                    </span>
                                </td>
                                <td class="text-center text-muted">
                                    <?= format_bytes_audio($audio['taille'] ?? $analysis['size'] ?? 0) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $source['class'] ?> bg-opacity-10 text-<?= $source['class'] ?>">
                                        <i class="bx bx-<?= ($source['text'] === 'FFmpeg') ? 'check-circle' : 'info-circle' ?> me-1"></i>
                                        <?= $source['text'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input status-toggle" type="checkbox" 
                                               data-id="<?= $audio['id_media'] ?>"
                                               <?= ($audio['est_actif'] ?? 0) ? 'checked' : '' ?>>
                                    </div>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-sm edit-audio" 
                                                data-id="<?= $audio['id_media'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#editModal">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-audio" 
                                                data-id="<?= $audio['id_media'] ?>"
                                                data-title="<?= htmlspecialchars($audio['titre'] ?? '') ?>">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($audios)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bx bx-music fs-1 mb-3 d-block"></i>
                                    Aucun audio dans la bibliothèque
                                    <br><small>Cliquez sur "Uploader" pour ajouter votre premier fichier</small>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL UPLOAD CHUNKED -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-spotify text-white">
                <h5 class="modal-title fw-bold"><i class="bx bx-upload me-2"></i>Uploader un Audio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Zone Upload -->
                <div id="dropZone" class="border-2 border-dashed rounded-3 p-5 text-center mb-4 bg-light">
                    <i class="bx bx-cloud-upload fs-1 text-spotify mb-3"></i>
                    <h5>Glissez-déposez votre fichier audio</h5>
                    <p class="text-muted mb-3">MP3, WAV, FLAC, AAC, OGG, M4A (max 500MB)</p>
                    <input type="file" id="audioFileInput" accept=".mp3,.wav,.flac,.aac,.ogg,.m4a,.wma,.aiff,.opus,.weba" class="d-none">
                    <button class="btn btn-spotify" onclick="document.getElementById('audioFileInput').click()">
                        <i class="bx bx-folder-open me-2"></i>Parcourir
                    </button>
                </div>

                <!-- Progress -->
                <div id="uploadProgress" class="d-none">
                    <div class="d-flex justify-content-between mb-2">
                        <span id="uploadStatus">Préparation...</span>
                        <span id="uploadPercent">0%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div id="progressBar" class="progress-bar bg-spotify progress-bar-striped progress-bar-animated" 
                             style="width: 0%"></div>
                    </div>
                    <div id="uploadDetails" class="small text-muted"></div>
                </div>

                <!-- Résultat Analyse -->
                <div id="analysisResult" class="d-none">
                    <div class="alert alert-success">
                        <i class="bx bx-check-circle me-2"></i>
                        Upload terminé! Métadonnées détectées automatiquement.
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <small class="text-muted">Durée détectée</small>
                                    <h4 id="detectedDuration" class="mb-0 text-spotify">--:--</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <small class="text-muted">Bitrate détecté</small>
                                    <h4 id="detectedBitrate" class="mb-0 text-spotify">--- kbps</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire Final -->
                    <form id="finalForm" action="<?= base_url('media/audio/Create') ?>" method="POST">
                        <input type="hidden" name="uploaded_file_path" id="uploadedFilePath">
                        <input type="hidden" name="auto_detected_data" id="autoDetectedData">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Titre *</label>
                            <input type="text" name="titre" id="audioTitle" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Catégorie</label>
                                <input type="text" name="categorie" id="audioCategory" class="form-control" list="categoriesList">
                                <datalist id="categoriesList">
                                    <?php foreach ($categories ?? [] as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Crédits / Artiste</label>
                                <input type="text" name="credits" id="audioCredits" class="form-control">
                            </div>
                        </div>

                        <!-- Miniature Selection -->
                        <div class="mb-3">
                            <label class="form-label">Miniature</label>
                            <div class="d-flex gap-2 flex-wrap" id="thumbnailOptions">
                                <!-- Généré dynamiquement -->
                            </div>
                            <input type="hidden" name="thumbnail" id="selectedThumbnail">
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="est_actif" value="1" checked>
                            <label class="form-check-label">Actif</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-spotify flex-fill">
                                <i class="bx bx-save me-2"></i>Enregistrer l'audio
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bx bx-edit me-2"></i>Modifier l'Audio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" action="<?= base_url('media/audio/Update') ?>" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="editId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Titre *</label>
                        <input type="text" name="titre" id="editTitle" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Catégorie</label>
                            <input type="text" name="categorie" id="editCategory" class="form-control" list="categoriesList">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date_media" id="editDate" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Crédits</label>
                        <input type="text" name="credits" id="editCredits" class="form-control">
                    </div>

                    <div class="d-flex gap-3 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="est_actif" id="editActive" value="1">
                            <label class="form-check-label">Actif</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_for_whatsapp" id="editWhatsapp" value="1">
                            <label class="form-check-label">WhatsApp</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_for_website" id="editWebsite" value="1">
                            <label class="form-check-label">Website</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSS Personnalisé -->
<style>
.bg-spotify { background-color: #1DB954 !important; }
.text-spotify { color: #1DB954 !important; }
.btn-spotify { background-color: #1DB954; border-color: #1DB954; color: white; }
.btn-spotify:hover { background-color: #1ed760; border-color: #1ed760; color: white; }
.border-dashed { border-style: dashed !important; border-color: #dee2e6; }
#dropZone.dragover { border-color: #1DB954 !important; background-color: #f0f9f4 !important; }
.bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
</style>

<!-- JavaScript Upload Chunked -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('audioFileInput');
    const progressDiv = document.getElementById('uploadProgress');
    const analysisDiv = document.getElementById('analysisResult');
    const progressBar = document.getElementById('progressBar');
    const uploadPercent = document.getElementById('uploadPercent');
    const uploadStatus = document.getElementById('uploadStatus');
    const uploadDetails = document.getElementById('uploadDetails');

    let currentUploadId = null;
    let chunkSize = 1.5 * 1024 * 1024; // 1.5MB par défaut

    // Drag & Drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) handleFile(e.target.files[0]);
    });

    async function handleFile(file) {
        // Validation
        const validExts = ['mp3','wav','flac','aac','ogg','m4a','wma','aiff','opus','weba'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!validExts.includes(ext)) {
            alert('Format non supporté: ' + ext);
            return;
        }
        if (file.size > 500 * 1024 * 1024) {
            alert('Fichier trop grand (max 500MB)');
            return;
        }

        // Init Upload
        dropZone.classList.add('d-none');
        progressDiv.classList.remove('d-none');
        uploadStatus.textContent = 'Initialisation...';

        try {
            // 1. Init session
            const initForm = new FormData();
            initForm.append('file_name', file.name);
            initForm.append('file_size', file.size);

            const initRes = await fetch('<?= base_url('media/audio/initUpload') ?>', {
                method: 'POST',
                body: initForm
            });
            const initData = await initRes.json();

            if (!initData.success) throw new Error(initData.message);

            currentUploadId = initData.upload_id;
            chunkSize = initData.chunk_size;
            const totalChunks = initData.total_chunks;

            uploadDetails.textContent = `${totalChunks} chunks de ${(chunkSize/1024/1024).toFixed(1)}MB`;

            // 2. Upload chunks
            for (let i = 0; i < totalChunks; i++) {
                const start = i * chunkSize;
                const end = Math.min(start + chunkSize, file.size);
                const chunk = file.slice(start, end);

                const chunkForm = new FormData();
                chunkForm.append('upload_id', currentUploadId);
                chunkForm.append('chunk_index', i);
                chunkForm.append('chunk', chunk);

                const chunkRes = await fetch('<?= base_url('media/audio/uploadChunk') ?>', {
                    method: 'POST',
                    body: chunkForm
                });
                const chunkData = await chunkRes.json();

                if (!chunkData.success) throw new Error(chunkData.message);

                const percent = ((i + 1) / totalChunks * 100).toFixed(1);
                progressBar.style.width = percent + '%';
                uploadPercent.textContent = percent + '%';
                uploadStatus.textContent = `Upload chunk ${i + 1}/${totalChunks}`;
            }

            // 3. Complete
            uploadStatus.textContent = 'Assemblage et analyse...';
            const completeForm = new FormData();
            completeForm.append('upload_id', currentUploadId);

            const completeRes = await fetch('<?= base_url('media/audio/completeUpload') ?>', {
                method: 'POST',
                body: completeForm
            });
            const completeData = await completeRes.json();

            if (!completeData.success) throw new Error(completeData.message);

            // Afficher résultat
            showAnalysisResult(completeData.data);

        } catch (error) {
            alert('Erreur: ' + error.message);
            resetUpload();
        }
    }

    function showAnalysisResult(data) {
        progressDiv.classList.add('d-none');
        analysisDiv.classList.remove('d-none');

        const analysis = data.analysis;
        document.getElementById('detectedDuration').textContent = analysis.duration_formatted || '--:--';
        document.getElementById('detectedBitrate').textContent = analysis.bitrate ? Math.round(analysis.bitrate/1000) + ' kbps' : '---';
        
        document.getElementById('uploadedFilePath').value = data.original_file;
        document.getElementById('autoDetectedData').value = JSON.stringify(data);
        
        // Suggestions formulaire
        if (data.form_suggestions) {
            document.getElementById('audioTitle').value = data.form_suggestions.titre || '';
            document.getElementById('audioCredits').value = data.form_suggestions.credits || '';
            document.getElementById('audioCategory').value = data.form_suggestions.categorie || '';
        }

        // Thumbnails
        const thumbContainer = document.getElementById('thumbnailOptions');
        thumbContainer.innerHTML = '';
        
        if (data.thumbnails) {
            if (data.thumbnails.cover) addThumbOption(data.thumbnails.cover, 'Cover extraite');
            if (data.thumbnails.generated) addThumbOption(data.thumbnails.generated, 'Waveform générée');
        }
        
        // Upload custom thumbnail option
        const customDiv = document.createElement('div');
        customDiv.className = 'border rounded p-2 text-center cursor-pointer';
        customDiv.style.cssText = 'width: 100px; cursor: pointer;';
        customDiv.innerHTML = '<i class="bx bx-plus fs-3"></i><br><small>Personnalisée</small>';
        customDiv.onclick = () => document.getElementById('customThumbInput').click();
        thumbContainer.appendChild(customDiv);
    }

    function addThumbOption(path, label) {
        const div = document.createElement('div');
        div.className = 'border rounded p-1 position-relative thumbnail-option';
        div.style.cssText = 'width: 100px; cursor: pointer;';
        div.innerHTML = `
            <img src="<?= base_url() ?>${path}" class="w-100 rounded" style="height: 80px; object-fit: cover;">
            <small class="d-block text-center text-muted mt-1">${label}</small>
        `;
        div.onclick = function() {
            document.querySelectorAll('.thumbnail-option').forEach(el => el.classList.remove('border-primary', 'border-2'));
            this.classList.add('border-primary', 'border-2');
            document.getElementById('selectedThumbnail').value = path;
        };
        document.getElementById('thumbnailOptions').appendChild(div);
    }

    function resetUpload() {
        dropZone.classList.remove('d-none');
        progressDiv.classList.add('d-none');
        analysisDiv.classList.add('d-none');
        progressBar.style.width = '0%';
        fileInput.value = '';
        currentUploadId = null;
    }

    // Edit modal population
    document.querySelectorAll('.edit-audio').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            // Fetch audio data via AJAX or use data attributes
            fetch(`<?= base_url('media/audio/getAudio/') ?>${id}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('editId').value = data.id_media;
                    document.getElementById('editTitle').value = data.titre;
                    document.getElementById('editDescription').value = data.description || '';
                    document.getElementById('editCategory').value = data.categorie || '';
                    document.getElementById('editDate').value = data.date_media || '';
                    document.getElementById('editCredits').value = data.credits || '';
                    document.getElementById('editActive').checked = data.est_actif == 1;
                    document.getElementById('editWhatsapp').checked = data.is_for_whatsapp == 1;
                    document.getElementById('editWebsite').checked = data.is_for_website == 1;
                });
        });
    });

    // Delete confirmation
    document.querySelectorAll('.delete-audio').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const title = this.dataset.title;
            if (confirm(`Supprimer "${title}" ?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('media/audio/Delete') ?>';
                form.innerHTML = `<input type="hidden" name="id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Status toggle AJAX
    document.querySelectorAll('.status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const id = this.dataset.id;
            const status = this.checked ? 1 : 0;
            
            fetch('<?= base_url('media/audio/ChangeStatus') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}&est_actif=${status}`
            });
        });
    });

    // Filter & Search
    document.getElementById('filterCategory')?.addEventListener('change', function() {
        const cat = this.value;
        document.querySelectorAll('#audioTable tbody tr').forEach(row => {
            if (!cat || row.dataset.category === cat) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    document.getElementById('searchAudio')?.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#audioTable tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>