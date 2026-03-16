<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Médias</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <?php if (isset($produit)): ?>
                        <li class="breadcrumb-item"><a href="<?= base_url('Produits') ?>">Produits</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($produit['nom_produit']) ?></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active">Galerie d'images</li>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#upload_modal">
                <i class="bx bx-upload"></i> <?= isset($produit) ? 'Ajouter des images' : 'Upload multiple' ?>
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-error-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!isset($produit)): ?>
    <!-- STATISTIQUES GLOBALES -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-white-50">Total Images</p>
                            <h3 class="mb-0"><?= $stats['total_images'] ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-images fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-white-50">Images Principales</p>
                            <h3 class="mb-0"><?= $stats['images_principales'] ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-star fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-white-50">Produits avec Images</p>
                            <h3 class="mb-0"><?= count($stats['produits_avec_images']) ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-package fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-dark-50">Moyenne/Produit</p>
                            <h3 class="mb-0"><?= $stats['moyenne_par_produit'] ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-calculator fs-1 text-dark-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($produit)): ?>
    <!-- INFO PRODUIT -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <img src="<?= base_url(!empty($produit['image_principale']) ? 'attachments/Produits/'.$produit['image_principale'] : 'assets/images/product-placeholder.png') ?>" 
                     class="rounded border me-3" style="width:80px; height:80px; object-fit:cover;">
                <div>
                    <h4 class="mb-1"><?= htmlspecialchars($produit['nom_produit']) ?></h4>
                    <p class="mb-0 text-muted"><?= count($images) ?> image(s) dans la galerie</p>
                </div>
                <div class="ms-auto">
                    <a href="<?= base_url('Produits') ?>" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-2"></i>Retour aux produits
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- GALERIE D'IMAGES -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-primary">
                    <i class="bx bx-images me-2"></i>
                    <?= isset($produit) ? 'Galerie du produit' : 'Toutes les images' ?>
                </h5>
                <?php if (!empty($images)): ?>
                <button class="btn btn-sm btn-outline-primary" onclick="enableSortMode()">
                    <i class="bx bx-sort me-2"></i>Réorganiser
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($images)): ?>
            <form action="<?= base_url('Produit_images/UpdateOrder') ?>" method="POST" id="sortForm">
                <div class="row g-4" id="imageGrid">
                    <?php foreach ($images as $img): 
                        $img_path = 'attachments/Produits/Images/' . $img['nom_fichier'];
                        $produit_nom = isset($produit) ? $produit['nom_produit'] : 'Produit #' . $img['id_produit'];
                    ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 image-item" data-id="<?= $img['id_image'] ?>">
                        <div class="card border-0 shadow-sm h-100 <?= $img['est_principale'] ? 'border-primary border-2' : '' ?> <?= !$img['est_active'] ? 'opacity-50' : '' ?>">
                            <div class="position-relative">
                                <img src="<?= base_url($img_path) ?>" 
                                     class="card-img-top" 
                                     style="height: 200px; object-fit: cover;"
                                     alt="<?= htmlspecialchars($img['alt_text'] ?: $produit_nom) ?>"
                                     onerror="this.src='<?= base_url('assets/images/product-placeholder.png') ?>'">
                                
                                <?php if ($img['est_principale']): ?>
                                <div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-warning text-dark"><i class="bx bx-star me-1"></i>Principale</span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="position-absolute top-0 end-0 m-2">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <?php if (!$img['est_principale']): ?>
                                            <li>
                                                <form action="<?= base_url('Produit_images/SetPrincipale') ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $img['id_image'] ?>">
                                                    <input type="hidden" name="id_produit" value="<?= $img['id_produit'] ?>">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bx bx-star text-warning me-2"></i>Définir principale
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#edit_<?= $img['id_image'] ?>">
                                                    <i class="bx bx-edit text-info me-2"></i>Modifier
                                                </a>
                                            </li>
                                            <li>
                                                <form action="<?= base_url('Produit_images/ToggleActive') ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $img['id_image'] ?>">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bx bx-<?= $img['est_active'] ? 'hide' : 'show' ?> text-secondary me-2"></i>
                                                        <?= $img['est_active'] ? 'Masquer' : 'Afficher' ?>
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $img['id_image'] ?>">
                                                    <i class="bx bx-trash me-2"></i>Supprimer
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <!-- Overlay pour réorganisation -->
                                <div class="sort-handle position-absolute bottom-0 start-0 end-0 text-white p-2 text-center d-none" style="background: rgba(0,0,0,0.7); cursor: move;">
                                    <i class="bx bx-move"></i> Glisser pour réordonner
                                    <input type="hidden" name="ordre[<?= $img['id_image'] ?>]" class="order-input" value="<?= $img['ordre_affichage'] ?>">
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="card-text small text-muted mb-1">
                                    <?= htmlspecialchars(substr($img['legende'] ?: 'Sans légende', 0, 50)) ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">#<?= $img['ordre_affichage'] ?></small>
                                    <?php if (!$img['est_active']): ?>
                                        <span class="badge bg-secondary">Masquée</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL EDIT -->
                        <div class="modal fade" id="edit_<?= $img['id_image'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'image</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('Produit_images/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_image" value="<?= $img['id_image'] ?>">
                                        <div class="modal-body">
                                            <div class="text-center mb-3">
                                                <img src="<?= base_url($img_path) ?>" class="img-fluid rounded" style="max-height: 200px;">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nouvelle image (laisser vide pour conserver)</label>
                                                <input type="file" class="form-control" name="image" accept="image/*">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Légende</label>
                                                <input type="text" class="form-control" name="legende" value="<?= htmlspecialchars($img['legende'] ?: '') ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Texte alternatif (SEO)</label>
                                                <input type="text" class="form-control" name="alt_text" value="<?= htmlspecialchars($img['alt_text'] ?: '') ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Ordre</label>
                                                <input type="number" class="form-control" name="ordre_affichage" value="<?= $img['ordre_affichage'] ?>" min="0">
                                            </div>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="form-check-input" name="est_active" id="active_<?= $img['id_image'] ?>" value="1" <?= $img['est_active'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="active_<?= $img['id_image'] ?>">Image active</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-info">Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $img['id_image'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center p-4">
                                        <img src="<?= base_url($img_path) ?>" class="rounded mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                        <p>Supprimer cette image ?</p>
                                        <p class="text-danger small">Action irréversible</p>
                                    </div>
                                    <form action="<?= base_url('Produit_images/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $img['id_image'] ?>">
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">Supprimer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-4 d-none" id="sortActions">
                    <button type="submit" class="btn btn-success me-2">
                        <i class="bx bx-save me-2"></i>Enregistrer l'ordre
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="disableSortMode()">
                        Annuler
                    </button>
                </div>
            </form>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bx bx-images text-muted" style="font-size: 4rem;"></i>
                <p class="mt-3 text-muted">Aucune image dans la galerie</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#upload_modal">
                    <i class="bx bx-upload me-2"></i>Ajouter des images
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- MODAL UPLOAD -->
<div class="modal fade" id="upload_modal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-upload me-2"></i>Uploader des images</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Produit_images/Create') ?>" method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="modal-body p-4">
                    <?php if (!isset($produit)): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Produit <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_produit" required>
                            <option value="">Sélectionner un produit...</option>
                            <?php foreach ($produits as $p): ?>
                                <option value="<?= $p['id_produit'] ?>">[<?= $p['id_produit'] ?>] <?= htmlspecialchars($p['nom_produit']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Images <span class="text-danger">*</span></label>
                        <div class="border border-dashed rounded p-4 text-center" id="dropZone" style="border-color: #dee2e6; transition: all 0.3s ease; cursor: pointer;">
                            <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                            <p class="mb-2">Glissez-déposez vos images ici ou cliquez pour sélectionner</p>
                            <input type="file" class="form-control" name="images[]" multiple accept="image/*" id="fileInput" style="display: none;">
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileInput').click()">
                                <i class="bx bx-folder-open me-2"></i>Parcourir
                            </button>
                        </div>
                        <small class="text-muted">Formats: JPG, PNG, GIF, WEBP. Max 5MB par image.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Légende par défaut (optionnel)</label>
                        <input type="text" class="form-control" name="legende" placeholder="Sera appliquée à toutes les images">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Texte alternatif par défaut (SEO)</label>
                        <input type="text" class="form-control" name="alt_text" placeholder="Description pour le référencement">
                    </div>
                    
                    <div id="previewContainer" class="row g-2 mt-3 d-none">
                        <p class="mb-2"><strong>Aperçu :</strong></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success" id="uploadBtn" disabled style="opacity: 0.6;">
                        <i class="bx bx-upload me-2"></i>Uploader
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SortableJS pour le glisser-déposer -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-hide alerts (vanilla JS)
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        });
    }, 5000);
    
    // Éléments upload
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('previewContainer');
    const uploadBtn = document.getElementById('uploadBtn');
    const dropZone = document.getElementById('dropZone');
    
    if (fileInput && previewContainer && uploadBtn) {
        // Preview des fichiers
        fileInput.addEventListener('change', function() {
            previewContainer.innerHTML = '<p class="mb-2"><strong>Aperçu :</strong></p>';
            previewContainer.classList.remove('d-none');
            
            if (this.files.length === 0) {
                uploadBtn.disabled = true;
                uploadBtn.style.opacity = '0.6';
                return;
            }
            
            Array.from(this.files).forEach(function(file, index) {
                // Vérifier que c'est une image
                if (!file.type.startsWith('image/')) {
                    console.warn('Fichier ignoré (pas une image):', file.name);
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'col-3 mb-2';
                    div.innerHTML = `
                        <div class="position-relative">
                            <img src="${e.target.result}" class="img-fluid rounded border" style="height: 80px; object-fit: cover; width: 100%;">
                            <span class="position-absolute top-0 end-0 badge bg-dark m-1" style="font-size: 8px;">${index + 1}</span>
                        </div>
                        <small class="d-block text-truncate text-muted" style="font-size: 10px;" title="${file.name}">${file.name}</small>
                        <small class="text-muted" style="font-size: 9px;">${(file.size / 1024).toFixed(1)} KB</small>
                    `;
                    previewContainer.appendChild(div);
                };
                reader.onerror = function() {
                    console.error('Erreur lecture fichier:', file.name);
                };
                reader.readAsDataURL(file);
            });
            
            // Activer le bouton seulement si des images valides sont sélectionnées
            const validImages = Array.from(this.files).filter(f => f.type.startsWith('image/'));
            if (validImages.length > 0) {
                uploadBtn.disabled = false;
                uploadBtn.style.opacity = '1';
            }
        });
    }
    
    if (dropZone && fileInput) {
        // Drag & drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
            dropZone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });
        
        ['dragenter', 'dragover'].forEach(function(eventName) {
            dropZone.addEventListener(eventName, function() {
                this.style.backgroundColor = '#f8f9fa';
                this.style.borderColor = '#0f4c3a';
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(function(eventName) {
            dropZone.addEventListener(eventName, function() {
                this.style.backgroundColor = '';
                this.style.borderColor = '#dee2e6';
            }, false);
        });
        
        dropZone.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
        
        dropZone.addEventListener('click', function(e) {
            if (e.target !== fileInput && !e.target.closest('button')) {
                fileInput.click();
            }
        });
    }
});

// Variables globales pour Sortable
let sortableInstance = null;

// Mode réorganisation
function enableSortMode() {
    const grid = document.getElementById('imageGrid');
    const handles = document.querySelectorAll('.sort-handle');
    const actions = document.getElementById('sortActions');
    const btn = document.querySelector('button[onclick="enableSortMode()"]');
    
    if (!grid) {
        console.error('Grid non trouvé');
        return;
    }
    
    // Afficher les handles et boutons
    handles.forEach(function(h) {
        h.classList.remove('d-none');
        h.style.display = 'block';
    });
    
    if (actions) {
        actions.classList.remove('d-none');
        actions.style.display = 'block';
    }
    
    // Changer le bouton
    if (btn) {
        btn.innerHTML = '<i class="bx bx-check me-2"></i>Mode édition actif';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-primary');
        btn.onclick = disableSortMode;
    }
    
    // Initialiser SortableJS
    if (typeof Sortable !== 'undefined') {
        sortableInstance = new Sortable(grid, {
            animation: 150,
            handle: '.sort-handle',
            ghostClass: 'sortable-ghost',
            onStart: function() {
                document.body.style.cursor = 'grabbing';
            },
            onEnd: function() {
                document.body.style.cursor = '';
                // Mettre à jour les valeurs des inputs d'ordre
                const items = grid.querySelectorAll('.image-item');
                items.forEach(function(item, index) {
                    const input = item.querySelector('.order-input');
                    if (input) {
                        input.value = index;
                    }
                });
            }
        });
        console.log('Sortable initialisé');
    } else {
        console.error('SortableJS non chargé');
        alert('Erreur: Bibliothèque de tri non chargée');
    }
}

function disableSortMode() {
    if (sortableInstance) {
        sortableInstance.destroy();
        sortableInstance = null;
    }
    window.location.reload();
}
</script>

<style>
/* ===== STYLES SPÉCIFIQUES PAGE MÉDIAS ===== */

/* Ghost element pendant le drag */
.sortable-ghost {
    opacity: 0.4 !important;
    background-color: #f8f9fa !important;
    border: 2px dashed #0f4c3a !important;
}

/* Zone de drop */
#dropZone {
    transition: all 0.3s ease !important;
}

#dropZone:hover {
    background-color: #f8f9fa !important;
    border-color: #0f4c3a !important;
}

/* Effet zoom images */
.card .card-img-top {
    transition: transform 0.3s ease !important;
}

.card:hover .card-img-top {
    transform: scale(1.02) !important;
}

/* Handle de tri */
.sort-handle {
    background-color: rgba(0,0,0,0.7) !important;
    color: white !important;
    font-size: 12px !important;
}

/* Bouton upload actif/inactif */
#uploadBtn:not(:disabled) {
    opacity: 1 !important;
    cursor: pointer !important;
}

#uploadBtn:disabled {
    opacity: 0.6 !important;
    cursor: not-allowed !important;
}

/* Cards images */
.image-item .card {
    transition: box-shadow 0.3s ease !important;
}

.image-item .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}

/* Image principale */
.border-primary.border-2 {
    border-width: 3px !important;
}

/* Modals */
.modal {
    z-index: 1055 !important;
}

.modal-backdrop {
    z-index: 1050 !important;
}

/* Preview container */
#previewContainer img {
    transition: transform 0.2s ease;
}

#previewContainer img:hover {
    transform: scale(1.05);
}
</style>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>