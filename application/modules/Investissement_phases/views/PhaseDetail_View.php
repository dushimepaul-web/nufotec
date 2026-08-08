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
        <div class="breadcrumb-title pe-3">Gestion Investissement</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Investissement_phases') ?>">Phases</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Détail</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-secondary" href="<?= base_url('Investissement_phases') ?>">
                <i class="bx bx-arrow-back"></i> Retour à la liste
            </a>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">
                        <i class="bx bx-trending-up me-2"></i><?= htmlspecialchars($detail['nom_phase']) ?>
                    </h5>
                    <span class="badge bg-success fs-6">
                        <?= format_montant($detail['montant_total'], $detail['devise']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2"><i class="bx bx-calendar me-2"></i>Période</h6>
                                    <h4 class="mb-0"><?= $detail['annee_debut'] ?> - <?= $detail['annee_fin'] ?: 'En cours' ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2"><i class="bx bx-money me-2"></i>Montant total</h6>
                                    <h4 class="mb-0 text-success">
                                        <?= format_montant($detail['montant_total'], $detail['devise']) ?>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php 
                    $allocations = get_allocation_array($detail['allocation_details']);
                    if (!empty($allocations)): 
                        $total_pct = array_sum($allocations);
                    ?>
                        <h6 class="text-primary mb-3"><i class="bx bx-pie-chart-alt me-2"></i>Répartition des fonds</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Catégorie</th>
                                        <th width="20%">Pourcentage</th>
                                        <th width="25%">Montant estimé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allocations as $key => $pourcentage): 
                                        $montant_cat = ($detail['montant_total'] * $pourcentage) / 100;
                                        $color = get_allocation_color($key);
                                        $label = get_allocation_label($key);
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?= $color ?> me-2"><?= $label ?></span>
                                                <small class="text-muted">(<?= $key ?>)</small>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-<?= $color ?>" role="progressbar" style="width: <?= $pourcentage ?>%">
                                                        <?= $pourcentage ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold">
                                                <?= format_montant($montant_cat, $detail['devise']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Total</th>
                                        <th><?= $total_pct ?>%</th>
                                        <th class="text-end"><?= format_montant($detail['montant_total'], $detail['devise']) ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($detail['description'])): ?>
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="text-primary mb-3"><i class="bx bx-detail me-2"></i>Description</h6>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($detail['description'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Nom:</td>
                            <td class="text-end fw-bold"><?= htmlspecialchars($detail['nom_phase']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Année début:</td>
                            <td class="text-end"><?= $detail['annee_debut'] ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Année fin:</td>
                            <td class="text-end"><?= $detail['annee_fin'] ?: 'Non définie' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Devise:</td>
                            <td class="text-end"><span class="badge bg-light text-dark border"><?= $detail['devise'] ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Page associée:</td>
                            <td class="text-end">
                                <?php 
                                $page_name = 'Aucune';
                                if (!empty($pages) && !empty($detail['id_page_associee'])) {
                                    foreach ($pages as $page) {
                                        if ($page['id_page'] == $detail['id_page_associee']) {
                                            $page_name = $page['titre_page'];
                                            break;
                                        }
                                    }
                                }
                                echo htmlspecialchars($page_name);
                                ?>
                            </td>
                        </tr>
                    </table>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal">
                            <i class="bx bx-edit me-2"></i>Modifier
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<!-- MODAL EDIT -->
<div class="modal fade" id="editModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier la phase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Investissement_phases/Update') ?>" method="POST">
                <input type="hidden" name="id_phase" value="<?= $detail['id_phase'] ?>">
                
                <div class="modal-body p-4">
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations générales</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nom de la phase <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nom_phase" value="<?= htmlspecialchars($detail['nom_phase']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Année début <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="annee_debut" value="<?= $detail['annee_debut'] ?>" min="2000" max="2100" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Année fin</label>
                                    <input type="number" class="form-control" name="annee_fin" value="<?= $detail['annee_fin'] ?>" min="2000" max="2100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-money me-2"></i>Financement</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Montant total <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="montant_total" value="<?= $detail['montant_total'] ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Devise</label>
                                    <select class="form-select" name="devise">
                                        <option value="USD" <?= $detail['devise'] == 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                        <option value="EUR" <?= $detail['devise'] == 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                                        <option value="GBP" <?= $detail['devise'] == 'GBP' ? 'selected' : '' ?>>GBP (£)</option>
                                        <option value="XOF" <?= $detail['devise'] == 'XOF' ? 'selected' : '' ?>>XOF (FCFA)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-pie-chart-alt me-2"></i>Allocation des fonds</h6>
                            <div id="allocation-container-edit">
                                <?php 
                                $allocations = get_allocation_array($detail['allocation_details']);
                                if (!empty($allocations)): 
                                    foreach ($allocations as $key => $pourcentage): 
                                ?>
                                    <div class="row g-2 mb-2 allocation-row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="allocation_categorie[]" value="<?= $key ?>">
                                        </div>
                                        <div class="col-md-5">
                                            <div class="input-group">
                                                <input type="number" step="0.1" class="form-control" name="allocation_pourcentage[]" value="<?= $pourcentage ?>" min="0" max="100">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeAllocationRow(this)">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php 
                                    endforeach; 
                                else: 
                                ?>
                                    <div class="row g-2 mb-2 allocation-row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="allocation_categorie[]" placeholder="Catégorie">
                                        </div>
                                        <div class="col-md-5">
                                            <div class="input-group">
                                                <input type="number" step="0.1" class="form-control" name="allocation_pourcentage[]" placeholder="%" min="0" max="100">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeAllocationRow(this)">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addAllocationRow('allocation-container-edit')">
                                <i class="bx bx-plus"></i> Ajouter une allocation
                            </button>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-detail me-2"></i>Description</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($detail['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-link me-2"></i>Association</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Page associée</label>
                                    <select class="form-select" name="id_page_associee">
                                        <option value="">-- Aucune --</option>
                                        <?php foreach ($pages as $page): ?>
                                            <option value="<?= $page['id_page'] ?>" <?= ($detail['id_page_associee'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
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
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Ajouter une ligne d'allocation
function addAllocationRow(containerId) {
    const container = document.getElementById(containerId);
    const newRow = document.createElement('div');
    newRow.className = 'row g-2 mb-2 allocation-row';
    newRow.innerHTML = `
        <div class="col-md-5">
            <input type="text" class="form-control" name="allocation_categorie[]" placeholder="Catégorie (ex: construction)">
        </div>
        <div class="col-md-5">
            <div class="input-group">
                <input type="number" step="0.1" class="form-control" name="allocation_pourcentage[]" placeholder="Pourcentage" min="0" max="100">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeAllocationRow(this)">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(newRow);
}

// Supprimer une ligne d'allocation
function removeAllocationRow(button) {
    const row = button.closest('.allocation-row');
    const container = row.parentElement;
    if (container.querySelectorAll('.allocation-row').length > 1) {
        row.remove();
    } else {
        row.querySelectorAll('input').forEach(input => input.value = '');
    }
}
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>