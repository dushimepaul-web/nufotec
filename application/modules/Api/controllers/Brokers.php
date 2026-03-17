<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author: Dushime Paul
 * Email: dushimeyesupaulin@gmail.com
 * Date: 27/02/2026
 */

class Brokers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('email');
        $this->load->database();
    }

    /**
     * Affiche le formulaire d'inscription des brokers
     */
    public function index()
    {    
        $sections = $this->get_sections('brokers-form'); 
        
        $data = [
            'title'  => 'Become Broker Partener',
            'hero'   => $sections['hero'] ?? null,
            'textes' => $sections['textes'] ?? [],
            'page'   => $sections['page'] ?? null,
            'pays'   => $this->Model->read('pays', [], 'pays', 'ASC')
        ];
        
        $this->load->view('Brokers_View', $data);
    }

    /**
     * Endpoint: /Api/brokers/Save
     */
    public function Save() {
        // Désactiver l'affichage des erreurs en production
        error_reporting(E_ALL);
        ini_set('display_errors', 0);

        // Configuration CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        header('Content-Type: application/json');

        // Gérer les requêtes OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        // Récupérer les données
        $input_data = $this->_get_input_data();
        
        if (empty($input_data)) {
            $this->_json_response(false, 'Aucune donnée reçue');
        }

        // Validation
        $errors = $this->_validate_broker_data($input_data);
        
        if (!empty($errors)) {
            $this->_json_response(false, 'Erreur de validation', ['errors' => $errors]);
        }

        // Préparation et insertion
        $insert_data = $this->_prepare_broker_data($input_data);
        
        $inserted = $this->db->insert('brokers', $insert_data);
        
        if (!$inserted) {
            $db_error = $this->db->error();
            log_message('error', 'Erreur insertion broker: ' . print_r($db_error, true));
            $this->_json_response(false, 'Erreur base de données: ' . $db_error['message']);
        }

        $insert_id = $this->db->insert_id();

        // Envoi des emails
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
     * Méthode de test
     */
    public function test() {
        $this->_json_response(true, 'API Brokers fonctionne', ['time' => date('Y-m-d H:i:s')]);
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
        exit(); // ← CRUCIAL !
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
            // Identification
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

            // Capacités
            'capacity_investment_broker' => !empty($input_data['capacity_investment_broker']) ? 1 : 0,
            'capacity_placement_agent' => !empty($input_data['capacity_placement_agent']) ? 1 : 0,
            'capacity_corporate_finance_advisor' => !empty($input_data['capacity_corporate_finance_advisor']) ? 1 : 0,
            'capacity_fund_manager' => !empty($input_data['capacity_fund_manager']) ? 1 : 0,
            'capacity_family_office_rep' => !empty($input_data['capacity_family_office_rep']) ? 1 : 0,
            'capacity_esg_advisor' => !empty($input_data['capacity_esg_advisor']) ? 1 : 0,
            'capacity_independent_introducer' => !empty($input_data['capacity_independent_introducer']) ? 1 : 0,
            'capacity_other' => $input_data['capacity_other'] ?? null,

            // Investisseurs
            'investor_private_equity' => !empty($input_data['investor_private_equity']) ? 1 : 0,
            'investor_venture_capital' => !empty($input_data['investor_venture_capital']) ? 1 : 0,
            'investor_esg_impact' => !empty($input_data['investor_esg_impact']) ? 1 : 0,
            'investor_dfi' => !empty($input_data['investor_dfi']) ? 1 : 0,
            'investor_institutional' => !empty($input_data['investor_institutional']) ? 1 : 0,
            'investor_hnwi' => !empty($input_data['investor_hnwi']) ? 1 : 0,
            'investor_sovereign' => !empty($input_data['investor_sovereign']) ? 1 : 0,
            'typical_ticket_size' => $input_data['typical_ticket_size'] ?? null,
            'geographic_coverage' => $input_data['geographic_coverage'] ?? null,

            // Mandats
            'mandate_equity' => !empty($input_data['mandate_equity']) ? 1 : 0,
            'mandate_structured_debt' => !empty($input_data['mandate_structured_debt']) ? 1 : 0,
            'mandate_blended_finance' => !empty($input_data['mandate_blended_finance']) ? 1 : 0,
            'mandate_grant' => !empty($input_data['mandate_grant']) ? 1 : 0,
            'mandate_strategic_partnership' => !empty($input_data['mandate_strategic_partnership']) ? 1 : 0,
            'mandate_full_program' => !empty($input_data['mandate_full_program']) ? 1 : 0,

            // Engagement
            'engagement_model' => $input_data['engagement_model'] ?? null,

            // Compliance
            'confirm_authorized' => !empty($input_data['confirm_authorized']) ? 1 : 0,
            'confirm_aml_kyc' => !empty($input_data['confirm_aml_kyc']) ? 1 : 0,
            'acknowledge_no_exclusivity' => !empty($input_data['acknowledge_no_exclusivity']) ? 1 : 0,
            'understand_formal_mandate_required' => !empty($input_data['understand_formal_mandate_required']) ? 1 : 0,
            
            // Timestamps
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Envoi des emails de notification
     */
    /**
 * Envoi des emails de notification via SendGrid
 */
private function _send_notification_emails($data)
{    
    try {
        // Charger SendGrid
        $this->load->library('Sendgrid_lib');

        $site_name = $this->Model->get_setting('site_name', 'AGF Phytomed');
        $admin_email = $this->Model->get_setting('admin_email', 'partnerships@agf-phytomed.com');
        $whatsapp = $this->Model->get_setting('site_phone', '68863945');

        // Pays
        $country_name = 'Unknown';
        if (!empty($data['id_pays'])) {
            $country = $this->db->get_where('pays', ['id' => $data['id_pays']])->row();
            $country_name = $country ? ($country->pays ?? $country->name ?? 'Unknown') : 'Unknown';
        }

        $format_bool = fn($v) => $v ? 'Yes' : 'No';

        // Capacités
        $capacities = [];
        $cap_map = [
            'capacity_investment_broker' => 'Investment Broker',
            'capacity_placement_agent' => 'Placement Agent',
            'capacity_corporate_finance_advisor' => 'Corporate Finance Advisor',
            'capacity_fund_manager' => 'Fund Manager',
            'capacity_family_office_rep' => 'Family Office Rep',
            'capacity_esg_advisor' => 'ESG Advisor',
            'capacity_independent_introducer' => 'Independent Introducer'
        ];
        foreach ($cap_map as $field => $label) {
            if (!empty($data[$field])) $capacities[] = $label;
        }
        if (!empty($data['capacity_other'])) $capacities[] = 'Other: ' . $data['capacity_other'];
        $caps = implode(', ', $capacities) ?: 'None';

        // Investisseurs
        $investor_types = [];
        if (!empty($data['investor_private_equity'])) $investor_types[] = 'PE/VC';
        if (!empty($data['investor_esg_impact'])) $investor_types[] = 'ESG/Impact';
        if (!empty($data['investor_dfi'])) $investor_types[] = 'DFI';
        if (!empty($data['investor_institutional'])) $investor_types[] = 'Institutional';
        if (!empty($data['investor_hnwi'])) $investor_types[] = 'HNWI/Family Office';
        if (!empty($data['investor_sovereign'])) $investor_types[] = 'Sovereign';
        $investors = implode(', ', $investor_types) ?: 'None';

        // Mandats
        $mandates = [];
        if (!empty($data['mandate_equity'])) $mandates[] = 'Equity';
        if (!empty($data['mandate_structured_debt'])) $mandates[] = 'Structured Debt';
        if (!empty($data['mandate_blended_finance'])) $mandates[] = 'Blended Finance';
        if (!empty($data['mandate_grant'])) $mandates[] = 'Grant';
        if (!empty($data['mandate_strategic_partnership'])) $mandates[] = 'Strategic Partnership';
        if (!empty($data['mandate_full_program'])) $mandates[] = 'Full Program';
        $mands = implode(', ', $mandates) ?: 'None';

        // ========== EMAIL ADMIN ==========
        $admin_subject = 'New Broker Registration';
        $admin_message = $this->_build_admin_email($data, $country_name, $caps, $investors, $mands, $format_bool, $site_name);
        
        $admin_result = $this->sendgrid_lib->send_email($admin_email, $admin_subject, $admin_message);
        $admin_sent = ($admin_result['status'] == 202 || $admin_result['status'] == 200);
        
        if (!$admin_sent) {
            log_message('error', 'SendGrid - Email admin broker échoué: ' . json_encode($admin_result));
        }

        // ========== EMAIL BROKER ==========
        $user_subject = 'Thank you for your interest in AGF Phytomed';
        $user_message = $this->_build_user_email($data, $whatsapp, $site_name);
        
        $user_result = $this->sendgrid_lib->send_email($data['email'], $user_subject, $user_message);
        $user_sent = ($user_result['status'] == 202 || $user_result['status'] == 200);
        
        if (!$user_sent) {
            log_message('error', 'SendGrid - Email broker échoué: ' . json_encode($user_result));
        }

        return ($admin_sent && $user_sent);

    } catch (Exception $e) {
        log_message('error', 'Exception email broker: ' . $e->getMessage());
        return false;
    }
}

    /**
     * Construction de l'email admin
     */
    private function _build_admin_email($data, $country_name, $caps, $investors, $mands, $format_bool, $site_name) {
        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden;'>
                <div style='background: #0B4F2E; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin:0;'>New Broker Application</h2>
                </div>
                <div style='padding: 25px;'>
                    <p><strong>Submitted:</strong> " . date('Y-m-d H:i') . "</p>
                    <p><strong>Name:</strong> {$data['full_name']}<br>
                    <strong>Firm:</strong> {$data['firm_name']}<br>
                    <strong>Country:</strong> $country_name<br>
                    <strong>Email:</strong> {$data['email']}<br>
                    <strong>Phone:</strong> {$data['mobile_phone']}</p>
                    
                    <p><strong>Capacity:</strong> $caps</p>
                    <p><strong>Investor types:</strong> $investors</p>
                    <p><strong>Ticket size:</strong> " . ($data['typical_ticket_size'] ?? 'Not specified') . "<br>
                    <strong>Geography:</strong> " . ($data['geographic_coverage'] ?? 'Not specified') . "</p>
                    <p><strong>Mandates:</strong> $mands</p>
                    <p><strong>Engagement model:</strong> " . ($data['engagement_model'] ?? 'Not specified') . "</p>
                    
                    <p><strong>Compliance:</strong><br>
                    Authorized: {$format_bool($data['confirm_authorized'])}<br>
                    AML/KYC: {$format_bool($data['confirm_aml_kyc'])}<br>
                    No exclusivity: {$format_bool($data['acknowledge_no_exclusivity'])}<br>
                    Formal mandate: {$format_bool($data['understand_formal_mandate_required'])}</p>
                    
                    <p style='text-align: center; margin-top: 30px;'>
                        <a href='" . base_url('Eoi_partners') . "' style='background: #0B4F2E; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px;'>View in Dashboard</a>
                    </p>
                </div>
                <div style='background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px;'>
                    Automated notification from $site_name.
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Construction de l'email utilisateur
     */
    private function _build_user_email($data, $whatsapp, $site_name) {
        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px;'>
            <div style='max-width: 500px; margin: auto; background: white; border-radius: 8px; overflow: hidden;'>
                <div style='background: #0B4F2E; color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin:0;'>Hello {$data['full_name']}!</h1>
                </div>
                <div style='padding: 30px;'>
                    <p>We are grateful for you approaching African Green Farmers LTD.</p>
                    <p>This is an automatic acknowledgement that we have received your broker registration.</p>
                    <p>Kindly expect feedback from us within <strong>two (2) working days</strong>.</p>
                    <p>In the unlikely event that you haven't heard from us within that period, please do not hesitate to call or send us a message via our WhatsApp number <strong>$whatsapp</strong> for a quicker reply.</p>
                    <p>Best Regards,<br>
                    <strong>Public Relation Officer</strong><br>
                    African Green Farmers LTD<br>
                    Muyinga, Burundi<br>
                    $whatsapp<br>
                    Email: <a href='mailto:partnerships@agf-phytomed.com'>partnerships@agf-phytomed.com</a></p>
                </div>
                <div style='background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px;'>
                    © " . date('Y') . " $site_name. All rights reserved.
                </div>
            </div>
        </body>
        </html>";
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