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
                        <li class="breadcrumb-item"><a href="<?= base_url('Commandes') ?>">Commandes</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($commande['numero_commande']) ?></li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="<?= base_url('Commandes') ?>" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i>Retour
                </a>
                <a href="<?= base_url('Commandes/Export?id=' . $commande['id']) ?>" class="btn btn-success me-2" target="_blank">
                    <i class="bx bx-export me-1"></i>Exporter
                </a>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="bx bx-printer me-1"></i>Imprimer
                </button>
            </div>
        </div>

        <!-- Messages Flash -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- En-tête Commande -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-0 text-primary">
                            <i class="bx bx-cart me-2"></i><?= htmlspecialchars($commande['numero_commande']) ?>
                        </h4>
                        <small class="text-muted">
                            Créée le <?= date('d/m/Y à H:i', strtotime($commande['created_at'])) ?>
                            <?php if ($commande['ip_commande']): ?>
                                | IP: <?= $commande['ip_commande'] ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <?php
                        $statut_badges = [
                            'en_attente' => ['bg-warning', 'text-dark', 'En attente'],
                            'confirmee' => ['bg-info', 'text-white', 'Confirmée'],
                            'preparation' => ['bg-primary', 'text-white', 'En préparation'],
                            'expediee' => ['bg-secondary', 'text-white', 'Expédiée'],
                            'livree' => ['bg-success', 'text-white', 'Livrée'],
                            'annulee' => ['bg-danger', 'text-white', 'Annulée'],
                            'remboursee' => ['bg-dark', 'text-white', 'Remboursée']
                        ];
                        $badge = $statut_badges[$commande['statut']] ?? $statut_badges['en_attente'];
                        ?>
                        <span class="badge <?= $badge[0] ?> <?= $badge[1] ?> fs-6 px-3 py-2">
                            <i class="bx bx-circle me-1"></i><?= $badge[2] ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Colonne gauche : Informations -->
            <div class="col-lg-8">
                
                <!-- Produits commandés -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bx bx-package me-2"></i>Produits commandés</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">Img</th>
                                        <th>Produit</th>
                                        <th class="text-center">Référence</th>
                                        <th class="text-center">Prix unit. HT</th>
                                        <th class="text-center">Qté</th>
                                        <th class="text-center">TVA</th>
                                        <th class="text-end">Total HT</th>
                                        <th class="text-end">Total TTC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lignes as $ligne): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($ligne['image_principale'])): ?>
                                                <img src="<?= base_url('attachments/Produits/' . $ligne['image_principale']) ?>" 
                                                     class="rounded" style="width:50px; height:50px; object-fit:cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width:50px; height:50px">
                                                    <i class="bx bx-package text-muted fs-4"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($ligne['nom_produit']) ?></strong>
                                            <?php if ($ligne['description_courte']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($ligne['description_courte']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><code><?= htmlspecialchars($ligne['reference_produit']) ?></code></td>
                                        <td class="text-center"><?= number_format($ligne['prix_unitaire_ht'], 2, ',', ' ') ?> €</td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?= $ligne['quantite'] ?></span>
                                        </td>
                                        <td class="text-center"><?= $ligne['taux_tva'] ?>%</td>
                                        <td class="text-end"><?= number_format($ligne['total_ligne_ht'], 2, ',', ' ') ?> €</td>
                                        <td class="text-end fw-bold"><?= number_format($ligne['total_ligne_ttc'], 2, ',', ' ') ?> €</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-group-divider">
                                    <tr class="table-light">
                                        <td colspan="6" class="text-end fw-bold">Sous-total produits HT:</td>
                                        <td class="text-end fw-bold"><?= number_format($totaux['total_lignes_ht'], 2, ',', ' ') ?> €</td>
                                        <td class="text-end fw-bold text-primary"><?= number_format($totaux['total_lignes_ttc'], 2, ',', ' ') ?> €</td>
                                    </tr>
                                    <?php if ($commande['frais_livraison_ht'] > 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-end">Frais de livraison HT:</td>
                                        <td class="text-end"><?= number_format($commande['frais_livraison_ht'], 2, ',', ' ') ?> €</td>
                                        <td class="text-end"><?= number_format($totaux['frais_livraison_ttc'], 2, ',', ' ') ?> €</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end">TVA sur livraison:</td>
                                        <td class="text-end"><?= number_format($totaux['tva_livraison'], 2, ',', ' ') ?> €</td>
                                        <td class="text-end">-</td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="table-success">
                                        <td colspan="6" class="text-end fw-bold fs-5">TOTAL GÉNÉRAL TTC:</td>
                                        <td colspan="2" class="text-end fw-bold fs-5 text-success">
                                            <?= number_format($totaux['total_general'], 2, ',', ' ') ?> €
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Informations client -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bx bx-user me-2"></i>Informations client</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Client</h6>
                                <p class="mb-1"><strong><?= htmlspecialchars(($client['prenom'] ?? '') . ' ' . ($client['nom'] ?? '')) ?></strong></p>
                                <p class="mb-1"><i class="bx bx-envelope me-2 text-muted"></i><?= htmlspecialchars($client['email'] ?? '-') ?></p>
                                <?php if ($client['telephone']): ?>
                                    <p class="mb-1"><i class="bx bx-phone me-2 text-muted"></i><?= htmlspecialchars($client['telephone']) ?></p>
                                <?php endif; ?>
                                <?php if ($client['created_at']): ?>
                                    <p class="mb-0"><small class="text-muted">Client depuis le <?= date('d/m/Y', strtotime($client['created_at'])) ?></small></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Informations commande</h6>
                                <p class="mb-1"><strong>N° Commande:</strong> <?= htmlspecialchars($commande['numero_commande']) ?></p>
                                <p class="mb-1"><strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($commande['created_at'])) ?></p>
                                <p class="mb-1"><strong>Mode livraison:</strong> <?= ucfirst($commande['mode_livraison'] ?? 'Standard') ?></p>
                                <p class="mb-0"><strong>Mode paiement:</strong> <?= ucfirst($commande['mode_paiement'] ?? 'Non défini') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Adresses -->
                <div class="row">
                    <!-- Adresse de livraison -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-truck me-2"></i>Adresse de livraison</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($adresse_livraison): ?>
                                    <p class="mb-1"><strong><?= htmlspecialchars($adresse_livraison['nom_complet']) ?></strong></p>
                                    <p class="mb-1"><?= htmlspecialchars($adresse_livraison['adresse_ligne1']) ?></p>
                                    <?php if ($adresse_livraison['adresse_ligne2']): ?>
                                        <p class="mb-1"><?= htmlspecialchars($adresse_livraison['adresse_ligne2']) ?></p>
                                    <?php endif; ?>
                                    <p class="mb-1"><?= htmlspecialchars($adresse_livraison['code_postal']) ?> <?= htmlspecialchars($adresse_livraison['ville']) ?></p>
                                    <?php if ($adresse_livraison['pays']): ?>
                                        <p class="mb-0 text-muted"><?= htmlspecialchars($adresse_livraison['pays']['pays'] ?? '') ?></p>
                                    <?php endif; ?>
                                    <?php if ($adresse_livraison['telephone']): ?>
                                        <p class="mb-0 mt-2"><i class="bx bx-phone me-1"></i><?= htmlspecialchars($adresse_livraison['telephone']) ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Adresse non disponible</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Adresse de facturation -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-receipt me-2"></i>Adresse de facturation</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($adresse_facturation): ?>
                                    <p class="mb-1"><strong><?= htmlspecialchars($adresse_facturation['nom_complet']) ?></strong></p>
                                    <p class="mb-1"><?= htmlspecialchars($adresse_facturation['adresse_ligne1']) ?></p>
                                    <?php if ($adresse_facturation['adresse_ligne2']): ?>
                                        <p class="mb-1"><?= htmlspecialchars($adresse_facturation['adresse_ligne2']) ?></p>
                                    <?php endif; ?>
                                    <p class="mb-1"><?= htmlspecialchars($adresse_facturation['code_postal']) ?> <?= htmlspecialchars($adresse_facturation['ville']) ?></p>
                                    <?php if ($adresse_facturation['pays']): ?>
                                        <p class="mb-0 text-muted"><?= htmlspecialchars($adresse_facturation['pays']['pays'] ?? '') ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Adresse non disponible</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <?php if ($commande['notes']): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-note me-2"></i>Notes</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted" style="white-space: pre-line;"><?= nl2br(htmlspecialchars($commande['notes'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Colonne droite : Actions et récapitulatif -->
            <div class="col-lg-4">
                
                <!-- Récapitulatif -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bx bx-calculator me-2"></i>Récapitulatif</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td>Total produits HT:</td>
                                <td class="text-end"><?= number_format($totaux['total_lignes_ht'], 2, ',', ' ') ?> €</td>
                            </tr>
                            <tr>
                                <td>TVA produits:</td>
                                <td class="text-end"><?= number_format($totaux['total_tva_lignes'], 2, ',', ' ') ?> €</td>
                            </tr>
                            <tr>
                                <td>Total produits TTC:</td>
                                <td class="text-end fw-bold"><?= number_format($totaux['total_lignes_ttc'], 2, ',', ' ') ?> €</td>
                            </tr>
                            <?php if ($commande['frais_livraison_ht'] > 0): ?>
                            <tr>
                                <td>Frais livraison HT:</td>
                                <td class="text-end"><?= number_format($commande['frais_livraison_ht'], 2, ',', ' ') ?> €</td>
                            </tr>
                            <tr>
                                <td>TVA livraison:</td>
                                <td class="text-end"><?= number_format($totaux['tva_livraison'], 2, ',', ' ') ?> €</td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-primary">
                                <td class="fw-bold">TOTAL GÉNÉRAL:</td>
                                <td class="text-end fw-bold fs-5 text-primary"><?= number_format($totaux['total_general'], 2, ',', ' ') ?> €</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Paiement -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-credit-card me-2"></i>Paiement</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $paiement_badges = [
                            'en_attente' => ['bg-warning', 'text-dark', 'En attente'],
                            'paye' => ['bg-success', 'text-white', 'Payé'],
                            'echoue' => ['bg-danger', 'text-white', 'Échoué'],
                            'rembourse' => ['bg-secondary', 'text-white', 'Remboursé']
                        ];
                        $badgeP = $paiement_badges[$commande['statut_paiement']] ?? $paiement_badges['en_attente'];
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Statut:</span>
                            <span class="badge <?= $badgeP[0] ?> <?= $badgeP[1] ?>"><?= $badgeP[2] ?></span>
                        </div>
                        <?php if ($commande['mode_paiement']): ?>
                            <p class="mb-1"><strong>Mode:</strong> <?= ucfirst($commande['mode_paiement']) ?></p>
                        <?php endif; ?>
                        <?php if ($commande['transaction_id']): ?>
                            <p class="mb-1"><strong>Transaction:</strong> <code><?= htmlspecialchars($commande['transaction_id']) ?></code></p>
                        <?php endif; ?>
                        <?php if ($commande['date_paiement']): ?>
                            <p class="mb-0"><strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($commande['date_paiement'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Livraison -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-truck me-2"></i>Livraison</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Mode:</strong> <?= ucfirst($commande['mode_livraison'] ?? 'Standard') ?></p>
                        <p class="mb-1"><strong>Frais HT:</strong> <?= number_format($commande['frais_livraison_ht'], 2, ',', ' ') ?> €</p>
                        <?php if ($commande['date_livraison_prevue']): ?>
                            <p class="mb-1"><strong>Date prévue:</strong> <?= date('d/m/Y', strtotime($commande['date_livraison_prevue'])) ?></p>
                        <?php endif; ?>
                        <?php if ($commande['date_livraison_reelle']): ?>
                            <p class="mb-0 text-success"><i class="bx bx-check-circle me-1"></i>Livrée le <?= date('d/m/Y', strtotime($commande['date_livraison_reelle'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if (!in_array($commande['statut'], ['annulee', 'livree'])): ?>
                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalStatut<?= $commande['id'] ?>">
                                    <i class="bx bx-transfer me-1"></i>Changer statut
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($commande['statut_paiement'] != 'paye'): ?>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPaiement<?= $commande['id'] ?>">
                                    <i class="bx bx-credit-card me-1"></i>Marquer payé
                                </button>
                            <?php endif; ?>
                            
                            <?php if (in_array($commande['statut'], ['confirmee', 'preparation'])): ?>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalExpedition<?= $commande['id'] ?>">
                                    <i class="bx bx-send me-1"></i>Marquer expédiée
                                </button>
                            <?php endif; ?>
                            
                            <?php if (!in_array($commande['statut'], ['annulee', 'livree', 'remboursee'])): ?>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalAnnuler<?= $commande['id'] ?>">
                                    <i class="bx bx-x-circle me-1"></i>Annuler commande
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

<!-- Modales d'action (placées en dehors du flux principal) -->

<!-- Modal Changer Statut -->
<?php if (!in_array($commande['statut'], ['annulee', 'livree'])): ?>
<div class="modal fade" id="modalStatut<?= $commande['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Changer le statut</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Commandes/ChangeStatut') ?>" method="POST">
                <input type="hidden" name="id" value="<?= $commande['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nouveau statut</label>
                        <select class="form-select" name="statut" required>
                            <?php 
                            $statutsDispo = ['en_attente', 'confirmee', 'preparation', 'expediee', 'livree', 'annulee', 'remboursee'];
                            foreach ($statutsDispo as $s): 
                                $labels = ['en_attente' => 'En attente', 'confirmee' => 'Confirmée', 'preparation' => 'En préparation', 'expediee' => 'Expédiée', 'livree' => 'Livrée', 'annulee' => 'Annulée', 'remboursee' => 'Remboursée'];
                            ?>
                                <option value="<?= $s ?>" <?= $commande['statut'] == $s ? 'selected' : '' ?>><?= $labels[$s] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Paiement -->
<?php if ($commande['statut_paiement'] != 'paye'): ?>
<div class="modal fade" id="modalPaiement<?= $commande['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Confirmer le paiement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Commandes/UpdatePaiement') ?>" method="POST">
                <input type="hidden" name="id" value="<?= $commande['id'] ?>">
                <input type="hidden" name="statut_paiement" value="paye">
                <div class="modal-body">
                    <p>Confirmer le paiement de <strong><?= number_format($totaux['total_general'], 2, ',', ' ') ?> €</strong> ?</p>
                    <div class="mb-3">
                        <label class="form-label">ID Transaction (optionnel)</label>
                        <input type="text" class="form-control" name="transaction_id" placeholder="ID de transaction">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Confirmer le paiement</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Annuler -->
<?php if (!in_array($commande['statut'], ['annulee', 'livree', 'remboursee'])): ?>
<div class="modal fade" id="modalAnnuler<?= $commande['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Annuler la commande</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Commandes/Annuler') ?>" method="POST">
                <input type="hidden" name="id" value="<?= $commande['id'] ?>">
                <div class="modal-body text-center">
                    <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-3">Voulez-vous vraiment annuler cette commande ?</p>
                    <div class="alert alert-warning text-start">
                        <small>Le stock des produits sera automatiquement restitué.</small>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="form-label">Motif <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="raison" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Retour</button>
                    <button type="submit" class="btn btn-danger">Confirmer l'annulation</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>