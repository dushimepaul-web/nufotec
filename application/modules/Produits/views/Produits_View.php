<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Catalogue</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion des Produits</li>
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

    <?php
    // Calcul des statistiques par catégorie
    $stats_by_category = [];
    $total_produits = count($produits);
    $total_actifs = 0;
    $total_vedettes = 0;
    $total_prix = 0;
    $produits_avec_prix = 0;
    
    foreach ($categories as $cat) {
        $stats_by_category[$cat['id_categorie']] = [
            'nom' => $cat['nom_categorie'],
            'code' => $cat['code_categorie'],
            'icone' => $cat['icone'] ?? 'tag',
            'total' => 0,
            'actifs' => 0,
            'inactifs' => 0,
            'vedettes' => 0,
            'commercialises' => 0,
            'en_developpement' => 0,
            'rupture' => 0,
            'abandonnes' => 0,
            'prix_moyen' => 0,
            'prix_total' => 0,
            'produits_avec_prix' => 0
        ];
    }
    
    foreach ($produits as $prod) {
        $cat_id = $prod['id_categorie'];
        if (isset($stats_by_category[$cat_id])) {
            $stats_by_category[$cat_id]['total']++;
            
            if (!empty($prod['est_actif']) && $prod['est_actif'] == 1) {
                $stats_by_category[$cat_id]['actifs']++;
                $total_actifs++;
            } else {
                $stats_by_category[$cat_id]['inactifs']++;
            }
            
            if (!empty($prod['est_vedette']) && $prod['est_vedette'] == 1) {
                $stats_by_category[$cat_id]['vedettes']++;
                $total_vedettes++;
            }
            
            // Statuts commerciaux
            switch ($prod['statut']) {
                case 'commercialise':
                    $stats_by_category[$cat_id]['commercialises']++;
                    break;
                case 'en_developpement':
                    $stats_by_category[$cat_id]['en_developpement']++;
                    break;
                case 'rupture':
                    $stats_by_category[$cat_id]['rupture']++;
                    break;
                case 'abandonne':
                    $stats_by_category[$cat_id]['abandones']++;
                    break;
            }
            
            // Prix
            if (!empty($prod['prix_public']) && $prod['prix_public'] > 0) {
                $stats_by_category[$cat_id]['prix_total'] += $prod['prix_public'];
                $stats_by_category[$cat_id]['produits_avec_prix']++;
                $total_prix += $prod['prix_public'];
                $produits_avec_prix++;
            }
        }
    }
    
    // Calcul des moyennes
    foreach ($stats_by_category as &$cat_stats) {
        if ($cat_stats['produits_avec_prix'] > 0) {
            $cat_stats['prix_moyen'] = $cat_stats['prix_total'] / $cat_stats['produits_avec_prix'];
        }
    }
    unset($cat_stats);
    
    $prix_moyen_global = $produits_avec_prix > 0 ? $total_prix / $produits_avec_prix : 0;
    ?>

    <!-- STATISTIQUES GÉNÉRALES -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-white-50">Total Produits</p>
                            <h3 class="mb-0"><?= $total_produits ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-package fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-white-50">Produits Actifs</p>
                            <h3 class="mb-0"><?= $total_actifs ?></h3>
                            <small><?= $total_produits > 0 ? round(($total_actifs/$total_produits)*100, 1) : 0 ?>% du total</small>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-check-circle fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-dark-50">Produits Vedettes</p>
                            <h3 class="mb-0"><?= $total_vedettes ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-star fs-1 text-dark-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-0 text-white-50">Prix Moyen</p>
                            <h3 class="mb-0"><?= number_format($prix_moyen_global, 2, ',', ' ') ?> €</h3>
                            <small><?= $produits_avec_prix ?> produits avec prix</small>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bx bx-euro fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRAPHIQUES ET STATS PAR CATÉGORIE -->
    <div class="row mb-4">
        <!-- Graphique répartition par catégorie -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="bx bx-pie-chart-alt me-2"></i>Répartition par Catégorie</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartCategories" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Graphique statuts commerciaux -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="bx bx-bar-chart-alt-2 me-2"></i>Statuts Commerciaux</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartStatuts" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLEAU DÉTAILLÉ PAR CATÉGORIE -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-primary"><i class="bx bx-table me-2"></i>Statistiques Détaillées par Catégorie</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="statsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Catégorie</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Actifs</th>
                            <th class="text-center">Inactifs</th>
                            <th class="text-center">Vedettes</th>
                            <th class="text-center">Commercialisés</th>
                            <th class="text-center">En Dév.</th>
                            <th class="text-center">Rupture</th>
                            <th class="text-end">Prix Moyen</th>
                            <th class="text-center">% du Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats_by_category as $cat_id => $stats): 
                            $pourcentage = $total_produits > 0 ? round(($stats['total'] / $total_produits) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded">
                                            <i class="bi bi-<?= htmlspecialchars($stats['icone']) ?> text-primary fs-4"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong class="d-block">[<?= $stats['code'] ?>] <?= htmlspecialchars($stats['nom']) ?></strong>
                                        <small class="text-muted">Code: <?= $stats['code'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary fs-6"><?= $stats['total'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success"><?= $stats['actifs'] ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($stats['inactifs'] > 0): ?>
                                    <span class="badge bg-danger"><?= $stats['inactifs'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($stats['vedettes'] > 0): ?>
                                    <span class="badge bg-warning text-dark"><i class="bx bx-star"></i> <?= $stats['vedettes'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($stats['commercialises'] > 0): ?>
                                    <span class="badge bg-success bg-opacity-75"><?= $stats['commercialises'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($stats['en_developpement'] > 0): ?>
                                    <span class="badge bg-warning"><?= $stats['en_developpement'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($stats['rupture'] > 0): ?>
                                    <span class="badge bg-danger bg-opacity-75"><?= $stats['rupture'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($stats['prix_moyen'] > 0): ?>
                                    <strong><?= number_format($stats['prix_moyen'], 2, ',', ' ') ?> €</strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                             style="width: <?= $pourcentage ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?= $pourcentage ?>%</small>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Filtres rapides -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <select class="form-select" id="filterCategorie">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id_categorie'] ?>"><?= htmlspecialchars($cat['nom_categorie']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterStatut">
                        <option value="">Tous les statuts</option>
                        <option value="commercialise">Commercialisé</option>
                        <option value="en_developpement">En développement</option>
                        <option value="rupture">Rupture</option>
                        <option value="abandonne">Abandonné</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterVedette">
                        <option value="">Tous les produits</option>
                        <option value="1">Produits vedettes</option>
                        <option value="0">Produits standard</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="bx bx-reset me-2"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTE DES PRODUITS -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-primary"><i class="bx bx-package me-2"></i>Liste des Produits</h5>
                <span class="badge bg-light text-dark border"><?= count($produits) ?> produits</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="produitsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">#</th>
                            <th width="5%">Ordre</th>
                            <th width="8%">Image</th>
                            <th width="15%">Nom</th>
                            <th width="12%">Catégorie</th>
                            <th width="10%">Prix</th>
                            <th width="8%">Statut</th>
                            <th width="7%">Vedette</th>
                            <th width="7%">Actif</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($produits)): $i = 1; foreach ($produits as $value): 
                        // Récupérer la catégorie
                        $categorie_nom = 'Non classé';
                        $categorie_code = '';
                        foreach ($categories as $cat) {
                            if ($cat['id_categorie'] == $value['id_categorie']) {
                                $categorie_nom = $cat['nom_categorie'];
                                $categorie_code = $cat['code_categorie'];
                                break;
                            }
                        }
                        
                        // Image path
                        $image_path = !empty($value['image_principale']) ? 'attachments/Produits/'.$value['image_principale'] : 'assets/images/product-placeholder.png';
                        
                        // Badges statut
                        $statut_badges = [
                            'commercialise' => '<span class="badge bg-success">Commercialisé</span>',
                            'en_developpement' => '<span class="badge bg-warning text-dark">En développement</span>',
                            'rupture' => '<span class="badge bg-danger">Rupture</span>',
                            'abandonne' => '<span class="badge bg-secondary">Abandonné</span>'
                        ];
                        $statut_badge = $statut_badges[$value['statut']] ?? '<span class="badge bg-light text-dark">Inconnu</span>';
                        
                        // Prix formaté
                        $prix_public = !empty($value['prix_public']) ? number_format($value['prix_public'], 2, ',', ' ') . ' €' : '-';
                    ?>
                        <tr data-categorie="<?= $value['id_categorie'] ?>" data-statut="<?= $value['statut'] ?>" data-vedette="<?= $value['est_vedette'] ?>">
                            <td><?= $i++ ?></td>
                            
                            <td>
                                <span class="badge bg-light text-dark border"><?= $value['ordre_affichage'] ?></span>
                            </td>

                            <td>
                                <img src="<?= base_url($image_path) ?>" 
                                     class="rounded border"
                                     style="width:60px; height:60px; object-fit:cover;"
                                     onerror="this.src='<?= base_url('assets/images/product-placeholder.png') ?>'"
                                     alt="<?= htmlspecialchars($value['nom_produit']) ?>">
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['nom_produit']) ?></strong>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($value['slug']) ?></small>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-primary me-1"><?= $categorie_code ?></span>
                                <small><?= htmlspecialchars($categorie_nom) ?></small>
                            </td>

                            <td>
                                <strong class="text-success"><?= $prix_public ?></strong>
                                <?php if (!empty($value['prix_grossiste'])): ?>
                                    <br><small class="text-muted">Grossiste: <?= number_format($value['prix_grossiste'], 2, ',', ' ') ?> €</small>
                                <?php endif; ?>
                            </td>

                            <td><?= $statut_badge ?></td>

                            <td class="text-center">
                                <?php if (!empty($value['est_vedette']) && $value['est_vedette'] == 1): ?>
                                    <span class="badge bg-warning text-dark"><i class="bx bx-star"></i> Vedette</span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id_produit'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['est_actif']) && $value['est_actif'] == 1): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactif</span>
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
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title"><i class="bx bx-package me-2"></i>Détails du Produit</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 text-center border-end">
                                                <img src="<?= base_url($image_path) ?>" 
                                                     class="rounded border border-3 border-primary mb-3"
                                                     style="width:200px; height:200px; object-fit:cover;"
                                                     onerror="this.src='<?= base_url('assets/images/product-placeholder.png') ?>'"
                                                     alt="<?= htmlspecialchars($value['nom_produit']) ?>">
                                                <h4 class="mb-1"><?= htmlspecialchars($value['nom_produit']) ?></h4>
                                                <p class="text-muted font-monospace small"><?= htmlspecialchars($value['slug']) ?></p>
                                                
                                                <div class="d-flex justify-content-center gap-2 mb-3">
                                                    <?= $statut_badge ?>
                                                    <?php if (!empty($value['est_vedette']) && $value['est_vedette'] == 1): ?>
                                                        <span class="badge bg-warning text-dark"><i class="bx bx-star"></i> Vedette</span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="alert alert-light border">
                                                    <h6 class="mb-2">Prix</h6>
                                                    <p class="mb-1"><strong>Public:</strong> <?= $prix_public ?></p>
                                                    <?php if (!empty($value['prix_grossiste'])): ?>
                                                        <p class="mb-0"><strong>Grossiste:</strong> <?= number_format($value['prix_grossiste'], 2, ',', ' ') ?> €</p>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if (!empty($value['fiche_technique_url'])): ?>
                                                    <a href="<?= base_url('attachments/Fiches/'.$value['fiche_technique_url']) ?>" 
                                                       class="btn btn-outline-primary btn-sm w-100" target="_blank">
                                                        <i class="bx bx-file me-2"></i>Fiche technique
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <h6 class="text-primary border-bottom pb-2">Description Courte</h6>
                                                        <p><?= nl2br(htmlspecialchars($value['description_courte'])) ?></p>
                                                    </div>
                                                    
                                                    <?php if (!empty($value['description_longue'])): ?>
                                                    <div class="col-12">
                                                        <h6 class="text-primary border-bottom pb-2">Description Longue</h6>
                                                        <p><?= nl2br(htmlspecialchars($value['description_longue'])) ?></p>
                                                    </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($value['composition'])): ?>
                                                    <div class="col-md-6">
                                                        <h6 class="text-primary border-bottom pb-2">Composition</h6>
                                                        <p><?= nl2br(htmlspecialchars($value['composition'])) ?></p>
                                                    </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($value['indications'])): ?>
                                                    <div class="col-md-6">
                                                        <h6 class="text-primary border-bottom pb-2">Indications</h6>
                                                        <p><?= nl2br(htmlspecialchars($value['indications'])) ?></p>
                                                    </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($value['mode_emploi'])): ?>
                                                    <div class="col-md-6">
                                                        <h6 class="text-primary border-bottom pb-2">Mode d'emploi</h6>
                                                        <p><?= nl2br(htmlspecialchars($value['mode_emploi'])) ?></p>
                                                    </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($value['contre_indications'])): ?>
                                                    <div class="col-md-6">
                                                        <h6 class="text-primary border-bottom pb-2">Contre-indications</h6>
                                                        <p><?= nl2br(htmlspecialchars($value['contre_indications'])) ?></p>
                                                    </div>
                                                    <?php endif; ?>

                                                    <div class="col-md-6">
                                                        <h6 class="text-primary border-bottom pb-2">Présentation</h6>
                                                        <p><?= htmlspecialchars($value['presentation'] ?: 'Non spécifié') ?></p>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <h6 class="text-primary border-bottom pb-2">Conditionnement</h6>
                                                        <p><?= htmlspecialchars($value['conditionnement'] ?: 'Non spécifié') ?></p>
                                                    </div>

                                                    <?php if (!empty($value['certifications'])): 
                                                        $certs = json_decode($value['certifications'], true);
                                                        if ($certs): ?>
                                                    <div class="col-12">
                                                        <h6 class="text-primary border-bottom pb-2">Certifications</h6>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <?php foreach ($certs as $cert): ?>
                                                                <span class="badge bg-info"><?= htmlspecialchars($cert) ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <?php endif; endif; ?>

                                                    <?php if (!empty($value['date_lancement'])): ?>
                                                    <div class="col-md-6">
                                                        <h6 class="text-primary border-bottom pb-2">Date de lancement</h6>
                                                        <p><?= date('d/m/Y', strtotime($value['date_lancement'])) ?></p>
                                                    </div>
                                                    <?php endif; ?>

                                                    <div class="col-md-6">
                                                        <h6 class="text-primary border-bottom pb-2">Ordre d'affichage</h6>
                                                        <p><?= $value['ordre_affichage'] ?></p>
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
                        <div class="modal fade" id="update_<?= $value['id_produit'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le Produit</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Produits/Update') ?>" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_produit" value="<?= $value['id_produit'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <!-- Informations de base -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations de base</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Nom du produit <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="nom_produit" 
                                                                   value="<?= htmlspecialchars($value['nom_produit']) ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="id_categorie" required>
                                                                <?php foreach ($categories as $cat): ?>
                                                                    <option value="<?= $cat['id_categorie'] ?>" <?= ($value['id_categorie'] == $cat['id_categorie']) ? 'selected' : '' ?>>
                                                                        [<?= $cat['code_categorie'] ?>] <?= htmlspecialchars($cat['nom_categorie']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Description courte <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" name="description_courte" rows="2" required><?= htmlspecialchars($value['description_courte']) ?></textarea>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold">Description longue</label>
                                                            <textarea class="form-control" name="description_longue" rows="4"><?= htmlspecialchars($value['description_longue'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Caractéristiques techniques -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-cog me-2"></i>Caractéristiques techniques</h6>
                                                    <div class="row g-3">
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
                                                            <label class="form-label fw-bold">Présentation</label>
                                                            <input type="text" class="form-control" name="presentation" 
                                                                   value="<?= htmlspecialchars($value['presentation'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Conditionnement</label>
                                                            <input type="text" class="form-control" name="conditionnement" 
                                                                   value="<?= htmlspecialchars($value['conditionnement'] ?? '') ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Prix et commercialisation -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-euro me-2"></i>Prix et commercialisation</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Prix public (€)</label>
                                                            <input type="number" step="0.01" class="form-control" name="prix_public" 
                                                                   value="<?= $value['prix_public'] ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Prix grossiste (€)</label>
                                                            <input type="number" step="0.01" class="form-control" name="prix_grossiste" 
                                                                   value="<?= $value['prix_grossiste'] ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Statut</label>
                                                            <select class="form-select" name="statut">
                                                                <option value="commercialise" <?= ($value['statut'] == 'commercialise') ? 'selected' : '' ?>>Commercialisé</option>
                                                                <option value="en_developpement" <?= ($value['statut'] == 'en_developpement') ? 'selected' : '' ?>>En développement</option>
                                                                <option value="rupture" <?= ($value['statut'] == 'rupture') ? 'selected' : '' ?>>Rupture</option>
                                                                <option value="abandonne" <?= ($value['statut'] == 'abandonne') ? 'selected' : '' ?>>Abandonné</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Date de lancement</label>
                                                            <input type="date" class="form-control" name="date_lancement" 
                                                                   value="<?= $value['date_lancement'] ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Ordre d'affichage</label>
                                                            <input type="number" class="form-control" name="ordre_affichage" 
                                                                   value="<?= $value['ordre_affichage'] ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Certifications (séparées par virgule)</label>
                                                            <input type="text" class="form-control" name="certifications" 
                                                                   value="<?= !empty($value['certifications']) ? htmlspecialchars(implode(', ', json_decode($value['certifications'], true) ?: [])) : '' ?>"
                                                                   placeholder="ISO, GMP, Bio...">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Médias -->
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Médias</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Image principale</label>
                                                            <input type="file" class="form-control" name="image_principale" accept="image/*">
                                                            <?php if (!empty($value['image_principale'])): ?>
                                                                <small class="text-muted">Actuelle: <?= $value['image_principale'] ?></small>
                                                                <img src="<?= base_url($image_path) ?>" class="d-block mt-2 rounded" style="width:80px; height:80px; object-fit:cover;">
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Fiche technique (PDF/DOC)</label>
                                                            <input type="file" class="form-control" name="fiche_technique" accept=".pdf,.doc,.docx">
                                                            <?php if (!empty($value['fiche_technique_url'])): ?>
                                                                <small class="text-muted">Actuelle: <?= $value['fiche_technique_url'] ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Options -->
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary mb-3"><i class="bx bx-toggle-left me-2"></i>Options</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input" name="est_vedette" id="est_vedette_<?= $value['id_produit'] ?>" value="1" <?= (!empty($value['est_vedette']) && $value['est_vedette'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="est_vedette_<?= $value['id_produit'] ?>">Produit vedette (mis en avant)</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input" name="est_actif" id="est_actif_<?= $value['id_produit'] ?>" value="1" <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="est_actif_<?= $value['id_produit'] ?>">Produit actif (visible)</label>
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
                                    <div class="modal-header <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? '<i class="bx bx-block me-2"></i>Désactiver' : '<i class="bx bx-check-circle me-2"></i>Activer' ?> le produit
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['est_actif']) && $value['est_actif'] == 1) ? 'désactiver' : 'activer' ?></strong> le produit <strong><?= htmlspecialchars($value['nom_produit']) ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('Produits/ChangeStatus') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id_produit'] ?>">
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

<!-- MODAL CREATE PRODUIT -->
<div class="modal fade" id="create_produit" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouveau Produit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Produits/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <!-- Informations de base -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations de base</h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Nom du produit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nom_produit" required 
                                           placeholder="Ex: Carica papaya Extract">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                                    <select class="form-select" name="id_categorie" required>
                                        <option value="">Sélectionner...</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id_categorie'] ?>">
                                                [<?= $cat['code_categorie'] ?>] <?= htmlspecialchars($cat['nom_categorie']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Description courte <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="description_courte" rows="2" required
                                              placeholder="Brève description pour les listes..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Description longue</label>
                                    <textarea class="form-control" name="description_longue" rows="4"
                                              placeholder="Description détaillée complète..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Caractéristiques techniques -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-cog me-2"></i>Caractéristiques techniques</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Composition</label>
                                    <textarea class="form-control" name="composition" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Indications</label>
                                    <textarea class="form-control" name="indications" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Contre-indications</label>
                                    <textarea class="form-control" name="contre_indications" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Mode d'emploi</label>
                                    <textarea class="form-control" name="mode_emploi" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Présentation</label>
                                    <input type="text" class="form-control" name="presentation" placeholder="Ex: Flacon de 60 capsules">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Conditionnement</label>
                                    <input type="text" class="form-control" name="conditionnement" placeholder="Ex: Boîte de 12 flacons">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prix et commercialisation -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-euro me-2"></i>Prix et commercialisation</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Prix public (€)</label>
                                    <input type="number" step="0.01" class="form-control" name="prix_public">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Prix grossiste (€)</label>
                                    <input type="number" step="0.01" class="form-control" name="prix_grossiste">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Statut</label>
                                    <select class="form-select" name="statut">
                                        <option value="commercialise" selected>Commercialisé</option>
                                        <option value="en_developpement">En développement</option>
                                        <option value="rupture">Rupture</option>
                                        <option value="abandonne">Abandonné</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Date de lancement</label>
                                    <input type="date" class="form-control" name="date_lancement">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Ordre d'affichage</label>
                                    <input type="number" class="form-control" name="ordre_affichage" value="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Certifications</label>
                                    <input type="text" class="form-control" name="certifications" 
                                           placeholder="ISO, GMP, Bio... (séparées par virgule)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Médias -->
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-image me-2"></i>Médias</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Image principale</label>
                                    <input type="file" class="form-control" name="image_principale" accept="image/*">
                                    <small class="text-muted">Formats: JPG, PNG, WEBP (max 2MB)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fiche technique (PDF/DOC)</label>
                                    <input type="file" class="form-control" name="fiche_technique" accept=".pdf,.doc,.docx">
                                    <small class="text-muted">Formats: PDF, DOC, DOCX (max 5MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3"><i class="bx bx-toggle-left me-2"></i>Options</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="est_vedette" id="create_est_vedette" value="1">
                                        <label class="form-check-label" for="create_est_vedette">Produit vedette (mis en avant sur la home)</label>
                                    </div>
                                </div>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    var table = $('#produitsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[1, 'asc']], // Tri par ordre d'affichage
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [2, 9] } // Image et Actions non triables
        ]
    });
    
    // Filtres personnalisés
    $('#filterCategorie').on('change', function() {
        table.column(4).search(this.value ? $(this).find('option:selected').text() : '').draw();
    });
    
    $('#filterStatut').on('change', function() {
        table.column(6).search(this.value).draw();
    });
    
    $('#filterVedette').on('change', function() {
        if (this.value === '') {
            table.column(7).search('').draw();
        } else if (this.value === '1') {
            table.column(7).search('Vedette').draw();
        } else {
            table.column(7).search('-').draw();
        }
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

function resetFilters() {
    $('#filterCategorie, #filterStatut, #filterVedette').val('');
    $('#produitsTable').DataTable().search('').columns().search('').draw();
}

// Auto-génération du slug
document.querySelector('input[name="nom_produit"]').addEventListener('blur', function() {
    const nom = this.value;
    if (nom && !document.querySelector('input[name="slug"]').value) {
        const slug = nom.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        document.querySelector('input[name="slug"]').value = slug;
    }
});

// Données pour les graphiques
const statsData = <?= json_encode(array_values($stats_by_category)) ?>;

// Graphique 1: Répartition par catégorie (Doughnut)
const ctxCategories = document.getElementById('chartCategories').getContext('2d');
new Chart(ctxCategories, {
    type: 'doughnut',
    data: {
        labels: statsData.map(s => `[${s.code}] ${s.nom.substring(0, 20)}...`),
        datasets: [{
            data: statsData.map(s => s.total),
            backgroundColor: [
                '#0f4c3a', '#1a6b52', '#d4af37', '#b8962e', 
                '#6c757d', '#dee2e6', '#212529', '#0a3326'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    boxWidth: 15,
                    font: { size: 11 }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} produits (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// Graphique 2: Statuts commerciaux par catégorie (Stacked Bar)
const ctxStatuts = document.getElementById('chartStatuts').getContext('2d');
new Chart(ctxStatuts, {
    type: 'bar',
    data: {
        labels: statsData.map(s => s.code),
        datasets: [
            {
                label: 'Commercialisés',
                data: statsData.map(s => s.commercialises),
                backgroundColor: '#28a745'
            },
            {
                label: 'En développement',
                data: statsData.map(s => s.en_developpement),
                backgroundColor: '#ffc107'
            },
            {
                label: 'Rupture',
                data: statsData.map(s => s.rupture),
                backgroundColor: '#dc3545'
            },
            {
                label: 'Abandonnés',
                data: statsData.map(s => s.abandones),
                backgroundColor: '#6c757d'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { stacked: true },
            y: { stacked: true, beginAtZero: true }
        },
        plugins: {
            legend: {
                position: 'top',
                labels: { boxWidth: 12, font: { size: 11 } }
            }
        }
    }
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>