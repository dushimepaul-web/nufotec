<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3">Administration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Configurations Système</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bx bx-plus me-2"></i>Nouvelle Configuration
            </button>
        </div>
    </div>

    <!-- Messages Flash Globaux -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-error-circle fs-5 me-2"></i><?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Toast Container pour notifications individuelles -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="liveToast" class="toast align-items-center border-0 bg-white shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center" id="toastMessage">
                    <i class="bx bx-check-circle text-success me-2 fs-5"></i>
                    <span>Action réussie !</span>
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Configurations par catégorie -->
    <div class="row" id="configurationsContainer">
        <?php 
        $category_icons = [
            'agf_identity' => 'bx-id-card',
            'agf_facility' => 'bx-building-house',
            'agf_finance' => 'bx-money',
            'contact' => 'bx-phone',
            'ui' => 'bx-layout',
            'system' => 'bx-cog',
            'general' => 'bx-slider',
            'media' => 'bx-image'
        ];
        
        $category_colors = [
            'agf_identity' => 'primary',
            'agf_facility' => 'success',
            'agf_finance' => 'warning',
            'contact' => 'info',
            'ui' => 'secondary',
            'system' => 'danger',
            'general' => 'dark',
            'media' => 'purple'
        ];

        foreach ($configurations as $categorie => $configs): 
            $icon = $category_icons[$categorie] ?? 'bx-folder';
            $color = $category_colors[$categorie] ?? 'primary';
            $category_label = $categories[$categorie] ?? $categorie;
        ?>
        
        <div class="col-12 mb-4 config-category" data-category="<?= $categorie ?>">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-<?= $color ?> bg-gradient text-white py-3">
                    <div class="d-flex align-items-center">
                        <i class="bx <?= $icon ?> fs-4 me-2"></i>
                        <h5 class="mb-0 fw-bold"><?= htmlspecialchars($category_label) ?></h5>
                        <span class="badge bg-white text-<?= $color ?> ms-auto rounded-pill"><?= count($configs) ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($configs as $config): 
                            $is_image = ($config['type'] === 'image');
                            $image_url = $is_image && !empty($config['valeur']) 
                                ? base_url('attachments/Configurations/' . $config['valeur']) 
                                : '';
                        ?>
                        <div class="list-group-item p-3 configuration-item" data-id="<?= $config['id'] ?>" data-type="<?= $config['type'] ?>" id="config-<?= $config['id'] ?>">
                            <div class="row align-items-center">
                                <!-- Label -->
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label class="fw-bold text-dark mb-1 d-block">
                                        <?= htmlspecialchars(str_replace('_', ' ', $config['cle'])) ?>
                                    </label>
                                    <?php if (!empty($config['description'])): ?>
                                        <small class="text-muted d-block text-truncate" title="<?= htmlspecialchars($config['description']) ?>">
                                            <i class="bx bx-info-circle me-1"></i><?= htmlspecialchars($config['description']) ?>
                                        </small>
                                    <?php endif; ?>
                                    <span class="badge bg-light text-dark border mt-1">
                                        <i class="bx <?= $is_image ? 'bx-image' : 'bx-tag' ?> me-1"></i><?= $config['type'] ?>
                                    </span>
                                </div>
                                
                                <!-- Input/Value -->
                                <div class="col-md-6 mb-2 mb-md-0">
                                    
                                    <?php if ($is_image): ?>
                                        <!-- Upload d'image -->
                                        <div class="image-upload-container">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="image-preview-wrapper" style="width: 80px; height: 80px; border: 2px dashed #ddd; border-radius: 8px; overflow: hidden; position: relative; background: #f8f9fa;">
                                                    <img src="<?= $image_url ?>" 
                                                         id="preview-<?= $config['id'] ?>"
                                                         style="width: 100%; height: 100%; object-fit: contain;"
                                                         onerror="this.src='<?= base_url('assets/images/no-image.png') ?>'"
                                                         alt="Preview">
                                                    <!-- Overlay de chargement -->
                                                    <div class="upload-overlay d-none" id="overlay-<?= $config['id'] ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;">
                                                        <div class="spinner-border spinner-border-sm text-white" role="status"></div>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <input type="file" 
                                                           class="form-control form-control-sm image-input" 
                                                           data-id="<?= $config['id'] ?>"
                                                           accept="image/*,.ico,.svg">
                                                    <small class="text-muted d-block mt-1">
                                                        <i class="bx bx-upload me-1"></i>Cliquez pour changer l'image
                                                    </small>
                                                    <?php if (!empty($config['valeur'])): ?>
                                                        <small class="text-success d-block text-truncate" style="max-width: 200px;" id="filename-<?= $config['id'] ?>" title="<?= $config['valeur'] ?>">
                                                            <?= $config['valeur'] ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted d-block" id="filename-<?= $config['id'] ?>">Aucune image</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    <?php elseif ($config['type'] == 'boolean'): ?>
                                        <!-- Toggle Switch -->
                                        <div class="form-check form-switch">
                                            <input class="form-check-input config-input" 
                                                   type="checkbox" 
                                                   data-id="<?= $config['id'] ?>"
                                                   data-type="boolean"
                                                   id="checkbox-<?= $config['id'] ?>"
                                                   <?= ($config['valeur'] == '1' || $config['valeur'] == 'true') ? 'checked' : '' ?>>
                                            <label class="form-check-label ms-2 status-label" for="checkbox-<?= $config['id'] ?>">
                                                <?= ($config['valeur'] == '1' || $config['valeur'] == 'true') ? '<span class="text-success"><i class="bx bx-check me-1"></i>Activé</span>' : '<span class="text-danger"><i class="bx bx-x me-1"></i>Désactivé</span>' ?>
                                            </label>
                                            <!-- Indicateur inline -->
                                            <span class="ms-2 d-none" id="inline-status-<?= $config['id'] ?>">
                                                <i class="bx bx-loader-alt bx-spin text-primary"></i>
                                            </span>
                                            <span class="ms-2 d-none text-success" id="inline-success-<?= $config['id'] ?>">
                                                <i class="bx bx-check"></i>
                                            </span>
                                        </div>
                                        
                                    <?php elseif ($config['type'] == 'json'): ?>
                                        <!-- Textarea JSON -->
                                        <textarea class="form-control config-input font-monospace" 
                                                  data-id="<?= $config['id'] ?>"
                                                  data-type="json"
                                                  id="input-<?= $config['id'] ?>"
                                                  rows="3"
                                                  style="font-size: 0.8rem;"><?= htmlspecialchars($config['valeur'] ?? '') ?></textarea>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small class="text-muted">Format JSON valide requis</small>
                                            <button class="btn btn-sm btn-success save-btn" data-id="<?= $config['id'] ?>">
                                                <i class="bx bx-save me-1"></i><span class="btn-text">Sauvegarder</span>
                                                <span class="d-none btn-loading"><i class="bx bx-loader-alt bx-spin"></i></span>
                                            </button>
                                        </div>
                                        
                                    <?php elseif ($config['type'] == 'nombre'): ?>
                                        <!-- Input number -->
                                        <div class="input-group">
                                            <input type="number" 
                                                   class="form-control config-input" 
                                                   data-id="<?= $config['id'] ?>"
                                                   data-type="nombre"
                                                   id="input-<?= $config['id'] ?>"
                                                   value="<?= htmlspecialchars($config['valeur'] ?? '') ?>"
                                                   step="any">
                                            <button class="btn btn-outline-success save-btn" type="button" data-id="<?= $config['id'] ?>">
                                                <span class="btn-text"><i class="bx bx-save"></i></span>
                                                <span class="d-none btn-loading"><i class="bx bx-loader-alt bx-spin"></i></span>
                                            </button>
                                        </div>
                                        
                                    <?php else: ?>
                                        <!-- Input text standard -->
                                        <div class="input-group">
                                            <input type="text" 
                                                   class="form-control config-input" 
                                                   data-id="<?= $config['id'] ?>"
                                                   data-type="texte"
                                                   id="input-<?= $config['id'] ?>"
                                                   value="<?= htmlspecialchars($config['valeur'] ?? '') ?>">
                                            <button class="btn btn-outline-success save-btn" type="button" data-id="<?= $config['id'] ?>">
                                                <span class="btn-text"><i class="bx bx-save"></i></span>
                                                <span class="d-none btn-loading"><i class="bx bx-loader-alt bx-spin"></i></span>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Info & Actions -->
                                <div class="col-md-2 text-md-end">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.7rem;">
                                        <i class="bx bx-time me-1"></i><span id="time-<?= $config['id'] ?>"><?= date('d/m/Y H:i', strtotime($config['updated_at'])) ?></span>
                                    </small>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" 
                                            data-id="<?= $config['id'] ?>" 
                                            data-cle="<?= htmlspecialchars($config['cle']) ?>"
                                            title="Supprimer">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Alertes individuelles pour chaque config -->
                            <div class="alert alert-success alert-sm py-2 px-3 mt-2 mb-0 d-none" id="alert-success-<?= $config['id'] ?>" style="font-size: 0.85rem;">
                                <i class="bx bx-check-circle me-1"></i><span class="alert-message">Sauvegardé avec succès !</span>
                            </div>
                            <div class="alert alert-danger alert-sm py-2 px-3 mt-2 mb-0 d-none" id="alert-error-<?= $config['id'] ?>" style="font-size: 0.85rem;">
                                <i class="bx bx-error-circle me-1"></i><span class="alert-message">Erreur lors de la sauvegarde</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Si vide -->
    <?php if (empty($configurations)): ?>
    <div class="text-center py-5" id="emptyState">
        <i class="bx bx-cog text-muted" style="font-size: 5rem;"></i>
        <h4 class="mt-3 text-muted">Aucune configuration trouvée</h4>
        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bx bx-plus me-2"></i>Créer la première configuration
        </button>
    </div>
    <?php endif; ?>

</div>


<!-- Modal Création -->
<div class="modal fade" id="createModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouvelle Configuration</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Configurations/create') ?>" method="POST" enctype="multipart/form-data" id="createForm">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Clé (identifiant unique) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="cle" id="createCle" required 
                                   placeholder="ex: site_logo, favicon_ico"
                                   pattern="[a-z0-9_]+"
                                   title="Lettres minuscules, chiffres et underscores uniquement">
                            <small class="text-muted">Format: site_logo, favicon_ico, banner_image</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-select" name="categorie" id="createCategorie" required>
                                <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $key === 'media' ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Type de donnée <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" required id="createType">
                                <?php foreach ($types as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Champ valeur texte/nombre (visible par défaut) -->
                        <div class="col-md-6" id="valueTextContainer">
                            <label class="form-label fw-bold">Valeur <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="valeur" id="createValueText">
                        </div>
                        
                        <!-- Champ upload image (caché par défaut) -->
                        <div class="col-md-6 d-none" id="valueImageContainer">
                            <label class="form-label fw-bold">Image/Logo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="valeur_image" id="createValueImage" accept="image/*,.ico,.svg">
                            <small class="text-muted">Formats: JPG, PNG, GIF, WEBP, SVG, ICO (max 2MB)</small>
                            <div class="mt-2 d-none" id="imagePreviewContainer">
                                <img id="createImagePreview" src="" style="max-height: 100px; max-width: 100%; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Description (optionnelle)</label>
                            <textarea class="form-control" name="description" id="createDescription" rows="2" placeholder="Description de cette configuration..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCancelCreate">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="btnCreate">
                        <span class="btn-text"><i class="bx bx-save me-2"></i>Créer la configuration</span>
                        <span class="d-none btn-loading"><i class="bx bx-loader-alt bx-spin me-2"></i>Création...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                <p class="mt-3">Supprimer <strong id="deleteCle"></strong> ?</p>
                <p class="text-danger small">Cette action est irréversible.</p>
            </div>
            <form action="<?= base_url('Configurations/delete') ?>" method="POST" id="deleteForm">
                <input type="hidden" name="id" id="deleteId">
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger" id="btnDelete">
                        <span class="btn-text"><i class="bx bx-trash me-2"></i>Supprimer</span>
                        <span class="d-none btn-loading"><i class="bx bx-loader-alt bx-spin me-2"></i>Suppression...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ========== JAVASCRIPT PUR (VANILLA JS) ==========

document.addEventListener('DOMContentLoaded', function() {
    
    // ========== TOAST NOTIFICATION ==========
    const toastEl = document.getElementById('liveToast');
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    
    function showToast(message, type = 'success') {
        const toastBody = document.getElementById('toastMessage');
        const iconClass = type === 'success' ? 'bx-check-circle text-success' : 'bx-error-circle text-danger';
        toastBody.innerHTML = `<i class="bx ${iconClass} me-2 fs-5"></i><span>${message}</span>`;
        toast.show();
    }
    
    // ========== SAUVEGARDE CHECKBOX (BOOLEAN) ==========
    document.querySelectorAll('.config-input[type="checkbox"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            const valeur = this.checked ? '1' : '0';
            const label = this.nextElementSibling;
            const statusIndicator = document.getElementById(`inline-status-${id}`);
            const successIndicator = document.getElementById(`inline-success-${id}`);
            
            // Loading state
            statusIndicator.classList.remove('d-none');
            successIndicator.classList.add('d-none');
            
            // Update UI optimistically
            if (this.checked) {
                label.innerHTML = '<span class="text-success"><i class="bx bx-check me-1"></i>Activé</span>';
            } else {
                label.innerHTML = '<span class="text-danger"><i class="bx bx-x me-1"></i>Désactivé</span>';
            }
            
            // AJAX request
            const formData = new FormData();
            formData.append('id', id);
            formData.append('valeur', valeur);
            
            fetch('<?= base_url('Configurations/update') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                statusIndicator.classList.add('d-none');
                
                if (data.success) {
                    successIndicator.classList.remove('d-none');
                    setTimeout(() => successIndicator.classList.add('d-none'), 2000);
                    showToast('Statut mis à jour avec succès');
                    updateTimestamp(id);
                } else {
                    showToast(data.message || 'Erreur lors de la mise à jour', 'error');
                    // Revert checkbox
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => {
                statusIndicator.classList.add('d-none');
                showToast('Erreur de connexion', 'error');
                console.error('Error:', error);
            });
        });
    });
    
    // ========== SAUVEGARDE BOUTON (TEXT, NOMBRE, JSON) ==========
    document.querySelectorAll('.save-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const input = document.getElementById(`input-${id}`);
            const valeur = input.value;
            const btnText = this.querySelector('.btn-text');
            const btnLoading = this.querySelector('.btn-loading');
            const alertSuccess = document.getElementById(`alert-success-${id}`);
            const alertError = document.getElementById(`alert-error-${id}`);
            
            // Loading state
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            this.disabled = true;
            
            // AJAX request
            const formData = new FormData();
            formData.append('id', id);
            formData.append('valeur', valeur);
            
            fetch('<?= base_url('Configurations/update') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
                this.disabled = false;
                
                if (data.success) {
                    showToast('Configuration sauvegardée avec succès');
                    alertSuccess.classList.remove('d-none');
                    alertSuccess.querySelector('.alert-message').textContent = 'Sauvegardé avec succès !';
                    setTimeout(() => alertSuccess.classList.add('d-none'), 3000);
                    updateTimestamp(id);
                } else {
                    showToast(data.message || 'Erreur lors de la sauvegarde', 'error');
                    alertError.classList.remove('d-none');
                    alertError.querySelector('.alert-message').textContent = data.message || 'Erreur lors de la sauvegarde';
                }
            })
            .catch(error => {
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
                this.disabled = false;
                showToast('Erreur de connexion au serveur', 'error');
                console.error('Error:', error);
            });
        });
    });
    


// ========== UPLOAD IMAGES ==========
document.querySelectorAll('.image-input').forEach(function(input) {
    input.addEventListener('change', function() {
        const id = this.getAttribute('data-id');
        const file = this.files[0];

        if (!file) return;

        // Validation taille
        if (file.size > 2 * 1024 * 1024) {
            showToast('L\'image ne doit pas dépasser 2MB', 'error');
            return;
        }

        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(`preview-${id}`).src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Loading state
        document.getElementById(`overlay-${id}`).classList.remove('d-none');
        this.disabled = true;

        // Tokens CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const csrfName = document.querySelector('meta[name="csrf-name"]')?.getAttribute('content');

        const formData = new FormData();
        formData.append('id', id);
        formData.append('image', file);
        if (csrfName && csrfToken) {
            formData.append(csrfName, csrfToken);
        }

        fetch('<?= base_url('Configurations/upload_image') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Réponse non JSON du serveur : ' + text.substring(0, 200));
            }
        })
        .then(data => {
            document.getElementById(`overlay-${id}`).classList.add('d-none');
            this.disabled = false;

            if (data.success) {
                showToast('Image uploadée avec succès');
                const filenameEl = document.getElementById(`filename-${id}`);
                filenameEl.textContent = data.filename;
                filenameEl.classList.remove('text-muted');
                filenameEl.classList.add('text-success');

                const alertSuccess = document.getElementById(`alert-success-${id}`);
                alertSuccess.classList.remove('d-none');
                alertSuccess.querySelector('.alert-message').textContent = 'Image mise à jour !';
                setTimeout(() => alertSuccess.classList.add('d-none'), 3000);
                updateTimestamp(id);
            } else {
                showToast(data.message || 'Erreur lors de l\'upload', 'error');
                document.getElementById(`preview-${id}`).src = '<?= base_url('assets/images/no-image.png') ?>';
            }
        })
        .catch(error => {
            document.getElementById(`overlay-${id}`).classList.add('d-none');
            this.disabled = false;
            showToast('Erreur : ' + error.message, 'error');
            console.error('Upload error:', error);
            document.getElementById(`preview-${id}`).src = '<?= base_url('assets/images/no-image.png') ?>';
        });
    });
});


    // ========== SUPPRESSION ==========
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const cle = this.getAttribute('data-cle');
            
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteCle').textContent = cle;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });
    
    // Loading state pour suppression
    document.getElementById('deleteForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnDelete');
        btn.querySelector('.btn-text').classList.add('d-none');
        btn.querySelector('.btn-loading').classList.remove('d-none');
        btn.disabled = true;
    });
    
    // ========== CRÉATION - GESTION DU TYPE IMAGE ==========
    const createType = document.getElementById('createType');
    const valueTextContainer = document.getElementById('valueTextContainer');
    const valueImageContainer = document.getElementById('valueImageContainer');
    const createValueText = document.getElementById('createValueText');
    const createValueImage = document.getElementById('createValueImage');
    
    createType.addEventListener('change', function() {
        const type = this.value;
        
        if (type === 'image') {
            // Masquer texte, afficher image
            valueTextContainer.classList.add('d-none');
            createValueText.removeAttribute('required');
            createValueText.value = '';
            
            valueImageContainer.classList.remove('d-none');
            createValueImage.setAttribute('required', 'required');
        } else {
            // Masquer image, afficher texte
            valueImageContainer.classList.add('d-none');
            createValueImage.removeAttribute('required');
            document.getElementById('imagePreviewContainer').classList.add('d-none');
            
            valueTextContainer.classList.remove('d-none');
            createValueText.setAttribute('required', 'required');
        }
    });
    
    // Preview image création
    document.getElementById('createValueImage').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('createImagePreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Loading state pour création
    document.getElementById('createForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnCreate');
        btn.querySelector('.btn-text').classList.add('d-none');
        btn.querySelector('.btn-loading').classList.remove('d-none');
        btn.disabled = true;
    });
    
    // Reset modal on close
    document.getElementById('btnCancelCreate').addEventListener('click', function() {
        document.getElementById('createForm').reset();
        valueTextContainer.classList.remove('d-none');
        valueImageContainer.classList.add('d-none');
        document.getElementById('imagePreviewContainer').classList.add('d-none');
    });
    
    // ========== UTILITAIRES ==========
    function updateTimestamp(id) {
        const now = new Date();
        const formatted = now.toLocaleString('fr-FR', { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric',
            hour: '2-digit', 
            minute: '2-digit'
        });
        document.getElementById(`time-${id}`).textContent = formatted;
    }
    
    // Auto-hide alerts globaux
    setTimeout(function() {
        document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
    
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>