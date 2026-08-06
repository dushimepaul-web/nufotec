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

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Contenu</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Ressources</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_ressource">
                <i class="bx bx-plus"></i> Nouvelle Ressource
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
                <h5 class="mb-0 text-primary"><i class="bx bx-file me-2"></i>Liste des Ressources Téléchargeables</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="ressourcesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="8%">Type</th>
                            <th width="25%">Titre</th>
                            <th width="12%">Fichier</th>
                            <th width="10%">Taille</th>
                            <th width="10%">Langue</th>
                            <th width="8%">Visibilité</th>
                            <th width="10%">Date Pub.</th>
                            <th width="12%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($ressources)): $i = 1; foreach ($ressources as $value): 
                        // Icône selon type
                        $type_icon = $get_type_icon($value['type']);
$type_label = $get_type_label($value['type']);
                        // Badge langue
                        $langue_badges = [
                            'fr' => '<span class="badge bg-primary">FR</span>',
                            'en' => '<span class="badge bg-success">EN</span>',
                            'es' => '<span class="badge bg-warning text-dark">ES</span>',
                            'pt' => '<span class="badge bg-info">PT</span>'
                        ];
                        $langue_badge = $langue_badges[$value['langue'] ?? 'fr'] ?? '<span class="badge bg-secondary">'.strtoupper($value['langue'] ?? 'FR').'</span>';
                        
                        // Page associée
                        $page_titre = 'Non associée';
                        if (!empty($value['id_page_associee']) && !empty($pages)) {
                            foreach ($pages as $page) {
                                if ($page['id_page'] == $value['id_page_associee']) {
                                    $page_titre = $page['titre_page'];
                                    break;
                                }
                            }
                        }
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td class="text-center">
                                <i class="<?= $type_icon ?>" style="font-size: 2rem;"></i>
                                <br><small class="text-muted"><?= $type_label ?></small>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['titre'] ?? '') ?></strong>
                                    <?php if (!empty($value['description'])): ?>
                                        <small class="text-muted text-truncate" style="max-width: 300px;"><?= htmlspecialchars($value['description']) ?></small>
                                    <?php endif; ?>
                                    <?php if ($page_titre != 'Non associée'): ?>
                                        <small class="text-info"><i class="bx bx-link me-1"></i><?= htmlspecialchars($page_titre) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <?php if (!empty($value['fichier_url'])): ?>
                                    <a href="<?= base_url('attachments/Ressources/'.$value['fichier_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-download me-1"></i>Télécharger
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Aucun fichier</span>
                                <?php endif; ?>
                            </td>

                            <td><?= $value['taille_fichier'] ?? '-' ?></td>

                            <td><?= $langue_badge ?></td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_ressource'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_public']) && $value['est_public'] == 1): ?>
                                        <span class="badge bg-success">Public</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Privé</span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td><?= !empty($value['date_publication']) ? date('d/m/Y', strtotime($value['date_publication'])) : '-' ?></td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_ressource'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_ressource'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_ressource'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_ressource'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-file me-2"></i>Détails de la ressource</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center border-end">
                                                <i class="<?= $type_icon ?>" style="font-size: 5rem;"></i>
                                                <h5 class="mt-3 mb-1"><?= $type_label ?></h5>
                                                <p class="text-muted"><?= htmlspecialchars($value['titre'] ?? '') ?></p>
                                                
                                                <?php if (!empty($value['fichier_url'])): ?>
                                                    <a href="<?= base_url('attachments/Ressources/'.$value['fichier_url']) ?>" target="_blank" class="btn btn-primary btn-sm">
                                                        <i class="bx bx-download me-1"></i>Télécharger
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="text-muted small">Titre complet</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($value['titre'] ?? '-') ?></p>
                                                    </div>
                                                    
                                                    <?php if (!empty($value['description'])): ?>
                                                    <div class="col-12">
                                                        <label class="text-muted small">Description</label>
                                                        <p class="mb-0"><?= nl2br(htmlspecialchars($value['description'])) ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="col-6">
                                                        <label class="text-muted small">Type</label>
                                                        <p class="mb-0 fw-bold"><?= $type_label ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Langue</label>
                                                        <p class="mb-0"><?= $langue_badge ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Date de publication</label>
                                                        <p class="mb-0 fw-bold"><?= !empty($value['date_publication']) ? date('d/m/Y', strtotime($value['date_publication'])) : '-' ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Taille du fichier</label>
                                                        <p class="mb-0 fw-bold"><?= $value['taille_fichier'] ?? '-' ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Visibilité</label>
                                                        <p class="mb-0">
                                                            <?php if (!empty($value['est_public']) && $value['est_public'] == 1): ?>
                                                                <span class="badge bg-success">Public</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Privé</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Page associée</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($page_titre) ?></p>
                                                    </div>
                                                    
                                                    <?php if (!empty($value['fichier_url'])): ?>
                                                    <div class="col-12">
                                                        <label class="text-muted small">Nom du fichier</label>
                                                        <p class="mb-0 font-monospace small text-muted"><?= $value['fichier_url'] ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="col-6">
                                                        <label class="text-muted small">Créé le</label>
                                                        <p class="mb-0 fw-bold"><?= !empty($value['created_at']) ? date('d/m/Y H:i', strtotime($value['created_at'])) : '-' ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Modifié le</label>
                                                        <p class="mb-0 fw-bold"><?= !empty($value['updated_at']) ? date('d/m/Y H:i', strtotime($value['updated_at'])) : '-' ?></p>
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
                        <div class="modal fade" id="update_<?= $value['id_ressource'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la ressource</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Ressources_telechargeables/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $value['id_ressource'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <!-- Section Informations -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations générales</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-8">
                                                            <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($value['titre'] ?? '') ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="type" required>
                                                                <option value="pdf" <?= ($value['type'] ?? '') == 'pdf' ? 'selected' : '' ?>>PDF</option>
                                                                <option value="video" <?= ($value['type'] ?? '') == 'video' ? 'selected' : '' ?>>Vidéo</option>
                                                                <option value="etude_clinique" <?= ($value['type'] ?? '') == 'etude_clinique' ? 'selected' : '' ?>>Étude clinique</option>
                                                                <option value="rapport_annuel" <?= ($value['type'] ?? '') == 'rapport_annuel' ? 'selected' : '' ?>>Rapport annuel</option>
                                                                <option value="fiche_technique" <?= ($value['type'] ?? '') == 'fiche_technique' ? 'selected' : '' ?>>Fiche technique</option>
                                                                <option value="brochure" <?= ($value['type'] ?? '') == 'brochure' ? 'selected' : '' ?>>Brochure</option>
                                                                <option value="dossier_investisseur" <?= ($value['type'] ?? '') == 'dossier_investisseur' ? 'selected' : '' ?>>Dossier investisseur</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Description</label>
                                                            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Publication -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-calendar me-2"></i>Publication & Association</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Date de publication</label>
                                                            <input type="date" class="form-control" name="date_publication" value="<?= $value['date_publication'] ?? date('Y-m-d') ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Langue</label>
                                                            <select class="form-select" name="langue">
                                                                <option value="fr" <?= ($value['langue'] ?? '') == 'fr' ? 'selected' : '' ?>>Français</option>
                                                                <option value="en" <?= ($value['langue'] ?? '') == 'en' ? 'selected' : '' ?>>English</option>
                                                                <option value="es" <?= ($value['langue'] ?? '') == 'es' ? 'selected' : '' ?>>Español</option>
                                                                <option value="pt" <?= ($value['langue'] ?? '') == 'pt' ? 'selected' : '' ?>>Português</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Page associée</label>
                                                            <select class="form-select" name="id_page_associee">
                                                                <option value="">Aucune</option>
                                                                <?php foreach ($pages as $page): ?>
                                                                    <option value="<?= $page['id_page'] ?>" <?= ($value['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($page['titre_page']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Fichier -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-file me-2"></i>Fichier</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-8">
                                                            <label class="form-label fw-bold">Nouveau fichier (laisser vide pour conserver l'actuel)</label>
                                                            <input type="file" class="form-control" name="fichier" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mp4,.mp3,.zip,.rar">
                                                            <small class="text-muted">Formats acceptés: PDF, DOC, XLS, PPT, MP4, MP3, ZIP (max 50MB)</small>
                                                            <?php if (!empty($value['fichier_url'])): ?>
                                                                <div class="mt-2 p-2 bg-white rounded border">
                                                                    <small class="text-muted">Fichier actuel:</small><br>
                                                                    <strong><?= $value['fichier_url'] ?></strong> (<?= $value['taille_fichier'] ?? '?' ?>)
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Visibilité</label>
                                                            <div class="form-check form-switch mt-2">
                                                                <input type="checkbox" class="form-check-input" name="est_public" id="est_public_<?= $value['id_ressource'] ?>" value="1" <?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="est_public_<?= $value['id_ressource'] ?>">Rendre public</label>
                                                            </div>
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

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id_ressource'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer <strong><?= htmlspecialchars($value['titre'] ?? '') ?></strong>.</p>
                                        <?php if (!empty($value['fichier_url'])): ?>
                                            <p class="text-warning small"><i class="bx bx-info-circle me-1"></i>Le fichier associé sera également supprimé.</p>
                                        <?php endif; ?>
                                        <p class="text-danger small">Cette action est irréversible (suppression logique).</p>
                                    </div>
                                    <form action="<?= base_url('Ressources_telechargeables/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_ressource'] ?>">
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
                        <div class="modal fade" id="status_<?= $value['id_ressource'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 'bg-secondary' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['est_public']) && $value['est_public'] == 1) ? '<i class="bx bx-hide me-2"></i>Rendre privé' : '<i class="bx bx-show me-2"></i>Rendre public' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 'masquer' : 'rendre public' ?></strong> la ressource <strong><?= htmlspecialchars($value['titre'] ?? '') ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('Ressources_telechargeables/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_ressource'] ?>">
                                        <input type="hidden" name="est_public" value="<?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 'btn-secondary' : 'btn-success' ?>">
                                                <?= (!empty($value['est_public']) && $value['est_public'] == 1) ? 'Rendre privé' : 'Rendre public' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bx bx-file text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune ressource trouvée</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


<!-- MODAL CREATE RESSOURCE -->
<div class="modal fade" id="create_ressource" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Ressource</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Ressources_telechargeables/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <!-- Section Informations -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations générales</h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="titre" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="type" required>
                                        <option value="pdf" selected>PDF</option>
                                        <option value="video">Vidéo</option>
                                        <option value="etude_clinique">Étude clinique</option>
                                        <option value="rapport_annuel">Rapport annuel</option>
                                        <option value="fiche_technique">Fiche technique</option>
                                        <option value="brochure">Brochure</option>
                                        <option value="dossier_investisseur">Dossier investisseur</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea class="form-control" name="description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Publication -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-calendar me-2"></i>Publication & Association</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Date de publication</label>
                                    <input type="date" class="form-control" name="date_publication" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Langue</label>
                                    <select class="form-select" name="langue">
                                        <option value="fr" selected>Français</option>
                                        <option value="en">English</option>
                                        <option value="es">Español</option>
                                        <option value="pt">Português</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Page associée</label>
                                    <select class="form-select" name="id_page_associee">
                                        <option value="">Aucune</option>
                                        <?php foreach ($pages as $page): ?>
                                            <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Fichier -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-upload me-2"></i>Fichier</h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Fichier</label>
                                    <input type="file" class="form-control" name="fichier" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mp4,.mp3,.zip,.rar">
                                    <small class="text-muted">Formats acceptés: PDF, DOC, XLS, PPT, MP4, MP3, ZIP (max 50MB)</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Visibilité</label>
                                    <div class="form-check form-switch mt-2">
                                        <input type="checkbox" class="form-check-input" name="est_public" id="create_est_public" value="1" checked>
                                        <label class="form-check-label" for="create_est_public">Rendre public</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer la ressource
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#ressourcesTable').DataTable({
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
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>