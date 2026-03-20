<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Audio Controller - Détection automatique durée/bitrate
 * Fonctionne avec OU sans FFmpeg (fallback intelligent)
 * cPanel compatible avec chemins étendus
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
        
        $this->_csrf_off();
        $this->initializePaths();
        $this->initializeConfig();
        $this->detectFFmpegTools();
        $this->checkGDAvailability();
        $this->ensureDirectories();
        
        $this->load->model('media/Model_media', 'Model');
    }

    // ==================== CSRF & HELPERS ====================

    private function _csrf_off()
    {
        if ($this->input->is_ajax_request() || $this->input->server('REQUEST_METHOD') === 'POST') {
            $this->config->set_item('csrf_protection', FALSE);
        }
    }

    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $value = (float)preg_replace('/[^0-9.]/', '', $size);
        $units = ['B', 'K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'];
        $unit = strtoupper($unit);
        $pow = array_search($unit, $units) ?: 0;
        return $value * pow(1024, $pow);
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

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $mins = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        return $hours > 0 
            ? sprintf('%d:%02d:%02d', $hours, $mins, $secs)
            : sprintf('%d:%02d', $mins, $secs);
    }

    // ==================== INITIALISATION ====================

    private function initializePaths()
    {
        $base = FCPATH;
        $this->paths = [
            'temp'         => $base . 'uploads/temp/audio/',
            'sessions'     => $base . 'uploads/temp/sessions/',
            'originals'    => $base . 'attachments/Audio/Originals/',
            'converted'    => $base . 'attachments/Audio/Converted/',
            'thumbnails'   => $base . 'attachments/Audio/Thumbnails/',
            'waveforms'    => $base . 'attachments/Audio/Waveforms/',
            'logs'         => $base . 'attachments/Audio/Logs/',
        ];
    }

    private function initializeConfig()
    {
        // 1.5MB chunks pour serveur 2M limit
        $chunk_size = 1.5 * 1024 * 1024;
        
        $this->audio_config = [
            'chunk_size'        => $chunk_size,
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

    // ==================== DÉTECTION FFMPEG AMÉLIORÉE ====================

    private function detectFFmpegTools()
    {
        $ffmpeg_paths = [
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/opt/ffmpeg/bin/ffmpeg',
            '/opt/cpanel/ea-php81/root/usr/bin/ffmpeg',
            'ffmpeg'
        ];
        
        $ffprobe_paths = [
            '/usr/bin/ffprobe',
            '/usr/local/bin/ffprobe',
            '/opt/ffmpeg/bin/ffprobe',
            '/opt/cpanel/ea-php81/root/usr/bin/ffprobe',
            'ffprobe'
        ];

        $this->ffmpeg_path = $this->findBestExecutable($ffmpeg_paths);
        $this->ffprobe_path = $this->findBestExecutable($ffprobe_paths);

        log_message('info', 'FFmpeg: ' . ($this->ffmpeg_path ?: 'NON TROUVÉ'));
        log_message('info', 'FFprobe: ' . ($this->ffprobe_path ?: 'NON TROUVÉ'));
    }

    private function findBestExecutable($candidates)
    {
        if (!function_exists('exec')) {
            return false;
        }

        foreach ($candidates as $cmd) {
            if (empty($cmd)) continue;
            
            // Test avec which pour commandes sans chemin
            if ($cmd === 'ffmpeg' || $cmd === 'ffprobe') {
                $which_output = [];
                $which_return = 0;
                exec('which ' . escapeshellarg($cmd) . ' 2>/dev/null', $which_output, $which_return);
                
                if ($which_return === 0 && !empty($which_output[0])) {
                    $full_path = $which_output[0];
                    if ($this->testExecutable($full_path)) {
                        return $full_path;
                    }
                }
            } else {
                // Chemin absolu
                if ($this->testExecutable($cmd)) {
                    return $cmd;
                }
            }
        }
        return false;
    }

    private function testExecutable($path)
    {
        if (!file_exists($path)) return false;
        
        $output = [];
        $return = 0;
        exec(escapeshellarg($path) . ' -version 2>/dev/null | head -1', $output, $return);
        
        return ($return === 0 && !empty($output[0]));
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

    // ==================== API UPLOAD ====================

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
        
        if ($file_size > $this->audio_config['max_file_size']) {
            echo json_encode(['success' => false, 'message' => 'Fichier trop grand (max 500MB)']);
            return;
        }
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->audio_config['allowed_extensions'])) {
            echo json_encode(['success' => false, 'message' => 'Format non supporté: ' . $ext]);
            return;
        }

        $upload_id = 'audio_' . uniqid() . '_' . bin2hex(random_bytes(4));
        $temp_dir = $this->paths['temp'] . $upload_id . '/';
        
        if (!@mkdir($temp_dir, 0777, true)) {
            echo json_encode(['success' => false, 'message' => 'Erreur création dossier']);
            return;
        }

        $chunk_size = $this->audio_config['chunk_size'];
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
        
        $upload_id = $this->input->post('upload_id');
        $chunk_index = (int)$this->input->post('chunk_index');

        if (empty($upload_id)) {
            echo json_encode(['success' => false, 'message' => 'Upload ID manquant']);
            return;
        }

        $temp_dir = $this->paths['temp'] . $upload_id . '/';
        $metadata_file = $temp_dir . 'metadata.json';
        
        if (!file_exists($metadata_file)) {
            echo json_encode(['success' => false, 'message' => 'Session non trouvée']);
            return;
        }

        $metadata = json_decode(file_get_contents($metadata_file), true);

        if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
            $error = isset($_FILES['chunk']) ? $_FILES['chunk']['error'] : 'aucun fichier';
            echo json_encode(['success' => false, 'message' => 'Erreur upload: ' . $error]);
            return;
        }

        $chunk_size = $_FILES['chunk']['size'];
        $max_allowed = $metadata['chunk_size'] + 1024;
        
        if ($chunk_size > $max_allowed) {
            echo json_encode([
                'success' => false, 
                'message' => 'Chunk trop grand: ' . $this->formatBytes($chunk_size)
            ]);
            return;
        }

        $chunk_path = $temp_dir . 'chunk_' . $chunk_index;
        
        if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path)) {
            echo json_encode(['success' => false, 'message' => 'Erreur sauvegarde chunk']);
            return;
        }

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

    public function completeUpload()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        // Timeout illimité pour assemblage
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }
        
        $upload_id = $this->input->post('upload_id');
        
        if (empty($upload_id)) {
            echo json_encode(['success' => false, 'message' => 'Upload ID manquant']);
            return;
        }

        $temp_dir = $this->paths['temp'] . $upload_id . '/';
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
                'missing' => array_slice($missing, 0, 10)
            ]);
            return;
        }

        // Assembler fichier final (STREAMING pour économie mémoire)
        $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($metadata['file_name'], PATHINFO_FILENAME));
        $original_name = date('YmdHis') . '_' . $safe_name . '_audio.' . pathinfo($metadata['file_name'], PATHINFO_EXTENSION);
        $original_path = $this->paths['originals'] . $original_name;
        
        $out = fopen($original_path, 'wb');
        if (!$out) {
            echo json_encode(['success' => false, 'message' => 'Impossible créer fichier']);
            return;
        }

        // Assemblage streaming (pas de file_get_contents complet)
        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            $chunk_file = $temp_dir . 'chunk_' . $i;
            $in = fopen($chunk_file, 'rb');
            if ($in) {
                while (!feof($in)) {
                    fwrite($out, fread($in, 262144)); // 256KB buffer
                }
                fclose($in);
                unlink($chunk_file);
            }
        }
        fclose($out);

        // Nettoyer
        @unlink($metadata_file);
        @rmdir($temp_dir);

        // ====== ANALYSE AUDIO AVEC FALLBACK ======
        $analysis = $this->analyzeAudioSmart($original_path);
        
        // Traitements optionnels
        $thumbnails = $this->ffmpeg_path ? $this->generateThumbnails($original_path, $original_name) : [];
        $waveform = $this->ffmpeg_path ? $this->generateWaveform($original_path, $original_name) : null;
        $conversions = $this->ffmpeg_path ? $this->convertToMultipleBitrates($original_path, $original_name) : [];

        // Formater thumbnails
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
            'analysis_source' => $analysis['source'] ?? 'unknown',
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

    // ==================== ANALYSE AUDIO INTELLIGENTE ====================

    /**
     * Analyse audio avec fallback automatique
     * 1. Essayer FFmpeg/ffprobe
     * 2. Sinon, estimer à partir de la taille/extension
     */
    private function analyzeAudioSmart($file_path)
    {
        // Étape 1: Essayer FFmpeg si disponible
        if ($this->ffprobe_path && file_exists($file_path)) {
            $result = $this->analyzeWithFFprobe($file_path);
            if ($result && $result['duration'] > 0) {
                return $result;
            }
        }

        // Étape 2: Fallback estimation
        log_message('info', 'Fallback estimation pour: ' . basename($file_path));
        return $this->estimateAudioProperties($file_path);
    }

    private function analyzeWithFFprobe($file_path)
    {
        $cmd = sprintf(
            '%s -v quiet -print_format json -show_format -show_streams %s 2>&1',
            escapeshellarg($this->ffprobe_path),
            escapeshellarg($file_path)
        );
        
        $output = [];
        $return = 0;
        exec($cmd, $output, $return);
        
        if ($return !== 0 || empty($output)) {
            return null;
        }

        $data = json_decode(implode("\n", $output), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        $format = $data['format'] ?? [];
        $audio = null;
        
        foreach ($data['streams'] ?? [] as $stream) {
            if (($stream['codec_type'] ?? '') === 'audio') {
                $audio = $stream;
                break;
            }
        }

        $tags = $format['tags'] ?? [];
        $duration = (float)($format['duration'] ?? 0);
        
        // Fallback durée dans stream si pas dans format
        if ($duration == 0 && $audio) {
            $duration = (float)($audio['duration'] ?? 0);
        }

        return [
            'duration'           => $duration,
            'duration_formatted' => $this->formatDuration($duration),
            'size'               => (int)($format['size'] ?? filesize($file_path)),
            'bitrate'            => (int)($format['bit_rate'] ?? 0),
            'sample_rate'        => (int)($audio['sample_rate'] ?? 44100),
            'channels'           => (int)($audio['channels'] ?? 2),
            'codec'              => $audio['codec_name'] ?? 'unknown',
            'title'              => $tags['title'] ?? $tags['TITLE'] ?? null,
            'artist'             => $tags['artist'] ?? $tags['ARTIST'] ?? null,
            'album'              => $tags['album'] ?? $tags['ALBUM'] ?? null,
            'year'               => $tags['date'] ?? $tags['DATE'] ?? null,
            'genre'              => $tags['genre'] ?? $tags['GENRE'] ?? null,
            'source'             => 'ffprobe'
        ];
    }

    /**
     * Estimation des propriétés sans FFmpeg (FALLBACK)
     */
    private function estimateAudioProperties($file_path)
    {
        $size = filesize($file_path);
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Bitrate moyen par format (kbps)
        $bitrate_map = [
            'mp3'  => 192,
            'aac'  => 128,
            'ogg'  => 160,
            'm4a'  => 128,
            'flac' => 800,
            'wav'  => 1411,
            'aiff' => 1411,
            'wma'  => 192,
            'opus' => 128,
            'weba' => 128,
        ];
        
        $bitrate_kbps = $bitrate_map[$ext] ?? 192;
        $bitrate = $bitrate_kbps * 1000;
        
        // Calculer durée: taille (bits) / bitrate (bits/s)
        $duration = ($size * 8) / $bitrate;
        
        // Correction pour formats lossless (estimation moins précise)
        if (in_array($ext, ['flac', 'wav', 'aiff'])) {
            $duration = $duration * 0.9;
        }

        return [
            'duration'           => round($duration, 2),
            'duration_formatted' => $this->formatDuration($duration),
            'size'               => $size,
            'bitrate'            => $bitrate,
            'sample_rate'        => 44100,
            'channels'           => 2,
            'codec'              => $ext,
            'title'              => null,
            'artist'             => null,
            'album'              => null,
            'year'               => null,
            'genre'              => null,
            'source'             => 'estimated',
            'note'               => 'Valeurs estimées (FFmpeg non disponible)'
        ];
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
        
        $id = $this->input->post('id');
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
        
        $id = $this->input->post('id');
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
        
        $filename = basename($audio['fichier'] ?? '');
        
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
            }
        }
        
        if (!file_exists($file_path)) {
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
        }
        
        fclose($fp);
    }

    // ==================== MINIATURES ====================

    public function uploadThumbnail()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        if (empty($_FILES['thumbnail_file']) || $_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu']);
            return;
        }

        $file = $_FILES['thumbnail_file'];
        $upload_max = $this->parseSize(ini_get('upload_max_filesize'));
        
        if ($file['size'] > $upload_max) {
            echo json_encode(['success' => false, 'message' => 'Image trop grande']);
            return;
        }

        $ref_folder = FCPATH . 'attachments/Audio/Thumbnails/Custom/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $valid_ext = ['gif', 'jpg', 'png', 'jpeg', 'webp', 'svg'];

        if (!in_array($file_extension, $valid_ext)) {
            echo json_encode(['success' => false, 'message' => 'Format non supporté']);
            return;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        $final_filename = $fichier . "." . $file_extension;
        $destination = $ref_folder . $final_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            echo json_encode(['success' => false, 'message' => 'Erreur déplacement']);
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

    private function generateThumbnails($audio_path, $filename)
    {
        $result = ['cover' => null, 'generated' => null];
        
        if (!$this->ffmpeg_path) {
            return $result;
        }

        $base_name = pathinfo($filename, PATHINFO_FILENAME);
        
        // Cover art
        $cover_name = $base_name . '_cover.jpg';
        $cover_path = $this->paths['thumbnails'] . $cover_name;
        
        $cmd = sprintf(
            '%s -i %s -an -vcodec copy -f image2 -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path),
            escapeshellarg($audio_path),
            escapeshellarg($cover_path)
        );
        exec($cmd);

        if (file_exists($cover_path) && filesize($cover_path) > 1000) {
            $result['cover'] = 'attachments/Audio/Thumbnails/' . $cover_name;
        } else {
            @unlink($cover_path);
        }

        // Waveform si pas de cover
        if (empty($result['cover'])) {
            $generated_name = $base_name . '_waveform.jpg';
            $generated_path = $this->paths['thumbnails'] . $generated_name;
            
            $cmd = sprintf(
                '%s -i %s -filter_complex "aformat=channel_layouts=mono,showwavespic=s=800x800:colors=#FF0000|#FF6B6B" -frames:v 1 -y %s 2>&1',
                escapeshellarg($this->ffmpeg_path),
                escapeshellarg($audio_path),
                escapeshellarg($generated_path)
            );
            exec($cmd);

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

    // ==================== HELPERS ====================

    private function getAudioCapabilities()
    {
        return [
            'hardware' => [
                'ffmpeg' => (bool)$this->ffmpeg_path,
                'ffprobe' => (bool)$this->ffprobe_path,
                'gd' => $this->gd_available
            ],
            'features' => [
                'multi_bitrate' => (bool)$this->ffmpeg_path,
                'streaming' => true,
                'auto_analysis' => true // Fallback intelligent actif
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

        $duration = $analysis['duration'] ?? 0;
        if ($duration > 600 && $duration < 3600) {
            return 'Podcast';
        }
        if ($duration > 3600) {
            return 'Conférence';
        }

        return 'Musique';
    }

    public function checkConfig()
    {
        echo "<pre>";
        echo "=== CONFIGURATION AUDIO ===\\n";
        echo "FFmpeg: " . ($this->ffmpeg_path ?: 'NON TROUVÉ') . "\\n";
        echo "FFprobe: " . ($this->ffprobe_path ?: 'NON TROUVÉ') . "\\n";
        echo "GD: " . ($this->gd_available ? 'OK' : 'NON') . "\\n\\n";
        
        echo "=== LIMITES PHP ===\\n";
        echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\\n";
        echo "post_max_size: " . ini_get('post_max_size') . "\\n";
        echo "max_execution_time: " . ini_get('max_execution_time') . "s\\n";
        echo "memory_limit: " . ini_get('memory_limit') . "\\n\\n";
        
        echo "=== CONFIG CHUNKS ===\\n";
        echo "Chunk size: " . $this->formatBytes($this->audio_config['chunk_size']) . "\\n";
        echo "Max file: " . $this->formatBytes($this->audio_config['max_file_size']) . "\\n";
        echo "</pre>";
        exit;
    }
}