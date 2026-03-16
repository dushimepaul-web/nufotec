<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include VIEWPATH . 'includes/backend/Header.php';

// Helpers (copiés depuis l'admin pour autonomie)
function getStatusBadge($statut, $type = 'consultation') {
    $configs = [
        'consultation' => [
            'en_attente'  => ['bg' => 'warning', 'icon' => 'bx-time', 'txt' => 'En attente'],
            'confirmee'   => ['bg' => 'primary', 'icon' => 'bx-calendar-check', 'txt' => 'Confirmée'],
            'en_cours'    => ['bg' => 'info', 'icon' => 'bx-video', 'txt' => 'En cours'],
            'terminee'    => ['bg' => 'success', 'icon' => 'bx-check-double', 'txt' => 'Terminée'],
            'annulee'     => ['bg' => 'danger', 'icon' => 'bx-x', 'txt' => 'Annulée'],
            'refusee'     => ['bg' => 'secondary', 'icon' => 'bx-block', 'txt' => 'Refusée']
        ]
    ];
    $config = $configs[$type][$statut] ?? ['bg' => 'secondary', 'icon' => 'bx-question-mark', 'txt' => ucfirst($statut)];
    
    return "<span class='badge bg-{$config['bg']} bg-opacity-10 text-{$config['bg']} px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1'>
        <i class='bx {$config['icon']}'></i>
        <span class='small'>{$config['txt']}</span>
    </span>";
}

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

// Données transmises par le contrôleur
$stats   = $stats ?? [];
$upcoming = $upcoming ?? [];
$medecin_id = $medecin_id ?? 0;
$page_title = 'Mon Espace Médecin';

// Récupérer les infos du médecin depuis la session
$doctor_name = trim(($this->session->userdata('prenom') ?? '') . ' ' . ($this->session->userdata('nom') ?? ''));
$doctor_photo = $this->session->userdata('photo') ?? 'default-avatar.png';
?>

<style>
:root {
    --agf-primary: #062C54;
    --agf-secondary: #1a8c78;
    --agf-success: #0F766E;
    --agf-warning: #FF8C00;
    --agf-danger: #DC143C;
    --agf-info: #FFD000;
}

.stat-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 16px;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(6, 44, 84, 0.1) !important;
}
.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #062C54 0%, #0d4a8c 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #0F766E 0%, #1a8c78 100%);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #FF8C00 0%, #ffa94d 100%);
}
.bg-gradient-info {
    background: linear-gradient(135deg, #1a8c78 0%, #20c997 100%);
}
.custom-scroll {
    max-height: 400px;
    overflow-y: auto;
}
.custom-scroll::-webkit-scrollbar {
    width: 4px;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: var(--agf-primary);
    border-radius: 2px;
}
.quick-action-btn {
    transition: all 0.3s ease;
    border-radius: 10px;
    padding: 10px 16px;
}
.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

<div class="wrapper">
    <?php include VIEWPATH . 'includes/backend/Sidebar.php'; ?>

    <div class="main-content">
        <?php 
        $topbar_data = [
            'page_title' => $page_title,
            'username' => $doctor_name ?: 'Dr.',
            'role' => 'Médecin',
            'prenom' => $this->session->userdata('prenom') ?? ''
        ];
        include VIEWPATH . 'includes/backend/Topheader.php'; 
        ?>

        <div class="page-wrapper px-4 py-4 bg-light">

            <!-- En-tête de bienvenue -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <h4 class="mb-1 fw-bold">Bonjour, Dr. <?= htmlspecialchars($doctor_name) ?></h4>
                                    <p class="mb-0 opacity-75">Gérez vos consultations et suivez votre activité</p>
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

            <!-- Statistiques principales -->
            <div class="row g-3 mb-4">
                <!-- Consultations aujourd'hui -->
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold">Aujourd'hui</p>
                                    <h2 class="mb-2 fw-bold text-dark"><?= number_format($stats['today_appointments'] ?? 0) ?></h2>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="bx bx-calendar"></i> Consultations
                                    </span>
                                </div>
                                <div class="stat-icon bg-info bg-opacity-10 text-info">
                                    <i class="bx bx-calendar-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- En attente -->
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold">En attente</p>
                                    <h2 class="mb-2 fw-bold text-dark"><?= number_format($stats['pending_appointments'] ?? 0) ?></h2>
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        <i class="bx bx-time"></i> À confirmer
                                    </span>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bx bx-hourglass"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Total patients (consultations) -->
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold">Total patients</p>
                                    <h2 class="mb-2 fw-bold text-dark"><?= number_format($stats['total_patients'] ?? 0) ?></h2>
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="bx bx-group"></i> Consultations
                                    </span>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="bx bx-user"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Note moyenne -->
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold">Note moyenne</p>
                                    <h2 class="mb-2 fw-bold text-dark"><?= number_format($stats['rating'] ?? 0, 1) ?> / 5</h2>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="bx bx-star"></i> Évaluation
                                    </span>
                                </div>
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bx bx-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deux colonnes : Consultations à venir + Activité récente -->
            <div class="row g-4 mb-4">
                <!-- Consultations à venir -->
                <div class="col-lg-8">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bx bx-calendar-event me-2 text-primary"></i>Consultations à venir
                            </h6>
                            <a href="<?= base_url('Consultations/medecin') ?>" class="btn btn-sm btn-link text-decoration-none">Voir tout</a>
                        </div>
                        <div class="table-responsive custom-scroll">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Patient</th>
                                        <th>Date/Heure</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th class="pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($upcoming)): foreach ($upcoming as $c): ?>
                                    <tr class="table-hover-row">
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-soft-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                                    <span class="small fw-bold text-primary"><?= strtoupper(substr($c['patient_prenom'] ?? $c['prenom'] ?? 'P', 0, 1)) ?></span>
                                                </div>
                                                <div>
                                                    <div class="small fw-bold"><?= htmlspecialchars(($c['patient_prenom'] ?? '') . ' ' . ($c['patient_nom'] ?? '')) ?></div>
                                                    <div class="text-muted small"><?= htmlspecialchars($c['patient_telephone'] ?? $c['telephone'] ?? '') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $date = new DateTime($c['date_souhaitee'] ?? $c['created_at']);
                                            echo $date->format('d/m/Y H:i');
                                            ?>
                                        </td>
                                        <td><span class="badge bg-light text-dark"><?= ucfirst($c['type'] ?? 'consultation') ?></span></td>
                                        <td><?= getStatusBadge($c['statut'] ?? 'en_attente') ?></td>
                                        <td class="pe-3">
                                            <a href="<?= base_url('Consultations/details/'.$c['id']) ?>" class="btn btn-sm btn-light rounded-circle" data-bs-toggle="tooltip" title="Voir">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <?php if (($c['statut'] ?? '') === 'en_attente'): ?>
                                            <a href="<?= base_url('Consultations/confirmer/'.$c['id']) ?>" class="btn btn-sm btn-success rounded-circle" data-bs-toggle="tooltip" title="Confirmer">
                                                <i class="bx bx-check"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">Aucune consultation à venir</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Activité récente / Historique rapide -->
                <div class="col-lg-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="bx bx-history me-2 text-secondary"></i>Dernières consultations
                            </h6>
                        </div>
                        <div class="list-group list-group-flush custom-scroll">
                            <?php 
                            // On peut récupérer un historique via une méthode du contrôleur, mais pour l'exemple on prend les 5 dernières terminées
                            // Si le contrôleur ne fournit pas d'historique, on peut le laisser vide ou le commenter.
                            // Ici on suppose que le contrôleur a passé $recent_history, mais on peut aussi l'ignorer.
                            // Pour l'exemple, on affiche les 5 dernières de $upcoming triées par date descendante.
                            $history = array_slice(array_reverse($upcoming), 0, 5);
                            ?>
                            <?php if (!empty($history)): foreach ($history as $c): ?>
                            <div class="list-group-item px-3 py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small fw-bold"><?= htmlspecialchars(($c['patient_prenom'] ?? '') . ' ' . ($c['patient_nom'] ?? '')) ?></div>
                                        <div class="text-muted small"><?= timeAgo($c['date_souhaitee'] ?? $c['created_at']) ?></div>
                                    </div>
                                    <?= getStatusBadge($c['statut'] ?? 'terminee') ?>
                                </div>
                            </div>
                            <?php endforeach; else: ?>
                            <div class="list-group-item text-center py-4 text-muted">Aucune activité récente</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body p-4">
                            <h6 class="mb-3 fw-bold">
                                <i class="bx bx-zap me-2 text-warning"></i>Actions rapides
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="<?= base_url('Consultations/Entente') ?>" class="quick-action-btn btn btn-primary btn-sm shadow-sm">
                                    <i class="bx bx-calendar-plus me-2"></i>Nouvelle consultation
                                </a>
                                <a href="<?= base_url('Consultations') ?>" class="quick-action-btn btn btn-success btn-sm shadow-sm">
                                    <i class="bx bx-user me-2"></i>Liste des patients
                                </a>
                                <a href="<?= base_url('Consultations/Entente/confirme') ?>" class="quick-action-btn btn btn-info btn-sm shadow-sm">
                                    <i class="bx bx-list-ul me-2"></i>Mes consultations
                                </a>
                                <a href="<?= base_url('Calendrier') ?>" class="quick-action-btn btn btn-warning btn-sm shadow-sm">
                                    <i class="bx bx-calendar me-2"></i>Calendrier
                                </a>
                                <a href="<?= base_url('Profil/medecin') ?>" class="quick-action-btn btn btn-secondary btn-sm shadow-sm">
                                    <i class="bx bx-cog me-2"></i>Mon profil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Petit graphique (optionnel) - on peut afficher la répartition des consultations par statut -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="bx bx-pie-chart-alt me-2 text-primary"></i>Répartition des consultations
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="consultationChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="bx bx-line-chart me-2 text-success"></i>Évolution hebdomadaire
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="weeklyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Données pour les graphiques (simulées, mais vous pouvez les passer depuis le contrôleur)
const consultationStats = {
    labels: ['En attente', 'Confirmées', 'Terminées', 'Annulées'],
    data: [
        <?= $stats['pending_appointments'] ?? 0 ?>,
        <?= ($stats['today_appointments'] ?? 0) + rand(1,5) ?>, // exemple
        <?= ($stats['total_patients'] ?? 0) - ($stats['pending_appointments'] ?? 0) - 2 ?>,
        2
    ]
};

const weeklyData = {
    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
    data: [3, 5, 2, 6, 4, 1, 2] // à remplacer par des vraies stats
};

// Pie chart
const ctx1 = document.getElementById('consultationChart').getContext('2d');
new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: consultationStats.labels,
        datasets: [{
            data: consultationStats.data,
            backgroundColor: ['#FF8C00', '#0F766E', '#1a8c78', '#DC143C'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 12 } } }
        }
    }
});

// Line chart
const ctx2 = document.getElementById('weeklyChart').getContext('2d');
new Chart(ctx2, {
    type: 'line',
    data: {
        labels: weeklyData.labels,
        datasets: [{
            label: 'Consultations',
            data: weeklyData.data,
            borderColor: '#062C54',
            backgroundColor: 'rgba(6, 44, 84, 0.05)',
            borderWidth: 3,
            tension: 0.3,
            fill: true,
            pointBackgroundColor: '#062C54'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});

// Mise à jour de l'heure
document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
setInterval(() => {
    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
}, 60000);

// Tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
});
</script>

<?php include VIEWPATH . 'includes/backend/Footer.php'; ?>