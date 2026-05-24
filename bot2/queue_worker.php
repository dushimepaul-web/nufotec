<?php
/**
 * ============================================================
 *  NUFOTEC — Queue Worker  (fichier unique)
 *  Envoie les messages en attente dans whatsapp_queue
 *
 *  Cron : * * * * * php /chemin/queue_worker.php >> /dev/null 2>&1
 *  Daemon: php queue_worker.php --daemon
 * ============================================================
 */
declare(strict_types=1);

// ── Même configuration que webhook.php ──────────────────────
define('DB_HOST',    'localhost');
define('DB_USER',    'nufotec_nufotec');     // ← changer
define('DB_PASS',    '6886Paul@');   // ← changer
define('DB_NAME',    'nufotec_db');

define('API_TOKEN',  'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw');
define('API_URL',    'https://gate.whapi.cloud/');

define('LOG_FILE',       __DIR__ . '/webhook.log');
define('LOG_MAX_BYTES',  5 * 1024 * 1024);
define('BATCH_SIZE',     30);   // messages par cycle
define('CURL_TIMEOUT',   8);


// ── Verrou anti-double exécution ─────────────────────────────
$lock = fopen(sys_get_temp_dir() . '/nufotec_worker.lock', 'w');
if (!flock($lock, LOCK_EX | LOCK_NB)) {
    wlog('DEBUG', 'Worker déjà actif');
    exit(0);
}

$daemon = in_array('--daemon', $argv ?? [], true);
wlog('INFO', 'Worker démarré' . ($daemon ? ' [daemon]' : ''));

do {
    $n = run_cycle();
    wlog('INFO', "Cycle terminé: $n messages traités");
    if ($daemon) sleep(5);
} while ($daemon);

flock($lock, LOCK_UN);


// ============================================================
// ███  CYCLE DE TRAITEMENT
// ============================================================
function run_cycle(): int
{
    // Pause si queue_paused = 1
    $paused = db_one(
        "SELECT setting_value FROM whatsapp_settings WHERE setting_key='queue_paused' LIMIT 1"
    );
    if ($paused && $paused->setting_value === '1') {
        wlog('INFO', 'Queue en pause');
        return 0;
    }

    $settings   = load_settings();
    $max_retry  = (int)($settings['max_retries'] ?? 3);
    $delay_min  = (int)($settings['delay_min']   ?? 3);
    $delay_max  = (int)($settings['delay_max']   ?? 8);

    // Récupérer un lot depuis whatsapp_queue
    $items = db_all(
        'SELECT * FROM whatsapp_queue
         WHERE status IN (\'pending\',\'retry\')
           AND scheduled_at <= NOW()
           AND retry_count < ?
         ORDER BY priority DESC, scheduled_at ASC
         LIMIT ?',
        [$max_retry, BATCH_SIZE]
    );

    if (empty($items)) return 0;

    $ok = $err = 0;
    foreach ($items as $item) {
        // Marquer "en cours"
        db_run(
            'UPDATE whatsapp_queue SET status=\'processing\' WHERE id=?',
            [$item->id]
        );

        $success = send_item($item);

        if ($success) {
            db_run(
                'UPDATE whatsapp_queue
                 SET status=\'completed\', processed_at=NOW()
                 WHERE id=?',
                [$item->id]
            );
            $ok++;
        } else {
            $retries = (int)$item->retry_count + 1;
            if ($retries >= $max_retry) {
                db_run(
                    'UPDATE whatsapp_queue
                     SET status=\'failed\', retry_count=?, processed_at=NOW()
                     WHERE id=?',
                    [$retries, $item->id]
                );
                wlog('ERROR', "Item {$item->id} échec définitif après $retries essais");
            } else {
                $delay = 60 * (2 ** $retries); // backoff: 120s, 240s, 480s
                db_run(
                    'UPDATE whatsapp_queue
                     SET status=\'retry\', retry_count=?,
                         scheduled_at=DATE_ADD(NOW(), INTERVAL ? SECOND)
                     WHERE id=?',
                    [$retries, $delay, $item->id]
                );
                wlog('WARN', "Item {$item->id} retry #{$retries} dans {$delay}s");
            }
            $err++;
        }

        // Délai anti-ban entre envois
        usleep(rand($delay_min * 1_000_000, $delay_max * 1_000_000));
    }

    wlog('INFO', "Lot: OK=$ok ERR=$err");
    return $ok + $err;
}

// ============================================================
// ███  ENVOI D'UN ITEM
// ============================================================
function send_item(object $item): bool
{
    // Déterminer la destination
    $to = match($item->target_type) {
        'group' => $item->target_id,
        'inbox' => $item->phone_number,
        default => null,
    };
    if (empty($to)) { wlog('WARN', "Item {$item->id} sans destinataire"); return false; }

    // Formater selon le type
    $result = null;
    if (!empty($item->media_url)) {
        $type = in_array($item->message_type, ['image','video','audio','document'], true)
            ? $item->message_type : 'image';
        $data = ['to' => wa_format($to), 'url' => $item->media_url];
        if (!empty($item->message_data)) $data['caption'] = $item->message_data;
        $result = whapi('messages/' . $type, 'POST', $data);
    } elseif (!empty($item->message_data)) {
        $result = whapi('messages/text', 'POST', [
            'to'   => wa_format($to),
            'body' => $item->message_data,
        ]);
    }

    // Logger dans whatsapp_logs
    $status = ($result !== null) ? 'sent' : 'failed';
    db_run(
        'INSERT INTO whatsapp_logs
         (phone_number, message_content, message_type, status, sent_at, created_at)
         VALUES (?,?,?,?,NOW(),NOW())',
        [
            preg_replace('/\D/', '', (string)$to),
            substr((string)($item->message_data ?? ''), 0, 500),
            $item->message_type,
            $status,
        ]
    );

    return $result !== null;
}

// ============================================================
// ███  API WHAPI
// ============================================================
function whapi(string $endpoint, string $method = 'GET', ?array $data = null): ?array
{
    $ch = curl_init(API_URL . ltrim($endpoint, '/'));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => CURL_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 4,
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

    if ($err || $status >= 400) {
        wlog('ERROR', "whapi $method $endpoint HTTP=$status err=$err");
        return null;
    }
    return json_decode((string)$body, true) ?: [];
}

function wa_format(string $to): string
{
    if (str_contains($to, '@')) return $to;
    return preg_replace('/\D/', '', $to) . '@c.us';
}

// ============================================================
// ███  BASE DE DONNÉES
// ============================================================
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}
function db_run(string $sql, array $p = []): PDOStatement { $st = db()->prepare($sql); $st->execute($p); return $st; }
function db_one(string $sql, array $p = []): ?object { $r = db_run($sql, $p)->fetch(); return $r ?: null; }
function db_all(string $sql, array $p = []): array { return db_run($sql, $p)->fetchAll(); }

function load_settings(): array
{
    $rows = db_all('SELECT setting_key, setting_value FROM whatsapp_settings');
    $s = [];
    foreach ($rows as $r) $s[$r->setting_key] = $r->setting_value;
    return $s;
}

// ============================================================
// ███  LOGGER
// ============================================================
function wlog(string $level, string $msg): void
{
    if ($level === 'DEBUG') return;
    if (file_exists(LOG_FILE) && filesize(LOG_FILE) > LOG_MAX_BYTES)
        rename(LOG_FILE, LOG_FILE . '.' . date('Ymd-His') . '.bak');
    @file_put_contents(LOG_FILE, date('Y-m-d H:i:s') . " [$level] $msg\n", FILE_APPEND | LOCK_EX);
}
