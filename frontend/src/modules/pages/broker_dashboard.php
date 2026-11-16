<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUFOTEC - Dashboard Courtier</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #0f4c3a;
            --primary-light: #1a6b52;
            --primary-dark: #0a3326;
            --primary-soft: rgba(15, 76, 58, 0.1);
            --accent: #d4af37;
            --accent-hover: #b8962e;
            --accent-soft: rgba(212, 175, 55, 0.15);
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #dee2e6;
            --gray-soft: #f1f3f5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --blue: #2563eb;
            --indigo: #4f46e5;
            --purple: #8b5cf6;
            --pink: #ec4899;
            --shadow-sm: 0 4px 6px rgba(0,0,0,0.05);
            --shadow: 0 10px 20px rgba(0,0,0,0.1);
            --shadow-lg: 0 20px 40px rgba(0,0,0,0.15);
            --shadow-xl: 0 30px 60px rgba(0,0,0,0.2);
            --shadow-hover: 0 30px 50px rgba(15, 76, 58, 0.25);
            --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            --border-radius-sm: 12px;
            --border-radius-md: 20px;
            --border-radius-lg: 30px;
            --border-radius-xl: 40px;
            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--light);
            color: var(--dark);
            font-family: var(--font-primary);
            overflow-x: hidden;
        }

        /* Header Dashboard */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--accent);
        }
        .logo span { color: white; }

        .user-info {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .user-details {
            text-align: right;
        }
        .user-name {
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .user-firm {
            color: var(--accent);
            font-size: 0.75rem;
            margin-top: 4px;
        }
        .logout-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            padding: 8px 20px;
            border-radius: 40px;
            color: white;
            cursor: pointer;
            font-family: var(--font-primary);
            font-size: 0.85rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        /* Layout Dashboard */
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px;
            position: relative;
            z-index: 1;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: white;
            border-radius: var(--border-radius-md);
            padding: 24px;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-light);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
            border-color: var(--primary-soft);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-soft);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            color: var(--primary);
            font-size: 1.4rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
        }
        .stat-label {
            color: var(--gray);
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Section Projet */
        .project-section {
            background: white;
            border-radius: var(--border-radius-md);
            margin-bottom: 32px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-light);
        }
        .project-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            padding: 28px 32px;
            color: white;
        }
        .project-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .project-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .project-content {
            padding: 32px;
        }
        .project-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }
        .detail-item {
            background: var(--gray-soft);
            padding: 20px;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
        }
        .detail-item:hover {
            transform: translateY(-3px);
            background: white;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--primary-soft);
        }
        .detail-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .detail-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
        }
        .detail-desc {
            font-size: 0.75rem;
            color: var(--gray);
        }

        /* Progression levée */
        .raise-progress {
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid var(--gray-light);
        }
        .raise-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .raise-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .raise-amount {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }
        .raise-bar {
            height: 12px;
            background: var(--gray-soft);
            border-radius: 99px;
            overflow: hidden;
            position: relative;
        }
        .raise-bar-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            transition: width 1.2s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
        }
        .raise-bar-fill::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.35), transparent);
            animation: shine 2.5s infinite;
        }
        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .raise-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 0.75rem;
            color: var(--gray);
            flex-wrap: wrap;
            gap: 8px;
        }
        .raise-meta b { color: var(--primary); }

        /* Répartition engagements */
        .commit-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        .commit-chip {
            background: var(--gray-soft);
            border-radius: var(--border-radius-sm);
            padding: 14px 16px;
            border: 1px solid transparent;
            transition: var(--transition);
            text-align: center;
        }
        .commit-chip:hover {
            border-color: var(--primary-soft);
            background: white;
            box-shadow: var(--shadow-sm);
            transform: translateY(-3px);
        }
        .commit-chip .c-count {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1.2;
        }
        .commit-chip .c-label {
            font-size: 0.7rem;
            color: var(--gray);
            margin-top: 4px;
            font-weight: 500;
        }
        .commit-chip.highlight {
            background: var(--accent-soft);
            border-color: rgba(212, 175, 55, 0.35);
        }
        .commit-chip.highlight .c-count { color: var(--accent-hover); }

        /* Investisseurs Table */
        .investors-section {
            background: white;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-light);
        }
        .section-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .section-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-add {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            color: white;
            cursor: pointer;
            font-family: var(--font-primary);
            font-size: 0.85rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }
        th {
            background: var(--gray-soft);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray);
        }
        tr:hover {
            background: var(--primary-soft);
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .status-pending {
            background: var(--accent-soft);
            color: var(--accent-hover);
        }
        .status-contacted {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }
        .status-invested {
            background: rgba(59, 130, 246, 0.15);
            color: var(--info);
        }
        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 6px 10px;
            border-radius: 8px;
            transition: var(--transition);
            color: var(--gray);
        }
        .action-btn:hover {
            background: var(--gray-soft);
            color: var(--primary);
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal {
            background: white;
            border-radius: var(--border-radius-md);
            max-width: 550px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalFadeIn 0.3s ease;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header {
            padding: 24px 28px 16px;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: var(--gray);
            transition: var(--transition);
        }
        .modal-close:hover {
            color: var(--danger);
        }
        .modal-body {
            padding: 24px 28px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--dark);
        }
        .form-group label i {
            color: var(--accent);
            margin-right: 6px;
        }
        .form-group label .required {
            color: var(--danger);
            margin-left: 3px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            font-family: var(--font-primary);
            font-size: 0.9rem;
            transition: var(--transition);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-soft);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .modal-footer {
            padding: 16px 28px 24px;
            border-top: 1px solid var(--gray-light);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        .modal-btn {
            padding: 10px 24px;
            border-radius: 40px;
            cursor: pointer;
            font-family: var(--font-primary);
            font-size: 0.85rem;
            font-weight: 500;
            transition: var(--transition);
            border: none;
        }
        .modal-btn-cancel {
            background: var(--gray-soft);
            color: var(--gray);
        }
        .modal-btn-cancel:hover {
            background: var(--gray-light);
        }
        .modal-btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
        }
        .modal-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            color: var(--gray);
        }
        .empty-state-icon {
            font-size: 3.5rem;
            margin-bottom: 16px;
            color: var(--gray-light);
        }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            .dashboard-header { padding: 0 20px; }
            .dashboard-container { padding: 20px; }
            .project-details { grid-template-columns: 1fr; }
            th, td { padding: 12px; font-size: 0.8rem; }
            .user-details { display: none; }
        }
    </style>
</head>
<body>

<div class="dashboard-header">
    <div class="logo">NUFO<span>TEC</span></div>
    <div class="user-info">
        <div class="user-details">
            <div class="user-name"><?= htmlspecialchars($broker->full_name ?? 'Courtier') ?></div>
            <div class="user-firm"><?= htmlspecialchars($broker->firm_name ?? '') ?></div>
        </div>
        <a href="<?= base_url('broker/logout') ?>" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</div>

<div class="dashboard-container">
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?= $stats['total_investors'] ?? 0 ?></div>
            <div class="stat-label">Investisseurs inscrits</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            <div class="stat-value"><?= $stats['total_potential'] ?? '0' ?> M€</div>
            <div class="stat-label">Capacité potentielle</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-phone-alt"></i></div>
            <div class="stat-value"><?= $stats['contacted'] ?? 0 ?></div>
            <div class="stat-label">Contactés</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?= $stats['invested'] ?? 0 ?></div>
            <div class="stat-label">Investis</div>
        </div>
    </div>

    <!-- Projet Section -->
    <div class="project-section">
        <div class="project-header">
            <h2><i class="fas fa-seedling"></i> Projet NUFOTEC - Engrais & Phytopharmaceutiques</h2>
            <p>Production d'engrais chimiques et produits phytopharmaceutiques de haute qualité pour l'agriculture africaine</p>
        </div>
        <div class="project-content">
            <div class="project-details">
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-bullseye"></i> Objectif de levée</div>
                    <div class="detail-value">5 000 000 €</div>
                    <div class="detail-desc">Budget total du projet</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-chart-simple"></i> Rendement attendu</div>
                    <div class="detail-value">15-20% IRR</div>
                    <div class="detail-desc">Sur 5 ans</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Localisation</div>
                    <div class="detail-value">Afrique de l'Ouest</div>
                    <div class="detail-desc">Siège & production</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-flask"></i> Secteur</div>
                    <div class="detail-value">Agrochimie</div>
                    <div class="detail-desc">Engrais & phytosanitaires</div>
                </div>
            </div>

            <!-- Progression de la levée -->
            <?php
                $target_raise = 5000000;
                $raised = 0;
                $commit_buckets = ['Below 250K' => 0, '250K-500K' => 0, '500K-1M' => 0, '1M-2M' => 0, '2M+' => 0];
                foreach ($investors as $inv) {
                    $r = $inv->commitment_range ?? '';
                    if (isset($commit_buckets[$r])) $commit_buckets[$r]++;
                    switch ($r) {
                        case 'Below 250K': $raised += 125000; break;
                        case '250K-500K': $raised += 375000; break;
                        case '500K-1M': $raised += 750000; break;
                        case '1M-2M': $raised += 1500000; break;
                        case '2M+': $raised += 2000000; break;
                    }
                }
                $raise_pct = $target_raise > 0 ? min(100, round($raised / $target_raise * 100, 1)) : 0;
                $investor_count = count($investors);
            ?>
            <div class="raise-progress">
                <div class="raise-head">
                    <div class="raise-title"><i class="fas fa-chart-pie"></i> Progression de la levée</div>
                    <div class="raise-amount"><?= number_format($raised, 0, ',', ' ') ?> € <span style="font-size:0.8rem;color:var(--gray);font-weight:500;">/ 5 000 000 €</span></div>
                </div>
                <div class="raise-bar">
                    <div class="raise-bar-fill" style="width:<?= $raise_pct ?>%" id="raiseBar"></div>
                </div>
                <div class="raise-meta">
                    <span><b><?= $raise_pct ?>%</b> engagé</span>
                    <span><b><?= $investor_count ?></b> investisseurs</span>
                    <span>Objectif restant : <b><?= number_format($target_raise - $raised, 0, ',', ' ') ?> €</b></span>
                </div>
            </div>

            <!-- Répartition par fourchette -->
            <div class="commit-grid">
                <div class="commit-chip <?= $commit_buckets['2M+'] > 0 ? 'highlight' : '' ?>">
                    <div class="c-count"><?= $commit_buckets['2M+'] ?></div>
                    <div class="c-label">2M+ €</div>
                </div>
                <div class="commit-chip <?= $commit_buckets['1M-2M'] > 0 ? 'highlight' : '' ?>">
                    <div class="c-count"><?= $commit_buckets['1M-2M'] ?></div>
                    <div class="c-label">1M - 2M €</div>
                </div>
                <div class="commit-chip <?= $commit_buckets['500K-1M'] > 0 ? 'highlight' : '' ?>">
                    <div class="c-count"><?= $commit_buckets['500K-1M'] ?></div>
                    <div class="c-label">500K - 1M €</div>
                </div>
                <div class="commit-chip <?= $commit_buckets['250K-500K'] > 0 ? 'highlight' : '' ?>">
                    <div class="c-count"><?= $commit_buckets['250K-500K'] ?></div>
                    <div class="c-label">250K - 500K €</div>
                </div>
                <div class="commit-chip <?= $commit_buckets['Below 250K'] > 0 ? 'highlight' : '' ?>">
                    <div class="c-count"><?= $commit_buckets['Below 250K'] ?></div>
                    <div class="c-label">Below 250K €</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Investisseurs Table -->
    <div class="investors-section">
        <div class="section-header">
            <h3><i class="fas fa-user-plus"></i> Mes investisseurs</h3>
            <button class="btn-add" onclick="openAddInvestorModal()">
                <i class="fas fa-plus"></i> Nouvel investisseur
            </button>
        </div>
        <div class="table-container">
            <?php if (!empty($investors)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom complet</th>
                        <th>Organisation</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($investors as $investor): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($investor->full_name) ?></strong></td>
                        <td><?= htmlspecialchars($investor->organization ?? '-') ?></td>
                        <td><?= htmlspecialchars($investor->email) ?></td>
                        <td><?= htmlspecialchars($investor->phone ?? '-') ?></td>
                        <td><?= $investor->commitment_range ?? '-' ?></td>
                        <td>
                            <span class="status-badge status-<?= $investor->status ?>">
                                <i class="fas <?= $investor->status == 'pending' ? 'fa-clock' : ($investor->status == 'contacted' ? 'fa-phone' : 'fa-check') ?>"></i>
                                <?php 
                                    $statusLabels = ['pending' => 'En attente', 'contacted' => 'Contacté', 'invested' => 'Investi'];
                                    echo $statusLabels[$investor->status] ?? $investor->status;
                                ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($investor->created_at)) ?></td>
                        <td>
                            <button class="action-btn" onclick="editInvestor(<?= $investor->id ?>)" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn" onclick="deleteInvestor(<?= $investor->id ?>)" title="Supprimer">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-user-friends"></i></div>
                <p>Aucun investisseur enregistré pour le moment.</p>
                <button class="btn-add" style="margin-top:16px" onclick="openAddInvestorModal()">
                    <i class="fas fa-plus"></i> Ajouter mon premier investisseur
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Ajout/Modification Investisseur -->
<div class="modal-overlay" id="investorModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-user"></i> <span id="modalTitle">Ajouter un investisseur</span></h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form id="investorForm">
            <div class="modal-body">
                <input type="hidden" name="investor_id" id="investor_id">
                <div class="form-group">
                    <label><i class="fas fa-user-circle"></i> Nom complet <span class="required">*</span></label>
                    <input type="text" name="full_name" id="full_name" placeholder="Jean Dupont" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-building"></i> Organisation</label>
                    <input type="text" name="organization" id="organization" placeholder="Nom de l'entreprise">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email <span class="required">*</span></label>
                    <input type="email" name="email" id="email" placeholder="contact@exemple.com" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Téléphone</label>
                    <input type="tel" name="phone" id="phone" placeholder="+257 XX XXX XXX">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-chart-line"></i> Fourchette d'investissement</label>
                    <select name="commitment_range" id="commitment_range">
                        <option value="">— Choisir —</option>
                        <option value="Below 250K">Below 250K €</option>
                        <option value="250K-500K">250K - 500K €</option>
                        <option value="500K-1M">500K - 1M €</option>
                        <option value="1M-2M">1M - 2M €</option>
                        <option value="2M+">2M+ €</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Statut</label>
                    <select name="status" id="status">
                        <option value="pending">🟡 En attente</option>
                        <option value="contacted">🔵 Contacté</option>
                        <option value="invested">🟢 Investi</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-sticky-note"></i> Notes (optionnel)</label>
                    <textarea name="notes" id="notes" placeholder="Informations supplémentaires..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal()">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="submit" class="modal-btn modal-btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const baseUrl = '<?= base_url() ?>';

function openAddInvestorModal() {
    document.getElementById('modalTitle').innerText = 'Ajouter un investisseur';
    document.getElementById('investorForm').reset();
    document.getElementById('investor_id').value = '';
    document.getElementById('investorModal').classList.add('show');
}

function closeModal() {
    document.getElementById('investorModal').classList.remove('show');
}

function editInvestor(id) {
    fetch(`${baseUrl}broker/get_investor/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').innerText = 'Modifier l\'investisseur';
                document.getElementById('investor_id').value = data.investor.id;
                document.getElementById('full_name').value = data.investor.full_name;
                document.getElementById('organization').value = data.investor.organization || '';
                document.getElementById('email').value = data.investor.email;
                document.getElementById('phone').value = data.investor.phone || '';
                document.getElementById('commitment_range').value = data.investor.commitment_range || '';
                document.getElementById('status').value = data.investor.status || 'pending';
                document.getElementById('notes').value = data.investor.notes || '';
                document.getElementById('investorModal').classList.add('show');
            }
        });
}

function deleteInvestor(id) {
    Swal.fire({
        title: 'Confirmer la suppression',
        text: 'Êtes-vous sûr de vouloir supprimer cet investisseur ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<i class="fas fa-trash"></i> Oui, supprimer',
        cancelButtonText: '<i class="fas fa-times"></i> Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${baseUrl}broker/delete_investor/${id}`, { method: 'DELETE' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Supprimé !', 'L\'investisseur a été supprimé.', 'success');
                        location.reload();
                    } else {
                        Swal.fire('Erreur', data.message, 'error');
                    }
                });
        }
    });
}

document.getElementById('investorForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
    
    const formData = new FormData(this);
    const investorId = document.getElementById('investor_id').value;
    const url = investorId ? `${baseUrl}broker/update_investor/${investorId}` : `${baseUrl}broker/add_investor`;
    
    const response = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    
    const result = await response.json();
    
    if (result.success) {
        Swal.fire('Succès !', result.message, 'success').then(() => location.reload());
        closeModal();
    } else {
        Swal.fire('Erreur', result.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

document.getElementById('investorModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
</body>
</html>