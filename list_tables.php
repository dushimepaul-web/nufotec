<?php
$mysqli = new mysqli("localhost", "root", "", "nufotec_db");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== TABLES ===\n";
$result = $mysqli->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $table = $row[0];
    echo "- $table\n";
}

echo "\n=== COLUMNS FOR groupes_whatsapp ===\n";
$res = $mysqli->query("SHOW COLUMNS FROM groupes_whatsapp");
if ($res) {
    while ($col = $res->fetch_assoc()) {
        echo "  {$col['Field']} - {$col['Type']}\n";
    }
} else {
    echo "groupes_whatsapp table does not exist\n";
}

echo "\n=== COLUMNS FOR whatsapp_queue ===\n";
$res = $mysqli->query("SHOW COLUMNS FROM whatsapp_queue");
if ($res) {
    while ($col = $res->fetch_assoc()) {
        echo "  {$col['Field']} - {$col['Type']}\n";
    }
} else {
    echo "whatsapp_queue table does not exist\n";
}

echo "\n=== COLUMNS FOR wa_messages_queue ===\n";
$res = $mysqli->query("SHOW COLUMNS FROM wa_messages_queue");
if ($res) {
    while ($col = $res->fetch_assoc()) {
        echo "  {$col['Field']} - {$col['Type']}\n";
    }
} else {
    echo "wa_messages_queue table does not exist\n";
}

echo "\n=== COLUMNS FOR whatsapp_settings ===\n";
$res = $mysqli->query("SHOW COLUMNS FROM whatsapp_settings");
if ($res) {
    while ($col = $res->fetch_assoc()) {
        echo "  {$col['Field']} - {$col['Type']}\n";
    }
} else {
    echo "whatsapp_settings table does not exist\n";
}

echo "\n=== COLUMNS FOR antiban_settings ===\n";
$res = $mysqli->query("SHOW COLUMNS FROM antiban_settings");
if ($res) {
    while ($col = $res->fetch_assoc()) {
        echo "  {$col['Field']} - {$col['Type']}\n";
    }
} else {
    echo "antiban_settings table does not exist\n";
}

$mysqli->close();
