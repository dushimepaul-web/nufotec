<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Risques_mitigations extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('Admin');
        }
        $this->load->helper('form');
        $this->load->library('form_validation');
    }
    
    public function index()
    {
        $data['risques'] = $this->Model->read('risques_mitigations', ['deleted_at' => NULL], 'ordre', 'ASC');
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL], 'id_page', 'ASC');
        $this->load->view('Risques_mitigations_View', $data);
    }

    function ChangeNiveau(){
        $id = $this->input->post('id');
        $niveau_actuel = $this->input->post('niveau_risque');
        
        // Cycle: faible -> moyen -> eleve -> faible
        $niveaux = ['faible' => 'moyen', 'moyen' => 'eleve', 'eleve' => 'faible'];
        $nouveau_niveau = $niveaux[$niveau_actuel] ?? 'moyen';
        
        $rsp = $this->Model->update('risques_mitigations', ['id_risque' => $id], [
            'niveau_risque' => $nouveau_niveau,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Niveau de risque mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Risques_mitigations'));    
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('risque', 'Risque', 'required');
        $this->form_validation->set_rules('mitigation', 'Mitigation', 'required');
        $this->form_validation->set_rules('niveau_risque', 'Niveau de risque', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Risques_mitigations'));
            return;
        }

        $risque = $this->input->post('risque');
        $mitigation = $this->input->post('mitigation');
        $categorie = $this->input->post('categorie') ?: NULL;
        $niveau_risque = $this->input->post('niveau_risque');
        $ordre = $this->input->post('ordre') ?: 0;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'risque' => $risque,
            'mitigation' => $mitigation,
            'categorie' => $categorie,
            'niveau_risque' => $niveau_risque,
            'ordre' => $ordre,
            'id_page_associee' => $id_page_associee,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('risques_mitigations', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Risque et mitigation créés avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Risques_mitigations'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('risque', 'Risque', 'required');
        $this->form_validation->set_rules('mitigation', 'Mitigation', 'required');
        $this->form_validation->set_rules('niveau_risque', 'Niveau de risque', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Risques_mitigations'));
            return;
        }

        $risque = $this->input->post('risque');
        $mitigation = $this->input->post('mitigation');
        $categorie = $this->input->post('categorie') ?: NULL;
        $niveau_risque = $this->input->post('niveau_risque');
        $ordre = $this->input->post('ordre') ?: 0;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'risque' => $risque,
            'mitigation' => $mitigation,
            'categorie' => $categorie,
            'niveau_risque' => $niveau_risque,
            'ordre' => $ordre,
            'id_page_associee' => $id_page_associee,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $rsp = $this->Model->update('risques_mitigations', ['id_risque' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Risque et mitigation mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Risques_mitigations'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Soft delete
        $rsp = $this->Model->update('risques_mitigations', ['id_risque' => $id], [
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Risque supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Risques_mitigations'));
    }
}