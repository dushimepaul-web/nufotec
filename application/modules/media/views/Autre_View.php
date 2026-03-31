<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
if (!function_exists('format_bytes_autre')) {
    function format_bytes_autre($bytes, $decimals = 2) {
        if (empty($bytes) || $bytes === 0) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), $decimals) . ' ' . $sizes[$i];
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
                        <li class="breadcrumb-item active" aria-current="page">Documents & Fichiers</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a class="btn btn-primary btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bx bx-upload"></i> <span class="d-none d-sm-inline">Nouveau</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Dashboard -->
        <div class="row mb-4 g-3">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-primary bg-opacity-10">
                    <div class="card-body text-center">
                        <i class="bx bx-collection text-primary fs-2 mb-2"></i>
                        <h4 class="mb-0 fw-bold"><?= $stats['total'] ?></h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
            <?php foreach ($type_configs as $key => $cfg): ?>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-<?= $cfg['color'] ?> bg-opacity-10">
                    <div class="card-body text-center">
                        <i class="bx <?= $cfg['icon'] ?> text-<?= $cfg['color'] ?> fs-2 mb-2"></i>
                        <h4 class="mb-0 fw-bold"><?= $stats['by_type'][$key] ?? 0 ?></h4>
                        <small class="text-muted"><?= $cfg['label'] ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100 bg-dark bg-opacity-10">
                    <div class="card-body text-center">
                        <i class="bx bx-hdd text-dark fs-2 mb-2"></i>
                        <h4 class="mb-0 fw-bold"><?= $stats['total_size_formatted'] ?></h4>
                        <small class="text-muted">Stockage</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Grid -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0"><i class="bx bx-folder-open me-2 text-primary"></i>Bibliothèque de Fichiers</h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="filterType" style="width: auto;">
                            <option value="">Tous les types</option>
                            <?php foreach ($type_configs as $key => $cfg): ?>
                                <option value="<?= $key ?>"><?= $cfg['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="itemsTable" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="8%">Type</th>
                                <th width="10%">Aperçu</th>
                                <th width="30%">Détails</th>
                                <th width="12%">Infos</th>
                                <th width="10%">Statut</th>
                                <th width="8%">Visibilité</th>
                                <th width="7%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($items)): foreach ($items as $item): 
                            $cfg = $item['type_config'];
                            $js_title = htmlspecialchars($item['titre'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr data-id="<?= $item['id_media'] ?>" data-type="<?= $item['sous_type'] ?>">
                                <td>
                                    <span class="badge bg-<?= $cfg['color'] ?> d-flex align-items-center gap-1 w-fit-content">
                                        <i class="bx <?= $cfg['icon'] ?>"></i>
                                        <span class="d-none d-md-inline"><?= $cfg['label'] ?></span>
                                    </span>
                                </td>

                                <td>
                                    <div class="item-thumb-wrapper" style="width: 80px; height: 60px;">
                                        <img src="<?= $item['thumbnail_url'] ?>" 
                                             class="rounded w-100 h-100" 
                                             style="object-fit: cover; background: #f8f9fa;"
                                             loading="lazy"
                                             onerror="this.src='<?= base_url('assets/images/file-default.png') ?>'">
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <h6 class="mb-1 fw-bold text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($item['titre'] ?? '') ?>">
                                            <?= htmlspecialchars($item['titre'] ?? 'Sans titre') ?>
                                        </h6>
                                        
                                        <?php if (!empty($item['categorie'])): ?>
                                            <span class="badge bg-light text-dark border w-fit-content mb-1" style="font-size: 0.7rem;">
                                                <?= htmlspecialchars($item['categorie']) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($item['description'])): ?>
                                            <small class="text-muted text-truncate" style="max-width: 350px; font-size: 0.75rem;">
                                                <?= htmlspecialchars($item['description']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <small class="text-muted">
                                            <i class="bx bx-hdd me-1"></i><?= $item['taille_formatee'] ?>
                                        </small>
                                        <?php if (!empty($item['date_media'])): ?>
                                            <small class="text-muted">
                                                <i class="bx bx-calendar me-1"></i><?= date('d/m/Y', strtotime($item['date_media'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="dropdown">
                                        <a href="javascript:void(0)" class="text-decoration-none" data-bs-toggle="dropdown">
                                            <?php if (!empty($item['est_actif']) && $item['est_actif'] == 1): ?>
                                                <span class="badge bg-success">Actif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactif</span>
                                            <?php endif; ?>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="toggleStatus(<?= $item['id_media'] ?>, 1)">
                                                    <i class="bx bx-check-circle me-2 text-success"></i>Activer
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="toggleStatus(<?= $item['id_media'] ?>, 0)">
                                                    <i class="bx bx-x-circle me-2 text-secondary"></i>Désactiver
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column gap-2 align-items-center">
                                        <div class="form-check form-switch form-check-sm">
                                            <input class="form-check-input" type="checkbox" 
                                                   data-id="<?= $item['id_media'] ?>" data-field="is_for_whatsapp"
                                                   <?= (!empty($item['is_for_whatsapp']) && $item['is_for_whatsapp'] == 1) ? 'checked' : '' ?>
                                                   title="WhatsApp">
                                        </div>
                                        <div class="form-check form-switch form-check-sm">
                                            <input class="form-check-input" type="checkbox" 
                                                   data-id="<?= $item['id_media'] ?>" data-field="is_for_website"
                                                   <?= (!empty($item['is_for_website']) && $item['is_for_website'] == 1) ? 'checked' : '' ?>
                                                   title="Site Web">
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="btn-group">
                                        <?php if ($item['sous_type'] === 'link' && !empty($item['lien'])): ?>
                                            <a href="<?= htmlspecialchars($item['lien']) ?>" target="_blank" class="btn btn-sm btn-light" title="Ouvrir">
                                                <i class="bx bx-link-external text-primary"></i>
                                            </a>
                                        <?php elseif (!empty($item['fichier'])): ?>
                                            <a href="<?= base_url($item['fichier']) ?>" download class="btn btn-sm btn-light" title="Télécharger">
                                                <i class="bx bx-download text-primary"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editModal<?= $item['id_media'] ?>" title="Modifier">
                                            <i class="bx bx-edit text-primary"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-light" onclick="confirmDelete(<?= $item['id_media'] ?>, '<?= $js_title ?>')" title="Supprimer">
                                            <i class="bx bx-trash text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal Simplifié -->
                            <div class="modal fade" id="editModal<?= $item['id_media'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-<?= $cfg['color'] ?> text-white">
                                            <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier <?= $cfg['label'] ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('autre/Update') ?>" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $item['id_media'] ?>">
                                                
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control form-control-lg" name="titre" 
                                                                   value="<?= htmlspecialchars($item['titre'] ?? '') ?>" required maxlength="255">
                                                        </div>

                                                        <?php if ($item['sous_type'] === 'link'): ?>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">URL <span class="text-danger">*</span></label>
                                                                <input type="url" class="form-control" name="lien" 
                                                                       value="<?= htmlspecialchars($item['lien'] ?? '') ?>" required>
                                                                <small class="text-muted">La miniature sera extraite automatiquement</small>
                                                            </div>
                                                        <?php elseif ($item['sous_type'] === 'texte'): ?>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Contenu</label>
                                                                <textarea class="form-control" name="contenu_texte" rows="6"><?= htmlspecialchars($item['contenu_texte'] ?? '') ?></textarea>
                                                            </div>
                                                        <?php endif; ?>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Description</label>
                                                            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Catégorie</label>
                                                                <input type="text" class="form-control" name="categorie" 
                                                                       value="<?= htmlspecialchars($item['categorie'] ?? '') ?>" list="categoriesList">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-bold">Date</label>
                                                                <input type="date" class="form-control" name="date_media" value="<?= $item['date_media'] ?? '' ?>">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Crédits</label>
                                                            <input type="text" class="form-control" name="credits" 
                                                                   value="<?= htmlspecialchars($item['credits'] ?? '') ?>">
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
                                                                               <?= (!empty($item['est_actif']) && $item['est_actif'] == 1) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label fw-bold">Élément actif</label>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1"
                                                                               <?= (!empty($item['is_for_whatsapp']) && $item['is_for_whatsapp'] == 1) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" name="is_for_website" value="1"
                                                                               <?= (!empty($item['is_for_website']) && $item['is_for_website'] == 1) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label"><i class="bx bx-globe text-primary me-1"></i>Site Web</label>
                                                                    </div>
                                                                </div>

                                                                <?php if (!empty($item['fichier'])): ?>
                                                                <hr>
                                                                <h6 class="mb-2"><i class="bx bx-file me-1"></i>Fichier</h6>
                                                                <ul class="list-unstyled small text-muted mb-0">
                                                                    <li><strong>Nom:</strong> <?= basename($item['fichier']) ?></li>
                                                                    <li><strong>Taille:</strong> <?= $item['taille_formatee'] ?></li>
                                                                    <li><strong>Type:</strong> <?= $item['mime_type'] ?? 'N/A' ?></li>
                                                                </ul>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-<?= $cfg['color'] ?>"><i class="bx bx-save me-1"></i>Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bx bx-folder-open fs-1 text-muted mb-3"></i>
                                        <h5>Aucun document</h5>
                                        <p class="text-muted">Commencez par ajouter votre premier fichier</p>
                                        <a href="javascript:;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                            <i class="bx bx-upload me-1"></i>Ajouter
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

    <!-- Upload Modal Simplifié -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title"><i class="bx bx-upload me-2"></i>Nouveau document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeUploadModal"></button>
                </div>
                <div class="modal-body p-0">
                    
                    <!-- Step 1: Select Type -->
                    <div id="uploadStep1" class="p-5">
                        <h5 class="mb-4 text-center">Choisissez le type de contenu</h5>
                        <div class="row g-3">
                            <?php foreach ($type_configs as $key => $cfg): ?>
                            <div class="col-md-4 col-6">
                                <div class="card type-card h-100 cursor-pointer border-2" 
                                     data-type="<?= $key ?>"
                                     onclick="selectType('<?= $key ?>')"
                                     style="border-color: transparent; transition: all 0.2s;">
                                    <div class="card-body text-center py-4">
                                        <i class="bx <?= $cfg['icon'] ?> display-4 text-<?= $cfg['color'] ?> mb-2"></i>
                                        <h6 class="mb-0"><?= $cfg['label'] ?></h6>
                                        <?php if ($cfg['has_file'] && $cfg['max_size'] > 0): ?>
                                            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                                Max: <?= format_bytes_autre($cfg['max_size']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 2: Upload/Form -->
                    <div id="uploadStep2" class="d-none">
                        <form id="itemDetailsForm" action="<?= base_url('autre/Create') ?>" method="POST">
                            <input type="hidden" name="sous_type" id="selectedType">
                            
                            <div class="p-3 border-bottom bg-light d-flex align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="backToStep1()">
                                    <i class="bx bx-arrow-back me-1"></i>Retour
                                </button>
                                <span class="ms-3 badge bg-primary" id="typeBadge">Type</span>
                            </div>

                            <div class="row g-0">
                                <div class="col-md-8 p-4 border-end">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" name="titre" id="itemTitle" required maxlength="255">
                                    </div>

                                    <!-- LINK Fields -->
                                    <div id="linkFields" class="d-none">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">URL <span class="text-danger">*</span></label>
                                            <input type="url" class="form-control" name="lien" id="itemLink" placeholder="https://...">
                                            <small class="text-muted">La miniature sera extraite automatiquement (YouTube, Vimeo, favicon)</small>
                                        </div>
                                    </div>

                                    <!-- TEXTE Fields -->
                                    <div id="texteFields" class="d-none">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Contenu</label>
                                            <textarea class="form-control" name="contenu_texte" id="itemContent" rows="6"></textarea>
                                        </div>
                                    </div>

                                    <!-- FILE Upload Fields -->
                                    <div id="fileFields" class="d-none">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Fichier <span class="text-danger">*</span></label>
                                            
                                            <div id="dropZone" class="upload-zone border rounded-3 p-5 text-center bg-light position-relative">
                                                <input type="file" id="fileInput" class="d-none">
                                                <input type="hidden" name="uploaded_file_path" id="uploadedFilePath">
                                                
                                                <!-- Initial State -->
                                                <div id="uploadInitial">
                                                    <i class="bx bx-cloud-upload display-3 text-primary mb-3"></i>
                                                    <h5>Glissez-déposez votre fichier ici</h5>
                                                    <p class="text-muted mb-2">ou <span class="text-primary fw-bold cursor-pointer" onclick="document.getElementById('fileInput').click()">cliquez pour parcourir</span></p>
                                                    <div id="fileConstraints" class="badge bg-info">Chargement...</div>
                                                </div>

                                                <!-- Progress -->
                                                <div id="uploadProgress" class="d-none">
                                                    <div class="mb-3">
                                                        <div class="progress" style="height: 25px;">
                                                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-muted small mb-2">
                                                        <span id="uploadPhase">Préparation...</span>
                                                        <span id="uploadPercent">0%</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-muted small">
                                                        <span id="uploadSpeed">0 MB/s</span>
                                                        <span id="uploadChunks">0 / 0 chunks</span>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm mt-3" onclick="resetUpload()">
                                                        <i class="bx bx-x me-1"></i>Annuler
                                                    </button>
                                                </div>

                                                <!-- Success -->
                                                <div id="uploadSuccess" class="d-none">
                                                    <i class="bx bx-check-circle display-3 text-success mb-2"></i>
                                                    <h5 class="text-success">Upload terminé !</h5>
                                                    <p id="fileInfo" class="mb-3"></p>
                                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="resetUpload()">
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

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Catégorie</label>
                                                <input type="text" class="form-control" name="categorie" id="itemCategory" list="categoriesList">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea class="form-control" name="description" rows="3" id="itemDescription"></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 bg-light p-4">
                                    <h6 class="mb-3"><i class="bx bx-cog me-2"></i>Paramètres</h6>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="est_actif" value="1" checked>
                                            <label class="form-check-label fw-bold">Publier immédiatement</label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1">
                                            <label class="form-check-label"><i class="bx bxl-whatsapp text-success me-1"></i>WhatsApp</label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked>
                                            <label class="form-check-label"><i class="bx bx-globe text-primary me-1"></i>Site Web</label>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <h6 class="mb-2"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                                    <ul class="list-unstyled small text-muted mb-0" id="itemInfoList">
                                        <li>Sélectionnez un fichier pour voir les détails</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="p-4 border-top bg-white d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetUpload()">
                                    <i class="bx bx-arrow-back me-1"></i>Annuler
                                </button>
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Supprimer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= base_url('autre/Delete') ?>" method="POST">
                    <div class="modal-body text-center py-4">
                        <i class="bx bx-error-circle text-danger display-4 mb-3"></i>
                        <h5>Confirmer la suppression</h5>
                        <p class="text-muted" id="deleteItemTitle"></p>
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-2"></i>
                            Cette action supprimera définitivement l'élément et tous les fichiers associés.
                        </div>
                        <input type="hidden" name="id" id="deleteItemId">
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

    <!-- Datalist -->
    <datalist id="categoriesList">
        <option value="Documentation">
        <option value="Ressources">
        <option value="Articles">
        <option value="Galerie">
        <option value="Divers">
        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>">
        <?php endforeach; endif; ?>
    </datalist>

</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
// Configuration
const UPLOAD_CONFIG = {
    baseUrl: '<?= base_url('autre/') ?>',
    chunkSize: 5 * 1024 * 1024,
    typeConfigs: <?= json_encode($type_configs) ?>
};

let uploadManager = null;

// Utils
class FileUtils {
    static formatBytes(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

// Upload Manager
class ItemUploadManager {
    constructor() {
        this.reset();
    }
    
    reset() {
        this.state = {
            file: null,
            uploadId: null,
            totalChunks: 0,
            uploadedChunks: new Set(),
            isUploading: false,
            startTime: null,
            bytesUploaded: 0
        };
    }
    
    async start(file, sousType) {
        try {
            this.reset();
            this.state.file = file;
            this.state.isUploading = true;
            this.state.startTime = Date.now();
            
            const config = UPLOAD_CONFIG.typeConfigs[sousType];
            
            if (config.max_size > 0 && file.size > config.max_size) {
                throw new Error(`Fichier trop grand. Maximum: ${FileUtils.formatBytes(config.max_size)}`);
            }
            
            this.updateUI('init', { fileName: file.name, fileSize: FileUtils.formatBytes(file.size) });
            
            const initData = await this.apiCall('initUpload', {
                file_name: file.name,
                file_size: file.size,
                sous_type: sousType
            });
            
            this.state.uploadId = initData.upload_id;
            this.state.totalChunks = initData.total_chunks;
            
            this.updateUI('uploading', { percent: 0 });
            await this.uploadChunks();
            
            const result = await this.apiCall('completeUpload', {
                upload_id: this.state.uploadId
            });
            
            this.updateUI('complete', result.data);
            
        } catch (error) {
            console.error('Upload error:', error);
            this.updateUI('error', { message: error.message });
        } finally {
            this.state.isUploading = false;
        }
    }
    
    async apiCall(endpoint, data) {
        const formData = new FormData();
        for (let key in data) formData.append(key, data[key]);
        
        const response = await fetch(UPLOAD_CONFIG.baseUrl + endpoint, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        if (!response.ok) {
            const text = await response.text();
            throw new Error(`Erreur HTTP ${response.status}`);
        }
        
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Erreur serveur');
        return result;
    }
    
    async uploadChunks() {
        const queue = [];
        for (let i = 0; i < this.state.totalChunks; i++) {
            queue.push(i);
        }
        
        const workers = [];
        const maxWorkers = Math.min(3, queue.length);
        for (let w = 0; w < maxWorkers; w++) {
            workers.push(this.worker(queue));
        }
        await Promise.all(workers);
    }
    
    async worker(queue) {
        while (queue.length > 0) {
            await this.uploadChunk(queue.shift());
        }
    }
    
    async uploadChunk(index, attempt = 0) {
        try {
            const start = index * UPLOAD_CONFIG.chunkSize;
            const end = Math.min(start + UPLOAD_CONFIG.chunkSize, this.state.file.size);
            const chunk = this.state.file.slice(start, end);
            
            const formData = new FormData();
            formData.append('upload_id', this.state.uploadId);
            formData.append('chunk_index', index);
            formData.append('chunk', chunk);
            
            const response = await fetch(UPLOAD_CONFIG.baseUrl + 'uploadChunk', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            this.state.uploadedChunks.add(index);
            this.state.bytesUploaded += chunk.size;
            
            const progress = (this.state.uploadedChunks.size / this.state.totalChunks) * 100;
            const speed = this.calculateSpeed();
            
            this.updateUI('progress', {
                percent: progress,
                uploadedChunks: this.state.uploadedChunks.size,
                totalChunks: this.state.totalChunks,
                speed: speed
            });
            
        } catch (error) {
            if (attempt < 3) {
                await new Promise(r => setTimeout(r, 1000 * Math.pow(2, attempt)));
                await this.uploadChunk(index, attempt + 1);
            } else {
                throw error;
            }
        }
    }
    
    calculateSpeed() {
        const elapsed = (Date.now() - this.state.startTime) / 1000;
        return elapsed > 0 ? (this.state.bytesUploaded / elapsed) / (1024 * 1024) : 0;
    }
    
    updateUI(event, data = {}) {
        const handlers = {
            init: () => {
                $('#uploadInitial').addClass('d-none');
                $('#uploadProgress').removeClass('d-none');
            },
            uploading: () => {
                $('#uploadPhase').text('Upload en cours...');
            },
            progress: () => {
                const pct = Math.round(data.percent);
                $('#progressBar').css('width', pct + '%');
                $('#uploadPercent').text(pct + '%');
                $('#uploadChunks').text(`${data.uploadedChunks} / ${data.totalChunks} chunks`);
                $('#uploadSpeed').text(data.speed.toFixed(2) + ' MB/s');
            },
            complete: () => {
                $('#uploadProgress').addClass('d-none');
                $('#uploadSuccess').removeClass('d-none');
                populateDetailsForm(data);
            },
            error: () => {
                $('#uploadProgress').addClass('d-none');
                $('#uploadError').removeClass('d-none');
                $('#errorMsg').text(data.message);
                toastr.error(data.message, 'Erreur');
            }
        };
        
        if (handlers[event]) handlers[event]();
    }
}

// UI Functions
function populateDetailsForm(data) {
    if (data.form_suggestions) {
        $('#itemTitle').val(data.form_suggestions.titre || '');
        $('#itemCategory').val(data.form_suggestions.categorie || '');
    }
    
    let infoHtml = '';
    infoHtml += `<li><i class="bx bx-file me-2"></i>Nom: ${data.file_name || 'N/A'}</li>`;
    infoHtml += `<li><i class="bx bx-hdd me-2"></i>Taille: ${data.file_size || 'N/A'}</li>`;
    $('#itemInfoList').html(infoHtml);
    
    $('#uploadedFilePath').val(data.original_file || '');
    $('#btnSubmit').prop('disabled', false);
}

function selectType(type) {
    const config = UPLOAD_CONFIG.typeConfigs[type];
    
    $('#selectedType').val(type);
    $('#typeBadge').text(config.label).removeClass().addClass('ms-3 badge bg-' + config.color);
    
    $('#uploadStep1').addClass('d-none');
    $('#uploadStep2').removeClass('d-none');
    
    $('#linkFields').toggleClass('d-none', type !== 'link');
    $('#texteFields').toggleClass('d-none', type !== 'texte');
    $('#fileFields').toggleClass('d-none', !config.has_file);
    
            if (config.has_file) {
            const accept = config.accept === '*' ? '' : config.accept.map(e => '.' + e).join(',');
            $('#fileInput').attr('accept', accept);
            $('#fileConstraints').text(`Max: ${FileUtils.formatBytes(config.max_size)} | Types: ${config.accept === '*' ? 'Tous' : config.accept.join(', ')}`);
            resetFileUploadUI();
        } else {
            $('#btnSubmit').prop('disabled', false);
        }
    }

    function backToStep1() {
        if (uploadManager && uploadManager.state.isUploading) {
            if (!confirm('Un upload est en cours. Voulez-vous vraiment annuler ?')) {
                return;
            }
        }
        resetUpload();
    }

    function resetFileUploadUI() {
        $('#uploadInitial').removeClass('d-none');
        $('#uploadProgress').addClass('d-none');
        $('#uploadSuccess').addClass('d-none');
        $('#uploadError').addClass('d-none');
        $('#fileInput').val('');
        $('#btnSubmit').prop('disabled', true);
    }

    function resetUpload() {
        $('#uploadStep1').removeClass('d-none');
        $('#uploadStep2').addClass('d-none');
        
        $('#itemDetailsForm')[0].reset();
        $('#uploadedFilePath').val('');
        $('#fileInput').val('');
        
        $('#uploadInitial').removeClass('d-none');
        $('#uploadProgress').addClass('d-none');
        $('#uploadSuccess').addClass('d-none');
        $('#uploadError').addClass('d-none');
        
        $('#itemInfoList').html('<li>Sélectionnez un fichier pour voir les détails</li>');
        $('#btnSubmit').prop('disabled', true);
        
        if (uploadManager) {
            uploadManager.reset();
        }
    }

    function toggleStatus(id, status) {
        $.ajax({
            url: '<?= base_url('autre/ChangeStatus') ?>',
            type: 'POST',
            data: { id: id, est_actif: status },
            success: function(response) {
                if (response.success) {
                    toastr.success(status ? 'Élément activé' : 'Élément désactivé');
                    setTimeout(() => location.reload(), 500);
                } else {
                    toastr.error('Erreur lors du changement de statut');
                }
            },
            error: function() {
                toastr.error('Erreur serveur');
            }
        });
    }

    function confirmDelete(id, title) {
        $('#deleteItemId').val(id);
        $('#deleteItemTitle').text(title || 'Cet élément');
        $('#deleteModal').modal('show');
    }

    // Initialization
    $(document).ready(function() {
        // Toastr config
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };
        
        // DataTable
        if ($.fn.DataTable) {
            $('#itemsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                },
                order: [[0, 'desc']],
                pageLength: 25,
                columnDefs: [
                    { targets: [0, 4, 5, 6], orderable: false }
                ]
            });
        }
        
        // Upload manager
        uploadManager = new ItemUploadManager();
        
        // File input change
        $('#fileInput').on('change', function(e) {
            const files = e.target.files;
            const sousType = $('#selectedType').val();
            if (files.length > 0 && sousType) {
                uploadManager.start(files[0], sousType);
            }
        });
        
        // Drag & drop
        $('#dropZone').on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('border-primary');
        }).on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('border-primary');
        }).on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('border-primary');
            const files = e.originalEvent.dataTransfer.files;
            const sousType = $('#selectedType').val();
            if (files.length > 0 && sousType) {
                uploadManager.start(files[0], sousType);
            }
        });
        
        // Toggles
        $('.form-check-input[data-field]').on('change', function() {
            const id = $(this).data('id');
            const field = $(this).data('field');
            const value = $(this).is(':checked') ? 1 : 0;
            
            $.ajax({
                url: '<?= base_url('autre/toggleField') ?>',
                type: 'POST',
                data: { id: id, field: field, value: value },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Paramètre mis à jour');
                    } else {
                        toastr.error('Erreur mise à jour');
                    }
                },
                error: function() {
                    toastr.error('Erreur serveur');
                }
            });
        });
        
        // Form validation
        $('#itemDetailsForm').on('submit', function(e) {
            const titre = $('#itemTitle').val().trim();
            const type = $('#selectedType').val();
            
            if (!titre) {
                e.preventDefault();
                toastr.error('Le titre est obligatoire');
                $('#itemTitle').focus();
                return false;
            }
            
            if (type === 'link') {
                const lien = $('#itemLink').val().trim();
                if (!lien) {
                    e.preventDefault();
                    toastr.error("L'URL est obligatoire");
                    $('#itemLink').focus();
                    return false;
                }
            }
            
            return true;
        });
        
        // Filter
        $('#filterType').on('change', function() {
            const type = $(this).val();
            const table = $('#itemsTable').DataTable();
            
            if (type) {
                table.column(0).search(type).draw();
            } else {
                table.column(0).search('').draw();
            }
        });
    });
</script>

<style>
    .type-card { 
        transition: all 0.2s; 
        cursor: pointer; 
        border: 2px solid transparent;
    }
    .type-card:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
        border-color: #0d6efd;
    }
    .type-card.selected {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }
    .upload-zone { 
        transition: all 0.2s; 
        cursor: pointer;
    }
    .upload-zone.drag-over {
        background-color: rgba(13, 110, 253, 0.1) !important;
        border-color: #0d6efd !important;
    }
    .cursor-pointer { cursor: pointer; }
    .w-fit-content { width: fit-content; }
</style>