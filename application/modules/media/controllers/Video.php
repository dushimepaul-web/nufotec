<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Video Controller - YouTube-Style Upload
 * Adapté pour serveur avec limites: upload_max_filesize=2M, post_max_size=8M
 */
class Video extends MX_Controller {

    private $paths;
    private $video_config;
    private $ffmpeg_path;
    private $ffprobe_path;
    private $gd_available = false;

    function __construct()
    {
        parent::__construct();
        
        // DÉSACTIVER CSRF POUR TOUTES LES MÉTHODES AJAX
        $this->_csrf_off();
        is_admin();
        
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
            'temp'         => $base . 'uploads/temp/video/',
            'originals'    => $base . 'attachments/Video/Originals/',
            'encoded'      => $base . 'attachments/Video/Encoded/',
            'thumbnails'   => $base . 'attachments/Video/Thumbnails/',
            'posters'      => $base . 'attachments/Video/Posters/',
            'logs'         => $base . 'attachments/Video/Logs/',
        ];
    }

    private function initializeConfig()
    {
        // Adapter aux limites serveur: upload_max_filesize=2M
        // On utilise des chunks de 1.5MB pour être safe (en dessous de 2M)
        $chunk_size = 1.5 * 1024 * 1024; // 1.5 MB (1,572,864 bytes)
        
        $this->video_config = [
            'chunk_size'        => $chunk_size,  // 1.5MB chunks
            'max_file_size'     => 2 * 1024 * 1024 * 1024, // 2GB max
            'allowed_extensions' => ['mp4', 'mov', 'avi', 'mkv', 'webm', 'm4v', '3gp', 'flv', 'wmv'],
        ];
        
        // Log pour debug
        log_message('debug', 'Video config - chunk_size: ' . $this->video_config['chunk_size'] . ' bytes (' . round($this->video_config['chunk_size'] / 1024 / 1024, 2) . ' MB)');
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

    // ==================== FONCTIONS DE GESTION DES SLUGS ====================

    /**
     * Générer un slug unique pour un média vidéo
     */
    private function generateSlug($title, $id = null)
    {
        // Nettoyer le titre
        $slug = strtolower(trim($title));
        if (empty($slug)) {
            $slug = 'video';
        }
        
        // Remplacer les caractères spéciaux
        $replacements = [
            ' ' => '-',
            "'" => '-',     // Remplacer l'apostrophe par un tiret
            '"' => '-',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c',
            'œ' => 'oe',
            '/' => '-',
            '\\' => '-',
            '&' => 'et',
            '?' => '',
            '!' => '',
            '.' => '-',
            ',' => '-',
            ';' => '-',
            ':' => '-',
            '(' => '',
            ')' => '',
            '[' => '',
            ']' => '',
            '{' => '',
            '}' => '',
            '+' => '-',
            '*' => '',
            '#' => '',
            '@' => '',
            '%' => '',
            '^' => '',
            '=' => '-'
        ];
        
        foreach ($replacements as $search => $replace) {
            $slug = str_replace($search, $replace, $slug);
        }
        
        // Supprimer les caractères non alphanumériques restants
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        
        // Supprimer les tirets multiples
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Supprimer les tirets au début et à la fin
        $slug = trim($slug, '-');
        
        // Limiter la longueur du slug
        if (strlen($slug) > 80) {
            $slug = substr($slug, 0, 80);
            $slug = preg_replace('/-+$/', '', $slug);
        }
        
        // Ajouter l'ID pour garantir l'unicité
        if ($id) {
            $slug = $slug . '-' . $id;
        }
        
        return $slug;
    }

    /**
     * Vérifier si un slug existe déjà et générer un slug unique
     */
    private function generateUniqueSlug($title, $id = null)
    {
        $slug = $this->generateSlug($title, $id);
        
        // Si pas d'ID, vérifier si le slug existe déjà
        if (!$id) {
            $existing = $this->db->query("SELECT id_media FROM galerie_medias WHERE slug = ?", [$slug])->num_rows();
            if ($existing > 0) {
                $counter = 2;
                $original_slug = $slug;
                while ($this->db->query("SELECT id_media FROM galerie_medias WHERE slug = ?", [$slug])->num_rows() > 0) {
                    $slug = $original_slug . '-' . $counter;
                    $counter++;
                }
            }
        } else {
            // Si ID fourni, vérifier que le slug n'appartient pas à un autre média
            $existing = $this->db->query("SELECT id_media FROM galerie_medias WHERE slug = ? AND id_media != ?", [$slug, $id])->num_rows();
            if ($existing > 0) {
                $counter = 2;
                $original_slug = $slug;
                while ($this->db->query("SELECT id_media FROM galerie_medias WHERE slug = ? AND id_media != ?", [$slug, $id])->num_rows() > 0) {
                    $slug = $original_slug . '-' . $counter;
                    $counter++;
                }
            }
        }
        
        return $slug;
    }

    // ==================== VUE PRINCIPALE ====================

    public function index()
    {
        $data = [
            'videos'           => $this->Model->read('galerie_medias', ['type' => 'video'], 'id_media', 'DESC'),
            'categories'       => $this->getExistingCategories(),
            'total_duration'   => $this->calculateTotalDuration(),
            'storage_stats'    => $this->getStorageStatistics(),
            'avc_capabilities' => $this->getAVCCapabilities()
        ];
        
        $this->load->view('Video_View', $data);
    }

    // ==================== API UPLOAD (JSON PUR) ====================

    public function initUpload()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');

        if (empty($file_name) || $file_size <= 0) {
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
            return;
        }
        
        if ($file_size > $this->video_config['max_file_size']) {
            echo json_encode(['success' => false, 'message' => 'Fichier trop grand (max 2GB)']);
            return;
        }
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->video_config['allowed_extensions'])) {
            echo json_encode(['success' => false, 'message' => 'Format non supporté: ' . $ext]);
            return;
        }

        // Créer session upload
        $upload_id = 'avc_' . uniqid() . '_' . bin2hex(random_bytes(4));
        $temp_dir  = $this->paths['temp'] . $upload_id . '/';
        
        if (!@mkdir($temp_dir, 0777, true)) {
            echo json_encode(['success' => false, 'message' => 'Erreur création dossier']);
            return;
        }

        $chunk_size = $this->video_config['chunk_size'];
        $total_chunks = (int)ceil($file_size / $chunk_size);
        
        // Log pour debug
        log_message('debug', 'Video initUpload - file: ' . $file_name . ', size: ' . $file_size . ', total_chunks: ' . $total_chunks . ', chunk_size: ' . $chunk_size);
        
        $metadata = [
            'upload_id'       => $upload_id,
            'file_name'       => $file_name,
            'file_size'       => $file_size,
            'total_chunks'    => $total_chunks,
            'chunk_size'      => $chunk_size,
            'uploaded_chunks' => [],
            'created_at'      => time(),
            'status'          => 'uploading'
        ];
        
        file_put_contents($temp_dir . 'metadata.json', json_encode($metadata));

        echo json_encode([
            'success'      => true,
            'upload_id'    => $upload_id,
            'chunk_size'   => $chunk_size,
            'total_chunks' => $total_chunks,
            'avc_ready'    => (bool)$this->ffmpeg_path
        ]);
        return;
    }

    public function uploadChunk()
    {
        // Ignorer le timeout PHP pour les chunks volumineux
        set_time_limit(0);
        
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        $upload_id   = $this->input->post('upload_id');
        $chunk_index = (int)$this->input->post('chunk_index');

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

        $metadata   = json_decode(file_get_contents($metadata_file), true);
        $chunk_path = $temp_dir . 'chunk_' . $chunk_index;
        
        // Vérifier si le chunk existe déjà
        if (file_exists($chunk_path)) {
            if (!in_array($chunk_index, $metadata['uploaded_chunks'])) {
                $metadata['uploaded_chunks'][] = $chunk_index;
                sort($metadata['uploaded_chunks']);
                file_put_contents($metadata_file, json_encode($metadata));
            }
            
            $uploaded = count($metadata['uploaded_chunks']);
            echo json_encode([
                'success'  => true,
                'message'  => 'Chunk déjà présent',
                'progress' => [
                    'uploaded_chunks' => $uploaded,
                    'total_chunks'    => $metadata['total_chunks'],
                    'percent'         => round(($uploaded / $metadata['total_chunks']) * 100, 2)
                ]
            ]);
            return;
        }

        // Vérification du fichier uploadé
        if (empty($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
            $error_msg = $this->getUploadErrorMessage($_FILES['chunk']['error'] ?? null);
            echo json_encode(['success' => false, 'message' => $error_msg]);
            return;
        }
        
        // Vérifier la taille du chunk
        $chunk_size = $_FILES['chunk']['size'];
        $max_allowed = $metadata['chunk_size'] + 1024; // Marge 1KB
        
        if ($chunk_size > $max_allowed) {
            echo json_encode([
                'success' => false, 
                'message' => 'Chunk trop grand: ' . $chunk_size . ' bytes (max: ' . $metadata['chunk_size'] . ')'
            ]);
            return;
        }

        // Sauvegarder le chunk
        if (!@move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path)) {
            echo json_encode(['success' => false, 'message' => 'Erreur sauvegarde chunk']);
            return;
        }

        // Mettre à jour metadata
        if (!in_array($chunk_index, $metadata['uploaded_chunks'])) {
            $metadata['uploaded_chunks'][] = $chunk_index;
            sort($metadata['uploaded_chunks']);
            file_put_contents($metadata_file, json_encode($metadata));
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
        return;
    }

    private function getUploadErrorMessage($error_code)
    {
        if ($error_code === null) return 'Aucun fichier reçu';
        
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Le chunk dépasse la limite du serveur (2MB max)';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Le chunk dépasse la taille MAX_FILE_SIZE';
            case UPLOAD_ERR_PARTIAL:
                return 'Upload partiel - Réessayez';
            case UPLOAD_ERR_NO_FILE:
                return 'Aucun fichier reçu';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Dossier temporaire manquant';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Erreur d\'écriture disque';
            case UPLOAD_ERR_EXTENSION:
                return 'Extension PHP bloquée';
            default:
                return 'Erreur inconnue: ' . $error_code;
        }
    }

    public function completeUpload()
    {
        // Ignorer le timeout PHP pour l'assemblage
        set_time_limit(0);
        
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

        // Vérifier les chunks manquants
        $missing = [];
        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            if (!file_exists($temp_dir . 'chunk_' . $i)) {
                $missing[] = $i;
            }
        }

        if (!empty($missing)) {
            echo json_encode([
                'success' => false,
                'message' => 'Chunks manquants: ' . count($missing),
                'missing' => $missing
            ]);
            return;
        }

        // Assembler le fichier final - version optimisée mémoire
        $safe_name      = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($metadata['file_name'], PATHINFO_FILENAME));
        $original_name  = date('YmdHis') . '_' . $safe_name . '_avc.mp4';
        $original_path  = $this->paths['originals'] . $original_name;
        
        $out = fopen($original_path, 'wb');
        if (!$out) {
            echo json_encode(['success' => false, 'message' => 'Impossible de créer fichier']);
            return;
        }

        // Assembler chunk par chunk sans tout charger en mémoire
        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            $chunk_file = $temp_dir . 'chunk_' . $i;
            $chunk_handle = fopen($chunk_file, 'rb');
            
            if ($chunk_handle) {
                while (!feof($chunk_handle)) {
                    $buffer = fread($chunk_handle, 8192); // 8KB buffer
                    fwrite($out, $buffer);
                }
                fclose($chunk_handle);
            }
            
            unlink($chunk_file);
            
            // Libérer la mémoire périodiquement
            if ($i % 100 == 0) {
                gc_collect_cycles();
            }
        }
        fclose($out);

        // Nettoyer temp
        @unlink($metadata_file);
        @rmdir($temp_dir);

        // Analyse et traitements vidéo
        $analysis   = $this->analyzeVideo($original_path);
        $thumbnails = $this->generateThumbnails($original_path, $original_name);

        // CORRECTION: S'assurer que thumbnails est un objet
        $thumbnails_obj = new stdClass();
        if (!empty($thumbnails['default'])) {
            $thumbnails_obj->default = $thumbnails['default'];
        }
        if (!empty($thumbnails['poster'])) {
            $thumbnails_obj->poster = $thumbnails['poster'];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Upload complété',
            'data'    => [
                'original_file' => 'attachments/Video/Originals/' . $original_name,
                'file_size'     => $this->formatBytes(filesize($original_path)),
                'analysis'      => $analysis,
                'thumbnails'    => $thumbnails_obj,
                'form_suggestions' => [
                    'titre'   => $this->suggestTitle($metadata['file_name']),
                    'credits' => 'Auteur inconnu'
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
            redirect(base_url('media/video'));
            return;
        }

        $auto_data = json_decode($this->input->post('auto_detected_data') ?: '{}', true);
        
        $data = [
            'titre'           => $this->input->post('titre'),
            'type'            => 'video',
            'description'     => $this->input->post('description'),
            'categorie'       => $this->input->post('categorie'),
            'fichier'         => $this->input->post('uploaded_file_path'),
            'duree'           => $auto_data['analysis']['duration'] ?? 0,
            'taille'          => $auto_data['analysis']['size'] ?? 0,
            'miniature'       => $this->input->post('thumbnail') ?: ($auto_data['thumbnails']->default ?? null),
            'metadata_id3'    => json_encode($auto_data),
            'est_actif'       => $this->input->post('est_actif') ? 1 : 0,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website'  => $this->input->post('is_for_website') ? 1 : 0,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ];
        
        // AJOUT: Générer le slug automatiquement avant l'insertion
        $data['slug'] = $this->generateUniqueSlug($data['titre']);

        $rsp = $this->Model->create('galerie_medias', $data);
        
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Vidéo créée' : 'Erreur création');
        redirect(base_url('media/video'));
    }

    public function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('media/video'));
            return;
        }

        $current_video = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
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
        
        // AJOUT: Mettre à jour le slug si le titre a changé
        if ($data['titre'] != $current_video['titre']) {
            $data['slug'] = $this->generateUniqueSlug($data['titre'], $id);
        }

        $new_thumbnail = $this->input->post('thumbnail');
        if (!empty($new_thumbnail) && $new_thumbnail !== ($current_video['miniature'] ?? '')) {
            if (!empty($current_video['miniature']) && strpos($current_video['miniature'], 'Custom/') !== false) {
                @unlink(FCPATH . $current_video['miniature']);
            }
            $data['miniature'] = $new_thumbnail;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);
        
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Vidéo mise à jour' : 'Erreur mise à jour');
        redirect(base_url('media/video'));
    }

    public function Delete()
    {
        $id = $this->input->post('id');
        $video = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        if ($video) {
            if (!empty($video['fichier'])) {
                @unlink(FCPATH . $video['fichier']);
            }
            if (!empty($video['miniature'])) {
                @unlink(FCPATH . $video['miniature']);
            }
            
            $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
                'est_actif'  => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Vidéo supprimée' : 'Erreur');
        }
        
        redirect(base_url('media/video'));
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

    public function stream($type, $id)
    {
        $video = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        if (!$video) {
            show_404();
            return;
        }
        
        $filename = null;
        
        if (!empty($video['fichier'])) {
            $filename = basename($video['fichier']);
        }
        
        if (empty($filename)) {
            show_404();
            return;
        }
        
        if ($type === 'progressive') {
            $this->serveProgressive($filename);
        } else {
            show_404();
        }
    }

    private function serveProgressive($filename)
    {
        $filename = basename($filename);
        
        $file_path = $this->paths['originals'] . $filename;
        
        if (!file_exists($file_path)) {
            $base_name = pathinfo($filename, PATHINFO_FILENAME);
            
            $encoded_files = glob($this->paths['encoded'] . $base_name . '*');
            if (!empty($encoded_files)) {
                $file_path = $encoded_files[0];
            } else {
                $file_path = $this->paths['encoded'] . $filename;
            }
        }
        
        if (!file_exists($file_path)) {
            log_message('error', 'Video file not found: ' . $file_path);
            show_404();
            return;
        }

        $file_size = filesize($file_path);
        
        header('Content-Type: video/mp4');
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

    public function uploadThumbnail()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        if (empty($_FILES['thumbnail_file'])) {
            echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu']);
            return;
        }

        if ($_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_OK) {
            $error_msg = $this->getUploadErrorMessage($_FILES['thumbnail_file']['error']);
            echo json_encode(['success' => false, 'message' => $error_msg]);
            return;
        }

        $file = $_FILES['thumbnail_file'];
        $nom_champ = $file['name'];
        $nom_file = $file['tmp_name'];
        
        $ref_folder = FCPATH . 'attachments/Video/Thumbnails/Custom/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = strtolower(pathinfo($nom_champ, PATHINFO_EXTENSION));
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg');

        if (!in_array($file_extension, $valid_ext)) {
            echo json_encode(['success' => false, 'message' => 'Format non supporté']);
            return;
        }

        if (!is_dir($ref_folder)) {
            @mkdir($ref_folder, 0777, TRUE);
        }

        $final_filename = $fichier . "." . $file_extension;
        $destination = $ref_folder . $final_filename;
        
        if (!@move_uploaded_file($nom_file, $destination)) {
            echo json_encode(['success' => false, 'message' => 'Erreur sauvegarde']);
            return;
        }

        if ($this->gd_available) {
            $this->resizeThumbnail($destination, 1280, 720);
        }

        $relative_path = 'attachments/Video/Thumbnails/Custom/' . $final_filename;
        
        echo json_encode([
            'success' => true,
            'message' => 'Miniature uploadée',
            'file_path' => $relative_path,
            'preview_url' => base_url($relative_path)
        ]);
        return;
    }

    private function resizeThumbnail($file_path, $max_width, $max_height)
    {
        if (!$this->gd_available || !function_exists('getimagesize')) {
            return false;
        }

        $image_info = @getimagesize($file_path);
        if ($image_info === false) return false;

        list($width, $height, $type) = $image_info;
        
        if ($width <= $max_width && $height <= $max_height) {
            return true;
        }

        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = (int)round($width * $ratio);
        $new_height = (int)round($height * $ratio);

        $src_image = null;
        switch ($type) {
            case IMAGETYPE_JPEG:
                if (!function_exists('imagecreatefromjpeg')) return false;
                $src_image = @imagecreatefromjpeg($file_path);
                break;
            case IMAGETYPE_PNG:
                if (!function_exists('imagecreatefrompng')) return false;
                $src_image = @imagecreatefrompng($file_path);
                break;
            case IMAGETYPE_GIF:
                if (!function_exists('imagecreatefromgif')) return false;
                $src_image = @imagecreatefromgif($file_path);
                break;
            case IMAGETYPE_WEBP:
                if (!function_exists('imagecreatefromwebp')) return false;
                $src_image = @imagecreatefromwebp($file_path);
                break;
            default:
                return false;
        }

        if (!$src_image) return false;

        $dst_image = imagecreatetruecolor($new_width, $new_height);
        
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($dst_image, false);
            imagesavealpha($dst_image, true);
            $transparent = imagecolorallocatealpha($dst_image, 255, 255, 255, 127);
            imagefilledrectangle($dst_image, 0, 0, $new_width, $new_height, $transparent);
        }

        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        $save_success = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $save_success = @imagejpeg($dst_image, $file_path, 90);
                break;
            case IMAGETYPE_PNG:
                $save_success = @imagepng($dst_image, $file_path, 6);
                break;
            case IMAGETYPE_GIF:
                $save_success = @imagegif($dst_image, $file_path);
                break;
            case IMAGETYPE_WEBP:
                $save_success = @imagewebp($dst_image, $file_path, 90);
                break;
        }

        imagedestroy($src_image);
        imagedestroy($dst_image);

        return $save_success;
    }

    // ==================== HELPERS ====================

    private function analyzeVideo($file_path)
    {
        if (!$this->ffprobe_path || !file_exists($file_path)) {
            return [
                'duration' => 0, 
                'width' => 0, 
                'height' => 0, 
                'fps' => 0, 
                'codec' => 'unknown',
                'bitrate' => 'N/A',
                'resolution' => 'N/A',
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
                'width' => 0, 
                'height' => 0, 
                'fps' => 0, 
                'codec' => 'unknown',
                'bitrate' => 'N/A',
                'resolution' => 'N/A',
                'duration_formatted' => '0:00'
            ];
        }

        $data   = json_decode(implode("\n", $output), true);
        $format = $data['format'] ?? [];
        $video  = null;
        
        foreach ($data['streams'] ?? [] as $stream) {
            if (isset($stream['codec_type']) && $stream['codec_type'] === 'video') {
                $video = $stream;
                break;
            }
        }

        $fps = 0;
        if ($video && !empty($video['r_frame_rate'])) {
            $parts = explode('/', $video['r_frame_rate']);
            if (count($parts) === 2 && $parts[1] > 0) {
                $fps = round($parts[0] / $parts[1], 2);
            }
        }

        $duration = (float)($format['duration'] ?? 0);
        $width = (int)($video['width'] ?? 0);
        $height = (int)($video['height'] ?? 0);

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
            'width'              => $width,
            'height'             => $height,
            'resolution'         => $width && $height ? $width . 'x' . $height : 'N/A',
            'fps'                => $fps,
            'codec_original'     => $video['codec_name'] ?? 'unknown',
            'bitrate'            => isset($format['bit_rate']) ? round($format['bit_rate'] / 1000) . ' kbps' : 'N/A'
        ];
    }

    private function generateThumbnails($video_path, $filename)
    {
        $result = ['default' => null, 'poster' => null];
        
        if (!$this->ffmpeg_path) {
            return $result;
        }

        $base_name  = pathinfo($filename, PATHINFO_FILENAME);
        $thumb_name = $base_name . '_thumb.jpg';
        $poster_name = $base_name . '_poster.jpg';
        $thumb_path = $this->paths['thumbnails'] . $thumb_name;
        $poster_path = $this->paths['posters'] . $poster_name;

        // Thumbnail à 1 seconde
        $cmd_thumb = sprintf(
            '%s -ss 00:00:01 -i %s -vframes 1 -q:v 5 -vf "scale=640:-1" -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path),
            escapeshellarg($video_path),
            escapeshellarg($thumb_path)
        );
        exec($cmd_thumb);

        // Poster à 5 secondes
        $cmd_poster = sprintf(
            '%s -ss 00:00:05 -i %s -vframes 1 -q:v 3 -vf "scale=1280:-1" -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path),
            escapeshellarg($video_path),
            escapeshellarg($poster_path)
        );
        exec($cmd_poster);

        if (file_exists($thumb_path)) {
            $result['default'] = 'attachments/Video/Thumbnails/' . $thumb_name;
        }
        
        if (file_exists($poster_path)) {
            $result['poster'] = 'attachments/Video/Posters/' . $poster_name;
        }
        
        return $result;
    }

    private function getAVCCapabilities()
    {
        return [
            'hardware' => ['nvenc' => false, 'vaapi' => false],
            'features' => [
                'hardware_encoding' => (bool)$this->ffmpeg_path,
                'streaming'         => true,
                'gd_available'      => $this->gd_available
            ]
        ];
    }

    private function getExistingCategories()
    {
        $this->db->select('DISTINCT(categorie) as cat');
        $this->db->where('type', 'video');
        $query = $this->db->get('galerie_medias');
        return array_filter(array_column($query->result_array(), 'cat'));
    }

    private function calculateTotalDuration()
    {
        $this->db->select_sum('duree', 'total');
        $this->db->where('type', 'video');
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
        echo "Chunk size configuré: " . round($this->video_config['chunk_size'] / 1024 / 1024, 2) . " MB\n";
        echo "FFmpeg disponible: " . ($this->ffmpeg_path ? 'OUI' : 'NON') . "\n";
        echo "GD disponible: " . ($this->gd_available ? 'OUI' : 'NON') . "\n";
        echo "</pre>";
        exit;
    }

    /**
     * Mettre à jour tous les slugs existants pour les vidéos
     */
    public function updateAllSlugs()
    {
        // Vérifier si l'utilisateur est admin (à adapter selon votre système)
        if (!$this->session->userdata('is_admin')) {
            show_404();
            return;
        }
        
        $videos = $this->db->query("
            SELECT id_media, titre FROM galerie_medias 
            WHERE type = 'video' AND est_actif = 1
        ")->result_array();
        
        $updated = 0;
        foreach ($videos as $video) {
            $slug = $this->generateUniqueSlug($video['titre'], $video['id_media']);
            
            $this->db->where('id_media', $video['id_media']);
            $this->db->update('galerie_medias', ['slug' => $slug]);
            $updated++;
            echo "ID: {$video['id_media']} - Slug: {$slug}<br>";
        }
        
        echo "<br>Total mis à jour: {$updated} slugs pour les vidéos.";
    }
}