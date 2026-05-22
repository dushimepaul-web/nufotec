<?php
// ==============================================
// CONFIGURATION BASE DE DONNÉES
// ==============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'nufotec_nufotec');   
define('DB_PASS', 'root123');
define('DB_NAME', 'nufotec_db');

// ==============================================
// CONFIGURATION WHAPI
// ==============================================
define('API_TOKEN',      'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw');
define('API_URL',        'https://gate.whapi.cloud/');
define('MASTER_GROUP',   '254743031262-1528423768@g.us');
define('WEBHOOK_TOKEN',  ''); // laisser vide si pas de token

// ==============================================
// LOGS
// ==============================================
define('LOG_FILE', __DIR__ . '/webhook.log');

function wlog($level, $msg) {
    $line = date('Y-m-d H:i:s') . " [$level] $msg" . PHP_EOL;
    file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

// ==============================================
// CONNEXION BASE DE DONNÉES
// ==============================================
function db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (Exception $e) {
            wlog('ERROR', 'DB connection failed: ' . $e->getMessage());
            die(json_encode(['error' => 'DB error']));
        }
    }
    return $pdo;
}

// ==============================================
// RÉPONDRE IMMÉDIATEMENT (éviter ETIMEDOUT)
// ==============================================
function send_immediate_response() {
    if (headers_sent()) return;
    $json = json_encode(['status' => 'received']);
    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($json));
    header('Connection: close');
    header('Cache-Control: no-cache');
    echo $json;
    if (ob_get_level() > 0) ob_end_flush();
    flush();
    if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
}

// ==============================================
// APPEL API WHAPI
// ==============================================
function whapi_request($endpoint, $method = 'GET', $data = null) {
    $ch = curl_init(API_URL . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . API_TOKEN,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err    = curl_error($ch);
    curl_close($ch);
    if ($err) {
        wlog('ERROR', "whapi_request $endpoint curl error: $err");
        return null;
    }
    if ($status >= 400) {
        wlog('ERROR', "whapi_request $endpoint HTTP $status: $body");
        return null;
    }
    return json_decode($body, true);
}

function send_text($to, $text) {
    return whapi_request('messages/text', 'POST', [
        'to'   => format_phone($to),
        'body' => sanitize_message($text),
    ]);
}

function delete_message($message_id) {
    if (empty($message_id)) return;
    whapi_request("messages/$message_id", 'DELETE');
}

// ==============================================
// HELPERS TÉLÉPHONE & TEXTE
// ==============================================
function format_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone, 0, 1) === '0') $phone = '62' . substr($phone, 1);
    if (substr($phone, 0, 2) !== '62') $phone = '62' . $phone;
    return $phone;
}

function sanitize_message($msg) {
    if (empty($msg)) return '';
    $msg = strip_tags($msg);
    return trim($msg);
}

function contains_link($msg) {
    return (bool) preg_match('/(https?:\/\/[^\s]+|www\.[^\s]+)/i', $msg);
}

function contains_mention($msg) {
    return (bool) preg_match('/@[a-zA-Z0-9_+]+/', $msg);
}

function contains_phone($msg) {
    return (bool) preg_match('/[0-9]{10,}/', $msg);
}

// ==============================================
// BASE DE DONNÉES HELPERS
// ==============================================
function is_blacklisted($phone) {
    try {
        $st = db()->prepare('SELECT id FROM whatsapp_blacklist WHERE phone_number = ? LIMIT 1');
        $st->execute([$phone]);
        return $st->fetch() !== false;
    } catch (Exception $e) {
        wlog('ERROR', 'is_blacklisted: ' . $e->getMessage());
        return false;
    }
}

function is_group_admin($group_id, $phone) {
    try {
        $st = db()->prepare('SELECT id FROM whatsapp_participants WHERE groupe_id = ? AND phone_formatted = ? AND is_admin = 1 LIMIT 1');
        $st->execute([$group_id, $phone]);
        return $st->fetch() !== false;
    } catch (Exception $e) {
        wlog('ERROR', 'is_group_admin: ' . $e->getMessage());
        return false;
    }
}

function get_admin_numbers() {
    try {
        $st = db()->prepare("SELECT setting_value FROM whatsapp_settings WHERE setting_key = 'admin_numbers' LIMIT 1");
        $st->execute();
        $row = $st->fetch();
        return $row ? (json_decode($row->setting_value, true) ?: []) : [];
    } catch (Exception $e) {
        wlog('ERROR', 'get_admin_numbers: ' . $e->getMessage());
        return [];
    }
}

function get_template($name) {
    try {
        $st = db()->prepare('SELECT content FROM whatsapp_templates WHERE name = ? LIMIT 1');
        $st->execute([$name]);
        $row = $st->fetch();
        return $row ? $row->content : null;
    } catch (Exception $e) {
        wlog('ERROR', 'get_template: ' . $e->getMessage());
        return null;
    }
}

function log_security($group_id, $sender, $action_type, $reason) {
    try {
        $st = db()->prepare('INSERT INTO whatsapp_security_logs (group_id, sender, action_type, reason, created_at) VALUES (?,?,?,?,NOW())');
        $st->execute([$group_id, $sender, $action_type, $reason]);
    } catch (Exception $e) {
        wlog('ERROR', 'log_security: ' . $e->getMessage());
    }
}

function log_whatsapp_msg($phone, $content, $type, $status, $error = null) {
    try {
        $st = db()->prepare('INSERT INTO whatsapp_logs (phone_number, message_content, message_type, status, error_message, sent_at, created_at) VALUES (?,?,?,?,?,NOW(),NOW())');
        $st->execute([format_phone($phone), $content, $type, $status, $error]);
    } catch (Exception $e) {
        wlog('ERROR', 'log_whatsapp_msg: ' . $e->getMessage());
    }
}

function upsert_group($groupe_id, $nom = null) {
    try {
        $st = db()->prepare('SELECT id FROM groupes_whatsapp WHERE groupe_id = ? LIMIT 1');
        $st->execute([$groupe_id]);
        $exists = $st->fetch();
        if ($exists) {
            $st = db()->prepare('UPDATE groupes_whatsapp SET updated_at = NOW() ' . ($nom ? ', nom = ?' : '') . ' WHERE groupe_id = ?');
            $params = $nom ? [$nom, $groupe_id] : [$groupe_id];
            $st->execute($params);
        } else {
            $st = db()->prepare('INSERT INTO groupes_whatsapp (groupe_id, nom, actif, created_at, updated_at) VALUES (?,?,1,NOW(),NOW())');
            $st->execute([$groupe_id, $nom]);
        }
    } catch (Exception $e) {
        wlog('ERROR', "upsert_group($groupe_id): " . $e->getMessage());
    }
}

function upsert_participant($groupe_id, $phone, $name = null) {
    try {
        $pf = format_phone($phone);
        $st = db()->prepare('SELECT id FROM whatsapp_participants WHERE groupe_id = ? AND phone_formatted = ? LIMIT 1');
        $st->execute([$groupe_id, $pf]);
        $exists = $st->fetch();
        if ($exists) {
            $st = db()->prepare('UPDATE whatsapp_participants SET synced_at = NOW(), updated_at = NOW() ' . ($name ? ', profile_name = ?' : '') . ' WHERE groupe_id = ? AND phone_formatted = ?');
            $params = $name ? [$name, $groupe_id, $pf] : [$groupe_id, $pf];
            $st->execute($params);
        } else {
            $st = db()->prepare('INSERT INTO whatsapp_participants (groupe_id, phone, phone_formatted, is_admin, violation_count, profile_name, synced_at, created_at, updated_at) VALUES (?,?,?,0,0,?,NOW(),NOW(),NOW())');
            $st->execute([$groupe_id, $phone, $pf, $name]);
        }
    } catch (Exception $e) {
        wlog('ERROR', "upsert_participant($phone): " . $e->getMessage());
    }
}

function increment_violation($phone_formatted) {
    try {
        $st = db()->prepare('SELECT id, violation_count FROM whatsapp_participants WHERE phone_formatted = ? LIMIT 1');
        $st->execute([$phone_formatted]);
        $p = $st->fetch();
        if (!$p) return;

        $new_count = (int)$p->violation_count + 1;
        $st = db()->prepare('UPDATE whatsapp_participants SET violation_count = ? WHERE phone_formatted = ?');
        $st->execute([$new_count, $phone_formatted]);

        if ($new_count >= 5) {
            $st = db()->prepare('SELECT id FROM whatsapp_blacklist WHERE phone_number = ? LIMIT 1');
            $st->execute([$phone_formatted]);
            if (!$st->fetch()) {
                $st = db()->prepare('INSERT INTO whatsapp_blacklist (phone_number, reason, created_at) VALUES (?,?,NOW())');
                $st->execute([$phone_formatted, "Auto-blacklist: $new_count violations"]);
                wlog('INFO', "$phone_formatted auto-blacklisté après $new_count violations");
            }
        }
    } catch (Exception $e) {
        wlog('ERROR', "increment_violation($phone_formatted): " . $e->getMessage());
    }
}

function enqueue($target_type, $target_id, $phone, $msg_type, $msg_data, $media_url = null) {
    try {
        $st = db()->prepare('INSERT INTO whatsapp_queue (target_type, target_id, phone_number, message_type, message_data, media_url, status, priority, scheduled_at, created_at) VALUES (?,?,?,?,?,?,\'pending\',1,NOW(),NOW())');
        $st->execute([$target_type, $target_id, $phone ? format_phone($phone) : null, $msg_type, $msg_data, $media_url]);
        return db()->lastInsertId();
    } catch (Exception $e) {
        wlog('ERROR', "enqueue: " . $e->getMessage());
        return false;
    }
}

function distribute($msg_data, $sender, $target_type) {
    $distributed = 0;

    // Vers les groupes actifs
    if (in_array($target_type, ['group', 'both'])) {
        try {
            $groups = db()->query('SELECT groupe_id FROM groupes_whatsapp WHERE actif = 1')->fetchAll();
            foreach ($groups as $g) {
                enqueue('group', $g->groupe_id, null, $msg_data['type'], $msg_data['text'], $msg_data['media_url']);
                $distributed++;
            }
        } catch (Exception $e) {
            wlog('ERROR', 'distribute groups: ' . $e->getMessage());
        }
    }

    // Vers les inboxes non blacklistées
    if (in_array($target_type, ['inbox', 'both'])) {
        try {
            $inboxes = db()->query('SELECT phone_number FROM whatsapp_inbox WHERE is_blacklisted = 0')->fetchAll();
            shuffle($inboxes);
            foreach ($inboxes as $inbox) {
                enqueue('inbox', null, $inbox->phone_number, $msg_data['type'], $msg_data['text'], $msg_data['media_url']);
                $distributed++;
            }
        } catch (Exception $e) {
            wlog('ERROR', 'distribute inbox: ' . $e->getMessage());
        }
    }

    return $distributed;
}

// ==============================================
// TRAITEMENT PRINCIPAL
// ==============================================
function process_message($payload) {

    // Ignorer les messages du bot
    if (!empty($payload['fromMe']) || !empty($payload['isFromMe'])) {
        wlog('DEBUG', 'Message du bot ignoré');
        return;
    }

    $message_type = $payload['type'] ?? 'unknown';
    $message_id   = $payload['id']   ?? null;

    // Expéditeur
    $sender = null;
    if (!empty($payload['from']['phone']))                         $sender = $payload['from']['phone'];
    elseif (!empty($payload['from']) && is_string($payload['from'])) $sender = $payload['from'];
    elseif (!empty($payload['author']))                            $sender = $payload['author'];

    if (empty($sender)) { wlog('INFO', 'Sender manquant, ignoré'); return; }

    // Chat
    $chat_id  = $payload['chat']['id'] ?? $payload['chatId'] ?? null;
    $is_group = !empty($chat_id) && str_contains($chat_id, '@g.us');

    // Texte
    $message_text = '';
    if ($message_type === 'text') {
        $message_text = $payload['text']['body'] ?? (is_string($payload['text'] ?? null) ? $payload['text'] : '');
    } elseif (!empty($payload['text'])) {
        $message_text = is_string($payload['text']) ? $payload['text'] : '';
    }

    // Médias
    $media_types = ['image', 'video', 'audio', 'document', 'sticker'];
    $has_media   = in_array($message_type, $media_types, true);
    $media_url   = null;

    if ($has_media && !empty($payload[$message_type])) {
        $media_url    = $payload[$message_type]['url'] ?? $payload[$message_type]['link'] ?? null;
        $media_caption = $payload[$message_type]['caption'] ?? null;
        if ($media_caption) $message_text = $media_caption;
    }

    $message_text = sanitize_message($message_text);
    $sender_clean = format_phone($sender);

    // Blacklist
    if (is_blacklisted($sender_clean)) {
        wlog('INFO', "$sender_clean est blacklisté, ignoré");
        return;
    }

    // Admin ?
    $admin_numbers = get_admin_numbers();
    $is_admin      = in_array($sender_clean, $admin_numbers, true);
    if (!$is_admin && $is_group && $chat_id) {
        $is_admin = is_group_admin($chat_id, $sender_clean);
    }

    // Sync groupe/participant
    if ($is_group && $chat_id) {
        upsert_group($chat_id, $payload['chat']['name'] ?? $payload['chatName'] ?? null);
        upsert_participant($chat_id, $sender, $payload['pushName'] ?? $payload['notifyName'] ?? null);
    }

    // Règles sécurité non-admins
    if (!$is_admin) {
        $violation = '';
        if ($has_media)                    $violation = 'media_non_autorise';
        elseif (contains_link($message_text))    $violation = 'lien_non_autorise';
        elseif (contains_mention($message_text)) $violation = 'mention_non_autorisee';
        elseif (contains_phone($message_text))   $violation = 'phone_non_autorise';

        if ($violation) {
            if ($message_id) delete_message($message_id);
            log_security($chat_id, $sender_clean, $violation, 'Message supprimé - non-admin');
            increment_violation($sender_clean);
            wlog('INFO', "Violation $violation de $sender_clean");
        } else {
            wlog('DEBUG', "Message membre OK de $sender_clean");
        }
        return;
    }

    // Admin : broadcast uniquement depuis le groupe maître
    if ($chat_id !== MASTER_GROUP) {
        wlog('DEBUG', "Admin dans groupe cible, pas de broadcast");
        return;
    }

    // Commandes #groupe / #inbox / #template
    $target_type = 'both';
    $clean_text  = $message_text;

    if (str_starts_with($clean_text, '#groupe')) {
        $target_type = 'group';
        $clean_text  = trim(substr($clean_text, 7));
    } elseif (str_starts_with($clean_text, '#inbox')) {
        $target_type = 'inbox';
        $clean_text  = trim(substr($clean_text, 6));
    } elseif (str_starts_with($clean_text, '#template:')) {
        preg_match('/#template:([a-zA-Z0-9_]+)/', $clean_text, $m);
        if (!empty($m[1])) {
            $tpl = get_template($m[1]);
            $clean_text = $tpl ?? preg_replace('/#template:[a-zA-Z0-9_]+\s*/', '', $clean_text);
        }
    }

    $clean_text = sanitize_message($clean_text);
    log_whatsapp_msg($sender, $clean_text, $message_type, 'received');

    $count = distribute(
        ['type' => $message_type, 'text' => $clean_text, 'media_url' => $media_url],
        $sender,
        $target_type
    );

    wlog('INFO', "Broadcast admin $sender_clean → $target_type — $count messages en queue");
}

// ==============================================
// POINT D'ENTRÉE
// ==============================================

// Lire le payload AVANT de répondre
$raw_input = file_get_contents('php://input');

// Répondre immédiatement à Whapi
send_immediate_response();

// Valider token (optionnel)
if (!empty(WEBHOOK_TOKEN)) {
    $token = $_GET['token'] ?? $_SERVER['HTTP_X_WEBHOOK_TOKEN'] ?? '';
    if ($token !== WEBHOOK_TOKEN) {
        wlog('ERROR', 'Token webhook invalide: ' . $token);
        exit;
    }
}

// Valider JSON
$payload = json_decode($raw_input, true);
if (empty($payload) || !is_array($payload)) {
    wlog('ERROR', 'Payload invalide: ' . substr($raw_input, 0, 300));
    exit;
}

wlog('INFO', 'Webhook reçu: ' . substr($raw_input, 0, 500));

// Traiter
process_message($payload); comment fonctionne ce programme etre pres donn moi les bsse de donne corresponddenta