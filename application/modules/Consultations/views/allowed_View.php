<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-s-center mb-3">
        <div class="breadcrumb-title pe-3">Télémédecine</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb- active" aria-current="page">Gestion des Consultations</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_consultation">
                <i class="bx bx-plus"></i> Nouvelle Consultation
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

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-s-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-health me-2"></i>Liste des Consultations</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="consultationsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="12%">N° Consultation</th>
                            <th width="15%">Patient</th>
                            <th width="12%">Médecin</th>
                            <th width="8%">Type</th>
                            <th width="10%">Date debut consultation</th>
                            <th width="8%">Prix</th>
                            <th width="10%">Live Consultation</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($consultations)): $i = 1; foreach ($consultations as $value): 
                        // Badges type
                        $type_badges = [
                            'video' => '<span class="badge bg-info"><i class="bx bx-video"></i> Vidéo</span>',
                            'presentiel' => '<span class="badge bg-primary"><i class="bx bx-building"></i> Présentiel</span>',
                            'telephone' => '<span class="badge bg-secondary"><i class="bx bx-phone"></i> Téléphone</span>'
                        ];
                        $type_badge = $type_badges[$value['type']] ?? '<span class="badge bg-light text-dark">Inconnu</span>';
                        
                        // Badges statut
                        $statut_badges = [
                            'en_attente' => '<span class="badge bg-warning text-dark"><i class="bx bx-time"></i> En attente</span>',
                            'confirmee' => '<span class="badge bg-info"><i class="bx bx-check"></i> Confirmée</span>',
                            'en_cours' => '<span class="badge bg-primary"><i class="bx bx-play"></i> En cours</span>',
                            'terminee' => '<span class="badge bg-success"><i class="bx bx-check-double"></i> Terminée</span>',
                            'annulee' => '<span class="badge bg-danger"><i class="bx bx-x"></i> Annulée</span>',
                            'refusee' => '<span class="badge bg-dark"><i class="bx bx-block"></i> Refusée</span>'
                        ];
                        $statut_badge = $statut_badges[$value['statut']] ?? '<span class="badge bg-light text-dark">Inconnu</span>';
                        
                        // Badge paiement
                        $paiement_badges = [
                            'en_attente' => '<span class="badge bg-warning text-dark">En attente</span>',
                            'paye' => '<span class="badge bg-success">Payé</span>',
                            'echoue' => '<span class="badge bg-danger">Échoué</span>',
                            'rembourse' => '<span class="badge bg-info">Remboursé</span>'
                        ];
                        $paiement_badge = $paiement_badges[$value['paiement_statut']] ?? '<span class="badge bg-light text-dark">-</span>';
                        
                        // Patient info
                        $patient_nom = htmlspecialchars(($value['patient_prenom'] ?? '') . ' ' . ($value['patient_nom'] ?? 'Inconnu'));
                        $patient_contact = !empty($value['patient_telephone']) ? $value['patient_telephone'] : ($value['patient_email'] ?? '-');
                        
                        // Médecin info
                        $medecin_nom = !empty($value['medecin_nom']) ? 
                            htmlspecialchars(($value['medecin_prenom'] ?? '') . ' ' . $value['medecin_nom']) : 
                            '<span class="text-muted">Non assigné</span>';
                        $medecin_spec = !empty($value['medecin_specialite']) ? 
                            '<small class="text-muted">' . htmlspecialchars($value['medecin_specialite']) . '</small>' : '';
                    ?>
                      <?php if (!can_view_consultation($value)) continue; ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <strong class="text-primary font-monospace"><?= htmlspecialchars($value['numero_consultation']) ?></strong>
                                <?php if (!empty($value['room_id'])): ?>
                                    <br><small class="text-muted font-monospace" title="Room ID"><?= substr($value['room_id'], 0, 15) ?>...</small>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong><?= $patient_nom ?></strong>
                                    <small class="text-muted"><i class="bx bx-phone me-1"></i><?= htmlspecialchars($patient_contact) ?></small>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <?= $medecin_nom ?>
                                    <?= $medecin_spec ?>
                                </div>
                            </td>

                            <td><?= $type_badge ?></td>

                            <td>
                                <?= !empty($value['date_debut']) ? date('d/m/Y H:i', strtotime($value['date_debut'])) : '-' ?>
                            </td>

                            <td>
                                <strong class="text-success"><?= number_format($value['prix_ttc'] ?? 0, 2, ',', ' ') ?> $</strong>
                            </td>
                            

                          <td class="text-center">
    <?php if (!empty($value['room_id'])): ?>
        <a href="<?= base_url('Joinconsultation/index?room=' . $value['room_id'] . '&user=' . $this->session->userdata('user_id')) ?>" 
           class="btn btn-success" 
           target="_blank">
            <i class="bx bx-video me-1"></i> REJOINDRE
        </a>
    <?php else: ?>
        <button class="btn btn-secondary" disabled>
            <i class="bx bx-time me-1"></i> PAS DE ROOM
        </button>
    <?php endif; ?>
</td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-health me-2"></i>Détails de la consultation</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <div class="d-flex justify-content-between align-s-center p-3 bg-light rounded">
                                                    <div>
                                                        <h5 class="mb-0"><?= htmlspecialchars($value['numero_consultation']) ?></h5>
                                                        <small class="text-muted">Créée le <?= date('d/m/Y H:i', strtotime($value['created_at'])) ?></small>
                                                    </div>
                                                    <div>
                                                        <?= $type_badge ?>
                                                        <?= $statut_badge ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <h6 class="text-primary border-bottom pb-2"><i class="bx bx-user me-2"></i>Patient</h6>
                                                <p class="mb-1"><strong><?= $patient_nom ?></strong></p>
                                                <p class="mb-1 text-muted"><i class="bx bx-envelope me-1"></i><?= htmlspecialchars($value['patient_email'] ?? '-') ?></p>
                                                <p class="mb-3 text-muted"><i class="bx bx-phone me-1"></i><?= htmlspecialchars($value['patient_telephone'] ?? '-') ?></p>
                                                
                                                <?php if (!empty($value['poids']) || !empty($value['taille'])): ?>
                                                    <h6 class="text-primary border-bottom pb-2 mt-3"><i class="bx bx-body me-2"></i>Constantes</h6>
                                                    <?php if (!empty($value['poids'])): ?>
                                                        <p class="mb-1">Poids: <strong><?= $value['poids'] ?> kg</strong></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($value['taille'])): ?>
                                                        <p class="mb-1">Taille: <strong><?= $value['taille'] ?> cm</strong></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($value['poids']) && !empty($value['taille'])): 
                                                        $taille_m = $value['taille'] / 100;
                                                        $imc = $value['poids'] / ($taille_m * $taille_m);
                                                    ?>
                                                        <p class="mb-3">IMC: <strong><?= number_format($imc, 1) ?></strong></p>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <h6 class="text-primary border-bottom pb-2"><i class="bx bx-user-md me-2"></i>Médecin</h6>
                                                <?php if (!empty($value['medecin_nom'])): ?>
                                                    <p class="mb-1"><strong><?= $medecin_nom ?></strong></p>
                                                    <p class="mb-3 text-muted"><?= htmlspecialchars($value['medecin_specialite'] ?? 'Spécialité non précisée') ?></p>
                                                <?php else: ?>
                                                    <p class="text-muted">Médecin non assigné</p>
                                                <?php endif; ?>
                                                
                                                <h6 class="text-primary border-bottom pb-2 mt-3"><i class="bx bx-calendar me-2"></i>Rendez-vous</h6>
                                                <p class="mb-1">Date souhaitée: <strong><?= !empty($value['date_souhaitee']) ? date('d/m/Y H:i', strtotime($value['date_souhaitee'])) : '-' ?></strong></p>
                                                <?php if (!empty($value['date_confirmee'])): ?>
                                                    <p class="mb-1 text-success">Date confirmée: <strong><?= date('d/m/Y H:i', strtotime($value['date_confirmee'])) ?></strong></p>
                                                <?php endif; ?>
                                                <?php if (!empty($value['date_debut'])): ?>
                                                    <p class="mb-1">Début: <?= date('d/m/Y H:i', strtotime($value['date_debut'])) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($value['date_fin'])): ?>
                                                    <p class="mb-1">Fin: <?= date('d/m/Y H:i', strtotime($value['date_fin'])) ?></p>
                                                <?php endif; ?>
                                                <p class="mb-3">Durée prévue: <strong><?= $value['duree_minutes'] ?> minutes</strong></p>
                                            </div>
                                            
                                            <div class="col-md-12 mt-3">
                                                <h6 class="text-primary border-bottom pb-2"><i class="bx bx-euro me-2"></i>Finances</h6>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <p class="mb-1">Prix HT: <strong><?= number_format($value['prix_ht'], 2, ',', ' ') ?> $</strong></p>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p class="mb-1">TVA: <strong><?= $value['tva'] ?>%</strong></p>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p class="mb-1">Prix TTC: <strong class="text-success"><?= number_format($value['prix_ttc'], 2, ',', ' ') ?> $</strong></p>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p class="mb-1">Paiement: <?= $paiement_badge ?></p>
                                                    </div>
                                                </div>
                                                <?php if (!empty($value['preuve_paiement'])): ?>
                                                    <p class="mt-2">
                                                        <a href="<?= base_url('attachments/Consultations/' . $value['preuve_paiement']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="bx bx-file me-1"></i>Voir la preuve de paiement
                                                        </a>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($value['symptomes'])): ?>
                                                <div class="col-md-12 mt-3">
                                                    <h6 class="text-primary border-bottom pb-2"><i class="bx bx-activity me-2"></i>Symptômes</h6>
                                                    <div class="p-3 bg-light rounded">
                                                        <?= nl2br(htmlspecialchars($value['symptomes'])) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($value['diagnostic'])): ?>
                                                <div class="col-md-12 mt-3">
                                                    <h6 class="text-primary border-bottom pb-2"><i class="bx bx-stethoscope me-2"></i>Diagnostic</h6>
                                                    <div class="p-3 bg-light rounded">
                                                        <?= nl2br(htmlspecialchars($value['diagnostic'])) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($value['traent'])): ?>
                                                <div class="col-md-12 mt-3">
                                                    <h6 class="text-primary border-bottom pb-2"><i class="bx bx-capsule me-2"></i>Traent</h6>
                                                    <div class="p-3 bg-light rounded">
                                                        <?= nl2br(htmlspecialchars($value['traent'])) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($value['examens_demandes'])): ?>
                                                <div class="col-md-12 mt-3">
                                                    <h6 class="text-primary border-bottom pb-2"><i class="bx bx-test-tube me-2"></i>Examens demandés</h6>
                                                    <div class="p-3 bg-light rounded">
                                                        <?= nl2br(htmlspecialchars($value['examens_demandes'])) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($value['notes_medecin'])): ?>
                                                <div class="col-md-12 mt-3">
                                                    <h6 class="text-primary border-bottom pb-2"><i class="bx bx-notepad me-2"></i>Notes du médecin</h6>
                                                    <div class="p-3 bg-light rounded">
                                                        <?= nl2br(htmlspecialchars($value['notes_medecin'])) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($value['motif_annulation'])): ?>
                                                <div class="col-md-12 mt-3">
                                                    <h6 class="text-danger border-bottom pb-2"><i class="bx bx-x-circle me-2"></i>Motif d'annulation</h6>
                                                    <div class="p-3 bg-light rounded border border-danger">
                                                        <?= nl2br(htmlspecialchars($value['motif_annulation'])) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($value['room_id'])): ?>
                                                <div class="col-md-12 mt-3">
                                                    <h6 class="text-primary border-bottom pb-2"><i class="bx bx-video me-2"></i>Room de consultation</h6>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control font-monospace" value="<?= $value['room_id'] ?>" readonly>
                                                        <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('<?= $value['room_id'] ?>')">
                                                            <i class="bx bx-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la consultation</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Consultations/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <!-- Section Identification -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-id-card me-2"></i>Identification</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">N° Consultation</label>
                                                            <input type="text" class="form-control" value="<?= htmlspecialchars($value['numero_consultation']) ?>" disabled>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Patient <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="patient_id" required>
                                                                <?php foreach ($patients as $patient): ?>
                                                                    <option value="<?= $patient['id'] ?>" <?= ($value['patient_id'] == $patient['id']) ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars(($patient['prenom'] ?? '') . ' ' . ($patient['nom'] ?? '')) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Médecin</label>
                                                            <select class="form-select" name="medecin_id">
                                                                <option value="">Non assigné</option>
                                                                <?php foreach ($medecins as $medecin): ?>
                                                                    <option value="<?= $medecin['id'] ?>" <?= ($value['medecin_id'] == $medecin['id']) ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars(($medecin['prenom'] ?? '') . ' ' . ($medecin['nom'] ?? '') . ' - ' . ($medecin['specialite'] ?? '')) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Type & Date -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-calendar me-2"></i>Type & Planification</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="type" required>
                                                                <option value="video" <?= ($value['type'] == 'video') ? 'selected' : '' ?>>Vidéo</option>
                                                                <option value="presentiel" <?= ($value['type'] == 'presentiel') ? 'selected' : '' ?>>Présentiel</option>
                                                                <option value="telephone" <?= ($value['type'] == 'telephone') ? 'selected' : '' ?>>Téléphone</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Date souhaitée <span class="text-danger">*</span></label>
                                                            <input type="datetime-local" class="form-control" name="date_souhaitee" 
                                                                   value="<?= !empty($value['date_souhaitee']) ? date('Y-m-d\TH:i', strtotime($value['date_souhaitee'])) : '' ?>" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Date confirmée</label>
                                                            <input type="datetime-local" class="form-control" name="date_confirmee" 
                                                                   value="<?= !empty($value['date_confirmee']) ? date('Y-m-d\TH:i', strtotime($value['date_confirmee'])) : '' ?>">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Durée (min) <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" name="duree_minutes" value="<?= $value['duree_minutes'] ?>" required min="5">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Constantes -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-body me-2"></i>Constantes du patient</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Poids (kg)</label>
                                                            <input type="number" step="0.01" class="form-control" name="poids" value="<?= $value['poids'] ?? '' ?>">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Taille (cm)</label>
                                                            <input type="number" step="0.01" class="form-control" name="taille" value="<?= $value['taille'] ?? '' ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Clinique -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-stethoscope me-2"></i>Informations cliniques</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Symptômes</label>
                                                            <textarea class="form-control" name="symptomes" rows="3"><?= htmlspecialchars($value['symptomes'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Examens demandés</label>
                                                            <textarea class="form-control" name="examens_demandes" rows="3"><?= htmlspecialchars($value['examens_demandes'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Diagnostic</label>
                                                            <textarea class="form-control" name="diagnostic" rows="3"><?= htmlspecialchars($value['diagnostic'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Traent</label>
                                                            <textarea class="form-control" name="traent" rows="3"><?= htmlspecialchars($value['traent'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-bold">Notes du médecin</label>
                                                            <textarea class="form-control" name="notes_medecin" rows="2"><?= htmlspecialchars($value['notes_medecin'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Finances -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-euro me-2"></i>Finances</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Prix HT ($) <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" class="form-control" name="prix_ht" value="<?= $value['prix_ht'] ?>" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">TVA (%)</label>
                                                            <input type="number" step="0.01" class="form-control" name="tva" value="<?= $value['tva'] ?>">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Prix TTC ($)</label>
                                                            <input type="text" class="form-control" value="<?= number_format($value['prix_ttc'], 2) ?>" disabled>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-bold">Statut paiement</label>
                                                            <select class="form-select" name="paiement_statut">
                                                                <option value="en_attente" <?= ($value['paiement_statut'] == 'en_attente') ? 'selected' : '' ?>>En attente</option>
                                                                <option value="paye" <?= ($value['paiement_statut'] == 'paye') ? 'selected' : '' ?>>Payé</option>
                                                                <option value="echoue" <?= ($value['paiement_statut'] == 'echoue') ? 'selected' : '' ?>>Échoué</option>
                                                                <option value="rembourse" <?= ($value['paiement_statut'] == 'rembourse') ? 'selected' : '' ?>>Remboursé</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Preuve de paiement</label>
                                                            <input type="file" class="form-control" name="preuve_paiement" accept=".pdf,.jpg,.png,.jpeg">
                                                            <?php if (!empty($value['preuve_paiement'])): ?>
                                                                <small class="text-muted">Actuelle: <?= $value['preuve_paiement'] ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bx bx-save me-2"></i>Enregistrer les modifications
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL CANCEL -->
                        <div class="modal fade" id="cancel_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-x-circle me-2"></i>Annuler la consultation</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('Consultations/Cancel') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <div class="modal-body p-4">
                                            <p>Vous êtes sur le point d'annuler la consultation <strong><?= htmlspecialchars($value['numero_consultation']) ?></strong>.</p>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Motif d'annulation <span class="text-danger">*</span></label>
                                                <textarea class="form-control" name="motif_annulation" rows="3" required placeholder="Veuillez indiquer le motif de l'annulation..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Retour</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bx bx-x me-2"></i>Confirmer l'annulation
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer définitivement la consultation :</p>
                                        <p class="fw-bold"><?= htmlspecialchars($value['numero_consultation']) ?></p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Consultations/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <div class="modal-footer bg-light justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bx bx-trash me-2"></i>Supprimer définitivement
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title"><i class="bx bx-transfer me-2"></i>Changer le statut</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('Consultations/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <div class="modal-body p-4">
                                            <p>Consultation: <strong><?= htmlspecialchars($value['numero_consultation']) ?></strong></p>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nouveau statut</label>
                                                <select class="form-select" name="statut" required>
                                                    <option value="en_attente" <?= ($value['statut'] == 'en_attente') ? 'selected' : '' ?>>En attente</option>
                                                    <option value="confirmee" <?= ($value['statut'] == 'confirmee') ? 'selected' : '' ?>>Confirmée</option>
                                                    <option value="refusee" <?= ($value['statut'] == 'refusee') ? 'selected' : '' ?>>Refusée</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-info text-white">
                                                <i class="bx bx-save me-2"></i>Mettre à jour
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bx bx-health text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune consultation trouvée</p>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#create_consultation">
                                    <i class="bx bx-plus"></i> Créer la première consultation
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

<!-- MODAL CREATE CONSULTATION -->
<div class="modal fade" id="create_consultation" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouvelle Consultation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Consultations/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <!-- Section Identification -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-id-card me-2"></i>Identification</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Patient <span class="text-danger">*</span></label>
                                    <select class="form-select" name="patient_id" required>
                                        <option value="">Sélectionner un patient...</option>
                                        <?php foreach ($patients as $patient): ?>
                                            <option value="<?= $patient['id'] ?>">
                                                <?= htmlspecialchars(($patient['prenom'] ?? '') . ' ' . ($patient['nom'] ?? '')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Médecin</label>
                                    <select class="form-select" name="medecin_id">
                                        <option value="">Non assigné</option>
                                        <?php foreach ($medecins as $medecin): ?>
                                            <option value="<?= $medecin['id'] ?>">
                                                <?= htmlspecialchars(($medecin['prenom'] ?? '') . ' ' . ($medecin['nom'] ?? '') . ' - ' . ($medecin['specialite'] ?? '')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Type & Date -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-calendar me-2"></i>Type & Planification</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="type" required id="create_type">
                                        <option value="video" selected>Vidéo</option>
                                        <option value="presentiel">Présentiel</option>
                                        <option value="telephone">Téléphone</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Date souhaitée <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="date_souhaitee" required value="<?= date('Y-m-d\TH:i') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Durée (min) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="duree_minutes" value="30" required min="5">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Constantes -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-body me-2"></i>Constantes du patient</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Poids (kg)</label>
                                    <input type="number" step="0.01" class="form-control" name="poids" placeholder="70.50">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Taille (cm)</label>
                                    <input type="number" step="0.01" class="form-control" name="taille" placeholder="175.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Clinique -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-stethoscope me-2"></i>Informations cliniques</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Symptômes</label>
                                    <textarea class="form-control" name="symptomes" rows="3" placeholder="Décrivez les symptômes du patient..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Examens demandés</label>
                                    <textarea class="form-control" name="examens_demandes" rows="3" placeholder="Liste des examens à réaliser..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Finances -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-euro me-2"></i>Finances</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Prix HT ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="prix_ht" required placeholder="50.00">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">TVA (%)</label>
                                    <input type="number" step="0.01" class="form-control" name="tva" value="20.00">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Statut paiement</label>
                                    <select class="form-select" name="paiement_statut">
                                        <option value="en_attente" selected>En attente</option>
                                        <option value="paye">Payé</option>
                                        <option value="echoue">Échoué</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Preuve de paiement</label>
                                    <input type="file" class="form-control" name="preuve_paiement" accept=".pdf,.jpg,.png,.jpeg">
                                    <small class="text-muted">Formats: PDF, JPG, PNG</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer la consultation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#consultationsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [9] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>