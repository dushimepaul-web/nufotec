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
        if (!$config || empty($config['api_key'])) {
            $this->session->set_flashdata('error', 'Configuration Whapi manquante');
            redirect('whatsapp/liste_groupes');
            return;
        }
        
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
        
        // Traitement du succès - CORRECTION ICI
        if (isset($resultat['response']['groups']) && is_array($resultat['response']['groups'])) {
            $compteur = 0;
            foreach ($resultat['response']['groups'] as $groupe) {
                // CORRECTION: L'API peut retourner 'name' ou 'subject'
                $nom_groupe = isset($groupe['name']) ? $groupe['name'] : (isset($groupe['subject']) ? $groupe['subject'] : 'Groupe sans nom');
                $description = isset($groupe['description']) ? $groupe['description'] : (isset($groupe['desc']) ? $groupe['desc'] : '');
                
                $this->Groupe_model->sauvegarder(
                    $groupe['id'],
                    $nom_groupe,
                    $description
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
        // Nettoyer le buffer de sortie
        ob_clean();
        
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
                    // CORRECTION: Utiliser 'name' ou 'subject'
                    $nom = isset($g['name']) ? $g['name'] : (isset($g['subject']) ? $g['subject'] : 'Sans nom');
                    echo "- " . $nom . " (" . $g['id'] . ")\n";
                }
                echo "</pre>";
            } else {
                echo "<pre>";
                print_r($data);
                echo "</pre>";
            }
        }
        
        exit; // Arrêter l'exécution pour éviter les headers already sent
    }
    
    /**
     * Ajouter une méthode pour voir le contenu de la table
     */
    public function voir_groupes() {
        $groupes = $this->Groupe_model->get_all_groupes();
        
        echo "<h1>Groupes en base de données</h1>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Groupe ID (WhatsApp)</th><th>Nom</th><th>Description</th><th>Actif</th></tr>";
        
        foreach ($groupes as $g) {
            echo "<tr>";
            echo "<td>" . $g['id'] . "</td>";
            echo "<td>" . htmlspecialchars($g['groupe_id']) . "</td>";
            echo "<td>" . htmlspecialchars($g['nom']) . "</td>";
            echo "<td>" . htmlspecialchars($g['description']) . "</td>";
            echo "<td>" . ($g['actif'] ? 'Oui' : 'Non') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<p>Total: " . count($groupes) . " groupes</p>";
        echo "<br><a href='" . site_url('whatsapp/liste_groupes') . "'>Retour à la liste</a>";
        exit;
    }

    /**
 * Méthode de test pour envoyer un message à un groupe spécifique
 * Utilisée par le bouton "Test" dans la liste des groupes
 */
public function envoyer_test() {
    // Récupérer les paramètres
    $groupe_id = $this->input->get('groupe_id');
    $message = $this->input->get('message');
    
    // Vérifier les paramètres
    if (empty($groupe_id) || empty($message)) {
        $this->session->set_flashdata('error', 'Paramètres manquants pour le test');
        redirect('whatsapp/liste_groupes');
        return;
    }
    
    // Décoder l'ID du groupe (l'URL l'a déjà décodé, mais on s'assure)
    $groupe_id = urldecode($groupe_id);
    
    // Charger la library
    $this->load->library('whapi_lib');
    
    // Envoyer le message
    $resultat = $this->whapi_lib->envoyer_message_groupe($groupe_id, $message);
    
    // Récupérer le nom du groupe pour l'affichage
    $groupe = $this->Groupe_model->get_groupe_par_id_whatsapp($groupe_id);
    $nom_groupe = $groupe ? $groupe['nom'] : $groupe_id;
    
    if ($resultat['success']) {
        $this->session->set_flashdata('success', "Message test envoyé avec succès au groupe '{$nom_groupe}'");
    } else {
        $error_msg = "Échec de l'envoi au groupe '{$nom_groupe}': ";
        if (isset($resultat['status_code'])) {
            $error_msg .= "HTTP " . $resultat['status_code'] . " - ";
        }
        $error_msg .= $resultat['error'] ?? 'Erreur inconnue';
        $this->session->set_flashdata('error', $error_msg);
    }
    
    redirect('whatsapp/liste_groupes');
}

/**
 * Envoie un message à tous les groupes actifs
 */
public function envoyer_a_tous() {
    // Vérifier si c'est une requête POST
    if ($this->input->server('REQUEST_METHOD') !== 'POST') {
        show_error('Méthode non autorisée', 405);
        return;
    }
    
    $message = $this->input->post('message');
    
    if (empty($message)) {
        $this->session->set_flashdata('error', 'Veuillez saisir un message');
        redirect('whatsapp/liste_groupes');
        return;
    }
    
    // Récupérer tous les groupes actifs
    $groupes = $this->Groupe_model->get_all_groupes();
    
    if (empty($groupes)) {
        $this->session->set_flashdata('error', 'Aucun groupe trouvé. Synchronisez d\'abord les groupes.');
        redirect('whatsapp/liste_groupes');
        return;
    }
    
    // Extraire les IDs des groupes
    $groupes_ids = array_column($groupes, 'groupe_id');
    $total_groupes = count($groupes_ids);
    
    // Confirmation avant envoi (optionnel)
    $confirm = $this->input->post('confirm');
    if (!$confirm) {
        // Afficher une page de confirmation
        $data['message'] = $message;
        $data['groupes'] = $groupes;
        $data['total_groupes'] = $total_groupes;
        $this->load->view('whatsapp/confirmation_envoi_tous', $data);
        return;
    }
    
    // Envoyer le message à tous les groupes
    $resultat = $this->whapi_lib->envoyer_message_multigroupes($groupes_ids, $message, 1000);
    
    // Préparer les résultats
    $data['resultat'] = $resultat;
    $data['groupes'] = $groupes;
    $data['message'] = $message;
    $data['total_groupes'] = $total_groupes;
    
    $this->load->view('whatsapp/resultat_envoi_tous', $data);
}

/**
 * API: Envoyer un message à tous les groupes via API
 */
public function api_envoyer_tous() {
    // Vérifier la clé API
    $api_key = $this->input->get_request_header('X-API-Key');
    $config = $this->config->item('whapi');
    
    if (!$api_key || $api_key != $config['api_key']) {
        $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode(['error' => 'Clé API invalide']));
        return;
    }
    
    $message = $this->input->post('message');
    
    if (empty($message)) {
        $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode(['error' => 'Message requis']));
        return;
    }
    
    // Récupérer tous les groupes
    $groupes = $this->Groupe_model->get_all_groupes();
    $groupes_ids = array_column($groupes, 'groupe_id');
    
    $resultat = $this->whapi_lib->envoyer_message_multigroupes($groupes_ids, $message);
    
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($resultat));
}
/**
 * Formulaire d'envoi avec fichier
 */
public function envoyer_fichier() {
    $data['groupes'] = $this->Groupe_model->get_all_groupes();
    $data['total_groupes'] = $this->Groupe_model->compter_groupes();
    $data['types_fichiers'] = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        'video' => ['mp4', 'avi', 'mov', 'wmv', 'mkv'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a']
    ];
    
    $this->load->view('whatsapp/envoyer_fichier', $data);
}

/**
 * Traite l'envoi de fichier
 */
public function traiter_envoi_fichier() {
    $groupes_ids = $this->input->post('groupes_ids');
    $caption = $this->input->post('caption');
    $fichier = $_FILES['fichier'] ?? null;
    
    if (empty($groupes_ids)) {
        $this->session->set_flashdata('error', 'Veuillez sélectionner au moins un groupe');
        redirect('whatsapp/envoyer_fichier');
        return;
    }
    
    if (!$fichier || $fichier['error'] != UPLOAD_ERR_OK) {
        $this->session->set_flashdata('error', 'Veuillez sélectionner un fichier valide');
        redirect('whatsapp/envoyer_fichier');
        return;
    }
    
    // Vérifier la taille du fichier (max 16MB pour WhatsApp)
    $max_size = 16 * 1024 * 1024; // 16MB
    if ($fichier['size'] > $max_size) {
        $this->session->set_flashdata('error', 'Le fichier est trop volumineux (max 16MB)');
        redirect('whatsapp/envoyer_fichier');
        return;
    }
    
    // Créer le dossier d'upload s'il n'existe pas
    $upload_dir = FCPATH . 'uploads/whatsapp/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Générer un nom unique pour le fichier
    $extension = pathinfo($fichier['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Déplacer le fichier
    if (!move_uploaded_file($fichier['tmp_name'], $filepath)) {
        $this->session->set_flashdata('error', 'Erreur lors de l\'upload du fichier');
        redirect('whatsapp/envoyer_fichier');
        return;
    }
    
    // Récupérer les noms des groupes
    $groupes_info = [];
    foreach ($groupes_ids as $groupe_id) {
        $groupe = $this->Groupe_model->get_groupe_par_id_whatsapp($groupe_id);
        if ($groupe) {
            $groupes_info[] = $groupe;
        }
    }
    
    // Envoyer le fichier
    $resultat = $this->whapi_lib->envoyer_fichier_multigroupes($groupes_ids, $filepath, $caption);
    
    // Supprimer le fichier temporaire
    @unlink($filepath);
    
    $data['resultat'] = $resultat;
    $data['groupes_info'] = $groupes_info;
    $data['caption'] = $caption;
    $data['fichier_nom'] = $fichier['name'];
    $data['fichier_type'] = $fichier['type'];
    
    $this->load->view('whatsapp/resultat_envoi_fichier', $data);
}
}