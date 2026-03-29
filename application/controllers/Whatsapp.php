<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('whapi_lib');
        $this->load->model('Groupe_model');
        $this->load->helper('form');
        $this->load->helper('url');
    }
    
    /**
     * Affiche la liste des groupes avec leurs noms et IDs
     */
    public function liste_groupes() {
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $data['total'] = count($data['groupes']);
        
        $this->load->view('whatsapp/groupes_liste_complete', $data);
    }
    
    /**
     * Formulaire d'envoi avec sélection par noms
     */
    public function envoyer_par_nom() {
        $data['groupes'] = $this->Groupe_model->get_all_groupes();
        $data['total_groupes'] = $this->Groupe_model->compter_groupes();
        
        $this->load->view('whatsapp/envoyer_par_nom', $data);
    }
    
    /**
     * Traite l'envoi vers des groupes sélectionnés par nom
     */
    public function traiter_envoi() {
        $groupes_ids = $this->input->post('groupes_ids');
        $message = $this->input->post('message');
        
        if (empty($groupes_ids) || empty($message)) {
            $this->session->set_flashdata('error', 'Veuillez sélectionner au moins un groupe et saisir un message');
            redirect('whatsapp/envoyer_par_nom');
            return;
        }
        
        // Récupérer les noms des groupes pour l'affichage
        $groupes_info = [];
        foreach ($groupes_ids as $groupe_id) {
            $groupe = $this->Groupe_model->get_groupe_par_id_whatsapp($groupe_id);
            if ($groupe) {
                $groupes_info[] = $groupe;
            }
        }
        
        // Envoyer les messages
        $resultat = $this->whapi_lib->envoyer_message_multigroupes($groupes_ids, $message);
        
        $data['resultat'] = $resultat;
        $data['groupes_info'] = $groupes_info;
        $data['message'] = $message;
        
        $this->load->view('whatsapp/resultat_envoi', $data);
    }
    
    /**
     * Synchronisation des groupes depuis Whapi
     * Récupère les IDs et les noms automatiquement
     */
   public function synchroniser() {
    // ACTIVER LES LOGS POUR DEBUG
    error_log("=== SYNC START ===");
    
    // Vérifier que la library est chargée
    if (!isset($this->whapi_lib)) {
        $this->load->library('whapi_lib');
        error_log("Whapi_lib chargé");
    }
    
    // Vérifier la configuration
    $config = $this->config->item('whapi');
    error_log("API Key: " . substr($config['api_key'], 0, 10) . "...");
    error_log("Base URL: " . $config['base_url']);
    
    // Tester d'abord la connexion
    $test = $this->whapi_lib->test_connexion();
    error_log("Test connexion: " . print_r($test, true));
    
    if (!$test['success']) {
        $error_msg = "Erreur de connexion à Whapi: ";
        if (isset($test['status_code'])) {
            $error_msg .= "HTTP " . $test['status_code'];
        }
        if (isset($test['error'])) {
            $error_msg .= " - " . $test['error'];
        }
        
        $this->session->set_flashdata('error', $error_msg);
        error_log($error_msg);
        redirect('whatsapp/liste_groupes');
        return;
    }
    
    // Récupérer les groupes
    $resultat = $this->whapi_lib->get_groupes();
    error_log("Résultat get_groupes: " . print_r($resultat, true));
    
    if (!$resultat['success']) {
        $error_msg = "Erreur lors de la récupération des groupes: ";
        if (isset($resultat['status_code'])) {
            $error_msg .= "HTTP " . $resultat['status_code'] . " - ";
        }
        if (isset($resultat['error'])) {
            $error_msg .= $resultat['error'];
        } elseif (isset($resultat['response']['error'])) {
            $error_msg .= $resultat['response']['error'];
        } else {
            $error_msg .= "Erreur inconnue";
        }
        
        $this->session->set_flashdata('error', $error_msg);
        error_log($error_msg);
        redirect('whatsapp/liste_groupes');
        return;
    }
    
    // Traitement du succès
    if (isset($resultat['response']['groups'])) {
        $compteur = 0;
        foreach ($resultat['response']['groups'] as $groupe) {
            $this->groupe_model->sauvegarder(
                $groupe['id'],
                $groupe['name'],
                $groupe['description'] ?? ''
            );
            $compteur++;
        }
        
        $this->session->set_flashdata('success', $compteur . ' groupes synchronisés avec succès');
        error_log("$compteur groupes synchronisés");
    } else {
        $this->session->set_flashdata('warning', 'Aucun groupe trouvé sur votre compte WhatsApp');
        error_log("Aucun groupe trouvé");
    }
    
    redirect('whatsapp/liste_groupes');
}
    /**
     * API: Récupère la liste des groupes (JSON)
     */
    public function api_groupes() {
        $groupes = $this->Groupe_model->get_ids_groupes();
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($groupes, JSON_PRETTY_PRINT));
    }

   public function test_direct() {
    echo "<h1>Test direct Whapi</h1>";
    
    // Afficher la configuration
    $config = $this->config->item('whapi');
    echo "<h3>Configuration:</h3>";
    echo "<pre>";
    echo "API Key: " . substr($config['api_key'], 0, 10) . "..." . substr($config['api_key'], -5) . "\n";
    echo "Base URL: " . $config['base_url'] . "\n";
    echo "</pre>";
    
    // Test avec cURL simple
    echo "<h3>Test cURL simple vers health:</h3>";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://gate.whapi.cloud/health');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $config['api_key']
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: " . $http_code . "<br>";
    if ($error) {
        echo "Erreur cURL: " . $error . "<br>";
    } else {
        echo "Réponse: " . $response . "<br>";
        $data = json_decode($response, true);
        if ($data) {
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        }
    }
    
    // Test de récupération des groupes
    echo "<h3>Test récupération groupes:</h3>";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://gate.whapi.cloud/groups');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $config['api_key']
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: " . $http_code . "<br>";
    if ($error) {
        echo "Erreur cURL: " . $error . "<br>";
    } else {
        echo "Réponse reçue (" . strlen($response) . " bytes)<br>";
        $data = json_decode($response, true);
        if ($data && isset($data['groups'])) {
            echo "<h4>Groupes trouvés: " . count($data['groups']) . "</h4>";
            echo "<pre>";
            foreach ($data['groups'] as $g) {
                echo "- " . $g['name'] . " (" . $g['id'] . ")\n";
            }
            echo "</pre>";
        } else {
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        }
    }
}
}