<?php
// =====================================================================
// PAGES STATIQUES — la table `pages` a été supprimée de la base.
// Ces données sont définies en dur directement dans les vues.
// =====================================================================
$pages = array(
    array('id_page' => 1,  'titre_page' => 'Home',                                    'slug' => 'home'),
    array('id_page' => 2,  'titre_page' => 'About',                                   'slug' => 'about'),
    array('id_page' => 3,  'titre_page' => 'Background & Strategic Rationale',        'slug' => 'background-strategic-rationale'),
    array('id_page' => 4,  'titre_page' => 'ESG & Sustainability',                    'slug' => 'esg-sustainability'),
    array('id_page' => 5,  'titre_page' => 'Research & Innovation',                   'slug' => 'research-innovation'),
    array('id_page' => 6,  'titre_page' => 'Corporate Structure & Governance',        'slug' => 'corporate-structure-governance'),
    array('id_page' => 7,  'titre_page' => 'nufotec-phytomed-industries-facility',    'slug' => 'nufotec-phytomed-industries-facility'),
    array('id_page' => 8,  'titre_page' => 'Our Product Categories',                  'slug' => 'product-categories'),
    array('id_page' => 9,  'titre_page' => 'Raw Material Acquisition',                'slug' => 'raw-material-acquisition'),
    array('id_page' => 10, 'titre_page' => 'Industrial Technology & Processing Systems', 'slug' => 'industrial-technology'),
    array('id_page' => 11, 'titre_page' => 'Market & Industry Outlook',               'slug' => 'market-outlook'),
    array('id_page' => 12, 'titre_page' => 'Digital Growth & Market Expansion Platform', 'slug' => 'digital-growth'),
    array('id_page' => 13, 'titre_page' => 'Digital Health Consultation',             'slug' => 'digital-health'),
    array('id_page' => 14, 'titre_page' => 'Phased Investment Projection',            'slug' => 'investment-projection'),
    array('id_page' => 15, 'titre_page' => 'Our Investor & Partner Commitment',       'slug' => 'investor-commitment'),
    array('id_page' => 16, 'titre_page' => 'Commission Fee Payment to Brokers',       'slug' => 'broker-commission'),
    array('id_page' => 17, 'titre_page' => 'Risk Analysis & Mitigation Strategies',   'slug' => 'risk-analysis'),
    array('id_page' => 18, 'titre_page' => 'Strategic Partnerships',                  'slug' => 'strategic-partnerships'),
    array('id_page' => 19, 'titre_page' => 'Our Services',                            'slug' => 'our-services'),
    array('id_page' => 22, 'titre_page' => 'Vision & Mission',                        'slug' => 'vision-mission'),
    array('id_page' => 23, 'titre_page' => 'Brokers Form',                            'slug' => 'brokers-form'),
    array('id_page' => 24, 'titre_page' => 'Investors form',                          'slug' => 'investors-form'),
);
$pages_list = $pages; ?>
<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// Helper pour les types de témoignages
$type_badges = [
    'client' => '<span class="badge bg-primary"><i class="bx bx-user"></i> Client</span>',
    'partenaire' => '<span class="badge bg-success"><i class="bx bx-handshake"></i> Partenaire</span>',
    'patient' => '<span class="badge bg-info"><i class="bx bx-plus-medical"></i> Patient</span>',
    'investisseur' => '<span class="badge bg-warning text-dark"><i class="bx bx-money"></i> Investisseur</span>',
    'outgrower' => '<span class="badge bg-secondary"><i class="bx bx-sprout"></i> Outgrower</span>'
];

$type_labels = [
    'client' => 'Client',
    'partenaire' => 'Partenaire',
    'patient' => 'Patient',
    'investisseur' => 'Investisseur',
    'outgrower' => 'Outgrower'
];
?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Contenu</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Témoignages</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_temoignage">
                <i class="bx bx-plus"></i> Nouveau Témoignage
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

    <!-- Statistiques -->
    <div class="row mb-4">
        <?php 
        $total_approuves = 0;
        $total_en_attente = 0;
        foreach ($temoignages as $t) {
            if ($t['est_approuve']) $total_approuves++;
            else $total_en_attente++;
        }
        ?>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Total Témoignages</h6>
                        <h3 class="mb-0"><?= count($temoignages) ?></h3>
                    </div>
                    <i class="bx bx-message-square-detail fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Approuvés</h6>
                        <h3 class="mb-0"><?= $total_approuves ?></h3>
                    </div>
                    <i class="bx bx-check-circle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">En Attente</h6>
                        <h3 class="mb-0"><?= $total_en_attente ?></h3>
                    </div>
                    <i class="bx bx-time fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-message-square-detail me-2"></i>Liste des Témoignages</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="temoignagesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Photo</th>
                            <th width="20%">Personne</th>
                            <th width="12%">Type</th>
                            <th width="25%">Message</th>
                            <th width="8%">Note</th>
                            <th width="10%">Date</th>
                            <th width="10%">Statut</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($temoignages)): $i = 1; foreach ($temoignages as $value): 
                        $photo_path = !empty($value['photo_url']) ? 'attachments/Temoignages/'.$value['photo_url'] : 'assets/frontend/img/default-avatar.jpg';
                        $type_badge = $type_badges[$value['type'] ?? 'client'] ?? '<span class="badge bg-light text-dark">Inconnu</span>';
                        
                        // Tronquer le message
                        $message_short = strlen($value['message'] ?? '') > 100 ? substr($value['message'], 0, 100).'...' : ($value['message'] ?? '');
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <img src="<?= base_url($photo_path) ?>" 
                                     class="rounded-circle border"
                                     style="width:50px; height:50px; object-fit:cover;"
                                     onerror="this.src='<?= base_url('assets/frontend/img/default-avatar.jpg') ?>'"
                                     alt="Photo">
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['nom_personne'] ?? '') ?></strong>
                                    <?php if (!empty($value['fonction'])): ?>
                                        <small class="text-muted"><?= htmlspecialchars($value['fonction']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($value['organisation'])): ?>
                                        <small class="text-primary"><i class="bx bx-buildings me-1"></i><?= htmlspecialchars($value['organisation']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td><?= $type_badge ?></td>

                            <td>
                                <p class="mb-0 text-muted" style="font-size: 0.9rem;"><?= htmlspecialchars($message_short) ?></p>
                                <?php if (strlen($value['message'] ?? '') > 100): ?>
                                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_temoignage'] ?>" class="small text-primary">Lire plus</a>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty($value['note'])): ?>
                                    <div class="text-warning">
                                        <?php for($j=1; $j<=5; $j++): ?>
                                            <i class="bx <?= $j <= $value['note'] ? 'bxs-star' : 'bx-star' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted"><?= $value['note'] ?>/5</small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <small class="text-muted">
                                    <i class="bx bx-calendar me-1"></i>
                                    <?= !empty($value['date_reception']) ? date('d/m/Y', strtotime($value['date_reception'])) : '-' ?>
                                </small>
                                <?php if (!empty($value['id_page_associee'])): ?>
                                    <br>
                                    <span class="badge bg-light text-dark border mt-1">Page #<?= $value['id_page_associee'] ?></span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_temoignage'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_approuve']) && $value['est_approuve'] == 1): ?>
                                        <span class="badge bg-success">Approuvé</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_temoignage'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_temoignage'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_temoignage'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_temoignage'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-message-square-detail me-2"></i>Détails du Témoignage</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center border-end">
                                                <img src="<?= base_url($photo_path) ?>" 
                                                     class="rounded-circle border border-3 border-primary mb-3"
                                                     style="width:120px; height:120px; object-fit:cover;"
                                                     onerror="this.src='<?= base_url('assets/frontend/img/default-avatar.jpg') ?>'"
                                                     alt="Photo">
                                                <h5 class="mb-1"><?= htmlspecialchars($value['nom_personne'] ?? '') ?></h5>
                                                <?php if (!empty($value['fonction'])): ?>
                                                    <p class="text-muted mb-1"><?= htmlspecialchars($value['fonction']) ?></p>
                                                <?php endif; ?>
                                                <?= $type_badge ?>
                                                
                                                <?php if (!empty($value['organisation'])): ?>
                                                    <div class="mt-3 p-2 bg-light rounded">
                                                        <small class="text-muted d-block">Organisation</small>
                                                        <strong><?= htmlspecialchars($value['organisation']) ?></strong>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label class="text-muted small">Message</label>
                                                    <div class="p-3 bg-light rounded">
                                                        <p class="mb-0 fst-italic">"<?= nl2br(htmlspecialchars($value['message'] ?? '')) ?>"</p>
                                                    </div>
                                                </div>
                                                
                                                <div class="row g-3">
                                                    <div class="col-6">
                                                        <label class="text-muted small">Note</label>
                                                        <p class="mb-0 text-warning fs-5">
                                                            <?php if (!empty($value['note'])): ?>
                                                                <?php for($j=1; $j<=5; $j++): ?>
                                                                    <i class="bx <?= $j <= $value['note'] ? 'bxs-star' : 'bx-star' ?>"></i>
                                                                <?php endfor; ?>
                                                                <span class="text-dark fs-6">(<?= $value['note'] ?>/5)</span>
                                                            <?php else: ?>
                                                                <span class="text-muted fs-6">Non noté</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Date de réception</label>
                                                        <p class="mb-0 fw-bold"><?= !empty($value['date_reception']) ? date('d/m/Y', strtotime($value['date_reception'])) : '-' ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Statut</label>
                                                        <p class="mb-0">
                                                            <?php if (!empty($value['est_approuve']) && $value['est_approuve'] == 1): ?>
                                                                <span class="badge bg-success">Approuvé</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning text-dark">En attente</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Page associée</label>
                                                        <p class="mb-0">
                                                            <?php if (!empty($value['id_page_associee'])): ?>
                                                                <span class="badge bg-secondary">
                                                                    Page #<?= $value['id_page_associee'] ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted">Aucune</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id_temoignage'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le Témoignage</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Temoignages/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_temoignage" value="<?= $value['id_temoignage'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Nom de la personne <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="nom_personne" value="<?= htmlspecialchars($value['nom_personne'] ?? '') ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Fonction</label>
                                                    <input type="text" class="form-control" name="fonction" value="<?= htmlspecialchars($value['fonction'] ?? '') ?>" placeholder="Ex: Director of Research">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Organisation</label>
                                                    <input type="text" class="form-control" name="organisation" value="<?= htmlspecialchars($value['organisation'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="type" required>
                                                        <?php foreach ($type_labels as $key => $label): ?>
                                                            <option value="<?= $key ?>" <?= ($value['type'] ?? '') == $key ? 'selected' : '' ?>><?= $label ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Note (1-5)</label>
                                                    <select class="form-select" name="note">
                                                        <option value="">Non noté</option>
                                                        <?php for($n=1; $n<=5; $n++): ?>
                                                            <option value="<?= $n ?>" <?= ($value['note'] ?? '') == $n ? 'selected' : '' ?>><?= $n ?> étoile<?= $n > 1 ? 's' : '' ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Date de réception</label>
                                                    <input type="date" class="form-control" name="date_reception" value="<?= $value['date_reception'] ?? '' ?>">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="message" rows="4" required><?= htmlspecialchars($value['message'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Photo</label>
                                                    <input type="file" class="form-control" name="photo_url" accept="image/*">
                                                    <?php if (!empty($value['photo_url'])): ?>
                                                        <small class="text-muted">Actuelle: <?= $value['photo_url'] ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Page associée</label>
                                                    <select class="form-select" name="id_page_associee">
                                                        <option value="">Aucune</option>
                                                        <?php foreach ($pages as $page): ?>
                                                            <option value="<?= $page['id_page'] ?>" <?= ($value['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>><?= htmlspecialchars($page['titre_page']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" name="est_approuve" id="est_approuve_<?= $value['id_temoignage'] ?>" value="1" <?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="est_approuve_<?= $value['id_temoignage'] ?>">Approuvé (visible sur le site)</label>
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

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id_temoignage'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer le témoignage de <strong><?= htmlspecialchars($value['nom_personne'] ?? '') ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Temoignages/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_temoignage'] ?>">
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
                        <div class="modal fade" id="status_<?= $value['id_temoignage'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? '<i class="bx bx-hide me-2"></i>Mettre en attente' : '<i class="bx bx-check-circle me-2"></i>Approuver' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 'mettre en attente' : 'approuver' ?></strong> le témoignage de <strong><?= htmlspecialchars($value['nom_personne'] ?? '') ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('Temoignages/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_temoignage'] ?>">
                                        <input type="hidden" name="est_approuve" value="<?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 'btn-warning' : 'btn-success' ?>">
                                                <?= (!empty($value['est_approuve']) && $value['est_approuve'] == 1) ? 'Mettre en attente' : 'Approuver' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bx bx-message-square-x text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun témoignage trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- MODAL CREATE -->
<div class="modal fade" id="create_temoignage" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouveau Témoignage</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Temoignages/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nom de la personne <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nom_personne" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fonction</label>
                            <input type="text" class="form-control" name="fonction" placeholder="Ex: Director of Research">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Organisation</label>
                            <input type="text" class="form-control" name="organisation">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" required>
                                <?php foreach ($type_labels as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $key == 'client' ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Note (1-5)</label>
                            <select class="form-select" name="note">
                                <option value="">Non noté</option>
                                <?php for($n=1; $n<=5; $n++): ?>
                                    <option value="<?= $n ?>"><?= $n ?> étoile<?= $n > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de réception</label>
                            <input type="date" class="form-control" name="date_reception" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="message" rows="4" required placeholder="Le témoignage..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Photo</label>
                            <input type="file" class="form-control" name="photo_url" accept="image/*">
                            <small class="text-muted">Formats: JPG, PNG, GIF, WEBP (max 2MB)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Page associée</label>
                            <select class="form-select" name="id_page_associee">
                                <option value="">Aucune</option>
                                <?php foreach ($pages as $page): ?>
                                    <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="est_approuve" id="create_est_approuve" value="1">
                                <label class="form-check-label" for="create_est_approuve">Approuvé immédiatement (visible sur le site)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer le témoignage
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#temoignagesTable').DataTable({
        language: {
            url: '<?= base_url("assets/backend/plugins/datatable/js/fr-FR.json") ?>'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [1, 8] }
        ]
    });
    
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>