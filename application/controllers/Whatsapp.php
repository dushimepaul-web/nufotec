<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('whapi_lib');
        $this->load->model('Groupe_model');
        $this->load->helper(array('form', 'url', 'file'));
        $this->load->library('upload');
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
     * Traitement de l'envoi - CORRIGÉ
     */
    public function traiter_envoi() {
    $groupes_ids = $this->input->post('groupes_ids');
    
    // ✅ CORRECTION: Forcer $message en string (évite l'erreur htmlspecialchars)
    $message_raw = $this->input->post('message');
    if (is_array($message_raw)) {
        $message = isset($message_raw[0]) ? trim($message_raw[0]) : '';
    } else {
        $message = trim((string)$message_raw);
    }
    
    $type_envoi = $this->input->post('type_envoi');
    
    if (empty($type_envoi)) {
        $type_envoi = 'texte';
    }
    
    if (empty($groupes_ids) || !is_array($groupes_ids)) {
        $this->session->set_flashdata('error', 'Veuillez sélectionner au moins un groupe');
        redirect('whatsapp/envoyer');
        return;
    }
    
    $groupes_ids = array_map('trim', $groupes_ids);
    $groupes_ids = array_filter($groupes_ids);
    
    if (empty($groupes_ids)) {
        $this->session->set_flashdata('error', 'Aucun groupe valide sélectionné');
        redirect('whatsapp/envoyer');
        return;
    }
    
    $delai = (int)$this->input->post('delai');
    if ($delai <= 0) {
        $delai = 1000;
    }
    
    $resultat = null;
    
    if ($type_envoi === 'texte') {
        if (empty($message)) {
            $this->session->set_flashdata('error', 'Le message est requis');
            redirect('whatsapp/envoyer');
            return;
        }
        
        $resultat = $this->whapi_lib->envoyer_message_multi($groupes_ids, $message, array('delai_ms' => $delai));
        
    } elseif ($type_envoi === 'fichier') {
        $resultat = $this->_traiter_envoi_fichier($groupes_ids, $message, $delai);
    }
    
    $data['groupes'] = $this->Groupe_model->get_all_groupes();
    $data['total_groupes'] = $this->Groupe_model->compter_groupes();
    $data['resultat'] = $resultat;
    $data['groupes_info'] = $this->_get_groupes_info($groupes_ids);
    $data['message'] = $message; // ✅ Maintenant garanti string
    $data['type_envoi'] = $type_envoi;
    
    $this->load->view('whatsapp/envoyer_whatsapp_style', $data);
}
    
    /**
     * Traitement fichier privé
     */
    private function _traiter_envoi_fichier($groupes_ids, $caption, $delai) {
        if (empty($_FILES['fichier']['name'])) {
            return array(
                'success' => false,
                'status_code' => 400,
                'response' => array(
                    'total' => count($groupes_ids),
                    'reussis' => 0,
                    'echoues' => count($groupes_ids),
                    'details' => array()
                ),
                'error' => 'Aucun fichier sélectionné'
            );
        }
        
        if ($_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
            return array(
                'success' => false,
                'status_code' => 400,
                'response' => array(
                    'total' => count($groupes_ids),
                    'reussis' => 0,
                    'echoues' => count($groupes_ids),
                    'details' => array()
                ),
                'error' => 'Erreur upload: ' . $_FILES['fichier']['error']
            );
        }
        
        $upload_dir = FCPATH . 'uploads/whatsapp/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $config['upload_path'] = $upload_dir;
        $config['allowed_types'] = '*';
        $config['max_size'] = 16384;
        $config['encrypt_name'] = true;
        
        $this->upload->initialize($config);
        
        if (!$this->upload->do_upload('fichier')) {
            return array(
                'success' => false,
                'status_code' => 400,
                'response' => array(
                    'total' => count($groupes_ids),
                    'reussis' => 0,
                    'echoues' => count($groupes_ids),
                    'details' => array()
                ),
                'error' => $this->upload->display_errors('', '')
            );
        }
        
        $upload_data = $this->upload->data();
        $filepath = $upload_data['full_path'];
        
        $resultat = $this->whapi_lib->envoyer_fichier_multigroupes($groupes_ids, $filepath, $caption, $delai);
        
        @unlink($filepath);
        
        return $resultat;
    }
    
    /**
     * Envoi à tous les groupes
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
}