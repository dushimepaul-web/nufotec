<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Projet</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Etapes_projet') ?>">Étapes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Détails</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="<?= base_url('Etapes_projet') ?>">
                <i class="bx bx-arrow-back"></i> Retour
            </a>
        </div>
    </div>

    <?php
    $phase_colors = [
        'Pre-Seed' => 'bg-info',
        'Phase I' => 'bg-primary',
        'Phase II' => 'bg-warning',
        'Phase II-III' => 'bg-orange',
        'Phase III' => 'bg-success'
    ];
    
    $status_badges = [
        'a_venir' => ['label' => 'À venir', 'class' => 'bg-secondary'],
        'en_cours' => ['label' => 'En cours', 'class' => 'bg-primary'],
        'termine' => ['label' => 'Terminé', 'class' => 'bg-success'],
        'retarde' => ['label' => 'Retardé', 'class' => 'bg-danger']
    ];
    
    $phase_color = $phase_colors[$detail['phase'] ?? ''] ?? 'bg-secondary';
    $status = $status_badges[$detail['statut'] ?? 'a_venir'];
    ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge <?= $phase_color ?> mb-2"><?= htmlspecialchars($detail['phase'] ?? '-') ?></span>
                        <h4 class="mb-0"><?= htmlspecialchars($detail['titre'] ?? '-') ?></h4>
                    </div>
                    <span class="badge <?= $status['class'] ?> fs-6"><?= $status['label'] ?></span>
                </div>
                <div class="card-body">
                    <?php if (!empty($detail['description'])): ?>
                    <div class="mb-4">
                        <label class="text-muted small">Description</label>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($detail['description'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Date de début</label>
                            <p class="mb-0 fw-bold"><i class="bx bx-play-circle text-success me-1"></i> <?= !empty($detail['date_debut']) ? date('d/m/Y', strtotime($detail['date_debut'])) : '-' ?></p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Date fin prévue</label>
                            <p class="mb-0 fw-bold"><i class="bx bx-stop-circle text-danger me-1"></i> <?= !empty($detail['date_fin_prevue']) ? date('d/m/Y', strtotime($detail['date_fin_prevue'])) : '-' ?></p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Date fin réelle</label>
                            <p class="mb-0 fw-bold">
                                <?php if (!empty($detail['date_fin_reelle'])): ?>
                                    <i class="bx bx-check-circle text-success me-1"></i> <?= date('d/m/Y', strtotime($detail['date_fin_reelle'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">Non terminée</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Page associée</label>
                            <p class="mb-0 fw-bold">
                                <?php 
                                $page_name = 'Aucune';
                                if (!empty($detail['id_page_associee'])) {
                                    foreach ($pages as $page) {
                                        if ($page['id_page'] == $detail['id_page_associee']) {
                                            $page_name = $page['titre_page'];
                                            break;
                                        }
                                    }
                                }
                                echo htmlspecialchars($page_name);
                                ?>
                            </p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="text-muted small">Progression</label>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-3" style="height: 25px;">
                                <div class="progress-bar <?= $phase_color ?> progress-bar-striped <?= ($detail['statut'] == 'en_cours') ? 'progress-bar-animated' : '' ?>" role="progressbar" style="width: <?= $detail['pourcentage_avancement'] ?? 0 ?>%">
                                    <?= $detail['pourcentage_avancement'] ?? 0 ?>%
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $detail['id_etape'] ?>" class="btn btn-warning">
                            <i class="bx bx-edit me-2"></i>Modifier
                        </a>
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $detail['id_etape'] ?>" class="btn btn-danger">
                            <i class="bx bx-trash me-2"></i>Supprimer
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Résumé</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Statut</span>
                        <span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Phase</span>
                        <span class="badge <?= $phase_color ?>"><?= htmlspecialchars($detail['phase'] ?? '-') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Progression</span>
                        <span class="fw-bold"><?= $detail['pourcentage_avancement'] ?? 0 ?>%</span>
                    </div>
                    <hr>
                    <div class="text-center">
                        <?php if ($detail['statut'] == 'termine'): ?>
                            <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-success">Étape terminée</p>
                        <?php elseif ($detail['statut'] == 'en_cours'): ?>
                            <i class="bx bx-loader-circle text-primary" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-primary">Étape en cours</p>
                        <?php elseif ($detail['statut'] == 'retarde'): ?>
                            <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-danger">Étape retardée</p>
                        <?php else: ?>
                            <i class="bx bx-time text-secondary" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-secondary">Étape à venir</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>