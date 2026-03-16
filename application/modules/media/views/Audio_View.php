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
                        <li class="breadcrumb-item active" aria-current="page">Gestion des Audio</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a class="btn btn-primary btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_audio">
                    <i class="bx bx-plus"></i> Nouvel Audio
                </a>
                <a class="btn btn-outline-info btn-sm ms-2" href="<?= base_url('audio/diagnostics') ?>" target="_blank">
                    <i class="bx bx-test-tube"></i> Diagnostic
                </a>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3"><i class="bx bx-music"></i></div>
                            <div>
                                <h6 class="mb-0">Total Audio</h6>
                                <h3 class="mb-0"><?= count($audios) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3"><i class="bx bx-time"></i></div>
                            <div>
                                <h6 class="mb-0">Durée Totale</h6>
                                <h3 class="mb-0">
    <?php
    $seconds = (int)$total_duration;
    if ($seconds < 60) {
        echo gmdate("s\\s", $seconds);
    } elseif ($seconds < 3600) {
        echo gmdate("i\\m s\\s", $seconds);
    } else {
        echo gmdate("H\\h i\\m s\\s", $seconds);
    }
    ?>
</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3"><i class="bx bx-headphone"></i></div>
                            <div>
                                <h6 class="mb-0">Écoutes</h6>
                                <h3 class="mb-0">-</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3"><i class="bx bx-trending-up"></i></div>
                            <div>
                                <h6 class="mb-0">Populaire</h6>
                                <h3 class="mb-0">-</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Flash -->
        <?php $this->load->view('includes/backend/FlashMessages.php'); ?>

        <!-- Card Principale -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 text-primary"><i class="bx bx-music me-2"></i>Bibliothèque Audio</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Upload Illimité</span>
                        <span class="badge bg-info"><i class="bx bx-wave me-1"></i>Waveform</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="audioTable" class="table table-hover align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Titre & Waveform</th>
                                <th width="10%">Source</th>
                                <th width="10%">Durée</th>
                                <th width="10%">Qualité</th>
                                <th width="8%">Taille</th>
                                <th width="8%">Statut</th>
                                <th width="8%">WhatsApp</th>
                                <th width="8%">Site</th>
                                <th width="8%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($audios)): $i = 1; foreach ($audios as $value): 
                            $is_upload = !empty($value['fichier']);
                            $is_link = !empty($value['lien']);
                            
                            $source_badge = $is_upload 
                                ? '<span class="badge bg-success"><i class="bx bx-upload me-1"></i>Upload</span>'
                                : ($is_link 
                                    ? '<span class="badge bg-info"><i class="bx bx-link me-1"></i>Lien</span>'
                                    : '<span class="badge bg-secondary">Inconnu</span>');
                            
                            // Formatage durée
                            $duree_formatee = '-';
                            if (!empty($value['duree'])) {
                                $duree = (int)$value['duree'];
                                if ($duree < 60) {
                                    $duree_formatee = $duree . 's';
                                } elseif ($duree < 3600) {
                                    $duree_formatee = floor($duree / 60) . 'm ' . ($duree % 60) . 's';
                                } else {
                                    $duree_formatee = floor($duree / 3600) . 'h ' . floor(($duree % 3600) / 60) . 'm';
                                }
                            }
                            
                            // Formatage taille
                            $taille_formatee = '-';
                            if (!empty($value['taille'])) {
                                $taille = $value['taille'];
                                $taille_formatee = $taille >= 1048576 
                                    ? number_format($taille / 1048576, 1) . ' MB'
                                    : number_format($taille / 1024, 1) . ' KB';
                            }
                            
                            // Qualité audio
                            $quality_info = [];
                            if (!empty($value['bitrate'])) {
                                $quality_info[] = round($value['bitrate'] / 1000) . 'kbps';
                            }
                            if (!empty($value['sample_rate'])) {
                                $quality_info[] = round($value['sample_rate'] / 1000, 1) . 'kHz';
                            }
                            $quality_badge = !empty($quality_info) 
                                ? '<span class="badge bg-light text-dark border">' . implode(' • ', $quality_info) . '</span>'
                                : '-';
                            
                            // Waveform data pour le player inline
                            $waveform_data = null;
                            if (!empty($value['waveform_data']) && file_exists(FCPATH . $value['waveform_data'])) {
                                $waveform_data = file_get_contents(FCPATH . $value['waveform_data']);
                            }
                        ?>
                            <tr data-audio-id="<?= $value['id_media'] ?>">
                                <td><?= $i++ ?></td>
                                
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="text-dark mb-1"><?= htmlspecialchars($value['titre'] ?? 'Sans titre') ?></strong>
                                        
                                        <?php if (!empty($value['credits'])): ?>
                                            <small class="text-muted mb-2"><i class="bx bx-user me-1"></i><?= htmlspecialchars($value['credits']) ?></small>
                                        <?php endif; ?>
                                        
                                        <!-- Mini Player avec Waveform -->
                                        <?php if ($is_upload && !empty($value['fichier'])): ?>
                                            <div class="audio-player-mini" data-src="<?= base_url($value['fichier']) ?>" data-waveform='<?= $waveform_data ?: '[]' ?>'>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button class="btn btn-sm btn-primary play-btn rounded-circle" style="width: 32px; height: 32px;">
                                                        <i class="bx bx-play"></i>
                                                    </button>
                                                    <div class="waveform-container flex-grow-1" style="height: 30px; background: #f8f9fa; border-radius: 4px; overflow: hidden;">
                                                        <canvas class="waveform-canvas" width="300" height="30"></canvas>
                                                        <div class="progress-overlay" style="width: 0%;"></div>
                                                    </div>
                                                    <span class="time-display small text-muted" style="min-width: 45px;">0:00</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($value['categorie'])): ?>
                                            <small class="mt-1"><span class="badge bg-light text-dark border"><?= htmlspecialchars($value['categorie']) ?></span></small>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td><?= $source_badge ?></td>
                                <td><span class="badge bg-dark"><?= $duree_formatee ?></span></td>
                                <td><?= $quality_badge ?></td>
                                <td><?= $taille_formatee ?></td>

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
                                                    <i class="bx bx-play-circle me-2 text-success"></i>Écouter
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#edit_<?= $value['id_media'] ?>">
                                                    <i class="bx bx-edit me-2 text-primary"></i>Modifier
                                                </a>
                                            </li>
                                            <?php if ($is_upload): ?>
                                            <li>
                                                <a class="dropdown-item download-audio" href="<?= base_url($value['fichier']) ?>" download>
                                                    <i class="bx bx-download me-2 text-info"></i>Télécharger
                                                </a>
                                            </li>
                                            <?php endif; ?>
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

                            <!-- Modal Player -->
                            <div class="modal fade audio-player-modal" id="view_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title"><i class="bx bx-music me-2"></i><?= htmlspecialchars($value['titre']) ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <?php if ($is_upload && !empty($value['fichier'])): ?>
                                                <div class="audio-full-player" data-audio-id="<?= $value['id_media'] ?>" data-src="<?= base_url('audio/stream/' . basename($value['fichier'])) ?>">
                                                    <!-- Cover Art ou Waveform -->
                                                    <div class="player-visualization text-center p-4 bg-light">
                                                        <?php if (!empty($value['waveform'])): ?>
                                                            <img src="<?= base_url($value['waveform']) ?>" class="img-fluid rounded" style="max-height: 200px;" alt="Waveform">
                                                        <?php else: ?>
                                                            <div class="display-1 text-muted"><i class="bx bx-music"></i></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <!-- Contrôles -->
                                                    <div class="p-4">
                                                        <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                                                            <button class="btn btn-outline-secondary btn-sm skip-back"><i class="bx bx-skip-previous"></i></button>
                                                            <button class="btn btn-primary btn-lg rounded-circle play-pause-btn" style="width: 60px; height: 60px;">
                                                                <i class="bx bx-play fs-4"></i>
                                                            </button>
                                                            <button class="btn btn-outline-secondary btn-sm skip-forward"><i class="bx bx-skip-next"></i></button>
                                                        </div>
                                                        
                                                        <!-- Progress Bar -->
                                                        <div class="progress-container mb-2">
                                                            <div class="progress" style="height: 8px; cursor: pointer;">
                                                                <div class="progress-bar bg-primary progress-bar-striped" role="progressbar" style="width: 0%"></div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="d-flex justify-content-between text-muted small">
                                                            <span class="current-time">0:00</span>
                                                            <span class="total-time"><?= $duree_formatee ?></span>
                                                        </div>
                                                        
                                                        <!-- Volume -->
                                                        <div class="d-flex align-items-center gap-2 mt-3">
                                                            <i class="bx bx-volume-low"></i>
                                                            <input type="range" class="form-range volume-slider" min="0" max="100" value="80" style="width: 100px;">
                                                            <i class="bx bx-volume-full"></i>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Audio Element Hidden -->
                                                    <audio preload="metadata">
                                                        <source src="<?= base_url('audio/stream/' . basename($value['fichier'])) ?>" type="<?= $value['mime_type'] ?? 'audio/mpeg' ?>">
                                                    </audio>
                                                </div>
                                            <?php elseif ($is_link && !empty($value['embed_code'])): ?>
                                                <div class="ratio ratio-16x9 bg-dark">
                                                    <?= $value['embed_code'] ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="p-5 text-center">
                                                    <i class="bx bx-error-circle display-1 text-muted"></i>
                                                    <p class="mt-3">Aucun lecteur disponible pour ce lien</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <?php if (!empty($value['spectrogram']) && file_exists(FCPATH . $value['spectrogram'])): ?>
                                                <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="collapse" data-bs-target="#spectro_<?= $value['id_media'] ?>">
                                                    <i class="bx bx-pulse me-1"></i>Spectrogramme
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                        </div>
                                        
                                        <?php if (!empty($value['spectrogram'])): ?>
                                        <div class="collapse" id="spectro_<?= $value['id_media'] ?>">
                                            <div class="card card-body border-0">
                                                <h6>Analyse fréquentielle</h6>
                                                <img src="<?= base_url($value['spectrogram']) ?>" class="img-fluid" alt="Spectrogramme">
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Edit -->
                            <div class="modal fade" id="edit_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'audio</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('audio/Update') ?>" method="POST" class="audio-form" data-mode="edit">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($value['titre']) ?>" required maxlength="255">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Type de source</label>
                                                    <select class="form-select source-type-selector" name="type_source" data-target="<?= $value['id_media'] ?>">
                                                        <option value="upload" <?= $is_upload ? 'selected' : '' ?>>Fichier uploadé</option>
                                                        <option value="link" <?= $is_link ? 'selected' : '' ?>>Lien externe</option>
                                                    </select>
                                                </div>

                                                <!-- Upload Section -->
                                                <div class="upload-section mb-3" id="upload_section_<?= $value['id_media'] ?>" style="<?= $is_upload ? '' : 'display:none;' ?>">
                                                    <label class="form-label fw-bold">Nouveau fichier audio</label>
                                                    
                                                    <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_<?= $value['id_media'] ?>">
                                                        <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                                                        <p class="mb-2">Glissez-déposez un fichier audio ou <span class="text-primary fw-bold browse-text">cliquez pour parcourir</span></p>
                                                        <small class="text-muted d-block">MP3, WAV, FLAC, AAC, OGG, M4A... (Taille illimitée)</small>
                                                        
                                                        <input type="file" class="form-control d-none file-input" accept="audio/*" data-upload-id="<?= $value['id_media'] ?>">
                                                        <input type="hidden" name="uploaded_file_path" class="uploaded-path">
                                                        <input type="hidden" name="audio_metadata" class="audio-metadata">
                                                        
                                                        <!-- Progress -->
                                                        <div class="upload-progress mt-3 d-none">
                                                            <div class="d-flex justify-content-between small mb-1">
                                                                <span class="upload-status fw-bold">Préparation...</span>
                                                                <span class="upload-percent text-primary">0%</span>
                                                            </div>
                                                            <div class="progress" style="height: 10px;">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                                                            </div>
                                                            <div class="d-flex justify-content-between small text-muted mt-1">
                                                                <span class="upload-chunks">Chunk 0/0</span>
                                                                <span class="upload-speed">0 MB/s</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if ($is_upload): ?>
                                                            <div class="current-file mt-3 p-2 bg-white rounded border d-flex align-items-center">
                                                                <i class="bx bx-music text-success fs-4 me-2"></i>
                                                                <div class="text-start flex-grow-1">
                                                                    <small class="d-block fw-bold text-truncate"><?= basename($value['fichier']) ?></small>
                                                                    <small class="text-muted"><?= $duree_formatee ?> • <?= $taille_formatee ?></small>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="new-file-info mt-2 d-none">
                                                            <div class="alert alert-success mb-0 py-2">
                                                                <i class="bx bx-check-circle me-2"></i>
                                                                <span class="new-file-name fw-bold"></span>
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
                                                        <button type="button" class="btn btn-sm btn-outline-primary resume-upload d-none">
                                                            <i class="bx bx-play me-1"></i>Reprendre
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Link Section -->
                                                <div class="link-section mb-3" id="link_section_<?= $value['id_media'] ?>" style="<?= $is_link ? '' : 'display:none;' ?>">
                                                    <label class="form-label fw-bold">Lien audio (SoundCloud, Spotify...)</label>
                                                    <input type="url" class="form-control" name="lien" value="<?= htmlspecialchars($value['lien'] ?? '') ?>" placeholder="https://...">
                                                    <small class="text-muted">SoundCloud, Spotify, Mixcloud, Bandcamp supportés</small>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">Artiste / Crédits</label>
                                                        <input type="text" class="form-control" name="credits" value="<?= htmlspecialchars($value['credits'] ?? '') ?>">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">Catégorie</label>
                                                        <input type="text" class="form-control" name="categorie" value="<?= htmlspecialchars($value['categorie'] ?? '') ?>" list="categories_list">
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Description</label>
                                                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="est_actif" value="1" <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label fw-bold">Audio actif</label>
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
                                                    <label class="form-label fw-bold">Message réseaux sociaux</label>
                                                    <textarea class="form-control" name="message_reseaux" rows="2" maxlength="280"><?= htmlspecialchars($value['message_reseaux'] ?? '') ?></textarea>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1" <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label fw-bold">Pour WhatsApp</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_for_website" value="1" <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label fw-bold">Pour Site Web</label>
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
                                            <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('audio/Delete') ?>" method="POST">
                                            <div class="modal-body text-center">
                                                <i class="bx bx-error-circle text-danger display-4"></i>
                                                <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                                <p class="text-muted"><?= htmlspecialchars($value['titre']) ?></p>
                                                <div class="alert alert-warning">
                                                    <i class="bx bx-info-circle me-2"></i>
                                                    Le fichier et les visualisations seront supprimés.
                                                </div>
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
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="bx bx-music fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">Aucun audio trouvé</p>
                                    <a href="javascript:;" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#create_audio">
                                        <i class="bx bx-plus me-1"></i>Ajouter un audio
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="create_audio" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouvel Audio</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= base_url('audio/Create') ?>" method="POST" class="audio-form" id="create_form" data-mode="create">
                    <div class="modal-body">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="titre" required maxlength="255" placeholder="Titre de la piste">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Type de source <span class="text-danger">*</span></label>
                            <select class="form-select source-type-selector" name="type_source" data-target="create" id="create_type_source">
                                <option value="upload" selected>Uploader un fichier (Illimité)</option>
                                <option value="link">Lien externe (SoundCloud, Spotify...)</option>
                            </select>
                        </div>

                        <!-- Upload Section -->
                        <div class="upload-section mb-3" id="upload_section_create">
                            <label class="form-label fw-bold">Fichier audio</label>
                            
                            <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_create">
                                <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                                <p class="mb-2">Glissez-déposez un fichier audio ou <span class="text-primary fw-bold browse-text">cliquez pour parcourir</span></p>
                                <small class="text-muted d-block mb-2">MP3, WAV, FLAC, AAC, OGG, M4A, WMA, AIFF</small>
                                <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Pas de limite de taille (chunks de 2MB)</span>
                                
                                <input type="file" class="form-control d-none file-input" id="create_file_input" accept="audio/*" data-upload-id="create">
                                <input type="hidden" name="uploaded_file_path" id="create_uploaded_path">
                                <input type="hidden" name="audio_metadata" id="create_audio_metadata">
                                
                                <!-- Progress détaillé -->
                                <div class="upload-progress mt-3 d-none" id="create_progress_container">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span id="create_upload_status" class="fw-bold">Préparation...</span>
                                        <span id="create_upload_percent" class="text-primary">0%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="create_progress_bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mt-1">
                                        <span id="create_chunks_info">Chunk 0/0</span>
                                        <span id="create_upload_speed">0 MB/s</span>
                                    </div>
                                    <div class="small text-primary mt-1" id="create_size_info">0 MB / 0 MB</div>
                                </div>
                                
                                <!-- Info fichier -->
                                <div class="file-info mt-2 d-none" id="create_file_info">
                                    <div class="alert alert-info mb-0 py-2 d-flex align-items-center justify-content-center">
                                        <i class="bx bx-file me-2"></i>
                                        <div>
                                            <div id="create_file_name" class="fw-bold text-truncate" style="max-width: 300px;"></div>
                                            <div id="create_file_size" class="small"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Métadonnées extraites -->
                                <div class="metadata-extracted mt-2 d-none" id="create_metadata">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white py-2">
                                            <small><i class="bx bx-info-circle me-1"></i>Métadonnées détectées</small>
                                        </div>
                                        <div class="card-body py-2">
                                            <div class="row text-start small">
                                                <div class="col-6"><strong>Durée:</strong> <span id="meta_duration">-</span></div>
                                                <div class="col-6"><strong>Bitrate:</strong> <span id="meta_bitrate">-</span></div>
                                                <div class="col-6"><strong>Sample Rate:</strong> <span id="meta_samplerate">-</span></div>
                                                <div class="col-6"><strong>Canaux:</strong> <span id="meta_channels">-</span></div>
                                                <div class="col-12 mt-1"><strong>Artiste:</strong> <span id="meta_artist">-</span></div>
                                                <div class="col-12"><strong>Album:</strong> <span id="meta_album">-</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Succès -->
                                <div class="upload-success mt-2 d-none" id="create_upload_success">
                                    <div class="alert alert-success mb-0 py-2 d-flex align-items-center justify-content-center">
                                        <i class="bx bx-check-circle me-2 fs-5"></i>
                                        <span class="fw-bold">Upload terminé avec succès!</span>
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

                        <!-- Link Section -->
                        <div class="link-section mb-3" id="link_section_create" style="display:none;">
                            <label class="form-label fw-bold">Lien audio <span class="text-muted">(SoundCloud, Spotify, Mixcloud, Bandcamp)</span></label>
                            <input type="url" class="form-control" name="lien" placeholder="https://soundcloud.com/...">
                            <small class="text-muted">Le lecteur intégré sera généré automatiquement</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Artiste / Crédits</label>
                                <input type="text" class="form-control" name="credits" placeholder="Nom de l'artiste...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Catégorie</label>
                                <input type="text" class="form-control" name="categorie" list="categories_list" placeholder="Ex: Podcast, Musique, Interview...">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Description de l'audio..."></textarea>
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
                                            <label class="form-check-label fw-bold">Partager sur réseaux sociaux</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3" id="create_msg_reseaux" style="display:none;">
                                    <label class="form-label fw-bold">Message pour réseaux sociaux</label>
                                    <textarea class="form-control" name="message_reseaux" rows="2" maxlength="280" placeholder="Texte à publier..."></textarea>
                                    <small class="text-muted">Maximum 280 caractères</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1">
                                            <label class="form-check-label fw-bold">
                                                <i class="bx bxl-whatsapp text-success me-1"></i>Disponible pour WhatsApp
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked>
                                            <label class="form-check-label fw-bold">
                                                <i class="bx bx-globe text-primary me-1"></i>Visible sur le site web
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success" id="create_submit">
                            <i class="bx bx-save me-1"></i>Créer l'audio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Datalist catégories -->
    <datalist id="categories_list">
        <option value="Musique">
        <option value="Podcast">
        <option value="Interview">
        <option value="Conférence">
        <option value="Livre audio">
        <option value="Méditation">
        <option value="Sound design">
        <option value="Field recording">
        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>">
        <?php endforeach; endif; ?>
    </datalist>

</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<script>
/**
 * =============================================================================
 * AUDIO UPLOAD MANAGER - VERSION PROFESSIONNELLE 3.0
 * Architecture: Modular ES6+ Class avec Web Audio API
 * Features: Waveform visualization, Real-time player, ID3 extraction
 * =============================================================================
 */

/**
 * Utilitaires pour fichiers audio
 */
class AudioUtils {
    static formatDuration(seconds) {
        if (!seconds || seconds < 0) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        if (mins < 60) {
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }
        const hours = Math.floor(mins / 60);
        const remainingMins = mins % 60;
        return `${hours}:${remainingMins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    static formatSize(bytes, decimals = 2) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i];
    }

    static getAudioMimeType(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const types = {
            'mp3': 'audio/mpeg',
            'wav': 'audio/wav',
            'flac': 'audio/flac',
            'aac': 'audio/aac',
            'ogg': 'audio/ogg',
            'm4a': 'audio/mp4',
            'wma': 'audio/x-ms-wma',
            'aiff': 'audio/aiff',
            'opus': 'audio/opus',
            'weba': 'audio/webm'
        };
        return types[ext] || 'audio/mpeg';
    }

    /**
     * Extrait les métadonnées ID3 d'un fichier audio via FileReader
     */
    static async extractMetadata(file) {
        return new Promise((resolve) => {
            // Lecture des premiers bytes pour ID3
            const reader = new FileReader();
            reader.onload = (e) => {
                const data = e.target.result;
                const metadata = {
                    title: null,
                    artist: null,
                    album: null,
                    year: null,
                    duration: null
                };

                // Parsing basique ID3v2
                const id3v2 = AudioUtils.parseID3v2(data);
                if (id3v2) {
                    Object.assign(metadata, id3v2);
                }

                // Estimation durée pour MP3 (approximation)
                if (file.name.endsWith('.mp3')) {
                    metadata.estimatedDuration = Math.floor(file.size / (128 * 1024 / 8)); // @128kbps
                }

                resolve(metadata);
            };
            reader.readAsArrayBuffer(file.slice(0, 1024 * 100)); // Lire 100KB max
        });
    }

    static parseID3v2(buffer) {
        const view = new DataView(buffer);
        const decoder = new TextDecoder('utf-8');
        
        // Vérifier signature ID3
        if (decoder.decode(new Uint8Array(buffer, 0, 3)) !== 'ID3') {
            return null;
        }

        const version = view.getUint8(3);
        const flags = view.getUint8(5);
        const size = ((view.getUint8(6) & 0x7F) << 21) |
                     ((view.getUint8(7) & 0x7F) << 14) |
                     ((view.getUint8(8) & 0x7F) << 7) |
                     (view.getUint8(9) & 0x7F);

        return { version: `2.${version}`, size: size };
    }
}

/**
 * Visualiseur de waveform sur Canvas
 */
class WaveformVisualizer {
    constructor(canvas, options = {}) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.options = {
            barWidth: 2,
            barGap: 1,
            barColor: '#007bff',
            progressColor: '#28a745',
            backgroundColor: 'transparent',
            ...options
        };
        this.peaks = [];
        this.progress = 0;
    }

    setPeaks(peaks) {
        this.peaks = peaks;
        this.draw();
    }

    setProgress(percent) {
        this.progress = Math.max(0, Math.min(100, percent));
        this.draw();
    }

    draw() {
        const { width, height } = this.canvas;
        this.ctx.clearRect(0, 0, width, height);
        
        if (!this.peaks.length) {
            this.drawPlaceholder();
            return;
        }

        const totalBars = Math.floor(width / (this.options.barWidth + this.options.barGap));
        const step = Math.ceil(this.peaks.length / totalBars);
        const barWidth = this.options.barWidth;
        const gap = this.options.barGap;
        const centerY = height / 2;

        const progressX = (this.progress / 100) * width;

        for (let i = 0; i < totalBars; i++) {
            const peakIndex = i * step;
            const peak = this.peaks[peakIndex] || 0;
            const barHeight = peak * height * 0.9;
            
            const x = i * (barWidth + gap);
            const isPlayed = x < progressX;

            this.ctx.fillStyle = isPlayed ? this.options.progressColor : this.options.barColor;
            
            // Dessiner barre centrée
            this.ctx.fillRect(
                x, 
                centerY - barHeight / 2, 
                barWidth, 
                barHeight
            );
        }
    }

    drawPlaceholder() {
        const { width, height } = this.canvas;
        this.ctx.fillStyle = '#dee2e6';
        
        // Dessiner une ligne plate
        this.ctx.fillRect(0, height / 2 - 1, width, 2);
    }

    /**
     * Génère des peaks à partir d'un AudioBuffer
     */
    static async generatePeaksFromBuffer(audioBuffer, samples = 1000) {
        const channelData = audioBuffer.getChannelData(0);
        const step = Math.floor(channelData.length / samples);
        const peaks = [];

        for (let i = 0; i < samples; i++) {
            const start = i * step;
            const end = start + step;
            let max = 0;
            
            for (let j = start; j < end; j++) {
                const abs = Math.abs(channelData[j]);
                if (abs > max) max = abs;
            }
            
            peaks.push(max);
        }

        return peaks;
    }
}

/**
 * Lecteur audio avancé avec Web Audio API
 */
class AdvancedAudioPlayer {
    constructor(container, options = {}) {
        this.container = container;
        this.audio = container.querySelector('audio');
        this.visualizer = null;
        this.analyser = null;
        this.dataArray = null;
        this.animationId = null;
        this.isPlaying = false;

        this.elements = {
            playBtn: container.querySelector('.play-pause-btn'),
            progressBar: container.querySelector('.progress-bar'),
            progressContainer: container.querySelector('.progress'),
            currentTime: container.querySelector('.current-time'),
            totalTime: container.querySelector('.total-time'),
            volumeSlider: container.querySelector('.volume-slider'),
            skipBack: container.querySelector('.skip-back'),
            skipForward: container.querySelector('.skip-forward')
        };

        this.init();
    }

    init() {
        if (!this.audio) return;

        // Event listeners
        this.elements.playBtn?.addEventListener('click', () => this.togglePlay());
        
        this.audio.addEventListener('timeupdate', () => this.updateProgress());
        this.audio.addEventListener('loadedmetadata', () => this.updateDuration());
        this.audio.addEventListener('ended', () => this.onEnded());
        
        this.elements.progressContainer?.addEventListener('click', (e) => this.seek(e));
        this.elements.volumeSlider?.addEventListener('input', (e) => this.setVolume(e.target.value));
        
        this.elements.skipBack?.addEventListener('click', () => this.skip(-10));
        this.elements.skipForward?.addEventListener('click', () => this.skip(10));

        // Web Audio API pour visualisation temps réel
        this.initWebAudio();
    }

    initWebAudio() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            this.audioContext = new AudioContext();
            
            this.analyser = this.audioContext.createAnalyser();
            this.analyser.fftSize = 256;
            
            const source = this.audioContext.createMediaElementSource(this.audio);
            source.connect(this.analyser);
            this.analyser.connect(this.audioContext.destination);
            
            this.dataArray = new Uint8Array(this.analyser.frequencyBinCount);
        } catch (e) {
            console.log('Web Audio API not available');
        }
    }

    togglePlay() {
        if (this.audioContext?.state === 'suspended') {
            this.audioContext.resume();
        }

        if (this.isPlaying) {
            this.pause();
        } else {
            this.play();
        }
    }

    play() {
        this.audio.play().then(() => {
            this.isPlaying = true;
            this.updatePlayButton();
            this.startVisualization();
        }).catch(e => console.error('Play error:', e));
    }

    pause() {
        this.audio.pause();
        this.isPlaying = false;
        this.updatePlayButton();
        this.stopVisualization();
    }

    updatePlayButton() {
        const icon = this.elements.playBtn?.querySelector('i');
        if (icon) {
            icon.className = this.isPlaying ? 'bx bx-pause fs-4' : 'bx bx-play fs-4';
        }
    }

    updateProgress() {
        const percent = (this.audio.currentTime / this.audio.duration) * 100;
        if (this.elements.progressBar) {
            this.elements.progressBar.style.width = percent + '%';
        }
        if (this.elements.currentTime) {
            this.elements.currentTime.textContent = AudioUtils.formatDuration(this.audio.currentTime);
        }
    }

    updateDuration() {
        if (this.elements.totalTime) {
            this.elements.totalTime.textContent = AudioUtils.formatDuration(this.audio.duration);
        }
    }

    seek(e) {
        const rect = this.elements.progressContainer.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        this.audio.currentTime = percent * this.audio.duration;
    }

    skip(seconds) {
        this.audio.currentTime = Math.max(0, Math.min(this.audio.duration, this.audio.currentTime + seconds));
    }

    setVolume(value) {
        this.audio.volume = value / 100;
    }

    onEnded() {
        this.isPlaying = false;
        this.updatePlayButton();
        this.stopVisualization();
    }

    startVisualization() {
        if (!this.analyser) return;
        
        const visualize = () => {
            if (!this.isPlaying) return;
            
            this.analyser.getByteFrequencyData(this.dataArray);
            // Ici on pourrait mettre à jour une visualisation temps réel
            this.animationId = requestAnimationFrame(visualize);
        };
        
        visualize();
    }

    stopVisualization() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
    }
}

/**
 * Gestionnaire d'upload chunked (réutilisé de Video avec adaptations)
 */
class ChunkedUploadManager {
    constructor(options = {}) {
        this.baseUrl = options.baseUrl || '';
        this.chunkSize = options.chunkSize || 2 * 1024 * 1024;
        this.maxRetries = options.maxRetries || 3;
        this.maxConcurrency = options.maxConcurrency || 2;
        this.retryDelay = options.retryDelay || 1000;

        this.state = {
            file: null,
            uploadId: null,
            totalChunks: 0,
            uploadedChunks: new Set(),
            failedChunks: new Map(),
            isPaused: false,
            isCancelled: false,
            isUploading: false,
            startTime: null,
            bytesUploaded: 0
        };

        this.callbacks = {
            onProgress: () => {},
            onChunkComplete: () => {},
            onComplete: () => {},
            onError: () => {},
            onCancel: () => {},
            onPause: () => {},
            onResume: () => {},
            onMetadataExtracted: () => {} // Nouveau callback pour audio
        };

        Object.assign(this.callbacks, options);
    }

    async start(file) {
        try {
            this.reset();
            this.state.file = file;
            this.state.isUploading = true;
            this.state.startTime = Date.now();

            // Extraction métadonnées avant upload (spécifique audio)
            const metadata = await AudioUtils.extractMetadata(file);
            this.callbacks.onMetadataExtracted(metadata);

            // Initialisation
            const initData = await this.initializeUpload(metadata);
            this.state.uploadId = initData.upload_id;
            this.state.totalChunks = initData.total_chunks;

            if (initData.chunk_size) {
                this.chunkSize = initData.chunk_size;
                this.state.totalChunks = Math.ceil(file.size / this.chunkSize);
            }

            this.notifyProgress({
                phase: 'uploading',
                percent: 0,
                uploadedChunks: 0,
                totalChunks: this.state.totalChunks,
                uploadedSize: 0,
                totalSize: file.size,
                speed: 0
            });

            // Upload parallèle
            await this.uploadChunksParallel();

            if (this.state.isCancelled) {
                await this.cancel();
                return;
            }

            await this.finalizeUpload();

        } catch (error) {
            console.error('[ChunkedUploadManager] Error:', error);
            this.callbacks.onError(error.message || 'Erreur inconnue', error);
        } finally {
            this.state.isUploading = false;
        }
    }

    async initializeUpload(metadata) {
        const formData = new FormData();
        formData.append('file_name', this.state.file.name);
        formData.append('file_size', this.state.file.size);
        
        const hash = await this.calculateHash(this.state.file.slice(0, Math.min(1024 * 1024, this.state.file.size)));
        formData.append('file_hash', hash);
        formData.append('metadata', JSON.stringify(metadata));

        const response = await fetch(this.baseUrl + 'initUpload', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const data = await response.json();
        if (!data.success) throw new Error(data.message);
        
        return data;
    }

    async uploadChunksParallel() {
        const queue = [];
        for (let i = 0; i < this.state.totalChunks; i++) {
            if (!this.state.uploadedChunks.has(i)) queue.push(i);
        }

        const workers = [];
        for (let w = 0; w < this.maxConcurrency; w++) {
            workers.push(this.uploadWorker(queue));
        }

        await Promise.all(workers);
    }

    async uploadWorker(queue) {
        while (queue.length > 0 && !this.state.isCancelled && !this.state.isPaused) {
            const index = queue.shift();
            await this.uploadChunkWithRetry(index);
        }
    }

    async uploadChunkWithRetry(index, attempt = 0) {
        try {
            await this.uploadChunk(index);
        } catch (error) {
            const retryCount = this.state.failedChunks.get(index) || 0;
            if (retryCount < this.maxRetries && !this.state.isCancelled) {
                const delay = this.retryDelay * Math.pow(2, retryCount);
                this.state.failedChunks.set(index, retryCount + 1);
                await this.sleep(delay);
                await this.uploadChunkWithRetry(index, retryCount + 1);
            } else {
                throw error;
            }
        }
    }

    async uploadChunk(index) {
        while (this.state.isPaused && !this.state.isCancelled) {
            await this.sleep(100);
        }
        if (this.state.isCancelled) return;

        const start = index * this.chunkSize;
        const end = Math.min(start + this.chunkSize, this.state.file.size);
        const chunk = this.state.file.slice(start, end);

        const formData = new FormData();
        formData.append('upload_id', this.state.uploadId);
        formData.append('chunk_index', index);
        formData.append('chunk', chunk, `chunk_${index}`);

        const startTime = Date.now();
        
        const response = await fetch(this.baseUrl + 'uploadChunk', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = await response.json();
        if (!data.success) {
            if (data.error_type === 'PHP_LIMIT') {
                throw new Error(`Limite serveur: ${data.message}`);
            }
            throw new Error(data.message);
        }

        this.state.uploadedChunks.add(index);
        this.state.bytesUploaded += chunk.size;

        const speed = this.calculateSpeed();
        this.notifyProgress({
            phase: 'uploading',
            percent: (this.state.uploadedChunks.size / this.state.totalChunks) * 100,
            uploadedChunks: this.state.uploadedChunks.size,
            totalChunks: this.state.totalChunks,
            uploadedSize: this.state.bytesUploaded,
            totalSize: this.state.file.size,
            currentChunk: index + 1,
            speed: speed
        });

        this.callbacks.onChunkComplete(index, data);
    }

    async finalizeUpload() {
        this.notifyProgress({ phase: 'finalizing', percent: 100, message: 'Traitement audio...' });

        const formData = new FormData();
        formData.append('upload_id', this.state.uploadId);

        const response = await fetch(this.baseUrl + 'completeUpload', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await response.json();

        if (!data.success) {
            if (data.missing_chunks?.length > 0) {
                for (const idx of data.missing_chunks) this.state.uploadedChunks.delete(idx);
                await this.uploadChunksParallel();
                return this.finalizeUpload();
            }
            throw new Error(data.message);
        }

        this.callbacks.onComplete(data);
    }

    async cancel() {
        this.state.isCancelled = true;
        this.state.isPaused = false;

        if (this.state.uploadId) {
            try {
                const formData = new FormData();
                formData.append('upload_id', this.state.uploadId);
                await fetch(this.baseUrl + 'cancelUpload', { method: 'POST', body: formData });
            } catch (e) {}
        }

        this.callbacks.onCancel();
    }

    pause() {
        if (this.state.isUploading && !this.state.isCancelled) {
            this.state.isPaused = true;
            this.callbacks.onPause();
        }
    }

    resume() {
        if (this.state.isPaused) {
            this.state.isPaused = false;
            this.callbacks.onResume();
        }
    }

    calculateSpeed() {
        const elapsed = (Date.now() - this.state.startTime) / 1000;
        return elapsed === 0 ? 0 : (this.state.bytesUploaded / elapsed) / (1024 * 1024);
    }

    notifyProgress(progress) {
        if (this.progressThrottle && progress.phase === 'uploading') {
            const now = Date.now();
            if (now - (this.lastProgressUpdate || 0) < 100) return;
            this.lastProgressUpdate = now;
        }
        this.callbacks.onProgress(progress);
    }

    reset() {
        this.state = {
            file: null,
            uploadId: null,
            totalChunks: 0,
            uploadedChunks: new Set(),
            failedChunks: new Map(),
            isPaused: false,
            isCancelled: false,
            isUploading: false,
            startTime: null,
            bytesUploaded: 0
        };
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async calculateHash(blob) {
        const buffer = await blob.arrayBuffer();
        const hashBuffer = await crypto.subtle.digest('SHA-256', buffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    get isUploading() { return this.state.isUploading; }
    get isPaused() { return this.state.isPaused; }
}

// =============================================================================
// INITIALISATION
// =============================================================================

$(document).ready(function() {
    
    const UPLOAD_CONFIG = {
        baseUrl: '<?= base_url('audio/') ?>',
        chunkSize: 2 * 1024 * 1024,
        maxRetries: 3
    };

    let currentUploader = null;

    // Auto-hide alerts
    setTimeout(() => {
        $('.alert:not(.alert-permanent)').fadeOut('slow');
    }, 5000);

    // Toggle AJAX fields
    $(document).on('change', '.toggle-field', function() {
        const $cb = $(this);
        $cb.prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url('audio/toggleField') ?>',
            type: 'POST',
            data: {
                id: $cb.data('id'),
                field: $cb.data('field'),
                value: $cb.is(':checked') ? 1 : 0,
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            },
            dataType: 'json',
            success: (r) => {
                if (!r?.success) {
                    $cb.prop('checked', !$cb.is(':checked'));
                    toastr.error('Erreur mise à jour');
                }
            },
            error: () => {
                $cb.prop('checked', !$cb.is(':checked'));
                toastr.error('Erreur connexion');
            },
            complete: () => $cb.prop('disabled', false)
        });
    });

    // Toggle sections
    $(document).on('change', '.source-type-selector', function() {
        const target = $(this).data('target');
        const isUpload = $(this).val() === 'upload';
        
        if (target === 'create') {
            $('#upload_section_create').toggle(isUpload);
            $('#link_section_create').toggle(!isUpload);
            $('#create_uploaded_path').prop('required', isUpload);
            $('input[name="lien"]').prop('required', !isUpload);
            if (!isUpload && currentUploader) currentUploader.cancel();
        } else {
            $(`#upload_section_${target}`).toggle(isUpload);
            $(`#link_section_${target}`).toggle(!isUpload);
        }
    });

    $(document).on('change', '.share-toggle', function() {
        $(`#${$(this).data('target')}`).toggle($(this).is(':checked'));
    });

    // ========================================================================
    // UPLOAD HANDLING
    // ========================================================================

    $(document).on('click', '.upload-zone', function(e) {
        if ($(e.target).is('input, .upload-progress, .upload-progress *')) return;
        $(this).find('.file-input').trigger('click');
    });

    $(document).on('dragover', '.upload-zone', function(e) {
        e.preventDefault();
        $(this).addClass('drag-active border-primary bg-light');
    });

    $(document).on('dragleave', '.upload-zone', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-active border-primary bg-light');
    });

    $(document).on('drop', '.upload-zone', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-active border-primary bg-light');
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) handleFileSelection(files[0], $(this));
    });

    $(document).on('change', '.file-input', function() {
        const file = this.files[0];
        if (file) handleFileSelection(file, $(this).closest('.upload-zone'));
    });

    function handleFileSelection(file, $zone) {
        if (!file.type.startsWith('audio/')) {
            toastr.error('Veuillez sélectionner un fichier audio.');
            return;
        }

        const uploadId = $zone.find('.file-input').data('upload-id');
        
        // Afficher info
        const formattedSize = AudioUtils.formatSize(file.size);
        if (uploadId === 'create') {
            $('#create_file_name').text(file.name).attr('title', file.name);
            $('#create_file_size').text(formattedSize);
            $('#create_file_info').removeClass('d-none');
        } else {
            $zone.find('.new-file-info').removeClass('d-none');
            $zone.find('.new-file-name').text(file.name);
        }

        startChunkedUpload(file, uploadId, $zone);
    }

    function startChunkedUpload(file, uploadId, $zone) {
        const isCreate = uploadId === 'create';
        const ui = getUploadUI(uploadId, $zone);
        
        resetUploadUI(ui);
        showUploadControls(ui);

        currentUploader = new ChunkedUploadManager({
            baseUrl: UPLOAD_CONFIG.baseUrl,
            chunkSize: UPLOAD_CONFIG.chunkSize,
            maxRetries: UPLOAD_CONFIG.maxRetries,
            
            onMetadataExtracted: (metadata) => {
                // Afficher métadonnées extraites
                if (isCreate && metadata) {
                    $('#meta_duration').text(metadata.estimatedDuration ? 
                        AudioUtils.formatDuration(metadata.estimatedDuration) : '-');
                    $('#create_metadata').removeClass('d-none');
                }
            },
            
            onProgress: (data) => updateProgressUI(ui, data),
            
            onChunkComplete: (index, response) => {
                console.log(`Chunk ${index} uploaded`);
            },
            
            onComplete: (data) => {
                handleUploadComplete(ui, data, uploadId);
                
                // Afficher métadonnées serveur
                if (isCreate) {
                    $('#meta_duration').text(data.duration_formatted || '-');
                    $('#meta_bitrate').text(data.bitrate ? Math.round(data.bitrate/1000) + ' kbps' : '-');
                    $('#meta_samplerate').text(data.sample_rate ? (data.sample_rate/1000).toFixed(1) + ' kHz' : '-');
                    $('#meta_channels').text(data.channels ? (data.channels === 1 ? 'Mono' : data.channels === 2 ? 'Stéréo' : data.channels + ' canaux') : '-');
                    if (data.metadata?.artist) $('#meta_artist').text(data.metadata.artist);
                    if (data.metadata?.album) $('#meta_album').text(data.metadata.album);
                }
            },
            
            onError: (message) => handleUploadError(ui, message),
            onCancel: () => handleUploadCancel(ui, uploadId),
            onPause: () => {
                ui.$status.text('En pause');
                ui.$bar.removeClass('progress-bar-animated');
            },
            onResume: () => {
                ui.$status.text('Upload en cours...');
                ui.$bar.addClass('progress-bar-animated');
            }
        });

        currentUploader.start(file);
        bindControlButtons(ui, currentUploader);
    }

    function getUploadUI(uploadId, $zone) {
        const isCreate = uploadId === 'create';
        
        if (isCreate) {
            return {
                $progress: $('#create_progress_container'),
                $status: $('#create_upload_status'),
                $percent: $('#create_upload_percent'),
                $bar: $('#create_progress_bar'),
                $chunks: $('#create_chunks_info'),
                $speed: $('#create_upload_speed'),
                $sizeInfo: $('#create_size_info'),
                $cancelBtn: $('#create_cancel'),
                $pauseBtn: $('#create_pause'),
                $resumeBtn: $('#create_resume'),
                $success: $('#create_upload_success'),
                $fileInfo: $('#create_file_info')
            };
        }
        
        return {
            $progress: $zone.find('.upload-progress'),
            $status: $zone.find('.upload-status'),
            $percent: $zone.find('.upload-percent'),
            $bar: $zone.find('.progress-bar'),
            $chunks: $zone.find('.upload-chunks'),
            $speed: $zone.find('.upload-speed'),
            $sizeInfo: null,
            $cancelBtn: $zone.closest('.upload-section').find('.cancel-upload'),
            $pauseBtn: $zone.closest('.upload-section').find('.pause-upload'),
            $resumeBtn: $zone.closest('.upload-section').find('.resume-upload'),
            $success: $zone.find('.new-file-info'),
            $fileInfo: $zone.find('.current-file')
        };
    }

    function resetUploadUI(ui) {
        ui.$progress.removeClass('d-none');
        ui.$bar.css('width', '0%').attr('aria-valuenow', 0);
        ui.$percent.text('0%');
        ui.$status.text('Préparation...');
        ui.$chunks.text('Chunk 0/0');
        if (ui.$speed) ui.$speed.text('0 MB/s');
        if (ui.$sizeInfo) ui.$sizeInfo.text('0 MB / 0 MB');
        ui.$success.addClass('d-none');
    }

    function showUploadControls(ui) {
        ui.$cancelBtn.removeClass('d-none');
        ui.$pauseBtn.removeClass('d-none');
        ui.$resumeBtn.addClass('d-none');
    }

    function updateProgressUI(ui, data) {
        const percent = Math.round(data.percent);
        ui.$bar.css('width', percent + '%').attr('aria-valuenow', percent);
        ui.$percent.text(percent + '%');
        
        if (data.phase === 'finalizing') {
            ui.$status.text(data.message);
        } else {
            ui.$status.text(`Upload en cours (${data.currentChunk || '?'}/${data.totalChunks})`);
            ui.$chunks.text(`Chunk ${data.uploadedChunks}/${data.totalChunks}`);
            if (ui.$speed) ui.$speed.text(data.speed.toFixed(2) + ' MB/s');
            if (ui.$sizeInfo) {
                ui.$sizeInfo.text(`${AudioUtils.formatSize(data.uploadedSize)} / ${AudioUtils.formatSize(data.totalSize)}`);
            }
        }
    }

    function handleUploadComplete(ui, data, uploadId) {
        ui.$progress.addClass('d-none');
        ui.$cancelBtn.addClass('d-none');
        ui.$pauseBtn.addClass('d-none');
        ui.$resumeBtn.addClass('d-none');
        
        if (uploadId === 'create') {
            $('#create_uploaded_path').val(data.file_path);
            $('#create_audio_metadata').val(JSON.stringify({
                duration: data.duration,
                bitrate: data.bitrate,
                sample_rate: data.sample_rate,
                channels: data.channels,
                waveform: data.waveform,
                spectrogram: data.spectrogram,
                waveform_data: data.waveform_data,
                metadata: data.metadata
            }));
            $('#create_upload_success').removeClass('d-none');
        } else {
            const $zone = $(`#drop_zone_${uploadId}`);
            $zone.closest('form').find('.uploaded-path').val(data.file_path);
            $zone.closest('form').find('.audio-metadata').val(JSON.stringify(data));
            ui.$success.html(`
                <div class="alert alert-success mb-0 py-2">
                    <i class="bx bx-check-circle me-2"></i>
                    <span class="fw-bold">Upload terminé!</span>
                    <div class="small">${data.file_size_formatted} • ${data.duration_formatted || ''}</div>
                </div>
            `).removeClass('d-none');
        }
        
        toastr.success('Upload terminé avec succès!');
        currentUploader = null;
    }

    function handleUploadError(ui, message) {
        ui.$progress.addClass('d-none');
        ui.$cancelBtn.addClass('d-none');
        ui.$pauseBtn.addClass('d-none');
        ui.$resumeBtn.addClass('d-none');
        toastr.error(message, 'Erreur Upload', { timeOut: 10000 });
        currentUploader = null;
    }

    function handleUploadCancel(ui, uploadId) {
        ui.$progress.addClass('d-none');
        ui.$cancelBtn.addClass('d-none');
        ui.$pauseBtn.addClass('d-none');
        ui.$resumeBtn.addClass('d-none');
        
        if (uploadId === 'create') {
            $('#create_file_info').addClass('d-none');
            $('#create_upload_success').addClass('d-none');
            $('#create_metadata').addClass('d-none');
            $('#create_uploaded_path').val('');
            $('#create_audio_metadata').val('');
        } else {
            $(`#drop_zone_${uploadId}`).find('.new-file-info').addClass('d-none');
        }
        currentUploader = null;
    }

    function bindControlButtons(ui, uploader) {
        ui.$cancelBtn.off('click').on('click', () => {
            if (confirm('Annuler l\'upload ?')) uploader.cancel();
        });
        
        ui.$pauseBtn.off('click').on('click', function() {
            uploader.pause();
            $(this).addClass('d-none');
            ui.$resumeBtn.removeClass('d-none');
        });
        
        ui.$resumeBtn.off('click').on('click', function() {
            uploader.resume();
            $(this).addClass('d-none');
            ui.$pauseBtn.removeClass('d-none');
        });
    }

    // ========================================================================
    // MINI PLAYERS (Waveform inline)
    // ========================================================================
    
    $('.audio-player-mini').each(function() {
        const $container = $(this);
        const src = $container.data('src');
        const waveformData = $container.data('waveform') || [];
        
        const canvas = $container.find('.waveform-canvas')[0];
        const visualizer = new WaveformVisualizer(canvas, {
            barWidth: 2,
            barGap: 1,
            barColor: '#6c757d',
            progressColor: '#0d6efd'
        });
        
        visualizer.setPeaks(waveformData.length ? waveformData : Array(100).fill(0.1));
        
        const audio = new Audio(src);
        let isPlaying = false;
        
        $container.find('.play-btn').on('click', function() {
            const $btn = $(this);
            const $icon = $btn.find('i');
            
            if (isPlaying) {
                audio.pause();
                $icon.removeClass('bx-pause').addClass('bx-play');
                isPlaying = false;
            } else {
                // Pause autres players
                $('.audio-player-mini audio').each(function() {
                    this.pause();
                });
                $('.audio-player-mini .play-btn i').removeClass('bx-pause').addClass('bx-play');
                
                audio.play();
                $icon.removeClass('bx-play').addClass('bx-pause');
                isPlaying = true;
            }
        });
        
        audio.addEventListener('timeupdate', () => {
            const percent = (audio.currentTime / audio.duration) * 100;
            visualizer.setProgress(percent);
            $container.find('.time-display').text(AudioUtils.formatDuration(audio.currentTime));
        });
        
        audio.addEventListener('ended', () => {
            isPlaying = false;
            $container.find('.play-btn i').removeClass('bx-pause').addClass('bx-play');
            visualizer.setProgress(0);
        });
    });

    // ========================================================================
    // MODAL PLAYERS (Full player)
    // ========================================================================
    
    $('.audio-player-modal').on('shown.bs.modal', function() {
        const $modal = $(this);
        const $player = $modal.find('.audio-full-player');
        
        if ($player.length && !$player.data('initialized')) {
            new AdvancedAudioPlayer($player[0]);
            $player.data('initialized', true);
        }
    });

    // ========================================================================
    // FORM VALIDATION
    // ========================================================================
    
    $('#create_form').on('submit', function(e) {
        const typeSource = $('#create_type_source').val();
        
        if (typeSource === 'upload' && !$('#create_uploaded_path').val()) {
            e.preventDefault();
            toastr.warning('Veuillez attendre la fin de l\'upload.');
            return false;
        }
        
        if (typeSource === 'link' && !$('input[name="lien"]').val().trim()) {
            e.preventDefault();
            toastr.warning('Veuillez saisir un lien.');
            return false;
        }
    });

    $('#create_audio').on('hidden.bs.modal', function() {
        if (currentUploader?.isUploading) currentUploader.cancel();
        
        $(this).find('form')[0].reset();
        $('#create_progress_container, #create_file_info, #create_upload_success, #create_metadata').addClass('d-none');
        $('#create_cancel, #create_pause, #create_resume').addClass('d-none');
        $('#create_upload_bar').css('width', '0%');
        $('#create_uploaded_path, #create_audio_metadata').val('');
        $('#upload_section_create').show();
        $('#link_section_create').hide();
    });

    // ========================================================================
    // DATATABLE
    // ========================================================================
    
    if ($.fn.DataTable) {
        $('#audioTable').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true
        });
    }

});
</script>

<style>
/* Styles Audio spécifiques */
.upload-zone {
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-zone:hover, .upload-zone.drag-active {
    border-color: #0d6efd !important;
    background-color: #f8f9fa !important;
}

.browse-text {
    cursor: pointer;
    text-decoration: underline;
}

/* Mini player inline */
.audio-player-mini {
    background: #f8f9fa;
    padding: 8px;
    border-radius: 8px;
    margin-top: 8px;
}

.waveform-container {
    position: relative;
    cursor: pointer;
}

.waveform-canvas {
    width: 100%;
    height: 100%;
}

.progress-overlay {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: rgba(13, 110, 253, 0.2);
    pointer-events: none;
    transition: width 0.1s;
}

/* Full player */
.audio-full-player .player-visualization {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Animations */
@keyframes pulse-audio {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.play-pause-btn:active {
    animation: pulse-audio 0.2s;
}

/* Responsive */
@media (max-width: 768px) {
    .audio-player-mini .waveform-container {
        display: none;
    }
}
</style>