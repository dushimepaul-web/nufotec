<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller d'enregistrement des Intermédiaires EOI
 * Utilise le Model générique pour les opérations CRUD
 */
class Eoi_register extends MY_Controller {

    protected $table = 'eoi_intermediaries';

    public function __construct() {
        parent::__construct();
        $this->load->model('Model');
        $this->load->library('form_validation');
        $this->load->library('email');
        $this->load->helper('url');
        $this->load->helper('form');
    }

    /**
     * Page d'inscription publique
     */
    public function index() {
        $data['title'] = 'Enregistrement Intermédiaire EOI';
        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');
        
        $this->load->view('eoi_register_View', $data);
    }

    /**
     * Traitement de l'inscription
     */
    public function submit() {
        // Règles de validation
        $this->form_validation->set_rules('full_name', 'Nom complet', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('firm_name', 'Nom de la société', 'trim|max_length[255]');
        $this->form_validation->set_rules('id_pays_jurisdiction', 'Pays de juridiction', 'required|integer');
        $this->form_validation->set_rules('id_pays_operation', 'Pays d\'opération', 'required|integer');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[191]|is_unique[eoi_intermediaries.email]');
        $this->form_validation->set_rules('mobile', 'Téléphone mobile', 'trim|max_length[50]');
        $this->form_validation->set_rules('whatsapp', 'WhatsApp', 'trim|max_length[50]');
        $this->form_validation->set_rules('website', 'Site web', 'trim|max_length[255]|valid_url');
        $this->form_validation->set_rules('registration_number', 'Numéro d\'enregistrement', 'trim|max_length[100]');
        $this->form_validation->set_rules('regulatory_authority', 'Autorité de régulation', 'trim|max_length[255]');
        $this->form_validation->set_rules('terms', 'Conditions d\'utilisation', 'required');

        if ($this->form_validation->run() == FALSE) {
            $data['title'] = 'Enregistrement Intermédiaire EOI';
            $data['pays'] = $this->Model->read('pays', [], 'nom', 'ASC');
            
            $this->load->view('eoi_register/form', $data);
            return;
        }

        // Préparer les données
        $data = [
            'full_name' => $this->input->post('full_name'),
            'firm_name' => $this->input->post('firm_name'),
            'id_pays_jurisdiction' => (int) $this->input->post('id_pays_jurisdiction'),
            'id_pays_operation' => (int) $this->input->post('id_pays_operation'),
            'registration_number' => $this->input->post('registration_number'),
            'regulatory_status' => $this->input->post('regulatory_status'),
            'regulatory_authority' => $this->input->post('regulatory_authority'),
            'email' => $this->input->post('email'),
            'mobile' => $this->input->post('mobile'),
            'whatsapp' => $this->input->post('whatsapp'),
            'website' => $this->input->post('website'),
            'capacity' => $this->input->post('capacity') ? implode(',', $this->input->post('capacity')) : null,
            'investor_types' => $this->input->post('investor_types') ? implode(',', $this->input->post('investor_types')) : null,
            'engagement_model' => $this->input->post('engagement_model'),
            'is_authorized' => 0, // Par défaut non autorisé (validation admin requise)
            'aml_kyc_compliant' => $this->input->post('aml_kyc_compliant') ? 1 : 0
            // submitted_at géré automatiquement par le Model si la colonne existe
        ];

        // Utilisation du Model générique avec retour de l'ID
        $insert_id = $this->Model->create_last_id($this->table, $data);

        if ($insert_id) {
            // Envoyer email de confirmation
            $this->_send_confirmation_email($data);
            
            $this->session->set_flashdata('success', 'Votre inscription a été enregistrée avec succès. Notre équipe vous contactera après validation.');
            redirect('eoi_register/success');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.');
            redirect('eoi_register');
        }
    }

    /**
     * Page de succès
     */
    public function success() {
        $data['title'] = 'Inscription Confirmée';
        $this->load->view('eoi_register/success', $data);
    }

    /**
     * Vérification du statut par email
     */
    public function status() {
        $data['title'] = 'Vérifier mon statut';
        $data['intermediary'] = null;
        $data['not_found'] = false;
        
        if ($this->input->post('check_email')) {
            $email = $this->input->post('check_email');
            $intermediary = $this->Model->read_one($this->table, ['email' => $email]);
            
            if ($intermediary) {
                // Récupérer les noms des pays
                if (!empty($intermediary['id_pays_jurisdiction'])) {
                    $pays_j = $this->Model->read_one('pays', ['id' => $intermediary['id_pays_jurisdiction']]);
                    $intermediary['pays_jurisdiction_nom'] = $pays_j['nom'] ?? 'Non défini';
                }
                if (!empty($intermediary['id_pays_operation'])) {
                    $pays_o = $this->Model->read_one('pays', ['id' => $intermediary['id_pays_operation']]);
                    $intermediary['pays_operation_nom'] = $pays_o['nom'] ?? 'Non défini';
                }
                
                $data['intermediary'] = $intermediary;
            } else {
                $data['not_found'] = true;
            }
        }
        
        $this->load->view('eoi_register/status', $data);
    }

    /**
     * Mise à jour par l'intermédiaire (via token sécurisé)
     */
    public function edit($token = null) {
        if (!$token) {
            show_404();
        }

        // Rechercher l'intermédiaire par token (stocké dans une colonne edit_token)
        $intermediary = $this->Model->read_one($this->table, ['edit_token' => $token]);
        
        if (!$intermediary) {
            $this->session->set_flashdata('error', 'Lien invalide ou expiré.');
            redirect('eoi_register/status');
        }

        // Vérifier si le token n'est pas expiré (24h)
        if (!empty($intermediary['token_expires_at']) && strtotime($intermediary['token_expires_at']) < time()) {
            $this->session->set_flashdata('error', 'Ce lien a expiré. Veuillez demander un nouveau lien.');
            redirect('eoi_register/status');
        }

        // Traitement du formulaire de modification
        if ($this->input->post()) {
            $this->form_validation->set_rules('full_name', 'Nom complet', 'required|trim|max_length[255]');
            $this->form_validation->set_rules('firm_name', 'Nom de la société', 'trim|max_length[255]');
            $this->form_validation->set_rules('mobile', 'Téléphone mobile', 'trim|max_length[50]');
            $this->form_validation->set_rules('whatsapp', 'WhatsApp', 'trim|max_length[50]');
            $this->form_validation->set_rules('website', 'Site web', 'trim|max_length[255]|valid_url');

            if ($this->form_validation->run() == TRUE) {
                $update_data = [
                    'full_name' => $this->input->post('full_name'),
                    'firm_name' => $this->input->post('firm_name'),
                    'mobile' => $this->input->post('mobile'),
                    'whatsapp' => $this->input->post('whatsapp'),
                    'website' => $this->input->post('website'),
                    'capacity' => $this->input->post('capacity') ? implode(',', $this->input->post('capacity')) : null,
                    'investor_types' => $this->input->post('investor_types') ? implode(',', $this->input->post('investor_types')) : null,
                    'engagement_model' => $this->input->post('engagement_model'),
                    'aml_kyc_compliant' => $this->input->post('aml_kyc_compliant') ? 1 : 0,
                    // Invalider le token après utilisation
                    'edit_token' => null,
                    'token_expires_at' => null
                ];

                if ($this->Model->update($this->table, ['id' => $intermediary['id']], $update_data)) {
                    $this->session->set_flashdata('success', 'Vos informations ont été mises à jour avec succès.');
                    redirect('eoi_register/status');
                } else {
                    $this->session->set_flashdata('error', 'Erreur lors de la mise à jour.');
                }
            }
        }

        $data['title'] = 'Modifier mes informations';
        $data['intermediary'] = $intermediary;
        $data['pays'] = $this->Model->read('pays', [], 'nom', 'ASC');
        $data['token'] = $token;

        $this->load->view('eoi_register/edit', $data);
    }

    /**
     * Demande de lien de modification (génération de token)
     */
    public function request_edit_link() {
        if (!$this->input->post('email')) {
            $this->session->set_flashdata('error', 'Veuillez fournir votre email.');
            redirect('eoi_register/status');
        }

        $email = $this->input->post('email');
        $intermediary = $this->Model->read_one($this->table, ['email' => $email]);

        if (!$intermediary) {
            // Pour la sécurité, ne pas révéler si l'email existe ou non
            $this->session->set_flashdata('success', 'Si cet email existe dans notre base, vous recevrez un lien de modification.');
            redirect('eoi_register/status');
        }

        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Sauvegarder le token
        $this->Model->update($this->table, ['id' => $intermediary['id']], [
            'edit_token' => $token,
            'token_expires_at' => $expires
        ]);

        // Envoyer l'email avec le lien
        $this->_send_edit_link_email($intermediary, $token);

        $this->session->set_flashdata('success', 'Un lien de modification vous a été envoyé par email (valable 24h).');
        redirect('eoi_register/status');
    }

    /**
     * Envoi email de confirmation d'inscription
     */
    private function _send_confirmation_email($data) {
        $config = $this->_get_email_config();
        $this->email->initialize($config);
        
        $this->email->from($config['smtp_user'], 'African Green Farmers');
        $this->email->to($data['email']);
        $this->email->subject('Confirmation d\'inscription - Intermédiaire EOI');
        
        $message = $this->load->view('emails/eoi_confirmation', [
            'full_name' => $data['full_name'],
            'firm_name' => $data['firm_name']
        ], TRUE);
        
        $this->email->message($message);
        
        if (!$this->email->send()) {
            log_message('error', 'Email confirmation failed: ' . $this->email->print_debugger());
        }
    }

    /**
     * Envoi email avec lien de modification
     */
    private function _send_edit_link_email($intermediary, $token) {
        $config = $this->_get_email_config();
        $this->email->initialize($config);
        
        $edit_url = site_url('eoi_register/edit/' . $token);
        
        $this->email->from($config['smtp_user'], 'African Green Farmers');
        $this->email->to($intermediary['email']);
        $this->email->subject('Modifier vos informations - Intermédiaire EOI');
        
        $message = $this->load->view('emails/eoi_edit_link', [
            'full_name' => $intermediary['full_name'],
            'edit_url' => $edit_url,
            'expires' => '24 heures'
        ], TRUE);
        
        $this->email->message($message);
        
        if (!$this->email->send()) {
            log_message('error', 'Email edit link failed: ' . $this->email->print_debugger());
        }
    }

    /**
     * Configuration email
     */
    private function _get_email_config() {
        // À configurer selon tes paramètres SMTP
        return [
            'protocol' => 'smtp',
            'smtp_host' => $this->Model->get_setting('smtp_host', 'ssl://smtp.gmail.com'),
            'smtp_port' => $this->Model->get_setting('smtp_port', 465),
            'smtp_user' => $this->Model->get_setting('smtp_user', 'noreply@agf.com'),
            'smtp_pass' => $this->Model->get_setting('smtp_pass', ''),
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n"
        ];
    }
}




/*<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * EOI Intermediaries Controller
 * Gestion des Expressions d'Intérêt pour les intermédiaires - Tout en un
 */
class Eoi_intermediaries extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url', 'security']);
        $this->load->library(['form_validation', 'session', 'email', 'database']);
    }

    /**
     * Affiche le formulaire multi-étapes pour intermédiaires
     */
    public function index()
    {
        $data['title'] = 'Devenir Intermédiaire Partenaire';
        $data['pays'] = $this->get_all_countries();
        
        $this->load->view('eoi_intermediaries/form', $data);
    }

    /**
     * Traite la soumission du formulaire (5 étapes)
     */
    public function submit()
    {
        if (!$this->input->post()) {
            redirect('eoi_intermediaries');
        }

        // ========== VALIDATION ÉTAPE 1 : IDENTITÉ ==========
        $this->form_validation->set_rules('full_name', 'Nom complet', 'required|trim|min_length[3]|max_length[255]|xss_clean');
        $this->form_validation->set_rules('firm_name', 'Nom de la société', 'trim|max_length[255]|xss_clean');
        $this->form_validation->set_rules('email', 'Email professionnel', 'required|trim|valid_email|max_length[191]|xss_clean');
        $this->form_validation->set_rules('mobile', 'Téléphone', 'trim|max_length[50]|xss_clean');
        $this->form_validation->set_rules('whatsapp', 'WhatsApp', 'trim|max_length[50]|xss_clean');
        $this->form_validation->set_rules('website', 'Site web', 'trim|valid_url|max_length[255]|xss_clean');

        // ========== VALIDATION ÉTAPE 2 : LOCALISATION ==========
        $this->form_validation->set_rules('id_pays_jurisdiction', 'Pays de juridiction', 'required|integer');
        $this->form_validation->set_rules('id_pays_operation', 'Pays d\'opération', 'required|integer');

        // ========== VALIDATION ÉTAPE 3 : RÉGULATION ==========
        $this->form_validation->set_rules('regulatory_status', 'Statut régulatoire', 'trim|in_list[Licensed,Exempt,Unlicensed]|xss_clean');
        $this->form_validation->set_rules('registration_number', 'Numéro d\'enregistrement', 'trim|max_length[100]|xss_clean');
        $this->form_validation->set_rules('regulatory_authority', 'Autorité de régulation', 'trim|max_length[255]|xss_clean');

        // ========== VALIDATION ÉTAPE 4 : CAPACITÉS ==========
        // Pas de règles CI pour les SET, validation manuelle ci-dessous

        // Vérifier email unique
        $email = $this->input->post('email', TRUE);
        $existing = $this->db->where('email', $email)->get('eoi_intermediaries')->row();
        
        if ($existing) {
            $this->form_validation->set_rules('email', 'Email', 'is_unique[eoi_intermediaries.email]');
        }

        // Validation personnalisée
        $custom_errors = [];
        
        // Vérifier capacités (au moins une)
        $capacities = $this->input->post('capacity');
        if (empty($capacities)) {
            $custom_errors['capacity'] = 'Veuillez sélectionner au moins une capacité';
        }

        // Vérifier terms
        if (!$this->input->post('terms')) {
            $custom_errors['terms'] = 'Vous devez accepter les conditions d\'utilisation';
        }

        if ($this->form_validation->run() === FALSE || !empty($custom_errors)) {
            // Retour au formulaire avec erreurs
            $data['title'] = 'Devenir Intermédiaire Partenaire';
            $data['pays'] = $this->get_all_countries();
            $data['custom_errors'] = $custom_errors;
            
            // Restaurer les pays sélectionnés
            $jur_id = $this->input->post('id_pays_jurisdiction');
            $op_id = $this->input->post('id_pays_operation');
            
            if ($jur_id) {
                $data['selected_jurisdiction'] = $this->db->where('id', $jur_id)->get('pays')->row_array();
            }
            if ($op_id) {
                $data['selected_operation'] = $this->db->where('id', $op_id)->get('pays')->row_array();
            }
            
            $this->session->set_flashdata('error', 'Veuillez corriger les erreurs ci-dessous.');
            $this->load->view('eoi_intermediaries/form', $data);
            
        } else {
            // Préparer les données pour insertion
            $data_insert = [
                'full_name' => $this->input->post('full_name', TRUE),
                'firm_name' => $this->input->post('firm_name', TRUE) ?: NULL,
                'id_pays_jurisdiction' => (int) $this->input->post('id_pays_jurisdiction'),
                'id_pays_operation' => (int) $this->input->post('id_pays_operation'),
                'registration_number' => $this->input->post('registration_number', TRUE) ?: NULL,
                'regulatory_status' => $this->input->post('regulatory_status', TRUE) ?: NULL,
                'regulatory_authority' => $this->input->post('regulatory_authority', TRUE) ?: NULL,
                'email' => $email,
                'mobile' => $this->input->post('mobile', TRUE) ?: NULL,
                'whatsapp' => $this->input->post('whatsapp', TRUE) ?: NULL,
                'website' => $this->input->post('website', TRUE) ?: NULL,
                'capacity' => $this->format_set_field($capacities),
                'investor_types' => $this->format_set_field($this->input->post('investor_types')),
                'engagement_model' => $this->input->post('engagement_model', TRUE) ?: NULL,
                'aml_kyc_compliant' => $this->input->post('aml_kyc_compliant') ? 1 : 0,
                'is_authorized' => 0, // Par défaut non autorisé
                'submitted_at' => date('Y-m-d H:i:s')
            ];

            // Insertion
            $this->db->insert('eoi_intermediaries', $data_insert);
            $insert_id = $this->db->insert_id();

            if ($insert_id) {
                $this->send_confirmation_email($data_insert, $insert_id);
                $this->send_admin_notification($data_insert, $insert_id);
                
                $this->session->set_flashdata('success', 'Votre candidature a été soumise avec succès. Notre équipe va étudier votre profil sous 5 jours ouvrés.');
                redirect('eoi_intermediaries/success');
            } else {
                $this->session->set_flashdata('error', 'Une erreur est survenue lors de la soumission. Veuillez réessayer.');
                redirect('eoi_intermediaries');
            }
        }
    }

    /**
     * Page de succès après soumission
     */
    public function success()
    {
        $data['title'] = 'Candidature Soumise avec Succès';
        $this->load->view('eoi_intermediaries/success', $data);
    }

    /**
     * Vérification du statut d'une candidature
     */
    public function status()
    {
        $data['title'] = 'Vérifier le Statut de ma Candidature';
        
        if ($this->input->post()) {
            $email = $this->input->post('email', TRUE);
            $reference = $this->input->post('reference', TRUE);
            
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('reference', 'Référence', 'required|integer');
            
            if ($this->form_validation->run() === TRUE) {
                $this->db->select('eoi_intermediaries.*, 
                    jur.pays as jurisdiction_name, 
                    op.pays as operation_name');
                $this->db->from('eoi_intermediaries');
                $this->db->join('pays as jur', 'jur.id = eoi_intermediaries.id_pays_jurisdiction', 'left');
                $this->db->join('pays as op', 'op.id = eoi_intermediaries.id_pays_operation', 'left');
                $this->db->where('eoi_intermediaries.email', $email);
                $this->db->where('eoi_intermediaries.id', $reference);
                $eoi = $this->db->get()->row_array();
                
                if ($eoi) {
                    $data['eoi'] = $eoi;
                } else {
                    $data['error'] = 'Aucune candidature trouvée avec ces informations.';
                }
            }
        }
        
        $this->load->view('eoi_intermediaries/status', $data);
    }

    /**
     * Liste des candidatures (Admin)
     */
    public function admin_list()
    {
        $this->db->select('eoi_intermediaries.*, 
            jur.pays as jurisdiction_name, 
            op.pays as operation_name');
        $this->db->from('eoi_intermediaries');
        $this->db->join('pays as jur', 'jur.id = eoi_intermediaries.id_pays_jurisdiction', 'left');
        $this->db->join('pays as op', 'op.id = eoi_intermediaries.id_pays_operation', 'left');
        $this->db->order_by('eoi_intermediaries.submitted_at', 'DESC');
        $data['intermediaries'] = $this->db->get()->result_array();
        
        $data['title'] = 'Liste des Intermédiaires';
        $this->load->view('eoi_intermediaries/admin_list', $data);
    }

    /**
     * Détails d'une candidature (Admin)
     */
    public function view($id)
    {
        $this->db->select('eoi_intermediaries.*, 
            jur.pays as jurisdiction_name, 
            jur.ISO_3166_1_2_Letter_Code as jurisdiction_code,
            op.pays as operation_name,
            op.ISO_3166_1_2_Letter_Code as operation_code');
        $this->db->from('eoi_intermediaries');
        $this->db->join('pays as jur', 'jur.id = eoi_intermediaries.id_pays_jurisdiction', 'left');
        $this->db->join('pays as op', 'op.id = eoi_intermediaries.id_pays_operation', 'left');
        $this->db->where('eoi_intermediaries.id', $id);
        $intermediary = $this->db->get()->row_array();
        
        if (!$intermediary) {
            show_404();
        }
        
        $data['title'] = 'Détails Intermédiaire #' . $id;
        $data['intermediary'] = $intermediary;
        
        $this->load->view('eoi_intermediaries/view', $data);
    }

    /**
     * Autoriser un intermédiaire (Admin)
     */
    public function authorize($id)
    {
        $this->db->where('id', $id)->update('eoi_intermediaries', [
            'is_authorized' => 1,
            'authorized_at' => date('Y-m-d H:i:s')
        ]);
        
        // Envoyer email d'autorisation
        $intermediary = $this->db->where('id', $id)->get('eoi_intermediaries')->row_array();
        if ($intermediary) {
            $this->send_authorization_email($intermediary);
        }
        
        $this->session->set_flashdata('success', 'Intermédiaire autorisé avec succès.');
        redirect('eoi_intermediaries/admin_list');
    }

    /**
     * Révoquer l'autorisation (Admin)
     */
    public function revoke($id)
    {
        $this->db->where('id', $id)->update('eoi_intermediaries', ['is_authorized' => 0]);
        $this->session->set_flashdata('success', 'Autorisation révoquée.');
        redirect('eoi_intermediaries/admin_list');
    }

    /**
     * Export CSV des intermédiaires (Admin)
     */
    public function export()
    {
        $this->db->select('eoi_intermediaries.*, 
            jur.pays as jurisdiction_name, 
            op.pays as operation_name');
        $this->db->from('eoi_intermediaries');
        $this->db->join('pays as jur', 'jur.id = eoi_intermediaries.id_pays_jurisdiction', 'left');
        $this->db->join('pays as op', 'op.id = eoi_intermediaries.id_pays_operation', 'left');
        $this->db->order_by('eoi_intermediaries.submitted_at', 'DESC');
        $intermediaries = $this->db->get()->result_array();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=intermediaries_' . date('Y-m-d_H-i') . '.csv');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'ID', 'Date Soumission', 'Nom', 'Société', 'Email', 'Téléphone', 'WhatsApp',
            'Juridiction', 'Opération', 'Statut Régulatoire', 'Autorité', 'Numéro Reg.',
            'Capacités', 'Types Investisseurs', 'Modèle Engagement', 'AML/KYC', 'Autorisé', 'Site Web'
        ]);
        
        foreach ($intermediaries as $inter) {
            fputcsv($output, [
                $inter['id'],
                $inter['submitted_at'],
                $inter['full_name'],
                $inter['firm_name'] ?: 'N/A',
                $inter['email'],
                $inter['mobile'] ?: 'N/A',
                $inter['whatsapp'] ?: 'N/A',
                $inter['jurisdiction_name'] ?: 'N/A',
                $inter['operation_name'] ?: 'N/A',
                $inter['regulatory_status'] ?: 'N/A',
                $inter['regulatory_authority'] ?: 'N/A',
                $inter['registration_number'] ?: 'N/A',
                $inter['capacity'],
                $inter['investor_types'] ?: 'N/A',
                $inter['engagement_model'] ?: 'N/A',
                $inter['aml_kyc_compliant'] ? 'Oui' : 'Non',
                $inter['is_authorized'] ? 'Oui' : 'Non',
                $inter['website'] ?: 'N/A'
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Suppression (Admin)
     */
    public function delete($id)
    {
        $this->db->where('id', $id)->delete('eoi_intermediaries');
        $this->session->set_flashdata('success', 'Candidature supprimée.');
        redirect('eoi_intermediaries/admin_list');
    }

    // ==================== MÉTHODES PRIVÉES ====================

    /**
     * Récupère tous les pays
     */
    private function get_all_countries()
    {
        return $this->db->order_by('pays', 'ASC')->get('pays')->result_array();
    }

    /**
     * Formate un champ SET pour MySQL
     */
    private function format_set_field($values)
    {
        if (empty($values) || !is_array($values)) {
            return NULL;
        }
        
        $cleaned = array_map(function($val) {
            return $this->db->escape_str($val);
        }, $values);
        
        return implode(',', $cleaned);
    }

    /**
     * Email de confirmation au candidat
     */
    private function send_confirmation_email($data, $insert_id)
    {
        $config = [
            'protocol' => 'mail',
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'wordwrap' => TRUE
        ];
        
        $this->email->initialize($config);
        $this->email->from('noreply@africangreenfarmers.com', 'African Green Farmers');
        $this->email->to($data['email']);
        $this->email->subject('Confirmation Candidature Intermédiaire #' . $insert_id);
        
        $capacity_list = str_replace(',', ', ', $data['capacity']);
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Inter', Arial, sans-serif; line-height: 1.6; color: #1a2e3f; background: #f8faf9; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
                .header { background: linear-gradient(135deg, #0B4F2E 0%, #1B7B4B 100%); color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; }
                .recap { background: #f8faf9; padding: 20px; border-radius: 12px; margin: 20px 0; border-left: 4px solid #27ae60; }
                .status-badge { display: inline-block; padding: 8px 16px; background: #FFD700; color: #1a2e3f; border-radius: 20px; font-weight: 600; }
                .btn { display: inline-block; padding: 12px 24px; background: #0B4F2E; color: white; text-decoration: none; border-radius: 50px; margin-top: 20px; }
                .footer { background: #f8faf9; padding: 20px; text-align: center; font-size: 0.85em; color: #6c757d; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Candidature Reçue !</h1>
                    <p>Devenir Intermédiaire Partenaire</p>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>{$data['full_name']}</strong>,</p>
                    
                    <p>Nous avons bien reçu votre candidature pour devenir <strong>Intermédiaire Partenaire</strong> African Green Farmers.</p>
                    
                    <center>
                        <span class='status-badge'>En cours d'étude</span>
                    </center>
                    
                    <div class='recap'>
                        <h3 style='margin-top: 0; color: #0B4F2E;'>Récapitulatif Candidature #{$insert_id}</h3>
                        <p><strong>Société :</strong> " . ($data['firm_name'] ?: 'Individuel') . "</p>
                        <p><strong>Capacités :</strong> {$capacity_list}</p>
                        <p><strong>Modèle :</strong> " . ($data['engagement_model'] ?: 'À définir') . "</p>
                    </div>
                    
                    <p>Notre équipe Compliance va vérifier votre profil sous <strong>5 à 10 jours ouvrés</strong>.</p>
                    <p>Vous recevrez une notification dès que votre statut sera mis à jour.</p>
                    
                    <center>
                        <a href='" . site_url('eoi_intermediaries/status') . "' class='btn'>Suivre mon statut</a>
                    </center>
                </div>
                <div class='footer'>
                    <p>Référence : #{$insert_id} | Date : " . date('d/m/Y') . "</p>
                    <p><a href='mailto:compliance@africangreenfarmers.com'>compliance@africangreenfarmers.com</a></p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $this->email->message($message);
        $this->email->send();
    }

    /**
     * Email notification admin
     */
    private function send_admin_notification($data, $insert_id)
    {
        $config = ['protocol' => 'mail', 'mailtype' => 'html', 'charset' => 'utf-8'];
        $this->email->initialize($config);
        
        $this->email->from('system@africangreenfarmers.com', 'AGF System');
        $this->email->to('compliance@africangreenfarmers.com');
        $this->email->cc('partnerships@africangreenfarmers.com');
        $this->email->subject('🆕 Nouvelle Candidature Intermédiaire #' . $insert_id);
        
        // Récupérer noms des pays
        $jur = $this->db->where('id', $data['id_pays_jurisdiction'])->get('pays')->row();
        $op = $this->db->where('id', $data['id_pays_operation'])->get('pays')->row();
        
        $message = "
        <h2>Nouvelle Candidature Intermédiaire</h2>
        <table border='0' cellpadding='8' style='font-family: Arial; border-collapse: collapse; width: 100%;'>
            <tr style='background: #f8faf9;'><td width='30%'><strong>ID</strong></td><td>#{$insert_id}</td></tr>
            <tr><td><strong>Nom</strong></td><td>{$data['full_name']}</td></tr>
            <tr style='background: #f8faf9;'><td><strong>Société</strong></td><td>" . ($data['firm_name'] ?: 'N/A') . "</td></tr>
            <tr><td><strong>Email</strong></td><td><a href='mailto:{$data['email']}'>{$data['email']}</a></td></tr>
            <tr style='background: #f8faf9;'><td><strong>Téléphone</strong></td><td>" . ($data['mobile'] ?: 'N/A') . "</td></tr>
            <tr><td><strong>WhatsApp</strong></td><td>" . ($data['whatsapp'] ?: 'N/A') . "</td></tr>
            <tr style='background: #f8faf9;'><td><strong>Juridiction</strong></td><td>" . ($jur ? $jur->pays : 'N/A') . "</td></tr>
            <tr><td><strong>Opération</strong></td><td>" . ($op ? $op->pays : 'N/A') . "</td></tr>
            <tr style='background: #f8faf9;'><td><strong>Statut Régulatoire</strong></td><td>" . ($data['regulatory_status'] ?: 'Non spécifié') . "</td></tr>
            <tr><td><strong>Autorité</strong></td><td>" . ($data['regulatory_authority'] ?: 'N/A') . "</td></tr>
            <tr style='background: #f8faf9;'><td><strong>Numéro Reg.</strong></td><td>" . ($data['registration_number'] ?: 'N/A') . "</td></tr>
            <tr><td><strong>Capacités</strong></td><td>{$data['capacity']}</td></tr>
            <tr style='background: #f8faf9;'><td><strong>Investisseurs</strong></td><td>" . ($data['investor_types'] ?: 'N/A') . "</td></tr>
            <tr><td><strong>Engagement</strong></td><td>" . ($data['engagement_model'] ?: 'N/A') . "</td></tr>
            <tr style='background: #f8faf9;'><td><strong>AML/KYC</strong></td><td>" . ($data['aml_kyc_compliant'] ? '✅ Conforme' : '❌ Non déclaré') . "</td></tr>
            <tr><td><strong>Site Web</strong></td><td>" . ($data['website'] ? "<a href='{$data['website']}'>{$data['website']}</a>" : 'N/A') . "</td></tr>
        </table>
        <p style='margin-top: 20px;'>
            <a href='" . site_url('eoi_intermediaries/view/' . $insert_id) . "' style='padding: 10px 20px; background: #0B4F2E; color: white; text-decoration: none; border-radius: 5px;'>Voir le dossier</a>
            <a href='" . site_url('eoi_intermediaries/authorize/' . $insert_id) . "' style='padding: 10px 20px; background: #27ae60; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Autoriser</a>
        </p>
        ";
        
        $this->email->message($message);
        $this->email->send();
    }

    /**
     * Email d'autorisation à l'intermédiaire
     */
    private function send_authorization_email($intermediary)
    {
        $config = ['protocol' => 'mail', 'mailtype' => 'html', 'charset' => 'utf-8'];
        $this->email->initialize($config);
        
        $this->email->from('partnerships@africangreenfarmers.com', 'African Green Farmers');
        $this->email->to($intermediary['email']);
        $this->email->subject('✅ Votre candidature est approuvée !');
        
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #27ae60; color: white; padding: 30px; text-align: center; border-radius: 16px 16px 0 0;'>
                <h1>Félicitations !</h1>
                <p>Vous êtes maintenant intermédiaire agréé</p>
            </div>
            <div style='background: white; padding: 30px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);'>
                <p>Bonjour {$intermediary['full_name']},</p>
                <p>Après vérification de votre dossier, nous avons le plaisir de vous informer que votre candidature en tant qu'<strong>Intermédiaire Partenaire</strong> est <span style='color: #27ae60; font-weight: bold;'>APPROUVÉE</span>.</p>
                
                <div style='background: #f8faf9; padding: 20px; border-radius: 12px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #0B4F2E;'>Prochaines étapes :</h3>
                    <ol>
                        <li>Téléchargez votre convention d'intermédiaire</li>
                        <li>Signez et retournez la convention</li>
                        <li>Accédez au portail partenaires sécurisé</li>
                        <li>Commencez à soumettre des opportunités</li>
                    </ol>
                </div>
                
                <center>
                    <a href='" . site_url('partner-portal/login') . "' style='display: inline-block; padding: 15px 30px; background: #0B4F2E; color: white; text-decoration: none; border-radius: 50px; font-weight: 600;'>Accéder au Portail Partenaire</a>
                </center>
                
                <p style='margin-top: 30px; font-size: 0.9em; color: #6c757d;'>
                    Votre référence intermédiaire : <strong>AGF-INT-{$intermediary['id']}</strong>
                </p>
            </div>
        </div>
        ";
        
        $this->email->message($message);
        $this->email->send();
    }
}*/