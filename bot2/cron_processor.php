<?php
/**
 * cron_processor.php
 * Alternative si exec() n'est pas disponible
 * À exécuter toutes les minutes via cron:
 * * * * * * php /chemin/vers/cron_processor.php
 */
declare(strict_types=1);

define('PENDING_DIR', __DIR__ . '/pending_webhooks/');
define('PROCESSOR_URL', 'https://nufotec.com/bot2/process.php');
define('PROCESSOR_SECRET', 'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw');
define('LOG_FILE', __DIR__ . '/webhook.log');

// Trouver tous les fichiers en attente
$files = glob(PENDING_DIR . '*.json');

if (empty($files)) {
    exit(0);
}

foreach ($files as $file) {
    // Ignorer les fichiers trop récents (< 2 secondes)
    if (filemtime($file) > time() - 2) {
        continue;
    }
    
    $raw = file_get_contents($file);
    if (empty($raw)) {
        @unlink($file);
        continue;
    }
    
    // Envoyer au processeur
    $ch = curl_init(PROCESSOR_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $raw,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Internal-Secret: ' . PROCESSOR_SECRET,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        @unlink($file);
    }
}

exit(0);