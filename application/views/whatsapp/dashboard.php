<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard WhatsApp - Whapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --whatsapp-green: #25D366;
            --whatsapp-dark: #128C7E;
            --bg-light: #f0f2f5;
        }
        
        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .page-content {
            flex: 1;
            padding: 2rem;
        }
        
        .sidebar {
            background: white;
            border-right: 1px solid #ddd;
            min-height: 100vh;
        }
        
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .btn-whatsapp {
            background-color: var(--whatsapp-green);
            border-color: var(--whatsapp-green);
            color: white;
        }
        
        .btn-whatsapp:hover {
            background-color: var(--whatsapp-dark);
            border-color: var(--whatsapp-dark);
            color: white;
        }
        
        .action-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            transition: all 0.3s;
            height: 100%;
        }
        
        .action-card:hover {
            border-color: var(--whatsapp-green);
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.15);
        }
        
        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }
        
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .status-online { background-color: var(--whatsapp-green); }
        .status-offline { background-color: #dc3545; }
        
        .quick-send {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= site_url('whatsapp') ?>">
                <i class="bi bi-whatsapp me-2" style="color: var(--whatsapp-green);"></i>
                <strong>Whapi Dashboard</strong>
            </a>
            <div class="d-flex align-items-center text-light">
                <span class="me-3">
                    <span class="status-indicator status-online me-1"></span>
                    API Connecté
                </span>
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> Admin
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= site_url('whatsapp/config') ?>"><i class="bi bi-gear me-2"></i>Configuration</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= site_url('auth/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="d-flex flex-grow-1">
        <!-- Sidebar -->
        <div class="sidebar p-3" style="width: 260px;">
            <div class="d-flex flex-column">
                <div class="p-2 mb-3 border-bottom">
                    <small class="text-muted text-uppercase fw-bold">Menu Principal</small>
                </div>
                
                <a href="<?= site_url('whatsapp') ?>" class="btn btn-light text-start mb-2 active">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="<?= site_url('whatsapp/envoyer') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-send me-2"></i> Envoyer Message au groupe
                </a>
                <a href="<?= site_url('whatsapp/participants_envoyer') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-send me-2"></i> Envoyer Message au personne
                </a>
                <a href="<?= site_url('whatsapp/groupes') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-people me-2"></i> Groupes
                    <span class="badge bg-success float-end"><?= $stats['total_groupes'] ?? 0 ?></span>
                </a>
                
                <div class="p-2 mt-3 mb-2 border-bottom">
                    <small class="text-muted text-uppercase fw-bold">Outils</small>
                </div>
                
                <a href="<?= site_url('whatsapp/historique') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-clock-history me-2"></i> Historique
                </a>
                <a href="<?= site_url('whatsapp/templates') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-file-text me-2"></i> Templates
                </a>
                <a href="<?= site_url('whatsapp/statistiques') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-graph-up me-2"></i> Statistiques
                </a>
                
                <div class="p-2 mt-3 mb-2 border-bottom">
                    <small class="text-muted text-uppercase fw-bold">Administration</small>
                </div>
                
                <a href="<?= site_url('whatsapp/synchroniser') ?>" class="btn btn-light text-start mb-2" onclick="return confirm('Synchroniser les groupes avec WhatsApp ?')">
                    <i class="bi bi-arrow-repeat me-2"></i> Synchroniser
                </a>
                <a href="<?= site_url('whatsapp/logs') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-journal-text me-2"></i> Logs API
                </a>
                <a href="<?= site_url('whatsapp/test_api') ?>" class="btn btn-light text-start mb-2">
                    <i class="bi bi-bug me-2"></i> Test API
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="page-content flex-grow-1">
            <div class="container-fluid">
                
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">Dashboard WhatsApp</h2>
                        <p class="text-muted mb-0">Gérez vos envois et groupes WhatsApp Business</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= site_url('whatsapp/envoyer') ?>" class="btn btn-whatsapp btn-lg">
                            <i class="bi bi-send-plus me-2"></i>Nouveau Message
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-lg dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-lightning-charge"></i> Actions Rapides
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= site_url('whatsapp/envoyer') ?>?type=texte"><i class="bi bi-chat-text me-2"></i>Message Texte</a></li>
                                <li><a class="dropdown-item" href="<?= site_url('whatsapp/envoyer') ?>?type=fichier"><i class="bi bi-paperclip me-2"></i>Envoyer Fichier</a></li>
                                <li><a class="dropdown-item" href="<?= site_url('whatsapp/envoyer_a_tous') ?>"><i class="bi bi-broadcast me-2"></i>Envoyer à Tous</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-success" href="<?= site_url('whatsapp/synchroniser') ?>"><i class="bi bi-arrow-repeat me-2"></i>Synchroniser Groupes</a></li>
                            </ul>
                        </div>
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

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-white">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?= $stats['total_groupes'] ?? 0 ?></h3>
                                    <small class="text-muted">Groupes Actifs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-white">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="bi bi-send-check"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?= $stats['messages_envoyes'] ?? 0 ?></h3>
                                    <small class="text-muted">Messages Envoyés</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-white">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?= $stats['en_attente'] ?? 0 ?></h3>
                                    <small class="text-muted">En Attente</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-white">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                                    <i class="bi bi-check2-all"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?= $stats['taux_succes'] ?? '100%' ?></h3>
                                    <small class="text-muted">Taux de Succès</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Grid -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <a href="<?= site_url('whatsapp/envoyer') ?>" class="text-decoration-none">
                            <div class="card action-card p-4 text-center">
                                <div class="action-icon bg-success bg-opacity-10 text-success mx-auto">
                                    <i class="bi bi-chat-dots-fill"></i>
                                </div>
                                <h5>Message Texte</h5>
                                <p class="text-muted small mb-0">Envoyez des messages texte à un ou plusieurs groupes</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= site_url('whatsapp/envoyer') ?>?type=fichier" class="text-decoration-none">
                            <div class="card action-card p-4 text-center">
                                <div class="action-icon bg-primary bg-opacity-10 text-primary mx-auto">
                                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                                </div>
                                <h5>Fichiers & Médias</h5>
                                <p class="text-muted small mb-0">PDF, Word, Images, Vidéos, Audio jusqu'à 16MB</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= site_url('whatsapp/envoyer_a_tous') ?>" class="text-decoration-none">
                            <div class="card action-card p-4 text-center">
                                <div class="action-icon bg-warning bg-opacity-10 text-warning mx-auto">
                                    <i class="bi bi-broadcast-pin"></i>
                                </div>
                                <h5>Diffusion Globale</h5>
                                <p class="text-muted small mb-0">Envoyer à tous les groupes simultanément</p>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <a href="<?= site_url('whatsapp/groupes') ?>" class="text-decoration-none">
                            <div class="card action-card p-4 text-center">
                                <div class="action-icon bg-info bg-opacity-10 text-info mx-auto">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <h5>Gérer les Groupes</h5>
                                <p class="text-muted small mb-0">Voir, éditer et synchroniser vos groupes WhatsApp</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= site_url('whatsapp/templates') ?>" class="text-decoration-none">
                            <div class="card action-card p-4 text-center">
                                <div class="action-icon bg-secondary bg-opacity-10 text-secondary mx-auto">
                                    <i class="bi bi-sticky-fill"></i>
                                </div>
                                <h5>Templates</h5>
                                <p class="text-muted small mb-0">Créer et gérer des modèles de messages</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= site_url('whatsapp/historique') ?>" class="text-decoration-none">
                            <div class="card action-card p-4 text-center">
                                <div class="action-icon bg-dark bg-opacity-10 text-dark mx-auto">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <h5>Historique</h5>
                                <p class="text-muted small mb-0">Consulter l'historique des envois et statuts</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Main Grid Content -->
                <div class="row">
                    <!-- Groupes Récents -->
                    <div class="col-lg-8 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Groupes Récents</h5>
                                <a href="<?= site_url('whatsapp/groupes') ?>" class="btn btn-sm btn-outline-primary">
                                    Voir Tous <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nom du Groupe</th>
                                                <th>ID</th>
                                                <th>Statut</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($groupes_recents)): ?>
                                                <?php foreach (array_slice($groupes_recents, 0, 5) as $groupe): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($groupe['nom']) ?></strong>
                                                        <?php if ($groupe['description']): ?>
                                                            <br><small class="text-muted"><?= substr(htmlspecialchars($groupe['description']), 0, 50) ?>...</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><code class="small"><?= substr($groupe['groupe_id'], 0, 20) ?>...</code></td>
                                                    <td>
                                                        <?php if ($groupe['actif']): ?>
                                                            <span class="badge bg-success">Actif</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Inactif</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?= site_url('whatsapp/tester/' . urlencode($groupe['groupe_id'])) ?>" 
                                                               class="btn btn-outline-success" title="Tester">
                                                                <i class="bi bi-send"></i>
                                                            </a>
                                                            <a href="<?= site_url('whatsapp/envoyer?groupe=' . urlencode($groupe['groupe_id'])) ?>" 
                                                               class="btn btn-outline-primary" title="Envoyer">
                                                                <i class="bi bi-chat-left-text"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                                <i class="bi bi-three-dots-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Modifier</a></li>
                                                                <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Désactiver</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted">
                                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                        Aucun groupe trouvé. 
                                                        <a href="<?= site_url('whatsapp/synchroniser') ?>" class="text-success">Synchroniser maintenant</a>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Widgets -->
                    <div class="col-lg-4">
                        <!-- Quick Send -->
                        <div class="card quick-send mb-4">
                            <div class="card-body">
                                <h5 class="mb-3"><i class="bi bi-lightning-charge-fill me-2"></i>Envoi Rapide</h5>
                                <form action="<?= site_url('whatsapp/traiter_envoi') ?>" method="post">
                                    <div class="mb-3">
                                        <select name="groupes_ids[]" class="form-select form-select-sm" required>
                                            <option value="">Choisir un groupe...</option>
                                            <?php foreach ($groupes_recents ?? [] as $g): ?>
                                                <option value="<?= $g['groupe_id'] ?>"><?= htmlspecialchars($g['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <textarea name="message" class="form-control form-control-sm" rows="3" 
                                                  placeholder="Votre message rapide..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-light btn-sm w-100 fw-bold">
                                        <i class="bi bi-send-fill me-1"></i> Envoyer Maintenant
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- API Status -->
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-hdd-network me-2"></i>Statut API</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Connexion Whapi</span>
                                    <span class="badge bg-success">En ligne</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Rate Limit</span>
                                    <span class="badge bg-info">OK</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Dernière sync</span>
                                    <small class="text-muted"><?= date('H:i') ?></small>
                                </div>
                                <hr>
                                <a href="<?= site_url('whatsapp/test_api') ?>" class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="bi bi-bug me-1"></i>Test de connexion
                                </a>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between">
                                <h6 class="mb-0"><i class="bi bi-activity me-2"></i>Activité Récente</h6>
                                <a href="<?= site_url('whatsapp/historique') ?>" class="small">Voir tout</a>
                            </div>
                            <div class="card-body p-0 recent-activity">
                                <div class="list-group list-group-flush">
                                    <?php if (!empty($activites_recentes)): ?>
                                        <?php foreach ($activites_recentes as $act): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <small class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Message envoyé</small>
                                                <small class="text-muted"><?= $act['heure'] ?></small>
                                            </div>
                                            <p class="mb-1 small text-truncate"><?= htmlspecialchars($act['message']) ?></p>
                                            <small class="text-muted">À <?= $act['nb_groupes'] ?> groupe(s)</small>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="list-group-item text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                            Aucune activité récente
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Action Bar -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <strong><i class="bi bi-magic me-2"></i>Actions Avancées</strong>
                                    <span class="text-muted ms-2">Outils supplémentaires pour gérer vos envois</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="<?= site_url('whatsapp/importer') ?>" class="btn btn-outline-dark">
                                        <i class="bi bi-upload me-1"></i>Importer CSV
                                    </a>
                                    <a href="<?= site_url('whatsapp/planifier') ?>" class="btn btn-outline-dark">
                                        <i class="bi bi-calendar-plus me-1"></i>Planifier Envoi
                                    </a>
                                    <a href="<?= site_url('whatsapp/exporter') ?>" class="btn btn-outline-dark">
                                        <i class="bi bi-download me-1"></i>Exporter Logs
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
// Auto-refresh des stats toutes les 30 secondes
setInterval(function() {
    fetch('<?= site_url('whatsapp/api_stats') ?>')
        .then(r => r.json())
        .then(data => {
            // Mettre à jour les compteurs si besoin
        });
}, 30000);
</script>

</body>
</html>