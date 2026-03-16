<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Paniers</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('Paniers') ?>">Paniers</a></li>
                        <li class="breadcrumb-item active">Détail #<?= $panier['panier_id'] ?></li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="<?= base_url('Paniers') ?>" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back"></i> Retour
                </a>
                <a href="<?= base_url('Paniers/ConvertirCommande/' . $panier['panier_id']) ?>" class="btn btn-success">
                    <i class="bx bx-cart"></i> Convertir en commande
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Informations client -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-user me-2"></i>Client</h6>
                    </div>
                    <div class="card-body">
                        <h5><?= htmlspecialchars(($panier['prenom'] ?? '') . ' ' . ($panier['nom'] ?? '')) ?></h5>
                        <p class="mb-1"><i class="bx bx-envelope me-2"></i><?= $panier['email'] ?></p>
                        <p class="mb-1"><i class="bx bx-phone me-2"></i><?= $panier['telephone'] ?? 'Non renseigné' ?></p>
                        <hr>
                        <h6 class="text-muted">Historique client</h6>
                        <?php if (!empty($historique_client)): ?>
                            <ul class="list-unstyled mb-0">
                            <?php foreach ($historique_client as $cmd): ?>
                                <li>
                                    <small>
                                        <a href="<?= base_url('Commandes/Detail/' . $cmd['id']) ?>">
                                            <?= $cmd['numero_commande'] ?>
                                        </a> - 
                                        <?= date('d/m/Y', strtotime($cmd['created_at'])) ?> - 
                                        <span class="badge bg-<?= $cmd['statut'] == 'livree' ? 'success' : 'warning' ?>">
                                            <?= $cmd['statut'] ?>
                                        </span>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <small class="text-muted">Aucune commande précédente</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Contenu du panier -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-package me-2"></i>Contenu du panier</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Produit</th>
                                    <th class="text-center">Prix</th>
                                    <th class="text-center">Qté</th>
                                    <th class="text-end">Total</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lignes as $ligne): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($ligne['image_principale']): ?>
                                                <img src="<?= base_url('attachments/Produits/' . $ligne['image_principale']) ?>" 
                                                     class="rounded me-2" style="width:40px; height:40px; object-fit:cover;">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= htmlspecialchars($ligne['nom_produit']) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($ligne['prix_unitaire_ht'], 2, ',', ' ') ?> €
                                    </td>
                                    <td class="text-center"><?= $ligne['quantite'] ?></td>
                                    <td class="text-end fw-bold">
                                        <?= number_format($ligne['total_ligne_ttc'], 2, ',', ' ') ?> €
                                    </td>
                                    <td>
                                        <?php if ($ligne['stock'] >= $ligne['quantite']): ?>
                                            <span class="badge bg-success">Dispo (<?= $ligne['stock'] ?>)</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Insuffisant</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-group-divider">
                                <tr class="table-light">
                                    <td colspan="3" class="text-end fw-bold">Total HT:</td>
                                    <td class="text-end fw-bold"><?= number_format($panier['total_ht'], 2, ',', ' ') ?> €</td>
                                    <td></td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="3" class="text-end fw-bold">Total TTC:</td>
                                    <td class="text-end fw-bold fs-5 text-primary">
                                        <?= number_format($panier['total_ttc'], 2, ',', ' ') ?> €
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informations panier</h6>
                                <p class="mb-1"><small>Créé le: <?= date('d/m/Y H:i', strtotime($panier['created_at'])) ?></small></p>
                                <p class="mb-0"><small>Modifié le: <?= date('d/m/Y H:i', strtotime($panier['updated_at'])) ?></small></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <?php $jours = (time() - strtotime($panier['updated_at'])) / 86400; ?>
                                <?php if ($jours > 2): ?>
                                    <button class="btn btn-warning" onclick="relancerPanier(<?= $panier['panier_id'] ?>)">
                                        <i class="bx bx-envelope"></i> Relancer le client
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


<?php include VIEWPATH.'includes/backend/Footer.php'; ?>