<?php
/**
 * ============================================================
 *  NUFOTEC — Test vitesse réponse webhook
 *  Uploader sur https://nufotec.com/bot2/test_webhook.php
 *  Puis envoyer une requête POST depuis Whapi ou curl :
 *    curl -X POST https://nufotec.com/bot2/test_webhook.php -d '{"test":1}'
 *  Vérifier que la réponse 200 arrive en < 1 seconde
 * ============================================================
 */
declare(strict_types=1);
ignore_user_abort(true);
set_time_limit(30);

// ── 1. Réponse IMMÉDIATE ─────────────────────────────────────
$resp = json_encode(['status' => 'ok', 'ts' => microtime(true)]);

http_response_code(200);
header('Content-Type: application/json');
header('Connection: close');
header('Cache-Control: no-cache');
header('Content-Length: ' . strlen($resp));
echo $resp;

// Vider tous les buffers
while (ob_get_level() > 0) ob_end_flush();
flush();

$fast_cgi = false;
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
    $fast_cgi = true;
}

// ── 2. Après la réponse : traitement lourd simulé ────────────
$log = __DIR__ . '/test_webhook.log';
$start = microtime(true);

// Simuler 5 secondes de traitement (DB, API, etc.)
sleep(5);

$elapsed = round(microtime(true) - $start, 3);

$line = date('Y-m-d H:i:s')
    . ' | fastcgi=' . ($fast_cgi ? 'OUI' : 'NON')
    . ' | traitement=' . $elapsed . 's'
    . ' | method=' . ($_SERVER['REQUEST_METHOD'] ?? '?')
    . ' | body=' . substr((string)file_get_contents('php://input'), 0, 100)
    . "\n";

file_put_contents($log, $line, FILE_APPEND | LOCK_EX);