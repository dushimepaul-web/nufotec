<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include VIEWPATH . 'includes/backend/Header.php';
include VIEWPATH . 'includes/backend/Sidebar.php';
include VIEWPATH . 'includes/backend/Topheader.php';

function userTimeAgo($datetime) {
    if (empty($datetime)) return '—';
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'À l\'instant';
    if ($diff < 3600) return floor($diff / 60) . ' min';
    if ($diff < 86400) return floor($diff / 3600) . ' h';
    if ($diff < 604800) return floor($diff / 86400) . ' j';
    return date('d/m/Y', strtotime($datetime));
}

function userStatBadge($statut) {
    $map = [
        'en_attente' => ['warning', 'bx-time', 'En attente'],
        'confirmee'  => ['info', 'bx-calendar-check', 'Confirmée'],
        'en_cours'   => ['primary', 'bx-video', 'En cours'],
        'terminee'   => ['success', 'bx-check-double', 'Terminée'],
        'annulee'    => ['danger', 'bx-x', 'Annulée'],
        'refusee'    => ['secondary', 'bx-block', 'Refusée'],
    ];
    $c = $map[$statut] ?? ['secondary', 'bx-question-mark', ucfirst($statut)];
    return "<span class='badge text-bg-{$c[0]} rounded-pill d-inline-flex align-items-center gap-1'><i class='bx {$c[1]}'></i><span class='small'>{$c[2]}</span></span>";
}

function userMoney($v) {
    return number_format((float)$v, 0, '.', ',') . ' €';
}

$user             = $user ?? new stdClass();
$investor         = $investor ?? null;
$broker           = $broker ?? null;
$stats            = $stats ?? [];
$consultations    = $consultations ?? [];
$broker_investors = $broker_investors ?? [];
$phases           = $phases ?? ['phases' => [], 'total' => 0, 'count' => 0];

$user_full_name = trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')) ?: 'Utilisateur';
$user_photo = $user->photo ?? 'default-avatar.png';
$is_investor = !empty($investor);
$is_broker = !empty($broker);
$commit_labels = [
    'Below 250K' => '< 250K €', '250K-500K' => '250K–500K €', '500K-1M' => '500K–1M €',
    '1M-2M' => '1M–2M €', '2M+' => '2M+ €',
];
?>

<div class="page-wrapper">
<div class="page-content">

    <!-- En-tête de bienvenue -->
    <div class="card mb-4" style="background: linear-gradient(135deg, #062C54 0%, #0F766E 100%);">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-7 d-flex align-items-center gap-3">
                    <img src="<?= base_url(!empty($user->photo) ? 'uploads/users/' . $user_photo : 'assets/frontend/img/default-avatar.jpg') ?>"
                         class="rounded-circle" style="width:64px;height:64px;object-fit:cover;border:3px solid #d4af37;" alt=""
                         onerror="this.style.display='none'">
                    <div>
                        <h4 class="mb-1 fw-bold text-white">Bonjour, <?= htmlspecialchars($user_full_name) ?></h4>
                        <p class="mb-0 text-white-50 small">Bienvenue sur votre espace NUFOTEC</p>
                        <div class="d-flex gap-2 mt-2 flex-wrap">
                            <span class="badge text-bg-light"><i class="bx bx-user"></i> Patient</span>
                            <?php if ($is_investor): ?>
                                <span class="badge text-bg-warning"><i class="bx bx-coin-stack"></i> Investisseur</span>
                            <?php endif; ?>
                            <?php if ($is_broker): ?>
                                <span class="badge text-bg-info"><i class="bx bx-handshake"></i> Courtier</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                    <div class="badge text-bg-light px-3 py-2 rounded-pill fw-semibold">
                        <i class="bx bx-calendar me-1"></i> <?= date('l d F Y') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════ ESPACE PATIENT (par défaut) ═══════════ -->
    <h6 class="text-uppercase small fw-bold text-secondary mb-3"><i class="bx bx-plus-medical me-1"></i> Mon espace santé</h6>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-outline card-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-success bg-success-subtle me-3" style="width:54px;height:54px;font-size:23px;"><i class="bx bx-plus-medical"></i></div>
                        <div><div class="h4 mb-0 fw-bold"><?= $stats['total_consultations'] ?? 0 ?></div><small class="text-secondary">Consultations</small></div>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary border-top pt-2">
                        <span>Ce mois</span><b class="text-success"><?= $stats['consultations_this_month'] ?? 0 ?></b>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-outline card-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-info bg-info-subtle me-3" style="width:54px;height:54px;font-size:23px;"><i class="bx bx-calendar-check"></i></div>
                        <div><div class="h4 mb-0 fw-bold"><?= $stats['upcoming_appointments'] ?? 0 ?></div><small class="text-secondary">Rendez-vous à venir</small></div>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary border-top pt-2">
                        <span>Statut</span><b class="text-info">En cours</b>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-outline card-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-warning bg-warning-subtle me-3" style="width:54px;height:54px;font-size:23px;"><i class="bx bx-book-heart"></i></div>
                        <div><div class="h4 mb-0 fw-bold"><?= $stats['active_prescriptions'] ?? 0 ?></div><small class="text-secondary">Ordonnances actives</small></div>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary border-top pt-2">
                        <span>30 derniers jours</span><b>—</b>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-outline card-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-primary bg-primary-subtle me-3" style="width:54px;height:54px;font-size:23px;"><i class="bx bx-chat"></i></div>
                        <div><div class="h4 mb-0 fw-bold">Nouveau</div><small class="text-secondary">Demander une consultation</small></div>
                    </div>
                    <a href="<?= base_url('home-patient') ?>" class="btn btn-sm btn-success rounded-pill px-3 mt-1">Demander <i class="bx bx-right-arrow-alt"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 fw-bold text-primary"><i class="bx bx-history me-2"></i>Mes consultations récentes</h6>
            <a href="<?= base_url('home-patient') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Tout voir <i class="bx bx-right-arrow-alt ms-1"></i></a>
        </div>
        <div class="table-responsive" style="max-height:380px;overflow-y:auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase small text-secondary">N°</th><th class="text-uppercase small text-secondary">Médecin</th><th class="text-uppercase small text-secondary">Spécialité</th><th class="text-uppercase small text-secondary">Type</th><th class="text-uppercase small text-secondary">Statut</th><th class="text-uppercase small text-secondary">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($consultations)): ?>
                        <?php foreach ($consultations as $c): ?>
                            <tr>
                                <td class="mono small">#<?= htmlspecialchars($c->numero_consultation ?? $c->id) ?></td>
                                <td class="fw-semibold small">Dr. <?= htmlspecialchars(trim(($c->medecin_prenom ?? '') . ' ' . ($c->medecin_nom ?? ''))) ?></td>
                                <td class="small text-secondary"><?= htmlspecialchars($c->specialite ?? '—') ?></td>
                                <td><span class="badge text-bg-light border"><?= htmlspecialchars($c->type ?? '—') ?></span></td>
                                <td><?= userStatBadge($c->statut ?? '') ?></td>
                                <td class="small text-secondary"><?= date('d/m/Y H:i', strtotime($c->date_souhaitee ?? 'now')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-secondary py-4 small">Aucune consultation pour le moment. <a href="<?= base_url('home-patient') ?>" class="text-primary fw-semibold">En demander une</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═══════════ ESPACE INVESTISSEUR (si identifié) ═══════════ -->
    <?php if ($is_investor): ?>
        <h6 class="text-uppercase small fw-bold text-secondary mb-3"><i class="bx bx-coin-stack me-1"></i> Mon espace investisseur</h6>
        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="card card-outline card-primary h-100">
                    <div class="card-body">
                        <h6 class="fw-bold text-primary mb-3"><i class="bx bx-user-circle me-2"></i>Mon profil</h6>
                        <div class="mb-2 small"><span class="text-secondary">Organisation :</span><br><b><?= htmlspecialchars($investor->organization ?? '—') ?></b></div>
                        <div class="mb-2 small"><span class="text-secondary">Fonction :</span><br><b><?= htmlspecialchars($investor->position_title ?? '—') ?></b></div>
                        <div class="mb-2 small"><span class="text-secondary">Engagement :</span><br><b class="text-success"><?= $commit_labels[$investor->commitment_range ?? ''] ?? ($investor->commitment_range ?? '—') ?></b></div>
                        <div class="small"><span class="text-secondary">Timeline :</span><br><b><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $investor->timeline ?? '—'))) ?></b></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card card-outline card-success h-100">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold text-primary"><i class="bx bx-git-branch me-2"></i>Phases du projet</h6>
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
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-semibold small"><?= htmlspecialchars($ph['nom_phase']) ?></span>
                                    <span class="small fw-bold" style="color:<?= $clr ?>;">$ <?= number_format((float)$ph['montant_total'], 0, '.', ',') ?></span>
                                </div>
                                <div class="d-flex gap-2 mb-2 flex-wrap">
                                    <?php if (!empty($ph['annee_debut'])): ?>
                                        <span class="badge text-bg-light border small"><?= $ph['annee_debut'] ?>–<?= $ph['annee_fin'] ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($ph['devise'])): ?>
                                        <span class="badge text-bg-light border small"><?= $ph['devise'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="progress" style="height:8px;"><div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $clr ?>;"></div></div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-secondary text-center py-3 small mb-0">Aucune phase configurée.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ═══════════ ESPACE COURTier (si identifié) ═══════════ -->
    <?php if ($is_broker): ?>
        <h6 class="text-uppercase small fw-bold text-secondary mb-3"><i class="bx bx-handshake me-1"></i> Mon espace courtier</h6>
        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="card card-outline card-info h-100">
                    <div class="card-body">
                        <h6 class="fw-bold text-primary mb-3"><i class="bx bx-briefcase me-2"></i>Mon profil</h6>
                        <div class="mb-2 small"><span class="text-secondary">Entreprise :</span><br><b><?= htmlspecialchars($broker->firm_name ?? '—') ?></b></div>
                        <div class="mb-2 small"><span class="text-secondary">Statut réglementaire :</span><br><b><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $broker->regulatory_status ?? '—'))) ?></b></div>
                        <div class="small"><span class="text-secondary">Contact :</span><br><b><?= htmlspecialchars($broker->mobile_phone ?? $user->telephone ?? '—') ?></b></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card card-outline card-success h-100">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="mb-0 fw-bold text-primary"><i class="bx bx-user-plus me-2"></i>Mes investisseurs</h6>
                        <a href="<?= base_url('broker/dashboard') ?>" class="btn btn-sm btn-success rounded-pill px-3">Gérer <i class="bx bx-right-arrow-alt ms-1"></i></a>
                    </div>
                    <div class="table-responsive" style="max-height:380px;overflow-y:auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th class="text-uppercase small text-secondary">Nom</th><th class="text-uppercase small text-secondary">Organisation</th><th class="text-uppercase small text-secondary">Engagement</th><th class="text-uppercase small text-secondary">Statut</th><th class="text-uppercase small text-secondary">Date</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($broker_investors)): ?>
                                    <?php foreach ($broker_investors as $bi):
                                        $bs = ['pending' => ['warning','bx-time','En attente'], 'contacted' => ['info','bx-phone','Contacté'], 'invested' => ['success','bx-check','Investi']];
                                        $bc = $bs[$bi->status] ?? ['secondary','bx-question-mark',ucfirst($bi->status ?? '')];
                                    ?>
                                    <tr>
                                        <td class="fw-semibold small"><?= htmlspecialchars($bi->full_name ?? '') ?></td>
                                        <td class="small text-secondary"><?= htmlspecialchars($bi->organization ?? '—') ?></td>
                                        <td class="small"><?= htmlspecialchars($bi->commitment_range ?? '—') ?></td>
                                        <td><span class="badge text-bg-<?= $bc[0] ?> rounded-pill"><i class="bx <?= $bc[1] ?>"></i> <?= $bc[2] ?></span></td>
                                        <td class="small text-secondary"><?= userTimeAgo($bi->created_at ?? '') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-secondary py-4 small">Aucun investisseur géré pour le moment.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div><!-- /.page-content -->


<style>
.mono { font-family: 'IBM Plex Mono', Consolas, monospace; }
</style>

<?php include VIEWPATH . 'includes/backend/Footer.php'; ?>