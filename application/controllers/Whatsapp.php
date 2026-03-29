<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp extends MY_Controller {
    
    private $jobs_dir;
    private $chunks_dir;
    private $chunk_size = 1572864; // 1.5 MB exactement
    
    public function __construct() {
        parent::__construct();
        $this->load->library('whapi_lib');
        $this->load->model('Groupe_model');
        $this->load->helper(['form', 'url', 'file']);
        
        // Dossiers
        $this->jobs_dir = FCPATH . 'uploads/jobs/';
        $this->chunks_dir = FCPATH . 'uploads/chunks/';
        
        foreach ([$this->jobs_dir, $this->chunks_dir] as $dir) {
            if (!is_dir($dir)) mkdir($dir, 0777, true);
        }
        
        // Configuration serveur pour chunks
        ini_set('max_execution_time', 0); // Illimité
        ini_set('memory_limit', '256M');
        ini_set('post_max_size', '2M'); // 2M suffit pour un chunk
        ini_set('upload_max_filesize', '2M');
    }
    
    /**
     * Dashboard
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
     * Liste des groupes
     */
    public function groupes() {
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $data['total'] = count($data['groupes']);
        $data['api_status'] = $this->whapi_lib->test_connexion();
        $this->load->view('whatsapp/groupes_liste', $data);
    }
    
    /**
     * Synchroniser avec Whapi
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
    
    /**
     * Interface d'envoi
     */
    public function envoyer() {
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $data['total_groupes'] = count($data['groupes']);
        $data['resultat'] = null;
        $this->load->view('whatsapp/envoyer_whatsapp_style', $data);
    }
    
    // ==================== UPLOAD PAR CHUNKS ====================
    
    /**
     * Étape 1: Initialiser l'upload
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
        
        // ✅ CORRECTION: Forcer type audio si MIME type audio
        $type_envoi = $input['type_envoi'];
        $filetype = $input['filetype'];
        $filename = $input['filename'];
        
        if (strpos($filetype, 'audio/') === 0 || $this->is_audio_file($filename)) {
            $type_envoi = 'audio';
            log_message('info', "Forçage type audio: $filename | MIME: $filetype");
        }
        
        // Sauvegarder les métadonnées
        $meta = [
            'upload_id' => $upload_id,
            'filename' => $filename,
            'filesize' => $input['filesize'],
            'filetype' => $filetype,
            'total_chunks' => $input['total_chunks'],
            'received_chunks' => [],
            'groupes_ids' => $input['groupes_ids'],
            'message' => $input['message'],
            'type_envoi' => $type_envoi, // Type corrigé
            'created_at' => time()
        ];
        
        file_put_contents($upload_dir . 'meta.json', json_encode($meta));
        
        log_message('info', "Whapi Upload initié: $upload_id - {$input['total_chunks']} chunks, Type: $type_envoi, " . 
                   round($input['filesize']/1024/1024, 2) . " MB");
        
        echo json_encode(['success' => true, 'upload_id' => $upload_id]);
    }
    
    /**
     * Vérifie si c'est un fichier audio par extension
     */
    private function is_audio_file($filename) {
        $audio_exts = ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac', 'wma', 'opus', 'weba', 'webm'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $audio_exts);
    }
    
    /**
     * Étape 2: Recevoir un chunk
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
        
        // Vérifier le chunk
        if (empty($_FILES['chunk_data']) || $_FILES['chunk_data']['error'] !== UPLOAD_ERR_OK) {
            $error = isset($_FILES['chunk_data']) ? $this->get_upload_error($_FILES['chunk_data']['error']) : 'Aucun fichier';
            log_message('error', "Chunk error: $error");
            echo json_encode(['success' => false, 'error' => 'Chunk invalide: ' . $error]);
            return;
        }
        
        // Sauvegarder le chunk
        $chunk_path = $upload_dir . 'chunk_' . $chunk_index . '.part';
        
        if (!move_uploaded_file($_FILES['chunk_data']['tmp_name'], $chunk_path)) {
            echo json_encode(['success' => false, 'error' => 'Erreur sauvegarde chunk']);
            return;
        }
        
        // Vérifier la taille
        $chunk_size = filesize($chunk_path);
        
        // Mettre à jour les métadonnées
        $meta_path = $upload_dir . 'meta.json';
        $meta = json_decode(file_get_contents($meta_path), true);
        $meta['received_chunks'][] = $chunk_index;
        $meta['received_chunks'] = array_unique($meta['received_chunks']);
        sort($meta['received_chunks']);
        file_put_contents($meta_path, json_encode($meta));
        
        log_message('debug', "Chunk reçu: $upload_id - chunk " . ($chunk_index + 1) . "/$total_chunks (" . 
                   round($chunk_size/1024, 2) . " KB)");
        
        echo json_encode([
            'success' => true, 
            'chunk' => $chunk_index + 1,
            'total' => $total_chunks,
            'received' => count($meta['received_chunks'])
        ]);
    }
    
    /**
     * Étape 3: Finaliser et envoyer via Whapi
     */
    public function finalize_and_send() {
        header('Content-Type: application/json');
        
        $upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->input->post('upload_id'));
        $upload_dir = $this->chunks_dir . $upload_id . '/';
        
        if (!is_dir($upload_dir)) {
            echo json_encode(['success' => false, 'error' => 'Upload non trouvé']);
            return;
        }
        
        // Lire les métadonnées
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
        log_message('info', "Fichier assemblé: $final_path (" . round($final_size/1024/1024, 2) . " MB)");
        
        // Nettoyer les chunks
        array_map('unlink', glob($upload_dir . '*'));
        rmdir($upload_dir);
        
        // Créer le job et lancer l'envoi Whapi
        $job_id = 'job_' . $upload_id;
        
        $job = [
            'job_id' => $job_id,
            'groupes_ids' => $meta['groupes_ids'],
            'message' => $meta['message'],
            'type_envoi' => $meta['type_envoi'], // Type déjà corrigé dans init
            'delai' => 1000,
            'file_info' => [
                'name' => $meta['filename'],
                'path' => $final_path,
                'size' => $final_size,
                'type' => $meta['filetype']
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
            'file_size' => $final_size
        ]);
    }
    
    /**
     * Traitement asynchrone de l'envoi Whapi
     */
    private function process_job_async($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return;
        
        $groupes_ids = $job['groupes_ids'];
        $message = $job['message'];
        $type_envoi = $job['type_envoi'];
        $file_info = $job['file_info'];
        
        $filepath = $file_info ? $file_info['path'] : null;
        
        // ✅ LOG détaillé pour debug audio
        if ($type_envoi === 'audio') {
            log_message('info', "TRAITEMENT AUDIO - Job: $job_id | Fichier: " . ($filepath ? basename($filepath) : 'AUCUN') . " | Groupes: " . count($groupes_ids));
        }
        
        foreach ($groupes_ids as $index => $groupe_id) {
            // Mettre à jour la progression
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
                    // ✅ Envoi fichier via Whapi - la librairie gère la conversion audio
                    $result = $this->whapi_lib->envoyer_fichier($groupe_id, $filepath, $message);
                    
                    // Log spécial pour audio
                    if ($type_envoi === 'audio') {
                        log_message('info', "Résultat envoi audio à $groupe_id: " . ($result['success'] ? 'SUCCÈS' : 'ÉCHEC') . ' - ' . ($result['error'] ?? 'OK'));
                    }
                }
                
                $success = $result['success'] ?? false;
                if (!$success) {
                    $error = $result['error'] ?? 'Erreur inconnue';
                    log_message('error', "Whapi erreur pour $groupe_id: " . $error);
                } else {
                    log_message('info', "Message envoyé à $groupe_id via Whapi");
                }
                
            } catch (Exception $e) {
                $error = $e->getMessage();
                log_message('error', "Exception pour $groupe_id: " . $error);
            }
            
            // Mettre à jour le résultat
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
            
            // Délai entre les envois (respecter les limites Whapi)
            if ($index < count($groupes_ids) - 1) {
                usleep(1000000); // 1 seconde entre chaque groupe
            }
        }
        
        // Finaliser
        $job['status'] = 'completed';
        $job['progress'] = 100;
        $job['current_group'] = null;
        $job['updated_at'] = date('Y-m-d H:i:s');
        $this->save_job($job_id, $job);
        
        // Nettoyer le fichier après envoi
        if ($filepath && file_exists($filepath)) {
            @unlink($filepath);
            log_message('info', "Fichier nettoyé: $filepath");
        }
    }
    
    /**
     * Envoi texte simple (sans chunks)
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
     * Vérifier le statut d'un job
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
            'result' => $job['status'] === 'completed' ? $job['result'] : null
        ]);
    }
    
    /**
     * Afficher le résultat
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
                    'response' => $job['result']
                ];
                $data['message'] = $job['message'];
                $data['type_envoi'] = $job['type_envoi'];
            }
        }
        
        $this->load->view('whatsapp/envoyer_whatsapp_style', $data);
    }
    
    // ==================== HELPERS ====================
    
    private function save_job($job_id, $job) {
        file_put_contents($this->jobs_dir . $job_id . '.json', json_encode($job, JSON_PRETTY_PRINT));
    }
    
    private function get_job($job_id) {
        $file = $this->jobs_dir . $job_id . '.json';
        return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
    }
    
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
     * Nettoyage automatique
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
}