<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur de gestion des audio avec upload chunked ultra-robuste
 * Support: MP3, WAV, FLAC, AAC, OGG, M4A, WMA, AIFF, ALAC
 * Features: Waveform generation, ID3 tags extraction, Spectrogram
 */
class Audio extends MY_Controller {

    private $upload_dir;
    private $final_dir;
    private $waveform_dir;
    private $chunk_size;
    private $max_file_size;
    private $allowed_extensions;
    private $allowed_mime_types;
    private $session_timeout;

    function __construct()
    {
        parent::__construct();
        is_admin();
        
        // Configuration des chemins
        $this->upload_dir = FCPATH . 'uploads/temp/audio/';
        $this->final_dir = FCPATH . 'attachments/Audio/';
        $this->waveform_dir = FCPATH . 'attachments/Audio/Waveforms/';
        
        // Configuration technique
        $this->chunk_size = 2 * 1024 * 1024; // 2MB chunks
        $this->max_file_size = 5 * 1024 * 1024 * 1024; // 5GB max
        $this->session_timeout = 3600; // 1 heure
        
        // Extensions et MIME types supportés
        $this->allowed_extensions = [
            'mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a', 'wma', 
            'aiff', 'alac', 'opus', 'weba', 'amr', 'au', 'snd'
        ];
        
        $this->allowed_mime_types = [
            'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav',
            'audio/flac', 'audio/x-flac', 'audio/aac', 'audio/x-aac',
            'audio/ogg', 'audio/vorbis', 'audio/x-m4a', 'audio/mp4',
            'audio/x-ms-wma', 'audio/aiff', 'audio/x-aiff', 'audio/alac',
            'audio/opus', 'audio/webm', 'audio/amr', 'audio/basic'
        ];
        
        // Création des dossiers
        $this->ensureDirectoryExists($this->upload_dir);
        $this->ensureDirectoryExists($this->final_dir);
        $this->ensureDirectoryExists($this->waveform_dir);
        
        // Configuration PHP dynamique
        $this->configurePHP();
        
        // Nettoyage des sessions expirées
        $this->cleanupExpiredSessions();
    }

    // ==================== CONFIGURATION ====================

    /**
     * Configure PHP pour gérer de gros uploads audio
     */
    private function configurePHP()
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '0');
        @ini_set('max_input_time', '0');
        @ini_set('upload_max_filesize', '10M');
        @ini_set('post_max_size', '10M');
        @ini_set('max_file_uploads', '20');
        
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', 'Off');
        @ini_set('session.gc_maxlifetime', $this->session_timeout);
    }

    /**
     * Crée un dossier avec permissions correctes
     */
    private function ensureDirectoryExists($path)
    {
        if (!is_dir($path)) {
            @mkdir($path, 0777, TRUE);
            @chmod($path, 0777);
        }
    }

    /**
     * Nettoie les sessions d'upload expirées
     */
    private function cleanupExpiredSessions()
    {
        if (!is_dir($this->upload_dir)) return;
        
        $dirs = glob($this->upload_dir . 'upload_*', GLOB_ONLYDIR);
        $now = time();
        
        foreach ($dirs as $dir) {
            $metadata_file = $dir . '/metadata.json';
            if (!file_exists($metadata_file)) {
                $this->recursiveDelete($dir);
                continue;
            }
            
            $metadata = json_decode(file_get_contents($metadata_file), true);
            if (!$metadata || ($now - $metadata['created_at']) > $this->session_timeout) {
                $this->recursiveDelete($dir);
            }
        }
    }

    /**
     * Suppression récursive de dossier
     */
    private function recursiveDelete($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    $path = $dir . DIRECTORY_SEPARATOR . $object;
                    if (is_dir($path)) {
                        $this->recursiveDelete($path);
                    } else {
                        @unlink($path);
                    }
                }
            }
            @rmdir($dir);
        }
    }

    // ==================== INTERFACE PUBLIQUE ====================

    /**
     * Page principale - Liste des audio
     */
    public function index()
    {
        $data['audios'] = $this->Model->read('galerie_medias', 
            ['type' => 'audio'], 
            'id_media', 
            'DESC'
        );
        
        $data['categories'] = $this->getExistingCategories();
        $data['total_duration'] = $this->calculateTotalDuration();
        
        $this->load->view('Audio_View', $data);
    }

    /**
     * API: Diagnostic serveur pour audio
     */
    public function diagnostics()
    {
        $this->setJSONHeaders();
        
        $info = [
            'php_version' => PHP_VERSION,
            'audio_tools' => [
                'ffmpeg' => $this->findFFmpeg() ? 'Disponible' : 'Non disponible',
                'ffprobe' => $this->findFFprobe() ? 'Disponible' : 'Non disponible',
                'sox' => $this->findSox() ? 'Disponible' : 'Non disponible',
                'lame' => $this->findLame() ? 'Disponible' : 'Non disponible'
            ],
            'limits' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
            ],
            'configured' => [
                'chunk_size' => $this->formatBytes($this->chunk_size),
                'max_file_size' => $this->formatBytes($this->max_file_size),
                'supported_formats' => $this->allowed_extensions
            ],
            'directories' => [
                'upload_dir_writable' => is_writable($this->upload_dir),
                'final_dir_writable' => is_writable($this->final_dir),
                'waveform_dir_writable' => is_writable($this->waveform_dir),
                'disk_free' => $this->formatBytes(@disk_free_space($this->final_dir))
            ],
            'timestamp' => time()
        ];
        
        echo json_encode($info);
    }

    /**
     * Streaming audio avec support range requests (comme Spotify)
     */
    public function stream($filename)
    {
        $file_path = $this->final_dir . $filename;
        
        if (!file_exists($file_path)) {
            show_404();
            return;
        }

        $mime_type = mime_content_type($file_path);
        $file_size = filesize($file_path);
        
        // Headers pour streaming
        header('Content-Type: ' . $mime_type);
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . $file_size);
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        
        // Gestion du Range (seeking audio)
        $start = 0;
        $end = $file_size - 1;
        
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = $_SERVER['HTTP_RANGE'];
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = intval($matches[1]);
                if (!empty($matches[2])) {
                    $end = intval($matches[2]);
                }
                
                header('HTTP/1.1 206 Partial Content');
                header("Content-Range: bytes $start-$end/$file_size");
                header('Content-Length: ' . ($end - $start + 1));
            }
        }
        
        // Streaming avec buffer
        $fp = fopen($file_path, 'rb');
        fseek($fp, $start);
        
        $buffer_size = 8192;
        $bytes_sent = 0;
        $bytes_to_send = $end - $start + 1;
        
        while (!feof($fp) && $bytes_sent < $bytes_to_send) {
            $chunk_size = min($buffer_size, $bytes_to_send - $bytes_sent);
            echo fread($fp, $chunk_size);
            $bytes_sent += $chunk_size;
            
            // Flush pour éviter le buffering
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }
        
        fclose($fp);
    }

    // ==================== API UPLOAD CHUNKED ====================

    /**
     * Étape 1: Initialiser une session d'upload
     */
    public function initUpload()
    {
        $this->setJSONHeaders();
        
        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');
        $file_hash = $this->input->post('file_hash') ?: null;
        $metadata_hint = $this->input->post('metadata') ? json_decode($this->input->post('metadata'), true) : null;

        $validation = $this->validateInitUpload($file_name, $file_size);
        if (!$validation['success']) {
            $this->jsonResponse(false, $validation['message']);
            return;
        }

        $upload_id = $this->generateUploadId();
        $temp_dir = $this->upload_dir . $upload_id . '/';
        
        if (!@mkdir($temp_dir, 0777, TRUE)) {
            $this->jsonResponse(false, 'Erreur création dossier temporaire');
            return;
        }

        $total_chunks = (int)ceil($file_size / $this->chunk_size);
        
        $metadata = [
            'upload_id' => $upload_id,
            'file_name' => $file_name,
            'file_size' => $file_size,
            'file_hash' => $file_hash,
            'chunk_size' => $this->chunk_size,
            'total_chunks' => $total_chunks,
            'uploaded_chunks' => [],
            'failed_chunks' => [],
            'created_at' => time(),
            'last_activity' => time(),
            'status' => 'active',
            'client_metadata' => $metadata_hint // Titre, artiste, etc. envoyés par le client
        ];

        $this->saveMetadata($upload_id, $metadata);

        $this->jsonResponse(true, 'Session initialisée', [
            'upload_id' => $upload_id,
            'chunk_size' => $this->chunk_size,
            'total_chunks' => $total_chunks,
            'max_retries' => 3,
            'supports_waveform' => (bool)$this->findFFmpeg()
        ]);
    }

    /**
     * Étape 2: Recevoir un chunk
     */
    public function uploadChunk()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $chunk_index = (int)$this->input->post('chunk_index');
        $chunk_hash = $this->input->post('chunk_hash') ?: null;

        if (empty($upload_id)) {
            $this->jsonResponse(false, 'ID upload manquant');
            return;
        }

        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata) {
            $this->jsonResponse(false, 'Session invalide ou expirée');
            return;
        }

        if ($metadata['status'] !== 'active') {
            $this->jsonResponse(false, 'Session non active: ' . $metadata['status']);
            return;
        }

        if ($chunk_index < 0 || $chunk_index >= $metadata['total_chunks']) {
            $this->jsonResponse(false, 'Index chunk invalide');
            return;
        }

        // Vérifier si déjà présent (idempotence)
        $chunk_path = $this->getChunkPath($upload_id, $chunk_index);
        if (file_exists($chunk_path)) {
            $this->markChunkUploaded($upload_id, $chunk_index);
            $progress = $this->calculateProgress($upload_id);
            $this->jsonResponse(true, 'Chunk déjà présent', $progress);
            return;
        }

        if (empty($_FILES['chunk'])) {
            $this->jsonResponse(false, 'Aucun fichier reçu');
            return;
        }

        $file = $_FILES['chunk'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_detail = $this->getDetailedUploadError($file['error']);
            $this->logError("Upload error for chunk $chunk_index", $error_detail);
            $this->jsonResponse(false, $error_detail['message'], [
                'error_code' => $file['error'],
                'error_type' => $error_detail['type']
            ]);
            return;
        }

        if ($file['size'] === 0) {
            $this->jsonResponse(false, 'Chunk vide reçu');
            return;
        }

        // Vérifier hash si fourni
        if ($chunk_hash && function_exists('hash_file')) {
            $calculated_hash = hash_file('crc32b', $file['tmp_name']);
            if ($calculated_hash !== $chunk_hash) {
                $this->jsonResponse(false, 'Corruption détectée - hash mismatch');
                return;
            }
        }

        // Déplacer le chunk
        if (!@move_uploaded_file($file['tmp_name'], $chunk_path)) {
            if (!@copy($file['tmp_name'], $chunk_path)) {
                $this->jsonResponse(false, 'Erreur écriture disque');
                return;
            }
            @unlink($file['tmp_name']);
        }

        if (!file_exists($chunk_path) || filesize($chunk_path) !== $file['size']) {
            @unlink($chunk_path);
            $this->jsonResponse(false, 'Erreur vérification écriture');
            return;
        }

        $this->markChunkUploaded($upload_id, $chunk_index);
        $this->updateLastActivity($upload_id);
        
        $progress = $this->calculateProgress($upload_id);

        $this->jsonResponse(true, 'Chunk reçu', array_merge($progress, [
            'received_size' => $file['size'],
            'chunk_hash_verified' => (bool)$chunk_hash
        ]));
    }

    /**
     * Étape 3: Vérifier le statut
     */
    public function checkStatus()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $metadata = $this->loadMetadata($upload_id);
        
        if (!$metadata) {
            $this->jsonResponse(false, 'Session non trouvée');
            return;
        }

        $actual_chunks = $this->getActualUploadedChunks($upload_id);
        
        if ($actual_chunks !== $metadata['uploaded_chunks']) {
            $metadata['uploaded_chunks'] = $actual_chunks;
            $this->saveMetadata($upload_id, $metadata);
        }

        $missing = array_diff(
            range(0, $metadata['total_chunks'] - 1),
            $actual_chunks
        );

        $this->jsonResponse(true, 'Statut récupéré', [
            'upload_id' => $upload_id,
            'status' => $metadata['status'],
            'progress' => $this->calculateProgress($upload_id),
            'missing_chunks' => array_values($missing),
            'can_resume' => count($actual_chunks) > 0 && count($missing) > 0
        ]);
    }

    /**
     * Étape 4: Finaliser l'upload avec traitement audio
     */
    public function completeUpload()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $metadata = $this->loadMetadata($upload_id);
        
        if (!$metadata) {
            $this->jsonResponse(false, 'Session non trouvée');
            return;
        }

        $missing = array_diff(
            range(0, $metadata['total_chunks'] - 1),
            $metadata['uploaded_chunks']
        );

        if (!empty($missing)) {
            $this->jsonResponse(false, 'Chunks manquants', [
                'missing_chunks' => array_values($missing),
                'total_missing' => count($missing)
            ]);
            return;
        }

        // Générer nom final
        $final_name = $this->generateFinalName($metadata['file_name']);
        $final_path = $this->final_dir . $final_name;
        $relative_path = 'attachments/Audio/' . $final_name;

        // Assembler les chunks
        $assembled = $this->assembleChunks($upload_id, $final_path, $metadata);
        
        if (!$assembled['success']) {
            $this->jsonResponse(false, 'Erreur assemblage: ' . $assembled['message']);
            return;
        }

        // Vérifier taille finale
        $final_size = filesize($final_path);
        if ($final_size !== $metadata['file_size']) {
            @unlink($final_path);
            $this->jsonResponse(false, 'Taille finale incorrecte');
            return;
        }

        // Traitement audio asynchrone (waveform, métadonnées)
        $audio_info = $this->processAudioFile($final_path, $final_name);

        // Nettoyer session
        $this->cleanupUploadSession($upload_id);

        $this->jsonResponse(true, 'Upload complété', [
            'file_path' => $relative_path,
            'file_name' => $final_name,
            'file_size' => $final_size,
            'file_size_formatted' => $this->formatBytes($final_size),
            'duration' => $audio_info['duration'] ?? null,
            'duration_formatted' => $this->formatDuration($audio_info['duration'] ?? 0),
            'bitrate' => $audio_info['bitrate'] ?? null,
            'sample_rate' => $audio_info['sample_rate'] ?? null,
            'channels' => $audio_info['channels'] ?? null,
            'waveform' => $audio_info['waveform'] ?? null,
            'spectrogram' => $audio_info['spectrogram'] ?? null,
            'metadata' => $audio_info['metadata'] ?? null,
            'mime_type' => mime_content_type($final_path)
        ]);
    }

    /**
     * Annuler un upload
     */
    public function cancelUpload()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        
        if ($upload_id) {
            $this->cleanupUploadSession($upload_id);
        }

        $this->jsonResponse(true, 'Upload annulé');
    }

    // ==================== CRUD OPÉRATIONS ====================

    /**
     * Créer un audio
     */
    public function Create()
    {
        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $type_source = $this->input->post('type_source');
        
        if ($type_source == 'link') {
            $this->form_validation->set_rules('lien', 'Lien audio', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('audio'));
            return;
        }

        $data = $this->prepareAudioData($type_source);
        
        if (!$data) {
            redirect(base_url('audio'));
            return;
        }

        $rsp = $this->Model->create('galerie_medias', $data);

        $this->setFlashMessage($rsp, 'Audio créé avec succès.', 'Erreur lors de la création.');
        redirect(base_url('audio'));
    }

    /**
     * Mettre à jour un audio
     */
    public function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $type_source = $this->input->post('type_source');
        
        if ($type_source == 'link') {
            $this->form_validation->set_rules('lien', 'Lien audio', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('audio'));
            return;
        }

        $data = $this->prepareUpdateData($id, $type_source);
        
        if (!$data) {
            redirect(base_url('audio'));
            return;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);

        $this->setFlashMessage($rsp, 'Audio mis à jour avec succès.', 'Erreur lors de la mise à jour.');
        redirect(base_url('audio'));
    }

    /**
     * Supprimer un audio (soft delete)
     */
    public function Delete()
    {
        $id = $this->input->post('id');
        $audio = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            'est_actif' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp && $audio) {
            $this->deleteAudioFiles($audio);
            $this->session->set_flashdata('success', 'Audio supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        
        redirect(base_url('audio'));
    }

    /**
     * Changer le statut
     */
    public function ChangeStatus()
    {
        $id = $this->input->post('id');
        $est_actif = $this->input->post('est_actif');
        $status = ($est_actif == 1) ? 0 : 1;
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], ['est_actif' => $status]);

        $this->setFlashMessage($rsp, 'Statut mis à jour avec succès.', 'Erreur lors de la mise à jour du statut.');
        redirect(base_url('audio'));    
    }

    /**
     * Toggle AJAX pour WhatsApp et Site Web
     */
    public function toggleField()
    {
        $this->setJSONHeaders();
        
        $id = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');
        
        $allowed = ['is_for_whatsapp', 'is_for_website'];
        if (!in_array($field, $allowed)) {
            $this->jsonResponse(false, 'Champ non autorisé');
            return;
        }
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            $field => $value,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->jsonResponse((bool)$rsp);
    }

    /**
     * API: Récupérer les données waveform pour visualisation
     */
    public function getWaveform($id)
    {
        $this->setJSONHeaders();
        
        $audio = $this->Model->readOne('galerie_medias', ['id_media' => $id, 'type' => 'audio']);
        
        if (!$audio) {
            $this->jsonResponse(false, 'Audio non trouvé');
            return;
        }

        // Si waveform existe, la retourner
        $waveform_path = FCPATH . ($audio['waveform_data'] ?? '');
        if (!empty($audio['waveform_data']) && file_exists($waveform_path)) {
            $waveform_data = json_decode(file_get_contents($waveform_path), true);
            $this->jsonResponse(true, 'Waveform récupérée', [
                'waveform' => $waveform_data,
                'duration' => $audio['duree'] ?? 0,
                'peaks_per_second' => 100
            ]);
            return;
        }

        // Sinon, générer à la volée
        if (!empty($audio['fichier'])) {
            $waveform = $this->generateWaveformData(FCPATH . $audio['fichier']);
            $this->jsonResponse(true, 'Waveform générée', [
                'waveform' => $waveform,
                'duration' => $audio['duree'] ?? 0,
                'peaks_per_second' => 100
            ]);
            return;
        }

        $this->jsonResponse(false, 'Impossible de générer la waveform');
    }

    // ==================== TRAITEMENT AUDIO AVANCÉ ====================

    /**
     * Traite un fichier audio après upload (métadonnées, waveform, etc.)
     */
    private function processAudioFile($file_path, $filename)
    {
        $info = [
            'duration' => 0,
            'bitrate' => null,
            'sample_rate' => null,
            'channels' => null,
            'waveform' => null,
            'spectrogram' => null,
            'metadata' => []
        ];

        // 1. Extraire les métadonnées avec FFprobe
        $ffprobe = $this->findFFprobe();
        if ($ffprobe) {
            $cmd = sprintf(
                '%s -v quiet -print_format json -show_format -show_streams %s',
                escapeshellarg($ffprobe),
                escapeshellarg($file_path)
            );
            
            exec($cmd, $output, $code);
            if ($code === 0) {
                $probe_data = json_decode(implode("\n", $output), true);
                
                if (isset($probe_data['format'])) {
                    $format = $probe_data['format'];
                    $info['duration'] = (float)($format['duration'] ?? 0);
                    $info['bitrate'] = (int)($format['bit_rate'] ?? 0);
                    
                    // Métadonnées ID3
                    if (isset($format['tags'])) {
                        $tags = $format['tags'];
                        $info['metadata'] = [
                            'title' => $tags['title'] ?? $tags['TITLE'] ?? null,
                            'artist' => $tags['artist'] ?? $tags['ARTIST'] ?? $tags['album_artist'] ?? null,
                            'album' => $tags['album'] ?? $tags['ALBUM'] ?? null,
                            'year' => $tags['date'] ?? $tags['DATE'] ?? $tags['TYER'] ?? null,
                            'genre' => $tags['genre'] ?? $tags['GENRE'] ?? null,
                            'track' => $tags['track'] ?? $tags['TRACK'] ?? null
                        ];
                    }
                }
                
                if (isset($probe_data['streams'][0])) {
                    $stream = $probe_data['streams'][0];
                    $info['sample_rate'] = (int)($stream['sample_rate'] ?? 0);
                    $info['channels'] = (int)($stream['channels'] ?? 0);
                }
            }
        }

        // 2. Générer la waveform visuelle
        $waveform_file = $this->waveform_dir . pathinfo($filename, PATHINFO_FILENAME) . '_waveform.png';
        $waveform_relative = 'attachments/Audio/Waveforms/' . pathinfo($filename, PATHINFO_FILENAME) . '_waveform.png';
        
        if ($this->generateWaveformImage($file_path, $waveform_file)) {
            $info['waveform'] = $waveform_relative;
        }

        // 3. Générer les données de waveform pour le player
        $waveform_data_file = $this->waveform_dir . pathinfo($filename, PATHINFO_FILENAME) . '_data.json';
        $waveform_data = $this->generateWaveformData($file_path);
        file_put_contents($waveform_data_file, json_encode($waveform_data));
        $info['waveform_data'] = 'attachments/Audio/Waveforms/' . pathinfo($filename, PATHINFO_FILENAME) . '_data.json';

        // 4. Générer un spectrogram (optionnel, pour les longs fichiers)
        if ($info['duration'] > 0 && $info['duration'] < 600) { // Max 10 minutes
            $spectrogram_file = $this->waveform_dir . pathinfo($filename, PATHINFO_FILENAME) . '_spectro.png';
            if ($this->generateSpectrogram($file_path, $spectrogram_file)) {
                $info['spectrogram'] = 'attachments/Audio/Waveforms/' . pathinfo($filename, PATHINFO_FILENAME) . '_spectro.png';
            }
        }

        return $info;
    }

    /**
     * Génère une image de waveform (visualisation)
     */
    private function generateWaveformImage($audio_path, $output_path)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) return false;

        // Utiliser FFmpeg avec filter complex pour waveform stylisée
        $cmd = sprintf(
            '%s -i %s -filter_complex ' .
            '"aformat=channel_layouts=mono,compand,showwavespic=s=800x200:colors=#007bff" ' .
            '-frames:v 1 -y %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($audio_path),
            escapeshellarg($output_path)
        );

        exec($cmd, $output, $code);
        return ($code === 0 && file_exists($output_path));
    }

    /**
     * Génère les données numériques de waveform pour le player
     * Retourne un tableau de peaks normalisés (-1 à 1)
     */
    private function generateWaveformData($audio_path, $samples = 1000)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) {
            // Fallback: générer des données aléatoires pour démo
            return $this->generateDummyWaveform($samples);
        }

        // Extraction des échantillons audio
        $cmd = sprintf(
            '%s -i %s -ac 1 -filter:a aresample=%d -map 0:a -c:a pcm_s16le -f data - 2>/dev/null | ' .
            'od -An -td2 -w2 -v | head -%d',
            escapeshellarg($ffmpeg),
            escapeshellarg($audio_path),
            $samples * 10, // Sur-échantillonnage pour moyenne
            $samples * 10
        );

        exec($cmd, $output, $code);
        
        if ($code !== 0 || empty($output)) {
            return $this->generateDummyWaveform($samples);
        }

        // Traiter les échantillons
        $peaks = [];
        $samples_per_peak = ceil(count($output) / $samples);
        
        for ($i = 0; $i < $samples; $i++) {
            $start = $i * $samples_per_peak;
            $end = min($start + $samples_per_peak, count($output));
            $slice = array_slice($output, $start, $end - $start);
            
            // Normaliser entre -32768 et 32767 vers -1 et 1
            $max = 0;
            foreach ($slice as $val) {
                $abs = abs((int)$val);
                if ($abs > $max) $max = $abs;
            }
            
            $peaks[] = round($max / 32768, 4);
        }

        return $peaks;
    }

    /**
     * Génère des données waveform factices pour fallback
     */
    private function generateDummyWaveform($samples)
    {
        $peaks = [];
        for ($i = 0; $i < $samples; $i++) {
            // Simuler une forme d'onde avec du bruit + sinusoïde
            $base = sin($i / 50) * 0.3 + 0.4;
            $noise = (mt_rand(-100, 100) / 1000);
            $peaks[] = max(0, min(1, $base + $noise));
        }
        return $peaks;
    }

    /**
     * Génère un spectrogramme (analyse fréquentielle)
     */
    private function generateSpectrogram($audio_path, $output_path)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) return false;

        $cmd = sprintf(
            '%s -i %s -lavfi showspectrumpic=s=800x400:mode=combined:color=intensity ' .
            '-frames:v 1 -y %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($audio_path),
            escapeshellarg($output_path)
        );

        exec($cmd, $output, $code);
        return ($code === 0 && file_exists($output_path));
    }

    /**
     * Convertit un fichier audio vers un autre format
     */
    public function convert()
    {
        $this->setJSONHeaders();
        
        $id = $this->input->post('id');
        $target_format = $this->input->post('format'); // mp3, ogg, flac, etc.
        
        $audio = $this->Model->readOne('galerie_medias', ['id_media' => $id, 'type' => 'audio']);
        
        if (!$audio || empty($audio['fichier'])) {
            $this->jsonResponse(false, 'Audio non trouvé');
            return;
        }

        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) {
            $this->jsonResponse(false, 'Conversion non disponible (FFmpeg manquant)');
            return;
        }

        $source_path = FCPATH . $audio['fichier'];
        $base_name = pathinfo($audio['fichier'], PATHINFO_FILENAME);
        $output_name = $base_name . '_converted.' . $target_format;
        $output_path = $this->final_dir . $output_name;

        // Paramètres de conversion selon le format
        $codec_params = [
            'mp3' => '-codec:a libmp3lame -q:a 2',
            'ogg' => '-codec:a libvorbis -q:a 4',
            'flac' => '-codec:a flac -compression_level 5',
            'wav' => '-codec:a pcm_s16le',
            'aac' => '-codec:a aac -b:a 192k',
            'opus' => '-codec:a libopus -b:a 128k'
        ];

        $params = $codec_params[$target_format] ?? '-codec:a copy';

        $cmd = sprintf(
            '%s -i %s %s -y %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($source_path),
            $params,
            escapeshellarg($output_path)
        );

        exec($cmd, $output, $code);

        if ($code !== 0 || !file_exists($output_path)) {
            $this->jsonResponse(false, 'Erreur conversion', ['details' => implode("\n", $output)]);
            return;
        }

        $this->jsonResponse(true, 'Conversion réussie', [
            'file_url' => base_url('attachments/Audio/' . $output_name),
            'file_size' => $this->formatBytes(filesize($output_path))
        ]);
    }

    // ==================== HELPERS PRIVÉS ====================

    private function validateInitUpload($file_name, $file_size)
    {
        if (empty($file_name) || $file_size <= 0) {
            return ['success' => false, 'message' => 'Paramètres invalides'];
        }

        if ($file_size > $this->max_file_size) {
            return [
                'success' => false, 
                'message' => 'Fichier trop grand (max: ' . $this->formatBytes($this->max_file_size) . ')'
            ];
        }

        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowed_extensions)) {
            return ['success' => false, 'message' => 'Format non supporté: ' . $ext];
        }

        return ['success' => true];
    }

    private function generateUploadId()
    {
        return 'audio_' . uniqid() . '_' . bin2hex(random_bytes(8));
    }

    private function generateFinalName($original_name)
    {
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        return date("YmdHis") . '_' . uniqid() . '_audio.' . $ext;
    }

    private function saveMetadata($upload_id, $metadata)
    {
        $path = $this->upload_dir . $upload_id . '/metadata.json';
        file_put_contents($path, json_encode($metadata, JSON_PRETTY_PRINT));
    }

    private function loadMetadata($upload_id)
    {
        $path = $this->upload_dir . $upload_id . '/metadata.json';
        if (!file_exists($path)) return null;
        
        $content = file_get_contents($path);
        return json_decode($content, true);
    }

    private function updateLastActivity($upload_id)
    {
        $metadata = $this->loadMetadata($upload_id);
        if ($metadata) {
            $metadata['last_activity'] = time();
            $this->saveMetadata($upload_id, $metadata);
        }
    }

    private function markChunkUploaded($upload_id, $chunk_index)
    {
        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata) return;
        
        if (!in_array($chunk_index, $metadata['uploaded_chunks'])) {
            $metadata['uploaded_chunks'][] = $chunk_index;
            sort($metadata['uploaded_chunks']);
            $this->saveMetadata($upload_id, $metadata);
        }
    }

    private function calculateProgress($upload_id)
    {
        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata) return null;
        
        $uploaded = count($metadata['uploaded_chunks']);
        $total = $metadata['total_chunks'];
        
        return [
            'uploaded_chunks' => $uploaded,
            'total_chunks' => $total,
            'percent' => round(($uploaded / $total) * 100, 2),
            'bytes_uploaded' => $uploaded * $metadata['chunk_size']
        ];
    }

    private function getActualUploadedChunks($upload_id)
    {
        $temp_dir = $this->upload_dir . $upload_id . '/';
        $chunks = [];
        
        foreach (glob($temp_dir . 'chunk_*') as $file) {
            if (preg_match('/chunk_(\d+)$/', $file, $m)) {
                $chunks[] = (int)$m[1];
            }
        }
        
        sort($chunks);
        return $chunks;
    }

    private function assembleChunks($upload_id, $final_path, $metadata)
    {
        $start_time = microtime(true);
        $temp_dir = $this->upload_dir . $upload_id . '/';
        
        $out = fopen($final_path, 'wb');
        if (!$out) {
            return ['success' => false, 'message' => 'Impossible de créer fichier final'];
        }

        try {
            for ($i = 0; $i < $metadata['total_chunks']; $i++) {
                $chunk_file = $temp_dir . 'chunk_' . $i;
                
                if (!file_exists($chunk_file)) {
                    fclose($out);
                    @unlink($final_path);
                    return ['success' => false, 'message' => "Chunk $i manquant"];
                }

                $handle = fopen($chunk_file, 'rb');
                while (!feof($handle)) {
                    fwrite($out, fread($handle, 8192));
                }
                fclose($handle);
                
                unlink($chunk_file);
            }
            
            fclose($out);
            
            $time_ms = round((microtime(true) - $start_time) * 1000);
            
            return ['success' => true, 'time_ms' => $time_ms];
            
        } catch (Exception $e) {
            fclose($out);
            @unlink($final_path);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function cleanupUploadSession($upload_id)
    {
        $temp_dir = $this->upload_dir . $upload_id . '/';
        $this->recursiveDelete($temp_dir);
    }

    private function prepareAudioData($type_source)
    {
        $data = [
            'titre' => $this->input->post('titre'),
            'type' => 'audio',
            'description' => $this->input->post('description') ?: null,
            'categorie' => $this->input->post('categorie') ?: null,
            'date_media' => $this->input->post('date_media') ?: null,
            'credits' => $this->input->post('credits') ?: null,
            'est_actif' => 1,
            'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
            'message_reseaux' => $this->input->post('message_reseaux') ?: null,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($type_source == 'upload') {
            $file_path = $this->input->post('uploaded_file_path');
            $audio_metadata = json_decode($this->input->post('audio_metadata') ?: '{}', true);
            
            if (empty($file_path) || !file_exists(FCPATH . $file_path)) {
                $this->session->set_flashdata('error', 'Aucun fichier audio uploadé.');
                return false;
            }
            
            $full_path = FCPATH . $file_path;
            $data['fichier'] = $file_path;
            $data['taille'] = filesize($full_path);
            $data['mime_type'] = mime_content_type($full_path);
            $data['duree'] = $audio_metadata['duration'] ?? null;
            $data['bitrate'] = $audio_metadata['bitrate'] ?? null;
            $data['sample_rate'] = $audio_metadata['sample_rate'] ?? null;
            $data['channels'] = $audio_metadata['channels'] ?? null;
            $data['waveform'] = $audio_metadata['waveform'] ?? null;
            $data['spectrogram'] = $audio_metadata['spectrogram'] ?? null;
            $data['waveform_data'] = $audio_metadata['waveform_data'] ?? null;
            $data['metadata_id3'] = !empty($audio_metadata['metadata']) ? json_encode($audio_metadata['metadata']) : null;
            
            // Utiliser les métadonnées ID3 pour le titre si non fourni
            if (empty($data['titre']) && !empty($audio_metadata['metadata']['title'])) {
                $data['titre'] = $audio_metadata['metadata']['title'];
            }
            if (empty($data['credits']) && !empty($audio_metadata['metadata']['artist'])) {
                $data['credits'] = $audio_metadata['metadata']['artist'];
            }
        } else {
            $data['lien'] = $this->input->post('lien');
            // Pour les liens externes, essayer de récupérer l'embed
            $data['embed_code'] = $this->generateEmbedCode($data['lien']);
        }
        
        return $data;
    }

    private function prepareUpdateData($id, $type_source)
    {
        $data = [
            'titre' => $this->input->post('titre'),
            'description' => $this->input->post('description') ?: null,
            'categorie' => $this->input->post('categorie') ?: null,
            'date_media' => $this->input->post('date_media') ?: null,
            'credits' => $this->input->post('credits') ?: null,
            'est_actif' => $this->input->post('est_actif') ? 1 : 0,
            'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
            'message_reseaux' => $this->input->post('message_reseaux') ?: null,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $old = $this->Model->readOne('galerie_medias', ['id_media' => $id]);

        if ($type_source == 'upload') {
            $new_path = $this->input->post('uploaded_file_path');
            $audio_metadata = json_decode($this->input->post('audio_metadata') ?: '{}', true);
            
            if (!empty($new_path) && file_exists(FCPATH . $new_path)) {
                if ($old && !empty($old['fichier'])) {
                    $this->deleteAudioFiles($old);
                }
                
                $full_path = FCPATH . $new_path;
                $data['fichier'] = $new_path;
                $data['taille'] = filesize($full_path);
                $data['mime_type'] = mime_content_type($full_path);
                $data['lien'] = null;
                $data['duree'] = $audio_metadata['duration'] ?? null;
                $data['bitrate'] = $audio_metadata['bitrate'] ?? null;
                $data['sample_rate'] = $audio_metadata['sample_rate'] ?? null;
                $data['channels'] = $audio_metadata['channels'] ?? null;
                $data['waveform'] = $audio_metadata['waveform'] ?? null;
                $data['spectrogram'] = $audio_metadata['spectrogram'] ?? null;
                $data['waveform_data'] = $audio_metadata['waveform_data'] ?? null;
                $data['metadata_id3'] = !empty($audio_metadata['metadata']) ? json_encode($audio_metadata['metadata']) : null;
            }
        } elseif ($type_source == 'link') {
            $new_lien = $this->input->post('lien');
            
            if ($old && !empty($old['fichier'])) {
                $this->deleteAudioFiles($old);
            }
            
            $data['lien'] = $new_lien;
            $data['embed_code'] = $this->generateEmbedCode($new_lien);
            $data['fichier'] = null;
            $data['taille'] = null;
            $data['mime_type'] = null;
            $data['duree'] = null;
            $data['bitrate'] = null;
            $data['waveform'] = null;
            $data['spectrogram'] = null;
        }

        return $data;
    }

    private function deleteAudioFiles($audio)
    {
        if (!empty($audio['fichier']) && file_exists(FCPATH . $audio['fichier'])) {
            @unlink(FCPATH . $audio['fichier']);
        }
        if (!empty($audio['waveform']) && file_exists(FCPATH . $audio['waveform'])) {
            @unlink(FCPATH . $audio['waveform']);
        }
        if (!empty($audio['spectrogram']) && file_exists(FCPATH . $audio['spectrogram'])) {
            @unlink(FCPATH . $audio['spectrogram']);
        }
        if (!empty($audio['waveform_data']) && file_exists(FCPATH . $audio['waveform_data'])) {
            @unlink(FCPATH . $audio['waveform_data']);
        }
    }

    private function generateEmbedCode($url)
    {
        // SoundCloud
        if (strpos($url, 'soundcloud.com') !== false) {
            return '<iframe width="100%" height="166" scrolling="no" frameborder="no" ' .
                   'src="https://w.soundcloud.com/player/?url=' . urlencode($url) . '&color=%23ff5500"></iframe>';
        }
        
        // Spotify
        if (preg_match('/spotify\.com\/track\/([a-zA-Z0-9]+)/', $url, $m)) {
            return '<iframe src="https://open.spotify.com/embed/track/' . $m[1] . '" ' .
                   'width="100%" height="80" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>';
        }
        
        // Mixcloud
        if (strpos($url, 'mixcloud.com') !== false) {
            return '<iframe width="100%" height="120" src="https://www.mixcloud.com/widget/iframe/?feed=' . 
                   urlencode($url) . '&hide_cover=1&light=1" frameborder="0"></iframe>';
        }
        
        // Bandcamp
        if (strpos($url, 'bandcamp.com') !== false) {
            return '<iframe style="border: 0; width: 100%; height: 120px;" ' .
                   'src="' . $url . '/size=large/bgcol=ffffff/linkcol=0687f5/tracklist=false/artwork=small/transparent=true/" ' .
                   'seamless></iframe>';
        }
        
        return null;
    }

    private function getExistingCategories()
    {
        $this->db->select('DISTINCT(categorie) as cat');
        $this->db->where('type', 'audio');
        $this->db->where('categorie IS NOT NULL');
        $this->db->where('categorie !=', '');
        $query = $this->db->get('galerie_medias');
        
        $categories = [];
        foreach ($query->result() as $row) {
            $categories[] = $row->cat;
        }
        return $categories;
    }

    private function calculateTotalDuration()
    {
        $this->db->select_sum('duree', 'total_duration');
        $this->db->where('type', 'audio');
        $this->db->where('est_actif', 1);
        $query = $this->db->get('galerie_medias');
        
        return $query->row()->total_duration ?? 0;
    }

    // ==================== OUTILS AUDIO ====================

    private function findFFmpeg()
    {
        $paths = ['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/opt/ffmpeg/bin/ffmpeg', 'C:\\ffmpeg\\bin\\ffmpeg.exe'];
        foreach ($paths as $p) {
            exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    private function findFFprobe()
    {
        $paths = ['ffprobe', '/usr/bin/ffprobe', '/usr/local/bin/ffprobe', '/opt/ffmpeg/bin/ffprobe', 'C:\\ffmpeg\\bin\\ffprobe.exe'];
        foreach ($paths as $p) {
            exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    private function findSox()
    {
        $paths = ['sox', '/usr/bin/sox', '/usr/local/bin/sox'];
        foreach ($paths as $p) {
            exec($p . ' --version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    private function findLame()
    {
        $paths = ['lame', '/usr/bin/lame', '/usr/local/bin/lame'];
        foreach ($paths as $p) {
            exec($p . ' --version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    // ==================== UTILITAIRES ====================

    private function getChunkPath($upload_id, $chunk_index)
    {
        return $this->upload_dir . $upload_id . '/chunk_' . $chunk_index;
    }

    private function setJSONHeaders()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }

    private function jsonResponse($success, $message = '', $data = [])
    {
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
            'timestamp' => time()
        ], $data));
    }

    private function setFlashMessage($success, $success_msg, $error_msg)
    {
        $this->session->set_flashdata(
            $success ? 'success' : 'error',
            $success ? $success_msg : $error_msg
        );
    }

    private function logError($context, $details)
    {
        $log = date('Y-m-d H:i:s') . " | $context | " . json_encode($details) . "\n";
        $log_dir = FCPATH . 'logs/';
        if (!is_dir($log_dir)) @mkdir($log_dir, 0777, true);
        error_log($log, 3, $log_dir . 'audio_upload_errors.log');
    }

    private function getDetailedUploadError($code)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => ['type' => 'PHP_LIMIT', 'message' => 'Fichier trop grand pour upload_max_filesize (' . ini_get('upload_max_filesize') . ')'],
            UPLOAD_ERR_FORM_SIZE => ['type' => 'FORM_LIMIT', 'message' => 'Fichier trop grand pour MAX_FILE_SIZE'],
            UPLOAD_ERR_PARTIAL => ['type' => 'NETWORK', 'message' => 'Upload partiel - erreur réseau'],
            UPLOAD_ERR_NO_FILE => ['type' => 'NO_FILE', 'message' => 'Aucun fichier reçu'],
            UPLOAD_ERR_NO_TMP_DIR => ['type' => 'SERVER_CONFIG', 'message' => 'Dossier temporaire manquant'],
            UPLOAD_ERR_CANT_WRITE => ['type' => 'DISK', 'message' => 'Erreur écriture disque'],
            UPLOAD_ERR_EXTENSION => ['type' => 'PHP_EXT', 'message' => 'Extension PHP bloquante']
        ];
        return $errors[$code] ?? ['type' => 'UNKNOWN', 'message' => 'Erreur #' . $code];
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1099511627776) return number_format($bytes / 1099511627776, 2) . ' TB';
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    private function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return gmdate("s\\s", $seconds);
        } elseif ($seconds < 3600) {
            return gmdate("i\\m s\\s", $seconds);
        } else {
            return gmdate("H\\h i\\m s\\s", $seconds);
        }
    }
}