<?php defined('BASEPATH') OR exit('No direct script access allowed');
 include VIEWPATH.'includes/backend/Header.php'; 
 include VIEWPATH.'includes/backend/Sidebar.php'; 
 include VIEWPATH.'includes/backend/Topheader.php'; 

/* ─── Helpers PHP locaux ─────────────────────────────────── */
function fbu(float $v, int $dec = 0): string {
    return number_format($v, $dec, ',', ' ') . ' F';
}
function usd(float $v, int $dec = 0): string {
    return '$ ' . number_format($v, $dec, '.', ',');
}
function pct(float $now, float $before): string {
    if ($before == 0) return '+0%';
    $d = round(($now - $before) / $before * 100, 1);
    return ($d >= 0 ? '+' : '') . $d . '%';
}
function ago(string $dt): string {
    if (!$dt) return '—';
    $diff = time() - strtotime($dt);
    if ($diff < 60)     return 'À l\'instant';
    if ($diff < 3600)   return floor($diff/60)   . ' min';
    if ($diff < 86400)  return floor($diff/3600)  . ' h';
    if ($diff < 604800) return floor($diff/86400) . ' j';
    return date('d/m/Y', strtotime($dt));
}
function badge_order(string $s): string {
    $map = [
        'en_attente' => ['warning','bx-time','En attente'],
        'confirmee'  => ['success','bx-check','Confirmée'],
        'preparation'=> ['info','bx-package','Préparation'],
        'expediee'   => ['primary','bx-truck','Expédiée'],
        'livree'     => ['success','bx-check-double','Livrée'],
        'annulee'    => ['danger','bx-x','Annulée'],
        'remboursee' => ['secondary','bx-refresh','Remboursée'],
    ];
    $c = $map[$s] ?? ['secondary','bx-question-mark',ucfirst($s)];
    return "<span class='badge text-bg-{$c[0]}'><i class='bx {$c[1]}'></i> {$c[2]}</span>";
}
function badge_consult(string $s): string {
    $map = [
        'en_attente' => ['warning','bx-time','En attente'],
        'confirmee'  => ['info','bx-calendar-check','Confirmée'],
        'en_cours'   => ['primary','bx-video','En cours'],
        'terminee'   => ['success','bx-check-double','Terminée'],
        'annulee'    => ['danger','bx-x','Annulée'],
        'refusee'    => ['secondary','bx-block','Refusée'],
    ];
    $c = $map[$s] ?? ['secondary','bx-question-mark',ucfirst($s)];
    return "<span class='badge text-bg-{$c[0]}'><i class='bx {$c[1]}'></i> {$c[2]}</span>";
}
function badge_req(string $s): string {
    $map = [
        'pending'   => ['warning','bx-time','En attente'],
        'processing'=> ['info','bx-loader-circle','Traitement'],
        'completed' => ['success','bx-check','Complétée'],
        'cancelled' => ['danger','bx-x','Annulée'],
    ];
    $c = $map[$s] ?? ['secondary','bx-question-mark',ucfirst($s)];
    return "<span class='badge text-bg-{$c[0]}'><i class='bx {$c[1]}'></i> {$c[2]}</span>";
}

/* ─── Variables avec valeurs par défaut ──────────────────── */
$kpi_users         = $kpi_users         ?? [];
$kpi_orders        = $kpi_orders        ?? [];
$kpi_finance       = $kpi_finance       ?? [];
$kpi_telemedecine  = $kpi_telemedecine  ?? [];
$kpi_products      = $kpi_products      ?? [];
$kpi_media         = $kpi_media         ?? [];
$visitor_stats     = $visitor_stats     ?? [];
$newsletter_stats  = $newsletter_stats  ?? [];
$investor_stats    = $investor_stats    ?? [];
$broker_stats      = $broker_stats      ?? [];
$investment_phases = $investment_phases ?? [];
$ecommerce         = $ecommerce         ?? [];
$advertise         = $advertise         ?? [];
$order_requests    = $order_requests    ?? [];
$telemedecine      = $telemedecine      ?? [];
$media_engagement  = $media_engagement  ?? [];
$social_networks   = $social_networks   ?? [];
$contact_messages  = $contact_messages  ?? [];
$latest_users      = $latest_users      ?? [];
$latest_orders     = $latest_orders     ?? [];
$latest_consultations    = $latest_consultations    ?? [];
$latest_order_requests   = $latest_order_requests   ?? [];
$latest_investors  = $latest_investors  ?? [];
$latest_brokers    = $latest_brokers    ?? [];
$chart_revenue_30d = $chart_revenue_30d ?? [];
$chart_users_30d   = $chart_users_30d   ?? [];
$chart_consult_30d = $chart_consult_30d ?? [];
$chart_visits_30d  = $chart_visits_30d  ?? [];
$chart_labels_30d  = $chart_labels_30d  ?? [];
$chart_newsletter  = $chart_newsletter  ?? [];
$chart_order_req   = $chart_order_req   ?? [];
$dist_user_types   = $dist_user_types   ?? [];
$dist_order_status = $dist_order_status ?? [];
$dist_consult_type = $dist_consult_type ?? [];
$dist_consult_stat = $dist_consult_stat ?? [];
$dist_media_type   = $dist_media_type   ?? [];
$dist_order_req_status = $dist_order_req_status ?? [];
$dist_invest_commit= $dist_invest_commit?? [];
$dist_devices      = $dist_devices      ?? [];
$dist_brokers_country = $dist_brokers_country ?? [];
$alerts            = $alerts            ?? [];
$quick_actions     = $quick_actions     ?? [];
$pending_verif     = $pending_verif     ?? [];
$top_products      = $top_products      ?? [];
$top_medias        = $top_medias        ?? [];
$upcoming_consults = $upcoming_consults ?? [];
$recent_activities = $recent_activities ?? [];
$system_health     = $system_health     ?? [];
$generated_at      = $generated_at      ?? date('d/m/Y H:i:s');
?>

<script src="<?= base_url() ?>assets/backend/plugins/chartjs/js/chart.js"></script>
<style>
.mono { font-family: 'IBM Plex Mono', Consolas, monospace; }
.scroll-y { max-height: 360px; overflow-y: auto; }
.ch-box { position: relative; width: 100%; }
.ch-200 { height: 200px; }
.ch-250 { height: 250px; }
.ch-300 { height: 300px; }
.timeline { padding-left: 20px; border-left: 2px solid rgba(0,0,0,.12); }
.tl-item { position: relative; padding-bottom: 14px; padding-left: 16px; }
.tl-item::before {
  content: ''; position: absolute; left: -22px; top: 5px;
  width: 10px; height: 10px; border-radius: 50%;
  background: var(--bs-primary); border: 2px solid var(--bs-body-bg);
}
.stat-row { display: flex; align-items: center; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid rgba(0,0,0,.08); }
.stat-row:last-child { border-bottom: none; }
.pulse { width: 10px; height: 10px; border-radius: 50%; background: var(--bs-success); display: inline-block; position: relative; }
.pulse::after { content: ''; position: absolute; inset: -4px; border-radius: 50%; border: 2px solid var(--bs-success); animation: pulseAnim 1.8s ease infinite; }
@keyframes pulseAnim { 0%{transform:scale(.8);opacity:1;} 100%{transform:scale(2);opacity:0;} }
</style>

<div class="page-wrapper">
<div class="page-content">

<!-- ═══════════════════ EN-TÊTE ═══════════════════ -->
<div class="card card-primary card-outline mb-4">
  <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3" style="background: linear-gradient(135deg, #062C54 0%, #0F766E 100%); border-radius: calc(.375rem - 1px);">
    <div>
      <h2 class="h4 fw-bold text-white mb-1">NUFOTEC Phytomed – Dashboard</h2>
      <p class="text-white-50 small mb-2">Tableau de bord administrateur · Généré le <?= $generated_at ?></p>
      <div class="d-flex gap-3 flex-wrap small text-white-50">
        <span class="d-inline-flex align-items-center gap-1"><i class="bx bx-map"></i> Bujumbura, Burundi</span>
        <span class="d-inline-flex align-items-center gap-1"><i class="bx bx-phone"></i> +257 79 666 439</span>
        <span class="d-inline-flex align-items-center gap-1"><span class="pulse" style="width:7px;height:7px;"></span> Système opérationnel</span>
      </div>
    </div>
    <div class="text-end">
      <div class="h3 fw-bold text-white mono mb-0" id="liveTime">--:--:--</div>
      <div class="small text-white-50 mt-1"><?= date('l d F Y') ?></div>
      <div class="d-flex gap-2 justify-content-end flex-wrap mt-2">
        <span class="badge text-bg-light"><i class="bx bx-user me-1"></i><?= htmlspecialchars($this->session->userdata('prenom').' '.$this->session->userdata('nom')) ?></span>
        <span class="badge text-bg-light mono"><?= htmlspecialchars($this->session->userdata('role_nom')) ?></span>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════ ERREUR ═══════════════════ -->
<?php if (!empty($error)): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
  <i class="bx bx-error-circle fs-4"></i>
  <div><div class="fw-semibold">Erreur de chargement</div><div class="small"><?= htmlspecialchars($error) ?></div></div>
</div>
<?php endif; ?>

<!-- ═══════════════════ ALERTES ═══════════════════ -->
<?php if (!empty($alerts)): ?>
<div class="mb-4">
  <?php foreach ($alerts as $al): ?>
  <div class="alert alert-<?= $al['type'] ?> d-flex align-items-center gap-3 py-2">
    <i class="<?= $al['icon'] ?> fs-4"></i>
    <div class="flex-grow-1">
      <div class="fw-semibold small"><?= htmlspecialchars($al['title']) ?></div>
      <div class="small opacity-75"><?= htmlspecialchars($al['msg']) ?></div>
    </div>
    <a href="<?= $al['link'] ?>" class="btn btn-outline-secondary btn-sm">Voir <i class="bx bx-right-arrow-alt"></i></a>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ═══════════════════ KPIs LIGNE 1 ═══════════════════ -->
<h5 class="text-uppercase small fw-bold text-secondary mb-3 d-flex align-items-center gap-2"><i class="bx bx-bar-chart-alt-2 fs-5"></i> Indicateurs clés</h5>
<div class="row g-3 mb-4">

  <!-- Utilisateurs -->
  <div class="col-sm-6 col-xl-4">
    <div class="card card-outline card-primary mb-0">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="d-flex align-items-center justify-content-center rounded-3 text-primary bg-primary-subtle" style="width:44px;height:44px;font-size:22px;"><i class="bx bx-group"></i></div>
          <div class="flex-grow-1">
            <div class="fs-3 fw-bold"><?= number_format($kpi_users['total'] ?? 0) ?></div>
            <div class="small text-secondary text-uppercase">Utilisateurs</div>
          </div>
        </div>
        <div class="small text-secondary d-flex flex-wrap gap-2 border-top pt-2">
          <span>En ligne: <b class="mono"><?= $kpi_users['online'] ?? 0 ?></b></span>
          <span>Actifs: <b><?= $kpi_users['active'] ?? 0 ?></b></span>
          <span>Auj: <b class="mono text-success">+<?= $kpi_users['today_n'] ?? 0 ?></b></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Commandes -->
  <div class="col-sm-6 col-xl-4">
    <div class="card card-outline card-danger mb-0">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="d-flex align-items-center justify-content-center rounded-3 text-danger bg-danger-subtle" style="width:44px;height:44px;font-size:22px;"><i class="bx bx-shopping-bag"></i></div>
          <div class="flex-grow-1">
            <div class="fs-3 fw-bold"><?= number_format(($kpi_orders['shop']['total'] ?? 0) + ($kpi_orders['requests']['total'] ?? 0)) ?></div>
            <div class="small text-secondary text-uppercase">Commandes (all)</div>
          </div>
        </div>
        <div class="small text-secondary d-flex flex-wrap gap-2 border-top pt-2">
          <span>Shop: <b><?= $kpi_orders['shop']['total'] ?? 0 ?></b></span>
          <span>Demandes: <b><?= $kpi_orders['requests']['total'] ?? 0 ?></b></span>
          <span>En attente: <b class="text-danger"><?= ($kpi_orders['shop']['pending'] ?? 0) + ($kpi_orders['requests']['pending'] ?? 0) ?></b></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Consultations -->
  <div class="col-sm-6 col-xl-4">
    <div class="card card-outline card-info mb-0">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="d-flex align-items-center justify-content-center rounded-3 text-info bg-info-subtle" style="width:44px;height:44px;font-size:22px;"><i class="bx bx-health"></i></div>
          <div class="flex-grow-1">
            <div class="fs-3 fw-bold"><?= number_format($kpi_telemedecine['total'] ?? 0) ?></div>
            <div class="small text-secondary text-uppercase">Consultations</div>
          </div>
        </div>
        <div class="small text-secondary d-flex flex-wrap gap-2 border-top pt-2">
          <span>Auj: <b><?= $kpi_telemedecine['today'] ?? 0 ?></b></span>
          <span>Terminées: <b><?= $kpi_telemedecine['completed'] ?? 0 ?></b></span>
          <span>Médecins: <b><?= $kpi_telemedecine['medecins'] ?? 0 ?></b></span>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ═══════════════════ KPIs LIGNE 2 ═══════════════════ -->
<div class="row g-3 mb-4">

  <div class="col-sm-6 col-xl-4">
    <div class="card card-outline card-secondary mb-0">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="d-flex align-items-center justify-content-center rounded-3 text-secondary bg-secondary-subtle" style="width:44px;height:44px;font-size:22px;"><i class="bx bx-package"></i></div>
          <div class="flex-grow-1">
            <div class="fs-3 fw-bold"><?= number_format($kpi_products['advertise_total'] ?? 0) ?></div>
            <div class="small text-secondary text-uppercase">Produits (total)</div>
          </div>
        </div>
        <div class="small text-secondary d-flex flex-wrap gap-2 border-top pt-2">
          <span>Publicisés: <b><?= $kpi_products['advertise_total'] ?? 0 ?></b></span>
          <span>Devis reçus: <b><?= $kpi_products['total_price_requests'] ?? 0 ?></b></span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-4">
    <div class="card card-outline card-danger mb-0">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="d-flex align-items-center justify-content-center rounded-3 text-danger bg-danger-subtle" style="width:44px;height:44px;font-size:22px;"><i class="bx bx-film"></i></div>
          <div class="flex-grow-1">
            <div class="fs-3 fw-bold"><?= number_format($kpi_media['total'] ?? 0) ?></div>
            <div class="small text-secondary text-uppercase">Médias publiés</div>
          </div>
        </div>
        <div class="small text-secondary d-flex flex-wrap gap-2 border-top pt-2">
          <span>Vues: <b><?= number_format($kpi_media['total_views'] ?? 0) ?></b></span>
          <span>Likes: <b><?= $kpi_media['total_likes'] ?? 0 ?></b></span>
          <span>DL: <b><?= $kpi_media['total_downloads'] ?? 0 ?></b></span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-4">
    <div class="card card-outline card-success mb-0">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="d-flex align-items-center justify-content-center rounded-3 text-success bg-success-subtle" style="width:44px;height:44px;font-size:22px;"><i class="bx bx-mail-send"></i></div>
          <div class="flex-grow-1">
            <div class="fs-3 fw-bold"><?= number_format($newsletter_stats['total'] ?? 0) ?></div>
            <div class="small text-secondary text-uppercase">Newsletter</div>
          </div>
        </div>
        <div class="small text-secondary d-flex flex-wrap gap-2 border-top pt-2">
          <span>Emails: <b><?= $newsletter_stats['total_email'] ?? 0 ?></b></span>
          <span>Tél: <b><?= $newsletter_stats['total_phone'] ?? 0 ?></b></span>
          <span>Ce mois: <b class="text-success">+<?= $newsletter_stats['month'] ?? 0 ?></b></span>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ═══════════════════ GRAPHIQUES PRINCIPAUX ═══════════════════ -->
<h5 class="text-uppercase small fw-bold text-secondary mb-3 d-flex align-items-center gap-2"><i class="bx bx-trending-up fs-5"></i> Tendances 30 jours</h5>
<div class="row g-3 mb-4">

  <!-- Revenus + commandes -->
  <div class="col-lg-6">
    <div class="card card-outline card-primary h-100">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="card-title mb-0"><i class="bx bx-line-chart me-1"></i> Revenus & Commandes</h5>
        <div class="btn-group btn-group-sm" role="group" id="tabsRevenue">
          <button type="button" class="btn btn-outline-secondary tab active" data-chart="revenue" onclick="switchMainChart('revenue',this)">Revenus</button>
          <button type="button" class="btn btn-outline-secondary tab" data-chart="users" onclick="switchMainChart('users',this)">Utilisateurs</button>
          <button type="button" class="btn btn-outline-secondary tab" data-chart="consults" onclick="switchMainChart('consults',this)">Consultations</button>
          <button type="button" class="btn btn-outline-secondary tab" data-chart="visits" onclick="switchMainChart('visits',this)">Visites</button>
          <button type="button" class="btn btn-outline-secondary tab" data-chart="newsletter" onclick="switchMainChart('newsletter',this)">Newsletter</button>
          <button type="button" class="btn btn-outline-secondary tab" data-chart="orderreq" onclick="switchMainChart('orderreq',this)">Demandes</button>
        </div>
      </div>
      <div class="card-body">
        <div class="ch-box ch-300"><canvas id="mainChart"></canvas></div>
      </div>
    </div>
  </div>

  <!-- Répartitions -->
  <div class="col-lg-4">
    <div class="card card-outline card-info h-100">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="card-title mb-0"><i class="bx bx-pie-chart-alt-2 me-1"></i> Répartitions</h5>
        <div class="btn-group btn-group-sm" role="group" id="tabsDist">
          <button type="button" class="btn btn-outline-secondary tab active" onclick="switchDist('userTypes',this)">Utilisateurs</button>
          <button type="button" class="btn btn-outline-secondary tab" onclick="switchDist('orderStatus',this)">Commandes</button>
          <button type="button" class="btn btn-outline-secondary tab" onclick="switchDist('consultStat',this)">Consultations</button>
          <button type="button" class="btn btn-outline-secondary tab" onclick="switchDist('mediaType',this)">Médias</button>
          <button type="button" class="btn btn-outline-secondary tab" onclick="switchDist('devices',this)">Appareils</button>
          <button type="button" class="btn btn-outline-secondary tab" onclick="switchDist('invest',this)">Investisseurs</button>
        </div>
      </div>
      <div class="card-body">
        <div class="ch-box ch-300"><canvas id="distChart"></canvas></div>
      </div>
    </div>
  </div>

</div>

<!-- ═══════════════════ FINANCE DÉTAIL ═══════════════════ -->
<h5 class="text-uppercase small fw-bold text-secondary mb-3 d-flex align-items-center gap-2"><i class="bx bx-dollar-circle fs-5"></i> Finance & Investissement</h5>
<div class="row g-3 mb-4">

  <!-- Résumé financier -->
  <div class="col-lg-4">
    <div class="card card-outline card-warning">
      <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-money me-1"></i> Résumé financier</h5></div>
      <div class="card-body py-2">
        <div class="stat-row"><span class="text-secondary small">Total télémédecine</span><b class="mono"><?= fbu((float)($kpi_finance['consultations']['total'] ?? 0)) ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Revenus aujourd'hui</span><b class="mono text-success"><?= fbu((float)($kpi_finance['today_revenue'] ?? 0)) ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Revenus ce mois</span><b class="mono"><?= fbu((float)($kpi_finance['month_revenue'] ?? 0)) ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Consultations terminées</span><b class="mono"><?= number_format($kpi_telemedecine['completed'] ?? 0) ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Médecins actifs</span><b class="mono"><?= $kpi_telemedecine['available'] ?? 0 ?>/<?= $kpi_telemedecine['medecins'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Investissement planifié</span><b class="mono text-warning"><?= usd((float)($kpi_finance['investment_planned'] ?? 0)) ?></b></div>
      </div>
    </div>
  </div>

  <!-- Phases d'investissement -->
  <div class="col-lg-4">
    <div class="card card-outline card-primary">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bx bx-rocket me-1"></i> Phases d'investissement</h5>
        <span class="badge text-bg-primary"><?= $investment_phases['count'] ?? 0 ?> phases</span>
      </div>
      <div class="card-body py-2">
        <?php if (!empty($investment_phases['phases'])): ?>
          <?php
          $max_inv = max(array_column($investment_phases['phases'],'montant_total') ?: [1]);
          $phase_colors = ['primary','warning','danger'];
          foreach ($investment_phases['phases'] as $idx => $ph):
            $pct = $max_inv > 0 ? round($ph['montant_total'] / $max_inv * 100) : 0;
            $clr = $phase_colors[$idx % count($phase_colors)];
          ?>
          <div class="py-2 border-bottom">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="small fw-semibold"><?= htmlspecialchars($ph['nom_phase']) ?></span>
              <span class="mono small text-<?= $clr ?> fw-bold"><?= usd((float)$ph['montant_total']) ?></span>
            </div>
            <div class="d-flex gap-2 mb-2">
              <?php if ($ph['annee_debut']): ?><span class="badge text-bg-light border"><?= $ph['annee_debut'] ?>–<?= $ph['annee_fin'] ?></span><?php endif; ?>
              <span class="badge text-bg-light border"><?= $ph['devise'] ?></span>
            </div>
            <div class="progress" style="height:6px;">
              <div class="progress-bar prog-bar bg-<?= $clr ?>" style="width:<?= $pct ?>%;"></div>
            </div>
          </div>
          <?php endforeach; ?>
          <div class="d-flex justify-content-end align-items-center gap-1 mt-3 small">
            <span class="text-secondary">Total: </span>
            <b class="mono text-warning"><?= usd((float)($investment_phases['total'] ?? 0)) ?></b>
          </div>
        <?php else: ?>
          <p class="text-secondary small text-center py-3 mb-0">Aucune phase configurée</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Investisseurs -->
  <div class="col-lg-4">
    <div class="card card-outline card-secondary">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bx bx-briefcase me-1"></i> Investisseurs</h5>
        <span class="badge text-bg-secondary"><?= $investor_stats['total'] ?? 0 ?> total</span>
      </div>
      <div class="card-body py-2">
        <div class="mb-3">
          <div class="ch-box ch-200"><canvas id="investCommitChart"></canvas></div>
        </div>
        <div class="stat-row"><span class="text-secondary small">Ce mois</span><b>+<?= $investor_stats['this_month'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Equity</span><b><?= $investor_stats['interests']['equity'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">ESG/Impact</span><b><?= $investor_stats['interests']['blended_finance'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Partenariat strat.</span><b><?= $investor_stats['interests']['strategic'] ?? 0 ?></b></div>
      </div>
    </div>
  </div>

</div>

<!-- ═══════════════════ COMMANDES & PRODUITS ═══════════════════ -->
<h5 class="text-uppercase small fw-bold text-secondary mb-3 d-flex align-items-center gap-2"><i class="bx bx-store fs-5"></i> Commerce & Produits</h5>
<div class="row g-3 mb-4">

  <!-- Dernières demandes de commande -->
  <div class="col-lg-8">
    <div class="card card-outline card-danger">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bx bx-cart me-1"></i> Demandes de commande récentes</h5>
        <a href="<?= base_url('Products/admin_orders') ?>" class="btn btn-outline-secondary btn-sm">Tout voir</a>
      </div>
      <div class="table-responsive scroll-y">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr><th class="text-uppercase small text-secondary">Client</th><th class="text-uppercase small text-secondary">Produit</th><th class="text-uppercase small text-secondary">Pays</th><th class="text-uppercase small text-secondary">Statut</th><th class="text-uppercase small text-secondary">Date</th></tr>
          </thead>
          <tbody>
            <?php if (!empty($latest_order_requests)):
              foreach (array_slice($latest_order_requests, 0, 8) as $req): ?>
            <tr>
              <td>
                <div class="fw-semibold small"><?= htmlspecialchars($req['customer_name']) ?></div>
                <div class="small text-secondary"><?= htmlspecialchars($req['customer_phone']) ?></div>
              </td>
              <td class="small" style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($req['product_title']) ?></td>
              <td><span class="badge text-bg-light border"><?= htmlspecialchars($req['customer_country']) ?></span></td>
              <td><?= badge_req($req['order_status']) ?></td>
              <td class="text-secondary small"><?= ago($req['created_at']) ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-center text-secondary small py-4">Aucune demande</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Top produits -->
  <div class="col-lg-4">
    <div class="card card-outline card-secondary">
      <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-package me-1"></i> Top produits publicisés</h5></div>
      <div class="card-body py-2">
        <?php if (!empty($top_products)):
          foreach ($top_products as $prod): ?>
        <div class="stat-row">
          <div class="min-w-0">
            <div class="fw-semibold small"><?= htmlspecialchars($prod['title']) ?></div>
            <div class="small text-secondary"><?= $prod['price'] ?></div>
          </div>
          <div class="text-end">
            <div class="mono small text-success"><?= $prod['price_request_count'] ?> devis</div>
            <div class="small text-secondary"><?= $prod['order_count'] ?? 0 ?> commandes</div>
          </div>
        </div>
        <?php endforeach; else: ?>
        <p class="text-secondary small text-center py-3 mb-0">Aucun produit</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<!-- ═══════════════════ TÉLÉMÉDECINE ═══════════════════ -->
<h5 class="text-uppercase small fw-bold text-secondary mb-3 d-flex align-items-center gap-2"><i class="bx bx-video fs-5"></i> Télémédecine</h5>
<div class="row g-3 mb-4">

  <!-- Stats médecins -->
  <div class="col-lg-4">
    <div class="card card-outline card-info">
      <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-plus-medical me-1"></i> Médecins</h5></div>
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="text-center flex-grow-1 rounded-3 py-2 bg-info-subtle">
            <div class="mono fs-4 fw-bold text-info"><?= $kpi_telemedecine['medecins'] ?? 0 ?></div>
            <div class="small text-secondary">Total</div>
          </div>
          <div class="text-center flex-grow-1 rounded-3 py-2 bg-success-subtle">
            <div class="mono fs-4 fw-bold text-success"><?= $kpi_telemedecine['available'] ?? 0 ?></div>
            <div class="small text-secondary">Disponibles</div>
          </div>
          <div class="text-center flex-grow-1 rounded-3 py-2 bg-warning-subtle">
            <div class="mono fs-4 fw-bold text-warning"><?= $kpi_telemedecine['avg_rating'] ?? '0.0' ?></div>
            <div class="small text-secondary">Note moy.</div>
          </div>
        </div>
        <?php if (!empty($telemedecine['specialties'])): ?>
        <div class="small text-uppercase text-secondary fw-semibold mb-2" style="letter-spacing:.05em;">Par spécialité</div>
        <?php foreach (array_slice($telemedecine['specialties'],0,4) as $sp): ?>
        <div class="stat-row">
          <span class="small"><?= htmlspecialchars($sp['specialite']) ?></span>
          <span class="badge text-bg-info"><?= $sp['total'] ?> consults</span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Consultations récentes -->
  <div class="col-lg-8">
    <div class="card card-outline card-info">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bx bx-calendar-check me-1"></i> Consultations récentes</h5>
        <a href="<?= base_url('Consultations') ?>" class="btn btn-outline-secondary btn-sm">Toutes</a>
      </div>
      <div class="table-responsive scroll-y">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr><th class="text-uppercase small text-secondary">Patient</th><th class="text-uppercase small text-secondary">Médecin</th><th class="text-uppercase small text-secondary">Spécialité</th><th class="text-uppercase small text-secondary">Type</th><th class="text-uppercase small text-secondary">Montant</th><th class="text-uppercase small text-secondary">Statut</th><th class="text-uppercase small text-secondary">Date</th></tr>
          </thead>
          <tbody>
            <?php if (!empty($latest_consultations)):
              foreach ($latest_consultations as $c): ?>
            <tr>
              <td>
                <div class="fw-semibold small"><?= htmlspecialchars($c['pat_prenom'].' '.$c['pat_nom']) ?></div>
                <div class="small text-secondary"><?= htmlspecialchars($c['pat_tel'] ?? '') ?></div>
              </td>
              <td class="small">Dr. <?= htmlspecialchars(($c['med_prenom'] ?? '').' '.($c['med_nom'] ?? '')) ?></td>
              <td class="small text-secondary"><?= htmlspecialchars($c['specialite'] ?? '—') ?></td>
              <td><span class="badge text-bg-light border"><?= htmlspecialchars($c['type']) ?></span></td>
              <td class="mono small"><?= fbu((float)($c['prix_ttc'] ?? 0)) ?></td>
              <td><?= badge_consult($c['statut']) ?></td>
              <td class="text-secondary small"><?= ago($c['created_at']) ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7" class="text-center text-secondary small py-4">Aucune consultation</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- Prochaines consultations -->
<?php if (!empty($upcoming_consults)): ?>
<div class="card card-outline card-success mb-4">
  <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-time-five me-1"></i> Prochaines consultations confirmées</h5></div>
  <div class="card-body">
    <div class="row g-2">
      <?php foreach ($upcoming_consults as $uc): ?>
      <div class="col-md-4 col-xl-3">
        <div class="border rounded-3 p-3 h-100">
          <div class="fw-semibold small"><?= htmlspecialchars($uc['pat_prenom'].' '.$uc['pat_nom']) ?></div>
          <div class="small text-secondary">Dr. <?= htmlspecialchars($uc['med_prenom'] ?? '?') ?> · <?= htmlspecialchars($uc['specialite'] ?? '') ?></div>
          <div class="mono small text-success mt-2"><?= date('d/m/Y H:i', strtotime($uc['date_souhaitee'])) ?></div>
          <div class="mt-2"><?= badge_consult($uc['statut']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ VISITEURS & NEWSLETTER ═══════════════════ -->
<h5 class="text-uppercase small fw-bold text-secondary mb-3 d-flex align-items-center gap-2"><i class="bx bx-globe fs-5"></i> Audience & Communication</h5>
<div class="row g-3 mb-4">

  <!-- Visiteurs -->
  <div class="col-lg-6">
    <div class="card card-outline card-success">
      <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-globe me-1"></i> Visiteurs du site</h5></div>
      <div class="card-body">
        <div class="row g-2 mb-3">
          <div class="col-6">
            <div class="rounded-3 py-2 text-center bg-success-subtle">
              <div class="mono fs-4 fw-bold text-success"><?= number_format($visitor_stats['today'] ?? 0) ?></div>
              <div class="small text-secondary">Aujourd'hui</div>
            </div>
          </div>
          <div class="col-6">
            <div class="rounded-3 py-2 text-center bg-info-subtle">
              <div class="mono fs-4 fw-bold text-info"><?= number_format($visitor_stats['unique_today'] ?? 0) ?></div>
              <div class="small text-secondary">Uniques</div>
            </div>
          </div>
          <div class="col-6">
            <div class="rounded-3 py-2 text-center bg-warning-subtle">
              <div class="mono fs-4 fw-bold text-warning"><?= number_format($visitor_stats['yesterday'] ?? 0) ?></div>
              <div class="small text-secondary">Hier</div>
            </div>
          </div>
          <div class="col-6">
            <?php $trend = $visitor_stats['trend_pct'] ?? 0; ?>
            <div class="rounded-3 py-2 text-center <?= $trend >= 0 ? 'bg-success-subtle' : 'bg-danger-subtle' ?>">
              <div class="mono fs-4 fw-bold <?= $trend >= 0 ? 'text-success' : 'text-danger' ?>"><?= ($trend >= 0 ? '+' : '') . $trend ?>%</div>
              <div class="small text-secondary">Tendance</div>
            </div>
          </div>
        </div>
        <?php if (!empty($visitor_stats['top_countries'])): ?>
        <div class="small text-uppercase text-secondary fw-semibold mb-2" style="letter-spacing:.05em;">Top pays</div>
        <?php foreach ($visitor_stats['top_countries'] as $ctry): ?>
        <div class="stat-row">
          <span class="small"><?= htmlspecialchars($ctry['country'] ?? '—') ?></span>
          <b class="mono small"><?= $ctry['cnt'] ?></b>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Newsletter -->
  <div class="col-lg-6">
    <div class="card card-outline card-warning">
      <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-mail-send me-1"></i> Newsletter</h5></div>
      <div class="card-body">
        <div class="mb-3">
          <div class="ch-box ch-200"><canvas id="newsletterMiniChart"></canvas></div>
        </div>
        <div class="stat-row"><span class="text-secondary small">Abonnés totaux</span><b class="mono text-success"><?= $newsletter_stats['total'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Emails</span><b><?= $newsletter_stats['total_email'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Numéros tél.</span><b><?= $newsletter_stats['total_phone'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Aujourd'hui</span><b class="text-success">+<?= $newsletter_stats['today'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Cette semaine</span><b>+<?= $newsletter_stats['week'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Ce mois</span><b>+<?= $newsletter_stats['month'] ?? 0 ?></b></div>
      </div>
    </div>
  </div>

</div>

<!-- ═══════════════════ RÉSEAUX SOCIAUX ═══════════════════ -->
<?php if (!empty($social_networks)): ?>
<div class="card card-outline card-secondary mb-4">
  <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-share-alt me-1"></i> Présence sur les réseaux</h5></div>
  <div class="card-body">
    <div class="row g-2">
      <?php foreach ($social_networks as $net):
        $net_colors = ['WhatsApp'=>'success','Telegram'=>'info','Facebook'=>'primary','Instagram'=>'danger','TikTok'=>'dark','YouTube'=>'danger'];
        $clr = $net_colors[$net['plateforme']] ?? 'secondary';
      ?>
      <div class="col-md-4 col-xl-3">
        <a href="<?= htmlspecialchars($net['url'] ?? '#') ?>" target="_blank" rel="noopener" class="d-flex align-items-center gap-3 border rounded-3 p-3 h-100 text-decoration-none" style="color:inherit;">
          <div class="d-flex align-items-center justify-content-center rounded-3 bg-<?= $clr ?>-subtle text-<?= $clr ?> fw-bold" style="width:38px;height:38px;font-size:.8rem;"><?= strtoupper(substr($net['plateforme'],0,2)) ?></div>
          <div class="min-w-0">
            <div class="fw-bold small"><?= htmlspecialchars($net['plateforme']) ?></div>
            <div class="mono small text-<?= $clr ?>"><?= htmlspecialchars($net['nombre_participants']) ?> membres</div>
            <div class="small text-secondary text-truncate" style="max-width:160px;"><?= htmlspecialchars($net['label']) ?></div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ UTILISATEURS & COURTIERS & INVESTISSEURS ═══════════════════ -->
<h5 class="text-uppercase small fw-bold text-secondary mb-3 d-flex align-items-center gap-2"><i class="bx bx-user-circle fs-5"></i> Communauté</h5>
<div class="row g-3 mb-4">

  <!-- Derniers utilisateurs -->
  <div class="col-lg-4">
    <div class="card card-outline card-primary">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bx bx-group me-1"></i> Derniers inscrits</h5>
        <a href="<?= base_url('Users') ?>" class="btn btn-outline-secondary btn-sm">Voir tout</a>
      </div>
      <div class="list-group list-group-flush scroll-y">
        <?php if (!empty($latest_users)):
          foreach (array_slice($latest_users,0,7) as $u): ?>
        <div class="list-group-item d-flex align-items-center gap-3 py-2">
          <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold bg-<?= ['primary','success','warning','danger'][crc32($u['email'])%4] ?>" style="width:34px;height:34px;font-size:.8rem;flex-shrink:0;">
            <?php if (!empty($u['photo']) && $u['photo'] != 'default-avatar.png'): ?>
              <img src="<?= base_url('uploads/users/'.$u['photo']) ?>" alt="" class="w-100 h-100 rounded-circle object-fit-cover">
            <?php else: ?>
              <?= strtoupper(substr($u['prenom']??'U',0,1)) ?>
            <?php endif; ?>
          </div>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-semibold small text-truncate"><?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?></div>
            <div class="small text-secondary"><?= htmlspecialchars($u['type_utilisateur'] ?? '') ?> · <?= ago($u['created_at']) ?></div>
          </div>
          <span class="badge <?= $u['is_active'] ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $u['is_active'] ? '✓' : '○' ?></span>
        </div>
        <?php endforeach; else: ?>
        <p class="text-secondary small text-center py-3 mb-0">Aucun utilisateur</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Investisseurs récents -->
  <div class="col-lg-4">
    <div class="card card-outline card-secondary">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bx bx-briefcase me-1"></i> Investisseurs récents</h5>
        <a href="<?= base_url('Investors') ?>" class="btn btn-outline-secondary btn-sm">Voir tout</a>
      </div>
      <div class="list-group list-group-flush scroll-y">
        <?php if (!empty($latest_investors)):
          foreach ($latest_investors as $inv): ?>
        <div class="list-group-item py-2">
          <div class="d-flex justify-content-between align-items-start">
            <div class="min-w-0">
              <div class="fw-semibold small"><?= htmlspecialchars($inv['full_name']) ?></div>
              <div class="small text-secondary"><?= htmlspecialchars($inv['organization'] ?? '—') ?> · <?= htmlspecialchars($inv['country_name'] ?? '') ?></div>
            </div>
            <span class="badge text-bg-primary"><?= htmlspecialchars($inv['commitment_range'] ?? '—') ?></span>
          </div>
          <div class="small text-secondary mt-1"><?= ago($inv['created_at']) ?></div>
        </div>
        <?php endforeach; else: ?>
        <p class="text-secondary small text-center py-3 mb-0">Aucun investisseur</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Courtiers récents -->
  <div class="col-lg-4">
    <div class="card card-outline card-warning">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bx bx-briefcase-alt me-1"></i> Courtiers récents</h5>
        <a href="<?= base_url('Brokers') ?>" class="btn btn-outline-secondary btn-sm">Voir tout</a>
      </div>
      <div class="list-group list-group-flush scroll-y">
        <?php if (!empty($latest_brokers)):
          foreach ($latest_brokers as $br): ?>
        <div class="list-group-item py-2">
          <div class="d-flex justify-content-between align-items-start">
            <div class="min-w-0">
              <div class="fw-semibold small"><?= htmlspecialchars($br['full_name']) ?></div>
              <div class="small text-secondary"><?= htmlspecialchars($br['firm_name'] ?? '—') ?> · <?= htmlspecialchars($br['country_name'] ?? '') ?></div>
            </div>
            <span class="badge <?= $br['regulatory_status']==='Licensed' ? 'text-bg-success' : 'text-bg-warning' ?>"><?= htmlspecialchars($br['regulatory_status'] ?? '—') ?></span>
          </div>
          <div class="small text-secondary mt-1"><?= ago($br['created_at']) ?></div>
        </div>
        <?php endforeach; else: ?>
        <p class="text-secondary small text-center py-3 mb-0">Aucun courtier</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<!-- ═══════════════════ MÉDIAS & CONTACT ═══════════════════ -->
<h5 class="text-uppercase small fw-bold text-secondary mb-3 d-flex align-items-center gap-2"><i class="bx bx-image fs-5"></i> Médias & Engagement</h5>
<div class="row g-3 mb-4">

  <!-- Top médias -->
  <div class="col-lg-7">
    <div class="card card-outline card-danger">
      <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-film me-1"></i> Top médias (vues)</h5></div>
      <div class="card-body py-2">
        <?php if (!empty($top_medias)):
          foreach ($top_medias as $med): ?>
        <div class="stat-row">
          <div class="d-flex align-items-center gap-2 min-w-0">
            <?php $ti=['audio'=>'bx-music','video'=>'bx-film','image'=>'bx-image','document'=>'bx-file-blank'];
            $ic=$ti[$med['type']]??'bx-file'; ?>
            <i class="bx <?= $ic ?> fs-4 text-danger"></i>
            <div class="min-w-0">
              <div class="fw-semibold small text-truncate" style="max-width:240px;"><?= htmlspecialchars($med['titre'] ?? '—') ?></div>
              <div class="small text-secondary"><?= ucfirst($med['type']) ?></div>
            </div>
          </div>
          <div class="text-end flex-shrink-0">
            <div class="mono small text-success"><?= number_format($med['views']) ?> vues</div>
            <div class="small text-secondary"><?= $med['likes'] ?> ♥ · <?= $med['comments'] ?> 💬</div>
          </div>
        </div>
        <?php endforeach; else: ?>
        <p class="text-secondary small text-center py-3 mb-0">Aucun média</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Commentaires récents + messages contact -->
  <div class="col-lg-5 d-flex flex-column gap-3">
    <!-- Messages contact -->
    <div class="card card-outline card-danger">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bx bx-envelope me-1"></i> Messages contact</h5>
        <span class="badge text-bg-danger"><?= $contact_messages['unread'] ?? 0 ?> non lus</span>
      </div>
      <div class="card-body py-2">
        <?php if (!empty($contact_messages['latest'])):
          foreach ($contact_messages['latest'] as $msg): ?>
        <div class="py-2 border-bottom">
          <div class="d-flex justify-content-between">
            <span class="fw-semibold small"><?= htmlspecialchars($msg['FullName']) ?></span>
            <span class="small text-secondary"><?= ago($msg['Date_creation']) ?></span>
          </div>
          <div class="small text-secondary text-truncate"><?= htmlspecialchars($msg['Subject']) ?></div>
        </div>
        <?php endforeach; else: ?>
        <p class="text-secondary small text-center py-2 mb-0">Aucun message</p>
        <?php endif; ?>
        <div class="mt-2">
          <a href="<?= base_url('contact_us/Contact_Us') ?>" class="btn btn-outline-secondary btn-sm w-100">Voir tous les messages</a>
        </div>
      </div>
    </div>

    <!-- Commentaires médias -->
    <div class="card card-outline card-secondary">
      <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-comment me-1"></i> Commentaires récents</h5></div>
      <div class="card-body py-2">
        <?php if (!empty($media_engagement['recent_comments'])):
          foreach ($media_engagement['recent_comments'] as $cm): ?>
        <div class="py-2 border-bottom">
          <div class="d-flex justify-content-between">
            <span class="fw-semibold small"><?= htmlspecialchars($cm['author_name'] ?? '—') ?></span>
            <span class="small text-secondary"><?= ago($cm['created_at']) ?></span>
          </div>
          <div class="small text-secondary text-truncate"><?= htmlspecialchars($cm['comment']) ?></div>
        </div>
        <?php endforeach; else: ?>
        <p class="text-secondary small text-center py-2 mb-0">Aucun commentaire</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<!-- ═══════════════════ ACTIVITÉS & ACTIONS RAPIDES ═══════════════════ -->
<div class="row g-3 mb-4">

  <!-- Activités récentes -->
  <div class="col-lg-7">
    <div class="card card-outline card-primary">
      <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-history me-1"></i> Activité récente</h5></div>
      <div class="card-body">
        <div class="timeline scroll-y" style="max-height:300px;">
          <?php if (!empty($recent_activities)):
            foreach (array_slice($recent_activities,0,8) as $act): ?>
          <div class="tl-item">
            <div class="d-flex justify-content-between align-items-start">
              <div class="min-w-0">
                <div class="fw-semibold small"><?= htmlspecialchars($act['prenom'].' '.$act['nom']) ?></div>
                <div class="small text-secondary"><?= htmlspecialchars($act['description'] ?? $act['action'] ?? '') ?></div>
              </div>
              <div class="text-end flex-shrink-0 ms-2">
                <span class="badge text-bg-light border" style="font-size:.65rem;"><?= htmlspecialchars($act['module'] ?? 'system') ?></span>
                <div class="small text-secondary mt-1"><?= ago($act['created_at']) ?></div>
              </div>
            </div>
          </div>
          <?php endforeach; else: ?>
          <p class="text-secondary small text-center py-3 mb-0">Aucune activité</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Actions rapides + vérifications + santé système -->
  <div class="col-lg-5 d-flex flex-column gap-3">

    <!-- Actions rapides -->
    <div class="card card-outline card-warning">
      <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-zap me-1"></i> Actions rapides</h5></div>
      <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ($quick_actions as $qa): ?>
          <a href="<?= $qa['link'] ?>" class="btn btn-outline-primary btn-sm"><i class="<?= $qa['icon'] ?>"></i> <?= htmlspecialchars($qa['title']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Éléments en attente -->
    <div class="card card-outline card-danger">
      <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-hourglass me-1"></i> En attente de traitement</h5></div>
      <div class="card-body py-2">
        <?php
        $pv_items = [
          ['label'=>'Demandes produits',       'val'=>$pending_verif['req_pending']??0,       'link'=>base_url('Products/admin_orders')],
          ['label'=>'Consultations en attente','val'=>$pending_verif['consults_pending']??0,  'link'=>base_url('Consultations?statut=en_attente')],
          ['label'=>'Messages non lus',        'val'=>$pending_verif['contact_unread']??0,    'link'=>base_url('contact_us/Contact_Us')],
          ['label'=>'Utilisateurs inactifs',   'val'=>$pending_verif['users_inactive']??0,    'link'=>base_url('Users?active=0')],
          ['label'=>'Emails non vérifiés',     'val'=>$pending_verif['unverified_email']??0,  'link'=>base_url('Users?verified=0')],
        ];
        foreach ($pv_items as $pvi): ?>
        <a href="<?= $pvi['link'] ?>" class="d-flex align-items-center justify-content-between text-decoration-none px-3 py-2 rounded-3 mb-2 bg-body-tertiary" style="color:inherit;">
          <span class="small"><?= $pvi['label'] ?></span>
          <span class="mono fw-bold text-warning"><?= $pvi['val'] ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Santé système -->
    <div class="card card-outline card-success">
      <div class="card-header"><h5 class="card-title mb-0"><i class="bx bx-heart-circle me-1"></i> Santé système</h5></div>
      <div class="card-body py-2">
        <div class="stat-row"><span class="text-secondary small">Connexions réussies auj.</span><b class="mono text-success"><?= $system_health['logins_today'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Tentatives échouées</span><b class="mono text-danger"><?= $system_health['failed_logins'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Erreurs aujourd'hui</span><b class="mono text-warning"><?= $system_health['errors_today'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Alertes système</span><b class="mono"><?= $system_health['warnings'] ?? 0 ?></b></div>
        <div class="stat-row"><span class="text-secondary small">Logs totaux</span><b class="mono"><?= number_format($system_health['total_logs'] ?? 0) ?></b></div>
      </div>
    </div>

  </div>
</div>

<!-- ═══════════════════ FOOTER ═══════════════════ -->
<div class="text-center small text-secondary py-3 border-top">
  NUFOTEC BURUNDI · Dashboard Admin · Généré le <?= $generated_at ?> ·
  <span id="realtimeStatus" class="text-success fw-semibold">● Live</span>
</div>

</div><!-- /.page-content -->


<!-- ═══════════════════ JAVASCRIPT ═══════════════════ -->
<script>
/* ── Horloge live ── */
function tick() {
  document.getElementById('liveTime').textContent = new Date().toLocaleTimeString('fr-FR');
}
setInterval(tick, 1000); tick();

/* ── Données PHP → JS ── */
const D = {
  labels30:     <?= json_encode($chart_labels_30d) ?>,
  revenue:      <?= json_encode($chart_revenue_30d) ?>,
  users:        <?= json_encode($chart_users_30d) ?>,
  consults:     <?= json_encode($chart_consult_30d) ?>,
  visits:       <?= json_encode($chart_visits_30d) ?>,
  newsletter:   <?= json_encode($chart_newsletter) ?>,
  orderReq:     <?= json_encode($chart_order_req) ?>,

  distUserTypes:    <?= json_encode(array_values($dist_user_types)) ?>,
  distOrderStatus:  <?= json_encode(array_values($dist_order_status)) ?>,
  distConsultStat:  <?= json_encode(array_values($dist_consult_stat)) ?>,
  distMediaType:    <?= json_encode(array_values($dist_media_type)) ?>,
  distDevices:      <?= json_encode(array_values($dist_devices)) ?>,
  distInvest:       <?= json_encode(array_values($dist_invest_commit)) ?>,
};

/* ── Palette ── */
const PAL = ['#0d6efd','#ffc107','#fd7e14','#0dcaf0','#d63384','#6f42c1','#198754','#fb923c','#0dcaf0','#fbbf24'];
const GRID = 'rgba(0,0,0,.06)';
const TICK = '#6c757d';

function rgba(hex, a=0.15) {
  const r=parseInt(hex.slice(1,3),16), g=parseInt(hex.slice(3,5),16), b=parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${a})`;
}

function chartDefaults() {
  return {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { labels: { color: TICK, font: { size: 11 }, boxWidth: 10 } } },
    scales: {
      x: { ticks: { color: TICK, font:{size:10} }, grid: { color: GRID } },
      y: { ticks: { color: TICK, font:{size:10}, callback: v => v>=1e6?(v/1e6).toFixed(1)+'M':v>=1000?(v/1000).toFixed(0)+'k':v }, grid: { color: GRID } }
    }
  };
}

/* ══════════════════════════════════════════
   GRAPHIQUE PRINCIPAL – multi-séries
══════════════════════════════════════════ */
const mainCtx = document.getElementById('mainChart').getContext('2d');
let mainChart = new Chart(mainCtx, {
  type: 'line',
  data: {
    labels: D.labels30,
    datasets: [{
      label: 'Revenus (F)',
      data: D.revenue,
      borderColor: '#ffc107', backgroundColor: rgba('#ffc107'), borderWidth: 2,
      tension: 0.4, fill: true, pointRadius: 2, pointHoverRadius: 5
    }]
  },
  options: {
    ...chartDefaults(),
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { position: 'top', align: 'end', labels: { color: TICK, boxWidth: 8, font:{size:11} } } }
  }
});

const chartDataMap = {
  revenue:    { label: 'Revenus (F)',          data: D.revenue,     color: '#ffc107' },
  users:      { label: 'Nouveaux utilisateurs', data: D.users,      color: '#0d6efd' },
  consults:   { label: 'Consultations',         data: D.consults,   color: '#0dcaf0' },
  visits:     { label: 'Visites du site',        data: D.visits,     color: '#6f42c1' },
  newsletter: { label: 'Inscriptions newsletter',data: D.newsletter, color: '#fd7e14' },
  orderreq:   { label: 'Demandes de commande',  data: D.orderReq,   color: '#d63384' },
};

function switchMainChart(key, el) {
  document.querySelectorAll('#tabsRevenue .tab').forEach(t => t.classList.remove('active'));
  if(el) el.classList.add('active');
  const m = chartDataMap[key] || chartDataMap.revenue;
  mainChart.data.datasets[0].data = m.data;
  mainChart.data.datasets[0].label = m.label;
  mainChart.data.datasets[0].borderColor = m.color;
  mainChart.data.datasets[0].backgroundColor = rgba(m.color);
  mainChart.update('none');
}

/* ══════════════════════════════════════════
   GRAPHIQUE RÉPARTITIONS – Doughnut
══════════════════════════════════════════ */
const distCtx = document.getElementById('distChart').getContext('2d');
let distChart = null;

function buildDistData(arr) {
  const labels = arr.map(r => r.label || r.type_utilisateur || r.statut || '?');
  const values = arr.map(r => parseInt(r.value || r.cnt || 0));
  return { labels, datasets: [{ data: values, backgroundColor: PAL.map(c => rgba(c, 0.7)), borderColor: PAL, borderWidth: 1 }] };
}

function buildDist(arr) {
  if (distChart) distChart.destroy();
  distChart = new Chart(distCtx, {
    type: 'doughnut',
    data: buildDistData(arr),
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '65%',
      plugins: { legend: { position: 'right', labels: { color: TICK, boxWidth: 10, font:{size:11}, padding: 8 } } }
    }
  });
}

const distMap = {
  userTypes:    D.distUserTypes,
  orderStatus:  D.distOrderStatus,
  consultStat:  D.distConsultStat,
  mediaType:    D.distMediaType,
  devices:      D.distDevices,
  invest:       D.distInvest,
};

function switchDist(key, el) {
  document.querySelectorAll('#tabsDist .tab').forEach(t => t.classList.remove('active'));
  if(el) el.classList.add('active');
  buildDist(distMap[key] || []);
}

buildDist(D.distUserTypes);

/* ══════════════════════════════════════════
   MINI CHART Newsletter
══════════════════════════════════════════ */
new Chart(document.getElementById('newsletterMiniChart'), {
  type: 'bar',
  data: {
    labels: D.labels30.slice(-14),
    datasets: [{ label: 'Inscrits', data: D.newsletter.slice(-14), backgroundColor: rgba('#fd7e14', 0.6), borderColor: '#fd7e14', borderWidth: 1, borderRadius: 4 }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: TICK, font:{size:9} }, grid: { display:false } },
      y: { ticks: { color: TICK, font:{size:9} }, grid: { color: GRID } }
    }
  }
});

/* ══════════════════════════════════════════
   MINI CHART Investisseurs Engagement
══════════════════════════════════════════ */
const invCtx = document.getElementById('investCommitChart');
if (invCtx && D.distInvest.length > 0) {
  new Chart(invCtx, {
    type: 'bar',
    data: {
      labels: D.distInvest.map(r => r.label || '—'),
      datasets: [{ data: D.distInvest.map(r=>parseInt(r.value||0)), backgroundColor: PAL.map(c=>rgba(c,0.65)), borderColor: PAL, borderWidth: 1, borderRadius: 5 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, indexAxis: 'y',
      plugins: { legend: { display:false } },
      scales: {
        x: { ticks:{color:TICK,font:{size:9}}, grid:{color:GRID} },
        y: { ticks:{color:TICK,font:{size:9}}, grid:{display:false} }
      }
    }
  });
}

/* ══════════════════════════════════════════
   REAL-TIME POLLING
══════════════════════════════════════════ */
async function pollRealtime() {
  try {
    const r = await fetch('<?= base_url('Dashboard/api_stats') ?>?type=realtime');
    const d = await r.json();
    if (d.success) {
      document.getElementById('realtimeStatus').textContent = '● Live · ' + d.data.server_time;
    }
  } catch(e) {
    document.getElementById('realtimeStatus').textContent = '○ Hors ligne';
    document.getElementById('realtimeStatus').style.color = '#dc3545';
  }
}
setInterval(pollRealtime, 60000);

/* ══════════════════════════════════════════
   ANIMATE PROGRESS BARS
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.prog-bar[style*="width"]').forEach(b => {
    const w = b.style.width;
    b.style.width = '0%';
    requestAnimationFrame(() => setTimeout(() => { b.style.width = w; }, 100));
  });
});
</script>

<?php include VIEWPATH . 'includes/backend/Footer.php'; ?>
