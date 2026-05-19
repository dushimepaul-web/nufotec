<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Audio Controller - YouTube-Style Upload
 * Adapté pour serveur avec limites: upload_max_filesize=2M, post_max_size=8M
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
        is_admin();
            $this->load->library('cpanel_email_lib');
        
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
        // Adapté aux limites serveur: upload_max_filesize=2M
        // On utilise des chunks de 1.5MB pour être safe (en dessous de 2M)
        $chunk_size = 1.5 * 1024 * 1024; // 1.5 MB (1,572,864 bytes)
        
        $this->audio_config = [
            'chunk_size'        => $chunk_size,  // 1.5MB chunks
            'max_file_size'     => 500 * 1024 * 1024, // 500MB max
            'allowed_extensions' => ['mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a', 'wma', 'aiff', 'opus', 'weba'],
            'qualities' => [
                'low'    => ['bitrate' => '64k',  'suffix' => '_64k'],
                'medium' => ['bitrate' => '128k', 'suffix' => '_128k'],
                'high'   => ['bitrate' => '192k', 'suffix' => '_192k'],
                'max'    => ['bitrate' => '320k', 'suffix' => '_320k']
            ]
        ];
        
        // Log pour debug
        log_message('debug', 'Audio config - chunk_size: ' . $this->audio_config['chunk_size'] . ' bytes (' . round($this->audio_config['chunk_size'] / 1024 / 1024, 2) . ' MB)');
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
     * Générer un slug unique pour un média audio
     */
    private function generateSlug($title, $id = null)
    {
        // Nettoyer le titre
        $slug = strtolower(trim($title));
        if (empty($slug)) {
            $slug = 'audio';
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

        $chunk_size = $this->audio_config['chunk_size'];
        $total_chunks = (int)ceil($file_size / $chunk_size);
        
        // Log pour debug
        log_message('debug', 'initUpload - file: ' . $file_name . ', size: ' . $file_size . ', total_chunks: ' . $total_chunks . ', chunk_size: ' . $chunk_size);
        
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
            'ffmpeg_ready' => (bool)$this->ffmpeg_path
        ]);
        return;
    }

    public function uploadChunk()
    {
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

        $metadata = json_decode(file_get_contents($metadata_file), true);
        $chunk_path = $temp_dir . 'chunk_' . $chunk_index;
        
        // Vérification détaillée du fichier uploadé
        if (!isset($_FILES['chunk'])) {
            echo json_encode(['success' => false, 'message' => 'Aucun chunk reçu']);
            return;
        }
        
        $file_error = $_FILES['chunk']['error'];
        
        if ($file_error !== UPLOAD_ERR_OK) {
            $error_msg = $this->getUploadErrorMessage($file_error);
            echo json_encode(['success' => false, 'message' => $error_msg]);
            return;
        }
        
        // Vérifier la taille du chunk (ne doit pas dépasser chunk_size)
        $chunk_size = $_FILES['chunk']['size'];
        $max_allowed = $metadata['chunk_size'] + 1024; // Marge 1KB
        
        if ($chunk_size > $max_allowed) {
            echo json_encode([
                'success' => false, 
                'message' => 'Chunk trop grand: ' . $chunk_size . ' bytes (max: ' . $metadata['chunk_size'] . ')'
            ]);
            return;
        }
        
        // Vérifier que le fichier temporaire existe
        if (!file_exists($_FILES['chunk']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'Fichier temporaire introuvable']);
            return;
        }
        
        $temp_size = filesize($_FILES['chunk']['tmp_name']);
        
        if ($temp_size == 0) {
            echo json_encode(['success' => false, 'message' => 'Fichier vide reçu']);
            return;
        }
        
        // Sauvegarder le chunk
        $move_result = @move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path);
        
        if (!$move_result) {
            echo json_encode(['success' => false, 'message' => 'Erreur sauvegarde chunk']);
            return;
        }
        
        // Vérifier que le fichier a bien été créé
        if (!file_exists($chunk_path)) {
            echo json_encode(['success' => false, 'message' => 'Chunk non créé']);
            return;
        }
        
        $saved_size = filesize($chunk_path);
        
        if ($saved_size != $temp_size) {
            echo json_encode(['success' => false, 'message' => 'Taille incohérente']);
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
    }

    private function getUploadErrorMessage($error_code)
    {
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
                'message' => 'Chunks manquants: ' . count($missing),
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
    
    // Générer le slug automatiquement avant l'insertion
    $data['slug'] = $this->generateUniqueSlug($data['titre']);

    $rsp = $this->Model->create('galerie_medias', $data);
    
    if ($rsp) {
        // Récupérer l'audio créé pour envoyer la notification
        $new_audio = $this->Model->readOne('galerie_medias', ['id_media' => $rsp]);
        
        // Envoyer les notifications à tous les utilisateurs
        if (!empty($new_audio)) {
            $notification_result = $this->sendAudioNotification($new_audio);
            $this->session->set_flashdata('success', 'Audio créé. ' . $notification_result['success'] . ' notifications envoyées.');
        } else {
            $this->session->set_flashdata('success', 'Audio créé avec succès.');
        }
    } else {
        $this->session->set_flashdata('error', 'Erreur lors de la création de l\'audio.');
    }
    
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
    
    // Mettre à jour le slug si le titre a changé
    if ($data['titre'] != $current_audio['titre']) {
        $data['slug'] = $this->generateUniqueSlug($data['titre'], $id);
    }

    // Gestion de la miniature modifiée
    $new_thumbnail = $this->input->post('thumbnail');
    if (!empty($new_thumbnail) && $new_thumbnail !== ($current_audio['miniature'] ?? '')) {
        if (!empty($current_audio['miniature']) && strpos($current_audio['miniature'], 'Custom/') !== false) {
            @unlink(FCPATH . $current_audio['miniature']);
        }
        $data['miniature'] = $new_thumbnail;
    }

    $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);
    
    // Optionnel: envoyer notification de mise à jour
    if ($rsp && $data['est_actif'] == 1) {
        $updated_audio = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        if (!empty($updated_audio)) {
            $this->sendAudioNotification($updated_audio);
        }
    }
    
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

    public function uploadThumbnail()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        if (empty($_FILES['thumbnail_file']) || $_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'success' => false, 
                'message' => 'Aucun fichier reçu'
            ]);
            return;
        }

        $file = $_FILES['thumbnail_file'];
        $nom_champ = $file['name'];
        $nom_file = $file['tmp_name'];
        
        $ref_folder = FCPATH . 'attachments/Audio/Thumbnails/Custom/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = strtolower(pathinfo($nom_champ, PATHINFO_EXTENSION));
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg');

        if (!in_array($file_extension, $valid_ext)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Format non supporté'
            ]);
            return;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        $final_filename = $fichier . "." . $file_extension;
        $destination = $ref_folder . $final_filename;
        
        if (!move_uploaded_file($nom_file, $destination)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Erreur sauvegarde'
            ]);
            return;
        }

        if ($this->gd_available) {
            $this->resizeThumbnail($destination, 800, 800);
        }

        $relative_path = 'attachments/Audio/Thumbnails/Custom/' . $final_filename;
        
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
            return;
        }

        list($width, $height, $type) = getimagesize($file_path);
        
        if ($width === false || $height === false) {
            return;
        }

        if ($width <= $max_width && $height <= $max_height) {
            return;
        }

        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = round($width * $ratio);
        $new_height = round($height * $ratio);

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

        $dst_image = imagecreatetruecolor($new_width, $new_height);
        
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($dst_image, false);
            imagesavealpha($dst_image, true);
        }

        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

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
            @unlink($cover_path);
        }

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
        echo "Chunk size configuré: " . round($this->audio_config['chunk_size'] / 1024 / 1024, 2) . " MB\n";
        echo "</pre>";
        exit;
    }

    /**
     * Mettre à jour tous les slugs existants pour les audios
     */
    public function updateAllSlugs()
    {
        // Vérifier si l'utilisateur est admin (à adapter selon votre système)
        if (!$this->session->userdata('is_admin')) {
            show_404();
            return;
        }
        
        $audios = $this->db->query("
            SELECT id_media, titre FROM galerie_medias 
            WHERE type = 'audio' AND est_actif = 1
        ")->result_array();
        
        $updated = 0;
        foreach ($audios as $audio) {
            $slug = $this->generateUniqueSlug($audio['titre'], $audio['id_media']);
            
            $this->db->where('id_media', $audio['id_media']);
            $this->db->update('galerie_medias', ['slug' => $slug]);
            $updated++;
            echo "ID: {$audio['id_media']} - Slug: {$slug}<br>";
        }
        
        echo "<br>Total mis à jour: {$updated} slugs pour les audios.";
    }




    // ==================== NOTIFICATION NOUVEAU AUDIO ====================

/**
 * Envoyer une notification à tous les utilisateurs pour un nouvel audio
 */
private function sendAudioNotification($audio_data)
{
    try {
        // Récupérer tous les emails des utilisateurs actifs
        $emails = $this->getAllUserEmails();
        
        if (empty($emails)) {
            log_message('info', "Aucun email trouvé pour la notification audio ID " . $audio_data['id_media']);
            return ['success' => 0, 'error' => 0];
        }
        
        // Récupérer les informations du site
        $site_logo = $this->Model->get_setting('site_logo');
        $site_name = $this->Model->get_setting('site_name', 'NUFOTEC BURUNDI');
        $logo_url = !empty($site_logo) ? base_url('attachments/Configurations/' . $site_logo) : '';
        $linkgroupewhatsapp = $this->Model->get_setting('linkgroupewhatsapp');
        $whatsapp_link = !empty($linkgroupewhatsapp) ? $linkgroupewhatsapp : '#';
        
        // Construire l'URL du détail audio
        $audio_slug = !empty($audio_data['slug']) ? $audio_data['slug'] : $audio_data['id_media'];
        $audio_url = base_url('media/detail/' . $audio_slug);
        
        // Récupérer la miniature
        $thumbnail_url = !empty($audio_data['miniature']) 
            ? base_url($audio_data['miniature']) 
            : base_url('assets/images/audio-default.png');
        
        $success_count = 0;
        $error_count = 0;
        $max_emails = 50;
        $email_count = 0;
        
        $subject = "🎵 NOUVEAU AUDIO - " . htmlspecialchars($site_name);
        
        foreach ($emails as $email) {
            if ($email_count >= $max_emails) {
                log_message('warning', "Limite d'emails atteinte ({$max_emails}) pour notification audio");
                break;
            }
            
            $message = $this->buildAudioNotificationTemplate(
                htmlspecialchars($audio_data['titre']),
                nl2br(htmlspecialchars($audio_data['description'] ?? '')),
                $thumbnail_url,
                $audio_url,
                $subject,
                $site_name,
                $logo_url,
                $whatsapp_link
            );
            
            $result = $this->cpanel_email_lib->send_email($email, $subject, $message);
            if ($result['success']) {
                $success_count++;
            } else {
                $error_count++;
                log_message('error', "Échec d'envoi à {$email} pour notification audio: " . print_r($result, true));
            }
            $email_count++;
        }
        
        log_message('info', "Notifications audio envoyées: {$success_count} succès, {$error_count} échecs");
        return ['success' => $success_count, 'error' => $error_count];
        
    } catch (Exception $e) {
        log_message('error', "Erreur lors de l'envoi des notifications audio: " . $e->getMessage());
        return ['success' => 0, 'error' => 1];
    }
}

/**
 * Récupérer tous les emails des utilisateurs actifs
 */
private function getAllUserEmails()
{
    $emails = [];
    
    $active_users = $this->Model->read('users', array('is_active' => 1, 'deleted_at' => null), 'id', 'ASC');
    $newsletter_emails = $this->Model->read('newsletter', null, 'id_newsletter', 'ASC');
    
    foreach ($active_users as $user) {
        if (!empty($user['email']) && filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
            $emails[$user['email']] = $user['email'];
        }
    }
    
    foreach ($newsletter_emails as $newsletter) {
        if (!empty($newsletter['email']) && filter_var($newsletter['email'], FILTER_VALIDATE_EMAIL)) {
            $emails[$newsletter['email']] = $newsletter['email'];
        }
    }
    
    return array_values($emails);
}

/**
 * Construire le template HTML pour la notification audio
 */
private function buildAudioNotificationTemplate($title, $description, $thumbnail_url, $audio_url, $subject, $site_name, $logo_url, $whatsapp_link)
{
    $current_date = date('d/m/Y');
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $subject . '</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
                background-color: #f4f6f9;
                margin: 0;
                padding: 20px;
                line-height: 1.5;
            }
            .container {
                max-width: 560px;
                margin: 0 auto;
                background: #ffffff;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            }
            .header {
                background: linear-gradient(135deg, #0a2540, #0f4c3a);
                padding: 30px 24px;
                text-align: center;
            }
            .header-logo {
                max-width: 100px;
                margin-bottom: 15px;
            }
            .header h1 {
                color: #ffffff;
                font-size: 24px;
                font-weight: 700;
                margin: 0;
            }
            .header p {
                color: rgba(255,255,255,0.8);
                font-size: 14px;
                margin: 8px 0 0;
            }
            .thumbnail {
                width: 100%;
                height: auto;
                max-height: 300px;
                object-fit: cover;
            }
            .content {
                padding: 28px;
            }
            .audio-title {
                font-size: 22px;
                font-weight: 700;
                color: #1a2a3a;
                margin-bottom: 15px;
            }
            .description {
                color: #5a6a7a;
                font-size: 14px;
                margin: 20px 0;
                line-height: 1.6;
            }
            .btn-listen {
                display: inline-block;
                background: #0a66c2;
                color: white;
                padding: 12px 28px;
                text-decoration: none;
                border-radius: 40px;
                font-weight: 600;
                font-size: 14px;
                margin: 10px 0;
            }
            .btn-whatsapp {
                display: inline-block;
                background: #25D366;
                color: white;
                padding: 10px 24px;
                text-decoration: none;
                border-radius: 40px;
                font-weight: 600;
                font-size: 13px;
                margin: 5px;
            }
            .social-links {
                margin: 15px 0;
                text-align: center;
            }
            .footer {
                background: #f8fafc;
                padding: 20px;
                text-align: center;
                border-top: 1px solid #eef2f6;
            }
            .footer-text {
                font-size: 12px;
                color: #9aaab9;
            }
            .date {
                color: #8a9aaa;
                font-size: 12px;
                margin-bottom: 15px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                ' . (!empty($logo_url) ? '<img src="' . $logo_url . '" alt="' . htmlspecialchars($site_name) . '" class="header-logo">' : '') . '
                <h1>🎵 Nouvel Audio disponible</h1>
                <p>' . htmlspecialchars($site_name) . '</p>
            </div>
            <img src="' . $thumbnail_url . '" alt="' . htmlspecialchars($title) . '" class="thumbnail">
            <div class="content">
                <div class="audio-title">' . htmlspecialchars($title) . '</div>
                <div class="date">📅 Publié le ' . $current_date . '</div>
                <div class="description">' . $description . '</div>
                <div style="text-align: center;">
                    <a href="' . $audio_url . '" class="btn-listen">🎧 Écouter ce contenu</a>
                </div>
            </div>
            <div class="footer">
                <div class="social-links">
                    ' . ($whatsapp_link != '#' ? '<a href="' . $whatsapp_link . '" class="btn-whatsapp" target="_blank">📱 Rejoignez notre groupe WhatsApp</a>' : '') . '
                </div>
                <div class="footer-text">© ' . date('Y') . ' ' . htmlspecialchars($site_name) . ' - Votre partenaire santé naturelle</div>
                <div class="footer-text"><a href="' . base_url() . '" style="color:#9aaab9;">Visitez notre site</a></div>
            </div>
        </div>
    </body>
    </html>';
}
}