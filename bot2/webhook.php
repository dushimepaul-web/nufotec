<?php
/**
 * ============================================================
 *  NUFOTEC — WhatsApp Webhook (webhook.php)
 *  CORRIGÉ : Réponse 200 ultra-rapide + traitement asynchrone
 *  Plus aucun risque de timeout ETIMEDOUT
 * ============================================================
 */
declare(strict_types=1);

// ============================================================
//  CONFIGURATION
// ============================================================
define('PROCESSOR_URL',    'https://nufotec.com/bot2/process.php');
define('PROCESSOR_SECRET', 'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw');
define('LOG_FILE',         __DIR__ . '/webhook.log');
define('LOG_MAX_BYTES',    5 * 1024 * 1024);
define('PENDING_DIR',      __DIR__ . '/pending_webhooks/');

// Créer le dossier pour les webhooks en attente s'il n'existe pas
if (!is_dir(PENDING_DIR)) {
    mkdir(PENDING_DIR, 0755, true);
}

// ============================================================
//  ÉTAPE 1 : RÉPONSE 200 IMMÉDIATE (rien ne doit précéder ceci)
// ============================================================
ob_start(); // Démarrer le buffer de sortie

// Envoyer la réponse 200
http_response_code(200);
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
echo '{"status":"ok","timestamp":' . time() . '}';

// Forcer l'envoi immédiat des headers
header('Content-Length: ' . ob_get_length());
header('Connection: close');
ob_end_flush();
flush();

// Clôture spécifique pour FastCGI/PHP-FPM
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

// ============================================================
//  ÉTAPE 2 : RÉCUPÉRER LE PAYLOAD (après la réponse)
// ============================================================
$raw = (string) file_get_contents('php://input');

// Si pas de payload, on arrête
if (empty($raw)) {
    wlog('INFO', 'Payload vide - rien à traiter');
    exit;
}

// ============================================================
//  ÉTAPE 3 : STOCKAGE EN FILE D'ATTENTE (disque)
//  Solution 1: Fichier temporaire (la plus simple et fiable)
// ============================================================
$filename = PENDING_DIR . time() . '_' . uniqid() . '.json';
$result = file_put_contents($filename, $raw, LOCK_EX);

if ($result === false) {
    wlog('ERROR', 'Impossible d\'écrire le fichier: ' . $filename);
    exit;
}

wlog('INFO', 'Webhook sauvegardé: ' . basename($filename) . ' (taille: ' . strlen($raw) . ' bytes)');

// ============================================================
//  ÉTAPE 4 : DÉCLENCHER LE TRAITEMENT EN ARRIÈRE-PLAN
//  Solution A: via exec() (recommandée pour Apache)
//  Solution B: via cron job (alternative plus robuste)
// ============================================================

// Vérifier quel environnement PHP on utilise
$php_path = PHP_BINARY; // Chemin vers l'exécutable PHP

// Commande pour lancer process.php en arrière-plan
$cmd = sprintf(
    '%s %s/process_worker.php %s > /dev/null 2>&1 &',
    escapeshellarg($php_path),
    __DIR__,
    escapeshellarg($filename)
);

// Exécuter en arrière-plan (non bloquant)
if (function_exists('exec')) {
    exec($cmd);
    wlog('DEBUG', 'Worker déclenché: ' . $cmd);
} else {
    // Si exec n'est pas disponible, on utilisera un cron job
    wlog('WARN', 'exec() non disponible, le webhook sera traité par le cron');
}

exit;

// ============================================================
//  FONCTION DE LOG (rotation automatique)
// ============================================================
function wlog(string $level, string $msg): void
{
    static $levels = ['DEBUG' => 0, 'INFO' => 1, 'WARN' => 2, 'ERROR' => 3];
    $minLevel = 1; // Niveau minimum (1 = INFO)
    
    if (($levels[$level] ?? 1) < $minLevel) {
        return;
    }
    
    // Rotation du log si nécessaire
    if (file_exists(LOG_FILE) && filesize(LOG_FILE) > LOG_MAX_BYTES) {
        $backup = LOG_FILE . '.' . date('Ymd-His') . '.bak';
        rename(LOG_FILE, $backup);
    }
    
    // Écrire dans le log
    @file_put_contents(
        LOG_FILE,
        date('Y-m-d H:i:s') . " [$level] $msg\n",
        FILE_APPEND | LOCK_EX
    );
}