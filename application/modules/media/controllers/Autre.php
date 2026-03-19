<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Autre Controller - Gestion des médias divers
 * Upload chunked 5MB, miniatures modifiables, interface moderne
 * Version: 4.0 - Style YouTube Studio
 */
class Autre extends MX_Controller {

    private $paths;
    private $upload_config;
    private $type_configs;
    private $gd_available = false;

    function __construct()
    {
        parent::__construct();
        
        // DÉSACTIVER CSRF POUR TOUTES LES MÉTHODES AJAX
        $this->_csrf_off();
        
        $this->initializePaths();
        $this->initializeConfig();
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
            'temp'       => $base . 'uploads/temp/autre/',
            'files'      => $base . 'attachments/Autre/Files/',
            'thumbnails' => $base . 'attachments/Autre/Thumbnails/',
            'custom'     => $base . 'attachments/Autre/Thumbnails/Custom/',
        ];
    }

    private function initializeConfig()
    {
        $this->upload_config = [
            'chunk_size'    => 5 * 1024 * 1024,  // 5MB chunks
            'max_file_size' => 2 * 1024 * 1024 * 1024, // 2GB max
        ];

        $this->type_configs = [
            'link' => [
                'label'   => 'Lien / URL',
                'icon'    => 'bx-link',
                'color'   => 'info',
                'accept'  => null,
                'max_size'=> 0,
                'has_file'=> false
            ],
            'book' => [
                'label'   => 'Livre / PDF',
                'icon'    => 'bx-book',
                'color'   => 'warning',
                'accept'  => ['pdf', 'epub', 'mobi'],
                'max_size'=> 500 * 1024 * 1024, // 500MB
                'has_file'=> true
            ],
            'texte' => [
                'label'   => 'Texte',
                'icon'    => 'bx-text',
                'color'   => 'success',
                'accept'  => null,
                'max_size'=> 0,
                'has_file'=> false
            ],
            'photo' => [
                'label'   => 'Photo / Image',
                'icon'    => 'bx-image',
                'color'   => 'danger',
                'accept'  => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff'],
                'max_size'=> 50 * 1024 * 1024, // 50MB
                'has_file'=> true
            ],
            'other' => [
                'label'   => 'Autre fichier',
                'icon'    => 'bx-file',
                'color'   => 'secondary',
                'accept'  => '*',
                'max_size'=> 500 * 1024 * 1024, // 500MB
                'has_file'=> true
            ]
        ];
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
        $items = $this->Model->read('galerie_medias', ['type' => 'autre'], 'id_media', 'DESC');
        
        // Formater les données
        foreach ($items as &$item) {
            $item['taille_formatee'] = !empty($item['taille']) ? $this->formatBytes($item['taille']) : '-';
            $item['type_config'] = $this->type_configs[$item['sous_type'] ?? 'other'] ?? $this->type_configs['other'];
        }

        $data = [
            'items'        => $items,
            'categories'   => $this->getExistingCategories(),
            'stats'        => $this->calculateStats(),
            'type_configs' => $this->type_configs
        ];
        
        $this->load->view('Autre_View', $data);
    }

    // ==================== API UPLOAD (JSON PUR) ====================

    public function initUpload()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');
        $sous_type = $this->input->post('sous_type') ?: 'other';

        // Validation
        if (empty($file_name) || $file_size <= 0) {
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
            return;
        }

        if (!isset($this->type_configs[$sous_type])) {
            echo json_encode(['success' => false, 'message' => 'Type non supporté']);
            return;
        }

        $config = $this->type_configs[$sous_type];
        
        // Vérifier taille max pour ce type
        if ($config['max_size'] > 0 && $file_size > $config['max_size']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Fichier trop grand. Max: ' . $this->formatBytes($config['max_size'])
            ]);
            return;
        }

        // Vérifier extension
        if ($config['accept'] && $config['accept'] !== '*') {
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($ext, $config['accept'])) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Format non supporté: ' . $ext
                ]);
                return;
            }
        }

        // Créer session upload
        $upload_id = 'autre_' . uniqid() . '_' . bin2hex(random_bytes(4));
        $temp_dir  = $this->paths['temp'] . $upload_id . '/';
        
        if (!@mkdir($temp_dir, 0777, true)) {
            echo json_encode(['success' => false, 'message' => 'Erreur création dossier']);
            return;
        }

        $total_chunks = (int)ceil($file_size / $this->upload_config['chunk_size']);
        
        $metadata = [
            'upload_id'       => $upload_id,
            'file_name'       => $file_name,
            'file_size'       => $file_size,
            'sous_type'       => $sous_type,
            'total_chunks'    => $total_chunks,
            'uploaded_chunks' => [],
            'created_at'      => time(),
            'status'          => 'uploading'
        ];
        
        file_put_contents($temp_dir . 'metadata.json', json_encode($metadata));

        echo json_encode([
            'success'      => true,
            'upload_id'    => $upload_id,
            'chunk_size'   => $this->upload_config['chunk_size'],
            'total_chunks' => $total_chunks
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

        $metadata   = json_decode(file_get_contents($metadata_file), true);
        $chunk_path = $temp_dir . 'chunk_' . $chunk_index;
        
        // Chunk déjà présent
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

        // Sauvegarder le chunk
        if (empty($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
            $error = $_FILES['chunk']['error'] ?? 'unknown';
            echo json_encode(['success' => false, 'message' => 'Erreur chunk: ' . $error]);
            return;
        }

        if (!@move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path)) {
            echo json_encode(['success' => false, 'message' => 'Erreur écriture disque']);
            return;
        }

        // Mettre à jour metadata
        $metadata['uploaded_chunks'][] = $chunk_index;
        sort($metadata['uploaded_chunks']);
        file_put_contents($metadata_file, json_encode($metadata));

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
        $safe_name     = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($metadata['file_name'], PATHINFO_FILENAME));
        $original_name = date('YmdHis') . '_' . $safe_name . '_' . uniqid() . '.' . pathinfo($metadata['file_name'], PATHINFO_EXTENSION);
        $original_path = $this->paths['files'] . $original_name;
        
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

        // Traitement selon le type
        $sous_type = $metadata['sous_type'];
        $processing = $this->processFile($original_path, $original_name, $sous_type);

        // CORRECTION: S'assurer que thumbnail est un objet
        $thumbnail_obj = new stdClass();
        if (!empty($processing['thumbnail'])) {
            $thumbnail_obj->generated = $processing['thumbnail'];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Upload complété',
            'data'    => [
                'original_file'  => 'attachments/Autre/Files/' . $original_name,
                'file_name'      => $original_name,
                'file_size'      => $this->formatBytes(filesize($original_path)),
                'sous_type'      => $sous_type,
                'thumbnails'     => $thumbnail_obj,
                'extra_data'     => $processing['extra'] ?? null,
                'form_suggestions' => [
                    'titre'     => $this->suggestTitle($metadata['file_name']),
                    'categorie' => $this->suggestCategory($sous_type)
                ]
            ]
        ]);
        return;
    }

    // ==================== UPLOAD MINIATURE PERSONNALISÉE ====================

    public function uploadThumbnail()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        if (empty($_FILES['thumbnail_file']) || $_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'success' => false, 
                'message' => 'Aucun fichier reçu ou erreur upload: ' . ($_FILES['thumbnail_file']['error'] ?? 'unknown')
            ]);
            return;
        }

        $file = $_FILES['thumbnail_file'];
        
        // Validation extension
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $valid_ext = ['gif', 'jpg', 'png', 'jpeg', 'webp', 'svg'];

        if (!in_array($file_extension, $valid_ext)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Format non supporté. Formats acceptés: ' . implode(', ', $valid_ext)
            ]);
            return;
        }

        // Créer le dossier si nécessaire
        if (!is_dir($this->paths['custom'])) {
            mkdir($this->paths['custom'], 0777, TRUE);
        }

        // Déplacer le fichier
        $code = date("YmdHis") . uniqid();
        $final_filename = $code . "." . $file_extension;
        $destination = $this->paths['custom'] . $final_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
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

        $relative_path = 'attachments/Autre/Thumbnails/Custom/' . $final_filename;
        
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

    // ==================== CRUD ====================

    public function Create()
    {
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('sous_type', 'Type', 'required');
        
        $sous_type = $this->input->post('sous_type');
        
        if ($sous_type === 'link') {
            $this->form_validation->set_rules('lien', 'Lien', 'required|valid_url');
        } elseif ($sous_type !== 'texte') {
            $this->form_validation->set_rules('uploaded_file_path', 'Fichier', 'required');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('autre'));
            return;
        }

        $auto_data = json_decode($this->input->post('auto_detected_data') ?: '{}', true);
        
        $data = $this->prepareData($sous_type, 'create', $auto_data);
        
        if (!$data) {
            redirect(base_url('autre'));
            return;
        }

        $rsp = $this->Model->create('galerie_medias', $data);
        
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Élément créé avec succès' : 'Erreur création');
        redirect(base_url('autre'));
    }

    public function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('autre'));
            return;
        }

        $current = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        if (!$current) {
            $this->session->set_flashdata('error', 'Élément non trouvé');
            redirect(base_url('autre'));
            return;
        }

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

        // Gestion lien pour type link
        if ($current['sous_type'] === 'link') {
            $data['lien'] = $this->input->post('lien');
            $data['miniature'] = $this->extractLinkThumb($data['lien']);
        }

        // Gestion miniature modifiée
        $new_thumbnail = $this->input->post('thumbnail');
        if (!empty($new_thumbnail) && $new_thumbnail !== ($current['miniature'] ?? '')) {
            if (!empty($current['miniature']) && strpos($current['miniature'], 'Custom/') !== false) {
                @unlink(FCPATH . $current['miniature']);
            }
            $data['miniature'] = $new_thumbnail;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);
        
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Élément mis à jour' : 'Erreur mise à jour');
        redirect(base_url('autre'));
    }

    public function Delete()
    {
        $id = $this->input->post('id');
        $item = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        if ($item) {
            $this->deleteFiles($item);
            
            $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
                'est_actif'  => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Élément supprimé' : 'Erreur suppression');
        }
        
        redirect(base_url('autre'));
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

    // ==================== TRAITEMENT FICHIERS ====================

    private function processFile($file_path, $filename, $sous_type)
    {
        $result = ['thumbnail' => null, 'extra' => null];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        switch ($sous_type) {
            case 'photo':
                $result = array_merge($result, $this->processImage($file_path, $filename));
                break;
                
            case 'book':
                if ($ext === 'pdf') {
                    $result = array_merge($result, $this->processPDF($file_path, $filename));
                } else {
                    $result['thumbnail'] = 'assets/images/book-default.png';
                }
                break;
                
            case 'other':
                $result['thumbnail'] = $this->getFileIcon($ext);
                break;
        }

        return $result;
    }

    private function processImage($source, $filename)
    {
        $result = ['thumbnail' => null, 'extra' => null];
        
        $dims = @getimagesize($source);
        if ($dims) {
            $result['extra'] = ['dimensions' => $dims[0] . 'x' . $dims[1]];
        }

        // Créer miniature
        $thumb_name = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumb_path = $this->paths['thumbnails'] . $thumb_name;
        
        if ($this->createThumbnail($source, $thumb_path, 400, 300)) {
            $result['thumbnail'] = 'attachments/Autre/Thumbnails/' . $thumb_name;
        } else {
            $result['thumbnail'] = 'assets/images/photo-default.png';
        }

        return $result;
    }

    private function processPDF($file_path, $filename)
    {
        $result = ['thumbnail' => null, 'extra' => null];
        
        // Compter pages
        $content = file_get_contents($file_path, false, null, 0, 50000);
        if (preg_match('/\/Type\s*\/Pages.*?\/Count\s+(\d+)/s', $content, $m)) {
            $result['extra'] = ['pages' => (int)$m[1]];
        }

        // Générer miniature avec FFmpeg si dispo
        $thumb_name = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumb_path = $this->paths['thumbnails'] . $thumb_name;
        
        $ffmpeg = $this->findFFmpeg();
        if ($ffmpeg) {
            $cmd = sprintf(
                '%s -i %s -vf "select=eq(n\\,0),scale=400:-1" -vframes 1 -y %s 2>&1',
                escapeshellarg($ffmpeg),
                escapeshellarg($file_path),
                escapeshellarg($thumb_path)
            );
            exec($cmd, $output, $code);
            if ($code === 0 && file_exists($thumb_path)) {
                $result['thumbnail'] = 'attachments/Autre/Thumbnails/' . $thumb_name;
            }
        }

        if (!$result['thumbnail']) {
            $result['thumbnail'] = 'assets/images/pdf-default.png';
        }

        return $result;
    }

    private function createThumbnail($source, $dest, $max_w, $max_h)
    {
        if (!extension_loaded('gd')) return false;

        $info = getimagesize($source);
        if (!$info) return false;

        list($w, $h, $type) = $info;
        $ratio = min($max_w / $w, $max_h / $h);
        $new_w = (int)($w * $ratio);
        $new_h = (int)($h * $ratio);

        switch ($type) {
            case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($source); break;
            case IMAGETYPE_PNG: $src = imagecreatefrompng($source); break;
            case IMAGETYPE_GIF: $src = imagecreatefromgif($source); break;
            case IMAGETYPE_WEBP: $src = imagecreatefromwebp($source); break;
            default: return false;
        }

        if (!$src) return false;

        $dst = imagecreatetruecolor($new_w, $new_h);
        
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
        $success = imagejpeg($dst, $dest, 85);
        
        imagedestroy($src);
        imagedestroy($dst);
        
        return $success;
    }

    // ==================== HELPERS ====================

    private function prepareData($sous_type, $mode, $auto_data = [])
    {
        $data = [
            'titre'           => $this->input->post('titre'),
            'type'            => 'autre',
            'sous_type'       => $sous_type,
            'description'     => $this->input->post('description') ?: null,
            'categorie'       => $this->input->post('categorie') ?: null,
            'date_media'      => $this->input->post('date_media') ?: null,
            'credits'         => $this->input->post('credits') ?: null,
            'est_actif'       => $this->input->post('est_actif') ? 1 : 1,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website'  => $this->input->post('is_for_website') ? 1 : 1,
            'updated_at'      => date('Y-m-d H:i:s')
        ];

        if ($mode === 'create') {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        switch ($sous_type) {
            case 'link':
                $data['lien'] = $this->input->post('lien');
                $data['miniature'] = $this->extractLinkThumb($data['lien']);
                break;
                
            case 'texte':
                $data['contenu_texte'] = $this->input->post('contenu_texte') ?: null;
                $data['miniature'] = 'assets/images/text-default.png';
                break;
                
            default:
                $file_path = $this->input->post('uploaded_file_path');
                
                if (!empty($file_path)) {
                    $full = FCPATH . $file_path;
                    $data['fichier'] = $file_path;
                    $data['taille'] = filesize($full);
                    $data['mime_type'] = mime_content_type($full);
                    $data['lien'] = null;
                    
                    // Miniature depuis auto_data ou défault
                    $thumbnails = $auto_data['thumbnails'] ?? new stdClass();
                    $data['miniature'] = $this->input->post('thumbnail') ?: ($thumbnails->generated ?? $this->getFileIcon(pathinfo($file_path, PATHINFO_EXTENSION)));
                }
                break;
        }

        return $data;
    }

    private function extractLinkThumb($url)
    {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
        }
        
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return "https://vumbnail.com/{$m[1]}.jpg";
        }
        
        $domain = parse_url($url, PHP_URL_HOST);
        if ($domain) {
            return "https://www.google.com/s2/favicons?domain={$domain}&sz=128";
        }
        
        return 'assets/images/link-default.png';
    }

    private function getFileIcon($ext)
    {
        $icons = [
            'pdf' => 'assets/images/pdf-default.png',
            'doc' => 'assets/images/doc-default.png',
            'docx' => 'assets/images/doc-default.png',
            'xls' => 'assets/images/xls-default.png',
            'xlsx' => 'assets/images/xls-default.png',
            'ppt' => 'assets/images/ppt-default.png',
            'pptx' => 'assets/images/ppt-default.png',
            'zip' => 'assets/images/zip-default.png',
            'mp3' => 'assets/images/audio-default.png',
            'mp4' => 'assets/images/video-default.png',
        ];
        return $icons[$ext] ?? 'assets/images/file-default.png';
    }

    private function deleteFiles($item)
    {
        if (!empty($item['fichier']) && file_exists(FCPATH . $item['fichier'])) {
            @unlink(FCPATH . $item['fichier']);
        }
        if (!empty($item['miniature']) && strpos($item['miniature'], 'http') !== 0 && file_exists(FCPATH . $item['miniature'])) {
            @unlink(FCPATH . $item['miniature']);
        }
    }

    private function findFFmpeg()
    {
        $paths = ['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', 'C:\\ffmpeg\\bin\\ffmpeg.exe'];
        foreach ($paths as $p) {
            if (empty($p)) continue;
            exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    private function getExistingCategories()
    {
        $this->db->select('DISTINCT(categorie) as cat');
        $this->db->where('type', 'autre');
        $this->db->where('categorie IS NOT NULL');
        $this->db->where('categorie !=', '');
        return array_filter(array_column($this->db->get('galerie_medias')->result_array(), 'cat'));
    }

    private function calculateStats()
    {
        $stats = [
            'total' => 0,
            'by_type' => array_fill_keys(array_keys($this->type_configs), 0),
            'total_size' => 0,
            'total_size_formatted' => '0 B'
        ];

        $items = $this->Model->read('galerie_medias', ['type' => 'autre', 'est_actif' => 1]);
        
        foreach ($items as $item) {
            $stats['total']++;
            $type = $item['sous_type'] ?? 'other';
            if (isset($stats['by_type'][$type])) $stats['by_type'][$type]++;
            $stats['total_size'] += $item['taille'] ?? 0;
        }
        
        $stats['total_size_formatted'] = $this->formatBytes($stats['total_size']);
        return $stats;
    }

    private function suggestTitle($filename)
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[^a-zA-Z0-9]/', ' ', $name);
        return ucwords(trim($name));
    }

    private function suggestCategory($sous_type)
    {
        $mappings = [
            'link' => 'Ressources',
            'book' => 'Documentation',
            'texte' => 'Articles',
            'photo' => 'Galerie',
            'other' => 'Divers'
        ];
        return $mappings[$sous_type] ?? 'Général';
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
}