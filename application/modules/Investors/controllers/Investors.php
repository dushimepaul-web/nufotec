<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Investors extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
        $this->load->helper('form');
        $this->load->library('form_validation');
    }
    
    public function index()
    {
        // Récupérer tous les investisseurs
        $data['investors'] = $this->Model->read('investors', [], 'id', 'DESC');
        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');
        $this->load->view('Investors_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $is_active = $this->input->post('is_active');
        
        // Note: La table investors n'a pas de champ is_active
        // Si vous voulez ajouter ce champ, modifiez la table
        // Sinon, cette fonction peut être supprimée ou adaptée
        
        $this->session->set_flashdata('error', 'Fonction non disponible pour les investisseurs');
        redirect(base_url('Investors'));    
    }

    function InvestorDetail($investorDetail){
        $id = explode('_', $investorDetail);
        $data['detail'] = $this->Model->readOne('investors', ['id' => $id[0]]);
        $data['pays'] = $this->Model->read('pays', [], 'pays', 'ASC');
        $this->load->view('InvestorDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('full_name', 'Nom complet', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[investors.email]');
        $this->form_validation->set_rules('id_pays', 'Pays', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Investors'));
            return;
        }

        // Données de base
        $full_name = $this->input->post('full_name');
        $organization = $this->input->post('organization') ?: null;
        $position_title = $this->input->post('position_title') ?: null;
        $id_pays = $this->input->post('id_pays');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone') ?: null;

        // Types d'intérêt (checkboxes)
        $interest_equity = $this->input->post('interest_equity') ? 1 : 0;
        $interest_debt = $this->input->post('interest_debt') ? 1 : 0;
        $interest_blended_finance = $this->input->post('interest_blended_finance') ? 1 : 0;
        $interest_grant = $this->input->post('interest_grant') ? 1 : 0;
        $interest_strategic_partnership = $this->input->post('interest_strategic_partnership') ? 1 : 0;
        $interest_technical_collaboration = $this->input->post('interest_technical_collaboration') ? 1 : 0;
        $interest_offtake_distribution = $this->input->post('interest_offtake_distribution') ? 1 : 0;
        $interest_other = $this->input->post('interest_other') ?: null;

        // Fourchette d'engagement
        $commitment_range = $this->input->post('commitment_range') ?: null;

        // Focus areas (checkboxes)
        $focus_research_lab = $this->input->post('focus_research_lab') ? 1 : 0;
        $focus_gmp_facility = $this->input->post('focus_gmp_facility') ? 1 : 0;
        $focus_medicinal_plant = $this->input->post('focus_medicinal_plant') ? 1 : 0;
        $focus_commercialization = $this->input->post('focus_commercialization') ? 1 : 0;
        $focus_full_platform = $this->input->post('focus_full_platform') ? 1 : 0;

        // Timeline
        $timeline = $this->input->post('timeline') ?: 'Exploratory';

        // Message stratégique
        $strategic_message = $this->input->post('strategic_message') ?: null;

        // Conformité
        $agree_contact = $this->input->post('agree_contact') ? 1 : 0;
        $non_binding_confirmation = $this->input->post('non_binding_confirmation') ? 1 : 0;

        $data = array(
            'full_name' => $full_name,
            'organization' => $organization,
            'position_title' => $position_title,
            'id_pays' => $id_pays,
            'email' => $email,
            'phone' => $phone,
            'interest_equity' => $interest_equity,
            'interest_debt' => $interest_debt,
            'interest_blended_finance' => $interest_blended_finance,
            'interest_grant' => $interest_grant,
            'interest_strategic_partnership' => $interest_strategic_partnership,
            'interest_technical_collaboration' => $interest_technical_collaboration,
            'interest_offtake_distribution' => $interest_offtake_distribution,
            'interest_other' => $interest_other,
            'commitment_range' => $commitment_range,
            'focus_research_lab' => $focus_research_lab,
            'focus_gmp_facility' => $focus_gmp_facility,
            'focus_medicinal_plant' => $focus_medicinal_plant,
            'focus_commercialization' => $focus_commercialization,
            'focus_full_platform' => $focus_full_platform,
            'timeline' => $timeline,
            'strategic_message' => $strategic_message,
            'agree_contact' => $agree_contact,
            'non_binding_confirmation' => $non_binding_confirmation,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('investors', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Investisseur créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Investors'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('full_name', 'Nom complet', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('id_pays', 'Pays', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Investors'));
            return;
        }

        // Récupérer l'investisseur existant
        $investor = $this->Model->readOne('investors', ['id' => $id]);
        if (!$investor) {
            $this->session->set_flashdata('error', 'Investisseur non trouvé');
            redirect(base_url('Investors'));
            return;
        }

        // Vérifier l'unicité de l'email (sauf pour le même investisseur)
        $existing = $this->Model->readOne('investors', ['email' => $this->input->post('email')]);
        if ($existing && $existing['id'] != $id) {
            $this->session->set_flashdata('error', 'Cet email est déjà utilisé par un autre investisseur');
            redirect(base_url('Investors'));
            return;
        }

        // Données de base
        $full_name = $this->input->post('full_name');
        $organization = $this->input->post('organization') ?: null;
        $position_title = $this->input->post('position_title') ?: null;
        $id_pays = $this->input->post('id_pays');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone') ?: null;

        // Types d'intérêt (checkboxes)
        $interest_equity = $this->input->post('interest_equity') ? 1 : 0;
        $interest_debt = $this->input->post('interest_debt') ? 1 : 0;
        $interest_blended_finance = $this->input->post('interest_blended_finance') ? 1 : 0;
        $interest_grant = $this->input->post('interest_grant') ? 1 : 0;
        $interest_strategic_partnership = $this->input->post('interest_strategic_partnership') ? 1 : 0;
        $interest_technical_collaboration = $this->input->post('interest_technical_collaboration') ? 1 : 0;
        $interest_offtake_distribution = $this->input->post('interest_offtake_distribution') ? 1 : 0;
        $interest_other = $this->input->post('interest_other') ?: null;

        // Fourchette d'engagement
        $commitment_range = $this->input->post('commitment_range') ?: null;

        // Focus areas (checkboxes)
        $focus_research_lab = $this->input->post('focus_research_lab') ? 1 : 0;
        $focus_gmp_facility = $this->input->post('focus_gmp_facility') ? 1 : 0;
        $focus_medicinal_plant = $this->input->post('focus_medicinal_plant') ? 1 : 0;
        $focus_commercialization = $this->input->post('focus_commercialization') ? 1 : 0;
        $focus_full_platform = $this->input->post('focus_full_platform') ? 1 : 0;

        // Timeline
        $timeline = $this->input->post('timeline') ?: 'Exploratory';

        // Message stratégique
        $strategic_message = $this->input->post('strategic_message') ?: null;

        // Conformité
        $agree_contact = $this->input->post('agree_contact') ? 1 : 0;
        $non_binding_confirmation = $this->input->post('non_binding_confirmation') ? 1 : 0;

        $data = array(
            'full_name' => $full_name,
            'organization' => $organization,
            'position_title' => $position_title,
            'id_pays' => $id_pays,
            'email' => $email,
            'phone' => $phone,
            'interest_equity' => $interest_equity,
            'interest_debt' => $interest_debt,
            'interest_blended_finance' => $interest_blended_finance,
            'interest_grant' => $interest_grant,
            'interest_strategic_partnership' => $interest_strategic_partnership,
            'interest_technical_collaboration' => $interest_technical_collaboration,
            'interest_offtake_distribution' => $interest_offtake_distribution,
            'interest_other' => $interest_other,
            'commitment_range' => $commitment_range,
            'focus_research_lab' => $focus_research_lab,
            'focus_gmp_facility' => $focus_gmp_facility,
            'focus_medicinal_plant' => $focus_medicinal_plant,
            'focus_commercialization' => $focus_commercialization,
            'focus_full_platform' => $focus_full_platform,
            'timeline' => $timeline,
            'strategic_message' => $strategic_message,
            'agree_contact' => $agree_contact,
            'non_binding_confirmation' => $non_binding_confirmation,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $rsp = $this->Model->update('investors', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Investisseur mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Investors'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer l'investisseur
        $investor = $this->Model->readOne('investors', ['id' => $id]);
        
        if (!$investor) {
            $this->session->set_flashdata('error', 'Investisseur non trouvé');
            redirect(base_url('Investors'));
            return;
        }

        // Suppression physique (pas de soft delete dans la table investors)
        $rsp = $this->Model->delete('investors', ['id' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Investisseur supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Investors'));
    }

    /**
     * Export des investisseurs en CSV
     */
    public function export_csv() {
        $investors = $this->Model->read('investors', [], 'id', 'DESC');
        
        // Nom du fichier
        $filename = 'investisseurs_' . date('Y-m-d') . '.csv';
        
        // En-têtes CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Ouvrir le flux de sortie
        $output = fopen('php://output', 'w');
        
        // En-têtes des colonnes
        fputcsv($output, [
            'ID', 'Nom complet', 'Organisation', 'Poste', 'Pays ID', 
            'Email', 'Téléphone', 'Intérêts', 'Fourchette', 'Timeline', 
            'Date création'
        ]);
        
        // Données
        foreach ($investors as $inv) {
            // Formater les intérêts
            $interests = [];
            if ($inv['interest_equity']) $interests[] = 'Equity';
            if ($inv['interest_debt']) $interests[] = 'Debt';
            if ($inv['interest_blended_finance']) $interests[] = 'Blended';
            if ($inv['interest_grant']) $interests[] = 'Grant';
            if ($inv['interest_strategic_partnership']) $interests[] = 'Strategic';
            if ($inv['interest_technical_collaboration']) $interests[] = 'Technical';
            if ($inv['interest_offtake_distribution']) $interests[] = 'Offtake';
            if ($inv['interest_other']) $interests[] = 'Autre: ' . $inv['interest_other'];
            
            $interests_str = implode(' | ', $interests);
            
            fputcsv($output, [
                $inv['id'],
                $inv['full_name'],
                $inv['organization'],
                $inv['position_title'],
                $inv['id_pays'],
                $inv['email'],
                $inv['phone'],
                $interests_str,
                $inv['commitment_range'],
                $inv['timeline'],
                $inv['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Vue détaillée d'un investisseur (alternative à InvestorDetail)
     */
    public function view($id) {
        $data['investor'] = $this->Model->readOne('investors', ['id' => $id]);
        $data['pays'] = $this->Model->readOne('pays', ['id' => $data['investor']['id_pays']]);
        
        if (!$data['investor']) {
            show_404();
        }
        
        $this->load->view('InvestorView_View', $data);
    }
}