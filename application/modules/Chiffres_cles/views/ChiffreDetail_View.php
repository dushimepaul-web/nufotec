<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Contenu</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Chiffres_cles') ?>">Chiffres Clés</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Détails</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="<?= base_url('Chiffres_cles') ?>">
                <i class="bx bx-arrow-back"></i> Retour
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-4 text-center">
                    <?php if (!empty($detail['icone'])): ?>
                        <i class="bx <?= $detail['icone'] ?> text-primary" style="font-size: 4rem;"></i>
                    <?php else: ?>
                        <i class="bx bx-bar-chart-alt-2 text-primary" style="font-size: 4rem;"></i>
                    <?php endif; ?>
                    <h3 class="mt-3 mb-0 display-4 fw-bold text-primary"><?= htmlspecialchars($detail['valeur'] ?? '0') ?></h3>
                    <?php if (!empty($detail['unite'])): ?>
                        <h5 class="text-muted"><?= htmlspecialchars($detail['unite']) ?></h5>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 text-center mb-3">
                            <h4 class="text-dark"><?= htmlspecialchars($detail['etiquette'] ?? '-') ?></h4>
                        </div>

                        <?php if (!empty($detail['description'])): ?>
                        <div class="col-12">
                            <label class="text-muted small">Description</label>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($detail['description'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-6">
                            <label class="text-muted small">Ordre d'affichage</label>
                            <p class="mb-0 fw-bold"><?= $detail['ordre'] ?? 0 ?></p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Année Vision</label>
                            <p class="mb-0 fw-bold">
                                <?= !empty($detail['annee_vision']) ? $detail['annee_vision'] : '<span class="text-muted">Non définie</span>' ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Page associée</label>
                            <p class="mb-0 fw-bold">
                                <?php 
                                $page_name = 'Global (toutes pages)';
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

                        <div class="col-md-6">
                            <label class="text-muted small">Classe de l'icône</label>
                            <p class="mb-0"><code><?= htmlspecialchars($detail['icone'] ?? 'Aucune') ?></code></p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2 justify-content-center">
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $detail['id_chiffre'] ?>" class="btn btn-warning">
                            <i class="bx bx-edit me-2"></i>Modifier
                        </a>
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $detail['id_chiffre'] ?>" class="btn btn-danger">
                            <i class="bx bx-trash me-2"></i>Supprimer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>