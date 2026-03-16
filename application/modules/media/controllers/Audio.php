<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Audio Controller
 * Gestion des médias audio avec upload chunked et enregistrement WebRTC
 */
class Audio extends MY_Controller {

    private $upload_dir;
    private $final_dir;

    public function __construct()
    {
        parent::__construct();

        // Vérifier que l'utilisateur est admin
        if (!is_admin()) {
            redirect('login');
        }

        // Définir les chemins
        $this->upload_dir = FCPATH . 'uploads/temp/audio/';
        $this->final_dir = FCPATH . 'attachments/Audio/';

        // Créer les dossiers si inexistant
        if (!is_dir($this->upload_dir)) {
            @mkdir($this->upload_dir, 0777, TRUE);
        }
        if (!is_dir($this->final_dir)) {
            @mkdir($this->final_dir, 0777, TRUE);
        }

        // Charger les modèles nécessaires
        $this->load->model('Model');
    }

    /**
     * Page principale - Liste des audios
     */
    public function index()
    {
        $data['audios'] = $this->Model->read('galerie_medias', 
            ['type' => 'audio'], 
            'id_media', 
            'DESC'
        );

        $this->load->view('Audio_View', $data);
    }

    // ==================== UPLOAD CHUNKED ====================

    /**
     * Étape 1: Initialiser l'upload chunked
     */
    public function initUpload()
    {
        header('Content-Type: application/json');

        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');
        $chunk_size = 5 * 1024 * 1024; // 5MB

        if (empty($file_name) || $file_size <= 0) {
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
            return;
        }

        $total_chunks = ceil($file_size / $chunk_size);
        $upload_id = uniqid('audio_upload_', true);
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
        $relative_path = 'attachments/Audio/' . $final_name;

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

        // Générer waveform/miniature
        $waveform = $this->generate_audio_waveform($relative_path);
        $duration = $this->get_audio_duration($final_path);

        echo json_encode([
            'success' => true,
            'file_path' => $relative_path,
            'file_name' => $final_name,
            'file_size' => filesize($final_path),
            'file_size_formatted' => $this->formatFileSize(filesize($final_path)),
            'waveform' => $waveform,
            'mime_type' => mime_content_type($final_path),
            'duration' => $duration
        ]);
    }

    /**
     * Annuler upload
     */
    public function cancelUpload()
    {
        header('Content-Type: application/json');

        $upload_id = $this->input->post('upload_id');
        $temp_dir = $this->upload_dir . $upload_id . '/';

        if (is_dir($temp_dir)) {
            array_map('unlink', glob($temp_dir . '*'));
            @rmdir($temp_dir);
        }

        echo json_encode(['success' => true]);
    }

    // ==================== ENREGISTREMENT AUDIO ====================

    /**
     * Sauvegarder un audio enregistré (WebRTC)
     */
    public function saveRecording()
    {
        header('Content-Type: application/json');

        if (empty($_FILES['audio'])) {
            echo json_encode(['success' => false, 'message' => 'Aucun audio reçu']);
            return;
        }

        if ($_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Erreur upload: ' . $_FILES['audio']['error']]);
            return;
        }

        // Types MIME acceptés
        $allowed_types = [
            'audio/webm', 
            'audio/ogg', 
            'audio/wav', 
            'audio/mp4', 
            'audio/mpeg',
            'video/webm'  // Chrome envoie parfois video/webm
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['audio']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Format non supporté: ' . $mime_type]);
            return;
        }

        // Récupérer la durée envoyée par JS
        $duration = $this->input->post('duration');
        if (empty($duration) || !is_numeric($duration)) {
            $duration = 0;
        }

        // Générer nom fichier
        $final_name = date("YmdHis") . '_recording_' . uniqid() . '.webm';
        $final_path = $this->final_dir . $final_name;
        $relative_path = 'attachments/Audio/' . $final_name;

        if (!move_uploaded_file($_FILES['audio']['tmp_name'], $final_path)) {
            echo json_encode(['success' => false, 'message' => 'Erreur sauvegarde fichier']);
            return;
        }

        // Convertir en MP3 si FFmpeg disponible
        $mp3_path = $this->convert_to_mp3($final_path);
        if ($mp3_path) {
            unlink($final_path);
            $final_path = $mp3_path;
            $relative_path = str_replace(FCPATH, '', $mp3_path);
            $final_name = basename($mp3_path);
        }

        // Générer waveform
        $waveform = $this->generate_audio_waveform($relative_path);

        echo json_encode([
            'success' => true,
            'file_path' => $relative_path,
            'file_name' => $final_name,
            'file_size' => filesize($final_path),
            'file_size_formatted' => $this->formatFileSize(filesize($final_path)),
            'waveform' => $waveform,
            'mime_type' => mime_content_type($final_path),
            'duration' => (int)$duration,
            'is_recording' => true
        ]);
    }

    // ==================== CRUD OPERATIONS ====================

    /**
     * Créer un nouvel audio
     */
    public function Create()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $type_source = $this->input->post('type_source');

        if ($type_source == 'link') {
            $this->form_validation->set_rules('lien', 'Lien audio', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('audio'));
            return;
        }

        $data = [
            'titre' => $this->input->post('titre'),
            'type' => 'audio',
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

        if ($type_source == 'upload' || $type_source == 'recording') {
            $file_path = $this->input->post('uploaded_file_path');

            if (!empty($file_path) && file_exists(FCPATH . $file_path)) {
                $full_path = FCPATH . $file_path;
                $data['fichier'] = $file_path;
                $data['taille'] = filesize($full_path);
                $data['mime_type'] = mime_content_type($full_path);

                // Récupérer la durée - d'abord depuis le champ caché, sinon calculer
                $duration = $this->input->post('duration');
                if (empty($duration) || $duration == 0) {
                    $duration = $this->get_audio_duration($full_path);
                }
                $data['duree'] = $duration ?: 0;

                $data['miniature'] = $this->input->post('waveform') ?: $this->generate_audio_waveform($file_path);
                $data['is_recording'] = ($type_source == 'recording') ? 1 : 0;
            } else {
                $this->session->set_flashdata('error', 'Aucun fichier audio uploadé.');
                redirect(base_url('audio'));
                return;
            }
        } else {
            $data['lien'] = $this->input->post('lien');
            $data['miniature'] = $this->extract_audio_thumbnail($data['lien']);
            $data['duree'] = 0;
        }

        $rsp = $this->Model->create('galerie_medias', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Audio créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la création.');
        }
        redirect(base_url('audio'));
    }

    /**
     * Mettre à jour un audio
     */
    public function Update()
    {
        $this->load->library('form_validation');

        $id = $this->input->post('id');

        $this->form_validation->set_rules('titre', 'Titre', 'required|max_length[255]');
        $type_source = $this->input->post('type_source');

        if ($type_source == 'link') {
            $this->form_validation->set_rules('lien', 'Lien audio', 'required|valid_url');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('audio'));
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
                $data['duree'] = $this->get_audio_duration(FCPATH . $new_path);
                $data['miniature'] = $this->generate_audio_waveform($new_path);
                $data['is_recording'] = 0;
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
            $data['duree'] = NULL;
            $data['miniature'] = $this->extract_audio_thumbnail($new_lien);
            $data['is_recording'] = 0;
        }

        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Audio mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la mise à jour.');
        }
        redirect(base_url('audio'));
    }

    /**
     * Supprimer un audio
     */
    public function Delete()
    {
        $id = $this->input->post('id');
        $audio = $this->Model->readOne('galerie_medias', ['id_media' => $id]);

        // Soft delete - marquer comme inactif
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            'est_actif' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            // Supprimer les fichiers physiques
            if ($audio && !empty($audio['fichier'])) @unlink(FCPATH . $audio['fichier']);
            if ($audio && !empty($audio['miniature'])) @unlink(FCPATH . $audio['miniature']);

            $this->session->set_flashdata('success', 'Audio supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        redirect(base_url('audio'));
    }

    /**
     * Changer le statut (Actif/Inactif)
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
        redirect(base_url('audio'));    
    }

    /**
     * Toggle AJAX pour WhatsApp et Site Web
     */
    public function toggleField()
    {
        header('Content-Type: application/json');

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
     * Générer une waveform/miniature pour audio
     */
    private function generate_audio_waveform($audio_path)
    {
        // Utiliser FFmpeg pour générer une image de waveform
        $ffmpeg = $this->check_ffmpeg();
        if (!$ffmpeg) return 'assets/images/audio-default.png';

        $folder = FCPATH . 'attachments/Audio/';
        $name = pathinfo($audio_path, PATHINFO_FILENAME) . '_waveform.png';
        $path = $folder . $name;

        // Générer waveform avec FFmpeg
        $cmd = sprintf('%s -i %s -filter_complex "showwavespic=s=800x200:colors=blue" -frames:v 1 %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg(FCPATH . $audio_path),
            escapeshellarg($path)
        );

        exec($cmd, $output, $code);

        if ($code === 0 && file_exists($path)) {
            return 'attachments/Audio/' . $name;
        }

        return 'assets/images/audio-default.png';
    }

    /**
     * Extraire miniature depuis URL (SoundCloud, Spotify, etc.)
     */
    private function extract_audio_thumbnail($url)
    {
        // SoundCloud (nécessite API)
        if (preg_match('/soundcloud\.com/', $url)) {
            return 'assets/images/soundcloud-default.png';
        }

        // Spotify (nécessite API)
        if (preg_match('/spotify\.com/', $url)) {
            return 'assets/images/spotify-default.png';
        }

        // YouTube Audio
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
        }

        return 'assets/images/audio-default.png';
    }

    /**
     * Obtenir la durée d'un fichier audio
     */
    private function get_audio_duration($file_path)
    {
        $ffmpeg = $this->check_ffmpeg();
        if (!$ffmpeg) return 0;

        $cmd = sprintf('%s -i %s 2>&1 | grep "Duration" | cut -d ' ' -f 4 | sed s/,//',
            escapeshellarg($ffmpeg),
            escapeshellarg($file_path)
        );

        exec($cmd, $output, $code);

        if (!empty($output[0])) {
            // Convertir HH:MM:SS.ms en secondes
            $parts = explode(':', $output[0]);
            if (count($parts) === 3) {
                $hours = (int)$parts[0];
                $minutes = (int)$parts[1];
                $seconds = (float)$parts[2];
                return (int)(($hours * 3600) + ($minutes * 60) + $seconds);
            }
        }

        return 0;
    }

    /**
     * Convertir audio en MP3
     */
    private function convert_to_mp3($input_path)
    {
        $ffmpeg = $this->check_ffmpeg();
        if (!$ffmpeg) return NULL;

        $output_path = str_replace(['.webm', '.ogg', '.wav'], '.mp3', $input_path);

        $cmd = sprintf('%s -i %s -codec:a libmp3lame -qscale:a 2 %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($input_path),
            escapeshellarg($output_path)
        );

        exec($cmd, $output, $code);

        return ($code === 0 && file_exists($output_path)) ? $output_path : NULL;
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
