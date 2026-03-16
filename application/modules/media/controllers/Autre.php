<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur de gestion des médias divers (Autre)
 * Support: Liens, Livres/PDF, Textes, Photos, Fichiers divers
 * Architecture: Chunked upload, Génération miniatures, OCR, Preview
 */
class Autre extends MY_Controller {

    private $upload_dir;
    private $final_dir;
    private $thumbs_dir;
    private $chunk_size;
    private $max_file_size;
    private $allowed_extensions;
    private $session_timeout;

    function __construct()
    {
        parent::__construct();
        is_admin();
        
        // Configuration des chemins
        $this->upload_dir = FCPATH . 'uploads/temp/autre/';
        $this->final_dir = FCPATH . 'attachments/Autre/';
        $this->thumbs_dir = FCPATH . 'attachments/Autre/thumbs/';
        
        // Configuration technique (2MB chunks pour compatibilité)
        $this->chunk_size = 2 * 1024 * 1024;
        $this->max_file_size = 10 * 1024 * 1024 * 1024; // 10GB
        $this->session_timeout = 3600; // 1 heure
        
        // Types supportés avec leurs configurations
        $this->type_configs = [
            'link' => [
                'label' => 'Lien / URL',
                'icon' => 'bx-link',
                'color' => 'info',
                'accept' => null,
                'max_size' => 0
            ],
            'book' => [
                'label' => 'Livre / PDF',
                'icon' => 'bx-book',
                'color' => 'warning',
                'accept' => ['pdf', 'epub', 'mobi', 'azw', 'azw3'],
                'max_size' => 500 * 1024 * 1024, // 500MB
                'mime_types' => ['application/pdf', 'application/epub+zip']
            ],
            'texte' => [
                'label' => 'Texte',
                'icon' => 'bx-text',
                'color' => 'success',
                'accept' => null,
                'max_size' => 0
            ],
            'photo' => [
                'label' => 'Photo / Image',
                'icon' => 'bx-image',
                'color' => 'danger',
                'accept' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff'],
                'max_size' => 50 * 1024 * 1024, // 50MB
                'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']
            ],
            'other' => [
                'label' => 'Autre fichier',
                'icon' => 'bx-file',
                'color' => 'secondary',
                'accept' => '*',
                'max_size' => 2 * 1024 * 1024 * 1024, // 2GB
                'mime_types' => '*'
            ]
        ];
        
        // Création des dossiers
        $this->ensureDirectoryExists($this->upload_dir);
        $this->ensureDirectoryExists($this->final_dir);
        $this->ensureDirectoryExists($this->thumbs_dir);
        
        // Configuration PHP
        $this->configurePHP();
        
        // Nettoyage sessions
        $this->cleanupExpiredSessions();
    }

    // ==================== CONFIGURATION ====================

    private function configurePHP()
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '0');
        @ini_set('max_input_time', '0');
        @ini_set('upload_max_filesize', '10M');
        @ini_set('post_max_size', '10M');
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
        
        $dirs = glob($this->upload_dir . 'autre_*', GLOB_ONLYDIR);
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
        $data['items'] = $this->Model->read('galerie_medias', 
            ['type' => 'autre'], 
            'id_media', 
            'DESC'
        );
        
        $data['categories'] = $this->getExistingCategories();
        $data['stats'] = $this->calculateStats();
        $data['type_configs'] = $this->type_configs;
        
        $this->load->view('Autre_View', $data);
    }

    public function diagnostics()
    {
        $this->setJSONHeaders();
        
        $info = [
            'php_version' => PHP_VERSION,
            'tools' => [
                'ffmpeg' => $this->findFFmpeg() ? 'Disponible' : 'Non disponible',
                'imagemagick' => $this->findImageMagick() ? 'Disponible' : 'Non disponible',
                'gd' => extension_loaded('gd') ? 'Disponible' : 'Non disponible',
                'tesseract' => $this->findTesseract() ? 'Disponible (OCR)' : 'Non disponible'
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
                'types' => $this->type_configs
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

    // ==================== API UPLOAD CHUNKED ====================

    public function initUpload()
    {
        $this->setJSONHeaders();
        
        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');
        $file_hash = $this->input->post('file_hash') ?: null;
        $sous_type = $this->input->post('sous_type') ?: 'other';

        // Validation
        $validation = $this->validateInitUpload($file_name, $file_size, $sous_type);
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
            'sous_type' => $sous_type,
            'chunk_size' => $this->chunk_size,
            'total_chunks' => $total_chunks,
            'uploaded_chunks' => [],
            'failed_chunks' => [],
            'created_at' => time(),
            'last_activity' => time(),
            'status' => 'active'
        ];

        $this->saveMetadata($upload_id, $metadata);

        $this->jsonResponse(true, 'Session initialisée', [
            'upload_id' => $upload_id,
            'chunk_size' => $this->chunk_size,
            'total_chunks' => $total_chunks,
            'max_retries' => 3,
            'sous_type' => $sous_type
        ]);
    }

    public function uploadChunk()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $chunk_index = (int)$this->input->post('chunk_index');
        $chunk_hash = $this->input->post('chunk_hash') ?: null;

        if (empty($upload_id)) {
            $this->jsonResponse(false, 'ID upload manquant');
            return;
        }

        $metadata = $this->loadMetadata($upload_id);
        if (!$metadata) {
            $this->jsonResponse(false, 'Session invalide ou expirée');
            return;
        }

        if ($metadata['status'] !== 'active') {
            $this->jsonResponse(false, 'Session non active');
            return;
        }

        // Vérifier si chunk déjà présent (idempotence)
        $chunk_path = $this->getChunkPath($upload_id, $chunk_index);
        if (file_exists($chunk_path)) {
            $this->markChunkUploaded($upload_id, $chunk_index);
            $progress = $this->calculateProgress($upload_id);
            $this->jsonResponse(true, 'Chunk déjà présent', $progress);
            return;
        }

        if (empty($_FILES['chunk'])) {
            $this->jsonResponse(false, 'Aucun chunk reçu');
            return;
        }

        $file = $_FILES['chunk'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_detail = $this->getDetailedUploadError($file['error']);
            $this->logError("Upload error chunk $chunk_index", $error_detail);
            $this->jsonResponse(false, $error_detail['message'], [
                'error_code' => $file['error'],
                'error_type' => $error_detail['type']
            ]);
            return;
        }

        if ($file['size'] === 0) {
            $this->jsonResponse(false, 'Chunk vide reçu');
            return;
        }

        // Vérifier hash si fourni
        if ($chunk_hash && function_exists('hash_file')) {
            $calculated_hash = hash_file('crc32b', $file['tmp_name']);
            if ($calculated_hash !== $chunk_hash) {
                $this->jsonResponse(false, 'Corruption détectée');
                return;
            }
        }

        // Déplacer le chunk
        if (!@move_uploaded_file($file['tmp_name'], $chunk_path)) {
            if (!@copy($file['tmp_name'], $chunk_path)) {
                $this->jsonResponse(false, 'Erreur écriture disque');
                return;
            }
            @unlink($file['tmp_name']);
        }

        if (!file_exists($chunk_path) || filesize($chunk_path) !== $file['size']) {
            @unlink($chunk_path);
            $this->jsonResponse(false, 'Erreur vérification écriture');
            return;
        }

        $this->markChunkUploaded($upload_id, $chunk_index);
        $this->updateLastActivity($upload_id);
        
        $progress = $this->calculateProgress($upload_id);

        $this->jsonResponse(true, 'Chunk reçu', array_merge($progress, [
            'received_size' => $file['size']
        ]));
    }

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
        
        if ($actual_chunks !== $metadata['uploaded_chunks']) {
            $metadata['uploaded_chunks'] = $actual_chunks;
            $this->saveMetadata($upload_id, $metadata);
        }

        $missing = array_diff(
            range(0, $metadata['total_chunks'] - 1),
            $actual_chunks
        );

        $this->jsonResponse(true, 'Statut récupéré', [
            'upload_id' => $upload_id,
            'status' => $metadata['status'],
            'progress' => $this->calculateProgress($upload_id),
            'missing_chunks' => array_values($missing),
            'can_resume' => count($actual_chunks) > 0 && count($missing) > 0
        ]);
    }

    public function completeUpload()
    {
        $this->setJSONHeaders();
        
        $upload_id = $this->input->post('upload_id');
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
            $this->jsonResponse(false, 'Chunks manquants', [
                'missing_chunks' => array_values($missing)
            ]);
            return;
        }

        // Générer nom final
        $final_name = $this->generateFinalName($metadata['file_name']);
        $final_path = $this->final_dir . $final_name;
        $relative_path = 'attachments/Autre/' . $final_name;

        // Assembler les chunks
        $assembled = $this->assembleChunks($upload_id, $final_path, $metadata);
        
        if (!$assembled['success']) {
            $this->jsonResponse(false, 'Erreur assemblage: ' . $assembled['message']);
            return;
        }

        // Vérifier taille finale
        $final_size = filesize($final_path);
        if ($final_size !== $metadata['file_size']) {
            @unlink($final_path);
            $this->jsonResponse(false, 'Taille finale incorrecte');
            return;
        }

        // Traitement selon le type
        $sous_type = $metadata['sous_type'];
        $processing_result = $this->processFile($final_path, $final_name, $sous_type);

        // Nettoyer session
        $this->cleanupUploadSession($upload_id);

        $this->jsonResponse(true, 'Upload complété', [
            'file_path' => $relative_path,
            'file_name' => $final_name,
            'file_size' => $final_size,
            'file_size_formatted' => $this->formatBytes($final_size),
            'sous_type' => $sous_type,
            'miniature' => $processing_result['thumbnail'] ?? null,
            'preview_data' => $processing_result['preview'] ?? null,
            'dimensions' => $processing_result['dimensions'] ?? null,
            'mime_type' => mime_content_type($final_path)
        ]);
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

    // ==================== CRUD OPÉRATIONS ====================

    public function Create()
    {
        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $this->form_validation->set_rules('sous_type', 'Type', 'required');
        
        $sous_type = $this->input->post('sous_type');
        
        // Validation selon le type
        if ($sous_type === 'link') {
            $this->form_validation->set_rules('lien', 'Lien', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('autre'));
            return;
        }

        $data = $this->prepareItemData($sous_type, 'create');
        
        if (!$data) {
            redirect(base_url('autre'));
            return;
        }

        $rsp = $this->Model->create('galerie_medias', $data);

        $this->setFlashMessage($rsp, 'Élément créé avec succès.', 'Erreur lors de la création.');
        redirect(base_url('autre'));
    }

    public function Update()
    {
        $id = $this->input->post('id');
        $old = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        if (!$old) {
            $this->session->set_flashdata('error', 'Élément non trouvé.');
            redirect(base_url('autre'));
            return;
        }

        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        
        $sous_type = $old['sous_type']; // On garde le même type
        
        if ($sous_type === 'link') {
            $this->form_validation->set_rules('lien', 'Lien', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('autre'));
            return;
        }

        $data = $this->prepareItemData($sous_type, 'update', $old);
        
        if (!$data) {
            redirect(base_url('autre'));
            return;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);

        $this->setFlashMessage($rsp, 'Élément mis à jour avec succès.', 'Erreur lors de la mise à jour.');
        redirect(base_url('autre'));
    }

    public function Delete()
    {
        $id = $this->input->post('id');
        $item = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            'est_actif' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            if ($item) $this->deleteItemFiles($item);
            $this->session->set_flashdata('success', 'Élément supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        
        redirect(base_url('autre'));
    }

    public function ChangeStatus()
    {
        $id = $this->input->post('id');
        $est_actif = $this->input->post('est_actif');
        $status = ($est_actif == 1) ? 0 : 1;
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], ['est_actif' => $status]);

        $this->setFlashMessage($rsp, 'Statut mis à jour avec succès.', 'Erreur lors de la mise à jour du statut.');
        redirect(base_url('autre'));    
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

    // ==================== TRAITEMENT FICHIERS ====================

    private function processFile($file_path, $filename, $sous_type)
    {
        $result = [
            'thumbnail' => null,
            'preview' => null,
            'dimensions' => null
        ];

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $relative_path = 'attachments/Autre/' . $filename;

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
                $result['thumbnail'] = $this->getIconForFile($ext);
                break;
        }

        return $result;
    }

    private function processImage($file_path, $filename)
    {
        $result = ['thumbnail' => null, 'dimensions' => null, 'preview' => null];
        
        // Dimensions
        $dims = @getimagesize($file_path);
        if ($dims) {
            $result['dimensions'] = ['width' => $dims[0], 'height' => $dims[1]];
        }

        // Générer miniature
        $thumb_name = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumb_path = $this->thumbs_dir . $thumb_name;
        
        if ($this->createThumbnail($file_path, $thumb_path, 400, 300)) {
            $result['thumbnail'] = 'attachments/Autre/thumbs/' . $thumb_name;
        }

        // Générer preview WebP si grande image
        if ($dims && ($dims[0] > 1920 || $dims[1] > 1080)) {
            $preview_name = pathinfo($filename, PATHINFO_FILENAME) . '_preview.webp';
            $preview_path = $this->thumbs_dir . $preview_name;
            if ($this->createWebPPreview($file_path, $preview_path, 1920, 1080)) {
                $result['preview'] = 'attachments/Autre/thumbs/' . $preview_name;
            }
        }

        return $result;
    }

    private function processPDF($file_path, $filename)
    {
        $result = ['thumbnail' => null, 'preview' => null, 'pages' => null];
        
        // Extraire nombre de pages si possible
        $result['pages'] = $this->getPDFPages($file_path);

        // Générer miniature première page
        $thumb_name = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumb_path = $this->thumbs_dir . $thumb_name;
        
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
                $result['thumbnail'] = 'attachments/Autre/thumbs/' . $thumb_name;
            }
        }

        if (!$result['thumbnail']) {
            $result['thumbnail'] = 'assets/images/pdf-default.png';
        }

        return $result;
    }

    private function createThumbnail($source, $dest, $max_width, $max_height)
    {
        if (!extension_loaded('gd')) return false;

        $info = getimagesize($source);
        if (!$info) return false;

        list($width, $height, $type) = $info;
        
        // Calculer nouvelles dimensions
        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = (int)($width * $ratio);
        $new_height = (int)($height * $ratio);

        // Créer image source
        switch ($type) {
            case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($source); break;
            case IMAGETYPE_PNG: $src = imagecreatefrompng($source); break;
            case IMAGETYPE_GIF: $src = imagecreatefromgif($source); break;
            case IMAGETYPE_WEBP: $src = imagecreatefromwebp($source); break;
            default: return false;
        }

        if (!$src) return false;

        // Créer miniature
        $dst = imagecreatetruecolor($new_width, $new_height);
        
        // Gérer transparence pour PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        
        $success = imagejpeg($dst, $dest, 85);
        
        imagedestroy($src);
        imagedestroy($dst);
        
        return $success;
    }

    private function createWebPPreview($source, $dest, $max_width, $max_height)
    {
        if (!function_exists('imagewebp')) return false;

        $info = getimagesize($source);
        if (!$info) return false;

        list($width, $height, $type) = $info;
        
        $ratio = min($max_width / $width, $max_height / $height, 1);
        $new_width = (int)($width * $ratio);
        $new_height = (int)($height * $ratio);

        switch ($type) {
            case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($source); break;
            case IMAGETYPE_PNG: $src = imagecreatefrompng($source); break;
            case IMAGETYPE_WEBP: $src = imagecreatefromwebp($source); break;
            default: return false;
        }

        $dst = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        
        $success = imagewebp($dst, $dest, 80);
        
        imagedestroy($src);
        imagedestroy($dst);
        
        return $success;
    }

    private function getPDFPages($file)
    {
        // Méthode rapide: compter /Type /Page
        $content = file_get_contents($file, false, null, 0, 50000);
        if (preg_match('/\/Type\s*\/Pages.*?\/Count\s+(\d+)/s', $content, $m)) {
            return (int)$m[1];
        }
        if (preg_match_all('/\/Type\s*\/Page[^s]/', $content, $m)) {
            return count($m[0]);
        }
        return null;
    }

    private function getIconForFile($ext)
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
            'rar' => 'assets/images/zip-default.png',
            'mp3' => 'assets/images/audio-default.png',
            'mp4' => 'assets/images/video-default.png',
        ];
        return $icons[$ext] ?? 'assets/images/file-default.png';
    }

    // ==================== HELPERS PRIVÉS ====================

    private function validateInitUpload($file_name, $file_size, $sous_type)
    {
        if (empty($file_name) || $file_size <= 0) {
            return ['success' => false, 'message' => 'Paramètres invalides'];
        }

        if (!isset($this->type_configs[$sous_type])) {
            return ['success' => false, 'message' => 'Type non supporté'];
        }

        $config = $this->type_configs[$sous_type];
        
        if ($config['max_size'] > 0 && $file_size > $config['max_size']) {
            return [
                'success' => false, 
                'message' => 'Fichier trop grand pour ce type (max: ' . $this->formatBytes($config['max_size']) . ')'
            ];
        }

        // Vérifier extension
        if ($config['accept'] !== '*' && $config['accept'] !== null) {
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($ext, $config['accept'])) {
                return ['success' => false, 'message' => 'Format non supporté pour ce type'];
            }
        }

        return ['success' => true];
    }

    private function prepareItemData($sous_type, $mode, $old = null)
    {
        $data = [
            'titre' => $this->input->post('titre'),
            'type' => 'autre',
            'sous_type' => $sous_type,
            'description' => $this->input->post('description') ?: null,
            'categorie' => $this->input->post('categorie') ?: null,
            'date_media' => $this->input->post('date_media') ?: null,
            'credits' => $this->input->post('credits') ?: null,
            'est_actif' => $this->input->post('est_actif') ? 1 : ($mode === 'create' ? 1 : ($old['est_actif'] ?? 1)),
            'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
            'message_reseaux' => $this->input->post('message_reseaux') ?: null,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : ($mode === 'create' ? 1 : 0),
            'id_page_associee' => $this->input->post('id_page_associee') ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($mode === 'create') {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        // Gestion selon le type
        switch ($sous_type) {
            case 'link':
                $data['lien'] = $this->input->post('lien');
                $data['miniature'] = $this->extractLinkThumbnail($data['lien']);
                break;
                
            case 'texte':
                $data['contenu_texte'] = $this->input->post('contenu_texte') ?: null;
                $data['miniature'] = 'assets/images/text-default.png';
                break;
                
            default: // book, photo, other
                $file_path = $this->input->post('uploaded_file_path');
                
                if ($mode === 'update' && empty($file_path) && $old && !empty($old['fichier'])) {
                    // Garder l'ancien fichier
                    $data['fichier'] = $old['fichier'];
                    $data['taille'] = $old['taille'];
                    $data['mime_type'] = $old['mime_type'];
                    $data['miniature'] = $old['miniature'];
                } elseif (!empty($file_path) && file_exists(FCPATH . $file_path)) {
                    // Supprimer ancien si update
                    if ($mode === 'update' && $old && !empty($old['fichier'])) {
                        $this->deleteItemFiles($old);
                    }
                    
                    $full_path = FCPATH . $file_path;
                    $data['fichier'] = $file_path;
                    $data['taille'] = filesize($full_path);
                    $data['mime_type'] = mime_content_type($full_path);
                    $data['lien'] = null;
                    $data['miniature'] = $this->input->post('miniature') ?: $this->getIconForFile(
                        pathinfo($file_path, PATHINFO_EXTENSION)
                    );
                } elseif ($mode === 'create') {
                    $this->session->set_flashdata('error', 'Aucun fichier uploadé.');
                    return false;
                }
                break;
        }

        return $data;
    }

    private function deleteItemFiles($item)
    {
        if (!empty($item['fichier']) && file_exists(FCPATH . $item['fichier'])) {
            @unlink(FCPATH . $item['fichier']);
        }
        if (!empty($item['miniature']) && strpos($item['miniature'], 'http') !== 0 && file_exists(FCPATH . $item['miniature'])) {
            @unlink(FCPATH . $item['miniature']);
        }
    }

    private function extractLinkThumbnail($url)
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return "https://vumbnail.com/{$m[1]}.jpg";
        }
        
        // Favicon générique
        $domain = parse_url($url, PHP_URL_HOST);
        if ($domain) {
            return "https://www.google.com/s2/favicons?domain={$domain}&sz=128";
        }
        
        return 'assets/images/link-default.png';
    }

    private function getExistingCategories()
    {
        $this->db->select('DISTINCT(categorie) as cat');
        $this->db->where('type', 'autre');
        $this->db->where('categorie IS NOT NULL');
        $this->db->where('categorie !=', '');
        $query = $this->db->get('galerie_medias');
        
        $categories = [];
        foreach ($query->result() as $row) {
            $categories[] = $row->cat;
        }
        return $categories;
    }

    private function calculateStats()
    {
        $stats = [
            'total' => 0,
            'by_type' => [],
            'total_size' => 0
        ];

        foreach ($this->type_configs as $key => $config) {
            $stats['by_type'][$key] = 0;
        }

        $items = $this->Model->read('galerie_medias', ['type' => 'autre', 'est_actif' => 1]);
        
        foreach ($items as $item) {
            $stats['total']++;
            $type = $item['sous_type'] ?? 'other';
            if (isset($stats['by_type'][$type])) {
                $stats['by_type'][$type]++;
            }
            $stats['total_size'] += $item['taille'] ?? 0;
        }

        return $stats;
    }

    // ==================== UTILITAIRES ====================

    private function generateUploadId()
    {
        return 'autre_' . uniqid() . '_' . bin2hex(random_bytes(8));
    }

    private function generateFinalName($original_name)
    {
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        return date("YmdHis") . '_' . uniqid() . '.' . $ext;
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
        return json_decode(file_get_contents($path), true);
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

    private function logError($context, $details)
    {
        $log = date('Y-m-d H:i:s') . " | $context | " . json_encode($details) . "\n";
        $log_dir = FCPATH . 'logs/';
        if (!is_dir($log_dir)) @mkdir($log_dir, 0777, true);
        error_log($log, 3, $log_dir . 'autre_upload_errors.log');
    }

    private function getDetailedUploadError($code)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => ['type' => 'PHP_LIMIT', 'message' => 'Fichier trop grand (' . ini_get('upload_max_filesize') . ')'],
            UPLOAD_ERR_FORM_SIZE => ['type' => 'FORM_LIMIT', 'message' => 'Fichier trop grand (formulaire)'],
            UPLOAD_ERR_PARTIAL => ['type' => 'NETWORK', 'message' => 'Upload partiel'],
            UPLOAD_ERR_NO_FILE => ['type' => 'NO_FILE', 'message' => 'Aucun fichier'],
            UPLOAD_ERR_NO_TMP_DIR => ['type' => 'SERVER', 'message' => 'Dossier temporaire manquant'],
            UPLOAD_ERR_CANT_WRITE => ['type' => 'DISK', 'message' => 'Erreur écriture disque'],
            UPLOAD_ERR_EXTENSION => ['type' => 'EXT', 'message' => 'Extension bloquée']
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

    // ==================== OUTILS EXTERNES ====================

    private function findFFmpeg()
    {
        $paths = ['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', 'C:\\ffmpeg\\bin\\ffmpeg.exe'];
        foreach ($paths as $p) {
            exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    private function findImageMagick()
    {
        $paths = ['convert', '/usr/bin/convert', '/usr/local/bin/convert'];
        foreach ($paths as $p) {
            exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    private function findTesseract()
    {
        $paths = ['tesseract', '/usr/bin/tesseract', '/usr/local/bin/tesseract'];
        foreach ($paths as $p) {
            exec($p . ' --version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }
}