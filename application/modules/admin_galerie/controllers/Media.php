<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Media extends MX_Controller {

    private $media_config;
    private $ffmpeg_path;
    private $ffprobe_path;
    private $gd_available = false;

    function __construct()
    {
        parent::__construct();
        if (!$this->input->is_cli_request()) {
            is_admin();
            $this->config->set_item('csrf_protection', FALSE);
        }
        $this->initializePaths();
        $this->ensureDirectories();
        $this->initializeConfig();
        $this->detectFFmpegTools();
        $this->checkGDAvailability();
        $this->load->model('admin_galerie/Media_models', 'MediaModel');
    }

    private $paths = [];

    private function initializePaths()
    {
        $base = FCPATH;
        $this->paths = [
            'audio_temp'       => $base . 'uploads/temp/audio/',
            'audio_originals'  => $base . 'attachments/Audio/Originals/',
            'audio_converted'  => $base . 'attachments/Audio/Converted/',
            'audio_thumbnails' => $base . 'attachments/Audio/Thumbnails/',
            'audio_covers'     => $base . 'attachments/Audio/Covers/',
            'audio_waveforms'  => $base . 'attachments/Audio/Waveforms/',
            'audio_logs'       => $base . 'attachments/Audio/Logs/',
            'video_temp'       => $base . 'uploads/temp/video/',
            'video_originals'  => $base . 'attachments/Video/Originals/',
            'video_encoded'    => $base . 'attachments/Video/Encoded/',
            'video_thumbnails' => $base . 'attachments/Video/Thumbnails/',
            'video_posters'    => $base . 'attachments/Video/Posters/',
            'video_logs'       => $base . 'attachments/Video/Logs/',
            'image_temp'       => $base . 'uploads/temp/image/',
            'image_originals'  => $base . 'attachments/Image/Originals/',
            'image_thumbnails' => $base . 'attachments/Image/Thumbnails/',
            'document_temp'    => $base . 'uploads/temp/document/',
            'document_files'   => $base . 'attachments/Document/Files/',
            'document_thumbnails' => $base . 'attachments/Document/Thumbnails/',
        ];
    }

    private function initializeConfig()
    {
        $chunk_size = $this->detectOptimalChunkSize();

        $this->media_config = [
            'chunk_size'    => $chunk_size,
            'max_file_size' => 4 * 1024 * 1024 * 1024,
            'audio_extensions'   => ['mp3','wav','flac','aac','ogg','m4a','wma','aiff','opus','weba'],
            'video_extensions'   => ['mp4','mov','avi','mkv','webm','m4v','3gp','flv','wmv'],
            'image_extensions'   => ['jpg','jpeg','png','gif','webp','svg'],
            'document_extensions'=> ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv'],
            'qualities' => [
                'low'    => ['bitrate' => '64k',  'suffix' => '_64k'],
                'medium' => ['bitrate' => '128k', 'suffix' => '_128k'],
                'high'   => ['bitrate' => '192k', 'suffix' => '_192k'],
                'max'    => ['bitrate' => '320k', 'suffix' => '_320k']
            ],
            'server_limits' => [
                'nginx_client_max_body_size' => $this->detectNginxClientMaxBodySize(),
                'upload_max_filesize'        => $this->returnBytes(ini_get('upload_max_filesize')),
                'post_max_size'              => $this->returnBytes(ini_get('post_max_size')),
            ]
        ];

        @set_time_limit(0);
        @ini_set('memory_limit', '256M');
    }

    private function detectOptimalChunkSize()
    {
        $nginx  = $this->detectNginxClientMaxBodySize();
        $php_up = $this->returnBytes(ini_get('upload_max_filesize'));
        $php_po = $this->returnBytes(ini_get('post_max_size'));

        $limits = array_filter([$nginx, $php_up, $php_po]);
        if (empty($limits)) return 64 * 1024;

        $min   = min($limits);
        $chunk = (int)floor($min / 2);
        $chunk = max(64 * 1024, $chunk);
        $chunk = min(256 * 1024, $chunk);

        return $chunk;
    }

    private function detectNginxClientMaxBodySize()
    {
        $cached = $this->detectionCacheGet('nginx_max_body');
        if ($cached !== null) return (int)$cached;

        $result = 20 * 1024 * 1024;
        $output = [];
        exec('nginx -T 2>/dev/null | grep -i client_max_body_size', $output, $code);
        if ($code === 0) {
            foreach ($output as $line) {
                $line = trim($line);
                if (strpos($line, '#') !== false) $line = trim(substr($line, 0, strpos($line, '#')));
                if (preg_match('/(\d+)\s*([kKmMgG])?/', $line, $m)) {
                    $val = (int)$m[1];
                    if (!empty($m[2])) {
                        $u = strtolower($m[2]);
                        if ($u === 'k') $val *= 1024;
                        elseif ($u === 'm') $val *= 1024 * 1024;
                        elseif ($u === 'g') $val *= 1024 * 1024 * 1024;
                    }
                    $result = $val;
                    break;
                }
            }
        }
        $env = getenv('NGINX_CLIENT_MAX_BODY_SIZE');
        if ($env !== false && is_numeric($env)) {
            $result = (int)$env;
        }

        $this->detectionCacheSet('nginx_max_body', $result);
        return $result;
    }

    private function returnBytes($val)
    {
        if (empty($val)) return PHP_INT_MAX;
        $val = trim($val);
        $last = strtolower(substr($val, -1));
        $num = (int)$val;
        switch ($last) {
            case 'g': $num *= 1024;
            case 'm': $num *= 1024;
            case 'k': $num *= 1024;
        }
        return $num;
    }

    private function detectionCacheGet($key)
    {
        $file = FCPATH . 'uploads/temp/_detect_' . md5($key) . '.json';
        if (!is_file($file)) return null;
        $data = @json_decode(@file_get_contents($file), true);
        if (!is_array($data) || empty($data['ts']) || (time() - (int)$data['ts']) > 3600) {
            @unlink($file);
            return null;
        }
        return $data['value'] ?? null;
    }

    private function detectionCacheSet($key, $value)
    {
        $file = FCPATH . 'uploads/temp/_detect_' . md5($key) . '.json';
        @file_put_contents($file, json_encode(['ts' => time(), 'value' => $value]), LOCK_EX);
    }

    private function detectFFmpegTools()
    {
        $cached = $this->detectionCacheGet('ffmpeg_tools');
        if ($cached !== null) {
            $this->ffmpeg_path  = $cached['ffmpeg']  ?? false;
            $this->ffprobe_path = $cached['ffprobe'] ?? false;
            return;
        }

        $this->ffmpeg_path  = $this->findExecutable(['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg']);
        $this->ffprobe_path = $this->findExecutable(['ffprobe', '/usr/bin/ffprobe', '/usr/local/bin/ffprobe']);

        $this->detectionCacheSet('ffmpeg_tools', [
            'ffmpeg'  => $this->ffmpeg_path,
            'ffprobe' => $this->ffprobe_path,
        ]);
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
                if (!mkdir($path, 0777, true) && !is_dir($path)) {
                    log_message('error', "Media: impossible de créer le répertoire $path");
                }
            }
        }
    }

    private function jsonResponse($data)
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
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
        if (empty($slug)) $slug = 'media';

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

    public function index($type = null)
    {
        if ($type && !in_array($type, ['audio', 'video', 'image', 'document', 'link'])) {
            $type = null;
        }

        // Traitement asynchrone délégué au worker dédié (Docker/worker.sh) :
        // aucune exécution FFmpeg synchrone ici pour ne jamais bloquer le chargement.

        $page  = max(1, (int)$this->input->get('page') ?: 1);
        $limit = min(50, max(12, (int)$this->input->get('limit') ?: 24));
        $offset = ($page - 1) * $limit;

        $search_file = trim((string)$this->input->get('search_file'));
        $where = $type ? ['type' => $type] : [];

        if ($search_file !== '') {
            $total = $this->MediaModel->countSearchByFile($search_file, $type);
            $medias = $this->MediaModel->searchByFile($search_file, $type, $limit, $offset);
        } else {
            $total = $this->MediaModel->count($where);
            $medias = $type
                ? $this->MediaModel->getByType($type, $limit, $offset)
                : $this->MediaModel->read([], 'id_media', 'DESC', $limit, $offset);
        }

        $data = [
            'medias'         => $medias,
            'current_type'   => $type,
            'search_file'    => $search_file,
            'categories'     => $this->MediaModel->getCategories($type),
            'statistics'     => $this->MediaModel->getStatistics(),
            'ffmpeg_ready'   => (bool)$this->ffmpeg_path,
            'gd_available'   => $this->gd_available,
            'pagination'     => [
                'page'  => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => max(1, (int)ceil($total / $limit)),
            ],
        ];
        $this->load->view('Media_View', $data);
    }

    public function searchAjax()
    {
        $type = $this->input->get('type');
        if ($type && !in_array($type, ['audio', 'video', 'image', 'document', 'link'])) {
            $type = null;
        }

        $page  = max(1, (int)$this->input->get('page') ?: 1);
        $limit = min(50, max(12, (int)$this->input->get('limit') ?: 24));
        $offset = ($page - 1) * $limit;
        $search_file = trim((string)$this->input->get('search_file'));

        if ($search_file !== '') {
            $total = $this->MediaModel->countSearchByFile($search_file, $type);
            $medias = $this->MediaModel->searchByFile($search_file, $type, $limit, $offset);
        } else {
            $where = $type ? ['type' => $type] : [];
            $total = $this->MediaModel->count($where);
            $medias = $type
                ? $this->MediaModel->getByType($type, $limit, $offset)
                : $this->MediaModel->read([], 'id_media', 'DESC', $limit, $offset);
        }

        $html = $this->load->view('_media_results', [
            'medias'       => $medias,
            'current_type' => $type,
            'search_file'  => $search_file,
            'pagination'   => [
                'page'  => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => max(1, (int)ceil($total / $limit)),
            ],
        ], true);

        $this->jsonResponse(['success' => true, 'html' => $html]);
    }

    private function processJobsInline($max_time_seconds = 5)
    {
        $start = microtime(true);
        $processed = 0;
        while (($job = $this->MediaModel->getNextJob()) !== null) {
            if ((microtime(true) - $start) > $max_time_seconds) break;
            $claimed = $this->MediaModel->claimJob($job['id']);
            if (!$claimed) continue;
            try {
                $result = $this->runProcessingJob($claimed);
                $this->MediaModel->finishJob($claimed['id'], 'done', $result);
            } catch (\Exception $e) {
                $this->MediaModel->finishJob($claimed['id'], 'failed', null, $e->getMessage());
                log_message('error', 'Media job inline ' . $claimed['id'] . ' failed: ' . $e->getMessage());
            }
            $processed++;
            if ($processed >= 10) break;
        }
        return $processed;
    }

    // ==================== CRUD ====================

    private function notifyIFTTT($data)
    {
        $file_path = $data['fichier'] ?? null;
        $this->load->helper('ifttt');
        notify_ifttt($data, $file_path);
    }

    public function Create()
    {
        $this->db->trans_start();
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('type', 'Type', 'required|in_list[audio,video,image,document,link]');

        $type = $this->input->post('type');
        if ($type === 'link') {
            $this->form_validation->set_rules('lien', 'Lien', 'required|valid_url');
        } elseif (in_array($type, ['audio', 'video', 'image', 'document'])) {
            $this->form_validation->set_rules('uploaded_file_path', 'Fichier', 'required');
        }
        $this->form_validation->set_rules('description', 'Description', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('admin/media'));
            return;
        }

        $data = [
            'titre'          => $this->input->post('titre'),
            'type'           => $type,
            'description'    => $this->input->post('description'),
            'categorie'      => $this->input->post('categorie'),
            'date_media'     => $this->input->post('date_media') ?: date('Y-m-d'),
            'credits'        => $this->input->post('credits'),
            'miniature'      => $this->input->post('thumbnail'),
            'est_actif'      => $this->input->post('est_actif') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
            'is_for_whatsapp'=> $this->input->post('is_for_whatsapp') ? 1 : 0,
        ];

        $data['slug'] = $this->generateUniqueSlug($data['titre']);

        if ($type === 'link') {
            $lien = $this->input->post('lien');
            // B3: rejet si l'URL est invalide (sécurité renforcée)
            if (empty($lien) || !filter_var($lien, FILTER_VALIDATE_URL)) {
                $this->session->set_flashdata('error', 'L\'URL fournie est invalide');
                redirect(base_url('admin/media'));
                return;
            }
            $data['lien']     = $lien;
            $data['miniature'] = $this->input->post('miniature_externe') ?: $data['miniature'];
        } elseif (in_array($type, ['audio', 'video', 'image', 'document'])) {
            $data['fichier']  = $this->input->post('uploaded_file_path');
            // Recoupe serveur des métadonnées : on ignore auto_detected_data (client)
            $server_meta = $this->recomputeServerMetadata($type, $data['fichier']);
            $data['taille']    = $server_meta['taille'];
            $data['mime_type'] = $server_meta['mime_type'];
            if ($type === 'audio' || $type === 'video') {
                $data['duree']  = $server_meta['duree'];
                $data['metadata_id3'] = json_encode($server_meta['metadata'], JSON_UNESCAPED_UNICODE);
                if ($type === 'audio') {
                    $data['bitrate']     = $server_meta['bitrate'];
                    $data['sample_rate'] = $server_meta['sample_rate'];
                    $data['channels']    = $server_meta['channels'];
                }
            }

            $dup = $this->MediaModel->checkDuplicate($data['fichier'], $data['titre'], $type);
            if ($dup) {
                $this->session->set_flashdata('error', $dup['kind'] === 'file'
                    ? 'Ce fichier existe déjà : ' . $dup['media']['fichier']
                    : 'Un média porte déjà ce titre : ' . $dup['media']['titre']);
                redirect(base_url('admin/media'));
                return;
            }
        }

        $rsp = $this->MediaModel->create($data);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) $rsp = false;

        if ($rsp) {
            $this->notifyIFTTT($data);
        }

        $this->session->set_flashdata($rsp ? 'success' : 'error',
            $rsp ? 'Média créé avec succès' : 'Erreur lors de la création');
        redirect(base_url('admin/media/index/' . $type));
    }

    private function recomputeServerMetadata($type, $relative_path)
    {
        $abs = FCPATH . $relative_path;
        $meta = [
            'taille'    => 0,
            'mime_type' => '',
            'duree'     => 0,
            'bitrate'   => 0,
            'sample_rate' => 0,
            'channels'  => 0,
            'metadata'  => [],
        ];

        if (!file_exists($abs)) {
            log_message('error', 'Media recomputeServerMetadata: fichier introuvable ' . $abs);
            return $meta;
        }

        $meta['taille']    = (int)filesize($abs);
        $meta['mime_type'] = (string)mime_content_type($abs);

        try {
            if ($type === 'audio' && $this->ffprobe_path) {
                $analysis = $this->analyzeAudio($abs);
                $meta['duree']       = (int)round((float)($analysis['duration'] ?? 0));
                $meta['bitrate']     = (int)($analysis['bitrate'] ?? 0);
                $meta['sample_rate'] = (int)($analysis['sample_rate'] ?? 0);
                $meta['channels']    = (int)($analysis['channels'] ?? 0);
                $meta['metadata']    = $analysis;
            } elseif ($type === 'video' && $this->ffprobe_path) {
                $analysis = $this->analyzeVideo($abs);
                $meta['duree']    = (int)round((float)($analysis['duration'] ?? 0));
                $meta['metadata'] = $analysis;
            }
        } catch (\Exception $e) {
            log_message('error', 'Media recomputeServerMetadata: ' . $e->getMessage());
        }

        return $meta;
    }

    public function Update()
    {
        $this->db->trans_start();
        $id = $this->input->post('id');
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('description', 'Description', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('admin/media'));
            return;
        }

        $current = $this->MediaModel->readOne(['id_media' => $id]);
        if (!$current) {
            $this->session->set_flashdata('error', 'Média introuvable');
            redirect(base_url('admin/media'));
            return;
        }

        $type = $current['type'];

        $data = [
            'titre'          => $this->input->post('titre'),
            'description'    => $this->input->post('description'),
            'categorie'      => $this->input->post('categorie'),
            'date_media'     => $this->input->post('date_media'),
            'credits'        => $this->input->post('credits'),
            'est_actif'      => $this->input->post('est_actif') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
            'is_for_whatsapp'=> $this->input->post('is_for_whatsapp') ? 1 : 0,
        ];

        if ($data['titre'] != ($current['titre'] ?? '')) {
            $data['slug'] = $this->generateUniqueSlug($data['titre'], $id);
        }

        if ($type === 'link') {
            $data['lien'] = $this->input->post('lien');
            $data['miniature'] = $this->input->post('miniature_externe');
        } else {
            $new_thumbnail = $this->input->post('thumbnail');
            if (!empty($new_thumbnail) && $new_thumbnail !== ($current['miniature'] ?? '')) {
                if (!empty($current['miniature']) && strpos($current['miniature'], 'Custom/') !== false) {
                    @unlink(FCPATH . $current['miniature']);
                }
                $data['miniature'] = $new_thumbnail;
            }
        }

        $rsp = $this->MediaModel->update(['id_media' => $id], $data);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) $rsp = false;
        $this->session->set_flashdata($rsp ? 'success' : 'error',
            $rsp ? 'Média mis à jour' : 'Erreur mise à jour');
        redirect(base_url('admin/media/index/' . $type));
    }
 
    public function Delete()
    {
        $this->db->trans_start();
        $id    = $this->input->post('id');
        $media = $this->MediaModel->readOne(['id_media' => $id]);

        if ($media) {
            if (!empty($media['fichier'])) {
                $path = FCPATH . $media['fichier'];
                if (file_exists($path)) unlink($path);

                $this->deleteDerivedFiles($media);
            }
            if (!empty($media['miniature']) && strpos($media['miniature'], 'Custom/') !== false) {
                $path = FCPATH . $media['miniature'];
                if (file_exists($path)) unlink($path);
            }

            $rsp = $this->MediaModel->delete(['id_media' => $id]);
            $this->db->trans_complete();
            if ($this->db->trans_status() === false) $rsp = false;
            $this->session->set_flashdata($rsp ? 'success' : 'error',
                $rsp ? 'Média supprimé' : 'Erreur suppression');
        } else {
            $this->session->set_flashdata('error', 'Média introuvable');
        }

        $type = $media['type'] ?? '';
        redirect(base_url('admin/media/index/' . $type));
    }

    public function ChangeStatus()
    {
        $id     = $this->input->post('id');
        $status = $this->input->post('est_actif');
        $rsp = $this->MediaModel->update(['id_media' => $id], ['est_actif' => $status]);
        $this->jsonResponse(['success' => (bool)$rsp]);
    }

    public function toggleField()
    {
        $id    = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');

        $allowed = ['is_for_website', 'est_actif', 'is_for_whatsapp'];
        if (!in_array($field, $allowed)) {
            $this->jsonResponse(['success' => false, 'message' => 'Champ non autorisé']);
        }

        $rsp = $this->MediaModel->update(['id_media' => $id], [$field => $value]);
        $this->jsonResponse(['success' => (bool)$rsp]);
    }

    // ==================== API UPLOAD CHUNKED ====================

    public function initUpload()
    {
        @set_time_limit(60);

        if (!$this->rateLimitCheck('init_upload', 30, 3600)) {
            $this->jsonResponse(['success' => false, 'message' => 'Trop d\'uploads initiés récemment. Réessayez plus tard.']);
        }

        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');
        $type      = $this->input->post('type');

        if (empty($file_name) || $file_size <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Paramètres invalides']);
        }

        if ($file_size > $this->media_config['max_file_size']) {
            $this->jsonResponse(['success' => false, 'message' => 'Fichier trop grand (max 4GB)']);
        }

        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $config = $this->getTypeConfig($type);
        if (!in_array($ext, $config['extensions'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Format non supporté: ' . $ext]);
        }

        $dup = $this->MediaModel->checkDuplicate($file_name, null, $type);
        if ($dup && $dup['kind'] === 'file') {
            $this->jsonResponse(['success' => false, 'message' => 'Ce fichier existe déjà : ' . $dup['media']['fichier']]);
        }

        $upload_id = $type . '_' . uniqid() . '_' . bin2hex(random_bytes(4));
        $temp_dir  = $this->getTempDir($type) . $upload_id . '/';

        if (!mkdir($temp_dir, 0777, true) && !is_dir($temp_dir)) {
            $this->jsonResponse(['success' => false, 'message' => 'Impossible de créer le dossier temporaire']);
        }

        $chunk_size   = $this->media_config['chunk_size'];
        $total_chunks = (int)ceil($file_size / $chunk_size);
        $limits       = $this->media_config['server_limits'];

        $metadata = [
            'upload_id'       => $upload_id,
            'file_name'       => $file_name,
            'file_size'       => $file_size,
            'total_chunks'    => $total_chunks,
            'chunk_size'      => $chunk_size,
            'uploaded_chunks' => [],
            'chunk_checksums' => [],
            'type'            => $type,
            'created_at'      => time(),
            'status'          => 'uploading'
        ];

        if (file_put_contents($temp_dir . 'metadata.json', json_encode($metadata)) === false) {
            $this->jsonResponse(['success' => false, 'message' => 'Impossible d\'écrire les métadonnées']);
        }

        $this->jsonResponse([
            'success'      => true,
            'upload_id'    => $upload_id,
            'chunk_size'   => $chunk_size,
            'total_chunks' => $total_chunks,
            'ffmpeg_ready' => (bool)$this->ffmpeg_path,
            'server_limits' => [
                'nginx_client_max_body_size' => $limits['nginx_client_max_body_size'],
                'upload_max_filesize'        => $limits['upload_max_filesize'],
                'post_max_size'              => $limits['post_max_size'],
            ],
            'debug'        => [
                'chunk_size_mb' => round($chunk_size / 1024 / 1024, 2),
                'total_chunks'  => $total_chunks,
            ]
        ]);
    }

    public function uploadStatus()
    {
        $upload_id = $this->input->post('upload_id');
        if (empty($upload_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'upload_id manquant']);
        }

        $type = explode('_', $upload_id)[0];
        $meta_file = $this->getTempDir($type) . $upload_id . '/metadata.json';
        if (!file_exists($meta_file)) {
            $this->jsonResponse(['success' => false, 'message' => 'Upload introuvable ou expiré. Redémarrez l\'upload.']);
        }

        $meta = json_decode(file_get_contents($meta_file), true);
        if (!$meta) {
            $this->jsonResponse(['success' => false, 'message' => 'Métadonnées corrompues']);
        }

        $this->jsonResponse([
            'success'         => true,
            'upload_id'       => $meta['upload_id'],
            'file_name'       => $meta['file_name'],
            'file_size'       => $meta['file_size'],
            'chunk_size'      => $meta['chunk_size'],
            'total_chunks'    => $meta['total_chunks'],
            'uploaded_chunks' => $meta['uploaded_chunks'],
            'chunk_checksums' => $meta['chunk_checksums'] ?? [],
            'percent'         => round(count($meta['uploaded_chunks']) / max($meta['total_chunks'], 1) * 100, 1)
        ]);
    }

    private function computeChunkChecksum($file_path)
    {
        if (!file_exists($file_path)) return null;
        $ctx = hash_init('sha256');
        $fp = fopen($file_path, 'rb');
        if (!$fp) return null;
        while (!feof($fp)) {
            $buffer = fread($fp, 8192);
            if ($buffer === false) break;
            hash_update($ctx, $buffer);
        }
        fclose($fp);
        return hash_final($ctx);
    }

    public function uploadChunk()
    {
        @set_time_limit(120);

        if (!$this->rateLimitCheck('upload_chunk', 5000, 60)) {
            $this->jsonResponse(['success' => false, 'message' => 'Trop de chunks envoyés. Réessayez plus tard.']);
        }

        $upload_id   = $this->input->post('upload_id');
        $chunk_index = (int)$this->input->post('chunk_index');

        if (empty($upload_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'upload_id manquant']);
        }

        $type = explode('_', $upload_id)[0];
        $temp_dir      = $this->getTempDir($type) . $upload_id . '/';
        $metadata_file = $temp_dir . 'metadata.json';

        if (!file_exists($metadata_file)) {
            $this->jsonResponse(['success' => false, 'message' => 'Session upload non trouvée pour: ' . $upload_id]);
        }

        $metadata = json_decode(file_get_contents($metadata_file), true);
        if (!$metadata) {
            $this->jsonResponse(['success' => false, 'message' => 'Métadonnées corrompues']);
        }

        $new_total = (int)$this->input->post('total_chunks');
        if ($new_total > 0 && $new_total !== (int)$metadata['total_chunks']) {
            $metadata['total_chunks'] = $new_total;
            file_put_contents($metadata_file, json_encode($metadata));
        }

        if ($chunk_index < 0 || $chunk_index >= $metadata['total_chunks']) {
            $this->jsonResponse(['success' => false, 'message' => "Index chunk invalide: {$chunk_index} / {$metadata['total_chunks']}"]);
        }

        if (!isset($_FILES['chunk'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Champ "chunk" manquant dans la requête']);
        }

        $file_error = $_FILES['chunk']['error'];
        if ($file_error !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'message' => $this->getUploadErrorMessage($file_error)]);
        }

        $chunk_size_received = $_FILES['chunk']['size'];
        $max_allowed         = $metadata['chunk_size'] + (10 * 1024);

        if ($chunk_size_received > $max_allowed) {
            $this->jsonResponse(['success' => false, 'message' => "Chunk trop grand: {$chunk_size_received} bytes (max autorisé: {$max_allowed})"]);
        }

        if ($chunk_size_received === 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Chunk vide reçu (0 bytes)']);
        }

        $client_checksum = $this->input->post('checksum');
        $tmp_path   = $_FILES['chunk']['tmp_name'];
        $chunk_path = $temp_dir . 'chunk_' . $chunk_index;

        if (!file_exists($tmp_path)) {
            $this->jsonResponse(['success' => false, 'message' => 'Fichier temporaire PHP introuvable']);
        }

        if (!move_uploaded_file($tmp_path, $chunk_path)) {
            $this->jsonResponse(['success' => false, 'message' => "Impossible de sauvegarder le chunk {$chunk_index}"]);
        }

        $saved_size = filesize($chunk_path);
        if ($saved_size !== $chunk_size_received) {
            @unlink($chunk_path);
            $this->jsonResponse(['success' => false, 'message' => "Taille incohérente: reçu={$chunk_size_received}, sauvegardé={$saved_size}"]);
        }

        $checksum = $this->computeChunkChecksum($chunk_path);

        if (!empty($client_checksum) && $checksum !== $client_checksum) {
            @unlink($chunk_path);
            $this->jsonResponse(['success' => false, 'message' => "Checksum invalide pour chunk {$chunk_index}"]);
        }

        if (in_array($chunk_index, $metadata['uploaded_chunks'])) {
            if (isset($metadata['chunk_checksums'][$chunk_index]) && $metadata['chunk_checksums'][$chunk_index] === $checksum) {
                @unlink($chunk_path);
                $this->jsonResponse(['success' => true, 'message' => "Chunk {$chunk_index} déjà reçu (ignoré)", 'checksum' => $checksum, 'duplicate' => true]);
                return;
            }
        }

        $metadata['uploaded_chunks'][] = $chunk_index;
        $metadata['chunk_checksums'][$chunk_index] = $checksum;
        sort($metadata['uploaded_chunks']);
        file_put_contents($metadata_file, json_encode($metadata));

        $uploaded = count($metadata['uploaded_chunks']);
        $percent  = round(($uploaded / $metadata['total_chunks']) * 100, 1);

        $this->jsonResponse([
            'success'  => true,
            'message'  => "Chunk {$chunk_index} reçu ({$saved_size} bytes)",
            'checksum' => $checksum,
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

    public function completeUpload()
    {
        @set_time_limit(3600);

        if (!$this->rateLimitCheck('complete_upload', 30, 3600)) {
            $this->jsonResponse(['success' => false, 'message' => 'Trop d\'uploads finalisés récemment. Réessayez plus tard.']);
        }

        $upload_id = $this->input->post('upload_id');
        if (empty($upload_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'upload_id manquant']);
        }

        $type = explode('_', $upload_id)[0];
        $temp_dir      = $this->getTempDir($type) . $upload_id . '/';
        $metadata_file = $temp_dir . 'metadata.json';

        if (!file_exists($metadata_file)) {
            $this->jsonResponse(['success' => false, 'message' => 'Session non trouvée']);
        }

        // B1: vérification du json_decode
        $metadata = json_decode(file_get_contents($metadata_file), true);
        if (!$metadata || !isset($metadata['total_chunks'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Métadonnées corrompues ou incomplètes']);
        }

        $missing = [];
        $corrupted = [];
        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            $chunk_path = $temp_dir . 'chunk_' . $i;
            if (!file_exists($chunk_path) || filesize($chunk_path) === 0) {
                $missing[] = $i;
                continue;
            }
            if (!empty($metadata['chunk_checksums'][$i])) {
                $expected = $metadata['chunk_checksums'][$i];
                $actual   = $this->computeChunkChecksum($chunk_path);
                if ($actual !== $expected) {
                    $corrupted[] = $i;
                }
            }
        }

        if (!empty($missing)) {
            $this->jsonResponse(['success' => false, 'message' => count($missing) . ' chunk(s) manquant(s)', 'missing' => $missing]);
        }

        if (!empty($corrupted)) {
            $this->jsonResponse(['success' => false, 'message' => count($corrupted) . ' chunk(s) corrompu(s)', 'corrupted' => $corrupted]);
        }

        $safe_name     = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($metadata['file_name'], PATHINFO_FILENAME));
        $ext           = strtolower(pathinfo($metadata['file_name'], PATHINFO_EXTENSION));
        // S1: revalidation de l'extension par rapport à la config du type déclaré
        $type_config = $this->getTypeConfig($type);
        if (!in_array($ext, $type_config['extensions'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Extension non autorisée pour le type ' . $type . ' : ' . $ext]);
        }
        $original_name = date('YmdHis') . '_' . $safe_name . '.' . $ext;
        $target_dir    = $this->getTargetDir($type);
        $original_path = $target_dir['path'] . $original_name;

        $out = fopen($original_path, 'wb');
        if (!$out) {
            $this->jsonResponse(['success' => false, 'message' => 'Impossible de créer le fichier final']);
        }

        $total_written = 0;
        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            $chunk_file = $temp_dir . 'chunk_' . $i;
            $chunk_data = file_get_contents($chunk_file);
            if ($chunk_data === false) {
                fclose($out); @unlink($original_path);
                $this->jsonResponse(['success' => false, 'message' => "Impossible de lire le chunk {$i}"]);
            }
            $written = fwrite($out, $chunk_data);
            if ($written === false) {
                fclose($out); @unlink($original_path);
                $this->jsonResponse(['success' => false, 'message' => "Erreur écriture chunk {$i}"]);
            }
            $total_written += $written;
            unlink($chunk_file);
            unset($chunk_data);
        }
        fclose($out);

        @unlink($metadata_file);
        @rmdir($temp_dir);

        $final_size = filesize($original_path);
        $relative_path = $target_dir['relative'] . $original_name;

        $response = [
            'success' => true,
            'message' => 'Upload terminé',
            'data'    => [
                'original_file'   => $relative_path,
                'file_size'       => $this->formatBytes($final_size),
                'file_size_bytes' => $final_size,
                'mime_type'       => mime_content_type($original_path) ?: 'application/octet-stream',
            ]
        ];

        if ($type === 'audio') {
            $analysis    = $this->analyzeAudio($original_path);
            $job_id      = $this->enqueueProcessingJob('audio', $original_path, $original_name, $relative_path);

            $response['data']['analysis']    = $analysis;
            $response['data']['job_id']      = $job_id;
            $response['data']['job_status']  = $job_id ? 'queued' : 'synchronous';
            $response['data']['form_suggestions'] = [
                'titre'     => $analysis['title'] ?: $this->suggestTitle($metadata['file_name']),
                'credits'   => $analysis['artist'] ?: 'Artiste inconnu',
                'categorie' => $this->suggestAudioCategory($analysis)
            ];
        } elseif ($type === 'video') {
            $analysis   = $this->analyzeVideo($original_path);
            $job_id     = $this->enqueueProcessingJob('video', $original_path, $original_name, $relative_path);

            $response['data']['analysis']    = $analysis;
            $response['data']['job_id']      = $job_id;
            $response['data']['job_status']  = $job_id ? 'queued' : 'synchronous';
            $response['data']['form_suggestions'] = [
                'titre'     => $analysis['title'] ?: $this->suggestTitle($metadata['file_name']),
                'credits'   => $analysis['artist'] ?: 'Auteur inconnu',
                'categorie' => $this->suggestVideoCategory($analysis)
            ];
        } elseif ($type === 'document') {
            $job_id = $this->enqueueProcessingJob('document', $original_path, $original_name, $relative_path);
            $response['data']['job_id']     = $job_id;
            $response['data']['job_status'] = $job_id ? 'queued' : 'synchronous';
        }

        $this->jsonResponse($response);
    }

    private function enqueueProcessingJob($type, $original_path, $original_name, $relative_path)
    {
        $job_id = $this->MediaModel->enqueueJob($type, [
            'original_path' => $original_path,
            'original_name' => $original_name,
            'relative_path' => $relative_path,
        ]);

        if (!$job_id) {
            // Fallback synchrone si la file n'est pas inscriptible
            try {
                $this->runProcessingJob([
                    'type'    => $type,
                    'payload' => [
                        'original_path' => $original_path,
                        'original_name' => $original_name,
                        'relative_path' => $relative_path,
                    ],
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Media fallback sync job failed: ' . $e->getMessage());
            }
        }

        $this->maybeSpawnJobWorker();
        return $job_id;
    }

    private function maybeSpawnJobWorker()
    {
        $lock = FCPATH . 'uploads/temp/jobs/_worker.lock';
        $fp = @fopen($lock, 'c');
        if (!$fp) return;
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            if (is_file(FCPATH . 'uploads/temp/jobs/_spawn.ts') && (time() - (int)@file_get_contents(FCPATH . 'uploads/temp/jobs/_spawn.ts')) < 30) {
                flock($fp, LOCK_UN);
                fclose($fp);
                return;
            }
            @file_put_contents(FCPATH . 'uploads/temp/jobs/_spawn.ts', time());
            $cmd = sprintf('cd %s && %s index.php admin_galerie/Media/jobs > /dev/null 2>&1 &',
                escapeshellarg(FCPATH),
                escapeshellarg(PHP_BINARY)
            );
            @exec($cmd);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    // ==================== WORKER FILE DE JOBS ====================

    public function jobs()
    {
        if (!$this->input->is_cli_request()) {
            show_404();
            return;
        }

        set_time_limit(0);

        $lock = FCPATH . 'uploads/temp/jobs/_worker.lock';
        $fp = @fopen($lock, 'c');
        if (!$fp) exit(0);
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);
            exit(0);
        }

        $processed = 0;
        while (($job = $this->MediaModel->getNextJob()) !== null) {
            $claimed = $this->MediaModel->claimJob($job['id']);
            if (!$claimed) continue;

            try {
                $result = $this->runProcessingJob($claimed);
                $this->MediaModel->finishJob($claimed['id'], 'done', $result);
            } catch (\Exception $e) {
                $this->MediaModel->finishJob($claimed['id'], 'failed', null, $e->getMessage());
                log_message('error', 'Media job ' . $claimed['id'] . ' failed: ' . $e->getMessage());
            }
            $processed++;

            if ($processed >= 50) break;
        }

        $this->purgeExpiredUploads();

        flock($fp, LOCK_UN);
        fclose($fp);
        exit(0);
    }

    private function runProcessingJob($job)
    {
        $type = $job['type'] ?? '';
        $payload = $job['payload'] ?? [];
        $original_path = $payload['original_path'] ?? null;
        $original_name = $payload['original_name'] ?? null;
        $relative_path = $payload['relative_path'] ?? null;

        if (!$original_path || !file_exists($original_path)) {
            throw new \Exception('Fichier source introuvable pour le job: ' . $original_path);
        }

        $result = [
            'job_id'        => $job['id'] ?? null,
            'relative_path' => $relative_path,
        ];

        if ($type === 'audio') {
            $thumbnails  = $this->generateAudioThumbnails($original_path, $original_name);
            $waveform    = $this->generateWaveform($original_path, $original_name);
            $conversions = $this->convertToMultipleBitrates($original_path, $original_name);
            $result['thumbnails']  = $thumbnails;
            $result['waveform']    = $waveform;
            $result['conversions'] = $conversions;
        } elseif ($type === 'video') {
            $thumbnails = $this->generateVideoThumbnails($original_path, $original_name);
            $result['thumbnails'] = $thumbnails;
        } elseif ($type === 'document') {
            $gen = $this->generateDocumentThumbnails($original_path, $original_name);
            $result['thumbnails'] = $gen ? ['document' => $gen] : [];
        }

        $this->applyJobResultToMedia($result);
        return $result;
    }

    private function applyJobResultToMedia($result)
    {
        $relative_path = $result['relative_path'] ?? null;
        if (empty($relative_path)) return;

        $media = $this->MediaModel->readOne(['fichier' => $relative_path]);
        if (!$media) return;

        $update = [];

        if (!empty($result['thumbnails'])) {
            $thumbs = $result['thumbnails'];
            $miniature = $thumbs['cover'] ?? $thumbs['default'] ?? $thumbs['generated'] ?? $thumbs['document'] ?? null;
            if (!empty($miniature)) $update['miniature'] = $miniature;
        }

        if (!empty($result['waveform'])) {
            $update['waveform'] = $result['waveform'];
        }

        if (isset($result['conversions']) && !empty($result['conversions'])) {
            $update['converted_versions'] = json_encode($result['conversions'], JSON_UNESCAPED_UNICODE);
        }

        if (!empty($update)) {
            $this->MediaModel->update(['id_media' => $media['id_media']], $update);
        }
    }

    // ==================== PURGE & RATE LIMIT ====================

    private function purgeExpiredUploads($max_age = 86400)
    {
        $roots = ['audio', 'video', 'image', 'document'];
        $now = time();
        foreach ($roots as $type) {
            $dir = FCPATH . 'uploads/temp/' . $type;
            if (!is_dir($dir)) continue;
            foreach (glob($dir . '/*', GLOB_ONLYDIR) ?: [] as $upload_dir) {
                $mtime = filemtime($upload_dir) ?: $now;
                if (($now - $mtime) > $max_age) {
                    $this->removeDirectory($upload_dir);
                }
            }
        }
    }

    private function removeDirectory($dir)
    {
        foreach (glob($dir . '/*') ?: [] as $file) {
            if (is_dir($file)) {
                $this->removeDirectory($file);
            } else {
                @unlink($file);
            }
        }
        @rmdir($dir);
    }

    private function rateLimitCheck($action, $max, $window_seconds)
    {
        $dir = FCPATH . 'uploads/temp/ratelimit/';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $identifier = (string)($this->session->userdata('user_id') ?: $this->input->ip_address());
        $window = (int)floor(time() / $window_seconds);
        $key = md5($action . '|' . $identifier . '|' . $window);
        $file = $dir . $key . '.cnt';

        $count = is_file($file) ? (int)@file_get_contents($file) : 0;
        if ($count >= $max) {
            return false;
        }
        @file_put_contents($file, $count + 1, LOCK_EX);
        return true;
    }

    private function getTypeConfig($type)
    {
        switch ($type) {
            case 'audio': return ['extensions' => $this->media_config['audio_extensions'], 'max_size' => 4 * 1024 * 1024 * 1024];
            case 'video': return ['extensions' => $this->media_config['video_extensions'], 'max_size' => 2 * 1024 * 1024 * 1024];
            case 'image': return ['extensions' => $this->media_config['image_extensions'], 'max_size' => 100 * 1024 * 1024];
            case 'document': return ['extensions' => $this->media_config['document_extensions'], 'max_size' => 500 * 1024 * 1024];
            default: return ['extensions' => [], 'max_size' => 0];
        }
    }

    private function getTempDir($type)
    {
        $dirs = [
            'audio' => $this->paths['audio_temp'],
            'video' => $this->paths['video_temp'],
            'image' => $this->paths['image_temp'],
            'document' => $this->paths['document_temp'],
        ];
        return $dirs[$type] ?? $this->paths['image_temp'];
    }

    private function getTargetDir($type)
    {
        // S2: le type 'link' ne doit jamais uploader de fichier
        if ($type === 'link') {
            throw new Exception('Le type link ne peut pas uploader de fichier');
        }

        $dirs = [
            'audio'    => ['path' => $this->paths['audio_originals'], 'relative' => 'attachments/Audio/Originals/'],
            'video'    => ['path' => $this->paths['video_originals'], 'relative' => 'attachments/Video/Originals/'],
            'image'    => ['path' => $this->paths['image_originals'], 'relative' => 'attachments/Image/Originals/'],
            'document' => ['path' => $this->paths['document_files'], 'relative' => 'attachments/Document/Files/'],
        ];
        return $dirs[$type] ?? $dirs['image'];
    }

    // ==================== STREAMING ====================

    public function getJson($id)
    {
        $media = $this->MediaModel->readOne(['id_media' => $id]);
        if (!$media) {
            $this->jsonResponse(['success' => false, 'message' => 'Média introuvable']);
        }

        $meta = !empty($media['metadata_id3']) ? json_decode($media['metadata_id3'], true) : [];

        $type = $media['type'];
        $placeholder = base_url('assets/images/' . $type . '-placeholder.jpg');
        $thumb = $placeholder;
        if (!empty($media['miniature'])) {
            $is_remote = (strpos($media['miniature'], 'http') === 0);
            if ($is_remote || file_exists(FCPATH . $media['miniature'])) {
                $thumb = $is_remote ? $media['miniature'] : base_url($media['miniature']);
            }
        }

        $dur = $media['duree'] ?? 0;
        $h = floor($dur / 3600);
        $m = floor(($dur % 3600) / 60);
        $s = floor($dur % 60);
        $dur_fmt = $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);

        $data = [
            'id'      => (int)$media['id_media'],
            'titre'   => $media['titre'] ?? 'Sans titre',
            'type'    => $type,
            'credits' => $media['credits'] ?? '',
            'thumb'   => $thumb,
            'duree'   => $dur_fmt,
            'description'   => $media['description'] ?? '',
            'date_media'    => $media['date_media'] ?? '',
            'categorie'     => $media['categorie'] ?? '',
            'miniature'     => $media['miniature'] ?? '',
            'est_actif'     => (int)($media['est_actif'] ?? 0),
            'is_for_website'   => (int)($media['is_for_website'] ?? 0),
            'is_for_whatsapp'  => (int)($media['is_for_whatsapp'] ?? 0),
        ];

        if ($type === 'audio') {
            $bitrate = $media['bitrate'] ?? 0;
            $data['bitrate'] = $bitrate > 0 ? round($bitrate / 1000) . ' kbps' : 'N/A';
            $data['stream_url'] = base_url('admin/media/stream/audio/' . $media['id_media']);
            $waveform = null;
            if (!empty($meta['waveform'])) {
                $w_path = $meta['waveform'];
                if (file_exists(FCPATH . $w_path)) {
                    $waveform = base_url($w_path);
                }
            }
            if (!$waveform && !empty($meta['thumbnails']->generated)) {
                $w_path = $meta['thumbnails']->generated;
                if (file_exists(FCPATH . $w_path)) {
                    $waveform = base_url($w_path);
                }
            }
            $data['waveform'] = $waveform;
        } elseif ($type === 'video') {
            $resolution = 'N/A';
            if (!empty($meta['analysis']['width']) && !empty($meta['analysis']['height'])) {
                $resolution = $meta['analysis']['width'] . 'x' . $meta['analysis']['height'];
            }
            $data['resolution'] = $resolution;
            $data['stream_url'] = base_url('admin/media/stream/video/' . $media['id_media']);
        } elseif ($type === 'link') {
            $data['lien'] = $media['lien'] ?? '';
            $data['miniature_externe'] = $media['miniature'] ?? '';
        } elseif ($type === 'image') {
            $data['file_url'] = !empty($media['fichier']) ? base_url($media['fichier']) : null;
            $data['mime_type'] = $media['mime_type'] ?? '';
        } elseif ($type === 'document') {
            $data['file_url'] = !empty($media['fichier']) ? base_url($media['fichier']) : null;
            $data['mime_type'] = $media['mime_type'] ?? '';
        }

        $this->jsonResponse(['success' => true, 'data' => $data]);
    }

    public function stream($type, $id)
    {
        if (!in_array($type, ['audio', 'video', 'image', 'document'])) { show_404(); return; }

        $media = $this->MediaModel->readOne(['id_media' => $id]);
        if (!$media) { show_404(); return; }

        $filename = !empty($media['fichier']) ? basename($media['fichier']) : null;
        if (empty($filename)) { show_404(); return; }

        $this->serveFile($filename, $type);
    }

    public function download($id)
    {
        $media = $this->MediaModel->readOne(['id_media' => $id]);
        if (!$media || empty($media['fichier'])) { show_404(); return; }

        $path = FCPATH . $media['fichier'];
        if (!file_exists($path)) { show_404(); return; }

        $filename = basename($media['fichier']);
        header('Content-Type: ' . ($media['mime_type'] ?? mime_content_type($path) ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    private function serveFile($filename, $type)
    {
        if (empty($filename)) { show_404(); return; }

        $filename  = basename($filename);
        $dirs = [
            'audio' => $this->paths['audio_originals'],
            'video' => $this->paths['video_originals'],
            'image' => $this->paths['image_originals'],
            'document' => $this->paths['document_files'],
        ];
        $fallback_dirs = [
            'audio' => $this->paths['audio_converted'],
            'video' => $this->paths['video_encoded'],
            'image' => $this->paths['image_thumbnails'],
            'document' => $this->paths['document_thumbnails'],
        ];
        $root_dirs = [
            'audio' => $this->paths['audio_originals'] . '../',
            'video' => $this->paths['video_originals'] . '../',
            'image' => $this->paths['image_originals'] . '../',
            'document' => $this->paths['document_files'] . '../',
        ];

        $file_path = $dirs[$type] . $filename;
        if (!file_exists($file_path)) {
            $root_path = realpath($root_dirs[$type]) . '/' . $filename;
            if (file_exists($root_path)) {
                $file_path = $root_path;
            } else {
                $base_name = pathinfo($filename, PATHINFO_FILENAME);
                $glob = glob($fallback_dirs[$type] . $base_name . '*');
                if (!empty($glob)) $file_path = $glob[0];
            }
        }

        if (!file_exists($file_path)) {
            log_message('error', "Media stream file not found: " . $file_path);
            show_404(); return;
        }

        $mime = mime_content_type($file_path) ?: 'application/octet-stream';
        $content_types = [
            'audio' => 'audio/mpeg',
            'video' => 'video/mp4',
            'image' => $mime,
            'document' => $mime,
        ];

        $file_size = filesize($file_path);
        $start = 0;
        $end   = $file_size - 1;

        header('Content-Type: ' . ($content_types[$type] ?? 'application/octet-stream'));
        header('Accept-Ranges: bytes');
        header('Cache-Control: public, max-age=31536000');

        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
                $start = intval($m[1]);
                if ($start >= $file_size) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes */{$file_size}");
                    exit;
                }
                $end = !empty($m[2]) ? min(intval($m[2]), $file_size - 1) : $file_size - 1;
                if ($start > $end) $end = $start;
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

        $ref_folder = FCPATH . 'attachments/Media/Thumbnails/Custom/';
        if (!is_dir($ref_folder)) {
            if (!mkdir($ref_folder, 0777, true) && !is_dir($ref_folder)) {
                $this->jsonResponse(['success' => false, 'message' => 'Erreur création dossier miniature']);
            }
        }

        $code           = date("YmdHis") . uniqid();
        $final_filename = $code . '.' . $ext;
        $destination    = $ref_folder . $final_filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur sauvegarde miniature']);
        }

        if ($this->gd_available) $this->resizeThumbnail($destination, 800, 800);

        $relative_path = 'attachments/Media/Thumbnails/Custom/' . $final_filename;

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

    // ==================== ANALYSE AUDIO ====================

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

    private function generateAudioThumbnails($audio_path, $filename)
    {
        $result    = ['cover' => null, 'generated' => null];
        if (!$this->ffmpeg_path) return $result;

        $base_name  = pathinfo($filename, PATHINFO_FILENAME);
        $cover_name = $base_name . '_cover.jpg';
        $cover_path = $this->paths['audio_thumbnails'] . $cover_name;

        exec(sprintf('%s -v quiet -i %s -an -vcodec copy -f image2 -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path), escapeshellarg($audio_path), escapeshellarg($cover_path)), $out, $code);

        if (file_exists($cover_path) && filesize($cover_path) > 1000) {
            $result['cover'] = 'attachments/Audio/Thumbnails/' . $cover_name;
        } else {
            @unlink($cover_path);
            $logo_source = FCPATH . 'attachments/Configurations/site_logo_20260320151223_69bd47b771f92.jpeg';
            if (file_exists($logo_source)) {
                $gen_name = $base_name . '_logo.jpg';
                $gen_path = $this->paths['audio_thumbnails'] . $gen_name;
                copy($logo_source, $gen_path);
                if (file_exists($gen_path)) $result['generated'] = 'attachments/Audio/Thumbnails/' . $gen_name;
            }
        }

        return $result;
    }

    private function generateWaveform($audio_path, $filename)
    {
        if (!$this->ffmpeg_path) return null;
        $base_name     = pathinfo($filename, PATHINFO_FILENAME);
        $waveform_name = $base_name . '_wave.png';
        $waveform_path = $this->paths['audio_waveforms'] . $waveform_name;

        exec(sprintf('%s -v quiet -i %s -filter_complex "aformat=channel_layouts=mono,showwavespic=s=1200x200:colors=#FF0000|#FF6B6B" -frames:v 1 -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path), escapeshellarg($audio_path), escapeshellarg($waveform_path)), $out, $code);

        return file_exists($waveform_path) ? 'attachments/Audio/Waveforms/' . $waveform_name : null;
    }

    private function convertToMultipleBitrates($audio_path, $filename)
    {
        if (!$this->ffmpeg_path) return [];
        $base_name   = pathinfo($filename, PATHINFO_FILENAME);
        $conversions = [];

        foreach ($this->media_config['qualities'] as $quality => $config) {
            $output_name = $base_name . $config['suffix'] . '.mp3';
            $output_path = $this->paths['audio_converted'] . $output_name;

            exec(sprintf('%s -v quiet -i %s -codec:a libmp3lame -b:a %s -map_metadata 0 -id3v2_version 3 -y %s 2>&1',
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

    // ==================== ANALYSE VIDEO ====================

    private function analyzeVideo($file_path)
    {
        $default = [
            'duration' => 0, 'duration_formatted' => '0:00',
            'size' => 0, 'width' => 0, 'height' => 0,
            'fps' => 0, 'codec' => 'unknown', 'bitrate' => 0,
            'resolution' => 'N/A',
            'title' => null, 'artist' => null,
            'sample_rate' => 0, 'channels' => 0
        ];

        if (!$this->ffprobe_path || !file_exists($file_path)) return $default;

        $cmd = sprintf('%s -v quiet -print_format json -show_format -show_streams %s 2>&1',
            escapeshellarg($this->ffprobe_path), escapeshellarg($file_path));

        exec($cmd, $output, $code);
        if ($code !== 0) return $default;

        $data   = json_decode(implode("\n", $output), true);
        $format = $data['format'] ?? [];
        $video  = null;
        $audio  = null;

        foreach ($data['streams'] ?? [] as $stream) {
            $type = $stream['codec_type'] ?? '';
            if ($type === 'video' && !$video) $video = $stream;
            if ($type === 'audio' && !$audio) $audio = $stream;
        }

        $tags     = $format['tags'] ?? [];
        $duration = (float)($format['duration'] ?? 0);
        $h = floor($duration / 3600);
        $m = floor(($duration % 3600) / 60);
        $s = floor($duration % 60);
        $dur_fmt = $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);

        $fps = 0;
        if ($video && !empty($video['r_frame_rate'])) {
            $parts = explode('/', $video['r_frame_rate']);
            if (count($parts) === 2 && $parts[1] > 0) {
                $fps = round($parts[0] / $parts[1], 2);
            }
        }

        $width  = (int)($video['width'] ?? 0);
        $height = (int)($video['height'] ?? 0);

        return [
            'duration'           => $duration,
            'duration_formatted' => $dur_fmt,
            'size'               => (int)($format['size'] ?? filesize($file_path)),
            'width'              => $width,
            'height'             => $height,
            'resolution'         => $width && $height ? $width . 'x' . $height : 'N/A',
            'fps'                => $fps,
            'codec'              => $video['codec_name'] ?? 'unknown',
            'bitrate'            => (int)($format['bit_rate'] ?? 0),
            'sample_rate'        => (int)($audio['sample_rate'] ?? 0),
            'channels'           => (int)($audio['channels'] ?? 0),
            'title'              => $tags['title'] ?? null,
            'artist'             => $tags['artist'] ?? null
        ];
    }

    private function generateVideoThumbnails($video_path, $filename)
    {
        $result = ['default' => null, 'poster' => null];
        if (!$this->ffmpeg_path) return $result;

        $base_name   = pathinfo($filename, PATHINFO_FILENAME);
        $thumb_name  = $base_name . '_thumb.jpg';
        $poster_name = $base_name . '_poster.jpg';
        $thumb_path  = $this->paths['video_thumbnails'] . $thumb_name;
        $poster_path = $this->paths['video_posters'] . $poster_name;

        exec(sprintf('%s -ss 00:00:01 -i %s -vframes 1 -q:v 5 -vf "scale=640:-1" -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path), escapeshellarg($video_path), escapeshellarg($thumb_path)));

        exec(sprintf('%s -ss 00:00:05 -i %s -vframes 1 -q:v 3 -vf "scale=1280:-1" -y %s 2>&1',
            escapeshellarg($this->ffmpeg_path), escapeshellarg($video_path), escapeshellarg($poster_path)));

        if (file_exists($thumb_path) && filesize($thumb_path) > 1000) {
            $result['default'] = 'attachments/Video/Thumbnails/' . $thumb_name;
        }
        if (file_exists($poster_path) && filesize($poster_path) > 1000) {
            $result['poster'] = 'attachments/Video/Posters/' . $poster_name;
        }

        return $result;
    }

    private function generateDocumentThumbnails($file_path, $filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf'])) return null;

        $gs_path = trim(`which gs 2>/dev/null`);
        if (empty($gs_path)) return null;

        $base_name  = pathinfo($filename, PATHINFO_FILENAME);
        $thumb_name = $base_name . '_thumb.jpg';
        $thumb_path = $this->paths['document_thumbnails'] . $thumb_name;

        $cmd = sprintf('%s -dQUIET -dSAFER -dBATCH -dNOPAUSE -dNOPROMPT -sDEVICE=jpeg -dFirstPage=1 -dLastPage=1 -r72 -sOutputFile=%s %s 2>&1',
            escapeshellarg($gs_path), escapeshellarg($thumb_path), escapeshellarg($file_path));
        exec($cmd, $out, $code);

        if ($code === 0 && file_exists($thumb_path) && filesize($thumb_path) > 1000) {
            return 'attachments/Document/Thumbnails/' . $thumb_name;
        }

        @unlink($thumb_path);
        return null;
    }

    // ==================== HELPERS ====================

    private function deleteDerivedFiles($media)
    {
        $type = $media['type'] ?? '';
        $base_name = pathinfo($media['fichier'] ?? '', PATHINFO_FILENAME);
        if (empty($base_name)) return;

        $derived_dirs = [
            'audio'    => ['Converted', 'Thumbnails', 'Waveforms', 'Covers'],
            'video'    => ['Encoded', 'Thumbnails', 'Posters'],
            'image'    => ['Thumbnails'],
            'document' => ['Thumbnails'],
        ];

        $type_key = ucfirst($type);
        foreach ($derived_dirs[$type] ?? [] as $dir) {
            $pattern = FCPATH . 'attachments/' . $type_key . '/' . $dir . '/' . $base_name . '.*';
            foreach (glob($pattern) as $file) {
                if (is_file($file)) @unlink($file);
            }
        }
    }

    private function formatBytes($bytes)
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B','KB','MB','GB','TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function suggestTitle($filename)
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        return ucwords(trim(preg_replace('/[^a-zA-Z0-9]/', ' ', $name)));
    }

    private function suggestAudioCategory($analysis)
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

    private function suggestVideoCategory($analysis)
    {
        $title   = strtolower($analysis['title'] ?? '');
        $mappings = [
            'Documentaire'  => ['documentaire','docu','documentary'],
            'Interview'     => ['interview','conversation','entretien'],
            'Reportage'     => ['reportage','report'],
            'Tutoriel'      => ['tutoriel','tutorial','guide','howto'],
            'Promotion'     => ['promotion','pub','commercial','annonce'],
            'Événement'     => ['event','live','concert','conference'],
            'Webinaire'     => ['webinar','webinaire','seminar'],
            'Podcast'       => ['podcast'],
            'Vlog'          => ['vlog','daily']
        ];

        foreach ($mappings as $cat => $keywords) {
            foreach ($keywords as $kw) {
                if (strpos($title, $kw) !== false) return $cat;
            }
        }

        $dur = $analysis['duration'] ?? 0;
        if ($dur > 600 && $dur < 3600) return 'Interview';
        if ($dur > 3600) return 'Conférence';

        return 'Documentaire';
    }

    public function checkServerLimits()
    {
        if (!$this->session->userdata('logged_in')) { show_404(); return; }
        $limits = $this->media_config['server_limits'];
        $chunk  = $this->detectOptimalChunkSize();

        echo "<pre>";
        echo "Serveur: " . php_uname('s') . "\n";
        echo "PHP upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
        echo "PHP post_max_size: " . ini_get('post_max_size') . "\n";
        echo "Nginx client_max_body_size détecté: " . $this->formatBytes($limits['nginx_client_max_body_size']) . "\n";
        echo "---\n";
        echo "Taille de chunk recommandée: " . $this->formatBytes($chunk) . "\n";
        echo "Fichier max supporté: 4 GB\n";
        echo "FFmpeg: " . ($this->ffmpeg_path ?: 'Non trouvé') . "\n";
        echo "</pre>";
        exit;
    }

    public function updateAllSlugs()
    {
        if (!$this->session->userdata('logged_in')) { show_404(); return; }

        $medias = $this->db->query("SELECT id_media, titre, type FROM galerie_medias WHERE est_actif = 1")->result_array();
        $updated = 0;

        foreach ($medias as $media) {
            $slug = $this->generateUniqueSlug($media['titre'], $media['id_media']);
            $this->db->where('id_media', $media['id_media'])->update('galerie_medias', ['slug' => $slug]);
            $updated++;
            echo "ID: {$media['id_media']} ({$media['type']}) - Slug: {$slug}<br>";
        }

        echo "<br>Total: {$updated} slugs mis à jour.";
    }
}
