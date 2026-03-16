<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Statistiques_reseaux extends MY_Controller {

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
        $data['statistiques'] = $this->Model->read('statistiques_reseaux', ['deleted_at' => NULL], 'nombre_participants', 'DESC');
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL], 'id_page', 'ASC');
        $this->load->view('Statistiques_reseaux_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('plateforme', 'Plateforme', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Statistiques_reseaux'));
            return;
        }

        $plateforme = $this->input->post('plateforme');
        $nombre_groupes = $this->input->post('nombre_groupes') ?: NULL;
        $nombre_participants = $this->input->post('nombre_participants') ?: NULL;
        $date_mesure = $this->input->post('date_mesure') ?: date('Y-m-d');
        $croissance_mensuelle = $this->input->post('croissance_mensuelle') ?: NULL;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'plateforme' => $plateforme,
            'nombre_groupes' => $nombre_groupes,
            'nombre_participants' => $nombre_participants,
            'date_mesure' => $date_mesure,
            'croissance_mensuelle' => $croissance_mensuelle,
            'id_page_associee' => $id_page_associee,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('statistiques_reseaux', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statistique créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Statistiques_reseaux'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('plateforme', 'Plateforme', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Statistiques_reseaux'));
            return;
        }

        $plateforme = $this->input->post('plateforme');
        $nombre_groupes = $this->input->post('nombre_groupes') ?: NULL;
        $nombre_participants = $this->input->post('nombre_participants') ?: NULL;
        $date_mesure = $this->input->post('date_mesure') ?: date('Y-m-d');
        $croissance_mensuelle = $this->input->post('croissance_mensuelle') ?: NULL;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'plateforme' => $plateforme,
            'nombre_groupes' => $nombre_groupes,
            'nombre_participants' => $nombre_participants,
            'date_mesure' => $date_mesure,
            'croissance_mensuelle' => $croissance_mensuelle,
            'id_page_associee' => $id_page_associee,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $rsp = $this->Model->update('statistiques_reseaux', ['id_stat' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statistique mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Statistiques_reseaux'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Soft delete
        $rsp = $this->Model->update('statistiques_reseaux', ['id_stat' => $id], [
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statistique supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Statistiques_reseaux'));
    }
}