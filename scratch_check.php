<?php
$mysqli = new mysqli("localhost", "root", "", "nufotec_db");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$tables = [
    'participants_whatsapp',
    'whatsapp_participants',
    'whatsapp_inbox',
    'whatsapp_blacklist',
    'violations_log'
];

foreach ($tables as $table) {
    echo "=== COLUMNS FOR $table ===\n";
    $res = $mysqli->query("SHOW COLUMNS FROM $table");
    if ($res) {
        while ($col = $res->fetch_assoc()) {
            echo "  {$col['Field']} - {$col['Type']}\n";
        }
    } else {
        echo "  Table $table does not exist\n";
    }
    echo "\n";
}

$mysqli->close();
