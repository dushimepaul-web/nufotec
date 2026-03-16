<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Video extends MY_Controller {

    private $upload_dir;
    private $final_dir;

    function __construct()
    {
        parent::__construct();
        is_admin();
        
        // Définir les chemins
        $this->upload_dir = FCPATH . 'uploads/temp/';
        $this->final_dir = FCPATH . 'attachments/Galerie/';
        
        // Créer les dossiers si inexistant
        if (!is_dir($this->upload_dir)) {
            @mkdir($this->upload_dir, 0777, TRUE);
        }
        if (!is_dir($this->final_dir)) {
            @mkdir($this->final_dir, 0777, TRUE);
        }
    }
    
    /**
     * Page principale - Liste des vidéos
     */
    public function index()
    {
        $data['videos'] = $this->Model->read('galerie_medias', 
            ['type' => 'video'], 
            'id_media', 
            'DESC'
        );
        $this->load->view('Video_View', $data);
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
        $upload_id = uniqid('upload_', true);
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
                UPLOAD_ERR_INI_SIZE => 'Fichier trop grand (php.ini)',
                UPLOAD_ERR_FORM_SIZE => 'Fichier trop grand (formulaire)',
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
     * Étape 3: Vérifier le statut
     */
    public function checkStatus()
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
        
        $actual_chunks = [];
        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            if (file_exists($temp_dir . 'chunk_' . $i)) {
                $actual_chunks[] = $i;
            }
        }

        if ($actual_chunks !== $metadata['uploaded_chunks']) {
            $metadata['uploaded_chunks'] = $actual_chunks;
            file_put_contents($metadata_path, json_encode($metadata));
        }

        $progress = (count($actual_chunks) / $metadata['total_chunks']) * 100;

        echo json_encode([
            'success' => true,
            'uploaded_chunks' => $actual_chunks,
            'total_chunks' => $metadata['total_chunks'],
            'progress' => round($progress, 2),
            'file_name' => $metadata['file_name'],
            'file_size' => $metadata['file_size']
        ]);
    }

    /**
     * Étape 4: Finaliser l'upload
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
        $relative_path = 'attachments/Galerie/' . $final_name;

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

        $thumb = $this->generate_video_thumbnail($relative_path);

        echo json_encode([
            'success' => true,
            'file_path' => $relative_path,
            'file_name' => $final_name,
            'file_size' => filesize($final_path),
            'file_size_formatted' => $this->formatFileSize(filesize($final_path)),
            'thumbnail' => $thumb,
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
     * Créer une nouvelle vidéo
     */
    public function Create()
    {
        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $type_source = $this->input->post('type_source');
        
        if ($type_source == 'link') {
            $this->form_validation->set_rules('lien', 'Lien vidéo', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('video'));
            return;
        }

        $data = [
            'titre' => $this->input->post('titre'),
            'type' => 'video',
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

        if ($type_source == 'upload') {
            $file_path = $this->input->post('uploaded_file_path');
            
            if (!empty($file_path) && file_exists(FCPATH . $file_path)) {
                $data['fichier'] = $file_path;
                $data['taille'] = filesize(FCPATH . $file_path);
                $data['mime_type'] = mime_content_type(FCPATH . $file_path);
                $data['miniature'] = $this->input->post('thumbnail') ?: $this->generate_video_thumbnail($file_path);
            } else {
                $this->session->set_flashdata('error', 'Aucun fichier vidéo uploadé.');
                redirect(base_url('video'));
                return;
            }
        } else {
            $data['lien'] = $this->input->post('lien');
            $data['miniature'] = $this->extract_video_thumbnail($data['lien']);
        }
        
        $rsp = $this->Model->create('galerie_medias', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Vidéo créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la création.');
        }
        redirect(base_url('video'));
    }

    /**
     * Mettre à jour une vidéo
     */
    public function Update()
    {
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $type_source = $this->input->post('type_source');
        
        if ($type_source == 'link') {
            $this->form_validation->set_rules('lien', 'Lien vidéo', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('video'));
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

        if ($type_source == 'upload') {
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
                $data['miniature'] = $this->generate_video_thumbnail($new_path);
            }
        } elseif ($type_source == 'link') {
            $new_lien = $this->input->post('lien');
            
            // Supprimer ancien fichier
            if ($old && !empty($old['fichier']) && file_exists(FCPATH . $old['fichier'])) {
                @unlink(FCPATH . $old['fichier']);
                if (!empty($old['miniature'])) @unlink(FCPATH . $old['miniature']);
            }
            
            $data['lien'] = $new_lien;
            $data['fichier'] = NULL;
            $data['taille'] = NULL;
            $data['mime_type'] = NULL;
            $data['miniature'] = $this->extract_video_thumbnail($new_lien);
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Vidéo mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la mise à jour.');
        }
        redirect(base_url('video'));
    }

    /**
     * Supprimer une vidéo
     */
    public function Delete()
    {
        $id = $this->input->post('id');
        $video = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            'est_actif' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            if ($video && !empty($video['fichier'])) @unlink(FCPATH . $video['fichier']);
            if ($video && !empty($video['miniature'])) @unlink(FCPATH . $video['miniature']);
            $this->session->set_flashdata('success', 'Vidéo supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        redirect(base_url('video'));
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
        redirect(base_url('video'));    
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
     * Générer une miniature pour vidéo uploadée
     */
    private function generate_video_thumbnail($video_path)
    {
        $ffmpeg = $this->check_ffmpeg();
        if (!$ffmpeg) return NULL;

        $folder = FCPATH . 'attachments/Galerie/';
        $name = pathinfo($video_path, PATHINFO_FILENAME) . '_thumb.jpg';
        $path = $folder . $name;

        $cmd = sprintf('%s -i %s -ss 00:00:01 -vframes 1 -q:v 2 -y %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg(FCPATH . $video_path),
            escapeshellarg($path)
        );

        exec($cmd, $output, $code);
        return ($code === 0 && file_exists($path)) ? 'attachments/Galerie/' . $name : NULL;
    }

    /**
     * Extraire miniature depuis URL (YouTube, Vimeo)
     */
    private function extract_video_thumbnail($url)
    {
        // YouTube - ID de 11 caractères
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return "https://vumbnail.com/{$m[1]}.jpg";
        }
        
        return NULL;
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