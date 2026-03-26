<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Télémédecine</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Consultations</li>
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
            <div class="d-flex align-items-center">
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
                            <th width="10%">Date debut Consultation</th>
                            <th width="8%">Prix</th>
                            <th width="10%">Statut</th>
                            <th width="8%">Paiement</th>
                            <th width="12%">Actions</th>
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
                                <br><small class="text-muted">HT: <?= number_format($value['prix_ht'] ?? 0, 2, ',', ' ') ?> $</small>
                            </td>

                            <td class="text-center">
                                <?php if (in_array($value['statut'], ['en_attente', 'confirmee'])): ?>
                                    <button type="button" 
                                           onclick="openStatusModal_<?= $value['id'] ?>()"
                                           class="btn btn-primary btn-sm">
                                        <?= $statut_badge ?>
                                    </button>
                                <?php else: ?>
                                    <span class="btn btn-secondary btn-sm disabled" style="opacity: 0.65;">
                                        <?= $statut_badge ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?= $paiement_badge ?>
                                <?php if (!empty($value['preuve_paiement'])): ?>
                                    <br>
                                    <a href="<?= base_url('Consultations/download_file/preuve/' . $value['id']) ?>" target="_blank" class="btn btn-xs btn-outline-info mt-1" title="Voir la preuve">
                                        <i class="bx bx-file"></i> Preuve
                                    </a>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        
                                        <?php if ($value['statut'] == 'confirmee'): ?>
                                            <li>
                                                <a class="dropdown-item text-success" href="<?= base_url('Consultations/StartConsultation/' . $value['id']) ?>">
                                                    <i class="bx bx-play-circle me-2"></i>Démarrer
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php if ($value['statut'] == 'en_cours'): ?>
                                            <li>
                                                <a class="dropdown-item text-danger" href="<?= base_url('Consultations/EndConsultation/' . $value['id']) ?>">
                                                    <i class="bx bx-stop-circle me-2"></i>Terminer
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php if (in_array($value['statut'], ['en_attente', 'confirmee'])): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-warning" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#cancel_<?= $value['id'] ?>">
                                                    <i class="bx bx-x-circle me-2"></i>Annuler
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php if (in_array($value['statut'], ['annulee', 'refusee', 'terminee'])): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id'] ?>">
                                                    <i class="bx bx-trash me-2"></i>Supprimer
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- ==================== MODAL VIEW DÉTAILS (avec fichiers) ==================== -->
                        <div class="modal fade" id="view_<?= $value['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title"><i class="bx bx-detail me-2"></i>Détails de la consultation</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Informations générales -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <p><strong>N° Consultation :</strong> <?= htmlspecialchars($value['numero_consultation']) ?></p>
                                                <p><strong>Patient :</strong> <?= $patient_nom ?></p>
                                                <p><strong>Médecin :</strong> <?= strip_tags($medecin_nom) ?> <?= strip_tags($medecin_spec) ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Type :</strong> <?= strip_tags($type_badge) ?></p>
                                                <p><strong>Date souhaitée :</strong> <?= !empty($value['date_debut']) ? date('d/m/Y H:i', strtotime($value['date_debut'])) : '-' ?></p>
                                                <p><strong>Durée prévue :</strong> <?= $value['duree_minutes'] ?? 30 ?> min</p>
                                                <?php if (!empty($value['date_debut'])): ?>
                                                    <p><strong>Début effectif :</strong> <?= date('d/m/Y H:i', strtotime($value['date_debut'])) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($value['date_fin'])): ?>
                                                    <p><strong>Fin effective :</strong> <?= date('d/m/Y H:i', strtotime($value['date_fin'])) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Constantes -->
                                        <?php if (!empty($value['poids']) || !empty($value['taille'])): ?>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <p><strong>Poids :</strong> <?= htmlspecialchars($value['poids']) ?> kg</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Taille :</strong> <?= htmlspecialchars($value['taille']) ?> cm</p>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Symptômes -->
                                        <?php if (!empty($value['symptomes'])): ?>
                                        <div class="mb-3">
                                            <strong>Symptômes :</strong>
                                            <p class="bg-light p-2 rounded"><?= nl2br(htmlspecialchars($value['symptomes'])) ?></p>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Diagnostic -->
                                        <?php if (!empty($value['diagnostic'])): ?>
                                        <div class="mb-3">
                                            <strong>Diagnostic :</strong>
                                            <p class="bg-light p-2 rounded"><?= nl2br(htmlspecialchars($value['diagnostic'])) ?></p>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Traitement -->
                                        <?php if (!empty($value['traitement'])): ?>
                                        <div class="mb-3">
                                            <strong>Traitement :</strong>
                                            <p class="bg-light p-2 rounded"><?= nl2br(htmlspecialchars($value['traitement'])) ?></p>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Notes du médecin -->
                                        <?php if (!empty($value['notes_medecin'])): ?>
                                        <div class="mb-3">
                                            <strong>Notes du médecin :</strong>
                                            <p class="bg-light p-2 rounded"><?= nl2br(htmlspecialchars($value['notes_medecin'])) ?></p>
                                        </div>
                                        <?php endif; ?>

                                        <!-- FICHIERS : Examens demandés -->
                                        <?php 
                                        $examens = !empty($value['examens_demandes']) ? json_decode($value['examens_demandes'], true) : [];
                                        if (is_array($examens) && count($examens) > 0): ?>
                                        <div class="mb-3">
                                            <strong class="text-primary"><i class="bx bx-file me-1"></i>Examens demandés</strong>
                                            <ul class="list-group mt-2">
                                            <?php foreach ($examens as $index => $file): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>
                                                        <i class="bx bx-file"></i>
                                                        <?= htmlspecialchars(basename($file)) ?>
                                                    </span>
                                                    <a href="<?= base_url('Consultations/download_file/examen/' . $value['id'] . '/' . $index) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-show"></i> Voir
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>

                                        <!-- FICHIERS : Ordonnances -->
                                        <?php 
                                        $ordonnances = !empty($value['ordonnances']) ? json_decode($value['ordonnances'], true) : [];
                                        if (is_array($ordonnances) && count($ordonnances) > 0): ?>
                                        <div class="mb-3">
                                            <strong class="text-success"><i class="bx bx-file me-1"></i>Ordonnances</strong>
                                            <ul class="list-group mt-2">
                                            <?php foreach ($ordonnances as $index => $file): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>
                                                        <i class="bx bx-file"></i>
                                                        <?= htmlspecialchars(basename($file)) ?>
                                                    </span>
                                                    <a href="<?= base_url('Consultations/download_file/ordonnance/' . $value['id'] . '/' . $index) ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                        <i class="bx bx-show"></i> Voir
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>

                                        <!-- FICHIERS : Documents liés (table documents) -->
                                        <?php if (!empty($value['documents'])): ?>
                                        <div class="mb-3">
                                            <strong class="text-info"><i class="bx bx-file me-1"></i>Documents associés</strong>
                                            <ul class="list-group mt-2">
                                            <?php foreach ($value['documents'] as $doc): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>
                                                        <i class="bx bx-file"></i>
                                                        <?= htmlspecialchars($doc['original_name'] ?: $doc['filename']) ?>
                                                        <small class="text-muted">(<?= date('d/m/Y', strtotime($doc['created_at'])) ?>)</small>
                                                    </span>
                                                    <a href="<?= base_url('Documents/Download/' . $doc['id']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                        <i class="bx bx-show"></i> Voir
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Preuve de paiement -->
                                        <?php if (!empty($value['preuve_paiement'])): ?>
                                        <div class="mb-3">
                                            <strong class="text-warning"><i class="bx bx-receipt me-1"></i>Preuve de paiement</strong>
                                            <div class="mt-2">
                                                <a href="<?= base_url('Consultations/download_file/preuve/' . $value['id']) ?>" target="_blank" class="btn btn-warning btn-sm">
                                                    <i class="bx bx-show"></i> Voir la preuve
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Informations de paiement -->
                                        <div class="row mt-3 p-3 bg-light rounded">
                                            <div class="col-md-6">
                                                <p><strong>Prix :</strong> <?= number_format($value['prix_ttc'] ?? 0, 2, ',', ' ') ?> $</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Statut paiement :</strong> <?= strip_tags($paiement_badge) ?></p>
                                                <?php if (!empty($value['mode_paiement'])): ?>
                                                    <p><strong>Mode de paiement :</strong> <?= htmlspecialchars($value['mode_paiement']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Motif d'annulation/refus -->
                                        <?php if (!empty($value['motif_annulation'])): ?>
                                        <div class="alert alert-warning mt-3">
                                            <strong>Motif d'annulation/refus :</strong><br>
                                            <?= nl2br(htmlspecialchars($value['motif_annulation'])) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== MODAL UPDATE ==================== -->
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
                                                            <input type="datetime-local" class="form-control" name="date_debut" 
                                                                   value="<?= !empty($value['date_debut']) ? date('Y-m-d\TH:i', strtotime($value['date_debut'])) : '' ?>" required>
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
                                                            <textarea class="form-control" name="examens_demandes" rows="3" placeholder="Liste des examens ou fichiers JSON"><?= htmlspecialchars($value['examens_demandes'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Diagnostic</label>
                                                            <textarea class="form-control" name="diagnostic" rows="3"><?= htmlspecialchars($value['diagnostic'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Traitement</label>
                                                            <textarea class="form-control" name="traitement" rows="3"><?= htmlspecialchars($value['traitement'] ?? '') ?></textarea>
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
                                                            <label class="form-label fw-bold">Mode de paiement</label>
                                                            <input type="text" class="form-control" name="mode_paiement" value="<?= htmlspecialchars($value['mode_paiement'] ?? '') ?>">
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

                        <!-- ==================== MODAL CANCEL (Annulation) ==================== -->
                        <div class="modal fade" id="cancel_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-white">
                                        <h5 class="modal-title"><i class="bx bx-x-circle me-2"></i>Annuler la consultation</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('Consultations/Cancel') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <div class="modal-body">
                                            <p>Êtes-vous sûr de vouloir annuler cette consultation ?</p>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Motif de l'annulation <span class="text-danger">*</span></label>
                                                <textarea class="form-control" name="motif_annulation" rows="3" required placeholder="Veuillez indiquer le motif..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non, retour</button>
                                            <button type="submit" class="btn btn-warning">Oui, annuler</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== MODAL DELETE ==================== -->
                        <div class="modal fade" id="delete_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p>Vous êtes sur le point de supprimer la consultation <strong><?= htmlspecialchars($value['numero_consultation']) ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible et supprimera également tous les fichiers associés.</p>
                                    </div>
                                    <form action="<?= base_url('Consultations/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                       <!-- MODAL STATUS PURE JAVASCRIPT -->
                        <div id="statusModal_<?= $value['id'] ?>" class="custom-modal" style="display: none;">
                            <div class="modal-overlay" onclick="closeStatusModal_<?= $value['id'] ?>()"></div>
                            <div class="modal-content-js">
                                <div class="modal-header-js">
                                    <h5><i class="bx bx-transfer me-2"></i>Changer le statut</h5>
                                    <button type="button" class="btn-close-js" onclick="closeStatusModal_<?= $value['id'] ?>()">&times;</button>
                                </div>
                                
                                <form action="<?= base_url('Consultations/Entente/ChangeStatus') ?>" method="POST" id="statusForm_<?= $value['id'] ?>">
                                    <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                    
                                    <div class="modal-body-js">
                                        <p>Consultation: <strong><?= htmlspecialchars($value['numero_consultation']) ?></strong></p>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nouveau statut</label>
                                            <select class="form-select" name="statut" id="statusSelect_<?= $value['id'] ?>" 
                                                    onchange="handleStatusChange_<?= $value['id'] ?>()">
                                                <option value="en_attente" <?= ($value['statut'] == 'en_attente') ? 'selected' : '' ?>>En attente</option>
                                                <option value="confirmee" <?= ($value['statut'] == 'confirmee') ? 'selected' : '' ?>>Confirmée</option>
                                                <option value="refusee" <?= ($value['statut'] == 'refusee') ? 'selected' : '' ?>>Refusée</option>
                                            </select>
                                        </div>

                                        <!-- Champ date/heure (caché par défaut) -->
                                        <div class="mb-3 status-field" id="dateField_<?= $value['id'] ?>" style="display: none;">
                                            <label class="form-label fw-bold text-success">
                                                <i class="bx bx-calendar-check me-1"></i>
                                                Date et heure du debut de la téléconsultation <span class="text-danger">*</span>
                                            </label>
                                            <input type="datetime-local" 
                                                   class="form-control border-success" 
                                                   name="date_debut" 
                                                   id="dateInput_<?= $value['id'] ?>"
                                                   value="<?= !empty($value['date_debut']) ? date('Y-m-d\TH:i', strtotime($value['date_debut'])) : '' ?>">
                                            <small class="text-muted">Date et heure confirmées pour la consultation</small><br>
                                            <label class="form-label fw-bold text-success py-3">
                                                <i class="bx bx-calendar-check me-1"></i>
                                                Date et heure de la fin téléconsultation <span class="text-danger">*</span>
                                            </label>
                                            <input type="datetime-local" 
                                                   class="form-control border-success" 
                                                   name="date_fin" 
                                                   id="dateInput_<?= $value['id'] ?>"
                                                   value="<?= !empty($value['date_fin']) ? date('Y-m-d\TH:i', strtotime($value['date_fin'])) : '' ?>">
                                            <small class="text-muted"> Date et heure de la fin téléconsultation </small>
                                        </div>

                                        <!-- Champ motif refus (caché par défaut) -->
                                        <div class="mb-3 status-field" id="motifField_<?= $value['id'] ?>" style="display: none;">
                                            <label class="form-label fw-bold text-danger">
                                                <i class="bx bx-x-circle me-1"></i>
                                                Motif du refus <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control border-danger" 
                                                      name="motif_annulation" 
                                                      id="motifInput_<?= $value['id'] ?>"
                                                      rows="3" 
                                                      placeholder="Veuillez indiquer le motif du refus..."></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="modal-footer-js">
                                        <button type="button" class="btn btn-secondary" onclick="closeStatusModal_<?= $value['id'] ?>()">Annuler</button>
                                        <button type="submit" class="btn btn-info text-white">
                                            <i class="bx bx-save me-2"></i>Mettre à jour
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- SCRIPT POUR CETTE MODAL -->
                        <script>
                        (function() {
                            // Ouvrir la modal
                            window.openStatusModal_<?= $value['id'] ?> = function() {
                                var modal = document.getElementById('statusModal_<?= $value['id'] ?>');
                                modal.style.display = 'block';
                                document.body.style.overflow = 'hidden';
                                
                                // Initialiser l'affichage
                                window.handleStatusChange_<?= $value['id'] ?>();
                            };

                            // Fermer la modal
                            window.closeStatusModal_<?= $value['id'] ?> = function() {
                                var modal = document.getElementById('statusModal_<?= $value['id'] ?>');
                                modal.style.display = 'none';
                                document.body.style.overflow = '';
                                
                                // Reset formulaire
                                document.getElementById('statusForm_<?= $value['id'] ?>').reset();
                                hideAllFields();
                            };

                            // Cacher tous les champs
                            function hideAllFields() {
                                document.getElementById('dateField_<?= $value['id'] ?>').style.display = 'none';
                                document.getElementById('motifField_<?= $value['id'] ?>').style.display = 'none';
                                document.getElementById('dateInput_<?= $value['id'] ?>').required = false;
                                document.getElementById('motifInput_<?= $value['id'] ?>').required = false;
                            }

                            // Gérer le changement de statut
                            window.handleStatusChange_<?= $value['id'] ?> = function() {
                                var status = document.getElementById('statusSelect_<?= $value['id'] ?>').value;
                                
                                hideAllFields();
                                
                                if (status === 'confirmee') {
                                    var dateField = document.getElementById('dateField_<?= $value['id'] ?>');
                                    var dateInput = document.getElementById('dateInput_<?= $value['id'] ?>');
                                    
                                    dateField.style.display = 'block';
                                    dateInput.required = true;
                                    
                                    // Date minimum = maintenant
                                    var now = new Date();
                                    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                                    dateInput.min = now.toISOString().slice(0, 16);
                                    
                                    // Valeur par défaut = maintenant si vide
                                    if (!dateInput.value) {
                                        dateInput.value = now.toISOString().slice(0, 16);
                                    }
                                } 
                                else if (status === 'refusee') {
                                    var motifField = document.getElementById('motifField_<?= $value['id'] ?>');
                                    var motifInput = document.getElementById('motifInput_<?= $value['id'] ?>');
                                    
                                    motifField.style.display = 'block';
                                    motifInput.required = true;
                                }
                            };

                            // Fermer avec ESC
                            document.addEventListener('keydown', function(e) {
                                if (e.key === 'Escape') {
                                    var modal = document.getElementById('statusModal_<?= $value['id'] ?>');
                                    if (modal.style.display === 'block') {
                                        window.closeStatusModal_<?= $value['id'] ?>();
                                    }
                                }
                            });
                        })();
                        </script>

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

<!-- ==================== MODAL CREATE CONSULTATION ==================== -->
<div class="modal fade" id="create_consultation" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle consultation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Consultations/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <!-- Reprendre la structure de la modal UPDATE mais en mode création -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-id-card me-2"></i>Patient & Médecin</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Patient <span class="text-danger">*</span></label>
                                    <select class="form-select" name="patient_id" required>
                                        <option value="">Sélectionner un patient</option>
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

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-calendar me-2"></i>Type & Planification</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="type" required>
                                        <option value="video">Vidéo</option>
                                        <option value="presentiel">Présentiel</option>
                                        <option value="telephone">Téléphone</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Date souhaitée <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="date_debut" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Durée (min) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="duree_minutes" value="30" required min="5">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-stethoscope me-2"></i>Informations cliniques</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Symptômes</label>
                                    <textarea class="form-control" name="symptomes" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Examens demandés</label>
                                    <textarea class="form-control" name="examens_demandes" rows="3" placeholder="Liste des examens ou fichiers JSON"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Diagnostic</label>
                                    <textarea class="form-control" name="diagnostic" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Traitement</label>
                                    <textarea class="form-control" name="traitement" rows="3"></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Notes du médecin</label>
                                    <textarea class="form-control" name="notes_medecin" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-euro me-2"></i>Finances</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Prix HT ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="prix_ht" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">TVA (%)</label>
                                    <input type="number" step="0.01" class="form-control" name="tva" value="20">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Mode de paiement</label>
                                    <input type="text" class="form-control" name="mode_paiement">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Statut paiement</label>
                                    <select class="form-select" name="paiement_statut">
                                        <option value="en_attente">En attente</option>
                                        <option value="paye">Payé</option>
                                        <option value="echoue">Échoué</option>
                                        <option value="rembourse">Remboursé</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Preuve de paiement</label>
                                    <input type="file" class="form-control" name="preuve_paiement" accept=".pdf,.jpg,.png,.jpeg">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Créer la consultation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSS POUR MODALS JAVASCRIPT -->
<style>
.custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    font-family: inherit;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(2px);
}

.modal-content-js {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: modalSlideIn 0.3s ease;
    overflow: hidden;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -55%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

.modal-header-js {
    background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
    color: white;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header-js h5 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.btn-close-js {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-close-js:hover {
    background: rgba(255,255,255,0.3);
    transform: rotate(90deg);
}

.modal-body-js {
    padding: 24px;
    max-height: 60vh;
    overflow-y: auto;
}

.modal-footer-js {
    padding: 16px 24px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: #f8f9fa;
}

.status-field {
    animation: fieldFadeIn 0.3s ease;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 16px;
}

@keyframes fieldFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- SCRIPT GLOBAL DataTable -->
<script>
$(document).ready(function() {
    $('#consultationsTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [{ orderable: false, targets: [9] }]
    });

    // Auto-hide alerts
    setTimeout(() => $('.alert').fadeOut('slow'), 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>