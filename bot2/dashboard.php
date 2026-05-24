<?php
/**
 * ============================================================
 *  NUFOTEC — Dashboard WhatsApp
 *  Gestion complète : groupes, membres, blacklist, queue, logs
 * ============================================================
 */
declare(strict_types=1);

// ── Configuration ────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_USER',    'nufotec_nufotec');
define('DB_PASS',    'root123');
define('DB_NAME',    'nufotec_db');
define('API_TOKEN',  'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw');
define('API_URL',    'https://gate.whapi.cloud/');
define('DASH_PASS',  'admin123');   // ← Changer ce mot de passe !

session_start();

// ── Auth simple ──────────────────────────────────────────────
if ($_POST['action'] ?? '' === 'login') {
    if ($_POST['password'] === DASH_PASS) {
        $_SESSION['auth'] = true;
    } else {
        $login_error = 'Mot de passe incorrect';
    }
}
if ($_GET['logout'] ?? '' === '1') {
    session_destroy();
    header('Location: dashboard.php');
    exit;
}
$authed = !empty($_SESSION['auth']);

// ── Base de données ──────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]
        );
    }
    return $pdo;
}
function dbq(string $sql, array $p = []): array {
    $st = db()->prepare($sql); $st->execute($p); return $st->fetchAll();
}
function db1(string $sql, array $p = []): ?object {
    $st = db()->prepare($sql); $st->execute($p); $r = $st->fetch(); return $r ?: null;
}
function dbx(string $sql, array $p = []): int {
    $st = db()->prepare($sql); $st->execute($p); return (int)$st->rowCount();
}

// ── API Whapi ────────────────────────────────────────────────
function whapi(string $ep, string $m = 'GET', ?array $d = null): ?array {
    $ch = curl_init(API_URL . ltrim($ep, '/'));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer '.API_TOKEN,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    if ($m === 'POST') { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($d)); }
    elseif ($m !== 'GET') { curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $m); if ($d) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($d)); }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($err || $code >= 400) return null;
    return json_decode((string)$body, true) ?: [];
}

// ── Actions AJAX ─────────────────────────────────────────────
if ($authed && isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $act = $_GET['ajax'];

    // Sync groupes depuis Whapi
    if ($act === 'sync_groups') {
        $data = whapi('groups?count=100');
        if (!$data) { echo json_encode(['ok'=>false,'msg'=>'Erreur API Whapi']); exit; }
        $groups = $data['groups'] ?? $data['data'] ?? [];
        $added = 0; $updated = 0;
        foreach ($groups as $g) {
            $gid  = $g['id'] ?? '';
            $nom  = $g['name'] ?? $g['subject'] ?? 'Groupe';
            if (!$gid) continue;
            $ex = db1('SELECT id FROM groupes_whatsapp WHERE groupe_id=?', [$gid]);
            if ($ex) {
                dbx('UPDATE groupes_whatsapp SET nom=?, updated_at=NOW() WHERE groupe_id=?', [$nom, $gid]);
                $updated++;
            } else {
                dbx('INSERT INTO groupes_whatsapp (groupe_id,nom,actif,created_at,updated_at) VALUES (?,?,1,NOW(),NOW())', [$gid,$nom]);
                $added++;
            }
        }
        echo json_encode(['ok'=>true,'msg'=>"$added ajouté(s), $updated mis à jour",'total'=>count($groups)]);
        exit;
    }

    // Sync membres d'un groupe (ou tous)
    if ($act === 'sync_members') {
        $gid_filter = $_GET['gid'] ?? null;
        $groups = $gid_filter
            ? [db1('SELECT groupe_id,nom FROM groupes_whatsapp WHERE groupe_id=?', [$gid_filter])]
            : dbq('SELECT groupe_id,nom FROM groupes_whatsapp WHERE actif=1');
        $total = 0; $errors = [];
        foreach ($groups as $g) {
            if (!$g) continue;
            $data = whapi("groups/{$g->groupe_id}/participants");
            if (!$data) { $errors[] = $g->groupe_id; continue; }
            $parts = $data['participants'] ?? $data['members'] ?? [];
            foreach ($parts as $p) {
                $phone_raw = is_array($p) ? ($p['id'] ?? $p['phone'] ?? '') : $p;
                $phone_fmt = preg_replace('/\D/','',preg_replace('/@.*/','', $phone_raw));
                $is_admin  = isset($p['isAdmin']) ? (int)$p['isAdmin'] : (isset($p['admin']) ? 1 : 0);
                $name      = $p['name'] ?? $p['pushName'] ?? null;
                if (!$phone_fmt) continue;
                dbx(
                    'INSERT INTO whatsapp_participants
                     (groupe_id,phone,phone_formatted,is_admin,violation_count,profile_name,synced_at,created_at,updated_at)
                     VALUES (?,?,?,?,0,?,NOW(),NOW(),NOW())
                     ON DUPLICATE KEY UPDATE
                       is_admin=VALUES(is_admin),
                       profile_name=COALESCE(VALUES(profile_name),profile_name),
                       synced_at=NOW(), updated_at=NOW()',
                    [$g->groupe_id,$phone_raw,$phone_fmt,$is_admin,$name]
                );
                $total++;
            }
        }
        $msg = "$total membre(s) synchronisé(s)";
        if ($errors) $msg .= ' · Erreurs: '.implode(', ',$errors);
        echo json_encode(['ok'=>true,'msg'=>$msg]);
        exit;
    }

    // Toggle actif/inactif groupe
    if ($act === 'toggle_group') {
        $gid = $_GET['gid'] ?? '';
        $g = db1('SELECT actif FROM groupes_whatsapp WHERE groupe_id=?', [$gid]);
        if ($g) { dbx('UPDATE groupes_whatsapp SET actif=? WHERE groupe_id=?', [$g->actif?0:1,$gid]); }
        echo json_encode(['ok'=>true,'actif'=>$g?($g->actif?0:1):0]);
        exit;
    }

    // Blacklist add/remove
    if ($act === 'blacklist_add') {
        $phone = preg_replace('/\D/','', $_GET['phone']??'');
        $reason = $_GET['reason'] ?? 'Ajouté manuellement';
        if ($phone) dbx('INSERT INTO whatsapp_blacklist (phone_number,reason,created_at) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE reason=VALUES(reason)',[$phone,$reason]);
        echo json_encode(['ok'=>true]);
        exit;
    }
    if ($act === 'blacklist_remove') {
        $phone = preg_replace('/\D/','', $_GET['phone']??'');
        dbx('DELETE FROM whatsapp_blacklist WHERE phone_number=?',[$phone]);
        echo json_encode(['ok'=>true]);
        exit;
    }

    // Reset violations
    if ($act === 'reset_violations') {
        $phone = preg_replace('/\D/','', $_GET['phone']??'');
        dbx('UPDATE whatsapp_participants SET violation_count=0 WHERE phone_formatted=?',[$phone]);
        echo json_encode(['ok'=>true]);
        exit;
    }

    // Vider queue
    if ($act === 'clear_queue') {
        $status = $_GET['status'] ?? 'completed';
        dbx("DELETE FROM whatsapp_queue WHERE status=?",[$status]);
        echo json_encode(['ok'=>true]);
        exit;
    }

    // Données dashboard JSON
    if ($act === 'stats') {
        $stats = [
            'groups'       => db1('SELECT COUNT(*) c FROM groupes_whatsapp')->c ?? 0,
            'groups_actif' => db1('SELECT COUNT(*) c FROM groupes_whatsapp WHERE actif=1')->c ?? 0,
            'members'      => db1('SELECT COUNT(*) c FROM whatsapp_participants')->c ?? 0,
            'blacklist'    => db1('SELECT COUNT(*) c FROM whatsapp_blacklist')->c ?? 0,
            'queue_pending'=> db1('SELECT COUNT(*) c FROM whatsapp_queue WHERE status=\'pending\'')->c ?? 0,
            'queue_failed' => db1('SELECT COUNT(*) c FROM whatsapp_queue WHERE status=\'failed\'')->c ?? 0,
            'logs_today'   => db1('SELECT COUNT(*) c FROM whatsapp_logs WHERE DATE(created_at)=CURDATE()')->c ?? 0,
            'violations'   => db1('SELECT COUNT(*) c FROM whatsapp_participants WHERE violation_count>0')->c ?? 0,
        ];
        echo json_encode($stats);
        exit;
    }

    // Liste groupes
    if ($act === 'list_groups') {
        $groups = dbq('SELECT g.*, (SELECT COUNT(*) FROM whatsapp_participants p WHERE p.groupe_id=g.groupe_id) as nb_membres FROM groupes_whatsapp g ORDER BY g.nom');
        echo json_encode($groups);
        exit;
    }

    // Liste membres
    if ($act === 'list_members') {
        $gid = $_GET['gid'] ?? '';
        $search = '%'.($_GET['search']??'').'%';
        $sql = 'SELECT p.*, g.nom as groupe_nom FROM whatsapp_participants p
                LEFT JOIN groupes_whatsapp g ON g.groupe_id=p.groupe_id
                WHERE (p.phone_formatted LIKE ? OR p.profile_name LIKE ?)';
        $params = [$search,$search];
        if ($gid) { $sql .= ' AND p.groupe_id=?'; $params[] = $gid; }
        $sql .= ' ORDER BY p.violation_count DESC, p.updated_at DESC LIMIT 200';
        echo json_encode(dbq($sql,$params));
        exit;
    }

    // Liste blacklist
    if ($act === 'list_blacklist') {
        echo json_encode(dbq('SELECT * FROM whatsapp_blacklist ORDER BY created_at DESC LIMIT 100'));
        exit;
    }

    // Liste queue
    if ($act === 'list_queue') {
        echo json_encode(dbq('SELECT * FROM whatsapp_queue ORDER BY created_at DESC LIMIT 100'));
        exit;
    }

    // Liste logs
    if ($act === 'list_logs') {
        echo json_encode(dbq('SELECT * FROM whatsapp_logs ORDER BY created_at DESC LIMIT 150'));
        exit;
    }

    // Liste security logs
    if ($act === 'list_security') {
        echo json_encode(dbq('SELECT * FROM whatsapp_security_logs ORDER BY created_at DESC LIMIT 100'));
        exit;
    }

    echo json_encode(['ok'=>false,'msg'=>'Action inconnue']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NUFOTEC · WhatsApp Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:       #0b0e14;
  --bg2:      #111520;
  --bg3:      #171c28;
  --border:   #1e2535;
  --border2:  #2a3347;
  --green:    #00e5a0;
  --green2:   #00c486;
  --red:      #ff4d6a;
  --amber:    #ffb547;
  --blue:     #4d9fff;
  --purple:   #a78bfa;
  --text:     #e2e8f4;
  --text2:    #7c8ba1;
  --text3:    #4a566a;
  --font:     'Syne', sans-serif;
  --mono:     'DM Mono', monospace;
  --radius:   10px;
  --shadow:   0 4px 24px rgba(0,0,0,.4);
}

body {
  font-family: var(--font);
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  overflow-x: hidden;
}

/* ─── LOGIN ─────────────────────────────────── */
.login-wrap {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
  background-image:
    radial-gradient(ellipse at 20% 50%, rgba(0,229,160,.06) 0%, transparent 60%),
    radial-gradient(ellipse at 80% 20%, rgba(77,159,255,.05) 0%, transparent 50%);
}
.login-box {
  background: var(--bg2);
  border: 1px solid var(--border2);
  border-radius: 16px;
  padding: 48px 40px;
  width: 360px;
  box-shadow: var(--shadow);
}
.login-logo {
  font-size: 13px;
  font-weight: 700;
  letter-spacing: .2em;
  color: var(--green);
  text-transform: uppercase;
  margin-bottom: 8px;
}
.login-title { font-size: 28px; font-weight: 800; margin-bottom: 32px; }
.login-box input {
  width: 100%;
  background: var(--bg3);
  border: 1px solid var(--border2);
  border-radius: var(--radius);
  padding: 12px 16px;
  color: var(--text);
  font-family: var(--mono);
  font-size: 14px;
  outline: none;
  margin-bottom: 16px;
  transition: border-color .2s;
}
.login-box input:focus { border-color: var(--green); }
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: var(--radius);
  font-family: var(--font);
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  border: none;
  transition: all .18s;
  white-space: nowrap;
  text-decoration: none;
}
.btn-green  { background: var(--green); color: #000; }
.btn-green:hover  { background: var(--green2); transform: translateY(-1px); }
.btn-red    { background: rgba(255,77,106,.15); color: var(--red); border: 1px solid rgba(255,77,106,.3); }
.btn-red:hover    { background: rgba(255,77,106,.25); }
.btn-blue   { background: rgba(77,159,255,.15); color: var(--blue); border: 1px solid rgba(77,159,255,.3); }
.btn-blue:hover   { background: rgba(77,159,255,.25); }
.btn-ghost  { background: transparent; color: var(--text2); border: 1px solid var(--border2); }
.btn-ghost:hover  { border-color: var(--border2); color: var(--text); background: var(--bg3); }
.btn-amber  { background: rgba(255,181,71,.15); color: var(--amber); border: 1px solid rgba(255,181,71,.3); }
.btn-sm { padding: 6px 12px; font-size: 12px; }
.btn-full { width: 100%; justify-content: center; }
.err { color: var(--red); font-size: 13px; margin-bottom: 12px; }

/* ─── LAYOUT ─────────────────────────────────── */
.shell { display: flex; min-height: 100vh; }

/* Sidebar */
.sidebar {
  width: 220px;
  min-width: 220px;
  background: var(--bg2);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0;
  height: 100vh;
  z-index: 100;
}
.sidebar-logo {
  padding: 24px 20px 20px;
  border-bottom: 1px solid var(--border);
}
.sidebar-logo .brand { font-size: 18px; font-weight: 800; letter-spacing: -.5px; }
.sidebar-logo .sub { font-size: 11px; color: var(--green); font-weight: 600; letter-spacing: .15em; text-transform: uppercase; margin-top: 2px; }
.nav { flex: 1; padding: 16px 12px; display: flex; flex-direction: column; gap: 2px; }
.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: var(--text2);
  cursor: pointer;
  transition: all .15s;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
}
.nav-item:hover { color: var(--text); background: var(--bg3); }
.nav-item.active { color: var(--text); background: var(--bg3); border-left: 2px solid var(--green); }
.nav-item .ico { font-size: 16px; width: 20px; text-align: center; }
.nav-badge {
  margin-left: auto;
  background: var(--red);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 20px;
  min-width: 18px;
  text-align: center;
}
.sidebar-footer {
  padding: 16px 12px;
  border-top: 1px solid var(--border);
}

/* Main */
.main {
  margin-left: 220px;
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}
.topbar {
  background: var(--bg2);
  border-bottom: 1px solid var(--border);
  padding: 16px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 50;
}
.topbar-title { font-size: 20px; font-weight: 800; }
.topbar-actions { display: flex; gap: 10px; align-items: center; }

.content { padding: 28px; flex: 1; }

/* Page sections */
.page { display: none; }
.page.active { display: block; }

/* ─── STATS CARDS ─────────────────────────────── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 28px;
}
.stat-card {
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px;
  position: relative;
  overflow: hidden;
  transition: border-color .2s;
}
.stat-card:hover { border-color: var(--border2); }
.stat-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 2px;
}
.stat-card.green::before { background: var(--green); }
.stat-card.blue::before  { background: var(--blue);  }
.stat-card.red::before   { background: var(--red);   }
.stat-card.amber::before { background: var(--amber); }
.stat-card.purple::before{ background: var(--purple);}
.stat-label { font-size: 11px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--text2); margin-bottom: 10px; }
.stat-val { font-size: 36px; font-weight: 800; line-height: 1; font-variant-numeric: tabular-nums; }
.stat-val.green  { color: var(--green); }
.stat-val.blue   { color: var(--blue);  }
.stat-val.red    { color: var(--red);   }
.stat-val.amber  { color: var(--amber); }
.stat-val.purple { color: var(--purple);}
.stat-sub { font-size: 12px; color: var(--text3); margin-top: 6px; }

/* ─── PANELS ─────────────────────────────────── */
.panel {
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  margin-bottom: 20px;
}
.panel-header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}
.panel-title { font-size: 14px; font-weight: 700; }
.panel-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

/* ─── TABLE ─────────────────────────────────── */
.tbl-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th {
  text-align: left;
  padding: 10px 16px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: var(--text2);
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
}
td {
  padding: 11px 16px;
  border-bottom: 1px solid var(--border);
  vertical-align: middle;
  color: var(--text);
}
tr:last-child td { border-bottom: none; }
tr:hover td { background: rgba(255,255,255,.02); }
.mono { font-family: var(--mono); font-size: 12px; color: var(--text2); }
.empty-row td { text-align: center; color: var(--text3); padding: 32px; }

/* ─── BADGES ─────────────────────────────────── */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  font-family: var(--mono);
}
.badge-green  { background: rgba(0,229,160,.12); color: var(--green); }
.badge-red    { background: rgba(255,77,106,.12); color: var(--red);  }
.badge-amber  { background: rgba(255,181,71,.12); color: var(--amber);}
.badge-blue   { background: rgba(77,159,255,.12); color: var(--blue); }
.badge-gray   { background: rgba(124,139,161,.12); color: var(--text2);}
.badge-purple { background: rgba(167,139,250,.12); color: var(--purple);}

/* ─── SEARCH ─────────────────────────────────── */
.search-input {
  background: var(--bg3);
  border: 1px solid var(--border2);
  border-radius: 8px;
  padding: 7px 12px;
  color: var(--text);
  font-family: var(--mono);
  font-size: 12px;
  outline: none;
  width: 200px;
  transition: border-color .2s;
}
.search-input:focus { border-color: var(--green); }

/* ─── TOAST ─────────────────────────────────── */
#toast {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 8px;
  pointer-events: none;
}
.toast-item {
  background: var(--bg3);
  border: 1px solid var(--border2);
  border-radius: var(--radius);
  padding: 12px 18px;
  font-size: 13px;
  font-weight: 600;
  max-width: 320px;
  box-shadow: var(--shadow);
  animation: toastIn .25s ease;
  pointer-events: all;
}
.toast-item.ok  { border-left: 3px solid var(--green); }
.toast-item.err { border-left: 3px solid var(--red);   }
@keyframes toastIn { from { transform: translateX(20px); opacity:0; } to { transform: none; opacity:1; } }

/* ─── LOADER ─────────────────────────────────── */
.spin {
  display: inline-block;
  width: 14px; height: 14px;
  border: 2px solid var(--border2);
  border-top-color: var(--green);
  border-radius: 50%;
  animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── MODAL ─────────────────────────────────── */
.modal-bg {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.65);
  z-index: 500;
  display: flex; align-items: center; justify-content: center;
  display: none;
}
.modal-bg.open { display: flex; }
.modal {
  background: var(--bg2);
  border: 1px solid var(--border2);
  border-radius: 14px;
  padding: 28px;
  width: 440px;
  max-width: 95vw;
  box-shadow: var(--shadow);
}
.modal h3 { font-size: 18px; font-weight: 800; margin-bottom: 16px; }
.modal label { display: block; font-size: 12px; color: var(--text2); margin-bottom: 6px; font-weight: 600; }
.modal input, .modal select {
  width: 100%;
  background: var(--bg3);
  border: 1px solid var(--border2);
  border-radius: 8px;
  padding: 10px 14px;
  color: var(--text);
  font-family: var(--mono);
  font-size: 13px;
  outline: none;
  margin-bottom: 14px;
}
.modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px; }

/* ─── GROUP CARDS ─────────────────────────────── */
.group-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 14px;
}
.group-card {
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 18px;
  transition: border-color .2s;
}
.group-card:hover { border-color: var(--border2); }
.group-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
.group-name { font-size: 14px; font-weight: 700; word-break: break-word; }
.group-id { font-family: var(--mono); font-size: 11px; color: var(--text3); margin-top: 3px; }
.group-footer { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 12px; }

/* Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: var(--bg); }
::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }

/* Responsive */
@media (max-width: 768px) {
  .sidebar { width: 60px; min-width: 60px; }
  .sidebar .brand, .sidebar .sub, .nav-item span, .sidebar-footer .btn span { display: none; }
  .main { margin-left: 60px; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
</head>
<body>

<?php if (!$authed): ?>
<!-- ═══════════════════════════════ LOGIN ═══════════════════════════════ -->
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo">NUFOTEC</div>
    <div class="login-title">WhatsApp<br>Dashboard</div>
    <?php if (!empty($login_error)): ?>
      <div class="err"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="action" value="login">
      <input type="password" name="password" placeholder="Mot de passe" autofocus autocomplete="current-password">
      <button type="submit" class="btn btn-green btn-full">Connexion →</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ═══════════════════════════════ APP ═══════════════════════════════ -->
<div class="shell">

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="brand">NUFOTEC</div>
    <div class="sub">WhatsApp</div>
  </div>
  <nav class="nav">
    <button class="nav-item active" onclick="goPage('overview')">
      <span class="ico">◈</span><span>Vue d'ensemble</span>
    </button>
    <button class="nav-item" onclick="goPage('groups')">
      <span class="ico">⬡</span><span>Groupes</span>
    </button>
    <button class="nav-item" onclick="goPage('members')">
      <span class="ico">◉</span><span>Membres</span>
    </button>
    <button class="nav-item" onclick="goPage('blacklist')">
      <span class="ico">⊘</span><span>Blacklist</span>
      <span class="nav-badge" id="nb-blacklist" style="display:none">0</span>
    </button>
    <button class="nav-item" onclick="goPage('queue')">
      <span class="ico">◎</span><span>File d'envoi</span>
      <span class="nav-badge" id="nb-queue" style="display:none">0</span>
    </button>
    <button class="nav-item" onclick="goPage('logs')">
      <span class="ico">≡</span><span>Logs</span>
    </button>
    <button class="nav-item" onclick="goPage('security')">
      <span class="ico">⚑</span><span>Sécurité</span>
    </button>
  </nav>
  <div class="sidebar-footer">
    <a href="?logout=1" class="btn btn-ghost btn-sm btn-full">
      <span>⏻</span><span>Déconnexion</span>
    </a>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div class="topbar-title" id="page-title">Vue d'ensemble</div>
    <div class="topbar-actions">
      <button class="btn btn-green" onclick="syncGroups()">
        <span>⬡</span> Sync groupes
      </button>
      <button class="btn btn-blue" onclick="syncMembers(null)">
        <span>◉</span> Sync membres admin
      </button>
    </div>
  </div>

  <div class="content">

    <!-- ── PAGE : VUE D'ENSEMBLE ──────────────────────────── -->
    <div class="page active" id="page-overview">
      <div class="stats-grid">
        <div class="stat-card green">
          <div class="stat-label">Groupes actifs</div>
          <div class="stat-val green" id="s-groups">—</div>
          <div class="stat-sub" id="s-groups-sub">sur — au total</div>
        </div>
        <div class="stat-card blue">
          <div class="stat-label">Membres</div>
          <div class="stat-val blue" id="s-members">—</div>
          <div class="stat-sub">synchronisés</div>
        </div>
        <div class="stat-card red">
          <div class="stat-label">Blacklist</div>
          <div class="stat-val red" id="s-blacklist">—</div>
          <div class="stat-sub">numéros bloqués</div>
        </div>
        <div class="stat-card amber">
          <div class="stat-label">File d'attente</div>
          <div class="stat-val amber" id="s-queue">—</div>
          <div class="stat-sub" id="s-queue-sub">— en échec</div>
        </div>
        <div class="stat-card purple">
          <div class="stat-label">Violations</div>
          <div class="stat-val purple" id="s-violations">—</div>
          <div class="stat-sub">membres signalés</div>
        </div>
        <div class="stat-card blue">
          <div class="stat-label">Logs aujourd'hui</div>
          <div class="stat-val blue" id="s-logs">—</div>
          <div class="stat-sub">messages traités</div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">Actions rapides</div>
        </div>
        <div style="padding:20px;display:flex;gap:12px;flex-wrap:wrap;">
          <button class="btn btn-green" onclick="syncGroups()">
            ⬡ &nbsp;Importer tous les groupes Whapi
          </button>
          <button class="btn btn-blue" onclick="syncMembers(null)">
            ◉ &nbsp;Sync membres (groupes où je suis admin)
          </button>
          <button class="btn btn-ghost" onclick="loadStats()">
            ↻ &nbsp;Actualiser les stats
          </button>
          <button class="btn btn-amber" onclick="openBlacklistModal()">
            ⊘ &nbsp;Ajouter à la blacklist
          </button>
        </div>
      </div>

      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">Dernières violations</div>
          <button class="btn btn-ghost btn-sm" onclick="goPage('security')">Voir tout →</button>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead><tr>
              <th>Numéro</th><th>Type</th><th>Raison</th><th>Date</th>
            </tr></thead>
            <tbody id="overview-security">
              <tr class="empty-row"><td colspan="4"><span class="spin"></span></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── PAGE : GROUPES ──────────────────────────────────── -->
    <div class="page" id="page-groups">
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">Groupes WhatsApp</div>
          <div class="panel-actions">
            <button class="btn btn-green" onclick="syncGroups()">⬡ Sync depuis Whapi</button>
            <button class="btn btn-blue btn-sm" onclick="loadGroups()">↻ Actualiser</button>
          </div>
        </div>
        <div id="groups-grid" class="group-grid" style="padding:20px;">
          <div style="color:var(--text3);font-size:13px;">Chargement…</div>
        </div>
      </div>
    </div>

    <!-- ── PAGE : MEMBRES ─────────────────────────────────── -->
    <div class="page" id="page-members">
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">Membres</div>
          <div class="panel-actions">
            <input class="search-input" type="text" id="member-search" placeholder="Rechercher…" oninput="loadMembers()">
            <select class="search-input" id="member-group" style="width:auto;" onchange="loadMembers()">
              <option value="">Tous les groupes</option>
            </select>
            <button class="btn btn-blue btn-sm" onclick="syncMembers(null)">↻ Sync admin</button>
          </div>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead><tr>
              <th>Téléphone</th><th>Nom</th><th>Groupe</th><th>Rôle</th>
              <th>Violations</th><th>Actions</th>
            </tr></thead>
            <tbody id="members-tbody">
              <tr class="empty-row"><td colspan="6"><span class="spin"></span></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── PAGE : BLACKLIST ───────────────────────────────── -->
    <div class="page" id="page-blacklist">
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">Blacklist</div>
          <div class="panel-actions">
            <button class="btn btn-amber" onclick="openBlacklistModal()">+ Ajouter</button>
            <button class="btn btn-ghost btn-sm" onclick="loadBlacklist()">↻</button>
          </div>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>Numéro</th><th>Raison</th><th>Date</th><th>Action</th></tr></thead>
            <tbody id="blacklist-tbody">
              <tr class="empty-row"><td colspan="4"><span class="spin"></span></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── PAGE : QUEUE ───────────────────────────────────── -->
    <div class="page" id="page-queue">
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">File d'envoi</div>
          <div class="panel-actions">
            <button class="btn btn-red btn-sm" onclick="if(confirm('Vider les messages complétés ?')) api('clear_queue','status=completed').then(()=>{toast('Nettoyé','ok');loadQueue()})">🗑 Vider complétés</button>
            <button class="btn btn-ghost btn-sm" onclick="loadQueue()">↻</button>
          </div>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead><tr>
              <th>Type</th><th>Cible</th><th>Contenu</th>
              <th>Statut</th><th>Tentatives</th><th>Créé</th>
            </tr></thead>
            <tbody id="queue-tbody">
              <tr class="empty-row"><td colspan="6"><span class="spin"></span></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── PAGE : LOGS ────────────────────────────────────── -->
    <div class="page" id="page-logs">
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">Logs de messages</div>
          <button class="btn btn-ghost btn-sm" onclick="loadLogs()">↻</button>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead><tr>
              <th>Téléphone</th><th>Type</th><th>Contenu</th>
              <th>Statut</th><th>Date</th>
            </tr></thead>
            <tbody id="logs-tbody">
              <tr class="empty-row"><td colspan="5"><span class="spin"></span></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── PAGE : SÉCURITÉ ───────────────────────────────── -->
    <div class="page" id="page-security">
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">Logs de sécurité</div>
          <button class="btn btn-ghost btn-sm" onclick="loadSecurity()">↻</button>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead><tr>
              <th>Expéditeur</th><th>Action</th><th>Raison</th>
              <th>Groupe</th><th>Date</th>
            </tr></thead>
            <tbody id="security-tbody">
              <tr class="empty-row"><td colspan="5"><span class="spin"></span></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /shell -->

<!-- MODAL Blacklist -->
<div class="modal-bg" id="modal-blacklist">
  <div class="modal">
    <h3>⊘ Ajouter à la blacklist</h3>
    <label>Numéro de téléphone (chiffres uniquement)</label>
    <input type="text" id="bl-phone" placeholder="ex: 25779666439">
    <label>Raison</label>
    <input type="text" id="bl-reason" placeholder="Spam, publicité…" value="Ajouté manuellement">
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal()">Annuler</button>
      <button class="btn btn-red" onclick="doBlacklistAdd()">⊘ Blacklister</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div id="toast"></div>

<script>
// ─── Utilitaires ──────────────────────────────────────────
const $ = id => document.getElementById(id);
const pages = {
  overview:  { el: 'page-overview',  title: 'Vue d\'ensemble',  load: loadOverview },
  groups:    { el: 'page-groups',    title: 'Groupes',          load: loadGroups   },
  members:   { el: 'page-members',   title: 'Membres',          load: loadMembersPage},
  blacklist: { el: 'page-blacklist', title: 'Blacklist',        load: loadBlacklist},
  queue:     { el: 'page-queue',     title: 'File d\'envoi',    load: loadQueue    },
  logs:      { el: 'page-logs',      title: 'Logs',             load: loadLogs     },
  security:  { el: 'page-security',  title: 'Sécurité',        load: loadSecurity },
};

let currentPage = 'overview';

function goPage(name) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const pg = pages[name];
  if (!pg) return;
  $(pg.el).classList.add('active');
  $('page-title').textContent = pg.title;
  document.querySelectorAll('.nav-item').forEach(n => {
    if (n.getAttribute('onclick')?.includes(`'${name}'`)) n.classList.add('active');
  });
  currentPage = name;
  pg.load();
}

async function api(action, params='') {
  const sep = params ? '&' : '';
  const r = await fetch(`?ajax=${action}${sep}${params}`);
  return r.json();
}

function toast(msg, type='ok') {
  const el = document.createElement('div');
  el.className = `toast-item ${type}`;
  el.textContent = msg;
  $('toast').appendChild(el);
  setTimeout(() => el.remove(), 4000);
}

function esc(s) {
  if (s == null) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function fmtDate(s) {
  if (!s) return '—';
  return s.replace('T',' ').substring(0,16);
}
function shortText(s, n=40) {
  if (!s) return '—';
  return s.length > n ? s.substring(0,n)+'…' : s;
}

// ─── Stats ────────────────────────────────────────────────
async function loadStats() {
  const d = await api('stats');
  $('s-groups').textContent     = d.groups_actif;
  $('s-groups-sub').textContent = `sur ${d.groups} au total`;
  $('s-members').textContent    = d.members;
  $('s-blacklist').textContent  = d.blacklist;
  $('s-queue').textContent      = d.queue_pending;
  $('s-queue-sub').textContent  = `${d.queue_failed} en échec`;
  $('s-violations').textContent = d.violations;
  $('s-logs').textContent       = d.logs_today;
  // badges sidebar
  if (d.blacklist > 0) { $('nb-blacklist').textContent = d.blacklist; $('nb-blacklist').style.display = ''; }
  if (d.queue_pending > 0) { $('nb-queue').textContent = d.queue_pending; $('nb-queue').style.display = ''; }
}

// ─── Vue d'ensemble ───────────────────────────────────────
async function loadOverview() {
  loadStats();
  const logs = await api('list_security');
  const tbody = $('overview-security');
  if (!logs.length) {
    tbody.innerHTML = '<tr class="empty-row"><td colspan="4">Aucune violation</td></tr>';
    return;
  }
  tbody.innerHTML = logs.slice(0,8).map(l => `
    <tr>
      <td class="mono">${esc(l.sender)}</td>
      <td><span class="badge badge-red">${esc(l.action_type)}</span></td>
      <td>${esc(shortText(l.reason))}</td>
      <td class="mono">${fmtDate(l.created_at)}</td>
    </tr>`).join('');
}

// ─── Groupes ──────────────────────────────────────────────
async function syncGroups() {
  const btn = event?.currentTarget;
  const orig = btn?.innerHTML;
  if (btn) btn.innerHTML = '<span class="spin"></span> Import…';
  toast('Synchronisation des groupes…');
  const d = await api('sync_groups');
  if (btn) btn.innerHTML = orig || '⬡ Sync groupes';
  if (d.ok) {
    toast(`✓ ${d.msg} (${d.total} groupes Whapi)`, 'ok');
    if (currentPage === 'groups') loadGroups();
    loadStats();
  } else {
    toast('✗ ' + d.msg, 'err');
  }
}

async function syncMembers(gid) {
  const param = gid ? `gid=${encodeURIComponent(gid)}` : '';
  toast('Synchronisation des membres…');
  const d = await api('sync_members', param);
  if (d.ok) {
    toast('✓ ' + d.msg, 'ok');
    if (currentPage === 'members') loadMembers();
    if (currentPage === 'groups') loadGroups();
    loadStats();
  } else {
    toast('✗ ' + d.msg, 'err');
  }
}

async function loadGroups() {
  const groups = await api('list_groups');
  const container = $('groups-grid');
  if (!groups.length) {
    container.innerHTML = `<div style="color:var(--text3);font-size:13px;padding:8px;">
      Aucun groupe. Cliquez sur <strong style="color:var(--green)">Sync depuis Whapi</strong>.
    </div>`;
    return;
  }
  container.innerHTML = groups.map(g => `
    <div class="group-card">
      <div class="group-card-top">
        <div>
          <div class="group-name">${esc(g.nom || 'Sans nom')}</div>
          <div class="group-id">${esc(g.groupe_id)}</div>
        </div>
        <span class="badge ${g.actif=='1'?'badge-green':'badge-gray'}">${g.actif=='1'?'Actif':'Inactif'}</span>
      </div>
      <div style="font-size:12px;color:var(--text2);">
        <span class="badge badge-blue">${g.nb_membres} membres</span>
      </div>
      <div class="group-footer">
        <button class="btn btn-ghost btn-sm" onclick="syncMembers('${esc(g.groupe_id)}')">↻ Membres</button>
        <button class="btn btn-sm ${g.actif=='1'?'btn-red':'btn-green'}"
          onclick="toggleGroup('${esc(g.groupe_id)}',this)">
          ${g.actif=='1'?'⊘ Désactiver':'✓ Activer'}
        </button>
      </div>
    </div>`).join('');
}

async function toggleGroup(gid, btn) {
  const d = await api('toggle_group', `gid=${encodeURIComponent(gid)}`);
  if (d.ok) { toast('Groupe mis à jour', 'ok'); loadGroups(); loadStats(); }
}

// ─── Membres ──────────────────────────────────────────────
async function loadMembersPage() {
  // Charger les groupes pour le filtre
  const groups = await api('list_groups');
  const sel = $('member-group');
  const cur = sel.value;
  sel.innerHTML = '<option value="">Tous les groupes</option>' +
    groups.map(g => `<option value="${esc(g.groupe_id)}" ${g.groupe_id===cur?'selected':''}>${esc(g.nom||g.groupe_id)}</option>`).join('');
  loadMembers();
}

async function loadMembers() {
  const search = $('member-search')?.value || '';
  const gid    = $('member-group')?.value || '';
  const params = `search=${encodeURIComponent(search)}&gid=${encodeURIComponent(gid)}`;
  const members = await api('list_members', params);
  const tbody = $('members-tbody');
  if (!members.length) {
    tbody.innerHTML = '<tr class="empty-row"><td colspan="6">Aucun membre trouvé</td></tr>';
    return;
  }
  tbody.innerHTML = members.map(m => {
    const vio = parseInt(m.violation_count)||0;
    const vioBadge = vio > 0
      ? `<span class="badge badge-red">${vio}</span>`
      : `<span class="badge badge-gray">0</span>`;
    return `<tr>
      <td class="mono">${esc(m.phone_formatted)}</td>
      <td>${esc(m.profile_name||'—')}</td>
      <td style="font-size:12px;color:var(--text2);">${esc(shortText(m.groupe_nom||m.groupe_id,25))}</td>
      <td>
        ${m.is_admin=='1' ? '<span class="badge badge-amber">Admin</span>' : '<span class="badge badge-gray">Membre</span>'}
      </td>
      <td>${vioBadge}</td>
      <td>
        <div style="display:flex;gap:6px;">
          ${vio > 0 ? `<button class="btn btn-ghost btn-sm" onclick="resetViolations('${esc(m.phone_formatted)}',this)">↺ Reset</button>` : ''}
          <button class="btn btn-red btn-sm" onclick="blacklistPhone('${esc(m.phone_formatted)}')">⊘</button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

async function resetViolations(phone, btn) {
  const d = await api('reset_violations', `phone=${encodeURIComponent(phone)}`);
  if (d.ok) { toast('Violations réinitialisées', 'ok'); loadMembers(); }
}

async function blacklistPhone(phone) {
  if (!confirm(`Blacklister ${phone} ?`)) return;
  const d = await api('blacklist_add', `phone=${encodeURIComponent(phone)}&reason=Blacklist%20manuel%20depuis%20dashboard`);
  if (d.ok) { toast('⊘ ' + phone + ' blacklisté', 'ok'); loadMembers(); loadStats(); }
}

// ─── Blacklist ────────────────────────────────────────────
async function loadBlacklist() {
  const list = await api('list_blacklist');
  const tbody = $('blacklist-tbody');
  if (!list.length) {
    tbody.innerHTML = '<tr class="empty-row"><td colspan="4">Aucun numéro blacklisté</td></tr>';
    return;
  }
  tbody.innerHTML = list.map(b => `
    <tr>
      <td class="mono">${esc(b.phone_number)}</td>
      <td>${esc(shortText(b.reason))}</td>
      <td class="mono">${fmtDate(b.created_at)}</td>
      <td><button class="btn btn-green btn-sm" onclick="removeBlacklist('${esc(b.phone_number)}',this)">↺ Débloquer</button></td>
    </tr>`).join('');
}

function openBlacklistModal() {
  $('modal-blacklist').classList.add('open');
  $('bl-phone').focus();
}
function closeModal() {
  $('modal-blacklist').classList.remove('open');
}
async function doBlacklistAdd() {
  const phone  = $('bl-phone').value.replace(/\D/g,'');
  const reason = $('bl-reason').value || 'Ajouté manuellement';
  if (!phone) { toast('Numéro invalide','err'); return; }
  const d = await api('blacklist_add', `phone=${encodeURIComponent(phone)}&reason=${encodeURIComponent(reason)}`);
  if (d.ok) {
    toast('⊘ ' + phone + ' blacklisté', 'ok');
    closeModal();
    if (currentPage === 'blacklist') loadBlacklist();
    loadStats();
  }
}
async function removeBlacklist(phone, btn) {
  const d = await api('blacklist_remove', `phone=${encodeURIComponent(phone)}`);
  if (d.ok) { toast('✓ Débloqué', 'ok'); loadBlacklist(); loadStats(); }
}

// ─── Queue ────────────────────────────────────────────────
async function loadQueue() {
  const list = await api('list_queue');
  const tbody = $('queue-tbody');
  const statusBadge = {
    pending:    'badge-amber',
    processing: 'badge-blue',
    completed:  'badge-green',
    failed:     'badge-red',
    retry:      'badge-purple',
  };
  if (!list.length) {
    tbody.innerHTML = '<tr class="empty-row"><td colspan="6">File vide</td></tr>';
    return;
  }
  tbody.innerHTML = list.map(q => `
    <tr>
      <td><span class="badge badge-blue">${esc(q.target_type)}</span></td>
      <td class="mono" style="font-size:11px;">${esc(shortText(q.target_id||q.phone_number,20))}</td>
      <td>${esc(shortText(q.message_data))}</td>
      <td><span class="badge ${statusBadge[q.status]||'badge-gray'}">${esc(q.status)}</span></td>
      <td class="mono">${q.retry_count}</td>
      <td class="mono">${fmtDate(q.created_at)}</td>
    </tr>`).join('');
}

// ─── Logs ────────────────────────────────────────────────
async function loadLogs() {
  const list = await api('list_logs');
  const tbody = $('logs-tbody');
  const statusBadge = { sent:'badge-green', failed:'badge-red', received:'badge-blue', processing:'badge-amber' };
  if (!list.length) {
    tbody.innerHTML = '<tr class="empty-row"><td colspan="5">Aucun log</td></tr>';
    return;
  }
  tbody.innerHTML = list.map(l => `
    <tr>
      <td class="mono">${esc(l.phone_number)}</td>
      <td><span class="badge badge-gray">${esc(l.message_type)}</span></td>
      <td>${esc(shortText(l.message_content))}</td>
      <td><span class="badge ${statusBadge[l.status]||'badge-gray'}">${esc(l.status)}</span></td>
      <td class="mono">${fmtDate(l.sent_at||l.created_at)}</td>
    </tr>`).join('');
}

// ─── Security ────────────────────────────────────────────
async function loadSecurity() {
  const list = await api('list_security');
  const tbody = $('security-tbody');
  if (!list.length) {
    tbody.innerHTML = '<tr class="empty-row"><td colspan="5">Aucune violation</td></tr>';
    return;
  }
  tbody.innerHTML = list.map(l => `
    <tr>
      <td class="mono">${esc(l.sender)}</td>
      <td><span class="badge badge-red">${esc(l.action_type)}</span></td>
      <td>${esc(shortText(l.reason))}</td>
      <td class="mono" style="font-size:11px;">${esc(shortText(l.group_id,22))}</td>
      <td class="mono">${fmtDate(l.created_at)}</td>
    </tr>`).join('');
}

// ─── Fermer modal en cliquant en dehors ───────────────────
$('modal-blacklist').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// ─── Clavier modal ────────────────────────────────────────
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeModal();
  if (e.key === 'Enter' && $('modal-blacklist').classList.contains('open')) doBlacklistAdd();
});

// ─── Init ────────────────────────────────────────────────
loadOverview();
// Auto-refresh stats toutes les 60s
setInterval(loadStats, 60000);
</script>

<?php endif; ?>
</body>
</html>