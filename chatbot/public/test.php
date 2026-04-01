<?php
// C:\wamp64\www\chatbot\public\test_send.php
require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/config.php';

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => $config['apiUrl'],
    'timeout' => 30
]);

$phoneNumber = '1234567890@s.whatsapp.net'; // Remplacez par votre numéro de test

try {
    $response = $client->post('/messages/text', [
        'headers' => [
            'Authorization' => 'Bearer ' . $config['token'],
            'Content-Type' => 'application/json'
        ],
        'json' => [
            'to' => $phoneNumber,
            'body' => 'Test message from your PHP bot!'
        ]
    ]);
    
    echo "✅ Message sent successfully!\n";
    echo "Response: " . $response->getBody() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    if ($e->hasResponse()) {
        echo "Response: " . $e->getResponse()->getBody() . "\n";
    }
}