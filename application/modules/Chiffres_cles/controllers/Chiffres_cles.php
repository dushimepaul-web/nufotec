<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chiffres_cles extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
        $this->load->helper('form');
    }
    
    public function index()
    {
        // Récupérer les chiffres clés
        $data['chiffres'] = $this->Model->read('chiffres_cles', [], 'ordre', 'ASC');
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('Chiffres_cles_View', $data);
    }

    function ChangeOrdre(){
        $id = $this->input->post('id');
        $direction = $this->input->post('direction'); // 'up' ou 'down'
        
        $chiffre = $this->Model->readOne('chiffres_cles', ['id_chiffre' => $id]);
        if (!$chiffre) {
            $this->session->set_flashdata('error', 'Chiffre clé non trouvé.');
            redirect(base_url('Chiffres_cles'));
            return;
        }

        $current_ordre = $chiffre['ordre'];
        $new_ordre = ($direction == 'up') ? $current_ordre - 1 : $current_ordre + 1;

        // Échanger l'ordre avec l'élément adjacent
        $adjacent = $this->Model->readOne('chiffres_cles', ['ordre' => $new_ordre]);
        if ($adjacent) {
            $this->Model->update('chiffres_cles', 
                ['id_chiffre' => $adjacent['id_chiffre']], 
                ['ordre' => $current_ordre]
            );
        }

        $rsp = $this->Model->update('chiffres_cles', 
            ['id_chiffre' => $id], 
            ['ordre' => $new_ordre]
        );

        if ($rsp) {
            $this->session->set_flashdata('success', 'Ordre mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue.');
        }
        redirect(base_url('Chiffres_cles'));    
    }

    function ChiffreDetail($chiffreDetail){
        $id = $chiffreDetail;
        $data['detail'] = $this->Model->readOne('chiffres_cles', ['id_chiffre' => $id[0]]);
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('ChiffreDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('etiquette', 'Étiquette', 'required');
        $this->form_validation->set_rules('valeur', 'Valeur', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Chiffres_cles'));
            return;
        }

        $etiquette = $this->input->post('etiquette');
        $valeur = $this->input->post('valeur');
        $unite = $this->input->post('unite');
        $description = $this->input->post('description');
        $icone = $this->input->post('icone');
        $ordre = $this->input->post('ordre') ?: 0;
        $annee_vision = $this->input->post('annee_vision') ?: NULL;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'etiquette' => $etiquette,
            'valeur' => $valeur,
            'unite' => $unite,
            'description' => $description,
            'icone' => $icone,
            'ordre' => $ordre,
            'annee_vision' => $annee_vision,
            'id_page_associee' => $id_page_associee
        );
        
        $rsp = $this->Model->create('chiffres_cles', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Chiffre clé créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Chiffres_cles'));
    }

    function Update(){
        $id = $this->input->post('id_chiffre');
        
        // Validation
        $this->form_validation->set_rules('etiquette', 'Étiquette', 'required');
        $this->form_validation->set_rules('valeur', 'Valeur', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Chiffres_cles'));
            return;
        }

        $etiquette = $this->input->post('etiquette');
        $valeur = $this->input->post('valeur');
        $unite = $this->input->post('unite');
        $description = $this->input->post('description');
        $icone = $this->input->post('icone');
        $ordre = $this->input->post('ordre') ?: 0;
        $annee_vision = $this->input->post('annee_vision') ?: NULL;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'etiquette' => $etiquette,
            'valeur' => $valeur,
            'unite' => $unite,
            'description' => $description,
            'icone' => $icone,
            'ordre' => $ordre,
            'annee_vision' => $annee_vision,
            'id_page_associee' => $id_page_associee
        );

        $rsp = $this->Model->update('chiffres_cles', ['id_chiffre' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Chiffre clé mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Chiffres_cles'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        $rsp = $this->Model->delete('chiffres_cles', ['id_chiffre' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Chiffre clé supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Chiffres_cles'));
    }
}