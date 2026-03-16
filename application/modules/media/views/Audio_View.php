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
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Audios</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary btn-sm me-2" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_audio">
                <i class="bx bx-plus"></i> Nouvel Audio
            </a>
            <a class="btn btn-success btn-sm" href="javascript:;" data-bs-toggle="modal" data-bs-target="#record_audio">
                <i class="bx bx-microphone"></i> Enregistrer
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

    <!-- Card Principale -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-primary"><i class="bx bx-music me-2"></i>Liste des Audios</h5>
                <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Upload Illimité (Chunked)</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="12%">Waveform</th>
                            <th width="20%">Titre</th>
                            <th width="10%">Source</th>
                            <th width="8%">Durée</th>
                            <th width="8%">Taille</th>
                            <th width="8%">Statut</th>
                            <th width="8%">WhatsApp</th>
                            <th width="8%">Site Web</th>
                            <th width="13%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($audios)): $i = 1; foreach ($audios as $value): 
                        $is_upload = !empty($value['fichier']);
                        $is_link = !empty($value['lien']);
                        $is_recording = !empty($value['is_recording']);
                        
                        // Badge source
                        if ($is_recording) {
                            $source_badge = '<span class="badge bg-warning"><i class="bx bx-microphone me-1"></i>Enreg.</span>';
                        } elseif ($is_upload) {
                            $source_badge = '<span class="badge bg-success"><i class="bx bx-upload me-1"></i>Upload</span>';
                        } elseif ($is_link) {
                            $source_badge = '<span class="badge bg-info"><i class="bx bx-link me-1"></i>Lien</span>';
                        } else {
                            $source_badge = '<span class="badge bg-secondary">Inconnu</span>';
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
                        
                        // Formatage durée
                        $duree_formatee = '-';
                        if (!empty($value['duree'])) {
                            $mins = floor($value['duree'] / 60);
                            $secs = floor($value['duree'] % 60);
                            $duree_formatee = sprintf('%02d:%02d', $mins, $secs);
                        }
                        
                        // Waveform/Miniature
                        $waveform_path = !empty($value['miniature']) ? $value['miniature'] : 'assets/images/audio-default.png';
                        $waveform_url = (strpos($waveform_path, 'http') === 0) ? $waveform_path : base_url($waveform_path);
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <div class="position-relative" style="width: 120px; height: 50px;">
                                    <img src="<?= $waveform_url ?>" class="rounded border w-100 h-100" style="object-fit: cover; background: #f8f9fa;" alt="Waveform">
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <i class="bx bx-play-circle text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <?php if ($is_recording): ?>
                                        <div class="position-absolute top-0 start-0">
                                            <span class="badge bg-danger" title="Enregistrement"><i class="bx bx-microphone"></i></span>
                                        </div>
                                    <?php endif; ?>
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
                                <span class="badge bg-light text-dark border">
                                    <i class="bx bx-time me-1"></i><?= $duree_formatee ?>
                                </span>
                            </td>

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
                                                <i class="bx bx-show me-2 text-info"></i>Écouter
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
                                    <form action="<?= base_url('audio/ChangeStatus') ?>" method="POST">
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

                        <!-- Modal View (Lecteur) -->
                        <div class="modal fade" id="view_<?= $value['id_media'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="bx bx-music me-2"></i><?= htmlspecialchars($value['titre']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <?php if ($is_upload && !empty($value['fichier'])): ?>
                                            <audio controls class="w-100 mb-3">
                                                <source src="<?= base_url($value['fichier']) ?>" type="<?= $value['mime_type'] ?? 'audio/mpeg' ?>">
                                                Votre navigateur ne supporte pas la lecture audio.
                                            </audio>
                                        <?php elseif ($is_link): ?>
                                            <div class="alert alert-info">
                                                <i class="bx bx-link-external me-2"></i>
                                                <a href="<?= htmlspecialchars($value['lien']) ?>" target="_blank">Ouvrir le lien audio</a>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="row text-muted small text-start">
                                            <div class="col-6">
                                                <p class="mb-1"><strong>Durée:</strong> <?= $duree_formatee ?></p>
                                                <p class="mb-1"><strong>Taille:</strong> <?= $taille_formatee ?></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1"><strong>Type:</strong> <?= $value['mime_type'] ?? '-' ?></p>
                                                <p class="mb-1"><strong>Catégorie:</strong> <?= htmlspecialchars($value['categorie'] ?? '-') ?></p>
                                            </div>
                                        </div>
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
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier l'audio</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

                                            <!-- Upload Section avec Chunked -->
                                            <div class="upload-section mb-3" id="upload_section_<?= $value['id_media'] ?>" style="<?= $is_upload ? '' : 'display:none;' ?>">
                                                <label class="form-label fw-bold">Nouveau fichier audio <span class="badge bg-success">Illimité</span></label>
                                                
                                                <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_<?= $value['id_media'] ?>">
                                                    <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                                                    <p class="mb-2">Glissez-déposez un audio ici ou <span class="text-primary fw-bold">cliquez pour parcourir</span></p>
                                                    <small class="text-muted d-block">Formats: MP3, WAV, OGG, WEBM, M4A</small>
                                                    
                                                    <input type="file" class="form-control d-none file-input" accept="audio/*" data-upload-id="<?= $value['id_media'] ?>">
                                                    <input type="hidden" name="uploaded_file_path" class="uploaded-path">
                                                    <input type="hidden" name="waveform" class="waveform-path">
                                                    <input type="hidden" name="duration" class="duration-field">
                                                    
                                                    <!-- Zone de progression -->
                                                    <div class="upload-progress mt-3 d-none">
                                                        <div class="d-flex justify-content-between small mb-1">
                                                            <span class="upload-status">Préparation...</span>
                                                            <span class="upload-percent">0%</span>
                                                        </div>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                                                        </div>
                                                        <div class="upload-chunks small text-muted mt-1">Chunk 0/0</div>
                                                    </div>
                                                    
                                                    <!-- Info fichier actuel -->
                                                    <?php if ($is_upload): ?>
                                                        <div class="current-file mt-3 p-2 bg-white rounded border d-flex align-items-center">
                                                            <i class="bx bx-music text-success fs-4 me-2"></i>
                                                            <div class="text-start">
                                                                <small class="d-block fw-bold"><?= basename($value['fichier']) ?></small>
                                                                <small class="text-muted"><?= $taille_formatee ?> • <?= $duree_formatee ?></small>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Info nouveau fichier -->
                                                    <div class="new-file-info mt-2 d-none">
                                                        <div class="alert alert-success mb-0 py-2">
                                                            <i class="bx bx-check-circle me-1"></i>
                                                            <span class="new-file-name"></span>
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
                                                <small class="text-muted">La waveform sera générée automatiquement</small>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Catégorie</label>
                                                    <input type="text" class="form-control" name="categorie" value="<?= htmlspecialchars($value['categorie'] ?? '') ?>" list="categories_list">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Date du média</label>
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
                                                <small class="text-muted">Max 280 caractères</small>
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
                                            <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                            <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                            <p class="text-muted"><?= htmlspecialchars($value['titre']) ?></p>
                                            <div class="alert alert-warning">
                                                <i class="bx bx-info-circle me-2"></i>
                                                Le fichier sera définitivement supprimé.
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
                        
                                <i class="bx bx-music fs-1 text-muted mb-3"></i>
                                <p class="text-muted">Aucun audio trouvé</p>
                                <a href="javascript:;" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#create_audio">
                                    <i class="bx bx-plus me-1"></i>Ajouter un audio
                                </a>
                                <a href="javascript:;" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#record_audio">
                                    <i class="bx bx-microphone me-1"></i>Enregistrer
                                </a>
                            
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<!-- Modal Create (Upload) -->
<div class="modal fade" id="create_audio" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouvel Audio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('audio/Create') ?>" method="POST" class="audio-form" id="create_form" data-mode="create">
                <input type="hidden" name="type_source" value="upload">
                <div class="modal-body">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="titre" required maxlength="255" placeholder="Titre de l'audio">
                    </div>

                    <!-- Upload Section avec Chunked -->
                    <div class="upload-section mb-3" id="upload_section_create">
                        <label class="form-label fw-bold">Fichier audio <span class="badge bg-success">Chunked Upload - Illimité</span></label>
                        
                        <div class="upload-zone border rounded p-4 text-center bg-light" id="drop_zone_create">
                            <i class="bx bx-cloud-upload fs-1 text-muted mb-2"></i>
                            <p class="mb-2">Glissez-déposez un audio ici ou <span class="text-primary fw-bold cursor-pointer">cliquez pour parcourir</span></p>
                            <small class="text-muted d-block mb-2">Formats: MP3, WAV, OGG, WEBM, M4A, FLAC</small>
                            <span class="badge bg-success"><i class="bx bx-infinity me-1"></i>Pas de limite de taille</span>
                            
                            <input type="file" class="form-control d-none file-input" id="create_file_input" accept="audio/*" data-upload-id="create">
                            <input type="hidden" name="uploaded_file_path" id="create_uploaded_path">
                            <input type="hidden" name="waveform" id="create_waveform_path">
                            <input type="hidden" name="duration" id="create_duration">
                            
                            <!-- Zone de progression -->
                            <div class="upload-progress mt-3 d-none" id="create_progress_container">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span id="create_upload_status">Préparation...</span>
                                    <span id="create_upload_percent">0%</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="create_progress_bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="small text-muted mt-1" id="create_chunks_info">Chunk 0/0</div>
                                <div class="small text-primary mt-1" id="create_size_info">0 MB / 0 MB</div>
                            </div>
                            
                            <!-- Info fichier sélectionné -->
                            <div class="file-info mt-2 d-none" id="create_file_info">
                                <div class="alert alert-info mb-0 py-2 d-flex align-items-center justify-content-center">
                                    <i class="bx bx-file me-2"></i>
                                    <span id="create_file_name"></span>
                                    <span class="mx-2">|</span>
                                    <span id="create_file_size" class="fw-bold"></span>
                                </div>
                            </div>
                            
                            <!-- Succès upload -->
                            <div class="upload-success mt-2 d-none" id="create_upload_success">
                                <div class="alert alert-success mb-0 py-2">
                                    <i class="bx bx-check-circle me-1"></i>
                                    <span>Upload terminé avec succès!</span>
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Catégorie</label>
                            <input type="text" class="form-control" name="categorie" list="categories_list" placeholder="Ex: Podcast, Musique...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date du média</label>
                            <input type="date" class="form-control" name="date_media">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Description de l'audio..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Crédits / Auteur</label>
                        <input type="text" class="form-control" name="credits" placeholder="Ex: Artiste, Studio...">
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
                                <textarea class="form-control" name="message_reseaux" rows="2" maxlength="280" placeholder="Texte à publier avec l'audio..."></textarea>
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
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Créer l'audio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Record Audio (WebRTC) - CORRIGÉ ET FONCTIONNEL -->
<div class="modal fade" id="record_audio" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-microphone me-2"></i>Enregistrement Audio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="close_record_modal"></button>
            </div>
            <form action="<?= base_url('audio/Create') ?>" method="POST" id="record_form">
                <input type="hidden" name="type_source" value="recording">
                <input type="hidden" name="uploaded_file_path" id="record_uploaded_path">
                <input type="hidden" name="waveform" id="record_waveform_path">
                <input type="hidden" name="duration" id="record_duration">
                
                <div class="modal-body">
                    <!-- Titre -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="titre" required maxlength="255" placeholder="Titre de l'enregistrement">
                    </div>

                    <!-- Interface Enregistrement -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body text-center p-4">
                            
                            <!-- Timer -->
                            <div class="mb-3">
                                <span id="recording_timer" class="display-4 fw-bold text-dark font-monospace">00:00</span>
                                <div class="text-muted small">Durée d'enregistrement</div>
                            </div>

                            <!-- Visualiseur -->
                            <div id="recording_visualizer" class="mb-4 rounded bg-white border" style="height: 100px; width: 100%; position: relative; overflow: hidden;">
                                <canvas id="audio_canvas" style="width: 100%; height: 100%; display: block;"></canvas>
                            </div>

                            <!-- Boutons de contrôle -->
                            <div class="d-flex justify-content-center gap-3 mb-3" id="recorder_controls">
                                <button type="button" id="btn_start_record" class="btn btn-danger btn-lg rounded-circle" style="width: 70px; height: 70px;" title="Démarrer l'enregistrement">
                                    <i class="bx bx-microphone fs-2"></i>
                                </button>
                                
                                <button type="button" id="btn_pause_record" class="btn btn-warning btn-lg rounded-circle d-none" style="width: 70px; height: 70px;" title="Pause">
                                    <i class="bx bx-pause fs-2"></i>
                                </button>
                                
                                <button type="button" id="btn_resume_record" class="btn btn-info btn-lg rounded-circle d-none" style="width: 70px; height: 70px;" title="Reprendre">
                                    <i class="bx bx-play fs-2"></i>
                                </button>
                                
                                <button type="button" id="btn_stop_record" class="btn btn-dark btn-lg rounded-circle d-none" style="width: 70px; height: 70px;" title="Arrêter">
                                    <i class="bx bx-stop fs-2"></i>
                                </button>
                                
                                <button type="button" id="btn_cancel_record" class="btn btn-outline-secondary btn-lg rounded-circle d-none" style="width: 70px; height: 70px;" title="Annuler">
                                    <i class="bx bx-x fs-2"></i>
                                </button>
                            </div>

                            <!-- Status -->
                            <div id="record_status" class="mb-2">
                                <span class="badge bg-secondary">Prêt à enregistrer - Cliquez sur le microphone</span>
                            </div>

                            <!-- Message d'erreur micro -->
                            <div id="mic_error" class="alert alert-danger d-none">
                                <i class="bx bx-error-circle me-2"></i>
                                <span id="mic_error_text">Erreur d'accès au microphone</span>
                            </div>

                            <!-- Preview après enregistrement -->
                            <div id="record_preview" class="d-none mt-3">
                                <div class="alert alert-success">
                                    <i class="bx bx-check-circle me-2"></i>Enregistrement terminé!
                                </div>
                                <audio id="recorded_audio" controls class="w-100 mb-2"></audio>
                                <button type="button" id="btn_new_record" class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-refresh me-1"></i>Nouvel enregistrement
                                </button>
                            </div>

                            <!-- Progress Upload -->
                            <div id="record_upload_progress" class="d-none mt-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Sauvegarde sur le serveur...</span>
                                    <span id="record_upload_percent">0%</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="record_progress_bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Catégorie</label>
                            <input type="text" class="form-control" name="categorie" list="categories_list" placeholder="Ex: Voix, Podcast...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date</label>
                            <input type="date" class="form-control" name="date_media" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Description de l'enregistrement..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_for_whatsapp" value="1">
                                <label class="form-check-label fw-bold">
                                    <i class="bx bxl-whatsapp text-success me-1"></i>Pour WhatsApp
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_for_website" value="1" checked>
                                <label class="form-check-label fw-bold">
                                    <i class="bx bx-globe text-primary me-1"></i>Visible sur le site
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success" id="record_submit" disabled>
                        <i class="bx bx-save me-1"></i>Sauvegarder l'enregistrement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Datalist pour les catégories -->
<datalist id="categories_list">
    <option value="Podcast">
    <option value="Musique">
    <option value="Voix">
    <option value="Interview">
    <option value="Conférence">
    <option value="Publicité">
    <option value="Autre">
</datalist>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/**
 * Audio Management System
 * Chunked Upload + WebRTC Recording + AJAX Toggles
 */

// ==================== CONFIGURATION ====================
const CONFIG = {
    CHUNK_SIZE: 5 * 1024 * 1024,
    MAX_RETRIES: 3,
    RETRY_DELAY: 1000,
    ALLOWED_TYPES: ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/webm', 'audio/mp4', 'audio/x-m4a', 'audio/flac'],
    ALLOWED_EXTENSIONS: ['mp3', 'wav', 'ogg', 'webm', 'm4a', 'flac', 'aac']
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
    
    formatDuration: (seconds) => {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
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
    constructor(uploadId, dropZoneId) {
        this.uploadId = uploadId;
        this.dropZone = document.getElementById(dropZoneId);
        this.fileInput = this.dropZone.querySelector('.file-input');
        this.progressBar = this.dropZone.querySelector('.progress-bar');
        this.progressContainer = this.dropZone.querySelector('.upload-progress');
        this.statusText = this.dropZone.querySelector('.upload-status') || document.getElementById(`${uploadId}_upload_status`);
        this.percentText = this.dropZone.querySelector('.upload-percent') || document.getElementById(`${uploadId}_upload_percent`);
        this.chunksInfo = this.dropZone.querySelector('.upload-chunks') || document.getElementById(`${uploadId}_chunks_info`);
        this.sizeInfo = document.getElementById(`${uploadId}_size_info`);
        
        this.currentFile = null;
        this.uploadSessionId = null;
        this.isPaused = false;
        this.isUploading = false;
        this.chunksQueue = [];
        this.uploadedChunks = [];
        this.totalChunks = 0;
        this.abortController = null;
        
        this.init();
    }
    
    init() {
        this.dropZone.addEventListener('click', (e) => {
            if (e.target !== this.fileInput && !e.target.closest('.progress') && !e.target.closest('button')) {
                this.fileInput.click();
            }
        });
        
        this.fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.handleFile(e.target.files[0]);
            }
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
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleFile(files[0]);
            }
        });
        
        const parent = this.dropZone.closest('.upload-section') || this.dropZone.parentElement;
        const cancelBtn = parent.querySelector('.cancel-upload');
        const pauseBtn = parent.querySelector('.pause-upload');
        const resumeBtn = parent.querySelector('.resume-upload');
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.cancelUpload();
            });
        }
        if (pauseBtn) {
            pauseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.pauseUpload();
            });
        }
        if (resumeBtn) {
            resumeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.resumeUpload();
            });
        }
    }
    
    handleFile(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!CONFIG.ALLOWED_EXTENSIONS.includes(ext)) {
            Utils.showToast('error', 'Format non supporté', 'Formats acceptés: MP3, WAV, OGG, WEBM, M4A, FLAC');
            return;
        }
        
        this.currentFile = file;
        
        const fileInfo = document.getElementById(`${this.uploadId}_file_info`);
        const fileName = document.getElementById(`${this.uploadId}_file_name`);
        const fileSize = document.getElementById(`${this.uploadId}_file_size`);
        
        if (fileInfo && fileName && fileSize) {
            fileName.textContent = file.name;
            fileSize.textContent = Utils.formatBytes(file.size);
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
            if (error.message !== 'Upload annulé' && error.name !== 'AbortError') {
                Utils.showToast('error', 'Erreur upload', error.message);
            }
        }
    }
    
    async initUpload() {
        const formData = new FormData();
        formData.append('file_name', this.currentFile.name);
        formData.append('file_size', this.currentFile.size);
        
        const response = await fetch('<?= base_url("audio/initUpload") ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Erreur initialisation');
        }
        
        this.uploadSessionId = data.upload_id;
        this.totalChunks = data.total_chunks;
        this.chunksQueue = [];
        for (let i = 0; i < this.totalChunks; i++) {
            this.chunksQueue.push(i);
        }
        
        if (this.progressContainer) {
            this.progressContainer.classList.remove('d-none');
        }
        
        const parent = this.dropZone.closest('.upload-section') || this.dropZone.parentElement;
        const cancelBtn = parent.querySelector('.cancel-upload');
        const pauseBtn = parent.querySelector('.pause-upload');
        if (cancelBtn) cancelBtn.classList.remove('d-none');
        if (pauseBtn) pauseBtn.classList.remove('d-none');
        
        this.updateProgress(0);
    }
    
    async uploadChunks() {
        const chunkSize = CONFIG.CHUNK_SIZE;
        
        for (let i = 0; i < this.totalChunks; i++) {
            if (!this.isUploading) break;
            
            while (this.isPaused) {
                await new Promise(resolve => setTimeout(resolve, 100));
            }
            
            if (!this.isUploading) break;
            if (this.uploadedChunks.includes(i)) continue;
            
            const start = i * chunkSize;
            const end = Math.min(start + chunkSize, this.currentFile.size);
            const chunk = this.currentFile.slice(start, end);
            
            let retries = 0;
            let success = false;
            
            while (retries < CONFIG.MAX_RETRIES && !success) {
                try {
                    await this.uploadChunk(i, chunk);
                    success = true;
                    this.uploadedChunks.push(i);
                    this.updateProgress((this.uploadedChunks.length / this.totalChunks) * 100);
                } catch (error) {
                    retries++;
                    if (retries >= CONFIG.MAX_RETRIES) {
                        throw new Error(`Échec chunk ${i} après ${CONFIG.MAX_RETRIES} tentatives`);
                    }
                    await new Promise(resolve => setTimeout(resolve, CONFIG.RETRY_DELAY));
                }
            }
        }
    }
    
    async uploadChunk(index, chunk) {
        const formData = new FormData();
        formData.append('upload_id', this.uploadSessionId);
        formData.append('chunk_index', index);
        formData.append('chunk', chunk);
        
        const response = await fetch('<?= base_url("audio/uploadChunk") ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: this.abortController.signal
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Erreur chunk');
        }
        
        return data;
    }
    
    async completeUpload() {
        if (!this.isUploading) return;
        
        const formData = new FormData();
        formData.append('upload_id', this.uploadSessionId);
        
        const response = await fetch('<?= base_url("audio/completeUpload") ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Erreur finalisation');
        }
        
        const uploadedPath = this.dropZone.querySelector('.uploaded-path') || document.getElementById(`${this.uploadId}_uploaded_path`);
        const waveformPath = this.dropZone.querySelector('.waveform-path') || document.getElementById(`${this.uploadId}_waveform_path`);
        const durationField = this.dropZone.querySelector('.duration-field') || document.getElementById(`${this.uploadId}_duration`);
        
        if (uploadedPath) uploadedPath.value = data.file_path;
        if (waveformPath) waveformPath.value = data.waveform || '';
        if (durationField) durationField.value = data.duration || 0;
        
        const successDiv = document.getElementById(`${this.uploadId}_upload_success`);
        if (successDiv) {
            successDiv.classList.remove('d-none');
        }
        
        Utils.showToast('success', 'Upload terminé!', `${data.file_size_formatted} uploadé avec succès`);
        
        const parent = this.dropZone.closest('.upload-section') || this.dropZone.parentElement;
        const cancelBtn = parent.querySelector('.cancel-upload');
        const pauseBtn = parent.querySelector('.pause-upload');
        const resumeBtn = parent.querySelector('.resume-upload');
        if (cancelBtn) cancelBtn.classList.add('d-none');
        if (pauseBtn) pauseBtn.classList.add('d-none');
        if (resumeBtn) resumeBtn.classList.add('d-none');
        
        this.isUploading = false;
    }
    
    pauseUpload() {
        this.isPaused = true;
        const parent = this.dropZone.closest('.upload-section') || this.dropZone.parentElement;
        const pauseBtn = parent.querySelector('.pause-upload');
        const resumeBtn = parent.querySelector('.resume-upload');
        if (pauseBtn) pauseBtn.classList.add('d-none');
        if (resumeBtn) resumeBtn.classList.remove('d-none');
        if (this.statusText) this.statusText.textContent = 'En pause...';
    }
    
    resumeUpload() {
        this.isPaused = false;
        const parent = this.dropZone.closest('.upload-section') || this.dropZone.parentElement;
        const pauseBtn = parent.querySelector('.pause-upload');
        const resumeBtn = parent.querySelector('.resume-upload');
        if (pauseBtn) pauseBtn.classList.remove('d-none');
        if (resumeBtn) resumeBtn.classList.add('d-none');
        if (this.statusText) this.statusText.textContent = 'Upload en cours...';
    }
    
    async cancelUpload() {
        this.isUploading = false;
        if (this.abortController) {
            this.abortController.abort();
        }
        
        if (this.uploadSessionId) {
            const formData = new FormData();
            formData.append('upload_id', this.uploadSessionId);
            
            try {
                await fetch('<?= base_url("audio/cancelUpload") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
            } catch (e) {
                console.error('Erreur annulation:', e);
            }
        }
        
        if (this.progressContainer) {
            this.progressContainer.classList.add('d-none');
        }
        
        const fileInfo = document.getElementById(`${this.uploadId}_file_info`);
        if (fileInfo) fileInfo.classList.add('d-none');
        
        const successDiv = document.getElementById(`${this.uploadId}_upload_success`);
        if (successDiv) successDiv.classList.add('d-none');
        
        const parent = this.dropZone.closest('.upload-section') || this.dropZone.parentElement;
        const cancelBtn = parent.querySelector('.cancel-upload');
        const pauseBtn = parent.querySelector('.pause-upload');
        const resumeBtn = parent.querySelector('.resume-upload');
        if (cancelBtn) cancelBtn.classList.add('d-none');
        if (pauseBtn) pauseBtn.classList.add('d-none');
        if (resumeBtn) resumeBtn.classList.add('d-none');
        
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
        if (this.percentText) {
            this.percentText.textContent = Math.round(percent) + '%';
        }
        if (this.statusText) {
            this.statusText.textContent = percent >= 100 ? 'Finalisation...' : 'Upload en cours...';
        }
        if (this.chunksInfo) {
            this.chunksInfo.textContent = `Chunk ${this.uploadedChunks.length}/${this.totalChunks}`;
        }
        if (this.sizeInfo && this.currentFile) {
            const uploaded = (this.uploadedChunks.length / this.totalChunks) * this.currentFile.size;
            this.sizeInfo.textContent = `${Utils.formatBytes(uploaded)} / ${Utils.formatBytes(this.currentFile.size)}`;
        }
    }
}

// ==================== WEBRTC AUDIO RECORDER - CORRIGÉ ====================
class AudioRecorder {
    constructor() {
        this.mediaRecorder = null;
        this.audioChunks = [];
        this.stream = null;
        this.startTime = null;
        this.elapsedTime = 0; // Temps écoulé en secondes
        this.timerInterval = null;
        this.isPaused = false;
        this.isRecording = false;
        this.audioContext = null;
        this.analyser = null;
        this.canvas = null;
        this.canvasCtx = null;
        this.animationId = null;
        this.recordedBlob = null;
        this.recordedDuration = 0; // Durée finale en secondes
        
        this.init();
    }
    
    init() {
        this.btnStart = document.getElementById('btn_start_record');
        this.btnPause = document.getElementById('btn_pause_record');
        this.btnStop = document.getElementById('btn_stop_record');
        this.btnResume = document.getElementById('btn_resume_record');
        this.btnCancel = document.getElementById('btn_cancel_record');
        this.btnNew = document.getElementById('btn_new_record');
        
        this.timerDisplay = document.getElementById('recording_timer');
        this.visualizer = document.getElementById('recording_visualizer');
        this.recordStatus = document.getElementById('record_status');
        this.recordPreview = document.getElementById('record_preview');
        this.recordedAudio = document.getElementById('recorded_audio');
        this.uploadProgress = document.getElementById('record_upload_progress');
        this.progressBar = document.getElementById('record_progress_bar');
        this.progressPercent = document.getElementById('record_upload_percent');
        this.submitBtn = document.getElementById('record_submit');
        this.micError = document.getElementById('mic_error');
        this.micErrorText = document.getElementById('mic_error_text');
        
        this.canvas = document.getElementById('audio_canvas');
        
        if (this.btnStart) this.btnStart.addEventListener('click', () => this.startRecording());
        if (this.btnPause) this.btnPause.addEventListener('click', () => this.pauseRecording());
        if (this.btnStop) this.btnStop.addEventListener('click', () => this.stopRecording());
        if (this.btnResume) this.btnResume.addEventListener('click', () => this.resumeRecording());
        if (this.btnCancel) this.btnCancel.addEventListener('click', () => this.cancelRecording());
        if (this.btnNew) this.btnNew.addEventListener('click', () => this.resetRecorder());
        
        const closeBtn = document.getElementById('close_record_modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                if (this.isRecording) this.stopRecording();
            });
        }
        
        const modal = document.getElementById('record_audio');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', () => this.resetRecorder());
            modal.addEventListener('shown.bs.modal', () => {
                this.setupCanvas();
                this.drawInitialState();
            });
        }
        
        if (this.canvas) {
            this.setupCanvas();
            this.drawInitialState();
        }
    }
    
    setupCanvas() {
        if (!this.canvas) return;
        const container = this.visualizer;
        if (container) {
            const rect = container.getBoundingClientRect();
            this.canvas.width = rect.width || 400;
            this.canvas.height = rect.height || 100;
        }
        this.canvasCtx = this.canvas.getContext('2d');
    }
    
    drawInitialState() {
        if (!this.canvasCtx || !this.canvas) return;
        const width = this.canvas.width;
        const height = this.canvas.height;
        
        this.canvasCtx.fillStyle = '#f8f9fa';
        this.canvasCtx.fillRect(0, 0, width, height);
        
        this.canvasCtx.strokeStyle = '#dee2e6';
        this.canvasCtx.lineWidth = 2;
        this.canvasCtx.beginPath();
        this.canvasCtx.moveTo(0, height / 2);
        this.canvasCtx.lineTo(width, height / 2);
        this.canvasCtx.stroke();
        
        this.canvasCtx.fillStyle = '#6c757d';
        this.canvasCtx.font = '14px Arial';
        this.canvasCtx.textAlign = 'center';
        this.canvasCtx.textBaseline = 'middle';
        this.canvasCtx.fillText('🎤 Cliquez sur le microphone pour démarrer', width / 2, height / 2);
    }
    
    drawVisualizer() {
        if (!this.analyser || !this.canvasCtx || !this.canvas) return;
        
        const bufferLength = this.analyser.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);
        this.analyser.getByteFrequencyData(dataArray);
        
        const width = this.canvas.width;
        const height = this.canvas.height;
        const barWidth = (width / bufferLength) * 2.5;
        let x = 0;
        
        this.canvasCtx.fillStyle = '#f8f9fa';
        this.canvasCtx.fillRect(0, 0, width, height);
        
        for (let i = 0; i < bufferLength; i++) {
            const barHeight = (dataArray[i] / 255) * height * 0.8;
            const gradient = this.canvasCtx.createLinearGradient(0, height, 0, height - barHeight);
            
            if (this.isPaused) {
                gradient.addColorStop(0, '#ffc107');
                gradient.addColorStop(1, '#ff9800');
            } else {
                gradient.addColorStop(0, '#dc3545');
                gradient.addColorStop(1, '#ff4444');
            }
            
            this.canvasCtx.fillStyle = gradient;
            this.canvasCtx.fillRect(x, height - barHeight, barWidth, barHeight);
            x += barWidth + 1;
        }
        
        if (this.isRecording) {
            this.animationId = requestAnimationFrame(() => this.drawVisualizer());
        }
    }
    
    async startRecording() {
        try {
            if (this.micError) this.micError.classList.add('d-none');
            
            if (!navigator.mediaDevices || !window.MediaRecorder) {
                throw new Error('Votre navigateur ne supporte pas l\'enregistrement audio');
            }
            
            console.log('Demande accès micro...');
            
            this.stream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                    sampleRate: 44100,
                    channelCount: 1
                }
            });
            
            console.log('Micro accès accordé');
            
            // Setup visualisation
            try {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const source = this.audioContext.createMediaStreamSource(this.stream);
                this.analyser = this.audioContext.createAnalyser();
                this.analyser.fftSize = 256;
                source.connect(this.analyser);
            } catch (audioErr) {
                console.warn('Web Audio API non disponible:', audioErr);
            }
            
            // Détecter format supporté
            const mimeTypes = [
                'audio/webm;codecs=opus',
                'audio/webm',
                'audio/ogg;codecs=opus',
                'audio/ogg',
                'audio/mp4'
            ];
            
            let selectedMimeType = '';
            for (const type of mimeTypes) {
                if (MediaRecorder.isTypeSupported(type)) {
                    selectedMimeType = type;
                    console.log('Format supporté:', type);
                    break;
                }
            }
            
            if (!selectedMimeType) {
                selectedMimeType = 'audio/webm';
            }
            
            const options = {
                mimeType: selectedMimeType,
                audioBitsPerSecond: 128000
            };
            
            this.mediaRecorder = new MediaRecorder(this.stream, options);
            this.audioChunks = [];
            
            this.mediaRecorder.ondataavailable = (e) => {
                console.log('Data available:', e.data.size, 'bytes, type:', e.data.type);
                if (e.data && e.data.size > 0) {
                    this.audioChunks.push(e.data);
                }
            };
            
            this.mediaRecorder.onstop = () => {
                console.log('MediaRecorder stopped, chunks:', this.audioChunks.length);
                this.handleRecordingComplete();
            };
            
            this.mediaRecorder.onerror = (e) => {
                console.error('MediaRecorder error:', e);
                Utils.showToast('error', 'Erreur lors de l\'enregistrement');
                this.resetRecorder();
            };
            
            // Démarrer l'enregistrement - collecte fréquente
            this.mediaRecorder.start(100);
            
            this.isRecording = true;
            this.isPaused = false;
            this.elapsedTime = 0;
            this.recordedDuration = 0;
            
            // Démarrer le timer
            this.startTimer();
            
            if (this.analyser) {
                this.drawVisualizer();
            }
            
            this.updateUIState('recording');
            this.updateStatus('Enregistrement en cours...', 'danger');
            
            console.log('Enregistrement démarré');
            
        } catch (error) {
            console.error('Erreur démarrage:', error);
            
            let errorMsg = error.message;
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                errorMsg = 'Accès au microphone refusé. Vérifiez les permissions.';
            } else if (error.name === 'NotFoundError') {
                errorMsg = 'Aucun microphone trouvé.';
            }
            
            if (this.micError && this.micErrorText) {
                this.micErrorText.textContent = errorMsg;
                this.micError.classList.remove('d-none');
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Erreur microphone',
                text: errorMsg
            });
        }
    }
    
    startTimer() {
        this.timerInterval = setInterval(() => {
            if (!this.isPaused) {
                this.elapsedTime++;
                if (this.timerDisplay) {
                    this.timerDisplay.textContent = Utils.formatDuration(this.elapsedTime);
                }
            }
        }, 1000);
    }
    
    pauseRecording() {
        if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
            this.mediaRecorder.pause();
            this.isPaused = true;
            if (this.animationId) {
                cancelAnimationFrame(this.animationId);
            }
            this.updateUIState('paused');
            this.updateStatus('Enregistrement en pause', 'warning');
        }
    }
    
    resumeRecording() {
        if (this.mediaRecorder && this.mediaRecorder.state === 'paused') {
            this.mediaRecorder.resume();
            this.isPaused = false;
            if (this.analyser) {
                this.drawVisualizer();
            }
            this.updateUIState('recording');
            this.updateStatus('Enregistrement en cours...', 'danger');
        }
    }
    
    stopRecording() {
        console.log('Arrêt enregistrement...');
        
        // Sauvegarder la durée avant d'arrêter
        this.recordedDuration = this.elapsedTime;
        console.log('Durée enregistrée:', this.recordedDuration, 'secondes');
        
        if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
            this.mediaRecorder.stop();
        }
        
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
        
        this.isRecording = false;
        this.isPaused = false;
        clearInterval(this.timerInterval);
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
        
        this.updateUIState('stopped');
        this.updateStatus('Enregistrement terminé', 'success');
    }
    
    cancelRecording() {
        console.log('Annulation enregistrement...');
        
        if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
            this.mediaRecorder.stop();
        }
        
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
        
        this.isRecording = false;
        this.isPaused = false;
        clearInterval(this.timerInterval);
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
        
        this.audioChunks = [];
        this.resetRecorder();
        this.updateStatus('Enregistrement annulé', 'secondary');
    }
    
    handleRecordingComplete() {
        if (this.audioChunks.length === 0) {
            console.error('Aucune donnée audio');
            Utils.showToast('error', 'Erreur', 'Aucune donnée audio capturée');
            this.resetRecorder();
            return;
        }
        
        // Créer le blob avec le type correct
        const blobType = this.audioChunks[0]?.type || 'audio/webm';
        this.recordedBlob = new Blob(this.audioChunks, { type: blobType });
        
        console.log('Blob créé:', this.recordedBlob.size, 'bytes, type:', this.recordedBlob.type);
        console.log('Durée finale:', this.recordedDuration, 'secondes');
        
        // Afficher preview
        const audioUrl = URL.createObjectURL(this.recordedBlob);
        if (this.recordedAudio) {
            this.recordedAudio.src = audioUrl;
        }
        
        if (this.recordPreview) {
            this.recordPreview.classList.remove('d-none');
        }
        
        if (this.submitBtn) {
            this.submitBtn.disabled = false;
        }
        
        // Mettre à jour le champ durée dans le formulaire
        const durationField = document.getElementById('record_duration');
        if (durationField) {
            durationField.value = this.recordedDuration;
            console.log('Champ duration mis à jour:', durationField.value);
        }
        
        // Upload automatique
        this.uploadRecording();
    }
    
    async uploadRecording() {
        if (!this.recordedBlob) return;
        
        console.log('Upload enregistrement...');
        console.log('Durée envoyée:', this.recordedDuration);
        
        if (this.uploadProgress) {
            this.uploadProgress.classList.remove('d-none');
        }
        
        const formData = new FormData();
        // Forcer le nom avec extension webm et type audio
        formData.append('audio', this.recordedBlob, 'recording.webm');
        formData.append('duration', this.recordedDuration); // Envoyer la durée
        
        try {
            const response = await fetch('<?= base_url("audio/saveRecording") ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log('Upload réussi:', data);
                
                const pathField = document.getElementById('record_uploaded_path');
                const waveformField = document.getElementById('record_waveform_path');
                const durationField = document.getElementById('record_duration');
                
                if (pathField) pathField.value = data.file_path;
                if (waveformField) waveformField.value = data.waveform || '';
                
                // Mettre à jour avec la durée retournée par le serveur ou garder la locale
                const finalDuration = data.duration || this.recordedDuration;
                if (durationField) durationField.value = finalDuration;
                
                if (this.progressBar) {
                    this.progressBar.style.width = '100%';
                    this.progressBar.classList.remove('progress-bar-animated');
                }
                if (this.progressPercent) {
                    this.progressPercent.textContent = '100%';
                }
                
                Utils.showToast('success', 'Enregistrement sauvegardé!', `Durée: ${Utils.formatDuration(finalDuration)}`);
                
            } else {
                throw new Error(data.message || 'Erreur serveur');
            }
            
        } catch (error) {
            console.error('Erreur upload:', error);
            Utils.showToast('error', 'Erreur sauvegarde', error.message);
            if (this.uploadProgress) {
                this.uploadProgress.classList.add('d-none');
            }
        }
    }
    
    resetRecorder() {
        console.log('Reset recorder...');
        
        if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
            try {
                this.mediaRecorder.stop();
            } catch (e) {
                // Déjà arrêté
            }
        }
        
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
        
        this.mediaRecorder = null;
        this.stream = null;
        this.audioChunks = [];
        this.recordedBlob = null;
        this.isRecording = false;
        this.isPaused = false;
        this.elapsedTime = 0;
        this.recordedDuration = 0;
        
        clearInterval(this.timerInterval);
        if (this.timerDisplay) {
            this.timerDisplay.textContent = '00:00';
        }
        
        this.updateUIState('idle');
        this.updateStatus('Prêt à enregistrer - Cliquez sur le microphone', 'secondary');
        
        if (this.recordPreview) this.recordPreview.classList.add('d-none');
        if (this.uploadProgress) this.uploadProgress.classList.add('d-none');
        if (this.micError) this.micError.classList.add('d-none');
        if (this.submitBtn) this.submitBtn.disabled = true;
        
        this.drawInitialState();
        
        // Reset champs cachés
        const pathField = document.getElementById('record_uploaded_path');
        const waveformField = document.getElementById('record_waveform_path');
        const durationField = document.getElementById('record_duration');
        
        if (pathField) pathField.value = '';
        if (waveformField) waveformField.value = '';
        if (durationField) durationField.value = '';
    }
    
    updateUIState(state) {
        if (this.btnStart) this.btnStart.classList.add('d-none');
        if (this.btnPause) this.btnPause.classList.add('d-none');
        if (this.btnResume) this.btnResume.classList.add('d-none');
        if (this.btnStop) this.btnStop.classList.add('d-none');
        if (this.btnCancel) this.btnCancel.classList.add('d-none');
        
        switch (state) {
            case 'idle':
                if (this.btnStart) this.btnStart.classList.remove('d-none');
                break;
            case 'recording':
                if (this.btnPause) this.btnPause.classList.remove('d-none');
                if (this.btnStop) this.btnStop.classList.remove('d-none');
                if (this.btnCancel) this.btnCancel.classList.remove('d-none');
                break;
            case 'paused':
                if (this.btnResume) this.btnResume.classList.remove('d-none');
                if (this.btnStop) this.btnStop.classList.remove('d-none');
                if (this.btnCancel) this.btnCancel.classList.remove('d-none');
                break;
            case 'stopped':
                // Preview affichée, bouton Nouveau visible
                break;
        }
    }
    
    updateStatus(message, type) {
        if (this.recordStatus) {
            const badgeClass = {
                'secondary': 'bg-secondary',
                'danger': 'bg-danger',
                'warning': 'bg-warning',
                'success': 'bg-success'
            }[type] || 'bg-secondary';
            
            this.recordStatus.innerHTML = `<span class="badge ${badgeClass}">${message}</span>`;
        }
    }
}









// ==================== INITIALISATION ROBUSTE ====================
document.addEventListener('DOMContentLoaded', function() {
    
   

    // ---- Upload Managers ----
    try {
        // Pour la création
        if (document.getElementById('drop_zone_create')) {
            window.createUpload = new ChunkedUploadManager('create', 'drop_zone_create');
        }
        
        // Pour les modals d'édition
        document.querySelectorAll('.upload-zone[id^="drop_zone_"]').forEach(zone => {
            const id = zone.id.replace('drop_zone_', '');
            if (id !== 'create') {
                if (!window['upload_' + id]) {
                    window['upload_' + id] = new ChunkedUploadManager(id, zone.id);
                }
            }
        });
    } catch (e) {
        console.error('Upload manager initialization error:', e);
    }

    // ---- Audio Recorder ----
    try {
        if (document.getElementById('record_audio')) {
            window.audioRecorder = new AudioRecorder();
        }
    } catch (e) {
        console.error('AudioRecorder error:', e);
    }

    // ---- Source type selector (upload/link) ----
    try {
        document.querySelectorAll('.source-type-selector').forEach(select => {
            select.addEventListener('change', function() {
                const targetId = this.dataset.target;
                const uploadSection = document.getElementById(`upload_section_${targetId}`);
                const linkSection = document.getElementById(`link_section_${targetId}`);
                if (this.value === 'upload') {
                    if (uploadSection) uploadSection.style.display = '';
                    if (linkSection) linkSection.style.display = 'none';
                } else {
                    if (uploadSection) uploadSection.style.display = 'none';
                    if (linkSection) linkSection.style.display = '';
                }
            });
        });
    } catch (e) {
        console.error('Source type selector error:', e);
    }

    // ---- Share toggle (réseaux sociaux) ----
    try {
        document.querySelectorAll('.share-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const targetId = this.dataset.target;
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.style.display = this.checked ? '' : 'none';
                }
            });
        });
    } catch (e) {
        console.error('Share toggle error:', e);
    }

    // ---- Toggle fields (whatsapp/site) ----
    try {
        document.querySelectorAll('.toggle-field').forEach(toggle => {
            toggle.addEventListener('change', async function() {
                const id = this.dataset.id;
                const field = this.dataset.field;
                const value = this.checked ? 1 : 0;
                try {
                    const response = await fetch('<?= base_url("audio/toggleField") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `id=${id}&field=${field}&value=${value}`
                    });
                    const data = await response.json();
                    if (data.success) {
                        Utils.showToast('success', 'Statut mis à jour');
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    console.error('Erreur toggle:', error);
                    Utils.showToast('error', 'Erreur', 'Impossible de mettre à jour');
                    this.checked = !this.checked;
                }
            });
        });
    } catch (e) {
        console.error('Toggle fields error:', e);
    }

    // ---- Vérification formulaire audio ----
    try {
        document.querySelectorAll('.audio-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const mode = this.dataset.mode;
                const typeSource = this.querySelector('[name="type_source"]')?.value || 'upload';
                if (typeSource === 'upload' || typeSource === 'recording') {
                    const filePath = this.querySelector('[name="uploaded_file_path"]')?.value;
                    if (!filePath && mode === 'create') {
                        e.preventDefault();
                        Utils.showToast('warning', 'Attention', 'Veuillez d\'abord uploader un fichier audio');
                        return false;
                    }
                }
            });
        });
    } catch (e) {
        console.error('Form validation error:', e);
    }
});



</script>