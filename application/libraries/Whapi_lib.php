<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Librairie Whapi - API WhatsApp
 * Support: texte, image, vidéo, audio (OGG Opus natif), document
 */
class Whapi_lib {
    
    private $api_key;
    private $base_url;
    private $timeout;
    private $debug;
    private $CI;
    private $max_retries = 3;
    private $retry_delay = 2;
    
    // Limites WhatsApp
    private $max_file_size = 104857600; // 100MB
    private $compression_threshold = 16777216; // 16MB
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('whapi');
        
        $config = $this->CI->config->item('whapi');
        
        $this->api_key = $config['api_key'] ?? '';
        $this->base_url = rtrim($config['base_url'] ?? '', '/');
        $this->timeout = $config['timeout'] ?? 300;
        $this->debug = $config['debug'] ?? true;
        
        // Vérifier FFmpeg
        $this->ffmpeg_available = $this->_check_ffmpeg();
        
        if ($this->debug) {
            log_message('info', 'Whapi_lib initialisé - FFmpeg: ' . ($this->ffmpeg_available ? 'OUI' : 'NON'));
        }
    }
    
    /**
     * Vérifier si FFmpeg est disponible
     */
    private function _check_ffmpeg() {
        $output = [];
        $return_var = 0;
        @exec('ffmpeg -version 2>&1', $output, $return_var);
        return ($return_var === 0);
    }
    
    /**
     * Formater la taille en bytes
     */
    private function format_bytes($bytes) {
        if ($bytes >= 1048576) return round($bytes/1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes/1024, 2) . ' KB';
        return $bytes . ' B';
    }
    
    // ==================== API DE BASE ====================
    
    /**
     * Test de connexion à l'API
     */
    public function test_connexion() {
        $url = $this->base_url . '/health';
        return $this->requete_api('GET', $url);
    }
    
    /**
     * Récupérer la liste des groupes
     */
    public function get_groupes() {
        $url = $this->base_url . '/groups';
        return $this->requete_api('GET', $url);
    }
    
    /**
     * Envoyer un message texte
     */
    public function envoyer_message($destinataire_id, $message) {
        $url = $this->base_url . '/messages/text';
        
        $payload = [
            'to' => $destinataire_id,
            'body' => (string)$message
        ];
        
        return $this->requete_api('POST', $url, $payload);
    }
    
    // ==================== ENVOI DE FICHIERS ====================
    
    /**
     * Envoyer un fichier (détection automatique du type)
     */
    public function envoyer_fichier($destinataire_id, $file_path, $caption = '') {
        if (!file_exists($file_path)) {
            return [
                'success' => false,
                'error' => 'Fichier introuvable',
                'status_code' => 0
            ];
        }
        
        $mime_type = mime_content_type($file_path);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Détection par MIME
        if (strpos($mime_type, 'image/') === 0) {
            $type = 'image';
        } elseif (strpos($mime_type, 'video/') === 0) {
            $type = 'video';
        } elseif (strpos($mime_type, 'audio/') === 0) {
            $type = 'audio';
        } else {
            // Fallback par extension
            $types = [
                'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'],
                'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'],
                'audio' => ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac', 'opus', 'weba'],
                'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'txt']
            ];
            
            $type = 'document';
            foreach ($types as $t => $exts) {
                if (in_array($extension, $exts)) {
                    $type = $t;
                    break;
                }
            }
        }
        
        // Forçage audio si extension audio
        if (in_array($extension, ['mp3', 'wav', 'ogg', 'm4a', 'opus', 'weba']) && $type !== 'audio') {
            $type = 'audio';
        }
        
        return $this->envoyer_fichier_direct($destinataire_id, $file_path, $type, $caption);
    }
    
    /**
     * Envoyer un fichier audio avec flag voice
     */
    public function envoyer_fichier_audio($destinataire_id, $file_path, $caption = '', $is_voice = false) {
        if (!file_exists($file_path)) {
            return [
                'success' => false,
                'error' => 'Fichier introuvable',
                'status_code' => 0
            ];
        }
        
        $mime_type = mime_content_type($file_path);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Si OGG Opus natif et voice=true
        if ($is_voice && $extension === 'ogg' && (strpos($mime_type, 'ogg') !== false)) {
            log_message('info', "Envoi OGG Opus natif avec voice=true à: $destinataire_id");
            return $this->envoyer_fichier_direct($destinataire_id, $file_path, 'audio', $caption, '', true);
        }
        
        // Sinon envoi standard
        return $this->envoyer_fichier($destinataire_id, $file_path, $caption);
    }
    
    /**
     * Envoi direct de fichier avec préparation
     */
    private function envoyer_fichier_direct($destinataire_id, $file_path, $type, $caption = '', $filename = '', $force_voice = false) {
        if (!file_exists($file_path)) {
            return [
                'success' => false,
                'error' => 'Fichier introuvable',
                'status_code' => 0
            ];
        }
        
        // Préparer le fichier (compression/conversion si nécessaire)
        $preparation = $this->preparer_fichier($file_path, $type);
        
        if (isset($preparation['error'])) {
            return [
                'success' => false,
                'error' => $preparation['error'],
                'status_code' => 413
            ];
        }
        
        $file_to_send = $preparation['path'];
        $is_temp = $preparation['temp'];
        $file_size = $preparation['final_size'];
        $is_voice = $force_voice || ($preparation['is_voice'] ?? false);
        
        // Timeout adaptatif
        $timeout = max(120, ceil($file_size / 1048576) * 10);
        
        $endpoint = $this->_get_endpoint($type);
        $url = $this->base_url . $endpoint;
        
        $post_data = [
            'to' => $destinataire_id,
            'caption' => (string)$caption
        ];
        
        // Flag voice pour messages vocaux OGG Opus
        if ($type === 'audio' && $is_voice) {
            $post_data['voice'] = 'true';
        }
        
        if ($type === 'document') {
            $post_data['filename'] = $filename ?: basename($file_path);
        }
        
        // Log pour debug audio
        if ($type === 'audio') {
            log_message('info', sprintf(
                'Envoi audio à %s | Type: %s | Taille: %s | Voice: %s',
                $destinataire_id,
                $type,
                $this->format_bytes($file_size),
                $is_voice ? 'OUI' : 'NON'
            ));
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
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->api_key,
                    'Accept: application/json'
                ],
                CURLOPT_POSTFIELDS => $post_data,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5
            ]);
            
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
        
        // Nettoyer fichier temporaire
        if ($is_temp && file_exists($file_to_send)) {
            @unlink($file_to_send);
        }
        
        return [
            'success' => $success,
            'status_code' => $last_http_code,
            'response' => $response_data,
            'error' => $success ? null : $last_error,
            'tentatives' => $tentative,
            'is_voice' => $is_voice
        ];
    }
    
    /**
     * Préparer un fichier (compression/conversion)
     */
    private function preparer_fichier($file_path, $type) {
        $file_size = filesize($file_path);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime = mime_content_type($file_path);
        
        // Traitement audio OGG Opus
        if ($type === 'audio') {
            log_message('info', "Préparation audio: $extension | MIME: $mime | Taille: " . $this->format_bytes($file_size));
            
            // Si déjà OGG Opus natif
            if ($extension === 'ogg' && (strpos($mime, 'ogg') !== false || strpos($mime, 'opus') !== false)) {
                log_message('info', 'Audio OGG Opus natif - envoi direct');
                return [
                    'path' => $file_path,
                    'temp' => false,
                    'original_size' => $file_size,
                    'final_size' => $file_size,
                    'is_voice' => true
                ];
            }
            
            // Conversion FFmpeg si disponible
            if ($this->ffmpeg_available && $extension !== 'ogg') {
                $conversion = $this->convertir_audio_opus($file_path);
                if ($conversion['success']) {
                    $conversion['is_voice'] = true;
                    return $conversion;
                }
                log_message('warning', 'Conversion OGG échouée, envoi direct');
            }
            
            // Sans conversion
            return [
                'path' => $file_path,
                'temp' => false,
                'original_size' => $file_size,
                'final_size' => $file_size,
                'is_voice' => false
            ];
        }
        
        // Pas de compression si < 16MB
        if ($file_size < $this->compression_threshold) {
            return [
                'path' => $file_path,
                'temp' => false,
                'original_size' => $file_size,
                'final_size' => $file_size,
                'is_voice' => false
            ];
        }
        
        // Compression vidéo si > 16MB
        if ($type === 'video' && $this->ffmpeg_available) {
            return $this->compresser_video($file_path);
        }
        
        // Fichier trop gros
        if ($file_size > $this->max_file_size) {
            return [
                'error' => 'Fichier trop gros (>100MB)',
                'path' => null
            ];
        }
        
        return [
            'path' => $file_path,
            'temp' => false,
            'original_size' => $file_size,
            'final_size' => $file_size,
            'is_voice' => false
        ];
    }
    
    /**
     * Convertir audio en OGG Opus
     */
    private function convertir_audio_opus($input_path) {
        $temp_dir = sys_get_temp_dir();
        $output_path = $temp_dir . '/whapi_audio_' . uniqid() . '.ogg';
        
        // Conversion OGG Opus 16kbps mono 48kHz
        $cmd = sprintf(
            'ffmpeg -i %s -c:a libopus -ar 48000 -ac 1 -b:a 16k -application voip %s 2>&1',
            escapeshellarg($input_path),
            escapeshellarg($output_path)
        );
        
        exec($cmd, $output, $return_var);
        
        $success = ($return_var === 0 && file_exists($output_path) && filesize($output_path) > 0);
        
        if ($success) {
            $original_size = filesize($input_path);
            $new_size = filesize($output_path);
            
            log_message('info', sprintf(
                'Converti OGG Opus: %s -> %s (%.1f%% réduction)',
                $this->format_bytes($original_size),
                $this->format_bytes($new_size),
                (1 - $new_size/$original_size) * 100
            ));
            
            return [
                'success' => true,
                'path' => $output_path,
                'temp' => true,
                'original_size' => $original_size,
                'final_size' => $new_size
            ];
        }
        
        if (file_exists($output_path)) {
            @unlink($output_path);
        }
        
        return [
            'success' => false,
            'error' => 'Échec conversion OGG Opus'
        ];
    }
    
    /**
     * Compresser une vidéo
     */
    private function compresser_video($input_path) {
        $temp_dir = sys_get_temp_dir();
        $output_path = $temp_dir . '/whapi_video_' . uniqid() . '.mp4';
        
        $cmd = sprintf(
            'ffmpeg -i %s -vcodec libx264 -crf 28 -preset fast -acodec aac -b:a 128k -movflags +faststart %s 2>&1',
            escapeshellarg($input_path),
            escapeshellarg($output_path)
        );
        
        exec($cmd, $output, $return_var);
        
        $success = ($return_var === 0 && file_exists($output_path) && filesize($output_path) > 0);
        
        if ($success && filesize($output_path) < $this->max_file_size) {
            return [
                'path' => $output_path,
                'temp' => true,
                'original_size' => filesize($input_path),
                'final_size' => filesize($output_path),
                'is_voice' => false
            ];
        }
        
        if (file_exists($output_path)) {
            @unlink($output_path);
        }
        
        return [
            'path' => $input_path,
            'temp' => false,
            'original_size' => filesize($input_path),
            'final_size' => filesize($input_path),
            'is_voice' => false
        ];
    }
    
    /**
     * Obtenir l'endpoint selon le type
     */
    private function _get_endpoint($type) {
        $endpoints = [
            'image' => '/messages/image',
            'video' => '/messages/video',
            'audio' => '/messages/audio',
            'document' => '/messages/document'
        ];
        
        return $endpoints[$type] ?? '/messages/document';
    }
    
    /**
     * Extraire le message d'erreur
     */
    private function _extract_error($data, $http_code) {
        if (isset($data['error'])) {
            return is_array($data['error']) ? json_encode($data['error']) : $data['error'];
        }
        if (isset($data['message'])) {
            return $data['message'];
        }
        return 'HTTP ' . $http_code;
    }
    
    /**
     * Requête API générique
     */
    private function requete_api($method, $url, $data = null) {
        $ch = curl_init();
        
        $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json'
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'success' => ($http_code >= 200 && $http_code < 300),
            'status_code' => $http_code,
            'response' => json_decode($response, true),
            'error' => $error
        ];
    }
    
    // ==================== GESTION DES PARTICIPANTS ====================
    
    /**
     * Récupérer les participants d'un groupe
     */
    public function get_group_participants($group_id, $save_to_db = true) {
        $group_id_clean = urlencode($group_id);
        $url = $this->base_url . '/groups/' . $group_id_clean;
        
        $result = $this->requete_api('GET', $url);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Erreur récupération groupe',
                'participants' => []
            ];
        }
        
        $group_data = $result['response'] ?? [];
        $participants_raw = $group_data['participants'] ?? [];
        
        // Formater les participants
        $formatted_participants = [];
        foreach ($participants_raw as $p) {
            $formatted_participants[] = [
                'phone' => $p['id'],
                'number_formatted' => $this->format_phone_number($p['id']),
                'rank' => $p['rank'] ?? 'member',
                'is_admin' => in_array($p['rank'] ?? '', ['creator', 'admin']),
                'is_creator' => ($p['rank'] ?? '') === 'creator',
                'profile_name' => $p['name'] ?? null
            ];
        }
        
        // Sauvegarde en BDD
        if ($save_to_db && !empty($formatted_participants)) {
            $this->CI->load->model('Participant_model');
            $stats = $this->CI->Participant_model->synchroniser_groupe(
                $group_data['id'] ?? $group_id, 
                $formatted_participants
            );
        }
        
        return [
            'success' => true,
            'group_id' => $group_data['id'] ?? $group_id,
            'group_name' => $group_data['name'] ?? 'Groupe sans nom',
            'participants_count' => count($formatted_participants),
            'participants' => $formatted_participants,
            'sync_stats' => $stats ?? null
        ];
    }
    
    /**
     * Synchroniser tous les groupes avec la BDD
     */
    public function sync_all_groups_with_db() {
        $url = $this->base_url . '/groups?count=100';
        $result = $this->requete_api('GET', $url);
        
        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error']];
        }
        
        $this->CI->load->model('Participant_model');
        $groups = $result['response'] ?? [];
        $total_stats = ['groups' => 0, 'inserted' => 0, 'updated' => 0, 'deleted' => 0];
        
        foreach ($groups as $group) {
            $participants = [];
            foreach ($group['participants'] ?? [] as $p) {
                $participants[] = [
                    'phone' => $p['id'],
                    'number_formatted' => $this->format_phone_number($p['id']),
                    'rank' => $p['rank'] ?? 'member',
                    'profile_name' => $p['name'] ?? null
                ];
            }
            
            $stats = $this->CI->Participant_model->synchroniser_groupe($group['id'], $participants);
            
            $total_stats['groups']++;
            $total_stats['inserted'] += $stats['inserted'];
            $total_stats['updated'] += $stats['updated'];
            $total_stats['deleted'] += $stats['deleted'];
        }
        
        log_message('info', sprintf(
            'Sync complète - Groupes: %d | Inserted: %d | Updated: %d | Deleted: %d',
            $total_stats['groups'],
            $total_stats['inserted'],
            $total_stats['updated'],
            $total_stats['deleted']
        ));
        
        return [
            'success' => true,
            'stats' => $total_stats
        ];
    }
    
    /**
     * Formater un numéro pour affichage
     */
    private function format_phone_number($phone) {
        $phone = str_replace(['@s.whatsapp.net', '@c.us'], '', $phone);
        
        if (preg_match('/^\d{10,15}$/', $phone)) {
            return '+' . $phone;
        }
        
        return $phone;
    }
}