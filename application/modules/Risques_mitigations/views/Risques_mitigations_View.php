<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// Helper arrays for labels and badges
$niveau_labels = [
    'faible' => 'Faible',
    'moyen' => 'Moyen',
    'eleve' => 'Élevé'
];

$niveau_badges = [
    'faible' => 'bg-success',
    'moyen' => 'bg-warning text-dark',
    'eleve' => 'bg-danger'
];

$niveau_icons = [
    'faible' => 'bx-check-circle',
    'moyen' => 'bx-error',
    'eleve' => 'bx-error-alt'
];

// Catégories prédéfinies
$categories_list = [
    'ESG' => 'ESG',
    'Financier' => 'Financier',
    'Environnemental' => 'Environnemental',
    'Opérationnel' => 'Opérationnel',
    'Social' => 'Social',
    'Sécurité' => 'Sécurité',
    'Réglementaire' => 'Réglementaire',
    'Supply Chain' => 'Supply Chain',
    'Gouvernance' => 'Gouvernance',
    'Marché' => 'Marché'
];
?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Gestion des Risques</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Risques & Mitigations</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_risque">
                <i class="bx bx-plus"></i> Nouveau Risque
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

    <!-- Statistiques par niveau -->
    <div class="row mb-4">
        <?php 
        $counts = [];
        foreach ($risques as $r) {
            $niveau = $r['niveau_risque'];
            $counts[$niveau] = ($counts[$niveau] ?? 0) + 1;
        }
        foreach ($niveau_labels as $key => $label): 
            $count = $counts[$key] ?? 0;
        ?>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge <?= $niveau_badges[$key] ?> mb-1"><?= $label ?></span>
                        <h4 class="mb-0"><?= $count ?> risques</h4>
                    </div>
                    <i class="bx <?= $niveau_icons[$key] ?> fs-1 text-muted"></i>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-shield-alt-2 me-2"></i>Liste des Risques & Mitigations</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="risquesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">Ordre</th>
                            <th width="25%">Risque</th>
                            <th width="30%">Mitigation</th>
                            <th width="12%">Catégorie</th>
                            <th width="10%">Niveau</th>
                            <th width="10%">Page</th>
                            <th width="8%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($risques)): foreach ($risques as $value): 
                        $niveau_label = $niveau_labels[$value['niveau_risque']] ?? ucfirst($value['niveau_risque']);
                        $niveau_badge = $niveau_badges[$value['niveau_risque']] ?? 'bg-light text-dark';
                        $niveau_icon = $niveau_icons[$value['niveau_risque']] ?? 'bx-help-circle';
                        
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
                            <td class="text-center">
                                <span class="badge bg-light text-dark border"><?= $value['ordre'] ?? 0 ?></span>
                            </td>

                            <td>
                                <div class="d-flex align-items-start">
                                    <i class="bx bx-error-circle text-danger me-2 mt-1"></i>
                                    <div>
                                        <strong class="text-dark"><?= htmlspecialchars($value['risque']) ?></strong>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex align-items-start">
                                    <i class="bx bx-check-shield text-success me-2 mt-1"></i>
                                    <div class="text-muted small"><?= htmlspecialchars($value['mitigation']) ?></div>
                                </div>
                            </td>

                            <td class="text-center">
                                <?php if (!empty($value['categorie'])): ?>
                                    <span class="badge bg-light text-dark border">
                                        <?= htmlspecialchars($value['categorie']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#niveau_<?= $value['id_risque'] ?>" class="text-decoration-none">
                                    <span class="badge <?= $niveau_badge ?>">
                                        <i class="bx <?= $niveau_icon ?> me-1"></i><?= $niveau_label ?>
                                    </span>
                                </a>
                            </td>

                            <td class="text-center">
                                <?php if ($page_titre != '-'): ?>
                                    <small class="text-info"><?= htmlspecialchars(strlen($page_titre) > 15 ? substr($page_titre, 0, 15).'...' : $page_titre) ?></small>
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
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_risque'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_risque'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_risque'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_risque'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-shield-alt-2 me-2"></i>Détails du Risque</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="bx bx-folder me-1"></i><?= !empty($value['categorie']) ? htmlspecialchars($value['categorie']) : 'Non catégorisé' ?>
                                                    </span>
                                                    <span class="badge <?= $niveau_badge ?> fs-6">
                                                        <i class="bx <?= $niveau_icon ?> me-1"></i><?= $niveau_label ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="card border-danger border-start-4 border-0 bg-light">
                                                    <div class="card-body">
                                                        <h6 class="text-danger mb-2"><i class="bx bx-error-circle me-2"></i>RISQUE</h6>
                                                        <p class="mb-0 fs-5"><?= nl2br(htmlspecialchars($value['risque'])) ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="card border-success border-start-4 border-0 bg-light">
                                                    <div class="card-body">
                                                        <h6 class="text-success mb-2"><i class="bx bx-check-shield me-2"></i>MITIGATION / SOLUTION</h6>
                                                        <p class="mb-0"><?= nl2br(htmlspecialchars($value['mitigation'])) ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="row g-2 text-muted small">
                                                    <div class="col-md-4">
                                                        <i class="bx bx-sort me-1"></i>Ordre: <strong><?= $value['ordre'] ?? 0 ?></strong>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <i class="bx bx-page me-1"></i>Page: <strong><?= htmlspecialchars($page_titre) ?></strong>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <i class="bx bx-id-card me-1"></i>ID: <strong>#<?= $value['id_risque'] ?></strong>
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
                        <div class="modal fade" id="update_<?= $value['id_risque'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le Risque</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Risques_mitigations/Update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_risque'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-error-circle me-2"></i>Description du Risque</h6>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Risque <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" name="risque" rows="3" required><?= htmlspecialchars($value['risque']) ?></textarea>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Mitigation / Solution <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" name="mitigation" rows="3" required><?= htmlspecialchars($value['mitigation']) ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-cog me-2"></i>Classification</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Catégorie</label>
                                                            <select class="form-select" name="categorie">
                                                                <option value="">Sélectionner...</option>
                                                                <?php foreach ($categories_list as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= ($value['categorie'] ?? '') == $key ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Niveau de risque <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="niveau_risque" required>
                                                                <?php foreach ($niveau_labels as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= $value['niveau_risque'] == $key ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-list-ol me-2"></i>Organisation</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Ordre d'affichage</label>
                                                            <input type="number" class="form-control" name="ordre" value="<?= $value['ordre'] ?? 0 ?>" min="0">
                                                            <small class="text-muted">0 = premier, ordre croissant</small>
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
                        <div class="modal fade" id="delete_<?= $value['id_risque'] ?>" tabindex="-1">
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
                                            Vous allez supprimer ce risque:<br>
                                            <strong><?= htmlspecialchars(substr($value['risque'], 0, 50)) ?>...</strong>
                                        </p>
                                        <p class="text-danger small mt-2">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Risques_mitigations/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_risque'] ?>">
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

                        <!-- MODAL CHANGE NIVEAU -->
                        <div class="modal fade" id="niveau_<?= $value['id_risque'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title"><i class="bx bx-refresh me-2"></i>Changer le niveau de risque</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Niveau actuel: <span class="badge <?= $niveau_badge ?>"><?= $niveau_label ?></span></p>
                                        <p class="text-muted">Cliquez sur le bouton ci-dessous pour passer au niveau suivant (Faible → Moyen → Élevé → Faible).</p>
                                    </div>
                                    <form action="<?= base_url('Risques_mitigations/ChangeNiveau') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_risque'] ?>">
                                        <input type="hidden" name="niveau_risque" value="<?= $value['niveau_risque'] ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-info text-white">
                                                <i class="bx bx-refresh me-1"></i>Changer le niveau
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bx bx-shield-alt-2" style="font-size: 3rem;"></i>
                                    <p class="mt-2 mb-0">Aucun risque enregistré</p>
                                    <small>Cliquez sur "Nouveau Risque" pour ajouter</small>
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
</div>

<!-- MODAL CREATE -->
<div class="modal fade" id="create_risque" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouveau Risque & Mitigation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Risques_mitigations/Create') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-error-circle me-2"></i>Description du Risque</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Risque <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="risque" rows="3" placeholder="Décrivez le risque..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Mitigation / Solution <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="mitigation" rows="3" placeholder="Décrivez la mitigation ou solution..." required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-cog me-2"></i>Classification</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Catégorie</label>
                                    <select class="form-select" name="categorie">
                                        <option value="">Sélectionner...</option>
                                        <?php foreach ($categories_list as $key => $label): ?>
                                            <option value="<?= $key ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Niveau de risque <span class="text-danger">*</span></label>
                                    <select class="form-select" name="niveau_risque" required>
                                        <option value="faible" class="text-success">Faible</option>
                                        <option value="moyen" class="text-warning" selected>Moyen</option>
                                        <option value="eleve" class="text-danger">Élevé</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-list-ol me-2"></i>Organisation</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Ordre d'affichage</label>
                                    <input type="number" class="form-control" name="ordre" value="0" min="0">
                                    <small class="text-muted">0 = premier, ordre croissant</small>
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
                    <button type="submit" class="btn btn-success"><i class="bx bx-save me-1"></i>Créer le risque</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#risquesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'asc']], // Sort by ordre
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