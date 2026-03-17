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
                        <li class="breadcrumb-item active" aria-current="page">Gestion Audio Intelligente</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a class="btn btn-primary btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_audio_modal">
                    <i class="bx bx-plus"></i> Nouvel Audio
                </a>
                <a class="btn btn-outline-info btn-sm ms-2" href="<?= base_url('audio/diagnostics') ?>" target="_blank">
                    <i class="bx bx-test-tube"></i> Diagnostic Système
                </a>
            </div>
        </div>

        <!-- Statistiques Avancées -->
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
                                    if ($seconds < 3600) {
                                        echo floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
                                    } else {
                                        echo floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
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
                            <div class="fs-1 me-3"><i class="bx bx-brain"></i></div>
                            <div>
                                <h6 class="mb-0">Auto-Détection</h6>
                                <h3 class="mb-0">Active</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3"><i class="bx bx-image"></i></div>
                            <div>
                                <h6 class="mb-0">Avec Cover</h6>
                                <h3 class="mb-0">
                                    <?= count(array_filter($audios, function($a) { 
                                        return !empty($a['miniature']); 
                                    })) ?>
                                </h3>
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
                    <h5 class="mb-0 text-primary"><i class="bx bx-music me-2"></i>Bibliothèque Audio Intelligente</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Upload Illimité</span>
                        <span class="badge bg-info"><i class="bx bx-brain me-1"></i>IA Détection</span>
                        <span class="badge bg-warning"><i class="bx bx-image me-1"></i>Covers Auto</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="audioTable" class="table table-hover align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="8%">Cover</th>
                                <th width="22%">Titre & Infos</th>
                                <th width="10%">Qualité</th>
                                <th width="8%">Durée</th>
                                <th width="8%">Taille</th>
                                <th width="8%">Statut</th>
                                <th width="8%">WhatsApp</th>
                                <th width="8%">Site</th>
                                <th width="13%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($audios)): $i = 1; foreach ($audios as $value): 
                            $is_upload = !empty($value['fichier']);
                            $is_link = !empty($value['lien']);
                            
                            // Parse metadata JSON
                            $metadata = !empty($value['metadata_id3']) ? json_decode($value['metadata_id3'], true) : [];
                            
                            // Formatage durée
                            $duree_formatee = '-';
                            if (!empty($value['duree']) && $value['duree'] > 0) {
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
                            
                            // Cover/Thumbnail
                            $cover_html = '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:60px;height:60px;"><i class="bx bx-music text-muted fs-2"></i></div>';
                            if (!empty($value['miniature']) && file_exists(FCPATH . $value['miniature'])) {
                                $cover_html = '<img src="' . base_url($value['miniature']) . '" class="rounded" style="width:60px;height:60px;object-fit:cover;" alt="Cover">';
                            } elseif (!empty($value['waveform']) && file_exists(FCPATH . $value['waveform'])) {
                                $cover_html = '<img src="' . base_url($value['waveform']) . '" class="rounded" style="width:60px;height:60px;object-fit:cover;" alt="Waveform">';
                            }
                            
                            // Waveform data pour player
                            $waveform_data = null;
                            if (!empty($value['waveform_data']) && file_exists(FCPATH . $value['waveform_data'])) {
                                $waveform_data = file_get_contents(FCPATH . $value['waveform_data']);
                            }
                        ?>
                            <tr data-audio-id="<?= $value['id_media'] ?>">
                                <td><?= $i++ ?></td>
                                
                                <td><?= $cover_html ?></td>
                                
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="text-dark mb-1"><?= htmlspecialchars($value['titre'] ?? 'Sans titre') ?></strong>
                                        
                                        <?php if (!empty($value['credits'])): ?>
                                            <small class="text-muted mb-1"><i class="bx bx-user me-1"></i><?= htmlspecialchars($value['credits']) ?></small>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($value['album'])): ?>
                                            <small class="text-muted mb-1"><i class="bx bx-album me-1"></i><?= htmlspecialchars($value['album']) ?></small>
                                        <?php endif; ?>
                                        
                                        <!-- Mini Player -->
                                        <?php if ($is_upload && !empty($value['fichier'])): ?>
                                            <div class="audio-player-mini mt-2" data-src="<?= base_url('audio/stream/' . basename($value['fichier'])) ?>" data-waveform='<?= $waveform_data ?: '[]' ?>'>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button class="btn btn-sm btn-primary play-btn rounded-circle" style="width: 32px; height: 32px;">
                                                        <i class="bx bx-play"></i>
                                                    </button>
                                                    <div class="waveform-container flex-grow-1" style="height: 30px; background: #f8f9fa; border-radius: 4px; overflow: hidden; position: relative;">
                                                        <canvas class="waveform-canvas" width="200" height="30"></canvas>
                                                        <div class="progress-overlay" style="position: absolute; top: 0; left: 0; height: 100%; background: rgba(13, 110, 253, 0.2); width: 0%; pointer-events: none;"></div>
                                                    </div>
                                                    <span class="time-display small text-muted" style="min-width: 45px; font-size: 11px;">0:00</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($value['categorie'])): ?>
                                            <small class="mt-1"><span class="badge bg-light text-dark border"><?= htmlspecialchars($value['categorie']) ?></span></small>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td><?= $quality_badge ?></td>
                                <td><span class="badge bg-dark"><?= $duree_formatee ?></span></td>
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
                                                <a class="dropdown-item" href="<?= base_url('audio/reanalyze/' . $value['id_media']) ?>" class="reanalyze-btn">
                                                    <i class="bx bx-refresh me-2 text-warning"></i>Ré-analyser
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url($value['fichier']) ?>" download>
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
                                                    <!-- Cover Art -->
                                                    <div class="player-visualization text-center p-4 bg-light">
                                                        <?php if (!empty($value['miniature'])): ?>
                                                            <img src="<?= base_url($value['miniature']) ?>" class="img-fluid rounded shadow" style="max-height: 300px;" alt="Cover">
                                                        <?php elseif (!empty($value['waveform'])): ?>
                                                            <img src="<?= base_url($value['waveform']) ?>" class="img-fluid rounded" style="max-height: 200px;" alt="Waveform">
                                                        <?php else: ?>
                                                            <div class="display-1 text-muted"><i class="bx bx-music"></i></div>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Métadonnées -->
                                                        <div class="mt-3">
                                                            <?php if (!empty($value['credits'])): ?>
                                                                <h6 class="mb-1"><?= htmlspecialchars($value['credits']) ?></h6>
                                                            <?php endif; ?>
                                                            <?php if (!empty($value['album'])): ?>
                                                                <small class="text-muted"><?= htmlspecialchars($value['album']) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Contrôles -->
                                                    <div class="p-4">
                                                        <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                                                            <button class="btn btn-outline-secondary btn-sm skip-back"><i class="bx bx-skip-previous"></i></button>
                                                            <button class="btn btn-primary btn-lg rounded-circle play-pause-btn" style="width: 70px; height: 70px;">
                                                                <i class="bx bx-play fs-2"></i>
                                                            </button>
                                                            <button class="btn btn-outline-secondary btn-sm skip-forward"><i class="bx bx-skip-next"></i></button>
                                                        </div>
                                                        
                                                        <!-- Progress -->
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
                                                        <div class="d-flex align-items-center justify-content-center gap-2 mt-3">
                                                            <i class="bx bx-volume-low"></i>
                                                            <input type="range" class="form-range volume-slider" min="0" max="100" value="80" style="width: 150px;">
                                                            <i class="bx bx-volume-full"></i>
                                                        </div>
                                                        
                                                        <!-- Infos techniques -->
                                                        <div class="mt-3 pt-3 border-top">
                                                            <div class="row text-center small text-muted">
                                                                <div class="col-4">
                                                                    <i class="bx bx-tachometer d-block mb-1"></i>
                                                                    <?= !empty($value['bitrate']) ? round($value['bitrate']/1000) . ' kbps' : '-' ?>
                                                                </div>
                                                                <div class="col-4">
                                                                    <i class="bx bx-wave d-block mb-1"></i>
                                                                    <?= !empty($value['sample_rate']) ? round($value['sample_rate']/1000, 1) . ' kHz' : '-' ?>
                                                                </div>
                                                                <div class="col-4">
                                                                    <i class="bx bx-speaker d-block mb-1"></i>
                                                                    <?= !empty($value['channels']) ? ($value['channels'] == 1 ? 'Mono' : ($value['channels'] == 2 ? 'Stéréo' : $value['channels'] . ' canaux')) : '-' ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
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
                                        <form action="<?= base_url('audio/Update') ?>" method="POST" class="audio-form">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                                <input type="hidden" name="auto_detected_data" class="auto-detected-data">
                                                
                                                <!-- Aperçu actuel -->
                                                <div class="current-preview mb-3 p-3 bg-light rounded">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <?php if (!empty($value['miniature'])): ?>
                                                            <img src="<?= base_url($value['miniature']) ?>" style="width:80px;height:80px;object-fit:cover;" class="rounded">
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="mb-1"><?= htmlspecialchars($value['titre']) ?></h6>
                                                            <small class="text-muted"><?= $duree_formatee ?> • <?= $taille_formatee ?></small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Description <span class="text-primary">(seul champ requis)</span></label>
                                                    <textarea class="form-control" name="description" rows="3" placeholder="Décrivez cet audio..."><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                    <small class="text-muted">Titre, artiste et métadonnées sont auto-détectés du fichier</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Type de source</label>
                                                    <select class="form-select source-type-selector" name="type_source" data-target="edit_<?= $value['id_media'] ?>">
                                                        <option value="upload" <?= $is_upload ? 'selected' : '' ?>>Fichier uploadé</option>
                                                        <option value="link" <?= $is_link ? 'selected' : '' ?>>Lien externe</option>
                                                    </select>
                                                </div>

                                                <!-- Upload Section -->
                                                <div class="upload-section mb-3" id="upload_section_edit_<?= $value['id_media'] ?>" style="<?= $is_upload ? '' : 'display:none;' ?>">
                                                    <label class="form-label fw-bold">Nouveau fichier audio (optionnel)</label>
                                                    
                                                    <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_edit_<?= $value['id_media'] ?>">
                                                        <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                                                        <p class="mb-2">Glissez-déposez ou <span class="text-primary fw-bold browse-text">cliquez pour parcourir</span></p>
                                                        <small class="text-muted d-block">Laissez vide pour conserver l'audio actuel</small>
                                                        
                                                        <input type="file" class="form-control d-none file-input" accept="audio/*" data-upload-id="edit_<?= $value['id_media'] ?>">
                                                        <input type="hidden" name="uploaded_file_path" class="uploaded-path">
                                                        
                                                        <!-- Progress -->
                                                        <div class="upload-progress mt-3 d-none">
                                                            <div class="progress" style="height: 8px;">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%"></div>
                                                            </div>
                                                            <div class="d-flex justify-content-between small mt-1">
                                                                <span class="upload-status">Préparation...</span>
                                                                <span class="upload-percent">0%</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Résultat analyse -->
                                                        <div class="analysis-result mt-3 d-none">
                                                            <div class="alert alert-info">
                                                                <h6 class="mb-2"><i class="bx bx-brain me-2"></i>Métadonnées détectées</h6>
                                                                <div class="row text-start small">
                                                                    <div class="col-6"><strong>Titre:</strong> <span class="detected-title">-</span></div>
                                                                    <div class="col-6"><strong>Artiste:</strong> <span class="detected-artist">-</span></div>
                                                                    <div class="col-6"><strong>Durée:</strong> <span class="detected-duration">-</span></div>
                                                                    <div class="col-6"><strong>Qualité:</strong> <span class="detected-quality">-</span></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Link Section -->
                                                <div class="link-section mb-3" id="link_section_edit_<?= $value['id_media'] ?>" style="<?= $is_link ? '' : 'display:none;' ?>">
                                                    <label class="form-label fw-bold">Lien audio</label>
                                                    <input type="url" class="form-control" name="lien" value="<?= htmlspecialchars($value['lien'] ?? '') ?>" placeholder="https://...">
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Catégorie suggérée</label>
                                                        <input type="text" class="form-control" name="categorie" value="<?= htmlspecialchars($value['categorie'] ?? '') ?>" list="categories_list">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Date média</label>
                                                        <input type="date" class="form-control" name="date_media" value="<?= $value['date_media'] ?? '' ?>">
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="est_actif" value="1" <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label">Audio actif</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1" <?= (!empty($value['is_for_whatsapp']) && $value['is_for_whatsapp'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label">WhatsApp</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_for_website" value="1" <?= (!empty($value['is_for_website']) && $value['is_for_website'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label">Site Web</label>
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
                                                    Le fichier, les visualisations et la miniature seront supprimés.
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

                            <!-- Modal Status -->
                            <div class="modal fade" id="status_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Changer le statut</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('audio/ChangeStatus') ?>" method="POST">
                                            <div class="modal-body text-center">
                                                <p>Statut actuel: <strong><?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'Actif' : 'Inactif' ?></strong></p>
                                                <input type="hidden" name="id" value="<?= $value['id_media'] ?>">
                                                <input type="hidden" name="est_actif" value="<?= $value['est_actif'] ?? 0 ?>">
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary">Confirmer</button>
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
                                    <a href="javascript:;" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#create_audio_modal">
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

    <!-- ==================== MODAL CRÉATION INTELLIGENTE ==================== -->
    <div class="modal fade" id="create_audio_modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouvel Audio - Détection Intelligente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <form action="<?= base_url('audio/Create') ?>" method="POST" id="create_audio_form">
                    <div class="modal-body">
                        <input type="hidden" name="type_source" value="upload">
                        <input type="hidden" name="uploaded_file_path" id="uploaded_file_path">
                        <input type="hidden" name="auto_detected_data" id="auto_detected_data">
                        
                        <div class="row">
                            <!-- Colonne Upload -->
                            <div class="col-md-7">
                                <!-- Zone Upload Audio -->
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bx bx-upload me-2"></i>1. Upload Audio</h6>
                                        
                                        <div class="upload-zone border-2 border-dashed rounded p-4 text-center" id="main_upload_zone" style="border-style: dashed; border-color: #dee2e6; cursor: pointer; transition: all 0.3s;">
                                            <div class="upload-placeholder">
                                                <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                                                <p class="mb-1 fw-bold">Glissez votre fichier audio ici</p>
                                                <p class="text-muted small mb-2">ou cliquez pour parcourir</p>
                                                <span class="badge bg-secondary">MP3, WAV, FLAC, AAC, OGG, M4A...</span>
                                                <p class="text-success small mt-2 mb-0"><i class="bx bx-infinity me-1"></i>Pas de limite de taille</p>
                                            </div>
                                            
                                            <input type="file" id="audio_file_input" class="d-none" accept="audio/*">
                                            
                                            <!-- Progress Upload -->
                                            <div class="upload-progress d-none mt-3">
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span id="upload_status" class="fw-bold text-primary">Préparation...</span>
                                                    <span id="upload_percent">0%</span>
                                                </div>
                                                <div class="progress" style="height: 10px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="upload_progress_bar" style="width: 0%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between small text-muted mt-1">
                                                    <span id="chunks_info">Chunk 0/0</span>
                                                    <span id="upload_speed">0 MB/s</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Fichier sélectionné -->
                                            <div class="file-selected d-none mt-3">
                                                <div class="alert alert-success mb-0 d-flex align-items-center">
                                                    <i class="bx bx-check-circle fs-4 me-2"></i>
                                                    <div class="text-start">
                                                        <div id="selected_filename" class="fw-bold text-truncate" style="max-width: 300px;"></div>
                                                        <small id="selected_filesize" class="text-muted"></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Contrôles upload -->
                                        <div class="upload-controls d-none">
                                            <button type="button" class="btn btn-sm btn-outline-danger me-2" id="cancel_upload">
                                                <i class="bx bx-x me-1"></i>Annuler
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="pause_upload">
                                                <i class="bx bx-pause me-1"></i>Pause
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary d-none" id="resume_upload">
                                                <i class="bx bx-play me-1"></i>Reprendre
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Résultats Analyse -->
                                <div class="card border-0 bg-light d-none" id="analysis_card">
                                    <div class="card-body">
                                        <h6 class="card-title text-success"><i class="bx bx-brain me-2"></i>2. Métadonnées Auto-Détectées</h6>
                                        
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="p-2 bg-white rounded">
                                                    <small class="text-muted d-block">Titre détecté</small>
                                                    <strong id="detected_title" class="text-primary">-</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2 bg-white rounded">
                                                    <small class="text-muted d-block">Artiste</small>
                                                    <strong id="detected_artist">-</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2 bg-white rounded">
                                                    <small class="text-muted d-block">Album</small>
                                                    <strong id="detected_album">-</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2 bg-white rounded">
                                                    <small class="text-muted d-block">Année</small>
                                                    <strong id="detected_year">-</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2 bg-white rounded">
                                                    <small class="text-muted d-block">Durée</small>
                                                    <strong id="detected_duration" class="text-success">-</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2 bg-white rounded">
                                                    <small class="text-muted d-block">Qualité</small>
                                                    <strong id="detected_quality">-</strong>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="p-2 bg-white rounded">
                                                    <small class="text-muted d-block">Genre</small>
                                                    <span id="detected_genre" class="badge bg-light text-dark">-</span>
                                                    <span id="detected_category" class="badge bg-info ms-1">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-warning mt-2 mb-0 py-2 small">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Ces valeurs seront utilisées automatiquement. Vous pouvez les modifier après création si nécessaire.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Colonne Miniature & Description -->
                            <div class="col-md-5">
                                <!-- Miniature -->
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bx bx-image me-2"></i>Miniature (Optionnel)</h6>
                                        
                                        <div class="text-center mb-3">
                                            <div id="thumbnail_preview" class="bg-white rounded d-flex align-items-center justify-content-center mx-auto" style="width: 200px; height: 200px; border: 2px dashed #dee2e6;">
                                                <div class="text-center p-3">
                                                    <i class="bx bx-image-add fs-1 text-muted mb-2"></i>
                                                    <p class="small text-muted mb-0">Aperçu miniature</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="upload_thumbnail_btn">
                                                <i class="bx bx-upload me-1"></i>Uploader une image
                                            </button>
                                            <input type="file" id="thumbnail_input" class="d-none" accept="image/*">
                                            <input type="hidden" name="custom_thumbnail" id="custom_thumbnail_path">
                                            
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="use_generated_thumb" checked>
                                                <label class="form-check-label small" for="use_generated_thumb">
                                                    Utiliser la cover extraite ou générée automatiquement si pas d'image
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2 small text-muted">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Ordre de priorité: 1. Image uploadée 2. Cover extraite du fichier 3. Générée depuis le waveform
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Description (Seul champ utilisateur) -->
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary"><i class="bx bx-edit me-2"></i>3. Description</h6>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Description de l'audio <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="description" rows="6" placeholder="Décrivez le contenu de cet audio... Quel est le sujet ? Qui parle ? Contexte ?" required></textarea>
                                            <small class="text-muted">C'est le seul champ que vous devez remplir. Tout le reste est détecté automatiquement !</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Catégorie (auto-suggérée)</label>
                                            <input type="text" class="form-control" name="categorie" id="suggested_category" list="categories_list" placeholder="Sera détectée automatiquement...">
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked>
                                                    <label class="form-check-label small">Publier sur site</label>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1">
                                                    <label class="form-check-label small">WhatsApp</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Visualisations générées (affichées après upload) -->
                        <div class="row mt-3 d-none" id="visualizations_row">
                            <div class="col-12">
                                <h6><i class="bx bx-pulse me-2"></i>Visualisations générées</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header py-2">Waveform</div>
                                            <div class="card-body p-0">
                                                <img id="generated_waveform" class="img-fluid" style="display:none;">
                                                <div id="waveform_placeholder" class="p-3 text-center text-muted small">En génération...</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header py-2">Spectrogramme</div>
                                            <div class="card-body p-0">
                                                <img id="generated_spectrogram" class="img-fluid" style="display:none;">
                                                <div id="spectrogram_placeholder" class="p-3 text-center text-muted small">En génération...</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success btn-lg" id="submit_btn" disabled>
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
 * AUDIO MANAGER ULTRA-INTELLIGENT v4.0
 * Auto-detection complète, Upload chunked, Miniature intelligente
 * =============================================================================
 */

class AudioUploadManager {
    constructor() {
        this.baseUrl = '<?= base_url('audio/') ?>';
        this.chunkSize = 2 * 1024 * 1024; // 2MB
        this.maxRetries = 3;
        
        this.state = {
            file: null,
            uploadId: null,
            isUploading: false,
            isPaused: false,
            uploader: null,
            detectedData: null
        };
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initMiniPlayers();
        this.initFullPlayers();
    }
    
    bindEvents() {
        // Upload zone events
        const zone = document.getElementById('main_upload_zone');
        const fileInput = document.getElementById('audio_file_input');
        
        zone.addEventListener('click', () => fileInput.click());
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.style.borderColor = '#0d6efd';
            zone.style.background = '#f8f9fa';
        });
        zone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            zone.style.borderColor = '#dee2e6';
            zone.style.background = '';
        });
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.style.borderColor = '#dee2e6';
            zone.style.background = '';
            if (e.dataTransfer.files.length > 0) {
                this.handleFileSelect(e.dataTransfer.files[0]);
            }
        });
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.handleFileSelect(e.target.files[0]);
            }
        });
        
        // Contrôles upload
        document.getElementById('cancel_upload')?.addEventListener('click', () => this.cancelUpload());
        document.getElementById('pause_upload')?.addEventListener('click', () => this.pauseUpload());
        document.getElementById('resume_upload')?.addEventListener('click', () => this.resumeUpload());
        
        // Miniature upload
        document.getElementById('upload_thumbnail_btn')?.addEventListener('click', () => {
            document.getElementById('thumbnail_input').click();
        });
        
        document.getElementById('thumbnail_input')?.addEventListener('change', (e) => {
            this.handleThumbnailUpload(e.target.files[0]);
        });
        
        // Soumission formulaire
        document.getElementById('create_audio_form')?.addEventListener('submit', (e) => {
            if (!document.getElementById('uploaded_file_path').value) {
                e.preventDefault();
                alert('Veuillez attendre la fin de l\'upload audio.');
                return false;
            }
        });
    }
    
    async handleFileSelect(file) {
        if (!file.type.startsWith('audio/')) {
            alert('Veuillez sélectionner un fichier audio valide.');
            return;
        }
        
        this.state.file = file;
        
        // UI updates
        document.querySelector('.upload-placeholder').classList.add('d-none');
        document.querySelector('.file-selected').classList.remove('d-none');
        document.getElementById('selected_filename').textContent = file.name;
        document.getElementById('selected_filesize').textContent = this.formatBytes(file.size);
        document.querySelector('.upload-controls').classList.remove('d-none');
        document.getElementById('submit_btn').disabled = true;
        
        // Démarrer upload chunked
        await this.startChunkedUpload(file);
    }
    
    async startChunkedUpload(file) {
        this.state.isUploading = true;
        
        try {
            // 1. Initialiser
            const initData = await this.initUpload(file);
            this.state.uploadId = initData.upload_id;
            
            // 2. Upload chunks
            const totalChunks = initData.total_chunks;
            let uploadedChunks = 0;
            
            this.showProgress();
            
            for (let i = 0; i < totalChunks; i++) {
                if (this.state.isPaused) {
                    await this.waitForResume();
                }
                
                if (!this.state.isUploading) {
                    throw new Error('Upload annulé');
                }
                
                const start = i * this.chunkSize;
                const end = Math.min(start + this.chunkSize, file.size);
                const chunk = file.slice(start, end);
                
                await this.uploadChunk(i, chunk, totalChunks);
                uploadedChunks++;
                
                this.updateProgress(uploadedChunks, totalChunks, file.size);
            }
            
            // 3. Finaliser
            await this.completeUpload();
            
        } catch (error) {
            console.error('Upload error:', error);
            alert('Erreur upload: ' + error.message);
            this.resetUpload();
        }
    }
    
    async initUpload(file) {
        const formData = new FormData();
        formData.append('file_name', file.name);
        formData.append('file_size', file.size);
        
        const response = await fetch(this.baseUrl + 'initUpload', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        if (!data.success) throw new Error(data.message);
        
        return data;
    }
    
    async uploadChunk(index, chunk, total) {
        const formData = new FormData();
        formData.append('upload_id', this.state.uploadId);
        formData.append('chunk_index', index);
        formData.append('chunk', chunk);
        
        const response = await fetch(this.baseUrl + 'uploadChunk', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        if (!data.success) throw new Error(data.message);
        
        return data;
    }
    
    async completeUpload() {
        const formData = new FormData();
        formData.append('upload_id', this.state.uploadId);
        formData.append('description', document.querySelector('textarea[name="description"]').value);
        formData.append('custom_thumbnail', document.getElementById('custom_thumbnail_path').value);
        
        document.getElementById('upload_status').textContent = 'Analyse en cours...';
        
        const response = await fetch(this.baseUrl + 'completeUpload', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        if (!data.success) throw new Error(data.message);
        
        // Stocker données détectées
        this.state.detectedData = data;
        
        // Mettre à jour UI avec résultats
        this.displayDetectedData(data);
        this.storeFormData(data);
        
        // Afficher visualisations
        this.displayVisualizations(data);
        
        // Activer soumission
        document.getElementById('submit_btn').disabled = false;
        document.querySelector('.upload-controls').classList.add('d-none');
        
        // Mettre à jour miniature si cover extraite
        if (data.thumbnail && !document.getElementById('custom_thumbnail_path').value) {
            this.updateThumbnailPreview(data.thumbnail);
        }
    }
    
    displayDetectedData(data) {
        document.getElementById('analysis_card').classList.remove('d-none');
        
        document.getElementById('detected_title').textContent = data.suggested_data.titre || '-';
        document.getElementById('detected_artist').textContent = data.artist || data.suggested_data.credits || '-';
        document.getElementById('detected_album').textContent = data.album || '-';
        document.getElementById('detected_year').textContent = data.year || '-';
        document.getElementById('detected_duration').textContent = data.duration_formatted || '-';
        document.getElementById('detected_quality').textContent = data.bitrate ? Math.round(data.bitrate/1000) + 'kbps, ' + (data.sample_rate/1000).toFixed(1) + 'kHz' : '-';
        document.getElementById('detected_genre').textContent = data.genre || 'Non détecté';
        document.getElementById('detected_category').textContent = data.suggested_data.categorie || 'Musique';
        
        // Suggérer catégorie dans champ
        document.getElementById('suggested_category').value = data.suggested_data.categorie || 'Musique';
    }
    
    storeFormData(data) {
        document.getElementById('uploaded_file_path').value = data.file_path;
        
        const autoData = {
            title: data.title || data.suggested_data.titre,
            artist: data.artist || data.suggested_data.credits,
            album: data.album,
            year: data.year,
            genre: data.genre,
            category: data.suggested_data.categorie,
            duration: data.duration,
            bitrate: data.bitrate,
            sample_rate: data.sample_rate,
            channels: data.channels,
            codec: data.codec,
            thumbnail: data.thumbnail,
            waveform: data.waveform,
            spectrogram: data.spectrogram,
            waveform_data: data.waveform_data
        };
        
        document.getElementById('auto_detected_data').value = JSON.stringify(autoData);
    }
    
    displayVisualizations(data) {
        document.getElementById('visualizations_row').classList.remove('d-none');
        
        if (data.waveform) {
            document.getElementById('generated_waveform').src = '<?= base_url() ?>' + data.waveform;
            document.getElementById('generated_waveform').style.display = 'block';
            document.getElementById('waveform_placeholder').style.display = 'none';
        }
        
        if (data.spectrogram) {
            document.getElementById('generated_spectrogram').src = '<?= base_url() ?>' + data.spectrogram;
            document.getElementById('generated_spectrogram').style.display = 'block';
            document.getElementById('spectrogram_placeholder').style.display = 'none';
        }
    }
    
    async handleThumbnailUpload(file) {
        if (!file.type.startsWith('image/')) {
            alert('Veuillez sélectionner une image valide.');
            return;
        }
        
        const formData = new FormData();
        formData.append('thumbnail', file);
        
        try {
            const response = await fetch(this.baseUrl + 'uploadThumbnail', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (!data.success) throw new Error(data.message);
            
            document.getElementById('custom_thumbnail_path').value = data.thumbnail_path;
            this.updateThumbnailPreview(data.thumbnail_path);
            
        } catch (error) {
            alert('Erreur upload miniature: ' + error.message);
        }
    }
    
    updateThumbnailPreview(path) {
        const preview = document.getElementById('thumbnail_preview');
        preview.innerHTML = `<img src="<?= base_url() ?>${path}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.375rem;">`;
        preview.style.border = 'none';
    }
    
    showProgress() {
        document.querySelector('.upload-progress').classList.remove('d-none');
    }
    
    updateProgress(uploaded, total, fileSize) {
        const percent = Math.round((uploaded / total) * 100);
        document.getElementById('upload_progress_bar').style.width = percent + '%';
        document.getElementById('upload_percent').textContent = percent + '%';
        document.getElementById('chunks_info').textContent = `Chunk ${uploaded}/${total}`;
        document.getElementById('upload_status').textContent = percent === 100 ? 'Finalisation...' : 'Upload en cours...';
    }
    
    pauseUpload() {
        this.state.isPaused = true;
        document.getElementById('pause_upload').classList.add('d-none');
        document.getElementById('resume_upload').classList.remove('d-none');
    }
    
    resumeUpload() {
        this.state.isPaused = false;
        document.getElementById('resume_upload').classList.add('d-none');
        document.getElementById('pause_upload').classList.remove('d-none');
    }
    
    waitForResume() {
        return new Promise(resolve => {
            const check = setInterval(() => {
                if (!this.state.isPaused) {
                    clearInterval(check);
                    resolve();
                }
            }, 100);
        });
    }
    
    cancelUpload() {
        if (!confirm('Annuler l\'upload ?')) return;
        
        this.state.isUploading = false;
        
        if (this.state.uploadId) {
            fetch(this.baseUrl + 'cancelUpload', {
                method: 'POST',
                body: JSON.stringify({upload_id: this.state.uploadId})
            });
        }
        
        this.resetUpload();
    }
    
    resetUpload() {
        this.state = {
            file: null,
            uploadId: null,
            isUploading: false,
            isPaused: false,
            uploader: null,
            detectedData: null
        };
        
        document.querySelector('.upload-placeholder').classList.remove('d-none');
        document.querySelector('.file-selected').classList.add('d-none');
        document.querySelector('.upload-progress').classList.add('d-none');
        document.querySelector('.upload-controls').classList.add('d-none');
        document.getElementById('analysis_card').classList.add('d-none');
        document.getElementById('visualizations_row').classList.add('d-none');
        document.getElementById('submit_btn').disabled = true;
        document.getElementById('uploaded_file_path').value = '';
        document.getElementById('auto_detected_data').value = '';
        document.getElementById('audio_file_input').value = '';
    }
    
    formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // ==================== MINI PLAYERS ====================
    
    initMiniPlayers() {
        document.querySelectorAll('.audio-player-mini').forEach(container => {
            const canvas = container.querySelector('.waveform-canvas');
            const ctx = canvas.getContext('2d');
            const waveformData = JSON.parse(container.dataset.waveform || '[]');
            const audioSrc = container.dataset.src;
            
            // Dessiner waveform
            this.drawWaveform(ctx, canvas, waveformData);
            
            // Setup audio
            const audio = new Audio(audioSrc);
            let isPlaying = false;
            
            const playBtn = container.querySelector('.play-btn');
            const progressOverlay = container.querySelector('.progress-overlay');
            const timeDisplay = container.querySelector('.time-display');
            
            playBtn.addEventListener('click', () => {
                if (isPlaying) {
                    audio.pause();
                    playBtn.innerHTML = '<i class="bx bx-play"></i>';
                    isPlaying = false;
                } else {
                    // Stop autres players
                    document.querySelectorAll('.audio-player-mini audio').forEach(a => a.pause());
                    document.querySelectorAll('.audio-player-mini .play-btn').forEach(b => b.innerHTML = '<i class="bx bx-play"></i>');
                    
                    audio.play();
                    playBtn.innerHTML = '<i class="bx bx-pause"></i>';
                    isPlaying = true;
                }
            });
            
            audio.addEventListener('timeupdate', () => {
                const percent = (audio.currentTime / audio.duration) * 100;
                progressOverlay.style.width = percent + '%';
                timeDisplay.textContent = this.formatTime(audio.currentTime);
            });
            
            audio.addEventListener('ended', () => {
                isPlaying = false;
                playBtn.innerHTML = '<i class="bx bx-play"></i>';
                progressOverlay.style.width = '0%';
            });
        });
    }
    
    drawWaveform(ctx, canvas, data) {
        const width = canvas.width;
        const height = canvas.height;
        const barWidth = 2;
        const gap = 1;
        const bars = Math.floor(width / (barWidth + gap));
        const step = Math.ceil(data.length / bars);
        
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#6c757d';
        
        for (let i = 0; i < bars; i++) {
            const idx = i * step;
            const value = data[idx] || 0;
            const barHeight = value * height * 0.8;
            const x = i * (barWidth + gap);
            const y = (height - barHeight) / 2;
            
            ctx.fillRect(x, y, barWidth, barHeight);
        }
    }
    
    // ==================== FULL PLAYERS ====================
    
    initFullPlayers() {
        document.querySelectorAll('.audio-player-modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', () => {
                const player = modal.querySelector('.audio-full-player');
                if (!player || player.dataset.initialized) return;
                
                this.setupFullPlayer(player);
                player.dataset.initialized = 'true';
            });
        });
    }
    
    setupFullPlayer(container) {
        const audio = container.querySelector('audio');
        const playBtn = container.querySelector('.play-pause-btn');
        const progressBar = container.querySelector('.progress-bar');
        const progressContainer = container.querySelector('.progress');
        const currentTime = container.querySelector('.current-time');
        const totalTime = container.querySelector('.total-time');
        const volumeSlider = container.querySelector('.volume-slider');
        
        let isPlaying = false;
        
        playBtn.addEventListener('click', () => {
            if (isPlaying) {
                audio.pause();
                playBtn.innerHTML = '<i class="bx bx-play fs-2"></i>';
                isPlaying = false;
            } else {
                audio.play();
                playBtn.innerHTML = '<i class="bx bx-pause fs-2"></i>';
                isPlaying = true;
            }
        });
        
        audio.addEventListener('timeupdate', () => {
            const percent = (audio.currentTime / audio.duration) * 100;
            progressBar.style.width = percent + '%';
            currentTime.textContent = this.formatTime(audio.currentTime);
        });
        
        audio.addEventListener('loadedmetadata', () => {
            totalTime.textContent = this.formatTime(audio.duration);
        });
        
        audio.addEventListener('ended', () => {
            isPlaying = false;
            playBtn.innerHTML = '<i class="bx bx-play fs-2"></i>';
            progressBar.style.width = '0%';
        });
        
        progressContainer.addEventListener('click', (e) => {
            const rect = progressContainer.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            audio.currentTime = percent * audio.duration;
        });
        
        volumeSlider.addEventListener('input', (e) => {
            audio.volume = e.target.value / 100;
        });
        
        // Skip buttons
        container.querySelector('.skip-back')?.addEventListener('click', () => {
            audio.currentTime = Math.max(0, audio.currentTime - 10);
        });
        
        container.querySelector('.skip-forward')?.addEventListener('click', () => {
            audio.currentTime = Math.min(audio.duration, audio.currentTime + 10);
        });
    }
    
    formatTime(seconds) {
        if (!seconds || isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
}

// ==================== INITIALISATION ====================

document.addEventListener('DOMContentLoaded', () => {
    // Initialiser Audio Manager
    window.audioManager = new AudioUploadManager();
    
    // Toggle fields AJAX
    document.querySelectorAll('.toggle-field').forEach(toggle => {
        toggle.addEventListener('change', async function() {
            this.disabled = true;
            
            try {
                const response = await fetch('<?= base_url('audio/toggleField') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `id=${this.dataset.id}&field=${this.dataset.field}&value=${this.checked ? 1 : 0}&<?= $this->security->get_csrf_token_name() ?>=<?= $this->security->get_csrf_hash() ?>`
                });
                
                const data = await response.json();
                if (!data.success) {
                    this.checked = !this.checked;
                    alert('Erreur mise à jour');
                }
            } catch (error) {
                this.checked = !this.checked;
                alert('Erreur connexion');
            } finally {
                this.disabled = false;
            }
        });
    });
    
    // Toggle source type (upload/link)
    document.querySelectorAll('.source-type-selector').forEach(selector => {
        selector.addEventListener('change', function() {
            const target = this.dataset.target;
            const isUpload = this.value === 'upload';
            
            document.getElementById(`upload_section_${target}`).style.display = isUpload ? 'block' : 'none';
            document.getElementById(`link_section_${target}`).style.display = isUpload ? 'none' : 'block';
        });
    });
    
    // DataTable
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#audioTable').DataTable({
            language: { 
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' 
            },
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [1, 9] } // Cover et Actions non triables
            ]
        });
    }
    
    // Reset modal on close
    document.getElementById('create_audio_modal')?.addEventListener('hidden.bs.modal', () => {
        window.audioManager.resetUpload();
        document.getElementById('create_audio_form').reset();
        document.getElementById('thumbnail_preview').innerHTML = `
            <div class="text-center p-3">
                <i class="bx bx-image-add fs-1 text-muted mb-2"></i>
                <p class="small text-muted mb-0">Aperçu miniature</p>
            </div>
        `;
        document.getElementById('thumbnail_preview').style.border = '2px dashed #dee2e6';
    });
});
</script>

<style>
/* Styles Audio Ultra-Modernes */
.upload-zone {
    transition: all 0.3s ease;
}

.upload-zone:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa !important;
}

.upload-zone.drag-active {
    border-color: #0d6efd !important;
    background-color: #e7f1ff !important;
    transform: scale(1.02);
}

/* Mini Player */
.audio-player-mini {
    background: #f8f9fa;
    padding: 6px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.waveform-container {
    position: relative;
    cursor: pointer;
}

.waveform-canvas {
    width: 100%;
    height: 100%;
    display: block;
}

.progress-overlay {
    transition: width 0.1s linear;
}

/* Full Player */
.audio-full-player .player-visualization {
    min-height: 300px;
    display: flex;
    flex-direction: column;
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

/* Cards */
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}

/* Badges */
.badge {
    font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
    .audio-player-mini .waveform-container {
        display: none;
    }
    
    .player-visualization img {
        max-height: 200px !important;
    }
}

/* Loading states */
.uploading {
    pointer-events: none;
    opacity: 0.7;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>