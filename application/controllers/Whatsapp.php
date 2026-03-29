<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp extends MY_Controller {
    
    private $jobs_dir;
    
    public function __construct() {
        parent::__construct();
        $this->load->library('whapi_lib');
        $this->load->model('Groupe_model');
        $this->load->helper(array('form', 'url', 'file'));
        $this->load->library('upload');
        
        // Dossier pour les jobs
        $this->jobs_dir = FCPATH . 'uploads/jobs/';
        if (!is_dir($this->jobs_dir)) {
            mkdir($this->jobs_dir, 0777, true);
        }
        
        // Augmenter les limites pour les gros fichiers
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '512M');
        ini_set('post_max_size', '128M');
        ini_set('upload_max_filesize', '128M');
    }
    
    /**
     * Dashboard principal
     */
    public function index() {
        $data['stats'] = array(
            'total_groupes' => $this->Groupe_model->compter_groupes(),
            'groupes_actifs' => count($this->Groupe_model->get_all_groupes())
        );
        
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
     * Synchronisation des groupes
     */
    public function synchroniser() {
        $config = $this->config->item('whapi');
        if (!$config || empty($config['api_key'])) {
            $this->session->set_flashdata('error', 'Configuration Whapi manquante');
            redirect('whatsapp/groupes');
            return;
        }
        
        $test = $this->whapi_lib->test_connexion();
        if (!$test['success']) {
            $this->session->set_flashdata('error', 'Erreur connexion: ' . ($test['error'] ? $test['error'] : 'Inconnu'));
            redirect('whatsapp/groupes');
            return;
        }
        
        $resultat = $this->whapi_lib->get_groupes();
        
        if (!$resultat['success']) {
            $this->session->set_flashdata('error', 'Erreur récupération: ' . ($resultat['error'] ? $resultat['error'] : 'Inconnu'));
            redirect('whatsapp/groupes');
            return;
        }
        
        $groupes = isset($resultat['response']['groups']) ? $resultat['response']['groups'] : array();
        $compteur = 0;
        
        foreach ($groupes as $groupe) {
            $nom = isset($groupe['name']) ? $groupe['name'] : (isset($groupe['subject']) ? $groupe['subject'] : 'Groupe sans nom');
            $description = isset($groupe['description']) ? $groupe['description'] : (isset($groupe['desc']) ? $groupe['desc'] : '');
            
            $this->Groupe_model->sauvegarder($groupe['id'], $nom, $description);
            $compteur++;
        }
        
        $this->session->set_flashdata('success', $compteur . ' groupes synchronisés');
        redirect('whatsapp/groupes');
    }
    
    /**
     * Interface d'envoi style WhatsApp
     */
    public function envoyer() {
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $data['total_groupes'] = $this->Groupe_model->compter_groupes();
        $data['resultat'] = null;
        $data['message'] = '';
        $data['type_envoi'] = 'texte';
        $data['groupes_info'] = array();
        
        $this->load->view('whatsapp/envoyer_whatsapp_style', $data);
    }
    
    /**
     * NOUVEAU: Traitement asynchrone avec job ID
     * Retourne JSON immédiatement, traitement en arrière-plan
     */
    public function traiter_envoi() {
        // Désactiver le output buffering pour réponse immédiate
        ob_implicit_flush(true);
        ob_end_flush();
        
        $groupes_ids = $this->input->post('groupes_ids');
        $message_raw = $this->input->post('message');
        $type_envoi = $this->input->post('type_envoi');
        $delai = (int)$this->input->post('delai');
        
        // Normaliser message
        if (is_array($message_raw)) {
            $message = isset($message_raw[0]) ? trim($message_raw[0]) : '';
        } else {
            $message = trim((string)$message_raw);
        }
        
        if (empty($type_envoi)) {
            $type_envoi = 'texte';
        }
        
        if (empty($groupes_ids) || !is_array($groupes_ids)) {
            echo json_encode([
                'success' => false,
                'error' => 'Veuillez sélectionner au moins un groupe'
            ]);
            return;
        }
        
        $groupes_ids = array_map('trim', array_filter($groupes_ids));
        
        if (empty($groupes_ids)) {
            echo json_encode([
                'success' => false,
                'error' => 'Aucun groupe valide sélectionné'
            ]);
            return;
        }
        
        // Validation selon type
        if ($type_envoi === 'texte' && empty($message)) {
            echo json_encode([
                'success' => false,
                'error' => 'Le message est requis'
            ]);
            return;
        }
        
        // Créer job ID unique
        $job_id = 'job_' . uniqid() . '_' . time();
        
        // Sauvegarder le fichier si présent
        $file_info = null;
        if (!empty($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
            $file_ext = pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
            $file_name = $job_id . '.' . $file_ext;
            $file_path = $this->jobs_dir . $file_name;
            
            if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $file_path)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erreur sauvegarde fichier'
                ]);
                return;
            }
            
            $file_info = [
                'name' => $_FILES['fichier']['name'],
                'path' => $file_path,
                'size' => $_FILES['fichier']['size'],
                'type' => $_FILES['fichier']['type']
            ];
        }
        
        if (($type_envoi === 'fichier' || $type_envoi === 'audio') && !$file_info) {
            echo json_encode([
                'success' => false,
                'error' => 'Aucun fichier sélectionné'
            ]);
            return;
        }
        
        // Créer le job
        $job = [
            'job_id' => $job_id,
            'groupes_ids' => $groupes_ids,
            'message' => $message,
            'type_envoi' => $type_envoi,
            'delai' => $delai > 0 ? $delai : 1000,
            'file_info' => $file_info,
            'status' => 'pending',
            'progress' => 0,
            'current_group' => null,
            'result' => [
                'total' => count($groupes_ids),
                'reussis' => 0,
                'echoues' => 0,
                'details' => []
            ],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Sauvegarder le job
        $this->save_job($job_id, $job);
        
        // Lancer le traitement (dans un thread séparé si possible)
        // Pour l'instant, on traite immédiatement mais en chunks
        $this->process_job_async($job_id);
        
        // Retourner immédiatement
        echo json_encode([
            'success' => true,
            'job_id' => $job_id,
            'message' => 'Traitement démarré',
            'total_groups' => count($groupes_ids),
            'status' => 'processing'
        ]);
    }
    
    /**
     * Traiter le job - version asynchrone simulée
     */
    private function process_job_async($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return;
        
        // Mettre à jour statut
        $job['status'] = 'processing';
        $job['updated_at'] = date('Y-m-d H:i:s');
        $this->save_job($job_id, $job);
        
        // Traiter les envois un par un avec sauvegarde intermédiaire
        $groupes_ids = $job['groupes_ids'];
        $message = $job['message'];
        $type_envoi = $job['type_envoi'];
        $delai = $job['delai'];
        $file_info = $job['file_info'];
        
        $filepath = $file_info ? $file_info['path'] : null;
        
        foreach ($groupes_ids as $index => $groupe_id) {
            // Mettre à jour la progression
            $job['current_group'] = $groupe_id;
            $job['progress'] = round(($index / count($groupes_ids)) * 100);
            $job['updated_at'] = date('Y-m-d H:i:s');
            $this->save_job($job_id, $job);
            
            $success = false;
            $error = null;
            
            try {
                if ($type_envoi === 'texte' && !empty($message)) {
                    $result = $this->whapi_lib->envoyer_message($groupe_id, $message);
                } elseif (($type_envoi === 'fichier' || $type_envoi === 'audio') && $filepath && file_exists($filepath)) {
                    $result = $this->whapi_lib->envoyer_fichier($groupe_id, $filepath, $message);
                } else {
                    $result = ['success' => false, 'error' => 'Type invalide ou fichier manquant'];
                }
                
                $success = $result['success'] ?? false;
                if (!$success) {
                    $error = $result['error'] ?? 'Erreur inconnue';
                }
                
            } catch (Exception $e) {
                $error = $e->getMessage();
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
            
            // Délai entre les envois
            if ($index < count($groupes_ids) - 1) {
                usleep($delai * 1000);
            }
        }
        
        // Finaliser
        $job['status'] = 'completed';
        $job['progress'] = 100;
        $job['updated_at'] = date('Y-m-d H:i:s');
        $this->save_job($job_id, $job);
        
        // Nettoyer le fichier
        if ($filepath && file_exists($filepath)) {
            @unlink($filepath);
        }
    }
    
    /**
     * API: Vérifier le statut d'un job
     */
    public function check_status($job_id = null) {
        if (!$job_id) {
            $job_id = $this->input->get('job_id');
        }
        
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
     * Afficher le résultat final
     */
    public function resultat($job_id = null) {
        if (!$job_id) {
            $job_id = $this->input->get('job_id');
        }
        
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $data['total_groupes'] = $this->Groupe_model->compter_groupes();
        $data['resultat'] = null;
        $data['message'] = '';
        $data['type_envoi'] = 'texte';
        $data['groupes_info'] = array();
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
                $data['groupes_info'] = $this->_get_groupes_info($job['groupes_ids']);
            }
        }
        
        $this->load->view('whatsapp/envoyer_whatsapp_style', $data);
    }
    
    /**
     * Helpers pour les jobs
     */
    private function save_job($job_id, $job) {
        $file = $this->jobs_dir . $job_id . '.json';
        file_put_contents($file, json_encode($job, JSON_PRETTY_PRINT));
    }
    
    private function get_job($job_id) {
        $file = $this->jobs_dir . $job_id . '.json';
        if (!file_exists($file)) return null;
        return json_decode(file_get_contents($file), true);
    }
    
    /**
     * Envoi à tous les groupes (gardé pour compatibilité)
     */
    public function envoyer_a_tous() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_error('Méthode non autorisée', 405);
            return;
        }
        
        $message = $this->input->post('message');
        $confirm = $this->input->post('confirm');
        
        $groupes = $this->Groupe_model->get_all_groupes();
        
        if (empty($groupes)) {
            $this->session->set_flashdata('error', 'Aucun groupe trouvé');
            redirect('whatsapp/groupes');
            return;
        }
        
        $groupes_ids = array_column($groupes, 'groupe_id');
        
        if (!$confirm) {
            $data['message'] = $message;
            $data['groupes'] = $groupes;
            $data['total_groupes'] = count($groupes);
            $this->load->view('whatsapp/confirmation_envoi_tous', $data);
            return;
        }
        
        $resultat = $this->whapi_lib->envoyer_message_multi($groupes_ids, $message, array('delai_ms' => 1000));
        
        $data['resultat'] = $resultat;
        $data['groupes'] = $groupes;
        $data['message'] = $message;
        
        $this->load->view('whatsapp/resultat_envoi_tous', $data);
    }
    
    /**
     * Test d'un groupe
     */
    public function tester($groupe_id) {
        $message = "Test - " . date('Y-m-d H:i:s');
        $resultat = $this->whapi_lib->envoyer_message(urldecode($groupe_id), $message);
        
        if ($resultat['success']) {
            $this->session->set_flashdata('success', 'Message test envoyé');
        } else {
            $this->session->set_flashdata('error', 'Échec: ' . $resultat['error']);
        }
        
        redirect('whatsapp/groupes');
    }
    
    /**
     * API JSON groupes
     */
    public function api_groupes() {
        $groupes = $this->Groupe_model->get_ids_groupes();
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($groupes, JSON_PRETTY_PRINT));
    }
    
    /**
     * Helper: récupère les infos des groupes
     */
    private function _get_groupes_info($groupes_ids) {
        $infos = array();
        foreach ($groupes_ids as $id) {
            $groupe = $this->Groupe_model->get_groupe_par_id_whatsapp($id);
            if ($groupe) {
                $infos[] = $groupe;
            }
        }
        return $infos;
    }
    
    /**
     * Nettoyage des vieux jobs (à appeler via cron)
     */
    public function cleanup_jobs() {
        $files = glob($this->jobs_dir . 'job_*.json');
        $now = time();
        $deleted = 0;
        
        foreach ($files as $file) {
            $mtime = filemtime($file);
            // Supprimer les jobs de plus de 24h
            if ($now - $mtime > 86400) {
                @unlink($file);
                $deleted++;
            }
        }
        
        echo "Nettoyé: $deleted jobs";
    }
}