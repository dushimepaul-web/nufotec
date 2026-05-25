<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=nufotec_db;charset=utf8mb4', 'root', '');
    $stmt = $pdo->query('SELECT * FROM whatsapp_settings');
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "SETTINGS:\n";
    print_r($settings);
} catch (PDOException $e) {
    echo "Root failed: " . $e->getMessage() . "\n";
}
