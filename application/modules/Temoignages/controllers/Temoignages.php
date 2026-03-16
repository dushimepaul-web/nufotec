<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Temoignages extends MY_Controller {

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
        $data['temoignages'] = $this->Model->read('temoignages', [], 'id_temoignage', 'DESC');
        $data['pages'] = $this->Model->read('pages', ['est_publiee' => 1], 'titre_page', 'ASC');
        $this->load->view('Temoignages_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_approuve = $this->input->post('est_approuve');
        
        $status = ($est_approuve == 1) ? 0 : 1;
        $rsp = $this->Model->update('temoignages', ['id_temoignage' => $id], ['est_approuve' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Temoignages'));    
    }

    function Create(){
        $this->form_validation->set_rules('nom_personne', 'Nom', 'required');
        $this->form_validation->set_rules('message', 'Message', 'required');
        $this->form_validation->set_rules('type', 'Type', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Temoignages'));
            return;
        }

        $data = array(
            'nom_personne' => $this->input->post('nom_personne'),
            'fonction' => $this->input->post('fonction') ?: NULL,
            'organisation' => $this->input->post('organisation') ?: NULL,
            'message' => $this->input->post('message'),
            'note' => $this->input->post('note') ?: NULL,
            'type' => $this->input->post('type'),
            'date_reception' => $this->input->post('date_reception') ?: date('Y-m-d'),
            'est_approuve' => $this->input->post('est_approuve') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: NULL
        );

        // Upload photo si fournie
        if (!empty($_FILES['photo_url']['name'])) {
            $photo = $this->upload_image($_FILES['photo_url']['tmp_name'], $_FILES['photo_url']['name']);
            if ($photo === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('Temoignages'));
                return;
            }
            $data['photo_url'] = $photo;
        }

        $rsp = $this->Model->create('temoignages', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Témoignage créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création du témoignage.');
        }
        redirect(base_url('Temoignages'));
    }

    function Update(){
        $id = $this->input->post('id_temoignage');
        
        $this->form_validation->set_rules('nom_personne', 'Nom', 'required');
        $this->form_validation->set_rules('message', 'Message', 'required');
        $this->form_validation->set_rules('type', 'Type', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Temoignages'));
            return;
        }

        $data = array(
            'nom_personne' => $this->input->post('nom_personne'),
            'fonction' => $this->input->post('fonction') ?: NULL,
            'organisation' => $this->input->post('organisation') ?: NULL,
            'message' => $this->input->post('message'),
            'note' => $this->input->post('note') ?: NULL,
            'type' => $this->input->post('type'),
            'date_reception' => $this->input->post('date_reception') ?: NULL,
            'est_approuve' => $this->input->post('est_approuve') ? 1 : 0,
            'id_page_associee' => $this->input->post('id_page_associee') ?: NULL
        );

        // Upload nouvelle photo si fournie
        if (!empty($_FILES['photo_url']['name'])) {
            $new_photo = $this->upload_image($_FILES['photo_url']['tmp_name'], $_FILES['photo_url']['name']);
            if ($new_photo === NULL) {
                $this->session->set_flashdata('error', 'Format de fichier non valide. Formats acceptés: gif, jpg, png, jpeg, webp, svg');
                redirect(base_url('Temoignages'));
                return;
            }
            
            // Supprimer l'ancienne photo
            $temoignage = $this->Model->readOne('temoignages', ['id_temoignage' => $id]);
            if ($temoignage && !empty($temoignage['photo_url']) && file_exists(FCPATH . 'attachments/Temoignages/' . $temoignage['photo_url'])) {
                unlink(FCPATH . 'attachments/Temoignages/' . $temoignage['photo_url']);
            }
            
            $data['photo_url'] = $new_photo;
        }

        $rsp = $this->Model->update('temoignages', ['id_temoignage' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Témoignage mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Temoignages'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        $temoignage = $this->Model->readOne('temoignages', ['id_temoignage' => $id]);
        
        $rsp = $this->Model->delete('temoignages', ['id_temoignage' => $id]);

        if ($rsp) {
            // Supprimer la photo physique
            if ($temoignage && !empty($temoignage['photo_url']) && file_exists(FCPATH . 'attachments/Temoignages/' . $temoignage['photo_url'])) {
                unlink(FCPATH . 'attachments/Temoignages/' . $temoignage['photo_url']);
            }
            $this->session->set_flashdata('success', 'Témoignage supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Temoignages'));
    }

    public function upload_image($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Temoignages/';
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

        move_uploaded_file($nom_file, $ref_folder . $fichier . "." . $file_extension);
        return $fichier . "." . $file_extension;
    }
}