<?php
/**
 * process_worker.php
 * Traite les webhooks en file d'attente (exécuté en arrière-plan)
 */
declare(strict_types=1);

define('PROCESSOR_URL',    'https://nufotec.com/bot2/process.php');
define('PROCESSOR_SECRET', 'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw');
define('LOG_FILE',         __DIR__ . '/webhook.log');

// Récupérer le fichier à traiter
$filename = $argv[1] ?? '';

if (empty($filename) || !file_exists($filename)) {
    wlog('ERROR', 'process_worker: Fichier invalide: ' . $filename);
    exit(1);
}

// Lire le payload
$raw = file_get_contents($filename);
if (empty($raw)) {
    wlog('ERROR', 'process_worker: Payload vide: ' . $filename);
    @unlink($filename);
    exit(1);
}

// Valider le JSON
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    wlog('ERROR', 'process_worker: JSON invalide dans: ' . $filename);
    @unlink($filename);
    exit(1);
}

wlog('INFO', 'process_worker: Traitement de ' . basename($filename) . ' - type: ' . ($payload['type'] ?? 'unknown'));

// Envoyer au processeur avec un timeout plus long (5 secondes)
$ch = curl_init(PROCESSOR_URL);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $raw,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'X-Internal-Secret: ' . PROCESSOR_SECRET,
    ],
    CURLOPT_RETURNTRANSFER => true,  // On attend la réponse cette fois
    CURLOPT_TIMEOUT        => 5,     // Timeout 5 secondes
    CURLOPT_CONNECTTIMEOUT => 2,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode === 200) {
    wlog('INFO', 'process_worker: Succès pour ' . basename($filename) . ' (HTTP ' . $httpCode . ')');
} else {
    wlog('ERROR', 'process_worker: Échec pour ' . basename($filename) . 
         ' (HTTP ' . $httpCode . ', erreur: ' . $error . ')');
}

// Supprimer le fichier après traitement
@unlink($filename);
exit(0);

function wlog(string $level, string $msg): void
{
    static $levels = ['DEBUG' => 0, 'INFO' => 1, 'WARN' => 2, 'ERROR' => 3];
    $minLevel = 1;
    
    if (($levels[$level] ?? 1) < $minLevel) {
        return;
    }
    
    @file_put_contents(
        LOG_FILE,
        date('Y-m-d H:i:s') . " [$level] $msg\n",
        FILE_APPEND | LOCK_EX
    );
}