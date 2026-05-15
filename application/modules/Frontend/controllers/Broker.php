<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Broker extends MX_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Broker_model');
        $this->load->library('form_validation');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->library('session');
        $this->load->helper('string');
    }
    
    /**
     * Affiche le formulaire d'inscription
     */
    public function create() {
        $data['countries'] = $this->Broker_model->get_all_pays();
        $this->load->view('broker_form', $data);
    }
    
    /**
     * Dashboard du broker (après connexion)
     */
    public function dashboard() {
        $broker_id = $this->session->userdata('broker_id');
        $user_id = $this->session->userdata('user_id');
        
        if (!$broker_id && !$user_id) {
            redirect('broker/login');
        }
        
        $broker = $this->Broker_model->get_broker_by_id($broker_id);
        if (!$broker) {
            $user = $this->Broker_model->get_user_by_id($user_id);
            if ($user) {
                $broker = $this->Broker_model->get_broker_by_email($user->email);
            }
        }
        
        if (!$broker) {
            $this->session->unset_userdata('broker_id');
            $this->session->unset_userdata('user_id');
            redirect('broker/login');
        }
        
        $investors = $this->Broker_model->get_investors_by_broker($broker->id);
        $investorStats = $this->Broker_model->get_investor_stats($broker->id);
        
        $totalPotential = 0;
        foreach ($investors as $inv) {
            switch ($inv->commitment_range) {
                case 'Below 250K': $totalPotential += 125000; break;
                case '250K-500K': $totalPotential += 375000; break;
                case '500K-1M': $totalPotential += 750000; break;
                case '1M-2M': $totalPotential += 1500000; break;
                case '2M+': $totalPotential += 2000000; break;
            }
        }
        
        $data = [
            'broker' => $broker,
            'investors' => $investors,
            'stats' => [
                'total_investors' => $investorStats['total_investors'],
                'total_potential' => number_format($totalPotential / 1000000, 1),
                'contacted' => $investorStats['contacted'],
                'invested' => $investorStats['invested']
            ]
        ];
        
        $this->load->view('broker_dashboard', $data);
    }
    
    /**
     * Page de connexion
     */
    public function login() {
        if ($this->session->userdata('broker_id') || $this->session->userdata('user_id')) {
            redirect('broker/dashboard');
        }
        $this->load->view('broker_login');
    }
    
    /**
     * Authentification
     */
    public function authenticate() {
        $this->output->set_content_type('application/json');
        
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis.']);
            return;
        }
        
        $user = $this->Broker_model->get_user_by_email($email);
        
        if ($user && password_verify($password, $user->password)) {
            if ($user->type_utilisateur == 'broker') {
                $broker = $this->Broker_model->get_broker_by_email($email);
                
                $this->session->set_userdata('user_id', $user->id);
                $this->session->set_userdata('user_email', $user->email);
                $this->session->set_userdata('user_name', $user->prenom . ' ' . $user->nom);
                $this->session->set_userdata('user_type', $user->type_utilisateur);
                
                if ($broker) {
                    $this->session->set_userdata('broker_id', $broker->id);
                    $this->session->set_userdata('broker_name', $broker->full_name);
                }
                
                echo json_encode(['success' => true, 'message' => 'Connexion réussie.']);
                return;
            } else {
                echo json_encode(['success' => false, 'message' => 'Compte non autorisé.']);
                return;
            }
        }
        
        $broker = $this->Broker_model->get_broker_by_email($email);
        if ($broker) {
            echo json_encode(['success' => false, 'message' => 'Veuillez créer un mot de passe pour votre compte.', 'need_password' => true, 'email' => $email]);
            return;
        }
        
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
    }
    
    /**
     * API: Création du mot de passe pour un compte existant (depuis la connexion)
     */
    public function create_password() {
        $this->output->set_content_type('application/json');
        
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');
        
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis.']);
            return;
        }
        
        if ($password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
            return;
        }
        
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères.']);
            return;
        }
        
        $broker = $this->Broker_model->get_broker_by_email($email);
        if (!$broker) {
            echo json_encode(['success' => false, 'message' => 'Compte non trouvé.']);
            return;
        }
        
        $existing_user = $this->Broker_model->get_user_by_email($email);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        if ($existing_user) {
            $this->Broker_model->update_user_password($existing_user->id, $hashed_password);
        } else {
            $name_parts = explode(' ', $broker->full_name, 2);
            $prenom = $name_parts[0];
            $nom = isset($name_parts[1]) ? $name_parts[1] : '';
            
            $user_data = [
                'uuid' => $this->generate_uuid(),
                'email' => $email,
                'password' => $hashed_password,
                'nom' => $nom,
                'prenom' => $prenom,
                'telephone' => $broker->mobile_phone,
                'role_id' => 8,
                'type_utilisateur' => 'broker',
                'nom_entreprise' => $broker->firm_name,
                'is_active' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'est_verifie' => 1
            ];
            
            $this->Broker_model->insert_user($user_data);
        }
        
        echo json_encode(['success' => true, 'message' => 'Mot de passe créé avec succès. Vous pouvez maintenant vous connecter.']);
    }
    
    /**
     * Déconnexion
     */
    public function logout() {
        $this->session->unset_userdata('broker_id');
        $this->session->unset_userdata('broker_name');
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('user_email');
        $this->session->unset_userdata('user_name');
        $this->session->unset_userdata('user_type');
        redirect('Auth');
    }
    
    /**
     * Enregistrement du broker (AJAX) - Sans mot de passe
     */
    public function store() {
        $this->output->set_content_type('application/json');
        
        $this->form_validation->set_rules('full_name', 'Nom complet', 'required|max_length[150]');
        $this->form_validation->set_rules('firm_name', 'Nom de l\'entreprise', 'required|max_length[200]');
        $this->form_validation->set_rules('id_pays', 'Pays', 'required|integer');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[150]');
        $this->form_validation->set_rules('confirm_authorized', 'Confirmation', 'required');
        $this->form_validation->set_rules('confirm_aml_kyc', 'Confirmation AML/KYC', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['success' => false, 'errors' => $this->form_validation->error_array()]);
            return;
        }
        
        if ($this->Broker_model->email_exists($this->input->post('email'))) {
            echo json_encode(['success' => false, 'errors' => ['email' => 'Cet email est déjà enregistré comme courtier.']]);
            return;
        }
        
        $email = $this->input->post('email');
        
        $broker_data = [
            'full_name' => $this->input->post('full_name'),
            'firm_name' => $this->input->post('firm_name'),
            'jurisdiction_of_incorporation' => $this->input->post('jurisdiction_of_incorporation'),
            'registration_number' => $this->input->post('registration_number'),
            'regulatory_status' => $this->input->post('regulatory_status'),
            'regulatory_authority' => $this->input->post('regulatory_authority'),
            'id_pays' => $this->input->post('id_pays'),
            'email' => $email,
            'mobile_phone' => $this->input->post('mobile_phone_full') ?: $this->input->post('mobile_phone'),
            'whatsapp' => $this->input->post('whatsapp_full') ?: $this->input->post('whatsapp'),
            'corporate_website' => $this->input->post('corporate_website'),
            'capacity_investment_broker' => $this->input->post('capacity_investment_broker') ? 1 : 0,
            'capacity_placement_agent' => $this->input->post('capacity_placement_agent') ? 1 : 0,
            'capacity_corporate_finance_advisor' => $this->input->post('capacity_corporate_finance_advisor') ? 1 : 0,
            'capacity_fund_manager' => $this->input->post('capacity_fund_manager') ? 1 : 0,
            'capacity_family_office_rep' => $this->input->post('capacity_family_office_rep') ? 1 : 0,
            'capacity_esg_advisor' => $this->input->post('capacity_esg_advisor') ? 1 : 0,
            'capacity_independent_introducer' => $this->input->post('capacity_independent_introducer') ? 1 : 0,
            'capacity_other' => $this->input->post('capacity_other'),
            'investor_private_equity' => $this->input->post('investor_private_equity') ? 1 : 0,
            'investor_venture_capital' => $this->input->post('investor_venture_capital') ? 1 : 0,
            'investor_esg_impact' => $this->input->post('investor_esg_impact') ? 1 : 0,
            'investor_dfi' => $this->input->post('investor_dfi') ? 1 : 0,
            'investor_institutional' => $this->input->post('investor_institutional') ? 1 : 0,
            'investor_hnwi' => $this->input->post('investor_hnwi') ? 1 : 0,
            'investor_sovereign' => $this->input->post('investor_sovereign') ? 1 : 0,
            'typical_ticket_size' => $this->input->post('typical_ticket_size'),
            'geographic_coverage' => $this->input->post('geographic_coverage'),
            'mandate_equity' => $this->input->post('mandate_equity') ? 1 : 0,
            'mandate_structured_debt' => $this->input->post('mandate_structured_debt') ? 1 : 0,
            'mandate_blended_finance' => $this->input->post('mandate_blended_finance') ? 1 : 0,
            'mandate_grant' => $this->input->post('mandate_grant') ? 1 : 0,
            'mandate_strategic_partnership' => $this->input->post('mandate_strategic_partnership') ? 1 : 0,
            'mandate_full_program' => $this->input->post('mandate_full_program') ? 1 : 0,
            'engagement_model' => $this->input->post('engagement_model'),
            'confirm_authorized' => $this->input->post('confirm_authorized') ? 1 : 0,
            'confirm_aml_kyc' => $this->input->post('confirm_aml_kyc') ? 1 : 0,
            'acknowledge_no_exclusivity' => $this->input->post('acknowledge_no_exclusivity') ? 1 : 0,
            'understand_formal_mandate_required' => $this->input->post('understand_formal_mandate_required') ? 1 : 0
        ];
        
        $insert_id = $this->Broker_model->insert_broker($broker_data);
        
        if ($insert_id) {
            $this->session->set_userdata('temp_broker_id', $insert_id);
            $this->session->set_userdata('temp_broker_email', $email);
            $this->session->set_userdata('temp_broker_name', $this->input->post('full_name'));
            
            echo json_encode([
                'success' => true, 
                'message' => 'Profil enregistré avec succès ! Veuillez maintenant créer votre mot de passe.',
                'redirect' => base_url('broker/set_password_view')
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.']);
        }
    }
    
    /**
     * Affiche la vue pour créer le mot de passe (après inscription)
     */
    public function set_password_view() {
        $temp_broker_id = $this->session->userdata('temp_broker_id');
        $temp_broker_email = $this->session->userdata('temp_broker_email');
        
        if (!$temp_broker_id || !$temp_broker_email) {
            redirect('broker/create');
        }
        
        $broker = $this->Broker_model->get_broker_by_id($temp_broker_id);
        if (!$broker) {
            redirect('broker/create');
        }
        
        $data['broker'] = $broker;
        $this->load->view('broker_set_password', $data);
    }
    
    /**
     * Enregistre le mot de passe (AJAX) - après formulaire de création
     */
    public function save_password() {
        $this->output->set_content_type('application/json');
        
        $temp_broker_id = $this->session->userdata('temp_broker_id');
        $temp_broker_email = $this->session->userdata('temp_broker_email');
        $temp_broker_name = $this->session->userdata('temp_broker_name');
        
        if (!$temp_broker_id || !$temp_broker_email) {
            echo json_encode(['success' => false, 'message' => 'Session expirée. Veuillez recommencer l\'inscription.']);
            return;
        }
        
        $this->form_validation->set_rules('password', 'Mot de passe', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirmation', 'required|matches[password]');
        
        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['success' => false, 'errors' => $this->form_validation->error_array()]);
            return;
        }
        
        $password = $this->input->post('password');
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $existing_user = $this->Broker_model->get_user_by_email($temp_broker_email);
        
        if ($existing_user) {
            $this->Broker_model->update_user_password($existing_user->id, $hashed_password);
            $user_id = $existing_user->id;
        } else {
            $name_parts = explode(' ', $temp_broker_name, 2);
            $prenom = $name_parts[0];
            $nom = isset($name_parts[1]) ? $name_parts[1] : '';
            
            $user_data = [
                'uuid' => $this->generate_uuid(),
                'email' => $temp_broker_email,
                'password' => $hashed_password,
                'nom' => $nom,
                'prenom' => $prenom,
                'role_id' => 8,
                'type_utilisateur' => 'broker',
                'is_active' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'est_verifie' => 1
            ];
            
            $user_id = $this->Broker_model->insert_user($user_data);
        }
        
        if ($user_id) {
            $this->session->unset_userdata('temp_broker_id');
            $this->session->unset_userdata('temp_broker_email');
            $this->session->unset_userdata('temp_broker_name');
            
            $this->session->set_userdata('broker_id', $temp_broker_id);
            $this->session->set_userdata('broker_name', $temp_broker_name);
            $this->session->set_userdata('user_id', $user_id);
            $this->session->set_userdata('user_email', $temp_broker_email);
            $this->session->set_userdata('user_type', 'broker');
            
            echo json_encode(['success' => true, 'message' => 'Mot de passe créé avec succès ! Redirection vers le dashboard...']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du compte.']);
        }
    }
    
    /**
     * Génère un UUID v4
     */
    private function generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Ajouter un investisseur (AJAX)
     */
    public function add_investor() {
        $broker_id = $this->session->userdata('broker_id');
        if (!$broker_id) {
            echo json_encode(['success' => false, 'message' => 'Session expirée.']);
            return;
        }
        
        $this->form_validation->set_rules('full_name', 'Nom complet', 'required|max_length[150]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[150]');
        
        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['success' => false, 'message' => 'Veuillez remplir les champs requis.']);
            return;
        }
        
        $data = [
            'broker_id' => $broker_id,
            'full_name' => $this->input->post('full_name'),
            'organization' => $this->input->post('organization'),
            'email' => $this->input->post('email'),
            'phone' => $this->input->post('phone'),
            'commitment_range' => $this->input->post('commitment_range'),
            'status' => $this->input->post('status'),
            'notes' => $this->input->post('notes')
        ];
        
        if ($this->Broker_model->insert_investor($data)) {
            echo json_encode(['success' => true, 'message' => 'Investisseur ajouté avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout.']);
        }
    }
    
    /**
     * Modifier un investisseur (AJAX)
     */
    public function update_investor($id) {
        $broker_id = $this->session->userdata('broker_id');
        if (!$broker_id) {
            echo json_encode(['success' => false, 'message' => 'Session expirée.']);
            return;
        }
        
        $investor = $this->Broker_model->get_investor_by_id($id, $broker_id);
        if (!$investor) {
            echo json_encode(['success' => false, 'message' => 'Investisseur non trouvé.']);
            return;
        }
        
        $data = [
            'full_name' => $this->input->post('full_name'),
            'organization' => $this->input->post('organization'),
            'email' => $this->input->post('email'),
            'phone' => $this->input->post('phone'),
            'commitment_range' => $this->input->post('commitment_range'),
            'status' => $this->input->post('status'),
            'notes' => $this->input->post('notes')
        ];
        
        if ($this->Broker_model->update_investor($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Investisseur modifié avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification.']);
        }
    }
    
    /**
     * Supprimer un investisseur (AJAX)
     */
    public function delete_investor($id) {
        $broker_id = $this->session->userdata('broker_id');
        if (!$broker_id) {
            echo json_encode(['success' => false, 'message' => 'Session expirée.']);
            return;
        }
        
        if ($this->Broker_model->delete_investor($id, $broker_id)) {
            echo json_encode(['success' => true, 'message' => 'Investisseur supprimé.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression.']);
        }
    }
    
    /**
     * Récupérer un investisseur (AJAX)
     */
    public function get_investor($id) {
        $broker_id = $this->session->userdata('broker_id');
        if (!$broker_id) {
            echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
            return;
        }
        
        $investor = $this->Broker_model->get_investor_by_id($id, $broker_id);
        if ($investor) {
            echo json_encode(['success' => true, 'investor' => $investor]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Investisseur non trouvé.']);
        }
    }
}
?>