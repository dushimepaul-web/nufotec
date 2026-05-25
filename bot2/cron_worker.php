<?php
/**
 * ============================================================
 *  NUFOTEC — WhatsApp CRON Worker (Version Ultra-Fast)
 *  Objectif : 10 000+ messages/jour sans ban
 *  
 *  Stratégies anti-ban :
 *  1. Rotation d'IP (si possible)
 *  2. Délais intelligents variables
 *  3. Contenu unique et personnalisé
 *  4. Rotation des sessions/messages
 *  5. Éviter les mots-clés spam
 *  6. Répartition sur 24h
 *  
 *  CRON à exécuter TOUTES LES 30 SECONDES :
 *  * * * * * php /path/to/cron_worker.php >> /var/log/nufotec_wa.log 2>&1
 *  * * * * * sleep 30 && php /path/to/cron_worker.php >> /var/log/nufotec_wa.log 2>&1
 * ============================================================
 */
declare(strict_types=1);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// ── Config ────────────────────────────────────────────────
define('DB_HOST',   'localhost');
define('DB_USER',   'nufotec_nufotec');
define('DB_PASS',   '6886Paul@');
define('DB_NAME',   'nufotec_db');
define('API_TOKEN', 'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw');
define('API_URL',   'https://gate.whapi.cloud/');

// ── Paramètres d'envoi (OPTIMISÉS POUR 10K/JOUR) ─────────
define('BATCH_SIZE',         20);    // Messages par exécution
define('DELAY_MIN_MS',      800);    // Délai minimum (ms)
define('DELAY_MAX_MS',     2500);    // Délai maximum (ms)
define('HOURLY_LIMIT',      500);    // Max/heure (ancien: 60)
define('DAILY_LIMIT',     15000);    // Max/jour (ancien: 500)
define('WARMUP_DAYS',         3);    // Jours de chauffe
define('LOCK_FILE', '/tmp/nufotec_wa_worker.lock');

// ── Mots interdits (éviter spam) ─────────────────────────
$SPAM_TRIGGERS = [
    '/gratuit/i', '/offre limitée/i', '/cliquez ici/i', '/urgent/i',
    '/100.?% gratuit/i', '/sans frais/i', '/gagner/i', '/cash/i',
    '/bitcoin/i', '/crypto/i', '/investissement/i', '/miracle/i'
];

// ── Verrouillage (évite concurrence) ─────────────────────
if (file_exists(LOCK_FILE)) {
    $lock_age = time() - filemtime(LOCK_FILE);
    if ($lock_age < 60) { // 1 minute max
        echo "[".date('Y-m-d H:i:s')."] ⚠️ Worker déjà actif (lock: {$lock_age}s)\n";
        exit(0);
    }
}
file_put_contents(LOCK_FILE, getmypid());
register_shutdown_function(fn() => file_exists(LOCK_FILE) && unlink(LOCK_FILE));

// ── Connexion DB ─────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]
        );
    }
    return $pdo;
}

// ── Helper DB ────────────────────────────────────────────
function dbq(string $sql, array $p = []): array { 
    $s = db()->prepare($sql); $s->execute($p); return $s->fetchAll(); 
}
function db1(string $sql, array $p = []): ?object { 
    $s = db()->prepare($sql); $s->execute($p); return $s->fetch() ?: null; 
}
function dbx(string $sql, array $p = []): int { 
    $s = db()->prepare($sql); $s->execute($p); return $s->rowCount(); 
}

// ── Anti-spam: vérifier contenu ─────────────────────────
function isSpammyContent(string $text): bool {
    global $SPAM_TRIGGERS;
    foreach ($SPAM_TRIGGERS as $pattern) {
        if (preg_match($pattern, $text)) return true;
    }
    return false;
}

// ── Personnalisation du message ─────────────────────────
function personalizeMessage(string $template, string $name, string $phone): string {
    $replacements = [
        '{name}' => $name,
        '{phone}' => substr($phone, -4),
        '{time}' => date('H:i'),
        '{day}' => date('l'),
        '{random}' => rand(100, 999)
    ];
    return str_replace(array_keys($replacements), $replacements, $template);
}

// ── Vérification des limites ────────────────────────────
function checkRateLimits(): bool {
    // Période de chauffe (début lent)
    $firstMessage = db1("SELECT MIN(processed_at) as first FROM whatsapp_queue WHERE status='completed'");
    if ($firstMessage && $firstMessage->first) {
        $daysActive = (time() - strtotime($firstMessage->first)) / 86400;
        if ($daysActive < WARMUP_DAYS) {
            $maxPerDay = 500 + ($daysActive / WARMUP_DAYS) * 9500; // 500 → 10000 progressif
            if (DAILY_LIMIT > $maxPerDay) {
                define('DAILY_LIMIT_EFFECTIVE', (int)$maxPerDay);
            } else {
                define('DAILY_LIMIT_EFFECTIVE', DAILY_LIMIT);
            }
        } else {
            define('DAILY_LIMIT_EFFECTIVE', DAILY_LIMIT);
        }
    } else {
        define('DAILY_LIMIT_EFFECTIVE', 100); // Premier jour: max 100
    }

    // Limite horaire
    $hourly = db1(
        "SELECT COUNT(*) c FROM whatsapp_queue 
         WHERE status='completed' AND processed_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
    );
    if (($hourly->c ?? 0) >= HOURLY_LIMIT) {
        echo "[".date('H:i:s')."] ⚠️ Limite horaire: {$hourly->c}/".HOURLY_LIMIT."\n";
        return false;
    }

    // Limite journalière (adaptative)
    $daily = db1(
        "SELECT COUNT(*) c FROM whatsapp_queue 
         WHERE status='completed' AND DATE(processed_at) = CURDATE()"
    );
    if (($daily->c ?? 0) >= DAILY_LIMIT_EFFECTIVE) {
        echo "[".date('H:i:s')."] ⚠️ Limite journalière: {$daily->c}/".DAILY_LIMIT_EFFECTIVE."\n";
        return false;
    }

    return true;
}

// ── Délai intelligent (variation naturelle) ─────────────
function intelligentDelay(): int {
    $base = rand(DELAY_MIN_MS, DELAY_MAX_MS);
    $hour = (int)date('H');
    
    // Heures de pointe (envoi plus rapide)
    if ($hour >= 10 && $hour <= 12 || $hour >= 15 && $hour <= 18) {
        $base = (int)($base * 0.7); // 30% plus rapide
    }
    // Nuit (plus lent pour imiter humain)
    elseif ($hour < 8 || $hour > 21) {
        $base = (int)($base * 1.8); // 80% plus lent
    }
    
    // Variation aléatoire supplémentaire
    $base += rand(-200, 500);
    return max(500, min(5000, $base));
}

// ── Appel API Whapi avec retry ──────────────────────────
function sendViaWhapi(string $endpoint, array $payload, int $retry = 0): ?array {
    $url = API_URL . ltrim($endpoint, '/');
    $ch = curl_init($url);
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . API_TOKEN,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Rate limiting (429) → attente et retry
    if ($code === 429 && $retry < 3) {
        $wait = pow(2, $retry) * 2;
        echo "[".date('H:i:s')."] ⚠️ Rate limit (429), attente {$wait}s...\n";
        sleep($wait);
        return sendViaWhapi($endpoint, $payload, $retry + 1);
    }
    
    if ($error || $code >= 400) {
        error_log("Whapi [$endpoint] code=$code err=$error");
        return null;
    }
    
    return json_decode($body, true) ?: [];
}

// ── Construire payload (version plus variée) ────────────
function buildWhapiPayload(object $job): array {
    $to = $job->target_type === 'group' 
        ? $job->target_id 
        : ($job->phone_number . '@s.whatsapp.net');
    
    $type = $job->message_type;
    $data = $job->message_data ?? '';
    
    // Personnalisation du texte (si message text)
    if ($type === 'text' && !empty($job->contact_name)) {
        $data = personalizeMessage($data, $job->contact_name, $job->phone_number ?? '');
    }
    
    // Éviter les contenus spammés
    if ($type === 'text' && isSpammyContent($data)) {
        throw new \Exception("Contenu potentiellement spam détecté");
    }
    
    $base = ['to' => $to];
    
    // Variation des endpoints pour imiter un humain
    $variations = ['messages/text', 'send/text', 'chats/send'];
    $endpoint = $type === 'text' ? $variations[array_rand($variations)] : 'messages/' . $type;
    
    switch ($type) {
        case 'image':
            return ['endpoint' => $endpoint, 'payload' => array_merge($base, [
                'image' => ['link' => $job->media_url ?? ''], 
                'caption' => $data
            ])];
        case 'text':
        default:
            return ['endpoint' => $endpoint, 'payload' => array_merge($base, ['body' => $data])];
    }
}

// ── MAIN ─────────────────────────────────────────────────
echo "\n[".date('Y-m-d H:i:s')."] 🚀 Worker démarré (PID: ".getmypid().")\n";

if (!checkRateLimits()) {
    echo "[".date('H:i:s')."] 🛑 Arrêt: limites atteintes\n";
    exit(0);
}

// Récupérer les jobs (optimisé pour gros volume)
$jobs = dbq(
    "SELECT * FROM whatsapp_queue 
     WHERE status IN ('pending','retry') 
       AND (scheduled_at IS NULL OR scheduled_at <= NOW())
       AND retry_count < 3
     ORDER BY priority ASC, scheduled_at ASC, created_at ASC
     LIMIT ?",
    [BATCH_SIZE]
);

if (empty($jobs)) {
    echo "[".date('H:i:s')."] ✅ Aucun job en attente\n";
    exit(0);
}

echo "[".date('H:i:s')."] 📨 ".count($jobs)." job(s) à traiter\n";

$stats = ['sent' => 0, 'failed' => 0, 'rate_limited' => 0];

foreach ($jobs as $job) {
    // Vérifier limites à nouveau (si on a dépassé pendant l'exécution)
    if (!checkRateLimits()) {
        echo "[".date('H:i:s')."] 🛑 Limite atteinte, arrêt prématuré\n";
        break;
    }
    
    dbx("UPDATE whatsapp_queue SET status='processing', processed_at=NOW() WHERE id=?", [$job->id]);
    
    try {
        $built = buildWhapiPayload($job);
        $result = sendViaWhapi($built['endpoint'], $built['payload']);
        
        if ($result !== null && !isset($result['error'])) {
            dbx(
                "UPDATE whatsapp_queue SET status='completed', delivery_status='sent', error_message=NULL WHERE id=?",
                [$job->id]
            );
            
            // Log rapide (optionnel)
            if (rand(1, 10) === 1) { // Log 10% des messages seulement
                dbx(
                    "INSERT INTO whatsapp_logs (phone_number, message_type, message_content, status, sent_at) 
                     VALUES (?, ?, ?, 'sent', NOW())",
                    [$job->phone_number ?? $job->target_id, $job->message_type, substr($job->message_data ?? '', 0, 100)]
                );
            }
            
            $stats['sent']++;
            echo "[".date('H:i:s')."] ✅ #{$job->id} → {$job->phone_number}\n";
        } else {
            throw new \Exception("API error");
        }
    } catch (\Throwable $e) {
        $newRetry = $job->retry_count + 1;
        $newStatus = $newRetry >= 3 ? 'failed' : 'retry';
        
        // Backoff exponentiel plus intelligent
        $backoffMinutes = min(pow(2, $newRetry), 60);
        $nextAttempt = date('Y-m-d H:i:s', time() + ($backoffMinutes * 60));
        
        dbx(
            "UPDATE whatsapp_queue SET status=?, retry_count=?, error_message=?, scheduled_at=? WHERE id=?",
            [$newStatus, $newRetry, $e->getMessage(), $newStatus === 'retry' ? $nextAttempt : null, $job->id]
        );
        
        $stats['failed']++;
        echo "[".date('H:i:s')."] ❌ #{$job->id} (retry {$newRetry}/3)\n";
    }
    
    // Délai entre messages
    $delay = intelligentDelay();
    usleep($delay * 1000);
}

// Statistiques finales
$remaining = db1("SELECT COUNT(*) c FROM whatsapp_queue WHERE status IN ('pending','retry')");
$completed = db1("SELECT COUNT(*) c FROM whatsapp_queue WHERE status='completed' AND DATE(processed_at)=CURDATE()");

echo "[".date('H:i:s')."] 🏁 Terminé — Envoyés: {$stats['sent']}, Échecs: {$stats['failed']}\n";
echo "[".date('H:i:s')."] 📊 Aujourd'hui: {$completed->c}/".DAILY_LIMIT_EFFECTIVE." messages | Restant: {$remaining->c}\n";

// Nettoyage
dbx("DELETE FROM whatsapp_queue WHERE status='completed' AND processed_at < DATE_SUB(NOW(), INTERVAL 3 DAY)");