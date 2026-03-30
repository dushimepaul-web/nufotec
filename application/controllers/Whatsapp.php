<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur WhatsApp - Gestion des groupes, participants et envois
 */
class Whatsapp extends MY_Controller {
    
    private $jobs_dir;
    private $chunks_dir;
    private $chunk_size = 1572864; // 1.5 MB
    
    public function __construct() {
        parent::__construct();
        $this->load->library('whapi_lib');
        $this->load->model('Groupe_model');
        $this->load->helper(['form', 'url', 'file']);
        
        // Création des dossiers nécessaires
        $this->jobs_dir = FCPATH . 'uploads/jobs/';
        $this->chunks_dir = FCPATH . 'uploads/chunks/';
        
        foreach ([$this->jobs_dir, $this->chunks_dir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
        
        // Configuration PHP pour les uploads par chunks
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '256M');
        ini_set('post_max_size', '2M');
        ini_set('upload_max_filesize', '2M');
    }
    
    // ==================== DASHBOARD & GROUPES ====================
    
    /**
     * Page d'accueil - Dashboard
     */
    public function index() {
        $data['stats'] = [
            'total_groupes' => $this->Groupe_model->compter_groupes(),
            'groupes_actifs' => count($this->Groupe_model->get_all_groupes()),
            'jobs_en_cours' => count(glob($this->jobs_dir . 'job_*.json'))
        ];
        $this->load->view('whatsapp/dashboard', $data);
    }
    
    /**
     * Liste des groupes WhatsApp
     */
    public function groupes() {
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $data['total'] = count($data['groupes']);
        $data['api_status'] = $this->whapi_lib->test_connexion();
        $this->load->view('whatsapp/groupes_liste', $data);
    }
    
    /**
     * Synchroniser les groupes avec l'API Whapi
     */
    public function synchroniser() {
        $config = $this->config->item('whapi');
        
        if (empty($config['api_key'])) {
            $this->session->set_flashdata('error', 'Configuration Whapi manquante');
            redirect('whatsapp/groupes');
            return;
        }
        
        $test = $this->whapi_lib->test_connexion();
        if (!$test['success']) {
            $this->session->set_flashdata('error', 'Erreur connexion Whapi: ' . ($test['error'] ?? 'Inconnu'));
            redirect('whatsapp/groupes');
            return;
        }
        
        $resultat = $this->whapi_lib->get_groupes();
        
        if (!$resultat['success']) {
            $this->session->set_flashdata('error', 'Erreur récupération groupes');
            redirect('whatsapp/groupes');
            return;
        }
        
        $groupes = $resultat['response']['groups'] ?? [];
        $compteur = 0;
        
        foreach ($groupes as $groupe) {
            $nom = $groupe['name'] ?? $groupe['subject'] ?? 'Groupe sans nom';
            $this->Groupe_model->sauvegarder($groupe['id'], $nom, $groupe['description'] ?? '');
            $compteur++;
        }
        
        $this->session->set_flashdata('success', "$compteur groupes synchronisés avec Whapi");
        redirect('whatsapp/groupes');
    }
    
    // ==================== ENVOI AUX GROUPES ====================
    
    /**
     * Interface d'envoi aux groupes (style WhatsApp)
     */
    public function envoyer() {
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $data['total_groupes'] = count($data['groupes']);
        $data['resultat'] = null;
        $this->load->view('whatsapp/envoyer_whatsapp_style', $data);
    }
    
    /**
     * Initialiser l'upload par chunks pour groupes
     */
    public function init_chunk_upload() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['upload_id'])) {
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }
        
        $upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $input['upload_id']);
        $upload_dir = $this->chunks_dir . $upload_id . '/';
        
        if (!mkdir($upload_dir, 0777, true)) {
            echo json_encode(['success' => false, 'error' => 'Impossible de créer le dossier temporaire']);
            return;
        }
        
        // Détection du type audio/OGG Opus
        $type_envoi = $input['type_envoi'];
        $filetype = $input['filetype'];
        $filename = $input['filename'];
        
        $is_ogg_opus = (strpos($filetype, 'audio/ogg') !== false || 
                       strpos($filetype, 'ogg') !== false || 
                       strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'ogg');
        
        if (strpos($filetype, 'audio/') === 0 || $this->is_audio_file($filename) || $is_ogg_opus) {
            $type_envoi = 'audio';
        }
        
        $is_voice = $is_ogg_opus || !empty($input['voice']);
        
        $meta = [
            'upload_id' => $upload_id,
            'filename' => $filename,
            'filesize' => $input['filesize'],
            'filetype' => $filetype,
            'total_chunks' => $input['total_chunks'],
            'received_chunks' => [],
            'groupes_ids' => $input['groupes_ids'],
            'message' => $input['message'],
            'type_envoi' => $type_envoi,
            'is_voice' => $is_voice,
            'created_at' => time()
        ];
        
        file_put_contents($upload_dir . 'meta.json', json_encode($meta));
        
        log_message('info', "Upload groupes initié: $upload_id - Type: $type_envoi, Voice: " . ($is_voice ? 'true' : 'false'));
        
        echo json_encode(['success' => true, 'upload_id' => $upload_id]);
    }
    
    /**
     * Recevoir un chunk pour groupes
     */
    public function upload_chunk() {
        header('Content-Type: application/json');
        
        $upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->input->post('upload_id'));
        $chunk_index = (int)$this->input->post('chunk_index');
        $total_chunks = (int)$this->input->post('total_chunks');
        
        $upload_dir = $this->chunks_dir . $upload_id . '/';
        
        if (!is_dir($upload_dir)) {
            echo json_encode(['success' => false, 'error' => 'Upload non trouvé']);
            return;
        }
        
        if (empty($_FILES['chunk_data']) || $_FILES['chunk_data']['error'] !== UPLOAD_ERR_OK) {
            $error = isset($_FILES['chunk_data']) ? $this->get_upload_error($_FILES['chunk_data']['error']) : 'Aucun fichier';
            echo json_encode(['success' => false, 'error' => 'Chunk invalide: ' . $error]);
            return;
        }
        
        $chunk_path = $upload_dir . 'chunk_' . $chunk_index . '.part';
        
        if (!move_uploaded_file($_FILES['chunk_data']['tmp_name'], $chunk_path)) {
            echo json_encode(['success' => false, 'error' => 'Erreur sauvegarde chunk']);
            return;
        }
        
        $meta_path = $upload_dir . 'meta.json';
        $meta = json_decode(file_get_contents($meta_path), true);
        $meta['received_chunks'][] = $chunk_index;
        $meta['received_chunks'] = array_unique($meta['received_chunks']);
        sort($meta['received_chunks']);
        file_put_contents($meta_path, json_encode($meta));
        
        echo json_encode([
            'success' => true, 
            'chunk' => $chunk_index + 1,
            'total' => $total_chunks,
            'received' => count($meta['received_chunks'])
        ]);
    }
    
    /**
     * Finaliser et envoyer aux groupes
     */
    public function finalize_and_send() {
        header('Content-Type: application/json');
        
        $upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->input->post('upload_id'));
        $upload_dir = $this->chunks_dir . $upload_id . '/';
        
        if (!is_dir($upload_dir)) {
            echo json_encode(['success' => false, 'error' => 'Upload non trouvé']);
            return;
        }
        
        $meta = json_decode(file_get_contents($upload_dir . 'meta.json'), true);
        
        // Assembler les chunks
        $final_filename = $upload_id . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $meta['filename']);
        $final_path = $this->jobs_dir . $final_filename;
        
        $out = fopen($final_path, 'wb');
        if (!$out) {
            echo json_encode(['success' => false, 'error' => 'Impossible de créer le fichier final']);
            return;
        }
        
        for ($i = 0; $i < $meta['total_chunks']; $i++) {
            $chunk_path = $upload_dir . 'chunk_' . $i . '.part';
            
            if (!file_exists($chunk_path)) {
                fclose($out);
                unlink($final_path);
                echo json_encode(['success' => false, 'error' => 'Chunk manquant: ' . ($i + 1)]);
                return;
            }
            
            fwrite($out, file_get_contents($chunk_path));
        }
        
        fclose($out);
        
        $final_size = filesize($final_path);
        $final_mime = mime_content_type($final_path);
        $is_ogg_opus = (strpos($final_mime, 'audio/ogg') !== false || strpos($final_mime, 'ogg') !== false);
        
        // Nettoyer les chunks
        array_map('unlink', glob($upload_dir . '*'));
        rmdir($upload_dir);
        
        // Créer le job
        $job_id = 'job_' . $upload_id;
        
        $job = [
            'job_id' => $job_id,
            'groupes_ids' => $meta['groupes_ids'],
            'message' => $meta['message'],
            'type_envoi' => $meta['type_envoi'],
            'is_voice' => $meta['is_voice'] ?? false,
            'delai' => 1000,
            'file_info' => [
                'name' => $meta['filename'],
                'path' => $final_path,
                'size' => $final_size,
                'type' => $final_mime,
                'is_ogg_opus' => $is_ogg_opus
            ],
            'status' => 'processing',
            'progress' => 0,
            'current_group' => null,
            'result' => [
                'total' => count($meta['groupes_ids']),
                'reussis' => 0,
                'echoues' => 0,
                'details' => []
            ],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->save_job($job_id, $job);
        
        // Lancer le traitement asynchrone
        $this->process_job_async($job_id);
        
        echo json_encode([
            'success' => true,
            'job_id' => $job_id,
            'file_size' => $final_size,
            'is_voice' => $job['is_voice'],
            'mime_type' => $final_mime
        ]);
    }
    
    /**
     * Traitement asynchrone pour envoi aux groupes
     */
    private function process_job_async($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return;
        
        $groupes_ids = $job['groupes_ids'];
        $message = $job['message'];
        $type_envoi = $job['type_envoi'];
        $file_info = $job['file_info'];
        $is_voice = $job['is_voice'] ?? false;
        
        $filepath = $file_info ? $file_info['path'] : null;
        
        foreach ($groupes_ids as $index => $groupe_id) {
            $job['current_group'] = $groupe_id;
            $job['progress'] = round((($index + 1) / count($groupes_ids)) * 100);
            $job['updated_at'] = date('Y-m-d H:i:s');
            $this->save_job($job_id, $job);
            
            $success = false;
            $error = null;
            
            try {
                if ($type_envoi === 'texte') {
                    $result = $this->whapi_lib->envoyer_message($groupe_id, $message);
                } else {
                    if ($type_envoi === 'audio') {
                        $result = $this->whapi_lib->envoyer_fichier_audio($groupe_id, $filepath, $message, $is_voice);
                    } else {
                        $result = $this->whapi_lib->envoyer_fichier($groupe_id, $filepath, $message);
                    }
                }
                
                $success = $result['success'] ?? false;
                if (!$success) {
                    $error = $result['error'] ?? 'Erreur inconnue';
                }
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            
            if ($success) {
                $job['result']['reussis']++;
            } else {
                $job['result']['echoues']++;
            }
            
            $job['result']['details'][] = [
                'destinataire_id' => $groupe_id,
                'statut' => $success ? 'succès' : 'échec',
                'erreur' => $error,
                'index' => $index + 1
            ];
            
            $this->save_job($job_id, $job);
            
            if ($index < count($groupes_ids) - 1) {
                usleep(1000000);
            }
        }
        
        $job['status'] = 'completed';
        $job['progress'] = 100;
        $job['current_group'] = null;
        $job['updated_at'] = date('Y-m-d H:i:s');
        $this->save_job($job_id, $job);
        
        if ($filepath && file_exists($filepath)) {
            @unlink($filepath);
        }
    }
    
    /**
     * Envoi texte simple aux groupes
     */
    public function traiter_envoi() {
        header('Content-Type: application/json');
        
        $groupes_ids = $this->input->post('groupes_ids');
        $message = trim($this->input->post('message') ?? '');
        $type_envoi = $this->input->post('type_envoi') ?: 'texte';
        
        if (empty($groupes_ids) || !is_array($groupes_ids)) {
            echo json_encode(['success' => false, 'error' => 'Aucun groupe sélectionné']);
            return;
        }
        
        if ($type_envoi === 'texte' && empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Message requis']);
            return;
        }
        
        $job_id = 'job_' . uniqid() . '_' . time();
        
        $job = [
            'job_id' => $job_id,
            'groupes_ids' => $groupes_ids,
            'message' => $message,
            'type_envoi' => $type_envoi,
            'is_voice' => false,
            'delai' => 1000,
            'file_info' => null,
            'status' => 'processing',
            'progress' => 0,
            'result' => [
                'total' => count($groupes_ids),
                'reussis' => 0,
                'echoues' => 0,
                'details' => []
            ],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->save_job($job_id, $job);
        $this->process_job_async($job_id);
        
        echo json_encode([
            'success' => true,
            'job_id' => $job_id,
            'status' => 'processing'
        ]);
    }
    
    /**
     * Vérifier le statut d'un job groupes
     */
    public function check_status($job_id = null) {
        header('Content-Type: application/json');
        
        $job_id = $job_id ?? $this->input->get('job_id');
        
        if (!$job_id) {
            echo json_encode(['success' => false, 'error' => 'Job ID manquant']);
            return;
        }
        
        $job = $this->get_job($job_id);
        
        if (!$job) {
            echo json_encode(['success' => false, 'error' => 'Job non trouvé']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'job_id' => $job_id,
            'status' => $job['status'],
            'progress' => $job['progress'],
            'current_group' => $job['current_group'],
            'is_voice' => $job['is_voice'] ?? false,
            'result' => $job['status'] === 'completed' ? $job['result'] : null
        ]);
    }
    
    /**
     * Afficher le résultat pour groupes
     */
    public function resultat($job_id = null) {
        $job_id = $job_id ?? $this->input->get('job_id');
        
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $data['resultat'] = null;
        $data['job_id'] = $job_id;
        
        if ($job_id) {
            $job = $this->get_job($job_id);
            if ($job && $job['status'] === 'completed') {
                $data['resultat'] = [
                    'success' => ($job['result']['reussis'] ?? 0) > 0,
                    'status_code' => 200,
                    'response' => $job['result'],
                    'is_voice' => $job['is_voice'] ?? false
                ];
                $data['message'] = $job['message'];
                $data['type_envoi'] = $job['type_envoi'];
            }
        }
        
        $this->load->view('whatsapp/envoyer_whatsapp_style', $data);
    }
    
    // ==================== PARTICIPANTS ====================
    
    /**
     * Voir les participants d'un groupe spécifique
     */
    public function participants_groupe($group_id = null) {
        if (!$group_id) {
            $this->session->set_flashdata('error', 'ID du groupe manquant');
            redirect('whatsapp/groupes');
            return;
        }
        
        $group_id = urldecode($group_id);
        $this->load->model('Participant_model');
        
        $result = $this->whapi_lib->get_group_participants($group_id, true);
        
        if (!$result['success']) {
            $participants_db = $this->Participant_model->get_by_groupe($group_id);
            
            if (empty($participants_db)) {
                $this->session->set_flashdata('error', 'Erreur API: ' . $result['error']);
                redirect('whatsapp/groupes');
                return;
            }
            
            $data['group'] = [
                'group_id' => $group_id,
                'group_name' => 'Données en cache',
                'participants_count' => count($participants_db)
            ];
            $data['participants'] = $participants_db;
            $data['from_cache'] = true;
        } else {
            $data['group'] = $result;
            $data['participants'] = $result['participants'];
            $data['sync_stats'] = $result['sync_stats'] ?? null;
            $data['from_cache'] = false;
        }
        
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $this->load->view('whatsapp/participants_groupe', $data);
    }
    
    /**
     * Interface d'envoi aux participants (tous les numéros de la BDD)
     */
    public function participants_envoyer() {
        $this->load->model('Participant_model');
        
        $data['participants'] = $this->Participant_model->get_all_participants();
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $data['resultat'] = null;
        $data['message'] = '';
        $data['type_envoi'] = 'texte';
        $data['job_id'] = null;
        
        $this->load->view('whatsapp/participants_envoyer', $data);
    }
    
    /**
     * Synchroniser tous les participants de tous les groupes
     */
    public function synchroniser_participants() {
        $result = $this->whapi_lib->sync_all_groups_with_db();
        
        if ($result['success']) {
            $stats = $result['stats'];
            $this->session->set_flashdata('success', sprintf(
                '%d groupes synchronisés: %d nouveaux, %d mis à jour, %d supprimés',
                $stats['groups'],
                $stats['inserted'],
                $stats['updated'],
                $stats['deleted']
            ));
        } else {
            $this->session->set_flashdata('error', 'Erreur: ' . $result['error']);
        }
        
        redirect('whatsapp/participants_envoyer');
    }
    
    // ==================== ENVOI AUX PARTICIPANTS ====================
    
    /**
     * Initialiser l'upload par chunks pour participants
     */
    public function init_chunk_upload_participants() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['upload_id'])) {
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }
        
        $upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $input['upload_id']);
        $upload_dir = $this->chunks_dir . $upload_id . '/';
        
        if (!mkdir($upload_dir, 0777, true)) {
            echo json_encode(['success' => false, 'error' => 'Impossible de créer le dossier temporaire']);
            return;
        }
        
        // Détection type audio
        $type_envoi = $input['type_envoi'];
        $filetype = $input['filetype'];
        $filename = $input['filename'];
        
        $is_ogg_opus = (strpos($filetype, 'audio/ogg') !== false || 
                       strpos($filetype, 'ogg') !== false || 
                       strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'ogg');
        
        if (strpos($filetype, 'audio/') === 0 || $this->is_audio_file($filename) || $is_ogg_opus) {
            $type_envoi = 'audio';
        }
        
        $is_voice = $is_ogg_opus || !empty($input['voice']);
        
        $meta = [
            'upload_id' => $upload_id,
            'filename' => $filename,
            'filesize' => $input['filesize'],
            'filetype' => $filetype,
            'total_chunks' => $input['total_chunks'],
            'received_chunks' => [],
            'phones' => $input['phones'],
            'message' => $input['message'],
            'type_envoi' => $type_envoi,
            'is_voice' => $is_voice,
            'created_at' => time()
        ];
        
        file_put_contents($upload_dir . 'meta.json', json_encode($meta));
        
        log_message('info', "Upload participants initié: $upload_id - Voice: " . ($is_voice ? 'true' : 'false'));
        
        echo json_encode(['success' => true, 'upload_id' => $upload_id]);
    }
    
    /**
     * Recevoir un chunk pour participants
     */
    public function upload_chunk_participants() {
        header('Content-Type: application/json');
        
        $upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->input->post('upload_id'));
        $chunk_index = (int)$this->input->post('chunk_index');
        $total_chunks = (int)$this->input->post('total_chunks');
        
        $upload_dir = $this->chunks_dir . $upload_id . '/';
        
        if (!is_dir($upload_dir)) {
            echo json_encode(['success' => false, 'error' => 'Upload non trouvé']);
            return;
        }
        
        if (empty($_FILES['chunk_data']) || $_FILES['chunk_data']['error'] !== UPLOAD_ERR_OK) {
            $error = isset($_FILES['chunk_data']) ? $this->get_upload_error($_FILES['chunk_data']['error']) : 'Aucun fichier';
            echo json_encode(['success' => false, 'error' => 'Chunk invalide: ' . $error]);
            return;
        }
        
        $chunk_path = $upload_dir . 'chunk_' . $chunk_index . '.part';
        
        if (!move_uploaded_file($_FILES['chunk_data']['tmp_name'], $chunk_path)) {
            echo json_encode(['success' => false, 'error' => 'Erreur sauvegarde chunk']);
            return;
        }
        
        $meta_path = $upload_dir . 'meta.json';
        $meta = json_decode(file_get_contents($meta_path), true);
        $meta['received_chunks'][] = $chunk_index;
        $meta['received_chunks'] = array_unique($meta['received_chunks']);
        sort($meta['received_chunks']);
        file_put_contents($meta_path, json_encode($meta));
        
        echo json_encode([
            'success' => true, 
            'chunk' => $chunk_index + 1,
            'total' => $total_chunks,
            'received' => count($meta['received_chunks'])
        ]);
    }
    
    /**
     * Finaliser et envoyer aux participants
     */
    public function finalize_and_send_participants() {
        header('Content-Type: application/json');
        
        $upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->input->post('upload_id'));
        $upload_dir = $this->chunks_dir . $upload_id . '/';
        
        if (!is_dir($upload_dir)) {
            echo json_encode(['success' => false, 'error' => 'Upload non trouvé']);
            return;
        }
        
        $meta = json_decode(file_get_contents($upload_dir . 'meta.json'), true);
        
        // Assembler les chunks
        $final_filename = $upload_id . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $meta['filename']);
        $final_path = $this->jobs_dir . $final_filename;
        
        $out = fopen($final_path, 'wb');
        if (!$out) {
            echo json_encode(['success' => false, 'error' => 'Impossible de créer le fichier final']);
            return;
        }
        
        for ($i = 0; $i < $meta['total_chunks']; $i++) {
            $chunk_path = $upload_dir . 'chunk_' . $i . '.part';
            
            if (!file_exists($chunk_path)) {
                fclose($out);
                unlink($final_path);
                echo json_encode(['success' => false, 'error' => 'Chunk manquant: ' . ($i + 1)]);
                return;
            }
            
            fwrite($out, file_get_contents($chunk_path));
        }
        
        fclose($out);
        
        $final_size = filesize($final_path);
        $final_mime = mime_content_type($final_path);
        $is_ogg_opus = (strpos($final_mime, 'audio/ogg') !== false || strpos($final_mime, 'ogg') !== false);
        
        // Nettoyer les chunks
        array_map('unlink', glob($upload_dir . '*'));
        rmdir($upload_dir);
        
        // Créer le job
        $job_id = 'job_participants_' . $upload_id;
        
        $job = [
            'job_id' => $job_id,
            'phones' => $meta['phones'],
            'message' => $meta['message'],
            'type_envoi' => $meta['type_envoi'],
            'is_voice' => $meta['is_voice'] ?? false,
            'delai' => 1000,
            'file_info' => [
                'name' => $meta['filename'],
                'path' => $final_path,
                'size' => $final_size,
                'type' => $final_mime,
                'is_ogg_opus' => $is_ogg_opus
            ],
            'status' => 'processing',
            'progress' => 0,
            'current_phone' => null,
            'result' => [
                'total' => count($meta['phones']),
                'reussis' => 0,
                'echoues' => 0,
                'details' => []
            ],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->save_job($job_id, $job);
        
        // Lancer le traitement asynchrone
        $this->process_job_participants_async($job_id);
        
        echo json_encode([
            'success' => true,
            'job_id' => $job_id,
            'file_size' => $final_size,
            'is_voice' => $job['is_voice'],
            'mime_type' => $final_mime
        ]);
    }
    
    /**
     * Traitement asynchrone pour envoi aux participants
     */
    private function process_job_participants_async($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return;
        
        $phones = $job['phones'];
        $message = $job['message'];
        $type_envoi = $job['type_envoi'];
        $file_info = $job['file_info'];
        $is_voice = $job['is_voice'] ?? false;
        
        $filepath = $file_info ? $file_info['path'] : null;
        
        foreach ($phones as $index => $phone) {
            $job['current_phone'] = $phone;
            $job['progress'] = round((($index + 1) / count($phones)) * 100);
            $job['updated_at'] = date('Y-m-d H:i:s');
            $this->save_job($job_id, $job);
            
            $success = false;
            $error = null;
            
            try {
                // Formater le numéro pour Whapi
                $formatted_phone = $this->format_phone_for_whapi($phone);
                
                if ($type_envoi === 'texte') {
                    $result = $this->whapi_lib->envoyer_message($formatted_phone, $message);
                } else {
                    if ($type_envoi === 'audio') {
                        $result = $this->whapi_lib->envoyer_fichier_audio($formatted_phone, $filepath, $message, $is_voice);
                    } else {
                        $result = $this->whapi_lib->envoyer_fichier($formatted_phone, $filepath, $message);
                    }
                }
                
                $success = $result['success'] ?? false;
                if (!$success) {
                    $error = $result['error'] ?? 'Erreur inconnue';
                }
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            
            if ($success) {
                $job['result']['reussis']++;
            } else {
                $job['result']['echoues']++;
            }
            
            $job['result']['details'][] = [
                'destinataire_id' => $phone,
                'statut' => $success ? 'succès' : 'échec',
                'erreur' => $error,
                'index' => $index + 1
            ];
            
            $this->save_job($job_id, $job);
            
            if ($index < count($phones) - 1) {
                usleep(1000000);
            }
        }
        
        $job['status'] = 'completed';
        $job['progress'] = 100;
        $job['current_phone'] = null;
        $job['updated_at'] = date('Y-m-d H:i:s');
        $this->save_job($job_id, $job);
        
        if ($filepath && file_exists($filepath)) {
            @unlink($filepath);
        }
    }
    
    /**
     * Envoi texte simple aux participants
     */
    public function traiter_envoi_participants() {
        header('Content-Type: application/json');
        
        $phones = $this->input->post('phones');
        $message = trim($this->input->post('message') ?? '');
        $type_envoi = $this->input->post('type_envoi') ?: 'texte';
        
        if (empty($phones) || !is_array($phones)) {
            echo json_encode(['success' => false, 'error' => 'Aucun participant sélectionné']);
            return;
        }
        
        if ($type_envoi === 'texte' && empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Message requis']);
            return;
        }
        
        $job_id = 'job_participants_' . uniqid() . '_' . time();
        
        $job = [
            'job_id' => $job_id,
            'phones' => $phones,
            'message' => $message,
            'type_envoi' => $type_envoi,
            'is_voice' => false,
            'delai' => 1000,
            'file_info' => null,
            'status' => 'processing',
            'progress' => 0,
            'result' => [
                'total' => count($phones),
                'reussis' => 0,
                'echoues' => 0,
                'details' => []
            ],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->save_job($job_id, $job);
        $this->process_job_participants_async($job_id);
        
        echo json_encode([
            'success' => true,
            'job_id' => $job_id,
            'status' => 'processing'
        ]);
    }
    
    /**
     * Vérifier le statut d'un job participants
     */
    public function check_status_participants($job_id = null) {
        header('Content-Type: application/json');
        
        $job_id = $job_id ?? $this->input->get('job_id');
        
        if (!$job_id) {
            echo json_encode(['success' => false, 'error' => 'Job ID manquant']);
            return;
        }
        
        $job = $this->get_job($job_id);
        
        if (!$job) {
            echo json_encode(['success' => false, 'error' => 'Job non trouvé']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'job_id' => $job_id,
            'status' => $job['status'],
            'progress' => $job['progress'],
            'current_phone' => $job['current_phone'],
            'is_voice' => $job['is_voice'] ?? false,
            'result' => $job['status'] === 'completed' ? $job['result'] : null
        ]);
    }
    
    /**
     * Afficher le résultat pour participants
     */
    public function resultat_participants($job_id = null) {
        $this->load->model('Participant_model');
        
        $job_id = $job_id ?? $this->input->get('job_id');
        
        $data['participants'] = $this->Participant_model->get_all_participants();
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $data['resultat'] = null;
        $data['job_id'] = $job_id;
        
        if ($job_id) {
            $job = $this->get_job($job_id);
            if ($job && $job['status'] === 'completed') {
                $data['resultat'] = [
                    'success' => ($job['result']['reussis'] ?? 0) > 0,
                    'status_code' => 200,
                    'response' => $job['result'],
                    'is_voice' => $job['is_voice'] ?? false
                ];
                $data['message'] = $job['message'];
                $data['type_envoi'] = $job['type_envoi'];
            }
        }
        
        $this->load->view('whatsapp/participants_envoyer', $data);
    }
    
    // ==================== HELPERS ====================
    
    /**
     * Vérifier si c'est un fichier audio par extension
     */
    private function is_audio_file($filename) {
        $audio_exts = ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac', 'wma', 'opus', 'weba', 'webm'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $audio_exts);
    }
    
    /**
     * Sauvegarder un job dans un fichier JSON
     */
    private function save_job($job_id, $job) {
        file_put_contents($this->jobs_dir . $job_id . '.json', json_encode($job, JSON_PRETTY_PRINT));
    }
    
    /**
     * Récupérer un job depuis un fichier JSON
     */
    private function get_job($job_id) {
        $file = $this->jobs_dir . $job_id . '.json';
        return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
    }
    
    /**
     * Obtenir le message d'erreur d'upload
     */
    private function get_upload_error($code) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Fichier trop gros (php.ini)',
            UPLOAD_ERR_FORM_SIZE => 'Fichier trop gros (formulaire)',
            UPLOAD_ERR_PARTIAL => 'Upload partiel',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier',
            UPLOAD_ERR_NO_TMP_DIR => 'Pas de dossier temporaire',
            UPLOAD_ERR_CANT_WRITE => 'Erreur écriture',
            UPLOAD_ERR_EXTENSION => 'Extension bloquée'
        ];
        return $errors[$code] ?? "Erreur $code";
    }
    
    /**
     * Formater un numéro de téléphone pour Whapi
     */
    private function format_phone_for_whapi($phone) {
        // Supprimer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ajouter le suffixe WhatsApp si pas présent
        if (strpos($phone, '@') === false) {
            $phone = $phone . '@s.whatsapp.net';
        }
        
        return $phone;
    }
    
    /**
     * Nettoyage automatique des anciens jobs
     */
    public function cleanup() {
        $now = time();
        $deleted = 0;
        
        // Jobs vieux de +24h
        foreach (glob($this->jobs_dir . '*.json') as $file) {
            if ($now - filemtime($file) > 86400) {
                unlink($file);
                $deleted++;
            }
        }
        
        // Uploads incomplèts vieux de +2h
        foreach (glob($this->chunks_dir . '*', GLOB_ONLYDIR) as $dir) {
            $meta = $dir . '/meta.json';
            if (file_exists($meta)) {
                $data = json_decode(file_get_contents($meta), true);
                if ($now - $data['created_at'] > 7200) {
                    array_map('unlink', glob($dir . '/*'));
                    rmdir($dir);
                    $deleted++;
                }
            }
        }
        
        echo "Nettoyé: $deleted éléments";
    }

 /**
 * ✅ MODIFIÉ: Voir les participants (avec sync auto)
 */
public function participants($group_id = null) {
    if (!$group_id) {
        $this->session->set_flashdata('error', 'ID du groupe manquant');
        redirect('whatsapp/groupes');
        return;
    }
    
    $group_id = urldecode($group_id);
    
    // Charger le modèle
    $this->load->model('Participant_model');
    
    // Appel API avec sauvegarde automatique (save_to_db = true par défaut)
    $result = $this->whapi_lib->get_group_participants($group_id, true);
    
    if (!$result['success']) {
        // Si échec API, essayer de récupérer depuis la BDD
        $participants_db = $this->Participant_model->get_by_groupe($group_id);
        
        if (empty($participants_db)) {
            $this->session->set_flashdata('error', 'Erreur API: ' . $result['error'] . ' - Aucune donnée en cache');
            redirect('whatsapp/groupes');
            return;
        }
        
        // Utiliser les données en cache
        $data['group'] = [
            'group_id' => $group_id,
            'group_name' => 'Données en cache (API indisponible)',
            'participants_count' => count($participants_db)
        ];
        $data['participants'] = $participants_db;
        $data['from_cache'] = true;
        
        $this->session->set_flashdata('warning', 'Données récupérées du cache local');
    } else {
        // Données fraîches de l'API (déjà sauvegardées en BDD)
        $data['group'] = $result;
        $data['participants'] = $result['participants'];
        $data['sync_stats'] = $result['sync_stats'] ?? null;
        $data['from_cache'] = false;
    }
    
    $data['groupes'] = $this->Groupe_model->get_all_groupes();
    $this->load->view('whatsapp/participants', $data);
}
}

