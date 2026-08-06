<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include VIEWPATH . 'includes/backend/Header.php';
include VIEWPATH . 'includes/backend/Sidebar.php';
include VIEWPATH . 'includes/backend/Topheader.php';

function agfTimeAgo($datetime) {
    if (empty($datetime)) return '—';
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'À l\'instant';
    if ($diff < 3600) return floor($diff / 60) . ' min';
    if ($diff < 86400) return floor($diff / 3600) . ' h';
    if ($diff < 604800) return floor($diff / 86400) . ' j';
    return date('d/m/Y', strtotime($datetime));
}

function agfMoney($v, $cur = '$') {
    return $cur . ' ' . number_format((float)$v, 0, '.', ',');
}

$investor_stats = $investor_stats ?? [];
$phases         = $phases ?? ['phases' => [], 'total' => 0, 'count' => 0];
$latest         = $latest ?? [];
$page_title     = $page_title ?? 'Espace Investisseurs';

$admin_name = trim(($this->session->userdata('prenom') ?? '') . ' ' . ($this->session->userdata('nom') ?? ''));
$interests_pie = [
    ['label' => 'Equity', 'val' => $investor_stats['interests']['equity'] ?? 0, 'color' => '#0F766E'],
    ['label' => 'Dette', 'val' => $investor_stats['interests']['debt'] ?? 0, 'color' => '#FF8C00'],
    ['label' => 'Finance mixte', 'val' => $investor_stats['interests']['blended_finance'] ?? 0, 'color' => '#062C54'],
    ['label' => 'Grant', 'val' => $investor_stats['interests']['grant'] ?? 0, 'color' => '#38BDF8'],
    ['label' => 'Partenariat strat.', 'val' => $investor_stats['interests']['strategic'] ?? 0, 'color' => '#8B5CF6'],
    ['label' => 'Collab. technique', 'val' => $investor_stats['interests']['technical'] ?? 0, 'color' => '#F472B6'],
];
$commit_labels = [
    'Below 250K' => '< 250K €',
    '250K-500K' => '250K–500K €',
    '500K-1M' => '500K–1M €',
    '1M-2M' => '1M–2M €',
    '2M+' => '2M+ €',
];
?>

<div class="page-wrapper">
<div class="page-content">

    <!-- En-tête -->
    <div class="card mb-4" style="background: linear-gradient(135deg, #062C54 0%, #0F766E 100%);">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h4 class="mb-1 fw-bold text-white">Espace Investisseurs & Opportunités</h4>
                    <p class="mb-0 text-white-50">Suivi des levées NUFOTEC Phytomed et des investisseurs enregistrés · <?= date('d/m/Y') ?></p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <div class="badge text-bg-light px-3 py-2 rounded-pill fw-semibold">
                        <i class="bx bx-line-chart me-1"></i> <?= $phases['count'] ?> phases · <?= agfMoney($phases['total']) ?> USD
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-outline card-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-success bg-success-subtle me-3" style="width:54px;height:54px;font-size:23px;"><i class="bx bx-group"></i></div>
                        <div><div class="h4 mb-0 fw-bold"><?= number_format($investor_stats['total'] ?? 0) ?></div><small class="text-secondary">Investisseurs inscrits</small></div>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary border-top pt-2">
                        <span>Ce mois</span><b class="text-success">+<?= $investor_stats['this_month'] ?? 0 ?></b>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-outline card-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-warning bg-warning-subtle me-3" style="width:54px;height:54px;font-size:23px;"><i class="bx bx-wallet"></i></div>
                        <div><div class="h4 mb-0 fw-bold"><?= agfMoney($phases['total']) ?></div><small class="text-secondary">Levé planifiée (USD)</small></div>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary border-top pt-2">
                        <span>Devise</span><b><?= $phases['phases'][0]['devise'] ?? 'USD' ?></b>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-outline card-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-info bg-info-subtle me-3" style="width:54px;height:54px;font-size:23px;"><i class="bx bx-trending-up"></i></div>
                        <div><div class="h4 mb-0 fw-bold"><?= $investor_stats['interests']['equity'] ?? 0 ?></div><small class="text-secondary">Intérêt Equity</small></div>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary border-top pt-2">
                        <span>Finance mixte</span><b><?= $investor_stats['interests']['blended_finance'] ?? 0 ?></b>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-outline card-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-primary bg-primary-subtle me-3" style="width:54px;height:54px;font-size:23px;"><i class="bx bx-handshake"></i></div>
                        <div><div class="h4 mb-0 fw-bold"><?= $investor_stats['interests']['strategic'] ?? 0 ?></div><small class="text-secondary">Partenariats stratégiques</small></div>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary border-top pt-2">
                        <span>Collab. technique</span><b><?= $investor_stats['interests']['technical'] ?? 0 ?></b>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Phases + Répartition -->
    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold text-primary"><i class="bx bx-git-branch me-2"></i>Phases d'investissement</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($phases['phases'])): ?>
                        <?php
                        $max_inv = max(array_column($phases['phases'], 'montant_total') ?: [1]);
                        $phase_colors = ['#0F766E', '#d4af37', '#FF8C00', '#38BDF8'];
                        foreach ($phases['phases'] as $idx => $ph):
                            $pct = $max_inv > 0 ? round($ph['montant_total'] / $max_inv * 100) : 0;
                            $clr = $phase_colors[$idx % count($phase_colors)];
                        ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold small"><?= htmlspecialchars($ph['nom_phase']) ?></span>
                                <span class="small fw-bold" style="color:<?= $clr ?>;"><?= agfMoney($ph['montant_total']) ?> <?= htmlspecialchars($ph['devise'] ?? 'USD') ?></span>
                            </div>
                            <?php if (!empty($ph['description'])): ?>
                                <div class="small text-secondary mb-2"><?= htmlspecialchars($ph['description']) ?></div>
                            <?php endif; ?>
                            <div class="d-flex gap-2 mb-2 flex-wrap">
                                <?php if (!empty($ph['annee_debut'])): ?>
                                    <span class="badge text-bg-light border" style="font-size:.72rem;"><?= $ph['annee_debut'] ?>–<?= $ph['annee_fin'] ?></span>
                                <?php endif; ?>
                                <?php
                                $alloc = json_decode($ph['allocation_details'] ?? '', true);
                                if (!empty($alloc)):
                                    $top_alloc = array_slice($alloc, 0, 3, true);
                                    foreach ($top_alloc as $k => $v): ?>
                                        <span class="badge text-bg-light border" style="font-size:.72rem;"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) ?> <?= $v ?>%</span>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $clr ?>;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-secondary text-center py-4 mb-0 small">Aucune phase configurée.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Répartition des intérêts -->
        <div class="col-xl-5">
            <div class="card card-outline card-info h-100">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold text-primary"><i class="bx bx-pie-chart-alt-2 me-2"></i>Intérêts des investisseurs</h6>
                </div>
                <div class="card-body">
                    <div class="ch-box" style="height:230px;">
                        <canvas id="interestChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <?php foreach ($interests_pie as $it): ?>
                            <div class="d-flex justify-content-between align-items-center py-1 small border-bottom">
                                <span><span class="d-inline-block rounded-circle me-2" style="width:10px;height:10px;background:<?= $it['color'] ?>;"></span><?= $it['label'] ?></span>
                                <b class="mono"><?= $it['val'] ?></b>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table investisseurs -->
    <div class="card card-outline card-success">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 fw-bold text-primary"><i class="bx bx-user-plus me-2"></i>Derniers investisseurs enregistrés</h6>
            <a href="<?= base_url('Investors') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Voir tout <i class="bx bx-right-arrow-alt ms-1"></i></a>
        </div>
        <div class="table-responsive" style="max-height:420px;overflow-y:auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase small text-secondary">Investisseur</th>
                        <th class="text-uppercase small text-secondary">Organisation</th>
                        <th class="text-uppercase small text-secondary">Pays</th>
                        <th class="text-uppercase small text-secondary">Engagement</th>
                        <th class="text-uppercase small text-secondary">Timeline</th>
                        <th class="text-uppercase small text-secondary">Inscription</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($latest)): ?>
                        <?php foreach ($latest as $inv): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($inv['full_name'] ?? '') ?></div>
                                    <div class="small text-secondary"><?= htmlspecialchars($inv['email'] ?? '') ?></div>
                                </td>
                                <td><?= htmlspecialchars($inv['organization'] ?? '—') ?></td>
                                <td><span class="badge text-bg-info"><?= htmlspecialchars($inv['country_name'] ?? '—') ?></span></td>
                                <td><span class="badge text-bg-success"><?= $commit_labels[$inv['commitment_range'] ?? ''] ?? ($inv['commitment_range'] ?? '—') ?></span></td>
                                <td class="text-secondary small"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $inv['timeline'] ?? '—'))) ?></td>
                                <td class="small text-secondary"><?= agfTimeAgo($inv['created_at'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-secondary py-4">Aucun investisseur enregistré pour le moment.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /.page-content -->


<style>
.ch-box { position: relative; width: 100%; }
.mono { font-family: 'IBM Plex Mono', Consolas, monospace; }
</style>

<script src="<?= base_url() ?>assets/backend/plugins/chartjs/js/chart.js"></script>
<script>
const interestData = {
    labels: <?= json_encode(array_column($interests_pie, 'label')) ?>,
    values: <?= json_encode(array_column($interests_pie, 'val')) ?>,
    colors: <?= json_encode(array_column($interests_pie, 'color')) ?>
};

const ctx3 = document.getElementById('interestChart').getContext('2d');
new Chart(ctx3, {
    type: 'doughnut',
    data: {
        labels: interestData.labels,
        datasets: [{
            data: interestData.values,
            backgroundColor: interestData.colors,
            borderWidth: 0
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '62%',
        plugins: { legend: { display: false } }
    }
});
</script>

<?php include VIEWPATH . 'includes/backend/Footer.php'; ?>