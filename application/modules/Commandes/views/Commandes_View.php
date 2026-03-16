<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Ventes</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gestion des Commandes</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a class="btn btn-success me-2" href="<?= base_url('Commandes/Export?' . http_build_query($filters)) ?>" target="_blank">
                    <i class="bx bx-export"></i> Exporter CSV
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateCommande">
                    <i class="bx bx-plus"></i> Nouvelle Commande
                </button>
            </div>
        </div>

        <!-- Messages Flash -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bx bx-check-circle fs-5 me-2"></i><?= $this->session->flashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bx bx-error-circle fs-5 me-2"></i><?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-primary"><?= $stats['total'] ?></h3>
                        <small class="text-muted">Total commandes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-warning"><?= $stats['en_attente_traitement'] ?></h3>
                        <small class="text-muted">À traiter</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-info"><?= $stats['aujourd_hui'] ?></h3>
                        <small class="text-muted">Aujourd'hui</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-success"><?= $stats['par_statut']['livree'] ?></h3>
                        <small class="text-muted">Livrées</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-danger"><?= $stats['par_statut']['annulee'] ?></h3>
                        <small class="text-muted">Annulées</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm bg-secondary bg-opacity-10">
                    <div class="card-body text-center">
                        <h3 class="mb-0 text-secondary"><?= number_format($stats['ca_total'], 2, ',', ' ') ?> €</h3>
                        <small class="text-muted">CA Total</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bx bx-filter-alt me-2"></i>Filtres de recherche</h6>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="bx bx-chevron-down"></i> Afficher/Masquer
                </button>
            </div>
            <div class="collapse show" id="filtersCollapse">
                <div class="card-body">
                    <form method="GET" action="<?= base_url('Commandes') ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Recherche</label>
                                <input type="text" class="form-control" name="search" value="<?= $filters['search'] ?? '' ?>" placeholder="N° commande, client, email...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">N° Commande</label>
                                <input type="text" class="form-control" name="numero_commande" value="<?= $filters['numero_commande'] ?? '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Client</label>
                                <select class="form-select" name="user_id">
                                    <option value="">Tous</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($filters['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Statut</label>
                                <select class="form-select" name="statut">
                                    <option value="">Tous</option>
                                    <?php 
                                    $statuts = ['en_attente' => '⏳ En attente', 'confirmee' => '✓ Confirmée', 'preparation' => '📦 Préparation', 'expediee' => '🚚 Expédiée', 'livree' => '✅ Livrée', 'annulee' => '❌ Annulée', 'remboursee' => '↩️ Remboursée'];
                                    foreach ($statuts as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($filters['statut'] ?? '') == $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Paiement</label>
                                <select class="form-select" name="statut_paiement">
                                    <option value="">Tous</option>
                                    <?php 
                                    $paiements = ['en_attente' => 'En attente', 'paye' => 'Payé', 'echoue' => 'Échoué', 'rembourse' => 'Remboursé'];
                                    foreach ($paiements as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($filters['statut_paiement'] ?? '') == $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Mode paiement</label>
                                <select class="form-select" name="mode_paiement">
                                    <option value="">Tous</option>
                                    <?php 
                                    $modes = ['carte' => 'Carte bancaire', 'virement' => 'Virement', 'paypal' => 'PayPal', 'especes' => 'Espèces'];
                                    foreach ($modes as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($filters['mode_paiement'] ?? '') == $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Du</label>
                                <input type="date" class="form-control" name="date_debut" value="<?= $filters['date_debut'] ?? '' ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Au</label>
                                <input type="date" class="form-control" name="date_fin" value="<?= $filters['date_fin'] ?? '' ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Montant min (€)</label>
                                <input type="number" step="0.01" class="form-control" name="montant_min" value="<?= $filters['montant_min'] ?? '' ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Montant max (€)</label>
                                <input type="number" step="0.01" class="form-control" name="montant_max" value="<?= $filters['montant_max'] ?? '' ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search"></i></button>
                                <a href="<?= base_url('Commandes') ?>" class="btn btn-outline-secondary" title="Réinitialiser"><i class="bx bx-reset"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tableau des Commandes -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-cart me-2"></i>Liste des Commandes</h5>
                <span class="badge bg-secondary"><?= count($commandes) ?> commande(s)</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tableCommandes" class="table table-hover align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="12%">N° Commande</th>
                                <th width="15%">Client</th>
                                <th width="10%">Date</th>
                                <th width="20%">Produits</th>
                                <th width="12%">Total</th>
                                <th width="10%">Statut</th>
                                <th width="10%">Paiement</th>
                                <th width="6%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($commandes)): 
                                $i = 1;
                                foreach ($commandes as $cmd): 
                                    // Badges statut
                                    $badgesStatut = [
                                        'en_attente' => ['bg-warning', 'text-dark', 'bx-time'],
                                        'confirmee' => ['bg-info', 'text-white', 'bx-check'],
                                        'preparation' => ['bg-primary', 'text-white', 'bx-package'],
                                        'expediee' => ['bg-secondary', 'text-white', 'bx-send'],
                                        'livree' => ['bg-success', 'text-white', 'bx-check-circle'],
                                        'annulee' => ['bg-danger', 'text-white', 'bx-x-circle'],
                                        'remboursee' => ['bg-dark', 'text-white', 'bx-undo']
                                    ];
                                    $badgeS = $badgesStatut[$cmd['statut']] ?? $badgesStatut['en_attente'];
                                    
                                    // Badges paiement
                                    $badgesPaiement = [
                                        'en_attente' => ['bg-warning', 'text-dark'],
                                        'paye' => ['bg-success', 'text-white'],
                                        'echoue' => ['bg-danger', 'text-white'],
                                        'rembourse' => ['bg-secondary', 'text-white']
                                    ];
                                    $badgeP = $badgesPaiement[$cmd['statut_paiement']] ?? $badgesPaiement['en_attente'];
                                    
                                    $client = htmlspecialchars(($cmd['user_prenom'] ?? '') . ' ' . ($cmd['user_nom'] ?? ''));
                                    $total = $cmd['total_general_ttc'] ?? ($cmd['total_ttc'] + ($cmd['frais_livraison_ht'] * 1.2));
                            ?>
                            <tr class="<?= $cmd['statut'] == 'annulee' ? 'table-danger' : ($cmd['statut'] == 'livree' ? 'table-success' : '') ?>">
                                <td><?= $i++ ?></td>
                                <td>
                                    <code class="bg-light px-2 py-1 rounded fw-bold text-primary d-block mb-1"><?= htmlspecialchars($cmd['numero_commande']) ?></code>
                                    <?php if ($cmd['mode_livraison']): ?>
                                        <small class="text-muted"><i class="bx bx-truck me-1"></i><?= ucfirst($cmd['mode_livraison']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-dark d-block"><?= $client ?></strong>
                                    <small class="text-muted"><i class="bx bx-envelope me-1"></i><?= htmlspecialchars($cmd['user_email'] ?? '-') ?></small>
                                    <?php if ($cmd['user_telephone']): ?>
                                        <br><small class="text-muted"><i class="bx bx-phone me-1"></i><?= htmlspecialchars($cmd['user_telephone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bx bx-calendar me-1"></i><?= date('d/m/Y H:i', strtotime($cmd['created_at'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-75 mb-1 d-inline-block">
                                        <i class="bx bx-box me-1"></i><?= $cmd['nb_produits'] ?> article(s)
                                    </span>
                                    <div class="small text-muted" style="max-height: 60px; overflow-y: auto;">
                                        <?php foreach (array_slice($cmd['lignes'], 0, 3) as $ligne): ?>
                                            <div class="d-flex align-items-center gap-1 mb-1">
                                                <?php if (!empty($ligne['image_principale'])): ?>
                                                    <img src="<?= base_url('attachments/Produits/' . $ligne['image_principale']) ?>" class="rounded" style="width:20px; height:20px; object-fit:cover;">
                                                <?php else: ?>
                                                    <i class="bx bx-package"></i>
                                                <?php endif; ?>
                                                <span class="text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($ligne['nom_produit']) ?>">
                                                    <?= htmlspecialchars($ligne['nom_produit']) ?> x<?= $ligne['quantite'] ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($cmd['lignes']) > 3): ?>
                                            <small class="text-primary">+<?= count($cmd['lignes']) - 3 ?> autre(s)...</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-success d-block"><?= number_format($total, 2, ',', ' ') ?> €</strong>
                                    <small class="text-muted">HT: <?= number_format($cmd['total_ht'], 2, ',', ' ') ?> €</small>
                                    <?php if ($cmd['frais_livraison_ht'] > 0): ?>
                                        <br><small class="text-muted">Livraison: <?= number_format($cmd['frais_livraison_ht'] * 1.2, 2, ',', ' ') ?> €</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeS[0] ?> <?= $badgeS[1] ?>">
                                        <i class="bx <?= $badgeS[2] ?> me-1"></i><?= ucfirst(str_replace('_', ' ', $cmd['statut'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeP[0] ?> <?= $badgeP[1] ?> d-block mb-1">
                                        <?= ucfirst($cmd['statut_paiement'] ?? 'en_attente') ?>
                                    </span>
                                    <?php if ($cmd['mode_paiement']): ?>
                                        <small class="text-muted"><i class="bx bx-credit-card me-1"></i><?= ucfirst($cmd['mode_paiement']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="<?= base_url('Commandes/Detail/' . $cmd['id']) ?>"><i class="bx bx-show text-info me-2"></i>Voir détail</a></li>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalLignes<?= $cmd['id'] ?>"><i class="bx bx-list-ul text-primary me-2"></i>Voir lignes</a></li>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalStatut<?= $cmd['id'] ?>"><i class="bx bx-transfer text-warning me-2"></i>Changer statut</a></li>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalPaiement<?= $cmd['id'] ?>"><i class="bx bx-credit-card text-success me-2"></i>Modifier paiement</a></li>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalLivraison<?= $cmd['id'] ?>"><i class="bx bx-truck text-primary me-2"></i>Infos livraison</a></li>
                                            <?php if (!in_array($cmd['statut'], ['annulee', 'livree', 'remboursee'])): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#modalAnnuler<?= $cmd['id'] ?>"><i class="bx bx-x-circle me-2"></i>Annuler</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bx bx-cart text-muted" style="font-size: 4rem;"></i>
                                    <p class="mt-3 text-muted">Aucune commande trouvée</p>
                                    <a href="<?= base_url('Commandes') ?>" class="btn btn-outline-primary btn-sm"><i class="bx bx-reset"></i> Réinitialiser</a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

<!-- MODALS - PLACÉS EN DEHORS DU TABLEAU -->
<?php if (!empty($commandes)): foreach ($commandes as $cmd): ?>

<!-- Modal Lignes -->
<div class="modal fade" id="modalLignes<?= $cmd['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-list-ul me-2"></i>Lignes - <?= htmlspecialchars($cmd['numero_commande']) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produit</th>
                                <th class="text-center">Réf</th>
                                <th class="text-center">Prix HT</th>
                                <th class="text-center">Qté</th>
                                <th class="text-center">TVA</th>
                                <th class="text-end">Total HT</th>
                                <th class="text-end">Total TTC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalHT = $totalTTC = 0;
                            foreach ($cmd['lignes'] as $ligne): 
                                $totalHT += $ligne['total_ligne_ht'];
                                $totalTTC += $ligne['total_ligne_ttc'];
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($ligne['image_principale'])): ?>
                                            <img src="<?= base_url('attachments/Produits/' . $ligne['image_principale']) ?>" class="rounded me-2" style="width:40px; height:40px; object-fit:cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width:40px; height:40px"><i class="bx bx-package text-muted"></i></div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= htmlspecialchars($ligne['nom_produit']) ?></strong>
                                            <br><small class="text-muted">ID: #<?= $ligne['produit_id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><code><?= htmlspecialchars($ligne['reference_produit']) ?></code></td>
                                <td class="text-center"><?= number_format($ligne['prix_unitaire_ht'], 2, ',', ' ') ?> €</td>
                                <td class="text-center"><span class="badge bg-info"><?= $ligne['quantite'] ?></span></td>
                                <td class="text-center"><?= $ligne['taux_tva'] ?>%</td>
                                <td class="text-end"><?= number_format($ligne['total_ligne_ht'], 2, ',', ' ') ?> €</td>
                                <td class="text-end fw-bold"><?= number_format($ligne['total_ligne_ttc'], 2, ',', ' ') ?> €</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-group-divider">
                            <tr class="table-light">
                                <td colspan="5" class="text-end fw-bold">Totaux:</td>
                                <td class="text-end fw-bold"><?= number_format($totalHT, 2, ',', ' ') ?> €</td>
                                <td class="text-end fw-bold text-primary"><?= number_format($totalTTC, 2, ',', ' ') ?> €</td>
                            </tr>
                            <?php if ($cmd['frais_livraison_ht'] > 0): ?>
                            <tr>
                                <td colspan="6" class="text-end">Frais livraison HT:</td>
                                <td class="text-end"><?= number_format($cmd['frais_livraison_ht'], 2, ',', ' ') ?> €</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end">TVA livraison:</td>
                                <td class="text-end"><?= number_format($cmd['frais_livraison_ht'] * 0.2, 2, ',', ' ') ?> €</td>
                            </tr>
                            <tr class="table-success">
                                <td colspan="6" class="text-end fw-bold">TOTAL GÉNÉRAL:</td>
                                <td class="text-end fw-bold fs-5"><?= number_format($totalTTC + ($cmd['frais_livraison_ht'] * 1.2), 2, ',', ' ') ?> €</td>
                            </tr>
                            <?php endif; ?>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <a href="<?= base_url('Commandes/Detail/' . $cmd['id']) ?>" class="btn btn-primary"><i class="bx bx-show me-2"></i>Détail complet</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Statut -->
<div class="modal fade" id="modalStatut<?= $cmd['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bx bx-transfer me-2"></i>Changer statut</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Commandes/ChangeStatut') ?>" method="POST">
                <input type="hidden" name="id" value="<?= $cmd['id'] ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nouveau statut</label>
                        <select class="form-select" name="statut" required>
                            <?php foreach ($statuts as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $cmd['statut'] == $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Raison du changement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning"><i class="bx bx-save me-2"></i>Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Paiement -->
<div class="modal fade" id="modalPaiement<?= $cmd['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-credit-card me-2"></i>Statut paiement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Commandes/UpdatePaiement') ?>" method="POST">
                <input type="hidden" name="id" value="<?= $cmd['id'] ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Statut</label>
                        <select class="form-select" name="statut_paiement" required>
                            <?php foreach ($paiements as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $cmd['statut_paiement'] == $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ID Transaction</label>
                        <input type="text" class="form-control" name="transaction_id" value="<?= htmlspecialchars($cmd['transaction_id'] ?? '') ?>">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-save me-2"></i>Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Livraison -->
<div class="modal fade" id="modalLivraison<?= $cmd['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-truck me-2"></i>Infos livraison</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Commandes/UpdateLivraison') ?>" method="POST">
                <input type="hidden" name="id" value="<?= $cmd['id'] ?>">
                <div class="modal-body p-4">
                    <?php $modesLivraison = ['standard' => 'Standard', 'express' => 'Express', 'retrait' => 'Retrait magasin', 'point_relais' => 'Point relais']; ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mode</label>
                        <select class="form-select" name="mode_livraison">
                            <?php foreach ($modesLivraison as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($cmd['mode_livraison'] ?? '') == $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Frais HT (€)</label>
                        <input type="number" step="0.01" class="form-control" name="frais_livraison_ht" value="<?= $cmd['frais_livraison_ht'] ?? 0 ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Date prévue</label>
                        <input type="datetime-local" class="form-control" name="date_livraison_prevue" value="<?= !empty($cmd['date_livraison_prevue']) ? date('Y-m-d\TH:i', strtotime($cmd['date_livraison_prevue'])) : '' ?>">
                    </div>
                    <?php if (!empty($cmd['adresse_livraison'])): ?>
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="bx bx-map-pin me-2"></i>Adresse</h6>
                        <p class="mb-1"><strong><?= htmlspecialchars($cmd['adresse_livraison']['nom_complet'] ?? '') ?></strong></p>
                        <p class="mb-0"><?= htmlspecialchars($cmd['adresse_livraison']['adresse_ligne1'] ?? '') ?>, <?= htmlspecialchars($cmd['adresse_livraison']['code_postal'] ?? '') ?> <?= htmlspecialchars($cmd['adresse_livraison']['ville'] ?? '') ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($cmd['date_livraison_reelle'])): ?>
                    <div class="alert alert-success">
                        <i class="bx bx-check-circle me-2"></i>Livrée le <?= date('d/m/Y H:i', strtotime($cmd['date_livraison_reelle'])) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-2"></i>Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Annuler -->
<div class="modal fade" id="modalAnnuler<?= $cmd['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-x-circle me-2"></i>Annuler commande</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Commandes/Annuler') ?>" method="POST">
                <input type="hidden" name="id" value="<?= $cmd['id'] ?>">
                <div class="modal-body p-4 text-center">
                    <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-3">Annuler la commande <strong><?= htmlspecialchars($cmd['numero_commande']) ?></strong> ?</p>
                    <div class="alert alert-warning text-start">
                        <h6><i class="bx bx-box me-2"></i>Produits:</h6>
                        <ul class="mb-0 small">
                            <?php foreach ($cmd['lignes'] as $ligne): ?>
                                <li><?= htmlspecialchars($ligne['nom_produit']) ?> (x<?= $ligne['quantite'] ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="alert alert-info text-start"><i class="bx bx-info-circle me-2"></i>Le stock sera restitué.</div>
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold">Motif <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="raison" rows="3" required placeholder="Raison..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Retour</button>
                    <button type="submit" class="btn btn-danger"><i class="bx bx-x-circle me-2"></i>Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endforeach; endif; ?>

<!-- Modal Création Commande -->
<div class="modal fade" id="modalCreateCommande" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouvelle Commande</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Commandes/Create') ?>" method="POST" id="formCreateCommande">
                <div class="modal-body p-4">
                    <!-- Client -->
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-primary mb-3"><i class="bx bx-user me-2"></i>Client</h6>
                            <select class="form-select" name="user_id" id="selectClient" required onchange="loadAdresses(this.value)">
                                <option value="">Choisir un client...</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '') . ' - ' . ($u['email'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Adresses -->
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-primary mb-3"><i class="bx bx-map me-2"></i>Adresses</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Livraison <span class="text-danger">*</span></label>
                                    <select class="form-select" name="adresse_livraison_id" id="adresseLivraison" required disabled>
                                        <option value="">Sélectionner un client...</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Facturation <span class="text-danger">*</span></label>
                                    <select class="form-select" name="adresse_facturation_id" id="adresseFacturation" required disabled>
                                        <option value="">Sélectionner un client...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Produits -->
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-primary mb-3"><i class="bx bx-package me-2"></i>Produits</h6>
                            <div id="containerProduits">
                                <div class="row g-3 produit-ligne mb-3 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label">Produit <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control produit-search" name="produits[0][produit_id]" placeholder="ID ou référence" required>
                                            <button type="button" class="btn btn-outline-secondary btn-search-produit" data-row="0"><i class="bx bx-search"></i></button>
                                        </div>
                                        <div class="produit-details small text-muted mt-1"></div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Qté <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control produit-qte" name="produits[0][quantite]" value="1" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Prix HT <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control produit-prix" name="produits[0][prix_ht]" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Total</label>
                                        <input type="text" class="form-control produit-total" readonly value="0.00 €">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-produit" style="display:none;"><i class="bx bx-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddProduit"><i class="bx bx-plus"></i> Ajouter</button>
                            </div>
                            
                            <!-- Totaux -->
                            <div class="row justify-content-end mt-3">
                                <div class="col-md-4">
                                    <table class="table table-sm">
                                        <tr><td>Total HT:</td><td class="text-end fw-bold" id="totalHT">0.00 €</td></tr>
                                        <tr><td>TVA (20%):</td><td class="text-end" id="totalTVA">0.00 €</td></tr>
                                        <tr class="table-primary"><td>Total TTC:</td><td class="text-end fw-bold" id="totalTTC">0.00 €</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="text-primary mb-3"><i class="bx bx-cog me-2"></i>Options</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Statut</label>
                                    <select class="form-select" name="statut">
                                        <option value="en_attente">En attente</option>
                                        <option value="confirmee">Confirmée</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Mode paiement</label>
                                    <select class="form-select" name="mode_paiement">
                                        <option value="">Non défini</option>
                                        <?php foreach ($modes as $val => $label): ?>
                                            <option value="<?= $val ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Mode livraison</label>
                                    <select class="form-select" name="mode_livraison">
                                        <?php foreach ($modesLivraison as $val => $label): ?>
                                            <option value="<?= $val ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Frais livraison HT</label>
                                    <input type="number" step="0.01" class="form-control" name="frais_livraison_ht" value="0.00">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-save me-2"></i>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sélecteur Produit -->
<div class="modal fade" id="modalSelectProduit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bx bx-search me-2"></i>Sélectionner un produit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="inputSearchProduit" placeholder="Rechercher...">
                <div id="listeProduits" style="max-height: 400px; overflow-y: auto;"></div>
            </div>
        </div>
    </div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<script>
let produitIndex = 1;
let currentRow = 0;

$(document).ready(function() {
    // DataTable
    $('#tableCommandes').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
        pageLength: 25,
        order: [[3, 'desc']],
        columnDefs: [{ orderable: false, targets: [8] }]
    });
});

// Charger adresses client
function loadAdresses(userId) {
    const livraison = $('#adresseLivraison');
    const facturation = $('#adresseFacturation');
    
    if (!userId) {
        livraison.html('<option value="">Sélectionner un client...</option>').prop('disabled', true);
        facturation.html('<option value="">Sélectionner un client...</option>').prop('disabled', true);
        return;
    }
    
    $.get('<?= base_url("Commandes/GetAdressesUser/") ?>' + userId, function(data) {
        const adresses = JSON.parse(data);
        let options = '<option value="">Choisir...</option>';
        let principaleId = null;
        
        adresses.forEach(a => {
            const text = `${a.nom_complet}, ${a.adresse_ligne1}, ${a.code_postal} ${a.ville}`;
            const selected = a.est_principale == 1 ? 'selected' : '';
            if (a.est_principale == 1) principaleId = a.id;
            options += `<option value="${a.id}" ${selected}>${text}</option>`;
        });
        
        livraison.html(options).prop('disabled', false);
        facturation.html(options).prop('disabled', false);
        
        if (principaleId) {
            livraison.val(principaleId);
            facturation.val(principaleId);
        }
    });
}

// Ajouter ligne produit
$('#btnAddProduit').click(function() {
    const html = `
        <div class="row g-3 produit-ligne mb-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Produit <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control produit-search" name="produits[${produitIndex}][produit_id]" placeholder="ID ou référence" required>
                    <button type="button" class="btn btn-outline-secondary btn-search-produit" data-row="${produitIndex}"><i class="bx bx-search"></i></button>
                </div>
                <div class="produit-details small text-muted mt-1"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Qté <span class="text-danger">*</span></label>
                <input type="number" class="form-control produit-qte" name="produits[${produitIndex}][quantite]" value="1" min="1" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Prix HT <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control produit-prix" name="produits[${produitIndex}][prix_ht]" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Total</label>
                <input type="text" class="form-control produit-total" readonly value="0.00 €">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm btn-remove-produit"><i class="bx bx-trash"></i></button>
            </div>
        </div>
    `;
    $('#containerProduits').append(html);
    produitIndex++;
    updateRemoveButtons();
});

// Supprimer ligne
$(document).on('click', '.btn-remove-produit', function() {
    $(this).closest('.produit-ligne').remove();
    calculerTotaux();
    updateRemoveButtons();
});

function updateRemoveButtons() {
    $('.btn-remove-produit').toggle($('.produit-ligne').length > 1);
}

// Ouvrir sélecteur
$(document).on('click', '.btn-search-produit', function() {
    currentRow = $(this).data('row');
    $('#modalSelectProduit').modal('show');
    $('#inputSearchProduit').val('').focus();
    searchProduits('');
});

// Recherche produits
let searchTimeout;
$('#inputSearchProduit').on('keyup', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => searchProduits($(this).val()), 300);
});

function searchProduits(term) {
    $.get('<?= base_url("Commandes/SearchProduit") ?>', { term: term }, function(data) {
        const produits = JSON.parse(data);
        let html = '<div class="list-group">';
        
        if (produits.length) {
            produits.forEach(p => {
                html += `
                    <a href="#" class="list-group-item list-group-item-action select-produit" 
                       data-id="${p.id}" data-nom="${p.nom}" data-prix="${p.prix_ht}" data-ref="${p.reference}">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>${p.nom}</strong><br>
                                <small class="text-muted">Réf: ${p.reference} | Stock: ${p.stock}</small>
                            </div>
                            <span class="badge bg-primary">${parseFloat(p.prix_ht).toFixed(2)} €</span>
                        </div>
                    </a>
                `;
            });
        } else {
            html += '<div class="list-group-item text-muted">Aucun produit trouvé</div>';
        }
        html += '</div>';
        $('#listeProduits').html(html);
    });
}

// Sélectionner produit
$(document).on('click', '.select-produit', function(e) {
    e.preventDefault();
    const row = $(`.btn-search-produit[data-row="${currentRow}"]`).closest('.produit-ligne');
    
    row.find('.produit-search').val($(this).data('id'));
    row.find('.produit-prix').val($(this).data('prix'));
    row.find('.produit-details').html(`<span class="text-success"><i class="bx bx-check"></i> ${$(this).data('nom')} (${$(this).data('ref')})</span>`);
    
    calculerLigne(row);
    $('#modalSelectProduit').modal('hide');
});

// Calculs
$(document).on('change', '.produit-qte, .produit-prix', function() {
    calculerLigne($(this).closest('.produit-ligne'));
});

function calculerLigne(row) {
    const qte = parseFloat(row.find('.produit-qte').val()) || 0;
    const prix = parseFloat(row.find('.produit-prix').val()) || 0;
    row.find('.produit-total').val((qte * prix).toFixed(2) + ' €');
    calculerTotaux();
}

function calculerTotaux() {
    let total = 0;
    $('.produit-total').each(function() {
        total += parseFloat($(this).val().replace(' €', '')) || 0;
    });
    
    const tva = total * 0.20;
    const ttc = total + tva;
    
    $('#totalHT').text(total.toFixed(2) + ' €');
    $('#totalTVA').text(tva.toFixed(2) + ' €');
    $('#totalTTC').text(ttc.toFixed(2) + ' €');
}

// Validation
$('#formCreateCommande').on('submit', function(e) {
    let valid = false;
    $('.produit-search').each(function() {
        if ($(this).val().trim()) valid = true;
    });
    
    if (!valid) {
        e.preventDefault();
        alert('Veuillez ajouter au moins un produit');
    }
});
</script>