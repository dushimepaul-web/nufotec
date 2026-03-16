<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">E-Commerce</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gestion des Paniers</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="<?= base_url('Paniers/Export') ?>" class="btn btn-success me-2">
                    <i class="bx bx-export"></i> Exporter CSV
                </a>
                <a href="<?= base_url('Paniers/Nettoyer') ?>" class="btn btn-danger" 
                   onclick="return confirm('Nettoyer les vieux paniers ?')">
                    <i class="bx bx-trash"></i> Nettoyer (>30j)
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary bg-opacity-10 border-0">
                    <div class="card-body text-center">
                        <h3 class="text-primary"><?= $stats['total_actifs'] ?></h3>
                        <small class="text-muted">Paniers actifs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning bg-opacity-10 border-0">
                    <div class="card-body text-center">
                        <h3 class="text-warning"><?= $stats['abandonnes'] ?></h3>
                        <small class="text-muted">Abandonnés (>2j)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info bg-opacity-10 border-0">
                    <div class="card-body text-center">
                        <h3 class="text-info"><?= number_format($stats['valeur_totale'], 2, ',', ' ') ?> €</h3>
                        <small class="text-muted">Valeur totale</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success bg-opacity-10 border-0">
                    <div class="card-body text-center">
                        <h3 class="text-success"><?= number_format($stats['panier_moyen'], 2, ',', ' ') ?> €</h3>
                        <small class="text-muted">Panier moyen</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bx bx-filter-alt me-2"></i>Filtres</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= base_url('Paniers') ?>">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <select class="form-select" name="statut">
                                <option value="">Tous les statuts</option>
                                <option value="recent" <?= ($filters['statut'] ?? '') == 'recent' ? 'selected' : '' ?>>Récents (< 24h)</option>
                                <option value="abandonne" <?= ($filters['statut'] ?? '') == 'abandonne' ? 'selected' : '' ?>>Abandonnés (> 2j)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" class="form-control" name="montant_min" 
                                   value="<?= $filters['montant_min'] ?? '' ?>" placeholder="Montant min €">
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" 
                                   value="<?= $filters['search'] ?? '' ?>" placeholder="Rechercher client...">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i> Filtrer</button>
                            <a href="<?= base_url('Paniers') ?>" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des paniers -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Liste des Paniers</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tablePaniers">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Client</th>
                                <th>Articles</th>
                                <th>Montant TTC</th>
                                <th>Dernière activité</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($paniers)): foreach ($paniers as $p): 
                                $jours = $p['jours_inactivite'];
                                $est_abandonne = $jours > 2;
                            ?>
                            <tr class="<?= $est_abandonne ? 'table-warning' : '' ?>">
                                <td><?= $p['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars(($p['user_prenom'] ?? '') . ' ' . ($p['user_nom'] ?? '')) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($p['user_email'] ?? '-') ?></small>
                                    <?php if ($p['user_telephone']): ?>
                                        <br><small><i class="bx bx-phone"></i> <?= $p['user_telephone'] ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $p['nb_articles'] ?> article(s)</span>
                                </td>
                                <td>
                                    <strong class="text-primary"><?= number_format($p['total_ttc'], 2, ',', ' ') ?> €</strong>
                                    <br><small class="text-muted"><?= number_format($p['total_ht'], 2, ',', ' ') ?> € HT</small>
                                </td>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($p['updated_at'])) ?>
                                    <br>
                                    <?php if ($jours == 0): ?>
                                        <span class="badge bg-success">Aujourd'hui</span>
                                    <?php elseif ($jours == 1): ?>
                                        <span class="badge bg-info">Hier</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?= $jours ?> jours</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($est_abandonne): ?>
                                        <span class="badge bg-warning text-dark"><i class="bx bx-time"></i> Abandonné</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><i class="bx bx-check"></i> Actif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('Paniers/Detail/' . $p['id']) ?>">
                                                    <i class="bx bx-show text-info me-2"></i>Voir détail
                                                </a>
                                            </li>
                                            <?php if ($est_abandonne): ?>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="relancerPanier(<?= $p['id'] ?>)">
                                                    <i class="bx bx-envelope text-warning me-2"></i>Relancer client
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('Paniers/ConvertirCommande/' . $p['id']) ?>">
                                                    <i class="bx bx-cart text-success me-2"></i>Convertir en commande
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bx bx-cart text-muted fs-1"></i>
                                    <p class="text-muted">Aucun panier trouvé</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top produits dans les paniers -->
        <?php if (!empty($stats['top_produits'])): ?>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bx bx-trophy me-2"></i>Top produits dans les paniers</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($stats['top_produits'] as $prod): ?>
                    <div class="col-md-2 col-6 text-center mb-3">
                        <div class="p-3 bg-light rounded">
                            <h4 class="text-primary mb-1"><?= $prod['total'] ?></h4>
                            <small class="text-muted text-truncate d-block"><?= htmlspecialchars($prod['nom_produit']) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>


<script>
function relancerPanier(panierId) {
    if (!confirm('Envoyer un email de relance au client ?')) return;
    
    $.post('<?= base_url("Paniers/Relancer/") ?>' + panierId, function(response) {
        if (response.success) {
            alert('Email envoyé avec succès !');
        } else {
            alert('Erreur: ' + response.message);
        }
    }, 'json');
}

$(document).ready(function() {
    $('#tablePaniers').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
        order: [[4, 'desc']],
        pageLength: 25
    });
});
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>