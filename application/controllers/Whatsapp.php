<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp extends MY_Controller {
    
    private $jobs_dir;
    private $uploads_dir;
    private $chunk_size = 1572864; // 1.5 MB exactement
    
    public function __construct() {
        parent::__construct();
        $this->load->library('whapi_lib');
        $this->load->model('Groupe_model');
        $this->load->helper(array('form', 'url', 'file'));
        $this->load->library('upload');
        
        // Dossiers
        $this->jobs_dir = FCPATH . 'uploads/jobs/';
        $this->uploads_dir = FCPATH . 'uploads/chunks/';
        
        foreach ([$this->jobs_dir, $this->uploads_dir] as $dir) {
            if (!is_dir($dir)) mkdir($dir, 0777, true);
        }
        
        // Limites serveur - illimité pour les chunks
        ini_set('max_execution_time', 0); // 0 = illimité
        ini_set('memory_limit', '512M');
        ini_set('post_max_size', '2M'); // Seulement 2M nécessaire pour un chunk
        ini_set('upload_max_filesize', '2M'); // 2M pour un chunk
    }
    
    // ... méthodes existantes (index, groupes, synchroniser, envoyer) ...
    
    /**
     * Initialiser un upload chunked
     */
    public function init_upload() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['upload_id'])) {
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }
        
        $upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $input['upload_id']);
        $upload_dir = $this->uploads_dir . $upload_id . '/';
        
        if (!mkdir($upload_dir, 0777, true)) {
            echo json_encode(['success' => false, 'error' => 'Impossible de créer le dossier']);
            return;
        }
        
        // Sauvegarder les métadonnées
        $meta = [
            'upload_id' => $upload_id,
            'filename' => $input['filename'],
            'filesize' => $input['filesize'],
            'filetype' => $input['filetype'],
            'total_chunks' => $input['total_chunks'],
            'received_chunks' => [],
            'groupes_ids' => $input['groupes_ids'],
            'message' => $input['message'],
            'type_envoi' => $input['type_envoi'],
            'created_at' => time()
        ];
        
        file_put_contents($upload_dir . 'meta.json', json_encode($meta));
        
        log_message('debug', 'Upload initié: ' . $upload_id . ' - ' . $input['total_chunks'] . ' chunks attendus');
        
        echo json_encode(['success' => true, 'upload_id' => $upload_id]);
    }
    
    /**
     * Recevoir un chunk
     */
    public function upload_chunk() {
        header('Content-Type: application/json');
        
        $upload_id = $this->input->post('upload_id');
        $chunk_index = (int)$this->input->post('chunk_index');
        $total_chunks = (int)$this->input->post('total_chunks');
        
        // Nettoyer l'ID
        $upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $upload_id);
        $upload_dir = $this->uploads_dir . $upload_id . '/';
        
        if (!is_dir($upload_dir)) {
            echo json_encode(['success' => false, 'error' => 'Upload non trouvé']);
            return;
        }
        
        // Vérifier le chunk
        if (empty($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
            $error = isset($_FILES['chunk']) ? $_FILES['chunk']['error'] : 'Aucun fichier';
            echo json_encode(['success' => false, 'error' => 'Chunk invalide: ' . $error]);
            return;
        }
        
        // Sauvegarder le chunk
        $chunk_path = $upload_dir . 'chunk_' . $chunk_index . '.part';
        
        if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path)) {
            echo json_encode(['success' => false, 'error' => 'Erreur sauvegarde chunk']);
            return;
        }
        
        // Mettre à jour les métadonnées
        $meta_path = $upload_dir . 'meta.json';
        $meta = json_decode(file_get_contents($meta_path), true);
        $meta['received_chunks'][] = $chunk_index;
        file_put_contents($meta_path, json_encode($meta));
        
        log_message('debug', 'Chunk reçu: ' . $upload_id . ' - chunk ' . ($chunk_index + 1) . '/' . $total_chunks);
        
        echo json_encode([
            'success' => true, 
            'chunk' => $chunk_index + 1,
            'total' => $total_chunks
        ]);
    }
    
    /**
     * Finaliser l'upload et créer le job
     */
    public function finalize_upload() {
        header('Content-Type: application/json');
        
        $upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->input->post('upload_id'));
        $upload_dir = $this->uploads_dir . $upload_id . '/';
        
        if (!is_dir($upload_dir)) {
            echo json_encode(['success' => false, 'error' => 'Upload non trouvé']);
            return;
        }
        
        // Lire les métadonnées
        $meta_path = $upload_dir . 'meta.json';
        $meta = json_decode(file_get_contents($meta_path), true);
        
        // Assembler les chunks
        $final_path = $this->jobs_dir . $upload_id . '_' . $meta['filename'];
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
                echo json_encode(['success' => false, 'error' => 'Chunk manquant: ' . $i]);
                return;
            }
            
            fwrite($out, file_get_contents($chunk_path));
        }
        
        fclose($out);
        
        // Vérifier la taille
        $final_size = filesize($final_path);
        if ($final_size != $meta['filesize']) {
            log_message('warning', 'Taille finale différente: attendu ' . $meta['filesize'] . ', reçu ' . $final_size);
        }
        
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
            'delai' => 1000,
            'file_info' => [
                'name' => $meta['filename'],
                'path' => $final_path,
                'size' => $final_size,
                'type' => $meta['filetype']
            ],
            'status' => 'pending',
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
        
        // Lancer le traitement en arrière-plan
        $this->process_job_async($job_id);
        
        log_message('debug', 'Upload finalisé: ' . $job_id . ' - Taille: ' . ($final_size/1024/1024) . ' MB');
        
        echo json_encode([
            'success' => true,
            'job_id' => $job_id,
            'file_size' => $final_size
        ]);
    }
    
    /**
     * Traiter l'envoi (modifié pour supporter chunked)
     */
    public function traiter_envoi() {
        header('Content-Type: application/json');
        
        $is_chunked = $this->input->post('is_chunked');
        
        // Si c'est un envoi chunked, il est déjà géré par finalize_upload
        if ($is_chunked === 'false' || $is_chunked === false) {
            $this->traiter_envoi_simple();
            return;
        }
        
        // Ancienne méthode pour compatibilité
        $this->traiter_envoi_ancien();
    }
    
    /**
     * Traiter envoi simple (texte uniquement)
     */
    private function traiter_envoi_simple() {
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
            'status' => 'pending',
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
     * Ancienne méthode (conservée pour compatibilité)
     */
    private function traiter_envoi_ancien() {
        // ... votre ancien code traiter_envoi ici ...
        // Mais il ne devrait plus être utilisé
    }
    
    // ... reste des méthodes (process_job_async, check_status, resultat, etc.) ...
    
    /**
     * Cleanup amélioré
     */
    public function cleanup_jobs() {
        // Nettoyer les vieux jobs
        $files = glob($this->jobs_dir . '*');
        $now = time();
        $deleted = 0;
        
        foreach ($files as $file) {
            if (is_file($file) && $now - filemtime($file) > 86400) {
                unlink($file);
                $deleted++;
            }
        }
        
        // Nettoyer les uploads incomplèts
        $upload_dirs = glob($this->uploads_dir . '*', GLOB_ONLYDIR);
        foreach ($upload_dirs as $dir) {
            $meta_file = $dir . '/meta.json';
            if (file_exists($meta_file)) {
                $meta = json_decode(file_get_contents($meta_file), true);
                // Supprimer si plus vieux que 2 heures
                if ($now - $meta['created_at'] > 7200) {
                    array_map('unlink', glob($dir . '/*'));
                    rmdir($dir);
                    $deleted++;
                }
            }
        }
        
        echo "Nettoyé: $deleted fichiers/dossiers";
    }
}