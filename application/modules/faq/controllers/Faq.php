<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Faq extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // SANS filtre deleted_at - la table n'a pas cette colonne
        $data['faq'] = $this->Model->read('faq', null, 'ordre','ASC');
        
        // Passer les catégories à la vue
        $data['categories_list'] = [
            'general' => 'Général',
            'produits' => 'Produits',
            'qualite' => 'Qualité & Certifications',
            'investissement' => 'Investissement',
            'social' => 'Impact Social',
            'technique' => 'Technique',
            'partenariats' => 'Partenariats',
            'livraison' => 'Livraison & Logistique',
            'paiement' => 'Paiement',
            'autre' => 'Autre'
        ];
        
        $data['categorie_badges'] = [
            'general' => 'bg-secondary',
            'produits' => 'bg-success',
            'qualite' => 'bg-info',
            'investissement' => 'bg-warning text-dark',
            'social' => 'bg-primary',
            'technique' => 'bg-dark',
            'partenariats' => 'bg-danger',
            'livraison' => 'bg-light text-dark',
            'paiement' => 'bg-success',
            'autre' => 'bg-light text-dark'
        ];
        
        $this->load->view('Faq_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_publiee = $this->input->post('est_publiee');
        
        $status = ($est_publiee == 1) ? 0 : 1;
        $rsp = $this->Model->update('faq', ['id_faq' => $id], ['est_publiee' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut de publication mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Faq'));    
    }

    function Create(){
        $this->form_validation->set_rules('question', 'Question', 'required');
        $this->form_validation->set_rules('reponse', 'Réponse', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Faq'));
            return;
        }

        $data = array(
            'question' => $this->input->post('question'),
            'reponse' => $this->input->post('reponse'),
            'categorie' => $this->input->post('categorie') ?: 'general',
            'ordre' => $this->input->post('ordre') ?: 0,
            'est_publiee' => $this->input->post('est_publiee') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: NULL
        );
        
        $rsp = $this->Model->create('faq', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Question FAQ créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création de la FAQ.');
        }
        redirect(base_url('Faq'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        $this->form_validation->set_rules('question', 'Question', 'required');
        $this->form_validation->set_rules('reponse', 'Réponse', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Faq'));
            return;
        }

        $data = array(
            'question' => $this->input->post('question'),
            'reponse' => $this->input->post('reponse'),
            'categorie' => $this->input->post('categorie') ?: 'general',
            'ordre' => $this->input->post('ordre') ?: 0,
            'est_publiee' => $this->input->post('est_publiee') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: NULL
        );

        $rsp = $this->Model->update('faq', ['id_faq' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'FAQ mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Faq'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Suppression définitive (pas de soft delete sans deleted_at)
        $rsp = $this->Model->delete('faq', ['id_faq' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'FAQ supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Faq'));
    }
}
