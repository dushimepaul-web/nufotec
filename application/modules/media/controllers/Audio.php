<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Audio Controller - YouTube-Style Upload (CORRIGÉ)
 * Upload chunked robuste - chunks de 1.5MB max - fichiers illimités
 * Compatible: upload_max_filesize=2M, post_max_size=8M
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

        // ⚠️ CSRF: désactiver GLOBALEMENT pour ce controller
        // Mettre dans config/autoload ou dans ce constructeur AVANT tout
        $this->config->set_item('csrf_protection', FALSE);

        $this->initializePaths();
        $this->initializeConfig();
        $this->detectFFmpegTools();
        $this->checkGDAvailability();
        $this->ensureDirectories();
        is_admin();

        $this->load->model('media/Model_media', 'Model');
    }

    // ==================== INITIALISATION ====================

    private function initializePaths()
    {
        $base = FCPATH;
        $this->paths = [
            'temp'       => $base . 'uploads/temp/audio/',
            'originals'  => $base . 'attachments/Audio/Originals/',
            'converted'  => $base . 'attachments/Audio/Converted/',
            'thumbnails' => $base . 'attachments/Audio/Thumbnails/',
            'covers'     => $base . 'attachments/Audio/Covers/',
            'waveforms'  => $base . 'attachments/Audio/Waveforms/',
            'logs'       => $base . 'attachments/Audio/Logs/',
        ];
    }

    private function initializeConfig()
    {
        // CHUNK: 1.5MB (1,572,864 bytes) - bien en dessous de upload_max_filesize=2M
        $chunk_size = intval(1.5 * 1024 * 1024); // 1,572,864 bytes

        $this->audio_config = [
            'chunk_size'         => $chunk_size,
            'max_file_size'      => 10 * 1024 * 1024 * 1024, // 10 GB max (illimité côté app)
            'allowed_extensions' => ['mp3','wav','flac','aac','ogg','m4a','wma','aiff','opus','weba'],
            'qualities' => [
                'low'    => ['bitrate' => '64k',  'suffix' => '_64k'],
                'medium' => ['bitrate' => '128k', 'suffix' => '_128k'],
                'high'   => ['bitrate' => '192k', 'suffix' => '_192k'],
                'max'    => ['bitrate' => '320k', 'suffix' => '_320k']
            ]
        ];

        // Forcer les limites PHP pour ce script
        @set_time_limit(0);
        @ini_set('memory_limit', '256M');
    }

    private function detectFFmpegTools()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->ffmpeg_path  = 'C:\\ffmpeg\\bin\\ffmpeg.exe';
            $this->ffprobe_path = 'C:\\ffmpeg\\bin\\ffprobe.exe';
            if (!file_exists($this->ffmpeg_path)) {
                $this->ffmpeg_path  = $this->findExecutable(['ffmpeg']);
                $this->ffprobe_path = $this->findExecutable(['ffprobe']);
            }
        } else {
            $this->ffmpeg_path  = $this->findExecutable(['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg']);
            $this->ffprobe_path = $this->findExecutable(['ffprobe', '/usr/bin/ffprobe', '/usr/local/bin/ffprobe']);
        }
    }

    private function findExecutable($candidates)
    {
        foreach ($candidates as $cmd) {
            if (empty($cmd)) continue;
            $output = []; $return = 0;
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

    // ==================== RÉPONSE JSON HELPER ====================

    /**
     * Envoyer une réponse JSON propre (nettoyer tout output parasite)
     */
    private function jsonResponse($data)
    {
        // Nettoyer tout output parasite (headers PHP, warnings, etc.)
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Désactiver tout output buffer supplémentaire
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('X-Content-Type-Options: nosniff');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ==================== SLUGS ====================

    private function generateSlug($title, $id = null)
    {
        $slug = strtolower(trim($title));
        if (empty($slug)) $slug = 'audio';

        $replacements = [
            ' '=>'-',"'"=>'-','"'=>'-',
            'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'à'=>'a','â'=>'a','ä'=>'a',
            'î'=>'i','ï'=>'i',
            'ô'=>'o','ö'=>'o',
            'ù'=>'u','û'=>'u','ü'=>'u',
            'ç'=>'c','œ'=>'oe',
            '/'=>'-','\\'=>'-','&'=>'et',
            '?'=>'','!'=>'','.'=>'-',','=>'-',
            ';'=>'-',':'=>'-','('=>'',')'=>'',
            '['=>'',']'=>'','{'=>'','}'=>'',
            '+'=>'-','*'=>'','#'=>'','@'=>'',
            '%'=>'','^'=>'','='=>'-'
        ];

        foreach ($replacements as $s => $r) $slug = str_replace($s, $r, $slug);
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        if (strlen($slug) > 80) $slug = rtrim(substr($slug, 0, 80), '-');
        if ($id) $slug = $slug . '-' . $id;

        return $slug;
    }

    private function generateUniqueSlug($title, $id = null)
    {
        $slug = $this->generateSlug($title, $id);

        if (!$id) {
            $exists = $this->db->query("SELECT id_media FROM galerie_medias WHERE slug = ?", [$slug])->num_rows();
            if ($exists > 0) {
                $c = 2; $base = $slug;
                while ($this->db->query("SELECT id_media FROM galerie_medias WHERE slug = ?", [$slug])->num_rows() > 0) {
                    $slug = $base . '-' . $c++;
                }
            }
        } else {
            $exists = $this->db->query("SELECT id_media FROM galerie_medias WHERE slug = ? AND id_media != ?", [$slug, $id])->num_rows();
            if ($exists > 0) {
                $c = 2; $base = $slug;
                while ($this->db->query("SELECT id_media FROM galerie_medias WHERE slug = ? AND id_media != ?", [$slug, $id])->num_rows() > 0) {
                    $slug = $base . '-' . $c++;
                }
            }
        }

        return $slug;
    }

    // ==================== VUE PRINCIPALE ====================

    public function index()
    {
        $data = [
            'audios'             => $this->Model->read('galerie_medias', ['type' => 'audio'], 'id_media', 'DESC'),
            'categories'         => $this->getExistingCategories(),
            'total_duration'     => $this->calculateTotalDuration(),
            'storage_stats'      => $this->getStorageStatistics(),
            'audio_capabilities' => $this->getAudioCapabilities()
        ];
        $this->load->view('Audio_View', $data);
    }

    // ==================== API UPLOAD ====================

    /**
     * ÉTAPE 1: Initialiser l'upload - retourne upload_id, chunk_size, total_chunks
     */
    public function initUpload()
    {
        @set_time_limit(60);

        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');

        if (empty($file_name) || $file_size <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Paramètres invalides (nom ou taille manquant)']);
        }

        if ($file_size > $this->audio_config['max_file_size']) {
            $this->jsonResponse(['success' => false, 'message' => 'Fichier trop grand (max 10GB)']);
        }

        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->audio_config['allowed_extensions'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Format non supporté: ' . $ext]);
        }

        // Créer un ID d'upload unique
        $upload_id = 'audio_' . uniqid() . '_' . bin2hex(random_bytes(4));
        $temp_dir  = $this->paths['temp'] . $upload_id . '/';

        if (!@mkdir($temp_dir, 0777, true)) {
            $this->jsonResponse(['success' => false, 'message' => 'Impossible de créer le dossier temporaire']);
        }

        $chunk_size   = $this->audio_config['chunk_size'];
        $total_chunks = (int)ceil($file_size / $chunk_size);

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

        if (file_put_contents($temp_dir . 'metadata.json', json_encode($metadata)) === false) {
            $this->jsonResponse(['success' => false, 'message' => 'Impossible d\'écrire les métadonnées']);
        }

        log_message('debug', "initUpload: file={$file_name}, size={$file_size}, chunks={$total_chunks}, chunk_size={$chunk_size}");

        $this->jsonResponse([
            'success'      => true,
            'upload_id'    => $upload_id,
            'chunk_size'   => $chunk_size,
            'total_chunks' => $total_chunks,
            'ffmpeg_ready' => (bool)$this->ffmpeg_path,
            'debug'        => [
                'chunk_size_mb'    => round($chunk_size / 1024 / 1024, 2),
                'total_chunks'     => $total_chunks,
                'server_limit'     => ini_get('upload_max_filesize')
            ]
        ]);
    }

    /**
     * ÉTAPE 2: Recevoir un chunk
     */
    public function uploadChunk()
    {
        @set_time_limit(120);

        $upload_id   = $this->input->post('upload_id');
        $chunk_index = (int)$this->input->post('chunk_index');

        // Validation basique
        if (empty($upload_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'upload_id manquant']);
        }

        $temp_dir      = $this->paths['temp'] . $upload_id . '/';
        $metadata_file = $temp_dir . 'metadata.json';

        if (!file_exists($metadata_file)) {
            $this->jsonResponse(['success' => false, 'message' => 'Session upload non trouvée pour: ' . $upload_id]);
        }

        $metadata = json_decode(file_get_contents($metadata_file), true);
        if (!$metadata) {
            $this->jsonResponse(['success' => false, 'message' => 'Métadonnées corrompues']);
        }

        // Vérifier index valide
        if ($chunk_index < 0 || $chunk_index >= $metadata['total_chunks']) {
            $this->jsonResponse(['success' => false, 'message' => "Index chunk invalide: {$chunk_index} / {$metadata['total_chunks']}"]);
        }

        // Vérifier présence du fichier
        if (!isset($_FILES['chunk'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Champ "chunk" manquant dans la requête']);
        }

        $file_error = $_FILES['chunk']['error'];
        if ($file_error !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'message' => $this->getUploadErrorMessage($file_error)]);
        }

        // Vérifier taille du chunk (avec marge de 10KB pour les headers multipart)
        $chunk_size_received = $_FILES['chunk']['size'];
        $max_allowed         = $metadata['chunk_size'] + (10 * 1024); // +10KB marge

        if ($chunk_size_received > $max_allowed) {
            $this->jsonResponse([
                'success' => false,
                'message' => "Chunk trop grand: {$chunk_size_received} bytes (max autorisé: {$max_allowed})"
            ]);
        }

        if ($chunk_size_received === 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Chunk vide reçu (0 bytes)']);
        }

        $tmp_path   = $_FILES['chunk']['tmp_name'];
        $chunk_path = $temp_dir . 'chunk_' . $chunk_index;

        if (!file_exists($tmp_path)) {
            $this->jsonResponse(['success' => false, 'message' => 'Fichier temporaire PHP introuvable']);
        }

        // Déplacer le chunk
        if (!@move_uploaded_file($tmp_path, $chunk_path)) {
            $this->jsonResponse(['success' => false, 'message' => "Impossible de sauvegarder le chunk {$chunk_index}"]);
        }

        // Vérifier que le fichier sauvegardé a la bonne taille
        $saved_size = filesize($chunk_path);
        if ($saved_size !== $chunk_size_received) {
            @unlink($chunk_path);
            $this->jsonResponse(['success' => false, 'message' => "Taille incohérente: reçu={$chunk_size_received}, sauvegardé={$saved_size}"]);
        }

        // Mettre à jour les métadonnées
        if (!in_array($chunk_index, $metadata['uploaded_chunks'])) {
            $metadata['uploaded_chunks'][] = $chunk_index;
            sort($metadata['uploaded_chunks']);
            file_put_contents($metadata_file, json_encode($metadata));
        }

        $uploaded = count($metadata['uploaded_chunks']);
        $percent  = round(($uploaded / $metadata['total_chunks']) * 100, 1);

        $this->jsonResponse([
            'success'  => true,
            'message'  => "Chunk {$chunk_index} reçu ({$saved_size} bytes)",
            'progress' => [
                'uploaded_chunks' => $uploaded,
                'total_chunks'    => $metadata['total_chunks'],
                'percent'         => $percent,
                'bytes_saved'     => $saved_size
            ]
        ]);
    }

    private function getUploadErrorMessage($code)
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'Chunk dépasse upload_max_filesize (' . ini_get('upload_max_filesize') . '). Réduisez la taille du chunk.',
            UPLOAD_ERR_FORM_SIZE  => 'Chunk dépasse MAX_FILE_SIZE du formulaire',
            UPLOAD_ERR_PARTIAL    => 'Upload partiel - réseau instable, réessayez',
            UPLOAD_ERR_NO_FILE    => 'Aucun fichier reçu dans la requête',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire PHP manquant (php.ini: upload_tmp_dir)',
            UPLOAD_ERR_CANT_WRITE => 'Erreur écriture disque (permissions?)',
            UPLOAD_ERR_EXTENSION  => 'Upload bloqué par extension PHP'
        ];
        return $messages[$code] ?? 'Erreur upload inconnue: ' . $code;
    }

    /**
     * ÉTAPE 3: Assembler tous les chunks en fichier final
     */
    public function completeUpload()
    {
        @set_time_limit(300); // 5 minutes pour l'assemblage + traitement

        $upload_id = $this->input->post('upload_id');

        if (empty($upload_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'upload_id manquant']);
        }

        $temp_dir      = $this->paths['temp'] . $upload_id . '/';
        $metadata_file = $temp_dir . 'metadata.json';

        if (!file_exists($metadata_file)) {
            $this->jsonResponse(['success' => false, 'message' => 'Session non trouvée']);
        }

        $metadata = json_decode(file_get_contents($metadata_file), true);

        // Vérifier tous les chunks sont présents
        $missing = [];
        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            $chunk_path = $temp_dir . 'chunk_' . $i;
            if (!file_exists($chunk_path) || filesize($chunk_path) === 0) {
                $missing[] = $i;
            }
        }

        if (!empty($missing)) {
            $this->jsonResponse([
                'success' => false,
                'message' => count($missing) . ' chunk(s) manquant(s)',
                'missing' => $missing
            ]);
        }

        // Construire le nom du fichier final
        $safe_name     = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($metadata['file_name'], PATHINFO_FILENAME));
        $ext           = strtolower(pathinfo($metadata['file_name'], PATHINFO_EXTENSION));
        $original_name = date('YmdHis') . '_' . $safe_name . '_audio.' . $ext;
        $original_path = $this->paths['originals'] . $original_name;

        // Assembler les chunks
        $out = fopen($original_path, 'wb');
        if (!$out) {
            $this->jsonResponse(['success' => false, 'message' => 'Impossible de créer le fichier final']);
        }

        $total_written = 0;
        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            $chunk_file = $temp_dir . 'chunk_' . $i;
            $chunk_data = file_get_contents($chunk_file);

            if ($chunk_data === false) {
                fclose($out);
                @unlink($original_path);
                $this->jsonResponse(['success' => false, 'message' => "Impossible de lire le chunk {$i}"]);
            }

            $written = fwrite($out, $chunk_data);
            if ($written === false) {
                fclose($out);
                @unlink($original_path);
                $this->jsonResponse(['success' => false, 'message' => "Erreur écriture chunk {$i}"]);
            }

            $total_written += $written;
            unlink($chunk_file);
            unset($chunk_data); // Libérer la mémoire
        }
        fclose($out);

        // Nettoyer le dossier temporaire
        @unlink($metadata_file);
        @rmdir($temp_dir);

        // Vérifier l'intégrité du fichier assemblé
        $final_size = filesize($original_path);
        if ($final_size !== (int)$metadata['file_size']) {
            log_message('warning', "Audio assemblé: taille attendue={$metadata['file_size']}, obtenu={$final_size}");
        }

        // Traitements audio
        $analysis    = $this->analyzeAudio($original_path);
        $thumbnails  = $this->generateThumbnails($original_path, $original_name);
        $waveform    = $this->generateWaveform($original_path, $original_name);
        $conversions = $this->convertToMultipleBitrates($original_path, $original_name);

        // Normaliser thumbnails en objet
        $thumbnails_obj = new stdClass();
        if (!empty($thumbnails['cover']))     $thumbnails_obj->cover     = $thumbnails['cover'];
        if (!empty($thumbnails['generated'])) $thumbnails_obj->generated = $thumbnails['generated'];

        $this->jsonResponse([
            'success' => true,
            'message' => 'Upload et traitement terminés',
            'data'    => [
                'original_file'    => 'attachments/Audio/Originals/' . $original_name,
                'file_size'        => $this->formatBytes($final_size),
                'file_size_bytes'  => $final_size,
                'analysis'         => $analysis,
                'thumbnails'       => $thumbnails_obj,
                'waveform'         => $waveform,
                'conversions'      => $conversions,
                'form_suggestions' => [
                    'titre'     => $analysis['title']  ?: $this->suggestTitle($metadata['file_name']),
                    'credits'   => $analysis['artist'] ?: 'Artiste inconnu',
                    'categorie' => $this->suggestCategory($analysis)
                ]
            ]
        ]);
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

        $data['slug'] = $this->generateUniqueSlug($data['titre']);

        $rsp = $this->Model->create('galerie_medias', $data);
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Audio créé avec succès' : 'Erreur lors de la création');
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

        if ($data['titre'] != ($current_audio['titre'] ?? '')) {
            $data['slug'] = $this->generateUniqueSlug($data['titre'], $id);
        }

        $new_thumbnail = $this->input->post('thumbnail');
        if (!empty($new_thumbnail) && $new_thumbnail !== ($current_audio['miniature'] ?? '')) {
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
        $id    = $this->input->post('id');
        $audio = $this->Model->readOne('galerie_medias', ['id_media' => $id]);

        if ($audio) {
            if (!empty($audio['fichier']))   @unlink(FCPATH . $audio['fichier']);
            if (!empty($audio['miniature'])) @unlink(FCPATH . $audio['miniature']);

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
        $id     = $this->input->post('id');
        $status = $this->input->post('est_actif');

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            'est_actif'  => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->jsonResponse(['success' => (bool)$rsp]);
    }

    public function toggleField()
    {
        $id    = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');

        $allowed = ['is_for_whatsapp', 'is_for_website', 'est_actif'];
        if (!in_array($field, $allowed)) {
            $this->jsonResponse(['success' => false, 'message' => 'Champ non autorisé']);
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            $field       => $value,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->jsonResponse(['success' => (bool)$rsp]);
    }

    // ==================== STREAMING ====================

    public function stream($id)
    {
        $audio = $this->Model->readOne('galerie_medias', ['id_media' => $id]);

        if (!$audio) { show_404(); return; }

        $filename = !empty($audio['fichier']) ? basename($audio['fichier']) : null;
        if (empty($filename)) { show_404(); return; }

        $this->serveAudio($filename);
    }

    private function serveAudio($filename)
    {
        $filename  = basename($filename);
        $file_path = $this->paths['originals'] . $filename;

        if (!file_exists($file_path)) {
            $base_name = pathinfo($filename, PATHINFO_FILENAME);
            $converted = glob($this->paths['converted'] . $base_name . '*');
            if (!empty($converted)) $file_path = $converted[0];
            else $file_path = $this->paths['converted'] . $filename;
        }

        if (!file_exists($file_path)) {
            log_message('error', 'Audio not found: ' . $file_path);
            show_404(); return;
        }

        $file_size = filesize($file_path);
        $start = 0;
        $end   = $file_size - 1;

        header('Content-Type: audio/mpeg');
        header('Accept-Ranges: bytes');
        header('Cache-Control: public, max-age=31536000');

        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
                $start = intval($m[1]);
                if (!empty($m[2])) $end = intval($m[2]);
                header('HTTP/1.1 206 Partial Content');
                header("Content-Range: bytes {$start}-{$end}/{$file_size}");
            }
        }

        header('Content-Length: ' . ($end - $start + 1));

        $fp = fopen($file_path, 'rb');
        if (!$fp) { show_404(); return; }

        fseek($fp, $start);
        $bytes_to_send = $end - $start + 1;
        $bytes_sent    = 0;

        while (!feof($fp) && $bytes_sent < $bytes_to_send) {
            $buffer = fread($fp, min(8192, $bytes_to_send - $bytes_sent));
            if ($buffer === false) break;
            echo $buffer;
            flush();
            $bytes_sent += strlen($buffer);
        }

        fclose($fp);
    }

    // ==================== UPLOAD MINIATURE ====================

    public function uploadThumbnail()
    {
        if (empty($_FILES['thumbnail_file']) || $_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'message' => 'Aucun fichier reçu']);
        }

        $file      = $_FILES['thumbnail_file'];
        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $valid_ext = ['gif', 'jpg', 'png', 'jpeg', 'webp'];

        if (!in_array($ext, $valid_ext)) {
            $this->jsonResponse(['success' => false, 'message' => 'Format non supporté: ' . $ext]);
        }

        $ref_folder = FCPATH . 'attachments/Audio/Thumbnails/Custom/';
        if (!is_dir($ref_folder)) @mkdir($ref_folder, 0777, true);

        $code           = date("YmdHis") . uniqid();
        $final_filename = $code . '.' . $ext;
        $destination    = $ref_folder . $final_filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur sauvegarde miniature']);
        }

        if ($this->gd_available) $this->resizeThumbnail($destination, 800, 800);

        $relative_path = 'attachments/Audio/Thumbnails/Custom/' . $final_filename;

        $this->jsonResponse([
            'success'     => true,
            'message'     => 'Miniature uploadée',
            'file_path'   => $relative_path,
            'preview_url' => base_url($relative_path)
        ]);
    }

    private function resizeThumbnail($file_path, $max_width, $max_height)
    {
        if (!$this->gd_available) return;
        list($width, $height, $type) = getimagesize($file_path);
        if (!$width || !$height) return;
        if ($width <= $max_width && $height <= $max_height) return;

        $ratio      = min($max_width / $width, $max_height / $height);
        $new_width  = round($width * $ratio);
        $new_height = round($height * $ratio);

        $src = null;
        switch ($type) {
            case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($file_path); break;
            case IMAGETYPE_PNG:  $src = @imagecreatefrompng($file_path);  break;
            case IMAGETYPE_GIF:  $src = @imagecreatefromgif($file_path);  break;
            case IMAGETYPE_WEBP: $src = @imagecreatefromwebp($file_path); break;
        }
        if (!$src) return;

        $dst = imagecreatetruecolor($new_width, $new_height);
        if ($type == IMAGETYPE_PNG) { imagealphablending($dst, false); imagesavealpha($dst, true); }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        switch ($type) {
            case IMAGETYPE_JPEG: imagejpeg($dst, $file_path, 90); break;
            case IMAGETYPE_PNG:  imagepng($dst, $file_path, 6);   break;
            case IMAGETYPE_GIF:  imagegif($dst, $file_path);      break;
            case IMAGETYPE_WEBP: imagewebp($dst, $file_path, 90); break;
        }
        imagedestroy($src); imagedestroy($dst);
    }

    // ==================== HELPERS AUDIO ====================

    private function analyzeAudio($file_path)
    {
        $default = [
            'duration' => 0, 'duration_formatted' => '0:00',
            'size' => 0, 'bitrate' => 0, 'sample_rate' => 0,
            'channels' => 0, 'codec' => 'unknown',
            'title' => null, 'artist' => null, 'album' => null,
            'year' => null, 'genre' => null
        ];

        if (!$this->ffprobe_path || !file_exists($file_path)) return $default;

        $cmd = sprintf('%s -v quiet -print_format json -show_format -show_streams %s 2>&1',
            escapeshellarg($this->ffprobe_path), escapeshellarg($file_path));

        exec($cmd, $output, $code);
        if ($code !== 0) return $default;

        $data   = json_decode(implode("\n", $output), true);
        $format = $data['format'] ?? [];
        $audio  = null;

        foreach ($data['streams'] ?? [] as $stream) {
            if (($stream['codec_type'] ?? '') === 'audio') { $audio = $stream; break; }
        }

        $tags     = $format['tags'] ?? [];
        $duration = (float)($format['duration'] ?? 0);
        $h = floor($duration / 3600);
        $m = floor(($duration % 3600) / 60);
        $s = floor($duration % 60);
        $dur_fmt = $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);

        return [
            'duration'           => $duration,
            'duration_formatted' => $dur_fmt,
            'size'               => (int)($format['size'] ?? filesize($file_path)),
            'bitrate'            => (int)($format['bit_rate'] ?? 0),
            'sample_rate'        => (int)($audio['sample_rate'] ?? 0),
            'channels'           => (int)($audio['channels'] ?? 0),
            'codec'              => $audio['codec_name'] ?? 'unknown',
            'title'              => $tags['title']  ?? $tags['TITLE']  ?? null,
            'artist'             => $tags['artist'] ?? $tags['ARTIST'] ?? null,
            'album'              => $tags['album']  ?? $tags['ALBUM']  ?? null,
            'year'               => $tags['date']   ?? $tags['DATE']   ?? null,
            'genre'              => $tags['genre']  ?? $tags['GENRE']  ?? null
        ];
    }

    private function generateThumbnails($audio_path, $filename)
    {
        $result    = ['cover' => null, 'generated' => null];
        if (!$this->ffmpeg_path) return $result;

        $base_name  = pathinfo($filename, PATHINFO_FILENAME);
        $cover_name = $base_name . '_cover.jpg';
        $cover_path = $this->paths['thumbnails'] . $cover_name;

        exec(sprintf('%s -i %s -an -vcodec copy -f image2 -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path), escapeshellarg($audio_path), escapeshellarg($cover_path)));

        if (file_exists($cover_path) && filesize($cover_path) > 1000) {
            $result['cover'] = 'attachments/Audio/Thumbnails/' . $cover_name;
        } else {
            @unlink($cover_path);
            $gen_name = $base_name . '_waveform.jpg';
            $gen_path = $this->paths['thumbnails'] . $gen_name;
            exec(sprintf('%s -i %s -filter_complex "aformat=channel_layouts=mono,showwavespic=s=800x800:colors=#FF0000|#FF6B6B" -frames:v 1 -y %s 2>&1',
                escapeshellarg($this->ffmpeg_path), escapeshellarg($audio_path), escapeshellarg($gen_path)));
            if (file_exists($gen_path)) $result['generated'] = 'attachments/Audio/Thumbnails/' . $gen_name;
        }

        return $result;
    }

    private function generateWaveform($audio_path, $filename)
    {
        if (!$this->ffmpeg_path) return null;
        $base_name     = pathinfo($filename, PATHINFO_FILENAME);
        $waveform_name = $base_name . '_wave.png';
        $waveform_path = $this->paths['waveforms'] . $waveform_name;

        exec(sprintf('%s -i %s -filter_complex "aformat=channel_layouts=mono,showwavespic=s=1200x200:colors=#FF0000|#FF6B6B" -frames:v 1 -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path), escapeshellarg($audio_path), escapeshellarg($waveform_path)));

        return file_exists($waveform_path) ? 'attachments/Audio/Waveforms/' . $waveform_name : null;
    }

    private function convertToMultipleBitrates($audio_path, $filename)
    {
        if (!$this->ffmpeg_path) return [];
        $base_name   = pathinfo($filename, PATHINFO_FILENAME);
        $conversions = [];

        foreach ($this->audio_config['qualities'] as $quality => $config) {
            $output_name = $base_name . $config['suffix'] . '.mp3';
            $output_path = $this->paths['converted'] . $output_name;

            exec(sprintf('%s -i %s -codec:a libmp3lame -b:a %s -map_metadata 0 -id3v2_version 3 -y %s 2>&1',
                escapeshellarg($this->ffmpeg_path), escapeshellarg($audio_path),
                $config['bitrate'], escapeshellarg($output_path)), $out, $code);

            if ($code === 0 && file_exists($output_path)) {
                $conversions[$quality] = [
                    'path'           => 'attachments/Audio/Converted/' . $output_name,
                    'bitrate'        => $config['bitrate'],
                    'size'           => filesize($output_path),
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
        return array_filter(array_column($this->db->get('galerie_medias')->result_array(), 'cat'));
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
            if (is_dir($path)) $total += $this->getDirSize($path);
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
        return ucwords(trim(preg_replace('/[^a-zA-Z0-9]/', ' ', $name)));
    }

    private function suggestCategory($analysis)
    {
        $genre    = strtolower($analysis['genre'] ?? '');
        $mappings = [
            'Podcast'    => ['podcast','spoken','audiobook','speech','talk'],
            'Musique'    => ['pop','rock','jazz','classical','electronic','hip-hop','rap','soul','funk'],
            'Interview'  => ['interview','conversation'],
            'Conférence' => ['conference','lecture','seminar'],
            'Méditation' => ['meditation','relaxation','yoga','spiritual'],
            'Son'        => ['sound','fx','ambient','nature']
        ];

        foreach ($mappings as $cat => $keywords) {
            foreach ($keywords as $kw) {
                if (strpos($genre, $kw) !== false) return $cat;
            }
        }

        $dur = $analysis['duration'] ?? 0;
        if ($dur > 600 && $dur < 3600) return 'Podcast';
        if ($dur > 3600) return 'Conférence';

        return 'Musique';
    }

    private function formatBytes($bytes)
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B','KB','MB','GB','TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function checkConfig()
    {
        echo "<pre>";
        echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
        echo "post_max_size: "       . ini_get('post_max_size')       . "\n";
        echo "max_execution_time: "  . ini_get('max_execution_time')  . "\n";
        echo "memory_limit: "        . ini_get('memory_limit')        . "\n";
        echo "Chunk size: "          . round($this->audio_config['chunk_size'] / 1024 / 1024, 2) . " MB\n";
        echo "Max file size: 10 GB\n";
        echo "FFmpeg: " . ($this->ffmpeg_path ?: 'Non trouvé') . "\n";
        echo "</pre>";
        exit;
    }

    public function updateAllSlugs()
    {
        if (!$this->session->userdata('is_admin')) { show_404(); return; }

        $audios  = $this->db->query("SELECT id_media, titre FROM galerie_medias WHERE type = 'audio' AND est_actif = 1")->result_array();
        $updated = 0;

        foreach ($audios as $audio) {
            $slug = $this->generateUniqueSlug($audio['titre'], $audio['id_media']);
            $this->db->where('id_media', $audio['id_media'])->update('galerie_medias', ['slug' => $slug]);
            $updated++;
            echo "ID: {$audio['id_media']} - Slug: {$slug}<br>";
        }

        echo "<br>Total: {$updated} slugs mis à jour.";
    }
}
