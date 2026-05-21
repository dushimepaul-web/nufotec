<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WhatsApp_Whapi Library
 * Toute la configuration est intégrée dans ce fichier
 * 
 * @package     CodeIgniter
 * @subpackage  Libraries
 * @author      WhatsApp Bot System
 * @version     1.0
 */

class WhatsApp_Whapi {
    
    // Configuration API Whapi
    private $api_key = 'VghiTs88mPZt3GkeA7dGf4G6v3Av6Skw';
    private $base_url = 'https://gate.whapi.cloud';
    private $timeout = 60;
    private $upload_timeout = 120;
    private $debug = true;
    private $max_file_size = 16777216; // 16 MB
    
    // Paramètres de retry
    private $retry_attempts = 3;
    private $retry_delay = 2000; // millisecondes
    private $rate_limit_delay = 1000;
    
    // Webhook
    private $webhook_secret = 'nufotecburundi2026';
    
    // Chemins de stockage
    private $media_storage_path;
    private $tmp_path;
    
    // Numéros admin (votre numéro WhatsApp)
    private $admin_numbers = ['25779666439', '25768863945'];
    
    // Types autorisés pour les membres (SEULEMENT TEXTE)
    private $allowed_for_members = ['text'];
    
    // Patterns bloqués pour les membres (liens)
    private $blocked_patterns = [
        '/https?:\/\//i',
        '/www\.[a-z0-9\.\-]+/i',
        '/\.(com|org|net|fr|cm|info|biz|io|ai)/i',
        '/wa\.me\//i',
        '/chat\.whatsapp\.com\//i'
    ];
    
    // Paramètres Anti-Ban
    private $antiban = [
        'min_delay_micro' => 500000,
        'max_delay_micro' => 1500000,
        'min_delay_seconds' => 2,
        'max_delay_seconds' => 4,
        'long_pause_probability' => 20,
        'long_pause_min' => 5,
        'long_pause_max' => 10,
        'batch_size' => 5,
        'batch_interval' => 60,
        'max_messages_per_hour' => 60
    ];
    
    private $CI;
    private $last_response = null;
    private $last_error = null;
    
    public function __construct() {
        $this->CI =& get_instance();
        
        // Définir les chemins de stockage
        $this->media_storage_path = FCPATH . 'uploads/whatsapp_media/';
        $this->tmp_path = FCPATH . 'tmp/';
        
        // Créer les dossiers s'ils n'existent pas
        $this->create_directories();
    }
    
    /**
     * Créer les dossiers nécessaires
     */
    private function create_directories() {
        if (!is_dir($this->media_storage_path)) {
            mkdir($this->media_storage_path, 0755, true);
        }
        if (!is_dir($this->tmp_path)) {
            mkdir($this->tmp_path, 0755, true);
        }
    }
    
    /**
     * Vérifier si un numéro est admin
     */
    public function is_admin($number) {
        $formatted_number = $this->format_number($number);
        foreach ($this->admin_numbers as $admin) {
            if ($this->format_number($admin) === $formatted_number) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Obtenir la liste des numéros admin
     */
    public function get_admin_numbers() {
        return $this->admin_numbers;
    }
    
    /**
     * Vérifier si un type de média est autorisé pour les membres
     */
    public function is_allowed_for_member($media_type) {
        return in_array($media_type, $this->allowed_for_members);
    }
    
    /**
     * Vérifier si un texte contient des liens bloqués
     */
    public function has_blocked_link($text) {
        foreach ($this->blocked_patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Obtenir les paramètres anti-ban
     */
    public function get_antiban_settings() {
        return $this->antiban;
    }
    
    /**
     * Obtenir le secret du webhook
     */
    public function get_webhook_secret() {
        return $this->webhook_secret;
    }
    
    /**
     * Obtenir le chemin de stockage des médias
     */
    public function get_media_storage_path() {
        return $this->media_storage_path;
    }
    
    /**
     * Envoyer un message texte
     */
    public function send_text($to, $message, $options = []) {
        // Vérifier les liens pour les non-admins
        if (!$this->is_admin($to) && $this->has_blocked_link($message)) {
            return [
                'success' => false,
                'error' => 'Message contient des liens non autorisés'
            ];
        }
        
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'text',
            'text' => ['body' => $message]
        ];
        if (!empty($options['reply_to'])) {
            $payload['context'] = ['message_id' => $options['reply_to']];
        }
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoyer une image
     */
    public function send_image($to, $image_url, $caption = null) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'image',
            'image' => ['link' => $image_url]
        ];
        if ($caption) $payload['image']['caption'] = $caption;
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoyer une vidéo
     */
    public function send_video($to, $video_url, $caption = null) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'video',
            'video' => ['link' => $video_url]
        ];
        if ($caption) $payload['video']['caption'] = $caption;
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoyer un audio
     */
    public function send_audio($to, $audio_url) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'audio',
            'audio' => ['link' => $audio_url]
        ];
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoyer un document
     */
    public function send_document($to, $doc_url, $filename, $caption = null) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'document',
            'document' => [
                'link' => $doc_url,
                'filename' => $filename
            ]
        ];
        if ($caption) $payload['document']['caption'] = $caption;
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoyer un sticker
     */
    public function send_sticker($to, $sticker_url) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'sticker',
            'sticker' => ['link' => $sticker_url]
        ];
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoyer une localisation
     */
    public function send_location($to, $latitude, $longitude, $name = null, $address = null) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'location',
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude
            ]
        ];
        if ($name) $payload['location']['name'] = $name;
        if ($address) $payload['location']['address'] = $address;
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoyer une réaction à un message (emoji)
     */
    public function react_to_message($message_id, $emoji) {
        return $this->request('POST', '/messages/' . $message_id . '/react', ['emoji' => $emoji]);
    }
    
    /**
     * Supprimer un message
     */
    public function delete_message($message_id) {
        return $this->request('DELETE', '/messages/' . $message_id);
    }
    
    /**
     * Obtenir les infos du compte WhatsApp
     */
    public function get_status() {
        return $this->request('GET', '/status');
    }
    
    /**
     * Obtenir la liste des groupes WhatsApp
     */
    public function get_groups() {
        return $this->request('GET', '/groups');
    }
    
    /**
     * Obtenir les participants d'un groupe
     */
    public function get_group_participants($group_id) {
        return $this->request('GET', '/groups/' . $group_id . '/participants');
    }
    
    /**
     * Obtenir les détails d'un groupe
     */
    public function get_group_info($group_id) {
        return $this->request('GET', '/groups/' . $group_id);
    }
    
    /**
     * Quitter un groupe
     */
    public function leave_group($group_id) {
        return $this->request('DELETE', '/groups/' . $group_id . '/leave');
    }
    
    /**
     * Obtenir un message par son ID
     */
    public function get_message($message_id) {
        return $this->request('GET', '/messages/' . $message_id);
    }
    
    /**
     * Marquer un message comme lu
     */
    public function mark_as_read($message_id) {
        return $this->request('POST', '/messages/' . $message_id . '/read');
    }
    
    /**
     * Télécharger un média depuis Whapi
     */
    public function download_media($media_url, $destination_path = null) {
        if (!$destination_path) {
            $destination_path = $this->tmp_path . uniqid() . '_media.bin';
        }
        
        $ch = curl_init($media_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->api_key
        ]);
        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $data) {
            file_put_contents($destination_path, $data);
            return $destination_path;
        }
        return false;
    }
    
    /**
     * Télécharger et sauvegarder un média avec un nom organisé
     */
    public function download_and_save_media($media_url, $media_type, $extension = null) {
        $extensions = [
            'image' => 'jpg',
            'video' => 'mp4',
            'audio' => 'mp3',
            'document' => 'pdf',
            'sticker' => 'webp'
        ];
        
        $ext = $extension ?? ($extensions[$media_type] ?? 'bin');
        $filename = uniqid() . '.' . $ext;
        $filepath = $this->media_storage_path . $filename;
        
        $result = $this->download_media($media_url, $filepath);
        
        if ($result) {
            return [
                'path' => $filepath,
                'url' => base_url('uploads/whatsapp_media/' . $filename),
                'filename' => $filename
            ];
        }
        
        return false;
    }
    
    /**
     * Uploader un fichier local vers Whapi
     */
    public function upload_media($file_path, $media_type) {
        if (!file_exists($file_path)) {
            return ['success' => false, 'error' => 'Fichier non trouvé'];
        }
        
        $ch = curl_init($this->base_url . '/media/upload');
        $headers = [
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: multipart/form-data'
        ];
        
        $post_fields = [
            'file' => new CURLFile($file_path),
            'type' => $media_type
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->upload_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code >= 200 && $http_code < 300) {
            return [
                'success' => true,
                'data' => json_decode($response, true)
            ];
        }
        
        return [
            'success' => false,
            'error' => "HTTP $http_code: $response"
        ];
    }
    
    /**
     * Formater le numéro de téléphone au format international
     */
    private function format_number($number) {
        $number = preg_replace('/[^0-9+]/', '', $number);
        if (substr($number, 0, 2) === '00') {
            $number = '+' . substr($number, 2);
        }
        if (substr($number, 0, 1) !== '+') {
            $number = '+257' . ltrim($number, '0'); // Indicatif Burundi
        }
        return $number;
    }
    
    /**
     * Requête API avec retry et gestion des erreurs
     */
    private function request($method, $endpoint, $data = null) {
        $attempt = 0;
        
        while ($attempt < $this->retry_attempts) {
            $attempt++;
            
            $ch = curl_init($this->base_url . $endpoint);
            $headers = [
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: application/json',
                'Accept: application/json'
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            } elseif ($method === 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            } elseif ($method === 'PUT') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            $this->last_response = $response;
            
            if ($this->debug) {
                log_message('debug', "Whapi API [$method $endpoint] - HTTP $http_code");
                if ($response) {
                    log_message('debug', "Response: " . substr($response, 0, 500));
                }
            }
            
            // Succès
            if ($http_code >= 200 && $http_code < 300) {
                $decoded = json_decode($response, true);
                return [
                    'success' => true,
                    'data' => $decoded ?: $response,
                    'http_code' => $http_code
                ];
            }
            
            // Erreurs critiques - ne pas réessayer
            if (in_array($http_code, [401, 403, 404])) {
                $this->last_error = $error ?: $response;
                return [
                    'success' => false,
                    'error' => "HTTP $http_code: " . ($error ?: $response),
                    'http_code' => $http_code
                ];
            }
            
            // Rate limiting - utiliser Retry-After
            if ($http_code === 429) {
                $retry_after = $this->rate_limit_delay * $attempt;
                if (preg_match('/Retry-After:\s*(\d+)/i', $response, $matches)) {
                    $retry_after = (int)$matches[1];
                }
                sleep(min($retry_after, 60));
                continue;
            }
            
            // Erreurs 5xx - réessai avec backoff exponentiel
            if ($http_code >= 500 && $http_code < 600) {
                $wait = min(pow(2, $attempt) * 1000, 30000);
                usleep($wait * 1000);
                continue;
            }
            
            // Autres erreurs
            if ($attempt < $this->retry_attempts) {
                usleep($this->retry_delay * 1000 * $attempt);
                continue;
            }
            
            $this->last_error = $error ?: $response;
            return [
                'success' => false,
                'error' => $error ?: $response,
                'http_code' => $http_code
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Échec après ' . $this->retry_attempts . ' tentatives'
        ];
    }
    
    // Getters publics
    public function get_last_response() { return $this->last_response; }
    public function get_last_error() { return $this->last_error; }
    public function get_api_key() { return $this->api_key; }
    public function get_base_url() { return $this->base_url; }
}