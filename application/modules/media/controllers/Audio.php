<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Audio Controller - YouTube-Style Upload
 * SANS CSRF - Upload chunked, miniatures modifiables, interface moderne
 */
class Audio extends MX_Controller {

    private $paths;
    private $audio_config;
    private $ffmpeg_path;
    private $ffprobe_path;
    private $gd_available = false;

    function __construct()
    {
        parent::__construct();
        
        // DÉSACTIVER CSRF POUR TOUTES LES MÉTHODES AJAX
        $this->_csrf_off();
        
        $this->initializePaths();
        $this->initializeConfig();
        $this->detectFFmpegTools();
        $this->checkGDAvailability();
        $this->ensureDirectories();
        
        $this->load->model('media/Model_media', 'Model');
    }

    // ==================== DÉSACTIVATION CSRF ====================

    private function _csrf_off()
    {
        if ($this->input->is_ajax_request() || $this->input->server('REQUEST_METHOD') === 'POST') {
            $this->config->set_item('csrf_protection', FALSE);
        }
    }

    // ==================== INITIALISATION ====================

    private function initializePaths()
    {
        $base = FCPATH;
        $this->paths = [
            'temp'         => $base . 'uploads/temp/audio/',
            'originals'    => $base . 'attachments/Audio/Originals/',
            'converted'    => $base . 'attachments/Audio/Converted/',
            'thumbnails'   => $base . 'attachments/Audio/Thumbnails/',
            'covers'       => $base . 'attachments/Audio/Covers/',
            'waveforms'    => $base . 'attachments/Audio/Waveforms/',
            'logs'         => $base . 'attachments/Audio/Logs/',
        ];
    }

    private function initializeConfig()
    {
        $this->audio_config = [
            'chunk_size'        => 5 * 1024 * 1024,  // 5MB chunks (comme vidéo)
            'max_file_size'     => 500 * 1024 * 1024, // 500MB max
            'allowed_extensions' => ['mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a', 'wma', 'aiff', 'opus', 'weba'],
            'qualities' => [
                'low'    => ['bitrate' => '64k',  'suffix' => '_64k'],
                'medium' => ['bitrate' => '128k', 'suffix' => '_128k'],
                'high'   => ['bitrate' => '192k', 'suffix' => '_192k'],
                'max'    => ['bitrate' => '320k', 'suffix' => '_320k']
            ]
        ];
    }

    private function detectFFmpegTools()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->ffmpeg_path   = 'C:\\ffmpeg\\bin\\ffmpeg.exe';
            $this->ffprobe_path  = 'C:\\ffmpeg\\bin\\ffprobe.exe';
            
            if (!file_exists($this->ffmpeg_path)) {
                $this->ffmpeg_path  = $this->findExecutable(['ffmpeg']);
                $this->ffprobe_path = $this->findExecutable(['ffprobe']);
            }
        } else {
            $this->ffmpeg_path   = $this->findExecutable(['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg']);
            $this->ffprobe_path  = $this->findExecutable(['ffprobe', '/usr/bin/ffprobe', '/usr/local/bin/ffprobe']);
        }
    }

    private function findExecutable($candidates)
    {
        foreach ($candidates as $cmd) {
            if (empty($cmd)) continue;
            $output = [];
            $return = 0;
            exec($cmd . ' -version 2>/dev/null', $output, $return);
            if ($return === 0) return $cmd;
        }
        return false;
    }

    private function checkGDAvailability()
    {
        $this->gd_available = extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }

    private function ensureDirectories()
    {
        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0777, true);
            }
        }
    }

    // ==================== VUE PRINCIPALE ====================

    public function index()
    {
        $data = [
            'audios'           => $this->Model->read('galerie_medias', ['type' => 'audio'], 'id_media', 'DESC'),
            'categories'       => $this->getExistingCategories(),
            'total_duration'   => $this->calculateTotalDuration(),
            'storage_stats'    => $this->getStorageStatistics(),
            'audio_capabilities' => $this->getAudioCapabilities()
        ];
        
        $this->load->view('Audio_View', $data);
    }

    // ==================== API UPLOAD (JSON PUR) ====================

    public function initUpload()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');

        // Validation
        if (empty($file_name) || $file_size <= 0) {
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
            return;
        }
        
        if ($file_size > $this->audio_config['max_file_size']) {
            echo json_encode(['success' => false, 'message' => 'Fichier trop grand (max 500MB)']);
            return;
        }
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->audio_config['allowed_extensions'])) {
            echo json_encode(['success' => false, 'message' => 'Format non supporté: ' . $ext]);
            return;
        }

        // Créer session upload
        $upload_id = 'audio_' . uniqid() . '_' . bin2hex(random_bytes(4));
        $temp_dir  = $this->paths['temp'] . $upload_id . '/';
        
        if (!@mkdir($temp_dir, 0777, true)) {
            echo json_encode(['success' => false, 'message' => 'Erreur création dossier']);
            return;
        }

        $total_chunks = (int)ceil($file_size / $this->audio_config['chunk_size']);
        
        $metadata = [
            'upload_id'       => $upload_id,
            'file_name'       => $file_name,
            'file_size'       => $file_size,
            'total_chunks'    => $total_chunks,
            'uploaded_chunks' => [],
            'created_at'      => time(),
            'status'          => 'uploading'
        ];
        
        file_put_contents($temp_dir . 'metadata.json', json_encode($metadata));

        echo json_encode([
            'success'      => true,
            'upload_id'    => $upload_id,
            'chunk_size'   => $this->audio_config['chunk_size'],
            'total_chunks' => $total_chunks,
            'ffmpeg_ready' => (bool)$this->ffmpeg_path
        ]);
        return;
    }

   public function uploadChunk()
{
    $this->_csrf_off();
    $this->output->set_content_type('application/json');
    
    // LOG DE DÉBUT
    log_message('debug', '=== UPLOAD CHUNK START ===');
    log_message('debug', 'POST data: ' . print_r($this->input->post(), true));
    log_message('debug', 'FILES: ' . print_r($_FILES, true));
    
    $upload_id   = $this->input->post('upload_id');
    $chunk_index = (int)$this->input->post('chunk_index');

    if (empty($upload_id)) {
        log_message('error', 'Upload ID manquant');
        echo json_encode(['success' => false, 'message' => 'Upload ID manquant']);
        return;
    }

    $temp_dir      = $this->paths['temp'] . $upload_id . '/';
    $metadata_file = $temp_dir . 'metadata.json';
    
    // Vérification détaillée du dossier
    log_message('debug', 'Temp dir: ' . $temp_dir);
    log_message('debug', 'Is dir? ' . (is_dir($temp_dir) ? 'YES' : 'NO'));
    
    if (!is_dir($temp_dir)) {
        if (!@mkdir($temp_dir, 0777, true)) {
            $error = error_get_last();
            log_message('error', 'Failed to create dir: ' . ($error['message'] ?? 'unknown'));
            echo json_encode([
                'success' => false, 
                'message' => 'Impossible de créer le dossier: ' . $temp_dir,
                'debug' => $error['message'] ?? 'unknown'
            ]);
            return;
        }
        log_message('debug', 'Directory created successfully');
    }
    
    if (!file_exists($metadata_file)) {
        log_message('error', 'Metadata file not found: ' . $metadata_file);
        echo json_encode([
            'success' => false, 
            'message' => 'Session non trouvée',
            'debug' => 'Missing: ' . $metadata_file
        ]);
        return;
    }

    $metadata = json_decode(file_get_contents($metadata_file), true);
    log_message('debug', 'Metadata loaded: ' . json_encode($metadata));
    
    $chunk_path = $temp_dir . 'chunk_' . $chunk_index;
    
    // Vérification détaillée du fichier uploadé
    if (!isset($_FILES['chunk'])) {
        log_message('error', 'No chunk in FILES');
        echo json_encode(['success' => false, 'message' => 'Aucun chunk reçu']);
        return;
    }
    
    $file_error = $_FILES['chunk']['error'];
    log_message('debug', 'File error code: ' . $file_error);
    
    if ($file_error !== UPLOAD_ERR_OK) {
        $error_msg = $this->getUploadErrorMessage($file_error);
        log_message('error', 'Upload error: ' . $error_msg);
        echo json_encode(['success' => false, 'message' => $error_msg]);
        return;
    }
    
    // Vérifier que le fichier temporaire existe
    if (!file_exists($_FILES['chunk']['tmp_name'])) {
        log_message('error', 'Temp file does not exist: ' . $_FILES['chunk']['tmp_name']);
        echo json_encode(['success' => false, 'message' => 'Fichier temporaire introuvable']);
        return;
    }
    
    $temp_size = filesize($_FILES['chunk']['tmp_name']);
    log_message('debug', 'Temp file size: ' . $temp_size . ' bytes');
    
    if ($temp_size == 0) {
        log_message('error', 'Empty file uploaded');
        echo json_encode(['success' => false, 'message' => 'Fichier vide reçu']);
        return;
    }
    
    // Sauvegarder le chunk
    $move_result = @move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path);
    
    if (!$move_result) {
        $error = error_get_last();
        log_message('error', 'Move failed: ' . ($error['message'] ?? 'unknown'));
        echo json_encode([
            'success' => false, 
            'message' => 'Erreur sauvegarde chunk',
            'debug' => $error['message'] ?? 'unknown',
            'target' => $chunk_path
        ]);
        return;
    }
    
    // Vérifier que le fichier a bien été créé
    if (!file_exists($chunk_path)) {
        log_message('error', 'Chunk not created at: ' . $chunk_path);
        echo json_encode(['success' => false, 'message' => 'Chunk non créé']);
        return;
    }
    
    $saved_size = filesize($chunk_path);
    log_message('debug', 'Saved chunk size: ' . $saved_size . ' bytes');
    
    if ($saved_size != $temp_size) {
        log_message('error', 'Size mismatch: temp=' . $temp_size . ', saved=' . $saved_size);
        echo json_encode(['success' => false, 'message' => 'Taille incohérente']);
        return;
    }

    // Mettre à jour metadata
    if (!in_array($chunk_index, $metadata['uploaded_chunks'])) {
        $metadata['uploaded_chunks'][] = $chunk_index;
        sort($metadata['uploaded_chunks']);
        file_put_contents($metadata_file, json_encode($metadata));
        log_message('debug', 'Metadata updated, chunks: ' . count($metadata['uploaded_chunks']));
    }

    $uploaded = count($metadata['uploaded_chunks']);
    echo json_encode([
        'success'  => true,
        'message'  => 'Chunk reçu',
        'progress' => [
            'uploaded_chunks' => $uploaded,
            'total_chunks'    => $metadata['total_chunks'],
            'percent'         => round(($uploaded / $metadata['total_chunks']) * 100, 2)
        ]
    ]);
}

private function getUploadErrorMessage($error_code)
{
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'Le fichier dépasse la taille maximum (upload_max_filesize)';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Le fichier dépasse la taille maximum (MAX_FILE_SIZE)';
        case UPLOAD_ERR_PARTIAL:
            return 'Le fichier n\'a été que partiellement uploadé';
        case UPLOAD_ERR_NO_FILE:
            return 'Aucun fichier n\'a été uploadé';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Dossier temporaire manquant';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Erreur d\'écriture sur le disque';
        case UPLOAD_ERR_EXTENSION:
            return 'Une extension PHP a arrêté l\'upload';
        default:
            return 'Erreur inconnue: ' . $error_code;
    }
}
    public function completeUpload()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        $upload_id = $this->input->post('upload_id');
        
        if (empty($upload_id)) {
            echo json_encode(['success' => false, 'message' => 'Upload ID manquant']);
            return;
        }

        $temp_dir      = $this->paths['temp'] . $upload_id . '/';
        $metadata_file = $temp_dir . 'metadata.json';
        
        if (!file_exists($metadata_file)) {
            echo json_encode(['success' => false, 'message' => 'Session non trouvée']);
            return;
        }

        $metadata = json_decode(file_get_contents($metadata_file), true);

        // Vérifier chunks manquants
        $missing = [];
        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            if (!file_exists($temp_dir . 'chunk_' . $i)) {
                $missing[] = $i;
            }
        }

        if (!empty($missing)) {
            echo json_encode([
                'success' => false,
                'message' => 'Chunks manquants',
                'missing' => $missing
            ]);
            return;
        }

        // Assembler le fichier final
        $safe_name      = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($metadata['file_name'], PATHINFO_FILENAME));
        $original_name  = date('YmdHis') . '_' . $safe_name . '_audio.' . pathinfo($metadata['file_name'], PATHINFO_EXTENSION);
        $original_path  = $this->paths['originals'] . $original_name;
        
        $out = fopen($original_path, 'wb');
        if (!$out) {
            echo json_encode(['success' => false, 'message' => 'Impossible de créer fichier']);
            return;
        }

        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            $chunk_file = $temp_dir . 'chunk_' . $i;
            fwrite($out, file_get_contents($chunk_file));
            unlink($chunk_file);
        }
        fclose($out);

        // Nettoyer temp
        @unlink($metadata_file);
        @rmdir($temp_dir);

        // Analyse et traitements audio
        $analysis   = $this->analyzeAudio($original_path);
        $thumbnails = $this->generateThumbnails($original_path, $original_name);
        $waveform   = $this->generateWaveform($original_path, $original_name);
        $conversions = $this->convertToMultipleBitrates($original_path, $original_name);

        // CORRECTION: S'assurer que thumbnails est un objet
        $thumbnails_obj = new stdClass();
        if (!empty($thumbnails['cover'])) {
            $thumbnails_obj->cover = $thumbnails['cover'];
        }
        if (!empty($thumbnails['generated'])) {
            $thumbnails_obj->generated = $thumbnails['generated'];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Upload complété',
            'data'    => [
                'original_file' => 'attachments/Audio/Originals/' . $original_name,
                'file_size'     => $this->formatBytes(filesize($original_path)),
                'analysis'      => $analysis,
                'thumbnails'    => $thumbnails_obj,
                'waveform'      => $waveform,
                'conversions'   => $conversions,
                'form_suggestions' => [
                    'titre'   => $analysis['title'] ?: $this->suggestTitle($metadata['file_name']),
                    'credits' => $analysis['artist'] ?: 'Artiste inconnu',
                    'categorie' => $this->suggestCategory($analysis)
                ]
            ]
        ]);
        return;
    }

    // ==================== CRUD ====================

    public function Create()
    {
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('media/audio'));
            return;
        }

        $auto_data = json_decode($this->input->post('auto_detected_data') ?: '{}', true);
        
        $data = [
            'titre'           => $this->input->post('titre'),
            'type'            => 'audio',
            'description'     => $this->input->post('description'),
            'categorie'       => $this->input->post('categorie'),
            'fichier'         => $this->input->post('uploaded_file_path'),
            'duree'           => $auto_data['analysis']['duration'] ?? 0,
            'taille'          => $auto_data['analysis']['size'] ?? 0,
            'bitrate'         => $auto_data['analysis']['bitrate'] ?? 0,
            'miniature'       => $this->input->post('thumbnail') ?: ($auto_data['thumbnails']->cover ?? $auto_data['thumbnails']->generated ?? null),
            'metadata_id3'    => json_encode($auto_data),
            'est_actif'       => $this->input->post('est_actif') ? 1 : 0,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website'  => $this->input->post('is_for_website') ? 1 : 0,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ];

        $rsp = $this->Model->create('galerie_medias', $data);
        
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Audio créé' : 'Erreur création');
        redirect(base_url('media/audio'));
    }

    public function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('media/audio'));
            return;
        }

        // Récupérer l'audio actuel pour comparer
        $current_audio = $this->Model->readOne('galerie_medias', ['id_media' => $id]);

        $data = [
            'titre'           => $this->input->post('titre'),
            'description'     => $this->input->post('description'),
            'categorie'       => $this->input->post('categorie'),
            'date_media'      => $this->input->post('date_media'),
            'credits'         => $this->input->post('credits'),
            'est_actif'       => $this->input->post('est_actif') ? 1 : 0,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website'  => $this->input->post('is_for_website') ? 1 : 0,
            'updated_at'      => date('Y-m-d H:i:s')
        ];

        // Gestion de la miniature modifiée
        $new_thumbnail = $this->input->post('thumbnail');
        if (!empty($new_thumbnail) && $new_thumbnail !== ($current_audio['miniature'] ?? '')) {
            // Supprimer l'ancienne miniature personnalisée si elle existe
            if (!empty($current_audio['miniature']) && strpos($current_audio['miniature'], 'Custom/') !== false) {
                @unlink(FCPATH . $current_audio['miniature']);
            }
            $data['miniature'] = $new_thumbnail;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);
        
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Audio mis à jour' : 'Erreur mise à jour');
        redirect(base_url('media/audio'));
    }

    public function Delete()
    {
        $id = $this->input->post('id');
        $audio = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        if ($audio) {
            if (!empty($audio['fichier'])) {
                @unlink(FCPATH . $audio['fichier']);
            }
            if (!empty($audio['miniature'])) {
                @unlink(FCPATH . $audio['miniature']);
            }
            
            $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
                'est_actif'  => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Audio supprimé' : 'Erreur');
        }
        
        redirect(base_url('media/audio'));
    }

    public function ChangeStatus()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        $id     = $this->input->post('id');
        $status = $this->input->post('est_actif');
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            'est_actif'  => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode(['success' => (bool)$rsp]);
        return;
    }

    public function toggleField()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        $id    = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');
        
        $allowed = ['is_for_whatsapp', 'is_for_website', 'est_actif'];
        if (!in_array($field, $allowed)) {
            echo json_encode(['success' => false]);
            return;
        }
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            $field       => $value,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode(['success' => (bool)$rsp]);
        return;
    }

    // ==================== STREAMING ====================

    public function stream($id)
    {
        $audio = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        if (!$audio) {
            show_404();
            return;
        }
        
        $filename = null;
        
        if (!empty($audio['fichier'])) {
            $filename = basename($audio['fichier']);
        }
        
        if (empty($filename)) {
            show_404();
            return;
        }
        
        $this->serveAudio($filename);
    }

    private function serveAudio($filename)
    {
        $filename = basename($filename);
        
        $file_path = $this->paths['originals'] . $filename;
        
        if (!file_exists($file_path)) {
            $base_name = pathinfo($filename, PATHINFO_FILENAME);
            
            $converted_files = glob($this->paths['converted'] . $base_name . '*');
            if (!empty($converted_files)) {
                $file_path = $converted_files[0];
            } else {
                $file_path = $this->paths['converted'] . $filename;
            }
        }
        
        if (!file_exists($file_path)) {
            log_message('error', 'Audio file not found: ' . $file_path . ' (original: ' . $filename . ')');
            show_404();
            return;
        }

        $file_size = filesize($file_path);
        
        header('Content-Type: audio/mpeg');
        header('Accept-Ranges: bytes');
        header('Cache-Control: public, max-age=31536000');
        
        $start = 0;
        $end = $file_size - 1;
        
        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
                $start = intval($m[1]);
                if (!empty($m[2])) {
                    $end = intval($m[2]);
                }
                header('HTTP/1.1 206 Partial Content');
                header("Content-Range: bytes $start-$end/$file_size");
            }
        }
        
        header('Content-Length: ' . ($end - $start + 1));
        
        $fp = fopen($file_path, 'rb');
        if (!$fp) {
            show_404();
            return;
        }
        
        fseek($fp, $start);
        
        $buffer_size = 8192;
        $bytes_sent = 0;
        $bytes_to_send = $end - $start + 1;
        
        while (!feof($fp) && $bytes_sent < $bytes_to_send) {
            $buffer = fread($fp, min($buffer_size, $bytes_to_send - $bytes_sent));
            if ($buffer === false) break;
            
            echo $buffer;
            flush();
            $bytes_sent += strlen($buffer);
            
            if ($bytes_sent % (1024 * 1024) === 0) {
                ob_flush();
            }
        }
        
        fclose($fp);
    }

    // ==================== UPLOAD MINIATURE PERSONNALISÉE ====================

    /**
     * Upload d'une miniature personnalisée pour un audio
     * IDENTIQUE à Video.php
     */
    public function uploadThumbnail()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        // Vérifier si un fichier a été envoyé
        if (empty($_FILES['thumbnail_file']) || $_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'success' => false, 
                'message' => 'Aucun fichier reçu ou erreur upload: ' . ($_FILES['thumbnail_file']['error'] ?? 'unknown')
            ]);
            return;
        }

        $file = $_FILES['thumbnail_file'];
        $nom_champ = $file['name'];
        $nom_file = $file['tmp_name'];
        
        // Dossier spécifique pour les miniatures audio
        $ref_folder = FCPATH . 'attachments/Audio/Thumbnails/Custom/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg');

        // Validation extension
        if (!in_array($file_extension, $valid_ext)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Format non supporté. Formats acceptés: ' . implode(', ', $valid_ext)
            ]);
            return;
        }

        // Créer le dossier si nécessaire
        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        // Déplacer le fichier
        $final_filename = $fichier . "." . $file_extension;
        $destination = $ref_folder . $final_filename;
        
        if (!move_uploaded_file($nom_file, $destination)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Erreur lors du déplacement du fichier'
            ]);
            return;
        }

        // Redimensionner si GD disponible
        if ($this->gd_available) {
            $this->resizeThumbnail($destination, 800, 800);
        }

        // Retourner le chemin relatif pour stockage en BDD
        $relative_path = 'attachments/Audio/Thumbnails/Custom/' . $final_filename;
        
        echo json_encode([
            'success' => true,
            'message' => 'Miniature uploadée avec succès',
            'file_path' => $relative_path,
            'file_name' => $final_filename,
            'preview_url' => base_url($relative_path),
            'gd_used' => $this->gd_available
        ]);
        return;
    }

    /**
     * Redimensionne une miniature si elle dépasse les dimensions max
     */
    private function resizeThumbnail($file_path, $max_width, $max_height)
    {
        if (!$this->gd_available || !function_exists('getimagesize')) {
            return;
        }

        list($width, $height, $type) = getimagesize($file_path);
        
        if ($width === false || $height === false) {
            return;
        }

        // Si l'image est plus petite que les dimensions max, ne rien faire
        if ($width <= $max_width && $height <= $max_height) {
            return;
        }

        // Calculer les nouvelles dimensions en conservant le ratio
        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = round($width * $ratio);
        $new_height = round($height * $ratio);

        // Créer l'image source selon le type
        switch ($type) {
            case IMAGETYPE_JPEG:
                if (!function_exists('imagecreatefromjpeg')) return;
                $src_image = @imagecreatefromjpeg($file_path);
                break;
            case IMAGETYPE_PNG:
                if (!function_exists('imagecreatefrompng')) return;
                $src_image = @imagecreatefrompng($file_path);
                break;
            case IMAGETYPE_GIF:
                if (!function_exists('imagecreatefromgif')) return;
                $src_image = @imagecreatefromgif($file_path);
                break;
            case IMAGETYPE_WEBP:
                if (!function_exists('imagecreatefromwebp')) return;
                $src_image = @imagecreatefromwebp($file_path);
                break;
            default:
                return;
        }

        if (!$src_image) {
            return;
        }

        // Créer l'image destination
        $dst_image = imagecreatetruecolor($new_width, $new_height);
        
        // Préserver la transparence pour PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($dst_image, false);
            imagesavealpha($dst_image, true);
        }

        // Redimensionner
        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        // Sauvegarder
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($dst_image, $file_path, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($dst_image, $file_path, 6);
                break;
            case IMAGETYPE_GIF:
                imagegif($dst_image, $file_path);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($dst_image, $file_path, 90);
                break;
        }

        imagedestroy($src_image);
        imagedestroy($dst_image);
    }

    // ==================== HELPERS AUDIO ====================

    private function analyzeAudio($file_path)
    {
        if (!$this->ffprobe_path || !file_exists($file_path)) {
            return [
                'duration' => 0, 
                'bitrate' => 0, 
                'sample_rate' => 0,
                'channels' => 0,
                'codec' => 'unknown',
                'title' => null,
                'artist' => null,
                'album' => null,
                'duration_formatted' => '0:00'
            ];
        }

        $cmd = sprintf(
            '%s -v quiet -print_format json -show_format -show_streams %s 2>&1',
            escapeshellarg($this->ffprobe_path),
            escapeshellarg($file_path)
        );
        
        exec($cmd, $output, $code);
        
        if ($code !== 0) {
            return [
                'duration' => 0, 
                'bitrate' => 0, 
                'sample_rate' => 0,
                'channels' => 0,
                'codec' => 'unknown',
                'title' => null,
                'artist' => null,
                'album' => null,
                'duration_formatted' => '0:00'
            ];
        }

        $data   = json_decode(implode("\n", $output), true);
        $format = $data['format'] ?? [];
        $audio  = null;
        
        foreach ($data['streams'] ?? [] as $stream) {
            if (isset($stream['codec_type']) && $stream['codec_type'] === 'audio') {
                $audio = $stream;
                break;
            }
        }

        $tags = $format['tags'] ?? [];
        $duration = (float)($format['duration'] ?? 0);

        // Format duration
        $hours = floor($duration / 3600);
        $mins = floor(($duration % 3600) / 60);
        $secs = floor($duration % 60);
        $duration_formatted = $hours > 0 
            ? sprintf('%d:%02d:%02d', $hours, $mins, $secs)
            : sprintf('%d:%02d', $mins, $secs);

        return [
            'duration'           => $duration,
            'duration_formatted' => $duration_formatted,
            'size'               => (int)($format['size'] ?? filesize($file_path)),
            'bitrate'            => (int)($format['bit_rate'] ?? 0),
            'sample_rate'        => (int)($audio['sample_rate'] ?? 0),
            'channels'           => (int)($audio['channels'] ?? 0),
            'codec'              => $audio['codec_name'] ?? 'unknown',
            'title'              => $tags['title'] ?? $tags['TITLE'] ?? null,
            'artist'             => $tags['artist'] ?? $tags['ARTIST'] ?? null,
            'album'              => $tags['album'] ?? $tags['ALBUM'] ?? null,
            'year'               => $tags['date'] ?? $tags['DATE'] ?? null,
            'genre'              => $tags['genre'] ?? $tags['GENRE'] ?? null
        ];
    }

    private function generateThumbnails($audio_path, $filename)
    {
        $result = ['cover' => null, 'generated' => null];
        
        if (!$this->ffmpeg_path) {
            return $result;
        }

        $base_name  = pathinfo($filename, PATHINFO_FILENAME);
        
        // 1. Essayer d'extraire la cover art intégrée
        $cover_name = $base_name . '_cover.jpg';
        $cover_path = $this->paths['thumbnails'] . $cover_name;
        
        $cmd_cover = sprintf(
            '%s -i %s -an -vcodec copy -f image2 -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path),
            escapeshellarg($audio_path),
            escapeshellarg($cover_path)
        );
        exec($cmd_cover);

        if (file_exists($cover_path) && filesize($cover_path) > 1000) {
            $result['cover'] = 'attachments/Audio/Thumbnails/' . $cover_name;
        } else {
            @unlink($cover_path); // Supprimer si vide ou trop petit
        }

        // 2. Générer une miniature depuis la waveform si pas de cover
        if (empty($result['cover'])) {
            $generated_name = $base_name . '_waveform.jpg';
            $generated_path = $this->paths['thumbnails'] . $generated_name;
            
            $cmd_waveform = sprintf(
                '%s -i %s -filter_complex "aformat=channel_layouts=mono,showwavespic=s=800x800:colors=#FF0000|#FF6B6B" -frames:v 1 -y %s 2>&1',
                escapeshellarg($this->ffmpeg_path),
                escapeshellarg($audio_path),
                escapeshellarg($generated_path)
            );
            exec($cmd_waveform);

            if (file_exists($generated_path)) {
                $result['generated'] = 'attachments/Audio/Thumbnails/' . $generated_name;
            }
        }

        return $result;
    }

    private function generateWaveform($audio_path, $filename)
    {
        if (!$this->ffmpeg_path) {
            return null;
        }

        $base_name = pathinfo($filename, PATHINFO_FILENAME);
        $waveform_name = $base_name . '_wave.png';
        $waveform_path = $this->paths['waveforms'] . $waveform_name;

        $cmd = sprintf(
            '%s -i %s -filter_complex "aformat=channel_layouts=mono,showwavespic=s=1200x200:colors=#FF0000|#FF6B6B" -frames:v 1 -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path),
            escapeshellarg($audio_path),
            escapeshellarg($waveform_path)
        );
        exec($cmd);

        return file_exists($waveform_path) ? 'attachments/Audio/Waveforms/' . $waveform_name : null;
    }

    private function convertToMultipleBitrates($audio_path, $filename)
    {
        if (!$this->ffmpeg_path) {
            return [];
        }

        $base_name = pathinfo($filename, PATHINFO_FILENAME);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $conversions = [];

        foreach ($this->audio_config['qualities'] as $quality => $config) {
            $output_name = $base_name . $config['suffix'] . '.mp3';
            $output_path = $this->paths['converted'] . $output_name;

            $cmd = sprintf(
                '%s -i %s -codec:a libmp3lame -b:a %s -map_metadata 0 -id3v2_version 3 -y %s 2>&1',
                escapeshellarg($this->ffmpeg_path),
                escapeshellarg($audio_path),
                $config['bitrate'],
                escapeshellarg($output_path)
            );
            exec($cmd, $output, $code);

            if ($code === 0 && file_exists($output_path)) {
                $conversions[$quality] = [
                    'path' => 'attachments/Audio/Converted/' . $output_name,
                    'bitrate' => $config['bitrate'],
                    'size' => filesize($output_path),
                    'size_formatted' => $this->formatBytes(filesize($output_path))
                ];
            }
        }

        return $conversions;
    }

    private function getAudioCapabilities()
    {
        return [
            'hardware' => ['ffmpeg' => (bool)$this->ffmpeg_path],
            'features' => [
                'multi_bitrate' => (bool)$this->ffmpeg_path,
                'streaming'     => true,
                'gd_available'  => $this->gd_available
            ]
        ];
    }

    private function getExistingCategories()
    {
        $this->db->select('DISTINCT(categorie) as cat');
        $this->db->where('type', 'audio');
        $query = $this->db->get('galerie_medias');
        return array_filter(array_column($query->result_array(), 'cat'));
    }

    private function calculateTotalDuration()
    {
        $this->db->select_sum('duree', 'total');
        $this->db->where('type', 'audio');
        $result = $this->db->get('galerie_medias')->row();
        return $result->total ?? 0;
    }

    private function getStorageStatistics()
    {
        $total = 0;
        foreach ($this->paths as $path) {
            if (is_dir($path)) {
                $total += $this->getDirSize($path);
            }
        }
        return ['total_used' => $this->formatBytes($total)];
    }

    private function getDirSize($dir)
    {
        $size = 0;
        foreach (glob($dir . '/*') as $file) {
            $size += is_file($file) ? filesize($file) : $this->getDirSize($file);
        }
        return $size;
    }

    private function suggestTitle($filename)
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[^a-zA-Z0-9]/', ' ', $name);
        return ucwords(trim($name));
    }

    private function suggestCategory($analysis)
    {
        $genre = strtolower($analysis['genre'] ?? '');
        
        $mappings = [
            'Podcast' => ['podcast', 'spoken', 'audiobook', 'speech', 'talk'],
            'Musique' => ['pop', 'rock', 'jazz', 'classical', 'electronic', 'hip-hop', 'rap', 'soul', 'funk'],
            'Interview' => ['interview', 'conversation'],
            'Conférence' => ['conference', 'lecture', 'seminar'],
            'Méditation' => ['meditation', 'relaxation', 'yoga', 'spiritual'],
            'Son' => ['sound', 'fx', 'ambient', 'nature']
        ];

        foreach ($mappings as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($genre, $keyword) !== false) {
                    return $category;
                }
            }
        }

        if (!empty($analysis['duration'])) {
            if ($analysis['duration'] > 600 && $analysis['duration'] < 3600) {
                return 'Podcast';
            }
            if ($analysis['duration'] > 3600) {
                return 'Conférence';
            }
        }

        return 'Musique';
    }

    private function formatBytes($bytes)
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function checkConfig()
{
    echo "<pre>";
    echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
    echo "post_max_size: " . ini_get('post_max_size') . "\n";
    echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
    echo "memory_limit: " . ini_get('memory_limit') . "\n";
    echo "</pre>";
    exit;
}
}