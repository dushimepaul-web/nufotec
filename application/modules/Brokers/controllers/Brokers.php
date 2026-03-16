<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Brokers extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
        $this->load->helper('form');
        $this->load->library('form_validation');
    }
    
    public function index()
    {
        // Récupérer tous les brokers
        $data['brokers'] = $this->Model->read('brokers', [], 'id', 'DESC');
        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');
        $this->load->view('Brokers_View', $data);
    }

    function BrokerDetail($brokerDetail){
        $id = explode('_', $brokerDetail);
        $data['detail'] = $this->Model->readOne('brokers', ['id' => $id[0]]);
        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');
        $this->load->view('BrokerDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('full_name', 'Nom complet', 'required');
        $this->form_validation->set_rules('firm_name', 'Nom de la société', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[brokers.email]');
        $this->form_validation->set_rules('id_pays', 'Pays', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Brokers'));
            return;
        }

        // Données de base
        $full_name = $this->input->post('full_name');
        $firm_name = $this->input->post('firm_name');
        $jurisdiction_of_incorporation = $this->input->post('jurisdiction_of_incorporation') ?: null;
        $registration_number = $this->input->post('registration_number') ?: null;
        $regulatory_status = $this->input->post('regulatory_status') ?: null;
        $regulatory_authority = $this->input->post('regulatory_authority') ?: null;
        $id_pays = $this->input->post('id_pays');
        $email = $this->input->post('email');
        $mobile_phone = $this->input->post('mobile_phone') ?: null;
        $whatsapp = $this->input->post('whatsapp') ?: null;
        $corporate_website = $this->input->post('corporate_website') ?: null;

        // Capacités (checkboxes)
        $capacity_investment_broker = $this->input->post('capacity_investment_broker') ? 1 : 0;
        $capacity_placement_agent = $this->input->post('capacity_placement_agent') ? 1 : 0;
        $capacity_corporate_finance_advisor = $this->input->post('capacity_corporate_finance_advisor') ? 1 : 0;
        $capacity_fund_manager = $this->input->post('capacity_fund_manager') ? 1 : 0;
        $capacity_family_office_rep = $this->input->post('capacity_family_office_rep') ? 1 : 0;
        $capacity_esg_advisor = $this->input->post('capacity_esg_advisor') ? 1 : 0;
        $capacity_independent_introducer = $this->input->post('capacity_independent_introducer') ? 1 : 0;
        $capacity_other = $this->input->post('capacity_other') ?: null;

        // Types d'investisseurs (checkboxes)
        $investor_private_equity = $this->input->post('investor_private_equity') ? 1 : 0;
        $investor_venture_capital = $this->input->post('investor_venture_capital') ? 1 : 0;
        $investor_esg_impact = $this->input->post('investor_esg_impact') ? 1 : 0;
        $investor_dfi = $this->input->post('investor_dfi') ? 1 : 0;
        $investor_institutional = $this->input->post('investor_institutional') ? 1 : 0;
        $investor_hnwi = $this->input->post('investor_hnwi') ? 1 : 0;
        $investor_sovereign = $this->input->post('investor_sovereign') ? 1 : 0;
        $typical_ticket_size = $this->input->post('typical_ticket_size') ?: null;
        $geographic_coverage = $this->input->post('geographic_coverage') ?: null;

        // Mandats (checkboxes)
        $mandate_equity = $this->input->post('mandate_equity') ? 1 : 0;
        $mandate_structured_debt = $this->input->post('mandate_structured_debt') ? 1 : 0;
        $mandate_blended_finance = $this->input->post('mandate_blended_finance') ? 1 : 0;
        $mandate_grant = $this->input->post('mandate_grant') ? 1 : 0;
        $mandate_strategic_partnership = $this->input->post('mandate_strategic_partnership') ? 1 : 0;
        $mandate_full_program = $this->input->post('mandate_full_program') ? 1 : 0;

        // Modèle d'engagement
        $engagement_model = $this->input->post('engagement_model') ?: null;

        // Conformité
        $confirm_authorized = $this->input->post('confirm_authorized') ? 1 : 0;
        $confirm_aml_kyc = $this->input->post('confirm_aml_kyc') ? 1 : 0;
        $acknowledge_no_exclusivity = $this->input->post('acknowledge_no_exclusivity') ? 1 : 0;
        $understand_formal_mandate_required = $this->input->post('understand_formal_mandate_required') ? 1 : 0;

        $data = array(
            'full_name' => $full_name,
            'firm_name' => $firm_name,
            'jurisdiction_of_incorporation' => $jurisdiction_of_incorporation,
            'registration_number' => $registration_number,
            'regulatory_status' => $regulatory_status,
            'regulatory_authority' => $regulatory_authority,
            'id_pays' => $id_pays,
            'email' => $email,
            'mobile_phone' => $mobile_phone,
            'whatsapp' => $whatsapp,
            'corporate_website' => $corporate_website,
            'capacity_investment_broker' => $capacity_investment_broker,
            'capacity_placement_agent' => $capacity_placement_agent,
            'capacity_corporate_finance_advisor' => $capacity_corporate_finance_advisor,
            'capacity_fund_manager' => $capacity_fund_manager,
            'capacity_family_office_rep' => $capacity_family_office_rep,
            'capacity_esg_advisor' => $capacity_esg_advisor,
            'capacity_independent_introducer' => $capacity_independent_introducer,
            'capacity_other' => $capacity_other,
            'investor_private_equity' => $investor_private_equity,
            'investor_venture_capital' => $investor_venture_capital,
            'investor_esg_impact' => $investor_esg_impact,
            'investor_dfi' => $investor_dfi,
            'investor_institutional' => $investor_institutional,
            'investor_hnwi' => $investor_hnwi,
            'investor_sovereign' => $investor_sovereign,
            'typical_ticket_size' => $typical_ticket_size,
            'geographic_coverage' => $geographic_coverage,
            'mandate_equity' => $mandate_equity,
            'mandate_structured_debt' => $mandate_structured_debt,
            'mandate_blended_finance' => $mandate_blended_finance,
            'mandate_grant' => $mandate_grant,
            'mandate_strategic_partnership' => $mandate_strategic_partnership,
            'mandate_full_program' => $mandate_full_program,
            'engagement_model' => $engagement_model,
            'confirm_authorized' => $confirm_authorized,
            'confirm_aml_kyc' => $confirm_aml_kyc,
            'acknowledge_no_exclusivity' => $acknowledge_no_exclusivity,
            'understand_formal_mandate_required' => $understand_formal_mandate_required,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('brokers', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Broker créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Brokers'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('full_name', 'Nom complet', 'required');
        $this->form_validation->set_rules('firm_name', 'Nom de la société', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('id_pays', 'Pays', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Brokers'));
            return;
        }

        // Récupérer le broker existant
        $broker = $this->Model->readOne('brokers', ['id' => $id]);
        if (!$broker) {
            $this->session->set_flashdata('error', 'Broker non trouvé');
            redirect(base_url('Brokers'));
            return;
        }

        // Vérifier l'unicité de l'email (sauf pour le même broker)
        $existing = $this->Model->readOne('brokers', ['email' => $this->input->post('email')]);
        if ($existing && $existing['id'] != $id) {
            $this->session->set_flashdata('error', 'Cet email est déjà utilisé par un autre broker');
            redirect(base_url('Brokers'));
            return;
        }

        // Données de base
        $full_name = $this->input->post('full_name');
        $firm_name = $this->input->post('firm_name');
        $jurisdiction_of_incorporation = $this->input->post('jurisdiction_of_incorporation') ?: null;
        $registration_number = $this->input->post('registration_number') ?: null;
        $regulatory_status = $this->input->post('regulatory_status') ?: null;
        $regulatory_authority = $this->input->post('regulatory_authority') ?: null;
        $id_pays = $this->input->post('id_pays');
        $email = $this->input->post('email');
        $mobile_phone = $this->input->post('mobile_phone') ?: null;
        $whatsapp = $this->input->post('whatsapp') ?: null;
        $corporate_website = $this->input->post('corporate_website') ?: null;

        // Capacités (checkboxes)
        $capacity_investment_broker = $this->input->post('capacity_investment_broker') ? 1 : 0;
        $capacity_placement_agent = $this->input->post('capacity_placement_agent') ? 1 : 0;
        $capacity_corporate_finance_advisor = $this->input->post('capacity_corporate_finance_advisor') ? 1 : 0;
        $capacity_fund_manager = $this->input->post('capacity_fund_manager') ? 1 : 0;
        $capacity_family_office_rep = $this->input->post('capacity_family_office_rep') ? 1 : 0;
        $capacity_esg_advisor = $this->input->post('capacity_esg_advisor') ? 1 : 0;
        $capacity_independent_introducer = $this->input->post('capacity_independent_introducer') ? 1 : 0;
        $capacity_other = $this->input->post('capacity_other') ?: null;

        // Types d'investisseurs (checkboxes)
        $investor_private_equity = $this->input->post('investor_private_equity') ? 1 : 0;
        $investor_venture_capital = $this->input->post('investor_venture_capital') ? 1 : 0;
        $investor_esg_impact = $this->input->post('investor_esg_impact') ? 1 : 0;
        $investor_dfi = $this->input->post('investor_dfi') ? 1 : 0;
        $investor_institutional = $this->input->post('investor_institutional') ? 1 : 0;
        $investor_hnwi = $this->input->post('investor_hnwi') ? 1 : 0;
        $investor_sovereign = $this->input->post('investor_sovereign') ? 1 : 0;
        $typical_ticket_size = $this->input->post('typical_ticket_size') ?: null;
        $geographic_coverage = $this->input->post('geographic_coverage') ?: null;

        // Mandats (checkboxes)
        $mandate_equity = $this->input->post('mandate_equity') ? 1 : 0;
        $mandate_structured_debt = $this->input->post('mandate_structured_debt') ? 1 : 0;
        $mandate_blended_finance = $this->input->post('mandate_blended_finance') ? 1 : 0;
        $mandate_grant = $this->input->post('mandate_grant') ? 1 : 0;
        $mandate_strategic_partnership = $this->input->post('mandate_strategic_partnership') ? 1 : 0;
        $mandate_full_program = $this->input->post('mandate_full_program') ? 1 : 0;

        // Modèle d'engagement
        $engagement_model = $this->input->post('engagement_model') ?: null;

        // Conformité
        $confirm_authorized = $this->input->post('confirm_authorized') ? 1 : 0;
        $confirm_aml_kyc = $this->input->post('confirm_aml_kyc') ? 1 : 0;
        $acknowledge_no_exclusivity = $this->input->post('acknowledge_no_exclusivity') ? 1 : 0;
        $understand_formal_mandate_required = $this->input->post('understand_formal_mandate_required') ? 1 : 0;

        $data = array(
            'full_name' => $full_name,
            'firm_name' => $firm_name,
            'jurisdiction_of_incorporation' => $jurisdiction_of_incorporation,
            'registration_number' => $registration_number,
            'regulatory_status' => $regulatory_status,
            'regulatory_authority' => $regulatory_authority,
            'id_pays' => $id_pays,
            'email' => $email,
            'mobile_phone' => $mobile_phone,
            'whatsapp' => $whatsapp,
            'corporate_website' => $corporate_website,
            'capacity_investment_broker' => $capacity_investment_broker,
            'capacity_placement_agent' => $capacity_placement_agent,
            'capacity_corporate_finance_advisor' => $capacity_corporate_finance_advisor,
            'capacity_fund_manager' => $capacity_fund_manager,
            'capacity_family_office_rep' => $capacity_family_office_rep,
            'capacity_esg_advisor' => $capacity_esg_advisor,
            'capacity_independent_introducer' => $capacity_independent_introducer,
            'capacity_other' => $capacity_other,
            'investor_private_equity' => $investor_private_equity,
            'investor_venture_capital' => $investor_venture_capital,
            'investor_esg_impact' => $investor_esg_impact,
            'investor_dfi' => $investor_dfi,
            'investor_institutional' => $investor_institutional,
            'investor_hnwi' => $investor_hnwi,
            'investor_sovereign' => $investor_sovereign,
            'typical_ticket_size' => $typical_ticket_size,
            'geographic_coverage' => $geographic_coverage,
            'mandate_equity' => $mandate_equity,
            'mandate_structured_debt' => $mandate_structured_debt,
            'mandate_blended_finance' => $mandate_blended_finance,
            'mandate_grant' => $mandate_grant,
            'mandate_strategic_partnership' => $mandate_strategic_partnership,
            'mandate_full_program' => $mandate_full_program,
            'engagement_model' => $engagement_model,
            'confirm_authorized' => $confirm_authorized,
            'confirm_aml_kyc' => $confirm_aml_kyc,
            'acknowledge_no_exclusivity' => $acknowledge_no_exclusivity,
            'understand_formal_mandate_required' => $understand_formal_mandate_required,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $rsp = $this->Model->update('brokers', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Broker mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Brokers'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer le broker
        $broker = $this->Model->readOne('brokers', ['id' => $id]);
        
        if (!$broker) {
            $this->session->set_flashdata('error', 'Broker non trouvé');
            redirect(base_url('Brokers'));
            return;
        }

        // Suppression physique
        $rsp = $this->Model->delete('brokers', ['id' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Broker supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Brokers'));
    }

    /**
     * Export des brokers en CSV
     */
    public function export_csv() {
        $brokers = $this->Model->read('brokers', [], 'id', 'DESC');
        
        // Nom du fichier
        $filename = 'brokers_' . date('Y-m-d') . '.csv';
        
        // En-têtes CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Ouvrir le flux de sortie
        $output = fopen('php://output', 'w');
        
        // En-têtes des colonnes
        fputcsv($output, [
            'ID', 'Nom complet', 'Société', 'Email', 'Téléphone', 'Pays ID',
            'Statut régulatoire', 'Capacités', 'Types investisseurs', 'Mandats',
            'Modèle engagement', 'Date création'
        ]);
        
        // Données
        foreach ($brokers as $broker) {
            // Formater les capacités
            $capacities = [];
            if ($broker['capacity_investment_broker']) $capacities[] = 'Investment Broker';
            if ($broker['capacity_placement_agent']) $capacities[] = 'Placement Agent';
            if ($broker['capacity_corporate_finance_advisor']) $capacities[] = 'Corporate Finance';
            if ($broker['capacity_fund_manager']) $capacities[] = 'Fund Manager';
            if ($broker['capacity_family_office_rep']) $capacities[] = 'Family Office';
            if ($broker['capacity_esg_advisor']) $capacities[] = 'ESG Advisor';
            if ($broker['capacity_independent_introducer']) $capacities[] = 'Independent';
            if ($broker['capacity_other']) $capacities[] = 'Autre: ' . $broker['capacity_other'];
            
            $capacities_str = implode(' | ', $capacities);
            
            // Formater les investisseurs
            $investors = [];
            if ($broker['investor_private_equity']) $investors[] = 'PE';
            if ($broker['investor_venture_capital']) $investors[] = 'VC';
            if ($broker['investor_esg_impact']) $investors[] = 'ESG';
            if ($broker['investor_dfi']) $investors[] = 'DFI';
            if ($broker['investor_institutional']) $investors[] = 'Institutionnel';
            if ($broker['investor_hnwi']) $investors[] = 'HNWI';
            if ($broker['investor_sovereign']) $investors[] = 'Souverain';
            
            $investors_str = implode(' | ', $investors);
            
            // Formater les mandats
            $mandates = [];
            if ($broker['mandate_equity']) $mandates[] = 'Equity';
            if ($broker['mandate_structured_debt']) $mandates[] = 'Debt';
            if ($broker['mandate_blended_finance']) $mandates[] = 'Blended';
            if ($broker['mandate_grant']) $mandates[] = 'Grant';
            if ($broker['mandate_strategic_partnership']) $mandates[] = 'Strategic';
            if ($broker['mandate_full_program']) $mandates[] = 'Full Program';
            
            $mandates_str = implode(' | ', $mandates);
            
            fputcsv($output, [
                $broker['id'],
                $broker['full_name'],
                $broker['firm_name'],
                $broker['email'],
                $broker['mobile_phone'],
                $broker['id_pays'],
                $broker['regulatory_status'],
                $capacities_str,
                $investors_str,
                $mandates_str,
                $broker['engagement_model'],
                $broker['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Vue détaillée d'un broker
     */
    public function view($id) {
        $data['broker'] = $this->Model->readOne('brokers', ['id' => $id]);
        $data['pays'] = $this->Model->readOne('pays', ['id' => $data['broker']['id_pays']]);
        
        if (!$data['broker']) {
            show_404();
        }
        
        $this->load->view('BrokerView_View', $data);
    }
}