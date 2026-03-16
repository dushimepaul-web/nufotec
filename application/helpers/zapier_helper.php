<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Envoie des données structurées vers Zapier Webhook
 * Supporte: texte, URLs d'images/vidéos, ou fichiers base64
 * 
 * @param array $data Données à envoyer (titre, description, media_urls, etc.)
 * @param string $webhook_url URL du webhook Zapier
 * @return bool|string Réponse Zapier ou false en cas d'erreur
 */
function send_to_zapier($data, $webhook_url = null)
{
    // Sécurité: récupérer l'URL depuis config si non fournie
    if (empty($webhook_url)) {
        $CI =& get_instance();
        $webhook_url = $CI->config->item('zapier_webhook_url');
        
        if (empty($webhook_url)) {
            log_message('error', 'Zapier: Aucune URL webhook configurée');
            return false;
        }
    }

    // Préparation des données médias (conversion fichiers locaux en base64 si nécessaire)
    $payload = prepare_zapier_payload($data);
    
    $ch = curl_init($webhook_url);

    // Configuration cURL robuste
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,                    // Timeout 30s
        CURLOPT_CONNECTTIMEOUT => 10,             // Connexion 10s max
        CURLOPT_SSL_VERIFYPEER => true,           // Vérification SSL obligatoire
        CURLOPT_SSL_VERIFYHOST => 2,              // Vérification hostname
        CURLOPT_FOLLOWLOCATION => true,           // Suivre redirects
        CURLOPT_MAXREDIRS => 3,                   // Limite redirects
        CURLOPT_USERAGENT => 'CodeIgniter-Zapier-Webhook/1.0'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Gestion erreurs
    if ($response === false || !empty($curl_error)) {
        log_message('error', "Zapier cURL Error: {$curl_error}");
        return false;
    }

    // Zapier renvoie généralement 200 (succès) ou 410 (webhook désactivé)
    if (!in_array($http_code, [200, 201, 202])) {
        log_message('error', "Zapier HTTP Error {$http_code}: {$response}");
        return false;
    }

    log_message('debug', "Zapier Success [{$http_code}]: " . substr($response, 0, 200));
    return $response;
}

/**
 * Prépare le payload pour Zapier (gère les médias)
 */
function prepare_zapier_payload($data)
{
    $payload = [
        'title' => $data['title'] ?? '',
        'description' => $data['description'] ?? '',
        'timestamp' => date('c'),
        'source' => base_url()
    ];

    // Gestion Images (URLs ou fichiers locaux)
    if (!empty($data['image'])) {
        if (filter_var($data['image'], FILTER_VALIDATE_URL)) {
            // C'est déjà une URL
            $payload['image_url'] = $data['image'];
        } elseif (file_exists($data['image'])) {
            // Fichier local: convertir en base64 ou URL temporaire
            $payload['image_base64'] = base64_encode(file_get_contents($data['image']));
            $payload['image_filename'] = basename($data['image']);
        }
    }

    // Gestion Vidéos (même logique)
    if (!empty($data['video'])) {
        if (filter_var($data['video'], FILTER_VALIDATE_URL)) {
            $payload['video_url'] = $data['video'];
        } elseif (file_exists($data['video'])) {
            // Pour les vidéos, préférer l'upload sur un CDN d'abord
            // car base64 est trop lourd pour Zapier directement
            log_message('warning', 'Zapier: Vidéo locale détectée, upload CDN recommandé');
            $payload['video_path'] = $data['video']; // Zapier peut récupérer via autre zap
        }
    }

    // Métadonnées additionnelles
    if (!empty($data['metadata'])) {
        $payload['metadata'] = $data['metadata'];
    }

    return $payload;
}
