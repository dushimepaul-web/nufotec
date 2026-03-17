<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur Video Ultra-Intelligent v4.0
 * Technologies: Auto-detection, FFprobe, Thumbnail intelligente, Streaming adaptatif
 * Conforme structure BDD: galerie_medias (même table que Audio)
 */
class Video extends MY_Controller {

    private $upload_dir;
    private $final_dir;
    private $thumbs_dir;
    private $posters_dir;
    private $previews_dir;
    private $chunk_size;
    private $max_file_size;
    private $allowed_extensions;
    private $allowed_image_extensions;
    private $session_timeout;

    // Résolutions pour traitement vidéo
    private $video_resolutions = [
        '1080p' => ['width' => 1920, 'height' => 1080, 'bitrate' => '5000k'],
        '720p'  => ['width' => 1280, 'height' => 720,  'bitrate' => '2500k'],
        '480p'  => ['width' => 854,  'height' => 480,  'bitrate' => '1000k'],
        '360p'  => ['width' => 640,  'height' => 360,  'bitrate' => '500k']
    ];

    function __construct()
    {
        parent::__construct();
        is_admin();
        
        // Configuration des chemins
        $this->upload_dir = FCPATH . 'uploads/temp/video/';
        $this->final_dir = FCPATH . 'attachments/Video/';
        $this->thumbs_dir = FCPATH . 'attachments/Video/Thumbs/';
        $this->posters_dir = FCPATH . 'attachments/Video/Posters/';
        $this->previews_dir = FCPATH . 'attachments/Video/Previews/';
        
        // Configuration technique
        $this->chunk_size = 2 * 1024 * 1024; // 2MB chunks
        $this->max_file_size = 10 * 1024 * 1024 * 1024; // 10GB max
        $this->session_timeout = 3600; // 1 heure
        
        // Extensions supportées (tous formats modernes)
        $this->allowed_extensions = [
            'mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 
            'flv', 'm4v', '3gp', 'wmv', 'ts', 'mts', 'm2ts',
            'mpg', 'mpeg', 'vob', 'ogv', 'divx', 'xvid'
        ];
        
        $this->allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Création des dossiers
        foreach ([$this->upload_dir, $this->final_dir, $this->thumbs_dir, 
                  $this->posters_dir, $this->previews_dir] as $dir) {
            $this->ensureDirectoryExists($dir);
        }
        
        // Configuration PHP
        $this->configurePHP();
        $this->cleanupExpiredSessions();
    }

    // ==================== CONFIGURATION AVANCÉE ====================

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
        $data['videos'] = $this->Model->read('galerie_medias', 
            ['type' => 'video'], 
            'id_media', 
            'DESC'
        );
        
        $data['categories'] = $this->getExistingCategories();
        $data['total_duration'] = $this->calculateTotalDuration();
        
        $this->load->view('Video_View', $data);
    }

    /**
     * API: Diagnostic serveur complet
     */
    public function diagnostics()
    {
        $this->setJSONHeaders();
        
        $info = [
            'php_version' => PHP_VERSION,
            'video_tools' => [
                'ffmpeg' => $this->findFFmpeg() ? 'Disponible' : 'Non disponible',
                'ffprobe' => $this->findFFprobe() ? 'Disponible' : 'Non disponible',
                'convert' => shell_exec('which convert') ? 'ImageMagick OK' : 'Non dispo'
            ],
            'capabilities' => [
                'thumbnail_generation' => (bool)$this->findFFmpeg(),
                'video_conversion' => (bool)$this->findFFmpeg(),
                'multi_resolution' => (bool)$this->findFFmpeg(),
                'gif_preview' => (bool)$this->findFFmpeg(),
                'auto_detection' => (bool)$this->findFFprobe()
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
                'resolutions' => array_keys($this->video_resolutions)
            ],
            'directories' => [
                'upload_dir_writable' => is_writable($this->upload_dir),
                'final_dir_writable' => is_writable($this->final_dir),
                'thumbs_dir_writable' => is_writable($this->thumbs_dir),
                'disk_free' => $this->formatBytes(@disk_free_space($this->final_dir))
            ],
            'timestamp' => time()
        ];
        
        echo json_encode($info);
    }

    /**
     * Streaming vidéo avec support range requests (comme YouTube)
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
        
        // Headers pour streaming
        header('Content-Type: ' . $mime_type);
        header('Accept-Ranges: bytes');
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        
        // Gestion du Range (seeking vidéo)
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
        } else {
            header('Content-Length: ' . $file_size);
        }
        
        // Streaming avec buffer optimisé
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

    // ==================== API UPLOAD CHUNKED INTELLIGENT ====================

    /**
     * Étape 1: Initialiser avec pré-analyse
     */
    public function initUpload()
    {
        $this->setJSONHeaders();
        
        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');
        $file_hash = $this->input->post('file_hash') ?: null;

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
            'pre_analysis' => [
                'filename_suggestion' => $this->suggestTitleFromFilename($file_name),
                'expected_format' => strtolower(pathinfo($file_name, PATHINFO_EXTENSION))
            ]
        ];

        $this->saveMetadata($upload_id, $metadata);

        $this->jsonResponse(true, 'Session initialisée', [
            'upload_id' => $upload_id,
            'chunk_size' => $this->chunk_size,
            'total_chunks' => $total_chunks,
            'max_retries' => 3,
            'supports_processing' => (bool)$this->findFFmpeg()
        ]);
    }

    /**
     * Upload de miniature personnalisée
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

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            $this->jsonResponse(false, 'Type d\'image non supporté');
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $thumb_name = 'thumb_' . date("YmdHis") . '_' . uniqid() . '.' . $ext;
        $thumb_path = $this->thumbs_dir . $thumb_name;
        $relative_path = 'attachments/Video/Thumbs/' . $thumb_name;

        if (!$this->processThumbnail($file['tmp_name'], $thumb_path, $ext)) {
            if (!move_uploaded_file($file['tmp_name'], $thumb_path)) {
                $this->jsonResponse(false, 'Erreur sauvegarde miniature');
                return;
            }
        }

        $this->jsonResponse(true, 'Miniature uploadée', [
            'thumbnail_path' => $relative_path,
            'thumbnail_url' => base_url($relative_path)
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
        
        $progress = $this->calculateProgress($upload_id);
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

        $this->jsonResponse(true, 'Statut récupéré', [
            'upload_id' => $upload_id,
            'status' => $metadata['status'],
            'progress' => $this->calculateProgress($upload_id),
            'missing_chunks' => array_values($missing)
        ]);
    }

    /**
     * Étape 4: Finaliser avec traitement vidéo avancé
     */
    public function completeUpload()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $user_description = $this->input->post('description');
        $custom_thumbnail = $this->input->post('custom_thumbnail');
        $generate_preview = $this->input->post('generate_preview') !== 'false';
        
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

        // Assembler
        $final_name = $this->generateFinalName($metadata['file_name']);
        $final_path = $this->final_dir . $final_name;
        $relative_path = 'attachments/Video/' . $final_name;

        $assembled = $this->assembleChunks($upload_id, $final_path, $metadata);
        
        if (!$assembled['success']) {
            $this->jsonResponse(false, 'Erreur assemblage: ' . $assembled['message']);
            return;
        }

        // Analyse complète
        $this->updateMetadataStatus($upload_id, 'processing');
        
        // 1. Analyse FFprobe complète
        $video_info = $this->analyzeWithFFprobe($final_path);
        
        // 2. Générer miniatures multiples
        $thumbnails = $this->generateThumbnails($final_path, $final_name, $video_info['duration']);
        
        // 3. Générer GIF preview si demandé et vidéo courte
        $gif_preview = null;
        if ($generate_preview && $video_info['duration'] > 0 && $video_info['duration'] <= 60) {
            $gif_preview = $this->generateGifPreview($final_path, $final_name);
        }
        
        // 4. Déterminer miniature finale
        $final_thumbnail = $custom_thumbnail ?: $thumbnails['poster'] ?: $thumbnails['default'];

        // Nettoyer session
        $this->cleanupUploadSession($upload_id);

        // Préparer réponse
        $suggested_title = $video_info['title'] ?: $metadata['pre_analysis']['filename_suggestion'];
        
        $response_data = [
            'file_path' => $relative_path,
            'file_name' => $final_name,
            'file_size' => filesize($final_path),
            'file_size_formatted' => $this->formatBytes(filesize($final_path)),
            
            // Métadonnées techniques
            'duration' => $video_info['duration'],
            'duration_formatted' => $this->formatDuration($video_info['duration']),
            'width' => $video_info['width'],
            'height' => $video_info['height'],
            'bitrate' => $video_info['bitrate'],
            'fps' => $video_info['fps'],
            'codec' => $video_info['codec'],
            'format' => $video_info['format'],
            
            // Métadonnées ID3
            'title' => $video_info['title'],
            'artist' => $video_info['artist'],
            'date' => $video_info['date'],
            'description_video' => $video_info['description'],
            
            // Miniatures générées
            'thumbnail' => $final_thumbnail,
            'thumbnail_type' => $custom_thumbnail ? 'custom' : 'generated',
            'thumbnails' => $thumbnails,
            'gif_preview' => $gif_preview,
            
            // Pour formulaire
            'suggested_data' => [
                'titre' => $suggested_title,
                'credits' => $video_info['artist'] ?: 'Auteur inconnu',
                'categorie' => $this->suggestCategory($video_info),
                'description' => $user_description,
                'date_media' => $video_info['date']
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
        }

        $this->jsonResponse(true, 'Upload annulé');
    }

    // ==================== CRUD ====================

    public function Create()
    {
        $this->form_validation->set_rules('description', 'Description', 'trim|max_length[2000]');
        $type_source = $this->input->post('type_source');
        
        if ($type_source == 'link') {
            $this->form_validation->set_rules('lien', 'Lien vidéo', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('video'));
            return;
        }

        $data = $this->prepareVideoData($type_source);
        
        if (!$data) {
            redirect(base_url('video'));
            return;
        }

        $rsp = $this->Model->create('galerie_medias', $data);

        $this->setFlashMessage($rsp, 'Vidéo créée avec succès.', 'Erreur lors de la création.');
        redirect(base_url('video'));
    }

    public function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('description', 'Description', 'trim|max_length[2000]');
        $type_source = $this->input->post('type_source');
        
        if ($type_source == 'link') {
            $this->form_validation->set_rules('lien', 'Lien vidéo', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('video'));
            return;
        }

        $data = $this->prepareUpdateData($id, $type_source);
        
        if (!$data) {
            redirect(base_url('video'));
            return;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);

        $this->setFlashMessage($rsp, 'Vidéo mise à jour avec succès.', 'Erreur lors de la mise à jour.');
        redirect(base_url('video'));
    }

    public function Delete()
    {
        $id = $this->input->post('id');
        $video = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            'est_actif' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp && $video) {
            $this->deleteVideoFiles($video);
            $this->session->set_flashdata('success', 'Vidéo supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        
        redirect(base_url('video'));
    }

    public function ChangeStatus()
    {
        $id = $this->input->post('id');
        $est_actif = $this->input->post('est_actif');
        $status = ($est_actif == 1) ? 0 : 1;
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], ['est_actif' => $status]);

        $this->setFlashMessage($rsp, 'Statut mis à jour avec succès.', 'Erreur lors de la mise à jour du statut.');
        redirect(base_url('video'));    
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

    // ==================== ANALYSE & TRAITEMENT VIDÉO ====================

    /**
     * Analyse complète avec FFprobe
     */
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
        $video_stream = null;
        $audio_stream = null;
        
        foreach ($data['streams'] ?? [] as $stream) {
            if ($stream['codec_type'] === 'video' && !$video_stream) {
                $video_stream = $stream;
            }
            if ($stream['codec_type'] === 'audio' && !$audio_stream) {
                $audio_stream = $stream;
            }
        }

        $tags = $format['tags'] ?? [];

        return [
            // Durée et taille
            'duration' => (float)($format['duration'] ?? 0),
            'bitrate' => (int)($format['bit_rate'] ?? 0),
            'format' => $format['format_name'] ?? pathinfo($file_path, PATHINFO_EXTENSION),
            'size' => (int)($format['size'] ?? filesize($file_path)),
            
            // Vidéo
            'width' => (int)($video_stream['width'] ?? 0),
            'height' => (int)($video_stream['height'] ?? 0),
            'fps' => $this->calculateFPS($video_stream),
            'codec' => $video_stream['codec_name'] ?? null,
            'pixel_format' => $video_stream['pix_fmt'] ?? null,
            
            // Audio
            'audio_codec' => $audio_stream['codec_name'] ?? null,
            'audio_channels' => (int)($audio_stream['channels'] ?? 0),
            'sample_rate' => (int)($audio_stream['sample_rate'] ?? 0),
            
            // Métadonnées
            'title' => $tags['title'] ?? $tags['TITLE'] ?? null,
            'artist' => $tags['artist'] ?? $tags['ARTIST'] ?? $tags['encoder'] ?? null,
            'date' => $tags['date'] ?? $tags['DATE'] ?? $tags['creation_time'] ?? null,
            'description' => $tags['description'] ?? $tags['comment'] ?? null,
            
            'raw_tags' => $tags
        ];
    }

    private function analyzeBasic($file_path)
    {
        $size = filesize($file_path);
        
        return [
            'duration' => 0,
            'bitrate' => 0,
            'format' => pathinfo($file_path, PATHINFO_EXTENSION),
            'size' => $size,
            'width' => 0,
            'height' => 0,
            'fps' => 0,
            'codec' => 'unknown',
            'title' => null,
            'artist' => null,
            'date' => null,
            'note' => 'Analyse basique - FFprobe non disponible'
        ];
    }

    private function calculateFPS($video_stream)
    {
        if (empty($video_stream)) return 0;
        
        // Format: "30000/1001" ou "30"
        $r_frame_rate = $video_stream['r_frame_rate'] ?? '0/1';
        
        if (strpos($r_frame_rate, '/') !== false) {
            list($num, $den) = explode('/', $r_frame_rate);
            return $den > 0 ? round($num / $den, 2) : 0;
        }
        
        return (float)$r_frame_rate;
    }

    /**
     * Générer multiples miniatures
     */
   private function generateThumbnails($video_path, $filename, $duration)
{
    $ffmpeg = $this->findFFmpeg();
    if (!$ffmpeg) {
        return ['default' => null, 'poster' => null, 'sprites' => []];
    }

    $base_name = pathinfo($filename, PATHINFO_FILENAME);
    $thumbnails = [
        'default' => null,
        'poster' => null,
        'sprites' => [],
        'timeline' => []
    ];

    // 1. Miniature par défaut (1 seconde)
    $default_name = $base_name . '_thumb.jpg';
    $default_path = $this->thumbs_dir . $default_name;
    
    $cmd = sprintf(
        '%s -i %s -ss 00:00:01 -vframes 1 -q:v 2 -vf "scale=480:-1" -y %s 2>&1',
        escapeshellarg($ffmpeg),
        escapeshellarg($video_path),
        escapeshellarg($default_path)
    );
    exec($cmd, $output, $code);
    
    if ($code === 0 && file_exists($default_path)) {
        $thumbnails['default'] = 'attachments/Video/Thumbs/' . $default_name;
    }

    // 2. Poster haute qualité (meilleur frame)
    $poster_name = $base_name . '_poster.jpg';
    $poster_path = $this->posters_dir . $poster_name;
    
    // Essayer à 10%, 30%, 50% de la durée pour éviter intros/outros noires
    $positions = [0.1, 0.3, 0.5];
    foreach ($positions as $pos) {
        $ss_time = $duration * $pos;
        $cmd = sprintf(
            '%s -i %s -ss %f -vframes 1 -q:v 2 -vf "scale=1280:-1" -y %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($video_path),
            $ss_time,
            escapeshellarg($poster_path)
        );
        exec($cmd, $output, $code);
        
        if ($code === 0 && file_exists($poster_path) && filesize($poster_path) > 10000) {
            $thumbnails['poster'] = 'attachments/Video/Posters/' . $poster_name;
            break;
        }
    }

    // 3. Sprites pour timeline (si vidéo > 30s)
    if ($duration > 30) {
        $sprite_count = min(10, ceil($duration / 30));
        for ($i = 1; $i <= $sprite_count; $i++) {
            $ss_time = ($duration / ($sprite_count + 1)) * $i;
            $sprite_name = $base_name . '_sprite_' . $i . '.jpg';
            $sprite_path = $this->thumbs_dir . $sprite_name;
            
            $cmd = sprintf(
                '%s -i %s -ss %f -vframes 1 -q:v 5 -vf "scale=160:-1" -y %s 2>&1',
                escapeshellarg($ffmpeg),
                escapeshellarg($video_path),
                $ss_time,
                escapeshellarg($sprite_path)
            );
            exec($cmd, $output, $code);
            
            if ($code === 0 && file_exists($sprite_path)) {
                $thumbnails['sprites'][] = 'attachments/Video/Thumbs/' . $sprite_name;
                $thumbnails['timeline'][] = round($ss_time);
            }
        }
    }

    return $thumbnails;
}
    /**
     * Générer GIF preview animé
     */
    private function generateGifPreview($video_path, $filename)
    {
        $ffmpeg = $this->findFFmpeg();
        if (!$ffmpeg) return null;

        $base_name = pathinfo($filename, PATHINFO_FILENAME);
        $gif_name = $base_name . '_preview.gif';
        $gif_path = $this->previews_dir . $gif_name;
        
        // Générer 3 secondes de preview à 10% de la vidéo
        $cmd = sprintf(
            '%s -i %s -ss 00:00:00 -t 3 -vf "fps=10,scale=480:-1:flags=lanczos,split[s0][s1];[s0]palettegen=max_colors=64[p];[s1][p]paletteuse=dither=bayer" -loop 0 -y %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($video_path),
            escapeshellarg($gif_path)
        );
        
        exec($cmd, $output, $code);
        
        return ($code === 0 && file_exists($gif_path) && filesize($gif_path) > 1000) 
            ? 'attachments/Video/Previews/' . $gif_name 
            : null;
    }

    /**
     * Traiter miniature uploadée
     */
    private function processThumbnail($source_path, $dest_path, $ext)
    {
        // ImageMagick
        $convert = shell_exec('which convert');
        if ($convert) {
            $cmd = sprintf(
                'convert %s -resize 1280x720> -quality 90 %s 2>&1',
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

        // Calculer dimensions 16:9
        $target_width = 1280;
        $target_height = 720;
        
        $ratio = min($target_width / $width, $target_height / $height);
        $new_width = intval($width * $ratio);
        $new_height = intval($height * $ratio);

        $dst_img = imagecreatetruecolor($new_width, $new_height);
        
        if ($ext == 'png') {
            imagealphablending($dst_img, false);
            imagesavealpha($dst_img, true);
        }

        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, 
            $new_width, $new_height, $width, $height);

        $result = false;
        switch($ext) {
            case 'jpg':
            case 'jpeg':
                $result = imagejpeg($dst_img, $dest, 90);
                break;
            case 'png':
                $result = imagepng($dst_img, $dest, 8);
                break;
            case 'gif':
                $result = imagegif($dst_img, $dest);
                break;
            case 'webp':
                $result = imagewebp($dst_img, $dest, 90);
                break;
        }

        imagedestroy($src_img);
        imagedestroy($dst_img);

        return $result;
    }

    // ==================== HELPERS & UTILITAIRES ====================

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
        return 'video_' . uniqid() . '_' . bin2hex(random_bytes(8));
    }

    private function generateFinalName($original_name)
    {
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        // Normaliser en mp4 pour compatibilité
        if (in_array($ext, ['avi', 'mkv', 'mov', 'wmv'])) {
            $ext = 'mp4';
        }
        return date("YmdHis") . '_' . uniqid() . '_video.' . $ext;
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

    private function suggestCategory($video_info)
    {
        $title = strtolower($video_info['title'] ?? '');
        $desc = strtolower($video_info['description'] ?? '');
        
        $keywords = [
            'tutoriel' => ['tutoriel', 'tutorial', 'comment', 'apprendre', 'cours'],
            'interview' => ['interview', 'entretien', 'discussion', 'talk'],
            'documentaire' => ['documentaire', 'documentary', 'reportage'],
            'musique' => ['clip', 'music', 'musique', 'concert', 'chanson'],
            'sport' => ['sport', 'football', 'basket', 'match', 'course'],
            'gaming' => ['game', 'gaming', 'jeu', 'gameplay', 'let\'s play'],
            'vlog' => ['vlog', 'daily', 'jour', 'vie quotidienne']
        ];

        foreach ($keywords as $category => $words) {
            foreach ($words as $word) {
                if (strpos($title, $word) !== false || strpos($desc, $word) !== false) {
                    return ucfirst($category);
                }
            }
        }

        // Par durée
        $duration = $video_info['duration'] ?? 0;
        if ($duration > 600 && $duration < 1800) {
            return 'Tutoriel';
        }
        if ($duration > 1800) {
            return 'Documentaire';
        }

        return 'Vidéo';
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
            
            return [
                'success' => true,
                'time_ms' => round((microtime(true) - $start_time) * 1000)
            ];
            
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

    // ==================== PRÉPARATION DONNÉES ====================

    private function prepareVideoData($type_source)
    {
        $auto_data = json_decode($this->input->post('auto_detected_data') ?: '{}', true);
        
        $data = [
            'titre' => !empty($auto_data['title']) ? $auto_data['title'] : 
                      $this->input->post('titre'),
            'type' => 'video',
            'description' => $this->input->post('description'),
            'categorie' => !empty($auto_data['category']) ? $auto_data['category'] : 
                          ($this->input->post('categorie') ?: 'Vidéo'),
            'date_media' => !empty($auto_data['date']) ? $auto_data['date'] : 
                           ($this->input->post('date_media') ?: null),
            'credits' => !empty($auto_data['artist']) ? $auto_data['artist'] : 
                        $this->input->post('credits'),
            
            // Champs techniques
            'duree' => $auto_data['duration'] ?? null,
            'taille' => $auto_data['file_size'] ?? null,
            
            // Stockage métadonnées complètes
            'metadata_id3' => !empty($auto_data) ? json_encode($auto_data) : null,
            
            // Miniature
            'miniature' => $auto_data['thumbnail'] ?? null,
            
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
                $this->session->set_flashdata('error', 'Aucun fichier vidéo uploadé.');
                return false;
            }
            
            $full_path = FCPATH . $file_path;
            $data['fichier'] = $file_path;
            $data['taille'] = filesize($full_path);
            $data['mime_type'] = mime_content_type($full_path);
            
        } else {
            $data['lien'] = $this->input->post('lien');
            $data['embed_code'] = $this->generateEmbedCode($data['lien']);
            $data['miniature'] = $this->extractVideoThumbnail($data['lien']);
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
            'date_media' => !empty($auto_data['date']) ? $auto_data['date'] : 
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
                    $this->deleteVideoFiles($old);
                }
                
                $full_path = FCPATH . $new_path;
                $data['fichier'] = $new_path;
                $data['taille'] = filesize($full_path);
                $data['mime_type'] = mime_content_type($full_path);
                $data['lien'] = null;
                $data['embed_code'] = null;
                $data['duree'] = $auto_data['duration'] ?? null;
                $data['miniature'] = $auto_data['thumbnail'] ?? null;
                $data['metadata_id3'] = !empty($auto_data) ? json_encode($auto_data) : null;
            }
        } elseif ($type_source == 'link') {
            $new_lien = $this->input->post('lien');
            
            if ($old && !empty($old['fichier'])) {
                $this->deleteVideoFiles($old);
            }
            
            $data['lien'] = $new_lien;
            $data['embed_code'] = $this->generateEmbedCode($new_lien);
            $data['miniature'] = $this->extractVideoThumbnail($new_lien);
            $data['fichier'] = null;
            $data['taille'] = null;
            $data['mime_type'] = null;
            $data['duree'] = null;
        }

        return $data;
    }

    private function deleteVideoFiles($video)
    {
        $paths = [
            $video['fichier'],
            $video['miniature']
        ];
        
        // Supprimer aussi les thumbnails générés
        if (!empty($video['fichier'])) {
            $base = pathinfo($video['fichier'], PATHINFO_FILENAME);
            $patterns = [
                $this->thumbs_dir . $base . '*',
                $this->posters_dir . $base . '*',
                $this->previews_dir . $base . '*'
            ];
            
            foreach ($patterns as $pattern) {
                foreach (glob($pattern) as $file) {
                    @unlink($file);
                }
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
        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return '<iframe width="100%" height="400" src="https://www.youtube.com/embed/' . $m[1] . '" frameborder="0" allowfullscreen></iframe>';
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return '<iframe src="https://player.vimeo.com/video/' . $m[1] . '" width="100%" height="400" frameborder="0" allowfullscreen></iframe>';
        }
        
        // Dailymotion
        if (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9]+)/', $url, $m)) {
            return '<iframe src="https://www.dailymotion.com/embed/video/' . $m[1] . '" width="100%" height="400" frameborder="0" allowfullscreen></iframe>';
        }
        
        // Facebook
        if (strpos($url, 'facebook.com') !== false) {
            return '<iframe src="https://www.facebook.com/plugins/video.php?href=' . urlencode($url) . '" width="100%" height="400" frameborder="0" allowfullscreen></iframe>';
        }
        
        return null;
    }

    private function extractVideoThumbnail($url)
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/maxresdefault.jpg";
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            $vimeo_data = @file_get_contents("https://vimeo.com/api/v2/video/{$m[1]}.json");
            if ($vimeo_data) {
                $data = json_decode($vimeo_data, true);
                return $data[0]['thumbnail_large'] ?? null;
            }
        }
        
        // Dailymotion
        if (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9]+)/', $url, $m)) {
            return "https://www.dailymotion.com/thumbnail/video/{$m[1]}";
        }
        
        return null;
    }

    private function getExistingCategories()
    {
        $this->db->select('DISTINCT(categorie) as cat');
        $this->db->where('type', 'video');
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
        $this->db->where('type', 'video');
        $this->db->where('est_actif', 1);
        $query = $this->db->get('galerie_medias');
        
        return $query->row()->total_duration ?? 0;
    }

    // ==================== OUTILS VIDÉO ====================

    private function findFFmpeg()
    {
        $paths = ['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/opt/ffmpeg/bin/ffmpeg'];
        foreach ($paths as $p) {
            exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    private function findFFprobe()
    {
        $paths = ['ffprobe', '/usr/bin/ffprobe', '/usr/local/bin/ffprobe', '/opt/ffmpeg/bin/ffprobe'];
        foreach ($paths as $p) {
            exec($p . ' -version 2>&1', $out, $code);
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

    private function getDetailedUploadError($code)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => ['type' => 'PHP_LIMIT', 'message' => 'Fichier trop grand'],
            UPLOAD_ERR_FORM_SIZE => ['type' => 'FORM_LIMIT', 'message' => 'Fichier trop grand'],
            UPLOAD_ERR_PARTIAL => ['type' => 'NETWORK', 'message' => 'Upload partiel'],
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
        if (empty($seconds) || $seconds < 0) return '0:00';
        if ($seconds < 60) {
            return gmdate("s\\s", $seconds);
        } elseif ($seconds < 3600) {
            return gmdate("i\\m s\\s", $seconds);
        } else {
            return gmdate("H\\h i\\m s\\s", $seconds);
        }
    }
}