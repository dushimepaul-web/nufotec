<?php
/**
 * ============================================================
 *  NUFOTEC — WhatsApp Webhook  (webhook.php)
 *  Rôle UNIQUE : recevoir, valider, répondre 200, déléguer
 *  Compatible 100% avec la base de données existante
 * ============================================================
 */
declare(strict_types=1);

// ── Empêcher PHP de s'arrêter si le client ferme la connexion ──
ignore_user_abort(true);
set_time_limit(30);

// ============================================================
//  CONFIGURATION
// ============================================================
define('WEBHOOK_TOKEN', '');          // laisser vide si non utilisé
define('PROCESSOR_URL', 'https://nufotec.com/bot2/process.php');
define('PROCESSOR_SECRET', 'CHANGE_MOI_secret_interne_xK9z');
define('LOG_FILE',     __DIR__ . '/webhook.log');
define('LOG_MAX_BYTES', 5 * 1024 * 1024);


// ============================================================
//  LIRE LE PAYLOAD AVANT TOUT
// ============================================================
$raw = (string) file_get_contents('php://input');


// ============================================================
//  VALIDATION TOKEN (optionnel)
// ============================================================
if (WEBHOOK_TOKEN !== '') {
    $tok = $_GET['token'] ?? $_SERVER['HTTP_X_WEBHOOK_TOKEN'] ?? '';
    if (!hash_equals(WEBHOOK_TOKEN, $tok)) {
        wlog('WARN', 'Token invalide: ' . $tok);
        http_response_code(401);
        exit;
    }
}


// ============================================================
//  VALIDATION JSON MINIMALE
// ============================================================
if (empty($raw)) {
    http_response_code(200);
    exit;
}
$payload = json_decode($raw, true);
if (!is_array($payload) || empty($payload)) {
    wlog('ERROR', 'JSON invalide: ' . substr($raw, 0, 200));
    http_response_code(200); // on répond quand même 200 à Whapi
    exit;
}

wlog('INFO', 'Webhook reçu · type=' . ($payload['type'] ?? $payload['event']['type'] ?? '?'));


// ============================================================
//  RÉPONSE IMMÉDIATE À WHAPI  (avant tout traitement lourd)
// ============================================================
$resp = '{"status":"ok"}';
http_response_code(200);
header('Content-Type: application/json');
header('Connection: close');
header('Cache-Control: no-cache');
header('Content-Length: ' . strlen($resp));
echo $resp;

// Vider tous les buffers de sortie
while (ob_get_level() > 0) {
    ob_end_flush();
}
flush();

// FastCGI : fermer la connexion client immédiatement
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
    wlog('DEBUG', 'Connexion fermée via fastcgi_finish_request');
} else {
    wlog('DEBUG', 'fastcgi_finish_request non disponible — flush() utilisé');
}


// ============================================================
//  DÉLÉGATION AU PROCESSEUR (connexion non bloquante)
//  On envoie le payload à process.php sans attendre sa réponse
// ============================================================
$ch = curl_init(PROCESSOR_URL);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $raw,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'X-Internal-Secret: ' . PROCESSOR_SECRET,
    ],
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_TIMEOUT_MS     => 500,    // on n'attend PAS la réponse (0.5 s max)
    CURLOPT_CONNECTTIMEOUT => 1,
    CURLOPT_NOBODY         => false,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_NOSIGNAL       => 1,      // obligatoire avec timeout < 1 s
]);
curl_exec($ch);
curl_close($ch);

wlog('INFO', 'Payload délégué à process.php');
exit;


// ============================================================
//  LOGGER  (rotation auto)
// ============================================================
function wlog(string $level, string $msg): void
{
    static $levels = ['DEBUG' => 0, 'INFO' => 1, 'WARN' => 2, 'ERROR' => 3];
    if (($levels[$level] ?? 1) < 1) return;  // min INFO en prod

    if (file_exists(LOG_FILE) && filesize(LOG_FILE) > LOG_MAX_BYTES) {
        rename(LOG_FILE, LOG_FILE . '.' . date('Ymd-His') . '.bak');
    }
    @file_put_contents(
        LOG_FILE,
        date('Y-m-d H:i:s') . " [$level] $msg\n",
        FILE_APPEND | LOCK_EX
    );
}