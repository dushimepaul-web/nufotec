<?php
/**
 * FICHIER DE DIAGNOSTIC WHATSAPP BOT
 * 
 * Exécutez ce fichier pour vérifier toutes les configurations
 * Navigateur: https://votre-site.com/check_config.php
 * Terminal: php check_config.php
 */

// Forcer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Couleurs pour terminal
$colors = [
    'red' => "\033[31m",
    'green' => "\033[32m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'reset' => "\033[0m"
];

// Détecter si on est en CLI
$is_cli = (php_sapi_name() === 'cli');
$br = $is_cli ? "\n" : "<br>";
$separator = $is_cli ? str_repeat("=", 60) . "\n" : "<hr>";

/**
 * Fonction d'affichage
 */
function output($message, $type = 'info', $is_cli = false, $colors = []) {
    if ($is_cli) {
        $color = '';
        switch($type) {
            case 'success': $color = $colors['green']; break;
            case 'error': $color = $colors['red']; break;
            case 'warning': $color = $colors['yellow']; break;
            case 'info': $color = $colors['blue']; break;
        }
        echo $color . $message . $colors['reset'] . "\n";
    } else {
        $style = '';
        switch($type) {
            case 'success': $style = 'color: green; font-weight: bold;'; break;
            case 'error': $style = 'color: red; font-weight: bold;'; break;
            case 'warning': $style = 'color: orange; font-weight: bold;'; break;
            case 'info': $style = 'color: blue;'; break;
        }
        echo "<div style='$style'>$message</div>";
    }
}

// Entête
if (!$is_cli) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>WhatsApp Bot - Diagnostic Configuration</title>
        <meta charset='UTF-8'>
        <style>
            body { font-family: monospace; padding: 20px; background: #f5f5f5; }
            .container { max-width: 1200px; margin: auto; background: white; padding: 20px; border-radius: 10px; }
            h1 { color: #075E54; }
            h2 { color: #128C7E; margin-top: 30px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
            .box { background: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #075E54; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #075E54; color: white; }
            .status-ok { color: green; font-weight: bold; }
            .status-error { color: red; font-weight: bold; }
            .status-warning { color: orange; font-weight: bold; }
            .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
            .badge-ok { background: green; color: white; }
            .badge-error { background: red; color: white; }
            .badge-warning { background: orange; color: white; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🤖 WhatsApp Bot - Diagnostic Système</h1>
            <p>Date du test: " . date('Y-m-d H:i:s') . "</p>
    ";
}

output($separator, 'info', $is_cli, $colors);
output("🔍 DIAGNOSTIC WHATSAPP BOT - " . date('Y-m-d H:i:s'), 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

// ============================================
// 1. INFORMATIONS GÉNÉRALES PHP
// ============================================
output("\n📌 1. INFORMATIONS PHP", 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

$php_info = [
    'Version PHP' => phpversion(),
    'SAPI (Interface)' => php_sapi_name(),
    'Système d\'exploitation' => PHP_OS,
    'User actuel' => function_exists('get_current_user') ? get_current_user() : 'N/A',
    'User du processus' => function_exists('exec') ? exec('whoami') : 'N/A',
    'Hostname' => gethostname(),
    'IP Serveur' => $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? 'N/A',
];

foreach ($php_info as $key => $value) {
    $status = '';
    if ($key == 'Version PHP' && version_compare($value, '7.4', '<')) {
        $status = '⚠️ Version obsolète (recommandé PHP 7.4+)';
        output("  • $key: $value - $status", 'warning', $is_cli, $colors);
    } else {
        output("  • $key: $value", 'info', $is_cli, $colors);
    }
}

// ============================================
// 2. EXTENSIONS PHP NÉCESSAIRES
// ============================================
output("\n📌 2. EXTENSIONS PHP REQUISES", 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

$required_extensions = [
    'curl' => 'API WhatsApp (requis)',
    'json' => 'Traitement des messages (requis)',
    'mysqli' => 'Base de données (requis)',
    'pdo_mysql' => 'Base de données (requis)',
    'mbstring' => 'Traitement texte (recommandé)',
    'gd' => 'Traitement images (optionnel)',
    'fileinfo' => 'Détection type fichiers (recommandé)',
    'openssl' => 'Sécurité HTTPS (recommandé)',
];

$missing_extensions = [];
foreach ($required_extensions as $ext => $desc) {
    if (extension_loaded($ext)) {
        output("  ✅ $ext - $desc", 'success', $is_cli, $colors);
    } else {
        output("  ❌ $ext - MANQUANT - $desc", 'error', $is_cli, $colors);
        $missing_extensions[] = $ext;
    }
}

// ============================================
// 3. CONFIGURATION PHP
// ============================================
output("\n📌 3. CONFIGURATION PHP", 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

$php_configs = [
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'post_max_size' => ini_get('post_max_size'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_input_time' => ini_get('max_input_time'),
    'max_input_vars' => ini_get('max_input_vars'),
    'allow_url_fopen' => ini_get('allow_url_fopen') ? 'On' : 'Off',
    'display_errors' => ini_get('display_errors') ? 'On' : 'Off',
    'log_errors' => ini_get('log_errors') ? 'On' : 'Off',
    'error_log' => ini_get('error_log') ?: 'Non défini',
];

$recommended = [
    'max_execution_time' => 300,
    'memory_limit' => 256,
    'post_max_size' => 100,
    'upload_max_filesize' => 100,
];

foreach ($php_configs as $key => $value) {
    $status = '';
    if (isset($recommended[$key])) {
        $num_value = (int)preg_replace('/[^0-9]/', '', $value);
        $num_recommended = $recommended[$key];
        if ($num_value < $num_recommended) {
            $status = "⚠️ Recommandé: {$num_recommended}M";
            output("  • $key: $value - $status", 'warning', $is_cli, $colors);
        } else {
            output("  • $key: $value", 'success', $is_cli, $colors);
        }
    } else {
        output("  • $key: $value", 'info', $is_cli, $colors);
    }
}

// ============================================
// 4. BASE DE DONNÉES
// ============================================
output("\n📌 4. BASE DE DONNÉES", 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

// Tenter de se connecter à la base
$db_config_path = dirname(__FILE__) . '/application/config/database.php';
$db_configured = false;

if (file_exists($db_config_path)) {
    output("  ✅ Fichier database.php trouvé", 'success', $is_cli, $colors);
    
    // Inclure la config
    include($db_config_path);
    
    if (isset($db['default'])) {
        try {
            $conn = new mysqli(
                $db['default']['hostname'],
                $db['default']['username'],
                $db['default']['password'],
                $db['default']['database']
            );
            
            if ($conn->connect_error) {
                output("  ❌ Connexion échouée: " . $conn->connect_error, 'error', $is_cli, $colors);
            } else {
                output("  ✅ Connexion réussie", 'success', $is_cli, $colors);
                output("  • Base de données: " . $db['default']['database'], 'info', $is_cli, $colors);
                output("  • Host: " . $db['default']['hostname'], 'info', $is_cli, $colors);
                output("  • Username: " . $db['default']['username'], 'info', $is_cli, $colors);
                
                // Vérifier les tables nécessaires
                $required_tables = ['groupes_whatsapp', 'participants_whatsapp', 'wa_messages_queue', 'messages_inbox'];
                output("\n  📋 Tables requises:", 'info', $is_cli, $colors);
                
                foreach ($required_tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($result->num_rows > 0) {
                        output("    ✅ $table existe", 'success', $is_cli, $colors);
                    } else {
                        output("    ❌ $table manquante - Exécutez le script SQL", 'error', $is_cli, $colors);
                    }
                }
                $conn->close();
            }
        } catch (Exception $e) {
            output("  ❌ Erreur connexion: " . $e->getMessage(), 'error', $is_cli, $colors);
        }
    } else {
        output("  ❌ Configuration base de données non trouvée dans database.php", 'error', $is_cli, $colors);
    }
} else {
    output("  ❌ Fichier database.php non trouvé", 'error', $is_cli, $colors);
}

// ============================================
// 5. FICHIERS ET PERMISSIONS
// ============================================
output("\n📌 5. FICHIERS ET PERMISSIONS", 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

$paths_to_check = [
    'application/logs/' => 'Logs système',
    'uploads/whatsapp_media/' => 'Stockage médias',
    'application/cache/' => 'Cache système',
    'tmp/' => 'Fichiers temporaires',
    'application/config/whatsapp.php' => 'Config WhatsApp',
    'application/config/database.php' => 'Config Base de données',
];

foreach ($paths_to_check as $path => $description) {
    $full_path = dirname(__FILE__) . '/' . $path;
    if (file_exists($full_path)) {
        if (is_writable($full_path)) {
            output("  ✅ $path - $description (accessible en écriture)", 'success', $is_cli, $colors);
        } else {
            output("  ⚠️ $path - $description (non accessible en écriture)", 'warning', $is_cli, $colors);
        }
    } else {
        output("  ❌ $path - $description (n'existe pas)", 'error', $is_cli, $colors);
        // Tenter de créer le dossier
        if (strpos($path, '/') !== false && !file_exists(dirname($full_path))) {
            $dir = dirname($full_path);
            if (@mkdir($dir, 0755, true)) {
                output("      ✅ Dossier créé: $dir", 'success', $is_cli, $colors);
            }
        }
    }
}

// ============================================
// 6. CRON JOBS (UNIQUEMENT EN CLI)
// ============================================
if ($is_cli) {
    output("\n📌 6. CRON JOBS", 'info', $is_cli, $colors);
    output($separator, 'info', $is_cli, $colors);
    
    $cron_check = shell_exec('crontab -l 2>/dev/null');
    if ($cron_check && !empty($cron_check)) {
        output("  ✅ Cron jobs trouvés:", 'success', $is_cli, $colors);
        $lines = explode("\n", $cron_check);
        foreach ($lines as $line) {
            if (trim($line) && !str_starts_with($line, '#')) {
                output("    • $line", 'info', $is_cli, $colors);
            }
        }
    } else {
        output("  ⚠️ Aucun cron job trouvé", 'warning', $is_cli, $colors);
        output("  📝 Exemple d'installation:", 'info', $is_cli, $colors);
        output("     crontab -e", 'info', $is_cli, $colors);
        output("     * * * * * php " . dirname(__FILE__) . "/index.php Cron process_queue", 'info', $is_cli, $colors);
    }
}

// ============================================
// 7. PERMISSIONS D'ÉCRITURE
// ============================================
output("\n📌 7. TESTS D'ÉCRITURE", 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

$test_file = dirname(__FILE__) . '/test_write_' . time() . '.txt';
if (@file_put_contents($test_file, 'Test')) {
    output("  ✅ Écriture de fichiers possible", 'success', $is_cli, $colors);
    unlink($test_file);
} else {
    output("  ❌ Impossible d'écrire des fichiers", 'error', $is_cli, $colors);
}

// ============================================
// 8. FONCTIONS PHP DISPONIBLES
// ============================================
output("\n📌 8. FONCTIONS PHP CRITIQUES", 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

$critical_functions = [
    'curl_init' => 'Requête API WhatsApp',
    'json_decode' => 'Traitement Webhook',
    'mysqli_connect' => 'Base de données',
    'preg_match' => 'Filtrage liens',
    'sleep' => 'Anti-ban (requis)',
    'usleep' => 'Micro-pauses (requis)',
];

foreach ($critical_functions as $func => $desc) {
    if (function_exists($func)) {
        output("  ✅ $func - $desc", 'success', $is_cli, $colors);
    } else {
        output("  ❌ $func - $desc (NON DISPONIBLE)", 'error', $is_cli, $colors);
    }
}

// ============================================
// 9. CONFIGURATION WHATSAPP
// ============================================
output("\n📌 9. CONFIGURATION WHATSAPP", 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

$whatsapp_config_path = dirname(__FILE__) . '/application/config/whatsapp.php';
if (file_exists($whatsapp_config_path)) {
    output("  ✅ Fichier whatsapp.php trouvé", 'success', $is_cli, $colors);
    
    // Lire le fichier pour vérifier les tokens
    $config_content = file_get_contents($whatsapp_config_path);
    
    // Vérifier si les valeurs par défaut sont présentes
    if (strpos($config_content, 'YOUR_ACCESS_TOKEN') !== false) {
        output("  ❌ Access Token non configuré (YOUR_ACCESS_TOKEN)", 'error', $is_cli, $colors);
    } elseif (strpos($config_content, 'YOUR_PHONE_NUMBER_ID') !== false) {
        output("  ❌ Phone Number ID non configuré", 'error', $is_cli, $colors);
    } else {
        output("  ✅ Configuration API WhatsApp présente", 'success', $is_cli, $colors);
        
        // Extraire quelques infos (sans afficher les tokens)
        preg_match("/admin_numbers'\] = \[(.*?)\]/s", $config_content, $matches);
        if (isset($matches[1]) && strpos($matches[1], '2376') !== false) {
            output("  ✅ Numéros admin configurés", 'success', $is_cli, $colors);
        } else {
            output("  ⚠️ Aucun numéro admin configuré", 'warning', $is_cli, $colors);
        }
    }
} else {
    output("  ❌ Fichier whatsapp.php non trouvé", 'error', $is_cli, $colors);
    output("  📝 Créez le fichier à partir de application/config/whatsapp.php.sample", 'info', $is_cli, $colors);
}

// ============================================
// 10. TEST API WHATSAPP (Optionnel)
// ============================================
output("\n📌 10. TEST API WHATSAPP (Optionnel)", 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

// Demander confirmation pour le test API
$test_api = false;
if (!$is_cli) {
    echo "<div class='box'>";
    echo "<form method='post' style='margin:0;'>";
    echo "<button type='submit' name='test_api' value='1' style='background:#075E54; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;'>🔌 Tester la connexion API WhatsApp</button>";
    echo "</form>";
    $test_api = isset($_POST['test_api']);
} else {
    $test_api = true;
}

if ($test_api && isset($whatsapp_config_path) && file_exists($whatsapp_config_path)) {
    include($whatsapp_config_path);
    
    if (isset($config['whatsapp_access_token']) && 
        $config['whatsapp_access_token'] != 'YOUR_ACCESS_TOKEN' &&
        isset($config['whatsapp_api_url']) &&
        $config['whatsapp_api_url'] != 'https://graph.facebook.com/v18.0/YOUR_PHONE_NUMBER_ID/messages') {
        
        output("  🔄 Test de connexion API WhatsApp...", 'info', $is_cli, $colors);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v18.0/me/accounts');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $config['whatsapp_access_token']]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            output("  ✅ API WhatsApp accessible - Token valide", 'success', $is_cli, $colors);
        } elseif ($http_code == 401) {
            output("  ❌ Token invalide ou expiré", 'error', $is_cli, $colors);
        } else {
            output("  ⚠️ API WhatsApp: Code HTTP $http_code", 'warning', $is_cli, $colors);
        }
    } else {
        output("  ⚠️ Configurez d'abord whatsapp.php avec vos tokens", 'warning', $is_cli, $colors);
    }
}

// ============================================
// 11. RÉCAPITULATIF
// ============================================
output("\n📌 11. RÉCAPITULATIF FINAL", 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

$total_checks = 0;
$passed_checks = 0;

// Compter les succès
if (version_compare(phpversion(), '7.4', '>=')) $passed_checks++;
$total_checks++;
if (empty($missing_extensions)) $passed_checks++;
$total_checks++;
if (file_exists($db_config_path)) $passed_checks++;
$total_checks++;
if (file_exists($whatsapp_config_path)) $passed_checks++;
$total_checks++;

$percentage = round(($passed_checks / $total_checks) * 100);

output("  • Configuration testée: $total_checks points", 'info', $is_cli, $colors);
output("  • Points valides: $passed_checks/$total_checks ($percentage%)", 'info', $is_cli, $colors);

if ($percentage >= 80) {
    output("\n  ✅ STATUT: SYSTÈME PRÊT À FONCTIONNER", 'success', $is_cli, $colors);
} elseif ($percentage >= 50) {
    output("\n  ⚠️ STATUT: CONFIGURATION PARTIELLE - CORRIGEZ LES ERREURS", 'warning', $is_cli, $colors);
} else {
    output("\n  ❌ STATUT: CONFIGURATION INCOMPLÈTE - VOIR ERREURS CI-DESSUS", 'error', $is_cli, $colors);
}

// Suggestions
output("\n📌 SUGGESTIONS D'AMÉLIORATION:", 'info', $is_cli, $colors);
output($separator, 'info', $is_cli, $colors);

if (!empty($missing_extensions)) {
    output("  • Installez les extensions PHP manquantes: " . implode(', ', $missing_extensions), 'warning', $is_cli, $colors);
}
if (ini_get('max_execution_time') < 300) {
    output("  • Augmentez max_execution_time (actuel: " . ini_get('max_execution_time') . "s, recommandé: 300s)", 'warning', $is_cli, $colors);
}
if (preg_replace('/[^0-9]/', '', ini_get('memory_limit')) < 256) {
    output("  • Augmentez memory_limit (actuel: " . ini_get('memory_limit') . ", recommandé: 256M)", 'warning', $is_cli, $colors);
}

// Footer
if (!$is_cli) {
    echo "
            <div class='box' style='margin-top: 20px; background: #e8f5e9; border-left-color: #4CAF50;'>
                <h3>📋 Prochaines étapes:</h3>
                <ol>
                    <li>Corrigez les erreurs indiquées en <span style='color:red'>rouge</span></li>
                    <li>Configurez <strong>application/config/whatsapp.php</strong> avec vos tokens</li>
                    <li>Importez les tables SQL dans votre base de données</li>
                    <li>Configurez les crons: <code>crontab -e</code></li>
                    <li>Testez avec un premier message WhatsApp</li>
                </ol>
            </div>
        </div>
        <script>
            // Recharger automatiquement les infos
            setTimeout(function() {
                location.reload();
            }, 30000);
        </script>
    </body>
    </html>
    ";
}

// Log du diagnostic
$log_entry = date('Y-m-d H:i:s') . " - PHP " . phpversion() . " - " . ($percentage >= 80 ? "OK" : "KO") . " - $passed_checks/$total_checks\n";
file_put_contents(dirname(__FILE__) . '/diagnostic.log', $log_entry, FILE_APPEND);

exit;