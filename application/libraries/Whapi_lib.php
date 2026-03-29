<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whapi_lib {
    
    private $api_key;
    private $base_url;
    private $timeout;
    private $debug;
    
    public function __construct() {
        $CI =& get_instance();
        $CI->load->config('whapi');
        
        $config = $CI->config->item('whapi');
        $this->api_key = $config['api_key'];
        $this->base_url = rtrim($config['base_url'], '/');
        $this->timeout = $config['timeout'] ?? 60;
        $this->debug = $config['debug'] ?? true;
    }
    
    /**
     * Récupère la liste des groupes WhatsApp
     */
    public function get_groupes() {
        $url = $this->base_url . '/groups';
        return $this->requete_api('GET', $url);
    }
    
    /**
     * Test de connexion à l'API
     */
    public function test_connexion() {
        $url = $this->base_url . '/health';
        return $this->requete_api('GET', $url);
    }
    
    /**
     * Envoie un message texte à un groupe
     */
    public function envoyer_message_groupe($groupe_id, $message) {
        $url = $this->base_url . '/messages/text';
        
        $payload = array(
            'to' => $groupe_id,
            'body' => $message
        );
        
        return $this->requete_api('POST', $url, $payload);
    }
    
    /**
     * Envoie un message à plusieurs groupes
     */
    public function envoyer_message_multigroupes($groupes_ids, $message, $delai_ms = 1000) {
        $resultats = array();
        $total = count($groupes_ids);
        $reussis = 0;
        $echoues = 0;
        
        foreach ($groupes_ids as $index => $groupe_id) {
            $resultat = $this->envoyer_message_groupe($groupe_id, $message);
            
            if ($resultat['success']) {
                $reussis++;
            } else {
                $echoues++;
            }
            
            $resultats[] = array(
                'groupe_id' => $groupe_id,
                'statut' => $resultat['success'] ? 'succès' : 'échec',
                'reponse' => $resultat,
                'index' => $index + 1
            );
            
            if ($index < $total - 1) {
                usleep($delai_ms * 1000);
            }
        }
        
        return array(
            'total' => $total,
            'reussis' => $reussis,
            'echoues' => $echoues,
            'details' => $resultats
        );
    }
    
    /**
     * Envoie une image à un groupe
     */
    public function envoyer_image($groupe_id, $image_path, $caption = '') {
        $url = $this->base_url . '/messages/image';
        
        if (filter_var($image_path, FILTER_VALIDATE_URL)) {
            $payload = array(
                'to' => $groupe_id,
                'media' => $image_path,
                'caption' => $caption
            );
        } else {
            $upload_result = $this->uploader_fichier($image_path);
            if (!$upload_result['success']) {
                return $upload_result;
            }
            $payload = array(
                'to' => $groupe_id,
                'media' => $upload_result['url'],
                'caption' => $caption
            );
        }
        
        return $this->requete_api('POST', $url, $payload);
    }
    
    /**
     * Envoie un document (PDF, DOC, etc.)
     */
    public function envoyer_document($groupe_id, $file_path, $filename = '', $caption = '') {
        $url = $this->base_url . '/messages/document';
        
        $upload_result = $this->uploader_fichier($file_path);
        if (!$upload_result['success']) {
            return $upload_result;
        }
        
        $payload = array(
            'to' => $groupe_id,
            'media' => $upload_result['url'],
            'filename' => $filename ?: basename($file_path),
            'caption' => $caption
        );
        
        return $this->requete_api('POST', $url, $payload);
    }
    
    /**
     * Envoie une vidéo à un groupe
     */
    public function envoyer_video($groupe_id, $video_path, $caption = '') {
        $url = $this->base_url . '/messages/video';
        
        $upload_result = $this->uploader_fichier($video_path);
        if (!$upload_result['success']) {
            return $upload_result;
        }
        
        $payload = array(
            'to' => $groupe_id,
            'media' => $upload_result['url'],
            'caption' => $caption
        );
        
        return $this->requete_api('POST', $url, $payload);
    }
    
    /**
     * Envoie un fichier audio à un groupe
     */
    public function envoyer_audio($groupe_id, $audio_path, $caption = '') {
        $url = $this->base_url . '/messages/audio';
        
        $upload_result = $this->uploader_fichier($audio_path);
        if (!$upload_result['success']) {
            return $upload_result;
        }
        
        $payload = array(
            'to' => $groupe_id,
            'media' => $upload_result['url'],
            'caption' => $caption
        );
        
        return $this->requete_api('POST', $url, $payload);
    }
    
    /**
     * Upload un fichier vers Whapi
     */
    private function uploader_fichier($file_path) {
        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'error' => 'Fichier introuvable: ' . $file_path
            );
        }
        
        $url = $this->base_url . '/files';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->api_key
        ]);
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        
        $curl_file = new CURLFile($file_path, $mime_type, basename($file_path));
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $curl_file]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error || $http_code != 200) {
            return array(
                'success' => false,
                'error' => $error ?: 'HTTP ' . $http_code,
                'status_code' => $http_code
            );
        }
        
        $data = json_decode($response, true);
        
        return array(
            'success' => true,
            'url' => $data['url'] ?? null,
            'response' => $data
        );
    }
    
    /**
     * Envoi intelligent : détecte automatiquement le type de fichier
     */
    public function envoyer_fichier($groupe_id, $file_path, $caption = '') {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime_type = mime_content_type($file_path);
        
        // Images
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']) || strpos($mime_type, 'image/') === 0) {
            return $this->envoyer_image($groupe_id, $file_path, $caption);
        }
        
        // Vidéos
        if (in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm']) || strpos($mime_type, 'video/') === 0) {
            return $this->envoyer_video($groupe_id, $file_path, $caption);
        }
        
        // Audio
        if (in_array($extension, ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac']) || strpos($mime_type, 'audio/') === 0) {
            return $this->envoyer_audio($groupe_id, $file_path, $caption);
        }
        
        // Documents
        return $this->envoyer_document($groupe_id, $file_path, basename($file_path), $caption);
    }
    
    /**
     * Envoi à plusieurs groupes avec média
     */
    public function envoyer_fichier_multigroupes($groupes_ids, $file_path, $caption = '', $delai_ms = 1000) {
        $resultats = array();
        $total = count($groupes_ids);
        $reussis = 0;
        
        foreach ($groupes_ids as $index => $groupe_id) {
            $resultat = $this->envoyer_fichier($groupe_id, $file_path, $caption);
            
            if ($resultat['success']) {
                $reussis++;
            }
            
            $resultats[] = array(
                'groupe_id' => $groupe_id,
                'statut' => $resultat['success'] ? 'succès' : 'échec',
                'reponse' => $resultat,
                'index' => $index + 1
            );
            
            if ($index < $total - 1) {
                usleep($delai_ms * 1000);
            }
        }
        
        return array(
            'total' => $total,
            'reussis' => $reussis,
            'echoues' => $total - $reussis,
            'details' => $resultats
        );
    }
    
    /**
     * Requête API générique
     */
    private function requete_api($method, $url, $data = null) {
        $ch = curl_init();
        
        $headers = array(
            'Accept: application/json',
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json'
        );
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $result = array(
            'success' => ($http_code >= 200 && $http_code < 300),
            'status_code' => $http_code,
            'response' => json_decode($response, true),
            'error' => $error
        );
        
        if ($this->debug) {
            log_message('debug', 'Whapi API - Method: ' . $method . ' - URL: ' . $url);
            log_message('debug', 'Whapi API - Status: ' . $http_code);
            if ($error) {
                log_message('error', 'Whapi API - Error: ' . $error);
            }
        }
        
        return $result;
    }
}