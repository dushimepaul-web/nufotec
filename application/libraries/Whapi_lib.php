<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Whapi_lib - Librairie robuste pour WhatsApp API
 * Support: texte, image, vidéo, audio, document
 * Gestion: retry, compression, timeouts adaptatifs
 */
class Whapi_lib {
    
    private $api_key;
    private $base_url;
    private $timeout;
    private $debug;
    private $CI;
    private $max_retries = 3;
    private $retry_delay = 2; // secondes
    private $chunk_size = 2097152; // 2MB pour lecture fichier
    
    // Limites WhatsApp
    private $max_file_size = 104857600; // 100MB
    private $compression_threshold = 16777216; // 16MB
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('whapi');
        
        $config = $this->CI->config->item('whapi');
        
        $this->api_key = $config['api_key'];
        $this->base_url = rtrim($config['base_url'], '/');
        $this->timeout = isset($config['timeout']) ? $config['timeout'] : 300;
        $this->debug = isset($config['debug']) ? $config['debug'] : true;
        
        // Vérifier FFmpeg disponible
        $this->ffmpeg_available = $this->_check_ffmpeg();
    }
    
    /**
     * Vérifie si FFmpeg est installé
     */
    private function _check_ffmpeg() {
        $output = array();
        $return_var = 0;
        @exec('ffmpeg -version 2>&1', $output, $return_var);
        return ($return_var === 0);
    }
    
    /**
     * Test connexion API
     */
    public function test_connexion() {
        $url = $this->base_url . '/health';
        return $this->requete_api('GET', $url);
    }
    
    /**
     * Récupère les groupes
     */
    public function get_groupes() {
        $url = $this->base_url . '/groups';
        return $this->requete_api('GET', $url);
    }
    
    /**
     * Envoie message texte simple
     */
    public function envoyer_message($destinataire_id, $message) {
        $url = $this->base_url . '/messages/text';
        
        $payload = array(
            'to' => $destinataire_id,
            'body' => (string)$message
        );
        
        return $this->requete_api('POST', $url, $payload);
    }
    
    /**
     * Envoie message à plusieurs destinataires avec retry
     */
    public function envoyer_message_multi($destinataires_ids, $message, $options = array()) {
        $resultats = array(
            'total' => count($destinataires_ids),
            'reussis' => 0,
            'echoues' => 0,
            'details' => array()
        );
        
        $delai = isset($options['delai_ms']) ? $options['delai_ms'] : 1000;
        $total = count($destinataires_ids);
        
        foreach ($destinataires_ids as $index => $destinataire_id) {
            $tentative = 0;
            $success = false;
            $last_error = null;
            
            // Retry loop
            while ($tentative < $this->max_retries && !$success) {
                $resultat = $this->envoyer_message($destinataire_id, $message);
                
                if ($resultat['success']) {
                    $success = true;
                    $resultats['reussis']++;
                } else {
                    $tentative++;
                    $last_error = $resultat['error'];
                    
                    if ($tentative < $this->max_retries) {
                        sleep($this->retry_delay * $tentative); // Backoff exponentiel
                    }
                }
            }
            
            if (!$success) {
                $resultats['echoues']++;
            }
            
            $resultats['details'][] = array(
                'destinataire_id' => $destinataire_id,
                'statut' => $success ? 'succès' : 'échec',
                'status_code' => isset($resultat['status_code']) ? $resultat['status_code'] : 0,
                'erreur' => $success ? null : $last_error,
                'tentatives' => $tentative,
                'index' => $index + 1
            );
            
            if ($index < $total - 1) {
                usleep($delai * 1000);
            }
        }
        
        return array(
            'success' => $resultats['reussis'] > 0,
            'status_code' => 200,
            'response' => $resultats,
            'error' => null
        );
    }
    
    /**
     * ✅ ROBUSTE: Compression vidéo avec FFmpeg
     */
    private function compresser_video($input_path, $output_path) {
        if (!$this->ffmpeg_available) {
            return false;
        }
        
        // Compression pour réduire à ~10MB ou moins
        $cmd = sprintf(
            'ffmpeg -i %s -vcodec libx264 -crf 28 -preset fast -acodec aac -b:a 128k -movflags +faststart %s 2>&1',
            escapeshellarg($input_path),
            escapeshellarg($output_path)
        );
        
        exec($cmd, $output, $return_var);
        
        return ($return_var === 0 && file_exists($output_path) && filesize($output_path) > 0);
    }
    
    /**
     * ✅ ROBUSTE: Compression audio
     */
    private function compresser_audio($input_path, $output_path) {
        if (!$this->ffmpeg_available) {
            return false;
        }
        
        // Compression MP3 128kbps
        $cmd = sprintf(
            'ffmpeg -i %s -codec:a libmp3lame -q:a 4 %s 2>&1',
            escapeshellarg($input_path),
            escapeshellarg($output_path)
        );
        
        exec($cmd, $output, $return_var);
        
        return ($return_var === 0 && file_exists($output_path) && filesize($output_path) > 0);
    }
    
    /**
     * ✅ ROBUSTE: Préparation fichier avec compression si nécessaire
     */
    private function preparer_fichier($file_path, $type) {
        $file_size = filesize($file_path);
        
        // Si fichier < 16MB, pas besoin de compression
        if ($file_size < $this->compression_threshold) {
            return array(
                'path' => $file_path,
                'temp' => false,
                'original_size' => $file_size,
                'final_size' => $file_size
            );
        }
        
        // Si fichier > 100MB, impossible même avec compression
        if ($file_size > $this->max_file_size) {
            // Essayer compression quand même
            if (!$this->ffmpeg_available) {
                return array(
                    'error' => 'Fichier trop gros (>100MB) et FFmpeg non disponible pour compression',
                    'path' => null
                );
            }
        }
        
        // Compression
        $temp_dir = sys_get_temp_dir();
        $temp_path = $temp_dir . '/whapi_' . uniqid() . '_' . basename($file_path);
        
        $compressed = false;
        
        if ($type === 'video' && $this->ffmpeg_available) {
            $compressed = $this->compresser_video($file_path, $temp_path);
        } elseif ($type === 'audio' && $this->ffmpeg_available) {
            $compressed = $this->compresser_audio($file_path, $temp_path);
        }
        
        if ($compressed && filesize($temp_path) < $this->max_file_size) {
            return array(
                'path' => $temp_path,
                'temp' => true,
                'original_size' => $file_size,
                'final_size' => filesize($temp_path)
            );
        }
        
        // Si compression échoue ou insuffisante
        if (file_exists($temp_path)) {
            @unlink($temp_path);
        }
        
        return array(
            'error' => 'Fichier trop gros même après compression',
            'path' => null
        );
    }
    
    /**
     * ✅ ROBUSTE: Envoi fichier avec retry et gestion erreurs
     */
    private function envoyer_fichier_direct($groupe_id, $file_path, $type, $caption = '', $filename = '') {
        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'error' => 'Fichier introuvable: ' . $file_path,
                'status_code' => 0
            );
        }
        
        // Préparer fichier (compression si nécessaire)
        $preparation = $this->preparer_fichier($file_path, $type);
        
        if (isset($preparation['error'])) {
            return array(
                'success' => false,
                'error' => $preparation['error'],
                'status_code' => 413 // Payload Too Large
            );
        }
        
        $file_to_send = $preparation['path'];
        $is_temp = $preparation['temp'];
        $file_size = $preparation['final_size'];
        
        // Timeout adaptatif selon taille
        $timeout = max(120, ceil($file_size / 1048576) * 10); // 10s par MB minimum 120s
        
        // Détection endpoint
        $endpoint = $this->_get_endpoint($type);
        
        $url = $this->base_url . $endpoint;
        
        $post_data = array(
            'to' => $groupe_id,
            'caption' => (string)$caption
        );
        
        if ($type === 'document') {
            $post_data['filename'] = $filename ? $filename : basename($file_path);
        }
        
        // Envoi avec retry
        $tentative = 0;
        $success = false;
        $last_error = null;
        $last_http_code = 0;
        $response_data = null;
        
        while ($tentative < $this->max_retries && !$success) {
            $ch = curl_init();
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file_to_send);
            finfo_close($finfo);
            
            $cfile = new CURLFile($file_to_send, $mime_type, basename($file_to_send));
            $post_data['media'] = $cfile;
            
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $this->api_key,
                    'Accept: application/json'
                ),
                CURLOPT_POSTFIELDS => $post_data,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5
            ));
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            $curl_errno = curl_errno($ch);
            curl_close($ch);
            
            if ($curl_error) {
                $last_error = 'cURL (' . $curl_errno . '): ' . $curl_error;
                $tentative++;
                
                if ($tentative < $this->max_retries) {
                    sleep($this->retry_delay * $tentative);
                }
                continue;
            }
            
            $response_data = json_decode($response, true);
            $last_http_code = $http_code;
            
            if ($http_code >= 200 && $http_code < 300) {
                $success = true;
            } else {
                $last_error = $this->_extract_error($response_data, $http_code);
                $tentative++;
                
                if ($tentative < $this->max_retries) {
                    sleep($this->retry_delay * $tentative);
                }
            }
        }
        
        // Nettoyage fichier temporaire
        if ($is_temp && file_exists($file_to_send)) {
            @unlink($file_to_send);
        }
        
        // Log debug
        if ($this->debug) {
            log_message('debug', 'Whapi envoi - Type: ' . $type . ' | Endpoint: ' . $endpoint);
            log_message('debug', 'Whapi envoi - Taille: ' . $file_size . ' | Tentatives: ' . $tentative);
            log_message('debug', 'Whapi envoi - HTTP: ' . $last_http_code . ' | Success: ' . ($success ? 'OUI' : 'NON'));
        }
        
        return array(
            'success' => $success,
            'status_code' => $last_http_code,
            'response' => $response_data,
            'error' => $success ? null : $last_error,
            'tentatives' => $tentative,
            'compression' => $is_temp,
            'taille_originale' => $preparation['original_size'],
            'taille_finale' => $preparation['final_size']
        );
    }
    
    /**
     * Détermine le bon endpoint selon le type
     */
    private function _get_endpoint($type) {
        $endpoints = array(
            'image' => '/messages/image',
            'video' => '/messages/video',
            'audio' => '/messages/audio',
            'document' => '/messages/document'
        );
        
        return isset($endpoints[$type]) ? $endpoints[$type] : '/messages/media';
    }
    
    /**
     * Extrait le message d'erreur de la réponse
     */
    private function _extract_error($data, $http_code) {
        if (isset($data['error'])) {
            return is_array($data['error']) ? json_encode($data['error']) : $data['error'];
        }
        if (isset($data['message'])) {
            return $data['message'];
        }
        if (isset($data['details'])) {
            return is_array($data['details']) ? json_encode($data['details']) : $data['details'];
        }
        return 'HTTP ' . $http_code;
    }
    
    /**
     * Détecte le type de fichier
     */
    public function envoyer_fichier($groupe_id, $file_path, $caption = '') {
        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'error' => 'Fichier introuvable',
                'status_code' => 0
            );
        }
        
        $mime_type = mime_content_type($file_path);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Détection par MIME prioritaire
        if (strpos($mime_type, 'image/') === 0) {
            $type = 'image';
        } elseif (strpos($mime_type, 'video/') === 0) {
            $type = 'video';
        } elseif (strpos($mime_type, 'audio/') === 0) {
            $type = 'audio';
        } else {
            // Fallback extension
            $types = array(
                'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
                'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', '3gp', 'm4v'],
                'audio' => ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac', 'wma', 'opus']
            );
            
            $type = 'document';
            foreach ($types as $t => $exts) {
                if (in_array($extension, $exts)) {
                    $type = $t;
                    break;
                }
            }
        }
        
        return $this->envoyer_fichier_direct($groupe_id, $file_path, $type, $caption);
    }
    
    /**
     * Envoie fichier à plusieurs groupes
     */
    public function envoyer_fichier_multigroupes($groupes_ids, $file_path, $caption = '', $delai_ms = 1000) {
        $resultats = array(
            'total' => count($groupes_ids),
            'reussis' => 0,
            'echoues' => 0,
            'details' => array()
        );
        
        $total = count($groupes_ids);
        
        foreach ($groupes_ids as $index => $groupe_id) {
            $resultat = $this->envoyer_fichier($groupe_id, $file_path, $caption);
            
            if ($resultat['success']) {
                $resultats['reussis']++;
            } else {
                $resultats['echoues']++;
            }
            
            $resultats['details'][] = array(
                'destinataire_id' => $groupe_id,
                'statut' => $resultat['success'] ? 'succès' : 'échec',
                'status_code' => isset($resultat['status_code']) ? $resultat['status_code'] : 0,
                'erreur' => isset($resultat['error']) ? $resultat['error'] : null,
                'tentatives' => isset($resultat['tentatives']) ? $resultat['tentatives'] : 1,
                'compression' => isset($resultat['compression']) ? $resultat['compression'] : false,
                'index' => $index + 1
            );
            
            if ($index < $total - 1) {
                usleep($delai_ms * 1000);
            }
        }
        
        return array(
            'success' => $resultats['reussis'] > 0,
            'status_code' => 200,
            'response' => $resultats,
            'error' => null
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
            log_message('debug', 'Whapi API - ' . $method . ' ' . $url . ' => ' . $http_code);
        }
        
        return $result;
    }
}