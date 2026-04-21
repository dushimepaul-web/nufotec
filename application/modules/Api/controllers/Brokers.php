<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author: Dushime Paul
 * Email: dushimeyesupaulin@gmail.com
 * Date: 27/02/2026
 */

class Brokers extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('email');
        $this->load->database();
        $this->load->model('Model');
        
        // Configuration CORS pour toutes les réponses
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        header('Content-Type: application/json');
        
        // Gérer les requêtes OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Méthode appelée par la route /{lang}/Brokers-form
     * Vérifie si c'est une requête AJAX POST ou une requête normale
     */
    public function index()
    {
        // Si c'est une requête POST (soumission du formulaire)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->Save();
            return;
        }
        
        // Sinon, afficher le formulaire (requête GET)
        $this->show_form();
    }

    /**
     * Affiche le formulaire d'inscription des brokers (GET)
     */
    public function show_form()
    {
        $sections = $this->get_sections('brokers-form'); 
        
        $data = [
            'title'  => 'Become Broker Partner',
            'hero'   => $sections['hero'] ?? null,
            'textes' => $sections['textes'] ?? [],
            'page'   => $sections['page'] ?? null,
            'pays'   => $this->Model->read('pays', [], 'pays', 'ASC'),
            'lang'   => $this->input->get('lang') ?? 'fr'
        ];
        
        $this->load->view('Brokers_View', $data);
    }

    /**
     * Endpoint API pour sauvegarder les données (POST)
     */
    public function Save() {
        // Récupérer les données
        $input_data = $this->_get_input_data();
        
        if (empty($input_data)) {
            $this->_json_response(false, 'Aucune donnée reçue');
            return;
        }

        // Validation
        $errors = $this->_validate_broker_data($input_data);
        
        if (!empty($errors)) {
            $this->_json_response(false, 'Erreur de validation', ['errors' => $errors]);
            return;
        }

        // Préparation et insertion
        $insert_data = $this->_prepare_broker_data($input_data);
        
        $inserted = $this->db->insert('brokers', $insert_data);
        
        if (!$inserted) {
            $db_error = $this->db->error();
            log_message('error', 'Erreur insertion broker: ' . print_r($db_error, true));
            $this->_json_response(false, 'Erreur base de données: ' . $db_error['message']);
            return;
        }

        $insert_id = $this->db->insert_id();

        // Envoi des emails (optionnel)
        $email_sent = false;
        try {
            $email_sent = $this->_send_notification_emails($insert_data);
        } catch (Exception $e) {
            log_message('error', 'Exception email broker: ' . $e->getMessage());
        }

        // Succès
        $this->_json_response(true, 'Votre inscription a été enregistrée avec succès', [
            'email_sent' => $email_sent,
            'id' => $insert_id
        ]);
    }

    /**
     * Helper: Récupérer les données d'entrée (JSON ou POST)
     */
    private function _get_input_data() {
        $input_data = [];
        $content_type = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';
        
        if (strpos($content_type, 'application/json') !== false) {
            $json_input = file_get_contents('php://input');
            $input_data = json_decode($json_input, true);
            
            log_message('debug', 'JSON broker reçu: ' . $json_input);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'JSON invalide: ' . json_last_error_msg());
                return null;
            }
        } else {
            $input_data = $this->input->post();
            log_message('debug', 'POST broker reçu: ' . print_r($input_data, true));
        }
        
        return $input_data;
    }

    /**
     * Helper: Envoyer une réponse JSON et terminer
     */
    private function _json_response($success, $message, $extra = []) {
        $response = array_merge([
            'success' => $success,
            'message' => $message
        ], $extra);
        
        echo json_encode($response);
        exit();
    }

    /**
     * Validation des données broker
     */
    private function _validate_broker_data($input_data) {
        $errors = [];
        
        // Nom complet
        if (empty($input_data['full_name'])) {
            $errors['full_name'] = 'Le nom complet est requis';
        } elseif (strlen($input_data['full_name']) > 150) {
            $errors['full_name'] = 'Le nom ne doit pas dépasser 150 caractères';
        }

        // Nom de la société
        if (empty($input_data['firm_name'])) {
            $errors['firm_name'] = 'Le nom de la société est requis';
        } elseif (strlen($input_data['firm_name']) > 200) {
            $errors['firm_name'] = 'Le nom ne doit pas dépasser 200 caractères';
        }

        // Email
        if (empty($input_data['email'])) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!filter_var($input_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format d\'email invalide';
        } elseif (strlen($input_data['email']) > 150) {
            $errors['email'] = 'L\'email ne doit pas dépasser 150 caractères';
        } else {
            $this->db->where('email', $input_data['email']);
            if ($this->db->count_all_results('brokers') > 0) {
                $errors['email'] = 'Cet email est déjà enregistré';
            }
        }

        // Pays
        if (empty($input_data['id_pays'])) {
            $errors['id_pays'] = 'Le pays est requis';
        } else {
            $this->db->where('id', $input_data['id_pays']);
            if ($this->db->count_all_results('pays') === 0) {
                $errors['id_pays'] = 'Pays invalide';
            }
        }

        // Capacité (au moins une)
        $capacity_fields = [
            'capacity_investment_broker', 'capacity_placement_agent',
            'capacity_corporate_finance_advisor', 'capacity_fund_manager',
            'capacity_family_office_rep', 'capacity_esg_advisor',
            'capacity_independent_introducer'
        ];
        
        $has_capacity = false;
        foreach ($capacity_fields as $field) {
            if (!empty($input_data[$field]) && $input_data[$field] == 1) {
                $has_capacity = true;
                break;
            }
        }
        
        $capacity_other_filled = !empty($input_data['capacity_other']) && trim($input_data['capacity_other']) !== '';
        
        if (!$has_capacity && !$capacity_other_filled) {
            $errors['capacity'] = 'Veuillez sélectionner au moins une capacité ou préciser "Autre"';
        }

        // Compliance (toutes requises)
        $compliance_fields = [
            'confirm_authorized' => 'Vous devez confirmer être autorisé à représenter votre entreprise',
            'confirm_aml_kyc' => 'Vous devez confirmer la conformité AML/KYC',
            'acknowledge_no_exclusivity' => 'Vous devez reconnaître le caractère non exclusif',
            'understand_formal_mandate_required' => 'Vous devez comprendre qu\'un mandat formel est requis'
        ];
        
        foreach ($compliance_fields as $field => $message) {
            if (empty($input_data[$field]) || $input_data[$field] != 1) {
                $errors[$field] = $message;
            }
        }

        return $errors;
    }

    /**
     * Préparation des données pour insertion
     */
    private function _prepare_broker_data($input_data) {
        return [
            'full_name' => $input_data['full_name'],
            'firm_name' => $input_data['firm_name'],
            'jurisdiction_of_incorporation' => $input_data['jurisdiction_of_incorporation'] ?? null,
            'registration_number' => $input_data['registration_number'] ?? null,
            'regulatory_status' => $input_data['regulatory_status'] ?? null,
            'regulatory_authority' => $input_data['regulatory_authority'] ?? null,
            'id_pays' => $input_data['id_pays'],
            'email' => $input_data['email'],
            'mobile_phone' => $input_data['mobile_phone'] ?? null,
            'whatsapp' => $input_data['whatsapp'] ?? null,
            'corporate_website' => $input_data['corporate_website'] ?? null,
            'capacity_investment_broker' => !empty($input_data['capacity_investment_broker']) ? 1 : 0,
            'capacity_placement_agent' => !empty($input_data['capacity_placement_agent']) ? 1 : 0,
            'capacity_corporate_finance_advisor' => !empty($input_data['capacity_corporate_finance_advisor']) ? 1 : 0,
            'capacity_fund_manager' => !empty($input_data['capacity_fund_manager']) ? 1 : 0,
            'capacity_family_office_rep' => !empty($input_data['capacity_family_office_rep']) ? 1 : 0,
            'capacity_esg_advisor' => !empty($input_data['capacity_esg_advisor']) ? 1 : 0,
            'capacity_independent_introducer' => !empty($input_data['capacity_independent_introducer']) ? 1 : 0,
            'capacity_other' => $input_data['capacity_other'] ?? null,
            'investor_private_equity' => !empty($input_data['investor_private_equity']) ? 1 : 0,
            'investor_venture_capital' => !empty($input_data['investor_venture_capital']) ? 1 : 0,
            'investor_esg_impact' => !empty($input_data['investor_esg_impact']) ? 1 : 0,
            'investor_dfi' => !empty($input_data['investor_dfi']) ? 1 : 0,
            'investor_institutional' => !empty($input_data['investor_institutional']) ? 1 : 0,
            'investor_hnwi' => !empty($input_data['investor_hnwi']) ? 1 : 0,
            'investor_sovereign' => !empty($input_data['investor_sovereign']) ? 1 : 0,
            'typical_ticket_size' => $input_data['typical_ticket_size'] ?? null,
            'geographic_coverage' => $input_data['geographic_coverage'] ?? null,
            'mandate_equity' => !empty($input_data['mandate_equity']) ? 1 : 0,
            'mandate_structured_debt' => !empty($input_data['mandate_structured_debt']) ? 1 : 0,
            'mandate_blended_finance' => !empty($input_data['mandate_blended_finance']) ? 1 : 0,
            'mandate_grant' => !empty($input_data['mandate_grant']) ? 1 : 0,
            'mandate_strategic_partnership' => !empty($input_data['mandate_strategic_partnership']) ? 1 : 0,
            'mandate_full_program' => !empty($input_data['mandate_full_program']) ? 1 : 0,
            'engagement_model' => $input_data['engagement_model'] ?? null,
            'confirm_authorized' => !empty($input_data['confirm_authorized']) ? 1 : 0,
            'confirm_aml_kyc' => !empty($input_data['confirm_aml_kyc']) ? 1 : 0,
            'acknowledge_no_exclusivity' => !empty($input_data['acknowledge_no_exclusivity']) ? 1 : 0,
            'understand_formal_mandate_required' => !empty($input_data['understand_formal_mandate_required']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Envoi des emails de notification
     */
    private function _send_notification_emails($data) {
        // Implémentez votre logique d'envoi d'email ici
        // Pour l'instant, retourne true
        return true;
    }

    /**
     * Récupération des sections CMS
     */
    private function get_sections($slug = 'brokers-form') {
        $page = $this->Model->readOne('pages', [
            'slug' => $slug,
            'est_publiee' => 1
        ]);

        if (empty($page)) {
            log_message('debug', 'Page "' . $slug . '" non trouvée');
            return null;
        }

        $hero = $this->Model->readOne('sections_contenu', [
            'id_page'      => $page['id_page'],
            'type_section' => 'hero',
            'est_active'   => 1
        ]);

        if (!empty($hero) && !empty($hero['options_json'])) {
            $hero['options'] = json_decode($hero['options_json'], true);
        }

        $textes = $this->Model->read('sections_contenu', [
            'id_page'      => $page['id_page'],
            'type_section' => 'texte',
            'est_active'   => 1
        ], 'ordre', 'ASC');

        if (empty($textes)) {
            $textes = [];
        }

        foreach ($textes as &$texte) {
            $texte['options'] = !empty($texte['options_json']) 
                ? json_decode($texte['options_json'], true) 
                : [];
        }

        return [
            'page'   => $page,
            'hero'   => $hero,
            'textes' => $textes
        ];
    }
}