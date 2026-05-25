<?php
/**
 * ============================================================
 *  NUFOTEC — Dashboard WhatsApp COMPLET
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





// ── Whapi API améliorée avec support de tous les endpoints
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








// ── AJAX ──────────────────────────────────────────────────
if ($authed && isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $act = $_GET['ajax'];

// ── send_message — Envoyer n'importe quel type de message
if ($act === 'send_message') {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true) ?? [];
    
    $to = trim($payload['to'] ?? '');
    $msg_text = trim($payload['text'] ?? '');
    $media_url = trim($payload['media_url'] ?? '');
    $message_type = trim($payload['message_type'] ?? 'text');
    $caption = trim($payload['caption'] ?? $msg_text);
    
    if (empty($to)) {
        echo json_encode(['ok' => false, 'msg' => 'Destinataire requis']);
        exit;
    }
    
    // Nettoyer le numéro
    $to_clean = preg_replace('/\D+/', '', $to);
    if (strlen($to_clean) < 6 && !str_contains($to, '@g.us')) {
        $to_clean = $to_clean . '@s.whatsapp.net';
    }
    
    $body = ['to' => $to_clean];
    $endpoint = 'messages/text';
    
    // Construire le message selon le type
    switch ($message_type) {
        case 'text':
            $endpoint = 'messages/text';
            $body['body'] = $msg_text;
            break;
        case 'link_preview':
            $endpoint = 'messages/link_preview';
            $body['body'] = $msg_text;
            $body['preview'] = true;
            break;
        case 'image':
            $endpoint = 'messages/image';
            $body['image'] = ['link' => $media_url];
            if ($caption) $body['caption'] = $caption;
            break;
        case 'video':
            $endpoint = 'messages/video';
            $body['video'] = ['link' => $media_url];
            if ($caption) $body['caption'] = $caption;
            break;
        case 'audio':
            $endpoint = 'messages/audio';
            $body['audio'] = ['link' => $media_url];
            break;
        case 'voice':
            $endpoint = 'messages/voice';
            $body['voice'] = ['link' => $media_url];
            break;
        case 'document':
            $endpoint = 'messages/document';
            $body['document'] = ['link' => $media_url, 'filename' => basename($media_url)];
            if ($caption) $body['caption'] = $caption;
            break;
        case 'sticker':
            $endpoint = 'messages/sticker';
            $body['sticker'] = ['link' => $media_url];
            break;
        case 'gif':
            $endpoint = 'messages/gif';
            $body['gif'] = ['link' => $media_url];
            if ($caption) $body['caption'] = $caption;
            break;
        case 'location':
            $endpoint = 'messages/location';
            $body['location'] = [
                'name' => $payload['location_name'] ?? 'Location',
                'address' => $payload['address'] ?? '',
                'latitude' => floatval($payload['latitude'] ?? 0),
                'longitude' => floatval($payload['longitude'] ?? 0)
            ];
            break;
        case 'contact':
            $endpoint = 'messages/contact';
            $body['contact'] = [
                'name' => $payload['contact_name'] ?? 'Contact',
                'phone' => $payload['contact_number'] ?? $to_clean
            ];
            break;
        case 'poll':
            $endpoint = 'messages/poll';
            $body['poll'] = [
                'question' => $msg_text,
                'options' => $payload['poll_options'] ?? ['Oui', 'Non'],
                'selectableCount' => intval($payload['selectable_count'] ?? 1)
            ];
            break;
        case 'story':
            $endpoint = 'messages/story';
            if ($media_url) {
                $body['story'] = ['link' => $media_url];
                $body['type'] = 'image';
            } else {
                $body['story'] = ['text' => $msg_text];
                $body['type'] = 'text';
            }
            break;
        default:
            $endpoint = 'messages/text';
            $body['body'] = $msg_text;
    }
    
    // Mode simulation
    if (($payload['simulate'] ?? false) === true) {
        echo json_encode(['ok' => true, 'msg' => 'Mode simulation - message non envoyé', 'simulated' => true]);
        exit;
    }
    
    $result = whapi($endpoint, 'POST', $body);
    
    if ($result && !isset($result['error'])) {
        dbx('INSERT INTO whatsapp_logs (phone_number, message_type, message_content, status, sent_at, created_at) 
             VALUES (?, ?, ?, "sent", NOW(), NOW())', 
             [$to_clean, $message_type, $msg_text ?: $media_url]);
        echo json_encode(['ok' => true, 'msg' => 'Message envoyé avec succès', 'result' => $result]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Erreur lors de l\'envoi', 'debug' => $result]);
    }
    exit;
}







// ── sync_groups — VERSION CORRIGÉE (sans participants_count)
if ($act === 'sync_groups') {
    // Récupérer TOUS les groupes en un seul appel avec count=500
    $data = whapi('groups?count=500');
    
    if (!$data) {
        // Fallback: essayer avec count=200 si 500 ne fonctionne pas
        $data = whapi('groups?count=200');
    }
    
    if (!$data) {
        $cnt = db1('SELECT COUNT(*) c FROM groupes_whatsapp');
        echo json_encode([
            'ok' => true, 
            'msg' => '⚠️ API inaccessible — données locales utilisées', 
            'total' => $cnt->c ?? 0, 
            'degraded' => true
        ]);
        exit;
    }
    
    // Extraire les groupes (différents formats possibles)
    $groups = $data['groups'] ?? $data['data'] ?? [];
    
    if (empty($groups)) {
        echo json_encode(['ok' => true, 'msg' => 'Aucun groupe trouvé sur Whapi', 'total' => 0]);
        exit;
    }
    
    $added = 0;
    $updated = 0;
    $group_ids = [];
    
    foreach ($groups as $g) {
        $gid = $g['id'] ?? '';
        $nom = $g['name'] ?? $g['subject'] ?? 'Groupe sans nom';
        
        if (!$gid) continue;
        
        $group_ids[] = $gid;
        
        // Vérifier si le groupe existe déjà
        $exists = db1('SELECT id FROM groupes_whatsapp WHERE groupe_id = ?', [$gid]);
        
        if ($exists) {
            // Mettre à jour le groupe existant (sans participants_count)
            dbx(
                'UPDATE groupes_whatsapp SET nom = ?, updated_at = NOW() WHERE groupe_id = ?',
                [$nom, $gid]
            );
            $updated++;
        } else {
            // Ajouter le nouveau groupe (sans participants_count)
            dbx(
                'INSERT INTO groupes_whatsapp (groupe_id, nom, actif, created_at, updated_at) 
                 VALUES (?, ?, 1, NOW(), NOW())',
                [$gid, $nom]
            );
            $added++;
        }
    }
    
    $msg = "$added groupe(s) ajouté(s), $updated mis à jour (Total: " . count($groups) . " groupes)";
    
    echo json_encode([
        'ok' => true, 
        'msg' => $msg, 
        'added' => $added,
        'updated' => $updated,
        'total' => count($groups),
        'groups_sample' => array_slice($group_ids, 0, 5)
    ]);
    exit;
}



// ── sync_members — VERSION OPTIMISÉE (1 seul appel API)
if ($act === 'sync_members') {
    $gid_filter = $_GET['gid'] ?? null;
    
    // 🔥 UN SEUL APPEL API POUR TOUS LES GROUPES
    $data = whapi('groups?count=500');
    
    if (!$data || isset($data['error'])) {
        // Fallback: essayer avec un plus petit count
        $data = whapi('groups?count=200');
    }
    
    if (!$data || isset($data['error'])) {
        echo json_encode(['ok' => false, 'msg' => 'Erreur API Whapi - Impossible de récupérer les groupes', 'debug' => $data]);
        exit;
    }
    
    // Extraire la liste des groupes
    $groups_from_api = $data['groups'] ?? $data['data'] ?? [];
    
    if (empty($groups_from_api)) {
        echo json_encode(['ok' => true, 'msg' => 'Aucun groupe trouvé sur Whapi', 'total_members' => 0]);
        exit;
    }
    
    $total_members = 0;
    $groups_processed = 0;
    $groups_not_found = [];
    $debug = [];
    
    // Déterminer quels groupes synchroniser
    if ($gid_filter) {
        // Synchroniser un seul groupe spécifique
        $target_groups = [db1('SELECT groupe_id, nom FROM groupes_whatsapp WHERE groupe_id = ?', [$gid_filter])];
        if (!$target_groups[0]) {
            echo json_encode(['ok' => false, 'msg' => 'Groupe non trouvé en base']);
            exit;
        }
    } else {
        // Synchroniser tous les groupes actifs
        $target_groups = dbq('SELECT groupe_id, nom FROM groupes_whatsapp WHERE actif = 1');
    }
    
    foreach ($target_groups as $g) {
        if (!$g) continue;
        
        // Chercher le groupe dans la réponse de l'API
        $found_group = null;
        foreach ($groups_from_api as $api_group) {
            if (($api_group['id'] ?? '') === $g->groupe_id) {
                $found_group = $api_group;
                break;
            }
        }
        
        if (!$found_group) {
            $groups_not_found[] = $g->groupe_id;
            $debug[$g->groupe_id] = ['status' => 'not_found_in_api'];
            continue;
        }
        
        // 🔥 RÉCUPÉRER LES PARTICIPANTS DIRECTEMENT
        $participants = $found_group['participants'] ?? $found_group['members'] ?? [];
        
        $debug[$g->groupe_id] = [
            'nom' => $found_group['name'] ?? $g->nom,
            'participants_count' => count($participants),
            'total_from_api' => $found_group['participants_count'] ?? count($participants)
        ];
        
        // Mettre à jour le nom du groupe si différent
        $api_nom = $found_group['name'] ?? $found_group['subject'] ?? $g->nom;
        if ($api_nom !== $g->nom) {
            dbx('UPDATE groupes_whatsapp SET nom = ?, updated_at = NOW() WHERE groupe_id = ?', [$api_nom, $g->groupe_id]);
        }
        
        // Parcourir tous les participants
        foreach ($participants as $p) {
            $phone_raw = '';
            $is_admin = 0;
            $name = null;
            
            if (is_string($p)) {
                // Format: "33612345678@s.whatsapp.net"
                $phone_raw = $p;
            } elseif (is_array($p)) {
                // Format standard Whapi
                $phone_raw = $p['id'] ?? $p['phone'] ?? $p['jid'] ?? '';
                
                // Détection admin (Whapi utilise 'rank')
                if (isset($p['rank'])) {
                    $is_admin = ($p['rank'] === 'admin' || $p['rank'] === 'superadmin') ? 1 : 0;
                } elseif (isset($p['isAdmin'])) {
                    $is_admin = $p['isAdmin'] ? 1 : 0;
                } elseif (isset($p['admin'])) {
                    $is_admin = $p['admin'] ? 1 : 0;
                }
                
                $name = $p['name'] ?? $p['pushName'] ?? $p['notify'] ?? $p['contactName'] ?? null;
            }
            
            if (!$phone_raw) continue;
            
            // Nettoyer le numéro (enlever @s.whatsapp.net et autres suffixes)
            $phone_clean = preg_replace('/@[^@]+$/', '', (string)$phone_raw);
            $phone_fmt = preg_replace('/\D+/', '', $phone_clean);
            
            if (!$phone_fmt || strlen($phone_fmt) < 6) continue;
            
            // Insérer ou mettre à jour le participant
            dbx(
                'INSERT INTO whatsapp_participants
                 (groupe_id, phone, phone_formatted, is_admin, violation_count, profile_name, synced_at, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 0, ?, NOW(), NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                   is_admin = VALUES(is_admin),
                   profile_name = COALESCE(VALUES(profile_name), profile_name),
                   synced_at = NOW(), 
                   updated_at = NOW()',
                [$g->groupe_id, $phone_raw, $phone_fmt, $is_admin, $name]
            );
            $total_members++;
        }
        $groups_processed++;
    }
    
    // Supprimer les participants qui ne sont plus dans les groupes (optionnel)
    // Cette étape nettoie les anciens membres qui ont quitté les groupes
    if (!$gid_filter && $groups_processed > 0) {
        $active_groups = array_column($target_groups, 'groupe_id');
        if (!empty($active_groups)) {
            $placeholders = implode(',', array_fill(0, count($active_groups), '?'));
            $deleted = dbx("DELETE FROM whatsapp_participants WHERE groupe_id NOT IN ($placeholders)", $active_groups);
            if ($deleted > 0) {
                $debug['cleanup'] = "{$deleted} anciens participants supprimés";
            }
        }
    }
    
    // Construction du message de retour
    $msg = "✅ $total_members membre(s) synchronisé(s) depuis $groups_processed groupe(s)";
    
    if (!empty($groups_not_found)) {
        $msg .= " ⚠️ Groupes non trouvés dans l'API: " . implode(', ', array_slice($groups_not_found, 0, 3));
        if (count($groups_not_found) > 3) $msg .= '…';
    }
    
    echo json_encode([
        'ok' => true, 
        'msg' => $msg, 
        'total_members' => $total_members,
        'groups_processed' => $groups_processed,
        'groups_not_found' => $groups_not_found,
        'debug' => $debug
    ]);
    exit;
}



    // ── toggle_group
    if ($act==='toggle_group') {
        $gid=$_GET['gid']??'';
        $g=db1('SELECT actif FROM groupes_whatsapp WHERE groupe_id=?',[$gid]);
        if($g) dbx('UPDATE groupes_whatsapp SET actif=? WHERE groupe_id=?',[$g->actif?0:1,$gid]);
        echo json_encode(['ok'=>true,'actif'=>$g?($g->actif?0:1):0]);
        exit;
    }

    // ── blacklist
    if ($act==='blacklist_add') {
        $phone=preg_replace('/\D/','',($_GET['phone']??''));
        $reason=$_GET['reason']??'Ajouté manuellement';
        if($phone) dbx('INSERT INTO whatsapp_blacklist (phone_number,reason,created_at) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE reason=VALUES(reason)',[$phone,$reason]);
        echo json_encode(['ok'=>true]);
        exit;
    }
    if ($act==='blacklist_remove') {
        dbx('DELETE FROM whatsapp_blacklist WHERE phone_number=?',[preg_replace('/\D/','',($_GET['phone']??''))]);
        echo json_encode(['ok'=>true]);
        exit;
    }

    // ── reset violations
    if ($act==='reset_violations') {
        dbx('UPDATE whatsapp_participants SET violation_count=0 WHERE phone_formatted=?',[preg_replace('/\D/','',($_GET['phone']??''))]);
        echo json_encode(['ok'=>true]);
        exit;
    }

    // ── clear queue
    if ($act==='clear_queue') {
        $status=$_GET['status']??'completed';
        dbx('DELETE FROM whatsapp_queue WHERE status=?',[$status]);
        echo json_encode(['ok'=>true]);
        exit;
    }

    // ── BROADCAST : envoyer message/audio/image/tous
    if ($act==='broadcast') {
        $raw      = file_get_contents('php://input');
        $payload  = json_decode($raw,true)??[];
        $msg_text = trim($payload['text']??'');
        $media_url= trim($payload['media_url']??'');
        $media_type=trim($payload['media_type']??'');  // image|audio|video|document
        $targets  = $payload['targets']??'groups';     // groups|inbox|both
        $errors=[]; $sent=0;

        // Construire le corps du message Whapi
        $buildMsg = function(string $to) use ($msg_text,$media_url,$media_type): ?array {
            if ($media_url && $media_type) {
                // Message avec média (+ texte optionnel)
                $body = ['to'=>$to];
                if ($media_type==='image') {
                    $body['image']=['link'=>$media_url];
                    if($msg_text) $body['caption']=$msg_text;
                } elseif ($media_type==='audio') {
                    $body['audio']=['link'=>$media_url];
                } elseif ($media_type==='video') {
                    $body['video']=['link'=>$media_url];
                    if($msg_text) $body['caption']=$msg_text;
                } elseif ($media_type==='document') {
                    $body['document']=['link'=>$media_url,'filename'=>basename($media_url)];
                    if($msg_text) $body['caption']=$msg_text;
                }
                return $body;
            } elseif ($msg_text) {
                return ['to'=>$to,'body'=>$msg_text];
            }
            return null;
        };

        $sendTo = function(string $to, string $type) use ($buildMsg,&$sent,&$errors): void {
            $ep = $type==='text' ? 'messages/text' : "messages/{$type}";
            // Déterminer l'endpoint correct
            $body = $buildMsg($to);
            if (!$body) return;
            $ep = isset($body['image']) ? 'messages/image'
                : (isset($body['audio']) ? 'messages/audio'
                : (isset($body['video']) ? 'messages/video'
                : (isset($body['document']) ? 'messages/document'
                : 'messages/text')));
            $r = whapi($ep,'POST',$body);
            if ($r && !isset($r['error'])) $sent++;
            else $errors[] = $to;
            usleep(500000); // 0.5s délai anti-spam
        };

        // Envoyer aux groupes actifs
        if ($targets==='groups' || $targets==='both') {
            $groups = dbq('SELECT groupe_id FROM groupes_whatsapp WHERE actif=1');
            foreach ($groups as $g) {
                // Mettre en queue pour traitement asynchrone
                dbx('INSERT INTO whatsapp_queue (target_type,target_id,message_type,message_data,media_url,status,priority,created_at)
                     VALUES (?,?,?,?,?,\'pending\',1,NOW())',
                    ['group',$g->groupe_id,$media_type?:'text',$msg_text,$media_url]);
                $sent++;
            }
        }
        // Envoyer dans inbox de tous les membres
        if ($targets==='inbox' || $targets==='both') {
            $phones = dbq('SELECT DISTINCT phone_formatted FROM whatsapp_participants WHERE phone_formatted!=\'\'');
            foreach ($phones as $p) {
                dbx('INSERT INTO whatsapp_queue (target_type,phone_number,message_type,message_data,media_url,status,priority,created_at)
                     VALUES (?,?,?,?,?,\'pending\',2,NOW())',
                    ['inbox',$p->phone_formatted,$media_type?:'text',$msg_text,$media_url]);
                $sent++;
            }
        }

        echo json_encode(['ok'=>true,'msg'=>"$sent message(s) mis en file",'errors'=>count($errors)]);
        exit;
    }

    // ── diag
    if ($act==='diag') {
        $diag=['php'=>PHP_VERSION,'curl'=>curl_version()['version']??'?','pdo_mysql'=>extension_loaded('pdo_mysql')];
        try {
            db(); $diag['db']='OK';
            $tables=[]; $st=db()->query('SHOW TABLES');
            while($r=$st->fetch(PDO::FETCH_NUM)) $tables[]=$r[0];
            $diag['tables']=$tables;
        } catch(\PDOException $e) { $diag['db']='ERR: '.$e->getMessage(); }
        $diag['whapi']=whapi('health')!==null;
        echo json_encode(['ok'=>true,'diag'=>$diag]);
        exit;
    }

    // ── stats
    if ($act==='stats') {
        echo json_encode([
            'groups'        => (db1('SELECT COUNT(*) c FROM groupes_whatsapp')->c??0),
            'groups_actif'  => (db1('SELECT COUNT(*) c FROM groupes_whatsapp WHERE actif=1')->c??0),
            'members'       => (db1('SELECT COUNT(*) c FROM whatsapp_participants')->c??0),
            'admins'        => (db1('SELECT COUNT(DISTINCT phone_formatted) c FROM whatsapp_participants WHERE is_admin=1')->c??0),
            'blacklist'     => (db1('SELECT COUNT(*) c FROM whatsapp_blacklist')->c??0),
            'queue_pending' => (db1("SELECT COUNT(*) c FROM whatsapp_queue WHERE status='pending'")->c??0),
            'queue_failed'  => (db1("SELECT COUNT(*) c FROM whatsapp_queue WHERE status='failed'")->c??0),
            'logs_today'    => (db1('SELECT COUNT(*) c FROM whatsapp_logs WHERE DATE(created_at)=CURDATE()')->c??0),
            'violations'    => (db1('SELECT COUNT(*) c FROM whatsapp_participants WHERE violation_count>0')->c??0),
            'inbox'         => (db1('SELECT COUNT(*) c FROM whatsapp_inbox')->c??0),
        ]);
        exit;
    }

    // ── list_groups
    if ($act==='list_groups') {
        echo json_encode(dbq(
            'SELECT g.*, (SELECT COUNT(*) FROM whatsapp_participants p WHERE p.groupe_id=g.groupe_id) as nb_membres
             FROM groupes_whatsapp g ORDER BY g.nom'
        ));
        exit;
    }

    // ── list_members
    if ($act==='list_members') {
        $gid=$_GET['gid']??''; $search='%'.($_GET['search']??'').'%';
        $sql='SELECT p.*,g.nom as groupe_nom FROM whatsapp_participants p
              LEFT JOIN groupes_whatsapp g ON g.groupe_id=p.groupe_id
              WHERE (p.phone_formatted LIKE ? OR p.profile_name LIKE ?)';
        $params=[$search,$search];
        if ($gid) { $sql.=' AND p.groupe_id=?'; $params[]=$gid; }
        $sql.=' ORDER BY p.violation_count DESC,p.updated_at DESC LIMIT 300';
        echo json_encode(dbq($sql,$params));
        exit;
    }

    // ── list_blacklist
    if ($act==='list_blacklist') {
        echo json_encode(dbq('SELECT * FROM whatsapp_blacklist ORDER BY created_at DESC LIMIT 200'));
        exit;
    }

    // ── list_queue
    if ($act==='list_queue') {
        echo json_encode(dbq('SELECT * FROM whatsapp_queue ORDER BY created_at DESC LIMIT 150'));
        exit;
    }

    // ── list_inbox
    if ($act==='list_inbox') {
        echo json_encode(dbq('SELECT * FROM whatsapp_inbox ORDER BY last_message_at DESC LIMIT 150'));
        exit;
    }

    // ── list_logs
    if ($act==='list_logs') {
        echo json_encode(dbq('SELECT * FROM whatsapp_logs ORDER BY created_at DESC LIMIT 200'));
        exit;
    }

    // ── list_security
    if ($act==='list_security') {
        echo json_encode(dbq('SELECT * FROM whatsapp_security_logs ORDER BY created_at DESC LIMIT 150'));
        exit;
    }

    // ── list_settings
    if ($act==='list_settings') {
        echo json_encode(dbq('SELECT * FROM whatsapp_settings ORDER BY id'));
        exit;
    }

    // ── save_setting
    if ($act==='save_setting') {
        $key=$_GET['key']??''; $val=$_GET['val']??'';
        if ($key) dbx('UPDATE whatsapp_settings SET setting_value=? WHERE setting_key=?',[$val,$key]);
        echo json_encode(['ok'=>true]);
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
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>NUFOTEC · WhatsApp Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  /* WhatsApp palette */
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

  /* Accents */
  --red:    #FF3B30;
  --amber:  #FF9500;
  --blue:   #007AFF;
  --purple: #5856D6;

  /* Text */
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

/* ── LOGIN ──────────────────────────────────────────── */
.login-wrap{
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg,var(--wa-green-dd) 0%,var(--wa-green-d) 50%,var(--wa-green) 100%);
}
.login-box{
  background:#fff;border-radius:20px;padding:48px 40px;width:380px;
  box-shadow:var(--shadow-lg);text-align:center;
}
.login-logo{font-size:56px;margin-bottom:8px;}
.login-brand{font-size:22px;font-weight:900;color:var(--wa-green-dd);letter-spacing:-.5px;}
.login-sub{font-size:13px;color:var(--text2);margin-bottom:32px;}
.login-box input{
  width:100%;border:1.5px solid var(--border);border-radius:10px;
  padding:13px 16px;font-family:var(--mono);font-size:14px;color:var(--text);
  outline:none;transition:border-color .2s;margin-bottom:14px;background:#f8f9fa;
}
.login-box input:focus{border-color:var(--wa-green);}
.login-err{color:var(--red);font-size:13px;margin-bottom:12px;}

/* ── BUTTONS ─────────────────────────────────────── */
.btn{
  display:inline-flex;align-items:center;gap:7px;padding:10px 18px;
  border-radius:10px;font-family:var(--font);font-size:13px;font-weight:700;
  cursor:pointer;border:none;transition:all .18s;white-space:nowrap;text-decoration:none;
}
.btn-green {background:var(--wa-green);color:#fff;}
.btn-green:hover{background:var(--wa-green-d);transform:translateY(-1px);}
.btn-dark  {background:var(--wa-green-dd);color:#fff;}
.btn-dark:hover{background:#043c35;transform:translateY(-1px);}
.btn-red   {background:rgba(255,59,48,.1);color:var(--red);border:1.5px solid rgba(255,59,48,.3);}
.btn-red:hover{background:rgba(255,59,48,.2);}
.btn-blue  {background:rgba(0,122,255,.1);color:var(--blue);border:1.5px solid rgba(0,122,255,.25);}
.btn-blue:hover{background:rgba(0,122,255,.2);}
.btn-amber {background:rgba(255,149,0,.1);color:var(--amber);border:1.5px solid rgba(255,149,0,.3);}
.btn-amber:hover{background:rgba(255,149,0,.2);}
.btn-ghost {background:transparent;color:var(--text2);border:1.5px solid var(--border);}
.btn-ghost:hover{background:var(--wa-sidebar);color:var(--text);}
.btn-sm{padding:6px 12px;font-size:12px;}
.btn-full{width:100%;justify-content:center;}
.btn-icon{padding:7px;border-radius:8px;}

/* ── LAYOUT ──────────────────────────────────────── */
.shell{display:flex;min-height:100vh;}

/* SIDEBAR */
.sidebar{
  width:240px;min-width:240px;
  background:var(--wa-panel);
  border-right:1px solid var(--border);
  display:flex;flex-direction:column;
  position:fixed;top:0;left:0;height:100vh;z-index:100;
  box-shadow:2px 0 8px rgba(0,0,0,.06);
}
.sidebar-header{
  background:var(--wa-header);
  padding:18px 20px;
  display:flex;align-items:center;gap:12px;
}
.sidebar-header .logo-icon{font-size:28px;}
.sidebar-header .logo-text .brand{font-size:17px;font-weight:900;color:#fff;letter-spacing:-.3px;}
.sidebar-header .logo-text .sub{font-size:10px;color:rgba(255,255,255,.7);font-weight:600;letter-spacing:.15em;text-transform:uppercase;}
.nav{flex:1;padding:12px 8px;overflow-y:auto;}
.nav-section{font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--text3);padding:12px 12px 6px;}
.nav-item{
  display:flex;align-items:center;gap:10px;padding:10px 12px;
  border-radius:10px;font-size:13px;font-weight:700;color:var(--text2);
  cursor:pointer;transition:all .15s;border:none;background:none;width:100%;text-align:left;
}
.nav-item:hover{color:var(--text);background:var(--wa-sidebar);}
.nav-item.active{color:var(--wa-green-dd);background:rgba(37,211,102,.1);}
.nav-item .ico{font-size:17px;width:22px;text-align:center;}
.nav-badge{
  margin-left:auto;background:var(--wa-green);color:#fff;
  font-size:10px;font-weight:800;padding:1px 7px;border-radius:20px;min-width:20px;text-align:center;
}
.nav-badge.red{background:var(--red);}
.sidebar-footer{padding:12px 8px;border-top:1px solid var(--border2);}

/* MAIN */
.main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh;}
.topbar{
  background:var(--wa-header);
  padding:0 24px;height:60px;
  display:flex;align-items:center;justify-content:space-between;
  position:sticky;top:0;z-index:50;
}
.topbar-title{font-size:17px;font-weight:800;color:#fff;}
.topbar-actions{display:flex;gap:8px;align-items:center;}
.topbar .btn-green{background:rgba(255,255,255,.2);border:1.5px solid rgba(255,255,255,.35);color:#fff;}
.topbar .btn-green:hover{background:rgba(255,255,255,.3);}
.topbar .btn-ghost{border-color:rgba(255,255,255,.3);color:rgba(255,255,255,.85);}
.topbar .btn-ghost:hover{background:rgba(255,255,255,.1);color:#fff;}

.content{padding:20px 24px;flex:1;background:var(--wa-chat-bg);}
.page{display:none;}
.page.active{display:block;}

/* ── STATS CARDS ─────────────────────────────────── */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:20px;}
.stat-card{
  background:var(--wa-panel);border-radius:var(--radius);padding:18px;
  box-shadow:var(--shadow);border-top:3px solid var(--wa-green);
  position:relative;overflow:hidden;transition:transform .18s,box-shadow .18s;
}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-lg);}
.stat-card.red{border-top-color:var(--red);}
.stat-card.amber{border-top-color:var(--amber);}
.stat-card.blue{border-top-color:var(--blue);}
.stat-card.purple{border-top-color:var(--purple);}
.stat-card.teal{border-top-color:var(--wa-teal);}
.stat-icon{font-size:24px;margin-bottom:8px;}
.stat-label{font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text2);margin-bottom:6px;}
.stat-val{font-size:32px;font-weight:900;color:var(--wa-green-dd);line-height:1;font-variant-numeric:tabular-nums;}
.stat-card.red .stat-val{color:var(--red);}
.stat-card.amber .stat-val{color:var(--amber);}
.stat-card.blue .stat-val{color:var(--blue);}
.stat-card.purple .stat-val{color:var(--purple);}
.stat-card.teal .stat-val{color:var(--wa-teal);}
.stat-sub{font-size:11px;color:var(--text3);margin-top:4px;}

/* ── PANELS ──────────────────────────────────────── */
.panel{background:var(--wa-panel);border-radius:var(--radius);overflow:hidden;margin-bottom:18px;box-shadow:var(--shadow);}
.panel-header{
  padding:14px 20px;
  border-bottom:1px solid var(--border2);
  display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;
  background:var(--wa-panel);
}
.panel-title{font-size:14px;font-weight:800;color:var(--text);}
.panel-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}

/* ── BROADCAST PANEL ─────────────────────────────── */
.broadcast-panel{
  background:linear-gradient(135deg,var(--wa-green-dd),var(--wa-green-d));
  border-radius:var(--radius);padding:24px;margin-bottom:18px;
  box-shadow:var(--shadow-lg);color:#fff;
}
.broadcast-panel h2{font-size:18px;font-weight:900;margin-bottom:4px;}
.broadcast-panel p{font-size:13px;opacity:.8;margin-bottom:20px;}
.broadcast-form{display:flex;flex-direction:column;gap:12px;}
.broadcast-textarea{
  width:100%;background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.3);
  border-radius:10px;padding:14px;color:#fff;font-family:var(--font);font-size:14px;
  resize:vertical;min-height:80px;outline:none;transition:border-color .2s;
  placeholder-color:rgba(255,255,255,.6);
}
.broadcast-textarea::placeholder{color:rgba(255,255,255,.55);}
.broadcast-textarea:focus{border-color:rgba(255,255,255,.7);}
.broadcast-row{display:flex;gap:10px;flex-wrap:wrap;}
.broadcast-input{
  flex:1;min-width:180px;background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.3);
  border-radius:10px;padding:11px 14px;color:#fff;font-family:var(--mono);font-size:13px;
  outline:none;transition:border-color .2s;
}
.broadcast-input::placeholder{color:rgba(255,255,255,.55);}
.broadcast-input:focus{border-color:rgba(255,255,255,.7);}
.broadcast-select{
  background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.3);
  border-radius:10px;padding:11px 14px;color:#fff;font-family:var(--font);font-size:13px;
  font-weight:700;outline:none;cursor:pointer;
}
.broadcast-select option{background:var(--wa-green-dd);color:#fff;}
.media-tabs{display:flex;gap:6px;flex-wrap:wrap;}
.media-tab{
  padding:7px 14px;border-radius:20px;font-size:12px;font-weight:700;
  background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.25);
  color:rgba(255,255,255,.8);cursor:pointer;transition:all .15s;
}
.media-tab:hover,.media-tab.active{background:rgba(255,255,255,.3);color:#fff;border-color:rgba(255,255,255,.6);}
.broadcast-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.btn-send{
  background:#fff;color:var(--wa-green-dd);padding:12px 28px;border-radius:10px;
  font-size:14px;font-weight:900;border:none;cursor:pointer;transition:all .2s;
  display:inline-flex;align-items:center;gap:8px;
}
.btn-send:hover{background:var(--wa-light);transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.2);}
.send-status{font-size:13px;opacity:.85;display:none;}

/* ── TABLE ───────────────────────────────────────── */
.tbl-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{
  text-align:left;padding:10px 16px;font-size:11px;font-weight:800;
  letter-spacing:.07em;text-transform:uppercase;color:var(--text2);
  border-bottom:1.5px solid var(--border2);white-space:nowrap;background:var(--wa-sidebar);
}
td{padding:11px 16px;border-bottom:1px solid var(--border2);vertical-align:middle;color:var(--text);}
tr:last-child td{border-bottom:none;}
tr:hover td{background:rgba(37,211,102,.04);}
.mono{font-family:var(--mono);font-size:12px;color:var(--text2);}
.empty-row td{text-align:center;color:var(--text3);padding:36px;}

/* ── BADGES ──────────────────────────────────────── */
.badge{
  display:inline-flex;align-items:center;gap:4px;padding:3px 9px;
  border-radius:20px;font-size:11px;font-weight:800;font-family:var(--mono);
}
.badge-green {background:rgba(37,211,102,.15);color:var(--wa-green-d);}
.badge-dark  {background:rgba(7,94,84,.12);color:var(--wa-green-dd);}
.badge-red   {background:rgba(255,59,48,.12);color:var(--red);}
.badge-amber {background:rgba(255,149,0,.12);color:var(--amber);}
.badge-blue  {background:rgba(0,122,255,.12);color:var(--blue);}
.badge-gray  {background:rgba(84,101,111,.1);color:var(--text2);}
.badge-purple{background:rgba(88,86,214,.12);color:var(--purple);}
.badge-teal  {background:rgba(0,191,165,.12);color:var(--wa-teal);}

/* ── SEARCH ──────────────────────────────────────── */
.search-input{
  background:var(--wa-sidebar);border:1.5px solid var(--border);border-radius:8px;
  padding:8px 12px;color:var(--text);font-family:var(--mono);font-size:12px;
  outline:none;width:200px;transition:border-color .2s;
}
.search-input:focus{border-color:var(--wa-green);}

/* ── GROUP CARDS ─────────────────────────────────── */
.group-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;padding:16px;}
.group-card{
  background:var(--wa-sidebar);border:1.5px solid var(--border2);border-radius:var(--radius);
  padding:16px;transition:all .18s;
}
.group-card:hover{border-color:var(--wa-green);box-shadow:0 2px 12px rgba(37,211,102,.15);}
.group-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:10px;}
.group-name{font-size:13px;font-weight:800;color:var(--text);word-break:break-word;line-height:1.3;}
.group-id{font-family:var(--mono);font-size:10px;color:var(--text3);margin-top:3px;}
.group-footer{display:flex;gap:6px;flex-wrap:wrap;margin-top:12px;}

/* ── TOAST ───────────────────────────────────────── */
#toast{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none;}
.toast-item{
  background:#fff;border-radius:10px;padding:12px 18px;font-size:13px;font-weight:700;
  max-width:320px;box-shadow:var(--shadow-lg);animation:toastIn .25s ease;pointer-events:all;
  border-left:4px solid var(--wa-green);display:flex;align-items:center;gap:10px;
}
.toast-item.err{border-left-color:var(--red);}
.toast-item.warn{border-left-color:var(--amber);}
@keyframes toastIn{from{transform:translateX(20px);opacity:0}to{transform:none;opacity:1}}

/* ── LOADER ──────────────────────────────────────── */
.spin{display:inline-block;width:14px;height:14px;border:2px solid var(--border);border-top-color:var(--wa-green);border-radius:50%;animation:spin .6s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── MODAL ───────────────────────────────────────── */
.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:500;display:none;align-items:center;justify-content:center;backdrop-filter:blur(2px);}
.modal-bg.open{display:flex;}
.modal{background:#fff;border-radius:16px;padding:28px;width:460px;max-width:95vw;box-shadow:var(--shadow-lg);}
.modal h3{font-size:17px;font-weight:900;margin-bottom:16px;color:var(--wa-green-dd);}
.modal label{display:block;font-size:12px;color:var(--text2);margin-bottom:5px;font-weight:700;}
.modal input,.modal select,.modal textarea{
  width:100%;background:var(--wa-sidebar);border:1.5px solid var(--border);border-radius:8px;
  padding:10px 14px;color:var(--text);font-family:var(--mono);font-size:13px;
  outline:none;margin-bottom:14px;transition:border-color .2s;
}
.modal input:focus,.modal select:focus,.modal textarea:focus{border-color:var(--wa-green);}
.modal-footer{display:flex;gap:10px;justify-content:flex-end;margin-top:4px;}

/* ── WARNING BANNER ──────────────────────────────── */
.warn-banner{
  background:rgba(255,149,0,.1);border-left:4px solid var(--amber);
  padding:10px 16px;margin-bottom:16px;font-size:13px;border-radius:8px;color:#804800;
}

/* ── SETTINGS ────────────────────────────────────── */
.settings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;padding:16px;}
.setting-item{
  background:var(--wa-sidebar);border:1.5px solid var(--border2);border-radius:10px;padding:16px;
}
.setting-key{font-size:12px;font-weight:800;color:var(--text2);margin-bottom:6px;font-family:var(--mono);}
.setting-input{
  width:100%;background:#fff;border:1.5px solid var(--border);border-radius:8px;
  padding:9px 12px;color:var(--text);font-family:var(--mono);font-size:13px;
  outline:none;transition:border-color .2s;
}
.setting-input:focus{border-color:var(--wa-green);}

/* ── SCROLLBAR ───────────────────────────────────── */
::-webkit-scrollbar{width:5px;height:5px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px;}

/* ── RESPONSIVE ──────────────────────────────────── */
@media(max-width:768px){
  .sidebar{width:56px;min-width:56px;}
  .sidebar .brand,.sidebar .sub,.nav-item span:not(.ico),.sidebar-footer .btn span:not(.ico),.nav-section{display:none;}
  .main{margin-left:56px;}
  .stats-grid{grid-template-columns:repeat(2,1fr);}
  .broadcast-row{flex-direction:column;}
}
</style>
</head>
<body>

<?php if (!$authed): ?>
<!-- ═══════════ LOGIN ═══════════ -->
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
      <input type="password" name="password" placeholder="Mot de passe" autofocus autocomplete="current-password">
      <button type="submit" class="btn btn-dark btn-full" style="font-size:15px;padding:14px;">Connexion →</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ═══════════ APP ═══════════ -->
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
    <a href="?logout=1" class="btn btn-ghost btn-sm btn-full"><span class="ico">🔒</span><span>Déconnexion</span></a>
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

<!-- MODAL Blacklist -->
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
// ─── Utilitaires ─────────────────────────────────────────
const $  = id => document.getElementById(id);
const esc = s => s==null?'':String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
const fmtDate = s => s ? s.replace('T',' ').substring(0,16) : '—';
const short   = (s,n=35) => !s?'—':(s.length>n?s.substring(0,n)+'…':s);

let currentPage = '';
let degraded = false;

async function api(action, params='') {
  const sep = params?'&':'';
  const r   = await fetch(`?ajax=${action}${sep}${params}`);
  return r.json();
}
async function apiPost(action, body) {
  const r = await fetch(`?ajax=${action}`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
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

const PAGES = {
    overview:  { title:'Vue d\'ensemble',  build:buildOverview,   load:loadOverview  },
    broadcast: { title:'Diffusion',         build:buildBroadcast,  load:initBroadcast },
    groups:    { title:'Groupes',           build:buildGroups,     load:loadGroups    },
    members:   { title:'Membres',           build:buildMembers,    load:loadMembersPage},
    inbox:     { title:'Inbox',             build:buildInbox,      load:loadInbox     },
    blacklist: { title:'Blacklist',         build:buildBlacklist,  load:loadBlacklist },
    queue:     { title:'File d\'envoi',     build:buildQueue,      load:loadQueue     },
    logs:      { title:'Logs',              build:buildLogs,       load:loadLogs      },
    security:  { title:'Sécurité',         build:buildSecurity,   load:loadSecurity  },
    settings:  { title:'Paramètres',       build:buildSettings,   load:loadSettings  },
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
    // bind events after build
    bindPageEvents(name);
  }
  el.classList.add('active');
  $('page-title').textContent = pg.title;
  document.querySelectorAll('.nav-item').forEach(n=>{
    if(n.getAttribute('onclick')?.includes(`'${name}'`)) n.classList.add('active');
  });
  currentPage = name;
  pg.load();
}

function bindPageEvents(name) {
  if (name==='blacklist') {
    $('modal-blacklist')?.addEventListener('click',e=>{ if(e.target===$('modal-blacklist')) closeModal('modal-blacklist'); });
  }
}

// ─── OVERVIEW ────────────────────────────────────────────
function buildOverview() { return `
<div class="stats-grid">
  <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-label">Groupes actifs</div><div class="stat-val" id="s-groups">—</div><div class="stat-sub" id="s-groups-sub">sur — total</div></div>
  <div class="stat-card blue"><div class="stat-icon">👤</div><div class="stat-label">Membres</div><div class="stat-val" id="s-members">—</div><div class="stat-sub" id="s-admins-sub">— admins</div></div>
  <div class="stat-card teal"><div class="stat-icon">📥</div><div class="stat-label">Inbox</div><div class="stat-val" id="s-inbox">—</div><div class="stat-sub">contacts</div></div>
  <div class="stat-card red"><div class="stat-icon">🚫</div><div class="stat-label">Blacklist</div><div class="stat-val" id="s-blacklist">—</div><div class="stat-sub">bloqués</div></div>
  <div class="stat-card amber"><div class="stat-icon">📤</div><div class="stat-label">En attente</div><div class="stat-val" id="s-queue">—</div><div class="stat-sub" id="s-queue-sub">— échecs</div></div>
  <div class="stat-card purple"><div class="stat-icon">⚠️</div><div class="stat-label">Violations</div><div class="stat-val" id="s-violations">—</div><div class="stat-sub">membres signalés</div></div>
</div>

<div class="panel">
  <div class="panel-header"><div class="panel-title">⚡ Actions rapides</div></div>
  <div style="padding:16px;display:flex;gap:10px;flex-wrap:wrap;">
    <button class="btn btn-green" onclick="goPage('broadcast')">📢 Nouvelle diffusion</button>
    <button class="btn btn-dark" onclick="syncGroups()">↻ Sync groupes Whapi</button>
    <button class="btn btn-blue" onclick="syncMembers(null)">↻ Sync membres</button>
    <button class="btn btn-ghost" onclick="loadStats()">↻ Actualiser stats</button>
    <button class="btn btn-amber" onclick="$('modal-blacklist').classList.add('open')">🚫 Blacklister numéro</button>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
  <div class="panel">
    <div class="panel-header"><div class="panel-title">🛡 Dernières violations</div><button class="btn btn-ghost btn-sm" onclick="goPage('security')">Voir tout →</button></div>
    <div class="tbl-wrap"><table><thead><tr><th>Numéro</th><th>Type</th><th>Date</th></tr></thead><tbody id="ov-security"><tr class="empty-row"><td colspan="3"><span class="spin"></span></td></tr></tbody></table></div>
  </div>
  <div class="panel">
    <div class="panel-header"><div class="panel-title">📋 Logs récents</div><button class="btn btn-ghost btn-sm" onclick="goPage('logs')">Voir tout →</button></div>
    <div class="tbl-wrap"><table><thead><tr><th>Téléphone</th><th>Type</th><th>Statut</th></tr></thead><tbody id="ov-logs"><tr class="empty-row"><td colspan="3"><span class="spin"></span></td></tr></tbody></table></div>
  </div>
</div>`; }

async function loadOverview() {
  await loadStats();
  // security
  const sec = await api('list_security');
  const sb = $('ov-security');
  if(sb) sb.innerHTML = sec.length ? sec.slice(0,6).map(l=>`<tr>
    <td class="mono">${esc(l.sender)}</td>
    <td><span class="badge badge-red">${esc(l.action_type)}</span></td>
    <td class="mono">${fmtDate(l.created_at)}</td></tr>`).join('') : '<tr class="empty-row"><td colspan="3">Aucune violation</td></tr>';
  // logs
  const logs = await api('list_logs');
  const sb2 = $('ov-logs');
  const stBadge = {sent:'badge-green',failed:'badge-red',received:'badge-blue',processing:'badge-amber'};
  if(sb2) sb2.innerHTML = logs.length ? logs.slice(0,6).map(l=>`<tr>
    <td class="mono">${esc(l.phone_number)}</td>
    <td><span class="badge badge-gray">${esc(l.message_type)}</span></td>
    <td><span class="badge ${stBadge[l.status]||'badge-gray'}">${esc(l.status)}</span></td></tr>`).join('') : '<tr class="empty-row"><td colspan="3">Aucun log</td></tr>';
}

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
  set('s-queue-sub',`${d.queue_failed} échecs`);
  set('s-violations',d.violations);
  const nb = $('nb-blacklist');
  if(nb&&d.blacklist>0){nb.textContent=d.blacklist;nb.style.display='';}
  const nq = $('nb-queue');
  if(nq&&d.queue_pending>0){nq.textContent=d.queue_pending;nq.style.display='';}
}

// ─── BROADCAST — Interface complète avec tous les types de messages
function buildBroadcast() { return `
<div class="broadcast-panel">
  <h2>📢 Centre de Diffusion</h2>
  <p>Envoyez des messages texte, images, audios, vidéos, documents, stickers, sondages, contacts, localisation et stories.</p>
  
  <!-- Onglets de sélection de cible -->
  <div style="display:flex; gap:10px; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,.2); padding-bottom:10px; flex-wrap:wrap;">
    <button class="media-tab active" onclick="switchBroadcastTab('groups')" id="tab-groups">👥 Groupes</button>
    <button class="media-tab" onclick="switchBroadcastTab('inbox')" id="tab-inbox">💬 Contacts</button>
    <button class="media-tab" onclick="switchBroadcastTab('both')" id="tab-both">📢 Groupes + Contacts</button>
  </div>
  
  <!-- Zone Groupes -->
  <div id="broadcast-groups">
    <div style="margin-bottom:15px;">
      <label style="font-size:12px; font-weight:700; opacity:0.8;">📌 Sélectionner les groupes</label>
      <select id="bc-group-select" multiple style="width:100%; background:rgba(255,255,255,.15); border-radius:8px; padding:10px; color:#fff; margin-top:5px;">
        <option value="all">📋 Tous les groupes actifs</option>
      </select>
    </div>
  </div>
  
  <!-- Zone Contacts -->
  <div id="broadcast-inbox" style="display:none;">
    <div style="margin-bottom:15px;">
      <label style="font-size:12px; font-weight:700; opacity:0.8;">📞 Sélectionner les contacts</label>
      <select id="bc-contact-select" multiple style="width:100%; background:rgba(255,255,255,.15); border-radius:8px; padding:10px; color:#fff; margin-top:5px;">
        <option value="all">📋 Tous les contacts</option>
      </select>
    </div>
    <div>
      <label style="font-size:12px; font-weight:700; opacity:0.8;">📱 Ou entrer un numéro spécifique</label>
      <input type="text" id="bc-custom-phone" class="broadcast-input" placeholder="+257XXXXXXXXX" style="width:100%; margin-top:5px;">
    </div>
  </div>
  
  <!-- Type de message -->
  <div style="margin-top:20px;">
    <label style="font-size:12px; font-weight:700; opacity:0.8;">🎯 TYPE DE MESSAGE</label>
    <select id="message-type-select" class="broadcast-select" style="width:100%; margin-top:5px;" onchange="changeMessageType()">
      <option value="text">💬 Texte simple</option>
      <option value="link_preview">🔗 Texte avec aperçu de lien</option>
      <option value="image">🖼 Image</option>
      <option value="video">🎥 Vidéo</option>
      <option value="audio">🎵 Audio</option>
      <option value="voice">🎤 Message vocal</option>
      <option value="document">📑 Document</option>
      <option value="sticker">🎭 Sticker</option>
      <option value="gif">🎬 GIF</option>
      <option value="location">📍 Localisation</option>
      <option value="contact">👤 Contact</option>
      <option value="poll">📊 Sondage</option>
      <option value="story">📖 Story</option>
    </select>
  </div>
  
  <!-- Zone message texte -->
  <div id="message-text-area" style="margin-top:15px;">
    <textarea class="broadcast-textarea" id="bc-text" placeholder="Votre message ici…" rows="4"></textarea>
  </div>
  
  <!-- Zone média (URL) -->
  <div id="message-media-area" style="margin-top:15px; display:none;">
    <input class="broadcast-input" type="url" id="bc-media-url" placeholder="URL du fichier (https://...)" style="width:100%;">
    <div style="font-size:11px; opacity:0.6; margin-top:4px;">⚠️ L'URL doit être accessible publiquement</div>
  </div>
  
  <!-- Zone localisation -->
  <div id="message-location-area" style="margin-top:15px; display:none;">
    <input class="broadcast-input" type="text" id="bc-lat" placeholder="Latitude" style="width:48%; display:inline-block; margin-right:4%;">
    <input class="broadcast-input" type="text" id="bc-lng" placeholder="Longitude" style="width:48%; display:inline-block;">
    <input class="broadcast-input" type="text" id="bc-location-name" placeholder="Nom du lieu" style="width:100%; margin-top:8px;">
    <input class="broadcast-input" type="text" id="bc-address" placeholder="Adresse" style="width:100%; margin-top:8px;">
  </div>
  
  <!-- Zone sondage -->
  <div id="message-poll-area" style="margin-top:15px; display:none;">
    <div id="poll-options">
      <div style="display:flex; gap:8px; margin-bottom:8px;">
        <input class="broadcast-input" type="text" placeholder="Option 1" style="flex:1;" value="Oui">
        <button class="btn btn-ghost btn-sm" onclick="removePollOption(this)">✖</button>
      </div>
      <div style="display:flex; gap:8px; margin-bottom:8px;">
        <input class="broadcast-input" type="text" placeholder="Option 2" style="flex:1;" value="Non">
        <button class="btn btn-ghost btn-sm" onclick="removePollOption(this)">✖</button>
      </div>
    </div>
    <button class="btn btn-ghost btn-sm" onclick="addPollOption()" style="margin-top:5px;">+ Ajouter une option</button>
    <div style="margin-top:10px;">
      <label style="font-size:11px;">Nombre de choix possibles:</label>
      <select id="poll-selectable" class="broadcast-select" style="width:auto; display:inline-block; margin-left:10px;">
        <option value="1">1 choix</option>
        <option value="2">2 choix</option>
        <option value="3">3 choix</option>
        <option value="4">4 choix</option>
      </select>
    </div>
  </div>
  
  <!-- Zone contact -->
  <div id="message-contact-area" style="margin-top:15px; display:none;">
    <input class="broadcast-input" type="text" id="bc-contact-name" placeholder="Nom du contact" style="width:100%;">
    <input class="broadcast-input" type="text" id="bc-contact-number" placeholder="Numéro du contact" style="width:100%; margin-top:8px;">
  </div>
  
  <!-- Options -->
  <div class="broadcast-row" style="margin-top:15px;">
    <div style="flex:1;">
      <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
        <input type="checkbox" id="bc-simulate">
        <span style="font-size:12px;">🎮 Mode simulation (test sans envoyer)</span>
      </label>
    </div>
  </div>
  
  <div class="broadcast-actions" style="margin-top:20px;">
    <button class="btn-send" onclick="doAdvancedBroadcast()">
      <span>📤</span> Envoyer la diffusion
    </button>
    <span class="send-status" id="bc-status"></span>
  </div>
</div>

<!-- Aperçu des sélections -->
<div class="panel" style="margin-top:16px;">
  <div class="panel-header">
    <div class="panel-title">📋 Récapitulatif</div>
    <button class="btn btn-ghost btn-sm" onclick="refreshSelections()">↻ Rafraîchir</button>
  </div>
  <div id="selection-preview" style="padding:16px; font-size:12px; color:var(--text2);">
    Chargement...
  </div>
</div>

<div class="panel">
  <div class="panel-header">
    <div class="panel-title">📤 Derniers envois</div>
    <button class="btn btn-ghost btn-sm" onclick="goPage('queue')">Voir tout →</button>
  </div>
  <div class="tbl-wrap"></tr>
    <thead>汽<th>Type</th><th>Cible</th><th>Statut</th><th>Date</th></tr></thead>
    <tbody id="bc-queue"><tr class="empty-row"><td colspan="4"><span class="spin"></span></td></tr></tbody>
  </table></div>
</div>
`; }

function changeMessageType() {
    const type = document.getElementById('message-type-select')?.value;
    
    // Masquer toutes les zones
    document.getElementById('message-text-area').style.display = 'none';
    document.getElementById('message-media-area').style.display = 'none';
    document.getElementById('message-location-area').style.display = 'none';
    document.getElementById('message-poll-area').style.display = 'none';
    document.getElementById('message-contact-area').style.display = 'none';
    
    // Afficher la zone appropriée
    if (type === 'text' || type === 'link_preview') {
        document.getElementById('message-text-area').style.display = 'block';
    } else if (type === 'image' || type === 'video' || type === 'audio' || type === 'voice' || 
               type === 'document' || type === 'sticker' || type === 'gif' || type === 'story') {
        document.getElementById('message-text-area').style.display = 'block';
        document.getElementById('message-media-area').style.display = 'block';
    } else if (type === 'location') {
        document.getElementById('message-location-area').style.display = 'block';
    } else if (type === 'poll') {
        document.getElementById('message-poll-area').style.display = 'block';
    } else if (type === 'contact') {
        document.getElementById('message-contact-area').style.display = 'block';
    }
}

function addPollOption() {
    const container = document.getElementById('poll-options');
    const div = document.createElement('div');
    div.style.display = 'flex';
    div.style.gap = '8px';
    div.style.marginBottom = '8px';
    div.innerHTML = `
        <input class="broadcast-input" type="text" placeholder="Option ${container.children.length + 1}" style="flex:1;">
        <button class="btn btn-ghost btn-sm" onclick="removePollOption(this)">✖</button>
    `;
    container.appendChild(div);
}

function removePollOption(btn) {
    const container = document.getElementById('poll-options');
    if (container.children.length > 2) {
        btn.closest('div').remove();
    } else {
        toast('Un sondage doit avoir au moins 2 options', 'warn');
    }
}

let currentBroadcastTab = 'groups';

async function switchBroadcastTab(tab) {
    currentBroadcastTab = tab;
    document.querySelectorAll('#tab-groups, #tab-inbox, #tab-both').forEach(btn => btn.classList.remove('active'));
    document.getElementById(`tab-${tab}`).classList.add('active');
    
    document.getElementById('broadcast-groups').style.display = (tab === 'groups' || tab === 'both') ? 'block' : 'none';
    document.getElementById('broadcast-inbox').style.display = (tab === 'inbox' || tab === 'both') ? 'block' : 'none';
    
    await refreshSelections();
}

async function refreshSelections() {
    const groups = await api('list_groups');
    const groupSelect = document.getElementById('bc-group-select');
    if (groupSelect) {
        groupSelect.innerHTML = '<option value="all">📋 Tous les groupes actifs</option>' +
            groups.filter(g => g.actif == 1).map(g => `<option value="${esc(g.groupe_id)}">${esc(g.nom)} (${g.nb_membres} membres)</option>`).join('');
    }
    
    const members = await api('list_members', '');
    const uniquePhones = [...new Map(members.map(m => [m.phone_formatted, m])).values()];
    const contactSelect = document.getElementById('bc-contact-select');
    if (contactSelect) {
        contactSelect.innerHTML = '<option value="all">📋 Tous les contacts</option>' +
            uniquePhones.slice(0, 100).map(c => `<option value="${esc(c.phone_formatted)}">${esc(c.profile_name || c.phone_formatted)}</option>`).join('');
    }
    
    const stats = await api('stats');
    document.getElementById('stat-groups')?.setAttribute('data-value', stats.groups_actif);
    document.getElementById('stat-contacts')?.setAttribute('data-value', stats.members);
    
    updatePreview();
}

async function updatePreview() {
    const preview = document.getElementById('selection-preview');
    if (!preview) return;
    
    let selectedGroups = [];
    let selectedPhones = [];
    const messageType = document.getElementById('message-type-select')?.value || 'text';
    
    if (currentBroadcastTab === 'groups' || currentBroadcastTab === 'both') {
        const groupSelect = document.getElementById('bc-group-select');
        const selected = Array.from(groupSelect?.selectedOptions || []).map(opt => opt.value);
        const allGroups = await api('list_groups');
        if (selected.includes('all') || selected.length === 0) {
            selectedGroups = allGroups.filter(g => g.actif == 1);
        } else {
            selectedGroups = allGroups.filter(g => selected.includes(g.groupe_id));
        }
    }
    
    if (currentBroadcastTab === 'inbox' || currentBroadcastTab === 'both') {
        const customPhone = document.getElementById('bc-custom-phone')?.value.trim();
        if (customPhone) selectedPhones.push(customPhone);
        const contactSelect = document.getElementById('bc-contact-select');
        const selected = Array.from(contactSelect?.selectedOptions || []).map(opt => opt.value);
        if (selected.includes('all') && !customPhone) {
            const members = await api('list_members', '');
            selectedPhones = [...new Map(members.map(m => [m.phone_formatted, m])).keys()];
        } else if (!selected.includes('all')) {
            selectedPhones.push(...selected);
        }
    }
    
    preview.innerHTML = `
        <div style="display:flex; gap:20px; flex-wrap:wrap;">
            <div><strong>🎯 Type:</strong> ${messageType}</div>
            <div><strong>👥 Groupes:</strong> ${selectedGroups.length}</div>
            <div><strong>💬 Contacts:</strong> ${selectedPhones.length}</div>
            <div><strong>📊 Total destinataires:</strong> ${selectedGroups.length + selectedPhones.length}</div>
        </div>
        ${selectedGroups.length > 0 ? `<div style="margin-top:8px;"><small>Groupes: ${selectedGroups.slice(0,3).map(g => g.nom).join(', ')}${selectedGroups.length > 3 ? '...' : ''}</small></div>` : ''}
        ${selectedPhones.length > 0 ? `<div style="margin-top:4px;"><small>Contacts: ${selectedPhones.slice(0,3).join(', ')}${selectedPhones.length > 3 ? '...' : ''}</small></div>` : ''}
    `;
}

async function doAdvancedBroadcast() {
    const messageType = document.getElementById('message-type-select')?.value;
    const text = document.getElementById('bc-text')?.value?.trim() || '';
    const mediaUrl = document.getElementById('bc-media-url')?.value?.trim() || '';
    const simulate = document.getElementById('bc-simulate')?.checked || false;
    
    // Récupérer les cibles
    let targets = [];
    
    if (currentBroadcastTab === 'groups' || currentBroadcastTab === 'both') {
        const groupSelect = document.getElementById('bc-group-select');
        const selected = Array.from(groupSelect?.selectedOptions || []).map(opt => opt.value);
        const allGroups = await api('list_groups');
        let groups = [];
        if (selected.includes('all') || selected.length === 0) {
            groups = allGroups.filter(g => g.actif == 1);
        } else {
            groups = allGroups.filter(g => selected.includes(g.groupe_id));
        }
        targets.push(...groups.map(g => ({ type: 'group', id: g.groupe_id })));
    }
    
    if (currentBroadcastTab === 'inbox' || currentBroadcastTab === 'both') {
        const customPhone = document.getElementById('bc-custom-phone')?.value.trim();
        if (customPhone) targets.push({ type: 'contact', id: customPhone });
        const contactSelect = document.getElementById('bc-contact-select');
        const selected = Array.from(contactSelect?.selectedOptions || []).map(opt => opt.value);
        if (selected.includes('all') && !customPhone) {
            const members = await api('list_members', '');
            const uniquePhones = [...new Map(members.map(m => [m.phone_formatted, m])).keys()];
            targets.push(...uniquePhones.map(p => ({ type: 'contact', id: p })));
        } else if (!selected.includes('all')) {
            targets.push(...selected.map(p => ({ type: 'contact', id: p })));
        }
    }
    
    if (targets.length === 0) {
        toast('Aucun destinataire sélectionné', 'err');
        return;
    }
    
    const st = document.getElementById('bc-status');
    if (st) {
        st.style.display = 'inline';
        st.textContent = `Préparation de l'envoi à ${targets.length} destinataire(s)...`;
    }
    
    let sent = 0;
    let failed = 0;
    
    for (const target of targets) {
        let to = target.type === 'group' ? target.id : target.id.replace(/\D/g, '');
        let payload = {
            to: to,
            text: text,
            media_url: mediaUrl,
            message_type: messageType,
            simulate: simulate
        };
        
        // Ajouter les champs spécifiques
        if (messageType === 'location') {
            payload.latitude = document.getElementById('bc-lat')?.value;
            payload.longitude = document.getElementById('bc-lng')?.value;
            payload.location_name = document.getElementById('bc-location-name')?.value;
            payload.address = document.getElementById('bc-address')?.value;
        }
        
        if (messageType === 'poll') {
            const options = Array.from(document.querySelectorAll('#poll-options input')).map(i => i.value).filter(v => v.trim());
            payload.poll_options = options;
            payload.selectable_count = parseInt(document.getElementById('poll-selectable')?.value || 1);
        }
        
        if (messageType === 'contact') {
            payload.contact_name = document.getElementById('bc-contact-name')?.value;
            payload.contact_number = document.getElementById('bc-contact-number')?.value;
        }
        
        const d = await apiPost('send_message', payload);
        if (d.ok) sent++; else failed++;
        
        await new Promise(r => setTimeout(r, 500));
        
        if (st) st.textContent = `Progression: ${sent + failed}/${targets.length}...`;
    }
    
    if (st) st.style.display = 'none';
    
    if (simulate) {
        toast(`🔍 SIMULATION - ${sent} message(s) auraient été envoyés (${failed} erreur(s))`, 'warn');
    } else {
        toast(`✅ ${sent} message(s) envoyés avec succès (${failed} échec(s))`, sent > 0 ? 'ok' : 'err');
    }
    
    // Réinitialisation
    if (document.getElementById('bc-text')) document.getElementById('bc-text').value = '';
    if (document.getElementById('bc-media-url')) document.getElementById('bc-media-url').value = '';
    
    loadBcQueue();
    loadStats();
}







let bcMediaType = '';
function setMediaType(el, type) {
  document.querySelectorAll('.media-tab').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
  bcMediaType = type;
  const row = $('bc-media-row');
  if(row) row.style.display = type ? 'block' : 'none';
}




// Supprimez les fonctions doBroadcast() et setMediaType() existantes
// Et remplacez loadBcQueue par :

async function loadBcQueue() {
    const list = await api('list_queue');
    const tbody = document.getElementById('bc-queue');
    if (!tbody) return;
    const stBadge = {pending:'badge-amber', processing:'badge-blue', completed:'badge-green', failed:'badge-red', retry:'badge-purple'};
    if (!list.length) {
        tbody.innerHTML = '<tr class="empty-row"><td colspan="4">File vide</td></tr>';
        return;
    }
    tbody.innerHTML = list.slice(0,10).map(q => `
        <tr>
            <td><span class="badge badge-teal">${esc(q.target_type)}</span></td>
            <td class="mono">${esc(q.target_id || q.phone_number || '—')}</td>
            <td><span class="badge ${stBadge[q.status] || 'badge-gray'}">${esc(q.status)}</span></td>
            <td class="mono">${fmtDate(q.created_at)}</td>
        </tr>
    `).join('');
}

// Ajoutez cette fonction pour l'initialisation de la page broadcast
async function initBroadcast() {
    await refreshSelections();
    loadBcQueue();
    setInterval(() => {
        if (currentPage === 'broadcast') {
            loadBcQueue();
        }
    }, 30000);
}

// ─── GROUPS ──────────────────────────────────────────────
function buildGroups() { return `
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">👥 Groupes WhatsApp</div>
    <div class="panel-actions">
      <button class="btn btn-dark" onclick="syncGroups()">↻ Sync Whapi</button>
      <button class="btn btn-blue btn-sm" onclick="loadGroups()">↻ Actualiser</button>
    </div>
  </div>
  <div id="groups-grid" class="group-grid"><div style="color:var(--text3);font-size:13px;padding:8px;">Chargement…</div></div>
</div>`; }

async function syncGroups() {
  toast('Synchronisation groupes…','');
  const d = await api('sync_groups');
  if(d.ok){
    if(d.degraded) toast('⚠️ '+d.msg,'warn');
    else toast(`✅ ${d.msg} (${d.total} groupes)`,'ok');
    if(currentPage==='groups') loadGroups();
    loadStats();
  } else toast(d.msg||'Erreur','err');
}

async function syncMembers(gid) {
  toast('Synchronisation membres…','');
  const d = await api('sync_members', gid?`gid=${encodeURIComponent(gid)}`:'');
  if(d.ok){
    toast('✅ '+d.msg,'ok');
    if(currentPage==='members') loadMembersPage();
    if(currentPage==='groups')  loadGroups();
    loadStats();
  } else toast(d.msg||'Erreur','err');
}

async function loadGroups() {
  const groups = await api('list_groups');
  const c = $('groups-grid'); if(!c) return;
  if(!groups.length){c.innerHTML='<div style="padding:16px;color:var(--text3);font-size:13px;">Aucun groupe. Cliquez sur <b>Sync Whapi</b>.</div>';return;}
  c.innerHTML = groups.map(g=>`
  <div class="group-card">
    <div class="group-card-top">
      <div>
        <div class="group-name">${esc(g.nom||'Sans nom')}</div>
        <div class="group-id">${esc(g.groupe_id)}</div>
      </div>
      <span class="badge ${g.actif=='1'?'badge-green':'badge-gray'}">${g.actif=='1'?'Actif':'Inactif'}</span>
    </div>
    <span class="badge badge-blue">👤 ${g.nb_membres} membres</span>
    <div class="group-footer">
      <button class="btn btn-ghost btn-sm" onclick="syncMembers('${esc(g.groupe_id)}')">↻ Membres</button>
      <button class="btn btn-sm ${g.actif=='1'?'btn-red':'btn-green'}" onclick="toggleGroup('${esc(g.groupe_id)}')">
        ${g.actif=='1'?'🚫 Désactiver':'✅ Activer'}
      </button>
    </div>
  </div>`).join('');
}

async function toggleGroup(gid) {
  const d = await api('toggle_group',`gid=${encodeURIComponent(gid)}`);
  if(d.ok){toast('Groupe mis à jour','ok');loadGroups();loadStats();}
}

// ─── MEMBERS ─────────────────────────────────────────────
function buildMembers() { return `
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">👤 Membres</div>
    <div class="panel-actions">
      <input class="search-input" type="text" id="member-search" placeholder="Rechercher…" oninput="loadMembers()">
      <select class="search-input" id="member-group" style="width:auto;" onchange="loadMembers()">
        <option value="">Tous les groupes</option>
      </select>
      <button class="btn btn-dark btn-sm" onclick="syncMembers(null)">↻ Sync</button>
    </div>
  </div>
  <div class="tbl-wrap">
    <table><thead><tr><th>Téléphone</th><th>Nom</th><th>Groupe</th><th>Rôle</th><th>Violations</th><th>Actions</th></tr></thead>
    <tbody id="members-tbody"><tr class="empty-row"><td colspan="6"><span class="spin"></span></td></tr></tbody></table>
  </div>
</div>`; }

async function loadMembersPage() {
  const groups = await api('list_groups');
  const sel = $('member-group');
  if(sel) {
    const cur=sel.value;
    sel.innerHTML='<option value="">Tous les groupes</option>'+
      groups.map(g=>`<option value="${esc(g.groupe_id)}" ${g.groupe_id===cur?'selected':''}>${esc(g.nom||g.groupe_id)}</option>`).join('');
  }
  loadMembers();
}

async function loadMembers() {
  const search=$('member-search')?.value||'';
  const gid=$('member-group')?.value||'';
  const params=`search=${encodeURIComponent(search)}&gid=${encodeURIComponent(gid)}`;
  const members=await api('list_members',params);
  const tbody=$('members-tbody'); if(!tbody) return;
  if(!members.length){tbody.innerHTML='<tr class="empty-row"><td colspan="6">Aucun membre</td></tr>';return;}
  tbody.innerHTML=members.map(m=>{
    const vio=parseInt(m.violation_count)||0;
    return `<tr>
      <td class="mono">${esc(m.phone_formatted)}</td>
      <td>${esc(m.profile_name||'—')}</td>
      <td style="font-size:12px;color:var(--text2);">${esc(short(m.groupe_nom||m.groupe_id,28))}</td>
      <td>${m.is_admin=='1'?'<span class="badge badge-dark">⭐ Admin</span>':'<span class="badge badge-gray">Membre</span>'}</td>
      <td>${vio>0?`<span class="badge badge-red">${vio}</span>`:'<span class="badge badge-gray">0</span>'}</td>
      <td><div style="display:flex;gap:5px;">
        ${vio>0?`<button class="btn btn-ghost btn-sm" onclick="resetViolations('${esc(m.phone_formatted)}')">↺</button>`:''}
        <button class="btn btn-amber btn-sm" onclick="blacklistPhone('${esc(m.phone_formatted)}')">🚫</button>
      </div></td></tr>`;
  }).join('');
}

async function resetViolations(phone) {
  const d=await api('reset_violations',`phone=${encodeURIComponent(phone)}`);
  if(d.ok){toast('Violations réinitialisées','ok');loadMembers();}
}
async function blacklistPhone(phone) {
  if(!confirm(`Blacklister ${phone} ?`)) return;
  const d=await api('blacklist_add',`phone=${encodeURIComponent(phone)}&reason=Blacklist%20dashboard`);
  if(d.ok){toast(`🚫 ${phone} blacklisté`,'ok');loadMembers();loadStats();}
}

// ─── INBOX ───────────────────────────────────────────────
function buildInbox() { return `
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">📥 Inbox — Contacts</div>
    <button class="btn btn-ghost btn-sm" onclick="loadInbox()">↻ Actualiser</button>
  </div>
  <div class="tbl-wrap">
    <table><thead><tr><th>Téléphone</th><th>Nom</th><th>Dernier message</th><th>Date</th><th>Statut</th></tr></thead>
    <tbody id="inbox-tbody"><tr class="empty-row"><td colspan="5"><span class="spin"></span></td></tr></tbody></table>
  </div>
</div>`; }

async function loadInbox() {
  const list=await api('list_inbox');
  const tbody=$('inbox-tbody'); if(!tbody) return;
  if(!list.length){tbody.innerHTML='<tr class="empty-row"><td colspan="5">Aucun contact inbox</td></tr>';return;}
  tbody.innerHTML=list.map(c=>`<tr>
    <td class="mono">${esc(c.phone_number)}</td>
    <td>${esc(c.full_name||'—')}</td>
    <td>${esc(short(c.last_message))}</td>
    <td class="mono">${fmtDate(c.last_message_at)}</td>
    <td>${c.is_blacklisted=='1'?'<span class="badge badge-red">🚫 Bloqué</span>':'<span class="badge badge-green">✅ OK</span>'}</td>
  </tr>`).join('');
}

// ─── BLACKLIST ────────────────────────────────────────────
function buildBlacklist() { return `
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">🚫 Blacklist</div>
    <div class="panel-actions">
      <button class="btn btn-red" onclick="$('modal-blacklist').classList.add('open')">+ Ajouter</button>
      <button class="btn btn-ghost btn-sm" onclick="loadBlacklist()">↻</button>
    </div>
  </div>
  <div class="tbl-wrap">
    <table><thead><tr><th>Numéro</th><th>Raison</th><th>Date ajout</th><th>Action</th></tr></thead>
    <tbody id="blacklist-tbody"><tr class="empty-row"><td colspan="4"><span class="spin"></span></td></tr></tbody></table>
  </div>
</div>`; }

async function loadBlacklist() {
  const list=await api('list_blacklist');
  const tbody=$('blacklist-tbody'); if(!tbody) return;
  if(!list.length){tbody.innerHTML='<tr class="empty-row"><td colspan="4">Aucun numéro blacklisté 🎉</td></tr>';return;}
  tbody.innerHTML=list.map(b=>`<tr>
    <td class="mono">${esc(b.phone_number)}</td>
    <td>${esc(short(b.reason))}</td>
    <td class="mono">${fmtDate(b.created_at)}</td>
    <td><button class="btn btn-green btn-sm" onclick="removeBlacklist('${esc(b.phone_number)}')">↺ Débloquer</button></td>
  </tr>`).join('');
}

async function doBlacklistAdd() {
  const phone=($('bl-phone')?.value||'').replace(/\D/g,'');
  const reason=$('bl-reason')?.value||'Ajouté manuellement';
  if(!phone){toast('Numéro invalide','err');return;}
  const d=await api('blacklist_add',`phone=${encodeURIComponent(phone)}&reason=${encodeURIComponent(reason)}`);
  if(d.ok){toast(`🚫 ${phone} blacklisté`,'ok');closeModal('modal-blacklist');if(currentPage==='blacklist')loadBlacklist();loadStats();}
}
async function removeBlacklist(phone) {
  const d=await api('blacklist_remove',`phone=${encodeURIComponent(phone)}`);
  if(d.ok){toast('✅ Débloqué','ok');loadBlacklist();loadStats();}
}

// ─── QUEUE ────────────────────────────────────────────────
function buildQueue() { return `
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">📤 File d'envoi</div>
    <div class="panel-actions">
      <button class="btn btn-red btn-sm" onclick="if(confirm('Vider les complétés ?'))api('clear_queue','status=completed').then(d=>{if(d.ok){toast('Nettoyé','ok');loadQueue();}})">🗑 Vider complétés</button>
      <button class="btn btn-amber btn-sm" onclick="if(confirm('Vider les échecs ?'))api('clear_queue','status=failed').then(d=>{if(d.ok){toast('Nettoyé','ok');loadQueue();}})">🗑 Vider échecs</button>
      <button class="btn btn-ghost btn-sm" onclick="loadQueue()">↻</button>
    </div>
  </div>
  <div class="tbl-wrap">
    <table><thead><tr><th>Type</th><th>Cible</th><th>Contenu</th><th>Média</th><th>Statut</th><th>Tentatives</th><th>Créé</th></tr></thead>
    <tbody id="queue-tbody"><tr class="empty-row"><td colspan="7"><span class="spin"></span></td></tr></tbody></table>
  </div>
</div>`; }

async function loadQueue() {
  const list=await api('list_queue');
  const tbody=$('queue-tbody'); if(!tbody) return;
  const stBadge={pending:'badge-amber',processing:'badge-blue',completed:'badge-green',failed:'badge-red',retry:'badge-purple'};
  if(!list.length){tbody.innerHTML='<tr class="empty-row"><td colspan="7">File vide ✅</td></tr>';return;}
  tbody.innerHTML=list.map(q=>`<tr>
    <td><span class="badge badge-teal">${esc(q.target_type)}</span></td>
    <td class="mono">${esc(short(q.target_id||q.phone_number,20))}</td>
    <td>${esc(short(q.message_data))}</td>
    <td>${q.media_url?`<span class="badge badge-blue">${esc(q.message_type)}</span>`:'<span class="badge badge-gray">texte</span>'}</td>
    <td><span class="badge ${stBadge[q.status]||'badge-gray'}">${esc(q.status)}</span></td>
    <td class="mono">${q.retry_count}</td>
    <td class="mono">${fmtDate(q.created_at)}</td></tr>`).join('');
}

// ─── LOGS ─────────────────────────────────────────────────
function buildLogs() { return `
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">📋 Logs de messages</div>
    <button class="btn btn-ghost btn-sm" onclick="loadLogs()">↻</button>
  </div>
  <div class="tbl-wrap">
    <table><thead><tr><th>Téléphone</th><th>Type</th><th>Contenu</th><th>Statut</th><th>Erreur</th><th>Date</th></tr></thead>
    <tbody id="logs-tbody"><tr class="empty-row"><td colspan="6"><span class="spin"></span></td></tr></tbody></table>
  </div>
</div>`; }

async function loadLogs() {
  const list=await api('list_logs');
  const tbody=$('logs-tbody'); if(!tbody) return;
  const stBadge={sent:'badge-green',failed:'badge-red',received:'badge-blue',processing:'badge-amber'};
  if(!list.length){tbody.innerHTML='<tr class="empty-row"><td colspan="6">Aucun log</td></tr>';return;}
  tbody.innerHTML=list.map(l=>`<tr>
    <td class="mono">${esc(l.phone_number)}</td>
    <td><span class="badge badge-gray">${esc(l.message_type||'?')}</span></td>
    <td>${esc(short(l.message_content))}</td>
    <td><span class="badge ${stBadge[l.status]||'badge-gray'}">${esc(l.status)}</span></td>
    <td style="color:var(--red);font-size:12px;">${esc(short(l.error_message||'',30))}</td>
    <td class="mono">${fmtDate(l.sent_at||l.created_at)}</td></tr>`).join('');
}

// ─── SECURITY ─────────────────────────────────────────────
function buildSecurity() { return `
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">🛡 Logs de sécurité</div>
    <button class="btn btn-ghost btn-sm" onclick="loadSecurity()">↻</button>
  </div>
  <div class="tbl-wrap">
    <table><thead><tr><th>Expéditeur</th><th>Action</th><th>Raison</th><th>Groupe</th><th>Date</th></tr></thead>
    <tbody id="security-tbody"><tr class="empty-row"><td colspan="5"><span class="spin"></span></td></tr></tbody></table>
  </div>
</div>`; }

async function loadSecurity() {
  const list=await api('list_security');
  const tbody=$('security-tbody'); if(!tbody) return;
  if(!list.length){tbody.innerHTML='<tr class="empty-row"><td colspan="5">Aucune violation 🎉</td></tr>';return;}
  tbody.innerHTML=list.map(l=>`<tr>
    <td class="mono">${esc(l.sender)}</td>
    <td><span class="badge badge-red">${esc(l.action_type)}</span></td>
    <td>${esc(short(l.reason))}</td>
    <td class="mono" style="font-size:11px;">${esc(short(l.group_id,22))}</td>
    <td class="mono">${fmtDate(l.created_at)}</td></tr>`).join('');
}

// ─── SETTINGS ─────────────────────────────────────────────
function buildSettings() { return `
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">⚙️ Paramètres système</div>
    <button class="btn btn-ghost btn-sm" onclick="loadSettings()">↻</button>
  </div>
  <div id="settings-grid" class="settings-grid"><div style="padding:16px;color:var(--text3);">Chargement…</div></div>
</div>
<div class="panel" style="margin-top:4px;">
  <div class="panel-header"><div class="panel-title">🔍 Diagnostic système</div></div>
  <div style="padding:16px;">
    <button class="btn btn-dark" onclick="runDiag()">▶ Lancer le diagnostic</button>
    <pre id="diag-out" style="margin-top:14px;background:var(--wa-sidebar);border-radius:8px;padding:14px;font-family:var(--mono);font-size:12px;color:var(--text);white-space:pre-wrap;display:none;"></pre>
  </div>
</div>`; }

async function loadSettings() {
  const list=await api('list_settings');
  const grid=$('settings-grid'); if(!grid) return;
  if(!list.length){grid.innerHTML='<div style="padding:16px;color:var(--text3);">Aucun paramètre</div>';return;}
  grid.innerHTML=list.map(s=>`
  <div class="setting-item">
    <div class="setting-key">${esc(s.setting_key)}</div>
    <div style="display:flex;gap:6px;">
      <input class="setting-input" type="text" value="${esc(s.setting_value||'')}" id="set-${esc(s.setting_key)}" onkeydown="if(event.key==='Enter')saveSetting('${esc(s.setting_key)}')">
      <button class="btn btn-green btn-sm" onclick="saveSetting('${esc(s.setting_key)}')">💾</button>
    </div>
  </div>`).join('');
}

async function saveSetting(key) {
  const val=$(`set-${key}`)?.value||'';
  const d=await api('save_setting',`key=${encodeURIComponent(key)}&val=${encodeURIComponent(val)}`);
  if(d.ok) toast(`💾 ${key} sauvegardé`,'ok');
}

async function runDiag() {
  const out=$('diag-out'); if(!out) return;
  out.style.display='block'; out.textContent='Diagnostic en cours…';
  const d=await api('diag');
  out.textContent=JSON.stringify(d.diag||d,null,2);
}

// ─── Keyboard + init ──────────────────────────────────────
document.addEventListener('keydown',e=>{
  if(e.key==='Escape') document.querySelectorAll('.modal-bg.open').forEach(m=>m.classList.remove('open'));
});

// Init
goPage('overview');
setInterval(loadStats,60000);
</script>

<?php endif; ?>
</body>
</html>