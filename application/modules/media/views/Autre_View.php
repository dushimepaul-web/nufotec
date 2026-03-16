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
                    <li class="breadcrumb-item"><a href="javascript:;">Galerie</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion Autre (Link, Book, Texte, Photo)</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_autre">
                <i class="bx bx-plus"></i> Nouvel Élément
            </a>
        </div>
    </div>

    <!-- Messages Flash -->
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

    <!-- Filtres par type -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-sm btn-outline-primary active filter-type" data-filter="all">
                    <i class="bx bx-grid-alt me-1"></i>Tous
                </button>
                <button class="btn btn-sm btn-outline-info filter-type" data-filter="link">
                    <i class="bx bx-link me-1"></i>Liens
                </button>
                <button class="btn btn-sm btn-outline-warning filter-type" data-filter="book">
                    <i class="bx bx-book me-1"></i>Livres/PDF
                </button>
                <button class="btn btn-sm btn-outline-success filter-type" data-filter="texte">
                    <i class="bx bx-text me-1"></i>Textes
                </button>
                <button class="btn btn-sm btn-outline-danger filter-type" data-filter="photo">
                    <i class="bx bx-image me-1"></i>Photos
                </button>
                <button class="btn btn-sm btn-outline-secondary filter-type" data-filter="other">
                    <i class="bx bx-file me-1"></i>Autres
                </button>
            </div>
        </div>
    </div>

    <!-- Card Principale -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-primary"><i class="bx bx-collection me-2"></i>Liste des Éléments</h5>
                <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Upload Illimité</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="autreTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Type</th>
                            <th width="12%">Aperçu</th>
                            <th width="20%">Titre</th>
                            <th width="10%">Source</th>
                            <th width="8%">Taille</th>
                            <th width="8%">Statut</th>
                            <th width="8%">WhatsApp</th>
                            <th width="8%">Site Web</th>
                            <th width="11%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($items)): $i = 1; foreach ($items as $value): 
                        $sous_type = $value['sous_type'] ?? 'other';
                        
                        // Badge type
                        $type_badges = [
                            'link' => '<span class="badge bg-info"><i class="bx bx-link me-1"></i>Lien</span>',
                            'book' => '<span class="badge bg-warning"><i class="bx bx-book me-1"></i>Livre</span>',
                            'texte' => '<span class="badge bg-success"><i class="bx bx-text me-1"></i>Texte</span>',
                            'photo' => '<span class="badge bg-danger"><i class="bx bx-image me-1"></i>Photo</span>',
                            'other' => '<span class="badge bg-secondary"><i class="bx bx-file me-1"></i>Autre</span>'
                        ];
                        $type_badge = $type_badges[$sous_type] ?? $type_badges['other'];
                        
                        // Source
                        if ($sous_type === 'link') {
                            $source_badge = '<span class="badge bg-primary"><i class="bx bx-globe me-1"></i>URL</span>';
                        } elseif (!empty($value['fichier'])) {
                            $source_badge = '<span class="badge bg-success"><i class="bx bx-upload me-1"></i>Fichier</span>';
                        } else {
                            $source_badge = '<span class="badge bg-light text-dark">-</span>';
                        }
                        
                        // Formatage taille
                        $taille_formatee = '-';
                        if (!empty($value['taille'])) {
                            $taille = $value['taille'];
                            if ($taille >= 1048576) {
                                $taille_formatee = number_format($taille / 1048576, 2) . ' MB';
                            } elseif ($taille >= 1024) {
                                $taille_formatee = number_format($taille / 1024, 2) . ' KB';
                            } else {
                                $taille_formatee = $taille . ' B';
                            }
                        }
                        
                        // Miniature
                        $thumb_path = !empty($value['miniature']) ? $value['miniature'] : 'assets/images/file-default.png';
                        $thumb_url = (strpos($thumb_path, 'http') === 0) ? $thumb_path : base_url($thumb_path);
                        
                        // Icône d'aperçu selon type
                        $preview_icon = [
                            'link' => 'bx-link-external',
                            'book' => 'bx-book-open',
                            'texte' => 'bx-text',
                            'photo' => 'bx-image',
                            'other' => 'bx-file'
                        ][$sous_type] ?? 'bx-file';
                    ?>
                        <tr data-type="<?= $sous_type ?>">
                            <td><?= $i++ ?></td>
                            <td><?= $type_badge ?></td>
                            
                            <td>
                                <div class="position-relative" style="width: 80px; height: 60px;">
                                    <img src="<?= $thumb_url ?>" class="rounded border w-100 h-100" style="object-fit: cover; background: #f8f9fa;" alt="Miniature">
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <i class="bx <?= $preview_icon ?> text-primary" style="font-size: 1.2rem; background: rgba(255,255,255,0.8); border-radius: 50%; padding: 2px;"></i>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['titre'] ?? 'Sans titre') ?></strong>
                                    <?php if (!empty($value['description'])): ?>
                                        <small class="text-muted text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars(substr($value['description'], 0, 50)) ?>...
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td><?= $source_badge ?></td>

                            <td>
                                <span class="badge bg-light text-dark border"><?= $taille_formatee ?></span>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_media'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_actif']) && $value['est_actif'] == 1): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactif</span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input toggle-field" type="checkbox" 
                                           data-id="<?= $value['id_media'] ?>" 
                                           data-field="is_for_whatsapp"
                                           <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>>
                                </div>
                            </td>

                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input toggle-field" type="checkbox" 
                                           data-id="<?= $value['id_media'] ?>" 
                                           data-field="is_for_website"
                                           <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>>
                                </div>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_media'] ?>">
                                                <i class="bx bx-show me-2 text-info"></i>Voir
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#edit_<?= $value['id_media'] ?>">
                                                <i class="bx bx-edit me-2 text-primary"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_media'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Status -->
                        <div class="modal fade" id="status_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Changer le statut</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('autre/ChangeStatus') ?>" method="POST">
                                        <div class="modal-body text-center">
                                            <i class="bx bx-question-mark-circle text-warning" style="font-size: 4rem;"></i>
                                            <h5 class="mt-3">Voulez-vous changer le statut ?</h5>
                                            <p class="text-muted"><?= htmlspecialchars($value['titre']) ?></p>
                                            <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                            <input type="hidden" name="est_actif" value="<?= $value['est_actif'] ?>">
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-warning">Confirmer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal View -->
                        <div class="modal fade" id="view_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bx <?= $preview_icon ?> me-2"></i>
                                            <?= htmlspecialchars($value['titre']) ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php if ($sous_type === 'link'): ?>
                                            <div class="alert alert-info">
                                                <i class="bx bx-link-external me-2"></i>
                                                <a href="<?= htmlspecialchars($value['lien']) ?>" target="_blank" class="fw-bold">
                                                    <?= htmlspecialchars($value['lien']) ?>
                                                </a>
                                            </div>
                                            <?php if (!empty($value['miniature']) && strpos($value['miniature'], 'http') === 0): ?>
                                                <img src="<?= $value['miniature'] ?>" class="img-fluid rounded" alt="Preview">
                                            <?php endif; ?>
                                            
                                        <?php elseif ($sous_type === 'texte'): ?>
                                            <div class="card">
                                                <div class="card-body bg-light">
                                                    <pre class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($value['contenu_texte'] ?? 'Aucun contenu') ?></pre>
                                                </div>
                                            </div>
                                            
                                        <?php elseif ($sous_type === 'photo' && !empty($value['fichier'])): ?>
                                            <img src="<?= base_url($value['fichier']) ?>" class="img-fluid rounded w-100" alt="Photo" style="max-height: 400px; object-fit: contain;">
                                            
                                        <?php elseif ($sous_type === 'book' && !empty($value['fichier'])): ?>
                                            <div class="text-center">
                                                <i class="bx bx-file-pdf text-danger" style="font-size: 4rem;"></i>
                                                <p class="mt-2">
                                                    <a href="<?= base_url($value['fichier']) ?>" target="_blank" class="btn btn-primary">
                                                        <i class="bx bx-download me-1"></i>Télécharger PDF
                                                    </a>
                                                </p>
                                            </div>
                                            
                                        <?php elseif (!empty($value['fichier'])): ?>
                                            <div class="text-center">
                                                <i class="bx bx-file text-secondary" style="font-size: 4rem;"></i>
                                                <p class="mt-2">
                                                    <a href="<?= base_url($value['fichier']) ?>" target="_blank" class="btn btn-outline-primary">
                                                        <i class="bx bx-download me-1"></i>Télécharger
                                                    </a>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($value['description'])): ?>
                                            <hr>
                                            <p class="text-muted"><?= nl2br(htmlspecialchars($value['description'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="edit_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('autre/Update') ?>" method="POST" class="autre-form" data-mode="edit">
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                            <input type="hidden" name="sous_type" value="<?= $sous_type ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($value['titre']) ?>" required maxlength="255">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Type</label>
                                                <input type="text" class="form-control" value="<?= ucfirst($sous_type) ?>" disabled>
                                                <small class="text-muted">Le type ne peut pas être modifié</small>
                                            </div>

                                            <!-- Contenu selon le type -->
                                            <?php if ($sous_type === 'link'): ?>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Lien URL <span class="text-danger">*</span></label>
                                                    <input type="url" class="form-control" name="lien" value="<?= htmlspecialchars($value['lien'] ?? '') ?>" required placeholder="https://...">
                                                </div>
                                                
                                            <?php elseif ($sous_type === 'texte'): ?>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Contenu texte</label>
                                                    <textarea class="form-control" name="contenu_texte" rows="6" placeholder="Votre texte ici..."><?= htmlspecialchars($value['contenu_texte'] ?? '') ?></textarea>
                                                </div>
                                                
                                            <?php else: ?>
                                                <!-- Upload pour book, photo, other -->
                                                <div class="upload-section mb-3">
                                                    <label class="form-label fw-bold">Nouveau fichier <span class="badge bg-success">Chunked</span></label>
                                                    
                                                    <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_<?= $value['id_media'] ?>">
                                                        <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                                                        <p class="mb-2">Glissez-déposez ou <span class="text-primary fw-bold">cliquez</span></p>
                                                        
                                                        <input type="file" class="form-control d-none file-input" accept="<?= $sous_type === 'photo' ? 'image/*' : ($sous_type === 'book' ? '.pdf' : '*/*') ?>" data-upload-id="<?= $value['id_media'] ?>">
                                                        <input type="hidden" name="uploaded_file_path" class="uploaded-path">
                                                        <input type="hidden" name="miniature" class="miniature-path">
                                                        
                                                        <div class="upload-progress mt-3 d-none">
                                                            <div class="progress" style="height: 8px;">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                                                            </div>
                                                            <div class="small text-muted mt-1 upload-percent">0%</div>
                                                        </div>
                                                        
                                                        <?php if (!empty($value['fichier'])): ?>
                                                            <div class="current-file mt-3 p-2 bg-white rounded border">
                                                                <i class="bx bx-file text-success fs-4"></i>
                                                                <small class="d-block fw-bold"><?= basename($value['fichier']) ?></small>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="new-file-info mt-2 d-none">
                                                            <div class="alert alert-success mb-0 py-2">
                                                                <i class="bx bx-check-circle me-1"></i>Nouveau fichier prêt
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mt-2 d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger cancel-upload d-none">
                                                            <i class="bx bx-x me-1"></i>Annuler
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary pause-upload d-none">
                                                            <i class="bx bx-pause me-1"></i>Pause
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Catégorie</label>
                                                    <input type="text" class="form-control" name="categorie" value="<?= htmlspecialchars($value['categorie'] ?? '') ?>" list="categories_list_autre">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Date</label>
                                                    <input type="date" class="form-control" name="date_media" value="<?= $value['date_media'] ?? '' ?>">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Description</label>
                                                <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Crédits</label>
                                                <input type="text" class="form-control" name="credits" value="<?= htmlspecialchars($value['credits'] ?? '') ?>">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="est_actif" value="1" <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label fw-bold">Actif</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input share-toggle" type="checkbox" name="a_partager_reseaux" value="1" <?= (!empty($value['a_partager_reseaux']) && $value['a_partager_reseaux'] == 1) ? 'checked' : '' ?> data-target="edit_msg_<?= $value['id_media'] ?>">
                                                        <label class="form-check-label fw-bold">Partager sur réseaux</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3" id="edit_msg_<?= $value['id_media'] ?>" style="<?= (!empty($value['a_partager_reseaux']) && $value['a_partager_reseaux'] == 1) ? '' : 'display:none;' ?>">
                                                <label class="form-label fw-bold">Message réseaux</label>
                                                <textarea class="form-control" name="message_reseaux" rows="2" maxlength="280"><?= htmlspecialchars($value['message_reseaux'] ?? '') ?></textarea>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1" <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label fw-bold"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_for_website" value="1" <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label fw-bold"><i class="bx bx-globe text-primary me-1"></i>Site Web</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Delete -->
                        <div class="modal fade" id="delete_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('autre/Delete') ?>" method="POST">
                                        <div class="modal-body text-center">
                                            <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                            <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                            <p class="text-muted"><?= htmlspecialchars($value['titre']) ?></p>
                                            <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
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

                    <?php endforeach; else: ?>
                        
                                <i class="bx bx-collection fs-1 text-muted mb-3"></i>
                                <p class="text-muted">Aucun élément trouvé</p>
                                <a href="javascript:;" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#create_autre">
                                    <i class="bx bx-plus me-1"></i>Ajouter un élément
                                </a>
                            
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="create_autre" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouvel Élément</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('autre/Create') ?>" method="POST" class="autre-form" id="create_form" data-mode="create">
                <div class="modal-body">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Type d'élément <span class="text-danger">*</span></label>
                        <select class="form-select" name="sous_type" id="create_sous_type" required>
                            <option value="">-- Choisir --</option>
                            <option value="link">🔗 Lien / URL</option>
                            <option value="book">📚 Livre / PDF</option>
                            <option value="texte">📝 Texte</option>
                            <option value="photo">🖼️ Photo / Image</option>
                            <option value="other">📁 Autre fichier</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="titre" required maxlength="255" placeholder="Titre de l'élément">
                    </div>

                    <!-- Sections dynamiques selon le type -->
                    
                    <!-- Section Lien -->
                    <div id="section_link" class="type-section d-none">
                        <div class="mb-3">
                            <label class="form-label fw-bold">URL du lien <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="lien" placeholder="https://example.com">
                            <small class="text-muted">Lien externe (site web, vidéo, etc.)</small>
                        </div>
                    </div>

                    <!-- Section Texte -->
                    <div id="section_texte" class="type-section d-none">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Contenu texte</label>
                            <textarea class="form-control" name="contenu_texte" rows="8" placeholder="Votre texte ici..."></textarea>
                        </div>
                    </div>

                    <!-- Section Upload (Book, Photo, Other) -->
                    <div id="section_upload" class="type-section d-none">
                        <label class="form-label fw-bold">Fichier <span class="badge bg-success">Chunked Upload</span></label>
                        
                        <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_create">
                            <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                            <p class="mb-2" id="upload_instructions">Glissez-déposez ou <span class="text-primary fw-bold">cliquez</span></p>
                            <small class="text-muted d-block mb-2" id="file_types">Tous fichiers acceptés</small>
                            
                            <input type="file" class="form-control d-none file-input" id="create_file_input">
                            <input type="hidden" name="uploaded_file_path" id="create_uploaded_path">
                            <input type="hidden" name="miniature" id="create_miniature_path">
                            
                            <div class="upload-progress mt-3 d-none" id="create_progress_container">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span id="create_upload_status">Préparation...</span>
                                    <span id="create_upload_percent">0%</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="create_progress_bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                            
                            <div class="file-info mt-2 d-none" id="create_file_info">
                                <div class="alert alert-info mb-0 py-2">
                                    <i class="bx bx-file me-2"></i>
                                    <span id="create_file_name"></span> | 
                                    <span id="create_file_size" class="fw-bold"></span>
                                </div>
                            </div>
                            
                            <div class="upload-success mt-2 d-none" id="create_upload_success">
                                <div class="alert alert-success mb-0 py-2">
                                    <i class="bx bx-check-circle me-1"></i>Fichier uploadé!
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-2 d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger cancel-upload d-none" id="create_cancel">
                                <i class="bx bx-x me-1"></i>Annuler
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary pause-upload d-none" id="create_pause">
                                <i class="bx bx-pause me-1"></i>Pause
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary resume-upload d-none" id="create_resume">
                                <i class="bx bx-play me-1"></i>Reprendre
                            </button>
                        </div>
                    </div>

                    <!-- Champs communs -->
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Catégorie</label>
                            <input type="text" class="form-control" name="categorie" list="categories_list_autre" placeholder="Ex: Document, Article...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date</label>
                            <input type="date" class="form-control" name="date_media">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Description..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Crédits / Source</label>
                        <input type="text" class="form-control" name="credits" placeholder="Auteur, source...">
                    </div>

                    <div class="card border mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bx bx-share-alt me-2"></i>Options de partage</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input share-toggle" type="checkbox" name="a_partager_reseaux" value="1" data-target="create_msg_reseaux">
                                        <label class="form-check-label fw-bold">Partager sur réseaux</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3" id="create_msg_reseaux" style="display:none;">
                                <label class="form-label fw-bold">Message réseaux</label>
                                <textarea class="form-control" name="message_reseaux" rows="2" maxlength="280" placeholder="Texte à publier..."></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1">
                                        <label class="form-check-label fw-bold"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked>
                                        <label class="form-check-label fw-bold"><i class="bx bx-globe text-primary me-1"></i>Site Web</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Datalist catégories -->
<datalist id="categories_list_autre">
    <option value="Document">
    <option value="Article">
    <option value="Lien utile">
    <option value="Image">
    <option value="PDF">
    <option value="Note">
    <option value="Autre">
</datalist>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<!-- Scripts spécifiques pour la gestion Autre -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ==================== CONFIGURATION ====================
const CONFIG = {
    CHUNK_SIZE: 5 * 1024 * 1024,
    MAX_RETRIES: 3,
    RETRY_DELAY: 1000
};

// ==================== UTILITAIRES ====================
const Utils = {
    formatBytes: (bytes) => {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    
    showToast: (icon, title, text = '') => {
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
};

// ==================== CHUNKED UPLOAD MANAGER ====================
class ChunkedUploadManager {
    constructor(uploadId, dropZoneId, options = {}) {
        this.uploadId = uploadId;
        this.dropZone = document.getElementById(dropZoneId);
        if (!this.dropZone) {
            console.error('Drop zone not found:', dropZoneId);
            return;
        }
        
        this.fileInput = this.dropZone.querySelector('.file-input');
        this.options = { accept: '*/*', ...options };
        
        this.progressBar = this.dropZone.querySelector('.progress-bar') || document.getElementById(`${uploadId}_progress_bar`);
        this.progressContainer = this.dropZone.querySelector('.upload-progress') || document.getElementById(`${uploadId}_progress_container`);
        this.statusText = document.getElementById(`${uploadId}_upload_status`);
        this.percentText = document.getElementById(`${uploadId}_upload_percent`);
        
        this.currentFile = null;
        this.uploadSessionId = null;
        this.isPaused = false;
        this.isUploading = false;
        this.uploadedChunks = [];
        this.totalChunks = 0;
        this.abortController = null;
        
        this.init();
    }
    
    init() {
        if (this.options.accept && this.options.accept !== '*/*') {
            this.fileInput.setAttribute('accept', this.options.accept);
        }
        
        this.dropZone.addEventListener('click', (e) => {
            if (e.target === this.fileInput || e.target.closest('button') || e.target.closest('.progress')) return;
            this.fileInput.click();
        });
        
        this.fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) this.handleFile(e.target.files[0]);
        });
        
        this.dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.dropZone.classList.add('border-primary', 'bg-light');
        });
        
        this.dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            this.dropZone.classList.remove('border-primary', 'bg-light');
        });
        
        this.dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            this.dropZone.classList.remove('border-primary', 'bg-light');
            if (e.dataTransfer.files.length > 0) this.handleFile(e.dataTransfer.files[0]);
        });
        
        // Boutons contrôle
        const parent = this.dropZone.closest('.type-section') || this.dropZone.parentElement;
        if (parent) {
            const cancelBtn = parent.querySelector('.cancel-upload') || document.getElementById(`${this.uploadId}_cancel`);
            const pauseBtn = parent.querySelector('.pause-upload') || document.getElementById(`${this.uploadId}_pause`);
            const resumeBtn = parent.querySelector('.resume-upload') || document.getElementById(`${this.uploadId}_resume`);
            
            if (cancelBtn) cancelBtn.addEventListener('click', (e) => { e.stopPropagation(); this.cancelUpload(); });
            if (pauseBtn) pauseBtn.addEventListener('click', (e) => { e.stopPropagation(); this.pauseUpload(); });
            if (resumeBtn) resumeBtn.addEventListener('click', (e) => { e.stopPropagation(); this.resumeUpload(); });
        }
    }
    
    handleFile(file) {
        if (this.options.accept && this.options.accept !== '*/*') {
            const acceptedTypes = this.options.accept.split(',');
            const isAccepted = acceptedTypes.some(type => {
                type = type.trim();
                if (type.startsWith('.')) return file.name.toLowerCase().endsWith(type.toLowerCase());
                if (type.includes('/*')) return file.type.startsWith(type.replace('/*', ''));
                return file.type === type;
            });
            
            if (!isAccepted) {
                Utils.showToast('error', 'Format non supporté', `Accepté: ${this.options.accept}`);
                return;
            }
        }
        
        this.currentFile = file;
        
        const fileInfo = document.getElementById(`${this.uploadId}_file_info`);
        const fileName = document.getElementById(`${this.uploadId}_file_name`);
        const fileSize = document.getElementById(`${this.uploadId}_file_size`);
        
        if (fileInfo) {
            if (fileName) fileName.textContent = file.name;
            if (fileSize) fileSize.textContent = Utils.formatBytes(file.size);
            fileInfo.classList.remove('d-none');
        }
        
        this.startUpload();
    }
    
    async startUpload() {
        if (!this.currentFile) return;
        this.isUploading = true;
        this.isPaused = false;
        this.abortController = new AbortController();
        
        try {
            await this.initUpload();
            await this.uploadChunks();
            await this.completeUpload();
        } catch (error) {
            console.error('Upload error:', error);
            if (error.message !== 'Upload annulé') Utils.showToast('error', 'Erreur upload', error.message);
        }
    }
    
    async initUpload() {
        const formData = new FormData();
        formData.append('file_name', this.currentFile.name);
        formData.append('file_size', this.currentFile.size);
        
        const response = await fetch('<?= base_url("autre/initUpload") ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Erreur initialisation');
        
        this.uploadSessionId = data.upload_id;
        this.totalChunks = data.total_chunks;
        
        if (this.progressContainer) this.progressContainer.classList.remove('d-none');
        
        const cancelBtn = document.getElementById(`${this.uploadId}_cancel`) || this.dropZone.closest('.type-section')?.querySelector('.cancel-upload');
        const pauseBtn = document.getElementById(`${this.uploadId}_pause`) || this.dropZone.closest('.type-section')?.querySelector('.pause-upload');
        if (cancelBtn) cancelBtn.classList.remove('d-none');
        if (pauseBtn) pauseBtn.classList.remove('d-none');
        
        this.updateProgress(0);
    }
    
    async uploadChunks() {
        const chunkSize = CONFIG.CHUNK_SIZE;
        for (let i = 0; i < this.totalChunks; i++) {
            if (!this.isUploading) break;
            while (this.isPaused) await new Promise(r => setTimeout(r, 100));
            if (!this.isUploading) break;
            if (this.uploadedChunks.includes(i)) continue;
            
            const start = i * chunkSize;
            const end = Math.min(start + chunkSize, this.currentFile.size);
            const chunk = this.currentFile.slice(start, end);
            
            let retries = 0, success = false;
            while (retries < CONFIG.MAX_RETRIES && !success) {
                try {
                    await this.uploadChunk(i, chunk);
                    success = true;
                    this.uploadedChunks.push(i);
                    this.updateProgress((this.uploadedChunks.length / this.totalChunks) * 100);
                } catch (error) {
                    if (++retries >= CONFIG.MAX_RETRIES) throw new Error(`Échec chunk ${i}`);
                    await new Promise(r => setTimeout(r, CONFIG.RETRY_DELAY));
                }
            }
        }
    }
    
    async uploadChunk(index, chunk) {
        const formData = new FormData();
        formData.append('upload_id', this.uploadSessionId);
        formData.append('chunk_index', index);
        formData.append('chunk', chunk);
        
        const response = await fetch('<?= base_url("autre/uploadChunk") ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: this.abortController.signal
        });
        
        const data = await response.json();
        if (!data.success) throw new Error(data.message);
        return data;
    }
    
    async completeUpload() {
        if (!this.isUploading) return;
        
        const formData = new FormData();
        formData.append('upload_id', this.uploadSessionId);
        
        const response = await fetch('<?= base_url("autre/completeUpload") ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const data = await response.json();
        if (!data.success) throw new Error(data.message);
        
        const uploadedPath = document.getElementById(`${this.uploadId}_uploaded_path`);
        const miniaturePath = document.getElementById(`${this.uploadId}_miniature_path`);
        if (uploadedPath) uploadedPath.value = data.file_path;
        if (miniaturePath) miniaturePath.value = data.miniature || '';
        
        const successDiv = document.getElementById(`${this.uploadId}_upload_success`);
        if (successDiv) successDiv.classList.remove('d-none');
        
        Utils.showToast('success', 'Upload terminé!', data.file_size_formatted);
        
        // Masquer boutons
        ['cancel', 'pause', 'resume'].forEach(type => {
            const btn = document.getElementById(`${this.uploadId}_${type}`);
            if (btn) btn.classList.add('d-none');
        });
        
        this.isUploading = false;
    }
    
    pauseUpload() {
        this.isPaused = true;
        document.getElementById(`${this.uploadId}_pause`)?.classList.add('d-none');
        document.getElementById(`${this.uploadId}_resume`)?.classList.remove('d-none');
        if (this.statusText) this.statusText.textContent = 'En pause...';
    }
    
    resumeUpload() {
        this.isPaused = false;
        document.getElementById(`${this.uploadId}_pause`)?.classList.remove('d-none');
        document.getElementById(`${this.uploadId}_resume`)?.classList.add('d-none');
        if (this.statusText) this.statusText.textContent = 'Upload en cours...';
    }
    
    async cancelUpload() {
        this.isUploading = false;
        if (this.abortController) this.abortController.abort();
        
        if (this.uploadSessionId) {
            const formData = new FormData();
            formData.append('upload_id', this.uploadSessionId);
            try {
                await fetch('<?= base_url("autre/cancelUpload") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
            } catch (e) {}
        }
        
        if (this.progressContainer) this.progressContainer.classList.add('d-none');
        document.getElementById(`${this.uploadId}_file_info`)?.classList.add('d-none');
        document.getElementById(`${this.uploadId}_upload_success`)?.classList.add('d-none');
        
        ['cancel', 'pause', 'resume'].forEach(type => {
            document.getElementById(`${this.uploadId}_${type}`)?.classList.add('d-none');
        });
        
        this.fileInput.value = '';
        this.currentFile = null;
        this.uploadSessionId = null;
        this.uploadedChunks = [];
        
        Utils.showToast('info', 'Upload annulé');
    }
    
    updateProgress(percent) {
        if (this.progressBar) {
            this.progressBar.style.width = percent + '%';
            this.progressBar.setAttribute('aria-valuenow', percent);
        }
        if (this.percentText) this.percentText.textContent = Math.round(percent) + '%';
        if (this.statusText) this.statusText.textContent = percent >= 100 ? 'Finalisation...' : 'Upload...';
    }
}

// ==================== INITIALISATION ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initialisation Autre...');
    
    // ========== GESTION DU CHANGEMENT DE TYPE (INTELLIGENT) ==========
    const sousTypeSelect = document.getElementById('create_sous_type');
    const sections = {
        link: document.getElementById('section_link'),
        texte: document.getElementById('section_texte'),
        upload: document.getElementById('section_upload')
    };
    const fileInput = document.getElementById('create_file_input');
    const uploadInstructions = document.getElementById('upload_instructions');
    const fileTypes = document.getElementById('file_types');
    
    let currentUploader = null;
    
    const typeConfigs = {
        link: { section: 'link' },
        texte: { section: 'texte' },
        book: { 
            accept: '.pdf,.epub,.mobi,application/pdf',
            instructions: 'Glissez-déposez un PDF ou cliquez',
            types: 'Formats: PDF, EPUB, MOBI',
            section: 'upload'
        },
        photo: { 
            accept: 'image/*',
            instructions: 'Glissez-déposez une image ou cliquez',
            types: 'Formats: JPG, PNG, GIF, WEBP',
            section: 'upload'
        },
        other: { 
            accept: '*/*',
            instructions: 'Glissez-déposez un fichier ou cliquez',
            types: 'Tous formats acceptés',
            section: 'upload'
        }
    };
    
    function showSection(type) {
        console.log('Changement type:', type);
        
        // Cacher tout
        Object.values(sections).forEach(sec => {
            if (sec) {
                sec.classList.add('d-none');
                sec.querySelectorAll('[required]').forEach(el => {
                    el.removeAttribute('required');
                    el.dataset.wasReq = 'true';
                });
            }
        });
        
        if (!type || !typeConfigs[type]) return;
        
        const config = typeConfigs[type];
        const target = sections[config.section];
        
        if (target) {
            target.classList.remove('d-none');
            
            // Remettre required
            target.querySelectorAll('[data-wasReq="true"]').forEach(el => {
                el.setAttribute('required', '');
                delete el.dataset.wasReq;
            });
            
            // Config upload
            if (config.section === 'upload') {
                if (fileInput) fileInput.setAttribute('accept', config.accept);
                if (uploadInstructions) uploadInstructions.innerHTML = `${config.instructions} <span class="text-primary fw-bold">pour parcourir</span>`;
                if (fileTypes) fileTypes.textContent = config.types;
                
                // Créer uploader si pas existant
                if (!currentUploader) {
                    currentUploader = new ChunkedUploadManager('create', 'drop_zone_create', { accept: config.accept });
                } else {
                    currentUploader.options.accept = config.accept;
                }
            }
            
            // Animation
            target.style.opacity = '0';
            setTimeout(() => {
                target.style.transition = 'opacity 0.3s ease';
                target.style.opacity = '1';
            }, 10);
        }
    }
    
    if (sousTypeSelect) {
        sousTypeSelect.addEventListener('change', (e) => showSection(e.target.value));
        // Initialiser si déjà sélectionné
        if (sousTypeSelect.value) showSection(sousTypeSelect.value);
    }
    
    // ========== DATATABLE ==========
    if (typeof $.fn.DataTable !== 'undefined') {
        const table = $('#autreTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
            order: [[0, 'desc']],
            pageLength: 10
        });
        
        // Filtres
        document.querySelectorAll('.filter-type').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-type').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const filter = this.dataset.filter;
                table.column(1).search(filter === 'all' ? '' : filter).draw();
            });
        });
    }
    
    // ========== UPLOADS EDITION ==========
    document.querySelectorAll('.upload-zone[id^="drop_zone_"]').forEach(zone => {
        const id = zone.id.replace('drop_zone_', '');
        if (id !== 'create') new ChunkedUploadManager(id, zone.id);
    });
    
    // ========== TOGGLES ==========
    document.querySelectorAll('.share-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const target = document.getElementById(this.dataset.target);
            if (target) target.style.display = this.checked ? 'block' : 'none';
        });
    });
    
    document.querySelectorAll('.toggle-field').forEach(toggle => {
        toggle.addEventListener('change', async function() {
            try {
                const res = await fetch('<?= base_url("autre/toggleField") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `id=${this.dataset.id}&field=${this.dataset.field}&value=${this.checked ? 1 : 0}`
                });
                const data = await res.json();
                if (data.success) Utils.showToast('success', 'Statut mis à jour');
                else throw new Error();
            } catch (e) {
                Utils.showToast('error', 'Erreur', 'Mise à jour impossible');
                this.checked = !this.checked;
            }
        });
    });
    
    // ========== VALIDATION FORMULAIRE ==========
    document.querySelectorAll('.autre-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const mode = this.dataset.mode;
            const type = this.querySelector('[name="sous_type"]')?.value;
            
            if (mode === 'create' && !type) {
                e.preventDefault();
                Utils.showToast('warning', 'Attention', 'Sélectionnez un type');
                return false;
            }
            
            // Validation spécifique
            if (type === 'link' && !this.querySelector('[name="lien"]')?.value.trim()) {
                e.preventDefault();
                Utils.showToast('warning', 'Attention', 'Entrez une URL');
                return false;
            }
            
            if (['book', 'photo', 'other'].includes(type) && !this.querySelector('[name="uploaded_file_path"]')?.value) {
                e.preventDefault();
                Utils.showToast('warning', 'Attention', 'Uploadez un fichier');
                return false;
            }
        });
    });
    
    // ========== RESET MODAL ==========
    const createModal = document.getElementById('create_autre');
    if (createModal) {
        createModal.addEventListener('hidden.bs.modal', function() {
            this.querySelector('form')?.reset();
            Object.values(sections).forEach(s => {
                if (s) {
                    s.classList.add('d-none');
                    s.style.opacity = '';
                }
            });
            ['file_info', 'upload_success', 'progress_container', 'uploaded_path'].forEach(id => {
                const el = document.getElementById(`create_${id}`);
                if (el) {
                    if (id === 'uploaded_path') el.value = '';
                    else el.classList.add('d-none');
                }
            });
            if (fileInput) fileInput.value = '';
        });
    }
    
    console.log('Initialisation terminée');
});
</script>