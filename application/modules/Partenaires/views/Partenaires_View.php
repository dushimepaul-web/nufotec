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
// Helper arrays for labels and badges
$type_labels = [
    'outgrower' => 'Outgrower',
    'client_export' => 'Client Export',
    'fournisseur' => 'Fournisseur',
    'institutionnel' => 'Institutionnel',
    'scientifique' => 'Scientifique',
    'financier' => 'Financier',
    'regulateur' => 'Régulateur'
];

$type_badges = [
    'outgrower' => 'bg-success',
    'client_export' => 'bg-info',
    'fournisseur' => 'bg-warning text-dark',
    'institutionnel' => 'bg-secondary',
    'scientifique' => 'bg-primary',
    'financier' => 'bg-dark',
    'regulateur' => 'bg-danger'
];

$niveau_labels = [
    'strategique' => 'Stratégique',
    'technique' => 'Technique',
    'commercial' => 'Commercial',
    'academique' => 'Académique'
];

$niveau_badges = [
    'strategique' => 'bg-dark',
    'technique' => 'bg-info',
    'commercial' => 'bg-success',
    'academique' => 'bg-primary'
];
?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Partenaires</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Partenaires</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_partenaire">
                <i class="bx bx-plus"></i> Nouveau Partenaire
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
                <h5 class="mb-0 text-primary"><i class="bx bx-group me-2"></i>Liste des Partenaires</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="partenairesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="8%">Logo</th>
                            <th width="20%">Nom</th>
                            <th width="12%">Type</th>
                            <th width="12%">Niveau</th>
                            <th width="10%">Pays</th>
                            <th width="8%">Statut</th>
                            <th width="10%">Page</th>
                            <th width="10%">Créé le</th>
                            <th width="9%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($partenaires)): $i = 1; foreach ($partenaires as $value): 
                        $type_label = $type_labels[$value['type_partenaire']] ?? ucfirst($value['type_partenaire']);
                        $type_badge = $type_badges[$value['type_partenaire']] ?? 'bg-light text-dark';
                        
                        $niveau_label = $niveau_labels[$value['niveau_partenariat']] ?? ucfirst($value['niveau_partenariat']);
                        $niveau_badge = $niveau_badges[$value['niveau_partenariat']] ?? 'bg-light text-dark';
                        
                        // Récupérer le nom de la page associée
                        $page_titre = '-';
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
                                <?php if (!empty($value['logo_url'])): ?>
                                    <img src="<?= base_url($value['logo_url']) ?>" alt="Logo" class="rounded" style="width: 45px; height: 45px; object-fit: contain;" onerror="this.src='<?= base_url('attachments/partenaires/default-logo.png') ?>'">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width: 45px; height: 45px;">
                                        <i class="bx bx-building text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['nom']) ?></strong>
                                    <?php if (!empty($value['site_web'])): ?>
                                        <small>
                                            <a href="<?= htmlspecialchars($value['site_web']) ?>" target="_blank" class="text-decoration-none">
                                                <i class="bx bx-link-external me-1"></i><?= parse_url($value['site_web'], PHP_URL_HOST) ?>
                                            </a>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge <?= $type_badge ?>"><?= $type_label ?></span>
                            </td>

                            <td>
                                <span class="badge <?= $niveau_badge ?>"><?= $niveau_label ?></span>
                            </td>

                            <td class="text-center">
                                <?= !empty($value['pays']) ? htmlspecialchars($value['pays']) : '<span class="text-muted">-</span>' ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_partenaire'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_actif']) && $value['est_actif'] == 1): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactif</span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td class="text-center">
                                <?php if ($page_titre != '-'): ?>
                                    <small class="text-info"><?= htmlspecialchars(strlen($page_titre) > 15 ? substr($page_titre, 0, 15).'...' : $page_titre) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td><?= !empty($value['created_at']) ? date('d/m/Y', strtotime($value['created_at'])) : '-' ?></td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_partenaire'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_partenaire'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_partenaire'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_partenaire'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-building me-2"></i>Détails du partenaire</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center mb-3 border-end">
                                                <?php if (!empty($value['logo_url'])): ?>
                                                    <img src="<?= base_url($value['logo_url']) ?>" alt="Logo" class="img-fluid rounded mb-2" style="max-height: 120px; object-fit: contain;" onerror="this.src='<?= base_url('attachments/partenaires/default-logo.png') ?>'">
                                                <?php else: ?>
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                                        <i class="bx bx-building text-muted" style="font-size: 3rem;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="mt-2">
                                                    <?php if (!empty($value['est_actif']) && $value['est_actif'] == 1): ?>
                                                        <span class="badge bg-success">Actif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactif</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h4 class="mb-2"><?= htmlspecialchars($value['nom']) ?></h4>
                                                <div class="mb-3">
                                                    <span class="badge <?= $type_badge ?> me-1"><?= $type_label ?></span>
                                                    <span class="badge <?= $niveau_badge ?>"><?= $niveau_label ?></span>
                                                </div>
                                                
                                                <?php if (!empty($value['description'])): ?>
                                                    <p class="text-muted"><?= nl2br(htmlspecialchars($value['description'])) ?></p>
                                                <?php endif; ?>

                                                <div class="row g-2 mt-3">
                                                    <?php if (!empty($value['pays'])): ?>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Pays</small>
                                                        <strong><?= htmlspecialchars($value['pays']) ?></strong>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($value['date_debut'])): ?>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Partenariat depuis</small>
                                                        <strong><?= date('d/m/Y', strtotime($value['date_debut'])) ?></strong>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($value['site_web'])): ?>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block">Site web</small>
                                                        <a href="<?= htmlspecialchars($value['site_web']) ?>" target="_blank" class="text-decoration-none">
                                                            <?= htmlspecialchars($value['site_web']) ?>
                                                        </a>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($page_titre != '-'): ?>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block">Page associée</small>
                                                        <strong class="text-primary"><?= htmlspecialchars($page_titre) ?></strong>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">ID Partenaire</small>
                                                        <p class="mb-0 font-monospace small text-muted">#<?= $value['id_partenaire'] ?></p>
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
                        <div class="modal fade" id="update_<?= $value['id_partenaire'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le partenaire</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Partenaires/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $value['id_partenaire'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <!-- Section Informations -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Nom du partenaire <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($value['nom']) ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="type_partenaire" required>
                                                                <?php foreach ($type_labels as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= $value['type_partenaire'] == $key ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Niveau <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="niveau_partenariat" required>
                                                                <?php foreach ($niveau_labels as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= $value['niveau_partenariat'] == $key ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Description</label>
                                                            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Coordonnées -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-globe me-2"></i>Coordonnées</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Pays</label>
                                                            <input type="text" class="form-control" name="pays" value="<?= htmlspecialchars($value['pays'] ?? '') ?>" placeholder="Ex: Zambia">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Site web</label>
                                                            <input type="url" class="form-control" name="site_web" value="<?= htmlspecialchars($value['site_web'] ?? '') ?>" placeholder="https://...">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Date début partenariat</label>
                                                            <input type="date" class="form-control" name="date_debut" value="<?= $value['date_debut'] ?? '' ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch mt-4">
                                                                <input class="form-check-input" type="checkbox" name="est_actif" id="est_actif_<?= $value['id_partenaire'] ?>" value="1" <?= $value['est_actif'] ? 'checked' : '' ?>>
                                                                <label class="form-check-label fw-bold" for="est_actif_<?= $value['id_partenaire'] ?>">Partenaire actif</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Logo & Page -->
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Logo & Association</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Logo actuel</label>
                                                            <?php if (!empty($value['logo_url'])): ?>
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <img src="<?= base_url($value['logo_url']) ?>" class="rounded" style="height: 50px; width: 50px; object-fit: contain;" onerror="this.style.display='none'">
                                                                    <span class="ms-2 text-muted small"><?= basename($value['logo_url']) ?></span>
                                                                </div>
                                                            <?php else: ?>
                                                                <p class="text-muted small">Aucun logo</p>
                                                            <?php endif; ?>
                                                            <label class="form-label fw-bold">Nouveau logo</label>
                                                            <input type="file" class="form-control" name="logo" accept="image/*">
                                                            <small class="text-muted">Formats: JPG, PNG, SVG, WEBP (max 2Mo). Laissez vide pour conserver l'actuel.</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Page associée</label>
                                                            <select class="form-select" name="id_page_associee">
                                                                <option value="">Aucune page</option>
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
                                        </div>

                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-warning"><i class="bx bx-save me-1"></i>Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id_partenaire'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        </div>
                                        <h5 class="mb-2">Êtes-vous sûr ?</h5>
                                        <p class="text-muted mb-0">
                                            Vous allez supprimer <strong><?= htmlspecialchars($value['nom']) ?></strong>.<br>
                                            Cette action est irréversible (suppression logique).
                                        </p>
                                    </div>
                                    <form action="<?= base_url('Partenaires/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_partenaire'] ?>">
                                        <div class="modal-footer bg-light justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bx bx-trash me-1"></i>Supprimer définitivement
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $value['id_partenaire'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? '<i class="bx bx-block me-2"></i>Désactiver' : '<i class="bx bx-check-circle me-2"></i>Activer' ?> le partenaire
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'désactiver' : 'activer' ?></strong> le partenaire <strong><?= htmlspecialchars($value['nom']) ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('Partenaires/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_partenaire'] ?>">
                                        <input type="hidden" name="est_actif" value="<?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'btn-warning' : 'btn-success' ?>">
                                                <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bx bx-group" style="font-size: 3rem;"></i>
                                    <p class="mt-2 mb-0">Aucun partenaire enregistré</p>
                                    <small>Cliquez sur "Nouveau Partenaire" pour ajouter</small>
                                </div>
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
<div class="modal fade" id="create_partenaire" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouveau Partenaire</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Partenaires/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <!-- Section Informations -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations obligatoires</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Nom du partenaire <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nom" placeholder="Ex: University of Zambia" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="type_partenaire" required>
                                        <option value="">Sélectionner...</option>
                                        <?php foreach ($type_labels as $key => $label): ?>
                                            <option value="<?= $key ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Niveau <span class="text-danger">*</span></label>
                                    <select class="form-select" name="niveau_partenariat" required>
                                        <?php foreach ($niveau_labels as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= $key == 'commercial' ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="Description du partenariat..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Coordonnées -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-globe me-2"></i>Coordonnées</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Pays</label>
                                    <input type="text" class="form-control" name="pays" placeholder="Ex: Zambia">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Site web</label>
                                    <input type="url" class="form-control" name="site_web" placeholder="https://...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Date début</label>
                                    <input type="date" class="form-control" name="date_debut">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" name="est_actif" id="create_est_actif" value="1" checked>
                                        <label class="form-check-label fw-bold" for="create_est_actif">Partenaire actif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Logo & Page -->
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Logo & Page</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Logo</label>
                                    <input type="file" class="form-control" name="logo" accept="image/*">
                                    <small class="text-muted">JPG, PNG, SVG, WEBP (max 2Mo)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Page associée</label>
                                    <select class="form-select" name="id_page_associee">
                                        <option value="">Aucune page</option>
                                        <?php foreach ($pages as $page): ?>
                                            <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-save me-1"></i>Créer le partenaire</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#partenairesTable').DataTable({
        language: {
            url: '<?= base_url("assets/backend/plugins/datatable/js/fr-FR.json") ?>'
        },
        order: [[2, 'asc']], // Sort by name
        pageLength: 25,
        responsive: true
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>