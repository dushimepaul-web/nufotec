<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('format_duration')) {
    function format_duration($seconds) {
        if (empty($seconds) || $seconds <= 0) return '0:00';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
    }
}
if (!function_exists('format_bytes')) {
    function format_bytes($bytes, $decimals = 2) {
        if (empty($bytes) || $bytes === 0) return '0 B';
        $k = 1024; $sizes = ['B','KB','MB','GB','TB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), $decimals) . ' ' . $sizes[$i];
    }
}
if (!function_exists('get_type_badge')) {
    function get_type_badge($type) {
        $map = [
            'audio'    => ['label' => 'Audio', 'class' => 'success', 'icon' => 'bx bx-music'],
            'video'    => ['label' => 'Vidéo', 'class' => 'danger', 'icon' => 'bx bx-video'],
            'image'    => ['label' => 'Image', 'class' => 'primary', 'icon' => 'bx bx-image'],
            'document' => ['label' => 'Document', 'class' => 'warning', 'icon' => 'bx bx-file'],
            'link'     => ['label' => 'Lien', 'class' => 'info', 'icon' => 'bx bx-link'],
        ];
        return $map[$type] ?? ['label' => 'Média', 'class' => 'secondary', 'icon' => 'bx bx-file'];
    }
}
?>

<style>
.form-step, .field-group { display: none; }
.form-step.active, .field-group.active { display: block; }
.upload-zone { border: 2px dashed #adb5bd; border-radius: .75rem; padding: 40px 16px; text-align: center; cursor: pointer; transition: border-color .3s, background .3s; }
.upload-zone.dragover { border-color: #0d6efd; background: #f0f7ff; }
.upload-zone.has-file { border-color: #198754; background: #f0fff4; }
</style>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Médias</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Galerie Multimédia</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-xl">
                <div class="card card-outline card-success h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bx bx-music me-1"></i>Audios</h5>
                    </div>
                    <div class="card-body text-center py-3">
                        <h5 class="mb-0"><?= $statistics['audio'] ?? 0 ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <div class="card card-outline card-danger h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bx bx-video me-1"></i>Vidéos</h5>
                    </div>
                    <div class="card-body text-center py-3">
                        <h5 class="mb-0"><?= $statistics['video'] ?? 0 ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <div class="card card-outline card-primary h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bx bx-image me-1"></i>Images</h5>
                    </div>
                    <div class="card-body text-center py-3">
                        <h5 class="mb-0"><?= $statistics['image'] ?? 0 ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <div class="card card-outline card-warning h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bx bx-file me-1"></i>Documents</h5>
                    </div>
                    <div class="card-body text-center py-3">
                        <h5 class="mb-0"><?= $statistics['document'] ?? 0 ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <div class="card card-outline card-info h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bx bx-link me-1"></i>Liens</h5>
                    </div>
                    <div class="card-body text-center py-3">
                        <h5 class="mb-0"><?= $statistics['link'] ?? 0 ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Type Selector & Actions -->
        <div class="card card-outline card-primary mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="btn-group">
                        <a href="<?= base_url('admin/media') ?>" class="btn btn-sm <?= !$current_type ? 'btn-dark' : 'btn-outline-secondary' ?>">Tous</a>
                        <a href="<?= base_url('admin/media/index/audio') ?>" class="btn btn-sm <?= $current_type === 'audio' ? 'btn-success' : 'btn-outline-success' ?>"><i class="bx bx-music me-1"></i>Audio</a>
                        <a href="<?= base_url('admin/media/index/video') ?>" class="btn btn-sm <?= $current_type === 'video' ? 'btn-danger' : 'btn-outline-danger' ?>"><i class="bx bx-video me-1"></i>Vidéo</a>
                        <a href="<?= base_url('admin/media/index/image') ?>" class="btn btn-sm <?= $current_type === 'image' ? 'btn-primary' : 'btn-outline-primary' ?>"><i class="bx bx-image me-1"></i>Image</a>
                        <a href="<?= base_url('admin/media/index/document') ?>" class="btn btn-sm <?= $current_type === 'document' ? 'btn-warning' : 'btn-outline-warning' ?>"><i class="bx bx-file me-1"></i>Document</a>
                        <a href="<?= base_url('admin/media/index/link') ?>" class="btn btn-sm <?= $current_type === 'link' ? 'btn-info' : 'btn-outline-info' ?>"><i class="bx bx-link me-1"></i>Lien</a>
                    </div>
                    <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bx bx-plus me-1"></i>Ajouter un média
                    </button>
                </div>
            </div>
        </div>

        <!-- Recherche par nom de fichier -->
        <div class="card card-outline card-primary mb-4">
            <div class="card-body">
                <form method="get" action="<?= base_url('admin/media' . ($current_type ? '/index/' . $current_type : '')) ?>" class="d-flex flex-wrap align-items-center gap-2" id="mediaSearchForm">
                    <div class="input-group" style="max-width: 420px;">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" name="search_file" id="searchFileInput" class="form-control" value="<?= htmlspecialchars($search_file ?? '', ENT_QUOTES) ?>" placeholder="Rechercher par nom de fichier..." autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-outline-primary"><i class="bx bx-search me-1"></i>Rechercher</button>
                    <?php if (!empty($search_file)): ?>
                        <a href="<?= base_url('admin/media/index/' . ($current_type ?? '')) ?>" class="btn btn-outline-secondary" id="clearSearchBtn"><i class="bx bx-x me-1"></i>Effacer</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Media Grid -->
        <div id="mediaResults">
            <?php $this->load->view('_media_results', ['medias' => $medias, 'current_type' => $current_type, 'search_file' => $search_file ?? '', 'pagination' => $pagination ?? null]); ?>
        </div>
    </div>


<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Ajouter un média</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" method="post" action="<?= base_url('admin/media/Create') ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <input type="hidden" name="auto_detected_data" id="auto_detected_data">
                    <input type="hidden" name="uploaded_file_path" id="uploaded_file_path">

                    <!-- Step 1: Type Selection -->
                    <div class="form-step active" id="step1">
                        <h6 class="mb-3">Sélectionnez le type de média</h6>
                        <div class="row g-3">
                            <div class="col-4 col-sm">
                                <div class="card type-option text-center p-3 cursor-pointer" data-type="audio" style="cursor:pointer;border:2px solid transparent;">
                                    <div class="text-success fs-2"><i class="bx bx-music"></i></div>
                                    <small class="mt-2">Audio</small>
                                </div>
                            </div>
                            <div class="col-4 col-sm">
                                <div class="card type-option text-center p-3 cursor-pointer" data-type="video" style="cursor:pointer;border:2px solid transparent;">
                                    <div class="text-danger fs-2"><i class="bx bx-video"></i></div>
                                    <small class="mt-2">Vidéo</small>
                                </div>
                            </div>
                            <div class="col-4 col-sm">
                                <div class="card type-option text-center p-3 cursor-pointer" data-type="image" style="cursor:pointer;border:2px solid transparent;">
                                    <div class="text-primary fs-2"><i class="bx bx-image"></i></div>
                                    <small class="mt-2">Image</small>
                                </div>
                            </div>
                            <div class="col-4 col-sm">
                                <div class="card type-option text-center p-3 cursor-pointer" data-type="document" style="cursor:pointer;border:2px solid transparent;">
                                    <div class="text-warning fs-2"><i class="bx bx-file"></i></div>
                                    <small class="mt-2">Document</small>
                                </div>
                            </div>
                            <div class="col-4 col-sm">
                                <div class="card type-option text-center p-3 cursor-pointer" data-type="link" style="cursor:pointer;border:2px solid transparent;">
                                    <div class="text-info fs-2"><i class="bx bx-link"></i></div>
                                    <small class="mt-2">Lien</small>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="type" id="media_type" value="">
                    </div>

                    <!-- Step 2: File Upload (audio, video, image, document) -->
                    <div class="form-step" id="step2_file">
                        <h6 class="mb-3">Télécharger le fichier</h6>
                        <div class="upload-zone" id="dropzone">
                            <i class="bx bx-cloud-upload fs-1 text-muted"></i>
                            <h6 class="mt-2">Glissez-déposez votre fichier ici</h6>
                            <p class="text-muted small">ou cliquez pour parcourir</p>
                            <input type="file" id="fileInput" style="display:none" accept="">
                            <div id="uploadProgress" style="display:none;" class="mt-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted" id="progressText">0%</small>
                                    <small class="text-muted" id="uploadSpeed"></small>
                                </div>
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width:0%"></div>
                                </div>
                                <small class="text-muted mt-1 d-block" id="uploadStatus">Initialisation...</small>
                            </div>
                            <div id="uploadComplete" style="display:none;" class="mt-3 text-success">
                                <i class="bx bx-check-circle fs-3"></i>
                                <p class="mb-0">Fichier téléchargé avec succès</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Link fields -->
                    <div class="form-step" id="step2_link">
                        <h6 class="mb-3">Ajouter un lien</h6>
                        <div class="mb-3">
                            <label class="form-label">URL du lien</label>
                            <input type="url" name="lien" class="form-control" placeholder="https://example.com/video" id="link_url">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Miniature externe (optionnel)</label>
                            <input type="url" name="miniature_externe" class="form-control" placeholder="https://example.com/thumb.jpg">
                        </div>
                    </div>

                    <!-- Step 3: Details -->
                    <div class="form-step" id="step3">
                        <h6 class="mb-3">Détails du média</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Titre *</label>
                                <input type="text" name="titre" class="form-control" required maxlength="255" id="media_titre" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catégorie</label>
                                <select name="categorie" class="form-select">
                                    <option value="">Sélectionner</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2" autocomplete="off"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Crédits</label>
                                <input type="text" name="credits" class="form-control" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" name="date_media" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Miniature</label>
                                <div class="d-flex gap-2">
                                    <input type="text" name="thumbnail" class="form-control" id="media_thumbnail" placeholder="Chemin ou URL" readonly>
                                    <button type="button" class="btn btn-outline-primary" id="uploadThumbBtn"><i class="bx bx-upload"></i></button>
                                </div>
                                <input type="file" id="thumbFileInput" style="display:none" accept="image/*">
                                <div id="thumbPreview" class="mt-2" style="display:none;">
                                    <img src="" alt="preview" style="max-height:60px;border-radius:8px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-3 mt-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="est_actif" checked>
                                        <label class="form-check-label">Actif</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_for_website">
                                        <label class="form-check-label">Site web</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_for_whatsapp">
                                        <label class="form-check-label">WhatsApp</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="prevStep" style="display:none;"><i class="bx bx-arrow-back me-1"></i>Précédent</button>
                        <button type="button" class="btn btn-primary" id="nextStep">Suivant<i class="bx bx-arrow-forward ms-1"></i></button>
                        <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;"><i class="bx bx-check me-1"></i>Créer le média</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Modifier le média</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="<?= base_url('admin/media/Update') ?>" id="editForm">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="type" id="edit_type">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Titre *</label>
                            <input type="text" name="titre" class="form-control" required id="edit_titre">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Catégorie</label>
                            <select name="categorie" class="form-select" id="edit_categorie">
                                <option value="">Sélectionner</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" id="edit_description"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Crédits</label>
                            <input type="text" name="credits" class="form-control" id="edit_credits">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="date_media" class="form-control" id="edit_date_media">
                        </div>
                        <!-- Link-specific fields -->
                        <div class="col-12 edit-link-field" style="display:none;">
                            <label class="form-label">URL du lien</label>
                            <input type="url" name="lien" class="form-control" id="edit_lien">
                        </div>
                        <div class="col-md-6 edit-link-field" style="display:none;">
                            <label class="form-label">Miniature externe</label>
                            <input type="url" name="miniature_externe" class="form-control" id="edit_miniature_externe">
                        </div>
                        <div class="col-md-6 edit-file-field">
                            <label class="form-label">Miniature</label>
                            <div class="d-flex gap-2">
                                <input type="text" name="thumbnail" class="form-control" id="edit_thumbnail" readonly>
                                <button type="button" class="btn btn-outline-primary" id="editUploadThumbBtn"><i class="bx bx-upload"></i></button>
                            </div>
                            <input type="file" id="editThumbFileInput" style="display:none" accept="image/*">
                            <div id="editThumbPreview" class="mt-2" style="display:none;">
                                <img src="" alt="preview" style="max-height:60px;border-radius:8px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 mt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="est_actif" id="edit_est_actif">
                                    <label class="form-check-label">Actif</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_for_website" id="edit_is_for_website">
                                    <label class="form-check-label">Site web</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_for_whatsapp" id="edit_is_for_whatsapp">
                                    <label class="form-check-label">WhatsApp</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce média ? Cette action est irréversible.</p>
                <form method="post" action="<?= base_url('admin/media/Delete') ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Modal (Player) -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="viewTitle">Lecture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="viewContent"></div>
            </div>
        </div>
    </div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/backend/plugins/toastr/toastr.min.css">
<script src="<?= base_url() ?>assets/backend/plugins/toastr/toastr.min.js"></script>
<script>
let currentStep = 1;
let selectedType = '';
let uploadManager = null;

$(document).ready(function() {
    // Type selection
    $('.type-option').click(function() {
        $('.type-option').css('border-color', 'transparent');
        $(this).css('border-color', $(this).find('.fs-2').css('color'));
        selectedType = $(this).data('type');
        $('#media_type').val(selectedType);
        $('#nextStep').show().text('Suivant');
        enableStep2();
    });

    function enableStep2() {
        goToStep(2);
        if (selectedType === 'link') {
            $('#step2_file').removeClass('active');
            $('#step2_link').addClass('active');
            $('#nextStep').hide();
            $('#submitBtn').show();
        } else {
            $('#step2_link').removeClass('active');
            $('#step2_file').addClass('active');
            setupFileUpload(selectedType);
        }
    }

    // Step navigation
    $('#nextStep').click(function() {
        if (currentStep === 1 && !selectedType) { toastr.error('Veuillez sélectionner un type de média'); return; }
        if (currentStep === 2 && selectedType !== 'link') { goToStep(3); return; }
        goToStep(currentStep + 1);
    });

    $('#prevStep').click(function() { goToStep(currentStep - 1); });

    function goToStep(step) {
        $('.form-step').removeClass('active');
        $('#step' + step).addClass('active');
        currentStep = step;
        $('#prevStep').toggle(step > 1);
        $('#nextStep').toggle(step === 1 || (step === 2 && selectedType !== 'link'));
        $('#submitBtn').toggle(step === 3 || (step === 2 && selectedType === 'link'));
    }

    // File upload setup
    function setupFileUpload(type) {
        const acceptMap = {
            audio: '.mp3,.wav,.flac,.aac,.ogg,.m4a,.wma,.aiff,.opus,.weba',
            video: '.mp4,.mov,.avi,.mkv,.webm,.m4v,.3gp,.flv,.wmv',
            image: '.jpg,.jpeg,.png,.gif,.webp,.svg',
            document: '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv'
        };
        $('#fileInput').attr('accept', acceptMap[type] || '*');
        $('#nextStep').hide();
        $('#submitBtn').hide();
        resetUploadUI();
    }

    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');

    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop', (e) => { e.preventDefault(); dropzone.classList.remove('dragover'); handleFile(e.dataTransfer.files[0]); });
    fileInput.addEventListener('change', () => { if (fileInput.files[0]) handleFile(fileInput.files[0]); });

    function handleFile(file) {
        if (!file) return;
        dropzone.classList.add('has-file');
        const maxSize = 4 * 1024 * 1024 * 1024;
        if (file.size > maxSize) { toastr.error('Fichier trop grand (max 4GB)'); return; }
        $('#progressText').text('0%');
        $('#progressBar').css('width', '0%');
        $('#uploadProgress').show();
        $('#uploadComplete').hide();
        startUpload(file);
    }

    async function startUpload(file) {
        const type = selectedType;
        try {
            const initResp = await $.ajax({
                url: '<?= base_url("admin/media/initUpload") ?>',
                method: 'POST',
                data: { file_name: file.name, file_size: file.size, type: type }
            });
            if (!initResp.success) { toastr.error(initResp.message); return; }

            const { upload_id, chunk_size, total_chunks } = initResp;
            let uploadedChunks = 0;

            for (let i = 0; i < total_chunks; i++) {
                const start = i * chunk_size;
                const end = Math.min(start + chunk_size, file.size);
                const chunk = file.slice(start, end);

                const checksum = await computeChecksum(chunk);
                const formData = new FormData();
                formData.append('upload_id', upload_id);
                formData.append('chunk_index', i);
                formData.append('total_chunks', total_chunks);
                formData.append('checksum', checksum);
                formData.append('chunk', chunk);

                const chunkResp = await $.ajax({
                    url: '<?= base_url("admin/media/uploadChunk") ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                });
                if (!chunkResp.success) { toastr.error(chunkResp.message); return; }
                uploadedChunks++;
                const pct = Math.round((uploadedChunks / total_chunks) * 100);
                $('#progressText').text(pct + '%');
                $('#progressBar').css('width', pct + '%');
                $('#uploadStatus').text(`Chunk ${uploadedChunks}/${total_chunks}`);
            }

            $('#progressText').text('Assemblage…');
            $('#uploadStatus').text('Traitement en cours');
            $('#progressBar').addClass('progress-bar-striped progress-bar-animated');

            const completeResp = await $.ajax({
                url: '<?= base_url("admin/media/completeUpload") ?>',
                method: 'POST',
                data: { upload_id: upload_id }
            });
            if (!completeResp.success) { toastr.error(completeResp.message); return; }

            $('#progressBar').removeClass('progress-bar-striped progress-bar-animated');
            $('#uploadProgress').hide();
            $('#uploadComplete').show();
            $('#uploaded_file_path').val(completeResp.data.original_file);
            $('#auto_detected_data').val(JSON.stringify(completeResp.data));

            if (completeResp.data.form_suggestions) {
                if (!document.getElementById('media_titre').value) {
                    $('#media_titre').val(completeResp.data.form_suggestions.titre);
                }
            }

            setTimeout(() => { goToStep(3); }, 500);
        } catch (err) {
            toastr.error('Erreur upload: ' + (err.responseJSON?.message || err.statusText));
        }
    }

    async function computeChecksum(blob) {
        const buffer = await blob.arrayBuffer();
        const hashBuffer = await crypto.subtle.digest('SHA-256', buffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    function resetUploadUI() {
        dropzone.classList.remove('has-file');
        $('#uploadProgress').hide();
        $('#uploadComplete').hide();
        $('#progressBar').css('width', '0%');
        $('#uploaded_file_path').val('');
        $('#auto_detected_data').val('');
    }

    // Thumbnail upload
    $('#uploadThumbBtn').click(() => $('#thumbFileInput').click());
    $('#thumbFileInput').change(async function() {
        const file = this.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('thumbnail_file', file);
        try {
            const resp = await $.ajax({
                url: '<?= base_url("admin/media/uploadThumbnail") ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false
            });
            if (resp.success) {
                $('#media_thumbnail').val(resp.file_path);
                $('#thumbPreview').show().find('img').attr('src', resp.preview_url);
                toastr.success('Miniature uploadée');
            }
        } catch (err) {
            toastr.error('Erreur upload miniature');
        }
    });

    // Thumbnail upload (edit modal)
    $('#editUploadThumbBtn').click(() => $('#editThumbFileInput').click());
    $('#editThumbFileInput').change(async function() {
        const file = this.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('thumbnail_file', file);
        try {
            const resp = await $.ajax({
                url: '<?= base_url("admin/media/uploadThumbnail") ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false
            });
            if (resp.success) {
                $('#edit_thumbnail').val(resp.file_path);
                $('#editThumbPreview').show().find('img').attr('src', resp.preview_url);
                toastr.success('Miniature uploadée');
            }
        } catch (err) {
            toastr.error('Erreur upload miniature');
        }
    });

    // Live search par nom de fichier
    const searchInput = document.getElementById('searchFileInput');
    const searchForm = document.getElementById('mediaSearchForm');
    const mediaResults = document.getElementById('mediaResults');
    let searchTimer = null;
    let searchReqSeq = 0;

    async function runLiveSearch(page) {
        const q = searchInput.value.trim();
        const type = '<?= e($current_type ?? '') ?>';
        const seq = ++searchReqSeq;
        const url = '<?= base_url("admin/media/searchAjax") ?>';
        const params = { search_file: q, type: type, page: page || 1 };
        const searchBase = '<?= base_url('admin/media' . ($current_type ? '/index/' . $current_type : '')) ?>';

        try {
            const resp = await $.ajax({ url: url, data: params, dataType: 'json', method: 'GET' });
            if (seq !== searchReqSeq) return;
            if (resp && resp.success && resp.html) {
                mediaResults.innerHTML = resp.html;
                bindMediaActions();
                history.replaceState(null, '', searchBase + '?' + $.param(params));
            } else {
                toastr.error('Erreur lors de la recherche');
            }
        } catch (err) {
            if (seq === searchReqSeq) {
                const detail = err.status ? ' (HTTP ' + err.status + ')' : '';
                toastr.error('Erreur lors de la recherche' + detail);
            }
        }
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => runLiveSearch(1), 300);
    });

    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        runLiveSearch(1);
    });

    // Reprise des clics de pagination rendus par AJAX
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('.page-link');
        if (!pageLink || !mediaResults.contains(pageLink)) return;
        e.preventDefault();
        const href = pageLink.getAttribute('href');
        const m = href.match(/[?&]page=(\d+)/);
        runLiveSearch(m ? parseInt(m[1], 10) : 1);
    });

    function bindMediaActions() {
        $('.delete-media').off('click').on('click', function() {
            $('#delete_id').val($(this).data('id'));
        });
        $('.edit-media').off('click').on('click', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            $('#editThumbPreview').hide().find('img').attr('src', '');
            $.getJSON('<?= base_url("admin/media/getJson/") ?>' + id, function(resp) {
                if (!resp.success || !resp.data) return;
                const d = resp.data;
                $('#edit_id').val(d.id);
                $('#edit_type').val(type);
                $('#edit_titre').val(d.titre || '');
                $('#edit_description').val(d.description || '');
                $('#edit_credits').val(d.credits || '');
                $('#edit_date_media').val(d.date_media || '');
                $('#edit_thumbnail').val(d.miniature || '');
                $('#edit_categorie').val(d.categorie || '');
                $('#edit_est_actif').prop('checked', d.est_actif == 1);
                $('#edit_is_for_website').prop('checked', d.is_for_website == 1);
                $('#edit_is_for_whatsapp').prop('checked', d.is_for_whatsapp == 1);
                if (type === 'link') {
                    $('.edit-link-field').show();
                    $('#edit_lien').val(d.lien || '');
                    $('#edit_miniature_externe').val(d.miniature_externe || '');
                } else {
                    $('.edit-link-field').hide();
                }
            });
        });
        $('.view-media').off('click').on('click', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            $.getJSON('<?= base_url("admin/media/getJson/") ?>' + id, function(resp) {
                if (!resp.success || !resp.data) return;
                const d = resp.data;
                $('#viewTitle').text(d.titre);
                let html = '';
                if (type === 'audio') {
                    html = '<img src="' + d.thumb + '" class="rounded mb-3" style="max-height:200px;"><br>' +
                           '<audio controls style="width:100%;"><source src="' + d.stream_url + '" type="audio/mpeg"></audio>' +
                           '<p class="mt-2 mb-0"><small>Durée: ' + d.duree + ' | ' + (d.bitrate || '') + '</small></p>';
                } else if (type === 'video') {
                    html = '<video controls style="width:100%;max-height:400px;"><source src="' + d.stream_url + '" type="video/mp4"></video>' +
                           '<p class="mt-2 mb-0"><small>Durée: ' + d.duree + ' | ' + (d.resolution || '') + '</small></p>';
                } else if (type === 'image') {
                    html = '<a href="' + d.file_url + '" target="_blank"><img src="' + d.file_url + '" class="img-fluid rounded" style="max-height:70vh;" onerror="this.parentElement.innerHTML=\'<i class=\\\'bx bx-image fs-1 text-muted\\\'></i><p>Image indisponible</p>\'"></a>' +
                           '<p class="mt-2 mb-0"><small>MIME: ' + (d.mime_type || 'N/A') + '</small></p>';
                } else if (type === 'document') {
                    const ext = d.file_url ? d.file_url.split('.').pop().toUpperCase() : 'FICHIER';
                    html = '<div class="text-center py-4"><i class="bx bxs-file-pdf fs-1 text-warning"></i>' +
                           '<h6 class="mt-2">' + ext + '</h6>' +
                           '<a href="' + d.file_url + '" target="_blank" class="btn btn-warning mt-3"><i class="bx bx-download me-1"></i>Ouvrir le document</a>' +
                           '<p class="mt-2 mb-0"><small>MIME: ' + (d.mime_type || 'N/A') + '</small></p></div>';
                } else if (type === 'link') {
                    html = '<div class="text-center py-4"><i class="bx bx-link fs-1 text-info"></i>' +
                           '<h6 class="mt-2 text-truncate">' + (d.lien || '') + '</h6>' +
                           '<a href="' + (d.lien || '') + '" target="_blank" class="btn btn-info mt-3" rel="noopener noreferrer"><i class="bx bx-link-external me-1"></i>Visiter le lien</a></div>';
                }
                $('#viewContent').html(html);
            });
        });
        $('.toggle-field').off('change').on('change', function() {
            const id = $(this).data('id');
            const field = $(this).data('field');
            const value = this.checked ? 1 : 0;
            $.post('<?= base_url("admin/media/toggleField") ?>', { id: id, field: field, value: value }, function(resp) {
                if (!resp.success) toastr.error('Erreur');
            }, 'json');
        });
    }

    bindMediaActions();

    // Delete modal
    $('.delete-media').click(function() {
        $('#delete_id').val($(this).data('id'));
    });
});
</script>