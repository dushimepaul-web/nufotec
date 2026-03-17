<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author: Dushime Paul
 * Email: dushimeyesupaulin@gmail.com
 * Date: 27/02/2026
 */

class Investors extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('email');
        $this->load->database();
        
        // Charger le modèle pour les settings si nécessaire
        if (!isset($this->Model)) {
            $this->load->model('Model');
        }
    }

    /**
     * Affiche le formulaire d'expression d'intérêt des investisseurs
     */
    public function index()
    {   
        // Récupérer les sections hero et texte
        $sections = $this->get_sections('investors-form'); 
        
        // Préparer les données pour la vue
        $data = [
            'title'  => 'Become Broker Partener',
            'hero'   => $sections['hero'] ?? null,
            'textes' => $sections['textes'] ?? [],
            'page'   => $sections['page'] ?? null
        ];
        $data['title'] = 'Devenir Investisseur Partenaire';

        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');

        
        
        // Charger la vue avec le formulaire
        $this->load->view('Investors_View', $data);
    }

    /**
     * Endpoint: /Api/investors/submit
     */
    public function Save() {
    // Activer le rapport d'erreurs pour le débogage
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // ← Mettre à 0 en production

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

    // 🔍 DEBUG : Loguer le début
    log_message('debug', '=== SAVE INVESTOR START ===');

    // Récupérer les données (JSON ou POST)
    $input_data = [];
    $content_type = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';
    
    if (strpos($content_type, 'application/json') !== false) {
        $json_input = file_get_contents('php://input');
        $input_data = json_decode($json_input, true);
        
        log_message('debug', 'JSON brut reçu: ' . $json_input);
        log_message('debug', 'JSON décodé: ' . print_r($input_data, true));
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->_json_response(false, 'JSON invalide: ' . json_last_error_msg());
            return;
        }
    } else {
        $input_data = $this->input->post();
        log_message('debug', 'POST reçu: ' . print_r($input_data, true));
    }

    // Vérifier si des données ont été reçues
    if (empty($input_data)) {
        $this->_json_response(false, 'Aucune donnée reçue');
        return;
    }

    // ========== VALIDATION ==========
    $errors = $this->_validate_data($input_data);
    
    if (!empty($errors)) {
        $this->_json_response(false, 'Erreur de validation', ['errors' => $errors]);
        return;
    }

    // ========== PRÉPARATION DES DONNÉES ==========
    $insert_data = $this->_prepare_insert_data($input_data);
    
    log_message('debug', 'Données préparées: ' . print_r($insert_data, true));

    // ========== INSERTION ==========
    $inserted = $this->db->insert('investors', $insert_data);
    
    if (!$inserted) {
        $db_error = $this->db->error();
        log_message('error', 'Erreur DB: ' . print_r($db_error, true));
        $this->_json_response(false, 'Erreur base de données: ' . $db_error['message']);
        return;
    }

    $insert_id = $this->db->insert_id();
    log_message('debug', 'Insertion OK, ID: ' . $insert_id);

    // ========== ENVOI EMAILS ==========
    $email_sent = false;
    try {
        $email_sent = $this->_send_notification_emails($insert_data, $insert_id);
    } catch (Exception $e) {
        log_message('error', 'Exception email: ' . $e->getMessage());
        // Continue même si l'email échoue
    }

    // ========== SUCCÈS ==========
    log_message('debug', '=== SAVE INVESTOR SUCCESS ===');
    
    $this->_json_response(true, 'Votre expression d\'intérêt a été enregistrée avec succès', [
        'email_sent' => $email_sent,
        'id' => $insert_id
    ]);
}

/**
 * Helper pour envoyer une réponse JSON et arrêter l'exécution
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
 * Validation des données
 */
private function _validate_data($input_data) {
    $errors = [];
    
    // Nom complet
    if (empty($input_data['full_name'])) {
        $errors['full_name'] = 'Le nom complet est requis';
    } elseif (strlen($input_data['full_name']) > 150) {
        $errors['full_name'] = 'Le nom ne doit pas dépasser 150 caractères';
    }

    // Email
    if (empty($input_data['email'])) {
        $errors['email'] = 'L\'email est requis';
    } elseif (!filter_var($input_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format d\'email invalide';
    } elseif (strlen($input_data['email']) > 150) {
        $errors['email'] = 'L\'email ne doit pas dépasser 150 caractères';
    } else {
        // Vérifier si l'email existe déjà
        $this->db->where('email', $input_data['email']);
        if ($this->db->count_all_results('investors') > 0) {
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

    // Type d'intérêt (au moins un)
    $interest_fields = [
        'interest_equity', 'interest_debt', 'interest_blended_finance',
        'interest_grant', 'interest_strategic_partnership',
        'interest_technical_collaboration', 'interest_offtake_distribution'
    ];
    
    $has_interest = false;
    foreach ($interest_fields as $field) {
        if (!empty($input_data[$field]) && $input_data[$field] == 1) {
            $has_interest = true;
            break;
        }
    }
    
    $interest_other_filled = !empty($input_data['interest_other']) && trim($input_data['interest_other']) !== '';
    
    if (!$has_interest && !$interest_other_filled) {
        $errors['interest'] = 'Veuillez sélectionner au moins un type d\'intérêt ou préciser "Autre"';
    }

    // Compliance
    if (empty($input_data['agree_contact']) || $input_data['agree_contact'] != 1) {
        $errors['agree_contact'] = 'Vous devez accepter d\'être contacté';
    }
    
    if (empty($input_data['non_binding_confirmation']) || $input_data['non_binding_confirmation'] != 1) {
        $errors['non_binding_confirmation'] = 'Vous devez confirmer que cette expression d\'intérêt est non engageante';
    }

    return $errors;
}

/**
 * Préparation des données pour insertion
 */
private function _prepare_insert_data($input_data) {
    return [
        'full_name' => $input_data['full_name'],
        'organization' => $input_data['organization'] ?? null,
        'position_title' => $input_data['position_title'] ?? null,
        'id_pays' => $input_data['id_pays'],
        'email' => $input_data['email'],
        'phone' => $input_data['phone'] ?? null,

        'interest_equity' => !empty($input_data['interest_equity']) ? 1 : 0,
        'interest_debt' => !empty($input_data['interest_debt']) ? 1 : 0,
        'interest_blended_finance' => !empty($input_data['interest_blended_finance']) ? 1 : 0,
        'interest_grant' => !empty($input_data['interest_grant']) ? 1 : 0,
        'interest_strategic_partnership' => !empty($input_data['interest_strategic_partnership']) ? 1 : 0,
        'interest_technical_collaboration' => !empty($input_data['interest_technical_collaboration']) ? 1 : 0,
        'interest_offtake_distribution' => !empty($input_data['interest_offtake_distribution']) ? 1 : 0,
        'interest_other' => $input_data['interest_other'] ?? null,

        'commitment_range' => $input_data['commitment_range'] ?? null,

        'focus_research_lab' => !empty($input_data['focus_research_lab']) ? 1 : 0,
        'focus_gmp_facility' => !empty($input_data['focus_gmp_facility']) ? 1 : 0,
        'focus_medicinal_plant' => !empty($input_data['focus_medicinal_plant']) ? 1 : 0,
        'focus_commercialization' => !empty($input_data['focus_commercialization']) ? 1 : 0,
        'focus_full_platform' => !empty($input_data['focus_full_platform']) ? 1 : 0,

        'timeline' => $input_data['timeline'] ?? 'Exploratory',
        'strategic_message' => $input_data['strategic_message'] ?? null,

        'agree_contact' => !empty($input_data['agree_contact']) ? 1 : 0,
        'non_binding_confirmation' => !empty($input_data['non_binding_confirmation']) ? 1 : 0,
        
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
}
    /**
     * Méthode de test simple
     */
    public function test() {
        echo json_encode([
            'success' => true,
            'message' => 'API Investors fonctionne',
            'time' => date('Y-m-d H:i:s')
        ]);
    }

    /**
 * Send concise and friendly notification emails to admin and investor using SendGrid
 * 
 * @param array $data      The investor's data
 * @param int   $insert_id The new investor ID
 * @return bool True if both emails sent successfully, false otherwise
 */
private function _send_notification_emails($data, $insert_id)
{
    try {
        // Charger SendGrid
        $this->load->library('Sendgrid_lib');

        // Get settings
        $site_name  = $this->Model->get_setting('site_name', 'AGF Phytomed');
        $admin_email = $this->Model->get_setting('admin_email', 'partnerships@agf-phytomed.com');
        $whatsapp    = $this->Model->get_setting('site_phone', '68863945');

        // Get country name
        $country_name = 'Unknown';
        if (!empty($data['id_pays'])) {
            $country = $this->db->get_where('pays', ['id' => $data['id_pays']])->row();
            $country_name = $country ? $country->pays : 'Unknown';
        }

        // Helper for boolean display
        $format_bool = function($val) {
            return $val ? 'Yes' : 'No';
        };

        // --- Prepare interest types list ---
        $interest_map = [
            'interest_equity'                 => 'Equity',
            'interest_debt'                   => 'Debt',
            'interest_blended_finance'        => 'Blended Finance',
            'interest_grant'                   => 'Grant',
            'interest_strategic_partnership'   => 'Strategic Partnership',
            'interest_technical_collaboration' => 'Technical Collaboration',
            'interest_offtake_distribution'    => 'Offtake/Distribution'
        ];
        $interests = [];
        foreach ($interest_map as $field => $label) {
            if (!empty($data[$field])) $interests[] = $label;
        }
        if (!empty($data['interest_other'])) {
            $interests[] = 'Other: ' . $data['interest_other'];
        }
        $interests_str = implode(', ', $interests) ?: 'None';

        // --- Prepare focus areas list ---
        $focus_map = [
            'focus_research_lab'       => 'Research & Lab',
            'focus_gmp_facility'       => 'GMP Facility',
            'focus_medicinal_plant'    => 'Medicinal Plant',
            'focus_commercialization'   => 'Commercialization',
            'focus_full_platform'       => 'Full Platform'
        ];
        $focus_areas = [];
        foreach ($focus_map as $field => $label) {
            if (!empty($data[$field])) $focus_areas[] = $label;
        }
        $focus_str = implode(', ', $focus_areas) ?: 'None';

        // ========== 1. EMAIL TO ADMIN ==========
        $admin_subject = 'New Investor Expression of Interest #' . $insert_id;
        $admin_message = "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden;'>
                <div style='background: #0B4F2E; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin:0;'>New Investor Application #{$insert_id}</h2>
                </div>
                <div style='padding: 25px;'>
                    <p><strong>Submitted:</strong> " . date('Y-m-d H:i') . "</p>
                    <p><strong>Name:</strong> {$data['full_name']}<br>
                    <strong>Position:</strong> {$data['position_title']}<br>
                    <strong>Organization:</strong> {$data['organization']}<br>
                    <strong>Country:</strong> $country_name<br>
                    <strong>Email:</strong> {$data['email']}<br>
                    <strong>Phone:</strong> {$data['phone']}</p>
                    
                    <p><strong>Interest types:</strong> $interests_str</p>
                    <p><strong>Focus areas:</strong> $focus_str</p>
                    <p><strong>Commitment range:</strong> " . ($data['commitment_range'] ?? 'Not specified') . "<br>
                    <strong>Timeline:</strong> " . ($data['timeline'] ?? 'Not specified') . "</p>
                    
                    <p><strong>Strategic message:</strong> " . nl2br($data['strategic_message'] ?? 'None') . "</p>
                    
                    <p><strong>Compliance:</strong><br>
                    Accept contact: {$format_bool($data['agree_contact'])}<br>
                    Non-binding confirmation: {$format_bool($data['non_binding_confirmation'])}</p>
                    
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

        $admin_result = $this->sendgrid_lib->send_email($admin_email, $admin_subject, $admin_message);
        $admin_sent = ($admin_result['status'] == 202 || $admin_result['status'] == 200);
        
        if (!$admin_sent) {
            log_message('error', 'SendGrid - Admin email failed: ' . json_encode($admin_result));
        }

        // ========== 2. WELCOME EMAIL TO INVESTOR ==========
        $user_subject = 'Thank you for your interest in AGF Phytomed';
        $user_message = "
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
                    <p>This is an automatic acknowledgement that we have received your expression of interest (reference #{$insert_id}).</p>
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
                    © " . date('Y') . " AGF Phytomed. All rights reserved.
                </div>
            </div>
        </body>
        </html>";

        $user_result = $this->sendgrid_lib->send_email($data['email'], $user_subject, $user_message);
        $user_sent = ($user_result['status'] == 202 || $user_result['status'] == 200);
        
        if (!$user_sent) {
            log_message('error', 'SendGrid - Investor email failed: ' . json_encode($user_result));
        }

        $both_sent = $admin_sent && $user_sent;
        log_message('info', 'SendGrid emails sent: admin=' . ($admin_sent ? 'OK' : 'FAIL') . ', investor=' . ($user_sent ? 'OK' : 'FAIL'));
        
        return $both_sent;

    } catch (Exception $e) {
        log_message('error', 'SendGrid email exception: ' . $e->getMessage());
        return false;
    }
}


    private function get_sections($slug = 'investors-form') {
        $page = $this->Model->readOne('pages', [
            'slug' => $slug,
            'est_publiee' => 1
        ]);

        if (empty($page)) {
            log_message('debug', 'Page "' . $slug . '" non trouvée');
            return null;
        }

        // Récupérer la section hero
        $hero = $this->Model->readOne('sections_contenu', [
            'id_page'      => $page['id_page'],
            'type_section' => 'hero',
            'est_active'   => 1
        ]);

        if (!empty($hero) && !empty($hero['options_json'])) {
            $hero['options'] = json_decode($hero['options_json'], true);
        }

        // Récupérer les sections texte
        $textes = $this->Model->read('sections_contenu', [
            'id_page'      => $page['id_page'],
            'type_section' => 'texte',
            'est_active'   => 1
        ], 'ordre', 'ASC');

        // S'assurer que $textes est toujours un tableau
        if (empty($textes)) {
            $textes = [];
        }

        // Parser les options JSON
        foreach ($textes as &$texte) {
            if (!empty($texte['options_json'])) {
                $texte['options'] = json_decode($texte['options_json'], true);
            } else {
                $texte['options'] = [];
            }
        }

        return [
            'page'   => $page,
            'hero'   => $hero,
            'textes' => $textes
        ];
    }
}