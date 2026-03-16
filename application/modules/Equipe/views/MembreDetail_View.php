<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Équipe</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Equipe') ?>">Équipe</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Détails</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="<?= base_url('Equipe') ?>">
                <i class="bx bx-arrow-back"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center p-4">
                    <?php 
                    $photo_path = !empty($detail['photo_url']) ? $detail['photo_url'] : 'attachments/Equipe/default-avatar.png';
                    ?>
                    <img src="<?= base_url($photo_path) ?>" 
                         class="rounded-circle border border-3 border-primary mb-3"
                         style="width:150px; height:150px; object-fit:cover;"
                         onerror="this.src='<?= base_url('attachments/Equipe/default-avatar.png') ?>'"
                         alt="Photo">
                    
                    <h4 class="mb-1"><?= htmlspecialchars(($detail['prenom'] ?? '').' '.($detail['nom'] ?? '')) ?></h4>
                    <p class="text-primary mb-2"><?= htmlspecialchars($detail['poste'] ?? '-') ?></p>
                    
                    <?php if (!empty($detail['specialite'])): ?>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info mb-3">
                            <?= htmlspecialchars($detail['specialite']) ?>
                        </span>
                    <?php endif; ?>

                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <?php if (!empty($detail['email'])): ?>
                            <a href="mailto:<?= htmlspecialchars($detail['email']) ?>" class="btn btn-outline-primary btn-sm" title="Envoyer un email">
                                <i class="bx bx-envelope"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($detail['linkedin'])): ?>
                            <a href="<?= htmlspecialchars($detail['linkedin']) ?>" target="_blank" class="btn btn-outline-primary btn-sm" title="Voir LinkedIn">
                                <i class="bx bxl-linkedin"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($detail['est_admin']) && $detail['est_admin'] == 1): ?>
                        <div class="mt-3">
                            <span class="badge bg-danger"><i class="bx bx-shield"></i> Administrateur</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="bx bx-user me-2"></i>Informations du membre</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Nom complet</label>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars(($detail['prenom'] ?? '').' '.($detail['nom'] ?? '')) ?></p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Poste</label>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($detail['poste'] ?? '-') ?></p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Email</label>
                            <p class="mb-0">
                                <?= !empty($detail['email']) ? '<a href="mailto:'.htmlspecialchars($detail['email']).'">'.htmlspecialchars($detail['email']).'</a>' : '<span class="text-muted">Non renseigné</span>' ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">LinkedIn</label>
                            <p class="mb-0">
                                <?= !empty($detail['linkedin']) ? '<a href="'.htmlspecialchars($detail['linkedin']).'" target="_blank">'.htmlspecialchars($detail['linkedin']).'</a>' : '<span class="text-muted">Non renseigné</span>' ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Spécialité</label>
                            <p class="mb-0 fw-bold"><?= !empty($detail['specialite']) ? htmlspecialchars($detail['specialite']) : '<span class="text-muted">Non renseignée</span>' ?></p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">Ordre d'affichage</label>
                            <p class="mb-0 fw-bold"><?= $detail['ordre_affichage'] ?? 0 ?></p>
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
                            <label class="text-muted small">Statut</label>
                            <p class="mb-0">
                                <?php if (!empty($detail['est_admin']) && $detail['est_admin'] == 1): ?>
                                    <span class="badge bg-danger">Administrateur</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">Membre</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <?php if (!empty($detail['biographie'])): ?>
                        <div class="col-12">
                            <label class="text-muted small">Biographie</label>
                            <div class="p-3 bg-light rounded border">
                                <?= nl2br(htmlspecialchars($detail['biographie'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Actions rapides</h6>
                </div>
                <div class="card-body">
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $detail['id_membre'] ?>" class="btn btn-warning mb-2 w-100">
                        <i class="bx bx-edit me-2"></i>Modifier
                    </a>
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $detail['id_membre'] ?>" class="btn btn-danger w-100">
                        <i class="bx bx-trash me-2"></i>Supprimer
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>