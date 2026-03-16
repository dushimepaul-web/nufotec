<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Etapes_projet extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // Récupérer les étapes du projet
        $data['etapes'] = $this->Model->read('etapes_projet', [], 'date_debut', 'ASC');
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('Etapes_projet_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $current_status = $this->input->post('statut');
        
        // Cycle de statut: a_venir -> en_cours -> termine -> retarde -> a_venir
        $status_cycle = [
            'a_venir' => 'en_cours',
            'en_cours' => 'termine',
            'termine' => 'retarde',
            'retarde' => 'a_venir'
        ];
        
        $new_status = $status_cycle[$current_status] ?? 'a_venir';
        
        // Si terminé, mettre à jour la date de fin réelle
        $update_data = ['statut' => $new_status];
        if ($new_status == 'termine') {
            $update_data['date_fin_reelle'] = date('Y-m-d');
            $update_data['pourcentage_avancement'] = 100;
        }
        
        $rsp = $this->Model->update('etapes_projet', ['id_etape' => $id], $update_data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Etapes_projet'));    
    }

    function UpdateProgress(){
        $id = $this->input->post('id');
        $progress = $this->input->post('pourcentage_avancement');
        
        $rsp = $this->Model->update('etapes_projet', ['id_etape' => $id], [
            'pourcentage_avancement' => $progress
        ]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Progression mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue.');
        }
        redirect(base_url('Etapes_projet'));    
    }

    function EtapeDetail($etapeDetail){
        $id = $etapeDetail;
        $data['detail'] = $this->Model->readOne('etapes_projet', ['id_etape' => $id[0]]);
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('EtapeDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('titre', 'Titre', 'required');
        $this->form_validation->set_rules('date_debut', 'Date de début', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Etapes_projet'));
            return;
        }

        $titre = $this->input->post('titre');
        $description = $this->input->post('description');
        $date_debut = $this->input->post('date_debut');
        $date_fin_prevue = $this->input->post('date_fin_prevue') ?: NULL;
        $date_fin_reelle = $this->input->post('date_fin_reelle') ?: NULL;
        $statut = $this->input->post('statut') ?: 'a_venir';
        $pourcentage_avancement = $this->input->post('pourcentage_avancement') ?: 0;
        $phase = $this->input->post('phase');
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'titre' => $titre,
            'description' => $description,
            'date_debut' => $date_debut,
            'date_fin_prevue' => $date_fin_prevue,
            'date_fin_reelle' => $date_fin_reelle,
            'statut' => $statut,
            'pourcentage_avancement' => $pourcentage_avancement,
            'phase' => $phase,
            'id_page_associee' => $id_page_associee
        );
        
        $rsp = $this->Model->create('etapes_projet', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Étape du projet créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Etapes_projet'));
    }

    function Update(){
        $id = $this->input->post('id_etape');
        
        // Validation
        $this->form_validation->set_rules('titre', 'Titre', 'required');
        $this->form_validation->set_rules('date_debut', 'Date de début', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Etapes_projet'));
            return;
        }

        $titre = $this->input->post('titre');
        $description = $this->input->post('description');
        $date_debut = $this->input->post('date_debut');
        $date_fin_prevue = $this->input->post('date_fin_prevue') ?: NULL;
        $date_fin_reelle = $this->input->post('date_fin_reelle') ?: NULL;
        $statut = $this->input->post('statut');
        $pourcentage_avancement = $this->input->post('pourcentage_avancement') ?: 0;
        $phase = $this->input->post('phase');
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'titre' => $titre,
            'description' => $description,
            'date_debut' => $date_debut,
            'date_fin_prevue' => $date_fin_prevue,
            'date_fin_reelle' => $date_fin_reelle,
            'statut' => $statut,
            'pourcentage_avancement' => $pourcentage_avancement,
            'phase' => $phase,
            'id_page_associee' => $id_page_associee
        );

        $rsp = $this->Model->update('etapes_projet', ['id_etape' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Étape du projet mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Etapes_projet'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        $rsp = $this->Model->delete('etapes_projet', ['id_etape' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Étape du projet supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Etapes_projet'));
    }
}