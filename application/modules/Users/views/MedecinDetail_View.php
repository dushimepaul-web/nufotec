<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Administration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Users/Medecins') ?>">Médecins</a></li>
                    <li class="breadcrumb-item active">Détails</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="<?= base_url('Users/Medecins') ?>" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-2"></i>Retour
            </a>
        </div>
    </div>

    <?php if (empty($detail)): ?>
        <div class="alert alert-danger">Médecin non trouvé</div>
    <?php else: 
        $photo_path = !empty($detail['photo']) ? 'attachments/Users/'.$detail['photo'] : 'assets/images/default-avatar.png';
        $est_dispo = isset($detail['est_disponible']) && $detail['est_disponible'] == 1;
        $est_verifie = isset($detail['est_verifie']) && $detail['est_verifie'] == 1;
    ?>

    <div class="row">
        <!-- Colonne infos principales -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center p-4">
                    <img src="<?= base_url($photo_path) ?>" 
                         class="rounded-circle border border-3 border-white shadow mb-3"
                         style="width:150px; height:150px; object-fit:cover;"
                         onerror="this.src='<?= base_url('assets/images/default-avatar.png') ?>'"
                         alt="Photo">
                    <h4 class="mb-1">Dr. <?= htmlspecialchars(($detail['prenom'] ?? '').' '.($detail['nom'] ?? '')) ?></h4>
                    <p class="text-muted mb-3"><?= htmlspecialchars($detail['specialite'] ?? 'Spécialité non définie') ?></p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-<?= $est_dispo ? 'success' : 'danger' ?> fs-6">
                            <i class="bx <?= $est_dispo ? 'bx-check-circle' : 'bx-block' ?>"></i>
                            <?= $est_dispo ? 'Disponible' : 'Indisponible' ?>
                        </span>
                        <span class="badge bg-<?= $est_verifie ? 'success' : 'warning' ?> fs-6">
                            <i class="bx <?= $est_verifie ? 'bx-shield' : 'bx-time' ?>"></i>
                            <?= $est_verifie ? 'Vérifié' : 'En attente' ?>
                        </span>
                    </div>

                    <?php if ($detail['note_moyenne'] > 0): ?>
                    <div class="text-warning mb-3">
                        <?php for($s = 1; $s <= 5; $s++): ?>
                            <i class="bx <?= $s <= round($detail['note_moyenne']) ? 'bxs-star' : 'bx-star' ?> fs-5"></i>
                        <?php endfor; ?>
                        <span class="text-muted ms-2">(<?= $detail['nombre_avis'] ?> avis)</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bx bx-envelope me-2"></i>Contact</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><i class="bx bx-envelope text-primary me-2"></i><?= htmlspecialchars($detail['email'] ?? '-') ?></p>
                    <p class="mb-0"><i class="bx bx-phone text-primary me-2"></i><?= htmlspecialchars($detail['telephone'] ?? '-') ?></p>
                </div>
            </div>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bx bx-envelope me-2"></i>Nombres des Ptient consulte</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><i class="bx bx-envelope text-primary me-2"></i><?= htmlspecialchars($detail['email'] ?? '-') ?></p>
                </div>
            </div>
        </div>

        <!-- Colonne détails -->
        <div class="col-lg-8">
            <!-- Informations professionnelles -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bx bx-id-card me-2"></i>Informations professionnelles</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Numéro de licence</label>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($detail['numero_licence'] ?? '-') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Années d'expérience</label>
                            <p class="fw-bold mb-0"><?= $detail['annees_experience'] ?? 0 ?> ans</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Honoraires</label>
                            <p class="fw-bold text-primary mb-0"><?= !empty($detail['honoraires_consultation']) ? number_format($detail['honoraires_consultation'], 2).' $' : '-' ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Langues parlées</label>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($detail['langues_parlees'] ?? 'Non spécifié') ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($detail['diplomes'])): ?>
                    <div class="mt-3">
                        <label class="text-muted small">Diplômes</label>
                        <div class="bg-light p-3 rounded mt-1">
                            <?= nl2br(htmlspecialchars($detail['diplomes'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Horaires -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bx bx-time me-2"></i>Horaires de consultation</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($horaires)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Jour</th>
                                    <th>Heure début</th>
                                    <th>Heure fin</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($horaires as $h): ?>
                                <tr class="<?= ($h['est_actif'] ?? 0) == 1 ? '' : 'table-secondary' ?>">
                                    <td class="text-capitalize fw-bold"><?= $h['jour_semaine'] ?></td>
                                    <td><?= substr($h['heure_debut'], 0, 5) ?></td>
                                    <td><?= substr($h['heure_fin'], 0, 5) ?></td>
                                    <td>
                                        <?php if (($h['est_actif'] ?? 0) == 1): ?>
                                            <span class="badge bg-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="bx bx-time me-2"></i>Aucun horaire défini pour ce médecin
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Consultations récentes -->
            <?php if (!empty($consultations)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>Consultations récentes</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consultations as $c): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
                                    <td><?= htmlspecialchars(($c['patient_prenom'] ?? '').' '.($c['patient_nom'] ?? '')) ?></td>
                                    <td><span class="badge bg-info"><?= $c['statut'] ?? 'En cours' ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php endif; ?>

</div>


<?php include VIEWPATH.'includes/backend/Footer.php'; ?>