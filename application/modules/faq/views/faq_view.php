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
                    <li class="breadcrumb-item active" aria-current="page">Gestion de la FAQ</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_faq">
                <i class="bx bx-plus"></i> Nouvelle Question
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
                <h5 class="mb-0 text-primary"><i class="bx bx-help-circle me-2"></i>Liste des Questions/Réponses (FAQ)</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="faqTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">Ordre</th>
                            <th width="25%">Question</th>
                            <th width="12%">Catégorie</th>
                            <th width="30%">Réponse (aperçu)</th>
                            <th width="8%">Page</th>
                            <th width="8%">Statut</th>
                            <th width="12%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $categories = [
                        'general' => 'Général',
                        'produits' => 'Produits',
                        'qualite' => 'Qualité',
                        'investissement' => 'Investissement',
                        'social' => 'Social',
                        'technique' => 'Technique',
                        'partenariats' => 'Partenariats',
                        'livraison' => 'Livraison',
                        'paiement' => 'Paiement',
                        'autre' => 'Autre'
                    ];
                    
                    $categorie_badges = [
                        'general' => 'bg-secondary',
                        'produits' => 'bg-success',
                        'qualite' => 'bg-info',
                        'investissement' => 'bg-warning text-dark',
                        'social' => 'bg-primary',
                        'technique' => 'bg-dark',
                        'partenariats' => 'bg-danger',
                        'livraison' => 'bg-light text-dark',
                        'paiement' => 'bg-success',
                        'autre' => 'bg-light text-dark'
                    ];

                    if (!empty($faq)): foreach ($faq as $value): 
                        $categorie_label = $categories[$value['categorie'] ?? ''] ?? ucfirst($value['categorie'] ?? 'Général');
                        $categorie_badge = $categorie_badges[$value['categorie'] ?? ''] ?? 'bg-light text-dark';
                        
                        // Page associée
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
                            <td class="text-center">
                                <span class="badge bg-light text-dark border"><?= $value['ordre'] ?? 0 ?></span>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['question'] ?? '') ?></strong>
                                    <small class="text-muted">ID: #<?= $value['id_faq'] ?></small>
                                </div>
                            </td>

                            <td><span class="badge <?= $categorie_badge ?>"><?= $categorie_label ?></span></td>

                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 400px;">
                                    <?= strip_tags($value['reponse'] ?? '-') ?>
                                </small>
                            </td>

                            <td class="text-center">
                                <?php if ($page_titre != '-'): ?>
                                    <small class="text-info" title="<?= htmlspecialchars($page_titre) ?>">
                                        <i class="bx bx-link me-1"></i><?= strlen($page_titre) > 15 ? substr(htmlspecialchars($page_titre), 0, 15).'...' : htmlspecialchars($page_titre) ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_faq'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_publiee']) && $value['est_publiee'] == 1): ?>
                                        <span class="badge bg-success">Publiée</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Brouillon</span>
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
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_faq'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_faq'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_faq'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_faq'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-help-circle me-2"></i>Détails de la FAQ</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="badge <?= $categorie_badge ?> fs-6"><?= $categorie_label ?></span>
                                                    <span class="badge bg-light text-dark border">Ordre: <?= $value['ordre'] ?? 0 ?></span>
                                                </div>
                                                
                                                <div class="card border-0 bg-light mb-3">
                                                    <div class="card-body">
                                                        <h6 class="card-title text-primary"><i class="bx bx-question-mark me-2"></i>Question</h6>
                                                        <p class="mb-0 fs-5"><?= nl2br(htmlspecialchars($value['question'] ?? '-')) ?></p>
                                                    </div>
                                                </div>

                                                <div class="card border-0 bg-light mb-3">
                                                    <div class="card-body">
                                                        <h6 class="card-title text-success"><i class="bx bx-check-circle me-2"></i>Réponse</h6>
                                                        <div class="faq-reponse">
                                                            <?= nl2br(htmlspecialchars($value['reponse'] ?? '-')) ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mt-2">
                                                    <div class="col-6">
                                                        <label class="text-muted small">Statut</label>
                                                        <p class="mb-0">
                                                            <?php if (!empty($value['est_publiee']) && $value['est_publiee'] == 1): ?>
                                                                <span class="badge bg-success">Publiée</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Brouillon</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Page associée</label>
                                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($page_titre) ?></p>
                                                    </div>
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
                        <div class="modal fade" id="update_<?= $value['id_faq'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la FAQ</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Faq/Update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_faq'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <!-- Section Question/Réponse -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-question-mark me-2"></i>Question & Réponse</h6>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Question <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" name="question" rows="2" required><?= htmlspecialchars($value['question'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Réponse <span class="text-danger">*</span></label>
                                                            <textarea class="form-control editor" name="reponse" rows="6" required><?= htmlspecialchars($value['reponse'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section Organisation -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-cog me-2"></i>Organisation & Publication</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Catégorie</label>
                                                            <select class="form-select" name="categorie">
                                                                <?php foreach ($categories as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= ($value['categorie'] ?? '') == $key ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Ordre d'affichage</label>
                                                            <input type="number" class="form-control" name="ordre" value="<?= $value['ordre'] ?? 0 ?>" min="0">
                                                            <small class="text-muted">0 = premier</small>
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
                                                        <div class="col-12">
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input" name="est_publiee" id="est_publiee_<?= $value['id_faq'] ?>" value="1" <?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="est_publiee_<?= $value['id_faq'] ?>">Publier cette question</label>
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
                        <div class="modal fade" id="delete_<?= $value['id_faq'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer la question :</p>
                                        <p class="fw-bold">"<?= htmlspecialchars(substr($value['question'] ?? '', 0, 100)) ?><?= strlen($value['question'] ?? '') > 100 ? '...' : '' ?>"</p>
                                        <p class="text-danger small">Cette action est irréversible (suppression logique).</p>
                                    </div>
                                    <form action="<?= base_url('Faq/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_faq'] ?>">
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
                        <div class="modal fade" id="status_<?= $value['id_faq'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 'bg-secondary' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? '<i class="bx bx-hide me-2"></i>Masquer' : '<i class="bx bx-show me-2"></i>Publier' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 'masquer' : 'publier' ?></strong> cette question ?</p>
                                        <p class="fw-bold">"<?= htmlspecialchars(substr($value['question'] ?? '', 0, 80)) ?><?= strlen($value['question'] ?? '') > 80 ? '...' : '' ?>"</p>
                                    </div>
                                    <form action="<?= base_url('Faq/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_faq'] ?>">
                                        <input type="hidden" name="est_publiee" value="<?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 'btn-secondary' : 'btn-success' ?>">
                                                <?= (!empty($value['est_publiee']) && $value['est_publiee'] == 1) ? 'Masquer' : 'Publier' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bx bx-help-circle text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucune question FAQ trouvée</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


<!-- MODAL CREATE FAQ -->
<div class="modal fade" id="create_faq" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Question FAQ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Faq/Create') ?>" method="POST">
                <div class="modal-body p-4">
                    <!-- Section Question/Réponse -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-question-mark me-2"></i>Question & Réponse</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Question <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="question" rows="2" required placeholder="Saisissez la question fréquente..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Réponse <span class="text-danger">*</span></label>
                                    <textarea class="form-control editor" name="reponse" rows="6" required placeholder="Saisissez la réponse détaillée..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Organisation -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-cog me-2"></i>Organisation & Publication</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Catégorie</label>
                                    <select class="form-select" name="categorie">
                                        <?php foreach ($categories as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= $key == 'general' ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Ordre d'affichage</label>
                                    <input type="number" class="form-control" name="ordre" value="0" min="0">
                                    <small class="text-muted">0 = premier</small>
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
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="est_publiee" id="create_est_publiee" value="1" checked>
                                        <label class="form-check-label" for="create_est_publiee">Publier immédiatement</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer la question
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#faqTable').DataTable({
        language: {
            url: '<?= base_url("assets/backend/plugins/datatable/js/fr-FR.json") ?>'
        },
        order: [[0, 'asc']], // Tri par ordre
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [6] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Initialisation éditeur WYSIWYG si disponible
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.editor',
            height: 200,
            plugins: 'link lists',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link'
        });
    }
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>