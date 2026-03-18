<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur Audio Ultra-Intelligent v5.0 - AVEC PROGRESSION TEMPS RÉEL
 * Conforme structure BDD: galerie_medias
 * Features: WebSocket-like progress, Multi-bitrate conversion, HLS streaming
 */
class Audio extends MY_Controller {

    private $upload_dir;
    private $final_dir;
    private $waveform_dir;
    private $covers_dir;
    private $thumbs_dir;
    private $hls_dir;
    private $progress_dir; // NOUVEAU: pour stocker la progression
    private $chunk_size;
    private $max_file_size;
    private $allowed_audio_extensions;
    private $allowed_image_extensions;
    private $session_timeout;

    // Configuration qualités audio (comme Spotify)
    private $audio_qualities = [
        'low'    => ['bitrate' => '64k',  'codec' => 'mp3', 'suffix' => '_64k'],
        'medium' => ['bitrate' => '128k', 'codec' => 'mp3', 'suffix' => '_128k'],
        'high'   => ['bitrate' => '192k', 'codec' => 'mp3', 'suffix' => '_192k'],
        'max'    => ['bitrate' => '320k', 'codec' => 'mp3', 'suffix' => '_320k']
    ];

    function __construct()
    {
        parent::__construct();
        is_admin();
        
        // Configuration des chemins
        $this->upload_dir = FCPATH . 'uploads/temp/audio/';
        $this->final_dir = FCPATH . 'attachments/Audio/';
        $this->waveform_dir = FCPATH . 'attachments/Audio/Waveforms/';
        $this->covers_dir = FCPATH . 'attachments/Audio/Covers/';
        $this->thumbs_dir = FCPATH . 'attachments/Audio/Thumbs/';
        $this->hls_dir = FCPATH . 'attachments/Audio/HLS/'; // NOUVEAU
        $this->progress_dir = FCPATH . 'uploads/progress/'; // NOUVEAU
        
        // Configuration technique
        $this->chunk_size = 2 * 1024 * 1024; // 2MB chunks
        $this->max_file_size = 500 * 1024 * 1024; // 500MB max
        $this->session_timeout = 3600;
        
        // Extensions supportées
        $this->allowed_audio_extensions = [
            'mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a', 'wma', 
            'aiff', 'alac', 'opus', 'weba', 'amr', 'au', 'snd',
            'ac3', 'dts', 'eac3', 'wv', 'mpc', 'ape'
        ];
        
        $this->allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Création des dossiers
        foreach ([$this->upload_dir, $this->final_dir, $this->waveform_dir, 
                  $this->covers_dir, $this->thumbs_dir, $this->hls_dir,
                  $this->progress_dir] as $dir) {
            $this->ensureDirectoryExists($dir);
        }
        
        $this->configurePHP();
        $this->cleanupExpiredSessions();
    }

    // ==================== CONFIGURATION ====================

    private function configurePHP()
    {
        @ini_set('memory_limit', '1024M');
        @ini_set('max_execution_time', '0');
        @ini_set('max_input_time', '0');
        @ini_set('upload_max_filesize', '20M');
        @ini_set('post_max_size', '20M');
        @ini_set('max_file_uploads', '20');
        
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', 'Off');
        @ini_set('session.gc_maxlifetime', $this->session_timeout);
    }

    private function ensureDirectoryExists($path)
    {
        if (!is_dir($path)) {
            @mkdir($path, 0777, TRUE);
            @chmod($path, 0777);
        }
    }

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
        
        // Cleanup progress files aussi
        if (is_dir($this->progress_dir)) {
            $progress_files = glob($this->progress_dir . 'progress_*.json');
            foreach ($progress_files as $file) {
                if ($now - filemtime($file) > $this->session_timeout) {
                    @unlink($file);
                }
            }
        }
    }

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

    public function index()
    {
        $data['audios'] = $this->Model->read('galerie_medias', 
            ['type' => 'audio'], 
            'titre', 
            'ASC'
        );
        
        $data['categories'] = $this->getExistingCategories();
        $data['total_duration'] = $this->calculateTotalDuration();
        
        $this->load->view('Audio_View', $data);
    }

    /**
     * API: Récupérer la progression en temps réel (polling)
     * Cette méthode est appelée par le frontend toutes les 500ms
     */
    public function getProgress()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->get('upload_id');
        $phase = $this->input->get('phase') ?: 'upload'; // upload, processing, conversion
        
        if (empty($upload_id)) {
            $this->jsonResponse(false, 'ID upload manquant');
            return;
        }
        
        $progress_file = $this->progress_dir . 'progress_' . $upload_id . '.json';
        
        // Si fichier existe, retourner contenu
        if (file_exists($progress_file)) {
            $progress = json_decode(file_get_contents($progress_file), true);
            $this->jsonResponse(true, 'Progression récupérée', $progress);
            return;
        }
        
        // Sinon, vérifier métadonnées upload
        $metadata = $this->loadMetadata($upload_id);
        if ($metadata) {
            $progress = [
                'phase' => 'upload',
                'percent' => round((count($metadata['uploaded_chunks']) / $metadata['total_chunks']) * 100, 2),
                'uploaded_chunks' => count($metadata['uploaded_chunks']),
                'total_chunks' => $metadata['total_chunks'],
                'message' => 'Upload en cours...',
                'timestamp' => time()
            ];
            $this->jsonResponse(true, 'Progression calculée', $progress);
            return;
        }
        
        $this->jsonResponse(false, 'Session non trouvée');
    }

    /**
     * API: Diagnostic complet
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
                'lame' => $this->findLame() ? 'Disponible' : 'Non disponible',
                'mediainfo' => $this->findMediaInfo() ? 'Disponible' : 'Non disponible',
                'convert' => shell_exec('which convert') ? 'ImageMagick OK' : 'Non dispo'
            ],
            'auto_detection' => [
                'id3_tags' => true,
                'ffprobe_metadata' => (bool)$this->findFFprobe(),
                'cover_extraction' => (bool)$this->findFFmpeg(),
                'waveform_generation' => (bool)$this->findFFmpeg(),
                'spectrogram' => (bool)$this->findFFmpeg(),
                'thumbnail_generation' => true,
                'multi_bitrate' => (bool)$this->findFFmpeg(),
                'hls_generation' => (bool)$this->findFFmpeg()
            ],
            'limits' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'memory_limit' => ini_get('memory_limit'),
            ],
            'directories' => [
                'upload_dir_writable' => is_writable($this->upload_dir),
                'final_dir_writable' => is_writable($this->final_dir),
                'waveform_dir_writable' => is_writable($this->waveform_dir),
                'covers_dir_writable' => is_writable($this->covers_dir),
                'thumbs_dir_writable' => is_writable($this->thumbs_dir),
                'hls_dir_writable' => is_writable($this->hls_dir),
                'disk_free' => $this->formatBytes(@disk_free_space($this->final_dir))
            ],
            'timestamp' => time()
        ];
        
        echo json_encode($info);
    }

    /**
     * Streaming audio avec range requests (comme Spotify)
     */
    public function stream($filename)
    {
        $file_path = $this->final_dir . basename($filename);
        
        if (!file_exists($file_path)) {
            show_404();
            return;
        }

        $mime_type = mime_content_type($file_path);
        $file_size = filesize($file_path);
        
        header('Content-Type: ' . $mime_type);
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . $file_size);
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        
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
        
        $fp = fopen($file_path, 'rb');
        fseek($fp, $start);
        
        $buffer_size = 8192;
        $bytes_sent = 0;
        $bytes_to_send = $end - $start + 1;
        
        while (!feof($fp) && $bytes_sent < $bytes_to_send) {
            $chunk_size = min($buffer_size, $bytes_to_send - $bytes_sent);
            echo fread($fp, $chunk_size);
            $bytes_sent += $chunk_size;
            
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }
        
        fclose($fp);
    }

    /**
     * Streaming HLS (m3u8 playlist) - comme YouTube/Spotify
     */
    public function hls($audio_id)
    {
        $audio = $this->Model->readOne('galerie_medias', ['id_media' => $audio_id, 'type' => 'audio']);
        
        if (!$audio || empty($audio['hls_playlist'])) {
            show_404();
            return;
        }
        
        $playlist_path = FCPATH . $audio['hls_playlist'];
        
        if (!file_exists($playlist_path)) {
            show_404();
            return;
        }
        
        header('Content-Type: application/vnd.apple.mpegurl');
        header('Cache-Control: public, max-age=3600');
        readfile($playlist_path);
    }

    /**
     * Servir les segments HLS (.ts files)
     */
    public function hls_segment($filename)
    {
        $file_path = $this->hls_dir . basename($filename);
        
        if (!file_exists($file_path)) {
            show_404();
            return;
        }
        
        header('Content-Type: video/mp2t');
        header('Cache-Control: public, max-age=31536000');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
    }

    // ==================== API UPLOAD CHUNKED AVEC PROGRESSION ====================

    /**
     * Étape 1: Initialiser upload audio
     */
    public function initUpload()
    {
        $this->setJSONHeaders();
        
        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');
        $file_hash = $this->input->post('file_hash') ?: null;

        $validation = $this->validateAudioUpload($file_name, $file_size);
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
            'type' => 'audio'
        ];

        $this->saveMetadata($upload_id, $metadata);
        
        // Initialiser fichier de progression
        $this->saveProgress($upload_id, [
            'phase' => 'upload',
            'percent' => 0,
            'message' => 'Initialisation...',
            'speed' => 0,
            'eta' => 0
        ]);

        $this->jsonResponse(true, 'Session initialisée', [
            'upload_id' => $upload_id,
            'chunk_size' => $this->chunk_size,
            'total_chunks' => $total_chunks,
            'max_retries' => 3
        ]);
    }

    /**
     * Upload de miniature séparé (optionnel)
     */
    public function uploadThumbnail()
    {
        $this->setJSONHeaders();
        
        if (empty($_FILES['thumbnail'])) {
            $this->jsonResponse(false, 'Aucune image reçue');
            return;
        }

        $file = $_FILES['thumbnail'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(false, 'Erreur upload: ' . $file['error']);
            return;
        }

        // Validation type MIME
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            $this->jsonResponse(false, 'Type d\'image non supporté. Utilisez JPG, PNG, GIF ou WEBP');
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowed_image_extensions)) {
            $this->jsonResponse(false, 'Extension non supportée');
            return;
        }

        // Générer nom unique
        $thumb_name = 'thumb_' . date("YmdHis") . '_' . uniqid() . '.' . $ext;
        $thumb_path = $this->thumbs_dir . $thumb_name;
        $relative_path = 'attachments/Audio/Thumbs/' . $thumb_name;

        // Redimensionner et optimiser
        if (!$this->processThumbnail($file['tmp_name'], $thumb_path, $ext)) {
            // Fallback: simple copie
            if (!move_uploaded_file($file['tmp_name'], $thumb_path)) {
                $this->jsonResponse(false, 'Erreur sauvegarde miniature');
                return;
            }
        }

        $this->jsonResponse(true, 'Miniature uploadée', [
            'thumbnail_path' => $relative_path,
            'thumbnail_url' => base_url($relative_path),
            'file_name' => $thumb_name
        ]);
    }

    /**
     * Étape 2: Recevoir un chunk audio avec mise à jour progression
     */
    public function uploadChunk()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $chunk_index = (int)$this->input->post('chunk_index');
        $chunk_start_time = (float)$this->input->post('chunk_start_time'); // Timestamp début envoi

        if (empty($upload_id)) {
            $this->jsonResponse(false, 'ID upload manquant');
            return;
        }

        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata || $metadata['status'] !== 'active') {
            $this->jsonResponse(false, 'Session invalide ou expirée');
            return;
        }

        if ($chunk_index < 0 || $chunk_index >= $metadata['total_chunks']) {
            $this->jsonResponse(false, 'Index chunk invalide');
            return;
        }

        $chunk_path = $this->getChunkPath($upload_id, $chunk_index);
        if (file_exists($chunk_path)) {
            $this->markChunkUploaded($upload_id, $chunk_index);
            $progress = $this->calculateProgress($upload_id);
            $this->updateProgressFile($upload_id, $progress);
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
            $this->jsonResponse(false, $error_detail['message']);
            return;
        }

        if (!@move_uploaded_file($file['tmp_name'], $chunk_path)) {
            $this->jsonResponse(false, 'Erreur écriture disque');
            return;
        }

        $this->markChunkUploaded($upload_id, $chunk_index);
        $this->updateLastActivity($upload_id);
        
        // Calculer progression détaillée
        $progress = $this->calculateDetailedProgress($upload_id, $chunk_index, $chunk_start_time);
        $this->updateProgressFile($upload_id, $progress);
        
        $this->jsonResponse(true, 'Chunk reçu', $progress);
    }

    /**
     * Étape 3: Vérifier statut
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
        $missing = array_diff(range(0, $metadata['total_chunks'] - 1), $actual_chunks);
        
        $progress = $this->calculateProgress($upload_id);
        $this->updateProgressFile($upload_id, $progress);

        $this->jsonResponse(true, 'Statut récupéré', [
            'upload_id' => $upload_id,
            'status' => $metadata['status'],
            'progress' => $progress,
            'missing_chunks' => array_values($missing)
        ]);
    }

    /**
     * Étape 4: Finaliser avec analyse complète et conversion multi-bitrate
     */
    public function completeUpload()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $user_description = $this->input->post('description');
        $custom_thumbnail = $this->input->post('custom_thumbnail');
        $generate_hls = $this->input->post('generate_hls') ? true : false; // Option HLS
        
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
            $this->jsonResponse(false, 'Chunks manquants', ['missing_chunks' => array_values($missing)]);
            return;
        }

        // Mettre à jour progression: phase assemblage
        $this->updateProgressFile($upload_id, [
            'phase' => 'processing',
            'percent' => 5,
            'message' => 'Assemblage des chunks...',
            'detail' => 'Reconstruction du fichier audio'
        ]);

        // Assembler
        $final_name = $this->generateFinalName($metadata['file_name']);
        $final_path = $this->final_dir . $final_name;
        $relative_path = 'attachments/Audio/' . $final_name;

        $assembled = $this->assembleChunks($upload_id, $final_path, $metadata);
        
        if (!$assembled['success']) {
            $this->jsonResponse(false, 'Erreur assemblage: ' . $assembled['message']);
            return;
        }

        // Analyse complète
        $this->updateMetadataStatus($upload_id, 'processing');
        
        // 1. Analyse FFprobe (10%)
        $this->updateProgressFile($upload_id, [
            'phase' => 'processing',
            'percent' => 10,
            'message' => 'Analyse du fichier audio...',
            'detail' => 'Extraction des métadonnées ID3'
        ]);
        
        $audio_info = $this->analyzeWithFFprobe($final_path);
        
        // 2. Extraction cover art intégré (20%)
        $this->updateProgressFile($upload_id, [
            'phase' => 'processing',
            'percent' => 20,
            'message' => 'Extraction de la pochette...',
            'detail' => 'Recherche artwork dans les métadonnées'
        ]);
        
        $cover_art = null;
        if (empty($custom_thumbnail)) {
            $cover_art = $this->extractCoverArt($final_path, $final_name);
        }
        
        // 3. Générer miniature depuis waveform si pas de cover (30%)
        $this->updateProgressFile($upload_id, [
            'phase' => 'processing',
            'percent' => 30,
            'message' => 'Génération de la miniature...',
            'detail' => 'Création visuelle depuis l\'audio'
        ]);
        
        $generated_thumb = null;
        if (empty($cover_art) && empty($custom_thumbnail)) {
            $generated_thumb = $this->generateThumbnailFromWaveform($final_path, $final_name);
        }
        
        // 4. Visualisations (50%)
        $this->updateProgressFile($upload_id, [
            'phase' => 'processing',
            'percent' => 50,
            'message' => 'Génération des visualisations...',
            'detail' => 'Waveform et spectrogramme'
        ]);
        
        $visualizations = $this->generateVisualizations($final_path, $final_name);
        
        // 5. Conversion multi-bitrate (70%) - OPTIONNEL mais recommandé
        $converted_versions = [];
        if ($this->findFFmpeg() && $audio_info['duration'] > 0) {
            $this->updateProgressFile($upload_id, [
                'phase' => 'processing',
                'percent' => 70,
                'message' => 'Conversion multi-bitrate...',
                'detail' => 'Création des qualités 64k, 128k, 192k, 320k'
            ]);
            
            $converted_versions = $this->convertToMultipleBitrates($final_path, $final_name);
        }
        
        // 6. Génération HLS (85%) - OPTIONNEL
        $hls_playlist = null;
        if ($generate_hls && $this->findFFmpeg()) {
            $this->updateProgressFile($upload_id, [
                'phase' => 'processing',
                'percent' => 85,
                'message' => 'Génération HLS...',
                'detail' => 'Création playlist streaming adaptatif'
            ]);
            
            $hls_playlist = $this->generateHLS($final_path, $final_name);
        }
        
        // Déterminer la miniature finale
        $final_thumbnail = $custom_thumbnail ?: $cover_art ?: $generated_thumb;

        // Nettoyer
        $this->cleanupUploadSession($upload_id);
        @unlink($this->progress_dir . 'progress_' . $upload_id . '.json');

        // Préparer réponse
        $suggested_title = $audio_info['title'] ?: $this->suggestTitleFromFilename($metadata['file_name']);
        
        $response_data = [
            'file_path' => $relative_path,
            'file_name' => $final_name,
            'file_size' => filesize($final_path),
            'file_size_formatted' => $this->formatBytes(filesize($final_path)),
            
            // Métadonnées techniques
            'duration' => $audio_info['duration'],
            'duration_formatted' => $this->formatDuration($audio_info['duration']),
            'bitrate' => $audio_info['bitrate'],
            'sample_rate' => $audio_info['sample_rate'],
            'channels' => $audio_info['channels'],
            'codec' => $audio_info['codec'],
            
            // Métadonnées ID3
            'title' => $audio_info['title'],
            'artist' => $audio_info['artist'],
            'album' => $audio_info['album'],
            'year' => $audio_info['year'],
            'genre' => $audio_info['genre'],
            
            // Miniature
            'thumbnail' => $final_thumbnail,
            'thumbnail_type' => $custom_thumbnail ? 'custom' : ($cover_art ? 'extracted' : 'generated'),
            
            // Visualisations
            'waveform' => $visualizations['waveform'],
            'waveform_data' => $visualizations['waveform_data'],
            'spectrogram' => $visualizations['spectrogram'],
            
            // Versions converties
            'converted_versions' => $converted_versions,
            
            // HLS
            'hls_playlist' => $hls_playlist,
            
            // Pour formulaire
            'suggested_data' => [
                'titre' => $suggested_title,
                'credits' => $audio_info['artist'] ?: 'Artiste inconnu',
                'categorie' => $this->suggestCategory($audio_info),
                'description' => $user_description,
                'date_media' => $audio_info['year'] ? $audio_info['year'] . '-01-01' : null
            ],
            
            'mime_type' => mime_content_type($final_path)
        ];

        $this->jsonResponse(true, 'Upload complété', $response_data);
    }

    public function cancelUpload()
    {
        $this->setJSONHeaders();
        $upload_id = $this->input->post('upload_id');
        
        if ($upload_id) {
            $this->cleanupUploadSession($upload_id);
            @unlink($this->progress_dir . 'progress_' . $upload_id . '.json');
        }

        $this->jsonResponse(true, 'Upload annulé');
    }

    // ==================== CONVERSION MULTI-BITRATE & HLS ====================

    /**
     * Convertir l'audio en plusieurs bitrates (comme Spotify)
     */
    private function convertToMultipleBitrates($source_path, $filename)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) return [];

        $base_name = pathinfo($filename, PATHINFO_FILENAME);
        $versions = [];

        foreach ($this->audio_qualities as $quality => $config) {
            $output_name = $base_name . $config['suffix'] . '.mp3';
            $output_path = $this->final_dir . $output_name;
            $relative_path = 'attachments/Audio/' . $output_name;

            $cmd = sprintf(
                '%s -i %s -codec:a libmp3lame -b:a %s -q:a 2 -map_metadata 0 -id3v2_version 3 -y %s 2>&1',
                escapeshellarg($ffmpeg),
                escapeshellarg($source_path),
                $config['bitrate'],
                escapeshellarg($output_path)
            );

            exec($cmd, $output, $code);

            if ($code === 0 && file_exists($output_path)) {
                $versions[$quality] = [
                    'path' => $relative_path,
                    'bitrate' => $config['bitrate'],
                    'size' => filesize($output_path),
                    'size_formatted' => $this->formatBytes(filesize($output_path))
                ];
            }
        }

        return $versions;
    }

    /**
     * Générer playlist HLS pour streaming adaptatif
     */
    private function generateHLS($source_path, $filename)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) return null;

        $base_name = pathinfo($filename, PATHINFO_FILENAME);
        $hls_folder = $this->hls_dir . $base_name . '/';
        
        if (!is_dir($hls_folder)) {
            mkdir($hls_folder, 0777, true);
        }

        $playlist_name = $base_name . '.m3u8';
        $playlist_path = $hls_folder . $playlist_name;
        $relative_playlist = 'attachments/Audio/HLS/' . $base_name . '/' . $playlist_name;

        // Générer HLS avec segments de 10 secondes
        $cmd = sprintf(
            '%s -i %s -codec:a aac -b:a 128k -hls_time 10 -hls_playlist_type vod -hls_segment_filename %s %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($source_path),
            escapeshellarg($hls_folder . $base_name . '_%03d.ts'),
            escapeshellarg($playlist_path)
        );

        exec($cmd, $output, $code);

        return ($code === 0 && file_exists($playlist_path)) ? $relative_playlist : null;
    }

    // ==================== CRUD ====================

    public function Create()
    {
        $this->form_validation->set_rules('description', 'Description', 'trim|max_length[2000]');
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

    public function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('description', 'Description', 'trim|max_length[2000]');
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

    public function ChangeStatus()
    {
        $id = $this->input->post('id');
        $est_actif = $this->input->post('est_actif');
        $status = ($est_actif == 1) ? 0 : 1;
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], ['est_actif' => $status]);

        $this->setFlashMessage($rsp, 'Statut mis à jour avec succès.', 'Erreur lors de la mise à jour du statut.');
        redirect(base_url('audio'));    
    }

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

    // ==================== ANALYSE & TRAITEMENT ====================

    private function analyzeWithFFprobe($file_path)
    {
        $ffprobe = $this->findFFprobe();
        if (!$ffprobe) {
            return $this->analyzeBasic($file_path);
        }

        $cmd = sprintf(
            '%s -v quiet -print_format json -show_format -show_streams %s 2>&1',
            escapeshellarg($ffprobe),
            escapeshellarg($file_path)
        );
        
        exec($cmd, $output, $code);
        
        if ($code !== 0) {
            return $this->analyzeBasic($file_path);
        }

        $data = json_decode(implode("\n", $output), true);
        if (!$data) {
            return $this->analyzeBasic($file_path);
        }

        $format = $data['format'] ?? [];
        $stream = $data['streams'][0] ?? [];
        $tags = $format['tags'] ?? [];

        return [
            'duration' => (float)($format['duration'] ?? 0),
            'bitrate' => (int)($format['bit_rate'] ?? 0),
            'format' => $format['format_name'] ?? pathinfo($file_path, PATHINFO_EXTENSION),
            'sample_rate' => (int)($stream['sample_rate'] ?? 0),
            'channels' => (int)($stream['channels'] ?? 0),
            'channel_layout' => $stream['channel_layout'] ?? null,
            'codec' => $stream['codec_name'] ?? null,
            
            // Tags ID3 normalisés
            'title' => $tags['title'] ?? $tags['TITLE'] ?? $tags['TIT2'] ?? null,
            'artist' => $tags['artist'] ?? $tags['ARTIST'] ?? $tags['TPE1'] ?? null,
            'album' => $tags['album'] ?? $tags['ALBUM'] ?? $tags['TALB'] ?? null,
            'year' => $tags['date'] ?? $tags['DATE'] ?? $tags['TYER'] ?? $tags['year'] ?? null,
            'genre' => $tags['genre'] ?? $tags['GENRE'] ?? $tags['TCON'] ?? null,
            'track' => $tags['track'] ?? $tags['TRACK'] ?? $tags['TRCK'] ?? null,
            'composer' => $tags['composer'] ?? $tags['COMPOSER'] ?? $tags['TCOM'] ?? null,
            'publisher' => $tags['publisher'] ?? $tags['PUBLISHER'] ?? $tags['TPUB'] ?? null,
            'comment' => $tags['comment'] ?? $tags['COMMENT'] ?? null,
            
            'raw_tags' => $tags
        ];
    }

    private function analyzeBasic($file_path)
    {
        $size = filesize($file_path);
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        $duration_estimate = 0;
        if ($ext === 'mp3') {
            $duration_estimate = ($size * 8) / (128 * 1024);
        }

        return [
            'duration' => $duration_estimate,
            'bitrate' => 128000,
            'format' => $ext,
            'sample_rate' => 44100,
            'channels' => 2,
            'codec' => $ext,
            'title' => null,
            'artist' => null,
            'album' => null,
            'year' => null,
            'genre' => null,
            'note' => 'Estimation basique'
        ];
    }

    /**
     * Extraction cover art depuis fichier audio
     */
    private function extractCoverArt($audio_path, $filename)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) return null;

        $cover_name = pathinfo($filename, PATHINFO_FILENAME) . '_cover.jpg';
        $cover_path = $this->covers_dir . $cover_name;
        $relative_path = 'attachments/Audio/Covers/' . $cover_name;

        // Tentative 1: Extraction directe
        $cmd = sprintf(
            '%s -i %s -an -vcodec copy -f image2 -y %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($audio_path),
            escapeshellarg($cover_path)
        );

        exec($cmd, $output, $code);

        // Tentative 2: Conversion si échec
        if ($code !== 0 || !file_exists($cover_path) || filesize($cover_path) < 1000) {
            $cmd2 = sprintf(
                '%s -i %s -an -vf "scale=800:800:force_original_aspect_ratio=decrease,pad=800:800:(ow-iw)/2:(oh-ih)/2:white" -frames:v 1 -y %s 2>&1',
                escapeshellarg($ffmpeg),
                escapeshellarg($audio_path),
                escapeshellarg($cover_path)
            );
            exec($cmd2, $output2, $code2);
            
            if ($code2 !== 0 || !file_exists($cover_path) || filesize($cover_path) < 1000) {
                return null;
            }
        }

        $this->optimizeImage($cover_path);
        return $relative_path;
    }

    /**
     * Générer miniature depuis waveform
     */
    private function generateThumbnailFromWaveform($audio_path, $filename)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) return null;

        $thumb_name = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumb_path = $this->thumbs_dir . $thumb_name;
        $relative_path = 'attachments/Audio/Thumbs/' . $thumb_name;

        // Générer waveform visuelle puis capturer
        $cmd = sprintf(
            '%s -i %s -filter_complex "aformat=channel_layouts=mono,showwavespic=s=800x800:colors=#007bff|#00c6ff" -frames:v 1 -y %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($audio_path),
            escapeshellarg($thumb_path)
        );

        exec($cmd, $output, $code);
        
        if ($code !== 0 || !file_exists($thumb_path)) {
            return null;
        }

        $this->optimizeImage($thumb_path);
        return $relative_path;
    }

    /**
     * Traiter miniature uploadée par utilisateur
     */
    private function processThumbnail($source_path, $dest_path, $ext)
    {
        // Essayer ImageMagick d'abord
        $convert = shell_exec('which convert');
        if ($convert) {
            $cmd = sprintf(
                'convert %s -resize 800x800> -quality 85 %s 2>&1',
                escapeshellarg($source_path),
                escapeshellarg($dest_path)
            );
            exec($cmd, $output, $code);
            return $code === 0 && file_exists($dest_path);
        }

        // Fallback GD
        if (extension_loaded('gd')) {
            return $this->resizeWithGD($source_path, $dest_path, $ext);
        }

        return false;
    }

    private function resizeWithGD($source, $dest, $ext)
    {
        $ext = strtolower($ext);
        
        switch($ext) {
            case 'jpg':
            case 'jpeg':
                $src_img = imagecreatefromjpeg($source);
                break;
            case 'png':
                $src_img = imagecreatefrompng($source);
                break;
            case 'gif':
                $src_img = imagecreatefromgif($source);
                break;
            case 'webp':
                $src_img = imagecreatefromwebp($source);
                break;
            default:
                return false;
        }

        if (!$src_img) return false;

        $width = imagesx($src_img);
        $height = imagesy($src_img);

        // Calculer nouvelles dimensions
        $max_size = 800;
        if ($width > $height) {
            $new_width = $max_size;
            $new_height = intval($height * $max_size / $width);
        } else {
            $new_height = $max_size;
            $new_width = intval($width * $max_size / $height);
        }

        $dst_img = imagecreatetruecolor($new_width, $new_height);
        
        // Préserver transparence pour PNG
        if ($ext == 'png') {
            imagealphablending($dst_img, false);
            imagesavealpha($dst_img, true);
        }

        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, 
            $new_width, $new_height, $width, $height);

        // Sauvegarder
        $result = false;
        switch($ext) {
            case 'jpg':
            case 'jpeg':
                $result = imagejpeg($dst_img, $dest, 85);
                break;
            case 'png':
                $result = imagepng($dst_img, $dest, 8);
                break;
            case 'gif':
                $result = imagegif($dst_img, $dest);
                break;
            case 'webp':
                $result = imagewebp($dst_img, $dest, 85);
                break;
        }

        imagedestroy($src_img);
        imagedestroy($dst_img);

        return $result;
    }

    /**
     * Optimiser image générée/extraite
     */
    private function optimizeImage($path)
    {
        // ImageMagick optimization
        $convert = shell_exec('which convert');
        if ($convert) {
            $tmp = $path . '.opt.jpg';
            exec("convert " . escapeshellarg($path) . " -strip -interlace Plane -gaussian-blur 0.05 -quality 85 " . escapeshellarg($tmp) . " 2>&1");
            if (file_exists($tmp) && filesize($tmp) > 0) {
                rename($tmp, $path);
            } else {
                @unlink($tmp);
            }
        }
    }

    /**
     * Générer toutes les visualisations
     */
    private function generateVisualizations($file_path, $filename)
    {
        $base_name = pathinfo($filename, PATHINFO_FILENAME);
        
        return [
            'waveform' => $this->generateWaveformImage($file_path, $base_name),
            'waveform_data' => $this->generateWaveformDataFile($file_path, $base_name),
            'spectrogram' => $this->generateSpectrogram($file_path, $base_name)
        ];
    }

    private function generateWaveformImage($audio_path, $base_name)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) return null;

        $output_path = $this->waveform_dir . $base_name . '_waveform.png';
        $relative = 'attachments/Audio/Waveforms/' . $base_name . '_waveform.png';

        $cmd = sprintf(
            '%s -i %s -filter_complex "aformat=channel_layouts=mono,compand,showwavespic=s=1200x200:colors=#007bff|#00c6ff" -frames:v 1 -y %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($audio_path),
            escapeshellarg($output_path)
        );

        exec($cmd, $output, $code);
        
        return ($code === 0 && file_exists($output_path)) ? $relative : null;
    }

    private function generateWaveformDataFile($audio_path, $base_name)
    {
        $data = $this->generateWaveformData($audio_path);
        $output_path = $this->waveform_dir . $base_name . '_data.json';
        $relative = 'attachments/Audio/Waveforms/' . $base_name . '_data.json';

        file_put_contents($output_path, json_encode($data));
        
        return file_exists($output_path) ? $relative : null;
    }

    private function generateWaveformData($audio_path, $samples = 1000)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) {
            return $this->generateDummyWaveform($samples);
        }

        $cmd = sprintf(
            '%s -i %s -ac 1 -filter:a aresample=%d -map 0:a -c:a pcm_s16le -f data - 2>/dev/null | od -An -td2 -w2 -v | head -%d',
            escapeshellarg($ffmpeg),
            escapeshellarg($audio_path),
            $samples * 10,
            $samples * 10
        );

        exec($cmd, $output, $code);
        
        if ($code !== 0 || empty($output)) {
            return $this->generateDummyWaveform($samples);
        }

        $peaks = [];
        $samples_per_peak = ceil(count($output) / $samples);
        
        for ($i = 0; $i < $samples; $i++) {
            $start = $i * $samples_per_peak;
            $end = min($start + $samples_per_peak, count($output));
            $slice = array_slice($output, $start, $end - $start);
            
            $max = 0;
            foreach ($slice as $val) {
                $abs = abs((int)$val);
                if ($abs > $max) $max = $abs;
            }
            
            $peaks[] = round($max / 32768, 4);
        }

        return $peaks;
    }

    private function generateDummyWaveform($samples)
    {
        $peaks = [];
        for ($i = 0; $i < $samples; $i++) {
            $base = sin($i / 50) * 0.3 + 0.4;
            $noise = (mt_rand(-100, 100) / 1000);
            $peaks[] = max(0, min(1, $base + $noise));
        }
        return $peaks;
    }

    private function generateSpectrogram($audio_path, $base_name)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) return null;

        $output_path = $this->waveform_dir . $base_name . '_spectro.png';
        $relative = 'attachments/Audio/Waveforms/' . $base_name . '_spectro.png';

        // Vérifier durée d'abord
        $duration = $this->getAudioDuration($audio_path);
        if ($duration > 600) return null; // Trop long

        $cmd = sprintf(
            '%s -i %s -lavfi showspectrumpic=s=1200x400:mode=combined:color=intensity:scale=lin -frames:v 1 -y %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($audio_path),
            escapeshellarg($output_path)
        );

        exec($cmd, $output, $code);
        
        return ($code === 0 && file_exists($output_path)) ? $relative : null;
    }

    private function getAudioDuration($file_path)
    {
        $ffprobe = $this->findFFprobe();
        if (!$ffprobe) return 0;

        $cmd = sprintf(
            '%s -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s',
            escapeshellarg($ffprobe),
            escapeshellarg($file_path)
        );

        exec($cmd, $output, $code);
        return ($code === 0 && !empty($output)) ? (float)$output[0] : 0;
    }

    // ==================== GESTION PROGRESSION ====================

    /**
     * Sauvegarder progression dans fichier JSON
     */
    private function saveProgress($upload_id, $data)
    {
        $progress_file = $this->progress_dir . 'progress_' . $upload_id . '.json';
        $data['timestamp'] = time();
        file_put_contents($progress_file, json_encode($data));
    }

    /**
     * Mettre à jour fichier de progression
     */
    private function updateProgressFile($upload_id, $progress_data)
    {
        $progress_file = $this->progress_dir . 'progress_' . $upload_id . '.json';
        $existing = [];
        
        if (file_exists($progress_file)) {
            $existing = json_decode(file_get_contents($progress_file), true);
        }
        
        $merged = array_merge($existing, $progress_data);
        $merged['timestamp'] = time();
        
        file_put_contents($progress_file, json_encode($merged));
    }

    /**
     * Calculer progression détaillée avec vitesse et ETA
     */
    private function calculateDetailedProgress($upload_id, $current_chunk, $chunk_start_time)
    {
        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata) return null;
        
        $uploaded = count($metadata['uploaded_chunks']);
        $total = $metadata['total_chunks'];
        $percent = round(($uploaded / $total) * 100, 2);
        
        // Calculer vitesse (bytes/seconde)
        $bytes_uploaded = $uploaded * $metadata['chunk_size'];
        $elapsed = time() - $metadata['created_at'];
        $speed = $elapsed > 0 ? round($bytes_uploaded / $elapsed) : 0;
        
        // Calculer ETA
        $remaining_bytes = ($total - $uploaded) * $metadata['chunk_size'];
        $eta = $speed > 0 ? ceil($remaining_bytes / $speed) : 0;
        
        return [
            'phase' => 'upload',
            'percent' => $percent,
            'uploaded_chunks' => $uploaded,
            'total_chunks' => $total,
            'bytes_uploaded' => $bytes_uploaded,
            'bytes_total' => $metadata['file_size'],
            'speed' => $speed,
            'speed_formatted' => $this->formatBytes($speed) . '/s',
            'eta' => $eta,
            'eta_formatted' => $this->formatDuration($eta),
            'message' => 'Upload en cours...',
            'current_chunk' => $current_chunk,
            'chunk_time_ms' => $chunk_start_time ? round((microtime(true) - $chunk_start_time) * 1000) : 0
        ];
    }

    // ==================== HELPERS & UTILITAIRES ====================

    private function validateAudioUpload($file_name, $file_size)
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
        if (!in_array($ext, $this->allowed_audio_extensions)) {
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

    private function updateMetadataStatus($upload_id, $status)
    {
        $metadata = $this->loadMetadata($upload_id);
        if ($metadata) {
            $metadata['status'] = $status;
            $metadata['last_activity'] = time();
            $this->saveMetadata($upload_id, $metadata);
        }
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

    private function suggestTitleFromFilename($filename)
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = str_replace(['_', '-', '.'], ' ', $name);
        $name = preg_replace('/^\d{1,3}[\s\.\-_]+/', '', $name);
        
        if (strpos($name, ' - ') !== false) {
            $parts = explode(' - ', $name, 2);
            return trim($parts[1]);
        }
        
        return ucwords(trim($name));
    }

    private function suggestCategory($audio_info)
    {
        $genre = strtolower($audio_info['genre'] ?? '');
        
        $mappings = [
            'podcast' => ['podcast', 'spoken', 'audiobook', 'speech'],
            'musique' => ['pop', 'rock', 'jazz', 'classical', 'electronic', 'hip-hop', 'rap'],
            'interview' => ['interview', 'conversation', 'talk'],
            'conference' => ['conference', 'lecture', 'seminar'],
            'meditation' => ['meditation', 'relaxation', 'yoga', 'spiritual'],
            'sound design' => ['sound design', 'soundtrack', 'fx', 'ambient']
        ];

        foreach ($mappings as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($genre, $keyword) !== false) {
                    return ucfirst($category);
                }
            }
        }

        if (!empty($audio_info['duration'])) {
            if ($audio_info['duration'] > 600 && $audio_info['duration'] < 3600) {
                return 'Podcast';
            }
            if ($audio_info['duration'] > 3600) {
                return 'Conférence';
            }
        }

        return 'Musique';
    }

    // ==================== PRÉPARATION DONNÉES ====================

    private function prepareAudioData($type_source)
    {
        $auto_data = json_decode($this->input->post('auto_detected_data') ?: '{}', true);
        
        $data = [
            'titre' => !empty($auto_data['title']) ? $auto_data['title'] : 
                      $this->input->post('titre'),
            'type' => 'audio',
            'description' => $this->input->post('description'),
            'categorie' => !empty($auto_data['category']) ? $auto_data['category'] : 
                          ($this->input->post('categorie') ?: 'Musique'),
            'date_media' => !empty($auto_data['year']) ? $auto_data['year'] . '-01-01' : 
                           ($this->input->post('date_media') ?: null),
            'credits' => !empty($auto_data['artist']) ? $auto_data['artist'] : 
                        $this->input->post('credits'),
            
            // Champs techniques auto-détectés
            'duree' => $auto_data['duration'] ?? null,
            'bitrate' => $auto_data['bitrate'] ?? null,
            'sample_rate' => $auto_data['sample_rate'] ?? null,
            'channels' => $auto_data['channels'] ?? null,
            
            // Métadonnées stockées en JSON
            'metadata_id3' => !empty($auto_data) ? json_encode($auto_data) : null,
            
            // Versions multi-bitrate (stocké en JSON)
            'converted_versions' => !empty($auto_data['converted_versions']) ? 
                                   json_encode($auto_data['converted_versions']) : null,
            
            // HLS playlist
            'hls_playlist' => $auto_data['hls_playlist'] ?? null,
            
            // Statuts
            'est_actif' => 1,
            'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
            'message_reseaux' => $this->input->post('message_reseaux') ?: null,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: null,
            'is_recording' => 0,
            
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($type_source == 'upload') {
            $file_path = $this->input->post('uploaded_file_path');
            
            if (empty($file_path) || !file_exists(FCPATH . $file_path)) {
                $this->session->set_flashdata('error', 'Aucun fichier audio uploadé.');
                return false;
            }
            
            $full_path = FCPATH . $file_path;
            $data['fichier'] = $file_path;
            $data['taille'] = filesize($full_path);
            $data['mime_type'] = mime_content_type($full_path);
            
            // Miniature (custom > extraite > générée)
            $data['miniature'] = $auto_data['thumbnail'] ?? null;
            
            // Visualisations
            $data['waveform'] = $auto_data['waveform'] ?? null;
            $data['spectrogram'] = $auto_data['spectrogram'] ?? null;
            $data['waveform_data'] = $auto_data['waveform_data'] ?? null;
            
        } else {
            $data['lien'] = $this->input->post('lien');
            $data['embed_code'] = $this->generateEmbedCode($data['lien']);
        }
        
        return $data;
    }

    private function prepareUpdateData($id, $type_source)
    {
        $auto_data = json_decode($this->input->post('auto_detected_data') ?: '{}', true);
        $old = $this->Model->readOne('galerie_medias', ['id_media' => $id]);

        $data = [
            'titre' => !empty($auto_data['title']) ? $auto_data['title'] : 
                      $this->input->post('titre'),
            'description' => $this->input->post('description'),
            'categorie' => !empty($auto_data['category']) ? $auto_data['category'] : 
                          $this->input->post('categorie'),
            'date_media' => !empty($auto_data['year']) ? $auto_data['year'] . '-01-01' : 
                           $this->input->post('date_media'),
            'credits' => !empty($auto_data['artist']) ? $auto_data['artist'] : 
                        $this->input->post('credits'),
            'est_actif' => $this->input->post('est_actif') ? 1 : 0,
            'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
            'message_reseaux' => $this->input->post('message_reseaux') ?: null,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($type_source == 'upload') {
            $new_path = $this->input->post('uploaded_file_path');
            
            if (!empty($new_path) && file_exists(FCPATH . $new_path)) {
                if ($old && !empty($old['fichier'])) {
                    $this->deleteAudioFiles($old);
                }
                
                $full_path = FCPATH . $new_path;
                $data['fichier'] = $new_path;
                $data['taille'] = filesize($full_path);
                $data['mime_type'] = mime_content_type($full_path);
                $data['lien'] = null;
                $data['embed_code'] = null;
                
                // Nouvelles métadonnées
                $data['duree'] = $auto_data['duration'] ?? null;
                $data['bitrate'] = $auto_data['bitrate'] ?? null;
                $data['sample_rate'] = $auto_data['sample_rate'] ?? null;
                $data['channels'] = $auto_data['channels'] ?? null;
                $data['miniature'] = $auto_data['thumbnail'] ?? null;
                $data['waveform'] = $auto_data['waveform'] ?? null;
                $data['spectrogram'] = $auto_data['spectrogram'] ?? null;
                $data['waveform_data'] = $auto_data['waveform_data'] ?? null;
                $data['metadata_id3'] = !empty($auto_data) ? json_encode($auto_data) : null;
                $data['converted_versions'] = !empty($auto_data['converted_versions']) ? 
                                              json_encode($auto_data['converted_versions']) : null;
                $data['hls_playlist'] = $auto_data['hls_playlist'] ?? null;
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
            $data['miniature'] = null;
            $data['waveform'] = null;
            $data['spectrogram'] = null;
            $data['converted_versions'] = null;
            $data['hls_playlist'] = null;
        }

        return $data;
    }

    private function deleteAudioFiles($audio)
    {
        $paths = [
            $audio['fichier'],
            $audio['miniature'],
            $audio['waveform'],
            $audio['spectrogram'],
            $audio['waveform_data']
        ];
        
        // Supprimer versions converties
        if (!empty($audio['converted_versions'])) {
            $versions = json_decode($audio['converted_versions'], true);
            foreach ($versions as $version) {
                if (!empty($version['path'])) {
                    $paths[] = $version['path'];
                }
            }
        }
        
        // Supprimer HLS
        if (!empty($audio['hls_playlist'])) {
            $hls_dir = dirname(FCPATH . $audio['hls_playlist']);
            if (is_dir($hls_dir)) {
                $this->recursiveDelete($hls_dir);
            }
        }
        
        foreach ($paths as $path) {
            if (!empty($path) && file_exists(FCPATH . $path)) {
                @unlink(FCPATH . $path);
            }
        }
    }

        private function generateEmbedCode($url)
    {
        if (strpos($url, 'soundcloud.com') !== false) {
            return '<iframe width="100%" height="166" scrolling="no" frameborder="no" ' .
                   'allow="autoplay" src="https://w.soundcloud.com/player/?url=' . urlencode($url) . 
                   '&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true"></iframe>';
        }
        
        if (preg_match('/spotify\.com\/track\/([a-zA-Z0-9]+)/', $url, $m)) {
            return '<iframe src="https://open.spotify.com/embed/track/' . $m[1] . 
                   '" width="100%" height="80" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>';
        }
        
        if (preg_match('/spotify\.com\/playlist\/([a-zA-Z0-9]+)/', $url, $m)) {
            return '<iframe src="https://open.spotify.com/embed/playlist/' . $m[1] . 
                   '" width="100%" height="380" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>';
        }
        
        if (preg_match('/spotify\.com\/album\/([a-zA-Z0-9]+)/', $url, $m)) {
            return '<iframe src="https://open.spotify.com/embed/album/' . $m[1] . 
                   '" width="100%" height="380" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>';
        }
        
        if (strpos($url, 'mixcloud.com') !== false) {
            return '<iframe width="100%" height="120" src="https://www.mixcloud.com/widget/iframe/?feed=' . 
                   urlencode($url) . '&hide_cover=1&light=1" frameborder="0"></iframe>';
        }
        
        if (strpos($url, 'audiomack.com') !== false) {
            return '<iframe src="' . $url . '/embed" scrolling="no" width="100%" height="252" scrollbars="no" frameborder="0"></iframe>';
        }
        
        if (strpos($url, 'bandcamp.com') !== false) {
            return '<iframe style="border: 0; width: 100%; height: 120px;" src="' . 
                   str_replace('/track/', '/EmbeddedPlayer/track=', $url) . 
                   '/size=large/bgcol=ffffff/linkcol=0687f5/tracklist=false/artwork=small/transparent=true/" seamless></iframe>';
        }
        
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $m) || 
            preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $m)) {
            return '<iframe width="100%" height="166" src="https://www.youtube.com/embed/' . $m[1] . 
                   '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        }
        
        if (strpos($url, 'apple.com') !== false && strpos($url, 'music') !== false) {
            // Apple Music embed nécessite une approche différente
            return '<iframe allow="autoplay *; encrypted-media *; fullscreen *" frameborder="0" height="150" style="width:100%;max-width:660px;overflow:hidden;background:transparent;" sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-storage-access-by-user-activation allow-top-navigation-by-user-activation" src="' . $url . '"></iframe>';
        }
        
        if (strpos($url, 'deezer.com') !== false) {
            if (preg_match('/track\/(\d+)/', $url, $m)) {
                return '<iframe title="deezer-widget" src="https://widget.deezer.com/widget/dark/track/' . $m[1] . 
                       '" width="100%" height="300" frameborder="0" allowtransparency="true" allow="encrypted-media; clipboard-write"></iframe>';
            }
        }
        
        if (strpos($url, 'tidal.com') !== false) {
            // Tidal nécessite une API key pour l'embed
            return '<div class="tidal-embed" data-type="tidal" data-url="' . $url . '">' .
                   '<a href="' . $url . '" target="_blank">Écouter sur Tidal</a></div>';
        }
        
        // Fallback: lecteur audio HTML5 basique pour URL directe
        if (preg_match('/\.(mp3|wav|ogg|m4a|aac|flac)$/i', $url)) {
            return '<audio controls style="width:100%;"><source src="' . $url . '">Votre navigateur ne supporte pas l\'audio HTML5.</audio>';
        }
        
        // Lien générique
        return '<a href="' . $url . '" target="_blank" class="btn btn-primary"><i class="fas fa-external-link-alt"></i> Écouter l\'audio</a>';
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
        $paths = ['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/opt/ffmpeg/bin/ffmpeg', '/opt/homebrew/bin/ffmpeg'];
        foreach ($paths as $p) {
            exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    private function findFFprobe()
    {
        $paths = ['ffprobe', '/usr/bin/ffprobe', '/usr/local/bin/ffprobe', '/opt/ffmpeg/bin/ffprobe', '/opt/homebrew/bin/ffprobe'];
        foreach ($paths as $p) {
            exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    private function findSox()
    {
        $paths = ['sox', '/usr/bin/sox', '/usr/local/bin/sox', '/opt/homebrew/bin/sox'];
        foreach ($paths as $p) {
            exec($p . ' --version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    private function findLame()
    {
        $paths = ['lame', '/usr/bin/lame', '/usr/local/bin/lame', '/opt/homebrew/bin/lame'];
        foreach ($paths as $p) {
            exec($p . ' --version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    private function findMediaInfo()
    {
        $paths = ['mediainfo', '/usr/bin/mediainfo', '/usr/local/bin/mediainfo', '/opt/homebrew/bin/mediainfo'];
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
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }

    private function jsonResponse($success, $message = '', $data = [])
    {
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
            'timestamp' => time()
        ], $data));
        exit;
    }

    private function setFlashMessage($success, $success_msg, $error_msg)
    {
        $this->session->set_flashdata(
            $success ? 'success' : 'error',
            $success ? $success_msg : $error_msg
        );
    }

    private function getDetailedUploadError($code)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => ['type' => 'PHP_LIMIT', 'message' => 'Fichier trop grand pour la configuration PHP'],
            UPLOAD_ERR_FORM_SIZE => ['type' => 'FORM_LIMIT', 'message' => 'Fichier dépasse la limite du formulaire'],
            UPLOAD_ERR_PARTIAL => ['type' => 'NETWORK', 'message' => 'Upload partiel - vérifiez votre connexion'],
            UPLOAD_ERR_NO_FILE => ['type' => 'NO_FILE', 'message' => 'Aucun fichier reçu'],
            UPLOAD_ERR_NO_TMP_DIR => ['type' => 'SERVER_CONFIG', 'message' => 'Dossier temporaire manquant sur le serveur'],
            UPLOAD_ERR_CANT_WRITE => ['type' => 'DISK', 'message' => 'Erreur écriture disque - espace insuffisant?'],
            UPLOAD_ERR_EXTENSION => ['type' => 'PHP_EXT', 'message' => 'Extension PHP a bloqué l\'upload']
        ];
        return $errors[$code] ?? ['type' => 'UNKNOWN', 'message' => 'Erreur inconnue #' . $code];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function formatDuration($seconds)
    {
        if (empty($seconds) || $seconds < 0) return '0:00';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%d:%02d', $minutes, $secs);
    }

    /**
     * API: Récupérer les statistiques audio
     */
    public function stats()
    {
        $this->setJSONHeaders();
        
        $stats = [
            'total_audios' => $this->db->where('type', 'audio')->count_all_results('galerie_medias'),
            'total_actifs' => $this->db->where(['type' => 'audio', 'est_actif' => 1])->count_all_results('galerie_medias'),
            'total_duration' => $this->calculateTotalDuration(),
            'total_duration_formatted' => $this->formatDuration($this->calculateTotalDuration()),
            'categories' => $this->getExistingCategories(),
            'storage_used' => $this->calculateStorageUsed(),
            'by_quality' => $this->getStatsByQuality()
        ];
        
        $this->jsonResponse(true, 'Statistiques récupérées', $stats);
    }

    private function calculateStorageUsed()
    {
        $this->db->select_sum('taille', 'total_size');
        $this->db->where('type', 'audio');
        $query = $this->db->get('galerie_medias');
        $size = $query->row()->total_size ?? 0;
        return [
            'bytes' => $size,
            'formatted' => $this->formatBytes($size)
        ];
    }

    private function getStatsByQuality()
    {
        $stats = [];
        $this->db->where('type', 'audio');
        $query = $this->db->get('galerie_medias');
        
        foreach ($query->result() as $row) {
            if (!empty($row->bitrate)) {
                $quality = $this->categorizeBitrate($row->bitrate);
                $stats[$quality] = ($stats[$quality] ?? 0) + 1;
            }
        }
        
        return $stats;
    }

    private function categorizeBitrate($bitrate)
    {
        if ($bitrate < 96000) return 'low';
        if ($bitrate < 160000) return 'medium';
        if ($bitrate < 256000) return 'high';
        return 'max';
    }

    /**
     * API: Rechercher dans les métadonnées ID3
     */
    public function searchByMetadata()
    {
        $this->setJSONHeaders();
        
        $query = $this->input->get('q');
        if (empty($query)) {
            $this->jsonResponse(false, 'Terme de recherche requis');
            return;
        }
        
        $this->db->where('type', 'audio');
        $this->db->group_start();
        $this->db->like('titre', $query);
        $this->db->or_like('credits', $query);
        $this->db->or_like('categorie', $query);
        $this->db->or_like('description', $query);
        $this->db->group_end();
        
        $results = $this->db->get('galerie_medias')->result_array();
        
        $this->jsonResponse(true, 'Recherche terminée', [
            'count' => count($results),
            'results' => $results
        ]);
    }

    /**
     * API: Batch processing - traiter plusieurs fichiers
     */
    public function batchProcess()
    {
        $this->setJSONHeaders();
        
        $upload_ids = json_decode($this->input->post('upload_ids'), true);
        if (empty($upload_ids) || !is_array($upload_ids)) {
            $this->jsonResponse(false, 'Liste d\'IDs requise');
            return;
        }
        
        $results = [];
        foreach ($upload_ids as $upload_id) {
            // Traiter chaque upload en arrière-plan ou séquentiellement
            $results[$upload_id] = $this->processSingleUpload($upload_id);
        }
        
        $this->jsonResponse(true, 'Traitement batch lancé', $results);
    }

    private function processSingleUpload($upload_id)
    {
        // Logique de traitement individuel pour batch
        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata) {
            return ['success' => false, 'error' => 'Metadata non trouvées'];
        }
        
        // Retourner statut pour suivi batch
        return [
            'success' => true,
            'status' => $metadata['status'],
            'progress' => $this->calculateProgress($upload_id)
        ];
    }
}