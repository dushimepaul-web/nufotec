<?php
/**
 * ============================================================
 *  NUFOTEC — WhatsApp Processor  (process.php)
 *  Appelé en arrière-plan par webhook.php
 *  Contient tout le traitement lourd : DB, API Whapi, queue
 *  Compatible 100% avec la base de données existante
 * ============================================================
 */
declare(strict_types=1);

ignore_user_abort(true);
set_time_limit(120);  // 2 min max pour le traitement


// ============================================================
//  SÉCURITÉ — accès réservé au webhook.php interne
// ============================================================
define('PROCESSOR_SECRET', 'CHANGE_MOI_secret_interne_xK9z');  // identique à webhook.php

$secret = $_SERVER['HTTP_X_INTERNAL_SECRET'] ?? '';
if (!hash_equals(PROCESSOR_SECRET, $secret)) {
    http_response_code(403);
    exit('Accès interdit');
}


// ============================================================
//  CONFIGURATION
// ============================================================
define('DB_HOST',    'localhost');
define('DB_USER',    'nufotec_nufotec');     // ← changer
define('DB_PASS',    '6886Paul@');              // ← changer
define('DB_NAME',    'nufotec_db');

define('API_TOKEN',    'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw');
define('API_URL',      'https://gate.whapi.cloud/');
define('MASTER_GROUP', '254743031262-1528423768@g.us');

define('VIOLATION_LIMIT', 5);
define('CURL_TIMEOUT',    5);   // réduit à 5 s (était 8)
define('LOG_FILE',        __DIR__ . '/process.log');
define('LOG_MAX_BYTES',   5 * 1024 * 1024);


// ============================================================
//  LIRE LE PAYLOAD
// ============================================================
$raw = (string) file_get_contents('php://input');

if (empty($raw)) exit;
$payload = json_decode($raw, true);
if (!is_array($payload) || empty($payload)) {
    wlog('ERROR', 'JSON invalide: ' . substr($raw, 0, 200));
    exit;
}

wlog('INFO', 'Traitement · type=' . ($payload['type'] ?? $payload['event']['type'] ?? '?'));

// Dispatcher selon l'événement
$event = $payload['event']['type'] ?? $payload['type'] ?? 'message';

if (str_contains($event, 'message')) {
    handle_message($payload);
} elseif (str_contains($event, 'group')) {
    handle_group_sync($payload);
} else {
    wlog('DEBUG', "Événement ignoré: $event");
}

http_response_code(200);
exit;


// ============================================================
// ███  TRAITEMENT MESSAGES
// ============================================================
function handle_message(array $p): void
{
    // ── Ignorer les messages du bot ─────────────────────────
    if (!empty($p['fromMe']) || !empty($p['isFromMe'])) return;

    // ── Extraire les champs essentiels ──────────────────────
    $msg_id   = $p['id'] ?? '';
    $msg_type = $p['type'] ?? 'unknown';

    // Expéditeur (plusieurs formats Whapi possibles)
    $sender = '';
    if (!empty($p['from'])) {
        $sender = is_array($p['from']) ? ($p['from']['phone'] ?? '') : $p['from'];
    }
    if (empty($sender)) $sender = $p['author'] ?? '';
    if (empty($sender)) { wlog('DEBUG', 'Sender manquant'); return; }

    $sender_clean = clean_phone($sender);

    // Chat
    $chat_id  = $p['chat']['id'] ?? $p['chatId'] ?? '';
    $is_group = str_contains($chat_id, '@g.us');

    // Médias
    $media_types = ['image', 'video', 'audio', 'document', 'sticker'];
    $has_media   = in_array($msg_type, $media_types, true);
    $media_url   = null;
    $msg_text    = '';

    if ($msg_type === 'text') {
        $msg_text = is_array($p['text'] ?? null)
            ? ($p['text']['body'] ?? '')
            : ($p['text'] ?? '');
    }
    foreach ($media_types as $mt) {
        if (!empty($p[$mt])) {
            $media_url = $p[$mt]['url'] ?? $p[$mt]['link'] ?? null;
            $cap = $p[$mt]['caption'] ?? '';
            if ($cap !== '') $msg_text = $cap;
            break;
        }
    }
    $msg_text = clean_text($msg_text);

    // ── Blacklist ────────────────────────────────────────────
    if (is_blacklisted($sender_clean)) {
        wlog('INFO', "Blacklisté $sender_clean — message supprimé");
        if ($msg_id) delete_message($msg_id);
        return;
    }

    // ── Détecter si admin ────────────────────────────────────
    $is_admin = is_admin($sender_clean, $chat_id, $is_group);

    // ── Synchro groupe & participant ─────────────────────────
    if ($is_group && $chat_id) {
        upsert_group($chat_id, $p['chat']['name'] ?? $p['chatName'] ?? null);
        upsert_participant(
            $chat_id, $sender, $sender_clean,
            $p['pushName'] ?? $p['notifyName'] ?? null,
            $is_admin
        );
    }

    // ── Règles sécurité pour non-admins ──────────────────────
    if (!$is_admin) {
        $violation = detect_violation($msg_text, $has_media);
        if ($violation) {
            if ($msg_id) delete_message($msg_id);
            log_security($chat_id, $sender_clean, $violation, 'Message supprimé');
            $count = increment_violation($sender_clean);
            warn_member($chat_id, $sender_clean, $violation, $count);
            wlog('INFO', "Violation [$violation] · $sender_clean · count=$count");
        }
        return;
    }

    // ── Admin : broadcast depuis le groupe maître uniquement ─
    if ($chat_id !== MASTER_GROUP) {
        wlog('DEBUG', 'Admin hors groupe maître — ignoré');
        return;
    }

    // Logger la réception
    db_run(
        'INSERT INTO whatsapp_logs
         (phone_number, message_content, message_type, status, sent_at, created_at)
         VALUES (?,?,?,\'received\',NOW(),NOW())',
        [$sender_clean, substr($msg_text, 0, 1000), $msg_type]
    );

    // Parser la commande et diffuser
    [$target_type, $final_text] = parse_command($msg_text);

    $count = broadcast(
        ['type' => $msg_type, 'text' => $final_text, 'media_url' => $media_url],
        $target_type
    );

    wlog('INFO', "Broadcast $target_type par $sender_clean → $count en queue");

    // Confirmation dans le groupe maître
    send_text(MASTER_GROUP, "✅ Diffusion *$target_type* lancée\n📨 $count messages en queue");
}


// ============================================================
// ███  SYNC GROUPE (add/remove/promote/demote)
// ============================================================
function handle_group_sync(array $p): void
{
    $group_id = $p['chat']['id'] ?? $p['chatId'] ?? '';
    if (empty($group_id)) return;

    $action  = $p['action'] ?? $p['event']['action'] ?? '';
    $members = $p['participants'] ?? $p['members'] ?? [];

    foreach ($members as $member) {
        $phone = is_array($member)
            ? ($member['phone'] ?? $member['id'] ?? '')
            : $member;
        $phone = clean_phone((string)$phone);
        if (empty($phone)) continue;

        switch ($action) {
            case 'add':
            case 'join':
                db_run(
                    'INSERT INTO whatsapp_participants
                     (groupe_id,phone,phone_formatted,is_admin,violation_count,synced_at,created_at,updated_at)
                     VALUES (?,?,?,0,0,NOW(),NOW(),NOW())
                     ON DUPLICATE KEY UPDATE synced_at=NOW(), updated_at=NOW()',
                    [$group_id, $phone, $phone]
                );
                break;
            case 'remove':
            case 'leave':
                db_run(
                    'DELETE FROM whatsapp_participants
                     WHERE groupe_id=? AND phone_formatted=?',
                    [$group_id, $phone]
                );
                break;
            case 'promote':
                db_run(
                    'UPDATE whatsapp_participants SET is_admin=1, updated_at=NOW()
                     WHERE groupe_id=? AND phone_formatted=?',
                    [$group_id, $phone]
                );
                break;
            case 'demote':
                db_run(
                    'UPDATE whatsapp_participants SET is_admin=0, updated_at=NOW()
                     WHERE groupe_id=? AND phone_formatted=?',
                    [$group_id, $phone]
                );
                break;
        }
    }
    wlog('INFO', "GroupSync [$action] $group_id · " . count($members) . ' membres');
}


// ============================================================
// ███  SÉCURITÉ — DÉTECTION VIOLATIONS
// ============================================================
function detect_violation(string $text, bool $has_media): ?string
{
    if ($has_media) return 'media_non_autorise';
    if (empty($text)) return null;

    // Liens (http, www, domaines raccourcis, invitations WhatsApp)
    if (preg_match('#(https?://|www\.|t\.me/|wa\.me/|chat\.whatsapp\.com/|bit\.ly/|tinyurl\.com/)[^\s]*#i', $text))
        return 'lien_non_autorise';

    // Mentions WhatsApp (@numéro)
    if (preg_match('/@[0-9]{5,}/', $text))
        return 'mention_non_autorisee';

    // Numéros de téléphone bruts (≥ 8 chiffres consécutifs)
    if (preg_match('/(?<!\@)\b[0-9]{8,}\b/', $text))
        return 'numero_non_autorise';

    return null;
}

function warn_member(string $group_id, string $phone, string $violation, int $count): void
{
    $remaining = VIOLATION_LIMIT - $count;
    $labels = [
        'media_non_autorise'    => 'l\'envoi de médias',
        'lien_non_autorise'     => 'le partage de liens',
        'mention_non_autorisee' => 'les mentions',
        'numero_non_autorise'   => 'le partage de numéros',
    ];
    $label = $labels[$violation] ?? 'ce type de contenu';

    if ($remaining <= 0) {
        $msg = "⛔ @$phone Vous avez été exclu pour infractions répétées.";
    } elseif ($remaining === 1) {
        $msg = "⚠️ @$phone $label n'est pas autorisé. *Dernière infraction avant exclusion.*";
    } else {
        $msg = "⚠️ @$phone $label n'est pas autorisé dans ce groupe. ($count/" . VIOLATION_LIMIT . " infractions)";
    }

    send_text($group_id, $msg);
}


// ============================================================
// ███  BROADCAST — DIFFUSION EN QUEUE
// ============================================================

/**
 * Commandes supportées (depuis le groupe maître) :
 *   #groupe Message     → groupes seulement
 *   #inbox  Message     → contacts privés seulement
 *   #tous   Message     → les deux (défaut sans commande)
 *   #template:nom       → utilise un template de la BDD
 */
function parse_command(string $text): array
{
    $target = 'both';

    foreach ([
        '#groupe' => 'group',
        '#inbox'  => 'inbox',
        '#tous'   => 'both',
        '#all'    => 'both',
    ] as $cmd => $type) {
        if (stripos($text, $cmd) === 0) {
            $target = $type;
            $text   = trim(substr($text, strlen($cmd)));
            break;
        }
    }

    // Template : #template:nom_template
    if (preg_match('/#template:([a-zA-Z0-9_]+)/i', $text, $m)) {
        $row = db_one(
            'SELECT content FROM whatsapp_templates WHERE name=? LIMIT 1',
            [$m[1]]
        );
        if ($row) {
            $text = preg_replace('/#template:[a-zA-Z0-9_]+\s*/i', '', $text);
            $text = $row->content . ($text ? "\n" . $text : '');
        }
    }

    return [$target, clean_text($text)];
}

function broadcast(array $msg, string $target_type): int
{
    $count = 0;

    if (in_array($target_type, ['group', 'both'], true)) {
        $groups = db_all('SELECT groupe_id FROM groupes_whatsapp WHERE actif=1');
        foreach ($groups as $g) {
            enqueue('group', $g->groupe_id, null, $msg);
            $count++;
        }
    }

    if (in_array($target_type, ['inbox', 'both'], true)) {
        $contacts = db_all('SELECT phone_number FROM whatsapp_inbox WHERE is_blacklisted=0');
        shuffle($contacts);
        foreach ($contacts as $c) {
            enqueue('inbox', null, $c->phone_number, $msg);
            $count++;
        }
    }

    return $count;
}

function enqueue(string $target_type, ?string $target_id, ?string $phone, array $msg): void
{
    db_run(
        'INSERT INTO whatsapp_queue
         (target_type, target_id, phone_number,
          message_type, message_data, media_url,
          status, priority, scheduled_at, created_at)
         VALUES (?,?,?,?,?,?,\'pending\',1,NOW(),NOW())',
        [
            $target_type,
            $target_id,
            $phone ? clean_phone($phone) : null,
            $msg['type'],
            $msg['text'],
            $msg['media_url'],
        ]
    );
}


// ============================================================
// ███  API WHAPI
// ============================================================
function send_text(string $to, string $body): ?array
{
    if (empty($body)) return null;
    return whapi_request('messages/text', 'POST', [
        'to'   => format_wa($to),
        'body' => clean_text($body),
    ]);
}

function delete_message(string $message_id): void
{
    if (empty($message_id)) return;

    $r = whapi_request("messages/$message_id", 'DELETE');
    if ($r !== null) {
        wlog('INFO', "Supprimé: $message_id");
        return;
    }

    // 2e essai après 300 ms (réduit de 800 ms)
    usleep(300_000);
    $r2 = whapi_request("messages/$message_id", 'DELETE');
    wlog($r2 !== null ? 'INFO' : 'WARN', "Suppression 2e essai [$message_id]: " . ($r2 !== null ? 'OK' : 'échec'));
}

function whapi_request(string $endpoint, string $method = 'GET', ?array $data = null): ?array
{
    $ch = curl_init(API_URL . ltrim($endpoint, '/'));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => CURL_TIMEOUT,   // 5 s
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_ENCODING       => 'gzip',
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . API_TOKEN,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err    = curl_error($ch);
    curl_close($ch);

    if ($err)       { wlog('ERROR', "cURL $method $endpoint: $err"); return null; }
    if ($status >= 400) { wlog('WARN', "HTTP $status $method $endpoint: " . substr((string)$body, 0, 150)); return null; }
    return json_decode((string)$body, true) ?: [];
}

/** Formater numéro pour Whapi */
function format_wa(string $to): string
{
    if (str_contains($to, '@')) return $to;
    return clean_phone($to) . '@c.us';
}


// ============================================================
// ███  BASE DE DONNÉES
// ============================================================
function db(): PDO
{
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
        } catch (PDOException $e) {
            wlog('ERROR', 'DB: ' . $e->getMessage());
            exit;
        }
    }
    return $pdo;
}

function db_run(string $sql, array $p = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($p);
    return $st;
}

function db_one(string $sql, array $p = []): ?object
{
    $r = db_run($sql, $p)->fetch();
    return $r ?: null;
}

function db_all(string $sql, array $p = []): array
{
    return db_run($sql, $p)->fetchAll();
}


// ============================================================
// ███  HELPERS BASE DE DONNÉES
// ============================================================
function is_blacklisted(string $phone): bool
{
    return db_one(
        'SELECT id FROM whatsapp_blacklist WHERE phone_number=? LIMIT 1',
        [$phone]
    ) !== null;
}

function is_admin(string $phone, string $chat_id, bool $is_group): bool
{
    $row = db_one(
        "SELECT setting_value FROM whatsapp_settings WHERE setting_key='admin_numbers' LIMIT 1"
    );
    $admins = $row ? (json_decode($row->setting_value, true) ?: []) : [];
    if (in_array($phone, $admins, true)) return true;

    if ($is_group && $chat_id) {
        return db_one(
            'SELECT id FROM whatsapp_participants
             WHERE groupe_id=? AND phone_formatted=? AND is_admin=1 LIMIT 1',
            [$chat_id, $phone]
        ) !== null;
    }
    return false;
}

function upsert_group(string $groupe_id, ?string $nom): void
{
    db_run(
        'INSERT INTO groupes_whatsapp (groupe_id, nom, actif, created_at, updated_at)
         VALUES (?,?,1,NOW(),NOW())
         ON DUPLICATE KEY UPDATE
           nom        = COALESCE(?, nom),
           updated_at = NOW()',
        [$groupe_id, $nom, $nom]
    );
}

function upsert_participant(
    string $groupe_id,
    string $phone_raw,
    string $phone_fmt,
    ?string $name,
    bool $is_admin
): void {
    db_run(
        'INSERT INTO whatsapp_participants
         (groupe_id, phone, phone_formatted, is_admin, violation_count,
          profile_name, synced_at, created_at, updated_at)
         VALUES (?,?,?,?,0,?,NOW(),NOW(),NOW())
         ON DUPLICATE KEY UPDATE
           is_admin     = VALUES(is_admin),
           profile_name = COALESCE(VALUES(profile_name), profile_name),
           synced_at    = NOW(),
           updated_at   = NOW()',
        [$groupe_id, $phone_raw, $phone_fmt, (int)$is_admin, $name]
    );
}

function increment_violation(string $phone_fmt): int
{
    db_run(
        'UPDATE whatsapp_participants
         SET violation_count = violation_count + 1
         WHERE phone_formatted=?',
        [$phone_fmt]
    );

    $row   = db_one(
        'SELECT violation_count FROM whatsapp_participants
         WHERE phone_formatted=? LIMIT 1',
        [$phone_fmt]
    );
    $count = (int)($row->violation_count ?? 1);

    if ($count >= VIOLATION_LIMIT) {
        db_run(
            'INSERT INTO whatsapp_blacklist (phone_number, reason, created_at)
             VALUES (?,?,NOW())
             ON DUPLICATE KEY UPDATE reason=VALUES(reason)',
            [$phone_fmt, "Auto-blacklist après $count infractions"]
        );
        wlog('INFO', "$phone_fmt blacklisté automatiquement ($count violations)");
    }

    return $count;
}

function log_security(string $group_id, string $sender, string $action_type, string $reason): void
{
    db_run(
        'INSERT INTO whatsapp_security_logs
         (group_id, sender, action_type, reason, created_at)
         VALUES (?,?,?,?,NOW())',
        [$group_id, $sender, $action_type, $reason]
    );
}


// ============================================================
// ███  HELPERS TEXTE & TÉLÉPHONE
// ============================================================
function clean_phone(string $phone): string
{
    $phone = preg_replace('/@.*/', '', $phone);
    return preg_replace('/\D/', '', $phone);
}

function clean_text(string $msg): string
{
    return trim(strip_tags($msg));
}


// ============================================================
// ███  LOGGER
// ============================================================
function wlog(string $level, string $msg): void
{
    static $levels = ['DEBUG' => 0, 'INFO' => 1, 'WARN' => 2, 'ERROR' => 3];
    if (($levels[$level] ?? 1) < 1) return;

    if (file_exists(LOG_FILE) && filesize(LOG_FILE) > LOG_MAX_BYTES) {
        rename(LOG_FILE, LOG_FILE . '.' . date('Ymd-His') . '.bak');
    }
    @file_put_contents(
        LOG_FILE,
        date('Y-m-d H:i:s') . " [$level] $msg\n",
        FILE_APPEND | LOCK_EX
    );
}