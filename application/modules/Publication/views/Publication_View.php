<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Gestion</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Documents Multimédias</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_document">
                <i class="bx bx-plus"></i> Nouveau Document
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

    <!-- Filtres par type -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group flex-wrap" role="group">
                <button type="button" class="btn btn-outline-primary active" onclick="filterDocuments('all', this)">Tous</button>
                <button type="button" class="btn btn-outline-danger" onclick="filterDocuments('VIDEO', this)"><i class="bx bx-video"></i> Vidéos</button>
                <button type="button" class="btn btn-outline-success" onclick="filterDocuments('AUDIO', this)"><i class="bx bx-music"></i> Audio</button>
                <button type="button" class="btn btn-outline-info" onclick="filterDocuments('PHOTO', this)"><i class="bx bx-image"></i> Photos</button>
                <button type="button" class="btn btn-outline-warning" onclick="filterDocuments('PDF', this)"><i class="bx bx-file-pdf"></i> PDF</button>
                <button type="button" class="btn btn-outline-secondary" onclick="filterDocuments('LIEN_WEB', this)"><i class="bx bx-link"></i> Liens</button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-folder-open me-2"></i>Bibliothèque de Documents</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="documentsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="8%">Type</th>
                            <th width="25%">Titre</th>
                            <th width="10%">Format</th>
                            <th width="10%">Taille</th>
                            <th width="12%">Publié par</th>
                            <th width="8%">Note</th>
                            <th width="8%">Statut</th>
                            <th width="14%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($documents)): $i = 1; foreach ($documents as $value): 
                        // Icônes et couleurs par type
                        $type_icons = [
                            'VIDEO' => ['icon' => 'bx-video', 'color' => 'danger', 'label' => 'Vidéo'],
                            'AUDIO' => ['icon' => 'bx-music', 'color' => 'success', 'label' => 'Audio'],
                            'PHOTO' => ['icon' => 'bx-image', 'color' => 'info', 'label' => 'Photo'],
                            'PDF' => ['icon' => 'bx-file-pdf', 'color' => 'warning', 'label' => 'PDF'],
                            'EBOOK' => ['icon' => 'bx-book', 'color' => 'primary', 'label' => 'E-Book'],
                            'LIEN_WEB' => ['icon' => 'bx-link', 'color' => 'secondary', 'label' => 'Lien Web'],
                            'DOCUMENT_TEXTE' => ['icon' => 'bx-file-text', 'color' => 'dark', 'label' => 'Texte'],
                            'TEXTE' => ['icon' => 'bx-text', 'color' => 'dark', 'label' => 'Texte'],
                            'ARCHIVE' => ['icon' => 'bx-archive', 'color' => 'dark', 'label' => 'Archive'],
                            'AUTRE' => ['icon' => 'bx-file', 'color' => 'light', 'label' => 'Autre']
                        ];
                        
                        $type_info = $type_icons[$value['type_document']] ?? $type_icons['AUTRE'];
                        
                        // Format taille
                        $taille_affichage = '-';
                        if (!empty($value['taille_octets'])) {
                            if ($value['taille_octets'] > 1073741824) {
                                $taille_affichage = round($value['taille_octets']/1073741824, 2) . ' GB';
                            } elseif ($value['taille_octets'] > 1048576) {
                                $taille_affichage = round($value['taille_octets']/1048576, 2) . ' MB';
                            } else {
                                $taille_affichage = round($value['taille_octets']/1024, 2) . ' KB';
                            }
                        }
                        
                        // Durée pour audio/video
                        $duree_affichage = '';
                        if (!empty($value['duree_secondes'])) {
                            $heures = floor($value['duree_secondes'] / 3600);
                            $minutes = floor(($value['duree_secondes'] % 3600) / 60);
                            $secondes = $value['duree_secondes'] % 60;
                            $duree_affichage = $heures > 0 ? sprintf('%02d:%02d:%02d', $heures, $minutes, $secondes) : sprintf('%02d:%02d', $minutes, $secondes);
                        }
                        
                        // Photo utilisateur
                        $user_photo = !empty($value['user_photo']) ? 'attachments/Users/'.$value['user_photo'] : 'attachments/Users/default-avatar.png';
                        
                        // Lien de partage WhatsApp
                        $share_text = "📄 " . $value['titre'];
                        if (!empty($value['description'])) {
                            $share_text .= "\n\n" . substr($value['description'], 0, 100) . (strlen($value['description']) > 100 ? '...' : '');
                        }
                        $share_text .= "\n\nVoir le document : ";
                        if (!empty($value['chemin_fichier'])) {
                            $share_text .= base_url($value['chemin_fichier']);
                        } elseif (!empty($value['url_source'])) {
                            $share_text .= $value['url_source'];
                        }
                        $whatsapp_url = "https://wa.me/?text=" . urlencode($share_text);
                    ?>
                        <tr data-type="<?= $value['type_document'] ?>">
                            <td><?= $i++ ?></td>
                            
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="bg-<?= $type_info['color'] ?> bg-opacity-10 rounded-circle p-2 mb-1">
                                        <i class="bx <?= $type_info['icon'] ?> text-<?= $type_info['color'] ?> fs-4"></i>
                                    </div>
                                    <small class="text-muted"><?= $type_info['label'] ?></small>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['titre']) ?></strong>
                                    <?php if (!empty($value['description'])): ?>
                                        <small class="text-muted text-truncate" style="max-width: 300px;"><?= htmlspecialchars($value['description']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($value['tags'])): ?>
                                        <div class="mt-1">
                                            <?php foreach (explode(',', $value['tags']) as $tag): ?>
                                                <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <?php if (!empty($value['format_fichier'])): ?>
                                    <span class="badge bg-dark">.<?= strtoupper($value['format_fichier']) ?></span>
                                <?php elseif ($value['type_document'] == 'LIEN_WEB'): ?>
                                    <span class="badge bg-secondary">URL</span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span><?= $taille_affichage ?></span>
                                    <?php if ($duree_affichage): ?>
                                        <small class="text-muted"><i class="bx bx-time me-1"></i><?= $duree_affichage ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= base_url($user_photo) ?>" 
                                         class="rounded-circle border me-2"
                                         style="width:30px; height:30px; object-fit:cover;"
                                         onerror="this.src='<?= base_url('attachments/Users/default-avatar.png') ?>'"
                                         alt="User">
                                    <div class="d-flex flex-column">
                                        <small class="fw-bold"><?= htmlspecialchars(($value['prenom'] ?? '').' '.($value['nom'] ?? '')) ?></small>
                                        <small class="text-muted"><?= $value['type_utilisateur'] ?? 'Utilisateur' ?></small>
                                    </div>
                                </div>
                            </td>

                            <td class="text-center">
                                <?php if (!empty($value['note_personnelle'])): ?>
                                    <div class="text-warning">
                                        <?php for ($j = 1; $j <= 5; $j++): ?>
                                            <i class="bx <?= $j <= $value['note_personnelle'] ? 'bxs-star' : 'bx-star' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_document'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['statut']) && $value['statut'] == 1): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactif</span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <?php if (!empty($value['chemin_fichier'])): ?>
                                        <a href="<?= base_url('Publication/Download/'.$value['id_document']) ?>" 
                                           class="btn btn-sm btn-outline-success" 
                                           title="Télécharger">
                                            <i class="bx bx-download"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($value['type_document'] == 'LIEN_WEB' && !empty($value['url_source'])): ?>
                                        <a href="<?= htmlspecialchars($value['url_source']) ?>" 
                                           target="_blank"
                                           class="btn btn-sm btn-outline-secondary" 
                                           title="Ouvrir le lien">
                                            <i class="bx bx-link-external"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="<?= $whatsapp_url ?>" 
                                       target="_blank"
                                       class="btn btn-sm btn-outline-success" 
                                       title="Partager sur WhatsApp">
                                        <i class="bx bxl-whatsapp"></i>
                                    </a>
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#view_<?= $value['id_document'] ?>"
                                            title="Voir détails">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#update_<?= $value['id_document'] ?>"
                                            title="Modifier">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#delete_<?= $value['id_document'] ?>"
                                            title="Supprimer">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_document'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-<?= $type_info['color'] ?> text-white">
                                        <h5 class="modal-title">
                                            <i class="bx <?= $type_info['icon'] ?> me-2"></i><?= $type_info['label'] ?> - Détails
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h4 class="mb-3"><?= htmlspecialchars($value['titre']) ?></h4>
                                                
                                                <?php if (!empty($value['description'])): ?>
                                                    <div class="mb-3">
                                                        <label class="text-muted small">Description</label>
                                                        <p><?= nl2br(htmlspecialchars($value['description'])) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="row g-3 mb-3">
                                                    <?php if (!empty($value['annee_publication'])): ?>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Année de publication</label>
                                                        <p class="fw-bold"><?= $value['annee_publication'] ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($value['isbn'])): ?>
                                                    <div class="col-6">
                                                        <label class="text-muted small">ISBN</label>
                                                        <p class="fw-bold font-monospace"><?= htmlspecialchars($value['isbn']) ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($value['format_fichier'])): ?>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Format</label>
                                                        <p class="fw-bold">.<?= strtoupper($value['format_fichier']) ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($value['taille_octets'])): ?>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Taille</label>
                                                        <p class="fw-bold"><?= $taille_affichage ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if (!empty($value['tags'])): ?>
                                                <div class="mb-3">
                                                    <label class="text-muted small">Tags</label>
                                                    <div>
                                                        <?php foreach (explode(',', $value['tags']) as $tag): ?>
                                                            <span class="badge bg-primary me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-4 border-start">
                                                <div class="text-center mb-3">
                                                    <div class="bg-<?= $type_info['color'] ?> bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                                                        <i class="bx <?= $type_info['icon'] ?> text-<?= $type_info['color'] ?> fs-1"></i>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="text-muted small">Publié par</label>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= base_url($user_photo) ?>" 
                                                             class="rounded-circle me-2"
                                                             style="width:40px; height:40px; object-fit:cover;">
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars(($value['prenom'] ?? '').' '.($value['nom'] ?? '')) ?></div>
                                                            <small class="text-muted"><?= $value['email'] ?? '' ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($value['note_personnelle'])): ?>
                                                <div class="mb-3">
                                                    <label class="text-muted small">Note personnelle</label>
                                                    <div class="text-warning fs-5">
                                                        <?php for ($j = 1; $j <= 5; $j++): ?>
                                                            <i class="bx <?= $j <= $value['note_personnelle'] ? 'bxs-star' : 'bx-star' ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <?php if ($value['type_document'] == 'PHOTO' && !empty($value['chemin_fichier'])): ?>
                                            <div class="mt-3 text-center">
                                                <img src="<?= base_url($value['chemin_fichier']) ?>" 
                                                     class="img-fluid rounded shadow-sm" 
                                                     style="max-height: 300px;"
                                                     alt="<?= htmlspecialchars($value['titre']) ?>">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <?php if (!empty($value['chemin_fichier'])): ?>
                                            <a href="<?= base_url('Publication/Download/'.$value['id_document']) ?>" class="btn btn-success">
                                                <i class="bx bx-download me-2"></i>Télécharger
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id_document'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le document</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Publication/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_document" value="<?= $value['id_document'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($value['titre']) ?>" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="type_document" required>
                                                        <?php 
                                                        $types = [
                                                            'VIDEO' => '🎬 Vidéo', 
                                                            'AUDIO' => '🎵 Audio', 
                                                            'PHOTO' => '📷 Photo', 
                                                            'LIEN_WEB' => '🔗 Lien Web', 
                                                            'PDF' => '📄 PDF', 
                                                            'EBOOK' => '📚 E-Book', 
                                                            'DOCUMENT_TEXTE' => '📝 Document Texte', 
                                                            'TEXTE' => '📝 Texte',
                                                            'ARCHIVE' => '📦 Archive', 
                                                            'AUTRE' => '📎 Autre'
                                                        ];
                                                        foreach ($types as $key => $label): 
                                                        ?>
                                                            <option value="<?= $key ?>" <?= $value['type_document'] == $key ? 'selected' : '' ?>><?= $label ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Description</label>
                                                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Tags (séparés par des virgules)</label>
                                                    <input type="text" class="form-control" name="tags" value="<?= htmlspecialchars($value['tags'] ?? '') ?>" placeholder="ex: important, travail, 2024">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold">Année</label>
                                                    <input type="number" class="form-control" name="annee_publication" value="<?= $value['annee_publication'] ?? '' ?>" min="1900" max="2099">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold">Note (1-5)</label>
                                                    <select class="form-select" name="note_personnelle">
                                                        <option value="">-</option>
                                                        <?php for ($n = 1; $n <= 5; $n++): ?>
                                                            <option value="<?= $n ?>" <?= ($value['note_personnelle'] ?? '') == $n ? 'selected' : '' ?>><?= $n ?> étoile<?= $n > 1 ? 's' : '' ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                                
                                                <?php if ($value['type_document'] == 'LIEN_WEB'): ?>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">URL <span class="text-danger">*</span></label>
                                                    <input type="url" class="form-control" name="url_source" value="<?= htmlspecialchars($value['url_source'] ?? '') ?>" required>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Nouveau fichier (laisser vide pour conserver l'actuel)</label>
                                                    <input type="file" class="form-control" name="fichier">
                                                    <?php if (!empty($value['chemin_fichier'])): ?>
                                                        <small class="text-muted">Actuel: <?= basename($value['chemin_fichier']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="col-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" name="statut" id="statut_<?= $value['id_document'] ?>" value="1" <?= (!empty($value['statut']) && $value['statut'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="statut_<?= $value['id_document'] ?>">Document actif</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bx bx-save me-2"></i>Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id_document'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer <strong><?= htmlspecialchars($value['titre']) ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible (suppression logique).</p>
                                    </div>
                                    <form action="<?= base_url('Publication/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_document'] ?>">
                                        <div class="modal-footer bg-light justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $value['id_document'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['statut']) && $value['statut'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['statut']) && $value['statut'] == 1) ? '<i class="bx bx-hide me-2"></i>Désactiver' : '<i class="bx bx-show me-2"></i>Activer' ?> le document
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['statut']) && $value['statut'] == 1) ? 'désactiver' : 'activer' ?></strong> le document <strong><?= htmlspecialchars($value['titre']) ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('Publication/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_document'] ?>">
                                        <input type="hidden" name="statut" value="<?= (!empty($value['statut']) && $value['statut'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['statut']) && $value['statut'] == 1) ? 'btn-warning' : 'btn-success' ?>">
                                                <?= (!empty($value['statut']) && $value['statut'] == 1) ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bx bx-folder-open text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun document trouvé</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_document">
                                    <i class="bx bx-plus me-2"></i>Ajouter un document
                                </button>
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

<!-- MODAL CREATE DOCUMENT - SANS ENREGISTREMENT AUDIO -->
<div class="modal fade" id="create_document" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouveau Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Tabs simplifiés : Upload et Lien uniquement -->
            <ul class="nav nav-tabs" id="createTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">📤 Upload fichier</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="link-tab" data-bs-toggle="tab" data-bs-target="#link" type="button" role="tab">🔗 Lien web</button>
                </li>
            </ul>

            <div class="tab-content p-3">
                <!-- Onglet Upload fichier -->
                <div class="tab-pane fade show active" id="upload" role="tabpanel">
                    <form action="<?= base_url('Publication/Create') ?>" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="titre" required placeholder="Nom du document">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="type_document" required>
                                    <option value="">Choisir...</option>
                                    <option value="VIDEO">🎬 Vidéo</option>
                                    <option value="AUDIO">🎵 Audio</option>
                                    <option value="PHOTO">📷 Photo</option>
                                    <option value="PDF">📄 PDF</option>
                                    <option value="EBOOK">📚 E-Book</option>
                                    <option value="DOCUMENT_TEXTE">📝 Document Texte</option>
                                    <option value="TEXTE">📝 Texte</option>
                                    <option value="ARCHIVE">📦 Archive</option>
                                    <option value="AUTRE">📎 Autre</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Description</label>
                                <textarea class="form-control" name="description" rows="2" placeholder="Description du contenu..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tags (séparés par des virgules)</label>
                                <input type="text" class="form-control" name="tags" placeholder="ex: important, travail, 2024">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Année</label>
                                <input type="number" class="form-control" name="annee_publication" min="1900" max="2099" placeholder="2024">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Note (1-5)</label>
                                <select class="form-select" name="note_personnelle">
                                    <option value="">-</option>
                                    <?php for ($n=1;$n<=5;$n++): ?>
                                        <option value="<?=$n?>"><?=$n?> étoile(s)</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Fichier <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="fichier" required>
                                <small class="text-muted">Formats acceptés selon le type choisi</small>
                            </div>
                        </div>
                        <div class="modal-footer bg-light px-0 pb-0 mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success"><i class="bx bx-save me-2"></i>Enregistrer</button>
                        </div>
                    </form>
                </div>

                <!-- Onglet Lien web -->
                <div class="tab-pane fade" id="link" role="tabpanel">
                    <form action="<?= base_url('Publication/Create') ?>" method="POST">
                        <input type="hidden" name="type_document" value="LIEN_WEB">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="titre" required placeholder="Nom du lien">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" name="url_source" required placeholder="https://...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Description</label>
                                <textarea class="form-control" name="description" rows="2" placeholder="Description du lien..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tags</label>
                                <input type="text" class="form-control" name="tags" placeholder="web, reference, lien">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Année</label>
                                <input type="number" class="form-control" name="annee_publication" min="1900" max="2099">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Note</label>
                                <select class="form-select" name="note_personnelle">
                                    <option value="">-</option>
                                    <?php for ($n=1;$n<=5;$n++): ?>
                                        <option value="<?=$n?>"><?=$n?> étoile(s)</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer bg-light px-0 pb-0 mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success"><i class="bx bx-save me-2"></i>Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#documentsTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });

    // Auto-hide alerts
    setTimeout(() => $('.alert').fadeOut('slow'), 5000);
});

// Filtre par type
function filterDocuments(type, element) {
    var table = $('#documentsTable').DataTable();
    if (type === 'all') {
        table.column(1).search('').draw();
    } else {
        table.column(1).search(type).draw();
    }
    
    // Mise à jour boutons actifs
    $(element).siblings().removeClass('active');
    $(element).addClass('active');
}
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>