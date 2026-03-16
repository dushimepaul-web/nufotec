<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audio extends MY_Controller {

    private $upload_dir;
    private $final_dir;

    function __construct()
    {
        parent::__construct();
        is_admin();
        
        // Définir les chemins absolus (FCPATH est la racine du projet)
        $this->upload_dir = FCPATH . 'uploads/temp/audio/';
        $this->final_dir = FCPATH . 'attachments/Audio/';
        
        // Créer les dossiers avec vérification des droits
        $this->ensure_directory($this->upload_dir);
        $this->ensure_directory($this->final_dir);
    }

    /**
     * Vérifie et crée un dossier avec les bonnes permissions
     */
    private function ensure_directory($dir)
    {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, TRUE)) {
                $error = error_get_last();
                log_message('error', 'Échec création dossier: ' . $dir . ' - ' . ($error['message'] ?? ''));
                show_error("Erreur de configuration : le dossier $dir n'a pas pu être créé.");
            }
        }
        // Vérifier les droits d'écriture
        if (!is_writable($dir)) {
            log_message('error', 'Dossier non accessible en écriture: ' . $dir);
            show_error("Erreur de configuration : le dossier $dir n'est pas accessible en écriture.");
        }
    }

    /**
     * Page principale - Liste des audios
     */
    public function index()
    {
        $data['audios'] = $this->Model->read('galerie_medias', ['type' => 'audio'], 'id_media', 'DESC');
        $this->load->view('Audio_View', $data);
    }

    // ==================== UPLOAD CHUNKED ====================

    /**
     * Étape 1: Initialiser l'upload chunked
     */
    public function initUpload()
    {
        header('Content-Type: application/json');
        log_message('debug', 'initUpload appelé');

        $file_name = $this->input->post('file_name');
        $file_size = (int)$this->input->post('file_size');
        $chunk_size = 5 * 1024 * 1024; // 5 Mo
        
        if (empty($file_name) || $file_size <= 0) {
            log_message('error', 'initUpload: paramètres invalides');
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
            return;
        }

        $total_chunks = ceil($file_size / $chunk_size);
        $upload_id = uniqid('audio_upload_', true);
        $temp_dir = $this->upload_dir . $upload_id . '/';

        if (!mkdir($temp_dir, 0777, TRUE)) {
            log_message('error', 'initUpload: échec création dossier temporaire: ' . $temp_dir);
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
        log_message('debug', 'uploadChunk appelé avec upload_id=' . $this->input->post('upload_id'));

        $upload_id = $this->input->post('upload_id');
        $chunk_index = $this->input->post('chunk_index');

        if (empty($upload_id) || !is_numeric($chunk_index)) {
            log_message('error', 'uploadChunk: paramètres invalides');
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
            return;
        }

        $chunk_index = (int)$chunk_index;
        $temp_dir = $this->upload_dir . $upload_id . '/';
        log_message('debug', 'uploadChunk: temp_dir = ' . $temp_dir);

        if (!is_dir($temp_dir)) {
            log_message('error', 'uploadChunk: dossier temporaire introuvable : ' . $temp_dir);
            echo json_encode(['success' => false, 'message' => 'Session invalide']);
            return;
        }

        if (empty($_FILES['chunk'])) {
            log_message('error', 'uploadChunk: aucun fichier chunk reçu');
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
            $msg = $errors[$_FILES['chunk']['error']] ?? 'Erreur ' . $_FILES['chunk']['error'];
            log_message('error', 'uploadChunk: erreur upload PHP - ' . $msg);
            echo json_encode(['success' => false, 'message' => $msg]);
            return;
        }

        $tmp_size = $_FILES['chunk']['size'];
        log_message('debug', "uploadChunk: chunk reçu taille = $tmp_size");

        $chunk_path = $temp_dir . 'chunk_' . $chunk_index;
        
        if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path)) {
            $error = error_get_last();
            log_message('error', 'uploadChunk: move_uploaded_file a échoué - ' . ($error['message'] ?? 'erreur inconnue'));
            echo json_encode(['success' => false, 'message' => 'Erreur sauvegarde chunk']);
            return;
        }

        if (!file_exists($chunk_path)) {
            log_message('error', 'uploadChunk: le fichier chunk n\'existe pas après déplacement');
            echo json_encode(['success' => false, 'message' => 'Erreur écriture chunk']);
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
     * Étape 3: Vérifier le statut (optionnel, utilisé pour la reprise)
     */
    public function checkStatus()
    {
        header('Content-Type: application/json');
        log_message('debug', 'checkStatus appelé');

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
        log_message('debug', 'completeUpload appelé');

        $upload_id = $this->input->post('upload_id');
        $temp_dir = $this->upload_dir . $upload_id . '/';
        $metadata_path = $temp_dir . 'metadata.json';
        
        if (!file_exists($metadata_path)) {
            log_message('error', 'completeUpload: metadata non trouvé');
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
            log_message('error', 'completeUpload: chunks manquants: ' . implode(',', $missing));
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
            log_message('error', 'completeUpload: impossible de créer ' . $final_path);
            echo json_encode(['success' => false, 'message' => 'Impossible de créer fichier final']);
            return;
        }

        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            $chunk_file = $temp_dir . 'chunk_' . $i;
            $chunk_data = file_get_contents($chunk_file);
            if ($chunk_data === false) {
                log_message('error', 'completeUpload: impossible de lire chunk ' . $i);
                fclose($out);
                unlink($final_path);
                echo json_encode(['success' => false, 'message' => 'Erreur lecture chunk']);
                return;
            }
            if (fwrite($out, $chunk_data) === false) {
                log_message('error', 'completeUpload: erreur écriture chunk ' . $i);
                fclose($out);
                unlink($final_path);
                echo json_encode(['success' => false, 'message' => 'Erreur écriture fichier']);
                return;
            }
            unlink($chunk_file);
        }
        fclose($out);

        unlink($metadata_path);
        rmdir($temp_dir);

        if (!file_exists($final_path)) {
            log_message('error', 'completeUpload: fichier final non trouvé après assemblage');
            echo json_encode(['success' => false, 'message' => 'Erreur création fichier final']);
            return;
        }

        // Générer waveform/miniature
        $waveform = $this->generate_audio_waveform($relative_path);

        echo json_encode([
            'success' => true,
            'file_path' => $relative_path,
            'file_name' => $final_name,
            'file_size' => filesize($final_path),
            'file_size_formatted' => $this->formatFileSize(filesize($final_path)),
            'waveform' => $waveform,
            'mime_type' => mime_content_type($final_path),
            'duration' => $this->get_audio_duration($final_path)
        ]);
    }

    /**
     * Annuler l'upload (supprimer les fichiers temporaires)
     */
    public function cancelUpload()
    {
        header('Content-Type: application/json');
        $upload_id = $this->input->post('upload_id');
        $temp_dir = $this->upload_dir . $upload_id . '/';
        
        if (is_dir($temp_dir)) {
            array_map('unlink', glob($temp_dir . '*'));
            rmdir($temp_dir);
        }

        echo json_encode(['success' => true]);
    }

    // ==================== ENREGISTREMENT AUDIO (WEBCAM) ====================

    /**
     * Sauvegarder un enregistrement audio (WebRTC)
     */
    public function saveRecording()
    {
        header('Content-Type: application/json');
        log_message('debug', 'saveRecording appelé');

        if (empty($_FILES['audio'])) {
            echo json_encode(['success' => false, 'message' => 'Aucun audio reçu']);
            return;
        }

        if ($_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Erreur upload: ' . $_FILES['audio']['error']]);
            return;
        }

        $allowed_types = [
            'audio/webm', 'audio/ogg', 'audio/wav', 'audio/mp4', 'audio/mpeg', 'video/webm',
            'audio/opus' // ajout du support opus
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['audio']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Format non supporté: ' . $mime_type]);
            return;
        }

        $duration = $this->input->post('duration');
        if (empty($duration) || !is_numeric($duration)) {
            $duration = 0;
        }

        $final_name = date("YmdHis") . '_recording_' . uniqid() . '.webm';
        $final_path = $this->final_dir . $final_name;
        $relative_path = 'attachments/Audio/' . $final_name;

        if (!move_uploaded_file($_FILES['audio']['tmp_name'], $final_path)) {
            log_message('error', 'saveRecording: move_uploaded_file failed');
            echo json_encode(['success' => false, 'message' => 'Erreur sauvegarde fichier']);
            return;
        }

        // Tentative de conversion en MP3 (si FFmpeg disponible)
        $mp3_path = $this->convert_to_mp3($final_path);
        if ($mp3_path) {
            unlink($final_path);
            $final_path = $mp3_path;
            $relative_path = str_replace(FCPATH, '', $mp3_path);
            $final_name = basename($mp3_path);
        }

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
            // Type 'link'
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
                // Supprimer l'ancien fichier s'il existe
                if ($old && !empty($old['fichier']) && file_exists(FCPATH . $old['fichier'])) {
                    @unlink(FCPATH . $old['fichier']);
                    if (!empty($old['miniature']) && file_exists(FCPATH . $old['miniature'])) {
                        @unlink(FCPATH . $old['miniature']);
                    }
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
            
            // Supprimer l'ancien fichier s'il existe
            if ($old && !empty($old['fichier']) && file_exists(FCPATH . $old['fichier'])) {
                @unlink(FCPATH . $old['fichier']);
                if (!empty($old['miniature']) && file_exists(FCPATH . $old['miniature'])) {
                    @unlink(FCPATH . $old['miniature']);
                }
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
     * Supprimer (désactiver) un audio
     */
    public function Delete()
    {
        $id = $this->input->post('id');
        $audio = $this->Model->readOne('galerie_medias', ['id_media' => $id]);
        
        $rsp = $this->Model->update('galerie_medias', ['id_media' => $id], [
            'est_actif' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            if ($audio && !empty($audio['fichier']) && file_exists(FCPATH . $audio['fichier'])) {
                @unlink(FCPATH . $audio['fichier']);
            }
            if ($audio && !empty($audio['miniature']) && file_exists(FCPATH . $audio['miniature'])) {
                @unlink(FCPATH . $audio['miniature']);
            }
            $this->session->set_flashdata('success', 'Audio supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Erreur lors de la suppression.');
        }
        redirect(base_url('audio'));
    }

    /**
     * Changer le statut (actif/inactif)
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
     * Toggle AJAX pour les champs binaires (WhatsApp, Site Web)
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
     * Formater la taille en Ko/Mo/Go
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Générer une image de waveform avec FFmpeg
     */
    private function generate_audio_waveform($audio_path)
    {
        $ffmpeg = $this->check_ffmpeg();
        if (!$ffmpeg) {
            log_message('error', 'FFmpeg non disponible, waveform par défaut utilisée');
            return 'assets/images/audio-default.png';
        }

        $folder = FCPATH . 'attachments/Audio/';
        $name = pathinfo($audio_path, PATHINFO_FILENAME) . '_waveform.png';
        $path = $folder . $name;

        $cmd = sprintf('%s -i %s -filter_complex "showwavespic=s=800x200:colors=blue" -frames:v 1 %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg(FCPATH . $audio_path),
            escapeshellarg($path)
        );

        exec($cmd, $output, $code);
        
        if ($code === 0 && file_exists($path)) {
            return 'attachments/Audio/' . $name;
        }

        log_message('error', 'Échec génération waveform, code: ' . $code . ' sortie: ' . implode("\n", $output));
        return 'assets/images/audio-default.png';
    }

    /**
     * Extraire une miniature pour un lien externe (YouTube, SoundCloud, Spotify)
     */
    private function extract_audio_thumbnail($url)
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
        }
        
        // SoundCloud / Spotify : on pourrait utiliser une API, mais on renvoie une image par défaut
        if (preg_match('/soundcloud\.com/', $url)) {
            return 'assets/images/soundcloud-default.png';
        }
        if (preg_match('/spotify\.com/', $url)) {
            return 'assets/images/spotify-default.png';
        }
        
        return 'assets/images/audio-default.png';
    }

    /**
     * Obtenir la durée d'un fichier audio via FFmpeg
     */
    private function get_audio_duration($file_path)
    {
        $ffmpeg = $this->check_ffmpeg();
        if (!$ffmpeg) return 0;

        $cmd = sprintf('%s -i %s 2>&1 | grep "Duration" | cut -d \' \' -f 4 | sed s/,//',
            escapeshellarg($ffmpeg),
            escapeshellarg($file_path)
        );

        exec($cmd, $output, $code);
        
        if (!empty($output[0])) {
            $parts = explode(':', $output[0]);
            if (count($parts) === 3) {
                $hours = (int)$parts[0];
                $minutes = (int)$parts[1];
                $seconds = (float)$parts[2];
                return ($hours * 3600) + ($minutes * 60) + $seconds;
            }
        }
        return 0;
    }

    /**
     * Convertir un fichier audio en MP3 (si FFmpeg disponible)
     */
    private function convert_to_mp3($input_path)
    {
        $ffmpeg = $this->check_ffmpeg();
        if (!$ffmpeg) return NULL;

        $output_path = str_replace('.webm', '.mp3', $input_path);
        $output_path = str_replace('.ogg', '.mp3', $output_path);
        $output_path = str_replace('.wav', '.mp3', $output_path);

        $cmd = sprintf('%s -i %s -codec:a libmp3lame -qscale:a 2 %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($input_path),
            escapeshellarg($output_path)
        );

        exec($cmd, $output, $code);
        
        if ($code === 0 && file_exists($output_path)) {
            return $output_path;
        }
        return NULL;
    }

    /**
     * Vérifier si FFmpeg est installé et retourner son chemin
     */
    private function check_ffmpeg()
    {
        $paths = ['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', 'C:\\ffmpeg\\bin\\ffmpeg.exe'];
        foreach ($paths as $p) {
            @exec($p . ' -version 2>&1', $out, $code);
            if ($code === 0) return $p;
        }
        return FALSE;
    }
}