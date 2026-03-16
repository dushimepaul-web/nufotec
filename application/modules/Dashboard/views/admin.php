<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include VIEWPATH . 'includes/backend/Header.php';

// Helper pour les badges de statut (inchangé)
function getStatusBadge($statut, $type = 'default') {
    $configs = [
        'commande' => [
            'livree'      => ['bg' => 'success', 'icon' => 'bx-check-circle', 'txt' => 'Livrée'],
            'expediee'    => ['bg' => 'info', 'icon' => 'bx-truck', 'txt' => 'Expédiée'],
            'confirmee'   => ['bg' => 'primary', 'icon' => 'bx-check', 'txt' => 'Confirmée'],
            'annulee'     => ['bg' => 'danger', 'icon' => 'bx-x-circle', 'txt' => 'Annulée'],
            'en_attente'  => ['bg' => 'warning', 'icon' => 'bx-time', 'txt' => 'En attente'],
            'preparation' => ['bg' => 'secondary', 'icon' => 'bx-package', 'txt' => 'Préparation'],
            'remboursee'  => ['bg' => 'dark', 'icon' => 'bx-refresh', 'txt' => 'Remboursée']
        ],
        'consultation' => [
            'en_attente'  => ['bg' => 'warning', 'icon' => 'bx-time', 'txt' => 'En attente'],
            'confirmee'   => ['bg' => 'primary', 'icon' => 'bx-calendar-check', 'txt' => 'Confirmée'],
            'en_cours'    => ['bg' => 'info', 'icon' => 'bx-video', 'txt' => 'En cours'],
            'terminee'    => ['bg' => 'success', 'icon' => 'bx-check-double', 'txt' => 'Terminée'],
            'annulee'     => ['bg' => 'danger', 'icon' => 'bx-x', 'txt' => 'Annulée'],
            'refusee'     => ['bg' => 'secondary', 'icon' => 'bx-block', 'txt' => 'Refusée']
        ],
        'user' => [
            'active'      => ['bg' => 'success', 'icon' => 'bx-check-circle', 'txt' => 'Actif'],
            'inactive'    => ['bg' => 'secondary', 'icon' => 'bx-x-circle', 'txt' => 'Inactif'],
            'verified'    => ['bg' => 'primary', 'icon' => 'bx-badge-check', 'txt' => 'Vérifié'],
            'unverified'  => ['bg' => 'warning', 'icon' => 'bx-time', 'txt' => 'Non vérifié']
        ]
    ];

    $config = $configs[$type][$statut] ?? ['bg' => 'secondary', 'icon' => 'bx-question-mark', 'txt' => ucfirst($statut)];
    
    return "<span class='badge bg-{$config['bg']} bg-opacity-10 text-{$config['bg']} px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1'>
        <i class='bx {$config['icon']}'></i>
        <span class='small'>{$config['txt']}</span>
    </span>";
}

// Helper pour formater les montants
function formatMoney($amount, $currency = 'F') {
    return number_format($amount ?? 0, 0, ',', ' ') . ' ' . $currency;
}

// Helper pour formater les dates relatives
function timeAgo($datetime) {
    if (empty($datetime)) return '-';
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'À l\'instant';
    if ($diff < 3600) return floor($diff / 60) . ' min';
    if ($diff < 86400) return floor($diff / 3600) . ' h';
    if ($diff < 604800) return floor($diff / 86400) . ' j';
    return date('d/m/Y', $time);
}

// Extraction des données du contrôleur (valeurs par défaut)
$global_stats         = $global_stats ?? [];
$facility_stats       = $facility_stats ?? [];
$financial_metrics    = $financial_metrics ?? [];
$visitor_analytics    = $visitor_analytics ?? [];
$ecommerce_stats      = $ecommerce_stats ?? [];
$telemedecine_stats   = $telemedecine_stats ?? [];
$investment_stats     = $investment_stats ?? [];
$charts_data          = $charts_data ?? [];
$recent_activities    = $recent_activities ?? [];
$system_alerts        = $system_alerts ?? [];
$quick_actions        = $quick_actions ?? [];
$latest_users         = $latest_users ?? [];
$latest_orders        = $latest_orders ?? [];
$latest_consultations = $latest_consultations ?? [];
$low_stock_products   = $low_stock_products ?? [];
$pending_verifications = $pending_verifications ?? [];

$page_title = 'Tableau de Bord AGF - Administration';
?>

<style>
:root {
    --agf-primary: #062C54;
    --agf-secondary: #1a8c78;
    --agf-success: #0F766E;
    --agf-warning: #FF8C00;
    --agf-danger: #DC143C;
    --agf-info: #FFD000;
    --agf-light: #f8f9fa;
    --agf-dark: #212529;
}

/* Card Styles */
.stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    border-radius: 16px;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(6, 44, 84, 0.15) !important;
}

.stat-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    transition: all 0.3s ease;
}

.stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
}

/* Gradient Backgrounds */
.bg-gradient-primary {
    background: linear-gradient(135deg, #062C54 0%, #0d4a8c 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #0F766E 0%, #1a8c78 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #FF8C00 0%, #ffa94d 100%);
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #DC143C 0%, #e85d75 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #1a8c78 0%, #20c997 100%);
}

/* Progress Bars */
.progress-thin {
    height: 6px;
    border-radius: 3px;
    background-color: rgba(0,0,0,0.05);
}

.progress-bar-animated {
    position: relative;
    overflow: hidden;
}

.progress-bar-animated::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background: linear-gradient(
        90deg,
        rgba(255,255,255,0) 0%,
        rgba(255,255,255,0.3) 50%,
        rgba(255,255,255,0) 100%
    );
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Activity Timeline */
.activity-timeline {
    position: relative;
    padding-left: 30px;
}

.activity-timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, var(--agf-primary), var(--agf-secondary));
}

.activity-item {
    position: relative;
    padding-bottom: 20px;
}

.activity-item::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--agf-primary);
    border: 3px solid white;
    box-shadow: 0 0 0 3px rgba(6, 44, 84, 0.1);
}

/* Alert Cards */
.alert-card {
    border-left: 4px solid;
    transition: all 0.3s ease;
}

.alert-card:hover {
    transform: translateX(5px);
}

/* Table Styles */
.table-hover-row:hover {
    background-color: rgba(6, 44, 84, 0.02);
    transform: scale(1.01);
    transition: all 0.2s ease;
}

/* Chart Container */
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

/* Quick Action Buttons */
.quick-action-btn {
    transition: all 0.3s ease;
    border-radius: 12px;
    padding: 12px 20px;
}

.quick-action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

/* Facility Stats */
.facility-stat {
    text-align: center;
    padding: 20px;
    border-radius: 12px;
    background: rgba(255,255,255,0.5);
    transition: all 0.3s ease;
}

.facility-stat:hover {
    background: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

/* Real-time indicator */
.pulse-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #0F766E;
    position: relative;
}

.pulse-dot::after {
    content: '';
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    border-radius: 50%;
    border: 2px solid #0F766E;
    animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}

@keyframes pulse-ring {
    0% { transform: scale(0.8); opacity: 1; }
    100% { transform: scale(2); opacity: 0; }
}

/* Custom Scrollbar */
.custom-scroll {
    max-height: 400px;
    overflow-y: auto;
}

.custom-scroll::-webkit-scrollbar {
    width: 6px;
}

.custom-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.custom-scroll::-webkit-scrollbar-thumb {
    background: var(--agf-primary);
    border-radius: 3px;
}
</style>

<div class="wrapper">
    <?php include VIEWPATH . 'includes/backend/Sidebar.php'; ?>

    <div class="main-content">
        <?php 
        $topbar_data = [
            'page_title' => $page_title,
            'username' => $this->session->userdata('username') ?? 'Admin',
            'role' => $this->session->userdata('role_nom') ?? 'Administrateur',
            'prenom' => $this->session->userdata('prenom') ?? ''
        ];
        include VIEWPATH . 'includes/backend/Topheader.php'; 
        ?>

        <div class="page-wrapper px-4 py-4 bg-light">
            
            <!-- Header de bienvenue (simplifié, sans agf_identity) -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <h4 class="mb-1 fw-bold">AGF-PHYTOMED</h4>
                                    <p class="mb-0 opacity-75">Excellence Agro-Industrielle</p>
                                    <div class="d-flex gap-4 mt-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bx bx-map"></i>
                                            <span>Muyinga, Burundi</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bx bx-phone"></i>
                                            <span>+257 79 666 439</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                    <div class="badge bg-white text-primary px-3 py-2 rounded-pill mb-2">
                                        <i class="bx bx-time me-1"></i> <?= date('d/m/Y H:i') ?>
                                    </div>
                                    <div class="small opacity-75">
                                        Dernière mise à jour: <span id="lastUpdate"><?= date('H:i:s') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertes Système -->
            <?php if (!empty($system_alerts)): ?>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div id="alertsCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($system_alerts as $index => $alert): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <div class="alert-card card border-0 shadow-sm bg-white" style="border-left-color: var(--bs-<?= $alert['type'] ?>) !important;">
                                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-<?= $alert['type'] ?> bg-opacity-10 text-<?= $alert['type'] ?> rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                <i class="<?= $alert['icon'] ?> fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($alert['title']) ?></h6>
                                                <p class="mb-0 text-muted small"><?= htmlspecialchars($alert['message']) ?></p>
                                            </div>
                                        </div>
                                        <a href="<?= $alert['link'] ?>" class="btn btn-<?= $alert['type'] ?> btn-sm rounded-pill px-3">
                                            Voir <i class="bx bx-right-arrow-alt ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Stats Cards Principales -->
            <div class="row g-3 mb-4">
                <!-- Utilisateurs -->
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold tracking-wider">Utilisateurs</p>
                                    <h2 class="mb-2 fw-bold text-dark"><?= number_format($global_stats['users']['total'] ?? 0) ?></h2>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bx bx-trending-up"></i> Auj: <?= $global_stats['users']['today'] ?? 0 ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bx bx-group"></i>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted">Actifs: <?= $global_stats['users']['active'] ?? 0 ?></span>
                                    <span class="text-muted">Vérifiés: <?= $global_stats['users']['verified'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commandes -->
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold tracking-wider">Commandes</p>
                                    <h2 class="mb-2 fw-bold text-dark"><?= number_format($global_stats['commandes']['total'] ?? 0) ?></h2>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-warning bg-opacity-10 text-warning">
                                            <i class="bx bx-time"></i> <?= $global_stats['commandes']['pending'] ?? 0 ?> en attente
                                        </span>
                                    </div>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="bx bx-shopping-bag"></i>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted">En prépa: <?= $global_stats['commandes']['processing'] ?? 0 ?></span>
                                    <span class="text-muted">Auj: <?= $global_stats['commandes']['today'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenus -->
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold tracking-wider">Revenus Total</p>
                                    <h2 class="mb-2 fw-bold text-dark"><?= formatMoney($financial_metrics['global']['total_revenue'] ?? 0) ?></h2>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <i class="bx bx-calendar"></i> Ce mois: <?= formatMoney($financial_metrics['orders']['this_month'] ?? 0) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bx bx-money"></i>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted">Panier moy: <?= formatMoney($ecommerce_stats['cart_stats']['average_value'] ?? 0) ?></span>
                                    <span class="text-muted"><?= $financial_metrics['orders']['total_count'] ?? 0 ?> commandes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investissements (Phases) -->
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold tracking-wider">Invest. Planifiés</p>
                                    <h2 class="mb-2 fw-bold text-dark"><?= formatMoney($investment_stats['montant_total'] ?? 0, '$') ?></h2>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <i class="bx bx-layer"></i> <?= $investment_stats['total_phases'] ?? 0 ?> phases
                                        </span>
                                    </div>
                                </div>
                                <div class="stat-icon bg-info bg-opacity-10 text-info">
                                    <i class="bx bx-rocket"></i>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between small">
                                    <?php if (!empty($investment_stats['by_currency'])): ?>
                                        <?php foreach ($investment_stats['by_currency'] as $curr): ?>
                                            <span class="text-muted"><?= $curr['devise'] ?>: <?= number_format($curr['count']) ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section AGF Facility & Real-time Stats -->
            <div class="row g-3 mb-4">
                <!-- AGF Facility Stats -->
                <div class="col-lg-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="bx bx-building-house me-2"></i>AGF Facility
                            </h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary small">Live</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="facility-stat">
                                        <i class="bx bx-area fs-2 text-primary mb-2"></i>
                                        <h5 class="mb-0 fw-bold"><?= $facility_stats['superficie_hectares'] ?? 50 ?> ha</h5>
                                        <small class="text-muted">Superficie</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="facility-stat">
                                        <i class="bx bx-package fs-2 text-success mb-2"></i>
                                        <h5 class="mb-0 fw-bold"><?= number_format($facility_stats['capacite_production'] ?? 10000) ?> T</h5>
                                        <small class="text-muted">Capacité/an</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="facility-stat">
                                        <i class="bx bx-snow fs-2 text-info mb-2"></i>
                                        <h5 class="mb-0 fw-bold"><?= $facility_stats['cold_storage'] ?? 'N/A' ?></h5>
                                        <small class="text-muted">Cold Storage</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="facility-stat">
                                        <i class="bx bx-flask fs-2 text-warning mb-2"></i>
                                        <h5 class="mb-0 fw-bold"><?= $facility_stats['lab_equipment'] ? 'Oui' : 'Non' ?></h5>
                                        <small class="text-muted">Lab. R&D</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-light rounded-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-muted">Investissement requis</span>
                                    <span class="fw-bold"><?= formatMoney($facility_stats['investissement_requis'] ?? 5000000, '$') ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">ROI estimé (5 ans)</span>
                                    <span class="fw-bold text-success"><?= $facility_stats['roi_estime'] ?? 25 ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Real-time Stats -->
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="card bg-gradient-success text-white border-0 shadow-sm">
                                <div class="card-body p-3 text-center">
                                    <div class="pulse-dot mx-auto mb-2"></div>
                                    <h4 class="mb-1 fw-bold" data-realtime="online_users"><?= number_format($global_stats['sessions']['active_now'] ?? 0) ?></h4>
                                    <small class="opacity-75">En ligne</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-gradient-primary text-white border-0 shadow-sm">
                                <div class="card-body p-3 text-center">
                                    <i class="bx bx-show fs-2 mb-2 opacity-75"></i>
                                    <h4 class="mb-1 fw-bold" data-realtime="today_visits"><?= number_format($visitor_analytics['today_visits'] ?? 0) ?></h4>
                                    <small class="opacity-75">Visites aujourd'hui</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-gradient-warning text-white border-0 shadow-sm">
                                <div class="card-body p-3 text-center">
                                    <i class="bx bx-trending-up fs-2 mb-2 opacity-75"></i>
                                    <h4 class="mb-1 fw-bold"><?= $visitor_analytics['trend'] ?? 0 ?>%</h4>
                                    <small class="opacity-75">vs hier</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-gradient-info text-white border-0 shadow-sm">
                                <div class="card-body p-3 text-center">
                                    <i class="bx bx-calendar-check fs-2 mb-2 opacity-75"></i>
                                    <h4 class="mb-1 fw-bold"><?= $telemedecine_stats['today_appointments'] ?? 0 ?></h4>
                                    <small class="opacity-75">Consultations</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mini Chart Revenue -->
                    <div class="card shadow-sm mt-3 border-0">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold">Aperçu des revenus (30 jours)</h6>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary active" onclick="updateChart('revenue', 7)">7J</button>
                                    <button class="btn btn-outline-primary" onclick="updateChart('revenue', 30)">30J</button>
                                    <button class="btn btn-outline-primary" onclick="updateChart('revenue', 90)">90J</button>
                                </div>
                            </div>
                            <div class="chart-container" style="height: 200px;">
                                <canvas id="revenueMiniChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Métriques Détaillées -->
            <div class="row g-3 mb-4">
                <!-- E-commerce Metrics -->
                <div class="col-lg-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="mb-0 fw-bold text-success">
                                <i class="bx bx-store me-2"></i>E-Commerce
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">Produits actifs</span>
                                    <span class="fw-bold"><?= $global_stats['produits']['total'] ?? 0 ?></span>
                                </div>
                                <div class="progress progress-thin">
                                    <div class="progress-bar bg-success progress-bar-animated" style="width: 100%"></div>
                                </div>
                            </div>
                            <div class="row g-2 mt-3">
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded text-center">
                                        <small class="d-block text-muted">Catégories</small>
                                        <span class="fw-bold"><?= $global_stats['produits']['categories'] ?? 0 ?></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded text-center">
                                        <small class="d-block text-muted">Panier moy.</small>
                                        <span class="fw-bold"><?= formatMoney($ecommerce_stats['cart_stats']['average_value'] ?? 0) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Télémédecine Metrics -->
                <div class="col-lg-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="mb-0 fw-bold text-info">
                                <i class="bx bx-health me-2"></i>Télémédecine
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="text-center flex-grow-1 border-end">
                                    <h4 class="mb-0 fw-bold text-info"><?= $telemedecine_stats['pending_appointments'] ?? 0 ?></h4>
                                    <small class="text-muted">En attente</small>
                                </div>
                                <div class="text-center flex-grow-1 border-end">
                                    <h4 class="mb-0 fw-bold text-success"><?= $telemedecine_stats['completed_today'] ?? 0 ?></h4>
                                    <small class="text-muted">Aujourd'hui</small>
                                </div>
                                <div class="text-center flex-grow-1">
                                    <h4 class="mb-0 fw-bold text-primary"><?= formatMoney($telemedecine_stats['revenue_today'] ?? 0) ?></h4>
                                    <small class="text-muted">Revenus</small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6 class="small text-muted mb-2">Par type de consultation</h6>
                                <?php foreach ($telemedecine_stats['by_type'] ?? [] as $type): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small"><i class="bx bx-video me-1"></i><?= ucfirst($type['type'] ?? 'N/A') ?></span>
                                    <span class="badge bg-light text-dark"><?= $type['count'] ?? 0 ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investissements Metrics (phases) -->
                <div class="col-lg-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="bx bx-trending-up me-2"></i>Phases d'Investissement
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <?php foreach ($investment_stats['phases'] ?? [] as $phase): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold"><?= htmlspecialchars($phase['nom_phase'] ?? '') ?></span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary"><?= formatMoney($phase['montant_total'] ?? 0, $phase['devise'] ?? '$') ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="p-3 bg-light rounded-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">Total planifié</span>
                                    <span class="fw-bold text-primary"><?= formatMoney($investment_stats['montant_total'] ?? 0, 'USD') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableaux de données -->
            <div class="row g-4 mb-4">
                <!-- Derniers Utilisateurs -->
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bx bx-user-plus me-2 text-primary"></i>Derniers inscrits
                            </h6>
                            <a href="<?= base_url('Users') ?>" class="btn btn-sm btn-link text-decoration-none">Voir tout</a>
                        </div>
                        <div class="table-responsive custom-scroll">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Utilisateur</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th class="pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($latest_users)): foreach (array_slice($latest_users, 0, 6) as $user): ?>
                                    <tr class="table-hover-row">
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-soft-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                                    <span class="small fw-bold text-primary"><?= strtoupper(substr($user['prenom'] ?: $user['nom'], 0, 1)) ?></span>
                                                </div>
                                                <div>
                                                    <div class="small fw-bold"><?= htmlspecialchars(($user['prenom'] ?: '') . ' ' . ($user['nom'] ?: '')) ?></div>
                                                    <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($user['email'] ?: '') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark small"><?= $user['type_utilisateur'] ?: 'user' ?></span></td>
                                        <td><?= getStatusBadge($user['is_active'] ? 'active' : 'inactive', 'user') ?></td>
                                        <td class="pe-3">
                                            <a href="<?= base_url('Users/edit/'.$user['id']) ?>" class="btn btn-sm btn-light rounded-circle" data-bs-toggle="tooltip" title="Modifier">
                                                <i class="bx bx-edit-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">Aucun utilisateur récent</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Dernières Commandes -->
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bx bx-shopping-bag me-2 text-success"></i>Récentes commandes
                            </h6>
                            <a href="<?= base_url('Commandes') ?>" class="btn btn-sm btn-link text-decoration-none">Toutes</a>
                        </div>
                        <div class="table-responsive custom-scroll">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">N°</th>
                                        <th>Client</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th class="pe-3"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($latest_orders)): foreach (array_slice($latest_orders, 0, 6) as $cmd): ?>
                                    <tr class="table-hover-row">
                                        <td class="ps-3 fw-bold">#<?= str_pad($cmd['id'] ?: 0, 6, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="small"><?= htmlspecialchars(($cmd['prenom'] ?: '') . ' ' . ($cmd['nom'] ?: '')) ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;"><?= $cmd['nb_items'] ?: 0 ?> article(s)</div>
                                        </td>
                                        <td class="fw-bold"><?= formatMoney($cmd['total_ttc'] ?: 0) ?></td>
                                        <td><?= getStatusBadge($cmd['statut'] ?: 'en_attente', 'commande') ?></td>
                                        <td class="pe-3">
                                            <a href="<?= base_url('Commandes/details/'.$cmd['id']) ?>" class="btn btn-sm btn-light rounded-circle">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">Aucune commande récente</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deuxième ligne de tableaux -->
            <div class="row g-4 mb-4">
                <!-- Consultations -->
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bx bx-calendar-check me-2 text-info"></i>Dernières consultations
                            </h6>
                            <a href="<?= base_url('Consultations') ?>" class="btn btn-sm btn-link text-decoration-none">Tout</a>
                        </div>
                        <div class="list-group list-group-flush custom-scroll">
                            <?php if (!empty($latest_consultations)): foreach (array_slice($latest_consultations, 0, 5) as $cons): ?>
                            <div class="list-group-item px-3 py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small fw-bold"><?= htmlspecialchars(($cons['patient_prenom'] ?: '') . ' ' . ($cons['patient_nom'] ?: '')) ?></div>
                                        <div class="text-muted small">Dr. <?= htmlspecialchars(($cons['medecin_prenom'] ?: '') . ' ' . ($cons['medecin_nom'] ?: '')) ?></div>
                                        <div class="text-info small"><i class="bx bx-time me-1"></i><?= timeAgo($cons['date_souhaitee']) ?></div>
                                    </div>
                                    <?= getStatusBadge($cons['statut'] ?: 'en_attente', 'consultation') ?>
                                </div>
                            </div>
                            <?php endforeach; else: ?>
                            <div class="list-group-item text-center py-4 text-muted">Aucune consultation récente</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Stock Faible (vide) & Activité récente -->
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bx bx-history me-2 text-secondary"></i>Activité récente
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="activity-timeline custom-scroll p-3">
                                <?php if (!empty($recent_activities)): foreach (array_slice($recent_activities, 0, 5) as $activity): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="small fw-bold"><?= htmlspecialchars(($activity['prenom'] ?: '') . ' ' . ($activity['nom'] ?: '')) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($activity['description'] ?: $activity['action'] ?: '') ?></div>
                                            <div class="text-primary small"><?= timeAgo($activity['created_at']) ?></div>
                                        </div>
                                        <span class="badge bg-light text-dark small"><?= $activity['module'] ?: 'system' ?></span>
                                    </div>
                                </div>
                                <?php endforeach; else: ?>
                                <div class="text-center py-4 text-muted">Aucune activité récente</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Rapides -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body p-4">
                            <h6 class="mb-3 fw-bold">
                                <i class="bx bx-zap me-2 text-warning"></i>Actions rapides
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($quick_actions ?: [] as $action): ?>
                                <a href="<?= $action['link'] ?>" class="quick-action-btn btn btn-<?= $action['color'] ?> btn-sm shadow-sm">
                                    <i class="<?= $action['icon'] ?> me-2"></i><?= $action['title'] ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques Détaillés -->
            <div class="row g-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bx bx-chart me-2 text-primary"></i>Analyses détaillées
                            </h6>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary active" onclick="switchChart('revenue')">Revenus</button>
                                <button class="btn btn-outline-primary" onclick="switchChart('users')">Utilisateurs</button>
                                <button class="btn btn-outline-primary" onclick="switchChart('orders')">Commandes</button>
                                <button class="btn btn-outline-primary" onclick="switchChart('consultations')">Consultations</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-lg-8">
                                    <div class="chart-container" style="height: 350px;">
                                        <canvas id="mainChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <h6 class="small text-muted mb-3">RÉPARTITION PAR RÔLE</h6>
                                    <div class="chart-container" style="height: 200px;">
                                        <canvas id="rolesChart"></canvas>
                                    </div>
                                    <h6 class="small text-muted mb-3 mt-4">STATUTS COMMANDES</h6>
                                    <div class="chart-container" style="height: 150px;">
                                        <canvas id="orderStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Configuration Chart.js par défaut
Chart.defaults.font.family = "'Segoe UI', 'Helvetica Neue', 'Arial', sans-serif";
Chart.defaults.color = '#6c757d';

// Données PHP vers JS
const chartsData = <?= json_encode($charts_data) ?>;
const themeColors = {
    primary: '#062C54',
    secondary: '#1a8c78',
    success: '#0F766E',
    warning: '#FF8C00',
    danger: '#DC143C',
    info: '#FFD000'
};

// Mini Chart Revenus
const revenueMiniCtx = document.getElementById('revenueMiniChart').getContext('2d');
const revenueMiniChart = new Chart(revenueMiniCtx, {
    type: 'line',
    data: {
        labels: chartsData.revenue?.labels?.slice(-7) || [],
        datasets: [{
            label: 'Revenus',
            data: chartsData.revenue?.data?.slice(-7) || [],
            borderColor: themeColors.primary,
            backgroundColor: 'rgba(6, 44, 84, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointRadius: 0,
            pointHoverRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { display: false },
            y: { 
                display: true,
                ticks: {
                    callback: function(value) {
                        return value >= 1000000 ? (value/1000000).toFixed(1) + 'M' : 
                               value >= 1000 ? (value/1000).toFixed(0) + 'k' : value;
                    },
                    font: { size: 10 }
                }
            }
        }
    }
});

// Main Chart
let currentChart = 'revenue';
const mainCtx = document.getElementById('mainChart').getContext('2d');
let mainChart = new Chart(mainCtx, {
    type: 'line',
    data: {
        labels: chartsData.revenue?.labels || [],
        datasets: [{
            label: 'Revenus (F)',
            data: chartsData.revenue?.data || [],
            borderColor: themeColors.primary,
            backgroundColor: 'rgba(6, 44, 84, 0.05)',
            borderWidth: 3,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'top',
                align: 'end',
                labels: { usePointStyle: true, boxWidth: 8 }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return formatMoney(value);
                    }
                }
            }
        }
    }
});

// Roles Doughnut Chart
const rolesCtx = document.getElementById('rolesChart').getContext('2d');
new Chart(rolesCtx, {
    type: 'doughnut',
    data: {
        labels: chartsData.roles_distribution?.map(r => r.role) || [],
        datasets: [{
            data: chartsData.roles_distribution?.map(r => r.count) || [],
            backgroundColor: [
                themeColors.primary,
                themeColors.secondary,
                themeColors.success,
                themeColors.warning,
                themeColors.info,
                themeColors.danger
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
        }
    }
});

// Order Status Pie Chart
const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
new Chart(orderStatusCtx, {
    type: 'pie',
    data: {
        labels: chartsData.order_status?.map(s => s.statut) || [],
        datasets: [{
            data: chartsData.order_status?.map(s => s.count) || [],
            backgroundColor: [
                themeColors.warning,
                themeColors.primary,
                themeColors.success,
                themeColors.info,
                themeColors.danger
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } }
        }
    }
});

// Fonctions de mise à jour
function updateChart(type, period) {
    fetch(`<?= base_url('Dashboard/api_stats') ?>?type=charts&period=${period}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                revenueMiniChart.data.labels = data.data.labels.slice(-period);
                revenueMiniChart.data.datasets[0].data = data.data.values;
                revenueMiniChart.update();
            }
        });
}

function switchChart(type) {
    currentChart = type;
    const dataMap = {
        'revenue': { data: chartsData.revenue, label: 'Revenus (F)', color: themeColors.primary },
        'users': { data: chartsData.users, label: 'Nouveaux utilisateurs', color: themeColors.success },
        'orders': { data: chartsData.orders, label: 'Commandes', color: themeColors.warning },
        'consultations': { data: chartsData.consultations, label: 'Consultations', color: themeColors.info }
    };
    
    const config = dataMap[type];
    mainChart.data.datasets[0].data = config.data?.data || [];
    mainChart.data.datasets[0].label = config.label;
    mainChart.data.datasets[0].borderColor = config.color;
    mainChart.data.datasets[0].backgroundColor = hexToRgba(config.color, 0.05);
    mainChart.update();
    
    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
        if(btn.textContent.toLowerCase().includes(type)) btn.classList.add('active');
    });
}

function hexToRgba(hex, alpha) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function formatMoney(value) {
    return value >= 1000000 ? (value/1000000).toFixed(1) + 'M F' : 
           value >= 1000 ? (value/1000).toFixed(0) + 'k F' : value + ' F';
}

// Real-time updates
setInterval(() => {
    fetch('<?= base_url('Dashboard/api_stats') ?>?type=realtime')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('[data-realtime]').forEach(el => {
                    const key = el.getAttribute('data-realtime');
                    if (data.data[key] !== undefined) {
                        el.textContent = data.data[key].toLocaleString();
                    }
                });
                document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
            }
        });
}, 60000);

// Tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
});
</script>

<?php include VIEWPATH . 'includes/backend/Footer.php'; ?>