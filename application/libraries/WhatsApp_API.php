<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WhatsApp_Whapi - Library complète pour Whapi.Cloud
 * Support: text, image, video, audio, document, sticker, location, contact
 * Anti-ban intégré + retry automatique
 */
class WhatsApp_Whapi {
    
    private $api_key;
    private $base_url;
    private $timeout;
    private $debug;
    private $max_file_size;
    private $allowed_extensions;
    private $mime_types;
    private $retry_attempts;
    private $retry_delay;
    private $rate_limit_delay;
    
    private $CI;
    private $last_response = null;
    private $last_error = null;
    
    public function __construct() {
        $this->CI =& get_instance();
        
        // Charger la configuration
        $this->CI->config->load('whapi', TRUE);
        $whapi_config = $this->CI->config->item('whapi');
        
        $this->api_key = $whapi_config['api_key'];
        $this->base_url = rtrim($whapi_config['base_url'], '/');
        $this->timeout = $whapi_config['timeout'] ?? 60;
        $this->debug = $whapi_config['debug'] ?? false;
        $this->max_file_size = $whapi_config['max_file_size'] ?? 16 * 1024 * 1024;
        $this->allowed_extensions = $whapi_config['allowed_extensions'] ?? [];
        $this->mime_types = $whapi_config['mime_types'] ?? [];
        $this->retry_attempts = $whapi_config['retry_attempts'] ?? 3;
        $this->retry_delay = $whapi_config['retry_delay'] ?? 2000;
        $this->rate_limit_delay = $whapi_config['rate_limit_delay'] ?? 1000;
    }
    
    // ============================================
    // MÉTHODES PRINCIPALES D'ENVOI
    // ============================================
    
    /**
     * Envoie un message texte
     * @param string $to Numéro du destinataire (format: 2376XXXXXXXX)
     * @param string $message Contenu du message
     * @param array $options Options supplémentaires
     * @return array ['success' => bool, 'data' => array, 'error' => string]
     */
    public function send_text($to, $message, $options = []) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'text',
            'text' => ['body' => $message]
        ];
        
        // Reply to un message spécifique
        if (!empty($options['reply_to'])) {
            $payload['context'] = ['message_id' => $options['reply_to']];
        }
        
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoie une image
     */
    public function send_image($to, $image_url, $caption = null) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'image',
            'image' => ['link' => $image_url]
        ];
        
        if ($caption) {
            $payload['image']['caption'] = $caption;
        }
        
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoie une vidéo
     */
    public function send_video($to, $video_url, $caption = null) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'video',
            'video' => ['link' => $video_url]
        ];
        
        if ($caption) {
            $payload['video']['caption'] = $caption;
        }
        
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoie un audio
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
     * Envoie un document
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
        
        if ($caption) {
            $payload['document']['caption'] = $caption;
        }
        
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoie un sticker
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
     * Envoie une localisation
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
     * Envoie un contact
     */
    public function send_contact($to, $contacts) {
        $payload = [
            'to' => $this->format_number($to),
            'type' => 'contacts',
            'contacts' => $contacts
        ];
        
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoie un message à un GROUPE
     */
    public function send_to_group($group_id, $message_data) {
        $payload = [
            'to' => $group_id,
            'type' => $message_data['type']
        ];
        
        $payload[$message_data['type']] = $message_data['content'];
        
        return $this->request('POST', '/messages/send', $payload);
    }
    
    /**
     * Envoie un message de broadcast (plusieurs destinataires)
     */
    public function send_broadcast($recipients, $message_data) {
        $payload = [
            'recipients' => array_map([$this, 'format_number'], $recipients),
            'type' => $message_data['type']
        ];
        
        $payload[$message_data['type']] = $message_data['content'];
        
        return $this->request('POST', '/messages/broadcast', $payload);
    }
    
    // ============================================
    // MÉTHODES DE GESTION DES GROUPES
    // ============================================
    
    /**
     * Récupère la liste de tous les groupes
     */
    public function get_groups() {
        $response = $this->request('GET', '/groups');
        return $response['success'] ? ($response['data']['groups'] ?? []) : [];
    }
    
    /**
     * Récupère les participants d'un groupe
     */
    public function get_group_participants($group_id) {
        $response = $this->request('GET', '/groups/' . $group_id . '/participants');
        return $response['success'] ? ($response['data']['participants'] ?? []) : [];
    }
    
    // ============================================
    // MÉTHODES DE GESTION DES MESSAGES
    // ============================================
    
    /**
     * Réagit à un message (emoji)
     */
    public function react_to_message($message_id, $emoji) {
        return $this->request('POST', '/messages/' . $message_id . '/react', ['emoji' => $emoji]);
    }
    
    /**
     * Marque un message comme lu
     */
    public function mark_as_read($message_id) {
        return $this->request('POST', '/messages/' . $message_id . '/read');
    }
    
    // ============================================
    // MÉTHODES DE STATUT
    // ============================================
    
    /**
     * Vérifie le statut de la connexion WhatsApp
     */
    public function get_status() {
        $response = $this->request('GET', '/status');
        return $response['success'] ? ($response['data'] ?? []) : [];
    }
    
    /**
     * Vérifie si le compte est connecté
     */
    public function is_connected() {
        $status = $this->get_status();
        return isset($status['connected']) && $status['connected'] === true;
    }
    
    // ============================================
    // MÉTHODES PRIVÉES
    // ============================================
    
    /**
     * Formate un numéro de téléphone
     */
    private function format_number($number) {
        // Supprimer les espaces, tirets, etc.
        $number = preg_replace('/[^0-9+]/', '', $number);
        
        // Si commence par 00, remplacer par +
        if (substr($number, 0, 2) === '00') {
            $number = '+' . substr($number, 2);
        }
        
        // Si pas de +, ajouter le code pays (par défaut 237 pour Cameroun)
        if (substr($number, 0, 1) !== '+') {
            $number = '+237' . ltrim($number, '0');
        }
        
        return $number;
    }
    
    /**
     * Effectue une requête API vers Whapi
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
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            } elseif ($method === 'GET' && $data) {
                curl_setopt($ch, CURLOPT_URL, $this->base_url . $endpoint . '?' . http_build_query($data));
            }
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            $this->last_response = $response;
            
            if ($this->debug) {
                log_message('debug', "Whapi API [$method $endpoint] - HTTP $http_code");
            }
            
            // Succès
            if ($http_code >= 200 && $http_code < 300) {
                $decoded = json_decode($response, true);
                return [
                    'success' => true,
                    'data' => $decoded,
                    'http_code' => $http_code
                ];
            }
            
            // Rate limiting - attendre plus longtemps
            if ($http_code === 429) {
                $wait = $this->rate_limit_delay * $attempt;
                usleep($wait * 1000);
                continue;
            }
            
            // Erreur serveur - réessayer
            if ($http_code >= 500) {
                usleep($this->retry_delay * 1000 * $attempt);
                continue;
            }
            
            // Autres erreurs
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
    
    public function get_last_response() {
        return $this->last_response;
    }
    
    public function get_last_error() {
        return $this->last_error;
    }
}
