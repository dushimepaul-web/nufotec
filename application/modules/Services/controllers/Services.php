<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Services extends MY_Controller {

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
        $data['services'] = $this->Model->read('services', ['deleted_at' => NULL], 'ordre', 'ASC');
        $data['pages'] = $this->Model->read('pages', ['deleted_at' => NULL], 'id_page', 'ASC');
        $this->load->view('Services_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('titre', 'Titre', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Services'));
            return;
        }

        $titre = $this->input->post('titre');
        $description = $this->input->post('description') ?: NULL;
        $icone = $this->input->post('icone') ?: NULL;
        $lien = $this->input->post('lien') ?: NULL;
        $ordre = $this->input->post('ordre') ?: 0;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        // Upload image si fournie
        $image_url = NULL;
        if (!empty($_FILES['image']['name'])) {
            $image_url = $this->upload_image($_FILES['image']['tmp_name'], $_FILES['image']['name'], 'services');
            if ($image_url === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('Services'));
                return;
            }
        }

        $data = array(
            'titre' => $titre,
            'description' => $description,
            'icone' => $icone,
            'image_url' => $image_url,
            'lien' => $lien,
            'ordre' => $ordre,
            'id_page_associee' => $id_page_associee,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('services', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Service créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Services'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('titre', 'Titre', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Services'));
            return;
        }

        $titre = $this->input->post('titre');
        $description = $this->input->post('description') ?: NULL;
        $icone = $this->input->post('icone') ?: NULL;
        $lien = $this->input->post('lien') ?: NULL;
        $ordre = $this->input->post('ordre') ?: 0;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;

        $data = array(
            'titre' => $titre,
            'description' => $description,
            'icone' => $icone,
            'lien' => $lien,
            'ordre' => $ordre,
            'id_page_associee' => $id_page_associee,
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Upload nouvelle image si fournie
        if (!empty($_FILES['image']['name'])) {
            $new_image = $this->upload_image($_FILES['image']['tmp_name'], $_FILES['image']['name'], 'services');
            if ($new_image === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('Services'));
                return;
            }
            
            // Supprimer l'ancienne image si existe
            $service = $this->Model->readOne('services', ['id_service' => $id]);
            if ($service && !empty($service['image_url']) && file_exists(FCPATH . $service['image_url'])) {
                unlink(FCPATH . $service['image_url']);
            }
            
            $data['image_url'] = $new_image;
        }

        $rsp = $this->Model->update('services', ['id_service' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Service mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Services'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer le service pour supprimer son image
        $service = $this->Model->readOne('services', ['id_service' => $id]);
        
        // Soft delete
        $rsp = $this->Model->update('services', ['id_service' => $id], [
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            // Supprimer l'image physique si existe
            if ($service && !empty($service['image_url']) && file_exists(FCPATH . $service['image_url'])) {
                unlink(FCPATH . $service['image_url']);
            }
            $this->session->set_flashdata('success', 'Service supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Services'));
    }

    // Upload images
    public function upload_image($nom_file, $nom_champ, $folder = 'services')
    {
        $ref_folder = FCPATH . 'attachments/' . $folder . '/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        $final_path = $ref_folder . $fichier . "." . $file_extension;
        move_uploaded_file($nom_file, $final_path);
        
        return 'attachments/' . $folder . '/' . $fichier . "." . $file_extension;
    }
}