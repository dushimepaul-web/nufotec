<?php
// public/index.php

// Définir le chemin racine du projet
define('ROOT_PATH', dirname(__DIR__));

// Charger l'autoloader depuis la racine
$autoloadPath = ROOT_PATH . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die('Autoloader not found at: ' . $autoloadPath . "\nPlease run: composer install");
}
require $autoloadPath;

use GuzzleHttp\Client;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

// Charger la configuration
$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    die('config.php not found at: ' . $configPath);
}
$config = require $configPath;

// Vérifier le token
if (empty($config['token'])) {
    die('Token not configured in config.php');
}

// Afficher les chemins pour déboguer
error_log("ROOT_PATH: " . ROOT_PATH);
error_log("Autoload path: " . $autoloadPath);
error_log("Config loaded from: " . $configPath);

$app = new App();

$client = new Client([
    'base_uri' => $config['apiUrl'],
    'timeout' => 30.0,
    'verify' => false
]);

// Commands
const COMMANDS = [
    'TEXT' => 'Simple text message',
    'IMAGE' => 'Send image',
    'DOCUMENT' => 'Send document',
    'VIDEO' => 'Send video',
    'CONTACT' => 'Send contact',
    'PRODUCT' => 'Send product',
    'GROUP_CREATE' => 'Create group',
    'GROUP_TEXT' => 'Simple text message for the group',
    'GROUPS_IDS' => 'Get the id\'s of your three groups'
];

// Chemins des fichiers - utiliser ROOT_PATH
const FILES = [
    'IMAGE' => ROOT_PATH . '/files/file_example_JPG_100kB.jpg',
    'DOCUMENT' => ROOT_PATH . '/files/file-example_PDF_500_kB.pdf',
    'VIDEO' => ROOT_PATH . '/files/file_example_MP4_480_1_5MG.mp4',
    'VCARD' => ROOT_PATH . '/files/sample-vcard.txt'
];

function sendWhapiRequest($endpoint, $params = [], $method = 'POST')
{
    global $config, $client;

    $url = $endpoint;
    $options = [
        'headers' => [
            'Authorization' => 'Bearer ' . $config['token'],
            'Accept' => 'application/json',
        ],
    ];

    if (!empty($params)) {
        if ($method === 'GET') {
            $options['query'] = $params;
        } else {
            // Vérifier si on envoie un fichier
            $hasFile = false;
            if (isset($params['media']) && is_string($params['media']) && file_exists($params['media'])) {
                $hasFile = true;
            }

            if ($hasFile) {
                $options['multipart'] = [];
                foreach ($params as $name => $contents) {
                    if ($name === 'media' && is_string($contents) && file_exists($contents)) {
                        $options['multipart'][] = [
                            'name' => $name,
                            'contents' => fopen($contents, 'r'),
                            'filename' => basename($contents)
                        ];
                    } else {
                        $options['multipart'][] = ['name' => $name, 'contents' => (string)$contents];
                    }
                }
            } else {
                $options['headers']['Content-Type'] = 'application/json';
                $options['body'] = json_encode($params);
            }
        }
    }

    try {
        error_log("Sending request to: {$config['apiUrl']}/{$endpoint}");
        $response = $client->request($method, $url, $options);
        $body = $response->getBody()->getContents();
        $json = json_decode($body, true);
        error_log("Response received");
        return $json;
    } catch (\Exception $e) {
        error_log("Error in sendWhapiRequest: " . $e->getMessage());
        throw $e;
    }
}

$app->get('/', function (Request $request, Response $response) {
    return $response->write('Bot is running. Webhook endpoint: /hook/messages');
});

$app->post('/hook/messages', function (Request $request, Response $response) use ($config) {
    try {
        $body = $request->getBody()->getContents();
        error_log("=== Webhook received ===");
        error_log("Raw body: " . $body);
        
        $data = json_decode($body, true);
        if (!$data) {
            error_log("Invalid JSON received");
            return $response->withStatus(400)->write('Invalid JSON');
        }
        
        $messages = $data['messages'] ?? [];
        error_log("Number of messages: " . count($messages));
        
        foreach ($messages as $message) {
            // Ignorer les messages envoyés par le bot
            if (isset($message['from_me']) && $message['from_me'] === true) {
                error_log("Skipping message from bot");
                continue;
            }
            
            $sender = ['to' => $message['chat_id'] ?? $message['from']];
            $endpoint = 'messages/text';
            
            // Récupérer le texte
            $textBody = trim($message['text']['body'] ?? '');
            error_log("Message from: {$sender['to']}, text: $textBody");
            
            // Traiter la commande
            $commandIndex = is_numeric($textBody) ? (int)$textBody - 1 : null;
            $commands = array_keys(COMMANDS);
            $command = ($commandIndex !== null && isset($commands[$commandIndex])) ? $commands[$commandIndex] : null;
            
            switch ($command) {
                case 'TEXT':
                    $sender['body'] = 'Simple text message from WhatsApp bot!';
                    error_log("Sending text message");
                    break;
                    
                case 'IMAGE':
                    if (file_exists(FILES['IMAGE'])) {
                        $sender['caption'] = 'Here is an image for you!';
                        $sender['media'] = FILES['IMAGE'];
                        $endpoint = 'messages/image';
                        error_log("Sending image from: " . FILES['IMAGE']);
                    } else {
                        $sender['body'] = "Image file not found. Please add an image to: " . FILES['IMAGE'];
                        error_log("Image file not found");
                    }
                    break;
                    
                case 'DOCUMENT':
                    if (file_exists(FILES['DOCUMENT'])) {
                        $sender['caption'] = 'Here is a document for you!';
                        $sender['media'] = FILES['DOCUMENT'];
                        $endpoint = 'messages/document';
                        error_log("Sending document from: " . FILES['DOCUMENT']);
                    } else {
                        $sender['body'] = "Document file not found. Please add a document to: " . FILES['DOCUMENT'];
                        error_log("Document file not found");
                    }
                    break;
                    
                case 'VIDEO':
                    if (file_exists(FILES['VIDEO'])) {
                        $sender['caption'] = 'Here is a video for you!';
                        $sender['media'] = FILES['VIDEO'];
                        $endpoint = 'messages/video';
                        error_log("Sending video from: " . FILES['VIDEO']);
                    } else {
                        $sender['body'] = "Video file not found. Please add a video to: " . FILES['VIDEO'];
                        error_log("Video file not found");
                    }
                    break;
                    
                case 'CONTACT':
                    if (file_exists(FILES['VCARD'])) {
                        $sender['name'] = 'Whapi Test Contact';
                        $sender['vcard'] = file_get_contents(FILES['VCARD']);
                        $endpoint = 'messages/contact';
                        error_log("Sending contact");
                    } else {
                        $sender['body'] = "VCard file not found: " . FILES['VCARD'];
                        error_log("VCard file not found");
                    }
                    break;
                    
                case 'GROUPS_IDS':
                    error_log("Getting groups list");
                    $groupsResponse = sendWhapiRequest('groups', ['count' => 3], 'GET');
                    if (!empty($groupsResponse['groups'])) {
                        $groupIds = [];
                        foreach ($groupsResponse['groups'] as $index => $group) {
                            $groupIds[] = ($index + 1) . ". {$group['id']} - {$group['name']}";
                        }
                        $sender['body'] = "Your groups:\n" . implode("\n", $groupIds);
                    } else {
                        $sender['body'] = 'No groups found';
                    }
                    break;
                    
                default:
                    // Message d'aide
                    $helpMessage = "🤖 *WhatsApp Bot Commands*\n\n";
                    $helpMessage .= "Send me a number from the list:\n\n";
                    $i = 1;
                    foreach (COMMANDS as $cmd => $desc) {
                        $helpMessage .= "*{$i}.* {$desc}\n";
                        $i++;
                    }
                    $helpMessage .= "\nOr send:\n";
                    $helpMessage .= "• /help - Show this menu\n";
                    $helpMessage .= "• /test - Test connection\n";
                    
                    $sender['body'] = $helpMessage;
                    error_log("Sending help message");
                    break;
            }
            
            // Envoyer la réponse
            if (isset($sender['body']) || isset($sender['media'])) {
                try {
                    $result = sendWhapiRequest($endpoint, $sender);
                    error_log("Message sent successfully");
                } catch (\Exception $e) {
                    error_log("Failed to send message: " . $e->getMessage());
                    // Ne pas renvoyer d'erreur au webhook pour éviter les boucles
                }
            }
        }
        
        return $response->withStatus(200)->write('OK');
        
    } catch (\Exception $e) {
        error_log("Webhook error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return $response->withStatus(500)->write('Error: ' . $e->getMessage());
    }
});

$app->run();