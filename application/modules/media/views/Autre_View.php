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
                        <li class="breadcrumb-item active">Gestion Autre</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="bx bx-plus"></i> Nouveau
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="row mb-4 g-3">
            <div class="col-md-2 col-6">
                <div class="card bg-primary text-white text-center">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= $stats['total'] ?></h4>
                        <small>Total</small>
                    </div>
                </div>
            </div>
            <?php foreach ($type_configs as $key => $cfg): ?>
            <div class="col-md-2 col-6">
                <div class="card bg-<?= $cfg['color'] ?> text-white text-center">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= $stats['by_type'][$key] ?? 0 ?></h4>
                        <small><i class="bx <?= $cfg['icon'] ?>"></i> <?= $cfg['label'] ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="col-md-2 col-6">
                <div class="card bg-dark text-white text-center">
                    <div class="card-body py-3">
                        <!-- CORRECTION ICI : format_bytes() au lieu de $this->autre->formatBytes() -->
                        <h4 class="mb-0"><?= format_bytes($stats['total_size']) ?></h4>
                        <small>Stockage</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php $this->load->view('includes/backend/FlashMessages.php'); ?>

        <!-- Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="mainTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Aperçu</th>
                            <th>Titre</th>
                            <th>Taille</th>
                            <th>Statut</th>
                            <th>WA</th>
                            <th>Web</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): 
                        $type = $item['sous_type'] ?? 'other';
                        $cfg = $type_configs[$type] ?? $type_configs['other'];
                        $thumb = !empty($item['miniature']) 
                            ? (strpos($item['miniature'], 'http') === 0 ? $item['miniature'] : base_url($item['miniature']))
                            : base_url('assets/images/file-default.png');
                    ?>
                        <tr>
                            <td>
                                <span class="badge bg-<?= $cfg['color'] ?>">
                                    <i class="bx <?= $cfg['icon'] ?> me-1"></i><?= $cfg['label'] ?>
                                </span>
                            </td>
                            <td>
                                <img src="<?= $thumb ?>" class="rounded" style="width:60px;height:45px;object-fit:cover;">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($item['titre']) ?></strong>
                                <?php if ($item['categorie']): ?>
                                    <br><small class="badge bg-light text-dark"><?= htmlspecialchars($item['categorie']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- CORRECTION ICI : Utiliser taille_formatee depuis le contrôleur ou format_bytes() -->
                                <?= !empty($item['taille_formatee']) ? $item['taille_formatee'] : format_bytes($item['taille'] ?? 0) ?>
                            </td>
                            <td>
                                <a href="<?= base_url('autre/ChangeStatus') ?>" class="status-toggle" data-id="<?= $item['id_media'] ?>" data-status="<?= $item['est_actif'] ?>">
                                    <span class="badge bg-<?= $item['est_actif'] ? 'success' : 'secondary' ?>">
                                        <?= $item['est_actif'] ? 'Actif' : 'Inactif' ?>
                                    </span>
                                </a>
                            </td>
                            <td>
                                <input type="checkbox" class="form-check-input toggle-field" 
                                       data-id="<?= $item['id_media'] ?>" data-field="is_for_whatsapp"
                                       <?= $item['is_for_whatsapp'] ? 'checked' : '' ?>>
                            </td>
                            <td>
                                <input type="checkbox" class="form-check-input toggle-field" 
                                       data-id="<?= $item['id_media'] ?>" data-field="is_for_website"
                                       <?= $item['is_for_website'] ? 'checked' : '' ?>>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php if ($type === 'link'): ?>
                                            <li><a class="dropdown-item" href="<?= htmlspecialchars($item['lien']) ?>" target="_blank">
                                                <i class="bx bx-link-external me-2"></i>Ouvrir le lien
                                            </a></li>
                                        <?php elseif (!empty($item['fichier'])): ?>
                                            <li><a class="dropdown-item" href="<?= base_url($item['fichier']) ?>" download>
                                                <i class="bx bx-download me-2"></i>Télécharger
                                            </a></li>
                                        <?php endif; ?>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit<?= $item['id_media'] ?>">
                                            <i class="bx bx-edit me-2"></i>Modifier
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#del<?= $item['id_media'] ?>">
                                            <i class="bx bx-trash me-2"></i>Supprimer
                                        </a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="edit<?= $item['id_media'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-<?= $cfg['color'] ?> text-white">
                                        <h5 class="modal-title">Modifier <?= $cfg['label'] ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('autre/Update') ?>" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $item['id_media'] ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Titre *</label>
                                                <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($item['titre']) ?>" required>
                                            </div>

                                            <?php if ($type === 'link'): ?>
                                                <!-- LINK: Uniquement le champ URL -->
                                                <div class="mb-3">
                                                    <label class="form-label">URL *</label>
                                                    <input type="url" name="lien" class="form-control" value="<?= htmlspecialchars($item['lien']) ?>" required>
                                                    <small class="text-muted">La miniature sera extraite automatiquement</small>
                                                </div>

                                            <?php elseif ($type === 'texte'): ?>
                                                <div class="mb-3">
                                                    <label class="form-label">Contenu</label>
                                                    <textarea name="contenu_texte" class="form-control" rows="5"><?= htmlspecialchars($item['contenu_texte'] ?? '') ?></textarea>
                                                </div>

                                            <?php else: ?>
                                                <!-- Upload pour book, photo, other -->
                                                <div class="mb-3 file-upload-edit" data-max-size="<?= $cfg['max_size'] ?>" data-type="<?= $type ?>">
                                                    <label class="form-label">Nouveau fichier (laisser vide pour garder l'actuel)</label>
                                                    
                                                    <div class="upload-zone-edit p-3 border rounded bg-light text-center">
                                                        <input type="file" class="file-input-edit d-none" 
                                                               accept="<?= is_array($cfg['accept']) ? '.' . implode(',.', $cfg['accept']) : '' ?>">
                                                        <input type="hidden" name="uploaded_file_path" class="uploaded-path">
                                                        <input type="hidden" name="miniature" class="uploaded-thumb">
                                                        
                                                        <div class="upload-prompt-edit">
                                                            <i class="bx bx-cloud-upload fs-2 text-primary mb-2"></i>
                                                            <p class="mb-1">Cliquez ou déposez un fichier</p>
                                                            <!-- CORRECTION ICI : format_bytes() au lieu de $this->autre->formatBytes() -->
                                                            <small class="text-muted">Max: <?= format_bytes($cfg['max_size']) ?></small>
                                                        </div>
                                                        
                                                        <div class="upload-progress-edit d-none">
                                                            <div class="progress mb-2">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div>
                                                            </div>
                                                            <small class="text-muted status-text">Préparation...</small>
                                                            <button type="button" class="btn btn-sm btn-danger mt-2 cancel-edit">Annuler</button>
                                                        </div>
                                                        
                                                        <div class="upload-success-edit d-none alert alert-success py-2 mb-0">
                                                            <i class="bx bx-check-circle me-1"></i> <span class="filename"></span>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($item['fichier'])): ?>
                                                        <small class="text-muted d-block mt-2">
                                                            <i class="bx bx-file me-1"></i>Actuel: <?= basename($item['fichier']) ?> 
                                                            (<?= format_bytes($item['taille'] ?? 0) ?>)
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Catégorie</label>
                                                    <input type="text" name="categorie" class="form-control" list="catList" value="<?= htmlspecialchars($item['categorie'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Date</label>
                                                    <input type="date" name="date_media" class="form-control" value="<?= $item['date_media'] ?? '' ?>">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                                            </div>

                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="est_actif" value="1" <?= $item['est_actif'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Actif</label>
                                            </div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1" <?= $item['is_for_whatsapp'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">WhatsApp</label>
                                            </div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="is_for_website" value="1" <?= $item['is_for_website'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Site Web</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="del<?= $item['id_media'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Supprimer</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('autre/Delete') ?>" method="POST">
                                        <div class="modal-body text-center py-4">
                                            <i class="bx bx-error-circle text-danger display-4"></i>
                                            <h5 class="mt-3">Confirmer la suppression ?</h5>
                                            <p><?= htmlspecialchars($item['titre']) ?></p>
                                            <input type="hidden" name="id" value="<?= $item['id_media'] ?>">
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">Supprimer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nouvel Élément</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeCreate"></button>
            </div>
            <div class="modal-body p-0">
                
                <!-- Step 1: Select Type -->
                <div id="step1" class="p-4">
                    <h6 class="mb-3">Choisissez le type de contenu :</h6>
                    <div class="row g-3">
                        <?php foreach ($type_configs as $key => $cfg): ?>
                        <div class="col-md-4">
                            <div class="card type-card h-100 cursor-pointer hover-shadow" data-type="<?= $key ?>" onclick="selectType('<?= $key ?>')">
                                <div class="card-body text-center py-4">
                                    <i class="bx <?= $cfg['icon'] ?> display-4 text-<?= $cfg['color'] ?> mb-2"></i>
                                    <h6 class="mb-0"><?= $cfg['label'] ?></h6>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Step 2: Form -->
                <div id="step2" class="d-none">
                    <form id="createForm" action="<?= base_url('autre/Create') ?>" method="POST">
                        <input type="hidden" name="sous_type" id="selectedType">
                        
                        <div class="p-4 border-bottom bg-light">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="backToStep1()">
                                <i class="bx bx-arrow-back me-1"></i>Retour
                            </button>
                            <span class="ms-2 badge bg-primary" id="typeBadge">Type</span>
                        </div>

                        <div class="p-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Titre -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Titre *</label>
                                        <input type="text" name="titre" class="form-control form-control-lg" required>
                                    </div>

                                    <!-- LINK: Simple URL field -->
                                    <div id="linkFields" class="d-none">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">URL *</label>
                                            <input type="url" name="lien" class="form-control" placeholder="https://...">
                                            <div class="form-text">
                                                <i class="bx bx-info-circle me-1"></i>
                                                La miniature sera extraite automatiquement depuis YouTube, Vimeo, ou le favicon du site.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TEXTE -->
                                    <div id="texteFields" class="d-none">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Contenu</label>
                                            <textarea name="contenu_texte" class="form-control" rows="6" placeholder="Votre texte..."></textarea>
                                        </div>
                                    </div>

                                    <!-- FILE UPLOAD: book, photo, other -->
                                    <div id="fileFields" class="d-none">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Fichier *</label>
                                            
                                            <div id="dropZone" class="border rounded-3 p-5 text-center bg-light position-relative">
                                                <input type="file" id="fileInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer">
                                                <input type="hidden" name="uploaded_file_path" id="uploadedPath">
                                                <input type="hidden" name="miniature" id="uploadedThumb">
                                                
                                                <!-- Initial State -->
                                                <div id="uploadInitial">
                                                    <i class="bx bx-cloud-upload display-3 text-primary mb-3"></i>
                                                    <h5>Déposez votre fichier ici</h5>
                                                    <p class="text-muted mb-2">ou cliquez pour parcourir</p>
                                                    <div id="fileConstraints" class="badge bg-info">Chargement...</div>
                                                </div>

                                                <!-- Progress -->
                                                <div id="uploadProgress" class="d-none">
                                                    <div class="mb-3">
                                                        <div class="progress" style="height:25px;">
                                                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width:0%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-muted small mb-2">
                                                        <span id="uploadStatus">Préparation...</span>
                                                        <span id="uploadPercent">0%</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-muted small">
                                                        <span id="uploadSpeed">0 MB/s</span>
                                                        <span id="uploadChunks">0 / 0</span>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm mt-3" id="btnCancel">
                                                        <i class="bx bx-x me-1"></i>Annuler
                                                    </button>
                                                </div>

                                                <!-- Success -->
                                                <div id="uploadSuccess" class="d-none">
                                                    <i class="bx bx-check-circle display-3 text-success mb-2"></i>
                                                    <h5 class="text-success">Upload terminé !</h5>
                                                    <p id="fileInfo" class="mb-3"></p>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="resetUpload()">
                                                        <i class="bx bx-refresh me-1"></i>Changer de fichier
                                                    </button>
                                                </div>

                                                <!-- Error -->
                                                <div id="uploadError" class="d-none">
                                                    <i class="bx bx-error-circle display-3 text-danger mb-2"></i>
                                                    <h5 class="text-danger">Échec</h5>
                                                    <p id="errorMsg" class="mb-3"></p>
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="resetUpload()">Réessayer</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="bx bx-cog me-1"></i>Options</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Catégorie</label>
                                                <input type="text" name="categorie" class="form-control" list="catList">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date</label>
                                                <input type="date" name="date_media" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Crédits</label>
                                                <input type="text" name="credits" class="form-control">
                                            </div>
                                            
                                            <hr>
                                            
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="est_actif" value="1" checked>
                                                <label class="form-check-label">Publier immédiatement</label>
                                            </div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1">
                                                <label class="form-check-label">WhatsApp</label>
                                            </div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked>
                                                <label class="form-check-label">Site Web</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 border-top bg-light d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit" disabled>
                                <i class="bx bx-save me-1"></i>Créer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<datalist id="catList">
    <?php foreach ($categories as $cat): ?>
        <option value="<?= htmlspecialchars($cat) ?>">
    <?php endforeach; ?>
</datalist>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<script>
// Configuration
const CHUNK_SIZE = 1048576; // 1MB
const API_URL = '<?= base_url('autre/') ?>';
const CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
const CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';

const TYPE_CONFIGS = <?= json_encode($type_configs) ?>;

let currentUpload = {
    id: null,
    file: null,
    chunks: [],
    uploaded: [],
    controller: null,
    startTime: null
};

// ==================== TYPE SELECTION ====================

function selectType(type) {
    const cfg = TYPE_CONFIGS[type];
    
    document.getElementById('selectedType').value = type;
    document.getElementById('typeBadge').textContent = cfg.label;
    document.getElementById('typeBadge').className = 'ms-2 badge bg-' + cfg.color;
    
    document.getElementById('step1').classList.add('d-none');
    document.getElementById('step2').classList.remove('d-none');
    
    // Show/hide fields
    document.getElementById('linkFields').classList.toggle('d-none', type !== 'link');
    document.getElementById('texteFields').classList.toggle('d-none', type !== 'texte');
    document.getElementById('fileFields').classList.toggle('d-none', !cfg.has_file);
    
    // Update constraints display
    if (cfg.has_file) {
        const acceptText = cfg.accept === '*' ? 'Tous types' : cfg.accept.join(', ');
        document.getElementById('fileConstraints').innerHTML = 
            `Max: ${formatBytes(cfg.max_size)} | Types: ${acceptText}`;
        
        // Set accept attribute
        const accept = cfg.accept === '*' ? '' : cfg.accept.map(e => '.' + e).join(',');
        document.getElementById('fileInput').accept = accept;
    }
    
    // Enable submit for link/texte immediately, disable for file types
    document.getElementById('btnSubmit').disabled = cfg.has_file;
}

function backToStep1() {
    document.getElementById('step1').classList.remove('d-none');
    document.getElementById('step2').classList.add('d-none');
    resetUpload();
}

// ==================== UPLOAD ====================

document.getElementById('fileInput').addEventListener('change', function(e) {
    if (e.target.files.length) handleFile(e.target.files[0]);
});

const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-primary', 'bg-white');
});
dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-primary', 'bg-white');
});
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-primary', 'bg-white');
    if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
});

async function handleFile(file) {
    const type = document.getElementById('selectedType').value;
    const cfg = TYPE_CONFIGS[type];
    
    // Validation
    if (cfg.max_size > 0 && file.size > cfg.max_size) {
        showError(`Fichier trop grand. Maximum: ${formatBytes(cfg.max_size)}`);
        return;
    }
    
    if (cfg.accept !== '*' && cfg.accept) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!cfg.accept.includes(ext)) {
            showError(`Type non supporté. Acceptés: ${cfg.accept.join(', ')}`);
            return;
        }
    }
    
    currentUpload.file = file;
    currentUpload.chunks = createChunks(file);
    currentUpload.uploaded = [];
    currentUpload.controller = new AbortController();
    currentUpload.startTime = Date.now();
    
    showProgress();
    
    try {
        // 1. Init
        const init = await apiCall('initUpload', {
            file_name: file.name,
            file_size: file.size,
            sous_type: type
        });
        
        if (!init.success) throw new Error(init.message);
        
        currentUpload.id = init.upload_id;
        updateProgress(0, init.total_chunks);
        
        // 2. Upload chunks
        await uploadChunks(init.total_chunks);
        
        // 3. Complete
        const complete = await apiCall('completeUpload', {
            upload_id: currentUpload.id
        });
        
        if (!complete.success) throw new Error(complete.message);
        
        // Success
        showSuccess(complete);
        document.getElementById('uploadedPath').value = complete.file_path;
        document.getElementById('uploadedThumb').value = complete.miniature || '';
        document.getElementById('btnSubmit').disabled = false;
        
    } catch (err) {
        if (err.name !== 'AbortError') {
            console.error(err);
            showError(err.message || 'Erreur upload');
        }
    }
}

function createChunks(file) {
    const chunks = [];
    let pos = 0;
    while (pos < file.size) {
        const end = Math.min(pos + CHUNK_SIZE, file.size);
        chunks.push(file.slice(pos, end));
        pos = end;
    }
    return chunks;
}

async function uploadChunks(total) {
    const concurrency = 3; // 3 chunks en parallèle
    let index = 0;
    
    while (index < currentUpload.chunks.length) {
        const batch = [];
        for (let i = 0; i < concurrency && index < currentUpload.chunks.length; i++) {
            const chunkIndex = index;
            const chunk = currentUpload.chunks[index];
            
            batch.push(uploadChunk(chunkIndex, chunk).then(() => {
                currentUpload.uploaded.push(chunkIndex);
                updateProgress(currentUpload.uploaded.length, total);
            }));
            
            index++;
        }
        
        await Promise.all(batch);
    }
}

async function uploadChunk(index, chunk) {
    const formData = new FormData();
    formData.append('upload_id', currentUpload.id);
    formData.append('chunk_index', index);
    formData.append('chunk', chunk);
    
    const res = await fetch(API_URL + 'uploadChunk', {
        method: 'POST',
        body: formData,
        signal: currentUpload.controller.signal
    });
    
    const text = await res.text();
    let data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        throw new Error('Réponse serveur invalide');
    }
    
    if (!data.success) throw new Error(data.message);
    return data;
}

async function apiCall(endpoint, params) {
    const formData = new FormData();
    for (let k in params) formData.append(k, params[k]);
    formData.append(CSRF_NAME, CSRF_HASH);
    
    const res = await fetch(API_URL + endpoint, {
        method: 'POST',
        body: formData
    });
    
    const text = await res.text();
    try {
        return JSON.parse(text);
    } catch (e) {
        return { success: false, message: 'Réponse invalide: ' + text.substring(0, 100) };
    }
}

// ==================== UI ====================

function showProgress() {
    document.getElementById('uploadInitial').classList.add('d-none');
    document.getElementById('uploadProgress').classList.remove('d-none');
    document.getElementById('uploadSuccess').classList.add('d-none');
    document.getElementById('uploadError').classList.add('d-none');
}

function updateProgress(uploaded, total) {
    const pct = Math.round((uploaded / total) * 100);
    const elapsed = (Date.now() - currentUpload.startTime) / 1000;
    const bytesUploaded = uploaded * CHUNK_SIZE;
    const speed = elapsed > 0 ? bytesUploaded / elapsed : 0;
    
    document.getElementById('progressBar').style.width = pct + '%';
    document.getElementById('uploadPercent').textContent = pct + '%';
    document.getElementById('uploadStatus').textContent = `Upload: ${uploaded}/${total} morceaux`;
    document.getElementById('uploadSpeed').textContent = formatBytes(speed) + '/s';
    document.getElementById('uploadChunks').textContent = formatBytes(bytesUploaded) + ' / ' + formatBytes(currentUpload.file.size);
}

function showSuccess(data) {
    document.getElementById('uploadProgress').classList.add('d-none');
    document.getElementById('uploadSuccess').classList.remove('d-none');
    
    let html = `<strong>${escapeHtml(data.file_name)}</strong><br>
                <small class="text-muted">${data.file_size_formatted}</small>`;
    
    if (data.miniature) {
        html += `<br><small class="text-success"><i class="bx bx-image me-1"></i>Miniature générée</small>`;
    }
    if (data.pages) {
        html += `<br><small class="text-info"><i class="bx bx-file me-1"></i>${data.pages} pages</small>`;
    }
    if (data.dimensions) {
        html += `<br><small class="text-muted"><i class="bx bx-ruler me-1"></i>${data.dimensions}</small>`;
    }
    
    document.getElementById('fileInfo').innerHTML = html;
}

function showError(msg) {
    document.getElementById('uploadProgress').classList.add('d-none');
    document.getElementById('uploadError').classList.remove('d-none');
    document.getElementById('errorMsg').textContent = msg;
}

function resetUpload() {
    if (currentUpload.controller) currentUpload.controller.abort();
    
    currentUpload = { id: null, file: null, chunks: [], uploaded: [], controller: null, startTime: null };
    
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadedPath').value = '';
    document.getElementById('uploadedThumb').value = '';
    document.getElementById('uploadInitial').classList.remove('d-none');
    document.getElementById('uploadProgress').classList.add('d-none');
    document.getElementById('uploadSuccess').classList.add('d-none');
    document.getElementById('uploadError').classList.add('d-none');
    document.getElementById('btnSubmit').disabled = true;
}

document.getElementById('btnCancel').addEventListener('click', () => {
    if (currentUpload.id) {
        apiCall('cancelUpload', { upload_id: currentUpload.id });
    }
    resetUpload();
});

// Close modal protection
document.getElementById('closeCreate').addEventListener('click', function(e) {
    if (currentUpload.id && currentUpload.uploaded.length < currentUpload.chunks.length) {
        if (!confirm('Un upload est en cours. Annuler ?')) {
            e.preventDefault();
            return false;
        }
        apiCall('cancelUpload', { upload_id: currentUpload.id });
    }
});

// ==================== EDIT UPLOADS ====================

document.querySelectorAll('.file-upload-edit').forEach(container => {
    const zone = container.querySelector('.upload-zone-edit');
    const input = container.querySelector('.file-input-edit');
    const maxSize = parseInt(container.dataset.maxSize);
    
    zone.addEventListener('click', () => input.click());
    
    zone.addEventListener('dragover', (e) => {
        e.preventDefault();
        zone.classList.add('border-primary');
    });
    zone.addEventListener('dragleave', () => zone.classList.remove('border-primary'));
    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.classList.remove('border-primary');
        if (e.dataTransfer.files.length) handleEditFile(e.dataTransfer.files[0], container, maxSize);
    });
    
    input.addEventListener('change', (e) => {
        if (e.target.files.length) handleEditFile(e.target.files[0], container, maxSize);
    });
    
    container.querySelector('.cancel-edit')?.addEventListener('click', function() {
        container.querySelector('.upload-progress-edit').classList.add('d-none');
        container.querySelector('.upload-prompt-edit').classList.remove('d-none');
        input.value = '';
    });
});

async function handleEditFile(file, container, maxSize) {
    if (maxSize > 0 && file.size > maxSize) {
        alert('Fichier trop grand');
        return;
    }
    
    const prompt = container.querySelector('.upload-prompt-edit');
    const progress = container.querySelector('.upload-progress-edit');
    const success = container.querySelector('.upload-success-edit');
    const bar = container.querySelector('.progress-bar');
    const status = container.querySelector('.status-text');
    
    prompt.classList.add('d-none');
    progress.classList.remove('d-none');
    success.classList.add('d-none');
    
    const type = container.dataset.type;
    
    try {
        // Upload chunked
        const chunks = createChunks(file);
        const init = await apiCall('initUpload', {
            file_name: file.name,
            file_size: file.size,
            sous_type: type
        });
        
        if (!init.success) throw new Error(init.message);
        
        const uploadId = init.upload_id;
        
        for (let i = 0; i < chunks.length; i++) {
            const formData = new FormData();
            formData.append('upload_id', uploadId);
            formData.append('chunk_index', i);
            formData.append('chunk', chunks[i]);
            formData.append(CSRF_NAME, CSRF_HASH);
            
            await fetch(API_URL + 'uploadChunk', { method: 'POST', body: formData });
            
            const pct = Math.round(((i + 1) / chunks.length) * 100);
            bar.style.width = pct + '%';
            status.textContent = `Upload ${i + 1}/${chunks.length}`;
        }
        
        const complete = await apiCall('completeUpload', { upload_id: uploadId });
        if (!complete.success) throw new Error(complete.message);
        
        container.querySelector('.uploaded-path').value = complete.file_path;
        container.querySelector('.uploaded-thumb').value = complete.miniature || '';
        
        progress.classList.add('d-none');
        success.classList.remove('d-none');
        container.querySelector('.filename').textContent = file.name;
        
    } catch (err) {
        console.error(err);
        alert('Erreur upload: ' + err.message);
        progress.classList.add('d-none');
        prompt.classList.remove('d-none');
    }
}

// ==================== TOGGLES & STATUS ====================

// Status toggle
document.querySelectorAll('.status-toggle').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        if (!confirm('Changer le statut ?')) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = this.href;
        
        form.innerHTML = `
            <input type="hidden" name="id" value="${this.dataset.id}">
            <input type="hidden" name="est_actif" value="${this.dataset.status}">
            <input type="hidden" name="${CSRF_NAME}" value="${CSRF_HASH}">
        `;
        
        document.body.appendChild(form);
        form.submit();
    });
});

// Field toggles (WhatsApp, Web)
document.querySelectorAll('.toggle-field').forEach(toggle => {
    toggle.addEventListener('change', async function() {
        const id = this.dataset.id;
        const field = this.dataset.field;
        const value = this.checked ? 1 : 0;
        
        this.disabled = true;
        
        const res = await apiCall('toggleField', { id, field, value });
        
        if (!res.success) {
            this.checked = !this.checked;
            alert('Erreur mise à jour');
        }
        
        this.disabled = false;
    });
});

// ==================== UTILS ====================

function formatBytes(bytes) {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// DataTable
$(document).ready(function() {
    $('#mainTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
        order: [[0, 'desc']],
        pageLength: 25
    });
});
</script>

<style>
.type-card { transition: all 0.2s; cursor: pointer; border: 2px solid transparent; }
.type-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-color: var(--bs-primary); }
.cursor-pointer { cursor: pointer; }
.hover-shadow:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.upload-zone-edit { transition: all 0.2s; cursor: pointer; }
.upload-zone-edit:hover { border-color: var(--bs-primary) !important; background: white; }
</style>