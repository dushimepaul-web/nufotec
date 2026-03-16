<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Appels_action extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
        $this->load->helper('form');
        $this->load->library('form_validation');
    }
    
    public function index()
    {
        // Récupérer les CTAs actifs
        $data['ctas'] = $this->Model->read('appels_action', [], 'ordre', 'ASC');
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('Appels_action_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_actif = $this->input->post('est_actif');
        
        $status = ($est_actif == 1) ? 0 : 1;
        $rsp = $this->Model->update('appels_action', ['id_cta' => $id], ['est_actif' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Appels_action'));    
    }

    function CtaDetail($ctaDetail){
        $id =  $ctaDetail;
        $data['detail'] = $this->Model->readOne('appels_action', ['id_cta' => $id[0]]);
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL, 'est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('CtaDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('titre', 'Titre', 'required');
        $this->form_validation->set_rules('bouton_texte', 'Texte du bouton', 'required');
        $this->form_validation->set_rules('bouton_lien', 'Lien du bouton', 'required');
        $this->form_validation->set_rules('type_public', 'Type de public', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Appels_action'));
            return;
        }

        $titre = $this->input->post('titre');
        $sous_titre = $this->input->post('sous_titre');
        $bouton_texte = $this->input->post('bouton_texte');
        $bouton_lien = $this->input->post('bouton_lien');
        $type_public = $this->input->post('type_public');
        $date_expiration = $this->input->post('date_expiration') ?: NULL;
        $ordre = $this->input->post('ordre') ?: 0;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;
        $est_actif = $this->input->post('est_actif') ? 1 : 0;

        // Upload image de fond si fournie
        $image_fond_url = NULL;
        if (!empty($_FILES['image_fond']['name'])) {
            $image_fond_url = $this->upload_image($_FILES['image_fond']['tmp_name'], $_FILES['image_fond']['name']);
            if ($image_fond_url === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Appels_action'));
                return;
            }
        } elseif (!empty($this->input->post('image_fond_url'))) {
            $image_fond_url = $this->input->post('image_fond_url');
        }

        $data = array(
            'titre' => $titre,
            'sous_titre' => $sous_titre,
            'bouton_texte' => $bouton_texte,
            'bouton_lien' => $bouton_lien,
            'type_public' => $type_public,
            'image_fond_url' => $image_fond_url,
            'est_actif' => $est_actif,
            'date_expiration' => $date_expiration,
            'ordre' => $ordre,
            'id_page_associee' => $id_page_associee
        );
        
        $rsp = $this->Model->create('appels_action', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Appel à l\'action créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Appels_action'));
    }

    function Update(){
        $id = $this->input->post('id_cta');
        
        // Validation
        $this->form_validation->set_rules('titre', 'Titre', 'required');
        $this->form_validation->set_rules('bouton_texte', 'Texte du bouton', 'required');
        $this->form_validation->set_rules('bouton_lien', 'Lien du bouton', 'required');
        $this->form_validation->set_rules('type_public', 'Type de public', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Appels_action'));
            return;
        }

        $titre = $this->input->post('titre');
        $sous_titre = $this->input->post('sous_titre');
        $bouton_texte = $this->input->post('bouton_texte');
        $bouton_lien = $this->input->post('bouton_lien');
        $type_public = $this->input->post('type_public');
        $date_expiration = $this->input->post('date_expiration') ?: NULL;
        $ordre = $this->input->post('ordre') ?: 0;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;
        $est_actif = $this->input->post('est_actif') ? 1 : 0;

        $data = array(
            'titre' => $titre,
            'sous_titre' => $sous_titre,
            'bouton_texte' => $bouton_texte,
            'bouton_lien' => $bouton_lien,
            'type_public' => $type_public,
            'est_actif' => $est_actif,
            'date_expiration' => $date_expiration,
            'ordre' => $ordre,
            'id_page_associee' => $id_page_associee
        );

        // Gestion de l'image de fond
        if (!empty($_FILES['image_fond']['name'])) {
            $new_image = $this->upload_image($_FILES['image_fond']['tmp_name'], $_FILES['image_fond']['name']);
            if ($new_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Appels_action'));
                return;
            }
            
            // Supprimer l'ancienne image si existe
            $cta = $this->Model->readOne('appels_action', ['id_cta' => $id]);
            if ($cta && !empty($cta['image_fond_url']) && file_exists(FCPATH . 'attachments/Cta/' . basename($cta['image_fond_url']))) {
                unlink(FCPATH . 'attachments/Cta/' . basename($cta['image_fond_url']));
            }
            
            $data['image_fond_url'] = $new_image;
        } elseif (!empty($this->input->post('image_fond_url'))) {
            $data['image_fond_url'] = $this->input->post('image_fond_url');
        }

        $rsp = $this->Model->update('appels_action', ['id_cta' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Appel à l\'action mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Appels_action'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer le CTA pour supprimer son image
        $cta = $this->Model->readOne('appels_action', ['id_cta' => $id]);
        
        $rsp = $this->Model->delete('appels_action', ['id_cta' => $id]);

        if ($rsp) {
            // Supprimer l'image physique si existe
            if ($cta && !empty($cta['image_fond_url']) && file_exists(FCPATH . 'attachments/Cta/' . basename($cta['image_fond_url']))) {
                unlink(FCPATH . 'attachments/Cta/' . basename($cta['image_fond_url']));
            }
            $this->session->set_flashdata('success', 'Appel à l\'action supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Appels_action'));
    }

    // Upload images
    public function upload_image($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Cta/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        move_uploaded_file($nom_file, $ref_folder . $fichier . "." . $file_extension);
        return '/attachments/Cta/' . $fichier . "." . $file_extension;
    }
}