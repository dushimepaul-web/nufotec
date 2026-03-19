<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Actualites Controller - Gestion des actualités et blog
 * Version: 5.0 - Avec upload image style vidéo
 */
class Actualites extends MX_Controller {

    private $paths;
    private $upload_config;
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
        $this->load->helper('url');
        $this->load->helper('text');
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
            'temp'       => $base . 'uploads/temp/actualites/',
            'images'     => $base . 'attachments/Actualites/Images/',
            'thumbnails' => $base . 'attachments/Actualites/Thumbnails/',
            'custom'     => $base . 'attachments/Actualites/Thumbnails/Custom/',
        ];
    }

    private function initializeConfig()
    {
        $this->upload_config = [
            'chunk_size'    => 5 * 1024 * 1024,  // 5MB chunks
            'max_file_size' => 50 * 1024 * 1024, // 50MB max pour images
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
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
        $actualites = $this->Model->read('actualites_blog', [], 'id_actualite', 'DESC');
        
        // Formater les données
        foreach ($actualites as &$item) {
            $item['date_formatee'] = !empty($item['date_publication']) 
                ? date('d/m/Y H:i', strtotime($item['date_publication'])) 
                : '-';
            $item['statut_badge'] = empty($item['deleted_at']) 
                ? '<span class="badge bg-success">Publié</span>' 
                : '<span class="badge bg-secondary">Archivé</span>';
        }

        $data = [
            'actualites' => $actualites,
            'categories' => $this->getExistingCategories(),
            'stats'      => $this->calculateStats(),
            'total_vues' => array_sum(array_column($actualites, 'vues')),
        ];
        
        $this->load->view('Actualites_View', $data);
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
        
        if ($file_size > $this->upload_config['max_file_size']) {
            echo json_encode(['success' => false, 'message' => 'Fichier trop grand (max 50MB)']);
            return;
        }
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->upload_config['allowed_extensions'])) {
            echo json_encode(['success' => false, 'message' => 'Format non supporté: ' . $ext]);
            return;
        }

        // Créer session upload
        $upload_id = 'actu_' . uniqid() . '_' . bin2hex(random_bytes(4));
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
        $safe_name     = $this->createSlug(pathinfo($metadata['file_name'], PATHINFO_FILENAME));
        $original_name = date('YmdHis') . '_' . $safe_name . '.' . pathinfo($metadata['file_name'], PATHINFO_EXTENSION);
        $original_path = $this->paths['images'] . $original_name;
        
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

        // Traitement image
        $processing = $this->processImage($original_path, $original_name);

        // Suggérer un titre depuis le nom du fichier
        $suggested_title = $this->suggestTitle($metadata['file_name']);
        
        // Récupérer les dimensions
        $dimensions = null;
        $img_info = @getimagesize($original_path);
        if ($img_info) {
            $dimensions = $img_info[0] . 'x' . $img_info[1];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Upload complété',
            'data'    => [
                'original_file'  => 'attachments/Actualites/Images/' . $original_name,
                'file_name'      => $original_name,
                'file_size'      => $this->formatBytes(filesize($original_path)),
                'dimensions'     => $dimensions,
                'thumbnails'     => !empty($processing['thumbnail']) ? ['generated' => $processing['thumbnail']] : [],
                'form_suggestions' => [
                    'titre'     => $suggested_title,
                    'slug'      => $safe_name,
                    'categorie' => 'Actualités'
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
        $valid_ext = ['gif', 'jpg', 'png', 'jpeg', 'webp'];

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
            $this->resizeThumbnail($destination, 1200, 800);
        }

        $relative_path = 'attachments/Actualites/Thumbnails/Custom/' . $final_filename;
        
        echo json_encode([
            'success' => true,
            'message' => 'Miniature uploadée avec succès',
            'file_path' => $relative_path,
            'file_name' => $final_filename,
            'preview_url' => base_url($relative_path)
        ]);
        return;
    }

    // ==================== UPLOAD IMAGE POUR MODAL UPDATE ====================

    public function uploadUpdateImage()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        if (empty($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'success' => false, 
                'message' => 'Aucun fichier reçu'
            ]);
            return;
        }

        $file = $_FILES['image_file'];
        
        // Validation
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $valid_ext = ['gif', 'jpg', 'png', 'jpeg', 'webp'];

        if (!in_array($file_extension, $valid_ext)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Format non supporté'
            ]);
            return;
        }

        // Créer dossier si nécessaire
        if (!is_dir($this->paths['images'])) {
            mkdir($this->paths['images'], 0777, TRUE);
        }

        // Générer nom unique
        $code = date("YmdHis") . '_' . uniqid();
        $final_filename = $code . "." . $file_extension;
        $destination = $this->paths['images'] . $final_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Erreur lors du déplacement'
            ]);
            return;
        }

        // Générer miniature
        $thumb_name = pathinfo($final_filename, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumb_path = $this->paths['thumbnails'] . $thumb_name;
        
        if ($this->createThumbnail($destination, $thumb_path, 400, 300)) {
            // Miniature générée avec succès
        }

        $relative_path = 'attachments/Actualites/Images/' . $final_filename;
        
        echo json_encode([
            'success' => true,
            'message' => 'Image uploadée avec succès',
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
            case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($file_path); break;
            case IMAGETYPE_PNG: $src = @imagecreatefrompng($file_path); break;
            case IMAGETYPE_GIF: $src = @imagecreatefromgif($file_path); break;
            case IMAGETYPE_WEBP: $src = @imagecreatefromwebp($file_path); break;
            default: return;
        }

        if (!$src) return;

        $dst = imagecreatetruecolor($new_width, $new_height);
        
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        switch ($type) {
            case IMAGETYPE_JPEG: imagejpeg($dst, $file_path, 90); break;
            case IMAGETYPE_PNG: imagepng($dst, $file_path, 6); break;
            case IMAGETYPE_GIF: imagegif($dst, $file_path); break;
            case IMAGETYPE_WEBP: imagewebp($dst, $file_path, 90); break;
        }

        imagedestroy($src);
        imagedestroy($dst);
    }

    // ==================== TRAITEMENT IMAGE ====================

    private function processImage($source, $filename)
    {
        $result = ['thumbnail' => null];
        
        // Créer miniature
        $thumb_name = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumb_path = $this->paths['thumbnails'] . $thumb_name;
        
        if ($this->createThumbnail($source, $thumb_path, 400, 300)) {
            $result['thumbnail'] = 'attachments/Actualites/Thumbnails/' . $thumb_name;
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

    // ==================== CRUD ====================

    public function Create()
    {
        $this->_csrf_off();
        
        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('slug', 'Slug', 'required|trim|max_length[255]|is_unique[actualites_blog.slug]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Actualites'));
            return;
        }

        $auto_data = json_decode($this->input->post('auto_detected_data') ?: '{}', true);
        
        // Gestion tags JSON
        $tags = $this->input->post('tags');
        $tags_json = null;
        if (!empty($tags)) {
            $tags_array = array_map('trim', explode(',', $tags));
            $tags_json = json_encode($tags_array);
        }

        // Récupérer l'image uploadée
        $uploaded_file = $this->input->post('uploaded_file_path');
        $thumbnail = $this->input->post('thumbnail');
        
        // Priorité: thumbnail personnalisé > image uploadée > auto_data
        $image_principale = $thumbnail ?: $uploaded_file;
        if (empty($image_principale) && !empty($auto_data['thumbnails'])) {
            $thumbnails = $auto_data['thumbnails'];
            if (is_array($thumbnails) && !empty($thumbnails['generated'])) {
                $image_principale = $thumbnails['generated'];
            }
        }

        $data = [
            'titre'             => $this->input->post('titre'),
            'slug'              => $this->input->post('slug'),
            'resume'            => $this->input->post('resume'),
            'contenu'           => $this->input->post('contenu'),
            'image_principale'  => $image_principale,
            'auteur'            => $this->input->post('auteur') ?: 'Admin',
            'date_publication'  => $this->input->post('date_publication') ?: date('Y-m-d H:i:s'),
            'categorie'         => $this->input->post('categorie'),
            'tags'              => $tags_json,
            'est_en_avant'      => $this->input->post('est_en_avant') ? 1 : 0,
            'for_subscriber'    => $this->input->post('for_subscriber') ? 1 : 0,
            'in_socialmedia'    => $this->input->post('in_socialmedia') ? 1 : 0,
            'id_page_associee'  => $this->input->post('id_page_associee') ?: null,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        // SUPPRIMEZ LE var_dump et die() qui bloquaient l'insertion
        // var_dump($data);
        // die();

        $rsp = $this->Model->create('actualites_blog', $data);
        
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Actualité créée avec succès' : 'Erreur création');
        redirect(base_url('Actualites'));
    }

    public function Update()
    {
        $this->_csrf_off();
        
        $id = $this->input->post('id');
        $current = $this->Model->readOne('actualites_blog', ['id_actualite' => $id]);
        
        if (!$current) {
            $this->session->set_flashdata('error', 'Actualité non trouvée');
            redirect(base_url('Actualites'));
            return;
        }

        $this->form_validation->set_rules('titre', 'Titre', 'required|trim|max_length[255]');
        
        // Vérifier unicité slug si changé
        if ($this->input->post('slug') !== $current['slug']) {
            $this->form_validation->set_rules('slug', 'Slug', 'required|trim|max_length[255]|is_unique[actualites_blog.slug]');
        }
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Actualites'));
            return;
        }

        // Gestion tags JSON
        $tags = $this->input->post('tags');
        $tags_json = $current['tags'];
        if (!empty($tags)) {
            $tags_array = array_map('trim', explode(',', $tags));
            $tags_json = json_encode($tags_array);
        }

        $data = [
            'titre'             => $this->input->post('titre'),
            'slug'              => $this->input->post('slug'),
            'resume'            => $this->input->post('resume'),
            'contenu'           => $this->input->post('contenu'),
            'auteur'            => $this->input->post('auteur'),
            'date_publication'  => $this->input->post('date_publication'),
            'categorie'         => $this->input->post('categorie'),
            'tags'              => $tags_json,
            'est_en_avant'      => $this->input->post('est_en_avant') ? 1 : 0,
            'for_subscriber'    => $this->input->post('for_subscriber') ? 1 : 0,
            'in_socialmedia'    => $this->input->post('in_socialmedia') ? 1 : 0,
            'id_page_associee'  => $this->input->post('id_page_associee') ?: null,
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        // Gestion image modifiée - IMPORTANT
        $new_image = $this->input->post('image_principale');
        if (!empty($new_image) && $new_image !== ($current['image_principale'] ?? '')) {
            // Supprimer ancienne image si existe
            if (!empty($current['image_principale']) && strpos($current['image_principale'], 'http') !== 0) {
                $old_image_path = FCPATH . $current['image_principale'];
                if (file_exists($old_image_path)) {
                    @unlink($old_image_path);
                }
                
                // Supprimer aussi la miniature associée
                $old_thumb_path = str_replace('Images/', 'Thumbnails/', $old_image_path);
                $old_thumb_path = str_replace(pathinfo($old_image_path, PATHINFO_EXTENSION), 'jpg', $old_thumb_path);
                if (file_exists($old_thumb_path)) {
                    @unlink($old_thumb_path);
                }
            }
            $data['image_principale'] = $new_image;
        }

        $rsp = $this->Model->update('actualites_blog', ['id_actualite' => $id], $data);
        
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Actualité mise à jour' : 'Erreur mise à jour');
        redirect(base_url('Actualites'));
    }

    public function Delete()
    {
        $id = $this->input->post('id');
        $permanent = $this->input->post('permanent');
        $item = $this->Model->readOne('actualites_blog', ['id_actualite' => $id]);
        
        if ($item) {
            if ($permanent) {
                // Suppression définitive
                $this->deleteFiles($item);
                $rsp = $this->Model->delete('actualites_blog', ['id_actualite' => $id]);
                $message = 'Article supprimé définitivement';
            } else {
                // Soft delete
                $rsp = $this->Model->update('actualites_blog', ['id_actualite' => $id], [
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $message = 'Article archivé';
            }
            
            $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? $message : 'Erreur');
        }
        
        redirect(base_url('Actualites'));
    }

    public function Restore()
    {
        $id = $this->input->post('id');
        
        $rsp = $this->Model->update('actualites_blog', ['id_actualite' => $id], [
            'deleted_at' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->session->set_flashdata($rsp ? 'success' : 'error', $rsp ? 'Actualité restaurée' : 'Erreur restauration');
        redirect(base_url('Actualites'));
    }

    public function toggleField()
    {
        $this->_csrf_off();
        $this->output->set_content_type('application/json');
        
        $id    = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');
        
        $allowed = ['est_en_avant', 'for_subscriber', 'in_socialmedia'];
        if (!in_array($field, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Champ non autorisé']);
            return;
        }
        
        $rsp = $this->Model->update('actualites_blog', ['id_actualite' => $id], [
            $field       => $value,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode(['success' => (bool)$rsp]);
        return;
    }

    // ==================== HELPERS ====================

    private function deleteFiles($item)
    {
        if (!empty($item['image_principale']) && strpos($item['image_principale'], 'http') !== 0) {
            $full_path = FCPATH . $item['image_principale'];
            if (file_exists($full_path)) {
                @unlink($full_path);
            }
            
            // Supprimer aussi la miniature
            $thumb_path = str_replace('Images/', 'Thumbnails/', $full_path);
            $thumb_path = str_replace(pathinfo($full_path, PATHINFO_EXTENSION), 'jpg', $thumb_path);
            if (file_exists($thumb_path)) {
                @unlink($thumb_path);
            }
        }
    }

    private function getExistingCategories()
    {
        $this->db->select('DISTINCT(categorie) as cat');
        $this->db->where('categorie IS NOT NULL');
        $this->db->where('categorie !=', '');
        $this->db->where('deleted_at', null);
        return array_filter(array_column($this->db->get('actualites_blog')->result_array(), 'cat'));
    }

    private function calculateStats()
    {
        $stats = [
            'total'          => 0,
            'publiees'       => 0,
            'archivees'      => 0,
            'en_avant'       => 0,
            'for_subscriber' => 0,
            'in_socialmedia' => 0
        ];

        $items = $this->Model->read('actualites_blog', []);
        
        foreach ($items as $item) {
            $stats['total']++;
            if (empty($item['deleted_at'])) {
                $stats['publiees']++;
                if (!empty($item['est_en_avant'])) $stats['en_avant']++;
                if (!empty($item['for_subscriber'])) $stats['for_subscriber']++;
                if (!empty($item['in_socialmedia'])) $stats['in_socialmedia']++;
            } else {
                $stats['archivees']++;
            }
        }

        return $stats;
    }

    private function createSlug($string)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
        return substr($slug, 0, 200);
    }

    private function suggestTitle($filename)
    {
        // Enlever l'extension
        $name = pathinfo($filename, PATHINFO_FILENAME);
        // Remplacer les tirets et underscores par des espaces
        $name = preg_replace('/[-_]/', ' ', $name);
        // Supprimer les caractères spéciaux
        $name = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        // Capitaliser chaque mot
        $name = ucwords(strtolower(trim($name)));
        return $name ?: 'Nouvel article';
    }

    private function formatBytes($bytes)
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }



    // ==================== AFFICHAGE PUBLIC D'UN ARTICLE ====================

public function view($slug)
{
    // Récupérer l'article par son slug
    $article = $this->Model->readOne('actualites_blog', [
        'slug' => $slug,
        'deleted_at' => null
    ]);
    
    if (!$article) {
        show_404();
        return;
    }
    
    
    
    // Récupérer les articles récents (pour sidebar)
    $recent_articles = $this->Model->read('actualites_blog', [
        'deleted_at' => null,
        'id_actualite !=' => $article['id_actualite']
    ], 'date_publication', 'DESC', 5);
    
    // Formater les tags
    $article['tags_array'] = [];
    if (!empty($article['tags'])) {
        $article['tags_array'] = json_decode($article['tags'], true) ?: [];
    }
    
    
    
    $data = [
        'article' => $article,
        'recent_articles' => $recent_articles,
        'meta_title' => $article['titre'],
        'meta_description' => $article['resume'] ?: substr(strip_tags($article['contenu']), 0, 160),
        'meta_image' => !empty($article['image_principale']) ? base_url($article['image_principale']) : base_url('assets/images/default-article.jpg')
    ];
    
    // Charger la vue publique
    $this->load->view('Actualites_Detail_View', $data);
}
}