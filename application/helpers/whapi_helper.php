<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;

/**
 * Whapi.Cloud Helper pour CodeIgniter 3
 * Envoi de messages WhatsApp vers des groupes via l'API Whapi.Cloud
 */

/**
 * Effectue une requête vers l'API Whapi.Cloud
 *
 * @param string $method Méthode HTTP (GET, POST, etc.)
 * @param string $endpoint Endpoint de l'API (ex: /messages/text)
 * @param array $data Données à envoyer (pour POST)
 * @return array|false Réponse décodée ou false en cas d'erreur
 */
function _whapi_request($method, $endpoint, $data = [])
{
    $CI =& get_instance();
    $token = $CI->config->item('whapi_token');
    $base_url = $CI->config->item('whapi_base_url');

    if (empty($token)) {
        log_message('error', 'Whapi: Token API non configuré');
        return false;
    }

    try {
        $client = new Client([
            'base_uri' => $base_url,
            'timeout'  => 30,
        ]);

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ];

        if (!empty($data) && $method === 'POST') {
            $options['json'] = $data;
        }

        $response = $client->request($method, $endpoint, $options);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);

    } catch (Exception $e) {
        log_message('error', 'Whapi API Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Envoie un message texte à un groupe WhatsApp
 *
 * @param string $group_id ID du groupe (sans @g.us)
 * @param string $message Contenu du message
 * @return array|false Réponse de l'API ou false
 */
function send_whapi_text($group_id, $message)
{
    $endpoint = '/messages/text';
    $data = [
        'to'   => $group_id . '@g.us',
        'body' => $message,
    ];
    return _whapi_request('POST', $endpoint, $data);
}

/**
 * Envoie une image à un groupe WhatsApp
 *
 * @param string $group_id ID du groupe
 * @param string $image_url URL publique de l'image
 * @param string $caption Légende (optionnel)
 * @return array|false
 */
function send_whapi_image($group_id, $image_url, $caption = '')
{
    $endpoint = '/messages/image';
    $data = [
        'to'    => $group_id . '@g.us',
        'media' => $image_url,
        'body'  => $caption,
    ];
    return _whapi_request('POST', $endpoint, $data);
}

/**
 * Envoie une vidéo à un groupe WhatsApp
 *
 * @param string $group_id ID du groupe
 * @param string $video_url URL publique de la vidéo
 * @param string $caption Légende
 * @return array|false
 */
function send_whapi_video($group_id, $video_url, $caption = '')
{
    $endpoint = '/messages/video';
    $data = [
        'to'    => $group_id . '@g.us',
        'media' => $video_url,
        'body'  => $caption,
    ];
    return _whapi_request('POST', $endpoint, $data);
}

/**
 * Envoie un document (PDF, etc.) à un groupe WhatsApp
 *
 * @param string $group_id ID du groupe
 * @param string $document_url URL publique du document
 * @param string $filename Nom du fichier (optionnel)
 * @return array|false
 */
function send_whapi_document($group_id, $document_url, $filename = '')
{
    $endpoint = '/messages/document';
    $data = [
        'to'       => $group_id . '@g.us',
        'media'    => $document_url,
        'filename' => $filename,
    ];
    return _whapi_request('POST', $endpoint, $data);
}

/**
 * Récupère la liste des groupes WhatsApp connectés
 *
 * @return array|false Liste des groupes ou false
 */
function get_whapi_groups()
{
    $endpoint = '/groups';
    return _whapi_request('GET', $endpoint);
}

/**
 * Envoie un message à plusieurs groupes à la fois
 *
 * @param array $group_ids Liste des IDs de groupes
 * @param string $message Contenu du message
 * @param string $media_url URL du média (optionnel)
 * @param string $media_type Type de média (image, video, document)
 * @return array Résultats par groupe
 */
function send_whapi_bulk_to_groups($group_ids, $message, $media_url = null, $media_type = 'text')
{
    $results = [];
    foreach ($group_ids as $group_id) {
        switch ($media_type) {
            case 'image':
                $result = send_whapi_image($group_id, $media_url, $message);
                break;
            case 'video':
                $result = send_whapi_video($group_id, $media_url, $message);
                break;
            case 'document':
                $result = send_whapi_document($group_id, $media_url);
                break;
            default:
                $result = send_whapi_text($group_id, $message);
        }
        $results[$group_id] = $result;
        // Petit délai pour éviter la surcharge
        usleep(500000); // 0.5 seconde
    }
    return $results;
}