<?php
// webhook.php
require_once 'vendor/autoload.php';

// Lisez les données entrantes
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Journalisez les messages reçus
file_put_contents('logs.txt', date('Y-m-d H:i:s') . " - " . $input . "\n", FILE_APPEND);

// Répondez aux commandes
if (isset($data['messages'][0])) {
    $message = $data['messages'][0];
    $from = $message['from'];
    $text = $message['text']['body'] ?? '';
    
    if ($text === '/help') {
        sendMessage($from, "Commandes disponibles :\n/help - Cette aide\n/image - Envoyer une image\n/file - Envoyer un fichier");
    } elseif ($text === '/image') {
        sendImage($from);
    } else {
        sendMessage($from, "Commande non reconnue. Tapez /help pour la liste des commandes.");
    }
}

function sendMessage($to, $message) {
    $config = require 'config.php';
    $ch = curl_init('https://gate.whapi.cloud/messages/text');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $config['token'],
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'to' => $to,
        'body' => $message
    ]));
    curl_exec($ch);
    curl_close($ch);
}

function sendImage($to) {
    $config = require 'config.php';
    $ch = curl_init('https://gate.whapi.cloud/messages/image');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $config['token'],
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'to' => $to,
        'media' => 'https://example.com/image.jpg',
        'caption' => 'Voici une image'
    ]));
    curl_exec($ch);
    curl_close($ch);
}