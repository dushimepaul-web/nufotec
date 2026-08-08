<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Investissement_phases extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
        $this->load->helper('form');
        $this->load->helper('investissement_helper');
        $this->load->library('form_validation');
    }
    
    public function index()
    {
        $data['phases'] = $this->Model->read('investissement_phases', [], 'annee_debut', 'ASC');
        $this->load->view('Investissement_phases_View', $data);
    }

    function PhaseDetail($phaseDetail = null)
    {
        $id = (int) current(explode('_', (string) $phaseDetail));

        if (empty($id)) {
            $this->session->set_flashdata('error', 'Phase introuvable.');
            redirect(base_url('Investissement_phases'));
            return;
        }

        $data['detail'] = $this->Model->readOne('investissement_phases', ['id_phase' => $id]);

        if (empty($data['detail'])) {
            $this->session->set_flashdata('error', 'Phase introuvable.');
            redirect(base_url('Investissement_phases'));
            return;
        }

        $this->load->view('PhaseDetail_View', $data);
    }

    function Create(){
        $this->form_validation->set_rules('nom_phase', 'Nom de la phase', 'required');
        $this->form_validation->set_rules('annee_debut', 'Année début', 'required|numeric');
        $this->form_validation->set_rules('montant_total', 'Montant total', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Investissement_phases'));
            return;
        }

        $nom_phase = $this->input->post('nom_phase');
        $annee_debut = $this->input->post('annee_debut');
        $annee_fin = $this->input->post('annee_fin') ?: NULL;
        $montant_total = $this->input->post('montant_total');
        $devise = $this->input->post('devise') ?: 'USD';
        $description = $this->input->post('description');
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        // Construction du JSON allocation_details
        $allocation_details = [];
        $categories = $this->input->post('allocation_categorie');
        $pourcentages = $this->input->post('allocation_pourcentage');
        
        if (!empty($categories)) {
            foreach ($categories as $index => $categorie) {
                if (!empty($categorie) && isset($pourcentages[$index])) {
                    $allocation_details[$categorie] = floatval($pourcentages[$index]);
                }
            }
        }

        $data = array(
            'nom_phase' => $nom_phase,
            'annee_debut' => $annee_debut,
            'annee_fin' => $annee_fin,
            'montant_total' => $montant_total,
            'devise' => $devise,
            'description' => $description,
            'allocation_details' => !empty($allocation_details) ? json_encode($allocation_details) : NULL,
            'id_page_associee' => $id_page_associee
        );
        
        $rsp = $this->Model->create('investissement_phases', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Phase d\'investissement créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Investissement_phases'));
    }

    function Update(){
        $id = $this->input->post('id_phase');
        
        $this->form_validation->set_rules('nom_phase', 'Nom de la phase', 'required');
        $this->form_validation->set_rules('annee_debut', 'Année début', 'required|numeric');
        $this->form_validation->set_rules('montant_total', 'Montant total', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Investissement_phases'));
            return;
        }

        $nom_phase = $this->input->post('nom_phase');
        $annee_debut = $this->input->post('annee_debut');
        $annee_fin = $this->input->post('annee_fin') ?: NULL;
        $montant_total = $this->input->post('montant_total');
        $devise = $this->input->post('devise') ?: 'USD';
        $description = $this->input->post('description');
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        // Construction du JSON allocation_details
        $allocation_details = [];
        $categories = $this->input->post('allocation_categorie');
        $pourcentages = $this->input->post('allocation_pourcentage');
        
        if (!empty($categories)) {
            foreach ($categories as $index => $categorie) {
                if (!empty($categorie) && isset($pourcentages[$index])) {
                    $allocation_details[$categorie] = floatval($pourcentages[$index]);
                }
            }
        }

        $data = array(
            'nom_phase' => $nom_phase,
            'annee_debut' => $annee_debut,
            'annee_fin' => $annee_fin,
            'montant_total' => $montant_total,
            'devise' => $devise,
            'description' => $description,
            'allocation_details' => !empty($allocation_details) ? json_encode($allocation_details) : NULL,
            'id_page_associee' => $id_page_associee
        );

        $rsp = $this->Model->update('investissement_phases', ['id_phase' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Phase d\'investissement mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Investissement_phases'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        $rsp = $this->Model->delete('investissement_phases', ['id_phase' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Phase d\'investissement supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Investissement_phases'));
    }
}
