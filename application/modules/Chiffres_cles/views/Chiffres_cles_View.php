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
                    <li class="breadcrumb-item active" aria-current="page">Chiffres Clés</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_chiffre">
                <i class="bx bx-plus"></i> Nouveau Chiffre
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
                <h5 class="mb-0 text-primary"><i class="bx bx-bar-chart-alt-2 me-2"></i>Liste des Chiffres Clés</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="chiffresTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">Ordre</th>
                            <th width="20%">Étiquette</th>
                            <th width="12%">Valeur</th>
                            <th width="10%">Unité</th>
                            <th width="8%">Année</th>
                            <th width="15%">Page</th>
                            <th width="8%">Icône</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($chiffres)): foreach ($chiffres as $value): 
                        // Récupérer le nom de la page associée
                        $page_name = 'Global';
                        if (!empty($value['id_page_associee'])) {
                            foreach ($pages as $page) {
                                if ($page['id_page'] == $value['id_page_associee']) {
                                    $page_name = $page['titre_page'];
                                    break;
                                }
                            }
                        }
                    ?>
                        <tr>
                            <td>
                                <div class="btn-group-vertical btn-group-sm">
                                    <form action="<?= base_url('Chiffres_cles/ChangeOrdre') ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $value['id_chiffre'] ?>">
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="Monter">
                                            <i class="bx bx-chevron-up"></i>
                                        </button>
                                    </form>
                                    <span class="badge bg-light text-dark border my-1"><?= $value['ordre'] ?? 0 ?></span>
                                    <form action="<?= base_url('Chiffres_cles/ChangeOrdre') ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $value['id_chiffre'] ?>">
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="Descendre">
                                            <i class="bx bx-chevron-down"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['etiquette'] ?? '') ?></strong>
                                    <?php if (!empty($value['description'])): ?>
                                        <small class="text-muted text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars(substr($value['description'], 0, 50)) ?>...
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-primary fs-6"><?= htmlspecialchars($value['valeur'] ?? '0') ?></span>
                            </td>

                            <td>
                                <?php if (!empty($value['unite'])): ?>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($value['unite']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty($value['annee_vision'])): ?>
                                    <span class="badge bg-info"><?= $value['annee_vision'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bx bx-file me-1"></i><?= htmlspecialchars($page_name) ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <?php if (!empty($value['icone'])): ?>
                                    <i class="bx <?= $value['icone'] ?> fs-4 text-primary"></i>
                                    <br><small class="text-muted"><?= $value['icone'] ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('Chiffres_cles/ChiffreDetail/'.$value['id_chiffre']) ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_chiffre'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_chiffre'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id_chiffre'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le chiffre clé</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Chiffres_cles/Update') ?>" method="POST">
                                        <input type="hidden" name="id_chiffre" value="<?= $value['id_chiffre'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Étiquette <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="etiquette" value="<?= htmlspecialchars($value['etiquette'] ?? '') ?>" required placeholder="Ex: Seed Capital">
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold">Valeur <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="valeur" value="<?= htmlspecialchars($value['valeur'] ?? '') ?>" required placeholder="Ex: 40+">
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold">Unité</label>
                                                    <input type="text" class="form-control" name="unite" value="<?= htmlspecialchars($value['unite'] ?? '') ?>" placeholder="Ex: USD Million">
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Description</label>
                                                    <textarea class="form-control" name="description" rows="2" placeholder="Description du chiffre clé..."><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Icône (classe Boxicons)</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bx <?= $value['icone'] ?? 'bx-star' ?>"></i></span>
                                                        <input type="text" class="form-control" name="icone" value="<?= htmlspecialchars($value['icone'] ?? '') ?>" placeholder="Ex: bx-money">
                                                    </div>
                                                    <small class="text-muted"><a href="https://boxicons.com/" target="_blank">Voir les icônes</a></small>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Ordre d'affichage</label>
                                                    <input type="number" class="form-control" name="ordre" value="<?= $value['ordre'] ?? 0 ?>" min="0">
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">Année Vision</label>
                                                    <input type="number" class="form-control" name="annee_vision" value="<?= $value['annee_vision'] ?? '' ?>" placeholder="Ex: 2031">
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Page associée</label>
                                                    <select class="form-select" name="id_page_associee">
                                                        <option value="">Global (toutes pages)</option>
                                                        <?php foreach ($pages as $page): ?>
                                                            <option value="<?= $page['id_page'] ?>" <?= ($value['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($page['titre_page']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
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
                        <div class="modal fade" id="delete_<?= $value['id_chiffre'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer le chiffre clé <strong><?= htmlspecialchars($value['etiquette'] ?? '') ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Chiffres_cles/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_chiffre'] ?>">
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

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-bar-chart-alt-2 text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun chiffre clé trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- MODAL CREATE CHIFFRE -->
<div class="modal fade" id="create_chiffre" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouveau Chiffre Clé</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Chiffres_cles/Create') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Étiquette <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="etiquette" required placeholder="Ex: Seed Capital">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Valeur <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="valeur" required placeholder="Ex: 40+">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Unité</label>
                            <input type="text" class="form-control" name="unite" placeholder="Ex: USD Million">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Description du chiffre clé..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Icône (classe Boxicons)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-star"></i></span>
                                <input type="text" class="form-control" name="icone" placeholder="Ex: bx-money">
                            </div>
                            <small class="text-muted"><a href="https://boxicons.com/" target="_blank">Voir les icônes Boxicons</a></small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ordre d'affichage</label>
                            <input type="number" class="form-control" name="ordre" value="0" min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Année Vision</label>
                            <input type="number" class="form-control" name="annee_vision" placeholder="Ex: 2031">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Page associée</label>
                            <select class="form-select" name="id_page_associee">
                                <option value="">Global (toutes pages)</option>
                                <?php foreach ($pages as $page): ?>
                                    <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer le chiffre clé
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#chiffresTable').DataTable({
        language: {
            url: '<?= base_url("assets/backend/plugins/datatable/js/fr-FR.json") ?>'
        },
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [6, 7] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>