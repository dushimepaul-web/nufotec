<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Broker extends MX_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Broker_model');
        $this->load->library('form_validation');
        $this->load->helper('url');
        $this->load->helper('form');
    }
    
    /**
     * Affiche le formulaire d'inscription
     */
    public function create() {
        $data['countries'] = $this->Broker_model->get_all_pays();
        $this->load->view('broker_form', $data);
    }
    
    /**
     * Liste des brokers (admin)
     */
    public function index() {
        // Récupérer les filtres
        $filters = array(
            'search' => $this->input->get('search'),
            'regulatory_status' => $this->input->get('regulatory_status'),
            'id_pays' => $this->input->get('id_pays')
        );
        
        // Supprimer les filtres vides
        $filters = array_filter($filters);
        
        // Pagination
        $page = (int) $this->input->get('page');
        if ($page < 1) $page = 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        // Récupérer les données
        $data['brokers'] = $this->Broker_model->get_all_brokers($per_page, $offset, $filters);
        $data['total_brokers'] = $this->Broker_model->count_all_brokers($filters);  // ← Variable définie
        $data['total_pages'] = ceil($data['total_brokers'] / $per_page);  // ← Variable définie
        $data['current_page'] = $page;
        $data['filters'] = $filters;
        $data['countries'] = $this->Broker_model->get_all_pays();
        
        // Charger la vue
        $this->load->view('Brokers_View', $data);
    }
    
    /**
     * Enregistre un nouveau broker (AJAX)
     */
    public function store() {
        // Définir les règles de validation
        $this->form_validation->set_rules('full_name', 'Nom complet', 'required|max_length[150]');
        $this->form_validation->set_rules('firm_name', 'Nom de l\'entreprise', 'required|max_length[200]');
        $this->form_validation->set_rules('id_pays', 'Pays', 'required|integer');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[150]');
        $this->form_validation->set_rules('confirm_authorized', 'Confirmation autorisation', 'required');
        $this->form_validation->set_rules('confirm_aml_kyc', 'Confirmation AML/KYC', 'required');
        $this->form_validation->set_rules('acknowledge_no_exclusivity', 'Reconnaissance non-exclusivité', 'required');
        $this->form_validation->set_rules('understand_formal_mandate_required', 'Compréhension mandat', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            echo json_encode([
                'success' => false,
                'errors' => $errors
            ]);
            return;
        }
        
        // Vérifier si l'email existe
        if ($this->Broker_model->email_exists($this->input->post('email'))) {
            echo json_encode([
                'success' => false,
                'errors' => ['email' => 'Cette adresse email est déjà enregistrée.']
            ]);
            return;
        }
        
        // Préparer les données
        $data = array(
            'full_name' => $this->input->post('full_name'),
            'firm_name' => $this->input->post('firm_name'),
            'jurisdiction_of_incorporation' => $this->input->post('jurisdiction_of_incorporation'),
            'registration_number' => $this->input->post('registration_number'),
            'regulatory_status' => $this->input->post('regulatory_status'),
            'regulatory_authority' => $this->input->post('regulatory_authority'),
            'id_pays' => $this->input->post('id_pays'),
            'email' => $this->input->post('email'),
            'mobile_phone' => $this->input->post('mobile_phone'),
            'whatsapp' => $this->input->post('whatsapp'),
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
            'understand_formal_mandate_required' => $this->input->post('understand_formal_mandate_required') ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        );
        
        $insert_id = $this->Broker_model->insert_broker($data);
        
        if ($insert_id) {
            echo json_encode([
                'success' => true,
                'message' => 'Votre inscription a été enregistrée avec succès ! Un consultant vous contactera sous 48h.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement.'
            ]);
        }
    }
    
    /**
     * Affiche les détails d'un broker
     */
    public function show($id) {
        $data['broker'] = $this->Broker_model->get_broker_by_id($id);
        
        if (!$data['broker']) {
            show_404();
        }
        
        $this->load->view('broker/show', $data);
    }
    
    /**
     * Supprime un broker (AJAX)
     */
    public function delete($id) {
        $deleted = $this->Broker_model->delete_broker($id);
        
        if ($deleted) {
            echo json_encode([
                'success' => true,
                'message' => 'Le broker a été supprimé avec succès.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la suppression.'
            ]);
        }
    }
    
    /**
     * Export CSV
     */
    public function export() {
        $brokers = $this->Broker_model->get_all_brokers();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=brokers_' . date('Y-m-d_His') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // En-têtes
        fputcsv($output, [
            'ID', 'Nom complet', 'Entreprise', 'Email', 'Téléphone', 'WhatsApp',
            'Pays', 'Statut réglementaire', 'Autorité', 'Ticket moyen', 'Créé le'
        ]);
        
        foreach ($brokers as $broker) {
            fputcsv($output, [
                $broker->id,
                $broker->full_name,
                $broker->firm_name,
                $broker->email,
                $broker->mobile_phone,
                $broker->whatsapp,
                $broker->country_name ?? 'N/A',
                $broker->regulatory_status,
                $broker->regulatory_authority,
                $broker->typical_ticket_size,
                date('d/m/Y H:i', strtotime($broker->created_at))
            ]);
        }
        
        fclose($output);
    }
}