<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autre extends MY_Controller {

    private $upload_dir;
    private $final_dir;

    function __construct()
    {
        parent::__construct();
        is_admin();
        
        // Définir les chemins
        $this->upload_dir = FCPATH . 'uploads/temp/autre/';
        $this->final_dir = FCPATH . 'attachments/Autre/';
        
        // Créer les dossiers si inexistant
        if (!is_dir($this->upload_dir)) {
            @mkdir($this->upload_dir, 0777, TRUE);
        }
        if (!is_dir($this->final_dir)) {
            @mkdir($this->final_dir, 0777, TRUE);
        }
    }
    
    /**
     * Page principale - Liste des médias "Autre"
     */
    public function index()
    {
        $data['items'] = $this->Model->read('galerie_medias', 
            ['type' => 'autre'], 
            'id_media', 
            'DESC'
        );
        $this->load->view('Autre_View', $data);
    }

    // ==================== UPLOAD CHUNKED ====================

    /**
     * Étape 1: Initialiser l'upload chunked
     */
    public function initUpload()
    {
        header('Content-Type: application/json');
        
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['success' => false, 'message' => 'Not AJAX']);
            return;
        }

        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');
        $chunk_size = 5 * 1024 * 1024; // 5MB
        
        if (empty($file_name) || $file_size <= 0) {
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
            return;
        }

        $total_chunks = ceil($file_size / $chunk_size);
        $upload_id = uniqid('autre_upload_', true);
        $temp_dir = $this->upload_dir . $upload_id . '/';

        if (!@mkdir($temp_dir, 0777, TRUE)) {
            echo json_encode(['success' => false, 'message' => 'Erreur création dossier temporaire']);
            return;
        }

        $metadata = [
            'file_name' => $file_name,
            'file_size' => $file_size,
            'chunk_size' => $chunk_size,
            'total_chunks' => $total_chunks,
            'uploaded_chunks' => [],
            'created_at' => time()
        ];

        file_put_contents($temp_dir . 'metadata.json', json_encode($metadata));

        echo json_encode([
            'success' => true,
            'upload_id' => $upload_id,
            'chunk_size' => $chunk_size,
            'total_chunks' => $total_chunks
        ]);
    }

    /**
     * Étape 2: Uploader un chunk
     */
    public function uploadChunk()
    {
        header('Content-Type: application/json');
        
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['success' => false, 'message' => 'Not AJAX']);
            return;
        }

        $upload_id = $this->input->post('upload_id');
        $chunk_index = $this->input->post('chunk_index');

        if (empty($upload_id) || !is_numeric($chunk_index)) {
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
            return;
        }

        $chunk_index = (int)$chunk_index;
        $temp_dir = $this->upload_dir . $upload_id . '/';

        if (!is_dir($temp_dir)) {
            echo json_encode(['success' => false, 'message' => 'Session invalide']);
            return;
        }

        if (empty($_FILES['chunk'])) {
            echo json_encode(['success' => false, 'message' => 'Aucun chunk reçu']);
            return;
        }

        if ($_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'Fichier trop grand',
                UPLOAD_ERR_FORM_SIZE => 'Fichier trop grand',
                UPLOAD_ERR_PARTIAL => 'Upload partiel',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temp manquant',
                UPLOAD_ERR_CANT_WRITE => 'Erreur écriture',
                UPLOAD_ERR_EXTENSION => 'Extension bloquée'
            ];
            $msg = isset($errors[$_FILES['chunk']['error']]) ? $errors[$_FILES['chunk']['error']] : 'Erreur '.$_FILES['chunk']['error'];
            echo json_encode(['success' => false, 'message' => $msg]);
            return;
        }

        $chunk_path = $temp_dir . 'chunk_' . $chunk_index;
        
        if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path)) {
            echo json_encode(['success' => false, 'message' => 'Erreur sauvegarde chunk']);
            return;
        }

        $metadata_path = $temp_dir . 'metadata.json';
        $metadata = json_decode(file_get_contents($metadata_path), true);
        
        if (!in_array($chunk_index, $metadata['uploaded_chunks'])) {
            $metadata['uploaded_chunks'][] = $chunk_index;
            sort($metadata['uploaded_chunks']);
            file_put_contents($metadata_path, json_encode($metadata));
        }

        $progress = (count($metadata['uploaded_chunks']) / $metadata['total_chunks']) * 100;

        echo json_encode([
            'success' => true,
            'chunk_index' => $chunk_index,
            'received' => filesize($chunk_path),
            'progress' => round($progress, 2),
            'uploaded_chunks' => count($metadata['uploaded_chunks']),
            'total_chunks' => $metadata['total_chunks']
        ]);
    }

    /**
     * Étape 3: Finaliser l'upload
     */
    public function completeUpload()
    {
        header('Content-Type: application/json');
        
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['success' => false, 'message' => 'Not AJAX']);
            return;
        }

        $upload_id = $this->input->post('upload_id');
        $temp_dir = $this->upload_dir . $upload_id . '/';
        $metadata_path = $temp_dir . 'metadata.json';
        
        if (!file_exists($metadata_path)) {
            echo json_encode(['success' => false, 'message' => 'Session non trouvée']);
            return;
        }

        $metadata = json_decode(file_get_contents($metadata_path), true);
        
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
                'missing_chunks' => $missing
            ]);
            return;
        }

        $ext = pathinfo($metadata['file_name'], PATHINFO_EXTENSION);
        $final_name = date("YmdHis") . '_' . uniqid() . '.' . strtolower($ext);
        $final_path = $this->final_dir . $final_name;
        $relative_path = 'attachments/Autre/' . $final_name;

        $out = fopen($final_path, 'wb');
        if (!$out) {
            echo json_encode(['success' => false, 'message' => 'Impossible de créer fichier final']);
            return;
        }

        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            $chunk_file = $temp_dir . 'chunk_' . $i;
            fwrite($out, file_get_contents($chunk_file));
            unlink($chunk_file);
        }
        fclose($out);

        unlink($metadata_path);
        rmdir($temp_dir);

        if (!file_exists($final_path)) {
            echo json_encode(['success' => false, 'message' => 'Erreur création fichier final']);
            return;
        }

        // Générer miniature selon type
        $sous_type = $this->input->post('sous_type') ?: 'other';
        $miniature = $this->generate_thumbnail($relative_path, $sous_type);

        echo json_encode([
            'success' => true,
            'file_path' => $relative_path,
            'file_name' => $final_name,
            'file_size' => filesize($final_path),
            'file_size_formatted' => $this->formatFileSize(filesize($final_path)),
            'miniature' => $miniature,
            'mime_type' => mime_content_type($final_path)
        ]);
    }

    /**
     * Annuler upload
     */
    public function cancelUpload()
    {
        header('Content-Type: application/json');
        
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['success' => false, 'message' => 'Not AJAX']);
            return;
        }

        $upload_id = $this->input->post('upload_id');
        $temp_dir = $this->upload_dir . $upload_id . '/';
        
        if (is_dir($temp_dir)) {
            array_map('unlink', glob($temp_dir . '*'));
            @rmdir($temp_dir);
        }

        echo json_encode(['success' => true]);
    }

    // ==================== CRUD OPERATIONS ====================

    /**
     * Créer un nouvel item
     */
    public function Create()
    {
        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $this->form_validation->set_rules('sous_type', 'Type', 'required|in_list[link,book,texte,photo,other]');
        
        $sous_type = $this->input->post('sous_type');
        
        // Validation selon le type
        if ($sous_type === 'link') {
            $this->form_validation->set_rules('lien', 'Lien', 'required|valid_url');
        } elseif (in_array($sous_type, ['book', 'photo'])) {
            // Fichier requis pour book et photo
            $file_path = $this->input->post('uploaded_file_path');
            if (empty($file_path)) {
                $this->session->set_flashdata('error', 'Un fichier est requis pour ce type de contenu.');
                redirect(base_url('autre'));
                return;
            }
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('autre'));
            return;
        }

        $data = [
            'titre' => $this->input->post('titre'),
            'type' => 'autre',
            'sous_type' => $sous_type,
            'description' => $this->input->post('description') ?: NULL,
            'categorie' => $this->input->post('categorie') ?: NULL,
            'date_media' => $this->input->post('date_media') ?: NULL,
            'credits' => $this->input->post('credits') ?: NULL,
            'est_actif' => 1,
            'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
            'message_reseaux' => $this->input->post('message_reseaux') ?: NULL,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: NULL,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Gestion selon le sous-type
        switch ($sous_type) {
            case 'link':
                $data['lien'] = $this->input->post('lien');
                $data['miniature'] = $this->extract_thumbnail_from_url($data['lien']);
                break;
                
            case 'texte':
                $data['contenu_texte'] = $this->input->post('contenu_texte') ?: NULL;
                $data['miniature'] = 'assets/images/text-default.png';
                break;
                
            case 'book':
            case 'photo':
            case 'other':
                $file_path = $this->input->post('uploaded_file_path');
                if (!empty($file_path) && file_exists(FCPATH . $file_path)) {
                    $full_path = FCPATH . $file_path;
                    $data['fichier'] = $file_path;
                    $data['taille'] = filesize($full_path);
                    $data['mime_type'] = mime_content_type($full_path);
                    $data['miniature'] = $this->input->post('miniature') ?: $this->generate_thumbnail($file_path, $sous_type);
                }
                break;
        }
        
        $rsp = $this->Model->create('galerie_medias', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Élément créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la création.');
        }
        redirect(base_url('autre'));
    }

    /**
     * Mettre à jour un item
     */
    public function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $sous_type = $this->input->post('sous_type');
        
        if ($sous_type === 'link') {
            $this->form_validation->set_rules('lien', 'Lien', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('autre'));
            return;
        }

        $data = [
            'titre' => $this->input->post('titre'),
            'description' => $this->input->post('description') ?: NULL,
            'categorie' => $this->input->post('categorie') ?: NULL,
            'date_media' => $this->input->post('date_media') ?: NULL,
            'credits' => $this->input->post('credits') ?: NULL,
            'est_actif' => $this->input->post('est_actif') ? 1 : 0,
            'a_partager_reseaux' => $this->input->post('a_partager_reseaux') ? 1 : 0,
            'message_reseaux' => $this->input->post('message_reseaux') ?: NULL,
            'is_for_whatsapp' => $this->input->post('is_for_whatsapp') ? 1 : 0,
            'is_for_website' => $this->input->post('is_for_website') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: NULL,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $old = $this->Model->readOne('galerie_medias', ['id_media' => $id]);

        // Gestion selon le sous-type
        switch ($sous_type) {
            case 'link':
                $new_lien = $this->input->post('lien');
                // Supprimer ancien fichier si existait
                if ($old && !empty($old['fichier']) && file_exists(FCPATH . $old['fichier'])) {
                    @unlink(FCPATH . $old['fichier']);
                    if (!empty($old['miniature'])) @unlink(FCPATH . $old['miniature']);
                }
                $data['lien'] = $new_lien;
                $data['fichier'] = NULL;
                $data['taille'] = NULL;
                $data['mime_type'] = NULL;
                $data['miniature'] = $this->extract_thumbnail_from_url($new_lien);
                break;
                
            case 'texte':
                $data['contenu_texte'] = $this->input->post('contenu_texte') ?: NULL;
                break;
                
            case 'book':
            case 'photo':
            case 'other':
                $new_path = $this->input->post('uploaded_file_path');
                if (!empty($new_path) && file_exists(FCPATH . $new_path)) {
                    // Supprimer ancien fichier
                    if ($old && !empty($old['fichier']) && file_exists(FCPATH . $old['fichier'])) {
                        @unlink(FCPATH . $old['fichier']);
                        if (!empty($old['miniature'])) @unlink(FCPATH . $old['miniature']);
                    }
                    
                    $data['fichier'] = $new_path;
                    $data['taille'] = filesize(FCPATH . $new_path);
                    $data['mime_type'] = mime_content_type(FCPATH . $new_path);
                    $data['lien'] = NULL;
                    $data['miniature'] = $this->input->post('miniature') ?: $this->generate_thumbnail($new_path, $sous_type);
                }
                break;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Élément mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la mise à jour.');
        }
        redirect(base_url('autre'));
    }

    /**
     * Supprimer un item
     */
    public function Delete()
    {
        $id = $this->input->post('id');
        $item = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            'est_actif' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            if ($item && !empty($item['fichier'])) @unlink(FCPATH . $item['fichier']);
            if ($item && !empty($item['miniature'])) @unlink(FCPATH . $item['miniature']);
            $this->session->set_flashdata('success', 'Élément supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        redirect(base_url('autre'));
    }

    /**
     * Changer le statut
     */
    public function ChangeStatus()
    {
        $id = $this->input->post('id');
        $est_actif = $this->input->post('est_actif');
        $status = ($est_actif == 1) ? 0 : 1;
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], ['est_actif' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la mise à jour du statut.');
        }
        redirect(base_url('autre'));    
    }

    /**
     * Toggle AJAX pour WhatsApp et Site Web
     */
    public function toggleField()
    {
        header('Content-Type: application/json');
        
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['success' => false, 'message' => 'Not AJAX']);
            return;
        }
        
        $id = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');
        
        $allowed = ['is_for_whatsapp', 'is_for_website'];
        if (!in_array($field, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Champ non autorisé']);
            return;
        }
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            $field => $value,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode(['success' => (bool)$rsp]);
    }

    // ==================== HELPERS ====================

    /**
     * Formater la taille de fichier
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Générer une miniature selon le type
     */
    private function generate_thumbnail($file_path, $sous_type)
    {
        $full_path = FCPATH . $file_path;
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Pour les images (photo)
        if ($sous_type === 'photo' && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return $this->create_image_thumbnail($file_path);
        }
        
        // Pour les PDF (book)
        if ($sous_type === 'book' && $ext === 'pdf') {
            return $this->create_pdf_thumbnail($file_path);
        }
        
        // Icônes par défaut selon le type
        $icons = [
            'book' => 'assets/images/book-default.png',
            'photo' => 'assets/images/photo-default.png',
            'other' => 'assets/images/file-default.png'
        ];
        
        return $icons[$sous_type] ?? 'assets/images/file-default.png';
    }

    /**
     * Créer miniature pour image
     */
    private function create_image_thumbnail($image_path)
    {
        $folder = FCPATH . 'attachments/Autre/thumbs/';
        if (!is_dir($folder)) {
            @mkdir($folder, 0777, TRUE);
        }
        
        $name = pathinfo($image_path, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumb_path = $folder . $name;
        $relative_path = 'attachments/Autre/thumbs/' . $name;
        
        $full_path = FCPATH . $image_path;
        
        // Utiliser GD ou Imagick si disponible
        if (extension_loaded('gd')) {
            list($width, $height) = getimagesize($full_path);
            $thumb_width = 300;
            $thumb_height = ($height / $width) * $thumb_width;
            
            $thumb = imagecreatetruecolor($thumb_width, $thumb_height);
            
            switch (mime_content_type($full_path)) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($full_path);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($full_path);
                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($full_path);
                    break;
                default:
                    return 'assets/images/photo-default.png';
            }
            
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
            imagejpeg($thumb, $thumb_path, 85);
            imagedestroy($thumb);
            imagedestroy($source);
            
            return $relative_path;
        }
        
        return 'assets/images/photo-default.png';
    }

    /**
     * Créer miniature pour PDF
     */
    private function create_pdf_thumbnail($pdf_path)
    {
        $ffmpeg = $this->check_ffmpeg();
        if (!$ffmpeg) {
            return 'assets/images/pdf-default.png';
        }
        
        $folder = FCPATH . 'attachments/Autre/thumbs/';
        if (!is_dir($folder)) {
            @mkdir($folder, 0777, TRUE);
        }
        
        $name = pathinfo($pdf_path, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumb_path = $folder . $name;
        $relative_path = 'attachments/Autre/thumbs/' . $name;
        
        // Convertir première page en image avec FFmpeg
        $cmd = sprintf('%s -i %s -vf "select=eq(n\\,0),scale=300:-1" -vframes 1 %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg(FCPATH . $pdf_path),
            escapeshellarg($thumb_path)
        );
        
        exec($cmd, $output, $code);
        
        return ($code === 0 && file_exists($thumb_path)) ? $relative_path : 'assets/images/pdf-default.png';
    }

    /**
     * Extraire miniature depuis URL
     */
    private function extract_thumbnail_from_url($url)
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return "https://vumbnail.com/{$m[1]}.jpg";
        }
        
        // Site générique - favicon
        $domain = parse_url($url, PHP_URL_HOST);
        if ($domain) {
            return "https://www.google.com/s2/favicons?domain={$domain}&sz=128";
        }
        
        return 'assets/images/link-default.png';
    }

    /**
     * Vérifier si FFmpeg est installé
     */
    private function check_ffmpeg()
    {
        $paths = ['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', 'C:\\ffmpeg\\bin\\ffmpeg.exe'];
        foreach ($paths as $p) {
            exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return FALSE;
    }
}