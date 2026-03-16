<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Marketing</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Appels_action') ?>">CTA</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Détails</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="<?= base_url('Appels_action') ?>">
                <i class="bx bx-arrow-back"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="bx bx-bullseye me-2"></i><?= htmlspecialchars($detail['titre'] ?? 'Détails du CTA') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Titre</label>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($detail['titre'] ?? '-') ?></p>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="text-muted small">Ordre d'affichage</label>
                            <p class="mb-0 fw-bold"><?= $detail['ordre'] ?? 0 ?></p>
                        </div>

                        <?php if (!empty($detail['sous_titre'])): ?>
                        <div class="col-12">
                            <label class="text-muted small">Sous-titre</label>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($detail['sous_titre'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-6">
                            <label class="text-muted small">Texte du bouton</label>
                            <p class="mb-0"><span class="badge bg-primary"><?= htmlspecialchars($detail['bouton_texte'] ?? '-') ?></span></p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Lien du bouton</label>
                            <p class="mb-0"><code><?= htmlspecialchars($detail['bouton_lien'] ?? '-') ?></code></p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Type de public</label>
                            <p class="mb-0 fw-bold">
                                <?php 
                                $public_labels = [
                                    'investisseurs' => 'Investisseurs',
                                    'donateurs' => 'Donateurs',
                                    'courtiers' => 'Courtiers',
                                    'acheteurs' => 'Acheteurs',
                                    'patients' => 'Patients',
                                    'tous' => 'Tous les publics'
                                ];
                                echo $public_labels[$detail['type_public'] ?? ''] ?? 'Tous';
                                ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Page associée</label>
                            <p class="mb-0 fw-bold">
                                <?php 
                                $page_name = 'Toutes les pages';
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
                            <label class="text-muted small">Statut</label>
                            <p class="mb-0">
                                <?php if (!empty($detail['est_actif']) && $detail['est_actif'] == 1): ?>
                                    <span class="badge bg-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactif</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Date d'expiration</label>
                            <p class="mb-0 fw-bold">
                                <?= !empty($detail['date_expiration']) ? date('d/m/Y', strtotime($detail['date_expiration'])) : '<span class="text-muted">Aucune</span>' ?>
                                <?php if (!empty($detail['date_expiration']) && strtotime($detail['date_expiration']) < time()): ?>
                                    <span class="badge bg-danger">Expiré</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <?php if (!empty($detail['image_fond_url'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Image de fond</h6>
                </div>
                <div class="card-body text-center p-0">
                    <img src="<?= base_url($detail['image_fond_url']) ?>" 
                         class="img-fluid w-100"
                         onerror="this.src='<?= base_url('attachments/Cta/default-cta.jpg') ?>'"
                         alt="Image CTA"
                         style="max-height: 200px; object-fit: cover;">
                </div>
            </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Aperçu du CTA</h6>
                </div>
                <div class="card-body">
                    <div class="p-3 bg-light rounded border text-center" style="background-image: url('<?= base_url($detail['image_fond_url'] ?? '') ?>'); background-size: cover; background-position: center; min-height: 150px;">
                        <div class="bg-white bg-opacity-75 p-2 rounded">
                            <h6><?= htmlspecialchars($detail['titre'] ?? '') ?></h6>
                            <?php if (!empty($detail['sous_titre'])): ?>
                                <small class="d-block text-muted mb-2"><?= htmlspecialchars(substr($detail['sous_titre'], 0, 50)) ?>...</small>
                            <?php endif; ?>
                            <button class="btn btn-primary btn-sm"><?= htmlspecialchars($detail['bouton_texte'] ?? 'Action') ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Actions rapides</h6>
                </div>
                <div class="card-body">
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $detail['id_cta'] ?>" class="btn btn-warning w-100 mb-2">
                        <i class="bx bx-edit me-2"></i>Modifier
                    </a>
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $detail['id_cta'] ?>" class="btn btn-danger w-100">
                        <i class="bx bx-trash me-2"></i>Supprimer
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>