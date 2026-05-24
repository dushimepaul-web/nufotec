<?php defined('BASEPATH') OR exit('No direct script access allowed');
include VIEWPATH . 'includes/backend/Header.php';

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
    return "<span class='badge b-{$c[0]}'><i class='bx {$c[1]}'></i> {$c[2]}</span>";
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
    return "<span class='badge b-{$c[0]}'><i class='bx {$c[1]}'></i> {$c[2]}</span>";
}
function badge_req(string $s): string {
    $map = [
        'pending'   => ['warning','bx-time','En attente'],
        'processing'=> ['info','bx-loader-circle','Traitement'],
        'completed' => ['success','bx-check','Complétée'],
        'cancelled' => ['danger','bx-x','Annulée'],
    ];
    $c = $map[$s] ?? ['secondary','bx-question-mark',ucfirst($s)];
    return "<span class='badge b-{$c[0]}'><i class='bx {$c[1]}'></i> {$c[2]}</span>";
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
$whatsapp_stats    = $whatsapp_stats    ?? [];
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
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Dashboard Admin – NUFOTEC</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<style>
/* ════════════════════════════════════════════════
   DESIGN SYSTEM – NUFOTEC DARK-TEAL INDUSTRIAL
   ════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@400;500&family=Outfit:wght@300;400;500;600&display=swap');

:root {
  --c-bg:       #050E1A;
  --c-surface:  #0A1929;
  --c-card:     #0D2136;
  --c-border:   rgba(255,255,255,.07);
  --c-text:     #E2EAF4;
  --c-muted:    #6B8BAD;
  --c-accent:   #00E5C3;
  --c-green:    #0F766E;
  --c-teal:     #1a8c78;
  --c-orange:   #FF8C00;
  --c-crimson:  #DC143C;
  --c-gold:     #FFD000;
  --c-navy:     #062C54;
  --c-blue:     #38BDF8;

  --r-card:     14px;
  --r-sm:       8px;
  --shadow:     0 4px 24px rgba(0,0,0,.4);
  --transition: .22s cubic-bezier(.4,0,.2,1);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  background: var(--c-bg);
  color: var(--c-text);
  font-family: 'Outfit', sans-serif;
  font-size: 14px;
  line-height: 1.5;
}

/* ── Layout ── */
.db-wrap    { display: flex; min-height: 100vh; }
.db-content { flex: 1; overflow-y: auto; padding: 24px; max-width: 100%; }

/* ── Typography ── */
h1,h2,h3,h4,h5 { font-family: 'Syne', sans-serif; }
.mono { font-family: 'IBM Plex Mono', monospace; }

/* ── Cards ── */
.card {
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: var(--r-card);
  box-shadow: var(--shadow);
}
.card-hd {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px 12px;
  border-bottom: 1px solid var(--c-border);
}
.card-hd h5 {
  font-size: .85rem; font-weight: 700; letter-spacing: .06em;
  text-transform: uppercase; color: var(--c-muted);
  display: flex; align-items: center; gap: 7px;
}
.card-hd h5 .dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--c-accent); display: inline-block;
}
.card-body { padding: 18px 20px; }

/* ── Grid helpers ── */
.grid   { display: grid; gap: 16px; }
.g-4    { grid-template-columns: repeat(4,1fr); }
.g-3    { grid-template-columns: repeat(3,1fr); }
.g-2    { grid-template-columns: repeat(2,1fr); }
.g-1    { grid-template-columns: 1fr; }
.g-64   { grid-template-columns: 3fr 2fr; }
.g-46   { grid-template-columns: 2fr 3fr; }
.g-642  { grid-template-columns: 4fr 2fr; }

@media(max-width:1200px){ .g-4{grid-template-columns:repeat(2,1fr);} .g-3{grid-template-columns:repeat(2,1fr);} .g-64,.g-46,.g-642{grid-template-columns:1fr;} }
@media(max-width:700px) { .g-4,.g-3,.g-2{grid-template-columns:1fr;} }

/* ── KPI Cards ── */
.kpi {
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: var(--r-card);
  padding: 20px;
  position: relative; overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition);
  cursor: default;
}
.kpi:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.5); }
.kpi::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: var(--kpi-color, var(--c-accent));
}
.kpi-icon {
  width: 48px; height: 48px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; margin-bottom: 12px;
  background: color-mix(in srgb, var(--kpi-color, var(--c-accent)) 15%, transparent);
  color: var(--kpi-color, var(--c-accent));
}
.kpi-value {
  font-family: 'Syne', sans-serif;
  font-size: 2rem; font-weight: 800; line-height: 1;
  color: #fff; margin-bottom: 4px;
}
.kpi-label { font-size: .78rem; color: var(--c-muted); text-transform: uppercase; letter-spacing: .05em; }
.kpi-meta  { margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--c-border); display: flex; gap: 12px; flex-wrap: wrap; }
.kpi-sub   { font-size: .72rem; color: var(--c-muted); }
.kpi-sub b { color: var(--c-text); }

/* ── Badge ── */
.badge {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 9px; border-radius: 50px;
  font-size: .7rem; font-weight: 600; white-space: nowrap;
}
.b-warning  { background: rgba(255,140,0,.15);  color: #FFB347; }
.b-success  { background: rgba(15,118,110,.2);  color: #34D399; }
.b-info     { background: rgba(56,189,248,.15); color: #7DD3FC; }
.b-primary  { background: rgba(6,44,84,.3);     color: #93C5FD; }
.b-danger   { background: rgba(220,20,60,.15);  color: #F87171; }
.b-secondary{ background: rgba(107,139,173,.15);color: #94A3B8; }
.b-accent   { background: rgba(0,229,195,.12);  color: var(--c-accent); }
.pill-g     { background: rgba(0,229,195,.12);  color: var(--c-accent); border-radius: 50px; padding: 3px 10px; font-size: .72rem; }
.pill-r     { background: rgba(220,20,60,.15);  color: #F87171; border-radius: 50px; padding: 3px 10px; font-size: .72rem; }

/* ── Tables ── */
.tbl { width: 100%; border-collapse: collapse; }
.tbl thead th {
  padding: 10px 12px; text-align: left;
  font-size: .7rem; text-transform: uppercase; letter-spacing: .06em;
  color: var(--c-muted); border-bottom: 1px solid var(--c-border);
  background: rgba(255,255,255,.02); font-family: 'Syne',sans-serif;
}
.tbl tbody tr { border-bottom: 1px solid var(--c-border); transition: background var(--transition); }
.tbl tbody tr:hover { background: rgba(255,255,255,.025); }
.tbl tbody td { padding: 10px 12px; font-size: .82rem; }
.tbl tbody tr:last-child { border-bottom: none; }

/* ── Alerts ── */
.alert-item {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 16px; border-radius: var(--r-sm);
  border-left: 3px solid; margin-bottom: 10px;
  background: rgba(255,255,255,.03);
  transition: transform var(--transition);
}
.alert-item:hover { transform: translateX(4px); }
.alert-item.warning  { border-color: var(--c-orange); }
.alert-item.info     { border-color: var(--c-blue); }
.alert-item.danger   { border-color: var(--c-crimson); }
.alert-item.success  { border-color: var(--c-accent); }
.alert-icon { font-size: 20px; flex-shrink: 0; }
.alert-item.warning  .alert-icon { color: var(--c-orange); }
.alert-item.info     .alert-icon { color: var(--c-blue); }
.alert-item.danger   .alert-icon { color: var(--c-crimson); }
.alert-item.success  .alert-icon { color: var(--c-accent); }

/* ── Buttons ── */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: var(--r-sm); font-size: .8rem; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: all var(--transition); }
.btn-accent   { background: var(--c-accent); color: #050E1A; }
.btn-accent:hover { background: #00c8ab; }
.btn-outline  { background: transparent; border: 1px solid var(--c-border); color: var(--c-muted); }
.btn-outline:hover { border-color: var(--c-accent); color: var(--c-accent); }
.btn-sm       { padding: 4px 10px; font-size: .72rem; }
.btn-ghost    { background: rgba(255,255,255,.05); color: var(--c-text); }
.btn-ghost:hover { background: rgba(255,255,255,.1); }

/* ── Quick actions ── */
.qa-grid { display: flex; flex-wrap: wrap; gap: 10px; }
.qa-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 16px; border-radius: var(--r-sm);
  background: rgba(255,255,255,.04); border: 1px solid var(--c-border);
  color: var(--c-text); text-decoration: none; font-size: .8rem; font-weight: 600;
  transition: all var(--transition);
}
.qa-btn:hover { background: rgba(0,229,195,.1); border-color: var(--c-accent); color: var(--c-accent); transform: translateY(-2px); }
.qa-btn i { font-size: 16px; }

/* ── Progress bar ── */
.prog-wrap { background: rgba(255,255,255,.06); border-radius: 99px; height: 6px; overflow: hidden; }
.prog-bar  { height: 100%; border-radius: 99px; transition: width 1s ease; }

/* ── Avatar ── */
.av {
  width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
  background: var(--c-green); display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: .8rem; color: #fff; overflow: hidden;
}
.av img { width: 100%; height: 100%; object-fit: cover; }

/* ── Section title ── */
.section-title {
  font-family: 'Syne', sans-serif; font-size: .75rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .1em; color: var(--c-muted);
  margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
}
.section-title::after {
  content: ''; flex: 1; height: 1px;
  background: linear-gradient(to right, var(--c-border), transparent);
}

/* ── Timeline ── */
.timeline { padding-left: 20px; border-left: 2px solid var(--c-border); }
.tl-item  { position: relative; padding-bottom: 16px; padding-left: 16px; }
.tl-item::before {
  content: ''; position: absolute; left: -21px; top: 5px;
  width: 10px; height: 10px; border-radius: 50%;
  background: var(--c-accent); border: 2px solid var(--c-bg);
}

/* ── Stat row ── */
.stat-row { display: flex; align-items: center; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid var(--c-border); }
.stat-row:last-child { border-bottom: none; }

/* ── Network chip ── */
.net-chip {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 14px; border-radius: var(--r-sm);
  background: rgba(255,255,255,.03); border: 1px solid var(--c-border);
  transition: border-color var(--transition);
}
.net-chip:hover { border-color: rgba(255,255,255,.15); }

/* ── Scrollable ── */
.scroll-y { overflow-y: auto; max-height: 360px; }
.scroll-y::-webkit-scrollbar { width: 4px; }
.scroll-y::-webkit-scrollbar-track { background: transparent; }
.scroll-y::-webkit-scrollbar-thumb { background: var(--c-border); border-radius: 2px; }

/* ── Chart container ── */
.ch-box { position: relative; width: 100%; }
.ch-200 { height: 200px; }
.ch-250 { height: 250px; }
.ch-300 { height: 300px; }

/* ── Header ── */
.db-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 24px; padding: 18px 22px;
  background: linear-gradient(135deg, #062C54 0%, #0F766E 100%);
  border-radius: var(--r-card); position: relative; overflow: hidden;
}
.db-header::after {
  content: 'NUFOTEC'; position: absolute; right: -20px; top: -20px;
  font-family: 'Syne',sans-serif; font-size: 7rem; font-weight: 800;
  color: rgba(255,255,255,.05); pointer-events: none; letter-spacing: -.02em;
}
.db-header h2 { font-size: 1.4rem; font-weight: 800; color: #fff; margin-bottom: 4px; }
.db-header p  { color: rgba(255,255,255,.65); font-size: .82rem; }
.db-header-right { text-align: right; }
.db-header-right .time { font-family: 'IBM Plex Mono',monospace; font-size: 1.2rem; color: var(--c-accent); font-weight: 500; }

/* ── Pulse ── */
.pulse { width: 10px; height: 10px; border-radius: 50%; background: var(--c-accent); display: inline-block; position: relative; }
.pulse::after { content: ''; position: absolute; inset: -4px; border-radius: 50%; border: 2px solid var(--c-accent); animation: pulseAnim 1.8s ease infinite; }
@keyframes pulseAnim { 0%{transform:scale(.8);opacity:1;} 100%{transform:scale(2);opacity:0;} }

/* ── Tabbed ── */
.tabs { display: flex; gap: 4px; margin-bottom: 14px; flex-wrap: wrap; }
.tab  { padding: 5px 12px; border-radius: var(--r-sm); font-size: .75rem; font-weight: 600; cursor: pointer; color: var(--c-muted); background: transparent; border: 1px solid transparent; transition: all var(--transition); }
.tab.active { background: var(--c-accent); color: #050E1A; }
.tab:hover:not(.active) { border-color: var(--c-border); color: var(--c-text); }
.tab-pane { display: none; }
.tab-pane.active { display: block; }

/* ── Pending verif ── */
.pv-item {
  display: flex; align-items: center; justify-content: space-between;
  padding: 9px 14px; border-radius: var(--r-sm);
  background: rgba(255,255,255,.025); margin-bottom: 8px;
}
.pv-num { font-family: 'IBM Plex Mono',monospace; font-size: 1.1rem; font-weight: 600; color: var(--c-orange); }

/* ── Phase progress ── */
.phase-item { padding: 12px 0; border-bottom: 1px solid var(--c-border); }
.phase-item:last-child { border-bottom: none; }
</style>

<div class="db-wrap">
<?php include VIEWPATH . 'includes/backend/Sidebar.php'; ?>
<div class="db-content">

<!-- ═══════════════════ EN-TÊTE ═══════════════════ -->
<div class="db-header">
  <div>
    <h2>NUFOTEC Phytomed – Dashboard</h2>
    <p>Tableau de bord administrateur · Généré le <?= $generated_at ?></p>
    <div style="display:flex;gap:16px;margin-top:10px;flex-wrap:wrap;">
      <span style="font-size:.75rem;color:rgba(255,255,255,.55);display:flex;align-items:center;gap:6px;">
        <i class="bx bx-map"></i> Bujumbura, Burundi
      </span>
      <span style="font-size:.75rem;color:rgba(255,255,255,.55);display:flex;align-items:center;gap:6px;">
        <i class="bx bx-phone"></i> +257 79 666 439
      </span>
      <span style="font-size:.75rem;color:rgba(255,255,255,.55);display:flex;align-items:center;gap:6px;">
        <span class="pulse" style="width:7px;height:7px;"></span> Système opérationnel
      </span>
    </div>
  </div>
  <div class="db-header-right">
    <div class="time" id="liveTime">--:--:--</div>
    <div style="font-size:.72rem;color:rgba(255,255,255,.5);margin-top:4px;"><?= date('l d F Y') ?></div>
    <div style="margin-top:8px;display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;">
      <span class="pill-g"><i class="bx bx-user"></i> <?= htmlspecialchars($this->session->userdata('prenom').' '.$this->session->userdata('nom')) ?></span>
      <span class="pill-g mono"><?= htmlspecialchars($this->session->userdata('role_nom')) ?></span>
    </div>
  </div>
</div>

<!-- ═══════════════════ ALERTES ═══════════════════ -->
<?php if (!empty($alerts)): ?>
<div style="margin-bottom:20px;">
  <?php foreach ($alerts as $al): ?>
  <div class="alert-item <?= $al['type'] ?>">
    <i class="<?= $al['icon'] ?> alert-icon"></i>
    <div style="flex:1;">
      <div style="font-weight:600;font-size:.82rem;"><?= htmlspecialchars($al['title']) ?></div>
      <div style="font-size:.75rem;color:var(--c-muted);"><?= htmlspecialchars($al['msg']) ?></div>
    </div>
    <a href="<?= $al['link'] ?>" class="btn btn-outline btn-sm">Voir <i class="bx bx-right-arrow-alt"></i></a>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ═══════════════════ KPIs LIGNE 1 ═══════════════════ -->
<p class="section-title"><i class="bx bx-bar-chart-alt-2"></i> Indicateurs clés</p>
<div class="grid g-4" style="margin-bottom:20px;">

  <!-- Utilisateurs -->
  <div class="kpi" style="--kpi-color:var(--c-accent);">
    <div class="kpi-icon"><i class="bx bx-group"></i></div>
    <div class="kpi-value"><?= number_format($kpi_users['total'] ?? 0) ?></div>
    <div class="kpi-label">Utilisateurs</div>
    <div class="kpi-meta">
      <div class="kpi-sub">En ligne: <b class="mono"><?= $kpi_users['online'] ?? 0 ?></b></div>
      <div class="kpi-sub">Actifs: <b><?= $kpi_users['active'] ?? 0 ?></b></div>
      <div class="kpi-sub">Auj: <b class="mono">+<?= $kpi_users['today_n'] ?? 0 ?></b></div>
    </div>
  </div>

  <!-- Revenus -->
  <div class="kpi" style="--kpi-color:var(--c-gold);">
    <div class="kpi-icon"><i class="bx bx-wallet"></i></div>
    <div class="kpi-value mono"><?= fbu((float)($kpi_finance['total_revenue'] ?? 0)) ?></div>
    <div class="kpi-label">Revenus totaux</div>
    <div class="kpi-meta">
      <div class="kpi-sub">Ce mois: <b><?= fbu((float)($kpi_finance['month_revenue'] ?? 0)) ?></b></div>
      <div class="kpi-sub">Auj: <b><?= fbu((float)($kpi_finance['today_revenue'] ?? 0)) ?></b></div>
    </div>
  </div>

  <!-- Commandes -->
  <div class="kpi" style="--kpi-color:var(--c-orange);">
    <div class="kpi-icon"><i class="bx bx-shopping-bag"></i></div>
    <div class="kpi-value"><?= number_format(($kpi_orders['shop']['total'] ?? 0) + ($kpi_orders['requests']['total'] ?? 0)) ?></div>
    <div class="kpi-label">Commandes (all)</div>
    <div class="kpi-meta">
      <div class="kpi-sub">Shop: <b><?= $kpi_orders['shop']['total'] ?? 0 ?></b></div>
      <div class="kpi-sub">Demandes: <b><?= $kpi_orders['requests']['total'] ?? 0 ?></b></div>
      <div class="kpi-sub">En attente: <b class="pill-r"><?= ($kpi_orders['shop']['pending'] ?? 0) + ($kpi_orders['requests']['pending'] ?? 0) ?></b></div>
    </div>
  </div>

  <!-- Consultations -->
  <div class="kpi" style="--kpi-color:var(--c-blue);">
    <div class="kpi-icon"><i class="bx bx-health"></i></div>
    <div class="kpi-value"><?= number_format($kpi_telemedecine['total'] ?? 0) ?></div>
    <div class="kpi-label">Consultations</div>
    <div class="kpi-meta">
      <div class="kpi-sub">Auj: <b><?= $kpi_telemedecine['today'] ?? 0 ?></b></div>
      <div class="kpi-sub">Terminées: <b><?= $kpi_telemedecine['completed'] ?? 0 ?></b></div>
      <div class="kpi-sub">Médecins: <b><?= $kpi_telemedecine['medecins'] ?? 0 ?></b></div>
    </div>
  </div>

</div>

<!-- ═══════════════════ KPIs LIGNE 2 ═══════════════════ -->
<div class="grid g-4" style="margin-bottom:28px;">

  <div class="kpi" style="--kpi-color:#A78BFA;">
    <div class="kpi-icon"><i class="bx bx-package"></i></div>
    <div class="kpi-value"><?= number_format(($kpi_products['catalogue_total'] ?? 0) + ($kpi_products['advertise_total'] ?? 0)) ?></div>
    <div class="kpi-label">Produits (total)</div>
    <div class="kpi-meta">
      <div class="kpi-sub">Catalogue: <b><?= $kpi_products['catalogue_total'] ?? 0 ?></b></div>
      <div class="kpi-sub">Publicisés: <b><?= $kpi_products['advertise_total'] ?? 0 ?></b></div>
      <div class="kpi-sub">Devis reçus: <b><?= $kpi_products['total_price_requests'] ?? 0 ?></b></div>
    </div>
  </div>

  <div class="kpi" style="--kpi-color:#F472B6;">
    <div class="kpi-icon"><i class="bx bx-film"></i></div>
    <div class="kpi-value"><?= number_format($kpi_media['total'] ?? 0) ?></div>
    <div class="kpi-label">Médias publiés</div>
    <div class="kpi-meta">
      <div class="kpi-sub">Vues: <b><?= number_format($kpi_media['total_views'] ?? 0) ?></b></div>
      <div class="kpi-sub">Likes: <b><?= $kpi_media['total_likes'] ?? 0 ?></b></div>
      <div class="kpi-sub">DL: <b><?= $kpi_media['total_downloads'] ?? 0 ?></b></div>
    </div>
  </div>

  <div class="kpi" style="--kpi-color:#34D399;">
    <div class="kpi-icon"><i class="bx bxl-whatsapp"></i></div>
    <div class="kpi-value"><?= number_format(($whatsapp_stats['wa_members'] ?? 0)) ?></div>
    <div class="kpi-label">Membres WA</div>
    <div class="kpi-meta">
      <div class="kpi-sub">Groupes: <b><?= $whatsapp_stats['wa_groups'] ?? 0 ?></b></div>
      <div class="kpi-sub">Msgs envoyés: <b><?= $whatsapp_stats['sent'] ?? 0 ?></b></div>
      <div class="kpi-sub">En file: <b><?= $whatsapp_stats['queue_pending'] ?? 0 ?></b></div>
    </div>
  </div>

  <div class="kpi" style="--kpi-color:#FB923C;">
    <div class="kpi-icon"><i class="bx bx-mail-send"></i></div>
    <div class="kpi-value"><?= number_format($newsletter_stats['total'] ?? 0) ?></div>
    <div class="kpi-label">Newsletter</div>
    <div class="kpi-meta">
      <div class="kpi-sub">Emails: <b><?= $newsletter_stats['total_email'] ?? 0 ?></b></div>
      <div class="kpi-sub">Tél: <b><?= $newsletter_stats['total_phone'] ?? 0 ?></b></div>
      <div class="kpi-sub">Ce mois: <b>+<?= $newsletter_stats['month'] ?? 0 ?></b></div>
    </div>
  </div>

</div>

<!-- ═══════════════════ GRAPHIQUES PRINCIPAUX ═══════════════════ -->
<p class="section-title"><i class="bx bx-trending-up"></i> Tendances 30 jours</p>
<div class="grid g-2" style="margin-bottom:28px;">

  <!-- Revenus + commandes -->
  <div class="card">
    <div class="card-hd">
      <h5><span class="dot"></span> Revenus & Commandes</h5>
      <div class="tabs" id="tabsRevenue">
        <div class="tab active" data-chart="revenue" onclick="switchMainChart('revenue',this)">Revenus</div>
        <div class="tab" data-chart="users" onclick="switchMainChart('users',this)">Utilisateurs</div>
        <div class="tab" data-chart="consults" onclick="switchMainChart('consults',this)">Consultations</div>
        <div class="tab" data-chart="visits" onclick="switchMainChart('visits',this)">Visites</div>
        <div class="tab" data-chart="newsletter" onclick="switchMainChart('newsletter',this)">Newsletter</div>
        <div class="tab" data-chart="orderreq" onclick="switchMainChart('orderreq',this)">Demandes</div>
      </div>
    </div>
    <div class="card-body">
      <div class="ch-box ch-300"><canvas id="mainChart"></canvas></div>
    </div>
  </div>

  <!-- Répartitions -->
  <div class="card">
    <div class="card-hd"><h5><span class="dot" style="background:#F472B6;"></span> Répartitions</h5>
      <div class="tabs" id="tabsDist">
        <div class="tab active" onclick="switchDist('userTypes',this)">Utilisateurs</div>
        <div class="tab" onclick="switchDist('orderStatus',this)">Commandes</div>
        <div class="tab" onclick="switchDist('consultStat',this)">Consultations</div>
        <div class="tab" onclick="switchDist('mediaType',this)">Médias</div>
        <div class="tab" onclick="switchDist('devices',this)">Appareils</div>
        <div class="tab" onclick="switchDist('invest',this)">Investisseurs</div>
      </div>
    </div>
    <div class="card-body">
      <div class="ch-box ch-300"><canvas id="distChart"></canvas></div>
    </div>
  </div>

</div>

<!-- ═══════════════════ FINANCE DÉTAIL ═══════════════════ -->
<p class="section-title"><i class="bx bx-dollar-circle"></i> Finance & Investissement</p>
<div class="grid g-3" style="margin-bottom:28px;">

  <!-- Résumé financier -->
  <div class="card">
    <div class="card-hd"><h5><span class="dot" style="background:var(--c-gold);"></span> Résumé financier</h5></div>
    <div class="card-body">
      <div class="stat-row">
        <span style="color:var(--c-muted);">Total e-commerce</span>
        <b class="mono"><?= fbu((float)($kpi_finance['orders']['total'] ?? 0)) ?></b>
      </div>
      <div class="stat-row">
        <span style="color:var(--c-muted);">Total télémédecine</span>
        <b class="mono"><?= fbu((float)($kpi_finance['consultations']['total'] ?? 0)) ?></b>
      </div>
      <div class="stat-row">
        <span style="color:var(--c-muted);">Revenu confirmé</span>
        <b class="mono" style="color:var(--c-accent);"><?= fbu((float)($kpi_finance['orders']['confirmed'] ?? 0)) ?></b>
      </div>
      <div class="stat-row">
        <span style="color:var(--c-muted);">Panier moyen</span>
        <b class="mono"><?= fbu((float)($kpi_finance['orders']['avg_order'] ?? 0)) ?></b>
      </div>
      <div class="stat-row">
        <span style="color:var(--c-muted);">Paniers abandonnés</span>
        <b class="mono" style="color:var(--c-crimson);"><?= $ecommerce['abandoned'] ?? 0 ?></b>
      </div>
      <div class="stat-row">
        <span style="color:var(--c-muted);">Investissement planifié</span>
        <b class="mono" style="color:var(--c-gold);"><?= usd((float)($kpi_finance['investment_planned'] ?? 0)) ?></b>
      </div>
    </div>
  </div>

  <!-- Phases d'investissement -->
  <div class="card">
    <div class="card-hd">
      <h5><span class="dot" style="background:var(--c-orange);"></span> Phases d'investissement</h5>
      <span class="badge b-accent"><?= $investment_phases['count'] ?? 0 ?> phases</span>
    </div>
    <div class="card-body">
      <?php if (!empty($investment_phases['phases'])): ?>
        <?php
        $max_inv = max(array_column($investment_phases['phases'],'montant_total') ?: [1]);
        $phase_colors = ['#00E5C3','#FFD000','#FF8C00'];
        foreach ($investment_phases['phases'] as $idx => $ph):
          $pct = $max_inv > 0 ? round($ph['montant_total'] / $max_inv * 100) : 0;
          $clr = $phase_colors[$idx % count($phase_colors)];
        ?>
        <div class="phase-item">
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
            <span style="font-size:.8rem;font-weight:600;"><?= htmlspecialchars($ph['nom_phase']) ?></span>
            <span class="mono" style="font-size:.78rem;color:<?= $clr ?>;"><?= usd((float)$ph['montant_total']) ?></span>
          </div>
          <div style="display:flex;gap:8px;margin-bottom:8px;">
            <?php if ($ph['annee_debut']): ?><span class="badge b-secondary"><?= $ph['annee_debut'] ?>–<?= $ph['annee_fin'] ?></span><?php endif; ?>
            <span class="badge b-secondary"><?= $ph['devise'] ?></span>
          </div>
          <div class="prog-wrap">
            <div class="prog-bar" style="width:<?= $pct ?>%;background:<?= $clr ?>;"></div>
          </div>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:12px;text-align:right;">
          <span style="font-size:.75rem;color:var(--c-muted);">Total: </span>
          <b class="mono" style="color:var(--c-gold);"><?= usd((float)($investment_phases['total'] ?? 0)) ?></b>
        </div>
      <?php else: ?>
        <p style="color:var(--c-muted);font-size:.82rem;text-align:center;padding:20px 0;">Aucune phase configurée</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Investisseurs -->
  <div class="card">
    <div class="card-hd">
      <h5><span class="dot" style="background:#A78BFA;"></span> Investisseurs</h5>
      <span class="badge b-accent"><?= $investor_stats['total'] ?? 0 ?> total</span>
    </div>
    <div class="card-body">
      <div style="margin-bottom:14px;">
        <div class="ch-box ch-200"><canvas id="investCommitChart"></canvas></div>
      </div>
      <div class="stat-row">
        <span style="color:var(--c-muted);">Ce mois</span><b>+<?= $investor_stats['this_month'] ?? 0 ?></b>
      </div>
      <div class="stat-row">
        <span style="color:var(--c-muted);">Equity</span><b><?= $investor_stats['interests']['equity'] ?? 0 ?></b>
      </div>
      <div class="stat-row">
        <span style="color:var(--c-muted);">ESG/Impact</span><b><?= $investor_stats['interests']['blended_finance'] ?? 0 ?></b>
      </div>
      <div class="stat-row">
        <span style="color:var(--c-muted);">Partenariat strat.</span><b><?= $investor_stats['interests']['strategic'] ?? 0 ?></b>
      </div>
    </div>
  </div>

</div>

<!-- ═══════════════════ COMMANDES & PRODUITS ═══════════════════ -->
<p class="section-title"><i class="bx bx-store"></i> Commerce & Produits</p>
<div class="grid g-64" style="margin-bottom:28px;">

  <!-- Dernières demandes de commande -->
  <div class="card">
    <div class="card-hd">
      <h5><span class="dot" style="background:var(--c-orange);"></span> Demandes de commande récentes</h5>
      <a href="<?= base_url('OrderRequests') ?>" class="btn btn-outline btn-sm">Tout voir</a>
    </div>
    <div class="scroll-y">
      <table class="tbl">
        <thead><tr>
          <th>Client</th><th>Produit</th><th>Pays</th><th>Statut</th><th>Date</th>
        </tr></thead>
        <tbody>
          <?php if (!empty($latest_order_requests)):
            foreach (array_slice($latest_order_requests, 0, 8) as $req): ?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:.82rem;"><?= htmlspecialchars($req['customer_name']) ?></div>
              <div style="font-size:.7rem;color:var(--c-muted);"><?= htmlspecialchars($req['customer_phone']) ?></div>
            </td>
            <td style="font-size:.78rem;max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($req['product_title']) ?></td>
            <td><span class="badge b-secondary"><?= htmlspecialchars($req['customer_country']) ?></span></td>
            <td><?= badge_req($req['order_status']) ?></td>
            <td style="color:var(--c-muted);font-size:.72rem;"><?= ago($req['created_at']) ?></td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--c-muted);">Aucune demande</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top produits -->
  <div class="card">
    <div class="card-hd">
      <h5><span class="dot" style="background:#A78BFA;"></span> Top produits publicisés</h5>
    </div>
    <div class="card-body">
      <?php if (!empty($top_products)):
        foreach ($top_products as $prod): ?>
      <div class="stat-row">
        <div>
          <div style="font-weight:600;font-size:.82rem;"><?= htmlspecialchars($prod['title']) ?></div>
          <div style="font-size:.7rem;color:var(--c-muted);"><?= $prod['price'] ?></div>
        </div>
        <div style="text-align:right;">
          <div class="mono" style="color:var(--c-accent);"><?= $prod['price_request_count'] ?> devis</div>
          <div style="font-size:.7rem;color:var(--c-muted);"><?= $prod['order_count'] ?? 0 ?> commandes</div>
        </div>
      </div>
      <?php endforeach; else: ?>
      <p style="color:var(--c-muted);text-align:center;padding:20px 0;">Aucun produit</p>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Dernières commandes shop -->
<div class="card" style="margin-bottom:28px;">
  <div class="card-hd">
    <h5><span class="dot" style="background:var(--c-gold);"></span> Dernières commandes e-shop</h5>
    <a href="<?= base_url('Commandes') ?>" class="btn btn-outline btn-sm">Toutes les commandes</a>
  </div>
  <div class="scroll-y">
    <table class="tbl">
      <thead><tr><th>N°</th><th>Client</th><th>Articles</th><th>Montant TTC</th><th>Statut</th><th>Paiement</th><th>Date</th></tr></thead>
      <tbody>
        <?php if (!empty($latest_orders)):
          foreach ($latest_orders as $o): ?>
        <tr>
          <td class="mono" style="color:var(--c-accent);">#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></td>
          <td>
            <div style="font-weight:600;"><?= htmlspecialchars($o['prenom'].' '.$o['nom']) ?></div>
            <div style="font-size:.7rem;color:var(--c-muted);"><?= htmlspecialchars($o['email']) ?></div>
          </td>
          <td class="mono"><?= $o['items'] ?? 0 ?></td>
          <td class="mono" style="color:var(--c-gold);"><?= fbu((float)$o['total_ttc']) ?></td>
          <td><?= badge_order($o['statut']) ?></td>
          <td><?php
            $pc=['paye'=>'b-success','en_attente'=>'b-warning','echoue'=>'b-danger','rembourse'=>'b-secondary'];
            $cl=$pc[$o['statut_paiement']]??'b-secondary';
            echo "<span class='badge {$cl}'>".htmlspecialchars(ucfirst($o['statut_paiement'] ?? '')).'</span>';
          ?></td>
          <td style="color:var(--c-muted);font-size:.72rem;"><?= ago($o['created_at']) ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="7" style="text-align:center;padding:20px;color:var(--c-muted);">Aucune commande</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ═══════════════════ TÉLÉMÉDECINE ═══════════════════ -->
<p class="section-title"><i class="bx bx-video"></i> Télémédecine</p>
<div class="grid g-3" style="margin-bottom:28px;">

  <!-- Stats médecins -->
  <div class="card">
    <div class="card-hd"><h5><span class="dot" style="background:var(--c-blue);"></span> Médecins</h5></div>
    <div class="card-body">
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
        <div style="text-align:center;flex:1;padding:12px;background:rgba(56,189,248,.08);border-radius:10px;">
          <div class="mono" style="font-size:1.6rem;font-weight:800;color:var(--c-blue);"><?= $kpi_telemedecine['medecins'] ?? 0 ?></div>
          <div style="font-size:.7rem;color:var(--c-muted);">Total</div>
        </div>
        <div style="text-align:center;flex:1;padding:12px;background:rgba(0,229,195,.08);border-radius:10px;">
          <div class="mono" style="font-size:1.6rem;font-weight:800;color:var(--c-accent);"><?= $kpi_telemedecine['available'] ?? 0 ?></div>
          <div style="font-size:.7rem;color:var(--c-muted);">Disponibles</div>
        </div>
        <div style="text-align:center;flex:1;padding:12px;background:rgba(255,208,0,.08);border-radius:10px;">
          <div class="mono" style="font-size:1.6rem;font-weight:800;color:var(--c-gold);"><?= $kpi_telemedecine['avg_rating'] ?? '0.0' ?></div>
          <div style="font-size:.7rem;color:var(--c-muted);">Note moy.</div>
        </div>
      </div>
      <?php if (!empty($telemedecine['specialties'])): ?>
      <div style="font-size:.72rem;color:var(--c-muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;">Par spécialité</div>
      <?php foreach (array_slice($telemedecine['specialties'],0,4) as $sp): ?>
      <div class="stat-row">
        <span style="font-size:.8rem;"><?= htmlspecialchars($sp['specialite']) ?></span>
        <span class="badge b-info"><?= $sp['total'] ?> consults</span>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Consultations récentes -->
  <div class="card" style="grid-column:span 2;">
    <div class="card-hd">
      <h5><span class="dot" style="background:var(--c-blue);"></span> Consultations récentes</h5>
      <a href="<?= base_url('Consultations') ?>" class="btn btn-outline btn-sm">Toutes</a>
    </div>
    <div class="scroll-y">
      <table class="tbl">
        <thead><tr><th>Patient</th><th>Médecin</th><th>Spécialité</th><th>Type</th><th>Montant</th><th>Statut</th><th>Date</th></tr></thead>
        <tbody>
          <?php if (!empty($latest_consultations)):
            foreach ($latest_consultations as $c): ?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:.82rem;"><?= htmlspecialchars($c['pat_prenom'].' '.$c['pat_nom']) ?></div>
              <div style="font-size:.7rem;color:var(--c-muted);"><?= htmlspecialchars($c['pat_tel'] ?? '') ?></div>
            </td>
            <td style="font-size:.8rem;">Dr. <?= htmlspecialchars(($c['med_prenom'] ?? '').' '.($c['med_nom'] ?? '')) ?></td>
            <td style="font-size:.75rem;color:var(--c-muted);"><?= htmlspecialchars($c['specialite'] ?? '—') ?></td>
            <td><span class="badge b-secondary"><?= htmlspecialchars($c['type']) ?></span></td>
            <td class="mono" style="font-size:.78rem;"><?= fbu((float)($c['prix_ttc'] ?? 0)) ?></td>
            <td><?= badge_consult($c['statut']) ?></td>
            <td style="color:var(--c-muted);font-size:.72rem;"><?= ago($c['created_at']) ?></td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="7" style="text-align:center;padding:20px;color:var(--c-muted);">Aucune consultation</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Prochaines consultations -->
<?php if (!empty($upcoming_consults)): ?>
<div class="card" style="margin-bottom:28px;">
  <div class="card-hd"><h5><span class="dot" style="background:var(--c-accent);"></span> Prochaines consultations confirmées</h5></div>
  <div class="card-body">
    <div style="display:flex;flex-wrap:wrap;gap:10px;">
      <?php foreach ($upcoming_consults as $uc): ?>
      <div style="flex:1;min-width:200px;padding:12px;background:rgba(255,255,255,.03);border:1px solid var(--c-border);border-radius:10px;">
        <div style="font-weight:600;font-size:.82rem;"><?= htmlspecialchars($uc['pat_prenom'].' '.$uc['pat_nom']) ?></div>
        <div style="font-size:.75rem;color:var(--c-muted);">Dr. <?= htmlspecialchars($uc['med_prenom'] ?? '?') ?> · <?= htmlspecialchars($uc['specialite'] ?? '') ?></div>
        <div style="margin-top:8px;font-family:'IBM Plex Mono',monospace;font-size:.75rem;color:var(--c-accent);"><?= date('d/m/Y H:i', strtotime($uc['date_souhaitee'])) ?></div>
        <?= badge_consult($uc['statut']) ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ VISITEURS & NEWSLETTER ═══════════════════ -->
<p class="section-title"><i class="bx bx-globe"></i> Audience & Communication</p>
<div class="grid g-3" style="margin-bottom:28px;">

  <!-- Visiteurs -->
  <div class="card">
    <div class="card-hd"><h5><span class="dot" style="background:var(--c-teal);"></span> Visiteurs du site</h5></div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
        <div style="background:rgba(0,229,195,.07);border-radius:10px;padding:12px;text-align:center;">
          <div class="mono" style="font-size:1.5rem;font-weight:800;color:var(--c-accent);"><?= number_format($visitor_stats['today'] ?? 0) ?></div>
          <div style="font-size:.7rem;color:var(--c-muted);">Aujourd'hui</div>
        </div>
        <div style="background:rgba(56,189,248,.07);border-radius:10px;padding:12px;text-align:center;">
          <div class="mono" style="font-size:1.5rem;font-weight:800;color:var(--c-blue);"><?= number_format($visitor_stats['unique_today'] ?? 0) ?></div>
          <div style="font-size:.7rem;color:var(--c-muted);">Uniques</div>
        </div>
        <div style="background:rgba(255,140,0,.07);border-radius:10px;padding:12px;text-align:center;">
          <div class="mono" style="font-size:1.5rem;font-weight:800;color:var(--c-orange);"><?= number_format($visitor_stats['yesterday'] ?? 0) ?></div>
          <div style="font-size:.7rem;color:var(--c-muted);">Hier</div>
        </div>
        <div style="background:rgba(255,208,0,.07);border-radius:10px;padding:12px;text-align:center;">
          <?php $trend = $visitor_stats['trend_pct'] ?? 0; $clrT = $trend >= 0 ? 'var(--c-accent)' : 'var(--c-crimson)'; ?>
          <div class="mono" style="font-size:1.5rem;font-weight:800;color:<?= $clrT ?>;"><?= ($trend >= 0 ? '+' : '') . $trend ?>%</div>
          <div style="font-size:.7rem;color:var(--c-muted);">Tendance</div>
        </div>
      </div>
      <?php if (!empty($visitor_stats['top_countries'])): ?>
      <div style="font-size:.72rem;color:var(--c-muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;">Top pays</div>
      <?php foreach ($visitor_stats['top_countries'] as $ctry): ?>
      <div class="stat-row">
        <span style="font-size:.8rem;"><?= htmlspecialchars($ctry['country'] ?? '—') ?></span>
        <b class="mono" style="font-size:.78rem;"><?= $ctry['cnt'] ?></b>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Newsletter -->
  <div class="card">
    <div class="card-hd"><h5><span class="dot" style="background:#FB923C;"></span> Newsletter</h5></div>
    <div class="card-body">
      <div style="margin-bottom:14px;">
        <div class="ch-box ch-200"><canvas id="newsletterMiniChart"></canvas></div>
      </div>
      <div class="stat-row"><span style="color:var(--c-muted);">Abonnés totaux</span><b class="mono" style="color:var(--c-accent);"><?= $newsletter_stats['total'] ?? 0 ?></b></div>
      <div class="stat-row"><span style="color:var(--c-muted);">Emails</span><b><?= $newsletter_stats['total_email'] ?? 0 ?></b></div>
      <div class="stat-row"><span style="color:var(--c-muted);">Numéros tél.</span><b><?= $newsletter_stats['total_phone'] ?? 0 ?></b></div>
      <div class="stat-row"><span style="color:var(--c-muted);">Aujourd'hui</span><b class="pill-g">+<?= $newsletter_stats['today'] ?? 0 ?></b></div>
      <div class="stat-row"><span style="color:var(--c-muted);">Cette semaine</span><b>+<?= $newsletter_stats['week'] ?? 0 ?></b></div>
      <div class="stat-row"><span style="color:var(--c-muted);">Ce mois</span><b>+<?= $newsletter_stats['month'] ?? 0 ?></b></div>
    </div>
  </div>

  <!-- WhatsApp -->
  <div class="card">
    <div class="card-hd"><h5><span class="dot" style="background:#34D399;"></span> WhatsApp</h5></div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:14px;text-align:center;">
        <div style="background:rgba(52,211,153,.08);border-radius:8px;padding:10px;">
          <div class="mono" style="font-size:1.2rem;font-weight:800;color:#34D399;"><?= $whatsapp_stats['sent'] ?? 0 ?></div>
          <div style="font-size:.65rem;color:var(--c-muted);">Envoyés</div>
        </div>
        <div style="background:rgba(56,189,248,.08);border-radius:8px;padding:10px;">
          <div class="mono" style="font-size:1.2rem;font-weight:800;color:var(--c-blue);"><?= $whatsapp_stats['received'] ?? 0 ?></div>
          <div style="font-size:.65rem;color:var(--c-muted);">Reçus</div>
        </div>
        <div style="background:rgba(220,20,60,.08);border-radius:8px;padding:10px;">
          <div class="mono" style="font-size:1.2rem;font-weight:800;color:var(--c-crimson);"><?= $whatsapp_stats['failed'] ?? 0 ?></div>
          <div style="font-size:.65rem;color:var(--c-muted);">Échoués</div>
        </div>
      </div>
      <div class="stat-row"><span style="color:var(--c-muted);">Groupes actifs</span><b><?= $whatsapp_stats['groups'] ?? 0 ?></b></div>
      <div class="stat-row"><span style="color:var(--c-muted);">Membres réseau</span><b class="mono" style="color:#34D399;"><?= number_format($whatsapp_stats['wa_members'] ?? 0) ?></b></div>
      <div class="stat-row"><span style="color:var(--c-muted);">En file d'attente</span><b class="<?= ($whatsapp_stats['queue_pending'] ?? 0) > 0 ? 'pill-r' : '' ?>"><?= $whatsapp_stats['queue_pending'] ?? 0 ?></b></div>
      <div class="stat-row"><span style="color:var(--c-muted);">Blacklistés</span><b><?= $whatsapp_stats['blacklisted'] ?? 0 ?></b></div>
      <div class="stat-row"><span style="color:var(--c-muted);">Templates</span><b><?= $whatsapp_stats['templates'] ?? 0 ?></b></div>
    </div>
  </div>

</div>

<!-- ═══════════════════ RÉSEAUX SOCIAUX ═══════════════════ -->
<?php if (!empty($social_networks)): ?>
<div class="card" style="margin-bottom:28px;">
  <div class="card-hd"><h5><span class="dot" style="background:#F472B6;"></span> Présence sur les réseaux</h5></div>
  <div class="card-body">
    <div style="display:flex;flex-wrap:wrap;gap:10px;">
      <?php foreach ($social_networks as $net):
        $net_colors = ['WhatsApp'=>'#34D399','Telegram'=>'#38BDF8','Facebook'=>'#818CF8','Instagram'=>'#F472B6','TikTok'=>'#fff','YouTube'=>'#F87171'];
        $clr = $net_colors[$net['plateforme']] ?? 'var(--c-accent)';
      ?>
      <div class="net-chip" style="flex:1;min-width:150px;">
        <div style="width:36px;height:36px;border-radius:8px;background:color-mix(in srgb, <?= $clr ?> 15%, transparent);display:flex;align-items:center;justify-content:center;">
          <span style="font-weight:800;font-size:.8rem;color:<?= $clr ?>;"><?= strtoupper(substr($net['plateforme'],0,2)) ?></span>
        </div>
        <div>
          <div style="font-weight:700;font-size:.82rem;"><?= htmlspecialchars($net['plateforme']) ?></div>
          <div class="mono" style="font-size:.75rem;color:<?= $clr ?>;"><?= number_format((int)$net['nombre_participants']) ?> membres</div>
          <div style="font-size:.7rem;color:var(--c-muted);"><?= $net['nombre_groupes'] ?> groupes</div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ UTILISATEURS & COURTIERS & INVESTISSEURS ═══════════════════ -->
<p class="section-title"><i class="bx bx-user-circle"></i> Communauté</p>
<div class="grid g-3" style="margin-bottom:28px;">

  <!-- Derniers utilisateurs -->
  <div class="card">
    <div class="card-hd">
      <h5><span class="dot"></span> Derniers inscrits</h5>
      <a href="<?= base_url('Users') ?>" class="btn btn-outline btn-sm">Voir tout</a>
    </div>
    <div class="scroll-y">
      <?php if (!empty($latest_users)):
        foreach (array_slice($latest_users,0,7) as $u): ?>
      <div style="display:flex;align-items:center;gap:10px;padding:9px 16px;border-bottom:1px solid var(--c-border);">
        <div class="av" style="background:<?= ['#0F766E','#1a8c78','#062C54','#FF8C00'][crc32($u['email'])%4] ?>;">
          <?php if (!empty($u['photo']) && $u['photo'] != 'default-avatar.png'): ?>
            <img src="<?= base_url('uploads/users/'.$u['photo']) ?>" alt="">
          <?php else: ?>
            <?= strtoupper(substr($u['prenom']??'U',0,1)) ?>
          <?php endif; ?>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-weight:600;font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?></div>
          <div style="font-size:.7rem;color:var(--c-muted);"><?= htmlspecialchars($u['type_utilisateur'] ?? '') ?> · <?= ago($u['created_at']) ?></div>
        </div>
        <span class="badge <?= $u['is_active'] ? 'b-success' : 'b-secondary' ?>"><?= $u['is_active'] ? '✓' : '○' ?></span>
      </div>
      <?php endforeach; else: ?>
      <p style="text-align:center;padding:20px;color:var(--c-muted);">Aucun utilisateur</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Investisseurs récents -->
  <div class="card">
    <div class="card-hd">
      <h5><span class="dot" style="background:#A78BFA;"></span> Investisseurs récents</h5>
      <a href="<?= base_url('Investors') ?>" class="btn btn-outline btn-sm">Voir tout</a>
    </div>
    <div class="scroll-y">
      <?php if (!empty($latest_investors)):
        foreach ($latest_investors as $inv): ?>
      <div style="padding:10px 16px;border-bottom:1px solid var(--c-border);">
        <div style="display:flex;justify-content:space-between;align-items:start;">
          <div>
            <div style="font-weight:600;font-size:.82rem;"><?= htmlspecialchars($inv['full_name']) ?></div>
            <div style="font-size:.7rem;color:var(--c-muted);"><?= htmlspecialchars($inv['organization'] ?? '—') ?> · <?= htmlspecialchars($inv['country_name'] ?? '') ?></div>
          </div>
          <span class="badge b-accent"><?= htmlspecialchars($inv['commitment_range'] ?? '—') ?></span>
        </div>
        <div style="margin-top:4px;font-size:.7rem;color:var(--c-muted);"><?= ago($inv['created_at']) ?></div>
      </div>
      <?php endforeach; else: ?>
      <p style="text-align:center;padding:20px;color:var(--c-muted);">Aucun investisseur</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Courtiers récents -->
  <div class="card">
    <div class="card-hd">
      <h5><span class="dot" style="background:var(--c-orange);"></span> Courtiers récents</h5>
      <a href="<?= base_url('Brokers') ?>" class="btn btn-outline btn-sm">Voir tout</a>
    </div>
    <div class="scroll-y">
      <?php if (!empty($latest_brokers)):
        foreach ($latest_brokers as $br): ?>
      <div style="padding:10px 16px;border-bottom:1px solid var(--c-border);">
        <div style="display:flex;justify-content:space-between;align-items:start;">
          <div>
            <div style="font-weight:600;font-size:.82rem;"><?= htmlspecialchars($br['full_name']) ?></div>
            <div style="font-size:.7rem;color:var(--c-muted);"><?= htmlspecialchars($br['firm_name'] ?? '—') ?> · <?= htmlspecialchars($br['country_name'] ?? '') ?></div>
          </div>
          <span class="badge <?= $br['regulatory_status']==='Licensed' ? 'b-success' : 'b-warning' ?>"><?= htmlspecialchars($br['regulatory_status'] ?? '—') ?></span>
        </div>
        <div style="margin-top:4px;font-size:.7rem;color:var(--c-muted);"><?= ago($br['created_at']) ?></div>
      </div>
      <?php endforeach; else: ?>
      <p style="text-align:center;padding:20px;color:var(--c-muted);">Aucun courtier</p>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ═══════════════════ MÉDIAS & CONTACT ═══════════════════ -->
<p class="section-title"><i class="bx bx-image"></i> Médias & Engagement</p>
<div class="grid g-64" style="margin-bottom:28px;">

  <!-- Top médias -->
  <div class="card">
    <div class="card-hd"><h5><span class="dot" style="background:#F472B6;"></span> Top médias (vues)</h5></div>
    <div class="card-body">
      <?php if (!empty($top_medias)):
        foreach ($top_medias as $med): ?>
      <div class="stat-row">
        <div style="display:flex;align-items:center;gap:10px;min-width:0;">
          <div style="width:32px;height:32px;border-radius:6px;background:rgba(244,114,182,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <?php $ti=['audio'=>'bx-music','video'=>'bx-film','image'=>'bx-image','document'=>'bx-file-blank'];
            $ic=$ti[$med['type']]??'bx-file'; ?>
            <i class="bx <?= $ic ?>" style="color:#F472B6;"></i>
          </div>
          <div style="min-width:0;">
            <div style="font-weight:600;font-size:.8rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;"><?= htmlspecialchars($med['titre'] ?? '—') ?></div>
            <div style="font-size:.7rem;color:var(--c-muted);"><?= ucfirst($med['type']) ?></div>
          </div>
        </div>
        <div style="text-align:right;flex-shrink:0;">
          <div class="mono" style="font-size:.8rem;color:var(--c-accent);"><?= number_format($med['views']) ?> vues</div>
          <div style="font-size:.7rem;color:var(--c-muted);"><?= $med['likes'] ?> ♥ · <?= $med['comments'] ?> 💬</div>
        </div>
      </div>
      <?php endforeach; else: ?>
      <p style="text-align:center;padding:20px;color:var(--c-muted);">Aucun média</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Commentaires récents + messages contact -->
  <div style="display:flex;flex-direction:column;gap:16px;">
    <!-- Messages contact -->
    <div class="card">
      <div class="card-hd">
        <h5><span class="dot" style="background:var(--c-crimson);"></span> Messages contact</h5>
        <span class="badge b-danger"><?= $contact_messages['unread'] ?? 0 ?> non lus</span>
      </div>
      <div class="card-body" style="padding:12px 16px;">
        <?php if (!empty($contact_messages['latest'])):
          foreach ($contact_messages['latest'] as $msg): ?>
        <div style="padding:8px 0;border-bottom:1px solid var(--c-border);">
          <div style="display:flex;justify-content:space-between;">
            <span style="font-weight:600;font-size:.8rem;"><?= htmlspecialchars($msg['FullName']) ?></span>
            <span style="font-size:.7rem;color:var(--c-muted);"><?= ago($msg['Date_creation']) ?></span>
          </div>
          <div style="font-size:.75rem;color:var(--c-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($msg['Subject']) ?></div>
        </div>
        <?php endforeach; else: ?>
        <p style="color:var(--c-muted);font-size:.82rem;text-align:center;padding:10px 0;">Aucun message</p>
        <?php endif; ?>
        <div style="margin-top:10px;">
          <a href="<?= base_url('Contact') ?>" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">Voir tous les messages</a>
        </div>
      </div>
    </div>

    <!-- Commentaires médias -->
    <div class="card">
      <div class="card-hd"><h5><span class="dot"></span> Commentaires récents</h5></div>
      <div class="card-body" style="padding:12px 16px;">
        <?php if (!empty($media_engagement['recent_comments'])):
          foreach ($media_engagement['recent_comments'] as $cm): ?>
        <div style="padding:7px 0;border-bottom:1px solid var(--c-border);">
          <div style="display:flex;justify-content:space-between;">
            <span style="font-weight:600;font-size:.78rem;"><?= htmlspecialchars($cm['author_name'] ?? '—') ?></span>
            <span style="font-size:.68rem;color:var(--c-muted);"><?= ago($cm['created_at']) ?></span>
          </div>
          <div style="font-size:.73rem;color:var(--c-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($cm['comment']) ?></div>
        </div>
        <?php endforeach; else: ?>
        <p style="color:var(--c-muted);font-size:.82rem;text-align:center;padding:10px 0;">Aucun commentaire</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<!-- ═══════════════════ ACTIVITÉS & ACTIONS RAPIDES ═══════════════════ -->
<div class="grid g-2" style="margin-bottom:28px;">

  <!-- Activités récentes -->
  <div class="card">
    <div class="card-hd"><h5><span class="dot"></span> Activité récente</h5></div>
    <div class="card-body">
      <div class="timeline scroll-y" style="max-height:300px;">
        <?php if (!empty($recent_activities)):
          foreach (array_slice($recent_activities,0,8) as $act): ?>
        <div class="tl-item">
          <div style="display:flex;justify-content:space-between;align-items:start;">
            <div>
              <div style="font-weight:600;font-size:.8rem;"><?= htmlspecialchars($act['prenom'].' '.$act['nom']) ?></div>
              <div style="font-size:.75rem;color:var(--c-muted);"><?= htmlspecialchars($act['description'] ?? $act['action'] ?? '') ?></div>
            </div>
            <div style="text-align:right;flex-shrink:0;margin-left:10px;">
              <span class="badge b-secondary" style="font-size:.65rem;"><?= htmlspecialchars($act['module'] ?? 'system') ?></span>
              <div style="font-size:.68rem;color:var(--c-muted);margin-top:2px;"><?= ago($act['created_at']) ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; else: ?>
        <p style="color:var(--c-muted);text-align:center;padding:20px;">Aucune activité</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Actions rapides + vérifications + santé système -->
  <div style="display:flex;flex-direction:column;gap:16px;">

    <!-- Actions rapides -->
    <div class="card">
      <div class="card-hd"><h5><span class="dot" style="background:var(--c-gold);"></span> Actions rapides</h5></div>
      <div class="card-body">
        <div class="qa-grid">
          <?php foreach ($quick_actions as $qa): ?>
          <a href="<?= $qa['link'] ?>" class="qa-btn">
            <i class="<?= $qa['icon'] ?>"></i><?= htmlspecialchars($qa['title']) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Éléments en attente -->
    <div class="card">
      <div class="card-hd"><h5><span class="dot" style="background:var(--c-orange);"></span> En attente de traitement</h5></div>
      <div class="card-body" style="padding:12px 16px;">
        <?php
        $pv_items = [
          ['label'=>'Commandes en attente',    'val'=>$pending_verif['orders_pending']??0,    'link'=>base_url('Commandes?statut=en_attente')],
          ['label'=>'Demandes produits',       'val'=>$pending_verif['req_pending']??0,       'link'=>base_url('OrderRequests')],
          ['label'=>'Consultations en attente','val'=>$pending_verif['consults_pending']??0,  'link'=>base_url('Consultations?statut=en_attente')],
          ['label'=>'Messages non lus',        'val'=>$pending_verif['contact_unread']??0,    'link'=>base_url('Contact')],
          ['label'=>'Utilisateurs inactifs',   'val'=>$pending_verif['users_inactive']??0,    'link'=>base_url('Users?active=0')],
          ['label'=>'Emails non vérifiés',     'val'=>$pending_verif['unverified_email']??0,  'link'=>base_url('Users?verified=0')],
        ];
        foreach ($pv_items as $pvi): ?>
        <a href="<?= $pvi['link'] ?>" class="pv-item" style="text-decoration:none;color:inherit;">
          <span style="font-size:.8rem;"><?= $pvi['label'] ?></span>
          <span class="pv-num"><?= $pvi['val'] ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Santé système -->
    <div class="card">
      <div class="card-hd"><h5><span class="dot" style="background:#34D399;"></span> Santé système</h5></div>
      <div class="card-body" style="padding:12px 16px;">
        <div class="stat-row"><span style="color:var(--c-muted);">Connexions réussies auj.</span><b class="mono" style="color:#34D399;"><?= $system_health['logins_today'] ?? 0 ?></b></div>
        <div class="stat-row"><span style="color:var(--c-muted);">Tentatives échouées</span><b class="mono" style="color:var(--c-crimson);"><?= $system_health['failed_logins'] ?? 0 ?></b></div>
        <div class="stat-row"><span style="color:var(--c-muted);">Erreurs aujourd'hui</span><b class="mono" style="color:var(--c-orange);"><?= $system_health['errors_today'] ?? 0 ?></b></div>
        <div class="stat-row"><span style="color:var(--c-muted);">Alertes système</span><b class="mono"><?= $system_health['warnings'] ?? 0 ?></b></div>
        <div class="stat-row"><span style="color:var(--c-muted);">Logs totaux</span><b class="mono"><?= number_format($system_health['total_logs'] ?? 0) ?></b></div>
      </div>
    </div>

  </div>
</div>

<!-- ═══════════════════ FOOTER ═══════════════════ -->
<div style="text-align:center;padding:20px 0;color:var(--c-muted);font-size:.72rem;border-top:1px solid var(--c-border);">
  NUFOTEC BURUNDI · Dashboard Admin · Généré le <?= $generated_at ?> · 
  <span id="realtimeStatus" style="color:var(--c-accent);">● Live</span>
</div>

</div><!-- /.db-content -->
</div><!-- /.db-wrap -->

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
const PAL = ['#00E5C3','#FFD000','#FF8C00','#38BDF8','#F472B6','#A78BFA','#34D399','#FB923C','#60A5FA','#FBBF24'];

function rgba(hex, a=0.15) {
  const r=parseInt(hex.slice(1,3),16), g=parseInt(hex.slice(3,5),16), b=parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${a})`;
}

function chartDefaults() {
  return {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { labels: { color: '#6B8BAD', font: { size: 11 }, boxWidth: 10 } } },
    scales: {
      x: { ticks: { color: '#6B8BAD', font:{size:10} }, grid: { color:'rgba(255,255,255,.04)' } },
      y: { ticks: { color: '#6B8BAD', font:{size:10}, callback: v => v>=1e6?(v/1e6).toFixed(1)+'M':v>=1000?(v/1000).toFixed(0)+'k':v }, grid: { color:'rgba(255,255,255,.04)' } }
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
      borderColor: '#FFD000', backgroundColor: rgba('#FFD000'), borderWidth: 2,
      tension: 0.4, fill: true, pointRadius: 2, pointHoverRadius: 5
    }]
  },
  options: {
    ...chartDefaults(),
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { position: 'top', align: 'end', labels: { color:'#6B8BAD', boxWidth:8, font:{size:11} } } }
  }
});

const chartDataMap = {
  revenue:    { label: 'Revenus (F)',          data: D.revenue,     color: '#FFD000' },
  users:      { label: 'Nouveaux utilisateurs', data: D.users,      color: '#00E5C3' },
  consults:   { label: 'Consultations',         data: D.consults,   color: '#38BDF8' },
  visits:     { label: 'Visites du site',        data: D.visits,     color: '#A78BFA' },
  newsletter: { label: 'Inscriptions newsletter',data: D.newsletter, color: '#FB923C' },
  orderreq:   { label: 'Demandes de commande',  data: D.orderReq,   color: '#F472B6' },
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
      plugins: { legend: { position: 'right', labels: { color:'#6B8BAD', boxWidth:10, font:{size:11}, padding:8 } } }
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
    datasets: [{ label: 'Inscrits', data: D.newsletter.slice(-14), backgroundColor: rgba('#FB923C', 0.6), borderColor: '#FB923C', borderWidth: 1, borderRadius: 4 }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color:'#6B8BAD', font:{size:9} }, grid: { display:false } },
      y: { ticks: { color:'#6B8BAD', font:{size:9} }, grid: { color:'rgba(255,255,255,.04)' } }
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
        x: { ticks:{color:'#6B8BAD',font:{size:9}}, grid:{color:'rgba(255,255,255,.04)'} },
        y: { ticks:{color:'#6B8BAD',font:{size:9}}, grid:{display:false} }
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
    document.getElementById('realtimeStatus').style.color = '#F87171';
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