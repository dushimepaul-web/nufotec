<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
    <div class="page-content">
        
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Médias divers</div>
            <div class="ms-auto">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouter">
                    <i class="bx bx-upload"></i> Nouveau média
                </button>
            </div>
        </div>

        <!-- Filtres rapides -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= base_url('autre/admin_liste') ?>" class="btn btn-sm btn-outline-primary <?= empty($filtre_actif) ? 'active' : '' ?>">Tous</a>
                    <a href="<?= base_url('autre/admin_liste?filtre=photo') ?>" class="btn btn-sm btn-outline-primary <?= isset($filtre_actif) && $filtre_actif == 'photo' ? 'active' : '' ?>">📷 Images</a>
                    <a href="<?= base_url('autre/admin_liste?filtre=book') ?>" class="btn btn-sm btn-outline-primary <?= isset($filtre_actif) && $filtre_actif == 'book' ? 'active' : '' ?>">📄 Documents</a>
                    <a href="<?= base_url('autre/admin_liste?filtre=link') ?>" class="btn btn-sm btn-outline-primary <?= isset($filtre_actif) && $filtre_actif == 'link' ? 'active' : '' ?>">🔗 Liens</a>
                    <a href="<?= base_url('autre/admin_liste?filtre=texte') ?>" class="btn btn-sm btn-outline-primary <?= isset($filtre_actif) && $filtre_actif == 'texte' ? 'active' : '' ?>">📝 Textes</a>
                    <a href="<?= base_url('autre/admin_liste?filtre=other') ?>" class="btn btn-sm btn-outline-primary <?= isset($filtre_actif) && $filtre_actif == 'other' ? 'active' : '' ?>">📦 Autres</a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                <div class="text-white"><?= $this->session->flashdata('success') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                <div class="text-white"><?= $this->session->flashdata('error') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Grille des médias -->
        <div class="row g-3">
            <?php foreach ($medias as $media): ?>
                <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 shadow-sm border-0">
                        
                        <!-- Preview -->
                        <div class="position-relative" style="height: 180px; overflow: hidden; background: #f8f9fa;">
                            <?php if ($media->sous_type == 'link'): ?>
                                <!-- Lien externe -->
                                <?php 
                                $video_id = '';
                                if (strpos($media->lien, 'youtube.com') !== false || strpos($media->lien, 'youtu.be') !== false) {
                                    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $media->lien, $matches);
                                    $video_id = $matches[1] ?? '';
                                }
                                ?>
                                <?php if ($video_id): ?>
                                    <img src="https://img.youtube.com/vi/<?= $video_id ?>/mqdefault.jpg" class="w-100 h-100 object-fit-cover" alt="">
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <i class="bx bx-play-circle text-white fs-1"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-primary">
                                        <i class="bx bx-link-external fs-1"></i>
                                        <small class="mt-2">Lien externe</small>
                                    </div>
                                <?php endif; ?>
                                
                            <?php elseif ($media->sous_type == 'photo' || strpos($media->mime_type, 'image') !== false): ?>
                                <!-- Image -->
                                <img src="<?= base_url($media->fichier) ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($media->titre) ?>" style="cursor: pointer;" onclick="voirMedia(<?= $media->id_media ?>)">
                                
                            <?php elseif ($media->sous_type == 'book' || $media->mime_type == 'application/pdf'): ?>
                                <!-- PDF -->
                                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-danger">
                                    <i class="bx bxs-file-pdf fs-1"></i>
                                    <small class="mt-2">PDF Document</small>
                                </div>
                                
                            <?php elseif (!empty($media->miniature)): ?>
                                <!-- Miniature personnalisée -->
                                <img src="<?= base_url($media->miniature) ?>" class="w-100 h-100 object-fit-cover" alt="">
                                
                            <?php else: ?>
                                <!-- Default -->
                                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-secondary">
                                    <i class="bx bx-file fs-1"></i>
                                    <small class="mt-2"><?= strtoupper(pathinfo($media->fichier, PATHINFO_EXTENSION)) ?></small>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badges -->
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-dark"><?= ucfirst($media->sous_type) ?></span>
                            </div>
                            
                            <div class="position-absolute top-0 end-0 m-2">
                                <?php if ($media->est_actif): ?>
                                    <span class="badge bg-success"><i class="bx bx-check"></i></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="bx bx-x"></i></span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Overlay actions -->
                            <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-1">
                                <?php if ($media->is_for_whatsapp): ?>
                                    <span class="badge bg-success" title="WhatsApp"><i class="bx bxl-whatsapp"></i></span>
                                <?php endif; ?>
                                <?php if ($media->is_for_website): ?>
                                    <span class="badge bg-info" title="Site web"><i class="bx bx-globe"></i></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Contenu -->
                        <div class="card-body p-3">
                            <h6 class="card-title text-truncate mb-1" title="<?= htmlspecialchars($media->titre) ?>">
                                <?= htmlspecialchars($media->titre) ?>
                            </h6>
                            <p class="card-text small text-muted text-truncate mb-2">
                                <?= $media->categorie ?: 'Sans catégorie' ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?= date('d/m/Y', strtotime($media->created_at)) ?></small>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="voirMedia(<?= $media->id_media ?>)" title="Voir">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="modifierMedia(<?= $media->id_media ?>)" title="Modifier">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmerSuppression(<?= $media->id_media ?>, '<?= htmlspecialchars(addslashes($media->titre)) ?>')" title="Supprimer">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if (!empty($pagination)): ?>
            <div class="mt-4">
                <?= $pagination ?>
            </div>
        <?php endif; ?>

        <!-- Empty state -->
        <?php if (empty($medias)): ?>
            <div class="text-center py-5">
                <i class="bx bx-folder-open fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">Aucun média trouvé</h5>
                <p class="text-muted">Cliquez sur "Nouveau média" pour ajouter du contenu</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- ==================== MODAL AJOUTER ==================== -->
<div class="modal fade" id="modalAjouter" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-upload"></i> Nouveau média</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <?= form_open_multipart('autre/admin_ajouter', ['id' => 'formAjouter', 'class' => 'needs-validation']) ?>
            
            <div class="modal-body">
                <div class="row g-3">
                    
                    <!-- Type de média -->
                    <div class="col-12">
                        <label class="form-label fw-bold">Type de média *</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <input type="radio" class="btn-check" name="sous_type" id="type_photo" value="photo" onchange="toggleTypeFields()">
                            <label class="btn btn-outline-primary" for="type_photo"><i class="bx bx-image"></i> Image</label>
                            
                            <input type="radio" class="btn-check" name="sous_type" id="type_book" value="book" onchange="toggleTypeFields()">
                            <label class="btn btn-outline-primary" for="type_book"><i class="bx bx-file"></i> Document</label>
                            
                            <input type="radio" class="btn-check" name="sous_type" id="type_link" value="link" onchange="toggleTypeFields()">
                            <label class="btn btn-outline-primary" for="type_link"><i class="bx bx-link"></i> Lien</label>
                            
                            <input type="radio" class="btn-check" name="sous_type" id="type_texte" value="texte" onchange="toggleTypeFields()">
                            <label class="btn btn-outline-primary" for="type_texte"><i class="bx bx-text"></i> Texte</label>
                            
                            <input type="radio" class="btn-check" name="sous_type" id="type_other" value="other" onchange="toggleTypeFields()">
                            <label class="btn btn-outline-primary" for="type_other"><i class="bx bx-package"></i> Autre</label>
                        </div>
                    </div>

                    <!-- Titre -->
                    <div class="col-12">
                        <label class="form-label">Titre *</label>
                        <input type="text" name="titre" class="form-control" required placeholder="Nom du média">
                    </div>

                    <!-- Zone FICHIER -->
                    <div class="col-12" id="zone_fichier">
                        <label class="form-label">Fichier *</label>
                        <input type="file" name="fichier" class="form-control" id="input_fichier">
                        <small class="text-muted">Images: jpg, png, gif, webp | Documents: pdf, doc, docx</small>
                    </div>

                    <!-- Zone LIEN -->
                    <div class="col-12" id="zone_lien" style="display: none;">
                        <label class="form-label">URL externe *</label>
                        <input type="url" name="lien" class="form-control" placeholder="https://youtube.com/watch?v=...">
                        <small class="text-muted">YouTube, Vimeo, ou tout lien externe</small>
                        
                        <div class="mt-2">
                            <label class="form-label">Miniature (URL optionnel)</label>
                            <input type="url" name="miniature_externe" class="form-control" placeholder="https://...">
                        </div>
                    </div>

                    <!-- Zone TEXTE -->
                    <div class="col-12" id="zone_texte" style="display: none;">
                        <label class="form-label">Contenu texte *</label>
                        <textarea name="contenu_texte" class="form-control" rows="4" placeholder="Votre texte ici..."></textarea>
                    </div>

                    <!-- Miniature upload (optionnel) -->
                    <div class="col-12 col-md-6" id="zone_miniature_upload">
                        <label class="form-label">Miniature personnalisée</label>
                        <input type="file" name="miniature" class="form-control" accept="image/*">
                        <small class="text-muted">Optionnel - pour override l'aperçu</small>
                    </div>

                    <!-- Catégorie -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Catégorie</label>
                        <input type="text" name="categorie" class="form-control" placeholder="Ex: Publicité, Témoignage...">
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <!-- Options -->
                    <div class="col-12">
                        <div class="row g-2">
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="est_actif" class="form-check-input" value="1" checked>
                                    <label class="form-check-label">Actif</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_for_website" class="form-check-input" value="1">
                                    <label class="form-check-label">Site web</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_for_whatsapp" class="form-check-input" value="1">
                                    <label class="form-check-label">WhatsApp</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="a_partager_reseaux" class="form-check-input" value="1">
                                    <label class="form-check-label">Réseaux</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Enregistrer</button>
            </div>
            
            <?= form_close() ?>
        </div>
    </div>


<!-- ==================== MODAL MODIFIER ==================== -->
<div class="modal fade" id="modalModifier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bx bx-edit"></i> Modifier le média</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <?= form_open_multipart('', ['id' => 'formModifier', 'class' => 'needs-validation']) ?>
            <input type="hidden" name="id_media" id="edit_id">
            
            <div class="modal-body">
                <div class="row g-3">
                    
                    <div class="col-12">
                        <label class="form-label fw-bold">Type: <span id="edit_type_label" class="badge bg-secondary"></span></label>
                        <input type="hidden" name="sous_type" id="edit_sous_type">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Titre *</label>
                        <input type="text" name="titre" id="edit_titre" class="form-control" required>
                    </div>

                    <!-- Zone fichier existant + nouveau -->
                    <div class="col-12" id="edit_zone_fichier">
                        <label class="form-label">Nouveau fichier (laisser vide pour garder l'actuel)</label>
                        <input type="file" name="fichier" class="form-control">
                        <div id="edit_fichier_actuel" class="mt-2 p-2 bg-light rounded small"></div>
                    </div>

                    <!-- Zone lien -->
                    <div class="col-12" id="edit_zone_lien" style="display: none;">
                        <label class="form-label">URL *</label>
                        <input type="url" name="lien" id="edit_lien" class="form-control">
                    </div>

                    <!-- Zone texte -->
                    <div class="col-12" id="edit_zone_texte" style="display: none;">
                        <label class="form-label">Contenu *</label>
                        <textarea name="contenu_texte" id="edit_contenu_texte" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Miniature</label>
                        <input type="file" name="miniature" class="form-control" accept="image/*">
                        <div id="edit_miniature_actuelle" class="mt-2"></div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Catégorie</label>
                        <input type="text" name="categorie" id="edit_categorie" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-12">
                        <div class="row g-2" id="edit_options">
                            <!-- Checkboxes injectés par JS -->
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-warning"><i class="bx bx-save"></i> Mettre à jour</button>
            </div>
            
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- ==================== MODAL VOIR ==================== -->
<div class="modal fade" id="modalVoir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="view_titre"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Preview -->
                    <div class="col-lg-8 bg-dark d-flex align-items-center justify-content-center" style="min-height: 400px;">
                        <div id="view_preview" class="w-100 text-center">
                            <!-- Contenu injecté par JS -->
                        </div>
                    </div>
                    
                    <!-- Infos -->
                    <div class="col-lg-4 p-4">
                        <div id="view_details">
                            <!-- Détails injectés par JS -->
                        </div>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <a href="#" id="view_btn_original" target="_blank" class="btn btn-primary">
                                <i class="bx bx-link-external"></i> Voir l'original
                            </a>
                            <button type="button" class="btn btn-outline-secondary" onclick="copierLien()">
                                <i class="bx bx-copy"></i> Copier le lien
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL CONFIRMATION SUPPRESSION ==================== -->
<div class="modal fade" id="modalSupprimer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title"><i class="bx bx-trash"></i> Supprimer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bx bx-error-circle text-danger fs-1 mb-3"></i>
                <p>Supprimer définitivement <strong id="del_titre" class="text-break"></strong> ?</p>
                <p class="small text-muted">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <a href="#" id="del_confirm_btn" class="btn btn-danger">Supprimer</a>
            </div>
        </div>
    </div>
</div>

<script>
// ==================== GESTION TYPES (AJOUT) ====================
function toggleTypeFields() {
    const type = document.querySelector('input[name="sous_type"]:checked')?.value;
    
    const zoneFichier = document.getElementById('zone_fichier');
    const zoneLien = document.getElementById('zone_lien');
    const zoneTexte = document.getElementById('zone_texte');
    const zoneMiniature = document.getElementById('zone_miniature_upload');
    const inputFichier = document.getElementById('input_fichier');
    
    // Reset
    zoneFichier.style.display = 'none';
    zoneLien.style.display = 'none';
    zoneTexte.style.display = 'none';
    inputFichier.removeAttribute('required');
    
    if (type === 'link') {
        zoneLien.style.display = 'block';
        zoneMiniature.style.display = 'block';
        document.querySelector('#zone_lien input[name="lien"]').setAttribute('required', 'required');
    } else if (type === 'texte') {
        zoneTexte.style.display = 'block';
        document.querySelector('#zone_texte textarea').setAttribute('required', 'required');
        zoneMiniature.style.display = 'block';
    } else if (type) {
        // photo, book, other
        zoneFichier.style.display = 'block';
        inputFichier.setAttribute('required', 'required');
        zoneMiniature.style.display = 'block';
    }
}

// ==================== MODIFIER ====================
function modifierMedia(id) {
    // Appel AJAX pour récupérer les données
    fetch('<?= base_url('autre/get_json/') ?>' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const media = data.media;
                
                // Remplir le formulaire
                document.getElementById('edit_id').value = media.id_media;
                document.getElementById('edit_titre').value = media.titre;
                document.getElementById('edit_sous_type').value = media.sous_type;
                document.getElementById('edit_type_label').textContent = media.sous_type.toUpperCase();
                document.getElementById('edit_categorie').value = media.categorie || '';
                document.getElementById('edit_description').value = media.description || '';
                
                // Afficher/masquer zones selon type
                const zoneFichier = document.getElementById('edit_zone_fichier');
                const zoneLien = document.getElementById('edit_zone_lien');
                const zoneTexte = document.getElementById('edit_zone_texte');
                
                zoneFichier.style.display = 'none';
                zoneLien.style.display = 'none';
                zoneTexte.style.display = 'none';
                
                if (media.sous_type === 'link') {
                    zoneLien.style.display = 'block';
                    document.getElementById('edit_lien').value = media.lien || '';
                } else if (media.sous_type === 'texte') {
                    zoneTexte.style.display = 'block';
                    document.getElementById('edit_contenu_texte').value = media.contenu_texte || '';
                } else {
                    zoneFichier.style.display = 'block';
                    let fichierHtml = '';
                    if (media.fichier) {
                        fichierHtml = `<i class="bx bx-file"></i> Actuel: <a href="<?= base_url() ?>${media.fichier}" target="_blank">${media.fichier.split('/').pop()}</a>`;
                    } else {
                        fichierHtml = '<em class="text-muted">Aucun fichier</em>';
                    }
                    document.getElementById('edit_fichier_actuel').innerHTML = fichierHtml;
                }
                
                // Miniature actuelle
                let miniHtml = '';
                if (media.miniature) {
                    miniHtml = `<img src="<?= base_url() ?>${media.miniature}" style="max-height: 60px;" class="img-thumbnail">`;
                }
                document.getElementById('edit_miniature_actuelle').innerHTML = miniHtml;
                
                // Options checkboxes
                const optionsHtml = `
                    <div class="col-6">
                        <div class="form-check">
                            <input type="checkbox" name="est_actif" class="form-check-input" value="1" ${media.est_actif == 1 ? 'checked' : ''}>
                            <label class="form-check-label">Actif</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input type="checkbox" name="is_for_website" class="form-check-input" value="1" ${media.is_for_website == 1 ? 'checked' : ''}>
                            <label class="form-check-label">Site web</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input type="checkbox" name="is_for_whatsapp" class="form-check-input" value="1" ${media.is_for_whatsapp == 1 ? 'checked' : ''}>
                            <label class="form-check-label">WhatsApp</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input type="checkbox" name="a_partager_reseaux" class="form-check-input" value="1" ${media.a_partager_reseaux == 1 ? 'checked' : ''}>
                            <label class="form-check-label">Réseaux</label>
                        </div>
                    </div>
                `;
                document.getElementById('edit_options').innerHTML = optionsHtml;
                
                // Mettre à jour l'action du formulaire
                document.getElementById('formModifier').action = '<?= base_url('autre/admin_modifier/') ?>' + id;
                
                // Ouvrir modal
                new bootstrap.Modal(document.getElementById('modalModifier')).show();
            }
        });
}

// ==================== VOIR ====================
function voirMedia(id) {
    fetch('<?= base_url('autre/get_json/') ?>' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const media = data.media;
                
                document.getElementById('view_titre').textContent = media.titre;
                
                let previewHtml = '';
                let originalUrl = '';
                
                if (media.sous_type === 'link') {
                    // YouTube embed ou lien
                    if (media.lien.includes('youtube') || media.lien.includes('youtu.be')) {
                        const videoId = extractYouTubeId(media.lien);
                        previewHtml = `<iframe width="100%" height="400" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen></iframe>`;
                    } else {
                        previewHtml = `<div class="text-white p-4"><i class="bx bx-link-external fs-1"></i><br><a href="${media.lien}" target="_blank" class="text-white">${media.lien}</a></div>`;
                    }
                    originalUrl = media.lien;
                    
                } else if (media.sous_type === 'photo' || media.mime_type?.includes('image')) {
                    previewHtml = `<img src="<?= base_url() ?>${media.fichier}" class="img-fluid" style="max-height: 500px;">`;
                    originalUrl = '<?= base_url() ?>' + media.fichier;
                    
                } else if (media.sous_type === 'book' || media.mime_type === 'application/pdf') {
                    previewHtml = `<iframe src="<?= base_url() ?>${media.fichier}" width="100%" height="500px"></iframe>`;
                    originalUrl = '<?= base_url() ?>' + media.fichier;
                    
                } else if (media.sous_type === 'texte') {
                    previewHtml = `<div class="text-white p-4 text-start"><pre class="text-white">${escapeHtml(media.contenu_texte)}</pre></div>`;
                    originalUrl = '#';
                    
                } else {
                    // Default - download link
                    previewHtml = `<div class="text-white p-4"><i class="bx bx-file fs-1 mb-3"></i><br><a href="<?= base_url() ?>${media.fichier}" class="btn btn-light" download>Télécharger ${media.fichier?.split('.').pop()}</a></div>`;
                    originalUrl = '<?= base_url() ?>' + media.fichier;
                }
                
                document.getElementById('view_preview').innerHTML = previewHtml;
                
                // Détails
                const detailsHtml = `
                    <h6>${media.titre}</h6>
                    <p class="text-muted small">${media.description || 'Aucune description'}</p>
                    
                    <table class="table table-sm">
                        <tr><td><small class="text-muted">Type</small></td><td><span class="badge bg-secondary">${media.sous_type}</span></td></tr>
                        <tr><td><small class="text-muted">Catégorie</small></td><td>${media.categorie || '-'}</td></tr>
                        <tr><td><small class="text-muted">Date</small></td><td>${new Date(media.created_at).toLocaleDateString('fr-FR')}</td></tr>
                        ${media.taille ? `<tr><td><small class="text-muted">Taille</small></td><td>${formatBytes(media.taille)}</td></tr>` : ''}
                        ${media.mime_type ? `<tr><td><small class="text-muted">Format</small></td><td>${media.mime_type}</td></tr>` : ''}
                    </table>
                    
                    <div class="d-flex gap-2 flex-wrap">
                        ${media.est_actif ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Inactif</span>'}
                        ${media.is_for_website ? '<span class="badge bg-info">Site</span>' : ''}
                        ${media.is_for_whatsapp ? '<span class="badge bg-success">WhatsApp</span>' : ''}
                    </div>
                `;
                
                document.getElementById('view_details').innerHTML = detailsHtml;
                
                // Bouton original
                const btnOriginal = document.getElementById('view_btn_original');
                if (originalUrl === '#') {
                    btnOriginal.style.display = 'none';
                } else {
                    btnOriginal.style.display = 'block';
                    btnOriginal.href = originalUrl;
                }
                
                new bootstrap.Modal(document.getElementById('modalVoir')).show();
            }
        });
}

// ==================== SUPPRESSION ====================
function confirmerSuppression(id, titre) {
    document.getElementById('del_titre').textContent = titre;
    document.getElementById('del_confirm_btn').href = '<?= base_url('autre/admin_supprimer/') ?>' + id;
    new bootstrap.Modal(document.getElementById('modalSupprimer')).show();
}

// ==================== UTILITAIRES ====================
function extractYouTubeId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
}

function formatBytes(bytes, decimals = 2) {
    if (!+bytes) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function copierLien() {
    const url = document.getElementById('view_btn_original').href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Lien copié dans le presse-papiers !');
    });
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Validation formulaire ajout
    const formAjouter = document.getElementById('formAjouter');
    if (formAjouter) {
        formAjouter.addEventListener('submit', function(e) {
            const type = document.querySelector('input[name="sous_type"]:checked');
            if (!type) {
                e.preventDefault();
                alert('Veuillez sélectionner un type de média');
                return false;
            }
        });
    }
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>