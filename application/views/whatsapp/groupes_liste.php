<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Groupes WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .page-wrapper { min-height: 100vh; display: flex; flex-direction: column; }
        .page-content { flex: 1; padding: 2rem; background: #f8f9fa; }
        .sidebar { background: white; border-right: 1px solid #dee2e6; min-height: 100vh; width: 260px; }
        .stat-card { border: none; border-radius: 8px; }
        .groupe-card { transition: all 0.2s; }
        .groupe-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .btn-whatsapp { background: #25D366; border-color: #25D366; color: white; }
        .btn-whatsapp:hover { background: #128C7E; border-color: #128C7E; color: white; }
        .status-online { color: #25D366; }
        .status-offline { color: #dc3545; }
    </style>
</head>
<body>

<div class="page-wrapper">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= site_url('whatsapp') ?>">
                <i class="bi bi-whatsapp me-2" style="color: #25D366;"></i>
                <strong>Whapi Dashboard</strong>
            </a>
            <div class="d-flex align-items-center text-light">
                <span class="me-3">
                    <?php if (isset($api_status) && $api_status['success']): ?>
                        <i class="bi bi-circle-fill status-online me-1 small"></i> API OK
                    <?php else: ?>
                        <i class="bi bi-circle-fill status-offline me-1 small"></i> API Hors ligne
                    <?php endif; ?>
                </span>
                <a href="<?= site_url('auth/logout') ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="d-flex flex-grow-1">
        <!-- Sidebar -->
        <div class="sidebar p-3">
            <div class="d-flex flex-column">
                <div class="p-2 mb-3 border-bottom">
                    <small class="text-muted text-uppercase fw-bold">Menu Principal</small>
                </div>
                
                <a href="<?= site_url('whatsapp') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="<?= site_url('whatsapp/envoyer') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-send me-2"></i> Envoyer Message
                </a>
                <a href="<?= site_url('whatsapp/groupes') ?>" class="btn btn-primary text-start mb-2">
                    <i class="bi bi-people me-2"></i> Groupes
                    <span class="badge bg-light text-primary float-end"><?= $total ?></span>
                </a>
                
                <div class="p-2 mt-3 mb-2 border-bottom">
                    <small class="text-muted text-uppercase fw-bold">Outils</small>
                </div>
                
                <a href="<?= site_url('whatsapp/synchroniser') ?>" class="btn btn-light text-start mb-2" 
                   onclick="return confirm('Synchroniser les groupes avec WhatsApp ?')">
                    <i class="bi bi-arrow-repeat me-2"></i> Synchroniser
                </a>
                <a href="<?= site_url('whatsapp/test_direct') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-bug me-2"></i> Test API
                </a>
                <a href="<?= site_url('whatsapp/voir_groupes') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-database me-2"></i> Debug DB
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="page-content flex-grow-1">
            <div class="container-fluid">
                
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1"><i class="bi bi-people-fill me-2"></i>Groupes WhatsApp</h2>
                        <p class="text-muted mb-0">Gérez vos groupes et envoyez des messages</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= site_url('whatsapp/synchroniser') ?>" class="btn btn-success">
                            <i class="bi bi-arrow-repeat me-1"></i>Synchroniser
                        </a>
                        <a href="<?= site_url('whatsapp/envoyer') ?>" class="btn btn-whatsapp">
                            <i class="bi bi-send-plus me-1"></i>Nouveau Message
                        </a>
                    </div>
                </div>

                <!-- Flash Messages -->
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= $this->session->flashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($this->session->flashdata('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="bi bi-exclamation-circle me-2"></i><?= $this->session->flashdata('warning') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body d-flex align-items-center">
                                <i class="bi bi-people-fill fs-1 me-3"></i>
                                <div>
                                    <h3 class="mb-0"><?= $total ?></h3>
                                    <small>Groupes Totaux</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body d-flex align-items-center">
                                <i class="bi bi-check-circle-fill fs-1 me-3"></i>
                                <div>
                                    <h3 class="mb-0"><?= count(array_filter($groupes, fn($g) => $g['actif'])) ?></h3>
                                    <small>Groupes Actifs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body d-flex align-items-center">
                                <i class="bi bi-clock-history fs-1 me-3"></i>
                                <div>
                                    <h3 class="mb-0"><?= isset($api_status['timestamp']) ? date('H:i') : '--:--' ?></h3>
                                    <small>Dernière Sync</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des groupes -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Liste des Groupes</h5>
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchGroupes" placeholder="Rechercher un groupe...">
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($groupes)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle" id="tableGroupes">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Nom du Groupe</th>
                                            <th>ID WhatsApp</th>
                                            <th>Description</th>
                                            <th>Statut</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($groupes as $index => $groupe): ?>
                                        <tr class="groupe-row">
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong class="groupe-nom"><?= htmlspecialchars($groupe['nom']) ?></strong>
                                            </td>
                                            <td>
                                                <code class="small text-muted"><?= htmlspecialchars($groupe['groupe_id']) ?></code>
                                                <button class="btn btn-sm btn-link" onclick="copyToClipboard('<?= $groupe['groupe_id'] ?>')" title="Copier">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </td>
                                            <td class="text-muted small">
                                                <?= !empty($groupe['description']) ? substr(htmlspecialchars($groupe['description']), 0, 50) . '...' : '-' ?>
                                            </td>
                                            <td>
                                                <?php if ($groupe['actif']): ?>
                                                    <span class="badge bg-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="<?= site_url('whatsapp/tester/' . urlencode($groupe['groupe_id'])) ?>" 
                                                       class="btn btn-sm btn-outline-success" 
                                                       onclick="return confirm('Envoyer un message de test à <?= htmlspecialchars($groupe['nom']) ?> ?')"
                                                       title="Test">
                                                        <i class="bi bi-send"></i>
                                                    </a>
                                                    <a href="<?= site_url('whatsapp/envoyer?groupe=' . urlencode($groupe['groupe_id'])) ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Envoyer message">
                                                        <i class="bi bi-chat-left-text"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="<?= site_url('whatsapp/envoyer?groupe=' . urlencode($groupe['groupe_id']) . '&type=fichier') ?>">
                                                                <i class="bi bi-paperclip me-2"></i>Envoyer fichier
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#">
                                                                <i class="bi bi-trash me-2"></i>Désactiver
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                <h5>Aucun groupe trouvé</h5>
                                <p class="text-muted">Synchronisez vos groupes WhatsApp pour commencer</p>
                                <a href="<?= site_url('whatsapp/synchroniser') ?>" class="btn btn-success">
                                    <i class="bi bi-arrow-repeat me-1"></i>Synchroniser maintenant
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($groupes)): ?>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Total: <?= $total ?> groupe(s)</small>
                            <div class="btn-group">
                                <a href="<?= site_url('whatsapp/api_groupes') ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                    <i class="bi bi-code me-1"></i>API JSON
                                </a>
                                <a href="<?= site_url('whatsapp/voir_groupes') ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-database me-1"></i>Debug
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Actions rapides bottom -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <strong><i class="bi bi-lightning-charge me-2"></i>Actions Rapides</strong>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="<?= site_url('whatsapp/envoyer_a_tous') ?>" class="btn btn-warning">
                                        <i class="bi bi-broadcast me-1"></i>Envoyer à tous
                                    </a>
                                    <a href="<?= site_url('whatsapp/test_direct') ?>" class="btn btn-outline-dark">
                                        <i class="bi bi-bug me-1"></i>Test API
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Copier dans le presse-papier
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('ID copié: ' + text);
    });
}

// Recherche en temps réel
document.getElementById('searchGroupes')?.addEventListener('keyup', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.groupe-row').forEach(row => {
        const nom = row.querySelector('.groupe-nom').textContent.toLowerCase();
        row.style.display = nom.includes(term) ? '' : 'none';
    });
});
</script>

</body>
</html>