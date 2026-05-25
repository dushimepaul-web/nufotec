<?php
/**
 * ============================================================
 *  NUFOTEC — WhatsApp Dashboard COMPLET
 *  Groupes · Membres · Blacklist · Queue · Logs · Broadcast
 * ============================================================
 */
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

set_exception_handler(function(Throwable $e) {
    if (isset($_GET['ajax'])) {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['ok'=>false,'msg'=>'[Exception] '.$e->getMessage(),'file'=>basename($e->getFile()),'line'=>$e->getLine()]);
        exit;
    }
    echo '<pre style="background:#075e54;color:#fff;padding:20px;">['.get_class($e).'] '.$e->getMessage()."\nFile: ".$e->getFile().' line '.$e->getLine().'</pre>';
    exit;
});
set_error_handler(function(int $errno, string $msg, string $file, int $line): bool {
    if (error_reporting() & $errno) throw new \ErrorException($msg, $errno, $errno, $file, $line);
    return false;
});

// ── Config ────────────────────────────────────────────────
define('DB_HOST',   'localhost');
define('DB_USER',   'nufotec_nufotec');
define('DB_PASS',   '6886Paul@');
define('DB_NAME',   'nufotec_db');
define('API_TOKEN', 'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw');
define('API_URL',   'https://gate.whapi.cloud/');
define('DASH_PASS', 'admin123');

session_start();

if (($_POST['action'] ?? '') === 'login') {
    if ($_POST['password'] === DASH_PASS) $_SESSION['auth'] = true;
    else $login_error = 'Mot de passe incorrect';
}
if (($_GET['logout'] ?? '') === '1') { session_destroy(); header('Location: dashboard.php'); exit; }
$authed = !empty($_SESSION['auth']);

// ── DB ────────────────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_OBJ]
        );
    }
    return $pdo;
}
function dbq(string $sql, array $p=[]): array { $s=db()->prepare($sql);$s->execute($p);return $s->fetchAll(); }
function db1(string $sql, array $p=[]): ?object { $s=db()->prepare($sql);$s->execute($p);$r=$s->fetch();return $r?:null; }
function dbx(string $sql, array $p=[]): int { $s=db()->prepare($sql);$s->execute($p);return (int)$s->rowCount(); }

// ── Whapi API ─────────────────────────────────────────────
function whapi(string $ep, string $m='GET', ?array $d=null): ?array {
    $url = API_URL.ltrim($ep,'/');
    $ch = curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer '.API_TOKEN,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: NUFOTEC-WhatsApp/1.0',
        ],
    ]);
    if ($m==='POST') { curl_setopt($ch,CURLOPT_POST,true); if($d) curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($d)); }
    elseif ($m!=='GET') { curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$m); if($d) curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($d)); }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($err || $code>=400) { 
        error_log("Whapi [$ep] err=$err code=$code body=$body"); 
        return null; 
    }
    return json_decode((string)$body,true) ?: [];
}

// ── Création des tables si inexistantes ───────────────────
try {
    db()->exec("
        CREATE TABLE IF NOT EXISTS `groupes_whatsapp` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `groupe_id` varchar(255) NOT NULL,
            `nom` varchar(255) DEFAULT NULL,
            `actif` tinyint(1) DEFAULT 1,
            `nb_membres` int(11) DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `groupe_id` (`groupe_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE IF NOT EXISTS `whatsapp_participants` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `groupe_id` varchar(255) NOT NULL,
            `phone` varchar(100) DEFAULT NULL,
            `phone_formatted` varchar(50) NOT NULL,
            `is_admin` tinyint(1) DEFAULT 0,
            `violation_count` int(11) DEFAULT 0,
            `profile_name` varchar(255) DEFAULT NULL,
            `synced_at` datetime DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `groupe_phone` (`groupe_id`, `phone_formatted`),
            KEY `phone_formatted` (`phone_formatted`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE IF NOT EXISTS `whatsapp_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `phone_number` varchar(50) DEFAULT NULL,
            `message_type` varchar(50) DEFAULT NULL,
            `message_content` text,
            `status` varchar(50) DEFAULT NULL,
            `error_message` text,
            `sent_at` datetime DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE IF NOT EXISTS `whatsapp_blacklist` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `phone_number` varchar(50) NOT NULL,
            `reason` text,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `phone_number` (`phone_number`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE IF NOT EXISTS `whatsapp_queue` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `target_type` varchar(20) DEFAULT NULL,
            `target_id` varchar(255) DEFAULT NULL,
            `phone_number` varchar(50) DEFAULT NULL,
            `message_type` varchar(50) DEFAULT 'text',
            `message_data` text,
            `media_url` text,
            `status` varchar(20) DEFAULT 'pending',
            `retry_count` int(11) DEFAULT 0,
            `priority` int(11) DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `processed_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE IF NOT EXISTS `whatsapp_inbox` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `phone_number` varchar(50) NOT NULL,
            `full_name` varchar(255) DEFAULT NULL,
            `last_message` text,
            `last_message_at` datetime DEFAULT NULL,
            `is_blacklisted` tinyint(1) DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `phone_number` (`phone_number`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE IF NOT EXISTS `whatsapp_security_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sender` varchar(50) DEFAULT NULL,
            `action_type` varchar(50) DEFAULT NULL,
            `reason` text,
            `group_id` varchar(255) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE IF NOT EXISTS `whatsapp_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(100) NOT NULL,
            `setting_value` text,
            PRIMARY KEY (`id`),
            UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE IF NOT EXISTS `whatsapp_media` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `media_type` varchar(50) DEFAULT NULL,
            `file_name` varchar(255) DEFAULT NULL,
            `file_size` int(11) DEFAULT NULL,
            `mime_type` varchar(100) DEFAULT NULL,
            `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (Exception $e) {
    // Tables existent déjà
}

// ── AJAX ──────────────────────────────────────────────────
if ($authed && isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $act = $_GET['ajax'];

    // ── sync_groups — Récupère TOUS les groupes depuis Whapi
    if ($act === 'sync_groups') {
        $all_groups = [];
        
        // Essayer plusieurs endpoints pour récupérer les groupes
        $data = whapi('groups?count=500');
        if (!$data) $data = whapi('groups?limit=500');
        if (!$data) $data = whapi('groups');
        
        if (!$data || isset($data['error'])) {
            echo json_encode(['ok' => false, 'msg' => 'Erreur API Whapi - Impossible de récupérer les groupes']);
            exit;
        }
        
        $groups = $data['groups'] ?? $data['data'] ?? [];
        
        if (empty($groups)) {
            echo json_encode(['ok' => true, 'msg' => 'Aucun groupe trouvé sur Whapi', 'total' => 0]);
            exit;
        }
        
        $added = 0;
        $updated = 0;
        
        foreach ($groups as $g) {
            $gid = $g['id'] ?? '';
            $nom = $g['name'] ?? $g['subject'] ?? 'Groupe sans nom';
            $nb_membres = $g['participants_count'] ?? $g['members_count'] ?? 0;
            
            if (!$gid) continue;
            
            $exists = db1('SELECT id FROM groupes_whatsapp WHERE groupe_id = ?', [$gid]);
            
            if ($exists) {
                dbx('UPDATE groupes_whatsapp SET nom = ?, nb_membres = ?, updated_at = NOW() WHERE groupe_id = ?', 
                    [$nom, $nb_membres, $gid]);
                $updated++;
            } else {
                dbx('INSERT INTO groupes_whatsapp (groupe_id, nom, nb_membres, actif, created_at, updated_at) 
                     VALUES (?, ?, ?, 1, NOW(), NOW())',
                    [$gid, $nom, $nb_membres]);
                $added++;
            }
        }
        
        echo json_encode([
            'ok' => true,
            'msg' => "$added groupe(s) ajouté(s), $updated mis à jour (Total: " . count($groups) . " groupes)",
            'added' => $added,
            'updated' => $updated,
            'total' => count($groups)
        ]);
        exit;
    }

    // ── sync_members — Récupère TOUS les membres de chaque groupe
    if ($act === 'sync_members') {
        $gid_filter = $_GET['gid'] ?? null;
        
        // Récupérer les groupes à synchroniser
        if ($gid_filter) {
            $groups = dbq('SELECT groupe_id, nom FROM groupes_whatsapp WHERE groupe_id = ?', [$gid_filter]);
        } else {
            $groups = dbq('SELECT groupe_id, nom FROM groupes_whatsapp');
        }
        
        if (empty($groups)) {
            echo json_encode(['ok' => false, 'msg' => 'Aucun groupe en base. Cliquez d\'abord sur "Sync groupes".']);
            exit;
        }
        
        $total_members = 0;
        $groups_processed = 0;
        
        foreach ($groups as $g) {
            if (!$g || !$g->groupe_id) continue;
            
            // Récupérer les participants du groupe
            $participants = whapi('groups/' . urlencode($g->groupe_id) . '/participants');
            
            if (!$participants || isset($participants['error'])) {
                // Fallback: essayer via groups endpoint
                $group_data = whapi('groups/' . urlencode($g->groupe_id));
                $participants = $group_data['participants'] ?? $group_data['members'] ?? [];
            }
            
            if (empty($participants)) {
                continue;
            }
            
            $members_count = 0;
            
            foreach ($participants as $p) {
                if (is_string($p)) {
                    $phone_raw = $p;
                    $is_admin = 0;
                    $name = null;
                } else {
                    $phone_raw = $p['id'] ?? $p['phone'] ?? $p['jid'] ?? '';
                    $is_admin = (isset($p['rank']) && $p['rank'] === 'admin') ? 1 : 
                               ((isset($p['isAdmin']) && $p['isAdmin']) ? 1 : 0);
                    $name = $p['name'] ?? $p['pushName'] ?? $p['notify'] ?? $p['contactName'] ?? null;
                }
                
                if (!$phone_raw) continue;
                
                $phone_clean = preg_replace('/@[^@]+$/', '', (string)$phone_raw);
                $phone_fmt = preg_replace('/\D+/', '', $phone_clean);
                
                if (!$phone_fmt || strlen($phone_fmt) < 6) continue;
                
                dbx(
                    'INSERT INTO whatsapp_participants 
                     (groupe_id, phone, phone_formatted, is_admin, violation_count, profile_name, synced_at, created_at, updated_at)
                     VALUES (?, ?, ?, ?, COALESCE((SELECT violation_count FROM whatsapp_participants WHERE phone_formatted = ? AND groupe_id = ?), 0), ?, NOW(), NOW(), NOW())
                     ON DUPLICATE KEY UPDATE
                       is_admin = VALUES(is_admin),
                       profile_name = COALESCE(VALUES(profile_name), profile_name),
                       synced_at = NOW(), 
                       updated_at = NOW()',
                    [$g->groupe_id, $phone_raw, $phone_fmt, $is_admin, $phone_fmt, $g->groupe_id, $name]
                );
                $members_count++;
                $total_members++;
            }
            
            dbx('UPDATE groupes_whatsapp SET nb_membres = ?, updated_at = NOW() WHERE groupe_id = ?', 
                [$members_count, $g->groupe_id]);
            $groups_processed++;
            
            // Petit délai pour éviter rate limiting
            usleep(200000);
        }
        
        echo json_encode([
            'ok' => true,
            'msg' => "✅ $total_members membre(s) synchronisé(s) depuis $groups_processed groupe(s)",
            'total_members' => $total_members,
            'groups_processed' => $groups_processed
        ]);
        exit;
    }

    // ── toggle_group
    if ($act === 'toggle_group') {
        $gid = $_GET['gid'] ?? '';
        $g = db1('SELECT actif FROM groupes_whatsapp WHERE groupe_id = ?', [$gid]);
        if ($g) dbx('UPDATE groupes_whatsapp SET actif = ? WHERE groupe_id = ?', [$g->actif ? 0 : 1, $gid]);
        echo json_encode(['ok' => true, 'actif' => $g ? ($g->actif ? 0 : 1) : 0]);
        exit;
    }

    // ── blacklist_add
    if ($act === 'blacklist_add') {
        $phone = preg_replace('/\D/', '', ($_GET['phone'] ?? ''));
        $reason = $_GET['reason'] ?? 'Ajouté manuellement';
        if ($phone) dbx('INSERT INTO whatsapp_blacklist (phone_number, reason, created_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE reason = VALUES(reason)', [$phone, $reason]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // ── blacklist_remove
    if ($act === 'blacklist_remove') {
        dbx('DELETE FROM whatsapp_blacklist WHERE phone_number = ?', [preg_replace('/\D/', '', ($_GET['phone'] ?? ''))]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // ── reset_violations
    if ($act === 'reset_violations') {
        dbx('UPDATE whatsapp_participants SET violation_count = 0 WHERE phone_formatted = ?', [preg_replace('/\D/', '', ($_GET['phone'] ?? ''))]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // ── clear_queue
    if ($act === 'clear_queue') {
        $status = $_GET['status'] ?? 'completed';
        dbx('DELETE FROM whatsapp_queue WHERE status = ?', [$status]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // ── stats
    if ($act === 'stats') {
        echo json_encode([
            'groups'        => (db1('SELECT COUNT(*) c FROM groupes_whatsapp')->c ?? 0),
            'groups_actif'  => (db1('SELECT COUNT(*) c FROM groupes_whatsapp WHERE actif = 1')->c ?? 0),
            'members'       => (db1('SELECT COUNT(*) c FROM whatsapp_participants')->c ?? 0),
            'admins'        => (db1('SELECT COUNT(DISTINCT phone_formatted) c FROM whatsapp_participants WHERE is_admin = 1')->c ?? 0),
            'blacklist'     => (db1('SELECT COUNT(*) c FROM whatsapp_blacklist')->c ?? 0),
            'queue_pending' => (db1("SELECT COUNT(*) c FROM whatsapp_queue WHERE status = 'pending'")->c ?? 0),
            'queue_failed'  => (db1("SELECT COUNT(*) c FROM whatsapp_queue WHERE status = 'failed'")->c ?? 0),
            'logs_today'    => (db1('SELECT COUNT(*) c FROM whatsapp_logs WHERE DATE(created_at) = CURDATE()')->c ?? 0),
            'violations'    => (db1('SELECT COUNT(*) c FROM whatsapp_participants WHERE violation_count > 0')->c ?? 0),
            'inbox'         => (db1('SELECT COUNT(*) c FROM whatsapp_inbox')->c ?? 0),
        ]);
        exit;
    }

    // ── list_groups — depuis la base
    if ($act === 'list_groups') {
        echo json_encode(dbq(
            'SELECT g.*, (SELECT COUNT(*) FROM whatsapp_participants p WHERE p.groupe_id = g.groupe_id) as nb_membres
             FROM groupes_whatsapp g ORDER BY g.actif DESC, g.nom ASC'
        ));
        exit;
    }

    // ── list_members — depuis la base
    if ($act === 'list_members') {
        $gid = $_GET['gid'] ?? '';
        $search = '%' . ($_GET['search'] ?? '') . '%';
        $sql = 'SELECT p.*, g.nom as groupe_nom FROM whatsapp_participants p
                LEFT JOIN groupes_whatsapp g ON g.groupe_id = p.groupe_id
                WHERE (p.phone_formatted LIKE ? OR p.profile_name LIKE ?)';
        $params = [$search, $search];
        if ($gid) { $sql .= ' AND p.groupe_id = ?'; $params[] = $gid; }
        $sql .= ' ORDER BY p.is_admin DESC, p.profile_name ASC LIMIT 500';
        echo json_encode(dbq($sql, $params));
        exit;
    }

    // ── list_blacklist
    if ($act === 'list_blacklist') {
        echo json_encode(dbq('SELECT * FROM whatsapp_blacklist ORDER BY created_at DESC LIMIT 200'));
        exit;
    }

    // ── list_queue
    if ($act === 'list_queue') {
        echo json_encode(dbq('SELECT * FROM whatsapp_queue ORDER BY created_at DESC LIMIT 150'));
        exit;
    }

    // ── list_inbox
    if ($act === 'list_inbox') {
        echo json_encode(dbq('SELECT * FROM whatsapp_inbox ORDER BY last_message_at DESC LIMIT 150'));
        exit;
    }

    // ── list_logs
    if ($act === 'list_logs') {
        echo json_encode(dbq('SELECT * FROM whatsapp_logs ORDER BY created_at DESC LIMIT 200'));
        exit;
    }

    // ── list_security
    if ($act === 'list_security') {
        echo json_encode(dbq('SELECT * FROM whatsapp_security_logs ORDER BY created_at DESC LIMIT 150'));
        exit;
    }

    // ── list_settings
    if ($act === 'list_settings') {
        echo json_encode(dbq('SELECT * FROM whatsapp_settings ORDER BY id'));
        exit;
    }

    // ── save_setting
    if ($act === 'save_setting') {
        $key = $_GET['key'] ?? '';
        $val = $_GET['val'] ?? '';
        if ($key) dbx('UPDATE whatsapp_settings SET setting_value = ? WHERE setting_key = ?', [$val, $key]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // ── diag
    if ($act === 'diag') {
        $diag = ['php' => PHP_VERSION, 'curl' => curl_version()['version'] ?? '?', 'pdo_mysql' => extension_loaded('pdo_mysql')];
        try {
            db();
            $diag['db'] = 'OK';
            $tables = [];
            $st = db()->query('SHOW TABLES');
            while ($r = $st->fetch(PDO::FETCH_NUM)) $tables[] = $r[0];
            $diag['tables'] = $tables;
        } catch (\PDOException $e) { $diag['db'] = 'ERR: ' . $e->getMessage(); }
        $diag['whapi'] = whapi('health') !== null;
        echo json_encode(['ok' => true, 'diag' => $diag]);
        exit;
    }

    echo json_encode(['ok' => false, 'msg' => 'Action inconnue']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>NUFOTEC · WhatsApp Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --wa-green:     #25D366;
  --wa-green-d:   #128C7E;
  --wa-green-dd:  #075E54;
  --wa-light:     #DCF8C6;
  --wa-teal:      #00BFA5;
  --wa-bg:        #ECE5DD;
  --wa-panel:     #FFFFFF;
  --wa-chat-bg:   #E5DDD5;
  --wa-sidebar:   #F0F2F5;
  --wa-header:    #075E54;
  --wa-icon:      #54656F;
  --red:    #FF3B30;
  --amber:  #FF9500;
  --blue:   #007AFF;
  --purple: #5856D6;
  --text:   #111B21;
  --text2:  #54656F;
  --text3:  #8696A0;
  --border: #D1D7DB;
  --border2:#E9EDEF;
  --font: 'Nunito', sans-serif;
  --mono: 'JetBrains Mono', monospace;
  --radius: 12px;
  --shadow: 0 2px 16px rgba(0,0,0,.10);
  --shadow-lg: 0 8px 32px rgba(0,0,0,.15);
}

body{font-family:var(--font);background:var(--wa-chat-bg);color:var(--text);min-height:100vh;overflow-x:hidden;}

.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--wa-green-dd) 0%,var(--wa-green-d) 50%,var(--wa-green) 100%);}
.login-box{background:#fff;border-radius:20px;padding:48px 40px;width:380px;box-shadow:var(--shadow-lg);text-align:center;}
.login-logo{font-size:56px;margin-bottom:8px;}
.login-brand{font-size:22px;font-weight:900;color:var(--wa-green-dd);}
.login-sub{font-size:13px;color:var(--text2);margin-bottom:32px;}
.login-box input{width:100%;border:1.5px solid var(--border);border-radius:10px;padding:13px 16px;font-family:var(--mono);font-size:14px;outline:none;margin-bottom:14px;}
.login-box input:focus{border-color:var(--wa-green);}

.btn{display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;border:none;transition:all .18s;}
.btn-green {background:var(--wa-green);color:#fff;}
.btn-green:hover{background:var(--wa-green-d);}
.btn-dark{background:var(--wa-green-dd);color:#fff;}
.btn-red{background:rgba(255,59,48,.1);color:var(--red);border:1.5px solid rgba(255,59,48,.3);}
.btn-blue{background:rgba(0,122,255,.1);color:var(--blue);border:1.5px solid rgba(0,122,255,.25);}
.btn-amber{background:rgba(255,149,0,.1);color:var(--amber);border:1.5px solid rgba(255,149,0,.3);}
.btn-ghost{background:transparent;color:var(--text2);border:1.5px solid var(--border);}
.btn-sm{padding:6px 12px;font-size:12px;}

.shell{display:flex;min-height:100vh;}
.sidebar{width:240px;min-width:240px;background:var(--wa-panel);border-right:1px solid var(--border);position:fixed;top:0;left:0;height:100vh;z-index:100;}
.sidebar-header{background:var(--wa-header);padding:18px 20px;display:flex;align-items:center;gap:12px;}
.sidebar-header .logo-icon{font-size:28px;}
.sidebar-header .logo-text .brand{font-size:17px;font-weight:900;color:#fff;}
.sidebar-header .logo-text .sub{font-size:10px;color:rgba(255,255,255,.7);}
.nav{flex:1;padding:12px 8px;overflow-y:auto;}
.nav-section{font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--text3);padding:12px 12px 6px;}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;font-size:13px;font-weight:700;color:var(--text2);cursor:pointer;width:100%;text-align:left;background:none;border:none;}
.nav-item:hover{color:var(--text);background:var(--wa-sidebar);}
.nav-item.active{color:var(--wa-green-dd);background:rgba(37,211,102,.1);}
.nav-item .ico{font-size:17px;width:22px;}
.sidebar-footer{padding:12px 8px;border-top:1px solid var(--border2);}

.main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh;}
.topbar{background:var(--wa-header);padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;}
.topbar-title{font-size:17px;font-weight:800;color:#fff;}
.content{padding:20px 24px;flex:1;background:var(--wa-chat-bg);}
.page{display:none;}
.page.active{display:block;}

.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:20px;}
.stat-card{background:var(--wa-panel);border-radius:var(--radius);padding:18px;box-shadow:var(--shadow);border-top:3px solid var(--wa-green);}
.stat-icon{font-size:24px;margin-bottom:8px;}
.stat-label{font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text2);margin-bottom:6px;}
.stat-val{font-size:32px;font-weight:900;color:var(--wa-green-dd);line-height:1;}
.stat-sub{font-size:11px;color:var(--text3);margin-top:4px;}

.panel{background:var(--wa-panel);border-radius:var(--radius);overflow:hidden;margin-bottom:18px;box-shadow:var(--shadow);}
.panel-header{padding:14px 20px;border-bottom:1px solid var(--border2);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;}
.panel-title{font-size:14px;font-weight:800;}

.tbl-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:left;padding:10px 16px;font-size:11px;font-weight:800;text-transform:uppercase;color:var(--text2);border-bottom:1.5px solid var(--border2);background:var(--wa-sidebar);}
td{padding:11px 16px;border-bottom:1px solid var(--border2);}
.mono{font-family:var(--mono);font-size:12px;color:var(--text2);}
.empty-row td{text-align:center;color:var(--text3);padding:36px;}

.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:800;}
.badge-green{background:rgba(37,211,102,.15);color:var(--wa-green-d);}
.badge-red{background:rgba(255,59,48,.12);color:var(--red);}
.badge-blue{background:rgba(0,122,255,.12);color:var(--blue);}
.badge-gray{background:rgba(84,101,111,.1);color:var(--text2);}

.group-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;padding:16px;}
.group-card{background:var(--wa-sidebar);border:1.5px solid var(--border2);border-radius:var(--radius);padding:16px;}
.group-card-top{display:flex;justify-content:space-between;margin-bottom:10px;}
.group-name{font-size:13px;font-weight:800;}
.group-id{font-family:var(--mono);font-size:10px;color:var(--text3);margin-top:3px;}
.group-footer{display:flex;gap:6px;margin-top:12px;justify-content:flex-end;}

.search-input{background:var(--wa-sidebar);border:1.5px solid var(--border);border-radius:8px;padding:8px 12px;font-family:var(--mono);font-size:12px;outline:none;width:200px;}
.search-input:focus{border-color:var(--wa-green);}

#toast{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
.toast-item{background:#fff;border-radius:10px;padding:12px 18px;font-size:13px;font-weight:700;max-width:320px;box-shadow:var(--shadow-lg);border-left:4px solid var(--wa-green);display:flex;align-items:center;gap:10px;}
.toast-item.err{border-left-color:var(--red);}
.spin{display:inline-block;width:14px;height:14px;border:2px solid var(--border);border-top-color:var(--wa-green);border-radius:50%;animation:spin .6s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}

.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:500;display:none;align-items:center;justify-content:center;}
.modal-bg.open{display:flex;}
.modal{background:#fff;border-radius:16px;padding:28px;width:460px;max-width:95vw;}
.modal h3{font-size:17px;font-weight:900;margin-bottom:16px;color:var(--wa-green-dd);}
.modal input{width:100%;background:var(--wa-sidebar);border:1.5px solid var(--border);border-radius:8px;padding:10px 14px;margin-bottom:14px;}
.modal-footer{display:flex;gap:10px;justify-content:flex-end;}

.settings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;padding:16px;}
.setting-item{background:var(--wa-sidebar);border:1.5px solid var(--border2);border-radius:10px;padding:16px;}
.setting-key{font-size:12px;font-weight:800;color:var(--text2);margin-bottom:6px;}
.setting-input{width:100%;background:#fff;border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;font-family:var(--mono);font-size:13px;}
.setting-input:focus{border-color:var(--wa-green);}

@media(max-width:768px){
  .sidebar{width:56px;min-width:56px;}
  .sidebar .brand,.sidebar .sub,.nav-item span:not(.ico),.sidebar-footer .btn span:not(.ico),.nav-section{display:none;}
  .main{margin-left:56px;}
}
</style>
</head>
<body>

<?php if (!$authed): ?>
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo">💬</div>
    <div class="login-brand">NUFOTEC</div>
    <div class="login-sub">WhatsApp Dashboard · Administration</div>
    <?php if (!empty($login_error)): ?>
      <div class="login-err">⚠ <?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="action" value="login">
      <input type="password" name="password" placeholder="Mot de passe" autofocus>
      <button type="submit" class="btn btn-dark btn-full" style="font-size:15px;padding:14px;width:100%;">Connexion →</button>
    </form>
  </div>
</div>

<?php else: ?>
<div class="shell">
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="logo-icon">💬</div>
    <div class="logo-text">
      <div class="brand">NUFOTEC</div>
      <div class="sub">WhatsApp</div>
    </div>
  </div>
  <nav class="nav">
    <div class="nav-section">Principal</div>
    <button class="nav-item active" onclick="goPage('overview')"><span class="ico">🏠</span><span>Vue d'ensemble</span></button>
    <button class="nav-item" onclick="goPage('broadcast')"><span class="ico">📢</span><span>Diffusion</span></button>
    <div class="nav-section">Gestion</div>
    <button class="nav-item" onclick="goPage('groups')"><span class="ico">👥</span><span>Groupes</span></button>
    <button class="nav-item" onclick="goPage('members')"><span class="ico">👤</span><span>Membres</span></button>
    <button class="nav-item" onclick="goPage('inbox')"><span class="ico">📥</span><span>Inbox</span></button>
    <button class="nav-item" onclick="goPage('blacklist')"><span class="ico">🚫</span><span>Blacklist</span><span class="nav-badge red" id="nb-blacklist" style="display:none">0</span></button>
    <div class="nav-section">Système</div>
    <button class="nav-item" onclick="goPage('queue')"><span class="ico">📤</span><span>File d'envoi</span><span class="nav-badge" id="nb-queue" style="display:none">0</span></button>
    <button class="nav-item" onclick="goPage('logs')"><span class="ico">📋</span><span>Logs</span></button>
    <button class="nav-item" onclick="goPage('security')"><span class="ico">🛡</span><span>Sécurité</span></button>
    <button class="nav-item" onclick="goPage('settings')"><span class="ico">⚙️</span><span>Paramètres</span></button>
  </nav>
  <div class="sidebar-footer">
    <a href="?logout=1" class="btn btn-ghost btn-sm btn-full" style="width:100%;"><span class="ico">🔒</span><span>Déconnexion</span></a>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <div class="topbar-title" id="page-title">Vue d'ensemble</div>
    <div class="topbar-actions">
      <button class="btn btn-green btn-sm" onclick="syncGroups()">↻ Sync groupes</button>
      <button class="btn btn-ghost btn-sm" onclick="syncMembers(null)">↻ Sync membres</button>
    </div>
  </div>
  <div class="content" id="main-content"></div>
</div>
</div>

<div class="modal-bg" id="modal-blacklist">
  <div class="modal">
    <h3>🚫 Ajouter à la blacklist</h3>
    <label>Numéro (chiffres uniquement)</label>
    <input type="text" id="bl-phone" placeholder="ex: 25779666439">
    <label>Raison</label>
    <input type="text" id="bl-reason" value="Ajouté manuellement">
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-blacklist')">Annuler</button>
      <button class="btn btn-red" onclick="doBlacklistAdd()">🚫 Blacklister</button>
    </div>
  </div>
</div>

<div id="toast"></div>

<script>
const $ = id => document.getElementById(id);
const esc = s => s==null?'':String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
const fmtDate = s => s ? s.replace('T',' ').substring(0,16) : '—';
const short = (s,n=35) => !s?'—':(s.length>n?s.substring(0,n)+'…':s);

let currentPage = '';

async function api(action, params='') {
  const sep = params?'&':'';
  const r = await fetch(`?ajax=${action}${sep}${params}`);
  return r.json();
}

function toast(msg, type='ok') {
  const el = document.createElement('div');
  el.className = `toast-item ${type==='err'?'err':type==='warn'?'warn':''}`;
  const icon = type==='err'?'❌':type==='warn'?'⚠️':'✅';
  el.innerHTML = `<span>${icon}</span><span>${msg}</span>`;
  $('toast').appendChild(el);
  setTimeout(()=>el.remove(),4000);
}

function closeModal(id) { $(id)?.classList.remove('open'); }

// ─── PAGES ────────────────────────────────────────────────
const PAGES = {
    overview:  { title:'Vue d\'ensemble', build:buildOverview, load:loadOverview },
    broadcast: { title:'Diffusion', build:buildBroadcast, load:initBroadcast },
    groups:    { title:'Groupes', build:buildGroups, load:loadGroups },
    members:   { title:'Membres', build:buildMembers, load:loadMembersPage },
    inbox:     { title:'Inbox', build:buildInbox, load:loadInbox },
    blacklist: { title:'Blacklist', build:buildBlacklist, load:loadBlacklist },
    queue:     { title:'File d\'envoi', build:buildQueue, load:loadQueue },
    logs:      { title:'Logs', build:buildLogs, load:loadLogs },
    security:  { title:'Sécurité', build:buildSecurity, load:loadSecurity },
    settings:  { title:'Paramètres', build:buildSettings, load:loadSettings },
};

function goPage(name) {
  const pg = PAGES[name]; if (!pg) return;
  document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  let el = $('page-'+name);
  if (!el) {
    el = document.createElement('div');
    el.id='page-'+name; el.className='page';
    el.innerHTML = pg.build();
    $('main-content').appendChild(el);
  }
  el.classList.add('active');
  $('page-title').textContent = pg.title;
  document.querySelectorAll('.nav-item').forEach(n=>{
    if(n.getAttribute('onclick')?.includes(`'${name}'`)) n.classList.add('active');
  });
  currentPage = name;
  pg.load();
}

// ─── OVERVIEW ────────────────────────────────────────────
function buildOverview() { return `
<div class="stats-grid">
  <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-label">Groupes actifs</div><div class="stat-val" id="s-groups">—</div><div class="stat-sub" id="s-groups-sub">sur — total</div></div>
  <div class="stat-card"><div class="stat-icon">👤</div><div class="stat-label">Membres</div><div class="stat-val" id="s-members">—</div><div class="stat-sub" id="s-admins-sub">— admins</div></div>
  <div class="stat-card"><div class="stat-icon">📥</div><div class="stat-label">Inbox</div><div class="stat-val" id="s-inbox">—</div></div>
  <div class="stat-card"><div class="stat-icon">🚫</div><div class="stat-label">Blacklist</div><div class="stat-val" id="s-blacklist">—</div></div>
  <div class="stat-card"><div class="stat-icon">📤</div><div class="stat-label">En attente</div><div class="stat-val" id="s-queue">—</div></div>
  <div class="stat-card"><div class="stat-icon">⚠️</div><div class="stat-label">Violations</div><div class="stat-val" id="s-violations">—</div></div>
</div>
<div class="panel"><div class="panel-header"><div class="panel-title">⚡ Actions rapides</div></div><div style="padding:16px;display:flex;gap:10px;flex-wrap:wrap;">
  <button class="btn btn-green" onclick="goPage('broadcast')">📢 Nouvelle diffusion</button>
  <button class="btn btn-dark" onclick="syncGroups()">↻ Sync groupes Whapi</button>
  <button class="btn btn-blue" onclick="syncMembers(null)">↻ Sync membres</button>
  <button class="btn btn-amber" onclick="$('modal-blacklist').classList.add('open')">🚫 Blacklister numéro</button>
</div></div>`; }

async function loadOverview() { await loadStats(); }
async function loadStats() {
  const d = await api('stats');
  const set = (id,v)=>{ const e=$(id); if(e) e.textContent=v; };
  set('s-groups',d.groups_actif);
  set('s-groups-sub',`sur ${d.groups} total`);
  set('s-members',d.members);
  set('s-admins-sub',`${d.admins} admin(s)`);
  set('s-inbox',d.inbox);
  set('s-blacklist',d.blacklist);
  set('s-queue',d.queue_pending);
  set('s-violations',d.violations);
}

// ─── GROUPS ──────────────────────────────────────────────
function buildGroups() { return `<div class="panel"><div class="panel-header"><div class="panel-title">👥 Groupes WhatsApp</div><div class="panel-actions"><button class="btn btn-dark" onclick="syncGroups()">↻ Sync Whapi</button><button class="btn btn-blue btn-sm" onclick="loadGroups()">↻ Actualiser</button></div></div><div id="groups-grid" class="group-grid">Chargement...</div></div>`; }
async function syncGroups() { toast('Synchronisation groupes...',''); const d=await api('sync_groups'); if(d.ok) toast(`✅ ${d.msg}`,'ok'); else toast(d.msg,'err'); if(currentPage==='groups') loadGroups(); loadStats(); }
async function loadGroups() { const groups=await api('list_groups'); const c=$('groups-grid'); if(!c) return; if(!groups.length){c.innerHTML='<div style="padding:16px;">Aucun groupe. Cliquez sur Sync Whapi.</div>';return;} c.innerHTML=groups.map(g=>`<div class="group-card"><div class="group-card-top"><div><div class="group-name">${esc(g.nom)}</div><div class="group-id">${esc(g.groupe_id)}</div></div><span class="badge ${g.actif=='1'?'badge-green':'badge-gray'}">${g.actif=='1'?'Actif':'Inactif'}</span></div><span class="badge badge-blue">👤 ${g.nb_membres} membres</span><div class="group-footer"><button class="btn btn-ghost btn-sm" onclick="syncMembers('${esc(g.groupe_id)}')">↻ Membres</button><button class="btn btn-sm ${g.actif=='1'?'btn-red':'btn-green'}" onclick="toggleGroup('${esc(g.groupe_id)}')">${g.actif=='1'?'🚫 Désactiver':'✅ Activer'}</button></div></div>`).join(''); }
async function syncMembers(gid) { toast('Synchronisation membres...',''); const d=await api('sync_members', gid?`gid=${encodeURIComponent(gid)}`:''); if(d.ok) toast(`✅ ${d.msg}`,'ok'); else toast(d.msg,'err'); if(currentPage==='members') loadMembersPage(); if(currentPage==='groups') loadGroups(); loadStats(); }
async function toggleGroup(gid) { const d=await api('toggle_group',`gid=${encodeURIComponent(gid)}`); if(d.ok){toast('Groupe mis à jour','ok');loadGroups();loadStats();} }

// ─── MEMBERS ─────────────────────────────────────────────
function buildMembers() { return `<div class="panel"><div class="panel-header"><div class="panel-title">👤 Membres</div><div class="panel-actions"><input class="search-input" type="text" id="member-search" placeholder="Rechercher…" oninput="loadMembers()"><select class="search-input" id="member-group" onchange="loadMembers()"><option value="">Tous les groupes</option></select><button class="btn btn-dark btn-sm" onclick="syncMembers(null)">↻ Sync</button></div></div><div class="tbl-wrap"><table><thead><tr><th>Téléphone</th><th>Nom</th><th>Groupe</th><th>Rôle</th><th>Violations</th><th>Actions</th></tr></thead><tbody id="members-tbody"><tr class="empty-row"><td colspan="6"><span class="spin"></span></td></tr></tbody></table></div></div>`; }
async function loadMembersPage() { const groups=await api('list_groups'); const sel=$('member-group'); if(sel){ const cur=sel.value; sel.innerHTML='<option value="">Tous les groupes</option>'+groups.map(g=>`<option value="${esc(g.groupe_id)}" ${g.groupe_id===cur?'selected':''}>${esc(g.nom)}</option>`).join(''); } loadMembers(); }
async function loadMembers() { const search=$('member-search')?.value||''; const gid=$('member-group')?.value||''; const members=await api('list_members',`search=${encodeURIComponent(search)}&gid=${encodeURIComponent(gid)}`); const tbody=$('members-tbody'); if(!tbody) return; if(!members.length){tbody.innerHTML='<tr class="empty-row"><td colspan="6">Aucun membre</td></tr>';return;} tbody.innerHTML=members.map(m=>`<tr><td class="mono">${esc(m.phone_formatted)}</td><td>${esc(m.profile_name||'—')}</td><td>${esc(short(m.groupe_nom,28))}</td><td>${m.is_admin=='1'?'⭐ Admin':'Membre'}</td><td>${m.violation_count>0?`<span class="badge badge-red">${m.violation_count}</span>`:'0'}</td><td><button class="btn btn-amber btn-sm" onclick="blacklistPhone('${esc(m.phone_formatted)}')">🚫</button></td></tr>`).join(''); }
async function blacklistPhone(phone) { if(!confirm(`Blacklister ${phone} ?`)) return; const d=await api('blacklist_add',`phone=${encodeURIComponent(phone)}&reason=Dashboard`); if(d.ok){toast(`🚫 ${phone} blacklisté`,'ok');loadMembers();loadStats();} }

// ─── INBOX ───────────────────────────────────────────────
function buildInbox() { return `<div class="panel"><div class="panel-header"><div class="panel-title">📥 Inbox</div><button class="btn btn-ghost btn-sm" onclick="loadInbox()">↻</button></div><div class="tbl-wrap"><table><thead><tr><th>Téléphone</th><th>Nom</th><th>Dernier message</th><th>Date</th><th>Statut</th></tr></thead><tbody id="inbox-tbody"><tr class="empty-row"><td colspan="5"><span class="spin"></span></td></tr></tbody></table></div></div>`; }
async function loadInbox() { const list=await api('list_inbox'); const tbody=$('inbox-tbody'); if(!tbody) return; if(!list.length){tbody.innerHTML='<tr class="empty-row"><td colspan="5">Aucun contact</td></tr>';return;} tbody.innerHTML=list.map(c=>`<tr><td class="mono">${esc(c.phone_number)}</td><td>${esc(c.full_name||'—')}</td><td>${esc(short(c.last_message))}</td><td class="mono">${fmtDate(c.last_message_at)}</td><td>${c.is_blacklisted=='1'?'🚫 Bloqué':'✅ OK'}</td></tr>`).join(''); }

// ─── BLACKLIST ───────────────────────────────────────────
function buildBlacklist() { return `<div class="panel"><div class="panel-header"><div class="panel-title">🚫 Blacklist</div><div class="panel-actions"><button class="btn btn-red" onclick="$('modal-blacklist').classList.add('open')">+ Ajouter</button><button class="btn btn-ghost btn-sm" onclick="loadBlacklist()">↻</button></div></div><div class="tbl-wrap"><table><thead><tr><th>Numéro</th><th>Raison</th><th>Date</th><th>Action</th></tr></thead><tbody id="blacklist-tbody"><tr class="empty-row"><td colspan="4"><span class="spin"></span></td></tr></tbody></table></div></div>`; }
async function loadBlacklist() { const list=await api('list_blacklist'); const tbody=$('blacklist-tbody'); if(!tbody) return; if(!list.length){tbody.innerHTML='<tr class="empty-row"><td colspan="4">Aucun numéro blacklisté</td></tr>';return;} tbody.innerHTML=list.map(b=>`<tr><td class="mono">${esc(b.phone_number)}</td><td>${esc(short(b.reason))}</td><td class="mono">${fmtDate(b.created_at)}</td><td><button class="btn btn-green btn-sm" onclick="removeBlacklist('${esc(b.phone_number)}')">Débloquer</button></td></tr>`).join(''); }
async function doBlacklistAdd() { const phone=($('bl-phone')?.value||'').replace(/\D/g,''); const reason=$('bl-reason')?.value||'Ajouté'; if(!phone){toast('Numéro invalide','err');return;} const d=await api('blacklist_add',`phone=${encodeURIComponent(phone)}&reason=${encodeURIComponent(reason)}`); if(d.ok){toast(`🚫 ${phone} blacklisté`,'ok');closeModal('modal-blacklist');if(currentPage==='blacklist')loadBlacklist();loadStats();} }
async function removeBlacklist(phone) { const d=await api('blacklist_remove',`phone=${encodeURIComponent(phone)}`); if(d.ok){toast('✅ Débloqué','ok');loadBlacklist();loadStats();} }

// ─── QUEUE ───────────────────────────────────────────────
function buildQueue() { return `<div class="panel"><div class="panel-header"><div class="panel-title">📤 File d'envoi</div><div class="panel-actions"><button class="btn btn-red btn-sm" onclick="if(confirm('Vider les complétés ?'))api('clear_queue','status=completed').then(()=>{toast('Nettoyé','ok');loadQueue();})">🗑 Vider complétés</button><button class="btn btn-ghost btn-sm" onclick="loadQueue()">↻</button></div></div><div class="tbl-wrap"><table><thead><tr><th>Type</th><th>Cible</th><th>Contenu</th><th>Statut</th><th>Date</th></tr></thead><tbody id="queue-tbody"><tr class="empty-row"><td colspan="5"><span class="spin"></span></td></tr></tbody></table></div></div>`; }
async function loadQueue() { const list=await api('list_queue'); const tbody=$('queue-tbody'); if(!tbody) return; if(!list.length){tbody.innerHTML='<tr class="empty-row"><td colspan="5">File vide</td></tr>';return;} tbody.innerHTML=list.map(q=>`<tr><td><span class="badge badge-blue">${esc(q.target_type)}</span></td><td class="mono">${esc(q.target_id||q.phone_number)}</td><td>${esc(short(q.message_data))}</td><td><span class="badge ${q.status=='pending'?'badge-blue':(q.status=='completed'?'badge-green':'badge-red')}">${esc(q.status)}</span></td><td class="mono">${fmtDate(q.created_at)}</td></tr>`).join(''); }

// ─── LOGS ────────────────────────────────────────────────
function buildLogs() { return `<div class="panel"><div class="panel-header"><div class="panel-title">📋 Logs</div><button class="btn btn-ghost btn-sm" onclick="loadLogs()">↻</button></div><div class="tbl-wrap"><table><thead><tr><th>Téléphone</th><th>Type</th><th>Message</th><th>Statut</th><th>Date</th></tr></thead><tbody id="logs-tbody"><tr class="empty-row"><td colspan="5"><span class="spin"></span></td></tr></tbody></table></div></div>`; }
async function loadLogs() { const list=await api('list_logs'); const tbody=$('logs-tbody'); if(!tbody) return; if(!list.length){tbody.innerHTML='<tr class="empty-row"><td colspan="5">Aucun log</td></tr>';return;} tbody.innerHTML=list.map(l=>`<tr><td class="mono">${esc(l.phone_number)}</td><td><span class="badge badge-gray">${esc(l.message_type)}</span></td><td>${esc(short(l.message_content))}</td><td><span class="badge ${l.status=='sent'?'badge-green':'badge-red'}">${esc(l.status)}</span></td><td class="mono">${fmtDate(l.sent_at||l.created_at)}</td></tr>`).join(''); }

// ─── SECURITY ────────────────────────────────────────────
function buildSecurity() { return `<div class="panel"><div class="panel-header"><div class="panel-title">🛡 Sécurité</div><button class="btn btn-ghost btn-sm" onclick="loadSecurity()">↻</button></div><div class="tbl-wrap"><table><thead><tr><th>Expéditeur</th><th>Action</th><th>Raison</th><th>Date</th></tr></thead><tbody id="security-tbody"><tr class="empty-row"><td colspan="4"><span class="spin"></span></td></tr></tbody></table></div></div>`; }
async function loadSecurity() { const list=await api('list_security'); const tbody=$('security-tbody'); if(!tbody) return; if(!list.length){tbody.innerHTML='<tr class="empty-row"><td colspan="4">Aucune violation</td></tr>';return;} tbody.innerHTML=list.map(l=>`<tr><td class="mono">${esc(l.sender)}</td><td><span class="badge badge-red">${esc(l.action_type)}</span></td><td>${esc(short(l.reason))}</td><td class="mono">${fmtDate(l.created_at)}</td></tr>`).join(''); }

// ─── SETTINGS ────────────────────────────────────────────
function buildSettings() { return `<div class="panel"><div class="panel-header"><div class="panel-title">⚙️ Paramètres</div></div><div id="settings-grid" class="settings-grid">Chargement...</div></div>`; }
async function loadSettings() { const list=await api('list_settings'); const grid=$('settings-grid'); if(!grid) return; grid.innerHTML=list.map(s=>`<div class="setting-item"><div class="setting-key">${esc(s.setting_key)}</div><input class="setting-input" type="text" value="${esc(s.setting_value||'')}" id="set-${esc(s.setting_key)}"><button class="btn btn-green btn-sm" onclick="saveSetting('${esc(s.setting_key)}')">💾</button></div>`).join(''); }
async function saveSetting(key) { const val=$(`set-${key}`)?.value||''; const d=await api('save_setting',`key=${encodeURIComponent(key)}&val=${encodeURIComponent(val)}`); if(d.ok) toast(`💾 ${key} sauvegardé`,'ok'); }

// ─── BROADCAST ───────────────────────────────────────────
function buildBroadcast() { return `
<div class="panel"><div class="panel-header"><div class="panel-title">📢 Diffusion WhatsApp</div><div style="display:flex;gap:8px;"><label><input type="checkbox" id="bc-simulate"> 🎮 Simulation</label><button class="btn btn-dark btn-sm" onclick="doBroadcast()">🚀 Lancer</button></div></div>
<div style="padding:16px;">
  <div style="display:flex;gap:10px;margin-bottom:16px;">
    <button class="btn ${currentBroadcastTab==='groups'?'btn-green':'btn-ghost'} btn-sm" onclick="switchTab('groups')">👥 Groupes</button>
    <button class="btn ${currentBroadcastTab==='inbox'?'btn-green':'btn-ghost'} btn-sm" onclick="switchTab('inbox')">💬 Contacts</button>
    <button class="btn ${currentBroadcastTab==='both'?'btn-green':'btn-ghost'} btn-sm" onclick="switchTab('both')">📢 Les deux</button>
  </div>
  <div id="groups-list" style="margin-bottom:16px;padding:12px;background:var(--wa-sidebar);border-radius:12px;max-height:200px;overflow-y:auto;"></div>
  <div id="contacts-list" style="margin-bottom:16px;padding:12px;background:var(--wa-sidebar);border-radius:12px;max-height:200px;overflow-y:auto;display:none;"></div>
  <textarea id="bc-message" class="broadcast-textarea" placeholder="Votre message..." rows="3" style="width:100%;padding:12px;border-radius:12px;border:1px solid var(--border);margin-bottom:12px;"></textarea>
  <div class="broadcast-preview" id="bc-preview" style="padding:12px;background:var(--wa-light);border-radius:12px;color:#111;font-style:italic;">Aperçu du message...</div>
</div></div>
<div class="panel"><div class="panel-header"><div class="panel-title">📤 Derniers envois</div></div><div class="tbl-wrap"><table><thead><tr><th>Type</th><th>Cible</th><th>Statut</th><th>Date</th></tr></thead><tbody id="bc-queue"><tr class="empty-row"><td colspan="4"><span class="spin"></span></td></tr></tbody></table></div></div>`; }

let currentBroadcastTab = 'groups';
function switchTab(tab) { currentBroadcastTab=tab; document.getElementById('groups-list').style.display=(tab==='groups'||tab==='both')?'block':'none'; document.getElementById('contacts-list').style.display=(tab==='inbox'||tab==='both')?'block':'none'; refreshSelections(); }
async function refreshSelections() { const groups=await api('list_groups'); const groupsDiv=$('groups-list'); if(groupsDiv) groupsDiv.innerHTML=groups.filter(g=>g.actif==1).map(g=>`<label><input type="checkbox" class="group-chk" value="${esc(g.groupe_id)}" checked> ${esc(g.nom)} (${g.nb_membres})</label><br>`).join('')||'<i>Aucun groupe actif</i>';
  const members=await api('list_members'); const contactsDiv=$('contacts-list'); if(contactsDiv) contactsDiv.innerHTML=members.slice(0,100).map(m=>`<label><input type="checkbox" class="contact-chk" value="${esc(m.phone_formatted)}" checked> ${esc(m.profile_name||m.phone_formatted)}</label><br>`).join('')||'<i>Aucun contact</i>'; }
async function doBroadcast() { const msg=$('bc-message')?.value; const simulate=$('bc-simulate')?.checked; let targets=[]; if(currentBroadcastTab==='groups'||currentBroadcastTab==='both') document.querySelectorAll('.group-chk:checked').forEach(c=>targets.push({type:'group',id:c.value})); if(currentBroadcastTab==='inbox'||currentBroadcastTab==='both') document.querySelectorAll('.contact-chk:checked').forEach(c=>targets.push({type:'contact',id:c.value})); if(targets.length===0){toast('Aucune cible','warn');return;} if(!msg){toast('Message vide','warn');return;} toast(`Envoi à ${targets.length} cible(s)...`,''); const res=await api('broadcast',`text=${encodeURIComponent(msg)}&targets=${encodeURIComponent(JSON.stringify(targets))}&simulate=${simulate}`); if(res.ok) toast(res.msg,'ok'); else toast('Erreur','err'); loadBcQueue(); }
async function initBroadcast() { await refreshSelections(); loadBcQueue(); setInterval(()=>{if(currentPage==='broadcast')loadBcQueue();},30000); }
async function loadBcQueue() { const list=await api('list_queue'); const tbody=$('bc-queue'); if(!tbody) return; if(!list.length){tbody.innerHTML='<tr class="empty-row"><td colspan="4">File vide</td></tr>';return;} tbody.innerHTML=list.slice(0,10).map(q=>`<tr><td><span class="badge badge-blue">${esc(q.target_type)}</span></td><td class="mono">${esc(q.target_id||q.phone_number)}</td><td><span class="badge ${q.status=='pending'?'badge-blue':(q.status=='completed'?'badge-green':'badge-red')}">${esc(q.status)}</span></td><td class="mono">${fmtDate(q.created_at)}</td></tr>`).join(''); }

// ─── INIT ─────────────────────────────────────────────────
document.addEventListener('keydown',e=>{ if(e.key==='Escape') document.querySelectorAll('.modal-bg.open').forEach(m=>m.classList.remove('open')); });
goPage('overview');
setInterval(loadStats,60000);
</script>

<?php endif; ?>
</body>
</html>