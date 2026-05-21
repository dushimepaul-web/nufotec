<?php
define('BASEPATH', true);
require_once 'system/core/Common.php';
require_once 'application/config/whapi.php';

echo "Test de configuration Whapi:\n\n";
echo "API Key: " . ($config['whapi']['api_key'] ?? 'NON DEFINI') . "\n";
echo "Base URL: " . ($config['whapi']['base_url'] ?? 'NON DEFINI') . "\n";
echo "Webhook Secret: " . ($config['whapi']['webhook_secret'] ?? 'NON DEFINI') . "\n";