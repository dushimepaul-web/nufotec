<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur Autre - Gestion des médias divers
 * Upload Chunked 1MB, Multi-types, Thumbnails auto
 * Version: 3.0 - Stable et optimisé
 */
class Autre extends MY_Controller {

    private $upload_dir;
    private $final_dir;
    private $thumbs_dir;
    private $chunk_size = 1048576; // 1MB exact
    private $max_file_size = 10737418240; // 10GB

    // Configuration des types supportés
    private $type_configs = [
        'link' => [
            'label' => 'Lien / URL',
            'icon' => 'bx-link',
            'color' => 'info',
            'accept' => null,
            'max_size' => 0,
            'has_file' => false
        ],
        'book' => [
            'label' => 'Livre / PDF',
            'icon' => 'bx-book',
            'color' => 'warning',
            'accept' => ['pdf', 'epub', 'mobi'],
            'max_size' => 524288000, // 500MB
            'has_file' => true
        ],
        'texte' => [
            'label' => 'Texte',
            'icon' => 'bx-text',
            'color' => 'success',
            'accept' => null,
            'max_size' => 0,
            'has_file' => false
        ],
        'photo' => [
            'label' => 'Photo / Image',
            'icon' => 'bx-image',
            'color' => 'danger',
            'accept' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'max_size' => 52428800, // 50MB
            'has_file' => true
        ],
        'other' => [
            'label' => 'Autre fichier',
            'icon' => 'bx-file',
            'color' => 'secondary',
            'accept' => '*',
            'max_size' => 2147483648, // 2GB
            'has_file' => true
        ]
    ];

    function __construct()
    {
        parent::__construct();
        is_admin();
        
        // Chemins
        $this->upload_dir = FCPATH . 'uploads/temp/autre/';
        $this->final_dir = FCPATH . 'attachments/Autre/';
        $this->thumbs_dir = FCPATH . 'attachments/Autre/thumbs/';
        // Charger le helper format
        $this->load->helper('format');
        
        // Créer les dossiers
        foreach ([$this->upload_dir, $this->final_dir, $this->thumbs_dir] as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, TRUE);
                @chmod($dir, 0777);
            }
        }
        
        // Config PHP
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '0');
        @ini_set('max_input_time', '0');
        
        // Nettoyage anciennes sessions
        $this->cleanupSessions();
    }

    // ==================== VUES ====================

   // Dans Autre.php, méthode index(), ajoutez :
public function index()
{
    $items = $this->Model->read('galerie_medias', 
        ['type' => 'autre'], 
        'id_media', 
        'DESC'
    );
    
    // Formater les tailles ici
    foreach ($items as &$item) {
        if (!empty($item['taille'])) {
            $item['taille_formatee'] = $this->formatBytes($item['taille']);
        } else {
            $item['taille_formatee'] = '-';
        }
    }
    
    $data['items'] = $items;
    $data['categories'] = $this->getCategories();
    $data['stats'] = $this->getStats();
    $data['type_configs'] = $this->type_configs;
    
    $this->load->view('Autre_View', $data);
}

    // ==================== API UPLOAD ====================

    /**
     * Étape 1: Initialiser l'upload
     */
    public function initUpload()
    {
        $this->jsonHeaders();
        
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
                    'message' => 'Format non supporté. Types: ' . implode(', ', $config['accept'])
                ]);
                return;
            }
        }

        // Créer session
        $upload_id = 'autre_' . uniqid() . '_' . bin2hex(random_bytes(4));
        $temp_dir = $this->upload_dir . $upload_id . '/';
        
        if (!@mkdir($temp_dir, 0777, TRUE)) {
            echo json_encode(['success' => false, 'message' => 'Erreur création dossier temporaire']);
            return;
        }

        $total_chunks = (int)ceil($file_size / $this->chunk_size);
        
        $metadata = [
            'upload_id' => $upload_id,
            'file_name' => $file_name,
            'file_size' => $file_size,
            'sous_type' => $sous_type,
            'total_chunks' => $total_chunks,
            'uploaded_chunks' => [],
            'created_at' => time()
        ];

        file_put_contents($temp_dir . 'metadata.json', json_encode($metadata));

        echo json_encode([
            'success' => true,
            'upload_id' => $upload_id,
            'chunk_size' => $this->chunk_size,
            'total_chunks' => $total_chunks
        ]);
    }

    /**
     * Étape 2: Recevoir un chunk
     */
    public function uploadChunk()
    {
        $this->jsonHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $chunk_index = (int)$this->input->post('chunk_index');

        if (empty($upload_id)) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            return;
        }

        $temp_dir = $this->upload_dir . $upload_id . '/';
        $metadata_file = $temp_dir . 'metadata.json';
        
        if (!file_exists($metadata_file)) {
            echo json_encode(['success' => false, 'message' => 'Session expirée']);
            return;
        }

        $metadata = json_decode(file_get_contents($metadata_file), true);
        
        // Chunk déjà présent ?
        $chunk_path = $temp_dir . 'chunk_' . $chunk_index;
        if (file_exists($chunk_path)) {
            if (!in_array($chunk_index, $metadata['uploaded_chunks'])) {
                $metadata['uploaded_chunks'][] = $chunk_index;
                sort($metadata['uploaded_chunks']);
                file_put_contents($metadata_file, json_encode($metadata));
            }
            
            $uploaded = count($metadata['uploaded_chunks']);
            $total = $metadata['total_chunks'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Chunk déjà présent',
                'uploaded_chunks' => $uploaded,
                'total_chunks' => $total,
                'percent' => round(($uploaded / $total) * 100, 2)
            ]);
            return;
        }

        // Vérifier fichier reçu
        if (empty($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Erreur réception chunk']);
            return;
        }

        // Sauvegarder chunk
        if (!@move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path)) {
            echo json_encode(['success' => false, 'message' => 'Erreur écriture disque']);
            return;
        }

        // Mettre à jour metadata
        $metadata['uploaded_chunks'][] = $chunk_index;
        sort($metadata['uploaded_chunks']);
        file_put_contents($metadata_file, json_encode($metadata));

        $uploaded = count($metadata['uploaded_chunks']);
        $total = $metadata['total_chunks'];

        echo json_encode([
            'success' => true,
            'uploaded_chunks' => $uploaded,
            'total_chunks' => $total,
            'percent' => round(($uploaded / $total) * 100, 2)
        ]);
    }

    /**
     * Étape 3: Finaliser l'upload
     */
    public function completeUpload()
    {
        $this->jsonHeaders();
        
        $upload_id = $this->input->post('upload_id');
        $temp_dir = $this->upload_dir . $upload_id . '/';
        $metadata_file = $temp_dir . 'metadata.json';
        
        if (!file_exists($metadata_file)) {
            echo json_encode(['success' => false, 'message' => 'Session non trouvée']);
            return;
        }

        $metadata = json_decode(file_get_contents($metadata_file), true);
        
        // Vérifier tous les chunks
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

        // Assembler le fichier
        $final_name = date('YmdHis') . '_' . uniqid() . '_' . $this->sanitizeFilename($metadata['file_name']);
        $final_path = $this->final_dir . $final_name;
        
        $out = fopen($final_path, 'wb');
        if (!$out) {
            echo json_encode(['success' => false, 'message' => 'Impossible de créer le fichier final']);
            return;
        }

        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            $chunk_file = $temp_dir . 'chunk_' . $i;
            fwrite($out, file_get_contents($chunk_file));
            unlink($chunk_file);
        }
        
        fclose($out);

        // Vérifier taille finale
        if (filesize($final_path) !== $metadata['file_size']) {
            unlink($final_path);
            echo json_encode(['success' => false, 'message' => 'Erreur vérification taille']);
            return;
        }

        // Traiter selon le type
        $sous_type = $metadata['sous_type'];
        $result = $this->processFile($final_path, $final_name, $sous_type);

        // Nettoyer
        @unlink($metadata_file);
        @rmdir($temp_dir);

        echo json_encode([
            'success' => true,
            'file_path' => 'attachments/Autre/' . $final_name,
            'file_name' => $final_name,
            'file_size' => $metadata['file_size'],
            'file_size_formatted' => $this->formatBytes($metadata['file_size']),
            'sous_type' => $sous_type,
            'miniature' => $result['miniature'],
            'dimensions' => $result['dimensions'] ?? null,
            'pages' => $result['pages'] ?? null
        ]);
    }

    /**
     * Annuler un upload
     */
    public function cancelUpload()
    {
        $this->jsonHeaders();
        
        $upload_id = $this->input->post('upload_id');
        if ($upload_id) {
            $this->cleanupUpload($upload_id);
        }
        
        echo json_encode(['success' => true]);
    }

    // ==================== CRUD ====================

    public function Create()
    {
        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $this->form_validation->set_rules('sous_type', 'Type', 'required');
        
        $sous_type = $this->input->post('sous_type');
        
        // Validation selon type
        if ($sous_type === 'link') {
            $this->form_validation->set_rules('lien', 'Lien', 'required|valid_url');
        } elseif ($sous_type !== 'texte') {
            // Types avec fichier: book, photo, other
            $this->form_validation->set_rules('uploaded_file_path', 'Fichier', 'required');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('autre'));
            return;
        }

        $data = $this->prepareData($sous_type, 'create');
        
        if (!$data) {
            redirect(base_url('autre'));
            return;
        }

        $rsp = $this->Model->create('galerie_medias', $data);
        
        $this->flashMessage($rsp, 'Élément créé avec succès.', 'Erreur création.');
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
        
        $sous_type = $old['sous_type'];
        
        if ($sous_type === 'link') {
            $this->form_validation->set_rules('lien', 'Lien', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('autre'));
            return;
        }

        $data = $this->prepareData($sous_type, 'update', $old);
        
        if (!$data) {
            redirect(base_url('autre'));
            return;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);
        
        $this->flashMessage($rsp, 'Élément mis à jour.', 'Erreur mise à jour.');
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

        if ($rsp && $item) {
            $this->deleteFiles($item);
            $this->session->set_flashdata('success', 'Élément supprimé.');
        } else {
            $this->session->set_flashdata('error', 'Erreur suppression.');
        }
        
        redirect(base_url('autre'));
    }

    public function ChangeStatus()
    {
        $id = $this->input->post('id');
        $current = $this->input->post('est_actif');
        $new = ($current == 1) ? 0 : 1;
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            'est_actif' => $new,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->flashMessage($rsp, 'Statut mis à jour.', 'Erreur.');
        redirect(base_url('autre'));
    }

    public function toggleField()
    {
        $this->jsonHeaders();
        
        $id = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');
        
        $allowed = ['is_for_whatsapp', 'is_for_website'];
        if (!in_array($field, $allowed)) {
            echo json_encode(['success' => false]);
            return;
        }
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            $field => $value,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode(['success' => (bool)$rsp]);
    }

    // ==================== TRAITEMENT FICHIERS ====================

    private function processFile($file_path, $filename, $sous_type)
    {
        $result = ['miniature' => null, 'dimensions' => null, 'pages' => null];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        switch ($sous_type) {
            case 'photo':
                $result = array_merge($result, $this->processImage($file_path, $filename));
                break;
                
            case 'book':
                if ($ext === 'pdf') {
                    $result = array_merge($result, $this->processPDF($file_path, $filename));
                } else {
                    $result['miniature'] = 'assets/images/book-default.png';
                }
                break;
                
            case 'other':
                $result['miniature'] = $this->getFileIcon($ext);
                break;
        }

        return $result;
    }

    private function processImage($source, $filename)
    {
        $result = ['miniature' => null, 'dimensions' => null];
        
        $dims = @getimagesize($source);
        if ($dims) {
            $result['dimensions'] = $dims[0] . 'x' . $dims[1];
        }

        // Créer miniature
        $thumb_name = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumb_path = $this->thumbs_dir . $thumb_name;
        
        if ($this->createThumbnail($source, $thumb_path, 400, 300)) {
            $result['miniature'] = 'attachments/Autre/thumbs/' . $thumb_name;
        }

        return $result;
    }

    private function processPDF($file_path, $filename)
    {
        $result = ['miniature' => null, 'pages' => null];
        
        // Compter pages
        $content = file_get_contents($file_path, false, null, 0, 50000);
        if (preg_match('/\/Type\s*\/Pages.*?\/Count\s+(\d+)/s', $content, $m)) {
            $result['pages'] = (int)$m[1];
        }

        // Générer miniature avec FFmpeg si dispo
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
                $result['miniature'] = 'attachments/Autre/thumbs/' . $thumb_name;
            }
        }

        if (!$result['miniature']) {
            $result['miniature'] = 'assets/images/pdf-default.png';
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

    private function prepareData($sous_type, $mode, $old = null)
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
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : ($mode === 'create' ? 1 : 0),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($mode === 'create') {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        // Traitement selon type
        switch ($sous_type) {
            case 'link':
                $data['lien'] = $this->input->post('lien');
                $data['miniature'] = $this->extractLinkThumb($data['lien']);
                break;
                
            case 'texte':
                $data['contenu_texte'] = $this->input->post('contenu_texte') ?: null;
                $data['miniature'] = 'assets/images/text-default.png';
                break;
                
            default: // book, photo, other
                $file_path = $this->input->post('uploaded_file_path');
                
                if ($mode === 'update' && empty($file_path) && $old && !empty($old['fichier'])) {
                    // Garder ancien
                    $data['fichier'] = $old['fichier'];
                    $data['taille'] = $old['taille'];
                    $data['mime_type'] = $old['mime_type'];
                    $data['miniature'] = $old['miniature'];
                } elseif (!empty($file_path)) {
                    // Supprimer ancien si update
                    if ($mode === 'update' && $old && !empty($old['fichier'])) {
                        $this->deleteFiles($old);
                    }
                    
                    $full = FCPATH . $file_path;
                    $data['fichier'] = $file_path;
                    $data['taille'] = filesize($full);
                    $data['mime_type'] = mime_content_type($full);
                    $data['lien'] = null;
                    $data['miniature'] = $this->input->post('miniature') ?: $this->getFileIcon(
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

    private function extractLinkThumb($url)
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return "https://vumbnail.com/{$m[1]}.jpg";
        }
        
        // Favicon
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

    private function cleanupUpload($upload_id)
    {
        $dir = $this->upload_dir . $upload_id . '/';
        if (is_dir($dir)) {
            $files = glob($dir . '*');
            foreach ($files as $file) {
                is_file($file) && @unlink($file);
            }
            @rmdir($dir);
        }
    }

    private function cleanupSessions()
    {
        if (!is_dir($this->upload_dir)) return;
        
        foreach (glob($this->upload_dir . 'autre_*', GLOB_ONLYDIR) as $dir) {
            $meta = $dir . '/metadata.json';
            if (!file_exists($meta)) {
                $this->cleanupUpload(basename($dir));
                continue;
            }
            
            $data = json_decode(file_get_contents($meta), true);
            if (!$data || (time() - $data['created_at']) > 7200) {
                $this->cleanupUpload(basename($dir));
            }
        }
    }

    private function getCategories()
    {
        $this->db->select('DISTINCT(categorie) as cat');
        $this->db->where('type', 'autre');
        $this->db->where('categorie IS NOT NULL');
        $this->db->where('categorie !=', '');
        return array_column($this->db->get('galerie_medias')->result_array(), 'cat');
    }

   private function getStats()
{
    $stats = ['total' => 0, 'by_type' => [], 'total_size' => 0, 'total_size_formatted' => '0 B'];
    
    foreach ($this->type_configs as $key => $cfg) {
        $stats['by_type'][$key] = 0;
    }

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

    private function sanitizeFilename($name)
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $base = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($name, PATHINFO_FILENAME));
        return substr($base, 0, 50) . '.' . $ext;
    }

    private function findFFmpeg()
    {
        $paths = ['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg'];
        foreach ($paths as $p) {
            exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return false;
    }

    public function formatBytes($bytes)
    {
        if ($bytes === 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log(1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    private function jsonHeaders()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: no-cache');
    }

    private function flashMessage($success, $msg, $err)
    {
        $this->session->set_flashdata($success ? 'success' : 'error', $success ? $msg : $err);
    }
}