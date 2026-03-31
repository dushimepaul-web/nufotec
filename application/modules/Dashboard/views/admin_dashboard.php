<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: <?= $theme_colors['primary'] ?>; --secondary: <?= $theme_colors['secondary'] ?>; --success: <?= $theme_colors['success'] ?>; --warning: <?= $theme_colors['warning'] ?>; --danger: <?= $theme_colors['danger'] ?>; --info: <?= $theme_colors['info'] ?>; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f6fa; color: #2c3e50; }
        .sidebar { width: 280px; height: 100vh; background: linear-gradient(180deg, var(--primary) 0%, #0a3d6e 100%); position: fixed; left: 0; top: 0; z-index: 1000; overflow-y: auto; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .brand-logo { display: flex; align-items: center; gap: 12px; color: white; text-decoration: none; font-size: 1.4rem; font-weight: 700; }
        .brand-logo i { font-size: 2rem; color: var(--info); }
        .nav-menu { padding: 20px 0; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 14px 25px; color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s ease; border-left: 4px solid transparent; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; border-left-color: var(--info); }
        .main-content { margin-left: 280px; padding: 30px; min-height: 100vh; }
        .top-header { background: white; border-radius: 16px; padding: 20px 30px; margin-bottom: 30px; box-shadow: 0 2px 20px rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center; }
        .page-title h1 { font-size: 1.8rem; font-weight: 700; color: var(--primary); margin-bottom: 5px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 25px; margin-bottom: 30px; }
        .stat-card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.3s ease; position: relative; overflow: hidden; }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: linear-gradient(90deg, var(--primary), var(--secondary)); }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        .stat-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .stat-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; }
        .stat-icon.primary { background: rgba(6, 44, 84, 0.1); color: var(--primary); }
        .stat-icon.success { background: rgba(15, 118, 110, 0.1); color: var(--success); }
        .stat-icon.warning { background: rgba(255, 140, 0, 0.1); color: var(--warning); }
        .stat-icon.info { background: rgba(255, 208, 0, 0.1); color: #d4a800; }
        .stat-value { font-size: 2.2rem; font-weight: 700; color: var(--primary); margin-bottom: 8px; }
        .stat-label { color: #6c757d; font-size: 0.95rem; font-weight: 500; }
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .card { background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: none; }
        .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; padding: 25px; display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 1.2rem; font-weight: 700; color: var(--primary); margin: 0; }
        .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-modern th { background: #f8f9fa; padding: 15px; font-weight: 600; color: #6c757d; font-size: 0.9rem; text-transform: uppercase; border: none; }
        .table-modern td { padding: 18px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .status-badge.success { background: rgba(15, 118, 110, 0.1); color: var(--success); }
        .status-badge.warning { background: rgba(255, 140, 0, 0.1); color: var(--warning); }
        .status-badge.danger { background: rgba(220, 20, 60, 0.1); color: var(--danger); }
        .status-badge.info { background: rgba(255, 208, 0, 0.15); color: #b38600; }
        .quick-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .quick-action-btn { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 25px 15px; border-radius: 16px; text-decoration: none; color: white; transition: all 0.3s ease; text-align: center; }
        .quick-action-btn:hover { transform: translateY(-3px); opacity: 0.9; color: white; }
        .alert-item { display: flex; align-items: flex-start; gap: 15px; padding: 18px; border-radius: 12px; margin-bottom: 15px; }
        .alert-item.warning { background: rgba(255, 140, 0, 0.08); border-left: 4px solid var(--warning); }
        .alert-item.danger { background: rgba(220, 20, 60, 0.08); border-left: 4px solid var(--danger); }
        .alert-item.info { background: rgba(255, 208, 0, 0.08); border-left: 4px solid var(--info); }
        .avatar-sm { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        @media (max-width: 1200px) { .content-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="<?= base_url('dashboard') ?>" class="brand-logo">
                <i class="bx bx-plus-medical"></i>
                <span>NUFOTEC</span>
            </a>
        </div>
        <nav class="nav-menu">
            <a href="<?= base_url('dashboard') ?>" class="nav-link active"><i class="bx bxs-dashboard"></i><span>Tableau de bord</span></a>
            <a href="<?= base_url('admin/consultations') ?>" class="nav-link"><i class="bx bx-calendar"></i><span>Consultations</span></a>
            <a href="<?= base_url('admin/medecins') ?>" class="nav-link"><i class="bx bx-plus-medical"></i><span>Médecins</span></a>
            <a href="<?= base_url('admin/patients') ?>" class="nav-link"><i class="bx bx-user"></i><span>Patients</span></a>
            <a href="<?= base_url('admin/investissements') ?>" class="nav-link"><i class="bx bx-line-chart"></i><span>Investissements</span></a>
            <a href="<?= base_url('admin/brokers') ?>" class="nav-link"><i class="bx bx-briefcase"></i><span>Courtiers</span></a>
            <a href="<?= base_url('admin/produits') ?>" class="nav-link"><i class="bx bx-package"></i><span>Produits</span></a>
            <a href="<?= base_url('admin/utilisateurs') ?>" class="nav-link"><i class="bx bx-group"></i><span>Utilisateurs</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <div class="page-title">
                <h1><i class="<?= $page_icon ?>"></i> <?= htmlspecialchars($page_title) ?></h1>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light" onclick="location.reload()"><i class="bx bx-refresh"></i></button>
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <img src="<?= base_url('uploads/' . ($user_info['photo'] ?? 'default-avatar.png')) ?>" class="avatar-sm">
                        <span><?= htmlspecialchars(($user_info['prenom'] ?? '') . ' ' . ($user_info['nom'] ?? '')) ?></span>
                        <i class="bx bx-chevron-down"></i>
                    </div>
                </div>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon primary"><i class="bx bx-group"></i></div>
                </div>
                <div class="stat-value"><?= number_format($global_stats['users']['total'] ?? 0) ?></div>
                <div class="stat-label">Utilisateurs</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon success"><i class="bx bx-plus-medical"></i></div>
                </div>
                <div class="stat-value"><?= number_format($global_stats['medecins']['total'] ?? 0) ?></div>
                <div class="stat-label">Médecins</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon warning"><i class="bx bx-calendar"></i></div>
                </div>
                <div class="stat-value"><?= number_format($global_stats['consultations']['total'] ?? 0) ?></div>
                <div class="stat-label">Consultations</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon info"><i class="bx bx-dollar-circle"></i></div>
                </div>
                <div class="stat-value">$<?= number_format($telemedecine_stats['revenue_today'] ?? 0, 0) ?></div>
                <div class="stat-label">Revenus Aujourd'hui</div>
            </div>
        </div>

        <div class="content-grid">
            <div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bx bx-calendar-check"></i> Consultations Récentes</h5>
                        <a href="<?= base_url('admin/consultations') ?>" class="btn btn-sm btn-primary">Voir tout</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table-modern">
                                <thead>
                                    <tr><th>Patient</th><th>Médecin</th><th>Type</th><th>Statut</th><th>Montant</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latest_consultations as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['patient_prenom'] . ' ' . $c['patient_nom']) ?></td>
                                        <td>Dr. <?= htmlspecialchars($c['medecin_nom'] ?? 'N/A') ?></td>
                                        <td><?= ucfirst($c['type']) ?></td>
                                        <td><span class="status-badge <?= $c['statut'] ?>"><?= ucfirst($c['statut']) ?></span></td>
                                        <td>$<?= number_format($c['honoraires_consultation'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bx bx-bell"></i> Alertes</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($system_alerts as $alert): ?>
                        <div class="alert-item <?= $alert['type'] ?>">
                            <i class="<?= $alert['icon'] ?> fs-4"></i>
                            <div>
                                <h6><?= htmlspecialchars($alert['title']) ?></h6>
                                <small><?= htmlspecialchars($alert['message']) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bx bx-lightning"></i> Actions Rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <?php foreach ($quick_actions as $action): ?>
                            <a href="<?= $action['link'] ?>" class="quick-action-btn bg-<?= $action['color'] ?>">
                                <i class="<?= $action['icon'] ?> fs-3"></i>
                                <span><?= $action['title'] ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>