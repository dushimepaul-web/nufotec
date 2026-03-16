<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
// Helper pour les statuts
$statut_badges = [
    'en_developpement' => '<span class="badge bg-info"><i class="bx bx-code-alt"></i> Développement</span>',
    'commercialise' => '<span class="badge bg-success"><i class="bx bx-check-circle"></i> Commercialisé</span>',
    'rupture' => '<span class="badge bg-warning text-dark"><i class="bx bx-pause-circle"></i> Rupture</span>',
    'abandonne' => '<span class="badge bg-danger"><i class="bx bx-x-circle"></i> Abandonné</span>'
];

$statut_labels = [
    'en_developpement' => 'En développement',
    'commercialise' => 'Commercialisé',
    'rupture' => 'Rupture de stock',
    'abandonne' => 'Abandonné'
];
?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">E-Commerce</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Produits</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_produit">
                <i class="bx bx-plus"></i> Nouveau Produit
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
        $total_commercialise = 0;
        $total_vedette = 0;
        foreach ($produits as $p) {
            if ($p['statut'] == 'commercialise') $total_commercialise++;
            if ($p['est_vedette']) $total_vedette++;
        }
        ?>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Total Produits</h6>
                        <h3 class="mb-0"><?= count($produits) ?></h3>
                    </div>
                    <i class="bx bx-package fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Commercialisés</h6>
                        <h3 class="mb-0"><?= $total_commercialise ?></h3>
                    </div>
                    <i class="bx bx-check-circle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Produits Vedettes</h6>
                        <h3 class="mb-0"><?= $total_vedette ?></h3>
                    </div>
                    <i class="bx bx-star fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-package me-2"></i>Liste des Produits</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="produitsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="8%">Image</th>
                            <th width="20%">Produit</th>
                            <th width="12%">Catégorie</th>
                            <th width="10%">Prix</th>
                            <th width="10%">Statut</th>
                            <th width="8%">Vedette</th>
                            <th width="8%">Ordre</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($produits)): $i = 1; foreach ($produits as $value): 
                        $image_path = !empty($value['image_principale']) ? 'attachments/Produits/'.$value['image_principale'] : 'attachments/Produits/default-product.png';
                        $statut_badge = $statut_badges[$value['statut'] ?? 'commercialise'] ?? '<span class="badge bg-light text-dark">Inconnu</span>';
                        
                        // Récupérer le nom de la catégorie
                        $categorie_nom = 'Non classé';
                        foreach ($categories as $cat) {
                            if ($cat['id_categorie'] == $value['id_categorie']) {
                                $categorie_nom = $cat['nom_categorie'];
                                break;
                            }
                        }
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <img src="<?= base_url($image_path) ?>" 
                                     class="rounded border"
                                     style="width:60px; height:60px; object-fit:cover;"
                                     onerror="this.src='<?= base_url('attachments/Produits/default-product.png') ?>'"
                                     alt="<?= htmlspecialchars($value['nom_produit']) ?>">
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['nom_produit']) ?></strong>
                                    <small class="text-muted text-truncate" style="max-width: 200px;"><?= htmlspecialchars($value['description_courte'] ?? '') ?></small>
                                    <small class="text-primary font-monospace">/<?= $value['slug'] ?></small>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bx bx-category me-1"></i><?= htmlspecialchars($categorie_nom) ?>
                                </span>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <?php if (!empty($value['prix_public'])): ?>
                                        <span class="fw-bold text-success"><?= number_format($value['prix_public'], 2, ',', ' ') ?> $</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                    <?php if (!empty($value['prix_grossiste'])): ?>
                                        <small class="text-muted">Gros: <?= number_format($value['prix_grossiste'], 2, ',', ' ') ?> $</small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_produit'] ?>" class="text-decoration-none">
                                    <?= $statut_badge ?>
                                </a>
                            </td>

                            <td class="text-center">
                                <?php if (!empty($value['est_vedette']) && $value['est_vedette'] == 1): ?>
                                    <span class="text-warning fs-4"><i class='bx bxs-star'></i></span>
                                <?php else: ?>
                                    <span class="text-muted fs-4"><i class='bx bx-star'></i></span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-secondary"><?= $value['ordre_affichage'] ?? 0 ?></span>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#view_<?= $value['id_produit'] ?>">
                                                <i class="bx bx-show text-info me-2"></i>Voir détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id_produit'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id_produit'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL VIEW DETAILS -->
                        <div class="modal fade" id="view_<?= $value['id_produit'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-package me-2"></i>Détails du Produit</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center border-end">
                                                <img src="<?= base_url($image_path) ?>" 
                                                     class="rounded border mb-3"
                                                     style="width:100%; max-height:200px; object-fit:cover;"
                                                     onerror="this.src='<?= base_url('attachments/Produits/default-product.png') ?>'"
                                                     alt="<?= htmlspecialchars($value['nom_produit']) ?>">
                                                
                                                <h5 class="mb-1"><?= htmlspecialchars($value['nom_produit']) ?></h5>
                                                <p class="text-muted small mb-2"><?= htmlspecialchars($categorie_nom) ?></p>
                                                <?= $statut_badge ?>
                                                
                                                <?php if (!empty($value['est_vedette']) && $value['est_vedette'] == 1): ?>
                                                    <div class="mt-2">
                                                        <span class="badge bg-warning text-dark"><i class="bx bx-star"></i> Produit Vedette</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label class="text-muted small">Description courte</label>
                                                    <p class="mb-0"><?= nl2br(htmlspecialchars($value['description_courte'] ?? 'Non renseignée')) ?></p>
                                                </div>
                                                
                                                <div class="row g-3">
                                                    <div class="col-6">
                                                        <label class="text-muted small">Prix public</label>
                                                        <p class="mb-0 fw-bold text-success fs-5">
                                                            <?= !empty($value['prix_public']) ? number_format($value['prix_public'], 2, ',', ' ') . ' $' : 'Non défini' ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Prix grossiste</label>
                                                        <p class="mb-0 fw-bold text-primary fs-5">
                                                            <?= !empty($value['prix_grossiste']) ? number_format($value['prix_grossiste'], 2, ',', ' ') . ' $' : 'Non défini' ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Présentation</label>
                                                        <p class="mb-0"><?= htmlspecialchars($value['presentation'] ?? '-') ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Conditionnement</label>
                                                        <p class="mb-0"><?= htmlspecialchars($value['conditionnement'] ?? '-') ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Date de lancement</label>
                                                        <p class="mb-0"><?= !empty($value['date_lancement']) ? date('d/m/Y', strtotime($value['date_lancement'])) : '-' ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="text-muted small">Ordre d'affichage</label>
                                                        <p class="mb-0"><?= $value['ordre_affichage'] ?? 0 ?></p>
                                                    </div>
                                                    
                                                    <?php if (!empty($value['fiche_technique_url'])): ?>
                                                    <div class="col-12">
                                                        <a href="<?= base_url('attachments/Fiches/' . $value['fiche_technique_url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                            <i class="bx bx-file me-2"></i>Voir la fiche technique
                                                        </a>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if (!empty($value['composition'])): ?>
                                                <div class="mt-3">
                                                    <label class="text-muted small">Composition</label>
                                                    <div class="p-2 bg-light rounded">
                                                        <p class="mb-0 small"><?= nl2br(htmlspecialchars($value['composition'])) ?></p>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
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
                        <div class="modal fade" id="update_<?= $value['id_produit'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le Produit</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Produits/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_produit" value="<?= $value['id_produit'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Nom du produit <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="nom_produit" value="<?= htmlspecialchars($value['nom_produit']) ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="slug" value="<?= $value['slug'] ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="id_categorie" required>
                                                        <?php foreach ($categories as $cat): ?>
                                                            <option value="<?= $cat['id_categorie'] ?>" <?= $value['id_categorie'] == $cat['id_categorie'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nom_categorie']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Statut</label>
                                                    <select class="form-select" name="statut">
                                                        <?php foreach ($statut_labels as $key => $label): ?>
                                                            <option value="<?= $key ?>" <?= $value['statut'] == $key ? 'selected' : '' ?>><?= $label ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Prix public ($)</label>
                                                    <input type="number" step="0.01" class="form-control" name="prix_public" value="<?= $value['prix_public'] ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Prix grossiste ($)</label>
                                                    <input type="number" step="0.01" class="form-control" name="prix_grossiste" value="<?= $value['prix_grossiste'] ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Présentation</label>
                                                    <input type="text" class="form-control" name="presentation" value="<?= htmlspecialchars($value['presentation'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Conditionnement</label>
                                                    <input type="text" class="form-control" name="conditionnement" value="<?= htmlspecialchars($value['conditionnement'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Date de lancement</label>
                                                    <input type="date" class="form-control" name="date_lancement" value="<?= $value['date_lancement'] ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Ordre d'affichage</label>
                                                    <input type="number" class="form-control" name="ordre_affichage" value="<?= $value['ordre_affichage'] ?? 0 ?>">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Description courte</label>
                                                    <textarea class="form-control" name="description_courte" rows="2"><?= htmlspecialchars($value['description_courte'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Description longue</label>
                                                    <textarea class="form-control" name="description_longue" rows="4"><?= htmlspecialchars($value['description_longue'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Composition</label>
                                                    <textarea class="form-control" name="composition" rows="3"><?= htmlspecialchars($value['composition'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Indications</label>
                                                    <textarea class="form-control" name="indications" rows="3"><?= htmlspecialchars($value['indications'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Contre-indications</label>
                                                    <textarea class="form-control" name="contre_indications" rows="3"><?= htmlspecialchars($value['contre_indications'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Mode d'emploi</label>
                                                    <textarea class="form-control" name="mode_emploi" rows="3"><?= htmlspecialchars($value['mode_emploi'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Image principale</label>
                                                    <input type="file" class="form-control" name="image_principale" accept="image/*">
                                                    <?php if (!empty($value['image_principale'])): ?>
                                                        <small class="text-muted">Actuelle: <?= $value['image_principale'] ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Fiche technique</label>
                                                    <input type="file" class="form-control" name="fiche_technique_url" accept=".pdf,.doc,.docx">
                                                    <?php if (!empty($value['fiche_technique_url'])): ?>
                                                        <small class="text-muted">Actuelle: <?= $value['fiche_technique_url'] ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" name="est_vedette" id="est_vedette_<?= $value['id_produit'] ?>" value="1" <?= (!empty($value['est_vedette']) && $value['est_vedette'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="est_vedette_<?= $value['id_produit'] ?>">Produit vedette (mis en avant)</label>
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
                        <div class="modal fade" id="delete_<?= $value['id_produit'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer <strong><?= htmlspecialchars($value['nom_produit']) ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Produits/Delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_produit'] ?>">
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
                        <div class="modal fade" id="status_<?= $value['id_produit'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= $value['statut'] == 'commercialise' ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= $value['statut'] == 'commercialise' ? '<i class="bx bx-pause-circle me-2"></i>Mettre en rupture' : '<i class="bx bx-check-circle me-2"></i>Commercialiser' ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment changer le statut de <strong><?= htmlspecialchars($value['nom_produit']) ?></strong> ?</p>
                                        <p>Statut actuel: <?= $statut_labels[$value['statut']] ?? $value['statut'] ?></p>
                                    </div>
                                    <form action="<?= base_url('Produits/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_produit'] ?>">
                                        <input type="hidden" name="statut" value="<?= $value['statut'] ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= $value['statut'] == 'commercialise' ? 'btn-warning' : 'btn-success' ?>">
                                                <?= $value['statut'] == 'commercialise' ? 'Mettre en rupture' : 'Commercialiser' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bx bx-package text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun produit trouvé</p>
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
<div class="modal fade" id="create_produit" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Nouveau Produit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Produits/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nom du produit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nom_produit" required id="create_nom_produit" onblur="generateSlug()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="slug" required id="create_slug">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_categorie" required>
                                <option value="">Sélectionner...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id_categorie'] ?>"><?= htmlspecialchars($cat['nom_categorie']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Statut</label>
                            <select class="form-select" name="statut">
                                <?php foreach ($statut_labels as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $key == 'commercialise' ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Prix public ($)</label>
                            <input type="number" step="0.01" class="form-control" name="prix_public">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Prix grossiste ($)</label>
                            <input type="number" step="0.01" class="form-control" name="prix_grossiste">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Présentation</label>
                            <input type="text" class="form-control" name="presentation" placeholder="Ex: Flacon de 100ml">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Conditionnement</label>
                            <input type="text" class="form-control" name="conditionnement" placeholder="Ex: Carton de 12 unités">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de lancement</label>
                            <input type="date" class="form-control" name="date_lancement">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ordre d'affichage</label>
                            <input type="number" class="form-control" name="ordre_affichage" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description courte</label>
                            <textarea class="form-control" name="description_courte" rows="2" placeholder="Description rapide du produit..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description longue</label>
                            <textarea class="form-control" name="description_longue" rows="4" placeholder="Description détaillée..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Composition</label>
                            <textarea class="form-control" name="composition" rows="3" placeholder="Ingrédients et composition..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Indications</label>
                            <textarea class="form-control" name="indications" rows="3" placeholder="Pour quels cas d'usage..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Contre-indications</label>
                            <textarea class="form-control" name="contre_indications" rows="3" placeholder="Précautions d'emploi..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mode d'emploi</label>
                            <textarea class="form-control" name="mode_emploi" rows="3" placeholder="Instructions d'utilisation..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Image principale</label>
                            <input type="file" class="form-control" name="image_principale" accept="image/*">
                            <small class="text-muted">Formats: JPG, PNG, WEBP (max 2MB)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fiche technique</label>
                            <input type="file" class="form-control" name="fiche_technique_url" accept=".pdf,.doc,.docx">
                            <small class="text-muted">Formats: PDF, DOC, DOCX</small>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="est_vedette" id="create_est_vedette" value="1">
                                <label class="form-check-label" for="create_est_vedette">Produit vedette (mis en avant sur la page d'accueil)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer le produit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#produitsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'asc']],
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

// Génération automatique du slug
function generateSlug() {
    const nom = document.getElementById('create_nom_produit').value;
    const slug = nom.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('create_slug').value = slug;
}
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>