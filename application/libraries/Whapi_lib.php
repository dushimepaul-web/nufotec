<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Whapi_lib - Librairie pour WhatsApp API
 * Support: texte, image, vidéo, audio (OGG Opus natif), document
 * Gestion: retry, compression, timeouts adaptatifs
 * CORRECTION: OGG Opus natif pour messages vocaux WhatsApp
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
        
        if ($this->debug) {
            log_message('info', 'Whapi_lib initialisé - FFmpeg disponible: ' . ($this->ffmpeg_available ? 'OUI' : 'NON'));
        }
    }
    
    /**
     * Vérifie si FFmpeg est installé
     */
    private function _check_ffmpeg() {
        $output = array();
        $return_var = 0;
        @exec('ffmpeg -version 2>&1', $output, $return_var);
        $available = ($return_var === 0);
        
        if ($this->debug) {
            log_message('debug', 'FFmpeg check: ' . ($available ? 'disponible' : 'indisponible') . ' (return: ' . $return_var . ')');
        }
        
        return $available;
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
     * Compression vidéo avec FFmpeg
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
     * ✅ CORRECTION: Conversion audio en OGG Opus (format natif WhatsApp)
     * Remplace l'ancienne conversion MP3
     */
    private function convertir_audio_opus($input_path) {
        $temp_dir = sys_get_temp_dir();
        $output_path = $temp_dir . '/whapi_audio_' . uniqid() . '.ogg';
        
        log_message('info', 'Conversion audio en OGG Opus: ' . basename($input_path));
        
        // Conversion OGG Opus 16kbps mono 48kHz (format WhatsApp natif)
        // -application voip = optimisé pour la voix
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
                'Audio converti OGG Opus: %s -> %s (%.1f%% de réduction)',
                $this->format_bytes($original_size),
                $this->format_bytes($new_size),
                (1 - $new_size/$original_size) * 100
            ));
            
            return array(
                'success' => true,
                'path' => $output_path,
                'temp' => true,
                'original_size' => $original_size,
                'final_size' => $new_size
            );
        }
        
        // Échec: nettoyer
        if (file_exists($output_path)) {
            @unlink($output_path);
        }
        
        log_message('error', 'Échec conversion OGG Opus: ' . implode("\n", $output));
        
        return array(
            'success' => false,
            'error' => 'Échec conversion audio en OGG Opus'
        );
    }
    
    /**
     * Helper: Formater taille en bytes
     */
    private function format_bytes($bytes) {
        if ($bytes >= 1048576) return round($bytes/1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes/1024, 2) . ' KB';
        return $bytes . ' B';
    }
    
    /**
     * ✅ CORRECTION: Préparation fichier avec OGG Opus natif
     * Accepte OGG Opus du navigateur sans conversion
     */
    private function preparer_fichier($file_path, $type) {
        $file_size = filesize($file_path);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime = mime_content_type($file_path);
        
        // ✅ AUDIO: OGG Opus natif pour WhatsApp
        if ($type === 'audio') {
            log_message('info', "Préparation audio: $extension | MIME: $mime | Taille: " . $this->format_bytes($file_size));
            
            // Si c'est déjà OGG Opus (depuis le navigateur), envoyer tel quel
            if ($extension === 'ogg' && (strpos($mime, 'ogg') !== false || strpos($mime, 'opus') !== false)) {
                log_message('info', '✅ Audio OGG Opus natif détecté, envoi direct sans conversion');
                return array(
                    'path' => $file_path,
                    'temp' => false,
                    'original_size' => $file_size,
                    'final_size' => $file_size,
                    'is_voice' => true  // Flag pour API
                );
            }
            
            // Si FFmpeg disponible et format différent: convertir en OGG Opus
            if ($this->ffmpeg_available && $extension !== 'ogg') {
                $conversion = $this->convertir_audio_opus($file_path);
                if ($conversion['success']) {
                    $conversion['is_voice'] = true;
                    return $conversion;
                }
                // Si conversion échoue, essayer d'envoyer tel quel
                log_message('warning', 'Conversion OGG échouée, tentative envoi direct');
            }
            
            // Sans FFmpeg ou conversion échouée: accepter tel quel
            return array(
                'path' => $file_path,
                'temp' => false,
                'original_size' => $file_size,
                'final_size' => $file_size,
                'is_voice' => false  // Pas de flag voice si format inconnu
            );
        }
        
        // Pour les autres types: compression seulement si > 16MB
        if ($file_size < $this->compression_threshold) {
            return array(
                'path' => $file_path,
                'temp' => false,
                'original_size' => $file_size,
                'final_size' => $file_size,
                'is_voice' => false
            );
        }
        
        // Si fichier > 100MB, impossible même avec compression
        if ($file_size > $this->max_file_size) {
            if (!$this->ffmpeg_available) {
                return array(
                    'error' => 'Fichier trop gros (>100MB) et FFmpeg non disponible pour compression',
                    'path' => null
                );
            }
        }
        
        // Compression vidéo uniquement
        $temp_dir = sys_get_temp_dir();
        $temp_path = $temp_dir . '/whapi_' . uniqid() . '_' . basename($file_path);
        
        $compressed = false;
        
        if ($type === 'video' && $this->ffmpeg_available) {
            $compressed = $this->compresser_video($file_path, $temp_path);
        }
        
        if ($compressed && filesize($temp_path) < $this->max_file_size) {
            return array(
                'path' => $temp_path,
                'temp' => true,
                'original_size' => $file_size,
                'final_size' => filesize($temp_path),
                'is_voice' => false
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
     * ✅ CORRECTION: Envoi fichier avec support OGG Opus natif
     * Ajoute voice=true pour les messages vocaux OGG Opus
     */
    /**
 * ✅ CORRECTION: Envoi fichier avec support OGG Opus natif
 * Ajoute voice=true pour les messages vocaux OGG Opus
 */
private function envoyer_fichier_direct($groupe_id, $file_path, $type, $caption = '', $filename = '', $force_voice = false) {
    if (!file_exists($file_path)) {
        return array(
            'success' => false,
            'error' => 'Fichier introuvable: ' . $file_path,
            'status_code' => 0
        );
    }
    
    // Préparer fichier (OGG Opus natif pour audio)
    $preparation = $this->preparer_fichier($file_path, $type);
    
    if (isset($preparation['error'])) {
        return array(
            'success' => false,
            'error' => $preparation['error'],
            'status_code' => 413
        );
    }
    
    $file_to_send = $preparation['path'];
    $is_temp = $preparation['temp'];
    $file_size = $preparation['final_size'];
    
    // ✅ CORRECTION: Force voice si paramètre explicite ou détection OGG Opus
    $is_voice = $force_voice || ($preparation['is_voice'] ?? false);
    
    // Timeout adaptatif selon taille
    $timeout = max(120, ceil($file_size / 1048576) * 10);
    
    // Détection endpoint
    $endpoint = $this->_get_endpoint($type);
    
    $url = $this->base_url . $endpoint;
    
    $post_data = array(
        'to' => $groupe_id,
        'caption' => (string)$caption
    );
    
    // ✅ AJOUT: voice=true pour OGG Opus natif (affichage message vocal WhatsApp)
    if ($type === 'audio' && $is_voice) {
        $post_data['voice'] = 'true';
        log_message('info', "Flag 'voice=true' activé pour message vocal natif");
    }
    
    if ($type === 'document') {
        $post_data['filename'] = $filename ? $filename : basename($file_path);
    }
    
    // Log détaillé pour debug audio
    if ($type === 'audio') {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $final_mime = finfo_file($finfo, $file_to_send);
        finfo_close($finfo);
        
        log_message('info', sprintf(
            'Envoi audio à %s | Endpoint: %s | Fichier: %s | MIME: %s | Taille: %s | Voice: %s',
            $groupe_id,
            $endpoint,
            basename($file_to_send),
            $final_mime,
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
        log_message('debug', 'Fichier temporaire nettoyé: ' . $file_to_send);
    }
    
    // Log debug
    if ($this->debug) {
        log_message('debug', 'Whapi envoi - Type: ' . $type . ' | Endpoint: ' . $endpoint);
        log_message('debug', 'Whapi envoi - Taille: ' . $file_size . ' | Tentatives: ' . $tentative);
        log_message('debug', 'Whapi envoi - HTTP: ' . $last_http_code . ' | Success: ' . ($success ? 'OUI' : 'NON'));
        if (!$success && $last_error) {
            log_message('error', 'Whapi erreur: ' . $last_error);
        }
    }
    
    return array(
        'success' => $success,
        'status_code' => $last_http_code,
        'response' => $response_data,
        'error' => $success ? null : $last_error,
        'tentatives' => $tentative,
        'compression' => $is_temp,
        'taille_originale' => $preparation['original_size'],
        'taille_finale' => $preparation['final_size'],
        'is_voice' => $is_voice  // ✅ Retourne info pour debug
    );
}
    /**
     * Détermine le bon endpoint selon le type
     */
    private function _get_endpoint($type) {
        $endpoints = array(
            'image' => '/messages/image',
            'video' => '/messages/video',
            'audio' => '/messages/audio',  // Endpoint audio
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
     * Détection type de fichier avec priorité audio
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
        
        log_message('debug', "Détection fichier: $file_path | MIME: $mime_type | Ext: $extension");
        
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
                'audio' => ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac', 'wma', 'opus', 'weba']
            );
            
            $type = 'document';
            foreach ($types as $t => $exts) {
                if (in_array($extension, $exts)) {
                    $type = $t;
                    break;
                }
            }
        }
        
        // Forçage type audio si extension audio
        if (in_array($extension, ['mp3', 'wav', 'ogg', 'm4a', 'opus', 'weba', 'webm']) && $type !== 'audio') {
            log_message('warning', "Forçage type audio pour extension: $extension");
            $type = 'audio';
        }
        
        log_message('info', "Type détecté: $type pour $file_path");
        
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
                'is_voice' => isset($resultat['is_voice']) ? $resultat['is_voice'] : false,
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

    /**
 * ✅ NOUVELLE MÉTHODE: Envoi audio avec flag voice explicite
 * Pour OGG Opus natif du navigateur
 */
public function envoyer_fichier_audio($groupe_id, $file_path, $caption = '', $is_voice = false) {
    if (!file_exists($file_path)) {
        return array(
            'success' => false,
            'error' => 'Fichier introuvable',
            'status_code' => 0
        );
    }
    
    $mime_type = mime_content_type($file_path);
    $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    // Forcer type audio
    $type = 'audio';
    
    // Si OGG Opus natif et is_voice=true, passer directement
    if ($is_voice && $extension === 'ogg' && (strpos($mime_type, 'ogg') !== false || strpos($mime_type, 'audio/ogg') !== false)) {
        log_message('info', "Envoi OGG Opus natif avec voice=true: $file_path");
        return $this->envoyer_fichier_direct($groupe_id, $file_path, $type, $caption, '', true);
    }
    
    // Sinon traitement normal
    return $this->envoyer_fichier($groupe_id, $file_path, $caption);
}




/**
 * ✅ NOUVEAU: Récupérer tous les groupes avec leurs participants
 * Utile pour synchronisation complète
 */
/**
 * ✅ MODIFIÉ: Récupérer les participants avec sauvegarde automatique en BDD
 */
public function get_group_participants($group_id, $save_to_db = true) {
    $group_id_clean = urlencode($group_id);
    $url = $this->base_url . '/groups/' . $group_id_clean;
    
    $result = $this->requete_api('GET', $url);
    
    if (!$result['success']) {
        return array(
            'success' => false,
            'error' => $result['error'] ?? 'Erreur récupération groupe',
            'participants' => []
        );
    }
    
    $group_data = $result['response'] ?? [];
    $participants_raw = $group_data['participants'] ?? [];
    
    // Formater les participants
    $formatted_participants = [];
    foreach ($participants_raw as $p) {
        $formatted_participants[] = array(
            'phone' => $p['id'],
            'number_formatted' => $this->format_phone_number($p['id']),
            'rank' => $p['rank'] ?? 'member',
            'is_admin' => in_array($p['rank'] ?? '', ['creator', 'admin']),
            'is_creator' => ($p['rank'] ?? '') === 'creator',
            'profile_name' => $p['name'] ?? null
        );
    }
    
    // ✅ SAUVEGARDE AUTOMATIQUE EN BASE DE DONNÉES
    if ($save_to_db && !empty($formatted_participants)) {
        $this->CI->load->model('Participant_model');
        $stats = $this->CI->Participant_model->synchroniser_groupe(
            $group_data['id'] ?? $group_id, 
            $formatted_participants
        );
        
        log_message('info', sprintf(
            'Participants synchronisés - Groupe: %s | Inserted: %d | Updated: %d | Deleted: %d',
            $group_id,
            $stats['inserted'],
            $stats['updated'],
            $stats['deleted']
        ));
    }
    
    return array(
        'success' => true,
        'group_id' => $group_data['id'] ?? $group_id,
        'group_name' => $group_data['name'] ?? 'Groupe sans nom',
        'participants_count' => count($formatted_participants),
        'participants' => $formatted_participants,
        'sync_stats' => $stats ?? null // Retourne les stats de sync
    );
}

/**
 * ✅ MODIFIÉ: Synchroniser tous les groupes avec leurs participants
 */
public function sync_all_groups_with_db() {
    $url = $this->base_url . '/groups?count=100';
    $result = $this->requete_api('GET', $url);
    
    if (!$result['success']) {
        return array('success' => false, 'error' => $result['error']);
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
        'Synchronisation complète - Groupes: %d | Inserted: %d | Updated: %d | Deleted: %d',
        $total_stats['groups'],
        $total_stats['inserted'],
        $total_stats['updated'],
        $total_stats['deleted']
    ));
    
    return array(
        'success' => true,
        'stats' => $total_stats
    );
}
}